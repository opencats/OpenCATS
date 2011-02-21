<?php
/**
 * CATS
 * File Compression Library
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
 * http://www.zend.com/codex.php?id=535&single=1
 * http://www.zend.com/codex.php?id=470&single=1
 *
 * Official ZIP file format: http://www.pkware.com/appnote.txt
 *
 * Note that code in this file should pay special attention to memory
 * usage optimization techniques.
 */

/**
 *	File Compression Library
 *	@package    CATS
 *	@subpackage Library
 */
 
include_once('./lib/HashUtility.php');

define('START_FILE_RECORD',             0x04034b50);
define('START_DATA_DESCRIPTOR',         0x08074b50);
define('START_CENTRAL_DIRECTORY_ENTRY', 0x02014b50);
define('END_CENTRAL_DIRECTORY',         0x06054b50);

/**
 *	Zip File Creator
 *	@package    CATS
 *	@subpackage Library
 */
class ZipFileCreator
{
    /* Full path (absolute or relative) to the zip file we're writing. Set by
     * the constructor.
     */
    private $_filename = '';
    
    /* Allow existing files by the same name as our zip file to be overwritten
     * by the new zip file? Set by the constructor.
     */
    private $_allowOverwrite = false;
    
    /* Central Directory record data. The central directory gets written to the
     * end of the zip file, so it must be stored in memory until we're done 
     * writing all the files we need to write.
     */
    private $_centralDirectory = '';
    
    /* Total number of file records added to the zip file. */
    private $_fileRecordCount = 0;

    /* Last file offset position. This starts at 0 and is increased each time a
     * file is added.
     */
    private $_lastOffset = 0;
    
    /* Total length of file records. This will eventually point to the starting
     * offset of the Central Directory.
     */
    private $_fileRecordsLength = 0;
    
    /* Total length of Central Directory. */
    private $_centralDirectoryLength = 0;
    
    /* File handle for the open zip file we're writing as we're writing it. */
    private $_fileHandle = null;
    
    /* Current error message. */
    private $_errorMessage = '';


    public function __construct($filename, $allowOverwrite = false)
    {
        $this->_filename = $filename;
        $this->_allowOverwrite = $allowOverwrite;
    }
    
    /**
     * Opens an archive for writing. This must be called before any files can
     * be added. If overwriting is not allowed and the file exists, or the file
     * could not be created (due to permissions, etc.), boolean false wil be
     * returned.
     *
     * @return boolean Was the operation successful?
     */
    public function open()
    {
        /* Return false if the specified filename is invalid. */
        if (file_exists($this->_filename) && !$this->_allowOverwrite)
        {
            return false;
        }
        
        /* Open the new file for writing in binary mode. */
        $fileHandle = @fopen($this->_filename, 'wb');
        if (!$fileHandle)
        {
            return false;
        }
        
        $this->_fileHandle = $fileHandle;
        return true;
    }
    
    /**
     * Closes, destroys, and deletes the current archive (if called before
     * finalize()).
     *
     * @return boolean Was the operation successful?
     */
    public function abort()
    {
        /* Free some memory. */
        $this->_centralDirectory = '';
        
        /* Close the file handle if it's open. */
        @fclose($this->_fileHandle);
        
        /* Remove the file. */
        @unlink($this->_filename);
        
        return true;
    }

