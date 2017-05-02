<?php
/**
 * CATS
 * Common Errors Friendly Display
 *
 * Copyright (C) 2006 - 2007 Cognizo Technologies, Inc.
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
 * @version    $Id: CommonErrors.php 3784 2007-12-03 21:57:10Z brian $
 */

include_once('./lib/Mailer.php');

define('COMMONERROR_PERMISSION',                            1);
define('COMMONERROR_NOTLOGGEDIN',                           2);
define('COMMONERROR_BADINDEX',                              3);
define('COMMONERROR_MISSINGFIELDS',                         4);
define('COMMONERROR_NOPASSWORDMATCH',                       5);
define('COMMONERROR_FILEERROR',                             6);
define('COMMONERROR_INVALIDMODULE',                         7);
define('COMMONERROR_RECORDERROR',                           8);
define('COMMONERROR_WILDCARDSTRING',                        9);
define('COMMONERROR_BADFIELDS',                             10);
define('COMMONERROR_RESTRICTEDEXTENSION',                   11);
define('COMMONERROR_FILENOTFOUND',                          12);

/**
 *	Common Errors Friendly Display Library
 *	@package    CATS
 *	@subpackage Library
 */
class CommonErrors
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}

    public static function isDemo()
    {
        $isDemo = $_SESSION['CATS']->isDemo();
        return $isDemo;
    }

    public static function fatalModal($code, $active, $customMessage = '')
    {
        return self::fatal($code, $active, $customMessage, true);
    }

    public static function fatal($code, $active, $customMessage = '', $modal = false)
    {
        $template = new Template();
        $internalErrorTitle = '';
        switch ($code)
        {
            case COMMONERROR_RESTRICTEDEXTENSION:
                $errorTitle = 'Upgrade to Professional for Plug-ins';
                $internalErrorTitle = 'Unauthorized Use of an Extension';
                $errorMessage = '<b>' . $customMessage . '</b><p />CATS is free software and is available to anyone free '
                    . 'of charge. To support the development of this software, we\'ve released several plug-ins and '
                    . 'extensions which allow CATS to work with software like Outlook and websites like Monster.<p />Designed '
                    . 'by recruiters, for recruits, they\'re purpose is to make recruiting faster and easier.<p />'
                    . '<b>At this time, these plug-ins are only available to CATS Professional users.</b><p /><ul>';
                $errorMessage .= '<li>For more information about the CATS Professional hosted version where we take care of '
                    . 'backups and the hassles of running a web server and you have access to all of our plug-ins, '
                    . '<a href="http://www.catsone.com/getcats.php" style="font-weight: bold;">click here</a>.<p /></li>';
                $errorMessage .= '<li>For more information about CATS Professional services where you host CATS on your '
                    . 'own server with our support and plug-ins, please visit '
                    . '<a href="http://www.catsone.com/Professional">http://www.catsone.com/Professional</a>.</li></ul>';
                break;

            case COMMONERROR_BADFIELDS:
                $errorTitle = 'Invalid Information';
                $internalErrorTitle = 'Bad Input';
                $errorMessage = 'Some of the information you provided doesn\'t follow the correct format and '
                    . 'CATS can\'t interpret what it is. Please <a href="javascript:back()">go back</a> and '
                    . 'complete each field paying close attention to any instructions provided. '
                    . '<p><b>' . $customMessage . '</b>';
                break;

            case COMMONERROR_RECORDERROR:
                $errorTitle = 'Internal Record Error';
                $internalErrorTitle = 'Insert, Delete or Update Error';
                $errorMessage = 'The internal mechanics CATS uses to add and edit records like candidates, companies, job '
                    . 'orders, etc. failed on your last operation. You may have entered incorrect data that it\'s not used '
                    . 'to handling or there is an internal issue.<p>The administrators have been notified and will '
                    . 'resolve the issue as soon as possible. We apologize for the inconvenience.';
                break;

            case COMMONERROR_INVALIDMODULE:
                $errorTitle = 'That Module is not Installed';
                $internalErrorTitle = 'Bad Module';
                $errorMessage = 'The module you requested, "<b>' . htmlentities($customMessage) . '</b>", doesn\'t exist or we haven\'t created it yet!<p>'
                    . 'One of the most amazing things about CATS is that through modules, you add new features '
                    . 'in the blink of an eye. Modules are like plug-ins, and perform specific tasks like integrating with '
                    . 'job boards or keeping your calendar up to date. You\'re getting this message because you followed '
                    . 'an old link, a bad link, or the module you\'re asking for no longer exists.<p>'
                    . '<a href="'.CATSUtility::getIndexName().'?m=home">Click here</a> to view the home module.';
                break;

            case COMMONERROR_FILEERROR:
                $errorTitle = 'A File Error has Occurred';
                $internalErrorTitle = 'File Error';
                $errorMessage = 'There was a problem when CATS attempted to process the file you selected. '
                    . '<b>' . $customMessage . '</b> File errors are sometimes caused by high Internet '
                    . 'traffic or older web browsers. The latest version of the <a href="http://www.getfirefox.com"> '
                    . 'Mozilla Firefox&copy;</a> browser '
                    . 'is recommended.<br /><br />The problem may be resolved by using the '
                    . '<a href="javascript:back()">back button</a> on your browser and trying again.';
                break;
            case COMMONERROR_BADINDEX:
                $errorTitle = 'Bad Server Information';
                $internalErrorTitle = 'Invalid ID';
                $errorMessage = 'When you perform actions in CATS like editting a candidate or a job order, '
                    . 'CATS assigns numbers to each record that uniquely identifies it. '
                    . 'For example, a candidate "Steve Smith" may be identified by the number <b>101</b>. '
                    . '<br /><br />'
                    . 'The action you\'re attempting to perform has a unique number like this '
                    . 'attached to it. CATS cannot find a record with the number you provided. The record '
                    . 'may have been deleted, you may have inadvertently logged off or an incorrect number '
                    . 'may have been provided to you.'
                    . '<br /><br />'
                    . '<b>It\'s ok!</b> CATS can\'t complete whatever action you requested; but chances are, '
                    . 'if you click the <a href="javascript:back()">back button</a> and <b>refresh</b> the page '
                    . 'and try again it will work just fine.';
                break;

            case COMMONERROR_PERMISSION:
                $errorTitle = 'You don\'t have permission';
                $internalErrorTitle = 'Permission Denied';
                $errorMessage = 'You don\'t have access to the action you\'re attempting to perform. If you '
                    . 'feel you should have access, contact your site administrator. '
                    . 'You can click the <a href="javascript:back()">back</a> button on your browser to return '
                    . 'to where you came from.';
                break;

            case COMMONERROR_NOTLOGGEDIN:
                $errorTitle = 'You are not logged in';
                $internalErrorTitle = 'Not Logged In';
                $errorMessage = 'You followed a link from an account logged into CATS but are no longer logged '
                    . 'in and CATS cannot allow you to continue. <b>It\'s Ok!</b> This sort of thing can happen '
                    . 'if you have CATS open in another window and log off from that window, if you timeout and your '
                    . 'login expires, if you delete your cookies or personal inforation while logged into CATS, '
                    . 'or because of a handful of other trivial circumstances.'
                    . '<br /><br />'
                    . 'The solution is simple: <a href="/?m=login">click here</a> to login to CATS.';
                break;

            case COMMONERROR_MISSINGFIELDS:
                $errorTitle = 'Required Fields are Missing';
                $internalErrorTitle = 'Required Fields Missing';
                $errorMessage = 'One or more of the input fields on the page you came from were required and '
                    . 'were left with blank or incorrect values. The action you\'re trying to perform cannot '
                    . 'be completed without those fields. Please use the <a href="javascript:back()">back button</a> '
                    . 'on your browser to return to where you came from.'
                    . '<br /><br />';

                if (!empty($customMessage))
                {
                    $errorMessage .= $customMessage . '<br /><br />';
                }

                $errorMessage .= 'In some cases, severe Internet traffic or older web browsers may have caused to the loss '
                    . 'of this information at no fault to you. You may want to try again or upgrade your browser '
                    . 'to one such as <a href="http://www.getfirefox.com">Mozilla FireFox&copy;</a> if you feel '
                    . 'this might be the culprit.';
                break;

            case COMMONERROR_WILDCARDSTRING:
                $errorTitle = 'Wild Card String Missing';
                $internalErrorTitle = 'Missing wild card string';
                $errorMessage = 'One or more of the input fields on the page you came from were required and '
                    . 'were left with blank or incorrect values. The action you\'re trying to perform cannot '
                    . 'be completed without those fields. Please use the <a href="javascript:back()">back button</a> '
                    . 'on your browser to return to where you came from.'
                    . '<br /><br />';

                if (!empty($customMessage))
                {
                    $errorMessage .= $customMessage . '<br /><br />';
                }

                $errorMessage .= 'In some cases, severe Internet traffic or older web browsers may have caused to the loss '
                    . 'of this information at no fault to you. You may want to try again or upgrade your browser '
                    . 'to one such as <a href="http://www.getfirefox.com">Mozilla FireFox&copy;</a> if you feel '
                    . 'this might be the culprit.';
                break;

            case COMMONERROR_NOPASSWORDMATCH:
                $errorTitle = 'Passwords do not Match';
                $internalErrorTitle = 'Passwords do not match';
                $errorMessage = 'The passwords you entered do not match. Please use the <a href="javascript:back()">'
                    . 'back button</a> on your browser to return from where you came and enter the correct password '
                    . 'in all required fields.';
                break;

            case COMMONERROR_FILENOTFOUND:
                $errorTitle = 'File Does Not Exist';
                $internalErrorTitle = 'File Does Not Exist';
                $errorMessage = 'The file you are requesting for does not exist. Please use the '
                    . '<a href="javascript:back()">back button</a> on your browser to return to where '
                    . 'you came from.<br /><br />';

                if (!empty($customMessage))
                {
                    $errorMessage .= $customMessage;
                }
                break;

            default:
                $errorTitle = 'An Error Has Occurred';
                $internalErrorTitle = 'Undefined';
                $errorMessage = 'An error has occurred and the operation you were attempting to perform cannot be '
                    . 'completed. Please use the <a href="javascript:back();">back</a> button on your web browser '
                    . 'to return from where you came. The administrators have been notified, so you may wish to '
                    . 'try again later.';
                break;
        }

        //self::sendEmail($internalErrorTitle, $customMessage);

        if (isset($_SESSION['CATS']) && !empty($_SESSION['CATS']))
        {
            /* Get the current user's user ID. */
            $userID = $_SESSION['CATS']->getUserID();

            /* Get the current user's site ID. */
            $siteID = $_SESSION['CATS']->getSiteID();

            /* Get the current user's access level. */
            $accessLevel = $_SESSION['CATS']->getAccessLevel(ACL::SECOBJ_ROOT);

            /* Is it a demo */
            $isDemo = $_SESSION['CATS']->isDemo();

            // Save log if a session is present and it's not a demo, and exceptions are logged
            if (!$isDemo && self::isExceptionLoggingEnabled())
            {
                self::saveLog($siteID, $userID, $accessLevel, $internalErrorTitle, $customMessage);
            }

            /* All templates have an access level if we have a session. */
            $template->assign('accessLevel', $accessLevel);
            $template->assign('siteID', $siteID);
            $template->assign('userID', $userID);
            $template->assign('isDemo', $isDemo);
        }
        else
        {
            $template->assign('isDemo', true); // no session might as well be a demo
            $template->assign('siteID', -1);
            $template->assign('userID', -1);
            $template->assign('accessLevel', 0);
        }

        $template->assign('active', $active);
        $template->assign('errorTitle', $errorTitle);
        $template->assign('errorMessage', $errorMessage);
        $template->assign('modal', $modal);
        $template->display('./modules/home/FriendlyError.tpl');
        die();
    }

    private static function isExceptionLoggingEnabled()
    {
        $db = DatabaseConnection::getInstance();
        $tables = array();
        $rs = $db->query('show tables');
        while ($tbl = mysql_fetch_array($rs)) $tables[] = $tbl[0];
        if (in_array('exceptions', $tables)) return true;
        else return false;
    }

    private static function getBacktrace()
    {
        ob_start();
        debug_print_backtrace();
        $backtrace = ob_get_contents();
        ob_end_clean();
        return $backtrace;
    }

    private static function saveLog($siteID, $userID, $accessLevel, $internalErrorTitle, $customMessage)
    {
        $db = DatabaseConnection::getInstance();

        $sql = sprintf('INSERT INTO exceptions (site_id, user_id, title, message, access_level, script, '
            . 'domain, request, backtrace, date) VALUES (%d, %d, "%s", "%s", %d, "%s", "%s", "%s", "%s", NOW())',
            $siteID, $userID, addslashes($internalErrorTitle), addslashes($customMessage),
            $accessLevel, addslashes($_SERVER['SCRIPT_NAME']), addslashes($_SERVER['SERVER_NAME']),
            addslashes($_SERVER['QUERY_STRING']), addslashes(self::getBacktrace())
        );

        $rs = $db->query($sql);
        if ($rs) return true;
        else return false;
    }

    private static function sendEmail($subject, $body)
    {
        if (!eval(Hooks::get('EXCEPTION_NOTIFY_DEV'))) return;
    }
};

?>
