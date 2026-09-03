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
    $recordSet = MySQLGetAssoc(\'SELECT zipcode FROM zipcodes LIMIT 1\');

    if (!empty($recordSet))
    {
        return true;
    }
    else
    {
        return false;
    }
';

?>
