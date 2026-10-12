<?php
namespace OpenCATS\Tests\IntegrationTests;

class WarningReviewTest extends DatabaseTestCase
{
    private $db;
    private $reviews;
    private $company;
    private $actor = 1;
    private $overrides = array();

    protected function setUp(): void
    {
        parent::setUp();
        require_once LEGACY_ROOT . '/lib/CATSUtility.php';
        require_once LEGACY_ROOT . '/lib/Session.php';
        require_once LEGACY_ROOT . '/lib/WarningReviews.php';
        $this->actor = 1;
        $this->overrides = array();
        $session = $this->createStub(\CATSSession::class);
        $session->method('getUserID')->willReturnCallback(fn() => $this->actor);
        $session->method('getAccessLevel')->willReturnCallback(fn($key) => $this->overrides[$key] ?? ACCESS_LEVEL_SA);
        $session->method('getRealAccessLevel')->willReturn(ACCESS_LEVEL_SA);
        $session->method('isLoggedIn')->willReturn(true);
        $session->method('getTimeZoneOffset')->willReturn(0);
        $_SESSION['CATS'] = $session;
        $this->db = \DatabaseConnection::getInstance();
        $this->reviews = new \WarningReviews();
        $this->db->query("INSERT INTO company (name, owner, commercial_tier, relationship_status) VALUES ('Review fixture', 1, 'A', 'Client')");
        $this->company = (int) $this->db->getLastInsertID();
    }

    protected function tearDown(): void
    {
        unset($_SESSION['CATS']);
        (new \ReflectionProperty(\DatabaseConnection::class, '_instance'))->setValue(null, null);
        parent::tearDown();
    }

    private function snapshot($reason = 'stale_relationship')
    {
        return $this->reviews->get('W07', DATA_ITEM_COMPANY, $this->company, $reason);
    }

    private function save($snapshot, $request = 'request-0000000001', $changes = array())
    {
        $i = $snapshot['identity'];
        $args = array($i['warning_rule'], $i['data_item_type'], $i['data_item_id'], $i['reason_key'], $i['reason_id'],
            'close', 'already_handled', date('Y-m-d', strtotime('+5 days')), $snapshot['version'], $snapshot['evidence'], $request);
        return $this->reviews->save(...array_replace($args, $changes));
    }

    private function rejected($call)
    {
        try { $call(); }
        catch (\InvalidArgumentException | \RuntimeException | \mysqli_sql_exception $e) { self::assertNotEmpty($e->getMessage()); return; }
        self::fail('Expected request rejection.');
    }

    private function countRows($table)
    {
        return (int) $this->db->getAssoc('SELECT COUNT(*) AS n FROM ' . $table)['n'];
    }

    public function testPersonalReasonSeparationAndUnchangedSourceQueues(): void
    {
        $before = (new \Tasks())->getRecruitmentWarnings();
        self::assertSame(1, $this->save($this->snapshot()));
        self::assertSame(0, $this->snapshot('no_next_action')['version']);
        self::assertSame(1, $this->save($this->snapshot('no_next_action')));
        self::assertSame($before, (new \Tasks())->getRecruitmentWarnings());
        $this->db->query("INSERT INTO user (user_name, access_level) VALUES ('reviewer-two', 400)");
        $this->actor = (int) $this->db->getLastInsertID();
        // Existing W07 scope is owner-only. A native ownership change must not
        // transfer the previous owner's personal review to the new owner.
        $this->db->query('UPDATE company SET owner = ' . $this->actor . ' WHERE company_id = ' . $this->company);
        self::assertSame(0, $this->snapshot()['version']);
        $this->save($this->snapshot());
        self::assertSame(3, $this->countRows('warning_review'));
        self::assertSame(3, $this->countRows('history'));
        $this->actor = 1;
        $this->rejected(fn() => $this->snapshot());
    }

