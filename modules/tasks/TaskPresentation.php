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

    /** Fixed Home destinations only; shared by native Task and review forms. */
    public static function homeReturnFields($input)
    {
        $fields = array('homeView' => 'compact', 'homeFilter' => 'all', 'homePage' => '1', 'homeContext' => '');
        foreach ($fields as $field => $default)
        {
            if (!array_key_exists($field, $input)) continue;
            if (!is_string($input[$field])) throw new InvalidArgumentException('Invalid Home return context.');
            $fields[$field] = $input[$field];
        }
        if (!in_array($fields['homeView'], array('compact', 'all'), true) ||
            !in_array($fields['homeFilter'], array('all', 'tasks', 'warnings'), true) ||
            !preg_match('/^[1-9][0-9]{0,8}$/D', $fields['homePage']) ||
            ($fields['homeContext'] !== '' && $fields['homeContext'] !== 'suppressedWarnings' &&
             !preg_match('/^priority-(task-[1-9][0-9]{0,9}|warning-W(01|02|03|07|08)-[1-9][0-9]{0,9}(-(invalid_owner|invalid_recruiter|invalid_assignee))?)$/D', $fields['homeContext'])))
            throw new InvalidArgumentException('Invalid Home return context.');
        return $fields;
    }

    public static function homeReturnURI($fields)
    {
        $fields = self::homeReturnFields($fields);
        return 'm=home&a=' . ($fields['homeView'] === 'all' ? 'priorities' : 'home') .
            '&filter=' . $fields['homeFilter'] . '&page=' . $fields['homePage'] .
            ($fields['homeContext'] === '' ? '' : '#' . $fields['homeContext']);
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
