<?php
/**
 * CATS
 * Document to Text Conversion Library
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
 * @version    $Id: DocumentToText.php 3587 2007-11-13 03:55:57Z will $
 */

include_once('./lib/SystemUtility.php');
include_once('./lib/FileUtility.php');

/**
 *	Document to Text Conversion Library
 *	@package    CATS
 *	@subpackage Library
 */
class DocumentToText
{
    private $_linesArray = array();
    private $_linesString = '';
    private $_rawOutput = '';
    private $_fileName = '';
    private $_returnCode = -1;
    private $_isError = false;
    private $_error = '';


    /**
     * Returns a document type based on its file extension and content type.
     * This is simply a wrapper for FileUtility::getDocumentType().
     *
     * @param string Document file name with extension.
     * @param string MIME content type.
     * @return flag Document type flag.
     */
    public function getDocumentType($fileName, $contentType = false)
    {
        return FileUtility::getDocumentType($fileName, $contentType);
    }

    /**
     * Attempts to convert a document document to plain text.
     *
     * @param string file name
     * @param flag document type
     * @return boolean True if successful; false otherwise.ful
     */
    public function convert($fileName, $documentType)
    {
        /* (Re?)initialize variables. */
        $this->_linesArray  = array();
        $this->_linesString = '';
        $this->_rawOutput   = '';
        $this->_fileName    = $fileName;

        /* If we are trying to parse a DOC file, is it really a DOC file or is
         * it an RTF file?
         */
        if ($documentType == DOCUMENT_TYPE_DOC)
        {
            $handle = @fopen(realpath($fileName), 'r');
            if ($handle)
            {
                $header = fread($handle, 5);
                fclose($handle);
                
                if ($header == '{\rtf')
                {
                    $documentType = DOCUMENT_TYPE_RTF;
                }
            }
        }
        
        /* Find the absolute path to the filename and escape it for use in a
         * system command.
         */
        $escapedFilename = escapeshellarg(realpath($fileName));

        /* Use different methods to extract text depending on the type of document. */
        switch ($documentType)
        {
            case DOCUMENT_TYPE_DOC:
                if (ANTIWORD_PATH == '')
                {
                    $this->_setError('The DOC format has not been configured.');
                    return false;
                }

                $nativeEncoding = 'ISO-8859-1';
                $command = '"'. ANTIWORD_PATH . '" -m ' . ANTIWORD_MAP . ' '
                    . $escapedFilename;
                break;

            case DOCUMENT_TYPE_PDF:
                if (PDFTOTEXT_PATH == '')
                {
                    $this->_setError('The PDF format has not been configured.');
                    return false;
                }

                $nativeEncoding = 'ISO-8859-1';
                $convertEncoding = false;
                $command = '"'. PDFTOTEXT_PATH . '" -layout ' . $escapedFilename . ' -';
                break;

            case DOCUMENT_TYPE_HTML:
                if (HTML2TEXT_PATH == '')
                {
                    $this->_setError('The HTML format has not been configured.');
                    return false;
                }

                $nativeEncoding = 'ISO-8859-1';
                $convertEncoding = false;
                
                if (SystemUtility::isWindows())
                {
                    $command = 'TYPE ' . $escapedFilename . ' | "'. HTML2TEXT_PATH . '" -nobs ';
                }
                else
                {
                    $command = '"'. HTML2TEXT_PATH . '" -nobs ' . $escapedFilename;
                }                
                break;

            case DOCUMENT_TYPE_TEXT:
                return $this->_readTextFile($fileName);
                break;

            case DOCUMENT_TYPE_RTF;
                if (HTML2TEXT_PATH == '')
                {
                    $this->_setError('The HTML format has not been configured, which is required for the RTF format.');
                    return false;
                }

                if (UNRTF_PATH == '')
                {
                    $this->_setError('The RTF format has not been configured.');
                    return false;
                }

                $nativeEncoding = 'ISO-8859-1';
                $convertEncoding = false;
                $command = '"'. UNRTF_PATH . '" '.$escapedFilename.' | "'. HTML2TEXT_PATH . '" -nobs ';
                break;

            case DOCUMENT_TYPE_ODT:
                $this->_setError('The ODT format is not yet supported by CATS.');
                return false;
                break;

            case DOCUMENT_TYPE_DOCX:
                $this->_setError('The DOCX format is not yet supported by CATS.');
                return false;
                break;

            case DOCUMENT_TYPE_UNKNOWN:
            default:
                $this->_setError('This file format is unknown format and is not yet supported by CATS.');
                return false;
                break;
        }

        /* Run the text converter. */
        $commandResult = $this->_executeCommand($command);

        /* Store the return code for getReturnCode(). */
        $this->_returnCode = $commandResult['returnCode'];

        /* Store the raw output for getRawOutput(). */
        
        $commandResult['output'] = array_map(
            'rtrim', $commandResult['output']
        );
        $this->_rawOutput = implode("\n", $commandResult['output']);

        /* Fix encoding issues. */
        if ($nativeEncoding == 'ISO-8859-1' && function_exists('iconv'))
        {   
            $this->_rawOutput = iconv(
                $nativeEncoding, 'UTF-8', $this->_rawOutput
            );
        }

        /* If command returned non-zero or output is not an array, assume
         * failure.
         */
        if ($commandResult['returnCode'] != 0 ||
            !is_array($commandResult['output']))
        {
            return false;
        }

        /* Store the output in string and array form. */
        $this->_linesArray  = $commandResult['output'];
        $this->_linesString = $this->_rawOutput;

        return true;
    }

