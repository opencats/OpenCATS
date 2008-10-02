<?php
/*
 * CATS
 * AJAX Column Resizing Interface
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
 * $Id: setColumnWidth.php 2373 2007-04-24 21:57:28Z will $
 */

$interface = new SecureAJAXInterface();

$instance = $_REQUEST['instance'];
$columnName = $_REQUEST['columnName'];
$columnWidth = $_REQUEST['columnWidth'];

$columnPreferences = $_SESSION['CATS']->getColumnPreferences($instance);

foreach ($columnPreferences as $index => $data)
{
    if ($data['name'] == $columnName)
    {
        $columnPreferences[$index]['width'] = $columnWidth;
    }
}

$_SESSION['CATS']->setColumnPreferences($instance, $columnPreferences);

$output =
    "<data>\n" .
    "    <errorcode>0</errorcode>\n" .
    "    <errormessage></errormessage>\n" .
    "</data>\n";

/* Send back the XML data. */
$interface->outputXMLPage($output);

?>
