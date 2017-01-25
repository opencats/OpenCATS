<?php
/**
 * CATS
 * Installation Tests Library
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
 * @copyright  Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: InstallationTests.php 3809 2007-12-05 02:12:35Z will $
 */

//FIXME: Test for zlib!

include_once('./lib/FileUtility.php');

/**
 *	Installation Tests Library
 *	@package    CATS
 *	@subpackage Library
 */
class InstallationTests
{
    /* Set this to true to force all tests to fail for debugging. */
    const DEBUG_FAIL = false;
    
    public static function runCoreTests()
    {
        $proceed = true;

        $proceed = $proceed && self::printCATSVersion();
        $proceed = $proceed && self::checkPHPVersion();
        $proceed = $proceed && self::checkMagicQuotes();
        $proceed = $proceed && self::checkRegisterGlobals();
        $proceed = $proceed && self::checkSessionAutoStart();
        $proceed = $proceed && self::checkMySQLExtension();
        $proceed = $proceed && self::checkSessionExtension();
        $proceed = $proceed && self::checkCTypeExtension();
        $proceed = $proceed && self::checkGD2Extension();
        $proceed = $proceed && self::checkLDAPExtension();
        $proceed = $proceed && self::checkPCREExtension();
        $proceed = $proceed && self::checkSOAPExtension();
        $proceed = $proceed && self::checkZipExtension();

        return $proceed;
    }
    
    public static function runInstallerTests()
    {
        global $result;
        
        if (!isset($result))
        {
            $result = true;
        }
        
        if (!InstallationTests::checkPHPVersion())
        {
            $result = false;
        }
        
        if (!InstallationTests::checkMagicQuotes())
        {
            $result = false;
        }
        
        if (!InstallationTests::checkRegisterGlobals())
        {
            $result = false;
        }
        
        if (!InstallationTests::checkMySQLExtension())
        {
            $result = false;
        }
        
        if (!InstallationTests::checkSessionExtension())
        {
            $result = false;
        }
        
        if (!InstallationTests::checkPCREExtension())
        {
            $result = false;
        }
        
        if (!InstallationTests::checkCTypeExtension())
        {
            $result = false;
        }
        
        if (!InstallationTests::checkGD2Extension())
        {
            $result = false;
        }
        
        if (!InstallationTests::checkLDAPExtension())
        {
            $result = false;
        }

        if (!InstallationTests::checkSOAPExtension())
        {
            $result = false;
        }
        
        if (!InstallationTests::checkZipExtension())
        {
            $result = false;
        }
        
        if (!InstallationTests::checkAttachmentsDir())
        {
            $result = false;
        }
        
        if (!InstallationTests::checkConfigWritable())
        {
            $result = false;
        }
        
        if (!InstallationTests::checkDirectoryWritable())
        {
            $result = false;
        }
    }

    /* Print the CATS version information just for informational purposes, for
     * example when someone posts installtest output to the CATS Forums.
     */
    public static function printCATSVersion()
    {
        echo sprintf(
            '<tr class="pass"><td>CATS version is %s.</td></tr>',
            CATS_VERSION
        );

        return true;
    }

    /* Check PHP version. */
    public static function checkPHPVersion()
    {
        if (!self::DEBUG_FAIL && version_compare(PHP_VERSION, '5.0.0', '>='))
        {
            echo sprintf(
                '<tr class="pass"><td>PHP version is %s.</td></tr>',
                PHP_VERSION
            );

            return true;
        }

        echo sprintf(
            '<tr class="fail"><td><strong>PHP 5.0.0 or greater is required to run OpenCATS.</strong><br />'
            . 'Found version: %s.</td></tr>',
            PHP_VERSION
        );
        return false;
    }

    /* magic_quotes_runtime cannot be enabled. */
    public static function checkMagicQuotes()
    {
        if (!self::DEBUG_FAIL && !get_magic_quotes_runtime())
        {
            echo '<tr class="pass"><td>PHP.ini: magic_quotes_runtime is disabled.</td></tr>';
            return true;
        }

        echo '<tr class="fail"><td><strong>PHP.ini: magic_quotes_runtime must be set to Off in php.ini.</strong><br />'
            . 'Check your settings in php.ini.</td></tr>';
        return false;
    }

