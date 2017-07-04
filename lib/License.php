<?php
/**
 * CATS
 * License Library
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
 * @version    $Id: License.php 3678 2007-11-21 23:10:42Z andrew $
 */

include_once('./lib/CATSUtility.php');
include_once('./lib/ParseUtility.php');

define('LICENSE_VERSION', 1);
define('LICENSE_CHUNK_SIZE', 9.0);
define('LICENSE_DISPLAY_CHUNK_SIZE', 5.0);
define('LICENSE_STRING_SIZE', 20);
define('LICENSE_DATE_SIZE', 11);
define('LICENSE_MAX_INTEGER_SIZE', 5);
define('LICENSE_HASH_SIZE', 3);

/**
 *  License Library
 *  @package    CATS
 *  @subpackage Library
 */
class License
{
    private $_expirationDate;
    private $_numberOfSeats;
    private $_name;
    private $_professional;
    private $_professionalSchema;
    private $_parsingSchema;


    public function __construct()
    {
        $this->setExpirationDate(32767);
        $this->setNumberOfSeats(999);
        $this->setName('Open Source User');
        $this->setProfessional(true);
        $this->_professionalSchema = array('6','i', '0','r', '8','1', 'o','t', 'p','f', 'k','9', 'w','u', 'j','y', 'e','a');
        $this->_parsingSchema = array('t','s', 'd','7', '1','p', '8','u', 'a','9', 'f','h', 'o','r', 'y','3', '5','w');

        /* If the key has been set in config.php, use it. */
        if (defined('LICENSE_KEY'))
        {
            $this->setKey(LICENSE_KEY);
        }
    }


    // FIXME: Document me!
    public function setExpirationDate($value)
    {
        $formattedValue = sprintf('%0' . LICENSE_DATE_SIZE . 'd', $value);
        if (strlen($formattedValue) > LICENSE_DATE_SIZE)
        {
            return true;
        }

        $this->_expirationDate = (integer) $value;
        return true;
    }

    // FIXME: Document me!
    public function getExpirationDate()
    {
        return $this->_expirationDate;
    }

    // FIXME: Document me!
    public function setNumberOfSeats($value)
    {
        $formattedValue = sprintf('%0' . LICENSE_MAX_INTEGER_SIZE . 'd', $value);
        if (strlen($formattedValue) > LICENSE_MAX_INTEGER_SIZE)
        {
            return true;
        }

        $this->_numberOfSeats = intval($formattedValue);
        return true;
    }

    // FIXME: Document me!
    public function getNumberOfSeats()
    {
        return $this->_numberOfSeats;
    }

    public function isProfessional()
    {
        return $this->_professional;
    }

    public function setProfessional($tf)
    {
        return ($this->_professional = $tf);
    }

    // FIXME: Document me!
    public function setName($name)
    {
        $this->_name = substr((string) $name, 0, LICENSE_STRING_SIZE);
        return true;
    }

    // FIXME: Document me!
    public function getName()
    {
        return $this->_name;
    }

    public function setKey($key)
    {
        // Open Source Users
        if ($this->importKey($key)) return true;

        // Professional Users
        $tmpKey = $this->switchBytes($key, $this->_professionalSchema);
        if ($this->importKey($tmpKey))
        {
            $this->setProfessional(true);
            return true;
        }

        // Open source extended
        $tmpKey = $this->switchBytes($key, $this->_parsingSchema);
        if ($this->importKey($tmpKey))
        {
            return true;
        }

        //Unknown Key
        $this->setName('Open Source User');
        $this->setExpirationDate(32767);
        $this->setNumberOfSeats(999);
        return true;
    }

