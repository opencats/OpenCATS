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

define('ACTIVITY_CALL',        100);
define('ACTIVITY_EMAIL',       200);
define('ACTIVITY_MEETING',     300);
define('ACTIVITY_OTHER',       400);
define('ACTIVITY_CALL_TALKED', 500);
define('ACTIVITY_CALL_LVM',    600);
define('ACTIVITY_CALL_MISSED', 700);
define('ACTIVITY_STATUS_CHANGE', 800);

/**
 * Candidates library.
 */
include_once(LEGACY_ROOT . '/lib/Candidates.php');

/**
 * Contacts library.
 */
include_once(LEGACY_ROOT . '/lib/Contacts.php');

/**
 * Job Orders library.
 */
include_once(LEGACY_ROOT . '/lib/JobOrders.php');


/**
 *  Activity Entries Library
 *  @package    CATS
 *  @subpackage Library
 */
class ActivityEntries
{
    private $_db;
    private $_mysqlDateTimeFormat;


    /** Small recorded-Activity preview for one readable selected source; no per-row histories. */
    public function getRecentForSource($type, $id, $limit = 3)
    {
        if (!is_int($limit) || $limit < 1 || $limit > 10) throw new InvalidArgumentException('Invalid preview size.');
        if (!isset($_SESSION['CATS']) || !$_SESSION['CATS']->isLoggedIn() ||
            $_SESSION['CATS']->getAccessLevel('activity.listByViewDataGrid') < ACCESS_LEVEL_READ) return null;
        include_once(LEGACY_ROOT . '/lib/Tasks.php');
        try { (new Tasks())->getParentContext($type, $id); }
        catch (RuntimeException $e) { return null; }
        $type = (int) $type;
        $id = (int) $id;
        $source = $type === DATA_ITEM_COMPANY ? '(a.data_item_type = 200 AND a.data_item_id = ' . $id . ') OR (a.data_item_type = 300 AND ct.company_id = ' . $id . ')' :
            ($type === DATA_ITEM_JOBORDER ? 'a.joborder_id = ' . $id : 'a.data_item_type = ' . $type . ' AND a.data_item_id = ' . $id);
        return $this->_db->getAllAssoc("SELECT a.activity_id AS activityID, a.notes,
            a.data_item_type AS dataItemType, a.data_item_id AS dataItemID,
            CASE a.data_item_type
                WHEN 100 THEN TRIM(CONCAT_WS(' ', c.first_name, c.last_name))
                WHEN 300 THEN TRIM(CONCAT_WS(' ', ct.first_name, ct.last_name))
                WHEN 200 THEN co.name
                WHEN 400 THEN j.title
            END AS subjectName,
            DATE_FORMAT(a.date_occurred, '" . str_replace('%%', '%', $this->_mysqlDateTimeFormat) . "') AS dateCreated,
            CASE WHEN a.data_item_type=400 AND a.type=800 THEN 'Status change' ELSE t.short_description END AS typeDescription, u.first_name AS enteredByFirstName, u.last_name AS enteredByLastName
            FROM activity a LEFT JOIN activity_type t ON t.activity_type_id = a.type
            LEFT JOIN user u ON u.user_id = a.entered_by
            LEFT JOIN candidate c ON a.data_item_type = 100 AND c.candidate_id = a.data_item_id
            LEFT JOIN contact ct ON a.data_item_type = 300 AND ct.contact_id = a.data_item_id
            LEFT JOIN company co ON a.data_item_type = 200 AND co.company_id = a.data_item_id
            LEFT JOIN joborder j ON j.joborder_id = a.joborder_id
            WHERE (" . $source . ") AND " . $this->readableSourceSQL() . "
            ORDER BY a.date_occurred DESC, a.activity_id DESC LIMIT " . $limit);
    }


    /** Recorded Activity, credited to entered_by; date boundaries use the session offset. */
    public function getOperationsRows($start, $end)
    {
        foreach (array($start, $end) as $date)
            if (!is_string($date) || !DateUtility::isValidDatabaseDateTime($date . ' 00:00:00'))
                throw new InvalidArgumentException('Invalid Activity period.');
        if (!isset($_SESSION['CATS']) || !$_SESSION['CATS']->isLoggedIn() || $_SESSION['CATS']->getUserID() <= 0 ||
            $_SESSION['CATS']->getAccessLevel('activity.listByViewDataGrid') < ACCESS_LEVEL_READ) return;
        $offset = (float) $_SESSION['CATS']->getTimeZoneOffset();
        $from = date('Y-m-d H:i:s', strtotime($start . ' 00:00:00') - (int) ($offset * 3600));
        $until = date('Y-m-d H:i:s', strtotime($end . ' 00:00:00 +1 day') - (int) ($offset * 3600));
        $after = 0;
        do
        {
            $rows = $this->_db->getAllAssoc("SELECT a.activity_id AS id, a.entered_by AS actor, a.type,
                a.data_item_type AS dataItemType, a.data_item_id AS dataItemID, a.joborder_id AS jobID,
                j.desk_id AS desk, DATE(DATE_ADD(a.date_occurred, INTERVAL " . (int) ($offset * 3600) . " SECOND)) AS sourceDate,
                CASE a.data_item_type WHEN 100 THEN CONCAT_WS(' ',c.first_name,c.last_name)
                    WHEN 200 THEN co.name WHEN 300 THEN CONCAT_WS(' ',ct.first_name,ct.last_name)
                    WHEN 400 THEN j.title END AS title
                FROM activity a
                LEFT JOIN candidate c ON a.data_item_type=100 AND c.candidate_id=a.data_item_id
                LEFT JOIN company co ON a.data_item_type=200 AND co.company_id=a.data_item_id
                LEFT JOIN contact ct ON a.data_item_type=300 AND ct.contact_id=a.data_item_id
                LEFT JOIN joborder j ON j.joborder_id=a.joborder_id
                WHERE " . $this->readableSourceSQL() . "
                AND a.date_occurred >= " . $this->_db->makeQueryString($from) . ' AND a.date_occurred < ' . $this->_db->makeQueryString($until) .
                ' AND a.activity_id > ' . $after . ' ORDER BY a.activity_id LIMIT 200');
            foreach ($rows as $row) { $after = (int) $row['id']; yield $row; }
        } while (count($rows) === 200);
    }

    /** Shared Activity subject and linked-Job visibility; aliases are fixed by both native reads. */
    private function readableSourceSQL()
    {
        $can = fn($action) => $_SESSION['CATS']->getAccessLevel($action) >= ACCESS_LEVEL_READ ? '1' : '0';
        $candidateHidden = $_SESSION['CATS']->getAccessLevel('candidates.hidden') >= ACCESS_LEVEL_SA ? '' : ' AND COALESCE(c.is_admin_hidden,0) <> 1';
        $jobHidden = $_SESSION['CATS']->getAccessLevel('joborders.hidden') >= ACCESS_LEVEL_SA ? '' : ' AND COALESCE(j.is_admin_hidden,0) <> 1';
        return "((a.data_item_type=100 AND " . $can('candidates.show') . " AND c.candidate_id IS NOT NULL" . $candidateHidden . ")
            OR (a.data_item_type=200 AND " . $can('companies.show') . " AND co.company_id IS NOT NULL)
            OR (a.data_item_type=300 AND " . $can('contacts.show') . " AND ct.contact_id IS NOT NULL)
            OR (a.data_item_type=400 AND a.data_item_id=a.joborder_id AND " . $can('joborders.show') . " AND j.joborder_id IS NOT NULL" . $jobHidden . "))
            AND (COALESCE(a.joborder_id,0)<=0 OR (" . $can('joborders.show') . " AND j.joborder_id IS NOT NULL" . $jobHidden . "))";
    }

