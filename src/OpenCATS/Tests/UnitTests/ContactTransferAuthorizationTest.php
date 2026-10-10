<?php
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class ContactTransferAuthorizationTest extends AuthorizationTestCase
{
    public function testTransferUsesExistingContactEditPermission(): void
    {
        require_once './modules/contacts/ContactsUI.php';
        $_GET['a'] = 'edit';
        $_POST = array('postback' => 'postback', 'contactID' => '10', 'companyID' => '20');
        $this->accessOverrides['contacts.edit'] = ACCESS_LEVEL_READ;
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $this->ui(ContactsUI::class)->handleRequest());
        self::assertSame(array(), $this->queries);
    }
}
