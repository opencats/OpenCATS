<?php
/**
 * CATS
 * Session Library
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
 * @version    $Id: Session.php 3676 2007-11-21 21:02:15Z brian $
 */

include('./lib/ACL.php');

/**
 *  CATS Session Object
 *  @package    CATS
 *  @subpackage Library
 */
class CATSSession
{
    private $_siteID = -1;
    private $_userID = -1;
    private $_siteCompanyID = -1;
    private $_userLoginID = -1;
    private $_accessLevel = -1;
    private $_realAccessLevel = -1;
    private $_isLoggedIn = false;
    private $_isDemo = false;
    private $_isASP = false;
    private $_isFree = false;
    private $_isHrMode = false;
    private $_accountActive = true;
    private $_accountDeleted = false;
    private $_siteName = '';
    private $_unixName = '';
    private $_username = '';
    private $_password = '';
    private $_firstName = '';
    private $_lastName = '';
    private $_email = '';
    private $_ip = '';
    private $_userAgent = '';
    private $_userLicenses = 0;
    private $_loginError = '';
    private $_checkBoxes = array();
    private $_dropdowns = array();
    private $_categories = array();
    private $_startTime;
    private $_endTime;
    private $_backupDirectory;
    private $_storedBuild = -1;
    private $_timeZoneOffset = 0;
    private $_timeZone = 0;
    private $_dateDMY = false;
    private $_pipelineEntriesPerPage = 15;
    private $_storedData = array();
    private $_storedValues = array();
    private $_MRU = null;
    private $_dataGridColumnPreferences = array();
    private $_dataGridParameters = array();
    private $_isFirstTimeSetup = false;
    private $_isAgreedToLicense = false;
    private $_isLocalizationConfigured = false;
    private $_loggedInDirectory = '';

    /**
     * Returns this session's MRU object, and creates one if it doesn't exist.
     *
     * @return object This session's MRU object.
     */
    public function getMRU()
    {
        if (!isset($this->_MRU) || $this->_MRU === null)
        {
            $this->_MRU = new MRU($this->_userID, $this->_siteID);
        }

        return $this->_MRU;
    }

    /**
     * Returns the current CATS development build number, or 0 for
     * non-development CATS builds. The build number is cached on the
     * first call and returned from cache on subsequent calls.
     *
     * @return integer CATS development build number.
     */
    public function getCachedBuild()
    {
        if ($this->_storedBuild == -1)
        {
            $this->_storedBuild = CATSUtility::getBuild();
        }

        return (integer) $this->_storedBuild;
    }

    /**
     * Forces all modules to be reloaded if the development build number
     * has changed since the last call. The build number is then cached in
     * $this->_storedBuild so that multiple filesystem accesses are not
     * required.
     *
     * @return void
     */
    public function checkForcedUpdate()
    {
       $build = CATSUtility::getBuild();

       /* We don't want to force an update on the first check (when the stored
        * build -1), because we just reloaded all of the modules anyway.
        * CATSUtility::getBuild() should never return -1, but just in case...
        */
       if ($this->_storedBuild != -1 && $this->_storedBuild != $build)
       {
           $this->forceUpdate();
       }

       $this->_storedBuild = $build;
    }

    /**
     * Forces all modules, hooks, filters, etc. to be reloaded. This is called
     * by checkForcedUpdate() whenever the development build number changes.
     *
     * @return void
     */
    public function forceUpdate()
    {
        /* Force the current session to reload everything (hooks, etc). */
        if (isset($_SESSION['modules']))
        {
             unset($_SESSION['modules']);
        }
    }

