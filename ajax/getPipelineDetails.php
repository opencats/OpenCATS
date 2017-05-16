<?php
/*
 * CATS
 * AJAX Pipeline Details Interface
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
 * $Id: getPipelineDetails.php 2976 2007-08-30 18:18:48Z andrew $
 */

include_once(LEGACY_ROOT . '/lib/Pipelines.php');


$interface = new SecureAJAXInterface();

if (!$interface->isRequiredIDValid('candidateJobOrderID', false))
{
    $interface->outputXMLErrorPage(-1, 'Invalid candidate-joborder ID.');
    die();
}

$siteID = $interface->getSiteID();

$candidateJobOrderID = $_REQUEST['candidateJobOrderID'];

/* Get an array of the company's contacts data. */
$pipelines = new Pipelines($siteID);
$pipelineActivitiesRS = $pipelines->getPipelineDetails($candidateJobOrderID);

foreach ($pipelineActivitiesRS as $rowIndex => $row)
{
    if (empty($pipelineActivitiesRS[$rowIndex]['notes']))
    {
        $pipelineActivitiesRS[$rowIndex]['notes'] = '(No Notes)';
    }
}

/* Output HTML. */
echo '<div class="noteUnsizedSpan">Activity History:</div>',
     '<table>';

if (empty($pipelineActivitiesRS))
{
    echo '<tr><td>No activity entries could be found.</td></tr>';
}
else
{
    foreach ($pipelineActivitiesRS as $activity)
    {

        echo '<tr>';
        echo '<td style="padding-right: 6px; width: 160px;">',
             $activity['dateModified'],
             '</td>';
        echo '<td style="padding-right: 6px; width: 125px">(',
             $activity['enteredByFirstName'],
             ' ',
             $activity['enteredByLastName'],
             ')</td>';
        echo '<td style="padding-right: 6px; width: 625px;">',
             $activity['notes'],
             '<br /></td>';
        echo '</tr>';
    }
}

echo '</table>';

?>
