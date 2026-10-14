<?php
/** Personal warning review persistence and visibility. */
include_once(LEGACY_ROOT . '/lib/Tasks.php');
include_once(LEGACY_ROOT . '/modules/tasks/TaskPresentation.php');

class WarningReviews
{
    private $_db;

    public function __construct()
    {
        $this->_db = DatabaseConnection::getInstance();
    }

    /** Stable source identity, independent of labels, dates and reviewer. */
    public static function identity($rule, $type, $id, $reason, $reasonID = 0)
    {
        foreach (array($type, $id, $reasonID) as $value)
            if ((!is_int($value) && !is_string($value)) ||
                !preg_match('/^(0|[1-9][0-9]*)$/D', (string) $value) || $value > 2147483647)
                throw new InvalidArgumentException('Invalid warning identity.');
        if ($id == 0 || !is_string($rule) || !is_string($reason))
            throw new InvalidArgumentException('Invalid warning identity.');
        $reasons = array(
            'W01' => array(DATA_ITEM_TASK => array('overdue_commitment')),
            'W02' => array(DATA_ITEM_JOBORDER => array('no_recorded_submission')),
            'W03' => array(DATA_ITEM_JOBORDER => array('no_recorded_candidate_progress')),
            'W07' => array(DATA_ITEM_COMPANY => array('stale_relationship', 'no_next_action', 'overdue_task', 'due_today', 'upcoming')),
            'W08' => array(DATA_ITEM_TASK => array('invalid_assignee'), DATA_ITEM_JOBORDER => array('invalid_owner', 'invalid_recruiter'))
        );
        if (!in_array($reason, $reasons[$rule][$type] ?? array(), true))
            throw new InvalidArgumentException('Invalid warning reason.');
        $taskReason = $rule === 'W07' && in_array($reason, array('overdue_task', 'due_today', 'upcoming'), true);
        if ($taskReason !== ($reasonID > 0)) throw new InvalidArgumentException('Invalid warning reason record.');
        return array('warning_rule' => $rule, 'data_item_type' => (int) $type,
            'data_item_id' => (int) $id, 'reason_key' => $reason, 'reason_id' => (int) $reasonID);
    }

    private function actor()
    {
        if (!isset($_SESSION['CATS']) || !$_SESSION['CATS']->isLoggedIn() ||
            $_SESSION['CATS']->getUserID() <= 0)
            throw new RuntimeException('Warning review denied.');
        return (int) $_SESSION['CATS']->getUserID();
    }

    private function where($identity, $actor)
    {
        $parts = array('reviewing_user = ' . $actor);
        foreach ($identity as $key => $value) $parts[] = $key . ' = ' . $this->_db->makeQueryString($value);
        return implode(' AND ', $parts);
    }

    /** Reuse the source query's ACL, eligibility and responsibility scope. */
    private function source($identity, $companyOversight = false)
    {
        $this->actor();
        foreach ($this->sourceRows($identity['data_item_type'], $identity['data_item_id'], $companyOversight) as $row)
        {
            $hash = $this->evidence($identity, $row);
            if ($hash !== null) return $hash;
        }
        throw new RuntimeException('Warning source unavailable.');
    }

    private function sourceRows($type, $id, $companyOversight = false)
    {
        $actor = $this->actor();
        $user = $this->_db->getAssoc('SELECT access_level FROM user WHERE user_id = ' . $actor);
        if (!$user || $user['access_level'] <= ACCESS_LEVEL_DISABLED) return array();
        $tasks = new Tasks();
        if ($type == DATA_ITEM_JOBORDER) return $tasks->getJobRecruitmentWarnings($id);
        if ($type == DATA_ITEM_COMPANY) return $tasks->getCompanyRecruitmentWarnings($id, $companyOversight);
        return $tasks->getRecruitmentWarnings()['warnings'];
    }

    /** Only internally enumerated, authorised evidence enters presentation preparation. */
    public function getForSource($type, $id)
    {
        if (!in_array($type, array(DATA_ITEM_JOBORDER, DATA_ITEM_COMPANY), true))
            throw new InvalidArgumentException('Invalid review source.');
        $rows = $this->sourceRows($type, $id);
        $counts = array_fill_keys(array('W01', 'W02', 'W03', 'W07', 'W08'), 0);
        foreach ($rows as $row) ++$counts[$row['code']];
        return $this->prepare(array('warnings' => $rows, 'counts' => $counts,
            'jobsAtRisk' => $type === DATA_ITEM_JOBORDER && $rows ? 1 : 0));
    }

