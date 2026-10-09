<?php
/* Native Task screens; all record and action authority remains in Tasks. */
include_once(LEGACY_ROOT . '/lib/Tasks.php');
include_once(LEGACY_ROOT . '/lib/DataGrid.php');
include_once(LEGACY_ROOT . '/modules/tasks/dataGrids.php');

class TasksUI extends UserInterface
{
    public function __construct()
    {
        parent::__construct();
        $this->_moduleDirectory = 'tasks';
        $this->_moduleName = 'tasks';
        $this->_moduleTabText = 'Tasks*al=' . ACCESS_LEVEL_READ . '@tasks.list';
        $this->_subTabs = array(
            'My Tasks' => CATSUtility::getIndexName() . '?m=tasks',
            'All Accessible Tasks' => CATSUtility::getIndexName() . '?m=tasks&assignedTo=all',
            'Add Task' => CATSUtility::getIndexName() . '?m=tasks&a=add*al=' . ACCESS_LEVEL_EDIT . '@tasks.add'
        );
    }

    public function handleRequest()
    {
        if ($this->getAction() === 'quickAdd')
        {
            $this->quickAdd();
            return;
        }
        $tasks = new Tasks();
        $action = $this->getAction();
        $record = array();
        $error = '';
        $view = 'List';
        $permissions = array();
        try
        {
            if (!isset($_SESSION['CATS']) || !$_SESSION['CATS']->isLoggedIn() ||
                $this->getUserAccessLevel('tasks.show') < ACCESS_LEVEL_READ)
                throw new RuntimeException('Task access denied.');
            if (!is_string($action) || !in_array($action, array('', 'list', 'show', 'add', 'edit', 'complete', 'cancel', 'reopen', 'completeLog'), true))
                throw new InvalidArgumentException('Invalid Task action.');
            $post = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
            $writeAction = in_array($action, array('complete', 'cancel', 'reopen', 'completeLog'), true);
            if ($writeAction && !$post) throw new InvalidArgumentException('Task changes require POST.');
            if ($post && !in_array($action, array('add', 'edit', 'complete', 'cancel', 'reopen', 'completeLog'), true))
                throw new InvalidArgumentException('Invalid Task request.');
            if ($post && !$_SESSION['CATS']->isCSRFTokenValid($_POST['csrfToken'] ?? null))
                throw new RuntimeException('Invalid request token. Please reload the form.');

            if (in_array($action, array('show', 'edit', 'complete', 'cancel', 'reopen', 'completeLog'), true))
            {
                $input = $post ? $_POST : $_GET;
                $id = $input['taskID'] ?? null;
                // Guard scalar shape before the legacy request helper casts to string.
                if ((!is_string($id) && !is_int($id)) || !$this->isRequiredIDValid('taskID', $input))
                    throw new InvalidArgumentException('Invalid Task ID.');
                $record = $tasks->get($id);
                if (empty($record)) throw new RuntimeException('Task unavailable.');
                $permissions = $tasks->getActionPermissions($id);
                $view = 'Show';
            }
            if ($action === 'add' || $action === 'edit')
            {
                if ($action === 'add' && $this->getUserAccessLevel('tasks.add') < ACCESS_LEVEL_EDIT)
                    throw new RuntimeException('Task creation denied.');
                if ($action === 'edit' && !$permissions['edit']) throw new RuntimeException('Task update denied.');
                $view = 'Form';
                if ($action === 'edit') $record['dueDate'] = TaskPresentation::date($record['dueDate']);
                if ($action === 'add')
                {
                    $record = array('title' => '', 'description' => '', 'dueDate' => '', 'status' => 'open',
                        'priority' => 'normal', 'purpose' => 'general', 'assignedTo' => $this->_userID,
                        'dataItemType' => '', 'dataItemID' => '', 'candidateJobOrderID' => '');
                    foreach (array('dataItemType', 'dataItemID', 'candidateJobOrderID') as $field)
                    {
                        if (isset($_GET[$field]))
                        {
                            if (!is_string($_GET[$field])) throw new InvalidArgumentException('Invalid Task association.');
                            $record[$field] = $_GET[$field];
                        }
                    }
                    $permissions = array('manage' => true);
                }
                if ($post)
                {
                    $values = $this->getFormValues($_POST);
                    // Preserve scalar input for correction. Never trust it for permission decisions.
                    $record = array_replace($record, $values);
                    if (isset($values['dueDate'])) $values['dueDate'] = TaskPresentation::parseDate($values['dueDate']);
                    if ($action === 'add') $id = $tasks->add($values);
                    else $tasks->update($id, $values);
                    $this->redirectToTask($id, 'saved');
                    return;
                }
            }
            elseif ($writeAction)
            {
                if (array_diff_key($_POST, array_flip(array('taskID', 'csrfToken', 'postback'))))
                    throw new InvalidArgumentException('Unexpected Task action fields.');
                $status = array('complete' => 'completed', 'completeLog' => 'completed', 'cancel' => 'cancelled', 'reopen' => 'open')[$action];
                $tasks->update($id, array('status' => $status));
                $this->redirectToTask($id, $action === 'completeLog' ? 'activity' : 'saved');
                return;
            }
            elseif ($action === '' || $action === 'list')
            {
                if ($this->getUserAccessLevel('tasks.list') < ACCESS_LEVEL_READ) throw new RuntimeException('Task listing denied.');
                $this->_template->assign('dataGrid', new TasksDataGrid(array('rangeStart' => 0, 'maxResults' => 15), 0));
            }
            if ($view === 'Show') $this->_template->assign('history', $tasks->getHistory($record['taskID']));
        }
        catch (InvalidArgumentException $e)
        {
            $error = $e->getMessage();
        }
        catch (RuntimeException $e)
        {
            $error = $e->getMessage();
            // Permission failures must not render a partially populated record/form.
            $view = 'Error';
            $record = array();
        }
        catch (Throwable $e)
        {
            $error = 'Unable to process the Task. Please try again.';
            $view = 'Error';
            $record = array();
        }
        if ($error !== '' && $view !== 'Form') $view = 'Error';
        $pipelineChoices = array();
        if ($view === 'Form')
        {
            try { $pipelineChoices = $tasks->getPipelineChoices($record['dataItemType'], $record['dataItemID']); }
            catch (InvalidArgumentException $e) { /* Invalid submitted associations stay editable. */ }
        }
        $this->_template->assign('pipelineChoices', $pipelineChoices);
        $this->_template->assign('activityTarget', $view === 'Show' && $this->getAction() === 'show' ? $tasks->getActivityTarget($record['taskID']) : array());
        $this->_template->assign('active', $this);
        $this->_template->assign('error', $error);
        $this->_template->assign('task', $record);
        $this->_template->assign('permissions', $permissions);
        $this->_template->assign('action', $action);
        $this->_template->assign('notice', is_string($_GET['notice'] ?? null) ? $_GET['notice'] : '');
        $this->_template->assign('users', $view === 'Error' ? array() : self::getUserChoices($record['assignedTo'] ?? null));
        $this->_template->display('./modules/tasks/' . $view . '.tpl');
    }

