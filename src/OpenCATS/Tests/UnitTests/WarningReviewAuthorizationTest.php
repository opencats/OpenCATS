<?php
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class WarningReviewAuthorizationTest extends AuthorizationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once './modules/home/HomeUI.php';
        $_GET['a'] = 'reviewWarning';
        $_POST = array('csrfToken' => 'valid', 'warning_rule' => 'W02', 'data_item_type' => (string) DATA_ITEM_JOBORDER,
            'data_item_id' => '7', 'reason_key' => 'no_recorded_submission', 'reason_id' => '0',
            'expectedVersion' => '0', 'expectedEvidence' => str_repeat('a', 64),
            'requestID' => 'request-fixture-01', 'returnTo' => 'joborders', 'reviewAction' => 'remind_later',
            'reviewUntil' => '2026-10-20');
    }

    public function testGetUnauthenticatedAndInvalidCsrfAreRejectedBeforeWrites(): void
    {
        $ui = $this->ui(HomeUI::class);
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $ui->handleRequest());
        $this->loggedIn = true;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $ui->handleRequest());
        $_SERVER['REQUEST_METHOD'] = 'POST';
        unset($_POST['csrfToken']);
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $ui->handleRequest());
        foreach (array(null, array('valid'), 'invalid') as $token)
        {
            $_POST['csrfToken'] = $token;
            $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $ui->handleRequest());
        }
        self::assertSame(array(), $this->queries);
    }

    public function testInputShapesActionsAndRedirectsAreRejected(): void
    {
        $this->loggedIn = true;
        $this->session->method('isCSRFTokenValid')->willReturn(true);
        $ui = $this->ui(HomeUI::class);
        $valid = $_POST;
        foreach (array_keys($valid) as $field)
        {
            if ($field === 'csrfToken') continue;
            $_POST = array_replace($valid, array($field => array('value')));
            $this->assertCommonError(COMMONERROR_BADFIELDS, fn() => $ui->handleRequest());
        }
        foreach (array(array('returnTo' => 'https://example.com'), array('returnTo' => 'companies'),
            array('expectedVersion' => '-1'), array('expectedVersion' => '2147483647'),
            array('reviewAction' => 'close', 'closeReason' => 'already_handled'),
            array('reviewAction' => 'delete'), array('reason_key' => 'stale_relationship'),
            array('reviewing_user' => '2'), array('closeReason' => array('x'))) as $change)
        {
            $_POST = array_replace($valid, $change);
            $this->assertCommonError(COMMONERROR_BADFIELDS, fn() => $ui->handleRequest());
        }
        self::assertSame(array(), $this->queries);
    }

    public function testReadOnlySourceCannotBeReviewed(): void
    {
        $this->loggedIn = true;
        $this->session->method('isCSRFTokenValid')->willReturn(true);
        $this->accessLevel = ACCESS_LEVEL_READ;
        $this->db->method('beginTransaction')->willReturn(true);
        $this->db->method('getAssoc')->willReturn(array('access_level' => ACCESS_LEVEL_READ, 'joborder_id' => 7));
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $this->ui(HomeUI::class)->handleRequest());
        self::assertSame(array(), $this->queries);
    }

    public function testOversightPermissionAndFilterShapesDenyMetadata(): void
    {
        require_once './lib/WarningReviews.php';
        $reviews = new WarningReviews();
        $this->loggedIn = true;
        $this->accessOverrides['reports.show'] = ACCESS_LEVEL_READ;
        $reads = array();
        $this->db->method('getAllAssoc')->willReturnCallback(function ($sql) use (&$reads) { $reads[] = $sql; return array(); });
        try { $reviews->getOversight(); self::fail('Read-only Reports must not grant oversight.'); }
        catch (RuntimeException $e) { self::assertSame('Warning oversight denied.', $e->getMessage()); }
        self::assertSame(array(), $reads);
        require_once './modules/reports/ReportsUI.php';
        $_GET = array('a' => 'warningOversight');
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $this->ui(ReportsUI::class)->handleRequest());
        $this->accessOverrides['reports.show'] = ACCESS_LEVEL_SA;
        $this->db->method('getAssoc')->willReturn(array('access_level' => ACCESS_LEVEL_EDIT));
        $_GET['reviewer'] = array('10');
        $this->assertCommonError(COMMONERROR_BADFIELDS, fn() => $this->ui(ReportsUI::class)->handleRequest());
        self::assertSame(array(), $reads);
    }

    public function testOversightDistinctCountsVisibilityHistoryAndAdministrativeReset(): void
    {
        require_once './lib/WarningReviews.php';
        $this->loggedIn = true;
        $realAccess = ACCESS_LEVEL_READ;
        $this->session->method('getRealAccessLevel')->willReturnCallback(function () use (&$realAccess) { return $realAccess; });
        $this->accessOverrides = array('reports.show' => ACCESS_LEVEL_SA, 'companies.show' => ACCESS_LEVEL_DISABLED,
            'tasks.list' => ACCESS_LEVEL_DISABLED, 'tasks.show' => ACCESS_LEVEL_DISABLED);
        $reviews = new WarningReviews();
        $today = DateUtility::getAdjustedDate('Y-m-d');
        $clock = $today . ' 12:00:00';
        $states = array();
        $history = array();
        $reads = array();
        $this->db->method('beginTransaction')->willReturn(true);
        $this->db->method('commitTransaction')->willReturn(true);
        $this->db->method('getAssoc')->willReturnCallback(function ($sql) use (&$states, $clock) {
            if (str_contains($sql, 'FROM warning_review')) return $states[0] ?? array();
            if (str_contains($sql, 'SELECT NOW()')) return array('clock' => $clock);
            return array('access_level' => ACCESS_LEVEL_EDIT, 'joborder_id' => 7);
        });
        $this->db->method('getAllAssoc')->willReturnCallback(function ($sql) use (&$states, &$history, &$reads, $today) {
            $reads[] = $sql;
            if (str_contains($sql, 'j.recruitment_baseline AS actionable'))
            {
                self::assertStringContainsString('is_admin_hidden', $sql);
                if ($this->accessOverrides['joborders.show'] ?? false) return array();
                return array(array('jobOrderID' => 7, 'title' => 'Visible job', 'owner' => 10, 'recruiter' => 10,
                    'isHot' => 0, 'isAdminHidden' => 0, 'actionable' => date('Y-m-d', strtotime($today . ' -20 days')) . ' 09:00:00',
                    'submitted' => 0, 'progress' => null));
            }
            // Scope review state/audit reads, not the source query's reopening status History.
            if (str_contains($sql, 'FROM warning_review') ||
                (str_contains($sql, 'FROM history') && str_contains($sql, "the_field = 'warning_review'")))
            {
                self::assertStringContainsString('data_item_id IN (7)', $sql);
                return str_contains($sql, 'FROM warning_review') ? $states : $history;
            }
            return array();
        });
        $initial = $reviews->getOversight();
        self::assertCount(2, $initial['rows']);
        $row = $initial['rows'][0];
        self::assertSame('W02', $row['identity']['warning_rule']);
        $state = array_merge(array('reviewing_user' => 20), $row['identity'], array('action' => 'remind_later',
            'close_reason' => null, 'action_at' => $clock, 'review_until' => WarningReviews::reminderDates()[5],
            'evidence_hash' => $row['evidence'], 'request_hash' => str_repeat('b', 64), 'version' => 1));
        $states = array($state, array_replace($state, array('reviewing_user' => 21)),
            array_replace($state, array('data_item_id' => 999))); // Defensive rejection even if returned outside the SQL scope.
        $history = array(array('new_value' => json_encode($state)), array('new_value' => json_encode($state)),
            array('new_value' => json_encode(array_replace($state, array('data_item_id' => 999)))));
        $result = $reviews->getOversight();
        self::assertCount(3, $result['rows']);
        self::assertSame(2, $result['counts']['outstanding']);
        self::assertSame(1, $result['counts']['remind_later']);
        self::assertSame(2, $result['events']);
        self::assertCount(1, $reviews->getOversight('W02', 'remind_later', 20)['rows']);
        $args = array('W02', DATA_ITEM_JOBORDER, 7, 'no_recorded_submission', 0, 'reset', null, null,
            1, $row['evidence'], 'oversight-reset-01', 20);
        try { $reviews->save(...$args); self::fail('Ordinary manager must not reset another user.'); }
        catch (RuntimeException $e) { self::assertSame('Warning review reset denied.', $e->getMessage()); }
        self::assertSame(array(), $this->queries);
        $realAccess = ACCESS_LEVEL_SA;
        self::assertSame(2, $reviews->save(...$args));
        self::assertCount(2, $this->queries);
        self::assertStringContainsString('reviewing_user = 20', $this->queries[0]);
        self::assertStringContainsString('INSERT INTO history', $this->queries[1]);
        self::assertStringContainsString('10)', $this->queries[1]);
        $reads = array();
        $this->accessOverrides['joborders.show'] = ACCESS_LEVEL_DISABLED;
        $denied = $reviews->getOversight();
        self::assertSame(array(), $denied['rows']);
        self::assertSame(0, $denied['events']);
        self::assertSame(0, $denied['counts']['outstanding']);
        foreach ($reads as $sql) self::assertStringNotContainsString('FROM warning_review', $sql);
    }

    public function testOversightW07ReasonsExpiryAndHistoryStayIndependent(): void
    {
        require_once './lib/WarningReviews.php';
        $this->loggedIn = true;
        $this->accessOverrides['reports.show'] = ACCESS_LEVEL_SA;
        $this->accessOverrides['joborders.show'] = ACCESS_LEVEL_DISABLED;
        $today = DateUtility::getAdjustedDate('Y-m-d');
        $states = array();
        $tier = 'A';
        $nativeHistory = array();
        $this->db->method('getAssoc')->willReturn(array('access_level' => ACCESS_LEVEL_EDIT));
        $this->db->method('getAllAssoc')->willReturnCallback(function ($sql) use (&$states, &$nativeHistory, &$tier) {
            if (str_contains($sql, 'FROM company
'))
            {
                self::assertTrue(str_contains($sql, 'company.owner = 10') || str_contains($sql, 'WHERE company.company_id = 7'));
                return array(array('companyID' => 7, 'name' => 'Visible Company', 'owner' => 10,
                    'commercialTier' => $tier, 'relationshipStatus' => 'Client'));
            }
            if (str_contains($sql, 'FROM warning_review')) return $states;
            if (str_contains($sql, 'history_id AS historyID')) return $nativeHistory;
            return array();
        });
        $reviews = new WarningReviews();
        $initial = $reviews->getOversight('W07');
        self::assertCount(2, $initial['rows']);
        $next = $initial['rows'][0];
        $stale = $initial['rows'][1];
        self::assertSame('no_next_action', $next['identity']['reason_key']);
        $closed = array_merge(array('reviewing_user' => 20), $next['identity'], array('action' => 'close',
            'close_reason' => 'already_handled', 'action_at' => $today . ' 09:00:00',
            'review_until' => date('Y-m-d', strtotime($today . ' +5 days')), 'evidence_hash' => $next['evidence'], 'version' => 1));
        $expired = array_merge(array('reviewing_user' => 20), $stale['identity'], array('action' => 'remind_later',
            'close_reason' => null, 'action_at' => $today . ' 09:00:00', 'review_until' => $today,
            'evidence_hash' => $stale['evidence'], 'version' => 1));
        $states = array($closed, array_replace($closed, array('reviewing_user' => 21)), $expired);
        $result = $reviews->getOversight('W07');
        self::assertSame(array('outstanding' => 2, 'remind_later' => 0, 'close' => 1, 'reappeared' => 1), $result['counts']);
        self::assertCount(1, $reviews->getOversight('W07', 'reappeared')['rows']);
        $nativeHistory = array(array('theField' => 'warning_review', 'newValue' => json_encode($closed),
            'dateModified' => '13-10-26 09:00', 'enteredByFullName' => 'Administrator'),
            array('theField' => 'warning_review', 'newValue' => json_encode($expired)),
            array('theField' => 'warning_review', 'newValue' => json_encode(array_replace($closed, array('reviewing_user' => 21)))));
        $history = $reviews->getOversightHistory($next['identity'], 20);
        self::assertCount(1, $history);
        self::assertSame('close', $history[0]['action']);
        self::assertSame('Administrator', $history[0]['actor']);
        $tier = 'B';
        self::assertSame(0, $reviews->getOversight('W07')['counts']['close']);
        self::assertSame(2, $reviews->getOversight('W07')['counts']['reappeared']);
        $this->accessOverrides['companies.show'] = ACCESS_LEVEL_DISABLED;
        try { $reviews->getOversightHistory($next['identity'], 20); self::fail('Denied source must not expose history.'); }
        catch (RuntimeException $e) { self::assertSame('Warning source unavailable.', $e->getMessage()); }
    }

    public function testClosePersistedDeadlineAcrossTimezonesAndMalformedState(): void
    {
        require_once './lib/WarningReviews.php';
        $offset = 0;
        $this->session->method('getTimeZoneOffset')->willReturnCallback(function () use (&$offset) { return $offset; });
        $reviews = new WarningReviews();
        $suppresses = new ReflectionMethod(WarningReviews::class, 'suppresses');
        // Existing revision-402 rows have no saved timezone. Their validated date
        // remains authoritative at midnight and on the UK clock-change dates.
        foreach (array(array('2026-01-01 00:30:00', '2026-01-31'),
            array('2026-03-28 23:30:00', '2026-04-28'),
            array('2026-03-29 00:30:00', '2026-04-28'),
            array('2026-10-25 00:30:00', '2026-11-24')) as [$actionAt, $until])
        {
            $state = array('action' => 'close', 'close_reason' => 'already_handled', 'version' => 7,
                'evidence_hash' => str_repeat('a', 64), 'action_at' => $actionAt, 'review_until' => $until);
            foreach (array(-5, 0, 1, 14) as $offset)
            {
                self::assertTrue($suppresses->invoke($reviews, $state, date('Y-m-d', strtotime($until . ' -1 day'))));
                self::assertFalse($suppresses->invoke($reviews, $state, $until));
                self::assertSame($until, $state['review_until']);
            }
        }
        $offset = 0;
        foreach (array(array('review_until' => '2026-02-30'), array('review_until' => '0000-00-00'),
            array('review_until' => array('2026-11-24')), array('review_until' => '2026-11-24 12:00:00'),
            array('action_at' => '2026-02-30 00:00:00'), array('close_reason' => 'free text'),
            array('version' => 0), array('evidence_hash' => 'invalid')) as $change)
            self::assertFalse($suppresses->invoke($reviews, array_replace($state, $change), '2026-10-26'));
        self::assertTrue($suppresses->invoke($reviews, array_replace($state, array('action' => 'remind_later', 'close_reason' => null)), '2026-10-26'));
    }

    public function testCloseSaveRejectsExcessiveDeadlinesAndStaleEvidence(): void
    {
        require_once './lib/WarningReviews.php';
        $this->loggedIn = true;
        $this->db->method('beginTransaction')->willReturn(true);
        $this->db->method('commitTransaction')->willReturn(true);
        $today = DateUtility::getAdjustedDate('Y-m-d');
        $this->db->method('getAssoc')->willReturnCallback(function ($sql) use ($today) {
            if (str_contains($sql, 'FROM warning_review')) return array();
            if (str_contains($sql, 'SELECT NOW()')) return array('clock' => $today . ' 12:00:00');
            return array('access_level' => ACCESS_LEVEL_EDIT, 'company_id' => 7);
        });
        $this->db->method('getAllAssoc')->willReturnCallback(function ($sql) {
            if (str_contains($sql, 'FROM company' . "\n"))
                return array(array('companyID' => 7, 'name' => 'Company', 'owner' => 10,
                    'commercialTier' => 'A', 'relationshipStatus' => 'Client'));
            return array();
        });
        $reviews = new WarningReviews();
        $snapshot = $reviews->get('W07', DATA_ITEM_COMPANY, 7, 'no_next_action');
        $args = array('W07', DATA_ITEM_COMPANY, 7, 'no_next_action', 0, 'close', 'already_handled',
            date('Y-m-d', strtotime($today . ' +30 days')), 0, $snapshot['evidence'], 'close-boundary-01');
        foreach (array(date('Y-m-d', strtotime($today . ' +31 days')),
            date('Y-m-d', strtotime($today . ' +60 days')), $today, '2026-02-30', array('date')) as $until)
        {
            try { $reviews->save(...array_replace($args, array(7 => $until))); self::fail('Invalid Close must be rejected.'); }
            catch (InvalidArgumentException $e) { self::assertNotEmpty($e->getMessage()); }
        }
        foreach (array(array(8 => 1), array(9 => str_repeat('b', 64))) as $change)
        {
            try { $reviews->save(...array_replace($args, $change)); self::fail('Stale Close must be rejected.'); }
            catch (RuntimeException $e) { self::assertSame('Warning review is stale. Reload the source.', $e->getMessage()); }
        }
        self::assertSame(array(), $this->queries);
        self::assertSame(1, $reviews->save(...$args));
        self::assertCount(2, $this->queries);
        self::assertStringContainsString($args[7], $this->queries[0]);
        self::assertStringContainsString('INSERT INTO history', $this->queries[1]);
    }

    public function testSelectedCompanyOversightKeepsNativeAccessAndOwnerQueue(): void
    {
        require_once './lib/WarningReviews.php';
        $this->loggedIn = true;
        $this->accessOverrides['reports.show'] = ACCESS_LEVEL_SA;
        $realAccess = ACCESS_LEVEL_READ;
        $this->session->method('getRealAccessLevel')->willReturnCallback(function () use (&$realAccess) { return $realAccess; });
        $states = array();
        $reads = array();
        $this->db->method('beginTransaction')->willReturn(true);
        $this->db->method('commitTransaction')->willReturn(true);
        $this->db->method('getAssoc')->willReturnCallback(function ($sql) use (&$states) {
            if (str_contains($sql, 'candidate_joborder')) return array('candidateID' => 99, 'isAdminHidden' => 1, 'isJobOrderAdminHidden' => 0);
            if (str_contains($sql, 'FROM warning_review')) return $states[0] ?? array();
            if (str_contains($sql, 'SELECT NOW()')) return array('clock' => date('Y-m-d H:i:s'));
            return array('access_level' => ACCESS_LEVEL_EDIT, 'company_id' => 7);
        });
        $this->db->method('getAllAssoc')->willReturnCallback(function ($sql) use (&$states, &$reads) {
            $reads[] = $sql;
            if (str_contains($sql, 'FROM company' . "\n"))
            {
                if (str_contains($sql, 'company.owner = 10')) return array();
                self::assertStringContainsString('WHERE company.company_id = 7', $sql);
                return array(array('companyID' => 7, 'name' => 'Other recruiter Company', 'owner' => 20,
                    'commercialTier' => 'A', 'relationshipStatus' => 'Client'));
            }
            if (str_contains($sql, 'FROM task'))
                return array(array('taskID' => 901, 'title' => 'Private Task secret', 'dataItemType' => null, 'dataItemID' => null,
                    'createdBy' => 20, 'assignedTo' => 20, 'candidateJobOrderID' => null),
                    array('taskID' => 902, 'title' => 'Restricted pipeline Task secret', 'dataItemType' => DATA_ITEM_COMPANY,
                        'dataItemID' => 7, 'createdBy' => 20, 'assignedTo' => 20, 'candidateJobOrderID' => 998));
            if (str_contains($sql, 'FROM warning_review')) return $states;
            return array(); // No permitted pipeline/candidate evidence.
        });
        $reviews = new WarningReviews();
        self::assertSame(array(), (new Tasks())->getCompanyRecruitmentWarnings(7));
        try { (new Companies())->getRelationshipAttention(null, true); self::fail('No unrestricted oversight enumeration.'); }
        catch (InvalidArgumentException $e) { self::assertSame('Oversight requires a Company ID.', $e->getMessage()); }
        $result = $reviews->getOversight('W07', '', 0, 7);
        self::assertCount(2, $result['rows']);
        self::assertSame(20, $result['rows'][0]['warning']['responsibleUserID']);
        self::assertStringNotContainsString('Task secret', json_encode($result));
        $row = $result['rows'][0];
        $states = array(array_merge(array('reviewing_user' => 20), $row['identity'], array('action' => 'close',
            'close_reason' => 'already_handled', 'action_at' => date('Y-m-d H:i:s'),
            'review_until' => date('Y-m-d', strtotime('+5 days')), 'evidence_hash' => $row['evidence'], 'request_hash' => str_repeat('c', 64), 'version' => 1)));
        self::assertSame(1, $reviews->getOversight('W07', '', 0, 7)['counts']['close']);
        $args = array('W07', DATA_ITEM_COMPANY, 7, 'no_next_action', 0, 'reset', null, null, 1,
            $row['evidence'], 'selected-reset-01', 20);
        try { $reviews->save(...$args); self::fail('Ordinary manager cannot reset.'); }
        catch (RuntimeException $e) { self::assertSame('Warning review reset denied.', $e->getMessage()); }
        $realAccess = ACCESS_LEVEL_SA;
        $this->session->method('isCSRFTokenValid')->willReturn(true);
        $ui = new class extends HomeUI {
            public $destination;
            public function __construct() {}
            protected function redirectWarningReview($uri) { $this->destination = $uri; }
        };
        $_POST = array_map('strval', array_merge($row['identity'], array('csrfToken' => 'valid',
            'reviewing_user' => 20, 'expectedVersion' => 1, 'expectedEvidence' => $row['evidence'],
            'requestID' => 'selected-reset-01', 'returnTo' => 'reports', 'reviewAction' => 'reset')));
        $ui->handleRequest();
        self::assertSame('m=reports&a=warningOversight&companyID=7&warningReview=restored', $ui->destination);
        self::assertCount(2, $this->queries);
        self::assertStringContainsString('reviewing_user = 20', $this->queries[0]);
        self::assertStringContainsString('INSERT INTO history', $this->queries[1]);
        self::assertStringContainsString('10)', $this->queries[1]);
        foreach (array(array(8 => 0), array(9 => str_repeat('b', 64))) as $change)
        {
            try { $reviews->save(...array_replace($args, $change)); self::fail('Stale reset cannot write.'); }
            catch (RuntimeException $e) { self::assertSame('Warning review is stale. Reload the source.', $e->getMessage()); }
        }
        self::assertCount(2, $this->queries);
        foreach (array('companies.show', 'contacts.show', 'tasks.show', 'activity.listByViewDataGrid') as $permission)
        {
            $this->accessOverrides[$permission] = ACCESS_LEVEL_DISABLED;
            $reads = array();
            $denied = $reviews->getOversight('W07', '', 0, 7);
            self::assertSame(array(), $denied['rows']);
            self::assertSame(0, $denied['events']);
            self::assertSame(0, $denied['counts']['outstanding']);
            foreach ($reads as $sql) self::assertStringNotContainsString('FROM warning_review', $sql);
            unset($this->accessOverrides[$permission]);
        }
        $this->accessOverrides['reports.show'] = ACCESS_LEVEL_READ;
        self::assertSame(array(), (new Tasks())->getCompanyRecruitmentWarnings(7, true));
    }

    public function testOrdinaryManagerCanReceiveOnlyReportsAclGrant(): void
    {
        require_once './lib/ACL.php';
        if (!class_exists('ACL_SETUP'))
        {
            eval('class ACL_SETUP { public static $ACCESS_LEVEL_MAP = array(); }');
        }
        ACL_SETUP::$ACCESS_LEVEL_MAP = array('oversight-fixture' => array('reports.show' => ACCESS_LEVEL_SA));
        self::assertSame(ACCESS_LEVEL_SA, ACL::getAccessLevel('reports.show', array('oversight-fixture'), ACCESS_LEVEL_READ));
        self::assertSame(ACCESS_LEVEL_READ, ACL::getAccessLevel('settings.administration', array('oversight-fixture'), ACCESS_LEVEL_READ));
        self::assertSame(ACCESS_LEVEL_READ, ACL::getAccessLevel('companies.edit', array('oversight-fixture'), ACCESS_LEVEL_READ));
    }
}
