<?php
/*
 * CATS
 * Settings Module
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
 * $Id: SettingsUI.php 3810 2007-12-05 19:13:25Z brian $
 */

include_once('./lib/LoginActivity.php');
include_once('./lib/NewVersionCheck.php');
include_once('./lib/Candidates.php');
include_once('./lib/Companies.php');
include_once('./lib/Contacts.php');
include_once('./lib/Graphs.php');
include_once('./lib/Site.php');
include_once('./lib/ListEditor.php');
include_once('./lib/SystemUtility.php');
include_once('./lib/Mailer.php');
include_once('./lib/EmailTemplates.php');
include_once('./lib/License.php');
include_once('./lib/History.php');
include_once('./lib/Pipelines.php');
include_once('./lib/CareerPortal.php');
include_once('./lib/WebForm.php');
include_once('./lib/CommonErrors.php');
include_once('./lib/Import.php');
include_once('./lib/Questionnaire.php');
include_once('./lib/Tags.php');
eval(Hooks::get('XML_FEED_SUBMISSION_SETTINGS_HEADERS'));

/* Users.php is included by index.php already. */


class SettingsUI extends UserInterface
{
    /* Maximum number of login history entries to display on User Details. */
    const MAX_RECENT_LOGINS = 15;


    public function __construct()
    {
        parent::__construct();

        $this->_realAccessLevel = $_SESSION['CATS']->getRealAccessLevel();
        $this->_authenticationRequired = true;
        $this->_moduleDirectory = 'settings';
        $this->_moduleName = 'settings';
        $this->_moduleTabText = 'Settings';

        /* Only CATS professional on site gets to make career portal customizer users. */
        if( class_exists('ACL_SETUP') && !empty(ACL_SETUP::$USER_ROLES) )
        {
            $this->_settingsUserCategories = ACL_SETUP::$USER_ROLES;
        }

        $mp = array(
            'Administration' => CATSUtility::getIndexName() . '?m=settings&amp;a=administration',
            'My Profile'     => CATSUtility::getIndexName() . '?m=settings'
        );

        $this->_subTabs = $mp;
        
        $this->_hooks = $this->defineHooks();
    }

    public function defineHooks()
    {
        return array(
            /* Hide all tabs in career portal mode. */
            'TEMPLATE_UTILITY_EVALUATE_TAB_VISIBLE' => '
                if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\'))
                {
                    if (!in_array($moduleName, array(\'settings\')))
                    {
                        $displayTab = false;
                    }
                }
            ',
            
            /* Home goes to settings in career portal mode. */
            'HOME' => '
                if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\'))
                {
                    CATSUtility::transferRelativeURI(\'m=settings\');
                    return false;
                }
            ',
            
            /* My Profile goes to administration in career portal mode. */
            'SETTINGS_DISPLAY_PROFILE_SETTINGS' => '
                if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\'))
                {
                    CATSUtility::transferRelativeURI(\'m=settings&a=administration\');
                    return false;
                }
            ',

            /* Deny access to all modules in career portal mode but settings. */
            'CLIENTS_HANDLE_REQUEST' =>    'if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\')) $this->fatal("' . ERROR_NO_PERMISSION . '");',
            'CONTACTS_HANDLE_REQUEST' =>   'if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\')) $this->fatal("' . ERROR_NO_PERMISSION . '");',
            'CALENDAR_HANDLE_REQUEST' =>   'if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\')) $this->fatal("' . ERROR_NO_PERMISSION . '");',
            'JO_HANDLE_REQUEST' =>         'if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\')) $this->fatal("' . ERROR_NO_PERMISSION . '");',
            'CANDIDATES_HANDLE_REQUEST' => 'if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\')) $this->fatal("' . ERROR_NO_PERMISSION . '");',
            'ACTIVITY_HANDLE_REQUEST' =>   'if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\')) $this->fatal("' . ERROR_NO_PERMISSION . '");',
            'REPORTS_HANDLE_REQUEST' =>    'if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\')) $this->fatal("' . ERROR_NO_PERMISSION . '");'
        );
    }
    
    private function onAddNewTag()
    {
        if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
        {
            echo 'CATS has lost your session data!';
            return;
        }
        $tags = new Tags($this->_siteID);
        $arr = $tags->add((isset($_POST['tag_parent_id'])?$_POST['tag_parent_id']:null),$_POST['tag_title'], "-");
        if (isset($_POST['tag_parent_id']))
        {
	        printf('
				<li id="id_li_tag_%d">
					<a href="javascript:;" onclick="doDelete(%d);"><img src="images/actions/delete.gif" /></a>
					<div id="id_tag_%d"><a href="javascript:;" onclick="editTag(%d);">%s</a><div></div></div>
				</li>',
	        $arr['id'],$arr['id'],$arr['id'],$arr['id'],$arr['tag_title']);
        }else
        {
	        printf('
				<li id="id_li_tag_%d">
					<a href="javascript:;" onclick="doDelete(%d);"><img src="images/actions/delete.gif" /></a> %s
					<ul>
						<li>
							<img src="images/actions/add.gif" />
							<form method="post" action="%s?m=settings&amp;a=ajax_tags_add">
								<input type="hidden" name="tag_parent_id" value="%d" />
								<input type="text" name="tag_title" value="" />
								<input type="button" value="Add" onclick="doAdd(this.form);" />
							</form>
						</li>
					</ul>
				</li>',
	        $arr['id'],$arr['id'],$arr['tag_title'], CATSUtility::getIndexName(), $arr['id']);        	
        }
        
        
        return; 
    }
    
    private function onRemoveTag()
    {
        if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
        {
            echo 'CATS has lost your session data!';
            return;
        }
        $tags = new Tags($this->_siteID);
        $tags->delete($_POST['tag_id']);
        return; 
    }
    
    private function onChangeTag()
    {
        if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
        {
            echo 'CATS has lost your session data!';
            return;
        }
        $tags = new Tags($this->_siteID);
        //$tags->update($_POST['tag_id'], $_POST['title'], $_POST['description']);
        $tags->update($_POST['tag_id'], $_POST['tag_title'], "-");
        echo $_POST['tag_title'];
        return;
    }
    
    
    /**
     * This function make changes to tags
     * @return unknown_type
     */
    private function onChangeTags()
    {
        // TODO: Add tags changing code
 
    }

    /**
     * Show the tag list
     * @return unknown_type
     */
    private function changeTags()
    {
        $tags = new Tags($this->_siteID);
        $tagsRS = $tags->getAll();

        //if (!eval(Hooks::get('SETTINGS_EMAIL_TEMPLATES'))) return;

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->assign('tagsRS', $tagsRS);
        $this->_template->display('./modules/settings/tags.tpl');
    }

    public function handleRequest()
    {
        $action = $this->getAction();

        if (!eval(Hooks::get('SETTINGS_HANDLE_REQUEST'))) return;

        switch ($action)
        {
            case 'tags':
                /* Bail out if the user is demo. */
                if ($this->getUserAccessLevel('settings.tags') < ACCESS_LEVEL_SA && !$_SESSION['CATS']->hasUserCategory('careerportal'))
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'You are not allowed to edit tags.');
                }
                if ($this->isPostBack())
                {
                    $this->onChangeTags();
                }
                else
                {
                    $this->changeTags();
                }
                break;
            
            case 'changePassword':
                /* Bail out if the user is demo. */
                if ($this->getUserAccessLevel('settings.changePassword') == ACCESS_LEVEL_DEMO)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'You are not allowed to change your password.');
                }
                if ($this->isPostBack())
                {
                    $this->onChangePassword();
                }
                break;

            case 'newInstallPassword':
                if ($this->getUserAccessLevel("settings.newInstallPassword") < ACCESS_LEVEL_SA)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                if ($this->isPostBack())
                {
                    $this->onNewInstallPassword();
                }
                else
                {
                    $this->newInstallPassword();
                }
                break;

            case 'forceEmail':
                if ($this->getUserAccessLevel("settings.forceEmail") < ACCESS_LEVEL_SA)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                if ($this->isPostBack())
                {
                    $this->onForceEmail();
                }
                else
                {
                    $this->forceEmail();
                }
                break;

            case 'newSiteName':
                if ($this->getUserAccessLevel('settings.newSiteName') < ACCESS_LEVEL_SA)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                if ($this->isPostBack())
                {
                    $this->onNewSiteName();
                }
                else
                {
                    $this->newSiteName();
                }
                break;

            case 'upgradeSiteName':
                if ($this->getUserAccessLevel('settings.upgradeSiteName') < ACCESS_LEVEL_SA)
                {
                    CATSUtility::transferRelativeURI('m=settings&a=newInstallFinished');
                }
                if ($this->isPostBack())
                {
                    $this->onNewSiteName();
                }
                else
                {
                    $this->upgradeSiteName();
                }
                break;

            case 'newInstallFinished':
                if ($this->getUserAccessLevel('settings.newSiteName') < ACCESS_LEVEL_SA)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                if ($this->isPostBack())
                {
                    $this->onNewInstallFinished();
                }
                else
                {
                    $this->newInstallFinished();
                }
                break;

            case 'manageUsers':
                if ($this->getUserAccessLevel('settings.manageUsers') < ACCESS_LEVEL_DEMO)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->manageUsers();
                break;

            case 'professional':
                if ($this->getUserAccessLevel('settings.professional') < ACCESS_LEVEL_DEMO)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->manageProfessional();
                break;

            case 'previewPage':
                if ($this->getUserAccessLevel('settings.previewPage') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->previewPage();
                break;

            case 'previewPageTop':
                if ($this->getUserAccessLevel('settings.previewPageTop') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->previewPageTop();
                break;

            case 'showUser':
                if ($this->getUserAccessLevel('settings.showUser') < ACCESS_LEVEL_DEMO
                    && $this->_userID != $_GET['userID'])
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->showUser();
                break;

            case 'addUser':
                if ($this->isPostBack())
                {
                    if ($this->getUserAccessLevel('settings.addUser.POST') < ACCESS_LEVEL_SA)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->onAddUser();
                }
                else
                {
                    if ($this->getUserAccessLevel('settings.addUser.GET') < ACCESS_LEVEL_DEMO)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->addUser();
                }

                break;

            case 'editUser':

                if ($this->isPostBack())
                {
                    if ($this->getUserAccessLevel('settings.editUser.POST') < ACCESS_LEVEL_SA)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->onEditUser();
                }
                else
                {
                    if ($this->getUserAccessLevel('settings.editUser.GET') < ACCESS_LEVEL_DEMO)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->editUser();
                }

                break;

            case 'createBackup':
                if ($this->getUserAccessLevel('settings.createBackup') < ACCESS_LEVEL_SA)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->createBackup();
                break;

            case 'deleteBackup':
                if ($this->getUserAccessLevel('settings.deleteBackup') < ACCESS_LEVEL_SA)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->deleteBackup();
                break;

            case 'customizeExtraFields':
                
                if ($this->isPostBack())
                {
                    if ($this->getUserAccessLevel('settings.customizeExtraFields.POST') < ACCESS_LEVEL_SA)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->onCustomizeExtraFields();
                }
                else
                {
                    if ($this->getUserAccessLevel('settings.customizeExtraFields.GET') < ACCESS_LEVEL_DEMO)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->customizeExtraFields();
                }
                break;

            case 'customizeCalendar':
                
                if ($this->isPostBack())
                {
                    if ($this->getUserAccessLevel('settings.customizeCalendar.POST') < ACCESS_LEVEL_SA)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->onCustomizeCalendar();
                }
                else
                {
                    if ($this->getUserAccessLevel('settings.customizeCalendar.GET') < ACCESS_LEVEL_DEMO)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->customizeCalendar();
                }
                break;

            case 'reports':
                if ($this->getUserAccessLevel('settings.reports') < ACCESS_LEVEL_DEMO)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                if ($this->isPostBack())
                {

                }
                else
                {
                    $this->reports();
                }
                break;

            case 'emailSettings':
                
                if ($this->isPostBack())
                {
                    if ($this->getUserAccessLevel('settings.emailSettings.POST') < ACCESS_LEVEL_SA)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->onEmailSettings();
                }
                else
                {
                    if ($this->getUserAccessLevel('settings.emailSettings.GET') < ACCESS_LEVEL_DEMO)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->emailSettings();
                }
                break;

            case 'careerPortalQuestionnairePreview':
                if ($this->getUserAccessLevel('settings.careerPortalQuestionnairePreview') < ACCESS_LEVEL_DEMO)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->careerPortalQuestionnairePreview();
                break;

            case 'careerPortalQuestionnaire':

                if ($this->getUserAccessLevel('settings.careerPortalQuestionnaire') < ACCESS_LEVEL_DEMO)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                if ($this->isPostBack())
                {
                    $this->onCareerPortalQuestionnaire();
                }
                else
                {
                    $this->careerPortalQuestionnaire();
                }
                break;

            case 'careerPortalQuestionnaireUpdate':
                if ($this->getUserAccessLevel('settings.careerPortalQuestionnaireUpdate') < ACCESS_LEVEL_DEMO)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->careerPortalQuestionnaireUpdate();
                break;

            case 'careerPortalTemplateEdit':
                
                if ($this->isPostBack())
                {
                    if ($this->getUserAccessLevel('settings.careerPortalTemplateEdit.POST') < ACCESS_LEVEL_SA && !$_SESSION['CATS']->hasUserCategory('careerportal'))
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->onCareerPortalTemplateEdit();
                }
                else
                {
                    if ($this->getUserAccessLevel('settings.careerPortalTemplateEdit') < ACCESS_LEVEL_DEMO && !$_SESSION['CATS']->hasUserCategory('careerportal'))
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->careerPortalTemplateEdit();
                }
                break;

            case 'careerPortalSettings':
                if ($this->getUserAccessLevel('settings.careerPortalSettings') < ACCESS_LEVEL_DEMO && !$_SESSION['CATS']->hasUserCategory('careerportal'))
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                if ($this->isPostBack())
                {
                    if ($this->getUserAccessLevel('settings.careerPortalSettings.POST') < ACCESS_LEVEL_SA && !$_SESSION['CATS']->hasUserCategory('careerportal'))
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->onCareerPortalSettings();
                }
                else
                {
                    if ($this->getUserAccessLevel('settings.careerPortalSettings.GET') < ACCESS_LEVEL_DEMO && !$_SESSION['CATS']->hasUserCategory('careerportal'))
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->careerPortalSettings();
                }
                break;

            case 'eeo':
                
                if ($this->isPostBack())
                {
                    if ($this->getUserAccessLevel('settings.eeo.POST') < ACCESS_LEVEL_SA)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->onEEOEOCSettings();
                }
                else
                {
                    if ($this->getUserAccessLevel('settings.eeo.GET') < ACCESS_LEVEL_DEMO)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->EEOEOCSettings();
                }
                break;

            case 'onCareerPortalTweak':
                if ($this->getUserAccessLevel('settings.careerPortalTweak') < ACCESS_LEVEL_SA && !$_SESSION['CATS']->hasUserCategory('careerportal'))
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }

                $this->onCareerPortalTweak();
                break;

            /* This really only exists for automated testing at this point. */
            case 'deleteUser':
                if ($this->getUserAccessLevel('settings.deleteUser') < ACCESS_LEVEL_SA)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->onDeleteUser();
                break;

            case 'emailTemplates':
                
                if ($this->isPostBack())
                {
                    if ($this->getUserAccessLevel('settings.emailTemplates.POST') < ACCESS_LEVEL_SA && !$_SESSION['CATS']->hasUserCategory('careerportal'))
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->onEmailTemplates();
                }
                else
                {
                    if ($this->getUserAccessLevel('settings.emailTemplates.GET') < ACCESS_LEVEL_DEMO && !$_SESSION['CATS']->hasUserCategory('careerportal'))
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->emailTemplates();
                }
                break;

           case 'aspLocalization':
                if ($this->getUserAccessLevel('settings.aspLocalization') < ACCESS_LEVEL_SA)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                if ($this->isPostBack())
                {
                    $this->onAspLocalization();
                }
                break;

           case 'loginActivity':
                if ($this->getUserAccessLevel('settings.loginActivity') < ACCESS_LEVEL_DEMO)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }

                include_once('./lib/BrowserDetection.php');

                $this->loginActivity();
                break;

            case 'viewItemHistory':
                if ($this->getUserAccessLevel('settings.viewItemHistory') < ACCESS_LEVEL_DEMO)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->viewItemHistory();
                break;

            case 'getFirefoxModal':
                $this->getFirefoxModal();
                break;

            case 'ajax_tags_add':
                if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
                {
                    echo 'CATS has lost your session data!';
                    return;
                }
                $this->onAddNewTag();
                break;
            
            case 'ajax_tags_del':
                if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
                {
                    echo 'CATS has lost your session data!';
                    return;
                }
                $this->onRemoveTag();
                break;

            case 'ajax_tags_upd':
                if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
                {
                    echo 'CATS has lost your session data!';
                    return;
                }
                $this->onChangeTag();
                break;
               
            case 'ajax_wizardAddUser':
                if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
                {
                    echo 'CATS has lost your session data!';
                    return;
                }
                if ($this->getUserAccessLevel('settings.addUser') < ACCESS_LEVEL_SA)
                {
                    echo 'You do not have access to add a user.';
                    return;
                }
                $this->wizard_addUser();
                break;

            case 'ajax_wizardDeleteUser':
                if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
                {
                    echo 'CATS has lost your session data!';
                    return;
                }
                if ($this->getUserAccessLevel('settings.deleteUser') < ACCESS_LEVEL_SA)
                {
                    echo 'You do not have access to delete a user.';
                    return;
                }
                $this->wizard_deleteUser();
                break;

            case 'ajax_wizardCheckKey':
                if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
                {
                    echo 'CATS has lost your session data!';
                    return;
                }
                if ($this->getUserAccessLevel('settings.checkKey') < ACCESS_LEVEL_SA)
                {
                    echo 'You do not have access to set the key.';
                    return;
                }
                $this->wizard_checkKey();
                break;

            case 'ajax_wizardLocalization':
                if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
                {
                    echo 'CATS has lost your session data!';
                    return;
                }
                if ($this->getUserAccessLevel('settings.localization') < ACCESS_LEVEL_SA)
                {
                    echo 'You do not have access to change your localization settings.';
                    return;
                }
                $this->wizard_localization();
                break;

            case 'ajax_wizardFirstTimeSetup':
                if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
                {
                    echo 'CATS has lost your session data!';
                    return;
                }
                if ($this->getUserAccessLevel('settings.firstTimeSetup') < ACCESS_LEVEL_SA)
                {
                    echo 'You do not has access to this first-time-setup wizard.';
                    return;
                }
                $this->wizard_firstTimeSetup();
                break;

            case 'ajax_wizardLicense':
                if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
                {
                    echo 'CATS has lost your session data!';
                    return;
                }
                if ($this->getUserAccessLevel('settings.license') < ACCESS_LEVEL_SA)
                {
                    echo 'You do not have access to accept the license agreement.';
                    return;
                }
                $this->wizard_license();
                break;

            case 'ajax_wizardPassword':
                if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
                {
                    echo 'CATS has lost your session data!';
                    return;
                }
                if ($this->getUserAccessLevel('settings.password') < ACCESS_LEVEL_SA)
                {
                    echo 'You do not have acess to set the site password.';
                    return;
                }
                $this->wizard_password();
                break;

            case 'ajax_wizardSiteName':
                if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
                {
                    echo 'CATS has lost your session data!';
                    return;
                }
                if ($this->getUserAccessLevel('settings.siteName') < ACCESS_LEVEL_SA)
                {
                    echo 'You do not have permission to change the site name.';
                    return;
                }
                $this->wizard_siteName();
                break;

            case 'ajax_wizardEmail':
                if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
                {
                    echo 'CATS has lost your session data!';
                    return;
                }
                if ($this->getUserAccessLevel('settings.setEmail') < ACCESS_LEVEL_READ)
                {
                    echo 'You do not have permission to set the email.';
                    return;
                }
                $this->wizard_email();
                break;

            case 'ajax_wizardImport':
                if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
                {
                    echo 'CATS has lost your session data!';
                    return;
                }
                if ($this->getUserAccessLevel('settings.import') < ACCESS_LEVEL_SA)
                {
                    echo 'You do not have permission to import.';
                    return;
                }
                $this->wizard_import();
                break;

            case 'ajax_wizardWebsite':
                if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
                {
                    echo 'CATS has lost your session data!';
                    return;
                }
                if ($this->getUserAccessLevel('settings.website') < ACCESS_LEVEL_SA)
                {
                    echo 'You do not have permission.';
                    return;
                }
                $this->wizard_website();
                break;

            case 'administration':
                if ($this->isPostBack())
                {
                    if ($this->getUserAccessLevel('settings.administration.POST') < ACCESS_LEVEL_SA && !$_SESSION['CATS']->hasUserCategory('careerportal'))
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->onAdministration();
                }
                else
                {
                    if ($this->getUserAccessLevel('settings.administration.GET') < ACCESS_LEVEL_DEMO && !$_SESSION['CATS']->hasUserCategory('careerportal'))
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                    }
                    $this->administration();
                }
                break;

            /* Main settings page. */
            case 'myProfile':
            default:
                if ($this->getUserAccessLevel('settings.myProfile') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->myProfile();
                break;
        }
    }

    /*
     * Called by handleRequest() to process loading the get firefox modal dialog.
     */
    private function getFirefoxModal()
    {
        $this->_template->display(
            './modules/settings/getFirefoxModal.tpl'
        );
    }

    /*
     * Called by handleRequest() to process loading the my profile page.
     */
    private function myProfile()
    {
        $isDemoUser = $_SESSION['CATS']->isDemo();

        if (isset($_GET['s']))
        {
            switch($_GET['s'])
            {
                case 'changePassword':
                    $templateFile = './modules/settings/ChangePassword.tpl';
                    break;

                default:
                    $templateFile = './modules/settings/MyProfile.tpl';
                    break;
            }
        }
        else
        {
            $templateFile = './modules/settings/MyProfile.tpl';
        }

        if (!eval(Hooks::get('SETTINGS_DISPLAY_PROFILE_SETTINGS'))) return;

        $this->_template->assign('isDemoUser', $isDemoUser);
        $this->_template->assign('userID', $this->_userID);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'My Profile');
        $this->_template->assign('auth_mode', AUTH_MODE);
        $this->_template->display($templateFile);
    }

