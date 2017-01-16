<?php
/*
 * CATS
 * AJAX Pipeline Rating Interface
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
 * $Id: setCandidateJobOrderRating.php 1479 2007-01-17 00:22:21Z will $
 */

include_once('./lib/Pipelines.php');


$interface = new SecureAJAXInterface();

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
    $_REQUEST['rating'] < -6 || $_REQUEST['rating'] > 5)
{
    $interface->outputXMLErrorPage(-1, 'Invalid rating.');
    die();
}

$siteID = $interface->getSiteID();

$candidateJobOrderID = $_REQUEST['candidateJobOrderID'];
$rating              = $_REQUEST['rating'];

$pipelines = new Pipelines($siteID);
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
