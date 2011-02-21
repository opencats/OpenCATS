<?php
/*
 * CATS
 * Wizard module
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
 * Multi-step wizard.
 *
 *
 * $Id: WizardUI.php 3569 2007-11-12 15:54:44Z andrew $
 */

include_once('./lib/ActivityEntries.php');
include_once('./lib/StringUtility.php');
include_once('./lib/DateUtility.php');
include_once('./lib/JobOrders.php');
include_once('./lib/Site.php');
include_once('./lib/CareerPortal.php');

class WizardUI extends UserInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = false;
        $this->_moduleDirectory = 'wizard';
        $this->_moduleName = 'wizard';
        $this->_moduleTabText = '';
        $this->_subTabs = array();

        /*
        $this->addPage('Welcome!', './modules/wizard/WizardIntroIntro.tpl', '', false, true);
        $this->addPage('License', './modules/wizard/WizardIntroLicense.tpl', '', true, true);
        $this->addPage('Register', './modules/wizard/WizardIntroProf.tpl', '', false, true);
        $this->addPage('Setup Users', './modules/wizard/WizardIntroUsers.tpl', '
            $users = new Users($siteID);
            $mp = $users->getAll();
            $data = $users->getLicenseData();

            $this->_template->assign(\'users\', $mp);
            $this->_template->assign(\'totalUsers\', $data[\'totalUsers\']);
            $this->_template->assign(\'userLicenses\', $data[\'userLicenses\']);
            $this->_template->assign(\'accessLevels\', $users->getAccessLevels());
        ');
        $this->addPage('Localization', './modules/wizard/WizardIntroLocalization.tpl', '
            $this->_template->assign(\'timeZone\', $_SESSION[\'CATS\']->getTimeZone());
            $this->_template->assign(\'isDateDMY\', $_SESSION[\'CATS\']->isDateDMY());
        ');

        $this->addJsInclude('./js/wizardIntro.js');
        $this->setFinishURL('?m=home');
        */
    }


    public function handleRequest()
    {
        $action = $this->getAction();
        switch ($action)
        {
            case 'ajax_getPage':
                $this->ajax_getPage();
                break;

            default:
                $this->show();
                break;
        }
    }

    public function show()
    {
        if (!isset($_SESSION['CATS_WIZARD']) || empty($_SESSION['CATS_WIZARD']) ||
            !is_array($_SESSION['CATS_WIZARD']))
        {
            // The user has removed or the session for the wizard has been lost,
            // redirect to rebuild it
            CATSUtility::transferRelativeURI(CATSUtility::getIndexName() . 'm=home');
            return;
        }

        // Build the javascript for navigation
        $js = '';
        for ($i=0; $i<count($_SESSION['CATS_WIZARD']['pages']); $i++)
        {
            $js .= sprintf('addWizardPage("%s", %s, %s);%s',
                addslashes($_SESSION['CATS_WIZARD']['pages'][$i]['title']),
                $_SESSION['CATS_WIZARD']['pages'][$i]['disableNext'] ? 'true' : 'false',
                $_SESSION['CATS_WIZARD']['pages'][$i]['disableSkip'] ? 'true' : 'false',
                "\n"
            );
        }
        $js .= sprintf('var finishURL = \'%s\';', $_SESSION['CATS_WIZARD']['finishURL'], "\n");
        $js .= sprintf('var currentPage = %d;%s', $_SESSION['CATS_WIZARD']['curPage'], "\n");
        $this->_template->assign('js', $js);

        if (isset($_SESSION['CATS_WIZARD']['js'])) $jsInclude = $_SESSION['CATS_WIZARD']['js'];
        else $jsInclude = '';

        $this->_template->assign('jsInclude', $jsInclude);
        $this->_template->assign('pages', $_SESSION['CATS_WIZARD']['pages']);
        $this->_template->assign('currentPage', $_SESSION['CATS_WIZARD']['pages'][$_SESSION['CATS_WIZARD']['curPage']-1]);
        $this->_template->assign('currentPageIndex', $_SESSION['CATS_WIZARD']['curPage']-1);
        $this->_template->assign('active', $this);
        $this->_template->assign('enableSkip', true);
        $this->_template->assign('enablePrevious', $_SESSION['CATS_WIZARD']['curPage']==1 ? false : true);
        $this->_template->assign('enableNext', true);

        $this->_template->display('./modules/wizard/Show.tpl');
    }

    public function ajax_getPage()
    {
        if (!isset($_SESSION['CATS_WIZARD']) || !is_array($_SESSION['CATS_WIZARD']) ||
            !count($_SESSION['CATS_WIZARD']))
        {
            echo 'This wizard has no pages.';
            return;
        }

        // Get the current page of the wizard
        if (isset($_GET['currentPage'])) $currentPage = intval($_GET['currentPage']); else $currentPage = 1;
        if ($currentPage < 1 || $currentPage > count($_SESSION['CATS_WIZARD']['pages'])) $currentPage = 1;

        if (isset($_GET['requestAction'])) $requestAction = $_GET['requestAction']; else $requestAction = '';
        switch ($requestAction)
        {
            case 'next':
                $requestPage = $currentPage + 1;
                break;
            case 'previous':
                $requestPage = $currentPage - 1;
                break;
            case 'skip':
                $requestPage = count($_SESSION['CATS_WIZARD']);
                break;
            case 'current':
            default:
                $requestPage = $currentPage;
                break;
        }

        // Set session variables (if they exist)
        if (isset($_SESSION['CATS']) && !empty($_SESSION['CATS']))
        {
            $session = $_SESSION['CATS'];
            $this->_template->assign('userID', $userID = $session->getUserID());
            $this->_template->assign('userName', $userName = $session->getUserName());
            $this->_template->assign('siteID', $siteID = $session->getSiteID());
            $this->_template->assign('siteName', $siteName = $session->getSiteName());
        }

        // Figure out which template to display
        if (!isset($_SESSION['CATS_WIZARD']['pages'][$requestPage -= 1])) $requestPage = 0;
        $template = $_SESSION['CATS_WIZARD']['pages'][$requestPage]['template'];
        $_SESSION['CATS_WIZARD']['curPage'] = $requestPage + 1;

        if (($php = $_SESSION['CATS_WIZARD']['pages'][$requestPage]['php']) != '')
        {
            eval($php);
        }

        $this->_template->display($template);
    }
}

?>
