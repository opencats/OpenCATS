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


include_once(LEGACY_ROOT . '/lib/Attachments.php');

$interface = new SecureAJAXInterface();

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    die('Invalid request.');
}

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

    $attachmentCreator = new AttachmentCreator();
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