    /*
     * Called by handleRequest() to process loading the user details page.
     */
    private function showUser()
    {
        // FIXME: Does $_GET['userID'] exist?
        if (isset($_GET['privledged']) &&  $_GET['privledged'] == 'false' &&
            $this->_userID == $_GET['userID'])
        {
            $privledged = false;
        }
        else
        {
            $privledged = true;
        }

        $userID = $_GET['userID'];

        $users = new Users($this->_siteID);
        $data = $users->get($userID);

        if (empty($data))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'No user found with selected ID.');
        }

        $data['successfulDate'] = DateUtility::fixZeroDate(
            $data['successfulDate'], 'Never'
        );

        $data['unsuccessfulDate'] = DateUtility::fixZeroDate(
            $data['unsuccessfulDate'], 'Never'
        );

        $accessLevels = $users->getAccessLevels();

        $loginAttempts = $users->getLastLoginAttempts(
            $userID, self::MAX_RECENT_LOGINS
        );

        if (!empty($loginAttempts))
        {
            foreach ($loginAttempts as $rowIndex => $row)
            {
                $loginAttempts[$rowIndex]['shortUserAgent'] = implode(
                    ' ', BrowserDetection::detect($loginAttempts[$rowIndex]['userAgent'])
                );

                if ($loginAttempts[$rowIndex]['successful'] == 0)
                {
                    $loginAttempts[$rowIndex]['successful'] = 'No';
                }
                else
                {
                    $loginAttempts[$rowIndex]['successful'] = 'Yes';
                }
            }
        }

        $siteIDPosition = strpos($data['username'], '@' . $_SESSION['CATS']->getSiteID());

        // FIXME: The last test here might be redundant.
        if ($siteIDPosition !== false &&
            substr($data['username'], $siteIDPosition) == '@' . $_SESSION['CATS']->getSiteID())
        {
           $data['username'] = str_replace(
               '@' . $_SESSION['CATS']->getSiteID(), '', $data['username']
           );
        }

        /* Get user categories, if any. */
        $modules = ModuleUtility::getModules();
        $categories = array();
        foreach ($modules as $moduleName => $parameters)
        {
            $moduleCategories = $parameters[MODULE_SETTINGS_USER_CATEGORIES];

            if ($moduleCategories != false)
            {
                foreach ($moduleCategories as $category)
                {
                    $categories[] = $category;
                }
            }
        }

        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();

        $this->_template->assign('privledged', $privledged);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', '');
        $this->_template->assign('data', $data);
        $this->_template->assign('categories', $categories);
        $this->_template->assign('accessLevels', $accessLevels);
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);
        $this->_template->assign('currentUser', $this->_userID);
        $this->_template->assign('loginDisplay', self::MAX_RECENT_LOGINS);
        $this->_template->assign('loginAttempts', $loginAttempts);
        $this->_template->display('./modules/settings/ShowUser.tpl');
    }

    /*
     * Called by handleRequest() to process loading the user add page.
     */
    private function addUser()
    {
        $users = new Users($this->_siteID);
        $accessLevels = $users->getAccessLevels();

        $rs = $users->getAll();
        $license = $users->getLicenseData();

        /* Get user categories, if any. */
        $modules = ModuleUtility::getModules();
        $categories = array();
        foreach ($modules as $moduleName => $parameters)
        {
            $moduleCategories = $parameters[MODULE_SETTINGS_USER_CATEGORIES];

            if ($moduleCategories != false)
            {
                foreach ($moduleCategories as $category)
                {
                    /* index 3 is the user level required to assign this type of category. */
                    if (!isset($category[3]) || $category[3] <= $this->_realAccessLevel)
                    {
                        $categories[] = $category;
                    }
                }
            }
        }

        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', '');
        $this->_template->assign('accessLevels', $accessLevels);
        $this->_template->assign('license', $license);
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);
        $this->_template->assign('defaultAccessLevel', ACCESS_LEVEL_DELETE);
        $this->_template->assign('currentUser', $this->_userID);
        $this->_template->assign('categories', $categories);
        $this->_template->assign('auth_mode', AUTH_MODE);

        if (!eval(Hooks::get('SETTINGS_ADD_USER'))) return;

        $this->_template->display('./modules/settings/AddUser.tpl');
    }

    /*
     * Called by handleRequest() to process adding a user.
     */
    private function onAddUser()
    {
        if (AUTH_MODE == "ldap")
        {
            /* LDAP users are not allowed to be created in DB manualy */
            return;
        }

        $firstName      = $this->getTrimmedInput('firstName', $_POST);
        $lastName       = $this->getTrimmedInput('lastName', $_POST);
        $email          = $this->getTrimmedInput('email', $_POST);
        $username       = $this->getTrimmedInput('username', $_POST);
        $accessLevel    = $this->getTrimmedInput('accessLevel', $_POST);
        $password       = $this->getTrimmedInput('password', $_POST);
        $retypePassword = $this->getTrimmedInput('retypePassword', $_POST);
        $role           = $this->getTrimmedInput('role', $_POST);
        $eeoIsVisible   = $this->isChecked('eeoIsVisible', $_POST);

        $users = new Users($this->_siteID);
        $license = $users->getLicenseData();

        if (!$license['canAdd'] && $accessLevel > ACCESS_LEVEL_READ)
        {
            // FIXME: Shouldn't be a fatal, should go to ugprade
            $this->fatal(
                'You have no remaining user account allotments. Please upgrade your license or disable another user.'
            );
        }

        /* Bail out if any of the required fields are empty. */
        if (empty($firstName) || empty($lastName) || empty($username) ||
            empty($accessLevel) || empty($password) || empty($retypePassword))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
        }

        /* Bail out if the two passwords don't match. */
        if ($password !== $retypePassword)
        {
            CommonErrors::fatal(COMMONERROR_NOPASSWORDMATCH, $this, 'Passwords do not match.');
        }

        /* If adding an e-mail username, verify it is a valid e-mail. */
        if (strpos($username, '@') !== false && !eregi("^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})$", $username))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Username is in improper format for an E-Mail address.');
        }

        /* Make it a multisite user name if the user is part of a hosted site. */
        $unixName = $_SESSION['CATS']->getUnixName();
        if (strpos($username, '@') === false && !empty($unixName))
        {
           $username .= '@' . $_SESSION['CATS']->getSiteID();
        }

        /* Bail out if the specified username already exists. */
        if ($users->usernameExists($username))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'The specified username already exists.');
        }

        $userID = $users->add(
            $lastName, $firstName, $email, $username, $password, $accessLevel, $eeoIsVisible
        );

        /* Check role (category) to make sure that the role is allowed to be set. */
        $modules = ModuleUtility::getModules();
        foreach ($modules as $moduleName => $parameters)
        {
            $moduleCategories = $parameters[MODULE_SETTINGS_USER_CATEGORIES];

            if ($moduleCategories != false)
            {
                foreach ($moduleCategories as $category)
                {
                    if ($category[1] == $role)
                    {
                        /* index 3 is the user level required to assign this type of category. */
                        if (!isset($category[3]) || $category[3] <= $this->_realAccessLevel)
                        {
                            /* Set this category. */
                            $users->updateCategories($userID, $role);
                        }
                    }
                }
            }
        }

        if ($userID <= 0)
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to add user.');
        }

        if (!eval(Hooks::get('SETTINGS_ON_ADD_USER'))) return;

        CATSUtility::transferRelativeURI(
            'm=settings&a=showUser&userID=' . $userID
        );
    }

    /*
     * Called by handleRequest() to process loading the user edit page.
     */
    private function editUser()
    {
        /* Bail out if we don't have a valid user ID. */
        if (!$this->isRequiredIDValid('userID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid user ID.');
        }

        $userID = $_GET['userID'];

        $users = new Users($this->_siteID);
        $license = $users->getLicenseData();
        $accessLevels = $users->getAccessLevels();
        $data = $users->get($userID);

        if (empty($data))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'No user found with that ID.');
        }

        if ($this->_userID == $userID)
        {
            $disableAccessChange = true;
            $cannotEnableMessage = false;
        }
        else if (($data['accessLevel'] <= ACCESS_LEVEL_READ) && ($license['diff'] < 1) && ($license['userLicenses'] != 0))
        {
            $disableAccessChange = true;
            $cannotEnableMessage = true;
        }
        else
        {
            $disableAccessChange = false;
            $cannotEnableMessage = false;
        }

        /* Change multisite usernames into single site usernames. */
        // FIXME: The last test here might be redundant.
        // FIXME: Put this in a private method. It is duplicated twice so far.
        $siteIDPosition = strpos($data['username'], '@' . $_SESSION['CATS']->getSiteID());

        if ($siteIDPosition !== false &&
            substr($data['username'], $siteIDPosition) == '@' . $_SESSION['CATS']->getSiteID())
        {
           $data['username'] = str_replace(
               '@' . $_SESSION['CATS']->getSiteID(), '', $data['username']
           );
        }

        /* Get user categories, if any. */
        $modules = ModuleUtility::getModules();
        $categories = array();
        foreach ($modules as $moduleName => $parameters)
        {
            $moduleCategories = $parameters[MODULE_SETTINGS_USER_CATEGORIES];

            if ($moduleCategories != false)
            {
                foreach ($moduleCategories as $category)
                {
                    /* index 3 is the user level required to assign this type of category. */
                    if (!isset($category[3]) || $category[3] <= $this->_realAccessLevel)
                    {
                        $categories[] = $category;
                    }
                }
            }
        }

        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', '');
        $this->_template->assign('data', $data);
        $this->_template->assign('accessLevels', $accessLevels);
        $this->_template->assign('defaultAccessLevel', ACCESS_LEVEL_DELETE);
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);
        $this->_template->assign('license', $license);
        $this->_template->assign('categories', $categories);
        $this->_template->assign('currentUser', $this->_userID);
        $this->_template->assign('cannotEnableMessage', $cannotEnableMessage);
        $this->_template->assign('disableAccessChange', $disableAccessChange);
        $this->_template->assign('auth_mode', AUTH_MODE);
        $this->_template->display('./modules/settings/EditUser.tpl');
    }

    /*
     * Called by handleRequest() to process updating a user.
     */
    private function onEditUser()
    {
        /* Bail out if we don't have a valid user ID. */
        if (!$this->isRequiredIDValid('userID', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid user ID.');
        }

        if ($this->isRequiredIDValid('accessLevel', $_POST, true))
        {
            $accessLevel = $_POST['accessLevel'];
        }
        else
        {
            $accessLevel = -1;
        }

        $userID = $_POST['userID'];

        $firstName   = $this->getTrimmedInput('firstName', $_POST);
        $lastName    = $this->getTrimmedInput('lastName', $_POST);
        $email       = $this->getTrimmedInput('email', $_POST);
        $username    = $this->getTrimmedInput('username', $_POST);
        $password1   = $this->getTrimmedInput('password1', $_POST);
        $password2   = $this->getTrimmedInput('password2', $_POST);
        $passwordRst = $this->getTrimmedInput('passwordIsReset', $_POST);
        $role        = $this->getTrimmedInput('role', $_POST);
        $eeoIsVisible   = $this->isChecked('eeoIsVisible', $_POST);

        /* Bail out if any of the required fields are empty. */
        if (empty($firstName) || empty($lastName) || empty($username))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'First name, last name and username are required.');
        }

        /* Bail out if reseting password to null. */
        if (trim($password1) == '' && $passwordRst == 1)
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Cannot set a blank password.');
        }

        /* Bail out if the two passwords don't match. */
        if ($password1 !== $password2)
        {
            CommonErrors::fatal(COMMONERROR_NOPASSWORDMATCH, $this, 'Passwords do not match.');
        }

        /* Don't allow access level changes to the currently logged-in user's
         * account.
         */
        if ($userID == $this->_userID)
        {
            $accessLevel = $this->_realAccessLevel;
        }

        /* If adding an e-mail username, verify it is a valid e-mail. */
        // FIXME: PREG!
        if (strpos($username, '@') !== false && !eregi("^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})$", $username))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Username is in improper format for an E-Mail address.');
        }

        /* Make it a multisite user name if the user is part of a hosted site. */
        $unixName = $_SESSION['CATS']->getUnixName();
        if (strpos($username, '@') === false && !empty($unixName))
        {
           $username .= '@' . $_SESSION['CATS']->getSiteID();
        }

        $users = new Users($this->_siteID);

        if (!$users->update($userID, $lastName, $firstName, $email, $username,
            $accessLevel, $eeoIsVisible))
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to update user.');
        }

        if (trim($password1) !== '')
        {
            /* Bail out if the password is 'cats'. */
            if ($password1 == 'cats')
            {
                CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'New password can not equal \'cats\'.');
            }

            if (!$users->resetPassword($userID, $password1))
            {
                CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to reset password.');
            }
        }

        /* Set categories. */
        $modules = ModuleUtility::getModules();
        $users->updateCategories($userID, '');
        foreach ($modules as $moduleName => $parameters)
        {
            $moduleCategories = $parameters[MODULE_SETTINGS_USER_CATEGORIES];

            if ($moduleCategories != false)
            {
                foreach ($moduleCategories as $category)
                {
                    if ($category[1] == $role)
                    {
                       /* index 3 is the user level required to assign this type of category. */
                        if (!isset($category[3]) || $category[3] <= $this->_realAccessLevel)
                        {
                            /* Set this category. */
                            $users->updateCategories($userID, $role);
                        }
                    }
                }
            }
        }

        CATSUtility::transferRelativeURI(
            'm=settings&a=showUser&userID=' . $userID
        );
    }

    /*
     * Called by handleRequest() to process deleting a user.
     *
     * This is only for automated testing right now. Deleting a user this way,
     * except for in special cases, will cause referential integrity problems.
     */
    private function onDeleteUser()
    {
        /* Bail out if we don't have a valid user ID. */
        if (!$this->isRequiredIDValid('userID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid user ID.');
        }

        /* Keep users other than the automated tester from trying this. */
        if (!$this->isRequiredIDValid('iAmTheAutomatedTester', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'You are not the automated tester.');
        }

        $userID = $_GET['userID'];

        $users = new Users($this->_siteID);
        $users->delete($userID);

        CATSUtility::transferRelativeURI('m=settings&a=manageUsers');
    }

    /*
     * Called by handleRequest() to show the customize extra fields template.
     */
    private function customizeExtraFields()
    {
        $candidates = new Candidates($this->_siteID);
        $candidatesRS = $candidates->extraFields->getSettings();

        $contacts = new Contacts($this->_siteID);
        $contactsRS = $contacts->extraFields->getSettings();

        $companies = new Companies($this->_siteID);
        $companiesRS = $companies->extraFields->getSettings();

        $jobOrders = new JobOrders($this->_siteID);
        $jobOrdersRS = $jobOrders->extraFields->getSettings();

        $extraFieldTypes = $candidates->extraFields->getValuesTypes();

        $this->_template->assign('extraFieldSettingsCandidatesRS', $candidatesRS);
        $this->_template->assign('extraFieldSettingsContactsRS', $contactsRS);
        $this->_template->assign('extraFieldSettingsCompaniesRS', $companiesRS);
        $this->_template->assign('extraFieldSettingsJobOrdersRS', $jobOrdersRS);
        $this->_template->assign('extraFieldTypes', $extraFieldTypes);
        $this->_template->assign('active', $this);
        $this->_template->display('./modules/settings/CustomizeExtraFields.tpl');
    }

    /*
     * Called by handleRequest() to process the customize extra fields template.
     */
    private function onCustomizeExtraFields()
    {
        $extraFieldsMaintScript = $this->getTrimmedInput('commandList', $_POST);
        $extraFieldsMaintScriptArray = explode(',', $extraFieldsMaintScript);

        foreach($extraFieldsMaintScriptArray as $index => $commandEncoded)
        {
            $command = urldecode($commandEncoded);
            $args = explode(' ', $command);

            if (!isset($args[0]))
            {
                continue;
            }

            switch ($args[0])
            {
                case 'ADDFIELD':
                    $args = explode(' ', $command, 4);
                    $extraFields = new ExtraFields($this->_siteID, intval($args[1]));
                    $extraFields->define(urldecode($args[3]), intval($args[2]));
                    break;

                case 'DELETEFIELD':
                    $args = explode(' ', $command, 3);
                    $extraFields = new ExtraFields($this->_siteID, intval($args[1]));
                    $extraFields->remove(urldecode($args[2]));
                    break;

                case 'ADDOPTION':
                    $args = explode(' ', $command, 3);
                    $args2 = explode(':', $args[2]);

                    $extraFields = new ExtraFields($this->_siteID, intval($args[1]));
                    $extraFields->addOptionToColumn(urldecode($args2[0]), urldecode($args2[1]));
                    break;

                case 'DELETEOPTION':
                    $args = explode(' ', $command, 3);
                    $args2 = explode(':', $args[2]);

                    $extraFields = new ExtraFields($this->_siteID, intval($args[1]));
                    $extraFields->deleteOptionFromColumn(urldecode($args2[0]), urldecode($args2[1]));
                    break;

                case 'SWAPFIELDS':
                    $args = explode(' ', $command, 3);
                    $args2 = explode(':', $args[2]);

                    $extraFields = new ExtraFields($this->_siteID, intval($args[1]));
                    $extraFields->swapColumns(urldecode($args2[0]), urldecode($args2[1]));
                    break;

                case 'RENAMEROW':
                    $args = explode(' ', $command, 3);
                    $args2 = explode(':', $args[2]);

                    $extraFields = new ExtraFields($this->_siteID, intval($args[1]));
                    $extraFields->renameColumn(urldecode($args2[0]), urldecode($args2[1]));
                    break;
            }
        }

        CATSUtility::transferRelativeURI('m=settings&a=customizeExtraFields');
    }

    //FIXME: Document me.
    private function emailTemplates()
    {
        $emailTemplates = new EmailTemplates($this->_siteID);
        $emailTemplatesRS = $emailTemplates->getAll();

        if (!eval(Hooks::get('SETTINGS_EMAIL_TEMPLATES'))) return;

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->assign('emailTemplatesRS', $emailTemplatesRS);
        $this->_template->display('./modules/settings/EmailTemplates.tpl');
    }

    //FIXME: Document me.
    private function onEmailTemplates()
    {
        if (!$this->isRequiredIDValid('templateID', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid template ID.');
        }

        if (!isset($_POST['templateID']))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
        }

        $templateID = $_POST['templateID'];
        $useThisTemplate = isset($_POST['useThisTemplate']);

        if ($useThisTemplate)
        {
            $text = $this->getTrimmedInput('messageText', $_POST);
            $disabled = 0;
        }
        else
        {
            $text = $this->getTrimmedInput('messageTextOrigional', $_POST);
            $disabled = 1;
        }

        if (!isset($_POST['templateID']))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
        }

        $emailTemplates = new EmailTemplates($this->_siteID);
        $emailTemplates->update($templateID, $text, $disabled);

        CATSUtility::transferRelativeURI('m=settings&a=emailTemplates');
    }

    /*
     * Called by handleRequest() to show a page with a message in the top frame
     * with a close window button.
     */
    private function previewPage()
    {
        $previewPage = $_GET['url'];
        $previewMessage = $_GET['message'];
        $this->_template->assign('previewPage', $previewPage);
        $this->_template->assign('previewMessage', $previewMessage);
        $this->_template->display('./modules/settings/PreviewPage.tpl');
    }

    /*
     * Called by handleRequest() to show the message in the top frame
     * with a close window button.
     */
    private function previewPageTop()
    {
        $previewMessage = $_GET['message'];
        $this->_template->assign('previewMessage', $previewMessage);
        $this->_template->display('./modules/settings/PreviewPageTop.tpl');
    }

    /*
     * Called by handleRequest() to show the careers website settings editor.
     */
    private function careerPortalTemplateEdit()
    {
        $templateName = $this->getTrimmedInput('templateName', $_GET);
        if (empty($templateName))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
        }

        $careerPortalSettings = new CareerPortalSettings($this->_siteID);

        $templateSource = $careerPortalSettings->getAllFromCustomTemplate($templateName);
        if (empty($templateSource))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'No custom template with that name exists.');
        }

        $templateBySetting = array();
        foreach ($templateSource as $templateLine)
        {
            $templateBySetting[$templateLine['setting']] = $templateLine['value'];
        }

        /* Arrange the array entries in a way that makes sense. */
        $desiredOrder = $careerPortalSettings->requiredTemplateFields;

        $template = array();
        foreach ($desiredOrder as $item)
        {
            if (isset($templateBySetting[$item]))
            {
                $template[$item] = $templateBySetting[$item];
            }
            else
            {
                $template[$item] = '';
            }
        }

        foreach ($templateBySetting as $item => $value)
        {
            if (!isset($template[$item]) && $item != '')
            {
                $template[$item] = $templateBySetting[$item];
            }
        }

        /* Get extra fields. */
        $jobOrders = new JobOrders($this->_siteID);
        $extraFieldsForJobOrders = $jobOrders->extraFields->getValuesForAdd();

        $candidates = new Candidates($this->_siteID);
        $extraFieldsForCandidates = $candidates->extraFields->getValuesForAdd();

        /* Get EEO settings. */
        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->assign('template', $template);
        $this->_template->assign('templateName', $templateName);
        $this->_template->assign('eeoEnabled', $EEOSettingsRS['enabled']);
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);
        $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());
        $this->_template->assign('extraFieldsForJobOrders', $extraFieldsForJobOrders);
        $this->_template->assign('extraFieldsForCandidates', $extraFieldsForCandidates);
        $this->_template->display('./modules/settings/CareerPortalTemplateEdit.tpl');
    }

    //FIXME: Document me.
    private function onCareerPortalTemplateEdit()
    {
        $templateName = $this->getTrimmedInput('templateName', $_POST);
        if (empty($templateName) || !isset($_POST['continueEdit']))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
        }

        $continueEdit = $_POST['continueEdit'];

        $careerPortalSettings = new CareerPortalSettings($this->_siteID);

        $templateSource = $careerPortalSettings->getAllFromCustomTemplate($templateName);

        // FIXME: Document this md5() stuff.
        foreach ($templateSource as $templateLine)
        {
            if ($templateLine['setting'] != '')
            {
                $careerPortalSettings->setForTemplate(
                    $templateLine['setting'],
                    $_POST[md5($templateLine['setting'])],
                    $templateName
                );
            }
        }

        foreach ($careerPortalSettings->requiredTemplateFields as $field)
        {
            if ($field != '' && isset($_POST[md5($field)]))
            {
                $careerPortalSettings->setForTemplate(
                    $field,
                    $_POST[md5($field)],
                    $templateName
                );
            }
        }

        if ($continueEdit == '1')
        {
            CATSUtility::transferRelativeURI(
                'm=settings&a=careerPortalTemplateEdit&templateName=' . urlencode($templateName)
            );
        }
        else
        {
            CATSUtility::transferRelativeURI(
                'm=settings&a=careerPortalSettings&templateName=' . urlencode($templateName)
            );
        }
    }

    /*
     * Called by handleRequest() to show the careers website settings template.
     */
    private function careerPortalSettings()
    {
        $careerPortalSettings = new CareerPortalSettings($this->_siteID);
        $careerPortalSettingsRS = $careerPortalSettings->getAll();
        $careerPortalTemplateNames = $careerPortalSettings->getDefaultTemplates();
        $careerPortalTemplateCustomNames = $careerPortalSettings->getCustomTemplates();

        $careerPortalURL = CATSUtility::getAbsoluteURI() . 'careers/';

        if (!eval(Hooks::get('SETTINGS_CAREER_PORTAL'))) return;

        $questionnaires = new Questionnaire($this->_siteID);
        $data = $questionnaires->getAll(true);

        $this->_template->assign('active', $this);
        $this->_template->assign('questionnaires', $data);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->assign('careerPortalSettingsRS', $careerPortalSettingsRS);
        $this->_template->assign('careerPortalTemplateNames', $careerPortalTemplateNames);
        $this->_template->assign('careerPortalTemplateCustomNames', $careerPortalTemplateCustomNames);
        $this->_template->assign('careerPortalURL', $careerPortalURL);
        $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());
        $this->_template->display('./modules/settings/CareerPortalSettings.tpl');
    }

    //FIXME: Document me.
    private function onCareerPortalSettings()
    {
        $careerPortalSettings = new CareerPortalSettings($this->_siteID);
        $careerPortalSettingsRS = $careerPortalSettings->getAll();

        foreach ($careerPortalSettingsRS as $setting => $value)
        {
            eval(Hooks::get('XML_FEED_SUBMISSION_SETTINGS_BODY'));
            if ($setting == 'enabled')
            {
                if ($this->isChecked($setting, $_POST))
                {
                    $careerPortalSettings->set($setting, '1');
                    if($value != '1')
                    {
                        CATSUtility::transferRelativeURI('m=settings&a=careerPortalSettings');
                    }
                }
                else
                {
                    $careerPortalSettings->set($setting, '0');
                    if($value != '0')
                    {
                        CATSUtility::transferRelativeURI('m=settings&a=careerPortalSettings');
                    }
                }
            }
            else if ($setting == 'allowBrowse')
            {
                if ($this->isChecked($setting, $_POST))
                {
                    $careerPortalSettings->set($setting, '1');
                }
                else
                {
                    $careerPortalSettings->set($setting, '0');
                }
            }
            else if ($setting == 'candidateRegistration')
            {
                if ($this->isChecked($setting, $_POST))
                {
                    $careerPortalSettings->set($setting, '1');
                }
                else
                {
                    $careerPortalSettings->set($setting, '0');
                }
            }
            else if ($setting == 'showDepartment')
            {
                if ($this->isChecked($setting, $_POST))
                {
                    $careerPortalSettings->set($setting, '1');
                }
                else
                {
                    $careerPortalSettings->set($setting, '0');
                }
            }
            else if ($setting == 'showCompany')
            {
                if ($this->isChecked($setting, $_POST))
                {
                    $careerPortalSettings->set($setting, '1');
                }
                else
                {
                    $careerPortalSettings->set($setting, '0');
                }
            }
            else
            {
                if (isset($_POST[$setting]))
                {
                    $careerPortalSettings->set($setting, $_POST[$setting]);
                }
            }
        }

        CATSUtility::transferRelativeURI('m=settings&a=administration');
    }

    private function onCareerPortalTweak()
    {
        if (!isset($_GET['p']))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid page.');
        }

        $page = $_GET['p'];

        $careerPortalSettings = new CareerPortalSettings($this->_siteID);

        switch ($page)
        {
            case 'new':
                $origName = 'Blank Page';
                $duplicateName = $this->getTrimmedInput('newName', $_POST);

                /* Copy default templates or existing customized templates from orig to duplicate. */
                $templateSource1 = $careerPortalSettings->getAllFromDefaultTemplate($origName);
                $templateSource2 = $careerPortalSettings->getAllFromCustomTemplate($origName);

                $templateSource = array_merge($templateSource1, $templateSource2);

                foreach ($templateSource as $setting)
                {
                    $careerPortalSettings->setForTemplate(
                        $setting['setting'],
                        $setting['value'],
                        $duplicateName
                    );
                }
                break;

            case 'duplicate':
                $origName      = $this->getTrimmedInput('origName', $_POST);
                $duplicateName = $this->getTrimmedInput('duplicateName', $_POST);

                if (empty($origName) || empty($duplicateName))
                {
                    CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
                }

                /* Copy default templates or existing customized templates from orig to duplicate. */
                $templateSource1 = $careerPortalSettings->getAllFromDefaultTemplate($origName);
                $templateSource2 = $careerPortalSettings->getAllFromCustomTemplate($origName);

                $templateSource = array_merge($templateSource1, $templateSource2);

                foreach ($templateSource as $setting)
                {
                    $careerPortalSettings->setForTemplate(
                        $setting['setting'],
                        $setting['value'],
                        $duplicateName
                    );
                }
                break;

            case 'delete':
                //FIXME: Input validation.
                $delName = $_POST['delName'];
                $careerPortalSettings->deleteCustomTemplate($delName);
                break;

            case 'setAsActive':
                //FIXME: Input validation.
                $activeName = $_POST['activeName'];
                $careerPortalSettings->set('activeBoard', $activeName);
                break;
        }

        CATSUtility::transferRelativeURI('m=settings&a=careerPortalSettings');
    }

    /*
     * Called by handleRequest() to show the careers website settings template.
     */
    private function EEOEOCSettings()
    {
        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);
        $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());
        $this->_template->display('./modules/settings/EEOEOCSettings.tpl');
    }

    //FIXME: Document me.
    private function onEEOEOCSettings()
    {
        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();

        foreach ($EEOSettingsRS as $setting => $value)
        {
            if ($this->isChecked($setting, $_POST))
            {
                $EEOSettings->set($setting, '1');
            }
            else
            {
                $EEOSettings->set($setting, '0');
            }
        }

        CATSUtility::transferRelativeURI('m=settings&a=administration');
    }

    /*
     * Called by handleRequest() to show the e-mail settings template.
     */
    private function emailSettings()
    {
        $mailerSettings = new MailerSettings($this->_siteID);
        $mailerSettingsRS = $mailerSettings->getAll();

        $candidateJoborderStatusSendsMessage = unserialize($mailerSettingsRS['candidateJoborderStatusSendsMessage']);

        $emailTemplates = new EmailTemplates($this->_siteID);
        $emailTemplatesRS = $emailTemplates->getAll();

        $this->_template->assign('emailTemplatesRS', $emailTemplatesRS);
        $this->_template->assign('candidateJoborderStatusSendsMessage', $candidateJoborderStatusSendsMessage);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->assign('mailerSettingsRS', $mailerSettingsRS);
        $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());
        $this->_template->display('./modules/settings/EmailSettings.tpl');
    }

    /*
     * Called by handleRequest() to process the e-mail settings template.
     */
    private function onEmailSettings()
    {
        $mailerSettings = new MailerSettings($this->_siteID);
        $mailerSettingsRS = $mailerSettings->getAll();

        foreach ($mailerSettingsRS as $setting => $value)
        {
            if (isset($_POST[$setting]))
            {
                $mailerSettings->set($setting, $_POST[$setting]);
            }
        }

        $candidateJoborderStatusSendsMessage = unserialize($mailerSettingsRS['candidateJoborderStatusSendsMessage']);

        $candidateJoborderStatusSendsMessage[PIPELINE_STATUS_CONTACTED] = (UserInterface::isChecked('statusChangeContacted', $_POST) ? 1 : 0);
        $candidateJoborderStatusSendsMessage[PIPELINE_STATUS_CANDIDATE_REPLIED] = (UserInterface::isChecked('statusChangeReplied', $_POST) ? 1 : 0);
        $candidateJoborderStatusSendsMessage[PIPELINE_STATUS_QUALIFYING] = (UserInterface::isChecked('statusChangeQualifying', $_POST) ? 1 : 0);
        $candidateJoborderStatusSendsMessage[PIPELINE_STATUS_SUBMITTED] = (UserInterface::isChecked('statusChangeSubmitted', $_POST) ? 1 : 0);
        $candidateJoborderStatusSendsMessage[PIPELINE_STATUS_INTERVIEWING] = (UserInterface::isChecked('statusChangeInterviewing', $_POST) ? 1 : 0);
        $candidateJoborderStatusSendsMessage[PIPELINE_STATUS_OFFERED] = (UserInterface::isChecked('statusChangeOffered', $_POST) ? 1 : 0);
        $candidateJoborderStatusSendsMessage[PIPELINE_STATUS_CLIENTDECLINED] = (UserInterface::isChecked('statusChangeDeclined', $_POST) ? 1 : 0);
        $candidateJoborderStatusSendsMessage[PIPELINE_STATUS_PLACED] = (UserInterface::isChecked('statusChangePlaced', $_POST) ? 1 : 0);

        $mailerSettings->set('candidateJoborderStatusSendsMessage', serialize($candidateJoborderStatusSendsMessage));

        $emailTemplates = new EmailTemplates($this->_siteID);
        $emailTemplatesRS = $emailTemplates->getAll();

        foreach ($emailTemplatesRS as $index => $data)
        {
            $emailTemplates->updateIsActive($data['emailTemplateID'], (UserInterface::isChecked('useThisTemplate'.$data['emailTemplateID'], $_POST) ? 0 : 1));
        }

        $this->_template->assign('active', $this);
        CATSUtility::transferRelativeURI('m=settings&a=administration');
    }

    /*
     * Called by handleRequest() to show the customize calendar template.
     */
    private function customizeCalendar()
    {
        $calendarSettings = new CalendarSettings($this->_siteID);
        $calendarSettingsRS = $calendarSettings->getAll();

        $this->_template->assign('calendarSettingsRS', $calendarSettingsRS);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->display('./modules/settings/CustomizeCalendar.tpl');
    }


    /*
     * Called by handleRequest() to process the customize calendar template.
     */
    private function onCustomizeCalendar()
    {
        $calendarSettings = new CalendarSettings($this->_siteID);
        $calendarSettingsRS = $calendarSettings->getAll();

        foreach ($calendarSettingsRS as $setting => $value)
        {
            if ($setting == 'noAjax' || $setting == 'defaultPublic' || $setting == 'firstDayMonday')
            {
                if ($this->isChecked($setting, $_POST))
                {
                    $calendarSettings->set($setting, '1');
                }
                else
                {
                    $calendarSettings->set($setting, '0');
                }
            }
            else
            {
                if (isset($_POST[$setting]))
                {
                    $calendarSettings->set($setting, $_POST[$setting]);
                }
            }
        }

        $this->_template->assign('active', $this);
        CATSUtility::transferRelativeURI('m=settings&a=administration');
    }

    /*
     * Called by handleRequest() to show the customize reports template.
     */
    private function reports()
    {
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->display('./modules/settings/CustomizeReports.tpl');
    }

    /*
     * Called by handleRequest() to process loading new site pages.
     */
    private function newInstallPassword()
    {
        $this->_template->assign('inputType', 'password');
        $this->_template->assign('title', 'Create Administrator Password');
        $this->_template->assign('prompt', 'Congratulations! You have successfully logged onto CATS for the first time. Please create a new administrator password. Note that you cannot use \'cats\' as a password.');
        $this->_template->assign('action', $this->getAction());
        $this->_template->assign('home', 'home');
        $this->_template->display('./modules/settings/NewInstallWizard.tpl');
    }

    private function newSiteName()
    {
        $this->_template->assign('inputType', 'siteName');
        $this->_template->assign('inputTypeTextParam', 'Please choose your site name.');
        $this->_template->assign('title', 'Site Name');
        $this->_template->assign('prompt', 'Your administrator password has been changed.<br /><br />Next, please create a name for your CATS installation (for example, MyCompany, Inc.). This will be displayed in the top right corner of all CATS pages.');
        $this->_template->assign('action', $this->getAction());
        $this->_template->assign('home', 'home');
        $this->_template->display('./modules/settings/NewInstallWizard.tpl');
    }

    private function upgradeSiteName()
    {
        $this->_template->assign('inputType', 'siteName');
        $this->_template->assign('inputTypeTextParam', 'Site Name');
        $this->_template->assign('title', 'Site Name');
        $this->_template->assign('prompt', 'You have no site name defined. Please create a name for your CATS installation (for example, MyCompany, Inc.). This will be displayed in the top right corner of all CATS pages.');
        $this->_template->assign('action', $this->getAction());
        $this->_template->assign('home', 'home');
        $this->_template->display('./modules/settings/NewInstallWizard.tpl');
    }

    private function createBackup()
    {
        /* Attachments */
        $attachments = new Attachments(CATS_ADMIN_SITE);
        $attachmentsRS = $attachments->getAll(
            DATA_ITEM_COMPANY, $_SESSION['CATS']->getSiteCompanyID()
        );

        foreach ($attachmentsRS as $index => $data)
        {
            $attachmentsRS[$index]['fileSize'] = FileUtility::sizeToHuman(
                filesize($data['retrievalURLLocal']), 2, 1
            );
        }

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->assign('attachmentsRS', $attachmentsRS);
        $this->_template->display('./modules/settings/Backup.tpl');
    }

    private function deleteBackup()
    {
        $attachments = new Attachments(CATS_ADMIN_SITE);
        $attachments->deleteAll(
            DATA_ITEM_COMPANY,
            $_SESSION['CATS']->getSiteCompanyID(),
            "AND content_type = 'catsbackup'"
        );

        CATSUtility::transferRelativeURI('m=settings&a=createBackup');
    }

    private function forceEmail()
    {
        $this->_template->assign('inputType', 'siteName');
        $this->_template->assign('inputTypeTextParam', 'E-Mail Address');
        $this->_template->assign('title', 'E-Mail Address');
        $this->_template->assign('prompt', 'CATS does not know what your e-mail address is for sending notifications. Please type your e-mail address in the box below.');
        $this->_template->assign('action', $this->getAction());
        $this->_template->assign('home', 'home');
        $this->_template->display('./modules/settings/NewInstallWizard.tpl');
    }

    private function onForceEmail()
    {
        $emailAddress = $this->getTrimmedInput('siteName', $_POST);

        if (empty($emailAddress))
        {
            $this->_template->assign('message', 'Please enter an e-mail address.');
            $this->_template->assign('messageSuccess', false);
            $this->forceEmail();
        }
        else
        {
            $site = new Users($this->_siteID);
            $site->updateSelfEmail($this->_userID, $emailAddress);

            $this->_template->assign('inputType', 'conclusion');
            $this->_template->assign('title', "E-Mail Address");
            $this->_template->assign('prompt', "Your e-mail settings have been saved. This concludes the CATS initial configuration wizard.");
            $this->_template->assign('action', $this->getAction());
            $this->_template->assign('home', 'home');
            $this->_template->display('./modules/settings/NewInstallWizard.tpl');
        }
    }

    private function newInstallFinished()
    {
        NewVersionCheck::checkForUpdate();

        $accessLevel = $_SESSION['CATS']->getAccessLevel(ACL::SECOBJ_ROOT);

        $mailerSettings = new MailerSettings($this->_siteID);
        $mailerSettingsRS = $mailerSettings->getAll();

        $this->_template->assign('inputType', 'conclusion');
        $this->_template->assign('title', 'Settings Saved');

        if ($mailerSettingsRS['configured'] == '0' &&
            $accessLevel >= ACCESS_LEVEL_SA)
        {
            $this->_template->assign('prompt', 'Your site name has been saved. This concludes the required CATS configuration wizard.<BR><BR><span style="font-weight: bold;">Warning:</span><BR><BR> E-mail features are disabled. In order to enable e-mail features (such as e-mail notifications), please configure your e-mail settings by clicking on the Settings tab and then clicking on Administration.');
        }
        else
        {
            $this->_template->assign('prompt', 'Your site name has been saved. This concludes the required CATS configuration wizard.');
        }

        $this->_template->assign('action', $this->getAction());
        $this->_template->assign('home', 'home');
        $this->_template->display('./modules/settings/NewInstallWizard.tpl');
    }

    /*
     * Called by handleRequest() to process handling new site pages.
     */
    private function onNewInstallPassword()
    {
        $error = '';

        $newPassword = $this->getTrimmedInput(
            'password1',
            $_POST
        );
        $retypeNewPassword = $this->getTrimmedInput(
            'password2',
            $_POST
        );

        /* Bail out if the two passwords don't match. */
        if ($retypeNewPassword !== $newPassword)
        {
            $error = 'New passwords do not match.';
        }

        /* Bail out if the password is 'cats'. */
        if ($newPassword == 'cats')
        {
            $error = 'New password cannot equal \'cats\'.';
        }

        /* Attempt to change the user's password. */
        if (!$error)
        {
            $users = new Users($this->_siteID);
            if ($users->changePassword($this->_userID, 'cats', $newPassword) != LOGIN_SUCCESS)
            {
                $error = 'Unable to reset password.';
            }
        }

        if ($error)
        {
            $this->_template->assign('message', $error);
            $this->_template->assign('messageSuccess', false);
            $this->newInstallPassword();
        }
        else
        {
            CATSUtility::transferRelativeURI('m=settings&a=newSiteName');
        }
    }

    private function onNewSiteName()
    {
        $newSiteName = $this->getTrimmedInput('siteName', $_POST);

        if (empty($newSiteName) || $newSiteName === 'default_site')
        {
            $this->_template->assign('message', "Please enter a site name.");
            $this->_template->assign('messageSuccess', false);
            $this->upgradeSiteName();
        }
        else
        {
            $site = new Site($this->_siteID);
            $site->setName($newSiteName);

            $companies = new Companies($this->_siteID);
            $companyIDInternal = $companies->add(
                'Internal Postings', '', '', '', '', '', '', '', '', '', '',
                '', '', 'Internal postings.', $this->_userID, $this->_userID
            );

            $companies->setCompanyDefault($companyIDInternal);

            $_SESSION['CATS']->setSiteName($newSiteName);

            /* If no E-Mail set for current user, make user set E-Mail address. */
            if (trim($_SESSION['CATS']->getEmail()) == '')
            {
                CATSUtility::transferRelativeURI('m=settings&a=forceEmail');
            }
            else
            {
                CATSUtility::transferRelativeURI('m=settings&a=newInstallFinished');
            }
        }
    }

    private function onNewInstallFinished()
    {
        CATSUtility::transferRelativeURI('m=home');
    }

    /*
     * Called by handleRequest() to process loading the administration page.
     */
    private function administration()
    {
        $systemInfo = new SystemInfo();
        $systemInfoData = $systemInfo->getSystemInfo();

        if (isset($systemInfoData['available_version']) && $systemInfoData['available_version'] > CATSUtility::getVersionAsInteger())
        {
            $newVersion = true;
        }
        else
        {
            $newVersion = false;
        }

        if (isset($systemInfoData['disable_version_check']) && $systemInfoData['disable_version_check'])
        {
            $versionCheckPref = false;
        }
        else
        {
            $versionCheckPref = true;
        }

        if ($this->getUserAccessLevel('settings.administration') >= ACCESS_LEVEL_ROOT || $this->getUserAccessLevel('settings.administration') == ACCESS_LEVEL_DEMO)
        {
            $systemAdministration = true;
        }
        else
        {
            $systemAdministration = false;
        }

        // FIXME: 's' isn't a good variable name.
        if (isset($_GET['s']))
        {
            switch($_GET['s'])
            {
                case 'siteName':
                    $templateFile = './modules/settings/SiteName.tpl';
                    break;

                case 'newVersionCheck':
                    if (!$systemAdministration)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for administration.');
                    }

                    $this->_template->assign('versionCheckPref', $versionCheckPref);
                    $this->_template->assign('availableVersion', $systemInfoData['available_version']);
                    $this->_template->assign('newVersion', $newVersion);
                    $this->_template->assign('newVersionNews', NewVersionCheck::getNews());
                    $templateFile = './modules/settings/NewVersionCheck.tpl';
                    break;

                case 'passwords':
                    if (!$systemAdministration)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for administration.');
                    }

                    $templateFile = './modules/settings/Passwords.tpl';
                    break;

                case 'localization':
                    if ($this->getUserAccessLevel('settings.administration.localization') < ACCESS_LEVEL_SA)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for administration.');
                    }

                    $this->_template->assign('timeZone', $_SESSION['CATS']->getTimeZone());
                    $this->_template->assign('isDateDMY', $_SESSION['CATS']->isDateDMY());
                    $templateFile = './modules/settings/Localization.tpl';
                    break;

                case 'systemInformation':
                    if ($this->getUserAccessLevel('settings.administration.systemInformation') < ACCESS_LEVEL_SA)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for administration.');
                    }

                    $db = DatabaseConnection::getInstance();
                    $databaseVersion = $db->getRDBMSVersion();

                    $installationDirectory = realpath('./');

                    if (SystemUtility::isWindows())
                    {
                        $OSType = 'Windows';
                    }
                    else if (SystemUtility::isMacOSX())
                    {
                        $OSType = 'Mac OS X';
                    }
                    else
                    {
                        $OSType = 'UNIX';
                    }

                    $schemaVersions = ModuleUtility::getModuleSchemaVersions();

                    $this->_template->assign('databaseVersion', $databaseVersion);
                    $this->_template->assign('installationDirectory', $installationDirectory);
                    $this->_template->assign('OSType', $OSType);
                    $this->_template->assign('schemaVersions', $schemaVersions);
                    $templateFile = './modules/settings/SystemInformation.tpl';
                    break;

                default:
                    $templateFile = './modules/settings/Administration.tpl';
                    break;
            }
        }
        else
        {
            $templateFile = './modules/settings/Administration.tpl';

            /* Load extra settings. */
            $extraSettings = array();

            $modules = ModuleUtility::getModules();
            foreach ($modules as $moduleName => $parameters)
            {
                $extraSettingsModule = $parameters[MODULE_SETTINGS_ENTRIES];

                if ($extraSettingsModule != false)
                {
                    foreach ($extraSettingsModule as $extraSettingsModuleData)
                    {
                        if ($extraSettingsModuleData[2] <= $this->_realAccessLevel)
                        {
                            $extraSettings[] = $extraSettingsModuleData;
                        }
                    }
                }
            }
            $this->_template->assign('extraSettings', $extraSettings);
        }

        if (!strcmp($templateFile, './modules/settings/Administration.tpl'))
        {
            // Highlight certain rows of importance based on criteria
            $candidates = new Candidates($this->_siteID);
            $this->_template->assign('totalCandidates', $candidates->getCount());
        }

        if (!eval(Hooks::get('SETTINGS_DISPLAY_ADMINISTRATION'))) return;

        /* Check if careers website is enabled or can be enabled */
        $careerPortalUnlock = false;
        $careerPortalSettings = new CareerPortalSettings($this->_siteID);
        $cpData = $careerPortalSettings->getAll();
        if (intval($cpData['enabled']) || !$_SESSION['CATS']->isFree() ||
            LicenseUtility::isProfessional())
        {
            $careerPortalUnlock = true;
        }

        $this->_template->assign('careerPortalUnlock', $careerPortalUnlock);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->assign('systemAdministration', $systemAdministration);
        $this->_template->assign('active', $this);
        $this->_template->display($templateFile);
    }

    /*
     * Called by handleRequest() to process the administration page.
     */
    private function onAdministration()
    {
        $administrationMode = $this->getTrimmedInput(
            'administrationMode',
            $_POST
        );

        switch ($administrationMode)
        {
            case 'changeSiteName':
                if ($this->getUserAccessLevel('settings.administration.changeSiteName') < ACCESS_LEVEL_SA)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for administration.');
                }
                $siteName = $this->getTrimmedInput(
                    'siteName',
                    $_POST
                );

                if (empty($siteName))
                {
                    CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
                }

                $this->changeSiteName($siteName);
                CATSUtility::transferRelativeURI('m=settings&a=administration');
                break;

            case 'changeVersionCheck':
                if ($this->getUserAccessLevel('settings.administration.changeVersionName') < ACCESS_LEVEL_ROOT)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for administration.');
                }

                $this->changeNewVersionCheck(
                    $this->isChecked('versionCheck', $_POST)
                );

                $versionCheckPref = $this->isChecked('versionCheck', $_POST);
                CATSUtility::transferRelativeURI('m=settings&a=administration');
                break;

            case 'localization':
                if ($this->getUserAccessLevel('settings.administration.localization') < ACCESS_LEVEL_SA)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for administration.');
                }
                //FIXME: Validation (escaped at lib level anyway)
                $timeZone = $_POST['timeZone'];
                $dateFormat = $_POST['dateFormat'];
                if ($dateFormat == 'mdy')
                {
                    $isDMY = false;
                }
                else
                {
                    $isDMY = true;
                }

                $site = new Site($this->_siteID);
                $site->setLocalization($timeZone, $isDMY);

                $_SESSION['CATS']->logout();
                unset($_SESSION['CATS']);

                CATSUtility::transferRelativeURI('?m=settings&a=administration&messageSuccess=true&message='.urlencode('Localization settings saved!  Please log back in for the settings to take effect.'));
                break;

            default:
                CATSUtility::transferRelativeURI('m=settings&a=administration');
                break;
        }
    }

    /*
     * Called by handleRequest to change localization settings at administrator login for ASP systems.
     */
    private function onAspLocalization()
    {
        // FIXME: Input validation!

        $timeZone = $_POST['timeZone'];
        $dateFormat = $_POST['dateFormat'];
        if ($dateFormat == 'mdy')
        {
            $isDMY = false;
        }
        else
        {
            $isDMY = true;
        }

        $site = new Site($this->_siteID);
        $site->setLocalization($timeZone, $dateFormat);

        /* Reload the new data for the session. */
        $_SESSION['CATS']->setTimeDateLocalization($timeZone, $isDMY);

        $this->_template->assign('inputType', 'conclusion');
        $this->_template->assign('title', 'Localization Settings Saved!');
        $this->_template->assign('prompt', 'Your localization settings have been saved. This concludes the CATS initial configuration wizard.');
        $this->_template->assign('action', $this->getAction());
        $this->_template->assign('home', 'home');
        $this->_template->display('./modules/settings/NewInstallWizard.tpl');
    }

    /*
     * Called by Administration to change site name.
     */
    private function changeSiteName($newSiteName)
    {
        $site = new Site($this->_siteID);
        $site->setName($newSiteName);

        $_SESSION['CATS']->setSiteName($newSiteName);
        NewVersionCheck::checkForUpdate();
    }

    /*
     *  Called by Administration to change new version preferences.
     */
    private function changeNewVersionCheck($enableNewVersionCheck)
    {
        $systemInfo = new SystemInfo();
        $systemInfo->updateVersionCheckPrefs($enableNewVersionCheck);

        NewVersionCheck::checkForUpdate();
    }

    /*
     * Called by handleRequest() to process loading the site users page.
     */
    private function manageUsers()
    {
        $users = new Users($this->_siteID);
        $rs = $users->getAll();
        $license = $users->getLicenseData();

        foreach ($rs as $rowIndex => $row)
        {
            $rs[$rowIndex]['successfulDate'] = DateUtility::fixZeroDate(
                $rs[$rowIndex]['successfulDate'], 'Never'
            );

            $rs[$rowIndex]['unsuccessfulDate'] = DateUtility::fixZeroDate(
                $rs[$rowIndex]['unsuccessfulDate'], 'Never'
            );

            // FIXME: The last test here might be redundant.
            // FIXME: Put this in a private method. It is duplicated twice so far.
            $siteIDPosition = strpos($row['username'], '@' .  $_SESSION['CATS']->getSiteID());

            if ($siteIDPosition !== false &&
                substr($row['username'], $siteIDPosition) == '@' . $_SESSION['CATS']->getSiteID())
            {
               $rs[$rowIndex]['username'] = str_replace(
                   '@' . $_SESSION['CATS']->getSiteID(), '', $row['username']
               );
            }
        }

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'User Management');
        $this->_template->assign('rs', $rs);
        $this->_template->assign('license', $license);
        $this->_template->display('./modules/settings/Users.tpl');
    }

    private function manageProfessional()
    {
        $wf = new WebForm();
        $wf->addField('licenseKey', 'License Key', WFT_TEXT, true, 60, 30, 190, '', '/[A-Za-z0-9 ]+/',
            'That is not a valid license key!');
        $message = '';
        $license = new License();

        $upgradeStatus = false;

        if (isset($_GET['webFormPostBack']))
        {
            list ($fields, $errors) = $wf->getValidatedFields();
            if (count($errors) > 0) $message = 'Please enter a license key in order to continue.';

            $key = trim($fields['licenseKey']);

            $configWritten = false;

            if ($license->setKey($key) === false)
            {
                $message = 'That is not a valid license key<br /><span style="font-size: 16px; color: #000000;">Please verify that you have the correct key and try again.</span>';
            }
            else if ($license->isProfessional())
            {
                if (!CATSUtility::isSOAPEnabled())
                {
                    $message = 'CATS Professional requires the PHP SOAP library which isn\'t currently installed.<br /><br />'
                        . 'Installation Instructions:<br /><br />'
                        . 'WAMP/Windows Users:<dl>'
                        . '<li>Left click on the wamp icon.</li>'
                        . '<li>Select "PHP Settings" from the drop-down list.</li>'
                        . '<li>Select "PHP Extensions" from the drop-down list.</li>'
                        . '<li>Check the "php_soap" option.</li>'
                        . '<li>Restart WAMP.</li></dl>'
                        . 'Linux Users:<br /><br />'
                        . 'Re-install PHP with the --enable-soap configuration option.<br /><br />'
                        . 'Please visit http://www.catsone.com for more support options.';
                }
                if (!LicenseUtility::validateProfessionalKey($key))
                {
                    $message = 'That is not a valid Professional membership key<br /><span style="font-size: 16px; color: #000000;">Please verify that you have the correct key and try again.</span>';
                }
                else if (!CATSUtility::changeConfigSetting('LICENSE_KEY', "'" . $key . "'"))
                {
                    $message = 'Internal Permissions Error<br /><span style="font-size: 12px; color: #000000;">CATS is unable '
                        . 'to write changes to your <b>config.php</b> file. Please change the file permissions or contact us '
                        . 'for support. Our support e-mail is <a href="mailto:support@catsone.com">support@catsone.com</a> '
                        . 'and our office number if (952) 417-0067.</span>';
                }
                else
                {
                    $upgradeStatus = true;
                }
            }
            else
            {
                $message = 'That is not a valid Professional membership key<br /><span style="font-size: 16px; color: #000000;">Please verify that you have the correct key and try again.</span>';
            }
        }

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Professional Membership');
        $this->_template->assign('message', $message);
        $this->_template->assign('upgradeStatus', $upgradeStatus);
        $this->_template->assign('webForm', $wf);
        $this->_template->assign('license', $license);
        $this->_template->display('./modules/settings/Professional.tpl');
    }

    /*
     * Called by handleRequest() to process changing a user's password.
     */
    private function onChangePassword()
    {
        $users = new Users($this->_siteID);
        if(AUTH_MODE == 'ldap' || AUTH_MODE == 'sql+ldap')
        {
            if($users->isUserLDAP($this->_userID)) {
                $this->fatal(
                    'LDAP authentication is enabled. You are not allowed to change your password.'
                );
            }
        }

        $logout = false;

        $currentPassword = $this->getTrimmedInput(
            'currentPassword', $_POST
        );
        $newPassword = $this->getTrimmedInput(
            'newPassword', $_POST
        );
        $retypeNewPassword = $this->getTrimmedInput(
            'retypeNewPassword', $_POST
        );

        /* Bail out if we don't have a current password. */
        if (empty($currentPassword))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid current password.');
        }

        /* Bail out if we don't have a new password. */
        if (empty($newPassword))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid new password.');
        }

        /* Bail out if we don't have a retyped new password. */
        if (empty($retypeNewPassword))
        {
            CommonErrors::fatal(COMMONERROR_NOPASSWORDMATCH, $this, 'Invalid retyped new password.');
        }

        /* Bail out if the two passwords don't match. */
        if ($retypeNewPassword !== $newPassword)
        {
            CommonErrors::fatal(COMMONERROR_NOPASSWORDMATCH, $this, 'Passwords do not match.');
        }

        /* Attempt to change the user's password. */
        $status = $users->changePassword(
            $this->_userID, $currentPassword, $newPassword
        );

        switch ($status)
        {
            case LOGIN_INVALID_PASSWORD:
                /* FIXME: No fatal()... we need a back button. */
                $error[] = 'The password that you specified for "Current Password" is incorrect.';
                break;

            case LOGIN_CANT_CHANGE_PASSWORD:
                /* FIXME: No fatal()... we need a back button. */
                $error[] = 'You are not allowed to change your password.';
                break;

            case LOGIN_INVALID_USER:
                $error[] = 'Your username appears to be invalid. Your password has not been changed and you have been logged out.';
                $messageSuccess = 'false';
                $logout = true;
                break;

            case LOGIN_DISABLED:
                $message = 'Your account is disabled. Your password cannot be changed and you have been logged out.';
                $messageSuccess = 'false';
                $logout = true;
                break;

            case LOGIN_SUCCESS:
                $message = 'Your password has been successfully changed. Please log in again using your new password.';
                $messageSuccess = 'true';
                $logout = true;
                break;

            default:
                $message = 'An unknown error occurred.';
                $messageSuccess = 'false';
                $logout = true;
                break;
        }

        if ($logout)
        {
            CATSUtility::transferRelativeURI(
                'm=logout&message=' . urlencode($message) .
                '&messageSuccess=' . urlencode($messageSuccess)
            );
        }
        else
        {
            $isDemoUser = $_SESSION['CATS']->isDemo();
            $this->_template->assign('userID', $this->_userID);
            $this->_template->assign('isDemoUser', $isDemoUser);

            $this->_template->assign('active', $this);
            $this->_template->assign('subActive', 'My Profile');
            $this->_template->assign('errorMessage', join('<br />', $error));
            $this->_template->display('./modules/settings/MyProfile.tpl');
        }
    }

    /*
     * Called by handleRequest() to process loading the login activity page.
     */
    private function loginActivity()
    {
        if (isset($_GET['view']) && !empty($_GET['view']))
        {
            $view = $_GET['view'];
        }
        else
        {
            $view = '';
        }

        if ($this->isRequiredIDValid('page', $_GET))
        {
            $currentPage = $_GET['page'];
        }
        else
        {
            $currentPage = 1;
        }

        switch ($view)
        {
            case 'unsuccessful':
                $successful = false;
                break;

            case 'successful':
            default:
                $successful = true;
        }

        $loginActivityPager = new LoginActivityPager(
            LOGIN_ENTRIES_PER_PAGE, $currentPage, $this->_siteID, $successful
        );

        if ($loginActivityPager->isSortByValid('sortBy', $_GET))
        {
            $sortBy = $_GET['sortBy'];
        }
        else
        {
            $sortBy = 'dateSort';
        }

        if ($loginActivityPager->isSortDirectionValid('sortDirection', $_GET))
        {
            $sortDirection = $_GET['sortDirection'];
        }
        else
        {
            $sortDirection = 'DESC';
        }

        $loginActivityPager->setSortByParameters(
            'm=settings&amp;a=loginActivity&amp;view=' . $view,
            $sortBy,
            $sortDirection
        );

        $currentPage       = $loginActivityPager->getCurrentPage();
        $totalPages        = $loginActivityPager->getTotalPages();
        $validSortByFields = $loginActivityPager->getSortByFields();

        $rs = $loginActivityPager->getPage();

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Login Activity');
        $this->_template->assign('rs', $rs);
        $this->_template->assign('currentPage', $currentPage);
        $this->_template->assign('totalPages', $totalPages);
        $this->_template->assign('pager', $loginActivityPager);
        $this->_template->assign('view', $view);
        $this->_template->display('./modules/settings/LoginActivity.tpl');
    }

    /*
     * Called by handleRequest() to process loading the item history page.
     */
    private function viewItemHistory()
    {
        /* Bail out if we don't have a valid data item type. */
        if (!$this->isRequiredIDValid('dataItemType', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid data item type.');
        }

        /* Bail out if we don't have a valid data item ID. */
        if (!$this->isRequiredIDValid('dataItemID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid data item ID.');
        }

        $dataItemType = $_GET['dataItemType'];
        $dataItemID   = $_GET['dataItemID'];

        switch ($dataItemType)
        {
            case DATA_ITEM_CANDIDATE:
                $candidates = new Candidates($this->_siteID);
                $data = $candidates->get($dataItemID);
                break;

            case DATA_ITEM_JOBORDER:
                $jobOrders = new JobOrders($this->_siteID);
                $data = $jobOrders->get($dataItemID);
                break;

            case DATA_ITEM_COMPANY:
                $companies = new Companies($this->_siteID);
                $data = $companies->get($dataItemID);
                break;

            case DATA_ITEM_CONTACT:
                $contacts = new Contacts($this->_siteID);
                $data = $contacts->get($dataItemID);
                break;

            default:
                CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid data item type.');
                break;
        }

        /* Get revision information. */
        $history = new History($this->_siteID);
        $revisionRS = $history->getAll($dataItemType, $dataItemID);

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Login Activity');
        $this->_template->assign('data', $data);
        $this->_template->assign('revisionRS', $revisionRS);
        $this->_template->display('./modules/settings/ItemHistory.tpl');
    }

    private function wizard_addUser()
    {
        if (isset($_GET[$id = 'firstName'])) $firstName = $_GET[$id]; else $firstName = '';
        if (isset($_GET[$id = 'lastName'])) $lastName = $_GET[$id]; else $lastName = '';
        if (isset($_GET[$id = 'password'])) $password = $_GET[$id]; else $password = '';
        if (isset($_GET[$id = 'loginName'])) $loginName = $_GET[$id]; else $loginName = '';
        if (isset($_GET[$id = 'email'])) $email = $_GET[$id]; else $email = '';
        if (isset($_GET[$id = 'accessLevel']) && intval($_GET[$id]) < ACCESS_LEVEL_SA)
            $accessLevel = intval($_GET[$id]); else $accessLevel = ACCESS_LEVEL_READ;

        if (strlen($firstName) < 2 || strlen($lastName) < 2 || strlen($loginName) < 2 || strlen($password) < 2)
        {
            echo 'First and last name are too short.';
            return;
        }

        $users = new Users($this->_siteID);

        /* If adding an e-mail username, verify it is a valid e-mail. */
        if (strpos($loginName, '@') !== false && !eregi("^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})$", $loginName))
        {
            echo 'That is not a valid login name.';
            return;
        }

        /* Make it a multisite user name if the user is part of a hosted site. */
        $unixName = $_SESSION['CATS']->getUnixName();
        if (strpos($loginName, '@') === false && !empty($unixName))
        {
           $loginName .= '@' . $_SESSION['CATS']->getSiteID();
        }

        /* Bail out if the specified username already exists. */
        if ($users->usernameExists($loginName))
        {
            echo 'That username already exists.';
            return;
        }

        $data = $users->getLicenseData();
        if ($data['totalUsers'] >= $data['userLicenses'])
        {
            echo 'You cannot add any more users with your license.';
            return;
        }

        if ($users->add($lastName, $firstName, $email, $loginName, $password, $accessLevel, false) !== -1)
        {
            echo 'Ok';
            return;
        }
        else
        {
            echo 'Unable to add user. One of the fields you entered may have been formatted incorrectly.';
            return;
        }
    }

    private function wizard_deleteUser()
    {
        if (isset($_GET[$id = 'userID'])) $userID = intval($_GET[$id]);
        else
        {
            echo 'Unable to find the user you are trying to delete.';
            return;
        }

        if ($userID == $_SESSION['CATS']->getUserID())
        {
            echo 'You cannot delete yourself!';
            return;
        }

        $users = new Users($this->_siteID);
        $users->delete($userID);
        echo 'Ok';
    }

    private function wizard_checkKey()
    {
        $fileError = false;

        if (isset($_GET[$id = 'key']) && $_GET[$id] != '')
        {
            $license = new License();
            $key = strtoupper(trim($_GET[$id]));

            $configWritten = false;

            if ($license->setKey($key) !== false)
            {
                if ($license->isProfessional())
                {
                    if (!CATSUtility::isSOAPEnabled())
                    {
                        echo "CATS Professional requires the PHP SOAP library which isn't currently installed.\n\n"
                            . "Installation Instructions:\n\n"
                            . "WAMP/Windows Users:\n"
                            . "1) Left click on the wamp icon.\n"
                            . "2) Select \"PHP Settings\" from the drop-down list.\n"
                            . "3) Select \"PHP Extensions\" from the drop-down list.\n"
                            . "4) Check the \"php_soap\" option.\n"
                            . "5) Restart WAMP.\n\n"
                            . "Linux Users:\n"
                            . "Re-install PHP with the --enable-soap configuration option.\n\n"
                            . "Please visit http://www.catsone.com for more support options.";
                        return;
                    }
                    else
                    {
                        if (!LicenseUtility::validateProfessionalKey($key))
                        {
                            echo "That is not a valid CATS Professional license key. Please visit "
                                . "http://www.catsone.com/professional for more information about CATS Professional.\n\n"
                                . "For a free open-source key, please visit http://www.catsone.com/ and "
                                . "click on \"Downloads\".";
                            return;
                        }
                    }
                }

                if (CATSUtility::changeConfigSetting('LICENSE_KEY', "'" . $key . "'"))
                {
                    $configWritten = true;
                }
            }

            if ($configWritten)
            {
                echo 'Ok';
                return;
            }
        }

        // The key hasn't been written. But they may have manually inserted the key into their config.php, check
        if (LicenseUtility::isLicenseValid())
        {
            echo 'Ok';
            return;
        }

        if ($fileError)
        {
            echo 'You entered a valid key, but this wizard is unable to write to your config.php file! You have '
                . 'two choices: ' . "\n\n"
                . '1) Change the file permissions of your config.php file.'."\n".'If you\'re using unix, try:' . "\n" . 'chmod 777 config.php' . "\n\n"
                . '2) Edit your config.php file manually and enter your valid key near this line: ' . "\n"
                . 'define(\'LICENSE_KEY\', \'ENTER YOUR KEY HERE\');' . "\n" . 'Once you\'ve done this, refresh your browser.' . "\n\n"
                . 'For more help, visit our website at http://www.catsone.com for support options.';
        }

        echo 'That is not a valid key. You can register for a free open source license key on our website '
            . 'at http://www.catsone.com or a professional key to unlock all of the available features at '
            . 'http://www.catsone.com/professional';
    }

    private function wizard_localization()
    {
        if (!isset($_GET['timeZone']) || !isset($_GET['dateFormat']))
        {
            echo 'You didn\'t provide a time zone or date format.';
            return;
        }

        $timeZone = $_GET['timeZone'];
        $dateFormat = $_GET['dateFormat'];
        if ($dateFormat == 'mdy')
        {
            $isDMY = false;
        }
        else
        {
            $isDMY = true;
        }

        $site = new Site($this->_siteID);
        $site->setLocalization($timeZone, $isDMY);
        $site->setLocalizationConfigured();

        echo 'Ok';
    }

    private function wizard_license()
    {
        $site = new Site($this->_siteID);
        $site->setAgreedToLicense();

        echo 'Ok';
    }

    private function wizard_firstTimeSetup()
    {
        $site = new Site($this->_siteID);
        $site->setFirstTimeSetup();

        echo 'Ok';
    }

    private function wizard_password()
    {
        if (isset($_GET['password']) && !empty($_GET['password'])) $password = $_GET['password'];
        else $password = '';

        if (strlen($password) < 5)
        {
            echo 'Your password length must be at least 5 characters long.';
            return;
        }

        $users = new Users($this->_siteID);
        if ($users->changePassword($this->_userID, 'cats', $password) != LOGIN_SUCCESS)
        {
            echo 'Cannot change your site password!';
            return;
        }

        echo 'Ok';
    }

    private function wizard_email()
    {
        if (isset($_GET['email']) && !empty($_GET['email'])) $email = $_GET['email'];
        else $email = '';

        if (strlen($email) < 5)
        {
            echo 'Your e-mail address must be at least 5 characters long.';
            return;
        }

        $site = new Users($this->_siteID);
        $site->updateSelfEmail($this->_userID, $email);

        echo 'Ok';
    }

    private function wizard_siteName()
    {
        if (isset($_GET['siteName']) && !empty($_GET['siteName'])) $siteName = $_GET['siteName'];
        else $siteName = '';

        if ($siteName == 'default_site' || strlen($siteName) <= 0)
        {
            echo 'That is not a valid site name. Please choose a different one.';
            return;
        }

        $site = new Site($this->_siteID);
        $site->setName($siteName);

        $companies = new Companies($this->_siteID);
        $companyIDInternal = $companies->add(
            'Internal Postings', '', '', '', '', '', '', '', '', '', '',
            '', '', 'Internal postings.', $this->_userID, $this->_userID
        );

        $companies->setCompanyDefault($companyIDInternal);

        $_SESSION['CATS']->setSiteName($siteName);

        echo 'Ok';
    }

    private function wizard_import()
    {
        $siteID = $_SESSION['CATS']->getSiteID();

        // Echos Ok to redirect to the import stage, or Fail to go to home module
        $files = ImportUtility::getDirectoryFiles(FileUtility::getUploadPath($siteID, 'massimport'));

        if (count($files)) echo 'Ok';
        else echo 'Fail';
    }

    private function wizard_website()
    {
        $website = trim(isset($_GET[$id='website']) ? $_GET[$id] : '');
        if (strlen($website) > 10)
        {
            if (!eval(Hooks::get('SETTINGS_CP_REQUEST'))) return;
        }

        echo 'Ok';
    }

    private function careerPortalQuestionnaire($fromPostback = false)
    {
        // Get the ID if provided, otherwise we're adding a questionnaire
        $questionnaireID = isset($_GET[$id='questionnaireID']) ? $_GET[$id] : '';

        $questions = array();

        if (!$fromPostback)
        {
            $title = $description = '';
            $isActive = 1;

            // If questionairreID is provided, this is an edit
            if ($questionnaireID != '')
            {
                $questionnaire = new Questionnaire($this->_siteID);
                if (count($data = $questionnaire->get($questionnaireID)))
                {
                    $questions = $questionnaire->getQuestions($questionnaireID);

                    for ($i=0; $i<count($questions); $i++)
                    {
                        $questions[$i]['questionTypeLabel'] = $questionnaire->convertQuestionConstantToType(
                            $questions[$i]['questionType']
                        );
                    }

                    $this->_template->assign('title', $title = $data['title']);
                    $this->_template->assign('description', $description = $data['description']);
                    $this->_template->assign('isActive', $isActive = $data['isActive']);
                    $this->_template->assign('questions', $questions);
                }
                else
                {
                    $questionnaireID = '';
                }
            }

            // Store the questionnaire in a sesssion. That way we can make post changes
            // without changing the database data. Only save the session to the DB if the
            // user requests it.
            if (isset($_SESSION['CATS_QUESTIONNAIRE'])) unset($_SESSION['CATS_QUESTIONNAIRE']);
            $_SESSION['CATS_QUESTIONNAIRE'] = array(
                'id' => $questionnaireID,
                'title' => $title,
                'description' => $description,
                'questions' => $questions,
                'isActive' => $isActive
            );
        }
        else
        {
            // This is being called from a postback, so we're actively working out of the
            // session. Postback will handle saves.
            if (!isset($_SESSION['CATS_QUESTIONNAIRE']) || empty($_SESSION['CATS_QUESTIONNAIRE']))
            {
                CommonErrors::fatal(COMMONERROR_BADINDEX, 'Please return to your careers website '
                    . 'and load the questionnaire a second time as your session has '
                    . 'expired.');
            }

            // Save/restore the scroll position of the page
            $scrollX = isset($_POST[$id = 'scrollX']) ? $_POST[$id] : '';
            $scrollY = isset($_POST[$id = 'scrollY']) ? $_POST[$id] : '';

            $questions = $_SESSION['CATS_QUESTIONNAIRE']['questions'];
            $questionnaireID = $_SESSION['CATS_QUESTIONNAIRE']['id'];

            $this->_template->assign('scrollX', $scrollX);
            $this->_template->assign('scrollY', $scrollY);
            $this->_template->assign('title', $_SESSION['CATS_QUESTIONNAIRE']['title']);
            $this->_template->assign('description', $_SESSION['CATS_QUESTIONNAIRE']['description']);
            $this->_template->assign('isActive', $_SESSION['CATS_QUESTIONNAIRE']['isActive']);
            $this->_template->assign('questions', $questions);
        }

        $this->_template->assign('questionnaireID', $questionnaireID);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', '');
        $this->_template->display('./modules/settings/CareerPortalQuestionnaire.tpl');
    }

    private function onCareerPortalQuestionnaire()
    {
        if (!isset($_SESSION['CATS_QUESTIONNAIRE']) || empty($_SESSION['CATS_QUESTIONNAIRE']))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, 'Please return to your careers website '
                . 'and load the questionnaire a second time as your session has '
                . 'expired.');
        }

        // Get the title
        $title = isset($_POST[$id = 'title']) ? substr(trim($_POST[$id]), 0, 255) : '';
        if (!strlen($title)) $title = '';

        // Get the description
        $description = isset($_POST[$id = 'description']) ? substr(trim($_POST[$id]), 0, 255) : '';
        if (!strlen($description)) $description = '';

        // Is this active?
        $active = isset($_POST[$id = 'isActive']) ? !strcasecmp($_POST[$id], 'yes') : 0;

        $_SESSION['CATS_QUESTIONNAIRE']['title'] = $title;
        $_SESSION['CATS_QUESTIONNAIRE']['description'] = $description;
        $_SESSION['CATS_QUESTIONNAIRE']['isActive'] = $active ? true : false;

        $questionnaire = new Questionnaire($this->_siteID);
        $questions = $_SESSION['CATS_QUESTIONNAIRE']['questions'];

        /**
         * STEP 1
         * Check for changes to question and answer texts, mark questions or
         * answers that the user specified to remove as "remove" which will be done
         * in the final step to prevent index changes.
         */
        for ($questionIndex=0; $questionIndex<count($questions); $questionIndex++)
        {
            // Update the position of the question
            $field = sprintf('question%dPosition', $questionIndex);
            if (isset($_POST[$field]))
            {
                $position = intval(trim($_POST[$field]));
                $questions[$questionIndex]['questionPosition'] = $position;
            }

            // Update the text of the question
            $field = sprintf('question%dTextValue', $questionIndex);
            if (isset($_POST[$field]))
            {
                if (strlen($text = substr(trim($_POST[$field]), 0, 255)))
                {
                    $questions[$questionIndex]['questionText'] = $text;
                }
            }

            // Update the type of the question
            $field = sprintf('question%dTypeValue', $questionIndex);
            if (isset($_POST[$field]))
            {
                $type = $questionnaire->convertQuestionTypeToConstant($_POST[$field]);
                $questions[$questionIndex]['questionType'] = $type;
                $questions[$questionIndex]['questionTypeLabel'] = (
                    $questionnaire->convertQuestionConstantToType($type)
                );
            }

            // Check if this question should be removed (user checked the box)
            $field = sprintf('question%dRemove', $questionIndex);
            if (isset($_POST[$field]) && !strcasecmp($_POST[$field], 'yes'))
            {
                $questions[$questionIndex]['remove'] = true;
            }
            else
            {
                $questions[$questionIndex]['remove'] = false;
            }

            for ($answerIndex=0; $answerIndex<count($questions[$questionIndex]['answers']); $answerIndex++)
            {
                // Update the position of the question
                $field = sprintf('question%dAnswer%dPosition', $questionIndex, $answerIndex);
                if (isset($_POST[$field]))
                {
                    $position = intval(trim($_POST[$field]));
                    $questions[$questionIndex]['answers'][$answerIndex]['answerPosition'] = $position;
                }

                // Update the text of the answer
                $field = sprintf('question%dAnswer%dTextValue', $questionIndex, $answerIndex);
                if (isset($_POST[$field]))
                {
                    if (strlen($text = substr(trim($_POST[$field]), 0, 255)))
                    {
                        $questions[$questionIndex]['answers'][$answerIndex]['answerText'] = $text;
                    }
                }

                // Check if this answer should be removed (user checked the box)
                $field = sprintf('question%dAnswer%dRemove', $questionIndex, $answerIndex);
                if (isset($_POST[$field]) && !strcasecmp($_POST[$field], 'yes'))
                {
                    $questions[$questionIndex]['answers'][$answerIndex]['remove'] = true;
                }
                else
                {
                    $questions[$questionIndex]['answers'][$answerIndex]['remove'] = false;
                }

                // Check the actions for whether or not they should exist
                $actionSourceField = sprintf('question%dAnswer%dActionSource',
                    $questionIndex, $answerIndex
                );
                $actionNotesField = sprintf('question%dAnswer%dActionNotes',
                    $questionIndex, $answerIndex
                );
                $actionIsHotField = sprintf('question%dAnswer%dActionIsHot',
                    $questionIndex, $answerIndex
                );
                $actionIsActiveField = sprintf('question%dAnswer%dActionIsActive',
                    $questionIndex, $answerIndex
                );
                $actionCanRelocateField = sprintf('question%dAnswer%dActionCanRelocate',
                    $questionIndex, $answerIndex
                );
                $actionKeySkillsField = sprintf('question%dAnswer%dActionKeySkills',
                    $questionIndex, $answerIndex
                );

                $actionSourceActive = isset($_POST[$id = $actionSourceField . 'Active']) ? $_POST[$id] : '';
                $actionNotesActive = isset($_POST[$id = $actionNotesField . 'Active']) ? $_POST[$id] : '';
                $actionIsHotActive = isset($_POST[$id = $actionIsHotField . 'Active']) ? $_POST[$id] : '';
                $actionIsActiveActive = isset($_POST[$id = $actionIsActiveField . 'Active']) ? $_POST[$id] : '';
                $actionCanRelocateActive = isset($_POST[$id = $actionCanRelocateField . 'Active']) ? $_POST[$id] : '';
                $actionKeySkillsActive = isset($_POST[$id = $actionKeySkillsField . 'Active']) ? $_POST[$id] : '';

                $actionSourceValue = isset($_POST[$id = $actionSourceField . 'Value']) ? $_POST[$id] : '';
                $actionNotesValue = isset($_POST[$id = $actionNotesField . 'Value']) ? $_POST[$id] : '';
                $actionIsHotValue = isset($_POST[$id = $actionIsHotField . 'Value']) ? $_POST[$id] : '';
                $actionIsActiveValue = isset($_POST[$id = $actionIsActiveField . 'Value']) ? $_POST[$id] : '';
                $actionCanRelocateValue = isset($_POST[$id = $actionCanRelocateField . 'Value']) ? $_POST[$id] : '';
                $actionKeySkillsValue = isset($_POST[$id = $actionKeySkillsField . 'Value']) ? $_POST[$id] : '';

                $questions[$questionIndex]['answers'][$answerIndex]['actionSource'] = (
                    strcasecmp($actionSourceActive, 'yes') ?
                    '' :
                    $actionSourceValue
                );
                $questions[$questionIndex]['answers'][$answerIndex]['actionNotes'] = (
                    strcasecmp($actionNotesActive, 'yes') ?
                    '' :
                    $actionNotesValue
                );
                $questions[$questionIndex]['answers'][$answerIndex]['actionIsHot'] = (
                    strcasecmp($actionIsHotActive, 'yes') ?
                    0 :
                    1
                );
                $questions[$questionIndex]['answers'][$answerIndex]['actionIsActive'] = (
                    strcasecmp($actionIsActiveActive, 'yes') ?
                    1 :
                    0
                );
                $questions[$questionIndex]['answers'][$answerIndex]['actionCanRelocate'] = (
                    strcasecmp($actionCanRelocateActive, 'yes') ?
                    0 :
                    1
                );
                $questions[$questionIndex]['answers'][$answerIndex]['actionKeySkills'] = (
                    strcasecmp($actionKeySkillsActive, 'yes') ?
                    '' :
                    $actionKeySkillsValue
                );
            }
        }

        /**
         * STEP 2
         * Perform addition requests like add question, answer or action. We do this before
         * performing the removal step because if a user removes a question and adds a answer
         * to it in the same step, the indexes will be misaligned. This way, the addition is
         * processed and then immediately removed if requested by the user (which is naughty).
         */
        $restrictAction = isset($_POST[$id = 'restrictAction']) ? $_POST[$id] : '';
        $restrictQuestionID = isset($_POST[$id = 'restrictActionQuestionID']) ? intval($_POST[$id]) : '';
        $restrictAnswerID = isset($_POST[$id = 'restrictActionAnswerID']) ? intval($_POST[$id]) : '';

        if (!strcasecmp($restrictAction, 'question'))
        {
            // Adding a new question to the questionnaire
            $questionText = isset($_POST[$id = 'questionText']) ? trim($_POST[$id]) : '';
            $questionTypeText = isset($_POST[$id = 'questionType']) ? $_POST[$id] : '';

            // Make sure the question doesn't already exist (re-submit)
            for ($i = 0, $exists = false; $i < count($questions); $i++)
            {
                if (!strcmp($questions[$i]['questionText'], $questionText))
                {
                    $exists = true;
                }
            }

            if (strlen($questionText) && !$exists)
            {
                $questions[] = array(
                    'questionID' => -1, // -1 indicates a record needs to be added
                    'questionType' => QUESTIONNAIRE_QUESTION_TYPE_TEXT,
                    'questionTypeLabel' =>
                        $questionnaire->convertQuestionConstantToType(QUESTIONNAIRE_QUESTION_TYPE_TEXT),
                    'questionText' => $questionText,
                    'minimumLength' => 0,
                    'maximumLength' => 255,
                    'questionPosition' => 1000, // should be positioned last (users can't enter higher than 999)
                    'answers' => array()
                );
            }
        }
        else if (!strcasecmp($restrictAction, 'answer') &&
            isset($questions[$restrictQuestionID]))
        {
            // Adding a new answer to an existing question
            $field = sprintf('question%dAnswerText', $restrictQuestionID);
            $answerText = substr(trim(isset($_POST[$field]) ? $_POST[$field] : ''), 0, 255);

            if (strlen($answerText))
            {
                $questions[$restrictQuestionID]['answers'][] = array(
                    'answerID' => -1, // append to the db
                    'answerText' => $answerText,
                    'actionSource' => '',
                    'actionNotes' => '',
                    'actionIsHot' => 0,
                    'actionIsActive' => 1,
                    'actionCanRelocate' => 0,
                    'actionKeySkills' => '',
                    'answerPosition' => 1000 // should be positioned last (see above)
                );
            }
        }
        else if (!strcasecmp($restrictAction, 'action') &&
            isset($questions[$restrictQuestionID]) &&
            isset($questions[$restrictQuestionID]['answers'][$restrictAnswerID]))
        {
            // Adding a new action to an existing answer of an existing question
            $field = sprintf('question%dAnswer%d', $restrictQuestionID, $restrictAnswerID);
            $newAction = isset($_POST[$id = $field . 'NewAction']) ? $_POST[$id] : '';
            $actionText = substr(trim(isset($_POST[$id = $field . 'NewActionText']) ? $_POST[$id] : ''), 0, 255);

            if (isset($questions[$restrictQuestionID]['answers'][$restrictAnswerID][$newAction]))
            {
                switch ($newAction)
                {
                    case 'actionSource': case 'actionNotes': case 'actionKeySkills':
                        $value = $actionText;
                        break;

                    case 'actionIsActive':
                        $value = 0;
                        break;

                    default:
                        $value = 1;
                        break;
                }

                $questions[$restrictQuestionID]['answers'][$restrictAnswerID][$newAction] = $value;
            }
        }

        /**
         * STEP 5
         * Remove any questions/answers that have "remove" checked prior to sorting/positioning
         */
        $savedQuestions = array();
        for ($questionIndex = 0, $savedQuestionIndex = 0;
             $questionIndex < count($questions);
             $questionIndex++)
        {
            if (isset($questions[$questionIndex]['remove']) && $questions[$questionIndex]['remove']) continue;
            $savedQuestions[$savedQuestionIndex] = $questions[$questionIndex];
            $savedQuestions[$savedQuestionIndex]['answers'] = array();

            for ($answerIndex = 0; $answerIndex < count($questions[$questionIndex]['answers']); $answerIndex++)
            {
                if (isset($questions[$questionIndex]['answers'][$answerIndex]['remove']) &&
                    $questions[$questionIndex]['answers'][$answerIndex]['remove']) continue;
                $savedQuestions[$savedQuestionIndex]['answers'][] =
                    $questions[$questionIndex]['answers'][$answerIndex];
            }

            $savedQuestionIndex++;
        }
        $questions = $savedQuestions;

        /**
         * STEP 6
         * Corrections. Any removals or changes that have altered the "way of things" need to
         * be fixed before sort.
         */
        for ($questionIndex = 0; $questionIndex < count($questions); $questionIndex++)
        {
            // If the question has no answers it is a TEXT automatically
            if (!count($questions[$questionIndex]['answers']))
            {
                $questions[$questionIndex]['questionType'] = QUESTIONNAIRE_QUESTION_TYPE_TEXT;
                $questions[$questionIndex]['questionTypeLabel'] =
                    $questionnaire->convertQuestionConstantToType(QUESTIONNAIRE_QUESTION_TYPE_TEXT);
            }
            // Otherwise, if there are answers, it cannot be a TEXT
            else if ($questions[$questionIndex]['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_TEXT)
            {
                $questions[$questionIndex]['questionType'] = QUESTIONNAIRE_QUESTION_TYPE_SELECT;
                $questions[$questionIndex]['questionTypeLabel'] =
                    $questionnaire->convertQuestionConstantToType(QUESTIONNAIRE_QUESTION_TYPE_SELECT);
            }
        }

        /**
         * STEP 7
         * Perform a bubble sort on the questions and answers. Then provide real values
         * (1, 2, 3) based on the results.
         */
        for ($questionIndex2 = 0;
             $questionIndex2 < count($questions);
             $questionIndex2++)
        {
            if ($questionIndex2 < count($questions) - 1)
            {
                for ($questionIndex3 = 0;
                     $questionIndex3 < count($questions) - 1;
                     $questionIndex3++)
                {
                    if (intval($questions[$questionIndex3]['questionPosition']) >
                        intval($questions[$questionIndex3+1]['questionPosition']))
                    {
                        $tmp = $questions[$questionIndex3];
                        $questions[$questionIndex3] = $questions[$questionIndex3+1];
                        $questions[$questionIndex3+1] = $tmp;
                    }
                }
            }

            // Bubble sort the answers for each question using the same method
            for ($answerIndex2 = 0;
                 $answerIndex2 < count($questions[$questionIndex2]['answers']) - 1;
                 $answerIndex2++)
            {
                for ($answerIndex3 = 0;
                     $answerIndex3 < count($questions[$questionIndex2]['answers']) - 1;
                     $answerIndex3++)
                {
                    if (intval($questions[$questionIndex2]['answers'][$answerIndex3]['answerPosition']) >
                        intval($questions[$questionIndex2]['answers'][$answerIndex3+1]['answerPosition']))
                    {
                        $tmp = $questions[$questionIndex2]['answers'][$answerIndex3];
                        $questions[$questionIndex2]['answers'][$answerIndex3] =
                            $questions[$questionIndex2]['answers'][$answerIndex3+1];
                        $questions[$questionIndex2]['answers'][$answerIndex3+1] = $tmp;
                    }
                }
            }
        }

        // Now define real position values (never trust the naughty user)
        for ($questionIndex2 = 0;
             $questionIndex2 < count($questions);
             $questionIndex2++)
        {
            $questions[$questionIndex2]['questionPosition'] = $questionIndex2 + 1;

            for ($answerIndex2 = 0;
                 $answerIndex2 < count($questions[$questionIndex2]['answers']);
                 $answerIndex2++)
            {
                $questions[$questionIndex2]['answers'][$answerIndex2]['answerPosition'] = ($answerIndex2 + 1);
            }
        }

        if (isset($_POST[$id = 'startOver']) && !strcasecmp($_POST[$id], 'yes'))
        {
            // User wants to start over
            $_SESSION['CATS_QUESTIONNAIRE']['questions'] = array();
        }
        else if (isset($_POST[$id = 'saveChanges']) && !strcasecmp($_POST[$id], 'yes'))
        {
            // User wants to add the new questionnaire
            if (($id = intval($_SESSION['CATS_QUESTIONNAIRE']['id'])) != 0)
            {
                $questionnaire->update(
                    $id, // the questionnaire id to update
                    $_SESSION['CATS_QUESTIONNAIRE']['title'],
                    $_SESSION['CATS_QUESTIONNAIRE']['description'],
                    $_SESSION['CATS_QUESTIONNAIRE']['isActive']
                );
            }
            // User is editting an existing questionnaire
            else
            {
                $id = $questionnaire->add(
                    $_SESSION['CATS_QUESTIONNAIRE']['title'],
                    $_SESSION['CATS_QUESTIONNAIRE']['description'],
                    $_SESSION['CATS_QUESTIONNAIRE']['isActive']
                );
            }

            if ($id !== false)
            {
                // Delete all existing questions/answers (replace with session values)
                $questionnaire->deleteQuestions($id);

                // Save the questions to the new or old questionnaire
                $questionnaire->addQuestions(
                    $id,
                    $_SESSION['CATS_QUESTIONNAIRE']['questions']
                );

                CATSUtility::transferRelativeURI('m=settings&a=careerPortalSettings');
                return;
            }
        }
        else
        {
            // Now save changes to the session
            $_SESSION['CATS_QUESTIONNAIRE']['questions'] = $questions;
        }

        // Now view the page as if we've just loaded it from the database
        $this->careerPortalQuestionnaire(true);
    }

    private function careerPortalQuestionnaireUpdate()
    {
        $questionnaire = new Questionnaire($this->_siteID);
        $data = $questionnaire->getAll(true);

        for ($i = 0; $i < count($data); $i++)
        {
            if (isset($_POST[$id = 'removeQuestionnaire' . $i]) &&
                !strcasecmp($_POST[$id], 'yes'))
            {
                $questionnaire->delete($data[$i]['questionnaireID']);
            }
        }

        CATSUtility::transferRelativeURI('m=settings&a=careerPortalSettings');
    }

    private function careerPortalQuestionnairePreview()
    {
        if (!isset($_GET['questionnaireID']))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Bad index.');
        }

        $questionnaireID = intval($_GET['questionnaireID']);
        $questionnaire = new Questionnaire($this->_siteID);
        $data = $questionnaire->get($questionnaireID);

        if (empty($data))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX);
        }

        $questions = $questionnaire->getQuestions($questionnaireID);

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->assign('isModal', false);
        $this->_template->assign('questionnaireID', $questionnaireID);
        $this->_template->assign('data', $data);
        $this->_template->assign('questions', $questions);
        $this->_template->display('./modules/settings/CareerPortalQuestionnaireShow.tpl');
    }
}

?>