    /* Warn if register_globals is on. */
    public static function checkRegisterGlobals()
    {
        if (!self::DEBUG_FAIL && !ini_get('register_globals'))
        {
            echo '<tr class="pass"><td>PHP.ini: register_globals is disabled.</td></tr>';
            return true;
        }

        echo '<tr class="warning"><td><strong>PHP.ini: register_globals is enabled in php.ini.</strong><br />'
            . 'Check your settings in php.ini.<br /><br />'
            . 'The developers of PHP recommend that this be disabled, as it can cause security problems. Please disable it.</td></tr>';
        $GLOBALS['warningsOccurred'] = true;
        return true;
    }

    /* Objects can't be stored in the session if session.auto_start is enabled. */
    public static function checkSessionAutoStart()
    {
        if (!self::DEBUG_FAIL && !ini_get('session.auto_start'))
        {
            echo '<tr class="pass"><td>PHP.ini: session.auto_start is disabled.</td></tr>';
            return true;
        }

        echo '<tr class="fail"><td><strong>PHP.ini: session.auto_start must be set to 0 in php.ini.</strong><br />'
            . 'Check your settings in php.ini.</td></tr>';
        return false;
    }

    /* Is MySQL extension loaded?. */
    public static function checkMySQLExtension()
    {
        if (!self::DEBUG_FAIL && extension_loaded('mysql') && function_exists('mysql_connect'))
        {
            echo '<tr class="pass"><td>PHP MySQL extension (mysql) is loaded.</td></tr>';
            return true;
        }

        echo '<tr class="fail"><td><strong>PHP MySQL extension (mysql) is not loaded.</strong><br />'
            . 'Check your settings in php.ini.<br /><br />'
            . 'Under certain Linux / BSD distributions, the PHP MySQL extension is a separate package.<br /><br />'
            . '<strong>Debian:</strong> Run "apt-get install php5-mysql" and restart your webserver.<br /><br />'
            . '<strong>FreeBSD:</strong> Install the php5-mysql port, or configure MySQL support in the '
            . 'php-extensions port and restart your webserver.</td></tr>';
        return false;
    }

    /* Is the session extension loaded?. */
    public static function checkSessionExtension()
    {
        if (!self::DEBUG_FAIL && extension_loaded('session') && function_exists('session_start'))
        {
            echo '<tr class="pass"><td>PHP Sessions extension (session) is loaded.</td></tr>';
            return true;
        }

        echo '<tr class="fail"><td><strong>PHP Sessions extension (session) is not loaded.</strong><br />'
            . 'Check your settings in php.ini.<br /><br />'
            . 'Under certain Linux / BSD distributions, the PHP session extension is a separate package.<br /><br />'
            . '<strong>Debian:</strong> Run "apt-get install php5-session" and restart your webserver.<br /><br />'
            . '<strong>FreeBSD:</strong> Install the php5-session session port, or configure session support in the'
            . ' php-extensions port and restart your webserver.<br /><br />';
        return false;
    }

    /* Check for ctype_*() support. */
    public static function checkCTypeExtension()
    {
        if (!self::DEBUG_FAIL && extension_loaded('ctype') && function_exists('ctype_digit'))
        {
            echo '<tr class="pass"><td>PHP CType string classification extension (ctype) is loaded.</td></tr>';
            return true;
        }

        echo '<tr class="fail"><td><strong>PHP CType string classification extension (ctype) is not loaded.</strong><br />'
            . 'Check your settings in php.ini.<br /><br />'
            . 'Under certain Linux / BSD distributions, the PHP CType extension is a separate package.<br /><br />'
            . '<strong>Debian:</strong> Run "apt-get install php5-ctype" and restart your webserver.<br /><br />'
            . '<strong>FreeBSD:</strong> Install the php5-ctype port, or configure CType support in the php-extensions port and restart your webserver.<br /><br />'
            . '<strong>See also:</strong> <a target="_blank" href="http://www.google.com/search?q=%22Call+to+undefined+function+ctype_digit%28%29%22">Google: "Call to undefined function ctype_digit()"</a></td></tr>';

        return false;
    }

