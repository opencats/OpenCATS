<?php
/**
 * OSATS
 */

/**
 *	OSATS System Information Library
 *	@package    OSATS
 *	@subpackage Library
 */
class SystemInfo
{
    private $_db;


    public function __construct()
    {
        $this->_db = DatabaseConnection::getInstance();
    }


    /**
     * Returns all entries for the system table.
     *
     * @return void
     */
    public function getSystemInfo()
    {
        //FIXME: SELECT INDIVIDUAL COLS!
        $sql = sprintf(
            "SELECT
                *
            FROM
                system
            WHERE
                system_id = 0"
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Updates the UID for the installed system.
     *
     * @param uid (int)
     * @return void
     */
    public function updateUID($uid)
    {
        $sql = sprintf(
            "UPDATE
                system
            SET
                uid = '%s'
            WHERE
                system_id = 0",
            $uid
        );
        $this->_db->query($sql);
    }

    /**
     * Updates the new version check preference.
     *
     * @param boolean (new value)
     * @return void
     */
    public function updateVersionCheckPrefs($enableNewVersionCheck)
    {
        $sql = sprintf(
            "UPDATE
                system
            SET
                disable_version_check = %s
            WHERE
                system_id = 0",
            ($enableNewVersionCheck ? 0 : 1)
        );
        $this->_db->query($sql);
    }

    /**
     * Updates the value of the last snapshot of the available version.
     *
     * @param integer
     * @param newsHtml
     * @param date
     * @return void
     */
    public function updateRemoteVersion($version, $newsRelease, $date)
    {
        $sql = sprintf(
            "UPDATE
                system
            SET
                available_version = '%s',
                available_version_description = '%s',
                date_version_checked = '%s'
            WHERE
                system_id = 0",
            $version,
            urlencode($newsRelease),
            $date
        );
        $this->_db->query($sql);
    }
}