    public function testDuplicateAndStaleWritesCannotExtendDeadlineOrAudit(): void
    {
        $initial = $this->snapshot();
        self::assertSame(1, $this->save($initial));
        $stored = $this->snapshot();
        self::assertSame(1, $this->save($initial));
        self::assertSame($stored, $this->snapshot());
        self::assertSame(1, $this->countRows('history'));
        $this->rejected(fn() => $this->save($initial, 'request-0000000002'));
        $this->rejected(fn() => $this->save($initial, 'request-0000000001', array(7 => date('Y-m-d', strtotime('+6 days')))));
        self::assertSame(2, $this->save($stored, 'request-0000000002', array(5 => 'reset', 6 => null, 7 => null)));
        $this->rejected(fn() => $this->save($initial));
        self::assertNull($this->snapshot()['state']['review_until']);
        self::assertSame(2, $this->countRows('history'));
    }

    public function testInvalidRequestsPermissionsAndSourceChanges(): void
    {
        $s = $this->snapshot();
        foreach (array(array(5 => array()), array(5 => 'log'), array(6 => 'free text'), array(7 => '2026-02-30'),
            array(7 => date('Y-m-d')), array(7 => date('Y-m-d', strtotime('+31 days'))), array(8 => '0'),
            array(9 => array()), array(10 => 'short'), array(2 => array()), array(4 => 1)) as $change)
            $this->rejected(fn() => $this->save($s, 'request-0000000001', $change));
        foreach (array('companies.show', 'companies.edit', 'tasks.show', 'contacts.show') as $permission)
        {
            $this->overrides[$permission] = ACCESS_LEVEL_DISABLED;
            $this->rejected(fn() => $this->save($s));
            $this->overrides = array();
        }
        $this->db->query("UPDATE company SET commercial_tier = 'B' WHERE company_id = " . $this->company);
        $this->rejected(fn() => $this->save($s));
        $s = $this->snapshot();
        $this->db->query('UPDATE user SET access_level = 0 WHERE user_id = 1');
        $this->rejected(fn() => $this->save($s));
        self::assertSame(0, $this->countRows('warning_review'));
        self::assertSame(0, $this->countRows('history'));
    }

    public function testReasonIdentityAndUnapprovedActionsFailClosed(): void
    {
        foreach (array(array('W01', DATA_ITEM_TASK, 'overdue_commitment'),
            array('W08', DATA_ITEM_TASK, 'invalid_assignee'), array('W08', DATA_ITEM_JOBORDER, 'invalid_owner'),
            array('W08', DATA_ITEM_JOBORDER, 'invalid_recruiter'), array('W02', DATA_ITEM_JOBORDER, 'no_recorded_submission')) as [$rule, $type, $reason])
        {
            $i = \WarningReviews::identity($rule, $type, 1, $reason);
            self::assertSame($reason, $i['reason_key']);
            foreach (array('close', 'remind_later') as $action)
                $this->rejected(fn() => $this->reviews->save($rule, $type, 1, $reason, 0, $action, null,
                    date('Y-m-d', strtotime('+1 day')), 0, str_repeat('a', 64), 'request-0000000001'));
        }
        $a = \WarningReviews::identity('W07', DATA_ITEM_COMPANY, 1, 'overdue_task', 10);
        $b = \WarningReviews::identity('W07', DATA_ITEM_COMPANY, 1, 'overdue_task', 11);
        self::assertNotSame($a, $b);
        $this->rejected(fn() => \WarningReviews::identity('W07', DATA_ITEM_COMPANY, 1, 'overdue_task'));
        $this->rejected(fn() => \WarningReviews::identity('W08', DATA_ITEM_JOBORDER, 1, 'invalid_assignee'));
    }

