<?php
/*
 * CATS
 * AJAX Company Name Search Interface
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
 * $Id: getCompanyNames.php 2367 2007-04-23 23:24:05Z will $
 */

include_once('./lib/Companies.php');
include_once('./lib/Search.php');


$interface = new SecureAJAXInterface();

if (!isset($_REQUEST['dataName']))
{
    $interface->outputXMLErrorPage(-1, 'Invalid data name.');
    die();
}

if (!$interface->isRequiredIDValid('maxResults'))
{
    $interface->outputXMLErrorPage(-1, 'Invalid max results count.');
    die();
}

$siteID = $interface->getSiteID();

$dataName   = trim($_REQUEST['dataName']);
$maxResults = $_REQUEST['maxResults'];

$search = new SearchCompanies($siteID);
$companiesArray = $search->byName($dataName, 'company.name', 'ASC');

if (empty($companiesArray))
{
    $interface->outputXMLErrorPage(-2, 'No companies data.');
    die();
}

$output =
    "<data>\n" .
    "    <errorcode>0</errorcode>\n" .
    "    <errormessage></errormessage>\n" .
    "    <totalelements>" . count($companiesArray) . "</totalelements>\n";

$arrayCounter = 0;
foreach ($companiesArray as $rowIndex => $row)
{
    $arrayCounter++;

    if ($arrayCounter > $maxResults)
    {
        break;
    }

    $output .=
        "    <result>\n" .
        "        <id>"   . $companiesArray[$rowIndex]['companyID'] . "</id>\n" .
        "        <name>" . rawurlencode($companiesArray[$rowIndex]['name']) . "</name>\n" .
        "    </result>\n";
}

$output .=
    "</data>\n";

/* Send back the XML data. */
$interface->outputXMLPage($output);

?>
