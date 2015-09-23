<?php
/**
 * CATS
 * General Utility Library
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
 * @version    $Id: CATSUtility.php 3819 2007-12-06 21:23:34Z andrew $
 */

// FIXME: Why is this being reincluded here?
include_once('./config.php');
include_once('./lib/FileUtility.php');

/**
 *	General Utility Library
 *	@package    CATS
 *	@subpackage Library
 */
class CATSUtility
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}


    /**
     * Returns the current CATS version string from the .version file in the
     * CATS root directory.
     *
     * @return string CATS version information.
     */
    public static function getVersion()
    {
        return CATS_VERSION;
    }

    /**
     * Returns the current CATS version number as an integer of the format
     * "MMmmpp", where MM is the major version number, mm is the minor version
     * number, and pp is the patchlevel. For example, 0.5.5 would be 505, and
     * 1.13.6 would be 11306.
     *
     * @return integer CATS version number.
     */
    public static function getVersionAsInteger()
    {
        $versionString = CATS_VERSION;

        /* Remove anything after (and including) the first space from the
         * version data.
         */
        $spacePosition = strpos($versionString, ' ');
        if ($spacePosition !== false)
        {
            $versionString = substr($versionString, 0, $spacePosition);
        }

        /* Multiply each version part (Major.Minor.Patchlevel) by a constant
         * to create an integer in which newer versions are always greater
         * mathematically than older versions.
         */
        $versionIntegers = explode('.', $versionString);
        $versionInteger =  $versionIntegers[0] * 10000;
        $versionInteger += $versionIntegers[1] * 100;
        $versionInteger += $versionIntegers[2] * 1;

        return $versionInteger;
    }

    /**
     * Returns the current CATS revision number from SVN metadata.
     *
     * @return integer CATS SVN revision or 0 if no metadata exists.
     */
    public static function getBuild()
    {
        if (!file_exists('.svn/entries'))
        {
            return 0;
        }

        $data = @file_get_contents('.svn/entries');

        /* XML Data? */
        if ($data{0} === '<')
        {
            $xml = @simplexml_load_string($data);
            if (!$xml || !isset($xml->entry[0]['committed-rev']))
            {
                return 0;
            }

            return (int) $xml->entry[0]['committed-rev'];
        }

        /* If the data is not XML, there is a version number at the first
         * character of the string. We can handle versions 7 and 8.
         */
        if ((int) $data{0} > 6 && (int) $data{0} < 9)
        {
            /* Return the text between the end of the first "dir" line and
             * the next linefeed.
             */
            $data = substr($data, strpos($data, "dir\n") + 4);
            $data = substr($data, 0, strpos($data, "\n"));

            return (integer) $data;
        }
    }

    /**
     * Modifies a setting in config.php (actually modifies the file). String
     * settings need to actually be enclosed in quotes.
     *
     * @param string Name of setting to modify.
     * @param string New value to assign to setting.
     * @return boolean Was the setting changed successfully?
     */
    public static function changeConfigSetting($name, $value)
    {
        /* Make sure we can read and write to config.php. */
        if (!is_readable('config.php') || !is_writeable('config.php'))
        {
            return false;
        }

        /* Try to read the existing config file. */
        $config = @file('config.php');
        if ($config === false)
        {
            return false;
        }

        $newconfig = array();
        foreach ($config as $index => $line)
        {
            if (strpos($line, 'define(\'' . $name . '\'') === 0)
            {
                $newconfig[] = sprintf("define('%s', %s);", $name, $value);
            }
            else
            {
                $newconfig[] = rtrim($line);
            }
        }

        $result = @file_put_contents(
            'config.php', implode("\n", $newconfig) . "\n"
        );
        if (!$result)
        {
            /* We either completely failed or wrote 0 bytes. */
            return false;
        }

        return true;
    }

    /**
     * Returns the current GET query string with all GET variables in $remove
     * removed. The resulting parameter string is separated by $separator.
     *
     * @param array GET variable names to remove.
     * @param array GET variable separator.
     * @return string Filtered GET query string.
     */
    public static function getFilteredGET($remove = array(), $separator = '&')
    {
        $getVars = $_GET;

        foreach ($remove as $name)
        {
            if (isset($getVars[$name]))
            {
                unset($getVars[$name]);
            }
        }

        $newParameters = array();
        foreach ($getVars as $name => $value)
        {
            $newParameters[] = urlencode($name) . '=' . urlencode($value);
        }

        return implode($separator, $newParameters);
    }

    /**
     * Returns the "absolute" version of a URI that is relative to the CATS
     * root directory.
     *
     * FIXME: Allow configuration override of HTTP_HOST.
     *
     * @param string Relative URI.
     * @return string Absolute URI.
     */
    public static function getAbsoluteURI($relativePath = '')
    {
        //FIXME: This causes problems on IIS. Check forums for reporters. bradoyler and one more...
        if (!isset($_SERVER['HTTPS']) || empty($_SERVER['HTTPS']) ||
            strtolower($_SERVER['HTTPS']) != 'on')
        {
            $absoluteURI  = 'http://';
        }
        else
        {
            $absoluteURI  = 'https://';
        }

        $absoluteURI .= $_SERVER['HTTP_HOST']
            . str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])) . '/';

        // This breaks stuff. FIXME http://www.catsone.com/bugs/?do=details&task_id=72
        // if (!eval(Hooks::get('CATS_UTILITY_GET_INDEX_URL'))) return;

        $absoluteURI .= $relativePath;

        /* Clean up extra /'s. */
        $absoluteURI = str_replace('//', '/', $absoluteURI);
        $absoluteURI = str_replace('http:/',  'http://',  $absoluteURI);
        $absoluteURI = str_replace('https:/', 'https://', $absoluteURI);

        return $absoluteURI;
    }

    /**
     * Transfers, via a Location: header, the "absolute" version of a URI that
     * is relative to index.php?.
     *
     * @param string Relative URI.
     * @return void
     */
    public static function transferRelativeURI($relativePath)
    {
        $newLocation = self::getAbsoluteURI(
            CATSUtility::getIndexName() . '?' . $relativePath
        );

        self::transferURL($newLocation);
    }

    /**
     * Transfers, via a Location: header, to an URL.
     *
     * @param string URL.
     * @return void
     */
    public static function transferURL($URL)
    {
        session_write_close();

        header('Location: ' . $URL);

        die();
    }

    /**
     * Returns the full public URL to the CATS root directory.
     *
     * @return string URL.
     */
    public static function getNonSSLDirectoryURL()
    {
        // FIXME: Make this work with ajax.php

        $parts = explode('/', $_SERVER['PHP_SELF']);

        unset($parts[count($parts) - 1]);

        $directory = implode('/', $parts);

        return 'http://' . $_SERVER['HTTP_HOST'] . $directory . '/';
    }

    /**
     * Returns the name of the PHP file that is being used as CATS's delegation
     * module. Usually index.php, but can be index.php5, or anything really.
     *
     * @return string Filename of index.php.
     */
    public static function getIndexName()
    {
        /* This shouldn't happen, but try to recover gracefully if it does. */
        if (!isset($_SERVER['PHP_SELF']))
        {
            return 'index.php';
        }

        $parts = explode('/', $_SERVER['PHP_SELF']);
        $index = end($parts);

        /* Handle ajax.php. */
        $indexParts = explode('.', $index);
        if ($indexParts[0] == 'ajax')
        {
            return 'index.' . $indexParts[1];
        }

        /* Older versions of apache sometimes don't concatinate script name by default. */
        if ($index == '')
        {
            return 'index.php';
        }

        return $index;
    }

    /**
     * Returns the directory CATS is being executed from,
     *
     * @return string directory containing index.php.
     */
    public static function getDirectoryName()
    {
        $parts = explode('/', $_SERVER['PHP_SELF']);
        unset ($parts[count($parts)-1]);

        $directory = implode('/', $parts);

        return $directory;
    }

    /**
     * Returns the full URL of the PHP file that is being used as CATS's
     * delegation module. Usually index.php, but can be index.php5, or
     * anything really. This forces 'http://' even if we are really using
     * HTTPS.
     *
     * @return string Full URL of index.php.
     */
    public static function getNonSSLIndexURL()
    {
        $parts = explode('/', $_SERVER['PHP_SELF']);
        unset($parts[count($parts) - 1]);

        $parts[] = self::getIndexName();
        $path = implode('/', $parts);

        $url = sprintf('http://%s%s', $_SERVER['HTTP_HOST'], $path);

        /* Fixes for CATS production server. */
        $url = str_replace('catsone.net', 'catsone.com', $url);
        $url = str_replace('http://catsone.com', 'http://www.catsone.com', $url);

        return $url;
    }

    /**
     * Returns the full URL of the PHP file that is being used as CATS's
     * delegation module. Usually index.php, but can be index.php5, or
     * anything really. This will use 'https://' if enabled.
     *
     * @param FIXME DOCUMENT ME
     * @return string Full URL of index.php.
     */
    public static function getSSLIndexURL($cutTopDir = false)
    {
        if (!SSL_ENABLED || !isset($_SERVER['HTTP_HOST']))
        {
            return self::getIndexName();
        }

        // FIXME: Document / clean up cut top dir stuff.
        if ($cutTopDir)
        {
            $dirs = explode('/', $_SERVER['PHP_SELF']);
            $path = '/' . implode('/', array_slice($dirs, 1, -2)) . '/' . implode('/', array_slice($dirs, -1, 1));
        }
        else
        {
            $path = $_SERVER['PHP_SELF'];
        }

        $url = sprintf('https://%s%s', $_SERVER['HTTP_HOST'], $path);

        /* Fixes for CATS production server. */
        $url = str_replace('catsone.net', 'catsone.com', $url);
        $url = str_replace('https://catsone.com', 'https://www.catsone.com', $url);

        return $url;
    }

    /**
     * Prints Network Solutions' SSL seal.
     *
     * @return void
     */
    public static function printSSLSeals()
    {
        // FIXME: Maybe I go in TemplateUtility?

        echo '<div style="text-align: left; width: 100%; padding: 20px 0px 0px 0px;">';
        echo '<script src="https://seal.networksolutions.com/siteseal/javascript/siteseal.js" type="text/javascript"></script>';
        echo '<script type="text/javascript">';
        echo 'SiteSeal("https://seal.networksolutions.com/images/basicsqgreen.gif", "NETSB", "none");';
        echo '</script>';
        echo '</div>';
    }

    /**
     * Checks to see if SSL is enabled.
     *
     * @return boolean Is SSL enabled?
     */
    public static function isSSL()
    {
        if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')
        {
            return true;
        }

        return false;
    }

    /**
     * Returns true of the SOAP extension is installed.
     *
     * @return boolean Is the SOAP extension installed?
     */
    public static function isSOAPEnabled()
    {
        if (extension_loaded('soap') && class_exists('SoapClient'))
        {
            return true;
        }

        return false;
    }
}

?>
