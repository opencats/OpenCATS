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
 *	Hash Utility Library
 *	@package    CATS
 *	@subpackage Library
 */
class HashUtility
{
    const CRC32_CRCPOLY = 0xEDB88320;
    const CRC32_CRCINV = 0x5B358FD3;
    const CRC32_INITXOR = 0xFFFFFFFF;
    const CRC32_FINALXOR = 0xFFFFFFFF;
    const CRC32_READ_BLOCKSIZE = 1048576;
    const INT_MAX = 0x7fffffff;

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
