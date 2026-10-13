<?php
namespace OpenCATS\Tests\IntegrationTests;

class WarningReviewTest extends DatabaseTestCase
{
    private $db;
    private $reviews;
    private $company;
    private $actor = 1;
    private $overrides = array();
    private $offset = 0;

    protected function setUp(): void
    {
        parent::setUp();
        require_once LEGACY_ROOT . '/lib/CATSUtility.php';
        require_once LEGACY_ROOT . '/lib/Session.php';
        require_once LEGACY_ROOT . '/lib/WarningReviews.php';
        $this->actor = 1;
        $this->offset = 0;
        $this->overrides = array();
        $session = $this->createStub(\CATSSession::class);
        $session->method('getUserID')->willReturnCallback(fn() => $this->actor);
        $session->method('getAccessLevel')->willReturnCallback(fn($key) => $this->overrides[$key] ?? ACCESS_LEVEL_SA);
        $session->method('getRealAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        $session->method('isLoggedIn')->willReturn(true);
        $session->method('getTimeZoneOffset')->willReturnCallback(fn() => $this->offset);
        $_SESSION['CATS'] = $session;
        $this->db = \DatabaseConnection::getInstance();
        $this->reviews = new \WarningReviews();
        $this->db->query("INSERT INTO company (name, owner, commercial_tier, relationship_status) VALUES ('Review fixture', 1, 'A', 'Client')");
        $this->company = (int) $this->db->getLastInsertID();
    }

    protected function tearDown(): void
    {
        unset($_SESSION['CATS']);
        (new \ReflectionProperty(\DatabaseConnection::class, '_instance'))->setValue(null, null);
        parent::tearDown();
    }

    private function snapshot($reason = 'stale_relationship')
    {
        return $this->reviews->get('W07', DATA_ITEM_COMPANY, $this->company, $reason);
    }

    private function save($snapshot, $request = 'request-0000000001', $changes = array())
    {
        $i = $snapshot['identity'];
        $args = array($i['warning_rule'], $i['data_item_type'], $i['data_item_id'], $i['reason_key'], $i['reason_id'],
            'close', 'already_handled', date('Y-m-d', strtotime('+5 days')), $snapshot['version'], $snapshot['evidence'], $request);
        return $this->reviews->save(...array_replace($args, $changes));
    }

    private function rejected($call)
    {
        try { $call(); }
        catch (\InvalidArgumentException | \RuntimeException | \mysqli_sql_exception $e) { self::assertNotEmpty($e->getMessage()); return; }
        self::fail('Expected request rejection.');
    }

    private function countRows($table)
    {
        return (int) $this->db->getAssoc('SELECT COUNT(*) AS n FROM ' . $table)['n'];
    }

    public function testPersonalReasonSeparationAndUnchangedSourceQueues(): void
    {
        $before = (new \Tasks())->getRecruitmentWarnings();
        self::assertSame(1, $this->save($this->snapshot()));
        self::assertSame(0, $this->snapshot('no_next_action')['version']);
        self::assertSame(1, $this->save($this->snapshot('no_next_action')));
        self::assertSame($before, (new \Tasks())->getRecruitmentWarnings());
        $this->db->query("INSERT INTO user (user_name, access_level) VALUES ('reviewer-two', 400)");
        $this->actor = (int) $this->db->getLastInsertID();
        // Existing W07 scope is owner-only. A native ownership change must not
        // transfer the previous owner's personal review to the new owner.
        $this->db->query('UPDATE company SET owner = ' . $this->actor . ' WHERE company_id = ' . $this->company);
        self::assertSame(0, $this->snapshot()['version']);
        $this->save($this->snapshot());
        self::assertSame(3, $this->countRows('warning_review'));
        self::assertSame(3, $this->countRows('history'));
        $this->actor = 1;
        $this->rejected(fn() => $this->snapshot());
    }

    public function testDuplicateAndStaleWritesCannotExtendDeadlineOrAudit(): void
    {
        $initial = $this->snapshot();
        self::assertSame(1, $this->save($initial));
        $stored = $this->snapshot();
        self::assertSame(1, $this->save($initial));
        self::assertSame($stored, $this->snapshot());
        self::assertSame(1, $this->countRows('history'));
        $this->rejected(fn() => $this->save($initial, 'request-0000000002'));
        $this->rejected(fn() => $this->save($initial, 'request-0000000001', array(7 => date('Y-m-d', strtotime('+6 days')))));
        self::assertSame(2, $this->save($stored, 'request-0000000002', array(5 => 'reset', 6 => null, 7 => null)));
        $this->rejected(fn() => $this->save($initial));
        self::assertNull($this->snapshot()['state']['review_until']);
        self::assertSame(2, $this->countRows('history'));
    }

    public function testInvalidRequestsPermissionsAndSourceChanges(): void
    {
        $s = $this->snapshot();
        foreach (array(array(5 => array()), array(5 => 'log'), array(6 => 'free text'), array(7 => '2026-02-30'),
            array(7 => date('Y-m-d')), array(7 => date('Y-m-d', strtotime('+31 days'))), array(8 => '0'),
            array(9 => array()), array(10 => 'short'), array(2 => array()), array(4 => 1)) as $change)
            $this->rejected(fn() => $this->save($s, 'request-0000000001', $change));
        foreach (array('companies.show', 'companies.edit', 'tasks.show', 'contacts.show') as $permission)
        {
            $this->overrides[$permission] = ACCESS_LEVEL_DISABLED;
            $this->rejected(fn() => $this->save($s));
            $this->overrides = array();
        }
        $this->db->query("UPDATE company SET commercial_tier = 'B' WHERE company_id = " . $this->company);
        $this->rejected(fn() => $this->save($s));
        $s = $this->snapshot();
        $this->db->query('UPDATE user SET access_level = 0 WHERE user_id = 1');
        $this->rejected(fn() => $this->save($s));
        self::assertSame(0, $this->countRows('warning_review'));
        self::assertSame(0, $this->countRows('history'));
    }

    public function testReasonIdentityAndUnapprovedActionsFailClosed(): void
    {
        foreach (array(array('W01', DATA_ITEM_TASK, 'overdue_commitment'),
            array('W08', DATA_ITEM_TASK, 'invalid_assignee'), array('W08', DATA_ITEM_JOBORDER, 'invalid_owner'),
            array('W08', DATA_ITEM_JOBORDER, 'invalid_recruiter')) as [$rule, $type, $reason])
        {
            $i = \WarningReviews::identity($rule, $type, 1, $reason);
            self::assertSame($reason, $i['reason_key']);
            foreach (array('close', 'remind_later') as $action)
                $this->rejected(fn() => $this->reviews->save($rule, $type, 1, $reason, 0, $action, null,
                    date('Y-m-d', strtotime('+1 day')), 0, str_repeat('a', 64), 'request-0000000001'));
        }
        $a = \WarningReviews::identity('W07', DATA_ITEM_COMPANY, 1, 'overdue_task', 10);
        $b = \WarningReviews::identity('W07', DATA_ITEM_COMPANY, 1, 'overdue_task', 11);
        self::assertNotSame($a, $b);
        $this->rejected(fn() => \WarningReviews::identity('W07', DATA_ITEM_COMPANY, 1, 'overdue_task'));
        $this->rejected(fn() => \WarningReviews::identity('W08', DATA_ITEM_JOBORDER, 1, 'invalid_assignee'));
    }

    public function testHistoryFailureRollsBackInsertAndUpdate(): void
    {
        $s = $this->snapshot();
        $this->db->query("CREATE TRIGGER reject_review_history BEFORE INSERT ON history FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Review audit test failure'");
        $this->rejected(fn() => $this->save($s));
        self::assertSame(0, $this->countRows('warning_review'));
        $this->db->query('DROP TRIGGER reject_review_history');
        $this->save($s);
        $before = $this->snapshot();
        $this->db->query("CREATE TRIGGER reject_review_history BEFORE INSERT ON history FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Review audit test failure'");
        $this->rejected(fn() => $this->save($before));
        self::assertSame($before, $this->snapshot());
        self::assertSame(1, $this->countRows('history'));
        self::assertTrue($this->db->beginTransaction());
        $this->rejected(fn() => $this->save($before));
        self::assertTrue($this->db->rollbackTransaction());
    }

    public function testReminderPresetsAndIndependentTaskReasons(): void
    {
        $s = $this->snapshot();
        $tomorrow = substr(\DateUtility::addWorkingDays(\DateUtility::getAdjustedDate('Y-m-d') . ' 00:00:00', 1), 0, 10);
        self::assertSame(1, $this->save($s, 'request-reminder-01', array(5 => 'remind_later', 6 => null, 7 => $tomorrow)));
        self::assertSame($tomorrow, $this->snapshot()['state']['review_until']);
        self::assertSame(1, $this->filtered()['suppressedReasonCount']);
        $taskIDs = array();
        foreach (array(1, 2) as $number)
            $taskIDs[] = (new \Tasks())->add(array('title' => 'Review commitment ' . $number,
                'dataItemType' => DATA_ITEM_COMPANY, 'dataItemID' => $this->company,
                'assignedTo' => 1, 'purpose' => 'relationship_follow_up', 'dueDate' => date('Y-m-d', strtotime('-1 day'))));
        foreach ($taskIDs as $id)
        {
            $taskState = $this->reviews->get('W07', DATA_ITEM_COMPANY, $this->company, 'overdue_task', $id);
            self::assertSame(0, $taskState['version']);
            $this->rejected(fn() => $this->save($taskState));
        }
        $this->rejected(fn() => $this->reviews->get('W07', DATA_ITEM_COMPANY, $this->company, 'overdue_task', 2147483647));
        self::assertSame(1, $this->countRows('warning_review'));
    }

    private function filtered()
    {
        return $this->reviews->getRecruitmentWarnings();
    }

    private function commitment($days = -1)
    {
        return (new \Tasks())->add(array('title' => 'Review commitment',
            'dataItemType' => DATA_ITEM_COMPANY, 'dataItemID' => $this->company,
            'assignedTo' => 1, 'purpose' => 'relationship_follow_up',
            'dueDate' => date('Y-m-d', strtotime($days . ' days'))));
    }

    public function testFilteringCountsResetAndReadOnlyHistory(): void
    {
        $before = $this->filtered();
        $this->save($this->snapshot('no_next_action'));
        $partial = $this->filtered();
        self::assertSame(1, $partial['visibleCounts']['W07']);
        self::assertSame('stale_relationship', $partial['visibleWarnings'][0]['reason']);
        self::assertSame(1, $partial['suppressedReasonCount']);
        $this->save($this->snapshot());
        $history = $this->countRows('history');
        $stored = $this->db->getAllAssoc('SELECT * FROM warning_review');
        $after = $this->filtered();
        foreach (array('warnings', 'counts', 'jobsAtRisk') as $key) self::assertSame($before[$key], $after[$key]);
        self::assertSame(0, $after['visibleCounts']['W07']);
        self::assertSame(2, $after['suppressedReasonCount']);
        self::assertSame($history, $this->countRows('history'));
        self::assertSame($stored, $this->db->getAllAssoc('SELECT * FROM warning_review'));
        $this->save($this->snapshot(), 'request-reset-0001', array(5 => 'reset', 6 => null, 7 => null));
        self::assertSame(1, $this->filtered()['visibleCounts']['W07']);
    }

    public function testTaskReasonsAndMaterialChanges(): void
    {
        $a = $this->commitment();
        $b = $this->commitment(-2);
        $this->save($this->snapshot());
        $result = $this->filtered();
        $company = array_values(array_filter($result['visibleWarnings'], fn($row) => $row['code'] === 'W07'))[0];
        self::assertSame(array('overdue_task'), $company['relationshipAttention']['reasons']);
        self::assertEqualsCanonicalizing(array($a, $b), array_column($company['relationshipAttention']['tasks'], 'taskID'));
        self::assertSame('overdue_task', $company['reason']);
        self::assertSame(1, $result['visibleCounts']['W07']);
        self::assertSame($result['counts']['W01'], $result['visibleCounts']['W01']);
        self::assertSame($result['counts']['W08'], $result['visibleCounts']['W08']);
        foreach (array("due_date = DATE_SUB(due_date, INTERVAL 1 DAY)", 'assigned_to = NULL', "status = 'cancelled'") as $change)
        {
            $this->db->query('UPDATE task SET ' . $change . ' WHERE task_id = ' . $a);
            self::assertSame(1, $this->filtered()['suppressedReasonCount']);
            $this->save($this->snapshot(), 'request-change-' . str_pad((string) strlen($change), 4, '0'));
        }
        $this->commitment(100);
        self::assertSame(1, $this->filtered()['suppressedReasonCount']);
    }

    public function testExpiryMalformedStateAndTimeIndependentEvidence(): void
    {
        $this->save($this->snapshot());
        $hash = $this->snapshot()['evidence'];
        // Older action/age without changed source evidence retains suppression.
        $this->db->query("UPDATE warning_review SET action_at = DATE_SUB(NOW(), INTERVAL 1 DAY)");
        self::assertSame($hash, $this->snapshot()['evidence']);
        self::assertSame(1, $this->filtered()['suppressedReasonCount']);
        $this->db->query("UPDATE warning_review SET review_until = CURDATE()");
        self::assertSame(0, $this->filtered()['suppressedReasonCount']);
        foreach (array("evidence_hash = 'bad'", "action = 'unknown'", "version = 0", "close_reason = NULL",
            "review_until = DATE_ADD(CURDATE(), INTERVAL 40 DAY)") as $change)
        {
            $this->db->query("UPDATE warning_review SET evidence_hash = '" . $hash . "', action = 'close', version = 1,
                close_reason = 'already_handled', review_until = DATE_ADD(CURDATE(), INTERVAL 5 DAY)");
            $this->db->query('UPDATE warning_review SET ' . $change);
            self::assertSame(0, $this->filtered()['suppressedReasonCount']);
        }
    }

    public function testDatePassageAndReviewExpiry(): void
    {
        $this->commitment(100);
        $before = $this->snapshot();
        $until = substr(\DateUtility::addWorkingDays(date('Y-m-d') . ' 00:00:00', 5), 0, 10);
        $this->save($before, 'request-reminder-02', array(7 => $until));
        $this->offset = 24;
        self::assertSame($before['evidence'], $this->snapshot()['evidence']);
        self::assertSame(1, $this->filtered()['suppressedReasonCount']);
        $this->offset = 24 * (int) (new \DateTimeImmutable(date('Y-m-d')))->diff(new \DateTimeImmutable($until))->days;
        self::assertSame(0, $this->filtered()['suppressedReasonCount']);
    }

    public function testPersonalFilteringPreservesOriginalJobWarningsAndRisk(): void
    {
        $this->db->query("INSERT INTO joborder (company_id, title, owner, recruiter, status, first_actionable_at)
            VALUES (" . $this->company . ", 'Risk fixture', 1, 1, 'Active', DATE_SUB(NOW(), INTERVAL 30 DAY))");
        $job = (int) $this->db->getLastInsertID();
        $before = $this->filtered();
        self::assertSame(1, $before['counts']['W02']);
        self::assertSame(1, $before['counts']['W03']);
        self::assertSame(1, $before['jobsAtRisk']);
        $this->save($this->snapshot());
        $this->save($this->snapshot('no_next_action'));
        $after = $this->filtered();
        self::assertSame($before['counts'], $after['counts']);
        self::assertSame(1, $after['jobsAtRisk']);
        foreach (array('W02', 'W03', 'W01', 'W08') as $code)
            self::assertSame($after['counts'][$code], $after['visibleCounts'][$code]);
        foreach (array('W02' => 'no_recorded_submission', 'W03' => 'no_recorded_candidate_progress') as $code => $reason)
        {
            $snapshot = $this->reviews->get($code, DATA_ITEM_JOBORDER, $job, $reason);
            $this->save($snapshot, 'request-job-' . $code . '-001', array(5 => 'remind_later', 6 => null,
                7 => \WarningReviews::reminderDates()[5]));
            self::assertSame(0, $this->filtered()['visibleCounts'][$code]);
        }
        self::assertSame($before['counts'], $this->filtered()['counts']);
        self::assertSame(1, $this->filtered()['jobsAtRisk']);
    }

    public function testPolicyClassificationAndRecurringEvidence(): void
    {
        $this->save($this->snapshot());
        $settings = new \CompanySettings();
        $policy = $settings->getAttention();
        ++$policy['tiers']['A']['days'];
        $settings->setAttention($policy);
        self::assertSame(0, $this->filtered()['suppressedReasonCount']);
        $this->save($this->snapshot(), 'request-policy-001');
        $this->db->query("UPDATE company SET commercial_tier = 'B' WHERE company_id = " . $this->company);
        self::assertSame(0, $this->filtered()['suppressedReasonCount']);
        $this->save($this->snapshot(), 'request-policy-002');
        $this->db->query("UPDATE company SET relationship_status = 'Prospect' WHERE company_id = " . $this->company);
        self::assertSame(0, $this->filtered()['suppressedReasonCount']);
        $this->save($this->snapshot(), 'request-policy-003');
        $this->db->query("UPDATE company SET relationship_status = 'Dormant' WHERE company_id = " . $this->company);
        self::assertSame(0, $this->filtered()['counts']['W07']);
        // No occurrence history exists: exact evidence restoration retains a review.
        $this->db->query("UPDATE company SET relationship_status = 'Prospect' WHERE company_id = " . $this->company);
        self::assertSame(1, $this->filtered()['suppressedReasonCount']);
        unset($_SESSION['CATS']);
        self::assertSame(array(), $this->filtered()['visibleWarnings']);
    }

    public function testPersonalFilteringOwnershipPermissionsAndDisabledActor(): void
    {
        $this->save($this->snapshot());
        $this->save($this->snapshot('no_next_action'));
        self::assertSame(0, $this->filtered()['visibleCounts']['W07']);
        $this->db->query("INSERT INTO user (user_name, access_level) VALUES ('other-reviewer', 400)");
        $other = (int) $this->db->getLastInsertID();
        $this->db->query('UPDATE company SET owner = ' . $other . ' WHERE company_id = ' . $this->company);
        self::assertSame(0, $this->filtered()['counts']['W07']);
        $this->actor = $other;
        self::assertSame(1, $this->filtered()['visibleCounts']['W07']);
        self::assertSame(0, $this->filtered()['suppressedReasonCount']);
        foreach (array('companies.show', 'contacts.show', 'tasks.show') as $permission)
        {
            $this->overrides[$permission] = ACCESS_LEVEL_DISABLED;
            self::assertSame(0, $this->filtered()['counts']['W07']);
            $this->overrides = array();
        }
        $this->db->query('UPDATE user SET access_level = 0 WHERE user_id = ' . $other);
        self::assertSame(array(), $this->filtered()['visibleWarnings']);
    }

    public function testActivityResolutionRecurrenceAndHomeRendering(): void
    {
        require_once LEGACY_ROOT . '/lib/Template.php';
        $this->db->query("UPDATE company SET name = '<script>private</script>' WHERE company_id = " . $this->company);
        $this->save($this->snapshot());
        $this->save($this->snapshot('no_next_action'));
        $this->db->query("INSERT INTO activity (data_item_id, data_item_type, type, entered_by, date_occurred)
            VALUES (" . $this->company . ', ' . DATA_ITEM_COMPANY . ", 500, 1, NOW())");
        $result = $this->filtered();
        self::assertSame(1, $result['suppressedReasonCount']);
        self::assertSame(array(), $result['visibleWarnings']);
        $task = $this->commitment(1);
        self::assertNotContains('stale_relationship', $this->filtered()['warnings'][0]['relationshipAttention']['reasons']);
        $this->db->query("UPDATE task SET status = 'completed' WHERE task_id = " . $task);
        self::assertSame(0, $this->filtered()['visibleCounts']['W07']);
        $this->save($this->snapshot('no_next_action'), 'request-home-reset', array(5 => 'reset', 6 => null, 7 => null));
        $template = new \Template();
        $template->assign('recruitmentWarnings', $this->filtered());
        ob_start();
        try { $template->display('./modules/home/RecruitmentWarnings.tpl'); $html = ob_get_contents(); }
        finally { ob_end_clean(); }
        self::assertStringContainsString('1 visible warnings', $html);
        self::assertStringContainsString('&lt;script&gt;private&lt;/script&gt;', $html);
        self::assertStringNotContainsString('<script>private</script>', $html);
        $this->overrides['companies.show'] = ACCESS_LEVEL_DISABLED;
        $template->assign('recruitmentWarnings', $this->filtered());
        ob_start();
        try { $template->display('./modules/home/RecruitmentWarnings.tpl'); $html = ob_get_contents(); }
        finally { ob_end_clean(); }
        self::assertStringNotContainsString('private', $html);
        self::assertStringContainsString('0 visible warnings', $html);
    }

    public function testReminderSurvivesTimezoneAndDstChanges(): void
    {
        // Pin the database action clock at a date boundary. A one-hour offset
        // change moves its interpreted date, but must not reinterpret the preset.
        $this->db->query("SET timestamp = UNIX_TIMESTAMP(CONCAT(CURDATE(), ' 23:30:00'))");
        $until = substr(\DateUtility::addWorkingDays(\DateUtility::getAdjustedDate('Y-m-d') . ' 00:00:00', 5), 0, 10);
        $this->save($this->snapshot(), 'request-dst-remind', array(5 => 'remind_later', 6 => null, 7 => $until));
        $stored = $this->snapshot();
        foreach (array(0, 1, -8, 14, 0) as $offset)
        {
            $this->offset = $offset;
            self::assertSame(1, $this->filtered()['suppressedReasonCount']);
            self::assertSame($stored, $this->snapshot());
        }
        // Malformed action metadata and dates still fail open to warning visibility.
        $this->db->query("UPDATE warning_review SET close_reason = 'already_handled'");
        self::assertSame(0, $this->filtered()['suppressedReasonCount']);
        $this->db->query("UPDATE warning_review SET close_reason = NULL, review_until = '0000-00-00'");
        self::assertSame(0, $this->filtered()['suppressedReasonCount']);
        $this->db->query("UPDATE warning_review SET review_until = CURDATE()");
        self::assertSame(0, $this->filtered()['suppressedReasonCount']);
    }

    public function testReasonSpecificEvidenceDoesNotCrossInvalidate(): void
    {
        $task = $this->commitment(100);
        $stale = $this->snapshot();
        $next = $this->snapshot('no_next_action');
        $this->save($stale);
        $this->save($next);
        $this->db->query("UPDATE task SET due_date = DATE_ADD(due_date, INTERVAL 1 DAY) WHERE task_id = " . $task);
        self::assertSame($stale['evidence'], $this->snapshot()['evidence']);
        self::assertNotSame($next['evidence'], $this->snapshot('no_next_action')['evidence']);
        self::assertSame(1, $this->filtered()['suppressedReasonCount']);
        $this->save($this->snapshot('no_next_action'), 'request-reason-002');
        $next = $this->snapshot('no_next_action');
        // An old qualifying contact changes stale evidence without resolving it.
        $this->db->query("INSERT INTO activity (data_item_id, data_item_type, type, entered_by, date_occurred)
            VALUES (" . $this->company . ', ' . DATA_ITEM_COMPANY . ", 500, 1, DATE_SUB(NOW(), INTERVAL 60 DAY))");
        self::assertNotSame($stale['evidence'], $this->snapshot()['evidence']);
        self::assertSame($next['evidence'], $this->snapshot('no_next_action')['evidence']);
        self::assertSame(1, $this->filtered()['suppressedReasonCount']);
    }

    public function testVisibleCompanyOrderingUsesRemainingReasonAndDate(): void
    {
        $first = $this->company;
        $this->commitment(5);
        $this->save($this->snapshot()); // Primary stale becomes upcoming.
        $this->db->query("INSERT INTO company (name, owner, commercial_tier, relationship_status)
            VALUES ('Earlier upcoming', 1, 'A', 'Client')");
        $second = $this->company = (int) $this->db->getLastInsertID();
        $this->commitment(2);
        $this->save($this->snapshot());
        $this->db->query("INSERT INTO company (name, owner, commercial_tier, relationship_status)
            VALUES ('Same date tie', 1, 'A', 'Client')");
        $third = $this->company = (int) $this->db->getLastInsertID();
        $this->commitment(2);
        $this->save($this->snapshot());
        $this->db->query("INSERT INTO company (name, owner, commercial_tier, relationship_status)
            VALUES ('Still stale', 1, 'B', 'Client')");
        $fourth = $this->company = (int) $this->db->getLastInsertID();
        $this->save($this->snapshot('no_next_action'));
        $result = $this->filtered();
        self::assertSame(array($fourth, $first, $second, $third), array_column($result['warnings'], 'entityID'));
        self::assertSame(array($fourth, $second, $third, $first), array_column($result['visibleWarnings'], 'entityID'));
        self::assertSame(array('stale_relationship', 'upcoming', 'upcoming', 'upcoming'), array_column($result['visibleWarnings'], 'reason'));
        self::assertSame(date('Y-m-d', strtotime('+2 days')), $result['visibleWarnings'][1]['relationshipAttention']['sortDate']);
        self::assertSame(4, $result['counts']['W07']);
        self::assertSame(4, $result['visibleCounts']['W07']);
        self::assertSame($result['visibleWarnings'], $this->filtered()['visibleWarnings']);
    }

    public function testHomeListLimitPreservesFullCounts(): void
    {
        require_once LEGACY_ROOT . '/lib/Template.php';
        for ($i = 1; $i <= 21; ++$i)
            $this->db->query("INSERT INTO company (name, owner, commercial_tier, relationship_status)
                VALUES ('Limit company " . $i . "', 1, 'A', 'Client')");
        $result = $this->filtered();
        $template = new \Template();
        $template->assign('recruitmentWarnings', $result);
        ob_start();
        try { $template->display('./modules/home/RecruitmentWarnings.tpl'); $html = ob_get_contents(); }
        finally { ob_end_clean(); }
        self::assertSame(22, $result['visibleCounts']['W07']);
        self::assertSame(20, substr_count($html, '<a href='));
        self::assertStringContainsString('22 visible warnings', $html);
        self::assertStringContainsString('Underlying authorised warnings: 22', $html);
        self::assertStringContainsString('Showing 20 of 22 visible warnings', $html);
        self::assertStringContainsString('2 additional warnings', $html);
        self::assertSame($result, $this->filtered());
        $result['visibleWarnings'] = array_slice($result['visibleWarnings'], 0, 20);
        $result['visibleCounts']['W07'] = 20;
        $template->assign('recruitmentWarnings', $result);
        ob_start();
        try { $template->display('./modules/home/RecruitmentWarnings.tpl'); $html = ob_get_contents(); }
        finally { ob_end_clean(); }
        self::assertSame(20, substr_count($html, '<a href='));
        self::assertStringNotContainsString('additional warnings', $html);
    }

    private function reviewJob()
    {
        $this->db->query("INSERT INTO joborder (company_id, title, owner, recruiter, status, first_actionable_at)
            VALUES (" . $this->company . ", '<script>Job</script>', 1, 1, 'Active', DATE_SUB(NOW(), INTERVAL 30 DAY))");
        return (int) $this->db->getLastInsertID();
    }

    public function testJobPersonalPresetsIsolationExpiryAndReset(): void
    {
        $job = $this->reviewJob();
        $this->db->query("UPDATE joborder SET w02_reviewed_at = NOW(), w03_reviewed_at = NOW() WHERE joborder_id = " . $job);
        $legacy = $this->db->getAssoc('SELECT w02_reviewed_at, w03_reviewed_at FROM joborder WHERE joborder_id = ' . $job);
        $this->db->query("INSERT INTO user (user_name, access_level) VALUES ('second-reviewer', 400)");
        $second = (int) $this->db->getLastInsertID();
        foreach (array('W02' => 'no_recorded_submission', 'W03' => 'no_recorded_candidate_progress') as $code => $reason)
        {
            foreach (\WarningReviews::reminderDates() as $days => $until)
            {
                $this->actor = 1;
                $s = $this->reviews->get($code, DATA_ITEM_JOBORDER, $job, $reason);
                $this->rejected(fn() => $this->save($s)); // Temporary Close remains forbidden.
                $changes = array(5 => 'remind_later', 6 => null, 7 => $until);
                $this->save($s, 'request-preset-' . $code . '-' . $days, $changes);
                $history = $this->countRows('history');
                $this->save($s, 'request-preset-' . $code . '-' . $days, $changes);
                self::assertSame($history, $this->countRows('history'));
                $this->rejected(fn() => $this->save($s, 'request-stale-' . $code . '-' . $days, $changes));
                $result = $this->reviews->getForSource(DATA_ITEM_JOBORDER, $job);
                self::assertSame(1, $result['counts'][$code]);
                self::assertSame(0, $result['visibleCounts'][$code]);
                self::assertSame(1, $result['jobsAtRisk']);
                $this->actor = $second;
                self::assertSame(1, $this->reviews->getForSource(DATA_ITEM_JOBORDER, $job)['visibleCounts'][$code]);
                if ($days === 1) self::assertSame(0, $this->reviews->get($code, DATA_ITEM_JOBORDER, $job, $reason)['version']);
                $this->save($this->reviews->get($code, DATA_ITEM_JOBORDER, $job, $reason), 'request-second-' . $code . '-' . $days, $changes);
                $this->actor = 1;
                $saved = $this->reviews->get($code, DATA_ITEM_JOBORDER, $job, $reason);
                $today = new \DateTimeImmutable(\DateUtility::getAdjustedDate('Y-m-d'));
                $this->offset = 24 * (int) $today->diff(new \DateTimeImmutable($until))->days;
                self::assertSame(1, $this->reviews->getForSource(DATA_ITEM_JOBORDER, $job)['visibleCounts'][$code]);
                $this->offset = 0;
                self::assertSame(0, $this->reviews->getForSource(DATA_ITEM_JOBORDER, $job)['visibleCounts'][$code]);
                $this->save($saved, 'request-reset-' . $code . '-' . $days, array(5 => 'reset', 6 => null, 7 => null));
                self::assertSame(1, $this->reviews->getForSource(DATA_ITEM_JOBORDER, $job)['visibleCounts'][$code]);
                $this->actor = $second;
                self::assertSame(0, $this->reviews->getForSource(DATA_ITEM_JOBORDER, $job)['visibleCounts'][$code]);
                $this->save($this->reviews->get($code, DATA_ITEM_JOBORDER, $job, $reason), 'request-second-reset-' . $code . '-' . $days, array(5 => 'reset', 6 => null, 7 => null));
            }
        }
        self::assertSame($legacy, $this->db->getAssoc('SELECT w02_reviewed_at, w03_reviewed_at FROM joborder WHERE joborder_id = ' . $job));
        self::assertSame(0, (int) $this->db->getAssoc("SELECT COUNT(*) AS n FROM history WHERE the_field IN ('w02_reviewed_at', 'w03_reviewed_at')")['n']);
    }

    public function testJobAccessEvidenceAndHistoryRollback(): void
    {
        $job = $this->reviewJob();
        $s = $this->reviews->get('W03', DATA_ITEM_JOBORDER, $job, 'no_recorded_candidate_progress');
        $changes = array(5 => 'remind_later', 6 => null, 7 => \WarningReviews::reminderDates()[5]);
        foreach (array('joborders.show', 'joborders.edit', 'candidates.show') as $permission)
        {
            $this->overrides[$permission] = ACCESS_LEVEL_DISABLED;
            $this->rejected(fn() => $this->save($s, 'request-access-001', $changes));
            $this->overrides = array();
        }
        $this->db->query("UPDATE joborder SET first_actionable_at = DATE_SUB(first_actionable_at, INTERVAL 1 DAY) WHERE joborder_id = " . $job);
        $this->rejected(fn() => $this->save($s, 'request-evidence-001', $changes));
        $s = $this->reviews->get('W03', DATA_ITEM_JOBORDER, $job, 'no_recorded_candidate_progress');
        $this->db->query("CREATE TRIGGER reject_review_history BEFORE INSERT ON history FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Review audit test failure'");
        $this->rejected(fn() => $this->save($s, 'request-audit-001', $changes));
        self::assertSame(0, $this->countRows('warning_review'));
        $this->db->query('DROP TRIGGER reject_review_history');
        $this->save($s, 'request-audit-002', $changes);
        $this->overrides['joborders.hidden'] = ACCESS_LEVEL_READ;
        $this->db->query('UPDATE joborder SET is_admin_hidden = 1 WHERE joborder_id = ' . $job);
        $this->rejected(fn() => $this->save($s, 'request-hidden-001', $changes));
        $result = $this->reviews->getForSource(DATA_ITEM_JOBORDER, $job);
        self::assertSame(array(), $result['reviews']);
        self::assertSame(array(), $result['suppressedWarnings']);
        $this->overrides = array();
        $this->db->query("UPDATE joborder SET status = 'Closed' WHERE joborder_id = " . $job);
        self::assertSame(array(), $this->reviews->getForSource(DATA_ITEM_JOBORDER, $job)['suppressedWarnings']);
    }

    public function testSourceAndHomeControlsExposeRecoverableReasonsOnly(): void
    {
        require_once LEGACY_ROOT . '/lib/Template.php';
        $job = $this->reviewJob();
        $this->save($this->snapshot());
        $template = new \Template();
        $template->assign('recruitmentWarnings', $this->reviews->getForSource(DATA_ITEM_COMPANY, $this->company));
        ob_start();
        try { $template->display('./modules/home/WarningReviewSource.tpl'); $html = ob_get_contents(); }
        finally { ob_end_clean(); }
        self::assertStringContainsString('Show Again', $html);
        self::assertStringContainsString('Temporary Close', $html);
        self::assertStringNotContainsString('Mark Reviewed', $html);
        self::assertSame(1, substr_count($html, 'id="warningReviewModal"'));
        $result = $this->reviews->getForSource(DATA_ITEM_JOBORDER, $job);
        $template->assign('recruitmentWarnings', $result);
        ob_start();
        try { $template->display('./modules/joborders/RecruitmentWarnings.tpl'); $html = ob_get_contents(); }
        finally { ob_end_clean(); }
        self::assertStringContainsString('Mark Reviewed — 5 working days', $html);
        self::assertStringNotContainsString('data-review-action="close"', $html);
        self::assertStringContainsString('&lt;script&gt;Job&lt;/script&gt;', $html);
        foreach ($result['reviews'] as $reasons)
            foreach ($reasons as $review) self::assertStringContainsString($review['evidence'], $html);
        $s = $this->reviews->get('W02', DATA_ITEM_JOBORDER, $job, 'no_recorded_submission');
        $this->save($s, 'request-render-001', array(5 => 'remind_later', 6 => null, 7 => \WarningReviews::reminderDates()[5]));
        $template->assign('recruitmentWarnings', $this->filtered());
        ob_start();
        try { $template->display('./modules/home/RecruitmentWarnings.tpl'); $html = ob_get_contents(); }
        finally { ob_end_clean(); }
        self::assertStringContainsString('data-bs-toggle="collapse"', $html);
        self::assertStringContainsString('Personally Reviewed / Remind Later (2)', $html);
        self::assertSame(2, substr_count($html, '>Show Again</button>'));
        $this->overrides['joborders.edit'] = ACCESS_LEVEL_READ;
        $template->assign('recruitmentWarnings', $this->reviews->getForSource(DATA_ITEM_JOBORDER, $job));
        ob_start();
        try { $template->display('./modules/joborders/RecruitmentWarnings.tpl'); $html = ob_get_contents(); }
        finally { ob_end_clean(); }
        self::assertStringNotContainsString('data-review-action=', $html);
        self::assertStringNotContainsString('>Show Again</button>', $html);
    }

    public function testNativePostHandlerSavesAndRetriesWithFixedReturnDestinations(): void
    {
        require_once LEGACY_ROOT . '/lib/Template.php';
        require_once LEGACY_ROOT . '/lib/UserInterface.php';
        require_once LEGACY_ROOT . '/lib/Hooks.php';
        require_once LEGACY_ROOT . '/modules/home/HomeUI.php';
        $_SESSION['CATS']->method('isCSRFTokenValid')->willReturn(true);
        $ui = new class extends \HomeUI {
            public $destination;
            public function __construct() {}
            protected function redirectWarningReview($uri) { $this->destination = $uri; }
        };
        $job = $this->reviewJob();
        foreach (array('home', 'companies', 'joborders') as $destination)
        {
            $snapshot = $destination === 'joborders' ? $this->reviews->get('W02', DATA_ITEM_JOBORDER, $job, 'no_recorded_submission') : $this->snapshot();
            $_GET = array('a' => 'reviewWarning');
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST = array_map('strval', array_merge($snapshot['identity'], array('csrfToken' => 'valid',
                'expectedVersion' => $snapshot['version'], 'expectedEvidence' => $snapshot['evidence'],
                'requestID' => 'request-post-' . $destination, 'returnTo' => $destination,
                'reviewAction' => 'remind_later', 'reviewUntil' => \WarningReviews::reminderDates()[5])));
            $ui->handleRequest();
            self::assertStringStartsWith('m=' . $destination, $ui->destination);
            self::assertStringEndsWith('&warningReview=saved', $ui->destination);
            $history = $this->countRows('history');
            $ui->handleRequest();
            self::assertSame($history, $this->countRows('history'));
        }
        $_GET = $_POST = array();
    }

    public function testApprovedCloseReasonsAndUnsupportedTaskActions(): void
    {
        foreach (array('already_handled', 'not_applicable', 'incorrect_warning', 'no_further_action') as $reason)
        {
            $until = (new \DateTimeImmutable(\DateUtility::getAdjustedDate('Y-m-d')))->modify('+30 days')->format('Y-m-d');
            $this->save($this->snapshot(), 'request-close-' . $reason, array(6 => $reason, 7 => $until));
            self::assertSame(1, $this->filtered()['suppressedReasonCount']);
        }
        $task = $this->commitment(-1);
        $snapshot = $this->reviews->get('W07', DATA_ITEM_COMPANY, $this->company, 'overdue_task', $task);
        foreach (array('close', 'remind_later', 'reset') as $action)
            $this->rejected(fn() => $this->save($snapshot, 'request-task-' . $action,
                array(5 => $action, 6 => $action === 'close' ? 'already_handled' : null,
                    7 => $action === 'reset' ? null : \WarningReviews::reminderDates()[1])));
        $job = $this->reviewJob();
        $snapshot = $this->reviews->get('W02', DATA_ITEM_JOBORDER, $job, 'no_recorded_submission');
        $this->save($snapshot, 'request-corrupt-job', array(5 => 'remind_later', 6 => null, 7 => \WarningReviews::reminderDates()[5]));
        $this->db->query("UPDATE warning_review SET action = 'close', close_reason = 'already_handled' WHERE warning_rule = 'W02'");
        self::assertSame(1, $this->reviews->getForSource(DATA_ITEM_JOBORDER, $job)['visibleCounts']['W02']);
        $this->db->query('UPDATE user SET access_level = 0 WHERE user_id = 1');
        self::assertSame(array(), $this->reviews->getForSource(DATA_ITEM_JOBORDER, $job)['reviews']);
        self::assertSame(array(), $this->reviews->getForSource(DATA_ITEM_COMPANY, $this->company)['reviews']);
    }

    public function testCorrectiveDefaultsNativeLinksAndReadOnlyTaskOpening(): void
    {
        require_once LEGACY_ROOT . '/lib/Template.php';
        require_once LEGACY_ROOT . '/lib/UserInterface.php';
        require_once LEGACY_ROOT . '/modules/tasks/TasksUI.php';
        $s = $this->snapshot();
        foreach (array(null, '') as $reason)
            $this->rejected(fn() => $this->save($s, 'request-missing-reason', array(6 => $reason)));
        $this->save($s);
        $states = $this->db->getAllAssoc('SELECT * FROM warning_review');
        $history = $this->countRows('history');
        $render = function ($type, $id) {
            $template = new \Template();
            $template->assign('recruitmentWarnings', $this->reviews->getForSource($type, $id));
            ob_start();
            try { $template->display($type === DATA_ITEM_COMPANY ? './modules/home/WarningReviewSource.tpl' : './modules/joborders/RecruitmentWarnings.tpl'); return ob_get_contents(); }
            finally { ob_end_clean(); }
        };
        $html = $render(DATA_ITEM_COMPANY, $this->company);
        self::assertStringContainsString('value="' . \WarningReviews::reminderDates()[5] . '" selected', $html);
        self::assertStringContainsString('<option value="" selected disabled>Choose a reason...</option>', $html);
        self::assertStringContainsString('m=tasks&amp;a=add&amp;dataItemType=' . DATA_ITEM_COMPANY . '&amp;dataItemID=' . $this->company, $html);
        self::assertStringContainsString('Open Company to log Contact activity', $html);
        self::assertStringContainsString('m=companies&amp;a=show&amp;companyID=' . $this->company, $html);
        foreach (array('tasks.add', 'tasks.show') as $permission)
        {
            $this->overrides[$permission] = ACCESS_LEVEL_DISABLED;
            self::assertStringNotContainsString('Add follow-up Task', $render(DATA_ITEM_COMPANY, $this->company));
            $this->overrides = array();
        }
        foreach (array('contacts.addActivityScheduleEvent', 'contacts.logActivityScheduleEvent') as $permission)
        {
            $this->overrides[$permission] = ACCESS_LEVEL_READ;
            self::assertStringNotContainsString('Open Company to log Contact activity', $render(DATA_ITEM_COMPANY, $this->company));
            $this->overrides = array();
        }
        $job = $this->reviewJob();
        self::assertStringContainsString('m=joborders&amp;a=show&amp;jobOrderID=' . $job, $render(DATA_ITEM_JOBORDER, $job));
        $this->overrides['joborders.show'] = ACCESS_LEVEL_DISABLED;
        self::assertStringNotContainsString('Open Job Order / Pipeline', $render(DATA_ITEM_JOBORDER, $job));
        $this->overrides = array();
        // Opening the existing linked Task Add form performs no review or History write.
        $ui = (new \ReflectionClass(\TasksUI::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(\UserInterface::class, '_template'))->setValue($ui, $this->createStub(\Template::class));
        (new \ReflectionProperty(\UserInterface::class, '_userID'))->setValue($ui, 1);
        $_GET = array('a' => 'add', 'dataItemType' => (string) DATA_ITEM_COMPANY, 'dataItemID' => (string) $this->company);
        $_POST = array();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $ui->handleRequest();
        $render(DATA_ITEM_COMPANY, $this->company); // Return without submitting the Task.
        self::assertSame($states, $this->db->getAllAssoc('SELECT * FROM warning_review'));
        self::assertSame($history, $this->countRows('history'));
        $_GET = $_POST = array();
    }

    public function testConcurrentFirstRequestsHaveOneWinner(): void
    {
        $this->concurrentRequests(false);
    }

    public function testConcurrentDuplicateRequestsAreIdempotent(): void
    {
        $this->concurrentRequests(true);
    }

    private function concurrentRequests($duplicate)
    {
        $s = $this->snapshot();
        $runner = tempnam(sys_get_temp_dir(), 'review-race-');
        file_put_contents($runner, <<<'RUNNER'
<?php
require 'vendor/autoload.php';
define('LEGACY_ROOT', '.');
define('DATABASE_NAME', 'cats_integrationtest');
define('DATABASE_HOST', 'integrationtestdb');
require 'config.php';
require 'constants.php';
require 'lib/DatabaseConnection.php';
require 'lib/CATSUtility.php';
require 'lib/WarningReviews.php';
$_SESSION['CATS'] = new class {
    public function getUserID() { return 1; }
    public function isLoggedIn() { return true; }
    public function getAccessLevel($key) { return ACCESS_LEVEL_SA; }
    public function getRealAccessLevel() { return ACCESS_LEVEL_SA; }
    public function getTimeZoneOffset() { return 0; }
    public function isDateDMY() { return false; }
    public function isTimeFormat24() { return false; }
};
$args = json_decode(trim(fgets(STDIN)), true);
echo "ready\n";
fflush(STDOUT);
fgets(STDIN);
try { echo 'saved:' . (new WarningReviews())->save(...$args) . "\n"; }
catch (RuntimeException $e) { echo 'rejected:' . $e->getMessage() . "\n"; }
RUNNER
        );
        $workers = array();
        try
        {
            self::assertTrue($this->db->beginTransaction());
            $this->db->getAssoc('SELECT user_id FROM user WHERE user_id = 1 FOR UPDATE');
            foreach (array('request-race-0001', $duplicate ? 'request-race-0001' : 'request-race-0002') as $request)
            {
                $pipes = array();
                $process = proc_open(array(PHP_BINARY, $runner), array(0 => array('pipe', 'r'),
                    1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
                self::assertIsResource($process);
                $args = array('W07', DATA_ITEM_COMPANY, $this->company, 'stale_relationship', 0,
                    'close', 'already_handled', date('Y-m-d', strtotime('+5 days')), 0, $s['evidence'], $request);
                fwrite($pipes[0], json_encode($args) . "\n");
                self::assertSame("ready\n", fgets($pipes[1]));
                $workers[] = array($process, $pipes);
            }
            foreach ($workers as [$process, $pipes]) { fwrite($pipes[0], "go\n"); fclose($pipes[0]); }
            // Observe both independent connections at the actual contested row.
            $deadline = microtime(true) + 5;
            do
            {
                $waiting = array_filter($this->db->getAllAssoc('SHOW PROCESSLIST'),
                    fn($row) => strpos($row['Info'] ?? '', 'SELECT access_level FROM user WHERE user_id = 1 FOR UPDATE') === 0);
                if (count($waiting) === 2) break;
                usleep(10000);
            } while (microtime(true) < $deadline);
            self::assertCount(2, $waiting);
            self::assertTrue($this->db->commitTransaction());
            $results = array();
            foreach ($workers as [$process, $pipes])
            {
                $results[] = trim(stream_get_contents($pipes[1]));
                fclose($pipes[1]);
                $errors = stream_get_contents($pipes[2]);
                fclose($pipes[2]);
                self::assertSame(0, proc_close($process), $errors);
            }
            sort($results);
            self::assertSame($duplicate ? array('saved:1', 'saved:1') :
                array('rejected:Warning review is stale. Reload the source.', 'saved:1'), $results);
            self::assertSame(1, $this->countRows('warning_review'));
            self::assertSame(1, $this->countRows('history'));
        }
        finally { $this->db->rollbackTransaction(); unlink($runner); }
    }

    public function testMigrationRetryAndBackupRoundTrip(): void
    {
        require_once LEGACY_ROOT . '/modules/install/Schema.php';
        require_once LEGACY_ROOT . '/modules/install/backupDB.php';
        $fresh = $this->db->getAssoc('SHOW CREATE TABLE warning_review')['Create Table'];
        $this->db->query('DROP TABLE warning_review');
        $db = $this->db;
        eval(substr(\CATSSchema::get()[402], 4));
        self::assertSame($fresh, $this->db->getAssoc('SHOW CREATE TABLE warning_review')['Create Table']);
        $this->save($this->snapshot());
        eval(substr(\CATSSchema::get()[402], 4));
        self::assertSame(1, $this->countRows('warning_review'));
        // Cross-module review history survives without broadening all legacy history.
        (new \History())->storeHistoryData(DATA_ITEM_JOBORDER, 1, 'warning_review', null, 'fixture', 'Review backup fixture');
        (new \History())->storeHistoryData(DATA_ITEM_COMPANY, 1, 'other', null, 'excluded', 'Unrelated history');
        $before = $this->snapshot();
        $file = tempnam(sys_get_temp_dir(), 'review-backup-');
        try
        {
            \dumpDB($this->db, $file);
            foreach (glob($file . '.*') as $part)
                foreach (explode('((ENDOFQUERY))', file_get_contents($part)) as $sql)
                    if (preg_match('/^(DROP TABLE IF EXISTS|CREATE TABLE|INSERT INTO) `(warning_review|history)`/', trim($sql)))
                        self::assertNotFalse($this->db->query(trim($sql)));
            self::assertSame($before, $this->snapshot());
            self::assertSame(2, $this->countRows('history'));
        }
        finally { foreach (glob($file . '*') as $part) unlink($part); }
        $this->db->query('ALTER TABLE warning_review DROP PRIMARY KEY');
        $this->rejected(function () use ($db) { eval(substr(\CATSSchema::get()[402], 4)); });
    }
}