    /**
     * If ENABLE_SINGLE_SESSION is turned on and this is not a demo account or
     * a read-only user account, this will check to see if the session should
     * be forcibly logged out due to another user recently having logged in as
     * the same user account.
     *
     * Will also log out the user if _loggedInDirectory is not the same
     * script that the user logged in as.
     *
     * @return boolean Force logout?
     */
    public function checkForceLogout()
    {
        /* Sanity check. */
        if (!$this->_isLoggedIn)
        {
            return false;
        }

        /* Is _loggedInDirectory equal getDirectoryName?  If not, logout. */
        if ($this->_loggedInDirectory != '' && $this->_loggedInDirectory != CATSUtility::getDirectoryName())
        {
            return true;
        }
        
        /* Sanity check. */
        if ($this->getUnixName() == '')
        {
            return false;
        }
        
        /* Forced logouts can only occur if Single Session mode is enabled. */
        if (!ENABLE_SINGLE_SESSION)
        {
            return false;
        }

        /* Don't force logout for certain kinds of accounts
         * account.
         */
        if ($this->isDemo() ||
            $this->getAccessLevel(ACL::SECOBJ_ROOT) == ACCESS_LEVEL_READ ||
            $this->getAccessLevel(ACL::SECOBJ_ROOT) >= ACCESS_LEVEL_ROOT ||
            $this->_unixName == 'cognizo')
        {
            return false;
        }

        /* Don't force logout for site 200.
         * TODO:  Remove me.
         */
        if ($this->getSiteID() == 200)
        {
            return false;
        }

        /* Get the current user's session cookie from the database. */
        $users = new Users($this->_siteID);
        $userRS = $users->get($this->_userID);
        if (empty($userRS) || !isset($userRS['sessionCookie']) ||
            empty($userRS['sessionCookie']))
        {
            return false;
        }

        /* Does this session's session cookie match the one stored in the
         * database? If not, this is probably a duplicate login.
         */
        if ($userRS['sessionCookie'] != $this->getCookie())
        {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the current session is logged in, false otherwise. This
     * can also be used to see if a login attempt is successful. See
     * processLogin().
     *
     * @return boolean Is the current session logged in?
     */
    public function isLoggedIn()
    {
        return $this->_isLoggedIn;
    }

    /**
     * Marks a session as logged out.
     *
     * @return void
     */
    public function logout()
    {
        $this->_isLoggedIn = false;
    }

    /**
     * Returns true if this is a demo account; false otherwise. The database is
     * not accessed.
     *
     * @return boolean Is the current session a demo account?
     */
    public function isDemo()
    {
        return $this->_isDemo;
    }

    // FIXME: Document me!
    public function isASP()
    {
        return $this->_isASP;
    }

    // FIXME: Document me!
    public function isFree()
    {
        return $this->_isFree;
    }

    public function isFirstTimeSetup()
    {
        return $this->_isFirstTimeSetup;
    }

    public function isAgreedToLicense()
    {
        return $this->_isAgreedToLicense;
    }

    public function isLocalizationConfigured()
    {
        return $this->_isLocalizationConfigured;
    }

    // FIXME: Document me!
    public function accountActive()
    {
        return $this->_accountActive;
    }

    // FIXME: Document me!
    public function accountDeleted()
    {
        return $this->_accountDeleted;
    }

    public function isHrMode()
    {
        return $this->_isHrMode;
    }

    /**
     * Returns the current user's site ID stored in the session. The database
     * is not accessed. -1 will be returned if the site ID does not exist for
     * any reason.
     *
     * @return integer Current user's site ID, or -1 if nonexistant.
     */
    public function getSiteID()
    {
        if (isset($this->_siteID) && !empty($this->_siteID))
        {
            return $this->_siteID;
        }

        return -1;
    }

    // FIXME: Document me!
    public function getSiteCompanyID()
    {
        return $this->_siteCompanyID;
    }

    /**
     * Returns the current user's user ID stored in the session. The database
     * is not accessed.
     *
     * @return integer Current user's user ID.
     */
    public function getUserID()
    {
        return $this->_userID;
    }

    /**
     * Returns the current user's username stored in the session. The database
     * is not accessed.
     *
     * @return string Current user's username.
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     * Returns the current user's password. Although this is a bad idea,
     * it is used to generate a password for the firefox toolbar download.
     *
     * The database is not accessed.
     *
     * @return string Current user's username.
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * Returns the current user's IP address in dotted decimal notation.
     *
     * @return string Current user's IP address.
     */
    public function getIP()
    {
        return $this->_ip;
    }

    /**
     * Returns the current user's browser user agent string.
     *
     * @return string Current user's browser user agent string.
     */
    public function getUserAgent()
    {
        return $this->_userAgent;
    }

    /**
     * Returns true if the D-M-Y date format is being used for the current
     * user, false otherwise. The database is not accessed.
     *
     * @return boolean Are D-M-Y format dates being used?
     */
    public function isDateDMY()
    {
        return $this->_dateDMY;
    }

    // FIXME: Document me!
    public function getAccessLevel($securedObjectName)
    {
        return ACL::getAccessLevel($securedObjectName, $this->getUserCategories(), $this->_accessLevel);
    }

    // FIXME: Document me!
    public function getRealAccessLevel()
    {
        return $this->_realAccessLevel;
    }

    // FIXME: Document me!
    public function canSeeEEOInfo()
    {
        return $this->_canSeeEEOInfo;
    }

    // FIXME: Document me!
    public function setRealAccessLevel($accessLevel)
    {
        $this->_realAccessLevel = $accessLevel;

        if ($accessLevel < $this->_accessLevel)
        {
            $this->_accessLevel = $accessLevel;
        }
    }

    /**
     * Sets the current site's site name stored in the session. The
     * database is not accessed.
     *
     * @return void
     */
    public function setSiteName($newSiteName)
    {
        $this->_siteName = $newSiteName;
    }

    /**
     * Gets the current site's site name stored in the session. The
     * database is not accessed.
     *
     * @return string Current site's site name.
     */
    public function getSiteName()
    {
        return $this->_siteName;
    }

    /**
     * Gets the current site's short / unix name stored in the session. The
     * database is not accessed.
     *
     * @return string Current site's short / unix name.
     */
    public function getUnixName()
    {
        return $this->_unixName;
    }

    /**
     * Gets the current user's first name stored in the session. The
     * database is not accessed.
     *
     * @return string Current user's first name.
     */
    public function getFirstName()
    {
        return $this->_firstName;
    }

    /**
     * Gets the current user's last name stored in the session. The
     * database is not accessed.
     *
     * @return string Current user's last name.
     */
    public function getLastName()
    {
        return $this->_lastName;
    }

    /**
     * Gets the current user's full name stored in the session. The
     * database is not accessed.
     *
     * @return string Current user's full name.
     */
    public function getFullName()
    {
        return $this->_firstName . ' ' . $this->_lastName;
    }

    /**
     * Gets the current user's e-mail address stored in the session. The
     * database is not accessed.
     *
     * @return string Current user's e-mail address.
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * Gets the current user's time zone offset from the system time zone
     * (from config.php) stored in the session. The database is not accessed,
     * nor is config.php. 0 is returned if the session is not logged in.
     *
     * @return integer Time zone offset from the system time zone.
     */
    public function getTimeZoneOffset()
    {
        if ($this->isLoggedIn())
        {
            return $this->_timeZoneOffset;
        }

        return 0;
    }

    /**
     * Gets the current user's time zone offset from GMT stored in the session.
     * The database is not accessed.
     *
     * @return integer Time zone offset from GMT.
     */
    public function getTimeZone()
    {
        return $this->_timeZone;
    }

    // FIXME: Document me!
    public function getUserCategories()
    {
        return $this->_categories;
    }

    // FIXME: Document me!
    public function hasUserCategory($category)
    {
        return in_array($category, $this->_categories);
    }

    // FIXME: Document me!
    public function getPipelineEntriesPerPage()
    {
         return $this->_pipelineEntriesPerPage;
    }

    // FIXME: Document me!
    public function getCookie()
    {
        return CATS_SESSION_NAME . '=' . session_id();
    }

    // FIXME: Document me!
    public function getCheckBox($name)
    {
        if (isset($this->_checkBoxes[$name]))
        {
            return (boolean) $this->_checkBoxes[$name];
        }

        return false;
    }

    // FIXME: Document me!
    public function setCheckBox($name, $state)
    {
        $this->_checkBoxes[$name] = $state;
    }

    // FIXME: Document me!
    public function getDropdown($name)
    {
        if (isset($this->_dropdowns[$name]))
        {
            return $this->_dropdowns[$name];
        }

        return false;
    }

    // FIXME: Document me!
    public function setDropdown($name, $value)
    {
        $this->_dropdowns[$name] = $value;
    }

    /**
     * Updates time and date localization settings in the session. The database
     * is not modified.
     *
     * @param integer Time zone offset from GMT.
     * @param boolean Display dates in D-M-Y format?
     * @return void
     */
    public function setTimeDateLocalization($timeZone, $isDMY)
    {
        $timeZone = (integer) $timeZone;

        $this->_timeZone       = $timeZone;
        $this->_timeZoneOffset = $timeZone - OFFSET_GMT;
        $this->_dateDMY        = $isDMY;
    }

    /**
     * This is called whenever a page is loaded to update "active" statistics
     * for the currently logged-in user. The last refresh timestamp in the
     * user_login table is updated.
     *
     * @return void
     */
    public function logPageView()
    {
        if (!$this->isLoggedIn())
        {
            return;
        }

        $users = new Users($this->_siteID);
        $userLoginID = $users->updateLastRefresh(
            $this->_userLoginID,
            $this->_siteID
        );
    }

    /**
     * Processes a user login request and sets up the session if successful.
     * After calling this method, if $this->isLoggedIn() returns false, an
     * error occurred (which can be retrieved using $this->getLoginError()).
     *
     * @param string User's username.
     * @param string User's password.
     * @param boolean Log this login attempt in Login History?
     * @return void
     */
    public function processLogin($username, $password, $addToHistory = true)
    {
        $db = DatabaseConnection::getInstance();

        /* Is the login information supplied correct? Get the status flag. */
        $users = new Users(-1);
        $loginStatus = $users->isCorrectLogin($username, $password);

        if ($loginStatus == LOGIN_INVALID_USER)
        {
            $this->_isLoggedIn = false;
            $this->_loginError = 'Invalid username or password.';

            return;
        }

        $sql = sprintf(
            "SELECT
                user.user_id AS userID,
                user.user_name AS username,
                user.password AS password,
                user.first_name AS firstName,
                user.last_name AS lastName,
                user.access_level AS accessLevel,
                user.site_id AS userSiteID,
                user.is_demo AS isDemoUser,
                user.email AS email,
                user.categories AS categories,
                user.pipeline_entries_per_page AS pipelineEntriesPerPage,
                user.column_preferences as columnPreferences,
                user.can_see_eeo_info as canSeeEEOInfo,
                site.name AS siteName,
                site.unix_name AS unixName,
                site.user_licenses AS userLicenses,
                site.company_id AS companyID,
                site.is_demo AS isDemo,
                site.account_active AS accountActive,
                site.account_deleted AS accountDeleted,
                site.time_zone AS timeZone,
                site.date_format_ddmmyy AS dateFormatDMY,
                site.is_free AS isFree,
                site.is_hr_mode AS isHrMode,
                site.first_time_setup as isFirstTimeSetup,
                site.localization_configured as isLocalizationConfigured,
                site.agreed_to_license as isAgreedToLicense,
                IF(site.last_viewed_day = CURDATE(), 1, 0) AS lastViewedDayIsToday
            FROM
                user
            LEFT JOIN site
                ON site.site_id = user.site_id
            WHERE
                user.user_name = %s",
            $db->makeQueryString($username)
        );
        $rs = $db->getAssoc($sql);

        /* Invalid username or password. */
        if (!$rs || $db->isEOF())
        {
            $this->_isLoggedIn = false;
            $this->_loginError = 'Invalid username or password.';
            return;
        }

        if (isset($_SERVER['REMOTE_ADDR']))
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        else
        {
            $ip = '';
        }

        if (isset($_SERVER['HTTP_USER_AGENT']))
        {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }
        else
        {
            $userAgent = '';
        }

        switch ($loginStatus)
        {
            case LOGIN_INVALID_PASSWORD:
                $this->_isLoggedIn = false;
                $this->_loginError = 'Invalid username or password.';

                /* Log the login as unsuccessful. */
                if ($addToHistory)
                {
                    $users->addLoginHistory(
                        $rs['userID'],
                        $rs['userSiteID'],
                        $ip,
                        $userAgent,
                        false
                    );
                }

                break;

            case LOGIN_ROOT_ONLY:
                $this->_isLoggedIn = false;
                $this->_loginError = 'Only root administrators can login at this time.';

                /* Log the login as unsuccessful. */
                if ($addToHistory)
                {
                    $users->addLoginHistory(
                        $rs['userID'],
                        $rs['userSiteID'],
                        $ip,
                        $userAgent,
                        false
                    );
                }

                break;

            case LOGIN_DISABLED:
                $this->_isLoggedIn = false;
                $this->_loginError = 'Your account is disabled or pending approval.';

                /* Log the login as unsuccessful. */
                if ($addToHistory)
                {
                    $users->addLoginHistory(
                        $rs['userID'],
                        $rs['userSiteID'],
                        $ip,
                        $userAgent,
                        false
                    );
                }

                break;
                
            case LOGIN_PENDING_APPROVAL:
                $this->_isLoggedIn = false;
                $this->_loginError = 'Your account has been created and is pending approval.';

                break;
                
            case LOGIN_SUCCESS:
                $this->_username               = $rs['username'];
                $this->_password               = $rs['password'];
                $this->_userID                 = $rs['userID'];
                $this->_siteID                 = $rs['userSiteID'];
                $this->_firstName              = $rs['firstName'];
                $this->_lastName               = $rs['lastName'];
                $this->_siteName               = $rs['siteName'];
                $this->_unixName               = $rs['unixName'];
                $this->_userLicenses           = $rs['userLicenses'];
                $this->_accessLevel            = $rs['accessLevel'];
                $this->_realAccessLevel        = $rs['accessLevel'];
                $this->_categories             = explode(',', $rs['categories']);
                $this->_isASP                  = ($rs['companyID'] != 0 ? true : false);
                $this->_isHrMode               = ($rs['isHrMode'] != 0 ? true : false);
                $this->_siteCompanyID          = ($rs['companyID'] != 0 ? $rs['companyID'] : -1);
                $this->_isFree                 = ($rs['isFree'] == 0 ? false : true);
                $this->_isFirstTimeSetup       = ($rs['isFirstTimeSetup'] == 0 ? false : true);
                $this->_isLocalizationConfigured = ($rs['isLocalizationConfigured'] == 0 ? false : true);
                $this->_isAgreedToLicense      = ($rs['isAgreedToLicense'] == 0 ? false : true);
                $this->_accountActive          = ($rs['accountActive'] == 0 ? false : true);
                $this->_accountDeleted         = ($rs['accountDeleted'] == 0 ? false : true);
                $this->_email                  = $rs['email'];
                $this->_ip                     = $ip;
                $this->_userAgent              = $userAgent;
                $this->_timeZoneOffset         = $rs['timeZone'] - OFFSET_GMT;
                $this->_timeZone               = $rs['timeZone'];
                $this->_dateDMY                = ($rs['dateFormatDMY'] == 0 ? false : true);
                $this->_canSeeEEOInfo          = ($rs['canSeeEEOInfo'] == 0 ? false : true);
                $this->_pipelineEntriesPerPage = $rs['pipelineEntriesPerPage'];
                $this->_loggedInScript         = CATSUtility::getDirectoryName(); 

                /* SA's can always see EEO Info. */
                if ($this->_accessLevel >= ACCESS_LEVEL_SA)
                {
                    $this->_canSeeEEOInfo = true;
                }

                if ($rs['isDemo'] == '1' &&
                    $_SERVER['REMOTE_ADDR'] != '127.0.0.1' &&
                    ENABLE_DEMO_MODE && $rs['isDemoUser'] == 1)
                {
                    $this->_isDemo = true;
                    $this->_accessLevel = ACCESS_LEVEL_DEMO;
                }
                else
                {
                    $this->_isDemo = false;
                }

                /* Account inactive. */
                if ($this->_accountActive == 0)
                {
                    $this->_accessLevel = ACCESS_LEVEL_READ;
                }

                /* Account deleted. */
                if ($this->_accountDeleted == 1)
                {
                    $this->_accessLevel = ACCESS_LEVEL_DISABLED;
                }

                if (strlen($rs['columnPreferences']) > 0 && $this->_isDemo == false)
                {
                    $this->__dataGridColumnPreferences = unserialize($rs['columnPreferences']);
                }
                else
                {
                    $this->__dataGridColumnPreferences = array();
                }

                /* Log the login as successful. */
                if ($addToHistory)
                {
                    $userLoginID = $users->addLoginHistory(
                        $this->_userID,
                        $this->_siteID,
                        $this->_ip,
                        $this->_userAgent,
                        true
                    );
                }
                else
                {
                    $userLoginID = -1;
                }

                $this->_userLoginID = $userLoginID;
                $this->_isLoggedIn = true;

                if ($rs['lastViewedDayIsToday'] == 0)
                {
                    $sql = sprintf(
                        "UPDATE
                            site
                         SET
                            last_viewed_day = CURDATE(),
                            page_view_days = page_view_days + 1
                         WHERE
                            site_id = %s",
                        $this->_siteID
                    );
                    $rs = $db->query($sql);
                }

                $cookie = $this->getCookie();
                $sql = sprintf(
                    "UPDATE
                        user
                     SET
                        session_cookie = %s,
                        force_logout = 0
                     WHERE
                        user_id = %s
                     AND
                        site_id = %s",
                    $db->makeQueryString($cookie),
                    $this->_userID,
                    $this->_siteID
                );
                $rs = $db->query($sql);

                break;
        }
    }

    /**
     * Forces the session to make the current user "transparently" login to
     * another site. This is used only to support the CATS administrative
     * console, but must remain part of Session.
     *
     * @param integer New Site ID to login to.
     * @param integer User ID with which to login to the new site.
     * @param integer Site ID associated with $asUserID
     * @return void
     */
    public function transparentLogin($toSiteID, $asUserID, $asSiteID)
    {
         $db = DatabaseConnection::getInstance();

         $sql = sprintf(
            "SELECT
                user.user_id AS userID,
                user.user_name AS username,
                user.first_name AS firstName,
                user.last_name AS lastName,
                user.access_level AS accessLevel,
                user.site_id AS userSiteID,
                user.is_demo AS isDemoUser,
                user.email AS email,
                user.categories AS categories,
                site.name AS siteName,
                site.unix_name AS unixName,
                site.company_id AS companyID,
                site.is_demo AS isDemo,
                site.account_active AS accountActive,
                site.account_deleted AS accountDeleted,
                site.time_zone AS timeZone,
                site.date_format_ddmmyy AS dateFormatDMY,
                site.is_free AS isFree,
                site.is_hr_mode AS isHrMode
            FROM
                user
            LEFT JOIN site
                ON site.site_id = %s
            WHERE
                user.user_id = %s
                AND user.site_id = %s",
            $toSiteID,
            $asUserID,
            $asSiteID
        );
        $rs = $db->getAssoc($sql);

        $this->_username        = $rs['username'];
        $this->_userID          = $rs['userID'];
        $this->_siteID          = $toSiteID;
        $this->_firstName       = $rs['firstName'];
        $this->_lastName        = $rs['lastName'];
        $this->_siteName        = $rs['siteName'];
        $this->_unixName        = $rs['unixName'];
        $this->_accessLevel     = $rs['accessLevel'];
        $this->_realAccessLevel = $rs['accessLevel'];
        $this->_categories      = array();
        $this->_isASP           = ($rs['companyID'] != 0 ? true : false);
        $this->_siteCompanyID   = ($rs['companyID'] != 0 ? $rs['companyID'] : -1);
        $this->_isFree          = ($rs['isFree'] == 0 ? false : true);
        $this->_isHrMode        = ($rs['isHrMode'] != 0 ? true : false);
        $this->_accountActive   = ($rs['accountActive'] == 0 ? false : true);
        $this->_accountDeleted  = ($rs['accountDeleted'] == 0 ? false : true);
        $this->_email           = $rs['email'];
        $this->_timeZone        = $rs['timeZone'];
        $this->_dateDMY         = ($rs['dateFormatDMY'] == 0 ? false : true);
        $this->_isFirstTimeSetup = true;
        $this->_isAgreedToLicense = true;
        $this->_isLocalizationConfigured = true;


        /* Mark session as logged in. */
        $this->_isLoggedIn = true;

        /* Force a new MRU object to be created. */
        $this->_MRU = null;

        if (!eval(Hooks::get('TRANSPARENT_LOGIN_POST'))) return;

        $cookie = $this->getCookie();
        $sql = sprintf(
            "UPDATE
                user
             SET
                session_cookie = %s
             WHERE
                user_id = %s
             AND
                site_id = %s",
            $db->makeQueryString($cookie),
            $asUserID,
            $asSiteID
        );
       $db->query($sql);
    }

    /**
     * Returns the error message indicating why login attempt failed, or '' if
     * it didn't.
     *
     * @return string Login error message, or '' if none.
     */
    public function getLoginError()
    {
        return $this->_loginError;
    }

    /**
     * Starts the server response time timer.
     *
     * @return void
     */
    public function startTimer()
    {
        $this->_startTime = microtime();
    }

    /**
     * Returns the difference between now and the last time startTimer()
     * was called.
     *
     * @return string Execution time in seconds (ex: 0.59).
     */
    public function getExecutionTime()
    {
        $this->_endTime = microtime();

        if (!isset($this->_startTime) || empty($this->_startTime))
        {
            $this->_startTime = $this->_endTime;
        }

        list($a_dec, $a_sec) = explode(' ', $this->_startTime);
        list($b_dec, $b_sec) = explode(' ', $this->_endTime);

        $duration = $b_sec - $a_sec + $b_dec - $a_dec;
        $duration = sprintf('%0.2f', $duration);

        return $duration;
    }

    /**
     * Saves number of pipeline entries to be viewed per page for the current
     * user to session and the database.
     *
     * @param integer Number of pipeline entries to display per page.
     * @return void
     */
    public function setPipelineEntriesPerPage($entriesPerPage)
    {
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "UPDATE
                user
             SET
                pipeline_entries_per_page = %s
            WHERE
                user_id = %s
            AND
                site_id = %s",
            $entriesPerPage,
            $this->_userID,
            $this->_siteID
        );
        $rs = $db->query($sql);

        $this->_pipelineEntriesPerPage = $entriesPerPage;
    }

    /**
     * Stores a piece of data and returns an ID to retrieve the data with
     * retrieve(). These should NEVER store anything that if the user
     * could manipulate could cause security issues. Always validate anything
     * that is read from retrieve. Although the user is not given a direct
     * interface to read and store ID numbers, the user could potentially read
     * the contents of any ID number and submit an ID with associated text that
     * has not been validated.
     *
     * Practical example:  The browser sends a 300 kb file to CATS via AJAX.
     * CATS remembers the contents of the file and sends back an ID number (0).
     * Now the browser can refer to ID 0 (being the entire file).
     *
     * @param mixed data to store
     * @return integer stored data ID
     */
    public function storeData($data)
    {
        foreach ($this->_storedData as $index => $storedData)
        {
            if ($storedData === $data)
            {
                return $index;
            }
        }

        $newIndex = count($this->_storedData);
        $this->_storedData[$newIndex] = $data;

        return $newIndex;
    }

    /**
     * Retrieves data set by storeData(). Read above documentation for
     * storeData() for an overview of potential security issues.
     *
     * @param integer stored data ID
     * @return mixed stored data
     */
    public function retrieveData($id)
    {
        if (!isset($this->_storedData[$id]))
        {
            return null;
        }

        return $this->_storedData[$id];
    }

    /**
     * Stores a value in the session with a name assigned to it.
     *
     * @param string name
     * @param mixed value
     * @return void
     */
    public function storeValueByName($name, $value)
    {
        $this->_storedValues[$name] = $value;
    }

    /**
     * Stores a value stored by storeValueByName().
     *
     * @param string name
     * @return mixed value
     */
    public function retrieveValueByName($name)
    {
        if (!isset($this->_storedValues[$name]))
        {
            return null;
        }

        return $this->_storedValues[$name];
    }

    /**
     * Returns a column layout.  Only called by the datagrid class.
     * Column layouts are loaded into the session from the database when the user logs in.
     *
     * @return array column preferences
     */
    public function getColumnPreferences($instance)
    {
        if (isset($this->__dataGridColumnPreferences[$instance]))
        {
            return $this->__dataGridColumnPreferences[$instance];
        }
        else
        {
            return array();
        }
    }

    /**
     * Saves a column layout.  Only called by the datagrid class.
     *
     * @return void
     */
    public function setColumnPreferences($instance, $columnPreferences)
    {
        $this->__dataGridColumnPreferences[$instance] = $columnPreferences;

        $columnString = serialize($this->__dataGridColumnPreferences);

        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            'UPDATE
                user
             SET
                column_preferences = %s
             WHERE
                site_id = %s
             AND
                user.user_id = %s',
            $db->makeQueryString($columnString),
            $this->getSiteID(),
            $this->getUserID()
        );
        $rs = $db->query($sql);
    }

    /**
     * Retrieves the most recent parameters a datagrid was invoked with.
     * This allows for filter persistance.
     *
     * Called by UI function that is invoking the datagrid.
     *
     * @return array parameters
     */
    public function getDataGridParameters($instance)
    {
        if (isset($this->_dataGridColumnPreferences[md5($instance)]))
        {
            return $this->_dataGridColumnPreferences[md5($instance)];
        }
        else
        {
            return array();
        }
    }

    /**
     * Saves the current parameters a datagrid is invoked with.
     *
     * Called by datagrid class.
     *
     * @return void
     */
    public function setDataGridParameters($instance, $parameters)
    {
        $this->_dataGridColumnPreferences[md5($instance)] = $parameters;
    }
}

?>
