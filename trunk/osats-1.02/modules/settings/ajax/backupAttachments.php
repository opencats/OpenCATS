<?php
/*
 * OSATS
 * AJAX Backup interface
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the OSATS Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.OSATSone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "OSATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * $Id: backupAttachments.php 2380 2007-04-25 21:01:23Z will $
 */


include_once('lib/Attachments.php');

$interface = new SecureAJAXInterface();

if ($_SESSION['OSATS']->getAccessLevel() < ACCESS_LEVEL_SA)
{
    die('No permision.');
}

if (!isset($_REQUEST['a']))
{
    die('No action.');
}

/* Send back the XML data. */
$action = $_REQUEST['a'];

$completedTasks = '';

if ($action == 'start')
{
    $companyID = $_SESSION['OSATS']->getSiteCompanyID();

    $attachments = new Attachments(ADMIN_SITE);

    /* Delete any old backups. */
    $attachments->deleteAll(
        DATA_ITEM_COMPANY,
        $companyID,
        "AND content_type = 'OSATSbackup'"
    );

    $attachmentCreator = new AttachmentCreator(ADMIN_SITE);
    $attachmentCreator->createFromFile(
        DATA_ITEM_COMPANY, $companyID, 'OSATSattachments.zip',
        'OSATS Attachments Backup', 'OSATSbackup', false, false
    );
    if ($attachmentCreator->isError())
    {
        die($attachmentCreator->getError());
    }

    $attachmentID = $attachmentCreator->getAttachmentID();
    $directory = $attachmentCreator->getContainingDirectory();

    $_SESSION['OSATS']->storeValueByName('backupDirectory', $directory);

    echo '<script type="text/javascript">watchBackup(\'',
         $directory, '\', ' . $attachmentID . ', \'settings:backupAttachments\');</script>';

}

function setStatusBackup($status, $progress)
{
    global $completedTasks;

    $command = '<script type="text/javascript">setStatus(\'' . $status . '\');'
        . ' setProgress(' . $progress . ');'
        . ' progressComplete(\'' . $completedTasks.'\');</script>';

    $directory = $_SESSION['OSATS']->retrieveValueByName('backupDirectory');
    @file_put_contents($directory . 'progress.txt', $command);
}

function appendComplete($task)
{
    global $completedTasks;

    $completedTasks .= '<tr><td width="295px;">' . $task . '</td></tr>';
}

if ($action == 'backup')
{
    /* Backups shouldn't time out. */
    set_time_limit(0);
    
    // FIXME: Make this configurable.
    @ini_set('memory_limit', '192M');

    $db = DatabaseConnection::getInstance();
    $connection = $db->getConnection();

    if (!$interface->isRequiredIDValid('attachmentID'))
    {
        die('Error: Invalid attachment ID.');
    }

    $attachmentID = $_REQUEST['attachmentID'];
    $siteID = $_SESSION['OSATS']->getSiteID();

    $directory = $_SESSION['OSATS']->retrieveValueByName('backupDirectory');

    include_once('lib/zip/ziplib.php');
    $zipfile = new Ziplib();

    // GET AND COMPRESS ATTACHMENTS

    $sql = "SELECT COUNT(*) AS attachmentCount FROM attachment WHERE site_id = " . $siteID;
    $rs = mysql_query($sql);
    $row = mysql_fetch_assoc($rs);
    $totalAttachments = $row['attachmentCount'];

    $sql = "SELECT * FROM attachment WHERE site_id = " . $siteID;
    $rs = mysql_query($sql);
    $attachment = 0;
    while ($recordSet = mysql_fetch_assoc($rs))
    {
        ++$attachment;

        setStatusBackup(
            'Adding attachments (' . $attachment . ' of ' . $totalAttachments . ' files processed)...',
            ($attachment / $totalAttachments)
        );
 	    $zipfile->zl_add_file(
            @file_get_contents('attachments/' . $recordSet['directory_name'] . '/' . $recordSet['stored_filename']),
            'attachments/' . $recordSet['directory_name'] . '/' . $recordSet['stored_filename'],
            'g9'
        );
    }

    appendComplete('Adding attachments... </td><td><span class="passedText">DONE</span>');
    setStatusBackup('Writing backup...', 1);

    // FIXME: Did we write the file successfully? Error handling.
    $newFileFullPath = $directory . 'OSATSattachments.zip';
    $status = @file_put_contents($newFileFullPath, $zipfile->zl_pack(''));

    // FIXME: Remove this after AttachmentsCreator refactoring is complete.
    $md5sum   = @md5_file($newFileFullPath);
    $fileSize = (int) @filesize($newFileFullPath) / 1024;
    @file_put_contents('blah.hi', $attachmentID);

    $attachments = new Attachments(ADMIN_SITE);
    $attachments->setSizeMD5($attachmentID, $fileSize, $md5sum);

    echo '<html><head>',
         '<script type="text/javascript">parent.backupFinished();</script>',
         '</head><body>Backup Finished.</body></html>';
}

?>