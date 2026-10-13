<?php
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class WarningReviewAuthorizationTest extends AuthorizationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once './modules/home/HomeUI.php';
        $_GET['a'] = 'reviewWarning';
        $_POST = array('csrfToken' => 'valid', 'warning_rule' => 'W02', 'data_item_type' => (string) DATA_ITEM_JOBORDER,
            'data_item_id' => '7', 'reason_key' => 'no_recorded_submission', 'reason_id' => '0',
            'expectedVersion' => '0', 'expectedEvidence' => str_repeat('a', 64),
            'requestID' => 'request-fixture-01', 'returnTo' => 'joborders', 'reviewAction' => 'remind_later',
            'reviewUntil' => '2026-10-20');
    }

    public function testGetUnauthenticatedAndInvalidCsrfAreRejectedBeforeWrites(): void
    {
        $ui = $this->ui(HomeUI::class);
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $ui->handleRequest());
        $this->loggedIn = true;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $ui->handleRequest());
        $_SERVER['REQUEST_METHOD'] = 'POST';
        unset($_POST['csrfToken']);
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $ui->handleRequest());
        foreach (array(null, array('valid'), 'invalid') as $token)
        {
            $_POST['csrfToken'] = $token;
            $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $ui->handleRequest());
        }
        self::assertSame(array(), $this->queries);
    }

    public function testInputShapesActionsAndRedirectsAreRejected(): void
    {
        $this->loggedIn = true;
        $this->session->method('isCSRFTokenValid')->willReturn(true);
        $ui = $this->ui(HomeUI::class);
        $valid = $_POST;
        foreach (array_keys($valid) as $field)
        {
            if ($field === 'csrfToken') continue;
            $_POST = array_replace($valid, array($field => array('value')));
            $this->assertCommonError(COMMONERROR_BADFIELDS, fn() => $ui->handleRequest());
        }
        foreach (array(array('returnTo' => 'https://example.com'), array('returnTo' => 'companies'),
            array('expectedVersion' => '-1'), array('expectedVersion' => '2147483647'),
            array('reviewAction' => 'close', 'closeReason' => 'already_handled'),
            array('reviewAction' => 'delete'), array('reason_key' => 'stale_relationship'),
            array('reviewing_user' => '2'), array('closeReason' => array('x'))) as $change)
        {
            $_POST = array_replace($valid, $change);
            $this->assertCommonError(COMMONERROR_BADFIELDS, fn() => $ui->handleRequest());
        }
        self::assertSame(array(), $this->queries);
    }

    public function testReadOnlySourceCannotBeReviewed(): void
    {
        $this->loggedIn = true;
        $this->session->method('isCSRFTokenValid')->willReturn(true);
        $this->accessLevel = ACCESS_LEVEL_READ;
        $this->db->method('beginTransaction')->willReturn(true);
        $this->db->method('getAssoc')->willReturn(array('access_level' => ACCESS_LEVEL_READ, 'joborder_id' => 7));
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $this->ui(HomeUI::class)->handleRequest());
        self::assertSame(array(), $this->queries);
    }
}
