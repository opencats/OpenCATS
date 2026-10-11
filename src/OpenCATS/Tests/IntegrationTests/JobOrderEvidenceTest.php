<?php
namespace OpenCATS\Tests\IntegrationTests;

class JobOrderEvidenceTest extends DatabaseTestCase
{
    private $db;

    protected function setUp(): void
    {
        parent::setUp();
        require_once LEGACY_ROOT . '/lib/CATSUtility.php';
        require_once LEGACY_ROOT . '/lib/ExtraFields.php';
        require_once LEGACY_ROOT . '/lib/Contacts.php';
        require_once LEGACY_ROOT . '/lib/JobOrders.php';
        require_once LEGACY_ROOT . '/lib/Session.php';
        $session = $this->createStub(\CATSSession::class);
        $session->method('getUserID')->willReturn(1);
        $session->method('getTimeZoneOffset')->willReturn(0);
        $session->method('getAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        $session->method('getRealAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        $session->method('isLoggedIn')->willReturn(true);
        $_SESSION['CATS'] = $session;
        $this->db = \DatabaseConnection::getInstance();
    }

    protected function tearDown(): void
    {
        unset($_SESSION['CATS']);
        parent::tearDown();
    }

    private function addJob($speculative = 0)
    {
        return (new \JobOrders())->add('Evidence fixture', 1, -1, '', '', '', '', 'H', false, false, 1, '', '', 'London', '', '', 1, 1, 1, '', false, '', null, null, $speculative);
    }

    private function editJob($id, $status = 'Active', $speculative = null)
    {
        return (new \JobOrders())->update($id, 'Evidence fixture', '', 1, -1, '', '', '', '', 'H', false, 1, 1, '', 'London', '', '', $status, 1, 1, false, '', '', '', false, false, false, false, $speculative);
    }

    private function row($id)
    {
        return (new \JobOrders())->get($id);
    }

    public function testCreationConversionAndEvidencePreservation(): void
    {
        $before = $this->db->getAssoc('SELECT NOW() AS clock')['clock'];
        $genuine = $this->addJob();
        $stamp = $this->row($genuine)['firstActionableAt'];
        self::assertNotNull($stamp);
        self::assertGreaterThanOrEqual($before, $stamp);
        self::assertSame('0', (string) $this->row($genuine)['isSpeculative']);
        $spec = $this->addJob(1);
        self::assertNull($this->row($spec)['firstActionableAt']);
        self::assertTrue($this->editJob($spec));
        self::assertSame('1', (string) (new \JobOrders())->getForEditing($spec)['isSpeculative']);
        self::assertNull($this->row($spec)['firstActionableAt']);
        self::assertTrue($this->editJob($spec, 'Active', 0));
        self::assertNotNull($this->row($spec)['firstActionableAt']);
        // Use a distinct existing event time so a reset cannot pass within the same second.
        $stamp = '2026-01-02 03:04:05';
        $this->db->query("UPDATE joborder SET first_actionable_at = '$stamp' WHERE joborder_id = $genuine");
        foreach (array(array('Active', 0), array('On Hold', 0), array('Active', 0), array('Closed', 1), array('Active', 1), array('Active', 0)) as [$status, $flag])
        {
            self::assertTrue($this->editJob($genuine, $status, $flag));
            self::assertSame($stamp, $this->row($genuine)['firstActionableAt']);
        }
    }

    public function testDirectRepositoryCreationHonoursStatusAndClassification(): void
    {
        foreach (array('On Hold', 'Upcoming', 'Custom', 'Active') as $status)
        {
            $entity = new \OpenCATS\Entity\JobOrder('Direct creation', 'H', $status, 'London', '', 1, false);
            foreach (array('CompanyId', 'EnteredBy', 'Recruiter', 'Owner', 'Openings', 'AvailableOpenings') as $field)
                $entity->{'set' . $field}(1);
            foreach (array('CompanyJobId', 'Description', 'Notes', 'Duration', 'MaxRate', 'Salary', 'Country', 'StartDate') as $field)
                $entity->{'set' . $field}('');
            $entity->setContactId(-1);
            $entity->setDepartmemtId(0);
            $entity->setQuestionnaire(false);
            $entity->setDeskID(null);
            $id = (new \OpenCATS\Entity\JobOrderRepository($this->db))->persist($entity, new \History());
            self::assertSame($status, $this->row($id)['status']);
            self::assertSame('0', (string) $this->row($id)['isSpeculative']);
            if ($status === 'Active') self::assertNotNull($this->row($id)['firstActionableAt']);
            else self::assertNull($this->row($id)['firstActionableAt']);
        }
    }

    public function testLegacyAndNonActionableTransitions(): void
    {
        foreach (array('Active', 'On Hold', 'Closed', 'Upcoming', 'Full', 'Custom') as $status)
        {
            $id = $this->addJob();
            $this->db->query('UPDATE joborder SET first_actionable_at = NULL, status = ' . $this->db->makeQueryString($status) . ' WHERE joborder_id = ' . $id);
            self::assertTrue($this->editJob($id, $status, 0));
            self::assertNull($this->row($id)['firstActionableAt']);
            self::assertTrue($this->editJob($id, 'Active', 0));
            if ($status === 'Active') self::assertNull($this->row($id)['firstActionableAt']);
            else self::assertNotNull($this->row($id)['firstActionableAt']);
        }
        $id = $this->addJob(1);
        self::assertTrue($this->editJob($id, 'On Hold', 0));
        self::assertNull($this->row($id)['firstActionableAt']);
        self::assertTrue($this->editJob($id));
        self::assertNotNull($this->row($id)['firstActionableAt']);
    }

    public function testNormalAddEditHandlersRoundTripAndPreserveExtraFields(): void
    {
        require_once LEGACY_ROOT . '/lib/UserInterface.php';
        require_once LEGACY_ROOT . '/lib/Template.php';
        require_once LEGACY_ROOT . '/lib/DataGrid.php';
        require_once LEGACY_ROOT . '/modules/joborders/JobOrdersUI.php';
        $ui = (new \ReflectionClass(\JobOrdersUI::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(\UserInterface::class, '_template'))->setValue($ui, $this->createStub(\Template::class));
        (new \ReflectionProperty(\UserInterface::class, '_userID'))->setValue($ui, 1);
        // Native post-save hooks stop only the redirect, after model and ExtraFields writes.
        $_SESSION['hooks'] = array('JO_ON_ADD_POST' => array('return false;'), 'JO_ON_EDIT_POST' => array('return false;'));
        $_GET = array('a' => 'add');
        $_POST = array('postback' => 'postback', 'title' => 'UI Evidence', 'companyID' => '1',
            'contactID' => '-1', 'recruiter' => '1', 'owner' => '1', 'openings' => '1',
            'type' => 'H', 'city' => 'London', 'isSpeculative' => '1', 'extraField0' => 'Custom preserved');
        $this->db->query("INSERT INTO extra_field_settings (field_name, extra_field_type, data_item_type) VALUES ('Evidence custom field', 1, 400)");
        $ui->handleRequest();
        $id = (int) $this->db->getAssoc("SELECT joborder_id FROM joborder WHERE title = 'UI Evidence'")['joborder_id'];
        self::assertGreaterThan(0, $id);
        self::assertSame('1', (string) $this->row($id)['isSpeculative']);
        self::assertNull($this->row($id)['firstActionableAt']);
        $_GET['a'] = 'edit';
        $_POST['jobOrderID'] = (string) $id;
        $_POST['status'] = 'Active';
        $_POST['openingsAvailable'] = '1';
        $ui->handleRequest();
        self::assertSame('1', (string) $this->row($id)['isSpeculative']);
        self::assertNull($this->row($id)['firstActionableAt']);
        unset($_POST['isSpeculative']); // Browser unchecked checkbox.
        $ui->handleRequest();
        self::assertSame('0', (string) $this->row($id)['isSpeculative']);
        self::assertNotNull($this->row($id)['firstActionableAt']);
        self::assertSame('Custom preserved', $this->db->getAssoc('SELECT value FROM extra_field WHERE data_item_id = ' . $id . ' AND data_item_type = 400')['value']);
        unset($_SESSION['hooks']);
        $_GET = $_POST = array();
    }

    public function testInvalidValuesCannotWrite(): void
    {
        $id = $this->addJob(1);
        foreach (array(array(1), 'on', '', 2, 'false', new \stdClass()) as $invalid)
        {
            foreach (array(fn() => $this->addJob($invalid), fn() => $this->editJob($id, 'Active', $invalid)) as $write)
            {
                try { $write(); self::fail('Invalid classification accepted'); }
                catch (\InvalidArgumentException $e) { self::assertSame('Invalid speculative role value.', $e->getMessage()); }
            }
        }
        self::assertSame('1', (string) $this->row($id)['isSpeculative']);
        self::assertNull($this->row($id)['firstActionableAt']);
    }

    public function testFreshUpgradeAndPartialRetryPreserveEvidence(): void
    {
        require_once LEGACY_ROOT . '/modules/install/Schema.php';
        require_once LEGACY_ROOT . '/lib/ModuleUtility.php';
        $id = $this->addJob();
        $readColumns = fn() => $this->db->getAllAssoc("SHOW COLUMNS FROM joborder WHERE Field IN ('is_speculative', 'first_actionable_at')");
        $fresh = $readColumns();
        $dates = $this->db->getAssoc('SELECT date_created, date_modified FROM joborder WHERE joborder_id = ' . $id);
        $this->db->query('ALTER TABLE joborder DROP COLUMN is_speculative, DROP COLUMN first_actionable_at');
        $this->db->query("UPDATE module_schema SET version = 399 WHERE name = 'install'");
        global $maintPage;
        $maintPage = true;
        try { (new \ReflectionMethod(\ModuleUtility::class, 'processModuleSchema'))->invoke(null, 'install', array(400 => \CATSSchema::get()[400])); }
        finally { $maintPage = false; }
        self::assertSame($fresh, $readColumns());
        self::assertSame('400', (string) $this->db->getAssoc("SELECT version FROM module_schema WHERE name = 'install'")['version']);
        self::assertSame('0', (string) $this->row($id)['isSpeculative']);
        self::assertNull($this->row($id)['firstActionableAt']);
        self::assertSame($dates, $this->db->getAssoc('SELECT date_created, date_modified FROM joborder WHERE joborder_id = ' . $id));
        $db = $this->db;
        $this->db->query('ALTER TABLE joborder DROP COLUMN first_actionable_at');
        eval(substr(\CATSSchema::get()[400], 4));
        self::assertSame($fresh, $readColumns());
        $this->editJob($id, 'Closed');
        $this->editJob($id);
        $stamp = $this->row($id)['firstActionableAt'];
        self::assertNotNull($stamp);
        eval(substr(\CATSSchema::get()[400], 4));
        eval(substr(\CATSSchema::get()[400], 4));
        self::assertSame($stamp, $this->row($id)['firstActionableAt']);
    }

    public function testImportDraftAndSpeculativePipelineRemainUsable(): void
    {
        require_once LEGACY_ROOT . '/modules/import/Import.php';
        require_once LEGACY_ROOT . '/lib/Pipelines.php';
        $draft = (new \JobOrdersImport())->add(array('company' => 'Internal Postings', 'title' => 'Imported', 'openings' => 1), 1, 1);
        self::assertSame('Draft', $this->row($draft)['status']);
        self::assertNull($this->row($draft)['firstActionableAt']);
        self::assertTrue($this->editJob($draft));
        self::assertNotNull($this->row($draft)['firstActionableAt']);
        $id = $this->addJob(1);
        $pipeline = new \Pipelines();
        $pipeline->add(1, $id, 1);
        $pipeline->setStatus(1, $id, PIPELINE_STATUS_SUBMITTED, '', '');
        $pipeline->setStatus(1, $id, PIPELINE_STATUS_SUBMITTED, '', '');
        $history = $this->db->getAllAssoc('SELECT status_to, date FROM candidate_joborder_status_history WHERE joborder_id = ' . $id);
        self::assertCount(1, $history);
        self::assertSame((string) PIPELINE_STATUS_SUBMITTED, (string) $history[0]['status_to']);
        self::assertNotEmpty($history[0]['date']);
        self::assertNull($this->row($id)['firstActionableAt']);
        $this->db->query('UPDATE joborder SET owner = -1, recruiter = -1 WHERE joborder_id = ' . $id);
        self::assertCount(2, (new \JobOrders())->getUnassignedWorkWarnings());
    }
}