    public function testHistoryFailureRollsBackInsertAndUpdate(): void
    {
        $s = $this->snapshot();
        $this->db->query("CREATE TRIGGER reject_review_history BEFORE INSERT ON history FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Review audit test failure'");
        $this->rejected(fn() => $this->save($s));
        self::assertSame(0, $this->countRows('warning_review'));
        $this->db->query('DROP TRIGGER reject_review_history');
        $this->save($s);
        $before = $this->snapshot();
        $this->db->query("CREATE TRIGGER reject_review_history BEFORE INSERT ON history FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Review audit test failure'");
        $this->rejected(fn() => $this->save($before));
        self::assertSame($before, $this->snapshot());
        self::assertSame(1, $this->countRows('history'));
        self::assertTrue($this->db->beginTransaction());
        $this->rejected(fn() => $this->save($before));
        self::assertTrue($this->db->rollbackTransaction());
    }

    public function testReminderPresetsAndIndependentTaskReasons(): void
    {
        $s = $this->snapshot();
        $tomorrow = substr(\DateUtility::addWorkingDays(\DateUtility::getAdjustedDate('Y-m-d') . ' 00:00:00', 1), 0, 10);
        self::assertSame(1, $this->save($s, 'request-reminder-01', array(5 => 'remind_later', 6 => null, 7 => $tomorrow)));
        self::assertSame($tomorrow, $this->snapshot()['state']['review_until']);
        $taskIDs = array();
        foreach (array(1, 2) as $number)
            $taskIDs[] = (new \Tasks())->add(array('title' => 'Review commitment ' . $number,
                'dataItemType' => DATA_ITEM_COMPANY, 'dataItemID' => $this->company,
                'assignedTo' => 1, 'purpose' => 'relationship_follow_up', 'dueDate' => date('Y-m-d', strtotime('-1 day'))));
        foreach ($taskIDs as $id)
        {
            $taskState = $this->reviews->get('W07', DATA_ITEM_COMPANY, $this->company, 'overdue_task', $id);
            self::assertSame(0, $taskState['version']);
            $this->rejected(fn() => $this->save($taskState));
        }
        $this->rejected(fn() => $this->reviews->get('W07', DATA_ITEM_COMPANY, $this->company, 'overdue_task', 2147483647));
        self::assertSame(1, $this->countRows('warning_review'));
    }

    public function testConcurrentFirstRequestsHaveOneWinner(): void
    {
        $this->concurrentRequests(false);
    }

    public function testConcurrentDuplicateRequestsAreIdempotent(): void
    {
        $this->concurrentRequests(true);
    }

