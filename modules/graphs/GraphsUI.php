<?php
/*
 * CATS
 * Reports Module
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * $Id: GraphsUI.php 3710 2007-11-27 16:41:19Z brian $
 */

include_once('./lib/Statistics.php');
include_once('./lib/Graphs.php');
include_once('./lib/GraphGenerator.php');
include_once('./lib/DateUtility.php');
include_once('./lib/CommonErrors.php');
include_once('./lib/Dashboard.php');


class GraphsUI extends UserInterface
{
    private $width;
    private $height;


    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = false;
        $this->_moduleDirectory = 'graphs';
        $this->_moduleName = 'graphs';
        $this->_subTabs = array();

        if (isset($_GET['width']) && $_GET['width'] < 2000)
        {
            $this->width = $_GET['width'];
        }
        else
        {
            $this->width = 300;
        }

        if (isset($_GET['height']) && $_GET['height'] < 1200)
        {
            $this->height = $_GET['height'];
        }
        else
        {
            $this->height = 200;
        }
    }


    public function handleRequest()
    {
        $action = $this->getAction();

        //These graphs do not require a login.
        switch ($action)
        {
            case 'testGraph':
                $this->testGraph();
                return;

            case 'wordVerify':
                $this->wordVerify();
                return;

            case 'jobOrderReportGraph':
                $this->jobOrderReportGraph();
                return;

            case 'generic':
                $this->generic();
                return;

            case 'genericPie':
                $this->genericPie();
                return;
        }

        if ($_SESSION['CATS']->isLoggedIn())
        {
            switch ($action)
            {
                case 'activity':
                    $this->activity();
                    return;

                case 'newCandidates':
                    $this->newCandidates();
                    return;

                case 'newJobOrders':
                    $this->newJobOrders();
                    return;

                case 'newSubmissions':
                    $this->newSubmissions();
                    return;

                case 'miniPlacementStatistics':
                    $this->miniPlacementStatistics();
                    return;

                case 'miniJobOrderPipeline':
                    $this->miniJobOrderPipeline();
                    return;

                default:
                    CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'No graph specified.');
                    return;
            }
        }
    }


    private function testGraph()
    {
        /* I am used for development purposes and intentionally empty. */
        $x = array(1, 2, 3, 4);
        $y = array(1, 2, 3, 4);
        $graph = new GraphSimple($x, $y, 'DarkGreen', 'Test Graph', $this->width, $this->height);

        if (!eval(Hooks::get('GRAPH_TEST'))) return;

        $graph->draw();
        die();
    }

    private function jobOrderReportGraph()
    {
        /* Build X values. */
        $data = $this->getTrimmedInput('data', $_GET);
        if (!empty($data))
        {
            $x = explode(',', $data);

            /* Ensure that each element in the data array is numeric and that
             * at least 4 elements exist.
             */
            for ($i = 0; $i < 4; ++$i)
            {
                if (!isset($x[$i]) || !ctype_digit((string) $x[$i]))
                {
                    $x[$i] = 0;
                }
            }

            /* Ensure that there are only 4 elements in the array. */
            $x = array_slice($x, 0, 4);
        }
        else
        {
            $x = array(0, 0, 0, 0);
        }

        $y = array('Screened', 'Submitted', 'Interviewed', 'Placed');

        $colorArray[] = new LinearGradient(new Red, new White, 0);
        $colorArray[] = new LinearGradient(new DarkGreen, new White, 0);
        $colorArray[] = new LinearGradient(new DarkBlue, new White, 0);
        $colorArray[] = new LinearGradient(new Orange, new White, 0);
        $graph = new jobOrderReportGraph($y, $x, $colorArray, '', 800, 800);

        if (!eval(Hooks::get('GRAPH_JOB_ORDER_REPORT'))) return;

        $graph->draw(IMG_JPG);
        die();
    }

    private function activity()
    {
        /* Grab an instance of Statistics. */
        $statistics = new Statistics($this->_siteID);
        $RS = $statistics->getActivitiesByPeriod(TIME_PERIOD_LASTTWOWEEKS);

        // FIXME: Factor out these calculations? Common to most of these graphs.
        $firstDay = mktime(
            0,
            0,
            0,
            DateUtility::getAdjustedDate('m'),
            DateUtility::getAdjustedDate('d') - DateUtility::getAdjustedDate('w') - 7,
            DateUtility::getAdjustedDate('Y')
        );

        /* Get Labels. */
        $y = array();

        for ($i = 0; $i < 14; $i++)
        {
            $thisDay = mktime(
                0,
                0,
                0,
                date('m', $firstDay),
                date('d', $firstDay) + $i,
                date('Y', $firstDay)
            );
            $y[] = date('d', $thisDay);
        }

        /* Get Values. */
        $x = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

        foreach ($RS as $lineRS)
        {
            $thisDay = mktime(0, 0, 0, $lineRS['month'], $lineRS['day'], $lineRS['year']);
            $dayOfWeek = (int) date('w', $thisDay);
            if (DateUtility::getWeekNumber($thisDay) != DateUtility::getWeekNumber())
            {
                $x[$dayOfWeek]++;
            }
            else
            {
                $x[$dayOfWeek + 7]++;
            }
        }

        $graph = new GraphSimple($y, $x, 'DarkGreen', 'Activity', $this->width, $this->height);

        if (!eval(Hooks::get('GRAPH_WEEKLY_ACTIVITY'))) return;

        $graph->draw();
        die();
    }

    private function newCandidates()
    {
        /* Grab an instance of Statistics. */
        $statistics = new Statistics($this->_siteID);
        $RS = $statistics->getCandidatesByPeriod(TIME_PERIOD_LASTTWOWEEKS);

        // FIXME: Factor out these calculations? Common to most of these graphs.
        $firstDay = mktime(
            0,
            0,
            0,
            DateUtility::getAdjustedDate('m'),
            DateUtility::getAdjustedDate('d') - DateUtility::getAdjustedDate('w') - 7,
            DateUtility::getAdjustedDate('Y')
        );

        /* Get labels. */
        $y = array();
        for ($i = 0; $i < 14; $i++)
        {
            $thisDay = mktime(
                0,
                0,
                0,
                date('m', $firstDay),
                date('d', $firstDay) + $i,
                date('Y', $firstDay)
            );
            $y[] = date('d', $thisDay);
        }

        /* Get values. */
        $x = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        foreach ($RS as $lineRS)
        {
            $thisDay = mktime(0, 0, 0, $lineRS['month'], $lineRS['day'], $lineRS['year']);
            $dayOfWeek = (int) date('w', $thisDay);
            if (DateUtility::getWeekNumber($thisDay) != DateUtility::getWeekNumber())
            {
                $x[$dayOfWeek]++;
            }
            else
            {
                $x[$dayOfWeek + 7]++;
            }
        }

        $graph = new GraphSimple($y, $x, 'Blue', 'New Candidates', $this->width, $this->height);

        if (!eval(Hooks::get('GRAPH_NEW_CANDIDATES'))) return;

        $graph->draw();
        die();
    }

    private function newJobOrders()
    {
        /* Grab an instance of Statistics. */
        $statistics = new Statistics($this->_siteID);
        $RS = $statistics->getJobOrdersByPeriod(TIME_PERIOD_LASTTWOWEEKS);

        // FIXME: Factor out these calculations? Common to most of these graphs.
        $firstDay = mktime(
            0,
            0,
            0,
            DateUtility::getAdjustedDate('m'),
            DateUtility::getAdjustedDate('d') - DateUtility::getAdjustedDate('w') - 7,
            DateUtility::getAdjustedDate('Y')
        );

        $y = array();
        for ($i = 0; $i < 14; $i++)
        {
            $thisDay = mktime(
                0,
                0,
                0,
                date('m', $firstDay),
                date('d', $firstDay) + $i,
                date('Y', $firstDay)
            );
            $y[] = date('d', $thisDay);
        }

        /* Get values. */
        $x = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        foreach ($RS as $lineRS)
        {
            $thisDay = mktime(0, 0, 0, $lineRS['month'], $lineRS['day'], $lineRS['year']);
            $dayOfWeek = (int) date('w', $thisDay);
            if (DateUtility::getWeekNumber($thisDay) != DateUtility::getWeekNumber())
            {
                $x[$dayOfWeek]++;
            }
            else
            {
                $x[$dayOfWeek + 7]++;
            }
        }

        $graph = new GraphSimple($y, $x, 'Red', 'New Job Orders', $this->width, $this->height);

        if (!eval(Hooks::get('GRAPH_NEW_JOB_ORDERS'))) return;

        $graph->draw();
        die();
    }

    private function generic()
    {
        /* Get passed data. */
        $labels = explode(',', $this->getTrimmedInput('labels', $_GET));
        $data = explode(',', $this->getTrimmedInput('data', $_GET));

        foreach ($labels as $index => $line)
        {
            if ($index % 2 == 1)
            {
                $labels[$index] = '||' . $line;
            }
        }

/*        $colorArray = array();
        $colorArray[] = new LinearGradient(new Red, new White, 0);
        $colorArray[] = new LinearGradient(new Green, new White, 0);
        $colorArray[] = new LinearGradient(new Orange, new White, 0);
        $colorArray[] = new LinearGradient(new Blue, new White, 0);
        $colorArray[] = new LinearGradient(new Purple, new White, 0); */

        $title = $this->getTrimmedInput('title', $_GET);

        $graph = new GraphSimple($labels, $data, "DarkGreen", $title, $this->width, $this->height);

        if (!eval(Hooks::get('GRAPH_GENERIC'))) return;

        $graph->draw();
        die();
    }

    private function genericPie()
    {
        /* Get passed data. */
        $labels = explode(',', $this->getTrimmedInput('labels', $_GET));
        $data = explode(',', $this->getTrimmedInput('data', $_GET));

        $title = $this->getTrimmedInput('title', $_GET);

        $graph = new GraphPie($labels, $data, $title, $this->width, $this->height);

        if (!eval(Hooks::get('GRAPH_GENERIC_PIE'))) return;

        $graph->draw();
        die();
    }

    //TODO: Document me.
    private function miniPlacementStatistics()
    {
        if (isset($_GET['view']))
        {
            $view = (int)$_GET['view'];
        }
        else
        {
            $view = DASHBOARD_GRAPH_WEEKLY;
        }
        
        $dashboard = new Dashboard($this->_siteID);
        $pipelineRS = $dashboard->getPipelineData($view);
        
        $noData = true;
        
        $y = array();
        $x = array();
        foreach ($pipelineRS as $index => $data)
        {
            /* Positioning hack */
            $y[] = str_repeat(" ", 13) . $data['label'];
            $y[] = "";
            $y[] = "";
            $y[] = "";
            
            $x[] = $data['submitted'];
            $x[] = $data['interviewing'];
            $x[] = $data['placed'];
            $x[] = 0;
            
            if ($data['submitted'] != 0 || $data['interviewing'] != 0 || $data['placed'] != 0)
            {
                $noData = false;
            }
        }
        
        /* Last column is useless... */
        unset ($x[15]);

        $colorOptions = Graphs::getColorOptions();
        $colorArray = array();

        for ($i = 0; $i < 15; $i+=4)
        {
            $colorArray[] = new LinearGradient(new Color(90, 90, 235), new White, 0);
            $colorArray[] = new LinearGradient(new Orange, new White, 0);
            $colorArray[] = new LinearGradient(new MidGreen, new White, 0);
            $colorArray[] = new LinearGradient(new DarkGreen, new White, 0);
        }
        
        $graph = new pipelineStatisticsGraph(
            $y, $x, $colorArray, $this->width, $this->height, "Submissions", "Interviews", "Hires", $view, $noData
        );
        
        $graph->draw();
        die();
    }
    

    private function miniJobOrderPipeline()
    {
        $statistics = new Statistics($this->_siteID);
        if (!$this->isRequiredIDValid('params', $_GET))
        {
            die();
        }

        $statisticsData = $statistics->getPipelineData($_GET['params']);

        /* We can expand things a bit if we have more room. */
        if ($this->width > 600)
        {
            $y = array(
                "Total Pipeline",
                "Contacted",
                "Cand Replied",
                "Qualifying",
                "Submitted",
                "Interviewing",
                "Offered",
                "Declined",
                "Placed"
            );
        }
        else
        {
            $y = array(
                "Total Pipeline",
                "|Contacted",
                "Cand Replied",
                "|Qualifying",
                "Submitted",
                "|Interviewing",
                "Offered",
                "|Declined",
                "Placed"
            );
        }

        $x[8] = $statisticsData['placed'];
        $x[7] = $statisticsData['passedOn'];
        $x[6] = $statisticsData['offered'] + $x[8];
        $x[5] = $statisticsData['interviewing'] + $x[6];
        $x[4] = $statisticsData['submitted'] + $x[5];
        $x[3] = $statisticsData['qualifying'] + $x[4];
        $x[2] = $statisticsData['replied'] + $x[3];
        $x[1] = $statisticsData['contacted'] + $x[2];
        $x[0] = $statisticsData['totalPipeline'];

        $colorOptions = Graphs::getColorOptions();
        $colorArray = array();

        for ($i = 0; $i < 9; $i++)
        {
            $colorArray[] = new LinearGradient(new DarkGreen, new White, 0);
        }
        $colorArray[4] = new LinearGradient(new Orange, new White, 0);
        $colorArray[7] = new LinearGradient(new AlmostBlack, new White, 0);

        $graph = new GraphComparisonChart(
            $y, $x, $colorArray, 'Job Order Pipeline', $this->width,
            $this->height, $statisticsData['totalPipeline']
        );

        if (!eval(Hooks::get('GRAPH_MINI_PIPELINE'))) return;

        $graph->draw();
        die();
    }

 
    private function newSubmissions()
    {
        /* Grab an instance of Statistics. */
        $statistics = new Statistics($this->_siteID);
        $RS = $statistics->getSubmissionsByPeriod(TIME_PERIOD_LASTTWOWEEKS);

        // FIXME: Factor out these calculations? Common to most of these graphs.
        $firstDay = mktime(
            0,
            0,
            0,
            DateUtility::getAdjustedDate('m'),
            DateUtility::getAdjustedDate('d') - DateUtility::getAdjustedDate('w') - 7,
            DateUtility::getAdjustedDate('Y')
        );

        $y = array();
        for ($i = 0; $i < 14; $i++)
        {
            $thisDay = mktime(
                0,
                0,
                0,
                date('m', $firstDay),
                date('d', $firstDay) + $i,
                date('Y', $firstDay)
            );
            $y[] = date('d', $thisDay);
        }

        /* Get values. */
        $x = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        foreach ($RS as $lineRS)
        {
            $thisDay = mktime(0, 0, 0, $lineRS['month'], $lineRS['day'], $lineRS['year']);
            $dayOfWeek = (int) date('w', $thisDay);
            if (DateUtility::getWeekNumber($thisDay) != DateUtility::getWeekNumber())
            {
                $x[$dayOfWeek]++;
            }
            else
            {
                $x[$dayOfWeek + 7]++;
            }
        }

        $graph = new GraphSimple($y, $x, 'Orange', 'New Submissions', $this->width, $this->height);

        if (!eval(Hooks::get('GRAPH_NEW_SUBMISSIONS'))) return;

        $graph->draw();
        die();
    }

    private function wordVerify()
    {
        if (!$this->isRequiredIDValid('wordVerifyID', $_GET) &&
            !isset($_GET['wordVerifyString']))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid word verification ID.');
        }

        if (isset($_GET['wordVerifyID']))
        {
           $wordVerifyID = $_GET['wordVerifyID'];

           $graphs = new Graphs();
           $text = $graphs->getVerificationImageText($wordVerifyID);
        }
        else
        {
           $text = $_GET['wordVerifyString'];
        }

        $graph = new WordVerify($text);
        $graph->draw();

        die();
    }

    /**
     * Print a fatal error and die. This is overriding UserInterface::fatal().
     * We do this because really, we never want the graphs module to return
     * anything but an image.
     *
     * @param string error message
     * @return void
     */
    protected function fatal($error, $directoryOverride = '')
    {
        // FIXME: Generate an image containing the error message?
        die($error);
    }
}

?>
