/*
 * CATS
 * Calendar JavaScript Library
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
 * $Id: Calendar.js 3615 2007-11-13 18:45:31Z brian $
 */

var MONTH_VIEW = 0;
var WEEK_VIEW = 1;
var DAY_VIEW = 2;

var currentYear = -1;
var currentMonth = -1;
var currentWeek = -1;
var currentDay = -1;

var monthUpToDate = false;
var weekUpToDate = false;
var dayUpToDate = false;

var todayYear = 0;
var todayMonth = 0;
var todayDay = 0;

var totalRecordsInMemory = 0;

var allowAjax = true;

var indexName = '';
var userEmail = '';

var currentViewMode = -1;
var noAddEvent = false;

var userID;
var userIsSuperUser;

var accessLevel = -1;

/* HELPER FUNCTIONS */

var monthName = new Array (
    "January ",
    "February ",
    "March ",
    "April ",
    "May ",
    "June ",
    "July ",
    "August ",
    "September ",
    "October ",
    "November ",
    "December "
);

var monthNameAbreiv = new Array (
    "Jan ",
    "Feb ",
    "Mar ",
    "Apr ",
    "May ",
    "Jun ",
    "Jul ",
    "Aug ",
    "Sept ",
    "Oct ",
    "Nov ",
    "Dec "
);

function monthNameWrap(month)
{
    monthID = month - 1;

    if (monthID < 0)
    {
        monthID += 12;
    }

    if (monthID > 11)
    {
        monthID -= 12;
    }

    return (monthName[monthID]);
}

function monthBackwardsByMonth(month)
{
    monthID = month - 1;

    if (monthID < 1)
    {
        monthID += 12;
    }

    return monthID;
}

function monthForewardsByMonth(month)
{
    monthID = month + 1;

    if (monthID > 12)
    {
        monthID -= 12;
    }

    return monthID;
}

function yearBackwardsByMonth(year, month)
{
    if (month == 1)
    {
        return year - 1;
    }

    return year;
}

function yearForewardsByMonth(year, month)
{
    if (month == 12)
    {
        return year + 1;
    }

    return year;
}

function weekBackwardsByWeek(year, month, week)
{
    if (week == 1)
    {
        var _daysInMonth = daysInMonth(yearBackwardsByMonth(month), monthBackwardsByMonth(month))
        var _firstDayOfMonth = firstDayOfMonth(yearBackwardsByMonth(month), monthBackwardsByMonth(month))

        if (_daysInMonth + _firstDayOfMonth < 36)
        {
           return 4;
        }

        return 5;
    }

    return week - 1;
}

function weekForewardsByWeek(year, month, week)
{
    var _daysInMonth = daysInMonth(year, month)
    var _firstDayOfMonth = firstDayOfMonth(year, month)

    // 7 week days * 5 rows on the calendar = 35 squares in a calendar spanning 5 rows.
    // If _daysInMonth + _firstDayOfMonth >= 36 then the calnedar takes up 6 rows.
    if (_daysInMonth + _firstDayOfMonth < 36)
    {
        if (week >= 4)
        {
            // the week after week 4 is next month week 1.
            return week - 3;
        }
        else
        {
            return (week * 1) + 1;
        }
    }

    if (week >= 5)
    {
        return week - 4;
    }
    else
    {
        return (week * 1) + 1;
    }
}

function monthBackwardsByWeek(year, month, week)
{
    if (weekBackwardsByWeek(year, month, week) != week - 1 &&
        weekBackwardsByWeek(year, month, week) != week - 2)
    {
        return monthBackwardsByMonth(month);
    }

    return month;
}

function monthForewardsByWeek(year, month, week)
{

    if (weekForewardsByWeek(year, month, week) != week + 1 &&
        weekForewardsByWeek(year, month, week) != week + 2)
    {
        return monthForewardsByMonth(month);
    }

    return month;
}

function yearBackwardsByWeek(year, month, week)
{
    if (weekBackwardsByWeek(year, month, week) != week - 1 &&
        weekBackwardsByWeek(year, month, week) != week - 2)
    {
        return yearBackwardsByMonth(year, month);
    }

    return year;
}

function yearForewardsByWeek(year, month, week)
{
    if (weekForewardsByWeek(year, month, week) != week + 1 &&
        weekForewardsByWeek(year, month, week) != week + 2)
    {
        return yearForewardsByMonth(year, month);
    }

    return year;
}

