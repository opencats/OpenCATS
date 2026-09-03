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

include_once(LEGACY_ROOT . '/lib/StringUtility.php');
include_once(LEGACY_ROOT . '/lib/AddressParser.php');
include_once(LEGACY_ROOT . '/lib/ResultSetUtility.php');


$interface = new AJAXInterface();

if (!isset($_REQUEST['mode']))
{
    $interface->outputXMLErrorPage(-1, 'Invalid parsing mode.');
    die();
}

if (!isset($_REQUEST['addressBlock']))
{
    $interface->outputXMLErrorPage(-1, 'Invalid address block.');
    die();
}

/* Figure out what phone number type we are supposed to recognize a solitary
 * phone number as.
 */
switch (urldecode($_REQUEST['mode']))
{
    case 'contact':
        $mode = ADDRESSPARSER_MODE_CONTACT;
        break;

    case 'company':
        $mode = ADDRESSPARSER_MODE_COMPANY;
        break;

    default:
    case 'person':
        $mode = ADDRESSPARSER_MODE_PERSON;
        break;
}

/* Instantiate a new AddressParser */
$addressParser = new AddressParser();

/* Feed the AddressParser a the address block from POST data and parse
 * the address.
 */
$addressBlock = urldecode($_REQUEST['addressBlock']);
$addressParser->parse($addressBlock, $mode);

/* Get the parsed address as an associative array. */
$parsedAddressArray = $addressParser->getAddressArray();
$phoneNumbers = $parsedAddressArray['phoneNumbers'];

/* Fetch individual phone numbers. */
$homePhoneRow = ResultSetUtility::findRowByColumnValue(
    $phoneNumbers, 'type', 'home'
);
$workPhoneRow = ResultSetUtility::findRowByColumnValue(
    $phoneNumbers, 'type', 'work'
);
$cellPhoneRow = ResultSetUtility::findRowByColumnValue(
    $phoneNumbers, 'type', 'cell'
);
$faxRow = ResultSetUtility::findRowByColumnValue(
    $phoneNumbers, 'type', 'fax'
);

if ($homePhoneRow !== false)
{
    $homePhone = $phoneNumbers[$homePhoneRow]['number'];
}
else
{
    $homePhone = '';
}

if ($cellPhoneRow !== false)
{
    $cellPhone = $phoneNumbers[$cellPhoneRow]['number'];
}
else
{
    $cellPhone = '';
}

if ($workPhoneRow !== false)
{
    $workPhone = $phoneNumbers[$workPhoneRow]['number'];
}
else
{
    $workPhone = '';
}

if ($faxRow !== false)
{
    $fax = $phoneNumbers[$faxRow]['number'];
}
else
{
    $fax = '';
}

/* Send back the XML data. */
$interface->outputXMLPage(
    "<data>\n" .
    "    <errorcode>0</errorcode>\n" .
    "    <errormessage></errormessage>\n" .
    "    <company>". $parsedAddressArray['company'] ."</company>\n" .
    "    <name>\n" .
    "        <first>"  . $parsedAddressArray['firstName']  . "</first>\n" .
    "        <middle>" . $parsedAddressArray['middleName'] . "</middle>\n" .
    "        <last>"   . $parsedAddressArray['lastName']   . "</last>\n" .
    "    </name>\n" .
    "    <address>\n" .
    "        <line>" . $parsedAddressArray['addressLineOne'] . "</line>\n" .
    "        <line>" . $parsedAddressArray['addressLineTwo'] . "</line>\n" .
    "    </address>\n" .
    "    <city>"   . $parsedAddressArray['city']  . "</city>\n" .
    "    <state>"  . $parsedAddressArray['state'] . "</state>\n" .
    "    <zip>"    . $parsedAddressArray['zip']   . "</zip>\n" .
    "    <email>"  . $parsedAddressArray['email'] . "</email>\n" .
    "    <phonenumbers>\n" .
    "        <home>" . $homePhone . "</home>\n" .
    "        <cell>" . $cellPhone . "</cell>\n" .
    "        <work>" . $workPhone . "</work>\n" .
    "        <fax>"  . $fax       . "</fax>\n" .
    "    </phonenumbers>\n" .
    "</data>\n"
);

?>
