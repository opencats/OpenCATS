<?php
/*
   * OSATS
   *
   *
   * Open Source GNU License will apply
*/

include_once('./lib/ActivityEntries.php');


$interface = new SecureAJAXInterface();

if (!$interface->isRequiredIDValid('activityID'))
{
    $interface->outputXMLErrorPage(-1, 'Invalid activity ID.');
    die();
}

$siteID = $interface->getSiteID();

$activityID = $_REQUEST['activityID'];

/* Delete the activity entry. */
$activityEntries = new ActivityEntries($siteID);
$activityEntries->delete($activityID);

/* Send back the XML data. */
$interface->outputXMLSuccessPage();
