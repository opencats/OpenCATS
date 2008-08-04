<?php
/**
 * CATS
 * JavaScript Compression Library
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
 * @version    $Id: JavaScriptCompressor.php 3587 2007-11-13 03:55:57Z will $
 */
 
include_once('./lib/StringUtility.php');

/**
 *	JavaScript Compression Library
 *	@package    CATS
 *	@subpackage Library
 */
class JavaScriptCompressor
{
    /**
     * Compresses the contents of a JavaScript file, removing whitespace, extra
     * newlines, and extra whitespace.
     *
     * @param string Path to JavaScript file to compress.
     * @return string Compressed JavaScript code.
     */
    public function compressFile($filename)
    {
        $string = @file_get_contents($filename);
        if ($string === false)
        {
            return false;
        }
        
        return $this->compress($string);
    }
    
    /**
     * Compresses a string of JavaScript code, removing whitespace, extra
     * newlines, and extra whitespace.
     *
     * @param string Uncompressed JavaScript source code.
     * @return string Compressed JavaScript code.
     */
    public function compressString($string)
    {
        return $this->compress($string);
    }
    
    /**
     * Compresses a string of JavaScript code, removing whitespace, extra
     * newlines, and extra whitespace.
     *
     * @param string Uncompressed JavaScript source code.
     * @return string Compressed JavaScript code.
     */
    protected function compress($string)
    {
        /* Remove leading and trailing whitespace from each line (note, the
         * ungreedy modifier before the '$' in the below regular expression
         * should theoretically not be needed, but without it, it seems to
         * eat newlines.
         */
        $string = preg_replace('/^\s*(.*?)\s*?$/m', '\1', $string);

        /* Remove C / C++ comments.
         *
         * \x27 is a single quote, \x5c is a backslash.
         *
         * This is based on code from Jeffrey Friedl's Mastering Regular
         * Expressions, 3rd Edition (O'Reilly Media, Inc.).
         *
         * If you're thinking about rewriting this, you'll probably break it.
         * It's very fragile ;).
         */
        $string = preg_replace(
            '@([^"\x27/]+|"[^\x5c"]*(?:\x5c.[^\x5c"]*)*"[^"\x27/]*|\x27[^\x27\x5c]*(?:\x5c.[^\x27\x5c]*)*\x27[^"\x27/]*)|/\*[^*]*\*+(?:[^/*][^*]*\*+)*/|//[^\n]*@',
            '\1',
            $string
        );

        /* Remove any blank lines from the string. */
        $string = StringUtility::removeEmptyLines($string);

        /* "Safe" newline removal. This should work with just about any code
         * that a browser's JavaScript implementation can understand.
         * Significant newlines will never be removed.
         */
        $string = preg_replace('/;\n/', ';', $string);
        $string = preg_replace('/\{\n/', '{', $string);
        $string = preg_replace('/\)\n{/', ') {', $string);
        $string = preg_replace('/\}\nelse/', '} else', $string);
        $string = preg_replace('/else\n\{/', 'else {', $string);

        return $string;
    }
}

?>
