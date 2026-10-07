<?php
/** Company tier labels, using the native settings table like CalendarSettings. */
class CompanySettings
{
    private $_db;

    public function __construct()
    {
        $this->_db = DatabaseConnection::getInstance();
    }

    public function getAll()
    {
        $labels = array('A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D');
        $rows = $this->_db->getAllAssoc('SELECT setting, value FROM settings WHERE settings_type = ' . SETTINGS_COMPANY);
        foreach ($rows as $row)
        {
            if (isset($labels[$row['setting']]) && is_string($row['value']) && trim($row['value']) !== '')
            {
                $labels[$row['setting']] = trim($row['value']);
            }
        }
        return $labels;
    }

    public static function formatTier($code, $labels)
    {
        if ($code === null || $code === '')
        {
            return 'Unclassified';
        }
        $label = $labels[$code] ?? $code;
        return $label === $code ? $code : $code . ' — ' . $label;
    }

    public function setAll($labels)
    {
        if ($_SESSION['CATS']->getRealAccessLevel() < ACCESS_LEVEL_SA ||
            $_SESSION['CATS']->getAccessLevel('settings.companyClassification') < ACCESS_LEVEL_SA)
        {
            throw new RuntimeException('Administrator access required.');
        }
        if (!is_array($labels) || count($labels) !== 4)
        {
            throw new InvalidArgumentException('Supply one label for each of A, B, C and D.');
        }
        foreach (array('A', 'B', 'C', 'D') as $code)
        {
            if (!isset($labels[$code]) || !is_string($labels[$code]) ||
                !mb_check_encoding($labels[$code], 'UTF-8') || mb_strlen($labels[$code], 'UTF-8') > 80 ||
                preg_match('/[\x00-\x1f\x7f]/', $labels[$code]))
            {
                throw new InvalidArgumentException('Tier labels must be plain text of at most 80 characters, without control characters.');
            }
            $labels[$code] = trim($labels[$code]);
        }
        /* Native delete/insert convention, with all four labels saved together. */
        if (!$this->_db->beginTransaction())
        {
            throw new RuntimeException('Unable to start Company label update.');
        }
        try
        {
            foreach ($labels as $code => $label)
            {
                if (!$this->_db->query('DELETE FROM settings WHERE settings_type = ' . SETTINGS_COMPANY .
                    ' AND setting = ' . $this->_db->makeQueryString($code)) ||
                    !$this->_db->query('INSERT INTO settings (settings_type, setting, value) VALUES (' .
                    SETTINGS_COMPANY . ', ' . $this->_db->makeQueryString($code) . ', ' .
                    $this->_db->makeQueryString($label) . ')'))
                {
                    throw new RuntimeException('Unable to save Company tier labels.');
                }
            }
            $this->_db->commitTransaction();
        }
        catch (Throwable $e)
        {
            $this->_db->rollbackTransaction();
            throw $e;
        }
    }
}
