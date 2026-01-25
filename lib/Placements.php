<?php
/**
 * CATS
 * Placements Library
 *
 * Manages hired candidates (successful job placements).
 * Bullhorn-compatible placement entity for tracking:
 * - Salary, fees, bill/pay rates
 * - Status workflow (Active, Completed, Terminated)
 *
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 */

include_once(LEGACY_ROOT . '/lib/History.php');

/**
 * Placements Library
 * @package    CATS
 * @subpackage Library
 */
class Placements
{
    // Placement status constants
    const STATUS_ACTIVE = 'Active';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_TERMINATED = 'Terminated';

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
     * Creates a new placement record.
     *
     * @param integer Candidate ID
     * @param integer Job Order ID
     * @param integer Company ID
     * @param string Start date (YYYY-MM-DD format)
     * @param integer User ID who is creating this
     * @param array Optional additional data:
     *              - salary: Salary amount
     *              - salaryType: 'Yearly', 'Hourly', 'Daily'
     *              - fee: Placement fee amount
     *              - feeType: 'Percentage', 'Flat'
     *              - billRate: Bill rate per hour/day
     *              - payRate: Pay rate per hour/day
     *              - endDate: Employment end date
     *              - contactID: Primary contact at company
     *              - notes: Additional notes
     *              - status: Placement status
     *              - ownerID: Owner user ID
     *              - referralFee: Referral fee if applicable
     * @return integer New placement ID, or -1 on failure
     */
    public function add($candidateID, $jobOrderID, $companyID, $startDate, $userID, $data = array())
    {
        // Check if placement already exists for this candidate/job combination
        $existing = $this->getByCandidateAndJob($candidateID, $jobOrderID);
        if (!empty($existing))
        {
            // Placement already exists
            return -1;
        }

        // Extract optional fields with defaults
        $salary = isset($data['salary']) ? $data['salary'] : null;
        $salaryType = isset($data['salaryType']) ? $data['salaryType'] : 'Yearly';
        $fee = isset($data['fee']) ? $data['fee'] : null;
        $feeType = isset($data['feeType']) ? $data['feeType'] : 'Percentage';
        $billRate = isset($data['billRate']) ? $data['billRate'] : null;
        $payRate = isset($data['payRate']) ? $data['payRate'] : null;
        $endDate = isset($data['endDate']) ? $data['endDate'] : null;
        $contactID = isset($data['contactID']) ? $data['contactID'] : null;
        $notes = isset($data['notes']) ? $data['notes'] : '';
        $status = isset($data['status']) ? $data['status'] : self::STATUS_ACTIVE;
        $ownerID = isset($data['ownerID']) ? $data['ownerID'] : $userID;
        $referralFee = isset($data['referralFee']) ? $data['referralFee'] : null;

        $sql = sprintf(
            "INSERT INTO placement (
                site_id,
                candidate_id,
                joborder_id,
                company_id,
                contact_id,
                status,
                start_date,
                end_date,
                salary,
                salary_type,
                fee,
                fee_type,
                bill_rate,
                pay_rate,
                referral_fee,
                notes,
                date_created,
                date_modified,
                created_by,
                owner
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
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                NOW(),
                NOW(),
                %s,
                %s
            )",
            $this->_siteID,
            $this->_db->makeQueryInteger($candidateID),
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_db->makeQueryInteger($companyID),
            ($contactID !== null) ? $this->_db->makeQueryInteger($contactID) : 'NULL',
            $this->_db->makeQueryString($status),
            $this->_db->makeQueryString($startDate),
            ($endDate !== null) ? $this->_db->makeQueryString($endDate) : 'NULL',
            ($salary !== null) ? $this->_db->makeQueryString($salary) : 'NULL',
            $this->_db->makeQueryString($salaryType),
            ($fee !== null) ? $this->_db->makeQueryString($fee) : 'NULL',
            $this->_db->makeQueryString($feeType),
            ($billRate !== null) ? $this->_db->makeQueryString($billRate) : 'NULL',
            ($payRate !== null) ? $this->_db->makeQueryString($payRate) : 'NULL',
            ($referralFee !== null) ? $this->_db->makeQueryString($referralFee) : 'NULL',
            $this->_db->makeQueryString($notes),
            $this->_db->makeQueryInteger($userID),
            $this->_db->makeQueryInteger($ownerID)
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        $placementID = $this->_db->getLastInsertID();

        // Store history
        $history = new History($this->_siteID);
        $history->storeHistoryNew(DATA_ITEM_PLACEMENT, $placementID);

        return $placementID;
    }


