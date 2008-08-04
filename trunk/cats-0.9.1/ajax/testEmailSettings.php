<?php
/*
 * CATS
 * AJAX E-Mail Settings Testing Interface
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
 * $Id: testEmailSettings.php 2101 2007-03-06 00:20:17Z brian $
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
