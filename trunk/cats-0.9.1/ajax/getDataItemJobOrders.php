<?php
/*
 * CATS
 * AJAX Data Item Job Orders Interface
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
 * $Id: getDataItemJobOrders.php 1892 2007-02-20 06:44:04Z will $
 */

$interface = new SecureAJAXInterface();

if (!$interface->isRequiredIDValid('dataItemID'))
{
    $interface->outputXMLErrorPage(-1, 'Invalid data item ID.');
    die();
}

if (!$interface->isRequiredIDValid('dataItemType'))
{
    $interface->outputXMLErrorPage(-1, 'Invalid data item type.');
    die();
}

$siteID = $interface->getSiteID();

$dataItemType = $_REQUEST['dataItemType'];
$dataItemID   = $_REQUEST['dataItemID'];

switch ($dataItemType)
{
    case DATA_ITEM_CANDIDATE:
        include_once('./lib/Candidates.php');
        $dataItem = new Candidates($siteID);
        break;

    case DATA_ITEM_COMPANY:
        include_once('./lib/Companies.php');
        $dataItem = new Companies($siteID);
        break;

    case DATA_ITEM_CONTACT:
        include_once('./lib/Contacts.php');
        $dataItem = new Contacts($siteID);
        break;

    default:
        $interface->outputXMLErrorPage(-1, 'Invalid data item type.');
        die();
        break;
}

$jobOrdersArray = $dataItem->getJobOrdersArray($dataItemID);

if (empty($jobOrdersArray))
{
    $interface->outputXMLErrorPage(-2, 'No job orders data.');
    die();
}

$output =
    "<data>\n" .
    "    <errorcode>0</errorcode>\n" .
    "    <errormessage></errormessage>\n";

foreach ($jobOrdersArray as $rowIndex => $row)
{
    if (!isset($jobOrdersArray[$rowIndex]['isAssigned']))
    {
        $jobOrdersArray[$rowIndex]['isAssigned'] = '0';
    }

    $output .=
        "    <joborder>\n" .
        "        <id>"         . $jobOrdersArray[$rowIndex]['jobOrderID'] . "</id>\n" .
        "        <title>"      . htmlspecialchars($jobOrdersArray[$rowIndex]['title']) . "</title>\n" .
        "        <companyname>" . htmlspecialchars($jobOrdersArray[$rowIndex]['companyName'])  . "</companyname>\n" .
        "        <assigned>"   . htmlspecialchars($jobOrdersArray[$rowIndex]['isAssigned'])  . "</assigned>\n" .
        "    </joborder>\n";
}

$output .=
    "</data>\n";

/* Send back the XML data. */
$interface->outputXMLPage($output);

?>
