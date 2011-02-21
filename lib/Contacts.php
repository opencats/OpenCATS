<?php
/**
 * CATS
 * Contacts Library
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
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
 * @version    $Id: Contacts.php 3690 2007-11-26 18:07:17Z brian $
 */

include_once('./lib/Pager.php');
include_once('./lib/EmailTemplates.php');
include_once('./lib/ExtraFields.php');
include_once('./lib/Calendar.php');

/**
 *	Contacts Library
 *	@package    CATS
 *	@subpackage Library
 */
class Contacts
{
    private $_db;
    private $_siteID;

    public $extraFields;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        $this->extraFields = new ExtraFields($siteID, DATA_ITEM_CONTACT);
    }


    /**
     * Adds a contact to the database and returns its contact ID.
     *
     * @param integer company ID
     * @param string first name
     * @param string last name
     * @param string title
     * @param string department
     * @param string e-mail address 1
     * @param string e-mail address 2
     * @param string work phone number
     * @param string cell phone number
     * @param string other phone number
     * @param string address line
     * @param string city
     * @param string state
     * @param string zip code
     * @param boolean is hot
     * @param string contact notes
     * @param integer entered-by user
     * @param integer owner user
     * @return new contact ID, or -1 on failure.
     */
    public function add($companyID, $firstName, $lastName, $title, $department,
        $reportsTo, $email1, $email2, $phoneWork, $phoneCell, $phoneOther, $address,
        $city, $state, $zip, $isHot, $notes, $enteredBy, $owner)
    {
        /* Get the department ID of the selected department. */
        $departmentID = $this->getDepartmentIDByName(
            $department, $companyID, $this->_db
        );

        $sql = sprintf(
            "INSERT INTO contact (
                company_id,
                first_name,
                last_name,
                title,
                company_department_id,
                reports_to,
                email1,
                email2,
                phone_work,
                phone_cell,
                phone_other,
                address,
                city,
                state,
                zip,
                is_hot,
                left_company,
                notes,
                entered_by,
                owner,
                site_id,
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
                %s,
                %s,
                NOW(),
                NOW()
            )",
            $this->_db->makeQueryInteger($companyID),
            $this->_db->makeQueryString($firstName),
            $this->_db->makeQueryString($lastName),
            $this->_db->makeQueryString($title),
            $this->_db->makeQueryInteger($departmentID),
            $this->_db->makeQueryInteger($reportsTo),
            $this->_db->makeQueryString($email1),
            $this->_db->makeQueryString($email2),
            $this->_db->makeQueryString($phoneWork),
            $this->_db->makeQueryString($phoneCell),
            $this->_db->makeQueryString($phoneOther),
            $this->_db->makeQueryString($address),
            $this->_db->makeQueryString($city),
            $this->_db->makeQueryString($state),
            $this->_db->makeQueryString($zip),
            ($isHot ? '1' : '0'),
            $this->_db->makeQueryString($notes),
            $this->_db->makeQueryInteger($enteredBy),
            $this->_db->makeQueryInteger($owner),
            $this->_siteID
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        $contactID = $this->_db->getLastInsertID();

        $history = new History($this->_siteID);
        $history->storeHistoryNew(DATA_ITEM_CONTACT, $contactID);

        return $contactID;
    }

    /**
     * Updates a contact.
     *
     * @param integer contact ID
     * @param integer company ID
     * @param string first name
     * @param string last name
     * @param string title
     * @param string department
     * @param string e-mail address 1
     * @param string e-mail address 2
     * @param string work phone number
     * @param string cell phone number
     * @param string other phone number
     * @param string address line
     * @param string city
     * @param string state
     * @param string zip code
     * @param boolean is hot
     * @param boolean left company
     * @param string contact notes
     * @param integer owner user
     * @param array argument array
     * @param string e-mail notification message
     * @param string e-mail notification address
     * @return boolean True if successful; false otherwise.
     */
    public function update($contactID, $companyID, $firstName, $lastName,
        $title, $department, $reportsTo, $email1, $email2, $phoneWork, $phoneCell,
        $phoneOther, $address, $city, $state, $zip, $isHot,
        $leftCompany, $notes, $owner, $email, $emailAddress)
    {
        /* Get the department ID of the selected department. */
        $departmentID = $this->getDepartmentIDByName(
            $department, $companyID, $this->_db
        );

        $sql = sprintf(
            "UPDATE
                contact
            SET
                contact.company_id     = %s,
                contact.first_name    = %s,
                contact.last_name     = %s,
                contact.title         = %s,
                contact.company_department_id = %s,
                contact.reports_to    = %s,
                contact.email1        = %s,
                contact.email2        = %s,
                contact.phone_work    = %s,
                contact.phone_cell    = %s,
                contact.phone_other   = %s,
                contact.address       = %s,
                contact.city          = %s,
                contact.state         = %s,
                contact.zip           = %s,
                contact.is_hot        = %s,
                contact.left_company  = %s,
                contact.notes         = %s,
                contact.owner         = %s,
                contact.date_modified = NOW()
            WHERE
                contact.contact_id = %s
            AND
                contact.site_id = %s",
            $this->_db->makeQueryInteger($companyID),
            $this->_db->makeQueryString($firstName),
            $this->_db->makeQueryString($lastName),
            $this->_db->makeQueryString($title),
            $this->_db->makeQueryInteger($departmentID),
            $this->_db->makeQueryInteger($reportsTo),
            $this->_db->makeQueryString($email1),
            $this->_db->makeQueryString($email2),
            $this->_db->makeQueryString($phoneWork),
            $this->_db->makeQueryString($phoneCell),
            $this->_db->makeQueryString($phoneOther),
            $this->_db->makeQueryString($address),
            $this->_db->makeQueryString($city),
            $this->_db->makeQueryString($state),
            $this->_db->makeQueryString($zip),
            ($isHot ? '1' : '0'),
            ($leftCompany ? '1' : '0'),
            $this->_db->makeQueryString($notes),
            $this->_db->makeQueryInteger($owner),
            $this->_db->makeQueryInteger($contactID),
            $this->_siteID
        );

        $preHistory = $this->get($contactID);
        $queryResult = $this->_db->query($sql);
        $postHistory = $this->get($contactID);

        if (!$queryResult)
        {
            return false;
        }

        $history = new History($this->_siteID);
        $history->storeHistoryChanges(
            DATA_ITEM_CONTACT, $contactID, $preHistory, $postHistory
        );

        if (!empty($emailAddress))
        {
            /* Send e-mail notification. */
            //FIXME: Make subject configurable.
            $mailer = new Mailer($this->_siteID);
            $mailerStatus = $mailer->sendToOne(
                array($emailAddress, ''),
                'CATS Notification: Contact Ownership Change',
                $email,
                true
            );
        }

        return true;
    }


    /**
     * Updates all contacts for a company (called when changing company details).
     *
     * @param integer company ID
     * @param string address line
     * @param string city
     * @param string state
     * @param string zip code
     * @return boolean True if successful; false otherwise.
     */
    public function updateByCompany($companyID, $address, $city,
        $state, $zip)
    {
        $sql = sprintf(
            "UPDATE
                contact
            SET
                address      = %s,
                city          = %s,
                state         = %s,
                zip           = %s,
                date_modified = NOW()
            WHERE
                left_company != 1
            AND
                company_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryString($address),
            $this->_db->makeQueryString($city),
            $this->_db->makeQueryString($state),
            $this->_db->makeQueryString($zip),
            $this->_db->makeQueryInteger($companyID),
            $this->_siteID
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return false;
        }

        return true;
    }

    /**
     * Removes a contact and all associated records from the system.
     *
     * @param integer contact ID
     * @return void
     */
    public function delete($contactID)
    {
        $sql = sprintf(
            "DELETE FROM
                contact
            WHERE
                contact_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($contactID),
            $this->_siteID
        );
        $this->_db->query($sql);

        $sql = sprintf(
            "UPDATE
                contact
            SET
                reports_to = -1
            WHERE
                reports_to = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($contactID),
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
            $this->_db->makeQueryInteger($contactID),
            $this->_siteID,
            DATA_ITEM_CONTACT
        );
        $this->_db->query($sql);

        /* Delete extra fields. */
        $this->extraFields->deleteValueByDataItemID($contactID);

        $history = new History($this->_siteID);
        $history->storeHistoryDeleted(DATA_ITEM_CONTACT, $contactID);
    }
    
    /**
     * Returns number of total activities (for contacts datagrid).
     *
     * @return integer count
     */
    public function getCount()
    {
        $sql = sprintf(
            "SELECT
                COUNT(*) AS totalContacts
            FROM
                contact
            WHERE
                contact.site_id = %s",
            $this->_siteID
        );

        return $this->_db->getColumn($sql, 0, 0);
    }

    /**
     * Returns all relevent contact information for a given contact ID.
     *
     * @param integer contact ID
     * @return array contact dats
     */
    public function get($contactID)
    {
        $sql = sprintf(
            "SELECT
                contact.contact_id AS contactID,
                contact.company_id AS companyID,
                contact.owner AS owner,
                contact.last_name AS lastName,
                contact.first_name AS firstName,
                contact.title AS title,
                contact.email1 AS email1,
                contact.email2 AS email2,
                contact.phone_work AS phoneWork,
                contact.phone_cell AS phoneCell,
                contact.phone_other AS phoneOther,
                contact.address AS address,
                contact.city AS city,
                contact.state AS state,
                contact.zip AS zip,
                contact.notes AS notes,
                contact.is_hot AS isHotContact,
                contact.left_company AS leftCompany,
                contact.reports_to AS reportsTo,
                reportsToContact.first_name as reportsToFirstName,
                reportsToContact.last_name as reportsToLastName,
                reportsToContact.title as reportsToTitle,
                company_department.name AS department,
                DATE_FORMAT(
                    contact.date_created, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateCreated,
                DATE_FORMAT(
                    contact.date_modified, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateModified,
                company.name AS companyName,
                company.is_hot AS isHotCompany,
                CONCAT(
                    entered_by_user.first_name, ' ', entered_by_user.last_name
                ) AS enteredByFullName,
                CONCAT(
                    owner_user.first_name, ' ', owner_user.last_name
                ) AS ownerFullName,
                owner_user.email AS owner_email
            FROM
                contact
            LEFT JOIN company
                ON contact.company_id = company.company_id
            LEFT JOIN user AS entered_by_user
                ON contact.entered_by = entered_by_user.user_id
            LEFT JOIN user AS owner_user
                ON contact.owner = owner_user.user_id
            LEFT JOIN company_department
                ON contact.company_department_id = company_department.company_department_id
            LEFT JOIN contact as reportsToContact
                ON contact.reports_to  = reportsToContact.contact_id
            WHERE
                contact.contact_id = %s
            AND
                contact.site_id = %s
            AND
                company.site_id = %s",
            $this->_db->makeQueryInteger($contactID),
            $this->_siteID,
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Returns all contact information relevent to the Edit Contact page for
     * a given contact ID.
     *
     * @param integer contact ID
     * @return array contact dats
     */
    public function getForEditing($contactID)
    {
        $sql = sprintf(
            "SELECT
                contact.contact_id AS contactID,
                contact.company_id AS companyID,
                contact.owner AS owner,
                contact.last_name AS lastName,
                contact.first_name AS firstName,
                contact.title AS title,
                contact.email1 AS email1,
                contact.email2 AS email2,
                contact.phone_work AS phoneWork,
                contact.phone_cell AS phoneCell,
                contact.phone_other AS phoneOther,
                contact.address AS address,
                contact.city AS city,
                contact.state AS state,
                contact.zip AS zip,
                contact.notes AS notes,
                contact.is_hot AS isHotContact,
                contact.left_company AS leftCompany,
                contact.company_department_id AS departmentID,
                contact.reports_to AS reportsTo,
                company.name AS companyName,
                company_department.name AS department
            FROM
                contact
            LEFT JOIN company
                ON contact.company_id = company.company_id
            LEFT JOIN company_department
                ON contact.company_department_id = company_department.company_department_id
            WHERE
                contact.contact_id = %s
            AND
                contact.site_id = %s",
            $this->_db->makeQueryInteger($contactID),
            $this->_siteID,
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Returns the entire contacts list.
     *
     * @return array contacts data
     */
    public function getAll($userID = -1, $companyID = -1)
    {
        if ($userID >= 0)
        {
            $userCriterion = sprintf(
                'AND contact.owner = %s', $userID, $userID
            );
        }
        else
        {
            $userCriterion = '';
        }

        if ($companyID >= 0)
        {
            $companyCriterion = sprintf(
                'AND company.company_id = %s', $companyID
            );
        }
        else
        {
            $companyCriterion = '';
        }

        $sql = sprintf(
            "SELECT
                contact.contact_id AS contactID,
                contact.company_id AS companyID,
                contact.last_name AS lastName,
                contact.first_name AS firstName,
                contact.title AS title,
                contact.phone_work AS phoneWork,
                contact.phone_cell AS phoneCell,
                contact.phone_other AS phoneOther,
                contact.email1 AS email1,
                contact.email2 AS email2,
                contact.is_hot AS isHot,
                contact.left_company AS leftCompany,
                company_department.name AS department,
                DATE_FORMAT(
                    contact.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    contact.date_modified, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                company.name AS companyName
            FROM
                contact
            LEFT JOIN company
                ON contact.company_id = company.company_id
            LEFT JOIN user AS owner_user
                ON contact.owner = owner_user.user_id
            LEFT JOIN company_department
                ON contact.company_department_id = company_department.company_department_id
            WHERE
                contact.site_id = %s
            %s
            %s
            ORDER BY
                contact.last_name ASC,
                contact.first_name ASC",
            $this->_siteID,
            $userCriterion,
            $companyCriterion
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all upcoming calendar events for given contact.
     * see Calendar::getUpcomingEvents.
     *
     * @return array calendar events
     */
    public function getUpcomingEvents($contactID)
    {
        $calendar = new Calendar($this->_siteID);
        return $calendar->getUpcomingEventsByDataItem(
            DATA_ITEM_CONTACT, $contactID
        );
    }

    /**
     * Updates a contact's modified timestamp.
     *
     * @param integer contact ID
     * @return boolean True if successful; false otherwise.
     */
    public function updateModified($contactID)
    {
        $sql = sprintf(
            "UPDATE
                contact
            SET
                date_modified = NOW()
            WHERE
                contact_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($contactID),
            $this->_siteID
        );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Returns an array of job orders data (jobOrderID, title, companyName)
     * for the specified contact ID.
     *
     * @param integer contact ID
     * @return array job orders data
     */
    public function getJobOrdersArray($contactID)
    {
        $sql = sprintf(
            "SELECT
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                company.name AS companyName,
                IF(joborder.contact_id = %s, 1, 0) AS isAssigned
            FROM
                joborder
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            LEFT JOIN contact
                ON company.company_id = contact.company_id
            WHERE
                contact.contact_id = %s
            AND
                joborder.site_id = %s
            ORDER BY
                isAssigned DESC,
                title ASC",
            $this->_db->makeQueryInteger($contactID),
            $this->_db->makeQueryInteger($contactID),
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
     }

    /**
     * Returns the entire contacts list.
     *
     * @return array contacts data
     */
    public function getColdCallList($userID = -1, $companyID = -1)
    {
        if ($userID >= 0)
        {
            $userCriterion = sprintf(
                "AND contact.owner = %s", $userID, $userID
            );
        }
        else
        {
            $userCriterion = '';
        }

        if ($companyID >= 0)
        {
            $companyCriterion = sprintf(
                "AND company.company_id = %s", $companyID
            );
        }
        else
        {
            $companyCriterion = '';
        }

        $sql = sprintf(
            "SELECT
                contact.contact_id AS contactID,
                contact.company_id AS companyID,
                contact.last_name AS lastName,
                contact.first_name AS firstName,
                contact.title AS title,
                contact.phone_work AS phoneWork,
                company.name AS companyName
            FROM
                contact
            LEFT JOIN company
                ON contact.company_id = company.company_id
            WHERE
                contact.phone_work != ''
            AND
                contact.phone_work IS NOT NULL
            AND
                contact.site_id = %s
            AND
                company.site_id = %s
            %s
            %s
            ORDER BY
                company.name ASC,
                contact.first_name ASC,
                contact.last_name ASC",
            $this->_siteID,
            $this->_siteID,
            $userCriterion,
            $companyCriterion
        );

        return $this->_db->getAllAssoc($sql);
    }


    /**
     * Returns department ID for the given company by department name.
     * FIXME:  Shouldn't this be in companies?
     * FIXME:  Why are we passing a database handle in?
     *
     * @param string department name
     * @param integer company ID
     * @param handle database handle
     * @return integer department ID
     */
    public function getDepartmentIDByName($departmentName, $companyID, $db)
    {
        /* (none) always has an ID of 0. */
        if ($departmentName === '(none)')
        {
            return 0;
        }

        $sql = sprintf(
            "SELECT
                company_department_id AS departmentID
             FROM
                company_department
             WHERE
                name = %s
             AND
                company_id = %s
             AND
                site_id = %s",
             $this->_db->makeQueryString($departmentName),
             $companyID,
             $this->_siteID
        );
        $rs = $db->getAssoc($sql);

        if (empty($rs))
        {
            return 0;
        }

        return $rs['departmentID'];
    }
}


class ContactsDataGrid extends DataGrid
{
    protected $_siteID;


    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($instanceName, $siteID, $parameters, $misc = 0)
    {
        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;
        $this->_assignedCriterion = "";
        $this->_dataItemIDColumn = 'contact.contact_id';

        $this->_classColumns = array(
            'First Name' =>     array('select'         => 'contact.first_name AS firstName',
                                      'pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=contacts&amp;a=show&amp;contactID=\'.$rsData[\'contactID\'].\'" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'firstName\']).\'</a>\';',
                                      'sortableColumn' => 'firstName',
                                      'pagerWidth'     => 75,
                                      'pagerOptional'  => false,
                                      'alphaNavigation'=> true,
                                      'filter'         => 'contact.first_name'),

            'Last Name' =>      array('select'         => 'contact.last_name AS lastName',
                                     'sortableColumn'  => 'lastName',
                                     'pagerRender'     => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=contacts&amp;a=show&amp;contactID=\'.$rsData[\'contactID\'].\'" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'lastName\']).\'</a>\';',
                                     'pagerWidth'      => 85,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filter'         => 'contact.last_name'),

            'Company' =>     array('select'         => 'company.name AS name,'.
                                                       'company.company_id as companyID',
                                      'pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=companies&amp;a=show&amp;companyID=\'.$rsData[\'companyID\'].\'" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'name\']).\'</a>\';',
                                      'sortableColumn' => 'name',
                                      'pagerWidth'     => 60,
                                      'pagerOptional'  => true,
                                      'alphaNavigation'=> true,
                                      'filter'         => 'company.name'),

            'Title' =>        array('select'  => 'contact.title AS title',
                                     'sortableColumn'    => 'title',
                                     'pagerWidth'   => 140,
                                     'alphaNavigation' => true,
                                     'pagerOptional'  => true,
                                     'filter'         => 'contact.title'),

            'Department' =>  array('select'  => 'company_department.company_department_id AS companyDepartmentID, company_department.name as department',
                                     'join'     => 'LEFT JOIN company_department on company_department.company_department_id = contact.company_department_id',
                                     'sortableColumn'    => 'department',
                                     'pagerWidth'   => 120,
                                     'alphaNavigation' => true,
                                     'pagerOptional'  => true,
                                     'filter'         => 'company_department.department'),

            'Work Phone' =>   array('select'  => 'contact.phone_work AS workPhone',
                                     'sortableColumn'    => 'workPhone',
                                     'pagerWidth'   => 140,
                                     'alphaNavigation' => false,
                                     'pagerOptional'  => true,
                                     'filter'         => 'contact.work_phone'),

            'Cell Phone' =>    array('select'  => 'contact.phone_cell AS cellPhone',
                                     'sortableColumn'    => 'cellPhone',
                                     'pagerWidth'   => 140,
                                     'alphaNavigation' => false,
                                     'pagerOptional'  => true,
                                     'filter'         => 'contact.phone_cell'),

            'Other Phone' =>   array('select'  => 'contact.phone_other AS otherPhone',
                                     'sortableColumn'    => 'otherPhone',
                                     'pagerWidth'   => 140,
                                     'alphaNavigation' => false,
                                     'pagerOptional'  => true,
                                     'filter'         => 'contact.phone_other'),

            'E-Mail' =>         array('select'   => 'contact.email1 AS email1',
                                     'sortableColumn'     => 'email1',
                                     'pagerWidth'    => 80,
                                     'filter'         => 'contact.email1'),

            '2nd E-Mail' =>     array('select'   => 'contact.email2 AS email2',
                                     'sortableColumn'     => 'email2',
                                     'pagerWidth'    => 80,
                                     'filter'         => 'contact.email2'),

            'Address' =>        array('select'   => 'contact.address AS address',
                                     'sortableColumn'     => 'address',
                                     'pagerWidth'    => 250,
                                     'alphaNavigation' => true,
                                     'filter'         => 'contact.address'),

            'City' =>           array('select'   => 'contact.city AS city',
                                     'sortableColumn'     => 'city',
                                     'pagerWidth'    => 80,
                                     'alphaNavigation' => true,
                                     'filter'         => 'contact.city'),


            'State' =>          array('select'   => 'contact.state AS state',
                                     'sortableColumn'     => 'state',
                                     'filterType' => 'dropDown',
                                     'pagerWidth'    => 50,
                                     'alphaNavigation' => true,
                                     'filter'         => 'contact.state'),

            'Zip' =>            array('select'  => 'contact.zip AS zip',
                                     'sortableColumn'    => 'zip',
                                     'pagerWidth'   => 50,
                                     'filter'         => 'contact.zip'),

            'Misc Notes' =>     array('select'  => 'contact.notes AS notes',
                                     'sortableColumn'    => 'notes',
                                     'pagerWidth'   => 300,
                                     'filter'         => 'contact.notes'),

            'Owner' =>         array('pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'ownerFirstName\'], $rsData[\'ownerLastName\'], false, LAST_NAME_MAXLEN);',
                                     'exportRender'     => 'return $rsData[\'ownerFirstName\'] . " " .$rsData[\'ownerLastName\'];',
                                     'sortableColumn'     => 'ownerSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'pagerOptional'  => true,
                                     'filter'         => 'CONCAT(owner_user.first_name, owner_user.last_name)'),

            'Created' =>       array('select'   => 'DATE_FORMAT(contact.date_created, \'%m-%d-%y\') AS dateCreated',
                                     'pagerRender'      => 'return $rsData[\'dateCreated\'];',
                                     'sortableColumn'     => 'dateCreatedSort',
                                     'pagerWidth'    => 60,
                                     'filterHaving' => 'DATE_FORMAT(contact.date_created, \'%m-%d-%y\')'),

            'Modified' =>      array('select'   => 'DATE_FORMAT(contact.date_modified, \'%m-%d-%y\') AS dateModified',
                                     'pagerRender'      => 'return $rsData[\'dateModified\'];',
                                     'sortableColumn'     => 'dateModifiedSort',
                                     'pagerWidth'    => 60,
                                     'pagerOptional' => false,
                                     'filterHaving' => 'DATE_FORMAT(contact.date_modified, \'%m-%d-%y\')'),

            'OwnerID' =>       array('select'    => '',
                                     'filter'    => 'contact.owner',
                                     'pagerOptional' => false,
                                     'filterable' => false,
                                     'filterDescription' => 'Only My Contacts'),

            'IsHot' =>         array('select'    => '',
                                     'filter'    => 'contact.is_hot',
                                     'pagerOptional' => false,
                                     'filterable' => false,
                                     'filterDescription' => 'Only Hot Contacts')

        );

        if (US_ZIPS_ENABLED)
        {
            $this->_classColumns['Near Zipcode'] =
                               array('select'  => 'contact.zip AS zip',
                                     'filter' => 'contact.zip',
                                     'pagerOptional' => false,
                                     'filterTypes'   => '=@');
        }

        /* Extra fields get added as columns here. */
        $contacts = new Contacts($this->_siteID);
        $extraFieldsRS = $contacts->extraFields->getSettings();
        foreach ($extraFieldsRS as $index => $data)
        {
            $fieldName = $data['fieldName'];

            if (!isset($this->_classColumns[$fieldName]))
            {
                $columnDefinition = $contacts->extraFields->getDataGridDefinition($index, $data, $this->_db);

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
     * @return array clients data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        if ($this->getMiscArgument() != 0)
        {
            $savedListID = (int) $this->getMiscArgument();
            $joinSQL  .= ' INNER JOIN saved_list_entry
                                    ON saved_list_entry.data_item_type = '.DATA_ITEM_CONTACT.'
                                    AND saved_list_entry.data_item_id = contact.contact_id
                                    AND saved_list_entry.site_id = '.$this->_siteID.'
                                    AND saved_list_entry.saved_list_id = '.$savedListID;
        }
        else
        {
            $joinSQL  .= ' LEFT JOIN saved_list_entry
                                    ON saved_list_entry.data_item_type = '.DATA_ITEM_CONTACT.'
                                    AND saved_list_entry.data_item_id = contact.contact_id
                                    AND saved_list_entry.site_id = '.$this->_siteID;
        }

        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                contact.is_hot AS isHot,
                contact.contact_id AS contactID,
                contact.contact_id AS exportID,
                contact.date_modified AS dateModifiedSort,
                contact.date_created AS dateCreatedSort,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                CONCAT(owner_user.last_name, owner_user.first_name) AS ownerSort,
            %s
            FROM
                contact
            LEFT JOIN company
                ON contact.company_id = company.company_id
            LEFT JOIN user AS owner_user
                ON contact.owner = owner_user.user_id
            %s
            WHERE
                contact.site_id = %s
            %s
            %s
            GROUP BY contact.contact_id
            %s
            %s
            %s",
            $distinct,
            $selectSQL,
            $joinSQL,
            $this->_siteID,
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
