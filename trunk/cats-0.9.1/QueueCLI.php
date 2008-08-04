<?php
/*
 * CATS
 * Asynchroneous Queue Processor
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
 * This is the command line interface version of the QueueProcessor. This
 * file should be called by cron, bash script, whatever (not the website)
 * to process the next appropriate queue item.
 *
 * $Id: QueueCLI.php 3555 2007-11-11 22:34:51Z will $
 */

$CATSHome = realpath(dirname(__FILE__) . '/');

chdir($CATSHome);

include_once('./config.php');
include_once('./constants.php');
include_once('./lib/CATSUtility.php');
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
@session_name(CATS_SESSION_NAME);
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
    case TASKRET_SUCCESS:
        echo "SUCCESS (NO LOG)";
        break;
}
echo "\n";

?>
