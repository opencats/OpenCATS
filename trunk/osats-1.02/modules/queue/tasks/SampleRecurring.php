<?php
/*
 * CATS
 * Asynchroneous Queue Processor - Recurring Task
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
 * $Id: SampleRecurring.php 3539 2007-11-09 23:03:11Z andrew $
 */

include_once('./modules/queue/lib/Task.php');

/**
 * This is a SAMPLE file for setting up a recurring task with the CATS
 * asynchroneous queue processor. Create a task with the class name
 * that inherits the Task library (included above). The class name
 * must match the file name of the php script.
 *
 * Create a "tasks" directory (if it doesn't exist) in the module
 * that owns the task. In the directory, create a file called
 * "tasks.php" (if it doesn't exist). The file should include a
 * line formatted like this one:
 *
 * QueueProcessor::registerRecurringTask('./modules/MODULENAME/tasks/TASKNAME.php');
 *
 * TASKNAME should match the name of THIS file. No includes are necessary
 * for the tasks.php file.
 *
 * NOTES about recurring tasks:
 *
 * 1) A RECURRING task will NOT run twice at the same time. If the
 *    same task is already running when it loads, an error will be
 *    created noting this and the task will NOT be run as a second
 *    instance.
 *
 * 2) RECURRING tasks MUST have a getSchedule() function that returns
 *    a crontab-formatted string or they will fail.
 *
 * 3) RECURRING tasks MUST have an entry in modules/queue/tasks.php
 *
 */

class SampleRecurring extends Task
{
    public function getSchedule()
    {
        /**
         * Crontab-formatted string for how often to run the recurring task
         * Examples:
         *     "* * * * *":             Every minute
         *     "1,3,5 * * * *":         1st, 2nd and 5th minute of every hour
         *     "* 1 * * *":             1:00am every day
         *     "* * 1 * *":             The 1st of every month
         *
         * Values are as follows: minute, hour, day of month, month, day of week (0 sun -> 6 mon)
         */
        return '52,53,54 * * * *';
    }

    public function run($siteID, $args)
    {
        Task::setName('Sample Recurring Task');
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
