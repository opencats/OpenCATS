<?php
/*
 * CATS
 * Login Module
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
 * $Id: LoginUI.php 3720 2007-11-27 21:06:13Z andrew $
 */

include_once('./lib/SystemInfo.php');
include_once('./lib/Mailer.php');
include_once('./lib/Site.php');
include_once('./lib/NewVersionCheck.php');
include_once('./lib/Wizard.php');
include_once('./lib/License.php');

class LoginUI extends UserInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = false;
        $this->_moduleName = 'login';
        $this->_moduleDirectory = 'login';
    }

    public function handleRequest()
    {
        $action = $this->getAction();
        switch ($action)
        {
            case 'attemptLogin':
                $this->attemptLogin();
                break;

            case 'forgotPassword':
                if ($this->isPostBack())
                {
                    $this->onForgotPassword();
                }
                else
                {
                    $this->forgotPassword();
                }
                break;

            case 'noCookiesModal':
                $this->noCookiesModal();
                break;

            case 'showLoginForm':
            default:
                $this->showLoginForm();
                break;
        }
    }


    /*
     * Called by handleRequest() to handle displaying the initial login form.
     */
    private function showLoginForm()
    {
        /* The username can be pre-filled in the input box by specifing
         * "&loginusername=Username" in the URL.
         */
        $username = $this->getTrimmedInput('loginusername', $_GET);

        /* If GET variables exist, preserve them so that after login, the user
         * can be transfered to the URL they were trying to access.
         */
        $reloginVars = $this->_getReloginVars();

        /* A message can be specified in the url via "&message=Message". The
         * message can be displayed as either an error or a "success" notice.
         * This is controlled by specifing "&messageSuccess=true" or
         * "&messageSuccess=false" in the URL.
         */
        $message = $this->getTrimmedInput('message', $_GET);
        if (isset($_GET['messageSuccess']) &&
            $_GET['messageSuccess'] == 'true')
        {
            $this->_template->assign('messageSuccess', true);
        }
        else
        {
            $this->_template->assign('messageSuccess', false);
        }

        /* A site name can be specified in the URL via "&s=Name". */
        if (isset($_GET['s']))
        {
            $siteName = $_GET['s'];
        }
        else
        {
            $siteName = '';
        }

        /* Only allow one user to be logged into a single account at the same
         * time.
         */
        if ($_SESSION['CATS']->isLoggedIn() &&
            $_SESSION['CATS']->checkForceLogout())
        {
            $siteName = $_SESSION['CATS']->getUnixName();
        }

        if (!eval(Hooks::get('SHOW_LOGIN_FORM_PRE'))) return;

        /* If a site was specified, get the site's full name from its
         * unixname.
         */
        if ($siteName != '')
        {
            $site = new Site(-1);
            $rs = $site->getSiteByUnixName($siteName);

            if (!empty($rs))
            {
                $siteNameFull = $rs['name'];
            }
            else
            {
                $siteNameFull = 'error';
            }
        }
        else
        {
            $siteNameFull = '';
        }

        if (!eval(Hooks::get('SHOW_LOGIN_FORM_POST'))) return;

        /* Display the login page. */
        $this->_template->assign('message', $message);
        $this->_template->assign('username', $username);
        $this->_template->assign('reloginVars', $reloginVars);
        $this->_template->assign('siteName', $siteName);
        $this->_template->assign('siteNameFull', $siteNameFull);
        $this->_template->assign('dateString', date('l, F jS, Y'));

        if (!eval(Hooks::get('SHOW_LOGIN_FORM_POST_2'))) return;

        $this->_template->display('./modules/login/Login.tpl');
    }

    private function noCookiesModal()
    {
        if (!eval(Hooks::get('NO_COOKIES_MODAL'))) return;

        $this->_template->display('./modules/login/NoCookiesModal.tpl');
    }

    /*
     * Called by handleRequest() to handle attempting to log in a user.
     */
    private function attemptLogin()
    {
        //FIXME: getTrimmedInput()!
        if (isset($_POST['siteName']))
        {
            $siteName = $_POST['siteName'];
        }
        else
        {
            $siteName = '';
        }

        if (!isset($_POST['username']) || !isset($_POST['password']))
        {
            $message = 'Invalid username or password.';

            if (isset($_GET['reloginVars']))
            {
                $this->_template->assign('reloginVars', urlencode($_GET['reloginVars']));
            }
            else
            {
                $this->_template->assign('reloginVars', '');
            }

            $site = new Site(-1);
            $rs = $site->getSiteByUnixName($siteName);
            if (isset($rs['name']))
            {
                $siteNameFull = $rs['name'];
            }
            else
            {
                $siteNameFull = $siteName;
            }

            if (!eval(Hooks::get('LOGIN_NO_CREDENTIALS'))) return;

            $this->_template->assign('message', $message);
            $this->_template->assign('messageSuccess', false);
            $this->_template->assign('siteName', $siteName);
            $this->_template->assign('siteNameFull', $siteNameFull);
            $this->_template->assign('dateString', date('l, F jS, Y'));

            $this->_template->display('./modules/login/Login.tpl');

            return;
        }

        $username = $this->getTrimmedInput('username', $_POST);
        $password = $this->getTrimmedInput('password', $_POST);

        if (strpos($username, '@') !== false)
        {
            $siteName = '';
        }

        if ($siteName != '')
        {
            $site = new Site(-1);
            $rs = $site->getSiteByUnixName($siteName);
            if (isset($rs['siteID']))
            {
                $username .= '@' . $rs['siteID'];
            }
        }

        /* Make a blind attempt at logging the user in. */
        $_SESSION['CATS']->processLogin($username, $password);

        /* If unsuccessful, take the user back to the login page. */
        if (!$_SESSION['CATS']->isLoggedIn())
        {
            $message = $_SESSION['CATS']->getLoginError();

            if (isset($_GET['reloginVars']))
            {
                $this->_template->assign('reloginVars', urlencode($_GET['reloginVars']));
            }
            else
            {
                $this->_template->assign('reloginVars', '');
            }

            $site = new Site(-1);
            $rs = $site->getSiteByUnixName($siteName);
            if (isset($rs['name']))
            {
                $siteNameFull = $rs['name'];
            }
            else
            {
                $siteNameFull = $siteName;
            }

            if (!eval(Hooks::get('LOGIN_UNSUCCESSFUL'))) return;

            $this->_template->assign('message', $message);
            $this->_template->assign('messageSuccess', false);
            $this->_template->assign('siteName', $siteName);
            $this->_template->assign('siteNameFull', $siteNameFull);
            $this->_template->assign('dateString', date('l, F jS, Y'));
            $this->_template->display('./modules/login/Login.tpl');

            return;
        }

        $systemInfoDb = new SystemInfo();

        $accessLevel = $_SESSION['CATS']->getAccessLevel(ACL::SECOBJ_ROOT);

        $mailerSettings = new MailerSettings($_SESSION['CATS']->getSiteID());
        $mailerSettingsRS = $mailerSettings->getAll();

        /***************************** BEGIN NEW WIZARD *****************************************/
        /**
         * Improved setup wizard using the Wizard library. If the user succeeds,
         * all old-style wizards will no longer be shown.
         */

        $wizard = new Wizard(CATSUtility::getIndexName() . '?m=home', './js/wizardIntro.js');
        if ($_SESSION['CATS']->isFirstTimeSetup())
        {
            $wizard->addPage('Welcome!', './modules/login/wizard/Intro.tpl', '', false, true);
        }

        if (!$_SESSION['CATS']->isAgreedToLicense())
        {
            $phpeval = '';
            if (!eval(Hooks::get('LICENSE_TERMS'))) return;
            $wizard->addPage('License', './modules/login/wizard/License.tpl', $phpeval, true, true);
        }

        if (defined('CATS_TEST_MODE') && CATS_TEST_MODE)
        {
            // On-site wizard pages
            if (!LicenseUtility::isLicenseValid())
            {
                if (defined('LICENSE_KEY') && LICENSE_KEY == '')
                {
                    $template = 'Register.tpl';
                    $templateName = 'Register';
                }
                else
                {
                    $template = 'Reregister.tpl';
                    $templateName = 'License Expired';
                }
                $wizard->addPage($templateName, './modules/login/wizard/' . $template, '', false, true);
            }
        }

        // if logged in for the first time, change password
        if (strtolower($username) == 'admin' && $password === DEFAULT_ADMIN_PASSWORD)
        {
            $wizard->addPage('Password', './modules/login/wizard/Password.tpl', '', false, true);
        }

        // make user set an e-mail address
        if (trim($_SESSION['CATS']->getEmail()) == '')
        {
            $wizard->addPage('E-mail', './modules/login/wizard/Email.tpl', '', false, true);
        }

        // if no site name set, make user set site name
        if ($accessLevel >= ACCESS_LEVEL_SA && $_SESSION['CATS']->getSiteName() === 'default_site')
        {
            $wizard->addPage('Site', './modules/login/wizard/SiteName.tpl', '', false, true);
        }

        // CATS Hosted Wizard Pages
        if (!eval(Hooks::get('ASP_WIZARD_PAGES'))) return;

        if ($_SESSION['CATS']->isFirstTimeSetup())
        {
            $wizard->addPage('Setup Users', './modules/login/wizard/Users.tpl', '
                $users = new Users($siteID);
                $mp = $users->getAll();
                $data = $users->getLicenseData();

                $this->_template->assign(\'users\', $mp);
                $this->_template->assign(\'totalUsers\', $data[\'totalUsers\']);
                $this->_template->assign(\'userLicenses\', $data[\'userLicenses\']);
                $this->_template->assign(\'accessLevels\', $users->getAccessLevels());
            ');

            if (!eval(Hooks::get('ASP_WIZARD_IMPORT'))) return;
        }

        // The wizard will not display if no pages have been added.
        $wizard->doModal();

        /******************************* END NEW WIZARD *******************************************/

        /* Session is logged in, do we need to send the user to the wizard?
         * This should be done only on the first use, indicated by the
         * admin user's password still being set to the default.
         */

        /* If we have a specific page to go to, go there. */

        /* These hooks are for important things, like disabling the site based on criteria. */
        if (!eval(Hooks::get('LOGGED_IN'))) return;

        if (isset($_GET['reloginVars']))
        {
            CATSUtility::transferRelativeURI($_GET['reloginVars']);
        }

        /* LOGGED_IN_MESSAGES hooks are only for messages which show up on initial login (warnings, etc) */
        if (!eval(Hooks::get('LOGGED_IN_MESSAGES'))) return;

        /* If logged in for the first time, make user change password. */
        if (strtolower($username) == 'admin' &&
            $password === DEFAULT_ADMIN_PASSWORD)
        {
            CATSUtility::transferRelativeURI('m=settings&a=newInstallPassword');
        }

        /* If no site name set, make user set site name. */
        else if ($accessLevel >= ACCESS_LEVEL_SA &&
                 $_SESSION['CATS']->getSiteName() === 'default_site')
        {
            CATSUtility::transferRelativeURI('m=settings&a=upgradeSiteName');
        }

        /* If the default email is set in the configuration, complain to the admin. */
        else if ($accessLevel >= ACCESS_LEVEL_SA &&
                 $mailerSettingsRS['configured'] == '0')
        {
            NewVersionCheck::checkForUpdate();

            $this->_template->assign('inputType', 'conclusion');
            $this->_template->assign('title', 'E-Mail Disabled');
            $this->_template->assign('prompt', 'E-mail features are disabled. In order to enable e-mail features (such as e-mail notifications), please configure your e-mail settings by clicking on the Settings tab and then clicking on Administration.');
            $this->_template->assign('action', $this->getAction());
            $this->_template->assign('home', 'home');
            $this->_template->display('./modules/settings/NewInstallWizard.tpl');
        }

        /* If no E-Mail set for current user, make user set E-Mail address. */
        else if (trim($_SESSION['CATS']->getEmail()) == '')
        {
            CATSUtility::transferRelativeURI('m=settings&a=forceEmail');
        }

        /* If nothing else has stopped us, just go to the home page. */
        else
        {
            if (!eval(Hooks::get('LOGGED_IN_HOME_PAGE'))) return;
            CATSUtility::transferRelativeURI('m=home');
        }
    }

    /*
     * Called by handleRequest() to handle displaying the form for retrieving
     * forgotten passwords.
     */
    private function forgotPassword()
    {
        if (!eval(Hooks::get('FORGOT_PASSWORD'))) return;

        $this->_template->display('./modules/login/ForgotPassword.tpl');
    }

    /*
     * Called by handleRequest() to handle processing the form for retrieving
     * forgotten passwords.
     */
    private function onForgotPassword()
    {
        $username = $this->getTrimmedInput('username', $_POST);

        if (!eval(Hooks::get('ON_FORGOT_PASSWORD'))) return;

        $user = new Users($this->_siteID);
        if ($password = $user->getPassword($username))
        {
            $mailer = new Mailer($this->_siteID);
            $mailerStatus = $mailer->sendToOne(
                array($username, $username),
                PASSWORD_RESET_SUBJECT,
                sprintf(PASSWORD_RESET_BODY, $password),
                true
            );

            if ($mailerStatus)
            {
                $this->_template->assign('username', $username);
                $this->_template->assign('complete', true);
            }
            else
            {
                $this->_template->assign('message',' Unable to send password to address specified.');
                $this->_template->assign('complete', false);
            }
        }
        else
        {
            $this->_template->assign('message', 'No such username found.');
            $this->_template->assign('complete', false);
        }

        $this->_template->display('./modules/login/ForgotPassword.tpl');
    }


    // FIXME: Document me.
    private function _getReloginVars()
    {
        if (empty($_GET))
        {
            return '';
        }

        $getFormatted = array();
        foreach ($_GET as $key => $value)
        {
            if (($key == 'm' && $value == 'logout') ||
                ($key == 'm' && $value == 'login') ||
                ($key == 's'))
            {
                continue;
            }

            $getFormatted[] = urlencode($key) . '=' . urlencode($value);
        }

        return urlencode(implode('&', $getFormatted));
    }
}

?>
