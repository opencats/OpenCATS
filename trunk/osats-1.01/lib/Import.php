<?php
/**
 * OSATS
 */

/**
 *	File Import Utility Library
 *	@package    CATS
 *	@subpackage Library
 */
class ImportUtility
{
    // FIXME: Document me.
    public static function getDirectoryFiles($dirName)
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

            if (is_dir($fileName))
            {
                $mp = self::getDirectoryFiles($fileName);
                $tmp = array_merge($files, $mp);
                $files = $tmp;
            }
            else
            {
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
