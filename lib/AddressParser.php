<?php
/**
 * CATS
 * Address Parser
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
 * @version    $Id: AddressParser.php 3592 2007-11-13 17:30:46Z brian $
 */

/* Flags for default phone number. */
define('ADDRESSPARSER_MODE_PERSON', 1);
define('ADDRESSPARSER_MODE_CONTACT', 2);
define('ADDRESSPARSER_MODE_COMPANY', 3);

/**
 * Aray Utility library.
 */
include_once('./lib/ArrayUtility.php');

/**
 * Result Set Utility library.
 */
include_once('./lib/ResultSetUtility.php');


/**
 *  Address Parser
 *  @package    CATS
 *  @subpackage Library
 */
class AddressParser
{
    protected $_firstName;
    protected $_middleName;
    protected $_lastName;
    protected $_company;
    protected $_addressLineOne;
    protected $_addressLineTwo;
    protected $_city;
    protected $_state;
    protected $_zip;
    protected $_email;
    protected $_phoneNumbers;
    protected $_addressBlock;
    protected $_mode;


    /**
     * Attempts to parse the given address block. This should be a string
     * in "raw" format with the newlines and everything still intact.
     *
     * @param string Intact address block.
     * @param integer Default phone number type flag.
     * @return void
     */
    public function parse($addressBlockString, $mode)
    {
        /* 'Fix up' the address block for parsing and initialize return data to
         * '' so we have safe / predictable return values even if we can't find
         * something.
         */
        $this->_initialize($addressBlockString, $mode);

        /* Extract and remove the e-mail address first; it's easy to identify. */
        $this->_email = $this->_extractEmailAddress();
        
        /* Attempt to find a company name. */
        //FIXME: Use a scoring system.
        foreach ($this->_addressBlock as $lineNumber => $line)
        {
            if ($this->_isCompanyName($line))
            {
                $this->_company = $line;
                break;
            }
        }
        
        /* Reverse the array before searching, as it is more probable to find a
         * "City, State Zip" line tword the end of the address block.
         */
        $reversedAddressBlock = array_reverse($this->_addressBlock, true);
        
        /* Find the "City, State Zip" line and use it to guide the rest of our
         * parsing.
         */
        $cityStateZipLineArray = array(-1, '');
        foreach ($reversedAddressBlock as $lineNumber => $line)
        {
            if ($this->_isCityStateZip($line))
            {
                $cityStateZipLineArray = array($lineNumber, $line);
                break;
            }
        }
        
        /* Using the "City, State Zip" line as a guiding point, find the
         * "address address line one.
         */
        $addressOneLineArray = array(-1, '');
        foreach ($this->_addressBlock as $lineNumber => $line)
        {
            if ($lineNumber != $cityStateZipLineArray[0] &&
                $this->_isStreetAddress($line))
            {
                $addressOneLineArray = array($lineNumber, $line);
                break;
            }
        }
        
        
        /* Get address line one number and text from the "line, text" array. */
        $addressOneLineOffset  = $addressOneLineArray[0];
        $this->_addressLineOne = $addressOneLineArray[1];

        /* Try to get an address line two unless address line one is the last
         * line, or the line after address line one is the city, state, and zip.
         * If there is an address line three, we ignore it.
         */
        if ($addressOneLineOffset >= 0 &&
            count($this->_addressBlock) > ($addressOneLineOffset + 1) &&
            ($addressOneLineOffset + 1) != $cityStateZipLineArray[0])
        {
            $this->_addressLineTwo = $this->_addressBlock[$addressOneLineOffset + 1];
        }

        /* Get the city, state, zip array. */
        $cityStateZipArray = $this->_getCityStateZipArray($cityStateZipLineArray[1]);

        /* Get the city, state, zip "pieces". */
        $this->_city   = $cityStateZipArray['city'];
        $this->_state  = $cityStateZipArray['state'];
        $this->_zip    = $cityStateZipArray['zip'];

        /* Find and parse the name if we're not in Company mode. */
        if ($mode != ADDRESSPARSER_MODE_COMPANY)
        {
            /* Get the full name array. */
            $fullNameArray = $this->_getFullNameArray($addressOneLineOffset);

            /* Get the name "pieces". */
            $this->_firstName  = $fullNameArray['firstName'];
            $this->_middleName = $fullNameArray['middleName'];
            $this->_lastName   = $fullNameArray['lastName'];
        }

        /* Extract all phone numbers and then sort out the types. */
        $this->_phoneNumbers = $this->_getPhoneNumbers();
    }