    /**
     * Returns all relevant placement information for a given placement ID.
     *
     * @param integer Placement ID
     * @return array Placement data
     */
    public function get($placementID)
    {
        $sql = sprintf(
            "SELECT
                placement.placement_id AS placementID,
                placement.site_id AS siteID,
                placement.candidate_id AS candidateID,
                placement.joborder_id AS jobOrderID,
                placement.company_id AS companyID,
                placement.contact_id AS contactID,
                placement.status AS status,
                placement.start_date AS startDate,
                DATE_FORMAT(placement.start_date, '%%m-%%d-%%y') AS startDateFormatted,
                placement.end_date AS endDate,
                DATE_FORMAT(placement.end_date, '%%m-%%d-%%y') AS endDateFormatted,
                placement.salary AS salary,
                placement.salary_type AS salaryType,
                placement.fee AS fee,
                placement.fee_type AS feeType,
                placement.bill_rate AS billRate,
                placement.pay_rate AS payRate,
                placement.referral_fee AS referralFee,
                placement.notes AS notes,
                placement.date_created AS dateCreated,
                DATE_FORMAT(placement.date_created, '%%m-%%d-%%y (%%h:%%i %%p)') AS dateCreatedFormatted,
                placement.date_modified AS dateModified,
                DATE_FORMAT(placement.date_modified, '%%m-%%d-%%y (%%h:%%i %%p)') AS dateModifiedFormatted,
                placement.created_by AS createdBy,
                placement.owner AS ownerID,
                candidate.first_name AS candidateFirstName,
                candidate.last_name AS candidateLastName,
                CONCAT(candidate.first_name, ' ', candidate.last_name) AS candidateFullName,
                candidate.email1 AS candidateEmail,
                joborder.title AS jobOrderTitle,
                joborder.type AS jobOrderType,
                company.name AS companyName,
                contact.first_name AS contactFirstName,
                contact.last_name AS contactLastName,
                CONCAT(contact.first_name, ' ', contact.last_name) AS contactFullName,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                CONCAT(owner_user.first_name, ' ', owner_user.last_name) AS ownerFullName,
                created_user.first_name AS createdByFirstName,
                created_user.last_name AS createdByLastName,
                CONCAT(created_user.first_name, ' ', created_user.last_name) AS createdByFullName
            FROM
                placement
            LEFT JOIN candidate
                ON placement.candidate_id = candidate.candidate_id
            LEFT JOIN joborder
                ON placement.joborder_id = joborder.joborder_id
            LEFT JOIN company
                ON placement.company_id = company.company_id
            LEFT JOIN contact
                ON placement.contact_id = contact.contact_id
            LEFT JOIN user AS owner_user
                ON placement.owner = owner_user.user_id
            LEFT JOIN user AS created_user
                ON placement.created_by = created_user.user_id
            WHERE
                placement.placement_id = %s
            AND
                placement.site_id = %s",
            $this->_db->makeQueryInteger($placementID),
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }


    /**
     * Returns a list of placements with optional filtering.
     *
     * @param integer Maximum number of results to return
     * @param integer Offset for pagination
     * @param string Status filter (null for all)
     * @param integer Candidate ID filter (null for all)
     * @param integer Company ID filter (null for all)
     * @return array Placements data
     */
    public function getAll($limit = 100, $offset = 0, $status = null, $candidateID = null, $companyID = null)
    {
        $whereClause = sprintf("placement.site_id = %s", $this->_siteID);

        if ($status !== null)
        {
            $whereClause .= sprintf(" AND placement.status = %s", $this->_db->makeQueryString($status));
        }

        if ($candidateID !== null)
        {
            $whereClause .= sprintf(" AND placement.candidate_id = %s", $this->_db->makeQueryInteger($candidateID));
        }

        if ($companyID !== null)
        {
            $whereClause .= sprintf(" AND placement.company_id = %s", $this->_db->makeQueryInteger($companyID));
        }

        $sql = sprintf(
            "SELECT
                placement.placement_id AS placementID,
                placement.candidate_id AS candidateID,
                placement.joborder_id AS jobOrderID,
                placement.company_id AS companyID,
                placement.contact_id AS contactID,
                placement.status AS status,
                placement.start_date AS startDate,
                DATE_FORMAT(placement.start_date, '%%m-%%d-%%y') AS startDateFormatted,
                placement.end_date AS endDate,
                DATE_FORMAT(placement.end_date, '%%m-%%d-%%y') AS endDateFormatted,
                placement.salary AS salary,
                placement.salary_type AS salaryType,
                placement.fee AS fee,
                placement.fee_type AS feeType,
                placement.bill_rate AS billRate,
                placement.pay_rate AS payRate,
                placement.date_created AS dateCreated,
                DATE_FORMAT(placement.date_created, '%%m-%%d-%%y') AS dateCreatedFormatted,
                placement.owner AS ownerID,
                candidate.first_name AS candidateFirstName,
                candidate.last_name AS candidateLastName,
                CONCAT(candidate.first_name, ' ', candidate.last_name) AS candidateFullName,
                joborder.title AS jobOrderTitle,
                company.name AS companyName,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                placement
            LEFT JOIN candidate
                ON placement.candidate_id = candidate.candidate_id
            LEFT JOIN joborder
                ON placement.joborder_id = joborder.joborder_id
            LEFT JOIN company
                ON placement.company_id = company.company_id
            LEFT JOIN user AS owner_user
                ON placement.owner = owner_user.user_id
            WHERE
                %s
            ORDER BY
                placement.date_created DESC
            LIMIT %s OFFSET %s",
            $whereClause,
            $this->_db->makeQueryInteger($limit),
            $this->_db->makeQueryInteger($offset)
        );

        return $this->_db->getAllAssoc($sql);
    }


    /**
     * Returns the count of placements with optional filtering.
     *
     * @param string Status filter (null for all)
     * @param integer Candidate ID filter (null for all)
     * @param integer Company ID filter (null for all)
     * @return integer Count of placements
     */
    public function getCount($status = null, $candidateID = null, $companyID = null)
    {
        $whereClause = sprintf("site_id = %s", $this->_siteID);

        if ($status !== null)
        {
            $whereClause .= sprintf(" AND status = %s", $this->_db->makeQueryString($status));
        }

        if ($candidateID !== null)
        {
            $whereClause .= sprintf(" AND candidate_id = %s", $this->_db->makeQueryInteger($candidateID));
        }

        if ($companyID !== null)
        {
            $whereClause .= sprintf(" AND company_id = %s", $this->_db->makeQueryInteger($companyID));
        }

        $sql = sprintf(
            "SELECT
                COUNT(*) AS count
            FROM
                placement
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
     * Updates a placement record.
     *
     * @param integer Placement ID
     * @param array Data to update (supports both camelCase and underscore field names):
     *              - salary / salary
     *              - salaryType / salary_type
     *              - fee / fee
     *              - feeType / fee_type
     *              - billRate / bill_rate
     *              - payRate / pay_rate
     *              - startDate / start_date
     *              - endDate / end_date
     *              - contactID / contact_id
     *              - notes / notes
     *              - status / status
     *              - ownerID / owner
     *              - referralFee / referral_fee
     * @return boolean True if successful; false otherwise
     */
    public function update($placementID, $data)
    {
        // Map camelCase to database column names
        $fieldMapping = array(
            'salary' => 'salary',
            'salaryType' => 'salary_type',
            'salary_type' => 'salary_type',
            'fee' => 'fee',
            'feeType' => 'fee_type',
            'fee_type' => 'fee_type',
            'billRate' => 'bill_rate',
            'bill_rate' => 'bill_rate',
            'payRate' => 'pay_rate',
            'pay_rate' => 'pay_rate',
            'startDate' => 'start_date',
            'start_date' => 'start_date',
            'endDate' => 'end_date',
            'end_date' => 'end_date',
            'contactID' => 'contact_id',
            'contact_id' => 'contact_id',
            'notes' => 'notes',
            'status' => 'status',
            'ownerID' => 'owner',
            'owner' => 'owner',
            'referralFee' => 'referral_fee',
            'referral_fee' => 'referral_fee'
        );

        // Numeric fields that should use makeQueryInteger or allow NULL
        $numericFields = array('salary', 'fee', 'bill_rate', 'pay_rate', 'contact_id', 'owner', 'referral_fee');

        // Date fields that can be NULL
        $dateFields = array('start_date', 'end_date');

        // Build SET clause
        $setClauses = array();
        foreach ($data as $key => $value)
        {
            if (!isset($fieldMapping[$key]))
            {
                continue;
            }

            $dbField = $fieldMapping[$key];

            if (in_array($dbField, $numericFields))
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
        $preHistory = $this->get($placementID);

        $sql = sprintf(
            "UPDATE
                placement
            SET
                %s
            WHERE
                placement_id = %s
            AND
                site_id = %s",
            implode(', ', $setClauses),
            $this->_db->makeQueryInteger($placementID),
            $this->_siteID
        );

        $queryResult = $this->_db->query($sql);

        if (!$queryResult)
        {
            return false;
        }

        // Get post-update state for history
        $postHistory = $this->get($placementID);

        // Store history changes
        $history = new History($this->_siteID);
        $history->storeHistoryChanges(DATA_ITEM_PLACEMENT, $placementID, $preHistory, $postHistory);

        return true;
    }


    /**
     * Deletes a placement record.
     *
     * @param integer Placement ID
     * @return boolean True if successful; false otherwise
     */
    public function delete($placementID)
    {
        // Delete the placement
        $sql = sprintf(
            "DELETE FROM
                placement
            WHERE
                placement_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($placementID),
            $this->_siteID
        );

        $queryResult = $this->_db->query($sql);

        if (!$queryResult)
        {
            return false;
        }

        // Store deletion in history
        $history = new History($this->_siteID);
        $history->storeHistoryDeleted(DATA_ITEM_PLACEMENT, $placementID);

        // Delete placement history records (cascades via FK, but explicit for clarity)
        $sql = sprintf(
            "DELETE FROM
                placement_history
            WHERE
                placement_id = %s",
            $this->_db->makeQueryInteger($placementID)
        );
        $this->_db->query($sql);

        return true;
    }


    /**
     * Finds an existing placement by candidate ID and job order ID.
     *
     * @param integer Candidate ID
     * @param integer Job Order ID
     * @return array Placement data or empty array if not found
     */
    public function getByCandidateAndJob($candidateID, $jobOrderID)
    {
        $sql = sprintf(
            "SELECT
                placement.placement_id AS placementID,
                placement.candidate_id AS candidateID,
                placement.joborder_id AS jobOrderID,
                placement.company_id AS companyID,
                placement.status AS status,
                placement.start_date AS startDate,
                DATE_FORMAT(placement.start_date, '%%m-%%d-%%y') AS startDateFormatted,
                placement.salary AS salary,
                placement.fee AS fee,
                placement.date_created AS dateCreated
            FROM
                placement
            WHERE
                placement.candidate_id = %s
            AND
                placement.joborder_id = %s
            AND
                placement.site_id = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }


    /**
     * Returns all placements for a given candidate.
     *
     * @param integer Candidate ID
     * @return array Placements data
     */
    public function getByCandidate($candidateID)
    {
        return $this->getAll(100, 0, null, $candidateID, null);
    }


    /**
     * Returns all placements for a given company.
     *
     * @param integer Company ID
     * @return array Placements data
     */
    public function getByCompany($companyID)
    {
        return $this->getAll(100, 0, null, null, $companyID);
    }


    /**
     * Returns all placements for a given job order.
     *
     * @param integer Job Order ID
     * @return array Placements data
     */
    public function getByJobOrder($jobOrderID)
    {
        $sql = sprintf(
            "SELECT
                placement.placement_id AS placementID,
                placement.candidate_id AS candidateID,
                placement.joborder_id AS jobOrderID,
                placement.company_id AS companyID,
                placement.status AS status,
                placement.start_date AS startDate,
                DATE_FORMAT(placement.start_date, '%%m-%%d-%%y') AS startDateFormatted,
                placement.salary AS salary,
                placement.fee AS fee,
                placement.date_created AS dateCreated,
                candidate.first_name AS candidateFirstName,
                candidate.last_name AS candidateLastName,
                CONCAT(candidate.first_name, ' ', candidate.last_name) AS candidateFullName
            FROM
                placement
            LEFT JOIN candidate
                ON placement.candidate_id = candidate.candidate_id
            WHERE
                placement.joborder_id = %s
            AND
                placement.site_id = %s
            ORDER BY
                placement.date_created DESC",
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }


    /**
     * Returns an array of all valid placement statuses.
     *
     * @return array Status values
     */
    public static function getStatuses()
    {
        return array(
            self::STATUS_ACTIVE,
            self::STATUS_COMPLETED,
            self::STATUS_TERMINATED
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
     * Returns placement statistics for a given date range.
     *
     * @param string Start date (YYYY-MM-DD)
     * @param string End date (YYYY-MM-DD)
     * @return array Statistics data
     */
    public function getStatistics($startDate, $endDate)
    {
        $sql = sprintf(
            "SELECT
                COUNT(*) AS totalPlacements,
                SUM(CASE WHEN status = %s THEN 1 ELSE 0 END) AS activePlacements,
                SUM(CASE WHEN status = %s THEN 1 ELSE 0 END) AS completedPlacements,
                SUM(CASE WHEN status = %s THEN 1 ELSE 0 END) AS terminatedPlacements,
                AVG(salary) AS averageSalary,
                SUM(CASE WHEN fee_type = 'Flat' THEN fee ELSE 0 END) AS totalFlatFees,
                SUM(CASE WHEN fee_type = 'Percentage' THEN (fee * salary / 100) ELSE 0 END) AS totalPercentageFees,
                AVG(bill_rate) AS averageBillRate,
                AVG(pay_rate) AS averagePayRate
            FROM
                placement
            WHERE
                site_id = %s
            AND
                start_date >= %s
            AND
                start_date <= %s",
            $this->_db->makeQueryString(self::STATUS_ACTIVE),
            $this->_db->makeQueryString(self::STATUS_COMPLETED),
            $this->_db->makeQueryString(self::STATUS_TERMINATED),
            $this->_siteID,
            $this->_db->makeQueryString($startDate),
            $this->_db->makeQueryString($endDate)
        );

        return $this->_db->getAssoc($sql);
    }
}

?>