    public function __construct()
    {
        $this->_db = DatabaseConnection::getInstance();

        $is24 = (isset($_SESSION['CATS']) && $_SESSION['CATS']->isTimeFormat24());
        $this->_mysqlDateTimeFormat = DateUtility::getMysqlDateTimeFormat($is24);
    }


    /**
     * Adds an activity entry to the database.
     *
     * @param integer Data Item ID.
     * @param flag Data Item type flag.
     * @param flag Activity type flag.
     * @param string Activity notes.
     * @param integer Entered-by user ID.
     * @param integer Job Order ID; -1 for general (stored as NULL).
     * @param string Date occurred timestamp (YYYY-MM-DD HH:MM:SS); false for NOW().
     * @return integer New Activity ID; -1 on failure.
     */
    public function add($dataItemID, $dataItemType, $activityType,
        $activityNotes, $enteredBy, $jobOrderID = -1, $dateOccurred = false)
    {
        if (!ctype_digit((string) $jobOrderID) || (int) $jobOrderID <= 0)
        {
            $jobOrderID = -1;
        }

        if (is_string($dateOccurred) &&
            preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $dateOccurred))
        {
            $dateOccurredSQL = $this->_db->makeQueryString($dateOccurred);
        }
        else
        {
            $dateOccurredSQL = 'NOW()';
        }

