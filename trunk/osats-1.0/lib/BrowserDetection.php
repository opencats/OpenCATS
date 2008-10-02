<?php
/**
 * CATS
 * CATS Browser Detection Library
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
 * @version    $Id: BrowserDetection.php 3587 2007-11-13 03:55:57Z will $
 */

/**
 *	Browser Detection Library
 *	@package    CATS
 *	@subpackage Library
 */
class BrowserDetection
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}


    /**
     * Detects a web browser's name and version based on the UserAgent string.
     * Based on code by Geoffrey Sneddon, with some help from Clayton Smith.
     *
     * Returns an array of the format:
     *     array('name' => browser name, 'version' => browser version);
     *
     * See http://www.useragentstring.com/ for updating.
     *
     * @param string Browser user-agent string.
     * @return array Associative array of browser version data.
     */
    public static function detect($userAgent)
    {
        $userAgent = trim($userAgent);

        /* Blank User Agent */
        if (empty($userAgent))
        {
            return array(
                'name' => 'Masked', 'version' => ''
            );
        }

        /* Firefox - Test this before Mozilla. */
        if (stripos($userAgent, 'firefox') !== false)
        {
            preg_match('/Firefox\/([0-9\.]+)(\+)?/i', $userAgent, $b);
            unset($b[0]);

            return array(
                'name' => 'Firefox', 'version' => implode('', $b)
            );
        }

        /* AOL Browser - Test this before Internet Explorer. */
        if (stripos($userAgent, 'america online browser') !== false)
        {
            preg_match('/America Online Browser ([0-9\.]+)/i', $userAgent, $b);

            return array(
                'name' => 'America Online Browser', 'version' => $b[1]
            );
        }

        /* AOL Builtin Browser - Test this before Internet Explorer. */
        if (stripos($userAgent, 'AOL') !== false)
        {
            preg_match('/AOL ([0-9\.]+)/i', $userAgent, $b);

            return array(
                'name' => 'AOL', 'version' => $b[1]
            );
        }

        /* Internet Explorer - Test this before Mozilla. */
        if (stripos($userAgent, 'msie') !== false)
        {
            preg_match('/MSIE ([0-9\.]+)(b)?/i', $userAgent, $b);
            unset($b[0]);

            return array(
                'name' => 'Internet Explorer', 'version' => implode('', $b)
            );
        }

        /* Opera */
        if (stripos($userAgent, 'opera') !== false)
        {
            preg_match('/Opera(\/| )([0-9\.]+)(u)?(\d+)?/i', $userAgent, $b);
            unset($b[0], $b[1]);

            return array(
                'name' => 'Opera', 'version' => implode('', $b)
            );
        }

        /* Safari */
        if (stripos($userAgent, 'safari') !== false)
        {
            @preg_match('/Safari\/([0-9\.]+)/i', $userAgent, $matches);
            if (isset($matches[1]))
            {
                $build = $matches[1];
            }
            else
            {
                $build = 'Unknown';
            }

            if ($build[0] == '5')
            {
                @preg_match('/Version\/([0-9\.]+)/i', $userAgent, $matches);
                if (isset($matches[1]))
                {
                    $version = $matches[1];
                }
                else
                {
                    $version = 'Unknown';
                }
            }
            else
            { 
                switch ($build)
                {   
                    case '412':
                    case '412.2':
                    case '412.2.2':
                        $version = '2.0';
                        break;

                    case '412.5':
                        $version = '2.0.1';
                        break;

                    case '416.12':
                    case '416.13':
                        $version = '2.0.2';
                        break;

                    case '417.8':
                    case '417.9.2':
                    case '417.9.3':
                        $version = '2.0.3';
                        break;

                    case '419.3':
                        $version = '2.0.4';
                        break;

                    case '100':
                        $version = '1.1';
                        break;

                    case '100.1':
                        $version = '1.1.1';
                        break;

                    case '125.7':
                    case '125.8':
                        $version = '1.2.2';
                        break;

                    case '125.9':
                        $version = '1.2.3';
                        break;

                    case '125.11':
                    case '125.12':
                        $version = '1.2.4';
                        break;

                    case '312':
                        $version = '1.3';
                        break;

                    case '312.3':
                    case '312.3.1':
                    case '312.3.3':
                        $version = '1.3.1';
                        break;

                    case '312.5':
                    case '312.6':
                        $version = '1.3.2';
                        break;

                    case '85.5':
                        $version = '1.0';
                        break;

                    case '85.7':
                        $version = '1.0.2';
                        break;

                    case '85.8':
                    case '85.8.1':
                        $version = '1.0.3';
                        break;

                    default:
                        $version = 'Unknown (' . $build . ')';
                        break;
                }
            }
            
            return array(
                'name' => 'Safari', 'version' => $version
            );
        }

        /* Camino - Test this before Mozilla. */
        if (stripos($userAgent, 'camino') !== false)
        {
            preg_match('/Camino\/([0-9\.]+)(a|b)?(\d+)?(\+)?/i', $userAgent, $b);
            unset($b[0]);

            return array(
                'name' => 'Camino', 'version' => implode('', $b)
            );
        }

        /* Netscape - Test this before Mozilla. */
        if (stripos($userAgent, 'mozilla/4') !== false)
        {
            preg_match('/Mozilla\/([0-9\.]+)/i', $userAgent, $b);

            return array(
                'name' => 'Netscape', 'version' => $b[1]
            );
        }

        /* Konqueror */
        if (stripos($userAgent, 'konqueror') !== false)
        {
            preg_match('/Konqueror\/([0-9\.]+)(\-rc)?(\d+)?/i', $userAgent, $b);
            unset($b[0]);

            return array(
                'name' => 'Konqueror', 'version' => implode('', $b)
            );
        }

        /* SeaMonkey - Test this before Mozilla. */
        if (stripos($userAgent, 'seamonkey') !== false)
        {
            preg_match('/SeaMonkey\/([0-9\.]+)(a|b)?/i', $userAgent, $b);
            unset($b[0]);

            return array(
                'name' => 'SeaMonkey', 'version' => implode('', $b)
            );
        }

        /* Googlebot - Test this before Mozilla. */
        if (stripos($userAgent, 'googlebot') !== false)
        {
            preg_match('/Googlebot\/([0-9\.]+)/i', $userAgent, $b);
            unset($b[0]);

            return array(
                'name' => 'Googlebot', 'version' => implode('', $b)
            );
        }

        /* Yahoo Crawles - Test this before Mozilla. */
        if (stripos($userAgent, 'yahoo') !== false)
        {
             return array(
                'name' => 'Yahoo Crawler', 'version' => ''
            );
        }

        /* iCab - Test this before Mozilla. */
        if (stripos($userAgent, 'icab') !== false)
        {
            preg_match('/iCab(?: |\/)([0-9\.]+)/i', $userAgent, $b);

            return array(
                'name' => 'iCab', 'version' => $b[1]
            );
        }

        /* Mozilla */
        if (stripos($userAgent, 'mozilla/5') !== false ||
            stripos($userAgent, 'gecko') !== false)
        {
            preg_match('/rv(:| )([0-9\.]+)(a|b)?/i', $userAgent, $b);
            unset($b[0], $b[1]);

            return array(
                'name' => 'Mozilla', 'version' => implode('', $b)
            );
        }

        /* Bots */
        if (stripos($userAgent, 'bot') !== false ||
            stripos($userAgent, 'crawl') !== false)
        {
            return array(
                'name' => 'Bot', 'version' => 'Unknown'
            );
        }

        /* OmniWeb */
        if (stripos($userAgent, 'omniweb') !== false)
        {
            preg_match('/OmniWeb\/([0-9\.]+)/i', $userAgent, $b);

            return array(
                'name' => 'OmniWeb',
                'version' => (isset($b[1]) ? $b[1] : 'Unknown')
            );
        }

        /* Flock */
        if (stripos($userAgent, 'flock') !== false)
        {
            preg_match('/Flock\/([0-9\.]+)(\+)?/i', $userAgent, $b);
            unset($b[0]);

            return array(
                'name' => 'Flock', 'version' => implode('', $b)
            );
        }

        /* Firebird */
        if (stripos($userAgent, 'firebird') !== false)
        {
            preg_match('/Firebird\/([0-9\.]+)(\+)?/i', $userAgent, $b);
            unset($b[0]);

            return array(
                'name' => 'Firebird', 'version' => implode('', $b)
            );
        }

        /* Phoenix */
        if (stripos($userAgent, 'phoenix') !== false)
        {
            preg_match('/Phoenix\/([0-9\.]+)(\+)?/i', $userAgent, $b);
            unset($b[0]);

            return array(
                'name' => 'Phoenix', 'version' => implode('', $b)
            );
        }

        /* Chimera */
        if (stripos($userAgent, 'chimera') !== false)
        {
            preg_match('/Chimera\/([0-9\.]+)(a|b)?(\d+)?(\+)?/i', $userAgent, $b);
            unset($b[0]);

            return array(
                'name' => 'Chimera', 'version' => implode('', $b)
            );
        }

        /* Galeon */
        if (stripos($userAgent, 'galeon') !== false)
        {
            preg_match('/Galeon\/([0-9\.]+)/i', $userAgent, $b);

            return array(
                'name' => 'Galeon', 'version' => $b[1]
            );
        }

        /* Epiphany */
        if (stripos($userAgent, 'epiphany') !== false)
        {
            preg_match('/Epiphany\/([0-9\.]+)/i', $userAgent, $b);

            return array(
                'name' => 'Epiphany', 'version' => $b[1]
            );
        }

        /* Lynx */
        if (stripos($userAgent, 'lynx') !== false)
        {
            preg_match('/Lynx\/([0-9\.]+)/i', $userAgent, $b);

            return array(
                'name' => 'Lynx', 'version' => $b[1]
            );
        }

        /* Links */
        if (stripos($userAgent, 'links') !== false)
        {
            preg_match('/Links \(([0-9\.]+)(pre)?(\d+)?/i', $userAgent, $b);
            unset($b[0]);

            return array(
                'name' => 'Links', 'version' => implode('', $b)
            );
        }

        /* cURL */
        if (stripos($userAgent, 'curl') !== false)
        {
            preg_match('/curl\/([0-9\.]+)/i', $userAgent, $b);

            return array(
                'name' => 'cURL', 'version' => $b[1]
            );
        }

        /* Wget */
        if (stripos($userAgent, 'wget') !== false)
        {
            preg_match('/Wget\/([0-9\.]+)/i', $userAgent, $b);

            return array(
                'name' => 'Wget', 'version' => $b[1]
            );
        }

        /* W3C Validator */
        if (stripos($userAgent, 'w3c_validator') !== false)
        {
            preg_match('/W3C_Validator\/([0-9\.]+)/i', $userAgent, $b);

            return array(
                'name' => 'W3C Validator', 'version' => $b[1]
            );
        }

        /* W3C Link Checker */
        if (stripos($userAgent, 'w3c-checklink') !== false)
        {
            preg_match('/W3C-checklink\/([0-9\.]+)/i', $userAgent, $b);

            return array(
                'name' => 'W3C Link Checker', 'version' => $b[1]
            );
        }

        /* W3C CSS Validator */
        if (stripos($userAgent, 'W3C_CSS_Validator_JFouffa') !== false)
        {
            preg_match('/W3C_CSS_Validator_JFouffa\/([0-9\.]+)/i', $userAgent, $b);

            return array(
                'name' => 'W3C CSS Validator', 'version' => $b[1]
            );
        }

        /* Unknown */
        return array(
            'name' => 'Unknown', 'version' => ''
        );
    }
}

?>
