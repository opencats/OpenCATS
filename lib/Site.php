<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

/**
 *	Site Library
 *	@package    CATS
 *	@subpackage Library
 */
class Site
{
    private $_db;


    public function __construct()
    {
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
                name = %s",
            $this->_db->makeQueryString($name)
        );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Sets the site name for the current site.
     *
     * @param integer time zone offset
     * @param boolean use D-M-Y format dates
     * @param boolean use 24-hour time format
     * @return boolean True if successful; false otherwise.
     */
    public function setLocalization($timeZone, $isDMY, $isTimeFormat24 = false)
    {
        $sql = sprintf(
            "UPDATE
                site
            SET
                time_zone = %s,
                date_format_ddmmyy = %s,
                time_format_24 = %s",
            $this->_db->makeQueryInteger($timeZone),
            ($isDMY ? 1 : 0),
            ($isTimeFormat24 ? 1 : 0)
        );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Sets the default phone country calling code for the current site.
     *
     * @param string $countryCode E.164 country calling code (for example "+49").
     * @return boolean True if successful; false otherwise.
     */
    public function setDefaultPhoneCountryCode($countryCode)
    {
        $sql = sprintf(
            "UPDATE
                 site
             SET
                 default_phone_country_code = %s",
            $this->_db->makeQueryString($countryCode)
        );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Get site information.
     *
     * @return array site data
     */
    public function getFirstSite()
    {
        $sql =
            "SELECT
                name AS name,
                is_demo AS isDemo,
                user_licenses AS userLicenses,
                entered_by AS enteredBy,
                unix_name AS unixName,
                date_format_ddmmyy as dateFormatDDMMYY,
                time_format_24 AS timeFormat24
            FROM
                site
            WHERE
                account_deleted = 0
            LIMIT 1";

        return $this->_db->getAssoc($sql);
    }

    public function setAgreedToLicense()
    {
        $sql =
            "UPDATE
                site
             SET
                agreed_to_license = 1";
        if (!$this->_db->query($sql))
        {
            return false;
        }
        return true;
    }

    public function setLocalizationConfigured()
    {
        $sql =
            "UPDATE
                site
             SET
                localization_configured = 1";
        if (!$this->_db->query($sql))
        {
            return false;
        }
        return true;
    }

    public function setFirstTimeSetup()
    {
        $sql =
            "UPDATE
                site
             SET
                first_time_setup = 0";
        if (!$this->_db->query($sql))
        {
            return false;
        }
        return true;
    }
}

?>
