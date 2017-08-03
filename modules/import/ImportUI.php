<?php
/*
 * CATS
 * Import Module
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
 * $Id: ImportUI.php 3833 2007-12-12 18:18:09Z brian $
 */

include_once('./lib/Statistics.php');
include_once('./lib/StringUtility.php');
include_once('./modules/import/Import.php');
include_once('./lib/Companies.php');
include_once('./lib/Contacts.php');
include_once('./lib/Candidates.php');
include_once('./lib/DatabaseSearch.php');
include_once('./lib/FileUtility.php');
include_once('./lib/ExtraFields.php');
include_once('./lib/Attachments.php');
include_once('./lib/ParseUtility.php');
include_once('./lib/Import.php');


class ImportUI extends UserInterface
{
    const MAX_ERRORS = 100;


    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = true;
        $this->_moduleDirectory = 'import';
        $this->_moduleName = 'import';
        $this->_subTabs = array();
    }


    public function handleRequest()
    {
        $action = $this->getAction();
        switch ($action)
        {
            case 'revert':
                $this->revert();
                break;

            case 'viewerrors':
                $this->viewErrors();
                break;

            case 'viewpending':
                $this->viewPending();
                break;

            case 'importSelectType':
                $this->importSelectType();
                break;

            case 'importUploadFile':
                $this->importUploadFile();
                break;

            case 'whatIsBulkResumes':
                $this->whatIsBulkResumes();
                break;

            case 'showMassImport':
                $this->showMassImport();
                break;

            case 'massImport':
                $this->massImport();
                break;

            case 'massImportDocument':
                $this->massImportDocument();
                break;

            case 'massImportEdit':
                $this->massImportEdit();
                break;

            case 'importBulkResumes':
                $this->importBulkResumes();
                break;

            case 'deleteBulkResumes':
                $this->deleteBulkResumes();
                break;

            case 'import':
            default:
                if ($this->isPostBack())
                {
                    $this->onImport();
                }
                else
                {
                    $this->import();
                }
                break;
        }
    }

   /*
    * Called by handleRequest() to revert an import.
    */
    private function revert()
    {
        if (!$this->isRequiredIDValid('importID', $_GET))
        {
            $this->import();
            return;
        }

        $importID = $_GET['importID'];

        $import = new Import($this->_siteID);
        $tableName = $import->get($importID);
        if (!$tableName)
        {
            $this->import();
            return;
        }
        $tableName = $import->revert(
            $tableName['moduleName'],
            $importID
        );
        $tableName = $import->delete($importID);

        if (!eval(Hooks::get('IMPORT_REVERT'))) return;

        $message = 'The revert was successful.';

        $this->_template->assign('successMessage', $message);
        $this->viewPending();
        return;
    }


   /*
    * Called by handleRequest() to view the errors of a pervious import.
    */
    private function viewErrors()
    {
        $importID = $_GET['importID'];

        if ($importID <= 0 || $importID == '')
        {
            $this->import();
            return;
        }

        $import = new Import($this->_siteID);
        $importData = $import->get($importID);

        if (!eval(Hooks::get('IMPORT_VIEW_ERRORS'))) return;

        if (isset($importData['importErrors']))
        {
            $this->_template->assign('importErrors', $importData['importErrors']);
        }
        else
        {
            $this->_template->assign('importErrors', '');
        }

        $this->_template->assign('importID', $importID);
        $this->viewPending();
        return;
    }

   /*
    * Called by handleRequest() and viewErrors() to view pending imports and to display relavent information.
    */
    private function viewPending()
    {
        $import = new Import($this->_siteID);
        $data = $import->getAll();

        if (count($data) == 0)
        {
            $this->import();
        }
        else
        {
            if (!eval(Hooks::get('IMPORT_VIEW_PENDING'))) return;

            $this->_template->assign('data', $data);
            $this->_template->assign('active', $this);
            $this->_template->display('./modules/import/ImportRecent.tpl');
        }

        return;
    }

   /*
    * Sets a variety of constants in the instantiated object.
    */
    private function setImportTypes()
    {
        $this->candidatesTypes = array(
            'Full Name',        'name',
            'First Name',       'first_name',
            'Last Name',        'last_name',
            'Address',          'address',
            'City',             'city',
            'State',            'state',
            'Zip',              'zip',
            'Home Phone',       'phone_home',
            'Cell Phone',       'phone_cell',
            'Work Phone',       'phone_work',
            'Notes',            'notes',
            'Current Employer', 'current_employer',
            'Email',            'email1',
            'Email 2',          'email2',
            'Web Site',         'web_site',
            'Key Skills',       'key_skills'
        );
        $this->contactsTypes = array(
            'Company',      'company_id',
            'Full Name',   'name',
            'First Name',  'first_name',
            'Last Name',   'last_name',
            'Address',     'address',
            'City',        'city',
            'State',       'state',
            'Zip',         'zip',
            'Cell Phone',  'phone_cell',
            'Work Phone',  'phone_work',
            'Other Phone', 'phone_other',
            'Notes',       'notes',
            'Email',       'email1',
            'Email 2',     'email2',
            'Title',       'title'
        );
        $this->companiesTypes = array(
            'Name',             'name',
            'Billing Contact',  'billing_contact',
            'Address',          'address',
            'City',             'city',
            'State',            'state',
            'Zip',              'zip',
            'Phone',            'phone1',
            'Phone 2',          'phone2',
            'URL',              'url',
            'Key Technologies', 'key_technologies',
            'Notes',            'notes',
            'Fax Number',       'fax_number'
        );

        if (!eval(Hooks::get('IMPORT_TYPES_2'))) return;

        $companies = new Companies($this->_siteID);
        $candidates = new Candidates($this->_siteID);
        $contacts = new Contacts($this->_siteID);

        $rs = $companies->extraFields->getSettings();
        foreach ($rs as $data)
        {
            $this->companiesTypes[] = $data['fieldName'];
            $this->companiesTypes[] = '#' . $data['fieldName'];
        }

        $rs = $candidates->extraFields->getSettings();
        foreach ($rs as $data)
        {
            $this->candidatesTypes[] = $data['fieldName'];
            $this->candidatesTypes[] = '#' . $data['fieldName'];
        }

        $rs = $contacts->extraFields->getSettings();
        foreach ($rs as $data)
        {
            $this->contactsTypes[] = $data['fieldName'];
            $this->contactsTypes[] = '#' . $data['fieldName'];
        }
    }

   /*
    * First page (also used to display errors.)
    */
    private function import()
    {
        $import = new Import($this->_siteID);
        $data = $import->getAll();

        $attachments = new Attachments($this->_siteID);
        $bulk = $attachments->getBulkAttachmentsInfo();

        if (count($data) > 0)
        {
            $this->_template->assign('pendingCommits', true);
        }

        if (!eval(Hooks::get('IMPORT2_SHOW'))) return;

        $this->_template->assign('active', $this);
        $this->_template->assign('bulk', $bulk);
        $this->_template->display('./modules/import/Import1.tpl');
    }

   /*
    * Second page (upload a file, select file format).
    */
   private function importSelectType()
   {
       $typeOfImport = $this->getTrimmedInput('typeOfImport', $_REQUEST);

       if ($typeOfImport == '')
       {
           $this->import();
           return;
       }
       else if ($typeOfImport == 'resume')
       {
           // Start the new mass import/parser
           $this->massImport();
       }
       else
       {
           $this->_template->assign('active', $this);
           $this->_template->assign('typeOfImport', $typeOfImport);

           if (!eval(Hooks::get('IMPORT_UPLOAD'))) return;

           $this->_template->display('./modules/import/Import2.tpl');
       }
   }

   /*
    * 3rd page for CSV data (After uploading a file).  Sets environment to behave like old style import.
    */
   private function importUploadFile()
   {
       /* Change passed in settings to settings the old importer knows how to handle. */
       $_POST['dataType'] = 'Text File';
       $_POST['importInto'] = $this->getTrimmedInput('typeOfImport', $_POST);
       $_POST['delimitedType'] = $this->getTrimmedInput('typeOfFile', $_POST);

       $this->onImport();
   }

   /*
    * 3rd page for resume data. (After uploading a file).  Sets environment to behave like old style import.
    */
   private function importUploadResume()
   {
       $_POST['dataType'] = 'Resume';

       $this->onImport();
   }

   /*
    * Called by handleRequest() to process an import both on step #2 (choose
    * fields) and step #3 (process import).
    */
    private function onImport()
    {
        if ($this->getUserAccessLevel('import.import') < ACCESS_LEVEL_EDIT)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
        }

        if( ini_get('safe_mode') )
        {
			//don't do anything in safe mode
		}
		else
        {
            /* limit the execution time of import to 500 secs. */
            set_time_limit(500);
        }

        $this->setImportTypes();

        $dataType   = $this->getTrimmedInput('dataType', $_POST);
        $importInto = $this->getTrimmedInput('importInto', $_POST);

        if (empty($dataType))
        {
            $this->_template->assign('errorMessage', 'No data type was specified.');
            $this->importSelectType();
            return;
        }

        if (empty($importInto) && $dataType != 'Resume')
        {
            $this->_template->assign('errorMessage', 'No destination was specified.');
            $this->importSelectType();
            return;
        }

        /* If a file was submitted, then the user sent what colums he wanted to use already. */
        if (isset($_POST['fileName']))
        {
            if ($_SESSION['CATS']->isDemo())
            {
                CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Demo user can not import data.');
            }

            if (!eval(Hooks::get('IMPORT_ON_IMPORT_1'))) return;

            switch($dataType)
            {
                case 'Text File':
                    $this->onImportFieldsDelimited();
                    return;

                default:
                    $this->_template->assign(
                        'errorMessage',
                        'No 2nd parser has been included for the specified data type.'
                    );
                    $this->import();
                    return;
            }
        }

        /* Otherwise, parse the file... */

        if (!eval(Hooks::get('IMPORT_ON_IMPORT_2'))) return;

        if (!isset($_FILES['file']) || empty($_FILES['file']['name']))
        {
            $errorMessage = sprintf(
                'No file was uploaded.'
            );
            $this->_template->assign('errorMessage', $errorMessage);
            $this->importSelectType();
            return;
        }

        /* Get file metadata. */
        $originalFilename = $_FILES['file']['name'];
        $tempFilename     = $_FILES['file']['tmp_name'];
        $contentType      = $_FILES['file']['type'];
        $fileSize         = $_FILES['file']['size'];
        $fileUploadError  = $_FILES['file']['error'];

        /* Recover from magic quotes. Note that tmp_name doesn't appear to
         * get escaped, and stripslashes() on it breaks on Windows. - Will
         */
        if (get_magic_quotes_gpc())
        {
            $originalFilename = stripslashes($originalFilename);
            $contentType      = stripslashes($contentType);
        }

        if ($fileUploadError != UPLOAD_ERR_OK)
        {
            $this->_template->assign(
                'errorMessage', FileUtility::getErrorMessage($fileUploadError)
            );
            $this->importSelectType();
            return;
        }

        if ($fileSize <= 0)
        {
            $this->_template->assign(
                'errorMessage', 'File size is less than 1 byte.'
            );
            $this->importSelectType();
            return;
        }
        if (!is_dir(CATS_TEMP_DIR))
        {
            @mkdir(CATS_TEMP_DIR);
        }
        /* Make sure the attachments directory exists and create it if not. */
        if (!is_dir(CATS_TEMP_DIR))
        {
            $errorMessage = sprintf(
                'Directory \'%s\' does not exist and can\'t be created. CATS is not configured correctly.',
                CATS_TEMP_DIR
            );
            $this->_template->assign('errorMessage', $errorMessage);
            $this->importSelectType();
            return;
        }

        /* Make a blind attempt to recover from invalid permissions. */
        @chmod(CATS_TEMP_DIR, 0777);

        /* Make a random file name for the file. */
        if ($dataType != 'Resume')
        {
            $randomFile = FileUtility::makeRandomFilename($tempFilename) . '.tmp';
        }
        else
        {
            $randomFile = $originalFilename;
        }

        /* Build new path information for the file. */
        $newFileFullPath  = CATS_TEMP_DIR . '/' . $randomFile;

        if (!@copy($tempFilename, $newFileFullPath))
        {
            $errorMessage = sprintf(
                'Cannot copy temporary file from %s to %s.',
                $tempFilename,
                $newFileFullPath
            );
            $this->_template->assign('errorMessage', $errorMessage);
            $this->importSelectType();
            return;
        }

        /* Try to remove the temp file; if it fails it doesn't matter. */
        @unlink($tempFilename);

        /* Store the file ID as a valid file ID (so users can't inject other file ids to read
           files they shouldn't be reading. */
        $_SESSION['CATS']->validImportFileIDs[] = $randomFile;

        if (!eval(Hooks::get('IMPORT_ON_IMPORT_3'))) return;

        switch($dataType)
        {
            case 'Text File':
                $this->onImportDelimited($randomFile);
                break;

            default:
                $this->_template->assign(
                    'errorMessage',
                    'No parser exists for the specified data type.'
                );
                $this->importSelectType();
                break;
        }
    }

    /*
     * Called by onImport() to process an import for Step 2 (decide what
     * fields go where).
     */
    private function onImportDelimited($fileID)
    {
        $filePath = CATS_TEMP_DIR . '/'. $fileID;

        $dataContaining = $this->getTrimmedInput('delimitedType', $_POST);
        $importInto     = $this->getTrimmedInput('importInto', $_POST);
        $dataType       = $this->getTrimmedInput('dataType', $_POST);

        if ($dataType == 'ACT')
        {
            $dataType = 'Text File';
            $dataContaining = $this->getTrimmedInput('ACTType', $_POST);
        }

        /* Parse data */

        $theFile = fopen($filePath, 'r');
        if (!$theFile)
        {
            $this->_template->assign('errorMessage', 'Cannot open the copied file (Internal error).');
            $this->import();
            return;
        }

        if (!eval(Hooks::get('IMPORT_ON_IMPORT_DELIMITED_1'))) return;

        switch ($dataContaining)
        {
            case 'tab':
                $theFields = fgetcsv($theFile, null, "\t");
                break;

            case 'csv':
                $theFields = fgetcsv($theFile, null, ',', '"');
                break;

            default:
                $this->_template->assign(
                    'errorMessage', 'Cannot handle that data type.'
                );
                $this->import();
                return;
        }

        if (!eval(Hooks::get('IMPORT_ON_IMPORT_DELIMITED_2'))) return;

        switch ($importInto)
        {
            case 'Candidates':
                $types = $this->candidatesTypes;
                break;

            case 'Contacts':
                $types = $this->contactsTypes;
                $this->_template->assign('contactsUploadNotice', true);
                break;

            case 'Companies':
                $types = $this->companiesTypes;
                break;

            default:
                $this->_template->assign(
                    'errorMessage', 'Cannot handle that destination.'
                );
                $this->import();
                return;
        }

        /* Figure out what fields match already */

        $matchingFields = array();

        foreach ($theFields AS $theField)
        {
            for ($i = 0; $i < count($types); $i += 2)
            {
                $lField = trim(strtolower($theField));
                $lType  = strtolower($types[$i]);

                if ($lField == $lType ||
                    ($lField == 'company' && $lType == 'company') ||
                    ($lField == 'company' && $lType == 'name' &&
                    $importInto == 'Companies'))
                {
                    $matchingFields[] = $theField;
                }
            }
        }

        /* Get some sample data */
        $ArrayOfData = array();
        for ($i = 0; $i < 20; $i++)
        {
            if (feof($theFile))
            {
                continue;
            }

            if (!eval(Hooks::get('IMPORT_ON_IMPORT_DELIMITED_3'))) return;

            switch ($dataContaining)
            {
                case 'tab':
                    $someData = fgetcsv($theFile, null, "\t");
                    break;

                case 'csv':
                    $someData = fgetcsv($theFile, null, ",", '"');
                    break;

                default:
                    $this->_template->assign('errorMessage', 'Cannot handle that data type for sample data.');
                    $this->import();
                    return;
            }
            $ArrayOfData[] = $someData;
        }

        $highlightModule = strtolower($importInto);

        $isSA = ($this->getUserAccessLevel('import.import') >= ACCESS_LEVEL_SA);

        if (!eval(Hooks::get('IMPORT_ON_IMPORT_DELIMITED_4'))) return;

        $this->_template->assign('isSA', $isSA);
        $this->_template->assign('arrayOfData', $ArrayOfData);
        $this->_template->assign('isUploaded', true);
        $this->_template->assign('fileName', $fileID);
        $this->_template->assign('dataType', $dataType);
        $this->_template->assign('typeOfImport', $_REQUEST['typeOfImport']);
        $this->_template->assign('importInto', $importInto);
        $this->_template->assign('highlightModule', $highlightModule);
        $this->_template->assign('dataContaining', $dataContaining);
        $this->_template->assign('theFields', $theFields);
        $this->_template->assign('matchingFields', $matchingFields);
        $this->_template->assign('importTypes', $types);
        $this->_template->assign('active', $this);
        $this->_template->display('./modules/import/Import.tpl');
    }

    /*
     * Called by onImport() to physically insert the data into the
     * database (Step 3).
     */
    public function onImportFieldsDelimited()
    {
        if ($this->getUserAccessLevel('import.import') < ACCESS_LEVEL_EDIT)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
        }

        $filePath = CATS_TEMP_DIR . '/' . $_POST['fileName'];
        if (!is_file($filePath))
        {
            $this->_template->assign('errorMessage', 'Invalid filename. (Internal error)');
            $this->import();
        }

        $dataContaining = $this->getTrimmedInput('dataContaining', $_POST);
        $importInto     = $this->getTrimmedInput('importInto', $_POST);

        $importID = -1;
        $totalRows = 0;
        $totalImported = 0;
        $totalImportedCompany = 0;
        $errorHtml = '';

        $importErrors = array();

        /* Parse data. */

        $theFile = fopen($filePath, 'r');
        if (!$theFile)
        {
            $this->_template->assign('errorMessage', 'Cannot open the copied file (Internal error).');
            $this->import();
            return;
        }

        if (!eval(Hooks::get('IMPORT_ON_IMPORT_DELIMITED_5'))) return;

        switch ($dataContaining)
        {
            case 'tab':
                $theFields = fgetcsv($theFile, null, "\t");
                break;

            case 'csv':
                $theFields = fgetcsv($theFile, null, ",", '"');
                break;

            default:
                $this->_template->assign('errorMessage', 'Cannot handle that data type.');
                $this->import();
                return;
        }

        if (!eval(Hooks::get('IMPORT_ON_IMPORT_DELIMITED_6'))) return;

        /* Set up a new import record, and set table types. */
        $import = new Import($this->_siteID);
        switch ($importInto)
        {
            case 'Candidates':
                $types = $this->candidatesTypes;
                $importID = $import->add('candidate');
                break;

            case 'Companies':
                $types = $this->companiesTypes;
                $importID = $import->add('company');
                break;

            case 'Contacts':
                $types = $this->contactsTypes;
                $importID = $import->add('contact');
                break;

            default:
                $this->_template->assign(
                    'errorMessage', 'Cannot handle the specified destination.'
                );
                $this->import();
                return;
        }

        /* Get user preference for what do to with each field */
        foreach ($theFields AS $fieldID => $theField)
        {
            $theFieldPreference[$fieldID] = $_POST['importType' . $fieldID];
        }

        /* Build the sql and alien parameters for each new item, and execute. */
        while (!feof($theFile))
        {
            $totalRows++;
            // FIXME: This decision should be made outside the loop.

            if (!eval(Hooks::get('IMPORT_ON_IMPORT_DELIMITED_7'))) return;

            switch ($dataContaining)
            {
                case 'tab':
                    $theData = fgetcsv($theFile, null, "\t");
                    break;

                case 'csv':
                    $theData = fgetcsv($theFile, null, ',', '"');
                    break;

                default:
                    $this->_template->assign('errorMessage', 'Cannot read that data type.');
                    $this->import();
                    return;
            }

            $catsEntriesRows = array();
            $catsEntriesValuesNamed = array();
            $foreignEntries = array();

            /* Put the data where the user picked for it to go. */
            foreach ($theFieldPreference AS $fieldID => $theFieldPreferenceValue)
            {
                if (count($theData) <= $fieldID || trim($theData[$fieldID]) == '')
                {
                    continue;
                }

                if ($theFieldPreferenceValue == 'cats')
                {
                    if (substr($_POST['importIntoField' . $fieldID], 0, 1) == '#')
                    {
                        /* This is an extra field. */
                        $foreignEntries[substr($_POST['importIntoField' . $fieldID], 1)] = $theData[$fieldID];
                    }
                    else
                    {
                        $catsEntriesRows[] = $_POST['importIntoField' .$fieldID];
                        $catsEntriesValuesNamed[$_POST['importIntoField' . $fieldID]] = trim($theData[$fieldID]);
                    }
                }
                else if ($theFieldPreferenceValue == 'foreign' || $theFieldPreferenceValue == 'foreignAdded')
                {
                    /* Before we do this, ensure that we have permision and the field is in the database. */
                    if ($this->getUserAccessLevel('import.import') >= ACCESS_LEVEL_SA)
                    {
                        $import = new Import($this->_siteID);
                        if ($theFieldPreferenceValue == 'foreign')
                        {
                            if (!eval(Hooks::get('IMPORT_ON_IMPORT_DELIMITED_8'))) return;

                            switch ($importInto)
                            {
                                case 'Candidates':
                                    $import->addForeignSettingUnique(DATA_ITEM_CANDIDATE, $theFields[$fieldID], $importID);
                                    break;

                                case 'Contacts':
                                    $import->addForeignSettingUnique(DATA_ITEM_CONTACT, $theFields[$fieldID], $importID);
                                    break;

                                case 'Companies':
                                    $import->addForeignSettingUnique(DATA_ITEM_COMPANY, $theFields[$fieldID], $importID);
                                    break;

                                default:
                                    $this->_template->assign('errorMessage', 'Cannot handle that destination for new foreign entry setting.');
                                    $this->import();
                                    return;
                            }
                        }

                        $foreignEntries[$theFields[$fieldID]] = $theData[$fieldID];

                        /* Future entries will be set to add directly to the table without trying to make the entry. */
                        $theFieldPreference[$fieldID] = 'foreignAdded';
                    }
                }
            }

            $result = '';

            if (!eval(Hooks::get('IMPORT_ON_IMPORT_DELIMITED_9'))) return;

            /* Execute the add data command. */
            switch ($importInto)
            {
                case 'Candidates':
                    $result = $this->addToCandidates($catsEntriesRows, $catsEntriesValuesNamed, $foreignEntries, $importID);
                    break;

                case 'Contacts':
                    $result = $this->addToContacts($catsEntriesRows, $catsEntriesValuesNamed, $foreignEntries, $importID);
                    break;

                case 'Companies':
                    $result = $this->addToCompanies($catsEntriesRows, $catsEntriesValuesNamed, $foreignEntries, $importID);
                    break;

                default:
                    $this->_template->assign('errorMessage', 'Cannot handle that destination.');
                    $this->import();
                    return;
            }

            if ($result == '' || $result == 'newCompany')
            {
                /* Add data successful. */
                $totalImported++;
                if ($result == 'newCompany')
                {
                    $totalImportedCompany++;
                }
            }
            else if ($totalRows - $totalImported <= self::MAX_ERRORS) /* Errors <= MAX_ERRORS */
            {
                /* Add data failed, record the result */
                $errorHtml .= '<span id="errorPlus'.$totalRows.'"><a href="javascript:void(0);" onclick="showErrorId('.$totalRows.');">[+]</a></span>';
                $errorHtml .= '<span id="errorMinus'.$totalRows.'" style="display:none;"><a href="javascript:void(0);" onclick="hideErrorId('.$totalRows.');">[-]</a></span>';
                $errorHtml .= '&nbsp;Record # '.$totalRows.': '.$result.'<br />';
                $errorHtml .= '<span id="errorId'.$totalRows.'" style="display:none;">';
                foreach ($theFields AS $fieldID => $theField)
                {
                    if (count($theData) > $fieldID)
                    {
                        $errorHtml .= '<span class="bold">' . htmlspecialchars($theField) . ':</span> ' . htmlspecialchars($theData[$fieldID]) . '<br />';
                    }
                }
                $errorHtml .= '</span>';
            }
        }

        /* Put a header on the error output, then update the import record with our errors. */
        if ($totalRows - $totalImported <= self::MAX_ERRORS)
        {
            $errorHtml = '<span class="bold">' . ($totalRows - $totalImported) . ' errors:</span><br /><br />' . $errorHtml;
        }
        else
        {
            $errorHtml = '<span class="bold">First ' . self::MAX_ERRORS . ' errors (of ' . ($totalRows - $totalImported) . '):</span><br /><br />' . $errorHtml;
        }

        $import->updateErrors($importID, $errorHtml, $totalImported);

        /* Generate a response. */
        $message =  'The import was successful.  Of a total ' . $totalRows;
        $message .= ' rows of data, ' . $totalImported . ' were imported into ' . $importInto . '.';

        if ($totalImportedCompany > 0)
        {
            $message .= ' In addition, ' . $totalImportedCompany . ' companies were created.';
        }

        if ($totalImported != $totalRows)
        {
            $message .= ' The dropped rows either had bad data, or were missing required fields (at least 1 name field).<br /><br />';
        }

        $message .= 'You will have 1 week to review the import before the changes become permanent.<br /><br />';

        $message .= '<input type="button" onclick="document.location.href=\'';
        $message .= CATSUtility::getIndexName() . '?m=import&amp;a=revert&amp;importID=' . $importID . '\';" value="Revert Import" class="button">';

        if ($totalRows != $totalImported)
        {
            $message .= '<input type="button" onclick="document.location.href=\'';
            $message .= CATSUtility::getIndexName() . '?m=import&amp;a=viewerrors&amp;importID=' . $importID . '\';" value="View Import Errors" class="button">';
        }

        if (!eval(Hooks::get('IMPORT_ON_IMPORT_DELIMITED_10'))) return;

        /* Send off to the import template. */
        $this->_template->assign('successMessage', $message);
        $this->import(strtolower($importInto));
    }

   /*
    * Generic function to add a extra field to any foreign table.
    */
    private function addForeign($dataTable, $data, $assocID, $importID)
    {
        if (!eval(Hooks::get('IMPORT_ADD_FOREIGN'))) return;

        $import = new Import($this->_siteID);
        $import->addForeign($dataTable, $data, $assocID, $importID);
    }

   /*
    * Inserts a record into candidates.
    */
    private function addToCandidates($dataFields, $dataNamed, $dataForeign, $importID)
    {
        $dateAvailable = '01/01/0001';

        /* Bail out if any of the required fields are empty. */

        if (!empty($dataNamed['name']))
        {
            $nameArray = explode(' ', $dataNamed['name']);
            $dataNamed['first_name'] = $nameArray[0];
            $dataNamed['last_name'] = $nameArray[count($nameArray) - 1];
            unset($dataNamed['name']);
        }

        if (!isset($dataNamed['first_name']) &&
            !isset($dataNamed['last_name']) &&
            !isset($dataNamed['company_id']))
        {
            return 'Required fields (first name, last name) are missing.';
        }

        if (!eval(Hooks::get('IMPORT_ADD_CANDIDATE'))) return;

        $candidatesImport = new CandidatesImport($this->_siteID);
        $candidateID = $candidatesImport->add($dataNamed, $this->_userID, $importID);

        if ($candidateID <= 0)
        {
            return 'Failed to add candidate.';
        }

        $this->addForeign(DATA_ITEM_CANDIDATE, $dataForeign, $candidateID, $importID);

        if (!eval(Hooks::get('IMPORT_ADD_CANDIDATE_POST'))) return;

        return '';
    }

   /*
    * Inserts a record into Companies.
    */
    private function addToCompanies($dataFields, $dataNamed, $dataForeign, $importID)
    {
        $companiesImport = new CompaniesImport($this->_siteID);

        /* Bail out if any of the required fields are empty. */

        if (!isset($dataNamed['name']))
        {
            return 'Required fields (Company Name) are missing.';
        }

        /* check for duplicates */

        $cID = $companiesImport->companyByName($dataNamed['name']);
        if ($cID != -1)
        {
            return 'Duplicate entry.';
        }

        if (!eval(Hooks::get('IMPORT_ADD_CLIENT'))) return;

        $companyID = $companiesImport->add($dataNamed, $this->_userID, $importID);

        if ($companyID <= 0)
        {
            return 'Failed to add candidate.';
        }

        $this->addForeign(DATA_ITEM_COMPANY, $dataForeign, $companyID, $importID);

        if (!eval(Hooks::get('IMPORT_ADD_CLIENT_POST'))) return;

        return '';
    }

   /*
    * Inserts a record into Contacts.
    */
    private function addToContacts($dataFields, $dataNamed, $dataForeign, $importID)
    {
        $contactImport = new ContactImport($this->_siteID);

        /* Try to find the company. */
        if (!isset($dataNamed['company_id']))
        {
            return 'Unable to add company - no company name.';
        }

        $companyID = $contactImport->companyByName($dataNamed['company_id']);

        $genCompany = false;

        /* The company does not exist. What do we do? */
        if ($companyID == -1)
        {
            if ($_POST['generateCompanies'] == 'yes')
            {
                /* Build data for the new company. */
                $dataCompany = array();
                $dataCompany['name'] = $dataNamed['company_id'];

                if (isset($dataNamed['phone_work']))
                {
                    $dataCompany['phone1'] = $dataNamed['phone_work'];
                }

                foreach (array('address', 'city', 'state', 'zip') as $field)
                {
                    if (isset($dataNamed[$field]))
                    {
                        $dataCompany[$field] = $dataNamed[$field];
                    }
                }

                if (!eval(Hooks::get('IMPORT_ADD_CONTACT_CLIENT'))) return;

                $companyID = $contactImport->addCompany($dataCompany, $this->_userID, $importID);
                if ($companyID == -1)
                {
                    return 'Unable to add company.';
                }
                $genCompany = true;

                if (!eval(Hooks::get('IMPORT_ADD_CONTACT_CLIENT_POST'))) return;
            }
            else
            {
                /* Bail out of add - no company. */
                return 'Invalid company name.';
            }
        }

        $dataNamed['company_id'] = $companyID;

        /* Bail out if any of the required fields are empty. */

        if (!empty($dataNamed['name']))
        {
            $nameArray = explode(' ', $dataNamed['name']);
            $dataNamed['first_name'] = $nameArray[0];
            $dataNamed['last_name'] = $nameArray[count($nameArray)-1];
            unset($dataNamed['name']);
        }

        if (!isset($dataNamed['first_name']) && !isset($dataNamed['last_name']))
        {
            if ($_POST['unnamedContacts'] == 'yes' && $genCompany)
            {
                $dataNamed['first_name'] = 'nobody';
            }
            else
            {
                $error = 'Required fields (first name, last name) are missing.';
                if ($genCompany)
                {
                    $error .= '  However, the company was generated.';
                }
                return $error;
            }

        }

        if (!eval(Hooks::get('IMPORT_ADD_CONTACT'))) return;

        $contactID = $contactImport->add($dataNamed, $this->_userID, $importID);

        if ($contactID <= 0)
        {
            return 'Failed to add candidate.';
        }

        $this->addForeign(DATA_ITEM_CONTACT, $dataForeign, $contactID, $importID);

        if (!eval(Hooks::get('IMPORT_ADD_CONTACT_POST'))) return;

        if ($genCompany)
        {
            return 'newCompany';
        }

        return '';
    }

    /*
     * Modal popup describing how to use bulk resumes.
     */
    function whatIsBulkResumes()
    {
       $this->_template->assign('active', $this);
       $this->_template->display('./modules/import/BulkResumesHelp.tpl');
    }

   /*
    * Scan the upload directory for files to import, show the files to the user.
    * save the files found so a ajax interface can import 1 file at a time later.
    */
    function showMassImport()
    {
        $directoryRoot = './upload/';
        $foundFiles = array();
        $numberOfFiles = 0;

        $directoriesToWalk = array('');

        while (count($directoriesToWalk) != 0)
        {
            $directoryName = array_pop($directoriesToWalk);
            $fullDirectoryName = $directoryRoot . $directoryName;

            if ($handle = @opendir($fullDirectoryName))
            {
                while (false !== ($file = readdir($handle)))
                {
                    $fileWithDirectory = $directoryName . $file;
                    $fullFileWithDirectory = $fullDirectoryName . $file;

                    if ($file != "." && $file != ".." && $file != ".svn" && filetype($fullFileWithDirectory) == "dir")
                    {
                        array_push ($directoriesToWalk, $fileWithDirectory . '/');
                    }
                    else if ($file != "." && $file != ".." && $file != ".svn")
                    {
                        $numberOfFiles++;
                        $foundFiles[] = $directoryName . $file;
                    }
                }
                closedir($handle);
            }
        }

        sort($foundFiles);

        $_SESSION['CATS']->massImportFiles = $foundFiles;
        $_SESSION['CATS']->massImportDirectory = $directoryRoot;

        $this->_template->assign('active', $this);
        $this->_template->assign('foundFiles', $foundFiles);
        $this->_template->display('./modules/import/ImportResumesBulk.tpl');
    }

    /**
     * AJAX:
     *   This function is called by the javascript progress bar page (step 2) of the
     *   mass resume importer. It parses the resume set in $_POST and saves the
     *   results to a temporary session.
     */
    public function massImportDocument()
    {
        // Find the files the user has uploaded and put them in an array
        if (isset($_SESSION['CATS']) && !empty($_SESSION['CATS']))
        {
            $siteID = $_SESSION['CATS']->getSiteID();
        }
        else
        {
             echo 'Fail';
             return;
        }

        if (isset($_GET['name'])) $name = $_GET['name']; else { echo 'Fail'; return; }
        if (isset($_GET['realName'])) $realName = $_GET['realName']; else { echo 'Fail'; return; }
        if (isset($_GET['ext'])) $ext = $_GET['ext']; else { echo 'Fail'; return; }
        if (isset($_GET['cTime'])) $cTime = intval($_GET['cTime']); else { echo 'Fail'; return; }
        if (isset($_GET['type'])) $type = intval($_GET['type']); else { echo 'Fail'; return; }

        if (!isset($_SESSION['CATS_PARSE_TEMP']))
        {
            $_SESSION['CATS_PARSE_TEMP'] = array();
        }

        $mp = array(
            'name' => $name,
            'realName' => $realName,
            'ext' => $ext,
            'type' => $type,
            'cTime' => $cTime,
        );

        $doc2text = new DocumentToText();
        $pu = new ParseUtility();
        if (LicenseUtility::isParsingEnabled())
        {
            $parsingEnabled = true;
        }
        else
        {
            $parsingEnabled = false;
        }

        if ($doc2text->convert($name, $type) === false)
        {
            $mp['success'] = false;
            $_SESSION['CATS_PARSE_TEMP'][] = $mp;
            echo 'Fail';
            return;
        }
        $contents = $doc2text->getString();

        // Decode things like _rATr to @ so the parser can accurately find things
        $contents = DatabaseSearch::fulltextDecode($contents);

        if ($parsingEnabled)
        {
            switch ($type)
            {
                case DOCUMENT_TYPE_DOC:
                    $contents = str_replace('|', "\n", $contents);
                    $contents = str_replace(' ? ', "\n", $contents);
                    break;
            }
            while (strpos($contents, '  ') !== false)
            {
                $contents = str_replace('  ', ' ', $contents);
            }
        }

        $mp['contents'] = $contents;

        if ($parsingEnabled)
        {
            $parseData = $pu->documentParse($realName, strlen($contents), 'application/text',
                $contents
            );
            $mp['parse'] = $parseData;
        }

        $mp['success'] = true;
        $_SESSION['CATS_PARSE_TEMP'][] = $mp;

        echo 'Ok';
        return;
    }

    public function massImportEdit()
    {
        if (isset($_GET['documentID']))
        {
            $documentID = intval($_GET['documentID']);
        }
        else
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this);
        }

        list($documents, $success, $failed) = $this->getMassImportDocuments();
        if (!count($documents))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this);
        }

        if (isset($_GET['postback']) && $_GET['postback'] == '1')
        {
            // User is saving changes
            if (!isset($_SESSION['CATS_PARSE_TEMP'][$documentID]['parse']))
                $_SESSION['CATS_PARSE_TEMP'][$documentID]['parse'] = array(
                    'firstName' => '', 'lastName' => '', 'address' => '', 'city' => '', 'state' => '',
                    'zipCode' => '', 'email' => '', 'phone' => '', 'skills' => '', 'education' => '',
                    'experience' => ''
            );
            if (isset($_POST['firstName']))
                $_SESSION['CATS_PARSE_TEMP'][$documentID]['parse']['first_name'] = $_POST['firstName'];
            if (isset($_POST['lastName']))
                $_SESSION['CATS_PARSE_TEMP'][$documentID]['parse']['last_name'] = $_POST['lastName'];
            if (isset($_POST['address']))
                $_SESSION['CATS_PARSE_TEMP'][$documentID]['parse']['us_address'] = $_POST['address'];
            if (isset($_POST['city']))
                $_SESSION['CATS_PARSE_TEMP'][$documentID]['parse']['city'] = $_POST['city'];
            if (isset($_POST['state']))
                $_SESSION['CATS_PARSE_TEMP'][$documentID]['parse']['state'] = $_POST['state'];
            if (isset($_POST['zipCode']))
                $_SESSION['CATS_PARSE_TEMP'][$documentID]['parse']['zip_code'] = $_POST['zipCode'];
            if (isset($_POST['homePhone']))
                $_SESSION['CATS_PARSE_TEMP'][$documentID]['parse']['phone_number'] = $_POST['homePhone'];
            if (isset($_POST['email']))
                $_SESSION['CATS_PARSE_TEMP'][$documentID]['parse']['email_address'] = $_POST['email'];
            if (isset($_POST['skills']))
                $_SESSION['CATS_PARSE_TEMP'][$documentID]['parse']['skills'] = $_POST['skills'];
            if (isset($_POST['education']))
                $_SESSION['CATS_PARSE_TEMP'][$documentID]['parse']['education'] = $_POST['education'];
            if (isset($_POST['experience']))
                $_SESSION['CATS_PARSE_TEMP'][$documentID]['parse']['experience'] = $_POST['experience'];

            // Step 3 is the review step
            $this->massImport(3);
            return;
        }

        $document = null;
        foreach ($documents as $doc)
        {
            if ($doc['id'] == $documentID)
            {
                $document = $doc;
            }
        }

        $this->_template->assign('active', $this);
        $this->_template->assign('document', $document);
        $this->_template->assign('documentID', $documentID);
        $this->_template->display('./modules/import/MassImportEdit.tpl');
    }

    public function massImport($step = 1)
    {
        if (isset($_SESSION['CATS']) && !empty($_SESSION['CATS']))
        {
            $siteID = $_SESSION['CATS']->getSiteID();
        }
        else
        {
            CommonErrors::fatal(COMMONERROR_NOTLOGGEDIN, $this);
        }

        if ($this->getUserAccessLevel('import.massImport') < ACCESS_LEVEL_EDIT)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'You do not have permission to import '
                . 'mass resume documents.'
            );
        }

        // Figure out what stage of the process we're on
        if (isset($_GET['step']) && ($step = intval($_GET['step'])) >= 1 && $step <= 4) {}

        $this->_template->assign('step', $step);

        if ($step == 1)
        {
            if (isset($_SESSION['CATS_PARSE_TEMP'])) unset($_SESSION['CATS_PARSE_TEMP']);
            $uploadDir = FileUtility::getUploadPath($siteID, 'massimport');
            $files = ImportUtility::getDirectoryFiles($uploadDir);
            if (is_array($files) && count($files))
            {
                // User already has files for upload
                $this->_template->assign('documents', $files);
            }

            // Figure out the path to post resumes
            $script = $_SERVER['SCRIPT_FILENAME'];
            $mp = explode('/', $script);
            $rootPath = implode('/', array_slice($mp, 0, count($mp) - 1));
            $subPath = FileUtility::getUploadPath($siteID, 'massimport');
            if ($subPath !== false)
            {
                $uploadPath = $rootPath . '/' . $subPath . '/';
            }
            else
            {
                $uploadPath = false;
            }

            $this->_template->assign('multipleFilesEnabled', true);
            $this->_template->assign('uploadPath', $uploadPath);
        }
        else if ($step == 2)
        {
            /**
             * Step 1: Find any uploaded files and get them into an array.
             */
            if (isset($_SESSION['CATS_PARSE_TEMP'])) unset($_SESSION['CATS_PARSE_TEMP']);
            $uploadDir = FileUtility::getUploadPath($siteID, 'massimport');
            $files = ImportUtility::getDirectoryFiles($uploadDir);
            if ($files === -1 || !is_array($files) || !count($files))
            {
                $this->_template->assign('errorMessage', 'You didn\'t upload any files or there was a '
                    . 'problem working with any files you uploaded. Please use the '
                    . '<a href="javascript:back()"><b>Back</b></a> button on your web browser '
                    . 'and select one or more files to import.'
                );

                $this->_template->assign('files', array());
                $this->_template->assign('js', '');
            }
            else
            {
                if (!eval(Hooks::get('MASS_IMPORT_SPACE_CHECK'))) return;

                // Build the javascript to handle the ajax parsing (for progress bar)
                $js = '';
                foreach ($files as $fileData)
                {
                    $js .= sprintf('addDocument(\'%s\', \'%s\', \'%s\', %d, %d);%s',
                        addslashes($fileData['name']), addslashes($fileData['realName']), addslashes($fileData['ext']),
                        $fileData['type'], $fileData['cTime'], "\n"
                    );
                }

                $this->_template->assign('files', $files);
                $this->_template->assign('js', $js);
            }
        }
        else if ($step == 3)
        {
            // Make sure the processed files exists, is an array, and is not empty
            list($documents, $success, $failed) = $this->getMassImportDocuments();
            if (!count($documents))
            {
                $this->_template->assign('errorMessage', 'None of the files you uploaded were able '
                    . 'to be imported!'
                );
            }

            $this->_template->assign('documents', $documents);
        }
        else if ($step == 4)
        {
            // Final step, import all applicable candidates
            list($importedCandidates, $importedDocuments, $importedFailed, $importedDuplicates) =
                $this->getMassImportCandidates();

            if (!count($importedCandidates) && !count($importedDocuments) && !count($importedFailed) &&
                !count($importedDuplicates))
            {
                $this->_template->assign('errorMessage', '<b style="font-size: 20px;">Information no Longer '
                    . 'Available</b><br /><br />'
                    . 'Ooops! You probably used the <b>back</b> or <b>refresh</b> '
                    . 'buttons on your browser. The information you previously had here is no longer '
                    . 'available. To start a new '
                    . 'mass resume import, <a style="font-size: 16px;" href="' . CATSUtility::getIndexName() . '?m=import&a=massImport&'
                    . 'step=1">click here</a>.'
                );
            }

            //if (!eval(Hooks::get('IMPORT_NOTIFY_DEV'))) return;

            $this->_template->assign('importedCandidates', $importedCandidates);
            $this->_template->assign('importedDocuments', $importedDocuments);
            $this->_template->assign('importedFailed', $importedFailed);
            $this->_template->assign('importedDuplicates', $importedDuplicates);

            unset($_SESSION['CATS_PARSE_TEMP']);
        }
        else if ($step == 99)
        {
            // User wants to delete all files in their upload folder
            $uploadDir = FileUtility::getUploadPath($siteID, 'massimport');
            $files = ImportUtility::getDirectoryFiles($uploadDir);
            if (is_array($files) && count($files))
            {
                foreach ($files as $file)
                {
                    @unlink($file['name']);
                }
            }
            echo 'Ok';
            return;
        }

        $this->_template->assign('active', $this);
        // ->isDemo() doesn't work here... oddly.
        $this->_template->assign('isDemo', $_SESSION['CATS']->getSiteID() == 201);

        // Build the sub-template to pass to the container
        ob_start();
        $this->_template->display(sprintf('./modules/import/MassImportStep%d.tpl', $step));
        $subTemplateContents = ob_get_contents();
        ob_end_clean();

        // Show the main template (the container with the large status sections)
        $this->_template->assign('subTemplateContents', $subTemplateContents);
        $this->_template->display('./modules/import/MassImport.tpl');
    }

    private function getMassImportCandidates()
    {
        $db = DatabaseConnection::getInstance();

        // Find the files the user has uploaded and put them in an array
        if (isset($_SESSION['CATS']) && !empty($_SESSION['CATS']))
        {
            $siteID = $_SESSION['CATS']->getSiteID();
            $userID = $_SESSION['CATS']->getUserID();
        }
        else
        {
            CommonErrors::fatal(COMMONERROR_NOTLOGGEDIN, $this);
        }

        list($documents, $success, $failed) = $this->getMassImportDocuments();
        if (!count($documents))
        {
            return array( array(), array(), array(), array() );
        }

        $importedCandidates = array();
        $importedDocuments = array();
        $importedFailed = array();
        $importedDuplicates = array();

        for ($ind=0; $ind<count($_SESSION['CATS_PARSE_TEMP']); $ind++)
        {
            $doc = $_SESSION['CATS_PARSE_TEMP'][$ind];

            // Get parsed information instead (if available)
            for ($ind2=0; $ind2<count($documents); $ind2++)
            {
                if ($documents[$ind2]['id'] == $ind)
                {
                    $doc = $documents[$ind2];
                }
            }

            if (isset($doc['success']) && $doc['success'])
            {
                $candidateAdded = false;

                if (isset($doc['lastName']) && $doc['lastName'] != '' && isset($doc['firstName']) && $doc['firstName'] != '')
                {
                    $isCandidateUnique = true;

                    /**
                     * We need to check for duplicate candidate entries before adding a new
                     * candidate into CATS. The criteria is as follows:
                     * - if email is present, does it match an existing e-mail
                     * - if last name and zip code or last name and phone numbers are present, do they match likewise
                     */
                    if (strpos($doc['email'], '@') !== false)
                    {
                        $sql = sprintf('SELECT count(*) '
                            . 'FROM candidate '
                            . 'WHERE (candidate.email1 = %s OR candidate.email2 = %s) '
                            . 'AND candidate.site_id = %d',
                            $db->makeQueryString($doc['email']),
                            $db->makeQueryString($doc['email']),
                            $this->_siteID
                        );
                        if ($db->getColumn($sql, 0, 0) > 0)
                        {
                            $isCandidateUnique = false;
                        }
                    }

                    if (strlen($doc['lastName']) > 3 && isset($doc['phone']) && strlen($doc['phone']) >= 10)
                    {
                        $sql = sprintf('SELECT count(*) '
                            . 'FROM candidate '
                            . 'WHERE candidate.last_name = %s '
                            . 'AND (candidate.phone_home = %s '
                            . 'OR candidate.phone_work = "%s '
                            . 'OR candidate.phone_cell = "%s) '
                            . 'AND candidate.site_id = %d',
                            $db->makeQueryString($doc['lastName']),
                            $db->makeQueryString($doc['phone']),
                            $db->makeQueryString($doc['phone']),
                            $db->makeQueryString($doc['phone']),
                            $this->_siteID
                        );
                        if ($db->getColumn($sql, 0, 0) > 0)
                        {
                            $isCandidateUnique = false;
                        }
                    }

                    if (strlen($doc['lastName']) > 3 && isset($doc['zip']) && strlen($doc['zip']) >= 5)
                    {
                        $sql = sprintf('SELECT count(*) '
                            . 'FROM candidate '
                            . 'WHERE candidate.last_name = %s '
                            . 'AND candidate.zip = %s '
                            . 'AND candidate.site_id = %d',
                            $db->makeQueryString($doc['lastName']),
                            $db->makeQueryString($doc['zipCode']),
                            $this->_siteID
                        );
                        if ($db->getColumn($sql, 0, 0) > 0)
                        {
                            $isCandidateUnique = false;
                        }
                    }

                    if ($isCandidateUnique)
                    {
                        // This was parsed data
                        $candidates = new Candidates($siteID);

                        $candidateID = $candidates->add(
                            $doc['firstName'],
                            '',
                            $doc['lastName'],
                            $doc['email'],
                            '',
                            $doc['phone'],
                            '',
                            '',
                            $doc['address'],
                            $doc['city'],
                            $doc['state'],
                            $doc['zipCode'],
                            '',
                            $doc['skills'],
                            NULL,
                            '',
                            false,
                            '',
                            '',
                            'This resume was parsed automatically. You should review it for errors.',
                            '',
                            '',
                            $userID,
                            $userID,
                            '',
                            0,
                            0,
                            '',
                            true
                        );

                        if ($candidateID > 0)
                        {
                            $candidateAdded = true;

                            // set the date created to the file modification date
                            $db->query(sprintf('UPDATE candidate SET date_created = "%s", date_modified = "%s" '
                                . 'WHERE candidate_id = %d AND site_id = %d',
                                date('c', $doc['cTime']), date('c', $doc['cTime']), $candidateID, $siteID
                            ));

                            // Success, attach resume to candidate as attachment
                            $ac = new AttachmentCreator($siteID);
                            if ($ac->createFromFile(DATA_ITEM_CANDIDATE, $candidateID, $doc['name'], $doc['realName'],
                                '', true, true))
                            {
                                // FIXME: error checking on fail?
                            }

                            $importedCandidates[] = array(
                                'name' => trim($doc['firstName'] . ' ' . $doc['lastName']),
                                'resume' => $doc['realName'],
                                'url' => sprintf(
                                    '%s?m=candidates&a=show&candidateID=%d',
                                    CATSUtility::getIndexName(),
                                    $candidateID
                                ),
                                'location' => trim($doc['city'] . ' ' . $doc['state'] . ' ' . $doc['zipCode'])
                            );
                        }
                    }
                    else
                    {
                        $importedDuplicates[] = array(
                            'name' => trim($doc['firstName'] . ' ' . $doc['lastName']),
                            'resume' => $doc['realName']
                        );
                        @unlink($doc['name']);
                        $candidateAdded = true;
                    }
                }

                /**
                 * A candidate was unable to be automatically added, add them as a
                 * bulk resume document which is still searchable and can be manually
                 * converted into a candidate later.
                 */
                if (!$candidateAdded)
                {
                    $brExists = false;
                    $error = false;

                    /**
                     * Bulk resumes can be "rescanned", make sure this particular file isn't a
                     * rescan before adding another copy.
                     */
                    if (preg_match('/^_BulkResume_(.*)\.txt$/', $doc['realName'], $matches))
                    {
                        $attachments = new Attachments($this->_siteID);
                        $bulkResumes = $attachments->getBulkAttachments();
                        foreach ($bulkResumes as $bulkResume)
                        {
                            $mp = explode('.', $bulkResume['originalFileName']);
                            $fileName = implode('.', array_slice($mp, 0, -1));

                            if (!strcmp($fileName, $matches[1]))
                            {
                                $brExists = true;
                                if (FileUtility::isUploadFileSafe($siteID, 'massimport', $doc['name']))
                                {
                                    @unlink($doc['name']);
                                }
                                break;
                            }
                        }
                    }

                    if (!$brExists)
                    {
                        $error = false;
                        $attachmentCreator = new AttachmentCreator($siteID);
                        $attachmentCreator->createFromFile(
                            DATA_ITEM_BULKRESUME, 0, $doc['name'], $doc['realName'], '', true, true
                        );

                        if ($attachmentCreator->isError())
                        {
                            $error = true;
                        }
                        if ($attachmentCreator->duplicatesOccurred())
                        {
                            $error = true;
                        }
                    }

                    // For use later on debugging
                    //$isTextExtractionError = $attachmentCreator->isTextExtractionError();
                    //$textExtractionErrorMessage = $attachmentCreator->getTextExtractionError();

                    if (!$error || $brExists)
                    {
                        $importedDocuments[] = array(
                            'name' => $doc['realName']
                        );
                    }
                    else
                    {
                        $importedFailed[] = array(
                            'name' => $doc['realName']
                        );
                    }
                }
                /**
                 * The candidate was successfully added. If this candidate came from an
                 * existing bulk resume rescan, that document should be deleted.
                 */
                else
                {
                    if (preg_match('/^_BulkResume_(.*)\.txt$/', $doc['realName'], $matches))
                    {
                        $attachments = new Attachments($this->_siteID);
                        $bulkResumes = $attachments->getBulkAttachments();
                        foreach ($bulkResumes as $bulkResume)
                        {
                            $mp = explode('.', $bulkResume['originalFileName']);
                            $fileName = implode('.', array_slice($mp, 0, -1));

                            if (!strcmp($fileName, $matches[1]))
                            {
                                // Delete the permanent file
                                $attachments->delete($bulkResume['attachmentID'], true);
                                // Delete the temporary file
                                if (FileUtility::isUploadFileSafe($siteID, 'massimport', $doc['name']))
                                {
                                    @unlink($doc['name']);
                                }
                                break;
                            }
                        }
                    }
                }
            }
            else
            {
                // This document failed to convert to a text-format using doc2text
                $importedFailed[] = array(
                    'name' => $doc['realName']
                );

                // Make sure it's a safe filename to delete and located in the site's upload directory
                if (FileUtility::isUploadFileSafe($siteID, 'massimport', $doc['name']))
                {
                    @unlink($doc['name']);
                }
            }
        }

        return array($importedCandidates, $importedDocuments, $importedFailed, $importedDuplicates);
    }

    private function getMassImportDocuments()
    {
        if (!isset($_SESSION['CATS_PARSE_TEMP']) || empty($_SESSION['CATS_PARSE_TEMP']) ||
            !is_array($_SESSION['CATS_PARSE_TEMP']))
        {
            return array(array(), 0, 0);
        }

        $mp = $_SESSION['CATS_PARSE_TEMP'];

        // Clean up the documents for the next stage
        $documents = array();
        $failed = $success = 0;
        for ($ind=0; $ind<count($mp); $ind++)
        {
            $doc = $mp[$ind];

            if (isset($doc['success']) && $doc['success'])
            {
                if (isset($doc['parse']) && is_array($doc['parse']))
                {
                    if (isset($doc['parse'][$id = 'first_name']))
                        $doc['firstName'] = $doc['parse'][$id]; else $doc['firstName'] = '';
                    if (isset($doc['parse'][$id = 'last_name']))
                        $doc['lastName'] = $doc['parse'][$id]; else $doc['lastName'] = '';
                    if (isset($doc['parse'][$id = 'us_address']))
                        $doc['address'] = $doc['parse'][$id]; else $doc['address'] = '';
                    if (isset($doc['parse'][$id = 'city']))
                        $doc['city'] = $doc['parse'][$id]; else $doc['city'] = '';
                    if (isset($doc['parse'][$id = 'state']))
                        $doc['state'] = $doc['parse'][$id]; else $doc['state'] = '';
                    if (isset($doc['parse'][$id = 'zip_code']))
                        $doc['zipCode'] = $doc['parse'][$id]; else $doc['zipCode'] = '';
                    if (isset($doc['parse'][$id = 'email_address']))
                        $doc['email'] = $doc['parse'][$id]; else $doc['email'] = '';
                    if (isset($doc['parse'][$id = 'phone_number']))
                        $doc['phone'] = $doc['parse'][$id]; else $doc['phone'] = '';
                    if (isset($doc['parse'][$id = 'education']))
                        $doc['education'] = $doc['parse'][$id]; else $doc['education'] = '';
                    if (isset($doc['parse'][$id = 'skills']))
                        $doc['skills'] = $doc['parse'][$id]; else $doc['skills'] = '';
                    if (isset($doc['parse'][$id = 'experience']))
                        $doc['experience'] = $doc['parse'][$id]; else $doc['experience'] = '';
                }
                else
                {
                    $doc['firstName'] = $doc['lastName'] = $doc['address'] = $doc['city'] =
                        $doc['state'] = $doc['zipCode'] = $doc['email'] = $doc['phone'] =
                        $doc['education'] = $doc['skills'] = $doc['experience'] = '';
                }
                $doc['id'] = $ind;
                $documents[] = $doc;
                $success++;
            }
            else
            {
                $failed++;
            }
        }
        return array($documents, $success, $failed);
    }

    private function deleteBulkResumes()
    {
        if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
        {
            CommonErrors::fatal(COMMONERROR_NOTLOGGEDIN, $this);
        }
        if ($this->getUserAccessLevel('import.bulkResumes') < ACCESS_LEVEL_SA)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
        }

        $uploadPath = FileUtility::getUploadPath($this->_siteID, 'massimport');

        $attachments = new Attachments($this->_siteID);
        $bulkResumes = $attachments->getBulkAttachments();

        if (!count($bulkResumes))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this);
        }

        /**
         * Write the parsed resume contents to the new file which will
         * be created as a text document for each bulk attachment.
         */
        foreach ($bulkResumes as $bulkResume)
        {
            $attachments->delete($bulkResume['attachmentID'], true);
        }

        $this->import();
    }

    private function importBulkResumes()
    {
        if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
        {
            CommonErrors::fatal(COMMONERROR_NOTLOGGEDIN, $this);
        }
        if ($this->getUserAccessLevel('import.bulkResumes') < ACCESS_LEVEL_SA)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
        }

        $uploadPath = FileUtility::getUploadPath($this->_siteID, 'massimport');

        $attachments = new Attachments($this->_siteID);
        $bulkResumes = $attachments->getBulkAttachments();

        if (!count($bulkResumes))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this);
        }

        /**
         * Write the parsed resume contents to the new file which will
         * be created as a text document for each bulk attachment.
         */
        foreach ($bulkResumes as $bulkResume)
        {
            $fullName = $bulkResume['originalFileName'];
            if (!strlen(trim($fullName)))
            {
                $fullName = 'Untitled';
            }

            $mp = explode('.', $fullName);
            $fileName = implode('.', array_slice($mp, 0, -1));

            if (!@file_exists($newFileName = $uploadPath . '/_BulkResume_' . $fileName . '.txt'))
            {
                // Some old files are fulltext encoded which makes them a pain for the parser, fixing here:
                $contents = DatabaseSearch::fulltextDecode($bulkResume['text']);

                @file_put_contents($newFileName, $contents);
                chmod($newFileName, 0777);
            }
        }

        CATSUtility::transferRelativeURI('m=import&a=massImport&step=2');
    }
}

?>
