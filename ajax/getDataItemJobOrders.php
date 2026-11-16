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

$interface = new SecureAJAXInterface();

if (is_array($_REQUEST['dataItemID'] ?? null) || !$interface->isRequiredIDValid('dataItemID', false))
{
    $interface->outputXMLErrorPage(-1, 'Invalid data item ID.');
    die();
}

if (is_array($_REQUEST['dataItemType'] ?? null) || !$interface->isRequiredIDValid('dataItemType', false))
{
    $interface->outputXMLErrorPage(-1, 'Invalid data item type.');
    die();
}

$dataItemType = $_REQUEST['dataItemType'];
$dataItemID   = $_REQUEST['dataItemID'];

switch ($dataItemType)
{
    case DATA_ITEM_CANDIDATE:
        include_once(LEGACY_ROOT . '/lib/CandidateAuthorization.php');
        $parentPermission = 'candidates.show';
        $dataItemClass = Candidates::class;
        break;

    case DATA_ITEM_COMPANY:
        include_once(LEGACY_ROOT . '/lib/Companies.php');
        $parentPermission = 'companies.show';
        $dataItemClass = Companies::class;
        break;

    case DATA_ITEM_CONTACT:
        include_once(LEGACY_ROOT . '/lib/Contacts.php');
        $parentPermission = 'contacts.show';
        $dataItemClass = Contacts::class;
        break;

    default:
        $interface->outputXMLErrorPage(-1, 'Invalid data item type.');
        die();
        break;
}

$session = $_SESSION['CATS'];
if ($session->getAccessLevel($parentPermission) < ACCESS_LEVEL_READ ||
    $session->getAccessLevel('joborders.show') < ACCESS_LEVEL_READ ||
    ($dataItemClass === Candidates::class && !CandidateAuthorization::canAccessCandidate($dataItemID)))
{
    $interface->outputXMLErrorPage(-2, 'No job orders data.');
    die();
}

$dataItem = new $dataItemClass();
$jobOrdersArray = $dataItem->getJobOrdersArray(
    $dataItemID, $session->getAccessLevel('joborders.hidden') >= ACCESS_LEVEL_SA
);

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
        "        <title>"      . htmlspecialchars($jobOrdersArray[$rowIndex]['title'], ENT_COMPAT, AJAX_ENCODING) . "</title>\n" .
        "        <companyname>" . htmlspecialchars($jobOrdersArray[$rowIndex]['companyName'], ENT_COMPAT, AJAX_ENCODING)  . "</companyname>\n" .
        "        <assigned>"   . htmlspecialchars($jobOrdersArray[$rowIndex]['isAssigned'], ENT_COMPAT, AJAX_ENCODING)  . "</assigned>\n" .
        "    </joborder>\n";
}

$output .=
    "</data>\n";

/* Send back the XML data. */
$interface->outputXMLPage($output);

?>
