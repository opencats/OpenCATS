<?php
/*
   * OSATS
   *
   *
   *
*/

include_once('./lib/Pipelines.php');


$interface = new SecureAJAXInterface();

if (!$interface->isRequiredIDValid('candidateID', false))
{
    $interface->outputXMLErrorPage(-1, 'Invalid candidate ID.');
    die();
}

if (!$interface->isRequiredIDValid('jobOrderID', false))
{
    $interface->outputXMLErrorPage(-1, 'Invalid job order ID.');
    die();
}

$siteID = $interface->getSiteID();

$candidateID = $_REQUEST['candidateID'];
$jobOrderID  = $_REQUEST['jobOrderID'];

/* Get the candidate-joborder ID. */
$pipelines = new Pipelines($siteID);
$candidateJobOrderID = $pipelines->getCandidateJobOrderID($candidateID, $jobOrderID);

/* Send back the XML data. */
$interface->outputXMLPage(
    "<data>\n" .
    "    <errorcode>0</errorcode>\n" .
    "    <errormessage></errormessage>\n" .
    "    <id>" . $candidateJobOrderID . "</id>\n" .
    "</data>\n"
);
