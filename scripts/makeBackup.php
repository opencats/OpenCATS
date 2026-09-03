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


if (php_sapi_name() == 'cli')
{
    $CATSHome = realpath(dirname(__FILE__) . '/../');
    chdir($CATSHome);

    include_once('./config.php');
    include_once(LEGACY_ROOT . '/constants.php');
    include_once(LEGACY_ROOT . '/lib/DatabaseConnection.php');
    include_once(LEGACY_ROOT . '/modules/install/backupDB.php');

    makeBackup(BACKUP_CATS);
}

include_once('./config.php');
include_once(LEGACY_ROOT . '/constants.php');
include_once(LEGACY_ROOT . '/lib/DatabaseConnection.php');
include_once(LEGACY_ROOT . '/modules/install/backupDB.php');

function makeBackup($backupType = BACKUP_TAR, $logFile = null)
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

    $rsSite = $db->getAssoc('SELECT * FROM site LIMIT 1');

    fwrite($stdout, "Backing up '".$rsSite['name']."' (database)... ");

    @mkdir('scripts/backup/'.$random.'/db');

    dumpDB($db, 'scripts/backup/'.$random.'/db/catsbackup.sql', false, true);

    fwrite($stdout, "(attachments)... ");

    dumpAttachments($db, 'scripts/backup/'.$random.'/');

    if ($backupType == BACKUP_TAR)
    {
        fwrite($stdout, "(tar.bz2)... ");

        exec('tar -cjf scripts/backup/'.$random.'/catsbackup.tar.bz2 scripts/backup/'.$random.'/*');
        exec('rm -rf scripts/backup/'.$random.'/db/');

    }
    else if ($backupType == BACKUP_ZIP)
    {
        fwrite($stdout, "(zip)... ");

        if (is_executable('/usr/local/bin/zip'))
        {
            exec('/usr/local/bin/zip -r scripts/backup/'.$random.'/catsbackup.zip scripts/backup/'.$random.'/*');
        }
        else
        {
            exec('zip -r scripts/backup/'.$random.'/catsbackup.zip scripts/backup/'.$random.'/*');
        }
        exec('rm -rf scripts/backup/'.$random.'/db/');

    }
    else
    {
        fwrite($stdout, "(bak)... ");

        chdir('scripts/backup/'.$random.'');

        if (is_executable('/usr/local/bin/zip'))
        {
            exec('/usr/local/bin/zip -r ../catsbackup.zip *');
        }
        else
        {
            exec('zip -r ../catsbackup.zip *');
        }
        exec('rm -rf *');

        chdir('../../..');
    }

    fwrite($stdout, ".\n\n");

    if ($backupType == BACKUP_TAR)
    {
        fwrite($stdout, "Archiving master tar file... \n\n");
        exec('tar -cf scripts/backup/catsbackup_full.tar scripts/backup/'.$random.'/');
        exec('rm -rf scripts/backup/'.$random);
    }
    else if ($backupType == BACKUP_ZIP)
    {
        fwrite($stdout, "Archiving master zip file... \n\n");
        if (file_exists('scripts/backup/catsbackup_full.zip'))
        {
            @unlink('scripts/backup/catsbackup_full.zip');
        }

        if (is_executable('/usr/local/bin/zip'))
        {
            exec('/usr/local/bin/zip scripts/backup/catsbackup_full.zip scripts/backup/'.$random.'/*');
        }
        else
        {
            exec('zip scripts/backup/catsbackup_full.zip scripts/backup/'.$random.'/*');
        }
    }
    else
    {
        fwrite($stdout, "Moving file to scripts/backup...  \n\n");

        exec('mv scripts/backup/'.$random.'/catsbackup.zip scripts/backup/catsbackup.bak');
        exec('rm -rf scripts/backup/'.$random);
    }


    if (php_sapi_name() == 'cli')
    {
        fwrite($stdout, "Archive complete!  \n\n");
    }
}

function dumpAttachments($db, $directory)
{
    $sql =
        "SELECT
            directory_name,
            stored_filename,
            attachment_id
        FROM
            attachment";

    $db->query($sql);
    $totalAttachments = $db->getNumRows();

    /* Add each attachment to the zip file. */
    while (true)
    {
        $row = $db->getAssoc();
        if (empty($row))
        {
            break;
        }

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
            include_once(LEGACY_ROOT . '/modules/s3storage/lib.php');

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