    public static function reviewKey($row)
    {
        return $row['code'] . ':' . $row['entityType'] . ':' . $row['entityID'];
    }

    public static function reminderDates()
    {
        $dates = array();
        $today = DateUtility::getAdjustedDate('Y-m-d');
        foreach (array(1, 5, 10) as $days)
            $dates[$days] = substr(DateUtility::addWorkingDays($today . ' 00:00:00', $days), 0, 10);
        return $dates;
    }

    /** Shared by writes and reads; never re-enumerates warning sources. */
    private function evidence($identity, $row)
    {
        if ($row['code'] !== $identity['warning_rule'] || $row['entityType'] != $identity['data_item_type'] ||
            $row['entityID'] != $identity['data_item_id']) return null;
        if ($row['code'] === 'W07')
        {
            $company = $row['relationshipAttention'];
            if (!in_array($identity['reason_key'], $company['reasons'], true)) return null;
            $evidence = array($company['owner'], $company['commercialTier'], $company['relationshipStatus'], $company['reviewDays']);
            if ($identity['reason_id'])
            {
                $task = null;
                $today = DateUtility::getAdjustedDate('Y-m-d');
                foreach ($company['tasks'] as $candidate)
                {
                    $reason = $candidate['dueDate'] < $today ? 'overdue_task' :
                        ($candidate['dueDate'] === $today ? 'due_today' : 'upcoming');
                    if ($candidate['taskID'] == $identity['reason_id'] && $reason === $identity['reason_key']) $task = $candidate;
                }
                if ($task === null) return null;
                $evidence[] = array($task['taskID'], $task['dueDate'], $task['assignedTo']);
            }
            elseif ($identity['reason_key'] === 'stale_relationship') $evidence[] = $company['lastContact'];
            else $evidence[] = $company['reviewTasks'];
            // no_next_action is the source query's authoritative absence of qualifying commitments.
        }
        else
        {
            if ($row['reason'] !== $identity['reason_key']) return null;
            $evidence = array($row['sourceDate'], $row['responsibleUserID'], $row['evidence']);
        }
        return hash('sha256', json_encode(array($identity, $evidence), JSON_THROW_ON_ERROR));
    }

    /**
     * Obtain current authorised sources once. Original warnings/counts/jobsAtRisk
     * remain intact; visibleWarnings/counts count Companies, suppressedReasonCount
     * counts individual reasons. No caller-supplied actor or source collection.
     */
    public function getRecruitmentWarnings()
    {
        return $this->prepare((new Tasks())->getRecruitmentWarnings());
    }