function getYearByDay(year, month, day)
{
    var _day = day;
    var _month = month;
    var _year = year;

    if (_day < 1)
    {
        if (_month == 1)
        {
            _year--;
        }
    }
    else if (_day > daysInMonth(_year, _month))
    {
        if (_month == 12)
        {
            _year++;
        }
    }

    return _year;
}

function getMonthByDay(year, month, day)
{
    var _day = day;
    var _month = month;
    var _year = year;

    if (_day < 1)
    {
        _month--;
        if (_month < 1)
        {
            _month = 12;
        }
    }
    else if (_day > daysInMonth(_year, _month))
    {
        _month++;
        if (_month > 12)
        {
            _month = 1;
        }
    }

    return _month;
}

function getDayByDay(year, month, day)
{
    var _day = day;
    var _month = month;
    var _year = year;

    if (_day < 1)
    {
        _month--;
        if (_month < 1)
        {
            _month = 12;
            _year--;
        }

        _day += daysInMonth(_year, _month);
    }
    else if (_day > daysInMonth(_year, _month))
    {
        _day -= daysInMonth(_year, _month);
    }

    return _day;
}

function daysInMonth(year, month)
{
    var date = new Date(year, month - 1, 32).getDate();
    return 32 - date;
}

function firstDayOfMonth(year, month)
{
    /* Returns offset. */
    return new Date(year,month - 1, 1 - (firstDayMonday * 1)).getDay();
}

function firstDayOfWeek(year, month, week)
{
    return ((week - 1) * 7) - firstDayOfMonth(year, month) + 1;
}

function getImageByType(entryType)
{
    for (var i = 0 ; i < entryTypesArray.length; i++)
    {
        if (entryType == entryTypesArray[i][0])
        {
            return entryTypesArray[i][2];
        }
    }

    return '';
}

function getShortDescriptionByType(entryType)
{
    for (var i = 0 ; i < entryTypesArray.length; i++)
    {
        if (entryType == entryTypesArray[i][0])
        {
            return entryTypesArray[i][1];
        }
    }

    return '';
}

function considerCheckBox(element, elementVisible)
{
    if (document.getElementById(element).checked )
    {
        document.getElementById(elementVisible).style.display = '';
    }
    else
    {
        document.getElementById(elementVisible).style.display = 'none';
    }
}

/* End helpers */

/* Data structures */

function DayPosition()
{
    this.Left = 0;
    this.Top = 0;
    this.Width = 0;
    this.Height = 0;
}

function CalendarEntry()
{
    /* Redundant */
    this.year = -1;
    this.month = -1;
    this.day = -1;

    this.hour = -1;
    this.minute = -1;
    this.description = '';
    this.dataHidden = 0;
    this.otherData = Array();

    this.setDataByArray = function(arr)
    {
        this.otherData = arr;
    }

    this.getData = function(field)
    {
        for (var i = 0; i < this.otherData.length; i++)
        {
            if (this.otherData[i][0] == field)
            {
                return this.otherData[i][1];
            }
        }
        return '';
    }
}

function CalendarDay()
{
    this.entries = [];
    this.day = -1;
}

function CalendarMonth()
{
    this.days = [];
    this.month = -1;
    this.year = -1;
}

var calendarData = new Array();
var visibleEntries = new Array();
var dayPositionData = new Array();

function getDataByDay(year, month, day)
{
    var i;
    var monthData = -1;

    /* Work out day outside of current month error */
    var _day = day;
    var _month = month;
    var _year = year;
    if (_day < 1)
    {
        _month--;
        if (_month < 1)
        {
            _month = 12;
            _year--;
        }

        _day += daysInMonth(_year, _month);
    }
    else if (_day > daysInMonth(_year, _month))
    {
        _day -= daysInMonth(_year, _month);
        _month++;
        if (_month > 12)
        {
            _month = 1;
            _year++;
        }
    }

    for (i = 0; i < calendarData.length; i++)
    {
        if (calendarData[i].year == _year && calendarData[i].month == _month)
        {
            monthData = calendarData[i];
        }
    }

    if (monthData == -1)
    {
        return -1; /* Needs more data. */
    }

    if (monthData.days.length != 0)
    {
        for (i = 0; i < monthData.days.length; i++)
        {
            if (monthData.days[i].day == _day)
            {
                return monthData.days[i];
            }
        }
    }

    return -2; /* No data. */
}

/* End Data structures */


/* Data population functions */

var httpLookup = AJAX_getXMLHttpObject();

