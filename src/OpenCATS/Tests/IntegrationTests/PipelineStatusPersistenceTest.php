<?php
namespace OpenCATS\Tests\IntegrationTests;

class PipelineStatusPersistenceTest extends DatabaseTestCase
{
    private $db;
    protected function setUp(): void
    {
        parent::setUp();
        require_once LEGACY_ROOT . '/lib/Session.php';
        require_once LEGACY_ROOT . '/lib/Hooks.php';
        require_once LEGACY_ROOT . '/lib/Pipelines.php';
        $session = $this->createStub(\CATSSession::class);
        $session->method('getUserID')->willReturn(1);
        $_SESSION['CATS'] = $session;
        $this->db = \DatabaseConnection::getInstance();
        (new \Pipelines())->add(1, 1, 1);
    }
    protected function tearDown(): void
    {
        unset($_SESSION['CATS']);
        (new \ReflectionProperty(\DatabaseConnection::class, '_instance'))->setValue(null, null);
        parent::tearDown();
    }
    public function testStatusAndHistoryCommitAndSequentialDuplicate(): void
    {
        $pipeline = new \Pipelines();
        foreach (array(400, 400, 100, 400) as $status) $pipeline->setStatus(1, 1, $status, '', '');
        self::assertEquals(400, $this->db->getAssoc('SELECT status FROM candidate_joborder')['status']);
        $rows = $this->db->getAllAssoc('SELECT status_from, status_to FROM candidate_joborder_status_history ORDER BY candidate_joborder_status_history_id');
        self::assertCount(3, $rows);
        self::assertEquals(array(100, 400, 100), array_column($rows, 'status_from'));
        self::assertCount(3, $this->db->getAllAssoc("SELECT entered_by FROM history WHERE the_field = 'PIPELINE'"));
    }
    public function testStatusHistoryAndAuditFailuresRollBack(): void
    {
        foreach (array('candidate_joborder', 'candidate_joborder_status_history', 'history') as $table)
        {
            $event = $table === 'candidate_joborder' ? 'UPDATE' : 'INSERT';
            $this->db->query("CREATE TRIGGER reject_write BEFORE $event ON $table FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'fixture write failure'");
            try
            {
                (new \Pipelines())->setStatus(1, 1, 400, '', '');
                self::fail('Failure must propagate.');
            }
            catch (\Throwable $e) { self::assertStringContainsString('fixture write failure', $e->getMessage()); }
            finally { $this->db->query('DROP TRIGGER reject_write'); }
            self::assertEquals(100, $this->db->getAssoc('SELECT status FROM candidate_joborder')['status']);
            self::assertSame(array(), $this->db->getAllAssoc('SELECT * FROM candidate_joborder_status_history'));
            self::assertSame(array(), $this->db->getAllAssoc("SELECT * FROM history WHERE the_field = 'PIPELINE'"));
        }
        (new \Pipelines())->setStatus(1, 1, 400, '', ''); // Connection/transaction remains usable.
        self::assertEquals(400, $this->db->getAssoc('SELECT status FROM candidate_joborder')['status']);
    }
}
