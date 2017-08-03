<?php
/*
 * CATS
 * Attachments Directory Layout Migration Tool
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
 * $Id: attachmentsToThreeDirectory.php 2336 2007-04-14 22:01:51Z will $
 */

include_once('./config.php');
include_once('./lib/DatabaseConnection.php');

$interface = new SecureAJAXInterface();

if ($_SESSION['CATS']->getAccessLevel(ACL::SECOBJ_ROOT) < ACCESS_LEVEL_ROOT)
{
    die('No permision.');
}

if( ini_get('safe_mode') )
{
	//don't do anything in safe mode
}
else
{
	/* Don't limit the execution time */
	set_time_limit(0);
}
@ini_set('memory_limit', '256M');

include_once('lib/Attachments.php');

$db = DatabaseConnection::getInstance();
 
include_once('lib/Attachments.php');

$db->query('ALTER IGNORE TABLE `attachment` CHANGE `directory_name` `directory_name` VARCHAR(64);');
 
$rs = $db->getAllAssoc('SELECT site_id, attachment_id, directory_name FROM attachment');

foreach ($rs as $index => $data)
{
    if (strpos($data['directory_name'], '/') !== false)
    {
        continue;
    }
    
    $siteDirectory = 'site_'.$data['site_id'];
    $idDirectory = ((int) ($data['attachment_id'] / 1000)) . 'xxx';
    
    $newFileDirectory = './attachments/' . $siteDirectory;
    
    if (!is_dir($newFileDirectory))
    {
        @mkdir($newFileDirectory, 0777);
        
        /* Prevent listing of new directory. */
        @file_put_contents($newFileDirectory . '/index.php', '');
    }

    $newFileDirectory = './attachments/' . $siteDirectory . '/' . $idDirectory;
    
    if (!is_dir($newFileDirectory))
    {
        @mkdir($newFileDirectory, 0777);
        
         /* Prevent listing of new directory. */
        @file_put_contents($newFileDirectory . '/index.php', '');
    }

    $fullDirectoryPath = $newFileDirectory . '/' . $data['directory_name'];
    
    if (@rename('./attachments/' . $data['directory_name'], $fullDirectoryPath) === true)
    {
        $newDirectoryEntry = $siteDirectory.'/'.$idDirectory.'/'.$data['directory_name'];
        $db->query('UPDATE attachment SET directory_name = '.$db->makeQueryString($newDirectoryEntry).' WHERE attachment_id = '.$data['attachment_id']);
    }
}

?>