function getCurrentCalendarUrl()
{
    var superuser = '';
    if (document.getElementById('hideNonPublic').checked)
    {
        superuser = '&superuser=1';
    }

    var viewString = '';
    switch (currentViewMode)
    {
        case (MONTH_VIEW):
            viewString = 'MONTHVIEW';
            break;

        case (WEEK_VIEW):
            viewString = 'WEEKVIEW';
            break;

        case (DAY_VIEW):
            viewString = 'DAYVIEW';
            break;
    }

    return indexName + '?m=calendar&view=' + viewString + '&month='
        + currentMonth + '&year=' + currentYear + '&week=' + currentWeek
        + '&day=' + currentDay + '' + superuser;
}

/* Pulls a string from the server to populate the calendar. */
function calendarDataPopulateServer(year, month)
{
    if (allowAjax && totalRecordsInMemory < 500)
    {
        // FIXME: Use lib.js AJAX code?
        httpLookup.open(
            'get', indexName + '?m=calendar&a=dynamicData&month=' + month
            + '&year=' + year
        );
        httpLookup.onreadystatechange = handleResponse;
        httpLookup.send(null);
    }
    else
    {
        document.location.href = getCurrentCalendarUrl();
    }
}

function handleResponse()
{
    if (httpLookup.readyState != 4)
    {
        return;
    }

    var response = httpLookup.responseText;
    if (response != '')
    {
        calendarDataPopulateString(response);
        refreshView();
    }
}

function refreshView()
{
    switch (currentViewMode)
    {
        case (MONTH_VIEW):
            updateCalendarViewMonth();
            break;

        case (WEEK_VIEW):
            userCalendarViewWeek();
            break;

        case (DAY_VIEW):
            updateCalendarViewDay();
            break;
    }
}


/* Calendar data string format - all text is url encoded
 * | - seperates data from field, * - seperates record for entry, @ - seperates entry
 * 'datetime|year,month,day,hour,minute*field|thedata*field|moredata@field|....'
 */
function calendarDataPopulateString(theData)
{
    var dateTime = [];
    var dateTimeVIndex = -1;

    /* Break the data down */
    var entriesArray = theData.split('@');
    for (var i = 0; i < entriesArray.length; i++)
    {
        if (entriesArray[i] == '')
        {
            continue;
        }

        valuesArray = entriesArray[i].split('*');
        dateTime = [];
        dateTimeIndex = -1;
        dateTimeNoEntriesIndex = -1;

        for (var j = 0; j < valuesArray.length; j++)
        {
            record = valuesArray[j].split('|');
            record[1] = decodeURIComponent(record[1]);

            if (record[0] == 'datetime')
            {
                dateTime = record[1].split(',');
                dateTimeIndex = j;
            }
            if (record[0] == 'noentries')
            {
                dateTime = record[1].split(',');
                dateTimeNoEntriesIndex = j;
            }
            valuesArray.splice(j, 1, record);
        }

        if (dateTime != -1)
        {
            valuesArray.splice(dateTimeIndex,1);
            calendarDataPopulate(
                dateTime[0], dateTime[1], dateTime[2], dateTime[3],
                dateTime[4], valuesArray
            );
        }

        if (dateTimeNoEntriesIndex != -1)
        {
            calendarDataMarkNoRecord(dateTime[0], dateTime[1]);
        }
    }
}

/* Insert a calendar entry -
 *  valuesArray is made of values[arbritrarylength][2] where 0 = field, 1 = value.
 */
function calendarDataPopulate(year, month, day, hour, minute, valuesArray)
{
    var i;

    /* Find or make month. */
    var monthIndex = -1;
    var monthObject;


    for (i = 0; i < calendarData.length; i++)
    {
        if (calendarData[i].year == year && calendarData[i].month == month)
        {
            monthIndex = i;
        }
    }

    if (monthIndex == -1)
    {
        calendarData = calendarData.concat(new CalendarMonth());
        monthIndex = calendarData.length - 1;
        monthObject = calendarData[monthIndex];

        monthObject.year = year;
        monthObject.month = month;
    }
    else
    {
        monthObject = calendarData[monthIndex];
    }

    /* Find or make day. */
    var dayIndex = -1;
    for (i = 0; i < monthObject.days.length; i++)
    {
        if (monthObject.days[i].day == day)
        {
            dayIndex = i;
        }
    }

    if (dayIndex == -1)
    {
        monthObject.days = monthObject.days.concat(new CalendarDay());
        dayIndex = monthObject.days.length - 1;
        dayObject = monthObject.days[dayIndex];

        dayObject.day = day;
    }
    else
    {
        dayObject = monthObject.days[dayIndex];
    }

    /* Make new entry. */
    dayObject.entries = dayObject.entries.concat(new CalendarEntry());
    entryIndex = dayObject.entries.length - 1;
    entryObject = dayObject.entries[entryIndex];

    /* Fill in entry. */
    entryObject.year = year;
    entryObject.month = month;
    entryObject.day = day;
    entryObject.hour = hour;
    entryObject.minute = minute;
    entryObject.setDataByArray(valuesArray);
    entryObject.description = entryObject.getData('description');

    totalRecordsInMemory++;

    if (currentMonth == month)
    {
        monthUpToDate = false;
        weekUpToDate = false;
    }

    if (currentDay == day)
    {
        dayUpToDate = false;
    }
}

