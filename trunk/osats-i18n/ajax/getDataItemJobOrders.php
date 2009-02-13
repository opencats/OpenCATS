<?php
/*
   * OSATS
   *
   *
   * Open Source GNU License will apply
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
