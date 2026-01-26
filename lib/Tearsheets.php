<?php
/**
 * CATS
 * Tearsheets Library
 *
 * Provides functionality for creating and managing saved job order lists.
 * Inspired by Bullhorn's tearsheet feature.
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * Copyright (C) 2026 Space-O Technologies (https://www.spaceotechnologies.com/)
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
 * @version    $Id: Tearsheets.php 2026-01-25 $
 */

include_once('./lib/DatabaseConnection.php');

class Tearsheets
{
    private $_siteID;
    private $_db;

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
     * Create a new tearsheet
     *
     * @param int    $userID      User ID
     * @param string $name        Tearsheet name
     * @param string $description Description (optional)
     * @param bool   $isPublic    Whether visible to all users
     * @return int                New tearsheet ID
     */
    public function create($userID, $name, $description = '', $isPublic = false)
    {
        $sql = sprintf(
            "INSERT INTO tearsheet 
             (site_id, user_id, name, description, is_public, date_created)
             VALUES (%d, %d, %s, %s, %d, NOW())",
            $this->_siteID,
            intval($userID),
            $this->_db->makeQueryString($name),
            $this->_db->makeQueryString($description),
            $isPublic ? 1 : 0
        );

        $this->_db->query($sql);
        return $this->_db->getLastInsertID();
    }

    /**
     * Get a single tearsheet by ID
     *
     * @param int $tearsheetID Tearsheet ID
     * @return array|null      Tearsheet data or null if not found
     */
    public function get($tearsheetID)
    {
        $sql = sprintf(
            "SELECT t.*,
                    u.first_name as owner_first_name,
                    u.last_name as owner_last_name,
                    (SELECT COUNT(*)
                     FROM tearsheet_joborder tj
                     WHERE tj.tearsheet_id = t.tearsheet_id) as job_count,
                    (SELECT COUNT(*)
                     FROM tearsheet_candidate tc
                     WHERE tc.tearsheet_id = t.tearsheet_id) as candidate_count
             FROM tearsheet t
             LEFT JOIN user u ON t.user_id = u.user_id
             WHERE t.tearsheet_id = %d
               AND t.site_id = %d",
            intval($tearsheetID),
            $this->_siteID
        );

        $result = $this->_db->getAssoc($sql);

        if (!$result || empty($result)) {
            return null;
        }

        return $result;
    }

