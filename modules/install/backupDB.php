<?php
/*
 * CATS
 * Database Backup Script
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
 * $Id: backupDB.php 3797 2007-12-04 17:13:21Z brian $
 */


/* Dumps the entire database schema for the currents site into $file, and
 * splits it up into ~1MB chunks with the naming convention $file.(number).
 *
 * The function returns the total number of chunks.
 *
 * If $useStatus is true, use setStatusBackup(status) to display progress.
 */

function BackupDBErrorHandler ($errno, $errstr, $errfile, $errline, $errcontext)
{    
      echo ('An error has occoured.');

      $errorMessage = "An error has occoured in __BACKUP__.  Line $errline of file '$errfile'.\n";
      $errorMessage .= "Script: '{$_SERVER['PHP_SELF']}'.\n\n";
      $errorMessage .= $errstr;
    
      if (file_exists('catsErrors.txt'))
      {
          $errorHandlerEmail = @file_get_contents('catsErrors.txt');
      }
      else
      {
          $errorHandlerEmail = '';
      }
  
      if ($errorHandlerEmail != '')
      {
          $errorHandlerEmail .= '-----------------------------------------------'."\n\n";
      }
  
      $errorHandlerEmail .= $errorMessage;
  
      @file_put_contents('catsErrors.txt', $errorHandlerEmail);

    die();
}

