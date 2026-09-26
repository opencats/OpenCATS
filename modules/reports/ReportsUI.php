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

include_once(LEGACY_ROOT . '/lib/Statistics.php');
include_once(LEGACY_ROOT . '/lib/Charts.php');
include_once(LEGACY_ROOT . '/lib/DateUtility.php');
include_once(LEGACY_ROOT . '/lib/Candidates.php');
include_once(LEGACY_ROOT . '/lib/CommonErrors.php');

class ReportsUI extends UserInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = true;
        $this->_moduleDirectory = 'reports';
        $this->_moduleName = 'reports';
        $this->_moduleTabText = 'Reports';
        $this->_subTabs = array(
                'EEO Reports' => CATSUtility::getIndexName() . '?m=reports&amp;a=customizeEEOReport'
            );
    }


    public function handleRequest()
    {
        if (!eval(Hooks::get('REPORTS_HANDLE_REQUEST'))) return;

        $action = $this->getAction();
        switch ($action)
        {
            case 'graphView':
                if ($this->getUserAccessLevel('reports.show') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->graphView();
                break;

            case 'generateJobOrderReportPDF':
                if ($this->getUserAccessLevel('reports.show') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->generateJobOrderReportPDF();
                break;

            case 'showSubmissionReport':
                if ($this->getUserAccessLevel('reports.show') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->showSubmissionReport();
                break;

            case 'showPlacementReport':
                if ($this->getUserAccessLevel('reports.show') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->showPlacementReport();
                break;

            case 'customizeJobOrderReport':
                if ($this->getUserAccessLevel('reports.show') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->customizeJobOrderReport();
                break;

            case 'customizeEEOReport':
                if ($this->getUserAccessLevel('reports.show') < ACCESS_LEVEL_READ ||
                    !$_SESSION['CATS']->canSeeEEOInfo())
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->customizeEEOReport();
                break;

            case 'generateEEOReportPreview':
                if ($this->getUserAccessLevel('reports.show') < ACCESS_LEVEL_READ ||
                    !$_SESSION['CATS']->canSeeEEOInfo())
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->generateEEOReportPreview();
                break;

            case 'reports':
            default:
                if ($this->getUserAccessLevel('reports.show') < ACCESS_LEVEL_READ)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
                }
                $this->reports();
                break;
        }
    }

    private function reports()
    {
        /* Grab an instance of Statistics. */
        $statistics = new Statistics();

        /* Get company statistics. */
        $statisticsData['totalCompanies']     = $statistics->getCompanyCount(TIME_PERIOD_TODATE);
        $statisticsData['companiesToday']     = $statistics->getCompanyCount(TIME_PERIOD_TODAY);
        $statisticsData['companiesYesterday'] = $statistics->getCompanyCount(TIME_PERIOD_YESTERDAY);
        $statisticsData['companiesThisWeek']  = $statistics->getCompanyCount(TIME_PERIOD_THISWEEK);
        $statisticsData['companiesLastWeek']  = $statistics->getCompanyCount(TIME_PERIOD_LASTWEEK);
        $statisticsData['companiesThisMonth'] = $statistics->getCompanyCount(TIME_PERIOD_THISMONTH);
        $statisticsData['companiesLastMonth'] = $statistics->getCompanyCount(TIME_PERIOD_LASTMONTH);
        $statisticsData['companiesThisYear']  = $statistics->getCompanyCount(TIME_PERIOD_THISYEAR);
        $statisticsData['companiesLastYear']  = $statistics->getCompanyCount(TIME_PERIOD_LASTYEAR);

        /* Get candidate statistics. */
        $statisticsData['totalCandidates']     = $statistics->getCandidateCount(TIME_PERIOD_TODATE);
        $statisticsData['candidatesToday']     = $statistics->getCandidateCount(TIME_PERIOD_TODAY);
        $statisticsData['candidatesYesterday'] = $statistics->getCandidateCount(TIME_PERIOD_YESTERDAY);
        $statisticsData['candidatesThisWeek']  = $statistics->getCandidateCount(TIME_PERIOD_THISWEEK);
        $statisticsData['candidatesLastWeek']  = $statistics->getCandidateCount(TIME_PERIOD_LASTWEEK);
        $statisticsData['candidatesThisMonth'] = $statistics->getCandidateCount(TIME_PERIOD_THISMONTH);
        $statisticsData['candidatesLastMonth'] = $statistics->getCandidateCount(TIME_PERIOD_LASTMONTH);
        $statisticsData['candidatesThisYear']  = $statistics->getCandidateCount(TIME_PERIOD_THISYEAR);
        $statisticsData['candidatesLastYear']  = $statistics->getCandidateCount(TIME_PERIOD_LASTYEAR);

        /* Get submission statistics. */
        $statisticsData['totalSubmissions']     = $statistics->getSubmissionCount(TIME_PERIOD_TODATE);
        $statisticsData['submissionsToday']     = $statistics->getSubmissionCount(TIME_PERIOD_TODAY);
        $statisticsData['submissionsYesterday'] = $statistics->getSubmissionCount(TIME_PERIOD_YESTERDAY);
        $statisticsData['submissionsThisWeek']  = $statistics->getSubmissionCount(TIME_PERIOD_THISWEEK);
        $statisticsData['submissionsLastWeek']  = $statistics->getSubmissionCount(TIME_PERIOD_LASTWEEK);
        $statisticsData['submissionsThisMonth'] = $statistics->getSubmissionCount(TIME_PERIOD_THISMONTH);
        $statisticsData['submissionsLastMonth'] = $statistics->getSubmissionCount(TIME_PERIOD_LASTMONTH);
        $statisticsData['submissionsThisYear']  = $statistics->getSubmissionCount(TIME_PERIOD_THISYEAR);
        $statisticsData['submissionsLastYear']  = $statistics->getSubmissionCount(TIME_PERIOD_LASTYEAR);

		/* Get placement statistics. */
        $statisticsData['totalPlacements']     = $statistics->getPlacementCount(TIME_PERIOD_TODATE);
        $statisticsData['placementsToday']     = $statistics->getPlacementCount(TIME_PERIOD_TODAY);
        $statisticsData['placementsYesterday'] = $statistics->getPlacementCount(TIME_PERIOD_YESTERDAY);
        $statisticsData['placementsThisWeek']  = $statistics->getPlacementCount(TIME_PERIOD_THISWEEK);
        $statisticsData['placementsLastWeek']  = $statistics->getPlacementCount(TIME_PERIOD_LASTWEEK);
        $statisticsData['placementsThisMonth'] = $statistics->getPlacementCount(TIME_PERIOD_THISMONTH);
        $statisticsData['placementsLastMonth'] = $statistics->getPlacementCount(TIME_PERIOD_LASTMONTH);
        $statisticsData['placementsThisYear']  = $statistics->getPlacementCount(TIME_PERIOD_THISYEAR);
        $statisticsData['placementsLastYear']  = $statistics->getPlacementCount(TIME_PERIOD_LASTYEAR);

        /* Get contact statistics. */
        $statisticsData['totalContacts']     = $statistics->getContactCount(TIME_PERIOD_TODATE);
        $statisticsData['contactsToday']     = $statistics->getContactCount(TIME_PERIOD_TODAY);
        $statisticsData['contactsYesterday'] = $statistics->getContactCount(TIME_PERIOD_YESTERDAY);
        $statisticsData['contactsThisWeek']  = $statistics->getContactCount(TIME_PERIOD_THISWEEK);
        $statisticsData['contactsLastWeek']  = $statistics->getContactCount(TIME_PERIOD_LASTWEEK);
        $statisticsData['contactsThisMonth'] = $statistics->getContactCount(TIME_PERIOD_THISMONTH);
        $statisticsData['contactsLastMonth'] = $statistics->getContactCount(TIME_PERIOD_LASTMONTH);
        $statisticsData['contactsThisYear']  = $statistics->getContactCount(TIME_PERIOD_THISYEAR);
        $statisticsData['contactsLastYear']  = $statistics->getContactCount(TIME_PERIOD_LASTYEAR);

        /* Get job order statistics. */
        $statisticsData['totalJobOrders']     = $statistics->getJobOrderCount(TIME_PERIOD_TODATE);
        $statisticsData['jobOrdersToday']     = $statistics->getJobOrderCount(TIME_PERIOD_TODAY);
        $statisticsData['jobOrdersYesterday'] = $statistics->getJobOrderCount(TIME_PERIOD_YESTERDAY);
        $statisticsData['jobOrdersThisWeek']  = $statistics->getJobOrderCount(TIME_PERIOD_THISWEEK);
        $statisticsData['jobOrdersLastWeek']  = $statistics->getJobOrderCount(TIME_PERIOD_LASTWEEK);
        $statisticsData['jobOrdersThisMonth'] = $statistics->getJobOrderCount(TIME_PERIOD_THISMONTH);
        $statisticsData['jobOrdersLastMonth'] = $statistics->getJobOrderCount(TIME_PERIOD_LASTMONTH);
        $statisticsData['jobOrdersThisYear']  = $statistics->getJobOrderCount(TIME_PERIOD_THISYEAR);
        $statisticsData['jobOrdersLastYear']  = $statistics->getJobOrderCount(TIME_PERIOD_LASTYEAR);

        if (!eval(Hooks::get('REPORTS_SHOW'))) return;

        $this->_template->assign('active', $this);
        $this->_template->assign('statisticsData', $statisticsData);
        $this->_template->display('./modules/reports/Reports.tpl');
    }

    private function graphView()
    {
        if (!$this->isRequiredIDValid('jobOrderID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }
        $statistics = new Statistics();
        $graph = Charts::pipeline($statistics->getPipelineData((int) $_GET['jobOrderID']));
        $pipelineGraph = '';
        if (eval(Hooks::get('GRAPH_MINI_PIPELINE')))
        {
            $pipelineGraph = Charts::render('expanded-pipeline', $graph);
        }
        $this->_template->assign('pipelineGraph', $pipelineGraph);

        if (!eval(Hooks::get('REPORTS_GRAPH'))) return;

        $this->_template->assign('active', $this);
        $this->_template->display('./modules/reports/GraphView.tpl');
    }

    private function showSubmissionReport()
    {
        //FIXME: getTrimmedInput
        if (isset($_GET['period']) && !empty($_GET['period']))
        {
            $period = $_GET['period'];
        }
        else
        {
            $period = '';
        }


        switch ($period)
        {
            case 'yesterday':
                $period = TIME_PERIOD_YESTERDAY;
                $reportTitle = 'Yesterday\'s Report';
                break;

            case 'thisWeek':
                $period = TIME_PERIOD_THISWEEK;
                $reportTitle = 'This Week\'s Report';
                break;

            case 'lastWeek':
                $period = TIME_PERIOD_LASTWEEK;
                $reportTitle = 'Last Week\'s Report';
                break;

            case 'thisMonth':
                $period = TIME_PERIOD_THISMONTH;
                $reportTitle = 'This Month\'s Report';
                break;

            case 'lastMonth':
                $period = TIME_PERIOD_LASTMONTH;
                $reportTitle = 'Last Month\'s Report';
                break;

            case 'thisYear':
                $period = TIME_PERIOD_THISYEAR;
                $reportTitle = 'This Year\'s Report';
                break;

            case 'lastYear':
                $period = TIME_PERIOD_LASTYEAR;
                $reportTitle = 'Last Year\'s Report';
                break;

            case 'toDate':
                $period = TIME_PERIOD_TODATE;
                $reportTitle = 'To Date Report';
                break;

            case 'today':
            default:
                $period = TIME_PERIOD_TODAY;
                $reportTitle = 'Today\'s Report';
                break;
        }

        $statistics = new Statistics();
        $submissionJobOrdersRS = $statistics->getSubmissionJobOrders($period);

        foreach ($submissionJobOrdersRS as $rowIndex => $submissionJobOrdersData)
        {
            /* Querys inside loops are bad, but I don't think there is any avoiding this. */
            $submissionJobOrdersRS[$rowIndex]['submissionsRS'] = $statistics->getSubmissionsByJobOrder(
                $period, $submissionJobOrdersData['jobOrderID']
            );
        }

        if (!eval(Hooks::get('REPORTS_SHOW_SUBMISSION'))) return;

        $this->_template->assign('reportTitle', $reportTitle);
        $this->_template->assign('submissionJobOrdersRS', $submissionJobOrdersRS);
        $this->_template->display('./modules/reports/SubmissionReport.tpl');
    }

    private function showPlacementReport()
    {
        //FIXME: getTrimmedInput
        if (isset($_GET['period']) && !empty($_GET['period']))
        {
            $period = $_GET['period'];
        }
        else
        {
            $period = '';
        }


        switch ($period)
        {
            case 'yesterday':
                $period = TIME_PERIOD_YESTERDAY;
                $reportTitle = 'Yesterday\'s Report';
                break;

            case 'thisWeek':
                $period = TIME_PERIOD_THISWEEK;
                $reportTitle = 'This Week\'s Report';
                break;

            case 'lastWeek':
                $period = TIME_PERIOD_LASTWEEK;
                $reportTitle = 'Last Week\'s Report';
                break;

            case 'thisMonth':
                $period = TIME_PERIOD_THISMONTH;
                $reportTitle = 'This Month\'s Report';
                break;

            case 'lastMonth':
                $period = TIME_PERIOD_LASTMONTH;
                $reportTitle = 'Last Month\'s Report';
                break;

            case 'thisYear':
                $period = TIME_PERIOD_THISYEAR;
                $reportTitle = 'This Year\'s Report';
                break;

            case 'lastYear':
                $period = TIME_PERIOD_LASTYEAR;
                $reportTitle = 'Last Year\'s Report';
                break;

            case 'toDate':
                $period = TIME_PERIOD_TODATE;
                $reportTitle = 'To Date Report';
                break;

            case 'today':
            default:
                $period = TIME_PERIOD_TODAY;
                $reportTitle = 'Today\'s Report';
                break;
        }

        $statistics = new Statistics();
        $placementsJobOrdersRS = $statistics->getPlacementsJobOrders($period);

        foreach ($placementsJobOrdersRS as $rowIndex => $placementsJobOrdersData)
        {
            /* Querys inside loops are bad, but I don't think there is any avoiding this. */
            $placementsJobOrdersRS[$rowIndex]['placementsRS'] = $statistics->getPlacementsByJobOrder(
                $period, $placementsJobOrdersData['jobOrderID']
            );
        }

        if (!eval(Hooks::get('REPORTS_SHOW_SUBMISSION'))) return;

        $this->_template->assign('reportTitle', $reportTitle);
        $this->_template->assign('placementsJobOrdersRS', $placementsJobOrdersRS);
        $this->_template->display('./modules/reports/PlacedReport.tpl');
    }

    private function customizeJobOrderReport()
    {
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        $jobOrderID = $_GET['jobOrderID'];

        $siteName = $_SESSION['CATS']->getSiteName();


        $statistics = new Statistics();
        $data = $statistics->getJobOrderReport($jobOrderID);

        /* Bail out if we got an empty result set. */
        if (empty($data))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'The specified job order ID could not be found.');
        }

        $reportParameters['siteName'] = $siteName;
        $reportParameters['companyName'] = $data['companyName'];
        $reportParameters['jobOrderName'] = $data['title'];
        $reportParameters['accountManager'] = $data['ownerFullName'];
        $reportParameters['recruiter'] = $data['recruiterFullName'];

        $reportParameters['periodLine'] = sprintf(
            '%s - %s',
            strtok($data['dateCreated'], ' '),
            DateUtility::getAdjustedDate('m-d-y')
        );

        $reportParameters['dataSet1'] = $data['pipeline'];
        $reportParameters['dataSet2'] = $data['submitted'];
        $reportParameters['dataSet3'] = $data['pipelineInterving'];
        $reportParameters['dataSet4'] = $data['pipelinePlaced'];

        $dataSet = array(
            $reportParameters['dataSet4'],
            $reportParameters['dataSet3'],
            $reportParameters['dataSet2'],
            $reportParameters['dataSet1']
        );

        $this->_template->assign('reportParameters', $reportParameters);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', '');
        $this->_template->display('./modules/reports/JobOrderReport.tpl');
    }

    private function customizeEEOReport()
    {
        $this->_template->assign('modePeriod', 'all');
        $this->_template->assign('modeStatus', 'all');
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', '');
        $this->_template->display('./modules/reports/EEOReport.tpl');
    }

    private function generateJobOrderReportPDF()
    {
        include_once(LEGACY_ROOT . '/vendor/autoload.php');

        $siteName       = $this->getTrimmedInput('siteName', $_GET);
        $companyName    = $this->getTrimmedInput('companyName', $_GET);
        $jobOrderName   = $this->getTrimmedInput('jobOrderName', $_GET);
        $periodLine     = $this->getTrimmedInput('periodLine', $_GET);
        $accountManager = $this->getTrimmedInput('accountManager', $_GET);
        $recruiter      = $this->getTrimmedInput('recruiter', $_GET);
        $notes          = $this->getTrimmedInput('notes', $_GET);

        if (isset($_GET['dataSet']))
        {
            $dataSet = $_GET['dataSet'];
            $dataSet = explode(',', $dataSet);
        }
        else
        {
            $dataSet = array(4, 3, 2, 1);
        }


        /* PDF Font Face. */
        // FIXME: Customizable.
        $fontFace = 'Arial';

        $pdf = new FPDF();
        $pdf->AddPage();

        if (!eval(Hooks::get('REPORTS_CUSTOMIZE_JO_REPORT_PRE'))) return;

        $pdf->SetXY(25, 35);
        $pdf->SetFont($fontFace, 'BU', 14);
        $pdf->Write(5, "Recruiting Summary Report\n");

        $pdf->SetFont($fontFace, '', 10);
        $pdf->SetX(25);
        $pdf->Write(5, DateUtility::getAdjustedDate('l, F d, Y') . "\n\n\n");

        $pdf->SetFont($fontFace, 'B', 10);
        $pdf->SetX(25);
        $pdf->Write(5, 'Company: '. $companyName . "\n");

        $pdf->SetFont($fontFace, '', 10);
        $pdf->SetX(25);
        $pdf->Write(5, 'Position: ' . $jobOrderName . "\n\n");

        $pdf->SetFont($fontFace, '', 10);
        $pdf->SetX(25);
        $pdf->Write(5, 'Period: ' . $periodLine . "\n\n");

        $pdf->SetFont($fontFace, '', 10);
        $pdf->SetX(25);
        $pdf->Write(5, 'Account Manager: ' . $accountManager . "\n");

        $pdf->SetFont($fontFace, '', 10);
        $pdf->SetX(25);
        $pdf->Write(5, 'Recruiter: ' . $recruiter . "\n");

        // The four labelled values below contain all information from the old image.
        $graph = Charts::comparison('Recruiting Summary',
            array('Screened', 'Submitted', 'Interviewed', 'Placed'), $dataSet);
        if (!eval(Hooks::get('GRAPH_JOB_ORDER_REPORT'))) return;

        $pdf->SetXY(25, 100);
        $pdf->SetFont($fontFace, '', 10);
        $pdf->Write(5, 'Total Candidates ');
        $pdf->SetTextColor(255, 0, 0);
        $pdf->Write(5, 'Screened');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Write(5, ' by ' . $siteName . ": \n\n");

        $pdf->SetX(25);
        $pdf->SetFont($fontFace, '', 10);
        $pdf->Write(5, 'Total Candidates ');
        $pdf->SetTextColor(0, 125, 0);
        $pdf->Write(5, 'Submitted');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Write(5, ' to ' . $companyName . ": \n\n");

        $pdf->SetX(25);
        $pdf->SetFont($fontFace, '', 10);
        $pdf->Write(5, 'Total Candidates ');
        $pdf->SetTextColor(0, 0, 255);
        $pdf->Write(5, 'Interviewed');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Write(5, ' by ' . $companyName . ": \n\n");

        $pdf->SetX(25);
        $pdf->SetFont($fontFace, '', 10);
        $pdf->Write(5, 'Total Candidates ');
        $pdf->SetTextColor(255, 75, 0);
        $pdf->Write(5, 'Placed');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Write(5, ' at ' . $companyName . ": \n\n\n");

        if ($notes != '')
        {
            $pdf->SetX(25);
            $pdf->SetFont($fontFace, '', 10);
            $pdf->Write(5, "Notes:\n");

            $len = strlen($notes);
            $maxChars = 70;

            $pdf->SetLeftMargin(25);
            $pdf->SetRightMargin(25);
            $pdf->SetX(25);
            $pdf->Write(5, $notes . "\n");
        }

        $pdf->SetXY(165, 100);
        $pdf->SetFont($fontFace, 'B', 10);
        $pdf->Write(5, $dataSet[0] . "\n\n");
        $pdf->SetX(165);
        $pdf->Write(5, $dataSet[1] . "\n\n");
        $pdf->SetX(165);
        $pdf->Write(5, $dataSet[2] . "\n\n");
        $pdf->SetX(165);
        $pdf->Write(5, $dataSet[3] . "\n\n");

        $pdf->Rect(3, 6, 204, 285);

        if (!eval(Hooks::get('REPORTS_CUSTOMIZE_JO_REPORT_POST'))) return;

        $pdf->Output();
        die();
    }

    function generateEEOReportPreview()
    {
        $modePeriod = $this->getTrimmedInput('period', $_GET);
        $modeStatus = $this->getTrimmedInput('status', $_GET);

        $statistics = new Statistics();
        $EEOReportStatistics = $statistics->getEEOReport($modePeriod, $modeStatus);

        //print_r($EEOReportStatistics);

        switch ($modePeriod)
        {
            case 'week':
                $labelPeriod = ' Last Week';
                break;

            case 'month':
                $labelPeriod = ' Last Month';
                break;

            default:
                $labelPeriod = '';
                break;
        }

        switch ($modeStatus)
        {
            case 'rejected':
                $labelStatus = ' Rejected';
                break;

            case 'placed':
                $labelStatus = ' Placed';
                break;

            default:
                $labelStatus = '';
                break;
        }

        $charts = array();
        foreach (array('Ethnic' => 'Ethnic Type', 'Veteran' => 'Veteran Status') as $key => $title)
        {
            $rows = $EEOReportStatistics['rs' . $key . 'Statistics'];
            $labels = array_column($rows, 'EEO' . $key . 'Type');
            $data = array_column($rows, 'numberOfCandidates');
            $graph = Charts::comparison('Number of Candidates' . $labelStatus . ' by ' . $title . $labelPeriod,
                $labels, $data);
            $charts[$key] = eval(Hooks::get('GRAPH_GENERIC')) ? Charts::render('eeo-' . strtolower($key), $graph, true) : '';
        }
        $gender = $EEOReportStatistics['rsGenderStatistics'];
        $disability = $EEOReportStatistics['rsDisabledStatistics'];
        foreach (array(
            'Gender' => array('Male' => $gender['numberOfCandidatesMale'] ?? 0, 'Female' => $gender['numberOfCandidatesFemale'] ?? 0),
            'Disability' => array('Disabled' => $disability['numberOfCandidatesDisabled'] ?? 0, 'Non Disabled' => $disability['numberOfCandidatesNonDisabled'] ?? 0)
        ) as $key => $values)
        {
            $graph = Charts::distribution('Number of Candidates by ' . $key, array_keys($values), array_values($values),
                'Recorded categories only; candidates without a recorded value are not included.');
            $charts[$key] = eval(Hooks::get('GRAPH_GENERIC_PIE')) ? Charts::render('eeo-' . strtolower($key), $graph) : '';
        }

        $EEOSettings = new EEOSettings();
        $EEOSettingsRS = $EEOSettings->getAll();

        $this->_template->assign('EEOReportStatistics', $EEOReportStatistics);
        $this->_template->assign('charts', $charts);
        $this->_template->assign('modePeriod', $modePeriod);
        $this->_template->assign('modeStatus', $modeStatus);
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', '');
        $this->_template->display('./modules/reports/EEOReport.tpl');
    }
}

?>
