<?php
/*
 * CATS
 * CATS Optional Components List
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
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
 * $Id: OptionalComponents.php 2693 2007-07-12 16:55:36Z brian $
 */

//TODO:  Parse optional components in module zip files.

$optionalComponents['usZipCodes']['name'] = 'United States Zip Code Lookup';
$optionalComponents['usZipCodes']['description'] = 'This contains cities, states, and geographical locations for each zip code in the United States.';
$optionalComponents['usZipCodes']['installCode'] = '
    $schema = @file_get_contents(\'db/upgrade-zipcodes.sql\');
    MySQLQueryMultiple($schema);
    CATSUtility::changeConfigSetting(\'US_ZIPS_ENABLED\', "true");
';
$optionalComponents['usZipCodes']['removeCode'] = '
    MySQLQuery(\'DELETE FROM zipcodes\');
    CATSUtility::changeConfigSetting(\'US_ZIPS_ENABLED\', "false");
';
$optionalComponents['usZipCodes']['detectCode'] = '
    $rs = MySQLQuery(\'SELECT * FROM zipcodes\');

    if ($rs && mysql_fetch_row($rs))
    {
        return true;
    }
    else
    {
        return false;
    }
';

?>
