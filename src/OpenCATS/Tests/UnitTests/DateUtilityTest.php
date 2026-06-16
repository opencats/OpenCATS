<?php
/**
 * CATS
* Date Utility Library
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
* @version    $Id: DateUtility.php 3592 2007-11-13 17:30:46Z brian $
*/

/**
 *	Date Utility Library
*	@package    CATS
*	@subpackage Library
*/

use PHPUnit\Framework\TestCase;

include_once(LEGACY_ROOT . '/constants.php');
include_once(LEGACY_ROOT . '/lib/StringUtility.php');
include_once(LEGACY_ROOT . '/lib/DateUtility.php');   /* Depends on StringUtility. */

class DateUtilityTest extends TestCase
{
    /* Tests for getStartingWeekday(). */
    function testGetStartingWeekday()
    {
        $this->assertSame(
            DateUtility::getStartingWeekday(CALENDAR_MONTH_MARCH, 2006),
            CALENDAR_DAY_WEDNSDAY
            );

        $this->assertSame(
            DateUtility::getStartingWeekday(CALENDAR_MONTH_MARCH, 1987),
            CALENDAR_DAY_SUNDAY
            );
        $this->assertSame(
            DateUtility::getStartingWeekday(CALENDAR_MONTH_APRIL, 1987),
            CALENDAR_DAY_WEDNSDAY
            );
    }

    /* Tests for getStartingWeekday(). */
    function testGetDaysInMonth()
    {
        $this->assertSame(
            DateUtility::getDaysInMonth(CALENDAR_MONTH_MARCH, 2006),
            31
            );

        $this->assertSame(
            DateUtility::getDaysInMonth(CALENDAR_MONTH_MARCH, 1987),
            DateUtility::getDaysInMonth(CALENDAR_MONTH_MARCH, 2006)
            );

        $this->assertSame(
            DateUtility::getDaysInMonth(CALENDAR_MONTH_APRIL, 1987),
            30
            );

        /* Leap years... */
        $this->assertSame(
            DateUtility::getDaysInMonth(CALENDAR_MONTH_FEBRUARY, 2008),
            29
            );
        $this->assertSame(
            DateUtility::getDaysInMonth(CALENDAR_MONTH_FEBRUARY, 2006),
            28
            );
    }

    /* Tests for getMonthName(). */
    function testGetMonthName()
    {
        $this->assertSame(
            DateUtility::getMonthName(CALENDAR_MONTH_JANUARY),
            'January'
            );
        $this->assertSame(
            DateUtility::getMonthName(CALENDAR_MONTH_FEBRUARY),
            'February'
            );
        $this->assertSame(
            DateUtility::getMonthName(CALENDAR_MONTH_MARCH),
            'March'
            );
        $this->assertSame(
            DateUtility::getMonthName(CALENDAR_MONTH_APRIL),
            'April'
            );
        $this->assertSame(
            DateUtility::getMonthName(CALENDAR_MONTH_MAY),
            'May'
            );
        $this->assertSame(
            DateUtility::getMonthName(CALENDAR_MONTH_JUNE),
            'June'
            );
        $this->assertSame(
            DateUtility::getMonthName(CALENDAR_MONTH_JULY),
            'July'
            );
        $this->assertSame(
            DateUtility::getMonthName(CALENDAR_MONTH_AUGUST),
            'August'
            );
        $this->assertSame(
            DateUtility::getMonthName(CALENDAR_MONTH_SEPTEMBER),
            'September'
            );
        $this->assertSame(
            DateUtility::getMonthName(CALENDAR_MONTH_OCTOBER),
            'October'
            );
        $this->assertSame(
            DateUtility::getMonthName(CALENDAR_MONTH_NOVEMBER),
            'November'
            );
        $this->assertSame(
            DateUtility::getMonthName(CALENDAR_MONTH_DECEMBER),
            'December'
            );
    }

