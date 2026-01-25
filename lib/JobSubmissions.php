<?php
/**
 * CATS
 * Job Submissions Library
 *
 * Provides a Bullhorn-compatible interface for managing candidate submissions
 * to job orders. Wraps the candidate_joborder table with status workflow support.
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * Copyright (C) 2024 OpenCATS Community
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
 */

include_once(LEGACY_ROOT . '/lib/History.php');

/**
 * Job Submissions Library
 *
 * Manages candidate submissions to job orders with Bullhorn-compatible status workflow.
 *
 * @package    CATS
 * @subpackage Library
 */
class JobSubmissions
{
    /** @var DatabaseConnection Database connection instance */
    private $_db;

    /** @var int Site ID for multi-tenant support */
    private $_siteID;

    /* Bullhorn-compatible status constants */
    const STATUS_SUBMITTED = 'Submitted';
    const STATUS_REVIEWED = 'Reviewed';
    const STATUS_INTERVIEW = 'Interview';
    const STATUS_OFFER = 'Offer';
    const STATUS_PLACED = 'Placed';
    const STATUS_REJECTED = 'Rejected';
    const STATUS_WITHDRAWN = 'Withdrawn';

    /**
     * Constructor
     *
     * @param int $siteID Site ID for multi-tenant isolation
     */
    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }

    /**
     * Adds a new job submission (candidate to job order).
     *
     * Checks if submission already exists before creating.
     *
     * @param int $candidateID Candidate ID
     * @param int $jobOrderID Job Order ID
     * @param int $userID User ID who is creating the submission
     * @param string $status Initial status (default: 'Submitted')
     * @param string $source Source of submission (e.g., 'Job Board', 'Referral')
     * @return int|false Submission ID on success, false on failure or if already exists
     */
    public function add($candidateID, $jobOrderID, $userID, $status = self::STATUS_SUBMITTED, $source = '')
    {
        /* Check if submission already exists */
        $existing = $this->getByCandidateAndJob($candidateID, $jobOrderID);
        if (!empty($existing))
        {
            /* Submission already exists */
            return false;
        }

        /* Validate status */
        $validStatuses = self::getStatuses();
        if (!in_array($status, $validStatuses))
        {
            $status = self::STATUS_SUBMITTED;
        }

        $sql = sprintf(
            "INSERT INTO candidate_joborder (
                site_id,
                joborder_id,
                candidate_id,
                status,
                bullhorn_status,
                source,
                added_by,
                date_created,
                date_modified
            )
            VALUES (
                %s,
                %s,
                %s,
                100,
                %s,
                %s,
                %s,
                NOW(),
                NOW()
            )",
            $this->_siteID,
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_db->makeQueryInteger($candidateID),
            $this->_db->makeQueryString($status),
            $this->_db->makeQueryString($source),
            $this->_db->makeQueryInteger($userID)
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return false;
        }

        $submissionID = $this->_db->getLastInsertID();

        /* Store history */
        $history = new History($this->_siteID);
        $history->storeHistoryData(
            DATA_ITEM_CANDIDATE,
            $candidateID,
            'PIPELINE',
            $jobOrderID,
            '(ADD)',
            '(USER) submitted candidate to job order ' . $jobOrderID . '.'
        );
        $history->storeHistoryData(
            DATA_ITEM_JOBORDER,
            $jobOrderID,
            'PIPELINE',
            $candidateID,
            '(ADD)',
            '(USER) added candidate ' . $candidateID . ' to job order pipeline.'
        );

        return $submissionID;
    }

    /**
     * Returns a single job submission with full details.
     *
     * Includes joins for candidate, job order, company, and user information.
     *
     * @param int $submissionID Submission (candidate_joborder) ID
     * @return array|false Submission data or false if not found
     */
    public function get($submissionID)
    {
        $sql = sprintf(
            "SELECT
                candidate_joborder.candidate_joborder_id AS submissionID,
                candidate_joborder.site_id AS siteID,
                candidate_joborder.candidate_id AS candidateID,
                candidate_joborder.joborder_id AS jobOrderID,
                candidate_joborder.status AS statusID,
                candidate_joborder.bullhorn_status AS status,
                candidate_joborder.source AS source,
                candidate_joborder.send_to_client AS sendToClient,
                candidate_joborder.rating_value AS ratingValue,
                candidate_joborder.added_by AS addedBy,
                DATE_FORMAT(
                    candidate_joborder.date_created, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate_joborder.date_modified, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateModified,
                DATE_FORMAT(
                    candidate_joborder.date_interview, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateInterview,
                DATE_FORMAT(
                    candidate_joborder.date_offer, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateOffer,
                candidate_joborder_status.short_description AS legacyStatus,
                candidate.first_name AS candidateFirstName,
                candidate.last_name AS candidateLastName,
                candidate.email1 AS candidateEmail,
                joborder.title AS jobTitle,
                joborder.company_id AS companyID,
                company.name AS companyName,
                added_user.first_name AS addedByFirstName,
                added_user.last_name AS addedByLastName,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                candidate_joborder
            LEFT JOIN candidate
                ON candidate_joborder.candidate_id = candidate.candidate_id
            LEFT JOIN joborder
                ON candidate_joborder.joborder_id = joborder.joborder_id
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            LEFT JOIN user AS added_user
                ON candidate_joborder.added_by = added_user.user_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
            LEFT JOIN candidate_joborder_status
                ON candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            WHERE
                candidate_joborder.candidate_joborder_id = %s
            AND
                candidate_joborder.site_id = %s",
            $this->_db->makeQueryInteger($submissionID),
            $this->_siteID
        );

        $result = $this->_db->getAssoc($sql);

        if (empty($result))
        {
            return false;
        }

        return $result;
    }

    /**
     * Finds an existing submission by candidate and job order.
     *
     * @param int $candidateID Candidate ID
     * @param int $jobOrderID Job Order ID
     * @return array|false Submission data or false if not found
     */
    public function getByCandidateAndJob($candidateID, $jobOrderID)
    {
        $sql = sprintf(
            "SELECT
                candidate_joborder.candidate_joborder_id AS submissionID,
                candidate_joborder.status AS statusID,
                candidate_joborder.bullhorn_status AS status,
                candidate_joborder.source AS source,
                candidate_joborder.added_by AS addedBy,
                DATE_FORMAT(
                    candidate_joborder.date_created, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateCreated
            FROM
                candidate_joborder
            WHERE
                candidate_joborder.candidate_id = %s
            AND
                candidate_joborder.joborder_id = %s
            AND
                candidate_joborder.site_id = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID
        );

        $result = $this->_db->getAssoc($sql);

        if (empty($result))
        {
            return false;
        }

        return $result;
    }

    /**
     * Returns all submissions for a specific job order.
     *
     * @param int $jobOrderID Job Order ID
     * @param string|null $status Filter by Bullhorn status (optional)
     * @return array Array of submissions
     */
    public function getByJobOrder($jobOrderID, $status = null)
    {
        $statusCriterion = '';
        if (!empty($status))
        {
            $statusCriterion = sprintf(
                "AND candidate_joborder.bullhorn_status = %s",
                $this->_db->makeQueryString($status)
            );
        }

        $sql = sprintf(
            "SELECT
                candidate_joborder.candidate_joborder_id AS submissionID,
                candidate_joborder.candidate_id AS candidateID,
                candidate_joborder.joborder_id AS jobOrderID,
                candidate_joborder.status AS statusID,
                candidate_joborder.bullhorn_status AS status,
                candidate_joborder.source AS source,
                candidate_joborder.send_to_client AS sendToClient,
                candidate_joborder.rating_value AS ratingValue,
                candidate_joborder.added_by AS addedBy,
                DATE_FORMAT(
                    candidate_joborder.date_created, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate_joborder.date_modified, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateModified,
                candidate.first_name AS candidateFirstName,
                candidate.last_name AS candidateLastName,
                candidate.email1 AS candidateEmail,
                candidate_joborder_status.short_description AS legacyStatus
            FROM
                candidate_joborder
            LEFT JOIN candidate
                ON candidate_joborder.candidate_id = candidate.candidate_id
            LEFT JOIN candidate_joborder_status
                ON candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            WHERE
                candidate_joborder.joborder_id = %s
            AND
                candidate_joborder.site_id = %s
            %s
            ORDER BY
                candidate_joborder.date_created DESC",
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID,
            $statusCriterion
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all submissions for a specific candidate.
     *
     * @param int $candidateID Candidate ID
     * @param string|null $status Filter by Bullhorn status (optional)
     * @return array Array of submissions
     */
    public function getByCandidate($candidateID, $status = null)
    {
        $statusCriterion = '';
        if (!empty($status))
        {
            $statusCriterion = sprintf(
                "AND candidate_joborder.bullhorn_status = %s",
                $this->_db->makeQueryString($status)
            );
        }

        $sql = sprintf(
            "SELECT
                candidate_joborder.candidate_joborder_id AS submissionID,
                candidate_joborder.candidate_id AS candidateID,
                candidate_joborder.joborder_id AS jobOrderID,
                candidate_joborder.status AS statusID,
                candidate_joborder.bullhorn_status AS status,
                candidate_joborder.source AS source,
                candidate_joborder.send_to_client AS sendToClient,
                candidate_joborder.rating_value AS ratingValue,
                candidate_joborder.added_by AS addedBy,
                DATE_FORMAT(
                    candidate_joborder.date_created, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate_joborder.date_modified, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateModified,
                joborder.title AS jobTitle,
                joborder.company_id AS companyID,
                company.name AS companyName,
                candidate_joborder_status.short_description AS legacyStatus
            FROM
                candidate_joborder
            LEFT JOIN joborder
                ON candidate_joborder.joborder_id = joborder.joborder_id
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            LEFT JOIN candidate_joborder_status
                ON candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            WHERE
                candidate_joborder.candidate_id = %s
            AND
                candidate_joborder.site_id = %s
            %s
            ORDER BY
                candidate_joborder.date_created DESC",
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID,
            $statusCriterion
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns a list of submissions with filtering and pagination.
     *
     * @param int $limit Maximum number of records to return
     * @param int $offset Number of records to skip
     * @param string|null $status Filter by Bullhorn status
     * @param int|null $jobOrderID Filter by Job Order ID
     * @param int|null $candidateID Filter by Candidate ID
     * @return array Array of submissions
     */
    public function getAll($limit = 100, $offset = 0, $status = null, $jobOrderID = null, $candidateID = null)
    {
        $whereClauses = array();

        if (!empty($status))
        {
            $whereClauses[] = sprintf(
                "candidate_joborder.bullhorn_status = %s",
                $this->_db->makeQueryString($status)
            );
        }

        if (!empty($jobOrderID))
        {
            $whereClauses[] = sprintf(
                "candidate_joborder.joborder_id = %s",
                $this->_db->makeQueryInteger($jobOrderID)
            );
        }

        if (!empty($candidateID))
        {
            $whereClauses[] = sprintf(
                "candidate_joborder.candidate_id = %s",
                $this->_db->makeQueryInteger($candidateID)
            );
        }

        $whereSQL = '';
        if (!empty($whereClauses))
        {
            $whereSQL = 'AND ' . implode(' AND ', $whereClauses);
        }

        $sql = sprintf(
            "SELECT
                candidate_joborder.candidate_joborder_id AS submissionID,
                candidate_joborder.candidate_id AS candidateID,
                candidate_joborder.joborder_id AS jobOrderID,
                candidate_joborder.status AS statusID,
                candidate_joborder.bullhorn_status AS status,
                candidate_joborder.source AS source,
                candidate_joborder.send_to_client AS sendToClient,
                candidate_joborder.rating_value AS ratingValue,
                candidate_joborder.added_by AS addedBy,
                DATE_FORMAT(
                    candidate_joborder.date_created, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate_joborder.date_modified, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateModified,
                candidate.first_name AS candidateFirstName,
                candidate.last_name AS candidateLastName,
                candidate.email1 AS candidateEmail,
                joborder.title AS jobTitle,
                joborder.company_id AS companyID,
                company.name AS companyName,
                candidate_joborder_status.short_description AS legacyStatus
            FROM
                candidate_joborder
            LEFT JOIN candidate
                ON candidate_joborder.candidate_id = candidate.candidate_id
            LEFT JOIN joborder
                ON candidate_joborder.joborder_id = joborder.joborder_id
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            LEFT JOIN candidate_joborder_status
                ON candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            WHERE
                candidate_joborder.site_id = %s
            %s
            ORDER BY
                candidate_joborder.date_modified DESC
            LIMIT %s OFFSET %s",
            $this->_siteID,
            $whereSQL,
            $this->_db->makeQueryInteger($limit),
            $this->_db->makeQueryInteger($offset)
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns the count of submissions matching the given filters.
     *
     * @param string|null $status Filter by Bullhorn status
     * @param int|null $jobOrderID Filter by Job Order ID
     * @param int|null $candidateID Filter by Candidate ID
     * @return int Number of matching submissions
     */
    public function getCount($status = null, $jobOrderID = null, $candidateID = null)
    {
        $whereClauses = array();

        if (!empty($status))
        {
            $whereClauses[] = sprintf(
                "candidate_joborder.bullhorn_status = %s",
                $this->_db->makeQueryString($status)
            );
        }

        if (!empty($jobOrderID))
        {
            $whereClauses[] = sprintf(
                "candidate_joborder.joborder_id = %s",
                $this->_db->makeQueryInteger($jobOrderID)
            );
        }

        if (!empty($candidateID))
        {
            $whereClauses[] = sprintf(
                "candidate_joborder.candidate_id = %s",
                $this->_db->makeQueryInteger($candidateID)
            );
        }

        $whereSQL = '';
        if (!empty($whereClauses))
        {
            $whereSQL = 'AND ' . implode(' AND ', $whereClauses);
        }

        $sql = sprintf(
            "SELECT
                COUNT(*) AS totalCount
            FROM
                candidate_joborder
            WHERE
                candidate_joborder.site_id = %s
            %s",
            $this->_siteID,
            $whereSQL
        );

        $result = $this->_db->getAssoc($sql);

        if (empty($result))
        {
            return 0;
        }

        return (int) $result['totalCount'];
    }

    /**
     * Updates the status of a job submission.
     *
     * Sets date_interview when status changes to Interview.
     * Sets date_offer when status changes to Offer.
     *
     * @param int $submissionID Submission (candidate_joborder) ID
     * @param string $status New Bullhorn status
     * @param int $userID User ID making the change
     * @return bool True on success, false on failure
     */
    public function updateStatus($submissionID, $status, $userID)
    {
        /* Validate status */
        $validStatuses = self::getStatuses();
        if (!in_array($status, $validStatuses))
        {
            return false;
        }

        /* Get existing submission for history */
        $existing = $this->get($submissionID);
        if (empty($existing))
        {
            return false;
        }

        /* Build additional date fields based on status */
        $dateFields = '';
        if ($status === self::STATUS_INTERVIEW)
        {
            $dateFields = ', date_interview = NOW()';
        }
        else if ($status === self::STATUS_OFFER)
        {
            $dateFields = ', date_offer = NOW()';
        }

        /* Map Bullhorn status to legacy status ID */
        $legacyStatusID = $this->mapBullhornToLegacyStatus($status);

        $sql = sprintf(
            "UPDATE
                candidate_joborder
            SET
                bullhorn_status = %s,
                status = %s,
                date_modified = NOW()
                %s
            WHERE
                candidate_joborder_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryString($status),
            $this->_db->makeQueryInteger($legacyStatusID),
            $dateFields,
            $this->_db->makeQueryInteger($submissionID),
            $this->_siteID
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return false;
        }

        /* Store history */
        $history = new History($this->_siteID);
        $history->storeHistoryData(
            DATA_ITEM_CANDIDATE,
            $existing['candidateID'],
            'PIPELINE',
            $existing['jobOrderID'],
            $status,
            '(USER) changed submission status from ' . $existing['status'] . ' to ' . $status . '.'
        );

        return true;
    }

    /**
     * Deletes a job submission.
     *
     * @param int $submissionID Submission (candidate_joborder) ID
     * @return bool True on success, false on failure
     */
    public function delete($submissionID)
    {
        /* Get submission data for history before deletion */
        $existing = $this->get($submissionID);
        if (empty($existing))
        {
            return false;
        }

        /* Delete the submission */
        $sql = sprintf(
            "DELETE FROM
                candidate_joborder
            WHERE
                candidate_joborder_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($submissionID),
            $this->_siteID
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return false;
        }

        /* Delete related status history */
        $sql = sprintf(
            "DELETE FROM
                candidate_joborder_status_history
            WHERE
                joborder_id = %s
            AND
                candidate_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($existing['jobOrderID']),
            $this->_db->makeQueryInteger($existing['candidateID']),
            $this->_siteID
        );
        $this->_db->query($sql);

        /* Store history */
        $history = new History($this->_siteID);
        $history->storeHistoryData(
            DATA_ITEM_CANDIDATE,
            $existing['candidateID'],
            'PIPELINE',
            $existing['jobOrderID'],
            '(DELETE)',
            '(USER) removed candidate from job order pipeline.'
        );
        $history->storeHistoryData(
            DATA_ITEM_JOBORDER,
            $existing['jobOrderID'],
            'PIPELINE',
            $existing['candidateID'],
            '(DELETE)',
            '(USER) removed candidate ' . $existing['candidateID'] . ' from pipeline.'
        );

        return true;
    }

    /**
     * Returns array of all valid Bullhorn-compatible statuses.
     *
     * @return array Array of status strings
     */
    public static function getStatuses()
    {
        return array(
            self::STATUS_SUBMITTED,
            self::STATUS_REVIEWED,
            self::STATUS_INTERVIEW,
            self::STATUS_OFFER,
            self::STATUS_PLACED,
            self::STATUS_REJECTED,
            self::STATUS_WITHDRAWN
        );
    }

    /**
     * Maps Bullhorn status to legacy OpenCATS status ID.
     *
     * @param string $bullhornStatus Bullhorn status string
     * @return int Legacy status ID
     */
    private function mapBullhornToLegacyStatus($bullhornStatus)
    {
        $statusMap = array(
            self::STATUS_SUBMITTED => 400,  // Submitted
            self::STATUS_REVIEWED  => 350,  // Reviewed
            self::STATUS_INTERVIEW => 500,  // Interviewing
            self::STATUS_OFFER     => 600,  // Offered
            self::STATUS_PLACED    => 800,  // Placed
            self::STATUS_REJECTED  => 850,  // Rejected
            self::STATUS_WITHDRAWN => 900   // Withdrawn
        );

        if (isset($statusMap[$bullhornStatus]))
        {
            return $statusMap[$bullhornStatus];
        }

        return 100; // Default: No Contact
    }

    /**
     * Updates additional fields on a submission.
     *
     * @param int $submissionID Submission ID
     * @param array $data Array of fields to update (source, sendToClient, ratingValue)
     * @return bool True on success, false on failure
     */
    public function update($submissionID, $data)
    {
        /* Verify submission exists */
        $existing = $this->get($submissionID);
        if (empty($existing))
        {
            return false;
        }

        $updates = array();

        if (isset($data['source']))
        {
            $updates[] = sprintf(
                "source = %s",
                $this->_db->makeQueryString($data['source'])
            );
        }

        if (isset($data['sendToClient']))
        {
            $updates[] = sprintf(
                "send_to_client = %s",
                $data['sendToClient'] ? '1' : '0'
            );
        }

        if (isset($data['ratingValue']))
        {
            $updates[] = sprintf(
                "rating_value = %s",
                $this->_db->makeQueryInteger($data['ratingValue'])
            );
        }

        if (isset($data['status']))
        {
            /* Delegate to updateStatus for proper handling */
            return $this->updateStatus($submissionID, $data['status'],
                isset($data['userID']) ? $data['userID'] : 0);
        }

        if (empty($updates))
        {
            return true; // Nothing to update
        }

        $updates[] = "date_modified = NOW()";

        $sql = sprintf(
            "UPDATE
                candidate_joborder
            SET
                %s
            WHERE
                candidate_joborder_id = %s
            AND
                site_id = %s",
            implode(', ', $updates),
            $this->_db->makeQueryInteger($submissionID),
            $this->_siteID
        );

        return (bool) $this->_db->query($sql);
    }
}

?>
