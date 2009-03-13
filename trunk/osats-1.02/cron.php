<?php
/*
 * OSATS
 * Open Source License Applies
 */

/* This is probably getting called from cron, so we have to figure out
 * where we are and where the application is.
 */

$OSATSHome = realpath(dirname(__FILE__) . '/');

chdir($OSATSHome);

include_once('./config.php');
include_once('./constants.php');
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

/* Make sure we aren't getting screwed over by magic quotes. */
if (get_magic_quotes_runtime())
{
    set_magic_quotes_runtime(0);
}
if (get_magic_quotes_gpc())
{
    $_GET     = array_map('stripslashes', $_GET);
    $_POST    = array_map('stripslashes', $_POST);
    $_REQUEST = array_map('stripslashes', $_REQUEST);
}

if (!isset($_SESSION['OSATS']) || empty($_SESSION['OSATS']))
{
    $_SESSION['OSATS'] = new WebSession();
}

/* Only 1 instance of cron.php can run at a time. */
$db = DatabaseConnection::getInstance();

if (!$db->isAdvisoryLockFree('OSATSCronLock'))
{
    die();
}

$db->getAdvisoryLock('OSATSCronLock', 610);
set_time_limit(600);

/* Get the cron timer array, or make a new one. */
if (file_exists('./cron.cache'))
{
    $GLOBALS['cronSettings'] = unserialize(file_get_contents('./cron.cache'));
}
else
{
    $GLOBALS['cronSettings']->schedulerLoops = array();
}

$GLOBALS['cronSettings']->timeExecuted = time();

function isIntervalPassed($interval)
{
    if (!isset($GLOBALS['cronSettings']->schedulerLoops[$interval]))
    {
        $GLOBALS['cronSettings']->schedulerLoops[$interval] = 0;
        return true;
    }

    if ($GLOBALS['cronSettings']->schedulerLoops[$interval] < $GLOBALS['cronSettings']->timeExecuted - ($interval * 60))
    {
        return true;
    }

    return false;
}

ModuleUtility::doModuleEvents();
foreach ($GLOBALS['cronSettings']->schedulerLoops as $interval => $time)
{
    if ($time < $GLOBALS['cronSettings']->timeExecuted - ($interval * 60))
    {
        $GLOBALS['cronSettings']->schedulerLoops[$interval] = $GLOBALS['cronSettings']->timeExecuted;
    }
}

@file_put_contents(('./cron.cache'), serialize($GLOBALS['cronSettings']));

$db->releaseAdvisoryLock('OSATSCronLock');

?>