/*
 * CATS
 * Calendar UI JavaScript Library
 *
 * Portions Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
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
 * $Id: CalendarUI.js 3423 2007-11-06 18:33:44Z brian $
 */

var imageAlert = 'images/bell.gif';
var defaultDaytableHeight = 725;

var currentViewedEntry = null;


function generateCalendarEntrySmall(time, title, separator, entry)
{
    var visibleEntryID = visibleEntries.length;
    visibleEntries = visibleEntries.concat(entry);

    var iconSet   = '<span nowrap="nowrap"><nobr>';
    var typeImage = getImageByType(entry.getData('eventType'));
    var string = '';


    if (typeImage != '')
    {
        // FIXME: Show actual type of event instead of "Type of Event" in title.
        iconSet += '<img class="absmiddle" src="' + typeImage + '" title="Type of Event" /> ';
    }

    iconSet += entry.getData('displayDataItemSmall');

    if (entry.getData('reminderEnabled') == 1)
    {
        iconSet += '<img class="absmiddle" src="' + imageAlert + '" title="Reminder Set" /> ';
    }

    if (entry.getData('public') == 1)
    {
        iconSet += '<img class="absmiddle" src="images/public.gif" title="Public Entry" /> ';
    }

    iconSet += '</nobr></nowrap>';

    string += '<table><tr><td class="calendarEntry" onclick="handleClickEntry(visibleEntries['
        + visibleEntryID
        + ']);" onmouseover="noAddEvent = true;" onmouseout="noAddEvent = false;">';

    if (entry.getData('allDay') != '1')
    {
        string += '<span class="bold">'+ time + '</span> '
            + iconSet + separator + '&nbsp;';
    }
    else
    {
        string += iconSet + '&nbsp;';
    }

    string += title + " - " + entry.getData('enteredByFirstName') + " " + entry.getData('enteredByLastName');

    string +='</td></tr></table>';
    return string;
}

function generateCalendarEntryDayView(time, title, position, idDiv, idEntry, separator, entry, durationWidth)
{
    var visibleEntryID = visibleEntries.length;
    visibleEntries = visibleEntries.concat(entry);

    var iconSet = '<span nowrap="nowrap"><nobr>';
    var typeImage = getImageByType(entry.getData('eventType'));
    var string = '';

    iconSet += entry.getData('displayDataItemSmall');

    if (typeImage != '')
    {
        // FIXME: Show actual type of event instead of "Type of Event" in title.
        iconSet += '<img class="absmiddle" src="' + typeImage + '" title="Type of Event" /> ';
    }

    if (entry.getData('reminderEnabled') == 1)
    {
        iconSet += '<img class="absmiddle" src="' + imageAlert + '" title="Reminder Set" /> ';
    }

    if (entry.getData('public') == 1)
    {
        iconSet += '<img class="absmiddle" src="images/public.gif" title="Public Entry" /> ';
    }

    iconSet += '</nobr></nowrap>';

    if (entry.getData('allDay') != '1')
    {
        string += '<table><tr><td class="calendarEntry" id="'
            + idEntry + '" onclick="handleClickEntry(visibleEntries['
            + visibleEntryID
            + ']);" onmouseover="noAddEvent = true;" onmouseout="noAddEvent = false;">';
    }
    else
    {
        string += '<table><tr><td class="calendarEntry" id="'
            + idEntry + '" onclick="handleClickEntry(visibleEntries['
            + visibleEntryID
            + ']);" onmouseover="noAddEvent = true;" onmouseout="noAddEvent = false;">';
    }

    if (entry.getData('allDay') != '1')
    {
        var durationText = getDurationString(entry.getData('duration'));

        string += '<span class="bold">' + time + '</span> '
            + iconSet + '('
            + durationText + ') ' + separator + '&nbsp;';
    }
    else
    {
        string += iconSet + '&nbsp;';
    }

    string += title + " - " + entry.getData('enteredByFirstName') + " " + entry.getData('enteredByLastName');

    string +='</td></tr></table>';
    
    return string;
}

function generateCalendarEntryGrouped(text)
{
    return '<table><tr><td class="calendarEntryMultiple" style="font-weight: bold;">'
        + text + '</td></tr></table>';
}

function calendarUpcomingEvents()
{
    resetSideBar();
    document.getElementById('upcomingEventsTD').style.display = '';
}

