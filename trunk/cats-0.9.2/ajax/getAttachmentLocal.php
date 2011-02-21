<?php
/*
 * CATS
 * AJAX Attachment retrieval function
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
 * $Id: getAttachmentLocal.php 3078 2007-09-21 20:25:28Z will $
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
if ($rs['contentType'] == 'catsbackup')
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

?>
