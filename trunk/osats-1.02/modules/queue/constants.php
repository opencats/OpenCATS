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
 * Constants for the standalone QueueProcessor and QueueProcessor tasks.
 *
 *
 * $Id: constants.php 3597 2007-11-13 17:45:42Z andrew $
 */

define('TASKRET_NO_TASKS',              0);
define('TASKRET_SUCCESS',               1);
define('TASKRET_FAILURE',               2);
define('TASKRET_ERROR',                 3);
define('TASKRET_SUCCESS_NOLOG',         4);

define('QUEUE_CLEANUP_HOURS',           1);

define('QUEUE_CLEANUP_FILE',            'cleanup.time');
define('QUEUE_STATUS_FILE',             'queue.time');

define('QUEUE_TASK_DIR', './modules/queue/tasks');
define('QUEUE_EXPIRATION_DAYS', 7);

define('DEFAULT_QUEUE_TIMEOUT_MINUTES', 60);

?>
