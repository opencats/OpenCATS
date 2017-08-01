<?php
/**
 * CATS
 * Users Library
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
 * @version    $Id: Users.php 3593 2007-11-13 17:36:57Z andrew $
 */

include_once('./lib/License.php');

if (AUTH_MODE == "ldap" || AUTH_MODE == "sql+ldap") 
{
    require_once('./lib/LDAP.php');
}

/* Login status flags. */
define('LOGIN_SUCCESS',               1);
define('LOGIN_INVALID_USER',         -1);
define('LOGIN_INVALID_PASSWORD',     -2);
define('LOGIN_DISABLED',             -3);
define('LOGIN_CANT_CHANGE_PASSWORD', -4);
define('LOGIN_ROOT_ONLY',            -5);
define('LOGIN_PENDING_APPROVAL',     -6);

/* Add User status flags. */
define('ADD_USER_SUCCESS',            1);
define('ADD_USER_BAD_PASS',          -1);
define('ADD_USER_EXISTS',            -2);
define('ADD_USER_DB_ERROR',          -3);

/* Password for user authenticated against LDAP */ 
define('LDAPUSER_PASSWORD',          '_LDAPUSER_');

/**
 *	Users Library
 *	@package    CATS
 *	@subpackage Library
 */
class Users
{
    private $_db;
    private $_siteID;
    private $_ldap;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }


    /**
     * Adds a user to the database.
     *
     * @param string last name
     * @param string first name
     * @param string e-mail address
     * @param string username
     * @param string password
     * @param flag access level
     * @param bool eeo information visible (false)
     * @return new user ID, or -1 on failure.
     */
    public function add($lastName, $firstName, $email, $username, $password,
            $accessLevel, $eeoIsVisible = false, $userSiteID = -1)
    {

        $md5pwd = $password == LDAPUSER_PASSWORD ? $password : md5($password);
        $userSiteID = $userSiteID < 0 ? $this->_siteID : $userSiteID;
        $sql = sprintf(
                "INSERT INTO user (
            user_name,
        password,
        access_level,
        can_change_password,
        is_test_user,
        email,
        first_name,
        last_name,
        site_id,
        can_see_eeo_info
            )
                VALUES (
                    %s,
                    %s,
                    %s,
                    1,
                    0,
                    %s,
                    %s,
                    %s,
                    %s,
                    %s
                    )",
        $this->_db->makeQueryString($username),
        $this->_db->makeQueryString($md5pwd),
        $this->_db->makeQueryInteger($accessLevel),
        $this->_db->makeQueryString($email),
        $this->_db->makeQueryString($firstName),
        $this->_db->makeQueryString($lastName),
        $userSiteID,
        ($eeoIsVisible ? 1 : 0)
            );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        return $this->_db->getLastInsertID();
    }

    /**
     * Updates a user in the database.
     *
     * @param string last name
     * @param string first name
     * @param string e-mail address
     * @param string username
     * @param flag access level
     * @param bool eeo information visible (false)
     * @return boolean True if successful; false otherwise.
     */
    public function update($userID, $lastName, $firstName, $email,
            $username, $accessLevel = -1, $eeoIsVisible = false)
    {
        /* If an access level was specified, make sure the access level is
         * updated by the query.
         */
        if ($accessLevel != -1)
        {
            $accessLevelSQL = sprintf(
                    ", access_level = %s",
                    $this->_db->makeQueryInteger($accessLevel)
                    );
        }
        else
        {
            $accessLevelSQL = '';
        }

        $sql = sprintf(
                "UPDATE
                user
                SET
                last_name        = %s,
                first_name       = %s,
                email            = %s,
                user_name        = %s,
                can_see_eeo_info = %s
                %s
                WHERE
                user_id = %s
                AND
                site_id = %s",
                $this->_db->makeQueryString($lastName),
                $this->_db->makeQueryString($firstName),
                $this->_db->makeQueryString($email),
                $this->_db->makeQueryString($username),
                ($eeoIsVisible ? 1 : 0),
                $accessLevelSQL,
                $this->_db->makeQueryInteger($userID),
                $this->_siteID
                    );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Updates the current user in the database.
     *
     * @param integer user ID
     * @param string e-mail address
     * @return boolean True if successful; false otherwise.
     */
    public function updateSelfEmail($userID, $email)
    {
        $sql = sprintf(
                "UPDATE
                user
                SET
                email = %s
                WHERE
                user_id = %s
                AND
                site_id = %s",
                $this->_db->makeQueryString($email),
                $this->_db->makeQueryInteger($userID),
                $this->_siteID
                );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Update the categories field of the user table for the specified user.
     *
     * @param integer ID of the user
     * @param string The new categories
     * @return boolean true on sucess
     */
    public function updateCategories($userID, $categories)
    {
        $sql = sprintf(
                "UPDATE
                user
                SET
                categories = %s
                WHERE
                user_id = %s
                AND
                site_id = %s",
                $this->_db->makeQueryString($categories),
                $this->_db->makeQueryInteger($userID),
                $this->_siteID
                );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Removes a user from the system.
     * NOTE: Associated records are not deleted! THIS WILL BREAK THINGS!
     * This is only here for use by the CATS Automated Testing Framework,
     * which will clean up after itself before calling this.
     *
     * @param integer user ID
     * @return void
     */
    public function delete($userID)
    {
        /* Delete the user. */
        $sql = sprintf(
                "DELETE FROM
                user
                WHERE
                user_id = %s
                AND
                site_id = %s",
                $this->_db->makeQueryInteger($userID),
                $this->_siteID
                );
        $this->_db->query($sql);
    }

    /**
     * Returns one user.
     *
     * @param integer user ID
     * @return array user data
     */
    public function get($userID)
    {
        $sql = sprintf(
                "SELECT
                user.user_name AS username,
                user.access_level AS accessLevel,
                access_level.short_description AS accessLevelDescription,
                access_level.long_description AS accessLevelLongDescription,
                user.first_name AS firstName,
                user.last_name AS lastName,
                CONCAT(
                    user.first_name, ' ', user.last_name
                    ) AS fullName,
                user.email AS email,
                user.company as company,
                user.city as city,
                user.state as state,
                user.zip_code as zipCode,
                user.country as country,
                user.address as address,
                user.phone_work as phoneWork,
                user.user_id AS userID,
                user.password AS password,
                user.categories AS categories,
                user.session_cookie AS sessionCookie,
                user.can_see_eeo_info AS canSeeEEOInfo,
                DATE_FORMAT(
                        MAX(
                            IF(user_login.successful = 1, user_login.date, NULL)
                           ),
                        '%%m-%%d-%%y (%%h:%%i %%p)'
                        ) AS successfulDate,
                DATE_FORMAT(
                        MAX(
                            IF(user_login.successful = 0, user_login.date, NULL)
                           ),
                        '%%m-%%d-%%y (%%h:%%i %%p)'
                        ) AS unsuccessfulDate,
                force_logout as forceLogout
                    FROM
                    user
                    LEFT JOIN access_level
                    ON user.access_level = access_level.access_level_id
                    LEFT JOIN user_login
                    ON user.user_id = user_login.user_id
                    WHERE
                    user.site_id = %s
                    AND
                    user.user_id = %s
                    GROUP BY
                    user.user_id",
                $this->_siteID,
                $this->_db->makeQueryInteger($userID)
                    );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Returns 1 user, ignoring the user's Site ID.
     *
     * @return array user data
     */
    public function getForAdministration($userID, $aspSiteRule)
    {
        $sql = sprintf("SELECT
                user.user_name AS username,
                user.access_level AS accessLevel,
                access_level.short_description AS accessLevelDescription,
                access_level.long_description AS accessLevelLongDescription,
                user.first_name AS firstName,
                user.last_name AS lastName,
                CONCAT(
                    user.first_name, ' ', user.last_name
                    ) AS fullName,
                user.email AS email,
                user.title AS title,
                user.address AS address,
                user.company as company,
                user.city as city,
                user.state as state,
                user.zip_code as zipCode,
                user.country as country,
                user.phone_work AS phoneWork,
                user.phone_other AS phoneOther,
                user.phone_cell AS phoneCell,
                user.notes AS notes,
                user.user_id AS userID,
                user.password AS password,
                user.categories AS categories,
                user.session_cookie AS sessionCookie,
                user.site_id AS siteID,
                user.can_see_eeo_info AS canSeeEEOInfo,
                site.name AS siteName,
                DATE_FORMAT(
                        MAX(
                            IF(user_login.successful = 1, user_login.date, NULL)
                           ),
                        '%%m-%%d-%%y (%%h:%%i %%p)'
                        ) AS successfulDate,
                DATE_FORMAT(
                        MAX(
                            IF(user_login.successful = 0, user_login.date, NULL)
                           ),
                        '%%m-%%d-%%y (%%h:%%i %%p)'
                        ) AS unsuccessfulDate,
                force_logout as forceLogout
                    FROM
                    user
                    LEFT JOIN access_level
                    ON user.access_level = access_level.access_level_id
                    LEFT JOIN user_login
                    ON user.user_id = user_login.user_id
                    LEFT JOIN site
                    ON user.site_id = site.site_id
                    WHERE
                    %s
                    AND
                    user.user_id = %s
                    GROUP BY
                    user.user_id",
                $aspSiteRule,
                $this->_db->makeQueryInteger($userID)
                    );

        return $this->_db->getAssoc($sql);
    }

    /**
     * TODO: DOCUMENT ME.
     */
    public function updateForAdministration($userID, $firstName, $lastName, $email,
            $title, $phone_work, $phone_cell, $phone_other, $notes, $aspSiteRule)
    {
        $sql = sprintf(
                "UPDATE
                user
                SET
                last_name    = %s,
                first_name   = %s,
                email        = %s,
                title        = %s,
                phone_work   = %s,
                phone_cell   = %s,
                phone_other  = %s,
                notes        = %s
                WHERE
                user_id = %s
                AND
                %s",
                $this->_db->makeQueryString($lastName),
                $this->_db->makeQueryString($firstName),
                $this->_db->makeQueryString($email),
                $this->_db->makeQueryString($title),
                $this->_db->makeQueryString($phone_work),
                $this->_db->makeQueryString($phone_cell),
                $this->_db->makeQueryString($phone_other),
                $this->_db->makeQueryString($notes),
                $this->_db->makeQueryInteger($userID),
                $this->_siteID,
                $aspSiteRule
                    );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Returns information pertaining to forced logouts for a user.
     *
     * @param integer user ID
     * @return array user data
     */
    public function getForceLogoutData($userID)
    {
        $sql = sprintf(
                "SELECT
                user.access_level AS accessLevel,
                force_logout as forceLogout
                FROM
                user
                WHERE
                user.site_id = %s
                AND
                user.user_id = %s",
                $this->_siteID,
                $this->_db->makeQueryInteger($userID)
                );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Returns all users.
     *
     * @return array users data
     */
    public function getAll()
    {
        $sql = sprintf(
                "SELECT
                user.user_name AS username,
                user.password AS password,
                user.access_level AS accessLevel,
                access_level.short_description AS accessLevelDescription,
                user.first_name AS firstName,
                user.last_name AS lastName,
                user.email AS email,
                user.company as company,
                user.city as city,
                user.state as state,
                user.zip_code as zipCode,
                user.country as country,
                user.address as address,
                user.phone_work as phoneWork,
                user.user_id AS userID,
                DATE_FORMAT(
                    MAX(
                        IF(user_login.successful = 1, user_login.date, NULL)
                       ),
                    '%%m-%%d-%%y (%%h:%%i %%p)'
                    ) AS successfulDate,
                DATE_FORMAT(
                        MAX(
                            IF(user_login.successful = 0, user_login.date, NULL)
                           ),
                        '%%m-%%d-%%y (%%h:%%i %%p)'
                        ) AS unsuccessfulDate
                    FROM
                    user
                    LEFT JOIN access_level
                    ON user.access_level = access_level.access_level_id
                    LEFT JOIN user_login
                    ON user.user_id = user_login.user_id
                    WHERE
                    user.site_id = %s
                    GROUP BY
                    user.user_id
                    ORDER BY
                    user.access_level DESC,
                user.last_name ASC,
                user.first_name ASC",
                $this->_siteID
                    );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Looks up and returns a user's ID by username. If no matching username is
     * found, false is returned.
     *
     * @param string username
     * @return integer user id or boolean false
     */
    public function getIDByUsername($username)
    {
        $sql = sprintf(
                "SELECT
                user_id AS userID
                FROM
                user
                WHERE
                user_name = %s
                AND
                site_id = %s",
                $this->_db->makeQueryString($username),
                $this->_siteID
                );
        $data = $this->_db->getAssoc($sql);

        if (empty($data))
        {
            return false;
        }

        return (int) $data['userID'];
    }

    /**
     * Returns a record set of access levels
     *
     * @return array access levels data
     */
    public function getAccessLevels()
    {
        $sql = sprintf(
                "SELECT
                access_level.access_level_id AS accessID,
                access_level.short_description AS shortDescription,
                access_level.long_description AS longDescription
                FROM
                access_level
                ORDER BY
                access_level.access_level_id ASC"
                );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns $limit most recent attempted logins for the specified user ID.
     *
     * @param integer user ID
     * @param integer entries to return
     * @return array access levels data
     */
    public function getLastLoginAttempts($userID, $limit)
    {
        $sql = sprintf(
                "SELECT
                user_login.ip AS ip,
                user_login.user_agent AS userAgent,
                user_login.date AS date,
                user_login.host AS hostname,
                user_login.successful AS successful
                FROM
                user_login
                WHERE
                user_login.user_id = %s
                ORDER BY
                user_login.date DESC
                LIMIT
                %s",
                $userID,
                $this->_db->makeQueryInteger($limit)
                );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns a minimal record set of all users (for use when creating
     * drop-down lists of users, etc.).
     *
     * @return array users data
     */
    public function getSelectList()
    {
        $sql = sprintf(
                "SELECT
                user.user_name AS username,
                user.first_name AS firstName,
                user.last_name AS lastName,
                user.user_id AS userID,
                user.categories AS categories
                FROM
                user
                WHERE
                user.site_id = %s
                AND
                user.access_level > %s
                ORDER BY
                user.last_name ASC,
                user.first_name ASC",
                $this->_siteID,
                ACCESS_LEVEL_DISABLED
                );

        if (!eval(Hooks::get('USERS_GET_SELECT_SQL'))) return;

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Changes a user's password to the password specified.
     *
     * @param integer user ID
     * @param string current password
     * @param string new password
     * @return flag status
     */
    public function changePassword($userID, $currentPassword, $newPassword)
    {
        if( $this->isUserLDAP($userID))
        {
            /* LDAP user not allowed to change password */
            return LOGIN_CANT_CHANGE_PASSWORD;
        }

        $sql = sprintf(
                "SELECT
                user.password AS password,
                user.access_level AS accessLevel,
                user.can_change_password AS canChangePassword
                FROM
                user
                WHERE
                user.user_id = %s",
                $this->_db->makeQueryInteger($userID)
                );
        $rs = $this->_db->getAssoc($sql);

        /* No results? Shouldn't happen, but it could if the user just got
         * deleted or something.
         */
        if (!$rs || $this->_db->isEOF())
        {
            return LOGIN_INVALID_USER;
        }

        /* Is the user's supplied password correct? */
        if ($rs['password'] !== md5($currentPassword))
        {
            return LOGIN_INVALID_PASSWORD;
        }

        /* Is the user's account disabled? */
        if ($rs['accessLevel'] <= ACCESS_LEVEL_DISABLED)
        {
            return LOGIN_DISABLED;
        }

        /* Is the user allowed to change his/her password? */
        if ($rs['canChangePassword'] != '1')
        {
            return LOGIN_CANT_CHANGE_PASSWORD;
        }

        /* Change the user's password. */
        $sql = sprintf(
                "UPDATE
                user
                SET
                password = md5(%s)
                WHERE
                user.user_id = %s",
                $this->_db->makeQueryString($newPassword),
                $this->_db->makeQueryInteger($userID)
                );
        $this->_db->query($sql);
        // FIXME: Did the above query succeed? If not, fail.

        return LOGIN_SUCCESS;
    }

    /**
     * Resets a user's password to the password specified.
     *
     * @param integer user ID
     * @param string current password
     * @param string new password
     * @return flag status
     */
    public function resetPassword($userID, $newPassword)
    {
        if( $this->isUserLDAP($userID))
        {
            /* LDAP user not allowed to reset password */
            return false;
        }

        $sql = sprintf(
                "SELECT
                user.password AS password,
                user.access_level AS accessLevel,
                user.can_change_password AS canChangePassword
                FROM
                user
                WHERE
                user.user_id = %s",
                $this->_db->makeQueryInteger($userID)
                );
        $rs = $this->_db->getAssoc($sql);

        /* No results? Shouldn't happen, but it could if the user just got
         * deleted or something.
         */
        if (empty($rs))
        {
            return false;
        }

        /* Change the user's password. */
        $sql = sprintf(
                "UPDATE
                user
                SET
                password = md5(%s)
                WHERE
                user.user_id = %s",
                $this->_db->makeQueryString($newPassword),
                $this->_db->makeQueryInteger($userID)
                );
        $this->_db->query($sql);
        // FIXME: Did the above query succeed? If not, fail.

        return true;
    }

    /**
     * Returns a login status flag indicating whether or not the specified
     * password is correct for the specified user and whether or not the
     * account is enabled or disabled.
     *
     *   LOGIN_INVALID_USER     - Invalid username.
     *   LOGIN_INVALID_PASSWORD - Invalid password.
     *   LOGIN_DISABLED         - Account is disabled.
     *   LOGIN_SUCCESS          - Password is valid and account is enabled.
     *   LOGIN_PENDING_APPROVAL - Account is new but disabled and needs to be approved by SA or root.
     *
     * @param string username
     * @param string password
     * @return flag status
     */
    public function isCorrectLogin($username, $password)
    {
        $existsInLDAP = false;
        $existsInDB = false;
        
        if (empty($username))
        {
            return LOGIN_INVALID_USER;
        }

        if (empty($password))
        {
            return LOGIN_INVALID_PASSWORD;
        }

        $sql = sprintf(
                "SELECT
                user.user_name AS username,
                user.password AS password,
                user.access_level AS accessLevel
                FROM
                user
                WHERE
                user.user_name = %s",
                $this->_db->makeQueryString($username)
                );
        $rs = $this->_db->getAssoc($sql);

        /* No results? Invalid user or new LDAP user. */
        if(!$rs || $this->_db->isEOF())
        {
            if(AUTH_MODE == 'sql')
            {
                return LOGIN_INVALID_USER;
            }
        } 
        else 
        {
            $existsInDB = true;
        }

        if((AUTH_MODE == 'ldap' || AUTH_MODE == 'sql+ldap')
            && (($existsInDB && $rs['password'] == LDAPUSER_PASSWORD) || !$existsInDB) ) {
            $this->_ldap = LDAP::getInstance($username, $password);
            if($this->_ldap == NULL)
            {
                return LOGIN_INVALID_USER;
            }
            if(!$this->_ldap->authenticate($username, $password)) 
            {
                return LOGIN_INVALID_PASSWORD;
            } 
            $existsInLDAP = true;
        } else if(AUTH_MODE == 'ldap'){
            /*  incorrect LDAP user in db */
            return LOGIN_INVALID_USER;
        } else {
            /* Is the user's supplied password correct? */
            if ($rs['password'] !== md5($password))
            {
                return LOGIN_INVALID_PASSWORD;
            }
        }
        
        if (!$existsInDB && $existsInLDAP) {
            /* ldap user not created in local db -> create one as disabled */
            $userInfo = $this->_ldap->getUserInfo($username);
            $userID = $this->add($userInfo[0], $userInfo[1], $userInfo[2], $userInfo[3], LDAPUSER_PASSWORD, '0', false, LDAP_SITEID);            
            return LOGIN_PENDING_APPROVAL;
        }

        /* Is the user's account disabled? */
        if ($rs['accessLevel'] <= ACCESS_LEVEL_DISABLED)
        {
            return LOGIN_DISABLED;
        }

        /* If in slave mode, only allow root login. */
        if (CATS_SLAVE && $rs['accessLevel'] < ACCESS_LEVEL_ROOT)
        {
            return LOGIN_ROOT_ONLY;
        }

        return LOGIN_SUCCESS;
    }

    /**
     * Returns an array of license data
     *
     * @return array
     */
    public function getLicenseData()
    {
        $sql = sprintf(
                "SELECT
                COUNT(user.site_id) AS totalUsers,
                user.access_level,
                site.user_licenses AS userLicenses
                FROM
                user
                LEFT JOIN site
                ON user.site_id = site.site_id
                WHERE
                user.site_id = %s
                AND
                user.access_level > %s
                GROUP BY
                user.site_id",
                $this->_siteID,
                ACCESS_LEVEL_READ
                );
        $license = $this->_db->getAssoc($sql);

        if (empty($license))
        {
            $license['totalUsers'] = 0;
            $license['userLicenses'] = 0;
        }

        $license['diff'] = $license['userLicenses'] - $license['totalUsers'];
        $license['unlimited'] = 0;
        $license['canAdd'] = 0;

        if ($license['userLicenses'] == 0)
        {
            $license['unlimited'] = 1;
            $license['canAdd'] = 1;
        }
        else if ($license['diff'] > 0)
        {
            $license['canAdd'] = 1;
        }

        return $license;
    }

    /**
     * Returns true if a user by the specified username already exists; false
     * otherwise.
     *
     * @return boolean user exists
     */
    public function usernameExists($username)
    {
        // FIXME: COUNT() not needed.
        $sql = sprintf(
                "SELECT
                COUNT(user.user_name) AS userExists
                FROM
                user
                WHERE
                user.user_name = %s",
                $this->_db->makeQueryString($username)
                );
        $rs = $this->_db->getAssoc($sql);

        if (!empty($rs) || $rs['userExists'] <= 0)
        {
            return false;
        }

        return true;
    }

    /**
     * Creates a login history entry.
     *
     * @param integer User's User ID.
     * @param integer User's Site ID
     * @param string User's IP address.
     * @param string User's browser user agent string.
     * @param boolean Was the login successful?
     * @return integer This login's User Login ID.
     */
    public function addLoginHistory($userID, $siteID, $ip, $userAgent,
            $wasSuccessful)
    {
        if (ENABLE_HOSTNAME_LOOKUP)
        {
            $hostname = @gethostbyaddr($ip);
        }
        else
        {
            $hostname = $ip;
        }

        $sql = sprintf(
                "INSERT INTO user_login (
            user_id,
            site_id,
            ip,
            user_agent,
            host,
            date,
            successful,
            date_refreshed
                )
                VALUES (
                    %s,
                    %s,
                    %s,
                    %s,
                    %s,
                    NOW(),
                    %s,
                    NOW()
                    )",
            $userID,
            $siteID,
            $this->_db->makeQueryString($ip),
            $this->_db->makeQueryString($userAgent),
            $this->_db->makeQueryString($hostname),
            ($wasSuccessful ? '1' : '0')
                );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        return $this->_db->getLastInsertID();
    }

    /**
     * Updates the user_login table to indicate the user if performing
     * activity. The site name column "page_views" is also updated as a
     * summary column for later logging.
     *
     * @param integer ID for the user login
     * @param integer ID for the site
     */
    public function updateLastRefresh($userLoginID, $siteID)
    {
        $sql = sprintf(
                "UPDATE
                user_login
                SET
                date_refreshed = NOW()
                WHERE
                user_login_id = %s
                AND
                site_id = %s",
                $this->_db->makeQueryInteger($userLoginID),
                $this->_db->makeQueryInteger($siteID)
                );

        $this->_db->query($sql);

        // FIXME: Don't hit "site" on each request. Lets make a new table.
        $sql = sprintf(
                "UPDATE
                site
                SET
                page_views = page_views + 1
                WHERE
                site_id = %s",
                $this->_db->makeQueryInteger($siteID)
                );

        $this->_db->query($sql);
    }

    /**
     * Get information about users that have performed an action
     * in the last 20 seconds. For all intents and purposes, all
     * active users.
     *
     * @return array
     */
    public function getLoadData()
    {
        // FIXME: This definatly has to need some optimization.
        $sql = sprintf(
                "SELECT
                COUNT(DISTINCT user_login.user_id) AS loggedInUsers,
                COUNT(
                    DISTINCT IF(
                        date_refreshed > DATE_SUB(NOW(), INTERVAL 20 SECOND),
                        user_login.user_id,
                        NULL
                        )
                    ) AS activeUsers
                FROM
                user_login
                WHERE
                date_refreshed > DATE_SUB(NOW(), INTERVAL 10 MINUTE)"
                );

        $rs = $this->_db->getAssoc($sql);

        if (!isset($rs['loggedInUsers']) || empty($rs['loggedInUsers']))
        {
            $rs['loggedInUsers'] = 0;
        }

        if (!isset($rs['activeUsers']) || empty($rs['activeUsers']))
        {
            $rs['activeUsers'] = 0;
        }

        return $rs;
    }

    /**
     * Get information about all users that have logged into CATS
     * and performed an action in the last 14 days.
     *
     * @return array
     */
    public function getUsageData()
    {
        // FIXME: This definatly has to need some optimization.
        $sql = sprintf(
                "SELECT
                COUNT(DISTINCT user_login.user_id) AS inUseUsers
                FROM
                user_login
                WHERE
                date_refreshed > DATE_SUB(NOW(), INTERVAL 14 DAY)"
                );

        $rs = $this->_db->getAssoc($sql);

        if (!isset($rs['inUseUsers']) || empty($rs['inUseUsers']))
        {
            $rs['inUseUsers'] = 0;
        }

        return $rs['inUseUsers'];
    }

    /**
     * Get a list of all active users for a given site with an optional
     * imposed limit.
     *
     * @param integer Limit the results to this number of users
     * @param integer ID of the site making the request.
     * @return array
     */
    public function getLoggedInUsers($limit, $siteID)
    {
        if ($limit > 0)
        {
            $limitSQL = sprintf(
                    'LIMIT %s', $this->_db->makeQueryInteger($limit)
                    );
        }
        else
        {
            $limitSQL = '';
        }

        if ($siteID > 0)
        {
            $siteCriterion = sprintf(
                    'AND user.site_id = %s', $this->_db->makeQueryInteger($siteID)
                    );
        }
        else
        {
            $siteCriterion = '';
        }

        $sql = sprintf(
                "SELECT
                MAX(user_login.user_login_id) AS userLoginID,
                user.user_id AS userID,
                user.site_id AS siteID,
                user.first_name AS firstName,
                user.last_name AS lastName,
                site.name AS siteName,
                DATE_FORMAT(
                    user_login.date_refreshed, '%%h:%%i %%p'
                    ) AS lastRefresh,
                IF(
                    user_login.date_refreshed > DATE_SUB(NOW(), INTERVAL 20 SECOND),
                    1,
                    0
                  ) AS active
                FROM
                user
                LEFT JOIN user_login
                ON user_login.user_id = user.user_id
                LEFT JOIN site
                ON site.site_id = user.site_id
                WHERE
                user_login.date_refreshed > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
                %s
                GROUP BY
                user_login.user_login_id
                ORDER BY
                user_login.date_refreshed DESC
                %s",
            $siteCriterion,
            $limitSQL
                );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns the user ID of the automated user.
     *
     * @param integer user ID
     * @return array user data
     */
    public function getAutomatedUser()
    {
        $sql = sprintf(
                "SELECT
                user.user_id AS userID
                FROM
                user
                WHERE
                user.site_id = %s
                AND
                user.user_name = 'cats@rootadmin'",
                CATS_ADMIN_SITE
                );
        $rs = $this->_db->getAssoc($sql);

        if (!isset($rs['userID']))
        {
            $sql = sprintf(
                    "INSERT INTO user (
                user_name,
                password,
                access_level,
                can_change_password,
                is_test_user,
                email,
                first_name,
                last_name,
                site_id
                    )
                    VALUES (
                        'cats@rootadmin',
                        '',
                        0,
                        0,
                        0,
                        '',
                        'CATS',
                        'Automated',
                        %s
                        )",
                CATS_ADMIN_SITE
                    );
            $this->_db->query($sql);

            $rs = $this->getAutomatedUser();
        }

        return $rs;
    }

    public function isUserLDAP($userID)
    {
        $sql = sprintf(
                "SELECT
                user.password AS password
                FROM
                user
                WHERE
                user.user_id = %s",
                $this->_db->makeQueryString($userID)
                );
        $rs = $this->_db->getAssoc($sql);
        
        return ($rs['password'] == LDAPUSER_PASSWORD);
    }
    
}

?>
