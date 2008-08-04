<?php
/**
 * CATS
 * Asynchroneous Queue Processor Library
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * This is the library file for the asynchronous queue processor with
 * addon tasks files. It handles the active queue in the "queue" table
 * by locking, marking as error, cleaning up, and executing various
 * queues. Tasks can be added dynamically simply by inserting the file in the
 * queue module's tasks directory.
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
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: QueueProcessor.php 3639 2007-11-16 18:02:56Z andrew $
 */

include_once('./modules/queue/constants.php');
include_once('./lib/Mailer.php');

/**
 *	Asynchroneous Queue Processor Library
 *	@package    CATS
 *	@subpackage Library
 */
class QueueProcessor
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}

    // FIXME: Document me.
    public static function setTaskLock($taskID, $lockCode = 1)
    {
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "UPDATE
                queue
             SET
                locked = %s
            WHERE
                queue_id = %s",
            $db->makeQueryInteger($lockCode),
            $db->makeQueryInteger($taskID)
        );

        return $db->query($sql);
    }

    // FIXME: Document me.
    public static function setTaskError($taskID, $errorCode = 1)
    {
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "UPDATE
                queue
             SET
                error = %s
             WHERE
                queue_id = %s",
            $db->makeQueryInteger($errorCode),
            $db->makeQueryInteger($taskID)
        );

        $rs = $db->query($sql);

        if ($errorCode == 1)
        {
            if (!eval(Hooks::get('QUEUEERROR_NOTIFY_DEV'))) return;
        }

        return $rs;
    }

    // FIXME: Document me.
    public static function setTaskCompleted($taskID, $completedTime = 0)
    {
        $db = DatabaseConnection::getInstance();

        $completedTxt = date('c', ($completedTime ? $completedTime : time()));

        $sql = sprintf(
            "UPDATE
                queue
             SET
                date_completed = %s
             WHERE
                queue_id = %s",
            $db->makeQueryString($completedTxt),
            $db->makeQueryInteger($taskID)
        );

        return $db->query($sql);
    }

    /**
     * A recurring task is one that runs on a defined schedule (every minute, every 5, etc.)
     * without needing to be added to the queue table. Recurring events will never run at the
     * same time.
     *
     * @param string $taskName Name of the task (from the tasks directory)
     */
    public static function registerRecurringTask($taskPath)
    {
        $db = DatabaseConnection::getInstance();

        $taskName = self::getTaskNameFromPath($taskPath);
        $task = self::getInstantiatedTask($taskPath);

        // recurring tasks need a getSchedule() function that returns a crontab string, i.e.: "0,1,5 * * * *", etc.
        if (!self::isTaskReady($task->getSchedule())) return;

        // Check if an old instance of this SAME recurring task is running, do not run over the top of it
        $sql = sprintf(
            "SELECT
                COUNT(queue_id)
             FROM
                queue
             WHERE
                task = %s
             AND
                locked = 1",
            $db->makeQueryString($taskName)
        );
        $cnt = $db->getColumn($sql, 0, 0);

        if ($cnt > 0)
        {
            // Instance of this task is running
            return;
        }

        $taskID = self::addAsynchronousTask(CATS_ADMIN_SITE, $taskName, 0, 5);
        self::startTask(CATS_ADMIN_SITE, $taskPath, 0, 5, $taskID);
    }

    /**
     * loadNextQueue()
     * Locks the next non-locked, non-error-coded, non-completed queue entry
     * with the highest priority and excutes it. It then sets the locked,
     * error-code, or completion date accordingly to the return value.
     */
    public static function startNextTask()
    {
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "SELECT
                *
             FROM queue
             WHERE
                locked = 0
             AND
                error = 0
             AND
                ISNULL(date_completed)
             ORDER BY priority DESC
             LIMIT 1"
        );

        $rs = $db->getAssoc($sql);

        if (!count($rs))
        {
            // There are no current appropriate queues to process, quit
            return TASKRET_NO_TASKS;
        }
        else
        {
            // Process the same return value to the CLI
            return self::startTask($rs['site_id'], $rs['task'], $rs['args'],
                $rs['priority'], $rs['queue_id']
            );
        }
    }

    // FIXME: Document me.
    public static function getInstantiatedTask($taskPath)
    {
        // Figure out the name from the path
        $taskName = self::getTaskNameFromPath($taskPath);

        if ($taskName != '' && file_exists($taskPath))
        {
            // Include the task file and instantiate an instance of the class
            include_once($taskPath);
            eval (sprintf('$curTask = new %s();', $taskName));

            return $curTask;
        }

        return false;
    }

    // FIXME: Document me.
    public static function getTaskNameFromPath($taskPath)
    {
        if (preg_match('/\/([^\/\.]+)\.php$/', $taskPath, $matches))
        {
            return $matches[1];
        }
        return '';
    }

    // FIXME: Document me.
    public static function startTask($siteID, $taskPath, $args, $priority, $taskID)
    {
        self::setTaskLock($taskID, 1);

        $taskName = self::getTaskNameFromPath($taskPath);
        $curTask = self::getInstantiatedTask($taskPath);

        if (!$curTask)
        {
            self::setTaskResponse($taskID, sprintf(
                'Cannot load task "%s" from "%s".',
                $taskName, $taskPath
            ));
            self::setTaskError($taskID);
            return;
        }

        $curTask->setTaskID($taskID);
        $retVal = $curTask->run($siteID, $args);

        self::setTaskLock($taskID, 0);

        // Handle the return of the process
        switch ($retVal)
        {
            case TASKRET_SUCCESS:
                self::setTaskCompleted($taskID);
                break;

            case TASKRET_FAILURE:
                self::setTaskError($taskID);
                break;

            case TASKRET_ERROR:
                self::setTaskError($taskID);
                break;

            case TASKRET_SUCCESS_NOLOG:
                // Successful, but don't keep a log of success
                self::setTaskCompleted($taskID);
                self::removeTask($taskID);
                break;
        }

        // Process the same return value to the CLI
        return $retVal;
    }

    // FIXME: Document me.
    public static function addAsynchronousTask($siteID, $taskPath, $args, $priority = 5)
    {
        $db = DatabaseConnection::getInstance();

        $timeoutDate = date('c', time()+(DEFAULT_QUEUE_TIMEOUT_MINUTES * 60));

        $sql = sprintf(
            "INSERT INTO
                queue (site_id, task, args, priority, date_created, date_timeout)
             VALUES
                (%s, %s, %s, %s, %s, %s)",
            $db->makeQueryString($siteID),
            $db->makeQueryString($taskPath),
            $db->makeQueryString(serialize($args)),
            $db->makeQueryInteger($priority),
            $db->makeQueryString(date('c')),
            $db->makeQueryString($timeoutDate)
        );

        $rs = $db->query($sql);

        return $db->getLastInsertID();
    }

    // FIXME: Document me.
    public static function removeTask($taskID)
    {
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "DELETE
             FROM
                queue
             WHERE
                queue_id = %s",
            $db->makeQueryInteger($taskID)
        );

        $db->query($sql);

        return $db->getAffectedRows();
    }

    // FIXME: Document me.
    public static function setTaskResponse($taskID, $response)
    {
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "UPDATE
                queue
             SET
                response = %s
             WHERE
                queue_id = %s",
            $db->makeQueryString($response),
            $db->makeQueryInteger($taskID)
        );

        $db->query($sql);

        return $db->getAffectedRows();
    }

    // FIXME: Document me.
    public static function getTaskResponse($taskID)
    {
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "SELECT
                response
             FROM
                queue
             WHERE
                queue_id = %s",
            $db->makeQueryInteger($taskID)
        );

        $rs = $db->query($sql);

        if (($response = $db->getColumn($sql, 0, 0)) !== false)
        {
            return $response;
        }
        else
        {
            return '';
        }
    }

    // FIXME: Document me.
    public static function getActiveTasksCount()
    {
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "SELECT
                COUNT(queue_id)
            FROM
                queue
            WHERE
                locked = 0
            AND
                error = 0
            AND
                ISNULL(date_completed)"
        );

        if (($num = $db->getColumn($sql, 0, 0)) !== false)
        {
            return $num;
        }
        else
        {
            return 0;
        }
    }

    // FIXME: Document me.
    public static function getLockedTasksCount()
    {
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "SELECT
                COUNT(queue_id)
            FROM
                queue
            WHERE
                locked = 1"
        );

        if (($num = $db->getColumn($sql, 0, 0)) !== false)
        {
            return $num;
        }
        else
        {
            return 0;
        }
    }

    // FIXME: Document me.
    public static function getErrorTasksCount()
    {
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "SELECT
                COUNT(queue_id)
            FROM
                queue
            WHERE
                error = 1"
        );

        if (($num = $db->getColumn($sql, 0, 0)) !== false)
        {
            return $num;
        }
        else
        {
            return 0;
        }
    }

    // FIXME: Document me.
    public static function cleanUpOldQueues()
    {
        $db = DatabaseConnection::getInstance();

        // delete completed queues that are QUEUE_EXPIRATION_DAYS old
        $sql = sprintf(
            "DELETE FROM
                queue
             WHERE
                (TO_DAYS(NOW()) - TO_DAYS(date_completed)) > %s
             AND
                locked = 0
             AND
                error = 0
             AND
                NOT ISNULL(date_completed)",
            $db->makeQueryInteger(QUEUE_EXPIRATION_DAYS)
        );

        $db->query($sql);
    }

    // FIXME: Document me.
    public static function cleanUpErroredTasks()
    {
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "UPDATE
                queue
             SET
                error = 1,
                locked = 0
             WHERE
                locked = 1
             AND
                date_timeout <= NOW()"
        );

        $db->query($sql);
    }

    /**
     * Return an epoch timestamp of the last time the queue processor was run.
     *
     * @return int
     */
    public static function getLastRunTime()
    {
        if (@file_exists(QUEUE_STATUS_FILE))
        {
            if (($mTime = @filemtime(QUEUE_STATUS_FILE)) !== false)
            {
                return $mTime;
            }
        }
        return 0;
    }

    /**
     * Makes an educated guess to determine if the Asynchronous Queue Processor
     * is currently active and running. This is determined if the last run
     * time noted is within the last 5 minutes.
     *
     * @param unknown_type $schedule
     * @return unknown
     */
    public static function isActive()
    {
        $lastRunTime = self::getLastRunTime();

        if ((time() - $lastRunTime) < (60*5))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    // FIXME: Document me.
    public static function isTaskReady($schedule)
    {
        $valid = true;
        list ($minute, $hour, $dayofmonth, $month, $dayofweek) = explode(' ', $schedule);
        if ($minute != '*')
        {
            $match = false;
            foreach (explode(',', $minute) as $_minute)
                if (intval($_minute) == self::getMinute()) $match = true;
            if (!$match) $valid = false;
        }
        if ($hour != '*')
        {
            $match = false;
            foreach (explode(',', $hour) as $_hour)
                if (intval($_hour) == self::getHour()) $match = true;

            if ($minute == '*')
            {
                if (self::getMinute() != 1) $match = false;
            }

            if (!$match) $valid = false;
        }
        if ($dayofmonth != '*')
        {
            $match = false;
            foreach (explode(',', $dayofmonth) as $_dayofmonth)
                if (intval($_dayofmonth) == self::getDayOfMonth()) $match = true;

            if ($minute == '*')
            {
                if (self::getMinute() != 1) $match = false;
            }

            if ($hour == '*')
            {
                if (self::getHour() != 1) $match = false;
            }

            if (!$match) $valid = false;
        }
        if ($month != '*')
        {
            $match = false;
            foreach (explode(',', $month) as $_month)
                if (intval($_month) == self::getMonth()) $match = true;

            if ($minute == '*')
            {
                if (self::getMinute() != 1) $match = false;
            }

            if ($hour == '*')
            {
                if (self::getHour() != 1) $match = false;
            }

            if ($dayofmonth == '*')
            {
                if (self::getDayOfMonth() != 1) $match = false;
            }

            if (!$match) $valid = false;
        }
        if ($dayofweek != '*')
        {
            $match = false;
            foreach (explode(',', $dayofweek) as $_dayofweek)
                if (intval($_dayofweek) == self::getDayOfWeek()) $match = true;

            if ($minute == '*')
            {
                if (self::getMinute() != 1) $match = false;
            }

            if ($hour == '*')
            {
                if (self::getHour() != 1) $match = false;
            }

            if ($dayofmonth == '*')
            {
                if (self::getDayOfMonth() != 1) $match = false;
            }

            if (!$match) $valid = false;
        }
        return $valid;
    }

    // FIXME: Document me.
    public function getDayOfMonth()
    {
        return intval(date('j'));
    }

    // FIXME: Document me.
    public function getDayOfWeek()
    {
        return intval(date('w'));
    }

    // FIXME: Document me.
    public function getMonth()
    {
        return intval(date('n'));
    }

    // FIXME: Document me.
    public function getYear()
    {
        return intval(date('Y'));
    }
    // FIXME: Document me.

    public function getHour()
    {
        return intval(date('G'));
    }

    // FIXME: Document me.
    public function getMinute()
    {
        return intval(date('i'));
    }
}



?>
