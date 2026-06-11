<?php
    include_once(__DIR__ . '/bootstrap.php');

    include_once(LEGACY_ROOT . '/lib/EmailTemplates.php');
    include_once(LEGACY_ROOT . '/lib/DateUtility.php');

    $interface = new SecureAJAXInterface();

    if (!$interface->isRequiredIDValid('templateID', false))
    {
        $interface->outputXMLErrorPage(-1, 'Invalid template ID.');
        die();
    }

    $templateID = $_REQUEST['templateID'];

    /* Get an array of the company's location data. */
    $emailTemplates = new EmailTemplates();
    $emailTemplateText = $emailTemplates->get($templateID)['text'];

    if (empty($emailTemplateText))
    {
        $interface->outputXMLErrorPage(-2, 'No template data.');
        die();
    }

    /* Send back the XML data. */
    $interface->outputXMLPage(
        "<data>\n" .
        "    <errorcode>0</errorcode>\n" .
        "    <errormessage></errormessage>\n" .
        "    <text>" . htmlspecialchars($emailTemplateText) . "</text>\n" .
        "</data>\n"
    );

?>
