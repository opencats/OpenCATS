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
        // Missing accounts cannot appear in current recruiter comparisons or selections.
        self::assertArrayNotHasKey(2,$r['groups']['recruiter']);
        self::assertArrayNotHasKey(2,$r['choices']['recruiter']);
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
        self::assertArrayNotHasKey(2,$all['groups']['recruiter']);
        self::assertArrayNotHasKey(2,$all['choices']['recruiter']);
        self::assertSame(1,$this->report(array('recruiter'=>'0'))['totals']['warnings']);
        self::assertStringContainsString('Unassigned',$all['details'][0]['actorName']);
        self::assertStringContainsString('W08',$all['details'][0]['status']);
        self::assertStringContainsString('W02',$all['details'][0]['status']);
        $this->expectException(\InvalidArgumentException::class);
        $this->report(array('recruiter'=>'2'));
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
    private function outcomePair($name = 'Outcome candidate', $job = null)
    {
        $job = $job ?? $this->job;
        $this->db->query("INSERT INTO candidate (first_name,last_name,is_admin_hidden) VALUES ('" . $name . "','Person',0)");
        $candidate = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO candidate_joborder (candidate_id,joborder_id,status,added_by,date_created) VALUES ($candidate,$job,100,2,'2024-01-01')");
        return array($candidate,$job);
    }

    private function outcomeEvent($pair, $stage, $date, $from = 100)
    {
        $this->db->query("INSERT INTO candidate_joborder_status_history (candidate_id,joborder_id,status_from,status_to,date)
            VALUES ($pair[0],$pair[1],$from,$stage,'$date')");
    }

    private function outcomes($changes = array())
    {
        return $this->report(array_replace(array('view'=>'outcomes','range'=>'custom','start'=>'2024-02-01','end'=>'2024-02-29'),$changes))['outcomes'];
    }

    public function testOutcomePairsDeduplicateReentryWithoutInventingStartsOrActors(): void
    {
        $pair = $this->outcomePair();
        foreach (array(400,500,600,800) as $stage)
        {
            $this->outcomeEvent($pair,$stage,'2024-02-05 12:00:00');
            $this->outcomeEvent($pair,$stage,'2024-02-20 12:00:00');
        }
        $this->outcomeEvent($pair,700,'2024-02-21 12:00:00',800); // Reversal does not erase recorded attainment.
        $this->db->query("INSERT INTO candidate_joborder (candidate_id,joborder_id,status,added_by) VALUES ($pair[0],$pair[1],100,3)");
        $this->db->query("INSERT INTO joborder (title,status,desk_id,is_speculative) VALUES ('Second vacancy','Closed',2,1)");
        $second = (int) $this->db->getLastInsertID();
        $this->db->query("INSERT INTO candidate_joborder (candidate_id,joborder_id,status,added_by) VALUES ($pair[0],$second,100,3)");
        $this->outcomeEvent(array($pair[0],$second),400,'2024-02-02 12:00:00');
        $out = $this->outcomes();
        self::assertSame(array('submissions'=>2,'interviews'=>1,'offers'=>1,'placements'=>1),$out['totals']);
        self::assertCount(2,$out['details']);
        self::assertSame(array(0=>'Unknown event actor'),$out['choices']['recruiter']);
        self::assertSame(2,(int) $out['details'][0]['repeats']);
        self::assertSame('2024-02-05',$out['details'][0]['sourceDate']);
        self::assertArrayNotHasKey('actor',$out['details'][0]);
        self::assertArrayNotHasKey('starts',$out['totals']);
        self::assertSame(1,$out['desks'][2]['submissions']);
        foreach (array_keys($out['totals']) as $key)
        {
            self::assertSame($out['totals'][$key],array_sum(array_column($out['weeks'],$key)));
            self::assertSame($out['totals'][$key],array_sum(array_column($out['desks'],$key)));
        }
    }

    public function testOutcomeMilestonesAcrossPeriodsAreNotConversionAndZeroGrowthIsUnavailable(): void
    {
        $pair=$this->outcomePair();
        $this->outcomeEvent($pair,400,'2024-01-20 12:00:00');
        $this->outcomeEvent($pair,500,'2024-02-05 12:00:00');
        $this->outcomeEvent($pair,800,'2024-02-06 12:00:00'); // No fabricated offer.
        $out=$this->outcomes();
        self::assertSame(0,$out['totals']['submissions']);
        self::assertSame(1,$out['changes']['submissions']['previous']);
        self::assertSame(-100,$out['changes']['submissions']['percent']);
        self::assertNull($out['changes']['interviews']['percent']);
        self::assertNull($out['changes']['offers']['percent']);
        self::assertSame(0,$out['totals']['offers']);
        $this->outcomeEvent($pair,400,'2024-02-28 12:00:00');
        self::assertSame(1,$this->outcomes()['totals']['submissions']); // One pair may contribute in separate periods.
    }

    public function testOutcomeHiddenMissingAndDeniedRecordsCannotLeakThroughAggregatesOrChoices(): void
    {
        $visible=$this->outcomePair();
        $this->outcomeEvent($visible,400,'2024-02-01 00:00:00');
        $this->outcomeEvent(array($this->candidate,$this->job),400,'2024-02-01 00:00:00'); // Hidden and no relationship.
        $this->db->query("INSERT INTO candidate_joborder (candidate_id,joborder_id,status) VALUES ($this->candidate,$this->job,100)");
        $hidden=$this->outcomePair('Hidden vacancy candidate');
        $this->db->query("INSERT INTO joborder (title,is_admin_hidden,desk_id) VALUES ('Hidden',1,3)");
        $hiddenJob=(int) $this->db->getLastInsertID();
        $this->db->query("UPDATE candidate_joborder SET joborder_id=$hiddenJob WHERE candidate_id=$hidden[0]");
        $this->outcomeEvent(array($hidden[0],$hiddenJob),400,'2024-02-01 00:00:00');
        $orphan=$this->outcomePair('Removed pair');
        $this->outcomeEvent($orphan,400,'2024-02-01 00:00:00');
        $this->db->query("DELETE FROM candidate_joborder WHERE candidate_id=$orphan[0]");
        $out=$this->outcomes();
        self::assertSame(1,$out['totals']['submissions']);
        self::assertCount(1,$out['details']);
        self::assertArrayNotHasKey(3,$out['choices']['desk']);
        $this->overrides['companies.show']=0;
        $this->overrides['contacts.show']=0;
        self::assertSame(1,$this->outcomes()['totals']['submissions']); // No Company/Contact data is disclosed.
        self::assertArrayNotHasKey('companyName',$out['details'][0]);
        $this->overrides['candidates.show']=0;
        $denied=$this->outcomes();
        self::assertSame(array_fill_keys(array('submissions','interviews','offers','placements'),false),$denied['available']);
        self::assertSame(array(),$denied['details']);
        self::assertSame(array(),$denied['choices']['desk']);
    }

    public function testOutcomeCurrentDeskTransferDoesNotAwardCreatorOwnerOrFormerRecruiterCredit(): void
    {
        $pair=$this->outcomePair();
        $this->outcomeEvent($pair,400,'2024-02-01 12:00:00');
        $this->db->query("UPDATE joborder SET recruiter=0,owner=0,desk_id=3 WHERE joborder_id=$this->job");
        $out=$this->outcomes(array('desk'=>'3','recruiter'=>'0'));
        self::assertSame(1,$out['totals']['submissions']);
        self::assertSame(1,$out['desks'][3]['submissions']);
        self::assertSame('Unknown event actor',$out['choices']['recruiter'][0]);
        $this->expectException(\InvalidArgumentException::class);
        $this->outcomes(array('recruiter'=>'2'));
    }

    public function testOutcomePaginationSortingAndBatchBoundaryReconcile(): void
    {
        for ($i=0;$i<205;++$i)
        {
            $pair=$this->outcomePair(sprintf('Candidate %03d',$i));
            $this->outcomeEvent($pair,400,'2024-02-10 12:00:00');
        }
        $out=$this->outcomes(array('outcomeSort'=>'candidate','outcomeDirection'=>'asc','page'=>'2'));
        self::assertSame(205,$out['totals']['submissions']);
        self::assertSame(205,$out['pager']->getTotalRows());
        self::assertCount(25,$out['details']);
        self::assertSame('Candidate 025 Person',$out['details'][0]['title']);
        $last=$this->outcomes(array('page'=>'999','outcomeSort'=>'candidate','outcomeDirection'=>'asc'));
        self::assertEquals(9,$last['pager']->getCurrentPage());
        self::assertCount(5,$last['details']);
        self::assertSame('Candidate 200 Person',$last['details'][0]['title']);
        self::assertSame(array(),$this->outcomes(array('milestone'=>'offers'))['details']);
    }

    public function testOutcomeFractionalSessionOffsetAndHalfOpenLeapBoundary(): void
    {
        $pair=$this->outcomePair();
        $session=$this->createStub(\CATSSession::class);
        $session->method('getUserID')->willReturn(1);
        $session->method('getAccessLevel')->willReturn(200);
        $session->method('isLoggedIn')->willReturn(true);
        $session->method('getTimeZoneOffset')->willReturn(5.5);
        $_SESSION['CATS']=$session;
        $this->outcomeEvent($pair,400,'2024-02-28 18:29:59'); // Before local leap day.
        $this->outcomeEvent($pair,500,'2024-02-28 18:30:00'); // At local midnight.
        $this->outcomeEvent($pair,600,'2024-02-29 18:29:59');
        $this->outcomeEvent($pair,800,'2024-02-29 18:30:00'); // Next local day.
        $out=$this->outcomes(array('start'=>'2024-02-29','end'=>'2024-02-29','milestone'=>'interviews'));
        self::assertSame(array('submissions'=>0,'interviews'=>1,'offers'=>1,'placements'=>0),$out['totals']);
        self::assertSame('2024-02-29',$out['details'][0]['sourceDate']);
    }

    public function testOutcomeDisabledStageIsUnavailableAndUnassignedDeskRemainsExplicit(): void
    {
        $pair=$this->outcomePair();
        $this->outcomeEvent($pair,400,'2024-02-01 00:00:00');
        $this->outcomeEvent($pair,800,'2024-02-02 00:00:00');
        $this->db->query("UPDATE joborder SET desk_id=NULL WHERE joborder_id=$this->job");
        $this->db->query('UPDATE candidate_joborder_status SET is_enabled=0 WHERE candidate_joborder_status_id=800');
        $out=$this->outcomes();
        self::assertFalse($out['available']['placements']);
        self::assertSame(1,$out['desks'][0]['submissions']);
        self::assertSame('Unassigned / no Job Desk',$out['desks'][0]['name']);
    }

    public function testOutcomeTemplateUsesAuthorisedValuesEscapingAndNativePairDestinations(): void
    {
        require_once LEGACY_ROOT . '/lib/Charts.php';
        $pair=$this->outcomePair('<svg onload=alert(1)>');
        $this->outcomeEvent($pair,400,'2024-02-01 12:00:00');
        $report=$this->report(array('view'=>'outcomes','range'=>'custom','start'=>'2024-02-01','end'=>'2024-02-29'));
        $template=new \Template();
        $template->assign('operations',$report);
        ob_start();
        try { $template->display('modules/reports/Operations.tpl'); $html=ob_get_contents(); }
        finally { ob_end_clean(); }
        self::assertStringNotContainsString('<svg',$html);
        self::assertStringContainsString('&lt;svg onload=alert(1)&gt;',$html);
        self::assertStringContainsString('candidateID='.$pair[0],$html);
        self::assertStringContainsString('jobOrderID='.$pair[1],$html);
        self::assertStringContainsString('Event actor: Unknown',$html);
        self::assertStringContainsString('Recorded entries',$html);
        self::assertStringContainsString('Confirmed starts',$html);
        self::assertStringNotContainsString('companyName',$html);
    }

    public function testOutcomeSourceDenialDisabledActorAndInvalidSelectorsFailClosed(): void
    {
        $this->overrides['joborders.show']=0;
        self::assertFalse($this->outcomes()['available']['submissions']);
        foreach (array(array('milestone'=>array('submissions')),array('outcomeSort'=>'date DESC; SELECT 1'),array('outcomeDirection'=>'sideways')) as $input)
        {
            try { \Statistics::operationsFilters($input); self::fail('Invalid outcome input must be rejected.'); }
            catch (\InvalidArgumentException $e) { self::assertNotEmpty($e->getMessage()); }
        }
        $this->db->query('UPDATE user SET access_level=0 WHERE user_id=1');
        $this->expectException(\RuntimeException::class);
        $this->outcomes();
    }

    public function testCurrentRecruiterEligibilityDoesNotRemoveRecordsFromSourceTotals(): void
    {
        $this->db->query('UPDATE user SET access_level=0 WHERE user_id=2');
        $this->task('Inactive assignee commitment',400,$this->job,2);
        $this->task('Unassigned commitment',400,$this->job,'NULL');
        $report=$this->report(array('view'=>'tasks'));
        self::assertSame(1,$report['totals']['jobs']);
        self::assertSame(2,$report['totals']['tasks']);
        self::assertArrayNotHasKey(2,$report['groups']['recruiter']);
        self::assertArrayNotHasKey(2,$report['choices']['recruiter']);
        self::assertSame(1,$report['groups']['recruiter'][0]['tasks']);
        self::assertSame(2,$report['groups']['desk'][1]['tasks']);
    }

    public function testHistoricalInactiveActivityKeepsItsOriginalContributor(): void
    {
        $this->db->query('UPDATE user SET access_level=0 WHERE user_id=2');
        $this->db->query("INSERT INTO activity (data_item_type,data_item_id,entered_by,type,date_occurred) VALUES (200,$this->company,2,500,NOW())");
        $report=$this->report(array('view'=>'activity'));
        self::assertSame(1,$report['totals']['activity']);
        self::assertSame(1,$report['groups']['recruiter'][2]['activity']);
        self::assertArrayHasKey(2,$report['choices']['recruiter']);
        self::assertSame(1,$this->report(array('view'=>'activity','recruiter'=>'2'))['totals']['activity']);
        self::assertArrayNotHasKey(2,$this->report()['choices']['recruiter']);
    }

    public function testNoWorkUsersDoNotFillCurrentRecruiterRowsOrTaskCharts(): void
    {
        $this->db->query("UPDATE joborder SET status='Closed' WHERE joborder_id=$this->job");
        $report=$this->report();
        self::assertSame(array(),$report['groups']['recruiter']);
        self::assertSame(array(),$report['choices']['recruiter']);
        require_once LEGACY_ROOT . '/lib/Charts.php';
        $this->db->query("UPDATE joborder SET status='Active' WHERE joborder_id=$this->job");
        $report=$this->report(); // Job responsibility alone must not produce zero-height Task bars.
        $template=new \Template();$template->assign('operations',$report);
        ob_start();
        try{$template->display('modules/reports/Operations.tpl');$html=ob_get_contents();}
        finally{ob_end_clean();}
        preg_match('~id="operations-task-workload".*?<script type="application/json" class="oc-chart-data">(.*?)</script>~s',$html,$match);
        $chart=json_decode($match[1],true);
        self::assertSame(array(),$chart['labels']);
        self::assertSame(array(),$chart['datasets'][0]['data']);
    }

    public function testActiveVacancyPopulationAndDetailsUseTheSameSource(): void
    {
        $this->access = ACCESS_LEVEL_SA;
        $this->db->query("INSERT INTO joborder (company_id,title,status,owner,recruiter,desk_id,date_created) VALUES
            ($this->company,'Completed Full','Full',1,1,1,NOW()),
            ($this->company,'Completed Closed','Closed',1,1,1,NOW()),
            ($this->company,'On hold requirement','On Hold',1,1,1,NOW()),
            ($this->company,'Active additional','Active',1,1,1,NOW())");
        $full=(int) $this->db->getAssoc("SELECT joborder_id AS id FROM joborder WHERE title='Completed Full'")['id'];
        $closed=(int) $this->db->getAssoc("SELECT joborder_id AS id FROM joborder WHERE title='Completed Closed'")['id'];
        $this->task('Full Job follow-up',400,$full,'1');
        $overview=$this->report();
        self::assertSame(2,$overview['totals']['jobs']);
        self::assertSame(1,$overview['totals']['tasks']);
        self::assertSame(2,array_sum(array_column($overview['groups']['desk'],'jobs')));
        $details=$this->report(array('view'=>'jobs'));
        self::assertCount(2,$details['details']);
        self::assertSame(array('Active'),array_values(array_unique(array_column($details['details'],'status'))));
        self::assertSame(1,$this->report(array('view'=>'jobs','recruiter'=>'1'))['totals']['jobs']);
        self::assertCount(4,iterator_to_array((new \JobOrders())->getOperationsRows()));
        self::assertCount(2,iterator_to_array((new \JobOrders())->getOperationsRows(true)));
        self::assertSame(2,(new \JobOrders())->getHomeCurrent()['total']);
        // Recorded historical milestones on completed requirements remain R02 outcomes.
        $this->db->query('UPDATE candidate SET is_admin_hidden=0 WHERE candidate_id='.$this->candidate);
        foreach (array($full,$closed) as $job)
        {
            $this->db->query("INSERT INTO candidate_joborder (candidate_id,joborder_id,status) VALUES ($this->candidate,$job,800)");
            $this->db->query("INSERT INTO candidate_joborder_status_history (candidate_id,joborder_id,status_from,status_to,date)
                VALUES ($this->candidate,$job,600,800,NOW())");
        }
        self::assertSame(2,$this->report(array('view'=>'outcomes'))['outcomes']['totals']['placements']);
    }

}
