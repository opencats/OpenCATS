<?php
/*
 * CATS
 * AJAX Company Location Interface
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
 * $Id: getCompanyLocation.php 2359 2007-04-21 22:49:17Z will $
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

/* Get an array of the company's location data. */
$companies = new Companies($siteID);
$locationArray = $companies->getLocationArray($companyID);

if (empty($locationArray))
{
    $interface->outputXMLErrorPage(-2, 'No location data.');
    die();
}

/* Send back the XML data. */
$interface->outputXMLPage(
    "<data>\n" .
    "    <errorcode>0</errorcode>\n" .
    "    <errormessage></errormessage>\n" .
    "    <address>" . $locationArray['address'] . "</address>\n" .
    "    <city>"    . $locationArray['city'] . "</city>\n" .
    "    <state>"   . $locationArray['state'] . "</state>\n" .
    "    <zip>"     . $locationArray['zip'] . "</zip>\n" .
    "</data>\n"
);

?>
