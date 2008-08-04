<?php
/*
 * CATS
 * CLI / Cron Backup Interface
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
 * Invoke with php scripts/makeBackup.php 1
 *
 * THIS FILE REQIRES THE UNIX UTILITY ZIP AND CAN ONLY BE EXECUTED IN A UNIX ENVIRONMENT.
 *
 * $Id: makeBackup.php 3539 2007-11-09 23:03:11Z andrew $
 */

global $stderr;
global $stdout;

if (php_sapi_name() == 'cli')
{
    $stderr = STDERR;
    $stdout = STDOUT;
}
else
{
    $stderr = fopen('php://output', 'w');
    $stdout = fopen('php://output', 'w');
}

    fwrite($stdout, "CATS Site Backup Utility\n");
    fwrite($stdout, "2007 Cognizo Technologies\n\n");


if (isset($_SERVER['argv'][1]))
{
    $CATSHome = realpath(dirname(__FILE__) . '/../');
    chdir($CATSHome);

    include_once('./config.php');
    include_once('./constants.php');
    include_once('./lib/DatabaseConnection.php');
    include_once('modules/install/backupDB.php');

    makeBackup((int) $_SERVER['argv'][1], BACKUP_CATS);
}
else if(php_sapi_name() == 'cli')
{
    fwrite($stderr, "Usage:  php makeBackup.php [Site ID]\n\n");
    fwrite($stderr, "Site ID is usually 1.\n");
}

include_once('./config.php');
include_once('./constants.php');
include_once('./lib/DatabaseConnection.php');
include_once('modules/install/backupDB.php');