    /**
     * Adds a file to the archive.
     *
     * @param string Name of file as to be stored inside the archive,
     *               optionally including the path.
     * @param string File data.
     * @param integer Last modified timestamp, or false for current time.
     * @return void
     */
    function addFileFromData($name, $data, $timestamp = false)
    {
        /* Normally, we would split this into several methods, but this must be
         * optimized for minimal RAM usage, which presents a design challenge.
         */

        /* Do we still have a valid file handle? */
        if (!$this->_fileHandle)
        {
            $this->_errorMessage = 'Unexpected end of file.';
            return false;
        }

        /* Convert DOS / Windows paths to UNIX paths. */
        $name = str_replace('\\', '/', $name);

        /* If a timestamp wasn't specified, use the current time. */
        if ($timestamp === false)
        {
            $timestamp = time();
        }
        
        /* Convert our UNIX timestamp to DOS format. */
        $DOSTime = FileCompressorUtility::UNIXToDOSTime($timestamp);

        /* Calculate the length of the file data before compression. */
        $uncompressedLength = strlen($data);
        
        /* Calculate the CRC32 checksum of the file data to be compressed. */
        $CRC32 = crc32($data);
        
        /* Compress the file data. */
        $data = gzdeflate($data);
         
        /* Calculate the length of the file data after compression. */
        $compressedLength = strlen($data);
        
        /* Version needed to extract.
         *
         * Store Only: 10
         * DEFLATE:    20
         * BZIP2:      46
         *
         * From the ZIP format specification:
         *
         * Current minimum feature versions are as defined below:
         *
         * 1.0 - Default value
         * 1.1 - File is a volume label
         * 2.0 - File is a folder (directory)
         * 2.0 - File is compressed using Deflate compression
         * 2.0 - File is encrypted using traditional PKWARE encryption
         * 2.1 - File is compressed using Deflate64(tm)
         * 2.5 - File is compressed using PKWARE DCL Implode 
         * 2.7 - File is a patch data set 
         * 4.5 - File uses ZIP64 format extensions
         * 4.6 - File is compressed using BZIP2 compression*
         * 5.0 - File is encrypted using DES
         * 5.0 - File is encrypted using 3DES
         * 5.0 - File is encrypted using original RC2 encryption
         * 5.0 - File is encrypted using RC4 encryption
         * 5.1 - File is encrypted using AES encryption
         * 5.1 - File is encrypted using corrected RC2 encryption**
         * 5.2 - File is encrypted using corrected RC2-64 encryption**
         * 6.1 - File is encrypted using non-OAEP key wrapping***
         * 6.2 - Central directory encryption
         * 6.3 - File is compressed using LZMA
         * 6.3 - File is compressed using PPMd+
         * 6.3 - File is encrypted using Blowfish
         * 6.3 - File is encrypted using Twofish
         */
        $versionNeededToExtract = 20;
        
        /* General purpose bit-flag.
         *
         * From the ZIP format specification:
         *
         * Bit 0: If set, indicates that the file is encrypted.
         *
         * <cut, irrelevant>
         *
         * (For Methods 8 and 9 - Deflating)
         * Bit 2  Bit 1
         *   0      0    Normal (-en) compression option was used.
         *   0      1    Maximum (-exx/-ex) compression option was used.
         *   1      0    Fast (-ef) compression option was used.
         *   1      1    Super Fast (-es) compression option was used.
         *
         * <cut, irrelevant>
         *
         * Bit 3: If this bit is set, the fields crc-32, compressed 
         *        size and uncompressed size are set to zero in the 
         *        local header.  The correct values are put in the 
         *        data descriptor immediately following the compressed
         *        data.  (Note: PKZIP version 2.04g for DOS only 
         *        recognizes this bit for method 8 compression, newer 
         *        versions of PKZIP recognize this bit for any 
         *        compression method.)
         *
         * Bit 4: Reserved for use with method 8, for enhanced
         *        deflating. 
         *
         * Bit 5: If this bit is set, this indicates that the file is 
         *        compressed patched data.  (Note: Requires PKZIP 
         *        version 2.70 or greater)
         *
         * Bit 6: Strong encryption.  If this bit is set, you should
         *        set the version needed to extract value to at least
         *        50 and you must also set bit 0.  If AES encryption
         *        is used, the version needed to extract value must 
         *        be at least 51.
         *
         * Bit [7-10]: Currently unused.
         *
         * Bit 11: Language encoding flag (EFS).  If this bit is set,
         *         the filename and comment fields for this file
         *         must be encoded using UTF-8. (see APPENDIX D)
         *
         * Bit 12: Reserved by PKWARE for enhanced compression.
         *
         * Bit 13: Used when encrypting the Central Directory to indicate 
         *         selected data values in the Local Header are masked to
         *         hide their actual values.  See the section describing 
         *         the Strong Encryption Specification for details.
         *
         * Bit [14-15]: Reserved by PKWARE.
         */
        $generalPurposeBitFlag = 0;
        
        /* Compression method.
         *
         * 0:  STORE
         * 8:  DEFLATE
         * 12: BZIP2
         */
        $compressionMethod = 8;
        
        /* Extra field length. */
        $extraFieldLength = 0;
        
        
        /* Format:
         *
         * [4B] [Start of File Record Marker]
         * [2B] [Version Needed to Extract]
         * [2B] [General Purpose Bit Flag (See Above)]
         * [2B] [Compression Method (See Above)]
         *
         * [4B] [Last-Modified Timestamp in DOS Format]
         * [4B] [CRC32 Checksum of Compressed Data]
         * [4B] [Compressed Data Length]
         * [4B] [Uncompressed Data Length]
         *
         * [2B] [Filename Length]
         * [2B] [Extra Field Length]
         */
        $fileRecord = pack(
            'VvvvVVVVvv',
            START_FILE_RECORD,
            $versionNeededToExtract,
            $generalPurposeBitFlag,
            $compressionMethod,
            
            $DOSTime,
            $CRC32,
            $compressedLength,
            $uncompressedLength,
            
            strlen($name),
            $extraFieldLength
        );
        
        /* Filename. */
        $fileRecord .= $name;

        /* File data segment. */
        $fileRecord .= $data;
        
        /* We can free up some memory now. */
        unset($data);

        /* This is the "data descriptor" section, however, apparently this
         * causes problems and is optional anyway.
         *
         * From the ZIP format specification:
         * C.  Data descriptor:
         *
         * crc-32                          4 bytes
         * compressed size                 4 bytes
         * uncompressed size               4 bytes
         *
         * This descriptor exists only if bit 3 of the general
         * purpose bit flag is set (see below).  It is byte aligned
         * and immediately follows the last byte of compressed data.
         * This descriptor is used only when it was not possible to
         * seek in the output .ZIP file, e.g., when the output .ZIP file
         * was standard output or a non-seekable device.  For ZIP64(tm) format
         * archives, the compressed and uncompressed sizes are 8 bytes each.
         *
         * When compressing files, compressed and uncompressed sizes 
         * should be stored in ZIP64 format (as 8 byte values) when a 
         * files size exceeds 0xFFFFFFFF.   However ZIP64 format may be 
         * used regardless of the size of a file.  When extracting, if 
         * the zip64 extended information extra field is present for 
         * the file the compressed and uncompressed sizes will be 8
         * byte values.  
         *
         * Although not originally assigned a signature, the value 
         * 0x08074b50 has commonly been adopted as a signature value 
         * for the data descriptor record.  Implementers should be 
         * aware that ZIP files may be encountered with or without this 
         * signature marking data descriptors and should account for
         * either case when reading ZIP files to ensure compatibility.
         * When writing ZIP files, it is recommended to include the
         * signature value marking the data descriptor record.  When
         * the signature is used, the fields currently defined for
         * the data descriptor record will immediately follow the
         * signature.
         *
         * <cut, irrlevent>
         *
         * When the Central Directory Encryption method is used, the data
         * descriptor record is not required, but may be used.  If present,
         * and bit 3 of the general purpose bit field is set to indicate
         * its presence, the values in fields of the data descriptor
         * record should be set to binary zeros.
         *
         * $fileRecord .= pack('V', START_DATA_DESCRIPTOR);
         * $fileRecord .= pack('V', $CRC32);
         * $fileRecord .= pack('V', $compressedLength);
         * $fileRecord .= pack('V', $uncompressedLength);
         */
         
        /* Get the length of this file record for use later on. */
        $fileRecordLength = strlen($fileRecord);
         
        /* Add this file record to the zip file. */
        if (fwrite($this->_fileHandle, $fileRecord) === false)
        {
            return false;
        }
        unset($fileRecord);
        
        /* Increment total compressed data length and file record count. */
        $this->_fileRecordsLength += $fileRecordLength;
        ++$this->_fileRecordCount;
        

        /* Create the Central Directory entry for this file and append it
         * to the Central Directory (stored in memory for now until all file
         * records have been written).
         */
        $this->createCentralDirectoryEntry(
            $name,
            $DOSTime,
            $CRC32,
            $compressedLength,
            $uncompressedLength,
            $fileRecordLength
        );
        
        return true;
    }
    

