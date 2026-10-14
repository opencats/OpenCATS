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
            foreach (array('reviewUntil', 'closeReason', 'reviewing_user') as $field)
                if (isset($_POST[$field]) && !is_string($_POST[$field]))
                    throw new InvalidArgumentException('Invalid warning review fields.');
            if (array_diff_key($_POST, array_flip(array_merge($fields, array('csrfToken', 'reviewUntil', 'closeReason', 'reviewing_user')))) ||
                !preg_match('/^(0|[1-9][0-9]*)$/D', $_POST['expectedVersion']) || $_POST['expectedVersion'] >= 2147483647)
                throw new InvalidArgumentException('Invalid warning review fields.');
            $identity = WarningReviews::identity($_POST['warning_rule'], $_POST['data_item_type'],
                $_POST['data_item_id'], $_POST['reason_key'], $_POST['reason_id']);
            $returnTo = $_POST['returnTo'];
            if (!in_array($returnTo, array('home', 'companies', 'joborders', 'reports'), true) ||
                ($returnTo === 'companies' && $identity['data_item_type'] !== DATA_ITEM_COMPANY) ||
                ($returnTo === 'joborders' && $identity['data_item_type'] !== DATA_ITEM_JOBORDER))
                throw new InvalidArgumentException('Invalid review destination.');
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
        $this->redirectWarningReview($uri . '&warningReview=' . ($_POST['reviewAction'] === 'reset' ? 'restored' : 'saved'));
    }

    protected function redirectWarningReview($uri)
    {
        CATSUtility::transferRelativeURI($uri);
    }

    private function home()
    {        
         if (!eval(Hooks::get('HOME'))) return;
        
        NewVersionCheck::getNews();
        
        include_once(LEGACY_ROOT . '/lib/WarningReviews.php');
        $this->_template->assign('recruitmentWarnings', (new WarningReviews())->getRecruitmentWarnings());

        $dashboard = new Dashboard();
        $placedRS = $dashboard->getPlacements();
        $chartView = $this->getHiringOverviewView();
        $hiringCharts = array($chartView => Charts::hiring($dashboard->getPipelineData($chartView)));
        $this->_template->assign('chartView', $chartView);
        $this->_template->assign('hiringCharts', $hiringCharts);
        
        $calendar = new Calendar();
        $upcomingEventsHTML = $calendar->getUpcomingEventsHTML(7, UPCOMING_FOR_DASHBOARD, false);
        
        $calendar = new Calendar();
        $upcomingEventsFupHTML = $calendar->getUpcomingEventsHTML(7, UPCOMING_FOR_DASHBOARD_FUP, false);

        /* Important cand datagrid */

        $dataGridProperties = array(
            'rangeStart'    => 0,
            'maxResults'    => 15,
            'filterVisible' => false
        );

        $dataGrid = DataGrid::get("home:ImportantPipelineDashboard", $dataGridProperties);

        $this->_template->assign('dataGrid', $dataGrid);

        $dataGridProperties = array(
            'rangeStart'    => 0,
            'maxResults'    => 15,
            'filterVisible' => false
        );

        /* Only show a month of activities. */
        $dataGridProperties['startDate'] = '';
        $dataGridProperties['endDate'] = '';
        $dataGridProperties['period'] = 'DATE_SUB(CURDATE(), INTERVAL 1 MONTH)';

        $dataGrid2 = DataGrid::get("home:CallsDataGrid", $dataGridProperties);

        $this->_template->assign('dataGrid2', $dataGrid2);
        
        $this->_template->assign('active', $this);
        $this->_template->assign('placedRS', $placedRS);
        $this->_template->assign('upcomingEventsHTML', $upcomingEventsHTML);
        $this->_template->assign('upcomingEventsFupHTML', $upcomingEventsFupHTML);
        $this->_template->assign('wildCardQuickSearch', '');
        $this->_template->display('./modules/home/Home.tpl');
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
