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

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    $interface->outputXMLErrorPage(-1, 'Invalid request.');
    die();
}

if ($_SESSION['CATS']->getAccessLevel('pipelines.editRating') < ACCESS_LEVEL_EDIT)
{
    $interface->outputXMLErrorPage(-1, ERROR_NO_PERMISSION);
    die();
}

if (!$interface->isRequiredIDValid('candidateJobOrderID'))
{
    $interface->outputXMLErrorPage(-1, 'Invalid candidate-joborder ID.');
    die();
}

if (!$interface->isRequiredIDValid('rating', true, true) ||
    $_POST['rating'] < -6 || $_POST['rating'] > 5)
{
    $interface->outputXMLErrorPage(-1, 'Invalid rating.');
    die();
}

$candidateJobOrderID = $_POST['candidateJobOrderID'];
$rating              = $_POST['rating'];

$pipelines = new Pipelines();
$pipelines->updateRatingValue($candidateJobOrderID, $rating);

$newRating = $pipelines->getRatingValue($candidateJobOrderID);

$output =
    "<data>\n" .
    "    <errorcode>0</errorcode>\n" .
    "    <errormessage></errormessage>\n" .
    "    <newrating>" . $newRating . "</newrating>\n" .
    "</data>\n";

/* Send back the XML data. */
$interface->outputXMLPage($output);

?>