    /* Tests for validate(). */
    function testValidate()
    {
        $validDates = array(
            array('/', '01/01/01', DATE_FORMAT_MMDDYY),
            array('/', '02/27/05', DATE_FORMAT_MMDDYY),
            array('/', '02/28/05', DATE_FORMAT_MMDDYY),
            array('/', '02/29/04', DATE_FORMAT_MMDDYY),
            array('/', '02/29/00', DATE_FORMAT_MMDDYY),
            array('/', '12/31/05', DATE_FORMAT_MMDDYY),
            array('-', '12-31-05', DATE_FORMAT_MMDDYY),
            array('-', '02-29-00', DATE_FORMAT_MMDDYY),
            array('-', '22-02-07', DATE_FORMAT_DDMMYY),
            array('-', '29-02-04', DATE_FORMAT_DDMMYY),
            array('-', '2007-03-25', DATE_FORMAT_YYYYMMDD)
        );

        $invalidDates = array(
            array('/', '00/00/00', DATE_FORMAT_MMDDYY),
            array('/', '02/29/05', DATE_FORMAT_MMDDYY),
            array('/', '02/31/05', DATE_FORMAT_MMDDYY),
            array('/', '13/01/05', DATE_FORMAT_MMDDYY),
            array('/', '00/01/05', DATE_FORMAT_MMDDYY),
            array('/', '12-01-05', DATE_FORMAT_MMDDYY),
            array('-', '00/01/05', DATE_FORMAT_MMDDYY),
            array('-', '00-01-05', DATE_FORMAT_MMDDYY),
            array('/', '00/01/2005', DATE_FORMAT_MMDDYY),
            array('-', '00/01/2005', DATE_FORMAT_MMDDYY),
            array('-', '00-01-2005', DATE_FORMAT_MMDDYY),
            array('-', '000105', DATE_FORMAT_MMDDYY),
            array('-', 'Test!', DATE_FORMAT_MMDDYY),
            array('-', '02-29-07', DATE_FORMAT_DDMMYY),
            array('-', '29-02-05', DATE_FORMAT_DDMMYY),
            array('-', '31-04-05', DATE_FORMAT_DDMMYY),
            array('-', '2007-03-40', DATE_FORMAT_YYYYMMDD),
            array('-', 'This sentence contains 12-01-05.', DATE_FORMAT_MMDDYY)
        );

        foreach ($validDates as $key => $value)
        {
            $this->assertTrue(
                DateUtility::validate($value[0], $value[1], $value[2]),
                $value[1] . ' (Separator: ' . $value[0] . ')'
                );
        }

        foreach ($invalidDates as $key => $value)
        {
            $this->assertFalse(
                DateUtility::validate($value[0], $value[1], $value[2]),
                $value[1] . ' (Separator: ' . $value[0] . ')'
                );
        }
    }

    /* Tests for convert(). */
    function testConvert()
    {
        $dates = array(
            array('/', '01/01/01', DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD, '2001/01/01'),
            array('/', '02/27/01', DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD, '2001/02/27'),
            array('-', '01-01-01', DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD, '2001-01-01'),
            array('-', '02-27-01', DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD, '2001-02-27'),
            array('/', '22/02/07', DATE_FORMAT_DDMMYY, DATE_FORMAT_YYYYMMDD, '2007/02/22'),
            array('-', '22-02-07', DATE_FORMAT_DDMMYY, DATE_FORMAT_YYYYMMDD, '2007-02-22'),
            array('-', '2002-01-30', DATE_FORMAT_YYYYMMDD, DATE_FORMAT_MMDDYY, '01-30-02'),
            array('-', '2007-02-22', DATE_FORMAT_YYYYMMDD, DATE_FORMAT_DDMMYY, '22-02-07'),
            array('-', '31-12-05', DATE_FORMAT_DDMMYY, DATE_FORMAT_MMDDYY, '12-31-05'),
            array('-', '12-31-05', DATE_FORMAT_MMDDYY, DATE_FORMAT_DDMMYY, '31-12-05'),
            array('-', 'invalid', DATE_FORMAT_DDMMYY, DATE_FORMAT_YYYYMMDD, '0000-00-00'),
            array('-', 'invalid', DATE_FORMAT_DDMMYY, DATE_FORMAT_MMDDYY, '00-00-00'),
        );

        foreach ($dates as $key => $value)
        {
            $this->assertSame(
                DateUtility::convert($value[0], $value[1], $value[2], $value[3]),
                $value[4]
                );
        }
    }

