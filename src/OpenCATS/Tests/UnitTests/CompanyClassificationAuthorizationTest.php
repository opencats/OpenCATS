<?php
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class CompanyClassificationAuthorizationTest extends AuthorizationTestCase
{
    public function testLabelRouteRejectsNonAdministrator(): void
    {
        require_once './modules/settings/SettingsUI.php';
        $_GET['a'] = 'companyClassification';
        $_POST = array('postback' => 'postback', 'tierLabels' => array('A'=>'New','B'=>'B','C'=>'C','D'=>'D'));
        $ui = $this->ui(SettingsUI::class);
        (new ReflectionProperty(SettingsUI::class, '_realAccessLevel'))->setValue($ui, ACCESS_LEVEL_EDIT);
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $ui->handleRequest());
        self::assertSame(array(), $this->queries);
    }

    public function testLabelModelAlsoRejectsNonAdministrator(): void
    {
        require_once './lib/CompanySettings.php';
        $this->session->method('getRealAccessLevel')->willReturn(ACCESS_LEVEL_EDIT);
        $this->expectException(RuntimeException::class);
        (new CompanySettings())->setAll(array('A'=>'New','B'=>'B','C'=>'C','D'=>'D'));
    }

    public function testInvalidLabelsCannotPartiallySave(): void
    {
        require_once './lib/CompanySettings.php';
        $this->accessLevel = ACCESS_LEVEL_SA;
        $this->session->method('getRealAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        try
        {
            (new CompanySettings())->setAll(array('A'=>'Valid','B'=>array('bad'),'C'=>'C','D'=>'D'));
            self::fail('Invalid labels accepted');
        }
        catch (InvalidArgumentException $e)
        {
            self::assertSame(array(), $this->queries);
        }
    }

    public function testControllerRejectsArraysBeforeMutation(): void
    {
        require_once './modules/companies/CompaniesUI.php';
        $_POST = array('commercialTier' => array('A'));
        $ui = $this->ui(CompaniesUI::class);
        $method = new ReflectionMethod(CompaniesUI::class, 'getClassificationInput');
        $this->assertCommonError(COMMONERROR_BADFIELDS, fn() => $method->invoke($ui));
        self::assertSame(array(), $this->queries);
    }

    public function testOmittedAndClearedRequestsAreDifferent(): void
    {
        require_once './modules/companies/CompaniesUI.php';
        $ui = $this->ui(CompaniesUI::class);
        $method = new ReflectionMethod(CompaniesUI::class, 'getClassificationInput');
        self::assertSame(array(false, false), $method->invoke($ui, true));
        $_POST = array('commercialTier' => '', 'relationshipStatus' => 'Fresh Prospect');
        self::assertSame(array(null, 'Fresh Prospect'), $method->invoke($ui, true));
    }

    public function testTierRenderingEscapesConfiguredLabels(): void
    {
        require_once './lib/CompanySettings.php';
        $text = CompanySettings::formatTier('A', array('A' => '<script>alert(1)</script>'));
        self::assertSame('A — &lt;script&gt;alert(1)&lt;/script&gt;', Template::escapeHtml($text));
        self::assertSame('Unclassified', CompanySettings::formatTier(null, array()));
        self::assertSame('B', CompanySettings::formatTier('B', array()));
    }
    public function testFailedMigrationDoesNotAdvanceStoredVersion(): void
    {
        global $maintPage;
        require_once './modules/install/Schema.php';
        require_once './lib/ModuleUtility.php';
        $maintPage = true;
        $db = $this->createStub(DatabaseConnection::class);
        $db->method('getAssoc')->willReturn(array('version' => 395));
        $db->method('getAllAssoc')->willReturn(array());
        $db->method('makeQueryString')->willReturnCallback(fn($value) => "'" . $value . "'");
        $queries = array();
        $db->method('query')->willReturnCallback(function ($sql) use (&$queries) {
            $queries[] = $sql;
            return false;
        });
        (new ReflectionProperty(DatabaseConnection::class, '_instance'))->setValue(null, $db);
        try
        {
            (new ReflectionMethod(ModuleUtility::class, 'processModuleSchema'))->invoke(null, 'install', array(396 => CATSSchema::get()[396]));
            self::fail('Migration failure should stop the runner.');
        }
        catch (RuntimeException $e)
        {
            self::assertStringContainsString('Unable to add Company classification', $e->getMessage());
            self::assertCount(1, $queries);
            self::assertStringStartsWith('ALTER TABLE', $queries[0]);
        }
        finally
        {
            $maintPage = false;
        }
    }
}
