<?php
include_once('./vendor/autoload.php');
use OpenCATS\Entity\Company;
use OpenCATS\Entity\CompanyRepository;


/**
 * CATS
 * Companies Library
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
 * @version    $Id: Companies.php 3690 2007-11-26 18:07:17Z brian $
 */

include_once('./lib/Pager.php');
include_once('./lib/ListEditor.php');
include_once('./lib/EmailTemplates.php');
include_once('./lib/Attachments.php');
include_once('./lib/JobOrders.php');
include_once('./lib/Contacts.php');


/**
 *  Companies Library
 *  @package    CATS
 *  @subpackage Library
 */
class Companies
{
    private $_db;
    private $_siteID;

    public $extraFields;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        $this->extraFields = new ExtraFields($siteID, DATA_ITEM_COMPANY);
    }


    /**
     * Adds a company to the database and returns its company ID.
     *
     * @param string Name
     * @param string Address line
     * @param string City
     * @param string State
     * @param string Zip code
     * @param string Phone 1
     * @param string Phone 2
     * @param string Url
     * @param string Key technologies
     * @param boolean Is company hot
     * @param string Company notes
     * @param integer Entered-by user
     * @param integer Owner user
     * @return new Company ID, or -1 on failure.
     */
    public function add($name, $address, $city, $state, $zip, $phone1,
                        $phone2, $faxNumber, $url, $keyTechnologies, $isHot,
                        $notes, $enteredBy, $owner)
    {
        $company= Company::create(
            $this->_siteID,
            $name,
            $address,
            $city,
            $state,
            $zip,
            $phone1,
            $phone2,
            $faxNumber,
            $url,
            $keyTechnologies,
            $isHot,
            $notes,
            $enteredBy,
            $owner
        );
        $CompanyRepository = new CompanyRepository($this->_db);
        try {
            $companyId = $CompanyRepository->persist($company, new History($this->_siteID));
        } catch(CompanyRepositoryException $e) {
            return -1;
        }
        return $companyId;
    }

    /**
     * Updates a company.
     *
     * @param integer Company ID
     * @param string Name
     * @param string Address line
     * @param string City
     * @param string State
     * @param string Zip Code
     * @param string Phone 1
     * @param string Phone 2
     * @param string URL
     * @param string Key Technologies
     * @param boolean Is company hot
     * @param string Company notes
     * @param integer Owner user
     * @param integer Billing contact ID
     * @return boolean True if successful; false otherwise.
     */
    public function update($companyID, $name, $address, $city, $state,
                           $zip, $phone1, $phone2, $faxNumber, $url,
                           $keyTechnologies, $isHot, $notes, $owner,
                           $billingContact, $email, $emailAddress)
    {
        $sql = sprintf(
            "UPDATE
                company
             SET
                name             = %s,
                address         = %s,
                city             = %s,
                state            = %s,
                zip              = %s,
                phone1           = %s,
                phone2           = %s,
                fax_number       = %s,
                url              = %s,
                key_technologies = %s,
                is_hot           = %s,
                notes            = %s,
                billing_contact  = %s,
                owner            = %s,
                date_modified    = NOW()
            WHERE
                company_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryString($name),
            $this->_db->makeQueryString($address),
            $this->_db->makeQueryString($city),
            $this->_db->makeQueryString($state),
            $this->_db->makeQueryString($zip),
            $this->_db->makeQueryString($phone1),
            $this->_db->makeQueryString($phone2),
            $this->_db->makeQueryString($faxNumber),
            $this->_db->makeQueryString($url),
            $this->_db->makeQueryString($keyTechnologies),
            ($isHot ? '1' : '0'),
            $this->_db->makeQueryString($notes),
            $this->_db->makeQueryInteger($billingContact),
            $this->_db->makeQueryInteger($owner),
            $this->_db->makeQueryInteger($companyID),
            $this->_siteID
        );

        $preHistory = $this->get($companyID);
        $queryResult = $this->_db->query($sql);
        $postHistory = $this->get($companyID);

        if (!$queryResult)
        {
            return false;
        }

        $history = new History($this->_siteID);
        $history->storeHistoryChanges(DATA_ITEM_COMPANY, $companyID, $preHistory, $postHistory);

        if (!empty($emailAddress))
        {
            /* Send e-mail notification. */
            //FIXME: Make subject configurable.
            $mailer = new Mailer($this->_siteID);
            $mailerStatus = $mailer->sendToOne(
                array($emailAddress, ''),
                'CATS Notification: Company Ownership Change',
                $email,
                true
            );
        }

        return true;
    }

    /**
     * Removes a company and all associated records from the system.
     *
     * @param integer Company ID
     * @return void
     */
    public function delete($companyID)
    {
        /* Delete the company. */
        $sql = sprintf(
            "DELETE FROM
                company
            WHERE
                company_id = %s
            AND
                site_id = %s",
            $companyID,
            $this->_siteID
        );
        $this->_db->query($sql);

        $history = new History($this->_siteID);
        $history->storeHistoryDeleted(DATA_ITEM_COMPANY, $companyID);

        /* Find associated contacts. */
        $sql = sprintf(
            "SELECT
                contact_id AS contactID
            FROM
                contact
            WHERE
                company_id = %s
            AND
                site_id = %s",
            $companyID,
            $this->_siteID
        );
        $contactsRS = $this->_db->getAllAssoc($sql);

        /* Find associated job orders. */
        $sql = sprintf(
            "SELECT
                joborder_id AS jobOrderID
            FROM
                joborder
            WHERE
                company_id = %s
            AND
                site_id = %s",
            $companyID,
            $this->_siteID
        );
        $jobOrdersRS = $this->_db->getAllAssoc($sql);

        /* Find associated attachments. */
        $attachments = new Attachments($this->_siteID);
        $attachmentsRS = $attachments->getAll(
            DATA_ITEM_COMPANY, $companyID
        );

        /* Delete associated contacts. */
        $contacts = new Contacts($this->_siteID);
        foreach ($contactsRS as $rowIndex => $row)
        {
            $contacts->delete($row['contactID']);
        }

        /* Delete associated job orders. */
        $jobOrders = new JobOrders($this->_siteID);
        foreach ($jobOrdersRS as $rowIndex => $row)
        {
            $jobOrders->delete($row['jobOrderID']);
        }

        /* Delete associated attachments. */
        foreach ($attachmentsRS as $rowNumber => $row)
        {
            $attachments->delete($row['attachmentID']);
        }

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
            $this->_db->makeQueryInteger($companyID),
            $this->_siteID,
            DATA_ITEM_COMPANY
        );
        $this->_db->query($sql);

        /* Delete extra fields. */
        $this->extraFields->deleteValueByDataItemID($companyID);
    }

    /**
     * Returns all relevent company information for a given company ID.
     *
     * @param integer Company ID
     * @return array Company data
     */
    public function get($companyID)
    {
        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.owner AS owner,
                company.name AS name,
                company.is_hot AS isHot,
                company.address AS address,
                company.city AS city,
                company.state AS state,
                company.zip AS zip,
                company.phone1 AS phone1,
                company.phone2 AS phone2,
                company.fax_number AS faxNumber,
                company.url AS url,
                company.key_technologies AS keyTechnologies,
                company.notes AS notes,
                company.default_company AS defaultCompany,
                billing_contact.contact_id AS billingContact,
                CONCAT(
                    billing_contact.first_name, ' ', billing_contact.last_name
                ) AS billingContactFullName,
                DATE_FORMAT(
                    company.date_created, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateCreated,
                CONCAT(
                    entered_by_user.first_name, ' ', entered_by_user.last_name
                ) AS enteredByFullName,
                CONCAT(
                    owner_user.first_name, ' ', owner_user.last_name
                ) AS ownerFullName,
                owner_user.email AS owner_email
            FROM
                company
            LEFT JOIN user AS entered_by_user
                ON company.entered_by = entered_by_user.user_id
            LEFT JOIN user AS owner_user
                ON company.owner = owner_user.user_id
            LEFT JOIN contact AS billing_contact
                ON company.billing_contact = billing_contact.contact_id
            WHERE
                company.company_id = %s
            AND
                company.site_id = %s",
            $this->_db->makeQueryInteger($companyID),
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Returns all company information relevent for the Edit Company page for
     * a given company ID.
     *
     * @param integer Company ID
     * @return array Company data
     */
    public function getForEditing($companyID)
    {
        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.owner AS owner,
                company.name AS name,
                company.is_hot AS isHot,
                company.address AS address,
                company.city AS city,
                company.state AS state,
                company.zip AS zip,
                company.phone1 AS phone1,
                company.phone2 AS phone2,
                company.fax_number AS faxNumber,
                company.url AS url,
                company.key_technologies AS keyTechnologies,
                company.notes AS notes,
                company.default_company AS defaultCompany,
                billing_contact.contact_id AS billingContact
            FROM
                company
            LEFT JOIN contact AS billing_contact
                ON company.billing_contact = billing_contact.contact_id
            WHERE
                company.company_id = %s
            AND
                company.site_id = %s",
            $this->_db->makeQueryInteger($companyID),
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Used by new site creation code to set a new company as the
     * default company for a site.  The default company can
     * not be deleted, and is referred to as "Internal Postings."
     *
     * @param integer Company ID
     * @return array Company data
     */
    public function setCompanyDefault($companyID)
    {
        $sql = sprintf(
            "UPDATE
                company
             SET
                default_company = 1,
                date_modified  = NOW()
            WHERE
                company_id = %s",
            $this->_db->makeQueryInteger($companyID)
        );

        $preHistory = $this->get($companyID);
        $queryResult = $this->_db->query($sql);
        $postHistory = $this->get($companyID);

        if (!$queryResult)
        {
            return false;
        }

        $history = new History($this->_siteID);
        $history->storeHistoryChanges(
            DATA_ITEM_COMPANY, $companyID, $preHistory, $postHistory
        );

        return true;
    }

    /**
     * Returns all relevent company information for a given company ID.
     *
     * @param integer Company ID
     * @return array Company data
     */
    public function getDefaultCompany()
    {
        $sql = sprintf(
            "SELECT
                company.company_id AS companyID
            FROM
                company
            WHERE
                company.default_company = 1
            AND
                company.site_id = %s",
            $this->_siteID
        );
        $rs = $this->_db->getAssoc($sql);

        if (empty($rs))
        {
            return false;
        }

        return $rs['companyID'];
    }

    /**
     * Returns a minimal record set of all companies (for use when creating
     * drop-down lists of companies, etc.).
     *
     * @return array Companies data
     */
    public function getSelectList()
    {
        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS name
            FROM
                company
            WHERE
                company.site_id = %s
            ORDER BY
                company.name ASC",
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
     }

    /**
     * Returns an array of location data (city, state, zip) for the specified
     * company ID.
     *
     * @param integer Company ID
     * @return array Companies data
     */
    public function getLocationArray($companyID)
    {
        $sql = sprintf(
            "SELECT
                company.address AS address,
                company.city AS city,
                company.state AS state,
                company.zip AS zip
            FROM
                company
            WHERE
                company.company_id = %s
            AND
                company.site_id = %s",
            $this->_db->makeQueryInteger($companyID),
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
     }

    /**
     * Returns an array of contacts data (contactID, firstName, lastName)
     * for the specified company ID.
     *
     * @param integer Company ID
     * @return array Contacts data
     */
    public function getContactsArray($companyID)
    {
        $sql = sprintf(
            "SELECT
                contact.contact_id AS contactID,
                contact.first_name AS firstName,
                contact.last_name AS lastName
            FROM
                contact
            WHERE
                contact.company_id = %s
            AND
                contact.site_id = %s
            ORDER BY
                contact.last_name ASC,
                contact.first_name ASC",
            $this->_db->makeQueryInteger($companyID),
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
     }

    /**
     * Returns an array of job orders data (jobOrderID, title, companyName)
     * for the specified company ID.
     *
     * @param integer Company ID
     * @return array Job Orders data
     */
    public function getJobOrdersArray($companyID)
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
            WHERE
                joborder.company_id = %s
            AND
                joborder.site_id = %s
            ORDER BY
                title ASC",
            $this->_db->makeQueryInteger($companyID),
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
     }

    /**
     * Returns a response array of all departments for a company.
     * by getDifferencesFromList (ListEditor.php).
     *
     * @param integer Company ID
     * @return array Departments
     */
    public function getDepartments($companyID)
    {
        $sql = sprintf(
            "SELECT
                company_department.company_department_id AS departmentID,
                company_department.name AS name
            FROM
                company_department
            WHERE
                company_department.site_id = %s
            AND
                company_department.company_id = %s
            ORDER BY
                company_department.name ASC",
            $this->_siteID,
            $this->_db->makeQueryInteger($companyID)
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Updates a companies departments with an array generated
     * by getDifferencesFromList (ListEditor.php).
     *
     * @param integer Company ID
     * @param array getDifferencesFromList
     * @return void
     */
    public function updateDepartments($companyID, $updates)
    {
        $history = new History($this->_siteID);

        foreach ($updates as $update)
        {
            switch ($update[2])
            {
                case LIST_EDITOR_ADD:
                    $sql = sprintf(
                        "INSERT INTO company_department (
                            name,
                            company_id,
                            site_id,
                            date_created
                         )
                         VALUES (
                            %s,
                            %s,
                            %s,
                            NOW()
                         )",
                         $this->_db->makeQueryString($update[0]),
                         $this->_db->makeQueryInteger($companyID),
                         $this->_siteID
                    );
                    $this->_db->query($sql);

                    $history->storeHistorySimple(
                        DATA_ITEM_COMPANY,
                        0,
                        '(USER) added ' . $update[0] . ' to departments.'
                    );

                    break;

                case LIST_EDITOR_REMOVE:
                    $sql = sprintf(
                        "DELETE FROM
                            company_department
                         WHERE
                            company_department_id = %s
                         AND
                            site_id = %s",
                         $this->_db->makeQueryInteger($update[1]),
                         $this->_siteID
                    );
                    $this->_db->query($sql);

                    $history->storeHistorySimple(
                        DATA_ITEM_COMPANY,
                        0,
                        '(USER) removed ' . $update[0] . ' from departments.'
                    );

                    break;

                case LIST_EDITOR_MODIFY:
                    $sql = sprintf(
                        "UPDATE
                            company_department
                         SET
                            name = %s
                         WHERE
                            company_department_id = %s
                         AND
                            site_id = %s",
                         $this->_db->makeQueryString($update[0]),
                         $this->_db->makeQueryInteger($update[1]),
                         $this->_siteID
                    );
                    $this->_db->query($sql);

                    $history->storeHistorySimple(
                        DATA_ITEM_COMPANY,
                        0,
                        '(USER) renamed a department to ' . $update[0] . '.'
                    );

                    break;

                default:
                    break;
            }
        }
    }
}


class CompaniesDataGrid extends DataGrid
{
    protected $_siteID;


    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($instanceName, $siteID, $parameters, $misc)
    {
        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;
        $this->_assignedCriterion = "";
        $this->_dataItemIDColumn = 'company.company_id';

        $this->_classColumns = array(
            'Attachments' => array(  'select'   => 'IF(attachment_id, 1, 0) AS attachmentPresent',
                                     'pagerRender' => '
                                                    if ($rsData[\'attachmentPresent\'] == 1)
                                                    {
                                                        $return = \'<img src="images/paperclip.gif" alt="" width="16" height="16" title="Attachment Present" />\';
                                                    }
                                                    else
                                                    {
                                                        $return = \'<img src="images/mru/blank.gif" alt="" width="16" height="16" />\';
                                                    }

                                                    return $return;
                                                   ',

                                     'pagerWidth'    => 10,
                                     'pagerOptional' => true,
                                     'pagerNoTitle' => true,
                                     'sizable'  => false,
                                     'exportable' => false,
                                     'filterable' => false),

            'Name' =>     array('select'         => 'company.name AS name',
                                      'pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=companies&amp;a=show&amp;companyID=\'.$rsData[\'companyID\'].\'" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'name\']).\'</a>\';',
                                      'sortableColumn' => 'name',
                                      'pagerWidth'     => 60,
                                      'pagerOptional'  => false,
                                      'alphaNavigation'=> true,
                                      'filter'         => 'company.name'),

            'Jobs' =>       array('select'   => '(
                                                            SELECT
                                                                COUNT(*)
                                                            FROM
                                                                joborder
                                                            WHERE
                                                                company_id = company.company_id
                                                            AND
                                                                site_id = '.$this->_siteID.'
                                                        ) AS jobs',
                                     'pagerRender'      => 'if ($rsData[\'jobs\'] != 0) {return $rsData[\'jobs\'];} else {return \'\';}',
                                     'sortableColumn'     => 'jobs',
                                     'pagerWidth'    => 40,
                                     'filterHaving'  => 'jobs',
                                     'filterTypes'   => '===>=<'),

            'Phone' =>     array('select'   => 'company.phone1 AS phone',
                                     'sortableColumn'     => 'phone',
                                     'pagerWidth'    => 80,
                                     'filter'         => 'company.phone1'),

            'Phone 2' =>     array('select'   => 'company.phone2 AS phone2',
                                     'sortableColumn'     => 'phone2',
                                     'pagerWidth'    => 80,
                                     'filter'         => 'company.phone2'),


            'City' =>           array('select'   => 'company.city AS city',
                                     'sortableColumn'     => 'city',
                                     'pagerWidth'    => 80,
                                     'alphaNavigation' => true,
                                     'filter'         => 'company.city'),


            'State' =>          array('select'   => 'company.state AS state',
                                     'sortableColumn'     => 'state',
                                     'filterType' => 'dropDown',
                                     'pagerWidth'    => 50,
                                     'alphaNavigation' => true,
                                     'filter'         => 'company.state'),

            'Zip' =>            array('select'  => 'company.zip AS zip',
                                     'sortableColumn'    => 'zip',
                                     'pagerWidth'   => 50,
                                     'filter'         => 'company.zip'),


            'Web Site' =>      array('select'  => 'company.url AS webSite',
                                     'pagerRender'     => 'return \'<a href="\'.htmlspecialchars($rsData[\'webSite\']).\'" target="_blank">\'.htmlspecialchars($rsData[\'webSite\']).\'</a>\';',
                                     'sortableColumn'    => 'webSite',
                                     'pagerWidth'   => 80,
                                     'filter'         => 'company.url'),

            'Owner' =>         array('select'   => 'owner_user.first_name AS ownerFirstName,' .
                                                   'owner_user.last_name AS ownerLastName,' .
                                                   'CONCAT(owner_user.last_name, owner_user.first_name) AS ownerSort',
                                     'pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'ownerFirstName\'], $rsData[\'ownerLastName\'], false, LAST_NAME_MAXLEN);',
                                     'exportRender'     => 'return $rsData[\'ownerFirstName\'] . " " .$rsData[\'ownerLastName\'];',
                                     'sortableColumn'     => 'ownerSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(owner_user.first_name, owner_user.last_name)'),

            'Contact' =>       array('select'   => 'contact.first_name AS contactFirstName,' .
                                                   'contact.last_name AS contactLastName,' .
                                                   'CONCAT(contact.last_name, contact.first_name) AS contactSort,' .
                                                   'contact.contact_id AS contactID',
                                     'pagerRender'      => 'return \'<a href="'.CATSUtility::getIndexName().'?m=contacts&amp;a=show&amp;contactID=\'.$rsData[\'contactID\'].\'">\'.StringUtility::makeInitialName($rsData[\'contactFirstName\'], $rsData[\'contactLastName\'], false, LAST_NAME_MAXLEN).\'</a>\';',
                                     'exportRender'     => 'return $rsData[\'contactFirstName\'] . " " .$rsData[\'contactLastName\'];',
                                     'sortableColumn'     => 'contactSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(contact.first_name, contact.last_name)'),


            'Created' =>       array('select'   => 'DATE_FORMAT(company.date_created, \'%m-%d-%y\') AS dateCreated',
                                     'pagerRender'      => 'return $rsData[\'dateCreated\'];',
                                     'sortableColumn'     => 'dateCreatedSort',
                                     'pagerWidth'    => 60,
                                     'filterHaving' => 'DATE_FORMAT(company.date_created, \'%m-%d-%y\')'),

            'Modified' =>      array('select'   => 'DATE_FORMAT(company.date_modified, \'%m-%d-%y\') AS dateModified',
                                     'pagerRender'      => 'return $rsData[\'dateModified\'];',
                                     'sortableColumn'     => 'dateModifiedSort',
                                     'pagerWidth'    => 60,
                                     'pagerOptional' => false,
                                     'filterHaving' => 'DATE_FORMAT(company.date_modified, \'%m-%d-%y\')'),

            'Misc Notes' =>     array('select'  => 'company.notes AS notes',
                                     'sortableColumn'    => 'notes',
                                     'pagerWidth'   => 300,
                                     'filter'         => 'company.notes'),

            'OwnerID' =>       array('select'    => '',
                                     'filter'    => 'company.owner',
                                     'pagerOptional' => false,
                                     'filterable' => false,
                                     'filterDescription' => 'Only My Companies'),

            'IsHot' =>         array('select'    => '',
                                     'filter'    => 'company.is_hot',
                                     'pagerOptional' => false,
                                     'filterable' => false,
                                     'filterDescription' => 'Only Hot Companies')
        );

        if (US_ZIPS_ENABLED)
        {
            $this->_classColumns['Near Zipcode'] =
                               array('select'  => 'company.zip AS zip',
                                     'filter' => 'company.zip',
                                     'pagerOptional' => false,
                                     'filterTypes'   => '=@');
        }

        /* Extra fields get added as columns here. */
        $companies = new Companies($this->_siteID);
        $extraFieldsRS = $companies->extraFields->getSettings();
        foreach ($extraFieldsRS as $index => $data)
        {
            $fieldName = $data['fieldName'];

            if (!isset($this->_classColumns[$fieldName]))
            {
                $columnDefinition = $companies->extraFields->getDataGridDefinition($index, $data, $this->_db);

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
     * @return array Clients data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        if ($this->getMiscArgument() != 0)
        {
            $savedListID = (int) $this->getMiscArgument();
            $joinSQL  .= ' INNER JOIN saved_list_entry
                                    ON saved_list_entry.data_item_type = '.DATA_ITEM_COMPANY.'
                                    AND saved_list_entry.data_item_id = company.company_id
                                    AND saved_list_entry.site_id = '.$this->_siteID.'
                                    AND saved_list_entry.saved_list_id = '.$savedListID;
        }
        else
        {
            $joinSQL  .= ' LEFT JOIN saved_list_entry
                                    ON saved_list_entry.data_item_type = '.DATA_ITEM_COMPANY.'
                                    AND saved_list_entry.data_item_id = company.company_id
                                    AND saved_list_entry.site_id = '.$this->_siteID;
        }

        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                IF(attachment_id, 1, 0) AS attachmentPresent,
                company.is_hot AS isHot,
                company.company_id AS companyID,
                company.company_id AS exportID,
                company.is_hot AS isHot,
                company.date_modified AS dateModifiedSort,
                company.date_created AS dateCreatedSort,
            %s
            FROM
                company
            LEFT JOIN user AS owner_user
                ON company.owner = owner_user.user_id
            LEFT JOIN joborder
                ON company.company_id = joborder.company_id
            LEFT JOIN contact
                ON company.billing_contact = contact.contact_id
            LEFT JOIN attachment
                ON company.company_id = attachment.data_item_id
                AND attachment.data_item_type = %s
            %s
            WHERE
                company.site_id = %s
            %s
            %s
            GROUP BY company.company_id
            %s
            %s
            %s",
            $distinct,
            $selectSQL,
            DATA_ITEM_COMPANY,
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
