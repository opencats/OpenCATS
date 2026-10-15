<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class JobOrderPipelineAuthorizationTest extends AuthorizationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once './lib/DataGrid.php';
        require_once './modules/joborders/JobOrdersUI.php';
        $_GET['a'] = 'addToPipeline';
        $_POST = array('postback' => 'postback', 'jobOrderID' => '7', 'candidateID' => '42');
    }

    public static function candidateAccess(): array
    {
        return array(array(false, false, true), array(true, false, false), array(true, true, true));
    }

    #[DataProvider('candidateAccess')]
    public function testAddingCandidateUsesExistingVisibilityRule($hidden, $admin, $allowed): void
    {
        $this->accessLevel = $admin ? ACCESS_LEVEL_SA : ACCESS_LEVEL_EDIT;
        $this->db->method('getAssoc')->willReturnCallback(function ($sql) use ($hidden) {
            if (str_contains($sql, 'candidateIDCount')) return array('candidateIDCount' => 0);
            return array('candidateID' => 42, 'isAdminHidden' => $hidden ? '1' : '0');
        });
        $ui = $this->ui(JobOrdersUI::class);
        if (!$allowed)
        {
            $this->assertCommonError(COMMONERROR_PERMISSION, $ui->handleRequest(...));
            self::assertSame(array(), $this->queries);
            return;
        }
        $ui->handleRequest();
        self::assertCount(1, array_filter($this->queries, fn($sql) => str_contains($sql, 'INSERT INTO candidate_joborder')));
        self::assertStringContainsString('INSERT INTO candidate_joborder', $this->queries[0]);
        self::assertStringContainsString('INSERT INTO activity', $this->queries[1]);
    }

    public function testMissingCandidateCannotBeAdded(): void
    {
        $this->db->method('getAssoc')->willReturn(array());
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $this->ui(JobOrdersUI::class)->handleRequest());
        self::assertSame(array(), $this->queries);
    }

    public static function readerAccess(): array
    {
        return array(array(false), array(true));
    }

    #[DataProvider('readerAccess')]
    public function testPipelineFiltersHiddenCandidatesBeforePagination($admin): void
    {
        $this->accessOverrides['candidates.hidden'] = $admin ? ACCESS_LEVEL_SA : ACCESS_LEVEL_EDIT;
        $rows = array(
            array('candidateID' => 41, 'isAdminHidden' => '0', 'firstName' => 'Visible'),
            array('candidateID' => 42, 'isAdminHidden' => '1', 'firstName' => 'Hidden', 'lastActivity' => 'Private notes'),
            array('candidateID' => 43, 'isAdminHidden' => '0', 'firstName' => 'Also visible')
        );
        $this->db->method('getAllAssoc')->willReturnCallback(function ($sql) use ($rows) {
            self::assertStringContainsString('candidate.is_admin_hidden AS isAdminHidden', $sql);
            return $rows;
        });
        $actual = (new Pipelines())->getJobOrderPipeline(7);
        self::assertSame($admin ? $rows : array($rows[0], $rows[2]), $actual);
        self::assertTrue(array_is_list($actual));
    }
}