    /* Tests for getMysqlTimeFormat(). */
    function testGetMysqlTimeFormat()
    {
        $this->assertSame('%%h:%%i %%p', DateUtility::getMysqlTimeFormat(false));
        $this->assertSame('%%H:%%i',     DateUtility::getMysqlTimeFormat(true));
    }

    /* Tests for getMysqlDateTimeFormat(). */
    function testGetMysqlDateTimeFormat()
    {
        $this->assertSame('%%m-%%d-%%y (%%h:%%i %%p)', DateUtility::getMysqlDateTimeFormat(false));
        $this->assertSame('%%m-%%d-%%y (%%H:%%i)',     DateUtility::getMysqlDateTimeFormat(true));
    }

    /* Tests for getMysqlDateTimeSecondsFormat(). */
    function testGetMysqlDateTimeSecondsFormat()
    {
        $this->assertSame('%%m-%%d-%%y (%%h:%%i:%%s %%p)', DateUtility::getMysqlDateTimeSecondsFormat(false));
        $this->assertSame('%%m-%%d-%%y (%%H:%%i:%%s)',     DateUtility::getMysqlDateTimeSecondsFormat(true));
    }

    /* Tests for getTimeFormat(). */
    function testGetTimeFormat()
    {
        $this->assertSame('g:i A', DateUtility::getTimeFormat(false));
        $this->assertSame('H:i',   DateUtility::getTimeFormat(true));
    }

    /* Tests for getDateTimeFormat(). */
    function testGetDateTimeFormat()
    {
        $this->assertSame('m-d-Y g:i A', DateUtility::getDateTimeFormat('m-d-Y', false));
        $this->assertSame('m-d-Y H:i',   DateUtility::getDateTimeFormat('m-d-Y', true));
        $this->assertSame('d-m-Y g:i A', DateUtility::getDateTimeFormat('d-m-Y', false));
        $this->assertSame('d-m-Y H:i',   DateUtility::getDateTimeFormat('d-m-Y', true));
    }

    /* Tests for normalizeActivityTime(). */
    function testNormalizeActivityTime()
    {
        /* 12-hour mode standard cases. */
        $this->assertSame('01:30:00', DateUtility::normalizeActivityTime(1,  30, 'AM', false));
        $this->assertSame('13:30:00', DateUtility::normalizeActivityTime(1,  30, 'PM', false));
        $this->assertSame('11:59:00', DateUtility::normalizeActivityTime(11, 59, 'AM', false));
        $this->assertSame('23:59:00', DateUtility::normalizeActivityTime(11, 59, 'PM', false));

        /* Midnight: 12:00 AM must become 00:00:00. */
        $this->assertSame('00:00:00', DateUtility::normalizeActivityTime(12, 0, 'AM', false));

        /* Noon: 12:00 PM must become 12:00:00. */
        $this->assertSame('12:00:00', DateUtility::normalizeActivityTime(12, 0, 'PM', false));

        /* 24-hour mode stored directly. */
        $this->assertSame('00:00:00', DateUtility::normalizeActivityTime(0,  0,  '', true));
        $this->assertSame('13:30:00', DateUtility::normalizeActivityTime(13, 30, '', true));
        $this->assertSame('23:59:00', DateUtility::normalizeActivityTime(23, 59, '', true));

        /* Invalid inputs must return false. */
        $this->assertFalse(DateUtility::normalizeActivityTime(0,  0,  'AM', false)); /* hour 0 invalid in 12h */
        $this->assertFalse(DateUtility::normalizeActivityTime(13, 0,  'AM', false)); /* hour 13 invalid in 12h */
        $this->assertFalse(DateUtility::normalizeActivityTime(24, 0,  '',   true));  /* hour 24 invalid in 24h */
        $this->assertFalse(DateUtility::normalizeActivityTime(1,  60, 'AM', false)); /* minute 60 invalid */
        $this->assertFalse(DateUtility::normalizeActivityTime(1,  0,  '',   false)); /* missing meridiem in 12h */
    }
}

?>
