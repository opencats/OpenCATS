<?php
/**
 * CATS
 * CATS System Information Library
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: SystemInfo.php 3593 2007-11-13 17:36:57Z andrew $
 */

/**
 *	CATS System Information Library
 *	@package    CATS
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

?>
