<?php
/*
 * CATS
 * Calendar Module
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
 * $Id: CalendarUI.php 3807 2007-12-05 01:47:41Z will $
 */

include_once('./lib/Calendar.php');
include_once('./lib/DateUtility.php');
include_once('./lib/SystemUtility.php');


class CalendarUI extends UserInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = true;
        $this->_moduleDirectory = 'calendar';
        $this->_moduleName = 'calendar';
        $this->_moduleTabText = 'Calendar*al=' . ACCESS_LEVEL_READ . '@calendar';
        $this->_subTabs = array(
            'My Upcoming Events' => 'javascript:void(0);*js=calendarUpcomingEvents();*al=' . ACCESS_LEVEL_READ . '@calendar',
            'Add Event' => 'javascript:void(0);*js=userCalendarAddEvent();*al=' . ACCESS_LEVEL_EDIT . '@calendar',
            'Goto Today' => 'javascript:void(0);*js=goToToday();*al=' . ACCESS_LEVEL_READ . '@calendar'
        );
    }


    public function handleRequest()
    {
        $action = $this->getAction();

        if (!eval(Hooks::get('CALENDAR_HANDLE_REQUEST'))) return;

        switch ($action)
        {
            case 'addEvent':
                if ($this->isPostBack())
                {
                    $this->onAddEvent();
                }
                break;

            case 'editEvent':
                if ($this->isPostBack())
                {
                    $this->onEditEvent();
                }
                break;

            case 'dynamicData':
                $this->dynamicData();
                break;

            case 'deleteEvent':
                $this->onDeleteEvent();
                break;

            case 'showCalendar':
            default:
                $this->showCalendar();
                break;
        }
    }

    /*
     * Called by handleRequest() to handle displaying the calendar.
     */
    private function showCalendar()
    {
        $currentHour     = DateUtility::getAdjustedDate('H');
        $currentDay      = DateUtility::getAdjustedDate('j');
        $currentMonth    = DateUtility::getAdjustedDate('n');
        $currentYear     = DateUtility::getAdjustedDate('Y');
        $currentUnixTime = DateUtility::getAdjustedDate();
        $currentDateMDY  = DateUtility::getAdjustedDate('m-d-y');

        $currentWeek  = DateUtility::getWeekNumber($currentUnixTime) - DateUtility::getWeekNumber(
            mktime(0, 0, 0, $currentMonth, 1, $currentYear)
        );

        /* Do we have a valid date argument? If a month was specified and
         * isn't valid, fatal() out. If none was specified, use the current
         * month.
         */
        if ($this->isRequiredIDValid('month', $_GET) &&
            $this->isRequiredIDValid('year', $_GET))
        {
            $month = $_GET['month'];
            $year  = $_GET['year'];

            if (!checkdate($month, 1, $year))
            {
                CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid date.');
            }

            if ($month == $currentMonth && $year == $currentYear)
            {
                $isCurrentMonth = true;
            }
            else
            {
                $isCurrentMonth = false;
            }
        }
        else
        {
            $month = $currentMonth;
            $year  = $currentYear;
            $isCurrentMonth = true;
        }

        if (isset($_GET['view']))
        {
            $view = $_GET['view'];
        }
        else
        {
            $view = 'DEFAULT_VIEW';
        }

        if (isset($_GET['week']))
        {
            $week = $_GET['week'];
        }
        else
        {
            $week = $currentWeek+1;
        }

        if (isset($_GET['day']))
        {
            $day = $_GET['day'];
        }
        else
        {
            $day = $currentDay;
        }

        if (isset($_GET['showEvent']))
        {
            $showEvent = $_GET['showEvent'];
        }
        else
        {
            $showEvent = null;
        }

        $userIsSuperUser = ($this->getUserAccessLevel('calendar.show') < ACCESS_LEVEL_SA ? 0 : 1);
        if ($userIsSuperUser && isset($_GET['superuser']) && $_GET['superuser'] == 1)
        {
            $superUserActive = true;
        }
        else
        {
            $superUserActive = false;
        }

        $startingWeekday = DateUtility::getStartingWeekday($month, $year);
        $daysInMonth     = DateUtility::getDaysInMonth($month, $year);

        $calendar = new Calendar($this->_siteID);

        $monthBefore = $month - 1;
        $monthAfter  = $month + 1;
        $yearBefore  = $year;
        $yearAfter   = $year;

        if ($monthAfter > 12)
        {
            $monthAfter = 1;
            $yearAfter = $year + 1;
        }

        if ($monthBefore < 1)
        {
            $monthBefore = 12;
            $yearBefore = $year - 1;
        }

        $eventsStringNow = $calendar->makeEventString(
            $calendar->getEventArray($month, $year),
            $month,
            $year
        );

        $eventsStringBefore = $calendar->makeEventString(
            $calendar->getEventArray($monthBefore, $yearBefore),
            $monthBefore,
            $yearBefore
        );

        $eventsStringAfter = $calendar->makeEventString(
            $calendar->getEventArray($monthAfter, $yearAfter),
            $monthAfter,
            $yearAfter
        );

        $eventsString = implode(
            '@',
            array($eventsStringNow, $eventsStringBefore, $eventsStringAfter, $userIsSuperUser)
        );

        /* Textual representation of the month and year. */
        $dateString = date(
            'F Y',
            mktime($_SESSION['CATS']->getTimeZoneOffset(), 0, 0, $month, 1, $year)
        );

        /* The offset is the number of days after the first Sunday on a given
         * calendar page on which the 1st of the month falls. We subtract 1
         * because Sunday has a value of 1.
         */
        $startingOffset = $startingWeekday - 1;

        $userEmail = $_SESSION['CATS']->getEmail();

        $calendarEventTypes = $calendar->getAllEventTypes();

        $calendarSettings = new CalendarSettings($this->_siteID);
        $calendarSettingsRS = $calendarSettings->getAll();

        if ($view == 'DEFAULT_VIEW')
        {
            $view = $calendarSettingsRS['calendarView'];
        }

        $summaryHTML = $calendar->getUpcomingEventsHTML(12, UPCOMING_FOR_CALENDAR);

        if (!eval(Hooks::get('CALENDAR_SHOW'))) return;

        if (SystemUtility::isSchedulerEnabled() && !$_SESSION['CATS']->isDemo())
        {
            $allowEventReminders = true;
        }
        else
        {
            $allowEventReminders = false;
        }

        /* FIXME: Configurable */
        $this->_template->assign('dayHourStart', $calendarSettingsRS['dayStart']);
        $this->_template->assign('dayHourEnd', $calendarSettingsRS['dayStop']);
        $this->_template->assign('firstDayMonday', $calendarSettingsRS['firstDayMonday']);
        $this->_template->assign('allowAjax', ($calendarSettingsRS['noAjax'] == 0 ? true : false));
        $this->_template->assign('defaultPublic', ($calendarSettingsRS['defaultPublic'] == 0 ? 'false' : 'true'));
        $this->_template->assign('militaryTime', false);

        $this->_template->assign('active', $this);
        $this->_template->assign('currentDateMDY', $currentDateMDY);
        $this->_template->assign('startingWeekday', $startingWeekday);
        $this->_template->assign('daysInMonth', $daysInMonth);
        $this->_template->assign('currentHour', $currentHour);
        $this->_template->assign('currentDay', $currentDay);
        $this->_template->assign('currentMonth', $currentMonth);
        $this->_template->assign('currentYear', $currentYear);
        $this->_template->assign('startingOffset', $startingOffset);
        $this->_template->assign('userEmail', $userEmail);
        $this->_template->assign('userID', $this->_userID);
        $this->_template->assign('userEmail', $_SESSION['CATS']->getEmail());
        $this->_template->assign('summaryHTML', $summaryHTML);
        $this->_template->assign('userIsSuperUser', $userIsSuperUser);
        $this->_template->assign('superUserActive', $superUserActive);
        $this->_template->assign('calendarEventTypes', $calendarEventTypes);
        $this->_template->assign('view', $view);
        $this->_template->assign('day', $day);
        $this->_template->assign('week', $week);
        $this->_template->assign('month', $month);
        $this->_template->assign('year', $year);
        $this->_template->assign('showEvent', $showEvent);
        $this->_template->assign('dateString', $dateString);
        $this->_template->assign('isCurrentMonth', $isCurrentMonth);
        $this->_template->assign('eventsString', $eventsString);
        $this->_template->assign('allowEventReminders', $allowEventReminders);
        $this->_template->display('./modules/calendar/Calendar.tpl');
    }

    /*
     * Called by handleRequest() to handle generating a string of data for the calendar.
     */
    private function dynamicData()
    {
        /* Do we have a valid date argument? If a month was specified and
         * isn't valid, fatal() out. If none was specified, use the current
         * month.
         */
        if ($this->isRequiredIDValid('month', $_GET) &&
            $this->isRequiredIDValid('year', $_GET))
        {
            $month = $_GET['month'];
            $year  = $_GET['year'];

            if (!checkdate($month, 1, $year))
            {
                CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid date.');
            }
        }
        else
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid date.');
        }

        $calendar = new Calendar($this->_siteID);

        $eventsString = $calendar->makeEventString(
            $calendar->getEventArray($month, $year),
            $month,
            $year
        );

        if (!eval(Hooks::get('CALENDAR_DATA'))) return;

        echo $eventsString;
    }

    /*
     * Called by handleRequest() to process adding an event.
     */
    private function onAddEvent()
    {
        if ($this->getUserAccessLevel('calendar.addEvent') < ACCESS_LEVEL_EDIT)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
        }

        /* Bail out if we received an invalid date. */
        $trimmedDate = $this->getTrimmedInput('dateAdd', $_POST);
        if (empty($trimmedDate) ||
            !DateUtility::validate('-', $trimmedDate, DATE_FORMAT_MMDDYY))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid date.');
        }

        // FIXME: typeID
        /* Bail out if we don't have a valid event type. */
        if (!$this->isRequiredIDValid('type', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid event type ID.');
        }

        /* If we don't have a valid event duration, set dur = 30. */
        if (!$this->isOptionalIDValid('duration', $_POST))
        {
            $duration = 30;
        }
        else
        {
            $duration = $_POST['duration'];
        }

        /* Bail out if we don't have a valid time format ID. */
        if (!isset($_POST['allDay']) ||
            ($_POST['allDay'] != '0' && $_POST['allDay'] != '1'))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid time format ID.');
        }

        $type = $_POST['type'];

        if ($_POST['allDay'] == 1)
        {
            $allDay = true;
        }
        else
        {
            $allDay = false;
        }

        $publicEntry     = $this->isChecked('publicEntry', $_POST);
        $reminderEnabled = $this->isChecked('reminderToggle', $_POST);

        $description   = $this->getTrimmedInput('description', $_POST);
        $title         = $this->getTrimmedInput('title', $_POST);
        $reminderEmail = $this->getTrimmedInput('sendEmail', $_POST);
        $reminderTime  = $this->getTrimmedInput('reminderTime', $_POST);

        // FIXME: Reminder time must be an integer!

        /* Bail out if any of the required fields are empty. */
        if (empty($title))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
        }

        /* Is this a scheduled event or an all day event? */
        if ($allDay)
        {
            $date = DateUtility::convert(
                '-', $trimmedDate, DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD
            );

            $hour = 12;
            $minute = 0;
            $meridiem = 'AM';
        }
        else
        {
            /* Bail out if we don't have a valid hour. */
            if (!isset($_POST['hour']))
            {
                CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid hour.');
            }

            /* Bail out if we don't have a valid minute. */
            if (!isset($_POST['minute']))
            {
                CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid minute.');
            }

            /* Bail out if we don't have a valid meridiem value. */
            if (!isset($_POST['meridiem']) ||
                ($_POST['meridiem'] != 'AM' && $_POST['meridiem'] != 'PM'))
            {
                CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid meridiem value.');
            }

            $hour     = $_POST['hour'];
            $minute   = $_POST['minute'];
            $meridiem = $_POST['meridiem'];

            /* Convert formatted time to UNIX timestamp. */
            $time = strtotime(
                sprintf('%s:%s %s', $hour, $minute, $meridiem)
            );

            /* Create MySQL date string w/ 24hr time (YYYY-MM-DD HH:MM:SS). */
            $date = sprintf(
                '%s %s',
                DateUtility::convert(
                    '-', $trimmedDate, DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD
                ),
                date('H:i:00', $time)
            );
        }

        $timeZoneOffset = $_SESSION['CATS']->getTimeZoneOffset();

        if (!eval(Hooks::get('CALENDAR_ADD_PRE'))) return;

        $calendar = new Calendar($this->_siteID);
        $eventID = $calendar->addEvent(
            $type, $date, $description, $allDay, $this->_userID, -1, -1, -1,
            $title, $duration, $reminderEnabled, $reminderEmail, $reminderTime,
            $publicEntry, $timeZoneOffset
        );

        if ($eventID <= 0)
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to add calendar event.');
        }

        /* Extract the date parts from the specified date. */
        $parsedDate = strtotime($date);
        $day   = date('j', $parsedDate);
        $month = date('n', $parsedDate);
        $year  = date('Y', $parsedDate);

        /* Transfer to same url without a=addEvent. */
        $newGet = $_GET;
        $newParams = array();

        unset($newGet['a']);
        $newGet['showEvent'] = $eventID;

        foreach ($newGet AS $name => $value)
        {
            $newParams[] = urlencode($name) . '=' . urlencode($value);
        }

        if (!eval(Hooks::get('CALENDAR_ADD_POST'))) return;

        CATSUtility::transferRelativeURI(implode('&', $newParams));
    }


    /*
     * Called by handleRequest() to process editing an event.
     */
    private function onEditEvent()
    {
        if ($this->getUserAccessLevel('calendar.editEvent') < ACCESS_LEVEL_EDIT)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
        }

        /* Bail out if we don't have a valid event ID. */
        if (!$this->isRequiredIDValid('eventID', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid event ID.');
        }

        // FIXME: typeID
        /* Bail out if we don't have a valid event type. */
        if (!$this->isRequiredIDValid('type', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid event type ID.');
        }

        /* If we don't have a valid event duration, set duration to 30. */
        if (!$this->isOptionalIDValid('duration', $_POST))
        {
            $duration = 30;
        }
        else
        {
            $duration = $_POST['duration'];
        }

        /* If we have a valid data item type / ID, associate it. */
        if ($this->isRequiredIDValid('dataItemID', $_POST) &&
            $this->isRequiredIDValid('dataItemType', $_POST))
        {
            $dataItemID   = $_POST['dataItemID'];
            $dataItemType = $_POST['dataItemType'];
        }
        else
        {
            $dataItemID   = 'NULL';
            $dataItemType = 'NULL';
        }

        /* If we have a valid job order ID, associate it. */
        if ($this->isRequiredIDValid('jobOrderID', $_POST))
        {
            $jobOrderID   = $_POST['jobOrderID'];
        }
        else
        {
            $jobOrderID   = 'NULL';
        }

        /* Bail out if we received an invalid date. */
        $trimmedDate = $this->getTrimmedInput('dateEdit', $_POST);
        if (empty($trimmedDate) ||
            !DateUtility::validate('-', $trimmedDate, DATE_FORMAT_MMDDYY))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid date.');
        }

        /* Bail out if we don't have a valid time format ID. */
        if (!isset($_POST['allDay']) ||
            ($_POST['allDay'] != '0' && $_POST['allDay'] != '1'))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid time format ID.');
        }

        $eventID  = $_POST['eventID'];
        $type     = $_POST['type'];

        if ($_POST['allDay'] == 1)
        {
            $allDay = true;
        }
        else
        {
            $allDay = false;
        }

        $publicEntry     = $this->isChecked('publicEntry', $_POST);
        $reminderEnabled = $this->isChecked('reminderToggle', $_POST);

        $description   = $this->getTrimmedInput('description', $_POST);
        $title         = $this->getTrimmedInput('title', $_POST);
        $reminderEmail = $this->getTrimmedInput('sendEmail', $_POST);
        $reminderTime  = $this->getTrimmedInput('reminderTime', $_POST);

        // FIXME: Reminder time must be an integer!

        /* Bail out if any of the required fields are empty. */
        if (empty($title))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
        }

        /* Is this a scheduled event or an all day event? */
        if ($allDay)
        {
            $date = DateUtility::convert(
                '-', $trimmedDate, DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD
            );

            $hour = 12;
            $minute = 0;
            $meridiem = 'AM';
        }
        else
        {
            /* Bail out if we don't have a valid hour. */
            if (!isset($_POST['hour']))
            {
                CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid hour.');
            }

            /* Bail out if we don't have a valid minute. */
            if (!isset($_POST['minute']))
            {
                CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid minute.');
            }

            /* Bail out if we don't have a valid meridiem value. */
            if (!isset($_POST['meridiem']) ||
                ($_POST['meridiem'] != 'AM' && $_POST['meridiem'] != 'PM'))
            {
                CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid meridiem value.');
            }

            $hour     = $_POST['hour'];
            $minute   = $_POST['minute'];
            $meridiem = $_POST['meridiem'];

            /* Convert formatted time to UNIX timestamp. */
            $time = strtotime(
                sprintf('%s:%s %s', $hour, $minute, $meridiem)
            );

            /* Create MySQL date string w/ 24hr time (YYYY-MM-DD HH:MM:SS). */
            $date = sprintf(
                '%s %s',
                DateUtility::convert(
                    '-', $trimmedDate, DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD
                ),
                date('H:i:00', $time)
            );
        }

        if (!eval(Hooks::get('CALENDAR_EDIT_PRE'))) return;

        /* Update the event. */
        $calendar = new Calendar($this->_siteID);
        if (!$calendar->updateEvent($eventID, $type, $date, $description,
            $allDay, $dataItemID, $dataItemType, 'NULL', $title, $duration,
            $reminderEnabled, $reminderEmail, $reminderTime, $publicEntry,
            $_SESSION['CATS']->getTimeZoneOffset()))
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to update calendar event.');
        }

        if (!eval(Hooks::get('CALENDAR_EDIT_POST'))) return;

        /* Extract the date parts from the specified date. */
        $parsedDate = strtotime($date);
        $day   = date('j', $parsedDate);
        $month = date('n', $parsedDate);
        $year  = date('Y', $parsedDate);

        /* Transfer to same url without a=editEvent. */
        $newGet = $_GET;
        $newParams = array();

        unset($newGet['a']);
        $newGet['showEvent'] = $eventID;

        foreach ($newGet AS $name => $value)
        {
            $newParams[] = urlencode($name) . '=' . urlencode($value);
        }

        CATSUtility::transferRelativeURI(implode('&', $newParams));
    }

    /*
     * Called by handleRequest() to process deleting an event.
     */
    private function onDeleteEvent()
    {
        if ($this->getUserAccessLevel('calendar.deleteEvent') < ACCESS_LEVEL_DELETE)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
        }

        /* Bail out if we don't have a valid event ID. */
        if (!$this->isRequiredIDValid('eventID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid event ID.');
        }

        $eventID = $_GET['eventID'];

        if (!eval(Hooks::get('CALENDAR_DELETE_PRE'))) return;

        $calendar = new Calendar($this->_siteID);
        $calendar->deleteEvent($eventID);

        if (!eval(Hooks::get('CALENDAR_DELETE_POST'))) return;

        /* Transfer to same url without a=deleteEvent or eventID. */
        $newGet = $_GET;
        $newParams = array();

        unset($newGet['a']);
        unset($newGet['eventID']);

        foreach ($newGet AS $name => $value)
        {
            $newParams[] = urlencode($name).'='.urlencode($value);
        }

        CATSUtility::transferRelativeURI(implode('&', $newParams));
    }

    // TODO: Document me.
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

?>