    /**
     * Adds a file to the archive.
     *
     * @param string Name of file as to be stored inside the archive,
     *               optionally including the path.
     * @param string File data.
     * @param integer Last modified timestamp, or false for current time.
     * @return void
     */
    function addFileFromDisk($name, $filename, $timestamp = false)
    {
        /* Normally, we would split this into several methods, but this must be
         * optimized for minimal RAM usage, which presents a design challenge.
         */

        $data = @file_get_contents($filename);

        /* Do we still have a valid file handle? */
        if (!$this->_fileHandle)
        {
            $this->_errorMessage = 'Unexpected end of file.';
            return false;
        }

        /* Convert DOS / Windows paths to UNIX paths. */
        $name = str_replace('\\', '/', $name);

        /* If a timestamp wasn't specified, use the current time. */
        if ($timestamp === false)
        {
            $timestamp = time();
        }
        
        /* Convert our UNIX timestamp to DOS format. */
        $DOSTime = FileCompressorUtility::UNIXToDOSTime($timestamp);

        /* Calculate the length of the file data before compression. */
        $uncompressedLength = filesize($filename);
        
        /* Calculate the CRC32 checksum of the file data to be compressed. */
        $CRC32 = HashUtility::crc32File($filename);
        
        /* Version needed to extract.
         *
         * Store Only: 10
         * DEFLATE:    20
         * BZIP2:      46
         *
         * From the ZIP format specification:
         *
         * Current minimum feature versions are as defined below:
         *
         * 1.0 - Default value
         * 1.1 - File is a volume label
         * 2.0 - File is a folder (directory)
         * 2.0 - File is compressed using Deflate compression
         * 2.0 - File is encrypted using traditional PKWARE encryption
         * 2.1 - File is compressed using Deflate64(tm)
         * 2.5 - File is compressed using PKWARE DCL Implode 
         * 2.7 - File is a patch data set 
         * 4.5 - File uses ZIP64 format extensions
         * 4.6 - File is compressed using BZIP2 compression*
         * 5.0 - File is encrypted using DES
         * 5.0 - File is encrypted using 3DES
         * 5.0 - File is encrypted using original RC2 encryption
         * 5.0 - File is encrypted using RC4 encryption
         * 5.1 - File is encrypted using AES encryption
         * 5.1 - File is encrypted using corrected RC2 encryption**
         * 5.2 - File is encrypted using corrected RC2-64 encryption**
         * 6.1 - File is encrypted using non-OAEP key wrapping***
         * 6.2 - Central directory encryption
         * 6.3 - File is compressed using LZMA
         * 6.3 - File is compressed using PPMd+
         * 6.3 - File is encrypted using Blowfish
         * 6.3 - File is encrypted using Twofish
         */
        $versionNeededToExtract = 20;
        
        /* General purpose bit-flag.
         *
         * From the ZIP format specification:
         *
         * Bit 0: If set, indicates that the file is encrypted.
         *
         * <cut, irrelevant>
         *
         * (For Methods 8 and 9 - Deflating)
         * Bit 2  Bit 1
         *   0      0    Normal (-en) compression option was used.
         *   0      1    Maximum (-exx/-ex) compression option was used.
         *   1      0    Fast (-ef) compression option was used.
         *   1      1    Super Fast (-es) compression option was used.
         *
         * <cut, irrelevant>
         *
         * Bit 3: If this bit is set, the fields crc-32, compressed 
         *        size and uncompressed size are set to zero in the 
         *        local header.  The correct values are put in the 
         *        data descriptor immediately following the compressed
         *        data.  (Note: PKZIP version 2.04g for DOS only 
         *        recognizes this bit for method 8 compression, newer 
         *        versions of PKZIP recognize this bit for any 
         *        compression method.)
         *
         * Bit 4: Reserved for use with method 8, for enhanced
         *        deflating. 
         *
         * Bit 5: If this bit is set, this indicates that the file is 
         *        compressed patched data.  (Note: Requires PKZIP 
         *        version 2.70 or greater)
         *
         * Bit 6: Strong encryption.  If this bit is set, you should
         *        set the version needed to extract value to at least
         *        50 and you must also set bit 0.  If AES encryption
         *        is used, the version needed to extract value must 
         *        be at least 51.
         *
         * Bit [7-10]: Currently unused.
         *
         * Bit 11: Language encoding flag (EFS).  If this bit is set,
         *         the filename and comment fields for this file
         *         must be encoded using UTF-8. (see APPENDIX D)
         *
         * Bit 12: Reserved by PKWARE for enhanced compression.
         *
         * Bit 13: Used when encrypting the Central Directory to indicate 
         *         selected data values in the Local Header are masked to
         *         hide their actual values.  See the section describing 
         *         the Strong Encryption Specification for details.
         *
         * Bit [14-15]: Reserved by PKWARE.
         */
        $generalPurposeBitFlag = 0;
        
        /* Compression method.
         *
         * 0:  STORE
         * 8:  DEFLATE
         * 12: BZIP2
         */
        $compressionMethod = 8;
        
        /* Extra field length. */
        $extraFieldLength = 0;
        
        
        /* Format:
         *
         * [4B] [Start of File Record Marker]
         * [2B] [Version Needed to Extract]
         * [2B] [General Purpose Bit Flag (See Above)]
         * [2B] [Compression Method (See Above)]
         *
         * [4B] [Last-Modified Timestamp in DOS Format]
         * [4B] [CRC32 Checksum of Compressed Data]
         * [4B] [Compressed Data Length]
         * [4B] [Uncompressed Data Length]
         *
         * [2B] [Filename Length]
         * [2B] [Extra Field Length]
         */
        $fileRecord = pack(
            'VvvvVVVVvv',
            START_FILE_RECORD,
            $versionNeededToExtract,
            $generalPurposeBitFlag,
            $compressionMethod,
            
            $DOSTime,
            $CRC32,
            0,
            $uncompressedLength,
            
            strlen($name),
            $extraFieldLength
        );
        
        /* Filename. */
        $fileRecord .= $name;

        /* This is the "data descriptor" section, however, apparently this
         * causes problems and is optional anyway.
         *
         * From the ZIP format specification:
         * C.  Data descriptor:
         *
         * crc-32                          4 bytes
         * compressed size                 4 bytes
         * uncompressed size               4 bytes
         *
         * This descriptor exists only if bit 3 of the general
         * purpose bit flag is set (see below).  It is byte aligned
         * and immediately follows the last byte of compressed data.
         * This descriptor is used only when it was not possible to
         * seek in the output .ZIP file, e.g., when the output .ZIP file
         * was standard output or a non-seekable device.  For ZIP64(tm) format
         * archives, the compressed and uncompressed sizes are 8 bytes each.
         *
         * When compressing files, compressed and uncompressed sizes 
         * should be stored in ZIP64 format (as 8 byte values) when a 
         * files size exceeds 0xFFFFFFFF.   However ZIP64 format may be 
         * used regardless of the size of a file.  When extracting, if 
         * the zip64 extended information extra field is present for 
         * the file the compressed and uncompressed sizes will be 8
         * byte values.  
         *
         * Although not originally assigned a signature, the value 
         * 0x08074b50 has commonly been adopted as a signature value 
         * for the data descriptor record.  Implementers should be 
         * aware that ZIP files may be encountered with or without this 
         * signature marking data descriptors and should account for
         * either case when reading ZIP files to ensure compatibility.
         * When writing ZIP files, it is recommended to include the
         * signature value marking the data descriptor record.  When
         * the signature is used, the fields currently defined for
         * the data descriptor record will immediately follow the
         * signature.
         *
         * <cut, irrlevent>
         *
         * When the Central Directory Encryption method is used, the data
         * descriptor record is not required, but may be used.  If present,
         * and bit 3 of the general purpose bit field is set to indicate
         * its presence, the values in fields of the data descriptor
         * record should be set to binary zeros.
         *
         * $fileRecord .= pack('V', START_DATA_DESCRIPTOR);
         * $fileRecord .= pack('V', $CRC32);
         * $fileRecord .= pack('V', $compressedLength);
         * $fileRecord .= pack('V', $uncompressedLength);
         */
         
        /* Get the length of this file record for use later on. */
        $fileRecordLength = strlen($fileRecord);
         
        /* Add this file record to the zip file. */
        if (fwrite($this->_fileHandle, $fileRecord) === false)
        {
            return false;
        }
        unset($fileRecord);

        $tempFilename = FileUtility::makeRandomTemporaryFilePath();

        $compressedLength = 0;
        $fhSource = fopen(realpath($filename), 'rb');        
        $fhCompressor = gzopen($tempFilename, 'wb');
        
        if (!$fhSource or !$fhCompressor)
        {
            $this->_errorMessage = 'Unexpected end of file.';
            return false;
        }
        
        while(!feof($fhSource))
        {
            $temp = fread($fhSource, 32767);
            gzwrite($fhCompressor, $temp);
        }
        gzclose($fhCompressor);
        fclose($fhSource);

        $fhCompressed = fopen($tempFilename, 'rb');

        if (!$fhCompressed)
        {
            $this->_errorMessage = 'Unexpected end of file.';
            return false;
        }

        /* Strip off the headers and footers. */
        $gzipHeaderLength = 10;
        $gzipFooterLength = 8;
    
        if (fseek($fhCompressed, $gzipHeaderLength, SEEK_SET)  === -1)
        {
            $this->_errorMessage = 'Unexpected end of file.';
            return false;
        }
    
        while(!feof($fhCompressed))
        {
            $temp = fread($fhCompressed, 8192);
            $compressedLength += strlen($temp);
            fwrite($this->_fileHandle, $temp);
        }
        fclose($fhCompressed);
        
        @unlink($tempFilename);
               
        /* Ignore last 8 bytes of compressed data. */
        $compressedLength -= $gzipFooterLength;
                
        /* We need to seek back into the file and correct the 4B representation
           of how large the compressed data is. It is stored at 
           pointer - $compressedSize - 12 bytes. */
       if (fseek($this->_fileHandle, 0 - $compressedLength - strlen($name) - $gzipFooterLength - 12, SEEK_CUR) === -1)
       {
           $this->_errorMessage = 'Unexpected end of file.';
           return false;
       }       
       
       $compressedLengthPacked = pack('V', $compressedLength);
       
       fwrite($this->_fileHandle, $compressedLengthPacked);

       /* Seek to the end of the file, but seek back 4 bytes to recover CRC bug for gzcompress. */
       if (fseek($this->_fileHandle, 0 - $gzipFooterLength, SEEK_END) === -1)
       {
           $this->_errorMessage = 'Unexpected end of file.';
           return false;
       }        
        
        /* Increment total compressed data length and file record count. */
        $fileRecordLength += $compressedLength;
        
        $this->_fileRecordsLength += $fileRecordLength;

        ++$this->_fileRecordCount;
        
        /* Create the Central Directory entry for this file and append it
         * to the Central Directory (stored in memory for now until all file
         * records have been written).
         */
        $this->createCentralDirectoryEntry(
            $name,
            $DOSTime,
            $CRC32,
            $compressedLength,
            $uncompressedLength,
            $fileRecordLength
        );
        
        return true;
    }
    
    
    private function createCentralDirectoryEntry($name, $DOSTime, $CRC32,
        $compressedLength, $uncompressedLength, $fileRecordLength)
    {
        
        /* External file attributes - archive bit set.
         * 0 for none. See the specification for details.
         */
        $externalFileAttributes = 32;
        
        /* Internal file attributes.
         * 0 for none. See the specification for details.
         */
        $internalFileAttrbutes = 0;
        
        /* Version needed to extract. See above. */
        $versionNeededToExtract = 20;
        
        /* Version made by (and operating system). See above for version
         * number information and below for operating system information. 
         *
         * From the ZIP format specification:
         *
         * The upper byte indicates the compatibility of the file
         * attribute information.  If the external file attributes 
         * are compatible with MS-DOS and can be read by PKZIP for 
         * DOS version 2.04g then this value will be zero.  If these 
         * attributes are not compatible, then this value will 
         * identify the host system on which the attributes are 
         * compatible.  Software can use this information to determine
         * the line record format for text files etc.  The current
         * mappings are:
         *
         * 0 - MS-DOS and OS/2 (FAT / VFAT / FAT32 file systems)
         * 1 - Amiga                     2 - OpenVMS
         * 3 - UNIX                      4 - VM/CMS
         * 5 - Atari ST                  6 - OS/2 H.P.F.S.
         * 7 - Macintosh                 8 - Z-System
         * 9 - CP/M                     10 - Windows NTFS
         * 11 - MVS (OS/390 - Z/OS)      12 - VSE
         * 13 - Acorn Risc               14 - VFAT
         * 15 - Alternate MVS            16 - BeOS
         * 17 - Tandem                   18 - OS/400
         * 19 - OS/X (Darwin)            20 thru 255 - unused
         *
         * The lower byte indicates the ZIP specification version 
         * (the version of this document) supported by the software 
         * used to encode the file.  The value/10 indicates the major 
         * version number, and the value mod 10 is the minor version 
         * number.
         */
        $madeByOS = 3;
        $madeByVersion = 20;
        
        /* General purpose bit flag (see above). */
        $generalPurposeBitFlag = 0;
        
        /* Compression method.
         *
         * 0:  STORE
         * 8:  DEFLATE
         * 12: BZIP2
         */
        $compressionMethod = 8;
        
        /* Disk number - used for archive spanning. */
        $diskNumber = 0;
        
        /* Length of extra field data. */
        $extraFieldLength = 0;
        
        /* Length of file comment. */
        $fileCommentLength = 0;

         
        /* Format:
         *
         * [4B] [Start of Central Directory Entry Marker]
         * [1B] [Version Made By]
         * [1B] [Operating System Made By]
         * [2B] [Version Needed to Extract]
         *
         * [2B] [General Purpose Bit Flag (See Above)]
         * [2B] [Compression Method (See Above)]
         * [4B] [Last-Modified Timestamp in DOS Format]
         * [4B] [CRC32 Checksum of Compressed Data]
         *
         * [4B] [Compressed Data Length]
         * [4B] [Uncompressed Data Length]
         * [2B] [Filename Length]
         * [2B] [Extra Field Length]
         *
         * [2B] [File Comment Length]
         * [2B] [Disk Number]
         * [2B] [Internal File Attributes]
         * [4B] [External File Attributes]
         *
         * [4B] [Starting Offset of Associated File Record]
         */
        $centralDirectoryEntry = pack(
            'VccvvvVVVVvvvvvVV',
            START_CENTRAL_DIRECTORY_ENTRY,
            $madeByVersion,
            $madeByOS,
            $versionNeededToExtract,
            
            $generalPurposeBitFlag,
            $compressionMethod,
            $DOSTime,
            $CRC32,
            
            $compressedLength,
            $uncompressedLength,
            strlen($name),
            $extraFieldLength,
            
            $fileCommentLength,
            $diskNumber,
            $internalFileAttrbutes,
            $externalFileAttributes,
            
            $this->_lastOffset
        );

        /* File name. */
        $centralDirectoryEntry .= $name;
        
        /* Set last file record offeset to one byte after the end of this
         * entry's file record.
         */
        $this->_lastOffset += $fileRecordLength;

        /* Add this entry to the Central Directory. */
        $this->_centralDirectory .= $centralDirectoryEntry;
        
        /* Increment total Central Directory length. */
        $this->_centralDirectoryLength += strlen($centralDirectoryEntry);
    }
    
