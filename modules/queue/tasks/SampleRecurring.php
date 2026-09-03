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

include_once(LEGACY_ROOT . '/modules/queue/lib/Task.php');

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

    public function run($args)
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
