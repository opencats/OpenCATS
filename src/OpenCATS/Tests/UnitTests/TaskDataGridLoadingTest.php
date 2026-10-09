<?php
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class TaskDataGridLoadingTest extends AuthorizationTestCase
{
    public function testDefaultDataGridLoaderPreservesSqlRowsAndCount(): void
    {
        require_once './lib/DataGrid.php';
        $seen = array();
        $this->db->method('getAllAssoc')->willReturnCallback(function ($sql) use (&$seen) {
            $seen[] = $sql;
            return array(array('id' => 7));
        });
        $this->db->method('getAssoc')->willReturnCallback(function ($sql) use (&$seen) {
            $seen[] = $sql;
            return array('rowCount' => 32);
        });
        $grid = (new ReflectionClass(DataGrid::class))->newInstanceWithoutConstructor();
        $result = (new ReflectionMethod(DataGrid::class, 'loadRows'))->invoke($grid, 'SELECT SQL_CALC_FOUND_ROWS id FROM sample LIMIT 1');
        self::assertSame(array(array(array('id' => 7)), 32), $result);
        self::assertSame(array('SELECT SQL_CALC_FOUND_ROWS id FROM sample LIMIT 1', 'SELECT FOUND_ROWS() as rowCount'), $seen);
    }

    public function testTaskGridLoadsAuthorisedCollectionOnlyOnceIncludingPageCorrection(): void
    {
        require_once './modules/tasks/dataGrids.php';
        $this->loggedIn = true;
        $this->session->method('getColumnPreferences')->willReturn(array());
        $reads = 0;
        $this->db->method('getAllAssoc')->willReturnCallback(function ($sql) use (&$reads) {
            if (str_contains($sql, 'FROM task'))
            {
                ++$reads;
                return array(array('taskID' => '7', 'title' => 'Task', 'description' => '', 'dueDate' => null,
                    'priority' => 'normal', 'status' => 'open', 'purpose' => 'general', 'assignedTo' => '10',
                    'createdBy' => '10', 'dataItemType' => null, 'dataItemID' => null, 'candidateJobOrderID' => null));
            }
            return array();
        });
        $grid = new TasksDataGrid(array('rangeStart' => 999, 'maxResults' => 15), 0);
        self::assertSame(1, $grid->getNumberOfRows());
        self::assertSame(1, $grid->getCurrentPage());
        self::assertSame(1, $grid->getNumberOfRows());
        self::assertSame(1, $reads);
    }
    public function testTaskGridReusesAuthorisedParentLabelsWithinTheRequest(): void
    {
        require_once './modules/tasks/dataGrids.php';
        $this->loggedIn = true;
        $this->session->method('getColumnPreferences')->willReturn(array());
        $parentReads = 0;
        $this->db->method('getAssoc')->willReturnCallback(function ($sql) use (&$parentReads) {
            ++$parentReads;
            return array('companyID' => '9', 'name' => 'Shared company');
        });
        $this->db->method('getAllAssoc')->willReturnCallback(function ($sql) {
            if (!str_contains($sql, 'FROM task')) return array();
            $rows = array();
            foreach (range(1, 3) as $id) $rows[] = array('taskID' => (string) $id, 'title' => 'Task',
                'dueDate' => null, 'priority' => 'normal', 'status' => 'open', 'purpose' => 'general',
                'assignedTo' => '10', 'createdBy' => '10', 'dataItemType' => DATA_ITEM_COMPANY,
                'dataItemID' => '9', 'candidateJobOrderID' => null);
            return $rows;
        });
        $grid = new TasksDataGrid(array('rangeStart' => 999, 'maxResults' => 15), 0);
        self::assertSame(3, $grid->getNumberOfRows());
        $rows = (new ReflectionProperty(DataGrid::class, '_rs'))->getValue($grid);
        foreach ($rows as $row) self::assertStringContainsString('Shared company', TasksDataGrid::renderCell($row, 'dataItemID'));
        // Three existing canRead checks, then one context's ACL read and label read.
        self::assertSame(5, $parentReads);
        self::assertSame(3, $grid->getNumberOfRows());
        self::assertSame(5, $parentReads);
    }

}
