<?php
/* Task presentation adapters; storage and validation remain in Tasks/DateUtility. */
include_once(LEGACY_ROOT . '/lib/DateUtility.php');

class TaskPresentation
{
    public static function dateFormat()
    {
        return $_SESSION['CATS']->isDateDMY() ? 'DD-MM-YY' : 'MM-DD-YY';
    }

    private static function dateFormatFlag()
    {
        return $_SESSION['CATS']->isDateDMY() ? DATE_FORMAT_DDMMYY : DATE_FORMAT_MMDDYY;
    }

    public static function parseDate($value)
    {
        if ($value === '') return '';
        if (!is_string($value) || !DateUtility::validate('-', $value, self::dateFormatFlag()))
            throw new InvalidArgumentException('Enter a valid date in ' . self::dateFormat() . ' format.');
        return DateUtility::convert('-', $value, self::dateFormatFlag(), DATE_FORMAT_YYYYMMDD);
    }

    public static function date($value)
    {
        if ($value === null || $value === '') return '';
        // Due dates are calendar dates: never apply the session timezone offset.
        return DateUtility::convert('-', $value, DATE_FORMAT_YYYYMMDD, self::dateFormatFlag());
    }

    public static function dateTime($value)
    {
        if ($value === null || $value === '') return '';
        return DateUtility::getAdjustedDate(DateUtility::getDateTimeFormat(
            $_SESSION['CATS']->isDateDMY() ? 'd-m-y' : 'm-d-y'), strtotime($value));
    }

    public static function parentURL($type, $id)
    {
        $parents = array(DATA_ITEM_COMPANY => array('companies', 'companyID'),
            DATA_ITEM_CONTACT => array('contacts', 'contactID'), DATA_ITEM_CANDIDATE => array('candidates', 'candidateID'),
            DATA_ITEM_JOBORDER => array('joborders', 'jobOrderID'));
        if (!isset($parents[$type])) return '';
        return CATSUtility::getIndexName() . '?m=' . $parents[$type][0] . '&a=show&' . $parents[$type][1] . '=' . (int) $id;
    }
}
