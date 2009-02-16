<?php
/*
 * CATS
 * AJAX New List Name Interface
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
 * $Id: newList.php 3198 2007-10-14 23:36:43Z will $
 */


include_once('./lib/StringUtility.php');
include_once('./lib/ActivityEntries.php');
include_once('./lib/SavedLists.php');


$interface = new SecureAJAXInterface();

if (!$interface->isRequiredIDValid('dataItemType'))
{
    $interface->outputXMLErrorPage(-1, 'Invalid saved list type.');
    die();
}

if (!isset($_REQUEST['description']))
{
    $interface->outputXMLErrorPage(-1, 'Invalid name.');
    die();
}

$siteID = $interface->getSiteID();

$savedListName = $_REQUEST['description'];
$dataItemType = $_REQUEST['dataItemType'];

$savedLists = new SavedLists($siteID);

/* Validate the lists - if name is in use or name is blank, fail. */
if ($savedLists->getIDByDescription($savedListName) != -1)
{
    $interface->outputXMLPage(
        "<data>\n" .
        "    <errorcode>0</errorcode>\n" .
        "    <errormessage></errormessage>\n" .
        "    <response>collision</response>\n" .
        "</data>\n"
    );  
    die;  
}

if ($savedListName == '')
{
    $interface->outputXMLPage(
        "<data>\n" .
        "    <errorcode>0</errorcode>\n" .
        "    <errormessage></errormessage>\n" .
        "    <response>badName</response>\n" .
        "</data>\n"
    );  
    die;  
}

/* Write changes. */
$savedLists->newListName($savedListName, $dataItemType);

$interface->outputXMLPage(
    "<data>\n" .
    "    <errorcode>0</errorcode>\n" .
    "    <errormessage></errormessage>\n" .
    "    <response>success</response>\n" .
    "</data>\n"
);

?>
