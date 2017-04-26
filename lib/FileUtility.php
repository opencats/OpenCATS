<?php
/**
 * CATS
 * File Utility Library
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
 * @version    $Id: FileUtility.php 3752 2007-11-28 23:39:06Z andrew $
 */

define('DOCUMENT_TYPE_UNKNOWN', 0);
define('DOCUMENT_TYPE_PDF',     100);
define('DOCUMENT_TYPE_DOC',     200);
define('DOCUMENT_TYPE_RTF',     300);
define('DOCUMENT_TYPE_DOCX',    400);
define('DOCUMENT_TYPE_HTML',    500);
define('DOCUMENT_TYPE_ODT',     600);
define('DOCUMENT_TYPE_TEXT',    700);

/**
 *	File Utility Library
 *	@package    CATS
 *	@subpackage Library
 */
class FileUtility
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}


    /**
     * Returns a document type based on its file extension and content type.
     *
     * @param string Document file name with extension.
     * @param string MIME content type.
     * @return flag Document type flag.
     */
    public static function getDocumentType($filename, $contentType = false)
    {
        $fileExtension = self::getFileExtension($filename);

        if ($contentType === 'text/plain' || $fileExtension == 'txt')
        {
            return DOCUMENT_TYPE_TEXT;
        }

        if ($contentType == 'application/rtf' || $contentType == 'text/rtf' ||
            $contentType == 'text/richtext' || $fileExtension == 'rtf')
        {
            return DOCUMENT_TYPE_RTF;
        }

        if ($contentType == 'application/msword' || $fileExtension == 'doc')
        {
            return DOCUMENT_TYPE_DOC;
        }

        if ($contentType == 'application/vnd.ms-word.document.12' ||
            $fileExtension == 'docx')
        {
            return DOCUMENT_TYPE_DOCX;
        }

        if ($contentType == 'application/pdf' || $fileExtension == 'pdf')
        {
            return DOCUMENT_TYPE_PDF;
        }

        if ($contentType === 'text/html' || $fileExtension == 'html' ||
            $fileExtension == 'htm')
        {
            return DOCUMENT_TYPE_HTML;
        }

        if ($contentType === 'application/vnd.oasis.opendocument.text' ||
            $contentType === 'application/x-vnd.oasis.opendocument.text' ||
            $fileExtension == 'odt')
        {
            return DOCUMENT_TYPE_ODT;
        }

        return DOCUMENT_TYPE_UNKNOWN;
    }

    /**
     * Recursively removes a directory tree.
     *
     * @param directory name
     * @return true on success; false otherwise
     */
    public static function recursivelyRemoveDirectory($directoryName)
    {
        $exceptions = array('.', '..');

        $directory = @opendir($directoryName);
        if (!$directory)
        {
            return false;
        }

        while (($child = readdir($directory)) !== false)
        {
            if (in_array($child, $exceptions))
            {
                continue;
            }

            $object = str_replace('//', '/', $directoryName . '/' . $child);

            if (is_dir($object))
            {
                recursivelyRemoveDirectory($object);
            }
            else if (is_file($object))
            {
                @unlink($object);
            }
        }

        closedir($directory);

        if (@rmdir($directoryName))
        {
            return true;
        }

        return false;
    }

    /**
     * Creates a filename safe to store on the local filesystem. Non-ASCII
     * characters will be stripped, directory names will be removed, and
     * filenames with "executable" extensions will have ".txt" appended.
     *
     * @param flag Data Item type flag.
     * @param integer Data Item ID.
     * @param string Name of HTTP POST file field.
     * @param boolean Is this a profile image attachment?
     * @param boolean Attempt to extract, store, and index the attachment's
     *                text?
     * @return string Safe filename.
     */
    public static function makeSafeFilename($filename)
    {
        /* Strip out *nix directories. */
        $filenameParts = explode('/', $filename);
        $filename = end($filenameParts);

        /* Strip out Windows directories. */
        $filenameParts = explode('\\', $filename);
        $filename = end($filenameParts);

        /* Strip out non-ASCII characters. */
        for ($i = 0; $i < strlen($filename); $i++)
        {
            if (ord($filename[$i]) >= 128 || ord($filename[$i]) < 32)
            {
                $filename[$i] = '_';
            }
        }

        /* Is the file extension safe? */
        $fileExtension = self::getFileExtension($filename);
        if (in_array($fileExtension, $GLOBALS['badFileExtensions']))
        {
            $filename .= '.txt';
        }

        return $filename;
    }

    /**
     * Returns a unused filename in CATS_TEMP_DIR.
     * // FIXME: Merge me with makeRandomFilename().
     *
     * @return string filename
     */
    public static function makeRandomTemporaryFilePath()
    {
        /* Even though the possibility of generating a filename that
         * already exists is small, we need to handle it just in case.
         */
        do
        {
            $filePath = CATS_TEMP_DIR . '/' . FileUtility::makeRandomFilename();
        }
        while (file_exists($filePath));

        return $filePath;
    }

    /**
     * Gets a random unique directory name for storing an attachment. Note that
     * this does NOT actually create the directory.
     *
     * @param string The parent directory in which the unique directory name
     *               will be created.
     * @param string Extra data to include in the MD5 hash.
     * @return string Ranom unique 32-character directory name.
     */
    public static function getUniqueDirectory($basePath, $extraData = '')
    {
        if (!empty($basePath) && substr($basePath, -1, 1) != '/')
        {
            $basePath .= '/';
        }

        /* Even though the possibility of generating a directory name that
         * already exists is small, we need to handle it just in case.
         */
        do
        {
            $md5 = md5(rand() . time() . $extraData);
        }
        while (file_exists($basePath . $md5));

        return $md5;
    }

    /**
     * Returns a random filename.
     * // FIXME: Merge with makeRandomTemporaryFilePath().
     *
     * @return string filename
     */
    public static function makeRandomFilename($padding = '')
    {
        return md5($padding . time() . mt_rand()) . mt_rand(0, 9);
    }

    /**
     * Returns true if directory is writable.
     *
     * @param string directory to test writability
     * @return boolean directory writable
     */
    public static function isDirectoryWritable($directory)
    {
        if (substr($directory, -1, 1) != '/')
        {
            $directory .= '/';
        }

        /* Create temp file name. */
        $path = $directory . self::makeRandomFilename() . '.tmp';

        $file = @fopen($path, 'w+');
        if (!$file)
        {
            return false;
        }

        fclose($file);

        /* Try to delete the temp file. */
        @unlink($path);

        return true;
    }

    /**
     * Returns the UNIX octal permissions string for a file (i.e., 0777).
     *
     * @param string path
     * @return string octal permissions string
     */
    public static function getOctalPermissions($path)
    {
        return substr(sprintf('%o', fileperms($path)), -4);
    }

    /**
     * Returns the proper title for a filename by removing the extension.
     *
     * @param string filename
     * @return string title
     */
    public static function getFileWithoutExtension($filename,
        $baseNameOnly = false)
    {
        if ($baseNameOnly)
        {
            $filename = basename($filename);
        }

        return substr($filename, 0, strrpos($filename, '.'));
    }

    /**
     * Returns the file extension from a filename (in lowercase).
     *
     * @param string filename
     * @return string extension
     */
    public static function getFileExtension($filename)
    {
        return strtolower(substr($filename, strrpos($filename, '.') + 1));
    }

    /**
     * Returns the attachment icon filename for a given file
     *
     * @param string filename
     * @return string attachment icon filename
     */
    public static function getAttachmentIcon($filename)
    {
        $fileExtension = strtolower(self::getFileExtension($filename));

        //FIXME: need to handle more extension types
        switch ($fileExtension)
        {
            case 'doc':
                return 'images/file/doc.gif';
                break;

            case 'xls':
                return 'images/file/xls.gif';
                break;

            case 'ppt':
                return 'images/file/ppt.gif';
                break;

            case 'pdf':
                return 'images/file/pdf.gif';
                break;

            case 'txt':
                return 'images/file/txt.gif';
                break;

            case 'jpg':
                return 'images/file/jpg.gif';
                break;

            case 'gif':
                return 'images/file/gif.gif';
                break;

            case 'zip':
                return 'images/file/zip.gif';
                break;

            default:
                return 'images/attachment.gif';
                break;
        }
    }

    /**
     * Returns an error message for a given error code.
     *
     * @param integer error code (check constants.php)
     * @return string error message
     */
    public static function getErrorMessage($errorCode)
    {
        switch ($errorCode)
        {
            case UPLOAD_ERR_INI_SIZE:
                return 'File size is greater than system-wide size limit.';
                break;

            case UPLOAD_ERR_FORM_SIZE:
                return 'File size is greater than form size limit.';
                break;

            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded. Try again.';
                break;

            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded. Try again.';
                break;

            case UPLOAD_ERR_NO_TMP_DIR:
                return 'No temporary directory exists. PHP is most likely '
                    . 'configured incorrectly.';
                break;

            case UPLOAD_ERR_CANT_WRITE:
                return 'Cannot write to directory.';
                break;

            default:
                return 'An unknown error has occurred.';
                break;
        }
    }

    /**
     * Returns a human-readable string representation of a file size, for
     * example '2.3 MB'.
     *
     * @param integer file size in bytes
     * @return string human-readable file size
     */
    public static function sizeToHuman($size, $round = 2, $skipUnits = 0)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        $unitIndex = 0;

        /* Keep dividing the file size by 1024 as long as the number is >0.
         * If we are skipping units, it's okay to have fractional sizes, so we
         * keep dividing until we're supposed to stop skipping units.
         */
        while ((int) ($size / 1024) > 0 || $skipUnits > 0)
        {
            $size /= 1024;
            ++$unitIndex;
            --$skipUnits;
        }

        /* Do rounding if necessary. */
        if ($round !== false)
        {
            $size = round($size, $round);
        }

        /* Return the unit description along with the unit we found. */
        return $size . ' ' . $units[$unitIndex];
    }

    /**
     * Get the path relative to the root directory to which all uploaded files
     * should be moved (for the current user at the current site). For example,
     * "upload/200". Does not return trailing forward slash. Returned directory
     * will be created if it doesn't exist with full-write permissions set.
     *
     * @param integer ID of the site the data is restricted to
     * @param string a subdirectory of their upload folder (if necessary)
     * @return string Upload directory path (relative to root directory).
     */
    public static function getUploadPath($siteID, $subDirectory = '')
    {
        $uploadPath = sprintf('upload%s',
            !empty($subDirectory) ? '/' . $subDirectory : ''
        );

        if (!eval(Hooks::get('FILE_UTILITY_UPLOAD_PATH'))) return;

        // Create the directory (recursively) if it doesn't exist
        if (!@file_exists($uploadPath))
        {
            if (@mkdir($uploadPath, 0777, true) === false)
            {
                return false;
            }
        }

        // Make sure it's writeable
        if (@is_writable($uploadPath) === false)
        {
            @chmod($uploadPath, 0777);
            if (@is_writable($uploadPath) === false) return false;
        }

        return $uploadPath;
    }

    /**
     * Checks whether a given file is safe to view, edit, delete, that it exists, and
     * that it is contained in a path restricted by FileUtility::getUploadPath()
     *
     * @param integer ID of the site
     * @param string subdirectory (if necessary)
     * @param string name of the file to be checked
     * @return boolean true or false
     */
    public static function isUploadFileSafe($siteID, $subDirectory, $fileName)
    {
        if (($uploadPath = FileUtility::getUploadPath($siteID, $subDirectory)) === false)
        {
            // site has no upload path, by definition it is not safe
            return false;
        }

        // Prevent uprooting
        $fileName = str_replace('..', '', $fileName);

        if (strcasecmp(substr($fileName, 0, strlen($uploadPath)), $uploadPath))
        {
            // Base of the filename doesn't match the upload path, it is not safe
            return false;
        }

        if (!@file_exists($fileName) || !@is_writable($fileName))
        {
            return false;
        }

        return true;
    }

    /**
     * This function will translate a file name into the relative path
     * to access the file (if it exists and it is considered safe).
     *
     * @param integer ID of the site containing the file
     * @param string Optional sub-directory, use blank string for root
     * @param string Full filesystem path to the file or boolean false
     */
    public static function getUploadFilePath($siteID, $subDirectory, $uploadFileName)
    {
        if (($uploadPath = self::getUploadPath($siteID, $subDirectory)) === false)
        {
            return false;
        }

        $filePath = sprintf('%s/%s', $uploadPath, $uploadFileName);

        if (!self::isUploadFileSafe($siteID, $subDirectory, $filePath))
        {
            return false;
        }

        return $filePath;
    }

    /**
     * Store the contents of a file upload in the site's upload directory with an
     * optional sub-directory and return the name of the file (not including path).
     *
     * @param integer ID of the site containing the file
     * @param string Optional sub-directory to place the file
     * @param string Index of the $_FILES array (name from the <input> tag)
     * @return string Complete name of the file (not including path)
     */
    public static function getUploadFileFromPost($siteID, $subDirectory, $id)
    {
        if (isset($_FILES[$id]))
        {
            if (!@file_exists($_FILES[$id]['tmp_name']))
            {
                // File was removed, accessed from another window, or no longer exists
                return false;
            }

            if (!eval(Hooks::get('FILE_UTILITY_SPACE_CHECK'))) return;

            $uploadPath = FileUtility::getUploadPath($siteID, $subDirectory);
            $newFileName = $_FILES[$id]['name'];

            // Could just while(file_exists) it, but I'm paranoid of infinate loops
            // Shouldn't have 1000 files of the same name anyway
            for ($i = 0; @file_exists($uploadPath . '/' . $newFileName) && $i < 1000; $i++)
            {
                $mp = explode('.', $newFileName);
                $fileNameBase = implode('.', array_slice($mp, 0, count($mp)-1));
                $fileNameExt = $mp[count($mp)-1];

                if (preg_match('/(.*)_Copy([0-9]{1,3})$/', $fileNameBase, $matches))
                {
                    // Copy already appending, increase the #
                    $fileNameBase = sprintf('%s_Copy%d', $matches[1], intval($matches[2]) + 1);
                }
                else
                {
                    $fileNameBase .= '_Copy1';
                }

                $newFileName = $fileNameBase . '.' . $fileNameExt;
            }

            if (@move_uploaded_file($_FILES[$id]['tmp_name'], $uploadPath . '/' . $newFileName) &&
                @chmod($uploadPath . '/' . $newFileName, 0777))
            {
                return $newFileName;
            }
        }

        return false;
    }
}

?>
