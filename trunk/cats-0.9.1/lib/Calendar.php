<?php
/**
 * CATS
 * Calendar Library
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
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: Calendar.php 3595 2007-11-13 17:41:18Z andrew $
 */

/* Calendar event type flags. */
define('CALENDAR_EVENT_CALL',      100);
define('CALENDAR_EVENT_EMAIL',     200);
define('CALENDAR_EVENT_MEETING',   300);
define('CALENDAR_EVENT_INTERVIEW', 400);
define('CALENDAR_EVENT_PERSONAL',  500);
define('CALENDAR_EVENT_OTHER',     600);


include_once('./lib/ResultSetUtility.php');
include_once('./lib/DateUtility.php');
include_once('./lib/Companies.php');
include_once('./lib/Candidates.php');
include_once('./lib/JobOrders.php');
include_once('./lib/Contacts.php');
include_once('./lib/Mailer.php');


/**
 *	Calendar Library
 *	@package    CATS
 *	@subpackage Library
 */
class Calendar
{
    private $_db;
    private $_siteID;
    private $_userID;


    public function __construct($siteID)
    {
        // FIXME: Factor out Session dependency.
        $this->_userID = $_SESSION['CATS']->getUserID();

        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }


    /**
     * Returns an array of events in a month, keyed by each day of the month.
     * There will always be a key present for each day of the month, but if
     * there are no events for a day then it's corresponding value will be an
     * empty array.
     *
     * @param integer Month number (1-12).
     * @param integer Year (four-digit).
     * @return array Multi-dimensional result set array.
     */
    public function getEventArray($month, $year)
    {
        // FIXME: Rewrite this query to use date ranges in WHERE, so that
        //        indexes can be used.
        $sql = sprintf(
            "SELECT
                calendar_event.calendar_event_id AS eventID,
                calendar_event.data_item_id AS dataItemID,
                calendar_event.data_item_type AS dataItemType,
                calendar_event.joborder_id AS jobOrderID,
                calendar_event.duration AS duration,
                calendar_event.all_day AS allDay,
                calendar_event.title AS title,
                calendar_event.description AS description,
                calendar_event.reminder_enabled AS reminderEnabled,
                calendar_event.reminder_email AS reminderEmail,
                calendar_event.reminder_time AS reminderTime,
                calendar_event.public AS public,
                DATE_FORMAT(
                    calendar_event.date, '%%d'
                ) AS day,
                DATE_FORMAT(
                    calendar_event.date, '%%m'
                ) AS month,
                DATE_FORMAT(
                    calendar_event.date, '%%y'
                ) AS year,
                DATE_FORMAT(
                    calendar_event.date, '%%m-%%d-%%y'
                ) AS date,
                DATE_FORMAT(
                    calendar_event.date, '%%h:%%i %%p'
                ) AS time,
                DATE_FORMAT(
                    calendar_event.date, '%%H'
                ) AS hour,
                DATE_FORMAT(
                    calendar_event.date, '%%i'
                ) AS minute,
                calendar_event.date AS dateSort,
                DATE_FORMAT(
                    calendar_event.date_created, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateCreated,
                calendar_event_type.calendar_event_type_id AS eventType,
                calendar_event_type.short_description AS eventTypeDescription,
                entered_by_user.user_id AS userID,
                entered_by_user.first_name AS enteredByFirstName,
                entered_by_user.last_name AS enteredByLastName
            FROM
                calendar_event
            LEFT JOIN calendar_event_type
                ON calendar_event.type = calendar_event_type.calendar_event_type_id
            LEFT JOIN user AS entered_by_user
                ON calendar_event.entered_by = entered_by_user.user_id
            WHERE
                DATE_FORMAT(calendar_event.date, '%%c') = %s
            AND
                DATE_FORMAT(calendar_event.date, '%%Y') = %s
            AND
                calendar_event.site_id = %s
            ORDER BY
                dateSort ASC",
            $month,
            $year,
            $this->_siteID
        );

        $rs = $this->_db->getAllAssoc($sql);

        /* Build an array of result set arrays for each day of the month.
         * Days without any events scheduled will have an empty array.
         */
        $daysInMonth = DateUtility::getDaysInMonth($month, $year);
        for ($i = 1; $i <= $daysInMonth; ++$i)
        {
            /* See if we can find a row in the result set that has 'day' set
             * to $i.
             */
            $firstOffset = ResultSetUtility::findRowByColumnValue(
                $rs, 'day', $i
            );

            /* Found? If yes, $firstOffset now contains the offset of the row;
             * otherwise false.
             */
            if ($firstOffset === false)
            {
                /* No events for this date. */
                $array[$i] = array();
                continue;
            }

            /* Store the first row we found that has 'day' set to $i. */
            $array[$i] = array($rs[$firstOffset]);

            /* There could be more than one row that has 'day' set to $i
             * (multiple events on the same day). We are going to tell
             * findRowByColumnValue() to skip the first row (we found it
             * and stored it already), and then keep increasing the number
             * of rows to skip until we can't find any more rows.
             */
            for ($skip = 1; ; ++$skip)
            {
                $nextOffset = ResultSetUtility::findRowByColumnValue(
                    $rs, 'day', $i, $skip
                );
                if ($nextOffset === false)
                {
                    /* No more rows for this date. */
                    break;
                }

                /* Found another one; store the row. */
                $array[$i][] = $rs[$nextOffset];
            }
        }

        return $array;
    }

    /**
     * Returns all events which are due for every site.
     *
     * @return array Multi-dimensional result set array of events data, or
     *               array() if no records are present.
     */
    public function getAllDueReminders()
    {
        /* The date-math below is done in a backwards kindof way intentionally.
         * Leaving calendar_event.date outside of a function call should allow
         * an index to be used on it.
         */
        $sql = sprintf(
            "SELECT
                calendar_event.calendar_event_id AS eventID,
                calendar_event.data_item_id AS dataItemID,
                calendar_event.data_item_type AS dataItemType,
                calendar_event.joborder_id AS jobOrderID,
                calendar_event.duration AS duration,
                calendar_event.all_day AS allDay,
                calendar_event.title AS title,
                calendar_event.description AS description,
                calendar_event.reminder_enabled AS reminderEnabled,
                calendar_event.reminder_email AS reminderEmail,
                calendar_event.reminder_time AS reminderTime,
                calendar_event.public AS public,
                calendar_event.date AS dateSort,
                calendar_event_type.calendar_event_type_id AS eventType,
                calendar_event_type.short_description AS eventTypeDescription,
                entered_by_user.user_id AS userID,
                entered_by_user.first_name AS enteredByFirstName,
                entered_by_user.last_name AS enteredByLastName,
                entered_by_user.site_id AS siteID
            FROM
                calendar_event
            LEFT JOIN calendar_event_type
                ON calendar_event.type = calendar_event_type.calendar_event_type_id
            LEFT JOIN user AS entered_by_user
                ON calendar_event.entered_by = entered_by_user.user_id
            WHERE
                calendar_event.reminder_enabled = 1
            AND
                DATE_ADD(NOW(), INTERVAL calendar_event.reminder_time MINUTE) >= calendar_event.date"
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all calendar event types defined in the database.
     *
     * @return array Multi-dimensional result set array of event types data, or
     *               array() if no records are present.
     */
    public function getAllEventTypes()
    {
        $sql = sprintf(
            "SELECT
                calendar_event_type.calendar_event_type_id AS typeID,
                calendar_event_type.short_description AS description,
                calendar_event_type.icon_image AS iconImage
            FROM
                calendar_event_type
            ORDER BY
                typeID ASC"
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Adds a calendar event to the database.
     *
     * @param flag Calendar event type flag.
     * @param string SQL-format date / time string for the event.
     * @param string Long event description
     * @param boolean Is this an all-day event?
     * @param integer Entered-by user ID.
     * @param integer Data Item ID with which to associate this event, or -1
     *                if none.
     * @param flag Data Item type flag corresponding with $dataItemID, or -1
     *             if none.
     * @param integer Job Order ID with which to associate this event, or -1
     *                if none.
     * @param string Short event title.
     * @param integer Event duration in minutes.
     * @param boolean Enable reminders?
     * @param string E-mail address to send reminders.
     * @param integer Minutes before event occurrs to send reminders.
     * @param boolean Is this a public event entry?
     * @param integer Time zone offset from GMT.
     * @return integer New Calendar Event ID, or -1 on failure.
     */
    // FIXME: Time Zone Offset probably shouldn't be paramaterized.
    public function addEvent($type, $date, $description, $allDay, $enteredBy,
        $dataItemID, $dataItemType, $jobOrderID, $title, $duration,
        $reminderEnabled, $reminderEmail, $reminderTime, $isPublic,
        $timeZoneOffset)
    {
        $sql = sprintf(
            "INSERT INTO calendar_event (
                type,
                date,
                description,
                all_day,
                entered_by,
                data_item_id,
                data_item_type,
                joborder_id,
                site_id,
                date_created,
                date_modified,
                title,
                duration,
                reminder_enabled,
                reminder_email,
                reminder_time,
                public
            )
            VALUES (
                %s,
                DATE_SUB(%s, INTERVAL %s HOUR),
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                NOW(),
                NOW(),
                %s,
                %s,
                %s,
                %s,
                %s,
                %s
            )",
            $this->_db->makeQueryInteger($type),
            $this->_db->makeQueryString($date),
            $this->_db->makeQueryInteger($timeZoneOffset),
            $this->_db->makeQueryString($description),
            ($allDay ? '1' : '0'),
            $this->_db->makeQueryInteger($enteredBy),
            $this->_db->makeQueryInteger($dataItemID),
            $this->_db->makeQueryInteger($dataItemType),
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID,
            $this->_db->makeQueryString($title),
            $this->_db->makeQueryInteger($duration),
            ($reminderEnabled ? '1' : '0'),
            $this->_db->makeQueryString($reminderEmail),
            $this->_db->makeQueryInteger($reminderTime),
            ($isPublic ? '1' : '0')
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        return $this->_db->getLastInsertID();
    }

    /**
     * Adds a calendar event to the database.
     *
     * @param integer Calendar Event ID.
     * @param flag Calendar event type flag.
     * @param string SQL-format date / time string for the event.
     * @param string Long event description
     * @param boolean Is this an all-day event?
     * @param integer Data Item ID with which to associate this event, or -1
     *                if none.
     * @param flag Data Item type flag corresponding with $dataItemID, or -1
     *             if none.
     * @param integer Job Order ID with which to associate this event, or -1
     *                if none.
     * @param string Short event title.
     * @param integer Event duration in minutes.
     * @param boolean Enable reminders?
     * @param string E-mail address to send reminders.
     * @param integer Minutes before event occurrs to send reminders.
     * @param boolean Is this a public event entry?
     * @param integer Time zone offset from GMT.
     * @return boolean True if successful; false otherwise.
     */
    // FIXME: Time Zone Offset probably shouldn't be paramaterized.
    public function updateEvent($eventID, $type, $date, $description, $allDay,
        $dataItemID, $dataItemType, $jobOrderID, $title, $duration,
        $reminderEnabled, $reminderEmail, $reminderTime, $isPublic,
        $timeZoneOffset)
    {
        $sql = sprintf(
            "UPDATE
                calendar_event
            SET
                type             = %s,
                date             = DATE_SUB(%s, INTERVAL %s HOUR),
                description      = %s,
                all_day          = %s,
                data_item_id     = %s,
                data_item_type   = %s,
                joborder_id      = %s,
                date_modified    = NOW(),
                title            = %s,
                duration         = %s,
                reminder_enabled = %s,
                reminder_email   = %s,
                reminder_time    = %s,
                public           = %s
            WHERE
                calendar_event_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($type),
            $this->_db->makeQueryString($date),
            $this->_db->makeQueryInteger($timeZoneOffset),
            $this->_db->makeQueryString($description),
            ($allDay ? '1' : '0'),
            $this->_db->makeQueryInteger($dataItemID),
            $this->_db->makeQueryInteger($dataItemType),
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_db->makeQueryString($title),
            $this->_db->makeQueryInteger($duration),
            ($reminderEnabled ? '1' : '0'),
            $this->_db->makeQueryString($reminderEmail),
            $this->_db->makeQueryInteger($reminderTime),
            ($isPublic ? '1' : '0'),
            $this->_db->makeQueryInteger($eventID),
            $this->_siteID
        );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Sets the reminder_enabled flag for an event to 0 in the database.
     * This is generally done after a reminder is sent for an event.
     *
     * FIXME: Add a new flag to mark reminders as triggered, so that events
     * that HAD reminders still have history preserved.
     *
     * @param integer Event ID on which to operate.
     * @return boolean Did the query execute successfully?
     */
    public function updateEventDisableReminder($eventID)
    {
        $sql = sprintf(
            "UPDATE
                calendar_event
            SET
                reminder_enabled = 0
            WHERE
                calendar_event_id = %s",
            $this->_db->makeQueryInteger($eventID)
        );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Removes a calendar event from the system.
     *
     * @param integer Event ID to delete.
     * @return boolean Did the query execute successfully?
     */
    public function deleteEvent($eventID)
    {
        $sql = sprintf(
            "DELETE FROM
                calendar_event
            WHERE
                calendar_event_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($eventID),
            $this->_siteID
        );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Returns a string for parsing by calendar.js from an event array.
     *
     * FIXME: Refactor so that this method grabs the event data too?
     * Look into.
     *
     * FIXME: Document this format!
     *
     * @param array Event array (for example, from Calendar::getEventArray().
     * @param integer Month number (1-12).
     * @param integer Year (four-digit).
     * @param boolean Show events from EVERYONE?
     * @return string Event data string for calendar.js to parse.
     */
    public function makeEventString($eventArray, $month, $year,
        $showAllUsersEvents = true)
    {
        $stringArray = array();

        foreach ($eventArray as $day => $dayData)
        {
            foreach ($dayData as $event)
            {
                $eventParameters = array();
                $eventParameters[] = sprintf(
                    'datetime|%s,%s,%s,%s,%s',
                    $event['year'] + 2000,
                    $event['month'],
                    $event['day'],
                    $event['hour'],
                    $event['minute']
                );

                unset($event['year']);
                unset($event['month']);
                unset($event['day']);
                unset($event['hour']);
                unset($event['minute']);

                if ($event['dataItemType'] > 0)
                {
                    $event['displayDataItemSmall'] = $this->getHTMLOfLink(
                        $event['dataItemID'], $event['dataItemType'], false
                    );
                    $event['displayDataItemLarge'] = $this->getHTMLOfLink(
                        $event['dataItemID'], $event['dataItemType'], true
                    );
                }

                foreach ($event AS $field => $value)
                {
                    if (empty($value))
                    {
                        continue;
                    }

                    $eventParameters[] = $field . '|' . str_replace(
                        '+', ' ', urlencode($value)
                    );
                }

                /* Filter out events user should not see here. */
                if ($showAllUsersEvents || $event['public'] == '1' ||
                    $event['userID'] == $this->_userID)
                {
                    $stringArray[] = implode('*', $eventParameters);
                }
            }
        }

        if (empty($stringArray))
        {
            return 'noentries|' . $year . ',' . $month;
        }

        return implode('@', $stringArray);
    }

    /**
     * Returns all upcoming calendar events for a data item.
     *
     * @param flag Data Item type flag.
     * @param integer Data Item ID.
     * @return array Multi-dimensional result set array of events data, or
     *               array() if no records are present.
     */
    public function getUpcomingEventsByDataItem($dataItemType, $dataItemID)
    {
        $currentDateForMySQL = strftime('%Y-%m-%d 00:00:00', time());

        $sql = sprintf(
            "SELECT
                calendar_event.calendar_event_id AS eventID,
                calendar_event.title AS title,
                calendar_event.all_day AS allDay,
                calendar_event.description AS description,
                calendar_event.public AS public,
                DATE_FORMAT(
                    calendar_event.date, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateShow,
                DATE_FORMAT(
                    calendar_event.date, '%%d'
                ) AS day,
                DATE_FORMAT(
                    calendar_event.date, '%%m'
                ) AS month,
                DATE_FORMAT(
                    calendar_event.date, '%%y'
                ) AS year,
                calendar_event.date AS dateSort,
                entered_by_user.user_id AS userID,
                entered_by_user.first_name AS enteredByFirstName,
                entered_by_user.last_name AS enteredByLastName,
                calendar_event_type.short_description AS type,
                calendar_event_type.icon_image AS typeImage
            FROM
                calendar_event
            LEFT JOIN user AS entered_by_user
                ON calendar_event.entered_by = entered_by_user.user_id
            LEFT JOIN calendar_event_type
                ON calendar_event.type = calendar_event_type.calendar_event_type_id
            WHERE
                calendar_event.site_id = %s
            AND
                calendar_event.date >= %s
            AND
            (
                calendar_event.public = 1
                OR calendar_event.entered_by = %s
            )
            AND
                calendar_event.data_item_type = %s
            AND
                calendar_event.data_item_id = %s
            ORDER BY
                dateSort ASC",
            $this->_siteID,
            $this->_db->makeQueryString($currentDateForMySQL),
            $this->_userID,
            $this->_db->makeQueryInteger($dataItemType),
            $this->_db->makeQueryInteger($dataItemID)
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns HTML for the 'my upcoming events' component of the dashboard /
     * calendar.
     *
     * @param integer Maximum number of events to return.
     * @param flag Type / format of upcoming events to return.
     * @return string Upcoming events HTML.
     */
    public function getUpcomingEventsHTML($limit, $flag = UPCOMING_FOR_CALENDAR)
    {
        switch ($flag)
        {
            case UPCOMING_FOR_CALENDAR:
                $HTML = '<div class="noteUnsizedSpan">My Upcoming Events / Calls</div>';
                $style = '';
                $criteria = '';
                break;

            case UPCOMING_FOR_DASHBOARD:
                $HTML = '<div class="noteUnsizedSpan" style="width:100%;">My Upcoming Events</div>';
                $style = 'font-size:11px;';
                $criteria = 'AND NOT TYPE = 100';
                break;

            case UPCOMING_FOR_DASHBOARD_FUP:
                $HTML = '<div class="noteUnsizedSpan">My Upcoming Calls</div>';
                $style = 'font-size:11px;';
                $criteria = 'AND TYPE = 100';
                break;
        }

        /* Get today's events. */
        $sql = sprintf(
            "SELECT
                calendar_event.calendar_event_id AS eventID,
                calendar_event.title AS title,
                calendar_event.description AS description,
                calendar_event.public AS public,
                calendar_event.all_day AS allDay,
                DATE_FORMAT(
                    calendar_event.date, '%%d'
                ) AS day,
                DATE_FORMAT(
                    calendar_event.date, '%%m'
                ) AS month,
                DATE_FORMAT(
                    calendar_event.date, '%%y'
                ) AS year,
                DATE_FORMAT(
                    calendar_event.date, '%%m-%%d-%%y'
                ) AS date,
                DATE_FORMAT(
                    calendar_event.date, '%%h:%%i %%p'
                ) AS time,
                calendar_event.date AS dateSort,
                entered_by_user.user_id AS userID,
                entered_by_user.first_name AS enteredByFirstName,
                entered_by_user.last_name AS enteredByLastName
            FROM
                calendar_event
            LEFT JOIN user AS entered_by_user
                ON calendar_event.entered_by = entered_by_user.user_id
            WHERE
                TO_DAYS(NOW()) = TO_DAYS(calendar_event.date)
            AND
                calendar_event.site_id = %s
            AND
            (
                %s
                OR calendar_event.entered_by = %s
            )
            %s
            ORDER BY
                dateSort ASC",
            $this->_siteID,
            ($flag == UPCOMING_FOR_CALENDAR ? 'calendar_event.public = 1' : 'false'),
            $this->_userID,
            $criteria
        );
        $todayRS = $this->_db->getAllAssoc($sql);

        /* Get events after today. */
        $sql = sprintf(
            "SELECT
                calendar_event.calendar_event_id AS eventID,
                calendar_event.title AS title,
                calendar_event.description AS description,
                calendar_event.public AS public,
                calendar_event.all_day AS allDay,
                DATE_FORMAT(
                    calendar_event.date, '%%d'
                ) AS day,
                DATE_FORMAT(
                    calendar_event.date, '%%m'
                ) AS month,
                DATE_FORMAT(
                    calendar_event.date, '%%y'
                ) AS year,
                DATE_FORMAT(
                    calendar_event.date, '%%m-%%d-%%y'
                ) AS date,
                DATE_FORMAT(
                    calendar_event.date, '%%h:%%i %%p'
                ) AS time,
                calendar_event.date AS dateSort,
                entered_by_user.user_id AS userID,
                entered_by_user.first_name AS enteredByFirstName,
                entered_by_user.last_name AS enteredByLastName
            FROM
                calendar_event
            LEFT JOIN user AS entered_by_user
                ON calendar_event.entered_by = entered_by_user.user_id
            WHERE
                DATE(calendar_event.date) > CURDATE()
            AND
                TO_DAYS(NOW()) != TO_DAYS(calendar_event.date)
            AND
                calendar_event.site_id = %s
            AND
            (
                calendar_event.public = 1
                OR calendar_event.entered_by = %s
            )
            %s
            ORDER BY
                dateSort ASC
            LIMIT
                0, %s",
            $this->_siteID,
            $this->_userID,
            $criteria,
            $limit
        );
        $futureRS = $this->_db->getAllAssoc($sql);

        $indexName = CATSUtility::getIndexName();

        foreach ($todayRS as $rowIndex => $row)
        {
            if ($row['allDay'] == '1')
            {
                $time = 'All Day';
            }
            else
            {
                $time = $row['time'];
            }

            $formatString = '<span title="%s" style="%s">%s %s: <a href="%s?m=calendar'
                . '&amp;view=DAYVIEW&amp;month=%s&amp;year=20%s&amp;day=%s'
                . '&amp;showEvent=%s" style="%s">%s</a></span><br />';

            $HTML .= sprintf(
                $formatString,
                htmlspecialchars($row['description']),
                $style,
                $row['date'],
                $time,
                $indexName,
                $row['month'],
                $row['year'],
                $row['day'],
                $row['eventID'],
                $style,
                htmlspecialchars($row['title'])
            );
        }

        foreach ($futureRS as $rowIndex => $row)
        {
            if ($row['allDay'] == '1')
            {
                $time = 'All Day';
            }
            else
            {
                $time = $row['time'];
            }

            $formatString = ''
                . '<span title="%s" style="%s">%s %s: <a href="%s?m=calendar'
                . '&amp;view=DAYVIEW&amp;month=%s&amp;year=20%s&amp;day=%s'
                . '&amp;showEvent=%s" style="%s">%s</a></span><br />';

            $HTML .= sprintf(
                $formatString,
                htmlspecialchars($row['description']),
                $style,
                $row['date'],
                $time,
                $indexName,
                $row['month'],
                $row['year'],
                $row['day'],
                $row['eventID'],
                $style,
                htmlspecialchars($row['title'])
            );
        }

        return $HTML;
    }

    /**
     * Returns link HTML for a data item.
     *
     * @param flag Data Item type flag.
     * @param integer Data Item ID.
     * @param boolean Show name / data item title?
     * @return string Link HTML (<a href="...">...</a>).
     */
    private function getHTMLOfLink($dataItemID, $dataItemType, $showTitle = true)
    {
        $string = '<a href="' . CATSUtility::getIndexName();

        switch ($dataItemType)
        {
            case DATA_ITEM_CANDIDATE:
                $candidates = new Candidates($this->_siteID);
                $string .= '?m=candidates&amp;a=show&amp;candidateID=' . $dataItemID . '">';
                $string .= '<img src="images/mru/candidate.gif" alt="" style="border: none;" title="Candidate" />';
                if ($showTitle)
                {
                    $data = $candidates->get($dataItemID);
                    if (!isset($data['firstName']))
                    {
                        $string = '<img src="images/mru/company.gif" alt="" style="border: none;" /> (Candidate Deleted)<a>';
                    }
                    else
                    {
                        $string .= '&nbsp;' . $data['firstName'] . ' ' . $data['lastName'];
                    }
                }
                $image = 'images/mru/candidate.gif';
                break;

            case DATA_ITEM_COMPANY:
                $companies = new Companies($this->_siteID);
                $string .= '?m=companies&amp;a=show&amp;companyID=' . $dataItemID . '">';
                $string .= '<img src="images/mru/company.gif" alt="" style="border: none;" title="Company" />';
                if ($showTitle)
                {
                    $data = $companies->get($dataItemID);
                    if (!isset($data['name']))
                    {
                        $string = '<img src="images/mru/company.gif" alt="" style="border: none;" /> (Company Deleted)<a>';
                    }
                    else
                    {
                        $string .= '&nbsp;' . $data['name'];
                    }
                }
                break;

            case DATA_ITEM_CONTACT:
                $contacts = new Contacts($this->_siteID);
                $string .= '?m=contacts&amp;a=show&amp;contactID=' . $dataItemID . '">';
                $string .= '<img src="images/mru/contact.gif" alt="" style="border: none;" title="Contact" />';
                if ($showTitle)
                {
                    $data = $contacts->get($dataItemID);
                    if (!isset($data['firstName']))
                    {
                        $string = '<img src="images/mru/contact.gif" alt="" style="border: none;" /> (Contact Deleted)<a>';
                    }
                    else
                    {
                        $string .= '&nbsp;' . $data['firstName'] . ' ' . $data['lastName'];
                    }
                }
                break;

            case DATA_ITEM_JOBORDER:
                $jobOrders = new JobOrders($this->_siteID);
                $string .= '?m=joborders&amp;a=show&amp;jobOrderID=' . $dataItemID . '">';
                $string .= '<img src="images/mru/job_order.gif" alt="" style="border: none;" title="Job Order" />';
                if ($showTitle)
                {
                    $data = $jobOrders->get($dataItemID);
                    if (!isset($data['title']))
                    {
                        $string = '<img src="images/mru/job_order.gif" alt="" style="border: none;" /> (Job Order Deleted)<a>';
                    }
                    else
                    {
                        $string .= '&nbsp;' . $data['title'];
                    }
                }
                break;
        }

        $string .= '</a>';

        return $string;
    }

    /**
     * Sends an e-mail using Mailer.
     *
     * FIXME: Having to specify a site ID here doesn't fit with our design, but
     * it's required for reminder processing.
     *
     * @param integer Site ID as which to send the e-mail (for history logging
     *                purposes, etc.).
     * @param integer User ID as which to send the e-mail (for history logging
     *                purposes, etc.).
     * @param string Destination e-mail address(es), separated by ',' or ';'.
     * @param string E-mail subject.
     * @param string E-mail body.
     * @return void
     */
    public function sendEmail($siteID, $userID, $destination, $subject, $body)
    {
        if (empty($destination))
        {
            return;
        }

        /* Send e-mail notification. */
        $mailer = new Mailer($siteID, $userID);

        $destination = str_replace(',', ';', $destination);
        $destinations = split(';', $destination);

        foreach ($destinations as $address)
        {
            $mailerStatus = $mailer->sendToOne(
                array($address, ''),
                $subject,
                $body,
                true
            );
        }
    }
}

/**
 *	Calendar Settings Library
 *	@package    CATS
 *	@subpackage Library
 */
class CalendarSettings
{
    private $_db;
    private $_siteID;
    private $_userID;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        // FIXME: Factor out Session dependency.
        $this->_userID = $_SESSION['CATS']->getUserID();
        $this->_db = DatabaseConnection::getInstance();
    }


    /**
     * Returns all calendar settings for a site.
     *
     * @return array (setting => value)
     */
    public function getAll()
    {
        /* Default values. */
        $settings = array(
            'noAjax' => '0',
            'defaultPublic' => '0',
            'dayStart' => '8',
            'dayStop' => '18',
            'firstDayMonday' => '1',
            'calendarView' => 'MONTHVIEW'
        );

        $sql = sprintf(
            "SELECT
                settings.setting AS setting,
                settings.value AS value,
                settings.site_id AS siteID
            FROM
                settings
            WHERE
                settings.site_id = %s
            AND
                settings.settings_type = %s",
            $this->_siteID,
            SETTINGS_CALENDAR
        );
        $rs = $this->_db->getAllAssoc($sql);

        /* Override default settings with settings from the database. */
        foreach ($rs as $rowIndex => $row)
        {
            foreach ($settings as $setting => $value)
            {
                if ($row['setting'] == $setting)
                {
                    $settings[$setting] = $row['value'];
                }
            }
        }

        return $settings;
    }

    /**
     * Sets a calendar setting for a site.
     *
     * @param string setting name
     * @param string setting value
     * @return void
     */
    public function set($setting, $value)
    {
        $sql = sprintf(
            "DELETE FROM
                settings
            WHERE
                settings.setting = %s
            AND
                site_id = %s
            AND
                settings_type = %s",
            $this->_db->makeQueryStringOrNULL($setting),
            $this->_siteID,
            SETTINGS_CALENDAR
        );
        $this->_db->query($sql);

        $sql = sprintf(
            "INSERT INTO settings (
                setting,
                value,
                site_id,
                settings_type
            )
            VALUES (
                %s,
                %s,
                %s,
                %s
            )",
            $this->_db->makeQueryStringOrNULL($setting),
            $this->_db->makeQueryStringOrNULL($value),
            $this->_siteID,
            SETTINGS_CALENDAR
         );
         $this->_db->query($sql);
    }
}

?>
