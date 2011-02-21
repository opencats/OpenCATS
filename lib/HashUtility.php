<?php
/**
 * CATS
 * Hash Utility Library
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
 * @version    $Id: HashUtility.php 3807 2007-12-05 01:47:41Z will $
 */

/**
 *	Hash Utility Library
 *	@package    CATS
 *	@subpackage Library
 */
class HashUtility
{
    const HASH_OFFSET = 2590992;
    const CRC32_CRCPOLY = 0xEDB88320;
    const CRC32_CRCINV = 0x5B358FD3;
    const CRC32_INITXOR = 0xFFFFFFFF;
    const CRC32_FINALXOR = 0xFFFFFFFF;
    const CRC32_READ_BLOCKSIZE = 1048576;
    const INT_MAX = 0x7fffffff;

    /**
     * Encodes a 32-bit integer into a 14-byte base-35 case-insensitive string.
     * This is a good method to use to mask an integer value to prevent data
     * phishing.
     *
     * Note that this does not handle values over (2,147,483,647 - HASH_OFFSET)
     * or under (-2,147,483,647 + HASH_OFFSET).
     *
     * @param integer 32-bit interger to hash.
     * @return string 14-byte base-35 case-insensitive encoded string.
     */
    public static function hashInt32($integer)
    {
        $bytes = pack('N1', ($integer + self::HASH_OFFSET));

        $scramble = rand(0,6);
        $hash = '';
        for ($i = 0; $i < 4; $i++)
        {
            $ch = ord($bytes[$i]);

            for ($bit=0; $bit<7; $bit++)
            {
                if ($ch & (1 << $bit)) $ch = $ch ^ (1 << $bit);
                else $ch = $ch | (1 << $bit);
            }

            /* Scramble */
            if ($ch & (1 << $scramble))
            {
                $ch = $ch ^ (1 << $scramble);
            }
            else
            {
                $ch = $ch | (1 << $scramble);
            }
            $tmp = base_convert($ch, 10, 34);
            if (strlen($tmp) < 2)
            {
                $tmp = 'Z' . $tmp;
            }
            $hash .= $tmp;
        }
        $tmp = chr(ord($scramble) + 63);
        $hash .= $tmp;

        $md5 = substr(md5($bytes), 0, 5);
        for ($i = 0; $i < 5; $i++)
        {
            $ch = ord($md5[$i]);
            
            /* Scramble */
            if ($ch & (1 << $scramble))
            {
                $ch = $ch ^ (1 << $scramble);
            }
            else
            {
                $ch = $ch | (1 << $scramble);
            }
            $tmp = base_convert($ch, 10, 34);
            if (strlen($tmp) < 2)
            {
                $tmp = 'Z' . $tmp;
            }
            $hash .= $tmp;
        }

        return $hash;
    }

    /**
     * Decodes a 14-byte case-insensative int32 hash string created by
     * hashInt32. Basic md5 hashing prevents tampering.
     *
     * Note that this does not handle values over (2,147,483,647 - HASH_OFFSET)
     * or under (-2,147,483,647 + HASH_OFFSET).
     *
     * @param string 14-byte base-35 case-insensitive encoded string.
     * @return integer Decoded integer or false if tampering/data errors
     *                 have occurred.
     */
    public static function unhashInt32($hash)
    {
        $hash = strtolower($hash);

        $scramble = intval(chr(ord(substr($hash, 8, 1)) - 63));
        $md5hash = substr($hash, 9, 10);
        $bytes = '';

        $md5 = '';
        for ($i = 0; $i < 10; $i += 2)
        {
            $pc = substr($md5hash, $i, 2);
            if ($pc[0] == 'z') $pc = substr($pc, 1);
            $ch = base_convert($pc, 34, 10);
            
            /* Scramble */
            if ($ch & (1 << $scramble))
            {
                $ch = $ch ^ (1 << $scramble);
            }
            else
            {
                $ch = $ch | (1 << $scramble);
            }
            $md5 .= chr($ch);
        }

        for ($i = 0; $i < 8; $i += 2)
        {
            $pc = substr($hash, $i, 2);
            if ($pc[0] == 'z') $pc = substr($pc, 1);
            $ch = base_convert($pc, 34, 10);

            for ($bit = 0; $bit < 7; ++$bit)
            {
                if ($ch & (1 << $bit)) $ch = $ch ^ (1 << $bit);
                else $ch = $ch | (1 << $bit);
            }

            /* Scramble */
            if ($ch & (1 << $scramble))
            {
                $ch = $ch ^ (1 << $scramble);
            }
            else
            {
                $ch = $ch | (1 << $scramble);
            }
            $bytes .= chr($ch);
        }

        if (strcasecmp(substr(md5($bytes), 0, 5), $md5))
        {
            return false;
        }

        list(, $integer) = unpack('N1', $bytes);
        return $integer - self::HASH_OFFSET;
    }
    