function calendarEditEvent(entry)
{
    if (accessLevel < ACCESS_LEVEL_EDIT)
    {
        return;
    }

    resetSideBar();
    document.getElementById('editEventTD').style.display = '';
    document.getElementById('editEventForm').reset();

    /* Prepare date data. */
    var yearString = (entry.year % 100) + '';
    if (yearString.length == 1)
    {
        yearString = '0' + yearString;
    }

    var monthString = entry.month + '';
    if (monthString.length == 1)
    {
        monthString = '0' + monthString;
    }

    var dayString = entry.day + '';
    if (dayString.length == 1)
    {
        dayString = '0' + dayString;
    }

    var dateString = monthString + '-' + dayString + '-' + yearString;
    SetDateInputDate('dateEdit', 'MM-DD-YY', dateString);

    if (entry.getData('allDay') != '1')
    {
        setCheckedValue(document.getElementById('editEventForm').elements['allDay'], '0');
        setEditAllDayEnabled();

        if (entry.hour > 12)
        {
            document.getElementById('hourEdit').value = entry.hour - 12;
        }
        else
        {
            if (entry.hour == 0)
            {
                document.getElementById('hourEdit').value = 12;
            }
            else
            {
                document.getElementById('hourEdit').value = entry.hour * 1;
            }
        }
        if (entry.hour >= 12)
        {
            document.getElementById('meridiemEdit').value = 'PM';
        }

        var string = '' + entry.minute;

        if (string.length == 1)
        {
            string = '0' + string;
        }

        document.getElementById('minuteEdit').value = string;

    }
    else
    {
        setCheckedValue(document.getElementById('editEventForm').elements['allDay'], '1');
        setEditAllDayEnabled();
    }

    document.getElementById('typeEdit').value         = entry.getData('eventType');
    document.getElementById('dataItemTypeEdit').value = entry.getData('dataItemType');
    document.getElementById('dataItemIDEdit').value   = entry.getData('dataItemID');
    document.getElementById('eventIDEdit').value      = entry.getData('eventID');
    document.getElementById('titleEdit').value        = entry.getData('title');
    document.getElementById('descriptionEdit').value  = entry.getData('description');
    document.getElementById('durationEdit').value     = entry.getData('duration');
    document.getElementById('sendEmailEdit').value    = entry.getData('reminderEmail');
    document.getElementById('reminderTimeEdit').value = entry.getData('reminderTime');

    document.getElementById('reminderToggleEdit').checked = (entry.getData('reminderEnabled') == 1);
    document.getElementById('publicEntryEdit').checked    = (entry.getData('public') == 1);

    considerCheckBox('reminderToggleEdit', 'sendEmailTDEdit');
    //cleanUpUI();
}

function calendarViewEvent(entry)
{
    resetSideBar();

    document.getElementById('viewEventTD').style.display = '';

    document.getElementById('viewEventTitle').innerHTML = entry.getData('title');
    document.getElementById('viewEventLink').innerHTML = entry.getData('displayDataItemLarge');

    document.getElementById('viewEventType').innerHTML = '<img src="'
        + getImageByType(entry.getData('eventType')) + '" /> '
        + getShortDescriptionByType(entry.getData('eventType'));

    document.getElementById('viewEventDate').innerHTML = entry.getData('date');

    document.getElementById('viewEventOwner').innerHTML = entry.getData('enteredByFirstName')
        + ' ' + entry.getData('enteredByLastName');

    if (entry.getData('allDay') != '1')
    {
        document.getElementById('viewEventTime').innerHTML = entry.getData('time');

        var durationText = getDurationString(entry.getData('duration'));
        document.getElementById('viewEventDuration').innerHTML = durationText;
    }
    else
    {
        document.getElementById('viewEventTime').innerHTML = 'All Day / No Specific Time';
        document.getElementById('viewEventDuration').innerHTML = '<i>(All day event - None Set)</i>';
    }
    if (entry.getData('reminderEnabled') == 1)
    {
        reminderTimeText = getReminderTimeString(entry.getData('reminderTime'));
        document.getElementById('viewEventReminder').innerHTML = reminderTimeText;
    }
    else
    {
        document.getElementById('viewEventReminder').innerHTML = '<i>(None Set)</i>';
    }

    if (entry.getData('description') != '')
    {
        document.getElementById('viewEventDescription').innerHTML = entry.getData('description');
    }
    else
    {
        document.getElementById('viewEventDescription').innerHTML = '<i>(None Set)</i>';
    }

    currentViewedEntry = entry;
}

function handleClickEntry(entry)
{
    calendarViewEvent(entry);
}

function handleClickEntryByID(entryID)
{
    for (var i = 0; i < calendarData.length; i++)
    {
        YM = calendarData[i];

        for (var j = 0; j < YM.days.length; j++)
        {
            D = YM.days[j];

            for (var k = 0; k < D.entries.length; k++)
            {
                if (D.entries[k].getData('eventID') == entryID)
                {
                    calendarViewEvent(D.entries[k]);
                }
            }
        }
    }
}

