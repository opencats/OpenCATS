<?php
namespace OpenCATS\Tests\IntegrationTests;

class ContactTransferTest extends DatabaseTestCase
{
    private $db;
    private $contact;
    private $companyA;
    private $companyB;

    protected function setUp(): void
    {
        parent::setUp();
        require_once LEGACY_ROOT . '/lib/CATSUtility.php';
        require_once LEGACY_ROOT . '/lib/Session.php';
        require_once LEGACY_ROOT . '/lib/Contacts.php';
        require_once LEGACY_ROOT . '/lib/Companies.php';
        require_once LEGACY_ROOT . '/lib/ActivityEntries.php';
        require_once LEGACY_ROOT . '/lib/Template.php';
        require_once LEGACY_ROOT . '/lib/TemplateUtility.php';
        $session = $this->createStub(\CATSSession::class);
        $session->method('getUserID')->willReturn(1);
        $session->method('getAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        $session->method('getRealAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        $session->method('isLoggedIn')->willReturn(true);
        $session->method('getTimeZoneOffset')->willReturn(0);
        $session->method('isDateDMY')->willReturn(false);
        $session->method('isTimeFormat24')->willReturn(false);
        $_SESSION['CATS'] = $session;
        $this->db = \DatabaseConnection::getInstance();
        foreach (array('companyA' => 'Company A & Partners', 'companyB' => 'Company B <script>test</script>') as $field => $name)
        {
            $this->db->query('INSERT INTO company (name, owner, commercial_tier, relationship_status, date_created) VALUES (' .
                $this->db->makeQueryString($name) . ", 1, 'A', 'Client', '2020-01-01')");
            $this->$field = (int) $this->db->getLastInsertID();
        }
        $this->contact = (new \Contacts())->add($this->companyA, 'Sarah', 'Smith', '', '', -1,
            '', '', '', '', '', '', '', '', '', '', false, '', 1, 1);
    }

    protected function tearDown(): void
    {
        unset($_SESSION['CATS']);
        (new \ReflectionProperty(\DatabaseConnection::class, '_instance'))->setValue(null, null);
        parent::tearDown();
    }

    private function updateContact($companyID, $phone = '', $id = null)
    {
        return (new \Contacts())->update($id ?? $this->contact, $companyID, 'Sarah', 'Smith', '', '', -1,
            '', '', $phone, '', '', '', '', '', '', '', false, false, '', 1, '', '');
    }

    private function activities()
    {
        return (new \ActivityEntries())->getAllByDataItem($this->contact, DATA_ITEM_CONTACT);
    }

    public function testTransferIsOneOrdinaryActivityWithSnapshotNamesAndExistingHistory(): void
    {
        self::assertSame(array(), $this->activities(), 'Creation is not a transfer.');
        $activity = new \ActivityEntries();
        $existing = $activity->add($this->contact, DATA_ITEM_CONTACT, ACTIVITY_EMAIL, 'Existing history', 1);
        self::assertTrue($this->updateContact($this->companyB));
        $rows = $this->activities();
        self::assertCount(2, $rows);
        self::assertSame('Contact transferred from "Company A & Partners" to "Company B <script>test</script>".', $rows[0]['notes']);
        self::assertSame((string) ACTIVITY_OTHER, (string) $rows[0]['type']);
        self::assertSame((string) $this->contact, (string) $rows[0]['dataItemID']);
        self::assertSame((string) $existing, (string) $rows[1]['activityID']);
        self::assertSame((string) $this->companyB, (string) (new \Contacts())->get($this->contact)['companyID']);
        $html = \TemplateUtility::highlightStatusChangeActivityNote($rows[0]['notes']);
        self::assertStringNotContainsString('<script>', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
        self::assertTrue($this->updateContact((string) $this->companyB));
        self::assertTrue($this->updateContact($this->companyB, '01234 567890'));
        self::assertCount(2, $this->activities(), 'Same Company/repeated/unrelated edits add no Activity.');
        $history = $this->db->getAllAssoc("SELECT previous_value, new_value FROM history WHERE data_item_type = " . DATA_ITEM_CONTACT .
            " AND data_item_id = " . $this->contact . " AND the_field = 'companyID'");
        self::assertCount(1, $history);
        self::assertSame((string) $this->companyA, $history[0]['previous_value']);
        self::assertSame((string) $this->companyB, $history[0]['new_value']);
        $this->db->query("UPDATE company SET name = 'Renamed' WHERE company_id IN ($this->companyA, $this->companyB)");
        self::assertSame($rows[0]['notes'], $this->activities()[0]['notes']);
        $new = $activity->add($this->contact, DATA_ITEM_CONTACT, ACTIVITY_CALL_TALKED, 'Normal new call', 1);
        self::assertCount(3, $this->activities());
        self::assertSame((string) $new, (string) $this->activities()[0]['activityID']);
        self::assertCount(3, $activity->getAllByCompany($this->companyB), 'Existing current-employer query remains unchanged.');
        self::assertSame(array(), $activity->getAllByCompany($this->companyA));
    }

    public function testRemovalAssignmentAndReturnUseMeaningfulMessages(): void
    {
        self::assertTrue($this->updateContact(-1));
        self::assertSame('Contact removed from "Company A & Partners" (now Unassigned).', $this->activities()[0]['notes']);
        self::assertTrue($this->updateContact(null));
        self::assertTrue($this->updateContact('0'));
        self::assertCount(1, $this->activities());
        self::assertTrue($this->updateContact($this->companyB));
        self::assertSame('Contact assigned to "Company B <script>test</script>".', $this->activities()[0]['notes']);
        self::assertTrue($this->updateContact($this->companyA));
        self::assertCount(3, $this->activities());
        self::assertSame('Contact transferred from "Company B <script>test</script>" to "Company A & Partners".', $this->activities()[0]['notes']);
    }

    public function testInvalidReferencesAndWriteFailuresLeaveNoMisleadingActivity(): void
    {
        foreach (array(array(1), 'invalid', 99999999, '-2') as $company)
            self::assertFalse($this->updateContact($company));
        self::assertFalse($this->updateContact($this->companyB, '', 99999999));
        self::assertSame(array(), $this->activities());
        $historyBefore = $this->db->getAllAssoc('SELECT * FROM history ORDER BY history_id');
        foreach (array('contact' => 'UPDATE', 'activity' => 'INSERT') as $table => $event)
        {
            $this->db->query("CREATE TRIGGER transfer_failure BEFORE $event ON $table FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'transfer fixture failure'");
            try
            {
                try { $this->updateContact($this->companyB); self::fail('Write failure was not propagated.'); }
                catch (\mysqli_sql_exception $e) { self::assertStringContainsString('transfer fixture failure', $e->getMessage()); }
                self::assertSame((string) $this->companyA, (string) (new \Contacts())->get($this->contact)['companyID']);
                self::assertSame(array(), $this->activities());
                self::assertSame($historyBefore, $this->db->getAllAssoc('SELECT * FROM history ORDER BY history_id'));
            }
            finally { $this->db->query('DROP TRIGGER transfer_failure'); }
        }
        // Native transaction helpers do not nest. Do not commit/roll back a caller's work.
        self::assertTrue($this->db->beginTransaction());
        self::assertFalse($this->updateContact($this->companyB));
        self::assertTrue($this->db->rollbackTransaction());
        self::assertSame(array(), $this->activities());
    }

    public function testTransferDoesNotChangeConservativeRelationshipEvidenceOrCreateTasks(): void
    {
        $this->db->query('INSERT INTO activity (data_item_type, data_item_id, type, date_occurred) VALUES (' . DATA_ITEM_COMPANY .
            ', ' . $this->companyA . ', ' . ACTIVITY_CALL_TALKED . ', DATE_SUB(NOW(), INTERVAL 30 DAY))');
        (new \ActivityEntries())->add($this->contact, DATA_ITEM_CONTACT, ACTIVITY_MEETING, 'Old Contact meeting', 1);
        $companies = new \Companies();
        $before = array_column($companies->getRelationshipAttention(), null, 'companyID');
        self::assertTrue($this->updateContact($this->companyB));
        $after = array_column($companies->getRelationshipAttention(), null, 'companyID');
        self::assertSame($before[$this->companyA]['lastContact'], $after[$this->companyA]['lastContact']);
        self::assertNull($after[$this->companyB]['lastContact']);
        self::assertSame($before[$this->companyB]['contactEvidenceScope'], $after[$this->companyB]['contactEvidenceScope']);
        self::assertSame(array(), $this->db->getAllAssoc('SELECT * FROM task'));
        self::assertSame((string) ACTIVITY_OTHER, (string) $this->activities()[0]['type']);
    }
}