/* Insert a null entry - make a month with no entries to symbolize that the data was loaded. */
function calendarDataMarkNoRecord(year, month)
{
    var i;

    /* Find or make month. */
    var monthIndex = -1;
    var monthObject;
    for (i = 0; i < calendarData.length; i++)
    {
        if (calendarData[i].year == year && calendarData[i].month == month)
        {
            monthIndex = i;
        }
    }

    if (monthIndex == -1)
    {
        calendarData = calendarData.concat(new CalendarMonth());
        monthIndex = calendarData.length - 1;
        monthObject = calendarData[monthIndex];

        monthObject.year = year;
        monthObject.month = month;
    }
    else
    {
        monthObject = calendarData[monthIndex];
    }

    monthObject.days = Array();
}

/* End data population functions */



/* User functions for showing view */

function setCalendarViewMonth(year, month)
{
    restoreHighlighting();

    if (currentViewMode != MONTH_VIEW)
    {
        hideAllViews();
        currentViewMode = MONTH_VIEW;
    }

    document.getElementById('calendarMonthParent').style.display = '';

    currentYear = year;
    currentMonth = month;

    updateCalendarViewMonth();
    setSideFormActions();
}

function setCalendarViewWeek(year, month, week)
{
    restoreHighlighting();

    if (currentViewMode != WEEK_VIEW)
    {
        hideAllViews();
        currentViewMode = WEEK_VIEW;
    }

    document.getElementById('calendarWeekParent').style.display = '';

    currentYear = year;
    currentMonth = month;
    currentWeek = week;
    

    updateCalendarViewWeek();
    setSideFormActions();
}

function setCalendarViewDay(year, month, day)
{
    restoreHighlighting();

    if (currentViewMode != DAY_VIEW)
    {
        hideAllViews();
        currentViewMode = DAY_VIEW;
    }

    document.getElementById('calendarDayParent').style.display = '';

    currentYear = year;
    currentMonth = month;
    currentDay = day;

    updateCalendarViewDay();
    setSideFormActions();
}

/* End user view funtions */

/* Fill in data onto chart functions */

