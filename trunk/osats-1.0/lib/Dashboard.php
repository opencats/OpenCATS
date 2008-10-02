<?php
/**
 * CATS
 * Dashboard Library
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
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: Dashboard.php 3784 2007-12-03 21:57:10Z brian $
 */

include_once('lib/Calendar.php');

/**
 *	Dashboard Library
 *	@package    CATS
 *	@subpackage Library
 */
class Dashboard 
{

    private $_db;
    private $_siteID;
    
    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }

    /**
     * Returns an array of recent placements to display on the dashboard.
     *
     * @return array recent placements
     */
    public function getPlacements()
    {
        $sql = sprintf(
            "SELECT
                candidate.first_name as firstName,
                candidate.last_name as lastName,
                candidate.candidate_id as candidateID,
                company.name as companyName,
                company.company_id as companyID,
                user.first_name as userFirstName,
                user.last_name as userLastName,
                IF (company.is_hot = 1, 'jobLinkHot', 'jobLinkCold') as companyClassName,
                IF (candidate.is_hot = 1, 'jobLinkHot', 'jobLinkCold') as candidateClassName,
                DATE_FORMAT(
                    candidate_joborder_status_history.date, '%%m-%%d-%%y'
                ) AS date,
                candidate_joborder_status_history.date AS datesort
            FROM
                candidate_joborder_status_history
            LEFT JOIN candidate ON
                candidate.candidate_id = candidate_joborder_status_history.candidate_id
            LEFT JOIN joborder ON
                joborder.joborder_id = candidate_joborder_status_history.joborder_id
            LEFT JOIN company ON
                joborder.company_id = company.company_id
            LEFT JOIN user ON
                joborder.recruiter = user.user_id
            WHERE
                status_to = 800
            AND
                candidate_joborder_status_history.site_id = %s
            ORDER BY 
                datesort DESC
            LIMIT
                10",
            $this->_siteID
        );

        $rs = $this->_db->getAllAssoc($sql);

        return $rs;
    }

    /**
     * Returns an associative array with 4 rows of either the last 4 weeks or 4 months 
     * statistics on submitted, interviewing, and placed candidates.
     *
     * @param integer pipeline view indentifier
     * @return array pipeline graph data
     */
    public function getPipelineData($view)
    {   
        $oneUnixDay = 86400;
        
        $calendarSettings = new CalendarSettings($this->_siteID);
        $calendarSettingsRS = $calendarSettings->getAll();

        if ($calendarSettingsRS['firstDayMonday'] == 1)
        {
            $firstDayMonday = true;
            $firstDayModifierPlus = ' + 1';
            $dateNowForWeeks = 'DATE_SUB(NOW(), INTERVAL 1 DAY)';
            $dateEventForWeeks = 'DATE_SUB(candidate_joborder_status_history.date, INTERVAL 1 DAY)';
        }
        else
        {
            $firstDayMonday = false;
            $firstDayModifierPlus = '';
            $firstDayModifierMinus = '';
            $dateNowForWeeks = 'NOW()';
            $dateEventForWeeks = 'candidate_joborder_status_history.date';
        }        
        
        switch ($view)
        {
            case DASHBOARD_GRAPH_YEARLY:
                $select = 'YEAR(candidate_joborder_status_history.date) as unixdate';
                break;

            case DASHBOARD_GRAPH_MONTHLY:
                $select = 'UNIX_TIMESTAMP(FROM_DAYS(TO_DAYS(candidate_joborder_status_history.date) - DAYOFMONTH(candidate_joborder_status_history.date) + 1)) as unixdate';
                break;
                
            case DASHBOARD_GRAPH_WEEKLY:
		    default:
                $select = 'UNIX_TIMESTAMP(FROM_DAYS(TO_DAYS('.$dateEventForWeeks.') - DAYOFWEEK('.$dateEventForWeeks.') + 1 '.$firstDayModifierPlus.')) as unixdate';
                break;
        }
        
        /* This SQL query either returns 1 row per week or 1 row per month for the total 
         * count of sub, int, and pla status changes in the system.
         */
        
        /* Limit 20 because if time was skewed, there may be events in the future that
         * the PHP function will throw out, but future events will prevent past events
         * from loading properly.  We don't need a limit at all, but limiting 20 results 
         * back should guarantee we will always at least get the relavant rows we want.
         */
        $sql = sprintf(
            "SELECT
                %s,
                SUM(IF(candidate_joborder_status_history.status_to = %s, 1, 0)) AS submitted,
                SUM(IF(candidate_joborder_status_history.status_to = %s, 1, 0)) AS interviewing,
                SUM(IF(candidate_joborder_status_history.status_to = %s, 1, 0)) AS placed
            FROM
                candidate_joborder_status_history
            WHERE
                candidate_joborder_status_history.site_id = %s
            GROUP BY unixdate
            ORDER BY unixdate DESC
            LIMIT 20
            ",
            $select,
            PIPELINE_STATUS_SUBMITTED,
            PIPELINE_STATUS_INTERVIEWING,
            PIPELINE_STATUS_PLACED,
            $this->_siteID
        );
        
        $rs = $this->_db->getAllAssoc($sql);
        
        /* Gets some numbers as to what week and month MySQL thinks it is. */ 
        $sql = sprintf(
            "SELECT 
                YEAR(NOW()) as currentYearNumber,
                UNIX_TIMESTAMP(FROM_DAYS(TO_DAYS(%s) - DAYOFWEEK(%s) + 1 %s)) as currentWeekNumber,
                UNIX_TIMESTAMP(FROM_DAYS(TO_DAYS(DATE_SUB(%s, INTERVAL 7 DAY)) - DAYOFWEEK(DATE_SUB(%s, INTERVAL 7 DAY)) + 1 %s)) as oneWeekAgoNumber,
                UNIX_TIMESTAMP(FROM_DAYS(TO_DAYS(DATE_SUB(%s, INTERVAL 14 DAY)) - DAYOFWEEK(DATE_SUB(%s, INTERVAL 14 DAY)) + 1 %s)) as twoWeekAgoNumber,
                UNIX_TIMESTAMP(FROM_DAYS(TO_DAYS(DATE_SUB(%s, INTERVAL 21 DAY)) - DAYOFWEEK(DATE_SUB(%s, INTERVAL 21 DAY)) + 1 %s)) as threeWeekAgoNumber,
                UNIX_TIMESTAMP(FROM_DAYS(TO_DAYS(NOW()) - DAYOFMONTH(NOW()) + 1)) as currentMonthNumber,
                UNIX_TIMESTAMP(FROM_DAYS(TO_DAYS(DATE_SUB(NOW(), INTERVAL 1 MONTH)) - DAYOFMONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) + 1)) as oneMonthAgoNumber,
                UNIX_TIMESTAMP(FROM_DAYS(TO_DAYS(DATE_SUB(NOW(), INTERVAL 2 MONTH)) - DAYOFMONTH(DATE_SUB(NOW(), INTERVAL 2 MONTH)) + 1)) as twoMonthAgoNumber,
                UNIX_TIMESTAMP(FROM_DAYS(TO_DAYS(DATE_SUB(NOW(), INTERVAL 3 MONTH)) - DAYOFMONTH(DATE_SUB(NOW(), INTERVAL 3 MONTH)) + 1)) as threeMonthAgoNumber,
                MONTHNAME(NOW()) as currentMonthName,
                MONTHNAME(DATE_SUB(NOW(), INTERVAL 1 MONTH)) as oneMonthAgoName,
                MONTHNAME(DATE_SUB(NOW(), INTERVAL 2 MONTH)) as twoMonthAgoName,
                MONTHNAME(DATE_SUB(NOW(), INTERVAL 3 MONTH)) as threeMonthAgoName
            ",
            $dateNowForWeeks,
            $dateNowForWeeks,
            $firstDayModifierPlus,
            $dateNowForWeeks,
            $dateNowForWeeks,
            $firstDayModifierPlus,
            $dateNowForWeeks,
            $dateNowForWeeks,
            $firstDayModifierPlus,
            $dateNowForWeeks,
            $dateNowForWeeks,
            $firstDayModifierPlus
        );
        
        $rsCurrentTime = $this->_db->getAssoc($sql);
        
        $data = array();
        
        switch ($view)
        {
            case DASHBOARD_GRAPH_YEARLY:
                $data[$rsCurrentTime['currentYearNumber']] = array('label' => $rsCurrentTime['currentYearNumber']);
                $data[$rsCurrentTime['currentYearNumber'] - 1] = array('label' => $rsCurrentTime['currentYearNumber'] - 1);
                $data[$rsCurrentTime['currentYearNumber'] - 2] = array('label' => $rsCurrentTime['currentYearNumber'] - 2);
                $data[$rsCurrentTime['currentYearNumber'] - 3] = array('label' => $rsCurrentTime['currentYearNumber'] - 3);
                break;
            
            case DASHBOARD_GRAPH_MONTHLY:
                $data[$rsCurrentTime['currentMonthNumber']] = array('label' => $rsCurrentTime['currentMonthName']);
                $data[$rsCurrentTime['oneMonthAgoNumber']] = array('label' => $rsCurrentTime['oneMonthAgoName']);
                $data[$rsCurrentTime['twoMonthAgoNumber']] = array('label' => $rsCurrentTime['twoMonthAgoName']);
                $data[$rsCurrentTime['threeMonthAgoNumber']] = array('label' => $rsCurrentTime['threeMonthAgoName']);
                break;
                
            case DASHBOARD_GRAPH_WEEKLY:
		    default:
              // TODO:   Localization d/m, week starts on monday
                if ($_SESSION['CATS']->isDateDMY())
                {
                    $pattern = "d/m";
                }
                else
                {
                    $pattern = "m/d";
                }            
            
                /* * 6 at the end gives us the last day in the week (first day in week plus 6 days) */
                $data[$rsCurrentTime['currentWeekNumber']] = array('label' => date($pattern, $rsCurrentTime['currentWeekNumber']) . ' - ' . date($pattern, $rsCurrentTime['currentWeekNumber'] + $oneUnixDay * 6));
                $data[$rsCurrentTime['oneWeekAgoNumber']] = array('label' => date($pattern, $rsCurrentTime['oneWeekAgoNumber']) . ' - ' . date($pattern, $rsCurrentTime['oneWeekAgoNumber'] + $oneUnixDay * 6));
                $data[$rsCurrentTime['twoWeekAgoNumber']] = array('label' => date($pattern, $rsCurrentTime['twoWeekAgoNumber']) . ' - ' . date($pattern, $rsCurrentTime['twoWeekAgoNumber'] + $oneUnixDay * 6));
                $data[$rsCurrentTime['threeWeekAgoNumber']] = array('label' => date($pattern, $rsCurrentTime['threeWeekAgoNumber']) . ' - ' . date($pattern, $rsCurrentTime['threeWeekAgoNumber'] + $oneUnixDay * 6));
                break;
        }  
        
        /* Fill the array with data. */
        foreach ($data as $indexData => $rowData)
        {
            $data[$indexData]['submitted'] = 0;
            $data[$indexData]['interviewing'] = 0;
            $data[$indexData]['placed'] = 0;
            
            foreach ($rs as $indexRS => $rowRS)
            {
                if ($rowRS['unixdate'] == $indexData)
                {
                    $data[$indexData]['submitted'] = $rowRS['submitted'];
                    $data[$indexData]['interviewing'] = $rowRS['interviewing'];
                    $data[$indexData]['placed'] = $rowRS['placed'];
                }
            }
        }
        
        ksort($data, SORT_NUMERIC);
        
        return $data;
    }
}
    
?>