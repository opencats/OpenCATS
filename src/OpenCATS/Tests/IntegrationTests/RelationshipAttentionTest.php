<?php
namespace OpenCATS\Tests\IntegrationTests;

class RelationshipAttentionTest extends DatabaseTestCase
{
    private $db;
    private $access = 400;
    private $overrides = array();
    private $loggedIn = true;
    private $today;

    protected function setUp(): void
    {
        parent::setUp();
        require_once LEGACY_ROOT . '/lib/CATSUtility.php';
        require_once LEGACY_ROOT . '/lib/Session.php';
        require_once LEGACY_ROOT . '/lib/Tasks.php';
        require_once LEGACY_ROOT . '/lib/ActivityEntries.php';
        $session = $this->createStub(\CATSSession::class);
        $session->method('getUserID')->willReturn(1);
        $session->method('getAccessLevel')->willReturnCallback(fn($key) => $this->overrides[$key] ?? $this->access);
        $session->method('getRealAccessLevel')->willReturnCallback(fn() => $this->access);
        $session->method('isLoggedIn')->willReturnCallback(fn() => $this->loggedIn);
        $session->method('getTimeZoneOffset')->willReturn(0);
        $session->method('isDateDMY')->willReturn(false);
        $session->method('isTimeFormat24')->willReturn(false);
        $_SESSION['CATS'] = $session;
        $this->db = \DatabaseConnection::getInstance();
        $this->today = \DateUtility::getAdjustedDate('Y-m-d');
    }

    protected function tearDown(): void
    {
        unset($_SESSION['CATS']);
        (new \ReflectionProperty(\DatabaseConnection::class, '_instance'))->setValue(null, null);
        parent::tearDown();
    }

    private function company($tier = 'A', $status = 'Client', $owner = 1)
    {
        $this->db->query('INSERT INTO company (name, commercial_tier, relationship_status, owner, date_created) VALUES (\'Attention fixture\', ' .
            $this->db->makeQueryStringOrNULL($tier) . ', ' . $this->db->makeQueryStringOrNULL($status) . ", $owner, '2020-01-01')");
        return (int) $this->db->getLastInsertID();
    }

    private function contact($company, $left = 0)
    {
        $this->db->query("INSERT INTO contact (company_id, company_department_id, first_name, last_name, left_company) VALUES ($company, -1, 'Attention', 'Contact', $left)");
        return (int) $this->db->getLastInsertID();
    }

    private function task($company, $days, $extra = array())
    {
        return (new \Tasks())->add(array_replace(array('title' => 'Follow up', 'dataItemType' => DATA_ITEM_COMPANY,
            'dataItemID' => $company, 'assignedTo' => 1, 'purpose' => 'relationship_follow_up',
            'dueDate' => $days === null ? null : $this->date($days)), $extra));
    }

    private function date($days)
    {
        return (new \DateTimeImmutable($this->today))->modify(sprintf('%+d days', $days))->format('Y-m-d');
    }

    private function activity($id, $type, $days, $parentType = DATA_ITEM_COMPANY)
    {
        // Seed existing explicitly attributed records; native Company Activity add has
        // an unrelated missing updateModified() path and is not a supported UI flow.
        $this->db->query("INSERT INTO activity (data_item_id, data_item_type, type, entered_by, date_occurred) VALUES ($id, $parentType, $type, 1, " .
            $this->db->makeQueryString($this->date($days) . ' 00:00:00') . ')');
        return (int) $this->db->getLastInsertID();
    }

    private function rows()
    {
        return array_column((new \Companies())->getRelationshipAttention(), null, 'companyID');
    }

