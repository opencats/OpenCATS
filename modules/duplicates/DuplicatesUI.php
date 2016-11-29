<?php
/**
* Duplicates Module
* @package OpenCATS
* @subpackage modules/duplicates
* @copyright (C) OpenCats
* @license GNU/GPL, see license.txt
* OpenCATS is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License 2
* as published by the Free Software Foundation.
*/

include_once('./lib/FileUtility.php');
include_once('./lib/StringUtility.php');
include_once('./lib/ResultSetUtility.php');
include_once('./lib/DateUtility.php'); /* Depends on StringUtility. */
include_once('./lib/Candidates.php');
include_once('./lib/Pipelines.php');
include_once('./lib/Attachments.php');
include_once('./lib/ActivityEntries.php');
include_once('./lib/JobOrders.php');
include_once('./lib/Export.php');
include_once('./lib/ExtraFields.php');
include_once('./lib/Calendar.php');
include_once('./lib/SavedLists.php');
include_once('./lib/EmailTemplates.php');
include_once('./lib/DocumentToText.php');
include_once('./lib/DatabaseSearch.php');
include_once('./lib/CommonErrors.php');
include_once('./lib/License.php');
include_once('./lib/ParseUtility.php');
include_once('./lib/Questionnaire.php');
include_once('./lib/Tags.php');
include_once('./lib/Duplicates.php');
include_once('./lib/Search.php');

class DuplicatesUI extends UserInterface
{
    /* Maximum number of characters of the candidate notes to show without the
     * user clicking "[More]"
     */
    const NOTES_MAXLEN = 500;

