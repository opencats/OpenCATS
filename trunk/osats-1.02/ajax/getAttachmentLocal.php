<?php
/*
   * OSATS
   * AJAX Activity Entry Deletion Interface
   *
   * Open Source GNU License will apply
*/


$interface = new SecureAJAXInterface();

include_once('./lib/CommonErrors.php');
include_once('./lib/Attachments.php');

@ini_set('memory_limit', '256M');

if (!isset($_POST['id']) || !$interface->isRequiredIDValid('id'))
{
    $interface->outputXMLErrorPage(-2, 'No attachment ID specified.');
    die();
}

$attachmentID = $_POST['id'];

$attachments = new Attachments(-1);

$rs = $attachments->get($attachmentID, false);

if (!isset($rs['directoryName']) ||
    !isset($rs['storedFilename']) ||
    md5($rs['directoryName']) != $_POST['directoryNameHash'])
{
    $interface->outputXMLErrorPage(-2, 'Invalid directory name hash.');
    die();
}

$directoryName = $rs['directoryName'];
$fileName = $rs['storedFilename'];

/* Check for the existence of the backup.  If it is gone, send the user to a page informing them to press back and generate the backup again. */
if ($rs['contentType'] == 'OSATSbackup')
{
    if (!file_exists('attachments/'.$directoryName.'/'.$fileName))
    {
        $interface->outputXMLErrorPage(-2, 'The specified backup file no longer exists.  Please press back and regenerate the backup before downloading.  We are sorry for the inconvenience.');
        die();
    }
}

$url = 'attachments/'.$directoryName.'/'.$fileName;

if (!eval(Hooks::get('ATTACHMENT_RETRIEVAL'))) return;

if (!file_exists('attachments/'.$directoryName.'/'.$fileName))
{
    $interface->outputXMLErrorPage(-2, 'The file is temporarily unavailable for download.  Please try again.');
    die();
}

$output =
    "<data>\n" .
    "    <errorcode>0</errorcode>\n" .
    "    <errormessage></errormessage>\n" .
    "    <success>1</success>\n" .
    "</data>\n";

/* Send back the XML data. */
$interface->outputXMLPage($output);