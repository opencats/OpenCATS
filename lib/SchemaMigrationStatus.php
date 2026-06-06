<?php
/**
 * CATS
 * Schema Migration Status Library
 *
 * @package    CATS
 * @subpackage Library
 */

include_once(LEGACY_ROOT . '/modules/install/Schema.php');

class SchemaMigrationStatus
{
    private static $_latestInstallSchemaVersion = NULL;
    private static $_storedInstallSchemaVersion = NULL;
    private static $_storedInstallSchemaVersionLoaded = false;
    private static $_hasPendingInstallMigrations = NULL;

    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}

    /**
     * Returns the latest install schema version available in the code.
     *
     * @return integer
     */
    public static function getLatestInstallSchemaVersion()
    {
        if (self::$_latestInstallSchemaVersion !== NULL)
        {
            return self::$_latestInstallSchemaVersion;
        }

        $schemaVersions = array_keys(CATSSchema::get());
        self::$_latestInstallSchemaVersion = empty($schemaVersions) ? 0 : (int) max($schemaVersions);

        return self::$_latestInstallSchemaVersion;
    }

    /**
     * Returns the install schema version stored in the database.
     *
     * NULL or an empty string is not a known applied schema version. It must
     * remain unresolved until an explicit installer or maintenance flow
     * finalizes it; this read-only checker must not normalize it.
     *
     * @return mixed Integer version, NULL or empty string if unresolved,
     *               or false if the row does not exist.
     */
    public static function getStoredInstallSchemaVersion()
    {
        if (self::$_storedInstallSchemaVersionLoaded)
        {
            return self::$_storedInstallSchemaVersion;
        }

        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "SELECT
                version AS version
            FROM
                module_schema
            WHERE
                name = %s",
            $db->makeQueryString('install')
        );
        $rs = $db->getAssoc($sql);

        if (empty($rs))
        {
            self::$_storedInstallSchemaVersion = false;
        }
        else
        {
            self::$_storedInstallSchemaVersion = $rs['version'];
        }

        self::$_storedInstallSchemaVersionLoaded = true;

        return self::$_storedInstallSchemaVersion;
    }

    /**
     * Returns whether install schema migrations are pending.
     *
     * @return boolean
     */
    public static function hasPendingInstallMigrations()
    {
        if (self::$_hasPendingInstallMigrations !== NULL)
        {
            return self::$_hasPendingInstallMigrations;
        }

        $storedVersion = self::getStoredInstallSchemaVersion();

        if ($storedVersion === false ||
            $storedVersion === NULL ||
            $storedVersion === '')
        {
            /* Unknown versions require explicit install or maintenance finalization. */
            self::$_hasPendingInstallMigrations = true;
            return true;
        }

        self::$_hasPendingInstallMigrations =
            (int) $storedVersion < self::getLatestInstallSchemaVersion();

        return self::$_hasPendingInstallMigrations;
    }

    /**
     * Clears cached migration status after explicit maintenance updates.
     *
     * @return void
     */
    public static function clearCache()
    {
        self::$_latestInstallSchemaVersion = NULL;
        self::$_storedInstallSchemaVersion = NULL;
        self::$_storedInstallSchemaVersionLoaded = false;
        self::$_hasPendingInstallMigrations = NULL;
    }
}

?>