    private function prepare($result)
    {
        $result['visibleWarnings'] = array();
        $result['visibleCounts'] = array_fill_keys(array_keys($result['counts']), 0);
        $result['suppressedReasonCount'] = 0;
        $result['reviews'] = array();
        $result['suppressedWarnings'] = array();
        $ids = array();
        foreach ($result['warnings'] as $row)
            if (in_array($row['code'], array('W02', 'W03', 'W07'), true))
                $ids[$row['entityType']][$row['entityID']] = (int) $row['entityID'];
        $states = array();
        // Bounded reads scoped to this actor and already authorised source IDs.
        foreach ($ids as $type => $sourceIDs)
            foreach (array_chunk($sourceIDs, 200) as $batch)
                foreach ($this->_db->getAllAssoc('SELECT * FROM warning_review WHERE reviewing_user = ' . $this->actor() .
                    " AND warning_rule IN ('W02', 'W03', 'W07') AND data_item_type = " . (int) $type .
                    ' AND reason_id = 0 AND data_item_id IN (' . implode(',', $batch) . ')') as $state)
                    $states[$state['warning_rule'] . ':' . $type . ':' . $state['data_item_id']][$state['reason_key']] = $state;
        if (!$result['warnings']) return $result;
        $today = DateUtility::getAdjustedDate('Y-m-d');
        $companies = array();
        $companyPositions = array();
        foreach ($result['warnings'] as $row)
        {
            $key = self::reviewKey($row);
            $reasons = $row['code'] === 'W07' ? $row['relationshipAttention']['reasons'] : array($row['reason']);
            foreach ($reasons as $reason)
            {
                if (!in_array($row['code'], array('W02', 'W03'), true) &&
                    !($row['code'] === 'W07' && in_array($reason, array('stale_relationship', 'no_next_action'), true))) continue;
                $identity = self::identity($row['code'], $row['entityType'], $row['entityID'], $reason);
                $state = $states[$key][$reason] ?? null;
                $hash = $this->evidence($identity, $row);
                $suppressed = $state && $this->suppresses($state, $today) &&
                    ($row['code'] === 'W07' || $state['action'] === 'remind_later') && hash_equals($hash, $state['evidence_hash']);
                $status = 'Active warning';
                if ($suppressed) $status = $state['action'] === 'close' ? 'Temporarily closed' : 'Remind later';
                elseif ($state && $state['action'] !== 'reset')
                    $status = !hash_equals($hash, $state['evidence_hash']) ? 'Warning evidence changed' :
                        ($state['review_until'] <= $today ? 'Review expired' : 'Review is invalid');
                $module = $row['code'] === 'W07' ? 'companies' : 'joborders';
                $review = array('identity' => $identity, 'evidence' => $hash,
                    'version' => $state ? (int) $state['version'] : 0, 'state' => $state ?: null,
                    'suppressed' => (bool) $suppressed, 'status' => $status,
                    'canReview' => $_SESSION['CATS']->getAccessLevel($module . '.show') >= ACCESS_LEVEL_READ &&
                        $_SESSION['CATS']->getAccessLevel($module . '.edit') >= ACCESS_LEVEL_EDIT);
                $result['reviews'][$key][$reason] = $review;
                if ($suppressed)
                {
                    ++$result['suppressedReasonCount'];
                    $result['suppressedWarnings'][] = array('warning' => $row, 'review' => $review);
                }
            }
            if (in_array($row['code'], array('W02', 'W03'), true) &&
                $result['reviews'][$key][$row['reason']]['suppressed']) continue;
            if ($row['code'] === 'W07')
            {
                $visible = array();
                foreach ($row['relationshipAttention']['reasons'] as $reason)
                {
                    if (!empty($result['reviews'][$key][$reason]['suppressed'])) continue;
                    $visible[] = $reason;
                }
                if (!$visible) continue;
                $row['relationshipAttention']['reasons'] = $visible;
                $row['relationshipAttention']['primaryReason'] = $visible[0];
                $row['reason'] = $visible[0];
                $company = &$row['relationshipAttention'];
                $company['sortDate'] = $company['lastContact'] === null ? '' :
                    DateUtility::getAdjustedDate('Y-m-d', strtotime($company['lastContact']));
                $dates = array();
                foreach ($company['tasks'] as $task)
                {
                    $reason = $task['dueDate'] < $today ? 'overdue_task' :
                        ($task['dueDate'] === $today ? 'due_today' : 'upcoming');
                    if ($reason === $visible[0]) $dates[] = $task['dueDate'];
                }
                if ($dates) $company['sortDate'] = min($dates);
                unset($company);
                $companyPositions[] = count($result['visibleWarnings']);
                $companies[] = $row;
            }
            $result['visibleWarnings'][] = $row;
            ++$result['visibleCounts'][$row['code']];
        }
        // Reorder only Company slots, using the source's priority and tie rules.
        usort($companies, function ($a, $b) {
            return Companies::compareRelationshipAttention($a['relationshipAttention'], $b['relationshipAttention']);
        });
        foreach ($companyPositions as $index => $position) $result['visibleWarnings'][$position] = $companies[$index];
        return $result;
    }

    /** Existing Reports action permission is the explicit oversight gate. */
    public function authorizeOversight()
    {
        $actor = $this->actor();
        if ($_SESSION['CATS']->getAccessLevel('reports.show') < ACCESS_LEVEL_SA)
            throw new RuntimeException('Warning oversight denied.');
        $user = $this->_db->getAssoc('SELECT access_level FROM user WHERE user_id = ' . $actor);
        if (!$user || $user['access_level'] <= ACCESS_LEVEL_DISABLED)
            throw new RuntimeException('Warning oversight denied.');
    }

