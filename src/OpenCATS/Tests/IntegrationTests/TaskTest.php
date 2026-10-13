<?php
namespace OpenCATS\Tests\IntegrationTests;

class TaskTest extends DatabaseTestCase
{
    private $db;
    private $tasks;
    private $actor = 1;
    private $access = 400;
    private $overrides = array();
    private $loggedIn = true;
    private $parents;
    private $assignee;
    private $pipeline;

    protected function setUp(): void
    {
        parent::setUp();
        require_once LEGACY_ROOT . '/lib/CATSUtility.php';
        require_once LEGACY_ROOT . '/lib/Session.php';
        require_once LEGACY_ROOT . '/lib/Tasks.php';
        $this->actor = 1;
        $this->access = ACCESS_LEVEL_SA;
        $this->overrides = array();
        $this->loggedIn = true;
        $session = $this->createStub(\CATSSession::class);
        $session->method('getUserID')->willReturnCallback(fn() => $this->actor);
        $session->method('getAccessLevel')->willReturnCallback(fn($key) => $this->overrides[$key] ?? $this->access);
        $session->method('getRealAccessLevel')->willReturnCallback(fn() => $this->access);
        $session->method('isLoggedIn')->willReturnCallback(fn() => $this->loggedIn);
        $session->method('getTimeZoneOffset')->willReturn(13);
        $session->method('isDateDMY')->willReturn(false);
        $session->method('isTimeFormat24')->willReturn(false);
        $_SESSION['CATS'] = $session;
        $this->db = \DatabaseConnection::getInstance();
        $this->tasks = new \Tasks();
        $this->db->query("INSERT INTO user (user_name, first_name, last_name, access_level) VALUES ('task-assignee', 'Task', 'Assignee', 200)");
        $this->assignee = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO company (name, entered_by, owner) VALUES ('Task Company', 1, 1)");
        $company = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO contact (company_id, company_department_id, first_name, last_name, entered_by, owner) VALUES ($company, -1, 'Task', 'Contact', 1, 1)");
        $contact = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO candidate (first_name, last_name, entered_by, owner) VALUES ('Task', 'Candidate', 1, 1)");
        $candidate = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO joborder (company_id, title, entered_by, owner) VALUES ($company, 'Task Job', 1, 1)");
        $job = (int) $this->db->getLastInsertID();
        $this->parents = array(DATA_ITEM_COMPANY => $company, DATA_ITEM_CONTACT => $contact,
            DATA_ITEM_CANDIDATE => $candidate, DATA_ITEM_JOBORDER => $job);
        (new \Pipelines())->add($candidate, $job, 1);
        $this->pipeline = (int) $this->db->getAssoc('SELECT candidate_joborder_id FROM candidate_joborder')['candidate_joborder_id'];
    }

    protected function tearDown(): void
    {
        $this->db->query('SET timestamp = DEFAULT');
        unset($_SESSION['CATS']);
        (new \ReflectionProperty(\DatabaseConnection::class, '_instance'))->setValue(null, null);
        parent::tearDown();
    }

    private function warningRows($code)
    {
        return array_values(array_filter($this->tasks->getRecruitmentWarnings()['warnings'],
            fn($row) => $row['code'] === $code));
    }

    public function testOverdueWarningUsesCalendarDueDatesAndCurrentState(): void
    {
        $today = \DateUtility::getAdjustedDate('Y-m-d');
        $yesterday = (new \DateTimeImmutable($today))->modify('-1 day')->format('Y-m-d');
        $id = $this->linked(DATA_ITEM_COMPANY, array('dueDate' => $yesterday, 'assignedTo' => $this->assignee));
        $progress = $this->linked(DATA_ITEM_COMPANY, array('dueDate' => $yesterday, 'status' => 'in_progress'));
        $this->linked(DATA_ITEM_COMPANY, array('dueDate' => $today));
        $this->linked(DATA_ITEM_COMPANY);
        foreach (array('completed', 'cancelled') as $status)
            $this->linked(DATA_ITEM_COMPANY, array('dueDate' => $yesterday, 'status' => $status));
        $invalid = $this->linked(DATA_ITEM_COMPANY);
        $this->db->query("UPDATE task SET due_date = '0000-00-00' WHERE task_id = $invalid");
        $rows = $this->warningRows('W01');
        self::assertSame(array($id, $progress), array_column($rows, 'entityID'));
        self::assertSame(1, $rows[0]['ageDays']);
        self::assertSame($yesterday, $rows[0]['sourceDate']);
        self::assertSame('overdue_follow_up', $rows[0]['coverage']);
        $this->tasks->update($id, array('assignedTo' => 1));
        self::assertEquals(1, $this->warningRows('W01')[0]['responsibleUserID']);
        $this->tasks->update($id, array('dueDate' => $today));
        self::assertCount(1, $this->warningRows('W01'));
        $this->tasks->update($progress, array('status' => 'completed'));
        self::assertSame(array(), $this->warningRows('W01'));
        $result = $this->tasks->getRecruitmentWarnings();
        self::assertSame(array(), $result['disabledRules']);
        self::assertSame(0, $result['counts']['W02']);
        self::assertSame(0, $result['counts']['W03']);
    }

