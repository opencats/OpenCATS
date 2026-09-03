<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

/* Do we need to run the installer? */

include_once('./config.php');

if (!file_exists('INSTALL_BLOCK') && !isset($_POST['performMaintenence']))
{
    include(LEGACY_ROOT . '/modules/install/notinstalled.php');
    die();
}

// FIXME: Config file setting.
@ini_set('memory_limit', '64M');

/* Hack to make CATS work with E_STRICT. */
if (function_exists('date_default_timezone_set'))
{
    @date_default_timezone_set(date_default_timezone_get());
}

include_once(LEGACY_ROOT . '/constants.php');
include_once(LEGACY_ROOT . '/lib/CommonErrors.php');
include_once(LEGACY_ROOT . '/lib/CATSUtility.php');
include_once(LEGACY_ROOT . '/lib/DatabaseConnection.php');
include_once(LEGACY_ROOT . '/lib/Template.php');
include_once(LEGACY_ROOT . '/lib/Users.php');
include_once(LEGACY_ROOT . '/lib/MRU.php');
include_once(LEGACY_ROOT . '/lib/Hooks.php');
include_once(LEGACY_ROOT . '/lib/Session.php'); /* Depends: MRU, Users, DatabaseConnection. */
include_once(LEGACY_ROOT . '/lib/UserInterface.php'); /* Depends: Template, Session. */
include_once(LEGACY_ROOT . '/lib/ModuleUtility.php'); /* Depends: UserInterface */
include_once(LEGACY_ROOT . '/lib/TemplateUtility.php'); /* Depends: ModuleUtility, Hooks */
include_once(LEGACY_ROOT . '/lib/SchemaMigrationStatus.php');


/* Give the session a unique name to avoid conflicts and start the session. */
@session_name(CATS_SESSION_NAME);
session_start();

/* Try to prevent caching. */
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

/* Objects can't be stored in the session if session.auto_start is enabled. */
if (ini_get('session.auto_start') !== '0' &&
    ini_get('session.auto_start') !== 'Off')
{
    die('CATS Error: session.auto_start must be set to 0 in php.ini.');
}

/* Proper extensions loaded?! */
if (!function_exists('mysqli_connect') || !function_exists('session_start'))
{
    die('OpenCATS Error: Either PHP Sessions extension or MySQLi extension is not loaded.');
}

/* Make sure we have a Session object stored in the user's session. */
if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
{
    $_SESSION['CATS'] = new CATSSession();
}

/* Start timer for measuring server response time. Displayed in footer. */
$_SESSION['CATS']->startTimer();

/* Check to see if the server went through a SVN update while the session
 * was active.
 */
$_SESSION['CATS']->checkForcedUpdate();

/* Check to see if the user level suddenly changed. If the user was changed to disabled,
 * also log the user out.
 */
// FIXME: This is slow!
if ($_SESSION['CATS']->isLoggedIn())
{
    $users = new Users();
    $forceLogoutData = $users->getForceLogoutData($_SESSION['CATS']->getUserID());

    if (!empty($forceLogoutData) && ($forceLogoutData['forceLogout'] == 1 ||
        $_SESSION['CATS']->getRealAccessLevel() != $forceLogoutData['accessLevel']))
    {
        $_SESSION['CATS']->setRealAccessLevel($forceLogoutData['accessLevel']);

        if ($forceLogoutData['accessLevel'] == ACCESS_LEVEL_DISABLED ||
            $forceLogoutData['forceLogout'] == 1)
        {
            /* Log the user out. */
            $_SESSION['CATS']->logout();
            unset($_SESSION['CATS']);
            unset($_SESSION['modules']);

            CATSUtility::transferRelativeURI('m=login');
            die();
        }
    }
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' &&
    $_SESSION['CATS']->isLoggedIn() &&
    (!isset($careerPage) || !$careerPage) &&
    (!isset($_GET['showCareerPortal']) || $_GET['showCareerPortal'] != '1') &&
    (!isset($rssPage) || !$rssPage) &&
    (!isset($xmlPage) || !$xmlPage))
{
    $token = null;

    if (isset($_POST['csrfToken']))
    {
        $token = $_POST['csrfToken'];
    }

    if (!$_SESSION['CATS']->isCSRFTokenValid($token))
    {
        CommonErrors::fatal(COMMONERROR_BADFIELDS, null, 'Invalid request.');
    }
}

