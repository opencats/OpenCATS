<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class ExportAuthorizationTest extends AuthorizationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once './modules/export/ExportUI.php';
    }

    public static function routes(): array
    {
        return array(array('export', array()), array('export', array('ids' => '42,99')),
            array('export', array('onlySelected' => 'true', 'checked_42' => 'on')),
            array('exportByDataGrid', array('i' => 'candidates:candidatesListByViewDataGrid', 'p' => '{"exportIDs":[42,99]}')),
            array('', array()), array('unknown', array()));
    }

    #[DataProvider('routes')]
    public function testNonAdministratorCannotExportThroughAnyDispatch($action, $parameters): void
    {
        $_GET = array('a' => $action, 'dataItemType' => DATA_ITEM_CANDIDATE) + $parameters;
        $_REQUEST = $_GET;
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $this->ui(ExportUI::class)->handleRequest());
        self::assertSame(array(), $this->queries);
    }

    public static function csvSelections(): array
    {
        return array(array(array()), array(array('ids' => '42')),
            array(array('onlySelected' => 'true', 'checked_42' => 'on')));
    }

    #[DataProvider('csvSelections')]
    public function testAdministratorExportsPreserveCSV($selection): void
    {
        $this->accessLevel = ACCESS_LEVEL_SA;
        $_GET = array('a' => 'export', 'dataItemType' => DATA_ITEM_CANDIDATE) + $selection;
        $this->db->method('getAllAssoc')->willReturn(array(array('Name' => 'Person, "Example"', 'Notes' => "Line one\nLine two")));
        ob_start();
        try
        {
            $this->ui(ExportUI::class)->handleRequest();
            $csv = ob_get_contents();
        }
        finally
        {
            ob_end_clean();
        }
        self::assertSame("Name,Notes\r\n\"Person, \"\"Example\"\"\",\"Line one\nLine two\"\r\n", $csv);
    }

    public function testAdministratorCanDispatchDataGridExport(): void
    {
        $this->accessLevel = ACCESS_LEVEL_SA;
        $_GET['a'] = 'exportByDataGrid';
        $ui = $this->getMockBuilder(ExportUI::class)->disableOriginalConstructor()
            ->onlyMethods(array('onExportByDataGrid'))->getMock();
        $ui->expects(self::once())->method('onExportByDataGrid');
        $ui->handleRequest();
    }

    public static function menuRoles(): array
    {
        return array(array(false), array(true));
    }

    #[DataProvider('menuRoles')]
    public function testLegacyExportMenuFollowsExportAcl($admin): void
    {
        $this->accessOverrides['export'] = $admin ? ACCESS_LEVEL_SA : ACCESS_LEVEL_EDIT;
        $form = ExportUtility::getForm(DATA_ITEM_CANDIDATE, '42');
        if ($admin) self::assertStringContainsString('Export All Records', $form['menu']);
        else self::assertSame(array('header' => '', 'footer' => '', 'menu' => ''), $form);
    }

    public static function gridMenus(): array
    {
        $cases = array();
        foreach (array('candidates', 'companies', 'contacts', 'joborders') as $module)
        {
            foreach (array('ListByViewDataGrid', 'SavedListByViewDataGrid') as $suffix)
            {
                $prefix = $suffix === 'SavedListByViewDataGrid' && in_array($module, array('contacts', 'joborders'))
                    ? rtrim($module, 's') : $module;
                foreach (array(false, true) as $admin) $cases[] = array($module, $prefix . $suffix, $admin);
            }
        }
        return $cases;
    }

    #[DataProvider('gridMenus')]
    public function testGridMenusHideOnlyTheExportAction($module, $class, $admin): void
    {
        require_once './modules/' . $module . '/dataGrids.php';
        $grid = (new ReflectionClass($class))->newInstanceWithoutConstructor();
        foreach (array('_parameters' => array(), '_instanceName' => $module . ':' . $class) as $key => $value)
        {
            (new ReflectionProperty(DataGrid::class, $key))->setValue($grid, $value);
        }
        $this->accessOverrides['export'] = $admin ? ACCESS_LEVEL_SA : ACCESS_LEVEL_EDIT;
        $html = $grid->getInnerActionArea();
        if ($admin) self::assertStringContainsString('exportByDataGrid', $html);
        else self::assertStringNotContainsString('exportByDataGrid', $html);
        self::assertStringContainsString(str_contains($class, 'SavedList') ? 'Remove From This List' : 'Add To List', $html);
    }
    #[DataProvider('menuRoles')]
    public function testSavedListsMenuUsesExportAcl($admin): void
    {
        require_once './modules/lists/dataGrids.php';
        $grid = (new ReflectionClass(ListsDataGrid::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty(DataGrid::class, '_parameters'))->setValue($grid, array());
        (new ReflectionProperty(DataGrid::class, '_instanceName'))->setValue($grid, 'lists:ListsDataGrid');
        $this->accessOverrides['export'] = $admin ? ACCESS_LEVEL_SA : ACCESS_LEVEL_EDIT;
        $html = $grid->getInnerActionArea();
        if ($admin) self::assertStringContainsString('Export Selected', $html);
        else self::assertStringNotContainsString('Export Selected', $html);
    }

}