    public function testEligibilitySettingsValidationAndImmediateRecalculation(): void
    {
        $settings = new \CompanySettings();
        $ids = array();
        foreach (array('A', 'B', 'C', 'D', null) as $tier)
            foreach (array('Fresh Prospect', 'Prospect', 'Engaged', 'Client', 'Dormant', 'Lost', null) as $status)
                $ids[] = $this->company($tier, $status);
        self::assertCount(12, $this->rows());
        self::assertSame(14, $this->rows()[$ids[0]]['reviewDays']);
        $policy = $settings->getAttention();
        $policy['tiers']['D'] = array('enabled' => true, 'days' => 90);
        $policy['lifecycles']['Dormant'] = true;
        $policy['tiers']['A']['days'] = 7;
        $settings->setAttention($policy);
        self::assertCount(20, $this->rows());
        self::assertSame(7, $this->rows()[$ids[0]]['reviewDays']);
        $settings->setAll(array('A' => '<Priority>', 'B' => 'B', 'C' => 'C', 'D' => 'D'));
        self::assertSame('A', $this->rows()[$ids[0]]['commercialTier']);
        self::assertSame('A — <Priority>', $this->rows()[$ids[0]]['tierLabel']);
        foreach (array(null, array(), array('tiers' => array(), 'lifecycles' => array())) as $bad)
        {
            try { $settings->setAttention($bad); self::fail('Invalid configuration accepted'); }
            catch (\InvalidArgumentException $e) { self::assertSame($policy, $settings->getAttention()); }
        }
        foreach (array(0, -1, '1.5', array(14), '36501', true) as $days)
        {
            $bad = $policy;
            $bad['tiers']['A']['days'] = $days;
            try { $settings->setAttention($bad); self::fail('Invalid interval accepted'); }
            catch (\InvalidArgumentException $e) { self::assertSame($policy, $settings->getAttention()); }
        }
        $bad = $policy;
        $bad['lifecycles']['Client'] = array('1');
        try { $settings->setAttention($bad); self::fail('Array flag accepted'); }
        catch (\InvalidArgumentException $e) { self::assertSame($policy, $settings->getAttention()); }
        self::assertSame('Fresh Prospect', (new \Companies())->get($ids[0])['relationshipStatus']);
    }

    public function testTaskCoverageBoundariesContactsAndExcludedAccounts(): void
    {
        $company = $this->company();
        $tasks = new \Tasks();
        $this->task($company, null);
        $this->task($company, 15);
        $this->task($company, 1, array('purpose' => 'general'));
        $this->task($company, 1, array('purpose' => 'administrative'));
        self::assertSame(array('no_next_action', 'stale_relationship'), $this->rows()[$company]['reasons']);
        $contact = $this->contact($company);
        $left = $this->contact($company, 1);
        $this->task($left, 0, array('dataItemType' => DATA_ITEM_CONTACT));
        $id = $this->task($contact, 14, array('dataItemType' => DATA_ITEM_CONTACT, 'status' => 'in_progress'));
        self::assertSame(array('stale_relationship', 'upcoming'), $this->rows()[$company]['reasons']);
        self::assertCount(1, $this->rows()[$company]['tasks']);
        $tasks->update($id, array('dueDate' => $this->date(-2)));
        self::assertSame(array('overdue_task', 'stale_relationship'), $this->rows()[$company]['reasons']);
        $tasks->update($id, array('status' => 'completed'));
        self::assertSame('no_next_action', $this->rows()[$company]['primaryReason']);
        $dormant = $this->company('A', 'Dormant');
        $commitment = $this->task($dormant, -1);
        self::assertArrayNotHasKey($dormant, $this->rows());
        self::assertContains((string) $commitment, array_map('strval', array_column($tasks->getAll(array('assignedTo' => 1, 'openOnly' => true)), 'taskID')));
    }