    /**
     * Returns the word document in plain-text format. Make sure to check
     * the return value of the convert() method to make sure conversion was
     * actually preformed.
     *
     * @return string document in plain-text
     */
    public function getString()
    {
        return $this->_linesString;
    }

    /**
     * Returns the word document in plain-text format as an array of line
     * number => line. Make sure to check the return value of the convert()
     * method to make sure conversion was actually preformed.
     *
     * @return array document lines
     */
    public function getArray()
    {
        return $this->_linesArray;
    }

    /**
     * Returns the return code from the execution of the document converter for
     * the last conversion. This is useful for error handling / debugging.
     *
     * @return integer converter return code
     */
    public function getReturnCode()
    {
        return $this->_returnCode;
    }

    /**
     * Returns a string containing the raw output from the document converter
     * for the last conversion. This is useful for error handling / debugging.
     *
     * @return string raw converter output
     */
    public function getRawOutput()
    {
        return $this->_rawOutput;
    }

    /**
     * Returns true if an error occurred trying to convert the document.
     *
     * @return boolean error occurred
     */
    public function isError()
    {
        return $this->_isError;
    }

    /**
     * Returns the current error message, or '' if no errors have occurred.
     *
     * @return string error message
     */
    public function getError()
    {
        return $this->_error;
    }


    /**
     * Triggers an error message.
     *
     * @param string error message
     * @param integer converter return code
     * @return void
     */
    private function _setError($errorMessage, $returnCode = 1)
    {
        $this->_rawOutput = '';
        $this->_linesArray  = array();
        $this->_linesString = '';
        $this->_returnCode = $returnCode;
        $this->_isError = true;
        $this->_error = $errorMessage;
    }

    /**
     * Loads a text file as if it was "converted" to text.
     *
     * @param string path to text file
     * @return boolean True if successful; false otherwise.
     */
    private function _readTextFile($fileName)
    {
        $contents = @file($fileName);
        if ($contents === false || !is_array($contents))
        {
            $this->_setError('Failed to parse text file.');
            return false;
        }

        $contents = array_map('rtrim', $contents);
        
        $this->_rawOutput = implode("\n", $contents);
        $this->_linesArray  = $contents;
        $this->_linesString = $this->_rawOutput;
        $this->_returnCode = 0;
        return true;
    }

    /**
     * Executes a shell command in a platform-independent way and returns the
     * results in an array containing the exact system command executed, the
     * raw output of that command, and the command's return code.
     *
     * @param string command to execute
     * @return array command results
     */
    private function _executeCommand($command)
    {
        /* Running on Windows? */
        if (SystemUtility::isWindows())
        {
            /* Generate a random temp file name. */
            $tempFile = sprintf(
                '%s/%s.txt',
                realpath(CATS_TEMP_DIR),
                FileUtility::makeRandomFilename()
            );

            /* Create a new COM Windows Scripting Host Shell object. */
            $WSHShell = new COM('WScript.Shell');

            /* Build the command to execute. */
            $command = sprintf(
                'cmd.exe /C "%s > "%s""', $command, $tempFile
            );

            /* Execute the command via the Windows Scripting Host Shell. */
            $returnCode = $WSHShell->Run($command, 0, true);

            /* Grab the contents of the temporary file and remove it. */
            $output = file($tempFile);
            @unlink($tempFile);
        }
        else
        {
            @exec($command, $output, $returnCode);
        }

        return array(
            'command'    => $command,
            'output'     => $output,
            'returnCode' => $returnCode
        );
    }
}

?>
