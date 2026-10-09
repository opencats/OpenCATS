<?php
include_once(LEGACY_ROOT . '/lib/Tasks.php');
include_once(LEGACY_ROOT . '/lib/DataGrid.php');
include_once(LEGACY_ROOT . '/modules/tasks/TaskPresentation.php');

class TasksDataGrid extends DataGrid
{
    protected $_tableWidth;
    protected $_defaultAlphabeticalSortBy;
    protected $_classColumns;
    protected $_totalEntries;
    protected $_currentPage;
    protected $_totalPages;
    protected $_totalColumnWidths;
    public $defaultSortBy;
    public $defaultSortDirection;
    public $ajaxMode;
    public $showExportCheckboxes;
    public $showExportColumn;
    public $showActionArea;
    public $showChooseColumnsBox;
    public $allowResizing;
    public $ignoreSavedColumnLayouts;
    public $globalStyle = '';
    private $_taskRows = null;
    private $_taskFilters;
    private $_parentContexts = array();

    public function __construct($parameters, $misc)
    {
        $this->_taskFilters = self::getTaskFilters($_GET);
        $requestKey = 'parameters' . 'tasks:TasksDataGrid';
        if (isset($_GET[$requestKey]))
        {
            if (!is_string($_GET[$requestKey])) throw new InvalidArgumentException('Invalid Task paging request.');
            $parameters = json_decode($_GET[$requestKey], true);
        }
        if (!is_array($parameters)) throw new InvalidArgumentException('Invalid Task paging request.');
        foreach ($parameters as $key => $value)
        {
            if (!in_array($key, array('rangeStart', 'maxResults', 'sortBy', 'sortDirection', 'filterVisible', 'noSaveParameters'), true) || !is_scalar($value))
                throw new InvalidArgumentException('Invalid Task paging request.');
        }
        if (isset($_GET['dynamicArgumenttasks:TasksDataGrid']) ||
            (isset($parameters['rangeStart']) && !preg_match('/^[0-9]{1,10}$/D', (string) $parameters['rangeStart'])) ||
            (isset($parameters['sortBy']) && !in_array($parameters['sortBy'], array('title', 'status', 'priority', 'dueDate', 'purpose', 'assignedTo', 'dataItemID'), true)) ||
            (isset($parameters['sortDirection']) && !in_array($parameters['sortDirection'], array('ASC', 'DESC'), true)))
            throw new InvalidArgumentException('Invalid Task paging request.');
        if (isset($parameters['maxResults']) && !in_array((int) $parameters['maxResults'], array(15, 30, 50, 100), true))
            throw new InvalidArgumentException('Choose 15, 30, 50 or 100 Tasks per page.');
        $this->_tableWidth = new Width(100, '%');
        $this->_defaultAlphabeticalSortBy = 'dueDate';
        $this->defaultSortBy = 'dueDate';
        $this->defaultSortDirection = 'ASC';
        $this->ajaxMode = false;
        $this->showExportCheckboxes = false;
        $this->showExportColumn = false;
        $this->showActionArea = false;
        $this->showChooseColumnsBox = false;
        $this->allowResizing = false;
        $this->ignoreSavedColumnLayouts = true;
        $this->_defaultColumns = array();
        $this->_classColumns = array();
        foreach (array('Title' => 'title', 'Status' => 'status', 'Priority' => 'priority',
            'Due date' => 'dueDate', 'Purpose' => 'purpose', 'Assigned recruiter' => 'assignedTo', 'Parent' => 'dataItemID') as $label => $field)
        {
            $this->_defaultColumns[] = array('name' => $label, 'width' => $field === 'title' ? 230 : 110);
            $this->_classColumns[$label] = array('pagerRender' => 'return TasksDataGrid::renderCell($rsData, ' . var_export($field, true) . ');',
                'sortableColumn' => $field, 'pagerWidth' => 110, 'pagerOptional' => false, 'alphaNavigation' => false);
        }
        parent::__construct('tasks:TasksDataGrid', $parameters);
    }

