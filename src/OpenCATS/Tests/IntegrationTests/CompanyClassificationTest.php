<?php
namespace OpenCATS\Tests\IntegrationTests;

use OpenCATS\Entity\Company;

class CompanyClassificationTest extends DatabaseTestCase
{
    private $db;
    private $savedColumns = array();

    protected function setUp(): void
    {
        parent::setUp();
        require_once LEGACY_ROOT . '/lib/CATSUtility.php';
        require_once LEGACY_ROOT . '/lib/ExtraFields.php';
        require_once LEGACY_ROOT . '/lib/Companies.php';
        require_once LEGACY_ROOT . '/lib/Session.php';
        $session = $this->createStub(\CATSSession::class);
        $session->method('getUserID')->willReturn(1);
        $session->method('getTimeZoneOffset')->willReturn(0);
        $session->method('getAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        $session->method('getRealAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        $session->method('getColumnPreferences')->willReturnCallback(fn() => $this->savedColumns);
        $session->method('setColumnPreferences')->willReturnCallback(function ($grid, $columns) {
            $this->savedColumns = $columns;
        });
        $_SESSION['CATS'] = $session;
        $this->db = \DatabaseConnection::getInstance();
    }

    protected function tearDown(): void
    {
        unset($_SESSION['CATS']);
        parent::tearDown();
    }

    private function addCompany($tier = null, $status = null)
    {
        return (new \Companies())->add('CRM fixture', '', '', '', '', '', '', '', '', '', '', true, '', 1, 1, '', $tier, $status);
    }

    private function updateCompany($id, ...$classification)
    {
        return (new \Companies())->update($id, 'CRM fixture', '', '', '', '', '', '', '', '', '', '', true, '', 1, -1, '', '', false, ...$classification);
    }

    public function testCreateUpdateClearAndLegacyCallersPreserveData(): void
    {
        $companies = new \Companies();
        foreach (array_merge(array(null), Company::getCommercialTiers()) as $tier)
        {
            foreach (array_merge(array(null), Company::getRelationshipStatuses()) as $status)
            {
                $id = $this->addCompany($tier, $status);
                $record = $companies->get($id);
                self::assertSame($tier, $record['commercialTier']);
                self::assertSame($status, $record['relationshipStatus']);
                self::assertTrue($this->updateCompany($id));
                self::assertSame($tier, $companies->getForEditing($id)['commercialTier']);
                self::assertSame($status, $companies->getForEditing($id)['relationshipStatus']);
                self::assertTrue($this->updateCompany($id, null, null));
                self::assertNull($companies->get($id)['commercialTier']);
                self::assertNull($companies->get($id)['relationshipStatus']);
                self::assertTrue($this->updateCompany($id, $tier, $status));
                $record = $companies->get($id);
                self::assertSame($tier, $record['commercialTier']);
                self::assertSame($status, $record['relationshipStatus']);
                self::assertSame('1', (string) $record['isHot']);
                self::assertSame('1', (string) $record['owner']);
            }
        }
        $id = $this->addCompany('B', 'Client');
        self::assertTrue($this->updateCompany($id, 'C'));
        self::assertSame('Client', $companies->get($id)['relationshipStatus']);
        self::assertFalse($this->updateCompany(array(1), 'A', 'Client'));
        self::assertNull($companies->get(1)['commercialTier']);
        self::assertSame('1', (string) $companies->get(1)['defaultCompany']);
        self::assertTrue($companies->setCompanyDefault($id));
        self::assertSame('C', $companies->get($id)['commercialTier']);
        self::assertSame('Client', $companies->get($id)['relationshipStatus']);
        self::assertNotEmpty($this->db->getAllAssoc("SELECT history_id FROM history WHERE data_item_id = $id AND the_field = 'defaultCompany'"));
        $history = $this->db->getAllAssoc("SELECT the_field FROM history WHERE the_field IN ('commercialTier','relationshipStatus')");
        self::assertNotEmpty($history);
        self::assertFalse($this->updateCompany(99999999, 'A', 'Client'));
    }

    public function testLabelChangesKeepCanonicalDataAndFilters(): void
    {
        $id = $this->addCompany('A', 'Fresh Prospect');
        $settings = new \CompanySettings();
        self::assertSame(array('A'=>'A','B'=>'B','C'=>'C','D'=>'D'), $settings->getAll());
        $settings->setAll(array('A'=>'<b>Priority</b>', 'B'=>'', 'C'=>'C', 'D'=>'Low'));
        self::assertSame('B', $settings->getAll()['B']);
        self::assertSame('A', (new \Companies())->get($id)['commercialTier']);
        $settings->setAll(array('A'=>'Strategic', 'B'=>'B', 'C'=>'C', 'D'=>'D'));
        self::assertSame('A', (new \Companies())->get($id)['commercialTier']);
        require_once LEGACY_ROOT . '/modules/companies/dataGrids.php';
        foreach (array('A'=>1, 'Unclassified'=>1) as $filter => $expected)
        {
            $grid = new \CompaniesListByViewDataGrid(array('filter'=>'Commercial Tier=='.$filter, 'sortBy'=>'commercialTier', 'sortDirection'=>'ASC'), 0);
            self::assertSame($expected, (int) $grid->getNumberOfRows());
        }
    }

    public function testPartialUpgradeRetryPreservesValuesAndSettings(): void
    {
        require_once LEGACY_ROOT . '/modules/install/Schema.php';
        $columns = $this->db->getAllAssoc("SHOW FULL COLUMNS FROM company WHERE Field IN ('commercial_tier','relationship_status')");
        $id = $this->addCompany('D', 'Lost');
        (new \CompanySettings())->setAll(array('A'=>'Priority', 'B'=>'B', 'C'=>'C', 'D'=>'D'));
        $this->db->query('ALTER TABLE company DROP COLUMN relationship_status');
        $db = $this->db;
        $code = substr(\CATSSchema::get()[396], 4);
        eval($code);
        eval($code);
        self::assertSame($columns, $this->db->getAllAssoc("SHOW FULL COLUMNS FROM company WHERE Field IN ('commercial_tier','relationship_status')"));
        self::assertSame('D', (new \Companies())->get($id)['commercialTier']);
        self::assertNull((new \Companies())->get($id)['relationshipStatus']);
        self::assertSame('Priority', (new \CompanySettings())->getAll()['A']);
        $this->db->query('ALTER TABLE company DROP COLUMN commercial_tier, DROP COLUMN relationship_status');
        eval($code);
        self::assertNull((new \Companies())->get($id)['commercialTier']);
    }
    public function testPagingSortingSavedLayoutAndSavedList(): void
    {
        require_once LEGACY_ROOT . '/modules/companies/dataGrids.php';
        $b = $this->addCompany('B', 'Client');
        $a = $this->addCompany('A', 'Fresh Prospect');
        $grid = new \CompaniesListByViewDataGrid(array('sortBy'=>'commercialTier', 'sortDirection'=>'ASC', 'rangeStart'=>1, 'maxResults'=>1), 0);
        self::assertSame(3, (int) $grid->getNumberOfRows());
        $rows = (new \ReflectionProperty(\DataGrid::class, '_rs'))->getValue($grid);
        self::assertCount(1, $rows);
        self::assertSame((string) $a, (string) $rows[0]['companyID']);
        self::assertSame('A', $rows[0]['commercialTier']);
        $grid = new \CompaniesListByViewDataGrid(array('filter'=>'Relationship Lifecycle==Client', 'sortBy'=>'relationshipStatus', 'sortDirection'=>'DESC'), 0);
        self::assertSame(1, (int) $grid->getNumberOfRows());
        $rows = (new \ReflectionProperty(\DataGrid::class, '_rs'))->getValue($grid);
        self::assertSame((string) $b, (string) $rows[0]['companyID']);
        $grid = new \CompaniesListByViewDataGrid(array('filter'=>'Relationship Lifecycle==Unclassified'), 0);
        self::assertSame(1, (int) $grid->getNumberOfRows());

        $this->savedColumns = array(array('name'=>'Name', 'width'=>255));
        $grid = new \CompaniesListByViewDataGrid(array(), 0);
        $columns = (new \ReflectionProperty(\DataGrid::class, '_currentColumns'))->getValue($grid);
        self::assertSame(array('Name'), array_column($columns, 'name'));
        self::assertSame(array(array('name'=>'Name', 'width'=>255)), $this->savedColumns);

        $this->db->query("INSERT INTO saved_list (description, data_item_type) VALUES ('CRM fixture list', 200)");
        $list = $this->db->getLastInsertID();
        $this->db->query("INSERT INTO saved_list_entry (saved_list_id, data_item_type, data_item_id) VALUES ($list, 200, $b)");
        $grid = new \companiesSavedListByViewDataGrid(array('filter'=>'Commercial Tier==B'), $list);
        self::assertSame(1, (int) $grid->getNumberOfRows());
    }

    public function testDemoDataRemainsUnclassified(): void
    {
        global $mySQLConnection;
        foreach (explode(";\n", file_get_contents(LEGACY_ROOT . '/db/cats_demo_data.sql')) as $sql)
        {
            if (trim($sql) !== '')
            {
                self::assertTrue(mysqli_query($mySQLConnection, $sql));
            }
        }
        $row = $this->db->getAssoc('SELECT COUNT(*) AS total FROM company WHERE commercial_tier IS NOT NULL OR relationship_status IS NOT NULL');
        self::assertSame(0, (int) $row['total']);
        require_once LEGACY_ROOT . '/modules/install/Schema.php';
        require_once LEGACY_ROOT . '/lib/ModuleUtility.php';
        $version = $this->db->getAssoc("SELECT version FROM module_schema WHERE name = 'install'");
        self::assertNull($version['version']);
        global $maintPage;
        $maintPage = true;
        try
        {
            (new \ReflectionMethod(\ModuleUtility::class, 'processModuleSchema'))->invoke(null, 'install', \CATSSchema::get());
        }
        finally
        {
            $maintPage = false;
        }
        $version = $this->db->getAssoc("SELECT version FROM module_schema WHERE name = 'install'");
        self::assertSame((int) max(array_keys(\CATSSchema::get())), (int) $version['version']);
        self::assertSame(array('A'=>'A','B'=>'B','C'=>'C','D'=>'D'), (new \CompanySettings())->getAll());
    }
}
