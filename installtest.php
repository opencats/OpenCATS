<?php
/*
 * CATS
 * Installation Test Script
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
 * $Id: installtest.php 2107 2007-03-07 06:20:57Z brian $
 */

// FIXME: Test config readable.

include_once('./config.php');
include_once('./constants.php');
include_once('./lib/InstallationTests.php');


define('REQUIRED_SCHEMA_VERSION', '1200');

header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title>CATS - Installation Test Script</title>
        <style type="text/css" media="all">@import "main.css";</style>
        <style type="text/css" media="all">
            table.test_output
            {
                border: 1px solid #000;
                border-collapse: collapse;
                border-spacing: 0px;
                width: 925px;
            }

            table.test_output th
            {
                border: 1px solid #000;
                border-collapse: collapse;
                border-spacing: 0px;
                padding: 2px 4px 2px 4px;
                color: #000;
                background: #ccc;
                font: normal normal bold 9pt Arial, Tahoma, sans-serif;
            }

            table.test_output td
            {
                border-right: 1px solid #000;
                border-bottom: 1px solid #000;
                border-collapse: collapse;
                border-spacing: 0px;
                padding: 2px 5px 2px 5px;
                font: normal normal normal 9pt Arial, Tahoma, sans-serif;
                vertical-align: top;
            }

            table.test_output tr.pass
            {
                border-spacing: 0px;
                font-weight: bold;
                background: #419933;
            }

            table.test_output tr.fail
            {
                border-spacing: 0px;
                font-weight: bold;
                background: #ec3737;
            }

            table.test_output tr.warning
            {
                border-spacing: 0px;
                font-weight: bold;
                background: orange;
            }

            p#footer_pass
            {
                padding: 6px;
                margin-top: 1em;
                background: #419933;
                font-weight: bold;
                border: 1px solid #000;
                color: #000;
                width: 910px;
            }

            p#footer_fail
            {
                padding: 6px;
                margin-top: 1em;
                background: #ec3737;
                font-weight: bold;
                border: 1px solid #000;
                color: #000;
                width: 910px;
            }

            p#footer_warning
            {
                padding: 6px;
                margin-top: 1em;
                background: orange;
                font-weight: bold;
                border: 1px solid #000;
                color: #000;
                width: 910px;
            }
        </style>
    </head>

    <body>
        <div id="headerBlock">
            <table cellspacing="0" cellpadding="0" style="margin: 0px; padding: 0px; float: left;">
                <tr>
                    <td rowspan="2"><div id="mainLogo"></div></td>
                    <td><span id="mainLogoText">OpenCATS</span></td>
                </tr>
                <tr>
                    <td><span id="subLogoText">Applicant Tracking System</span></td>
                </tr>
            </table>
        </div>
        <br />
        <p class="note">CATS Installation Test</p>
<?php
echo '<table class="test_output">';

$proceed = true;
$warningsOccurred = false;

$proceed = $proceed && InstallationTests::runCoreTests();

$proceed = $proceed && InstallationTests::checkMySQL(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

$proceed = $proceed && InstallationTests::checkAttachmentsDir();
$proceed = $proceed && InstallationTests::checkAntiword();

echo '</table>';

if (!$proceed)
{
    echo '<p id="footer_fail">One ore more tests failed. Please fix the problem and refresh this page.</p>';

    if ($warningsOccurred)
    {
        echo '<p id="footer_warning">One or more tests issued a warning. Once the fatal errors (red) are fixed, you may still proceed, but read the warnings carefully and address them if you can.</p>';
    }
}
else
{
    if ($warningsOccurred)
    {
        echo '<p id="footer_warning">One or more tests issued a warning. You may still proceed, but read the warnings carefully and address them if you can.</p>';
    }

    echo '<p id="footer_pass">All tests passed successfully! Proceed to the next step.</p>';
}

?>
    </body>
</html>