function addEventByDay(year, month, day, hour)
{
    /* Hack to stop executing on edit event. */
    if (noAddEvent)
    {
        return;
    }

    /* User permiasions (GUI level). */
    if (accessLevel < ACCESS_LEVEL_EDIT)
    {
        return;
    }

    resetSideBar();
    document.getElementById('addEventTD').style.display = '';
    document.getElementById('addEventForm').reset();

    var _hour = hour;
    if (_hour == null)
    {
        hour = 0;
        setCheckedValue(document.getElementById('addEventForm').elements['allDay'], '1');
        setAddAllDayEnabled();
    }
    else
    {
        setCheckedValue(document.getElementById('addEventForm').elements['allDay'], '0');
        setAddAllDayEnabled();
    }

    /* Prepare date data. */
    var yearString = (year % 100) + '';
    if (yearString.length == 1)
    {
        yearString = '0' + yearString;
    }

    var monthString = month + '';
    if (monthString.length == 1)
    {
        monthString = '0' + monthString;
    }

    var dayString = day + '';
    if (dayString.length == 1)
    {
        dayString = '0' + dayString;
    }

    var dateString = monthString + '-' + dayString + '-' + yearString;
    SetDateInputDate('dateAdd', 'MM-DD-YY', dateString);

    document.getElementById('publicEntry').checked = defaultPublic;

    if (hour > 12)
    {
        document.getElementById('hour').value = hour - 12;
    }
    else
    {
        if (hour == 0)
        {
            document.getElementById('hour').value = 12;
        }
        else
        {
            document.getElementById('hour').value = hour;
        }
    }

    if (hour >= 12)
    {
        document.getElementById('meridiem').value = 'PM';
    }

    //cleanUpUI();

    document.getElementById('sendEmail').value = userEmail;
}

function userCalendarAddEvent()
{
    resetSideBar();
    document.getElementById('addEventTD').style.display = '';
    document.getElementById('addEventForm').reset();
    document.getElementById('sendEmail').value = userEmail;
    //cleanUpUI();
}

function confirmDeleteEntry()
{
    if (!confirm("Are you sure you want to delete this entry?"))
    {
        return;
    }

    document.location = getCurrentCalendarUrl() + '&a=deleteEvent&eventID='
        + document.getElementById('eventIDEdit').value;
}

/* Hides all the main window views (month, week, day, etc). */
function hideAllViews()
{
    document.getElementById('calendarMonthParent').style.display = 'none';
    document.getElementById('calendarWeekParent').style.display  = 'none';
    document.getElementById('calendarDayParent').style.display   = 'none';
}

function restoreHighlighting()
{
    for (var i = 0; i < savedStateCount; i++)
    {
        restoreBackgroundStyle(savedStates[i]);
    }

    for (var i = 0; i < savedStateCountSelected; i++)
    {
        restoreBackgroundStyleSelected(savedStatesSelected[i]);
    }

    selectedTableRow = 0;
    selectedTableCell = 0;
}

function resetSideBar()
{
    document.getElementById('upcomingEventsTD').style.display  = 'none';
    document.getElementById('addEventTD').style.display  = 'none';
    document.getElementById('editEventTD').style.display = 'none';
    document.getElementById('viewEventTD').style.display = 'none';
}

function setSideFormActions()
{
    document.getElementById('addEventForm').action  = getCurrentCalendarUrl() + '&a=addEvent';
    document.getElementById('editEventForm').action = getCurrentCalendarUrl() + '&a=editEvent';
}

/* When we moved things around and possibly broke the UI. */
/*function cleanUpUI()
{
}*/

function setAddAllDayEnabled()
{
    var state = false;

    if (getCheckedValue(document.getElementById('addEventForm').elements['allDay'], '1') == '1')
    {
        state = true;
    }

    document.getElementById('hour').disabled = state;
    document.getElementById('minute').disabled = state;
    document.getElementById('meridiem').disabled = state;
    document.getElementById('duration').disabled = state;
}

function setEditAllDayEnabled()
{
    var state = false;

    if (getCheckedValue(document.getElementById('editEventForm').elements['allDay'], '1') == '1')
    {
        state = true;
    }

    document.getElementById('hourEdit').disabled = state;
    document.getElementById('minuteEdit').disabled = state;
    document.getElementById('meridiemEdit').disabled = state;
    document.getElementById('durationEdit').disabled = state;
}

function getDurationString(duration)
{
    var string;

    if (duration < 1)
    {
        string = 'All Day';
    }
    else if (duration == 1)
    {
        string = '1 Minute';
    }
    else if (duration < 60)
    {
        string = duration + ' Minutes';
    }
    else if (duration == 60)
    {
        string = '1 Hour';
    }
    else if (duration <= 240)
    {
        string = ((duration * 1.0) / 60) + ' Hours';
    }
    else
    {
        string = 'More Than 4 Hours';
    }

    return string;
}

function getReminderTimeString(reminderTime)
{
    if (reminderTime < 1)
    {
        string = '(None Set)';
    }
    else if (reminderTime == 1)
    {
        string = '1 Minute Before';
    }
    else if (reminderTime < 60)
    {
        string = reminderTime + ' Minutes Before';
    }
    else if (reminderTime == 60)
    {
        string = '1 Hour Before';
    }
    else if (reminderTime < 1440)
    {
        string = ((reminderTime * 1.0) / 60) + ' Hours Before';
    }
    else if (reminderTime == 1440)
    {
        string = '1 Day Before';
    }
    else
    {
        string = ((reminderTime * 1.0) / 1440) + ' Days Before';
    }

	return string;
}
