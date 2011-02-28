<?php
/**
 * CATS
 * Attachments Library
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
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: Attachments.php 3793 2007-12-03 22:58:01Z andrew $
 */


include_once('./lib/FileUtility.php');
include_once('./lib/DocumentToText.php');
include_once('./lib/DatabaseSearch.php');
include_once('./lib/Hooks.php');

/**
 *	Attachments Library
 *	@package    CATS
 *	@subpackage Library
 */
class Attachments
{
    private $_db;
    private $_siteID;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }

    /**
     * Adds attachment metadata to the database.
     *
     * @param flag Data Item type.
     * @param integer Data Item ID.
     * @param string Attachment title.
     * @param string Original filename.
     * @param string Filename on the local filesystem.
     * @param string MIME content type.
     * @param boolean Is this attachment a resume?
     * @param string Resume text, or '' if none.
     * @param boolean Is this attachment a profile image?
     * @param string Directory name on the local filesystem.
     * @param integer File size in KB.
     * @param string Attachment md5sum.
     * @return integer New attachment ID, or -1 on failure.
     */
    public function add($dataItemType, $dataItemID, $attachmentTitle,
        $originalFilename, $storedFilename, $contentType, $isResume,
        $resumeText, $isProfileImage, $directoryName, $fileSize = 0,
        $md5sum = '')
    {
        /* If this is a profile image, delete all other profile images (users
         * can only have one).
         */
        if ($isProfileImage)
        {
            $this->deleteProfileImages($dataItemType, $dataItemID);
        }

        // FIXME: Will: Remove DatabaseSearch dependency.
        $resumeText = DatabaseSearch::fulltextEncode($resumeText);

        if ($resumeText != '')
        {
            $md5sumText = md5($resumeText);
        }
        else
        {
            $md5sumText = '';
        }

        $sql = sprintf(
            "INSERT INTO attachment (
                data_item_type,
                data_item_id,
                title,
                original_filename,
                stored_filename,
                content_type,
                resume,
                text,
                profile_image,
                site_id,
                date_created,
                date_modified,
                directory_name,
                md5_sum,
                md5_sum_text,
                file_size_kb
            )
            VALUES (
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                NOW(),
                NOW(),
                %s,
                %s,
                %s,
                %s
            )",
            $this->_db->makeQueryInteger($dataItemType),
            $this->_db->makeQueryInteger($dataItemID),
            $this->_db->makeQueryString($attachmentTitle),
            $this->_db->makeQueryString($originalFilename),
            $this->_db->makeQueryString($storedFilename),
            $this->_db->makeQueryString($contentType),
            ($isResume ? '1' : '0'),
            $this->_db->makeQueryStringOrNULL($resumeText),
            ($isProfileImage ? '1' : '0'),
            $this->_siteID,
            $this->_db->makeQueryString($directoryName),
            $this->_db->makeQueryString($md5sum),
            $this->_db->makeQueryString($md5sumText),
            $this->_db->makeQueryInteger($fileSize)
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        $lastInsertID = $this->_db->getLastInsertID();

        // FIXME: Slow?
        eval(Hooks::get('UPDATE_SPHINX_DELTA'));

        $this->updateSiteSize();

        return $lastInsertID;
    }

    /**
     * Does whatever steps are necessary to make the file local so the file
     * can be edited or removed or whatever happens to files.
     *
     * @param integer Attachment ID.
     */
    public function forceAttachmentLocal($attachmentID)
    {
        if (!eval(Hooks::get('FORCE_ATTACHMENT_LOCAL'))) return;
    }

    /**
     * Does whatever steps are necessary to make the file get off of the local
     * file system if a remote file storage module is available.
     *
     * Would generally be used by a matienence script that uses forceAttachmentLocal
     * to make an attachment local, then must release the space used by that
     * attachment so a new attachment can be downloaded.  This is not
     * necessary to be used generally in CATS because cron will slowly
     * move local files to their remote location on its own.
     *
     * @param integer Attachment ID.
     */
    public function forceAttachmentRemote($attachmentID)
    {
        if (!eval(Hooks::get('FORCE_ATTACHMENT_REMOTE'))) return;
    }

    /**
     * Deletes an attachment off of whatever services it may be on remotly.
     *
     * @param integer Attachment ID.
     */
    public function forceRemoteDeleteAttachment($attachmentID)
    {
        if (!eval(Hooks::get('FORCE_ATTACHMENT_DELETE'))) return;
    }

    /**
     * Sets the size and md5sum information for an attachment to the specified
     * values.
     *
     * @param integer Attachment ID
     * @param integer File size in KB.
     * @param string File md5sum ('' for none).
     * @return boolean Was the query executed successful?
     */
    public function setSizeMD5($attachmentID, $fileSize, $md5sum)
    {
        $sql = sprintf(
            "UPDATE
                attachment
            SET
                file_size_kb = %s,
                md5_sum = %s
            WHERE
                attachment_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($fileSize),
            $this->_db->makeQueryString($md5sum),
            $this->_db->makeQueryInteger($attachmentID),
            $this->_siteID
        );

        $queryResult = (boolean) $this->_db->query($sql);

        $this->updateSiteSize();

        return $queryResult;
    }

    /**
     * Recalculates the current site's total stored file size.
     *
     * @return void
     */
    public function updateSiteSize()
    {
        $sql = sprintf(
            "UPDATE
                site
            SET
                file_size_kb = (
                    SELECT
                        SUM(file_size_kb)
                    FROM
                        attachment
                    WHERE
                        site_id = %s
                )
            WHERE
                site_id = %s",
            $this->_siteID,
            $this->_siteID
        );

        $this->_db->query($sql);
    }

    /**
     * Reassociates a attachment to a different record.
     *
     * @param integer Attachment ID.
     * @param integer Data Item ID.
     * @param flag Data Item type.
     * @return boolean Was the query executed successfully?
     */
    public function setDataItemID($attachmentID, $dataItemID, $dataItemType)
    {
        $sql = sprintf(
            "UPDATE
                attachment
            SET
                data_item_type = %s,
                data_item_id = %s
            WHERE
                attachment_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($dataItemType),
            $this->_db->makeQueryInteger($dataItemID),
            $this->_db->makeQueryInteger($attachmentID),
            $this->_siteID
        );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Removes an attachment.
     *
     * @param integer Attachment ID.
     * @param boolean Attempt to remove the actual file from the filesystem?
     *                Otherwise, only the database record will be removed.
     * @return boolean True if successful; false otherwise.
     */
    public function delete($attachmentID, $removeFile = true)
    {
        $sql = sprintf(
            "SELECT
                directory_name AS directoryName,
                stored_filename AS storedFileName,
                site_id AS siteId
            FROM
                attachment
            WHERE
                attachment_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($attachmentID),
            $this->_siteID
        );
        $rs = $this->_db->getAssoc($sql);

        if (isset($rs['siteID']))
        {
            $this->forceRemoteDeleteAttachment($attachmentID);
        }

        $sql = sprintf(
            "DELETE FROM
                attachment
            WHERE
                attachment_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($attachmentID),
            $this->_siteID
        );
        $queryResult = $this->_db->query($sql);

        /* Was the delete successful? */
        if (!$queryResult)
        {
            return false;
        }

        /* Should we even try to remove any directories? */
        if (!$removeFile || empty($rs))
        {
            return true;
        }

        /* Sanity check. Don't delete the whole attachments directory. */
        $directoryName = trim($rs['directoryName']);
        if (empty($directoryName) || $directoryName == '.')
        {
            return true;
        }

        /* Remove the directory. */
        $directoryName = 'attachments/' . $directoryName;
        if (is_dir($directoryName))
        {
            FileUtility::recursivelyRemoveDirectory($directoryName);
        }

        return true;
    }

    /**
     * Removes all attachments associated with a Data Item ID from the
     * filesystem.
     *
     * @param flag Data Item type.
     * @param integer Data Item ID.
     * @param string Extra SQL criteria to apply to the SELECT / DELETE query.
     * @return void
     */
    public function deleteAll($dataItemType, $dataItemID, $criteria = '')
    {
        /* Get all attachment IDs for the Data Item. */
        $sql = sprintf(
            "SELECT
                attachment_id AS attachmentID,
                directory_name AS directoryName
            FROM
                attachment
            WHERE
                data_item_id = %s
            AND
                data_item_type = %s
            AND
                site_id = %s
            %s",
            $this->_db->makeQueryInteger($dataItemID),
            $this->_db->makeQueryInteger($dataItemType),
            $this->_siteID,
            $criteria
        );
        $rs = $this->_db->getAllAssoc($sql);

        /* Return if we have no attachments. */
        if (empty($rs))
        {
            return;
        }

        foreach ($rs as $rowNumber => $row)
        {
            /* Sanity check. Don't delete the whole attachments directory. */
            $directoryName = trim($row['directoryName']);
            if (empty($directoryName) || $directoryName = '.')
            {
                continue;
            }

            $directory = 'attachments/' . $directoryName;

            if (is_dir($directory))
            {
                FileUtility::recursivelyRemoveDirectory($directory);
            }
        }

        /* Delete the attachments metadata. */
        $sql = sprintf(
            "DELETE FROM
                attachment
            WHERE
                data_item_id = %s
            AND
                data_item_type = %s
            AND
                site_id = %s
            %s",
            $this->_db->makeQueryInteger($dataItemID),
            $this->_db->makeQueryInteger($dataItemType),
            $this->_siteID,
            $criteria
        );
        $this->_db->query($sql);
    }

    /**
     * Removes all profile image attachments for the specified Data Item ID.
     *
     * @param flag Data Item type.
     * @param integer Data Item ID.
     * @return boolean Was the query executed successfully?
     */
    public function deleteProfileImages($dataItemType, $dataItemID)
    {
        $sql = sprintf(
            "SELECT
                attachment_id AS attachmentID
            FROM
                attachment
            WHERE
                data_item_type = %s
            AND
                data_item_id = %s
            AND
                profile_image = 1
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($dataItemType),
            $this->_db->makeQueryInteger($dataItemID),
            $this->_siteID
        );
        $rs = $this->_db->getAllAssoc($sql);

        foreach ($rs as $data)
        {
            $this->delete($data['attachmentID']);
        }

        return true;
    }

    /**
     * Sets the directory name in which an attachment is contained. Directory
     * names should be relative to attachments/.
     *
     * @param integer Attachment ID.
     * @param string Directory name (under attachments/).
     * @return boolean True if successful; false otherwise.
     */
    public function setDirectoryName($attachmentID, $directoryName)
    {
        $sql = sprintf(
            "UPDATE
                attachment
            SET
                directory_name = %s
            WHERE
                attachment_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryString($directoryName),
            $this->_db->makeQueryInteger($attachmentID),
            $this->_siteID
        );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Returns the attachments list for a data item.
     *
     * @param flag Data Item type.
     * @param integer Data Item ID.
     * @return array Multi-dimensional associative array of attachments data, or
     *               array() if no records are present.
     */
    public function getAll($dataItemType, $dataItemID)
    {
        $sql = sprintf(
            "SELECT
                IF(ISNULL(text), 0, 1) AS hasText,
                attachment_id AS attachmentID,
                data_item_id AS dataItemID,
                data_item_type AS dataItemType,
                title AS title,
                original_filename AS originalFilename,
                stored_filename AS storedFilename,
                content_type AS contentType,
                profile_image AS isProfileImage,
                directory_name AS directoryName,
                md5_sum AS md5sum,
                file_size_kb AS fileSizeKB,
                DATE_FORMAT(date_created, '%%m-%%d-%%y (%%h:%%i:%%s %%p)') AS dateCreated
            FROM
                attachment
            WHERE
                data_item_id = %s
            AND
                data_item_type = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($dataItemID),
            $this->_db->makeQueryInteger($dataItemType),
            $this->_siteID
        );
        $rs = $this->_db->getAllAssoc($sql);

        foreach ($rs as $index => $data)
        {
            $rs[$index]['retrievalURL'] = sprintf(
                '%s?m=attachments&amp;a=getAttachment&amp;id=%s&amp;directoryNameHash=%s',
                CATSUtility::getIndexName(),
                $data['attachmentID'],
                urlencode(md5($data['directoryName']))
            );

            $directoryName = $data['directoryName'];
            $fileName      = $data['storedFilename'];
            $filePath      = sprintf('attachments/%s/%s', $directoryName, $fileName);

            $rs[$index]['retrievalURLLocal'] = $filePath;

            $rs[$index]['retrievalLink'] = self::makeRetrievalLink(
                sprintf(
                    'id=%s&amp;directoryNameHash=%s',
                    $data['attachmentID'],
                    urlencode(md5($data['directoryName']))
                ),
                $rs[$index]['retrievalURL']
            );
        }

        return $rs;
    }

    /**
     * Returns an attachment by attachment ID.
     *
     * @param integer Attachment ID.
     * @return array Associative array of attachments data, or array() if no
     *               records are present.
     */
    public function get($attachmentID, $verifySiteID = true)
    {
        $sql = sprintf(
            "SELECT
                IF(ISNULL(text), 0, 1) AS hasText,
                attachment_id AS attachmentID,
                data_item_id AS dataItemID,
                data_item_type AS dataItemType,
                title AS title,
                original_filename AS originalFilename,
                stored_filename AS storedFilename,
                content_type AS contentType,
                profile_image AS isProfileImage,
                directory_name AS directoryName,
                md5_sum AS md5sum,
                file_size_kb AS fileSizeKB,
                DATE_FORMAT(date_created, '%%m-%%d-%%y (%%h:%%i:%%s %%p)') AS dateCreated
            FROM
                attachment
            WHERE
                attachment_id = %s
            AND
                (site_id = %s || content_type = 'catsbackup' || %s)",
            $this->_db->makeQueryInteger($attachmentID),
            $this->_siteID,
            ($verifySiteID ? 'false' : 'true')
        );
        $rs = $this->_db->getAssoc($sql);

        // FIXME: This doesn't follow the normal paradigm. Normally, we return
        //        array() on failed queries so upper layers know the query failed.
        if (empty($rs))
        {
            /* This url is designed for failure. */
            $rs['retrievalURL'] = 'index.php?m=attachments&amp;a=getAttachment';
        }
        else
        {
            $rs['retrievalURL'] = sprintf(
                '%s?m=attachments&amp;a=getAttachment&amp;id=%s&amp;directoryNameHash=%s',
                CATSUtility::getIndexName(),
                $rs['attachmentID'],
                urlencode(md5($rs['directoryName']))
            );
        }

        return $rs;
    }

    /**
     * Returns any attachments for a Data Item with the specified file size
     * and md5sum. If $text is specified, the text of attachments are matched
     * instead of the raw file contents
     *
     * @param flag Data Item type flag.
     * @param integer Data Item ID.
     * @param integer File size in kB.
     * @param string File md5sum value.
     * @param string Extracted text.
     * @return resultset Found attachments data.
     */
    public function getMatching($dataItemType, $dataItemID, $fileSize, $md5sum,
        $text = '')
    {
        if (empty($text))
        {
            $md5Criterion = sprintf(
                'AND md5_sum = %s AND md5_sum != \'\'',
                $this->_db->makeQueryString($md5sum)
            );
        }
        else
        {
            $md5 = md5(DatabaseSearch::fulltextEncode($text));
            $md5Criterion = sprintf(
                'AND md5_sum_text = %s AND md5_sum_text != \'\'',
                $this->_db->makeQueryString($md5)
            );
        }

        $sql = sprintf(
            "SELECT
                attachment_id AS attachmentID,
                original_filename AS originalFilename,
                directory_name AS directoryName
            FROM
                attachment
            WHERE
                data_item_id = %s
            AND
                data_item_type = %s
            AND
                file_size_kb = %s
            AND
                file_size_kb > 0
            AND
                site_id = %s
            %s",
            $this->_db->makeQueryInteger($dataItemID),
            $this->_db->makeQueryInteger($dataItemType),
            $this->_db->makeQueryInteger($fileSize),
            $this->_siteID,
            $md5Criterion
        );

        return $this->_db->getAllAssoc($sql);
    }


    /**
     * Generates an attachment retrieval link.
     *
     * @param string Attachment GET variables.
     * @param string Attachment URL.
     * @return string HTML link (<a href="...">).
     */
    private static function makeRetrievalLink($getVars, $url)
    {
        // FIXME:  Make attachment download preparer work in IE7.
        //return '<a href="'.$url.'" onclick="return doPrepareAndDownload(\''.$getVars.'\', \''.$url.'\', document.getElementById(\'download'.md5($url).'\'), \''.$_SESSION['CATS']->getCookie().'\');">';
        return '<a href="' . $url . '">';
    }

    /**
     * Returns the MIME type for a file based on its extension.
     *
     * @param string Filename.
     * @return string MIME type, or 'application/octet-stream' if not found.
     */
    public static function fileMimeType($filename)
    {
        $extension = FileUtility::getFileExtension($filename);

        foreach (file('lib/mime.types') as $line)
        {
            $line = str_replace(' ', "\t", $line);
            if (strpos($line, "\t" . $extension) !== false)
            {
                $array = explode("\t", $line);
                return $array[0];
            }
        }

        return 'application/octet-stream';
    }

    /**
     * Get basic summary statistics about bulk attachments.
     *
     */
    public function getBulkAttachmentsInfo()
    {
        $sql = sprintf(
            "SELECT
                COUNT(attachment_id) as numBulkAttachments,
                SUM(file_size_kb) as fileSizeKB,
                MIN(date_created) as firstAttachmentCreatedDate,
                MAX(date_created) as lastAttachmentCreatedDate
            FROM
                attachment
            WHERE
                data_item_type = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger(DATA_ITEM_BULKRESUME),
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Get information about bulk attachments that have been saved to the
     * site attached to this class by siteID.
     *
     */
    public function getBulkAttachments()
    {
        $sql = sprintf(
            "SELECT
                attachment_id as attachmentID,
                site_id as siteID,
                title,
                original_filename as originalFileName,
                stored_filename as storedFileName,
                content_type as contentType,
                resume,
                text,
                date_created as dateCreated,
                date_modified as dateModified,
                directory_name as directoryName,
                file_size_kb as fileSizeKB,
                stored_on_s3 as storedOnS3,
                stored_locally as storedLocally
            FROM
                attachment
            WHERE
                data_item_type = %s
            AND
                site_id = %d",
            $this->_db->makeQueryInteger(DATA_ITEM_BULKRESUME),
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }
}


/**
 *	Attachment Creator
 *	@package    CATS
 *	@subpackage Library
 */
class AttachmentCreator
{
    private $_isError = false;
    private $_error = '';
    private $_isTextExtractionError = false;
    private $_textExtractionError = '';
    private $_extractedText = '';
    private $_siteID = -1;
    private $_duplicatesOccurred = false;
    private $_newFilePath = '';
    private $_containingDirectory = '';
    private $_attachmentID = -1;


    // FIXME: Document me.
    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
    }


    /**
     * Returns true if an error occurred during attachment creation. Errors
     * generally mean that the attachment was not created, although this should
     * be verified by checking the return value of the create*() method.
     *
     * @return boolean Did errors occur during the last attachment creation
     *                 attempt?
     */
    public function isError()
    {
        return $this->_isError;
    }

    /**
     * Returns the error message from the last attachment creation attempt. Use
     * isError() to see if an error occurred. '' will be returned if no errors
     * occurred.
     *
     * @return string Error message, or '' if none.
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * Returns true if an error occurred during text extraction. Text
     * extraction errors do not prevent the attachment from being created.
     *
     * @return boolean Did text extraction errors occur during the last
     *                 attachment creation attempt?
     */
    public function isTextExtractionError()
    {
        return $this->_isTextExtractionError;
    }

    /**
     * Returns the text extraction error message from the last attachment
     * creation attempt. Use isTextExtractionError() to see if an error
     * occurred. '' will be returned if no errors occurred.
     *
     * @return string Text extraction error message, or '' if none.
     */
    public function getTextExtractionError()
    {
        return $this->_textExtractionError;
    }

    /**
     * Returns the extracted text from the last attachment creation, or '' if
     * text wasn't extracted.
     *
     * @return string Extracted text from last attachment creation.
     */
    public function getExtractedText()
    {
        return $this->_extractedText;
    }

    /**
     * Returns true if an identical attachemt already existed during the last
     * attachment creation attempt. The create*() method will have returned
     * false as well if this has happened.
     *
     * @return boolean Did duplicates occur during the last attachment creation
     *                 attempt?
     */
    public function duplicatesOccurred()
    {
        return $this->_duplicatesOccurred;
    }

    /**
     * Returns the extracted text from the last attachment creation, or '' if
     * text wasn't extracted.
     *
     * @return string Extracted text from last attachment creation.
     */
    public function getNewFilePath()
    {
        return $this->_newFilePath;
    }

    /**
     * Returns the containing directory for the new attachment.
     *
     * @return string directory
     */
    public function getContainingDirectory()
    {
        return $this->_containingDirectory;
    }

    /**
     * Returns the new attachment ID.
     *
     * @return integer attachment ID
     */
    public function getAttachmentID()
    {
        return $this->_attachmentID;
    }

    /**
     * Creates an attachment to the specified data item from an HTTP POST file
     * upload. This will also pass the attachment along for text extraction and
     * indexing if requested.
     *
     * @param flag Data Item type flag.
     * @param integer Data Item ID.
     * @param string Name of HTTP POST file field.
     * @param boolean Is this a profile image attachment?
     * @param boolean Attempt to extract, store, and index the attachment's
     *                text?
     * @return boolean Was the attachment created successfully?
     */
    public function createFromUpload($dataItemType, $dataItemID, $fileField,
        $isProfileImage, $extractText)
    {
        /* Get file upload metadata. */
        $originalFilename = $_FILES[$fileField]['name'];
        $tempFilename     = $_FILES[$fileField]['tmp_name'];
        $contentType      = $_FILES[$fileField]['type'];
        $fileSize         = $_FILES[$fileField]['size'];
        $uploadError      = $_FILES[$fileField]['error'];

        /* Recover from magic quotes. Note that tmp_name doesn't appear to
         * get escaped, and stripslashes() on it breaks on Windows. - Will
         */
        if (get_magic_quotes_gpc())
        {
            $originalFilename = stripslashes($originalFilename);
            $contentType      = stripslashes($contentType);
        }

        /* Did a file upload error occur? */
        if ($uploadError != UPLOAD_ERR_OK)
        {
            $this->_isError = true;
            $this->_error = FileUtility::getErrorMessage($uploadError);
            return false;
        }

        /* This usually indicates an error. */
        if ($fileSize <= 0)
        {
            $this->_isError = true;
            $this->_error = 'File size is less than 1 byte.';
            return false;
        }

        return $this->createGeneric(
            $dataItemType, $dataItemID, $isProfileImage, $extractText, false,
            $originalFilename, $tempFilename, $contentType, false, true
        );
    }

    /**
     * Creates an attachment to the specified data item from a file that
     * currently exists on the system where CATS is located. This will
     * also pass the attachment along for text extraction and indexing if
     * requested. Note that the file will be moved out of its current location
     * and into the attachments/ directory.
     *
     * @param flag Data Item type flag.
     * @param integer Data Item ID.
     * @param string The temporary location where the file is currently stored
     *               on the system where CATS is located.
     * @param string Attachment title, or boolean false to create a title
     *               automatically from the attachment's filename.
     * @param string MIME content type (or '' if unknown).
     * @param boolean Attempt to extract, store, and index the attachment's
     *                text?
     * @param boolean Does this file actually exist? If true, the file will be
     *                moved to the created attachment directory automatically.
     *                If false, the caller is responsible.
     * @return boolean Was the attachment created successfully?
     */
    public function createFromFile($dataItemType, $dataItemID, $filePath,
        $title, $contentType, $extractText, $fileExists)
    {
        $filePathParts = split('/', $filePath);
        $originalFilename = end($filePathParts);

        return $this->createGeneric(
            $dataItemType, $dataItemID, false, $extractText, $title,
            $originalFilename, $filePath, $contentType, false,
            $fileExists
        );
    }

    /**
     * Creates an attachment to the specified data item from text. This will
     * also pass the attachment along for text extraction and indexing if
     * requested.
     *
     * @param flag Data Item type flag.
     * @param integer Data Item ID.
     * @param string File's text contents.
     * @param string Filename for to-be-created file.
     * @param boolean Attempt to extract, store, and index the attachment's
     *                text?
     * @return boolean Was the attachment created successfully?
     */
    public function createFromText($dataItemType, $dataItemID, $text,
        $fileName, $extractText)
    {
        return $this->createGeneric(
            $dataItemType, $dataItemID, false, $extractText, false,
            $fileName, false, 'text/plain', $text, false
        );
    }

    /**
     * Creates an attachment to the specified data item. This will also pass
     * the attachment along for text extraction and indexing if requested. This
     * method supports the above createFrom*() methods; however it may also be
     * called directly.
     *
     * @param flag Data Item type flag.
     * @param integer Data Item ID.
     * @param boolean Is this a profile image attachment?
     * @param boolean Attempt to extract, store, and index the attachment's
     *                text?
     * @param string Attachment title, or boolean false to create a title
     *               automatically from the attachment's filename.
     * @param string The filename an attachment originally before any renaming.
     * @param string The temporary location where the file is currently stored
     *               on the system where CATS is located.
     * @param string MIME content type (or '' if unknown).
     * @param string File's contents if a file is being created from text /
     *               contents. Specify false if not creating a file by its
     *               contents.
     * @param boolean Does this file actually exist? If true, the file will be
     *                moved to the created attachment directory automatically.
     *                If false, the caller is responsible. This has no effect
     *                if $fileContents is not false.
     * @return boolean Was the attachment created successfully?
     */
    public function createGeneric($dataItemType, $dataItemID, $isProfileImage,
        $extractText, $title, $originalFilename, $tempFilename,
        $contentType, $fileContents, $fileExists)
    {
        /* Make a 'safe' filename with only standard ASCII characters. */
        $storedFilename = FileUtility::makeSafeFilename($originalFilename);

        /* Create an attachment title. */
        $attachmentTitle = FileUtility::getFileWithoutExtension(
            $originalFilename
        );

        /* Make attachment searchable. */
        if (!$extractText)
        {
            $extractedText = '';
        }
        else
        {
            $documentToText = new DocumentToText();
            $documentType = $documentToText->getDocumentType(
                $storedFilename, $contentType
            );

            /* If we're creating a file from text contents, we can skip
             * extracting because we already know the text contents.
             */
            if ($fileContents !== false && $documentType == DOCUMENT_TYPE_TEXT)
            {
                $extractedText = $fileContents;
            }
            else if ($fileContents !== false)
            {
                /* If it's not text and we are creating a file from contents,
                 * don't try to extract text.
                 */
                $extractedText = '';
            }
            else if (!$fileExists)
            {
                /* Can't extract text from a file that doesn't exist. */
                $extractedText = '';
            }
            else
            {
                $documentToText->convert($tempFilename, $documentType);

                if ($documentToText->isError())
                {
                    $this->_isTextExtractionError = true;
                    $this->_textExtractionError = $documentToText->getError();
                    $extractedText = '';
                }
                else
                {
                    $extractedText = $documentToText->getString();
                }

                /* If we are adding a bulk resume, and parsing fails, consider it
                 * a fatal error.
                 */
                if ($dataItemType == DATA_ITEM_BULKRESUME &&
                    $this->_isTextExtractionError)
                {
                    $this->_isError = true;
                    $this->_error = $this->_textExtractionError;
                    return false;
                }
            }
        }

        $attachments = new Attachments($this->_siteID);

        /* We can only check for duplicates right now if the file actually
         * exists. We'll do it again later below.
         */
        if ($fileExists && !$fileContents)
        {
            /* We store file size in KB, rounded to nearest KB. */
            $fileSize = round(@filesize($tempFilename) / 1024);

            /* The md5sum is stored for duplicate checking. */
            $md5sum = @md5_file($tempFilename);

            /* Check for duplicates. */
            $duplicates = $attachments->getMatching(
                $dataItemType, $dataItemID, $fileSize, $md5sum, $extractedText
            );

            /* Duplicate attachments are never added, but this is not a fatal
             * error. We will set a property to notify the caller that a
             * duplicate occurred.
             */
            if (!empty($duplicates))
            {
                $this->_duplicatesOccurred = true;

                if (file_exists($tempFilename))
                {
                    unlink($tempFilename);
                }

                return false;
            }
        }
        else
        {
            $fileSize = 0;
            $md5sum = '';
        }

        /* Add the attachment record. At this point, there is no actual
         * associated directory / full file path.
         */
        $attachmentID = $attachments->add(
            $dataItemType, $dataItemID, $attachmentTitle, $originalFilename,
            $storedFilename, $contentType, $extractText, $extractedText,
            $isProfileImage, '', $fileSize, $md5sum
        );

        /* Were we successful? */
        if (!$attachmentID)
        {
            $this->_isError = true;
            $this->_error = 'Error adding attachment to the database.';
            @unlink($tempFilename);
            return false;
        }

        /* Store the extracted text and attachment ID in properties for later
         * access.
         */
        $this->_extractedText = $extractedText;
        $this->_attachmentID  = $attachmentID;

        /* Create the attachment directory. */
        $uniqueDirectory = $this->_createDirectory(
            $attachmentID, $storedFilename
        );
        if (!$uniqueDirectory)
        {
            $attachments->delete($attachmentID, false);
            return false;
        }

        /* Create the full path name to the file. */
        $newFileFullPath = $uniqueDirectory . $storedFilename;

        /* Are we creating a new file from file contents, or are we moving a
         * temporary file?
         */
        if ($fileContents !== false)
        {
            $status = @file_put_contents($newFileFullPath, $fileContents);
            if (!$status)
            {
                $this->_isError = true;
                $this->_error = sprintf(
                    'Cannot create file %s.', $newFileFullPath
                );
                $attachments->delete($attachmentID, false);
                @unlink($uniqueDirectory);
                return false;
            }

            /* We store file size in KB, rounded to nearest KB. */
            $fileSize = round(@filesize($newFileFullPath) / 1024);

            /* The md5sum is stored for duplicate checking. */
            $md5sum = @md5_file($newFileFullPath);

            /* Check for duplicates. */
            $duplicates = $attachments->getMatching(
                $dataItemType, $dataItemID, $fileSize, $md5sum, $extractedText
            );

            /* Duplicate attachments are never added, but this is not a fatal
             * error. We will set a property to notify the caller that a
             * duplicate occurred.
             */
            if (!empty($duplicates))
            {
                $this->_duplicatesOccurred = true;
                $attachments->delete($attachmentID, false);
                @unlink($newFileFullPath);
                @unlink($uniqueDirectory);
                return false;
            }
        }
        else if ($fileExists)
        {
            /* Copy the temp file to the new path. */
            if (!@copy($tempFilename, $newFileFullPath))
            {
                $this->_isError = true;
                $this->_error = sprintf(
                    'Cannot copy temporary file %s to %s.',
                    $tempFilename,
                    $newFileFullPath
                );
                $attachments->delete($attachmentID, false);
                @unlink($newFileFullPath);
                @unlink($uniqueDirectory);
                return false;
            }

            /* Try to remove the temp file; if it fails it doesn't matter. */
            @unlink($tempFilename);
        }

        /* Store path to the file (inside the attachments directory) in this
         * object.
         */
        $this->_newFilePath = $newFileFullPath;
        $this->_containingDirectory = $uniqueDirectory;

        /* Update the database with the new directory name. */
        $attachments->setDirectoryName(
            $attachmentID,
            str_replace('./attachments/', '', $uniqueDirectory)
        );

        if (!eval(Hooks::get('CREATE_ATTACHMENT_FINISHED'))) return;

        return true;
    }

    // FIXME: Document me.
    private function _createDirectory($attachmentID, $storedFilename)
    {
        /* Make sure attachments exists. */
        if (!is_dir('./attachments'))
        {
            /* No? Create it. */
            @mkdir('./attachments', 0777);
            @touch('./attachments/index.php');
        }

        /* Attachments are first grouped under a directory for each site ID. */
        $siteDirectory = './attachments/site_' . $this->_siteID;

        /* Make sure the site directory exists. */
        if (!is_dir($siteDirectory))
        {
            /* No? Create it. */
            @mkdir($siteDirectory, 0777);
        }

        /* Attachments are then grouped in groups of 1000 attachment IDs. */
        $IDGroupDirectory = sprintf(
            '%s/%sxxx',
            $siteDirectory,
            ((int) ($attachmentID / 1000))
        );

        /* Make sure the attachment ID group directory exists. */
        if (!is_dir($IDGroupDirectory))
        {
            /* No? Create it. */
            @mkdir($IDGroupDirectory, 0777);
        }

        /* If we had to create directories above, make sure that the creation
         * was successful.
         */
        if (!is_dir($IDGroupDirectory))
        {
            $this->_isError = true;
            $this->_error = sprintf(
                'Cannot create directory %s, and it does not already exist.',
                $IDGroupDirectory
            );
            return false;
        }

        /* Prevent webserver listing of new directories. */
        if (!file_exists($siteDirectory . '/index.php'))
        {
            @file_put_contents($siteDirectory . '/index.php', "\n");
        }
        if (!file_exists($IDGroupDirectory . '/index.php'))
        {
            @file_put_contents($IDGroupDirectory . '/index.php', "\n");
        }

        /* Get a unique directroy name in which to store the attachment. */
        $uniqueDirectory = sprintf(
            '%s/%s/',
            $IDGroupDirectory,
            FileUtility::getUniqueDirectory($IDGroupDirectory, $storedFilename)
        );

        /* Attempt to create a directory for this attachment ID. */
        if (!is_dir($uniqueDirectory))
        {
            @mkdir($uniqueDirectory, 0777);
        }

        /* Was creation successful? */
        if (!is_dir($uniqueDirectory))
        {
            $this->_isError = true;
            $this->_error = sprintf(
                'Cannot create directory %s, and it does not already exist.',
                $uniqueDirectory
            );
            return false;
        }

        /* We probably don't have permission to chmod, but try anyway. */
        @chmod($uniqueDirectory, 0777);

        return $uniqueDirectory;
    }

    // FIXME: Document me.
    private function _sanityCheck()
    {
        /* Does the attachments directory even exist? */
        if (!is_dir('./attachments/'))
        {
            $this->_isError = true;
            $this->_error = 'Directory \'./attachments/\' does not '
                . 'exist. CATS is not configured correctly.';
            return false;
        }

        /* Make a blind attempt to recover from invalid permissions. */
        @chmod('./attachments/', 0777);
    }
}

?>
