<?php
/*
 * CATS
 * Update 112- fix bad UTF8 filenames
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
 * $Id: 114.php 2359 2007-04-21 22:49:17Z will $
 */

function getAllFilesInDirectory($directory)
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

function update_114($db)
{
    $attachments = $db->query('SELECT * FROM attachment');
    while ($attachment = mysql_fetch_assoc($attachments))
    {
        $newFilename = $attachment['stored_filename'];
        for ($i = 0; $i < strlen($newFilename); $i++)
        {
            if (ord($newFilename[$i]) > 128 || ord($newFilename[$i]) < 32)
            {
                $newFilename[$i] = '_';
            }
        }

        if ((!file_exists('attachments/' . $attachment['directory_name'] . '/' . $attachment['stored_filename']) &&
             is_dir('attachments/' . $attachment['directory_name'])) ||
            $newFilename != $attachment['stored_filename'])
        {
            $filesInDirectory = getAllFilesInDirectory('attachments/'.$attachment['directory_name'].'/');
            if (count($filesInDirectory) == 1)
            {
                rename ('attachments/'.$attachment['directory_name'].'/'.$filesInDirectory[0], 'attachments/'.$attachment['directory_name'].'/'.$newFilename);
                $db->query('UPDATE attachment SET stored_filename = "' . addslashes($newFilename) . '" WHERE attachment_id = '.$attachment['attachment_id']);
            }
        }
    }
}

?>
