<?php
/**
 * CATS
 * Candidates Library
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
 * @version    $Id: Candidates.php 3813 2007-12-05 23:16:22Z brian $
 */

include_once(LEGACY_ROOT . '/lib/Attachments.php');
include_once(LEGACY_ROOT . '/lib/Pipelines.php');
include_once(LEGACY_ROOT . '/lib/History.php');
include_once(LEGACY_ROOT . '/lib/SavedLists.php');
include_once(LEGACY_ROOT . '/lib/ExtraFields.php');
include_once(LEGACY_ROOT . '/lib/DataGrid.php');


/**
 *  Candidates Library
 *  @package    CATS
 *  @subpackage Library
 */
class Candidates
{
    private $_db;
    private $_siteID;

    public $extraFields;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        $this->extraFields = new ExtraFields($siteID, DATA_ITEM_CANDIDATE);
    }

    /**
     * Adds a candidate to the database and returns its candidate ID.
     *
     * @param string First name.
     * @param string Middle name / initial.
     * @param string Last name.
     * @param string Primary e-mail address.
     * @param string Secondary e-mail address.
     * @param string Home phone number.
     * @param string Mobile phone number.
     * @param string Work phone number.
     * @param string Address (can be multiple lines).
     * @param string City.
     * @param string State / province.
     * @param string Postal code.
     * @param string Source where this candidate was found.
     * @param string Key skills.
     * @param string Date available.
     * @param string Current employer.
     * @param boolean Is this candidate willing to relocate?
     * @param string Current pay rate / salary.
     * @param string Desired pay rate / salary.
     * @param string Misc. candidate notes.
     * @param string Candidate's personal web site.
     * @param integer Entered-by user ID.
     * @param integer Owner user ID.
     * @param string EEO gender, or '' to not specify.
     * @param string EEO gender, or '' to not specify.
     * @param string EEO veteran status, or '' to not specify.
     * @param string EEO disability status, or '' to not specify.
     * @param boolean Skip creating a history entry?
     * @return integer Candidate ID of new candidate, or -1 on failure.
     */
    public function add($firstName, $middleName, $lastName, $email1, $email2,
        $phoneHome, $phoneCell, $phoneWork, $address, $city, $state, $zip,
        $source, $keySkills, $dateAvailable, $currentEmployer, $canRelocate,
        $currentPay, $desiredPay, $notes, $webSite, $bestTimeToCall, $enteredBy, $owner,
        $gender = '', $race = '', $veteran = '', $disability = '',
        $skipHistory = false)
    {
        $sql = sprintf(
            "INSERT INTO candidate (
                first_name,
                middle_name,
                last_name,
                email1,
                email2,
                phone_home,
                phone_cell,
                phone_work,
                address,
                city,
                state,
                zip,
                source,
                key_skills,
                date_available,
                current_employer,
                can_relocate,
                current_pay,
                desired_pay,
                notes,
                web_site,
                best_time_to_call,
                entered_by,
                is_hot,
                owner,
                site_id,
                date_created,
                date_modified,
                eeo_ethnic_type_id,
                eeo_veteran_type_id,
                eeo_disability_status,
                eeo_gender
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
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                0,
                %s,
                %s,
                NOW(),
                NOW(),
                %s,
                %s,
                %s,
                %s
            )",
            $this->_db->makeQueryString($firstName),
            $this->_db->makeQueryString($middleName),
            $this->_db->makeQueryString($lastName),
            $this->_db->makeQueryString($email1),
            $this->_db->makeQueryString($email2),
            $this->_db->makeQueryString($phoneHome),
            $this->_db->makeQueryString($phoneCell),
            $this->_db->makeQueryString($phoneWork),
            $this->_db->makeQueryString($address),
            $this->_db->makeQueryString($city),
            $this->_db->makeQueryString($state),
            $this->_db->makeQueryString($zip),
            $this->_db->makeQueryString($source),
            $this->_db->makeQueryString($keySkills),
            $this->_db->makeQueryStringOrNULL($dateAvailable),
            $this->_db->makeQueryString($currentEmployer),
            ($canRelocate ? '1' : '0'),
            $this->_db->makeQueryString($currentPay),
            $this->_db->makeQueryString($desiredPay),
            $this->_db->makeQueryString($notes),
            $this->_db->makeQueryString($webSite),
            $this->_db->makeQueryString($bestTimeToCall),
            $this->_db->makeQueryInteger($enteredBy),
            $this->_db->makeQueryInteger($owner),
            $this->_siteID,
            $this->_db->makeQueryInteger($race),
            $this->_db->makeQueryInteger($veteran),
            $this->_db->makeQueryString($disability),
            $this->_db->makeQueryString($gender)
        );
        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        $candidateID = $this->_db->getLastInsertID();

        if (!$skipHistory)
        {
            $history = new History($this->_siteID);
            $history->storeHistoryNew(DATA_ITEM_CANDIDATE, $candidateID);
        }

        return $candidateID;
    }

    /**
     * Updates a candidate.
     *
     * @param integer Candidate ID to update.
     * @param string First name.
     * @param string Middle name / initial.
     * @param string Last name.
     * @param string Primary e-mail address.
     * @param string Secondary e-mail address.
     * @param string Home phone number.
     * @param string Mobile phone number.
     * @param string Work phone number.
     * @param string Address (can be multiple lines).
     * @param string City.
     * @param string State / province.
     * @param string Postal code.
     * @param string Source where this candidate was found.
     * @param string Key skills.
     * @param string Date available.
     * @param string Current employer.
     * @param boolean Is this candidate willing to relocate?
     * @param string Current pay rate / salary.
     * @param string Desired pay rate / salary.
     * @param string Misc. candidate notes.
     * @param string Candidate's personal web site.
     * @param integer Owner user ID.
     * @param string EEO gender, or '' to not specify.
     * @param string EEO gender, or '' to not specify.
     * @param string EEO veteran status, or '' to not specify.
     * @param string EEO disability status, or '' to not specify.
     * @return boolean True if successful; false otherwise.
     */
    public function update($candidateID, $isActive, $firstName, $middleName, $lastName,
        $email1, $email2, $phoneHome, $phoneCell, $phoneWork, $address,
        $city, $state, $zip, $source, $keySkills, $dateAvailable,
        $currentEmployer, $canRelocate, $currentPay, $desiredPay,
        $notes, $webSite, $bestTimeToCall, $owner, $isHot, $email, $emailAddress,
        $gender = '', $race = '', $veteran = '', $disability = '')
    {
        $sql = sprintf(
            "UPDATE
                candidate
            SET
                is_active             = %s,
                first_name            = %s,
                middle_name           = %s,
                last_name             = %s,
                email1                = %s,
                email2                = %s,
                phone_home            = %s,
                phone_work            = %s,
                phone_cell            = %s,
                address               = %s,
                city                  = %s,
                state                 = %s,
                zip                   = %s,
                source                = %s,
                key_skills            = %s,
                date_available        = %s,
                current_employer      = %s,
                current_pay           = %s,
                desired_pay           = %s,
                can_relocate          = %s,
                is_hot                = %s,
                notes                 = %s,
                web_site              = %s,
                best_time_to_call     = %s,
                owner                 = %s,
                date_modified         = NOW(),
                eeo_ethnic_type_id    = %s,
                eeo_veteran_type_id   = %s,
                eeo_disability_status = %s,
                eeo_gender            = %s
            WHERE
                candidate_id = %s
            AND
                site_id = %s",
            ($isActive ? '1' : '0'),
            $this->_db->makeQueryString($firstName),
            $this->_db->makeQueryString($middleName),
            $this->_db->makeQueryString($lastName),
            $this->_db->makeQueryString($email1),
            $this->_db->makeQueryString($email2),
            $this->_db->makeQueryString($phoneHome),
            $this->_db->makeQueryString($phoneWork),
            $this->_db->makeQueryString($phoneCell),
            $this->_db->makeQueryString($address),
            $this->_db->makeQueryString($city),
            $this->_db->makeQueryString($state),
            $this->_db->makeQueryString($zip),
            $this->_db->makeQueryString($source),
            $this->_db->makeQueryString($keySkills),
            $this->_db->makeQueryStringOrNULL($dateAvailable),
            $this->_db->makeQueryString($currentEmployer),
            $this->_db->makeQueryString($currentPay),
            $this->_db->makeQueryString($desiredPay),
            ($canRelocate ? '1' : '0'),
            ($isHot ? '1' : '0'),
            $this->_db->makeQueryString($notes),
            $this->_db->makeQueryString($webSite),
            $this->_db->makeQueryString($bestTimeToCall),
            $this->_db->makeQueryInteger($owner),
            $this->_db->makeQueryInteger($race),
            $this->_db->makeQueryInteger($veteran),
            $this->_db->makeQueryString($disability),
            $this->_db->makeQueryString($gender),
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );

        $preHistory = $this->get($candidateID);
        $queryResult = $this->_db->query($sql);
        $postHistory = $this->get($candidateID);

        $history = new History($this->_siteID);
        $history->storeHistoryChanges(
            DATA_ITEM_CANDIDATE, $candidateID, $preHistory, $postHistory
        );

        if (!$queryResult)
        {
            return false;
        }

        if (!empty($emailAddress))
        {
            /* Send e-mail notification. */
            //FIXME: Make subject configurable.
            $mailer = new Mailer($this->_siteID);
            $mailerStatus = $mailer->sendToOne(
                array($emailAddress, ''),
                'CATS Notification: Candidate Ownership Change',
                $email,
                true
            );
        }

        return true;
    }

    /**
     * Removes a candidate and all associated records from the system.
     *
     * @param integer Candidate ID to delete.
     * @return void
     */
    public function delete($candidateID)
    {
        /* Delete the candidate from candidate. */
        $sql = sprintf(
            "DELETE FROM
                candidate
            WHERE
                candidate_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );
        $this->_db->query($sql);

        $history = new History($this->_siteID);
        $history->storeHistoryDeleted(DATA_ITEM_CANDIDATE, $candidateID);

        /* Delete pipeline entries from candidate_joborder. */
        $sql = sprintf(
            "DELETE FROM
                candidate_joborder
            WHERE
                candidate_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );
        $this->_db->query($sql);

        /* Delete pipeline history from candidate_joborder_status_history. */
        $sql = sprintf(
            "DELETE FROM
                candidate_joborder_status_history
            WHERE
                candidate_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );
        $this->_db->query($sql);

        /* Delete from saved lists. */
        $sql = sprintf(
            "DELETE FROM
                saved_list_entry
            WHERE
                data_item_id = %s
            AND
                site_id = %s
            AND
                data_item_type = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID,
            DATA_ITEM_CANDIDATE
        );
        $this->_db->query($sql);
        
        /* Delete from candidate_duplicates. */
        $sql = sprintf(
            "DELETE FROM
                candidate_duplicates
            WHERE
                old_candidate_id = %s
            OR
                new_candidate_id = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_db->makeQueryInteger($candidateID)
        );
        $this->_db->query($sql);

        /* Delete attachments. */
        $attachments = new Attachments($this->_siteID);
        $attachmentsRS = $attachments->getAll(
            DATA_ITEM_CANDIDATE, $candidateID
        );

        foreach ($attachmentsRS as $rowNumber => $row)
        {
            $attachments->delete($row['attachmentID']);
        }

        /* Delete extra fields. */
        $this->extraFields->deleteValueByDataItemID($candidateID);
    }

    /**
     * Returns all relevent candidate information for a given candidate ID.
     *
     * @param integer Candidate ID.
     * @return array Associative result set array of candidate data, or array()
     *               if no records were returned.
     */
    public function get($candidateID)
    {
        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.is_active AS isActive,
                candidate.first_name AS firstName,
                candidate.middle_name AS middleName,
                candidate.last_name AS lastName,
                candidate.email1 AS email1,
                candidate.email2 AS email2,
                candidate.phone_home AS phoneHome,
                candidate.phone_work AS phoneWork,
                candidate.phone_cell AS phoneCell,
                candidate.address AS address,
                candidate.city AS city,
                candidate.state AS state,
                candidate.zip AS zip,
                candidate.source AS source,
                candidate.key_skills AS keySkills,
                candidate.current_employer AS currentEmployer,
                candidate.current_pay AS currentPay,
                candidate.desired_pay AS desiredPay,
                candidate.notes AS notes,
                candidate.owner AS owner,
                candidate.can_relocate AS canRelocate,
                candidate.web_site AS webSite,
                candidate.best_time_to_call AS bestTimeToCall,
                candidate.is_hot AS isHot,
                candidate.is_admin_hidden AS isAdminHidden,
                DATE_FORMAT(
                    candidate.date_created, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate.date_modified, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateModified,
                COUNT(
                    candidate_joborder.joborder_id
                ) AS pipeline,
                (
                    SELECT
                        COUNT(*)
                    FROM
                        candidate_joborder_status_history
                    WHERE
                        candidate_id = %s
                    AND
                        status_to = %s
                    AND
                        site_id = %s
                ) AS submitted,
                CONCAT(
                    candidate.first_name, ' ', candidate.last_name
                ) AS candidateFullName,
                CONCAT(
                    entered_by_user.first_name, ' ', entered_by_user.last_name
                ) AS enteredByFullName,
                CONCAT(
                    owner_user.first_name, ' ', owner_user.last_name
                ) AS ownerFullName,
                owner_user.email AS owner_email,
                DATE_FORMAT(
                    candidate.date_available, '%%m-%%d-%%y'
                ) AS dateAvailable,
                eeo_ethnic_type.type AS eeoEthnicType,
                eeo_veteran_type.type AS eeoVeteranType,
                candidate.eeo_disability_status AS eeoDisabilityStatus,
                candidate.eeo_gender AS eeoGender,
                IF (candidate.eeo_gender = 'm',
                    'Male',
                    IF (candidate.eeo_gender = 'f',
                        'Female',
                        ''))
                     AS eeoGenderText
            FROM
                candidate
            LEFT JOIN user AS entered_by_user
                ON candidate.entered_by = entered_by_user.user_id
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
            LEFT JOIN candidate_joborder
                ON candidate.candidate_id = candidate_joborder.candidate_id
            LEFT JOIN eeo_ethnic_type
                ON eeo_ethnic_type.eeo_ethnic_type_id = candidate.eeo_ethnic_type_id
            LEFT JOIN eeo_veteran_type
                ON eeo_veteran_type.eeo_veteran_type_id = candidate.eeo_veteran_type_id
            WHERE
                candidate.candidate_id = %s
            AND
                candidate.site_id = %s
            GROUP BY
                candidate.candidate_id",
            $this->_db->makeQueryInteger($candidateID),
            PIPELINE_STATUS_SUBMITTED,
            $this->_siteID,
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }
    
    public function getWithDuplicity($candidateID)
    {
        $data = $this->get($candidateID);
        
        $sql = sprintf(
            "SELECT
                candidate_duplicates.old_candidate_id AS duplicateTo
            FROM
                candidate_duplicates
            WHERE
                candidate_duplicates.new_candidate_id = %s",
            $this->_db->makeQueryInteger($candidateID)
            );
        $rs = $this->_db->getAllAssoc($sql);
        $temp = array();
        if($rs && !$this->_db->isEOF())
        {
            foreach($rs as $row)
            {
                array_push($temp, $row);
            }
        }
        $data['isDuplicate'] = $temp;
        return $data;
    }

    /**
     * Returns all candidate information relevent for the Edit Candidate page
     * for a given candidate ID.
     *
     * @param integer Candidate ID.
     * @return array Associative result set array of candidate data, or array()
     *               if no records were returned.
     */
    public function getForEditing($candidateID)
    {
        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.is_active AS isActive,
                candidate.first_name AS firstName,
                candidate.middle_name AS middleName,
                candidate.last_name AS lastName,
                candidate.email1 AS email1,
                candidate.email2 AS email2,
                candidate.phone_home AS phoneHome,
                candidate.phone_work AS phoneWork,
                candidate.phone_cell AS phoneCell,
                candidate.address AS address,
                candidate.city AS city,
                candidate.state AS state,
                candidate.zip AS zip,
                candidate.source AS source,
                candidate.key_skills AS keySkills,
                candidate.current_employer AS currentEmployer,
                candidate.current_pay AS currentPay,
                candidate.desired_pay AS desiredPay,
                candidate.notes AS notes,
                candidate.owner AS owner,
                candidate.can_relocate AS canRelocate,
                candidate.web_site AS webSite,
                candidate.best_time_to_call AS bestTimeToCall,
                candidate.is_hot AS isHot,
                candidate.eeo_ethnic_type_id AS eeoEthnicTypeID,
                candidate.eeo_veteran_type_id AS eeoVeteranTypeID,
                candidate.eeo_disability_status AS eeoDisabilityStatus,
                candidate.eeo_gender AS eeoGender,
                candidate.is_admin_hidden AS isAdminHidden,
                DATE_FORMAT(
                    candidate.date_available, '%%m-%%d-%%y'
                ) AS dateAvailable
            FROM
                candidate
            WHERE
                candidate.candidate_id = %s
            AND
                candidate.site_id = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }

    // FIXME: Document me.
    public function getExport($IDs)
    {
        if (count($IDs) != 0)
        {
            $IDsValidated = array();
            
            foreach ($IDs as $id)
            {
                $IDsValidated[] = $this->_db->makeQueryInteger($id);
            }
            
            $criterion = 'AND candidate.candidate_id IN ('.implode(',', $IDsValidated).')';
        }
        else
        {
            $criterion = '';
        }

        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.last_name AS lastName,
                candidate.first_name AS firstName,
                candidate.phone_home AS phoneHome,
                candidate.phone_cell AS phoneCell,
                candidate.email1 AS email1,
                candidate.key_skills as keySkills
            FROM
                candidate
            WHERE
                candidate.site_id = %s
                %s
            ORDER BY
                candidate.last_name ASC,
                candidate.first_name ASC",
            $this->_siteID,
            $criterion
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns a candidate ID that matches the specified e-mail address.
     *
     * @param string Candidate e-mail address,
     * @return integer Candidate ID, or -1 if no matching candidates were
     *                 found.
     */
    public function getIDByEmail($email)
    {
        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID
            FROM
                candidate
            WHERE
            (
                candidate.email1 = %s
                OR candidate.email2 = %s
            )
            AND
                candidate.site_id = %s",
            $this->_db->makeQueryString($email),
            $this->_db->makeQueryString($email),
            $this->_siteID
        );
        $rs = $this->_db->getAssoc($sql);

        if (empty($rs))
        {
            return -1;
        }

        return $rs['candidateID'];
    }
    public function getIDByPhone($phone)
    {
        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID
            FROM
                candidate
            WHERE
            (
                candidate.phone_home = %s
                OR candidate.phone_cell = %s
                OR candidate.phone_work = %s
            )
            AND
                candidate.site_id = %s",
            $this->_db->makeQueryString($phone),
            $this->_db->makeQueryString($phone),
            $this->_db->makeQueryString($phone),
            $this->_siteID
        );
        $rs = $this->_db->getAssoc($sql);
         
        if (empty($rs))
        {
            return -1;
        }
         
        return $rs['candidateID'];
    }
     

    /**
     * Returns the number of candidates in the system.  Useful
     * for determining if the friendly "no candidates in system"
     * should be displayed rather than the datagrid.
     *
     * @param boolean Include administratively hidden candidates?
     * @return integer Number of Candidates in site.
     */
    public function getCount($allowAdministrativeHidden = false)
    {
        if (!$allowAdministrativeHidden)
        {
            $adminHiddenCriterion = 'AND candidate.is_admin_hidden = 0';
        }
        else
        {
            $adminHiddenCriterion = '';
        }

        $sql = sprintf(
            "SELECT
                COUNT(*) AS totalCandidates
            FROM
                candidate
            WHERE
                candidate.site_id = %s
            %s",
            $this->_siteID,
            $adminHiddenCriterion
        );

        return $this->_db->getColumn($sql, 0, 0);
    }

    /**
     * Returns the entire candidates list.
     *
     * @param boolean Include administratively hidden candidates?
     * @return array Multi-dimensional associative result set array of
     *               candidates data, or array() if no records were returned.
     */
    public function getAll($allowAdministrativeHidden = false)
    {
        if (!$allowAdministrativeHidden)
        {
            $adminHiddenCriterion = 'AND candidate.is_admin_hidden = 0';
        }
        else
        {
            $adminHiddenCriterion = '';
        }

        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.last_name AS lastName,
                candidate.first_name AS firstName,
                candidate.phone_home AS phoneHome,
                candidate.phone_cell AS phoneCell,
                candidate.email1 AS email1,
                candidate.key_skills AS keySkills,
                candidate.is_hot AS isHot,
                DATE_FORMAT(
                    candidate.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                candidate.date_created AS dateCreatedSort,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                candidate
            LEFT JOIN user AS owner_user
                ON candidate.entered_by = user.user_id
            WHERE
                candidate.site_id = %s
            %s
            ORDER BY
                candidate.last_name ASC,
                candidate.first_name ASC",
            $this->_siteID,
            $adminHiddenCriterion
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all resumes for a candidate.
     *
     * @param integer Candidate ID.
     * @return array Multi-dimensional associative result set array of
     *               candidate attachments data, or array() if no records were
     *               returned.
     */
    public function getResumes($candidateID)
    {
        $sql = sprintf(
            "SELECT
                attachment.attachment_id AS attachmentID,
                attachment.data_item_id AS candidateID,
                attachment.title AS title,
                attachment.text AS text
            FROM
                attachment
            WHERE
                resume = 1
            AND
                attachment.data_item_type = %s
            AND
                attachment.data_item_id = %s
            AND
                attachment.site_id = %s",
            DATA_ITEM_CANDIDATE,
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns a candidate resume attachment by attachment.
     *
     * @param integer Attachment ID.
     * @return array Associative result set array of candidate / attachment
     *               data, or array() if no records were returned.
     */
    public function getResume($attachmentID)
    {
        $sql = sprintf(
            "SELECT
                attachment.attachment_id AS attachmentID,
                attachment.data_item_id AS candidateID,
                attachment.title AS title,
                attachment.text AS text,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName
            FROM
                attachment
            LEFT JOIN candidate
                ON attachment.data_item_id = candidate.candidate_id
                AND attachment.site_id = candidate.site_id
            WHERE
                attachment.resume = 1
            AND
                attachment.attachment_id = %s
            AND
                attachment.site_id = %s",
            $this->_db->makeQueryInteger($attachmentID),
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Returns an array of job orders data (jobOrderID, title, companyName)
     * for the specified candidate ID.
     *
     * @param integer Candidate ID,
     * @return array Multi-dimensional associative result set array of
     *               job orders data, or array() if no records were returned.
     */
    public function getJobOrdersArray($candidateID)
    {
        $sql = sprintf(
            "SELECT
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                company.name AS companyName
            FROM
                joborder
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            LEFT JOIN candidate_joborder
                ON joborder.joborder_id = candidate_joborder.joborder_id
            WHERE
                candidate_joborder.candidate_id = %s
            AND
                joborder.site_id = %s
            ORDER BY
                title ASC",
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
     }

    /**
     * Updates a candidate's modified timestamp.
     *
     * @param integer Candidate ID.
     * @return boolean Boolean was the query executed successfully?
     */
    public function updateModified($candidateID)
    {
        $sql = sprintf(
            "UPDATE
                candidate
            SET
                date_modified = NOW()
            WHERE
                candidate_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Returns all upcoming events for the candidate.
     *
     * @param integer Candidate ID.
     * @return array Multi-dimensional associative result set array of
     *               candidate events data, or array() if no records were
     *               returned.
     */
    public function getUpcomingEvents($candidateID)
    {
        $calendar = new Calendar($this->_siteID);
        return $calendar->getUpcomingEventsByDataItem(
            DATA_ITEM_CANDIDATE, $candidateID
        );
    }

    /**
     * Gets all possible source suggestions for a site.
     *
     * @return array Multi-dimensional associative result set array of
     *               candidate sources data.
     */
    public function getPossibleSources()
    {
        $sql = sprintf(
            "SELECT
                candidate_source.source_id AS sourceID,
                candidate_source.name AS name
            FROM
                candidate_source
            WHERE
                candidate_source.site_id = %s
            ORDER BY
                candidate_source.name ASC",
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Updates a sites possible sources with an array generated
     * by getDifferencesFromList (ListEditor.php).
     *
     * @param array Result of ListEditor::getDifferencesFromList().
     * @return void
     */
    public function updatePossibleSources($updates)
    {
        $history = new History($this->_siteID);

        foreach ($updates as $update)
        {
            switch ($update[2])
            {
                case LIST_EDITOR_ADD:
                    $sql = sprintf(
                        "INSERT INTO candidate_source (
                            name,
                            site_id,
                            date_created
                         )
                         VALUES (
                            %s,
                            %s,
                            NOW()
                         )",
                         $this->_db->makeQueryString($update[0]),
                         $this->_siteID
                    );
                    $this->_db->query($sql);

                    break;

                case LIST_EDITOR_REMOVE:
                    $sql = sprintf(
                        "DELETE FROM
                            candidate_source
                         WHERE
                            source_id = %s
                         AND
                            site_id = %s",
                         $update[1],
                         $this->_siteID
                    );
                    $this->_db->query($sql);

                    break;

                case LIST_EDITOR_MODIFY:
                    $sql = sprintf(
                        "SELECT
                            name
                         FROM
                            candidate_source
                         WHERE
                            source_id = %s
                         AND
                            site_id = %s",
                         $this->_db->makeQueryInteger($update[1]),
                         $this->_siteID
                    );
                    $firstSource = $this->_db->getAssoc($sql);

                    $sql = sprintf(
                        "UPDATE
                            candidate
                         SET
                            source = %s
                         WHERE
                            source = %s
                         AND
                            site_id = %s",
                         $update[1],
                         $this->_db->makeQueryString($firstSource['name']),
                         $this->_siteID
                    );
                    $this->_db->query($sql);

                    $sql = sprintf(
                        "UPDATE
                            candidate_source
                         SET
                            name = %s
                         WHERE
                            source_id = %s
                         AND
                            site_id = %s",
                         $this->_db->makeQueryString($update[0]),
                         $this->_db->makeQueryInteger($update[1]),
                         $this->_siteID
                    );
                    $this->_db->query($sql);

                    break;

                default:
                    break;
            }
        }
    }

    /**
     * Changes the administrative hide / show flag.
     * Only can be accessed by a MSA or higher user.
     *
     * @param integer Candidate ID.
     * @param boolean Administratively hide this candidate?
     * @return boolean Was the query executed successfully?
     */    
    public function administrativeHideShow($candidateID, $state)
    {
        $sql = sprintf(
            "UPDATE
                candidate
            SET
                is_admin_hidden = %s
            WHERE
                candidate_id = %s
            AND
                site_id = %s",
            ($state ? 1 : 0),
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );

        return (boolean) $this->_db->query($sql);
    }

    public function checkDuplicity($firstName, $middleName, $lastName, $email1, $email2, $phoneHome, $phoneCell, $phoneWork, $address, $city)
    {
        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.middle_name AS middleName,
                candidate.phone_home AS phoneHome,
                candidate.phone_cell AS phoneCell,
                candidate.phone_work AS phoneWork,
                candidate.email1 AS email1,
                candidate.email2 AS email2,
                candidate.address AS address,
                candidate.city AS city
            FROM
                candidate
            WHERE
                candidate.first_name = %s AND
                candidate.last_name = %s",
            $this->_db->makeQueryStringOrNULL($firstName),
            $this->_db->makeQueryStringOrNULL($lastName)
        );
        
        $rs = $this->_db->getAllAssoc($sql);
        
        $duplicatesID = array();
        
        if($rs && !$this->_db->isEOF())
        {
            $phoneNumbers = array();

            if($phoneHome != ""){array_push($phoneNumbers, preg_replace('/\s+/', '', $phoneHome));}
            if($phoneCell != ""){array_push($phoneNumbers, preg_replace('/\s+/', '', $phoneCell));}
            if($phoneWork != ""){array_push($phoneNumbers, preg_replace('/\s+/', '', $phoneWork));}
            
            $phoneNumbers = array_map('strtolower', $phoneNumbers);
            $phoneNumbers = array_map('trim', $phoneNumbers);
            
            
            foreach($rs as $row)
            {   
                $phoneNumbersDB = array();
                if($row['phoneHome'] != ""){array_push($phoneNumbersDB, preg_replace('/\s+/', '', $row['phoneHome']));}
                if($row['phoneCell'] != ""){array_push($phoneNumbersDB, preg_replace('/\s+/', '', $row['phoneCell']));}
                if($row['phoneWork'] != ""){array_push($phoneNumbersDB, preg_replace('/\s+/', '', $row['phoneWork']));}
                $phoneNumbersDB = array_map('strtolower', $phoneNumbersDB);
                $phoneNumbersDB = array_map('trim', $phoneNumbersDB);
                
                if (strtolower($row['middleName']) == strtolower($middleName) && $middleName != "")
                {
                    array_push($duplicatesID, $row['candidateID']);
                }
                else if(sizeof(array_diff($phoneNumbers, $phoneNumbersDB)) != sizeof($phoneNumbers) || sizeof(array_diff($phoneNumbersDB, $phoneNumbers)) != sizeof($phoneNumbersDB))
                {
                    array_push($duplicatesID, $row['candidateID']);
                }
                else if((strtolower(trim($email1)) == strtolower(trim($row['email1'])) && trim($email1)!= "" ) || (strtolower(trim($email1)) == strtolower(trim($row['email2'])) && trim($email1) != "") ||
                        (strtolower(trim($email2)) == strtolower(trim($row['email1'])) && trim($email2)!= "" ) || (strtolower(trim($email2)) == strtolower(trim($row['email2'])) && trim($email2) != ""))
                {
                    array_push($duplicatesID, $row['candidateID']);
                }
                else if(strtolower(trim($city)) == strtolower(trim($row['city'])) && trim($city) != "")
                {
                    if(strtolower(trim($address)) == strtolower(trim($row['address'])) && trim($address) != "")
                    {
                         array_push($duplicatesID, $row['candidateID']);
                    }
                }
            }
            return $duplicatesID;
        }
        else
        {
            return $duplicatesID;    
        }   
    }
    
     /**
     * Returns the number of duplicates in the system.
     *
     * @return array Number of Duplicates in site.
     */
    public function getDuplicatesCount()
    {
        $sql = sprintf(
            "SELECT
                COUNT(*) AS totalDuplicates
            FROM 
                (SELECT * 
                    FROM candidate_duplicates
                WHERE
                    candidate_duplicates.site_id = %s
                GROUP BY
                    candidate_duplicates.new_candidate_id) as innerQuery",
            $this->_siteID
        );
        return $this->_db->getColumn($sql, 0, 0);
    }


    /**
     * Removes a candidate's duplicate warning/link from the system.
     * @param $oldCandidateID int the candidate that is going to stay
     * @param $newCandidateID int the candidate that is going to be deleted after merge
     */
     public function removeDuplicity($oldCandidateID, $newCandidateID)
    {
        $sql = sprintf(
                "DELETE FROM 
                    candidate_duplicates
                WHERE
                    candidate_duplicates.old_candidate_id = %s
                AND
                    candidate_duplicates.new_candidate_id = %s",
                 $this->_db->makeQueryStringOrNULL($oldCandidateID),
                 $this->_db->makeQueryStringOrNULL($newCandidateID)
            );
        $this->_db->query($sql);
    }
    
    /**
     * Adds a duplicate to the database.
     *
     * @param $candidateID string first candidate ID.
     * @param $duplicates string second candidate ID.
     * @return int 1 on success, or -1 on failure.
     */
    
    public function addDuplicates($candidateID, $duplicates)
    {
        if(is_array($duplicates))
        {
            foreach($duplicates as $duplicateID)
            {
                $sql = sprintf(
                            "INSERT INTO candidate_duplicates (
                                old_candidate_id,
                                new_candidate_id,
                                site_id
                             )
                             VALUES (
                                %s,
                                %s,
                                %s
                             )",
                             $this->_db->makeQueryString($duplicateID),
                             $this->_db->makeQueryString($candidateID),
                             $this->_siteID
                        );
                $this->_db->query($sql);
            }
        }
        else if($duplicates != "")
        {
            $sql = sprintf(
                            "INSERT INTO candidate_duplicates (
                                old_candidate_id,
                                new_candidate_id,
                                site_id
                             )
                             VALUES (
                                %s,
                                %s,
                                %s
                             )",
                             $this->_db->makeQueryString($duplicates),
                             $this->_db->makeQueryString($candidateID),
                             $this->_siteID
                        );
                $this->_db->query($sql);
        }
    }
    
    
    
    public function mergeDuplicates($params, $rs)
    {
        $oldCandidateID = $params['oldCandidateID'];
        $newCandidateID = $params['newCandidateID']; 
         $sql = sprintf(
            "UPDATE
                activity
            SET
                data_item_id = %s
            WHERE
                data_item_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($oldCandidateID),
            $this->_db->makeQueryInteger($newCandidateID),
            $this->_siteID
        );

        $this->_db->query($sql);
        
        $sql = sprintf(
            "UPDATE
                attachment
            SET
                data_item_id = %s
            WHERE
                data_item_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($oldCandidateID),
            $this->_db->makeQueryInteger($newCandidateID),
            $this->_siteID
        );

        $this->_db->query($sql);
        
        $sql = sprintf(
            "UPDATE
                calendar_event
            SET
                data_item_id = %s
            WHERE
                data_item_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($oldCandidateID),
            $this->_db->makeQueryInteger($newCandidateID),
            $this->_siteID
        );

        $this->_db->query($sql);
        
        $sql = sprintf(
            "DELETE FROM
                candidate_duplicates
            WHERE
                new_candidate_id = %s",
            $this->_db->makeQueryInteger($newCandidateID)
        );

        $this->_db->query($sql);

        $sql = sprintf(
            "SELECT 
                new_candidate_id AS newID
            FROM 
                candidate_duplicates
            WHERE
                old_candidate_id = %s
            ",
            $this->_db->makeQueryInteger($newCandidateID)
        );

        $rsTmp = $this->_db->getAllAssoc($sql);

        if($rsTmp || count($rsTmp) > 0){
            foreach($rsTmp AS $index => $newID){
                $sql = sprintf(
                    "DELETE FROM
                    candidate_duplicates
                WHERE
                    new_candidate_id = %s
                AND
                    old_candidate_id = %s",
                $this->_db->makeQueryInteger($newID['newID']),
                $this->_db->makeQueryInteger($newCandidateID)
                );

                $this->_db->query($sql);

                $sql = sprintf(
                    "INSERT IGNORE INTO
                    candidate_duplicates(old_candidate_id, new_candidate_id, site_id)
                VALUES
                    (%s, %s, %s)",
                    $this->_db->makeQueryInteger($oldCandidateID),
                    $this->_db->makeQueryInteger($newID['newID']),
                    $this->_siteID
                );

                $this->_db->query($sql);
            }
        }

        $sql = sprintf(
            "UPDATE
                candidate_tag
            SET
                candidate_id = %s
            WHERE
                candidate_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($oldCandidateID),
            $this->_db->makeQueryInteger($newCandidateID),
            $this->_siteID
        );

        $this->_db->query($sql);

        $this->mergePipelines($oldCandidateID, $newCandidateID);
        $this->mergeLists($oldCandidateID, $newCandidateID);

        $update = " ";
        $comma = false;
        
        if($params['firstName'] == "1")
        {
            $update .= "first_name = '" . $rs['firstName']."'";
            $comma = true;
        }
        if($params['middleName'] == "1")
        {
            if($comma)
            {
                $update .= ", ";
            }
            $update .= "middle_name = '" . $rs['middleName']."'";
            $comma = true;
        }
        if($params['lastName'] == "1")
        {
            if($comma)
            {
                $update .= ", ";
            }
            $update .= "last_name = '" . $rs['lastName']."'";
            $comma = true;
        }
        if($params['phoneCell'] == "1")
        {
            if($comma)
            {
                $update .= ", ";
            }
            $update .= "phone_cell = '" . $rs['phoneCell']."'";
            $comma = true;
        }
        if($params['phoneWork'] == "1")
        {
            if($comma)
            {
                $update .= ", ";
            }
            $update .= "phone_work = '" . $rs['phoneWork']."'";
            $comma = true;
        }
        if($params['phoneHome'] == "1")
        {
            if($comma)
            {
                $update .= ", ";
            }
            $update .= "phone_home = '" . $rs['phoneHome']."'";
            $comma = true;
        }
        if($params['address'] == "1")
        {
            if($comma)
            {
                $update .= ", ";
            }
            $update .= "address = '" . $rs['address'] . "', city = '" . $rs['city'] . "', zip = '" . $rs['zip'] . "', state = '" . $rs['state'] . "'";
            $comma = true;
        }
        if($params['website'] == "1")
        {
            if($comma)
            {
                $update .= ", ";
            }
            $update .= "web_site = '" . $rs['webSite'] . "'";
            $comma = true;
        }
        if(sizeof($params['emails']) == 1)
        {
            if($comma)
            {
                $update .= ", ";
            }
            $update .= "email1 = '" . $params['emails'][0]."'";
            $comma = true;
        }else if(sizeof($params['emails']) == 2)
        {
            if($comma)
            {
                $update .= ", ";
                $comma = false;
            }
            $update .= "email1 = '" . $params['emails'][0] . "', ";
            $update .= "email2 = '" . $params['emails'][1] . "', ";
            $comma = false;
        }
        if($comma){
           $update .= ", "; 
        }
        $dateAvailable = $rs['dateAvailable'];
        $dateParts = explode("-", $dateAvailable);
        $dateAvailable = "20" . $dateParts[2] . "-" . $dateParts[0] . "-" . $dateParts[1] . " 00:00:00";
        $update .= "is_active = " . $rs['isActive'] . ", " .
                    "current_employer = '" . $rs['currentEmployer'] . "', " .
                    "current_pay = '" . $rs['currentPay'] . "', " .     
                    "desired_pay = '" . $rs['desiredPay'] . "', " .  
                    "can_relocate = " . $rs['canRelocate'] . ", " .  
                    "best_time_to_call = '" . $rs['bestTimeToCall'] . "', " .
                    "is_hot = " . $rs['isHot'] . ", " . 
                    "date_modified = NOW()";
        $comma = true;
        if($rs['source'] != "" && $rs['source'] != "(none)")
        {
            if($comma){$update .= ", ";}
            $update.= "source = IFNULL(CONCAT(source, ', ".$rs['source'] . "'), '" . $rs['source'] . "')";
            $comma = true;
        }
        if($rs['keySkills'] != "")
        {   
            if($comma){$update .= ", ";}    
            $update .= "key_skills = IFNULL(CONCAT(key_skills, ', ".$rs['keySkills']."'), '" . $rs['keySkills'] . "')";
            $comma = true;
        }
        if($rs['notes'] != "")
        { 
            if($comma){$update .= ", ";}  
            $update .= "notes = IFNULL(CONCAT(notes, ', ".$rs['notes']."'), '" . $rs['notes'] . "')";
            $comma = true;
        }
        if($rs['date_available'] != "")
        { 
            if($comma){$update .= ", ";}  
            $update .= "date_available = '".$dateAvailable."' ";
        }
        
        $sql = sprintf(
            "UPDATE
                candidate
            SET
                %s 
            WHERE
                candidate_id = %s
            AND
                site_id = %s",
            $update,
            $this->_db->makeQueryInteger($oldCandidateID),
            $this->_siteID
        );
        
        if($this->_db->query($sql))
        {
            $sql = sprintf(
                "DELETE FROM
                    candidate
                WHERE
                    candidate_id = %s",
                $this->_db->makeQueryInteger($newCandidateID)
            );
            $this->_db->query($sql);
        }
    }

    private function mergeLists($oldCandidateID, $newCandidateID){
        /* Get list IDs where both old and new candidate already are placed */
        $sql = sprintf("
            SELECT
                saved_list_id AS listID
            FROM 
                saved_list_entry
            WHERE
                data_item_id IN(%s, %s)
            AND 
                data_item_type = %s 
            AND
                site_id = %s
            GROUP BY
                saved_list_id
            HAVING COUNT(data_item_id) > 1
            ",
            $this->_db->makeQueryInteger($oldCandidateID),
            $this->_db->makeQueryInteger($newCandidateID),
            DATA_ITEM_CANDIDATE,
            $this->_db->makeQueryInteger($this->_siteID)

        );

        $rsTmp = $this->_db->getAllAssoc($sql);
        if($rsTmp && count($rsTmp) > 0){
            $listIDs = "";
            foreach($rsTmp as $row){
                $listIDs .= $row['listID'];
                $listIDs .= ", ";
            }
            $listIDs = substr($listIDs, 0, strlen($lists) - 2);
        } else {
            $listIDs = "0";
        }

        /* update all entries where no duplicate rows will occur */
        $sql = sprintf(
            "UPDATE
                saved_list_entry
            SET
                data_item_id = %s
            WHERE
                data_item_id = %s
            AND
                saved_list_id NOT IN(%s)
            AND 
                site_id = %s",
            $this->_db->makeQueryInteger($oldCandidateID),
            $this->_db->makeQueryInteger($newCandidateID),
            $listIDs,
            $this->_siteID
        );

        $this->_db->query($sql);

        /* Delete rows which would cause duplicate rows if updated with the oldCandidateID */

        $sql = sprintf("
                DELETE FROM
                    saved_list_entry
                WHERE
                    data_item_id = %s 
                AND 
                    data_item_type = %s 
                AND 
                    site_id = %s",
            $this->_db->makeQueryInteger($newCandidateID),
            DATA_ITEM_CANDIDATE,
            $this->_db->makeQueryInteger($this->_siteID)
        );
        $this->_db->query($sql);

        foreach($rsTmp as $row) {
            $sql = sprintf("
                UPDATE
                    saved_list
                SET
                    number_entries = number_entries - 1
                WHERE
                    saved_list_id = %s
                AND 
                    site_id = %s",

                $this->_db->makeQueryInteger($row['listID']),
                $this->_db->makeQueryInteger($this->_siteID)
            );
            $this->_db->query($sql);
        }
    }

    private function mergePipelines($oldCandidateID, $newCandidateID){
        /* start: find joborders that would cause duplicate rows in db (when both candidates already belong to the pipeline prior to merge) */
        $sql = sprintf("
            SELECT
                joborder_id AS jobOrderID
            FROM 
                candidate_joborder
            WHERE
                candidate_id IN(%s, %s)
            AND 
                site_id = %s
            GROUP BY
                joborder_id
            HAVING COUNT(candidate_id) > 1
            ",
            $this->_db->makeQueryInteger($oldCandidateID),
            $this->_db->makeQueryInteger($newCandidateID),
            $this->_db->makeQueryInteger($this->_siteID)
        );

        $rsTmp = $this->_db->getAllAssoc($sql);
        if($rsTmp && count($rsTmp) > 0){
            $jobOrderIDs = "";
            foreach($rsTmp as $row){
                $jobOrderIDs .= $row['jobOrderID'];
                $jobOrderIDs .= ", ";
            }
            $jobOrderIDs = substr($jobOrderIDs, 0, strlen($jobOrderIDs) - 2);
        } else {
            $jobOrderIDs = "0";
        }
        /* end: find joborders that would cause duplicate rows in db (when both candidates already belong to the pipeline prior to merge) */

        /* start: update pipeline and pipeline status history for job orders that will not cause duplicate rows in database */
        $sql = sprintf(
            "UPDATE
                candidate_joborder
            SET
                candidate_id = %s
            WHERE
                candidate_id = %s
            AND
                joborder_id NOT IN(%s)
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($oldCandidateID),
            $this->_db->makeQueryInteger($newCandidateID),
            $jobOrderIDs,
            $this->_siteID
        );

        $this->_db->query($sql);

        $sql = sprintf(
            "UPDATE
                candidate_joborder_status_history
            SET
                candidate_id = %s
            WHERE
                candidate_id = %s
            AND
                joborder_id NOT IN(%s)
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($oldCandidateID),
            $this->_db->makeQueryInteger($newCandidateID),
            $jobOrderIDs,
            $this->_siteID
        );

        $this->_db->query($sql);
        /* end: update pipeline and pipeline status history for job orders that will not cause duplicate rows in database */

        /*start: take care of candidates that are in the same lists and would cause duplicates if merged */
        foreach($rsTmp as $row){
            $sql = sprintf("
                SELECT
                    candidate_joborder_id AS candidateJobOrderID,
                    status AS status,
                    candidate_id as candidateID
                FROM
                    candidate_joborder
                WHERE
                    joborder_id = %s 
                AND 
                    candidate_id IN (%s, %s)
                AND 
                    site_id = %s
                ORDER BY
                    status DESC",
                $this->_db->makeQueryInteger($row['jobOrderID']),
                $this->_db->makeQueryInteger($oldCandidateID),
                $this->_db->makeQueryInteger($newCandidateID),
                $this->_db->makeQueryInteger($this->_siteID)
            );
            $rs2 = $this->_db->getAllAssoc($sql);

            //delete from pipeline the lower status
            $sql = sprintf("
                    DELETE FROM
                        candidate_joborder
                    WHERE
                        candidate_joborder_id = %s
                    AND 
                        site_id = %s",
                $this->_db->makeQueryInteger($rs2[1]['candidateJobOrderID']),
                $this->_db->makeQueryInteger($this->_siteID)
            );
            $this->_db->query($sql);

            // if the old candidate has higher status, keep it and delete new candidate pipeline history
            if($rs2[0]['candidateID'] == $oldCandidateID){

                $sql = sprintf("
                    DELETE FROM
                        candidate_joborder_status_history
                    WHERE
                        candidate_id = %s
                    AND 
                        site_id = %s",
                    $this->_db->makeQueryInteger($newCandidateID),
                    $this->_db->makeQueryInteger($this->_siteID)
                );
                $this->_db->query($sql);
            }
            // if the newer candidate (to be merged and deleted) has higher status, keep this status and its history
            else {
                $sql = sprintf("
                    DELETE FROM
                        candidate_joborder_status_history
                    WHERE
                        candidate_id = %s
                    AND 
                        site_id = %s",
                    $this->_db->makeQueryInteger($oldCandidateID),
                    $this->_db->makeQueryInteger($this->_siteID)
                );
                $this->_db->query($sql);

                $sql = sprintf(
                    "UPDATE
                        candidate_joborder
                    SET
                        candidate_id = %s
                    WHERE
                        candidate_id = %s
                    AND
                        site_id = %s",
                    $this->_db->makeQueryInteger($oldCandidateID),
                    $this->_db->makeQueryInteger($newCandidateID),
                    $this->_siteID
                );
                $this->_db->query($sql);

                $sql = sprintf(
                    "UPDATE
                        candidate_joborder_status_history
                    SET
                        candidate_id = %s
                    WHERE
                        candidate_id = %s
                    AND
                        site_id = %s",
                    $this->_db->makeQueryInteger($oldCandidateID),
                    $this->_db->makeQueryInteger($newCandidateID),
                    $this->_siteID
                );
                $this->_db->query($sql);
            }
        }
        /*end: take care of candidates that are in the same lists and would cause duplicates if merged */
    }
    
    public function checkIfLinked($oldCandidateID, $newCandidateID)
    {
        if($oldCandidateID == $newCandidateID)
        {
            return true;
        }
        
        $sql = sprintf(
            "SELECT 
                candidate_duplicates.old_candidate_id as oldCandidateID,
                candidate_duplicates.new_candidate_id as newCandidateID
            FROM
                candidate_duplicates
            WHERE
                candidate_duplicates.old_candidate_id = %s
            AND
                candidate_duplicates.new_candidate_id = %s
            OR
                candidate_duplicates.old_candidate_id = %s
            AND
                candidate_duplicates.new_candidate_id = %s",
            $this->_db->makeQueryInteger($oldCandidateID),
            $this->_db->makeQueryInteger($newCandidateID),
            $this->_db->makeQueryInteger($newCandidateID),
            $this->_db->makeQueryInteger($oldCandidateID)
        );
        $rs = $this->_db->getAllAssoc($sql);
        
        if($rs && !$this->_db->isEOF())
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function getListsForCandidate($candidateID){
        $sql = sprintf("
            SELECT 
                saved_list.description AS name,
                saved_list.saved_list_id AS listID
            FROM
                saved_list
            WHERE 
                data_item_type = %s 
            AND 
                site_id = %s 
            AND 
                saved_list_id IN (
                  SELECT
                      saved_list_entry.saved_list_id
                  FROM
                      saved_list_entry
                  WHERE
                      site_id = %s 
                  AND
                      data_item_type = %s 
                  AND 
                      data_item_id = %s
                )",
            DATA_ITEM_CANDIDATE,
                $this->_siteID,
                $this->_siteID,
                DATA_ITEM_CANDIDATE,
                $candidateID
            );
        return $this->_db->getAllAssoc($sql);
    }
}


class CandidatesDataGrid extends DataGrid
{
    protected $_siteID;

    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($instanceName, $siteID, $parameters, $misc = 0)
    {
        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;
        $this->_assignedCriterion = "";
        $this->_dataItemIDColumn = 'candidate.candidate_id';

        $this->_classColumns = array(
            'Attachments' => array('select' => 'IF(candidate_joborder_submitted.candidate_joborder_id, 1, 0) AS submitted,
                                                IF(attachment_id, 1, 0) AS attachmentPresent,
                                                IF(old_candidate_id, 1, 0) AS duplicatePresent',

                                     'pagerRender' => 'if ($rsData[\'duplicatePresent\'] == 1 && $_SESSION[\'CATS\']->getAccessLevel(\'candidates.duplicates\') >= ACCESS_LEVEL_SA)
                                                    {
                                                        $return = \'<img src="images/wf_error.gif" alt="" width="16" height="16" title="Possible Duplicate" />\';
                                                    }
                                                    else
                                                    {
                                                        $return = \'<img src="images/mru/blank.gif" alt="" width="16" height="16" />\';
                                                    }
                                     
                                                    if ($rsData[\'submitted\'] == 1)
                                                    {
                                                        $return .= \'<img src="images/job_orders.gif" alt="" width="16" height="16" title="Submitted for a Job Order" />\';
                                                    }
                                                    else
                                                    {
                                                        $return .= \'<img src="images/mru/blank.gif" alt="" width="16" height="16" />\';
                                                    }

                                                    if ($rsData[\'attachmentPresent\'] == 1)
                                                    {
                                                        $return .= \'<img src="images/paperclip.gif" alt="" width="16" height="16" title="Attachment Present" />\';
                                                    }
                                                    else
                                                    {
                                                        $return .= \'<img src="images/mru/blank.gif" alt="" width="16" height="16" />\';
                                                    }
                                                    return $return;
                                                   ',

                                     'join'     => 'LEFT JOIN attachment
                                                        ON candidate.candidate_id = attachment.data_item_id
														AND attachment.data_item_type = '.DATA_ITEM_CANDIDATE.'
                                                    LEFT JOIN candidate_joborder AS candidate_joborder_submitted
                                                        ON candidate_joborder_submitted.candidate_id = candidate.candidate_id
                                                        AND candidate_joborder_submitted.status >= '.PIPELINE_STATUS_SUBMITTED.'
                                                        AND candidate_joborder_submitted.site_id = '.$this->_siteID.'
                                                        AND candidate_joborder_submitted.status != '.PIPELINE_STATUS_NOTINCONSIDERATION.' LEFT JOIN candidate_duplicates 
                                                        ON candidate.candidate_id = 
                                                        candidate_duplicates.new_candidate_id'
                                                    
                                   ,
                                     'pagerWidth'    => 100,
                                     'pagerOptional' => true,
                                     'pagerNoTitle' => true,
                                     'sizable'  => true,
                                     'exportable' => false,
                                     'filterable' => false),

            'First Name' =>     array('select'         => 'candidate.first_name AS firstName',
                                      'pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'firstName\']).\'</a>\';',
                                      'sortableColumn' => 'firstName',
                                      'pagerWidth'     => 75,
                                      'pagerOptional'  => false,
                                      'alphaNavigation'=> true,
                                      'filter'         => 'candidate.first_name'),

            'Last Name' =>      array('select'         => 'candidate.last_name AS lastName',
                                     'sortableColumn'  => 'lastName',
                                     'pagerRender'     => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'lastName\']).\'</a>\';',
                                     'pagerWidth'      => 85,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filter'         => 'candidate.last_name'),

            'E-Mail' =>         array('select'   => 'candidate.email1 AS email1',
                                     'sortableColumn'     => 'email1',
                                     'pagerWidth'    => 80,
                                     'filter'         => 'candidate.email1'),

            '2nd E-Mail' =>     array('select'   => 'candidate.email2 AS email2',
                                     'sortableColumn'     => 'email2',
                                     'pagerWidth'    => 80,
                                     'filter'         => 'candidate.email2'),

            'Home Phone' =>     array('select'   => 'candidate.phone_home AS phoneHome',
                                     'sortableColumn'     => 'phoneHome',
                                     'pagerWidth'    => 80,
                                     'filter'         => 'candidate.phone_home'),

            'Cell Phone' =>     array('select'   => 'candidate.phone_cell AS phoneCell',
                                     'sortableColumn'     => 'phoneCell',
                                     'pagerWidth'    => 80,
                                     'filter'         => 'candidate.phone_cell'),

            'Work Phone' =>     array('select'   => 'candidate.phone_work AS phoneWork',
                                     'sortableColumn'     => 'phoneWork',
                                     'pagerWidth'    => 80,
                                     'filter'         => 'candidate.phone_work'),

            'Address' =>        array('select'   => 'candidate.address AS address',
                                     'sortableColumn'     => 'address',
                                     'pagerWidth'    => 250,
                                     'alphaNavigation' => true,
                                     'filter'         => 'candidate.address'),

            'City' =>           array('select'   => 'candidate.city AS city',
                                     'sortableColumn'     => 'city',
                                     'pagerWidth'    => 80,
                                     'alphaNavigation' => true,
                                     'filter'         => 'candidate.city'),


            'State' =>          array('select'   => 'candidate.state AS state',
                                     'sortableColumn'     => 'state',
                                     'filterType' => 'dropDown',
                                     'pagerWidth'    => 50,
                                     'alphaNavigation' => true,
                                     'filter'         => 'candidate.state'),

            'Zip' =>            array('select'  => 'candidate.zip AS zip',
                                     'sortableColumn'    => 'zip',
                                     'pagerWidth'   => 50,
                                     'filter'         => 'candidate.zip'),

            'Misc Notes' =>     array('select'  => 'candidate.notes AS notes',
                                     'sortableColumn'    => 'notes',
                                     'pagerWidth'   => 300,
                                     'filter'         => 'candidate.notes'),

            'Web Site' =>      array('select'  => 'candidate.web_site AS webSite',
                                     'pagerRender'     => 'return \'<a href="\'.htmlspecialchars($rsData[\'webSite\']).\'">\'.htmlspecialchars($rsData[\'webSite\']).\'</a>\';',
                                     'sortableColumn'    => 'webSite',
                                     'pagerWidth'   => 80,
                                     'filter'         => 'candidate.web_site'),

            'Key Skills' =>    array('select'  => 'candidate.key_skills AS keySkills',
                                     'pagerRender' => 'return substr(trim($rsData[\'keySkills\']), 0, 30) . (strlen(trim($rsData[\'keySkills\'])) > 30 ? \'...\' : \'\');',
                                     'sortableColumn'    => 'keySkills',
                                     'pagerWidth'   => 210,
                                     'filter'         => 'candidate.key_skills'),

            'Recent Status' => array('select'  => '(
                                                    SELECT
                                                        CONCAT(
                                                            \'<a href="'.CATSUtility::getIndexName().'?m=joborders&amp;a=show&amp;jobOrderID=\',
                                                            joborder.joborder_id,
                                                            \'" title="\',
                                                            joborder.title,
                                                            \' (\',
                                                            company.name,
                                                            \')">\',
                                                            candidate_joborder_status.short_description,
                                                            \'</a>\'
                                                        )
                                                    FROM
                                                        candidate_joborder
                                                    LEFT JOIN candidate_joborder_status
                                                        ON candidate_joborder_status.candidate_joborder_status_id = candidate_joborder.status
                                                    LEFT JOIN joborder
                                                        ON joborder.joborder_id = candidate_joborder.joborder_id
                                                    LEFT JOIN company
                                                        ON joborder.company_id = company.company_id
                                                    WHERE
                                                        candidate_joborder.candidate_id = candidate.candidate_id
                                                    ORDER BY
                                                        candidate_joborder.date_modified DESC
                                                    LIMIT 1
                                                ) AS lastStatus
                                                ',
                                     'sort'    => 'lastStatus',
                                     'pagerRender'     => 'return $rsData[\'lastStatus\'];',
                                     'exportRender'     => 'return $rsData[\'lastStatus\'];',
                                     'pagerWidth'   => 140,
                                     'exportable' => false,
                                     'filterHaving'  => 'lastStatus',
                                     'filterTypes'   => '=~'),

            'Recent Status (Extended)' => array('select'  => '(
                                                    SELECT
                                                        CONCAT(
                                                            candidate_joborder_status.short_description,
                                                            \'<br />\',
                                                            \'<a href="'.CATSUtility::getIndexName().'?m=companies&amp;a=show&amp;companyID=\',
                                                            company.company_id,
                                                            \'">\',
                                                            company.name,
                                                            \'</a> - \',
                                                            \'<a href="'.CATSUtility::getIndexName().'?m=joborders&amp;a=show&amp;jobOrderID=\',
                                                            joborder.joborder_id,
                                                            \'">\',
                                                            joborder.title,
                                                            \'</a>\'
                                                        )
                                                    FROM
                                                        candidate_joborder
                                                    LEFT JOIN candidate_joborder_status
                                                        ON candidate_joborder_status.candidate_joborder_status_id = candidate_joborder.status
                                                    LEFT JOIN joborder
                                                        ON joborder.joborder_id = candidate_joborder.joborder_id
                                                    LEFT JOIN company
                                                        ON joborder.company_id = company.company_id
                                                    WHERE
                                                        candidate_joborder.candidate_id = candidate.candidate_id
                                                    ORDER BY
                                                        candidate_joborder.date_modified DESC
                                                    LIMIT 1
                                                ) AS lastStatusLong
                                                ',
                                     'sortableColumn'    => 'lastStatusLong',
                                     'pagerRender'     => 'return $rsData[\'lastStatusLong\'];',
                                     'pagerWidth'   => 310,
                                     'exportable' => false,
                                     'filterable' => false),

            'Source' =>        array('select'  => 'candidate.source AS source',
                                     'sortableColumn'    => 'source',
                                     'pagerWidth'   => 140,
                                     'alphaNavigation' => true,
                                     'filter'         => 'candidate.source'),

            'Available' =>     array('select'   => 'DATE_FORMAT(candidate.date_available, \'%m-%d-%y\') AS dateAvailable',
                                     'sortableColumn'     => 'dateAvailable',
                                     'pagerWidth'    => 60),

            'Current Employer' => array('select'  => 'candidate.current_employer AS currentEmployer',
                                     'sortableColumn'    => 'currentEmployer',
                                     'pagerWidth'   => 125,
                                     'alphaNavigation' => true,
                                     'filter'         => 'candidate.current_employer'),

            'Current Pay' => array('select'  => 'candidate.current_pay AS currentPay',
                                     'sortableColumn'    => 'currentPay',
                                     'pagerWidth'   => 125,
                                     'filter'         => 'candidate.current_pay',
                                     'filterTypes'   => '===>=<'),

            'Desired Pay' => array('select'  => 'candidate.desired_pay AS desiredPay',
                                     'sortableColumn'    => 'desiredPay',
                                     'pagerWidth'   => 125,
                                     'filter'         => 'candidate.desired_pay',
                                     'filterTypes'   => '===>=<'),

            'Can Relocate'  => array('select'  => 'candidate.can_relocate AS canRelocate',
                                     'pagerRender'     => 'return ($rsData[\'canRelocate\'] == 0 ? \'No\' : \'Yes\');',
                                     'exportRender'     => 'return ($rsData[\'canRelocate\'] == 0 ? \'No\' : \'Yes\');',
                                     'sortableColumn'    => 'canRelocate',
                                     'pagerWidth'   => 80,
                                     'filter'         => 'candidate.can_relocate'),

            'Owner' =>         array('select'   => 'owner_user.first_name AS ownerFirstName,' .
                                                   'owner_user.last_name AS ownerLastName,' .
                                                   'CONCAT(owner_user.last_name, owner_user.first_name) AS ownerSort',
                                     'join'     => 'LEFT JOIN user AS owner_user ON candidate.owner = owner_user.user_id',
                                     'pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'ownerFirstName\'], $rsData[\'ownerLastName\'], false, LAST_NAME_MAXLEN);',
                                     'exportRender'     => 'return $rsData[\'ownerFirstName\'] . " " .$rsData[\'ownerLastName\'];',
                                     'sortableColumn'     => 'ownerSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(owner_user.first_name, owner_user.last_name)'),

            'Created' =>       array('select'   => 'DATE_FORMAT(candidate.date_created, \'%m-%d-%y\') AS dateCreated',
                                     'pagerRender'      => 'return $rsData[\'dateCreated\'];',
                                     'sortableColumn'     => 'dateCreatedSort',
                                     'pagerWidth'    => 60,
                                     'filterHaving' => 'DATE_FORMAT(candidate.date_created, \'%m-%d-%y\')'),

            'Modified' =>      array('select'   => 'DATE_FORMAT(candidate.date_modified, \'%m-%d-%y\') AS dateModified',
                                     'pagerRender'      => 'return $rsData[\'dateModified\'];',
                                     'sortableColumn'     => 'dateModifiedSort',
                                     'pagerWidth'    => 60,
                                     'pagerOptional' => false,
                                     'filterHaving' => 'DATE_FORMAT(candidate.date_modified, \'%m-%d-%y\')'),

            /* This one only works when called from the saved list view.  Thats why it is not optional, filterable, or exportable.
             * FIXME:  Somehow make this defined in the associated savedListDataGrid class child.
             */
            'Added To List' =>  array('select'   => 'DATE_FORMAT(saved_list_entry.date_created, \'%m-%d-%y\') AS dateAddedToList,
                                                     saved_list_entry.date_created AS dateAddedToListSort',
                                     'pagerRender'      => 'return $rsData[\'dateAddedToList\'];',
                                     'sortableColumn'     => 'dateAddedToListSort',
                                     'pagerWidth'    => 60,
                                     'pagerOptional' => false,
                                     'filterable' => false,
                                     'exportable' => false),

            'OwnerID' =>       array('select'    => '',
                                     'filter'    => 'candidate.owner',
                                     'pagerOptional' => false,
                                     'filterable' => false,
                                     'filterDescription' => 'Only My Candidates'),

            'IsHot' =>         array('select'    => '',
                                     'filter'    => 'candidate.is_hot',
                                     'pagerOptional' => false,
                                     'filterable' => false,
                                     'filterDescription' => 'Only Hot Candidates'),
        // Tags filtering
        	'Tags'	=>			array(
                                     'select'	=> '(
                                                    SELECT TRIM(GROUP_CONCAT(\' \',t2.title))	FROM candidate_tag t1
                                                    LEFT JOIN tag t2 ON t1.tag_id = t2.tag_id
                                                    WHERE t1.candidate_id = candidate.candidate_id
                                                    GROUP BY candidate_id
                                                    ) as tags
                                                    ',
                                     'sortableColumn' => 'tags',
                                     'pagerRender'    => 'return $rsData[\'tags\'];',
                                     'pagerOptional' => false,
                                     'pagerWidth'     => 310,
                                     'exportable'     => false,
                                     'filterable'     => false,

                                     'filterTypes'    => '=#',
                                     'filterRender=#' => '
                                      return "candidate.candidate_id IN (
                                         SELECT t1.candidate_id tags FROM candidate t1
                                         LEFT JOIN candidate_tag t2 ON t1.candidate_id = t2.candidate_id
                                         WHERE t2.site_id = 1 AND t2.tag_id IN (". implode(",",$arguments)."))";
                                     ')
        );
        
        if (US_ZIPS_ENABLED)
        {
            $this->_classColumns['Near Zipcode'] =
                               array('select'  => 'candidate.zip AS zip',
                                     'filter' => 'candidate.zip',
                                     'pagerOptional' => false,
                                     'filterTypes'   => '=@');
        }

        /* Extra fields get added as columns here. */
        $candidates = new Candidates($this->_siteID);
        $extraFieldsRS = $candidates->extraFields->getSettings();
        foreach ($extraFieldsRS as $index => $data)
        {
            $fieldName = $data['fieldName'];

            if (!isset($this->_classColumns[$fieldName]))
            {
                $columnDefinition = $candidates->extraFields->getDataGridDefinition($index, $data, $this->_db);

                /* Return false for extra fields that should not be columns. */
                if ($columnDefinition !== false)
                {
                    $this->_classColumns[$fieldName] = $columnDefinition;
                }
            }
        }

        parent::__construct($instanceName, $parameters, $misc);
    }

    /**
     * Returns the sql statment for the pager.
     *
     * @return array Candidates data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        // FIXME: Factor out Session dependency.
        if ($_SESSION['CATS']->isLoggedIn() && $_SESSION['CATS']->getAccessLevel('candidates') < ACCESS_LEVEL_MULTI_SA)
        {
            $adminHiddenCriterion = 'AND candidate.is_admin_hidden = 0';
        }
        else
        {
            $adminHiddenCriterion = '';
        }

        if ($this->getMiscArgument() != 0)
        {
            $savedListID = (int) $this->getMiscArgument();
            $joinSQL  .= ' INNER JOIN saved_list_entry
                                    ON saved_list_entry.data_item_type = '.DATA_ITEM_CANDIDATE.'
                                    AND saved_list_entry.data_item_id = candidate.candidate_id
                                    AND saved_list_entry.site_id = '.$this->_siteID.'
                                    AND saved_list_entry.saved_list_id = '.$savedListID;
        }
        else
        {
            $joinSQL  .= ' LEFT JOIN saved_list_entry
                                    ON saved_list_entry.data_item_type = '.DATA_ITEM_CANDIDATE.'
                                    AND saved_list_entry.data_item_id = candidate.candidate_id
                                    AND saved_list_entry.site_id = '.$this->_siteID;         
        }

        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                candidate.candidate_id AS candidateID,
                candidate.candidate_id AS exportID,
                candidate.is_hot AS isHot,
                candidate.date_modified AS dateModifiedSort,
                candidate.date_created AS dateCreatedSort,
            %s
            FROM
                candidate
            %s
            WHERE
                candidate.site_id = %s
            %s
            %s
            %s
            GROUP BY candidate.candidate_id
            %s
            %s
            %s",
            $distinct,
            $selectSQL,
            $joinSQL,
            $this->_siteID,
            $adminHiddenCriterion,
            (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
            $this->_assignedCriterion,
            (strlen($havingSQL) > 0) ? ' HAVING ' . $havingSQL : '',
            $orderSQL,
            $limitSQL
        );

        return $sql;
    }
}

/**
 *  EEO Settings Library
 *  @package    CATS
 *  @subpackage Library
 */
class EEOSettings
{
    private $_db;
    private $_siteID;
    private $_userID;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        // FIXME: Factor out Session dependency.
        $this->_userID = $_SESSION['CATS']->getUserID();
        $this->_db = DatabaseConnection::getInstance();
    }


    /**
     * Returns all EEO settings for a site.
     *
     * @return array (setting => value)
     */
    public function getAll()
    {
        /* Default values. */
        $settings = array(
            'enabled' => '0',
            'genderTracking' => '0',
            'ethnicTracking' => '0',
            'veteranTracking' => '0',
            'veteranTracking' => '0',
            'disabilityTracking' => '0',
            'canSeeEEOInfo' => false
        );

        $sql = sprintf(
            "SELECT
                settings.setting AS setting,
                settings.value AS value,
                settings.site_id AS siteID
            FROM
                settings
            WHERE
                settings.site_id = %s
            AND
                settings.settings_type = %s",
            $this->_siteID,
            SETTINGS_EEO
        );
        $rs = $this->_db->getAllAssoc($sql);

        /* Override default settings with settings from the database. */
        foreach ($rs as $rowIndex => $row)
        {
            foreach ($settings as $setting => $value)
            {
                if ($row['setting'] == $setting)
                {
                    $settings[$setting] = $row['value'];
                }
            }
        }

        $settings['canSeeEEOInfo'] = $_SESSION['CATS']->canSeeEEOInfo();

        return $settings;
    }

    /**
     * Sets an EEO setting for a site.
     *
     * @param string Setting name
     * @param string Setting value
     * @return void
     */
    public function set($setting, $value)
    {
        $sql = sprintf(
            "DELETE FROM
                settings
            WHERE
                settings.setting = %s
            AND
                site_id = %s
            AND
                settings_type = %s",
            $this->_db->makeQueryStringOrNULL($setting),
            $this->_siteID,
            SETTINGS_EEO
        );
        $this->_db->query($sql);

        $sql = sprintf(
            "INSERT INTO settings (
                setting,
                value,
                site_id,
                settings_type
            )
            VALUES (
                %s,
                %s,
                %s,
                %s
            )",
            $this->_db->makeQueryStringOrNULL($setting),
            $this->_db->makeQueryStringOrNULL($value),
            $this->_siteID,
            SETTINGS_EEO
         );
         $this->_db->query($sql);
    }
}

?>