    /**
     * Finishes writing the zip file to a file, frees resources, and closes
     * the file. After this point, it is safe to reuse the same ZipFileCreator
     * to create a new zip file.
     *
     * @return boolean True if successful; false otherwise.
     */
    public function finalize()
    {
        /* This method is written with RAM optimization in mind. This code
         * could probably be more clear.
         *
         * Format:
         * [File Records]
         * [Central Directory]
         * [End of Central Directory Marker]
         * [This "Disk's" "Disk Number"]
         * ["Disk Number" Containing Central Directory]
         * [Entries "On This Disk"]
         * [Total Entries]
         * [Size of Central Directory]
         * [Offset to Start of Central Directory (Length of Data)]
         * [Zip File Comment Length]
         */

        /* Write and free the Central Directory. */
        if (fwrite($this->_fileHandle, $this->_centralDirectory) === false)
        {
            return false;
        }
        $this->_centralDirectory = '';

        /* Build and write the End of Central Directory marker and file footer
         * fields.
         */
        // FIXME: Document me. I'm tired; will do tomorrow. -Will
        $footerFields = pack(
            'VvvvvVVv',
            END_CENTRAL_DIRECTORY,
            0,
            0,
            $this->_fileRecordCount,
            $this->_fileRecordCount,
            $this->_centralDirectoryLength,
            $this->_fileRecordsLength,
            0
        );
        if (fwrite($this->_fileHandle, $footerFields) === false)
        {
            return false;
        }
        
        /* Close the file handle. */
        @fclose($this->_fileHandle);
        $this->_fileHandle = null;
        return true;
    }
}

