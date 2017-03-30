<?php
/**
 * CATS
 * Statistics Library
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
 * @version    $Id: Statistics.php 3587 2007-11-13 03:55:57Z will $
 */

include_once('./lib/Pipelines.php');

/**
 *	Statistics Library
 *	@package    CATS
 *	@subpackage Library
 */
class Statistics
{
    private $_db;
    private $_siteID;
    private $_timeZoneOffset;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();

        // FIXME: Session coupling...
        $this->_timeZoneOffset = $_SESSION['CATS']->getTimeZoneOffset();
    }


    /**
     * Returns the total number of candidates created in the given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getCandidateCount($period)
    {
        $criterion = $this->makePeriodCriterion('date_created', $period);

        $sql = sprintf(
            "SELECT
                COUNT(*) AS candidate_count
            FROM
                candidate
            WHERE
                site_id = %s
            %s",
            $this->_siteID,
            $criterion
        );
        $rs = $this->_db->getAssoc($sql);

        return $rs['candidate_count'];
    }

    /**
     * Returns the total number of submissions created in the given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getSubmissionCount($period)
    {
        $criterion = $this->makePeriodCriterion('date', $period);

        $sql = sprintf(
            "SELECT
                COUNT(*) AS submissionCount
            FROM
                candidate_joborder_status_history
            LEFT JOIN joborder
                ON joborder.joborder_id = candidate_joborder_status_history.joborder_id
            WHERE
                status_to = 400
            AND
                joborder.status IN ('Active', 'OnHold', 'Full', 'Closed')
            AND
                candidate_joborder_status_history.site_id = %s
            %s",
            $this->_siteID,
            $criterion
        );
        $rs = $this->_db->getAssoc($sql);

        return $rs['submissionCount'];
    }

	/**
     * Returns the total number of placements in the given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getPlacementCount($period)
    {
        $criterion = $this->makePeriodCriterion('date', $period);

        $sql = sprintf(
            "SELECT
                COUNT(*) AS placementCount
            FROM
                candidate_joborder_status_history
            WHERE
                status_to = 800
            AND
                site_id = %s
            %s",
            $this->_siteID,
            $criterion
        );
        $rs = $this->_db->getAssoc($sql);

        return $rs['placementCount'];
    }

    /**
     * Returns the total number of companies created in the given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getCompanyCount($period)
    {
        $criterion = $this->makePeriodCriterion('date_created', $period);

        $sql = sprintf(
            "SELECT
                COUNT(*) AS company_count
            FROM
                company
            WHERE
                site_id = %s
            %s",
            $this->_siteID,
            $criterion
        );
        $rs = $this->_db->getAssoc($sql);

        return $rs['company_count'];
    }

    /**
     * Returns the total number of contacts created in the given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getContactCount($period)
    {
        $criterion = $this->makePeriodCriterion('date_created', $period);

        $sql = sprintf(
            "SELECT
                COUNT(*) AS contact_count
            FROM
                contact
            WHERE
                site_id = %s
            %s",
            $this->_siteID,
            $criterion
        );
        $rs = $this->_db->getAssoc($sql);

        return $rs['contact_count'];
    }

    /**
     * Returns the total number of job orders created in the given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getJobOrderCount($period)
    {
        $criterion = $this->makePeriodCriterion('date_created', $period);

        $sql = sprintf(
            "SELECT
                COUNT(*) AS joborder_count
            FROM
                joborder
            WHERE
                site_id = %s
            %s",
            $this->_siteID,
            $criterion
        );
        $rs = $this->_db->getAssoc($sql);

        return $rs['joborder_count'];
    }

    /**
     * Returns all job orders with submissions created in the given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getSubmissionJobOrders($period)
    {
        $criterion = $this->makePeriodCriterion(
            'candidate_joborder_status_history.date', $period
        );

        $sql = sprintf(
            "SELECT
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.company_id AS companyID,
                SUM(
                    IF(candidate_joborder_status_history.status_to = 400, 1, 0)
                ) AS submittedCount,
                CONCAT(
                    owner_user.first_name, ' ', owner_user.last_name
                ) AS ownerFullName,
                company.name AS companyName
            FROM
                joborder
            LEFT OUTER JOIN candidate_joborder_status_history
                ON candidate_joborder_status_history.joborder_id = joborder.joborder_id
                %s
            LEFT JOIN company
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS owner_user
                ON owner_user.user_id = joborder.owner
            WHERE
                joborder.status IN ('Active', 'OnHold', 'Full', 'Closed')
            AND
                joborder.site_id = %s
            GROUP BY
                jobOrderID
            HAVING
                submittedCount > 0",
            $criterion,
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all submissions for the specified job order created in the
     * given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getSubmissionsByJobOrder($period, $jobOrderID)
    {
        $criterion = $this->makePeriodCriterion(
            'candidate_joborder_status_history.date', $period
        );

        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.company_id AS companyID,
                CONCAT(
                    owner_user.first_name, ' ', owner_user.last_name
                ) AS ownerFullName,
                DATE_FORMAT(
                    candidate_joborder_status_history.date, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateSubmitted
            FROM
                candidate_joborder_status_history
            LEFT JOIN candidate
                ON candidate.candidate_id = candidate_joborder_status_history.candidate_id
            LEFT JOIN joborder
                ON joborder.joborder_id = candidate_joborder_status_history.joborder_id
            LEFT JOIN company
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS owner_user
                ON owner_user.user_id = candidate.owner
            WHERE
                candidate_joborder_status_history.joborder_id = %s
            AND
                candidate_joborder_status_history.status_to = 400
            %s
            AND
                candidate.site_id = %s
            AND
                joborder.site_id = %s
            AND
                company.site_id = %s
            ORDER BY
                candidate.last_name ASC,
                candidate.first_name ASC",
            $jobOrderID,
            $criterion,
            $this->_siteID,
            $this->_siteID,
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }
    
    /**
     * Returns all job orders with placements created in the given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getPlacementsJobOrders($period)
    {
        $criterion = $this->makePeriodCriterion(
            'candidate_joborder_status_history.date', $period
        );

        $sql = sprintf(
            "SELECT
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.company_id AS companyID,
                SUM(
                    IF(candidate_joborder_status_history.status_to = 800, 1, 0)
                ) AS submittedCount,
                CONCAT(
                    owner_user.first_name, ' ', owner_user.last_name
                ) AS ownerFullName,
                company.name AS companyName
            FROM
                joborder
            LEFT OUTER JOIN candidate_joborder_status_history
                ON candidate_joborder_status_history.joborder_id = joborder.joborder_id
                %s
            LEFT JOIN company
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS owner_user
                ON owner_user.user_id = joborder.owner
            WHERE
                joborder.status IN ('Active', 'OnHold', 'Full', 'Closed')
            AND
                joborder.site_id = %s
            GROUP BY
                jobOrderID
            HAVING
                submittedCount > 0",
            $criterion,
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }
    
    /**
     * Returns all placements for the specified job order created in the
     * given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getPlacementsByJobOrder($period, $jobOrderID)
    {
        $criterion = $this->makePeriodCriterion(
            'candidate_joborder_status_history.date', $period
        );

        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.company_id AS companyID,
                CONCAT(
                    owner_user.first_name, ' ', owner_user.last_name
                ) AS ownerFullName,
                DATE_FORMAT(
                    candidate_joborder_status_history.date, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateSubmitted
            FROM
                candidate_joborder_status_history
            LEFT JOIN candidate
                ON candidate.candidate_id = candidate_joborder_status_history.candidate_id
            LEFT JOIN joborder
                ON joborder.joborder_id = candidate_joborder_status_history.joborder_id
            LEFT JOIN company
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS owner_user
                ON owner_user.user_id = candidate.owner
            WHERE
                candidate_joborder_status_history.joborder_id = %s
            AND
                candidate_joborder_status_history.status_to = 800
            %s
            AND
                candidate.site_id = %s
            AND
                joborder.site_id = %s
            AND
                company.site_id = %s
            ORDER BY
                candidate.last_name ASC,
                candidate.first_name ASC",
            $jobOrderID,
            $criterion,
            $this->_siteID,
            $this->_siteID,
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }
    
    // FIXME: Document me.
    public function getActivitiesByPeriod($period)
    {
        $criterion = $this->makePeriodCriterion(
            'activity.date_created', $period
        );

        $sql = sprintf(
            "SELECT
                activity.activity_id AS activityID,
                DATE_FORMAT(
                    activity.date_created, '%%m'
                ) AS month,
                DATE_FORMAT(
                    activity.date_created, '%%d'
                ) AS day,
                DATE_FORMAT(
                    activity.date_created, '%%y'
                ) AS year
            FROM
                activity
            WHERE
                activity.site_id = %s
            %s",
            $this->_siteID,
            $criterion
        );

        return $this->_db->getAllAssoc($sql);
    }

    // FIXME: Document me.
    public function getCandidatesByPeriod($period)
    {
        $criterion = $this->makePeriodCriterion(
            'candidate.date_created', $period
        );

        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                DATE_FORMAT(
                    candidate.date_created, '%%m'
                ) AS month,
                DATE_FORMAT(
                    candidate.date_created, '%%d'
                ) AS day,
                DATE_FORMAT(
                    candidate.date_created, '%%y'
                ) AS year
            FROM
                candidate
            WHERE
                candidate.site_id = %s
            %s",
            $this->_siteID,
            $criterion
        );

        return $this->_db->getAllAssoc($sql);
    }

    // FIXME: Document me.
    public function getJobOrdersByPeriod($period)
    {
        $criterion = $this->makePeriodCriterion(
            'joborder.date_created', $period
        );

        $sql = sprintf(
            "SELECT
                joborder.joborder_id AS jobOrderID,
                DATE_FORMAT(
                    joborder.date_created, '%%m'
                ) AS month,
                DATE_FORMAT(
                    joborder.date_created, '%%d'
                ) AS day,
                DATE_FORMAT(
                    joborder.date_created, '%%y'
                ) AS year
            FROM
                joborder
            WHERE
                joborder.site_id = %s
            %s",
            $this->_siteID,
            $criterion
        );

        return $this->_db->getAllAssoc($sql);
    }

    // FIXME: Document me.
    public function getSubmissionsByPeriod($period)
    {
        $criterion = $this->makePeriodCriterion(
            'candidate_joborder_status_history.date', $period
        );

        $sql = sprintf(
            "SELECT
                DATE_FORMAT(
                    candidate_joborder_status_history.date, '%%m'
                ) AS month,
                DATE_FORMAT(
                    candidate_joborder_status_history.date, '%%d'
                ) AS day,
                DATE_FORMAT(
                    candidate_joborder_status_history.date, '%%y'
                ) AS year
            FROM
                candidate_joborder_status_history
            WHERE
                candidate_joborder_status_history.site_id = %s
            AND
                candidate_joborder_status_history.status_to = 400
            %s",
            $this->_siteID,
            $criterion
        );

        return $this->_db->getAllAssoc($sql);
    }

	// FIXME: Document me.
    public function getPlacementsByPeriod($period)
    {
        $criterion = $this->makePeriodCriterion(
            'candidate_joborder_status_history.date', $period
        );

        $sql = sprintf(
            "SELECT
                DATE_FORMAT(
                    candidate_joborder_status_history.date, '%%m'
                ) AS month,
                DATE_FORMAT(
                    candidate_joborder_status_history.date, '%%d'
                ) AS day,
                DATE_FORMAT(
                    candidate_joborder_status_history.date, '%%y'
                ) AS year
            FROM
                candidate_joborder_status_history
            WHERE
                candidate_joborder_status_history.site_id = %s
            AND
                candidate_joborder_status_history.status_to = 800
            %s",
            $this->_siteID,
            $criterion
        );

        return $this->_db->getAllAssoc($sql);
    }

    // FIXME: Document me.
    public function getJobOrderReport($jobOrderID)
    {
        $sql = sprintf(
            "SELECT
                joborder.joborder_id AS jobOrderID,
                joborder.company_id AS companyID,
                company.name AS companyName,
                joborder.client_job_id AS clientJobID,
                joborder.title AS title,
                joborder.city AS city,
                joborder.state AS state,
                CONCAT(
                    recruiter_user.first_name, ' ', recruiter_user.last_name
                ) AS recruiterFullName,
                CONCAT(
                    owner_user.first_name, ' ', owner_user.last_name
                ) AS ownerFullName,
                DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateCreated,
                COUNT(
                    candidate_joborder.joborder_id
                ) AS pipeline,
                (
                    SELECT
                        COUNT(*)
                    FROM
                        candidate_joborder_status_history
                    WHERE
                        joborder_id = %s
                    AND
                        status_to = %s
                    AND
                        site_id = %s
                ) AS submitted,
                (
                    SELECT
                        COUNT(*)
                    FROM
                        candidate_joborder_status_history
                    WHERE
                        joborder_id = %s
                    AND
                        status_to = %s
                    AND
                        site_id = %s
                ) AS pipelinePlaced,
                (
                    SELECT
                        COUNT(*)
                    FROM
                        candidate_joborder_status_history
                    WHERE
                        joborder_id = %s
                    AND
                        status_to = %s
                    AND
                        site_id = %s
                ) AS pipelineInterving
            FROM
                joborder
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            LEFT JOIN user AS recruiter_user
                ON joborder.recruiter = recruiter_user.user_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
            LEFT JOIN user AS entered_by_user
                ON joborder.entered_by = entered_by_user.user_id
            LEFT JOIN candidate_joborder
                ON joborder.joborder_id = candidate_joborder.joborder_id
            WHERE
                joborder.joborder_id = %s
            AND
                joborder.site_id = %s
            GROUP BY
                joborder.joborder_id",
            $this->_db->makeQueryInteger($jobOrderID),
            PIPELINE_STATUS_SUBMITTED,
            $this->_siteID,
            $this->_db->makeQueryInteger($jobOrderID),
            PIPELINE_STATUS_PLACED,
            $this->_siteID,
            $this->_db->makeQueryInteger($jobOrderID),
            PIPELINE_STATUS_INTERVIEWING,
            $this->_siteID,
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }
    
    public function getEEOReport($modePeriod, $modeStatus)
    {
        switch ($modePeriod)
        {
            case 'month':
                $periodChriterion = 'AND TO_DAYS(candidate.date_modified) >= TO_DAYS(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))';
                break;
                
            case 'week':
                $periodChriterion = 'AND TO_DAYS(candidate.date_modified) >= TO_DAYS(DATE_SUB(CURDATE(), INTERVAL 7 DAY))';
                break;
            
            default:
                $periodChriterion = '';
                break;
        }
        
        switch ($modeStatus)
        {
            case 'placed':
                $statusChriterion = 'AND IF(candidate_joborder_status.candidate_joborder_id, 1, 0) = 1';
                $join = 'LEFT JOIN candidate_joborder AS candidate_joborder_status
                            ON candidate_joborder_status.candidate_id = candidate.candidate_id
                            AND candidate_joborder_status.status >= '.PIPELINE_STATUS_PLACED.'
                            AND candidate_joborder_status.site_id = '.$this->_siteID.'
                        ';
                break;
                
            case 'rejected':
                $statusChriterion = 'AND IF(candidate_joborder_status.candidate_joborder_id, 1, 0) = 1';
                $join = 'LEFT JOIN candidate_joborder AS candidate_joborder_status
                            ON candidate_joborder_status.candidate_id = candidate.candidate_id
                            AND candidate_joborder_status.status = '.PIPELINE_STATUS_NOTINCONSIDERATION.'
                            AND candidate_joborder_status.site_id = '.$this->_siteID.'
                        ';
                break;
            
            default:
                $statusChriterion = '';
                $join = '';
                break;
        }
        
        $chriterion = $periodChriterion . $statusChriterion;
        
        $sql = sprintf(
            "SELECT
                COUNT(candidate.candidate_id) AS totalCandidates
             FROM   
                candidate
                %s
             WHERE
                candidate.site_id = %s
                %s
            ",
            $join,
            $this->_siteID,
            $chriterion
        );
        
        $statistics['rsTotalCandidates'] = $this->_db->getAssoc($sql);
        
        $sql = sprintf(
            "SELECT
                (
                    SELECT
                        COUNT(candidate.candidate_id)
                    FROM 
                        candidate
                        %s
                    WHERE
                        candidate.eeo_ethnic_type_id = eeo_ethnic_type.eeo_ethnic_type_id
                    AND
                        candidate.site_id = %s
                        %s
                ) AS numberOfCandidates,
                eeo_ethnic_type.eeo_ethnic_type_id as EEOEthnicTypeID,
                eeo_ethnic_type.type as EEOEthnicType
             FROM   
                eeo_ethnic_type
            ",
            $join,
            $this->_siteID,
            $chriterion
        );
             
        $statistics['rsEthnicStatistics'] = $this->_db->getAllAssoc($sql);

        $sql = sprintf(
            "SELECT
                (
                    SELECT
                        COUNT(candidate.candidate_id)
                    FROM 
                        candidate
                        %s
                    WHERE
                        candidate.eeo_veteran_type_id = eeo_veteran_type.eeo_veteran_type_id
                    AND
                        candidate.site_id = %s
                        %s
                ) AS numberOfCandidates,
                eeo_veteran_type.eeo_veteran_type_id as EEOVeteranTypeID,
                eeo_veteran_type.type as EEOVeteranType
             FROM   
                eeo_veteran_type
            ",
            $join,
            $this->_siteID,
            $chriterion
        );
             
        $statistics['rsVeteranStatistics'] = $this->_db->getAllAssoc($sql);

        $sql = sprintf(
            "SELECT
                (
                    SELECT
                        COUNT(candidate.candidate_id)
                    FROM 
                        candidate
                        %s
                    WHERE
                        candidate.eeo_disability_status = 'Yes'
                    AND
                        candidate.site_id = %s
                        %s
                ) AS numberOfCandidatesDisabled,
                (
                    SELECT
                        COUNT(candidate.candidate_id)
                    FROM 
                        candidate
                        %s
                    WHERE
                        candidate.eeo_disability_status = 'No'
                    AND
                        candidate.site_id = %s
                        %s
                ) AS numberOfCandidatesNonDisabled
             FROM   
                candidate
            ",
            $join,
            $this->_siteID,
            $chriterion,
            $join,
            $this->_siteID,
            $chriterion
        );
             
        $statistics['rsDisabledStatistics'] = $this->_db->getAssoc($sql);
        
        $sql = sprintf(
            "SELECT
                (
                    SELECT
                        COUNT(candidate.candidate_id)
                    FROM 
                        candidate
                        %s
                    WHERE
                        candidate.eeo_gender = 'm'
                    AND
                        candidate.site_id = %s
                        %s
                ) AS numberOfCandidatesMale,
                (
                    SELECT
                        COUNT(candidate.candidate_id)
                    FROM 
                        candidate
                        %s
                    WHERE
                        candidate.eeo_gender = 'f'
                    AND
                        candidate.site_id = %s
                        %s
                ) AS numberOfCandidatesFemale
             FROM   
                candidate
            ",
            $join,
            $this->_siteID,
            $chriterion,
            $join,
            $this->_siteID,
            $chriterion
        );
             
        $statistics['rsGenderStatistics'] = $this->_db->getAssoc($sql);
        
        return $statistics;
    }
    

    /**
     * Returns an array containing the number of candidates currently in each
     * "status" in the pipeline.
     *
     * @return array statistics data
     */
    public function getPipelineData($jobOrderID = -1)
    {
        $sql = sprintf(
            "SELECT
                COUNT(*) AS totalPipeline,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS noStatus,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) +
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS noContact,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS contacted,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS qualifying,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS submitted,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS interviewing,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS offered,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS passedOn,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS placed,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS replied
            FROM
                candidate_joborder
            LEFT JOIN joborder
                ON joborder.joborder_id = candidate_joborder.joborder_id
            WHERE
                candidate_joborder.site_id = %s
            AND
                joborder.status != 'Closed'
            %s",
            PIPELINE_STATUS_NOSTATUS,
            PIPELINE_STATUS_NOCONTACT,
            PIPELINE_STATUS_NOTINCONSIDERATION,
            PIPELINE_STATUS_CONTACTED,
            PIPELINE_STATUS_QUALIFYING,
            PIPELINE_STATUS_SUBMITTED,
            PIPELINE_STATUS_INTERVIEWING,
            PIPELINE_STATUS_OFFERED,
            PIPELINE_STATUS_CLIENTDECLINED,
            PIPELINE_STATUS_PLACED,
            PIPELINE_STATUS_CANDIDATE_REPLIED,
            $this->_siteID,
            ($jobOrderID != -1 ? "AND candidate_joborder.joborder_id = ".$jobOrderID : "")
        );
        $rs = $this->_db->getAssoc($sql);

        if (empty($rs))
        {
            return array(
                'totalPipeline' => 0,
                'noStatus' => 0,
                'noContact' => 0,
                'contacted' => 0,
                'qualifying' => 0,
                'submitted' => 0,
                'interviewing' => 0,
                'offered' => 0,
                'passedOn' => 0,
                'placed' => 0
            );
        }

        return $rs;
    }


    // FIXME: Document me.
    private function makePeriodCriterion($dateField, $period)
    {
        /* Note: we add a bogus "AND date > '1900-01-01'" condition to the
         * WHERE clause to force MySQL to use an index containing the date
         * column. MySQL can then build the entire result set without scanning
         * any rows.
         */
        $criteria = '';
        switch ($period)
        {
            case TIME_PERIOD_TODAY:
                $criteria = sprintf(
                    'AND %s > \'1900-01-01\' AND DATE(%s) = CURDATE()',
                    $dateField,
                    $dateField
                );
                break;

            case TIME_PERIOD_YESTERDAY:
                $criteria = sprintf(
                    'AND %s > \'1900-01-01\' AND DATE(%s) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)',
                    $dateField,
                    $dateField
                );
                break;

            case TIME_PERIOD_THISWEEK:
                $criteria = sprintf(
                    'AND %s > \'1900-01-01\' AND YEARWEEK(%s) = YEARWEEK(NOW())',
                    $dateField,
                    $dateField
                );
                break;

            case TIME_PERIOD_LASTWEEK:
                $criteria = sprintf(
                    'AND %s > \'1900-01-01\' AND YEARWEEK(%s) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 7 DAY))',
                    $dateField,
                    $dateField
                );
                break;

            case TIME_PERIOD_LASTTWOWEEKS:
                $criteria =sprintf(
                    'AND %s > \'1900-01-01\' AND (YEARWEEK(%s) = YEARWEEK(NOW()) OR YEARWEEK(%s) = YEARWEEK(NOW() - INTERVAL 7 DAY))',
                    $dateField,
                    $dateField,
                    $dateField
                );
                break;

            case TIME_PERIOD_THISMONTH:
                $criteria = sprintf(
                    'AND %s > \'1900-01-01\' AND EXTRACT(YEAR_MONTH FROM %s) = EXTRACT(YEAR_MONTH FROM CURDATE())',
                    $dateField,
                    $dateField
                );
                break;

            case TIME_PERIOD_LASTMONTH:
                $criteria = sprintf(
                    'AND %s > \'1900-01-01\' AND EXTRACT(YEAR_MONTH FROM %s) = EXTRACT(YEAR_MONTH FROM DATE_SUB(CURDATE(), INTERVAL 1 MONTH))',
                    $dateField,
                    $dateField
                );
                break;

            case TIME_PERIOD_THISYEAR:
                $criteria = sprintf(
                    'AND %s > \'1900-01-01\' AND YEAR(%s) = YEAR(NOW())',
                    $dateField,
                    $dateField
                );
                break;

            case TIME_PERIOD_LASTYEAR:
                $criteria = sprintf(
                    'AND %s > \'1900-01-01\' AND YEAR(%s) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR))',
                    $dateField,
                    $dateField
                );
                break;

            case TIME_PERIOD_TODATE:
            default:
                return sprintf('AND %s > \'1900-01-01\'', $dateField);
                break;
        }

        if ($this->_timeZoneOffset != 0)
        {
            $criteria = str_replace('CURDATE()', 'DATE_ADD(CURDATE(), INTERVAL ' . $this->_timeZoneOffset . ' HOUR)', $criteria);
            $criteria = str_replace('NOW()', 'DATE_ADD(NOW(), INTERVAL ' . $this->_timeZoneOffset . ' HOUR)', $criteria);
            $criteria = str_replace($dateField, 'DATE_ADD(' . $dateField . ', INTERVAL ' . $this->_timeZoneOffset . ' HOUR)', $criteria);
        }

        return $criteria;
    }
}

?>
