<?php
/**
 * CATS
 * String Utility Library
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
 * @version    $Id: StringUtility.php 3587 2007-11-13 03:55:57Z will $
 */

/**
 *  String Utility Library
 *  @package    CATS
 *  @subpackage Library
 */
class StringUtility
{
    const matchPHSeparator    = '[\s\/.-]*';                            /* PCRE */
    const matchPHCountryCode  = '[(]?[+]?\d{0,3}[)]?';                  /* PCRE */
    const matchPHECountryCode = '[(]?[+]?(?P<countryCode>\d{0,3})[)]?'; /* PCRE */
    const matchPHAreaCode     = '[(]?[2-9]{1}\d{2}[)]?';                /* PCRE */
    const matchPHEAreaCode    = '[(]?(?P<areaCode>[2-9]{1}\d{2})[)]?';  /* PCRE */
    const matchPHExchange     = '\d{3}';                                /* PCRE */
    const matchPHEExchange    = '(?P<exchange>\d{3})';                  /* PCRE */
    const matchPHNumber       = '\d{4}';                                /* PCRE */
    const matchPHENumber      = '(?P<number>\d{4})';                    /* PCRE */

    const matchPHExtension  = '([(]?(?:e?xt?(?:ension|)|#|[*]|)[)]?[\s\/.-]*\d{1,6}[)]?)?';                /* PCRE */
    const matchPHEExtension = '([(]?(?:e?xt?(?:ension|)|#|[*]|)[)]?[\s\/.-]*(?P<extension>\d{1,6})[)]?)?'; /* PCRE */

    const matchEmailDot   = '(?:\.|\s*\(?\[?dot\)?\]?\s*)';                             /* PCRE */
    const matchEmailAt    = '(?:@|\s*\(?\[?at\)?\]?\s*)';                               /* PCRE */
    const matchEmailTLD   = '[a-z]{2,}';                                                /* PCRE */
    const matchEmailETLD  = '(?P<tld>[a-z]{2,})';                                       /* PCRE */
    const matchEmailHost  = '([a-z0-9_-]+(?:\.|\s*\(?\[?dot\)?\]?\s*)?)+';              /* PCRE */
    const matchEmailEHost = '(?P<host>(?:[a-z0-9_-]+(?:\.|\s*\(?\[?dot\)?\]?\s*)?)+)';  /* PCRE */
    const matchEmailUser  = '[a-z0-9._-]+';                                             /* PCRE */
    const matchEmailEUser = '(?P<user>[a-z0-9._-]+)';                                   /* PCRE */

    const matchURLDomain  = '(?:localhost|(?:\d{1,3}\.){3}\d{1,3}|(?:[a-z\d-]+\.)*[a-z\d-]+\.[a-z]{2,6})';         /* PCRE */
    const matchEURLDomain = '(?P<domain>localhost|(?:\d{1,3}\.){3}\d{1,3}|(?:[a-z\d-]+\.)*[a-z\d-]+\.[a-z]{2,6})'; /* PCRE */

    const matchEURLProtocol     = '(?:(?P<protocol>[a-z]+)(?:\:\/\/))?';                           /* PCRE */
    const matchEURLUserPassword = '(?:(?P<user>[a-z\d.-]+)(?:\:(?P<password>[a-z&%\$\d.-]+))*@)?'; /* PCRE */
    const matchEURLPort         = '\:?(?P<port>\d+)?';                                             /* PCRE */
    const matchEURLExtras       = '(?P<extras>[\/][a-z\d.,\x27?\/+&%\$#=~_@-]*)*';                 /* PCRE */

