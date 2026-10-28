<?php
use PHPUnit\Framework\TestCase;

require_once LEGACY_ROOT . '/constants.php';
require_once LEGACY_ROOT . '/lib/DatabaseConnection.php';
require_once LEGACY_ROOT . '/lib/Session.php';
require_once LEGACY_ROOT . '/lib/DateUtility.php';
require_once LEGACY_ROOT . '/lib/WarningReviews.php';
require_once LEGACY_ROOT . '/lib/Tasks.php';

/** Database-free query-budget checks for factual warnings which have no personal review action. */
class WarningOversightReadTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SESSION['CATS']);
        (new ReflectionProperty(DatabaseConnection::class, '_instance'))->setValue(null, null);
    }

    private function oversightWithCommitment($invalidAssignee)
    {
        $session = $this->createStub(CATSSession::class);
        $session->method('getUserID')->willReturn(1);
        $session->method('isLoggedIn')->willReturn(true);
        $session->method('getAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        $session->method('getRealAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        $session->method('getTimeZoneOffset')->willReturn(0);
        $_SESSION['CATS'] = $session;
        $db = $this->getMockBuilder(DatabaseConnection::class)->disableOriginalConstructor()
            ->onlyMethods(array('getAssoc','getAllAssoc','makeQueryString','makeQueryInteger'))->getMock();
        $db->method('getAssoc')->willReturnCallback(function ($sql) {
            if (str_contains($sql,'SELECT access_level')) return array('access_level'=>ACCESS_LEVEL_SA);
            if (str_contains($sql,'SELECT NOW()')) return array('clock'=>date('Y-m-d H:i:s'));
            return array();
        });
        $db->method('makeQueryString')->willReturnCallback(fn($value)=>"'" . str_replace("'","''",$value) . "'");
        $db->method('makeQueryInteger')->willReturnCallback(fn($value)=>(string) (int) $value);
        $auditReads = 0;
        $db->expects(self::atLeastOnce())->method('getAllAssoc')->willReturnCallback(function ($sql) use ($invalidAssignee, &$auditReads) {
            // Recruitment-source status History is independent of personal review audit.
            if (str_contains($sql,'FROM warning_review') ||
                (str_contains($sql,'FROM history') && str_contains($sql, "the_field = 'warning_review'"))) ++$auditReads;
            if (str_contains($sql,'AS invalidAssignee') && str_contains($sql,'FROM task'))
                return array(array('taskID'=>1,'title'=>'Overdue commitment','dueDate'=>date('Y-m-d',strtotime('-1 day')),
                    'priority'=>'normal','assignedTo'=>$invalidAssignee ? 2 : 1,'dataItemType'=>null,'dataItemID'=>null,'candidateJobOrderID'=>null,
                    'contextJobOrderID'=>null,'invalidAssignee'=>$invalidAssignee,'createdBy'=>1));
            return array();
        });
        (new ReflectionProperty(DatabaseConnection::class, '_instance'))->setValue(null, $db);
        $result = (new WarningReviews())->getOversight();
        self::assertSame(0,$auditReads,'Non-reviewable factual warnings must not acquire unrelated personal review state or audit.');
        self::assertSame(0,$result['events']);
        self::assertSame(array(),$result['eventCounts']);
        self::assertSame(array(),array_filter($result['rows'],fn($row)=>$row['state']!==null || $row['status']!=='outstanding'));
        return $result;
    }

    public function testOverdueCommitmentRemainsVisibleWithoutReviewReads(): void
    {
        $result=$this->oversightWithCommitment(false);
        self::assertCount(1,$result['rows']);
        self::assertSame('W01',$result['rows'][0]['identity']['warning_rule']);
    }

    public function testInvalidResponsibilityAndOverdueConditionsRemainDistinctWithoutReviewReads(): void
    {
        $result=$this->oversightWithCommitment(true);
        self::assertCount(2,$result['rows']);
        self::assertSame(array('W01','W08'),array_column(array_column($result['rows'],'identity'),'warning_rule'));
    }
}
