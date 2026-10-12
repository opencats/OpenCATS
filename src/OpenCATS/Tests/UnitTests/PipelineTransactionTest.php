<?php
use PHPUnit\Framework\TestCase;

require_once './constants.php';
require_once './lib/DatabaseConnection.php';
require_once './lib/Session.php';
require_once './lib/Pipelines.php';

class PipelineTransactionTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SESSION['CATS']);
        (new ReflectionProperty(DatabaseConnection::class, '_instance'))->setValue(null, null);
    }

    public function testTransactionHelpersReportBeginAndCommitFailure(): void
    {
        $db = $this->getMockBuilder(DatabaseConnection::class)->disableOriginalConstructor()->onlyMethods(array('query'))->getMock();
        $fail = 'BEGIN';
        $db->expects(self::once())->method('query')->willReturnCallback(fn($sql) => $sql !== $fail);
        self::assertFalse($db->beginTransaction());
        self::assertFalse($db->commitTransaction());
        // A second fixture exercises real helper state after a failed COMMIT.
        $db = $this->getMockBuilder(DatabaseConnection::class)->disableOriginalConstructor()->onlyMethods(array('query'))->getMock();
        $db->expects(self::exactly(4))->method('query')->willReturnCallback(fn($sql) => $sql !== 'COMMIT');
        self::assertTrue($db->beginTransaction());
        self::assertFalse($db->commitTransaction());
        self::assertTrue($db->rollbackTransaction());
        self::assertTrue($db->beginTransaction());
        self::assertFalse($db->beginTransaction());
    }

    public function testPipelineCommitFailureRollsBackBeforeNotification(): void
    {
        $db = $this->getMockBuilder(DatabaseConnection::class)->disableOriginalConstructor()
            ->onlyMethods(array('query', 'getAssoc', 'makeQueryInteger', 'makeQueryStringOrNULL', 'getLastInsertID'))->getMock();
        $queries = array();
        $db->expects(self::exactly(6))->method('query')->willReturnCallback(function ($sql) use (&$queries) {
            $queries[] = $sql;
            return $sql !== 'COMMIT';
        });
        $db->method('getAssoc')->willReturnCallback(function ($sql) {
            self::assertStringContainsString('FOR UPDATE', $sql);
            return array('candidateJobOrderID' => 1, 'oldStatusID' => 100);
        });
        $db->method('makeQueryInteger')->willReturnCallback(fn($id) => (string) $id);
        $db->method('makeQueryStringOrNULL')->willReturnCallback(fn($value) => "'" . $value . "'");
        $db->method('getLastInsertID')->willReturn(2);
        (new ReflectionProperty(DatabaseConnection::class, '_instance'))->setValue(null, $db);
        $session = $this->createStub(CATSSession::class);
        $session->method('getUserID')->willReturn(1);
        $_SESSION['CATS'] = $session;
        try
        {
            (new Pipelines())->setStatus(1, 1, 400, 'unused@example.invalid', 'Must not be sent');
            self::fail('Commit failure must propagate');
        }
        catch (RuntimeException $e) { self::assertSame('Unable to commit pipeline status.', $e->getMessage()); }
        self::assertSame(array('COMMIT', 'ROLLBACK'), array_slice($queries, -2));
    }
}