    /**
     * Returns an associative array of the parsed address:
     *   firstName, middleName, lastName, addressLineOne, addressLineTwo, zip
     *   email, phoneCell, phoneHome, phoneWork
     *
     * @return array Address info (associative array).
     */
    public function getAddressArray()
    {
        return array(
            'company'        => $this->_company,
            'firstName'      => $this->_firstName,
            'middleName'     => $this->_middleName,
            'lastName'       => $this->_lastName,
            'addressLineOne' => $this->_addressLineOne,
            'addressLineTwo' => $this->_addressLineTwo,
            'city'           => $this->_city,
            'state'          => $this->_state,
            'zip'            => $this->_zip,
            'email'          => $this->_email,
            'phoneNumbers'   => $this->_phoneNumbers
        );
    }

    // FIXME: Document me.
    protected function _initialize($addressBlock, $mode)
    {
        /* Set some safe default values. */
        $this->_company        = '';
        $this->_firstName      = '';
        $this->_middleName     = '';
        $this->_lastName       = '';
        $this->_addressLineOne = '';
        $this->_addressLineTwo = '';
        $this->_city           = '';
        $this->_state          = '';
        $this->_zip            = '';
        $this->_email          = '';
        $this->_phoneNumbers   = array();

        /* Clear out any old data if this is not our first run this
         * instance.
         */
        $this->_addressBlock = array();

        /* Remove blank or space-only lines from address block. */
        $addressBlock = StringUtility::removeEmptyLines($addressBlock);

        /* Split address block into an array indexed by line number. */
        $addressBlockArray = explode("\n", $addressBlock);

        /* Trim whitespace/etc. from each line in the array. */
        $addressBlockArray = array_map(
            array($this, '_cleanAddressLine'),
            $addressBlockArray
        );

        $this->_addressBlock = $addressBlockArray;
        $this->_mode = $mode;
    }
    
    // FIXME: Document me.
    protected function _isStreetAddress($string)
    {
        /* Phone number matching is pretty solid. Return false if this is
         * a phone number. We do this to rule out other parts of an address
         * block that start with numbers.
         */
        if (StringUtility::containsPhoneNumber($string))
        {
            return false;
        }

        /* Array of regular expressions that a "written-out" english number
         * can begin with.
         */
        $validAddressPrefixes = array(
            'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight',
            'nine', 'ten', 'eleven', 'twelve', '.{3,5}teen', 'twenty',
            'thirty', 'fourty', 'fifty', 'seventy', 'eighty', 'ninety',
            'PO', 'P\.O\.', 'Post Office', 'Postal', 'Rural'
        );
        
        /* Build a regular expression to match street addresses. */
        $regex = '/^(?:\d+(?:-\d+)?,? |(?:'
            . implode('|', $validAddressPrefixes)
            . ').* |R\.?\s*R\.?\s*\d+)/i';
            
        /* Does it match? */
        if (!preg_match($regex, $string))
        {
            return false;
        }

        return true;
    }

