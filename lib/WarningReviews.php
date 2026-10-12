<?php
/** Personal review persistence only. No warning query consumes this state in 4C-1. */
include_once(LEGACY_ROOT . '/lib/Tasks.php');

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
    private function source($identity)
    {
        $this->actor();
        foreach ((new Tasks())->getRecruitmentWarnings()['warnings'] as $row)
        {
            if ($row['code'] !== $identity['warning_rule'] || $row['entityType'] != $identity['data_item_type'] ||
                $row['entityID'] != $identity['data_item_id']) continue;
            if ($row['code'] === 'W07')
            {
                $company = $row['relationshipAttention'];
                if (!in_array($identity['reason_key'], $company['reasons'], true)) continue;
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
                    if ($task === null) continue;
                    $evidence[] = array($task['taskID'], $task['dueDate'], $task['assignedTo']);
                }
                elseif ($identity['reason_key'] === 'stale_relationship') $evidence[] = $company['lastContact'];
                // no_next_action is the source query's authoritative absence of qualifying commitments.
            }
            else
            {
                if ($row['reason'] !== $identity['reason_key']) continue;
                $evidence = array($row['sourceDate'], $row['responsibleUserID'], $row['evidence']);
            }
            return hash('sha256', json_encode(array($identity, $evidence), JSON_THROW_ON_ERROR));
        }
        throw new RuntimeException('Warning source unavailable.');
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
     * Internal model API, intentionally not wired to a controller. Future HTTP callers
     * must use the existing POST/CSRF guard. Version + request hash makes retries
     * idempotent and prevents old requests from overwriting newer decisions.
     */
    public function save($rule, $type, $id, $reason, $reasonID, $action, $closeReason,
        $reviewUntil, $expectedVersion, $expectedEvidence, $requestID)
    {
        $identity = self::identity($rule, $type, $id, $reason, $reasonID);
        $actor = $this->actor();
        if (!is_int($expectedVersion) || $expectedVersion < 0 || $expectedVersion >= 2147483647 ||
            !is_string($expectedEvidence) || !preg_match('/^[a-f0-9]{64}$/D', $expectedEvidence) ||
            !is_string($requestID) || !preg_match('/^[A-Za-z0-9_-]{16,64}$/D', $requestID) ||
            !is_string($action) || !in_array($action, array('close', 'remind_later', 'reset'), true))
            throw new InvalidArgumentException('Invalid warning review request.');
        // Only ordinary W07 relationship policies are settled. Do not use W07 to
        // bypass W01 restrictions for a Task, or invent W02/W03 cutover policy.
        if ($rule !== 'W07' || !in_array($reason, array('stale_relationship', 'no_next_action'), true))
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
            $evidence = $this->source($identity);
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
                $dates = array();
                foreach (array(1, 5, 10) as $days) $dates[] = substr(DateUtility::addWorkingDays($today . ' 00:00:00', $days), 0, 10);
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