    private function quickAdd()
    {
        $post = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
        try
        {
            if (!isset($_SESSION['CATS']) || !$_SESSION['CATS']->isLoggedIn())
                throw new RuntimeException('Task access denied.');
            if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', array('GET', 'POST'), true))
                throw new InvalidArgumentException('Invalid Task request.');
            if ($post && !$_SESSION['CATS']->isCSRFTokenValid($_POST['csrfToken'] ?? null))
                throw new RuntimeException('Invalid request token. Please reload the page.');
            $input = $post ? $_POST : $_GET;
            $tasks = new Tasks();
            $context = $tasks->getParentContext($input['dataItemType'] ?? null, $input['dataItemID'] ?? null, true);
            if ($post)
            {
                $values = $this->getFormValues($_POST);
                if (isset($values['dueDate'])) $values['dueDate'] = TaskPresentation::parseDate($values['dueDate']);
                $id = $tasks->add($values);
                // Saving and refreshing are distinct: a refresh failure must never invite a second add.
                try { $html = self::renderParentRows($context['type'], $context['id']); }
                catch (Throwable $e) { $html = null; }
                $this->sendTaskJSON(array('success' => true, 'taskID' => $id, 'html' => $html));
                return;
            }
            $this->_template->assign('context', $context);
            $this->_template->assign('task', array_replace(Tasks::getDefaults(), array('assignedTo' => $this->_userID)));
            $this->_template->assign('users', self::getUserChoices());
            $this->_template->assign('pipelineChoices', $tasks->getPipelineChoices($context['type'], $context['id']));
            $this->_template->display('./modules/tasks/QuickAdd.tpl');
        }
        catch (InvalidArgumentException | RuntimeException $e)
        {
            $this->sendTaskJSON(array('success' => false, 'message' => $e->getMessage()));
        }
        catch (Throwable $e)
        {
            $this->sendTaskJSON(array('success' => false, 'message' => 'Unable to process the Task. Please check your session and try again.'));
        }
    }

