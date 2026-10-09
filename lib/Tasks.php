<?php
/** Native personal and record-linked Tasks. Public operations enforce session scope. */
include_once(LEGACY_ROOT . '/lib/History.php');
include_once(LEGACY_ROOT . '/lib/CandidateAuthorization.php');
include_once(LEGACY_ROOT . '/lib/Companies.php');
include_once(LEGACY_ROOT . '/lib/Contacts.php');
include_once(LEGACY_ROOT . '/lib/JobOrders.php');
include_once(LEGACY_ROOT . '/lib/Pipelines.php');
include_once(LEGACY_ROOT . '/lib/Users.php');

class Tasks
{
    private $_db;

    public function __construct()
    {
        $this->_db = DatabaseConnection::getInstance();
    }

    public static function getStatuses()
    {
        return array('open' => 'Open', 'in_progress' => 'In Progress',
            'completed' => 'Completed', 'cancelled' => 'Cancelled');
    }

    public static function getPriorities()
    {
        return array('low' => 'Low', 'normal' => 'Normal', 'high' => 'High');
    }

    public static function getPurposes()
    {
        return array('general' => 'General', 'relationship_follow_up' => 'Relationship follow-up',
            'candidate_update' => 'Candidate update', 'job_follow_up' => 'Job follow-up',
            'submission_feedback' => 'Submission feedback', 'interview_feedback' => 'Interview feedback',
            'administrative' => 'Administrative');
    }

    private static function normalizeID($value, $nullable = true)
    {
        if ($nullable && ($value === null || $value === '')) return null;
        if ((!is_int($value) && !is_string($value)) ||
            !preg_match('/^[1-9][0-9]*$/D', (string) $value) ||
            strlen((string) $value) > 10 || (float) $value > 2147483647)
        {
            throw new InvalidArgumentException('Invalid Task or association ID.');
        }
        return (int) $value;
    }

    private function hasAccess($action, $level)
    {
        return isset($_SESSION['CATS']) && $_SESSION['CATS']->isLoggedIn() &&
            $_SESSION['CATS']->getUserID() > 0 &&
            $_SESSION['CATS']->getAccessLevel($action) >= $level;
    }

    private function isAdministrator()
    {
        return $this->hasAccess('tasks.admin', ACCESS_LEVEL_SA) &&
            $_SESSION['CATS']->getRealAccessLevel() >= ACCESS_LEVEL_SA;
    }

    private function parentModule($type)
    {
        return array(DATA_ITEM_COMPANY => 'companies', DATA_ITEM_CONTACT => 'contacts',
            DATA_ITEM_CANDIDATE => 'candidates', DATA_ITEM_JOBORDER => 'joborders')[$type] ?? null;
    }

    private function canAccessParent($type, $id, $edit = false)
    {
        $module = $this->parentModule($type);
        if ($module === null || $id === null || !$this->hasAccess($module . '.show', ACCESS_LEVEL_READ) ||
            ($edit && !$this->hasAccess($module . '.edit', ACCESS_LEVEL_EDIT))) return false;

        switch ($type)
        {
            case DATA_ITEM_CANDIDATE:
                return CandidateAuthorization::canAccessCandidate($id);
            case DATA_ITEM_JOBORDER:
                $record = (new JobOrders())->get($id);
                return !empty($record) && ($record['isAdminHidden'] != 1 ||
                    $this->hasAccess('joborders.hidden', ACCESS_LEVEL_SA));
            case DATA_ITEM_COMPANY:
                return !empty((new Companies())->get($id));
            case DATA_ITEM_CONTACT:
                return !empty((new Contacts())->get($id));
        }
        return false;
    }

    private function canRead($record)
    {
        if (empty($record) || !$this->hasAccess('tasks.show', ACCESS_LEVEL_READ)) return false;
        if ($record['dataItemType'] === null)
        {
            $actor = $_SESSION['CATS']->getUserID();
            if ($record['dataItemID'] !== null || ($record['createdBy'] != $actor &&
                $record['assignedTo'] != $actor && !$this->isAdministrator())) return false;
        }
        elseif (!$this->canAccessParent($record['dataItemType'], $record['dataItemID'])) return false;
        return $record['candidateJobOrderID'] === null ||
            (new Pipelines())->canAccess($record['candidateJobOrderID']);
    }