function updateCalendarViewMonth()
{
    var i;
    var string;

    monthUpToDate = true;

    var _firstDayOfMonth = firstDayOfMonth(currentYear, currentMonth);
    var _daysInMonth = daysInMonth(currentYear, currentMonth);
    var offset = 0;

    var monthData = -1;

    document.getElementById('calendarTitle').innerHTML = 'Calendar: '
        + monthNameWrap(currentMonth) + ' ' + currentYear;

    document.getElementById('linkMonthBack').innerHTML = '<a href="javascript:setCalendarViewMonth('
        + yearBackwardsByMonth(currentYear, currentMonth) + ', '
        + monthBackwardsByMonth(currentMonth)
        + ');"><img src="images/arrow_left_24.gif" style="border:none;" />&nbsp;</a>';

    document.getElementById('linkMonthForeward').innerHTML = '<a href="javascript:setCalendarViewMonth('
        + yearForewardsByMonth(currentYear, currentMonth) + ', '
        + monthForewardsByMonth(currentMonth)
        + ');">&nbsp;<img src="images/arrow_right_24.gif" style="border:none;" /></a>';

    for (i = 0; i < calendarData.length; i++)
    {
        if (calendarData[i].year == currentYear &&
            calendarData[i].month == currentMonth)
        {
            monthData = calendarData[i];
        }
    }

    if (monthData == -1)
    {
        /* Clear all cells. */
        for (i = 0; i < 42; i++)
        {
            offset = i;
            document.getElementById('calendarMonthCell' + offset).innerHTML = '';
        }

        /* Trigger loading more data here. */
        document.getElementById('monthNotice').innerHTML = 'Loading '
            + monthNameWrap(currentMonth) + ' ' + currentYear + '...';
        document.getElementById('linkMonthBack').innerHTML = '';
        document.getElementById('linkMonthForeward').innerHTML = '';
        calendarDataPopulateServer(currentYear, currentMonth);
        return;
    }

    if (monthData.days.length == 0)
    {
        string = monthNameWrap(currentMonth) + ' ' + currentYear + ' (No Entries)';
        document.getElementById('monthNotice').innerHTML = string;
    }
    else
    {
        string = monthNameWrap(currentMonth) + ' ' + currentYear + '';
        document.getElementById('monthNotice').innerHTML = string;
    }

    for (i = 0; i < _firstDayOfMonth; i++)
    {
        offset = i;
        document.getElementById('calendarMonthCell' + offset).className = 'empty';
        document.getElementById('calendarMonthCell' + offset).style.display = '';
        document.getElementById('calendarMonthCell' + offset).innerHTML = '';
    }

    for (i = 0; i < _daysInMonth; i++)
    {
        offset = i + _firstDayOfMonth;
        if (currentYear == todayYear && currentMonth == todayMonth && i + 1 == todayDay)
        {
            document.getElementById('calendarMonthCell' + offset).className = 'today';
        }
        else
        {
            document.getElementById('calendarMonthCell' + offset).className = 'day';
        }
        document.getElementById('calendarMonthCell' + offset).style.display = '';

        /* Fill in day data. */
        updateCalendarViewMonthCell(currentYear, currentMonth, i + 1, offset, monthData);
    }

    for (i = _daysInMonth + _firstDayOfMonth; i < 42; i++)
    {
        offset = i;
        /* Get rid of 6th row if we can */
        if ((_daysInMonth + _firstDayOfMonth < 36) && (i >= 35))
        {
            document.getElementById('calendarMonthCell' + offset).style.display = 'none';
        }
        else
        {
            document.getElementById('calendarMonthCell' + offset).className = 'empty';
            document.getElementById('calendarMonthCell' + offset).innerHTML = '';
        }
    }
}


function updateCalendarViewMonthCell(year, month, day, cellID, monthData)
{
    var daylink = '<a class="dateLink" href="javascript:void(0);" onclick="setCalendarViewDay('
        + year + ', ' + month + ', ' + day + ');"'
    var string = daylink + '>' + day + '</a><br />';


    if (monthData != -1)
    {
        var dayIndex = -1;

        for (i = 0; i < monthData.days.length; i++)
        {
            if (monthData.days[i].day == day)
            {
                dayIndex = i;
            }
        }

        if (dayIndex != -1)
        {
            dayData =  monthData.days[dayIndex];

            var validDayEntriesCount = 0;
            for (i = 0; i < dayData.entries.length; i++)
            {
                if (dayData.entries[i].getData('userID') != userID &&
                    dayData.entries[i].getData('public') == 0 &&
                    document.getElementById('hideNonPublic').checked == false)
                {
                    continue;
                }
                else
                {
                    validDayEntriesCount++;
                }
            }

            if (validDayEntriesCount > 1)
            {
                string += generateCalendarEntryGrouped(
                    daylink + ' style="font-weight: bold;">' + validDayEntriesCount + ' Events' + '</a>'
                );
            }
            else
            {
                /* Fill in events */
                for (i = 0; i < dayData.entries.length; i++)
                {
                    if (dayData.entries[i].getData('userID') != userID &&
                        dayData.entries[i].getData('public') == 0 &&
                        document.getElementById('hideNonPublic').checked == false)
                    {
                        continue;
                    }

                    string += generateCalendarEntrySmall(
                        dayData.entries[i].getData('time'),
                        dayData.entries[i].getData('title'),
                        '<br />',
                        dayData.entries[i]
                    );
                }
            }
        }
    }

    document.getElementById('calendarMonthCell' + cellID).onclick  = function() { addEventByDay(year, month, day); };
    document.getElementById('calendarMonthCell' + cellID).innerHTML = string;
}


