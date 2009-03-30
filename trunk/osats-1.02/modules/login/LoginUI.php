<?php
/*
 * OSATS
 * Login
 *
 *
 */

include_once('./lib/SystemInfo.php');
include_once('./lib/Mailer.php');
include_once('./lib/Site.php');
include_once('./lib/i18n.php');

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
        if ($_SESSION['OSATS']->isLoggedIn() &&
            $_SESSION['OSATS']->checkForceLogout())
        {
            $siteName = $_SESSION['OSATS']->getUnixName();
        }

        $this->_template->assign('aspMode', false);

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

        $this->_template->assign('aspMode', false);

        if (!eval(Hooks::get('SHOW_LOGIN_FORM_POST'))) return;

        /* Display the login page. */
        $this->_template->assign('message', $message);
        $this->_template->assign('username', $username);
        $this->_template->assign('reloginVars', $reloginVars);
        $this->_template->assign('siteName', $siteName);
        $this->_template->assign('siteNameFull', $siteNameFull);
        $this->_template->assign('dateString', date('l, F jS, Y'));

        if (!eval(Hooks::get('SHOW_LOGIN_FORM_POST_2'))) return;

        if (ModuleUtility::moduleExists("asp"))
                $this->_template->display('./modules/asp/AspLogin.tpl');
            else
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

            $this->_template->assign('aspMode', false);

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
        $_SESSION['OSATS']->processLogin($username, $password);

        /* If unsuccessful, take the user back to the login page. */
        if (!$_SESSION['OSATS']->isLoggedIn())
        {
            $message = $_SESSION['OSATS']->getLoginError();

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

            $this->_template->assign('aspMode', false);

            if (!eval(Hooks::get('LOGIN_UNSUCCESSFUL'))) return;

            $this->_template->assign('message', $message);
            $this->_template->assign('messageSuccess', false);
            $this->_template->assign('siteName', $siteName);
            $this->_template->assign('siteNameFull', $siteNameFull);
            $this->_template->assign('dateString', date('l, F jS, Y'));
            if (ModuleUtility::moduleExists("asp"))
                $this->_template->display('./modules/asp/AspLogin.tpl');
            else
                $this->_template->display('./modules/login/Login.tpl');

            return;
        }

        $systemInfoDb = new SystemInfo();

        $accessLevel = $_SESSION['OSATS']->getAccessLevel();

        $mailerSettings = new MailerSettings($_SESSION['OSATS']->getSiteID());
        $mailerSettingsRS = $mailerSettings->getAll();
        if (!eval(Hooks::get('LOGGED_IN'))) return;
        if (isset($_GET['reloginVars']))
        {
            osatutil::transferRelativeURI($_GET['reloginVars']);
        }
        /* If nothing else has stopped us, just go to the home page. */
        if (!eval(Hooks::get('LOGGED_IN_HOME_PAGE'))) return;
        osatutil::transferRelativeURI('m=home');
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
                $this->_template->assign('message',__('Unable to send password to address specified.'));
                $this->_template->assign('complete', false);
            }
        }
        else
        {
            $this->_template->assign('message', __('No such username found.'));
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