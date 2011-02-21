<?php
/**
 * CATS
 * User/Site Profile Library
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
 * @version    $Id: Profile.php 3831 2007-12-11 23:14:32Z brian $
 */

include_once('./lib/DatabaseConnection.php');
include_once('./lib/Site.php');

class Profile
{
    private $_siteID;
    private $_db;
    private $_savedProfileID;
    private $_savedProfile;
    private $_titleCache;

    public function __construct($siteID, $profileID = false)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        $this->_savedProfileID = $profileID;
        $this->_savedProfile = false;
        $this->_titleCache = false;
    }

    /**
     * Get the internal profile ID which is saved on the get() call.
     *
     * @return mixed Integer ID of the internal profile or false if there is none
     */
    public function getProfileID()
    {
        return $this->_savedProfileID;
    }

    public function getProfile()
    {
        if ($this->_savedProfileID !== false && $this->_savedProfile !== false)
        {
            return $this->_savedProfile['profile'];
        }
        else
        {
            return false;
        }
    }

    public function getProfileStylesheet()
    {
        if ($this->_savedProfileID !== false && $this->_savedProfile !== false)
        {
            return sprintf('./profile/%s/style.css', $this->_savedProfile['profile']);
        }
        else
        {
            return false;
        }
    }

    /**
     * Set the internal profile ID variable.
     *
     * @param integer ID from the profile table
     * @return mixed The new value of the internal variable
     */
    public function setProfileID($profileID = false)
    {
        return ($this->_savedProfileID = $profileID);
    }

    /**
     * Load all profile titles into memory so they aren't queried one-by-one on a
     * page load.
     *
     * @param integer ID from the profile table or false to use the internal profile id
     * @return boolean true on successfully cache, false on failure
     */
    public function cacheTitles($profileID = false)
    {
        if (!($profileID = ($profileID !== false ? $profileID : $this->_savedProfileID)))
        {
            return false;
        }

        $this->setProfileID($profileID);
        $this->_titleCache = $this->getAllTitles($profileID);

        return true;
    }

    /**********************************************************************************************
     *
     * THE FOLLOWING FUNCTIONS DEAL PRIMARILY WITH THE PROFILE TABLE
     *
     *********************************************************************************************/

    /**
     * Base select-area SQL from a get*() method to prevent data duplication between
     * similar get() methods.
     *
     * @return string extendible SQL statement
     */
    public function getBaseSQL()
    {
        $sql =
            "SELECT
                profile.profile_id as profileID,
                profile.profile as profile,
                profile.title,
                profile.active as isActive,
                profile.date_created as dateCreated,
                profile.date_modified as dateModified";

        return $sql;
    }

    /**
     * Returns an associative array describing a site-wide or user-specific profile. If a user
     * has no set profile, the site profile will be returned. If there is no site profile,
     * a default site profile will be created. If a default profile is unable to be created,
     * boolean false is returned.
     *
     * @param integer profileID ID from the profile table or false to return the active profile
     * @param integer userID ID from the user table or false to return site-wide profile
     * @param boolean true to cache titles for faster lookup, false for faster return/no cacheing
     *
     * @return mixed array on success, false on failure
     */
    public function get($profileID = false, $userID = false, $cacheTitles = true)
    {
        if ($userID !== false)
        {
            $critereon1 =
                "RIGHT JOIN user
                    ON user.profile_id = profile.profile_id";
            $critereon2 = sprintf(
                "AND
                    user.user_id = %s",
                $this->_db->makeQueryInteger($userID)
            );
        }
        else
        {
            $critereon1 =
                "RIGHT JOIN site
                    ON (site.site_id = profile.site_id AND site.profile_id = profile.profile_id)";
            $critereon2 = '';
        }

        if ($profileID !== false)
        {
            $profileCritereon = sprintf(
                "AND
                    profile.profile_id = %s",
                $this->_db->makeQueryInteger($profileID)
            );
        }
        else
        {
            $profileCritereon = sprintf(
                "AND
                    profile.active = 1"
            );
        }

        $sql = sprintf(
            "%s
            FROM
                profile
            %s
            WHERE
                profile.site_id = %d
            %s
            %s",
            $this->getBaseSQL(),
            $critereon1,
            $this->_siteID,
            $critereon2,
            $profileCritereon
        );

        $rs = $this->_db->getAssoc($sql);

        // Provided a user_id but found no profile, try getting the site profile
        if (empty($rs) && $userID !== false && $profileID === false)
        {
            return $this->get(false, false);
        }

        // No profile exists for site or provided profile_id
        if (empty($rs))
        {
            // Make an empty profile and attach it to the site
            $id = $this->add('Default Profile', true);
            if ($id !== false)
            {
                $site = new Site($this->_siteID);
                if ($site->setProfile($id) !== false)
                {
                    return $this->get(false, false);
                }
            }
        }

        // Save the profile ID to an internal variable for later use (if requested)
        if (!empty($rs))
        {
            $this->_savedProfileID = $rs['profileID'];
            $this->_savedProfile = $rs;

            if ($cacheTitles)
            {
                $this->cacheTitles();
            }

            return $rs;
        }

        return false;
    }

    /**
     * Get all profiles for a site with the users that own them (if available)
     *
     */
    public function getAll()
    {
        $sql = sprintf(
            "%s
                user.user_id as userID,
                user.user_name as userName,
                user.access_level as accessLevel,
                user.first_name as firstName,
                user.last_name as lastName,
                user.email as email,
                user.title as title
            FROM
                profile
            LEFT JOIN
                user
            ON
                user.profile_id = profile.profile_id
            WHERE
                profile.site_id = %d",
            $this->getBaseSQL(),
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Get all user and site-public profiles (not attached to other users).
     *
     */
    public function getAllUser($userID)
    {
        $sql = sprintf(
            "%s
            FROM
                profile
            WHERE
                profile.site_id = %d
            AND
                profile.profile_id NOT IN (
                    SELECT
                        user.profile_id
                    FROM
                        user
                    WHERE
                        user.site_id = %d
                    AND
                        user.user_id != %s
                )",
            $this->getBaseSQL(),
            $this->_siteID,
            $this->_siteID,
            $this->_db->makeQueryInteger($userID)
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Add a profile for a user or site with optional default (active).
     *
     * @param string Title to identify the profile
     * @param boolean true for an enabled, usable profile. false to disable
     */
    public function add($title, $active = true, $profile = 'Default')
    {
        $sql = sprintf(
            "INSERT INTO
                profile (site_id, title, active, date_created, date_modified, profile)
             VALUES
                (%d, %s, %s, NOW(), NOW(), %s)",
            $this->_siteID,
            $this->_db->makeQueryString($title),
            $active ? '1' : '0',
            $this->_db->makeQueryString($profile)
        );

        // Error handling on, primary key is auto_increment
        $rs = $this->_db->query($sql);

        if (!$rs || $this->_db->getAffectedRows() <= 0)
        {
            return false;
        }

        return $this->_db->getLastInsertID();
    }

    /**
     * Update a profile.
     *
     * @param integer ID from the profile table or false to use current ID
     * @param string New title for the profile
     * @param boolean Is this profile active?
     * @return boolean true on success, false on failure
     */
    public function update($profileID, $title, $active = true, $profile = 'Default')
    {
        if (!($profileID = ($profileID !== false ? $profileID : $this->_savedProfileID)))
        {
            return false;
        }

        $sql = sprintf(
            "UPDATE
                profile
            SET
                title = %s,
                active = %s,
                date_modified = NOW(),
                profile = %s
            WHERE
                profile.site_id = %s
            AND
                profile.profile_id = %s",
            $this->_siteID,
            $this->_db->makeQueryInteger($profileID),
            $this->_db->makeQueryString($profile)
        );

        $this->_db->query($sql);
        return ($this->_db->getAffectedRows() > 0) ? true : false;
    }

    /**
     * Deletes a profile and all it's pages, titles and fields associated.
     *
     * @param integer ID from the profile table or false to use current ID
     * @return mixed true on success, false on failure (or if profile doesn't exist)
     */
    public function delete($profileID = false)
    {
        if (!($profileID = ($profileID !== false ? $profileID : $this->_savedProfileID)))
        {
            return false;
        }

        // Begin transaction (if one delete fails, roll everything back, I <3 InnoDB)
        $inTransaction = $this->_db->beginTransaction();

        // Delete base profile
        $sql = sprintf(
            "DELETE FROM
                profile
             WHERE
                profile_id = %s
             AND
                site_id = %d",
            $this->_db->makeQueryInteger($profileID),
            $this->_siteID
        );

        $rs = $this->_db->query($sql);

        if (!$rs || $this->_db->getAffectedRows() <= 0)
        {
            $inTransaction && $this->_db->rollBackTransaction();
            return false;
        }
        else if (!$this->deletePage($profileID))
        {
            $inTransaction && $this->_db->rollbackTransaction();
            return false;
        }
        else if (!$this->deleteField($profileID))
        {
            $inTransaction && $this->_db->rollbackTransaction();
            return false;
        }
        else if (!$this->deleteTitle($profileID))
        {
            $inTransaction && $this->_db->rollbackTransaction();
            return false;
        }

        $inTransaction && $this->_db->commitTransaction();

        return true;
    }



    /**********************************************************************************************
     *
     * THE FOLLOWING FUNCTIONS DEAL PRIMARILY WITH THE PROFILE_PAGE TABLE
     *
     *********************************************************************************************/

    /**
     * Get the profile for a page from the profile_page table.
     *
     * @param integer ID from the profile table or false to use the current ID
     * @param string Identifier for the page (i.e.: addcandidate)
     * @param mixed array of the page information or false if no page exists
     */
    public function getPage($profileID = false, $page)
    {
        if (!($profileID = ($profileID !== false ? $profileID : $this->_savedProfileID)))
        {
            return false;
        }

        $sql = sprintf(
            "SELECT
                profile_page.site_id as siteID,
                profile_page.profile_id as profileID,
                profile_page.page as page,
                profile_page.columns as numColumns,
                profile_page.column_width as columnWidth,
                profile_page.column_height as columnHeight
            FROM
                profile_page
            WHERE
                profile_page.profile_id = %s
            AND
                profile_page.page = %s
            AND
                profile_page.site_id = %d",
            $this->_db->makeQueryInteger($profileID),
            $this->_db->makeQueryString($page),
            $this->_siteID
        );

        $rs = $this->_db->getAssoc($sql);

        if (empty($rs))
        {
            return false;
        }
        else
        {
            return $rs;
        }
    }

    public function getPageFields($profileID = false, $page)
    {
        $sql = sprintf(
            "SELECT
                profile_page_field.site_id as siteID,
                profile_page_field.profile_id as profileID,
                profile_page_field.page,
                profile_page_field.column_name as columnName,
                profile_page_field.x_position as xPosition,
                profile_page_field.y_position as yPosition,
                profile_page_field.column_span as columnSpan,
                profile_page_field.row_span as rowSpan,
                profile_page_field.field_format_id as fieldFormatID
            FROM
                profile_page_field
            WHERE
                profile_page_field.profile_id = %s
            AND
                profile_page_field.page = %s
            AND
                profile_page_field.site_id = %d
            ORDER BY
                x_position, y_position",
            $this->_db->makeQueryInteger($profileID),
            $this->_db->makeQueryString($page),
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Adds a page to a user/site profile.
     *
     * @param integer ID from the profile table or false to use the current ID
     * @param string Identifier of the page
     * @param integer Numbers of columns
     * @param integer HTML-CSS width for each column
     * @param integer HTML-CSS height of each column
     * @return mixed ID of the inserted field or false on error
     */
    public function addPage($profileID = false, $page, $numColumns, $columnWidth, $columnHeight)
    {
        if (!($profileID = ($profileID !== false ? $profileID : $this->_savedProfileID)))
        {
            return false;
        }

        $sql = sprintf(
            "INSERT INTO
                profile_page (profile_id, page, columns, column_width, column_height, site_id)
            VALUES (%s, %s, %s, %s, %s, %d)",
            $this->_db->makeQueryInteger($profileID),
            $this->_db->makeQueryString($page),
            $this->_db->makeQueryInteger($numColumns),
            $this->_db->makeQueryString($columnWidth),
            $this->_db->makeQueryString($columnHeight),
            $this->_siteID
        );

        // No errors, primary key violations indicate page exists
        $rs = $this->_db->query($sql, true);

        if (!$rs || $this->_db->getAffectedRows() <= 0)
        {
            return false;
        }

        return true;
    }

    /**
     * Updates a page on a user/site profile.
     *
     * @param integer ID from the profile table or false to use the current ID
     * @param string Identifier of the page
     * @param integer Numbers of columns
     * @param integer Pixel width for each column
     * @param integer Pixel height of each column
     * @return boolean true on success, false on failure
     */
    public function updatePage($profileID = false, $page, $numColumns, $columnWidth, $columnHeight)
    {
        if (!($profileID = ($profileID !== false ? $profileID : $this->_savedProfileID)))
        {
            return false;
        }

        $sql = sprintf(
            "UPDATE
                profile_page
            SET
                columns = %s,
                width = %s,
                height = %s
            WHERE
                profile_page.profile_id = %s
            AND
                profile_page.page = %s
            AND
                profile_page.site_id = %d",
            $this->_db->makeQueryInteger($numColumns),
            $this->_db->makeQueryInteger($columnWidth),
            $this->_db->makeQueryInteger($columnHeight),
            $this->_db->makeQueryInteger($profileID),
            $this->_db->makeQueryString($page),
            $this->_siteID
        );

        $this->_db->query($sql);
        return ($this->_db->getAffectedRows() > 0) ? true : false;
    }

    /**
     * Delete a profile page (or all pages for a profile)
     *
     * @param integer ID from the profile table or false to use the current ID
     * @param string Identifier for the page (boolean false to delete all pages in the profile)
     * @return boolean true on success or no pages deleted, false on failure
     */
    public function deletePage($profileID = false, $page = false)
    {
        if (!($profileID = ($profileID !== false ? $profileID : $this->_savedProfileID)))
        {
            return false;
        }

        $inTransaction = $this->_db->beginTransaction();

        if ($page !== false)
        {
            $criterion = sprintf(
                "AND
                    page = %s",
                $this->_db->makeQueryString($page)
            );
        }
        else
        {
            $criterion = '';
        }

        $sql = sprintf(
            "DELETE FROM
                profile_page
            WHERE
                profile_id = %s
            %s
            AND
                profile_page.site_id = %d",
            $this->_db->makeQueryInteger($profileID),
            $criterion,
            $this->_siteID
        );

        $rs = $this->_db->query($sql);
        if (!$rs)
        {
            $inTransaction && $this->_db->rollbackTransaction();
            return false;
        }
        else if (!$this->deleteField($profileID))
        {
            $inTransaction && $this->_db->rollbackTransaction();
            return false;
        }

        $inTransaction && $this->_db->commitTransaction();

        return true;
    }



    /**********************************************************************************************
     *
     * THE FOLLOWING FUNCTIONS DEAL PRIMARILY WITH THE PROFILE_PAGE_FIELD TABLE
     *
     *********************************************************************************************/

    /**
     * Gets profile information for a field on a page.
     *
     * @param integer ID from the profile table or false to use the current ID
     * @param string Identifier from the profile_page table
     * @param string Name of the column from the database (i.e.: first_name)
     * @return mixed Array of the field data or false if no field exists in the current profile
     */
    public function getField($profileID = false, $page, $columnName)
    {
        if (!($profileID = ($profileID !== false ? $profileID : $this->_savedProfileID)))
        {
            return false;
        }

        $sql = sprintf(
            "SELECT
                profile_page_field.profile_id as profileID,
                profile_page_field.page,
                profile_page_field.column_name as columnName,
                profile_page_field.x_position as xPosition,
                profile_page_field.y_position as yPosition,
                profile_page_field.column_span as columnSpan,
                profile_page_field.row_span as rowSpan,
                profile_page_field.field_format_id as fieldFormatID
            FROM
                profile_page_field
            WHERE
                profile_page_field.profile_id = %s
            AND
                profile_page_field.page = %s
            AND
                profile_page_field.column_name = %s
            AND
                profile_page_field.site_id = %d",
            $this->_db->makeQueryInteger($profileID),
            $this->_db->makeQueryString($page),
            $this->_db->makeQueryString($columnName),
            $this->_siteID
        );

        $rs = $this->_db->getAssoc($sql);

        if (empty($rs))
        {
            return false;
        }

        return $rs;
    }

    /**
     * Enters a field into a page connected to a site/user profile.
     *
     * @param integer ID from the profile table or false to use the current ID
     * @param string Page from the profile_page table (i.e.: addcandidate)
     * @param string Column from the database (i.e.: first_name)
     * @param string Title for the column or boolean false to use existing text
     *               If you specify boolean false and no existing text exists, this function
     *               will fail like all who tried to defeat Chuck Norris.
     * @param integer X position in the grid
     * @param integer Y position in the grid
     * @param integer Number of columns this field should span
     * @param integer Number of rows this field should span
     * @param boolean true if the field should be shown, false if hidden
     */
    public function addField($profileID = false, $page, $columnName, $title = false, $xPosition = 0,
        $yPosition = 0, $columnSpan = 1, $rowSpan = 1)
    {
        if (!($profileID = ($profileID !== false ? $profileID : $this->_savedProfileID)))
        {
            return false;
        }

        // We're adding to two tables, make this transactional (fail on either)
        $inTransaction = $this->_db->beginTransaction();

        if (!$title)
        {
            // Add column name as the column text, failure means it's already set
            $result = $this->addTitle($profileID, $columnName, $columnName, false);
        }
        else
        {
            // Try to insert title text
            $result = $this->addTitle($profileID, $columnName, $title, false);

            // It's ok to fail, that means it already exists. Update it.
            if (!$result)
            {
                // If the update fails, that means nothing was changed, so no need to check.
                $this->updateTitle($profileID, $columnName, $title, false);
            }
        }

        $sql = sprintf(
            "INSERT INTO
                profile_page_field (profile_id, page, column_name, x_position, y_position, column_span,
                row_span, site_id)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %d)",
            $this->_db->makeQueryInteger($profileID),
            $this->_db->makeQueryString($page),
            $this->_db->makeQueryString($columnName),
            $this->_db->makeQueryInteger($xPosition),
            $this->_db->makeQueryInteger($yPosition),
            $this->_db->makeQueryInteger($columnSpan),
            $this->_db->makeQueryInteger($rowSpan),
            $this->_siteID
        );

        // No errors, primary key violations indicate field exists
        $rs = $this->_db->query($sql, true);

        if (!$rs || $this->_db->getAffectedRows() <= 0)
        {
            $inTransaction && $this->_db->rollbackTransaction();
            return false;
        }

        $inTransaction && $this->_db->commitTransaction();
        return true;
    }

    /**
     * Updates a field in a page connected to a site/user profile.
     *
     * @param integer ID from the profile table or false to use the current ID
     * @param string Page from the profile_page table
     * @param string Column from the database (i.e.: first_name)
     * @param string Title Use boolean false to retain value. Setting this value will change
     *               ALL INSTANCES of column_name regardless of which field they're attached
     *               to in the profile.
     * @param integer X position in the grid
     * @param integer Y position in the grid
     * @param integer Number of columns this field should span
     * @param integer Number of rows this field should span
     * @param boolean true if the field should be shown, false if hidden
     */
    public function updateField($profileID = false, $page, $columnName, $title = false,
        $xPosition = 0, $yPosition = 0, $columnSpan = 1, $rowSpan = 1)
    {
        if (!($profileID = ($profileID !== false ? $profileID : $this->_savedProfileID)))
        {
            return false;
        }

        if ($title !== false)
        {
            // Update all occurances of this column name title
            // No need to error check, false just means nothing was changed
            $this->updateTitle($profileID, $columnName, $title, false);
        }

        $sql = sprintf(
            "UPDATE
                profile_page_field
            SET
                x_position = %s,
                y_position = %s,
                column_span = %s,
                row_span = %s
            WHERE
                profile_id = %s
            AND
                page = %s
            AND
                column_name = %s
            AND
                site_id = %d",
            $this->_db->makeQueryInteger($xPosition),
            $this->_db->makeQueryInteger($yPosition),
            $this->_db->makeQueryInteger($columnSpan),
            $this->_db->makeQueryInteger($rowSpan),
            $this->_db->makeQueryInteger($profileID),
            $this->_db->makeQueryString($page),
            $this->_db->makeQueryString($columnName),
            $this->_siteID
        );

        return $this->_db->query($sql);
    }

    /**
     * Attempts to delete a field from a page or all fields from the profile.
     *
     * @param integer ID from the profile table or false to use the current ID
     * @param string Name of the page from the profile_page table
     * @param string Column from the database
     */
    public function deleteField($profileID = false, $page = false, $columnName = false)
    {
        if (!($profileID = ($profileID !== false ? $profileID : $this->_savedProfileID)))
        {
            return false;
        }

        $inTransaction = $this->_db->beginTransaction();

        if ($page !== false && $columnName !== false)
        {
            $criterion = sprintf(
                "AND
                    page = %s
                 AND
                    column_name = %s",
                $this->_db->makeQueryString($page),
                $this->_db->makeQueryString($columnName)
            );
        }
        else if ($page === false || $columnName === false) {}
        else
        {
            // Cannot do a selective page/column only delete
            $inTransaction && $this->_db->rollbackTransaction();
            return false;
        }

        $sql = sprintf(
            "DELETE FROM
                profile_page_field
             WHERE
                profile_id = %s
             %s
             AND
                site_id = %d",
            $this->_db->makeQueryInteger($profileID),
            $criterion,
            $this->_siteID
        );

        $rs = $this->_db->query($sql);

        if (!$rs)
        {
            $inTransaction && $this->_db->rollbackTransaction();
            return false;
        }

        $inTransaction && $this->_db->commitTransaction();

        return true;
    }


    /**********************************************************************************************
     *
     * THE FOLLOWING FUNCTIONS DEAL PRIMARILY WITH THE PROFILE_TITLE TABLE
     *
     *********************************************************************************************/

    /**
     * Template SQL for getting a row from the profile_title table so no
     * rewrites between get(), getAll() etc.
     *
     * @return string SQL retrieval code
     */
    public function getTitleBaseSQL()
    {
        $sql = sprintf(
            "SELECT
                profile_title.site_id as siteID,
                profile_title.profile_id as profileID,
                profile_title.column_name as columnName,
                profile_title.title as title,
                profile_title.note as note
            FROM
                profile_title
            WHERE
                profile_title.site_id = %d",
            $this->_siteID
        );

        return $sql;
    }

    /**
     * Get the title for a column name for a given profile. If none is found,
     * boolean false is returned. Otherwise an associate array.
     *
     * @param integer ID from the profile table or false to use the current ID
     * @param mixed Array or boolean false
     */
    public function getTitle($profileID = false, $columnName)
    {
        if (!($profileID = ($profileID !== false ? $profileID : $this->_savedProfileID)))
        {
            return false;
        }

        // If title cacheing is enabled and we're using the internal profile ID,
        // then return the value from the cache instead of running another query.
        if ($this->_savedProfileID !== false && $this->_savedProfileID == $profileID &&
            $this->_titleCache !== false)
        {
            foreach ($this->_titleCache as $item)
            {
                if (!strcmp($item['columnName'], $columnName))
                {
                    return $item;
                }
            }

            return $columnName;
        }

        $sql = sprintf(
            "%s
            AND
                profile_title.profile_id = %s
            AND
                profile_title.column_name = %s",
            $this->getTitleBaseSQL(),
            $this->_db->makeQueryInteger($profileID),
            $this->_db->makeQueryString($columnName)
        );

        $rs = $this->_db->getAssoc($sql);

        return empty($rs) ? false : $rs;
    }

    public function getTitleText($profileID = false, $columnName)
    {
        if (!($profileID = ($profileID !== false ? $profileID : $this->_savedProfileID)))
        {
            return false;
        }

        // If title cacheing is enabled and we're using the internal profile ID,
        // then return the value from the cache instead of running another query.
        if ($this->_savedProfileID !== false && $this->_savedProfileID == $profileID &&
            $this->_titleCache !== false)
        {
            foreach ($this->_titleCache as $item)
            {
                if (!strcmp($item['columnName'], $columnName))
                {
                    return $item['title'];
                }
            }

            return $columnName;
        }

        $sql = sprintf(
            "SELECT
                profile_title.title as title
            FROM
                profile_title
            WHERE
                profile_title.profile_id = %s
            AND
                profile_title.column_name = %s
            AND
                profile_title.site_id = %d",
            $this->_siteID,
            $this->_db->makeQueryInteger($profileID),
            $this->_db->makeQueryString($columnName)
        );

        $rs = $this->_db->getAssoc($sql);
        if (!$rs || empty($rs) || !isset($rs['title']))
        {
            // Try to add the new title text since it doesn't seem to exist
            $this->addTitle($profileID, $columnName, $columnName, false);

            return $columnName;
        }

        return $rs['title'];
    }

    /**
     * Gets all profile titles for a specific profile.
     *
     * @param integer ID from the profile table or false to use the current ID
     * @return array Empty or populated array of titles and their values
     */
    public function getAllTitles($profileID = false)
    {
        if (!($profileID = ($profileID !== false ? $profileID : $this->_savedProfileID)))
        {
            return false;
        }

        // If title cacheing is enabled and we're using the internal profile ID,
        // then return the value from the cache instead of running another query.
        if ($this->_savedProfileID !== false && $this->_savedProfileID == $profileID &&
            $this->_titleCache !== false)
        {
            return $this->_titleCache;
        }

        $sql = sprintf(
            "%s
            AND
                profile_title.profile_id = %s",
            $this->getTitleBaseSQL(),
            $this->_db->makeQueryInteger($profileID)
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Adds a title for a given column name in a profile.
     *
     * @param integer ID from the profile table or false to use the current ID
     * @param string Name of the column "first_name"
     * @param string Title for the column "First Name"
     * @param string Anything to describe the column in more detail than the title or boolean false for null
     * @return mixed Integer ID of the insert or boolean false on failure
     */
    public function addTitle($profileID = false, $columnName, $title, $note = false)
    {
        if (!($profileID = ($profileID !== false ? $profileID : $this->_savedProfileID)))
        {
            return false;
        }

        $sql = sprintf(
            "INSERT INTO
                profile_title (profile_id, column_name, title, note, site_id)
            VALUES
                (%s, %s, %s, %s, %d)",
            $this->_db->makeQueryInteger($profileID),
            $this->_db->makeQueryString($columnName),
            $this->_db->makeQueryString($title),
            $note !== false && !empty($note) ? $this->_db->makeQueryString($note) : 'NULL',
            $this->_siteID
        );

        // No errors, as primary key "duplicates" errors indicate title exists
        $rs = $this->_db->query($sql, true);

        if (!$rs || $this->_db->getAffectedRows() <= 0)
        {
            return false;
        }

        // If cacheing is enabled, add it the new title to the local cache
        if ($this->_savedProfileID !== false && $this->_titleCache !== false)
        {
            $this->_titleCache[] = array(
                'profileID' => $profileID,
                'column_name' => $columnName,
                'title' => $title,
                'note' => $note !== false ? $note : ''
            );
        }

        return true;
    }

    /**
     * Updates a title for a given column name in a profile.
     *
     * @param integer ID from the profile table or false to use the current ID
     * @param string Name of the column "first_name"
     * @param string Title for the column "First Name"
     * @param string Anything to describe the column in more detail than the title
     * @return boolean True or false, true meaning success
     */
    public function updateTitle($profileID = false, $columnName, $title, $note = false)
    {
        if (!($profileID = ($profileID !== false ? $profileID : $this->_savedProfileID)))
        {
            return false;
        }

        $sql = sprintf(
            "UPDATE
                profile_title
            SET
                title = %s,
                note = %s
            WHERE
                profile_title.profile_id = %s
            AND
                profile_title.column_name = %s
            AND
                profile_title.site_id = %d",
            $this->_db->makeQueryString($title),
            $note !== false && !empty($note) ? $this->_db->makeQueryString($note) : 'NULL',
            $this->_db->makeQueryInteger($profileID),
            $this->_db->makeQueryString($columnName),
            $this->_siteID
        );

        $rs = $this->_db->query($sql);

        if (!$rs || $this->_db->getAffectedRows() <= 0)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * Delete a title from the profile_title table or all from a profile.
     *
     * @param integer ID from the profile table or false to use the current ID
     * @param string Name of the unique column
     * @return boolean true on success or none deleted, false on error
     */
    public function deleteTitle($profileID = false, $columnName = false)
    {
        if (!($profileID = ($profileID !== false ? $profileID : $this->_savedProfileID)))
        {
            return false;
        }

        $inTransaction = $this->_db->beginTransaction();

        $sql = sprintf(
            "DELETE FROM
                profile_title
            WHERE
                profile_id = %s
            AND
                column_name = %s
            AND
                site_id = %d",
            $this->_db->makeQueryInteger($profileID),
            $this->_db->makeQueryString($columnName),
            $this->_siteID
        );

        $rs = $this->_db->query($sql);

        if (!$rs)
        {
            $inTransaction && $this->_db->rollbackTransaction();
            return false;
        }

        $inTransaction && $this->_db->commitTransaction();

        return true;
    }


    /**********************************************************************************************
     *
     * THE FOLLOWING FUNCTIONS ARE TABLE IN-SPECIFIC/GENERAL FUNCTIONS
     *
     *********************************************************************************************/

}