    private static function identityKey($identity)
    {
        return implode(':', array_values($identity));
    }

    /** Raw source conditions remain visible regardless of any personal suppression.
     * Default scope stays owner-only for W07. An explicit Company uses native
     * record-read permissions through the same source query; never impersonate users.
     * Counts are distinct rule/subject/reasons, not reviewer rows or History events.
     */
    public function getOversight($rule = '', $status = '', $reviewer = 0, $companyID = null)
    {
        $this->authorizeOversight();
        if (!is_string($rule) || !in_array($rule, array('', 'W01', 'W02', 'W03', 'W07', 'W08'), true) ||
            !is_string($status) || !in_array($status, array('', 'outstanding', 'remind_later', 'close', 'reappeared'), true) ||
            !is_int($reviewer) || $reviewer < 0 || $reviewer > 2147483647)
            throw new InvalidArgumentException('Invalid oversight filter.');
        if ($companyID !== null && (!is_int($companyID) || $companyID <= 0 || $companyID > 2147483647))
            throw new InvalidArgumentException('Invalid oversight Company.');
        $sources = array();
        $ids = array();
        $tasks = new Tasks();
        $warnings = $companyID === null ? $tasks->getRecruitmentWarnings()['warnings'] :
            $tasks->getCompanyRecruitmentWarnings($companyID, true);
        foreach ($warnings as $warning)
        {
            $reasons = $warning['code'] === 'W07' ? $warning['relationshipAttention']['reasons'] : array($warning['reason']);
            foreach ($reasons as $reason)
            {
                $reasonIDs = array(0);
                if ($warning['code'] === 'W07' && in_array($reason, array('overdue_task', 'due_today', 'upcoming'), true))
                {
                    $reasonIDs = array();
                    $today = DateUtility::getAdjustedDate('Y-m-d');
                    foreach ($warning['relationshipAttention']['tasks'] as $task)
                        if (($task['dueDate'] < $today ? 'overdue_task' : ($task['dueDate'] === $today ? 'due_today' : 'upcoming')) === $reason)
                            $reasonIDs[] = (int) $task['taskID'];
                }
                foreach ($reasonIDs as $reasonID)
                {
                    $identity = self::identity($warning['code'], $warning['entityType'], $warning['entityID'], $reason, $reasonID);
                    $sources[self::identityKey($identity)] = array('warning' => $warning, 'identity' => $identity,
                        'evidence' => $this->evidence($identity, $warning));
                    $ids[$warning['entityType']][$warning['entityID']] = (int) $warning['entityID'];
                }
            }
        }
        $states = array();
        $events = array();
        $since = DateUtility::getAdjustedDate('Y-m-d', strtotime('-30 days'));
        foreach ($ids as $type => $sourceIDs)
            foreach (array_chunk($sourceIDs, 200) as $batch)
            {
                $scope = 'data_item_type = ' . (int) $type . ' AND data_item_id IN (' . implode(',', $batch) . ')';
                foreach ($this->_db->getAllAssoc('SELECT * FROM warning_review WHERE ' . $scope) as $state)
                {
                    try { $identity = self::identity($state['warning_rule'], $state['data_item_type'], $state['data_item_id'], $state['reason_key'], $state['reason_id']); }
                    catch (InvalidArgumentException $e) { continue; }
                    $key = self::identityKey($identity);
                    if (isset($sources[$key]) && (int) $state['reviewing_user'] > 0)
                        $states[$key][] = $state;
                }
                foreach ($this->_db->getAllAssoc("SELECT new_value FROM history WHERE the_field = 'warning_review' AND " . $scope .
                    ' AND set_date >= ' . $this->_db->makeQueryString($since . ' 00:00:00') . ' AND set_date <= NOW()') as $event)
                {
                    $state = json_decode($event['new_value'], true);
                    if (!is_array($state)) continue;
                    try { $identity = self::identity($state['warning_rule'] ?? null, $state['data_item_type'] ?? null,
                        $state['data_item_id'] ?? null, $state['reason_key'] ?? null, $state['reason_id'] ?? null); }
                    catch (InvalidArgumentException $e) { continue; }
                    $key = self::identityKey($identity);
                    if (isset($sources[$key]) && (int) ($state['reviewing_user'] ?? 0) > 0)
                        $events[] = array('identity' => $identity, 'reviewer' => (int) $state['reviewing_user']);
                }
            }
        $counts = array('outstanding' => array(), 'remind_later' => array(), 'close' => array(), 'reappeared' => array());
        $rows = array();
        $users = array();
        $today = DateUtility::getAdjustedDate('Y-m-d');
        foreach ($sources as $key => $source)
        {
            if ($rule !== '' && $source['identity']['warning_rule'] !== $rule) continue;
            $personal = $states[$key] ?? array(null);
            foreach ($personal as $state)
            {
                if ($reviewer && (!$state || (int) $state['reviewing_user'] !== $reviewer)) continue;
                $suppressed = $state && $this->suppresses($state, $today) &&
                    is_string($state['evidence_hash']) && hash_equals($source['evidence'], $state['evidence_hash']);
                $reappeared = $state && $state['action'] !== 'reset' &&
                    ((is_string($state['evidence_hash']) && preg_match('/^[a-f0-9]{64}$/D', $state['evidence_hash']) &&
                        !hash_equals($source['evidence'], $state['evidence_hash'])) ||
                    (DateUtility::getCalendarDaysBetween($state['review_until'], $today) !== null && $state['review_until'] <= $today));
                $category = $suppressed ? $state['action'] : ($reappeared ? 'reappeared' : 'outstanding');
                $counts['outstanding'][$key] = true; // All sources still have their underlying condition.
                if ($category !== 'outstanding') $counts[$category][$key] = true;
                if ($status !== '' && $status !== 'outstanding' && $status !== $category) continue;
                $rows[] = array_merge($source, array('state' => $state, 'status' => $category));
                if ($state) $users[(int) $state['reviewing_user']] = true;
                if ($source['warning']['responsibleUserID']) $users[(int) $source['warning']['responsibleUserID']] = true;
            }
        }
        $names = array();
        foreach (array_chunk(array_keys($users), 200) as $batch)
            foreach ($this->_db->getAllAssoc("SELECT user_id, CONCAT(first_name, ' ', last_name) AS name FROM user WHERE user_id IN (" . implode(',', $batch) . ')') as $user)
                $names[$user['user_id']] = $user['name'];
        $eventCount = 0;
        foreach ($events as $event)
            if (($rule === '' || $event['identity']['warning_rule'] === $rule) && (!$reviewer || $event['reviewer'] === $reviewer)) ++$eventCount;
        return array('rows' => $rows, 'counts' => array_map('count', $counts), 'events' => $eventCount,
            'since' => $since, 'names' => $names);
    }