    /* Check for preg_*() support. */
    public static function checkPCREExtension()
    {
        if (!self::DEBUG_FAIL && extension_loaded('pcre') && function_exists('preg_match'))
        {
            echo '<tr class="pass"><td>PHP PCRE regular expressions extension (pcre) is loaded.</td></tr>';
            return true;
        }

        echo '<tr class="fail"><td><strong>PHP PCRE regular expressions extension (pcre) is not loaded.</strong><br />'
            . 'Check your settings in php.ini.<br /><br />'
            . 'Under certain Linux / BSD distributions, the PHP PCRE extension is a separate package.<br /><br />'
            . '<strong>Debian:</strong> Run "apt-get install php5-pcre" and restart your webserver.<br /><br />'
            . '<strong>FreeBSD:</strong> Install the php5-pcre port, or configure PCRE support in the php-extensions port and restart your webserver.</td></tr>';

        return false;
    }

    /* Check for libgd support. */
    public static function checkGD2Extension()
    {
        /* Is the GD2 extension loaded?. */
        if (!self::DEBUG_FAIL && extension_loaded('gd') && function_exists('ImageCreateFromJpeg'))
        {
            echo '<tr class="pass"><td>PHP GD image manipulation library extension (gd) is loaded.</td></tr>';
            return true;
        }

        // FIXME: More information.
        echo '<tr class="warning"><td><strong>PHP GD image manipulation library extension (gd) is not loaded.</strong><br />'
            . 'Check your settings in php.ini.<br /><br />OpenCATS will function without GD, but no graphs will load.<br /><br />'
            . 'Under certain Linux / BSD distributions, the PHP GD extension is a separate package.<br /><br />'
            . '<strong>Ubuntu:</strong> Run "apt-get install php5-gd" and restart your webserver.<br /><br />'
            . '<strong>Debian:</strong> Run "apt-get install php5-gd" and restart your webserver.<br /><br />'
            . '<strong>FreeBSD:</strong> Install the php5-gd port, or configure GD support in the php-extensions port and restart your webserver.'
            . '</td></tr>';
        $GLOBALS['warningsOccurred'] = true;
        return true;
    }

    /* Check for php-ldap support. */
    public static function checkLDAPExtension()
    {
        /* Is the GD2 extension loaded?. */
        if (!self::DEBUG_FAIL && extension_loaded('ldap') && function_exists('ldap_connect'))
        {
            echo '<tr class="pass"><td>PHP LDAP library extension (ldap) is loaded.</td></tr>';
            return true;
        }

        echo '<tr class="warning"><td><strong>PHP LDAP library extension (ldap) is not loaded.</strong><br />'
            . 'Check your settings in php.ini.<br /><br />OpenCATS will function without LDAP, but will not authenticate from a LDAP service<br /><br />'
            . 'Under certain GNU/Linux distributions, the PHP LDAP extension is a separate package.<br /><br />'
            . '<strong>Ubuntu/Debian:</strong> Run "apt-get install php5-ldap" and restart your webserver.<br /><br />'
            . '<strong>Fedora/CentOS/RHEL:</strong> Run "dnf install php-ldap" or "yum install php-ldap" and restart your webserver.<br /><br />'
            . '</td></tr>';
        $GLOBALS['warningsOccurred'] = true;
        return true;
    }

