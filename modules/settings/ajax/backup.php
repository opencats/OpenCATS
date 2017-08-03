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
 * $Id: backup.php 3402 2007-11-02 22:03:43Z brian $
 */

@ini_set('memory_limit', '512M');

include_once('lib/Attachments.php');

$interface = new SecureAJAXInterface();

if ($_SESSION['CATS']->getAccessLevel(ACL::SECOBJ_ROOT) < ACCESS_LEVEL_SA)
{
    die('No permision.');
}

if (!isset($_REQUEST['a']))
{
    die('No action.');
}

$action = $_REQUEST['a'];

$completedTasks = '';
function markCompleted($task)
{
    global $completedTasks;

    $completedTasks .= '<tr><td width="295px;">' . $task
        . '</td><td><span class="passedText">DONE</span></td></tr>';
}

function setStatusBackup($status, $progress)
{
    global $completedTasks;

    $command = '<script type="text/javascript">setStatus(\'' . $status . '\');'
        . ' setProgress(' . $progress . ');'
        . ' progressComplete(\'' . $completedTasks.'\');</script>';

    $directory = $_SESSION['CATS']->retrieveValueByName('backupDirectory');
    @file_put_contents($directory . 'progress.txt', $command);
}


if ($action == 'start')
{
    $companyID = $_SESSION['CATS']->getSiteCompanyID();
    $attachmentsOnly = $interface->isChecked('attachmentsOnly');

    /* Delete any old backups. */
    $attachments = new Attachments(CATS_ADMIN_SITE);
    $attachments->deleteAll(
        DATA_ITEM_COMPANY,
        $companyID,
        "AND content_type = 'catsbackup'"
    );
    
    /* Build title string. */
    if ($attachmentsOnly)
    {
        $title = 'CATS Attachments Backup';
    }
    else
    {
        $title = 'CATS Backup';
    }

    $attachmentCreator = new AttachmentCreator(CATS_ADMIN_SITE);
    $attachmentCreator->createFromFile(
        DATA_ITEM_COMPANY, $companyID, 'catsbackup.bak', $title, 'catsbackup',
        false, false
    );
    if ($attachmentCreator->isError())
    {
        die($attachmentCreator->getError());
    }
    
    $attachmentID = $attachmentCreator->getAttachmentID();
    $directory = $attachmentCreator->getContainingDirectory();

    $_SESSION['CATS']->storeValueByName('backupDirectory', $directory);

    /* Build request parameters. */
    $extraPOSTData = '&attachmentID=' . $attachmentID;
    if ($attachmentsOnly)
    {
        $extraPOSTData .= '&attachmentsOnly=true';
    }

    echo '<script type="text/javascript">watchBackup(\'', $directory,
        '\', \'', $extraPOSTData, '\', \'settings:backup\');</script>';

}