$isPublicRequest =
    (isset($careerPage) && $careerPage) ||
    (isset($_GET['showCareerPortal']) && $_GET['showCareerPortal'] == '1') ||
    (isset($rssPage) && $rssPage) ||
    (isset($xmlPage) && $xmlPage);
$isMigrationGateExcluded =
    (isset($_GET['m']) && ($_GET['m'] === 'login' || $_GET['m'] === 'logout'));

if ($_SESSION['CATS']->isLoggedIn() &&
    !$isPublicRequest &&
    !$isMigrationGateExcluded &&
    SchemaMigrationStatus::hasPendingInstallMigrations())
{
    $template = new Template();
    $template->assign(
        'isAdministrator',
        $_SESSION['CATS']->getAccessLevel(ACL::SECOBJ_ROOT) >= ACCESS_LEVEL_SA
    );
    $template->display('./modules/login/PendingMigrations.tpl');
    die();
}

/* Check to see if we are supposed to display the career page. */
if (((isset($careerPage) && $careerPage) ||
    (isset($_GET['showCareerPortal']) && $_GET['showCareerPortal'] == '1')))
{
    if (SchemaMigrationStatus::hasPendingInstallMigrations())
    {
        header('HTTP/1.1 503 Service Unavailable');
        header('Content-Type: text/html; charset=UTF-8');

        echo '<!DOCTYPE html>',
             '<html><head><title>Career Portal Maintenance</title></head><body>',
             '<p>The career portal is temporarily unavailable while system maintenance is in progress. Please try again later.</p>',
             '</body></html>';
        die();
    }

    ModuleUtility::loadModule('careers');
}

/* Check to see if we are supposed to display an rss page. */
else if (isset($rssPage) && $rssPage)
{
    ModuleUtility::loadModule('rss');
}

else if (isset($xmlPage) && $xmlPage)
{
    ModuleUtility::loadModule('xml');
}

/* Check to see if the user was forcibly logged out (logged in from another browser). */
else if ($_SESSION['CATS']->isLoggedIn() &&
    (!isset($_GET['m']) || ModuleUtility::moduleRequiresAuthentication($_GET['m'])) &&
    $_SESSION['CATS']->checkForceLogout())
{
    // FIXME: Unset session / etc.?
    ModuleUtility::loadModule('login');
}

/* If user specified a module, load it; otherwise, load the home module. */
else if (!isset($_GET['m']) || empty($_GET['m']))
{
    if ($_SESSION['CATS']->isLoggedIn())
    {
        $_SESSION['CATS']->logPageView();

        if (!eval(Hooks::get('INDEX_LOAD_HOME'))) return;

        ModuleUtility::loadModule('home');
    }
    else
    {
        ModuleUtility::loadModule('login');
    }
}
else
{
    if ($_GET['m'] == 'logout')
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, null, 'Invalid request.');
        }

        /* There isn't really a logout module. It's just a few lines. */
        $_SESSION['CATS']->logout();
        unset($_SESSION['CATS']);
        unset($_SESSION['modules']);

        $URI = 'm=login';

        if (isset($_POST['message']))
        {
            $URI .= '&message=' . urlencode($_POST['message']);
        }
        else if (isset($_GET['message']))
        {
            $URI .= '&message=' . urlencode($_GET['message']);
        }

        if (isset($_POST['messageSuccess']))
        {
            $URI .= '&messageSuccess=' . urlencode($_POST['messageSuccess']);
        }
        else if (isset($_GET['messageSuccess']))
        {
            $URI .= '&messageSuccess=' . urlencode($_GET['messageSuccess']);
        }

        /* catsone.com demo domain doesn't relogin. */
        if (strpos(CATSUtility::getIndexName(), '://demo.catsone.com') !== false)
        {
            CATSUtility::transferURL('http://www.catsone.com');
        }
        else
        {
            CATSUtility::transferRelativeURI($URI);
        }
    }
    else if (!ModuleUtility::moduleRequiresAuthentication($_GET['m']))
    {
        /* No authentication required; load the module. */
        ModuleUtility::loadModule($_GET['m']);
    }
    else if (!$_SESSION['CATS']->isLoggedIn())
    {
        /* User isn't logged in and authentication is required; send the user
         * to the login page.
         */
        ModuleUtility::loadModule('login');
    }
    else
    {
        /* Everything's good; load the requested module. */
        $_SESSION['CATS']->logPageView();
        ModuleUtility::loadModule($_GET['m']);
    }
}

?>
