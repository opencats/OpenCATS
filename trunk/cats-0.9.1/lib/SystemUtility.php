<?php
/**
 * CATS
 * System Utility Library
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
 * @version    $Id: SystemUtility.php 3593 2007-11-13 17:36:57Z andrew $
 */

include_once('./lib/QueueProcessor.php');

/**
 *	System Utility Library
 *	@package    CATS
 *	@subpackage Library
 */
class SystemUtility
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}


    /**
     * Returns true if PHP is running on Microsoft Windows.
     *
     * @return boolean True if running on Windows; false otherwise.
     */
    public static function isWindows()
    {
        /* Check for either Windows, WinNT, or Win32. */
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
        {
            return true;
        }

        return false;
    }

    /**
     * Returns true if PHP is running on Mac OS X.
     *
     * @return boolean True if running on Mac OS X; false otherwise.
     */
    public static function isMacOSX()
    {
        if (PHP_OS == 'Darwin')
        {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the Asynchronous Queue Processor is enabled and is
     * functioning properly.
     *
     * @return boolean Is the Asynchronous Queue Processor is enabled and
     *                 functioning properly?
     */
    public static function isSchedulerEnabled()
    {
        return QueueProcessor::isActive();
    }
}

?>