    /* Maximum number of characters of the candidate name to show on the main
     * contacts listing.
     */
    const TRUNCATE_KEYSKILLS = 30;


    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = true;
        $this->_moduleDirectory = 'duplicates';
        $this->_moduleName = 'duplicates';
        $this->_moduleTabText = 'Duplicates';
    }


    public function handleRequest()
    {
        if (!eval(Hooks::get('DUPLICATES_HANDLE_REQUEST'))) return;
        
        $action = $this->getAction();
        switch ($action)
        {
            case 'show':
                $this->show();
                break;

            case 'viewResume':
                include_once('./lib/Search.php');

                $this->viewResume();
                break;

            /* Hot List Page */
            case 'savedLists':
                $this->savedList();
                break;
                
            case 'linkDuplicate':
                $this->findDuplicateCandidateSearch();
                break;
                
             /* Merge two duplicate candidates into the older one */
            case 'merge':
                $this->mergeDuplicates();
                break;
                
            case 'mergeInfo':
                $this->mergeDuplicatesInfo();
                break;
            
            /* Remove duplicity warning from a new candidate */
            case 'removeDuplicity':
                $this->removeDuplicity();
                break;
            
            case 'addDuplicates':
                $this->addDuplicates();
                break;
                
            /* Main candidates page. */
            case 'listByView':
            default:
                $this->listByView();
                break;
        }
    }


    /*
     * Called by handleRequest() to process loading the list / main page.
     */
    private function listByView($errMessage = '')
    {
        // Log message that shows up on the top of the list page
        $topLog = '';

        $dataGridProperties = DataGrid::getRecentParamaters("duplicates:duplicatesListByViewDataGrid");

        /* If this is the first time we visited the datagrid this session, the recent paramaters will
         * be empty.  Fill in some default values. */
        if ($dataGridProperties == array())
        {
            $dataGridProperties = array('rangeStart'    => 0,
                                        'maxResults'    => 15,
                                        'filterVisible' => false);
        }

        //$newParameterArray = $this->_parameters;
        $tags = new Tags($this->_siteID);
        $tagsRS = $tags->getAll();
        //foreach($tagsRS as $r) $r['link'] = DataGrid::_makeControlLink($newParameterArray);

        $dataGrid = DataGrid::get("duplicates:duplicatesListByViewDataGrid", $dataGridProperties);

        $duplicates = new Duplicates($this->_siteID);
        $this->_template->assign('totalDuplicates', $duplicates->getCount());

        $this->_template->assign('active', $this);
        $this->_template->assign('dataGrid', $dataGrid);
        $this->_template->assign('userID', $_SESSION['CATS']->getUserID());
        $this->_template->assign('errMessage', $errMessage);
        $this->_template->assign('topLog', $topLog);
        $this->_template->assign('tagsRS', $tagsRS);

        if (!eval(Hooks::get('DUPLICATE_LIST_BY_VIEW'))) return;

        $this->_template->display('./modules/duplicates/Duplicates.tpl');
    }

    /*
     * Called by handleRequest() to process loading the details page.
     */
    private function show()
    {
        /* Is this a popup? */
        if (isset($_GET['display']) && $_GET['display'] == 'popup')
        {
            $isPopup = true;
        }
        else
        {
            $isPopup = false;
        }

        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('candidateID', $_GET) && !isset($_GET['email']))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
        }

        $candidates = new Candidates($this->_siteID);

        if (isset($_GET['candidateID']))
        {
            $candidateID = $_GET['candidateID'];
        }
        else
        {
            $candidateID = $candidates->getIDByEmail($_GET['email']);
        }

        $data = $candidates->getWithDuplicity($candidateID);

        /* Bail out if we got an empty result set. */
        if (empty($data))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'The specified candidate ID could not be found.');
            return;
        }

        if ($data['isAdminHidden'] == 1 && $this->_accessLevel < ACCESS_LEVEL_MULTI_SA)
        {
            $this->listByView('This candidate is hidden - only a CATS Administrator can unlock the candidate.');
            return;
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

        /* Format "can relocate" status. */
        if ($data['canRelocate'] == 1)
        {
            $data['canRelocate'] = 'Yes';
        }
        else
        {
            $data['canRelocate'] = 'No';
        }

        if ($data['isHot'] == 1)
        {
            $data['titleClass'] = 'jobTitleHot';
        }
        else
        {
            $data['titleClass'] = 'jobTitleCold';
        }

        $attachments = new Attachments($this->_siteID);
        $attachmentsRS = $attachments->getAll(
            DATA_ITEM_CANDIDATE, $candidateID
        );

        foreach ($attachmentsRS as $rowNumber => $attachmentsData)
        {
            /* If profile image is not local, force it to be local. */
            if ($attachmentsData['isProfileImage'] == 1)
            {
                $attachments->forceAttachmentLocal($attachmentsData['attachmentID']);
            }

            /* Show an attachment icon based on the document's file type. */
            $attachmentIcon = strtolower(
                FileUtility::getAttachmentIcon(
                    $attachmentsRS[$rowNumber]['originalFilename']
                )
            );

            $attachmentsRS[$rowNumber]['attachmentIcon'] = $attachmentIcon;

            /* If the text field has any text, show a preview icon. */
            if ($attachmentsRS[$rowNumber]['hasText'])
            {
                $attachmentsRS[$rowNumber]['previewLink'] = sprintf(
                    '<a href="#" onclick="window.open(\'%s?m=candidates&amp;a=viewResume&amp;attachmentID=%s\', \'viewResume\', \'scrollbars=1,width=800,height=760\')"><img width="15" height="15" style="border: none;" src="images/search.gif" alt="(Preview)" /></a>',
                    CATSUtility::getIndexName(),
                    $attachmentsRS[$rowNumber]['attachmentID']
                );
            }
            else
            {
                $attachmentsRS[$rowNumber]['previewLink'] = '&nbsp;';
            }
        }
        $pipelines = new Pipelines($this->_siteID);
        $pipelinesRS = $pipelines->getCandidatePipeline($candidateID);

        $sessionCookie = $_SESSION['CATS']->getCookie();

        /* Format pipeline data. */
        foreach ($pipelinesRS as $rowIndex => $row)
        {
            /* Hot jobs [can] have different title styles than normal
             * jobs.
             */
            if ($row['isHot'] == 1)
            {
                $pipelinesRS[$rowIndex]['linkClass'] = 'jobLinkHot';
            }
            else
            {
                $pipelinesRS[$rowIndex]['linkClass'] = 'jobLinkCold';
            }

            $pipelinesRS[$rowIndex]['ownerAbbrName'] = StringUtility::makeInitialName(
                $pipelinesRS[$rowIndex]['ownerFirstName'],
                $pipelinesRS[$rowIndex]['ownerLastName'],
                false,
                LAST_NAME_MAXLEN
            );

            $pipelinesRS[$rowIndex]['addedByAbbrName'] = StringUtility::makeInitialName(
                $pipelinesRS[$rowIndex]['addedByFirstName'],
                $pipelinesRS[$rowIndex]['addedByLastName'],
                false,
                LAST_NAME_MAXLEN
            );

            $pipelinesRS[$rowIndex]['ratingLine'] = TemplateUtility::getRatingObject(
                $pipelinesRS[$rowIndex]['ratingValue'],
                $pipelinesRS[$rowIndex]['candidateJobOrderID'],
                $sessionCookie
            );
        }

        $activityEntries = new ActivityEntries($this->_siteID);
        $activityRS = $activityEntries->getAllByDataItem($candidateID, DATA_ITEM_CANDIDATE);
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
        $calendarRS = $candidates->getUpcomingEvents($candidateID);
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

        /* Get extra fields. */
        $extraFieldRS = $candidates->extraFields->getValuesForShow($candidateID);

        /* Add an MRU entry. */
        $_SESSION['CATS']->getMRU()->addEntry(
            DATA_ITEM_CANDIDATE, $candidateID, $data['firstName'] . ' ' . $data['lastName']
        );

        /* Is the user an admin - can user see history? */
        if ($this->_accessLevel < ACCESS_LEVEL_DEMO)
        {
            $privledgedUser = false;
        }
        else
        {
            $privledgedUser = true;
        }

        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();
        $EEOValues = array();

        /* Make a list of all EEO related values so they can be positioned by index
         * rather than static positioning (like extra fields). */
        if ($EEOSettingsRS['enabled'] == 1)
        {
            if ($EEOSettingsRS['genderTracking'] == 1)
            {
                $EEOValues[] = array('fieldName' => 'Gender', 'fieldValue' => $data['eeoGenderText']);
            }
            if ($EEOSettingsRS['ethnicTracking'] == 1)
            {
                $EEOValues[] = array('fieldName' => 'Ethnicity', 'fieldValue' => $data['eeoEthnicType']);
            }
            if ($EEOSettingsRS['veteranTracking'] == 1)
            {
                $EEOValues[] = array('fieldName' => 'Veteran Status', 'fieldValue' => $data['eeoVeteranType']);
            }
            if ($EEOSettingsRS['disabilityTracking'] == 1)
            {
                $EEOValues[] = array('fieldName' => 'Disability Status', 'fieldValue' => $data['eeoDisabilityStatus']);
            }
        }

        $tags = new Tags($this->_siteID);

        $questionnaire = new Questionnaire($this->_siteID);
        $questionnaires = $questionnaire->getCandidateQuestionnaires($candidateID);

        $this->_template->assign('active', $this);
        $this->_template->assign('questionnaires', $questionnaires);
        $this->_template->assign('data', $data);
        $this->_template->assign('isShortNotes', $isShortNotes);
        $this->_template->assign('attachmentsRS', $attachmentsRS);
        $this->_template->assign('pipelinesRS', $pipelinesRS);
        $this->_template->assign('activityRS', $activityRS);
        $this->_template->assign('calendarRS', $calendarRS);
        $this->_template->assign('extraFieldRS', $extraFieldRS);
        $this->_template->assign('candidateID', $candidateID);
        $this->_template->assign('isPopup', $isPopup);
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);
        $this->_template->assign('EEOValues', $EEOValues);
        $this->_template->assign('privledgedUser', $privledgedUser);
        $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());
        $this->_template->assign('tagsRS', $tags->getAll());
        $this->_template->assign('assignedTags', $tags->getCandidateTagsTitle($candidateID));

        if (!eval(Hooks::get('DUPLICATES_SHOW'))) return;

        $this->_template->display('./modules/duplicates/Show.tpl');
    }

   
   

    /*
     * Called by handleRequest() to process showing a resume preview.
     */
    private function viewResume()
    {
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('attachmentID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid attachment ID.');
        }

        $attachmentID = $_GET['attachmentID'];

        /* Get the search string. */
        $query = $this->getTrimmedInput('wildCardString', $_GET);

        /* Get resume text. */
        $candidates = new Candidates($this->_siteID);
        $data = $candidates->getResume($attachmentID);

        if (!empty($data))
        {
            /* Keyword highlighting. */
            $data['text'] = SearchUtility::makePreview($query, $data['text']);
        }

        if (!eval(Hooks::get('CANDIDATE_VIEW_RESUME'))) return;

        $this->_template->assign('active', $this);
        $this->_template->assign('data', $data);
        $this->_template->display('./modules/candidates/ResumeView.tpl');
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
            if ($resultSet[$rowIndex]['isHot'] == 1)
            {
                $resultSet[$rowIndex]['linkClass'] = 'jobLinkHot';
            }
            else
            {
                $resultSet[$rowIndex]['linkClass'] = 'jobLinkCold';
            }

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

            if ($resultSet[$rowIndex]['submitted'] == 1)
            {
                $resultSet[$rowIndex]['iconTag'] = '<img src="images/job_orders.gif" alt="" width="16" height="16" title="Submitted for a Job Order" />';
            }
            else
            {
                $resultSet[$rowIndex]['iconTag'] = '<img src="images/mru/blank.gif" alt="" width="16" height="16" />';
            }

            if ($resultSet[$rowIndex]['attachmentPresent'] == 1)
            {
                $resultSet[$rowIndex]['iconTag'] .= '<img src="images/paperclip.gif" alt="" width="16" height="16" title="Attachment Present" />';
            }
            else
            {
                $resultSet[$rowIndex]['iconTag'] .= '<img src="images/mru/blank.gif" alt="" width="16" height="16" />';
            }


            if (empty($resultSet[$rowIndex]['keySkills']))
            {
                $resultSet[$rowIndex]['keySkills'] = '&nbsp;';
            }
            else
            {
                $resultSet[$rowIndex]['keySkills'] = htmlspecialchars(
                    $resultSet[$rowIndex]['keySkills']
                );
            }

            /* Truncate Key Skills to fit the column width */
            if (strlen($resultSet[$rowIndex]['keySkills']) > self::TRUNCATE_KEYSKILLS)
            {
                $resultSet[$rowIndex]['keySkills'] = substr(
                    $resultSet[$rowIndex]['keySkills'],
                    0,
                    self::TRUNCATE_KEYSKILLS
                ) . "...";
            }
        }

        return $resultSet;
    }
    
    /*
     * Called by handleRequest() to handle processing an "Add to a Job Order
     * Pipeline" search and displaying the results in the modal dialog, or
     * to show the initial dialog.
     */
    private function findDuplicateCandidateSearch()
    {
        $duplicateCandidateID = $_GET['candidateID'];
        if($duplicateCandidateID == "")
        {
            $duplicateCandidateID = $_POST['candidateID'];
        }
        $query = $this->getTrimmedInput('wildCardString', $_POST);
        $mode  = $this->getTrimmedInput('mode', $_POST);

        /* Execute the search. */
        $search = new SearchCandidates($this->_siteID);
        switch ($mode)
        {
            case 'searchByCandidateName':
                $rs = $search->byFullName($query, 'candidate.last_name', 'ASC', true);
                $resultsMode = true;
                break;

            default:
                $rs = $search->all($query, 'candidate.last_name', 'ASC', 'true');
                $resultsMode = false;
                break;
        }
        
        $duplicates = new Duplicates($this->_siteID);
        
        foreach ($rs as $rowIndex => $row)
        {
            $rs[$rowIndex]['duplicateCandidateID'] = $duplicateCandidateID;
            if ($duplicates->checkIfLinked($rs[$rowIndex]['candidateID'], $duplicateCandidateID))
            {
                $rs[$rowIndex]['linked'] = true;
            }
            else
            {
                $rs[$rowIndex]['linked'] = false;
            }

            if ($row['isHot'] == 1)
            {
                $rs[$rowIndex]['linkClass'] = 'jobLinkHot';
            }
            else
            {
                $rs[$rowIndex]['linkClass'] = 'jobLinkCold';
            }
        }

        if (!eval(Hooks::get('DUPLICATE_ON_LINK_DUPLICATES'))) return;

        $this->_template->assign('rs', $rs);
        $this->_template->assign('isFinishedMode', false);
        $this->_template->assign('isResultsMode', $resultsMode);
        $this->_template->assign('duplicateCandidateID', $duplicateCandidateID);
        $this->_template->display('./modules/duplicates/LinkDuplicity.tpl');
    }
    
     private function mergeDuplicates()
    {
        $duplicates = new Duplicates($this->_siteID);
        $candidates = new Candidates($this->_siteID);
        $oldCandidateID = $_GET['oldCandidateID'];
        $newCandidateID = $_GET['newCandidateID'];
        
        $rsOld = $candidates->getWithDuplicity($oldCandidateID);
        $rsNew = $candidates->getWithDuplicity($newCandidateID);
         
        $this->_template->assign('isFinishedMode', false); 
        $this->_template->assign('rsOld', $rsOld);
        $this->_template->assign('rsNew', $rsNew);
        $this->_template->assign('oldCandidateID', $oldCandidateID);
        $this->_template->assign('newCandidateID', $newCandidateID); 
        $this->_template->display('./modules/duplicates/Merge.tpl');
    }
    
    private function mergeDuplicatesInfo()
    {
        $duplicates = new Duplicates($this->_siteID);
        $candidates = new Candidates($this->_siteID);
        $params = array();
        $params['firstName'] = $_POST['firstName'];
        $params['middleName'] =  $_POST['middleName'];
        $params['lastName'] = $_POST['lastName'];
        if(isset($_POST['email']))
        {
            $params['emails'] = $_POST['email'];
        }
        else
        {
            $params['emails'] = array();
        }
        $params['phoneCell'] = $_POST['phoneCell'];
        $params['phoneWork'] = $_POST['phoneWork'];
        $params['phoneHome'] = $_POST['phoneHome'];
        $params['address'] = $_POST['address'];
        $params['website'] = $_POST['website'];
        $params['oldCandidateID'] = $_POST['oldCandidateID'];
        $params['newCandidateID'] = $_POST['newCandidateID'];
        
        $duplicates->mergeDuplicates($params, $candidates->getWithDuplicity($params['newCandidateID']));
        $this->_template->assign('isFinishedMode', true); 
        $this->_template->display('./modules/duplicates/Merge.tpl');
    }
    
    private function removeDuplicity()
    {
        $duplicates = new Duplicates($this->_siteID);
        $oldCandidateID = $_GET['oldCandidateID'];
        $newCandidateID = $_GET['newCandidateID'];
        $duplicates->removeDuplicity($oldCandidateID, $newCandidateID);
        $url = CATSUtility::getIndexName()."?m=duplicates";
        header("Location: " . $url); /* Redirect browser */
        exit();
    }
    
    
    private function addDuplicates()
    {
        $duplicates = new Duplicates($this->_siteID);
        $oldCandidateID = $_GET['candidateID'];
        $newCandidateID = $_GET['duplicateCandidateID'];
        $duplicates->addDuplicates($newCandidateID, $oldCandidateID);
        $this->_template->assign('isFinishedMode', true);
        $this->_template->display('./modules/duplicates/LinkDuplicity.tpl');
    }
}

?>
