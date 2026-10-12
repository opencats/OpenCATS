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
    public function testRecruitmentWorkingDatetimeBoundaries(): void
    {
        $original = date_default_timezone_get();
        try
        {
            foreach (array('UTC', 'Europe/London', 'America/New_York') as $zone)
            {
                date_default_timezone_set($zone);
                foreach (array(
                    array('2026-10-09 15:30:00', 2, '2026-10-13 15:30:00'),
                    array('2026-10-09 15:30:00', 5, '2026-10-16 15:30:00'),
                    array('2026-10-10 15:30:00', 2, '2026-10-13 15:30:00'),
                    array('2026-10-11 15:30:00', 5, '2026-10-16 15:30:00'),
                    array('2026-03-27 15:30:00', 2, '2026-03-31 15:30:00'),
                    array('2026-10-23 15:30:00', 2, '2026-10-27 15:30:00'),
                    array('2026-12-24 15:30:00', 2, '2026-12-28 15:30:00')) as [$start, $days, $end])
                    self::assertSame($end, DateUtility::addWorkingDays($start, $days));
            }
            foreach (array(null, array(), '', '2026-02-30 12:00:00', '2026-01-01 24:00:00',
                '0000-00-00 00:00:00', '2026-01-01') as $invalid)
                self::assertNull(DateUtility::addWorkingDays($invalid, 2));
            self::assertNull(DateUtility::addWorkingDays('2026-01-01 00:00:00', -1));
        }
        finally { date_default_timezone_set($original); }
    }

    public function testWarningCalendarAndWorkingDays(): void
    {
        foreach (array(
            array('2026-10-09', '2026-10-12', 1), // Friday to Monday
            array('2026-10-09', '2026-10-11', 0),
            array('2026-10-10', '2026-10-12', 1),
            array('2026-10-09', '2026-10-13', 2),
            array('2026-10-09', '2026-10-14', 3),
            array('2026-10-09', '2026-10-16', 5),
            array('2026-10-09', '2026-10-19', 6),
            array('2026-10-30', '2026-11-02', 1),
            array('2026-12-31', '2027-01-04', 2), // No holiday calendar
            array('2026-10-12', '2026-10-09', 0),
            array('2026-10-09', '2026-10-09', 0),
            array(null, '2026-10-09', null),
            array('', '2026-10-09', null),
            array('2026-02-30', '2026-10-09', null),
            array('2026-10-09', '0000-00-00', null),
            array(array(), '2026-10-09', null)
        ) as $case)
            self::assertSame($case[2], DateUtility::getWorkingDaysBetween($case[0], $case[1]));
        self::assertSame(3, DateUtility::getCalendarDaysBetween('2026-10-09', '2026-10-12'));
        self::assertSame(-3, DateUtility::getCalendarDaysBetween('2026-10-12', '2026-10-09'));
        $timezone = date_default_timezone_get();
        try
        {
            date_default_timezone_set('Europe/London');
            self::assertSame(3, DateUtility::getCalendarDaysBetween('2026-03-27', '2026-03-30'));
            self::assertSame(1, DateUtility::getWorkingDaysBetween('2026-03-27', '2026-03-30'));
        }
        finally { date_default_timezone_set($timezone); }
    }

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
