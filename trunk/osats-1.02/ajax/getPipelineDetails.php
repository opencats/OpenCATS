<?php
/*
   * OSATS
   *
   *
   * Open Source GNU License will apply
*/

include_once('./lib/Pipelines.php');


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
