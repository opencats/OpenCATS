<?php
/**
 * CATS
 * Tasks Library
 *
 * Manages to-do items / tasks for the ATS.
 * Bullhorn-compatible Task entity for tracking:
 * - Subject, description, due dates
 * - Status workflow (Not Started, In Progress, Completed, Deferred)
 * - Priority levels (Low, Normal, High)
 * - Association with candidates, contacts, job orders, etc.
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
 * Tasks Library
 * @package    CATS
 * @subpackage Library
 */
class Tasks
{
    // Task status constants
    const STATUS_NOT_STARTED = 'Not Started';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_DEFERRED = 'Deferred';

    // Task priority constants
    const PRIORITY_LOW = 'Low';
    const PRIORITY_NORMAL = 'Normal';
    const PRIORITY_HIGH = 'High';

    private $_db;
    private $_siteID;


    /**
     * Constructor
     *
     * @param integer Site ID
     */
    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }


    /**
     * Creates a new task record.
     *
     * @param string Subject/title of the task
     * @param integer Owner user ID
     * @param array Optional additional data:
     *              - description: Task description
     *              - status: Task status (default: Not Started)
     *              - priority: Task priority (default: Normal)
     *              - dueDate: Due date (YYYY-MM-DD format)
     *              - personType: Type of associated person ('candidate', 'contact', 'joborder', 'company')
     *              - personID: ID of the associated person/entity
     * @return integer New task ID, or -1 on failure
     */
    public function add($subject, $ownerID, $data = array())
    {
        // Extract optional fields with defaults
        $description = isset($data['description']) ? $data['description'] : '';
        $status = isset($data['status']) ? $data['status'] : self::STATUS_NOT_STARTED;
        $priority = isset($data['priority']) ? $data['priority'] : self::PRIORITY_NORMAL;
        $dueDate = isset($data['dueDate']) ? $data['dueDate'] : null;
        $personType = isset($data['personType']) ? $data['personType'] : null;
        $personID = isset($data['personID']) ? $data['personID'] : null;

        // Validate status
        if (!self::isValidStatus($status)) {
            $status = self::STATUS_NOT_STARTED;
        }

        // Validate priority
        if (!self::isValidPriority($priority)) {
            $priority = self::PRIORITY_NORMAL;
        }

        $sql = sprintf(
            "INSERT INTO task (
                site_id,
                subject,
                description,
                status,
                priority,
                due_date,
                person_type,
                person_id,
                owner_id,
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
                %s,
                NOW(),
                NOW()
            )",
            $this->_siteID,
            $this->_db->makeQueryString($subject),
            $this->_db->makeQueryString($description),
            $this->_db->makeQueryString($status),
            $this->_db->makeQueryString($priority),
            ($dueDate !== null) ? $this->_db->makeQueryString($dueDate) : 'NULL',
            ($personType !== null) ? $this->_db->makeQueryString($personType) : 'NULL',
            ($personID !== null) ? $this->_db->makeQueryInteger($personID) : 'NULL',
            $this->_db->makeQueryInteger($ownerID)
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        $taskID = $this->_db->getLastInsertID();

        // Store history
        if (defined('DATA_ITEM_TASK'))
        {
            $history = new History($this->_siteID);
            $history->storeHistoryNew(DATA_ITEM_TASK, $taskID);
        }

        return $taskID;
    }


    /**
     * Returns all relevant task information for a given task ID.
     *
     * @param integer Task ID
     * @return array Task data
     */
    public function get($taskID)
    {
        $sql = sprintf(
            "SELECT
                task.task_id AS taskID,
                task.site_id AS siteID,
                task.subject AS subject,
                task.description AS description,
                task.status AS status,
                task.priority AS priority,
                task.due_date AS dueDate,
                DATE_FORMAT(task.due_date, '%%m-%%d-%%y') AS dueDateFormatted,
                task.person_type AS personType,
                task.person_id AS personID,
                task.owner_id AS ownerID,
                task.date_created AS dateCreated,
                DATE_FORMAT(task.date_created, '%%m-%%d-%%y (%%h:%%i %%p)') AS dateCreatedFormatted,
                task.date_modified AS dateModified,
                DATE_FORMAT(task.date_modified, '%%m-%%d-%%y (%%h:%%i %%p)') AS dateModifiedFormatted,
                task.date_completed AS dateCompleted,
                DATE_FORMAT(task.date_completed, '%%m-%%d-%%y (%%h:%%i %%p)') AS dateCompletedFormatted,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                CONCAT(owner_user.first_name, ' ', owner_user.last_name) AS ownerFullName
            FROM
                task
            LEFT JOIN user AS owner_user
                ON task.owner_id = owner_user.user_id
            WHERE
                task.task_id = %s
            AND
                task.site_id = %s",
            $this->_db->makeQueryInteger($taskID),
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }


    /**
     * Returns tasks owned by a specific user.
     *
     * @param integer Owner user ID
     * @param string Status filter (null for all)
     * @return array Tasks data
     */
    public function getByOwner($ownerID, $status = null)
    {
        $whereClause = sprintf(
            "task.site_id = %s AND task.owner_id = %s",
            $this->_siteID,
            $this->_db->makeQueryInteger($ownerID)
        );

        if ($status !== null)
        {
            $whereClause .= sprintf(" AND task.status = %s", $this->_db->makeQueryString($status));
        }

        $sql = sprintf(
            "SELECT
                task.task_id AS taskID,
                task.subject AS subject,
                task.description AS description,
                task.status AS status,
                task.priority AS priority,
                task.due_date AS dueDate,
                DATE_FORMAT(task.due_date, '%%m-%%d-%%y') AS dueDateFormatted,
                task.person_type AS personType,
                task.person_id AS personID,
                task.owner_id AS ownerID,
                task.date_created AS dateCreated,
                DATE_FORMAT(task.date_created, '%%m-%%d-%%y') AS dateCreatedFormatted,
                task.date_completed AS dateCompleted,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                task
            LEFT JOIN user AS owner_user
                ON task.owner_id = owner_user.user_id
            WHERE
                %s
            ORDER BY
                FIELD(task.priority, 'High', 'Normal', 'Low'),
                task.due_date ASC,
                task.date_created DESC",
            $whereClause
        );

        return $this->_db->getAllAssoc($sql);
    }


    /**
     * Returns tasks associated with a specific person/entity.
     *
     * @param string Person type ('candidate', 'contact', 'joborder', 'company')
     * @param integer Person/Entity ID
     * @param integer Maximum number of results to return
     * @param integer Offset for pagination
     * @return array Tasks data
     */
    public function getByPerson($personType, $personID, $limit = 100, $offset = 0)
    {
        $sql = sprintf(
            "SELECT
                task.task_id AS taskID,
                task.subject AS subject,
                task.description AS description,
                task.status AS status,
                task.priority AS priority,
                task.due_date AS dueDate,
                DATE_FORMAT(task.due_date, '%%m-%%d-%%y') AS dueDateFormatted,
                task.person_type AS personType,
                task.person_id AS personID,
                task.owner_id AS ownerID,
                task.date_created AS dateCreated,
                DATE_FORMAT(task.date_created, '%%m-%%d-%%y') AS dateCreatedFormatted,
                task.date_completed AS dateCompleted,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                task
            LEFT JOIN user AS owner_user
                ON task.owner_id = owner_user.user_id
            WHERE
                task.site_id = %s
            AND
                task.person_type = %s
            AND
                task.person_id = %s
            ORDER BY
                task.date_created DESC
            LIMIT %s OFFSET %s",
            $this->_siteID,
            $this->_db->makeQueryString($personType),
            $this->_db->makeQueryInteger($personID),
            $this->_db->makeQueryInteger($limit),
            $this->_db->makeQueryInteger($offset)
        );

        return $this->_db->getAllAssoc($sql);
    }


    /**
     * Returns a list of tasks with optional filtering.
     *
     * @param integer Maximum number of results to return
     * @param integer Offset for pagination
     * @param integer Owner ID filter (null for all)
     * @param string Status filter (null for all)
     * @return array Tasks data
     */
    public function getAll($limit = 100, $offset = 0, $ownerID = null, $status = null)
    {
        $whereClause = sprintf("task.site_id = %s", $this->_siteID);

        if ($ownerID !== null)
        {
            $whereClause .= sprintf(" AND task.owner_id = %s", $this->_db->makeQueryInteger($ownerID));
        }

        if ($status !== null)
        {
            $whereClause .= sprintf(" AND task.status = %s", $this->_db->makeQueryString($status));
        }

        $sql = sprintf(
            "SELECT
                task.task_id AS taskID,
                task.subject AS subject,
                task.description AS description,
                task.status AS status,
                task.priority AS priority,
                task.due_date AS dueDate,
                DATE_FORMAT(task.due_date, '%%m-%%d-%%y') AS dueDateFormatted,
                task.person_type AS personType,
                task.person_id AS personID,
                task.owner_id AS ownerID,
                task.date_created AS dateCreated,
                DATE_FORMAT(task.date_created, '%%m-%%d-%%y') AS dateCreatedFormatted,
                task.date_modified AS dateModified,
                task.date_completed AS dateCompleted,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                task
            LEFT JOIN user AS owner_user
                ON task.owner_id = owner_user.user_id
            WHERE
                %s
            ORDER BY
                FIELD(task.priority, 'High', 'Normal', 'Low'),
                task.due_date ASC,
                task.date_created DESC
            LIMIT %s OFFSET %s",
            $whereClause,
            $this->_db->makeQueryInteger($limit),
            $this->_db->makeQueryInteger($offset)
        );

        return $this->_db->getAllAssoc($sql);
    }


    /**
     * Updates a task record.
     *
     * @param integer Task ID
     * @param array Data to update (supports both camelCase and underscore field names):
     *              - subject / subject
     *              - description / description
     *              - status / status
     *              - priority / priority
     *              - dueDate / due_date
     *              - personType / person_type
     *              - personID / person_id
     *              - ownerID / owner_id
     * @return boolean True if successful; false otherwise
     */
    public function update($taskID, $data)
    {
        // Map camelCase to database column names
        $fieldMapping = array(
            'subject' => 'subject',
            'description' => 'description',
            'status' => 'status',
            'priority' => 'priority',
            'dueDate' => 'due_date',
            'due_date' => 'due_date',
            'personType' => 'person_type',
            'person_type' => 'person_type',
            'personID' => 'person_id',
            'person_id' => 'person_id',
            'ownerID' => 'owner_id',
            'owner_id' => 'owner_id'
        );

        // Numeric fields that should use makeQueryInteger or allow NULL
        $numericFields = array('person_id', 'owner_id');

        // Date fields that can be NULL
        $dateFields = array('due_date');

        // String fields that can be NULL
        $nullableStringFields = array('person_type');

        // Build SET clause
        $setClauses = array();
        foreach ($data as $key => $value)
        {
            if (!isset($fieldMapping[$key]))
            {
                continue;
            }

            $dbField = $fieldMapping[$key];

            // Validate status if provided
            if ($dbField === 'status' && !self::isValidStatus($value))
            {
                continue;
            }

            // Validate priority if provided
            if ($dbField === 'priority' && !self::isValidPriority($value))
            {
                continue;
            }

            if (in_array($dbField, $numericFields))
            {
                if ($value === null || $value === '')
                {
                    $setClauses[] = sprintf("%s = NULL", $dbField);
                }
                else
                {
                    $setClauses[] = sprintf("%s = %s", $dbField, $this->_db->makeQueryInteger($value));
                }
            }
            else if (in_array($dbField, $dateFields))
            {
                if ($value === null || $value === '')
                {
                    $setClauses[] = sprintf("%s = NULL", $dbField);
                }
                else
                {
                    $setClauses[] = sprintf("%s = %s", $dbField, $this->_db->makeQueryString($value));
                }
            }
            else if (in_array($dbField, $nullableStringFields))
            {
                if ($value === null || $value === '')
                {
                    $setClauses[] = sprintf("%s = NULL", $dbField);
                }
                else
                {
                    $setClauses[] = sprintf("%s = %s", $dbField, $this->_db->makeQueryString($value));
                }
            }
            else
            {
                $setClauses[] = sprintf("%s = %s", $dbField, $this->_db->makeQueryString($value));
            }
        }

        if (empty($setClauses))
        {
            return false;
        }

        // Add date_modified
        $setClauses[] = "date_modified = NOW()";

        // Get pre-update state for history
        $preHistory = $this->get($taskID);

        $sql = sprintf(
            "UPDATE
                task
            SET
                %s
            WHERE
                task_id = %s
            AND
                site_id = %s",
            implode(', ', $setClauses),
            $this->_db->makeQueryInteger($taskID),
            $this->_siteID
        );

        $queryResult = $this->_db->query($sql);

        if (!$queryResult)
        {
            return false;
        }

        // Get post-update state for history
        $postHistory = $this->get($taskID);

        // Store history changes
        if (defined('DATA_ITEM_TASK'))
        {
            $history = new History($this->_siteID);
            $history->storeHistoryChanges(DATA_ITEM_TASK, $taskID, $preHistory, $postHistory);
        }

        return true;
    }


    /**
     * Marks a task as completed.
     *
     * @param integer Task ID
     * @return boolean True if successful; false otherwise
     */
    public function complete($taskID)
    {
        // Get pre-update state for history
        $preHistory = $this->get($taskID);

        if (empty($preHistory))
        {
            return false;
        }

        $sql = sprintf(
            "UPDATE
                task
            SET
                status = %s,
                date_completed = NOW(),
                date_modified = NOW()
            WHERE
                task_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryString(self::STATUS_COMPLETED),
            $this->_db->makeQueryInteger($taskID),
            $this->_siteID
        );

        $queryResult = $this->_db->query($sql);

        if (!$queryResult)
        {
            return false;
        }

        // Get post-update state for history
        $postHistory = $this->get($taskID);

        // Store history changes
        if (defined('DATA_ITEM_TASK'))
        {
            $history = new History($this->_siteID);
            $history->storeHistoryChanges(DATA_ITEM_TASK, $taskID, $preHistory, $postHistory);
        }

        return true;
    }


    /**
     * Deletes a task record.
     *
     * @param integer Task ID
     * @return boolean True if successful; false otherwise
     */
    public function delete($taskID)
    {
        // Delete the task
        $sql = sprintf(
            "DELETE FROM
                task
            WHERE
                task_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($taskID),
            $this->_siteID
        );

        $queryResult = $this->_db->query($sql);

        if (!$queryResult)
        {
            return false;
        }

        // Store deletion in history
        if (defined('DATA_ITEM_TASK'))
        {
            $history = new History($this->_siteID);
            $history->storeHistoryDeleted(DATA_ITEM_TASK, $taskID);
        }

        return true;
    }


    /**
     * Returns the count of tasks with optional filtering.
     *
     * @param integer Owner ID filter (null for all)
     * @param string Status filter (null for all)
     * @return integer Count of tasks
     */
    public function getCount($ownerID = null, $status = null)
    {
        $whereClause = sprintf("site_id = %s", $this->_siteID);

        if ($ownerID !== null)
        {
            $whereClause .= sprintf(" AND owner_id = %s", $this->_db->makeQueryInteger($ownerID));
        }

        if ($status !== null)
        {
            $whereClause .= sprintf(" AND status = %s", $this->_db->makeQueryString($status));
        }

        $sql = sprintf(
            "SELECT
                COUNT(*) AS count
            FROM
                task
            WHERE
                %s",
            $whereClause
        );

        $rs = $this->_db->getAssoc($sql);

        if (empty($rs))
        {
            return 0;
        }

        return (int) $rs['count'];
    }


    /**
     * Returns an array of all valid task statuses.
     *
     * @return array Status values
     */
    public static function getStatuses()
    {
        return array(
            self::STATUS_NOT_STARTED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
            self::STATUS_DEFERRED
        );
    }


    /**
     * Returns an array of all valid task priorities.
     *
     * @return array Priority values
     */
    public static function getPriorities()
    {
        return array(
            self::PRIORITY_LOW,
            self::PRIORITY_NORMAL,
            self::PRIORITY_HIGH
        );
    }


    /**
     * Validates if a status value is valid.
     *
     * @param string Status to validate
     * @return boolean True if valid, false otherwise
     */
    public static function isValidStatus($status)
    {
        return in_array($status, self::getStatuses());
    }


    /**
     * Validates if a priority value is valid.
     *
     * @param string Priority to validate
     * @return boolean True if valid, false otherwise
     */
    public static function isValidPriority($priority)
    {
        return in_array($priority, self::getPriorities());
    }


    /**
     * Returns overdue tasks for a user.
     *
     * @param integer Owner user ID (null for all users)
     * @return array Overdue tasks
     */
    public function getOverdue($ownerID = null)
    {
        $whereClause = sprintf(
            "task.site_id = %s AND task.due_date < CURDATE() AND task.status != %s",
            $this->_siteID,
            $this->_db->makeQueryString(self::STATUS_COMPLETED)
        );

        if ($ownerID !== null)
        {
            $whereClause .= sprintf(" AND task.owner_id = %s", $this->_db->makeQueryInteger($ownerID));
        }

        $sql = sprintf(
            "SELECT
                task.task_id AS taskID,
                task.subject AS subject,
                task.description AS description,
                task.status AS status,
                task.priority AS priority,
                task.due_date AS dueDate,
                DATE_FORMAT(task.due_date, '%%m-%%d-%%y') AS dueDateFormatted,
                task.person_type AS personType,
                task.person_id AS personID,
                task.owner_id AS ownerID,
                task.date_created AS dateCreated,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                task
            LEFT JOIN user AS owner_user
                ON task.owner_id = owner_user.user_id
            WHERE
                %s
            ORDER BY
                task.due_date ASC,
                FIELD(task.priority, 'High', 'Normal', 'Low')",
            $whereClause
        );

        return $this->_db->getAllAssoc($sql);
    }


    /**
     * Returns tasks due today for a user.
     *
     * @param integer Owner user ID (null for all users)
     * @return array Tasks due today
     */
    public function getDueToday($ownerID = null)
    {
        $whereClause = sprintf(
            "task.site_id = %s AND task.due_date = CURDATE() AND task.status != %s",
            $this->_siteID,
            $this->_db->makeQueryString(self::STATUS_COMPLETED)
        );

        if ($ownerID !== null)
        {
            $whereClause .= sprintf(" AND task.owner_id = %s", $this->_db->makeQueryInteger($ownerID));
        }

        $sql = sprintf(
            "SELECT
                task.task_id AS taskID,
                task.subject AS subject,
                task.description AS description,
                task.status AS status,
                task.priority AS priority,
                task.due_date AS dueDate,
                DATE_FORMAT(task.due_date, '%%m-%%d-%%y') AS dueDateFormatted,
                task.person_type AS personType,
                task.person_id AS personID,
                task.owner_id AS ownerID,
                task.date_created AS dateCreated,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                task
            LEFT JOIN user AS owner_user
                ON task.owner_id = owner_user.user_id
            WHERE
                %s
            ORDER BY
                FIELD(task.priority, 'High', 'Normal', 'Low')",
            $whereClause
        );

        return $this->_db->getAllAssoc($sql);
    }
}

?>
