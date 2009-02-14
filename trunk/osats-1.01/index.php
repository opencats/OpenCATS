<?php

/*
 * OSATS
 *
 */

/* Retrieve all the value from the System table to check if OSATS has been installed yet.
   * If the value is null then its false, if the value is 1 then its true.
*/
include_once('./config.php');

mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS) or die(mysql_error("Check your mySQL user info and DB name!"));
mysql_select_db(DATABASE_NAME) or die(mysql_error("Oops. No DB by the name: ".DATABASE_NAME));
$result = mysql_query("SELECT Installed FROM system")
or die(mysql_error("I cant find info in the DB called ".DATABASE_NAME));
$row = mysql_result( $result,'Installed' );
if ($row==null)//if the table does not have a 1 in it, then run the setup wizard.
{
    include('installation.php');
	die();
}

// FIXME: Config file setting.
@ini_set('memory_limit', '64M');

/* Hack to make OSATS work with E_STRICT. */
if (function_exists('date_default_timezone_set'))
{
    @date_default_timezone_set(date_default_timezone_get());
}

/* Start error handler if ASP error handler exists and this isn't a localhost
 * connection.
 */
if (file_exists('modules/asp/lib/ErrorHandler.php') &&
    @$_SERVER['REMOTE_ADDR'] !== '127.0.0.1' &&
    @$_SERVER['REMOTE_ADDR'] !== '::1' &&
    substr(@$_SERVER['REMOTE_ADDR'], 0, 3) !== '10.')
{
    include_once('modules/asp/lib/ErrorHandler.php');
    $errorHandler = new ErrorHandler();
}
include_once('./constants.php');
include_once('./lib/CommonErrors.php');
include_once('./lib/osatutil.php');
include_once('./lib/DatabaseConnection.php');
include_once('./lib/Template.php');
include_once('./lib/Users.php');
include_once('./lib/MRU.php');
include_once('./lib/Hooks.php');
include_once('./lib/Session.php'); /* Depends: MRU, Users, DatabaseConnection. */
include_once('./lib/UserInterface.php'); /* Depends: Template, Session. */
include_once('./lib/ModuleUtility.php'); /* Depends: UserInterface */
include_once('./lib/TemplateUtility.php'); /* Depends: ModuleUtility, Hooks */


/* Give the session a unique name to avoid conflicts and start the session. */
@session_name(SESSIONNAME);
session_start();

/* Try to prevent caching. */
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

/* Make sure we aren't getting screwed over by magic quotes. */
if (get_magic_quotes_runtime())
{
    set_magic_quotes_runtime(0);
}
if (get_magic_quotes_gpc())
{
    include_once('./lib/ArrayUtility.php');

    $_GET     = array_map('stripslashes', $_GET);
    $_POST    = array_map('stripslashes', $_POST);
    $_REQUEST = array_map('stripslashes', $_REQUEST);
    $_GET     = ArrayUtility::arrayMapKeys('stripslashes', $_GET);
    $_POST    = ArrayUtility::arrayMapKeys('stripslashes', $_POST);
    $_REQUEST = ArrayUtility::arrayMapKeys('stripslashes', $_REQUEST);
}

/* Objects can't be stored in the session if session.auto_start is enabled. */
if (ini_get('session.auto_start') !== '0' &&
    ini_get('session.auto_start') !== 'Off')
{
    die('OSATS Error: session.auto_start must be set to 0 in php.ini.');
}

/* Proper extensions loaded?! */
if (!function_exists('mysql_connect') || !function_exists('session_start'))
{
    die('OSATS Error: All required PHP extensions are not loaded.');
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
    $users = new Users($_SESSION['CATS']->getSiteID());
    $forceLogoutData = $users->getForceLogoutData($_SESSION['CATS']->getUserID());

    if (!empty($forceLogoutData) && ($forceLogoutData['forceLogout'] == 1 ||
        $_SESSION['CATS']->getRealAccessLevel() != $forceLogoutData['accessLevel']))
    {
        $_SESSION['CATS']->setRealAccessLevel($forceLogoutData['accessLevel']);

        if ($forceLogoutData['accessLevel'] == ACCESS_LEVEL_DISABLED ||
            $forceLogoutData['forceLogout'] == 1)
        {
            /* Log the user out. */
            $unixName = $_SESSION['CATS']->getUnixName();

            $_SESSION['CATS']->logout();
            unset($_SESSION['CATS']);
            unset($_SESSION['modules']);

            $URI = 'm=login';

            if (!empty($unixName) && $unixName != 'demo')
            {
                $URI .= '&s=' . $unixName;
            }

            osatutil::transferRelativeURI($URI);
            die();
        }
    }
}

/* Check to see if we are supposed to display the career page. */
if (((isset($careerPage) && $careerPage) ||
    (isset($_GET['showCareerPortal']) && $_GET['showCareerPortal'] == '1')))
{
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
        /* There isn't really a logout module. It's just a few lines. */
        $unixName = $_SESSION['CATS']->getUnixName();

        $_SESSION['CATS']->logout();
        unset($_SESSION['CATS']);
        unset($_SESSION['modules']);

        $URI = 'm=login';

/* Local demo account doesn't relogin. I have remmed the following lines but did not delete
 * until I know it doesnt cause issues on a *nix system
 * Jamin

*        if (!empty($unixName) && $unixName != 'demo')
*        {
*            $URI .= '&s=' . $unixName;
*        }
*/

        if (isset($_GET['message']))
        {
            $URI .= '&message=' . urlencode($_GET['message']);
        }

        if (isset($_GET['messageSuccess']))
        {
            $URI .= '&messageSuccess=' . urlencode($_GET['messageSuccess']);
        }

        /* This will log out and display the main login page.*/
        {
            osatutil::transferRelativeURI($URI);
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

if (isset($errorHandler))
{
    $errorHandler->reportErrors();
}
