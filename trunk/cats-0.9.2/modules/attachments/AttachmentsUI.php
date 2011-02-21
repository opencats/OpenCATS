<?php
/*
 * CATS
 * Attachments Module
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
 * $Id: HomeUI.php 2969 2007-08-29 23:33:39Z brian $
 */

include_once('./lib/CommonErrors.php');
include_once('./lib/Attachments.php');

class AttachmentsUI extends UserInterface
{
    /* This is how many bytes at a time we read and output from an attachment. */
    const ATTACHMENT_BLOCK_SIZE = 80192;


    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = false;
        $this->_moduleDirectory = 'attachments';
        $this->_moduleName = 'attachments';
        $this->_moduleTabText = '';
        $this->_subTabs = array();
    }


    public function handleRequest()
    {
        $action = $this->getAction();

        if (!eval(Hooks::get('ATTACHMENTS_HANDLE_REQUEST'))) return;

        switch ($action)
        {
            case 'getAttachment':
                $this->getAttachment();
                break;
                
            default:
                break;
        }
    }


    private function getAttachment()
    {
        // FIXME: Do we really need to mess with memory limits here? We're only reading ~80KB at a time...
        @ini_set('memory_limit', '128M'); 
        
        if (!$this->isRequiredIDValid('id', $_GET))
        {
            CommonErrors::fatal(
                COMMONERROR_BADINDEX, $this, 'No attachment ID specified.'
            );
        }

        $attachmentID = $_GET['id'];

        $attachments = new Attachments(-1);
        $rs = $attachments->get($attachmentID, false);

        if (empty($rs) || md5($rs['directoryName']) != $_GET['directoryNameHash'])
        {
            CommonErrors::fatal(
                COMMONERROR_BADFIELDS,
                $this,
                'Invalid id / directory / filename, or you do not have permission to access this attachment.'
            );
        }
        
        $directoryName = $rs['directoryName'];
        $fileName      = $rs['storedFilename'];
        $filePath      = sprintf('attachments/%s/%s', $directoryName, $fileName);

        /* Check for the existence of the backup.  If it is gone, send the user to a page informing them to press back and generate the backup again. */
        if ($rs['contentType'] == 'catsbackup' && !file_exists($filePath))
        {
            CommonErrors::fatal(
                COMMONERROR_FILENOTFOUND,
                $this,
                'The specified backup file no longer exists. Please go back and regenerate the backup before downloading. We are sorry for the inconvenience.'
            );
        }
        
        // FIXME: Stream file rather than redirect? (depends on download preparer working).
        if (!eval(Hooks::get('ATTACHMENT_RETRIEVAL'))) return;

        /* Determine MIME content type of the file. */
        $contentType = Attachments::fileMimeType($fileName);

        /* Open the file and verify that it is readable. */
        $fp = @fopen($filePath, 'r');
        if ($fp === false)
        {
            CommonErrors::fatal(
                COMMONERROR_BADFIELDS,
                $this,
                'This attachment is momentarily offline, please try again later. The support staff has been notified.'
            );
        }

        /* Set headers for sending the file. */
        header('Content-Disposition: inline; filename="' . $fileName . '"');  //Disposition attachment was default, but forces download.
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . filesize($filePath));
        header('Pragma: no-cache');
        header('Expires: 0');
        
        /* Read the file in ATTACHMENT_BLOCK_SIZE-sized chunks from disk and
         * output to the browser.
         */
        while (!feof($fp))
        {
            print fread($fp, self::ATTACHMENT_BLOCK_SIZE);
        }
        
        fclose($fp);
        
        /* Exit to prevent output after the attachment. */
        exit();
    }

}

?>
