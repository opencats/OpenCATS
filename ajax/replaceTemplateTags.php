<?php
    include_once(__DIR__ . '/bootstrap.php');

    include_once(LEGACY_ROOT . '/lib/EmailTemplates.php');
    include_once(LEGACY_ROOT . '/lib/DateUtility.php');
    include_once(LEGACY_ROOT . '/lib/CandidateAuthorization.php');
    
    $interface = new SecureAJAXInterface();

    if (is_array($_REQUEST['candidateID'] ?? null) || !$interface->isRequiredIDValid('candidateID', false))
    {
        $interface->outputXMLErrorPage(-1, 'Invalid candidate ID.');
        die();
    }

    if ($_SESSION['CATS']->getAccessLevel('candidates.show') < ACCESS_LEVEL_READ ||
        !CandidateAuthorization::canAccessCandidate($_REQUEST['candidateID'], $candidateData))
    {
        $interface->outputXMLErrorPage(-2, 'No candidate data.');
        die();
    }

    $emailTemplates = new EmailTemplates();
    $emailTemplateText = $emailTemplates->replaceVariables($_REQUEST['templateText']);

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
