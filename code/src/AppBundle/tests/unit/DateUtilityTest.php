<?php

include_once(LEGACY_ROOT . '/constants.php');
include_once(LEGACY_ROOT . '/lib/StringUtility.php');
include_once(LEGACY_ROOT . '/lib/DateUtility.php');   /* Depends on StringUtility. */

class DateUtilityTest extends \PHPUnit_Framework_TestCase
{
    /* Tests for getStartingWeekday(). */
    function testGetStartingWeekday()
    {
        $this->assertSame(
            \DateUtility::getStartingWeekday(CALENDAR_MONTH_MARCH, 2006),
            CALENDAR_DAY_WEDNSDAY
            );

        $this->assertSame(
            \DateUtility::getStartingWeekday(CALENDAR_MONTH_MARCH, 1987),
            CALENDAR_DAY_SUNDAY
            );
        $this->assertSame(
            \DateUtility::getStartingWeekday(CALENDAR_MONTH_APRIL, 1987),
            CALENDAR_DAY_WEDNSDAY
            );
    }

    /* Tests for getStartingWeekday(). */
    function testGetDaysInMonth()
    {
        $this->assertSame(
            \DateUtility::getDaysInMonth(CALENDAR_MONTH_MARCH, 2006),
            31
            );

        $this->assertSame(
            \DateUtility::getDaysInMonth(CALENDAR_MONTH_MARCH, 1987),
            \DateUtility::getDaysInMonth(CALENDAR_MONTH_MARCH, 2006)
            );

        $this->assertSame(
            \DateUtility::getDaysInMonth(CALENDAR_MONTH_APRIL, 1987),
            30
            );

        /* Leap years... */
        $this->assertSame(
            \DateUtility::getDaysInMonth(CALENDAR_MONTH_FEBRUARY, 2008),
            29
            );
        $this->assertSame(
            \DateUtility::getDaysInMonth(CALENDAR_MONTH_FEBRUARY, 2006),
            28
            );
    }

    /* Tests for getMonthName(). */
    function testGetMonthName()
    {
        $this->assertSame(
            \DateUtility::getMonthName(CALENDAR_MONTH_JANUARY),
            'January'
            );
        $this->assertSame(
            \DateUtility::getMonthName(CALENDAR_MONTH_FEBRUARY),
            'February'
            );
        $this->assertSame(
            \DateUtility::getMonthName(CALENDAR_MONTH_MARCH),
            'March'
            );
        $this->assertSame(
            \DateUtility::getMonthName(CALENDAR_MONTH_APRIL),
            'April'
            );
        $this->assertSame(
            \DateUtility::getMonthName(CALENDAR_MONTH_MAY),
            'May'
            );
        $this->assertSame(
            \DateUtility::getMonthName(CALENDAR_MONTH_JUNE),
            'June'
            );
        $this->assertSame(
            \DateUtility::getMonthName(CALENDAR_MONTH_JULY),
            'July'
            );
        $this->assertSame(
            \DateUtility::getMonthName(CALENDAR_MONTH_AUGUST),
            'August'
            );
        $this->assertSame(
            \DateUtility::getMonthName(CALENDAR_MONTH_SEPTEMBER),
            'September'
            );
        $this->assertSame(
            \DateUtility::getMonthName(CALENDAR_MONTH_OCTOBER),
            'October'
            );
        $this->assertSame(
            \DateUtility::getMonthName(CALENDAR_MONTH_NOVEMBER),
            'November'
            );
        $this->assertSame(
            \DateUtility::getMonthName(CALENDAR_MONTH_DECEMBER),
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
            array('-', '2007-03-40', DATE_FORMAT_YYYYMMDD),
            array('-', 'This sentence contains 12-01-05.', DATE_FORMAT_MMDDYY)
        );

        foreach ($validDates as $key => $value)
        {
            $this->assertTrue(
                \DateUtility::validate($value[0], $value[1], $value[2]),
                $value[1] . ' (Separator: ' . $value[0] . ')'
                );
        }

        foreach ($invalidDates as $key => $value)
        {
            $this->assertFalse(
                \DateUtility::validate($value[0], $value[1], $value[2]),
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
            array('-', '2002-01-30', DATE_FORMAT_YYYYMMDD, DATE_FORMAT_MMDDYY, '01-30-02'),
        );

        foreach ($dates as $key => $value)
        {
            $this->assertSame(
                \DateUtility::convert($value[0], $value[1], $value[2], $value[3]),
                $value[4]
                );
        }
    }
}

?>
