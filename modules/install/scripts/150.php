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

function getAllFilesInDirectory150($directory)
{
    $files = array();

    $handle = @opendir($directory);
    if (!$handle)
    {
        return array();
    }

    while (($file = readdir($handle)) !== false)
    {
        if ($file != '.' && $file != '..')
        {
            $files[] = $file;
        }
    }

    closedir($handle);

    return $files;
}

function update_150($db)
{
    global $badFileExtensions;

    $lastAttachmentID = 0;
    $batchSize = 100;

    while (true)
    {
        $attachments = $db->getAllAssoc(
            'SELECT
            attachment_id,
            directory_name,
            stored_filename
            FROM
            attachment
            WHERE
            attachment_id > '
            . $db->makeQueryInteger($lastAttachmentID)
            . '
            ORDER BY
            attachment_id ASC
            LIMIT '
            . (int) $batchSize
        );

        if (empty($attachments))
        {
            break;
        }

        foreach ($attachments as $attachment)
        {
            /*
             * Always advance the keyset position, including when no rename
             * is required or the filesystem rename fails.
             */
            $lastAttachmentID = (int) $attachment['attachment_id'];

            $fileExtension = substr(
                $attachment['stored_filename'],
                strrpos($attachment['stored_filename'], '.') + 1
            );

            if (!in_array($fileExtension, $badFileExtensions))
            {
                continue;
            }

            $oldFilename = $attachment['stored_filename'];
            $newFilename = $attachment['stored_filename'] . '.txt';

            $status = @rename(
                'attachments/'
                . $attachment['directory_name']
                . '/'
                . $oldFilename,
                'attachments/'
                . $attachment['directory_name']
                . '/'
                . $newFilename
            );

            if ($status)
            {
                $db->query(
                    'UPDATE attachment SET stored_filename = '
                    . $db->makeQueryString($newFilename)
                    . ' WHERE attachment_id = '
                    . $db->makeQueryInteger($attachment['attachment_id'])
                );
            }
        }
    }
}

?>
