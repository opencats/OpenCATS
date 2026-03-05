<?php
/*
 * CATS
 * AJAX Activity Entry Editing Interface
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
 * $Id: editActivity.php 2883 2007-08-14 15:25:26Z brian $
 */


include_once(LEGACY_ROOT . '/lib/StringUtility.php');
include_once(LEGACY_ROOT . '/lib/ActivityEntries.php');
include_once(LEGACY_ROOT . '/lib/Pipelines.php');


$interface = new SecureAJAXInterface();

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    $interface->outputXMLErrorPage(-1, 'Invalid request.');
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

$siteID = $interface->getSiteID();

$activityID = $_POST['activityID'];
$type       = $_POST['type'];
$jobOrderID = $_POST['jobOrderID'];

/* Decode and trim the activity notes from the company. */
$activityNote = trim(urldecode($_POST['notes']));
$activityDate = trim(urldecode($_POST['date']));
$activityHour = trim(urldecode($_POST['hour']));
$activityMinute = trim(urldecode($_POST['minute']));
$activityAMPM = trim(urldecode($_POST['ampm']));

$dateFormatFlag = $_SESSION['CATS']->isDateDMY()
    ? DATE_FORMAT_DDMMYY
    : DATE_FORMAT_MMDDYY;

if (!DateUtility::validate('-', $activityDate, $dateFormatFlag))
{
    die('Invalid availability date.');
    return;
}

/* Convert formatted time to UNIX timestamp. */
$time = strtotime(
    sprintf('%02d:%02d %s', $activityHour, $activityMinute, $activityAMPM)
);

/* Create MySQL date string w/ 24hr time (YYYY-MM-DD HH:MM:SS). */
$date = sprintf(
    '%s %s',
    DateUtility::convert(
        '-', $activityDate, $dateFormatFlag, DATE_FORMAT_YYYYMMDD
    ),
    date('H:i:00', $time)
);

/* Highlight what needs highlighting. */
if (strpos($activityNote, 'Status change: ') === 0)
{
    $pipelines = new Pipelines($siteID);

    $statusRS = $pipelines->getStatusesForPicking();
    foreach ($statusRS as $data)
    {
        $activityNote = StringUtility::replaceOnce(
            $data['status'],
            '<span style="color: #ff6c00;">' . $data['status'] . '</span>',
            $activityNote
        );
    }
}

/* Save the new activity entry. */
$activityEntries = new ActivityEntries($siteID);
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
