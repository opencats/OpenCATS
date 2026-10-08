<?php
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\DataProvider;
require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class DeskAuthorizationTest extends AuthorizationTestCase
{
    public function testDeskAdministrationRejectsOrdinaryEditors(): void
    {
        require_once './modules/settings/SettingsUI.php';
        $_GET['a'] = 'desks';
        $_POST = array('postback'=>'postback','name'=>'Unauthorised','isActive'=>'1');
        $ui = $this->ui(SettingsUI::class);
        (new ReflectionProperty(SettingsUI::class, '_realAccessLevel'))->setValue($ui, ACCESS_LEVEL_EDIT);
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $ui->handleRequest());
        self::assertSame(array(), $this->queries);
    }

    public function testDeskModelRejectsOrdinaryEditors(): void
    {
        require_once './lib/Desks.php';
        $this->session->method('getRealAccessLevel')->willReturn(ACCESS_LEVEL_EDIT);
        $this->expectException(RuntimeException::class);
        (new Desks())->save(null, 'Unauthorised', true);
    }

    public function testControllersRejectArrayAssignmentsBeforeMutation(): void
    {
        require_once './modules/settings/SettingsUI.php';
        require_once './lib/DataGrid.php';
        require_once './modules/joborders/JobOrdersUI.php';
        $_POST = array('deskID'=>array('1'));
        foreach (array(SettingsUI::class, JobOrdersUI::class) as $class)
        {
            $ui = $this->ui($class);
            $this->assertCommonError(COMMONERROR_BADFIELDS, fn() => (new ReflectionMethod($class, 'getDeskInput'))->invoke($ui));
        }
        self::assertSame(array(), $this->queries);
    }

    public static function invalidIDs(): array
    {
        return array(array(array(1)),array(false),array(true),array(0),array(-1),array('1x'),array('1.0'),array(' 1'),array('2147483648'));
    }

    #[DataProvider('invalidIDs')]
    public function testInvalidIDsAreRejected($id): void
    {
        require_once './lib/Desks.php';
        $this->expectException(InvalidArgumentException::class);
        Desks::normalizeID($id);
    }

    public function testNullAndOmissionDiffer(): void
    {
        require_once './lib/DataGrid.php';
        require_once './modules/joborders/JobOrdersUI.php';
        $method = new ReflectionMethod(JobOrdersUI::class, 'getDeskInput');
        $ui = $this->ui(JobOrdersUI::class);
        self::assertFalse($method->invoke($ui));
        $_POST['deskID'] = '';
        self::assertNull($method->invoke($ui));
        self::assertSame(1, Desks::normalizeID('1'));
    }

    public static function failurePoints(): array
    {
        return array(array(1),array(3),array(5),array(6),array(7),array(8));
    }

    #[DataProvider('failurePoints')]
    public function testMigrationFailureCannotAdvanceVersion($failAt): void
    {
        global $maintPage;
        require_once './modules/install/Schema.php';
        require_once './lib/ModuleUtility.php';
        $maintPage = true;
        $db = $this->createStub(DatabaseConnection::class);
        $db->method('getAssoc')->willReturn(array('version'=>396));
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
            (new ReflectionMethod(ModuleUtility::class, 'processModuleSchema'))->invoke(null, 'install', array(397=>CATSSchema::get()[397]));
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
