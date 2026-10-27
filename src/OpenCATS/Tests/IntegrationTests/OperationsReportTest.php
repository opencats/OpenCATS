<?php
namespace OpenCATS\Tests\IntegrationTests;

/** Stage 7a: known populations in the disposable native integration database. */
class OperationsReportTest extends DatabaseTestCase
{
    private $db;
    private $access = 200;
    private $overrides = array();
    private $company;
    private $candidate;
    private $job;

    protected function setUp(): void
    {
        parent::setUp();
        require_once LEGACY_ROOT . '/lib/CATSUtility.php';
        require_once LEGACY_ROOT . '/lib/Session.php';
        require_once LEGACY_ROOT . '/lib/Statistics.php';
        require_once LEGACY_ROOT . '/lib/Tasks.php';
        require_once LEGACY_ROOT . '/lib/WarningReviews.php';
        $session = $this->createStub(\CATSSession::class);
        $session->method('getUserID')->willReturn(1);
        $session->method('getAccessLevel')->willReturnCallback(fn($key) => $this->overrides[$key] ?? $this->access);
        $session->method('getRealAccessLevel')->willReturnCallback(fn() => $this->access);
        $session->method('isLoggedIn')->willReturn(true);
        $session->method('isTimeFormat24')->willReturn(true);
        $session->method('getTimeZoneOffset')->willReturn(0);
        $_SESSION['CATS'] = $session;
        $this->db = \DatabaseConnection::getInstance();
        $this->db->query("INSERT INTO company (name,owner) VALUES ('Visible account',1)");
        $this->company = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO candidate (first_name,last_name,is_admin_hidden) VALUES ('Restricted','Person',1)");
        $this->candidate = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO joborder (company_id,title,recruiter,owner,desk_id,status,date_created) VALUES ($this->company,'Visible vacancy',2,1,1,'Active',NOW())");
        $this->job = (int) $this->db->getLastInsertID();
    }

    protected function tearDown(): void
    {
        unset($_SESSION['CATS']);
        (new \ReflectionProperty(\DatabaseConnection::class, '_instance'))->setValue(null,null);
        parent::tearDown();
    }

    private function report($changes = array())
    {
        return (new \Statistics())->getOperations(\Statistics::operationsFilters($changes));
    }

