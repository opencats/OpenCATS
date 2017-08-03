<?php
/*
 * CATS
 * AJAX Installer Interface
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
 * $Id: ui.php 3807 2007-12-05 01:47:41Z will $
 */

include_once('./config.php');
include_once('./lib/InstallationTests.php');
include_once('./lib/CATSUtility.php');

if( ini_get('safe_mode') )
{
	//don't do anything in safe mode
}
else
{
	/* limit the execution time to 300 secs. */
	set_time_limit(300);
}
@ini_set('memory_limit', '192M');

if (file_exists('modules.cache')) @unlink('modules.cache');

if (!isset($_REQUEST['a']) || empty($_REQUEST['a']))
{
    die('Invalid action.');
}

$action = $_REQUEST['a'];

/* Don't allow installation if ./INSTALL_BLOCK exists. */
if (file_exists('INSTALL_BLOCK'))
{
    echo '
        <script type="text/javascript">
            setActiveStep(1);
            showTextBlock(\'installLocked\');
        </script>';
    die();
}

switch ($action)
{
    case 'startInstall':
        echo '
            <script type="text/javascript">
                setActiveStep(1);
                showTextBlock(\'startInstall\');
                Installpage_append(\'a=installTest\', \'Please wait while your system is tested...\');
            </script>';
        break;

    case 'installTest':
        $result = true;
        $warningsOccurred = false;

        echo '<br />',
             '<span style="font-weight: bold;">Test Results</span>',
             '<table class="test_output">';


        InstallationTests::runInstallerTests();

        if (!$result)
        {
            if ($warningsOccurred)
            {
                echo '<script type="text/javascript">showTextBlock(\'testFailedWarning\');</script>';

            }
            else
            {
                echo '<script type="text/javascript">showTextBlock(\'testFailed\');</script>';
            }
        }
        else if ($warningsOccurred)
        {
            echo '<script type="text/javascript">showTextBlock(\'testWarning\');</script>';

        }
        else
        {
            echo '<script type="text/javascript">showTextBlock(\'testPassed\');</script>';
        }

        echo '</table>';
        break;

    case 'databaseConnectivity':
        /* If $_REQUEST['user'] is set, we have been passed parameters to test
         * the connection.
         */
        if (isset($_REQUEST['user']))
        {
            if (isset($_REQUEST['user']) && !empty($_REQUEST['user']))
            {
                CATSUtility::changeConfigSetting('DATABASE_USER', "'" . $_REQUEST['user'] . "'");
            }

            if (isset($_REQUEST['pass']))
            {
                CATSUtility::changeConfigSetting('DATABASE_PASS', "'" . $_REQUEST['pass'] . "'");
            }

            if (isset($_REQUEST['host']) && !empty($_REQUEST['host']))
            {
                CATSUtility::changeConfigSetting('DATABASE_HOST', "'" . $_REQUEST['host'] . "'");
            }

            if (isset($_REQUEST['name']) && !empty($_REQUEST['name']))
            {
                CATSUtility::changeConfigSetting('DATABASE_NAME', "'" . $_REQUEST['name'] . "'");
            }

            echo '
                <script type="text/javascript">
                    setActiveStep(2);
                    showTextBlock(\'databaseConnectivity\');
                    document.getElementById(\'testDatabaseConnectivity\').disabled = true;
                    document.getElementById(\'testDatabaseConnectivityIndicator\').style.visibility = \'visible\';
                    Installpage_append(\'a=testDatabaseConnectivity\', \'Please wait while your connection is tested...\');
                </script>';
            die();
        }

        echo '
            <script type="text/javascript">
                setActiveStep(2);
                showTextBlock(\'databaseConnectivity\');
                document.getElementById(\'dbname\').value = \'' . htmlspecialchars(DATABASE_NAME) . '\';
                document.getElementById(\'dbuser\').value = \'' . htmlspecialchars(DATABASE_USER) . '\';
                document.getElementById(\'dbpass\').value = \'' . htmlspecialchars(DATABASE_PASS) . '\';
                document.getElementById(\'dbhost\').value = \'' . htmlspecialchars(DATABASE_HOST) . '\';
            </script>';
        break;

    case 'mailSettings':
        MySQLConnect();

        if (MAIL_MAILER == 3 && MAIL_SMTP_AUTH == 1)
            $mailOption = '4';
        else
            $mailOption = '';

        $mailFromAddress = '';
        if (isset($tables['settings']))
        {
            $rs = MySQLQuery('SELECT value FROM settings WHERE setting = "fromAddress" LIMIT 1');
            if (mysql_num_rows($rs) > 0)
                $mailFromAddress = mysql_result($rs, 0, 0);
        }

        echo '
            <script type="text/javascript">
                setActiveStep(5);
                showTextBlock(\'mailSettings\');
                document.getElementById(\'mailSupport\').value = \'opt' . ($mailOption != '' ? $mailOption : htmlspecialchars(MAIL_MAILER)) . '\';
                document.getElementById(\'mailSendmail\').value = \'' . htmlspecialchars(MAIL_SENDMAIL_PATH) . '\';
                document.getElementById(\'mailSmtpHost\').value = \'' . htmlspecialchars(MAIL_SMTP_HOST) . '\';
                document.getElementById(\'mailSmtpPort\').value = \'' . htmlspecialchars(MAIL_SMTP_PORT) . '\';
                document.getElementById(\'mailSmtpUsername\').value = \'' . htmlspecialchars(MAIL_SMTP_USER) . '\';
                document.getElementById(\'mailSmtpPassword\').value = \'' . htmlspecialchars(MAIL_SMTP_PASS) . '\';
                document.getElementById(\'mailFromAddress\').value = \'' . htmlspecialchars($mailFromAddress) . '\';
                changeMailForm();
            </script>';
        break;

    case 'setMailSettings':
        $mailSupportTxt = $_REQUEST['mailSupport'];
        $mailSendmailPath = trim($_REQUEST['mailSendmail']);
        $mailSmtpHost = trim($_REQUEST['mailSmtpHost']);
        $mailSmtpPort = intval(trim($_REQUEST['mailSmtpPort']));
        $mailSmtpUsername = trim($_REQUEST['mailSmtpUsername']);
        $mailSmtpPassword = trim($_REQUEST['mailSmtpPassword']);
        $fromAddress = substr(trim($_REQUEST['mailFromAddress']), 0, 255);

        // validate e-mail address reply-to field
        if(strlen($fromAddress) < 4)
        {
            echo('
                <script type="text/javascript">
                    setActiveStep(5);
                    showTextBlock(\'mailSettings\');
                    var objLabel = document.getElementById(\'mailFromAddressLabel\');
                    objLabel.style.color = \'#ff0000\';
                    changeMailForm();
                    alert(\'You must enter your e-mail address to continue.\');
                </script>
                '
            );
        }
        else
        {
            if(strlen($mailSupportTxt) == 4)
            {
                $mailSupport = intval(substr($mailSupportTxt, 3, 1));
            }

            if ($mailSupport == 4)
            {
                CATSUtility::changeConfigSetting('MAIL_MAILER', '3');
                CATSUtility::changeConfigSetting('MAIL_SMTP_AUTH', 'true');
            }
            else
            {
                CATSUtility::changeConfigSetting('MAIL_MAILER', sprintf('%d', $mailSupport));
                CATSUtility::changeConfigSetting('MAIL_SMTP_AUTH', 'false');
            }

            CATSUtility::changeConfigSetting('MAIL_SENDMAIL_PATH', '"' . $mailSendmailPath . '"');
            CATSUtility::changeConfigSetting('MAIL_SMTP_HOST', '"' . $mailSmtpHost . '"');
            CATSUtility::changeConfigSetting('MAIL_SMTP_PORT', sprintf('%d', $mailSmtpPort));
            CATSUtility::changeConfigSetting('MAIL_SMTP_USER', '"' . $mailSmtpUsername . '"');
            CATSUtility::changeConfigSetting('MAIL_SMTP_PASS', '"' . $mailSmtpPassword . '"');

            @session_name(CATS_SESSION_NAME);
            session_start();

            $_SESSION['fromAddressInstaller'] = $fromAddress;

            echo '<script type="text/javascript">
                      setActiveStep(6);
                      showTextBlock(\'detectingOptional\');
                      setTimeout("Installpage_populate(\'a=optionalComponents\');", 5000);
                  </script>';
        }
        break;

    case 'testDatabaseConnectivity':
        echo '<br /><span style="font-weight: bold;">Test Results</span>';

        echo '<table class="test_output">';

        if (InstallationTests::checkMySQL(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME))
        {
            echo '<script type="text/javascript">showTextBlock(\'MySQLTestPassed\');</script>';
        }
        else
        {
            echo '<script type="text/javascript">showTextBlock(\'MySQLTestFailed\');</script>';
        }

        echo '</table>';

        echo '
            <script type="text/javascript">
                document.getElementById(\'testDatabaseConnectivity\').disabled = false;
                document.getElementById(\'testDatabaseConnectivityIndicator\').style.visibility = \'hidden\';
            </script>';
        break;

    case 'resumeParsing':
        echo '<script type="text/javascript">setActiveStep(4);</script>';

        if (ANTIWORD_PATH == '')
        {
            echo '
                <script type="text/javascript">
                    document.getElementById(\'docEnabled\').checked = false;
                    document.getElementById(\'docExecutable\').disabled = true;
                    document.getElementById(\'docExecutable\').value = \'\';
                    document.getElementById(\'docExecutableOrg\').value = \'\';
                </script>';
        }
        else
        {
            $antiwordWithSlashes = str_replace('\\', '\\\\', ANTIWORD_PATH);

            include_once ('lib/SystemUtility.php');
            /* Change Windows default command to UNIX default command hack. */
            if (strpos(strtolower($antiwordWithSlashes), "c:\\") === 0 && !SystemUtility::isWindows())
            {
                $antiwordWithSlashes = '/usr/bin/antiword';
            }

            echo '
                <script type="text/javascript">
                    document.getElementById(\'docEnabled\').checked = true;
                    document.getElementById(\'docExecutable\').disabled = false;
                    document.getElementById(\'docExecutable\').value = \'' . $antiwordWithSlashes . '\';
                    document.getElementById(\'docExecutableOrg\').value = \'' . $antiwordWithSlashes . '\';
                </script>';
        }

        if (PDFTOTEXT_PATH == '')
        {
            echo '
                <script type="text/javascript">
                    document.getElementById(\'pdfEnabled\').checked = false;
                    document.getElementById(\'pdfExecutable\').disabled = true;
                    document.getElementById(\'pdfExecutable\').value = \'\';
                    document.getElementById(\'pdfExecutableOrg\').value = \'\';
                </script>';
        }
        else
        {
            $pdftotextWithSlashes = str_replace('\\', '\\\\', PDFTOTEXT_PATH);

            include_once ('lib/SystemUtility.php');
            /* Change Windows default command to UNIX default command hack. */
            if (strpos(strtolower($pdftotextWithSlashes), "c:\\") === 0 && !SystemUtility::isWindows())
            {
                $pdftotextWithSlashes = '/usr/bin/pdftotext';
            }

            echo '
                <script type="text/javascript">
                    document.getElementById(\'pdfEnabled\').checked = true;
                    document.getElementById(\'pdfExecutable\').disabled = false;
                    document.getElementById(\'pdfExecutable\').value = \'' . $pdftotextWithSlashes . '\';
                    document.getElementById(\'pdfExecutableOrg\').value = \'' . $pdftotextWithSlashes . '\';
                </script>';
        }

        if (HTML2TEXT_PATH == '')
        {
            echo '
                <script type="text/javascript">
                    document.getElementById(\'htmlEnabled\').checked = false;
                    document.getElementById(\'htmlExecutable\').disabled = true;
                    document.getElementById(\'htmlExecutable\').value = \'\';
                    document.getElementById(\'htmlExecutableOrg\').value = \'\';
                </script>';
        }
        else
        {
            $html2textWithSlashes = str_replace('\\', '\\\\', HTML2TEXT_PATH);

            include_once ('lib/SystemUtility.php');
            /* Change Windows default command to UNIX default command hack. */
            if (strpos(strtolower($html2textWithSlashes), "c:\\") === 0 && !SystemUtility::isWindows())
            {
                $html2textWithSlashes = '/usr/bin/html2text';
            }

            echo '
                <script type="text/javascript">
                    document.getElementById(\'htmlEnabled\').checked = true;
                    document.getElementById(\'htmlExecutable\').disabled = false;
                    document.getElementById(\'htmlExecutable\').value = \'' . $html2textWithSlashes . '\';
                    document.getElementById(\'htmlExecutableOrg\').value = \'' . $html2textWithSlashes . '\';
                </script>';
        }

        if (UNRTF_PATH == '')
        {
            echo '
                <script type="text/javascript">
                    document.getElementById(\'rtfEnabled\').checked = false;
                    document.getElementById(\'rtfExecutable\').disabled = true;
                    document.getElementById(\'rtfExecutable\').value = \'\';
                    document.getElementById(\'rtfExecutableOrg\').value = \'\';
                </script>';
        }
        else
        {
            $unrtfWithSlashes = str_replace('\\', '\\\\', UNRTF_PATH);

            include_once ('lib/SystemUtility.php');
            /* Change Windows default command to UNIX default command hack. */
            if (strpos(strtolower($unrtfWithSlashes), "c:\\") === 0 && !SystemUtility::isWindows())
            {
                $unrtfWithSlashes = '/usr/bin/unrtf';
            }

            echo '
                <script type="text/javascript">
                    document.getElementById(\'rtfEnabled\').checked = true;
                    document.getElementById(\'rtfExecutable\').disabled = false;
                    document.getElementById(\'rtfExecutable\').value = \'' . $unrtfWithSlashes . '\';
                    document.getElementById(\'rtfExecutableOrg\').value = \'' . $unrtfWithSlashes . '\';
                </script>';
        }

        echo '<script type="text/javascript">showTextBlock(\'resumeParsing\');</script>';
        break;

    case 'testResumeParsing':
        echo '
            <script type="text/javascript">
                showTextBlock(\'resumeParsing\');
                Installpage_append(\'a=testResumeParsing2\', \'Please wait while your settings are tested...\');
            </script>';

        $antiwordPath = $_REQUEST['docExecutable'];
        $antiwordWithSlashes = str_replace('\\', '\\\\', $antiwordPath);
        CATSUtility::changeConfigSetting('ANTIWORD_PATH', '"' . $antiwordWithSlashes . '"');

        $pdftotextPath = $_REQUEST['pdfExecutable'];
        $pdftotextWithSlashes = str_replace('\\', '\\\\', $pdftotextPath);
        CATSUtility::changeConfigSetting('PDFTOTEXT_PATH', '"' . $pdftotextWithSlashes . '"');

        $html2textPath = $_REQUEST['htmlExecutable'];
        $html2textWithSlashes = str_replace('\\', '\\\\', $html2textPath);
        CATSUtility::changeConfigSetting('HTML2TEXT_PATH', '"' . $html2textWithSlashes . '"');

        $unrtfPath = $_REQUEST['rtfExecutable'];
        $unrtfWithSlashes = str_replace('\\', '\\\\', $unrtfPath);
        CATSUtility::changeConfigSetting('UNRTF_PATH', '"' . $unrtfWithSlashes . '"');

        break;

    case 'testResumeParsing2':
        echo '<script type="text/javascript">showTextBlock(\'resumeParsing\');</script>';

        $result = true;

        echo '<br />',
             '<span style="font-weight: bold;">Test Results</span>',
             '<table class="test_output">';

        $antiwordResults = !(ANTIWORD_PATH != '' && !InstallationTests::checkAntiword());
        $pdftotextResults = !(PDFTOTEXT_PATH != '' && !InstallationTests::checkPdftotext());
        $html2textResults = !(HTML2TEXT_PATH != '' && !InstallationTests::checkHtml2text());
        if (UNRTF_PATH != '' && !$html2textResults)
        {
            echo '<tr class="fail"><td>UnRTF depends on Html2Text and can not execute.</td></tr>';
            $unrtfResults = false;
        }
        else
        {
            $unrtfResults = !(UNRTF_PATH != '' && !InstallationTests::checkUnrtf());
        }

        if (!$antiwordResults || !$pdftotextResults)
        {
            echo '<script type="text/javascript">showTextBlock(\'testFailed\');</script>';
        }
        else
        {
            echo '<script type="text/javascript">showTextBlock(\'testPassedParsing\');</script>';
        }

        break;

    case 'optionalComponents':
        MySQLConnect();
        initializeOptionalComponents();

        echo '<script type="text/javascript">';

        /* Detect date format preferences. */
        $rs = MySQLQuery('SELECT date_format_ddmmyy FROM site', true);
        if ($rs)
        {
            $record = mysql_fetch_assoc($rs);
        }
        else
        {
            $record = array();
        }

        if (!isset($record['date_format_ddmmyy']) || $record['date_format_ddmmyy'] == 0)
        {
            echo 'document.getElementById(\'dateFormat\').value = \'mdy\';';
        }
        else
        {
            echo 'document.getElementById(\'dateFormat\').value = \'dmy\';';
        }

        echo 'setActiveStep(6);';
        echo 'showTextBlock(\'pickOptionalComponents\');';
        echo '</script>';

        $onClick  = 'document.getElementById(\'pickOptionalComponents\').style.display = \'none\'; ';
        $onClick .= 'showTextBlock(\'installingComponentsExtra\'); ';
        $onClick .= 'Installpage_populate(\'a=setupOptional&list=';
        foreach ($optionalComponents as $index => $component)
        {
            $onClick .= htmlspecialchars($index) . ',\' + encodeURIComponent(getCheckedValue(document.getElementsByName(\'' . htmlspecialchars($index) . '\'))) + \',';
        }
        $onClick .= '&timeZone=\' + encodeURIComponent(document.getElementById(\'timeZone\').value) + \'&dateFormat=\' + encodeURIComponent(document.getElementById(\'dateFormat\').value) + \'\');';

        echo '<script type="text/javascript">';
        echo 'var onClick = \'' . addslashes($onClick) . '\';';
        echo 'document.getElementById(\'extrasList\').innerHTML = \'<table style="width: 450px;"><tr><td style="font-weight: bold;">Feature Name</td><td style="width: 85px; font-weight: bold">Install</td><td style="width: 85px; font-weight: bold">Do Not Install</td></tr>';
        foreach ($optionalComponents as $index => $component)
        {
            echo '<tr>';
            echo '<td><a href="javascript:void(0);" onclick="function HTML' . htmlspecialchars($index) . '() { return \\\'<p style=\\\' + String.fromCharCode(34) + \\\'font-weight: bold; padding-left: 8px; padding-right: 8px;\\\' + String.fromCharCode(34) + \\\'>' . htmlspecialchars($component['name']) . '</p><p style=\\\' + String.fromCharCode(34) + \\\'padding-left: 8px; padding-right: 8px;\\\' + String.fromCharCode(34) + \\\'>' . htmlspecialchars($component['description']) . '</p>\\\'; } showPopWinHTML(HTML' . htmlspecialchars($index) . '(), 400, 100, null); return false;">' . htmlspecialchars($component['name']) . '</a>&nbsp;&nbsp;&nbsp;</td>';
            echo '<td><input type="radio" name="' . htmlspecialchars($index) . '" value="true"' . ($component['componentExists'] ? ' checked' : '') . '></td>';
            echo '<td><input type="radio" name="' . htmlspecialchars($index) . '" value="false"' . ($component['componentExists'] ? '' : ' checked') . '></td>';
            echo '</tr>';
        }

        echo '</table><br /><br />';

        echo '<input type="button" style="float: right;" class="button" value="Next -->" onclick="\' + onClick + \'">\';</script>';
        break;

    case 'setupOptional':
        MySQLConnect();
        initializeOptionalComponents();

        @session_name(CATS_SESSION_NAME);
        session_start();

        // FIXME: Input validation.
        $timeZone = $_REQUEST['timeZone'];
        CATSUtility::changeConfigSetting('OFFSET_GMT', ($timeZone));

        $dateFormat = $_REQUEST['dateFormat'];

        $_SESSION['timeZoneInstaller'] = $timeZone;
        $_SESSION['dateFormatInstaller'] = $dateFormat;

        $list = explode(',', $_REQUEST['list']);

        for ($i = 0; $i < count($list); $i+=2)
        {
            if (!isset($list[$i+1]))
            {
                continue;
            }

            if ($optionalComponents[$list[$i]]['componentExists'] == false)
            {
                if ($list[$i+1] == 'true')
                {
                    eval($optionalComponents[$list[$i]]['installCode']);
                }
            }
            else
            {
                if ($list[$i+1] == 'false')
                {
                    eval($optionalComponents[$list[$i]]['removeCode']);
                }
            }
        }

        echo '<script type="text/javascript">
                  setActiveStep(7);
                  showTextBlock(\'installingComponentsMaint\');
                  setTimeout("Installpage_populate(\'a=maint\');", 5000);
              </script>';
        break;

    case 'detectRevision':
        MySQLConnect();

        echo '<script type="text/javascript">setActiveStep(3);</script>';

        if (count($tables) == 0)
        {
            echo '<script type="text/javascript">
                      showTextBlock(\'emptyDatabase\');
                      document.getElementById(\'emptyCheckBox\').checked = true;
                  </script>';
            die();
        }

        $rs = MySQLQuery('SELECT * FROM candidate', true);
        $fields = array();
        while ($meta = @mysql_fetch_field($rs))
        {
            if ($meta)
            {
                $fields[$meta->name] = true;
            }
        }

        $catsVersion = '';

        /* Look for more versions here. */
        if (!isset($fields['date_available']) && isset($tables['client']))
        {
            $catsVersion = 'CATS 0.5.0.';
        }
        else if (!isset($tables['candidate_joborder_status']) && isset($tables['client']))
        {
            $catsVersion = 'CATS 0.5.1 or 0.5.2.';
        }
        else if (!isset($tables['candidate_foreign']) && isset($tables['client']))
        {
            $catsVersion = 'CATS 0.5.5.';
        }
        else if (!isset($tables['history']) && isset($tables['client']))
        {
            $catsVersion = 'CATS 0.6.x.';
        }
        else if (isset($tables['history']))
        {
            echo '
                <script type="text/javascript">
                    showTextBlock(\'catsUpToDate\');
                    document.getElementById(\'currentCheckBox\').checked = true;
                </script>';

            echo '<br /><br />';
            die();
        }

        if ($catsVersion == '')
        {
            echo '
                <script type="text/javascript">
                    showTextBlock(\'unknownDataInDatabase\');
                    document.getElementById(\'tableNamesUnknown\').innerHTML = \'\';
                </script>';

            foreach ($tables as $table => $data)
            {
                echo '<script type="text/javascript">document.getElementById(\'tableNamesUnknown\').innerHTML += \'' . htmlspecialchars($table ) . ', \';</script>';
            }
        }
        else
        {
            echo '
                <script type="text/javascript">
                    showTextBlock(\'databaseUpgrade\');
                    document.getElementById(\'upgradeVersion\').innerHTML = \'' . htmlspecialchars($catsVersion) . '\';
                </script>';
        }
        break;

    case 'queryResetDatabase':
        echo '<script type="text/javascript">showTextBlock(\'queryResetDatabase\');</script>';
        break;

    case 'resetDatabase':
        MySQLConnect();

        foreach ($tables as $table => $data)
        {
            $queryResult = MySQLQuery(sprintf("DROP TABLE %s", $table));
        }

        if(!isset($_REQUEST['type']))
        {
            echo '<script type="text/javascript">Installpage_populate(\'a=detectRevision\');</script>';
        }
        else
        {
            echo '<script type="text/javascript">Installpage_populate(\'a=selectDBType&type=' . urlencode($_REQUEST['type']) . '\');</script>';
        }
        break;

    case 'selectDBType':
        $type = $_REQUEST['type'];

        switch ($type)
        {
            case 'empty':
                echo '<script type="text/javascript">
                          showTextBlock(\'installingComponents\');
                          Installpage_populate(\'a=doInstallEmptyDatabase\');
                      </script>';
                break;

            case 'demo':
                echo '<script type="text/javascript">showTextBlock(\'queryInstallDemo\');</script>';
                break;

            case 'restore':
                echo '<script type="text/javascript">
                          document.getElementById(\'continueRestoreCheck\').checked = false;
                          showTextBlock(\'queryInstallBackup\');
                      </script>';
                break;

            default:
                break;
        }
        break;

    case 'restoreFromBackup':
        include_once('lib/FileCompressor.php');
        MySQLConnect();
        $extractor = new ZipFileExtractor('./restore/catsbackup.bak');
        
        CATSUtility::changeConfigSetting('ENABLE_DEMO_MODE', 'false');

        /* Extract the file.  This command also executes all sql commands in the file. */
        /* Normally, we could just do the following lines, but we want a custom extractor
           that ignores the file 'database', and executes all of the catsbackup.sql.xxx
           files rather than extracting them. */
        /* 
            if (!$extractor->open() || !$extractor->extractAll())
            {
                echo($extractor->getErrorMessage());
            }
        */
        
        if (!$extractor->open())
        {
            echo($extractor->getErrorMessage());
        }       
        
        $metaData = $extractor->getMetaData();
        
        foreach ($metaData['centralDirectory'] as $index => $data)
        {
            $fileName = $data['filename'];

            /* Execute all sql files */
            if (strpos($fileName, 'db/catsbackup.sql.') === 0)
            {
                $fileContents = $extractor->getFile($index);
                MySQLQueryMultiple($fileContents, '((ENDOFQUERY))');
            }
            /* Extract everything else but ./database */
            else if ($fileName != 'database')
            {
                if (strpos($fileName, '/') !== false)
                {
                    $directorySplit = explode('/', $fileName);
                    unset($directorySplit[count($directorySplit)-1]);
                    $directory = implode('/', $directorySplit);
                    @mkdir($directory, 0777, true);
                }

                $fileContents = $extractor->getFile($index);
                
                if ($fileContents === false)
                {
                    /* Report error? */
                }
                
                file_put_contents ($fileName, $fileContents);
            }
        }
        
        echo '<script type="text/javascript">Installpage_populate(\'a=upgradeCats\');</script>';
        break;

    case 'doDeleteBackup':
        echo '<script type="text/javascript">Installpage_populate(\'a=detectRevision\', \'subFormBlock\', \'\');</script>';
        break;

    case 'doInstallEmptyDatabase':
        MySQLConnect();

        CATSUtility::changeConfigSetting('ENABLE_DEMO_MODE', 'false');

        $schema = file_get_contents('db/cats_schema.sql');
        MySQLQueryMultiple($schema, ";\n");

        //Check if we need to update from 0.6.0 to 0.7.0
        $tables = array();
        $result = MySQLQuery(sprintf("SHOW TABLES FROM `%s`", DATABASE_NAME));
        while ($row = mysql_fetch_array($result, MYSQL_NUM))
        {
            $tables[$row[0]] = true;
        }

        if (!isset($tables['history']))
        {
            // FIXME: File exists?!
            $schema = file_get_contents('db/upgrade-0.6.x-0.7.0.sql');
            MySQLQueryMultiple($schema);
        }

        echo '<script type="text/javascript">Installpage_populate(\'a=resumeParsing\');</script>';
        break;

    case 'onLoadDemoData':
        CATSUtility::changeConfigSetting('ENABLE_DEMO_MODE', 'true');

        include_once('lib/FileCompressor.php');
        MySQLConnect();
        $extractor = new ZipFileExtractor('./db/cats_testdata.bak');
        
        /* Extract the file.  This command also executes all sql commands in the file. */
        /* Normally, we could just do the following lines, but we want a custom extractor
           that ignores the file 'database', and executes all of the catsbackup.sql.xxx
           files rather than extracting them. */
        /* 
            if (!$extractor->open() || !$extractor->extractAll())
            {
                echo($extractor->getErrorMessage());
            }
        */
        
        if (!$extractor->open())
        {
            echo($extractor->getErrorMessage());
        }       
        
        $metaData = $extractor->getMetaData();
        
        foreach ($metaData['centralDirectory'] as $index => $data)
        {
            $fileName = $data['filename'];

            /* Execute all sql files */
            if (strpos($fileName, 'db/catsbackup.sql.') === 0)
            {
                $fileContents = $extractor->getFile($index);
                MySQLQueryMultiple($fileContents, '((ENDOFQUERY))');
            }
            /* Extract everything else but ./database */
            else if ($fileName != 'database')
            {
                if (strpos($fileName, '/') !== false)
                {
                    $directorySplit = explode('/', $fileName);
                    unset($directorySplit[count($directorySplit)-1]);
                    $directory = implode('/', $directorySplit);
                    @mkdir($directory, 0777, true);
                }

                $fileContents = $extractor->getFile($index);
                
                if ($fileContents === false)
                {
                    /* Report error? */
                }
                
                file_put_contents ($fileName, $fileContents);
            }
        }

        echo '
            <script type="text/javascript">
                showTextBlock(\'installingComponents\');
                Installpage_populate(\'a=upgradeCats\');
            </script>';
        break;

    case 'upgradeCats':
        MySQLConnect();

        /* This shouldn't be possible - there is no option to upgrade CATS if no tables are in the database. */
        if (count($tables) == 0)
        {
            echo 'Error - no schema present.<br /><br /> ';
            echo '<input type="button" class="button" value="Retry Installation" onclick="Installpage_populate(\'a=detectConnectivity\', \'subFormBlock\', \'Checking database connectivity...\');">&nbsp;&nbsp;&nbsp;';
            die();
        }

        $revision = 0;
        $rs = MySQLQuery('SELECT * FROM candidate', true);
        $fields = array();
        while ($meta = mysql_fetch_field($rs))
        {
            $fields[$meta->name] = true;
        }

        /* Look for more versions here. */
        if (!isset($fields['date_available']))
        {
            /* 0.5.0 */
            $revision = 50;
        }
        else if (!isset($tables['candidate_joborder_status']))
        {
            /* 0.5.2 */
            $revision = 52;
        }
        else if (!isset($tables['candidate_foreign']) && !isset($tables['extra_field']))
        {
            /* 0.5.5 */
            $revision = 55;
        }
        else if (!isset($tables['history']))
        {
            /* 0.6.0 */
            $revision = 60;
        }
        else if (isset($tables['history']))
        {
            /* 0.7.0 */
            $revision = 70;
        }

        if ($revision <= 50)
        {
            // FIXME: File exists?!
            $schema = file_get_contents('db/upgrade-0.5.0-0.5.1.sql');
            MySQLQueryMultiple($schema);
        }
        if ($revision <= 52)
        {
            // FIXME: File exists?!
            $schema = file_get_contents('db/upgrade-0.5.2-0.5.5.sql');
            MySQLQueryMultiple($schema);
        }
        if ($revision <= 55)
        {
            // FIXME: File exists?!
            $schema = file_get_contents('db/upgrade-0.5.5-0.6.x.sql');
            MySQLQueryMultiple($schema);
        }
        if ($revision <= 60)
        {
            // FIXME: File exists?!
            $schema = file_get_contents('db/upgrade-0.6.x-0.7.0.sql');
            MySQLQueryMultiple($schema);
        }

        // FIXME: File exists?!
        $schema = @file_get_contents('db/upgrade-zipcodes.sql');
        MySQLQueryMultiple($schema);

        echo '<script type="text/javascript">Installpage_populate(\'a=resumeParsing\');</script>';
        break;

    case 'maint':
        @session_name(CATS_SESSION_NAME);
        session_start();

        if (isset($_SESSION['CATS']))
        {
            unset($_SESSION['CATS']);
        }

        if (isset($_SESSION['modules']))
        {
            unset($_SESSION['modules']);
        }

        echo '<script type="text/javascript">
                  showTextBlock(\'installingComponentsMaint\');
                  setTimeout("Installpage_maint();", 2000);
              </script>';
        break;

    case 'reindexResumes':
        echo '<script type="text/javascript">
                  showTextBlock(\'installingComponentsMaintResume\');
                  Installpage_populate(\'a=onReindexResumes\');
              </script>';
        break;
            
    case 'onReindexResumes':
        include_once('modules/install/ajax/attachmentsReindex.php');
        
        echo '<script type="text/javascript">
                  Installpage_populate(\'a=maintComplete\');
              </script>';
        
        break;

    case 'maintComplete':
        MySQLConnect();

        // FIXME: Make sure we have permissions to create INSTALL_BLOCK.
        file_put_contents(
            'INSTALL_BLOCK',
            'This file prevents the installer from running. Remove this file to edit or reset your CATS installation.'
        );

        @session_name(CATS_SESSION_NAME);
        session_start();


        $fromAddress = $_SESSION['fromAddressInstaller'];

        // If this is an existing database, just set all the fromAddress settings to new
        MySQLQuery(sprintf('UPDATE settings SET value = "%s" WHERE setting = "fromAddress"', $fromAddress));
        // This is a new install, insert a settings value for each site in the database
        if(mysql_affected_rows() == 0)
        {
            // Insert a "fromAddress" = $fromAddress for each site
            MySQLQuery(sprintf(
                'INSERT INTO settings (setting, value, site_id, settings_type) '
                . 'SELECT "fromAddress", "%s", site_id, 1 FROM site',
                $fromAddress
            ));
            // Insert a "configured" = 1 setting for each site
            MySQLQuery(
                'INSERT INTO settings (setting, value, site_id, settings_type) '
                . 'SELECT "configured", "1", site_id, 1 FROM site'
            );
        }

        /* We can't set date format ortime zone until installer is complete
         * (rows don't exist in schema till now.)
         */

        $dateFormat = $_SESSION['dateFormatInstaller'];

        if ($dateFormat == 'mdy')
        {
            MySQLQuery('UPDATE site SET date_format_ddmmyy = 0');
        }
        else
        {
            MySQLQuery('UPDATE site SET date_format_ddmmyy = 1');
        }

        $timeZone = $_SESSION['timeZoneInstaller'];

        MySQLQuery(sprintf("UPDATE site SET time_zone = %s", $timeZone));

        if (isset($_SESSION['CATS']))
        {
            unset($_SESSION['CATS']);
        }

        if (isset($_SESSION['modules']))
        {
            unset($_SESSION['modules']);
        }

        echo '<script type="text/javascript">setActiveStep(7);</script>';

        if (ENABLE_DEMO_MODE)
        {
            echo '<script type="text/javascript">showTextBlock("installCompleteDemo");</script>';
        }
        else
        {
            echo '<script type="text/javascript">showTextBlock("installCompleteProd");</script>';
        }
        break;

    case 'loginCATS':
        MySQLConnect();

        /* Determine if a default user is set. */
        $rs = MySQLQuery("SELECT * FROM user WHERE user_name = 'admin' AND password = 'cats'");
        if ($rs && mysql_fetch_row($rs))
        {
            //Default user set
            echo '<script type="text/javascript">document.location.href="index.php?defaultlogin=true";</script>';
        }
        else
        {
            echo '<script type="text/javascript">document.location.href="index.php";</script>';
        }
        break;

    default:
        die('Invalid action.');
        break;
}

function MySQLConnect()
{
    global $tables, $mySQLConnection;

    $mySQLConnection = @mysql_connect(
        DATABASE_HOST, DATABASE_USER, DATABASE_PASS
    );

    if (!$mySQLConnection)
    {
        die(
            '<p style="background: #ec3737; padding: 4px; margin-top: 0; font:'
            . ' normal normal bold 12px/130% Arial, Tahoma, sans-serif;">Error '
            . " Connecting to Database</p><pre>\n\n" . mysql_error() . "</pre>\n\n"
        );
        return false;
    }


    /* Create an array of all tables in the database. */
    $tables = array();
    $result = MySQLQuery(sprintf("SHOW TABLES FROM `%s`", DATABASE_NAME));
    while ($row = mysql_fetch_row($result))
    {
        $tables[$row[0]] = true;
    }

    /* Select CATS database. */
    $isDBSelected = @mysql_select_db(DATABASE_NAME, $mySQLConnection);
    if (!$isDBSelected)
    {
        $error = mysql_error($mySQLConnection);

        die(
            '<p style="background: #ec3737; padding: 4px; margin-top: 0; font:'
            . ' normal normal bold 12px/130% Arial, Tahoma, sans-serif;">Error'
            . " Selecting Database</p><pre>\n\n" . $error . "</pre>\n\n"
        );
        return false;
    }
}

function MySQLQuery($query, $ignoreErrors = false)
{
    global $mySQLConnection;

    $queryResult = mysql_query($query, $mySQLConnection);
    if (!$queryResult && !$ignoreErrors)
    {
        $error = mysql_error($mySQLConnection);

        if ($error == 'Query was empty')
        {
            return $queryResult;
        }

        die (
            '<p style="background: #ec3737; padding: 4px; margin-top: 0; font:'
            . ' normal normal bold 12px/130% Arial, Tahoma, sans-serif;">Query'
            . " Error -- Please Report This Bug!</p><pre>\n\nMySQL Query "
            . "Failed: " . $error . "\n\n" . $query . "</pre>\n\n"
        );
    }

    return $queryResult;
}

function MySQLQueryMultiple($SQLData, $delimiter = ';')
{
    $SQLStatments = explode($delimiter, $SQLData);

    foreach ($SQLStatments as $SQL)
    {
        $SQL = trim($SQL);

        if (empty($SQL))
        {
            continue;
        }

        MySQLQuery($SQL);
    }
}

function initializeOptionalComponents()
{
    global $optionalComponents;

    //Detect which components are installed and which ones are not
    include_once('modules/install/OptionalComponents.php');

    foreach ($optionalComponents as $index => $data)
    {
        if (isset($data['detectCode']))
        {
            $optionalComponents[$index]['componentExists'] = eval($data['detectCode']);
        }
        else
        {
            $optionalComponents[$index]['componentExists'] = false;
        }
    }
}

?>
