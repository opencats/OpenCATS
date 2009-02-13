<?php
/*
 * CATS
 * Update 150 - rename executable attachment file names
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
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
 * $Id: 150.php 2359 2007-04-21 22:49:17Z will $
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

    $attachments = $db->query('SELECT * FROM attachment');
    while ($attachment = mysql_fetch_assoc($attachments))
    {
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
            'attachments/' . $attachment['directory_name'] . '/' . $oldFilename,
            'attachments/' . $attachment['directory_name'] . '/' . $newFilename
        );
        if ($status)
        {
            $db->query(
                'UPDATE attachment SET stored_filename = '
                . $db->makeQueryString($newFilename)
                . ' WHERE attachment_id = ' . $attachment['attachment_id']
            );
        }
    }
}


?>