    private function selectSQL()
    {
        return 'SELECT task_id AS taskID, title, description, due_date AS dueDate,
            priority, status, purpose, assigned_to AS assignedTo,
            data_item_type AS dataItemType, data_item_id AS dataItemID,
            candidate_joborder_id AS candidateJobOrderID, created_by AS createdBy,
            date_created AS dateCreated, date_modified AS dateModified,
            completed_by AS completedBy, date_completed AS dateCompleted FROM task';
    }

    /** Missing and inaccessible IDs have the same result. */
    public function get($id)
    {
        $id = self::normalizeID($id, false);
        if (!$this->hasAccess('tasks.show', ACCESS_LEVEL_READ)) return array();
        $record = $this->_db->getAssoc($this->selectSQL() . ' WHERE task_id = ' . $id);
        return $this->canRead($record) ? $record : array();
    }

    /**
     * Filters: assignedTo (including NULL), dataItemType + dataItemID, status,
     * priority, purpose and openOnly. Authorise before returning rows or counts;
     * a future DataGrid must not paginate the unscoped intermediate result.
     */
    public function getAll($filters = array())
    {
        if (!is_array($filters)) throw new InvalidArgumentException('Invalid Task filters.');
        $where = array('1 = 1');
        foreach ($filters as $key => $value)
        {
            switch ($key)
            {
                case 'assignedTo':
                case 'dataItemID':
                case 'dataItemType':
                    $column = array('assignedTo' => 'assigned_to', 'dataItemID' => 'data_item_id', 'dataItemType' => 'data_item_type')[$key];
                    $value = self::normalizeID($value);
                    if ($key === 'dataItemType' && $value !== null && $this->parentModule($value) === null)
                        throw new InvalidArgumentException('Invalid Task parent type.');
                    $where[] = $column . ($value === null ? ' IS NULL' : ' = ' . $value);
                    break;
                case 'status':
                case 'priority':
                case 'purpose':
                    $choices = array('status' => self::getStatuses(), 'priority' => self::getPriorities(), 'purpose' => self::getPurposes());
                    if (!is_string($value) || !isset($choices[$key][$value])) throw new InvalidArgumentException('Invalid Task filter.');
                    $where[] = $key . ' = ' . $this->_db->makeQueryString($value);
                    break;
                case 'openOnly':
                    if (!is_bool($value)) throw new InvalidArgumentException('Invalid open-work filter.');
                    if ($value) $where[] = "status IN ('open', 'in_progress')";
                    break;
                default:
                    throw new InvalidArgumentException('Unknown Task filter.');
            }
        }
        if (array_key_exists('dataItemID', $filters) !== array_key_exists('dataItemType', $filters) ||
            (array_key_exists('dataItemID', $filters) &&
             (self::normalizeID($filters['dataItemID']) === null) !== (self::normalizeID($filters['dataItemType']) === null)))
            throw new InvalidArgumentException('Specify both Task parent type and ID.');
        if (!$this->hasAccess('tasks.list', ACCESS_LEVEL_READ) || !$this->hasAccess('tasks.show', ACCESS_LEVEL_READ)) return array();
        $rows = $this->_db->getAllAssoc($this->selectSQL() . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY due_date IS NULL, due_date, task_id');
        return array_values(array_filter($rows, fn($row) => $this->canRead($row)));
    }

    public function getCount($filters = array())
    {
        return count($this->getAll($filters));
    }

    private function validate($values, $current = array())
    {
        $defaults = array('title' => '', 'description' => null, 'dueDate' => null,
            'priority' => 'normal', 'status' => 'open', 'purpose' => 'general',
            'assignedTo' => null, 'dataItemType' => null, 'dataItemID' => null,
            'candidateJobOrderID' => null);
        if (!is_array($values) || array_diff_key($values, $defaults)) throw new InvalidArgumentException('Unknown Task fields.');
        $record = array_replace($defaults, array_intersect_key($current, $defaults), $values);
        if (!is_string($record['title']) || !mb_check_encoding($record['title'], 'UTF-8') ||
            trim($record['title']) === '' || mb_strlen($record['title'], 'UTF-8') > 255 ||
            preg_match('/[\x00-\x1f\x7f]/', $record['title'])) throw new InvalidArgumentException('Task title must contain 1–255 characters without control characters.');
        $record['title'] = trim($record['title']);
        if ($record['description'] !== null && (!is_string($record['description']) ||
            !mb_check_encoding($record['description'], 'UTF-8') || strlen($record['description']) > 65535 ||
            preg_match('/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/', $record['description']))) throw new InvalidArgumentException('Invalid Task description.');
        foreach (array('status' => self::getStatuses(), 'priority' => self::getPriorities(), 'purpose' => self::getPurposes()) as $field => $choices)
        {
            if (!is_string($record[$field]) || !isset($choices[$record[$field]])) throw new InvalidArgumentException('Invalid Task ' . $field . '.');
        }
        if ($record['dueDate'] === '') $record['dueDate'] = null;
        if ($record['dueDate'] !== null && (!is_string($record['dueDate']) ||
            !preg_match('/^([1-9][0-9]{3})-([0-9]{2})-([0-9]{2})$/D', $record['dueDate'], $date) ||
            !checkdate((int) $date[2], (int) $date[3], (int) $date[1]))) throw new InvalidArgumentException('Use a valid YYYY-MM-DD due date or NULL.');
        foreach (array('assignedTo', 'dataItemType', 'dataItemID', 'candidateJobOrderID') as $field)
            $record[$field] = self::normalizeID($record[$field]);
        if (($record['dataItemType'] === null) !== ($record['dataItemID'] === null) ||
            ($record['dataItemType'] !== null && $this->parentModule($record['dataItemType']) === null)) throw new InvalidArgumentException('Invalid Task parent.');
        if ($record['dataItemType'] === null && $record['assignedTo'] === null) throw new InvalidArgumentException('Standalone Tasks require an assignee.');
        if ($record['assignedTo'] !== null && (empty($current) || $record['assignedTo'] != $current['assignedTo']))
        {
            $user = (new Users())->get($record['assignedTo']);
            if (empty($user) || $user['accessLevel'] <= ACCESS_LEVEL_DISABLED) throw new InvalidArgumentException('Choose an active Task assignee.');
        }
        return $record;
    }

    private function authorizeAssociations($record, $editParent)
    {
        if ($record['dataItemType'] !== null && !$this->canAccessParent($record['dataItemType'], $record['dataItemID'], $editParent))
            throw new RuntimeException('Task association unavailable.');
        if ($record['candidateJobOrderID'] !== null)
        {
            if (!(new Pipelines())->canAccess($record['candidateJobOrderID'])) throw new RuntimeException('Task association unavailable.');
            $context = $this->_db->getAssoc('SELECT candidate_id, joborder_id FROM candidate_joborder WHERE candidate_joborder_id = ' . $record['candidateJobOrderID']);
            if (($record['dataItemType'] == DATA_ITEM_CANDIDATE && $record['dataItemID'] != $context['candidate_id']) ||
                ($record['dataItemType'] == DATA_ITEM_JOBORDER && $record['dataItemID'] != $context['joborder_id']))
                throw new InvalidArgumentException('Pipeline context must match the Task parent.');
        }
    }

    private function valuesSQL($record)
    {
        $columns = array('title' => 'title', 'description' => 'description', 'dueDate' => 'due_date',
            'priority' => 'priority', 'status' => 'status', 'purpose' => 'purpose',
            'assignedTo' => 'assigned_to', 'dataItemType' => 'data_item_type', 'dataItemID' => 'data_item_id',
            'candidateJobOrderID' => 'candidate_joborder_id');
        $values = array();
        foreach ($columns as $field => $column)
            $values[] = $column . ' = ' . ($record[$field] === null ? 'NULL' : $this->_db->makeQueryString((string) $record[$field]));
        return implode(', ', $values);
    }

    private function completionSQL($status)
    {
        return $status === 'completed' ? ', completed_by = ' . (int) $_SESSION['CATS']->getUserID() . ', date_completed = NOW()' : ', completed_by = NULL, date_completed = NULL';
    }

    private function authorizeTransition($from, $to)
    {
        if ($from === $to) return;
        $closed = in_array($from, array('completed', 'cancelled'), true);
        if ($closed && $to !== 'open') throw new InvalidArgumentException('Reopen closed Tasks to Open.');
        $action = $closed ? 'reopen' : ($to === 'completed' ? 'complete' : ($to === 'cancelled' ? 'cancel' : 'edit'));
        if (!$this->hasAccess('tasks.' . $action, ACCESS_LEVEL_EDIT)) throw new RuntimeException('Task transition denied.');
    }

    /** Plain text input; creator and completion metadata always come from the session. */
    public function add($values)
    {
        if (!$this->hasAccess('tasks.add', ACCESS_LEVEL_EDIT) || !$this->hasAccess('tasks.show', ACCESS_LEVEL_READ)) throw new RuntimeException('Task creation denied.');
        $record = $this->validate($values);
        $this->authorizeAssociations($record, true);
        $this->authorizeTransition('open', $record['status']);
        if (!$this->_db->beginTransaction()) throw new RuntimeException('Task writes require their own transaction.');
        try
        {
            if (!$this->_db->query('INSERT INTO task SET ' . $this->valuesSQL($record) . $this->completionSQL($record['status']) . ', created_by = ' . (int) $_SESSION['CATS']->getUserID() . ', date_created = NOW(), date_modified = NOW()')) throw new RuntimeException('Unable to create Task.');
            $id = $this->_db->getLastInsertID();
            if (!(new History())->storeHistoryNew(DATA_ITEM_TASK, $id)) throw new RuntimeException('Unable to record Task history.');
            $this->_db->commitTransaction();
            return $id;
        }
        catch (Throwable $e)
        {
            $this->_db->rollbackTransaction();
            throw $e;
        }
    }

    /** Patch only supplied fields. Lock the current Task so concurrent edits retain audit facts. */
    public function update($id, $values)
    {
        $id = self::normalizeID($id, false);
        if (!$this->hasAccess('tasks.edit', ACCESS_LEVEL_EDIT)) throw new RuntimeException('Task update denied.');
        if (!$this->_db->beginTransaction()) throw new RuntimeException('Task writes require their own transaction.');
        try
        {
            $before = $this->_db->getAssoc($this->selectSQL() . ' WHERE task_id = ' . $id . ' FOR UPDATE');
            if (!$this->canRead($before)) throw new RuntimeException('Task unavailable.');
            $actor = $_SESSION['CATS']->getUserID();
            $manager = $this->isAdministrator() || ($before['createdBy'] == $actor &&
                ($before['dataItemType'] === null || $this->canAccessParent($before['dataItemType'], $before['dataItemID'], true)));
            if (!$manager && $before['assignedTo'] != $actor) throw new RuntimeException('Task update denied.');
            $record = $this->validate($values, $before);
            foreach (array('assignedTo', 'dataItemType', 'dataItemID', 'candidateJobOrderID') as $field)
                if (!$manager && $record[$field] != $before[$field]) throw new RuntimeException('Task reassignment or association change denied.');
            $parentChanged = $record['dataItemType'] != $before['dataItemType'] || $record['dataItemID'] != $before['dataItemID'];
            $this->authorizeAssociations($record, $parentChanged);
            $this->authorizeTransition($before['status'], $record['status']);
            $completion = $record['status'] === $before['status'] ? '' : $this->completionSQL($record['status']);
            if (!$this->_db->query('UPDATE task SET ' . $this->valuesSQL($record) . $completion . ', date_modified = NOW() WHERE task_id = ' . $id)) throw new RuntimeException('Unable to update Task.');
            $after = $this->_db->getAssoc($this->selectSQL() . ' WHERE task_id = ' . $id);
            if (!(new History())->storeHistoryChanges(DATA_ITEM_TASK, $id, $this->historySnapshot($before), $this->historySnapshot($after))) throw new RuntimeException('Unable to record Task history.');
            $this->_db->commitTransaction();
            return true;
        }
        catch (Throwable $e)
        {
            $this->_db->rollbackTransaction();
            throw $e;
        }
    }

    private function historySnapshot($record)
    {
        // Keep the parent pair together so historical visibility can also be checked.
        $record['parent'] = $record['dataItemType'] === null ? null : $record['dataItemType'] . ':' . $record['dataItemID'];
        unset($record['dataItemType'], $record['dataItemID'], $record['dateModified']);
        return $record;
    }

    public function getHistory($id)
    {
        $record = $this->get($id);
        if (empty($record)) return array();
        $rows = (new History())->getAll(DATA_ITEM_TASK, $record['taskID']);
        // Reparenting must not reveal previously linked restricted records through history.
        foreach ($rows as $row)
        {
            foreach (array('previousValue', 'newValue') as $field)
            {
                if ($row[$field] === null || $row[$field] === '') continue;
                if ($row['theField'] === 'parent')
                {
                    $parent = explode(':', $row[$field]);
                    if (count($parent) !== 2 || !$this->canAccessParent($parent[0], $parent[1])) return array();
                }
                if ($row['theField'] === 'candidateJobOrderID' && !(new Pipelines())->canAccess($row[$field])) return array();
            }
        }
        return $rows;
    }
}
