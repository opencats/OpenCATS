<?php
/**
 * OSATS
 */

include_once('./lib/ActivityEntries.php');
include_once('./lib/StringUtility.php');
include_once('./lib/Contacts.php');
include_once('./lib/Candidates.php');
include_once('./lib/DateUtility.php');
include_once('./lib/InfoString.php');
include_once('./lib/i18n.php');

class ActivityUI extends UserInterface
{
    /* Maximum number of characters of a line in the regarding field to show
     * on the main listing.
     */
    const TRUNCATE_REGARDING = 24;

    /* Maximum number of characters to display of an activity note. */
    const ACTIVITY_NOTE_MAXLEN = 140;


    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = true;
        $this->_moduleDirectory = 'activity';
        $this->_moduleName = 'activity';
        $this->_moduleTabText = __('Activities');
    }

    public function handleRequest()
    {
        $action = $this->getAction();

        if (!eval(Hooks::get('ACTIVITY_HANDLE_REQUEST'))) return;

        switch ($action)
        {
            case 'viewByDate':
                if ($this->isGetBack())
                {
                    $this->onSearch();
                }
                else
                {
                    $this->Search();
                }

                break;

            case 'listByViewDataGrid':
            default:
                $this->listByViewDataGrid();
                break;
        }
    }

    /*
     * Called by handleRequest() to process loading the list / main page.
     */
    private function listByViewDataGrid()
    {
        $dataGridProperties = DataGrid::getRecentParamaters("activity:ActivityDataGrid");

        /* If this is the first time we visited the datagrid this session, the recent paramaters will
         * be empty.  Fill in some default values. */
        if ($dataGridProperties == array())
        {
            $dataGridProperties = array(
                'rangeStart'    => 0,
                'maxResults'    => 15,
                'filterVisible' => false
            );
        }

        /* Only show a month of activities. */
        $dataGridProperties['startDate'] = '';
        $dataGridProperties['endDate'] = '';
        $dataGridProperties['period'] = 'DATE_SUB(CURDATE(), INTERVAL 1 MONTH)';

        $dataGrid = DataGrid::get("activity:ActivityDataGrid", $dataGridProperties);

        $quickLinks = $this->getQuickLinks();

        if (!eval(Hooks::get('ACTIVITY_LIST_BY_VIEW_DG'))) return;

        $this->_template->assign('quickLinks', $quickLinks);
        $this->_template->assign('active', $this);
        $this->_template->assign('dataGrid', $dataGrid);
        $this->_template->assign('userID', $_SESSION['OSATS']->getUserID());

        $activityEntries = new ActivityEntries($this->_siteID);
        $this->_template->assign('numActivities', $activityEntries->getCount());

        $this->_template->display('./modules/activity/ActivityDataGrid.tpl');
    }

    /*
     * Called by handleRequest() to handle displaying the search page.
     */
    private function search()
    {
        if (!eval(Hooks::get('ACTIVITY_SEARCH'))) return;

        $this->_template->assign('isResultsMode', false);
        $this->_template->assign('wildCardString', '');
        $this->_template->assign('active', $this);
        $this->_template->display('./modules/activity/Search.tpl');
    }

    /*
     * Called by handleRequest() to process displaying the search results.
     */
    private function onSearch()
    {
        $periodString = $this->getTrimmedInput('period', $_GET);
        if (!empty($periodString) &&
            in_array($periodString, array('lastweek', 'lastmonth', 'lastsixmonths', 'lastyear', 'all')))
        {
            /* formats start and end date for searching */
            switch ($periodString)
            {
                case 'lastweek':
                    $period = 'DATE_SUB(CURDATE(), INTERVAL 1 WEEK)';
                    break;

                case 'lastmonth':
                    $period = 'DATE_SUB(CURDATE(), INTERVAL 1 MONTH)';
                    break;

                case 'lastsixmonths':
                    $period = 'DATE_SUB(CURDATE(), INTERVAL 6 MONTH)';
                    break;

                case 'lastyear':
                    $period = 'DATE_SUB(CURDATE(), INTERVAL 1 YEAR)';
                    break;

                case 'all':
                default:
                    $period = '';
                    break;
            }

            $startDate = '';
            $endDate = '';

            $startDateURLString = '';
            $endDateURLString   = '';
        }
        else
        {
            /* Do we have a valid starting date? */
            if (!$this->isRequiredIDValid('startDay', $_GET) ||
                !$this->isRequiredIDValid('startMonth', $_GET) ||
                !$this->isRequiredIDValid('startYear', $_GET))
            {
                CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, __('Invalid starting date.'));
            }

            /* Do we have a valid ending date? */
            if (!$this->isRequiredIDValid('endDay', $_GET) ||
                !$this->isRequiredIDValid('endMonth', $_GET) ||
                !$this->isRequiredIDValid('endYear', $_GET))
            {
                CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, __('Invalid ending date.'));
            }

            if (!checkdate($_GET['startMonth'], $_GET['startDay'], $_GET['startYear']))
            {
                CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, __('Invalid starting date.'));
            }

            if (!checkdate($_GET['endMonth'], $_GET['endDay'], $_GET['endYear']))
            {
                CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, __('Invalid ending date.'));
            }

            /* formats start and end date for searching */
            $startDate = DateUtility::formatSearchDate(
                $_GET['startMonth'], $_GET['startDay'], $_GET['startYear']
            );
            $endDate = DateUtility::formatSearchDate(
                $_GET['endMonth'], $_GET['endDay']+1, $_GET['endYear']
            );

            $startDateURLString = sprintf(
                '&amp;startMonth=%s&amp;startDay=%s&amp;startYear=%s',
                $_GET['startMonth'],
                $_GET['startDay'],
                $_GET['startYear']
            );

            $endDateURLString = sprintf(
                '&amp;endMonth=%s&amp;endDay=%s&amp;endYear=%s',
                $_GET['endMonth'],
                $_GET['endDay'],
                $_GET['endYear']
            );

            $period = '';
        }

        $baseURL = sprintf(
            'm=activity&amp;a=viewByDate&amp;getback=getback%s%s',
            $startDateURLString, $endDateURLString
        );

        $dataGridProperties = DataGrid::getRecentParamaters("activity:ActivityDataGrid");

        /* If this is the first time we visited the datagrid this session, the recent paramaters will
         * be empty.  Fill in some default values. */
        if ($dataGridProperties == array())
        {
            $dataGridProperties = array(
              'rangeStart'    => 0,
              'maxResults'    => 15,
              'filterVisible' => false
            );
        }

        $dataGridProperties['startDate'] = $startDate;
        $dataGridProperties['endDate']   = $endDate;
        $dataGridProperties['period']    = $period;

        $dataGrid = DataGrid::get("activity:ActivityDataGrid", $dataGridProperties);

        $quickLinks = $this->getQuickLinks();

        if (!eval(Hooks::get('ACTIVITY_LIST_BY_VIEW_DG'))) return;

        $this->_template->assign('quickLinks', $quickLinks);
        $this->_template->assign('active', $this);
        $this->_template->assign('dataGrid', $dataGrid);
        $this->_template->assign('userID', $_SESSION['OSATS']->getUserID());

        $activityEntries = new ActivityEntries($this->_siteID);
        $this->_template->assign('numActivities', $activityEntries->getCount());

        $this->_template->display('./modules/activity/ActivityDataGrid.tpl');
    }

    /**
     * Returns the "Quick Links" navigation HTML for the top right corner of
     * the Activities page.
     *
     * @return string "Quick Links" HTML
     */
    private function getQuickLinks()
    {
        $today = array(
          'month' => date('n'),
          'day'   => date('j'),
          'year'  => date('Y')
        );

        $yesterdayTimeStamp = DateUtility::subtractDaysFromDate(time(), 1);
        $yesterday = array(
          'month' => date('n', $yesterdayTimeStamp),
          'day'   => date('j', $yesterdayTimeStamp),
          'year'  => date('Y', $yesterdayTimeStamp)
        );

        $baseURL = sprintf(
          '%s?m=activity&amp;a=viewByDate&amp;getback=getback',
          osatutil::getIndexName()
        );

        $quickLinks[0] = sprintf(
          '<a href="%s&amp;startMonth=%s&amp;startDay=%s&amp;startYear=%s&amp;endMonth=%s&amp;endDay=%s&amp;endYear=%s">%s</a>',
          $baseURL,
          $today['month'],
          $today['day'],
          $today['year'],
          $today['month'],
          $today['day'],
          $today['year'],
          __('Today')
        );

        $quickLinks[1] = sprintf(
          '<a href="%s&amp;startMonth=%s&amp;startDay=%s&amp;startYear=%s&amp;endMonth=%s&amp;endDay=%s&amp;endYear=%s">%s</a>',
          $baseURL,
          $yesterday['month'],
          $yesterday['day'],
          $yesterday['year'],
          $yesterday['month'],
          $yesterday['day'],
          $yesterday['year'],
          __('Yesterday')
        );

        $quickLinks[2] = sprintf(
          '<a href="%s&amp;period=lastweek">%s</a>',
          $baseURL,
          __('Last Week')
        );

        $quickLinks[3] = sprintf(
          '<a href="%s&amp;period=lastmonth">%s</a>',
          $baseURL,
          __('Last Month')
        );

        $quickLinks[4] = sprintf(
          '<a href="%s&amp;period=lastsixmonths">%s</a>',
          $baseURL,
          __('Last 6 Months')
        );

        $quickLinks[5] = sprintf(
            '<a href="%s&amp;period=all">%s</a>',
            $baseURL,
            __('All')
        );

        return implode(' | ', $quickLinks);
    }
}