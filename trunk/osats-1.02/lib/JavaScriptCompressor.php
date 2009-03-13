<?php
/**
 * OSATS
 */

include_once('./lib/StringUtility.php');

/**
 *	JavaScript Compression Library
 *	@package    OSATS
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