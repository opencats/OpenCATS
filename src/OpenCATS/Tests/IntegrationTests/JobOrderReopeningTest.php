<?php
namespace OpenCATS\Tests\IntegrationTests;

/** Native reopening behaviour on the existing disposable integration database. */
class JobOrderReopeningTest extends DatabaseTestCase
{
    private $db;
    private $access = array();
    private $job;
    private $candidate;

    protected function setUp(): void
    {
        parent::setUp();
        require_once LEGACY_ROOT . '/lib/CATSUtility.php';
        require_once LEGACY_ROOT . '/lib/Session.php';
        require_once LEGACY_ROOT . '/lib/JobOrders.php';
        require_once LEGACY_ROOT . '/lib/Tasks.php';
        require_once LEGACY_ROOT . '/lib/ActivityEntries.php';
        $session = $this->createStub(\CATSSession::class);
        $session->method('getUserID')->willReturn(1);
        $session->method('getAccessLevel')->willReturnCallback(fn($key) => $this->access[$key] ?? ACCESS_LEVEL_SA);
        $session->method('getRealAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        $session->method('isLoggedIn')->willReturn(true);
        $session->method('isTimeFormat24')->willReturn(true);
        $session->method('getTimeZoneOffset')->willReturn(0);
        $session->method('isCSRFTokenValid')->willReturnCallback(fn($token) => $token === 'fixture-token');
        $_SESSION['CATS'] = $session;
        $this->db = \DatabaseConnection::getInstance();
        $this->db->query("INSERT INTO company (name,owner) VALUES ('Reopening account',1)");
        $company = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO joborder (company_id,title,status,openings,openings_available,owner,recruiter,first_actionable_at,date_created,date_modified)
            VALUES ($company,'Reopening fixture','Full',10,0,1,1,'2025-01-01 09:00:00','2025-01-01 09:00:00','2025-02-01 09:00:00')");
        $this->job = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO candidate (first_name,last_name) VALUES ('Fixture','Candidate')");
        $this->candidate = (int) $this->db->getLastInsertID();
    }

    protected function tearDown(): void
    {
        unset($_SESSION['CATS']);
        $_GET = $_POST = array();
        unset($_SERVER['REQUEST_METHOD']);
        (new \ReflectionProperty(\DatabaseConnection::class, '_instance'))->setValue(null,null);
        parent::tearDown();
    }

    private function countRows($sql)
    {
        return array_values($this->db->getAssoc($sql))[0];
    }

    private function reopen($vacancies = '1')
    {
        $jobs = new \JobOrders();
        return $jobs->reopen($this->job,$vacancies,$jobs->getForPipeline($this->job)['reopenVersion']);
    }

    public function testFullAndClosedCapacityAndAuditPreserveFilledPositions(): void
    {
        foreach (array(array('Full',10,0,1,11),array('Full',10,3,2,9),array('Closed',10,3,4,11)) as [$status,$total,$available,$new,$expected])
        {
            $this->db->query("UPDATE joborder SET status='$status',openings=$total,openings_available=$available WHERE joborder_id=".$this->job);
            $before = (new \JobOrders())->get($this->job);
            self::assertTrue($this->reopen((string) $new));
            $after = (new \JobOrders())->get($this->job);
            self::assertSame('Active',$after['status']);
            self::assertSame($expected,(int) $after['openings']);
            self::assertSame($new,(int) $after['openingsAvailable']);
            self::assertSame($before['firstActionableAt'],$after['firstActionableAt']);
            foreach (array('title','owner','recruiter','companyID','deskID','sectorID','isSpeculative','public') as $key)
                self::assertSame($before[$key],$after[$key]);
        }
        self::assertSame(3,(int) $this->countRows("SELECT COUNT(*) FROM activity WHERE data_item_type=400 AND data_item_id=".$this->job));
        $a=$this->db->getAssoc('SELECT * FROM activity ORDER BY activity_id DESC LIMIT 1');
        self::assertSame(800,(int) $a['type']);
        self::assertSame(1,(int) $a['entered_by']);
        self::assertSame($this->job,(int) $a['joborder_id']);
        self::assertSame('Moved Job to Active. New vacancies: 4.',$a['notes']);
        self::assertSame(3,(int) $this->countRows("SELECT COUNT(*) FROM history WHERE data_item_type=400 AND data_item_id=".$this->job." AND the_field='status' AND new_value='Active'"));
        self::assertGreaterThan(0,(int) $this->countRows("SELECT COUNT(*) FROM history WHERE data_item_id=".$this->job." AND the_field='openingsAvailable'"));
    }

    public function testInvalidVacanciesAndContradictoryCapacityCannotWrite(): void
    {
        foreach (array('0','-1','1.5','10000','9999999999999999','1e2',' 1','01',array('1'),null) as $value)
        {
            try { $this->reopen($value); self::fail('Invalid vacancies accepted'); }
            catch (\InvalidArgumentException $e) { self::assertSame('Full',(new \JobOrders())->get($this->job)['status']); }
        }
        $this->db->query('UPDATE joborder SET openings_available=11 WHERE joborder_id='.$this->job);
        try { $this->reopen(); self::fail('Contradictory capacity accepted'); }
        catch (\RuntimeException $e) { self::assertStringContainsString('capacity',$e->getMessage()); }
        self::assertSame(0,(int) $this->countRows('SELECT COUNT(*) FROM activity'));
    }

    public function testStaleAndDuplicateSubmissionCannotCreateAnotherEvent(): void
    {
        $jobs=new \JobOrders();$version=$jobs->getForPipeline($this->job)['reopenVersion'];
        self::assertTrue($jobs->reopen($this->job,'2',$version));
        try { $jobs->reopen($this->job,'2',$version); self::fail('Duplicate reopening accepted'); }
        catch (\RuntimeException $e) { self::assertStringContainsString('already been reopened',$e->getMessage()); }
        self::assertSame(1,(int) $this->countRows('SELECT COUNT(*) FROM activity'));
        $this->db->query("UPDATE joborder SET status='Closed' WHERE joborder_id=".$this->job);
        try { $jobs->reopen($this->job,'1',$version); self::fail('Stale preview accepted'); }
        catch (\RuntimeException $e) { self::assertSame('Closed',$jobs->get($this->job)['status']); }
    }

    public function testPermissionsHiddenRecordsAndDirectPipelineBypass(): void
    {
        foreach (array('joborders.edit','joborders.show','pipelines.addToPipeline','pipelines.addActivity') as $permission)
        {
            $this->access[$permission]=0;
            try { $this->reopen(); self::fail('Permission denial bypassed'); }
            catch (\RuntimeException $e) { self::assertSame('Full',(new \JobOrders())->get($this->job)['status']); }
            unset($this->access[$permission]);
        }
        $this->db->query('UPDATE joborder SET is_admin_hidden=1 WHERE joborder_id='.$this->job);
        $this->access['joborders.hidden']=0;
        self::assertFalse((new \Pipelines())->add($this->candidate,$this->job,1));
        $this->db->query('UPDATE joborder SET is_admin_hidden=0 WHERE joborder_id='.$this->job);
        foreach (array('Full','Closed') as $status)
        {
            $this->db->query("UPDATE joborder SET status='$status' WHERE joborder_id=".$this->job);
            self::assertFalse((new \Pipelines())->add($this->candidate,$this->job,1));
        }
        self::assertTrue($this->reopen());
        self::assertTrue((new \Pipelines())->add($this->candidate,$this->job,1));
        self::assertFalse((new \Pipelines())->add($this->candidate,$this->job,1));
    }

    public function testPublicApplicationsPreserveReopeningAndVisibilityGuards(): void
    {
        unset($_SESSION['CATS']);
        $pipelines = new \Pipelines();
        foreach (array(array('Active', 0, 0), array('Active', 1, 1), array('Full', 1, 0), array('Closed', 1, 0)) as [$status, $public, $hidden])
        {
            $this->db->query("UPDATE joborder SET status='$status',public=$public,is_admin_hidden=$hidden WHERE joborder_id=" . $this->job);
            self::assertFalse($pipelines->add($this->candidate, $this->job));
            self::assertSame(0, (int) $this->countRows('SELECT COUNT(*) FROM candidate_joborder WHERE joborder_id=' . $this->job));
        }
        $this->db->query("UPDATE joborder SET status='Active',public=1,is_admin_hidden=0 WHERE joborder_id=" . $this->job);
        self::assertFalse($pipelines->add($this->candidate, $this->job, 1)); // Staff ACL still required.
        self::assertTrue($pipelines->add($this->candidate, $this->job));
        self::assertSame('0', (string) $this->db->getAssoc('SELECT added_by FROM candidate_joborder WHERE joborder_id=' . $this->job)['added_by']);
        self::assertFalse($pipelines->add($this->candidate, $this->job));
    }

    public function testExistingFeedbackAndMilestonesRemainUsable(): void
    {
        $this->db->query('INSERT INTO candidate_joborder (candidate_id,joborder_id,status,date_created,date_modified) VALUES ('.$this->candidate.','.$this->job.",800,'2025-01-01','2025-02-01')");
        $this->db->query('INSERT INTO candidate_joborder_status_history (candidate_id,joborder_id,status_from,status_to,date) VALUES ('.$this->candidate.','.$this->job.",600,800,'2025-02-01')");
        $history=$this->db->getAllAssoc('SELECT * FROM candidate_joborder_status_history');
        $this->db->query('UPDATE joborder SET openings_available=3 WHERE joborder_id='.$this->job);
        self::assertTrue($this->reopen());
        self::assertSame($history,$this->db->getAllAssoc('SELECT * FROM candidate_joborder_status_history'));
        self::assertSame(8,(int) (new \JobOrders())->get($this->job)['openings']);
        self::assertSame(1,(int) (new \JobOrders())->get($this->job)['openingsAvailable']);
        $capacityAudit=$this->db->getAssoc("SELECT previous_value,new_value FROM history WHERE data_item_id=".$this->job." AND data_item_type=400 AND the_field='openingsAvailable' ORDER BY history_id DESC LIMIT 1");
        self::assertSame('3',$capacityAudit['previous_value']);
        self::assertSame('1',$capacityAudit['new_value']);
        self::assertSame(800,(int) (new \Pipelines())->get($this->candidate,$this->job)['statusID']);
        $this->db->query("UPDATE joborder SET status='Full' WHERE joborder_id=".$this->job);
        self::assertGreaterThan(0,(new \ActivityEntries())->add($this->candidate,100,400,'Existing candidate feedback',1,$this->job));
        self::assertNotEmpty((new \Pipelines())->getJobOrderPipeline($this->job));
    }

    public function testWarningsRestartWithoutChangingAcquisitionOrEngagement(): void
    {
        $this->db->query('INSERT INTO candidate_joborder (candidate_id,joborder_id,status) VALUES ('.$this->candidate.','.$this->job.',400)');
        $this->db->query('INSERT INTO candidate_joborder_status_history (candidate_id,joborder_id,status_from,status_to,date) VALUES ('.$this->candidate.','.$this->job.",100,400,'2025-01-02')");
        require_once LEGACY_ROOT . '/lib/Statistics.php';
        $company=(int) (new \JobOrders())->get($this->job)['companyID'];
        $this->db->query("UPDATE company SET date_created='2024-01-01' WHERE company_id=".$company);
        $this->db->query("INSERT INTO activity (data_item_type,data_item_id,entered_by,type,date_occurred,notes) VALUES (200,$company,1,500,'2025-01-02 12:00:00','Existing client conversation')");
        $engagement=(new \ActivityEntries())->getRelationshipContactDates(array($company));
        self::assertSame('2025-01-02 12:00:00',$engagement[$company]);
        self::assertTrue($this->reopen());
        self::assertSame($engagement,(new \ActivityEntries())->getRelationshipContactDates(array($company)));
        self::assertSame(array(),(new \Tasks())->getJobRecruitmentWarnings($this->job));
        self::assertSame('2025-01-01 09:00:00',(new \JobOrders())->get($this->job)['firstActionableAt']);
        $this->db->query("UPDATE history SET set_date=NOW()-INTERVAL 30 DAY WHERE data_item_id=".$this->job." AND data_item_type=400 AND the_field='status'");
        $warnings=(new \Tasks())->getJobRecruitmentWarnings($this->job);
        self::assertSame(array('W02','W03'),array_column($warnings,'code'));
        self::assertSame(1,(int) $this->countRows('SELECT COUNT(*) FROM activity WHERE data_item_type IN (200,300) OR type IN (100,300,500)'));
        $activities=iterator_to_array((new \ActivityEntries())->getOperationsRows(date('Y-m-01'),date('Y-m-d')));
        self::assertCount(1,$activities);
        self::assertSame(400,(int) $activities[0]['dataItemType']);
        $this->db->query("UPDATE activity_type SET short_description='Call (Sales call)' WHERE activity_type_id=800");
        self::assertSame('Status change',(new \ActivityEntries())->getAllByDataItem($this->job,400)[0]['typeDescription']);
        $recent=(new \ActivityEntries())->getRecentForSource(400,$this->job);
        self::assertSame('Status change',$recent[0]['typeDescription']);
        self::assertSame(400,(int) $recent[0]['dataItemType']);
        self::assertSame($this->job,(int) $recent[0]['dataItemID']);
        self::assertSame('Reopening fixture',$recent[0]['subjectName']);
        $report=(new \Statistics())->getOperations(\Statistics::operationsFilters(array('view'=>'activity')));
        self::assertSame(1,$report['groups']['recruiter'][1]['statusChanges']);
        foreach (array('calls','conversations','emails','meetings') as $metric)
            self::assertSame(0,$report['groups']['recruiter'][1][$metric]);
        self::assertStringContainsString('m=joborders&a=show&jobOrderID='.$this->job,$report['details'][0]['url']);
    }

    public function testAuditAndActivityFailuresRollBackTheEntireReopening(): void
    {
        foreach (array('statusAudit','activity','activityAudit','commit') as $failure)
        {
            $real=$this->db;
            $fake=$this->createStub(\DatabaseConnection::class);
            foreach (array('getAssoc','getAllAssoc','makeQueryInteger','makeQueryIntegerOrNULL','makeQueryString','makeQueryStringOrNULL','beginTransaction','commitTransaction','rollbackTransaction','getLastInsertID') as $method)
                $fake->method($method)->willReturnCallback(fn(...$args)=>$method === 'commitTransaction' && $failure === 'commit' ? false : $real->$method(...$args));
            $fake->method('query')->willReturnCallback(function ($sql,$ignore=false) use ($real,$failure) {
                if (($failure==='statusAudit' && str_contains($sql,'INSERT INTO history')) ||
                    ($failure==='activity' && preg_match('/INSERT INTO activity\\s*\\(/',$sql)) ||
                    ($failure==='activityAudit' && str_contains($sql,"'ACTIVITY'"))) return false;
                return $real->query($sql,$ignore);
            });
            (new \ReflectionProperty(\DatabaseConnection::class,'_instance'))->setValue(null,$fake);
            try { $this->reopen(); self::fail('Failed write was committed'); }
            catch (\RuntimeException $e) { self::assertSame('Full',(new \JobOrders())->get($this->job)['status']); }
            finally { (new \ReflectionProperty(\DatabaseConnection::class,'_instance'))->setValue(null,$real); }
            self::assertSame(0,(int) $real->getAssoc('SELECT COUNT(*) AS n FROM activity')['n']);
            self::assertSame(0,(int) $real->getAssoc('SELECT COUNT(*) AS n FROM history')['n']);
        }
    }
    public function testCandidateSideAndJobSideUseTheNativeAssociationHandlers(): void
    {
        require_once LEGACY_ROOT . '/lib/UserInterface.php';
        require_once LEGACY_ROOT . '/lib/Template.php';
        require_once LEGACY_ROOT . '/modules/joborders/JobOrdersUI.php';
        require_once LEGACY_ROOT . '/modules/candidates/CandidatesUI.php';
        self::assertTrue($this->reopen());
        foreach (array(\JobOrdersUI::class,\CandidatesUI::class) as $class)
        {
            $this->db->query("INSERT INTO candidate (first_name,last_name) VALUES ('Pipeline','Fixture')");
            $candidate=(int) $this->db->getLastInsertID();
            $ui=(new \ReflectionClass($class))->newInstanceWithoutConstructor();
            (new \ReflectionProperty(\UserInterface::class,'_template'))->setValue($ui,$this->createStub(\Template::class));
            (new \ReflectionProperty(\UserInterface::class,'_userID'))->setValue($ui,1);
            $_GET=array('a'=>'addToPipeline');
            $_POST=array('postback'=>'postback','jobOrderID'=>(string) $this->job,'candidateID'=>(string) $candidate);
            $ui->handleRequest();
            self::assertNotEmpty((new \Pipelines())->get($candidate,$this->job));
        }
        self::assertSame(2,(int) $this->countRows('SELECT COUNT(*) FROM candidate_joborder'));
    }

}
