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
        unset($_SESSION['CATS']);
        (new \ReflectionProperty(\DatabaseConnection::class, '_instance'))->setValue(null, null);
        parent::tearDown();
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
