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

$interface = new SecureAJAXInterface();

include (LEGACY_ROOT . '/lib/Candidates.php');

if (!isset($_REQUEST['email']))
{
    die ('Invalid E-Mail address.');
}

$email = $_REQUEST['email'];

$candidates = new Candidates();

$output = "<data>\n";

$candidateID = $candidates->getIDByEmail($email);

if ($candidateID == -1)
{
    $output .=
        "    <candidate>\n" .
        "        <id>-1</id>\n" .
        "    </candidate>\n";
}
else
{
    $candidateRS = $candidates->get($candidateID);
    
    $output .=
        "    <candidate>\n" .
        "        <id>"         . $candidateID . "</id>\n" .
        "        <name>"         . $candidateRS['candidateFullName'] . "</name>\n" .
        "    </candidate>\n";
}

$output .=
    "</data>\n";

/* Send back the XML data. */
$interface->outputXMLPage($output);

?>
