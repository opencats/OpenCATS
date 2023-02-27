<?php
/*
 * CATS
 * Candidates Module
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
 * $Id: CandidatesUI.php 3810 2007-12-05 19:13:25Z brian $
 */

include_once(LEGACY_ROOT . '/lib/FileUtility.php');
include_once(LEGACY_ROOT . '/lib/StringUtility.php');
include_once(LEGACY_ROOT . '/lib/ResultSetUtility.php');
include_once(LEGACY_ROOT . '/lib/DateUtility.php'); /* Depends on StringUtility. */
include_once(LEGACY_ROOT . '/lib/Candidates.php');
include_once(LEGACY_ROOT . '/lib/Pipelines.php');
include_once(LEGACY_ROOT . '/lib/Attachments.php');
include_once(LEGACY_ROOT . '/lib/ActivityEntries.php');
include_once(LEGACY_ROOT . '/lib/JobOrders.php');
include_once(LEGACY_ROOT . '/lib/Export.php');
include_once(LEGACY_ROOT . '/lib/ExtraFields.php');
include_once(LEGACY_ROOT . '/lib/Calendar.php');
include_once(LEGACY_ROOT . '/lib/SavedLists.php');
include_once(LEGACY_ROOT . '/lib/EmailTemplates.php');
include_once(LEGACY_ROOT . '/lib/DocumentToText.php');
include_once(LEGACY_ROOT . '/lib/DatabaseSearch.php');
include_once(LEGACY_ROOT . '/lib/CommonErrors.php');
include_once(LEGACY_ROOT . '/lib/License.php');
include_once(LEGACY_ROOT . '/lib/ParseUtility.php');
include_once(LEGACY_ROOT . '/lib/Questionnaire.php');
include_once(LEGACY_ROOT . '/lib/Tags.php');
include_once(LEGACY_ROOT . '/lib/Search.php');

