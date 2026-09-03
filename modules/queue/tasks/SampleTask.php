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
 * This is a SAMPLE file for setting up a NON-recurring task with the CATS
 * asynchroneous queue processor. Create a task with the class name
 * that inherits the Task library (included above). The class name
 * must match the file name of the php script.
 *
 * Call your task by inheriting the QueueProcessor library (in /lib) and calling:
 * QueueProcessor::addAsynchronousTask(
 *      $taskPath,      // relative path to the task handler php file
 *                      //    (i.e.: ./modules/MODULE_NAME/tasks/TASKNAME.php)
 *      $args,          // Mixed variable type
 *      $priority,      // 1-5 (5 being lowest priority)
 *
 */

class SampleRecurring extends Task
{
    public function run($args)
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
