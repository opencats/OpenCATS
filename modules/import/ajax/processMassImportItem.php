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
include_once(LEGACY_ROOT . '/lib/ImportUtility.php');

$interface = new SecureAJAXInterface();

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    die('Invalid request.');
}

if ($_SESSION['CATS']->getAccessLevel('import.bulkResumes') < ACCESS_LEVEL_SA)
{
    $interface->outputXMLErrorPage(-1, ERROR_NO_PERMISSION);
    return;
}

$queue = $_SESSION['CATS_LEGACY_BULK_RESUMES'] ?? null;
if (($queue['userID'] ?? null) !== $interface->getUserID() ||
    !is_array($queue['files'] ?? null))
{
    die('No mass import in progress.');
}

$files = &$_SESSION['CATS_LEGACY_BULK_RESUMES']['files'];
if (!$files)
{
    die('done');
}

// Direct requests start outside the application root.
if (!chdir(LEGACY_ROOT))
{
    die('No mass import in progress.');
}
$directoryRoot = ImportUtility::getLegacyBulkResumeDirectory();
if ($directoryRoot === false)
{
    die('No mass import in progress.');
}

$dups = 0;
$success = 0;
$processed = 0;
// FIXME: Count failures.

for ($i = 0; $i < 50 && $files; ++$i)
{
    $name = array_key_last($files);
    $staged = array_pop($files);
    ++$processed;

    if (!is_string($name) || !is_string($staged['path'] ?? null))
    {
        continue;
    }

    $fullFilename = ImportUtility::resolveMassImportPath($directoryRoot, $name);
    if ($fullFilename !== $staged['path'] ||
        ($stat = @stat($fullFilename)) === false ||
        $stat['dev'] !== ($staged['device'] ?? null) || $stat['ino'] !== ($staged['inode'] ?? null))
    {
        continue;
    }

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
}

echo $dups, ',', $success, ',', $processed;

?>
