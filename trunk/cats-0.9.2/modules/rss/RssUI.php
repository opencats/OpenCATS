<?php
/*
 * CATS
 * RSS Feed module
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
 * This example module is meant as a very BASIC guide to the CATS module API.
 * It does not demonstrate every feature, but it should help you get started.
 * This example is also intended for programmers with a working knowledge of
 * PHP, so only aspects of the code specific to the CATS module API are
 * explained.
 *
 *
 * $Id: RssUI.php 3687 2007-11-26 16:36:01Z andrew $
 */

include_once('./lib/ActivityEntries.php');
include_once('./lib/StringUtility.php');
include_once('./lib/DateUtility.php');
include_once('./lib/JobOrders.php');
include_once('./lib/Site.php');

class RssUI extends UserInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = false;
        $this->_moduleDirectory = 'rss';
        $this->_moduleName = 'rss';
        $this->_moduleTabText = '';
        $this->_subTabs = array();
    }


    public function handleRequest()
    {
        $action = $this->getAction();
        switch ($action)
        {
            case 'jobOrders':
            default:
                $this->displayPublicJobOrders();
                break;
        }
    }

    private function outputRSSError($title, $errorMessage)
    {
        /* XML Headers */
        header('Content-type: text/xml');

        $link = CATSUtility::getAbsoluteURI('../careers/');

        echo sprintf(
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"
            . "<rss version=\"2.0\">\n"
            . "<channel>\n"
            . "<title>%s</title>\n"
            . "<description>CATS RSS Feed</description>\n"
            . "<link>%s</link>\n"
            . "<pubDate>%s</pubDate>\n\n"
            . "<item>\n"
            . "<title>Error</title>\n"
            . "<description>%s</description>\n"
            . "<link>%s</link>\n"
            . "</item>\n"
            . "</channel>\n"
            . "</rss>\n",
            $title,
            $link,
            DateUtility::getRSSDate(),
            $errorMessage,
            $link
        );
    }

    private function displayPublicJobOrders()
    {
        $site = new Site(-1);

        $careerPortalSiteID = $site->getFirstSiteID();

        if (!eval(Hooks::get('RSS_SITEID'))) return;

        $jobOrders = new JobOrders($careerPortalSiteID);
        $rs = $jobOrders->getAll(JOBORDERS_STATUS_ACTIVE, -1, -1, -1, false, true);

        /* XML Headers */
        header('Content-type: text/xml');

        $indexName = CATSUtility::getIndexName();

        $stream = sprintf(
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"
            . "<rss version=\"2.0\">\n"
            . "<channel>\n"
            . "<title>New Job Orders</title>\n"
            . "<description>CATS RSS Feed</description>\n"
            . "<link>%s</link>\n"
            . "<pubDate>%s</pubDate>\n",
            CATSUtility::getAbsoluteURI(),
            DateUtility::getRSSDate()
        );

        foreach ($rs as $rowIndex => $row)
        {
            $uri = sprintf("%scareers/?p=showJob&amp;ID=%d",
                CATSUtility::getAbsoluteURI(),
                $row['jobOrderID']
            );

            // Fix URL if viewing from /rss without using globals or dirup '../'
            if (strpos($_SERVER['PHP_SELF'], '/rss/') !== false)
            {
                $uri = str_replace('/rss/', '/', $uri);
            }

            $stream .= sprintf(
                "<item>\n" .
                "<title>%s (%s)</title>\n" .
                "<description>Located in %s.</description>\n" .
                "<link>%s</link>\n" .
                "</item>\n",
                $row['title'],
                $jobOrders->typeCodeToString($row['type']),
                StringUtility::makeCityStateString($row['city'], $row['state']),
                $uri
            );
        }

        $stream .= "</channel>\n</rss>\n";

        echo $stream;
    }
}

?>
