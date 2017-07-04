<?php
/**
 * CATS
 * E-Mail Templates Library
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
 * @copyright Copyright (C) 2005 - 2007 .
 * @version    $Id: EmailTemplates.php 3694 2007-11-26 21:11:00Z Veaceslav Vasilache $
 */

include_once('./lib/Site.php');

/**
 *	E-Mail Templates Library
 *	@package    CATS
 *	@subpackage Library
 */
class Tags
{
    private $_db;
    private $_siteID;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }

    /**
     * Updates an tag.
     *
     * @param integer tag  ID
     * @param string tag title
     * @param string tag description
     * @return boolean True if successful; false otherwise.
     */
    public function update($tagID, $title, $description)
    {
        $sql = sprintf(
            "UPDATE
                tag
            SET
                title = %s,
                description = %s
            WHERE
                tag_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryStringOrNULL($title),
            $this->_db->makeQueryStringOrNULL($description),
            $tagID,
            $this->_siteID
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return false;
        }

        return true;
    }


    public function delete($tagID)
    {
        $sql = sprintf(
            "DELETE FROM
                tag
            WHERE
                (tag_id = %s OR tag_parent_id = %s)
            AND
                site_id = %s",
            $tagID, $tagID,
            $this->_siteID
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return false;
        }

        return true;
    }


    public function add($parent_tag_id=null, $title, $description)
    {
        $sql = sprintf(
            "INSERT INTO
                tag
            (
                tag_parent_id,
                title ,
                description,
                site_id
            ) VALUES (
                %s,
                %s,
                %s,
                %s
            )",
            $this->_db->makeQueryStringOrNULL($parent_tag_id),
            $this->_db->makeQueryStringOrNULL($title),
            $this->_db->makeQueryStringOrNULL($description),
            $this->_siteID
        );

        $queryResult = $this->_db->query($sql);
        
        if (!$queryResult)
        {
            return false;
        }

        return array('id'=> $queryResult = $this->_db->getLastInsertID(), 'tag_title' => $title);
    }

    /**
     * Returns all relevent template data for all templates.
     *
     * @return array e-mail template data
     */
    public function getAll()
    {
        $sql = sprintf(
			"SELECT
				t1.tag_id,
				t1.tag_parent_id,
				t2.title AS tag_parent_title,
				t1.title AS tag_title
			FROM
				tag t1
			LEFT JOIN
				tag t2 ON t2.tag_id = t1.tag_parent_id
			WHERE t1.site_id = %d  
			ORDER BY IFNULL(t1.tag_parent_id, t1.tag_id), t1.tag_id",
			$this->_siteID
		);

		return $this->_db->getAllAssoc($sql);
	}
	
    /**
     * Returns all tags related to a candidate id
     *
     * @param $candidateID ID of the candidate for witch you want to return list of tags assigned
     * @return array e-mail template data
     */
    public function getCandidateTags($candidateID)
    {
    	if (FALSE === is_numeric($candidateID) ) return array();
        $sql = sprintf(
			"SELECT
				t1.tag_id,
				t1.tag_parent_id,
				t2.title AS tag_parent_title,
				t1.title AS tag_title 
			FROM
				tag t1
			LEFT JOIN
				tag t2 ON t2.tag_id = t1.tag_parent_id
			WHERE t1.site_id = %d AND 
				  t1.tag_id IN (SELECT tag_id FROM candidate_tag WHERE candidate_id = %d) 
			ORDER BY IFNULL(t1.tag_parent_id, t1.tag_id), t1.tag_id",
			$this->_siteID, $candidateID
		);
		return $this->_db->getAllAssoc($sql);
	}
	
    public function getCandidateTagsID($candidateID)
    {
    	$result = array();
    	$tags = $this->getCandidateTags($candidateID);
    	foreach($tags as $t){
    		$result[] = $t['tag_id'];
    	}
		return $result;
	}
	
    public function getCandidateTagsTitle($candidateID)
    {
    	$result = array();
    	$tags = $this->getCandidateTags($candidateID);
    	foreach($tags as $t){
    		$result[] = $t['tag_title'];
    	}
		return $result;
	}
	
	/**
	 * This function assignes new tags to a candidate
	 * @param $candidateID	INT	Candidate ID
	 * @param $tagIDs		INT or array of INTs
	 * @return Boolean 		TRUE on success and FALSE on failure
	 */
	public function AddTagsToCandidate($candidateID, $tagIDs){

		if (is_array($tagIDs)){
			foreach($tagIDs as $t){
				$values[] = sprintf(" (
	                %s,
	                %s,
	                %s
	            )",
	            $this->_db->makeQueryStringOrNULL($candidateID),
	            $this->_db->makeQueryStringOrNULL($t),
	            $this->_siteID);
			}

		}
		else
		{
			$values = sprintf(" (
                %s,
                %s,
                %s
            )",
            $this->_db->makeQueryStringOrNULL($candidateID),
            $this->_db->makeQueryStringOrNULL($tagIDs),
            $this->_siteID);
			
		}

		// Clear all tags from this candidate
        $sql = sprintf(
            "DELETE FROM 
                candidate_tag 
            WHERE candidate_id = %s AND site_id = %s ",
        $this->_db->makeQueryStringOrNULL($candidateID),
        $this->_siteID);
        
		if ($this->_db->query($sql)){
			// Add current selected tag ids to the candidate
	        $sql = 
	            "INSERT INTO
	                candidate_tag
	            (
	                candidate_id,
	                tag_id,
	                site_id
	            ) VALUES " . (is_array($values) ? implode(", ", $values) : $values );
	        
	        $queryResult = $this->_db->query($sql);
		
		}		
		
        if (!$queryResult)
        {
            return false;
        }
	}
}

?>
