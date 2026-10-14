<?php
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class HandoverAuthorizationTest extends AuthorizationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once './modules/settings/SettingsUI.php';
        require_once './lib/Tasks.php';
    }

    public function testRoleOverrideCannotGrantAdministratorHandover(): void
    {
        $this->accessLevel = ACCESS_LEVEL_SA;
        $_GET['a'] = 'handover';
        $ui = $this->ui(SettingsUI::class);
        (new ReflectionProperty(SettingsUI::class, '_realAccessLevel'))->setValue($ui, ACCESS_LEVEL_EDIT);
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $ui->handleRequest());
        self::assertSame(array(), $this->queries);
    }

    public function testModelRejectsOrdinaryEditorEvenWithAclOverride(): void
    {
        $this->loggedIn = true;
        $this->accessLevel = ACCESS_LEVEL_SA;
        $this->session->method('getRealAccessLevel')->willReturn(ACCESS_LEVEL_EDIT);
        $this->expectException(RuntimeException::class);
        (new Users())->previewHandover(1, 2, array('company'));
    }

    private function administrator(): void
    {
        $this->loggedIn = true;
        $this->accessLevel = ACCESS_LEVEL_SA;
        $this->session->method('getRealAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        $this->db->method('getAssoc')->willReturn(array('userID' => 10, 'accessLevel' => ACCESS_LEVEL_SA, 'categories' => '', 'fullName' => 'Administrator'));
    }

    public function testInvalidCsrfRejectsExecutionAndIndependentDisableBeforeWrites(): void
    {
        $this->administrator();
        $this->session->method('isCSRFTokenValid')->willReturn(false);
        foreach (array(null, array('invalid'), 'invalid') as $token)
        {
            foreach (array(fn() => (new Users())->executeHandover(array(), $token),
                fn() => (new Users())->disableForHandover(20, $token)) as $call)
            {
                try { $call(); self::fail('CSRF must reject'); }
                catch (RuntimeException $e) { self::assertNotEmpty($e->getMessage()); }
            }
        }
        self::assertSame(array(), $this->queries);
    }

    public function testMalformedSelectionCannotWrite(): void
    {
        $this->administrator();
        foreach (array(array(array(1), 2, array('company'), array()), array(1, 2, array('unknown'), array()),
            array(1, 2, array('company', 'company'), array()), array(1, 2, array('company'), array('company' => array(2)))) as $args)
        {
            try { (new Users())->previewHandover(...$args); self::fail('Invalid selection must reject'); }
            catch (InvalidArgumentException $e) { self::assertNotEmpty($e->getMessage()); }
        }
        self::assertSame(array(), $this->queries);
    }

    public function testStringSessionIdentityCannotDisableItself(): void
    {
        $this->administrator();
        $session = $this->createStub(CATSSession::class);
        $session->method('isLoggedIn')->willReturn(true);
        $session->method('getUserID')->willReturn('10');
        $session->method('getRealAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        $session->method('getAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        $session->method('isCSRFTokenValid')->willReturn(true);
        $_SESSION['CATS'] = $session;
        try { (new Users())->disableForHandover('10', 'valid'); self::fail('Self-disable must reject'); }
        catch (InvalidArgumentException $e) { self::assertSame('Cannot disable your own account.', $e->getMessage()); }
        self::assertSame(array(), $this->queries);
    }

    public function testDisabledRecipientIsRejected(): void
    {
        $this->db->method('getAssoc')->willReturn(array('accessLevel' => ACCESS_LEVEL_DISABLED, 'categories' => ''));
        $this->expectException(InvalidArgumentException::class);
        (new Users())->validateHandoverRecipient(20, 'company');
    }
}
