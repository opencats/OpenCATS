<?php
/*
   * OSATS
   * GNU License
*/

// FIXME: Why is this being reincluded here?
include_once('./config.php');
include_once('./lib/FileUtility.php');

/**
 *	General Utility Library
 *	@package    OSATS
 *	@subpackage Library
 */
class osatutil
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}
    /**
     * modify the config file. -can we put more configs in the db? Jamin 
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
     * Returns the "absolute" version of a URI that is relative to the OSATS
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

        // This breaks stuff. FIXME http://www.OSATSone.com/bugs/?do=details&task_id=72
        // if (!eval(Hooks::get('OSATS_UTILITY_GET_INDEX_URL'))) return;

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
            osatutil::getIndexName() . '?' . $relativePath
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
     * Returns the full public URL to the OSATS root directory.
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
     * Returns the name of the PHP file that is being used as OSATS's delegation
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
            return 'index' . $indexParts[1];
        }

        /* Older versions of apache sometimes don't concatinate script name by default. */
        if ($index == '')
        {
            return 'index.php';
        }
        return $index;
    }

    /**
     * Returns the directory OSATS is being executed from,
     *
     * @return string directory containing index.php.
     * whats calling this?  I think on the old module routine used it. Jamin 
     */
    public static function getDirectoryName()
    {
        $parts = explode('/', $_SERVER['PHP_SELF']);
        unset ($parts[count($parts)-1]);

        $directory = implode('/', $parts);

        return $directory;
    }

    /**
     * Returns the full URL of the PHP file that is being used as OSATS's
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

        /* Fixes for OSATS production server. */
        $url = str_replace('OSATSone.net', 'OSATSone.com', $url);
        $url = str_replace('http://OSATSone.com', 'http://www.OSATSone.com', $url);

        return $url;
    }

    /**
     * Returns the full URL of the PHP file that is being used as OSATS's
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

        /* Fixes for OSATS production server. */
        $url = str_replace('OSATSone.net', 'OSATSone.com', $url);
        $url = str_replace('https://OSATSone.com', 'https://www.OSATSone.com', $url);

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
public static function TabsAtTop($showTopRight = true)
    {      	
        	
        
    }
    
    public static function TabsAtBottom($showTopRight = true)
    {
        /*
		$username     = $_SESSION['OSATS']->getUsername();
        $siteName     = $_SESSION['OSATS']->getSiteName();
        $fullName     = $_SESSION['OSATS']->getFullName();
        $indexName    = osatutil::getIndexName();

        echo '<div id="footerBlock">', "\n";
        //echo in logo here.
		echo '<table cellpadding="0" style="margin: 0px; padding: 0px; float: left;" width="100%">
				<tr>
					<td rowspan="2" width="303">
						
					</td>
					<td width="282">
				
					</td>
					<td width="278" align="right">
						<a href="', $indexName, '?m=logout">' . __('Logout') . ' <img src="images/lock.png" alt="" class="ico" /></a><br />
						<br />
						<span>You are Currently Logged on as: <span style="font-weight:bold;">', "\n";
					echo $fullName; 
					
			         echo '</span></span><br /></td>
				</tr>
				
				</table>', "\n";
        
            /* Disabled notice */
/*
            if (!$_SESSION['OSATS']->accountActive())
            {
                echo '<span style="font-weight:bold;">'.__('Account Inactive').'</span><br />', "\n";
            }
            else if ($_SESSION['OSATS']->getAccessLevel() == ACCESS_LEVEL_READ)
            {
                echo '<span>'.__('Read Only Access').'</span><br />';
            }
            else
            {
                if (!eval(Hooks::get('TEMPLATE_LOGIN_INFO_TOP_RIGHT_2_ELSE'))) return;
            }
            echo '</div>';
        
        echo '</div>';
        echo '<br />';
    */
	}
    
}

