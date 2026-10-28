<?php
namespace OpenCATS\Tests\IntegrationTests;

/** Real bounded Home projections. Run only against the disposable integration database. */
class HomeBindingsTest extends DatabaseTestCase
{
    private $db;
    private $access = 200;
    private $overrides = array();
    private $loggedIn = true;
    private $company;
    private $contact;
    private $candidate;
    private $job;

    protected function setUp(): void
    {
        parent::setUp();
        require_once LEGACY_ROOT . '/lib/CATSUtility.php';
        require_once LEGACY_ROOT . '/lib/Session.php';
        require_once LEGACY_ROOT . '/lib/Tasks.php';
        require_once LEGACY_ROOT . '/lib/Calendar.php';
        require_once LEGACY_ROOT . '/lib/ActivityEntries.php';
        $session = $this->createStub(\CATSSession::class);
        $session->method('getUserID')->willReturn(1);
        $session->method('getAccessLevel')->willReturnCallback(fn($key) => $this->overrides[$key] ?? $this->access);
        $session->method('getRealAccessLevel')->willReturnCallback(fn() => $this->access);
        $session->method('isLoggedIn')->willReturnCallback(fn() => $this->loggedIn);
        $session->method('isTimeFormat24')->willReturn(true);
        $_SESSION['CATS'] = $session;
        $this->db = \DatabaseConnection::getInstance();
        $this->db->query("INSERT INTO company (name,owner,commercial_tier,relationship_status) VALUES ('Classified account',1,'B','Prospect')");
        $this->company = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO contact (company_id,company_department_id,first_name,last_name) VALUES ($this->company,-1,'Helen','Ward')");
        $this->contact = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO candidate (first_name,last_name) VALUES ('Alex','Morgan')");
        $this->candidate = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO joborder (company_id,title,owner,recruiter,status,date_created) VALUES ($this->company,'Active owned job',1,2,'Active',CURDATE()-INTERVAL 5 DAY)");
        $this->job = (int) $this->db->getLastInsertID();
    }

    protected function tearDown(): void
    {
        unset($_SESSION['CATS']);
        (new \ReflectionProperty(\DatabaseConnection::class, '_instance'))->setValue(null, null);
        parent::tearDown();
    }

    public function testAccountsUseNativeTierOwnershipAndDenyWithoutFalseZero(): void
    {
        $this->db->query("INSERT INTO company (name,owner,commercial_tier) VALUES ('A owned',1,'A'),('Other owner',2,'A'),('Unclassified',1,NULL),('C owned',1,'C')");
        $model = new \Companies();
        $rows = $model->getHomeAccounts(2);
        self::assertSame(array('A owned','Classified account'), array_column($rows,'name'));
        self::assertSame('Prospect', $rows[1]['relationshipStatus']);
        self::assertArrayHasKey('lastContact', $rows[0]);
        $this->overrides['contacts.show'] = 0;
        self::assertArrayNotHasKey('lastContact', $model->getHomeAccounts()[0]);
        $this->overrides['companies.show'] = 0;
        self::assertNull($model->getHomeAccounts());
        $this->overrides = array();
        $this->loggedIn = false;
        self::assertNull($model->getHomeAccounts());
    }

