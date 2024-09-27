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
    public function testGetDaysInMonth()
    {
        // Test March 2006
        $days = DateUtility::getDaysInMonth(CALENDAR_MONTH_MARCH, 2006);
        var_dump("Testing March 2006: Month: " . CALENDAR_MONTH_MARCH . ", Year: 2006, Days Returned: " . $days);
        $this->assertSame(
            $days,
            31,
            "Expected 31 days for March 2006, got $days"
        );

        // Test March 1987
        $days1987 = DateUtility::getDaysInMonth(CALENDAR_MONTH_MARCH, 1987);
        $days2006 = DateUtility::getDaysInMonth(CALENDAR_MONTH_MARCH, 2006);
        var_dump("Testing March 1987: Month: " . CALENDAR_MONTH_MARCH . ", Year: 1987, Days Returned: " . $days1987);
        $this->assertSame(
            $days1987,
            $days2006,
            "Expected the same number of days for March 1987 and March 2006, got $days1987 and $days2006"
        );

        // Test April 1987
        $daysApril = DateUtility::getDaysInMonth(CALENDAR_MONTH_APRIL, 1987);
        var_dump("Testing April 1987: Month: " . CALENDAR_MONTH_APRIL . ", Year: 1987, Days Returned: " . $daysApril);
        $this->assertSame(
            $daysApril,
            30,
            "Expected 30 days for April 1987, got $daysApril"
        );

        // Leap year tests with February
        $daysFebLeapYear = DateUtility::getDaysInMonth(CALENDAR_MONTH_FEBRUARY, 2008);
        var_dump("Testing February 2008 (Leap Year): Month: " . CALENDAR_MONTH_FEBRUARY . ", Year: 2008, Days Returned: " . $daysFebLeapYear);
        $this->assertSame(
            $daysFebLeapYear,
            29,
            "Expected 29 days for February 2008 (Leap Year), got $daysFebLeapYear"
        );

        $daysFebNonLeapYear = DateUtility::getDaysInMonth(CALENDAR_MONTH_FEBRUARY, 2006);
        var_dump("Testing February 2006 (Non-Leap Year): Month: " . CALENDAR_MONTH_FEBRUARY . ", Year: 2006, Days Returned: " . $daysFebNonLeapYear);
        $this->assertSame(
            $daysFebNonLeapYear,
            28,
            "Expected 28 days for February 2006 (Non-Leap Year), got $daysFebNonLeapYear"
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
