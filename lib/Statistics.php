<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

include_once(LEGACY_ROOT . '/lib/Pipelines.php');
include_once(LEGACY_ROOT . '/lib/JobOrderStatuses.php');

/**
 *	Statistics Library
 *	@package    CATS
 *	@subpackage Library
 */
class Statistics
{
    private $_db;
    private $_timeZoneOffset;


    public function __construct()
    {
        $this->_db = DatabaseConnection::getInstance();

        // FIXME: Session coupling...
        $this->_timeZoneOffset = $_SESSION['CATS']->getTimeZoneOffset();
    }


    /** Strict GET contract shared by the landing page and its contributing-record views. */
    public static function operationsFilters($input)
    {
        $filters = array('range' => 'month', 'start' => '', 'end' => '', 'recruiter' => 'all',
            'desk' => 'all', 'view' => 'overview', 'page' => '1', 'comparisonPage' => '1',
            'milestone'=>'submissions', 'outcomeSort'=>'date', 'outcomeDirection'=>'desc');
        foreach ($filters as $key => $default)
        {
            if (isset($input[$key]) && !is_string($input[$key])) throw new InvalidArgumentException('Invalid reporting filter.');
            if (isset($input[$key])) $filters[$key] = $input[$key];
        }
        if (!in_array($filters['range'], array('week','month','quarter','custom'), true) ||
            !in_array($filters['view'], array('overview','activity','jobs','tasks','warnings','outcomes'), true))
            throw new InvalidArgumentException('Invalid reporting selection.');
        if (!in_array($filters['milestone'], array('submissions','interviews','offers','placements'), true) ||
            !in_array($filters['outcomeSort'], array('date','candidate','job','repeats'), true) ||
            !in_array($filters['outcomeDirection'], array('asc','desc'), true))
            throw new InvalidArgumentException('Invalid outcome selection.');
        foreach (array('recruiter','desk','page','comparisonPage') as $field)
            if (!(in_array($field, array('recruiter','desk'), true) && $filters[$field] === 'all') &&
                (!preg_match('/^(0|[1-9][0-9]*)$/D', $filters[$field]) || strlen($filters[$field]) > 10 || $filters[$field] > 2147483647))
                throw new InvalidArgumentException('Invalid reporting scope.');
        if ($filters['page'] === '0' || $filters['comparisonPage'] === '0') throw new InvalidArgumentException('Invalid reporting page.');
        $today = new DateTimeImmutable(DateUtility::getAdjustedDate('Y-m-d'));
        if ($filters['range'] !== 'custom')
        {
            $filters['end'] = $today->format('Y-m-d');
            $filters['start'] = match ($filters['range']) {
                'week' => $today->modify('monday this week')->format('Y-m-d'),
                'month' => $today->format('Y-m-01'),
                'quarter' => sprintf('%s-%02d-01', $today->format('Y'), 1 + 3 * intdiv((int) $today->format('n') - 1, 3))
            };
        }
        foreach (array('start','end') as $field)
            if (!DateUtility::isValidDatabaseDateTime($filters[$field] . ' 00:00:00'))
                throw new InvalidArgumentException('Choose valid reporting dates.');
        if ($filters['start'] > $filters['end'] || $filters['end'] > $today->format('Y-m-d') ||
            DateUtility::getCalendarDaysBetween($filters['start'], $filters['end']) > 366)
            throw new InvalidArgumentException('Choose an ordered reporting range of at most 367 days ending today or earlier.');
        return $filters;
    }

    /** Equal elapsed calendar-day windows; a shorter previous month/quarter caps both windows. */
    public static function operationsComparisonPeriod($filters)
    {
        $filters = self::operationsFilters($filters);
        $start = new DateTimeImmutable($filters['start']);
        $days = DateUtility::getCalendarDaysBetween($filters['start'], $filters['end']) + 1;
        $previous = $start->modify(match ($filters['range']) {
            'week' => '-7 days', 'month' => '-1 month', 'quarter' => '-3 months',
            'custom' => '-' . $days . ' days'
        });
        if (in_array($filters['range'], array('month','quarter'), true))
        {
            $capacity = (int) $previous->diff($start)->format('%a');
            $days = min($days, $capacity);
        }
        return array('currentStart'=>$start->format('Y-m-d'), 'currentEnd'=>$start->modify('+' . ($days - 1) . ' days')->format('Y-m-d'),
            'previousStart'=>$previous->format('Y-m-d'), 'previousEnd'=>$previous->modify('+' . ($days - 1) . ' days')->format('Y-m-d'),
            'days'=>$days);
    }

