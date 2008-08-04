<?php
/**
 * CATS
 * File Import Utility Library
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
 * @version    $Id: Import.php 3587 2007-11-13 03:55:57Z will $
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

?>