/**
 *	Zip File Extractor
 *	@package    CATS
 *	@subpackage Library
 */
class ZipFileExtractor
{
    /* Full path (absolute or relative) to the zip file we're reading. Set by
     * the constructor.
     */
    private $_filename = '';
    
    /* File handle for the open zip file we're writing as we're writing it. */
    private $_fileHandle = null;
    
    /* Meta data and Central Directory. */
    private $_metaData = array();
    
    /* Current error message. */
    private $_errorMessage = '';


    public function __construct($filename)
    {
        $this->_filename = $filename;
    }
    
    private function getErrorMessage()
    {
        return $this->_errorMessage;
    }
    
    public function open()
    {
        /* Return false if the specified filename is invalid. */
        if (!file_exists($this->_filename) || !is_readable($this->_filename))
        {
            $this->_errorMessage = 'File does not exist.';
            return false;
        }
        
        /* Open the new file for reading in binary mode. */
        $fileHandle = @fopen($this->_filename, 'rb');
        if (!$fileHandle)
        {
            $this->_errorMessage = 'Failed to open file for reading.';
            return false;
        }
        
        $this->_fileHandle = $fileHandle;
        
        /* Seek to the end of the file. */
        if (fseek($fileHandle, 0, SEEK_END) === -1)
        {
            $this->_errorMessage = 'Unexpected end of file.';
            return false;
        }
        
        /* Find the length of the file (position of the end of the file). */
        $fileLength = ftell($fileHandle);
        if (!$fileLength)
        {
            $this->_errorMessage = 'Unexpected end of file.';
            return false;
        }
        
        /* Find the offset from the end of the file where the first byte of the
         * End of Central Directory marker is found. If false, we didn't find
         * an End of Central Directory marker and we can't read this file.
         */
        $position = $this->findEndCentralDirectoryMarker(
            $fileHandle, $fileLength
        );
        if (!$position)
        {
            $this->_errorMessage = 'Could not find end of Central Directory.';
            return false;
        }

        /* Seek to the start of the End of Central Directory record, right
         * after the End of Central Directory marker.
         */
        if (fseek($fileHandle, ($position + 4), SEEK_END) == -1)
        {
            $this->_errorMessage = 'Unexpected end of file.';
            return false;
        }
        
        /* Read the next 18 bytes of the file (everything up to the start of
         * the variable-length comment field).
         */
        $string = fread($fileHandle, 18);
        if (!$string)
        {
            $this->_errorMessage = 'Unexpected end of file.';
            return false;
        }
        
        /* Parse the metadata we just extracted. */
        $metaData = @unpack(
              'vdiskNumber/'
            . 'vcentralDirectoryDiskNumber/'
            . 'vdiskFileRecordCount/'
            . 'vfileRecordCount/'
            . 'VcentralDirectoryLength/'
            . 'VcentralDirectoryStart/'
            . 'vcommentLength',
            $string
        );

        /* Was parsing successful? */
        if (!$metaData)
        {
            $this->_errorMessage = 'Failed to parse general meta information.';
            return false;
        }
        
        /* Read the zip comment into a string. */
        if ($metaData['commentLength'] > 0)
        {
            /* Read <comment-length> bytes and try to extract the comment. */
            $string = fread($fileHandle, $metaData['commentLength']);
            if ($string === false)
            {
                $this->_errorMessage = 'Invalid zip comment.';
                return false;
            }
            
            /* Attempt to extract the zip comment. */
            $commentData = @unpack('a*0', $string);
        }
        else
        {
            $commentData = null;
        }
        
        /* Add the zip comment to the metadata array. */
        if (!$commentData)
        {
            $commentData = array('');
        }
        list($metaData['comment']) = $commentData;
        
        /* Is our central directory start offset valid? */
        $centralDirectoryOffset = $metaData['centralDirectoryStart'];
        if ($centralDirectoryOffset <= 0 ||
            $centralDirectoryOffset >= $fileLength)
        {
            $this->_errorMessage = 'Invalid start of Central Directory.';
            return false;
        }
        
        /* Parse the central directory into an array. */
        $metaData['centralDirectory'] = $this->parseCentralDirectory(
            $centralDirectoryOffset, $metaData['centralDirectoryLength']
        );
        
        if ($metaData['centralDirectory'] === false)
        {
            /* Error message was set by parseCentralDirectory. */
            return false;
        }
        
        /* Set our position to the start of the file again. */
        if (fseek($fileHandle, 0, SEEK_SET) === -1)
        {
            $this->_errorMessage = 'Unexpected end of file.';
            return false;
        }
        
        $this->_metaData = $metaData;
        return true;
    }
    