    /** Available native audit for an enumerated, still-authorised source/reviewer. */
    public function getOversightHistory($identity, $reviewer)
    {
        $result = $this->getOversight($identity['warning_rule'], '', $reviewer,
            $identity['data_item_type'] === DATA_ITEM_COMPANY ? $identity['data_item_id'] : null);
        foreach ($result['rows'] as $row)
            if ($row['identity'] === $identity)
            {
                $events = array();
                foreach ((new History())->getAll($identity['data_item_type'], $identity['data_item_id']) as $event)
                {
                    if ($event['theField'] !== 'warning_review') continue;
                    $state = json_decode($event['newValue'], true);
                    if (!is_array($state) || (int) ($state['reviewing_user'] ?? 0) !== $reviewer) continue;
                    foreach ($identity as $field => $value)
                        if (($state[$field] ?? null) != $value) continue 2;
                    $events[] = array('action' => $state['action'], 'closeReason' => $state['close_reason'],
                        'reviewUntil' => $state['review_until'], 'date' => $event['dateModified'], 'actor' => $event['enteredByFullName']);
                }
                return $events;
            }
        throw new RuntimeException('Warning source unavailable.');
    }

    /** Corrupt/unsupported state must never hide a warning. Expiry is date-only. */
    private function suppresses($state, $today)
    {
        if (!in_array($state['action'], array('close', 'remind_later'), true) ||
            (int) $state['version'] < 1 || !is_string($state['evidence_hash']) ||
            !preg_match('/^[a-f0-9]{64}$/D', $state['evidence_hash']) ||
            !DateUtility::isValidDatabaseDateTime($state['action_at']) ||
            !is_string($state['review_until']) ||
            !preg_match('/^([1-9][0-9]{3})-([0-9]{2})-([0-9]{2})$/D', $state['review_until'], $parts) ||
            !checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) return false;
        if ($state['review_until'] <= $today) return false;
        // Both deadlines were validated by save(). Close is an approximate-month
        // pause: a later session offset must not reconstruct or change its limit.
        if ($state['action'] === 'remind_later') return $state['close_reason'] === null;
        return in_array($state['close_reason'], array('already_handled', 'not_applicable', 'incorrect_warning', 'no_further_action'), true);
    }