    const matchURLProtocol     = '(?:[a-z]+\:\/\/|)';                     /* PCRE */
    const matchURLUserPassword = '(?:[a-z\d.-]+(?:\:[a-z&%\$\d.-]+)*@|)'; /* PCRE */
    const matchURLPort         = '\:?(\d+)?';                             /* PCRE */
    const matchURLExtras       = '([\/][a-z\d.,\x27?\/+&%\$#=~_@-]*)*';   /* PCRE */


    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}


    /**
     * Returns true if the string appears to be a phone number. Leading /
     * trailing text is expected to be stripped prior to call.
     *
     * @param string to test
     * @return boolean is most likely a phone number
     */
    public static function isPhoneNumber($string)
    {
        if (preg_match('/^'
            . self::matchPHCountryCode . self::matchPHSeparator . self::matchPHAreaCode
            . self::matchPHSeparator   . self::matchPHExchange  . self::matchPHSeparator
            . self::matchPHNumber      . self::matchPHSeparator . self::matchPHExtension
            . '$/i', $string))
        {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the string appears to contain a phone number.
     *
     * @param string to test
     * @return boolean most likely contains a phone number
     */
    public static function containsPhoneNumber($string)
    {
        if (preg_match('/'
            . self::matchPHCountryCode . self::matchPHSeparator . self::matchPHAreaCode
            . self::matchPHSeparator   . self::matchPHExchange  . self::matchPHSeparator
            . self::matchPHNumber      . self::matchPHSeparator . self::matchPHExtension
            . '/i', $string))
        {
            return true;
        }

        return false;
    }

    /**
     * Returns the first phone number that could be extracted from a string.
     *
     * @param string to test
     * @return string phone number or '' if not found
     */
    public static function extractPhoneNumber($string)
    {
        if (preg_match('/'
            . self::matchPHECountryCode . self::matchPHSeparator . self::matchPHEAreaCode
            . self::matchPHSeparator    . self::matchPHEExchange . self::matchPHSeparator
            . self::matchPHENumber      . self::matchPHSeparator . self::matchPHEExtension
            . '/i', $string, $matches))
        {
            //print_r($matches);

            /* Don't format international phone numbers. */
            if (!empty($matches['countryCode']) && ($matches['countryCode'] != '1'))
            {
                return $string;
            }

            $formattedPhoneNumber = sprintf(
                "%s-%s-%s",
                $matches['areaCode'],
                $matches['exchange'],
                $matches['number']
            );

            if (isset($matches['extension']) && !empty($matches['extension']))
            {
                $formattedPhoneNumber .= ' x ' . $matches['extension'];
            }

            return $formattedPhoneNumber;
        }

        return '';
    }

    /**
     * Returns an array all phone numbers that could be extracted from a
     * string. The array is of the following format:
     *
     *      array(
     *          [0] => array(
     *              'formatted'   => 'Formatted Phone Number'
     *              'unformatted' => 'Phone Number Exactly as Found'
     *          ),
     *          [1] => array(
     *              'formatted'   => 'Formatted Phone Number'
     *              'unformatted' => 'Phone Number Exactly as Found'
     *          ),
     *          ...
     *
     * @param string to test
     * @return array matches or empty array if not found
     */
    public static function extractAllPhoneNumbers($string)
    {
        $formattedPhoneNumbers = array();

        if (preg_match_all('/'
            . self::matchPHECountryCode . self::matchPHSeparator . self::matchPHEAreaCode
            . self::matchPHSeparator    . self::matchPHEExchange . self::matchPHSeparator
            . self::matchPHENumber      . self::matchPHSeparator . self::matchPHEExtension
            . '/i', $string, $matches, PREG_SET_ORDER))
        {
            //print_r($matches);

            foreach ($matches as $matchIndex => $match)
            {
                $formattedPhoneNumbers[$matchIndex]['unformatted'] = $match[0];

                $formattedPhoneNumbers[$matchIndex]['formatted'] = sprintf(
                    "%s-%s-%s",
                    $match['areaCode'],
                    $match['exchange'],
                    $match['number']
                );

                if (isset($match['extension']) && !empty($match['extension']))
                {
                    $formattedPhoneNumbers[$matchIndex]['formatted'] .= ' x ' . $match['extension'];
                }
            }
        }

        return $formattedPhoneNumbers;
    }

    /**
     * Returns true if the string appears to be an e-mail address. This also
     * supports '@' -> 'at', and '.' -> 'dot' anti-spam addresses.
     *
     * @param string to test
     * @return boolean is most likely a phone number
     */
    public static function isEmailAddress($string)
    {
        if (preg_match('/^' . self::matchEmailUser . self::matchEmailAt
            . self::matchEmailHost . self::matchEmailDot
            . self::matchEmailTLD . '$/i', $string))
        {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the string appears to contain an e-mail address. This
     * also supports '@' -> 'at', and '.' -> 'dot' anti-spam addresses.
     *
     * @param string to test
     * @return boolean is most likely a phone number
     */
    public static function containsEmailAddress($string)
    {
        if (preg_match('/' . self::matchEmailUser . self::matchEmailAt
            . self::matchEmailHost . self::matchEmailDot
            . self::matchEmailTLD . '/i', $string))
        {
            return true;
        }

        return false;
    }

    /**
     * Returns the first e-mail address that could be extracted from a line.
     *
     * @param string to test
     * @return string e-mail address or '' if none found
     */
    public static function extractEmailAddress($string)
    {
        if (preg_match('/' . self::matchEmailEUser . self::matchEmailAt
            . self::matchEmailEHost . self::matchEmailDot
            . self::matchEmailETLD . '/i', $string, $matches))
        {
            $formattedEmailAddress = sprintf(
                "%s@%s.%s",
                $matches['user'],
                $matches['host'],
                $matches['tld']
            );

            $formattedEmailAddress = preg_replace(
                '/' . self::matchEmailDot . '/i', '.', $formattedEmailAddress
            );

            return $formattedEmailAddress;
        }

        return '';
    }

    /**
     * Returns the given string with any e-mail addresses removed. If $trim is
     * true, trim() will be run on the string before return.
     *
     * @param string to work on
     * @param boolean trim the string after extraction?
     * @return string without e-mail addresses
     */
    public static function removeEmailAddress($string, $trim = false)
    {
        $string = preg_replace('/' . self::matchEmailUser . self::matchEmailAt
                . self::matchEmailHost . self::matchEmailDot
                . self::matchEmailTLD . '/i', '', $string);

        if ($trim)
        {
            $string = trim($string);
        }

        return $string;
    }

    /**
     * Returns true if the string could be a url.
     *
     * @param string to test
     * @return boolean is most likely a URL
     */
    public static function isURL($string)
    {
        if (preg_match('/^' . self::matchURLProtocol .
            self::matchURLUserPassword . self::matchURLDomain .
            self::matchURLPort . self::matchURLExtras . '$/i',
            $string, $matches))
        {
            return true;
        }

        return false;
    }

    /**
     * Returns the first URL address that could be extracted from a line.
     *
     * @param string to test
     * @return string URL or '' if none found
     */
    public static function extractURL($string)
    {
        if (preg_match('/^(|.*\s+)' . self::matchEURLProtocol .
            self::matchEURLUserPassword . self::matchEURLDomain .
            self::matchEURLPort . self::matchEURLExtras .
            '/i', $string, $matches))
        {
            if (!empty($matches['protocol']))
            {
                $protocol = $matches['protocol'];
            }
            else
            {
                $protocol = 'http';
            }

            if (!empty($matches['user']))
            {
                if (!empty($matches['password']))
                {
                    $userPassword = $matches['user'] . ':' .
                                    $matches['password'] . '@';
                }
                else
                {
                    $userPassword = $matches['user'] . '@';
                }
            }
            else
            {
                $userPassword = '';
            }

            if (isset($matches['port']) && !empty($matches['port']) &&
                $matches['port'] != 80)
            {
                $port = ':' . $matches['port'];
            }
            else
            {
                $port = '';
            }

            if (isset($matches['extras']) && !empty($matches['extras']))
            {
                $extras = $matches['extras'];
            }
            else
            {
                $extras = '/';
            }

            $formattedURL = sprintf(
                "%s://%s%s%s%s",
                $protocol,
                $userPassword,
                $matches['domain'],
                $port,
                $extras
            );

            return $formattedURL;
        }

        return '';
    }

    /**
     * Checks if a string is a valid IP address.
     *
     * @param string string to test
     * @return boolean is valid IP address
     */
     public static function isIPAddress($string)
     {
         $octet = '(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])';

         if (preg_match('/^' . $octet . '\.' . $octet . '\.' . $octet . '\.' . $octet . '$/', $string))
         {
             return true;
         }

         return false;
     }

    /**
     * Removes all blank lines from a string.
     *
     * @return string cleaned string
     */
    public static function removeEmptyLines($string)
    {
        $string = preg_replace("/[\r\n]+[\s\t]*[\r\n]+/", "\n", $string);
        $string = preg_replace("/^[\s\t]*[\r\n]+/", "", $string);
        $string = trim($string);

        return $string;
    }

    /**
     * Counts the number of 'words' / tokens in a string delimeted by a
     * character in $splitCharacters.
     *
     * @return int tokens in string
     */
    public static function countTokens($splitCharacters, $string)
    {
        if (!strtok($string, $splitCharacters))
        {
            return 0;
        }

        $tokenCount = 1;
        while (strtok($splitCharacters))
        {
            ++$tokenCount;
        }

        return $tokenCount;
    }

    /**
     * Splits a string into an array by 'words' / tokens delimeted by a
     * character in $splitCharacters.
     *
     * @param string delimiter characters used to split the string into tokens
     * @param string string to split into tokens
     * @return array tokens or an empty array if no tokens are found
     */
    public static function tokenize($splitCharacters, $string)
    {
        $tokenIndex = 0;

        $tokens[$tokenIndex] = strtok($string, $splitCharacters);

        if ($tokens[$tokenIndex] === false || empty($tokens[$tokenIndex]))
        {
            return array();
        }

        while (true)
        {
            ++$tokenIndex;

            $tempToken = strtok($splitCharacters);

            if ($tempToken === false || empty($tempToken))
            {
                break;
            }
            else
            {
                $tokens[$tokenIndex] = $tempToken;
            }
        }

        return $tokens;
    }

    /**
     * Makes a first name and last name into a first name and last initial
     * (with an optional maximum length).
     *
     * @return formatted first initial and last name.
     */
    public static function makeInitialName($firstName, $lastName,
        $lastCommaFirst = false, $maxLength = 1000)
    {
        if (empty($firstName) && empty($lastName))
        {
            return '';
        }

        if ($lastCommaFirst)
        {
            $firstInitial = $firstName{0} . '.';

            if (strlen($lastName) > $maxLength)
            {
                return ucwords(
                    substr($lastName, 0, $maxLength) . ', ' . $firstInitial
                );
            }

            return ucwords($lastName . ', ' . $firstInitial);
        }

        $lastInitial = $lastName{0} . '.';

        if (strlen($firstName) > $maxLength)
        {
            return ucwords(
                substr($firstName, 0, $maxLength) . ' ' . $lastInitial
            );
        }

        return ucwords($firstName . ' ' . $lastInitial);
    }

    /**
     * Formats a city and state as "City, State" if neither are empty, "State"
     * if city is empty, or "City" if state is empty.
     *
     * @return formatted city and state
     */
    public static function makeCityStateString($city, $state)
    {
        $city  = trim($city);
        $state = trim($state);

        if (!empty($city))
        {
            $string = $city;

            if (!empty($state))
            {
                $string .= ', ' . $state;
            }

            return $string;
        }

        return $state;
    }

    /**
     * Returns the specified string in quoted-printable encoding according to
     * RFC 2045 (http://www.faqs.org/rfcs/rfc2045).
     *
     * Slightly modified from documentation comment on php.net by bendi (at)
     * interia (dot) pl (http://us2.php.net/quoted_printable_decode).
     *
     * @return string encoded string
     */
    public static function quotedPrintableEncode($string)
    {
        $string = preg_replace_callback(
            '/[^\x21-\x3C\x3E-\x7E\x09\x20]/',
            function($result) {
                return sprintf("=%02X", ord($result[0])); 
            },
            $string
        );
       /* Prevent the splitting of lines from interfering with escaped
        * characters.
        */
       preg_match_all('/.{1,73}([^=]{0,3})?/', $string, $matches);

       return implode("=\r\n", $matches[0]);
    }

    /**
     * Returns the specified string with any single-quotes (') escaped (\').
     *
     * @param string unescaped string
     * @return string escaped string
     */
    public static function escapeSingleQuotes($string)
    {
        return str_replace("'", "\'", $string);
    }

    /* Converts a single CSV line to an array. Note that this cannot handle
     * escaped double-quotes inside double quotes (FIXME).
     */
    //FIXME: Document me.
    public static function CSVLineToArray($string)
    {
        $string .= '';
        $string = trim($string);

        if (empty($string))
        {
            return array();
        }

        $results = preg_split(
            '/,(?=(?:[^"]*"[^"]*")*(?![^"]*"))/',
            trim($string)
        );

        return preg_replace('/^"(.*)"$/', '\1', $results);
    }

    /* Implemented like JS substring function. */
    public static function JSSubString($string, $start, $end)
    {
        return substr($string, $start, ($end - $start));
    }

    /**
     * Functions like str_replace() except only the first occurrance of
     * $needle will be replaced. Array parameters are not supported.
     *
     * @param string needle
     * @param string replacement string
     * @param string haystack
     * @return string replaced string
     */
    public static function replaceOnce($needle, $replace, $haystack)
    {
        $potision = strpos($haystack, $needle);

        if ($potision === false)
        {
            return $haystack;
        }

        return substr_replace($haystack, $replace, $potision, strlen($needle));
    }

    public static function cardinal($x)
    {
        if ($x <= 0) return 'zero';
        $y = ($x % 20);

        if ($x >= 10000000) return number_format(strval($x),0);

        if (($y=floor($x/1000000)) > 0 && $y <= 9)
        {
            $val = '';
            $z = $x - ($y*1000000);
            if ($z > 0) $val = ' ' . StringUtility::cardinal($z);
            return StringUtility::cardinal($y) . ' million' . $val;
        }

        if (($y=floor($x/1000)) > 0 && $y <= 999)
        {
            $val = '';
            $z = $x - ($y*1000);
            if ($z > 0) $val = ' ' . StringUtility::cardinal($z);
            return StringUtility::cardinal($y) . ' thousand' . $val;
        }

        if (($y=floor($x/100)) > 0 && $y <= 9)
        {
            $val = '';
            $z = $x - ($y*100);
            if ($z > 0) $val = ' and ' . StringUtility::cardinal($z);
            return StringUtility::cardinal($y) . ' hundred' . $val;
        }

        switch($y=floor($x/10))
        {
            case 2:
                $val = 'twenty';
                if (($z = $x % ($y*10)) > 0 && $z <= 9) $val .= ' ' . StringUtility::cardinal($z);
                return $val;
            case 3:
                $val = 'thirty';
                if (($z = $x % ($y*10)) > 0 && $z <= 9) $val .= ' ' . StringUtility::cardinal($z);
                return $val;
            case 4:
                $val = 'fourty';
                if (($z = $x % ($y*10)) > 0 && $z <= 9) $val .= ' ' . StringUtility::cardinal($z);
                return $val;
            case 5:
                $val = 'fifty';
                if (($z = $x % ($y*10)) > 0 && $z <= 9) $val .= ' ' . StringUtility::cardinal($z);
                return $val;
            case 6:
                $val = 'sixty';
                if (($z = $x % ($y*10)) > 0 && $z <= 9) $val .= ' ' . StringUtility::cardinal($z);
                return $val;
            case 7:
                $val = 'seventy';
                if (($z = $x % ($y*10)) > 0 && $z <= 9) $val .= ' ' . StringUtility::cardinal($z);
                return $val;
            case 8:
                $val = 'eighty';
                if (($z = $x % ($y*10)) > 0 && $z <= 9) $val .= ' ' . StringUtility::cardinal($z);
                return $val;
            case 9:
                $val = 'ninety';
                if (($z = $x % ($y*10)) > 0 && $z <= 9) $val .= ' ' . StringUtility::cardinal($z);
                return $val;
        }

        switch($x)
        {
            case 1: return 'one';
            case 2: return 'two';
            case 3: return 'three';
            case 4: return 'four';
            case 5: return 'five';
            case 6: return 'six';
            case 7: return 'seven';
            case 8: return 'eight';
            case 9: return 'nine';
            case 10: return 'ten';
            case 11: return 'eleven';
            case 12: return 'twelve';
            case 13: return 'thirteen';
            case 14: return 'fourteen';
            case 15: return 'fifteen';
            case 16: return 'sixteen';
            case 17: return 'seventeen';
            case 18: return 'eighteen';
            case 19: return 'nineteen';
        }
    }
}

?>
