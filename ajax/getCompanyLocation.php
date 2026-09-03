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

include_once(__DIR__ . '/bootstrap.php');

include_once(LEGACY_ROOT . '/lib/Companies.php');


$interface = new SecureAJAXInterface();

if (!$interface->isRequiredIDValid('companyID', false))
{
    $interface->outputXMLErrorPage(-1, 'Invalid company ID.');
    die();
}

$companyID = $_REQUEST['companyID'];

/* Get an array of the company's location data. */
$companies = new Companies();
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
    "    <country>" . $locationArray['country'] . "</country>\n" .
    "</data>\n"
);

?>