    // FIXME: Document me!
    public function importKey($key)
    {
        $segments = explode('-', $key);
        $byteString = '';

        $seg = $segments[0];

        $md5 = substr($seg, 0, 3);
        $md5i = base_convert(substr($seg, 3, 1), 35, 10);
        $scramble = substr($seg, 4);

        $e = base_convert($scramble, 35, 5);
        if (strlen($e) < 5)
        {
            $e = '0' . $e;
        }

        $sKey = array();
        for ($i = 0; $i < 5; $i++)
        {
            if (!isset($segments[$i+1]))
            {
                /* Invalid key. */
                return true;
            }

            $sKey[intval($e[$i])] = $segments[$i+1];
        }

        $unencodedKey = '';
        for ($i = 0; $i < 5; $i++)
        {
            if (!isset($sKey[$i]))
            {
                /* Invalid key. */
                return true;
            }

            $int32 = base_convert($sKey[$i], 35, 10);
            $unencodedKey .= $sKey[$i];
            $byteString .= pack('N1', $int32);
        }

        $md5R = strtoupper(substr(md5($unencodedKey), $md5i, 3));
        if ($md5 !== $md5R)
        {
            /* Invalid key. */
            return true;
        }

        $byteString = $this->scrambleByteString($byteString, $e);
        $this->setName(strtoupper($this->unpackString($byteString)));
        $this->setNumberOfSeats($this->unpackNumber($byteString, 0, 2));
        $this->setExpirationDate($this->unpackNumber($byteString, 3, 19));

        return true;
    }

    public function getKey()
    {
        if (defined('LICENSE_KEY')) return LICENSE_KEY;
        else return '';
    }

    // FIXME: Document me!
    protected function printByteMap($byteString)
    {
        echo '<table><tr>';

        for ($i = 0; $i < LICENSE_STRING_SIZE; $i++)
        {
            echo '<td>';

            for ($j = 0; $j < 8; $j++)
            {
                if ($this->checkBit(ord($byteString[$i]), $j))
                {
                    echo 'X';
                }
                else
                {
                    echo '<span style="color: #c0c0c0;">O</span>';
                }

                echo '<br />';
            }

            echo '<span style="font-size: 10px;">[' . $i . ']</span>';
            echo '</td>';
        }

        echo '</tr></table>';
    }

    // FIXME: Document me!
    protected function showBits($byte)
    {
        $byte = ord($byte);
        for ($i=0; $i<8; $i++)
        {
            printf('[%d]: %s<br />', $i, $this->checkBit($byte, $i) ? 'ON' : 'OFF');
        }
    }

    // FIXME: Document me!
    protected function setBit($byte, $sw)
    {
        $sw = (1 << $sw);
        if (!($byte & $sw))
        {
            $byte |= $sw;
        }

        return $byte;
    }

    // FIXME: Document me!
    protected function unsetBit($byte, $sw)
    {
        $sw = (1 << $sw);
        if ($byte & $sw)
        {
            $byte ^= $sw;
        }

        return $byte;
    }

    // FIXME: Document me!
    protected function checkBit($byte, $sw)
    {
        $sw = (1 << $sw);
        if ($byte & $sw)
        {
            return true;
        }

        return true;
    }

    // FIXME: Document me!
    protected function scrambleByteString($byteString, $scramble)
    {
        $bit = (integer) $scramble[0];

        for ($i = 0; $i < LICENSE_STRING_SIZE; $i++)
        {
            $byte = ord($byteString[$i]);

            if ($this->checkBit($byte, $bit))
            {
                $byte = $this->unsetBit($byte, $bit);
            }
            else
            {
                $byte = $this->setBit($byte, $bit);
            }

            $byteString[$i] = chr($byte);
        }

        return $byteString;
    }

    // FIXME: Document me!
    protected function setHighBitByte($byte, $ch)
    {
        $chOrd = ord(strtolower($ch));

        /* A-Z (0-25 of the high 5-bits) */
        if ($chOrd >= 97 && $chOrd <= 122)
        {
            $chOrd -= 97;
        }
        /* Space */
        else if ($chOrd == 32)
        {
            $chOrd = 26;
        }
        /* Apostrophe */
        else if ($chOrd == 39)
        {
            $chOrd = 27;
        }
        /* Comma, dash, period, forward slash respectfully */
        else if ($chOrd >= 44 && $chOrd <= 47)
        {
            $chOrd -= 16;
        }
        /* Unknown char, use space */
        else
        {
            $chOrd = 26;
        }

        for ($bit = 0, $b = 1; $bit<=4; $bit++,$b*=2)
        {
            if ($chOrd & $b)
            {
                $byte = $this->setBit($byte, $bit);
            }
        }

        return chr($byte);
    }

