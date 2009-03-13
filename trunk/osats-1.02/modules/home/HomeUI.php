<?php
/*
 * OSATS
 * Home Module
 *
 */
 
include_once('./lib/CommonErrors.php');
include_once('./lib/Dashboard.php');
include_once('./lib/i18n.php');

class HomeUI extends UserInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = true;
        $this->_moduleDirectory = 'home';
        $this->_moduleName = 'home';
        $this->_moduleTabText = 'Home';
        $this->_subTabs = array();
    }


    public function handleRequest()
    {
        $action = $this->getAction();

        if (!eval(Hooks::get('HOME_HANDLE_REQUEST'))) return;

        switch ($action)
        {
            case 'quickSearch':
                include_once('./lib/Search.php');
                include_once('./lib/StringUtility.php');

                $this->quickSearch();
                break;

            case 'deleteSavedSearch':
                include_once('./lib/Search.php');

                $this->deleteSavedSearch();
                break;

            case 'addSavedSearch':
                include_once('./lib/Search.php');

                $this->addSavedSearch();
                break;

            case 'getAttachment':
                include_once('./lib/Attachments.php');

                $this->getAttachment();
                break;

            case 'home':
            default:
                $this->home();
                break;
        }
    }


    private function home()
    {        
         if (!eval(Hooks::get('HOME'))) return;

        
        $dashboard = new Dashboard($this->_siteID);
        $placedRS = $dashboard->getPlacements();
        
        $calendar = new Calendar($this->_siteID);
        $upcomingEventsHTML = $calendar->getUpcomingEventsHTML(7, UPCOMING_FOR_DASHBOARD);
        
        $calendar = new Calendar($this->_siteID);
        $upcomingEventsFupHTML = $calendar->getUpcomingEventsHTML(7, UPCOMING_FOR_DASHBOARD_FUP);        

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

    private function deleteSavedSearch()
    {
        if (!isset($_GET['searchID']))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, __('No search ID specified.'));
        }

        if (!isset($_GET['currentURL']))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, __('No current URL specified.'));
        }

        $searchID   = $_GET['searchID'];
        $currentURL = $_GET['currentURL'];

        if (!eval(Hooks::get('HOME_DELETE_SAVED_SEARCH_PRE'))) return;

        $savedSearches = new SavedSearches($this->_siteID);
        $savedSearches->remove($searchID);

        if (!eval(Hooks::get('HOME_DELETE_SAVED_SEARCH_POST'))) return;

        osatutil::transferRelativeURI($currentURL);
    }

    private function addSavedSearch()
    {
        if (!isset($_GET['searchID']))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, __('No search ID specified.'));
        }

        if (!isset($_GET['currentURL']))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, __('No current URL specified.'));
        }

        $searchID   = $_GET['searchID'];
        $currentURL = $_GET['currentURL'];

        if (!eval(Hooks::get('HOME_ADD_SAVED_SEARCH_PRE'))) return;

        $savedSearches = new SavedSearches($this->_siteID);
        $savedSearches->save($searchID);

        if (!eval(Hooks::get('HOME_ADD_SAVED_SEARCH_POST'))) return;

        osatutil::transferRelativeURI($currentURL);
    }

    private function quickSearch()
    {
        /* Bail out to prevent an error if the GET string doesn't even contain
         * a field named 'quickSearchFor' at all.
         */
        if (!isset($_GET['quickSearchFor']))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, __('No query string specified.'));
        }

        $query = trim($_GET['quickSearchFor']);
        $wildCardQuickSearch = $query;

        $search = new QuickSearch($this->_siteID);
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
                    $candidatesRS[$rowIndex]['ownerAbbrName'] = __('_None');
                }

                if (empty($candidatesRS[$rowIndex]['phoneHome']))
                {
                    $candidatesRS[$rowIndex]['phoneHome'] = __('_None');
                }

                if (empty($candidatesRS[$rowIndex]['phoneCell']))
                {
                    $candidatesRS[$rowIndex]['phoneCell'] = __('_None');
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
                    $companiesRS[$rowIndex]['ownerAbbrName'] = __('_None');
                }

                if (empty($companiesRS[$rowIndex]['phone1']))
                {
                    $companiesRS[$rowIndex]['phone1'] = __('_None');
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
                    $contactsRS[$rowIndex]['ownerAbbrName'] = __('_None');
                }

                if (empty($contactsRS[$rowIndex]['phoneWork']))
                {
                    $contactsRS[$rowIndex]['phoneWork'] = __('_None');
                }

                if (empty($contactsRS[$rowIndex]['phoneCell']))
                {
                    $contactsRS[$rowIndex]['phoneCell'] = __('_None');
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
                    $jobOrdersRS[$rowIndex]['recruiterAbbrName'] = __('_None');
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
                    $jobOrdersRS[$rowIndex]['ownerAbbrName'] = __('_None');
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