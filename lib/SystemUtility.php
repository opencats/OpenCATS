<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

include_once(LEGACY_ROOT . '/lib/QueueProcessor.php');

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