    private function concurrentRequests($duplicate)
    {
        $s = $this->snapshot();
        $runner = tempnam(sys_get_temp_dir(), 'review-race-');
        file_put_contents($runner, <<<'RUNNER'
<?php
require 'vendor/autoload.php';
define('LEGACY_ROOT', '.');
define('DATABASE_NAME', 'cats_integrationtest');
define('DATABASE_HOST', 'integrationtestdb');
require 'config.php';
require 'constants.php';
require 'lib/DatabaseConnection.php';
require 'lib/CATSUtility.php';
require 'lib/WarningReviews.php';
$_SESSION['CATS'] = new class {
    public function getUserID() { return 1; }
    public function isLoggedIn() { return true; }
    public function getAccessLevel($key) { return ACCESS_LEVEL_SA; }
    public function getRealAccessLevel() { return ACCESS_LEVEL_SA; }
    public function getTimeZoneOffset() { return 0; }
    public function isDateDMY() { return false; }
    public function isTimeFormat24() { return false; }
};
$args = json_decode(trim(fgets(STDIN)), true);
echo "ready\n";
fflush(STDOUT);
fgets(STDIN);
try { echo 'saved:' . (new WarningReviews())->save(...$args) . "\n"; }
catch (RuntimeException $e) { echo 'rejected:' . $e->getMessage() . "\n"; }
RUNNER
        );
        $workers = array();
        try
        {
            self::assertTrue($this->db->beginTransaction());
            $this->db->getAssoc('SELECT user_id FROM user WHERE user_id = 1 FOR UPDATE');
            foreach (array('request-race-0001', $duplicate ? 'request-race-0001' : 'request-race-0002') as $request)
            {
                $pipes = array();
                $process = proc_open(array(PHP_BINARY, $runner), array(0 => array('pipe', 'r'),
                    1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
                self::assertIsResource($process);
                $args = array('W07', DATA_ITEM_COMPANY, $this->company, 'stale_relationship', 0,
                    'close', 'already_handled', date('Y-m-d', strtotime('+5 days')), 0, $s['evidence'], $request);
                fwrite($pipes[0], json_encode($args) . "\n");
                self::assertSame("ready\n", fgets($pipes[1]));
                $workers[] = array($process, $pipes);
            }
            foreach ($workers as [$process, $pipes]) { fwrite($pipes[0], "go\n"); fclose($pipes[0]); }
            // Observe both independent connections at the actual contested row.
            $deadline = microtime(true) + 5;
            do
            {
                $waiting = array_filter($this->db->getAllAssoc('SHOW PROCESSLIST'),
                    fn($row) => strpos($row['Info'] ?? '', 'SELECT access_level FROM user WHERE user_id = 1 FOR UPDATE') === 0);
                if (count($waiting) === 2) break;
                usleep(10000);
            } while (microtime(true) < $deadline);
            self::assertCount(2, $waiting);
            self::assertTrue($this->db->commitTransaction());
            $results = array();
            foreach ($workers as [$process, $pipes])
            {
                $results[] = trim(stream_get_contents($pipes[1]));
                fclose($pipes[1]);
                $errors = stream_get_contents($pipes[2]);
                fclose($pipes[2]);
                self::assertSame(0, proc_close($process), $errors);
            }
            sort($results);
            self::assertSame($duplicate ? array('saved:1', 'saved:1') :
                array('rejected:Warning review is stale. Reload the source.', 'saved:1'), $results);
            self::assertSame(1, $this->countRows('warning_review'));
            self::assertSame(1, $this->countRows('history'));
        }
        finally { $this->db->rollbackTransaction(); unlink($runner); }
    }

    public function testMigrationRetryAndBackupRoundTrip(): void
    {
        require_once LEGACY_ROOT . '/modules/install/Schema.php';
        require_once LEGACY_ROOT . '/modules/install/backupDB.php';
        $fresh = $this->db->getAssoc('SHOW CREATE TABLE warning_review')['Create Table'];
        $this->db->query('DROP TABLE warning_review');
        $db = $this->db;
        eval(substr(\CATSSchema::get()[402], 4));
        self::assertSame($fresh, $this->db->getAssoc('SHOW CREATE TABLE warning_review')['Create Table']);
        $this->save($this->snapshot());
        eval(substr(\CATSSchema::get()[402], 4));
        self::assertSame(1, $this->countRows('warning_review'));
        // Cross-module review history survives without broadening all legacy history.
        (new \History())->storeHistoryData(DATA_ITEM_JOBORDER, 1, 'warning_review', null, 'fixture', 'Review backup fixture');
        (new \History())->storeHistoryData(DATA_ITEM_COMPANY, 1, 'other', null, 'excluded', 'Unrelated history');
        $before = $this->snapshot();
        $file = tempnam(sys_get_temp_dir(), 'review-backup-');
        try
        {
            \dumpDB($this->db, $file);
            foreach (glob($file . '.*') as $part)
                foreach (explode('((ENDOFQUERY))', file_get_contents($part)) as $sql)
                    if (preg_match('/^(DROP TABLE IF EXISTS|CREATE TABLE|INSERT INTO) `(warning_review|history)`/', trim($sql)))
                        self::assertNotFalse($this->db->query(trim($sql)));
            self::assertSame($before, $this->snapshot());
            self::assertSame(2, $this->countRows('history'));
        }
        finally { foreach (glob($file . '*') as $part) unlink($part); }
        $this->db->query('ALTER TABLE warning_review DROP PRIMARY KEY');
        $this->rejected(function () use ($db) { eval(substr(\CATSSchema::get()[402], 4)); });
    }
}