    protected function sendTaskJSON($response)
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        echo json_encode($response, JSON_INVALID_UTF8_SUBSTITUTE);
    }

    private static function renderParentRows($type, $id)
    {
        $template = new Template();
        $template->assign('tasks', (new Tasks())->getAll(array('dataItemType' => $type, 'dataItemID' => $id, 'openOnly' => true)));
        $template->assign('users', self::getUserChoices());
        ob_start();
        try
        {
            $template->display('./modules/tasks/ParentRows.tpl');
            return ob_get_contents();
        }
        finally { ob_end_clean(); }
    }

    private function getFormValues($input)
    {
        $fields = array('title', 'description', 'dueDate', 'priority', 'status', 'purpose',
            'assignedTo', 'dataItemType', 'dataItemID', 'candidateJobOrderID');
        $values = array();
        foreach ($fields as $field)
        {
            if (!array_key_exists($field, $input)) continue;
            if (!is_string($input[$field])) throw new InvalidArgumentException('Invalid Task field: ' . $field . '.');
            $values[$field] = $input[$field];
        }
        foreach ($input as $field => $value)
            if (!in_array($field, array_merge($fields, array('taskID', 'csrfToken', 'postback')), true))
                throw new InvalidArgumentException('Unknown Task field.');
        return $values;
    }

    protected function redirectToTask($id, $notice)
    {
        CATSUtility::transferRelativeURI('m=tasks&a=show&taskID=' . (int) $id . '&notice=' . $notice);
    }

    public static function getUserChoices($retained = null)
    {
        $choices = array('' => 'Unassigned');
        foreach ((new Users())->getSelectList() as $user)
            $choices[$user['userID']] = trim($user['firstName'] . ' ' . $user['lastName']);
        if ($retained !== null && $retained !== '' && !isset($choices[$retained]))
        {
            $user = (new Users())->get($retained);
            $choices[$retained] = ($user['fullName'] ?? ('User #' . $retained)) . ' (inactive)';
        }
        return $choices;
    }

    public static function parentURL($type, $id)
    {
        return TaskPresentation::parentURL($type, $id);
    }

    /** Shared parent panel, called only after the parent screen's existing access checks. */
    public static function showForParent($type, $id)
    {
        if ($_SESSION['CATS']->getAccessLevel('tasks.list') < ACCESS_LEVEL_READ ||
            $_SESSION['CATS']->getAccessLevel('tasks.show') < ACCESS_LEVEL_READ) return;
        $template = new Template();
        $template->assign('taskRowsHTML', self::renderParentRows($type, $id));
        $template->assign('parentType', $type);
        $template->assign('parentID', $id);
        $template->display('./modules/tasks/Parent.tpl');
    }
}
