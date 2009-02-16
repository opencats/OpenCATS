<?php
/*
 * CATS
 * AJAX Backup interface
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1 (the "License"); you may not use this file except in
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
 * $Id: processMassImportItem.php 2359 2007-04-21 22:49:17Z will $
 */


include_once('lib/Attachments.php');

$interface = new SecureAJAXInterface();

if (!isset($_SESSION['CATS']->massImportFiles) ||
    !isset($_SESSION['CATS']->massImportDirectory))
{
    die ('No mass import in progress.');
}

if (count($_SESSION['CATS']->massImportFiles) == 0)
{
    die ('done');
}

$dups = 0;
$success = 0;
$processed = 0;
// FIXME: Count failures.

for ($i = 0; $i < 50; ++$i)
{
    if (count($_SESSION['CATS']->massImportFiles) == 0)
    {
        continue;
    }
    
    $fileName = array_pop($_SESSION['CATS']->massImportFiles);

    $fullFilename = $_SESSION['CATS']->massImportDirectory . '/' . $fileName;

    $attachmentCreator = new AttachmentCreator($_SESSION['CATS']->getSiteID());
    $attachmentID = $attachmentCreator->createFromFile(
        DATA_ITEM_BULKRESUME, 0, $fullFilename, false, '', true, true
    );

    if ($attachmentCreator->isError())
    {
        //Nothing
    }
    else if ($attachmentCreator->isTextExtractionError())
    {
        //Nothing
    }
    else if ($attachmentCreator->duplicatesOccurred())
    {
        ++$dups;
    }
    else
    {
        ++$success;
    }
    
    ++$processed;
}

echo $dups, ',', $success, ',', $processed;

?>