    /** Native read population only. Ownership and Desk selections never grant access. */
    public function getOperations($filters)
    {
        include_once(LEGACY_ROOT . '/lib/Tasks.php');
        include_once(LEGACY_ROOT . '/lib/ActivityEntries.php');
        include_once(LEGACY_ROOT . '/lib/WarningReviews.php');
        include_once(LEGACY_ROOT . '/lib/Pager.php');
        $filters = self::operationsFilters($filters);
        if (!isset($_SESSION['CATS']) || !$_SESSION['CATS']->isLoggedIn() || $_SESSION['CATS']->getUserID() <= 0 ||
            $_SESSION['CATS']->getAccessLevel('reports.show') < ACCESS_LEVEL_READ)
            throw new RuntimeException('Reports unavailable.');
        $actor = $this->_db->getAssoc('SELECT access_level FROM user WHERE user_id = ' . (int) $_SESSION['CATS']->getUserID());
        if (!$actor || $actor['access_level'] <= ACCESS_LEVEL_DISABLED) throw new RuntimeException('Reports unavailable.');
        if ($filters['view'] === 'outcomes')
        {
            $outcomes = $this->getRecruitmentOutcomes($filters);
            return array('filters'=>$filters, 'choices'=>$outcomes['choices'], 'outcomes'=>$outcomes);
        }
        $can = fn($key) => $_SESSION['CATS']->getAccessLevel($key) >= ACCESS_LEVEL_READ;
        $available = array('activity' => $can('activity.listByViewDataGrid'),
            'jobs' => $can('joborders.list') && $can('joborders.show'),
            'tasks' => $can('tasks.list') && $can('tasks.show'));
        $available['warnings'] = $available['tasks'] && $available['jobs'] && $can('candidates.show') &&
            $can('companies.show') && $can('contacts.show') && $available['activity'];
        $totals = array('activity'=>0, 'jobs'=>0, 'tasks'=>0, 'overdue'=>0, 'warnings'=>0);
        $groups = array('recruiter'=>array(), 'desk'=>array());
        $choices = array('recruiter'=>array(), 'desk'=>array());
        // Zero-activity users are included only as comparison labels, never as authority.
        $users = $this->_db->getAllAssoc("SELECT user_id, CONCAT_WS(' ',first_name,last_name) AS name, access_level FROM user ORDER BY first_name,last_name,user_id");
        $userNames = array_column($users, 'name', 'user_id');
        $userNames[0] = 'Unassigned';
        $deskNames = array_column((new Desks())->getAll(true), 'name', 'deskID');
        $deskNames[0] = 'Unassigned / no Job Desk';
        $details = array();
        $detailCount = 0;
        $trend = array();
        $dispositions = array('remind_later'=>array(), 'close'=>array(), 'reappeared'=>array());
        $reviewActions = 0;
        $repeatedReviews = 0;
        $warningJobs = array();
        $offset = ((int) $filters['page'] - 1) * 25;
        $empty = array('activity'=>0,'calls'=>0,'conversations'=>0,'emails'=>0,'meetings'=>0,'statusChanges'=>0,'other'=>0,'jobs'=>0,'tasks'=>0,'overdue'=>0,'warnings'=>0);
        $add = function ($kind, $row) use (&$totals, &$groups, &$choices, &$details, &$detailCount, &$trend,
            $filters, $offset, $empty, $userNames, $deskNames) {
            $actor = max(0, (int) ($row['actor'] ?? 0));
            $desk = max(0, (int) ($row['desk'] ?? 0));
            // Filter options are derived from readable contributing records before scope filtering.
            $choices['recruiter'][$actor] = $userNames[$actor] ?? ('Former user #' . $actor);
            $choices['desk'][$desk] = $deskNames[$desk] ?? ('Former Desk #' . $desk);
            if (($filters['recruiter'] !== 'all' && (int) $filters['recruiter'] !== $actor) ||
                ($filters['desk'] !== 'all' && (int) $filters['desk'] !== $desk)) return false;
            ++$totals[$kind];
            foreach (array('recruiter'=>$actor,'desk'=>$desk) as $dimension=>$key)
            {
                if (!isset($groups[$dimension][$key])) $groups[$dimension][$key] = $empty;
                ++$groups[$dimension][$key][$kind];
                if ($kind === 'tasks' && $row['overdue']) ++$groups[$dimension][$key]['overdue'];
                if ($kind === 'activity')
                {
                    if (in_array((int) $row['type'], array(ACTIVITY_CALL,ACTIVITY_CALL_TALKED,ACTIVITY_CALL_LVM,ACTIVITY_CALL_MISSED), true)) ++$groups[$dimension][$key]['calls'];
                    if ((int) $row['type'] === ACTIVITY_CALL_TALKED) ++$groups[$dimension][$key]['conversations'];
                    if ((int) $row['type'] === ACTIVITY_EMAIL) ++$groups[$dimension][$key]['emails'];
                    if ((int) $row['type'] === ACTIVITY_MEETING) ++$groups[$dimension][$key]['meetings'];
                    if ((int) $row['type'] === ACTIVITY_STATUS_CHANGE) ++$groups[$dimension][$key]['statusChanges'];
                    if (!in_array((int) $row['type'], array(ACTIVITY_CALL,ACTIVITY_CALL_TALKED,ACTIVITY_CALL_LVM,
                        ACTIVITY_CALL_MISSED,ACTIVITY_EMAIL,ACTIVITY_MEETING,ACTIVITY_STATUS_CHANGE), true))
                        ++$groups[$dimension][$key]['other'];
                }
            }
            if ($kind === 'tasks' && $row['overdue']) ++$totals['overdue'];
            if ($kind === 'activity') $trend[$row['sourceDate']] = ($trend[$row['sourceDate']] ?? 0) + 1;
            if ($filters['view'] === $kind)
            {
                if ($detailCount >= $offset && $detailCount < $offset + 25)
                    $details[] = $row + array('actorName'=>$userNames[$actor] ?? ('Former user #' . $actor),
                        'deskName'=>$deskNames[$desk] ?? ('Former Desk #' . $desk));
                ++$detailCount;
            }
            return true;
        };
        foreach ((new ActivityEntries())->getOperationsRows($filters['start'], $filters['end']) as $row)
        {
            $module = array(100=>'candidates',200=>'companies',300=>'contacts',400=>'joborders')[(int) $row['dataItemType']];
            $row['url'] = CATSUtility::getIndexName() . '?m=' . $module . '&a=show&' .
                array(100=>'candidateID',200=>'companyID',300=>'contactID',400=>'jobOrderID')[(int) $row['dataItemType']] . '=' . (int) $row['dataItemID'];
            $add('activity', $row);
        }
        $comparison = null;
        if ($filters['view'] === 'activity' && $available['activity'])
        {
            $comparison = self::operationsComparisonPeriod($filters) + array('current'=>0,'previous'=>0);
            foreach ($trend as $date=>$count)
                if ($date >= $comparison['currentStart'] && $date <= $comparison['currentEnd']) $comparison['current'] += $count;
            // Only the missing historical window is acquired; the current period reuses the authorised trend.
            foreach ((new ActivityEntries())->getOperationsRows($comparison['previousStart'], $comparison['previousEnd']) as $row)
            {
                $actorID = max(0, (int) $row['actor']);
                $deskID = max(0, (int) $row['desk']);
                if (($filters['recruiter'] === 'all' || (int) $filters['recruiter'] === $actorID) &&
                    ($filters['desk'] === 'all' || (int) $filters['desk'] === $deskID)) ++$comparison['previous'];
            }
        }
        foreach ((new JobOrders())->getOperationsRows(true) as $row)
        {
            $row['url'] = CATSUtility::getIndexName() . '?m=joborders&a=show&jobOrderID=' . (int) $row['id'];
            $add('jobs', $row);
        }
        // Resolve Task Desk through its readable explicit Job or pipeline; never through assignee membership.
        $batch = array();
        $consume = function ($batch) use ($add, $can) {
            if (!$batch) return;
            $ids = array_column($batch, 'candidateJobOrderID');
            $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);
            $pipelines = $ids ? array_column($this->_db->getAllAssoc('SELECT candidate_joborder_id,joborder_id FROM candidate_joborder WHERE candidate_joborder_id IN (' . implode(',', $ids) . ')'), 'joborder_id','candidate_joborder_id') : array();
            $jobIDs = array();
            foreach ($batch as $task)
            {
                $id = $task['dataItemType'] == DATA_ITEM_JOBORDER ? $task['dataItemID'] : ($pipelines[(int) $task['candidateJobOrderID']] ?? null);
                if ($id > 0) $jobIDs[] = (int) $id;
            }
            $desks = $jobIDs && $can('joborders.show') ? array_column($this->_db->getAllAssoc('SELECT joborder_id,desk_id FROM joborder WHERE joborder_id IN (' . implode(',', array_unique($jobIDs)) . ')'), 'desk_id','joborder_id') : array();
            foreach ($batch as $task)
            {
                $jobID = $task['dataItemType'] == DATA_ITEM_JOBORDER ? $task['dataItemID'] : ($pipelines[(int) $task['candidateJobOrderID']] ?? null);
                $add('tasks', array('id'=>$task['taskID'],'title'=>$task['title'],'actor'=>$task['assignedTo'],
                    'desk'=>$desks[(int) $jobID] ?? null, 'sourceDate'=>$task['dueDate'],
                    'overdue'=>DateUtility::getCalendarDaysBetween($task['dueDate'], DateUtility::getAdjustedDate('Y-m-d')) > 0,
                    'url'=>CATSUtility::getIndexName() . '?m=tasks&a=show&taskID=' . (int) $task['taskID'], 'status'=>$task['status']));
            }
        };
        foreach ((new Tasks())->getOperationsRows() as $task)
        {
            $batch[] = $task;
            if (count($batch) === 200) { $consume($batch); $batch = array(); }
        }
        $consume($batch);
        $oversight = $_SESSION['CATS']->getAccessLevel('reports.show') >= ACCESS_LEVEL_SA;
        $warnings = array();
        if ($available['warnings'])
        {
            if ($oversight)
            {
                $result = (new WarningReviews())->getOversight('', '', 0, null, $filters['start'], $filters['end']);
                $warnings = $result['rows'];
            }
            else
                foreach ((new Tasks())->getRecruitmentWarnings()['warnings'] as $warning)
                    $warnings[] = array('warning'=>$warning,'state'=>null,'status'=>'outstanding');
        }
        // Reuse the existing enumeration. W07 remains the viewer's account scope.
        $jobIDs = array();
        foreach ($warnings as $row) if ($row['warning']['jobOrderID']) $jobIDs[] = (int) $row['warning']['jobOrderID'];
        $jobDesks = array();
        foreach (array_chunk(array_unique($jobIDs), 200) as $ids)
            foreach ($this->_db->getAllAssoc('SELECT joborder_id,desk_id FROM joborder WHERE joborder_id IN (' . implode(',', $ids) . ')') as $job)
                $jobDesks[$job['joborder_id']] = $job['desk_id'];
        $warningSubjects = array();
        $warningGroups = array('recruiter'=>array(),'desk'=>array());
        $acceptedKeys = array();
        foreach ($warnings as $item)
        {
            $w = $item['warning'];
            $key = isset($item['identity']) ? implode(':', array_values($item['identity'])) : $w['identity'];
            $subject = $w['entityType'] . ':' . $w['entityID'];
            $actor = max(0, (int) $w['responsibleUserID']);
            $desk = max(0, (int) ($jobDesks[(int) $w['jobOrderID']] ?? 0));
            $choices['recruiter'][$actor] = $userNames[$actor] ?? ('Former user #' . $actor);
            $choices['desk'][$desk] = $deskNames[$desk] ?? ('Former Desk #' . $desk);
            if (($filters['recruiter'] !== 'all' && (int) $filters['recruiter'] !== $actor) ||
                ($filters['desk'] !== 'all' && (int) $filters['desk'] !== $desk)) continue;
            foreach (array('recruiter'=>$actor,'desk'=>$desk) as $dimension=>$id)
                $warningGroups[$dimension][$id][$subject] = true;
            if (isset($dispositions[$item['status']])) $dispositions[$item['status']][$key] = true;
            $acceptedKeys[$key] = true;
            if ($w['jobOrderID']) $warningJobs[$w['jobOrderID']] = true;
            $type = (int) $w['entityType'];
            $route = array(DATA_ITEM_TASK=>array('tasks','taskID'), DATA_ITEM_JOBORDER=>array('joborders','jobOrderID'), DATA_ITEM_COMPANY=>array('companies','companyID'))[$type];
            if (!isset($warningSubjects[$subject]))
                $warningSubjects[$subject] = array('id'=>$w['entityID'],'title'=>$w['title'],'actor'=>$actor,'desk'=>$desk,
                    'sourceDate'=>$w['sourceDate'], 'conditions'=>array(), 'actors'=>array(),
                    'url'=>CATSUtility::getIndexName() . '?m=' . $route[0] . '&a=show&' . $route[1] . '=' . (int) $w['entityID']);
            $warningSubjects[$subject]['conditions'][$w['code'] . ': ' . str_replace('_',' ',$w['reason'])] = true;
            $warningSubjects[$subject]['actors'][$actor] = $userNames[$actor] ?? ('Former user #' . $actor);
        }
        foreach ($warningSubjects as $row)
        {
            $row['status'] = implode('; ', array_keys($row['conditions']));
            $row['actorName'] = implode(', ', $row['actors']);
            unset($row['conditions'], $row['actors']);
            $add('warnings', $row);
        }
        // Distinct subjects per native responsibility may overlap across recruiters.
        foreach ($warningGroups as $dimension=>$byID)
            foreach ($byID as $id=>$sourceSubjects)
            {
                if (!isset($groups[$dimension][$id])) $groups[$dimension][$id] = $empty;
                $groups[$dimension][$id]['warnings'] = count($sourceSubjects);
            }
        if ($oversight && isset($result))
            foreach ($result['eventCounts'] as $key=>$count)
                if (isset($acceptedKeys[$key]))
                {
                    $reviewActions += $count;
                    if ($count > 1) ++$repeatedReviews;
                }
        // Current comparisons/selectors follow native Users::getSelectList() eligibility.
        // Historical Activity keeps its recorded contributors regardless of current user status.
        if ($filters['view'] !== 'activity')
        {
            $levels = array_column($users, 'access_level', 'user_id');
            foreach ($choices['recruiter'] as $id=>$name)
                if ($id > 0 && (!isset($levels[$id]) || $levels[$id] <= ACCESS_LEVEL_DISABLED))
                    unset($choices['recruiter'][$id]);
            foreach ($groups['recruiter'] as $id=>$row)
                if ($id > 0 && (!isset($levels[$id]) || $levels[$id] <= ACCESS_LEVEL_DISABLED))
                    unset($groups['recruiter'][$id]);
        }
        foreach (array('recruiter','desk') as $dimension)
        {
            $names = $dimension === 'recruiter' ? $userNames : $deskNames;
            foreach ($groups[$dimension] as $key=>&$row) $row['name'] = $names[$key] ?? ('Former #' . $key);
            unset($row);
            uasort($groups[$dimension], fn($a,$b) => array(-$a['overdue'],-$a['warnings'],-$a['jobs'],$a['name']) <=> array(-$b['overdue'],-$b['warnings'],-$b['jobs'],$b['name']));
            asort($choices[$dimension]);
            if ($filters[$dimension] !== 'all' && !array_key_exists((int) $filters[$dimension], $choices[$dimension]))
                throw new InvalidArgumentException('Reporting scope unavailable.');
        }
        ksort($trend);
        return array('filters'=>$filters, 'available'=>$available, 'totals'=>$totals, 'groups'=>$groups,
            'choices'=>$choices, 'details'=>$details, 'pager'=>new Pager($detailCount,25,(int) $filters['page']),
            'trend'=>$trend, 'comparison'=>$comparison, 'oversight'=>$oversight, 'dispositions'=>array_map('count',$dispositions),
            'reviewActions'=>$oversight ? $reviewActions : null, 'repeatedReviews'=>$oversight ? $repeatedReviews : null, 'jobsAtRisk'=>count($warningJobs),
            'outcomes'=>$filters['view']==='overview' ? $this->getRecruitmentOutcomes($filters) : null);
    }

    /** R02: period-distinct recorded attainments, never inferred recruiter credit or conversion. */
    private function getRecruitmentOutcomes($filters)
    {
        $definitions = array(
            'submissions'=>array('id'=>PIPELINE_STATUS_SUBMITTED,'label'=>'Submissions recorded'),
            'interviews'=>array('id'=>PIPELINE_STATUS_INTERVIEWING,'label'=>'Interview stage recorded'),
            'offers'=>array('id'=>PIPELINE_STATUS_OFFERED,'label'=>'Offers recorded'),
            'placements'=>array('id'=>PIPELINE_STATUS_PLACED,'label'=>'Placements recorded'));
        $canRead = $_SESSION['CATS']->getAccessLevel('candidates.show') >= ACCESS_LEVEL_READ &&
            $_SESSION['CATS']->getAccessLevel('joborders.show') >= ACCESS_LEVEL_READ;
        // Named recruiter filters refer to current operational responsibility, not a known outcome actor.
        $actorUnavailable = !in_array($filters['recruiter'], array('all','0'), true);
        $enabled = array_column((new Pipelines())->getStatuses(), 'status', 'statusID');
        $totals = array_fill_keys(array_keys($definitions), 0);
        $available = array();
        foreach ($definitions as $key=>&$definition)
        {
            $definition['configuredLabel'] = $enabled[$definition['id']] ?? null;
            $available[$key] = $canRead && !$actorUnavailable && $definition['configuredLabel'] !== null;
        }
        unset($definition);
        $comparison = self::operationsComparisonPeriod($filters);
        $current = $previous = $totals;
        $desks = array();
        $deskNames = array_column((new Desks())->getAll(true), 'name', 'deskID');
        $deskNames[0] = 'Unassigned / no Job Desk';
        $choices = array('recruiter'=>array(0=>'Unknown event actor'), 'desk'=>array());
        $trend = array();
        for ($date=new DateTimeImmutable($filters['start']); $date->format('Y-m-d') <= $filters['end']; $date=$date->modify('+1 day'))
            $trend[$date->format('Y-m-d')] = $totals;
        $metricKeys = array_combine(array_column($definitions, 'id'), array_keys($definitions));
        if ($canRead && !$actorUnavailable)
        {
            foreach ((new Pipelines())->getOutcomeRows($filters['start'], $filters['end'], array('summary'=>true)) as $row)
            {
                $key = $metricKeys[(int) $row['milestone']];
                if (!$available[$key]) continue;
                $desk = max(0, (int) $row['desk']);
                $choices['desk'][$desk] = $deskNames[$desk] ?? ('Former Desk #' . $desk);
                if ($filters['desk'] !== 'all' && (int) $filters['desk'] !== $desk) continue;
                $count = (int) $row['pairs'];
                $totals[$key] += $count;
                if ($row['sourceDate'] <= $comparison['currentEnd']) $current[$key] += $count;
                if (!isset($desks[$desk])) $desks[$desk] = array_fill_keys(array_keys($definitions),0);
                $desks[$desk][$key] += $count;
                $trend[$row['sourceDate']][$key] += $count;
            }
            foreach ((new Pipelines())->getOutcomeRows($comparison['previousStart'], $comparison['previousEnd'], array('summary'=>true)) as $row)
            {
                $key = $metricKeys[(int) $row['milestone']];
                if (!$available[$key]) continue;
                $desk = max(0, (int) $row['desk']);
                $choices['desk'][$desk] = $deskNames[$desk] ?? ('Former Desk #' . $desk);
                if ($filters['desk'] === 'all' || (int) $filters['desk'] === $desk) $previous[$key] += (int) $row['pairs'];
            }
        }
        if ($filters['view'] === 'outcomes')
        {
            if ($actorUnavailable) throw new InvalidArgumentException('Outcome event actor is unavailable.');
            if ($filters['desk'] !== 'all' && !isset($choices['desk'][(int) $filters['desk']]))
                throw new InvalidArgumentException('Outcome Desk scope unavailable.');
        }
        asort($choices['desk']);
        foreach ($desks as $id=>&$row) $row['name'] = $deskNames[$id] ?? ('Former Desk #' . $id);
        unset($row);
        uasort($desks, fn($a,$b) => strcmp($a['name'],$b['name']));
        $changes = array();
        foreach ($totals as $key=>$count)
            $changes[$key] = array('current'=>$current[$key], 'previous'=>$previous[$key],
                'absolute'=>$current[$key]-$previous[$key],
                'percent'=>$previous[$key]>0 ? 100*($current[$key]-$previous[$key])/$previous[$key] : null);
        $pager = new Pager($totals[$filters['milestone']],25,(int) $filters['page']);
        $details = array();
        if ($filters['view'] === 'outcomes' && $available[$filters['milestone']])
        {
            $options = array('milestone'=>$definitions[$filters['milestone']]['id'], 'sort'=>$filters['outcomeSort'],
                'direction'=>$filters['outcomeDirection'], 'page'=>(int) $pager->getCurrentPage());
            if ($filters['desk'] !== 'all') $options['desk'] = (int) $filters['desk'];
            foreach ((new Pipelines())->getOutcomeRows($filters['start'], $filters['end'], $options) as $row)
            {
                $row['deskName'] = $deskNames[max(0,(int) $row['desk'])] ?? ('Former Desk #' . (int) $row['desk']);
                $details[] = $row;
            }
        }
        $weeks = array();
        foreach ($trend as $date=>$counts)
        {
            $week = (new DateTimeImmutable($date))->modify('monday this week');
            $name = $week->format('Y-m-d');
            if ($name < $filters['start'] || $week->modify('+6 days')->format('Y-m-d') > $filters['end']) $name .= ' (partial)';
            if (!isset($weeks[$name])) $weeks[$name] = array_fill_keys(array_keys($definitions),0) + array('name'=>$name);
            foreach ($counts as $key=>$count) $weeks[$name][$key] += $count;
        }
        return array('definitions'=>$definitions, 'available'=>$available, 'totals'=>$totals,
            'comparison'=>$comparison, 'changes'=>$changes, 'desks'=>$desks, 'choices'=>$choices,
            'trend'=>$trend, 'weeks'=>array_values($weeks), 'details'=>$details, 'pager'=>$pager, 'actorUnavailable'=>$actorUnavailable,
            'timeZoneOffset'=>(float) $_SESSION['CATS']->getTimeZoneOffset());
    }

    /**
     * Returns the total number of candidates created in the given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getCandidateCount($period)
    {
        $criterion = $this->makePeriodCriterion('date_created', $period);

        $sql = sprintf(
            "SELECT
                COUNT(*) AS candidate_count
            FROM
                candidate
            WHERE
                1=1
            %s",
            $criterion
        );
        $rs = $this->_db->getAssoc($sql);

        return $rs['candidate_count'];
    }

    /**
     * Returns the total number of submissions created in the given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getSubmissionCount($period)
    {
        $criterion = $this->makePeriodCriterion('date', $period);

        $sql = sprintf(
            "SELECT
                COUNT(*) AS submissionCount
            FROM
                candidate_joborder_status_history
            LEFT JOIN joborder
                ON joborder.joborder_id = candidate_joborder_status_history.joborder_id
            WHERE
                status_to = 400
            AND
                joborder.status IN %s
            %s",
            JobOrderStatuses::getStatisticsStatusSQL(),
            $criterion
        );
        $rs = $this->_db->getAssoc($sql);

        return $rs['submissionCount'];
    }

	/**
     * Returns the total number of placements in the given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getPlacementCount($period)
    {
        $criterion = $this->makePeriodCriterion('date', $period);

        $sql = sprintf(
            "SELECT
                COUNT(*) AS placementCount
            FROM
                candidate_joborder_status_history
            WHERE
                status_to = 800
            %s",
            $criterion
        );
        $rs = $this->_db->getAssoc($sql);

        return $rs['placementCount'];
    }

    /**
     * Returns the total number of companies created in the given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getCompanyCount($period)
    {
        $criterion = $this->makePeriodCriterion('date_created', $period);

        $sql = sprintf(
            "SELECT
                COUNT(*) AS company_count
            FROM
                company
            WHERE
                1=1
            %s",
            $criterion
        );
        $rs = $this->_db->getAssoc($sql);

        return $rs['company_count'];
    }

    /**
     * Returns the total number of contacts created in the given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getContactCount($period)
    {
        $criterion = $this->makePeriodCriterion('date_created', $period);

        $sql = sprintf(
            "SELECT
                COUNT(*) AS contact_count
            FROM
                contact
            WHERE
                1=1
            %s",
            $criterion
        );
        $rs = $this->_db->getAssoc($sql);

        return $rs['contact_count'];
    }

    /**
     * Returns the total number of job orders created in the given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getJobOrderCount($period)
    {
        $criterion = $this->makePeriodCriterion('date_created', $period);

        $sql = sprintf(
            "SELECT
                COUNT(*) AS joborder_count
            FROM
                joborder
            WHERE
                1=1
            %s",
            $criterion
        );
        $rs = $this->_db->getAssoc($sql);

        return $rs['joborder_count'];
    }

    /**
     * Returns all job orders with submissions created in the given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getSubmissionJobOrders($period)
    {
        $criterion = $this->makePeriodCriterion(
            'candidate_joborder_status_history.date', $period
        );

        $sql = sprintf(
            "SELECT
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.company_id AS companyID,
                SUM(
                    IF(candidate_joborder_status_history.status_to = 400, 1, 0)
                ) AS submittedCount,
                CONCAT(
                    owner_user.first_name, ' ', owner_user.last_name
                ) AS ownerFullName,
                company.name AS companyName
            FROM
                joborder
            LEFT OUTER JOIN candidate_joborder_status_history
                ON candidate_joborder_status_history.joborder_id = joborder.joborder_id
                %s
            LEFT JOIN company
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS owner_user
                ON owner_user.user_id = joborder.owner
            WHERE
                joborder.status IN %s
            GROUP BY
                jobOrderID
            HAVING
                submittedCount > 0",
            $criterion,
            JobOrderStatuses::getStatisticsStatusSQL()
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all submissions for the specified job order created in the
     * given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getSubmissionsByJobOrder($period, $jobOrderID)
    {
        $criterion = $this->makePeriodCriterion(
            'candidate_joborder_status_history.date', $period
        );

        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.company_id AS companyID,
                CONCAT(
                    owner_user.first_name, ' ', owner_user.last_name
                ) AS ownerFullName,
                DATE_FORMAT(
                    candidate_joborder_status_history.date, '" . DateUtility::getMysqlDateTimeFormat() . "'
                ) AS dateSubmitted
            FROM
                candidate_joborder_status_history
            LEFT JOIN candidate
                ON candidate.candidate_id = candidate_joborder_status_history.candidate_id
            LEFT JOIN joborder
                ON joborder.joborder_id = candidate_joborder_status_history.joborder_id
            LEFT JOIN company
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS owner_user
                ON owner_user.user_id = candidate.owner
            WHERE
                candidate_joborder_status_history.joborder_id = %s
            AND
                candidate_joborder_status_history.status_to = 400
            %s
            ORDER BY
                candidate.last_name ASC,
                candidate.first_name ASC",
            $jobOrderID,
            $criterion
        );

        return $this->_db->getAllAssoc($sql);
    }
    
    /**
     * Returns all job orders with placements created in the given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getPlacementsJobOrders($period)
    {
        $criterion = $this->makePeriodCriterion(
            'candidate_joborder_status_history.date', $period
        );

        $sql = sprintf(
            "SELECT
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.company_id AS companyID,
                SUM(
                    IF(candidate_joborder_status_history.status_to = 800, 1, 0)
                ) AS submittedCount,
                CONCAT(
                    owner_user.first_name, ' ', owner_user.last_name
                ) AS ownerFullName,
                company.name AS companyName
            FROM
                joborder
            LEFT OUTER JOIN candidate_joborder_status_history
                ON candidate_joborder_status_history.joborder_id = joborder.joborder_id
                %s
            LEFT JOIN company
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS owner_user
                ON owner_user.user_id = joborder.owner
            WHERE
                joborder.status IN %s
            GROUP BY
                jobOrderID
            HAVING
                submittedCount > 0",
            $criterion,
            JobOrderStatuses::getStatisticsStatusSQL()
        );

        return $this->_db->getAllAssoc($sql);
    }
    
    /**
     * Returns all placements for the specified job order created in the
     * given period.
     *
     * @param flag statistics period flag
     * @return integer candidate count
     */
    public function getPlacementsByJobOrder($period, $jobOrderID)
    {
        $criterion = $this->makePeriodCriterion(
            'candidate_joborder_status_history.date', $period
        );

        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.company_id AS companyID,
                CONCAT(
                    owner_user.first_name, ' ', owner_user.last_name
                ) AS ownerFullName,
                DATE_FORMAT(
                    candidate_joborder_status_history.date, '" . DateUtility::getMysqlDateTimeFormat() . "'
                ) AS dateSubmitted
            FROM
                candidate_joborder_status_history
            LEFT JOIN candidate
                ON candidate.candidate_id = candidate_joborder_status_history.candidate_id
            LEFT JOIN joborder
                ON joborder.joborder_id = candidate_joborder_status_history.joborder_id
            LEFT JOIN company
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS owner_user
                ON owner_user.user_id = candidate.owner
            WHERE
                candidate_joborder_status_history.joborder_id = %s
            AND
                candidate_joborder_status_history.status_to = 800
            %s
            ORDER BY
                candidate.last_name ASC,
                candidate.first_name ASC",
            $jobOrderID,
            $criterion
        );

        return $this->_db->getAllAssoc($sql);
    }
    
    // FIXME: Document me.
    public function getActivitiesByPeriod($period)
    {
        $criterion = $this->makePeriodCriterion(
            'activity.date_occurred', $period
        );

        $sql = sprintf(
            "SELECT
                activity.activity_id AS activityID,
                DATE_FORMAT(
                    activity.date_occurred, '%%m'
                ) AS month,
                DATE_FORMAT(
                    activity.date_occurred, '%%d'
                ) AS day,
                DATE_FORMAT(
                    activity.date_occurred, '%%y'
                ) AS year
            FROM
                activity
            WHERE
                1=1
            %s",
            $criterion
        );

        return $this->_db->getAllAssoc($sql);
    }

    // FIXME: Document me.
    public function getCandidatesByPeriod($period)
    {
        $criterion = $this->makePeriodCriterion(
            'candidate.date_created', $period
        );

        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                DATE_FORMAT(
                    candidate.date_created, '%%m'
                ) AS month,
                DATE_FORMAT(
                    candidate.date_created, '%%d'
                ) AS day,
                DATE_FORMAT(
                    candidate.date_created, '%%y'
                ) AS year
            FROM
                candidate
            WHERE
                1=1
            %s",
            $criterion
        );

        return $this->_db->getAllAssoc($sql);
    }

    // FIXME: Document me.
    public function getJobOrdersByPeriod($period)
    {
        $criterion = $this->makePeriodCriterion(
            'joborder.date_created', $period
        );

        $sql = sprintf(
            "SELECT
                joborder.joborder_id AS jobOrderID,
                DATE_FORMAT(
                    joborder.date_created, '%%m'
                ) AS month,
                DATE_FORMAT(
                    joborder.date_created, '%%d'
                ) AS day,
                DATE_FORMAT(
                    joborder.date_created, '%%y'
                ) AS year
            FROM
                joborder
            WHERE
                1=1
            %s",
            $criterion
        );

        return $this->_db->getAllAssoc($sql);
    }

    // FIXME: Document me.
    public function getSubmissionsByPeriod($period)
    {
        $criterion = $this->makePeriodCriterion(
            'candidate_joborder_status_history.date', $period
        );

        $sql = sprintf(
            "SELECT
                DATE_FORMAT(
                    candidate_joborder_status_history.date, '%%m'
                ) AS month,
                DATE_FORMAT(
                    candidate_joborder_status_history.date, '%%d'
                ) AS day,
                DATE_FORMAT(
                    candidate_joborder_status_history.date, '%%y'
                ) AS year
            FROM
                candidate_joborder_status_history
            WHERE
                candidate_joborder_status_history.status_to = 400
            %s",
            $criterion
        );

        return $this->_db->getAllAssoc($sql);
    }

	// FIXME: Document me.
    public function getPlacementsByPeriod($period)
    {
        $criterion = $this->makePeriodCriterion(
            'candidate_joborder_status_history.date', $period
        );

        $sql = sprintf(
            "SELECT
                DATE_FORMAT(
                    candidate_joborder_status_history.date, '%%m'
                ) AS month,
                DATE_FORMAT(
                    candidate_joborder_status_history.date, '%%d'
                ) AS day,
                DATE_FORMAT(
                    candidate_joborder_status_history.date, '%%y'
                ) AS year
            FROM
                candidate_joborder_status_history
            WHERE
                candidate_joborder_status_history.status_to = 800
            %s",
            $criterion
        );

        return $this->_db->getAllAssoc($sql);
    }

    // FIXME: Document me.
    public function getJobOrderReport($jobOrderID)
    {
        $sql = sprintf(
            "SELECT
                joborder.joborder_id AS jobOrderID,
                joborder.company_id AS companyID,
                company.name AS companyName,
                joborder.client_job_id AS clientJobID,
                joborder.title AS title,
                joborder.city AS city,
                joborder.state AS state,
                CONCAT(
                    recruiter_user.first_name, ' ', recruiter_user.last_name
                ) AS recruiterFullName,
                CONCAT(
                    owner_user.first_name, ' ', owner_user.last_name
                ) AS ownerFullName,
                DATE_FORMAT(
                    joborder.date_created, '" . DateUtility::getMysqlDateTimeFormat() . "'
                ) AS dateCreated,
                COUNT(
                    candidate_joborder.joborder_id
                ) AS pipeline,
                (
                    SELECT
                        COUNT(*)
                    FROM
                        candidate_joborder_status_history
                    WHERE
                        joborder_id = %s
                    AND
                        status_to = %s
                ) AS submitted,
                (
                    SELECT
                        COUNT(*)
                    FROM
                        candidate_joborder_status_history
                    WHERE
                        joborder_id = %s
                    AND
                        status_to = %s
                ) AS pipelinePlaced,
                (
                    SELECT
                        COUNT(*)
                    FROM
                        candidate_joborder_status_history
                    WHERE
                        joborder_id = %s
                    AND
                        status_to = %s
                ) AS pipelineInterving
            FROM
                joborder
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            LEFT JOIN user AS recruiter_user
                ON joborder.recruiter = recruiter_user.user_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
            LEFT JOIN user AS entered_by_user
                ON joborder.entered_by = entered_by_user.user_id
            LEFT JOIN candidate_joborder
                ON joborder.joborder_id = candidate_joborder.joborder_id
            WHERE
                joborder.joborder_id = %s
            GROUP BY
                joborder.joborder_id",
            $this->_db->makeQueryInteger($jobOrderID),
            PIPELINE_STATUS_SUBMITTED,
            $this->_db->makeQueryInteger($jobOrderID),
            PIPELINE_STATUS_PLACED,
            $this->_db->makeQueryInteger($jobOrderID),
            PIPELINE_STATUS_INTERVIEWING,
            $this->_db->makeQueryInteger($jobOrderID)
        );

        return $this->_db->getAssoc($sql);
    }
    
    public function getEEOReport($modePeriod, $modeStatus)
    {
        switch ($modePeriod)
        {
            case 'month':
                $periodChriterion = 'AND TO_DAYS(candidate.date_modified) >= TO_DAYS(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))';
                break;
                
            case 'week':
                $periodChriterion = 'AND TO_DAYS(candidate.date_modified) >= TO_DAYS(DATE_SUB(CURDATE(), INTERVAL 7 DAY))';
                break;
            
            default:
                $periodChriterion = '';
                break;
        }
        
        switch ($modeStatus)
        {
            case 'placed':
                $statusChriterion = 'AND IF(candidate_joborder_status.candidate_joborder_id, 1, 0) = 1';
                $join = 'LEFT JOIN candidate_joborder AS candidate_joborder_status
                            ON candidate_joborder_status.candidate_id = candidate.candidate_id
                            AND candidate_joborder_status.status >= '.PIPELINE_STATUS_PLACED.'
                        ';
                break;
                
            case 'rejected':
                $statusChriterion = 'AND IF(candidate_joborder_status.candidate_joborder_id, 1, 0) = 1';
                $join = 'LEFT JOIN candidate_joborder AS candidate_joborder_status
                            ON candidate_joborder_status.candidate_id = candidate.candidate_id
                            AND candidate_joborder_status.status = '.PIPELINE_STATUS_NOTINCONSIDERATION.'
                        ';
                break;
            
            default:
                $statusChriterion = '';
                $join = '';
                break;
        }
        
        $chriterion = $periodChriterion . $statusChriterion;
        
        $sql = sprintf(
            "SELECT
                COUNT(candidate.candidate_id) AS totalCandidates
             FROM   
                candidate
                %s
             WHERE
                1=1
                %s
            ",
            $join,
            $chriterion
        );
        
        $statistics['rsTotalCandidates'] = $this->_db->getAssoc($sql);
        
        $sql = sprintf(
            "SELECT
                (
                    SELECT
                        COUNT(candidate.candidate_id)
                    FROM 
                        candidate
                        %s
                    WHERE
                        candidate.eeo_ethnic_type_id = eeo_ethnic_type.eeo_ethnic_type_id
                        %s
                ) AS numberOfCandidates,
                eeo_ethnic_type.eeo_ethnic_type_id as EEOEthnicTypeID,
                eeo_ethnic_type.type as EEOEthnicType
             FROM   
                eeo_ethnic_type
            ",
            $join,
            $chriterion
        );
             
        $statistics['rsEthnicStatistics'] = $this->_db->getAllAssoc($sql);

        $sql = sprintf(
            "SELECT
                (
                    SELECT
                        COUNT(candidate.candidate_id)
                    FROM 
                        candidate
                        %s
                    WHERE
                        candidate.eeo_veteran_type_id = eeo_veteran_type.eeo_veteran_type_id
                        %s
                ) AS numberOfCandidates,
                eeo_veteran_type.eeo_veteran_type_id as EEOVeteranTypeID,
                eeo_veteran_type.type as EEOVeteranType
             FROM   
                eeo_veteran_type
            ",
            $join,
            $chriterion
        );
             
        $statistics['rsVeteranStatistics'] = $this->_db->getAllAssoc($sql);

        $sql = sprintf(
            "SELECT
                (
                    SELECT
                        COUNT(candidate.candidate_id)
                    FROM 
                        candidate
                        %s
                    WHERE
                        candidate.eeo_disability_status = 'Yes'
                        %s
                ) AS numberOfCandidatesDisabled,
                (
                    SELECT
                        COUNT(candidate.candidate_id)
                    FROM 
                        candidate
                        %s
                    WHERE
                        candidate.eeo_disability_status = 'No'
                        %s
                ) AS numberOfCandidatesNonDisabled
             FROM   
                candidate
            ",
            $join,
            $chriterion,
            $join,
            $chriterion
        );
             
        $statistics['rsDisabledStatistics'] = $this->_db->getAssoc($sql);
        
        $sql = sprintf(
            "SELECT
                (
                    SELECT
                        COUNT(candidate.candidate_id)
                    FROM 
                        candidate
                        %s
                    WHERE
                        candidate.eeo_gender = 'm'
                        %s
                ) AS numberOfCandidatesMale,
                (
                    SELECT
                        COUNT(candidate.candidate_id)
                    FROM 
                        candidate
                        %s
                    WHERE
                        candidate.eeo_gender = 'f'
                        %s
                ) AS numberOfCandidatesFemale
             FROM   
                candidate
            ",
            $join,
            $chriterion,
            $join,
            $chriterion
        );
             
        $statistics['rsGenderStatistics'] = $this->_db->getAssoc($sql);
        
        return $statistics;
    }
    

    /**
     * Returns an array containing the number of candidates currently in each
     * "status" in the pipeline.
     *
     * @return array statistics data
     */
    public function getPipelineData($jobOrderID = -1)
    {
        $sql = sprintf(
            "SELECT
                COUNT(*) AS totalPipeline,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS noStatus,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) +
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS noContact,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS contacted,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS qualifying,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS submitted,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS interviewing,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS offered,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS candidateDeclined,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS passedOn,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS placed,
                SUM(IF(candidate_joborder.status = %s, 1, 0)) AS replied
            FROM
                candidate_joborder
            LEFT JOIN joborder
                ON joborder.joborder_id = candidate_joborder.joborder_id
            WHERE
                joborder.status != 'Closed'
            %s",
            PIPELINE_STATUS_NOSTATUS,
            PIPELINE_STATUS_NOCONTACT,
            PIPELINE_STATUS_NOTINCONSIDERATION,
            PIPELINE_STATUS_CONTACTED,
            PIPELINE_STATUS_QUALIFYING,
            PIPELINE_STATUS_SUBMITTED,
            PIPELINE_STATUS_INTERVIEWING,
            PIPELINE_STATUS_OFFERED,
            PIPELINE_STATUS_CANDIDATEDECLINED,
            PIPELINE_STATUS_CLIENTDECLINED,
            PIPELINE_STATUS_PLACED,
            PIPELINE_STATUS_CANDIDATE_REPLIED,
            ($jobOrderID != -1 ? "AND candidate_joborder.joborder_id = ".$jobOrderID : "")
        );
        $rs = $this->_db->getAssoc($sql);

        if (empty($rs))
        {
            return array(
                'totalPipeline' => 0,
                'noStatus' => 0,
                'noContact' => 0,
                'contacted' => 0,
                'qualifying' => 0,
                'submitted' => 0,
                'interviewing' => 0,
                'offered' => 0,
                'candidateDeclined' => 0,
                'passedOn' => 0,
                'placed' => 0
            );
        }

        return $rs;
    }


    // FIXME: Document me.
    private function makePeriodCriterion($dateField, $period)
    {
        /* Note: we add a bogus "AND date > '1900-01-01'" condition to the
         * WHERE clause to force MySQL to use an index containing the date
         * column. MySQL can then build the entire result set without scanning
         * any rows.
         */
        $criteria = '';
        switch ($period)
        {
            case TIME_PERIOD_TODAY:
                $criteria = sprintf(
                    'AND %s > \'1900-01-01\' AND DATE(%s) = CURDATE()',
                    $dateField,
                    $dateField
                );
                break;

            case TIME_PERIOD_YESTERDAY:
                $criteria = sprintf(
                    'AND %s > \'1900-01-01\' AND DATE(%s) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)',
                    $dateField,
                    $dateField
                );
                break;

            case TIME_PERIOD_THISWEEK:
                $criteria = sprintf(
                    'AND %s > \'1900-01-01\' AND YEARWEEK(%s) = YEARWEEK(NOW())',
                    $dateField,
                    $dateField
                );
                break;

            case TIME_PERIOD_LASTWEEK:
                $criteria = sprintf(
                    'AND %s > \'1900-01-01\' AND YEARWEEK(%s) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 7 DAY))',
                    $dateField,
                    $dateField
                );
                break;

            case TIME_PERIOD_LASTTWOWEEKS:
                $criteria =sprintf(
                    'AND %s > \'1900-01-01\' AND (YEARWEEK(%s) = YEARWEEK(NOW()) OR YEARWEEK(%s) = YEARWEEK(NOW() - INTERVAL 7 DAY))',
                    $dateField,
                    $dateField,
                    $dateField
                );
                break;

            case TIME_PERIOD_THISMONTH:
                $criteria = sprintf(
                    'AND %s > \'1900-01-01\' AND EXTRACT(YEAR_MONTH FROM %s) = EXTRACT(YEAR_MONTH FROM CURDATE())',
                    $dateField,
                    $dateField
                );
                break;

            case TIME_PERIOD_LASTMONTH:
                $criteria = sprintf(
                    'AND %s > \'1900-01-01\' AND EXTRACT(YEAR_MONTH FROM %s) = EXTRACT(YEAR_MONTH FROM DATE_SUB(CURDATE(), INTERVAL 1 MONTH))',
                    $dateField,
                    $dateField
                );
                break;

            case TIME_PERIOD_THISYEAR:
                $criteria = sprintf(
                    'AND %s > \'1900-01-01\' AND YEAR(%s) = YEAR(NOW())',
                    $dateField,
                    $dateField
                );
                break;

            case TIME_PERIOD_LASTYEAR:
                $criteria = sprintf(
                    'AND %s > \'1900-01-01\' AND YEAR(%s) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR))',
                    $dateField,
                    $dateField
                );
                break;

            case TIME_PERIOD_TODATE:
            default:
                return sprintf('AND %s > \'1900-01-01\'', $dateField);
                break;
        }

        if ($this->_timeZoneOffset != 0)
        {
            $criteria = str_replace('CURDATE()', 'DATE_ADD(CURDATE(), INTERVAL ' . $this->_timeZoneOffset . ' HOUR)', $criteria);
            $criteria = str_replace('NOW()', 'DATE_ADD(NOW(), INTERVAL ' . $this->_timeZoneOffset . ' HOUR)', $criteria);
            $criteria = str_replace($dateField, 'DATE_ADD(' . $dateField . ', INTERVAL ' . $this->_timeZoneOffset . ' HOUR)', $criteria);
        }

        return $criteria;
    }
}

?>