    /* Check for SOAP support. */
    public static function checkSOAPExtension()
    {
        /* Is the SOAP extension loaded?. */
        /* The is_callable function seems to work with   */
        if (!self::DEBUG_FAIL && extension_loaded('soap') && class_exists('SoapClient') &&
            (method_exists('SoapClient', '__soapCall') ||
            method_exists('SoapClient', '__call')))
        {
            echo '<tr class="pass"><td>PHP SOAP extension (soap) is loaded.</td></tr>';
            return true;
        }

        echo '<tr class="warning"><td><strong>PHP SOAP extension (soap) is not loaded.</strong><br />'
            . 'Check your settings in php.ini.<br /><br />'
            . 'OpenCATS will function without SOAP, but '
            . 'CATS Professional functionality will not be supported.<br /><br />'
            . 'Under certain Linux / BSD distributions, the PHP SOAP extension is a separate package.<br /><br />'
            . '<strong>Ubuntu:</strong> Run "apt-get install php-soap" and restart your webserver.<br /><br />'
            . '<strong>Debian:</strong> Run "apt-get install php5-soap" and restart your webserver.<br /><br />'
            . '<strong>FreeBSD:</strong> Install the php5-soap port, or configure SOAP support in the php-extensions port and restart your webserver.'
            . '</td></tr>';
        $GLOBALS['warningsOccurred'] = true;
        return true;
    }

    /* Check for Zip support. */
    public static function checkZipExtension()
    {
        /* Is the ZIP extension loaded?. */
        if (!self::DEBUG_FAIL && extension_loaded('zip') && class_exists('ZipArchive'))
        {
            echo '<tr class="pass"><td>PHP zip extension is loaded.</td></tr>';
            return true;
        }

        echo '<tr class="warning"><td><strong>PHP Zip support extension (zip) is not loaded.</strong><br />'
            . 'Check your settings in php.ini.<br /><br />'
            . 'openCATS will function without zip, but '
            . 'attachment handling functionality will be limited.<br /><br />'
            . '</td></tr>';
        $GLOBALS['warningsOccurred'] = true;
        return true;
    }

