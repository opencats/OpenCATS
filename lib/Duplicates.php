<?php
/**
* Duplicates Module
* @package OpenCATS
* @subpackage Library
* @copyright (C) OpenCats
* @license GNU/GPL, see license.txt
* OpenCATS is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License 2
* as published by the Free Software Foundation.
*/

include_once('./lib/Attachments.php');
include_once('./lib/Pipelines.php');
include_once('./lib/History.php');
include_once('./lib/SavedLists.php');
include_once('./lib/ExtraFields.php');
include_once('lib/DataGrid.php');


/**
 *  Candidates Library
 *  @package    CATS
 *  @subpackage Library
 */
class Duplicates
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
     * Removes a candidate and all associated records from the system.
     *
     * @param integer Candidate ID to delete.
     * @return void
     */
    public function delete($firstCandidateID, $secondCandidateID)
    {
        /* Delete the duplicate from candidate_duplicates. */
        $sql = sprintf(
            "DELETE FROM
                candidate_duplicates
            WHERE
                old_candidate_id = %s
            AND
                new_candidate_id = %s
            OR
                new_candidate_id = %s
            AND
                old_candidate_id = %s"
            ,
            $this->_db->makeQueryInteger($firstCandidateID),
            $this->_db->makeQueryInteger($secondCandidateID),
            $this->_db->makeQueryInteger($firstCandidateID),
            $this->_db->makeQueryInteger($secondCandidateID)
        );
        $this->_db->query($sql);
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
        $data = $this->_db->getAssoc($sql);
        
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
     * Returns the number of duplicates in the system.  Useful
     * for determining if the friendly "no candidates in system"
     * should be displayed rather than the datagrid.
     *
     * @return integer Number of Duplicates in site.
     */
    public function getCount()
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
     * @param string first candidate ID.
     * @param string second candidate ID.
     * @return integer 1 on success, or -1 on failure.
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

        echo $this->_db->query($sql);
        
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

        echo $this->_db->query($sql);
        
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

        echo $this->_db->query($sql);
        
        $sql = sprintf(
            "DELETE FROM
                candidate_duplicates
            WHERE
                new_candidate_id = %s",
            $this->_db->makeQueryInteger($newCandidateID)
        );

        echo $this->_db->query($sql);
        
        $sql = sprintf(
            "UPDATE 
                candidate_duplicates
            SET
                old_candidate_id = %s
            WHERE
                old_candidate_id = %s",
            $this->_db->makeQueryInteger($oldCandidateID),
            $this->_db->makeQueryInteger($newCandidateID)
        );
        
        echo $this->_db->query($sql);
        
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

        echo $this->_db->query($sql);
        
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

        echo $this->_db->query($sql);
        
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

        echo $this->_db->query($sql);
        
         $sql = sprintf(
            "UPDATE
                saved_list_entry
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

        echo $this->_db->query($sql);
        
        
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
            if($params['emails'][0] != "")
            {
                $update .= "email1 = '" . $params['emails'][0] . "'";
                $comma = true;
            }
            if(comma)
            {
                $update .= ", ";
            }
            if($params['emails'][1] != "")
            {
                $update .= "email2 = '" . $params['emails'][1] . "'";
            }
            $comma = true;
        }
        if(comma){
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
            echo $this->_db->query($sql);
        }
        
        //TO-DO:  delete new candidate
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
}


class DuplicatesDataGrid extends DataGrid
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
                                                IF(attachment_id, 1, 0) AS attachmentPresent',

                                     'pagerRender' => 'if ($rsData[\'submitted\'] == 1)
                                                    {
                                                        $return = \'<img src="images/job_orders.gif" alt="" width="16" height="16" title="Submitted for a Job Order" />\';
                                                    }
                                                    else
                                                    {
                                                        $return = \'<img src="images/mru/blank.gif" alt="" width="16" height="16" />\';
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
                                                        AND candidate_joborder_submitted.status != '.PIPELINE_STATUS_NOTINCONSIDERATION.'
                                                    INNER JOIN candidate_duplicates 
                                                        ON candidate.candidate_id = candidate_duplicates.new_candidate_id
                                   ',
                                     'pagerWidth'    => 70,
                                     'pagerOptional' => true,
                                     'pagerNoTitle' => true,
                                     'sizable'  => false,
                                     'exportable' => false,
                                     'filterable' => false),

            'First Name' =>     array('select'         => 'candidate.first_name AS firstName',
                                      'pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=duplicates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'firstName\']).\'</a>\';',
                                      'sortableColumn' => 'firstName',
                                      'pagerWidth'     => 75,
                                      'pagerOptional'  => false,
                                      'alphaNavigation'=> true,
                                      'filter'         => 'candidate.first_name'),

            'Last Name' =>      array('select'         => 'candidate.last_name AS lastName',
                                     'sortableColumn'  => 'lastName',
                                     'pagerRender'     => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=duplicates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'lastName\']).\'</a>\';',
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
        if ($_SESSION['CATS']->isLoggedIn() && $_SESSION['CATS']->getAccessLevel() < ACCESS_LEVEL_MULTI_SA)
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

?>
