<?php
/*
 * CATS
 * Attachments Reindexing Tool
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
 */

include_once('./config.php');
include_once('./lib/DatabaseConnection.php');
include_once('./lib/ModuleUtility.php');

if (file_exists('INSTALL_BLOCK'))
{
    $interface = new SecureAJAXInterface();
}

if( ini_get('safe_mode') )
{
	//don't do anything in safe mode
}
else
{
	/* Don't limit the execution time. */
	set_time_limit(0);
}
@ini_set('memory_limit', '256M');

$reindexed = 0;

include_once('lib/Attachments.php');

if (file_exists('INSTALL_BLOCK') && ($_SESSION['CATS']->getAccessLevel(ACL::SECOBJ_ROOT) < ACCESS_LEVEL_SA))
{
    die('No permision.');
}

$db = DatabaseConnection::getInstance();
 
$rs = $db->getAllAssoc('SELECT site_id, attachment_id, directory_name, stored_filename FROM attachment WHERE text = "" OR isnull(text) AND resume = 1');

foreach ($rs as $index => $data)
{
    /* Attempt to reindex file. */
    $storedFilename = './attachments/'.$data['directory_name'].'/'.$data['stored_filename'];
    
    $documentToText = new DocumentToText();
    $documentType = $documentToText->getDocumentType(
        $storedFilename
    );

    $fileContents = @file_get_contents($storedFilename);

    /* If we're creating a file from text contents, we can skip
     * extracting because we already know the text contents.
     */
    if ($fileContents !== false && $documentType == DOCUMENT_TYPE_TEXT)
    {
        $extractedText = $fileContents;
    }
    else if (!file_exists($storedFilename))
    {
        /* Can't extract text from a file that doesn't exist. */
        $extractedText = '';
    }
    else
    {
        $documentToText->convert($storedFilename, $documentType);

        if (!$documentToText->isError())
        {
            $extractedText = $documentToText->getString();
            
            $reindexed++;
            
            $db->query('UPDATE attachment SET text = '.$db->makeQueryString($extractedText).' WHERE attachment_id = '.$data['attachment_id']);
        }
    }
}

echo ($reindexed);

?>
