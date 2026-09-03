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

include_once(dirname(__DIR__, 3) . '/ajax/bootstrap.php');


include_once(LEGACY_ROOT . '/lib/StringUtility.php');
include_once(LEGACY_ROOT . '/lib/ActivityEntries.php');
include_once(LEGACY_ROOT . '/lib/SavedLists.php');


$interface = new SecureAJAXInterface();

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    $interface->outputXMLErrorPage(-1, 'Invalid request.');
    die();
}

if ($_SESSION['CATS']->getAccessLevel('lists') < ACCESS_LEVEL_EDIT)
{
    $interface->outputXMLErrorPage(-1, ERROR_NO_PERMISSION);
    die();
}

if (!isset($_POST['dataItemType']) || !ctype_digit((string) $_POST['dataItemType']))
{
    $interface->outputXMLErrorPage(-1, 'Invalid saved list type.');
    die();
}

if (!isset($_POST['description']))
{
    $interface->outputXMLErrorPage(-1, 'Invalid name.');
    die();
}

$savedListName = $_POST['description'];
$dataItemType = $_POST['dataItemType'];

$savedLists = new SavedLists();

/* Validate the lists - if name is in use or name is blank, fail. */
if ($savedLists->getIDByDescription($savedListName) != -1)
{
    $interface->outputXMLPage(
        "<data>\n" .
        "    <errorcode>0</errorcode>\n" .
        "    <errormessage></errormessage>\n" .
        "    <response>collision</response>\n" .
        "</data>\n"
    );  
    die;  
}

if ($savedListName == '')
{
    $interface->outputXMLPage(
        "<data>\n" .
        "    <errorcode>0</errorcode>\n" .
        "    <errormessage></errormessage>\n" .
        "    <response>badName</response>\n" .
        "</data>\n"
    );  
    die;  
}

/* Write changes. */
$savedLists->newListName($savedListName, $dataItemType);

$interface->outputXMLPage(
    "<data>\n" .
    "    <errorcode>0</errorcode>\n" .
    "    <errormessage></errormessage>\n" .
    "    <response>success</response>\n" .
    "</data>\n"
);

?>