    public function testInteractionEvidenceAttributionAndStaleness(): void
    {
        $company = $this->company();
        $contact = $this->contact($company);
        $this->activity($contact, ACTIVITY_CALL_TALKED, -1, DATA_ITEM_CONTACT);
        foreach (array(ACTIVITY_CALL, ACTIVITY_EMAIL, ACTIVITY_OTHER, ACTIVITY_CALL_LVM, ACTIVITY_CALL_MISSED, ACTIVITY_STATUS_CHANGE) as $type)
            $this->activity($company, $type, -1);
        self::assertNull($this->rows()[$company]['lastContact']);
        self::assertSame('No recorded contact', $this->rows()[$company]['contactLabel']);
        $this->activity($company, ACTIVITY_CALL_TALKED, -15);
        $task = $this->task($company, 2);
        self::assertSame(array('stale_relationship', 'upcoming'), $this->rows()[$company]['reasons']);
        self::assertSame(15, $this->rows()[$company]['contactAgeDays']);
        $this->activity($company, ACTIVITY_MEETING, -14);
        self::assertSame(array('upcoming'), $this->rows()[$company]['reasons']);
        $this->activity($company, ACTIVITY_CALL_TALKED, 1);
        self::assertSame(14, $this->rows()[$company]['contactAgeDays']);
        (new \Tasks())->update($task, array('status' => 'completed'));
        self::assertSame(14, $this->rows()[$company]['contactAgeDays']);
        $other = $this->company();
        $this->db->query("UPDATE contact SET company_id = $other WHERE contact_id = $contact");
        self::assertNull($this->rows()[$other]['lastContact']);
        $policy = (new \CompanySettings())->getAttention();
        $policy['tiers']['A']['days'] = 13;
        (new \CompanySettings())->setAttention($policy);
        self::assertSame(array('no_next_action', 'stale_relationship'), $this->rows()[$company]['reasons']);
    }

    public function testReasonTierDateAndStableIDOrderingWithoutDuplicates(): void
    {
        $b = $this->company('B'); $a = $this->company(); $aEarlier = $this->company();
        $aTie = $this->company(); $today = $this->company(); $missing = $this->company();
        $stale = $this->company(); $upcoming = $this->company();
        $this->task($b, -20); $this->task($a, -1); $this->task($aEarlier, -3); $this->task($aTie, -3);
        $this->task($a, 0); $this->task($a, 1); $this->contact($a); $this->contact($a);
        $this->task($today, 0); $this->task($stale, 1); $this->task($upcoming, 1);
        $this->activity($upcoming, ACTIVITY_MEETING, -1);
        $rows = (new \Companies())->getRelationshipAttention();
        self::assertSame(array($aEarlier, $aTie, $a, $b, $today, $missing, $stale, $upcoming), array_map('intval', array_column($rows, 'companyID')));
        self::assertSame(array('overdue_task', 'due_today', 'stale_relationship', 'upcoming'), $this->rows()[$a]['reasons']);
    }

    public function testOwnershipPermissionsAndSettingsSecurity(): void
    {
        $own = $this->company(); $this->company('A', 'Client', -1);
        $this->db->query("INSERT INTO user (user_name, access_level) VALUES ('disabled-attention', 0)");
        $disabled = (int) $this->db->getLastInsertID();
        $this->company('A', 'Client', $disabled);
        self::assertSame(array($own), array_map('intval', array_keys($this->rows())));
        foreach (array('companies.show', 'contacts.show', 'tasks.list', 'tasks.show', 'activity.listByViewDataGrid') as $key)
        {
            $this->overrides = array($key => 0);
            self::assertSame(array(), $this->rows());
        }
        $this->overrides = array('settings.relationshipAttention' => 0);
        try { (new \CompanySettings())->setAttention(\CompanySettings::getAttentionDefaults()); self::fail('Denied action accepted'); }
        catch (\RuntimeException $e) { self::assertSame('Administrator access required.', $e->getMessage()); }
        $this->overrides = array(); $this->access = ACCESS_LEVEL_EDIT;
        try { (new \CompanySettings())->setAttention(\CompanySettings::getAttentionDefaults()); self::fail('Nonadmin accepted'); }
        catch (\RuntimeException $e) { self::assertSame('Administrator access required.', $e->getMessage()); }
        $this->loggedIn = false;
        self::assertSame(array(), $this->rows());
        self::assertSame(array(), (new \ActivityEntries())->getRelationshipContactDates(array($own)));
    }
}
