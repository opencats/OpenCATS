<?php
/*
 * Finalize the bundled snapshot schema for integration tests without
 * hard-coding the current install schema version.
 */

include_once('./config.php');
include_once(LEGACY_ROOT . '/constants.php');
include_once(LEGACY_ROOT . '/lib/DatabaseConnection.php');
include_once(LEGACY_ROOT . '/lib/SchemaMigrationStatus.php');

$latestVersion = SchemaMigrationStatus::getLatestInstallSchemaVersion();
if ($latestVersion <= 0)
{
    throw new RuntimeException('Unable to determine the latest install schema version.');
}

$db = DatabaseConnection::getInstance();
$result = $db->query(sprintf(
    "UPDATE
        module_schema
    SET
        version = %d
    WHERE
        name = %s",
    $latestVersion,
    $db->makeQueryString('install')
));

if ($result === false)
{
    throw new RuntimeException('Unable to finalize the install schema version.');
}

SchemaMigrationStatus::clearCache();
if ((int) SchemaMigrationStatus::getStoredInstallSchemaVersion() !== $latestVersion)
{
    throw new RuntimeException('The install schema version was not finalized.');
}

?>
