<?php
    include_once('./lib/EmailTemplates.php');
    include_once('./lib/DateUtility.php');
    include_once('./lib/Candidates.php');
    
    $interface = new SecureAJAXInterface();

    if (!$interface->isRequiredIDValid('candidateID', false))
    {
        $interface->outputXMLErrorPage(-1, 'Invalid candidate ID.');
        die();
    }

    $siteID = $interface->getSiteID();

    $candidateID = $_REQUEST['candidateID'];
    $templateText = $_REQUEST['templateText'];

    /* Get an array of the company's location data. */
    $candidates = new Candidates($siteID);
    $candidateData = $candidates->get($candidateID);
    $emailTemplates = new EmailTemplates($siteID);
    $emailTemplateText = $emailTemplates->replaceVariables($templateText);

    if (empty($candidateData))
    {
        $interface->outputXMLErrorPage(-2, 'No candidate data.');
        die();
    }

    $stringsToFind = array(
            '%CANDOWNER%',
            '%CANDFIRSTNAME%',
            '%CANDFULLNAME%'
        );
    $replacementStrings = array(
            $candidateData['ownerFullName'],
            $candidateData['firstName'],
            $candidateData['candidateFullName'],
        );
    $emailTemplateText = str_replace(
            $stringsToFind,
            $replacementStrings,
            $emailTemplateText
        );
    
    /* Send back the XML data. */
    $interface->outputXMLPage(
        "<data>\n" .
        "    <errorcode>0</errorcode>\n" .
        "    <errormessage></errormessage>\n" .
        "    <text>" . htmlspecialchars($emailTemplateText) . "</text>\n" .
        "</data>\n"
    );

?>