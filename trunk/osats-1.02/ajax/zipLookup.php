<?php
/*
   * OSATS
   *
   *
   * Open Source GNU License will apply
*/

include_once('./lib/ZipLookup.php');
include_once('./lib/StringUtility.php');

$interface = new AJAXInterface();

if (!isset($_REQUEST['zip']))
{
    $interface->outputXMLErrorPage(-1, 'Invalid zip code.');
    die();
}

$zip = $_REQUEST['zip'];

$zipLookup = new ZipLookup();
$searchableZip = $zipLookup->makeSearchableUSZip($zip);

$data = $zipLookup->getCityStateByZip($searchableZip);

$city  = $data['city'];
$state = $data['state'];

/* Send back the XML data. */
$interface->outputXMLPage(
    "<data>\n" .
    "    <errorcode>0</errorcode>\n" .
    "    <errormessage></errormessage>\n" .
    "    <city>"   . $city  . "</city>\n" .
    "    <state>"  . $state . "</state>\n" .
    "</data>\n"
);
