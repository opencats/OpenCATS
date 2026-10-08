<?php
/** Controlled Job Order Sectors, using the native DatabaseConnection model. */
class Sectors
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
            throw new InvalidArgumentException('Invalid Sector ID.');
        }
        return (int) $value;
    }

    public function get($id)
    {
        $id = self::normalizeID($id);
        if ($id === null) return array();
        return $this->_db->getAssoc('SELECT sector_id AS sectorID, name, is_active AS isActive FROM sector WHERE sector_id = ' . $id);
    }

    public function getAll($includeInactive = false, $currentID = null)
    {
        $currentID = self::normalizeID($currentID);
        $where = $includeInactive ? '' : ' WHERE is_active = 1' . ($currentID === null ? '' : ' OR sector_id = ' . $currentID);
        return $this->_db->getAllAssoc('SELECT sector_id AS sectorID, name, is_active AS isActive FROM sector' . $where . ' ORDER BY name, sector_id');
    }

    /** Inactive Sectors may be retained on existing records, never newly assigned. */
    public function validateAssignment($value, $currentID = null)
    {
        $id = self::normalizeID($value);
        if ($id === null) return null;
        $sector = $this->get($id);
        if (empty($sector) || (!$sector['isActive'] && $id !== self::normalizeID($currentID)))
        {
            throw new InvalidArgumentException('Choose an available Sector or Unclassified.');
        }
        return $id;
    }

    /** No delete operation: retain definitions and associations for history. */
    public function save($id, $name, $isActive)
    {
        if ($_SESSION['CATS']->getRealAccessLevel() < ACCESS_LEVEL_SA ||
            $_SESSION['CATS']->getAccessLevel('settings.sectors') < ACCESS_LEVEL_SA)
        {
            throw new RuntimeException('Administrator access required.');
        }
        $id = self::normalizeID($id);
        if (!is_string($name) || !mb_check_encoding($name, 'UTF-8') ||
            mb_strlen($name, 'UTF-8') > 64 || preg_match('/[\x00-\x1f\x7f]/', $name) ||
            trim($name) === '' || strcasecmp(trim($name), 'Unclassified') === 0 || !is_bool($isActive))
        {
            throw new InvalidArgumentException('Use a unique Sector name of 1–64 characters, other than Unclassified, without control characters.');
        }
        $name = trim($name);
        if ($id !== null && empty($this->get($id)))
        {
            throw new InvalidArgumentException('Sector not found.');
        }
        $duplicate = $this->_db->getAssoc('SELECT sector_id FROM sector WHERE name = ' . $this->_db->makeQueryString($name) . ($id === null ? '' : ' AND sector_id <> ' . $id));
        if (!empty($duplicate)) throw new InvalidArgumentException('That Sector name is already in use.');
        $values = 'name = ' . $this->_db->makeQueryString($name) . ', is_active = ' . ($isActive ? '1' : '0');
        $sql = $id === null ? 'INSERT INTO sector SET ' . $values : 'UPDATE sector SET ' . $values . ' WHERE sector_id = ' . $id;
        if (!$this->_db->query($sql)) throw new RuntimeException('Unable to save Sector.');
        return $id ?? $this->_db->getLastInsertID();
    }
}
