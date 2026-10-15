<?php
namespace OpenCATS\Tests\IntegrationTests;

class CandidateJoborderHistoryIndexesTest extends DatabaseTestCase
{
    public function testFreshInstallAndUpgradeHaveHistoryIndexes(): void
    {
        global $mySQLConnection;

        $expected = array(
            'idx_cjosh_candidate_status' => array('candidate_id', 'status_to'),
            'idx_cjosh_joborder_status' => array('joborder_id', 'status_to')
        );

        $this->assertHistoryIndexes($expected);

        /* Restore the table's pre-395 indexes, then apply the upgrade SQL. */
        foreach ($expected as $name => $columns)
        {
            mysqli_query($mySQLConnection, 'DROP INDEX `' . $name . '` ON `candidate_joborder_status_history`');
        }

        require_once LEGACY_ROOT . '/modules/install/Schema.php';
        $schema = \CATSSchema::get();
        foreach (explode(';', $schema[395]) as $sql)
        {
            if (trim($sql) !== '')
            {
                $this->assertTrue(mysqli_query($mySQLConnection, $sql));
            }
        }

        $this->assertHistoryIndexes($expected);
    }

    private function assertHistoryIndexes(array $expected): void
    {
        global $mySQLConnection;

        $result = mysqli_query($mySQLConnection, 'SHOW INDEX FROM `candidate_joborder_status_history`');
        $actual = array();
        while ($row = mysqli_fetch_assoc($result))
        {
            if (isset($expected[$row['Key_name']]))
            {
                $this->assertSame('1', (string) $row['Non_unique']);
                $this->assertNull($row['Sub_part']);
                $actual[$row['Key_name']][(int) $row['Seq_in_index'] - 1] = $row['Column_name'];
            }
        }

        $this->assertEquals($expected, $actual);
    }
}