    /**
     * Returns true if the string appears to be a "City, State zip" address line . Leading /
     * trailing text is expected to be stripped prior to call.
     *
     * @param string String to test.
     * @return boolean Is this string most likely a city, state, and address?
     */
    protected static function _isCityStateZip($string)
    {
        $statePrefixes = array(
            'new', 'north', 'south', 'west', 'rhode', 'district\sof', 'puerto'
        );
        
        $cityStateZip = '[a-z\s.-]+[;,\s-]+(?:(?:'
            . implode('|', $statePrefixes)
            . ')\s+)?[a-z]{2,}[;.,\s-]+\d{5}(-\d{4})?';

        $POBox = 'P(?:ost|\.)?\s*O(?:ffice|\.)?\s+Box\s+';
        
        if (preg_match('/^' . $cityStateZip . '$/i', $string) &&
            !preg_match('/^' . $POBox . '/i', $string))
        {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the string appears to be a company name line. Leading /
     * trailing text is expected to be stripped prior to call.
     *
     * @param string String to test.
     * @return boolean Is this string most likely a company name?
     */
    protected function _isCompanyName($string)
    {
        $company = ',?\s+(?:Inc|LLC|GmbH|Ltd|Co|Company|Corp|Corporation|Enterprises)\b';
        $title = '\b(?:Manager|Director)\b';
        
        if (preg_match('/' . $company . '/i', $string) &&
            !preg_match('/' . $title . '/i', $string))
        {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the string could be a full name.
     *
     * @param string String to test.
     * @return boolean Is this string likely to be a full name?
     */
    protected function _isFullName($string)
    {
        $matchFirstName  = '[a-z.\x27-]+';      /* \x27 is a single quote ('). */
        $matchLastName   = '[a-z.\x27-]+';      /* \x27 is a single quote ('). */
        $matchMiddleName = '[a-z.\x27-]+\.?';   /* \x27 is a single quote ('). */

        /* Match either Lastname, Firstname M.I. or Firstname M.I. Lastname. */
        $matchFirstMILast = '(?:' . $matchFirstName . '\s+)+' . $matchMiddleName . '?\s*' . $matchLastName;
        $matchLastFirstMI = $matchLastName . '\s*,\s*' . '(?:' . $matchFirstName . '?\s*)+' . $matchMiddleName;

        /* Heuristics to make sure we aren't matching a company name. */
        $company = ',?\s+(?:Inc|LLC|Ltd|Corp(?:\b|oration))\b';
        $company = ',?\s(?:Inc|LLC|GmbH|Ltd|Co|Company|Corp|Corporation|Enterprises)\b';
        
        if (preg_match('/^(?:' . $matchFirstMILast . '|' . $matchLastFirstMI . ')$/i', $string) &&
            !preg_match('/' . $company . '/i', $string))
        {
            return true;
        }

        return false;
    }

    protected function _getFullNameArray($addressOneLineOffset)
    {
        /* Safe default values. */
        $fullNameArray['firstName']  = '';
        $fullNameArray['middleName'] = '';
        $fullNameArray['lastName']   = '';

        /* Sanity check. It is possible that the only line of the address
         * block has been removed during e-mail address extraction.
         */
        if (empty($this->_addressBlock))
        {
            return $fullNameArray;
        }

        if (($addressOneLineOffset - 2) >= 0 && StringUtility::isEmailAddress(
            $this->_addressBlock[$addressOneLineOffset - 1]))
        {
            $possibleFirstName = $this->_addressBlock[$addressOneLineOffset - 2];
        }
        else if (($addressOneLineOffset - 1) >= 0)
        {
            $possibleFirstName = $this->_addressBlock[$addressOneLineOffset - 1];
        }
        else
        {
            $possibleFirstName = $this->_addressBlock[0];
        }

        /* If our best guess is not possibly a full name, abort. */
        if (!$this->_isFullName($possibleFirstName))
        {
            return $fullNameArray;
        }
        
        $fullName = $possibleFirstName;

        /* Is it Lastname, Firstname; or Firstname Lastname? */
        if (preg_match('/^[A-Za-z.\x27-]+\s*,/', $fullName))
        {
            $lastCommaFirst = true;
        }
        else
        {
            $lastCommaFirst = false;
        }

        /* Count the number of "words" / tokens in the name. */
        $tokenCount = StringUtility::countTokens(", \t", $fullName);
        if (!$tokenCount)
        {
            return $fullNameArray;
        }

        $tokens = StringUtility::tokenize(", \t", $fullName);
        switch ($tokenCount)
        {
            case '2':
                /* Flip firstName and lastName around if we're in "last,
                 * first" mode.
                 */
                $fullNameArray['firstName']  = $tokens[($lastCommaFirst ? 1 : 0)];
                $fullNameArray['lastName']   = $tokens[($lastCommaFirst ? 0 : 1)];
                break;

            case '3':
                /* Reorder everything if we're in "last, first" mode. */
                $fullNameArray['firstName']  = $tokens[($lastCommaFirst ? 1 : 0)];
                $fullNameArray['middleName'] = $tokens[($lastCommaFirst ? 2 : 1)];
                $fullNameArray['lastName']   = $tokens[($lastCommaFirst ? 0 : 2)];
                break;

            default:
                if ($lastCommaFirst)
                {
                    /* Assume that the first token is the last name, the last token
                     * is the middle name, and the tokens inbetween are the first name.
                     */
                    $fullNameArray['firstName']  = ArrayUtility::implodeRange(
                        ' ', $tokens, 1, ($tokenCount - 2)
                    );
                    $fullNameArray['middleName'] = $tokens[$tokenCount - 1];
                    $fullNameArray['lastName']   = $tokens[0];
                }
                else
                {
                    /* Assume that the last token is the last name, the token before
                     * the last token is the middle name, and all other preceding
                     * tokens are part of the first name.
                     */
                    $fullNameArray['firstName']  = ArrayUtility::implodeRange(
                        ' ', $tokens, 0, ($tokenCount - 3)
                    );
                    $fullNameArray['middleName'] = $tokens[$tokenCount - 2];
                    $fullNameArray['lastName']   = $tokens[$tokenCount - 1];
                }
                break;
        }

        return $fullNameArray;
    }

    // FIXME: Document me.
    protected function _getCityStateZipArray($cityStateZipLine)
    {
        /* Safe default values. */
        $city  = '';
        $state = '';
        $zip   = '';
        
        /* Count the number of "words" / tokens in the line. */
        $tokenCount = StringUtility::countTokens(";, \t", $cityStateZipLine);
        if ($tokenCount < 2)
        {
            return array('city' => '', 'state' => '', 'zip' => '');
        }
        
        /* Split the string into an array of tokens. */
        $tokens = StringUtility::tokenize(";, \t", $cityStateZipLine);
        if ($tokenCount == 3)
        {
            $city  = $tokens[0];
            $state = $tokens[1];
            $zip   = $tokens[2];
        }
        else
        {
            /* If we have a known two- or three-word state, recognize it. */
            $twoWordState = ArrayUtility::implodeRange(
                ' ', $tokens, ($tokenCount - 3), ($tokenCount - 2)
            );
            $threeWordState = ArrayUtility::implodeRange(
                ' ', $tokens, ($tokenCount - 4), ($tokenCount - 2)
            );
            
            /* Known two- and three- word states / provinces. */
            $twoWordStates = array(
                'New Hampshire',
                'New York',
                'New Jersey',
                'New Mexico',
                'North Dakota',
                'North Carolina',
                'South Dakota',
                'South Carolina',
                'West Virginia',
                'Rhode Island',
                
                'American Samoa',
                'Puerto Rico',
                
                'British Columbia',
                'New Brunswick',
                'Nova Scotia',
                'Northwest Territories',
                
                /* Account for spelling errors. */
                'South Dekota',
                'North Dekota',
                'Rode Island',
                'New Hamshire'
            );
            $threeWordStates = array(
                'District Of Columbia',
                'Prince Edward Island'
            );
            
            /* Do we have a two-word state? */
            if (in_array(ucwords($twoWordState), $twoWordStates))
            {
                /* Yes; assume that the last token is the zip code, the two
                 * proceeding tokens before are part of the state, and all
                 * other preceding tokens are part of the city.
                 */
                $city  = ArrayUtility::implodeRange(
                    ' ', $tokens, 0, ($tokenCount - 4)
                );
                $state = $twoWordState;
                $zip   = $tokens[$tokenCount - 1];
            }
            
            /* If we didn't find a two-word state and we have enough words for
             * there to be a three-word state, check for one.
             */
            else if ($tokenCount > 4 &&
                     in_array(ucwords($threeWordState), $threeWordStates))
            {
                /* Yes; assume that the last token is the zip code, the
                 * three proceeding tokens before are part of the state,
                 * and all other preceding tokens are part of the city.
                 */
                $city  = ArrayUtility::implodeRange(
                    ' ', $tokens, 0, ($tokenCount - 5)
                );
                $state = $threeWordState;
                $zip   = $tokens[$tokenCount - 1];
            }
            
            /* Otherwise, assume a one word state with extra words belonging
             * to the city.
             */
            else
            {
                /* Assume that the last token is the zip code, the token before
                 * the last token is the state, and all other preceding tokens
                 * are part of the city.
                 */
                $city  = ArrayUtility::implodeRange(
                    ' ', $tokens, 0, ($tokenCount - 3)
                );
                $state = $tokens[$tokenCount - 2];
                $zip   = $tokens[$tokenCount - 1];
            }
        }
    
        /* Regular expression to match US state abbreviations. */
        $USStateABBR = 'A[AEKLPRSZ]|C[AOT]|D[CE]|F[LM]|G[AU]|HI|I[ADLN]'
            . '|K[SY]|LA|M[ADEHINOPST]|N[CDEH]|N[JMVY]|O[HKR]|P[ARW]|RI'
            . '|S[CD]|T[NX]|UT|V[AIT]|W[AIVY]';
            
        /* If the state is a United States Postal Service state abbreviation,
         * we can apply additional formatting.
         */
        if (!empty($state) && strlen($state) == 3 &&
            preg_match('/^(' . $USStateABBR . ')\.$/i', $state))
        {
            $state = strtoupper(substr($state, 0, 2));
        }

        /* Common spelling corrections. */
        $search = array(
            'South Dekota',
            'North Dekota',
            'Rode Island',
            'New Hamshire'
        );
        $replace = array(
            'South Dakota',
            'North Dakota',
            'Rhode Island',
            'New Hampshire'
        );
        $state = str_replace($search, $replace, $state);
        
        // FIXME: Convert US state names to abbreviations.
        // FIXME: Proper title case.
        return array(
            'city'  => ucwords($city),
            'state' => ucwords($state),
            'zip'   => strtoupper($zip)
        );
    }

    // FIXME: Document me.
    protected function _extractEmailAddress()
    {
        foreach ($this->_addressBlock as $lineNumber => $line)
        {
            if (!StringUtility::containsEmailAddress($line))
            {
                continue;
            }

            /* Extract and properly format the e-mail address. */
            $emailAddress = StringUtility::extractEmailAddress($line);

            /* If there is more on this line, remove the e-mail address from
             * the line. Otherwise, just delete the line.
             */
            if (!StringUtility::isEmailAddress($line))
            {
                $line = StringUtility::removeEmailAddress($line, true);
                $this->_addressBlock[$lineNumber] = $line;
            }
            else
            {
                unset($this->_addressBlock[$lineNumber]);
                $this->_addressBlock = array_merge(
                    $this->_addressBlock
                );
            }

            return $emailAddress;
        }

        return '';
    }

    // FIXME: Document me.
    protected function _getPhoneNumbers()
    {
        /* Sanity check. It is possible that the only line of the address
         * block has been removed during e-mail address extraction.
         */
        if (empty($this->_addressBlock))
        {
            return array();
        }

        $unknownNumbers = array();
        $numbers = array();
        
        /* Loop through each line of the address block and attempt to extract
         * and identify phone numbers.
         */
        foreach ($this->_addressBlock as $lineNumber => $line)
        {
            /* Skip lines that don't contain phone numbers. */
            if (!StringUtility::containsPhoneNumber($line))
            {
                continue;
            }
            
            /* Regular expressions to help identify phone number types. */
            $cell    = '/cell|[\x28\x5b][CM][\x29\x5d]|mob(:?ile|\b)|\bc[:\x5d]|\bm[:\x5d]/i';
            $home    = '/[\x28\x5b]H[\x29\x5d]|home|evening|night|house/i';
            $work    = '/work|off(:?ice|\b)|[\x28\x5b][WO][\x29\x5d]|direct|day(?:time)?|job/i';
            $general = '/[\x28\x5b]PH?[\x29\x5d]|primary|voice|main|toll|ph(:?one|\b)/i';
            $fax     = '/[\x28\x5b]FX?[\x29\x5d]|fax|facsimile|\bFX?[:\x5d]/i';
            $tty     = '/\bTT[YD]\b/i';
            $pager   = '/pager|beeper/i';

            /* Look for keywords that might tell us what type of number it is.
             * First check to see if the line is ONLY a phone number. If not,
             * try do identify what kind of phone number it is.
             *
             * \x28 is a '(', \x5b is a '[', \x29 is a ')', \x5d is a ']'.
             */
            if (preg_match($cell, $line))
            {
                $numbers[] = array(
                    'number' => StringUtility::extractPhoneNumber($line),
                    'type'   => 'cell'
                );
            }
            else if (preg_match($home, $line))
            {
                $numbers[] = array(
                    'number' => StringUtility::extractPhoneNumber($line),
                    'type'   => 'home'
                );
            }
            else if (preg_match($work, $line))
            {
                $numbers[] = array(
                    'number' => StringUtility::extractPhoneNumber($line),
                    'type'   => 'work'
                );
            }
            else if (preg_match($general, $line))
            {
                if ($this->_mode != ADDRESSPARSER_MODE_COMPANY)
                {
                    $unknownNumbers[] = StringUtility::extractPhoneNumber($line);
                }
                else
                {
                    $numbers[] = array(
                        'number' => StringUtility::extractPhoneNumber($line),
                        'type'   => 'general'
                    );
                }
            }
            else if (preg_match($fax, $line))
            {
                $numbers[] = array(
                    'number' => StringUtility::extractPhoneNumber($line),
                    'type'   => 'fax'
                );
            }
            else if (preg_match($tty, $line))
            {
                $numbers[] = array(
                    'number' => StringUtility::extractPhoneNumber($line),
                    'type'   => 'tty'
                );
            }
            else if (preg_match($pager, $line))
            {
                $numbers[] = array(
                    'number' => StringUtility::extractPhoneNumber($line),
                    'type'   => 'pager'
                );
            }
            else if (StringUtility::isPhoneNumber($line))
            {
                /* In this case, the line contains only a phone number, and is
                 * truely unknown.
                 */
                $unknownNumbers[] = StringUtility::extractPhoneNumber($line);
            }
            else
            {
                /* In this case, the line contains other data besides just a
                 * phone number. We just can't identify it as anything.
                 */
                $unknownNumbers[] = StringUtility::extractPhoneNumber($line);
            }
        }

        /* Figure out which phone number types we've already found. We'll
         * use this below.
         */
        $homePhoneRow = ResultSetUtility::findRowByColumnValue(
            $numbers, 'type', 'home'
        );
        $workPhoneRow = ResultSetUtility::findRowByColumnValue(
            $numbers, 'type', 'work'
        );
        $cellPhoneRow = ResultSetUtility::findRowByColumnValue(
            $numbers, 'type', 'cell'
        );
            
        /* Did we find any unknown phone numbers? If so, we have to try to
         * guess their types.
         */
        $unknownCount = count($unknownNumbers);
        if ($unknownCount == 1)
        {   
            /* If we're only missing one of the three phone number types, and we
             * found a number on a line by itself, we will assume that the extra
             * number is one of the missing ones.
             *
             * If we don't have a work number, but we have a home number
             * and a cell number, this is probably a work number.
             */
            if ($workPhoneRow === false && $homePhoneRow !== false &&
                $cellPhoneRow !== false)
            {
                $numbers[] = array(
                    'number' => $unknownNumbers[0],
                    'type'   => 'work'
                );
            }
            /* If we don't have a home number, but we have a work number
             * and a cell number, this is probably a home number.
             */
            else if ($homePhoneRow === false && $workPhoneRow !== false &&
                $cellPhoneRow !== false)
            {
                $numbers[] = array(
                    'number' => $unknownNumbers[0],
                    'type'   => 'home'
                );
            }
            /* If we don't have a cell number, but we have a work number
             * and a home number, this is probably a cell number.
             */
            else if ($cellPhoneRow === false && $workPhoneRow !== false &&
                $homePhoneRow !== false)
            {
                $numbers[] = array(
                    'number' => $unknownNumbers[0],
                    'type'   => 'cell'
                );
            }
            else if ($cellPhoneRow !== false && $workPhoneRow !== false &&
                $homePhoneRow !== false)
            {
                /* We already know all the phone numbers we need to know, and
                 * it's probably not a fax number, as fax numbers are usually
                 * labeled. Nothing to do except mark it as unknown.
                 */
                $numbers[] = array(
                    'number' => $unknownNumbers[0],
                    'type'   => 'unknown'
                );
            }
            else
            {
                /* We have more than one phone number missing. We will make a
                 * "best guess" according to the mode we are in.
                 */
                switch ($this->_mode)
                {
                    case ADDRESSPARSER_MODE_PERSON:
                        if ($homePhoneRow === false)
                        {
                            $type = 'home';
                        }
                        else if ($cellPhoneRow === false)
                        {
                            $type = 'cell';
                        }
                        else if ($workPhoneRow === false)
                        {
                            $type = 'work';
                        }
                        else
                        {
                            $type = 'unknown';
                        }
                        break;
                        
                    case ADDRESSPARSER_MODE_CONTACT:
                        /* 'Contacts' are more likely to list a work or cell
                         * number than a home number.
                         */
                        if ($workPhoneRow === false)
                        {
                            $type = 'work';
                        }
                        else if ($cellPhoneRow === false)
                        {
                            $type = 'cell';
                        }
                        else if ($homePhoneRow === false)
                        {
                            $type = 'home';
                        }
                        else
                        {
                            $type = 'unknown';
                        }
                        break;
                        
                    case ADDRESSPARSER_MODE_COMPANY:
                        // FIXME: Here we should be looking for "general".
                        // We could also have two phone phone numbers.
                        $type = 'general';
                        break;
                        
                    default:
                        /* Error! Invalid mode. */
                        $type = 'unknown';
                        break;
                }
                
                $numbers[] = array(
                    'number' => $unknownNumbers[0],
                    'type'   => $type
                );
            }
        }
        else if ($unknownCount > 1)
        {
            // FIXME
        }

        return $numbers;
    }

    // FIXME: Document me.
    protected function _cleanAddressLine($line)
    {
        $line = trim($line);
        $line = rtrim($line, ',');
        $line = preg_replace('/^(?:Address|Name):?\s/', '', $line);

        return $line;
    }
}

?>
