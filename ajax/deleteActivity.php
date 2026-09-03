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

include_once(LEGACY_ROOT . '/lib/ActivityEntries.php');


$interface = new SecureAJAXInterface();

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    $interface->outputXMLErrorPage(-1, 'Invalid request.');
    die();
}

if ($_SESSION['CATS']->getAccessLevel('contacts.deleteActivity') < ACCESS_LEVEL_EDIT)
{
    $interface->outputXMLErrorPage(-1, ERROR_NO_PERMISSION);
    die();
}

if (!$interface->isRequiredIDValid('activityID'))
{
    $interface->outputXMLErrorPage(-1, 'Invalid activity ID.');
    die();
}

$activityID = $_POST['activityID'];

/* Delete the activity entry. */
$activityEntries = new ActivityEntries();
$activityEntries->delete($activityID);

/* Send back the XML data. */
$interface->outputXMLSuccessPage();

?>
