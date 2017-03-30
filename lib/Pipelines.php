<?php
/**
 * CATS
 * Pipelines Library
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
 * @version    $Id: Pipelines.php 3593 2007-11-13 17:36:57Z andrew $
 */

include_once('./lib/History.php');

/**
 *	Pipelines Library
 *	@package    CATS
 *	@subpackage Library
 */
class Pipelines
{
    private $_db;
    private $_siteID;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }


    /**
     * Adds a candidate to the pipeline for a job order.
     *
     * @param integer job order ID
     * @param integer candidate ID
     * @return true on success; false otherwise.
     */
    public function add($candidateID, $jobOrderID, $userID = 0)
    {
        $sql = sprintf(
            "SELECT
                COUNT(candidate_id) AS candidateIDCount
            FROM
                candidate_joborder
            WHERE
                candidate_id = %s
            AND
                joborder_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID
        );
        $rs = $this->_db->getAssoc($sql);

        if (empty($rs))
        {
            return false;
        }

        $count = $rs['candidateIDCount'];
        if ($count > 0)
        {
            /* Candidate already exists in the pipeline. */
            return false;
        }

        $extraFields = '';
        $extraValues = '';

        if (!eval(Hooks::get('PIPELINES_ADD_SQL'))) return;

        $sql = sprintf(
            "INSERT INTO candidate_joborder (
                site_id,
                joborder_id,
                candidate_id,
                status,
                added_by,
                date_created,
                date_modified%s
            )
            VALUES (
                %s,
                %s,
                %s,
                100,
                %s,
                NOW(),
                NOW()%s
            )",
            $extraFields,
            $this->_siteID,
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_db->makeQueryInteger($candidateID),
            $this->_db->makeQueryInteger($userID),
            $extraValues
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return false;
        }

        return true;
    }

    /**
     * Removes a candidate from the pipeline for a job order.
     *
     * @param integer candidate ID
     * @param integer job order ID
     * @return void
     */
    public function remove($candidateID, $jobOrderID)
    {
        $sql = sprintf(
            "DELETE FROM
                candidate_joborder
            WHERE
                joborder_id = %s
            AND
                candidate_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );
        $this->_db->query($sql);

        $sql = sprintf(
            "DELETE FROM
                candidate_joborder_status_history
            WHERE
                joborder_id = %s
            AND
                candidate_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );
        $this->_db->query($sql);

        $history = new History($this->_siteID);
        $history->storeHistoryData(
            DATA_ITEM_CANDIDATE,
            $candidateID,
            'PIPELINE',
            $jobOrderID,
            '(DELETE)',
            '(USER) deleted candidate from pipeline for job order ' . $jobOrderID . '.'
        );
        $history->storeHistoryData(
            DATA_ITEM_JOBORDER,
            $jobOrderID,
            'PIPELINE',
            $candidateID,
            '(DELETE)',
            '(USER) deleted job order from pipeline for candidate ' . $candidateID . '.'
        );
    }

    /**
     * Returns a single pipeline row.
     *
     * @param integer candidate ID
     * @param integer job order ID
     * @return array pipeline data
     */
    public function get($candidateID, $jobOrderID)
    {
        $sql = sprintf(
            "SELECT
                candidate_joborder.candidate_joborder_id as candidateJobOrderID,
                company.company_id AS companyID,
                company.name AS companyName,
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.type AS type,
                joborder.duration AS duration,
                joborder.rate_max AS maxRate,
                joborder.status AS jobOrderStatus,
                joborder.salary AS salary,
                joborder.is_hot AS isHot,
                joborder.openings AS openings,
                joborder.openings_available AS openingsAvailable,
                DATE_FORMAT(
                    joborder.start_date, '%%m-%%d-%%y'
                ) AS start_date,
                DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                candidate.candidate_id AS candidateID,
                candidate.email1 AS candidateEmail,
                candidate_joborder_status.candidate_joborder_status_id AS statusID,
                candidate_joborder_status.short_description AS status,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                candidate_joborder
            LEFT JOIN candidate
                ON candidate_joborder.candidate_id = candidate.candidate_id
            LEFT JOIN joborder
                ON candidate_joborder.joborder_id = joborder.joborder_id
            LEFT JOIN company
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
            LEFT JOIN candidate_joborder_status
                ON candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            WHERE
                candidate.candidate_id = %s
            AND
                joborder.joborder_id = %s
            AND
                candidate.site_id = %s
            AND
                joborder.site_id = %s
            AND
                company.site_id = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID,
            $this->_siteID,
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Returns a pipeline entry's candidate-joborder ID from the specified
     * candidate ID and job order ID; -1 if not found.
     *
     * @param integer candidate ID
     * @param integer job order ID
     * @return integer candidate-joborder ID or -1 if not found
     */
    public function getCandidateJobOrderID($candidateID, $jobOrderID)
    {
        $sql = sprintf(
            "SELECT
                candidate_joborder_id AS candidateJobOrderID
            FROM
                candidate_joborder
            WHERE
                candidate_id = %s
            AND
                joborder_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID
        );
        $rs = $this->_db->getAssoc($sql);

        if (empty($rs))
        {
            return -1;
        }

        return (int) $rs['candidateJobOrderID'];
    }

    // FIXME: Document me.
    public function setStatus($candidateID, $jobOrderID, $statusID,
                              $emailAddress, $emailText)
    {
        /* Get existing status. */
        $sql = sprintf(
            "SELECT
                status AS oldStatusID,
                candidate_joborder_id AS candidateJobOrderID
            FROM
                candidate_joborder
            WHERE
                joborder_id = %s
            AND
                candidate_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );
        $rs = $this->_db->getAssoc($sql);

        if (empty($rs))
        {
            return;
        }

        $candidateJobOrderID = $rs['candidateJobOrderID'];
        $oldStatusID         = $rs['oldStatusID'];

        if ($oldStatusID == $statusID)
        {
            /* No need to update the database and scew the history if there is
             * no actual change.
             */
            return;
        }

        /* Change status. */
        $sql = sprintf(
            "UPDATE
                candidate_joborder
            SET
                status        = %s,
                date_modified = NOW()
            WHERE
                candidate_joborder_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($statusID),
            $this->_db->makeQueryInteger($candidateJobOrderID),
            $this->_siteID
        );
        $this->_db->query($sql);

        /* Add history. */
        $historyID = $this->addStatusHistory(
            $candidateID, $jobOrderID, $statusID, $oldStatusID
        );

        /* Add auditing history. */
        $historyDescription = '(USER) changed pipeline status of candidate '
            . $candidateID . ' for job order ' . $jobOrderID . '.';
        $history = new History($this->_siteID);
        $history->storeHistoryData(
            DATA_ITEM_PIPELINE,
            $candidateJobOrderID,
            'PIPELINE',
            $oldStatusID,
            $statusID,
            $historyDescription
        );

        if (!empty($emailAddress))
        {
            /* Send e-mail notification. */
            //FIXME: Make subject configurable.
            $mailer = new Mailer($this->_siteID);
            $mailerStatus = $mailer->sendToOne(
                array($emailAddress, ''),
                CANDIDATE_STATUSCHANGE_SUBJECT,
                $emailText,
                true
            );
        }
    }

    // FIXME: Document me.
    public function getStatuses()
    {
        $sql = sprintf(
            "SELECT
                candidate_joborder_status_id AS statusID,
                short_description AS status,
                can_be_scheduled AS canBeScheduled,
                triggers_email AS triggersEmail
            FROM
                candidate_joborder_status
            WHERE
                is_enabled = 1
            ORDER BY
                candidate_joborder_status_id ASC",
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    // FIXME: Document me.
    // Throws out No Status.
    public function getStatusesForPicking()
    {
        $sql = sprintf(
            "SELECT
                candidate_joborder_status_id AS statusID,
                short_description AS status,
                can_be_scheduled AS canBeScheduled,
                triggers_email AS triggersEmail
            FROM
                candidate_joborder_status
            WHERE
                is_enabled = 1
            AND
                candidate_joborder_status_id != 0
            ORDER BY
                candidate_joborder_status_id ASC",
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    // FIXME: Document me.
    public function addStatusHistory($candidateID, $jobOrderID, $statusToID,
                                     $statusFromID)
    {
        $sql = sprintf(
            "INSERT INTO candidate_joborder_status_history (
                joborder_id,
                candidate_id,
                site_id,
                date,
                status_to,
                status_from
            )
            VALUES (
                %s,
                %s,
                %s,
                NOW(),
                %s,
                %s
            )",
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID,
            $this->_db->makeQueryInteger($statusToID),
            $this->_db->makeQueryInteger($statusFromID)
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        return $this->_db->getLastInsertID();
    }

    /**
     * Returns a candidate's pipeline.
     *
     * @param integer candidate ID
     * @return array pipeline data
     */
    public function getCandidatePipeline($candidateID)
    {
        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS companyName,
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.type AS type,
                joborder.duration AS duration,
                joborder.rate_max AS maxRate,
                joborder.status AS jobOrderStatus,
                joborder.salary AS salary,
                joborder.is_hot AS isHot,
                DATE_FORMAT(
                    joborder.start_date, '%%m-%%d-%%y'
                ) AS start_date,
                DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                candidate.candidate_id AS candidateID,
                candidate.email1 AS candidateEmail,
                candidate_joborder_status.candidate_joborder_status_id AS statusID,
                candidate_joborder_status.short_description AS status,
                candidate_joborder.candidate_joborder_id AS candidateJobOrderID,
                candidate_joborder.rating_value AS ratingValue,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                added_user.first_name AS addedByFirstName,
                added_user.last_name AS addedByLastName
            FROM
                candidate_joborder
            INNER JOIN candidate
                ON candidate_joborder.candidate_id = candidate.candidate_id
            INNER JOIN joborder
                ON candidate_joborder.joborder_id = joborder.joborder_id
            INNER JOIN company
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
            LEFT JOIN user AS added_user
                ON candidate_joborder.added_by = added_user.user_id
            INNER JOIN candidate_joborder_status
                ON candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            WHERE
                candidate.candidate_id = %s
            AND
                candidate.site_id = %s
            AND
                joborder.site_id = %s
            AND
                company.site_id = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID,
            $this->_siteID,
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns a job order's pipeline.
     *
     * @param integer job order ID
     * @return array pipeline data
     */
    public function getJobOrderPipeline($jobOrderID, $orderBy = '')
    {
        /* FIXME: CONCAT() stuff is a very ugly hack, but I don't think there
         * is a way to return multiple values from a subquery.
         */
        $sql = sprintf(
            "SELECT
                IF(attachment_id, 1, 0) AS attachmentPresent,
                candidate.candidate_id AS candidateID,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                candidate.state As state,
                candidate.email1 AS candidateEmail,
                candidate_joborder.status AS jobOrderStatus,
                candidate.is_hot AS isHotCandidate,
                DATE_FORMAT(
                    candidate_joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                UNIX_TIMESTAMP(candidate_joborder.date_created) AS dateCreatedInt,
                candidate_joborder_status.short_description AS status,
                candidate_joborder.candidate_joborder_id AS candidateJobOrderID,
                candidate_joborder.rating_value AS ratingValue,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                (
                    SELECT
                        CONCAT(
                            '<strong>',
                            DATE_FORMAT(activity.date_created, '%%m-%%d-%%y'),
                            ' (',
                            entered_by_user.first_name,
                            ' ',
                            entered_by_user.last_name,
                            '):</strong> ',
                            IF(
                                ISNULL(activity.notes) OR activity.notes = '',
                                '(No Notes)',
                                activity.notes
                            )
                        )
                    FROM
                        activity
                    LEFT JOIN activity_type
                        ON activity.type = activity_type.activity_type_id
                    LEFT JOIN user AS entered_by_user
                        ON activity.entered_by = entered_by_user.user_id
                    WHERE
                        activity.data_item_id = candidate.candidate_id
                    AND
                        activity.data_item_type = %s
                    AND
                        activity.joborder_id = %s
                    ORDER BY
                        activity.date_created DESC
                    LIMIT 1
                ) AS lastActivity,
                IF((
                    SELECT
                        COUNT(*)
                    FROM
                        candidate_joborder_status_history
                    WHERE
                        joborder_id = %s
                    AND
                        candidate_id = candidate.candidate_id
                    AND
                        status_to = %s
                    AND
                        site_id = %s
                ) >= 1, 1, 0) AS submitted,
                added_user.first_name AS addedByFirstName,
                added_user.last_name AS addedByLastName
            FROM
                candidate_joborder
            LEFT JOIN candidate
                ON candidate_joborder.candidate_id = candidate.candidate_id
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
            LEFT JOIN user AS added_user
                ON candidate_joborder.added_by = added_user.user_id
            LEFT JOIN attachment
                ON candidate.candidate_id = attachment.data_item_id
            LEFT JOIN candidate_joborder_status
                ON candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            WHERE
                candidate_joborder.joborder_id = %s
            AND
                candidate_joborder.site_id = %s
            AND
                candidate.site_id = %s
            GROUP BY
                candidate_joborder.candidate_id
            %s",
            DATA_ITEM_CANDIDATE,
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_db->makeQueryInteger($jobOrderID),
            PIPELINE_STATUS_SUBMITTED,
            $this->_siteID,
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID,
            $this->_siteID,
            $orderBy
        );

        return $this->_db->getAllAssoc($sql);
    }

    // FIXME: Document me.
    public function updateRatingValue($candidateJobOrderID, $value)
    {
        $sql = sprintf(
            "UPDATE
                candidate_joborder
            SET
                rating_value = %s
            WHERE
                candidate_joborder.candidate_joborder_id = %s
            AND
                candidate_joborder.site_id = %s",
            $this->_db->makeQueryInteger($value),
            $this->_db->makeQueryInteger($candidateJobOrderID),
            $this->_siteID
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }
    }

    // FIXME: Document me.
    public function getRatingValue($candidateJobOrderID)
    {
        $sql = sprintf(
            "SELECT
                rating_value AS ratingValue
            FROM
                candidate_joborder
            WHERE
                candidate_joborder_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($candidateJobOrderID),
            $this->_siteID
        );
        $rs = $this->_db->getAssoc($sql);

        if (!isset($rs['ratingValue']) || empty($rs['ratingValue']))
        {
            return 0;
        }

        return $rs['ratingValue'];
    }

    //FIXME: Document me.
    public function getPipelineDetails($candidateJobOrderID)
    {
        $sql = sprintf(
            "SELECT
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                candidate.email1 AS candidateEmail,
                candidate_joborder.status AS jobOrderStatus,
                activity.notes AS notes,
                DATE_FORMAT(
                    candidate_joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                candidate_joborder.candidate_joborder_id AS candidateJobOrderID,
                candidate_joborder.rating_value AS ratingValue,
                entered_by_user.first_name AS enteredByFirstName,
                entered_by_user.last_name AS enteredByLastName,
                DATE_FORMAT(activity.date_modified, '%%m-%%d-%%y (%%h:%%i:%%s %%p)') AS dateModified
            FROM
                candidate_joborder
            LEFT JOIN candidate
                ON candidate_joborder.candidate_id = candidate.candidate_id
            INNER JOIN activity
                ON activity.joborder_id = candidate_joborder.joborder_id
            LEFT JOIN user AS entered_by_user
                ON entered_by_user.user_id = activity.entered_by
            WHERE
                candidate_joborder.candidate_joborder_id = %s
            AND
                activity.data_item_type = %s
            AND
                activity.data_item_id = candidate_joborder.candidate_id
            AND
                candidate_joborder.site_id = %s
            ",
            $this->_db->makeQueryInteger($candidateJobOrderID),
            DATA_ITEM_CANDIDATE,
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

}

?>
