<?php
namespace OpenCATS\Tests\IntegrationTests;

class DeskTest extends DatabaseTestCase
{
    private $db;
    private $access;

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
        $session->method('getColumnPreferences')->willReturn(array());
        $_SESSION['CATS'] = $session;
        $this->db = \DatabaseConnection::getInstance();
    }

    protected function tearDown(): void
    {
        unset($_SESSION['CATS']);
        parent::tearDown();
    }

    private function userDesk($id, $desk = false)
    {
        $users = new \Users();
        $u = $users->get($id);
        return $users->update($id, $u['lastName'], $u['firstName'], $u['email'], $u['username'], -1, false, $desk);
    }

    private function addJob($desk = false, $creator = 1, $recruiter = 1)
    {
        return (new \JobOrders())->add('Desk fixture', 1, -1, '', '', '', '', 'H', false, false, 1, '', '', 'London', '', '', $creator, $recruiter, 1, '', false, '', $desk);
    }

    private function updateJob($id, $desk = false, $recruiter = 1)
    {
        return (new \JobOrders())->update($id, 'Desk fixture', '', 1, -1, '', '', '', '', 'H', false, 1, 1, '', 'London', '', '', 'Active', $recruiter, 1, false, '', '', '', false, false, $desk);
    }

    public function testFreshDefaultsAndIndependentPersistedAssignments(): void
    {
        $desks = new \Desks();
        self::assertSame(array('Commercial', 'Engineering', 'Industrial'), array_column($desks->getAll(), 'name'));
        self::assertNull((new \Users())->get(1)['deskID']);
        $unassigned = $this->addJob();
        self::assertNull((new \JobOrders())->get($unassigned)['deskID']);
        self::assertTrue($this->updateJob($unassigned));
        self::assertNull((new \JobOrders())->get($unassigned)['deskID']);
        self::assertTrue($this->userDesk(1, 1));
        self::assertSame('1', (string) (new \Users())->get(1)['deskID']);
        $automatic = $this->addJob();
        $manual = $this->addJob(2);
        $cleared = $this->addJob(null);
        self::assertSame('1', (string) (new \JobOrders())->get($automatic)['deskID']);
        self::assertSame('2', (string) (new \JobOrders())->get($manual)['deskID']);
        self::assertNull((new \JobOrders())->get($cleared)['deskID']);
        self::assertTrue($this->userDesk(1, 3));
        self::assertTrue($this->userDesk(1)); // old positional callers preserve Desk
        self::assertSame('3', (string) (new \Users())->get(1)['deskID']);
        self::assertSame('1', (string) (new \JobOrders())->get($automatic)['deskID']);
        $other = (new \Users())->add('Recruiter', 'Other', '', 'desk-other', 'test-fixture', ACCESS_LEVEL_EDIT, false, 2);
        self::assertSame('2', (string) (new \Users())->get($other)['deskID']);
        self::assertTrue($this->updateJob($automatic, false, $other));
        $record = (new \JobOrders())->get($automatic);
        self::assertSame('1', (string) $record['deskID']);
        self::assertSame((string) $other, (string) $record['recruiter']);
        self::assertSame('1', (string) $record['owner']);
        $creatorDefault = $this->addJob(false, 1, $other);
        self::assertSame('3', (string) (new \JobOrders())->get($creatorDefault)['deskID']);
        self::assertTrue($this->updateJob($automatic, 2, $other));
        self::assertSame('2', (string) (new \JobOrders())->getForEditing($automatic)['deskID']);
        self::assertTrue($this->updateJob($automatic, null, $other));
        self::assertNull((new \JobOrders())->get($automatic)['deskID']);
        self::assertTrue($this->userDesk($other, null));
        self::assertNull((new \Users())->get($other)['deskID']);
        self::assertNotEmpty($this->db->getAllAssoc("SELECT history_id FROM history WHERE the_field = 'deskID'"));
    }

    public function testRenameRetirementAndEligibilityPreserveHistory(): void
    {
        $desks = new \Desks();
        $this->userDesk(1, 1);
        $id = $this->addJob();
        $desks->save(1, 'Commercial & Recruitment', false);
        $record = (new \JobOrders())->get($id);
        self::assertSame('Commercial & Recruitment', $record['deskName']);
        self::assertSame('1', (string) $record['deskID']);
        self::assertSame('Commercial & Recruitment', (new \Users())->get(1)['deskName']);
        self::assertCount(2, $desks->getAll());
        self::assertCount(3, $desks->getAll(false, 1));
        self::assertNull($desks->getDefaultForUser(1));
        self::assertTrue($this->updateJob($id, 1)); // retain current inactive Desk
        self::assertTrue($this->userDesk(1, 1));
        self::assertNull((new \JobOrders())->get($this->addJob())['deskID']);
        foreach (array(1, 999999, array(2), '2x', false) as $invalid)
        {
            try { $desks->validateAssignment($invalid); self::fail('Invalid new assignment accepted'); }
            catch (\InvalidArgumentException $e) { self::assertNotEmpty($e->getMessage()); }
        }
        $new = $desks->save(null, 'Technical', true);
        self::assertGreaterThan(3, $new);
        $desks->save(1, 'Commercial & Recruitment', true);
        self::assertSame(1, $desks->validateAssignment(1));
        $this->db->query('UPDATE user SET access_level = 0 WHERE user_id = 1');
        self::assertSame('Commercial & Recruitment', (new \Users())->get(1)['deskName']);
    }

    public function testInvalidWritesAreRejectedWithoutChangingRecords(): void
    {
        $job = $this->addJob(1);
        foreach (array(array(1), 999999, '2x', 0) as $value)
        {
            foreach (array(fn() => $this->userDesk(1, $value), fn() => $this->updateJob($job, $value), fn() => $this->addJob($value)) as $write)
            {
                try { $write(); self::fail('Invalid Desk was accepted'); }
                catch (\InvalidArgumentException $e) { self::assertNotEmpty($e->getMessage()); }
            }
        }
        self::assertSame('1', (string) (new \JobOrders())->get($job)['deskID']);
        self::assertNull((new \Users())->get(1)['deskID']);
    }

    public function testDataGridFilteringSortingAndVisibilityRemainIndependent(): void
    {
        require_once LEGACY_ROOT . '/modules/joborders/dataGrids.php';
        $a = $this->addJob(1);
        $b = $this->addJob(2);
        $this->addJob(null);
        foreach (array('Commercial', 'Industrial', 'Unassigned') as $name)
        {
            $grid = new \JobOrdersListByViewDataGrid(array('filter'=>'Desk=='.$name), 0);
            self::assertSame(1, (int) $grid->getNumberOfRows());
        }
        $grid = new \JobOrdersListByViewDataGrid(array('sortBy'=>'deskName','sortDirection'=>'ASC','rangeStart'=>1,'maxResults'=>1), 0);
        $rows = (new \ReflectionProperty(\DataGrid::class, '_rs'))->getValue($grid);
        self::assertCount(1, $rows);
        self::assertSame((string) $a, (string) $rows[0]['jobOrderID']);
        $this->db->query('UPDATE joborder SET is_admin_hidden = 1 WHERE joborder_id = ' . $b);
        $this->userDesk(1, 2);
        $this->access = ACCESS_LEVEL_READ;
        $grid = new \JobOrdersListByViewDataGrid(array('filter'=>'Desk==Industrial'), 0);
        self::assertSame(0, (int) $grid->getNumberOfRows());
        $grid = new \JobOrdersListByViewDataGrid(array(), 0);
        self::assertSame(2, (int) $grid->getNumberOfRows());
    }

    public function testUpgradeAndPartialRetryPreserveAssignmentsAndRenames(): void
    {
        require_once LEGACY_ROOT . '/modules/install/Schema.php';
        require_once LEGACY_ROOT . '/lib/ModuleUtility.php';
        $job = $this->addJob(null);
        $freshColumns = $this->db->getAllAssoc("SHOW FULL COLUMNS FROM joborder WHERE Field = 'desk_id'");
        $this->db->query('ALTER TABLE user DROP COLUMN desk_id');
        $this->db->query('ALTER TABLE joborder DROP COLUMN desk_id');
        $this->db->query('DROP TABLE desk');
        $this->db->query("UPDATE module_schema SET version = 396 WHERE name = 'install'");
        global $maintPage;
        $maintPage = true;
        try
        {
            (new \ReflectionMethod(\ModuleUtility::class, 'processModuleSchema'))->invoke(null, 'install', array(397 => \CATSSchema::get()[397]));
        }
        finally { $maintPage = false; }
        self::assertSame('397', (string) $this->db->getAssoc("SELECT version FROM module_schema WHERE name = 'install'")['version']);
        self::assertSame($freshColumns, $this->db->getAllAssoc("SHOW FULL COLUMNS FROM joborder WHERE Field = 'desk_id'"));
        self::assertNull((new \Users())->get(1)['deskID']);
        self::assertNull((new \JobOrders())->get($job)['deskID']);
        self::assertCount(3, (new \Desks())->getAll());
        $this->userDesk(1, 1);
        (new \Desks())->save(1, 'Renamed', false);
        $this->db->query('ALTER TABLE joborder DROP COLUMN desk_id');
        $db = $this->db;
        eval(substr(\CATSSchema::get()[397], 4));
        eval(substr(\CATSSchema::get()[397], 4));
        self::assertSame('1', (string) (new \Users())->get(1)['deskID']);
        self::assertSame('Renamed', (new \Desks())->get(1)['name']);
        self::assertSame('0', (string) (new \Desks())->get(1)['isActive']);
        self::assertNull((new \JobOrders())->get($job)['deskID']);
        self::assertCount(1, $this->db->getAllAssoc("SHOW INDEX FROM joborder WHERE Key_name = 'idx_joborder_desk'"));
    }
}
