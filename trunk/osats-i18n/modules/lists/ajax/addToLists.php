<?php
/*
 * CATS
 * AJAX New List Name Interface
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * $Id: addToLists.php 3198 2007-10-14 23:36:43Z will $
 */

include_once('./lib/StringUtility.php');
include_once('./lib/ActivityEntries.php');
include_once('./lib/SavedLists.php');

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

if (!isset($_REQUEST['listsToAdd']))
{
    $interface->outputXMLErrorPage(-1, 'No listsToAdd passed.');
    die();
}

if (!isset($_REQUEST['itemsToAdd']))
{
    $interface->outputXMLErrorPage(-1, 'No itemsToAdd passed.');
    die();
}

if (!$interface->isRequiredIDValid('dataItemType'))
{
    $interface->outputXMLErrorPage(-1, 'Invalid saved list type.');
    die();
}

$siteID = $interface->getSiteID();

$listsToAdd = explode(',', $_REQUEST['listsToAdd']);
$itemsToAdd = explode(',', $_REQUEST['itemsToAdd']);
$dataItemType = $_REQUEST['dataItemType'];

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

$savedLists = new SavedLists($siteID);

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
