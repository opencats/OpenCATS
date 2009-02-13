<?php
/**
 * OSATS
 */

include_once('./lib/SystemInfo.php');
include_once('./lib/Users.php');

/**
 *	New Version Check Library
 *	@package    CATS
 *	@subpackage Library
 */
class NewVersionCheck
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}


    /**
     * Checks remote server for a new version of CATS.  Also submits
     * usage information and license key for statistics tracking
     * purposes.
     *
     * @return void
     */
    public static function checkForUpdate()
    {
        $systemInfoDb = new SystemInfo();
        $systemInfo = $systemInfoDb->getSystemInfo();

        /* Set a UID number if it does not exist. */
        if ($systemInfo['uid'] == 0)
        {
            $randMax = mt_getrandmax();
            if ($randMax >= 100000000)
            {
                $randMax = 100000000;
            }

            $systemInfo['uid'] = mt_rand(1, $randMax);
            $systemInfoDb->updateUID($systemInfo['uid']);
        }

        if (!eval(Hooks::get('NEW_VERSION_CHECK_CHECK_FOR_UPDATE'))) return;

        /* Bail if the user disabled new version checking. */
        if ($systemInfo['disable_version_check'])
        {
            return;
        }

        if (isset($_SERVER['SERVER_SOFTWARE']))
        {
            $serverSoftware = $_SERVER['SERVER_SOFTWARE'];
        }
        else
        {
            $serverSoftware = '';
        }

        if (isset($_SERVER['HTTP_USER_AGENT']))
        {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }
        else
        {
            $userAgent = '';
        }

        //FIXME: Library code Session dependencies suck.
        $siteName = $_SESSION['CATS']->getSiteName();
        $catsVersion = osatutil::getVersionAsInteger();

        $users = new Users(1);
        $numberOfActiveUsers = $users->getUsageData();

        $licenseKey = LICENSE_KEY;

        /* Build POST data. */
        $postData  = 'CatsVersion='     . urlencode($catsVersion);
        $postData .= '&CatsUID='        . urlencode($systemInfo['uid']);
        $postData .= '&PHPVersion='     . urlencode(phpversion());
        $postData .= '&ServerSoftware=' . urlencode($serverSoftware);
        $postData .= '&UserAgent='      . urlencode($userAgent);
        $postData .= '&SiteName='       . urlencode($siteName);
        $postData .= '&activeUsers='    . urlencode($numberOfActiveUsers);
        $postData .= '&licenseKey='     . urlencode($licenseKey);

        /* Hack for compatability with older CATS versions. */
        $postData .= '&CatsVersionAgain=' . urlencode($catsVersion);

        $theData = self::getDataFromServer(
            'www.catsone.com', 80, '/catsnewversion.php', $postData
        );

        /* Check to see if getting information failed, if it did reset the weekly counter */
        if (strpos($theData, '(end of CATS version info)') == 0)
        {
            if (!empty($systemInfo['available_version']))
            {
                $systemInfoDb->updateRemoteVersion(
                    $systemInfo['available_version'],
                    $systemInfo['available_version_description'],
                    date('Y-m-d')
                );
            }
            else
            {
                $systemInfoDb->updateRemoteVersion(
                    0,
                    $systemInfo['available_version_description'],
                    date('Y-m-d')
                );
            }

            return;
        }

        /* Strip down the data into $remoteVersion and $newVersionNotice. */
        $temp             = substr($theData, strpos($theData, '{<') + 2);
        $newVersionNotice = substr($temp, strpos($temp, '{<') + 2);
        $remoteVersion    = substr($newVersionNotice, strpos($newVersionNotice, '{<') + 2);
        $newVersionNotice = substr($newVersionNotice, 0, strpos($newVersionNotice, '>}'));
        $remoteVersion    = substr($remoteVersion, 0, strpos($remoteVersion, '>}'));

        $systemInfoDb->updateRemoteVersion(
            $remoteVersion, $newVersionNotice, date('Y-m-d')
        );
    }

    /**
     * Returns news for the dashboard (if a new version is available).
     *
     * @return html
     */
    public static function getNews()
    {
        $systemInfoDb = new SystemInfo();
        $systemInfo = $systemInfoDb->getSystemInfo();

        /* Update daily. */
        $lastWeeksDate = time() - (SECONDS_IN_A_DAY);
        $lastCheck = strtotime($systemInfo['date_version_checked']);
        if ($lastWeeksDate > $lastCheck)
        {
            self::checkForUpdate();
            /* Refresh the new information. */
            $systemInfo = $systemInfoDb->getSystemInfo();
        }

        /* Only display new version news if a new version is available. */
        if ($systemInfo['available_version'] > osatutil::getVersionAsInteger())
        {
            return urldecode($systemInfo['available_version_description']);
        }

        return '';
    }

    /**
     * Returns an HTTP response from a server.
     *
     * @param string remote server host or IP
     * @param integer port number
     * @param string resource path
     * @param string GET data
     * @return void
     */
    private static function getDataFromServer($host, $port, $path, $data)
    {
        $socket = @fsockopen($host, $port, $errorno, $errorstr, 5);
        if ($socket === false)
        {
            return false;
        }

        stream_set_timeout($socket, 5);
        fputs($socket, sprintf("GET %s?%s HTTP/1.1\r\n", $path, $data));
        fputs($socket, sprintf("Host: %s\r\n", $host));
        fputs($socket, sprintf("User-Agent: MSIE\r\n"));
        fputs($socket, sprintf("Content-type: application/x-www-form-urlencoded\r\n"));
        //fputs($socket, "Content-length: " . strlen($data) . "\r\n");
        fputs($socket, sprintf("Connection: close\r\n\r\n"));

        $buffer = '';

        while (!feof($socket))
        {
            $buffer .= fgets($socket, 128);
        }

        fclose($socket);

        return $buffer;
    }
}

?>