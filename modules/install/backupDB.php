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

function dumpDB($db, $file, $useStatus = false, $splitFiles = true)
{
    set_error_handler('BackupDBErrorHandler');


    $len = 0;
    $fileNumber = 0;

    $text = '';

    $resultSet = $db->getAllAssoc(sprintf("SHOW TABLES FROM `%s`", DATABASE_NAME));
    foreach ($resultSet as $row)
    {
        $tables[] = reset($row);
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
        if ($table == 'timecard_user') continue;

        $text .= 'DROP TABLE IF EXISTS `' . $table . '`((ENDOFQUERY))'."\n";
        $sql = 'SHOW CREATE TABLE ' . $table;
        $row = $db->getAssoc($sql);
        if (!empty($row))
        {
            $text .= $row['Create Table'] . "((ENDOFQUERY))\n\n";
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

        $sql = 'SELECT * FROM ' . $table;

        $index = 0;
        $db->query($sql);
        while (true)
        {
            $recordSet = $db->getAssoc();
            if (empty($recordSet))
            {
                break;
            }

            $continue = true;

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
                    $text .= $db->makeQueryString($field);
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