    public function getMetaData()
    {
        return $this->_metaData;
    }
    
    /* Returns a binary string containing the contents of the specified file.
     * For a text file, this can be used just like a normal string.
     * TODO:  Rework so the entire file doesn't need to be read into memory to extract.
     */
    public function getFile($fileID)
    {
        if (!isset($this->_metaData['centralDirectory'][$fileID]))
        {
            return false;
        }
        
        return $this->getFileByOffset($this->_metaData['centralDirectory'][$fileID]['fileRecordStart']);
    }
    
    public function extractAll()
    {
        foreach ($this->_metaData['centralDirectory'] as $index => $data)
        {
            $fileName = $data['filename'];

            if (strpos($fileName, '/') !== false)
            {
                $directorySplit = explode('/', $fileName);
                unset($directorySplit[count($directorySplit)-1]);
                $directory = implode('/', $directorySplit);
                @mkdir($directory, 0777, true);
            }
            
            $fileContents = $this->getFile($index);
            if ($fileContents === false)
            {
                return false;
            }
            file_put_contents ($fileName, $fileContents);
        }
        
        return true;
    }
    
    
    private function parseCentralDirectory($startOffset, $length)
    {
        /* Seek to the start of the central directory. */
        if (fseek($this->_fileHandle, $startOffset, SEEK_SET) === -1)
        {
            $this->_errorMessage = 'Unexpected end of file.';
            return false;
        }
        
        /* Read the entire central directory into memory. */
        // FIXME: Should we be doing this? It usually won't be *THAT* much ram...
        $bytes = fread($this->_fileHandle, $length);
        if (!$bytes)
        {
            $this->_errorMessage = 'Unexpected end of file.';
            return false;
        }
        
        /* Get an array of central directory entries. */
        $entries = explode(
            pack('V', START_CENTRAL_DIRECTORY_ENTRY),
            $bytes
        );
        unset($bytes);
        
        /* Remove the empty element at the start of the array caused by the
         * first start-of-entry marker.
         */
        array_shift($entries);

        $metaData = array();
        foreach ($entries as $index => $entry)
        {   
            /* Format:
             *
             * [4B] [Start of Central Directory Entry Marker] [REMOVED]
             * [1B] [Version Made By]
             * [1B] [Operating System Made By]
             * [2B] [Version Needed to Extract]
             *
             * [2B] [General Purpose Bit Flag (See Above)]
             * [2B] [Compression Method (See Above)]
             * [4B] [Last-Modified Timestamp in DOS Format]
             * [4B] [CRC32 Checksum of Compressed Data]
             *
             * [4B] [Compressed Data Length]
             * [4B] [Uncompressed Data Length]
             * [2B] [Filename Length]
             * [2B] [Extra Field Length]
             *
             * [2B] [File Comment Length]
             * [2B] [Disk Number]
             * [2B] [Internal File Attributes]
             * [4B] [External File Attributes]
             *
             * [4B] [Starting Offset of Associated File Record]
             */
             
            /* Parse the metadata we just extracted. */
            $metaData[$index] = @unpack(
                  'cversionMadeBy/'
                . 'coperatingSystemMadeBy/'
                . 'vversionNeededToExtract/'
                . 'vgeneralPurposeBitFlag/'
                . 'vcompressionMethod/'
                . 'VlastModified/'
                . 'VCRC32/'
                . 'VcompressedSize/'
                . 'VuncompressedSize/'
                . 'vfilenameLength/'
                . 'vextraFieldLength/'
                . 'vfileCommentLength/'
                . 'vdiskNumber/'
                . 'vinternalFileAttributes/'
                . 'VexternalFileAttributes/'
                . 'VfileRecordStart',
                $entry
            );

            /* Was parsing successful? */
            if (!$metaData)
            {
                $this->_errorMessage = sprintf(
                    'Failed to parse central directory entry %s.',
                    $index
                );
                return false;
            }
            
            /* Read the filename into a string. */
            if ($metaData[$index]['filenameLength'] > 0)
            {
                /* Attempt to extract the filename. */
                list($metaData[$index]['filename']) = @unpack(
                    'a*0',
                    substr($entry, 42, $metaData[$index]['filenameLength'])
                );
            }
            else
            {
                $metaData[$index]['filename'] = null;
            }

            unset($entries[$index]);
        }
        
        return $metaData;
    }
    
