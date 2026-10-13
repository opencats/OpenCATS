<?php
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class JobOrderEvidenceAuthorizationTest extends AuthorizationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once './lib/DataGrid.php';
        require_once './modules/joborders/JobOrdersUI.php';
    }

    public function testSharedRecruitmentReviewEndpointIsRetired(): void
    {
        $_GET['a'] = 'reviewRecruitmentWarning';
        $_POST = array('jobOrderID' => '7', 'warningCode' => 'W02', 'reviewAction' => 'review');
        foreach (array('GET', 'POST') as $method)
        {
            $_SERVER['REQUEST_METHOD'] = $method;
            $this->assertCommonError(COMMONERROR_BADFIELDS, fn() => $this->ui(JobOrdersUI::class)->handleRequest());
        }
        self::assertSame(array(), $this->queries);
    }

    public function testCheckboxInputValidation(): void
    {
        $ui = $this->ui(JobOrdersUI::class);
        $method = new ReflectionMethod(JobOrdersUI::class, 'getSpeculativeInput');
        self::assertSame(0, $method->invoke($ui));
        foreach (array('0' => 0, '1' => 1) as $value => $expected)
        {
            $_POST['isSpeculative'] = (string) $value;
            self::assertSame($expected, $method->invoke($ui));
        }
        foreach (array(array('1'), null, 'on', '2', '') as $invalid)
        {
            $_POST['isSpeculative'] = $invalid;
            $this->assertCommonError(COMMONERROR_BADFIELDS, fn() => $method->invoke($ui));
        }
        self::assertSame(array(), $this->queries);
    }

    public function testAddAndEditRequireNativePermission(): void
    {
        $this->accessLevel = ACCESS_LEVEL_READ;
        foreach (array('add', 'edit') as $action)
        {
            $_GET['a'] = $action;
            $_POST = array('postback' => 'postback', 'jobOrderID' => '7', 'isSpeculative' => '1');
            $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $this->ui(JobOrdersUI::class)->handleRequest());
        }
        self::assertSame(array(), $this->queries);
    }

    public function testHiddenJobCannotBeConvertedByOrdinaryEditor(): void
    {
        $_GET['a'] = 'edit';
        $_POST = array('postback' => 'postback', 'jobOrderID' => '7', 'isSpeculative' => '0');
        $this->db->method('getAssoc')->willReturn(array('jobOrderID' => 7, 'isAdminHidden' => 1));
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $this->ui(JobOrdersUI::class)->handleRequest());
        $_POST = array();
        $_GET['jobOrderID'] = '7';
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $this->ui(JobOrdersUI::class)->handleRequest());
        self::assertSame(array(), $this->queries);
    }

    public function testActiveRequiresConfiguredOpenMembership(): void
    {
        define('JOB_ORDER_STATUS_GROUP', array('Open' => array('Custom'), 'Closed' => array('Active')));
        self::assertFalse(JobOrderStatuses::isActionable('Active'));
        self::assertFalse(JobOrderStatuses::isActionable('Custom'));
        self::assertFalse(JobOrderStatuses::isActionable('On Hold'));
    }
}
