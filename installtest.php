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
        <link href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css" media="screen" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="vendor/components/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <img src="images/CATS-sig.gif" alt="Login" hspace="10" vspace="10" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">CATS Installation Test</div>
<?php
echo '<table class="table table-bordered">';

$proceed = true;
$warningsOccurred = false;

$proceed = $proceed && InstallationTests::runCoreTests();

$proceed = $proceed && InstallationTests::checkMySQL(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

$proceed = $proceed && InstallationTests::checkAttachmentsDir();
$proceed = $proceed && InstallationTests::checkAntiword();

echo '</table>';
?>
</div>
<?php

if (!$proceed)
{
    echo '<div class="alert alert-danger">One ore more tests failed. Please fix the problem and refresh this page.</div>';

    if ($warningsOccurred)
    {
        echo '<div class="alert alert-warning">One or more tests issued a warning. Once the fatal errors (red) are fixed, you may still proceed, but read the warnings carefully and address them if you can.</div>';
    }
}
else
{
    if ($warningsOccurred)
    {
        echo '<div class="alert alert-warning">One or more tests issued a warning. You may still proceed, but read the warnings carefully and address them if you can.</div>';
    }

    echo '<div class="alert alert-success">All tests passed successfully! Proceed to the next step.</div>';
}


?>
                </div>
            </div>
        </div>
    </body>
</html>
