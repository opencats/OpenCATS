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

/**
 *	File Import Utility Library
 *	@package    CATS
 *	@subpackage Library
 */
class ImportUtility
{
    public static function getLegacyBulkResumeDirectory()
    {
        clearstatcache(true);

        // A linked staging directory could point at another upload workflow.
        if (is_link('upload/bulkresumes'))
        {
            return false;
        }

        return FileUtility::getUploadPath('bulkresumes');
    }

    public static function resolveMassImportPath($uploadDir, $fileName)
    {
        clearstatcache(true);
        $root = realpath($uploadDir);
        $path = realpath($fileName);

        if ($root === false || $path === false ||
            !str_starts_with($path, rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR) ||
            !is_file($path))
        {
            return false;
        }

        return $path;
    }

    // FIXME: Document me.
    public static function getDirectoryFiles($dirName, $skipLinks = false)
    {
        $files = array();
        
        $handle = opendir($dirName);
        if (!$handle)
        {
            return -1;
        }
        
        while (false !== ($file = readdir($handle)))
        {
            if ($file == '.' || $file == '..') continue;
            $fileName = $dirName . '/' . $file;

            if ($skipLinks && is_link($fileName)) continue;

            if (is_dir($fileName))
            {
                $subdirectoryFiles = self::getDirectoryFiles($fileName, $skipLinks);
                if (is_array($subdirectoryFiles))
                {
                    $files = array_merge($files, $subdirectoryFiles);
                }
            }
            else
            {
                if ($skipLinks && !is_file($fileName)) continue;
                if (!($info = stat($fileName))) continue;
                $fileSize = filesize($fileName);

                if ($fileSize <= 50) continue;
                if (!($fileExt = strchr($file, '.'))) continue;
                $fileExt = strtolower(substr($fileExt, 1));

                // Make sure it's a document type we can get text from
                if (($docType = FileUtility::getDocumentType($file)) == DOCUMENT_TYPE_UNKNOWN) continue;

                $fileMp = array(
                    'realName' => $file,
                    'name' => $fileName,
                    'size' => $fileSize,
                    'ext' => $fileExt,
                    'type' => $docType,
                    'cTime' => $info['ctime'],
                    'parsed' => false
                );

                $files[] = $fileMp;
            }
        }

        return $files;
    }
}

?>
