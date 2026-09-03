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

$CATSHome = realpath(dirname(__FILE__) . '/');

chdir($CATSHome);

include_once('./config.php');
include_once(LEGACY_ROOT . '/constants.php');
include_once(LEGACY_ROOT . '/lib/CATSUtility.php');
include_once(LEGACY_ROOT . '/lib/DatabaseConnection.php');
include_once(LEGACY_ROOT . '/lib/DateUtility.php');
include_once(LEGACY_ROOT . '/lib/Template.php');
include_once(LEGACY_ROOT . '/lib/Users.php');
include_once(LEGACY_ROOT . '/lib/MRU.php');
include_once(LEGACY_ROOT . '/lib/Hooks.php');
include_once(LEGACY_ROOT . '/lib/Session.php'); /* Depends: MRU, Users, DatabaseConnection. */
include_once(LEGACY_ROOT . '/lib/UserInterface.php'); /* Depends: Template, Session. */
include_once(LEGACY_ROOT . '/lib/ModuleUtility.php'); /* Depends: UserInterface */
include_once(LEGACY_ROOT . '/lib/TemplateUtility.php'); /* Depends: ModuleUtility, Hooks */
include_once(LEGACY_ROOT . '/lib/QueueProcessor.php');
include_once(LEGACY_ROOT . '/modules/queue/constants.php');

/* Give the session a unique name to avoid conflicts and start the session. */
@session_name(CATS_SESSION_NAME);
session_start();

if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
{
    $_SESSION['CATS'] = new CATSSession();
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

echo "CATS Queue Processor status: ";
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
    case TASKRET_SUCCESS_NOLOG:
        echo "SUCCESS (NO LOG)";
        break;
}
echo "\n";

?>