    /**
     * Extracts a file record from the given file offset. 
     * //FIXME: Make private after testing.
     *
     * @param integer start offset
     * @return string binary file contents
     */
    public function getFileByOffset($startOffset)
    {
        /* Seek to the start of the central directory. */
        if (fseek($this->_fileHandle, $startOffset, SEEK_SET) === -1)
        {
            $this->_errorMessage = 'Unexpected end of file.';
            return false;
        }
        
        /* Read the first 30 bytes into memory. */
        $bytes = fread($this->_fileHandle, 30);
        if (!$bytes)
        {
            $this->_errorMessage = 'Unexpected end of file.';
            return false;
        }
        
        /* Remove the Start of File Record marker. */
        $bytes = substr($bytes, 4);
        
        /* Format:
         *
         * [4B] [Start of File Record Marker] [REMOVED]
         * [2B] [Version Needed to Extract]
         * [2B] [General Purpose Bit Flag (See Above)]
         * [2B] [Compression Method (See Above)]
         *
         * [4B] [Last-Modified Timestamp in DOS Format]
         * [4B] [CRC32 Checksum of Compressed Data]
         * [4B] [Compressed Data Length]
         * [4B] [Uncompressed Data Length]
         *
         * [2B] [Filename Length]
         * [2B] [Extra Field Length]
         */
        
        /* Parse the first 26 bytes (metadata). */
        $metaData = @unpack(
              'vversionNeededToExtract/'
            . 'vgeneralPurposeBitFlag/'
            . 'vcompressionMethod/'
            . 'VlastModified/'
            . 'VCRC32/'
            . 'VcompressedSize/'
            . 'VuncompressedSize/'
            . 'vfilenameLength/'
            . 'vextraFieldLength',
            $bytes
        );

        /* Was parsing successful? */
        if (!$metaData)
        {
            $this->_errorMessage = 'Failed to parse file meta information.';
            return false;
        }
        
        /* Make things a bit easier down below. */
        extract($metaData);
        unset($metaData);
        
        /* Seek to the start of the compressed data. */
        $offset = $filenameLength + $extraFieldLength;
        if (fseek($this->_fileHandle, $offset, SEEK_CUR) === -1)
        {
            $this->_errorMessage = 'Unexpected end of file.';
            return false;
        }
        
        /* Read the compressed data into memory. */
        $bytes = fread($this->_fileHandle, $compressedSize);
        if (!$bytes)
        {
            $this->_errorMessage = 'Unexpected end of file.';
            return false;
        }

        /* Decompress the compressed data. */
        switch ($compressionMethod)
        {
            /* DEFLATE compression. */
            case 8:
                $uncompressedData = gzinflate($bytes);
                break;
                
            /* BZIP2 compression. See manual for reduced-memory method. */
            case 12:
                $uncompressedData = bzdecompress($bytes);
                break;
                
            /* STORE (no compression). */
            case 0:
                $uncompressedData = $bytes;
                break;
            
            /* Something we don't know how to handle. There are several ZIP
             * compression methods allowed for in the APPNOTE that we don't
             * support.
             */
            default:
                $this->_errorMessage = 'Invalid / unknown compression method.';
                return false;
                break;
        }
        
        /* Free up a bit of memory. */
        unset($bytes);
        
        /* Calculate the CRC32 checksum of the file data to be compressed. */
        if (crc32($uncompressedData) != $CRC32)
        {
            $this->_errorMessage = 'CRC32 checksum does not match.';
            return false;
        }
        
        return $uncompressedData;
    }
    
