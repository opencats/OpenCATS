<?php
/**
 * CATS
 * SOAP Resume Parser Interface Library
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
 * @version    $Id: ParseUtility.php 3720 2007-11-27 21:06:13Z andrew $
 */

// FIXME: Document me! Explain how external parsers can integrate.

define('PARSE_CODE_SUCCESS', 'ok');
define('PARSE_CODE_FAILED',  'failed');
define('PARSE_CODE_ERROR',   'error');
define('PARSE_CODE_NOAUTH',  'noauth');

/**
 *  SOAP Resume Parser Interface Library
 *  @package    CATS
 *  @subpackage Library
 */
class ParseUtility
{
    private $_wsdl;
    private $_client;


    public function __construct()
    {
        $this->_wsdl = 'wsdl/parse.wsdl';
        $this->_client = null;
    }


    public function startClient()
    {
        $this->_client = new SoapClient($this->_wsdl);
    }

    /**
     * Feeds a file through the resume parsing service via the XML-based SOAP protocol.
     *
     * @param string $name Name of the file to be parsed (i.e.: MyResume.doc)
     * @param int32 $size Size (in bytes) of the file to be parsed.
     * @param string $mimeType mime_content_type() response on the file to be parsed.
     * @param string $contents get_file_contents() response on the file to be parsed (file contents).
     * @return response object {
     *      RESPONSE MESSAGE (string value: ok, failed, error, noauth)
     *      firstName
     *      lastName
     *      address
     *      city
     *      state
     *      zip
     *      email
     *      phone
     *      skills
     *      education
     *      experience
     * }
     */
    public function documentParse($name, $size, $mimeType, $contents)
    {
        if (!$this->_client) $this->startClient();
        if (!defined('CATS_TEST_MODE') || !CATS_TEST_MODE)
        {
            try
            {
                $res = $this->_client->DocumentParse(LICENSE_KEY, $name, $size, $mimeType, self::cleanText($contents));
            }
            catch (SoapFault $exception)
            {
                return false;
            }
        }
        else
        {
            $res = $this->_client->DocumentParse(LICENSE_KEY, $name, $size, $mimeType, self::cleanText($contents));
        }

        switch($res->message)
        {
            case PARSE_CODE_SUCCESS:
                break;
            case PARSE_CODE_ERROR:
            case PARSE_CODE_FAILED:
                return false;
            case PARSE_CODE_NOAUTH:
                return false;
        }

        $ret = array(
            'first_name' => $res->firstName,
            'last_name' => $res->lastName,
            'us_address' => $res->address,
            'city' => $res->city,
            'state' => $res->state,
            'zip_code' => $res->zip,
            'email_address' => $res->email,
            'phone_number' => $res->phone,
            'skills' => $res->skills,
            'education' => $res->education,
            'experience' => $res->experience
        );

        return $ret;
    }

    public function status($key)
    {
        if (!CATSUtility::isSOAPEnabled()) return false;
        $client = new SoapClient('wsdl/status.wsdl');
        if (!defined('CATS_TEST_MODE') || !CATS_TEST_MODE)
        {
            try
            {
                $res = $client->Status($key);
            }
            catch (SoapFault $exception)
            {
                return false;
            }
        }
        else
        {
            $res = $client->Status($key);
        }

        switch($res->message)
        {
            case PARSE_CODE_SUCCESS:
                break;
            case PARSE_CODE_ERROR:
            case PARSE_CODE_FAILED:
                return false;
            case PARSE_CODE_NOAUTH:
                return false;
        }

        $ret = array(
            'version' => $res->version,
            'name' => $res->name,
            'lastUse' => $res->lastUse,
            'parseUsed' => $res->parseUsed,
            'parseLimit' => $res->parseLimit,
            'parseLimitReset' => $res->parseLimitReset
        );

        return $ret;
    }


    // Destroy unicode before it contaminates our soap, what's next? our children and our candy?
    public static function cleanText($txt)
    {
        for ($i=0; $i<strlen($txt); $i++)
        {
            $ch = ord($txt[$i]);

            // ASCII control characters (character code 0-31)
            // PHP 5 SOAP Libraries partially supported
            if ($ch == 9 || $ch == 10 || $ch == 13) continue;

            // ASCII Printable characters (character code 32-127)
            else if ($ch >= 32 && $ch <= 127) continue;

            // Extended ASCII codes (character code 128-255)
            // These are NOT SUPPORTED by the PHP 5 SOAP Libraries

            // Anything not supported:
            else
            {
                // Replace unrecognizable characters with a space
                $txt[$i] = ' ';
            }
        }
        return $txt;
    }

    // Get/sets
    public function setWSDL($wsdl) { return ($this->_wsdl = $wsdl); }
    public function getWSDL() { return $this->_wsdl; }
}

?>
