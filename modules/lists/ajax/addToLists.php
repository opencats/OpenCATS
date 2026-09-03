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
include_once(LEGACY_ROOT . '/lib/CandidateAuthorization.php');

function isRequiredValueValid($value)
{
    $value = (string) $value;

    /* Return false if the key is empty, or if the key is zero and
     * zero-values are not allowed.
     */
    if (empty($value) && ($value !== '0' || !$allowZero))
    {
        return false;
    }

    /* -0 should not be allowed. */
    if ($value === '-0')
    {
        return false;
    }

    /* Only allow digits. */
    if (!ctype_digit($value))
    {
        return false;
    }

    return true;
}



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

if (!isset($_POST['listsToAdd']))
{
    $interface->outputXMLErrorPage(-1, 'No listsToAdd passed.');
    die();
}

if (!isset($_POST['itemsToAdd']))
{
    $interface->outputXMLErrorPage(-1, 'No itemsToAdd passed.');
    die();
}

if (!isset($_POST['dataItemType']) || !ctype_digit((string) $_POST['dataItemType']))
{
    $interface->outputXMLErrorPage(-1, 'Invalid saved list type.');
    die();
}

$listsToAdd = explode(',', $_POST['listsToAdd']);
$itemsToAdd = explode(',', $_POST['itemsToAdd']);
$dataItemType = $_POST['dataItemType'];

foreach ($listsToAdd as $index => $data)
{
    if ($data == '')
    {
        unset($listsToAdd[$index]);
    }
    else
    {
        if (isRequiredValueValid($data) == false)
        {
            $interface->outputXMLErrorPage(-1, 'Invalid lists value. ('.$data.')');
            die;
        }
    }
}

foreach ($itemsToAdd as $index => $data)
{
    if ($data == '')
    {
        unset($itemsToAdd[$index]);
    }
    else
    {
        if (isRequiredValueValid($data) == false)
        {
            $interface->outputXMLErrorPage(-1, 'Invalid items value.');
            die;
        }
    }
}

if ((int) $dataItemType === DATA_ITEM_CANDIDATE)
{
    foreach ($itemsToAdd as $item)
    {
        $candidate = null;
        if (!CandidateAuthorization::canAccessCandidate($item, $candidate))
        {
            $interface->outputXMLErrorPage(-1, ERROR_NO_PERMISSION);
            die();
        }
    }
}

$savedLists = new SavedLists();

/* Write changes. */
foreach ($listsToAdd as $list)
{
    $itemsToAddTemp = array();
    foreach ($itemsToAdd as $item)
    {
        $itemsToAddTemp[] = $item;
        /* Because its too slow adding 1 item at a time, we do it in spurts of 200 items. */
        if (count($itemsToAddTemp) > 200)
        {
            $savedLists->addEntryMany($list, $dataItemType, $itemsToAddTemp);
            $itemsToAddTemp = array();
        }
    }
    if (count($itemsToAddTemp) > 0)
    {
        $savedLists->addEntryMany($list, $dataItemType, $itemsToAddTemp);
    }
}

$interface->outputXMLPage(
    "<data>\n" .
    "    <errorcode>0</errorcode>\n" .
    "    <errormessage></errormessage>\n" .
    "    <response>success</response>\n" .
    "</data>\n"
);

?>
