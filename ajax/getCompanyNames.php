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
include_once(LEGACY_ROOT . '/lib/Search.php');


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

$dataName   = trim($_REQUEST['dataName']);
$maxResults = $_REQUEST['maxResults'];

$search = new SearchCompanies();
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
