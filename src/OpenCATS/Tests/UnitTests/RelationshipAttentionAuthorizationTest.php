<?php
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class RelationshipAttentionAuthorizationTest extends AuthorizationTestCase
{
    public function testAttentionSettingsRouteRejectsNonAdminAndInvalidCsrf(): void
    {
        require_once './modules/settings/SettingsUI.php';
        $_GET['a'] = 'relationshipAttention';
        $_POST = array('postback' => 'postback', 'attention' => CompanySettings::getAttentionDefaults(), 'csrfToken' => array('bad'));
        $ui = $this->ui(SettingsUI::class);
        (new ReflectionProperty(SettingsUI::class, '_realAccessLevel'))->setValue($ui, ACCESS_LEVEL_EDIT);
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $ui->handleRequest());
        (new ReflectionProperty(SettingsUI::class, '_realAccessLevel'))->setValue($ui, ACCESS_LEVEL_SA);
        $this->accessLevel = ACCESS_LEVEL_SA;
        $this->assertCommonError(COMMONERROR_BADFIELDS, fn() => $ui->handleRequest());
        self::assertSame(array(), $this->queries);
    }
}