function updateCalendarViewWeek()
{
    var i;
    var string;

    weekUpToDate = true;

    var _firstDayOfMonth = firstDayOfMonth(currentYear, currentMonth);
    var _daysInMonth = daysInMonth(currentYear, currentMonth);
    var _firstDayOfWeek = firstDayOfWeek(currentYear, currentMonth, currentWeek);

    var offset = 0;
    var dayData = -1;
    var theDay = 0;
    var totalEntries = 0;

    weekNames = Array('1st', '2nd', '3rd', '4th', '5th', '6th');

    document.getElementById('calendarTitle').innerHTML = 'Calendar: '
        + weekNames[currentWeek-1] + ' week of ' + monthNameWrap(currentMonth)
        + ' ' + currentYear;

    document.getElementById('linkWeekBack').innerHTML = '<a href="javascript:setCalendarViewWeek('
        + yearBackwardsByWeek(currentYear, currentMonth, currentWeek)
        + ', ' + monthBackwardsByWeek(currentYear, currentMonth, currentWeek)
        + ', ' + weekBackwardsByWeek(currentYear, currentMonth, currentWeek)
        + ');"><img src="images/arrow_left_24.gif" style="border:none;" />&nbsp;</a>';

    document.getElementById('linkWeekForeward').innerHTML = '<a href="javascript:setCalendarViewWeek('
        + yearForewardsByWeek(currentYear, currentMonth, currentWeek)
        + ', ' + monthForewardsByWeek(currentYear, currentMonth, currentWeek)
        + ', ' + weekForewardsByWeek(currentYear, currentMonth, currentWeek)
        + ');">&nbsp;<img src="images/arrow_right_24.gif" style="border:none;" /></a>';

    for (i = 0; i < 7 ; i++)
    {
        theDay = i + 1 - (_firstDayOfMonth) + ((currentWeek - 1) * 7);
        dayData = getDataByDay(currentYear, currentMonth, theDay);

        document.getElementById('calendarWeekCell' + i).onclick  = function()
        {
            var day = (this.id.substring('calendarWeekCell'.length) * 1)
                + 1
                - (firstDayOfMonth(currentYear, currentMonth))
                + ((currentWeek - 1) * 7);
            addEventByDay(
                getYearByDay(currentYear, currentMonth, day),
                getMonthByDay(currentYear, currentMonth, day),
                getDayByDay(currentYear, currentMonth, day)
            );
        };

        if (dayData == -1)
        {
            /* Populate more data. */
            document.getElementById('weekNotice').innerHTML = 'Loading '
                + monthNameWrap(getMonthByDay(currentYear, currentMonth, theDay))
                + ' ' + getYearByDay(currentYear, currentMonth, theDay) + '...';
            document.getElementById('linkWeekBack').innerHTML = '';
            document.getElementById('linkWeekForeward').innerHTML = '';
            calendarDataPopulateServer(getYearByDay(currentYear, currentMonth, theDay), getMonthByDay(currentYear, currentMonth, theDay));
            return;
        }
        else if (dayData == -2)
        {
            /* Nothing. */
            document.getElementById('calendarWeekCell' + i).innerHTML = '';
        }
        else
        {
            totalEntries += dayData.entries.length;
            updateCalendarViewWeekCell('calendarWeekCell' + i, dayData);
        }

        if (getDayByDay(currentYear, currentMonth, theDay) == todayDay &&
            getMonthByDay(currentYear, currentMonth, theDay) == todayMonth &&
            getYearByDay(currentYear, currentMonth, theDay) == currentYear)
        {
            document.getElementById('calendarWeekCell' + i).className = 'today';
        }
        else
        {
            document.getElementById('calendarWeekCell' + i).className = 'day';
        }
        document.getElementById('weekDay' + i).innerHTML = monthNameAbreiv[getMonthByDay(currentYear, currentMonth, theDay) - 1]
            + getDayByDay(currentYear, currentMonth, theDay);
    }

    document.getElementById('weekNotice').innerHTML = totalEntries + ' entries for ' + weekNames[currentWeek - 1] + ' week of ' + monthNameWrap(currentMonth) + ' ' + currentYear;
}

function updateCalendarViewWeekCell(cellID, dayData)
{
    var string = '';

    for (var i = 0; i < dayData.entries.length; i++)
    {
        if (dayData.entries[i].getData('userID') != userID &&
            dayData.entries[i].getData('public') == 0 &&
            document.getElementById('hideNonPublic').checked == false)
        {
            continue;
        }

        string += generateCalendarEntrySmall(
            dayData.entries[i].getData('time'),
            dayData.entries[i].getData('title'),
            '&nbsp;-',
            dayData.entries[i]
        );
    }
    document.getElementById(cellID).innerHTML = string;

}

