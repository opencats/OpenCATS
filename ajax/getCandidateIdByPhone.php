<?php
/*
 * CATS
 * AJAX Data Item Job Orders Interface
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
* $Id: getCandidateIdByPhone.php 3078 2007-09-21 20:25:28Z will $
*/

$interface = new SecureAJAXInterface();

include ('lib/Candidates.php');

    if (!isset($_REQUEST['phone']))
    {
        die ('Invalid E-Mail address.');
    }
    
    $siteID = $interface->getSiteID();
    
    $phone = $_REQUEST['phone'];
    
    $candidates = new Candidates($siteID);
    
    $output = "<data>\n";
    
    $candidateID = $candidates->getIDByPhone($phone);
    
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


