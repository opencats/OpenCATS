<?php
namespace OpenCATS\Tests\IntegrationTests;

class HandoverTest extends DatabaseTestCase
{
    private $db;
    private $users;
    private $from;
    private $to;
    private $other;
    private $ids = array();
    private $overrides = array();
    private $csrf = true;

    protected function setUp(): void
    {
        parent::setUp();
        require_once LEGACY_ROOT . '/lib/CATSUtility.php';
        require_once LEGACY_ROOT . '/lib/Session.php';
        require_once LEGACY_ROOT . '/lib/Tasks.php';
        require_once LEGACY_ROOT . '/lib/Calendar.php';
        $session = $this->createStub(\CATSSession::class);
        $session->method('isLoggedIn')->willReturn(true);
        $session->method('getUserID')->willReturn(1);
        $session->method('getRealAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        $session->method('getAccessLevel')->willReturnCallback(fn($key) => $this->overrides[$key] ?? ACCESS_LEVEL_SA);
        $session->method('isCSRFTokenValid')->willReturnCallback(fn($token) => $this->csrf && $token === 'valid');
        $session->method('isDateDMY')->willReturn(false);
        $session->method('getTimeZoneOffset')->willReturn(0);
        $_SESSION['CATS'] = $session;
        $this->db = \DatabaseConnection::getInstance();
        $this->users = new \Users();
        foreach (array('from', 'to', 'other') as $name)
        {
            $this->db->query("INSERT INTO user (user_name, first_name, last_name, access_level, categories) VALUES ('handover-$name', '$name', 'User', 200, '')");
            $this->$name = (int) $this->db->getLastInsertID();
        }
        $f = $this->from;
        $this->ids['company'] = $this->insert("INSERT INTO company (name, owner, entered_by, relationship_status) VALUES ('Current Company', $f, $f, 'Client')");
        $c = $this->ids['company'];
        $this->ids['lost'] = $this->insert("INSERT INTO company (name, owner, entered_by, relationship_status) VALUES ('Lost Company', $f, $f, 'Lost')");
        $this->ids['candidate'] = $this->insert("INSERT INTO candidate (first_name, last_name, owner, entered_by, is_active) VALUES ('Current', 'Candidate', $f, $f, 1)");
        $this->ids['inactive'] = $this->insert("INSERT INTO candidate (first_name, last_name, owner, entered_by, is_active) VALUES ('Inactive', 'Candidate', $f, $f, 0)");
        $this->ids['hidden'] = $this->insert("INSERT INTO candidate (first_name, last_name, owner, entered_by, is_active, is_admin_hidden) VALUES ('Hidden', 'Candidate', $f, $f, 1, 1)");
        $this->ids['contact'] = $this->insert("INSERT INTO contact (first_name, last_name, company_id, company_department_id, owner, entered_by, left_company) VALUES ('Current', 'Contact', $c, -1, $f, $f, 0)");
        $this->ids['left'] = $this->insert("INSERT INTO contact (first_name, last_name, company_id, company_department_id, owner, entered_by, left_company) VALUES ('Former', 'Contact', $c, -1, $f, $f, 1)");
        $this->ids['job'] = $this->insert("INSERT INTO joborder (title, company_id, owner, recruiter, entered_by, status, is_speculative, first_actionable_at) VALUES ('Active Job', $c, $f, $f, $f, 'Active', 0, '2026-01-01 12:00:00')");
        $this->ids['closed'] = $this->insert("INSERT INTO joborder (title, company_id, owner, recruiter, entered_by, status) VALUES ('Closed Job', $c, $f, $f, $f, 'Closed')");
        $this->ids['task'] = (new \Tasks())->add(array('title' => 'Open Task', 'assignedTo' => $f));
        $this->ids['completed'] = (new \Tasks())->add(array('title' => 'Historical Task', 'assignedTo' => $f, 'status' => 'completed'));
        $this->db->query("INSERT INTO warning_review (reviewing_user, warning_rule, data_item_type, data_item_id, reason_key, reason_id, action, action_at, evidence_hash, request_hash, version) VALUES ($f, 'W07', 200, $c, 'stale_relationship', 0, 'close', NOW(), REPEAT('a',64), REPEAT('b',64), 1)");
        $this->ids['event'] = $this->insert("INSERT INTO calendar_event (type, date, title, entered_by, public, data_item_id, data_item_type) VALUES (100, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 1 DAY), 'Future appointment', $f, 0, -1, -1)");
    }

    private function insert($sql)
    {
        $this->db->query($sql);
        return (int) $this->db->getLastInsertID();
    }

    private function snapshot($table)
    {
        return $this->db->getAllAssoc('SELECT * FROM ' . $table);
    }

    protected function tearDown(): void
    {
        unset($_SESSION['CATS'], $_SESSION['userHandover']);
        $_GET = $_POST = $_REQUEST = array();
        (new \ReflectionProperty(\DatabaseConnection::class, '_instance'))->setValue(null, null);
        parent::tearDown();
    }

    public function testPreviewAndSelectiveTransferPreserveHistoryAndOtherFields(): void
    {
        $tables = array('task', 'candidate', 'contact', 'company', 'joborder', 'history', 'warning_review', 'calendar_event');
        $before = array_combine($tables, array_map(fn($t) => $this->snapshot($t), $tables));
        $preview = $this->users->previewHandover($this->from, $this->to,
            array('task', 'candidate', 'contact', 'company', 'jobOwner'), array('jobOwner' => $this->other));
        foreach ($tables as $table) self::assertSame($before[$table], $this->snapshot($table), 'Preview wrote ' . $table);
        self::assertSame(1, $preview['categories']['task']['eligible']);
        self::assertSame(1, $preview['categories']['candidate']['blocked']); // recipient cannot see hidden Candidate
        foreach (array('task', 'candidate', 'contact', 'company', 'jobOwner') as $key) self::assertSame(1, $preview['categories'][$key]['excluded']);
        $results = $this->users->executeHandover($preview, 'valid');
        foreach ($results as $rows) self::assertCount(1, array_filter($rows, fn($r) => $r['state'] === 'transferred'));
        foreach (array('candidate', 'contact', 'company') as $table)
        {
            $id = $this->ids[$table];
            $row = $this->db->getAssoc("SELECT * FROM $table WHERE {$table}_id = $id");
            $old = array_values(array_filter($before[$table], fn($r) => $r[$table . '_id'] == $id))[0];
            $old['owner'] = (string) $this->to;
            unset($old['date_modified'], $row['date_modified']);
            self::assertSame($old, $row);
        }
        $job = $this->db->getAssoc('SELECT * FROM joborder WHERE joborder_id = ' . $this->ids['job']);
        $old = array_values(array_filter($before['joborder'], fn($r) => $r['joborder_id'] == $this->ids['job']))[0];
        $old['owner'] = (string) $this->other;
        unset($old['date_modified'], $job['date_modified']);
        self::assertSame($old, $job); // recruiter, evidence, Desk, Sector, metadata unchanged
        $task = $this->db->getAssoc('SELECT * FROM task WHERE task_id = ' . $this->ids['task']);
        $old = array_values(array_filter($before['task'], fn($r) => $r['task_id'] == $this->ids['task']))[0];
        $old['assigned_to'] = (string) $this->to;
        unset($old['date_modified'], $task['date_modified']);
        self::assertSame($old, $task);
        foreach (array('warning_review', 'calendar_event') as $table) self::assertSame($before[$table], $this->snapshot($table));
        foreach (array('completed' => 'task', 'inactive' => 'candidate', 'hidden' => 'candidate', 'left' => 'contact', 'lost' => 'company', 'closed' => 'joborder') as $key => $table)
        {
            $id = $this->ids[$key];
            $column = $table === 'task' ? 'assigned_to' : 'owner';
            self::assertEquals($this->from, $this->db->getAssoc("SELECT $column FROM $table WHERE {$table}_id = $id")[$column]);
        }
        $audits = $this->db->getAllAssoc("SELECT * FROM history WHERE the_field IN ('owner','assignedTo') AND previous_value = '{$this->from}'");
        self::assertCount(5, $audits);
        foreach ($audits as $audit) self::assertEquals(1, $audit['entered_by']);
    }

    public function testPartialFailureAuditRollbackRetryAndIndependentRecruiter(): void
    {
        $preview = $this->users->previewHandover($this->from, $this->to, array('company', 'jobRecruiter'));
        $history = count($this->snapshot('history'));
        $this->db->query("CREATE TRIGGER handover_audit_failure BEFORE INSERT ON history FOR EACH ROW BEGIN IF NEW.data_item_type = 200 AND NEW.the_field = 'owner' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'fixture audit failure'; END IF; END");
        $result = $this->users->executeHandover($preview, 'valid');
        self::assertSame('failed', $result['company'][0]['state']);
        self::assertSame('transferred', $result['jobRecruiter'][0]['state']);
        self::assertEquals($this->from, (new \Companies())->get($this->ids['company'])['owner']);
        self::assertEquals($this->from, (new \JobOrders())->get($this->ids['job'])['owner']);
        self::assertEquals($this->to, (new \JobOrders())->get($this->ids['job'])['recruiter']);
        $this->db->query('DROP TRIGGER handover_audit_failure');
        $retry = $this->users->executeHandover($preview, 'valid');
        self::assertSame('transferred', $retry['company'][0]['state']);
        self::assertSame('skipped', $retry['jobRecruiter'][0]['state']);
        self::assertGreaterThan($history, count($this->snapshot('history')));
        $after = count($this->snapshot('history'));
        $this->users->executeHandover($preview, 'valid');
        self::assertSame($after, count($this->snapshot('history')));
    }

    public function testVisibilityEligibilityAndDisabledRecipientsAreRechecked(): void
    {
        $this->overrides['candidates.hidden'] = ACCESS_LEVEL_READ;
        $preview = $this->users->previewHandover($this->from, $this->to, array('candidate', 'task', 'jobOwner'));
        self::assertCount(2, $preview['categories']['candidate']['rows']);
        $this->db->query('UPDATE candidate SET is_admin_hidden = 1 WHERE candidate_id = ' . $this->ids['candidate']);
        $this->db->query("UPDATE joborder SET status = 'Closed' WHERE joborder_id = " . $this->ids['job']);
        $this->db->query('UPDATE user SET access_level = 0 WHERE user_id = ' . $this->to);
        $before = $this->snapshot('warning_review');
        $result = $this->users->executeHandover($preview, 'valid');
        foreach ($result as $rows) foreach ($rows as $row) self::assertNotSame('transferred', $row['state']);
        self::assertSame($before, $this->snapshot('warning_review'));
        self::assertEquals($this->from, (new \Tasks())->get($this->ids['task'])['assignedTo']);
    }

    public function testDisableAndCalendarInventoryDoNotReassignAnything(): void
    {
        $before = $this->snapshot('warning_review');
        self::assertCount(1, (new \Calendar())->getHandoverInventory($this->from));
        self::assertTrue($this->users->disableForHandover($this->from, 'valid'));
        self::assertEquals(ACCESS_LEVEL_DISABLED, $this->users->get($this->from)['accessLevel']);
        self::assertSame($before, $this->snapshot('warning_review'));
        self::assertEquals($this->from, (new \Companies())->get($this->ids['company'])['owner']);
        $preview = $this->users->previewHandover($this->from, $this->to, array('company'));
        self::assertSame('transferred', $this->users->executeHandover($preview, 'valid')['company'][0]['state']);
    }
    /** Exercise the real Settings dispatcher while capturing native template assignments. */
    private function handoverRequest($post = array())
    {
        require_once LEGACY_ROOT . '/lib/UserInterface.php';
        require_once LEGACY_ROOT . '/lib/Template.php';
        require_once LEGACY_ROOT . '/modules/settings/SettingsUI.php';
        $_GET = array('a' => 'handover');
        $_POST = $post;
        $_SERVER['REQUEST_METHOD'] = $post ? 'POST' : 'GET';
        $values = array();
        $template = $this->createStub(\Template::class);
        $template->method('assign')->willReturnCallback(function ($key, $value) use (&$values) { $values[$key] = $value; });
        $ui = (new \ReflectionClass(\SettingsUI::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(\UserInterface::class, '_template'))->setValue($ui, $template);
        (new \ReflectionProperty(\SettingsUI::class, '_realAccessLevel'))->setValue($ui, ACCESS_LEVEL_SA);
        $ui->handleRequest();
        return $values;
    }

    public function testDisabledLeaverIsSelectableFromFreshAdministratorSession(): void
    {
        $before = $this->snapshot('company');
        $disabled = $this->handoverRequest(array('postback' => 'postback', 'operation' => 'disable',
            'csrfToken' => 'valid', 'from' => (string) $this->from));
        self::assertStringContainsString('Account disabled', $disabled['message']);
        self::assertEquals(ACCESS_LEVEL_DISABLED, $this->users->get($this->from)['accessLevel']);
        self::assertSame($before, $this->snapshot('company'));
        $freshSession = clone $_SESSION['CATS'];
        $_SESSION = array('CATS' => $freshSession);
        $fresh = $this->handoverRequest();
        self::assertContains((string) $this->from, array_column($fresh['users'], 'userID'));
        self::assertNotContains((string) $this->from, array_column($fresh['replacements'], 'userID'));
        self::assertArrayNotHasKey('userHandover', $_SESSION);
        $preview = $this->handoverRequest(array('postback' => 'postback', 'operation' => 'preview',
            'csrfToken' => 'valid', 'from' => (string) $this->from, 'default' => (string) $this->to,
            'categories' => array('company')));
        self::assertSame(1, $preview['preview']['categories']['company']['eligible']);
        $result = $this->handoverRequest(array('postback' => 'postback', 'operation' => 'execute',
            'csrfToken' => 'valid', 'previewToken' => $preview['savedPreview']['token']));
        self::assertSame('transferred', $result['results']['company'][0]['state']);
    }

    public function testDepartingUserIsRejectedForDefaultAndEveryCategoryOverride(): void
    {
        $before = $this->snapshot('history');
        try { $this->users->previewHandover($this->from, (string) $this->from, array('company')); self::fail('Default must reject departing user.'); }
        catch (\InvalidArgumentException $e) { self::assertStringContainsString('different', $e->getMessage()); }
        foreach (array_keys(\Users::getHandoverCategories()) as $category)
        {
            try { $this->users->previewHandover($this->from, $this->to, array($category), array($category => (string) $this->from)); self::fail('Override must reject departing user.'); }
            catch (\InvalidArgumentException $e) { self::assertStringContainsString('differ', $e->getMessage()); }
        }
        // Submitted overrides must be valid even if that category's checkbox is not selected.
        try { $this->users->previewHandover($this->from, $this->to, array('company'), array('jobRecruiter' => (string) $this->from)); self::fail('Unselected override must reject departing user.'); }
        catch (\InvalidArgumentException $e) { self::assertStringContainsString('differ', $e->getMessage()); }
        self::assertSame($before, $this->snapshot('history'));
    }

    public function testCursorPagesTransferMoreThanFiftyInEveryCategoryExactlyOnce(): void
    {
        $f = $this->from; $c = $this->ids['company'];
        foreach (array('company', 'candidate', 'contact', 'joborder', 'task') as $table)
        {
            $values = array();
            for ($i = 0; $i < 105; $i++)
            {
                switch ($table)
                {
                    case 'company': $values[] = "('Page $i', $f, $f, 'Client')"; $columns = 'name, owner, entered_by, relationship_status'; break;
                    case 'candidate': $values[] = "('Page $i', 'Candidate', $f, $f, 1)"; $columns = 'first_name, last_name, owner, entered_by, is_active'; break;
                    case 'contact': $values[] = "('Page $i', 'Contact', $c, -1, $f, $f, 0)"; $columns = 'first_name, last_name, company_id, company_department_id, owner, entered_by, left_company'; break;
                    case 'joborder': $values[] = "('Page $i', $c, $f, $f, $f, 'Active')"; $columns = 'title, company_id, owner, recruiter, entered_by, status'; break;
                    case 'task': $values[] = "('Page $i', $f, $f, NOW(), NOW())"; $columns = 'title, assigned_to, created_by, date_created, date_modified'; break;
                }
            }
            $this->db->query("INSERT INTO $table ($columns) VALUES " . implode(',', $values));
        }
        $categories = array_keys(\Users::getHandoverCategories());
        $expected = array();
        foreach (\Users::getHandoverCategories() as $key => $d)
        {
            $criteria = array('task' => "status = 'open'", 'candidate' => 'is_active = 1 AND is_admin_hidden = 0',
                'contact' => 'left_company = 0', 'company' => "relationship_status = 'Client'", 'jobOwner' => "status = 'Active'", 'jobRecruiter' => "status = 'Active'")[$key];
            $expected[$key] = array_map('intval', array_column($this->db->getAllAssoc('SELECT ' . $d['id'] . ' AS id FROM ' . $d['table'] .
                ' WHERE ' . $d['column'] . ' = ' . $f . ' AND ' . $criteria . ' ORDER BY ' . $d['id']), 'id'));
        }
        $page = $this->handoverRequest(array('postback' => 'postback', 'operation' => 'preview', 'csrfToken' => 'valid',
            'from' => $f, 'default' => $this->to, 'categories' => $categories));
        $seen = array_fill_keys($categories, array());
        $pages = 0;
        do
        {
            self::assertNotNull($page['preview']);
            foreach ($page['preview']['categories'] as $key => $p) self::assertLessThanOrEqual(50, count($p['rows']));
            $hasMore = (bool) array_filter($page['preview']['categories'], fn($p) => $p['more']);
            $token = $page['savedPreview']['token'];
            $result = $this->handoverRequest(array('postback' => 'postback', 'operation' => 'execute', 'csrfToken' => 'valid', 'previewToken' => $token));
            foreach ($result['results'] as $key => $rows)
            {
                self::assertSame(array(), array_values(array_filter($rows, fn($r) => $r['state'] === 'failed')));
                foreach ($rows as $row) if ($row['state'] === 'transferred') $seen[$key][] = $row['id'];
            }
            $pages++;
            if ($hasMore) $page = $this->handoverRequest(array('postback' => 'postback', 'operation' => 'next', 'csrfToken' => 'valid', 'previewToken' => $token));
        } while ($hasMore && $pages < 5);
        self::assertSame(3, $pages);
        foreach ($categories as $key)
        {
            self::assertSame($expected[$key], $seen[$key]);
            self::assertCount(106, array_unique($seen[$key]));
        }
        $after = count($this->snapshot('history'));
        $remaining = $this->users->previewHandover($f, $this->to, $categories);
        foreach ($remaining['categories'] as $p) self::assertSame(0, $p['eligible']);
        $this->users->executeHandover($remaining, 'valid');
        self::assertSame($after, count($this->snapshot('history')));
    }

    public function testInterruptedAndPartiallyFailedPagesRecoverThroughFreshPreview(): void
    {
        $f = $this->from;
        $badCompany = $this->insert("INSERT INTO company (name, owner, entered_by, relationship_status) VALUES ('Fail middle', $f, $f, 'Client')");
        $lastCompany = $this->insert("INSERT INTO company (name, owner, entered_by, relationship_status) VALUES ('Last success', $f, $f, 'Client')");
        $badTask = (new \Tasks())->add(array('title' => 'Fail middle', 'assignedTo' => $f));
        (new \Tasks())->add(array('title' => 'Last success', 'assignedTo' => $f));
        $page = $this->handoverRequest(array('postback' => 'postback', 'operation' => 'preview', 'csrfToken' => 'valid',
            'from' => $f, 'default' => $this->to, 'categories' => array('company', 'task')));
        $prefix = $page['preview'];
        foreach ($prefix['categories'] as &$p) $p['rows'] = array_slice($p['rows'], 0, 1);
        unset($p);
        // Model a request stopping after its confirmation was consumed and first record commits.
        $_SESSION['userHandover']['executed'] = true;
        $this->users->executeHandover($prefix, 'valid');
        $history = count($this->snapshot('history'));
        $replay = $this->handoverRequest(array('postback' => 'postback', 'operation' => 'execute', 'csrfToken' => 'valid',
            'previewToken' => $page['savedPreview']['token']));
        self::assertNull($replay['results']);
        self::assertSame($history, count($this->snapshot('history')));
        $this->db->query("CREATE TRIGGER handover_page_failure BEFORE INSERT ON history FOR EACH ROW BEGIN IF
            (NEW.data_item_type = 200 AND NEW.data_item_id = $badCompany AND NEW.the_field = 'owner') OR
            (NEW.data_item_type = 1000 AND NEW.data_item_id = $badTask AND NEW.the_field = 'assignedTo')
            THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'fixture middle audit failure'; END IF; END");
        $retry = $this->users->previewHandover($f, $this->to, array('company', 'task'));
        $result = $this->users->executeHandover($retry, 'valid');
        foreach ($result as $rows)
        {
            self::assertCount(1, array_filter($rows, fn($r) => $r['state'] === 'failed'));
            self::assertCount(1, array_filter($rows, fn($r) => $r['state'] === 'transferred'));
        }
        self::assertEquals($f, (new \Companies())->get($badCompany)['owner']);
        self::assertEquals($f, (new \Tasks())->get($badTask)['assignedTo']);
        self::assertEquals($this->to, (new \Companies())->get($lastCompany)['owner']);
        $this->db->query('DROP TRIGGER handover_page_failure');
        $retry = $this->users->previewHandover($f, $this->to, array('company', 'task'));
        foreach ($retry['categories'] as $p) self::assertSame(1, $p['eligible']);
        $result = $this->users->executeHandover($retry, 'valid');
        foreach ($result as $rows) self::assertCount(1, array_filter($rows, fn($r) => $r['state'] === 'transferred'));
        $audits = $this->db->getAllAssoc("SELECT data_item_type, data_item_id, COUNT(*) AS n FROM history
            WHERE the_field IN ('owner','assignedTo') AND previous_value = '$f' GROUP BY data_item_type, data_item_id");
        self::assertCount(6, $audits);
        foreach ($audits as $audit) self::assertEquals(1, $audit['n']);
    }

    public function testAccessRevocationAndCallerTransactionsRemainEnforced(): void
    {
        $preview = $this->users->previewHandover($this->from, $this->to, array('company', 'task', 'candidate'));
        $history = $this->snapshot('history');
        $this->overrides['companies.edit'] = ACCESS_LEVEL_READ;
        $this->overrides['tasks.edit'] = ACCESS_LEVEL_READ;
        $this->overrides['candidates.hidden'] = ACCESS_LEVEL_READ;
        $this->db->query('UPDATE candidate SET is_admin_hidden = 1 WHERE candidate_id = ' . $this->ids['candidate']);
        $result = $this->users->executeHandover($preview, 'valid');
        foreach ($result as $rows) foreach ($rows as $row) self::assertNotSame('transferred', $row['state']);
        self::assertNull($result['candidate'][0]['id']);
        self::assertSame($history, $this->snapshot('history'));
        $this->overrides = array();
        $preview = $this->users->previewHandover($this->from, $this->to, array('company', 'task'));
        self::assertTrue($this->db->beginTransaction());
        $this->db->query("UPDATE company SET name = 'Caller transaction marker' WHERE company_id = " . $this->ids['company']);
        $result = $this->users->executeHandover($preview, 'valid');
        foreach ($result as $rows) self::assertSame('failed', $rows[0]['state']);
        self::assertTrue((new \ReflectionProperty(\DatabaseConnection::class, '_inTransaction'))->getValue($this->db));
        self::assertSame('Caller transaction marker', (new \Companies())->get($this->ids['company'])['name']);
        self::assertSame($history, $this->snapshot('history'));
        self::assertTrue($this->db->rollbackTransaction());
        self::assertSame('Current Company', (new \Companies())->get($this->ids['company'])['name']);
    }

    public function testEligibilityIsReadFromLockedRecordAfterConcurrentChange(): void
    {
        $preview = $this->users->previewHandover($this->from, $this->to, array('jobOwner'));
        $real = $this->db;
        $other = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
        $proxy = $this->createStub(\DatabaseConnection::class);
        foreach (array('query', 'getAllAssoc', 'makeQueryString', 'makeQueryStringOrNULL', 'makeQueryInteger', 'makeQueryIntegerOrNULL',
            'beginTransaction', 'commitTransaction', 'rollbackTransaction', 'getAffectedRows') as $method)
            $proxy->method($method)->willReturnCallback(fn(...$args) => $real->$method(...$args));
        $changed = false;
        $job = $this->ids['job'];
        $proxy->method('getAssoc')->willReturnCallback(function ($sql) use ($real, $other, $job, &$changed) {
            if (!$changed && str_contains($sql, 'FROM joborder WHERE joborder_id = ' . $job . ' FOR UPDATE'))
            {
                mysqli_query($other, "UPDATE joborder SET status = 'Closed' WHERE joborder_id = $job");
                $changed = true;
            }
            return $real->getAssoc($sql);
        });
        (new \ReflectionProperty(\DatabaseConnection::class, '_instance'))->setValue(null, $proxy);
        try { $result = (new \Users())->executeHandover($preview, 'valid'); }
        finally
        {
            (new \ReflectionProperty(\DatabaseConnection::class, '_instance'))->setValue(null, $real);
            mysqli_close($other);
        }
        self::assertTrue($changed);
        self::assertSame('skipped', $result['jobOwner'][0]['state']);
        self::assertEquals($this->from, (new \JobOrders())->get($job)['owner']);
    }


    public function testConcurrentRecipientDisableCannotUseAnOlderTransactionSnapshot(): void
    {
        $real = $this->db;
        $other = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
        try
        {
            foreach (array('company', 'task') as $category)
            {
                $real->query('UPDATE user SET access_level = 200 WHERE user_id = ' . $this->to);
                $preview = $this->users->previewHandover($this->from, $this->to, array($category));
                $proxy = $this->createStub(\DatabaseConnection::class);
                foreach (array('query', 'getAllAssoc', 'makeQueryString', 'makeQueryStringOrNULL', 'makeQueryInteger', 'makeQueryIntegerOrNULL',
                    'beginTransaction', 'commitTransaction', 'rollbackTransaction', 'getAffectedRows') as $method)
                    $proxy->method($method)->willReturnCallback(fn(...$args) => $real->$method(...$args));
                $disabled = false;
                $to = $this->to;
                $proxy->method('getAssoc')->willReturnCallback(function ($sql) use ($real, $other, $to, &$disabled) {
                    if (!$disabled && str_contains($sql, 'user.user_id = ' . $to) && str_ends_with($sql, ' FOR UPDATE'))
                    {
                        mysqli_query($other, 'UPDATE user SET access_level = 0 WHERE user_id = ' . $to);
                        $disabled = true;
                    }
                    return $real->getAssoc($sql);
                });
                (new \ReflectionProperty(\DatabaseConnection::class, '_instance'))->setValue(null, $proxy);
                try { $result = (new \Users())->executeHandover($preview, 'valid'); }
                finally { (new \ReflectionProperty(\DatabaseConnection::class, '_instance'))->setValue(null, $real); }
                self::assertTrue($disabled);
                self::assertSame('failed', $result[$category][0]['state']);
                $d = \Users::getHandoverCategories()[$category];
                $model = new $d['model']();
                self::assertEquals($this->from, $model->get($this->ids[$category])[$d['field']]);
            }
        }
        finally { mysqli_close($other); }
    }


}
