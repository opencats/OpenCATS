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

include_once(LEGACY_ROOT . '/lib/ActivityEntries.php');
include_once(LEGACY_ROOT . '/lib/StringUtility.php');
include_once(LEGACY_ROOT . '/lib/DateUtility.php');
include_once(LEGACY_ROOT . '/lib/JobOrders.php');

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
        $jobOrders = new JobOrders();
        $rs = $jobOrders->getAll(JOBORDERS_STATUS_SHARE, -1, -1, -1, false, true);

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