    public static function getTaskFilters($request)
    {
        $filters = array('assignedTo' => (string) $_SESSION['CATS']->getUserID(), 'status' => 'open_work',
            'priority' => '', 'purpose' => '', 'due' => '', 'dueFrom' => '', 'dueTo' => '', 'dataItemType' => '', 'dataItemID' => '');
        foreach ($filters as $key => $value)
        {
            if (!array_key_exists($key, $request)) continue;
            if (!is_string($request[$key])) throw new InvalidArgumentException('Invalid Task filter.');
            $filters[$key] = $request[$key];
        }
        foreach (array('status' => array_merge(array('open_work' => '', '' => ''), Tasks::getStatuses()),
            'priority' => array_merge(array('' => ''), Tasks::getPriorities()),
            'purpose' => array_merge(array('' => ''), Tasks::getPurposes()),
            'due' => array_fill_keys(array('', 'overdue', 'today', 'upcoming', 'undated'), '')) as $key => $choices)
            if (!array_key_exists($filters[$key], $choices)) throw new InvalidArgumentException('Invalid Task filter.');
        foreach (array('assignedTo', 'dataItemID', 'dataItemType') as $key)
        {
            if ($filters[$key] === '' || ($key === 'assignedTo' && $filters[$key] === 'all')) continue;
            if (!preg_match('/^[1-9][0-9]{0,9}$/D', $filters[$key]) || (float) $filters[$key] > 2147483647)
                throw new InvalidArgumentException('Invalid Task filter ID.');
        }
        if ($filters['dataItemType'] !== '' && !isset(Tasks::getParentTypes()[$filters['dataItemType']]))
            throw new InvalidArgumentException('Invalid Task parent type.');
        if ($filters['dataItemID'] !== '' && $filters['dataItemType'] === '')
            throw new InvalidArgumentException('Choose a parent type for the record ID.');
        $from = TaskPresentation::parseDate($filters['dueFrom']);
        $to = TaskPresentation::parseDate($filters['dueTo']);
        if ($from !== '' && $to !== '' && $from > $to)
            throw new InvalidArgumentException('Due date range is reversed.');
        return $filters;
    }

    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        // loadRows uses the existing scoped model, never raw Task SQL.
        return '';
    }

    protected function loadRows($sql)
    {
        if ($this->_taskRows === null)
        {
            $filters = $this->_taskFilters;
            foreach (array('dueFrom', 'dueTo') as $field) $filters[$field] = TaskPresentation::parseDate($filters[$field]);
            $backend = array();
            foreach (array('priority', 'purpose') as $field)
                if ($filters[$field] !== '') $backend[$field] = $filters[$field];
            if ($filters['assignedTo'] !== 'all') $backend['assignedTo'] = $filters['assignedTo'] === '' ? null : $filters['assignedTo'];
            if ($filters['status'] === 'open_work') $backend['openOnly'] = true;
            elseif ($filters['status'] !== '') $backend['status'] = $filters['status'];
            if ($filters['dataItemID'] !== '')
            {
                $backend['dataItemID'] = $filters['dataItemID'];
                $backend['dataItemType'] = $filters['dataItemType'];
            }
            $today = DateUtility::getAdjustedDate('Y-m-d');
            $rows = (new Tasks())->getAll($backend);
            $this->_taskRows = array_values(array_filter($rows, function ($row) use ($filters, $today) {
                if ($filters['dataItemType'] !== '' && $row['dataItemType'] != $filters['dataItemType']) return false;
                $due = $row['dueDate'];
                if ($filters['dueFrom'] !== '' && ($due === null || $due < $filters['dueFrom'])) return false;
                if ($filters['dueTo'] !== '' && ($due === null || $due > $filters['dueTo'])) return false;
                switch ($filters['due'])
                {
                    case 'overdue': return $due !== null && $due < $today;
                    case 'today': return $due === $today;
                    case 'upcoming': return $due !== null && $due > $today;
                    case 'undated': return $due === null;
                }
                return true;
            }));
            $users = array();
            foreach ((new Users())->getSelectList() as $user)
                $users[$user['userID']] = trim($user['firstName'] . ' ' . $user['lastName']);
            foreach ($this->_taskRows as &$row)
                $row['assigneeLabel'] = $row['assignedTo'] === null ? 'Unassigned' : ($users[$row['assignedTo']] ?? ('User #' . $row['assignedTo'] . ' (inactive)'));
            unset($row);
            $field = $this->_parameters['sortBy'];
            $direction = $this->_parameters['sortDirection'] === 'DESC' ? -1 : 1;
            usort($this->_taskRows, function ($a, $b) use ($field, $direction) {
                // Undated work has its own filter and follows dated work in either direction.
                if ($field === 'dueDate' && ($a[$field] === null) !== ($b[$field] === null)) return $a[$field] === null ? 1 : -1;
                $left = $field === 'assignedTo' ? $a['assigneeLabel'] : $a[$field];
                $right = $field === 'assignedTo' ? $b['assigneeLabel'] : $b[$field];
                if ($field === 'priority')
                {
                    $rank = array('low' => 1, 'normal' => 2, 'high' => 3);
                    $left = $rank[$left];
                    $right = $rank[$right];
                }
                return ($left <=> $right) * $direction ?: (int) $a['taskID'] <=> (int) $b['taskID'];
            });
        }
        $page = array_slice($this->_taskRows, max(0, $this->_parameters['rangeStart']), $this->_parameters['maxResults']);
        $tasks = new Tasks();
        foreach ($page as &$row)
        {
            if ($row['dataItemID'] === null) continue;
            $key = $row['dataItemType'] . ':' . $row['dataItemID'];
            if (!array_key_exists($key, $this->_parentContexts))
            {
                try { $this->_parentContexts[$key] = $tasks->getParentContext($row['dataItemType'], $row['dataItemID']); }
                catch (RuntimeException $e) { $this->_parentContexts[$key] = null; }
            }
            $row['parentContext'] = $this->_parentContexts[$key];
        }
        unset($row);
        return array($page, count($this->_taskRows));
    }

    public static function renderCell($row, $field)
    {
        $value = $row[$field];
        if ($field === 'title') return '<a href="' . Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&a=show&taskID=' . (int) $row['taskID']) . '">' . Template::escapeHtml($value) . '</a>';
        if ($field === 'assignedTo') $value = $row['assigneeLabel'];
        elseif ($field === 'dataItemID')
        {
            if ($value === null) return 'Standalone';
            $context = $row['parentContext'] ?? null;
            if ($context === null) return 'Unavailable';
            return '<a href="' . Template::escapeAttr(TaskPresentation::parentURL($context['type'], $context['id'])) . '">' .
                Template::escapeHtml($context['name']) . '</a>' .
                ($context['companyName'] === '' ? '' : '<div>' . Template::escapeHtml($context['companyName']) . '</div>') .
                '<div class="small text-body-secondary">' . Template::escapeHtml(Tasks::getParentTypes()[$context['type']] . ' #' . $context['id']) . '</div>';
        }
        elseif ($field === 'dueDate') $value = $value === null ? 'Undated' : TaskPresentation::date($value);
        elseif ($field === 'status') $value = Tasks::getStatuses()[$value];
        elseif ($field === 'priority') $value = Tasks::getPriorities()[$value];
        elseif ($field === 'purpose') $value = Tasks::getPurposes()[$value];
        return Template::escapeHtml($value);
    }
}