    /* Run a series of tests against the MySQL database. */
    public static function checkMySQL($host, $user, $pass, $name)
    {
        /* Check MySQL connection. */
        if (self::DEBUG_FAIL || !@mysql_connect($host, $user, $pass))
        {
            echo sprintf(
                '<tr class="fail"><td>Cannot connect to database.<pre class="fail">%s</pre></td></tr>',
                mysql_error()
            );
            return false;
        }

        echo '<tr class="pass"><td>MySQL connection was successful.</td></tr>';

        /* Check MySQL version number. */
        if (!self::_checkMySQLVersion())
        {
            return false;
        }

        /* Try to switch to the CATS database. */
        if (!@mysql_select_db($name))
        {
            echo sprintf(
                '<tr class="fail"><td>Failed to select database \'%s\'.<pre class="fail">%s</pre></td></tr>',
                $name,
                mysql_error()
            );
            return false;
        }

        echo sprintf(
            '<tr class="pass"><td>Database \'%s\' selected.</td></tr>',
            $name
        );

        /* Check CREATE TABLE permissions. */
        $queryResult = @mysql_query('CREATE TABLE `testtable` (`id` int(11) NOT NULL default \'0\') ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
        if (!$queryResult)
        {
            mysql_query('DROP TABLE testtable');
            $queryResult = @mysql_query('CREATE TABLE `testtable` (`id` int(11) NOT NULL default \'0\') ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
        }
        if (!$queryResult)
        {
            echo sprintf(
                '<tr class="fail"><td>Cannot create table \'testtable\'. Please verify that '
                . '\'ALL PERMISSIONS\' were granted to the \'%s\' user for the \'%s\' database. '
                . 'You may also have to recreate the database before trying again.',
                $user,
                $name
            );
            return false;
        }

        echo sprintf(
            '<tr class="pass"><td>Can create \'testtable\' table.</td></tr>'
        );

        /* Check INSERT permissions. */
        if (!@mysql_query('INSERT INTO testtable (id) VALUES (1)'))
        {
            echo sprintf(
                '<tr class="fail"><td>Cannot insert into \'testtable\' table. Please verify that '
                . '\'ALL PERMISSIONS\' were granted to the \'%s\' user for the \'%s\' database. '
                . 'You may also have to recreate the database before trying again.',
                $user,
                $name
            );
            return false;
        }

        echo '<tr class="pass"><td>Can insert into \'testtable\' table.</td></tr>';

        /* Check UPDATE permissions. */
        if (!@mysql_query('UPDATE testtable SET id = 5 WHERE id = 1'))
        {
            echo sprintf(
                '<tr class="fail"><td>Cannot update \'testtable\' table. Please verify that '
                . '\'ALL PERMISSIONS\' were granted to the \'%s\' user for the \'%s\' database. '
                . 'You will also need to re-import the database schema.',
                $user,
                $name
            );
            return false;
        }

        echo '<tr class="pass"><td>Can update \'testtable\' table.</td></tr>';

        /* Check DELETE permissions. */
        if (!@mysql_query('DELETE FROM testtable WHERE id = 5'))
        {
            echo sprintf(
                '<tr class="fail"><td>Cannot delete from \'testtable\' table. Please verify that '
                . '\'ALL PERMISSIONS\' were granted to the \'%s\' user for the \'%s\' database. '
                . 'You will also need to re-import the database schema.',
                $user,
                $name
            );
            return false;
        }

        echo '<tr class="pass"><td>Can delete from \'testtable\' table.</td></tr>';

        /* Check DROP TABLES permissions. */
        if (!@mysql_query('DROP TABLE testtable'))
        {
            echo sprintf(
                '<tr class="fail"><td>Cannot drop table \'testtable\'. Please verify that '
                . '\'ALL PERMISSIONS\' were granted to the \'%s\' user for the \'%s\' database. '
                . 'You will also need to re-import the database schema.',
                $user,
                $name
            );
            return false;
        }

        echo '<tr class="pass"><td>Can drop table \'testtable\'.</td></tr>';

        return true;
    }

    public static function checkAttachmentsDir()
    {
        return (!self::DEBUG_FAIL && self::_checkReadWrite('./attachments'));
    }

    public static function checkTempDir()
    {
        return (!self::DEBUG_FAIL && self::_checkReadWrite('./temp'));
    }

    /* Check for Antiword. */
    public static function checkAntiword()
    {
        if (self::DEBUG_FAIL || !is_executable(ANTIWORD_PATH))
        {
            if (file_exists(ANTIWORD_PATH))
            {
                echo sprintf(
                    '<tr class="fail"><td>Antiword binary %s is not executable (permissions: %s).</td></tr>',
                    ANTIWORD_PATH,
                    FileUtility::getOctalPermissions(ANTIWORD_PATH)
                );
            }
            else
            {
                echo sprintf(
                    '<tr class="fail"><td>Antiword binary %s does not exist.</td></tr>',
                    ANTIWORD_PATH
                );
            }

            return false;
        }

        include_once('lib/DocumentToText.php');
        $documentToText = new DocumentToText();
        if ($documentToText->convert('modules/install/testdocs/test.doc', DOCUMENT_TYPE_DOC))
        {
            $resumeText = $documentToText->getString();
        }
        else
        {
            $resumeText = '';
        }

        if (strpos($resumeText, 'This is a test document.') === false)
        {
            echo '<tr class="fail"><td>Antiword binary failed to convert a DOC file to text properly (Should have returned \'This is a test document\', returned \'', htmlspecialchars($resumeText), '\').</td></tr>';
            return false;
        }
        else
        {
            echo sprintf(
                '<tr class="pass"><td>Antiword binary %s can convert DOC files to text.</td></tr>',
                ANTIWORD_PATH
            );
        }
        return true;
    }

    /* Check for PdfToText. */
    public static function checkPdftotext()
    {
        if (self::DEBUG_FAIL || !is_executable(PDFTOTEXT_PATH))
        {
            if (file_exists(PDFTOTEXT_PATH))
            {
                echo sprintf(
                    '<tr class="fail"><td>Pdftotext binary %s is not executable (permissions: %s).</td></tr>',
                    PDFTOTEXT_PATH,
                    FileUtility::getOctalPermissions(PDFTOTEXT_PATH)
                );
            }
            else
            {
                echo sprintf(
                    '<tr class="fail"><td>Pdftotext binary %s does not exist.</td></tr>',
                    PDFTOTEXT_PATH
                );
            }

            return false;
        }

        include_once('lib/DocumentToText.php');
        $documentToText = new DocumentToText();
        if ($documentToText->convert('modules/install/testdocs/test.pdf', DOCUMENT_TYPE_PDF))
        {
            $resumeText = $documentToText->getString();
        }
        else
        {
            $resumeText = '';
        }

        if (strpos($resumeText, 'This is a test document.') === false)
        {
            echo '<tr class="fail"><td>Pdftotext binary failed to convert a PDF file to text properly (Should have returned \'This is a test document\', returned \'', htmlspecialchars($resumeText), '\').</td></tr>';
            return false;
        }
        else
        {
            echo sprintf(
                '<tr class="pass"><td>Pdftotext binary %s can convert PDF files to text.</td></tr>',
                PDFTOTEXT_PATH
            );
        }
        return true;
    }

    /* Check for Html2text. */
    public static function checkHtml2text()
    {
        if (self::DEBUG_FAIL || !is_executable(HTML2TEXT_PATH))
        {
            if (file_exists(HTML2TEXT_PATH))
            {
                echo sprintf(
                    '<tr class="fail"><td>Html2Text binary %s is not executable (permissions: %s).</td></tr>',
                    HTML2TEXT_PATH,
                    FileUtility::getOctalPermissions(HTML2TEXT_PATH)
                );
            }
            else
            {
                echo sprintf(
                    '<tr class="fail"><td>Html2Text binary %s does not exist.</td></tr>',
                    HTML2TEXT_PATH
                );
            }

            return false;
        }

        include_once('lib/DocumentToText.php');
        $documentToText = new DocumentToText();
        if ($documentToText->convert('modules/install/testdocs/test.html', DOCUMENT_TYPE_HTML))
        {
            $resumeText = $documentToText->getString();
        }
        else
        {
            $resumeText = '';
        }

        if (strpos($resumeText, 'This is a test document.') === false)
        {
            echo '<tr class="fail"><td>Html2Text binary failed to convert a HTML file to text properly (Should have returned \'This is a test document\', returned \'', htmlspecialchars($resumeText), '\').</td></tr>';
            return false;
        }
        else
        {
            echo sprintf(
                '<tr class="pass"><td>Html2Text binary %s can convert HTML files to text.</td></tr>',
                HTML2TEXT_PATH
            );
        }
        return true;
    }

    /* Check for Unrtf. */
    public static function checkUnrtf()
    {
        if (self::DEBUG_FAIL || !is_executable(UNRTF_PATH))
        {
            if (file_exists(UNRTF_PATH))
            {
                echo sprintf(
                    '<tr class="fail"><td>UnRTF binary %s is not executable (permissions: %s).</td></tr>',
                    HTML2TEXT_PATH,
                    FileUtility::getOctalPermissions(HTML2TEXT_PATH)
                );
            }
            else
            {
                echo sprintf(
                    '<tr class="fail"><td>UnRTF binary %s does not exist.</td></tr>',
                    HTML2TEXT_PATH
                );
            }

            return false;
        }

        include_once('lib/DocumentToText.php');
        $documentToText = new DocumentToText();
        if ($documentToText->convert('modules/install/testdocs/test.rtf', DOCUMENT_TYPE_RTF))
        {
            $resumeText = $documentToText->getString();
        }
        else
        {
            $resumeText = '';
        }

        if (strpos($resumeText, 'This is a test document.') === false)
        {
            echo '<tr class="fail"><td>UnRTF binary failed to convert a RTF file to text properly (Should have returned \'This is a test document\', returned \'', htmlspecialchars($resumeText), '\').</td></tr>';
            return false;
        }
        else
        {
            echo sprintf(
                '<tr class="pass"><td>UnRTF binary %s can convert RTF files to text.</td></tr>',
                UNRTF_PATH
            );
        }
        return true;
    }

    public static function checkConfigWritable()
    {
        $proceed = true;
        if (!self::DEBUG_FAIL && is_writable('config.php'))
        {
            echo sprintf(
                '<tr class="pass"><td>Configuration file ./config.php is writable.</td></tr>'
            );
        }
        else
        {
            echo sprintf(
                '<tr class="fail"><td><strong>Configuration file ./config.php is not writable!</strong><br />'
                . 'Please check your permissions and try again.</td></tr>'
            );
            $proceed = false;
        }
        return $proceed;
    }

    public static function checkDirectoryWritable()
    {
        $proceed = true;
        if (!self::DEBUG_FAIL && FileUtility::isDirectoryWritable('./'))
        {
            echo sprintf(
                '<tr class="pass"><td>Creating a file within ./ succeeded.</td></tr>'
            );
        }
        else
        {
            echo sprintf(
                '<tr class="fail"><td><strong>Creating a file within ./ failed!</strong><br />'
                . 'Please check your permissions and try again.</td></tr>'
            );
            $proceed = false;
        }

        return $proceed;
    }


    private static function _checkMySQLVersion()
    {
        /* Check MySQL version. */
        $queryResult = mysql_query('SELECT VERSION()');
        if (!$queryResult)
        {
            echo sprintf(
                '<tr class="fail"><td>Cannot retrieve MySQL version number. <pre class="fail">%s</pre></td></tr>',
                mysql_error()
            );
            return false;
        }

        $row = mysql_fetch_row($queryResult);
        $versionParts = explode('-', $row[0]);
        $version = $versionParts[0];

        if (version_compare($version, '4.1.0', '>='))
        {
            echo sprintf(
                '<tr class="pass"><td>MySQL version is %s.</td></tr>',
                $version
            );
            return true;
        }

        echo sprintf(
            '<tr class="fail"><td>MySQL 4.1.0 or greater is required to run OpenCATS. Found version: %s.</td></tr>',
            $version
        );

        return false;
    }

    private static function _checkReadWrite($directory)
    {
        $directory .= '/';

        if (!is_dir($directory))
        {
            echo sprintf(
                '<tr class="fail"><td><strong>Directory %s does not exist or is not a directory.</strong></td></tr>',
                $directory
            );
            return false;
        }

        $octalPermissions = FileUtility::getOctalPermissions($directory);

        $proceed = true;

        /* Check for read. */
        if (is_readable($directory))
        {
            echo sprintf(
                '<tr class="pass"><td>Directory %s is readable (permissions: %s).</td></tr>',
                $directory,
                $octalPermissions
            );
        }
        else
        {
            echo sprintf(
                '<tr class="fail"><td><strong>Directory %s is not readable (permissions: %s).</strong></td></tr>',
                $directory,
                $octalPermissions
            );
            $proceed = false;
        }

        /* Check for write. */
        if (is_writeable($directory))
        {
            echo sprintf(
                '<tr class="pass"><td>Directory %s is writeable (permissions: %s).</td></tr>',
                $directory,
                $octalPermissions
            );
        }
        else
        {
            echo sprintf(
                '<tr class="fail"><td><strong>Directory %s is not writeable (permissions: %s).</strong></td></tr>',
                $directory,
                $octalPermissions
            );
            $proceed = false;
        }

        /* Test ACTUAL writeability by creating a file, not relying on is_writable()
         * as it sometimes returns a false positive.
         */
        if (FileUtility::isDirectoryWritable($directory))
        {
            echo sprintf(
                '<tr class="pass"><td>Creating a file within %s succeeded.</td></tr>',
                $directory
            );
        }
        else
        {
            echo sprintf(
                '<tr class="fail"><td><strong>Creating a file within %s failed. Check your permissions.</strong></td></tr>',
                $directory
            );
            $proceed = false;
        }

        /* Check for create directory ability. */
        $testPath = $directory . 'testdir';

        if (is_dir($testPath))
        {
            FileUtility::recursivelyRemoveDirectory($testPath);
        }

        if (@mkdir($testPath, 0777))
        {
            echo sprintf(
                '<tr class="pass"><td>Directories can be created inside %s/.</td></tr>',
                $directory
            );
        }
        else
        {
            echo sprintf(
                '<tr class="fail"><td><strong>Directories cannot be created inside %s/.</strong></td></tr>',
                $directory
            );
            $proceed = false;
        }

        if (is_dir($testPath))
        {
            FileUtility::recursivelyRemoveDirectory($testPath);
        }

        return $proceed;
    }
}

?>
