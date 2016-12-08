<?php
/*
 * OpenCATS
 * AJAX Street/City/State lookup via Zip Interface
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

$street = $data[1];
$city  = $data[2];
$state = $data[3];

/* Send back the XML data. */
$interface->outputXMLPage(
    "<data>\n" .
    "    <errorcode>0</errorcode>\n" .
    "    <errormessage></errormessage>\n" .
    "    <address>" . $street. "</address>\n" .
    "    <city>"    . $city  . "</city>\n" .
    "    <state>"   . $state . "</state>\n" .
    "</data>\n"
);
?>
