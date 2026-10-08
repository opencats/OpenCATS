<?php
/** Controlled recruitment Desks, using the native DatabaseConnection model. */
class Desks
{
    private $_db;

    public function __construct()
    {
        $this->_db = DatabaseConnection::getInstance();
    }

    public static function normalizeID($value)
    {
        if ($value === null || $value === '') return null;
        if ((!is_int($value) && !is_string($value)) ||
            !preg_match('/^[1-9][0-9]*$/D', (string) $value) ||
            strlen((string) $value) > 10 || (float) $value > 2147483647)
        {
            throw new InvalidArgumentException('Invalid Desk ID.');
        }
        return (int) $value;
    }

    public function get($id)
    {
        $id = self::normalizeID($id);
        if ($id === null) return array();
        return $this->_db->getAssoc('SELECT desk_id AS deskID, name, is_active AS isActive FROM desk WHERE desk_id = ' . $id);
    }

    public function getAll($includeInactive = false, $currentID = null)
    {
        $currentID = self::normalizeID($currentID);
        $where = $includeInactive ? '' : ' WHERE is_active = 1' . ($currentID === null ? '' : ' OR desk_id = ' . $currentID);
        return $this->_db->getAllAssoc('SELECT desk_id AS deskID, name, is_active AS isActive FROM desk' . $where . ' ORDER BY name, desk_id');
    }

    /** Inactive Desks may be retained on existing records, never newly assigned. */
    public function validateAssignment($value, $currentID = null)
    {
        $id = self::normalizeID($value);
        if ($id === null) return null;
        $desk = $this->get($id);
        if (empty($desk) || (!$desk['isActive'] && $id !== self::normalizeID($currentID)))
        {
            throw new InvalidArgumentException('Choose an available Desk or Unassigned.');
        }
        return $id;
    }

    public function getDefaultForUser($userID)
    {
        if (in_array($userID, array(0, -1, '0', '-1'), true)) return null;
        $userID = self::normalizeID($userID);
        if ($userID === null) return null;
        $row = $this->_db->getAssoc('SELECT desk.desk_id AS deskID FROM user INNER JOIN desk ON user.desk_id = desk.desk_id AND desk.is_active = 1 WHERE user.user_id = ' . $userID);
        return empty($row) ? null : (int) $row['deskID'];
    }

    /** No delete operation: retain definitions and associations for history. */
    public function save($id, $name, $isActive)
    {
        if ($_SESSION['CATS']->getRealAccessLevel() < ACCESS_LEVEL_SA ||
            $_SESSION['CATS']->getAccessLevel('settings.desks') < ACCESS_LEVEL_SA)
        {
            throw new RuntimeException('Administrator access required.');
        }
        $id = self::normalizeID($id);
        if (!is_string($name) || !mb_check_encoding($name, 'UTF-8') ||
            mb_strlen($name, 'UTF-8') > 64 || preg_match('/[\x00-\x1f\x7f]/', $name) ||
            trim($name) === '' || strcasecmp(trim($name), 'Unassigned') === 0 || !is_bool($isActive))
        {
            throw new InvalidArgumentException('Use a unique Desk name of 1–64 characters, other than Unassigned, without control characters.');
        }
        $name = trim($name);
        if ($id !== null && empty($this->get($id)))
        {
            throw new InvalidArgumentException('Desk not found.');
        }
        $duplicate = $this->_db->getAssoc('SELECT desk_id FROM desk WHERE name = ' . $this->_db->makeQueryString($name) . ($id === null ? '' : ' AND desk_id <> ' . $id));
        if (!empty($duplicate)) throw new InvalidArgumentException('That Desk name is already in use.');
        $values = 'name = ' . $this->_db->makeQueryString($name) . ', is_active = ' . ($isActive ? '1' : '0');
        $sql = $id === null ? 'INSERT INTO desk SET ' . $values : 'UPDATE desk SET ' . $values . ' WHERE desk_id = ' . $id;
        if (!$this->_db->query($sql)) throw new RuntimeException('Unable to save Desk.');
        return $id ?? $this->_db->getLastInsertID();
    }
}
