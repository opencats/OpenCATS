<?php
namespace OpenCATS\Tests\IntegrationTests;

/** Focused HTTP-handler coverage against the real Task model and a disposable DB. */
class TaskUITest extends DatabaseTestCase
{
    private $db;
    private $tasks;
    private $actor = 1;
    private $level = 400;
    private $acl = array();
    private $loggedIn = true;
    private $assignee;
    private $parents;
    private $pipeline;
    private $session;
    private $dateDMY = false;
    private $timezoneOffset = 0;

    protected function setUp(): void
    {
        parent::setUp();
        require_once LEGACY_ROOT . '/lib/CATSUtility.php';
        require_once LEGACY_ROOT . '/lib/UserInterface.php';
        require_once LEGACY_ROOT . '/lib/Template.php';
        require_once LEGACY_ROOT . '/lib/Session.php';
        require_once LEGACY_ROOT . '/modules/tasks/TasksUI.php';
        $this->actor = 1;
        $this->level = ACCESS_LEVEL_SA;
        $this->acl = array();
        $this->loggedIn = true;
        $this->session = $this->createStub(\CATSSession::class);
        $this->session->method('getUserID')->willReturnCallback(fn() => $this->actor);
        $this->session->method('getAccessLevel')->willReturnCallback(fn($key) => $this->acl[$key] ?? $this->level);
        $this->session->method('getRealAccessLevel')->willReturnCallback(fn() => $this->level);
        $this->session->method('isLoggedIn')->willReturnCallback(fn() => $this->loggedIn);
        $this->session->method('isCSRFTokenValid')->willReturnCallback(fn($token) => is_string($token) && $token === 'task-ui-token');
        $this->session->method('getCSRFToken')->willReturn('task-ui-token');
        $this->session->method('getColumnPreferences')->willReturn(array());
        $this->session->method('isDateDMY')->willReturnCallback(fn() => $this->dateDMY);
        $this->session->method('isTimeFormat24')->willReturn(false);
        $this->session->method('getTimeZoneOffset')->willReturnCallback(fn() => $this->timezoneOffset);
        $_SESSION = array('CATS' => $this->session);
        $_GET = $_POST = $_REQUEST = array();
        $this->db = \DatabaseConnection::getInstance();
        $this->tasks = new \Tasks();
        $this->db->query("INSERT INTO user (user_name, first_name, last_name, access_level) VALUES ('task-ui-assignee', 'UI', 'Assignee', 200)");
        $this->assignee = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO company (name, entered_by, owner) VALUES ('UI Company', 1, 1)");
        $company = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO contact (company_id, company_department_id, first_name, last_name, entered_by, owner) VALUES ($company, -1, 'UI', 'Contact', 1, 1)");
        $contact = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO candidate (first_name, last_name, entered_by, owner) VALUES ('UI', 'Candidate', 1, 1)");
        $candidate = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO joborder (company_id, title, entered_by, owner) VALUES ($company, 'UI Job', 1, 1)");
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

    private function request($action, $post = null, $get = array())
    {
        $_GET = array_merge(array('a' => $action), $get);
        $_POST = $post ?? array();
        $_REQUEST = array_merge($_GET, $_POST);
        $_SERVER['REQUEST_METHOD'] = $post === null ? 'GET' : 'POST';
        $result = array('redirect' => null, 'template' => null, 'data' => array());
        $template = $this->createStub(\Template::class);
        $template->method('assign')->willReturnCallback(function ($key, $value) use (&$result) { $result['data'][$key] = $value; });
        $template->method('display')->willReturnCallback(function ($path) use (&$result) { $result['template'] = $path; });
        $ui = new class($template, $result) extends \TasksUI {
            private $result;
            public function __construct($template, &$result) { parent::__construct(); $this->_template = $template; $this->result =& $result; }
            protected function redirectToTask($id, $notice) { $this->result['redirect'] = array($id, $notice); }
            protected function sendTaskJSON($response) { $this->result['json'] = $response; }
        };
        $ui->handleRequest();
        return $result;
    }

    private function post($id, $values = array())
    {
        return array_merge(array('csrfToken' => 'task-ui-token', 'taskID' => (string) $id), $values);
    }

    private function create($values = array())
    {
        return $this->tasks->add(array_merge(array('title' => 'UI Task', 'assignedTo' => $this->assignee), $values));
    }

    private function denied($result)
    {
        self::assertNull($result['redirect']);
        self::assertNotEmpty($result['data']['error']);
    }

    public function testCreateEditLifecycleAndActivityHandoff(): void
    {
        foreach ($this->parents as $type => $parent)
        {
            $form = $this->request('add', null, array('dataItemType' => (string) $type, 'dataItemID' => (string) $parent));
            self::assertSame((string) $parent, $form['data']['task']['dataItemID']);
            $result = $this->request('add', array('csrfToken' => 'task-ui-token', 'title' => '<Task & title>',
                'description' => '<script>alert(1)</script>', 'dataItemType' => (string) $type, 'dataItemID' => (string) $parent));
            self::assertNotNull($result['redirect'], $result['data']['error'] ?? '');
            $id = $result['redirect'][0];
            self::assertNull($this->tasks->get($id)['assignedTo']);
            $edit = $this->request('edit', $this->post($id, array('dueDate' => '02-28-30', 'assignedTo' => (string) $this->assignee, 'purpose' => 'job_follow_up')));
            self::assertNotNull($edit['redirect']);
            self::assertSame('2030-02-28', $this->tasks->get($id)['dueDate']);
            foreach (array(array('completeLog', 'completed'), array('reopen', 'open'), array('cancel', 'cancelled'), array('reopen', 'open')) as list($action, $status))
            {
                $result = $this->request($action, $this->post($id));
                self::assertNotNull($result['redirect']);
                self::assertSame($status, $this->tasks->get($id)['status']);
            }
            $this->request('reopen', $this->post($id));
            $this->request('complete', $this->post($id));
            $history = count($this->tasks->getHistory($id));
            $this->request('completeLog', $this->post($id));
            self::assertCount($history, $this->tasks->getHistory($id));
            self::assertTrue($this->tasks->getActionPermissions($id)['reopen']);
            self::assertFalse($this->tasks->getActionPermissions($id)['complete']);
        }
        self::assertSame('0', (string) $this->db->getAssoc('SELECT COUNT(*) AS n FROM activity')['n']);
        $result = $this->request('add', array('csrfToken' => 'task-ui-token', 'title' => 'Personal', 'assignedTo' => (string) $this->assignee));
        self::assertNotNull($result['redirect']);
        self::assertNull($this->tasks->get($result['redirect'][0])['dataItemID']);
    }

    public function testCrossUserAssignmentRelinkingAndParentVisibility(): void
    {
        $id = $this->create(array('dataItemType' => DATA_ITEM_COMPANY, 'dataItemID' => $this->parents[DATA_ITEM_COMPANY]));
        $this->level = ACCESS_LEVEL_EDIT;
        $this->actor = $this->assignee;
        $this->acl['companies.edit'] = ACCESS_LEVEL_READ;
        self::assertNotNull($this->request('edit', $this->post($id, array('title' => 'Ordinary edit')))['redirect']);
        self::assertTrue($this->tasks->getActionPermissions($id)['edit']);
        self::assertFalse($this->tasks->getActionPermissions($id)['manage']);
        foreach (array(array('assignedTo' => '1'), array('dataItemType' => '', 'dataItemID' => ''),
            array('candidateJobOrderID' => (string) $this->pipeline)) as $values)
            $this->denied($this->request('edit', $this->post($id, $values)));
        self::assertSame((string) $this->assignee, (string) $this->tasks->get($id)['assignedTo']);
        self::assertNotNull($this->request('complete', $this->post($id))['redirect']);
        $this->acl['companies.show'] = ACCESS_LEVEL_DISABLED;
        $this->denied($this->request('show', null, array('taskID' => (string) $id)));
        $this->denied($this->request('reopen', $this->post($id)));
        unset($this->acl['companies.show']);
        $this->actor = 99999;
        $this->denied($this->request('edit', $this->post($id, array('title' => 'Other user'))));
        $this->actor = 1;
        $this->level = ACCESS_LEVEL_SA;
        $standalone = $this->create();
        $hidden = $this->create(array('dataItemType' => DATA_ITEM_CANDIDATE, 'dataItemID' => $this->parents[DATA_ITEM_CANDIDATE]));
        $pipeline = $this->create(array('candidateJobOrderID' => $this->pipeline));
        $this->db->query('UPDATE candidate SET is_admin_hidden = 1 WHERE candidate_id = ' . $this->parents[DATA_ITEM_CANDIDATE]);
        $this->level = ACCESS_LEVEL_EDIT;
        $this->actor = 99999;
        foreach (array($standalone, $hidden, $pipeline) as $task)
        {
            $this->denied($this->request('show', null, array('taskID' => (string) $task)));
            $this->denied($this->request('complete', $this->post($task)));
        }
        $this->actor = $this->assignee;
        $this->denied($this->request('show', null, array('taskID' => (string) $pipeline)));
        $this->acl['tasks.complete'] = ACCESS_LEVEL_READ;
        $this->denied($this->request('complete', $this->post($standalone)));
    }

    public function testCsrfMalformedIdsInputsAndTransitions(): void
    {
        $id = $this->create();
        foreach (array(null, '', 'wrong', array('task-ui-token')) as $token)
            $this->denied($this->request('edit', array('taskID' => (string) $id, 'title' => 'forged', 'csrfToken' => $token)));
        foreach (array('complete', 'cancel', 'reopen', 'completeLog') as $action)
            $this->denied($this->request($action, null, array('taskID' => (string) $id)));
        foreach (array('0', '-1', '1e2', '2147483648', '999999', array('1')) as $bad)
        {
            $this->denied($this->request('show', null, array('taskID' => $bad)));
            $this->denied($this->request('complete', array('taskID' => $bad, 'csrfToken' => 'task-ui-token')));
        }
        foreach (array('title', 'description', 'assignedTo', 'dueDate', 'priority', 'status', 'purpose', 'dataItemType', 'dataItemID', 'candidateJobOrderID') as $field)
            $this->denied($this->request('edit', $this->post($id, array($field => array('bad')))));
        foreach (array(array('dueDate' => '2030-02-30'), array('priority' => 'urgent'), array('createdBy' => '5'), array('title' => '')) as $bad)
            $this->denied($this->request('edit', $this->post($id, $bad)));
        $this->denied($this->request(array('edit'), $this->post($id)));
        $this->denied($this->request('complete', $this->post($id, array('title' => 'forged'))));
        self::assertSame('UI Task', $this->tasks->get($id)['title']);
        $this->request('complete', $this->post($id));
        $this->denied($this->request('cancel', $this->post($id)));
        self::assertSame('completed', $this->tasks->get($id)['status']);
        $this->loggedIn = false;
        $this->denied($this->request('edit', $this->post($id, array('title' => 'logged out'))));
    }

    public function testScopedGridDatesCountsPagingAndEscapedRows(): void
    {
        $hidden = $this->create(array('dataItemType' => DATA_ITEM_CANDIDATE, 'dataItemID' => $this->parents[DATA_ITEM_CANDIDATE]));
        $this->db->query('UPDATE candidate SET is_admin_hidden = 1 WHERE candidate_id = ' . $this->parents[DATA_ITEM_CANDIDATE]);
        foreach (range(1, 17) as $n) $this->create(array('title' => '<Task & ' . $n . '>', 'dueDate' => '2000-01-01'));
        $this->create(array('title' => 'Today', 'dueDate' => date('Y-m-d')));
        $this->create(array('title' => 'Upcoming', 'dueDate' => '2099-01-01'));
        $this->create(array('title' => 'Undated'));
        $this->create(array('title' => 'Closed', 'status' => 'completed'));
        $this->actor = $this->assignee;
        $this->level = ACCESS_LEVEL_EDIT;
        $result = $this->request('list');
        self::assertSame('', $result['data']['error']);
        $grid = $result['data']['dataGrid'];
        self::assertSame(20, $grid->getNumberOfRows());
        $rows = (new \ReflectionProperty(\DataGrid::class, '_rs'))->getValue($grid);
        self::assertCount(15, $rows);
        self::assertNotContains($hidden, array_map('intval', array_column($rows, 'taskID')));
        self::assertStringContainsString('&lt;Task &amp;', \TasksDataGrid::renderCell($rows[0], 'title'));
        self::assertStringNotContainsString('<Task', \TasksDataGrid::renderCell($rows[0], 'title'));
        foreach (array('overdue' => 17, 'today' => 1, 'upcoming' => 1, 'undated' => 1) as $due => $count)
        {
            $result = $this->request('list', null, array('due' => $due));
            self::assertSame($count, $result['data']['dataGrid']->getNumberOfRows());
        }
        $result = $this->request('list', null, array('parameters' . 'tasks:TasksDataGrid' => json_encode(array('rangeStart' => 15, 'maxResults' => 15))));
        $rows = (new \ReflectionProperty(\DataGrid::class, '_rs'))->getValue($result['data']['dataGrid']);
        self::assertCount(5, $rows);
        $result = $this->request('list', null, array('status' => 'completed'));
        self::assertSame(1, $result['data']['dataGrid']->getNumberOfRows());
        foreach (array(array('due' => array('today')), array('dataItemType' => '999'), array('assignedTo' => '-1'),
            array('dueFrom' => '2026-02-30'), array('dataItemID' => '2'), array('parameters' . 'tasks:TasksDataGrid' => 'null')) as $bad)
        {
            $this->denied($this->request('list', null, $bad));
        }
        $this->acl['tasks.list'] = 0;
        $this->denied($this->request('list'));
    }

    public function testPipelineSuggestionsAndActivityHandoffRespectBothParents(): void
    {
        $candidate = $this->parents[DATA_ITEM_CANDIDATE];
        $job = $this->parents[DATA_ITEM_JOBORDER];
        $id = $this->create(array('dataItemType' => DATA_ITEM_CANDIDATE, 'dataItemID' => $candidate,
            'candidateJobOrderID' => $this->pipeline));
        self::assertArrayHasKey($this->pipeline, $this->tasks->getPipelineChoices(DATA_ITEM_CANDIDATE, $candidate));
        self::assertArrayHasKey($this->pipeline, $this->tasks->getPipelineChoices(DATA_ITEM_JOBORDER, $job));
        $form = $this->request('edit', null, array('taskID' => (string) $id));
        self::assertArrayHasKey($this->pipeline, $form['data']['pipelineChoices']);
        $result = $this->request('completeLog', $this->post($id));
        self::assertSame('activity', $result['redirect'][1]);
        $show = $this->request('show', null, array('taskID' => (string) $id, 'notice' => 'activity'));
        self::assertSame((string) $candidate, (string) $show['data']['activityTarget']['candidateID']);
        self::assertSame((string) $job, (string) $show['data']['activityTarget']['jobOrderID']);
        $this->level = ACCESS_LEVEL_EDIT;
        $this->db->query('UPDATE joborder SET is_admin_hidden = 1 WHERE joborder_id = ' . $job);
        self::assertSame(array(), $this->tasks->getPipelineChoices(DATA_ITEM_CANDIDATE, $candidate));
        self::assertSame(array(), $this->tasks->getActivityTarget($id));
        $this->denied($this->request('show', null, array('taskID' => (string) $id)));
        $this->denied($this->request('add', array('csrfToken' => 'task-ui-token', 'title' => 'Hidden context',
            'assignedTo' => (string) $this->assignee, 'candidateJobOrderID' => (string) $this->pipeline)));
    }

    public function testTemplatesEscapeTextAndExposeNativeForms(): void
    {
        require_once LEGACY_ROOT . '/lib/TemplateUtility.php';
        require_once LEGACY_ROOT . '/lib/ModuleUtility.php';
        require_once LEGACY_ROOT . '/lib/MRU.php';
        $mru = $this->createStub(\MRU::class);
        $mru->method('getFormatted')->willReturn('');
        $this->session->method('getMRU')->willReturn($mru);
        $_SESSION['modules'] = array('tasks' => array('TasksUI', 'Tasks', array(), array(), array()));
        $_SERVER['PHP_SELF'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php?m=tasks';
        $id = $this->create(array('title' => '<svg onload=alert(1)>', 'description' => '<script>alert(1)</script> & plain'));
        foreach (array('show', 'edit', 'add', 'list') as $action)
        {
            $result = $this->request($action, null, array('taskID' => (string) $id, 'assignedTo' => 'all'));
            self::assertSame('', $result['data']['error']);
            $template = new \Template();
            foreach ($result['data'] as $key => $value) $template->assign($key, $value);
            ob_start();
            try { $template->display($result['template']); $html = ob_get_contents(); }
            finally { ob_end_clean(); }
            self::assertStringNotContainsString('<svg onload=alert(1)>', $html);
            self::assertStringNotContainsString('<script>alert(1)</script>', $html);
            if ($action === 'show' || $action === 'edit')
            {
                self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt; &amp; plain', $html);
                self::assertStringContainsString('&lt;svg onload=alert(1)&gt;', $html);
            }
            if ($action === 'show')
            {
                self::assertStringNotContainsString('!newEntry!', $html);
                self::assertStringContainsString('Created', $html);
            }
            if ($action === 'add' || $action === 'edit')
            {
                self::assertStringContainsString('method="post"', $html);
                self::assertStringContainsString('name="csrfToken" value="task-ui-token"', $html);
                self::assertStringContainsString('name="candidateJobOrderID"', $html);
            }
        }
        foreach ($this->parents as $type => $parent)
        {
            $this->create(array('dataItemType' => $type, 'dataItemID' => $parent, 'title' => '<parent-task>'));
            ob_start();
            try { \TasksUI::showForParent($type, $parent); $html = ob_get_contents(); }
            finally { ob_end_clean(); }
            self::assertStringContainsString('&lt;parent-task&gt;', $html);
            self::assertStringNotContainsString('<parent-task>', $html);
            self::assertStringContainsString('Add Task', $html);
        }
    }

    public function testStandalonePipelineActivityHandoffRendering(): void
    {
        require_once LEGACY_ROOT . '/lib/TemplateUtility.php';
        require_once LEGACY_ROOT . '/lib/ModuleUtility.php';
        require_once LEGACY_ROOT . '/lib/MRU.php';
        $mru = $this->createStub(\MRU::class);
        $mru->method('getFormatted')->willReturn('');
        $this->session->method('getMRU')->willReturn($mru);
        $_SESSION['modules'] = array('tasks' => array('TasksUI', 'Tasks', array(), array(), array()));
        $_SERVER['PHP_SELF'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php?m=tasks';
        $id = $this->create(array('candidateJobOrderID' => $this->pipeline));
        $this->request('completeLog', $this->post($id));
        foreach (array(ACCESS_LEVEL_EDIT, ACCESS_LEVEL_READ) as $access)
        {
            $this->acl['pipelines.addActivity'] = $access;
            $result = $this->request('show', null, array('taskID' => (string) $id, 'notice' => 'activity'));
            $template = new \Template();
            foreach ($result['data'] as $key => $value) $template->assign($key, $value);
            ob_start();
            try { $template->display($result['template']); $html = ob_get_contents(); }
            finally { ob_end_clean(); }
            self::assertStringNotContainsString('href=""', $html);
            if ($access === ACCESS_LEVEL_EDIT)
            {
                self::assertStringContainsString('Open Activity form', $html);
                self::assertStringContainsString('candidateID=' . $this->parents[DATA_ITEM_CANDIDATE] . '&amp;jobOrderID=' . $this->parents[DATA_ITEM_JOBORDER], $html);
            }
            else
            {
                self::assertStringContainsString('You do not have permission to log an Activity', $html);
                self::assertStringNotContainsString('Open Activity form', $html);
            }
        }
        self::assertSame('completed', $this->tasks->get($id)['status']);
        self::assertSame('0', (string) $this->db->getAssoc('SELECT COUNT(*) AS n FROM activity')['n']);
    }

    public function testQuickAddContextAndCompactTemplateForAllParents(): void
    {
        $names = array(DATA_ITEM_CANDIDATE => 'UI Candidate', DATA_ITEM_CONTACT => 'UI Contact',
            DATA_ITEM_COMPANY => 'UI Company', DATA_ITEM_JOBORDER => 'UI Job');
        foreach ($this->parents as $type => $id)
        {
            $result = $this->request('quickAdd', null, array('dataItemType' => (string) $type, 'dataItemID' => (string) $id));
            self::assertSame('./modules/tasks/QuickAdd.tpl', $result['template']);
            self::assertSame($names[$type], $result['data']['context']['name']);
            self::assertSame(in_array($type, array(DATA_ITEM_CONTACT, DATA_ITEM_JOBORDER)) ? 'UI Company' : '', $result['data']['context']['companyName']);
            self::assertSame(\Tasks::getDefaults()['purpose'], $result['data']['task']['purpose']);
            self::assertSame(\Tasks::getDefaults()['status'], $result['data']['task']['status']);
            $template = new \Template();
            foreach ($result['data'] as $key => $value) $template->assign($key, $value);
            ob_start();
            try { $template->display($result['template']); $html = ob_get_contents(); }
            finally { ob_end_clean(); }
            foreach (array('modal-lg', 'modal-dialog-centered', 'modal-dialog-scrollable', 'form-control-sm', 'form-select-sm',
                'rows="3"', 'class="collapse"', 'name="csrfToken" value="task-ui-token"', 'class="modal-footer py-2"') as $fragment)
                self::assertStringContainsString($fragment, $html);
            self::assertStringContainsString('type="hidden" name="dataItemID" value="' . $id . '"', $html);
            self::assertStringNotContainsString('Parent record ID', $html);
            self::assertStringNotContainsString('name="dataItemType"><option', $html);
        }
        $this->db->query("UPDATE company SET name = '<svg onload=alert(1)> & Company' WHERE company_id = " . $this->parents[DATA_ITEM_COMPANY]);
        $result = $this->request('quickAdd', null, array('dataItemType' => (string) DATA_ITEM_CONTACT, 'dataItemID' => (string) $this->parents[DATA_ITEM_CONTACT]));
        $template = new \Template();
        foreach ($result['data'] as $key => $value) $template->assign($key, $value);
        ob_start();
        try { $template->display($result['template']); $html = ob_get_contents(); }
        finally { ob_end_clean(); }
        self::assertStringContainsString('&lt;svg onload=alert(1)&gt; &amp; Company', $html);
        self::assertStringNotContainsString('<svg onload=alert(1)>', $html);
    }

    public function testQuickAddSavesDefaultsAndReturnsOnlyScopedEscapedOpenRows(): void
    {
        foreach ($this->parents as $type => $id)
        {
            $result = $this->request('quickAdd', array('csrfToken' => 'task-ui-token', 'title' => '<Quick & Task>',
                'description' => '<script>plain text</script>', 'dataItemType' => (string) $type, 'dataItemID' => (string) $id));
            self::assertTrue($result['json']['success']);
            self::assertStringContainsString('&lt;Quick &amp; Task&gt;', $result['json']['html']);
            self::assertStringNotContainsString('<Quick & Task>', $result['json']['html']);
            $task = $this->tasks->get($result['json']['taskID']);
            self::assertSame('open', $task['status']);
            self::assertSame('general', $task['purpose']);
            self::assertSame('normal', $task['priority']);
            self::assertNull($task['assignedTo']);
            self::assertSame('<script>plain text</script>', $task['description']);
        }
        $type = DATA_ITEM_COMPANY;
        $id = $this->parents[$type];
        $closed = $this->request('quickAdd', array('csrfToken' => 'task-ui-token', 'title' => 'Already closed',
            'dataItemType' => (string) $type, 'dataItemID' => (string) $id, 'status' => 'completed'));
        self::assertTrue($closed['json']['success']);
        self::assertStringNotContainsString('Already closed', $closed['json']['html']);
        self::assertSame('completed', $this->tasks->get($closed['json']['taskID'])['status']);
        $this->acl['tasks.list'] = ACCESS_LEVEL_DISABLED;
        $noList = $this->request('quickAdd', array('csrfToken' => 'task-ui-token', 'title' => 'Permitted creation',
            'dataItemType' => (string) $type, 'dataItemID' => (string) $id));
        self::assertTrue($noList['json']['success']);
        self::assertStringNotContainsString('&lt;Quick', $noList['json']['html']);
    }

    public function testQuickAddRejectsCsrfMalformedContextAndInvalidFields(): void
    {
        $valid = array('csrfToken' => 'task-ui-token', 'title' => 'Not saved', 'dataItemType' => (string) DATA_ITEM_COMPANY,
            'dataItemID' => (string) $this->parents[DATA_ITEM_COMPANY]);
        foreach (array(array('csrfToken' => ''), array('csrfToken' => array('task-ui-token')), array('title' => ''),
            array('title' => array('bad')), array('dataItemType' => array('200')), array('dataItemType' => '999'),
            array('dataItemID' => '0'), array('dataItemID' => '2147483648'), array('dataItemID' => '999999'),
            array('dataItemID' => ''), array('dueDate' => '2026-02-30'), array('priority' => 'urgent'),
            array('assignedTo' => '999999'), array('createdBy' => '2'), array('purpose' => array('general'))) as $bad)
        {
            $result = $this->request('quickAdd', array_replace($valid, $bad));
            self::assertFalse($result['json']['success']);
            self::assertNotEmpty($result['json']['message']);
        }
        foreach (array(array(), array('dataItemType' => '200', 'dataItemID' => array('2')),
            array('dataItemType' => '200', 'dataItemID' => '999999')) as $bad)
            self::assertFalse($this->request('quickAdd', null, $bad)['json']['success']);
        self::assertSame('0', (string) $this->db->getAssoc('SELECT COUNT(*) AS n FROM task')['n']);
    }

    public function testQuickAddParentPermissionsAndPipelineVisibility(): void
    {
        $this->level = ACCESS_LEVEL_EDIT;
        $companyID = $this->parents[DATA_ITEM_COMPANY];
        $values = array('csrfToken' => 'task-ui-token', 'title' => 'No privilege escalation',
            'dataItemType' => (string) DATA_ITEM_COMPANY, 'dataItemID' => (string) $companyID);
        foreach (array('tasks.add', 'tasks.show', 'companies.show', 'companies.edit') as $permission)
        {
            $this->acl[$permission] = ACCESS_LEVEL_DISABLED;
            self::assertFalse($this->request('quickAdd', null, $values)['json']['success']);
            self::assertFalse($this->request('quickAdd', $values)['json']['success']);
            unset($this->acl[$permission]);
        }
        $this->db->query('UPDATE candidate SET is_admin_hidden = 1 WHERE candidate_id = ' . $this->parents[DATA_ITEM_CANDIDATE]);
        $hidden = array_replace($values, array('dataItemType' => (string) DATA_ITEM_CANDIDATE, 'dataItemID' => (string) $this->parents[DATA_ITEM_CANDIDATE]));
        self::assertFalse($this->request('quickAdd', null, $hidden)['json']['success']);
        self::assertFalse($this->request('quickAdd', $hidden)['json']['success']);
        self::assertFalse($this->request('quickAdd', $values + array('candidateJobOrderID' => (string) $this->pipeline))['json']['success']);
        $jobForm = $this->request('quickAdd', null, array('dataItemType' => (string) DATA_ITEM_JOBORDER, 'dataItemID' => (string) $this->parents[DATA_ITEM_JOBORDER]));
        self::assertSame(array(), $jobForm['data']['pipelineChoices']);
        $this->acl['companies.show'] = ACCESS_LEVEL_DISABLED;
        $contactForm = $this->request('quickAdd', null, array('dataItemType' => (string) DATA_ITEM_CONTACT, 'dataItemID' => (string) $this->parents[DATA_ITEM_CONTACT]));
        self::assertSame('', $contactForm['data']['context']['companyName']);
        $this->loggedIn = false;
        self::assertFalse($this->request('quickAdd', $values)['json']['success']);
        self::assertSame('0', (string) $this->db->getAssoc('SELECT COUNT(*) AS n FROM task')['n']);
    }

    public function testHistoryFormerParentIsNotDisclosedAndDisabledAssignmentRetained(): void
    {
        $id = $this->create(array('dataItemType' => DATA_ITEM_CANDIDATE, 'dataItemID' => $this->parents[DATA_ITEM_CANDIDATE]));
        $this->tasks->update($id, array('dataItemType' => DATA_ITEM_COMPANY, 'dataItemID' => $this->parents[DATA_ITEM_COMPANY]));
        $this->db->query('UPDATE candidate SET is_admin_hidden = 1 WHERE candidate_id = ' . $this->parents[DATA_ITEM_CANDIDATE]);
        $this->db->query('UPDATE user SET access_level = 0 WHERE user_id = ' . $this->assignee);
        $this->actor = 1;
        $this->level = ACCESS_LEVEL_EDIT;
        $result = $this->request('show', null, array('taskID' => (string) $id));
        self::assertSame(array(), $result['data']['history']);
        self::assertStringContainsString('(inactive)', $result['data']['users'][$this->assignee]);
        self::assertNotNull($this->request('edit', $this->post($id, array('title' => 'Retain disabled')))['redirect']);
        $this->denied($this->request('add', array('csrfToken' => 'task-ui-token', 'title' => 'Disabled', 'assignedTo' => (string) $this->assignee)));
    }
    public function testConfiguredTaskDatesRoundTripAndPreserveRejectedInput(): void
    {
        foreach (array(false => '02-29-28', true => '29-02-28') as $dmy => $display)
        {
            $this->dateDMY = (bool) $dmy;
            $this->timezoneOffset = -12;
            $values = array('csrfToken' => 'task-ui-token', 'title' => 'Configured date',
                'assignedTo' => (string) $this->assignee, 'dueDate' => $display);
            $result = $this->request('add', $values);
            self::assertNotNull($result['redirect']);
            $id = $result['redirect'][0];
            self::assertSame('2028-02-29', $this->tasks->get($id)['dueDate']);
            $edit = $this->request('edit', null, array('taskID' => (string) $id));
            self::assertSame($display, $edit['data']['task']['dueDate']);
            self::assertSame($display, \TasksDataGrid::renderCell($this->tasks->get($id), 'dueDate'));
            $badDate = $dmy ? '30-02-28' : '02-30-28';
            $bad = $this->request('edit', $this->post($id, array('title' => 'Keep this title', 'dueDate' => $badDate)));
            self::assertNull($bad['redirect']);
            self::assertSame($badDate, $bad['data']['task']['dueDate']);
            self::assertSame('Keep this title', $bad['data']['task']['title']);
            self::assertSame('2028-02-29', $this->tasks->get($id)['dueDate']);
            $context = array('dataItemType' => (string) DATA_ITEM_CONTACT, 'dataItemID' => (string) $this->parents[DATA_ITEM_CONTACT]);
            $quick = $this->request('quickAdd', array_merge($values, $context));
            self::assertTrue($quick['json']['success']);
            self::assertSame('2028-02-29', $this->tasks->get($quick['json']['taskID'])['dueDate']);
            self::assertStringContainsString($display, $quick['json']['html']);
            $invalid = $this->request('quickAdd', array_merge($values, $context, array('dueDate' => $badDate)));
            self::assertFalse($invalid['json']['success']);
            $csrf = $this->request('quickAdd', array_merge($values, $context, array('csrfToken' => 'invalid')));
            self::assertFalse($csrf['json']['success']);
            $this->acl['contacts.edit'] = ACCESS_LEVEL_READ;
            self::assertFalse($this->request('quickAdd', array_merge($values, $context))['json']['success']);
            unset($this->acl['contacts.edit']);
            $cleared = $this->request('edit', $this->post($id, array('dueDate' => '')));
            self::assertNotNull($cleared['redirect']);
            self::assertNull($this->tasks->get($id)['dueDate']);
        }
    }

    public function testConfiguredDateFiltersAndRenderedTaskDates(): void
    {
        require_once LEGACY_ROOT . '/lib/TemplateUtility.php';
        require_once LEGACY_ROOT . '/lib/ModuleUtility.php';
        require_once LEGACY_ROOT . '/lib/MRU.php';
        $mru = $this->createStub(\MRU::class);
        $mru->method('getFormatted')->willReturn('');
        $this->session->method('getMRU')->willReturn($mru);
        $_SESSION['modules'] = array('tasks' => array('TasksUI', 'Tasks', array(), array(), array()));
        $_SERVER['PHP_SELF'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php?m=tasks';
        $id = $this->create(array('dueDate' => '2028-02-29'));
        $this->tasks->update($id, array('dueDate' => '2028-03-01'));
        $this->create(array('dueDate' => '2028-03-02'));
        $this->create();
        foreach (array(false, true) as $dmy)
        {
            $this->dateDMY = $dmy;
            $from = $dmy ? '29-02-28' : '02-29-28';
            $to = $dmy ? '01-03-28' : '03-01-28';
            $filters = array('assignedTo' => 'all', 'dueFrom' => $from, 'dueTo' => $to);
            $list = $this->request('list', null, $filters);
            self::assertSame(1, $list['data']['dataGrid']->getNumberOfRows());
            self::assertSame($from, \TasksDataGrid::getTaskFilters($filters)['dueFrom']);
            $this->denied($this->request('list', null, array_merge($filters, array('dueFrom' => $to, 'dueTo' => $from))));
            foreach (array('show', 'edit', 'add', 'list', 'quickAdd') as $action)
            {
                $result = $this->request($action, null, array_merge($filters, array('taskID' => (string) $id,
                    'dataItemType' => (string) DATA_ITEM_CONTACT, 'dataItemID' => (string) $this->parents[DATA_ITEM_CONTACT])));
                $template = new \Template();
                foreach ($result['data'] as $key => $value) $template->assign($key, $value);
                ob_start();
                try { $template->display($result['template']); $html = ob_get_contents(); }
                finally { ob_end_clean(); }
                self::assertStringNotContainsString('type="date"', $html);
                self::assertStringNotContainsString('2028-03-01', $html);
                if ($action === 'show')
                {
                    self::assertStringContainsString($from, $html); // previous history due date
                    self::assertStringContainsString($to, $html);
                    self::assertStringContainsString(\TaskPresentation::dateTime($result['data']['task']['dateCreated']), $html);
                    self::assertStringContainsString(\TaskPresentation::dateTime($result['data']['history'][0]['setDate']), $html);
                }
                else self::assertStringContainsString('data-date-format="' . ($dmy ? 'DD-MM-YY' : 'MM-DD-YY') . '"', $html);
                if ($action === 'edit') self::assertStringContainsString('value="' . $to . '"', $html);
            }
        }
    }

    public function testGridParentNamesLinksCompanyVisibilityAndEscaping(): void
    {
        $names = array(DATA_ITEM_CANDIDATE => 'UI Candidate', DATA_ITEM_CONTACT => 'UI Contact',
            DATA_ITEM_COMPANY => 'UI Company', DATA_ITEM_JOBORDER => 'UI Job');
        foreach ($this->parents as $type => $id) $this->create(array('dataItemType' => $type, 'dataItemID' => $id));
        $this->create();
        $getRows = function () {
            $result = $this->request('list', null, array('assignedTo' => 'all'));
            return (new \ReflectionProperty(\DataGrid::class, '_rs'))->getValue($result['data']['dataGrid']);
        };
        foreach ($getRows() as $row)
        {
            $html = \TasksDataGrid::renderCell($row, 'dataItemID');
            if ($row['dataItemType'] === null) { self::assertSame('Standalone', $html); continue; }
            $type = $row['dataItemType'];
            self::assertStringContainsString($names[$type], $html);
            self::assertStringContainsString(\Template::escapeAttr(\TasksUI::parentURL($type, $row['dataItemID'])), $html);
            self::assertStringContainsString(\Tasks::getParentTypes()[$type] . ' #' . $row['dataItemID'], $html);
            if (in_array($type, array(DATA_ITEM_CONTACT, DATA_ITEM_JOBORDER))) self::assertStringContainsString('UI Company', $html);
        }
        $this->db->query("UPDATE company SET name = '<svg onload=alert(1)> & Company' WHERE company_id = " . $this->parents[DATA_ITEM_COMPANY]);
        foreach ($getRows() as $row)
        {
            $html = \TasksDataGrid::renderCell($row, 'dataItemID');
            self::assertStringNotContainsString('<svg', $html);
            if (in_array($row['dataItemType'], array(DATA_ITEM_COMPANY, DATA_ITEM_CONTACT, DATA_ITEM_JOBORDER)))
                self::assertStringContainsString('&lt;svg onload=alert(1)&gt; &amp; Company', $html);
        }
        $this->level = ACCESS_LEVEL_EDIT;
        $this->actor = $this->assignee;
        $this->acl['companies.show'] = 0;
        $this->db->query('UPDATE candidate SET is_admin_hidden = 1 WHERE candidate_id = ' . $this->parents[DATA_ITEM_CANDIDATE]);
        $this->db->query('UPDATE joborder SET is_admin_hidden = 1 WHERE joborder_id = ' . $this->parents[DATA_ITEM_JOBORDER]);
        $rows = $getRows();
        self::assertCount(2, $rows); // Contact and standalone only
        foreach ($rows as $row)
        {
            $html = \TasksDataGrid::renderCell($row, 'dataItemID');
            self::assertStringNotContainsString('Company', $html);
            self::assertStringNotContainsString('UI Candidate', $html);
            self::assertStringNotContainsString('UI Job', $html);
            self::assertStringNotContainsString('m=companies', $html);
        }
        $this->acl['contacts.show'] = 0;
        self::assertCount(1, $getRows());
    }

}
