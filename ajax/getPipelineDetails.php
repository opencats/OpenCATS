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

include_once(LEGACY_ROOT . '/lib/Pipelines.php');


$interface = new SecureAJAXInterface();

if (!$interface->isRequiredIDValid('candidateJobOrderID', false))
{
    $interface->outputXMLErrorPage(-1, 'Invalid candidate-joborder ID.');
    die();
}

$candidateJobOrderID = $_REQUEST['candidateJobOrderID'];

/* Get an array of the company's contacts data. */
$pipelines = new Pipelines();
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
             nl2br(htmlspecialchars($activity['notes'], ENT_QUOTES | ENT_SUBSTITUTE, HTML_ENCODING)),
             '<br /></td>';
        echo '</tr>';
    }
}

echo '</table>';

?>
