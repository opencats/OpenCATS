<?php
/*
 * CATS
 * AJAX Company Contacts Interface
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
 * $Id: getCompanyContacts.php 1892 2007-02-20 06:44:04Z will $
 */

include_once('./lib/Companies.php');


$interface = new SecureAJAXInterface();

if (!$interface->isRequiredIDValid('companyID', false))
{
    $interface->outputXMLErrorPage(-1, 'Invalid company ID.');
    die();
}

$siteID = $interface->getSiteID();

$companyID = $_REQUEST['companyID'];

/* Get an array of the company's contacts data. */
$companies = new Companies($siteID);
$contactsArray = $companies->getContactsArray($companyID);

if (empty($contactsArray))
{
    $interface->outputXMLErrorPage(-2, 'No contacts data.');
    die();
}

$output =
    "<data>\n" .
    "    <errorcode>0</errorcode>\n" .
    "    <errormessage></errormessage>\n";

foreach ($contactsArray as $rowIndex => $row)
{
    $output .=
        "    <contact>\n" .
        "        <id>"        . $contactsArray[$rowIndex]['contactID'] . "</id>\n" .
        "        <firstname>" . $contactsArray[$rowIndex]['firstName'] . "</firstname>\n" .
        "        <lastname>"  . $contactsArray[$rowIndex]['lastName']  . "</lastname>\n" .
        "    </contact>\n";
}

$output .=
    "</data>\n";

/* Send back the XML data. */
$interface->outputXMLPage($output);

?>