    /** Current actor only; source visibility is required even for stored state. */
    public function get($rule, $type, $id, $reason, $reasonID = 0)
    {
        $identity = self::identity($rule, $type, $id, $reason, $reasonID);
        $evidence = $this->source($identity);
        $state = $this->_db->getAssoc('SELECT * FROM warning_review WHERE ' . $this->where($identity, $this->actor()));
        return array('identity' => $identity, 'evidence' => $evidence,
            'version' => $state ? (int) $state['version'] : 0, 'state' => $state ?: null);
    }

    private function authorizeWrite($identity)
    {
        $module = array(DATA_ITEM_COMPANY => 'companies', DATA_ITEM_JOBORDER => 'joborders', DATA_ITEM_TASK => 'tasks')[$identity['data_item_type']];
        if ($_SESSION['CATS']->getAccessLevel($module . '.show') < ACCESS_LEVEL_READ ||
            $_SESSION['CATS']->getAccessLevel($module . '.edit') < ACCESS_LEVEL_EDIT)
            throw new RuntimeException('Warning review denied.');
        if ($module === 'tasks' && !(new Tasks())->getActionPermissions($identity['data_item_id'])['edit'])
            throw new RuntimeException('Warning review denied.');
        if ($identity['warning_rule'] === 'W08' && $_SESSION['CATS']->getRealAccessLevel() < ACCESS_LEVEL_SA)
            throw new RuntimeException('Warning review denied.');
    }

