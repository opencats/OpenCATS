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
 * QueueProcessor Task for automatically submitting web forms using
 * PHP Curl to job bulletin boards like SimplyHired or Indeed.
 *
 *
 * $Id: SampleTask.php 3539 2007-11-09 23:03:11Z andrew $
 */

include_once('./modules/queue/lib/Task.php');

/**
 * This is a SAMPLE file for setting up a NON-recurring task with the CATS
 * asynchroneous queue processor. Create a task with the class name
 * that inherits the Task library (included above). The class name
 * must match the file name of the php script.
 *
 * Call your task by inheriting the QueueProcessor library (in /lib) and calling:
 * QueueProcessor::addAsynchronousTask(
 *      $siteID,        // CATS_ADMIN_SITE or site_id if applicable
 *      $taskPath,      // relative path to the task handler php file
 *                      //    (i.e.: ./modules/MODULE_NAME/tasks/TASKNAME.php)
 *      $args,          // Mixed variable type
 *      $priority,      // 1-5 (5 being lowest priority)
 *
 */

class SampleRecurring extends Task
{
    public function run($siteID, $args)
    {
        Task::setName('Sample Non-Recurring Task');
        Task::setDescription('This is the description of this sample task.');

        /**
         * The following are the possible return values of this function.
         * You should put the code you want to run in this function.
         */
        switch (rand(0, 3))
        {
            /**
             * TASKRET_ERROR
             *   This task will not be attempted again. It will be marked as an error
             *   and the development team will be notified.
             */
            case 0:
                $message = 'Error';
                $ret = TASKRET_ERROR;
                break;

            /**
             * TASKRET_FAILURE
             *   This task will be tried again a few times. If it continues to fail, it
             *   will be marked as an error (see above).
             */
            case 1:
                $message = 'Failure (will try again)';
                $ret = TASKRET_FAILURE;
                break;

            /**
             * TASKRET_SUCCESS
             *   This task completed successfully and will be logged.
             */
            case 2:
                $message = 'Success';
                $ret = TASKRET_SUCCESS;
                break;

            /**
             * TASKRET_SUCCESS_NOLOG
             *   The task completed successfully but will not save a log.
             */
            default:
                $message = 'Success (no log)';
                $ret = TASKRET_SUCCESS_NOLOG;
                break;
        }

        // Set the response the task wants logged
        $this->setResponse($message);

        // Return one of the above TASKRET_ constants.
        return $ret;
    }
}
