<?php
/*
 * CATS
 * CRON Scheduling Module
 *
 * CATS Version: 0.8.0 (Jhelum)
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
 * $Id: cron.php 2582 2007-06-21 19:41:47Z brian $
 */

/* This is probably getting called from cron, so we have to figure out
 * where we are and where CATS is.
 */
 
$CATSHome = realpath(dirname(__FILE__) . '/');

chdir($CATSHome);

include_once('./config.php');
include_once('./constants.php');
include_once('./lib/CATSUtility.php');
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

/* Only 1 instance of cron.php can run at a time. */
$db = DatabaseConnection::getInstance();

if (!$db->isAdvisoryLockFree('CATSCronLock'))
{
    die();
}

$db->getAdvisoryLock('CATSCronLock', 610);
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

$db->releaseAdvisoryLock('CATSCronLock');

?>