    /**
     * Get all tearsheets accessible to a user
     *
     * @param int|null $userID Optional user ID to filter by ownership
     * @return array           Array of tearsheet records
     */
    public function getAll($userID = null)
    {
        $sql = sprintf(
            "SELECT t.*,
                    u.first_name as owner_first_name,
                    u.last_name as owner_last_name,
                    (SELECT COUNT(*)
                     FROM tearsheet_joborder tj
                     WHERE tj.tearsheet_id = t.tearsheet_id) as job_count,
                    (SELECT COUNT(*)
                     FROM tearsheet_candidate tc
                     WHERE tc.tearsheet_id = t.tearsheet_id) as candidate_count
             FROM tearsheet t
             LEFT JOIN user u ON t.user_id = u.user_id
             WHERE t.site_id = %d",
            $this->_siteID
        );

        if ($userID !== null) {
            $sql .= sprintf(
                " AND (t.user_id = %d OR t.is_public = 1)",
                intval($userID)
            );
        }

        $sql .= " ORDER BY t.name ASC";

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Update a tearsheet
     *
     * @param int    $tearsheetID Tearsheet ID
     * @param string $name        New name
     * @param string $description New description
     * @param bool   $isPublic    New visibility
     * @return bool               Success
     */
    public function update($tearsheetID, $name, $description, $isPublic)
    {
        $sql = sprintf(
            "UPDATE tearsheet 
             SET name = %s,
                 description = %s,
                 is_public = %d,
                 date_modified = NOW()
             WHERE tearsheet_id = %d
               AND site_id = %d",
            $this->_db->makeQueryString($name),
            $this->_db->makeQueryString($description),
            $isPublic ? 1 : 0,
            intval($tearsheetID),
            $this->_siteID
        );

        return $this->_db->query($sql);
    }

    /**
     * Delete a tearsheet
     *
     * @param int $tearsheetID Tearsheet ID
     * @return bool            Success
     */
    public function delete($tearsheetID)
    {
        // CASCADE will handle tearsheet_joborder cleanup
        $sql = sprintf(
            "DELETE FROM tearsheet 
             WHERE tearsheet_id = %d 
               AND site_id = %d",
            intval($tearsheetID),
            $this->_siteID
        );

        return $this->_db->query($sql);
    }

    /**
     * Add a job order to a tearsheet
     *
     * @param int $tearsheetID Tearsheet ID
     * @param int $jobOrderID  Job Order ID
     * @param int $addedBy     User ID who added it
     * @return bool            Success
     */
    public function addJobOrder($tearsheetID, $jobOrderID, $addedBy = null)
    {
        $sql = sprintf(
            "INSERT IGNORE INTO tearsheet_joborder 
             (tearsheet_id, joborder_id, date_added, added_by)
             VALUES (%d, %d, NOW(), %s)",
            intval($tearsheetID),
            intval($jobOrderID),
            $addedBy ? intval($addedBy) : 'NULL'
        );

        return $this->_db->query($sql);
    }

    /**
     * Add multiple job orders to a tearsheet
     *
     * @param int   $tearsheetID Tearsheet ID
     * @param array $jobOrderIDs Array of Job Order IDs
     * @param int   $addedBy     User ID who added them
     * @return int               Number of jobs added
     */
    public function addJobOrders($tearsheetID, array $jobOrderIDs, $addedBy = null)
    {
        $added = 0;
        foreach ($jobOrderIDs as $jobOrderID) {
            if ($this->addJobOrder($tearsheetID, $jobOrderID, $addedBy)) {
                $added++;
            }
        }
        return $added;
    }

    /**
     * Remove a job order from a tearsheet
     *
     * @param int $tearsheetID Tearsheet ID
     * @param int $jobOrderID  Job Order ID
     * @return bool            Success
     */
    public function removeJobOrder($tearsheetID, $jobOrderID)
    {
        $sql = sprintf(
            "DELETE FROM tearsheet_joborder 
             WHERE tearsheet_id = %d 
               AND joborder_id = %d",
            intval($tearsheetID),
            intval($jobOrderID)
        );

        return $this->_db->query($sql);
    }

    /**
     * Get all job orders in a tearsheet
     *
     * @param int $tearsheetID Tearsheet ID
     * @return array           Array of job order records
     */
    public function getJobOrders($tearsheetID)
    {
        $sql = sprintf(
            "SELECT j.joborder_id,
                    j.title,
                    j.description,
                    j.city,
                    j.state,
                    j.status,
                    j.public,
                    j.date_created,
                    j.date_modified,
                    j.salary,
                    j.type,
                    j.duration,
                    j.openings,
                    j.start_date,
                    c.company_id,
                    c.name as company_name,
                    u.user_id as recruiter_id,
                    u.first_name as recruiter_first_name,
                    u.last_name as recruiter_last_name,
                    tj.date_added as added_to_tearsheet,
                    tj.added_by
             FROM tearsheet_joborder tj
             INNER JOIN joborder j ON tj.joborder_id = j.joborder_id
             LEFT JOIN company c ON j.company_id = c.company_id
             LEFT JOIN user u ON j.recruiter = u.user_id
             WHERE tj.tearsheet_id = %d
             ORDER BY tj.date_added DESC",
            intval($tearsheetID)
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Get job order IDs in a tearsheet
     *
     * @param int $tearsheetID Tearsheet ID
     * @return array           Array of job order IDs
     */
    public function getJobOrderIDs($tearsheetID)
    {
        $sql = sprintf(
            "SELECT joborder_id 
             FROM tearsheet_joborder 
             WHERE tearsheet_id = %d",
            intval($tearsheetID)
        );

        $results = $this->_db->getAllAssoc($sql);
        return array_column($results, 'joborder_id');
    }

    /**
     * Check if a job order is in a tearsheet
     *
     * @param int $tearsheetID Tearsheet ID
     * @param int $jobOrderID  Job Order ID
     * @return bool            True if job is in tearsheet
     */
    public function hasJobOrder($tearsheetID, $jobOrderID)
    {
        $sql = sprintf(
            "SELECT COUNT(*) as count 
             FROM tearsheet_joborder 
             WHERE tearsheet_id = %d 
               AND joborder_id = %d",
            intval($tearsheetID),
            intval($jobOrderID)
        );

        $result = $this->_db->getAssoc($sql);
        return intval($result['count']) > 0;
    }

    /**
     * Get count of jobs in a tearsheet
     *
     * @param int $tearsheetID Tearsheet ID
     * @return int             Job count
     */
    public function getJobOrderCount($tearsheetID)
    {
        $sql = sprintf(
            "SELECT COUNT(*) as count 
             FROM tearsheet_joborder 
             WHERE tearsheet_id = %d",
            intval($tearsheetID)
        );

        $result = $this->_db->getAssoc($sql);
        return intval($result['count']);
    }

    /**
     * Find tearsheets containing a specific job order
     *
     * @param int $jobOrderID Job Order ID
     * @return array          Array of tearsheet records
     */
    public function findByJobOrder($jobOrderID)
    {
        $sql = sprintf(
            "SELECT t.*, 
                    (SELECT COUNT(*) 
                     FROM tearsheet_joborder tj2 
                     WHERE tj2.tearsheet_id = t.tearsheet_id) as job_count
             FROM tearsheet t
             INNER JOIN tearsheet_joborder tj ON t.tearsheet_id = tj.tearsheet_id
             WHERE tj.joborder_id = %d
               AND t.site_id = %d",
            intval($jobOrderID),
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Clone a tearsheet with all its job orders and candidates
     *
     * @param int    $tearsheetID Source tearsheet ID
     * @param int    $userID      New owner user ID
     * @param string $newName     Name for the clone
     * @return int                New tearsheet ID
     */
    public function duplicate($tearsheetID, $userID, $newName = null)
    {
        $original = $this->get($tearsheetID);
        if (!$original) {
            return false;
        }

        $name = $newName ?: $original['name'] . ' (Copy)';

        $newID = $this->create(
            $userID,
            $name,
            $original['description'],
            false  // New copy is private by default
        );

        // Copy all job orders
        $jobOrders = $this->getJobOrderIDs($tearsheetID);
        foreach ($jobOrders as $jobOrderID) {
            $this->addJobOrder($newID, $jobOrderID, $userID);
        }

        // Copy all candidates
        $candidates = $this->getCandidateIDs($tearsheetID);
        foreach ($candidates as $candidateID) {
            $this->addCandidate($newID, $candidateID, $userID);
        }

        return $newID;
    }

    // ========================================================================
    // CANDIDATE ASSOCIATION METHODS
    // ========================================================================

    /**
     * Add a candidate to a tearsheet
     *
     * @param int $tearsheetID Tearsheet ID
     * @param int $candidateID Candidate ID
     * @param int $addedBy     User ID who added it
     * @return bool            Success
     */
    public function addCandidate($tearsheetID, $candidateID, $addedBy = null)
    {
        $sql = sprintf(
            "INSERT IGNORE INTO tearsheet_candidate
             (tearsheet_id, candidate_id, date_added, added_by)
             VALUES (%d, %d, NOW(), %s)",
            intval($tearsheetID),
            intval($candidateID),
            $addedBy ? intval($addedBy) : 'NULL'
        );

        return $this->_db->query($sql);
    }

    /**
     * Add multiple candidates to a tearsheet
     *
     * @param int   $tearsheetID  Tearsheet ID
     * @param array $candidateIDs Array of Candidate IDs
     * @param int   $addedBy      User ID who added them
     * @return int                Number of candidates added
     */
    public function addCandidates($tearsheetID, array $candidateIDs, $addedBy = null)
    {
        $added = 0;
        foreach ($candidateIDs as $candidateID) {
            if ($this->addCandidate($tearsheetID, $candidateID, $addedBy)) {
                $added++;
            }
        }
        return $added;
    }

    /**
     * Remove a candidate from a tearsheet
     *
     * @param int $tearsheetID Tearsheet ID
     * @param int $candidateID Candidate ID
     * @return bool            Success
     */
    public function removeCandidate($tearsheetID, $candidateID)
    {
        $sql = sprintf(
            "DELETE FROM tearsheet_candidate
             WHERE tearsheet_id = %d
               AND candidate_id = %d",
            intval($tearsheetID),
            intval($candidateID)
        );

        return $this->_db->query($sql);
    }

    /**
     * Get all candidates in a tearsheet
     *
     * @param int $tearsheetID Tearsheet ID
     * @return array           Array of candidate records
     */
    public function getCandidates($tearsheetID)
    {
        $sql = sprintf(
            "SELECT c.candidate_id,
                    c.first_name,
                    c.last_name,
                    c.email1,
                    c.phone_home,
                    c.phone_cell,
                    c.city,
                    c.state,
                    c.current_employer,
                    c.current_pay,
                    c.desired_pay,
                    c.can_relocate,
                    c.is_hot,
                    c.date_created,
                    c.date_modified,
                    tc.date_added as added_to_tearsheet,
                    tc.added_by
             FROM tearsheet_candidate tc
             INNER JOIN candidate c ON tc.candidate_id = c.candidate_id
             WHERE tc.tearsheet_id = %d
             ORDER BY tc.date_added DESC",
            intval($tearsheetID)
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Get candidate IDs in a tearsheet
     *
     * @param int $tearsheetID Tearsheet ID
     * @return array           Array of candidate IDs
     */
    public function getCandidateIDs($tearsheetID)
    {
        $sql = sprintf(
            "SELECT candidate_id
             FROM tearsheet_candidate
             WHERE tearsheet_id = %d",
            intval($tearsheetID)
        );

        $results = $this->_db->getAllAssoc($sql);
        return array_column($results, 'candidate_id');
    }

    /**
     * Check if a candidate is in a tearsheet
     *
     * @param int $tearsheetID Tearsheet ID
     * @param int $candidateID Candidate ID
     * @return bool            True if candidate is in tearsheet
     */
    public function hasCandidate($tearsheetID, $candidateID)
    {
        $sql = sprintf(
            "SELECT COUNT(*) as count
             FROM tearsheet_candidate
             WHERE tearsheet_id = %d
               AND candidate_id = %d",
            intval($tearsheetID),
            intval($candidateID)
        );

        $result = $this->_db->getAssoc($sql);
        return intval($result['count']) > 0;
    }

    /**
     * Get count of candidates in a tearsheet
     *
     * @param int $tearsheetID Tearsheet ID
     * @return int             Candidate count
     */
    public function getCandidateCount($tearsheetID)
    {
        $sql = sprintf(
            "SELECT COUNT(*) as count
             FROM tearsheet_candidate
             WHERE tearsheet_id = %d",
            intval($tearsheetID)
        );

        $result = $this->_db->getAssoc($sql);
        return intval($result['count']);
    }

    /**
     * Find tearsheets containing a specific candidate
     *
     * @param int $candidateID Candidate ID
     * @return array           Array of tearsheet records
     */
    public function findByCandidate($candidateID)
    {
        $sql = sprintf(
            "SELECT t.*,
                    (SELECT COUNT(*)
                     FROM tearsheet_joborder tj2
                     WHERE tj2.tearsheet_id = t.tearsheet_id) as job_count,
                    (SELECT COUNT(*)
                     FROM tearsheet_candidate tc2
                     WHERE tc2.tearsheet_id = t.tearsheet_id) as candidate_count
             FROM tearsheet t
             INNER JOIN tearsheet_candidate tc ON t.tearsheet_id = tc.tearsheet_id
             WHERE tc.candidate_id = %d
               AND t.site_id = %d",
            intval($candidateID),
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }
}