    /**
     * HTTP callers must use the existing POST/CSRF guard. Version + request hash makes retries
     * idempotent and prevents old requests from overwriting newer decisions.
     */
    public function save($rule, $type, $id, $reason, $reasonID, $action, $closeReason,
        $reviewUntil, $expectedVersion, $expectedEvidence, $requestID, $reviewingUser = null)
    {
        $identity = self::identity($rule, $type, $id, $reason, $reasonID);
        $actor = $this->actor();
        if ($reviewingUser !== null)
        {
            $this->authorizeOversight();
            if (!is_int($reviewingUser) || $reviewingUser <= 0 || $reviewingUser > 2147483647 ||
                $action !== 'reset' || $_SESSION['CATS']->getRealAccessLevel() < ACCESS_LEVEL_SA)
                throw new RuntimeException('Warning review reset denied.');
            $actor = $reviewingUser;
        }
        if (!is_int($expectedVersion) || $expectedVersion < 0 || $expectedVersion >= 2147483647 ||
            !is_string($expectedEvidence) || !preg_match('/^[a-f0-9]{64}$/D', $expectedEvidence) ||
            !is_string($requestID) || !preg_match('/^[A-Za-z0-9_-]{16,64}$/D', $requestID) ||
            !is_string($action) || !in_array($action, array('close', 'remind_later', 'reset'), true))
            throw new InvalidArgumentException('Invalid warning review request.');
        // Identity validation already constrains each rule's source and reason.
        if ((!in_array($rule, array('W02', 'W03'), true) &&
            !($rule === 'W07' && in_array($reason, array('stale_relationship', 'no_next_action'), true))) ||
            ($rule !== 'W07' && $action === 'close'))
            throw new InvalidArgumentException('Warning review action is not approved.');
        if (($action === 'close' && (!is_string($closeReason) || !in_array($closeReason,
            array('already_handled', 'not_applicable', 'incorrect_warning', 'no_further_action'), true))) ||
            ($action !== 'close' && $closeReason !== null))
            throw new InvalidArgumentException('Invalid close reason.');
        if ($action === 'reset')
        {
            if ($reviewUntil !== null) throw new InvalidArgumentException('Reset has no review deadline.');
        }
        elseif (!is_string($reviewUntil) || !preg_match('/^([1-9][0-9]{3})-([0-9]{2})-([0-9]{2})$/D', $reviewUntil, $parts) ||
            !checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1]))
            throw new InvalidArgumentException('Invalid review deadline.');
        $requestHash = hash('sha256', json_encode(array($identity, $action, $closeReason, $reviewUntil,
            $expectedVersion, $expectedEvidence, $requestID), JSON_THROW_ON_ERROR));
        if (!$this->_db->beginTransaction()) throw new RuntimeException('Warning review requires its own transaction.');
        try
        {
            // Lock an existing row even for a first review. This serializes personal
            // writes without an insert race or a second lock/idempotency table.
            $user = $this->_db->getAssoc('SELECT access_level FROM user WHERE user_id = ' . $actor . ' FOR UPDATE');
            if (!$user || $user['access_level'] <= ACCESS_LEVEL_DISABLED) throw new RuntimeException('Warning review denied.');
            $table = array(DATA_ITEM_COMPANY => 'company', DATA_ITEM_JOBORDER => 'joborder', DATA_ITEM_TASK => 'task')[$identity['data_item_type']];
            if (!$this->_db->getAssoc('SELECT ' . $table . '_id FROM ' . $table . ' WHERE ' . $table . '_id = ' . $identity['data_item_id'] . ' FOR UPDATE'))
                throw new RuntimeException('Warning source unavailable.');
            $this->authorizeWrite($identity);
            $evidence = $this->source($identity, $reviewingUser !== null);
            $where = $this->where($identity, $actor);
            $before = $this->_db->getAssoc('SELECT * FROM warning_review WHERE ' . $where . ' FOR UPDATE');
            if ($before && (int) $before['version'] === $expectedVersion + 1 && hash_equals($before['request_hash'], $requestHash))
            {
                if (!$this->_db->commitTransaction()) throw new RuntimeException('Unable to commit warning review.');
                return (int) $before['version'];
            }
            if (($before ? (int) $before['version'] : 0) !== $expectedVersion || !hash_equals($evidence, $expectedEvidence))
                throw new RuntimeException('Warning review is stale. Reload the source.');
            if ($action === 'reset' && !$before) throw new InvalidArgumentException('No warning review to reset.');
            $today = DateUtility::getAdjustedDate('Y-m-d');
            if ($action === 'close' && ($reviewUntil <= $today || $reviewUntil > (new DateTimeImmutable($today))->modify('+30 days')->format('Y-m-d')))
                throw new InvalidArgumentException('Close requires a future deadline within 30 calendar days.');
            if ($action === 'remind_later')
            {
                $dates = self::reminderDates();
                if (!in_array($reviewUntil, $dates, true)) throw new InvalidArgumentException('Invalid reminder deadline.');
            }
            $after = array_merge(array('reviewing_user' => $actor), $identity, array('action' => $action,
                'close_reason' => $closeReason, 'action_at' => $this->_db->getAssoc('SELECT NOW() AS clock')['clock'],
                'review_until' => $reviewUntil, 'evidence_hash' => $evidence, 'request_hash' => $requestHash,
                'version' => $expectedVersion + 1));
            $values = array();
            foreach ($after as $key => $value) $values[] = $key . ' = ' . $this->_db->makeQueryStringOrNULL($value);
            $sql = $before ? 'UPDATE warning_review SET ' . implode(', ', $values) . ' WHERE ' . $where :
                'INSERT INTO warning_review SET ' . implode(', ', $values);
            if (!$this->_db->query($sql, true)) throw new RuntimeException('Unable to save warning review.');
            if (!(new History())->storeHistoryData($type, $id, 'warning_review',
                $before ? json_encode($before, JSON_THROW_ON_ERROR) : null, json_encode($after, JSON_THROW_ON_ERROR),
                '(USER) recorded personal ' . $rule . ' review: ' . $reason . ' / ' . $action . '.'))
                throw new RuntimeException('Unable to record warning review history.');
            if (!$this->_db->commitTransaction()) throw new RuntimeException('Unable to commit warning review.');
            return $expectedVersion + 1;
        }
        catch (Throwable $e)
        {
            $this->_db->rollbackTransaction();
            throw $e;
        }
    }
}
