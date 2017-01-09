<?php
    include_once('./lib/EmailTemplates.php');
    include_once('./lib/DateUtility.php');

    $interface = new SecureAJAXInterface();

    if (!$interface->isRequiredIDValid('templateID', false))
    {
        $interface->outputXMLErrorPage(-1, 'Invalid template ID.');
        die();
    }

    $siteID = $interface->getSiteID();

    $templateID = $_REQUEST['templateID'];

    /* Get an array of the company's location data. */
    $emailTemplates = new EmailTemplates($siteID);
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