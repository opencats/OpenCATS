<?php
/**
 * CATS
 * Data Item Saved Lists Library
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
 * @version    $Id: SavedLists.php 3587 2007-11-13 03:55:57Z will $
 */

/**
 *	Data Item Saved Lists Library
 *	@package    CATS
 *	@subpackage Library
 */
class SavedLists
{
    private $_db;
    private $_siteID;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }


    /**
     * Returns all saved lists that the specified data item belongs to.
     *
     * @param flag data item type
     * @param flag data item type
     * @return array saved lists data
     */
    public function get($savedListID)
    {
        $sql = sprintf(
            "SELECT
                saved_list_id AS savedListID,
                description AS description,
                data_item_type AS dataItemType,
                is_dynamic AS isDynamic,
                datagrid_instance AS datagridInstance,
                parameters AS parameters,
                number_entries as numberEntries
            FROM
                saved_list
            WHERE
                site_id = %s
            AND
                saved_list_id = %s",
            $this->_siteID,
            $savedListID
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Returns relevant data for all saved lists, optionally restricted by data
     * item type.
     *
     * define('ALL_LISTS',     0);
     * define('STATIC_LISTS',  1);
     * define('DYNAMIC_LISTS', 2);
     *
     * @param flag data item type
     * @return array saved lists data
     */
     
    public function getAll($dataItemType = -1, $listType = ALL_LISTS)
    {
        if ($dataItemType != -1)
        {
            $typeCriterion = sprintf(
                'AND data_item_type = %s',
                $this->_db->makeQueryInteger($dataItemType)
            );
        }
        else
        {
            $typeCriterion = '';
        }
        
        if ($listType == STATIC_LISTS)
        {
            $typeCriterion .= ' AND is_dynamic = false';
        }

        if ($listType == DYNAMIC_LISTS)
        {
            $typeCriterion .= ' AND is_dynamic = true';
        }

        $sql = sprintf(
            "SELECT
                saved_list_id AS savedListID,
                data_item_type as dataItemType,
                description AS description,
                is_dynamic AS isDynamic,
                datagrid_instance as datagridInstance,
                parameters as parameters,
                created_by as createdBy,
                number_entries as numberEntries
            FROM
                saved_list
            WHERE
                site_id = %s
            %s
            ORDER BY
                saved_list_id ASC",
            $this->_siteID,
            $typeCriterion
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns the ID of a saved list based on its description.
     *
     * @param string saved list description
     * @return integer saved list ID or -1 on failure
     */
    public function getIDByDescription($description)
    {
        $sql = sprintf(
            "SELECT
                saved_list_id AS savedListID
            FROM
                saved_list
            WHERE
                description = %s
            AND
                site_id = %s",
            $this->_db->makeQueryString($description),
            $this->_siteID
        );
        $rs = $this->_db->getAssoc($sql);

        if (empty($rs))
        {
            return -1;
        }

        return $rs['savedListID'];
    }

    /**
     * Updates a lists description.
     *
     * @param integer savedListID
     * @param string description
     * @return void
     */
    public function updateListName($savedListID, $description)
    {
        $sql = sprintf(
            "UPDATE
                saved_list
             SET
                description = %s,
                date_modified = NOW()
             WHERE
                saved_list_id = %s
             AND
                site_id = %s",
            $this->_db->makeQueryString($description),
            $savedListID,
            $this->_siteID
        );
        $rs = $this->_db->query($sql);
    }     
    
    /**
     * Adds a list.
     *
     * @param string list name
     * @return void
     */
    public function newListName($description, $dataItemType)
    {
        $sql = sprintf(
            "INSERT INTO
                saved_list
             SET
                description = %s,
                data_item_type = %s,
                is_dynamic = 0,
                site_id = %s,
                number_entries = 0,
                created_by = %s,
                date_created = NOW(),
                date_modified = NOW()",
            $this->_db->makeQueryString($description),
            $dataItemType,
            $this->_siteID,
            $_SESSION['CATS']->getUserID()
        );
        $rs = $this->_db->query($sql);       
    }

    /**
     * Deletes a list.
     *
     * @param string list name
     * @return void
     */
    public function delete($savedListID)
    {
        $sql = sprintf(
            "DELETE FROM
                saved_list
             WHERE
                saved_list_id = %s
             AND
                site_id = %s",
            $savedListID,
            $this->_siteID
        );
        $rs = $this->_db->query($sql);       
        
        $sql = sprintf(
            "DELETE FROM
                saved_list_entry
             WHERE
                saved_list_id = %s
             AND
                site_id = %s",
            $savedListID,
            $this->_siteID
        );
        $rs = $this->_db->query($sql);    
    }

    /**
     * Returns all saved lists that the specified data item belongs to.
     *
     * @param flag data item type
     * @param flag data item type
     * @return array saved lists data
     */
    public function getListsByItem($dataItemType, $dataItemID)
    {
        $sql = sprintf(
            "SELECT
                saved_list_entry.saved_list_entry_id AS savedListEntryID,
                saved_list_entry.saved_list_id AS savedListID,
                saved_list.description AS description
            FROM
                saved_list_entry
            LEFT JOIN saved_list
                ON saved_list.saved_list_id = saved_list_entry.saved_list_id
            WHERE
                saved_list_entry.data_item_id = %s
            AND
                saved_list_entry.data_item_type = %s
            AND
                saved_list_entry.site_id = %s
            AND
                saved_list.site_id = %s",
            $this->_db->makeQueryInteger($dataItemID),
            $this->_db->makeQueryInteger($dataItemType),
            $this->_siteID,
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Adds, renames, and deletes saved lists from list editor data.
     *
     * @param array list editor data
     * @param flag data item type
     * @return void
     */
    public function updateSavedLists($updates, $dataItemType)
    {
        foreach ($updates as $update)
        {
            switch ($update[2])
            {
                case LIST_EDITOR_ADD:
                    $sql = sprintf(
                        "INSERT INTO saved_list (
                            description,
                            site_id,
                            data_item_type,
                            created_by,
                            date_created,
                            date_modified
                         )
                         VALUES (
                            %s,
                            %s,
                            %s,
                            %s,
                            NOW(),
                            NOW()
                        )",
                        $this->_db->makeQueryString($update[0]),
                        $this->_siteID,
                        $this->_db->makeQueryInteger($dataItemType),
                        $_SESSION['CATS']->getUserID()
                    );
                    $this->_db->query($sql);

                    break;

                case LIST_EDITOR_REMOVE:
                    $sql = sprintf(
                        "DELETE FROM
                            saved_list_entry
                        WHERE
                            saved_list_id = %s
                        AND
                            site_id = %s",
                        $this->_db->makeQueryInteger($update[1]),
                        $this->_siteID
                    );
                    $this->_db->query($sql);

                    $sql = sprintf(
                        "DELETE FROM
                            saved_list
                         WHERE
                            saved_list_id = %s
                         AND
                            site_id = %s",
                         $this->_db->makeQueryInteger($update[1]),
                         $this->_siteID
                    );
                    $this->_db->query($sql);

                    break;

                case LIST_EDITOR_MODIFY:
                    $sql = sprintf(
                        "UPDATE
                            saved_list
                         SET
                            description = %s,
                            date_modified = NOW()
                         WHERE
                            saved_list_id = %s
                         AND
                            site_id = %s",
                         $this->_db->makeQueryString($update[0]),
                         $this->_db->makeQueryInteger($update[1]),
                         $this->_siteID
                    );
                    $this->_db->query($sql);

                    $this->updateSavedListItemCountAndTimeStamp($update[1]);

                    break;

                default:
                    break;
            }
        }
    }
    
    /*
     * TODO: Document Me!
     */ 
    function addEntryMany($savedListID, $dataItemType, $dataItemIDs)
    {
        $sql = sprintf(
            "SELECT
                saved_list_id AS savedListID
            FROM
                saved_list_entry
            WHERE
                site_id = %s
            AND
                saved_list_id = %s
            AND
                data_item_id IN (%s)",
            $this->_siteID,
            $savedListID,
            implode(',', $dataItemIDs)
        );

        $rs = $this->_db->getAssoc($sql);     
        
        if (isset($rs['savedListID']))
        {
            return;
        }  
        
        $valuesArray = array();
        foreach ($dataItemIDs as $dataItemID)
        {
            $valuesArray[] = '(' . $this->_db->makeQueryInteger($savedListID) . ',' .
                                  $this->_db->makeQueryInteger($dataItemType) . ',' .
                                  $this->_db->makeQueryInteger($dataItemID) . ',' .
                                  $this->_siteID .','.
                                  'NOW())';
        }
        
        $sql = sprintf(
            "INSERT INTO saved_list_entry (
                saved_list_id,
                data_item_type,
                data_item_id,
                site_id,
                date_created
            )
            VALUES %s",
            implode(',', $valuesArray)
        );
        $this->_db->query($sql);
        
        $this->updateSavedListItemCountAndTimeStamp($savedListID);
    }
    
    /*
     * TODO: Document Me!
     */ 
    function removeEntryMany($savedListID, $dataItemIDs)
    {        
        $sql = sprintf(
            "DELETE FROM
                saved_list_entry 
             WHERE
                saved_list_id = %s
             AND
                site_id = %s
             AND
                data_item_id IN (%s)
            ",
            $this->_db->makeQueryInteger($savedListID),
            $this->_db->makeQueryInteger($this->_siteID),
            implode(',', $dataItemIDs)
        );
        $this->_db->query($sql);
        
        $this->updateSavedListItemCountAndTimeStamp($savedListID);
    }    
    
    /*
     * Immediantly regenerates the number of entries count for a static list.
     * Execute this EVERY TIME any change is made to the static list (except for
     * initial static list creation and deleteion)
     */
    private function updateSavedListItemCountAndTimeStamp($savedListID)
    {
        $sql = sprintf(
            "SELECT
                COUNT(saved_list_entry_id) AS numberOfEntries
             FROM
                saved_list_entry
             WHERE
                saved_list_id = %s",
             $savedListID
        );
                         
        $countRS = $this->_db->getAssoc($sql);

        $sql = sprintf(
            "UPDATE
                saved_list
             SET
                number_entries = %s
             WHERE
                saved_list_id = %s",
            $countRS['numberOfEntries'],
            $savedListID
        );
        
        $this->_db->query($sql);
        
        $sql = sprintf(
            "UPDATE 
                saved_list
             SET
                date_modified = NOW()
             WHERE
                site_id = %s
             AND
                saved_list_id = %s",
            $this->_siteID,
            $savedListID
        );
        
        $this->_db->query($sql);
    }

    /**
     * Adds, renames, and deletes saved list entries from list editor data.
     *
     * @param array list editor data
     * @param integer data item ID
     * @param flag data item type
     * @return void
     */
    public function updateDataItemSavedLists($updates, $dataItemID, $dataItemType)
    {
        foreach ($updates as $update)
        {
            switch ($update[2])
            {
                case LIST_EDITOR_ADD:
                    // FIXME: There has to be some way to already know the ID...
                    $savedListID = $this->getIDByDescription($update[0]);

                    if ($savedListID == -1)
                    {
                        return;
                        break;
                    }

                    $sql = sprintf(
                        "INSERT INTO saved_list_entry (
                            saved_list_id,
                            data_item_type,
                            data_item_id,
                            site_id,
                            date_created
                        )
                        VALUES (
                            %s,
                            %s,
                            %s,
                            %s,
                            NOW()
                        )",
                        $this->_db->makeQueryInteger($savedListID),
                        $this->_db->makeQueryInteger($dataItemType),
                        $this->_db->makeQueryInteger($dataItemID),
                        $this->_siteID
                    );
                    $this->_db->query($sql);
                    
                    $this->updateSavedListItemCountAndTimeStamp($savedListID);

                    break;

                case LIST_EDITOR_REMOVE:
                    $sql = sprintf(
                        "SELECT 
                            saved_list_id AS savedListID
                        FROM
                            saved_list_entry
                         WHERE
                            saved_list_entry_id = %s
                         AND
                            site_id = %s",
                         $this->_db->makeQueryInteger($update[1]),
                         $this->_siteID
                    );
                    
                    $rs = $this->_db->getAssoc($sql);
                
                    $sql = sprintf(
                        "DELETE FROM
                            saved_list_entry
                         WHERE
                            saved_list_entry_id = %s
                         AND
                            site_id = %s",
                         $this->_db->makeQueryInteger($update[1]),
                         $this->_siteID
                    );

                    $this->_db->query($sql);
                    
                    if (isset($rs['savedListID']))
                    {
                        $this->updateSavedListItemCountAndTimeStamp($rs['savedListID']);
                    }

                    break;

                /* This shouldn't happen. */
                case LIST_EDITOR_MODIFY:
                    $this->fatal(
                        'Tried to modify left side of double list. This '
                        . 'shouldn\'t have happened; please notify the CATS '
                        . 'Development Team at http://www.catsone.com/forum/ '
                        . 'of what you did to trigger this.'
                    );
                    break;

                default:
                    break;
            }
        }
    }
}

?>