class CandidatesUI extends UserInterface
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
        $this->_moduleDirectory = 'candidates';
        $this->_moduleName = 'candidates';
        $this->_moduleTabText = 'Candidates';
        $this->_subTabs = array(
            'Add Candidate'     => CATSUtility::getIndexName() . '?m=candidates&amp;a=add*al=' . ACCESS_LEVEL_EDIT . '@candidates.add',
            'Search Candidates' => CATSUtility::getIndexName() . '?m=candidates&amp;a=search'
        );
    }


    public function handleRequest()
    {
        if (!eval(Hooks::get('CANDIDATES_HANDLE_REQUEST'))) return;
        
        $action = $this->getAction();
        switch ($action)
        {
            case 'show':
                if ($this->getUserAccessLevel('candidates.show') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->show();
                break;

            case 'add':
                if ($this->getUserAccessLevel('candidates.add') < ACCESS_LEVEL_EDIT)
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
                if ($this->getUserAccessLevel('candidates.edit') < ACCESS_LEVEL_EDIT)
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
                if ($this->getUserAccessLevel('candidates.delete') < ACCESS_LEVEL_DELETE)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->onDelete();
                break;

            case 'search':
                if ($this->getUserAccessLevel('candidates.search') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                include_once(LEGACY_ROOT . '/lib/Search.php');

                if ($this->isGetBack())
                {
                    $this->onSearch();
                }
                else
                {
                    $this->search();
                }

                break;

            case 'viewResume':
                if ($this->getUserAccessLevel('candidates.viewResume') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                include_once(LEGACY_ROOT . '/lib/Search.php');

                $this->viewResume();
                break;

            /*
             * Search for a job order (in the modal window) for which to
             * consider a candidate.
             */
            case 'considerForJobSearch':
                if ($this->getUserAccessLevel('candidates.search') < ACCESS_LEVEL_EDIT)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                include_once(LEGACY_ROOT . '/lib/Search.php');

                $this->considerForJobSearch();

                break;

            /*
             * Add candidate to pipeline after selecting a job order for which
             * to consider a candidate (in the modal window).
             */
            case 'addToPipeline':
                if ($this->getUserAccessLevel('pipelines.addToPipeline') < ACCESS_LEVEL_EDIT)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->onAddToPipeline();
                break;

            case 'addCandidateTags':
                if ($this->getUserAccessLevel('candidates.addCandidateTags') < ACCESS_LEVEL_EDIT )
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                if ($this->isPostBack())
                {
                    $this->onAddCandidateTags();
                }
                else
                {
                    $this->addCandidateTags();
                }
            	break;
                
            /* Change candidate-joborder status. */
            case 'addActivityChangeStatus':
                if ($this->getUserAccessLevel('pipelines.addActivityChangeStatus') < ACCESS_LEVEL_EDIT)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                if ($this->isPostBack())
                {
                    $this->onAddActivityChangeStatus();
                }
                else
                {
                    $this->addActivityChangeStatus();
                }

                break;

            /* Remove a candidate from a pipeline. */
            case 'removeFromPipeline':
                if ($this->getUserAccessLevel('pipelines.removeFromPipeline') < ACCESS_LEVEL_DELETE)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->onRemoveFromPipeline();
                break;

            case 'addEditImage':
                if ($this->getUserAccessLevel('candidates.addEditImage') < ACCESS_LEVEL_EDIT)
                {
                    CommonErrors::fatalModal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                if ($this->isPostBack())
                {
                    $this->onAddEditImage();
                }
                else
                {
                    $this->addEditImage();
                }

                break;

            /* Add an attachment to the candidate. */
            case 'createAttachment':
                if ($this->getUserAccessLevel('candidates.createAttachment') < ACCESS_LEVEL_EDIT)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }

                include_once(LEGACY_ROOT . '/lib/DocumentToText.php');

                if ($this->isPostBack())
                {
                    $this->onCreateAttachment();
                }
                else
                {
                    $this->createAttachment();
                }

                break;

            /* Administrators can hide a candidate from a site with this action. */
            case 'administrativeHideShow':
                if ($this->getUserAccessLevel('candidates.hidden') < ACCESS_LEVEL_MULTI_SA)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->administrativeHideShow();
                break;

            /* Delete a candidate attachment */
            case 'deleteAttachment':
                if ($this->getUserAccessLevel('candidates.deleteAttachment') < ACCESS_LEVEL_DELETE)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->onDeleteAttachment();
                break;

            /* Hot List Page */
            /* FIXME: function savedList() missing
            case 'savedLists':
                if ($this->getUserAccessLevel('candidates.savedLists') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->savedList();
                break;
            */

            case 'emailCandidates':
                if ($this->getUserAccessLevel('candidates.emailCandidates') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                if ($this->getUserAccessLevel('candidates.emailCandidates') < ACCESS_LEVEL_SA)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Sorry, but you are not allowed to send e-mails.');
                }
                $this->onEmailCandidates();
                break;

            case 'show_questionnaire':
                if ($this->getUserAccessLevel('candidates.show_questionnaire') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->onShowQuestionnaire();
                break;

            case 'linkDuplicate':
                if ($this->getUserAccessLevel('candidates.duplicates') < ACCESS_LEVEL_SA)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->findDuplicateCandidateSearch();
                break;

             /* Merge two duplicate candidates into the older one */
            case 'merge':
                if ($this->getUserAccessLevel('candidates.duplicates') < ACCESS_LEVEL_SA)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->mergeDuplicates();
                break;
                
            case 'mergeInfo':
                if ($this->getUserAccessLevel('candidates.duplicates') < ACCESS_LEVEL_SA)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->mergeDuplicatesInfo();
                break;
            
            /* Remove duplicity warning from a new candidate */
            case 'removeDuplicity':
                if ($this->getUserAccessLevel('candidates.duplicates') < ACCESS_LEVEL_SA)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->removeDuplicity();
                break;
            
            case 'addDuplicates':
                if ($this->getUserAccessLevel('candidates.duplicates') < ACCESS_LEVEL_SA)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->addDuplicates();
                break;

            /* Main candidates page. */
            case 'listByView':
            default:
                if ($this->getUserAccessLevel('candidates.list') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->listByView();
                break;
        }
    }

    
    
    
    /*
     * Called by external modules for adding candidates.
     */
    public function publicAddCandidate($isModal, $transferURI, $moduleDirectory)
    {
        if ($this->getUserAccessLevel('candidates.add') < ACCESS_LEVEL_EDIT)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
        }

        $candidateID = $this->_addCandidate($isModal, $moduleDirectory);

        if ($candidateID <= 0)
        {
            CommonErrors::fatalModal(COMMONERROR_RECORDERROR, $this, 'Failed to add candidate.');
        }

        $transferURI = str_replace(
            '__CANDIDATE_ID__', $candidateID, $transferURI
        );
        CATSUtility::transferRelativeURI($transferURI);
    }


    /*
     * Called by external modules for processing the log activity / change
     * status dialog.
     */
    public function publicAddActivityChangeStatus($isJobOrdersMode, $regardingID, $moduleDirectory)
    {
        if ($this->getUserAccessLevel('pipelines.addActivityChangeStatus') < ACCESS_LEVEL_EDIT)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
        }

        $this->_AddActivityChangeStatus(
            $isJobOrdersMode, $regardingID, $moduleDirectory
        );
    }

    /*
     * Called by handleRequest() to process loading the list / main page.
     */
    private function listByView($errMessage = '')
    {
        // Log message that shows up on the top of the list page
        $topLog = '';

        $dataGridProperties = DataGrid::getRecentParamaters("candidates:candidatesListByViewDataGrid");

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

        $dataGrid = DataGrid::get("candidates:candidatesListByViewDataGrid", $dataGridProperties);

        $candidates = new Candidates($this->_siteID);
        $this->_template->assign('totalCandidates', $candidates->getCount());

        $this->_template->assign('active', $this);
        $this->_template->assign('dataGrid', $dataGrid);
        $this->_template->assign('userID', $_SESSION['CATS']->getUserID());
        $this->_template->assign('errMessage', $errMessage);
        $this->_template->assign('topLog', $topLog);
        $this->_template->assign('tagsRS', $tagsRS);

        if (!eval(Hooks::get('CANDIDATE_LIST_BY_VIEW'))) return;

        $this->_template->display('./modules/candidates/Candidates.tpl');
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

        if ($data['isAdminHidden'] == 1 && $this->getUserAccessLevel('candidates.hidden') < ACCESS_LEVEL_MULTI_SA)
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
        if ($this->getUserAccessLevel('candidates.priviledgedUser') < ACCESS_LEVEL_DEMO)
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

        $lists = $candidates->getListsForCandidate($candidateID);

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
        $this->_template->assign('lists', $lists);

        $this->_template->display('./modules/candidates/Show.tpl');
        
        if (!eval(Hooks::get('CANDIDATE_SHOW'))) return;
       
    }

    /*
     * Called by handleRequest() to process loading the add page.
     *
     * The user could have already added a resume to the system
     * before this page is displayed.  They could have indicated
     * that they want to use a bulk resume, or a text resume
     * stored in the  session.  These ocourances are looked
     * for here, and the Add.tpl file displays the results.
     */
    private function add($contents = '', $fields = array())
    {
        $candidates = new Candidates($this->_siteID);

        /* Get possible sources. */
        $sourcesRS = $candidates->getPossibleSources();
        $sourcesString = ListEditor::getStringFromList($sourcesRS, 'name');

        /* Get extra fields. */
        $extraFieldRS = $candidates->extraFields->getValuesForAdd();

        /* Get passed variables. */
        $preassignedFields = $_GET;
        if (count($fields) > 0)
        {
            $preassignedFields = array_merge($preassignedFields, $fields);
        }

        /* Get preattached resume, if any. */
        if ($this->isRequiredIDValid('attachmentID', $_GET))
        {
            $associatedAttachment = $_GET['attachmentID'];

            $attachments = new Attachments($this->_siteID);
            $associatedAttachmentRS = $attachments->get($associatedAttachment);

            /* Show an attachment icon based on the document's file type. */
            $attachmentIcon = strtolower(
                FileUtility::getAttachmentIcon(
                    $associatedAttachmentRS['originalFilename']
                )
            );

            $associatedAttachmentRS['attachmentIcon'] = $attachmentIcon;

            /* If the text field has any text, show a preview icon. */
            if ($associatedAttachmentRS['hasText'])
            {
                $associatedAttachmentRS['previewLink'] = sprintf(
                    '<a href="#" onclick="window.open(\'%s?m=candidates&amp;a=viewResume&amp;attachmentID=%s\', \'viewResume\', \'scrollbars=1,width=800,height=760\')"><img width="15" height="15" style="border: none;" src="images/popup.gif" alt="(Preview)" /></a>',
                    CATSUtility::getIndexName(),
                    $associatedAttachmentRS['attachmentID']
                );
            }
            else
            {
                $associatedAttachmentRS['previewLink'] = '&nbsp;';
            }
        }
        else
        {
            $associatedAttachment = 0;
            $associatedAttachmentRS = array();
        }

        /* Get preuploaded resume text, if any */
        if ($this->isRequiredIDValid('resumeTextID', $_GET, true))
        {
            $associatedTextResume = $_SESSION['CATS']->retrieveData($_GET['resumeTextID']);
        }
        else
        {
            $associatedTextResume = false;
        }

        /* Get preuploaded resume file (unattached), if any */
        if ($this->isRequiredIDValid('resumeFileID', $_GET, true))
        {
            $associatedFileResume = $_SESSION['CATS']->retrieveData($_GET['resumeFileID']);
            $associatedFileResume['id'] = $_GET['resumeFileID'];
            $associatedFileResume['attachmentIcon'] = strtolower(
                FileUtility::getAttachmentIcon(
                    $associatedFileResume['filename']
                )
            );
        }
        else
        {
            $associatedFileResume = false;
        }

        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();


        if (!eval(Hooks::get('CANDIDATE_ADD'))) return;

        /* If parsing is not enabled server-wide, say so. */
        if (!LicenseUtility::isParsingEnabled())
        {
            $isParsingEnabled = false;
        }
        /* For CATS Toolbar, if e-mail has been sent and it wasn't set by
         * parser, it's toolbar and it needs the old format.
         */
        else if (!isset($preassignedFields['email']))
        {
            $isParsingEnabled = true;
        }
        else if (empty($preassignedFields['email']))
        {
            $isParsingEnabled = true;
        }
        else if (isset($preassignedFields['isFromParser']) && $preassignedFields['isFromParser'])
        {
            $isParsingEnabled = true;
        }
        else
        {
            $isParsingEnabled = false;
        }

        if (is_array($parsingStatus = LicenseUtility::getParsingStatus()) &&
            isset($parsingStatus['parseLimit']))
        {
            $parsingStatus['parseLimit'] = $parsingStatus['parseLimit'] - 1;
        }

        $this->_template->assign('parsingStatus', $parsingStatus);
        $this->_template->assign('isParsingEnabled', $isParsingEnabled);
        $this->_template->assign('contents', $contents);
        $this->_template->assign('extraFieldRS', $extraFieldRS);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Add Candidate');
        $this->_template->assign('sourcesRS', $sourcesRS);
        $this->_template->assign('sourcesString', $sourcesString);
        $this->_template->assign('preassignedFields', $preassignedFields);
        $this->_template->assign('associatedAttachment', $associatedAttachment);
        $this->_template->assign('associatedAttachmentRS', $associatedAttachmentRS);
        $this->_template->assign('associatedTextResume', $associatedTextResume);
        $this->_template->assign('associatedFileResume', $associatedFileResume);
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);
        $this->_template->assign('isModal', false);

        /* REMEMBER TO ALSO UPDATE JobOrdersUI::addCandidateModal() IF
         * APPLICABLE.
         */
        $this->_template->display('./modules/candidates/Add.tpl');
    }

    public function checkParsingFunctions()
    {
        if (LicenseUtility::isParsingEnabled())
        {
            if (isset($_POST['documentText'])) $contents = $_POST['documentText'];
            else $contents = '';

            // Retain all field data since this isn't done over AJAX (yet)
            $fields = array(
                'firstName'       => $this->getSanitisedInput('firstName', $_POST),
                'middleName'      => $this->getSanitisedInput('middleName', $_POST),
                'lastName'        => $this->getSanitisedInput('lastName', $_POST),
                'email1'          => $this->getSanitisedInput('email1', $_POST),
                'email2'          => $this->getSanitisedInput('email2', $_POST),
                'phoneHome'       => $this->getSanitisedInput('phoneHome', $_POST),
                'phoneCell'       => $this->getSanitisedInput('phoneCell', $_POST),
                'phoneWork'       => $this->getSanitisedInput('phoneWork', $_POST),
                'address'         => $this->getSanitisedInput('address', $_POST),
                'city'            => $this->getSanitisedInput('city', $_POST),
                'state'           => $this->getSanitisedInput('state', $_POST),
                'zip'             => $this->getSanitisedInput('zip', $_POST),
                'source'          => $this->getTrimmedInput('source', $_POST),
                'keySkills'       => $this->getSanitisedInput('keySkills', $_POST),
                'currentEmployer' => $this->getSanitisedInput('currentEmployer', $_POST),
                'currentPay'      => $this->getSanitisedInput('currentPay', $_POST),
                'desiredPay'      => $this->getSanitisedInput('desiredPay', $_POST),
                'notes'           => $this->getSanitisedInput('notes', $_POST),
                'canRelocate'     => $this->getSanitisedInput('canRelocate', $_POST),
                'webSite'         => $this->getSanitisedInput('webSite', $_POST),
                'bestTimeToCall'  => $this->getSanitisedInput('bestTimeToCall', $_POST),
                'gender'          => $this->getTrimmedInput('gender', $_POST),
                'race'            => $this->getTrimmedInput('race', $_POST),
                'veteran'         => $this->getTrimmedInput('veteran', $_POST),
                'disability'      => $this->getTrimmedInput('disability', $_POST),
                'documentTempFile'=> $this->getTrimmedInput('documentTempFile', $_POST),
                'isFromParser'    => true
            );

            /**
             * User is loading a resume from a document. Convert it to a string and paste the contents
             * into the textarea field on the add candidate page after validating the form.
             */
            if (isset($_POST['loadDocument']) && $_POST['loadDocument'] == 'true')
            {
                // Get the upload file from the post data
                $newFileName = FileUtility::getUploadFileFromPost(
                    $this->_siteID, // The site ID
                    'addcandidate', // Sub-directory of the site's upload folder
                    'documentFile'  // The DOM "name" from the <input> element
                );

                if ($newFileName !== false)
                {
                    // Get the relative path to the file (to perform operations on)
                    $newFilePath = FileUtility::getUploadFilePath(
                        $this->_siteID, // The site ID
                        'addcandidate', // The sub-directory
                        $newFileName
                    );

                    $documentToText = new DocumentToText();
                    $doctype = $documentToText->getDocumentType($newFilePath);

                    if ($documentToText->convert($newFilePath, $doctype))
                    {
                        $contents = $documentToText->getString();
                        if ($doctype == DOCUMENT_TYPE_DOC)
                        {
                            $contents = str_replace('|', "\n", $contents);
                        }

                        // Remove things like _rDOTr for ., etc.
                        $contents = DatabaseSearch::fulltextDecode($contents);
                    }
                    else
                    {
                        $contents = @file_get_contents($newFilePath);
                        $fields['binaryData'] = true;
                    }

                    // Save the short (un-pathed) name
                    $fields['documentTempFile'] = $newFileName;

                    if (isset($_COOKIE['CATS_SP_TEMP_FILE']) && ($oldFile = $_COOKIE['CATS_SP_TEMP_FILE']) != '' &&
                        strcasecmp($oldFile, $newFileName))
                    {
                        // Get the safe, old file they uploaded and didn't use (if exists) and delete
                        $oldFilePath = FileUtility::getUploadFilePath($this->_siteID, 'addcandidate', $oldFile);

                        if ($oldFilePath !== false)
                        {
                            @unlink($oldFilePath);
                        }
                    }

                    // Prevent users from creating more than 1 temp file for single parsing (sp)
                    setcookie('CATS_SP_TEMP_FILE', $newFileName, time() + (60*60*24*7));
                }

                if (isset($_POST['parseDocument']) && $_POST['parseDocument'] == 'true' && $contents != '')
                {
                    // ...
                }
                else
                {
                    return array($contents, $fields);
                }
            }

            /**
             * User is parsing the contents of the textarea field on the add candidate page.
             */
            if (isset($_POST['parseDocument']) && $_POST['parseDocument'] == 'true' && $contents != '')
            {
                $pu = new ParseUtility();
                if ($res = $pu->documentParse('untitled', strlen($contents), '', $contents))
                {
                    if (isset($res['first_name'])) $fields['firstName'] = $res['first_name']; else $fields['firstName'] = '';
                    if (isset($res['last_name'])) $fields['lastName'] = $res['last_name']; else $fields['lastName'] = '';
                    $fields['middleName'] = '';
                    if (isset($res['email_address'])) $fields['email1'] = $res['email_address']; else $fields['email1'] = '';
                    $fields['email2'] = '';
                    if (isset($res['us_address'])) $fields['address'] = $res['us_address']; else $fields['address'] = '';
                    if (isset($res['city'])) $fields['city'] = $res['city']; else $fields['city'] = '';
                    if (isset($res['state'])) $fields['state'] = $res['state']; else $fields['state'] = '';
                    if (isset($res['zip_code'])) $fields['zip'] = $res['zip_code']; else $fields['zip'] = '';
                    if (isset($res['phone_number'])) $fields['phoneHome'] = $res['phone_number']; else $fields['phoneHome'] = '';
                    $fields['phoneWork'] = $fields['phoneCell'] = '';
                    if (isset($res['skills'])) $fields['keySkills'] = str_replace("\n", ' ', str_replace('"', '\'\'', $res['skills']));
                }

                return array($contents, $fields);
            }
        }

        return false;
    }

    /*
     * Called by handleRequest() to process saving / submitting the add page.
     */
    private function onAdd()
    {
        if (is_array($mp = $this->checkParsingFunctions()))
        {
            return $this->add($mp[0], $mp[1]);
        }

        $candidateID = $this->_addCandidate(false);

        if ($candidateID <= 0)
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to add candidate.');
        }

        $activityEntries = new ActivityEntries($this->_siteID);
        $activityID = $activityEntries->add(
            $candidateID,
            DATA_ITEM_CANDIDATE,
            400,
            'Added a new candidate.',
            $this->_userID
        );

        CATSUtility::transferRelativeURI(
            'm=candidates&a=show&candidateID=' . $candidateID
        );
    }

    /*
     * Called by handleRequest() to process loading the edit page.
     */
    private function edit()
    {
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('candidateID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
        }

        $candidateID = $_GET['candidateID'];

        $candidates = new Candidates($this->_siteID);
        $data = $candidates->getForEditing($candidateID);

        /* Bail out if we got an empty result set. */
        if (empty($data))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'The specified candidate ID could not be found.');
        }

        if ($data['isAdminHidden'] == 1 && $this->getUserAccessLevel('candidates.hidden') < ACCESS_LEVEL_MULTI_SA)
        {
            $this->listByView('This candidate is hidden - only a CATS Administrator can unlock the candidate.');
            return;
        }

        $users = new Users($this->_siteID);
        $usersRS = $users->getSelectList();

        /* Add an MRU entry. */
        $_SESSION['CATS']->getMRU()->addEntry(
            DATA_ITEM_CANDIDATE, $candidateID, $data['firstName'] . ' ' . $data['lastName']
        );

        /* Get extra fields. */
        $extraFieldRS = $candidates->extraFields->getValuesForEdit($candidateID);

        /* Get possible sources. */
        $sourcesRS = $candidates->getPossibleSources();
        $sourcesString = ListEditor::getStringFromList($sourcesRS, 'name');

        /* Is current source a possible source? */
        // FIXME: Use array search functions!
        $sourceInRS = false;
        foreach ($sourcesRS as $sourceData)
        {
            if ($sourceData['name'] == $data['source'])
            {
                $sourceInRS = true;
            }
        }

        // TODO - improve for permission who can send email
        if ($this->getUserAccessLevel('candidates.emailCandidates') == ACCESS_LEVEL_DEMO)
        {
            $canEmail = false;
        }
        else
        {
            $canEmail = true;
        }

        $emailTemplates = new EmailTemplates($this->_siteID);
        $statusChangeTemplateRS = $emailTemplates->getByTag(
            'EMAIL_TEMPLATE_OWNERSHIPASSIGNCANDIDATE'
        );
        if ($statusChangeTemplateRS['disabled'] == 1)
        {
            $emailTemplateDisabled = true;
        }
        else
        {
            $emailTemplateDisabled = false;
        }

        /* Date format for DateInput()s. */
        if ($_SESSION['CATS']->isDateDMY())
        {
            $data['dateAvailableMDY'] = DateUtility::convert(
                '-', $data['dateAvailable'], DATE_FORMAT_DDMMYY, DATE_FORMAT_MMDDYY
            );
        }
        else
        {
            $data['dateAvailableMDY'] = $data['dateAvailable'];
        }

        if (!eval(Hooks::get('CANDIDATE_EDIT'))) return;

        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();

        $this->_template->assign('active', $this);
        $this->_template->assign('data', $data);
        $this->_template->assign('usersRS', $usersRS);
        $this->_template->assign('extraFieldRS', $extraFieldRS);
        $this->_template->assign('sourcesRS', $sourcesRS);
        $this->_template->assign('sourcesString', $sourcesString);
        $this->_template->assign('sourceInRS', $sourceInRS);
        $this->_template->assign('candidateID', $candidateID);
        $this->_template->assign('canEmail', $canEmail);
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);
        $this->_template->assign('emailTemplateDisabled', $emailTemplateDisabled);
        $this->_template->display('./modules/candidates/Edit.tpl');
    }

    /*
     * Called by handleRequest() to process saving / submitting the edit page.
     */
    private function onEdit()
    {
        $candidates = new Candidates($this->_siteID);

        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('candidateID', $_POST))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
            return;
        }

        /* Bail out if we don't have a valid owner user ID. */
        if (!$this->isOptionalIDValid('owner', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid owner user ID.');
        }

        /* Bail out if we received an invalid availability date; if not, go
         * ahead and convert the date to MySQL format.
         */
        $dateAvailable = $this->getTrimmedInput('dateAvailable', $_POST);
        if (!empty($dateAvailable))
        {
            if (!DateUtility::validate('-', $dateAvailable, DATE_FORMAT_MMDDYY))
            {
                CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Invalid availability date.');
            }

            /* Convert start_date to something MySQL can understand. */
            $dateAvailable = DateUtility::convert(
                '-', $dateAvailable, DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD
            );
        }

        $formattedPhoneHome = StringUtility::extractPhoneNumber(
            $this->getSanitisedInput('phoneHome', $_POST)
        );
        if (!empty($formattedPhoneHome))
        {
            $phoneHome = $formattedPhoneHome;
        }
        else
        {
            $phoneHome = $this->getSanitisedInput('phoneHome', $_POST);
        }

        $formattedPhoneCell = StringUtility::extractPhoneNumber(
            $this->getSanitisedInput('phoneCell', $_POST)
        );
        if (!empty($formattedPhoneCell))
        {
            $phoneCell = $formattedPhoneCell;
        }
        else
        {
            $phoneCell = $this->getSanitisedInput('phoneCell', $_POST);
        }

        $formattedPhoneWork = StringUtility::extractPhoneNumber(
            $this->getSanitisedInput('phoneWork', $_POST)
        );
        if (!empty($formattedPhoneWork))
        {
            $phoneWork = $formattedPhoneWork;
        }
        else
        {
            $phoneWork = $this->getSanitisedInput('phoneWork', $_POST);
        }

        $candidateID = $_POST['candidateID'];
        $owner       = $_POST['owner'];

        /* Can Relocate */
        $canRelocate = $this->isChecked('canRelocate', $_POST);

        $isHot = $this->isChecked('isHot', $_POST);

        /* Change ownership email? */
        if ($this->isChecked('ownershipChange', $_POST) && $owner > 0)
        {
            $candidateDetails = $candidates->get($candidateID);

            $users = new Users($this->_siteID);
            $ownerDetails = $users->get($owner);

            if (!empty($ownerDetails))
            {
                $emailAddress = $ownerDetails['email'];

                /* Get the change status email template. */
                $emailTemplates = new EmailTemplates($this->_siteID);
                $statusChangeTemplateRS = $emailTemplates->getByTag(
                    'EMAIL_TEMPLATE_OWNERSHIPASSIGNCANDIDATE'
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
                    '%CANDOWNER%',
                    '%CANDFIRSTNAME%',
                    '%CANDFULLNAME%',
                    '%CANDCATSURL%'
                );
                $replacementStrings = array(
                    $ownerDetails['fullName'],
                    $candidateDetails['firstName'],
                    $candidateDetails['firstName'] . ' ' . $candidateDetails['lastName'],
                    '<a href="http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) . '?m=candidates&amp;a=show&amp;candidateID=' . $candidateID . '">'.
                        'http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) . '?m=candidates&amp;a=show&amp;candidateID=' . $candidateID . '</a>'
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

        $isActive        = $this->isChecked('isActive', $_POST);
        $firstName       = $this->getSanitisedInput('firstName', $_POST);
        $middleName      = $this->getSanitisedInput('middleName', $_POST);
        $lastName        = $this->getSanitisedInput('lastName', $_POST);
        $email1          = $this->getSanitisedInput('email1', $_POST);
        $email2          = $this->getSanitisedInput('email2', $_POST);
        $address         = $this->getSanitisedInput('address', $_POST);
        $city            = $this->getSanitisedInput('city', $_POST);
        $state           = $this->getSanitisedInput('state', $_POST);
        $zip             = $this->getSanitisedInput('zip', $_POST);
        $source          = $this->getSanitisedInput('source', $_POST);
        $keySkills       = $this->getSanitisedInput('keySkills', $_POST);
        $currentEmployer = $this->getSanitisedInput('currentEmployer', $_POST);
        $currentPay      = $this->getSanitisedInput('currentPay', $_POST);
        $desiredPay      = $this->getSanitisedInput('desiredPay', $_POST);
        $notes           = $this->getSanitisedInput('notes', $_POST);
        $webSite         = $this->getSanitisedInput('webSite', $_POST);
        $bestTimeToCall  = $this->getTrimmedInput('bestTimeToCall', $_POST);
        $gender          = $this->getTrimmedInput('gender', $_POST);
        $race            = $this->getTrimmedInput('race', $_POST);
        $veteran         = $this->getTrimmedInput('veteran', $_POST);
        $disability      = $this->getTrimmedInput('disability', $_POST);

        /* Candidate source list editor. */
        $sourceCSV = $this->getTrimmedInput('sourceCSV', $_POST);

        /* Bail out if any of the required fields are empty. */
        if (empty($firstName) || empty($lastName))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
        }

        if (!eval(Hooks::get('CANDIDATE_ON_EDIT_PRE'))) return;

        /* Update the candidate record. */
        $updateSuccess = $candidates->update(
            $candidateID,
            $isActive,
            $firstName,
            $middleName,
            $lastName,
            $email1,
            $email2,
            $phoneHome,
            $phoneCell,
            $phoneWork,
            $address,
            $city,
            $state,
            $zip,
            $source,
            $keySkills,
            $dateAvailable,
            $currentEmployer,
            $canRelocate,
            $currentPay,
            $desiredPay,
            $notes,
            $webSite,
            $bestTimeToCall,
            $owner,
            $isHot,
            $email,
            $emailAddress,
            $gender,
            $race,
            $veteran,
            $disability
        );
        if (!$updateSuccess)
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to update candidate.');
        }

        /* Update extra fields. */
        $candidates->extraFields->setValuesOnEdit($candidateID);

        /* Update possible source list */
        $sources = $candidates->getPossibleSources();
        $sourcesDifferences = ListEditor::getDifferencesFromList(
            $sources, 'name', 'sourceID', $sourceCSV
        );

        $candidates->updatePossibleSources($sourcesDifferences);

        if (!eval(Hooks::get('CANDIDATE_ON_EDIT_POST'))) return;

        CATSUtility::transferRelativeURI(
            'm=candidates&a=show&candidateID=' . $candidateID
        );
    }

    /*
     * Called by handleRequest() to process deleting a candidate.
     */
    private function onDelete()
    {
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('candidateID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
        }

        $candidateID = $_GET['candidateID'];

        if (!eval(Hooks::get('CANDIDATE_DELETE'))) return;

        $candidates = new Candidates($this->_siteID);
        $candidates->delete($candidateID);

        /* Delete the MRU entry if present. */
        $_SESSION['CATS']->getMRU()->removeEntry(
            DATA_ITEM_CANDIDATE, $candidateID
        );

        CATSUtility::transferRelativeURI('m=candidates&a=listByView');
    }

    /*
     * Called by handleRequest() to handle processing an "Add to a Job Order
     * Pipeline" search and displaying the results in the modal dialog, or
     * to show the initial dialog.
     */
    private function considerForJobSearch($candidateIDArray = array())
    {
        
        /* Get list of candidates. */
        if (isset($_REQUEST['candidateIDArrayStored']) && $this->isRequiredIDValid('candidateIDArrayStored', $_REQUEST, true))
        {
            $candidateIDArray = $_SESSION['CATS']->retrieveData($_REQUEST['candidateIDArrayStored']);
        }
        else if($this->isRequiredIDValid('candidateID', $_REQUEST))
        {
            $candidateIDArray = array($_REQUEST['candidateID']);
        }
        else if ($candidateIDArray === array())
        {
            $dataGrid = DataGrid::getFromRequest();

            $candidateIDArray = $dataGrid->getExportIDs();
        }

        if (!is_array($candidateIDArray))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid variable type.');
            return;
        }

        /* Validate each ID */
        foreach ($candidateIDArray as $index => $candidateID)
        {
            if (!$this->isRequiredIDValid($index, $candidateIDArray))
            {
                echo('&'.$candidateID.'>');

                CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
                return;
            }
        }

        /* Bail out to prevent an error if the POST string doesn't even contain
         * a field named 'wildCardString' at all.
         */
        if (!isset($_POST['wildCardString']) && isset($_POST['mode']))
        {
            CommonErrors::fatal(COMMONERROR_WILDCARDSTRING, $this, 'No wild card string specified.');
        }

        $query = $this->getTrimmedInput('wildCardString', $_POST);
        $mode  = $this->getTrimmedInput('mode', $_POST);

        /* Execute the search. */
        $search = new SearchJobOrders($this->_siteID);
        switch ($mode)
        {
            case 'searchByJobTitle':
                $rs = $search->byTitle($query, 'title', 'ASC', true);
                $resultsMode = true;
                break;

            case 'searchByCompanyName':
                $rs = $search->byCompanyName($query, 'title', 'ASC', true);
                $resultsMode = true;
                break;

            default:
                $rs = $search->recentlyModified('DESC', true, 5);
                $resultsMode = false;
                break;
        }

        $pipelines = new Pipelines($this->_siteID);
        $pipelinesRS = $pipelines->getCandidatePipeline($candidateIDArray[0]);

        foreach ($rs as $rowIndex => $row)
        {
            if (ResultSetUtility::findRowByColumnValue($pipelinesRS,
                'jobOrderID', $row['jobOrderID']) !== false && count($candidateIDArray) == 1)
            {
                $rs[$rowIndex]['inPipeline'] = true;
            }
            else
            {
                $rs[$rowIndex]['inPipeline'] = false;
            }

            /* Convert '00-00-00' dates to empty strings. */
            $rs[$rowIndex]['startDate'] = DateUtility::fixZeroDate(
                $row['startDate']
            );

            if ($row['isHot'] == 1)
            {
                $rs[$rowIndex]['linkClass'] = 'jobLinkHot';
            }
            else
            {
                $rs[$rowIndex]['linkClass'] = 'jobLinkCold';
            }

            $rs[$rowIndex]['recruiterAbbrName'] = StringUtility::makeInitialName(
                $row['recruiterFirstName'],
                $row['recruiterLastName'],
                false,
                LAST_NAME_MAXLEN
            );

            $rs[$rowIndex]['ownerAbbrName'] = StringUtility::makeInitialName(
                $row['ownerFirstName'],
                $row['ownerLastName'],
                false,
                LAST_NAME_MAXLEN
            );
        }

        if (!eval(Hooks::get('CANDIDATE_ON_CONSIDER_FOR_JOB_SEARCH'))) return;

        $this->_template->assign('rs', $rs);
        $this->_template->assign('isFinishedMode', false);
        $this->_template->assign('isResultsMode', $resultsMode);
        $this->_template->assign('candidateIDArray', $candidateIDArray);
        $this->_template->assign('candidateIDArrayStored', $_SESSION['CATS']->storeData($candidateIDArray));
        $this->_template->display('./modules/candidates/ConsiderSearchModal.tpl');
    }

    /*
     * Called by handleRequest() to process adding a candidate to a pipeline
     * in the modal dialog.
     */
    private function onAddToPipeline()
    {
        /* Bail out if we don't have a valid job order ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        if (isset($_GET['candidateID']))
        {
            /* Bail out if we don't have a valid candidate ID. */
            if (!$this->isRequiredIDValid('candidateID', $_GET))
            {
                CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
            }

            $candidateIDArray = array($_GET['candidateID']);
        }
        else
        {
            if (!isset($_REQUEST['candidateIDArrayStored']) || !$this->isRequiredIDValid('candidateIDArrayStored', $_REQUEST, true))
            {
                CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid candidateIDArrayStored parameter.');
                return;
            }

            $candidateIDArray = $_SESSION['CATS']->retrieveData($_REQUEST['candidateIDArrayStored']);

            if (!is_array($candidateIDArray))
            {
                CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid variable type.');
                return;
            }

            /* Validate each ID */
            foreach ($candidateIDArray as $index => $candidateID)
            {
                if (!$this->isRequiredIDValid($index, $candidateIDArray))
                {
                    echo ($dataItemID);

                    CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
                    return;
                }
            }
        }


        $jobOrderID  = $_GET['jobOrderID'];

        if (!eval(Hooks::get('CANDIDATE_ADD_TO_PIPELINE_PRE'))) return;

        $pipelines = new Pipelines($this->_siteID);
        $activityEntries = new ActivityEntries($this->_siteID);

        /* Drop candidate ID's who are already in the pipeline */
        $pipelinesRS = $pipelines->getJobOrderPipeline($jobOrderID);

        foreach($pipelinesRS as $data)
        {
            $arrayPos = array_search($data['candidateID'], $candidateIDArray);
            if ($arrayPos !== false)
            {
                unset($candidateIDArray[$arrayPos]);
            }
        }

        /* Add to pipeline */
        foreach($candidateIDArray as $candidateID)
        {
            if (!$pipelines->add($candidateID, $jobOrderID, $this->_userID))
            {
                CommonErrors::fatalModal(COMMONERROR_RECORDERROR, $this, 'Failed to add candidate to Job Order.');
            }

            $activityID = $activityEntries->add(
                $candidateID,
                DATA_ITEM_CANDIDATE,
                400,
                'Added candidate to job order.',
                $this->_userID,
                $jobOrderID
            );

            if (!eval(Hooks::get('CANDIDATE_ADD_TO_PIPELINE_POST_IND'))) return;
        }

        if (!eval(Hooks::get('CANDIDATE_ADD_TO_PIPELINE_POST'))) return;

        $this->_template->assign('isFinishedMode', true);
        $this->_template->assign('jobOrderID', $jobOrderID);
        $this->_template->assign('candidateIDArray', $candidateIDArray);
        $this->_template->display(
            './modules/candidates/ConsiderSearchModal.tpl'
        );
    }

    private function addActivityChangeStatus()
    {
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('candidateID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
        }

        /* Bail out if we don't have a valid job order ID. */
        if (!$this->isOptionalIDValid('jobOrderID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        $selectedJobOrderID = $_GET['jobOrderID'];
        $candidateID        = $_GET['candidateID'];

        $candidates = new Candidates($this->_siteID);
        $candidateData = $candidates->get($candidateID);

        /* Bail out if we got an empty result set. */
        if (empty($candidateData))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this);
            return;
        }

        $pipelines = new Pipelines($this->_siteID);
        $pipelineRS = $pipelines->getCandidatePipeline($candidateID);

        $statusRS = $pipelines->getStatusesForPicking();

        if ($selectedJobOrderID != -1)
        {
            $selectedStatusID = ResultSetUtility::getColumnValueByIDValue(
                $pipelineRS, 'jobOrderID', $selectedJobOrderID, 'statusID'
            );
        }
        else
        {
            $selectedStatusID = -1;
        }

        /* Get the change status email template. */
        $emailTemplates = new EmailTemplates($this->_siteID);
        $statusChangeTemplateRS = $emailTemplates->getByTag(
            'EMAIL_TEMPLATE_STATUSCHANGE'
        );
        if (empty($statusChangeTemplateRS) ||
            empty($statusChangeTemplateRS['textReplaced']))
        {
            $statusChangeTemplate = '';
            $emailDisabled = '1';
        }
        else
        {
            $statusChangeTemplate = $statusChangeTemplateRS['textReplaced'];
            $emailDisabled = $statusChangeTemplateRS['disabled'];
        }

        /* Replace e-mail template variables. '%CANDSTATUS%', '%JBODTITLE%',
         * '%JBODCLIENT%' are replaced by JavaScript.
         */
        $stringsToFind = array(
            '%CANDOWNER%',
            '%CANDFIRSTNAME%',
            '%CANDFULLNAME%'
        );
        $replacementStrings = array(
            $candidateData['ownerFullName'],
            $candidateData['firstName'],
            $candidateData['firstName'] . ' ' . $candidateData['lastName'],
            $candidateData['firstName'],
            $candidateData['firstName']
        );
        $statusChangeTemplate = str_replace(
            $stringsToFind,
            $replacementStrings,
            $statusChangeTemplate
        );

        /* Are we in "Only Schedule Event" mode? */
        $onlyScheduleEvent = $this->isChecked('onlyScheduleEvent', $_GET);

        $calendar = new Calendar($this->_siteID);
        $calendarEventTypes = $calendar->getAllEventTypes();

        if (!eval(Hooks::get('CANDIDATE_ADD_ACTIVITY_CHANGE_STATUS'))) return;

        if (SystemUtility::isSchedulerEnabled() && !$_SESSION['CATS']->isDemo())
        {
            $allowEventReminders = true;
        }
        else
        {
            $allowEventReminders = false;
        }

        $this->_template->assign('candidateID', $candidateID);
        $this->_template->assign('pipelineRS', $pipelineRS);
        $this->_template->assign('statusRS', $statusRS);
        $this->_template->assign('selectedJobOrderID', $selectedJobOrderID);
        $this->_template->assign('selectedStatusID', $selectedStatusID);
        $this->_template->assign('allowEventReminders', $allowEventReminders);
        $this->_template->assign('userEmail', $_SESSION['CATS']->getEmail());
        $this->_template->assign('calendarEventTypes', $calendarEventTypes);
        $this->_template->assign('statusChangeTemplate', $statusChangeTemplate);
        $this->_template->assign('onlyScheduleEvent', $onlyScheduleEvent);
        $this->_template->assign('emailDisabled', $emailDisabled);
        $this->_template->assign('isFinishedMode', false);
        $this->_template->assign('isJobOrdersMode', false);
        $this->_template->display(
            './modules/candidates/AddActivityChangeStatusModal.tpl'
        );
    }

    private function onAddCandidateTags()
    {
        /* Bail out if we don't have a valid regardingjob order ID. */
        if (!$this->isOptionalIDValid('candidateID', $_POST))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid Candidate ID.');
        }

        /* Bail out if we don't have a valid regardingjob order ID. */
        if (!isset($_POST['candidate_tags']) || !is_array($_POST['candidate_tags']))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid Tag ID.');
        }

        $candidateID	= $_POST['candidateID'];
        $tagIDs			= $_POST['candidate_tags'];
        
        $tags = new Tags($this->_siteID);
        $tags->AddTagsToCandidate($candidateID, $tagIDs);
        
        $this->_template->assign('candidateID', $candidateID);
        $this->_template->assign('isFinishedMode', true);
        $this->_template->display(
            './modules/candidates/AssignCandidateTagModal.tpl'
        );
        
    }
    
   
    private function addCandidateTags()
    {
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('candidateID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
        }

        $candidateID        = $_GET['candidateID'];

        $candidates = new Candidates($this->_siteID);
        $candidateData = $candidates->get($candidateID);

        /* Bail out if we got an empty result set. */
        if (empty($candidateData))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this);
            return;
            /*$this->fatalModal(
                'The specified candidate ID could not be found.'
            );*/
        }
        
        $tags = new Tags($this->_siteID);
        $tagsRS = $tags->getAll();
        
        $this->_template->assign('candidateID', $candidateID);
        $this->_template->assign('assignedTags', $tags->getCandidateTagsID($candidateID));
        $this->_template->assign('isFinishedMode', false);
        
        $this->_template->assign('tagsRS', $tagsRS);
        $this->_template->display(
            './modules/candidates/AssignCandidateTagModal.tpl'
        );
        
    }
    
    
    private function onAddActivityChangeStatus()
    {
        /* Bail out if we don't have a valid regardingjob order ID. */
        if (!$this->isOptionalIDValid('regardingID', $_POST))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        $regardingID = $_POST['regardingID'];

        $this->_addActivityChangeStatus(false, $regardingID);
    }

    /*
     * Called by handleRequest() to process removing a candidate from the
     * pipeline for a job order.
     */
    private function onRemoveFromPipeline()
    {
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('candidateID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
        }

        /* Bail out if we don't have a valid job order ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        $candidateID = $_GET['candidateID'];
        $jobOrderID  = $_GET['jobOrderID'];

        if (!eval(Hooks::get('CANDIDATE_REMOVE_FROM_PIPELINE_PRE'))) return;

        $pipelines = new Pipelines($this->_siteID);
        $pipelines->remove($candidateID, $jobOrderID);

        if (!eval(Hooks::get('CANDIDATE_REMOVE_FROM_PIPELINE_POST'))) return;

        CATSUtility::transferRelativeURI(
            'm=candidates&a=show&candidateID=' . $candidateID
        );
    }

    /*
     * Called by handleRequest() to process loading the search page.
     */
    private function search()
    {
        $savedSearches = new SavedSearches($this->_siteID);
        $savedSearchRS = $savedSearches->get(DATA_ITEM_CANDIDATE);

        if (!eval(Hooks::get('CANDIDATE_SEARCH'))) return;

        $this->_template->assign('wildCardString', '');
        $this->_template->assign('savedSearchRS', $savedSearchRS);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Search Candidates');
        $this->_template->assign('isResultsMode', false);
        $this->_template->assign('isResumeMode', false);
        $this->_template->assign('resumeWildCardString', '');
        $this->_template->assign('keySkillsWildCardString', '');
        $this->_template->assign('fullNameWildCardString', '');
        $this->_template->assign('phoneNumberWildCardString', '');
        $this->_template->assign('mode', '');
        $this->_template->display('./modules/candidates/Search.tpl');
    }

    /*
     * Called by handleRequest() to process displaying the search results.
     */
    private function onSearch()
    {
        /* Bail out to prevent an error if the GET string doesn't even contain
         * a field named 'wildCardString' at all.
         */
        if (!isset($_GET['wildCardString']))
        {
            $this->listByView('No wild card string specified.');
            return;
        }

        $query = trim($_GET['wildCardString']);

        /* Initialize stored wildcard strings to safe default values. */
        $resumeWildCardString      = '';
        $keySkillsWildCardString   = '';
        $phoneNumberWildCardString = '';
        $fullNameWildCardString    = '';

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
            CANDIDATES_PER_PAGE, $currentPage, $this->_siteID
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

        $candidates = new Candidates($this->_siteID);

        /* Get our current searching mode. */
        $mode = $this->getTrimmedInput('mode', $_GET);

        /* Execute the search. */
        $search = new SearchCandidates($this->_siteID);
        switch ($mode)
        {
            case 'searchByFullName':
                $rs = $search->byFullName($query, $sortBy, $sortDirection);

                foreach ($rs as $rowIndex => $row)
                {
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

                    $rsResume = $candidates->getResumes($row['candidateID']);
                    if (isset($rsResume[0]))
                    {
                        $rs[$rowIndex]['resumeID'] = $rsResume[0]['attachmentID'];
                    }
                }

                $isResumeMode = false;

                $fullNameWildCardString = $query;
                break;

            case 'searchByKeySkills':
                $rs = $search->byKeySkills($query, $sortBy, $sortDirection);

                foreach ($rs as $rowIndex => $row)
                {
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

                    $rsResume = $candidates->getResumes($row['candidateID']);
                    if (isset($rsResume[0]))
                    {
                        $rs[$rowIndex]['resumeID'] = $rsResume[0]['attachmentID'];
                    }
                }

                $isResumeMode = false;

                $keySkillsWildCardString = $query;

                break;

            case 'searchByResume':
                $searchPager = new SearchByResumePager(
                    20,
                    $currentPage,
                    $this->_siteID,
                    $query,
                    $sortBy,
                    $sortDirection
                );

                $baseURL = 'm=candidates&amp;a=search&amp;getback=getback&amp;mode=searchByResume&amp;wildCardString='
                    . urlencode($query)
                    . '&amp;searchByResume=Search';

                $searchPager->setSortByParameters(
                    $baseURL, $sortBy, $sortDirection
                );

                $rs = $searchPager->getPage();

                $currentPage = $searchPager->getCurrentPage();
                $totalPages  = $searchPager->getTotalPages();

                $pageStart = $searchPager->getThisPageStartRow() + 1;

                if (($searchPager->getThisPageStartRow() + 20) <= $searchPager->getTotalRows())
                {
                    $pageEnd = $searchPager->getThisPageStartRow() + 20;
                }
                else
                {
                    $pageEnd = $searchPager->getTotalRows();
                }

                foreach ($rs as $rowIndex => $row)
                {
                    $rs[$rowIndex]['excerpt'] = SearchUtility::searchExcerpt(
                        $query, $row['text']
                    );

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

                $isResumeMode = true;

                $this->_template->assign('active', $this);
                $this->_template->assign('currentPage', $currentPage);
                $this->_template->assign('pageStart', $pageStart);
                $this->_template->assign('totalResults', $searchPager->getTotalRows());
                $this->_template->assign('pageEnd', $pageEnd);
                $this->_template->assign('totalPages', $totalPages);

                $resumeWildCardString = $query;
                break;

            case 'phoneNumber':
                $rs = $search->byPhone($query, $sortBy, $sortDirection);

                foreach ($rs as $rowIndex => $row)
                {
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

                    $rsResume = $candidates->getResumes($row['candidateID']);
                    if (isset($rsResume[0]))
                    {
                        $rs[$rowIndex]['resumeID'] = $rsResume[0]['attachmentID'];
                    }
                }

                $isResumeMode = false;

                $phoneNumberWildCardString = $query;
                break;

            default:
                $this->listByView('Invalid search mode.');
                return;
                break;
        }

        $candidateIDs = implode(',', ResultSetUtility::getColumnValues($rs, 'candidateID'));
        $exportForm = ExportUtility::getForm(
            DATA_ITEM_CANDIDATE, $candidateIDs, 32, 9
        );

        if (!eval(Hooks::get('CANDIDATE_ON_SEARCH'))) return;

        /* Save the search. */
        $savedSearches = new SavedSearches($this->_siteID);
        $savedSearches->add(
            DATA_ITEM_CANDIDATE,
            $query,
            $_SERVER['REQUEST_URI'],
            false
        );
        $savedSearchRS = $savedSearches->get(DATA_ITEM_CANDIDATE);

        $this->_template->assign('savedSearchRS', $savedSearchRS);
        $this->_template->assign('exportForm', $exportForm);
        $this->_template->assign('active', $this);
        $this->_template->assign('rs', $rs);
        $this->_template->assign('pager', $searchPager);
        $this->_template->assign('isResultsMode', true);
        $this->_template->assign('isResumeMode', $isResumeMode);
        $this->_template->assign('wildCardString', $query);
        $this->_template->assign('resumeWildCardString', $resumeWildCardString);
        $this->_template->assign('keySkillsWildCardString', $keySkillsWildCardString);
        $this->_template->assign('fullNameWildCardString', $fullNameWildCardString);
        $this->_template->assign('phoneNumberWildCardString', $phoneNumberWildCardString);
        $this->_template->assign('mode', $mode);
        $this->_template->display('./modules/candidates/Search.tpl');
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

    private function addEditImage()
    {
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('candidateID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
        }

        $candidateID = $_GET['candidateID'];

        $attachments = new Attachments($this->_siteID);
        $attachmentsRS = $attachments->getAll(
            DATA_ITEM_CANDIDATE, $candidateID
        );

        if (!eval(Hooks::get('CANDIDATE_ADD_EDIT_IMAGE'))) return;

        $this->_template->assign('isFinishedMode', false);
        $this->_template->assign('candidateID', $candidateID);
        $this->_template->assign('attachmentsRS', $attachmentsRS);
        $this->_template->display(
            './modules/candidates/CreateImageAttachmentModal.tpl'
        );
    }

    /*
     * Called by handleRequest() to process creating an attachment.
     */
    private function onAddEditImage()
    {
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('candidateID', $_POST))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
        }

        $candidateID = $_POST['candidateID'];

        if (!eval(Hooks::get('CANDIDATE_ON_ADD_EDIT_IMAGE_PRE'))) return;

        $attachmentCreator = new AttachmentCreator($this->_siteID);
        $attachmentCreator->createFromUpload(
            DATA_ITEM_CANDIDATE, $candidateID, 'file', true, false
        );

        if ($attachmentCreator->isError())
        {
            CommonErrors::fatalModal(COMMONERROR_FILEERROR, $this, $attachmentCreator->getError());
            return;
            //$this->fatalModal($attachmentCreator->getError());
        }

        if (!eval(Hooks::get('CANDIDATE_ON_ADD_EDIT_IMAGE_POST'))) return;

        $this->_template->assign('isFinishedMode', true);
        $this->_template->assign('candidateID', $candidateID);
        $this->_template->display(
            './modules/candidates/CreateImageAttachmentModal.tpl'
        );
    }

    /*
     * Called by handleRequest() to process loading the create attachment
     * modal dialog.
     */
    private function createAttachment()
    {
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('candidateID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
        }

        $candidateID = $_GET['candidateID'];

        if (!eval(Hooks::get('CANDIDATE_CREATE_ATTACHMENT'))) return;

        $this->_template->assign('isFinishedMode', false);
        $this->_template->assign('candidateID', $candidateID);
        $this->_template->display(
            './modules/candidates/CreateAttachmentModal.tpl'
        );
    }

    /*
     * Called by handleRequest() to process creating an attachment.
     */
    private function onCreateAttachment()
    {
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('candidateID', $_POST))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
        }

        /* Bail out if we don't have a valid resume status. */
        if (!$this->isRequiredIDValid('resume', $_POST, true) ||
            $_POST['resume'] < 0 || $_POST['resume'] > 1)
        {
            CommonErrors::fatalModal(COMMONERROR_RECORDERROR, $this, 'Invalid resume status.');
        }

        $candidateID = $_POST['candidateID'];

        if ($_POST['resume'] == '1')
        {
            $isResume = true;
        }
        else
        {
            $isResume = false;
        }

        if (!eval(Hooks::get('CANDIDATE_ON_CREATE_ATTACHMENT_PRE'))) return;

        $attachmentCreator = new AttachmentCreator($this->_siteID);
        $attachmentCreator->createFromUpload(
            DATA_ITEM_CANDIDATE, $candidateID, 'file', false, $isResume
        );

        if ($attachmentCreator->isError())
        {
            CommonErrors::fatalModal(COMMONERROR_FILEERROR, $this, $attachmentCreator->getError());
            return;
            //$this->fatalModal($attachmentCreator->getError());
        }

        if ($attachmentCreator->duplicatesOccurred())
        {
            $this->fatalModal(
                'This attachment has already been added to this candidate.'
            );
        }

        $isTextExtractionError = $attachmentCreator->isTextExtractionError();
        $textExtractionErrorMessage = $attachmentCreator->getTextExtractionError();
        $resumeText = $attachmentCreator->getExtractedText();

        if (!eval(Hooks::get('CANDIDATE_ON_CREATE_ATTACHMENT_POST'))) return;

        $this->_template->assign('resumeText', $resumeText);
        $this->_template->assign('isFinishedMode', true);
        $this->_template->assign('candidateID', $candidateID);
        $this->_template->display(
            './modules/candidates/CreateAttachmentModal.tpl'
        );
    }

    /*
     * Called by handleRequest() to process deleting an attachment.
     */
    private function onDeleteAttachment()
    {
        /* Bail out if we don't have a valid attachment ID. */
        if (!$this->isRequiredIDValid('attachmentID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid attachment ID.');
        }

        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('candidateID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
        }

        $candidateID  = $_GET['candidateID'];
        $attachmentID = $_GET['attachmentID'];

        if (!eval(Hooks::get('CANDIDATE_ON_DELETE_ATTACHMENT_PRE'))) return;

        $attachments = new Attachments($this->_siteID);
        $attachments->delete($attachmentID);

        if (!eval(Hooks::get('CANDIDATE_ON_DELETE_ATTACHMENT_POST'))) return;

        CATSUtility::transferRelativeURI(
            'm=candidates&a=show&candidateID=' . $candidateID
        );
    }

    //TODO: Document me.
    //Only accessable by MSA users - hides this job order from everybody by
    private function administrativeHideShow()
    {
        /* Bail out if we don't have a valid joborder ID. */
        if (!$this->isRequiredIDValid('candidateID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid Job Order ID.');
        }

        /* Bail out if we don't have a valid status ID. */
        if (!$this->isRequiredIDValid('state', $_GET, true))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid state ID.');
        }

        $candidateID = $_GET['candidateID'];

        // FIXME: Checkbox?
        $state = (boolean) $_GET['state'];

        $candidates = new Candidates($this->_siteID);
        $candidates->administrativeHideShow($candidateID, $state);

        CATSUtility::transferRelativeURI('m=candidates&a=show&candidateID='.$candidateID);
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

    /**
     * Adds a candidate. This is factored out for code clarity.
     *
     * @param boolean is modal window
     * @param string module directory
     * @return integer candidate ID
     */
    private function _addCandidate($isModal, $directoryOverride = '')
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

        /* Modal override for fatal() calls. */
        if ($isModal)
        {
            $fatal = 'fatalModal';
        }
        else
        {
            $fatal = 'fatal';
        }

        /* Bail out if we received an invalid availability date; if not, go
         * ahead and convert the date to MySQL format.
         */
        $dateAvailable = $this->getTrimmedInput('dateAvailable', $_POST);
        if (!empty($dateAvailable))
        {
            if (!DateUtility::validate('-', $dateAvailable, DATE_FORMAT_MMDDYY))
            {
                $this->$fatal('Invalid availability date.', $moduleDirectory);
            }

            /* Convert start_date to something MySQL can understand. */
            $dateAvailable = DateUtility::convert(
                '-', $dateAvailable, DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD
            );
        }

        $formattedPhoneHome = StringUtility::extractPhoneNumber(
            $this->getTrimmedInput('phoneHome', $_POST)
        );
        if (!empty($formattedPhoneHome))
        {
            $phoneHome = $formattedPhoneHome;
        }
        else
        {
            $phoneHome = $this->getTrimmedInput('phoneHome', $_POST);
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

        /* Can Relocate */
        $canRelocate = $this->isChecked('canRelocate', $_POST);

        $lastName        = $this->getTrimmedInput('lastName', $_POST);
        $middleName      = $this->getTrimmedInput('middleName', $_POST);
        $firstName       = $this->getTrimmedInput('firstName', $_POST);
        $email1          = $this->getTrimmedInput('email1', $_POST);
        $email2          = $this->getTrimmedInput('email2', $_POST);
        $address         = $this->getTrimmedInput('address', $_POST);
        $city            = $this->getTrimmedInput('city', $_POST);
        $state           = $this->getTrimmedInput('state', $_POST);
        $zip             = $this->getTrimmedInput('zip', $_POST);
        $source          = $this->getTrimmedInput('source', $_POST);
        $keySkills       = $this->getTrimmedInput('keySkills', $_POST);
        $currentEmployer = $this->getTrimmedInput('currentEmployer', $_POST);
        $currentPay      = $this->getTrimmedInput('currentPay', $_POST);
        $desiredPay      = $this->getTrimmedInput('desiredPay', $_POST);
        $notes           = $this->getTrimmedInput('notes', $_POST);
        $webSite         = $this->getTrimmedInput('webSite', $_POST);
        $bestTimeToCall  = $this->getTrimmedInput('bestTimeToCall', $_POST);
        $gender          = $this->getTrimmedInput('gender', $_POST);
        $race            = $this->getTrimmedInput('race', $_POST);
        $veteran         = $this->getTrimmedInput('veteran', $_POST);
        $disability      = $this->getTrimmedInput('disability', $_POST);

        /* Candidate source list editor. */
        $sourceCSV = $this->getTrimmedInput('sourceCSV', $_POST);

        /* Text resume. */
        $textResumeBlock = $this->getTrimmedInput('textResumeBlock', $_POST);
        $textResumeFilename = $this->getTrimmedInput('textResumeFilename', $_POST);

        /* File resume. */
        $associatedFileResumeID = $this->getTrimmedInput('associatedbFileResumeID', $_POST);

        /* Bail out if any of the required fields are empty. */
        if (empty($firstName) || empty($lastName))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this);
        }

        if (!eval(Hooks::get('CANDIDATE_ON_ADD_PRE'))) return;

        $candidates = new Candidates($this->_siteID);
        
        $duplicatesID = $candidates->checkDuplicity($firstName, $middleName, $lastName, $email1, $email2, $phoneHome, $phoneCell, $phoneWork, $address, $city);
        
        $candidateID = $candidates->add(
            $firstName,
            $middleName,
            $lastName,
            $email1,
            $email2,
            $phoneHome,
            $phoneCell,
            $phoneWork,
            $address,
            $city,
            $state,
            $zip,
            $source,
            $keySkills,
            $dateAvailable,
            $currentEmployer,
            $canRelocate,
            $currentPay,
            $desiredPay,
            $notes,
            $webSite,
            $bestTimeToCall,
            $this->_userID,
            $this->_userID,
            $gender,
            $race,
            $veteran,
            $disability
        );

        
        if ($candidateID <= 0)
        {
            return $candidateID;
        }
        
        if(sizeof($duplicatesID) > 0)
        {
            $candidates->addDuplicates($candidateID, $duplicatesID);
        }
        
        /* Update extra fields. */
        $candidates->extraFields->setValuesOnEdit($candidateID);

        /* Update possible source list. */
        $sources = $candidates->getPossibleSources();
        $sourcesDifferences = ListEditor::getDifferencesFromList(
            $sources, 'name', 'sourceID', $sourceCSV
        );
        $candidates->updatePossibleSources($sourcesDifferences);

        /* Associate an exsisting resume if the user created a candidate with one. (Bulk) */
        if (isset($_POST['associatedAttachment']))
        {
            $attachmentID = $_POST['associatedAttachment'];

            $attachments = new Attachments($this->_siteID);
            $attachments->setDataItemID($attachmentID, $candidateID, DATA_ITEM_CANDIDATE);
        }

        /* Attach a resume if the user uploaded one. (http POST) */
        /* NOTE: This function cannot be called if parsing is enabled */
        else if (isset($_FILES['file']) && !empty($_FILES['file']['name']))
        {
            if (!eval(Hooks::get('CANDIDATE_ON_CREATE_ATTACHMENT_PRE'))) return;

            $attachmentCreator = new AttachmentCreator($this->_siteID);
            $attachmentCreator->createFromUpload(
                DATA_ITEM_CANDIDATE, $candidateID, 'file', false, true
            );

            if ($attachmentCreator->isError())
            {
                CommonErrors::fatal(COMMONERROR_FILEERROR, $this, $attachmentCreator->getError());
            }


            if ($attachmentCreator->duplicatesOccurred())
            {
                $this->listByView(
                    'This attachment has already been added to this candidate.'
                );
                return;
            }

            $isTextExtractionError = $attachmentCreator->isTextExtractionError();
            $textExtractionErrorMessage = $attachmentCreator->getTextExtractionError();

            // FIXME: Show parse errors!

            if (!eval(Hooks::get('CANDIDATE_ON_CREATE_ATTACHMENT_POST'))) return;
        }

        /**
         * User has loaded and/or parsed a resume. The attachment is saved in a temporary
         * file already and just needs to be attached. The attachment has also successfully
         * been DocumentToText converted, so we know it's a good file.
         */
        else if (LicenseUtility::isParsingEnabled())
        {
            /**
             * Description: User clicks "browse" and selects a resume file. User doesn't click
             * upload. The resume file is STILL uploaded.
             * Controversial: User uploads a resume, parses, etc. User selects a new file with
             * "Browse" but doesn't click "Upload". New file is accepted.
             * It's technically correct either way, I'm opting for the "use whats in "file"
             * box over what's already uploaded method to avoid losing resumes on candidate
             * additions.
             */
            $newFile = FileUtility::getUploadFileFromPost($this->_siteID, 'addcandidate', 'documentFile');

            if ($newFile !== false)
            {
                $newFilePath = FileUtility::getUploadFilePath($this->_siteID, 'addcandidate', $newFile);

                $tempFile = $newFile;
                $tempFullPath = $newFilePath;
            }
            else
            {
                $attachmentCreated = false;

                $tempFile = false;
                $tempFullPath = false;

                if (isset($_POST['documentTempFile']) && !empty($_POST['documentTempFile']))
                {
                    $tempFile = $_POST['documentTempFile'];
                    // Get the path of the file they uploaded already to attach
                    $tempFullPath = FileUtility::getUploadFilePath(
                        $this->_siteID,   // ID of the containing site
                        'addcandidate',   // Sub-directory in their storage
                        $tempFile         // Name of the file (not pathed)
                    );
                }
            }

            if ($tempFile !== false && $tempFullPath !== false)
            {
                if (!eval(Hooks::get('CANDIDATE_ON_CREATE_ATTACHMENT_PRE'))) return;

                $attachmentCreator = new AttachmentCreator($this->_siteID);
                $attachmentCreator->createFromFile(
                    DATA_ITEM_CANDIDATE, $candidateID, $tempFullPath, $tempFile, '', true, true
                );

                if ($attachmentCreator->isError())
                {
                    CommonErrors::fatal(COMMONERROR_FILEERROR, $this, $attachmentCreator->getError());
                }


                if ($attachmentCreator->duplicatesOccurred())
                {
                    $this->listByView(
                        'This attachment has already been added to this candidate.'
                    );
                    return;
                }

                $isTextExtractionError = $attachmentCreator->isTextExtractionError();
                $textExtractionErrorMessage = $attachmentCreator->getTextExtractionError();

                if (!eval(Hooks::get('CANDIDATE_ON_CREATE_ATTACHMENT_POST'))) return;

                // Remove the cleanup cookie since the file no longer exists
                setcookie('CATS_SP_TEMP_FILE', '');

                $attachmentCreated = true;
            }

            if (!$attachmentCreated && isset($_POST['documentText']) && !empty($_POST['documentText']))
            {
                // Resume was pasted into the form and not uploaded from a file

                if (!eval(Hooks::get('CANDIDATE_ON_CREATE_ATTACHMENT_PRE'))) return;

                $attachmentCreator = new AttachmentCreator($this->_siteID);
                $attachmentCreator->createFromText(
                    DATA_ITEM_CANDIDATE, $candidateID, $_POST['documentText'], 'MyResume.txt', true
                );

                if ($attachmentCreator->isError())
                {
                    CommonErrors::fatal(COMMONERROR_FILEERROR, $this, $attachmentCreator->getError());
                }

                if ($attachmentCreator->duplicatesOccurred())
                {
                    $this->listByView(
                        'This attachment has already been added to this candidate.'
                    );
                    return;
                }

                if (!eval(Hooks::get('CANDIDATE_ON_CREATE_ATTACHMENT_POST'))) return;
            }
        }

        /* Create a text resume if the user posted one. (automated tool) */
        else if (!empty($textResumeBlock))
        {
            $attachmentCreator = new AttachmentCreator($this->_siteID);
            $attachmentCreator->createFromText(
                DATA_ITEM_CANDIDATE, $candidateID, $textResumeBlock, $textResumeFilename, true
            );

            if ($attachmentCreator->isError())
            {
                CommonErrors::fatal(COMMONERROR_FILEERROR, $this, $attachmentCreator->getError());
                return;
                //$this->fatal($attachmentCreator->getError());
            }
            $isTextExtractionError = $attachmentCreator->isTextExtractionError();
            $textExtractionErrorMessage = $attachmentCreator->getTextExtractionError();

            // FIXME: Show parse errors!
        }

        if (!eval(Hooks::get('CANDIDATE_ON_ADD_POST'))) return;

        return $candidateID;
    }

    /**
     * Processes an Add Activity / Change Status form and displays
     * candidates/AddActivityChangeStatusModal.tpl. This is factored out
     * for code clarity.
     *
     * @param boolean from joborders module perspective
     * @param integer "regarding" job order ID or -1
     * @param string module directory
     * @return void
     */
    private function _addActivityChangeStatus($isJobOrdersMode, $regardingID,
        $directoryOverride = '')
    {
        $notificationHTML = '';

        $pipelines = new Pipelines($this->_siteID);
        $statusRS = $pipelines->getStatusesForPicking();

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
        if (!$this->isRequiredIDValid('candidateID', $_POST))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
        }

        /* Do we have a valid status ID. */
        if (!$this->isOptionalIDValid('statusID', $_POST))
        {
            $statusID = -1;
        }
        else
        {
            $statusID = $_POST['statusID'];
            if($statusID == PIPELINE_STATUS_PLACED)
            {
                $jobOrders = new JobOrders($this->_siteID);
                $canBeHired = $jobOrders->checkOpenings($regardingID);
                if(!$canBeHired)
                {
                    $this->fatalModal(
                        'This job order has been filled. Cannot assign the status Placed to any other candidate.'
                    );
                }
            }
        }

        $candidateID = $_POST['candidateID'];

        if (!eval(Hooks::get('CANDIDATE_ON_ADD_ACTIVITY_CHANGE_STATUS_PRE'))) return;

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

            // FIXME: Move this to a highlighter-method? */
            if (strpos($activityNote, 'Status change: ') === 0)
            {
                foreach ($statusRS as $data)
                {
                    $activityNote = StringUtility::replaceOnce(
                        $data['status'],
                        '<span style="color: #ff6c00;">' . $data['status'] . '</span>',
                        $activityNote
                    );
                }
            }

            /* Add the activity entry. */
            $activityEntries = new ActivityEntries($this->_siteID);
            $activityID = $activityEntries->add(
                $candidateID,
                DATA_ITEM_CANDIDATE,
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

        if ($regardingID <= 0 || $statusID == -1)
        {
            $statusChanged = false;
            $oldStatusDescription = '';
            $newStatusDescription = '';
        }
        else
        {
            $data = $pipelines->get($candidateID, $regardingID);

            /* Bail out if we got an empty result set. */
            if (empty($data))
            {
                $this->fatalModal(
                    'The specified pipeline entry could not be found.'
                );
            }

            $validStatus = ResultSetUtility::findRowByColumnValue(
                $statusRS, 'statusID', $statusID
            );

            /* If the status is invalid or unchanged, don't mess with it. */
            if ($validStatus === false || $statusID == $data['status'])
            {
                $oldStatusDescription = '';
                $newStatusDescription = '';
                $statusChanged = false;
            }
            else
            {
                $oldStatusDescription = $data['status'];
                $newStatusDescription = ResultSetUtility::getColumnValueByIDValue(
                    $statusRS, 'statusID', $statusID, 'status'
                );

                if ($oldStatusDescription != $newStatusDescription)
                {
                    $statusChanged = true;
                }
                else
                {
                    $statusChanged = false;
                }
            }

            if ($statusChanged && $this->isChecked('triggerEmail', $_POST))
            {
                $customMessage = $this->getTrimmedInput('customMessage', $_POST);

                // FIXME: Actually validate the e-mail address?
                if (empty($data['candidateEmail']))
                {
                    $email = '';
                    $notificationHTML = '<p><span class="bold">Error:</span> An e-mail notification'
                        . ' could not be sent to the candidate because the candidate'
                        . ' does not have a valid e-mail address.</p>';
                }
                else if (empty($customMessage))
                {
                    $email = '';
                    $notificationHTML = '<p><span class="bold">Error:</span> An e-mail notification'
                        . ' will not be sent because the message text specified was blank.</p>';
                }
                else if ($this->getUserAccessLevel('candidates.emailCandidates') == ACCESS_LEVEL_DEMO)
                {
                    $email = '';
                    $notificationHTML = '<p><span class="bold">Error:</span> Demo users can not send'
                        . ' E-Mails.  No E-Mail was sent.</p>';
                }
                else
                {
                    $email = $data['candidateEmail'];
                    $notificationHTML = '<p>An e-mail notification has been sent to the candidate.</p>';
                }
            }
            else
            {
                $email = '';
                $customMessage = '';
                $notificationHTML = '<p>No e-mail notification has been sent to the candidate.</p>';
            }

            /* Set the pipeline entry's status, but don't send e-mails for now. */
            $pipelines->setStatus(
                $candidateID, $regardingID, $statusID, $email, $customMessage
            );

            /* If status = placed, and open positions > 0, reduce number of open positions by one. */
            if ($statusID == PIPELINE_STATUS_PLACED && is_numeric($data['openingsAvailable']) && $data['openingsAvailable'] > 0)
            {
                $jobOrders = new JobOrders($this->_siteID);
                $jobOrders->updateOpeningsAvailable($regardingID, $data['openingsAvailable'] - 1);
            }
            
            /* If status is changed from placed to something else, increase number of open positions by one. */
            if ($statusID != PIPELINE_STATUS_PLACED && $data['statusID'] == PIPELINE_STATUS_PLACED)
            {
                $jobOrders = new JobOrders($this->_siteID);
                $jobOrders->updateOpeningsAvailable($regardingID, $data['openingsAvailable'] + 1);
            }
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
            $duration = $this->getTrimmedInput('duration', $_POST);;

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
                    $this->fatalModal(
                        'Invalid meridiem value.', $moduleDirectory
                    );
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
                CommonErrors::fatalModal(COMMONERROR_MISSINGFIELDS, $this);
                return;
                /*$this->fatalModal(
                    'Required fields are missing.', $moduleDirectory
                );*/
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
                $candidateID, DATA_ITEM_CANDIDATE, $eventJobOrderID, $title,
                $duration, $reminderEnabled, $reminderEmail, $reminderTime,
                $publicEntry, $_SESSION['CATS']->getTimeZoneOffset()
            );

            if ($eventID <= 0)
            {
                $this->fatalModal(
                    'Failed to add calendar event.', $moduleDirectory
                );
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

        if (!$statusChanged && !$activityAdded && !$eventScheduled)
        {
            $changesMade = false;
        }
        else
        {
            $changesMade = true;
        }

        if (!eval(Hooks::get('CANDIDATE_ON_ADD_ACTIVITY_CHANGE_STATUS_POST'))) return;

        $this->_template->assign('candidateID', $candidateID);
        $this->_template->assign('regardingID', $regardingID);
        $this->_template->assign('oldStatusDescription', $oldStatusDescription);
        $this->_template->assign('newStatusDescription', $newStatusDescription);
        $this->_template->assign('statusChanged', $statusChanged);
        $this->_template->assign('activityAdded', $activityAdded);
        $this->_template->assign('activityDescription', $activityNote);
        $this->_template->assign('activityType', $activityTypeDescription);
        $this->_template->assign('eventScheduled', $eventScheduled);
        $this->_template->assign('eventHTML', $eventHTML);
        $this->_template->assign('notificationHTML', $notificationHTML);
        $this->_template->assign('onlyScheduleEvent', $onlyScheduleEvent);
        $this->_template->assign('changesMade', $changesMade);
        $this->_template->assign('isFinishedMode', true);
        $this->_template->assign('isJobOrdersMode', $isJobOrdersMode);
        $this->_template->display(
            './modules/candidates/AddActivityChangeStatusModal.tpl'
        );
    }

    /*
     * Sends mass emails from the datagrid
     */
    private function onEmailCandidates()
    {
        if (isset($_POST['postback']))
        {
            $emailTo = $_POST['emailTo'];
            $emailSubject = $_POST['emailSubject'];
            $emailBody = $_POST['emailBody'];

            $tmpDestination = explode(', ', $emailTo);
            $destination = array();
            foreach($tmpDestination as $emailDest)
            {
                $destination[] = array($emailDest, $emailDest);
            }

            $mailer = new Mailer(CATS_ADMIN_SITE);
            
            if($_POST['emailTemplate'] == "-1")
            {
                $mailerStatus = $mailer->send(
                    array($_SESSION['CATS']->getEmail(), $_SESSION['CATS']->getEmail()),
                    $destination,
                    $emailSubject,
                    $emailBody,
                    true,
                    true
                );
            }
            else
            {
                $emailTemplates = new EmailTemplates($this->_siteID);
                $candidates = new Candidates($this->_siteID);
                
                $emailsToIDs = $_POST['candidateID'];
                $candidateIDs = array();
                foreach($emailsToIDs as $email)
                {
                    $temp = explode('=', $email);
                    $candidateIDs[$temp[0]] = $temp[1];
                }
                foreach($candidateIDs as $email => $ID)
                {
                    $candidateData = $candidates->get($ID);
                    $emailTextSubstituted = $emailTemplates->replaceVariables($emailBody);
                    $stringsToFind = array(
                        '%CANDOWNER%',
                        '%CANDFIRSTNAME%',
                        '%CANDFULLNAME%'
                    );
                    $replacementStrings = array(
                            $candidateData['ownerFullName'],
                            $candidateData['firstName'],
                            $candidateData['candidateFullName']
                    );
                    $emailTextSubstituted = str_replace(
                            $stringsToFind,
                            $replacementStrings,
                            $emailTextSubstituted
                    );
                    
                    $mailerStatus = $mailer->sendToOne(
                        array($email, $candidateData['candidateFullName']), 
                        $emailSubject,
                        $emailTextSubstituted,
                        true
                    );
                }
            }

            $this->_template->assign('active', $this);
            $this->_template->assign('success', true);
            $this->_template->assign('success_to', $emailTo);
            $this->_template->display('./modules/candidates/SendEmail.tpl');
        }
        else
        {
            $dataGrid = DataGrid::getFromRequest();

            $candidateIDs = $dataGrid->getExportIDs();

            /* Validate each ID */
            foreach ($candidateIDs as $index => $candidateID)
            {
                if (!$this->isRequiredIDValid($index, $candidateIDs))
                {
                    CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
                    return;
                }
            }

            $db_str = implode(", ", $candidateIDs);

            $db = DatabaseConnection::getInstance();

            $rs = $db->getAllAssoc(sprintf(
                'SELECT candidate_id, first_name, last_name, email1, email2 '
                . 'FROM candidate '
                . 'WHERE candidate_id IN (%s)',
                $db_str
            ));

            $emailTemplates = new EmailTemplates($this->_siteID);
            $emailTemplatesRS = $emailTemplates->getAllCustom();

            //$this->_template->assign('privledgedUser', $privledgedUser);
            $this->_template->assign('active', $this);
            $this->_template->assign('success', false);
            $this->_template->assign('emailTemplatesRS', $emailTemplatesRS);
            $this->_template->assign('recipients', $rs);
            $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());
            $this->_template->display('./modules/candidates/SendEmail.tpl');
        }
    }

    private function onShowQuestionnaire()
    {
        $candidateID = isset($_GET[$id='candidateID']) ? $_GET[$id] : false;
        $title = isset($_GET[$id='questionnaireTitle']) ? urldecode($_GET[$id]) : false;
        $printOption = isset($_GET[$id='print']) ? $_GET[$id] : '';
        $printValue = !strcasecmp($printOption, 'yes') ? true : false;

        if (!$candidateID || !$title)
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Bad Server Information.');
        }

        $candidates = new Candidates($this->_siteID);
        $cData = $candidates->get($candidateID);

        $questionnaire = new Questionnaire($this->_siteID);
        $qData = $questionnaire->getCandidateQuestionnaire($candidateID, $title);

        $attachment = new Attachments($this->_siteID);
        $attachments = $attachment->getAll(DATA_ITEM_CANDIDATE, $candidateID);
        if (!empty($attachments))
        {
            $resume = $candidates->getResume($attachments[0]['attachmentID']);
            $this->_template->assign('resumeText', str_replace("\n", "<br \>\n", htmlentities(DatabaseSearch::fulltextDecode($resume['text']))));
            $this->_template->assign('resumeTitle', htmlentities($resume['title']));
        }

        $this->_template->assign('active', $this);
        $this->_template->assign('candidateID', $candidateID);
        $this->_template->assign('title', $title);
        $this->_template->assign('cData', $cData);
        $this->_template->assign('qData', $qData);
        $this->_template->assign('print', $printValue);

        $this->_template->display('./modules/candidates/Questionnaire.tpl');
    }
    
    private function findDuplicateCandidateSearch()
    {
        $duplicateCandidateID = $_GET['candidateID'];
        if($duplicateCandidateID == "")
        {
            $duplicateCandidateID = $_POST['candidateID'];
        }
        $query = $this->getSanitisedInput('wildCardString', $_POST);
        $mode  = $this->getSanitisedInput('mode', $_POST);

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
        
        $candidates = new Candidates($this->_siteID);
        
        foreach ($rs as $rowIndex => $row)
        {
            $rs[$rowIndex]['duplicateCandidateID'] = $duplicateCandidateID;
            if ($candidates->checkIfLinked($rs[$rowIndex]['candidateID'], $duplicateCandidateID))
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
        $this->_template->display('./modules/candidates/LinkDuplicity.tpl');
    }
    
    private function mergeDuplicates()
    {
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
        $this->_template->display('./modules/candidates/Merge.tpl');
    }
    
    private function mergeDuplicatesInfo()
    {
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
        
        $candidates->mergeDuplicates($params, $candidates->getWithDuplicity($params['newCandidateID']));
        $this->_template->assign('isFinishedMode', true); 
        $this->_template->display('./modules/candidates/Merge.tpl');
    }
    
    private function removeDuplicity()
    {
        $candidates = new Candidates($this->_siteID);
        $oldCandidateID = $_GET['oldCandidateID'];
        $newCandidateID = $_GET['newCandidateID'];
        $candidates->removeDuplicity($oldCandidateID, $newCandidateID);
        $url = CATSUtility::getIndexName()."?m=candidates";
        header("Location: " . $url); /* Redirect browser */
        exit();
    }
    
    
    private function addDuplicates()
    {
        $candidates = new Candidates($this->_siteID);
        $oldCandidateID = $_GET['candidateID'];
        $newCandidateID = $_GET['duplicateCandidateID'];
        $candidates->addDuplicates($newCandidateID, $oldCandidateID);
        $this->_template->assign('isFinishedMode', true);
        $this->_template->display('./modules/candidates/LinkDuplicity.tpl');
    }
}

?>
