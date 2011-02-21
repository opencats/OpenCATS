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
 * $Id: CleanExceptions.php 3539 2007-11-09 23:03:11Z andrew $
 */

include_once('./modules/queue/lib/Task.php');

// The number of days a logged exception should be saved before it is deleted.
define('EXCEPTIONS_TTL_DAYS', 7);

class CleanExceptions extends Task
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
        return '* 3 * * *';
    }

    public function run($siteID, $args)
    {
        Task::setName('CleanExceptions');
        Task::setDescription('Clean up the exceptions log.');

        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "DELETE FROM
                exceptions
             WHERE
                DATEDIFF(NOW(), exceptions.date) > %s",
            EXCEPTIONS_TTL_DAYS
        );

        if (!($rs = $db->query($sql)))
        {
            $message = 'Query "' . $sql . '" failed!';
            $ret = TASKRET_ERROR;
        }
        else
        {
            $num = $db->getAffectedRows();

            if ($num > 0)
            {
                $message = 'Cleaned up ' . number_format($num, 0) . ' exception logs.';
                $ret = TASKRET_SUCCESS;
            }
            else
            {
                // Do not log if nothing was done
                $message = 'No logs were cleaned.';
                $ret = TASKRET_SUCCESS_NOLOG;
            }
        }

        // Set the response the task wants logged
        $this->setResponse($message);

        // Return one of the above TASKRET_ constants.
        return $ret;
    }
}
