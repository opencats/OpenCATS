<?php
/**
 * CATS
 * History Library
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
 * @version    $Id: History.php 3592 2007-11-13 17:30:46Z brian $
 */

/**
 *	Data Item History Library
 *	@package    CATS
 *	@subpackage Library
 */
class History
{
    private $_db;
    private $_siteID;
    private $_userID;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        // FIXME: Remove dependency on Session here.
        $this->_userID = $_SESSION['CATS']->getUserID();
        $this->_db = DatabaseConnection::getInstance();
    }


    /**
     * Compares array prehistory and posthistory, and
     * stores any differences between them into the database.
     *
     * @param integer data item type
     * @param integer data item ID
     * @param array all record values prior to edit (prehistory)
     * @param array all record values post edit (posthistory)
     * @return query response
     */
    public function storeHistoryChanges($dataItemType, $dataItemID,
        $preHistory, $postHistory)
    {
        $changedHistory = array();

        /* Drop fields that change way too often. */
        if (isset($preHistory['dateModified']))
        {
            unset($preHistory['dateModified']);
        }

        $causedHistory = false;

        /* Find out what changed, store it in changedHistory. */
        foreach ($preHistory as $index => $value)
        {
            if ($value != $postHistory[$index])
            {
                $causedHistory = true;
                $changedHistory[] = $index;
            }
        }

        if (!$causedHistory)
        {
            return;
        }

        /* Make a description. */
        $description = sprintf(
            '(USER) changed field(s): %s.', implode(', ', $changedHistory)
        );

        /* Build new history entry entries. */
        $changedHistoryValues = array();
        foreach ($changedHistory as $index => $field)
        {
            /*NOTE:  Store strings (value value value value) for implode()ing later. */
            $changedHistoryValues[] = sprintf(
                "(%s, %s, %s, %s, %s, %s, NOW(), %s, %s)",
                $dataItemType,
                $dataItemID,
                $this->_db->makeQueryStringOrNULL($field),
                $this->_db->makeQueryStringOrNULL($preHistory[$field]),
                $this->_db->makeQueryStringOrNULL($postHistory[$field]),
                ($index == sizeof($changedHistory) - 1 ? $this->_db->makeQueryStringOrNULL($description) : 'NULL'),
                $this->_userID,
                $this->_siteID
            );
        }

        $sql = sprintf(
            "INSERT INTO history (data_item_type, data_item_id, the_field, previous_value, new_value, description, set_date, entered_by, site_id) VALUES %s",
            implode(',', $changedHistoryValues)
        );
        $this->_db->query($sql);
    }

    /**
     * Stores the fact that a new record was created.
     * 
     * @param integer data item type
     * @param integer data item id
     * @return void
     */
    public function storeHistoryNew($dataItemType, $dataItemID)
    {
        $this->storeHistoryCatagorized(
            $dataItemType, $dataItemID, '!newEntry!', '(USER) created entry.'
        );
    }

    /**
     * Stores the fact a record was deleted.
     * 
     * @param integer data item type
     * @param integer data item ID
     * @return query response
     */
    public function storeHistoryDeleted($dataItemType, $dataItemID)
    {
        $description = '(USER) deleted entry.';

        $changedHistoryValues[] = sprintf(
            "(%s, %s, %s, NULL, NULL, %s, NOW(), %s, %s)",
            $dataItemType,
            $dataItemID,
            $this->_db->makeQueryString('(DELETED)'),
            $this->_db->makeQueryStringOrNULL($description),
            $this->_userID,
            $this->_siteID
        );

        $sql = sprintf(
            "INSERT INTO history (data_item_type, data_item_id, the_field, previous_value, new_value, description, set_date, entered_by, site_id) VALUES %s",
            implode(',', $changedHistoryValues)
        );
        $this->_db->query($sql);
    }

    /**
     * Stores an arbritrary note concerning a data item.
     *
     * @param integer data item type
     * @param integer data item ID
     * @return query response
     */
    public function storeHistoryCatagorized($dataItemType, $dataItemID,
        $category, $description)
    {
        $changedHistoryValues[] = sprintf(
            "(%s, %s, %s, NULL, NULL, %s, NOW(), %s, %s)",
            $dataItemType,
            $dataItemID,
            $this->_db->makeQueryStringOrNULL($category),
            $this->_db->makeQueryStringOrNULL($description),
            $this->_userID,
            $this->_siteID
        );

        $sql = sprintf(
            "INSERT INTO history (data_item_type, data_item_id, the_field, previous_value, new_value, description, set_date, entered_by, site_id) VALUES %s",
            implode(',', $changedHistoryValues)
        );
        $this->_db->query($sql);
    }

    /**
     * Stores history into the history table (generated by one of the 
     * other History:: functions.)
     *
     * @param integer data item type
     * @param integer data item ID
     * @param string category
     * @param string before value
     * @param string after value
     * @param string modification description (if applicable) 
     * @return query response
     */
    public function storeHistoryData($dataItemType, $dataItemID, $category,
        $before, $after, $description)
    {
        $changedHistoryValues[] = sprintf(
            "(%s, %s, %s, %s, %s, %s, NOW(), %s, %s)",
            $dataItemType,
            $dataItemID,
            $this->_db->makeQueryStringOrNULL($category),
            $this->_db->makeQueryStringOrNULL($before),
            $this->_db->makeQueryStringOrNULL($after),
            $this->_db->makeQueryStringOrNULL($description),
            $this->_userID,
            $this->_siteID
        );

        $sql = sprintf(
            "INSERT INTO history (data_item_type, data_item_id, the_field, previous_value, new_value, description, set_date, entered_by, site_id) VALUES %s",
            implode(',', $changedHistoryValues)
        );
        $this->_db->query($sql);
    }

    /**
     * Stores history into the history table (generated by one of the 
     * other History:: functions.)
     *
     * @param integer data item type
     * @param integer data item ID
     * @param string modification description (if applicable) 
     * @return query response
     */
    public function storeHistorySimple($dataItemType, $dataItemID,
        $description)
    {
        $changedHistoryValues[] = sprintf(
            "(%s, %s, NULL, NULL, NULL, %s, NOW(), %s, %s)",
            $dataItemType,
            $dataItemID,
            $this->_db->makeQueryStringOrNULL($description),
            $this->_userID,
            $this->_siteID
        );

        // FIXME: Use field names! This is dangerous!
        $sql = sprintf(
            "INSERT INTO history (data_item_type, data_item_id, the_field, previous_value, new_value, description, set_date, entered_by, site_id) VALUES %s",
            implode(',', $changedHistoryValues)
        );
        $this->_db->query($sql);
    }

    /**
     * Get all history entries for a given data item.
     *
     * @param integer data item type
     * @param integer data item ID
     * @return array history entries
     */
    public function getAll($dataItemType, $dataItemID)
    {
        $sql = sprintf(
            "SELECT
                history_id AS historyID,
                the_field AS theField,
                previous_value AS previousValue,
                new_value AS newValue,
                description AS description,
                set_date AS setDate,
                entered_by AS enteredByID,
                CONCAT(
                    entered_by_user.first_name, ' ', entered_by_user.last_name
                ) AS enteredByFullName,
                DATE_FORMAT(
                    set_date, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateModified
            FROM
                history
            LEFT JOIN user AS entered_by_user
                ON history.entered_by = entered_by_user.user_id
            WHERE
                history.site_id = %s
            AND
                data_item_type = %s
            AND
                data_item_id = %s
            ORDER BY
                history.history_id DESC",
            $this->_siteID,
            $this->_db->makeQueryInteger($dataItemType),
            $this->_db->makeQueryInteger($dataItemID)
        );

        return $this->_db->getAllAssoc($sql);
    }
}

?>
