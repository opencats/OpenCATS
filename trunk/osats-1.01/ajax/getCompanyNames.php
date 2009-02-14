<?php
/*
   * OSATS
   *
   *
   * Open Source GNU License will apply
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
