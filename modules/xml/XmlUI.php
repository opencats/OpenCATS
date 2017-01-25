<?php
/*
 * CATS
 * XML module
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
 * This module builds an XML file containing public job postings. The
 * exported XML data can be used to submit, en masse, all public job
 * postings to job bulletin sites such as Indeed.com.
 *
 *
 * $Id: XmlUI.php 3565 2007-11-12 09:09:22Z will $
 */

include_once('./lib/ActivityEntries.php');
include_once('./lib/StringUtility.php');
include_once('./lib/DateUtility.php');
include_once('./lib/JobOrders.php');
include_once('./lib/Site.php');
include_once('./lib/XmlJobExport.php');
include_once('./lib/HttpLogger.php');
include_once('./lib/CareerPortal.php');

define('XTPL_HEADER_STRING',    'header');
define('XTPL_FOOTER_STRING',    'footer');
define('XTPL_JOB_STRING',       'job');

class XmlUI extends UserInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = false;
        $this->_moduleDirectory = 'xml';
        $this->_moduleName = 'xml';
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

    private function outputXMLError($title, $errorMessage)
    {
        /* XML Headers */
        header('Content-type: text/xml');

        $link = CATSUtility::getAbsoluteURI('../careers/');
        echo sprintf(
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"
            . "<rss version=\"2.0\">\n"
            . "<channel>\n"
            . "<title>%s</title>\n"
            . "<description>CATS XML Output</description>\n"
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

        // Log that this file was accessed
        // FIXME: Does this really need to involve two queries? Can we store
        //        the IDs in constants too?
        HTTPLogger::addHTTPLog(
            HTTPLogger::getHTTPLogTypeIDByName('xml'),
            $careerPortalSiteID
        );

        /* XML Headers */
        header('Content-type: text/xml');

        $indexName = CATSUtility::getIndexName();

        $availTemplates = XmlTemplate::getTemplates();

        if (isset($_GET['t']))
        {
            $templateName = $_GET['t'];
            // Check if the template exists
            foreach ($availTemplates as $template)
            {
                if (!strcasecmp($template['xml_template_name'], $templateName))
                {
                    $templateSections = XmlTemplate::loadTemplate($templateName);
                }
            }
        }

        // no template exists, load the default (which will always be first)
        if (!isset($templateSections))
        {
            $templateSections = XmlTemplate::loadTemplate(
                $templateName = $availTemplates[0]["xml_template_name"]
            );
        }

        // get the section bodies from the template into strings
        $templateHeader = $templateSections[XTPL_HEADER_STRING];
        $templateJob = $templateSections[XTPL_JOB_STRING];
        $templateFooter = $templateSections[XTPL_FOOTER_STRING];

        $tags = XmlTemplate::loadTemplateTags($templateHeader);
        foreach ($tags as $tag)
        {
            switch ($tag)
            {
                case 'date':
                    $templateHeader = XmlTemplate::replaceTemplateTags(
                        $tag,
                        DateUtility::getRSSDate(),
                        $templateHeader
                    );
                    break;

                case 'siteURL':
                    $templateHeader = XmlTemplate::replaceTemplateTags(
                        $tag,
                        CATSUtility::getAbsoluteURI(''),
                        $templateHeader
                    );
                    break;
            }
        }
        $stream = $templateHeader;

        $tags = XmlTemplate::loadTemplateTags($templateJob);

        $careerPortalSettings = new CareerPortalSettings($careerPortalSiteID);
        $settings = $careerPortalSettings->getAll();

        $url = CATSUtility::getAbsoluteURI();
        if(strrpos($url, 'xml') == (strlen($url) - 4))
        {
            $url = substr($url, 0, -4);
        }

        if ($settings['allowBrowse'] == 1)
        {
            // browse the jobs, adding a section body for each job
            foreach ($rs as $rowIndex => $row)
            {
                $txtJobPosting = $templateJob;

                foreach ($tags as $tag)
                {
                    switch ($tag)
                    {
                        case 'siteURL':
                            $txtJobPosting = XmlTemplate::replaceTemplateTags(
                                $tag,
                                $url,
                                $txtJobPosting
                        );
                        break;

                        case 'jobTitle':
                            $txtJobPosting = XmlTemplate::replaceTemplateTags(
                                $tag,
                                $row['title'],
                                $txtJobPosting
                            );
                            break;

                        case 'jobPostDate':
                            $txtJobPosting = XmlTemplate::replaceTemplateTags(
                                $tag,
                                $row['dateCreatedSort'],
                                $txtJobPosting
                            );
                            break;

                        case 'jobURL':
                            $uri = sprintf("%scareers/?p=showJob&ID=%d&ref=%s",
                                $url,
                                $row['jobOrderID'],
                                $templateName
                            );

                            $txtJobPosting = XmlTemplate::replaceTemplateTags(
                                $tag,
                                $uri,
                                $txtJobPosting
                            );
                            break;

                        case 'jobOrderID':
                            $txtJobPosting = XmlTemplate::replaceTemplateTags(
                                $tag,
                                $row['jobOrderID'],
                                $txtJobPosting
                            );
                            break;
                            
                        case 'jobID':
                            $txtJobPosting = XmlTemplate::replaceTemplateTags(
                                $tag,
                                $row['jobID'],
                                $txtJobPosting
                            );
                            break;

                        case 'hiringCompany':
                            $txtJobPosting = XmlTemplate::replaceTemplateTags(
                                $tag,
                                'CATS (www.catsone.com)',
                                $txtJobPosting
                            );
                            break;

                        case 'jobCity':
                            $txtJobPosting = XmlTemplate::replaceTemplateTags(
                                $tag,
                                $row['city'],
                                $txtJobPosting
                            );
                            break;

                        case 'jobState':
                            $txtJobPosting = XmlTemplate::replaceTemplateTags(
                                $tag,
                                $row['state'],
                                $txtJobPosting
                            );
                            break;

                        // FIXME: Make this expandable to non-US?
                        case 'jobCountry':
                            $txtJobPosting = XmlTemplate::replaceTemplateTags(
                                $tag,
                                "US",
                                $txtJobPosting
                            );
                            break;

                        case 'jobZipCode':
                            $txtJobPosting = XmlTemplate::replaceTemplateTags(
                                $tag,
                                '',
                                $txtJobPosting
                            );
                            break;

                        case 'jobDescription':
                            $txtJobPosting = XmlTemplate::replaceTemplateTags(
                                $tag,
                                $row['jobDescription'],
                                $txtJobPosting
                            );
                            break;
                            
                        case 'notes':
                            $txtJobPosting = XmlTemplate::replaceTemplateTags(
                                $tag,
                                $row['notes'],
                                $txtJobPosting
                            );
                            break;
                        case 'type':
                            $txtJobPosting = XmlTemplate::replaceTemplateTags(
                                $tag,
                                $row['type'],
                                $txtJobPosting
                            );
                            break;
                    }
                }

                $stream .= $txtJobPosting;
            }
        }

        $stream .= $templateFooter;

        echo $stream;
    }
}

?>
