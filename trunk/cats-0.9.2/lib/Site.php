<?php
/**
 * CATS
 * Site Library
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
 * @version    $Id: Site.php 3779 2007-12-03 20:49:58Z andrew $
 */

/**
 *	Site Library
 *	@package    CATS
 *	@subpackage Library
 */
class Site
{
    private $_db;
    private $_siteID;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }


    /**
     * Sets the site name for the current site.
     *
     * @param string new site name
     * @return boolean True if successful; false otherwise.
     */
    public function setName($name)
    {
        $sql = sprintf(
            "UPDATE
                site
            SET
                name = %s
            WHERE
                site_id = %s",
            $this->_db->makeQueryString($name),
            $this->_siteID
        );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Sets the site name for the current site.
     *
     * @param integer time zone offset
     * @param boolean use D-M-Y format dates
     * @return boolean True if successful; false otherwise.
     */
    public function setLocalization($timeZone, $isDMY)
    {
        $sql = sprintf(
            "UPDATE
                site
            SET
                time_zone = %s,
                date_format_ddmmyy = %s
            WHERE
                site_id = %s",
            $this->_db->makeQueryInteger($timeZone),
            ($isDMY ? 1 : 0),
            $this->_siteID
        );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Get site information by unix name.
     *
     * @param integer site ID
     * @return array site data
     */
    public function getSiteByUnixName($unixName)
    {
        $sql = sprintf(
            "SELECT
                site_id AS siteID,
                name AS name,
                is_demo AS isDemo,
                user_licenses AS userLicenses,
                entered_by AS enteredBy,
                unix_name AS unixName
            FROM
                site
            WHERE
                unix_name = %s
            AND
                account_deleted = 0",
           $this->_db->makeQueryStringOrNULL($unixName)
       );

       return $this->_db->getAssoc($sql);
    }

    /**
     * Get site information by site ID.
     *
     * @param integer site ID
     * @return array site data
     */
    public function getSiteBySiteID($siteID)
    {
        $sql = sprintf(
            "SELECT
                site_id AS siteID,
                name AS name,
                is_demo AS isDemo,
                user_licenses AS userLicenses,
                entered_by AS enteredBy,
                unix_name AS unixName,
                date_format_ddmmyy as dateFormatDDMMYY
            FROM
                site
            WHERE
                site_id = %s
            AND
                account_deleted = 0",
           $this->_db->makeQueryInteger($siteID)
       );

       return $this->_db->getAssoc($sql);
    }

    /**
     * Get site information by site ID.
     *
     * @param integer site ID
     * @return array site data
     */
    public function getFirstSiteID()
    {
        $sql = sprintf("
            SELECT
                site_id AS siteID
            FROM
                site
            WHERE
                account_deleted = 0
            AND
                site_id != %s
            ORDER BY
                site_id ASC
            LIMIT 1
        ",
            CATS_ADMIN_SITE
       );

       $rs = $this->_db->getAssoc($sql);

       return $rs['siteID'];
    }

    public function setAgreedToLicense()
    {
        $sql = sprintf(
            "UPDATE
                site
             SET
                agreed_to_license = 1
             WHERE
                site.site_id = %d",
            $this->_siteID
        );
        if (!$this->_db->query($sql))
        {
            return false;
        }
        return true;
    }

    public function setLocalizationConfigured()
    {
        $sql = sprintf(
            "UPDATE
                site
             SET
                localization_configured = 1
             WHERE
                site.site_id = %d",
            $this->_siteID
        );
        if (!$this->_db->query($sql))
        {
            return false;
        }
        return true;
    }

    public function setFirstTimeSetup()
    {
        $db =  DatabaseConnection::getInstance();
        $sql = sprintf(
            "UPDATE
                site
             SET
                first_time_setup = 0
             WHERE
                site.site_id = %d",
            $this->_siteID
        );
        if (!$this->_db->query($sql))
        {
            return false;
        }
        return true;
    }
}

?>