function getDayCellByHour(hour)
{
    if (hour < dayHourStart)
    {
        /* Morning. */
        return document.getElementById('calendarDayCell0');
    }
    if (hour > dayHourEnd)
    {
        /* Evening. */
        return document.getElementById('calendarDayCell' + (dayTotalCells - 1));
    }

    /* Mid day. */
    return document.getElementById('calendarDayCell' + (hour - dayHourStart + 1));
}

function getDayHourByCell(id)
{
    if (id == 0)
    {
        /* Morning. */
        return 0;
    }

    if (dayTotalCells - 1 == id)
    {
        /* Evening. */
        return dayHourEnd + 1;
    }

    /* Mid day. */
    return dayHourStart + id - 1;
}

function updateCalendarViewDay()
{
    var i;
    var string;

    dayUpToDate = true;

    var offset = 0;
    var dayData = -1;
    var theDay = currentDay;
    var totalEntries = 0;

    dayPositionData = Array();

    document.getElementById('calendarTitle').innerHTML = 'Calendar: ' + monthNameWrap(currentMonth) + ' ' + currentDay + ', ' + currentYear;

    document.getElementById('linkDayBack').innerHTML = '<a href="javascript:setCalendarViewDay(' + getYearByDay(currentYear, currentMonth, currentDay - 1) + ', ' + getMonthByDay(currentYear, currentMonth, currentDay - 1) + ', ' + getDayByDay(currentYear, currentMonth, currentDay - 1) + ');"><img src="images/arrow_left_24.gif" style="border:none;" />&nbsp;</a>';
    document.getElementById('linkDayForeward').innerHTML = '<a href="javascript:setCalendarViewDay(' + getYearByDay(currentYear, currentMonth, currentDay + 1) + ', ' + getMonthByDay(currentYear, currentMonth, currentDay + 1) + ', ' + getDayByDay(currentYear, currentMonth, currentDay + 1) + ');">&nbsp;<img src="images/arrow_right_24.gif" style="border:none;" /></a>';

    /* Reset view */
    for (i = 0; i < dayTotalCells; i++)
    {
        document.getElementById('calendarDayCell' + i).innerHTML = '';
    }

    dayData = getDataByDay(currentYear, currentMonth, theDay)
    if (dayData == -1)
    {
        /* Populate more data */
        document.getElementById('dayNotice').innerHTML = 'Loading ' + monthNameWrap(getMonthByDay(currentYear, currentMonth, theDay)) + ' ' + getYearByDay(currentYear, currentMonth, theDay) + '...'
        document.getElementById('linkDayBack').innerHTML = '';
        document.getElementById('linkDayForeward').innerHTML = '';
        calendarDataPopulateServer(getYearByDay(currentYear, currentMonth, theDay), getMonthByDay(currentYear, currentMonth, theDay));
        return;
    }
    else if (dayData == -2)
    {
        /* Nothing. */
    }
    else
    {
        totalEntries += dayData.entries.length;
        for (i = 0; i < dayData.entries.length; i++)
        {
            updateCalendarViewDayCell(getDayCellByHour(dayData.entries[i].hour), dayData.entries[i]);
        }
    }

    for (i = 0; i < dayTotalCells; i++)
    {
        document.getElementById('calendarDayCell' + i).className = 'day';
        document.getElementById('calendarDayCell' + i).onclick  = function()
        {
            addEventByDay(
                currentYear,
                currentMonth,
                currentDay,
                getDayHourByCell(this.id.substring('calendarDayCell'.length) * 1)
            );
        };
    }

    if (currentDay == todayDay && currentMonth == todayMonth && currentYear == currentYear)
    {
        getDayCellByHour(todayHour).className = 'today';
    }


    document.getElementById('dayNotice').innerHTML = totalEntries + ' entries for ' + monthNameWrap(currentMonth) + ' ' + currentDay + ', ' + currentYear;

}


function updateCalendarViewDayCell(cell, entry)
{
    if (entry.getData('userID') != userID && entry.getData('public') == 0 && document.getElementById('hideNonPublic').checked == false)
    {
        return;
    }

    var border = 5;

    string = '';

    var idEntry = 'dayCell' + dayPositionData.length;

    string += generateCalendarEntryDayView(
        entry.getData('time'),
        entry.getData('title'),
        0,
        0,
        idEntry,
        '',
        entry,
        0
    );

    cell.innerHTML += string;
}

/* End fill in data into cells functions */

/* Event handlers */
function userCalendarViewMonth()
{
    setCalendarViewMonth(currentYear, currentMonth);
}

