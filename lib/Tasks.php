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

    /** Derived warnings for the signed-in actor; counts contain only authorized rows. */
    public function getRecruitmentWarnings()
    {
        $result = array('warnings' => array(), 'counts' => array('W01' => 0, 'W02' => 0, 'W03' => 0, 'W07' => 0, 'W08' => 0),
            'jobsAtRisk' => 0, 'disabledRules' => array());
        if (!isset($_SESSION['CATS']) || !$_SESSION['CATS']->isLoggedIn() ||
            $_SESSION['CATS']->getUserID() <= 0) return $result;
        $today = DateUtility::getAdjustedDate('Y-m-d');
        $rows = array();
        $manager = $this->isAdministrator() && $this->hasAccess('tasks.edit', ACCESS_LEVEL_EDIT);
        foreach ($this->getWarningTasks($today, $manager) as $task)
        {
            $due = $task['dueDate'];
            $age = DateUtility::getCalendarDaysBetween($due, $today);
            $jobID = $task['dataItemType'] == DATA_ITEM_JOBORDER ? (int) $task['dataItemID'] :
                ($task['contextJobOrderID'] === null ? null : (int) $task['contextJobOrderID']);
            $base = array('entityType' => DATA_ITEM_TASK, 'entityID' => (int) $task['taskID'],
                'title' => $task['title'], 'responsibleUserID' => $task['assignedTo'],
                'taskID' => (int) $task['taskID'], 'jobOrderID' => $jobID,
                'parentType' => $task['dataItemType'], 'parentID' => $task['dataItemID'],
                'candidateJobOrderID' => $task['candidateJobOrderID'],
                'sourceDate' => null, 'ageDays' => null, 'coverage' => 'uncovered',
                'priority' => $task['priority'], 'routing' => 'task_assignee',
                'evidence' => array('table' => 'task', 'id' => (int) $task['taskID']));
            if ($age !== null && $age > 0)
            {
                $rows[] = array_replace($base, array('code' => 'W01',
                    'identity' => 'W01:task:' . $task['taskID'] . ':' . $due,
                    'reason' => 'overdue_commitment', 'sourceDate' => $due, 'ageDays' => $age,
                    'coverage' => 'overdue_follow_up', 'sortGroup' => 0));
            }
            if ($manager && $task['invalidAssignee'])
            {
                $rows[] = array_replace($base, array('code' => 'W08',
                    'identity' => 'W08:task:' . $task['taskID'] . ':assignee',
                    'reason' => 'invalid_assignee', 'routing' => 'tasks_administrator', 'sortGroup' => 1));
            }
        }
        foreach ($this->getJobRecruitmentWarnings() as $warning) $rows[] = $warning;
        foreach ((new JobOrders())->getUnassignedWorkWarnings() as $warning) $rows[] = $warning;
        // Preserve the authoritative Company results and their relative ordering.
        foreach ((new Companies())->getRelationshipAttention() as $order => $company)
        {
            $coverage = !$company['tasks'] ? 'uncovered' :
                (in_array('overdue_task', $company['reasons'], true) ? 'overdue_follow_up' : 'covered');
            $rows[] = array('code' => 'W07', 'identity' => 'W07:company:' . $company['companyID'],
                'entityType' => DATA_ITEM_COMPANY, 'entityID' => (int) $company['companyID'],
                'title' => $company['name'], 'reason' => $company['primaryReason'],
                'sourceDate' => $company['lastContact'], 'ageDays' => $company['contactAgeDays'],
                'responsibleUserID' => $company['owner'], 'routing' => 'company_owner',
                'taskID' => null, 'jobOrderID' => null, 'coverage' => $coverage,
                'evidence' => array('table' => 'company', 'id' => (int) $company['companyID']),
                'relationshipAttention' => $company, 'sortGroup' => 3, 'relationshipOrder' => $order);
        }
        $priority = array('high' => 0, 'normal' => 1, 'low' => 2);
        usort($rows, function ($a, $b) use ($priority) {
            if ($a['code'] === 'W07' && $b['code'] === 'W07')
                return $a['relationshipOrder'] <=> $b['relationshipOrder'];
            return array($a['sortGroup'], -($a['ageDays'] ?? 0), $priority[$a['priority'] ?? 'normal'],
                $a['entityType'], $a['entityID'], $a['identity']) <=>
                array($b['sortGroup'], -($b['ageDays'] ?? 0), $priority[$b['priority'] ?? 'normal'],
                $b['entityType'], $b['entityID'], $b['identity']);
        });
        // W07 counts include covered attention records. W08 counts each invalid
        // responsibility separately; jobsAtRisk counts distinct authorised Jobs.
        $jobs = array();
        foreach ($rows as $row)
        {
            ++$result['counts'][$row['code']];
            if ($row['jobOrderID'] !== null) $jobs[$row['jobOrderID']] = true;
        }
        $result['warnings'] = $rows;
        $result['jobsAtRisk'] = count($jobs);
        return $result;
    }

    /** W02/W03 use recorded milestones, not inferred outcomes or lifecycle cycles. */
    public function getJobRecruitmentWarnings($jobOrderID = null)
    {
        if ($jobOrderID !== null) $jobOrderID = self::normalizeID($jobOrderID, false);
        if (!isset($_SESSION['CATS']) || !$_SESSION['CATS']->isLoggedIn() ||
            $_SESSION['CATS']->getUserID() <= 0 || !$this->hasAccess('joborders.list', ACCESS_LEVEL_READ) ||
            !$this->hasAccess('joborders.show', ACCESS_LEVEL_READ) ||
            !$this->hasAccess('candidates.show', ACCESS_LEVEL_READ) || !JobOrderStatuses::isActionable('Active')) return array();
        $now = $this->_db->getAssoc('SELECT NOW() AS clock')['clock'];
        if (!DateUtility::isValidDatabaseDateTime($now)) return array();
        $clock = $this->_db->makeQueryString($now);
        $hiddenJob = $this->hasAccess('joborders.hidden', ACCESS_LEVEL_SA) ? '' : ' AND (j.is_admin_hidden IS NULL OR j.is_admin_hidden <> 1)';
        $hiddenCandidate = $this->hasAccess('candidates.hidden', ACCESS_LEVEL_SA) ? '' : ' OR c.is_admin_hidden = 1';
        $milestones = implode(',', array(PIPELINE_STATUS_SUBMITTED, PIPELINE_STATUS_INTERVIEWING, PIPELINE_STATUS_OFFERED, PIPELINE_STATUS_PLACED));
        $lastID = 0;
        $result = array();
        do
        {
            $jobs = $this->_db->getAllAssoc('SELECT j.joborder_id AS jobOrderID, j.title, j.owner, j.recruiter,
                j.is_hot AS isHot, j.is_admin_hidden AS isAdminHidden, j.first_actionable_at AS actionable,
                j.w02_reviewed_at AS W02, j.w03_reviewed_at AS W03,
                EXISTS(SELECT 1 FROM candidate_joborder_status_history h WHERE h.joborder_id = j.joborder_id
                    AND h.status_to = ' . PIPELINE_STATUS_SUBMITTED . ' AND h.date >= j.first_actionable_at) AS submitted,
                (SELECT MAX(h.date) FROM candidate_joborder_status_history h WHERE h.joborder_id = j.joborder_id
                    AND h.status_to IN (' . $milestones . ') AND h.date >= j.first_actionable_at) AS progress
                FROM joborder j WHERE j.status = \'Active\' AND j.is_speculative = 0
                AND j.first_actionable_at IS NOT NULL AND j.first_actionable_at <= ' . $clock . $hiddenJob . '
                AND NOT EXISTS(SELECT 1 FROM candidate_joborder p LEFT JOIN candidate c ON c.candidate_id = p.candidate_id
                    WHERE p.joborder_id = j.joborder_id AND (c.candidate_id IS NULL' . $hiddenCandidate . '))
                AND NOT EXISTS(SELECT 1 FROM candidate_joborder_status_history h LEFT JOIN candidate c ON c.candidate_id = h.candidate_id
                    WHERE h.joborder_id = j.joborder_id AND h.status_to IN (' . $milestones . ')
                    AND (h.date < \'1000-01-01 00:00:00\' OR MONTH(h.date) = 0 OR DAY(h.date) = 0
                        OR LAST_DAY(h.date) IS NULL OR DAY(h.date) > DAY(LAST_DAY(h.date)) OR h.date > ' . $clock . '
                        OR (h.date >= j.first_actionable_at AND (c.candidate_id IS NULL' . $hiddenCandidate . '))))
                AND j.joborder_id > ' . $lastID . ($jobOrderID === null ? '' : ' AND j.joborder_id = ' . $jobOrderID) . '
                ORDER BY j.joborder_id LIMIT 200');
            if (!$jobs) break;
            $parents = array(DATA_ITEM_JOBORDER => array_column($jobs, null, 'jobOrderID'));
            $coverage = array();
            if ($this->hasAccess('tasks.list', ACCESS_LEVEL_READ) && $this->hasAccess('tasks.show', ACCESS_LEVEL_READ))
            {
                $lastTaskID = 0;
                do
                {
                    $tasks = $this->_db->getAllAssoc($this->selectSQL() . " WHERE data_item_type = " . DATA_ITEM_JOBORDER .
                        " AND data_item_id IN (" . implode(',', array_keys($parents[DATA_ITEM_JOBORDER])) . ")
                        AND status IN ('open', 'in_progress') AND purpose = 'job_follow_up' AND task_id > " . $lastTaskID . " ORDER BY task_id LIMIT 200");
                    $access = (new Pipelines())->getAccessMap(array_filter(array_column($tasks, 'candidateJobOrderID'), fn($id) => $id !== null));
                    foreach ($tasks as $task)
                    {
                        $lastTaskID = (int) $task['taskID'];
                        if (!$this->canRead($task, $parents, $access)) continue;
                        $job = (int) $task['dataItemID'];
                        $overdue = DateUtility::getCalendarDaysBetween($task['dueDate'], DateUtility::getAdjustedDate('Y-m-d'));
                        if (!isset($coverage[$job]) || $overdue > 0)
                            $coverage[$job] = array('taskID' => (int) $task['taskID'], 'coverage' => $overdue > 0 ? 'overdue_follow_up' : 'covered');
                    }
                } while (count($tasks) === 200);
            }
            foreach ($jobs as $job)
            {
                $lastID = (int) $job['jobOrderID'];
                if (!DateUtility::isValidDatabaseDateTime($job['actionable'])) continue;
                foreach (array('W02' => 2, 'W03' => 5) as $code => $days)
                {
                    if ($code === 'W02' && $job['submitted']) continue;
                    $source = $code === 'W03' && $job['progress'] !== null ? $job['progress'] : $job['actionable'];
                    $deadline = DateUtility::addWorkingDays($source, $days);
                    if ($deadline === null || $now <= $deadline) continue;
                    if ($job[$code] !== null)
                    {
                        $until = DateUtility::addWorkingDays($job[$code], 5);
                        // Malformed/future review evidence is uncertain, not permission to warn.
                        if ($until === null || $job[$code] > $now || $now < $until) continue;
                    }
                    $result[] = array_merge(array('code' => $code, 'identity' => $code . ':joborder:' . $lastID,
                        'entityType' => DATA_ITEM_JOBORDER, 'entityID' => $lastID, 'jobOrderID' => $lastID,
                        'title' => $job['title'], 'reason' => $code === 'W02' ? 'no_recorded_submission' : 'no_recorded_candidate_progress',
                        'sourceDate' => $source, 'deadline' => $deadline,
                        'ageDays' => DateUtility::getWorkingDaysBetween(substr($source, 0, 10), substr($now, 0, 10)),
                        'responsibleUserID' => $job['recruiter'], 'routing' => 'joborder_recruiter',
                        'taskID' => null, 'coverage' => 'uncovered', 'priority' => $job['isHot'] ? 'high' : 'normal',
                        'sortGroup' => 2, 'evidence' => array('table' => 'joborder', 'id' => $lastID)),
                        $coverage[$lastID] ?? array());
                }
            }
        } while (count($jobs) === 200);
        return $result;
    }

    /** SQL-filtered chunks; reuse canRead with batch-loaded parent/pipeline evidence. */
    private function getWarningTasks($today, $manager)
    {
        if (!$this->hasAccess('tasks.list', ACCESS_LEVEL_READ) || !$this->hasAccess('tasks.show', ACCESS_LEVEL_READ)) return array();
        $invalid = '(assigned_to IS NULL OR NOT EXISTS (SELECT 1 FROM user
            WHERE user.user_id = task.assigned_to AND user.access_level > ' . ACCESS_LEVEL_DISABLED . '))';
        $where = " WHERE status IN ('open', 'in_progress') AND (due_date < " .
            $this->_db->makeQueryString($today) . ($manager ? ' OR ' . $invalid : '') . ')';
        $lastID = 0;
        $result = array();
        do
        {
            $sql = $this->selectSQL(', ' . $invalid . ' AS invalidAssignee,
                (SELECT joborder_id FROM candidate_joborder WHERE candidate_joborder_id = task.candidate_joborder_id) AS contextJobOrderID');
            $rows = $this->_db->getAllAssoc($sql . $where . ' AND task_id > ' . $lastID . ' ORDER BY task_id LIMIT 200');
            $parents = array();
            $ids = array();
            $pipelineIDs = array();
            foreach ($rows as $row)
            {
                $lastID = (int) $row['taskID'];
                if ($row['dataItemType'] !== null) $ids[$row['dataItemType']][] = (int) $row['dataItemID'];
                if ($row['candidateJobOrderID'] !== null) $pipelineIDs[] = (int) $row['candidateJobOrderID'];
            }
            foreach (array(DATA_ITEM_COMPANY => 'company', DATA_ITEM_CONTACT => 'contact',
                DATA_ITEM_CANDIDATE => 'candidate', DATA_ITEM_JOBORDER => 'joborder') as $type => $table)
            {
                if (empty($ids[$type]) || !$this->hasAccess($this->parentModule($type) . '.show', ACCESS_LEVEL_READ)) continue;
                $hidden = in_array($type, array(DATA_ITEM_CANDIDATE, DATA_ITEM_JOBORDER), true) ? ', is_admin_hidden AS isAdminHidden' : '';
                $records = $this->_db->getAllAssoc('SELECT ' . $table . '_id AS id' . $hidden .
                    ' FROM ' . $table . ' WHERE ' . $table . '_id IN (' . implode(',', array_unique($ids[$type])) . ')');
                $parents[$type] = array_column($records, null, 'id');
            }
            $pipelines = (new Pipelines())->getAccessMap($pipelineIDs);
            foreach ($rows as $row)
                if ($this->canRead($row, $parents, $pipelines)) $result[] = $row;
        } while (count($rows) === 200);
        return $result;
    }

    public static function getDefaults()
    {
        return array('title' => '', 'description' => null, 'dueDate' => null,
            'priority' => 'normal', 'status' => 'open', 'purpose' => 'general',
            'assignedTo' => null, 'dataItemType' => null, 'dataItemID' => null,
            'candidateJobOrderID' => null);
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

    private function canAccessParent($type, $id, $edit = false, $record = false)
    {
        $module = $this->parentModule($type);
        if ($module === null || $id === null || !$this->hasAccess($module . '.show', ACCESS_LEVEL_READ) ||
            ($edit && !$this->hasAccess($module . '.edit', ACCESS_LEVEL_EDIT))) return false;

        if ($record === false)
        {
            switch ($type)
            {
                case DATA_ITEM_CANDIDATE: $record = CandidateAuthorization::getCandidate($id); break;
                case DATA_ITEM_JOBORDER: $record = (new JobOrders())->get($id); break;
                case DATA_ITEM_COMPANY: $record = (new Companies())->get($id); break;
                case DATA_ITEM_CONTACT: $record = (new Contacts())->get($id); break;
            }
        }
        switch ($type)
        {
            case DATA_ITEM_CANDIDATE:
                return CandidateAuthorization::canAccessCandidateRecord($record);
            case DATA_ITEM_JOBORDER:
                return !empty($record) && ($record['isAdminHidden'] != 1 ||
                    $this->hasAccess('joborders.hidden', ACCESS_LEVEL_SA));
            case DATA_ITEM_COMPANY:
            case DATA_ITEM_CONTACT:
                return !empty($record);
        }
        return false;
    }

    private function canRead($record, $parents = null, $pipelines = null)
    {
        if (empty($record) || !$this->hasAccess('tasks.show', ACCESS_LEVEL_READ)) return false;
        if ($record['dataItemType'] === null)
        {
            $actor = $_SESSION['CATS']->getUserID();
            if ($record['dataItemID'] !== null || ($record['createdBy'] != $actor &&
                $record['assignedTo'] != $actor && !$this->isAdministrator())) return false;
        }
        elseif (!$this->canAccessParent($record['dataItemType'], $record['dataItemID'], false,
            $parents === null ? false : ($parents[$record['dataItemType']][$record['dataItemID']] ?? array()))) return false;
        return $record['candidateJobOrderID'] === null ||
            ($pipelines === null ? (new Pipelines())->canAccess($record['candidateJobOrderID']) :
             !empty($pipelines[$record['candidateJobOrderID']]));
    }

    public static function getParentTypes()
    {
        return array(DATA_ITEM_CANDIDATE => 'Candidate', DATA_ITEM_CONTACT => 'Contact',
            DATA_ITEM_COMPANY => 'Company', DATA_ITEM_JOBORDER => 'Job Order');
    }

    /** Existing pipeline selectors, narrowed by both records' current visibility. */
    public function getPipelineChoices($type, $id)
    {
        $type = self::normalizeID($type);
        $id = self::normalizeID($id);
        if (!$this->hasAccess('tasks.show', ACCESS_LEVEL_READ) ||
            !in_array($type, array(DATA_ITEM_CANDIDATE, DATA_ITEM_JOBORDER), true) ||
            !$this->canAccessParent($type, $id)) return array();
        $pipelines = new Pipelines();
        $rows = $type === DATA_ITEM_CANDIDATE ? $pipelines->getCandidatePipeline($id) : $pipelines->getJobOrderPipeline($id);
        $choices = array();
        foreach ($rows as $row)
        {
            if (!$pipelines->canAccess($row['candidateJobOrderID'])) continue;
            $choices[$row['candidateJobOrderID']] = $type === DATA_ITEM_CANDIDATE ?
                $row['title'] . ' (' . $row['companyName'] . ')' : trim($row['firstName'] . ' ' . $row['lastName']);
        }
        return $choices;
    }

    /** Resolve a readable Task's exact Activity context without inventing an Activity. */
    public function getActivityTarget($id)
    {
        $record = $this->get($id);
        if (empty($record)) return array();
        if ($record['candidateJobOrderID'] !== null)
        {
            // get() has already authorised both sides through Pipelines::canAccess().
            return $this->_db->getAssoc('SELECT candidate_id AS candidateID, joborder_id AS jobOrderID
                FROM candidate_joborder WHERE candidate_joborder_id = ' . (int) $record['candidateJobOrderID']);
        }
        if ($record['dataItemType'] == DATA_ITEM_CANDIDATE) return array('candidateID' => $record['dataItemID']);
        if ($record['dataItemType'] == DATA_ITEM_CONTACT) return array('contactID' => $record['dataItemID']);
        return array();
    }

    /** Context labels are returned only after the same parent/action checks used by add(). */
    public function getParentContext($type, $id, $forCreation = false)
    {
        $type = self::normalizeID($type, false);
        $id = self::normalizeID($id, false);
        if (!$this->hasAccess('tasks.show', ACCESS_LEVEL_READ) ||
            ($forCreation && !$this->hasAccess('tasks.add', ACCESS_LEVEL_EDIT)) ||
            !$this->canAccessParent($type, $id, $forCreation))
            throw new RuntimeException('Task association unavailable.');
        switch ($type)
        {
            case DATA_ITEM_CANDIDATE: $record = (new Candidates())->get($id); break;
            case DATA_ITEM_CONTACT: $record = (new Contacts())->get($id); break;
            case DATA_ITEM_COMPANY: $record = (new Companies())->get($id); break;
            case DATA_ITEM_JOBORDER: $record = (new JobOrders())->get($id); break;
        }
        if (empty($record)) throw new RuntimeException('Task association unavailable.');
        $name = $type === DATA_ITEM_COMPANY ? $record['name'] : ($type === DATA_ITEM_JOBORDER ?
            $record['title'] : trim($record['firstName'] . ' ' . $record['lastName']));
        $companyName = '';
        if (in_array($type, array(DATA_ITEM_CONTACT, DATA_ITEM_JOBORDER), true) &&
            !empty($record['companyID']) && $this->canAccessParent(DATA_ITEM_COMPANY, $record['companyID']))
            $companyName = $record['companyName'];
        return array('type' => $type, 'id' => $id, 'name' => $name, 'companyName' => $companyName);
    }

    private function canManage($record)
    {
        return $this->isAdministrator() || ($record['createdBy'] == $_SESSION['CATS']->getUserID() &&
            ($record['dataItemType'] === null || $this->canAccessParent($record['dataItemType'], $record['dataItemID'], true)));
    }

    /** Presentation hints only; update() rechecks the locked record on every write. */
    public function getActionPermissions($id)
    {
        $record = $this->get($id);
        $permissions = array_fill_keys(array('edit', 'manage', 'complete', 'cancel', 'reopen'), false);
        if (empty($record)) return $permissions;
        $manager = $this->canManage($record);
        $permissions['edit'] = $this->hasAccess('tasks.edit', ACCESS_LEVEL_EDIT) &&
            ($manager || $record['assignedTo'] == $_SESSION['CATS']->getUserID());
        $permissions['manage'] = $permissions['edit'] && $manager;
        $closed = in_array($record['status'], array('completed', 'cancelled'), true);
        foreach (array('complete', 'cancel', 'reopen') as $action)
            $permissions[$action] = $permissions['edit'] &&
                ($action === 'reopen' ? $closed : !$closed) && $this->hasAccess('tasks.' . $action, ACCESS_LEVEL_EDIT);
        return $permissions;
    }

    /** Additional SELECT expressions are internal SQL, never request input. */
    private function selectSQL($additionalColumns = '')
    {
        return 'SELECT task_id AS taskID, title, description, due_date AS dueDate,
            priority, status, purpose, assigned_to AS assignedTo,
            data_item_type AS dataItemType, data_item_id AS dataItemID,
            candidate_joborder_id AS candidateJobOrderID, created_by AS createdBy,
            date_created AS dateCreated, date_modified AS dateModified,
            completed_by AS completedBy, date_completed AS dateCompleted' . $additionalColumns . ' FROM task';
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
     * priority, purpose, openOnly and relationshipCompanyIDs (Company/current
     * Contact parents). Authorise before returning rows or counts;
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
                case 'relationshipCompanyIDs':
                    if (!is_array($value) || !$value)
                        throw new InvalidArgumentException('Supply Company IDs for relationship follow-up.');
                    $ids = array_map(fn($id) => self::normalizeID($id, false), $value);
                    $ids = implode(',', array_unique($ids));
                    $where[] = '(data_item_type = ' . DATA_ITEM_COMPANY . ' AND data_item_id IN (' . $ids .
                        ') OR data_item_type = ' . DATA_ITEM_CONTACT . ' AND data_item_id IN
                        (SELECT contact_id FROM contact WHERE company_id IN (' . $ids . ') AND left_company = 0))';
                    break;
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
        $defaults = self::getDefaults();
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
            $manager = $this->canManage($before);
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
