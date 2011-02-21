<?php
/**
 * CATS
 * Career Portal Questionnaire Library
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
 * @version    $Id: Questionnaire.php 3745 2007-11-28 18:37:56Z andrew $
 */

include_once('./lib/DatabaseConnection.php');
include_once('./lib/Candidates.php');

define('QUESTIONNAIRE_QUESTION_TYPE_TEXT', 1);
define('QUESTIONNAIRE_QUESTION_TYPE_SELECT', 2);
define('QUESTIONNAIRE_QUESTION_TYPE_CHECKBOX', 3);
define('QUESTIONNAIRE_QUESTION_TYPE_RADIO', 4);

/**
 *  Questionnaire Library
 *  @package    CATS
 *  @subpackage Library
 */
class Questionnaire
{
    private $_siteID;
    private $_db;

    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }

    /**
     * Get all questionnaires for the current site.
     *
     * @return array
     */
    public function getAll($includeInactive = false)
    {
        $activeCritereon = $includeInactive ? '' : 'AND is_active = 1';
        $sql = sprintf(
            "SELECT
                career_portal_questionnaire_id as questionnaireID,
                title,
                description,
                is_active as isActive
             FROM
                career_portal_questionnaire
             WHERE
                career_portal_questionnaire.site_id = %d
             %s",
            $this->_siteID,
            $activeCritereon
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Get information about a specific questionnaire.
     *
     * @param integer career_portal_questionnaire_id of the requested questionnaire
     * @return array
     */
    public function get($id)
    {
        $sql = sprintf(
            "SELECT
                career_portal_questionnaire_id as questionnaireID,
                title,
                description,
                is_active as isActive
             FROM
                career_portal_questionnaire
             WHERE
                career_portal_questionnaire.site_id = %s
             AND
                career_portal_questionnaire.career_portal_questionnaire_id = %s",
            $this->_siteID,
            $this->_db->makeQueryInteger($id)
        );

        return $this->_db->getAssoc($sql);
    }

    public function add($title, $description, $isActive)
    {
        $sql = sprintf(
            "INSERT INTO
                career_portal_questionnaire (
                    title,
                    description,
                    is_active,
                    site_id
                )
             VALUES( %s, %s, %s, %d )",
            $this->_db->makeQueryString($title),
            $this->_db->makeQueryString($description),
            $this->_db->makeQueryInteger($isActive),
            $this->_siteID
        );

        if ($this->_db->query($sql))
        {
            return $this->_db->getLastInsertID();
        }
        else
        {
            return false;
        }
    }

    public function delete($id)
    {
        $sql = sprintf(
            "DELETE FROM
                career_portal_questionnaire
             WHERE
                career_portal_questionnaire_id = %s
             AND
                site_id = %d",
            $this->_db->makeQueryInteger($id),
            $this->_siteID
        );

        return $this->_db->query($sql);
    }

    /**
     * Get all attached questions with applicable answers from a questionnaire
     * sorted by their position value.
     */
    public function getQuestions($id)
    {
        $sql = sprintf(
            "SELECT
                a.career_portal_questionnaire_question_id as questionID,
                a.text as questionText,
                a.minimum_length as minimumLength,
                a.maximum_length as maximumLength,
                a.position as questionPosition,
                a.type as questionType,
                b.career_portal_questionnaire_answer_id as answerID,
                b.text as answerText,
                b.action_source as actionSource,
                b.action_notes as actionNotes,
                b.action_is_hot as actionIsHot,
                b.action_is_active as actionIsActive,
                b.action_can_relocate as actionCanRelocate,
                b.action_key_skills as actionKeySkills,
                b.position as answerPosition
            FROM
                career_portal_questionnaire_question a
            RIGHT JOIN
                career_portal_questionnaire_answer b
            ON
                a.career_portal_questionnaire_question_id = b.career_portal_questionnaire_question_id
            WHERE
                a.site_id = %s
            AND
                a.career_portal_questionnaire_id = %s
            ORDER BY
                a.position, b.position ASC",
            $this->_siteID,
            $this->_db->makeQueryInteger($id)
        );

        $rs = $this->_db->getAllAssoc($sql);

        // Put the data into a well-formatted php array
        for (
            $rowIndex = 0, $questions = array(), $questionIndex = $questionID = -1;
            $rowIndex < count($rs);
            $rowIndex++
        )
        {
            if ($questionID != ($newID = $rs[$rowIndex]['questionID']))
            {
                $questionID = $newID;
                $questions[++$questionIndex] = array(
                    'questionID' => $newID,
                    'questionType' => $rs[$rowIndex]['questionType'],
                    'questionText' => $rs[$rowIndex]['questionText'],
                    'minimumLength' => $rs[$rowIndex]['minimumLength'],
                    'maximumLength' => $rs[$rowIndex]['maximumLength'],
                    'questionPosition' => $rs[$rowIndex]['questionPosition'],
                    'answers' => array()
                );
            }
            if ($questions[$questionIndex]['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_TEXT)
            {
                continue;
            }

            $questions[$questionIndex]['answers'][] = array(
                'answerID' => $rs[$rowIndex]['answerID'],
                'answerText' => $rs[$rowIndex]['answerText'],
                'actionSource' => $rs[$rowIndex]['actionSource'],
                'actionNotes' => $rs[$rowIndex]['actionNotes'],
                'actionIsHot' => $rs[$rowIndex]['actionIsHot'],
                'actionIsActive' => $rs[$rowIndex]['actionIsActive'],
                'actionCanRelocate' => $rs[$rowIndex]['actionCanRelocate'],
                'actionKeySkills' => $rs[$rowIndex]['actionKeySkills'],
                'answerPosition' => $rs[$rowIndex]['answerPosition']
            );
        }

        return $questions;
    }

    public function deleteQuestions($id)
    {
        $sql = sprintf(
            "DELETE FROM
                career_portal_questionnaire_question
             WHERE
                career_portal_questionnaire_id = %s
             AND
                site_id = %d",
            $this->_db->makeQueryInteger($id),
            $this->_siteID
        );

        $sql = sprintf(
            "DELETE FROM
                career_portal_questionnaire_answer
             WHERE
                career_portal_questionnaire_id = %s
             AND
                site_id = %d",
            $this->_db->makeQueryInteger($id),
            $this->_siteID
        );

        return $this->_db->query($sql);
    }

    public function update($id, $title, $description, $isActive)
    {
        $sql = sprintf(
            "UPDATE
                career_portal_questionnaire
             SET
                title = %s,
                description = %s,
                is_active = %s
             WHERE
                career_portal_questionnaire_id = %s
             AND
                site_id = %d",
            $this->_db->makeQueryString($title),
            $this->_db->makeQueryString($description),
            $this->_db->makeQueryInteger($isActive),
            $this->_db->makeQueryInteger($id),
            $this->_siteID
        );

        return $this->_db->query($sql);
    }

    /**
     * Take a well-formatted PHP array (like the one generated from getQuestions()
     * and uses it to insert new records. Existing records are not replaced, one
     * should call deleteQuestions() prior to calling this function unless the
     * array contains new data to prevent duplication.
     *
     * @param integer ID of the questionnaire
     * @param boolean true on success, false if there were errors
     */
    public function addQuestions($questionnaireID, $questions)
    {
        foreach ($questions as $question)
        {
            $questionID = $this->addQuestion(
                $questionnaireID,
                $question['questionText'],
                isset($question[$id='minimumLength']) ? $question[$id] : 0,
                isset($question[$id='maximumLength']) ? $question[$id] : 255,
                true,
                $question['questionPosition'],
                $question['questionType']
            );

            if ($questionID !== false)
            {
                if ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_TEXT)
                {
                    $this->addAnswer(
                        $questionnaireID,
                        $questionID,
                        '',
                        '',
                        '',
                        0,
                        1,
                        0,
                        '',
                        1
                    );
                }
                else
                {
                    foreach ($question['answers'] as $answer)
                    {
                        $this->addAnswer(
                            $questionnaireID,
                            $questionID,
                            $answer['answerText'],
                            isset($answer[$id='actionSource']) ? $answer[$id] : '',
                            isset($answer[$id='actionNotes']) ? $answer[$id] : '',
                            isset($answer[$id='actionIsHot']) ? $answer[$id] : 0,
                            isset($answer[$id='actionIsActive']) ? $answer[$id] : 1,
                            isset($answer[$id='actionCanRelocate']) ? $answer[$id] : 0,
                            isset($answer[$id='actionKeySkills']) ? $answer[$id] : '',
                            $answer['answerPosition']
                        );
                    }
                }
            }
        }

        return true;
    }

    public function addQuestion($id, $text, $minimumLength, $maximumLength, $required,
        $position, $type)
    {
        $sql = sprintf(
            "INSERT INTO
                career_portal_questionnaire_question (
                    career_portal_questionnaire_id,
                    text,
                    minimum_length,
                    maximum_length,
                    required,
                    position,
                    site_id,
                    type
                )
             VALUES (
                %s, %s, %s, %s, %s, %s, %s, %s
             )",
            $this->_db->makeQueryInteger($id),
            $this->_db->makeQueryString($text),
            $this->_db->makeQueryString($minimumLength),
            $this->_db->makeQueryString($maximumLength),
            $this->_db->makeQueryString($required),
            $this->_db->makeQueryInteger($position),
            $this->_siteID,
            $this->_db->makeQueryInteger($type)
        );

        if ($this->_db->query($sql))
        {
            return $this->_db->getLastInsertID();
        }
        else
        {
            return false;
        }
    }

    /**
     * Adds an answer to the questionnaire attached to a question.
     *
     * @param integer $questionnaireID
     * @param integer $questionID
     * @param string $text
     * @param string $actionSource
     * @param string $actionNotes
     * @param integer $actionIsHot
     * @param integer $actionIsActive
     * @param integer $actionCanRelocate
     * @param string $actionKeySkills
     * @param integer $position
     * @param integer $siteID
     * @return boolean
     */
    public function addAnswer(
        $questionnaireID,    // ID for the questionnaire
        $questionID,         // ID for the parent question
        $text,               // Textual description of the answer
        $actionSource,       // Append to candidate source
        $actionNotes,        // Appent to candidate notes
        $actionIsHot,        // Set candidate is_hot
        $actionIsActive,     // Set candidate is_active
        $actionCanRelocate,  // Set candidate can_relocate
        $actionKeySkills,    // Append to candidate skills
        $position           // Position in the question (1,2,3,4, etc.)
    )
    {
        $sql = sprintf(
            "INSERT INTO
                career_portal_questionnaire_answer (
                    career_portal_questionnaire_id,
                    career_portal_questionnaire_question_id,
                    text,
                    action_source,
                    action_notes,
                    action_is_hot,
                    action_is_active,
                    action_can_relocate,
                    action_key_skills,
                    position,
                    site_id
                )
             VALUES (
                %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s
             )",
            $this->_db->makeQueryInteger($questionnaireID),
            $this->_db->makeQueryInteger($questionID),
            $this->_db->makeQueryString($text),
            $this->_db->makeQueryString($actionSource),
            $this->_db->makeQueryString($actionNotes),
            $this->_db->makeQueryInteger($actionIsHot),
            $this->_db->makeQueryInteger($actionIsActive),
            $this->_db->makeQueryInteger($actionCanRelocate),
            $this->_db->makeQueryString($actionKeySkills),
            $this->_db->makeQueryInteger($position),
            $this->_siteID
        );

        if ($this->_db->query($sql))
        {
            return $this->_db->getLastInsertID();
        }
        else
        {
            return false;
        }
    }

    public function convertQuestionTypeToConstant($type)
    {
        switch ($type)
        {
            case 'checkbox': return QUESTIONNAIRE_QUESTION_TYPE_CHECKBOX;
            case 'select': return QUESTIONNAIRE_QUESTION_TYPE_SELECT;
            case 'radio': return QUESTIONNAIRE_QUESTION_TYPE_RADIO;
            default: return QUESTIONNAIRE_QUESTION_TYPE_TEXT;
        }
    }

    public function convertQuestionConstantToType($type)
    {
        switch ($type)
        {
            case QUESTIONNAIRE_QUESTION_TYPE_CHECKBOX: return 'Checkboxes';
            case QUESTIONNAIRE_QUESTION_TYPE_SELECT: return 'Drop-down List';
            case QUESTIONNAIRE_QUESTION_TYPE_RADIO: return 'Radio Buttons';
            default: return 'Text';
        }
    }

    public function getCandidateQuestionnaires($candidateID)
    {
        $sql = sprintf(
            "SELECT
                questionnaire_title as questionnaireTitle,
                questionnaire_description as questionnaireDescription,
                date as questionnaireDate,
                site_id as siteID,
                candidate_id as candidateID,
                career_portal_questionnaire_history_id as historyID
             FROM
                career_portal_questionnaire_history
             WHERE
                site_id = %d
             AND
                candidate_id = %s",
            $this->_siteID,
            $this->_db->makeQueryInteger($candidateID)
        );

        $rs = $this->_db->getAllAssoc($sql);
        $lastTitle = '';
        $results = array();
        foreach ($rs as $row)
        {
            if (strcmp($lastTitle, $row[$id='questionnaireTitle']))
            {
                $results[] = $row;
                $lastTitle = $row[$id];
            }
        }

        return $results;
    }

    /**
     * Get all questions and answers to a given questionnaire.
     *
     * @param integer $candidateID
     * @param integer $questionnaireID
     * @return array
     */
    public function getCandidateQuestionnaire($candidateID, $questionnaireTitle)
    {
        $sql = sprintf(
            "SELECT
                career_portal_questionnaire_history_id as historyID,
                candidate_id as candidateID,
                site_id as siteID,
                questionnaire_title as questionnaireTitle,
                questionnaire_description as questionnaireDescription,
                date as questionnaireDate,
                question as questionText,
                answer as answerText
             FROM
                career_portal_questionnaire_history
             WHERE
                questionnaire_title = %s
             AND
                candidate_id = %s
             AND
                site_id = %d",
            $this->_db->makeQueryString($questionnaireTitle),
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    public function log($candidateID, $title, $description, $question, $answer)
    {
        $sql = sprintf(
            "INSERT INTO
                career_portal_questionnaire_history (
                    site_id,
                    candidate_id,
                    questionnaire_title,
                    questionnaire_description,
                    question,
                    answer,
                    date
                )
             VALUES ( %d, %s, %s, %s, %s, %s, NOW() )",
            $this->_siteID,
            $this->_db->makeQueryInteger($candidateID),
            $this->_db->makeQueryString($title),
            $this->_db->makeQueryString($description),
            $this->_db->makeQueryString($question),
            $this->_db->makeQueryString($answer)
        );
        $this->_db->query($sql);
    }

    /**
     * It is assumed the applicant has completed a questionnaire using the Career
     * Portal and has been added to CATS as a candidate. Based on their responses
     * to the questionnaire, perform any actions provided by the questionnaire
     * based on their responses (which should be in post provided by postData);
     *
     * @param ID of the attached questionnaire ID
     * @param integer candidate_id from candidate table
     * @param array $_POST equivilent data
     */
    public function doActions($questionnaireID, $candidateID, $postData)
    {
        // Get the candidate (if exists)
        $candidate = new Candidates($this->_siteID);
        if (!count($cData = $candidate->get($candidateID))) return false;

        // Default values (which may be changed by actions)
        $source = $notes = $keySkills = '';
        $isHot = $canRelocate = 0;
        $isActive = 1;

        $qData = $this->get($questionnaireID);
        if (is_array($qData) && !empty($qData))
        {
            if (!count($questions = $this->getQuestions($qData['questionnaireID']))) return false;

            foreach ($questions as $question)
            {
                $answerText = '';

                switch ($question['questionType'])
                {
                    case QUESTIONNAIRE_QUESTION_TYPE_CHECKBOX:
                        // Multiple answers possible
                        $answerIDs = array();
                        foreach ($question['answers'] as $answer)
                        {
                            $index = sprintf('questionnaire%dQuestion%dAnswer%d',
                                $qData['questionnaireID'],
                                $question['questionID'],
                                $answer['answerID']
                            );
                            if (isset($postData[$index]))
                            {
                                $answerIDs[] = $answer['answerID'];
                            }
                        }
                        break;
                    case QUESTIONNAIRE_QUESTION_TYPE_RADIO:
                    case QUESTIONNAIRE_QUESTION_TYPE_SELECT:
                        // One answer
                        $index = sprintf('questionnaire%dQuestion%d',
                            $qData['questionnaireID'],
                            $question['questionID']
                        );
                        $answerIDs = array(isset($postData[$index]) ? intval($postData[$index]) : false);
                        break;
                    case QUESTIONNAIRE_QUESTION_TYPE_TEXT:
                    default:
                        // text answer
                        $index = sprintf('questionnaire%dQuestion%d',
                            $qData['questionnaireID'],
                            $question['questionID']
                        );
                        $answerText = substr(trim(isset($postData[$index]) ? $postData[$index] : ''), 0, 255);
                        $answerIDs = array();
                        break;
                }

                foreach ($answerIDs as $answerID)
                {
                    foreach ($question['answers'] as $answer)
                    {
                        if ($answer['answerID'] == $answerID)
                        {
                            if ($answerText != '') $answerText .= ', ';
                            $answerText .= $answer['answerText'];

                            // Perform any actions (if there are any)
                            if (strlen($answer['actionSource']))
                            {
                                if (strlen($source)) $source .= ', ';
                                $source .= $answer['actionSource'];
                            }
                            if (strlen($answer['actionNotes']))
                            {
                                if (strlen($notes)) $notes .= ', ';
                                $notes .= $answer['actionNotes'];
                            }
                            if (strlen($answer['actionKeySkills']))
                            {
                                if (strlen($keySkills)) $keySkills .= ', ';
                                $keySkills .= $answer['actionKeySkills'];
                            }
                            if ($answer['actionIsHot']) $isHot = 1;
                            if (!$answer['actionIsActive']) $isActive = 0;
                            if ($answer['actionCanRelocate']) $canRelocate = 1;
                        }
                    }
                }

                // Log textual response (not multiple choice)
                // Save this candidates response
                $this->log($candidateID, $qData['title'], $qData['description'],
                    $question['questionText'], $answerText
                );
            }
        }

        return $candidate->update(
            $cData['candidateID'],
            $isActive ? true : false,
            $cData['firstName'],
            $cData['middleName'],
            $cData['lastName'],
            $cData['email1'],
            $cData['email2'],
            $cData['phoneHome'],
            $cData['phoneCell'],
            $cData['phoneWork'],
            $cData['address'],
            $cData['city'],
            $cData['state'],
            $cData['zip'],
            $source,
            $keySkills,
            $cData['dateAvailable'],
            $cData['currentEmployer'],
            $canRelocate ? true : false,
            $cData['currentPay'],
            $cData['desiredPay'],
            $notes,
            $cData['webSite'],
            $cData['bestTimeToCall'],
            $cData['owner'],
            $isHot ? true : false,
            $cData['email1'],
            $cData['email1']
        );
    }
}
