function dumpDB($db, $file, $useStatus = false, $splitFiles = true, $siteID = -1)
{
    set_error_handler('BackupDBErrorHandler');
    
    if ($siteID == -1)
    {
        $siteID = $_SESSION['CATS']->getSiteID();
    }
    
    $len = 0;
    $fileNumber = 0;

    $connection = $db->getConnection();

    $text = '';

    $result = mysql_query(
        sprintf("SHOW TABLES FROM `%s`", DATABASE_NAME),
        $connection
    );
    while ($row = mysql_fetch_array($result, MYSQL_NUM))
    {
        $tables[] = $row[0];
    }
    
    if ($splitFiles) $fh = fopen($file . '.' . $fileNumber, 'w');
    $fh2 = fopen($file, 'w');

    $tableCounter = 0;
    $totalTables = count($tables);
    foreach ($tables as $table)
    {
        ++$tableCounter;
        
        if ($table == 'arb_queue') continue;
        if ($table == 'prepaid_payment') continue;
        if ($table == 'monthly_payment') continue;
        if ($table == 'address_parser_failures') continue;
        if ($table == 'admin_user') continue;
        if ($table == 'admin_user_login') continue;
        if ($table == 'candidate_joborder_status_type') continue;
        if ($table == 'timecard_user') continue;

        $text .= 'DROP TABLE IF EXISTS `' . $table . '`((ENDOFQUERY))'."\n";
        $sql = 'SHOW CREATE TABLE ' . $table;
        $rs = mysql_query($sql, $connection);
        if ($rs)
        {
            if ($row = mysql_fetch_assoc($rs))
            {
                $text .= $row['Create Table'] . "((ENDOFQUERY))\n\n";
            }
        }

        if ($table == 'word_verification') continue;

        if ($useStatus)
        {
            setStatusBackup(
                'Dumping tables (' . $table . ')...',
                $tableCounter / $totalTables
            );
        }

        // We do not need history records.
        if ($table == 'history') continue;

        $isSiteIdColumn = false;
        $sql = sprintf("SHOW COLUMNS FROM %s", $table);
        $rs = mysql_query($sql, $connection);
        while ($recordSet = mysql_fetch_assoc($rs))    
        {
            if ($recordSet['Field'] == 'site_id')
            {
                $isSiteIdColumn = true;
            }
        }    
        
        if ($isSiteIdColumn)
        {
            $sql = 'SELECT * FROM ' . $table . ' WHERE site_id = '.$siteID;
        }
        else
        {
            $sql = 'SELECT * FROM ' . $table . '';
        }

        $rs = mysql_query($sql, $connection);
        $index = 0;
        while ($recordSet = mysql_fetch_assoc($rs))
        {
            $continue = true;

            if (isset($recordSet['site_id']))
            {
                if ($recordSet['site_id'] != $siteID)
                {
                    if ($table == 'site' && $recordSet['site_id'] == CATS_ADMIN_SITE)
                    {
                        $continue = true;
                    }
                    /* Password cantlogin is the password for the automated user.  Automated
                     * user has user level 0 (disabled) preventing a client from logging into
                     * the user. */
                    else if ($table == 'user' && $recordSet['password'] == 'cantlogin' &&
                             $recordSet['site_id'] == CATS_ADMIN_SITE)
                    {
                        $continue = true;
                    }
                    else
                    {
                        $continue = false;
                    }
                }
                if ($table == 'user' && $recordSet['user_name'] == 'brian' &&
                    $recordSet['email'] == 'brian@catsone.com')
                {
                    $continue = false;
                }

            }

            if($table == 'user_login' || $table == 'zipcodes')
            {
                $continue = false;
            }

            if ($continue)
            {
                if ($table == 'site')
                {
                    if (isset($recordSet['unix_name'])) $recordSet['unix_name'] = '';
                    if (isset($recordSet['company_id'])) $recordSet['company_id'] = 0;
                    if (isset($recordSet['is_free'])) $recordSet['is_free'] = 0;
                    if (isset($recordSet['size_limit'])) $recordSet['size_limit'] = 0;
                    if (isset($recordSet['account_active'])) $recordSet['account_active'] = 1;
                    if (isset($recordSet['user_licenses'])) $recordSet['user_licenses'] = 0;
                    if (isset($recordSet['invoice_number'])) $recordSet['invoice_number'] = 0;
                }

                if ($table == 'user')
                {
                    if (strpos($recordSet['user_name'], '@' . $siteID) !== false && substr($recordSet['user_name'], strpos($recordSet['user_name'], '@'.$siteID)) == '@'.$siteID)
                    {
                       $recordSet['user_name'] = str_replace('@' . $siteID, '', $recordSet['user_name']);
                    }
                    if (strtolower($recordSet['user_name']) == 'john@mycompany.net')
                    {
                        $recordSet['access_level'] = 500;
                    }
                }

                if ($index == 0)
                {
                    $text .= 'INSERT INTO `'.$table.'` VALUES '."\n";
                }
                else
                {
                    $text .= ",\n";
                }

                $text .= '(';
                $i = 0;
                foreach ($recordSet as $field)
                {
                    $text .= "'".mysql_real_escape_string($field)."'";
                    $i++;
                    if ($i != count($recordSet))
                    {
                        $text .= ',';
                    }
                }
                $text .= ")";
                $index++;
                
                
                
                if ($splitFiles) fwrite($fh, $text);
                $len += strlen($text);
                $text = str_replace('((ENDOFQUERY))', ';', $text);
                fwrite($fh2, $text);
                $text = '';
                //1000000 is about 1 MB.
                if ($len > 1000000 && $splitFiles)
                {
                    //Next file!
                    $text .= "((ENDOFQUERY))\n\n\n";
                    $index = 0;
                    $len = 0;
                    fwrite($fh, $text);
                    $text = str_replace('((ENDOFQUERY))', ';', $text);
                    fwrite($fh2, $text);
                    $text = '';
                    fclose($fh);
                    $fileNumber++;
                    $fh = fopen($file.'.'.$fileNumber, 'w');
                }
            }
        }

        if ($index > 0)
        {
            $text .= "((ENDOFQUERY))\n\n\n";
        }
    }

    if ($splitFiles) fwrite($fh, $text);
    $text = str_replace('((ENDOFQUERY))', ';', $text);
    fwrite($fh2, $text);
    $text = '';
    if ($splitFiles) fclose($fh);
    fclose($fh2);

    restore_error_handler();

    return $fileNumber + 1;
}

?>