        $sql = sprintf(
            "INSERT INTO activity (
                data_item_id,
                data_item_type,
                joborder_id,
                entered_by,
                type,
                notes,
                date_occurred,
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
                NOW(),
                NOW()
            )",
            $this->_db->makeQueryInteger($dataItemID),
            $this->_db->makeQueryInteger($dataItemType),
            $this->_db->makeQueryIntegerOrNULL($jobOrderID),
            $this->_db->makeQueryInteger($enteredBy),
            $this->_db->makeQueryInteger($activityType),
            $this->_db->makeQueryString($activityNotes),
            $dateOccurredSQL
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        $activityEntryID = $this->_db->getLastInsertID();

        $history = new History();
        if (!$history->storeHistoryData(
            $dataItemType,
            $dataItemID,
            'ACTIVITY',
            '(NEW)',
            $activityNotes,
            '(USER) Added activity.'
        )) return -1;

        /* Update the last-modified timestamp for the "parent" Data Item. */
        $this->_updateDataItemModified($dataItemID, $dataItemType);

        /* If there is a job order being associated, update it's modified
         * timestamp, too.
         */
        if (!empty($jobOrderID) && ctype_digit((string) $jobOrderID) &&
            (int) $jobOrderID > 0)
        {
            $this->_updateDataItemModified($jobOrderID, DATA_ITEM_JOBORDER);
        }

