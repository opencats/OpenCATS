<?php
/*
 * CATS
 * Contacts Module
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
 * $Id: ContactsUI.php 3444 2007-11-06 23:16:27Z will $
 */

include_once('./lib/StringUtility.php');
include_once('./lib/ResultSetUtility.php');
include_once('./lib/DateUtility.php');
include_once('./lib/Contacts.php');
include_once('./lib/Companies.php');
include_once('./lib/JobOrders.php');
include_once('./lib/ActivityEntries.php');
include_once('./lib/Export.php');
include_once('./lib/ExtraFields.php');
include_once('./lib/Calendar.php');
include_once('./lib/CommonErrors.php');


class ContactsUI extends UserInterface
{
    /* Maximum number of characters of the job notes to show without the user
     * clicking "[More]"
     */
    const NOTES_MAXLEN = 500;

    /* Maximum number of characters of the company name to show on the main
     * contacts listing.
     */
    const TRUNCATE_CLIENT_NAME = 22;

    /* Maximum number of characters of the contact's title to show on the main
     * contacts listing.
     */
    const TRUNCATE_TITLE = 24;


    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = true;
        $this->_moduleDirectory = 'contacts';
        $this->_moduleName = 'contacts';
        $this->_moduleTabText = 'Contacts';
        $this->_subTabs = array(
            'Add Contact'     => CATSUtility::getIndexName() . '?m=contacts&amp;a=add*al=' . ACCESS_LEVEL_EDIT . '@contacts.add',
            'Search Contacts' => CATSUtility::getIndexName() . '?m=contacts&amp;a=search',
            'Cold Call List'  => CATSUtility::getIndexName() . '?m=contacts&amp;a=showColdCallList'
        );
    }


    public function handleRequest()
    {
        $action = $this->getAction();

        if (!eval(Hooks::get('CONTACTS_HANDLE_REQUEST'))) return;

        switch ($action)
        {
            case 'show':
                if ($this->getUserAccessLevel('contacts.show') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->show();
                break;

            case 'add':
                if ($this->getUserAccessLevel('contacts.add') < ACCESS_LEVEL_EDIT)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                if ($this->isPostBack())
                {
                    $this->onAdd();
                }
                else
                {
                    $this->add();
                }

                break;

            case 'edit':
                if ($this->getUserAccessLevel('contacts.edit') < ACCESS_LEVEL_EDIT)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                if ($this->isPostBack())
                {
                    $this->onEdit();
                }
                else
                {
                    $this->edit();
                }

                break;

            case 'delete':
                if ($this->getUserAccessLevel('contacts.delete') < ACCESS_LEVEL_DELETE)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->onDelete();
                break;

            case 'search':
                if ($this->getUserAccessLevel('contacts.search') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                include_once('./lib/Search.php');

                if ($this->isGetBack())
                {
                    $this->onSearch();
                }
                else
                {
                    $this->search();
                }

                break;

            case 'addActivityScheduleEvent':
                if ($this->getUserAccessLevel('contacts.addActivityScheduleEvent') < ACCESS_LEVEL_EDIT)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                if ($this->isPostBack())
                {
                    $this->onAddActivityScheduleEvent();
                }
                else
                {
                    $this->addActivityScheduleEvent();
                }

                break;

            case 'showColdCallList':
                if ($this->getUserAccessLevel('contacts.showColdCallList') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->showColdCallList();
                break;

            case 'downloadVCard':
                if ($this->getUserAccessLevel('contacts.downloadVCard') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                include_once('./lib/VCard.php');

                $this->downloadVCard();
                break;

            /* Main contacts page. */
            case 'listByView':
            default:
                if ($this->getUserAccessLevel('contacts.list') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->listByView();
                break;
        }
    }

    /*
     * Called by handleRequest() to process loading the list / main page.
     */
    private function listByView($errMessage = '')
    {
        if (!eval(Hooks::get('CONTACTS_LIST_BY_VIEW_TOP'))) return;

        $dataGridProperties = DataGrid::getRecentParamaters("contacts:ContactsListByViewDataGrid");

        /* If this is the first time we visited the datagrid this session, the recent paramaters will
         * be empty.  Fill in some default values. */
        if ($dataGridProperties == array())
        {
            $dataGridProperties = array('rangeStart'    => 0,
                                        'maxResults'    => 15,
                                        'filterVisible' => false);
        }

        $dataGrid = DataGrid::get("contacts:ContactsListByViewDataGrid", $dataGridProperties);

        $this->_template->assign('active', $this);
        $this->_template->assign('dataGrid', $dataGrid);
        $this->_template->assign('userID', $_SESSION['CATS']->getUserID());
        $this->_template->assign('errMessage', $errMessage);

        $contacts = new Contacts($this->_siteID);
        $this->_template->assign('totalContacts', $contacts->getCount());

        if (!eval(Hooks::get('CONTACTS_LIST_BY_VIEW'))) return;

        $this->_template->display('./modules/contacts/Contacts.tpl');
    }

    /*
     * Called by handleRequest() to process loading the details page.
     */
    private function show()
    {
        /* Bail out if we don't have a valid contact ID. */
        if (!$this->isRequiredIDValid('contactID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid contact ID.');
        }

        $contactID = $_GET['contactID'];

        $contacts = new Contacts($this->_siteID);
        $data = $contacts->get($contactID);

        /* Bail out if we got an empty result set. */
        if (empty($data))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'The specified contact ID could not be found.');
        }

        /* We want to handle formatting the city and state here instead
         * of in the template.
         */
        $data['cityAndState'] = StringUtility::makeCityStateString(
            $data['city'], $data['state']
        );

        /*
         * Replace newlines with <br />, fix HTML "special" characters, and
         * strip leading empty lines and spaces.
         */
        $data['notes'] = trim(
            nl2br(htmlspecialchars($data['notes'], ENT_QUOTES))
        );

        /* Chop $data['notes'] to make $data['shortNotes']. */
        if (strlen($data['notes']) > self::NOTES_MAXLEN)
        {
            $data['shortNotes']  = substr(
                $data['notes'], 0, self::NOTES_MAXLEN
            );
            $isShortNotes = true;
        }
        else
        {
            $data['shortNotes'] = $data['notes'];
            $isShortNotes = false;
        }

        /* Hot contacts [can] have different title styles than normal contacts. */
        if ($data['isHotContact'] == 1)
        {
            $data['titleClassContact'] = 'jobTitleHot';
        }
        else
        {
            $data['titleClassContact'] = 'jobTitleCold';
        }

        /* Hot companies [can] also have different title styles than normal companies. */
        if ($data['isHotCompany'] == 1)
        {
            $data['titleClassCompany'] = 'jobTitleHot';
        }
        else
        {
            $data['titleClassCompany'] = 'jobTitleCold';
        }

        $jobOrders   = new JobOrders($this->_siteID);
        $jobOrdersRS = $jobOrders->getAll(JOBORDERS_STATUS_ALL, -1, -1, $contactID);

        if (!empty($jobOrdersRS))
        {
            foreach ($jobOrdersRS as $rowIndex => $row)
            {
                /* Convert '00-00-00' dates to empty strings. */
                $jobOrdersRS[$rowIndex]['startDate'] = DateUtility::fixZeroDate(
                    $jobOrdersRS[$rowIndex]['startDate']
                );

                /* Hot jobs [can] have different title styles than normal
                 * jobs.
                 */
                if ($jobOrdersRS[$rowIndex]['isHot'] == 1)
                {

                    $jobOrdersRS[$rowIndex]['linkClass'] = 'jobLinkHot';
                }
                else
                {
                    $jobOrdersRS[$rowIndex]['linkClass'] = 'jobLinkCold';
                }

                $jobOrdersRS[$rowIndex]['recruiterAbbrName'] = StringUtility::makeInitialName(
                    $jobOrdersRS[$rowIndex]['recruiterFirstName'],
                    $jobOrdersRS[$rowIndex]['recruiterLastName'],
                    false,
                    LAST_NAME_MAXLEN
                );

                $jobOrdersRS[$rowIndex]['ownerAbbrName'] = StringUtility::makeInitialName(
                    $jobOrdersRS[$rowIndex]['ownerFirstName'],
                    $jobOrdersRS[$rowIndex]['ownerLastName'],
                    false,
                    LAST_NAME_MAXLEN
                );
            }
        }

        $activityEntries = new ActivityEntries($this->_siteID);
        $activityRS = $activityEntries->getAllByDataItem($contactID, DATA_ITEM_CONTACT);
        if (!empty($activityRS))
        {
            foreach ($activityRS as $rowIndex => $row)
            {
                if (empty($activityRS[$rowIndex]['notes']))
                {
                    $activityRS[$rowIndex]['notes'] = '(No Notes)';
                }

                if (empty($activityRS[$rowIndex]['jobOrderID']) ||
                    empty($activityRS[$rowIndex]['regarding']))
                {
                    $activityRS[$rowIndex]['regarding'] = 'General';
                }

                $activityRS[$rowIndex]['enteredByAbbrName'] = StringUtility::makeInitialName(
                    $activityRS[$rowIndex]['enteredByFirstName'],
                    $activityRS[$rowIndex]['enteredByLastName'],
                    false,
                    LAST_NAME_MAXLEN
                );
            }
        }

        /* Get upcoming calendar entries. */
        $calendarRS = $contacts->getUpcomingEvents($contactID);
        if (!empty($calendarRS))
        {
            foreach ($calendarRS as $rowIndex => $row)
            {
                $calendarRS[$rowIndex]['enteredByAbbrName'] = StringUtility::makeInitialName(
                    $calendarRS[$rowIndex]['enteredByFirstName'],
                    $calendarRS[$rowIndex]['enteredByLastName'],
                    false,
                    LAST_NAME_MAXLEN
                );
            }
        }

        /* Add an MRU entry. */
        $_SESSION['CATS']->getMRU()->addEntry(
            DATA_ITEM_CONTACT, $contactID, $data['firstName'] . ' ' . $data['lastName']
        );

        /* Get extra fields. */
        $extraFieldRS = $contacts->extraFields->getValuesForShow($contactID);

        /* Is the user an admin - can user see history? */
        if ($this->getUserAccessLevel('contacts.show') < ACCESS_LEVEL_DEMO)
        {
            $privledgedUser = false;
        }
        else
        {
            $privledgedUser = true;
        }

        $this->_template->assign('active', $this);
        $this->_template->assign('data', $data);
        $this->_template->assign('isShortNotes', $isShortNotes);
        $this->_template->assign('jobOrdersRS', $jobOrdersRS);
        $this->_template->assign('extraFieldRS', $extraFieldRS);
        $this->_template->assign('calendarRS', $calendarRS);
        $this->_template->assign('activityRS', $activityRS);
        $this->_template->assign('contactID', $contactID);
        $this->_template->assign('privledgedUser', $privledgedUser);
        $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());

        if (!eval(Hooks::get('CONTACTS_SHOW'))) return;

        $this->_template->display('./modules/contacts/Show.tpl');
    }

    /*
     * Called by handleRequest() to process loading the add page.
     */
    private function add()
    {
        $companies = new Companies($this->_siteID);
        $contacts = new Contacts($this->_siteID);

        /* Do we have a selected_company_id? */
        if ($_SESSION['CATS']->isHrMode())
        {
            $selectedCompanyID = $companies->getDefaultCompany();
            $companyRS = $companies->get($selectedCompanyID);
            $reportsToRS = $contacts->getAll(-1, $selectedCompanyID);
        }
        else if (!$this->isRequiredIDValid('selected_company_id', $_GET))
        {
            $selectedCompanyID = false;
            $companyRS = array();
            $reportsToRS = array();
        }
        else
        {
            $selectedCompanyID = $_GET['selected_company_id'];
            $companyRS = $companies->get($selectedCompanyID);
            $reportsToRS = $contacts->getAll(-1, $_GET['selected_company_id']);
        }

        /* Get extra fields. */
        $extraFieldRS = $contacts->extraFields->getValuesForAdd();

        $defaultCompanyID = $companies->getDefaultCompany();
        if ($defaultCompanyID !== false)
        {
            $defaultCompanyRS = $companies->get($defaultCompanyID);
        }
        else
        {
            $defaultCompanyRS = array();
        }

        if (!eval(Hooks::get('CONTACTS_ADD'))) return;

        $this->_template->assign('defaultCompanyID', $defaultCompanyID);
        $this->_template->assign('defaultCompanyRS', $defaultCompanyRS);
        $this->_template->assign('active', $this);
        $this->_template->assign('extraFieldRS', $extraFieldRS);
        $this->_template->assign('subActive', 'Add Contact');
        $this->_template->assign('companyRS', $companyRS);
        $this->_template->assign('reportsToRS', $reportsToRS);
        $this->_template->assign('selectedCompanyID', $selectedCompanyID);
        $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());
        $this->_template->display('./modules/contacts/Add.tpl');
    }

    /*
     * Called by handleRequest() to process saving / submitting the add page.
     */
    private function onAdd()
    {
        /* Bail out if we don't have a valid company ID. */
        if (!$this->isRequiredIDValid('companyID', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid company ID.');
        }

        $formattedPhoneWork = StringUtility::extractPhoneNumber(
            $this->getTrimmedInput('phoneWork', $_POST)
        );
        if (!empty($formattedPhoneWork))
        {
            $phoneWork = $formattedPhoneWork;
        }
        else
        {
            $phoneWork = $this->getTrimmedInput('phoneWork', $_POST);
        }

        $formattedPhoneCell = StringUtility::extractPhoneNumber(
            $this->getTrimmedInput('phoneCell', $_POST)
        );
        if (!empty($formattedPhoneCell))
        {
            $phoneCell = $formattedPhoneCell;
        }
        else
        {
            $phoneCell = $this->getTrimmedInput('phoneCell', $_POST);
        }

        $formattedPhoneOther = StringUtility::extractPhoneNumber(
            $this->getTrimmedInput('phoneOther', $_POST)
        );
        if (!empty($formattedPhoneOther))
        {
            $phoneOther = $formattedPhoneOther;
        }
        else
        {
            $phoneOther = $this->getTrimmedInput('phoneOther', $_POST);
        }

        $companyID = $_POST['companyID'];

        $firstName  = $this->getTrimmedInput('firstName', $_POST);
        $lastName   = $this->getTrimmedInput('lastName', $_POST);
        $title      = $this->getTrimmedInput('title', $_POST);
        $department = $this->getTrimmedInput('department', $_POST);
        $reportsTo  = $this->getTrimmedInput('reportsTo', $_POST);
        $email1     = $this->getTrimmedInput('email1', $_POST);
        $email2     = $this->getTrimmedInput('email2', $_POST);
        $address    = $this->getTrimmedInput('address', $_POST);
        $city       = $this->getTrimmedInput('city', $_POST);
        $state      = $this->getTrimmedInput('state', $_POST);
        $zip        = $this->getTrimmedInput('zip', $_POST);
        $notes      = $this->getTrimmedInput('notes', $_POST);

         /* Hot contact? */
        $isHot = $this->isChecked('isHot', $_POST);

        /* Departments list editor. */
        $departmentsCSV = $this->getTrimmedInput('departmentsCSV', $_POST);

        /* Bail out if any of the required fields are empty. */
        if (empty($firstName) || empty($lastName) || empty($title))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
        }

        /* Update departments. */
        $companies = new Companies($this->_siteID);
        $departments = $companies->getDepartments($companyID);
        $departmentsDifferences = ListEditor::getDifferencesFromList(
            $departments, 'name', 'departmentID', $departmentsCSV
        );
        $companies->updateDepartments($companyID, $departmentsDifferences);

        if (!eval(Hooks::get('CONTACTS_ON_ADD_PRE'))) return;

        $contacts = new Contacts($this->_siteID);
        $contactID = $contacts->add(
            $companyID, $firstName, $lastName, $title, $department, $reportsTo,
            $email1, $email2, $phoneWork, $phoneCell, $phoneOther, $address,
            $city, $state, $zip, $isHot, $notes, $this->_userID, $this->_userID
        );

        if ($contactID <= 0)
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to add contact.');
        }

        /* Update extra fields. */
        $contacts->extraFields->setValuesOnEdit($contactID);

        if (!eval(Hooks::get('CONTACTS_ON_ADD_POST'))) return;

        if (isset($_GET['v']) && $_GET['v'] != -1)
        {
            CATSUtility::transferRelativeURI(
                'm=companies&a=show&companyID=' . $_GET['v']
            );
        }
        else
        {
            CATSUtility::transferRelativeURI(
                'm=contacts&a=show&contactID=' . $contactID
            );
        }
    }

    /*
     * Called by handleRequest() to process loading the edit page.
     */
    private function edit()
    {
        /* Bail out if we don't have a valid contact ID. */
        if (!$this->isRequiredIDValid('contactID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid contact ID.');
        }

        $contactID = $_GET['contactID'];

        $contacts = new Contacts($this->_siteID);
        $data = $contacts->getForEditing($contactID);

        /* Bail out if we got an empty result set. */
        if (empty($data))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'The specified contact ID could not be found.');
        }

        $companies = new Companies($this->_siteID);
        $companiesRS = $companies->getSelectList();

        $users = new Users($this->_siteID);
        $usersRS = $users->getSelectList();

        /* Add an MRU entry. */
        $_SESSION['CATS']->getMRU()->addEntry(
            DATA_ITEM_CONTACT, $contactID, $data['firstName'] . ' ' . $data['lastName']
        );

        /* Get extra fields. */
        $extraFieldRS = $contacts->extraFields->getValuesForEdit($contactID);

        /* Get departments. */
        $departmentsRS = $companies->getDepartments($data['companyID']);
        $departmentsString = ListEditor::getStringFromList($departmentsRS, 'name');

        $emailTemplates = new EmailTemplates($this->_siteID);
        $statusChangeTemplateRS = $emailTemplates->getByTag(
            'EMAIL_TEMPLATE_OWNERSHIPASSIGNCONTACT'
        );

        if (!isset($statusChangeTemplateRS['disabled']) || $statusChangeTemplateRS['disabled'] == 1)
        {
            $emailTemplateDisabled = true;
        }
        else
        {
            $emailTemplateDisabled = false;
        }

        $reportsToRS = $contacts->getAll(-1, $data['companyID']);

        if ($this->getUserAccessLevel('contacts.emailContact') == ACCESS_LEVEL_DEMO)
        {
            $canEmail = false;
        }
        else
        {
            $canEmail = true;
        }

        $companies = new Companies($this->_siteID);
        $defaultCompanyID = $companies->getDefaultCompany();
        if ($defaultCompanyID !== false)
        {
            $defaultCompanyRS = $companies->get($defaultCompanyID);
        }
        else
        {
            $defaultCompanyRS = array();
        }

        if (!eval(Hooks::get('CONTACTS_EDIT'))) return;

        $this->_template->assign('defaultCompanyID', $defaultCompanyID);
        $this->_template->assign('defaultCompanyRS', $defaultCompanyRS);
        $this->_template->assign('canEmail', $canEmail);
        $this->_template->assign('emailTemplateDisabled', $emailTemplateDisabled);
        $this->_template->assign('active', $this);
        $this->_template->assign('data', $data);
        $this->_template->assign('companiesRS', $companiesRS);
        $this->_template->assign('extraFieldRS', $extraFieldRS);
        $this->_template->assign('departmentsRS', $departmentsRS);
        $this->_template->assign('departmentsString', $departmentsString);
        $this->_template->assign('usersRS', $usersRS);
        $this->_template->assign('reportsToRS', $reportsToRS);
        $this->_template->assign('contactID', $contactID);
        $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());
        $this->_template->display('./modules/contacts/Edit.tpl');
    }

    /*
     * Called by handleRequest() to process saving / submitting the edit page.
     */
    private function onEdit()
    {
        /* Bail out if we don't have a valid contact ID. */
        if (!$this->isRequiredIDValid('contactID', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid contact ID.');
        }

        /* Bail out if we don't have a valid company ID. */
        if (!$this->isRequiredIDValid('companyID', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid company ID.');
        }

        /* Bail out if we don't have a valid owner user ID. */
        if (!$this->isOptionalIDValid('owner', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid owner user ID.');
        }

        $contactID  = $_POST['contactID'];
        $companyID  = $_POST['companyID'];
        $owner      = $_POST['owner'];

        $formattedPhoneWork = StringUtility::extractPhoneNumber(
            $this->getTrimmedInput('phoneWork', $_POST)
        );
        if (!empty($formattedPhoneWork))
        {
            $phoneWork = $formattedPhoneWork;
        }
        else
        {
            $phoneWork = $this->getTrimmedInput('phoneWork', $_POST);
        }

        $formattedPhoneCell = StringUtility::extractPhoneNumber(
            $this->getTrimmedInput('phoneCell', $_POST)
        );
        if (!empty($formattedPhoneCell))
        {
            $phoneCell = $formattedPhoneCell;
        }
        else
        {
            $phoneCell = $this->getTrimmedInput('phoneCell', $_POST);
        }

        $formattedPhoneOther = StringUtility::extractPhoneNumber(
            $this->getTrimmedInput('phoneOther', $_POST)
        );
        if (!empty($formattedPhoneOther))
        {
            $phoneOther = $formattedPhoneOther;
        }
        else
        {
            $phoneOther = $this->getTrimmedInput('phoneOther', $_POST);
        }

        $contacts = new Contacts($this->_siteID);

        if ($this->isChecked('ownershipChange', $_POST) && $owner > 0)
        {
            $contactDetails = $contacts->get($contactID);

            $users = new Users($this->_siteID);
            $ownerDetails = $users->get($owner);

            if (!empty($ownerDetails))
            {
                $emailAddress = $ownerDetails['email'];

                /* Get the change status email template. */
                $emailTemplates = new EmailTemplates($this->_siteID);
                $statusChangeTemplateRS = $emailTemplates->getByTag(
                    'EMAIL_TEMPLATE_OWNERSHIPASSIGNCONTACT'
                );

                if (empty($statusChangeTemplateRS) ||
                    empty($statusChangeTemplateRS['textReplaced']))
                {
                    $statusChangeTemplate = '';
                }
                else
                {
                    $statusChangeTemplate = $statusChangeTemplateRS['textReplaced'];
                }
                /* Replace e-mail template variables. */
                $stringsToFind = array(
                    '%CONTOWNER%',
                    '%CONTFIRSTNAME%',
                    '%CONTFULLNAME%',
                    '%CONTCLIENTNAME%',
                    '%CONTCATSURL%'
                );
                $replacementStrings = array(
                    $ownerDetails['fullName'],
                    $contactDetails['firstName'],
                    $contactDetails['firstName'] . ' ' . $contactDetails['lastName'],
                    $contactDetails['companyName'],
                    '<a href="http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) . '?m=contacts&amp;a=show&amp;contactID=' . $contactID . '">'.
                        'http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) . '?m=contacts&amp;a=show&amp;contactID=' . $contactID . '</a>'
                );
                $statusChangeTemplate = str_replace(
                    $stringsToFind,
                    $replacementStrings,
                    $statusChangeTemplate
                );

                $email = $statusChangeTemplate;
            }
            else
            {
                $email = '';
                $emailAddress = '';
            }
        }
        else
        {
            $email = '';
            $emailAddress = '';
        }

        $firstName  = $this->getTrimmedInput('firstName', $_POST);
        $lastName   = $this->getTrimmedInput('lastName', $_POST);
        $title      = $this->getTrimmedInput('title', $_POST);
        $department = $this->getTrimmedInput('department', $_POST);
        $reportsTo  = $this->getTrimmedInput('reportsTo', $_POST);
        $email1     = $this->getTrimmedInput('email1', $_POST);
        $email2     = $this->getTrimmedInput('email2', $_POST);
        $address    = $this->getTrimmedInput('address', $_POST);
        $city       = $this->getTrimmedInput('city', $_POST);
        $state      = $this->getTrimmedInput('state', $_POST);
        $zip        = $this->getTrimmedInput('zip', $_POST);
        $notes      = $this->getTrimmedInput('notes', $_POST);

        $isHot = $this->isChecked('isHot', $_POST);
        $leftCompany = $this->isChecked('leftCompany', $_POST);

        /* Departments list editor. */
        $departmentsCSV = $this->getTrimmedInput('departmentsCSV', $_POST);

        /* Bail out if any of the required fields are empty. */
        if (empty($firstName) || empty($lastName) || empty($title))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
        }

        if (!eval(Hooks::get('CONTACTS_ON_EDIT_PRE'))) return;

        /* Update departments. */
        $companies = new Companies($this->_siteID);
        $departments = $companies->getDepartments($companyID);
        $departmentsDifferences = ListEditor::getDifferencesFromList(
            $departments, 'name', 'departmentID', $departmentsCSV
        );
        $companies->updateDepartments($companyID, $departmentsDifferences);

        if (!$contacts->update($contactID, $companyID, $firstName, $lastName,
            $title, $department, $reportsTo, $email1, $email2, $phoneWork, $phoneCell,
            $phoneOther, $address, $city, $state, $zip, $isHot,
            $leftCompany, $notes, $owner, $email, $emailAddress))
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to update contact.');
        }

        /* Update extra fields. */
        $contacts->extraFields->setValuesOnEdit($contactID);

        if (!eval(Hooks::get('CONTACTS_ON_EDIT_POST'))) return;

        CATSUtility::transferRelativeURI(
            'm=contacts&a=show&contactID=' . $contactID
        );
    }

    /*
     * Called by handleRequest() to process deleting a contact.
     */
    private function onDelete()
    {

        /* Bail out if we don't have a valid contact ID. */
        if (!$this->isRequiredIDValid('contactID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid contact ID.');
        }

        $contactID = $_GET['contactID'];

        if (!eval(Hooks::get('CONTACTS_DELETE_PRE'))) return;

        $contacts = new Contacts($this->_siteID);
        $contacts->delete($contactID);

        /* Delete the MRU entry if present. */
        $_SESSION['CATS']->getMRU()->removeEntry(
            DATA_ITEM_CONTACT, $contactID
        );

        if (!eval(Hooks::get('CONTACTS_DELETE_POST'))) return;

        CATSUtility::transferRelativeURI('m=contacts&a=listByView');
    }

    /*
     * Called by handleRequest() to process loading the search page.
     */
    private function search()
    {
        $savedSearches = new SavedSearches($this->_siteID);
        $savedSearchRS = $savedSearches->get(DATA_ITEM_CONTACT);

        if (!eval(Hooks::get('CONTACTS_SEARCH'))) return;

        $this->_template->assign('wildCardString', '');
        $this->_template->assign('savedSearchRS', $savedSearchRS);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Search Contacts');
        $this->_template->assign('isResultsMode', false);
        $this->_template->assign('wildCardContactName', '');
        $this->_template->assign('wildCardCompanyName', '');
        $this->_template->assign('wildCardContactTitle', '');
        $this->_template->assign('mode', '');
        $this->_template->display('./modules/contacts/Search.tpl');
    }

    /*
     * Called by handleRequest() to process displaying the search results.
     */
    private function onSearch()
    {
        $wildCardContactName = '';
        $wildCardCompanyName = '';
        $wildCardContactTitle = '';

        /* Bail out to prevent an error if the GET string doesn't even contain
         * a field named 'wildCardString' at all.
         */
        if (!isset($_GET['wildCardString']))
        {
            CommonErrors::fatal(COMMONERROR_WILDCARDSTRING, $this, 'No wild card string specified.');
        }

        $query = trim($_GET['wildCardString']);

        /* Set up sorting. */
        if ($this->isRequiredIDValid('page', $_GET))
        {
            $currentPage = $_GET['page'];
        }
        else
        {
            $currentPage = 1;
        }

        $searchPager = new SearchPager(
            CANDIDATES_PER_PAGE, $currentPage, $this->_siteID, $_GET
        );

        if ($searchPager->isSortByValid('sortBy', $_GET))
        {
            $sortBy = $_GET['sortBy'];
        }
        else
        {
            $sortBy = 'lastName';
        }

        if ($searchPager->isSortDirectionValid('sortDirection', $_GET))
        {
            $sortDirection = $_GET['sortDirection'];
        }
        else
        {
            $sortDirection = 'ASC';
        }

        $baseURL = CATSUtility::getFilteredGET(
            array('sortBy', 'sortDirection', 'page'), '&amp;'
        );
        $searchPager->setSortByParameters($baseURL, $sortBy, $sortDirection);

        /* Get our current searching mode. */
        $mode = $this->getTrimmedInput('mode', $_GET);

        /* Execute the search. */
        $search = new ContactsSearch($this->_siteID);
        switch ($mode)
        {
            case 'searchByFullName':
                $wildCardContactName = $query;
                $rs = $search->byFullName($query, $sortBy, $sortDirection);
                break;

            case 'searchByCompanyName':
                $wildCardCompanyName = $query;
                $rs = $search->byCompanyName($query, $sortBy, $sortDirection);
                break;

            case 'searchByTitle':
                $wildCardContactTitle = $query;
                $rs = $search->byTitle($query, $sortBy, $sortDirection);
                break;

            default:
                CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid search mode.');
                break;
        }

        foreach ($rs as $rowIndex => $row)
        {
            if ($row['isHotContact'] == 1)
            {
                $rs[$rowIndex]['linkClassContact'] = 'jobLinkHot';
            }
            else
            {
                $rs[$rowIndex]['linkClassContact'] = 'jobLinkCold';
            }

            if ($row['leftCompany'] == 1)
            {
                 $rs[$rowIndex]['linkClassCompany'] = 'jobLinkDead';
            }
            else if ($row['isHotCompany'] == 1)
            {
                $rs[$rowIndex]['linkClassCompany'] = 'jobLinkHot';
            }
            else
            {
                $rs[$rowIndex]['linkClassCompany'] = 'jobLinkCold';
            }

            if (!empty($row['ownerFirstName']))
            {
                $rs[$rowIndex]['ownerAbbrName'] = StringUtility::makeInitialName(
                    $row['ownerFirstName'],
                    $row['ownerLastName'],
                    false,
                    LAST_NAME_MAXLEN
                );
            }
            else
            {
                $rs[$rowIndex]['ownerAbbrName'] = 'None';
            }
        }


        $contactIDs = implode(',', ResultSetUtility::getColumnValues($rs, 'contactID'));
        $exportForm = ExportUtility::getForm(
            DATA_ITEM_CONTACT, $contactIDs, 40, 15
        );

        /* Save the search. */
        $savedSearches = new SavedSearches($this->_siteID);
        $savedSearches->add(
            DATA_ITEM_CONTACT,
            $query,
            $_SERVER['REQUEST_URI'],
            false
        );
        $savedSearchRS = $savedSearches->get(DATA_ITEM_CONTACT);

        $query = urlencode(htmlspecialchars($query));

        if (!eval(Hooks::get('CONTACTS_ON_SEARCH'))) return;

        $this->_template->assign('savedSearchRS', $savedSearchRS);
        $this->_template->assign('active', $this);
        $this->_template->assign('pager', $searchPager);
        $this->_template->assign('exportForm', $exportForm);
        $this->_template->assign('subActive', 'Search Contacts');
        $this->_template->assign('rs', $rs);
        $this->_template->assign('isResultsMode', true);
        $this->_template->assign('wildCardString', $query);
        $this->_template->assign('wildCardContactName', $wildCardContactName);
        $this->_template->assign('wildCardCompanyName', $wildCardCompanyName);
        $this->_template->assign('wildCardContactTitle', $wildCardContactTitle);
        $this->_template->assign('mode', $mode);
        $this->_template->display('./modules/contacts/Search.tpl');
    }

    /*
     * Called by handleRequest() to process loading the cold call list.
     */
    private function showColdCallList()
    {
        $contacts = new Contacts($this->_siteID);

        $rs = $contacts->getColdCallList();

        if (!eval(Hooks::get('CONTACTS_COLD_CALL_LIST'))) return;
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Cold Call List');
        $this->_template->assign('rs', $rs);
        $this->_template->display('./modules/contacts/ColdCallList.tpl');
    }

    //TODO: Document me.
    private function addActivityScheduleEvent()
    {
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('contactID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid contact ID.');
        }

        $contactID = $_GET['contactID'];

        $contacts = new Contacts($this->_siteID);
        $contactData = $contacts->get($contactID);

        $regardingRS = $contacts->getJobOrdersArray($contactID);

        $calendar = new Calendar($this->_siteID);
        $calendarEventTypes = $calendar->getAllEventTypes();

        /* Are we in "Only Schedule Event" mode? */
        $onlyScheduleEvent = $this->isChecked('onlyScheduleEvent', $_GET);

        if (!eval(Hooks::get('CONTACTS_ADD_ACTIVITY_SCHEDULE_EVENT'))) return;

        if (SystemUtility::isSchedulerEnabled() && !$_SESSION['CATS']->isDemo())
        {
            $allowEventReminders = true;
        }
        else
        {
            $allowEventReminders = false;
        }

        $this->_template->assign('contactID', $contactID);
        $this->_template->assign('regardingRS', $regardingRS);
        $this->_template->assign('allowEventReminders', $allowEventReminders);
        $this->_template->assign('userEmail', $_SESSION['CATS']->getEmail());
        $this->_template->assign('onlyScheduleEvent', $onlyScheduleEvent);
        $this->_template->assign('calendarEventTypes', $calendarEventTypes);
        $this->_template->assign('isFinishedMode', false);
        $this->_template->display(
            './modules/contacts/AddActivityScheduleEventModal.tpl'
        );
    }

    //TODO: Document me.
    private function onAddActivityScheduleEvent()
    {
        /* Bail out if we don't have a valid regardingjob order ID. */
        if (!$this->isOptionalIDValid('regardingID', $_POST))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        $regardingID = $_POST['regardingID'];

        $this->_addActivityScheduleEvent($regardingID);
    }

    /*
     * Called by handleRequest() to process downloading of a contact's vCard.
     *
     * Example vCard output in doc/NOTES.
     */
    private function downloadVCard()
    {
        /* Bail out if we don't have a valid contact ID. */
        if (!$this->isRequiredIDValid('contactID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid contact ID.');
        }

        $contactID = $_GET['contactID'];

        $contacts = new Contacts($this->_siteID);
        $contact = $contacts->get($contactID);

        $companies = new Companies($this->_siteID);
        $company = $companies->get($contact['companyID']);

        /* Bail out if we got an empty result set. */
        if (empty($contact))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'The specified contact ID could not be found.');
        }

        /* Create a new vCard. */
        $vCard = new VCard();

        $vCard->setName($contact['lastName'], $contact['firstName']);

        if (!empty($contact['phoneWork']))
        {
            $vCard->setPhoneNumber($contact['phoneWork'], 'PREF;WORK;VOICE');
        }

        if (!empty($contact['phoneCell']))
        {
            $vCard->setPhoneNumber($contact['phoneCell'], 'CELL;VOICE');
        }

        /* FIXME: Add fax to contacts and use setPhoneNumber('WORK;FAX') here */

        $addressLines = explode("\n", $contact['address']);

        $address1 = trim($addressLines[0]);
        if (isset($addressLines[1]))
        {
            $address2 = trim($addressLines[1]);
        }
        else
        {
            $address2 = '';
        }

        $vCard->setAddress(
            $address1, $address2, $contact['city'],
            $contact['state'], $contact['zip']
        );

        if (!empty($contact['email1']))
        {
            $vCard->setEmail($contact['email1']);
        }

        if (!empty($company['url']))
        {
            $vCard->setURL($company['url']);
        }

        $vCard->setTitle($contact['title']);
        $vCard->setOrganization($company['name']);

        if (!eval(Hooks::get('CONTACTS_GET_VCARD'))) return;

        $vCard->printVCardWithHeaders();
    }


    /**
     * Formats SQL result set for display. This is factored out for code
     * clarity.
     *
     * @param array result set from listByView()
     * @return array formatted result set
     */
    private function _formatListByViewResults($resultSet)
    {
        if (empty($resultSet))
        {
            return $resultSet;
        }

        foreach ($resultSet as $rowIndex => $row)
        {
            if (!empty($resultSet[$rowIndex]['ownerFirstName']))
            {
                $resultSet[$rowIndex]['ownerAbbrName'] = StringUtility::makeInitialName(
                    $resultSet[$rowIndex]['ownerFirstName'],
                    $resultSet[$rowIndex]['ownerLastName'],
                    false,
                    LAST_NAME_MAXLEN
                );
            }
            else
            {
                $resultSet[$rowIndex]['ownerAbbrName'] = 'None';
            }

            /* Hot contacts [can] have different title styles than normal
             * companies.
             */
            if ($resultSet[$rowIndex]['isHotContact'] == 1)
            {
                $resultSet[$rowIndex]['linkClassContact'] = 'jobLinkHot';
            }
            else
            {
                $resultSet[$rowIndex]['linkClassContact'] = 'jobLinkCold';
            }

           /* Strikethrough on no longer associated companies takes priority
            * over hot companies.
            */
            if ($resultSet[$rowIndex]['leftCompany'] == 1)
            {
                $resultSet[$rowIndex]['linkClassCompany'] = 'jobLinkDead';
            }
            else if ($resultSet[$rowIndex]['isHotCompany'] == 1)
            {
                $resultSet[$rowIndex]['linkClassCompany'] = 'jobLinkHot';
            }
            else
            {
                $resultSet[$rowIndex]['linkClassCompany'] = 'jobLinkCold';
            }

            /* Truncate Company Name column */
            if (strlen($resultSet[$rowIndex]['companyName']) > self::TRUNCATE_CLIENT_NAME)
            {
                $resultSet[$rowIndex]['companyName'] = substr(
                    $resultSet[$rowIndex]['companyName'], 0, self::TRUNCATE_CLIENT_NAME
                ) . "...";
            }

            /* Truncate Title column */
            if (strlen($resultSet[$rowIndex]['title']) > self::TRUNCATE_TITLE)
            {
                $resultSet[$rowIndex]['title'] = substr(
                    $resultSet[$rowIndex]['title'], 0, self::TRUNCATE_TITLE
                ) . "...";
            }
        }

        if (!eval(Hooks::get('CONTACTS_FORMAT_LIST_BY_VIEW'))) return;

        return $resultSet;
    }

    /**
     * Processes an Add Activity / Schedule Event form and displays
     * contacts/AddActivityScheduleEventModal.tpl. This is factored out
     * for code clarity.
     *
     * @param boolean from joborders module perspective
     * @param integer "regarding" job order ID or -1
     * @param string module directory
     * @return void
     */
    private function _addActivityScheduleEvent($regardingID, $directoryOverride = '')
    {
        /* Module directory override for fatal() calls. */
        if ($directoryOverride != '')
        {
            $moduleDirectory = $directoryOverride;
        }
        else
        {
            $moduleDirectory = $this->_moduleDirectory;
        }

        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('contactID', $_POST))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid contact ID.');
        }

        $contactID = $_POST['contactID'];

        //if (!eval(Hooks::get('CONTACT_ON_ADD_ACTIVITY_SCHEDULE_EVENT_PRE'))) return;

        if ($this->isChecked('addActivity', $_POST))
        {
            /* Bail out if we don't have a valid job order ID. */
            if (!$this->isOptionalIDValid('activityTypeID', $_POST))
            {
                CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid activity type ID.');
            }

            $activityTypeID = $_POST['activityTypeID'];

            $activityNote = $this->getTrimmedInput('activityNote', $_POST);

            $activityNote = htmlspecialchars($activityNote);

            /* Add the activity entry. */
            $activityEntries = new ActivityEntries($this->_siteID);
            $activityID = $activityEntries->add(
                $contactID,
                DATA_ITEM_CONTACT,
                $activityTypeID,
                $activityNote,
                $this->_userID,
                $regardingID
            );
            $activityTypes = $activityEntries->getTypes();
            $activityTypeDescription = ResultSetUtility::getColumnValueByIDValue(
                $activityTypes, 'typeID', $activityTypeID, 'type'
            );

            $activityAdded = true;
        }
        else
        {
            $activityAdded = false;
            $activityNote = '';
            $activityTypeDescription = '';
        }

        if ($this->isChecked('scheduleEvent', $_POST))
        {
            /* Bail out if we received an invalid date. */
            $trimmedDate = $this->getTrimmedInput('dateAdd', $_POST);
            if (empty($trimmedDate) ||
                !DateUtility::validate('-', $trimmedDate, DATE_FORMAT_MMDDYY))
            {
                CommonErrors::fatalModal(COMMONERROR_MISSINGFIELDS, $this, 'Invalid date.');
            }

            /* Bail out if we don't have a valid event type. */
            if (!$this->isRequiredIDValid('eventTypeID', $_POST))
            {
                CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid event type ID.');
            }

            /* Bail out if we don't have a valid time format ID. */
            if (!isset($_POST['allDay']) ||
                ($_POST['allDay'] != '0' && $_POST['allDay'] != '1'))
            {
                CommonErrors::fatalModal(COMMONERROR_MISSINGFIELDS, $this, 'Invalid time format ID.');
            }

            $eventTypeID = $_POST['eventTypeID'];

            if ($_POST['allDay'] == 1)
            {
                $allDay = true;
            }
            else
            {
                $allDay = false;
            }

            $publicEntry = $this->isChecked('publicEntry', $_POST);

            $reminderEnabled = $this->isChecked('reminderToggle', $_POST);
            $reminderEmail = $this->getTrimmedInput('sendEmail', $_POST);
            $reminderTime  = $this->getTrimmedInput('reminderTime', $_POST);

            $duration = -1;

            /* Is this a scheduled event or an all day event? */
            if ($allDay)
            {
                $date = DateUtility::convert(
                    '-', $trimmedDate, DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD
                );

                $hour = 12;
                $minute = 0;
                $meridiem = 'AM';
            }
            else
            {
                /* Bail out if we don't have a valid hour. */
                if (!isset($_POST['hour']))
                {
                    CommonErrors::fatalModal(COMMONERROR_MISSINGFIELDS, $this, 'Invalid hour.');
                }

                /* Bail out if we don't have a valid minute. */
                if (!isset($_POST['minute']))
                {
                    CommonErrors::fatalModal(COMMONERROR_MISSINGFIELDS, $this, 'Invalid minute.');
                }

                /* Bail out if we don't have a valid meridiem value. */
                if (!isset($_POST['meridiem']) ||
                    ($_POST['meridiem'] != 'AM' && $_POST['meridiem'] != 'PM'))
                {
                    CommonErrors::fatalModal(COMMONERROR_MISSINGFIELDS, $this, 'Invalid meridiem value.');
                }

                $hour     = $_POST['hour'];
                $minute   = $_POST['minute'];
                $meridiem = $_POST['meridiem'];

                /* Convert formatted time to UNIX timestamp. */
                $time = strtotime(
                    sprintf('%s:%s %s', $hour, $minute, $meridiem)
                );

                /* Create MySQL date string w/ 24hr time (YYYY-MM-DD HH:MM:SS). */
                $date = sprintf(
                    '%s %s',
                    DateUtility::convert(
                        '-',
                        $trimmedDate,
                        DATE_FORMAT_MMDDYY,
                        DATE_FORMAT_YYYYMMDD
                    ),
                    date('H:i:00', $time)
                );
            }

            $description = $this->getTrimmedInput('description', $_POST);
            $title       = $this->getTrimmedInput('title', $_POST);

            /* Bail out if any of the required fields are empty. */
            if (empty($title))
            {
                CommonErrors::fatalModal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
            }

            if ($regardingID > 0)
            {
                $eventJobOrderID = $regardingID;
            }
            else
            {
                $eventJobOrderID = -1;
            }

            $calendar = new Calendar($this->_siteID);
            $eventID = $calendar->addEvent(
                $eventTypeID, $date, $description, $allDay, $this->_userID,
                $contactID, DATA_ITEM_CONTACT, $eventJobOrderID, $title,
                $duration, $reminderEnabled, $reminderEmail, $reminderTime,
                $publicEntry, $_SESSION['CATS']->getTimeZoneOffset()
            );

            if ($eventID <= 0)
            {
                CommonErrors::fatalModal(COMMONERROR_RECORDERROR, $this, 'Failed to add calendar event.');
            }

            /* Extract the date parts from the specified date. */
            $parsedDate = strtotime($date);
            $formattedDate = date('l, F jS, Y', $parsedDate);

            $calendar = new Calendar($this->_siteID);
            $calendarEventTypes = $calendar->getAllEventTypes();

            $eventTypeDescription = ResultSetUtility::getColumnValueByIDValue(
                $calendarEventTypes, 'typeID', $eventTypeID, 'description'
            );

            $eventHTML = sprintf(
                '<p>An event of type <span class="bold">%s</span> has been scheduled on <span class="bold">%s</span>.</p>',
                htmlspecialchars($eventTypeDescription),
                htmlspecialchars($formattedDate)

            );
            $eventScheduled = true;
        }
        else
        {
            $eventHTML = '<p>No event has been scheduled.</p>';
            $eventScheduled = false;
        }

        if (isset($_GET['onlyScheduleEvent']))
        {
            $onlyScheduleEvent = true;
        }
        else
        {
            $onlyScheduleEvent = false;
        }

        if (!$activityAdded && !$eventScheduled)
        {
            $changesMade = false;
        }
        else
        {
            $changesMade = true;
        }

        if (!eval(Hooks::get('CANDIDATE_ON_ADD_ACTIVITY_CHANGE_STATUS_POST'))) return;

        $this->_template->assign('contactID', $contactID);
        $this->_template->assign('regardingID', $regardingID);
        $this->_template->assign('activityAdded', $activityAdded);
        $this->_template->assign('activityDescription', $activityNote);
        $this->_template->assign('activityType', $activityTypeDescription);
        $this->_template->assign('eventScheduled', $eventScheduled);
        $this->_template->assign('onlyScheduleEvent', $onlyScheduleEvent);
        $this->_template->assign('eventHTML', $eventHTML);
        $this->_template->assign('changesMade', $changesMade);
        $this->_template->assign('isFinishedMode', true);
        $this->_template->display(
            './modules/contacts/AddActivityScheduleEventModal.tpl'
        );
    }
}

?>
