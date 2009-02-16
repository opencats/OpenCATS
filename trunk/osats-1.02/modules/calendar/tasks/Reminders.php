<?php
/**
 * OSATS
 */
include_once('./modules/queue/lib/Task.php');
include_once('./lib/Calendar.php');
include_once('./lib/DateUtility.php');
include_once('./lib/SystemUtility.php');

class Reminders extends Task
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
        return '* * * * *';
    }

    public function run($siteID, $args)
    {
        Task::setName('Calendar Reminders');
        Task::setDescription('Send out reminder e-mails from the CATS calendar.');

        $calendar = new Calendar(0);

        //Check for reminders that need to be sent out.
        $dueEvents = $calendar->getAllDueReminders();

        // Do/log nothing if no events exist
        if (!count($dueEvents))
        {
            return TASKRET_SUCCESS_NOLOG;
        }

        foreach ($dueEvents as $index => $data)
        {
            $emailSubject = 'CATS Event Reminder: ' . $data['title'];

            $emailContents = $GLOBALS['eventReminderEmail'];

            $stringsToFind = array(
                '%FULLNAME%',
                '%NOTES%',
                '%EVENTNAME%',
                '%DUETIME%',
            );
            $replacementStrings = array(
                $data['enteredByFirstName'] . ' ' . $data['enteredByLastName'],
                $data['description'],
                $data['title'],
                self::_getReminderTimeString($data['reminderTime'])
            );
            $emailContents = str_replace(
                $stringsToFind,
                $replacementStrings,
                $emailContents
            );

            $emailDestination = $data['reminderEmail'];

            // SEND E-Mail here
            $calendar->sendEmail(
                $data['siteID'],
                0,
                $emailDestination,
                $emailSubject,
                $emailContents
            );

            // Remove alert.
            $calendar->updateEventDisableReminder($data['eventID']);
        }

        // Set the response the task wants logged
        $this->setResponse(sprintf(
            'E-mailed %d calendar reminders.',
            count($dueEvents)
        ));

        return TASKRET_SUCCESS;
    }

    private function _getReminderTimeString($reminderTime)
    {
        if ($reminderTime < 1)
        {
            $string = 'immediately';
        }
        else if ($reminderTime == 1)
        {
            $string = 'in 1 minute';
        }
        else if ($reminderTime < 60)
        {
            $string = 'in ' . $reminderTime . ' minutes';
        }
        else if ($reminderTime == 60)
        {
            $string = 'in 1 hour';
        }
        else if ($reminderTime < 1440)
        {
            $string = 'in ' . (($reminderTime * 1.0) / 60) . ' hours';
        }
        else if ($reminderTime == 1440)
        {
            $string = 'in 1 day';
        }
        else
        {
            $string = 'in ' . (($reminderTime * 1.0) / 1440) . ' days';
        }

    	return $string;
    }
}