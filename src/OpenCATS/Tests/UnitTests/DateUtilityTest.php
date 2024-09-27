<?php

use PHPUnit\Framework\TestCase;

if (! defined('LEGACY_ROOT')) {
    define('LEGACY_ROOT', '.');
}

include_once(LEGACY_ROOT . '/constants.php');
include_once(LEGACY_ROOT . '/lib/StringUtility.php');
include_once(LEGACY_ROOT . '/lib/DateUtility.php');   /* Depends on StringUtility. */

class DateUtilityTest extends TestCase
{
    // Declare the properties
    private $CALENDAR_MONTH_JANUARY;
    private $CALENDAR_MONTH_FEBRUARY;
    private $CALENDAR_MONTH_MARCH;
    private $CALENDAR_MONTH_APRIL;
    private $CALENDAR_MONTH_MAY;
    private $CALENDAR_MONTH_JUNE;
    private $CALENDAR_MONTH_JULY;
    private $CALENDAR_MONTH_AUGUST;
    private $CALENDAR_MONTH_SEPTEMBER;
    private $CALENDAR_MONTH_OCTOBER;
    private $CALENDAR_MONTH_NOVEMBER;
    private $CALENDAR_MONTH_DECEMBER;
    private $CALENDAR_DAY_WEDNSDAY;
    private $CALENDAR_DAY_SUNDAY;
    private $DATE_FORMAT_MMDDYY;
    private $DATE_FORMAT_YYYYMMDD;
    private $DATE_FORMAT_DDMMYY;

    /* Tests for getStartingWeekday(). */
    public function testGetStartingWeekday()
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
    public function testGetDaysInMonth()
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
    public function testGetMonthName()
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
    public function testValidate()
    {
        $validDates = [
            ['/', '01/01/01', DATE_FORMAT_MMDDYY],
            ['/', '02/27/05', DATE_FORMAT_MMDDYY],
            ['/', '02/28/05', DATE_FORMAT_MMDDYY],
            ['/', '02/29/04', DATE_FORMAT_MMDDYY],
            ['/', '02/29/00', DATE_FORMAT_MMDDYY],
            ['/', '12/31/05', DATE_FORMAT_MMDDYY],
            ['-', '12-31-05', DATE_FORMAT_MMDDYY],
            ['-', '02-29-00', DATE_FORMAT_MMDDYY],
            ['-', '22-02-07', DATE_FORMAT_DDMMYY],
            ['-', '2007-03-25', DATE_FORMAT_YYYYMMDD],
        ];

        $invalidDates = [
            ['/', '00/00/00', DATE_FORMAT_MMDDYY],
            ['/', '02/29/05', DATE_FORMAT_MMDDYY],
            ['/', '02/31/05', DATE_FORMAT_MMDDYY],
            ['/', '13/01/05', DATE_FORMAT_MMDDYY],
            ['/', '00/01/05', DATE_FORMAT_MMDDYY],
            ['/', '12-01-05', DATE_FORMAT_MMDDYY],
            ['-', '00/01/05', DATE_FORMAT_MMDDYY],
            ['-', '00-01-05', DATE_FORMAT_MMDDYY],
            ['/', '00/01/2005', DATE_FORMAT_MMDDYY],
            ['-', '00/01/2005', DATE_FORMAT_MMDDYY],
            ['-', '00-01-2005', DATE_FORMAT_MMDDYY],
            ['-', '000105', DATE_FORMAT_MMDDYY],
            ['-', 'Test!', DATE_FORMAT_MMDDYY],
            ['-', '02-29-07', DATE_FORMAT_DDMMYY],
            ['-', '2007-03-40', DATE_FORMAT_YYYYMMDD],
            ['-', 'This sentence contains 12-01-05.', DATE_FORMAT_MMDDYY],
        ];

        foreach ($validDates as $key => $value) {
            $this->assertTrue(
                DateUtility::validate($value[0], $value[1], $value[2]),
                $value[1] . ' (Separator: ' . $value[0] . ')'
            );
        }

        foreach ($invalidDates as $key => $value) {
            $this->assertFalse(
                DateUtility::validate($value[0], $value[1], $value[2]),
                $value[1] . ' (Separator: ' . $value[0] . ')'
            );
        }
    }

    /* Tests for convert(). */
    public function testConvert()
    {
        $dates = [
            ['/', '01/01/01', DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD, '2001/01/01'],
            ['/', '02/27/01', DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD, '2001/02/27'],
            ['-', '01-01-01', DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD, '2001-01-01'],
            ['-', '02-27-01', DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD, '2001-02-27'],
            ['-', '2002-01-30', DATE_FORMAT_YYYYMMDD, DATE_FORMAT_MMDDYY, '01-30-02'],
        ];

        foreach ($dates as $key => $value) {
            $this->assertSame(
                DateUtility::convert($value[0], $value[1], $value[2], $value[3]),
                $value[4]
            );
        }
    }
}
