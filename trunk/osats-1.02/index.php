<?php

/*
 * OSATS
 *
 */

include_once('./dbconfig.php');

//check on timezone setting.
if (function_exists('date_default_timezone_set'))
{
	@date_default_timezone_set(date_default_timezone_get());
} 
else 
{
	echo "date function doesnt exist!";
}

/* Retrieve all the value from the System table to check if OSATS has been installed yet.
   * If the value is null then its false, if the value is 1 then its true.
*/
$myServer = mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS);
	if (!$myServer)
		{
		/* dbserver does not exist or is incorrect. Run installation process*/
			include('_install/install.php');
			die();
		}
	else
		{
			$myDB = mysql_select_db(DATABASE_NAME);
			if (!$myDB)
				{/* dbserver exists but no db found.. Run installation process*/
				include('_install/install.php');
				die();
				}
		}

$result = mysql_query("SELECT Installed FROM system");

if (!$result==null)
	$row = mysql_result( $result, 0);
	if ($row==null)//if the table does not have a 1 in it, then run the setup wizard.
	{
    	include('_install/install.php');
		die();
	}
/* do not allow for caching */
header("Expires: 0"); header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); header("cache-control: no-store, no-cache, must-revalidate"); header("Pragma: no-cache");
//@ini_set('memory_limit', '64M');

/* If all went well above we can continue to load the rest of the items. */
include_once('./constants.php');
include_once('./lib/CommonErrors.php');
include_once('./lib/osatutil.php');
include_once('./lib/DatabaseConnection.php');
include_once('./lib/Template.php');
include_once('./lib/Users.php');
include_once('./lib/MRU.php');
include_once('./lib/Hooks.php');
include_once('./lib/Session.php'); 
include_once('./lib/UserInterface.php'); 
include_once('./lib/ModuleUtility.php'); 
include_once('./lib/TemplateUtility.php');
/* Creates a session or resumes the current one based on the current session id that's being passed via a request, such as GET, POST, or a cookie and give the session a unique name to avoid conflicts and start the session. */
@session_name(SESSIONNAME);
session_start();

/* if you are programming and get stuck on errors... make sure you didnt bust the session. Simply un-remark this next line and hit refresh, then remark this line again. */
//session_destroy();


// Check if magic_quotes_runtime is active
if(get_magic_quotes_runtime())
{
    // if active, then Deactive
    set_magic_quotes_runtime(false);
}

/* Isnt there a better way to do this? - Jamin */
if (get_magic_quotes_gpc())
{
    //echo "magic quotes gpc is true";
	include_once('./lib/ArrayUtility.php');

    $_GET     = array_map('stripslashes', $_GET);
    $_POST    = array_map('stripslashes', $_POST);
    $_REQUEST = array_map('stripslashes', $_REQUEST);
    $_GET     = ArrayUtility::arrayMapKeys('stripslashes', $_GET);
    $_POST    = ArrayUtility::arrayMapKeys('stripslashes', $_POST);
    $_REQUEST = ArrayUtility::arrayMapKeys('stripslashes', $_REQUEST);
}



if (!isset($_SESSION['OSATS']) || empty($_SESSION['OSATS']))
{
    //start a new session.
	$_SESSION['OSATS'] = new WebSession();
}

/* Start timer for measuring server response time. Displayed in footer. */
//$_SESSION['OSATS']->startTimer();


/* Check to see if the user level suddenly changed. If the user was changed to disabled,
 * also log the user out.
 */
// DO WE REALLY CARE?  A better way is to expire the session at certain times!
/*
if ($_SESSION['OSATS']->isLoggedIn())
{
    $users = new Users($_SESSION['OSATS']->getSiteID());
    $forceLogoutData = $users->getForceLogoutData($_SESSION['OSATS']->getUserID());

    if (!empty($forceLogoutData) && ($forceLogoutData['forceLogout'] == 1 ||
        $_SESSION['OSATS']->getRealAccessLevel() != $forceLogoutData['accessLevel']))
    {
        $_SESSION['OSATS']->setRealAccessLevel($forceLogoutData['accessLevel']);

        if ($forceLogoutData['accessLevel'] == ACCESS_LEVEL_DISABLED ||
            $forceLogoutData['forceLogout'] == 1)
        {
            //Log the user out.
            $unixName = $_SESSION['OSATS']->getUnixName();

            $_SESSION['OSATS']->logout();
            unset($_SESSION['OSATS']);
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
//I would like to break away from this and have it seperated so its not called each freaking time we do something in the main app!
// I will come up with something later. - Jamin

if (((isset($careerPage) && $careerPage) || (isset($_GET['showCareerPortal'])) && $_GET['showCareerPortal'] == '1'))
//if (isset($_GET['showCareerPortal']))
{
	ModuleUtility::loadModule('careers');
}

/* Check to see if we are supposed to display an rss page. */
else if (isset($rssPage) && $rssPage)
{
    ModuleUtility::loadModule('rss');
}

//else if (isset($xmlPage) && $xmlPage)
//{
 //   ModuleUtility::loadModule('xml');
//}

/* Check to see if the user was forcibly logged out (logged in from another browser). */

else if ($_SESSION['OSATS']->isLoggedIn() && (!isset($_GET['m']) || ModuleUtility::moduleRequiresAuthentication($_GET['m'])) && $_SESSION['OSATS']->checkForceLogout())
{
    // FIXME: Unset session / etc.?
    ModuleUtility::loadModule('login');
}

/* If user specified a module, load it; otherwise, load the home module. */
else if (!isset($_GET['m']) || empty($_GET['m']))
{
    if ($_SESSION['OSATS']->isLoggedIn())
    {
        $_SESSION['OSATS']->logPageView();

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
        $unixName = $_SESSION['OSATS']->getUnixName();

        $_SESSION['OSATS']->logout();
        unset($_SESSION['OSATS']);
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
    else if (!$_SESSION['OSATS']->isLoggedIn())
    {
        /* User isn't logged in and authentication is required; send the user
         * to the login page.
         */
        ModuleUtility::loadModule('login');
    }
    else
    {
        /* Everything's good; load the requested module. */
        $_SESSION['OSATS']->logPageView();
        ModuleUtility::loadModule($_GET['m']);
    }
}

if (isset($errorHandler))
{
    $errorHandler->reportErrors();
}
