<?php
/*
   * OSATS
   *
   *
   *
*/
$OSATSHome = realpath(dirname(__FILE__) . '/');

chdir($OSATSHome);

include_once('./config.php');
include_once('./constants.php');
include_once('./lib/osatutil.php');
include_once('./lib/DatabaseConnection.php');
include_once('./lib/DateUtility.php');
include_once('./lib/Template.php');
include_once('./lib/Users.php');
include_once('./lib/MRU.php');
include_once('./lib/Hooks.php');
include_once('./lib/Session.php'); /* Depends: MRU, Users, DatabaseConnection. */
include_once('./lib/UserInterface.php'); /* Depends: Template, Session. */
include_once('./lib/ModuleUtility.php'); /* Depends: UserInterface */
include_once('./lib/TemplateUtility.php'); /* Depends: ModuleUtility, Hooks */
include_once('./lib/QueueProcessor.php');
include_once('./modules/queue/constants.php');

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
    $_SESSION['OSATS'] = new OSATSSession();
}

// Register module specific tasks
$taskedModules = ModuleUtility::registerModuleTasks();

print_r($taskedModules);

// Execute the next appropriate (if available) queue and return a status code
$retVal = QueueProcessor::startNextTask();

// Mark the queue processor last-run time
touch(QUEUE_STATUS_FILE);

if (file_exists(QUEUE_CLEANUP_FILE))
{
    $lastCleanupTime = @filemtime(QUEUE_CLEANUP_FILE);
}
else {
    $lastCleanupTime = 0;
}

if( ((time() - $lastCleanupTime) > QUEUE_CLEANUP_HOURS*60*60) || !$lastCleanupTime )
{
    @touch(QUEUE_CLEANUP_FILE);
    QueueProcessor::cleanUpErroredTasks();
    QueueProcessor::cleanUpOldQueues();
}

echo "OSATS Queue Processor status: ";
switch($retVal)
{
    case TASKRET_ERROR:
        echo "ERROR";
        break;
    case TASKRET_FAILURE:
        echo "FAILURE";
        break;
    case TASKRET_NO_TASKS:
        echo "NO TASKS";
        break;
    case TASKRET_SUCCESS:
        echo "SUCCESS";
        break;
    case TASKRET_SUCCESS:
        echo "SUCCESS (NO LOG)";
        break;
}
echo "\n";

?>