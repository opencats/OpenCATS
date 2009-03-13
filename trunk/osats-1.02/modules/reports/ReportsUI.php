<?php
/*
 * OSATS
 * Reports Module
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the OSATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.OSATSone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "OSATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * $Id: ReportsUI.php 3810 2007-12-05 19:13:25Z brian $
 */

include_once('./lib/Statistics.php');
include_once('./lib/DateUtility.php');
include_once('./lib/Candidates.php');
include_once('./lib/CommonErrors.php');
include_once('./lib/i18n.php');

class ReportsUI extends UserInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = true;
        $this->_moduleDirectory = 'reports';
        $this->_moduleName = 'reports';
        $this->_moduleTabText = __('Reports');
        $this->_subTabs = array(
                'EEO Reports' => osatutil::getIndexName() . '?m=reports&amp;a=customizeEEOReport'
            );
    }


    public function handleRequest()
    {
        if (!eval(Hooks::get('REPORTS_HANDLE_REQUEST'))) return;

        $action = $this->getAction();
        switch ($action)
        {
            case 'graphView':
                $this->graphView();
                break;

            case 'generateJobOrderReportPDF':
                $this->generateJobOrderReportPDF();
                break;

            case 'showSubmissionReport':
                $this->showSubmissionReport();
                break;

            case 'showPlacementReport':
                $this->showPlacementReport();
                break;

            case 'customizeJobOrderReport':
                $this->customizeJobOrderReport();
                break;

            case 'customizeEEOReport':
                $this->customizeEEOReport();
                break;

            case 'generateEEOReportPreview':
                $this->generateEEOReportPreview();
                break;

            case 'reports':
            default:
                $this->reports();
                break;
        }
    }

    private function reports()
    {
        /* Grab an instance of Statistics. */
        $statistics = new Statistics($this->_siteID);

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
        if (isset($_GET['theImage']))
        {
            $this->_template->assign('theImage', $_GET['theImage']);
        }
        else
        {
            $this->_template->assign('theImage', '');
        }

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
                $reportTitle = __("Yesterday's Report");
                break;

            case 'thisWeek':
                $period = TIME_PERIOD_THISWEEK;
                $reportTitle = __("This Week's Report");
                break;

            case 'lastWeek':
                $period = TIME_PERIOD_LASTWEEK;
                $reportTitle = __("Last Week's Report");
                break;

            case 'thisMonth':
                $period = TIME_PERIOD_THISMONTH;
                $reportTitle = __("This Month's Report");
                break;

            case 'lastMonth':
                $period = TIME_PERIOD_LASTMONTH;
                $reportTitle = __("Last Month's Report");
                break;

            case 'thisYear':
                $period = TIME_PERIOD_THISYEAR;
                $reportTitle = __("This Year's Report");
                break;

            case 'lastYear':
                $period = TIME_PERIOD_LASTYEAR;
                $reportTitle = __("Last Year's Report");
                break;

            case 'toDate':
                $period = TIME_PERIOD_TODATE;
                $reportTitle = __("To Date Report");
                break;

            case 'today':
            default:
                $period = TIME_PERIOD_TODAY;
                $reportTitle = __("Today's Report");
                break;
        }

        $statistics = new Statistics($this->_siteID);
        $submissionJobOrdersRS = $statistics->getSubmissionJobOrders($period);

        foreach ($submissionJobOrdersRS as $rowIndex => $submissionJobOrdersData)
        {
            /* Querys inside loops are bad, but I don't think there is any avoiding this. */
            $submissionJobOrdersRS[$rowIndex]['submissionsRS'] = $statistics->getSubmissionsByJobOrder(
                $period, $submissionJobOrdersData['jobOrderID'], $this->_siteID
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
                $reportTitle = __("Yesterday's Report");
                break;

            case 'thisWeek':
                $period = TIME_PERIOD_THISWEEK;
                $reportTitle = __("This Week's Report");
                break;

            case 'lastWeek':
                $period = TIME_PERIOD_LASTWEEK;
                $reportTitle = __("Last Week's Report");
                break;

            case 'thisMonth':
                $period = TIME_PERIOD_THISMONTH;
                $reportTitle = 'This Month\'s Report';
                break;

            case 'lastMonth':
                $period = TIME_PERIOD_LASTMONTH;
                $reportTitle = __("Last Month's Report");
                break;

            case 'thisYear':
                $period = TIME_PERIOD_THISYEAR;
                $reportTitle = __("This Year's Report");
                break;

            case 'lastYear':
                $period = TIME_PERIOD_LASTYEAR;
                $reportTitle = __("Last Year's Report");
                break;

            case 'toDate':
                $period = TIME_PERIOD_TODATE;
                $reportTitle = __("To Date Report");
                break;

            case 'today':
            default:
                $period = TIME_PERIOD_TODAY;
                $reportTitle = __("Today's Report");
                break;
        }

        $statistics = new Statistics($this->_siteID);
        $placementsJobOrdersRS = $statistics->getPlacementsJobOrders($period);

        foreach ($placementsJobOrdersRS as $rowIndex => $placementsJobOrdersData)
        {
            /* Querys inside loops are bad, but I don't think there is any avoiding this. */
            $placementsJobOrdersRS[$rowIndex]['placementsRS'] = $statistics->getPlacementsByJobOrder(
                $period, $placementsJobOrdersData['jobOrderID'], $this->_siteID
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
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, __('Invalid job order ID.'));
        }

        $jobOrderID = $_GET['jobOrderID'];

        $siteName = $_SESSION['OSATS']->getSiteName();


        $statistics = new Statistics($this->_siteID);
        $data = $statistics->getJobOrderReport($jobOrderID);

        /* Bail out if we got an empty result set. */
        if (empty($data))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, __('The specified job order ID could not be found.'));
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
        /* E_STRICT doesn't like FPDF. */
        $errorReporting = error_reporting();
        error_reporting($errorReporting & ~ E_STRICT);
        include_once('./lib/fpdf/fpdf.php');
        error_reporting($errorReporting);

        // FIXME: Hook?
        $isASP = $_SESSION['OSATS']->isASP();

        $unixName = $_SESSION['OSATS']->getUnixName();

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

        if ($isASP && $unixName == 'cognizo')
        {
            /* TODO: MAKE THIS CUSTOMIZABLE FOR EVERYONE. */
            $pdf->SetFont($fontFace, 'B', 10);
            $pdf->Image('images/cognizo-logo.jpg', 130, 10, 59, 20);
            $pdf->SetXY(129,27);
            $pdf->Write(5, 'Information Technology Consulting');
        }

        $pdf->SetXY(25, 35);
        $pdf->SetFont($fontFace, 'BU', 14);
        $pdf->Write(5, __('Recruiting Summary Report'). "\n");

        $pdf->SetFont($fontFace, '', 10);
        $pdf->SetX(25);
        $pdf->Write(5, DateUtility::getAdjustedDate('l, F d, Y') . "\n\n\n");

        $pdf->SetFont($fontFace, 'B', 10);
        $pdf->SetX(25);
        $pdf->Write(5, __('Company').': '. $companyName . "\n");

        $pdf->SetFont($fontFace, '', 10);
        $pdf->SetX(25);
        $pdf->Write(5, __('Position'). ': ' . $jobOrderName . "\n\n");

        $pdf->SetFont($fontFace, '', 10);
        $pdf->SetX(25);
        $pdf->Write(5, __('Period'). ': ' . $periodLine . "\n\n");

        $pdf->SetFont($fontFace, '', 10);
        $pdf->SetX(25);
        $pdf->Write(5, __('Account Manager'). ': ' . $accountManager . "\n");

        $pdf->SetFont($fontFace, '', 10);
        $pdf->SetX(25);
        $pdf->Write(5, __('Recruiter'). ': ' . $recruiter . "\n");

        /* Note that the server is not logged in when getting this file from
         * itself.
         */
        // FIXME: Pass session cookie in URL? Use cURL and send a cookie? I
        //        really don't like this... There has to be a way.
        // FIXME: "could not make seekable" - http://demo.OSATSone.net/index.php?m=graphs&a=jobOrderReportGraph&data=%2C%2C%2C
        //        in /usr/local/www/OSATSone.net/data/lib/fpdf/fpdf.php on line 1500
        $URI = osatutil::getAbsoluteURI(
            osatutil::getIndexName()
            . '?m=graphs&a=jobOrderReportGraph&data='
            . urlencode(implode(',', $dataSet))
        );

        $pdf->Image($URI, 70, 95, 80, 80, 'jpg');

        $pdf->SetXY(25,180);
        $pdf->SetFont($fontFace, '', 10);
        $pdf->Write(5, __('Total Candidates'). ' ');
        $pdf->SetTextColor(255, 0, 0);
        $pdf->Write(5, __('Screened'));
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Write(5, ' '. __('by'). ' ' . $siteName . ": \n\n");

        $pdf->SetX(25);
        $pdf->SetFont($fontFace, '', 10);
        $pdf->Write(5, __('Total Candidates'). ' ');
        $pdf->SetTextColor(0, 125, 0);
        $pdf->Write(5, __('Submitted'));
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Write(5, __('to'). ' ' . $companyName . ": \n\n");

        $pdf->SetX(25);
        $pdf->SetFont($fontFace, '', 10);
        $pdf->Write(5, __('Total Candidates'). ' ');
        $pdf->SetTextColor(0, 0, 255);
        $pdf->Write(5, __('Interviewed'));
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Write(5, ' '. __('by'). ' ' . $companyName . ": \n\n");

        $pdf->SetX(25);
        $pdf->SetFont($fontFace, '', 10);
        $pdf->Write(5, __('Total Candidates'). ' ');
        $pdf->SetTextColor(255, 75, 0);
        $pdf->Write(5, __('Placed'));
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Write(5, ' '. __('at'). ' ' . $companyName . ": \n\n\n");

        if ($notes != '')
        {
            $pdf->SetX(25);
            $pdf->SetFont($fontFace, '', 10);
            $pdf->Write(5, __('Notes'). ":\n");

            $len = strlen($notes);
            $maxChars = 70;

            $pdf->SetLeftMargin(25);
            $pdf->SetRightMargin(25);
            $pdf->SetX(25);
            $pdf->Write(5, $notes . "\n");
        }

        $pdf->SetXY(165, 180);
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

        $statistics = new Statistics($this->_siteID);
        $EEOReportStatistics = $statistics->getEEOReport($modePeriod, $modeStatus);

        //print_r($EEOReportStatistics);

        switch ($modePeriod)
        {
            case 'week':
                $labelPeriod = ' '.__('Last Week');
                break;

            case 'month':
                $labelPeriod = ' '.__('Last Month');
                break;

            default:
                $labelPeriod = '';
                break;
        }

        switch ($modeStatus)
        {
            case 'rejected':
                $labelStatus = ' '.__('Rejected');
                break;

            case 'placed':
                $labelStatus = ' '.__('Placed');
                break;

            default:
                $labelStatus = '';
                break;
        }

        /* Produce the URL to the ethic statistics graph. */
        $labels = array();
        $data = array();

        $rsEthnicStatistics = $EEOReportStatistics['rsEthnicStatistics'];

        foreach ($rsEthnicStatistics as $index => $line)
        {
            $labels[] = $line['EEOEthnicType'];
            $data[] = $line['numberOfCandidates'];
        }

        $urlEthnicGraph = osatutil::getAbsoluteURI(
            sprintf("%s?m=graphs&a=generic&title=%s&labels=%s&data=%s&width=%s&height=%s",
                osatutil::getIndexName(),
                urlencode(__('Number of Candidates').$labelStatus.' '. __('by Ethnic Type').$labelPeriod),
                urlencode(implode(',', $labels)),
                urlencode(implode(',', $data)),
                400,
                240
            ));


        /* Produce the URL to the veteran status statistics graph. */
        $labels = array();
        $data = array();

        $rsVeteranStatistics = $EEOReportStatistics['rsVeteranStatistics'];

        foreach ($rsVeteranStatistics as $index => $line)
        {
            $labels[] = $line['EEOVeteranType'];
            $data[] = $line['numberOfCandidates'];
        }

        $urlVeteranGraph = osatutil::getAbsoluteURI(
            sprintf("%s?m=graphs&a=generic&title=%s&labels=%s&data=%s&width=%s&height=%s",
                osatutil::getIndexName(),
                urlencode(__('Number of Candidates').$labelStatus.' '. __('by Veteran Status').$labelPeriod),
                urlencode(implode(',', $labels)),
                urlencode(implode(',', $data)),
                400,
                240
            ));

        /* Produce the URL to the gender statistics graph. */
        $labels = array();
        $data = array();

        $rsGenderStatistics = $EEOReportStatistics['rsGenderStatistics'];

        $labels[] = __('Male'). ' ('.$rsGenderStatistics['numberOfCandidatesMale'].')';
        $data[] = $rsGenderStatistics['numberOfCandidatesMale'];

        $labels[] = __('Female'). ' ('.$rsGenderStatistics['numberOfCandidatesFemale'].')';
        $data[] = $rsGenderStatistics['numberOfCandidatesFemale'];

        $urlGenderGraph = osatutil::getAbsoluteURI(
            sprintf("%s?m=graphs&a=genericPie&title=%s&labels=%s&data=%s&width=%s&height=%s&legendOffset=%s",
                osatutil::getIndexName(),
                urlencode(__('Number of Candidates by Gender')),
                urlencode(implode(',', $labels)),
                urlencode(implode(',', $data)),
                320,
                300,
                1.575
            ));

        if ($rsGenderStatistics['numberOfCandidatesMale'] == 0 && $rsGenderStatistics['numberOfCandidatesFemale'] == 0)
        {
            $urlGenderGraph = "images/noDataByGender.png";
        }

        /* Produce the URL to the disability statistics graph. */
        $labels = array();
        $data = array();

        $rsDisabledStatistics = $EEOReportStatistics['rsDisabledStatistics'];

        $labels[] = 'Disabled ('.$rsDisabledStatistics['numberOfCandidatesDisabled'].')';
        $data[] = $rsDisabledStatistics['numberOfCandidatesDisabled'];

        $labels[] = 'Non Disabled ('.$rsDisabledStatistics['numberOfCandidatesNonDisabled'].')';
        $data[] = $rsDisabledStatistics['numberOfCandidatesNonDisabled'];

        $urlDisabilityGraph = osatutil::getAbsoluteURI(
            sprintf("%s?m=graphs&a=genericPie&title=%s&labels=%s&data=%s&width=%s&height=%s&legendOffset=%s",
                osatutil::getIndexName(),
                urlencode(__('Number of Candidates by Disability Status')),
                urlencode(implode(',', $labels)),
                urlencode(implode(',', $data)),
                320,
                300,
                1.575
            ));

        if ($rsDisabledStatistics['numberOfCandidatesNonDisabled'] == 0 && $rsDisabledStatistics['numberOfCandidatesDisabled'] == 0)
        {
            $urlDisabilityGraph = "images/noDataByDisability.png";
        }

        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();

        $this->_template->assign('EEOReportStatistics', $EEOReportStatistics);
        $this->_template->assign('urlEthnicGraph', $urlEthnicGraph);
        $this->_template->assign('urlVeteranGraph', $urlVeteranGraph);
        $this->_template->assign('urlGenderGraph', $urlGenderGraph);
        $this->_template->assign('urlDisabilityGraph', $urlDisabilityGraph);
        $this->_template->assign('modePeriod', $modePeriod);
        $this->_template->assign('modeStatus', $modeStatus);
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', '');
        $this->_template->display('./modules/reports/EEOReport.tpl');
    }
}