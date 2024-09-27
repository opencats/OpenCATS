<?php
/**
 * CATS
 * Date Utility Library
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
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
    /**
     * Converts a date from one format to another.
     *
     * @param string $separator The separator used in the date string (e.g., '-', '/')
     * @param string $date The date string to convert
     * @param string $fromFormat The original format of the date (e.g., DATE_FORMAT_MMDDYY)
     * @param string $toFormat The target format for the date
     * @return string The converted date or a fallback value
     */
    public static function convert($separator, $date, $fromFormat, $toFormat)
    {
        /* Extract the three date fields. */
        $dateFields = explode($separator, $date);

        /* Make sure explode() didn't fail. */
        if (count($dateFields) < 3) {
            return ($toFormat == DATE_FORMAT_YYYYMMDD) ? '0000-00-00' : '00-00-00';
        }

        /* Remove leading zeros from fields */
        $dateFields = self::_removeLeadingZeros($dateFields);

        /* Map date fields according to format */
        switch ($fromFormat) {
            case DATE_FORMAT_YYYYMMDD:
                $year = $dateFields[0];
                $month = $dateFields[1];
                $day = $dateFields[2];
                break;
            case DATE_FORMAT_MMDDYY:
                $month = $dateFields[0];
                $day = $dateFields[1];
                $year = $dateFields[2];
                break;
            case DATE_FORMAT_DDMMYY:
                $day = $dateFields[0];
                $month = $dateFields[1];
                $year = $dateFields[2];
                break;
            default:
                return false;
        }

        // Validate the date
        if (!self::validateDate($year, $month, $day)) {
            return false;
        }

        // Use DateTime to handle the conversion and formatting
        try {
            $dateTime = new DateTime("$year-$month-$day");
        } catch (Exception $e) {
            return false;
        }

        /* Convert to the desired format */
        switch ($toFormat) {
            case DATE_FORMAT_YYYYMMDD:
                return $dateTime->format("Y{$separator}m{$separator}d");
            case DATE_FORMAT_MMDDYY:
                return $dateTime->format("m{$separator}d{$separator}y");
            case DATE_FORMAT_DDMMYY:
                return $dateTime->format("d{$separator}m{$separator}y");
            default:
                return false;
        }
    }

    /**
     * Validates a date, checking for valid day, month, and year values.
     *
     * @param int $year The year
     * @param int $month The month
     * @param int $day The day
     * @return bool True if the date is valid, false otherwise
     */
    public static function validateDate($year, $month, $day)
    {
        return checkdate((int) $month, (int) $day, (int) $year);
    }

    /**
     * Returns the number of days in a given month for a specific year.
     *
     * @param int $month The month (1-12)
     * @param int $year The year
     * @return int The number of days in the month
     */
    public static function getDaysInMonth($month, $year)
    {
        return cal_days_in_month(CAL_GREGORIAN, (int) $month, (int) $year);
    }

    /**
     * Returns the name of the month for the given month number.
     *
     * @param int $month The month number (1-12)
     * @return string The month name
     */
    public static function getMonthName($month)
    {
        try {
            $dateTime = DateTime::createFromFormat('!m', $month);
            return $dateTime ? $dateTime->format('F') : '';
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Returns the starting weekday for the given month and year.
     *
     * @param int $month The month number (1-12)
     * @param int $year The year
     * @return int The starting weekday (0 = Sunday, 6 = Saturday)
     */
    public static function getStartingWeekday($month, $year)
    {
        try {
            $dateTime = new DateTime("$year-$month-01");
            return (int) $dateTime->format('w'); // 0 = Sunday, 6 = Saturday
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Validates a date string based on the given format and separator.
     *
     * @param string $separator The separator used in the date string (e.g., '-', '/')
     * @param string $dateString The date string to validate
     * @param int $format The date format (e.g., DATE_FORMAT_MMDDYY)
     * @return bool True if the date is valid, false otherwise
     */
    public static function validate($separator, $dateString, $format)
    {
        // Ensure the string is numeric except for separators.
        if (!ctype_digit(str_replace($separator, '', $dateString))) {
            return false;
        }

        // Ensure we have exactly two separators.
        if (substr_count($dateString, $separator) != 2) {
            return false;
        }

        // Extract the three date fields.
        $dateFields = explode($separator, $dateString);

        // Ensure explode() didn't fail.
        if (count($dateFields) < 3) {
            return false;
        }

        // Check the length of individual date fields and assign them appropriately.
        switch ($format) {
            case DATE_FORMAT_YYYYMMDD:
                if (strlen($dateFields[0]) != 4 || strlen($dateFields[1]) != 2 || strlen($dateFields[2]) != 2) {
                    return false;
                }
                $year = (int) $dateFields[0];
                $month = (int) $dateFields[1];
                $day = (int) $dateFields[2];
                break;

            case DATE_FORMAT_MMDDYY:
            case DATE_FORMAT_DDMMYY:
                if (strlen($dateFields[0]) != 2 || strlen($dateFields[1]) != 2 || strlen($dateFields[2]) != 2) {
                    return false;
                }
                $year = (int) $dateFields[2];
                $month = (int) ($format === DATE_FORMAT_MMDDYY ? $dateFields[0] : $dateFields[1]);
                $day = (int) ($format === DATE_FORMAT_MMDDYY ? $dateFields[1] : $dateFields[0]);
                break;

            default:
                return false;
        }

        // Adjust two-digit years (YY) to four-digit years.
        if (strlen($dateFields[2]) == 2) {
            $year = ($year >= 0 && $year <= 50) ? 2000 + $year : 1900 + $year;
        }

        // Validate that the month, day, and year are within valid ranges.
        if (!checkdate($month, $day, $year)) {
            return false;
        }

        return true;
    }




    /**
     * Removes leading zeros from date fields.
     *
     * @param array $dateFields An array of date fields (e.g., [01, 01, 2022])
     * @return array The date fields with leading zeros removed
     */
    protected static function _removeLeadingZeros($dateFields)
    {
        return array_map('ltrim', $dateFields, array_fill(0, count($dateFields), '0'));
    }
}