    private function findEndCentralDirectoryMarker($fileHandle, $fileLength)
    {
        /* The marker we're looking for (a 4-byte sequence). */
        $markerBytes = pack('V', END_CENTRAL_DIRECTORY);
        
        /* Loop through the file one byte at a time, starting from the end,
         * until we find an End of Central Directory marker.
         */
        $position = -1;
        while (abs($position) <= $fileLength)
        {
            /* Seek to whatever position (from the end) we're set to go to.
             * This will be one byte from the end on the first loop.
             */
            if (fseek($fileHandle, $position, SEEK_END) === -1)
            {
                return false;
            }
            
            /* Read one byte into a buffer and see if it could be the last byte
             * of the End of Central Directory marker. If not, just continue
             * looping and read in another character.
             */
            $byte = fgetc($fileHandle);
            if ($byte != $markerBytes[3])
            {
                --$position;
                continue;
            }
            
            /* Seek back another 3 bytes before the byte we just read and read
             * 4 bytes (including the current byte).
             */
            if (fseek($fileHandle, ($position - 3), SEEK_END) === -1)
            {
                return false;
            }
            
            $bytes = fread($fileHandle, 4);
            if (!$bytes)
            {
                return false;
            }
            
            /* Is the 4 byte string we just read an End of Central Directory
             * marker? If so, return the position of the first byte of the
             * marker.
             */
            if ($bytes === $markerBytes)
            {
                return ($position - 3);
            }
            
            /* We didn't really have an End of Central Directory marker, keep
             * looping backwards.
             */
            --$position;
        }
    }
}

/**
 *	File Compression Utility Library
 *	@package    CATS
 *	@subpackage Library
 */
class FileCompressorUtility
{
    /**
     * Converts an UNIX timestamp to a 32-bit DOS timestamp. A date before 1980
     * cannot be specified. See http://www.vsft.com/hal/dostime.htm for more
     * information.
     *
     * Bits:
     *
     * 0-4     5-10    11-15  16-20             21-24         25-31
     * Second  Minute  Hour   Month Day (1-31)  Month (1-12)  Years from 1980
     *
     * @param integer UNIX timestamp (as returned by time()) to be converted.
     * @return integer DOS timestamp.
     */
    public static function UNIXToDOSTime($timestamp)
    {
        $date = getdate($timestamp);

        /* Sanity check. */
        if ($date['year'] < 1980)
        {
            $date['year']    = 1980;
            $date['mon']     = 1;
            $date['mday']    = 1;
            $date['hours']   = 0;
            $date['minutes'] = 0;
            $date['seconds'] = 0;
        }

        /* Subtract 1980 from the year; the DOS format stores "years from 1980"
         * and not a full four-digit year.
         */
        $date['year'] -= 1980;
        
        /* Do a bit of shifting to convert to the date parts to the 32-bit DOS-
         * formatted parts.
         */
        $year   = $date['year']    << 25;
        $month  = $date['mon']     << 21;
        $day    = $date['mday']    << 16;
        $hour   = $date['hours']   << 11;
        $minute = $date['minutes'] << 5;
        $second = $date['seconds'] >> 1;
        
        /* OR the DOS-formatted parts to build the complete DOS timestamp. */
        $DOSTime = ($year | $month | $day | $hour | $minute | $second);

        return $DOSTime;
    }
    
    /**
     * Converts a DOS time and date stamp into a UNIX timestamp. See
     * http://www.vsft.com/hal/dostime.htm for more information.
     *
     * Time Bits:
     *
     * 0-4     5-10    11-15
     * Second  Minute  Hour  
     *
     * Date Bits:
     *
     * 0-4               5-8           9-15
     * Month Day (1-31)  Month (1-12)  Years from 1980
     *
     * @param integer UNIX timestamp (as returned by time()) to be converted.
     * @return integer DOS timestamp.
     */
    public static function DOSToUNIXTime($date, $time)
    {
        /* We can recover if we don't have a date, there's nothing we can do,
         * but if we have a date but not a time, we can return the timestamp
         * for the specified date at midnight.
         */
        if (!$date)
        {
            return 0;
        }
        
        /* Convert the date. */
        $year  = (($date & 0xFE00) >> 9) + 1980;
        $month =  ($date & 0x01E0) >> 5;
        $day   =  ($date & 0x001F);

        /* Convert the time. If we don't have one, use midnight. */
        if ($time)
        {
            $hour   = ($time & 0xF800) >> 11;
            $minute = ($time & 0x07E0) >> 5;
            $second = ($time & 0x001F) >> 1;
        }
        else
        {
            $hour   = 0;
            $minute = 0;
            $second = 0;
        }

        return mktime($hour, $minute, $second, $month, $day, $year);
    }
}
?>

