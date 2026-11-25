<?php
namespace OpenCATS\Tests\IntegrationTests;

use PHPUnit\Framework\TestCase;

class LegacyUpgradeTest extends TestCase
{
    private const DATABASE_HOST = 'integrationtestdb';
    private const DATABASE_USER = 'dev';
    private const DATABASE_PASS = 'dev';
    private const DATABASE_NAME = 'cats_integrationtest';

    public function testOpenCATS094DatabaseUpgradesToCurrentSchema(): void
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $db = new \mysqli(
            self::DATABASE_HOST,
            self::DATABASE_USER,
            self::DATABASE_PASS
        );
        $db->set_charset('utf8mb4');

        $originalConfig = file_get_contents('./config.php');
        if ($originalConfig === false)
        {
            $this->fail('Unable to read config.php.');
        }

        try
        {
            $db->query(
                'DROP DATABASE IF EXISTS `' . self::DATABASE_NAME . '`'
            );
            $db->query(
                'CREATE DATABASE `' . self::DATABASE_NAME . '`
                 CHARACTER SET utf8
                 COLLATE utf8_unicode_ci'
            );
            $db->select_db(self::DATABASE_NAME);

            $this->loadSQLFile(
                $db,
                'test/data/opencats-0.9.4.sql'
            );
            $this->loadSQLFile(
                $db,
                'test/data/opencats-0.9.4-fixture.sql'
            );

            $this->assertSame(
                363,
                $this->getInstallSchemaVersion($db),
                'OpenCATS 0.9.4 fixture should begin at install schema 363.'
            );

            $this->writeUpgradeConfig();

            $latestVersion = $this->getLatestInstallSchemaVersion();
            $this->assertGreaterThan(363, $latestVersion);

            $currentVersion = $this->getInstallSchemaVersion($db);

            while ($currentVersion < $latestVersion)
            {
                list($exitCode, $output) = $this->runMaintenanceStep();

                $this->assertSame(
                    0,
                    $exitCode,
                    "Upgrade failed at schema {$currentVersion}:\n"
                    . implode("\n", $output)
                );

                $newVersion = $this->getInstallSchemaVersion($db);

                $this->assertGreaterThan(
                    $currentVersion,
                    $newVersion,
                    "Upgrade did not advance beyond schema {$currentVersion}:\n"
                    . implode("\n", $output)
                );

                $currentVersion = $newVersion;
            }

            $this->assertSame(
                $latestVersion,
                $this->getInstallSchemaVersion($db)
            );

            $candidate = $db->query(
                "SELECT
                    first_name,
                    last_name,
                    notes
                 FROM candidate
                 WHERE candidate_id = 1"
            )->fetch_assoc();

            $this->assertSame('Test', $candidate['first_name']);
            $this->assertSame('Müller', $candidate['last_name']);
            $this->assertSame(
                'Legacy & encoded candidate note',
                $candidate['notes']
            );

            $jobOrder = $db->query(
                "SELECT
                    title,
                    notes
                 FROM joborder
                 WHERE joborder_id = 1"
            )->fetch_assoc();

            $this->assertSame('Test Job Order', $jobOrder['title']);
            $this->assertSame(
                'Legacy & encoded job order note',
                $jobOrder['notes']
            );

            $activity = $db->query(
                "SELECT notes
                 FROM activity
                 WHERE activity_id = 1"
            )->fetch_assoc();

            $this->assertSame(
                'Legacy & encoded activity note',
                $activity['notes']
            );

            $countryColumn = $db->query(
                "SHOW COLUMNS
                 FROM candidate
                 LIKE 'country'"
            );

            $this->assertSame(1, $countryColumn->num_rows);

            $duplicatesTable = $db->query(
                "SHOW TABLES
                 LIKE 'candidate_duplicates'"
            );

            $this->assertSame(1, $duplicatesTable->num_rows);

            $siteIDColumn = $db->query(
                "SHOW COLUMNS
                 FROM candidate_duplicates
                 LIKE 'site_id'"
            );

            $this->assertSame(
                0,
                $siteIDColumn->num_rows,
                'Migration 392 should remove site_id from candidate_duplicates.'
            );

            $jobDetails = $db->query(
                "SELECT value
                 FROM career_portal_template
                 WHERE career_portal_name = 'CATS 2.0'
                   AND setting = 'Content - Job Details'"
            )->fetch_assoc();

            $this->assertStringContainsString(
                '<location>',
                $jobDetails['value']
            );

            $applyTemplate = $db->query(
                "SELECT value
                 FROM career_portal_template
                 WHERE career_portal_name = 'CATS 2.0'
                   AND setting = 'Content - Apply for Position'"
            )->fetch_assoc();

            $this->assertStringContainsString(
                '<input-country>',
                $applyTemplate['value']
            );
        }
        finally
        {
            file_put_contents('./config.php', $originalConfig);

            $db->query(
                'DROP DATABASE IF EXISTS `' . self::DATABASE_NAME . '`'
            );
            $db->close();
        }
    }

    private function loadSQLFile(\mysqli $db, string $path): void
    {
        $sql = file_get_contents($path);
        if ($sql === false)
        {
            throw new \RuntimeException(
                sprintf('Unable to read SQL fixture: %s', $path)
            );
        }

        $db->multi_query($sql);

        do
        {
            $result = $db->store_result();
            if ($result !== false)
            {
                $result->free();
            }

            if (!$db->more_results())
            {
                break;
            }

            $db->next_result();
        }
        while (true);
    }

    private function getInstallSchemaVersion(\mysqli $db): int
    {
        $result = $db->query(
            "SELECT version
             FROM module_schema
             WHERE name = 'install'"
        );
        $row = $result->fetch_assoc();

        if (empty($row))
        {
            throw new \RuntimeException(
                'Install schema version was not found.'
            );
        }

        return (int) $row['version'];
    }

    private function writeUpgradeConfig(): void
    {
        $config = file_get_contents('./test/config.php');
        if ($config === false)
        {
            throw new \RuntimeException(
                'Unable to read test/config.php.'
            );
        }

        $config = str_replace(
            array(
                "define('DATABASE_HOST', 'opencatsdb');",
                "define('DATABASE_NAME', 'cats_test');"
            ),
            array(
                "define('DATABASE_HOST', 'integrationtestdb');",
                "define('DATABASE_NAME', 'cats_integrationtest');"
            ),
            $config,
            $replacementCount
        );

        if ($replacementCount !== 2)
        {
            throw new \RuntimeException(
                'Unable to prepare upgrade test configuration.'
            );
        }

        if (file_put_contents('./config.php', $config) === false)
        {
            throw new \RuntimeException(
                'Unable to write upgrade test configuration.'
            );
        }
    }

    private function getLatestInstallSchemaVersion(): int
    {
        $code = <<<'CODE'
include_once('./config.php');
include_once(LEGACY_ROOT . '/constants.php');
include_once(LEGACY_ROOT . '/lib/DatabaseConnection.php');
include_once(LEGACY_ROOT . '/lib/SchemaMigrationStatus.php');

echo SchemaMigrationStatus::getLatestInstallSchemaVersion();
CODE;

        list($exitCode, $output) = $this->runPHPCode($code);

        if ($exitCode !== 0)
        {
            throw new \RuntimeException(
                "Unable to determine current schema version:\n"
                . implode("\n", $output)
            );
        }

        return (int) trim(implode('', $output));
    }

    private function runMaintenanceStep(): array
    {
        /* modules/install/ajax/maint.php only performs its access checks and
         * then hands over to index.php. Those checks require an authenticated
         * site admin session, which is not available here, so drive the
         * maintenance run the same way that endpoint does.
         */
        $code = <<<'CODE'
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['performMaintenence'] = 'yes';

if (file_exists('./modules.cache'))
{
    @unlink('./modules.cache');
}

$maintPage = true;

include './index.php';
CODE;

        return $this->runPHPCode($code);
    }

    private function runPHPCode(string $code): array
    {
        $output = array();
        $exitCode = 0;

        exec(
            escapeshellarg(PHP_BINARY)
            . ' -d display_errors=1 -r '
            . escapeshellarg($code)
            . ' 2>&1',
            $output,
            $exitCode
        );

        return array($exitCode, $output);
    }
}
