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

include_once(__DIR__ . '/bootstrap.php');

$interface = new SecureAJAXInterface();

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    $interface->outputXMLErrorPage(-1, 'Invalid request.');
    die();
}

if (!isset($_POST['instance']) || !isset($_POST['columnName']) || !isset($_POST['columnWidth']))
{
    $interface->outputXMLErrorPage(-1, 'Invalid request.');
    die();
}

$instance = $_POST['instance'];
$columnName = $_POST['columnName'];
$columnWidth = $_POST['columnWidth'];

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