function userCalendarViewWeek()
{
    /* Determine current week.*/
    if (selectedTableRow != 0 && currentViewMode == MONTH_VIEW)
    {
        currentWeek = selectedTableRow.id.substring('calendarRow'.length);
        setCalendarViewWeek(currentYear, currentMonth, currentWeek);
    }
    else if (currentViewMode == MONTH_VIEW)
    {
        /* If no week, try todays week, otherwise do previously seleted week. */
        if (currentYear == todayYear && currentMonth == todayMonth)
        {
            var _firstDayOfMonth = firstDayOfMonth(currentYear, currentMonth);
            currentWeek = Math.floor((todayDay + _firstDayOfMonth - 1) / 7) + 1;
        }
        else
        {
            /* If week 6 and only 5 weeks... */
            var _firstDayOfMonth = firstDayOfMonth(currentYear, currentMonth);
            var _daysInMonth = daysInMonth(currentYear, currentMonth);

            if (_daysInMonth + _firstDayOfMonth < 36 && currentWeek >= 6)
            {
                currentWeek = 5;
            }

            if (currentWeek < 1)
            {
                currentWeek = 1;
            }
        }
        setCalendarViewWeek(currentYear, currentMonth, currentWeek);
    }
    else if (currentViewMode == DAY_VIEW)
    {
        var _firstDayOfMonth = firstDayOfMonth(currentYear, currentMonth);
        currentWeek = Math.floor((currentDay + _firstDayOfMonth - 1) / 7) + 1;
        setCalendarViewWeek(currentYear, currentMonth, currentWeek);
    }
    else
    {
        setCalendarViewWeek(currentYear, currentMonth, currentWeek);
    }
}

function userCalendarViewDay()
{
    /* Determine current day */
    if (selectedTableCell != 0 && currentViewMode == MONTH_VIEW && selectedTableCell.id.indexOf('calendarMonthCell') == 0)
    {
        currentDay = (selectedTableCell.id.substring('calendarMonthCell'.length) * 1);
        currentDay -= firstDayOfMonth(currentYear, currentMonth) - 1;
        if (currentDay < 1)
        {
            currentDay = 1;
        }
        if (currentDay > daysInMonth(currentYear, currentMonth))
        {
            currentDay = daysInMonth(currentYear, currentMonth);
        }
        setCalendarViewDay(currentYear, currentMonth, currentDay);
    }
    else if (currentViewMode == MONTH_VIEW)
    {
        if (currentYear == todayYear && currentMonth == todayMonth)
        {
            currentDay = todayDay;
        }
        else
        {
            if (currentDay < 1)
            {
                currentDay = 1;
            }
            if (currentDay > daysInMonth(currentYear, currentMonth))
            {
                currentDay = daysInMonth(currentYear, currentMonth);
            }
        }
        setCalendarViewDay(currentYear, currentMonth, currentDay);
    }
    else if (selectedTableCell != 0 && currentViewMode == WEEK_VIEW && selectedTableCell.id.indexOf('calendarWeekCell') == 0)
    {
        var i = (selectedTableCell.id.substring('calendarWeekCell'.length) * 1);
        var _firstDayOfMonth = firstDayOfMonth(currentYear, currentMonth);
        theDay = i + 1 - (_firstDayOfMonth) + ((currentWeek-1) * 7);

        var _day = getDayByDay(currentYear, currentMonth, theDay);
        var _month = getMonthByDay(currentYear, currentMonth, theDay);
        var _year = getYearByDay(currentYear, currentMonth, theDay);

        currentDay = _day;
        currentMonth = _month;
        currentYear = _year;

        setCalendarViewDay(currentYear, currentMonth, currentDay);
    }
    else if (currentViewMode == WEEK_VIEW)
    {
        var i = 1;
        var _firstDayOfMonth = firstDayOfMonth(currentYear, currentMonth);
        theDay = i + 1 - (_firstDayOfMonth) + ((currentWeek-1) * 7);

        var _day = getDayByDay(currentYear, currentMonth, theDay);
        var _month = getMonthByDay(currentYear, currentMonth, theDay);
        var _year = getYearByDay(currentYear, currentMonth, theDay);

        currentDay = _day;
        currentMonth = _month;
        currentYear = _year;

        setCalendarViewDay(currentYear, currentMonth, currentDay);
    }
}

function goToToday()
{
    setCalendarViewDay(todayYear, todayMonth, todayDay);
}




/* End event handlers */

/* Display functions */




/* End display functions */