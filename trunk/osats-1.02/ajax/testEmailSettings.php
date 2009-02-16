<?php
/*
   * OSATS
   *
   *
   * Open Source GNU License will apply
*/

include_once('./lib/Mailer.php');


$interface = new SecureAJAXInterface();

$siteID = $interface->getSiteID();

if (!isset($_REQUEST['testEmailAddress']) ||
    empty($_REQUEST['testEmailAddress']))
{
    $interface->outputXMLErrorPage(
        -1, 'Invalid test e-mail address.'
    );

    die();
}

if (!isset($_REQUEST['fromAddress']) ||
    empty($_REQUEST['fromAddress']))
{
    $interface->outputXMLErrorPage(
        -1, 'Invalid from e-mail address.'
    );

    die();
}

$testEmailAddress = $_REQUEST['testEmailAddress'];
$fromAddress      = $_REQUEST['fromAddress'];

/* Is the test e-mail address specified valid? */
// FIXME: Validate properly.
if (strpos($testEmailAddress, '@') === false)
{
    $interface->outputXMLErrorPage(
        -2, 'Invalid test e-mail address.'
    );

    die();
}

/* Is the from e-mail address specified valid? */
// FIXME: Validate properly.
if (strpos($fromAddress, '@') === false)
{
    $interface->outputXMLErrorPage(
        -2, 'Invalid from e-mail address.'
    );

    die();
}

$mailerSettings = new MailerSettings($siteID);
$mailerSettingsRS = $mailerSettings->getAll();
$mailer = new Mailer($siteID);

$mailer->overrideSetting('fromAddress', $fromAddress);

$mailerStatus = $mailer->sendToOne(
    array($testEmailAddress, ''),
    'OSATS Test E-Mail',
    'This is a test e-mail in HTML format.',
    true
);

if (!$mailerStatus)
{
    $interface->outputXMLErrorPage(
        -2, $mailer->getError()
    );
    die();
}

$errorMessage = $mailer->getError();
if (!empty($errorMessage))
{
    $interface->outputXMLErrorPage(
        -2, $errorMessage
    );

    die();
}

/* Send back the XML data. */
$interface->outputXMLSuccessPage();
