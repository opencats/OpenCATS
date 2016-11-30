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
class DateUtility
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}


    /**
     * Returns the number of days in the month for the specified month in
     * the specified year.
     *
     * @param flag / integer month
     * @param integer year
     * @return integer days in month
     */
    public static function getDaysInMonth($month, $year)
    {
        return (int) date('t', mktime(0, 0, 0, $month, 1, $year));
    }

    /**
     * Returns the starting weekday flag for the specified month in the
     * specified year.
     *
     * @param flag / integer month
     * @param integer year
     * @return flag / integer starting weekday
     */
    public static function getStartingWeekday($month, $year)
    {
        /* Add 1 to the value returned by date('w', ...); to get the weekday
         * flag value.
         */
        return (int) date('w', mktime(0, 0, 0, $month, 1, $year)) + 1;
    }

    /**
     * Returns the capitalized string name of the month number specified.
     *
     * @param flag / integer month
     * @return string month name
     */
    public static function getMonthName($month)
    {
        return date('F', mktime(0, 0, 0, $month, 1, 2000));
    }

    /**
     * Converts a valid DD-MM-YY date to a MM-DD-YY date.
     *
     * @param string separation character ('-' or '/')
     * @param string date in $fromFormat format
     * @param convert-to integer/flag date format
     * @return string date in $toFormat format
     */
    public static function convert($separator, $date, $fromFormat, $toFormat)
    {
        /* Extract the three date fields. */
        $dateFields = explode($separator, $date);

        /* Make sure explode() didn't fail. */
        if (sizeof($dateFields) < 3)
        {
            if ($toFormat == DATE_FORMAT_YYYYMMDD)
            {
                return '0000-00-00';
            }

            return '00-00-00';
        }

        $dateFields = self::_removeLeadingZeros($dateFields);

        switch ($fromFormat)
        {
            case DATE_FORMAT_YYYYMMDD:
                $year  = $dateFields[0];
                $month = $dateFields[1];
                $day   = $dateFields[2];
                break;

            case DATE_FORMAT_MMDDYY:
                $month = $dateFields[0];
                $day   = $dateFields[1];
                $year  = $dateFields[2];
                break;

            case DATE_FORMAT_DDMMYY:
                $day   = $dateFields[0];
                $month = $dateFields[1];
                $year  = $dateFields[2];
                break;
        }

        switch ($toFormat)
        {
            case DATE_FORMAT_YYYYMMDD:
                $dateFormat = '%Y' . $separator . '%m' . $separator . '%d';
                break;

            case DATE_FORMAT_DDMMYY:
                $dateFormat = '%d' . $separator . '%m' . $separator . '%y';
                break;

            case DATE_FORMAT_MMDDYY:
                $dateFormat = '%m' . $separator . '%d' . $separator . '%y';
                break;
        }

        /* Return the date in the correct format. */
        return strftime($dateFormat, mktime(0, 0, 0, $month, $day, $year));
    }
  

    /**
     * Returns true if the specified date string is a valid date in
     * MM-DD-YY format.
     *
     * @param string separation character ('-' or '/')
     * @param string date string to validate
     * @param integer/flag date format
     * @return boolean valid
     */
    public static function validate($separator, $dateString, $format)
    {
        /* Make sure the string is numeric except for separators. */
        if (!ctype_digit((string) str_replace($separator, '', $dateString)))
        {
            return false;
        }

        /* Make sure we have exactly two separators. */
        if (substr_count($dateString, $separator) != 2)
        {
            return false;
        }

        /* Extract the three date fields. */
        $dateFields = explode($separator, $dateString);

        /* Make sure explode() didn't fail. */
        if (sizeof($dateFields) < 3)
        {
            return false;
        }

        /* Check the length of individual date fields. */
        switch ($format)
        {
            case DATE_FORMAT_YYYYMMDD:
                if (strlen($dateFields[0]) != 4 || strlen($dateFields[1]) != 2 ||
                    strlen($dateFields[2]) != 2)
                {
                    return false;
                }
                break;

            default:
                if (strlen($dateFields[0]) != 2 || strlen($dateFields[1]) != 2 ||
                    strlen($dateFields[2]) != 2)
                {
                    return false;
                }
                break;
        }

        /* Remove leading '0's from fields. */
        $dateFields = self::_removeLeadingZeros($dateFields);

        /* Extract ields. */
        switch ($format)
        {
            case DATE_FORMAT_YYYYMMDD:
                $year  = $dateFields[0];
                $month = $dateFields[1];
                $day   = $dateFields[2];
                break;

            case DATE_FORMAT_MMDDYY:
                $month = $dateFields[0];
                $day   = $dateFields[1];
                $year  = $dateFields[2];
                break;

            case DATE_FORMAT_DDMMYY:
                $day   = $dateFields[0];
                $month = $dateFields[1];
                $year  = $dateFields[2];
                break;
        }

        /* Validate day and month numbers. */
        if ($month < 1 || $month > 12 || $day < 1 ||
            $day > self::getDaysInMonth($month, $year))
        {
            return false;
        }

        return true;
    }

    /**
     * If a date string is equal to '00-00-00', '0000-00-00', '', or
     * '00-00-00 (12:00 AM)', the specified replacement string (or '' if none
     * is specified) is returned. Otherwise the original string is returned.
     *
     * @param string date string to fix
     * @param string zero-date replacement string (optional)
     * @return fixed date string
     */
    public static function fixZeroDate($date, $replacement = '')
    {
        if (empty($date) || $date == '00-00-00' || $date == '0000-00-00' ||
            $date == '00-00-00 (12:00 AM)')
        {
            return $replacement;
        }

        return $date;
    }

    /**
     * Formats a date string from integer values for use in an SQL query
     * (YYYY-MM-DD).
     *
     * @param flag / integer month
     * @param integer day of month
     * @param integer year
     * @return string YYYY-MM-DD
     */
    public function formatSearchDate($month, $day, $year)
    {
        return date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
    }

    /**
     * Calculate the date X days in the past.
     *
     * @param integer start date timestamp
     * @param integer number of days to subtract
     * @return integer calculated past timestamp
     */
    public static function subtractDaysFromDate($startDate, $daysToSubtract)
    {
        return self::addDaysToDate($startDate, ($daysToSubtract * -1));
    }

    /**
     * Calculate the date X days in the future.
     *
     * @param integer start date timestamp
     * @param integer number of days to add
     * @return integer calculated future timestamp
     */
    public static function addDaysToDate($startDate, $daysToAdd)
    {
        return mktime(
            0,
            0,
            0,
            date('m', $startDate),
            date('j', $startDate) + $daysToAdd,
            date('Y', $startDate)
        );
    }

    /**
     * Get the week number of the year for the specified date, with weeks
     * starting on Sunday. If a date is not specified, the current date will
     * be used instead.
     *
     * @param integer date timestamp (optional)
     * @return integer week number
     */
    public static function getWeekNumber($date = false)
    {
        if ($date === false)
        {
            if (isset($_SESSION['CATS']) && $_SESSION['CATS']->isLoggedIn())
            {
                $timeZoneOffset = $_SESSION['CATS']->getTimeZoneOffset();
                $date = mktime(
                    date('H') + $timeZoneOffset,
                    date('i'),
                    date('s'),
                    date('m'),
                    date('d'),
                    date('Y')
                );
            }
            else
            {
                $date = time();
            }
        }

        /* To calculate the week number starting on Sunday instead of Monday,
         * find the starting-on-Monday week number of the day after the
         * specified date instead.
         */
        return date('W', self::addDaysToDate($date, 1));
    }

    /**
     * Converts UNIXTime into an RSS format date. These dates are identical
     * to RFC2822 format, with the exception of the time zone identifier. This
     * chops off the numeric time zone identifier and replaces it with the
     * string time zone identifier.
     *
     * @param integer UNIX time (optional)
     * @return string RSS format date
     */
    public function getRSSDate($unixTime = false)
    {
        if ($unixTime === false)
        {
            $unixTime = time();
        }

        $RFC2822  = date('r', $unixTime);
        $timeZone = date('T', $unixTime);

        return preg_replace(
            '/[+-][0-9]{4}\s*$/', $timeZone, $RFC2822
        );
    }

    /**
     * Gets the current time for a site if logged in, or the system time if
     * not logged in.
     *
     * @param integer UNIX time (optional)
     * @return integer UNIX time
     */
    public static function getAdjustedDate($format = 'U', $date = false)
    {
        if ($date === false)
        {
            $date = time();
        }

        $unixTime = mktime(
            date('H', $date) + $_SESSION['CATS']->getTimeZoneOffset(),
            date('i', $date),
            date('s', $date),
            date('m', $date),
            date('d', $date),
            date('Y', $date)
        );

        return date($format, $unixTime);
    }
    
    /**
     * Returns a human readable representation of a period of time in seconds.
     *
     * @param integer number of seconds
     * @param boolean short time representation (yr instead of year)
     * @param boolean round results?
     * @return string human readable time
     */
    public static function getFormattedDuration($seconds, $short = false, $round = false)
    {
        $abbreviations = array(
            'year'   => 'yr',
            'week'   => 'wk',
            'day'    => 'day',
            'hour'   => 'hr',
            'minute' => 'min',
            'second' => 'sec'
        );
    
        $periods = array(
            'year'   => (60 * 60 * 24 * 365),
            'week'   => (60 * 60 * 24 * 7),
            'day'    => (60 * 60 * 24),
            'hour'   => (60 * 60),
            'minute' => (60),
            'second' => (1)
        );
    
        $seconds = (float) $seconds;
        foreach ($periods as $period => $divisor)
        {   
            $value = floor($seconds / $divisor);
            if ($value == 0)
            {
                continue;
            }
        
            $seconds = ($seconds % $divisor);
        
            if ($short)
            {
                $segment = $value . $abbreviations[$period];
            }
            else
            {
                $segment = $value . ' ' . $period;
            }

            if ($value != 1)
            {
                $segment .= 's';
            }

            $segments[] = $segment;
        
            if ($round === $period)
            {
                break;
            }
        }
    
        if ($short)
        {
            return implode(' ', $segments);
        }
    
        return implode(', ', $segments);
    }
    
    /**
     * Returns a human readable representation of a UNIX date.
     *
     * @param integer unix timestamp
     * @param integer token date format (check constants.php)
     * @return string human readable date
     */
    public function getFormattedDate($unixTime, $format)
    {
        switch ($format)
        {
            case DATE_FORMAT_YYYYMMDD:
                return strftime('%Y-%m-%d', $unixTime);
                break;

            case DATE_FORMAT_MMDDYY:
                return strftime('%m-%d-%y', $unixTime);
                break;

            case DATE_FORMAT_DDMMYY:
                return strftime('%d-%m-%y', $unixTime);
                break;

            case DATE_FORMAT_SECONDS:
                /* Ensure that we are returning JUST the datestamp,
                 * not the time.
                 */
                return mktime(
                    0,
                    0,
                    0,
                    date('m', $unixTime),
                    date('j', $unixTime),
                    date('Y', $unixTime)
                );
                break;
        }
        
        return false;
    }

    /**
     * Returns a human readable representation of a period of time relative
     * to now based on predetermined constants (check constants.php)
     *
     * @param integer token period of time
     * @param integer token date format
     * @return array (startDate => formatted start date, endDate => formatted end date)
     */
    public function getPeriodDateRange($period, $dateFormat = false)
    {
        if ($dateFormat === false)
        {
            $dateFormat = DATE_FORMAT_YYYYMMDD;
        }
        
        $currentUnixTime = time();
        $currentDay      = date('j', $currentUnixTime);
        $currentMonth    = date('m', $currentUnixTime);
        $currentYear     = date('Y', $currentUnixTime);
        
        switch ($period)
        {
            case TIME_PERIOD_TODAY:
                $startDate = self::getFormattedDate($currentUnixTime, $dateFormat);
                $endDate   = $startDate;
                break;

            case TIME_PERIOD_YESTERDAY:
                $startUnixTime = mktime(
                    0,
                    0,
                    0,
                    date('m', $currentUnixTime),
                    date('j', $currentUnixTime) - 1,
                    date('Y', $currentUnixTime)
                );
                
                $startDate = self::getFormattedDate($startUnixTime, $dateFormat);
                $endDate   = $startDate;
                break;

            case TIME_PERIOD_THISWEEK:
                $currentWeekday = date('w', $currentUnixTime);
                $startUnixTime = mktime(
                    0,
                    0,
                    0,
                    date('m', $currentUnixTime),
                    date('j', $currentUnixTime) - $currentWeekday,
                    date('Y', $currentUnixTime)
                );
                
                $endUnixTime = mktime(
                    0,
                    0,
                    0,
                    date('m', $currentUnixTime),
                    date('j', $currentUnixTime) - $currentWeekday + 6,
                    date('Y', $currentUnixTime)
                );
                
                $startDate = self::getFormattedDate($startUnixTime, $dateFormat);
                $endDate   = self::getFormattedDate($endUnixTime, $dateFormat);
                break;

            case TIME_PERIOD_LASTWEEK:
                $currentWeekday = date('w', $currentUnixTime);
                $startUnixTime = mktime(
                    0,
                    0,
                    0,
                    date('m', $currentUnixTime),
                    date('j', $currentUnixTime) - $currentWeekday - 7,
                    date('Y', $currentUnixTime)
                );
                
                $endUnixTime = mktime(
                    0,
                    0,
                    0,
                    date('m', $currentUnixTime),
                    date('j', $currentUnixTime) - $currentWeekday - 1,
                    date('Y', $currentUnixTime)
                );
                
                $startDate = self::getFormattedDate($startUnixTime, $dateFormat);
                $endDate   = self::getFormattedDate($endUnixTime, $dateFormat);
                break;

            case TIME_PERIOD_LASTTWOWEEKS:
                $currentWeekday = date('w', $currentUnixTime);
                $startUnixTime = mktime(
                    0,
                    0,
                    0,
                    date('m', $currentUnixTime),
                    date('j', $currentUnixTime) - $currentWeekday - 7,
                    date('Y', $currentUnixTime)
                );
                
                $endUnixTime = mktime(
                    0,
                    0,
                    0,
                    date('m', $currentUnixTime),
                    date('j', $currentUnixTime) - $currentWeekday + 6,
                    date('Y', $currentUnixTime)
                );
                
                $startDate = self::getFormattedDate($startUnixTime, $dateFormat);
                $endDate   = self::getFormattedDate($endUnixTime, $dateFormat);
                break;

            case TIME_PERIOD_THISMONTH:
                $startUnixTime = mktime(
                    0,
                    0,
                    0,
                    date('m', $currentUnixTime),
                    1,
                    date('Y', $currentUnixTime)
                );
                
                $lastDayOfMonth = self::getDaysInMonth(
                    $currentMonth,
                    $currentYear
                );
                $endUnixTime = mktime(
                    0,
                    0,
                    0,
                    date('m', $currentUnixTime),
                    $lastDayOfMonth,
                    date('Y', $currentUnixTime)
                );
                
                $startDate = self::getFormattedDate($startUnixTime, $dateFormat);
                $endDate   = self::getFormattedDate($endUnixTime, $dateFormat);
                break;

            case TIME_PERIOD_LASTMONTH:
                /* The 1st of 1 month ago. */
                $startUnixTime = mktime(
                    0,
                    0,
                    0,
                    date('m', $currentUnixTime) - 1,
                    1,
                    date('Y', $currentUnixTime)
                );
                
                /* The last day of 1 month ago. */
                $lastDayOfMonth = self::getDaysInMonth(
                    date('m', $startUnixTime),
                    date('Y', $startUnixTime)
                );
                $endUnixTime = mktime(
                    0,
                    0,
                    0,
                    date('m', $currentUnixTime) - 1,
                    $lastDayOfMonth,
                    date('Y', $currentUnixTime)
                );
                
                $startDate = self::getFormattedDate($startUnixTime, $dateFormat);
                $endDate   = self::getFormattedDate($endUnixTime, $dateFormat);
                break;

            case TIME_PERIOD_THISYEAR:
                /* January 1st of the current year. */
                $startUnixTime = mktime(
                    0,
                    0,
                    0,
                    1,
                    1,
                    date('Y', $currentUnixTime)
                );
                
                /* December 31st of the current year. */
                $endUnixTime = mktime(
                    0,
                    0,
                    0,
                    12,
                    31,
                    date('Y', $currentUnixTime)
                );
                
                $startDate = self::getFormattedDate($startUnixTime, $dateFormat);
                $endDate   = self::getFormattedDate($endUnixTime, $dateFormat);
                break;

            case TIME_PERIOD_LASTYEAR:
                /* January 1st of the previous year. */
                $startUnixTime = mktime(
                    0,
                    0,
                    0,
                    1,
                    1,
                    date('Y', $currentUnixTime) - 1
                );
                
                /* December 31st of the previous year. */
                $endUnixTime = mktime(
                    0,
                    0,
                    0,
                    12,
                    31,
                    date('Y', $currentUnixTime) - 1
                );
                
                $startDate = self::getFormattedDate($startUnixTime, $dateFormat);
                $endDate   = self::getFormattedDate($endUnixTime, $dateFormat);
                break;

            case TIME_PERIOD_TODATE:
            default:
                $startDate = false;
                $endDate   = false;
                break;
        }
        
        return array(
            'startDate' => $startDate,
            'endDate'   => $endDate
        );
    }

    private static function _removeLeadingZeros($array)
    {
        foreach ($array as $key => $value)
        {
            /* Remove leading '0's from fields. */
            if ($array[$key]{0} == '0')
            {
                $array[$key] = substr($array[$key], 1);
            }
        }

        return $array;
    }
}
?>
