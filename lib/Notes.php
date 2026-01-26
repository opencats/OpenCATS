<?php
/**
 * CATS
 * Notes Library
 *
 * Provides CRUD operations for notes with Bullhorn API compatibility.
 * Notes can be associated with candidates, contacts, job orders, or companies.
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * Copyright (C) 2026 Space-O Technologies (https://www.spaceotechnologies.com/)
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
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 */

include_once(LEGACY_ROOT . '/lib/History.php');

/**
 * Notes Library
 * @package    CATS
 * @subpackage Library
 */
class Notes
{
    private $_db;
    private $_siteID;

    /** Valid person types for note associations */
    const VALID_PERSON_TYPES = ['candidate', 'contact', 'joborder', 'company'];

    /** Default activity type (Other) */
    const DEFAULT_ACTIVITY_TYPE = 400;


    /**
     * Constructor
     *
     * @param int $siteID Site ID
     */
    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }

    /**
     * Adds a new note to the database.
     *
     * @param string $action Note title/action/subject
     * @param string $comments Detailed note content
     * @param string $personType Type of entity: 'candidate', 'contact', 'joborder', 'company'
     * @param int $personID ID of the associated entity
     * @param int $userID User ID of the note creator
     * @param int $jobOrderID Optional related job order ID
     * @param int $activityType Optional activity type (default: 400 = Other)
     * @return int New note ID; -1 on failure
     */
    public function add($action, $comments, $personType, $personID, $userID,
        $jobOrderID = null, $activityType = self::DEFAULT_ACTIVITY_TYPE)
    {
        // Validate person type
        if (!in_array($personType, self::VALID_PERSON_TYPES)) {
            return -1;
        }

        $sql = sprintf(
            "INSERT INTO note (
                site_id,
                action,
                comments,
                person_type,
                person_id,
                joborder_id,
                activity_type,
                entered_by,
                date_created,
                date_modified
            )
            VALUES (
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                NOW(),
                NOW()
            )",
            $this->_siteID,
            $this->_db->makeQueryString($action),
            $this->_db->makeQueryString($comments),
            $this->_db->makeQueryString($personType),
            $this->_db->makeQueryInteger($personID),
            $jobOrderID !== null ? $this->_db->makeQueryInteger($jobOrderID) : 'NULL',
            $this->_db->makeQueryInteger($activityType),
            $this->_db->makeQueryInteger($userID)
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult) {
            return -1;
        }

        $noteID = $this->_db->getLastInsertID();

        // Store history entry
        $dataItemType = $this->_getDataItemType($personType);
        if ($dataItemType !== null) {
            $history = new History($this->_siteID);
            $history->storeHistoryData(
                $dataItemType,
                $personID,
                'NOTE',
                '(NEW)',
                $action . ': ' . substr($comments, 0, 100),
                '(USER) Added note.'
            );
        }

        return $noteID;
    }

    /**
     * Gets a single note by ID.
     *
     * @param int $noteID Note ID
     * @return array|false Note data or false if not found
     */
    public function get($noteID)
    {
        $sql = sprintf(
            "SELECT
                n.note_id AS noteID,
                n.site_id AS siteID,
                n.action,
                n.comments,
                n.person_type AS personType,
                n.person_id AS personID,
                n.joborder_id AS jobOrderID,
                n.activity_type AS activityType,
                at.short_description AS activityTypeName,
                n.entered_by AS enteredBy,
                n.date_created AS dateCreated,
                n.date_modified AS dateModified,
                u.first_name AS enteredByFirstName,
                u.last_name AS enteredByLastName,
                CONCAT(u.first_name, ' ', u.last_name) AS enteredByName,
                j.title AS jobOrderTitle
            FROM
                note n
            LEFT JOIN user u ON n.entered_by = u.user_id
            LEFT JOIN activity_type at ON n.activity_type = at.activity_type_id
            LEFT JOIN joborder j ON n.joborder_id = j.joborder_id
            WHERE
                n.note_id = %s
            AND
                n.site_id = %s",
            $this->_db->makeQueryInteger($noteID),
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Gets all notes for a specific entity.
     *
     * @param string $personType Type of entity: 'candidate', 'contact', 'joborder', 'company'
     * @param int $personID ID of the entity
     * @param int $limit Maximum number of records to return (0 = no limit)
     * @param int $offset Number of records to skip
     * @return array Notes data
     */
    public function getByPerson($personType, $personID, $limit = 0, $offset = 0)
    {
        // Validate person type
        if (!in_array($personType, self::VALID_PERSON_TYPES)) {
            return [];
        }

        $limitClause = '';
        if ($limit > 0) {
            $limitClause = sprintf(
                "LIMIT %s, %s",
                $this->_db->makeQueryInteger($offset),
                $this->_db->makeQueryInteger($limit)
            );
        }

        $sql = sprintf(
            "SELECT
                n.note_id AS noteID,
                n.site_id AS siteID,
                n.action,
                n.comments,
                n.person_type AS personType,
                n.person_id AS personID,
                n.joborder_id AS jobOrderID,
                n.activity_type AS activityType,
                at.short_description AS activityTypeName,
                n.entered_by AS enteredBy,
                n.date_created AS dateCreated,
                n.date_modified AS dateModified,
                u.first_name AS enteredByFirstName,
                u.last_name AS enteredByLastName,
                CONCAT(u.first_name, ' ', u.last_name) AS enteredByName,
                j.title AS jobOrderTitle
            FROM
                note n
            LEFT JOIN user u ON n.entered_by = u.user_id
            LEFT JOIN activity_type at ON n.activity_type = at.activity_type_id
            LEFT JOIN joborder j ON n.joborder_id = j.joborder_id
            WHERE
                n.person_type = %s
            AND
                n.person_id = %s
            AND
                n.site_id = %s
            ORDER BY
                n.date_created DESC
            %s",
            $this->_db->makeQueryString($personType),
            $this->_db->makeQueryInteger($personID),
            $this->_siteID,
            $limitClause
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Gets all notes for the site.
     *
     * @param int $limit Maximum number of records to return (0 = no limit)
     * @param int $offset Number of records to skip
     * @return array Notes data
     */
    public function getAll($limit = 0, $offset = 0)
    {
        $limitClause = '';
        if ($limit > 0) {
            $limitClause = sprintf(
                "LIMIT %s, %s",
                $this->_db->makeQueryInteger($offset),
                $this->_db->makeQueryInteger($limit)
            );
        }

        $sql = sprintf(
            "SELECT
                n.note_id AS noteID,
                n.site_id AS siteID,
                n.action,
                n.comments,
                n.person_type AS personType,
                n.person_id AS personID,
                n.joborder_id AS jobOrderID,
                n.activity_type AS activityType,
                at.short_description AS activityTypeName,
                n.entered_by AS enteredBy,
                n.date_created AS dateCreated,
                n.date_modified AS dateModified,
                u.first_name AS enteredByFirstName,
                u.last_name AS enteredByLastName,
                CONCAT(u.first_name, ' ', u.last_name) AS enteredByName,
                j.title AS jobOrderTitle
            FROM
                note n
            LEFT JOIN user u ON n.entered_by = u.user_id
            LEFT JOIN activity_type at ON n.activity_type = at.activity_type_id
            LEFT JOIN joborder j ON n.joborder_id = j.joborder_id
            WHERE
                n.site_id = %s
            ORDER BY
                n.date_created DESC
            %s",
            $this->_siteID,
            $limitClause
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Gets all notes by a specific user.
     *
     * @param int $userID User ID
     * @param int $limit Maximum number of records to return (0 = no limit)
     * @param int $offset Number of records to skip
     * @return array Notes data
     */
    public function getByUser($userID, $limit = 0, $offset = 0)
    {
        $limitClause = '';
        if ($limit > 0) {
            $limitClause = sprintf(
                "LIMIT %s, %s",
                $this->_db->makeQueryInteger($offset),
                $this->_db->makeQueryInteger($limit)
            );
        }

        $sql = sprintf(
            "SELECT
                n.note_id AS noteID,
                n.site_id AS siteID,
                n.action,
                n.comments,
                n.person_type AS personType,
                n.person_id AS personID,
                n.joborder_id AS jobOrderID,
                n.activity_type AS activityType,
                at.short_description AS activityTypeName,
                n.entered_by AS enteredBy,
                n.date_created AS dateCreated,
                n.date_modified AS dateModified,
                u.first_name AS enteredByFirstName,
                u.last_name AS enteredByLastName,
                CONCAT(u.first_name, ' ', u.last_name) AS enteredByName,
                j.title AS jobOrderTitle
            FROM
                note n
            LEFT JOIN user u ON n.entered_by = u.user_id
            LEFT JOIN activity_type at ON n.activity_type = at.activity_type_id
            LEFT JOIN joborder j ON n.joborder_id = j.joborder_id
            WHERE
                n.entered_by = %s
            AND
                n.site_id = %s
            ORDER BY
                n.date_created DESC
            %s",
            $this->_db->makeQueryInteger($userID),
            $this->_siteID,
            $limitClause
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Updates an existing note.
     *
     * @param int $noteID Note ID to update
     * @param array $data Associative array of fields to update
     *                    Supported: action, comments, personType, personID, jobOrderID, activityType
     * @return bool True on success, false on failure
     */
    public function update($noteID, $data)
    {
        // Get existing note for history
        $existing = $this->get($noteID);
        if (!$existing) {
            return false;
        }

        $updates = [];

        if (isset($data['action'])) {
            $updates[] = sprintf("action = %s", $this->_db->makeQueryString($data['action']));
        }

        if (isset($data['comments'])) {
            $updates[] = sprintf("comments = %s", $this->_db->makeQueryString($data['comments']));
        }

        if (isset($data['personType']) && in_array($data['personType'], self::VALID_PERSON_TYPES)) {
            $updates[] = sprintf("person_type = %s", $this->_db->makeQueryString($data['personType']));
        }

        if (isset($data['personID'])) {
            $updates[] = sprintf("person_id = %s", $this->_db->makeQueryInteger($data['personID']));
        }

        if (array_key_exists('jobOrderID', $data)) {
            $updates[] = $data['jobOrderID'] !== null
                ? sprintf("joborder_id = %s", $this->_db->makeQueryInteger($data['jobOrderID']))
                : "joborder_id = NULL";
        }

        if (isset($data['activityType'])) {
            $updates[] = sprintf("activity_type = %s", $this->_db->makeQueryInteger($data['activityType']));
        }

        if (empty($updates)) {
            return true; // Nothing to update
        }

        $updates[] = "date_modified = NOW()";

        $sql = sprintf(
            "UPDATE note SET %s WHERE note_id = %s AND site_id = %s",
            implode(', ', $updates),
            $this->_db->makeQueryInteger($noteID),
            $this->_siteID
        );

        $result = $this->_db->query($sql);

        if ($result) {
            // Store history entry
            $dataItemType = $this->_getDataItemType($existing['personType']);
            if ($dataItemType !== null) {
                $history = new History($this->_siteID);
                $history->storeHistoryData(
                    $dataItemType,
                    $existing['personID'],
                    'NOTE',
                    $existing['action'],
                    isset($data['action']) ? $data['action'] : $existing['action'],
                    '(USER) Edited note.'
                );
            }
        }

        return $result;
    }

    /**
     * Deletes a note.
     *
     * @param int $noteID Note ID to delete
     * @return bool True on success, false on failure
     */
    public function delete($noteID)
    {
        // Get note for history before deleting
        $note = $this->get($noteID);
        if (!$note) {
            return false;
        }

        $sql = sprintf(
            "DELETE FROM note WHERE note_id = %s AND site_id = %s",
            $this->_db->makeQueryInteger($noteID),
            $this->_siteID
        );

        $result = $this->_db->query($sql);

        if ($result) {
            // Store history entry
            $dataItemType = $this->_getDataItemType($note['personType']);
            if ($dataItemType !== null) {
                $history = new History($this->_siteID);
                $history->storeHistoryData(
                    $dataItemType,
                    $note['personID'],
                    'NOTE',
                    $note['action'],
                    '(DELETE)',
                    '(USER) Deleted note.'
                );
            }
        }

        return $result;
    }

    /**
     * Gets the count of notes for a specific entity.
     *
     * @param string $personType Type of entity: 'candidate', 'contact', 'joborder', 'company'
     * @param int $personID ID of the entity
     * @return int Note count
     */
    public function getCount($personType, $personID)
    {
        // Validate person type
        if (!in_array($personType, self::VALID_PERSON_TYPES)) {
            return 0;
        }

        $sql = sprintf(
            "SELECT COUNT(*) AS total
            FROM note
            WHERE person_type = %s
            AND person_id = %s
            AND site_id = %s",
            $this->_db->makeQueryString($personType),
            $this->_db->makeQueryInteger($personID),
            $this->_siteID
        );

        $result = $this->_db->getAssoc($sql);
        return $result ? intval($result['total']) : 0;
    }

    /**
     * Gets total count of all notes in the site.
     *
     * @return int Total note count
     */
    public function getTotalCount()
    {
        $sql = sprintf(
            "SELECT COUNT(*) AS total FROM note WHERE site_id = %s",
            $this->_siteID
        );

        $result = $this->_db->getAssoc($sql);
        return $result ? intval($result['total']) : 0;
    }

    /**
     * Search notes by action or comments content.
     *
     * @param string $query Search query
     * @param int $limit Maximum number of records to return
     * @param int $offset Number of records to skip
     * @return array Matching notes
     */
    public function search($query, $limit = 25, $offset = 0)
    {
        $searchTerm = '%' . $query . '%';

        $sql = sprintf(
            "SELECT
                n.note_id AS noteID,
                n.site_id AS siteID,
                n.action,
                n.comments,
                n.person_type AS personType,
                n.person_id AS personID,
                n.joborder_id AS jobOrderID,
                n.activity_type AS activityType,
                at.short_description AS activityTypeName,
                n.entered_by AS enteredBy,
                n.date_created AS dateCreated,
                n.date_modified AS dateModified,
                u.first_name AS enteredByFirstName,
                u.last_name AS enteredByLastName,
                CONCAT(u.first_name, ' ', u.last_name) AS enteredByName,
                j.title AS jobOrderTitle
            FROM
                note n
            LEFT JOIN user u ON n.entered_by = u.user_id
            LEFT JOIN activity_type at ON n.activity_type = at.activity_type_id
            LEFT JOIN joborder j ON n.joborder_id = j.joborder_id
            WHERE
                n.site_id = %s
            AND
                (n.action LIKE %s OR n.comments LIKE %s)
            ORDER BY
                n.date_created DESC
            LIMIT %s, %s",
            $this->_siteID,
            $this->_db->makeQueryString($searchTerm),
            $this->_db->makeQueryString($searchTerm),
            $this->_db->makeQueryInteger($offset),
            $this->_db->makeQueryInteger($limit)
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Converts person type string to DATA_ITEM_* constant.
     *
     * @param string $personType Person type string
     * @return int|null DATA_ITEM_* constant or null if invalid
     */
    private function _getDataItemType($personType)
    {
        switch ($personType) {
            case 'candidate':
                return DATA_ITEM_CANDIDATE;
            case 'contact':
                return DATA_ITEM_CONTACT;
            case 'joborder':
                return DATA_ITEM_JOBORDER;
            case 'company':
                return DATA_ITEM_COMPANY;
            default:
                return null;
        }
    }
}

?>
