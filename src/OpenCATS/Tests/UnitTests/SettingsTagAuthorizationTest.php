<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class SettingsTagAuthorizationTest extends AuthorizationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once './modules/settings/SettingsUI.php';
        $_POST = array('postback' => 'postback', 'tag_id' => 42, 'tag_parent_id' => 1, 'tag_title' => 'Test tag');
    }

    public static function deniedRoutes(): array
    {
        return array(array('tags', 'GET'), array('tags', 'POST'),
            array('ajax_tags_add', 'POST'), array('ajax_tags_upd', 'POST'), array('ajax_tags_del', 'POST'));
    }

    #[DataProvider('deniedRoutes')]
    public function testDirectRequestsRequireTagManagementPermission($action, $method): void
    {
        $this->accessOverrides['settings.tags'] = ACCESS_LEVEL_EDIT;
        $_GET['a'] = $action;
        $_SERVER['REQUEST_METHOD'] = $method;
        if ($method === 'GET') $_POST = array();
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $this->ui(SettingsUI::class)->handleRequest());
        self::assertSame(array(), $this->queries);
    }

    public function testTagAclOverrideIsRespectedForAdministrators(): void
    {
        $this->accessLevel = ACCESS_LEVEL_SA;
        $this->accessOverrides['settings.tags'] = ACCESS_LEVEL_EDIT;
        $_GET['a'] = 'ajax_tags_del';
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $this->ui(SettingsUI::class)->handleRequest());
        self::assertSame(array(), $this->queries);
    }

    public function testSiteAdministratorCanOpenTagPage(): void
    {
        $this->accessLevel = ACCESS_LEVEL_SA;
        $_GET['a'] = 'tags';
        $_POST = array();
        $this->db->method('getAllAssoc')->willReturn(array());
        $ui = $this->ui(SettingsUI::class);
        $template = $this->createMock(Template::class);
        $template->expects(self::once())->method('display')->with('./modules/settings/tags.tpl');
        (new ReflectionProperty(UserInterface::class, '_template'))->setValue($ui, $template);
        $ui->handleRequest();
    }

    public static function mutationRoutes(): array
    {
        return array(array('ajax_tags_add', 'INSERT INTO'), array('ajax_tags_upd', 'UPDATE'), array('ajax_tags_del', 'DELETE FROM'));
    }

    #[DataProvider('mutationRoutes')]
    public function testSiteAdministratorCanMutateTags($action, $verb): void
    {
        $this->accessOverrides['settings.tags'] = ACCESS_LEVEL_SA;
        $_GET['a'] = $action;
        ob_start();
        try
        {
            $this->ui(SettingsUI::class)->handleRequest();
        }
        finally
        {
            ob_end_clean();
        }
        self::assertCount(1, $this->queries);
        self::assertStringStartsWith($verb, $this->queries[0]);
    }
}