        return $activityEntryID;
    }

    /**
     * Updates an activity entry with the information provided.
     *
     * @param integer Activity ID to update.
     * @param flag New activity type flag.
     * @param string New activity notes.
     * @param integer New Job Order ID; -1 for general (stored as NULL).
     * @return boolean True if successful; false otherwise.
     */
    public function update($activityID, $activityType, $activityNotes,
        $jobOrderID, $date, $timezoneOffset)
    {
        /* Get some extra information about the activity entry that we'll
         * need later on.
         */
        $sql = sprintf(
            "SELECT
                activity.data_item_id AS dataItemID,
                activity.data_item_type AS dataItemType,
                activity.joborder_id AS jobOrderID,
                activity.notes AS notes,
                activity.entered_by AS enteredBy,
                CONCAT(
                    user.first_name, ' ', user.last_name
                ) AS enteredByFullName
            FROM
                activity
            LEFT JOIN user AS user
                ON activity.entered_by = user.user_id
            WHERE
                activity.activity_id = %s",
            $this->_db->makeQueryInteger($activityID)
        );
        $activityIDRS = $this->_db->getAssoc($sql);

        if (!$activityIDRS)
        {
            return false;
        }

        /* If a job order ID wasn't specified, use the existing one. */
        if ($jobOrderID === false)
        {
            $newJobOrderID = $activityIDRS['jobOrderID'];
        }
        else
        {
            $newJobOrderID = $jobOrderID;
        }

        if (!ctype_digit((string) $newJobOrderID) || (int) $newJobOrderID <= 0)
        {
            $newJobOrderID = -1;
        }

        $sql = sprintf(
            "UPDATE
                activity
            SET
                type          = %s,
                notes         = %s,
                joborder_id   = %s,
                date_modified = NOW()
            WHERE
                activity_id = %s",
            $this->_db->makeQueryInteger($activityType),
            $this->_db->makeQueryString($activityNotes),
            $this->_db->makeQueryIntegerOrNULL($newJobOrderID),
            $this->_db->makeQueryInteger($activityID)
        );
        $queryResult = $this->_db->query($sql);

        if ($date !== false)
        {
            $sql = sprintf(
                "UPDATE
                    activity
                SET
                    date_occurred = DATE_SUB(%s, INTERVAL %s HOUR),
                    date_modified = NOW()
                WHERE
                    activity_id = %s",
                $this->_db->makeQueryString($date),
                $this->_db->makeQueryInteger($timezoneOffset),
                $this->_db->makeQueryInteger($activityID)
            );

            $queryResult = $this->_db->query($sql);
        }

        $history = new History();
        $history->storeHistoryData(
            $activityIDRS['dataItemType'],
            $activityIDRS['dataItemID'],
            'ACTIVITY',
            $activityIDRS['notes'],
            $activityNotes,
            '(USER) Edited ' . $activityIDRS['enteredByFullName'] . '\'s activity.'
        );

        if (!$queryResult)
        {
            return false;
        }

        /* Update the last-modified timestamp for the "parent" Data Item. */
        $this->_updateDataItemModified(
            $activityIDRS['dataItemID'], $activityIDRS['dataItemType']
        );

        /* If there is a job order being associated, update it's modified
         * timestamp, too.
         */
        if (!empty($jobOrderID) && ctype_digit((string) $jobOrderID) &&
            (int) $jobOrderID > 0)
        {
            $this->_updateDataItemModified($jobOrderID, DATA_ITEM_JOBORDER);
        }

        /* The job order ID may have been changed. If it has, and the new one
         * is valid, update its modified timestamp, too.
         */
        if (!empty($newJobOrderID) && ctype_digit((string) $newJobOrderID) &&
            (int) $newJobOrderID > 0 && $jobOrderID != $newJobOrderID)
        {
            $this->_updateDataItemModified($newJobOrderID, DATA_ITEM_JOBORDER);
        }

        return true;
    }

    /**
     * Removes an activity note from the system.
     *
     * @param integer Activity ID.
     * @return boolean True if successful; false otherwise.
     */
    public function delete($activityID)
    {
        $sql = sprintf(
            "SELECT
                activity.data_item_id AS dataItemID,
                activity.data_item_type AS dataItemType,
                activity.joborder_id AS jobOrderID,
                activity.notes AS notes,
                activity.entered_by AS enteredBy,
                CONCAT(
                    user.first_name, ' ', user.last_name
                ) AS enteredByFullName
            FROM
                activity
            LEFT JOIN user AS user
                ON activity.entered_by = user.user_id
            WHERE
                activity.activity_id = %s",
            $this->_db->makeQueryInteger($activityID)
        );
        $activityIDRS = $this->_db->getAssoc($sql);

        if (!$activityIDRS)
        {
            return false;
        }

        $sql = sprintf(
            "DELETE FROM
                activity
            WHERE
                activity_id = %s",
            $this->_db->makeQueryInteger($activityID)
        );
        $queryResult = $this->_db->query($sql);

        if (!$queryResult)
        {
            return false;
        }

        $history = new History();
        $history->storeHistoryData(
            $activityIDRS['dataItemType'],
            $activityIDRS['dataItemID'],
            'ACTIVITY',
            $activityIDRS['notes'],
            '(DELETE)',
            '(USER) Deleted ' . $activityIDRS['enteredByFullName'] . '\'s activity.'
        );

        /* Update the last-modified timestamp for the "parent" Data Item. */
        $this->_updateDataItemModified(
            $activityIDRS['dataItemID'], $activityIDRS['dataItemType']
        );

        /* If there is a job order associated, update it's modified timestamp,
         * too.
         */
        if (!empty($activityIDRS['jobOrderID']) &&
            ctype_digit((string) $activityIDRS['jobOrderID']))
        {
            $this->_updateDataItemModified(
                $activityIDRS['jobOrderID'], DATA_ITEM_JOBORDER
            );
        }

        return true;
    }

    /**
     * Returns number of total activities (for activities datagrid).
     *
     * @return integer count
     */
    public function getCount()
    {
        $sql = "SELECT
                COUNT(*) AS totalActivities
            FROM
                activity";

        return $this->_db->getColumn($sql, 0, 0);
    }

    /**
     * Returns an activity entry.
     *
     * @param integer Activity ID.
     * @return array Activity data.
     */
    public function get($activityID)
    {
        $sql = sprintf(
            "SELECT
                activity.activity_id AS activityID,
                activity.data_item_id AS dataItemID,
                activity.data_item_type AS dataItemType,
                activity.joborder_id AS jobOrderID,
                activity.type AS type,
                activity_type.short_description AS typeDescription,
                activity.notes AS notes,
                DATE_FORMAT(
                    activity.date_occurred, '" . $this->_mysqlDateTimeFormat . "'
                ) AS dateCreated,
                entered_by_user.first_name AS enteredByFirstName,
                entered_by_user.last_name AS enteredByLastName,
                IF(
                    ISNULL(joborder.title),
                    'General',
                    CONCAT(joborder.title, ' (', company.name, ')')
                ) AS regarding,
                joborder.title AS regardingJobTitle,
                company.name AS regardingCompanyName
            FROM
                activity
            LEFT JOIN activity_type
                ON activity.type = activity_type.activity_type_id
            LEFT JOIN user AS entered_by_user
                ON activity.entered_by = entered_by_user.user_id
            LEFT JOIN joborder
                ON activity.joborder_id = joborder.joborder_id
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            WHERE
                activity.activity_id = %s",
            $this->_db->makeQueryInteger($activityID)
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Apply the parent module's existing activity permissions, not authorship.
     */
    public function canModify($activity, $delete = false)
    {
        if (empty($activity) || empty($activity['dataItemID']))
        {
            return false;
        }

        switch ($activity['dataItemType'])
        {
            case DATA_ITEM_CANDIDATE:
                $module = 'candidates';
                $permission = $delete ? 'candidates.delete' : 'candidates.edit';
                $requiredLevel = $delete ? ACCESS_LEVEL_DELETE : ACCESS_LEVEL_EDIT;
                break;

            case DATA_ITEM_CONTACT:
                $module = 'contacts';
                $permission = $delete ? 'contacts.deleteActivity' : 'contacts.editActivity';
                $requiredLevel = ACCESS_LEVEL_EDIT;
                break;

            default:
                // Normal activity UI supports candidates and contacts only.
                // The company page aggregates its contacts' activities.
                return false;
        }

        if ($_SESSION['CATS']->getAccessLevel($permission) < $requiredLevel ||
            $_SESSION['CATS']->getAccessLevel($module . '.show') < ACCESS_LEVEL_READ)
        {
            return false;
        }

        if ($activity['dataItemType'] == DATA_ITEM_CANDIDATE)
        {
            include_once(LEGACY_ROOT . '/lib/CandidateAuthorization.php');
            return CandidateAuthorization::canAccessCandidate($activity['dataItemID']);
        }

        $contacts = new Contacts();
        return !empty($contacts->get($activity['dataItemID']));
    }

    /**
     * Returns all activity entries for a Data Item.
     *
     * @param integer Data Item ID.
     * @param flag Data Item type flag.
     * @return resultset Activity entries data.
     */
    public function getAllByDataItem($dataItemID, $dataItemType)
    {
        $sql = sprintf(
            "SELECT
                activity.activity_id AS activityID,
                activity.data_item_id AS dataItemID,
                activity.joborder_id AS jobOrderID,
                activity.notes AS notes,
                DATE_FORMAT(
                    activity.date_occurred, '" . $this->_mysqlDateTimeFormat . "'
                ) AS dateCreated,
                activity.date_occurred AS dateCreatedSort,
                activity.type AS type,
                CASE WHEN activity.data_item_type=400 AND activity.type=800 THEN 'Status change' ELSE activity_type.short_description END AS typeDescription,
                entered_by_user.first_name AS enteredByFirstName,
                entered_by_user.last_name AS enteredByLastName,
                IF(
                    ISNULL(joborder.title),
                    'General',
                    CONCAT(joborder.title, ' (', company.name, ')')
                ) AS regarding,
                joborder.title AS regardingJobTitle,
                company.name AS regardingCompanyName
            FROM
                activity
            LEFT JOIN user AS entered_by_user
                ON activity.entered_by = entered_by_user.user_id
            LEFT JOIN activity_type
                ON activity.type = activity_type.activity_type_id
            LEFT JOIN joborder
                ON activity.joborder_id = joborder.joborder_id
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            WHERE
                activity.data_item_id = %s
            AND
                activity.data_item_type = %s
            ORDER BY
                dateCreatedSort DESC,
                activity.activity_id DESC",
            $this->_db->makeQueryInteger($dataItemID),
            $this->_db->makeQueryInteger($dataItemType)
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Like getAllByCompany(), Contact evidence follows the current employer.
     * Departed Contacts are excluded; historical employer attribution is not stored.
     * Email has no direct/bulk or engagement outcome; it cannot prove contact.
     */
    public function getRelationshipContactDates($companyIDs)
    {
        if (!is_array($companyIDs)) throw new InvalidArgumentException('Invalid Company IDs.');
        if (!isset($_SESSION['CATS']) || !$_SESSION['CATS']->isLoggedIn() ||
            $_SESSION['CATS']->getUserID() <= 0 ||
            $_SESSION['CATS']->getAccessLevel('companies.show') < ACCESS_LEVEL_READ ||
            $_SESSION['CATS']->getAccessLevel('contacts.show') < ACCESS_LEVEL_READ ||
            $_SESSION['CATS']->getAccessLevel('activity.listByViewDataGrid') < ACCESS_LEVEL_READ)
            return array();
        $ids = array();
        foreach ($companyIDs as $id)
        {
            if ((!is_int($id) && !is_string($id)) || !preg_match('/^[1-9][0-9]{0,9}$/D', (string) $id) ||
                (float) $id > 2147483647) throw new InvalidArgumentException('Invalid Company ID.');
            $ids[] = (int) $id;
        }
        if (!$ids) return array();
        $scope = 'company.company_id IN (' . implode(',', array_unique($ids)) . ')
            AND company.owner = ' . (int) $_SESSION['CATS']->getUserID() . '
            AND activity.type IN (' . ACTIVITY_CALL_TALKED . ', ' . ACTIVITY_MEETING . ')
            AND activity.date_occurred >= company.date_created
            AND activity.date_occurred > \'1000-01-01 00:00:00\'
            AND activity.date_occurred <= NOW()
            AND DAYOFMONTH(activity.date_occurred) BETWEEN 1 AND DAYOFMONTH(LAST_DAY(activity.date_occurred))';
        $rows = $this->_db->getAllAssoc('SELECT companyID, MAX(lastContact) AS lastContact FROM (
            SELECT company.company_id AS companyID, MAX(activity.date_occurred) AS lastContact
            FROM company INNER JOIN activity ON activity.data_item_id = company.company_id
                AND activity.data_item_type = ' . DATA_ITEM_COMPANY . '
            WHERE ' . $scope . '
            GROUP BY company.company_id
            UNION ALL
            SELECT company.company_id AS companyID, MAX(activity.date_occurred) AS lastContact
            FROM company INNER JOIN contact ON contact.company_id = company.company_id AND contact.left_company = 0
            INNER JOIN activity ON activity.data_item_id = contact.contact_id
                AND activity.data_item_type = ' . DATA_ITEM_CONTACT . '
            WHERE ' . $scope . '
            GROUP BY company.company_id
            ) AS contact_dates GROUP BY companyID');
        return array_column($rows, 'lastContact', 'companyID');
    }

    /**
     * Returns all activity entries for contacts belonging to a company.
     *
     * @param integer Company ID.
     * @return resultset Activity entries data.
     */
    public function getAllByCompany($companyID)
    {
        $sql = sprintf(
            "SELECT
                activity.activity_id AS activityID,
                activity.data_item_id AS dataItemID,
                activity.joborder_id AS jobOrderID,
                activity.notes AS notes,
                DATE_FORMAT(
                    activity.date_occurred, '" . $this->_mysqlDateTimeFormat . "'
                ) AS dateCreated,
                activity.date_occurred AS dateCreatedSort,
                activity.type AS type,
                activity_type.short_description AS typeDescription,
                entered_by_user.first_name AS enteredByFirstName,
                entered_by_user.last_name AS enteredByLastName,
                contact.contact_id AS contactID,
                contact.first_name AS contactFirstName,
                contact.last_name AS contactLastName,
                IF(
                    ISNULL(joborder.title),
                    'General',
                    CONCAT(joborder.title, ' (', company.name, ')')
                ) AS regarding,
                joborder.title AS regardingJobTitle,
                company.name AS regardingCompanyName
            FROM
                activity
            LEFT JOIN user AS entered_by_user
                ON activity.entered_by = entered_by_user.user_id
            LEFT JOIN activity_type
                ON activity.type = activity_type.activity_type_id
            LEFT JOIN joborder
                ON activity.joborder_id = joborder.joborder_id
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            INNER JOIN contact
                ON activity.data_item_id = contact.contact_id
            WHERE
                contact.company_id = %s
            AND
                activity.data_item_type = %s
            ORDER BY
                dateCreatedSort DESC,
                activity.activity_id DESC",
            $this->_db->makeQueryInteger($companyID),
            $this->_db->makeQueryInteger(DATA_ITEM_CONTACT)
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all activity types and their descriptions.
     *
     * @return resultset Activity type IDs and descriptions.
     */
    public function getTypes()
    {
        $sql = "SELECT
                activity_type_id AS typeID,
                short_description AS type
            FROM
                activity_type
            ORDER BY
                activity_type_id DESC";

        return $this->_db->getAllAssoc($sql);
    }


    /**
     * Updates a Data Item's modified timestamp.
     *
     * @param integer Data Item ID.
     * @param flag Data Item type flag.
     * @return void
     */
    private function _updateDataItemModified($dataItemID, $dataItemType)
    {
        switch ($dataItemType)
        {
            case DATA_ITEM_CANDIDATE:
                $dataItem = new Candidates();
                break;

            case DATA_ITEM_COMPANY:
                $dataItem = new Companies();
                break;

            case DATA_ITEM_CONTACT:
                $dataItem = new Contacts();
                break;

            case DATA_ITEM_JOBORDER:
                $dataItem = new JobOrders();
                break;

            default:
                return;
                break;
        }

        $dataItem->updateModified($dataItemID);
    }
}

?>
