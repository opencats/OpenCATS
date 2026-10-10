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

    public static function getAttentionDefaults()
    {
        return array('tiers' => array(
            'A' => array('enabled' => true, 'days' => 14),
            'B' => array('enabled' => true, 'days' => 30),
            'C' => array('enabled' => true, 'days' => 60),
            'D' => array('enabled' => false, 'days' => null),
            'Unclassified' => array('enabled' => false, 'days' => null)),
            'lifecycles' => array('Fresh Prospect' => true, 'Prospect' => true,
                'Engaged' => true, 'Client' => true, 'Dormant' => false,
                'Lost' => false, 'Unclassified' => false));
    }

    /** Read on each evaluation: changing policy never rewrites operational data. */
    public function getAttention()
    {
        $values = self::getAttentionDefaults();
        $rows = $this->_db->getAllAssoc('SELECT setting, value FROM settings WHERE settings_type = ' . SETTINGS_COMPANY);
        $saved = array_column($rows, 'value', 'setting');
        foreach ($values as $group => &$entries)
            foreach ($entries as $code => &$value)
            {
                $key = 'attention.' . $group . '.' . $code;
                if (array_key_exists($key, $saved)) $value = json_decode($saved[$key], true);
            }
        unset($entries, $value);
        return self::validateAttention($values);
    }

    private static function attentionFlag($value)
    {
        if (!in_array($value, array(true, false, 1, 0, '1', '0'), true))
            throw new InvalidArgumentException('Monitoring eligibility must be enabled or disabled.');
        return (bool) $value;
    }

    public static function validateAttention($values)
    {
        $defaults = self::getAttentionDefaults();
        if (!is_array($values) || count($values) !== 2 ||
            !isset($values['tiers'], $values['lifecycles']))
            throw new InvalidArgumentException('Supply tier and lifecycle monitoring settings.');
        foreach ($defaults as $group => $expected)
        {
            if (!is_array($values[$group]) || count($values[$group]) !== count($expected) ||
                array_diff_key($expected, $values[$group]))
                throw new InvalidArgumentException('Supply every recognised tier and lifecycle.');
        }
        foreach ($values['tiers'] as $code => &$tier)
        {
            if (!is_array($tier) || count($tier) !== 2 ||
                !array_key_exists('enabled', $tier) || !array_key_exists('days', $tier))
                throw new InvalidArgumentException('Supply monitoring eligibility and review days for every tier.');
            $tier['enabled'] = self::attentionFlag($tier['enabled']);
            if (!$tier['enabled'] && ($tier['days'] === null || $tier['days'] === ''))
            {
                $tier['days'] = null;
                continue;
            }
            if ((!is_int($tier['days']) && !is_string($tier['days'])) ||
                !preg_match('/^[1-9][0-9]{0,4}$/D', (string) $tier['days']) || $tier['days'] > 36500)
                throw new InvalidArgumentException('Enabled tiers require 1 to 36500 calendar days.');
            $tier['days'] = (int) $tier['days'];
        }
        unset($tier);
        foreach ($values['lifecycles'] as &$enabled) $enabled = self::attentionFlag($enabled);
        unset($enabled);
        return $values;
    }

    public function setAttention($values)
    {
        if (!isset($_SESSION['CATS']) || !$_SESSION['CATS']->isLoggedIn() ||
            $_SESSION['CATS']->getRealAccessLevel() < ACCESS_LEVEL_SA ||
            $_SESSION['CATS']->getAccessLevel('settings.relationshipAttention') < ACCESS_LEVEL_SA)
            throw new RuntimeException('Administrator access required.');
        $values = self::validateAttention($values);
        if (!$this->_db->beginTransaction()) throw new RuntimeException('Unable to start attention settings update.');
        try
        {
            foreach ($values as $group => $entries)
                foreach ($entries as $code => $value)
                {
                    $key = $this->_db->makeQueryString('attention.' . $group . '.' . $code);
                    if (!$this->_db->query('DELETE FROM settings WHERE settings_type = ' .
                        SETTINGS_COMPANY . ' AND setting = ' . $key) ||
                        !$this->_db->query('INSERT INTO settings (settings_type, setting, value) VALUES (' .
                        SETTINGS_COMPANY . ', ' . $key . ', ' . $this->_db->makeQueryString(json_encode($value)) . ')'))
                        throw new RuntimeException('Unable to save attention settings.');
                }
            $this->_db->commitTransaction();
        }
        catch (Throwable $e)
        {
            $this->_db->rollbackTransaction();
            throw $e;
        }
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
