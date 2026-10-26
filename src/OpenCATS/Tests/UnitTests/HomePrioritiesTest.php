<?php
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class HomePrioritiesTest extends AuthorizationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once './modules/home/HomeUI.php';
        require_once './lib/WarningReviews.php';
        require_once './modules/tasks/TasksUI.php';
    }

    private function task($id = 7): array
    {
        return array('taskID' => $id, 'title' => 'Call <client>', 'description' => 'Actual follow-up',
            'dueDate' => '2026-10-01', 'priority' => 'normal', 'status' => 'open', 'purpose' => 'general',
            'assignedTo' => 10, 'createdBy' => 10, 'dataItemType' => null, 'dataItemID' => null,
            'candidateJobOrderID' => null, 'dateModified' => '2026-10-01 12:00:00',
            'dateCreated' => '2026-10-01 12:00:00', 'dateCompleted' => null, 'completedBy' => null);
    }

    private function warning($code, $id, $taskID = null): array
    {
        return array('code' => $code, 'entityType' => $code === 'W01' ? DATA_ITEM_TASK : DATA_ITEM_JOBORDER,
            'entityID' => $id, 'taskID' => $taskID, 'title' => 'Source warning', 'reason' => 'no_recorded_submission');
    }

    public function testQueueGroupsCommitmentsWithoutTruncatingOrChangingWarningCollection(): void
    {
        $tasks = array($this->task(), array_replace($this->task(8), array('dueDate' => null)));
        $warnings = array($this->warning('W01', 7, 7), $this->warning('W02', 20, 7), $this->warning('W03', 21));
        $relationship = array_replace($this->warning('W07', 30), array('entityType' => DATA_ITEM_COMPANY,
            'relationshipAttention' => array('reasons' => array('overdue_task'), 'tasks' => array(array('taskID' => 7, 'dueDate' => '2026-10-01')))));
        $warnings[] = $relationship;
        $copy = $warnings;
        $rows = HomeUI::priorityRows($tasks, $warnings, '2026-10-15');
        self::assertCount(3, $rows);
        self::assertSame('priority-task-7', $rows[0]['id']);
        self::assertSame(array('W01', 'W02', 'W07'), array_column($rows[0]['warnings'], 'code'));
        self::assertSame('priority-warning-W03-21', $rows[1]['id']);
        self::assertSame('priority-task-8', $rows[2]['id']);
        self::assertSame($copy, $warnings);
        $afterCompletion = HomeUI::priorityRows(array(), array($warnings[1]), '2026-10-15');
        self::assertSame('priority-warning-W02-20', $afterCompletion[0]['id']);
        self::assertCount(4, HomeUI::priorityRows($tasks, $warnings, '2026-10-15', 'warnings'));
        self::assertCount(2, HomeUI::priorityRows($tasks, $warnings, '2026-10-15', 'tasks'));
        // An independent ordinary reason remains a warning, even beside an existing Task.
        $relationship['relationshipAttention']['reasons'][] = 'stale_relationship';
        $mixed = HomeUI::priorityRows($tasks, array($relationship), '2026-10-15');
        self::assertCount(3, $mixed);
        self::assertSame('stale_relationship', $mixed[1]['warnings'][0]['reason']);
        self::assertSame(array('overdue_task'), $mixed[0]['warnings'][0]['relationshipAttention']['reasons']);
        self::assertCount(0, HomeUI::priorityRows(array(), array(), '2026-10-15'));
        $responsibilities = array(array_replace($this->warning('W08', 20), array('reason' => 'invalid_owner')),
            array_replace($this->warning('W08', 20), array('reason' => 'invalid_recruiter')));
        $ids = array_column(HomeUI::priorityRows(array(), $responsibilities, '2026-10-15'), 'id');
        self::assertCount(2, array_unique($ids));
        foreach ($ids as $id) self::assertSame($id, TaskPresentation::homeReturnFields(array('homeContext' => $id))['homeContext']);
        self::assertCount(40, HomeUI::priorityRows(array_map(fn($id) => $this->task($id), range(1, 40)), array(), '2026-10-15'));
    }

    public function testOverviewUsesOnlySuppliedAuthorisedCollectionsWithoutChangingThem(): void
    {
        $tasks = array($this->task(), array_replace($this->task(8), array('dueDate' => '2026-10-15')),
            array_replace($this->task(9), array('dueDate' => null)));
        $warnings = array($this->warning('W02', 20), $this->warning('W03', 20),
            array_replace($this->warning('W07', 30), array('entityType' => DATA_ITEM_COMPANY)));
        $copy = $warnings;
        $overview = HomeUI::priorityOverview($tasks, $warnings, '2026-10-15');
        self::assertSame(1, $overview['overdueTaskCount']);
        self::assertSame(1, $overview['dueTodayTaskCount']);
        self::assertSame(array(20), array_keys($overview['jobs']));
        self::assertSame(array(30), array_keys($overview['companies']));
        self::assertSame($warnings[0], $overview['jobs'][20]);
        self::assertSame($copy, $warnings);
        self::assertSame(array('overdueTaskCount' => 0, 'dueTodayTaskCount' => 0, 'companies' => array(), 'jobs' => array()),
            HomeUI::priorityOverview(array(), array(), '2026-10-15'));
        self::assertSame(array(), $this->queries);
    }

    public function testWarningPresentationKeepsEvidenceDatesUnitsAndAbsenceDistinct(): void
    {
        $job = array_replace($this->warning('W02', 20), array('sourceDate' => '2026-10-06 09:00:00', 'ageDays' => 7));
        self::assertSame(array('label' => 'Actionable since', 'date' => TaskPresentation::date('2026-10-06'), 'age' => '7 working days'), HomeUI::warningEvidence($job));
        self::assertSame(array('label' => 'No qualifying contact recorded', 'date' => '', 'age' => ''),
            HomeUI::warningEvidence(array('code' => 'W07', 'sourceDate' => null, 'ageDays' => null)));
        self::assertSame(array('label' => 'Owner inactive or unassigned', 'date' => '', 'age' => ''),
            HomeUI::warningEvidence(array('code' => 'W08', 'reason' => 'invalid_owner')));
        self::assertSame(array(), $this->queries);
    }

    public function testFixedReturnContextRejectsRedirectsArraysAndMalformedPaging(): void
    {
        $fields = array('homeView' => 'all', 'homeFilter' => 'warnings', 'homePage' => '3', 'homeContext' => 'priority-warning-W07-7');
        self::assertSame('m=home&a=priorities&filter=warnings&page=3#priority-warning-W07-7', TaskPresentation::homeReturnURI($fields));
        foreach (array('homeView' => 'https://example.com', 'homeFilter' => 'all&x=1', 'homePage' => '0', 'homeContext' => 'https://example.com') as $field => $bad)
        {
            foreach (array($bad, array('x')) as $value)
            {
                try { TaskPresentation::homeReturnFields(array_replace($fields, array($field => $value))); self::fail('Invalid context accepted.'); }
                catch (InvalidArgumentException $e) { self::assertSame('Invalid Home return context.', $e->getMessage()); }
            }
        }
        self::assertSame(array(), $this->queries);
    }

    public function testHomeCountsAndPaginatesOnlyAuthorisedTasks(): void
    {
        $this->loggedIn = true;
        $this->accessOverrides = array('companies.show' => 0, 'joborders.list' => 0, 'candidates.show' => 0);
        $rows = array_map(fn($id) => $this->task($id), range(1, 32));
        $rows[] = array_replace($this->task(99), array('dataItemType' => DATA_ITEM_CANDIDATE, 'dataItemID' => 999, 'title' => 'Hidden parent'));
        $rows[] = array_replace($this->task(100), array('createdBy' => 999, 'assignedTo' => 999, 'title' => 'Private stranger'));
        $this->db->method('getAssoc')->willReturnCallback(function ($sql) use ($rows) {
            if (str_contains($sql, 'FROM user')) return array('access_level' => ACCESS_LEVEL_EDIT);
            if (preg_match('/WHERE task_id = ([0-9]+)/', $sql, $match)) return $rows[(int) $match[1] - 1] ?? array();
            return array();
        });
        $reads = array();
        $this->db->method('getAllAssoc')->willReturnCallback(function ($sql) use (&$rows, &$reads) {
            $reads[] = $sql;
            return str_contains($sql, 'FROM task WHERE 1 = 1') ? $rows : array();
        });
        foreach (array(array('home', '1', 8, 1), array('priorities', '2', 7, 2), array('priorities', '99', 7, 2)) as [$action, $page, $size, $expectedPage])
        {
            $_GET = array('a' => $action, 'filter' => 'all', 'page' => $page);
            $assigned = array();
            $template = $this->createStub(Template::class);
            $template->method('assign')->willReturnCallback(function ($key, $value) use (&$assigned) { $assigned[$key] = $value; });
            $ui = $this->ui(HomeUI::class);
            (new ReflectionProperty(UserInterface::class, '_template'))->setValue($ui, $template);
            $ui->handleRequest();
            self::assertSame(32, $assigned['myTaskCount']);
            self::assertSame(32, $assigned['priorityPager']->getTotalRows());
            self::assertSame($expectedPage, (int) $assigned['priorityPager']->getCurrentPage());
            self::assertCount($size, $assigned['priorityRows']);
            self::assertNotContains('Hidden parent', array_column(array_column($assigned['priorityRows'], 'task'), 'title'));
            self::assertNotContains('Private stranger', array_column(array_column($assigned['priorityRows'], 'task'), 'title'));
        }
        self::assertSame(array(), $this->queries);
        self::assertStringContainsString('assigned_to = 10', implode("\n", $reads));
        $seen = array();
        foreach (range(1, 4) as $page)
        {
            $_GET = array('a' => 'home', 'filter' => 'tasks', 'page' => (string) $page);
            $ui->handleRequest();
            self::assertCount(8, $assigned['priorityRows']);
            $seen = array_merge($seen, array_column(array_column($assigned['priorityRows'], 'task'), 'taskID'));
        }
        self::assertSame(range(1, 32), $seen);
        // A visible row removed by the native writer must not leave a gap/duplicate on return.
        // The writer itself is covered by testNativeTaskActionsRetainHomeContextAndRejectInvalidRequestsBeforeWrites.
        unset($rows[31]);
        $_GET['page'] = '99';
        $ui->handleRequest();
        self::assertSame(31, $assigned['priorityPager']->getTotalRows());
        self::assertSame(4, (int) $assigned['homeState']['homePage']);
        self::assertSame(range(25, 31), array_column(array_column($assigned['priorityRows'], 'task'), 'taskID'));
    }

    public function testReviewUsesNativeWriterAndReturnsToFilteredHomePage(): void
    {
        $this->loggedIn = true;
        $this->session->method('isCSRFTokenValid')->willReturn(true);
        $this->db->method('beginTransaction')->willReturn(true);
        $this->db->method('commitTransaction')->willReturn(true);
        $clock = DateUtility::getAdjustedDate('Y-m-d') . ' 12:00:00';
        $this->db->method('getAssoc')->willReturnCallback(function ($sql) use ($clock) {
            if (str_contains($sql, 'FROM warning_review')) return array();
            if (str_contains($sql, 'SELECT NOW()')) return array('clock' => $clock);
            return array('access_level' => ACCESS_LEVEL_EDIT, 'joborder_id' => 7);
        });
        $this->db->method('getAllAssoc')->willReturnCallback(function ($sql) use ($clock) {
            if (str_contains($sql, 'FROM joborder j'))
                return array(array('jobOrderID' => 7, 'title' => 'Visible job', 'owner' => 10, 'recruiter' => 10,
                    'isHot' => 0, 'isAdminHidden' => 0, 'actionable' => date('Y-m-d', strtotime($clock . ' -20 days')) . ' 09:00:00',
                    'submitted' => 0, 'progress' => null));
            return array();
        });
        $review = (new WarningReviews())->get('W02', DATA_ITEM_JOBORDER, 7, 'no_recorded_submission');
        $ui = new class extends HomeUI {
            public $destination;
            public function __construct() {}
            protected function redirectWarningReview($uri) { $this->destination = $uri; }
        };
        $_GET = array('a' => 'reviewWarning');
        $_POST = array_map('strval', array_merge($review['identity'], array('csrfToken' => 'valid',
            'expectedVersion' => 0, 'expectedEvidence' => $review['evidence'], 'requestID' => 'home-review-fixture-01',
            'returnTo' => 'home', 'reviewAction' => 'remind_later', 'reviewUntil' => WarningReviews::reminderDates()[5],
            'homeView' => 'all', 'homeFilter' => 'warnings', 'homePage' => '3', 'homeContext' => 'priority-warning-W02-7')));
        $valid = $_POST;
        foreach (array(array('homePage' => array('3')), array('homeContext' => '//example.com')) as $change)
        {
            $_POST = array_replace($valid, $change);
            $this->assertCommonError(COMMONERROR_BADFIELDS, fn() => $ui->handleRequest());
            self::assertSame(array(), $this->queries);
        }
        $_POST = $valid;
        $ui->handleRequest();
        self::assertSame('m=home&a=priorities&filter=warnings&page=3&warningReview=saved#priority-warning-W02-7', $ui->destination);
        self::assertCount(2, $this->queries);
        self::assertStringContainsString('INSERT INTO warning_review', $this->queries[0]);
        self::assertStringContainsString('INSERT INTO history', $this->queries[1]);
        self::assertStringNotContainsString('UPDATE task', implode("\n", $this->queries));
        self::assertStringNotContainsString('INSERT INTO activity', implode("\n", $this->queries));
    }

    public function testNativeTaskActionsRetainHomeContextAndRejectInvalidRequestsBeforeWrites(): void
    {
        $this->loggedIn = true;
        $this->session->method('isCSRFTokenValid')->willReturnCallback(fn($token) => $token === 'valid');
        $this->db->method('beginTransaction')->willReturn(true);
        $this->db->method('commitTransaction')->willReturn(true);
        $status = 'open';
        $this->db->method('getAssoc')->willReturnCallback(function ($sql) use (&$status) { return array_replace($this->task(), array('status' => $status)); });
        $ui = new class extends TasksUI {
            public $destinations = array();
            public function __construct() {}
            protected function redirectToTask($id, $notice)
            {
                $state = (new ReflectionProperty(TasksUI::class, '_homeReturn'))->getValue($this);
                $this->destinations[] = array($id, $notice, TaskPresentation::homeReturnURI($state));
            }
        };
        $redirects =& $ui->destinations;
        $template = $this->createStub(Template::class);
        (new ReflectionProperty(UserInterface::class, '_template'))->setValue($ui, $template);
        $base = array('taskID' => '7', 'csrfToken' => 'valid', 'returnTo' => 'home', 'homeView' => 'all',
            'homeFilter' => 'tasks', 'homePage' => '2', 'homeContext' => 'priority-task-7');
        $_GET = array('a' => 'complete');
        foreach (array(array('csrfToken' => array('valid')), array('returnTo' => '//example.com'), array('homePage' => array('2')), array('homeContext' => 'evil')) as $change)
        {
            $_POST = array_replace($base, $change);
            $ui->handleRequest();
            self::assertSame(array(), $this->queries);
            self::assertSame(array(), $redirects);
        }
        $_POST = $base;
        unset($_POST['returnTo']);
        $ui->handleRequest();
        self::assertSame(array(), $this->queries);
        self::assertSame(array(), $redirects);
        $_POST = $base;
        $ui->handleRequest();
        self::assertStringContainsString("status = 'completed'", implode("\n", $this->queries));
        self::assertSame(array('7', 'saved', 'm=home&a=priorities&filter=tasks&page=2#priority-task-7'), $redirects[0]);
        // The native edit form remains the rescheduling writer, including local date conversion.
        $this->queries = array();
        $_GET['a'] = 'edit';
        $_POST = array_merge($base, array('dueDate' => '10-20-26'));
        $ui->handleRequest();
        self::assertStringContainsString("due_date = '2026-10-20'", implode("\n", $this->queries));
        self::assertCount(2, $redirects);
        $this->queries = array();
        $status = 'completed';
        $_GET['a'] = 'reopen';
        $_POST = $base;
        $ui->handleRequest();
        self::assertStringContainsString("status = 'open'", implode("\n", $this->queries));
        self::assertCount(3, $redirects);
        $this->queries = array();
        $this->accessOverrides['tasks.reopen'] = ACCESS_LEVEL_READ;
        $ui->handleRequest();
        self::assertSame(array(), $this->queries);
        self::assertCount(3, $redirects);
    }
}