    public function testCurrentJobsMatchActiveOwnerOrRecruiterAndHiddenACL(): void
    {
        $this->db->query("INSERT INTO joborder (company_id,title,owner,recruiter,status,is_admin_hidden,date_created) VALUES
            ($this->company,'Recruiter job',2,1,'On Hold',0,NOW()),
            ($this->company,'Full owned',1,2,'Full',0,NOW()),
            ($this->company,'Closed owned',1,1,'Closed',0,NOW()),
            ($this->company,'Other owner',2,2,'Active',0,NOW()),
            ($this->company,'Hidden owned',1,1,'Active',1,NOW())");
        $model = new \JobOrders();
        $result = $model->getHomeCurrent(2);
        self::assertSame(1, $result['total']);
        self::assertCount(1, $result['rows']);
        self::assertSame(array('Active owned job'), array_column($result['rows'],'title'));
        self::assertSame('5', $model->getHomeCurrent(10)['rows'][0]['daysOld']);
        $this->overrides['companies.show'] = 0;
        self::assertSame(array(null), array_column($model->getHomeCurrent(10)['rows'],'companyName'));
        $this->overrides['joborders.hidden'] = ACCESS_LEVEL_SA;
        self::assertSame(2, $model->getHomeCurrent()['total']);
        $this->overrides['joborders.list'] = 0;
        self::assertNull($model->getHomeCurrent());
    }

    public function testCalendarIsOwnUpcomingAndChecksBothNativeAssociations(): void
    {
        $this->db->query("INSERT INTO calendar_event (title,date,entered_by,data_item_type,data_item_id,joborder_id,public) VALUES
            ('Linked interview',CURDATE()+INTERVAL 9 HOUR,1,100,$this->candidate,$this->job,0),
            ('Standalone',CURDATE()+INTERVAL 1 DAY,1,-1,-1,NULL,0),
            ('Public other owner',CURDATE()+INTERVAL 2 DAY,2,-1,-1,NULL,1),
            ('Past',CURDATE()-INTERVAL 1 DAY,1,-1,-1,NULL,0),
            ('Later own',CURDATE()+INTERVAL 3 DAY,1,-1,-1,NULL,0)");
        $model = new \Calendar();
        $events = $model->getHomeUpcoming(2);
        self::assertSame(array('Linked interview','Standalone'), array_column($events,'title'));
        self::assertSame('09:00', $events[0]['time']);
        self::assertSame((string) $this->candidate, $events[0]['dataItemID']);
        self::assertSame((string) $this->job, $events[0]['jobOrderID']);
        $this->overrides['candidates.show'] = 0;
        self::assertSame(array('Standalone','Later own'), array_column($model->getHomeUpcoming(),'title'));
        $this->overrides = array('joborders.show'=>0);
        self::assertSame(array('Standalone','Later own'), array_column($model->getHomeUpcoming(),'title'));
        $this->overrides = array();
        $this->db->query("UPDATE joborder SET is_admin_hidden=1 WHERE joborder_id=$this->job");
        self::assertSame(array('Standalone','Later own'), array_column($model->getHomeUpcoming(),'title'));
        $this->overrides['calendar.show'] = 0;
        self::assertNull($model->getHomeUpcoming());
    }

    public function testRecentActivityIsBoundedAssociatedAndCannotRevealDeniedRecords(): void
    {
        for ($i=0; $i<5; ++$i)
            $this->db->query("INSERT INTO activity (data_item_id,data_item_type,joborder_id,type,notes,date_occurred) VALUES ($this->contact,300,$this->job,500,'Contact note $i',CURDATE()+INTERVAL $i HOUR)");
        $this->db->query("INSERT INTO activity (data_item_id,data_item_type,type,notes,date_occurred) VALUES ($this->candidate,100,300,'Candidate note',NOW())");
        $this->db->query("INSERT INTO activity (data_item_id,data_item_type,type,notes,date_occurred) VALUES ($this->company,200,300,'Direct Company meeting',CURDATE()-INTERVAL 1 DAY)");
        $model = new \ActivityEntries();
        $notes = array('Contact note 4','Contact note 3','Contact note 2');
        self::assertSame($notes, array_column($model->getRecentForSource(DATA_ITEM_CONTACT,$this->contact),'notes'));
        self::assertSame($notes, array_column($model->getRecentForSource(DATA_ITEM_COMPANY,$this->company),'notes'));
        self::assertSame($notes, array_column($model->getRecentForSource(DATA_ITEM_JOBORDER,$this->job),'notes'));
        self::assertSame(array('Candidate note'), array_column($model->getRecentForSource(DATA_ITEM_CANDIDATE,$this->candidate),'notes'));
        self::assertStringNotContainsString('%', $model->getRecentForSource(DATA_ITEM_CONTACT,$this->contact)[0]['dateCreated']);
        self::assertContains('Direct Company meeting', array_column($model->getRecentForSource(DATA_ITEM_COMPANY,$this->company,10),'notes'));
        $this->overrides['joborders.show'] = 0;
        self::assertSame(array(), $model->getRecentForSource(DATA_ITEM_CONTACT,$this->contact));
        $this->overrides = array('contacts.show'=>0);
        self::assertNull($model->getRecentForSource(DATA_ITEM_CONTACT,$this->contact));
        self::assertSame(array('Direct Company meeting'), array_column($model->getRecentForSource(DATA_ITEM_COMPANY,$this->company),'notes'));
        $this->overrides = array();
        $this->db->query("UPDATE candidate SET is_admin_hidden=1 WHERE candidate_id=$this->candidate");
        self::assertNull($model->getRecentForSource(DATA_ITEM_CANDIDATE,$this->candidate));
        $this->overrides['activity.listByViewDataGrid'] = 0;
        self::assertNull($model->getRecentForSource(DATA_ITEM_COMPANY,$this->company));
    }
    public function testRecentActivityIdentifiesStoredSubjectsWithoutEntryLookups(): void
    {
        $this->db->query("INSERT INTO candidate (first_name,last_name) VALUES ('Priya <img src=x onerror=alert(1)>','Shah')");
        $second = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO candidate (first_name,last_name,is_admin_hidden) VALUES ('Restricted','Person',1)");
        $hidden = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO user (user_name,first_name,last_name,access_level) VALUES ('home-recorder','Maya','Patel',200)");
        $author = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO joborder (company_id,title,owner,recruiter,status,is_admin_hidden) VALUES ($this->company,'Hidden job',1,1,'Active',1)");
        $hiddenJob = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO activity (data_item_id,data_item_type,joborder_id,entered_by,type,notes,date_occurred) VALUES
            ($this->candidate,100,$this->job,$author,500,'Called; awaiting feedback.','2026-10-15 15:00:00'),
            ($second,100,$this->job,$author,500,'Called; awaiting feedback.','2026-10-15 15:30:00'),
            ($this->contact,300,$this->job,$author,300,'Confirmed availability.','2026-10-15 16:00:00'),
            ($hidden,100,$this->job,$author,500,'Restricted note.','2026-10-15 17:00:00'),
            (999999,300,$this->job,$author,500,'Missing subject.','2026-10-15 18:00:00'),
            ($this->contact,300,$hiddenJob,$author,500,'Restricted secondary job.','2026-10-15 19:00:00'),
            ($this->company,200,NULL,$author,300,'Updated the account briefing.','2026-10-15 14:30:00')");
        $model = new \ActivityEntries();
        $reads = (int) $this->db->getAssoc("SHOW SESSION STATUS LIKE 'Com_select'")['Value'];
        $rows = $model->getRecentForSource(DATA_ITEM_JOBORDER,$this->job);
        $cost = (int) $this->db->getAssoc("SHOW SESSION STATUS LIKE 'Com_select'")['Value'] - $reads;
        self::assertSame(array(
            array((string) DATA_ITEM_CONTACT,(string) $this->contact,'Helen Ward'),
            array((string) DATA_ITEM_CANDIDATE,(string) $second,'Priya <img src=x onerror=alert(1)> Shah'),
            array((string) DATA_ITEM_CANDIDATE,(string) $this->candidate,'Alex Morgan')
        ), array_map(fn($row) => array($row['dataItemType'],$row['dataItemID'],$row['subjectName']), $rows));
        self::assertSame(array('Confirmed availability.','Called; awaiting feedback.','Called; awaiting feedback.'),array_column($rows,'notes'));
        $reads = (int) $this->db->getAssoc("SHOW SESSION STATUS LIKE 'Com_select'")['Value'];
        self::assertCount(1, $model->getRecentForSource(DATA_ITEM_JOBORDER,$this->job,1));
        self::assertSame($cost, (int) $this->db->getAssoc("SHOW SESSION STATUS LIKE 'Com_select'")['Value'] - $reads);

        require_once LEGACY_ROOT . '/lib/Template.php';
        require_once LEGACY_ROOT . '/modules/tasks/TaskPresentation.php';
        $template = new \Template();
        $template->assign('homeActivity',$rows);
        $template->assign('homeActivityURL',\TaskPresentation::parentURL(DATA_ITEM_JOBORDER,$this->job));
        $renderReads = (int) $this->db->getAssoc("SHOW SESSION STATUS LIKE 'Com_select'")['Value'];
        ob_start();
        try { $template->display('./modules/home/RecentActivity.tpl'); $html = ob_get_contents(); }
        finally { ob_end_clean(); }
        self::assertSame($renderReads,(int) $this->db->getAssoc("SHOW SESSION STATUS LIKE 'Com_select'")['Value']);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $links = $xpath->query('//ol/li//a');
        self::assertCount(3,$links);
        foreach ($rows as $index => $row)
        {
            self::assertSame($row['subjectName'],$links[$index]->textContent);
            self::assertSame(\TaskPresentation::parentURL($row['dataItemType'],$row['dataItemID']),$links[$index]->getAttribute('href'));
        }
        self::assertSame(0,$xpath->query('//img')->length);
        self::assertSame(3,substr_count($html,'Recorded by Maya Patel'));
        self::assertStringContainsString('View record &amp; full Activity',$html);
        $companyRows = $model->getRecentForSource(DATA_ITEM_COMPANY,$this->company);
        self::assertSame(array('Helen Ward','Classified account'),array_column($companyRows,'subjectName'));
        self::assertSame((string) DATA_ITEM_COMPANY,$companyRows[1]['dataItemType']);
        self::assertSame((string) $this->company,$companyRows[1]['dataItemID']);
        $this->overrides['candidates.show'] = 0;
        self::assertSame(array('Helen Ward'),array_column($model->getRecentForSource(DATA_ITEM_JOBORDER,$this->job),'subjectName'));
        $this->overrides = array('contacts.show'=>0);
        self::assertSame(array('Priya <img src=x onerror=alert(1)> Shah','Alex Morgan'),array_column($model->getRecentForSource(DATA_ITEM_JOBORDER,$this->job),'subjectName'));
        self::assertSame(array('Classified account'),array_column($model->getRecentForSource(DATA_ITEM_COMPANY,$this->company),'subjectName'));
        $this->overrides = array('joborders.show'=>0);
        self::assertSame(array('Classified account'),array_column($model->getRecentForSource(DATA_ITEM_COMPANY,$this->company),'subjectName'));
        $this->overrides = array('companies.show'=>0);
        self::assertNull($model->getRecentForSource(DATA_ITEM_COMPANY,$this->company));
        $this->overrides = array();
        $this->db->query("UPDATE joborder SET is_admin_hidden=1 WHERE joborder_id=$this->job");
        self::assertNull($model->getRecentForSource(DATA_ITEM_JOBORDER,$this->job));
    }

    public function testPreviewReadCostsDoNotGrowWithRenderedRowLimit(): void
    {
        $companies = new \Companies();
        $jobs = new \JobOrders();
        $calendar = new \Calendar();
        $activity = new \ActivityEntries();
        $reads = function ($callback) {
            $before = (int) $this->db->getAssoc("SHOW SESSION STATUS LIKE 'Com_select'")['Value'];
            $callback();
            return (int) $this->db->getAssoc("SHOW SESSION STATUS LIKE 'Com_select'")['Value'] - $before;
        };
        foreach (array(
            array(fn() => $companies->getHomeAccounts(1), fn() => $companies->getHomeAccounts(3), 3),
            array(fn() => $jobs->getHomeCurrent(1), fn() => $jobs->getHomeCurrent(3), 2),
            array(fn() => $calendar->getHomeUpcoming(1), fn() => $calendar->getHomeUpcoming(3), 1),
            array(fn() => $activity->getRecentForSource(DATA_ITEM_CONTACT,$this->contact,1),
                fn() => $activity->getRecentForSource(DATA_ITEM_CONTACT,$this->contact,3), null)
        ) as [$one,$three,$expected])
        {
            $small = $reads($one);
            self::assertSame($small, $reads($three));
            if ($expected !== null) self::assertSame($expected, $small);
            else self::assertGreaterThan(0, $small);
        }
    }

}