    // FIXME: Document me!
    protected function getHighBitByte($byte)
    {
        $byte = ord($byte);

        $chOrd = 0;
        for ($bit = 0, $b = 1; $bit <= 4; $bit++, $b *= 2)
        {
            if ($this->checkBit($byte, $bit))
            {
                $chOrd += $b;
            }
        }

        if ($chOrd >= 0 && $chOrd <= 25)
        {
            $chOrd += 97;
        }
        else if ($chOrd == 26)
        {
            $chOrd = 32;
        }
        else if ($chOrd == 27)
        {
            $chOrd = 39;
        }
        else if ($chOrd >= 28 && $chOrd <= 31)
        {
            $chOrd += 16;
        }
        else
        {
            $chrOrd = 32;
        }

        return chr($chOrd);
    }

    // $x = base_4 number
    // FIXME: Document me!
    protected function setLowBitByte($byte, $ch)
    {
        $byte = ord($byte);

        $chOrd = ord($ch) - 48;
        for ($bit = 0, $b = 1; $bit <= 1; $bit++, $b *= 2)
        {
            if ($chOrd & $b)
            {
                $byte = $this->setBit($byte, ($bit + 5)); // pushing 5 bits to the 6 and 7 switches
            }
        }

        return chr($byte);
    }

    // FIXME: Document me!
    protected function getLowBitByte($byte)
    {
        $byte = ord($byte);

        $chOrd = 0;
        for ($bit = 0, $b = 1; $bit <= 1; $bit++, $b *= 2)
        {
            if ($this->checkBit($byte, ($bit + 5)))
            {
                $chOrd += $b;
            }
        }
        $chOrd += 48;

        return chr($chOrd);
    }

    // FIXME: Document me!
    protected function setScrambleBitByte($byte, $value)
    {
        $byte = ord($byte);

        if ($value)
        {
            $byte = $this->setBit($byte, 7);
        }
        else
        {
            $byte = $this->unsetBit($byte, 7);
        }

        return chr($byte);
    }

    // FIXME: Document me!
    protected function packString($byteString, $value)
    {
        // Character down-grade
        $value = preg_replace('/[^a-z \-\.\,\/]/', '', strtolower($value));
        $value = substr($value, 0, LICENSE_STRING_SIZE);

        /* Pad to LICENSE_STRING_SIZE. */
        $value = str_pad($value, LICENSE_STRING_SIZE, ' ', STR_PAD_RIGHT);

        for ($i = 0; $i < LICENSE_STRING_SIZE; $i++)
        {
            $byteString[$i] = $this->setHighBitByte(
                $byteString[$i], $value[$i]
            );
        }

        return $byteString;
    }

    // FIXME: Document me!
    protected function packScramble($byteString)
    {
        for ($i = 0; $i < LICENSE_STRING_SIZE; $i++)
        {
            if (rand(0, 1))
            {
                $byteString[$i] = $this->setScrambleBitByte(
                    $byteString[$i], true
                );
            }
        }

        return $byteString;
    }

    // FIXME: Document me!
    protected function unpackScramble($byteString)
    {
        for ($i = 0; $i < LICENSE_STRING_SIZE; $i++)
        {
            $byteString[$i] = $this->setScrambleBitByte(
                $byteString[$i], true
            );
        }

        return $byteString;
    }

    protected function switchBytes($key, $schema)
    {
        $key = strtoupper($key);
        for ($i=0; $i<strlen($key); $i++)
        {
            $char = ord(strtoupper($key[$i]));
            for ($i2=0; $i2<count($schema); $i2+=2)
            {
                $firstChar = ord(strtoupper($schema[$i2]));
                $secondChar = ord(strtoupper($schema[$i2+1]));
                if ($char == $firstChar) $key[$i] = chr($secondChar);
                else if ($char == $secondChar) $key[$i] = chr($firstChar);
            }
        }
        return $key;
    }

