<?php
/**
 * CATS
 * License Library
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
 * @version    $Id: License.php 3678 2007-11-21 23:10:42Z andrew $
 */

include_once(LEGACY_ROOT . '/lib/CATSUtility.php');
include_once(LEGACY_ROOT . '/lib/ParseUtility.php');

/**
 * License compatibility defaults.
 *
 * Legacy license keys are no longer validated or persisted. This class remains
 * only for old call sites that still ask for license-derived account limits.
 *
 *  @package    CATS
 *  @subpackage Library
 */
class License
{
    public function getExpirationDate()
    {
        return 32767;
    }

    public function getNumberOfSeats()
    {
        return 999;
    }

    public function getName()
    {
        return 'Open Source User';
    }

    public function setKey($key)
    {
        return true;
    }

    public function getKey()
    {
        return '';
    }

    public function isProfessional()
    {
        return false;
    }

    public function isLicenseValid()
    {
        return true;
    }

    public function isOpenSource()
    {
        return true;
    }
}

class LicenseUtility
{
    public static function getNumberOfSeats()
    {
        return 999;
    }

    public static function getName()
    {
        return 'Open Source User';
    }

    public static function getExpirationDate()
    {
        return 32767;
    }

    public static function validateProfessionalKey($key = '')
    {
        return false;
    }

    public static function isProfessional()
    {
        return false;
    }

    public static function isOpenSource()
    {
        return true;
    }

    public static function isLicenseValid()
    {
        return true;
    }

    public static function isParsingEnabled()
    {
        return true;
    }

    public static function getParsingStatus()
    {
        return true;
    }
}

?>