function makeBackup($siteID, $backupType = BACKUP_TAR, $logFile = null)
{
    global $stderr;
    global $stdout;

    if ($logFile !== null)
    {
        $stdout = $logFile;
        $stderr = $logFile;
    }

    $db = DatabaseConnection::getInstance();

    $random = rand();
    $i = 0;
    while (file_exists('scripts/backup/'.$random) && $i++ < 30000)
    {
        $random = rand();
    }
    if (file_exists('scripts/backup/'.$random))
    {
        fwrite($stderr, "Unable to create temporary directory.\n");
        die();
    }

    if (!file_exists('scripts/backup'))
    {
        mkdir('scripts/backup');
    }

    if (!file_exists('scripts/backup/'.$random))
    {
        mkdir('scripts/backup/'.$random);
    }

    exec('touch scripts/backup/index.php');
    exec('echo deny from all > scripts/backup/.htaccess');

    fwrite($stdout, "Temporary directory is backup/".$random.". \n\n");

    $primarySiteID = $siteID;
    $siteIDStack = array($siteID);

    while($siteID = array_pop($siteIDStack))
    {
        $rsSite = $db->getAssoc('SELECT * FROM site WHERE site_id = '.$siteID);

        fwrite($stdout, "Backing up '".$rsSite['name']."' (database)... ");

        @mkdir('scripts/backup/'.$random.'/'.$siteID);
        @mkdir('scripts/backup/'.$random.'/'.$siteID.'/db');

        dumpDB($db, 'scripts/backup/'.$random.'/'.$siteID.'/db/catsbackup.sql', false, true, $siteID);

        fwrite($stdout, "(attachments)... ");

        dumpAttachments($db, 'scripts/backup/'.$random.'/'.$siteID.'/', $siteID);

        if ($backupType == BACKUP_TAR)
        {
            fwrite($stdout, "(tar.bz2)... ");

            exec('tar -cjf scripts/backup/'.$random.'/'.$siteID.'.tar.bz2 scripts/backup/'.$random.'/'.$siteID.'/*');
            exec('rm -rf scripts/backup/'.$random.'/'.$siteID.'/');

            $rsSites = $db->getAllAssoc('SELECT * FROM site WHERE parent_site_id = '.$siteID);

            foreach($rsSites as $index => $data)
            {
                array_push($siteIDStack, $data['site_id']);
            }
        }
        else if ($backupType == BACKUP_ZIP)
        {
            //ZIP backup
            fwrite($stdout, "(zip)... ");;

            if (is_executable('/usr/local/bin/zip'))
            {
                exec('/usr/local/bin/zip -r scripts/backup/'.$random.'/'.$siteID.'.zip scripts/backup/'.$random.'/'.$siteID.'/*');
            }
            else
            {
                exec('zip -r scripts/backup/'.$random.'/'.$siteID.'.zip scripts/backup/'.$random.'/'.$siteID.'/*');
            }
            exec('rm -rf scripts/backup/'.$random.'/'.$siteID.'/');

            $rsSites = $db->getAllAssoc('SELECT * FROM site WHERE parent_site_id = '.$siteID);

            foreach($rsSites as $index => $data)
            {
                array_push($siteIDStack, $data['site_id']);
            }
        }
        else
        {
            //CATS Format backup
            fwrite($stdout, "(bak)... ");;

            chdir('scripts/backup/'.$random.'/'.$siteID.'');

            if (is_executable('/usr/local/bin/zip'))
            {
                exec('/usr/local/bin/zip -r ../'.$siteID.'.zip *');
            }
            else
            {
                exec('zip -r ../'.$siteID.'.zip *');
            }
            exec('rm -rf *');

            chdir('../../../..');
        }

        fwrite($stdout, ".\n\n");
    }

    if ($backupType == BACKUP_TAR)
    {
        fwrite($stdout, "Archiving master tar file... \n\n");
        exec('tar -cf scripts/backup/'.$primarySiteID.'_full.tar scripts/backup/'.$random.'/');
        exec('rm -rf scripts/backup/'.$random);
    }
    else if ($backupType == BACKUP_ZIP)
    {
        fwrite($stdout, "Archiving master zip file... \n\n");
        if (file_exists('scripts/backup/'.$primarySiteID.'_full.zip'))
        {
            @unlink('scripts/backup/'.$primarySiteID.'_full.zip');
        }

        if (is_executable('/usr/local/bin/zip'))
        {
            exec('/usr/local/bin/zip scripts/backup/'.$primarySiteID.'_full.zip scripts/backup/'.$random.'/*');
        }
        else
        {
            exec('zip scripts/backup/'.$primarySiteID.'_full.zip scripts/backup/'.$random.'/*');
        }
        //exec('rm -rf scripts/backup/'.$random);
    }
    else
    {
        fwrite($stdout, "Moving file to scripts\backup...  \n\n");

        //CATS Backup
        exec('mv scripts/backup/'.$random.'/'.$primarySiteID.'.zip scripts/backup/catsbackup.bak');
        exec('rm -rf scripts/backup/'.$random);
    }


    if (php_sapi_name() == 'cli')
    {
        fwrite($stdout, "Archive complete!  \n\n");
    }
}

function dumpAttachments($db, $directory, $siteID)
{
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
    while ($row = mysql_fetch_assoc($queryResult))
    {
        $relativePath = sprintf(
            'attachments/%s/%s',
            $row['directory_name'],
            $row['stored_filename']
        );

        $relativeDirectory = sprintf(
            'attachments/%s',
            $row['directory_name']
        );

        $relativeDirectoryParts = explode('/', $relativeDirectory);

        $s = '';
        foreach ($relativeDirectoryParts as $part)
        {
            if (!file_exists($directory.$s.$part))
            {
                mkdir($directory.$s.$part);
            }
            $s .= $part.'/';
        }

        if (file_exists('modules/s3storage'))
        {
            include_once('modules/s3storage/lib.php');

            $s3storage = new S3Storage();
            $s3storage->getTemporarilyFromS3Storage($row['attachment_id']);
        }

        if (file_exists($relativePath))
        {
            @copy($relativePath, $directory.$relativePath);
        }
    }
}


?>