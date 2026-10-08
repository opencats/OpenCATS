<?php
namespace OpenCATS\Tests\IntegrationTests;

class SectorTest extends DatabaseTestCase
{
    private $db;
    private $access;
    private $savedColumns = array();

    protected function setUp(): void
    {
        parent::setUp();
        $this->access = ACCESS_LEVEL_SA;
        require_once LEGACY_ROOT . '/lib/CATSUtility.php';
        require_once LEGACY_ROOT . '/lib/ExtraFields.php';
        require_once LEGACY_ROOT . '/lib/Contacts.php';
        require_once LEGACY_ROOT . '/lib/Users.php';
        require_once LEGACY_ROOT . '/lib/JobOrders.php';
        require_once LEGACY_ROOT . '/lib/Session.php';
        $session = $this->createStub(\CATSSession::class);
        $session->method('getUserID')->willReturn(1);
        $session->method('getTimeZoneOffset')->willReturn(0);
        $session->method('getAccessLevel')->willReturnCallback(fn() => $this->access);
        $session->method('getRealAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        $session->method('isLoggedIn')->willReturn(true);
        $session->method('getColumnPreferences')->willReturnCallback(fn() => $this->savedColumns);
        $_SESSION['CATS'] = $session;
        $this->db = \DatabaseConnection::getInstance();
    }

    protected function tearDown(): void
    {
        unset($_SESSION['CATS']);
        parent::tearDown();
    }

    private function addJob($sector = null, $desk = false, $recruiter = 1)
    {
        return (new \JobOrders())->add('Sector fixture', 1, -1, '', '', '', '', 'H', false, false, 1, '', '', 'London', '', '', 1, $recruiter, 1, '', false, '', $desk, $sector);
    }

    private function updateJob($id, $sector = false)
    {
        return (new \JobOrders())->update($id, 'Sector fixture', '', 1, -1, '', '', '', '', 'H', false, 1, 1, '', 'London', '', '', 'Active', 1, 1, false, '', '', '', false, false, false, $sector);
    }

    public function testDefaultsPersistenceAndHistoricalReferences(): void
    {
        $sectors = new \Sectors();
        self::assertSame(array('Catering', 'Distribution and Logistics', 'Driving', 'Finance', 'Financial Services', 'Information Technology', 'Insurance', 'Sales', 'Technology'), array_column($sectors->getAll(), 'name'));
        $this->db->query('UPDATE user SET desk_id = 2 WHERE user_id = 1');
        $unclassified = $this->addJob();
        self::assertNull((new \JobOrders())->get($unclassified)['sectorID']);
        self::assertSame('2', (string) (new \JobOrders())->get($unclassified)['deskID']);
        $id = $this->addJob(1, 3);
        self::assertTrue($this->updateJob($id)); // omitted argument preserves assignment
        self::assertSame('1', (string) (new \JobOrders())->getForEditing($id)['sectorID']);
        $sectors->save(1, 'Renamed & <Sector> "label"', false);
        self::assertCount(8, $sectors->getAll());
        self::assertCount(9, $sectors->getAll(false, 1));
        self::assertTrue($this->updateJob($id, 1));
        $row = (new \JobOrders())->get($id);
        self::assertSame('Renamed & <Sector> "label"', $row['sectorName']);
        self::assertSame('3', (string) $row['deskID']);
        self::assertSame('1', (string) $row['recruiter']);
        self::assertSame('1', (string) $row['owner']);
        foreach (array(1, 999999, array(2), '2x', 0) as $invalid)
        {
            foreach (array(fn() => $this->addJob($invalid), fn() => $this->updateJob($unclassified, $invalid)) as $write)
            {
                try { $write(); self::fail('Invalid assignment accepted'); }
                catch (\InvalidArgumentException $e) { self::assertNotEmpty($e->getMessage()); }
            }
        }
        self::assertNull((new \JobOrders())->get($unclassified)['sectorID']);
        self::assertTrue($this->updateJob($id, 2));
        self::assertTrue($this->updateJob($id, null));
        self::assertNull((new \JobOrders())->get($id)['sectorID']);
        self::assertNotEmpty($this->db->getAllAssoc("SELECT history_id FROM history WHERE the_field = 'sectorID'"));
        self::assertGreaterThan(9, $sectors->save(null, 'New Sector', true));
        $sectors->save(1, 'Renamed & <Sector> "label"', true);
        self::assertSame(1, $sectors->validateAssignment(1));
        foreach (array(array(999999, 'Unknown'), array(1, 'Technology')) as [$key, $name])
        {
            try { $sectors->save($key, $name, true); self::fail('Invalid maintenance accepted'); }
            catch (\InvalidArgumentException $e) { self::assertNotEmpty($e->getMessage()); }
        }
    }

    public function testCombinedFiltersSortingEscapingAndVisibility(): void
    {
        require_once LEGACY_ROOT . '/modules/joborders/dataGrids.php';
        require_once LEGACY_ROOT . '/lib/Template.php';
        $a = $this->addJob(1, 1);
        $this->addJob(2, 1);
        $this->addJob(1, 2);
        $other = (new \Users())->add('Recruiter', 'Other', '', 'sector-other', 'test-fixture', ACCESS_LEVEL_EDIT, false, 1);
        $this->addJob(1, 1, $other);
        $this->addJob(null, null);
        $name = $this->db->getAssoc('SELECT CONCAT(first_name, last_name) AS name FROM user WHERE user_id = 1')['name'];
        foreach (array('Sector==Distribution+and+Logistics' => 3, 'Desk==Commercial' => 3,
            'Desk==Commercial,Sector==Distribution+and+Logistics' => 2,
            'Desk==Commercial,Sector==Distribution+and+Logistics,Recruiter==' . urlencode($name) => 1,
            'Sector==Unclassified' => 1) as $filter => $count)
        {
            $grid = new \JobOrdersListByViewDataGrid(array('filter' => $filter), 0);
            self::assertSame($count, (int) $grid->getNumberOfRows(), $filter);
        }
        $grid = new \JobOrdersListByViewDataGrid(array('sortBy' => 'sectorName', 'sortDirection' => 'DESC', 'maxResults' => 1, 'rangeStart' => 0), 0);
        $rows = (new \ReflectionProperty(\DataGrid::class, '_rs'))->getValue($grid);
        self::assertCount(1, $rows);
        self::assertSame('Information Technology', $rows[0]['sectorName']);
        (new \Sectors())->save(1, 'Sector & <script>alert("x")</script>', true);
        $grid = new \JobOrdersListByViewDataGrid(array(), 0);
        ob_start();
        try { $grid->drawHTML(); $html = ob_get_contents(); }
        finally { ob_end_clean(); }
        self::assertStringContainsString('Sector &amp; &lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', $html);
        self::assertStringNotContainsString('<script>alert("x")</script>', $html);
        self::assertStringContainsString('Unclassified', $html);
        $this->db->query('UPDATE joborder SET is_admin_hidden = 1 WHERE joborder_id = ' . $a);
        $this->access = ACCESS_LEVEL_READ;
        $grid = new \JobOrdersListByViewDataGrid(array('filter'=>'Desk==Commercial,Recruiter==' . urlencode($name)), 0);
        self::assertSame(1, (int) $grid->getNumberOfRows());
    }

    public function testSavedLayoutAndCustomFieldsSurviveSectorFiltering(): void
    {
        require_once LEGACY_ROOT . '/modules/joborders/dataGrids.php';
        $job = $this->addJob(1, 2);
        $this->db->query("INSERT INTO extra_field_settings (field_name, extra_field_type, data_item_type) VALUES ('Sector custom fixture', 1, 400)");
        $this->db->query("INSERT INTO extra_field (data_item_id, data_item_type, field_name, value) VALUES (" . $job . ", 400, 'Sector custom fixture', 'Custom value')");
        $this->savedColumns = array(array('name'=>'Title', 'width'=>170), array('name'=>'Sector custom fixture', 'width'=>100));
        $grid = new \JobOrdersListByViewDataGrid(array('filter'=>'Sector==Distribution+and+Logistics,Desk==Industrial'), 0);
        self::assertSame(1, (int) $grid->getNumberOfRows());
        $columns = (new \ReflectionProperty(\DataGrid::class, '_currentColumns'))->getValue($grid);
        self::assertSame(array_column($this->savedColumns, 'name'), array_column($columns, 'name'));
        self::assertSame(array_column($this->savedColumns, 'width'), array_column($columns, 'width'));
        ob_start();
        try { $grid->drawHTML(); $html = ob_get_contents(); }
        finally { ob_end_clean(); }
        self::assertStringContainsString('Custom value', $html);
    }

    public function testNativeBackupRestoresSectorDefinitionsAndNullableAssignments(): void
    {
        require_once LEGACY_ROOT . '/modules/install/backupDB.php';
        $classified = $this->addJob(1, 2);
        $unclassified = $this->addJob(null, null);
        (new \Sectors())->save(1, 'Historical Sector', false);
        $textValues = array(null, '', 'NULL', "O'Brien", "Both \"quotes\" and 'apostrophes'\nnext line\r\ntab\tbackslash\\end\0");
        $textJobs = array();
        foreach ($textValues as $value)
        {
            $jobID = $this->addJob();
            $this->db->query('UPDATE joborder SET notes = ' . ($value === null ? 'NULL' : $this->db->makeQueryString($value)) . ' WHERE joborder_id = ' . $jobID);
            $textJobs[$jobID] = $value;
        }
        $file = tempnam(sys_get_temp_dir(), 'sector-backup-');
        try
        {
            \dumpDB($this->db, $file);
            $dump = file_get_contents($file . '.0');
            self::assertStringContainsString('CREATE TABLE `sector`', $dump);
            foreach (explode('((ENDOFQUERY))', $dump) as $sql)
            {
                if (preg_match('/^(DROP TABLE IF EXISTS|CREATE TABLE|INSERT INTO) `(sector|joborder)`/', trim($sql)))
                {
                    self::assertNotFalse($this->db->query(trim($sql)));
                }
            }
            foreach ($textJobs as $jobID => $value)
            {
                self::assertSame($value, $this->db->getAssoc('SELECT notes FROM joborder WHERE joborder_id = ' . $jobID)['notes']);
            }
            self::assertSame('Historical Sector', (new \Sectors())->get(1)['name']);
            self::assertSame('0', (string) (new \Sectors())->get(1)['isActive']);
            self::assertSame('1', (string) (new \JobOrders())->get($classified)['sectorID']);
            self::assertSame('2', (string) (new \JobOrders())->get($classified)['deskID']);
            self::assertNull((new \JobOrders())->get($unclassified)['sectorID']);
            self::assertNull((new \JobOrders())->get($unclassified)['deskID']);
        }
        finally
        {
            foreach (glob($file . '*') as $part) unlink($part);
        }
    }

    public function testFreshSchemaUpgradeAndInterruptedRecovery(): void
    {
        require_once LEGACY_ROOT . '/modules/install/Schema.php';
        require_once LEGACY_ROOT . '/lib/ModuleUtility.php';
        $jobID = $this->addJob(null, 2);
        $schema = function () {
            return array($this->db->getAllAssoc('SHOW FULL COLUMNS FROM sector'),
                $this->db->getAllAssoc("SHOW FULL COLUMNS FROM joborder"),
                $this->db->getAllAssoc("SELECT INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME, INDEX_TYPE FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('sector', 'joborder') AND COLUMN_NAME IN ('desk_id', 'sector_id') ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX"));
        };
        $fresh = $schema();
        $this->db->query('ALTER TABLE joborder DROP COLUMN sector_id');
        $this->db->query('DROP TABLE sector');
        $this->db->query("UPDATE module_schema SET version = 397 WHERE name = 'install'");
        global $maintPage;
        $maintPage = true;
        try { (new \ReflectionMethod(\ModuleUtility::class, 'processModuleSchema'))->invoke(null, 'install', array(398 => \CATSSchema::get()[398])); }
        finally { $maintPage = false; }
        self::assertSame('398', (string) $this->db->getAssoc("SELECT version FROM module_schema WHERE name = 'install'")['version']);
        self::assertSame($fresh, $schema());
        self::assertNull((new \JobOrders())->get($jobID)['sectorID']);
        self::assertSame('2', (string) (new \JobOrders())->get($jobID)['deskID']);
        $this->updateJob($jobID, 1);
        (new \Sectors())->save(1, 'Renamed', false);
        // Simulate interruption after column creation, before index creation.
        $this->db->query('ALTER TABLE joborder DROP INDEX idx_joborder_sector');
        $db = $this->db;
        eval(substr(\CATSSchema::get()[398], 4));
        eval(substr(\CATSSchema::get()[398], 4));
        self::assertSame($fresh, $schema());
        self::assertCount(9, (new \Sectors())->getAll(true));
        self::assertSame('Renamed', (new \Sectors())->get(1)['name']);
        self::assertSame('0', (string) (new \Sectors())->get(1)['isActive']);
        self::assertSame('1', (string) (new \JobOrders())->get($jobID)['sectorID']);
        self::assertSame('2', (string) (new \JobOrders())->get($jobID)['deskID']);
        // Simulate interruption during seeding; existing IDs must survive.
        $this->db->query('DELETE FROM sector WHERE sector_id > 5');
        eval(substr(\CATSSchema::get()[398], 4));
        self::assertCount(9, (new \Sectors())->getAll(true));
        self::assertSame('Renamed', (new \Sectors())->get(1)['name']);
    }
}