    /**
     * A reasonably fast pure-PHP CRC algorithm that doesn't require the
     * entire file to be read into memory first. This is based on the following
     * publication:
     *
     * Reversing CRC – Theory and Practice.
     * HU Berlin Public Report
     * SAR-PR-2006-05
     * May 2006
     * Authors:
     * Martin Stigge, Henryk Plötz, Wolf Müller, Jens-Peter Redlich
     *
     * and on comments on PHP.net's online documentation.
     *
     * This consistently generates the same checksums as zlib's crc32(),
     * however zlib is still slightly faster. This needs to be implemented in a
     * pure "tables" manner, but that can be put off.
     *
     * This probably doesn't like files larger than 2GB.
     */
    public function crc32File($filename, $forcePHPImplementation = false)
    {
        /* PHP's hash_file() is faster if available (PHP 5.2.1+). */
        if (function_exists('hash_file') && !$forcePHPImplementation && false)
        {            
            $rawHash = @hash_file('crc32b', $filename, true);
            if ($rawHash === false)
            {
                return false;
            }
            
            // FIXME: Should this be in machine byte order, or always little endian?
            list(,$hash) = unpack('V', $rawHash);
            return $hash;
        }

        $bytesLeftToRead = @filesize($filename);
        
        $fileHandle = @fopen($filename,'rb');
        if ($fileHandle === false)
        {
            return false;
        }

        $crc32 = 0;
        $crc32String = '';
        while ($bytesLeftToRead > 0)
        {
            $maxBytesToRead = min($bytesLeftToRead, self::CRC32_READ_BLOCKSIZE);
            
            $buffer = @fread($fileHandle, $maxBytesToRead);
            if ($buffer === false)
            {
                return false;
            }
            
            $crc32 = crc32($crc32String . $buffer);
            
            $bytesLeftToRead -= $maxBytesToRead;
            if ($bytesLeftToRead)
            {
                $crc32String = self::crc32Reverse($crc32);
            }
        }
        
        @fclose($fileHandle);

        return $crc32;
   }
    
    private static function crc32Reverse($crc)
    {
        $crc ^= self::CRC32_INITXOR;

        $newCRC = 0;
        for ($i = 0; $i < 32; ++$i)
        {
            if (($newCRC & 1) != 0)
            {
                $newCRC = self::CRC32_CRCPOLY ^ (($newCRC >> 1) & self::INT_MAX);
            }
            else
            {
                $newCRC = (($newCRC >> 1) & self::INT_MAX);
            }
    
            if (($crc & 1) != 0)
            {
                $newCRC ^= self::CRC32_CRCINV;
            }
            
            $crc = (($crc >> 1) & self::INT_MAX);
        }

        $newCRC ^= self::CRC32_FINALXOR;
        
        $buffer  = chr($newCRC & 0xFF);
        $buffer .= chr($newCRC >> 8 & 0xFF);
        $buffer .= chr($newCRC >> 16 & 0xFF);
        $buffer .= chr($newCRC >> 24 & 0xFF);
        
        return $buffer;
    }
}

?>