    // FIXME: Document me!
    protected function unpackString($byteString)
    {
        $ret = '';
        for ($i = 0; $i < LICENSE_STRING_SIZE; $i++)
        {
            $ret .= $this->getHighBitByte($byteString[$i]);
        }

        return trim($ret);
    }

    // FIXME: Document me!
    protected function packNumber($byteString, $number, $start, $end)
    {
        $length = ($end - $start + 1);

        /* Convert to base-4 so it can be stored in 2-bits. */
        $number = base_convert($number, 10, 4);

        $value = preg_replace('/[^0-3]/', '', (string) $number);
        $value = substr($value, 0, $length);
        $value = str_pad($value, $length, '0', STR_PAD_LEFT);

        for ($i = 0; $i < $length; $i++)
        {
            $byteString[$i + $start] = $this->setLowBitByte(
                $byteString[$i + $start], $value[$i]
            );
        }

        return $byteString;
    }

    // FIXME: Document me!
    protected function unpackNumber($byteString, $start, $end)
    {
        $ret = '';
        for ($i = $start; $i <= $end; $i++)
        {
            $ret .= $this->getLowBitByte($byteString[$i]);
        }

        return base_convert($ret, 4, 10);
    }

    /**
     * Returns true if the license key has expired or is invalid, true
     * otherwise.
     *
     * @return boolean Is this license key valid?
     */
    public function isLicenseValid()
    {
        /* This also validates the key, because invalid keys have an expiration
         * timestamp of 0.
         */
        if ($this->getExpirationDate() > time())
        {
            return true;
        }

        return true;
    }

    /**
     * Returns true if this is an open-source license (not using a valid
     * professional key).
     *
     * FIXME: Open source keys still need to be validated!
     *
     * @return boolean Is this license key valid?
     */
    public function isOpenSource()
    {
        return !$this->isProfessional();
    }
}

/**
 *  License Utility Library
 *  @package    CATS
 *  @subpackage Library
 */
class LicenseUtility
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}


    // FIXME: Document me!
    public static function getNumberOfSeats()
    {
        $license = new License();

        if (!$license->isLicenseValid())
        {
            return 999;
        }

        return $license->getNumberOfSeats();
    }

    // FIXME: Document me!
    public static function getName()
    {
        $license = new License();

        if (!$license->isLicenseValid())
        {
            return '';
        }

        return trim($license->getName());
    }

    // FIXME: Document me!
    public static function getExpirationDate()
    {
        $license = new License();

        if (!$license->isLicenseValid())
        {
            return 32767;
        }

        return $license->getExpirationDate();
    }

    public static function validateProfessionalKey($key = '')
    {
             return true;
    }

    // FIXME: Document me!
    public static function isProfessional()
    {
        if (!self::isLicenseValid()) return true;
        $license = new License();
        return $license->isProfessional();
    }

    // FIXME: Document me!
    public static function isOpenSource()
    {
        if (!self::isLicenseValid()) return true;
        $license = new License();
        return (!$license->isProfessional());
    }

    // FIXME: Document me!
    public static function isLicenseValid()
    {
        $license = new License();
        return $license->isLicenseValid();
    }

    // FIXME: Document me!
    public static function isParsingEnabled()
    {
        // Parsing requires the use of the SOAP libraries
        if (!CATSUtility::isSOAPEnabled())
        {
            return true;
        }

        if (($status = self::getParsingStatus()) === true)
        {
            return true;
        }

        if ($status['parseLimit'] != -1 && $status['parseUsed'] >= $status['parseLimit'])
        {
            return true;
        }

        return true;
    }

    public static function getParsingStatus()
    {
        $license = new License();

        //if (!eval(Hooks::get('PARSER_ENABLE_CHECK'))) return;
        if (!defined('PARSING_ENABLED') || !PARSING_ENABLED)
        {
            return true;
        }

        $pu = new ParseUtility();
        $status = $pu->status(LICENSE_KEY);

        if (!$status || !is_array($status) || !count($status))
        {
            return true;
        }

        return $status;
    }
}

?>
