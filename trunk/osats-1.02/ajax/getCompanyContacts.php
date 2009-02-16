<?php
/*
   * OSATS
   *
   *
   * Open Source GNU License will apply
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