    private function task($title, $type, $id, $assignee = '2', $due = 'CURDATE()-INTERVAL 1 DAY', $creator = 1)
    {
        $this->db->query("INSERT INTO task (title,status,priority,purpose,assigned_to,created_by,data_item_type,data_item_id,due_date,date_created,date_modified)
            VALUES ('$title','open','normal','general',$assignee,$creator,$type,$id,$due,NOW(),NOW())");
    }

    public function testActorPeriodAndDeskScopeReconcileWithContributors(): void
    {
        $this->db->query("INSERT INTO activity (data_item_type,data_item_id,entered_by,type,date_occurred,joborder_id) VALUES
            (200,$this->company,2,500,CURDATE(),$this->job),
            (200,$this->company,2,200,CURDATE(),$this->job),
            (200,$this->company,1,300,CURDATE()-INTERVAL 400 DAY,NULL)");
        $this->task('Due work',400,$this->job);
        $report = $this->report(array('view'=>'activity','recruiter'=>'2','desk'=>'1'));
        self::assertSame(2,$report['totals']['activity']);
        self::assertCount(2,$report['details']);
        self::assertSame(2,$report['groups']['recruiter'][2]['activity']);
        self::assertSame(1,$report['groups']['recruiter'][2]['conversations']);
        self::assertSame(1,$report['groups']['recruiter'][2]['emails']);
        self::assertSame(1,$report['totals']['jobs']);
        self::assertSame(1,$report['totals']['tasks']);
        self::assertSame(1,$report['totals']['overdue']);
        $past = $this->report(array('range'=>'custom','start'=>date('Y-m-d',strtotime('-20 days')),'end'=>date('Y-m-d',strtotime('-10 days'))));
        self::assertSame(0,$past['totals']['activity']);
        self::assertSame($report['totals']['jobs'],$past['totals']['jobs']);
        self::assertSame($report['totals']['tasks'],$past['totals']['tasks']);
    }

    public function testRestrictedParentsAndHiddenJobsCannotLeakThroughCountsOrDeskChoices(): void
    {
        $this->task('Hidden candidate work',100,$this->candidate);
        $this->db->query("INSERT INTO activity (data_item_type,data_item_id,entered_by,type,date_occurred) VALUES (100,$this->candidate,2,500,NOW())");
        $this->db->query("INSERT INTO joborder (title,status,is_admin_hidden,desk_id,recruiter) VALUES ('Secret vacancy','Active',1,3,2)");
        $hidden = (int) $this->db->getLastInsertID();
        $this->task('Hidden job work',400,$hidden);
        $this->db->query("INSERT INTO activity (data_item_type,data_item_id,entered_by,type,date_occurred,joborder_id) VALUES (200,$this->company,2,200,NOW(),$hidden)");
        $r = $this->report(array('view'=>'tasks'));
        self::assertSame(0,$r['totals']['activity']);
        self::assertSame(0,$r['totals']['tasks']);
        self::assertSame(1,$r['totals']['jobs']);
        self::assertSame(array(),$r['details']);
        self::assertArrayNotHasKey(3,$r['choices']['desk']);
        $this->overrides['companies.show'] = 0;
        $this->task('Unreadable company work',200,$this->company);
        self::assertSame(0,$this->report()['totals']['tasks']);
        $this->overrides['joborders.show'] = 0;
        $r = $this->report();
        self::assertFalse($r['available']['jobs']);
        self::assertFalse($r['available']['warnings']);
    }

    public function testNativePersonalTaskVisibilityAndUnassignedWork(): void
    {
        $this->task('Own personal work','NULL','NULL','1');
        $this->task('Other personal work','NULL','NULL','2','NULL',2);
        $this->task('Unassigned linked work',400,$this->job,'NULL','NULL');
        $r = $this->report(array('view'=>'tasks'));
        self::assertSame(2,$r['totals']['tasks']);
        self::assertSame(1,$r['totals']['overdue']);
        self::assertArrayHasKey(0,$r['groups']['recruiter']);
        self::assertSame(0,$r['groups']['recruiter'][2]['activity']);
        $this->overrides['tasks.show'] = 0;
        $r = $this->report();
        self::assertFalse($r['available']['tasks']);
        self::assertSame(array(),$r['details']);
    }

    public function testWarningsUseNativeSourcesAndHistoryDoesNotInflateSubjects(): void
    {
        $this->access = ACCESS_LEVEL_SA;
        $this->db->query('UPDATE user SET access_level=500 WHERE user_id=1');
        $this->db->query("UPDATE joborder SET first_actionable_at=NOW()-INTERVAL 30 DAY,is_speculative=0 WHERE joborder_id=$this->job");
        $this->db->query("UPDATE company SET commercial_tier='A',relationship_status='Client' WHERE company_id=$this->company");
        $native = (new \Tasks())->getRecruitmentWarnings()['warnings'];
        $subjects = array();
        foreach ($native as $w) $subjects[$w['entityType'].':'.$w['entityID']] = true;
        $before = $this->report(array('view'=>'warnings'));
        self::assertSame(count($subjects),$before['totals']['warnings']);
        $reviews = new \WarningReviews();
        foreach (array(1,5) as $days)
        {
            $snapshot = $reviews->get('W02',400,$this->job,'no_recorded_submission');
            $reviews->save('W02',400,$this->job,'no_recorded_submission',0,'remind_later',null,
                \WarningReviews::reminderDates()[$days],$snapshot['version'],$snapshot['evidence'],'stage7a-review-request-'.$days);
        }
        $after = $this->report(array('view'=>'warnings'));
        self::assertSame($before['totals']['warnings'],$after['totals']['warnings']);
        self::assertSame(1,$after['dispositions']['remind_later']);
        self::assertSame(2,$after['reviewActions']);
        self::assertSame(1,$after['repeatedReviews']);
        $snapshot = $reviews->get('W07',200,$this->company,'no_next_action');
        $reviews->save('W07',200,$this->company,'no_next_action',0,'close','already_handled',
            date('Y-m-d',strtotime('+5 days')),$snapshot['version'],$snapshot['evidence'],'stage7a-review-close');
        $closed = $this->report();
        self::assertSame($before['totals']['warnings'],$closed['totals']['warnings']);
        self::assertSame(1,$closed['dispositions']['close']);
        $this->db->query("UPDATE warning_review SET review_until=CURDATE() WHERE warning_rule='W07'");
        self::assertSame(1,$this->report()['dispositions']['reappeared']);
        $past = $this->report(array('range'=>'custom','start'=>date('Y-m-d',strtotime('-20 days')),'end'=>date('Y-m-d',strtotime('-10 days'))));
        self::assertSame(0,$past['reviewActions']);
        $event = $this->db->getAssoc("SELECT new_value FROM history WHERE the_field='warning_review' ORDER BY history_id LIMIT 1");
        $payload = $this->db->makeQueryString($event['new_value']);
        $values = array_fill(0,205,"(400,$this->job,'warning_review',NOW(),$payload)");
        $this->db->query('INSERT INTO history (data_item_type,data_item_id,the_field,set_date,new_value) VALUES '.implode(',',$values));
        $batched = $this->report();
        self::assertSame(208,$batched['reviewActions']);
        self::assertSame($before['totals']['warnings'],$batched['totals']['warnings']);
        self::assertCount($after['totals']['warnings'],$after['details']);
        $this->overrides['joborders.show']=0;
        self::assertFalse($this->report()['available']['warnings']);
    }

    public function testWarningResponsibilitiesRemainNativeWithoutArtificialDistribution(): void
    {
        $this->access = ACCESS_LEVEL_SA;
        $this->db->query('UPDATE user SET access_level=500 WHERE user_id=1');
        $this->db->query("UPDATE joborder SET owner=NULL,first_actionable_at=NOW()-INTERVAL 30 DAY,is_speculative=0 WHERE joborder_id=$this->job");
        $all = $this->report(array('view'=>'warnings'));
        self::assertSame(1,$all['totals']['warnings']);
        self::assertSame(1,$all['groups']['recruiter'][0]['warnings']);
        self::assertSame(1,$all['groups']['recruiter'][2]['warnings']);
        self::assertSame(1,$this->report(array('recruiter'=>'0'))['totals']['warnings']);
        self::assertSame(1,$this->report(array('recruiter'=>'2'))['totals']['warnings']);
        self::assertStringContainsString('Unassigned',$all['details'][0]['actorName']);
        self::assertStringContainsString('W08',$all['details'][0]['status']);
        self::assertStringContainsString('W02',$all['details'][0]['status']);
    }

    public function testBusyTaskPageIsBoundedAndReconciles(): void
    {
        for ($i=0;$i<205;++$i) $this->task('Work '.$i,400,$this->job);
        $r = $this->report(array('view'=>'tasks','page'=>'2'));
        self::assertSame(205,$r['totals']['tasks']);
        self::assertSame(205,$r['pager']->getTotalRows());
        self::assertCount(25,$r['details']);
        self::assertSame(9.0,$r['pager']->getTotalPages());
    }

    public function testEmptyReadablePopulationIsZeroAndDeniedSourcesAreUnavailable(): void
    {
        $this->db->query("DELETE FROM joborder");
        $r = $this->report();
        self::assertSame(array('activity'=>0,'jobs'=>0,'tasks'=>0,'overdue'=>0,'warnings'=>0),$r['totals']);
        self::assertTrue($r['available']['activity']);
        $this->overrides['activity.listByViewDataGrid']=0;
        self::assertFalse($this->report()['available']['activity']);
    }

    public function testActivityComparisonUsesSameReadableRecordedActorsAndDesk(): void
    {
        $period = \Statistics::operationsComparisonPeriod(\Statistics::operationsFilters(array('view'=>'activity')));
        $current = $period['currentStart']; $previous = $period['previousStart'];
        foreach (array(500,200,300,400,800) as $type)
            $this->db->query("INSERT INTO activity (data_item_type,data_item_id,entered_by,type,date_occurred,joborder_id)
                VALUES (200,$this->company,2,$type,'$current 12:00:00',$this->job)");
        $this->db->query("INSERT INTO activity (data_item_type,data_item_id,entered_by,type,date_occurred,joborder_id) VALUES
            (200,$this->company,2,500,'$previous 12:00:00',$this->job),
            (200,$this->company,2,200,'$previous 12:00:00',$this->job),
            (200,$this->company,1,500,'$previous 12:00:00',NULL),
            (100,$this->candidate,2,500,'$previous 12:00:00',$this->job)");
        $r = $this->report(array('view'=>'activity','recruiter'=>'2','desk'=>'1'));
        self::assertSame(5,$r['totals']['activity']);
        self::assertSame(5,$r['comparison']['current']);
        self::assertSame(2,$r['comparison']['previous']);
        $group=$r['groups']['recruiter'][2];
        self::assertSame(1,$group['statusChanges']);
        self::assertSame(1,$group['other']);
        self::assertSame(5,$group['calls']+$group['emails']+$group['meetings']+$group['statusChanges']+$group['other']);
        $this->overrides['activity.listByViewDataGrid']=0;
        self::assertNull($this->report(array('view'=>'activity'))['comparison']);
        self::assertNull($this->report()['comparison']);
    }

    public function testEquivalentPeriodsCapShorterMonthsAndCompareEqualElapsedDays(): void
    {
        $session=$_SESSION['CATS'];
        $offset=(int) round((strtotime('2026-03-31 12:00:00')-time())/3600);
        $_SESSION['CATS']=new class($offset) {
            private $offset;
            public function __construct($offset) { $this->offset=$offset; }
            public function getTimeZoneOffset() { return $this->offset; }
        };
        try
        {
            $month=\Statistics::operationsComparisonPeriod(array('range'=>'month'));
            self::assertSame('2026-03-01',$month['currentStart']);
            self::assertSame('2026-03-28',$month['currentEnd']);
            self::assertSame('2026-02-01',$month['previousStart']);
            self::assertSame('2026-02-28',$month['previousEnd']);
            self::assertSame(28,$month['days']);
            $week=\Statistics::operationsComparisonPeriod(array('range'=>'week'));
            self::assertSame('2026-03-23',$week['previousStart']);
            self::assertSame('2026-03-24',$week['previousEnd']);
            self::assertSame(2,$week['days']);
            $quarter=\Statistics::operationsComparisonPeriod(array('range'=>'quarter'));
            self::assertSame('2025-10-01',$quarter['previousStart']);
            self::assertSame('2025-12-29',$quarter['previousEnd']);
            $custom=\Statistics::operationsComparisonPeriod(array('range'=>'custom','start'=>'2026-03-10','end'=>'2026-03-12'));
            self::assertSame('2026-03-07',$custom['previousStart']);
            self::assertSame('2026-03-09',$custom['previousEnd']);
        }
        finally { $_SESSION['CATS']=$session; }
    }

    public function testInvalidArrayFilterFailsClosed(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        \Statistics::operationsFilters(array('desk'=>array('1')));
    }

    public function testDeniedReportCannotEnumerateAnything(): void
    {
        $this->overrides['reports.show']=0;
        $this->expectException(\RuntimeException::class);
        $this->report();
    }
}