    private function warningJob($clock = '2026-10-19 15:30:01')
    {
        $job = $this->parents[DATA_ITEM_JOBORDER];
        $this->db->query("SET timestamp = UNIX_TIMESTAMP('$clock')");
        $this->db->query("UPDATE joborder SET status = 'Active', owner = 1, recruiter = 1,
            first_actionable_at = '2026-10-09 15:30:00' WHERE joborder_id = $job");
        return $job;
    }

    private function milestone($status, $date, $candidate = null)
    {
        $job = $this->parents[DATA_ITEM_JOBORDER];
        $candidate = $candidate ?? $this->parents[DATA_ITEM_CANDIDATE];
        $this->db->query("INSERT INTO candidate_joborder_status_history (joborder_id, candidate_id, status_from, status_to, date)
            VALUES ($job, $candidate, 100, $status, '$date')");
    }

    public function testRecruitmentWarningBoundariesAndRecordedMilestones(): void
    {
        $job = $this->warningJob();
        foreach (array('2026-10-10 15:30:00' => array(0, 0), '2026-10-12 15:30:00' => array(0, 0),
            '2026-10-13 15:30:00' => array(0, 0), '2026-10-13 15:30:01' => array(1, 0),
            '2026-10-16 15:30:00' => array(1, 0), '2026-10-16 15:30:01' => array(1, 1)) as $clock => $counts)
        {
            $this->db->query("SET timestamp = UNIX_TIMESTAMP('$clock')");
            $result = $this->tasks->getRecruitmentWarnings();
            self::assertSame($counts, array($result['counts']['W02'], $result['counts']['W03']), $clock);
            self::assertSame($counts[0] ? 1 : 0, $result['jobsAtRisk']);
        }
        $this->milestone(400, '2026-10-09 15:29:59');
        self::assertCount(1, $this->warningRows('W02'));
        $this->milestone(400, '2026-10-09 15:30:00');
        self::assertSame(array(), $this->warningRows('W02')); // Same-second evidence qualifies.
        self::assertCount(1, $this->warningRows('W03'));
        foreach (array(400, 500, 600, 800) as $status)
        {
            $this->db->query('DELETE FROM candidate_joborder_status_history');
            $this->milestone($status, '2026-10-16 15:30:00');
            self::assertSame(array(), $this->warningRows('W03'));
            self::assertCount($status === 400 ? 0 : 1, $this->warningRows('W02'));
        }
        $this->db->query('DELETE FROM candidate_joborder_status_history');
        foreach (array(100, 200, 300, 650, 675, 700) as $status) $this->milestone($status, '2026-10-16 15:30:00');
        self::assertCount(1, $this->warningRows('W03'));
        $pipeline = new \Pipelines();
        $candidate = $this->parents[DATA_ITEM_CANDIDATE];
        foreach (array(400, 100, 400) as $status) $pipeline->setStatus($candidate, $job, $status, '', '');
        self::assertSame(array(), $this->warningRows('W03'));
        self::assertSame(array(), $this->warningRows('W02'));
        $pipeline->setStatus($candidate, $job, 700, '', '');
        self::assertSame(array(), $this->warningRows('W02')); // Decline does not erase submission.
    }

    public function testRecruitmentWarningUnknownEligibilityAndAccess(): void
    {
        $job = $this->warningJob();
        foreach (array('On Hold', 'Closed', 'Canceled', 'Full', 'Upcoming') as $status)
        {
            $this->db->query("UPDATE joborder SET status = '$status' WHERE joborder_id = $job");
            self::assertSame(array(), $this->tasks->getJobRecruitmentWarnings());
        }
        $this->db->query("UPDATE joborder SET status = 'Active' WHERE joborder_id = $job");
        self::assertCount(2, $this->tasks->getJobRecruitmentWarnings()); // Reopened, no reset.
        foreach (array('NULL', "'2027-01-01 00:00:00'", "'0000-00-00 00:00:00'") as $value)
        {
            $this->db->query("UPDATE joborder SET first_actionable_at = $value WHERE joborder_id = $job");
            self::assertSame(array(), $this->tasks->getJobRecruitmentWarnings());
        }
        $this->warningJob();
        $this->db->query("UPDATE joborder SET is_speculative = 1 WHERE joborder_id = $job");
        self::assertSame(array(), $this->tasks->getJobRecruitmentWarnings());
        $this->db->query("UPDATE joborder SET is_speculative = 0 WHERE joborder_id = $job");
        foreach (array('2027-01-01 00:00:00', '0000-00-00 00:00:00') as $date)
        {
            $this->milestone(500, $date);
            self::assertSame(array(), $this->tasks->getJobRecruitmentWarnings());
            $this->db->query('DELETE FROM candidate_joborder_status_history');
        }
        foreach (array('joborders.list', 'joborders.show', 'candidates.show') as $permission)
        {
            $this->overrides = array($permission => ACCESS_LEVEL_DISABLED);
            self::assertSame(array(), $this->tasks->getJobRecruitmentWarnings());
        }
        $this->overrides = array('joborders.hidden' => ACCESS_LEVEL_READ);
        $this->db->query("UPDATE joborder SET is_admin_hidden = 1 WHERE joborder_id = $job");
        self::assertSame(array(), $this->tasks->getJobRecruitmentWarnings());
        $this->overrides = array('candidates.hidden' => ACCESS_LEVEL_READ);
        $candidate = $this->parents[DATA_ITEM_CANDIDATE];
        $this->db->query("UPDATE candidate SET is_admin_hidden = 1 WHERE candidate_id = $candidate");
        self::assertSame(array(), $this->tasks->getJobRecruitmentWarnings());
        $this->db->query('DELETE FROM candidate_joborder');
        $this->milestone(500, '2026-10-12 15:30:00');
        self::assertSame(array(), $this->tasks->getJobRecruitmentWarnings()); // History-only hidden candidate.
        $this->overrides = array();
        self::assertCount(2, $this->tasks->getJobRecruitmentWarnings());
    }

    public function testRecruitmentCoverageAndLegacyReviewsDoNotSuppress(): void
    {
        $job = $this->warningJob();
        $jobs = new \JobOrders();
        $task = $this->linked(DATA_ITEM_JOBORDER, array('purpose' => 'general', 'assignedTo' => 1));
        self::assertSame('uncovered', $this->warningRows('W02')[0]['coverage']);
        $this->tasks->update($task, array('purpose' => 'job_follow_up'));
        self::assertSame('covered', $this->warningRows('W02')[0]['coverage']);
        $this->tasks->update($task, array('status' => 'completed'));
        self::assertSame('uncovered', $this->warningRows('W03')[0]['coverage']);
        // Retained legacy columns must no longer influence authoritative eligibility.
        $this->db->query("UPDATE joborder SET w02_reviewed_at = NOW(), w03_reviewed_at = NOW() WHERE joborder_id = $job");
        self::assertCount(2, $this->tasks->getJobRecruitmentWarnings());
        self::assertSame(1, $this->tasks->getRecruitmentWarnings()['jobsAtRisk']);
        self::assertSame('completed', $this->tasks->get($task)['status']);
        self::assertEquals(0, $this->scalar('SELECT COUNT(*) FROM candidate_joborder_status_history'));
    }

    public function testLegacyRecruitmentReviewMigrationRetainsColumnsAndData(): void
    {
        $job = $this->warningJob();
        $jobs = new \JobOrders();
        require_once LEGACY_ROOT . '/modules/install/Schema.php';
        $db = $this->db;
        $db->query("UPDATE joborder SET w02_reviewed_at = NOW() WHERE joborder_id = $job");
        $stamp = $jobs->get($job)['w02ReviewedAt'];
        $fresh = $db->getAllAssoc("SHOW COLUMNS FROM joborder WHERE Field IN ('w02_reviewed_at', 'w03_reviewed_at')");
        $db->query('ALTER TABLE joborder DROP COLUMN w03_reviewed_at');
        eval(substr(\CATSSchema::get()[401], 4));
        eval(substr(\CATSSchema::get()[401], 4));
        self::assertSame($stamp, $jobs->get($job)['w02ReviewedAt']);
        self::assertSame($fresh, $db->getAllAssoc("SHOW COLUMNS FROM joborder WHERE Field IN ('w02_reviewed_at', 'w03_reviewed_at')"));
        $db->query('ALTER TABLE joborder DROP COLUMN w02_reviewed_at, DROP COLUMN w03_reviewed_at');
        eval(substr(\CATSSchema::get()[401], 4));
        self::assertSame($fresh, $db->getAllAssoc("SHOW COLUMNS FROM joborder WHERE Field IN ('w02_reviewed_at', 'w03_reviewed_at')"));
        self::assertNull($jobs->get($job)['w02ReviewedAt']);
    }

    public function testRecruitmentWarningBatchesDeduplicateJobs(): void
    {
        $job = $this->warningJob();
        $values = array_fill(0, 205, "('Batch Job', 'Active', 1, 1, '2026-10-09 15:30:00')");
        $this->db->query('INSERT INTO joborder (title, status, owner, recruiter, first_actionable_at) VALUES ' . implode(',', $values));
        $this->milestone(500, '2026-10-09 15:30:00');
        $this->milestone(500, '2026-10-09 15:30:00');
        $candidate = $this->parents[DATA_ITEM_CANDIDATE];
        $this->db->query("INSERT INTO candidate (first_name, last_name, entered_by, owner) VALUES ('Second', 'Pipeline', 1, 1)");
        $second = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO candidate_joborder (joborder_id, candidate_id, status)
            SELECT joborder_id, $second, 500 FROM joborder WHERE first_actionable_at IS NOT NULL");
        $this->db->query("INSERT INTO candidate_joborder_status_history (joborder_id, candidate_id, status_from, status_to, date)
            SELECT joborder_id, $second, 100, 500, first_actionable_at FROM joborder
            CROSS JOIN (SELECT 1 AS n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5) repeats
            WHERE first_actionable_at IS NOT NULL");

        $queries = array();
        $proxy = $this->createStub(\DatabaseConnection::class);
        $proxy->method('getAllAssoc')->willReturnCallback(function ($sql) use (&$queries) {
            if (str_starts_with($sql, 'SELECT j.joborder_id')) $queries[] = $sql;
            return $this->db->getAllAssoc($sql);
        });
        $proxy->method('getAssoc')->willReturnCallback(fn($sql) => $this->db->getAssoc($sql));
        $proxy->method('makeQueryString')->willReturnCallback(fn($value) => $this->db->makeQueryString($value));
        (new \ReflectionProperty(\Tasks::class, '_db'))->setValue($this->tasks, $proxy);
        $before = $this->db->getAssoc("SHOW SESSION STATUS LIKE 'Questions'")['Value'];
        $result = $this->tasks->getRecruitmentWarnings();
        $after = $this->db->getAssoc("SHOW SESSION STATUS LIKE 'Questions'")['Value'];
        self::assertLessThan(25, (int) $after - (int) $before);
        self::assertSame(206, $result['counts']['W02']);
        self::assertSame(206, $result['counts']['W03']);
        self::assertSame(206, $result['jobsAtRisk']);
        self::assertCount(412, array_unique(array_column($result['warnings'], 'identity')));
        self::assertSame('W02:joborder:' . $job, $result['warnings'][0]['identity']);
        self::assertCount(2, $queries);
        $plan = $this->db->getAllAssoc('EXPLAIN ' . $queries[0]);
        self::assertNotEmpty($plan);
        file_put_contents('/tmp/stage4br-warning-explain-' . PHP_VERSION . '.json', json_encode(array('questions' => (int) $after - (int) $before, 'plan' => $plan), JSON_PRETTY_PRINT));

    }

    public function testUnassignedWarningSeparatesResponsibilitiesAndDeduplicatesJobs(): void
    {
        $job = $this->parents[DATA_ITEM_JOBORDER];
        $this->db->query("UPDATE joborder SET status = 'Active', is_speculative = 1, owner = -1, recruiter = 999999 WHERE joborder_id = $job");
        $task = $this->linked(DATA_ITEM_JOBORDER, array('purpose' => 'job_follow_up'));
        $rows = $this->warningRows('W08');
        self::assertCount(3, $rows);
        self::assertSame(array('invalid_owner', 'invalid_recruiter', 'invalid_assignee'), array_column($rows, 'reason'));
        self::assertSame(1, $this->tasks->getRecruitmentWarnings()['jobsAtRisk']);
        self::assertSame(array('uncovered'), array_values(array_unique(array_column($rows, 'coverage'))));
        $this->tasks->update($task, array('status' => 'completed'));
        self::assertCount(2, $this->warningRows('W08')); // Completion cannot repair the Job.
        foreach (array('On Hold', 'Full', 'Closed', 'Canceled', 'Upcoming', 'Lead') as $status)
        {
            $this->db->query("UPDATE joborder SET status = '$status' WHERE joborder_id = $job");
            self::assertSame(array(), $this->warningRows('W08'));
        }
        $this->db->query("UPDATE joborder SET status = 'Active', owner = 1, recruiter = {$this->assignee} WHERE joborder_id = $job");
        self::assertSame(array(), $this->warningRows('W08'));
        $this->db->query("UPDATE user SET access_level = 0 WHERE user_id = {$this->assignee}");
        self::assertSame('invalid_recruiter', $this->warningRows('W08')[0]['reason']);
        self::assertEquals($this->assignee, $this->scalar("SELECT recruiter FROM joborder WHERE joborder_id = $job"));
        $this->db->query("UPDATE task SET status = 'open', assigned_to = {$this->assignee} WHERE task_id = $task");
        self::assertCount(2, $this->warningRows('W08'));
        $this->access = ACCESS_LEVEL_EDIT;
        self::assertSame(array(), $this->warningRows('W08'));
    }

    public function testUnassignedJobWarningsRequireListPermission(): void
    {
        $job = $this->parents[DATA_ITEM_JOBORDER];
        $this->db->query("UPDATE joborder SET status = 'Active', owner = -1, recruiter = -1 WHERE joborder_id = $job");
        $jobs = new \JobOrders();
        self::assertCount(2, $jobs->getUnassignedWorkWarnings());
        $this->overrides['joborders.list'] = ACCESS_LEVEL_DISABLED;
        self::assertGreaterThanOrEqual(ACCESS_LEVEL_READ, $_SESSION['CATS']->getAccessLevel('joborders.show'));
        self::assertGreaterThanOrEqual(ACCESS_LEVEL_EDIT, $_SESSION['CATS']->getAccessLevel('joborders.edit'));
        self::assertSame(array(), $jobs->getUnassignedWorkWarnings());
        $result = $this->tasks->getRecruitmentWarnings();
        self::assertSame(0, $result['counts']['W08']);
        self::assertSame(0, $result['jobsAtRisk']);
    }

    public function testWarningRowsAndCountsRespectParentPipelineAndManagerPermissions(): void
    {
        $job = $this->parents[DATA_ITEM_JOBORDER];
        $candidate = $this->parents[DATA_ITEM_CANDIDATE];
        $due = (new \DateTimeImmutable(\DateUtility::getAdjustedDate('Y-m-d')))->modify('-2 days')->format('Y-m-d');
        $id = $this->linked(DATA_ITEM_CANDIDATE, array('dueDate' => $due, 'assignedTo' => 1, 'candidateJobOrderID' => $this->pipeline));
        self::assertSame(1, $this->tasks->getRecruitmentWarnings()['jobsAtRisk']);
        $this->db->query("UPDATE joborder SET is_admin_hidden = 1, status = 'Active', recruiter = -1 WHERE joborder_id = $job");
        $this->overrides['joborders.hidden'] = ACCESS_LEVEL_READ;
        self::assertSame(array(), $this->warningRows('W01'));
        self::assertSame(0, $this->tasks->getRecruitmentWarnings()['jobsAtRisk']);
        self::assertSame(array(), (new \JobOrders())->getUnassignedWorkWarnings());
        $this->overrides = array();
        $this->db->query("UPDATE candidate SET is_admin_hidden = 1 WHERE candidate_id = $candidate");
        $this->overrides['candidates.hidden'] = ACCESS_LEVEL_READ;
        self::assertSame(array(), $this->warningRows('W01'));
        $this->overrides = array('candidates.show' => ACCESS_LEVEL_DISABLED);
        self::assertSame(array(), $this->warningRows('W01'));
        $this->overrides = array('joborders.edit' => ACCESS_LEVEL_READ, 'tasks.edit' => ACCESS_LEVEL_READ);
        self::assertSame(array(), $this->warningRows('W08'));
        $this->overrides = array('tasks.show' => ACCESS_LEVEL_DISABLED);
        self::assertSame(array(), $this->warningRows('W01'));
        $this->overrides = array();
        $this->db->query("DELETE FROM candidate_joborder WHERE candidate_joborder_id = {$this->pipeline}");
        self::assertSame(array(), $this->warningRows('W01'));
        $this->loggedIn = false;
        self::assertSame(array('W01' => 0, 'W02' => 0, 'W03' => 0, 'W07' => 0, 'W08' => 0), $this->tasks->getRecruitmentWarnings()['counts']);
        self::assertSame(array(), (new \JobOrders())->getUnassignedWorkWarnings());
    }

    public function testWarningBatchAuthorizationMatchesNativeTaskReads(): void
    {
        $due = (new \DateTimeImmutable(\DateUtility::getAdjustedDate('Y-m-d')))->modify('-1 day')->format('Y-m-d');
        foreach ($this->parents as $type => $id)
            $this->linked($type, array('dueDate' => $due, 'assignedTo' => 1));
        $compare = function () {
            $expected = array_map('intval', array_column($this->tasks->getAll(array('openOnly' => true)), 'taskID'));
            $actual = array_column($this->warningRows('W01'), 'entityID');
            sort($expected);
            sort($actual);
            self::assertSame($expected, $actual);
        };
        $compare();
        $this->db->query('UPDATE candidate SET is_admin_hidden = 1');
        $this->db->query('UPDATE joborder SET is_admin_hidden = 1');
        $this->access = ACCESS_LEVEL_EDIT;
        $compare();
        $this->overrides['contacts.show'] = ACCESS_LEVEL_DISABLED;
        $compare();
        $this->db->query('DELETE FROM company WHERE company_id = ' . $this->parents[DATA_ITEM_COMPANY]);
        $compare();
        self::assertSame(array(), $this->warningRows('W01'));
    }

    public function testWarningBatchesPreserveStandalonePrivacyAndStableOrdering(): void
    {
        $due = (new \DateTimeImmutable(\DateUtility::getAdjustedDate('Y-m-d')))->modify('-3 days')->format('Y-m-d');
        $private = $this->tasks->add(array('title' => 'Private', 'assignedTo' => $this->assignee, 'dueDate' => $due));
        $this->db->query("UPDATE task SET created_by = {$this->assignee} WHERE task_id = $private");
        $this->access = ACCESS_LEVEL_EDIT;
        self::assertSame(array(), $this->warningRows('W01'));
        $company = $this->parents[DATA_ITEM_COMPANY];
        $values = array_fill(0, 205, "('Batch', '$due', 'open', 'normal', 'general', 1, $company, " . DATA_ITEM_COMPANY . ', 1, NOW(), NOW())');
        $this->db->query('INSERT INTO task (title, due_date, status, priority, purpose, assigned_to, data_item_id, data_item_type, created_by, date_created, date_modified) VALUES ' . implode(',', $values));
        $before = $this->db->getAssoc("SHOW SESSION STATUS LIKE 'Questions'")['Value'];
        $rows = $this->warningRows('W01');
        $after = $this->db->getAssoc("SHOW SESSION STATUS LIKE 'Questions'")['Value'];
        self::assertLessThan(20, (int) $after - (int) $before);
        self::assertCount(205, $rows);
        self::assertCount(205, array_unique(array_column($rows, 'identity')));
        $ids = array_column($rows, 'entityID');
        $sorted = $ids;
        sort($sorted);
        self::assertSame($sorted, $ids);
        $this->overrides['companies.show'] = ACCESS_LEVEL_DISABLED;
        self::assertSame(array(), $this->warningRows('W01'));
        self::assertSame(0, $this->tasks->getRecruitmentWarnings()['counts']['W01']);
    }

    private function linked($type = DATA_ITEM_COMPANY, $values = array())
    {
        return $this->tasks->add(array_replace(array('title' => 'Follow up',
            'dataItemType' => $type, 'dataItemID' => $this->parents[$type]), $values));
    }

    private function rejected(callable $call, $class = \RuntimeException::class): void
    {
        try { $call(); }
        catch (\Throwable $e) { self::assertInstanceOf($class, $e); return; }
        self::fail('Operation should have been rejected.');
    }

    private function scalar($sql)
    {
        return array_values($this->db->getAssoc($sql))[0];
    }

    public function testParentsStandaloneDefaultsAndPurposeRoundTrips(): void
    {
        foreach ($this->parents as $type => $parent)
        {
            $id = $this->linked($type);
            $row = $this->tasks->get($id);
            self::assertSame((string) $parent, (string) $row['dataItemID']);
            self::assertSame((string) $type, (string) $row['dataItemType']);
            self::assertNull($row['assignedTo']);
            self::assertNull($row['dueDate']);
            self::assertNull($row['dateCompleted']);
            self::assertSame('normal', $row['priority']);
            self::assertSame('open', $row['status']);
            self::assertSame('general', $row['purpose']);
            self::assertCount(1, $this->tasks->getAll(array('dataItemType' => $type, 'dataItemID' => $parent)));
        }
        $id = $this->tasks->add(array('title' => 'Personal <text>', 'assignedTo' => $this->assignee,
            'description' => "  O'Brien & <b>plain</b>\nsecond line  ", 'dueDate' => '2028-02-29'));
        self::assertNull($this->tasks->get($id)['dataItemID']);
        self::assertSame('2028-02-29', $this->tasks->get($id)['dueDate']);
        self::assertSame("  O'Brien & <b>plain</b>\nsecond line  ", $this->tasks->get($id)['description']);
        foreach (array_keys(\Tasks::getPurposes()) as $purpose)
        {
            self::assertTrue($this->tasks->update($id, array('purpose' => $purpose)));
            self::assertSame($purpose, $this->tasks->get($id)['purpose']);
        }
        foreach (array_keys(\Tasks::getPriorities()) as $priority)
        {
            $this->tasks->update($id, array('priority' => $priority));
            self::assertSame($priority, $this->tasks->get($id)['priority']);
        }
        self::assertSame(5, $this->tasks->getCount(array('openOnly' => true)));
        self::assertSame(1, $this->tasks->getCount(array('assignedTo' => $this->assignee, 'purpose' => 'administrative', 'priority' => 'high')));
    }

    public function testCompletionReopeningReschedulingAndReassignmentAudit(): void
    {
        $id = $this->linked(DATA_ITEM_CANDIDATE, array('assignedTo' => $this->assignee));
        $activityBefore = $this->scalar('SELECT COUNT(*) FROM activity');
        $this->tasks->update($id, array('dueDate' => '2026-12-31', 'status' => 'in_progress'));
        $this->tasks->update($id, array('dueDate' => '2027-01-02', 'assignedTo' => 1));
        $this->tasks->update($id, array('status' => 'completed'));
        $completed = $this->tasks->get($id);
        self::assertSame('1', (string) $completed['completedBy']);
        self::assertNotEmpty($completed['dateCompleted']);
        self::assertSame(0, $this->tasks->getCount(array('openOnly' => true)));
        $count = count($this->tasks->getHistory($id));
        $this->tasks->update($id, array('status' => 'completed'));
        self::assertCount($count, $this->tasks->getHistory($id));
        self::assertSame($completed['dateCompleted'], $this->tasks->get($id)['dateCompleted']);
        $this->rejected(fn() => $this->tasks->update($id, array('status' => 'in_progress')), \InvalidArgumentException::class);
        $this->tasks->update($id, array('status' => 'open'));
        self::assertNull($this->tasks->get($id)['completedBy']);
        self::assertNull($this->tasks->get($id)['dateCompleted']);
        $this->tasks->update($id, array('status' => 'cancelled', 'dueDate' => null));
        self::assertSame(0, $this->tasks->getCount(array('openOnly' => true)));
        $this->tasks->update($id, array('status' => 'open'));
        self::assertSame(1, $this->tasks->getCount(array('openOnly' => true)));
        $history = $this->tasks->getHistory($id);
        foreach (array('dueDate', 'assignedTo', 'status', 'completedBy', 'dateCompleted') as $field)
            self::assertContains($field, array_column($history, 'theField'));
        self::assertContains('2026-12-31', array_column($history, 'previousValue'));
        self::assertContains('completed', array_column($history, 'previousValue'));
        self::assertSame($activityBefore, $this->scalar('SELECT COUNT(*) FROM activity'));
        $createdClosed = $this->linked(DATA_ITEM_COMPANY, array('status' => 'completed'));
        self::assertNotNull($this->tasks->get($createdClosed)['dateCompleted']);
    }

    public function testAssigneeCanCompleteWithParentReadButCannotReassign(): void
    {
        $id = $this->linked(DATA_ITEM_COMPANY, array('assignedTo' => $this->assignee));
        $this->actor = $this->assignee;
        $this->access = ACCESS_LEVEL_EDIT;
        $this->overrides['companies.edit'] = ACCESS_LEVEL_READ;
        $this->tasks->update($id, array('title' => 'Assignee edit', 'status' => 'completed'));
        self::assertSame((string) $this->assignee, (string) $this->tasks->get($id)['completedBy']);
        $this->tasks->update($id, array('status' => 'open'));
        $this->tasks->update($id, array('status' => 'cancelled'));
        $this->tasks->update($id, array('status' => 'open'));
        $this->rejected(fn() => $this->tasks->update($id, array('assignedTo' => 1)));
        $this->rejected(fn() => $this->tasks->update($id, array('dataItemType' => null, 'dataItemID' => null)));
        $this->rejected(fn() => $this->tasks->update($id, array('candidateJobOrderID' => $this->pipeline)));
        $this->rejected(fn() => $this->linked());
        $this->overrides['tasks.complete'] = ACCESS_LEVEL_READ;
        $this->rejected(fn() => $this->tasks->update($id, array('status' => 'completed')));
        self::assertSame('open', $this->tasks->get($id)['status']);
        $this->overrides['companies.show'] = ACCESS_LEVEL_DISABLED;
        self::assertSame(array(), $this->tasks->get($id));
        self::assertSame(array(), $this->tasks->getHistory($id));
        self::assertSame(0, $this->tasks->getCount());
        $this->rejected(fn() => $this->tasks->update($id, array('title' => 'Forbidden')));
    }

    public function testStandalonePrivacyCreatorManagementAndActionDenial(): void
    {
        $id = $this->tasks->add(array('title' => 'Private', 'assignedTo' => $this->assignee));
        $this->actor = 999;
        $this->access = ACCESS_LEVEL_EDIT;
        self::assertSame(array(), $this->tasks->get($id));
        self::assertSame(0, $this->tasks->getCount());
        $this->rejected(fn() => $this->tasks->update($id, array('status' => 'completed')));
        $this->access = ACCESS_LEVEL_SA;
        self::assertNotEmpty($this->tasks->get($id));
        $this->overrides['tasks.admin'] = ACCESS_LEVEL_READ;
        self::assertSame(array(), $this->tasks->get($id));
        $this->actor = 1;
        $this->access = ACCESS_LEVEL_EDIT;
        $this->tasks->update($id, array('assignedTo' => 1));
        self::assertSame('1', (string) $this->tasks->get($id)['assignedTo']);
        $linked = $this->linked();
        $this->actor = $this->assignee;
        self::assertNotEmpty($this->tasks->get($linked)); // parent visibility permits read, not mutation
        $this->rejected(fn() => $this->tasks->update($linked, array('title' => 'Not creator or assignee')));
        $this->overrides['tasks.show'] = ACCESS_LEVEL_DISABLED;
        self::assertSame(0, $this->tasks->getCount());
        $this->rejected(fn() => $this->linked());
        $this->loggedIn = false;
        self::assertSame(array(), $this->tasks->get($linked));
        $this->rejected(fn() => $this->tasks->update($linked, array('title' => 'Logged out')));
    }

    public function testDisabledAssigneesAreRetainedButReceiveNoNewAssignments(): void
    {
        $id = $this->linked(DATA_ITEM_CONTACT, array('assignedTo' => $this->assignee));
        $other = $this->linked();
        $this->db->query('UPDATE user SET access_level = 0 WHERE user_id = ' . $this->assignee);
        $this->tasks->update($id, array('title' => 'Retain historical assignment'));
        $this->tasks->update($id, array('assignedTo' => $this->assignee));
        self::assertSame((string) $this->assignee, (string) $this->tasks->get($id)['assignedTo']);
        $this->rejected(fn() => $this->linked(DATA_ITEM_COMPANY, array('assignedTo' => $this->assignee)), \InvalidArgumentException::class);
        $this->rejected(fn() => $this->tasks->update($other, array('assignedTo' => $this->assignee)), \InvalidArgumentException::class);
        $this->tasks->update($id, array('assignedTo' => null));
        self::assertNull($this->tasks->get($id)['assignedTo']);
    }

    public function testInvalidInputIsRejectedWithoutMutation(): void
    {
        $id = $this->linked();
        $before = $this->tasks->get($id);
        $invalid = array('title' => array(array('bad'), '', "bad\nline", str_repeat('a', 256), "\xff"),
            'description' => array(array('bad'), "\xff", str_repeat('x', 65536)),
            'dueDate' => array(array('2026-01-01'), '2026-02-29', '2026-13-01', '2026-01-01 12:00', '0000-00-00'),
            'status' => array(array('open'), 'unknown'), 'priority' => array(array('high'), 'urgent'),
            'purpose' => array(array('general'), 'automatic'),
            'assignedTo' => array(array(1), true, -1, 0, '1x', '1.0', '2147483648', 999999),
            'dataItemType' => array(array(200), 999), 'dataItemID' => array(array(1), null),
            'candidateJobOrderID' => array(array(1)), 'createdBy' => array(999), 'completedBy' => array(999));
        foreach ($invalid as $field => $values)
            foreach ($values as $value)
                $this->rejected(fn() => $this->tasks->update($id, array($field => $value)), \InvalidArgumentException::class);
        self::assertSame($before, $this->tasks->get($id));
        $this->rejected(fn() => $this->tasks->add(array('title' => 'Unassigned standalone')), \InvalidArgumentException::class);
        $this->rejected(fn() => $this->linked(DATA_ITEM_COMPANY, array('dataItemID' => 999999)));
        foreach (array(array('dataItemID' => 1), array('assignedTo' => array(1)), array('status' => 'bad'), array('unknown' => 1), array('openOnly' => 'yes')) as $filters)
            $this->rejected(fn() => $this->tasks->getAll($filters), \InvalidArgumentException::class);
        $this->rejected(fn() => $this->tasks->get(array(1)), \InvalidArgumentException::class);
    }

    public function testPipelineScopeStateAndRemoval(): void
    {
        $id = $this->linked(DATA_ITEM_CANDIDATE, array('assignedTo' => $this->assignee,
            'candidateJobOrderID' => $this->pipeline, 'purpose' => 'interview_feedback'));
        $this->actor = $this->assignee;
        $this->access = ACCESS_LEVEL_EDIT;
        foreach (array('candidates.show', 'joborders.show') as $module)
        {
            $this->overrides[$module] = ACCESS_LEVEL_DISABLED;
            self::assertSame(array(), $this->tasks->get($id));
            self::assertSame(0, $this->tasks->getCount());
            $this->rejected(fn() => $this->tasks->update($id, array('status' => 'completed')));
            unset($this->overrides[$module]);
        }
        foreach (array('candidate' => $this->parents[DATA_ITEM_CANDIDATE], 'joborder' => $this->parents[DATA_ITEM_JOBORDER]) as $table => $parent)
        {
            $this->db->query("UPDATE $table SET is_admin_hidden = 1 WHERE {$table}_id = $parent");
            self::assertSame(array(), $this->tasks->get($id));
            self::assertSame(0, $this->tasks->getCount());
            $this->db->query("UPDATE $table SET is_admin_hidden = 0 WHERE {$table}_id = $parent");
        }
        $this->db->query('UPDATE candidate_joborder SET status = 800 WHERE candidate_joborder_id = ' . $this->pipeline);
        self::assertSame('open', $this->tasks->get($id)['status']);
        $this->db->query('UPDATE candidate_joborder SET status = 100 WHERE candidate_joborder_id = ' . $this->pipeline);
        self::assertSame('open', $this->tasks->get($id)['status']);
        (new \Pipelines())->remove($this->parents[DATA_ITEM_CANDIDATE], $this->parents[DATA_ITEM_JOBORDER]);
        self::assertSame(array(), $this->tasks->get($id));
        self::assertSame(0, $this->tasks->getCount());
        self::assertSame('open', $this->scalar('SELECT status FROM task WHERE task_id = ' . $id));
        $this->rejected(fn() => $this->tasks->update($id, array('status' => 'completed')));
    }

    public function testContextEditsAndHistoricalParentVisibility(): void
    {
        $id = $this->linked();
        $this->tasks->update($id, array('candidateJobOrderID' => $this->pipeline));
        $this->tasks->update($id, array('candidateJobOrderID' => null));
        $this->access = ACCESS_LEVEL_EDIT;
        $this->overrides['joborders.show'] = ACCESS_LEVEL_DISABLED;
        self::assertNotEmpty($this->tasks->get($id));
        self::assertSame(array(), $this->tasks->getHistory($id));
        unset($this->overrides['joborders.show']);
        self::assertNotEmpty($this->tasks->getHistory($id));
        $this->tasks->update($id, array('dataItemType' => DATA_ITEM_CANDIDATE, 'dataItemID' => $this->parents[DATA_ITEM_CANDIDATE]));
        $this->tasks->update($id, array('dataItemType' => DATA_ITEM_COMPANY, 'dataItemID' => $this->parents[DATA_ITEM_COMPANY]));
        $this->overrides['candidates.show'] = ACCESS_LEVEL_DISABLED;
        self::assertSame(array(), $this->tasks->getHistory($id));
        $this->rejected(fn() => $this->tasks->update($id, array('candidateJobOrderID' => $this->pipeline)));
        unset($this->overrides['candidates.show']);
        $this->db->query("INSERT INTO candidate (first_name, last_name, entered_by, owner) VALUES ('Other', 'Candidate', 1, 1)");
        $other = (int) $this->db->getLastInsertID();
        $this->rejected(fn() => $this->linked(DATA_ITEM_CANDIDATE, array('dataItemID' => $other, 'candidateJobOrderID' => $this->pipeline)), \InvalidArgumentException::class);
    }

    public function testActualParentDeletionFailsClosedWithoutDeletingTasks(): void
    {
        $ids = array();
        foreach ($this->parents as $type => $parent) $ids[$type] = $this->linked($type);
        (new \Candidates())->delete($this->parents[DATA_ITEM_CANDIDATE]);
        (new \Companies())->delete($this->parents[DATA_ITEM_COMPANY]); // also deletes Contacts and Job Orders
        foreach ($ids as $id)
        {
            self::assertSame(array(), $this->tasks->get($id));
            self::assertSame(array(), $this->tasks->getHistory($id));
            $this->rejected(fn() => $this->tasks->update($id, array('title' => 'Orphan edit')));
        }
        self::assertSame(0, $this->tasks->getCount());
        self::assertSame(4, (int) $this->scalar('SELECT COUNT(*) FROM task'));
    }

    public function testFreshUpgradeRetryAndIncompatibleTablePreservation(): void
    {
        require_once LEGACY_ROOT . '/modules/install/Schema.php';
        require_once LEGACY_ROOT . '/lib/ModuleUtility.php';
        $schema = fn() => array($this->db->getAllAssoc('SHOW FULL COLUMNS FROM task'),
            $this->db->getAllAssoc("SELECT INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'task' ORDER BY INDEX_NAME, SEQ_IN_INDEX"));
        $fresh = $schema();
        $this->db->query('DROP TABLE task');
        $this->db->query("UPDATE module_schema SET version = 398 WHERE name = 'install'");
        global $maintPage;
        $maintPage = true;
        try { (new \ReflectionMethod(\ModuleUtility::class, 'processModuleSchema'))->invoke(null, 'install', array(399 => \CATSSchema::get()[399])); }
        finally { $maintPage = false; }
        self::assertSame(399, (int) $this->scalar("SELECT version FROM module_schema WHERE name = 'install'"));
        self::assertSame($fresh, $schema());
        $id = $this->linked(DATA_ITEM_COMPANY, array('dueDate' => null));
        $before = $this->tasks->get($id);
        $this->db->query('ALTER TABLE task DROP INDEX idx_task_parent_status');
        $db = $this->db;
        eval(substr(\CATSSchema::get()[399], 4));
        eval(substr(\CATSSchema::get()[399], 4));
        self::assertSame($fresh, $schema());
        self::assertSame($before, $this->tasks->get($id));
        $this->db->query('ALTER TABLE task MODIFY priority VARCHAR(16) NOT NULL DEFAULT \'urgent\'');
        $this->db->query("UPDATE module_schema SET version = 398 WHERE name = 'install'");
        $maintPage = true;
        try { $this->rejected(fn() => (new \ReflectionMethod(\ModuleUtility::class, 'processModuleSchema'))->invoke(null, 'install', array(399 => \CATSSchema::get()[399]))); }
        finally { $maintPage = false; }
        self::assertSame(398, (int) $this->scalar("SELECT version FROM module_schema WHERE name = 'install'"));
        self::assertSame($before, $this->tasks->get($id));
    }

    public function testNativeBackupRoundTripIncludesTaskAuditAndNulls(): void
    {
        require_once LEGACY_ROOT . '/modules/install/backupDB.php';
        $id = $this->linked(DATA_ITEM_CONTACT, array('description' => "O'Brien\n<plain> \\", 'dueDate' => '2026-12-31'));
        $this->tasks->update($id, array('dueDate' => null, 'assignedTo' => $this->assignee, 'status' => 'completed'));
        $this->tasks->update($id, array('status' => 'open'));
        $before = $this->tasks->get($id);
        $history = $this->tasks->getHistory($id);
        $file = tempnam(sys_get_temp_dir(), 'task-backup-');
        try
        {
            \dumpDB($this->db, $file);
            foreach (glob($file . '.*') as $part)
                foreach (explode('((ENDOFQUERY))', file_get_contents($part)) as $sql)
                    if (preg_match('/^(DROP TABLE IF EXISTS|CREATE TABLE|INSERT INTO) `(task|history)`/', trim($sql)))
                        self::assertNotFalse($this->db->query(trim($sql)));
            self::assertSame($before, $this->tasks->get($id));
            self::assertSame($history, $this->tasks->getHistory($id));
            self::assertSame(0, (int) $this->scalar('SELECT COUNT(*) FROM history WHERE data_item_type <> ' . DATA_ITEM_TASK));
        }
        finally { foreach (glob($file . '*') as $part) unlink($part); }
    }

    public function testHistoryFailureRollsBackTaskAndCompletion(): void
    {
        $id = $this->linked();
        $before = $this->tasks->get($id);
        $this->db->query("CREATE TRIGGER reject_task_history BEFORE INSERT ON history FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Task test audit failure'");
        $this->rejected(fn() => $this->tasks->update($id, array('status' => 'completed')), \mysqli_sql_exception::class);
        self::assertSame($before, $this->tasks->get($id));
        $this->rejected(fn() => $this->linked(), \mysqli_sql_exception::class);
        self::assertSame(1, (int) $this->scalar('SELECT COUNT(*) FROM task'));
    }
    public function testFalseHistoryResultRollsBackWithoutOwningCallerTransactions(): void
    {
        $id = $this->linked();
        $before = $this->tasks->get($id);
        $real = $this->db;
        $stub = $this->createStub(\DatabaseConnection::class);
        $stub->method('makeQueryStringOrNULL')->willReturnCallback(fn($value) => $real->makeQueryStringOrNULL($value));
        $stub->method('makeQueryInteger')->willReturnCallback(fn($value) => $real->makeQueryInteger($value));
        $stub->method('getAssoc')->willReturnCallback(fn($sql = null) => $real->getAssoc($sql));
        $stub->method('query')->willReturn(false);
        $instance = new \ReflectionProperty(\DatabaseConnection::class, '_instance');
        $instance->setValue(null, $stub);
        try
        {
            foreach (array(fn() => $this->tasks->update($id, array('status' => 'completed')),
                fn() => $this->tasks->add(array('title' => 'Follow up', 'dataItemType' => DATA_ITEM_COMPANY,
                    'dataItemID' => $this->parents[DATA_ITEM_COMPANY]))) as $write)
            {
                try { $write(); self::fail('Audit failure should reject the write.'); }
                catch (\RuntimeException $e) { self::assertSame('Unable to record Task history.', $e->getMessage()); }
            }
        }
        finally { $instance->setValue(null, $real); }
        self::assertSame($before, $this->tasks->get($id));
        self::assertSame(1, (int) $this->scalar('SELECT COUNT(*) FROM task'));
        self::assertTrue($this->db->beginTransaction());
        $this->db->query("UPDATE task SET title = 'Caller transaction' WHERE task_id = " . $id);
        $this->rejected(fn() => $this->tasks->update($id, array('title' => 'Nested')));
        self::assertSame('Caller transaction', $this->tasks->get($id)['title']);
        self::assertTrue($this->db->rollbackTransaction());
        self::assertSame($before, $this->tasks->get($id));
    }

    public function testExistingHistoryWritesRemainCallerTransactional(): void
    {
        $history = new \History();
        $id = $this->parents[DATA_ITEM_COMPANY];
        $count = (int) $this->scalar('SELECT COUNT(*) FROM history');
        $this->db->beginTransaction();
        self::assertTrue($history->storeHistoryNew(DATA_ITEM_COMPANY, $id));
        self::assertTrue($history->storeHistoryCatagorized(DATA_ITEM_COMPANY, $id, 'review', 'Review note'));
        self::assertTrue($history->storeHistoryChanges(DATA_ITEM_COMPANY, $id,
            array('name' => 'Before', 'dateModified' => 'old'), array('name' => 'After', 'dateModified' => 'new')));
        self::assertSame($count + 3, (int) $this->scalar('SELECT COUNT(*) FROM history'));
        self::assertTrue($history->storeHistoryChanges(DATA_ITEM_COMPANY, $id,
            array('name' => 'After'), array('name' => 'After')));
        self::assertSame($count + 3, (int) $this->scalar('SELECT COUNT(*) FROM history'));
        $rows = $history->getAll(DATA_ITEM_COMPANY, $id);
        self::assertSame('Before', $rows[0]['previousValue']);
        self::assertSame('After', $rows[0]['newValue']);
        self::assertSame('1', (string) $rows[0]['enteredByID']);
        $this->db->rollbackTransaction();
        self::assertSame($count, (int) $this->scalar('SELECT COUNT(*) FROM history'));
    }

    public function testRelinkingRechecksDestinationAndStandaloneScope(): void
    {
        $id = $this->linked(DATA_ITEM_COMPANY, array('assignedTo' => $this->assignee));
        $this->access = ACCESS_LEVEL_EDIT;
        $this->overrides['contacts.edit'] = ACCESS_LEVEL_READ;
        $this->rejected(fn() => $this->tasks->update($id, array('dataItemType' => DATA_ITEM_CONTACT,
            'dataItemID' => $this->parents[DATA_ITEM_CONTACT])));
        unset($this->overrides['contacts.edit']);
        $this->tasks->update($id, array('dataItemType' => DATA_ITEM_CONTACT,
            'dataItemID' => $this->parents[DATA_ITEM_CONTACT]));
        self::assertSame(0, $this->tasks->getCount(array('dataItemType' => DATA_ITEM_COMPANY,
            'dataItemID' => $this->parents[DATA_ITEM_COMPANY])));
        self::assertSame(1, $this->tasks->getCount(array('dataItemType' => DATA_ITEM_CONTACT,
            'dataItemID' => $this->parents[DATA_ITEM_CONTACT])));
        $this->tasks->update($id, array('dataItemType' => null, 'dataItemID' => null));
        $this->actor = 999;
        self::assertSame(array(), $this->tasks->get($id));
        self::assertSame(array(), $this->tasks->getAll());
        self::assertSame(0, $this->tasks->getCount());
        $this->actor = $this->assignee;
        self::assertCount(1, $this->tasks->getAll());
        $this->rejected(fn() => $this->tasks->update($id, array('assignedTo' => 1)));
        $this->actor = 1;
        $this->tasks->update($id, array('assignedTo' => 1));
        $this->actor = $this->assignee;
        self::assertSame(array(), $this->tasks->get($id));
        self::assertSame(array(), $this->tasks->getHistory($id));
        self::assertSame(0, $this->tasks->getCount());
    }

    public function testPreTaskNativeBackupRestoresThenUpgrades(): void
    {
        require_once LEGACY_ROOT . '/modules/install/backupDB.php';
        require_once LEGACY_ROOT . '/modules/install/Schema.php';
        require_once LEGACY_ROOT . '/lib/ModuleUtility.php';
        // Reproduce a revision-398 native backup: no Task table or Task history.
        $this->db->query('DROP TABLE task');
        $this->db->query("UPDATE module_schema SET version = 398 WHERE name = 'install'");
        $company = $this->db->getAssoc('SELECT * FROM company WHERE company_id = ' . $this->parents[DATA_ITEM_COMPANY]);
        $file = tempnam(sys_get_temp_dir(), 'pre-task-backup-');
        try
        {
            \dumpDB($this->db, $file);
            $parts = glob($file . '.*');
            natsort($parts);
            self::assertStringNotContainsString('CREATE TABLE `task`', file_get_contents($file . '.0'));
            foreach ($this->db->getAllAssoc('SHOW TABLES') as $row)
                $this->db->query('DROP TABLE `' . reset($row) . '`');
            // Same SQL delimiter used by installer restoreFromBackup.
            foreach ($parts as $part)
                foreach (explode('((ENDOFQUERY))', file_get_contents($part)) as $sql)
                    if (trim($sql) !== '') self::assertNotFalse($this->db->query(trim($sql)));
            self::assertSame(398, (int) $this->scalar("SELECT version FROM module_schema WHERE name = 'install'"));
            self::assertSame($company, $this->db->getAssoc('SELECT * FROM company WHERE company_id = ' . $this->parents[DATA_ITEM_COMPANY]));
            self::assertSame(0, (int) $this->scalar('SELECT COUNT(*) FROM history'));
            global $maintPage;
            $maintPage = true;
            try { (new \ReflectionMethod(\ModuleUtility::class, 'processModuleSchema'))->invoke(null, 'install', array(399 => \CATSSchema::get()[399])); }
            finally { $maintPage = false; }
            self::assertSame(399, (int) $this->scalar("SELECT version FROM module_schema WHERE name = 'install'"));
            self::assertSame(0, $this->tasks->getCount());
            self::assertNotEmpty($this->tasks->get($this->linked()));
        }
        finally { foreach (glob($file . '*') as $part) unlink($part); }
    }

    public function testMigrationAcceptsIntegerMetadataWithoutDisplayWidths(): void
    {
        require_once LEGACY_ROOT . '/modules/install/Schema.php';
        $real = $this->db;
        $db = $this->createStub(\DatabaseConnection::class);
        $db->method('query')->willReturnCallback(fn($sql) => $real->query($sql));
        $db->method('getAssoc')->willReturnCallback(fn($sql) => $real->getAssoc($sql));
        $db->method('makeQueryString')->willReturnCallback(fn($value) => $real->makeQueryString($value));
        $db->method('getAllAssoc')->willReturnCallback(function ($sql) use ($real) {
            $rows = $real->getAllAssoc($sql);
            if ($sql === 'SHOW FULL COLUMNS FROM task')
                foreach ($rows as &$row)
                    if ($row['Type'] === 'int(11)') $row['Type'] = 'int';
            return $rows;
        });
        eval(substr(\CATSSchema::get()[399], 4));
        self::assertSame(0, $this->tasks->getCount());
    }

}
