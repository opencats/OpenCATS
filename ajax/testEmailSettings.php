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

include_once(LEGACY_ROOT . '/lib/Mailer.php');


$interface = new SecureAJAXInterface();

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    $interface->outputXMLErrorPage(-1, 'Invalid request.');
    die();
}

if ($_SESSION['CATS']->getAccessLevel('settings.emailSettings.POST') < ACCESS_LEVEL_SA)
{
    $interface->outputXMLErrorPage(-1, ERROR_NO_PERMISSION);
    die();
}

if (!isset($_POST['testEmailAddress']) ||
    empty($_POST['testEmailAddress']))
{
    $interface->outputXMLErrorPage(
        -1, 'Invalid test e-mail address.'
    );

    die();
}

if (!isset($_POST['fromAddress']) ||
    empty($_POST['fromAddress']))
{
    $interface->outputXMLErrorPage(
        -1, 'Invalid from e-mail address.'
    );

    die();
}

$testEmailAddress = $_POST['testEmailAddress'];
$fromAddress      = $_POST['fromAddress'];

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

$mailerSettings = new MailerSettings();
$mailerSettingsRS = $mailerSettings->getAll();
$mailer = new Mailer();

$mailer->overrideSetting('fromAddress', $fromAddress);

$mailerStatus = $mailer->sendToOne(
    array($testEmailAddress, ''),
    'CATS Test E-Mail',
    'This is a CATS test e-mail in HTML format.',
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

?>