if ($action == 'backup')
{
    include_once('./lib/FileCompressor.php');
    
	if( ini_get('safe_mode') )
	{
		//don't do anything in safe mode
	}
	else
	{
		/* Don't limit the execution time during backup. */
		set_time_limit(0);
	}
    
    // FIXME: Make this configurable.
    @ini_set('memory_limit', '192M');

    if (!$interface->isRequiredIDValid('attachmentID'))
    {
        die('Error: Invalid attachment ID.');
    }

    $attachmentID    = $_REQUEST['attachmentID'];
    $attachmentsOnly = $interface->isChecked('attachmentsOnly');
    
    $siteID = $_SESSION['CATS']->getSiteID();
    $db = DatabaseConnection::getInstance();

    /* Our "temp" path, as well as the path where the final zip file will be
     * saved.
     */
    $directory = $_SESSION['CATS']->retrieveValueByName('backupDirectory');

    // FIXME: Show progress.

    /* Create a new zip file. */
    $zipFilePath = $directory . 'catsbackup.bak';
    $zipFileCreator = new ZipFileCreator($directory . 'catsbackup.bak', true);
    if (!$zipFileCreator->open())
    {
        setStatusBackup('Error: Failed to open zip file.', 0);
        die('Failed to open zip file.');
    }

    /* Backup the database if we're not in attachments-only mode. */
    if (!$attachmentsOnly)
    {
        include_once('modules/install/backupDB.php');
        
        $SQLDumpPath = $directory . 'catsbackup.sql';
    
        /* Dump SQL tables to the filesystem. This will dump both a complete
         * schema and special CATS restore files split into ~1MB chunks.
         */
        $totalFiles = dumpDB($db, $SQLDumpPath, true);
        markCompleted('Dumping tables...');

        /* Add the complete database dump to the zip file. */
        setStatusBackup('Compressing database...', 0);
        $status = $zipFileCreator->addFileFromDisk(
            'database',
            $SQLDumpPath
        );
        @unlink($SQLDumpPath);

        /* Fail out if we were't successful writing the file to the zip. */
        if (!$status)
        {
            setStatusBackup('Error: Failed to add database to zip file.', 0);
            $zipFileCreator->abort();
            die('Failed to add database to zip file.');
        }
        markCompleted('Compressing SQL dump...');

        /* Add the CATS restore files to the zip file. */
        for ($i = 0; $i < $totalFiles; ++$i)
        {
            $fileNumber = $i + 1;
        
            setStatusBackup(
                sprintf(
                    'Compressing database (%s of %s files processed)...',
                    $fileNumber,
                    $totalFiles
                ),
                ($fileNumber / $totalFiles)
            );

            $status = $zipFileCreator->addFileFromDisk(
                'db/catsbackup.sql.' . $i,
                $SQLDumpPath . '.' . $i
            );
            if (!$status)
            {
                setStatusBackup(
                    'Error: Failed to add database part to zip file.', 0
                );
                $zipFileCreator->abort();
                die('Failed to add database part to zip file.');
            }
        
            @unlink($SQLDumpPath . '.' . $i);
        }
        markCompleted('Compressing database for CATS restore...');
    }

    /* Add all attachments to the archive. */
    // FIXME: SQL shouldn't be trickling up to this layer.

    /* Get attachments metadata for this site. */
    $sql = sprintf(
        "SELECT
            directory_name,
            stored_filename,
            attachment_id
        FROM
            attachment
        WHERE
            site_id = %s",
        $siteID
    );
    $queryResult = mysql_query($sql);
    $totalAttachments = mysql_num_rows($queryResult);

    /* Add each attachment to the zip file. */
    $attachmentCount = 0;
    while ($row = mysql_fetch_assoc($queryResult))
    {
        ++$attachmentCount;
        $relativePath = sprintf(
            'attachments/%s/%s',
            $row['directory_name'],
            $row['stored_filename']
        );
        if (!file_exists($relativePath)) {
            setStatusBackup(
                sprintf(
                    '%s - Skipping attachment as it\'s missing on the file system (%s of %s files processed)...',
                    $relativePath,
                    $attachmentCount,
                    $totalAttachments
                ),
                ($attachmentCount / $totalAttachments)
            );
            continue;
        }

        setStatusBackup(
            sprintf(
                '%s - Adding attachments (%s of %s files processed)...',
                $relativePath,
                $attachmentCount,
                $totalAttachments
            ),
            ($attachmentCount / $totalAttachments)
        );
        $attachmentID = $row['attachment_id'];
        
        if (!eval(Hooks::get('FORCE_ATTACHMENT_LOCAL'))) return;
        
        $status = $zipFileCreator->addFileFromDisk(
            $relativePath, $relativePath
        );
    }
    markCompleted('Adding attachments...');

    /* Finalize the zip file and write it to disk. */
    setStatusBackup('Writing backup...', 1);
    $status = $zipFileCreator->finalize();
    if (!$status)
    {
        setStatusBackup('Error: Failed to write zip file.', 0);
        die('Failed to add write zip file.');
    }

    /* Update attachment metadata for the zip file now that it's completed. */
    $md5sum   = @md5_file($zipFilePath);
    $fileSize = (int) @filesize($zipFilePath) / 1024;

    $attachments = new Attachments(CATS_ADMIN_SITE);
    $attachments->setSizeMD5($attachmentID, $fileSize, $md5sum);

    echo '<html><head>',
         '<script type="text/javascript">parent.backupFinished();</script>',
         '</head><body>Backup Finished.</body></html>';
}

?>
