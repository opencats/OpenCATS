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

include_once(LEGACY_ROOT . '/lib/NewVersionCheck.php');
include_once(LEGACY_ROOT . '/lib/CommonErrors.php');
include_once(LEGACY_ROOT . '/lib/Dashboard.php');
include_once(LEGACY_ROOT . '/lib/Charts.php');

class HomeUI extends UserInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = true;
        $this->_moduleDirectory = 'home';
        $this->_moduleName = 'home';
        $this->_moduleTabText = 'Dashboard';
        $this->_subTabs = array();
    }


    public function handleRequest()
    {
        $action = $this->getAction();

        if (!eval(Hooks::get('HOME_HANDLE_REQUEST'))) return;

        switch ($action)
        {
            case 'reviewWarning':
                $this->reviewWarning();
                break;

            case 'hiringOverview':
                $dashboard = new Dashboard();
                $view = $this->getHiringOverviewView();
                header('Content-Type: text/html; charset=UTF-8');
                header('Cache-Control: no-store');
                echo(Charts::render('hiring-' . $view, Charts::hiring($dashboard->getPipelineData($view))));
                return;

            case 'quickSearch':
                include_once(LEGACY_ROOT . '/lib/Search.php');
                include_once(LEGACY_ROOT . '/lib/StringUtility.php');

                $this->quickSearch();
                break;

            case 'deleteSavedSearch':
                include_once(LEGACY_ROOT . '/lib/Search.php');

                if ($this->isPostBack())
                {
                    $this->deleteSavedSearch();
                }
                else
                {
                    CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid request.');
                }
                break;

            case 'addSavedSearch':
                include_once(LEGACY_ROOT . '/lib/Search.php');

                if ($this->isPostBack())
                {
                    $this->addSavedSearch();
                }
                else
                {
                    CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid request.');
                }
                break;

            /* FIXME: undefined function getAttachment()
            case 'getAttachment':
                include_once(LEGACY_ROOT . '/lib/Attachments.php');

                $this->getAttachment();
                break;
            */     

            case 'contextActivity':
                $this->home(true);
                break;

            case 'priorities':
            case 'home':
            default:
                $this->home();
                break;
        }
    }


    /** Shared personal-review endpoint for Home and both source pages. */
    private function reviewWarning()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' ||
            !isset($_SESSION['CATS']) || !$_SESSION['CATS']->isLoggedIn())
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Warning reviews require an authenticated POST.');
        if (!isset($_POST['csrfToken']) || !is_string($_POST['csrfToken']) ||
            !$_SESSION['CATS']->isCSRFTokenValid($_POST['csrfToken']))
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid request token. Refresh the warning and try again.');
        include_once(LEGACY_ROOT . '/lib/WarningReviews.php');
        try
        {
            $fields = array('warning_rule', 'data_item_type', 'data_item_id', 'reason_key', 'reason_id',
                'reviewAction', 'expectedVersion', 'expectedEvidence', 'requestID', 'returnTo');
            foreach ($fields as $field)
                if (!isset($_POST[$field]) || !is_string($_POST[$field]))
                    throw new InvalidArgumentException('Invalid warning review fields.');
            foreach (array('reviewUntil', 'closeReason', 'reviewing_user', 'homeView', 'homeFilter', 'homePage', 'homeContext') as $field)
                if (isset($_POST[$field]) && !is_string($_POST[$field]))
                    throw new InvalidArgumentException('Invalid warning review fields.');
            if (array_diff_key($_POST, array_flip(array_merge($fields, array('csrfToken', 'reviewUntil', 'closeReason', 'reviewing_user', 'homeView', 'homeFilter', 'homePage', 'homeContext')))) ||
                !preg_match('/^(0|[1-9][0-9]*)$/D', $_POST['expectedVersion']) || $_POST['expectedVersion'] >= 2147483647)
                throw new InvalidArgumentException('Invalid warning review fields.');
            $identity = WarningReviews::identity($_POST['warning_rule'], $_POST['data_item_type'],
                $_POST['data_item_id'], $_POST['reason_key'], $_POST['reason_id']);
            $returnTo = $_POST['returnTo'];
            if (!in_array($returnTo, array('home', 'companies', 'joborders', 'reports'), true) ||
                ($returnTo === 'companies' && $identity['data_item_type'] !== DATA_ITEM_COMPANY) ||
                ($returnTo === 'joborders' && $identity['data_item_type'] !== DATA_ITEM_JOBORDER))
                throw new InvalidArgumentException('Invalid review destination.');
            $homeReturn = $returnTo === 'home' ? TaskPresentation::homeReturnFields($_POST) : null;
            $reviewer = null;
            if (isset($_POST['reviewing_user']))
            {
                if ($returnTo !== 'reports' || $_POST['reviewAction'] !== 'reset' ||
                    !preg_match('/^[1-9][0-9]*$/D', $_POST['reviewing_user']) || $_POST['reviewing_user'] > 2147483647)
                    throw new InvalidArgumentException('Invalid review target.');
                $reviewer = (int) $_POST['reviewing_user'];
            }
            (new WarningReviews())->save($_POST['warning_rule'], $_POST['data_item_type'], $_POST['data_item_id'],
                $_POST['reason_key'], $_POST['reason_id'], $_POST['reviewAction'], $_POST['closeReason'] ?? null,
                $_POST['reviewUntil'] ?? null, (int) $_POST['expectedVersion'], $_POST['expectedEvidence'], $_POST['requestID'], $reviewer);
        }
        catch (InvalidArgumentException $e)
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, $e->getMessage());
        }
        catch (RuntimeException $e)
        {
            $message = $e->getMessage() === 'Warning review is stale. Reload the source.' ?
                'Warning evidence or personal review state changed. Refresh the current warning before trying again.' :
                'Unable to review this warning. Check your permissions and refresh the current warning.';
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, $message);
        }
        catch (Throwable $e)
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Unable to save the warning review. Refresh and try again.');
        }
        $uri = 'm=' . $returnTo;
        if ($returnTo === 'reports')
        {
            $uri .= '&a=warningOversight';
            if ($identity['data_item_type'] === DATA_ITEM_COMPANY) $uri .= '&companyID=' . $identity['data_item_id'];
        }
        elseif ($returnTo !== 'home') $uri .= '&a=show&' . ($returnTo === 'companies' ? 'companyID' : 'jobOrderID') . '=' . $identity['data_item_id'];
        if ($homeReturn !== null)
        {
            $uri = TaskPresentation::homeReturnURI($homeReturn);
            $parts = explode('#', $uri, 2);
            $uri = $parts[0] . '&warningReview=' . ($_POST['reviewAction'] === 'reset' ? 'restored' : 'saved') .
                (isset($parts[1]) ? '#' . $parts[1] : '');
        }
        else $uri .= '&warningReview=' . ($_POST['reviewAction'] === 'reset' ? 'restored' : 'saved');
        $this->redirectWarningReview($uri);
    }

    protected function redirectWarningReview($uri)
    {
        CATSUtility::transferRelativeURI($uri);
    }

    private function home($activityOnly = false)
    {
        if (!eval(Hooks::get('HOME'))) return;
        include_once(LEGACY_ROOT . '/lib/WarningReviews.php');
        include_once(LEGACY_ROOT . '/lib/Pager.php');
        try
        {
            $state = TaskPresentation::homeReturnFields(array('homeView' => $activityOnly ? ($_GET['homeView'] ?? 'compact') : ($this->getAction() === 'priorities' ? 'all' : 'compact'),
                'homeFilter' => $_GET['filter'] ?? 'all', 'homePage' => $_GET['page'] ?? '1',
                'homeContext' => $activityOnly ? ($_GET['homeContext'] ?? '') : ''));
        }
        catch (InvalidArgumentException $e)
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, $e->getMessage());
        }
        $tasks = new Tasks();
        $warnings = (new WarningReviews())->getRecruitmentWarnings();
        $myTasks = $tasks->getAll(array('assignedTo' => $_SESSION['CATS']->getUserID(), 'openOnly' => true));
        $today = DateUtility::getAdjustedDate('Y-m-d');
        $rows = self::priorityRows($myTasks, $warnings['visibleWarnings'], $today, $state['homeFilter']);
        $pager = new Pager(count($rows), $state['homeView'] === 'all' ? 25 : 8, (int) $state['homePage']);
        $state['homePage'] = (string) $pager->getCurrentPage();
        $pager->setSortByParameters('m=home&amp;a=' . ($state['homeView'] === 'all' ? 'priorities' : 'home') .
            '&amp;filter=' . $state['homeFilter'], '', '');
        $pageRows = array_slice($rows, $pager->getThisPageStartRow(), $state['homeView'] === 'all' ? 25 : 8);
        if ($activityOnly)
        {
            if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET')
                CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Activity preview requires GET.');
            $selected = null;
            foreach ($pageRows as $row) if ($row['id'] === $state['homeContext']) $selected = $row;
            if ($selected === null) CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Priority unavailable.');
            include_once(LEGACY_ROOT . '/lib/ActivityEntries.php');
            $task = $selected['task'];
            $warning = $selected['warnings'][0] ?? null;
            if ($task === null && ($warning['entityType'] ?? null) == DATA_ITEM_TASK)
                $task = $tasks->get($warning['entityID']) ?: null;
            $type = $task !== null ? $task['dataItemType'] : ($warning['entityType'] ?? null);
            $id = $task !== null ? $task['dataItemID'] : ($warning['entityID'] ?? null);
            $entries = $type === null || $id === null ? null : (new ActivityEntries())->getRecentForSource($type, $id);
            $this->_template->assign('homeActivity', $entries);
            $this->_template->assign('homeActivityURL', $entries === null ? null : TaskPresentation::parentURL($type, $id));
            header('Content-Type: text/html; charset=UTF-8');
            header('Cache-Control: no-store');
            header('X-Home-Activity: 1');
            $this->_template->display('./modules/home/RecentActivity.tpl');
            return;
        }
        $contexts = array();
        foreach ($pageRows as &$row)
        {
            if ($row['task'] === null) continue;
            $task = $row['task'];
            $row['permissions'] = $tasks->getActionPermissions($task['taskID']);
            $row['parentContext'] = null;
            if ($task['dataItemType'] !== null)
            {
                $key = $task['dataItemType'] . ':' . $task['dataItemID'];
                if (!array_key_exists($key, $contexts))
                {
                    try { $contexts[$key] = $tasks->getParentContext($task['dataItemType'], $task['dataItemID']); }
                    catch (RuntimeException $e) { $contexts[$key] = null; }
                }
                $row['parentContext'] = $contexts[$key];
            }
        }
        unset($row);
        // A matching readable Task parent may already supply this warning's association.
        // Reuse only that page-local result; do not read parents merely to decorate warnings.
        foreach ($pageRows as &$row)
            if ($row['task'] === null)
            {
                $warning = $row['warnings'][0];
                $row['parentContext'] = $contexts[$warning['entityType'] . ':' . $warning['entityID']] ?? null;
            }
        unset($row);
        include_once(LEGACY_ROOT . '/lib/Calendar.php');
        $this->_template->assign('homeAccounts', (new Companies())->getHomeAccounts());
        $this->_template->assign('homeJobs', (new JobOrders())->getHomeCurrent());
        $this->_template->assign('homeCalendar', (new Calendar())->getHomeUpcoming());
        $this->_template->assign('priorityRows', $pageRows);
        $this->_template->assign('priorityPager', $pager);
        $this->_template->assign('homeState', $state);
        $this->_template->assign('myTaskCount', count($myTasks));
        $this->_template->assign('homeToday', $today);
        $this->_template->assign('priorityOverview', self::priorityOverview($myTasks, $warnings['visibleWarnings'], $today));
        $this->_template->assign('recruitmentWarnings', $warnings);
        $this->_template->assign('active', $this);
        $this->_template->assign('wildCardQuickSearch', '');
        $this->_template->display('./modules/home/Home.tpl');
    }

    /** Presentation only: group existing evidence, without changing warning eligibility/counts. */
    public static function priorityRows($tasks, $warnings, $today, $filter = 'all')
    {
        $rows = array();
        $taskPositions = array();
        if ($filter !== 'warnings')
            foreach ($tasks as $task)
            {
                $taskPositions[$task['taskID']] = count($rows);
                $rows[] = array('id' => 'priority-task-' . $task['taskID'], 'task' => $task, 'warnings' => array(),
                    'sort' => array($task['dueDate'] !== null && $task['dueDate'] <= $today ? 0 : 2,
                        $task['dueDate'] ?? '9999-12-31', (int) $task['taskID']));
            }
        foreach ($warnings as $index => $warning)
        {
            $linked = $warning['taskID'] ?? null;
            if ($warning['entityType'] == DATA_ITEM_TASK) $linked = $warning['entityID'];
            // A covering Task is still the work item. Keep its risk as context.
            if ($linked !== null && isset($taskPositions[$linked]))
            {
                $rows[$taskPositions[$linked]]['warnings'][] = $warning;
                continue;
            }
            if ($filter === 'tasks') continue;
            // Group Task-linked relationship reasons while retaining independent ordinary risk.
            if ($filter === 'all' && $warning['code'] === 'W07')
            {
                $remaining = $warning['relationshipAttention']['reasons'];
                foreach (array_intersect($remaining, array('overdue_task', 'due_today', 'upcoming')) as $reason)
                {
                    $linkedTasks = array_filter($warning['relationshipAttention']['tasks'], function ($task) use ($reason, $today) {
                        if (empty($task['dueDate'])) return false;
                        return ($task['dueDate'] < $today ? 'overdue_task' : ($task['dueDate'] === $today ? 'due_today' : 'upcoming')) === $reason;
                    });
                    if (!$linkedTasks || array_diff(array_column($linkedTasks, 'taskID'), array_keys($taskPositions))) continue;
                    $contextWarning = $warning;
                    $contextWarning['reason'] = $reason;
                    $contextWarning['relationshipAttention']['reasons'] = array($reason);
                    foreach ($linkedTasks as $linkedTask)
                        $rows[$taskPositions[$linkedTask['taskID']]]['warnings'][] = $contextWarning;
                    $remaining = array_values(array_diff($remaining, array($reason)));
                }
                if (!$remaining) continue;
                $warning['relationshipAttention']['reasons'] = $remaining;
                $warning['reason'] = $remaining[0];
            }
            $rows[] = array('id' => 'priority-warning-' . $warning['code'] . '-' . $warning['entityID'] . ($warning['code'] === 'W08' ? '-' . $warning['reason'] : ''),
                'task' => null, 'warnings' => array($warning), 'sort' => array(1, $index, 0));
        }
        usort($rows, fn($a, $b) => $a['sort'] <=> $b['sort']);
        return $rows;
    }

    /** Small display excerpts/counts from the already authorised collections; no additional reads. */
    public static function priorityOverview($tasks, $warnings, $today)
    {
        $overview = array('overdueTaskCount' => 0, 'dueTodayTaskCount' => 0, 'companies' => array(), 'jobs' => array());
        foreach ($tasks as $task)
        {
            if ($task['dueDate'] === null) continue;
            if ($task['dueDate'] < $today) ++$overview['overdueTaskCount'];
            elseif ($task['dueDate'] === $today) ++$overview['dueTodayTaskCount'];
        }
        foreach ($warnings as $warning)
        {
            $group = $warning['entityType'] == DATA_ITEM_COMPANY ? 'companies' :
                ($warning['entityType'] == DATA_ITEM_JOBORDER ? 'jobs' : null);
            if ($group === null || isset($overview[$group][$warning['entityID']]) || count($overview[$group]) >= 3) continue;
            $overview[$group][$warning['entityID']] = $warning;
        }
        return $overview;
    }

    /** Describe supplied evidence, never infer an event or age from missing data. */
    public static function warningEvidence($warning)
    {
        $code = $warning['code'];
        if ($code === 'W08') return array('label' => ucfirst(str_replace('invalid_', '', $warning['reason'])) . ' inactive or unassigned', 'date' => '', 'age' => '');
        $label = array('W01' => 'Due', 'W02' => 'Actionable since', 'W03' => 'Progress check since',
            'W07' => 'Last qualifying contact')[$code] ?? 'Source date';
        $source = $warning['sourceDate'] ?? null;
        $date = $source ? TaskPresentation::date($code === 'W07' ?
            DateUtility::getAdjustedDate('Y-m-d', strtotime($source)) : substr($source, 0, 10)) : '';
        $age = ($warning['ageDays'] ?? null) === null ? '' : (int) $warning['ageDays'] .
            (in_array($code, array('W02', 'W03'), true) ? ' working day' : ' calendar day') . ((int) ($warning['ageDays'] ?? 0) === 1 ? '' : 's');
        if ($date === '') $label = $code === 'W07' ? 'No qualifying contact recorded' : 'Source date not supplied';
        return array('label' => $label, 'date' => $date, 'age' => $age);
    }

    public static function warningReason($reason)
    {
        return array('overdue_commitment' => 'Follow up on an overdue Task',
            'no_recorded_submission' => 'Review this job: no recorded submission',
            'no_recorded_candidate_progress' => 'Review this job: no recent candidate progress',
            'stale_relationship' => 'Reconnect with this Company', 'no_next_action' => 'Plan the next follow-up',
            'overdue_task' => 'Follow up on an overdue Task', 'due_today' => 'Follow-up due today',
            'upcoming' => 'Upcoming follow-up', 'invalid_assignee' => 'Assign this Task to an active recruiter',
            'invalid_owner' => 'Assign an active Job Order owner',
            'invalid_recruiter' => 'Assign an active Job Order recruiter')[$reason] ?? ucwords(str_replace('_', ' ', $reason));
    }

    private function getHiringOverviewView()
    {
        $view = isset($_GET['view']) ? (int) $_GET['view'] : DASHBOARD_GRAPH_WEEKLY;
        return in_array($view, array(DASHBOARD_GRAPH_WEEKLY, DASHBOARD_GRAPH_MONTHLY, DASHBOARD_GRAPH_YEARLY), true)
            ? $view : DASHBOARD_GRAPH_WEEKLY;
    }

    private function deleteSavedSearch()
    {
        if (!isset($_POST['searchID']))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'No search ID specified.');
        }

        if (!isset($_POST['currentURL']))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'No current URL specified.');
        }

        $searchID   = $_POST['searchID'];
        $currentURL = $_POST['currentURL'];

        if (!eval(Hooks::get('HOME_DELETE_SAVED_SEARCH_PRE'))) return;

        $savedSearches = new SavedSearches();
        $savedSearches->remove($searchID);

        if (!eval(Hooks::get('HOME_DELETE_SAVED_SEARCH_POST'))) return;

        CATSUtility::transferRelativeURI($currentURL);
    }

    private function addSavedSearch()
    {
        if (!isset($_POST['searchID']))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'No search ID specified.');
        }

        if (!isset($_POST['currentURL']))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'No current URL specified.');
        }

        $searchID   = $_POST['searchID'];
        $currentURL = $_POST['currentURL'];

        if (!eval(Hooks::get('HOME_ADD_SAVED_SEARCH_PRE'))) return;

        $savedSearches = new SavedSearches();
        $savedSearches->save($searchID);

        if (!eval(Hooks::get('HOME_ADD_SAVED_SEARCH_POST'))) return;

        CATSUtility::transferRelativeURI($currentURL);
    }

    private function quickSearch()
    {
        /* Bail out to prevent an error if the GET string doesn't even contain
         * a field named 'quickSearchFor' at all.
         */
        if (!isset($_GET['quickSearchFor']))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'No query string specified.');
        }

        $query = trim($_GET['quickSearchFor']);
        $wildCardQuickSearch = $query;

        $search = new QuickSearch();
        $candidatesRS = $search->candidates($query);
        $companiesRS  = $search->companies($query);
        $contactsRS   = $search->contacts($query);
        $jobOrdersRS  = $search->jobOrders($query);
        //$listsRS      = $search->lists($query);

        if (!empty($candidatesRS))
        {
            foreach ($candidatesRS as $rowIndex => $row)
            {
                if (!empty($candidatesRS[$rowIndex]['ownerFirstName']))
                {
                    $candidatesRS[$rowIndex]['ownerAbbrName'] = StringUtility::makeInitialName(
                        $candidatesRS[$rowIndex]['ownerFirstName'],
                        $candidatesRS[$rowIndex]['ownerLastName'],
                        false,
                        LAST_NAME_MAXLEN
                    );
                }
                else
                {
                    $candidatesRS[$rowIndex]['ownerAbbrName'] = 'None';
                }

                if (empty($candidatesRS[$rowIndex]['phoneHome']))
                {
                    $candidatesRS[$rowIndex]['phoneHome'] = 'None';
                }

                if (empty($candidatesRS[$rowIndex]['phoneCell']))
                {
                    $candidatesRS[$rowIndex]['phoneCell'] = 'None';
                }
            }
        }

        if (!empty($companiesRS))
        {
            foreach ($companiesRS as $rowIndex => $row)
            {
                if (!empty($companiesRS[$rowIndex]['ownerFirstName']))
                {
                    $companiesRS[$rowIndex]['ownerAbbrName'] = StringUtility::makeInitialName(
                        $companiesRS[$rowIndex]['ownerFirstName'],
                        $companiesRS[$rowIndex]['ownerLastName'],
                        false,
                        LAST_NAME_MAXLEN
                    );
                }
                else
                {
                    $companiesRS[$rowIndex]['ownerAbbrName'] = 'None';
                }

                if (empty($companiesRS[$rowIndex]['phone1']))
                {
                    $companiesRS[$rowIndex]['phone1'] = 'None';
                }
            }
        }

        if (!empty($contactsRS))
        {
            foreach ($contactsRS as $rowIndex => $row)
            {

                if ($contactsRS[$rowIndex]['isHotContact'] == 1)
                {
                    $contactsRS[$rowIndex]['linkClassContact'] = 'jobLinkHot';
                }
                else
                {
                    $contactsRS[$rowIndex]['linkClassContact'] = 'jobLinkCold';
                }

                if ($contactsRS[$rowIndex]['leftCompany'] == 1)
                {
                    $contactsRS[$rowIndex]['linkClassCompany'] = 'jobLinkDead';
                }
                else if ($contactsRS[$rowIndex]['isHotCompany'] == 1)
                {
                    $contactsRS[$rowIndex]['linkClassCompany'] = 'jobLinkHot';
                }
                else
                {
                    $contactsRS[$rowIndex]['linkClassCompany'] = 'jobLinkCold';
                }

                if (!empty($contactsRS[$rowIndex]['ownerFirstName']))
                {
                    $contactsRS[$rowIndex]['ownerAbbrName'] = StringUtility::makeInitialName(
                        $contactsRS[$rowIndex]['ownerFirstName'],
                        $contactsRS[$rowIndex]['ownerLastName'],
                        false,
                        LAST_NAME_MAXLEN
                    );
                }
                else
                {
                    $contactsRS[$rowIndex]['ownerAbbrName'] = 'None';
                }

                if (empty($contactsRS[$rowIndex]['phoneWork']))
                {
                    $contactsRS[$rowIndex]['phoneWork'] = 'None';
                }

                if (empty($contactsRS[$rowIndex]['phoneCell']))
                {
                    $contactsRS[$rowIndex]['phoneCell'] = 'None';
                }
            }
        }

        if (!empty($jobOrdersRS))
        {
            foreach ($jobOrdersRS as $rowIndex => $row)
            {
                if ($jobOrdersRS[$rowIndex]['startDate'] == '00-00-00')
                {
                    $jobOrdersRS[$rowIndex]['startDate'] = '';
                }

                if ($jobOrdersRS[$rowIndex]['isHot'] == 1)
                {
                    $jobOrdersRS[$rowIndex]['linkClass'] = 'jobLinkHot';
                }
                else
                {
                    $jobOrdersRS[$rowIndex]['linkClass'] = 'jobLinkCold';
                }

                if (!empty($jobOrdersRS[$rowIndex]['recruiterAbbrName']))
                {
                    $jobOrdersRS[$rowIndex]['recruiterAbbrName'] = StringUtility::makeInitialName(
                        $jobOrdersRS[$rowIndex]['recruiterFirstName'],
                        $jobOrdersRS[$rowIndex]['recruiterLastName'],
                        false,
                        LAST_NAME_MAXLEN
                    );
                }
                else
                {
                    $jobOrdersRS[$rowIndex]['recruiterAbbrName'] = 'None';
                }

                if (!empty($jobOrdersRS[$rowIndex]['ownerFirstName']))
                {
                    $jobOrdersRS[$rowIndex]['ownerAbbrName'] = StringUtility::makeInitialName(
                        $jobOrdersRS[$rowIndex]['ownerFirstName'],
                        $jobOrdersRS[$rowIndex]['ownerLastName'],
                        false,
                        LAST_NAME_MAXLEN
                    );
                }
                else
                {
                    $jobOrdersRS[$rowIndex]['ownerAbbrName'] = 'None';
                }
            }
        }

        $this->_template->assign('active', $this);
        $this->_template->assign('jobOrdersRS', $jobOrdersRS);
        $this->_template->assign('candidatesRS', $candidatesRS);
        $this->_template->assign('companiesRS', $companiesRS);
        $this->_template->assign('contactsRS', $contactsRS);
        //$this->_template->assign('listsRS', $listsRS);
        $this->_template->assign('wildCardQuickSearch', $wildCardQuickSearch);

        if (!eval(Hooks::get('HOME_QUICK_SEARCH'))) return;

        $this->_template->display('./modules/home/SearchEverything.tpl');
    }
}

?>
