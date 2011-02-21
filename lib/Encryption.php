<?php
/**
 * CATS
 * Encryption Library
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
 * @version    $Id: Encryption.php 3587 2007-11-13 03:55:57Z will $
 */

/**
 *	Encryption Library
 *	@package    CATS
 *	@subpackage Library
 */
class Encryption
{
    private $_td;


    public function __construct($key, $algorithm, $mode = 'ecb', $iv = false)
    {
        /* In non-ECB mode, an initialization vector is required. */
        if ($mode != 'ecb' && $iv === false)
        {
            return false;
        }

        /* Try to open the encryption module. */
        $this->_td = mcrypt_module_open($algorithm, '', $mode, '');
        if ($this->_td === false)
        {
            return false;
        }

        /* Use UNIX random number generator if available. */
        if (strstr(PHP_OS, 'WIN') !== false)
        {
            $randomSeed = MCRYPT_RAND;
        }
        else
        {
            $randomSeed = MCRYPT_DEV_RANDOM;
        }

        /* If an initialization vector was not specified, create one;
         * otherwise ensure that the specified IV is the proper size.
         */
        if ($iv === false)
        {
            $iv = mcrypt_create_iv(
                mcrypt_enc_get_iv_size($this->_td), $randomSeed
            );
        }
        else
        {
            $iv = substr($iv, 0, mcrypt_enc_get_iv_size($this->_td));
        }

        /* Trim the key to the maximum allowed key size. */
        $key = substr($key, 0, mcrypt_enc_get_key_size($this->_td));

        /* Initialize the MCrypt library. */
        mcrypt_generic_init($this->_td, $key, $iv);
    }


    public function encrypt($plainText)
    {
        /* Base64 encode data to protect special characters. */
        return base64_encode(mcrypt_generic($this->_td, $plainText));
    }

    public function decrypt($cypherText)
    {
        /* Base64-decode the encrypted data and decrypt it. */
        $plainText = mdecrypt_generic($this->_td, base64_decode($cypherText));

        /* Remove any \0 padding. */
        return rtrim($plainText, "\0");
    }


    public function __destruct()
    {
        /* Clean up after ourselves. */
        mcrypt_generic_deinit($this->_td);
        mcrypt_module_close($this->_td);
    }
}

?>
