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

if ($_SESSION['CATS']->getAccessLevel('contacts.editActivity') < ACCESS_LEVEL_EDIT)
{
    $interface->outputXMLErrorPage(-1, ERROR_NO_PERMISSION);
    die();
}

if (!$interface->isRequiredIDValid('activityID'))
{
    $interface->outputXMLErrorPage(-1, 'Invalid activity ID.');
    die();
}

if (!$interface->isRequiredIDValid('type'))
{
    $interface->outputXMLErrorPage(-1, 'Invalid activity entry type.');
    die();
}

if (!$interface->isOptionalIDValid('jobOrderID'))
{
    $interface->outputXMLErrorPage(-1, 'Invalid job order ID.');
    die();
}

if (!isset($_POST['notes']))
{
    $interface->outputXMLErrorPage(-1, 'Invalid notes.');
    die();
}

$activityID = $_POST['activityID'];
$type       = $_POST['type'];
$jobOrderID = isset($_POST['jobOrderID']) ? trim($_POST['jobOrderID']) : null;

/* Decode and trim the activity notes from the company. */
$activityNote = trim(urldecode($_POST['notes']));
$activityDate = trim(urldecode($_POST['date']));
$activityHour = trim(urldecode($_POST['hour']));
$activityMinute = trim(urldecode($_POST['minute']));

$dateFormatFlag = $_SESSION['CATS']->isDateDMY()
    ? DATE_FORMAT_DDMMYY
    : DATE_FORMAT_MMDDYY;

if (!DateUtility::validate('-', $activityDate, $dateFormatFlag))
{
    die('Invalid availability date.');
    return;
}

if ($jobOrderID === null || $jobOrderID === '' || $jobOrderID === 'NULL' ||
    $jobOrderID === '0' || $jobOrderID === '-1' || !is_numeric($jobOrderID) ||
    (int) $jobOrderID <= 0)
{
    $jobOrderID = -1;
}

/* Convert time fields to a 'HH:MM:SS' string. */
$is24 = $_SESSION['CATS']->isTimeFormat24();
$activityAMPM = $is24 ? '' : trim(urldecode($_POST['ampm']));
$timeStr = DateUtility::normalizeActivityTime($activityHour, $activityMinute, $activityAMPM, $is24);
if ($timeStr === false)
{
    die('Invalid time.');
}

/* Create MySQL date string w/ 24hr time (YYYY-MM-DD HH:MM:SS). */
$date = DateUtility::convert('-', $activityDate, $dateFormatFlag, DATE_FORMAT_YYYYMMDD) . ' ' . $timeStr;

/* Save the new activity entry. */
$activityEntries = new ActivityEntries();
$activityEntries->update($activityID, $type, $activityNote, $jobOrderID, $date, $_SESSION['CATS']->getTimeZoneOffset());

/* Grab the current activity entry. */
$activityEntry = $activityEntries->get($activityID);

/* Send back "(No Notes)" to be displayed if we don't have any. */
if (empty($activityEntry['notes']))
{
    $activityEntry['notes'] = '(No Notes)';
}

/* Send back the XML data. */
$interface->outputXMLPage(
    "<data>\n" .
    "    <errorcode>0</errorcode>\n" .
    "    <errormessage></errormessage>\n" .
    "    <type>"            . $activityEntry['type'] . "</type>\n" .
    "    <typedescription>" . $activityEntry['typeDescription'] . "</typedescription>\n" .
    "    <notes>"           . htmlspecialchars($activityEntry['notes']) . "</notes>\n" .
    "    <regarding>"       . htmlspecialchars($activityEntry['regarding']) . "</regarding>\n" .
    "    <date>"            . htmlspecialchars($activityEntry['dateCreated']) . "</date>\n" .
    "</data>\n"
);

?>
