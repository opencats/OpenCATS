<?php
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\DataProvider;
require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class SectorAuthorizationTest extends AuthorizationTestCase
{
    public function testSectorAdministrationRejectsOrdinaryEditors(): void
    {
        require_once './modules/settings/SettingsUI.php';
        $_GET['a'] = 'sectors';
        $_POST = array('postback'=>'postback','name'=>'Unauthorised','isActive'=>'1');
        $ui = $this->ui(SettingsUI::class);
        (new ReflectionProperty(SettingsUI::class, '_realAccessLevel'))->setValue($ui, ACCESS_LEVEL_EDIT);
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $ui->handleRequest());
        self::assertSame(array(), $this->queries);
    }

    public function testSectorModelRejectsOrdinaryEditors(): void
    {
        require_once './lib/Sectors.php';
        $this->session->method('getRealAccessLevel')->willReturn(ACCESS_LEVEL_EDIT);
        $this->expectException(RuntimeException::class);
        (new Sectors())->save(null, 'Unauthorised', true);
    }

    public function testControllersRejectArrayAssignmentsBeforeMutation(): void
    {
        require_once './modules/settings/SettingsUI.php';
        require_once './lib/DataGrid.php';
        require_once './modules/joborders/JobOrdersUI.php';
        $_POST = array('sectorID'=>array('1'));
        foreach (array(JobOrdersUI::class) as $class)
        {
            $ui = $this->ui($class);
            $this->assertCommonError(COMMONERROR_BADFIELDS, fn() => (new ReflectionMethod($class, 'getSectorInput'))->invoke($ui));
        }
        self::assertSame(array(), $this->queries);
    }

    public function testEffectiveAdministratorRestrictionIsEnforced(): void
    {
        require_once './modules/settings/SettingsUI.php';
        $_GET['a'] = 'sectors';
        $ui = $this->ui(SettingsUI::class);
        (new ReflectionProperty(SettingsUI::class, '_realAccessLevel'))->setValue($ui, ACCESS_LEVEL_SA);
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $ui->handleRequest());
        $this->session->method('getRealAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        require_once './lib/Sectors.php';
        try { (new Sectors())->save(null, 'Forbidden', true); self::fail('Effective permission ignored'); }
        catch (RuntimeException $e) { self::assertSame('Administrator access required.', $e->getMessage()); }
        self::assertSame(array(), $this->queries);
    }

    public function testReadOnlyUserCannotUpdateJobSector(): void
    {
        require_once './lib/DataGrid.php';
        require_once './modules/joborders/JobOrdersUI.php';
        $this->accessLevel = ACCESS_LEVEL_READ;
        $_GET['a'] = 'edit';
        $_POST = array('postback' => 'postback', 'jobOrderID' => '1', 'sectorID' => '2');
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $this->ui(JobOrdersUI::class)->handleRequest());
        self::assertSame(array(), $this->queries);
    }

    public static function invalidNames(): array
    {
        return array(array(array('name')), array(''), array('  '), array('Unclassified'),
            array("bad\nname"), array(str_repeat('a', 65)), array("\xff"));
    }

    #[DataProvider('invalidNames')]
    public function testInvalidLabelsAreRejected($name): void
    {
        require_once './lib/Sectors.php';
        $this->accessLevel = ACCESS_LEVEL_SA;
        $this->session->method('getRealAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        $this->expectException(InvalidArgumentException::class);
        (new Sectors())->save(null, $name, true);
    }

    public static function invalidIDs(): array
    {
        return array(array(array(1)),array(false),array(true),array(0),array(-1),array('1x'),array('1.0'),array(' 1'),array('2147483648'));
    }

    #[DataProvider('invalidIDs')]
    public function testInvalidIDsAreRejected($id): void
    {
        require_once './lib/Sectors.php';
        $this->expectException(InvalidArgumentException::class);
        Sectors::normalizeID($id);
    }

    public function testNullAndOmissionDiffer(): void
    {
        require_once './lib/DataGrid.php';
        require_once './modules/joborders/JobOrdersUI.php';
        $method = new ReflectionMethod(JobOrdersUI::class, 'getSectorInput');
        $ui = $this->ui(JobOrdersUI::class);
        self::assertFalse($method->invoke($ui));
        $_POST['sectorID'] = '';
        self::assertNull($method->invoke($ui));
        self::assertSame(1, Sectors::normalizeID('1'));
    }

    public static function failurePoints(): array
    {
        return array_map(fn($n) => array($n), range(1, 12));
    }

    #[DataProvider('failurePoints')]
    public function testMigrationFailureCannotAdvanceVersion($failAt): void
    {
        global $maintPage;
        require_once './modules/install/Schema.php';
        require_once './lib/ModuleUtility.php';
        $maintPage = true;
        $db = $this->createStub(DatabaseConnection::class);
        $db->method('getAssoc')->willReturn(array('version'=>397));
        $db->method('getAllAssoc')->willReturn(array());
        $db->method('makeQueryString')->willReturnCallback(fn($s)=>"'".$s."'");
        $queries = array();
        $db->method('query')->willReturnCallback(function ($sql) use (&$queries, $failAt) {
            $queries[] = $sql;
            return count($queries) !== $failAt;
        });
        (new ReflectionProperty(DatabaseConnection::class, '_instance'))->setValue(null, $db);
        try
        {
            (new ReflectionMethod(ModuleUtility::class, 'processModuleSchema'))->invoke(null, 'install', array(398=>CATSSchema::get()[398]));
            self::fail('Migration failure should stop version advancement');
        }
        catch (RuntimeException $e)
        {
            self::assertCount($failAt, $queries);
            self::assertStringNotContainsString('UPDATE module_schema', implode("\n", $queries));
        }
        finally { $maintPage = false; }
    }
}
