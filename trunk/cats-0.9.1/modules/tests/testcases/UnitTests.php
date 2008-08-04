<?php
/*
 * CATS
 * Tests Module - Unit Test Cases
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * All rights reserved.
 *
 * DO NOT RUN TABS-TO-SPACES ON THIS FILE!!!
 *
 * $Id: UnitTests.php 3565 2007-11-12 09:09:22Z will $
 */

include_once('./lib/StringUtility.php');
include_once('./lib/DateUtility.php');   /* Depends on StringUtility. */
include_once('./lib/AddressParser.php'); /* Depends on StringUtility. */
include_once('./lib/ArrayUtility.php');
include_once('./lib/ResultSetUtility.php');
include_once('./lib/DatabaseConnection.php');
include_once('./lib/VCard.php');
include_once('./lib/Attachments.php');
include_once('./lib/AJAXInterface.php');
include_once('./lib/BrowserDetection.php');
include_once('./lib/FileUtility.php');
include_once('./lib/HashUtility.php');



/* Tests for ActivityEntries class. */
class ActivityEntriesTest extends CATSUnitTestCase
{
}

/* Tests for AddressParser class. */
class AddressParserTest extends CATSUnitTestCase
{
    private $addressParser;


    function makePhoneNumberArray($phoneNumbers)
    {
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
        
        return array(
            'homePhone' => $homePhone,
            'workPhone' => $workPhone,
            'cellPhone' => $cellPhone,
            'fax'  => $fax
        );
    }
    
    
    function setUp()
    {
        $this->addressParser = new AddressParser();
    }
    

    function testSampleAddress1()
    {
        $address = <<<EOF

Raed-Anne Stanford
110 North Elk Road
Sterling, VA  20164
US
raed@btjjdtjt.com

Mobile: (743) 959-6344
Home: (743) 450-3855
Fax: (743) 450-3333
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_PERSON);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertIdentical($parsedAddressArray['firstName'], 'Raed-Anne');
        $this->assertIdentical($parsedAddressArray['middleName'], '');
        $this->assertIdentical($parsedAddressArray['lastName'], 'Stanford');
        $this->assertIdentical($parsedAddressArray['addressLineOne'], '110 North Elk Road');
        $this->assertIdentical($parsedAddressArray['addressLineTwo'], '');
        $this->assertIdentical($parsedAddressArray['city'], 'Sterling');
        $this->assertIdentical($parsedAddressArray['state'], 'VA');
        $this->assertIdentical($parsedAddressArray['zip'], '20164');
        $this->assertIdentical($parsedAddressArray['email'], 'raed@btjjdtjt.com');
        
        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
        );
        $this->assertIdentical($phoneNumbers['cellPhone'], '743-959-6344');
        $this->assertIdentical($phoneNumbers['homePhone'], '743-450-3855');
        $this->assertIdentical($phoneNumbers['workPhone'], '');
        $this->assertIdentical($phoneNumbers['fax'], '743-450-3333');
    }

    function testSampleAddress2()
    {
        $address = <<<EOF
Lee Ann Chambers
9128 Standard Blvd
El Paso, TN  75593
US
lachambers@yahoooo.com

Primary Phone: (962) 398-0687
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_PERSON);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertIdentical($parsedAddressArray['firstName'], 'Lee');
        $this->assertIdentical($parsedAddressArray['middleName'], 'Ann');
        $this->assertIdentical($parsedAddressArray['lastName'], 'Chambers');
        $this->assertIdentical($parsedAddressArray['addressLineOne'], '9128 Standard Blvd');
        $this->assertIdentical($parsedAddressArray['addressLineTwo'], '');
        $this->assertIdentical($parsedAddressArray['city'], 'El Paso');
        $this->assertIdentical($parsedAddressArray['state'], 'TN');
        $this->assertIdentical($parsedAddressArray['zip'], '75593');
        $this->assertIdentical($parsedAddressArray['email'], 'lachambers@yahoooo.com');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
        );
        $this->assertIdentical($phoneNumbers['cellPhone'], '');
        $this->assertIdentical($phoneNumbers['homePhone'], '962-398-0687');
        $this->assertIdentical($phoneNumbers['workPhone'], '');
        $this->assertIdentical($phoneNumbers['fax'], '');
    }

    function testSampleAddress3()
    {
        $address = <<<EOF
Michael Nicholas O'Mercurio
57830 Decoration Park
Apt. 7
My Really Long City  , MI  48048
US
mikeomercurio@yahooooooooooo.com

Mobile: 5862992513
Work: 4443002929
Home:
Contact Preference: E-Mail
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_PERSON);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertIdentical($parsedAddressArray['firstName'], 'Michael');
        $this->assertIdentical($parsedAddressArray['middleName'], 'Nicholas');
        $this->assertIdentical($parsedAddressArray['lastName'], 'O\'Mercurio');
        $this->assertIdentical($parsedAddressArray['addressLineOne'], '57830 Decoration Park');
        $this->assertIdentical($parsedAddressArray['addressLineTwo'], 'Apt. 7');
        $this->assertIdentical($parsedAddressArray['city'], 'My Really Long City');
        $this->assertIdentical($parsedAddressArray['state'], 'MI');
        $this->assertIdentical($parsedAddressArray['zip'], '48048');
        $this->assertIdentical($parsedAddressArray['email'], 'mikeomercurio@yahooooooooooo.com');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
        );
        $this->assertIdentical($phoneNumbers['cellPhone'], '586-299-2513');
        $this->assertIdentical($phoneNumbers['homePhone'], '');
        $this->assertIdentical($phoneNumbers['workPhone'], '444-300-2929');
        $this->assertIdentical($phoneNumbers['fax'], '');
    }

    function testSampleAddress4()
    {
        $address = <<<EOF
Enock Chamberlin
281 Kerby Road Apt # C
Arlington, Texas  79999-5801
US
enock21@hooooootmail.com


Mobile: 817-715-6875
Home: 817-303-3864
Work: 8173933899
Contact Preference: Telephone
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_PERSON);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertIdentical($parsedAddressArray['firstName'], 'Enock');
        $this->assertIdentical($parsedAddressArray['middleName'], '');
        $this->assertIdentical($parsedAddressArray['lastName'], 'Chamberlin');
        $this->assertIdentical($parsedAddressArray['addressLineOne'], '281 Kerby Road Apt # C');
        $this->assertIdentical($parsedAddressArray['addressLineTwo'], '');
        $this->assertIdentical($parsedAddressArray['city'], 'Arlington');
        $this->assertIdentical($parsedAddressArray['state'], 'Texas');
        $this->assertIdentical($parsedAddressArray['zip'], '79999-5801');
        $this->assertIdentical($parsedAddressArray['email'], 'enock21@hooooootmail.com');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
        );
        $this->assertIdentical($phoneNumbers['cellPhone'], '817-715-6875');
        $this->assertIdentical($phoneNumbers['homePhone'], '817-303-3864');
        $this->assertIdentical($phoneNumbers['workPhone'], '817-393-3899');
        $this->assertIdentical($phoneNumbers['fax'], '');
    }

    function testSampleAddress5()
    {

        $address = <<<EOF
Horacio Alfonzo
PO BOX 422428
Miami Beach, FL  33541-1234
US
halfonzo@belleast.net

Home: 305 777 1222
Contact Preference: E-Mail
URL: http://idonthaveawebsite-ordoi.com/
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_PERSON);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertIdentical($parsedAddressArray['firstName'], 'Horacio');
        $this->assertIdentical($parsedAddressArray['middleName'], '');
        $this->assertIdentical($parsedAddressArray['lastName'], 'Alfonzo');
        $this->assertIdentical($parsedAddressArray['addressLineOne'], 'PO BOX 422428');
        $this->assertIdentical($parsedAddressArray['addressLineTwo'], '');
        $this->assertIdentical($parsedAddressArray['city'], 'Miami Beach');
        $this->assertIdentical($parsedAddressArray['state'], 'FL');
        $this->assertIdentical($parsedAddressArray['zip'], '33541-1234');
        $this->assertIdentical($parsedAddressArray['email'], 'halfonzo@belleast.net');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
        );
        $this->assertIdentical($phoneNumbers['cellPhone'], '');
        $this->assertIdentical($phoneNumbers['homePhone'], '305-777-1222');
        $this->assertIdentical($phoneNumbers['workPhone'], '');
        $this->assertIdentical($phoneNumbers['fax'], '');
    }

    function testSampleAddress6()
    {

        $address = <<<EOF
EHhn nunbiv $%&%*$* !!!!!!! I am < wcc@nospammonkeys.org > NOT a valid address! RH%
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_PERSON);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertIdentical($parsedAddressArray['firstName'], '');
        $this->assertIdentical($parsedAddressArray['middleName'], '');
        $this->assertIdentical($parsedAddressArray['lastName'], '');
        $this->assertIdentical($parsedAddressArray['addressLineOne'], '');
        $this->assertIdentical($parsedAddressArray['addressLineTwo'], '');
        $this->assertIdentical($parsedAddressArray['city'], '');
        $this->assertIdentical($parsedAddressArray['state'], '');
        $this->assertIdentical($parsedAddressArray['zip'], '');
        $this->assertIdentical($parsedAddressArray['email'], 'wcc@nospammonkeys.org');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
        );
        $this->assertIdentical($phoneNumbers['cellPhone'], '');
        $this->assertIdentical($phoneNumbers['homePhone'], '');
        $this->assertIdentical($phoneNumbers['workPhone'], '');
        $this->assertIdentical($phoneNumbers['fax'], '');
    }

    function testSampleAddress7()
    {

        $address = <<<EOF
EHhn nunbiv $%&%*$* !!!!!!! I am NOT a valid address! RH%
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_PERSON);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertIdentical($parsedAddressArray['firstName'], '');
        $this->assertIdentical($parsedAddressArray['middleName'], '');
        $this->assertIdentical($parsedAddressArray['lastName'], '');
        $this->assertIdentical($parsedAddressArray['addressLineOne'], '');
        $this->assertIdentical($parsedAddressArray['addressLineTwo'], '');
        $this->assertIdentical($parsedAddressArray['city'], '');
        $this->assertIdentical($parsedAddressArray['state'], '');
        $this->assertIdentical($parsedAddressArray['zip'], '');
        $this->assertIdentical($parsedAddressArray['email'], '');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
        );
        $this->assertIdentical($phoneNumbers['cellPhone'], '');
        $this->assertIdentical($phoneNumbers['homePhone'], '');
        $this->assertIdentical($phoneNumbers['workPhone'], '');
        $this->assertIdentical($phoneNumbers['fax'], '');
    }

    function testSampleAddress8()
    {

        $address = <<<EOF
Will G. Buckner
wcc@nospammonkeys.org
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_PERSON);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertIdentical($parsedAddressArray['firstName'], 'Will');
        $this->assertIdentical($parsedAddressArray['middleName'], 'G.');
        $this->assertIdentical($parsedAddressArray['lastName'], 'Buckner');
        $this->assertIdentical($parsedAddressArray['addressLineOne'], '');
        $this->assertIdentical($parsedAddressArray['addressLineTwo'], '');
        $this->assertIdentical($parsedAddressArray['city'], '');
        $this->assertIdentical($parsedAddressArray['state'], '');
        $this->assertIdentical($parsedAddressArray['zip'], '');
        $this->assertIdentical($parsedAddressArray['email'], 'wcc@nospammonkeys.org');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
        );
        $this->assertIdentical($phoneNumbers['cellPhone'], '');
        $this->assertIdentical($phoneNumbers['homePhone'], '');
        $this->assertIdentical($phoneNumbers['workPhone'], '');
        $this->assertIdentical($phoneNumbers['fax'], '');
    }

    function testSampleAddress9()
    {

        $address = <<<EOF
Roger B. Pickler
2073 Physics Ct. E
Maplewood, MI 55219-5815 rbpicker2@mail.fake.com
Home: 641-748-2441
Cell: 641-244-8444
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_PERSON);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertIdentical($parsedAddressArray['firstName'], 'Roger');
        $this->assertIdentical($parsedAddressArray['middleName'], 'B.');
        $this->assertIdentical($parsedAddressArray['lastName'], 'Pickler');
        $this->assertIdentical($parsedAddressArray['addressLineOne'], '2073 Physics Ct. E');
        $this->assertIdentical($parsedAddressArray['addressLineTwo'], '');
        $this->assertIdentical($parsedAddressArray['city'], 'Maplewood');
        $this->assertIdentical($parsedAddressArray['state'], 'MI');
        $this->assertIdentical($parsedAddressArray['zip'], '55219-5815');
        $this->assertIdentical($parsedAddressArray['email'], 'rbpicker2@mail.fake.com');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
        );
        $this->assertIdentical($phoneNumbers['cellPhone'], '641-244-8444');
        $this->assertIdentical($phoneNumbers['homePhone'], '641-748-2441');
        $this->assertIdentical($phoneNumbers['workPhone'], '');
        $this->assertIdentical($phoneNumbers['fax'], '');
    }
    
    function testSampleAddress10()
    {
        $address = <<<EOF
Mike Jackson
PO Box 30205
Salt Lake City, UT 84130-0285
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_PERSON);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertIdentical($parsedAddressArray['firstName'], 'Mike');
        $this->assertIdentical($parsedAddressArray['middleName'], '');
        $this->assertIdentical($parsedAddressArray['lastName'], 'Jackson');
        $this->assertIdentical($parsedAddressArray['addressLineOne'], 'PO Box 30205');
        $this->assertIdentical($parsedAddressArray['addressLineTwo'], '');
        $this->assertIdentical($parsedAddressArray['city'], 'Salt Lake City');
        $this->assertIdentical($parsedAddressArray['state'], 'UT');
        $this->assertIdentical($parsedAddressArray['zip'], '84130-0285');
        $this->assertIdentical($parsedAddressArray['email'], '');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
        );
        $this->assertIdentical($phoneNumbers['cellPhone'], '');
        $this->assertIdentical($phoneNumbers['homePhone'], '');
        $this->assertIdentical($phoneNumbers['workPhone'], '');
        $this->assertIdentical($phoneNumbers['fax'], '');
    }
    
    function testSampleAddress11()
    {

        $address = <<<EOF
Mike Jackson
RR 2
Box 101
Salt Lake City, UT 84130-0285
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_PERSON);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertIdentical($parsedAddressArray['firstName'], 'Mike');
        $this->assertIdentical($parsedAddressArray['middleName'], '');
        $this->assertIdentical($parsedAddressArray['lastName'], 'Jackson');
        $this->assertIdentical($parsedAddressArray['addressLineOne'], 'RR 2');
        $this->assertIdentical($parsedAddressArray['addressLineTwo'], 'Box 101');
        $this->assertIdentical($parsedAddressArray['city'], 'Salt Lake City');
        $this->assertIdentical($parsedAddressArray['state'], 'UT');
        $this->assertIdentical($parsedAddressArray['zip'], '84130-0285');
        $this->assertIdentical($parsedAddressArray['email'], '');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
        );
        $this->assertIdentical($phoneNumbers['cellPhone'], '');
        $this->assertIdentical($phoneNumbers['homePhone'], '');
        $this->assertIdentical($phoneNumbers['workPhone'], '');
        $this->assertIdentical($phoneNumbers['fax'], '');
    }
    
    function testSampleAddress12()
    {

        $address = <<<EOF
Mike Jackson
RR 2
Box 101
Salt Lake City, ut. 84130
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_PERSON);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertIdentical($parsedAddressArray['firstName'], 'Mike');
        $this->assertIdentical($parsedAddressArray['middleName'], '');
        $this->assertIdentical($parsedAddressArray['lastName'], 'Jackson');
        $this->assertIdentical($parsedAddressArray['addressLineOne'], 'RR 2');
        $this->assertIdentical($parsedAddressArray['addressLineTwo'], 'Box 101');
        $this->assertIdentical($parsedAddressArray['city'], 'Salt Lake City');
        $this->assertIdentical($parsedAddressArray['state'], 'UT');
        $this->assertIdentical($parsedAddressArray['zip'], '84130');
        $this->assertIdentical($parsedAddressArray['email'], '');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
        );
        $this->assertIdentical($phoneNumbers['cellPhone'], '');
        $this->assertIdentical($phoneNumbers['homePhone'], '');
        $this->assertIdentical($phoneNumbers['workPhone'], '');
        $this->assertIdentical($phoneNumbers['fax'], '');
    }

    function testSampleAddress13()
    {
        $address = <<<EOF
Roger B. Pickler
2073 Physics Ct. E
Maplewood, MI 55219-5815 rbpicker2@mail.fake.com
Ph: 641-748-2441
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_PERSON);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertIdentical($parsedAddressArray['firstName'], 'Roger');
        $this->assertIdentical($parsedAddressArray['middleName'], 'B.');
        $this->assertIdentical($parsedAddressArray['lastName'], 'Pickler');
        $this->assertIdentical($parsedAddressArray['addressLineOne'], '2073 Physics Ct. E');
        $this->assertIdentical($parsedAddressArray['addressLineTwo'], '');
        $this->assertIdentical($parsedAddressArray['city'], 'Maplewood');
        $this->assertIdentical($parsedAddressArray['state'], 'MI');
        $this->assertIdentical($parsedAddressArray['zip'], '55219-5815');
        $this->assertIdentical($parsedAddressArray['email'], 'rbpicker2@mail.fake.com');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
        );
        $this->assertIdentical($phoneNumbers['cellPhone'], '');
        $this->assertIdentical($phoneNumbers['homePhone'], '641-748-2441');
        $this->assertIdentical($phoneNumbers['workPhone'], '');
        $this->assertIdentical($phoneNumbers['fax'], '');
    }

    function testSampleAddress14()
    {
        $address = <<<EOF
Roger B. Pickler
2073 Physics Ct. E
Maplewood, MI 55219-5815 rbpicker2@mail.fake.com
641-748-2441
F: 555-444-5555
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_PERSON);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertIdentical($parsedAddressArray['firstName'], 'Roger');
        $this->assertIdentical($parsedAddressArray['middleName'], 'B.');
        $this->assertIdentical($parsedAddressArray['lastName'], 'Pickler');
        $this->assertIdentical($parsedAddressArray['addressLineOne'], '2073 Physics Ct. E');
        $this->assertIdentical($parsedAddressArray['addressLineTwo'], '');
        $this->assertIdentical($parsedAddressArray['city'], 'Maplewood');
        $this->assertIdentical($parsedAddressArray['state'], 'MI');
        $this->assertIdentical($parsedAddressArray['zip'], '55219-5815');
        $this->assertIdentical($parsedAddressArray['email'], 'rbpicker2@mail.fake.com');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
        );
        $this->assertIdentical($phoneNumbers['cellPhone'], '');
        $this->assertIdentical($phoneNumbers['homePhone'], '641-748-2441');
        $this->assertIdentical($phoneNumbers['workPhone'], '');
        $this->assertIdentical($phoneNumbers['fax'], '555-444-5555');
    }

    function testSampleAddress15()
    {
        $address = <<<EOF
Roger B. Pickler
2073 Physics Ct. E
Maplewood, MI 55219-5815 rbpicker2@mail.fake.com
Ph: 641-748-2441
Fx: 444-345-4444
TTY: 444-345-4444
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_PERSON);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertIdentical($parsedAddressArray['firstName'], 'Roger');
        $this->assertIdentical($parsedAddressArray['middleName'], 'B.');
        $this->assertIdentical($parsedAddressArray['lastName'], 'Pickler');
        $this->assertIdentical($parsedAddressArray['addressLineOne'], '2073 Physics Ct. E');
        $this->assertIdentical($parsedAddressArray['addressLineTwo'], '');
        $this->assertIdentical($parsedAddressArray['city'], 'Maplewood');
        $this->assertIdentical($parsedAddressArray['state'], 'MI');
        $this->assertIdentical($parsedAddressArray['zip'], '55219-5815');
        $this->assertIdentical($parsedAddressArray['email'], 'rbpicker2@mail.fake.com');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
        );
        $this->assertIdentical($phoneNumbers['cellPhone'], '');
        $this->assertIdentical($phoneNumbers['homePhone'], '641-748-2441');
        $this->assertIdentical($phoneNumbers['workPhone'], '');
        $this->assertIdentical($phoneNumbers['fax'], '444-345-4444');
    }


    function testSampleAddressCompany1()
    {
        $address = <<<EOF
CompuServe, Inc.
5000 Arlington Centre Boulevard
P.O. Box 20212
Columbus, OH 43220
1-800-848-8990 USA
(614) 529-1340 Outside USA
(614) 444-4555 Fax
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_COMPANY);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertIdentical($parsedAddressArray['firstName'], '');
        $this->assertIdentical($parsedAddressArray['middleName'], '');
        $this->assertIdentical($parsedAddressArray['lastName'], '');
        $this->assertIdentical($parsedAddressArray['company'], 'CompuServe, Inc.');
        $this->assertIdentical($parsedAddressArray['addressLineOne'], '5000 Arlington Centre Boulevard');
        $this->assertIdentical($parsedAddressArray['addressLineTwo'], 'P.O. Box 20212');
        $this->assertIdentical($parsedAddressArray['city'], 'Columbus');
        $this->assertIdentical($parsedAddressArray['state'], 'OH');
        $this->assertIdentical($parsedAddressArray['zip'], '43220');
        $this->assertIdentical($parsedAddressArray['email'], '');
        

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
        );
        $this->assertIdentical($phoneNumbers['cellPhone'], '');
        $this->assertIdentical($phoneNumbers['homePhone'], '');
        $this->assertIdentical($phoneNumbers['workPhone'], '');
        $this->assertIdentical($phoneNumbers['fax'], '614-444-4555');
    }

    function testSampleAddressCompany2()
    {
        $address = <<<EOF
Graphical Brass Interfaces, Inc.
R.R. 1, Box 210A
Monticello, IN 47960
1-800-424-7711
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_COMPANY);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertIdentical($parsedAddressArray['firstName'], '');
        $this->assertIdentical($parsedAddressArray['middleName'], '');
        $this->assertIdentical($parsedAddressArray['lastName'], '');
        $this->assertIdentical($parsedAddressArray['company'], 'Graphical Brass Interfaces, Inc.');
        $this->assertIdentical($parsedAddressArray['addressLineOne'], 'R.R. 1, Box 210A');
        $this->assertIdentical($parsedAddressArray['addressLineTwo'], '');
        $this->assertIdentical($parsedAddressArray['city'], 'Monticello');
        $this->assertIdentical($parsedAddressArray['state'], 'IN');
        $this->assertIdentical($parsedAddressArray['zip'], '47960');
        $this->assertIdentical($parsedAddressArray['email'], '');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
        );
        $this->assertIdentical($phoneNumbers['cellPhone'], '');
        $this->assertIdentical($phoneNumbers['homePhone'], '');
        $this->assertIdentical($phoneNumbers['workPhone'], '');
        $this->assertIdentical($phoneNumbers['fax'], '');
    }

    function testSampleAddressCompany3()
    {
        $address = <<<EOF
1st Tech Systems, Inc.
53-54 Cape Road
Mendon, MA 01756
1-800-522-2203
info@firsttechsystemsinc.com
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_COMPANY);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertIdentical($parsedAddressArray['firstName'], '');
        $this->assertIdentical($parsedAddressArray['middleName'], '');
        $this->assertIdentical($parsedAddressArray['lastName'], '');
        $this->assertIdentical($parsedAddressArray['company'], '1st Tech Systems, Inc.');
        $this->assertIdentical($parsedAddressArray['addressLineOne'], '53-54 Cape Road');
        $this->assertIdentical($parsedAddressArray['addressLineTwo'], '');
        $this->assertIdentical($parsedAddressArray['city'], 'Mendon');
        $this->assertIdentical($parsedAddressArray['state'], 'MA');
        $this->assertIdentical($parsedAddressArray['zip'], '01756');
        $this->assertIdentical($parsedAddressArray['email'], 'info@firsttechsystemsinc.com');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
        );
        $this->assertIdentical($phoneNumbers['cellPhone'], '');
        $this->assertIdentical($phoneNumbers['homePhone'], '');
        $this->assertIdentical($phoneNumbers['workPhone'], '');
        $this->assertIdentical($phoneNumbers['fax'], '');
    }
}

/* Tests for AJAXInterface class. */
class AJAXInterfaceTest extends CATSUnitTestCase
{
    function testIsRequiredIDValid()
    {
        $AJAXInterface = new AJAXInterface();

        /* Make sure an unset key does not pass. */
        $random = md5('random' . time());
        $this->assertFalse(
            $AJAXInterface->isRequiredIDValid($random, true, true),
            sprintf("\$_POST['%s'] should not exist and should not be a valid required ID", $random)
        );

        /* Make sure -0, non-numeric strings, and symbols never pass. */
        $invalidIDs = array('-0', 'test', '0abc', '1abc', '-abc', '$');
        foreach ($invalidIDs as $ID)
        {
            $_REQUEST['isRequiredIDValidTest'] = $ID;
            $this->assertFalse(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', true, true),
                sprintf("'%s' should not be a valid required ID", $ID)
            );
            $this->assertFalse(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', true, false),
                sprintf("'%s' should not be a valid required ID", $ID)
            );
            $this->assertFalse(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', false, true),
                sprintf("'%s' should not be a valid required ID", $ID)
            );
            $this->assertFalse(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', false, false),
                sprintf("'%s' should not be a valid required ID", $ID)
            );
        }

        /* Make sure we don't allow '0' if $allowZero is false. */
        $invalidIDs = array(0, '0');
        foreach ($invalidIDs as $ID)
        {
            $_REQUEST['isRequiredIDValidTest'] = $ID;
            $this->assertFalse(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', false, true),
                sprintf("'%s' should not be a valid required ID with \$allowZero false", $ID)
            );
            $this->assertFalse(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', false, false),
                sprintf("'%s' should not be a valid required ID with \$allowZero false", $ID)
            );
        }

        /* Make sure we don't allow negatives if $allowNegative is false. */
        $invalidIDs = array(-1, -100, '-1', '-100');
        foreach ($invalidIDs as $ID)
        {
            $_REQUEST['isRequiredIDValidTest'] = $ID;
            $this->assertFalse(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', true, false),
                sprintf("'%s' should not be a valid required ID with \$allowNegative false", $ID)
            );
            $this->assertFalse(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', false, false),
                sprintf("'%s' should not be a valid required ID with \$allowNegative false", $ID)
            );
        }

        /* Make sure any positive, negative, or 0 number passes valid ID checks
         * if $allowZero and $allowNegative are true.
         */
        $validIDs = array(1, 100, -1, -100, 0, '0', '-100', '1', '65535');
        foreach ($validIDs as $ID)
        {
            $_REQUEST['isRequiredIDValidTest'] = $ID;
            $this->assertTrue(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', true, true),
                sprintf("'%s' should be a valid required ID", $ID)
            );
        }

        /* Make sure any positive number always passes valid ID checks
         * regardless of $allowZero and $allowNegative.
         */
        $validIDs = array(1, 100, '1', '65535');
        foreach ($validIDs as $ID)
        {
            $_REQUEST['isRequiredIDValidTest'] = $ID;
            $this->assertTrue(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', false, false),
                sprintf("'%s' should be a valid required ID", $ID)
            );
        }
    }

    function testIsOptionalIDValid()
    {
        $AJAXInterface = new AJAXInterface();

        /* Make sure an unset key does not pass. */
        $random = md5('random' . time());
        $this->assertFalse(
            $AJAXInterface->isOptionalIDValid($random),
            sprintf("\$_POST['%s'] should not exist and should not be a valid optional ID", $random)
        );

        /* Make sure 0, -0, negative numbers, non-numeric strings, and symbols
         * never pass.
         */
        $invalidIDs = array(
            0, -1, -100, '0', '-0', '-1', '-100',
            'test', '0abc', '1abc', '-abc', '$'
        );
        foreach ($invalidIDs as $ID)
        {
            $_REQUEST['isRequiredIDValidTest'] = $ID;
            $this->assertFalse(
                $AJAXInterface->isOptionalIDValid('isOptionalIDValidTest'),
                sprintf("'%s' should not be a valid optional ID", $ID)
            );
        }

        /* Make sure any positive number always passes. */
        $validIDs = array(1, 100, '1', '65535');
        foreach ($validIDs as $ID)
        {
            $_REQUEST['isOptionalIDValidValidTest'] = $ID;
            $this->assertTrue(
                $AJAXInterface->isOptionalIDValid('isOptionalIDValidValidTest'),
                sprintf("'%s' should be a valid optional ID", $ID)
            );
        }

        /* Make sure 'NULL' always passes. */
        $_REQUEST['isOptionalIDValidValidTest'] = 'NULL';
        $this->assertTrue(
            $AJAXInterface->isOptionalIDValid('isOptionalIDValidValidTest'),
            "'NULL' should be a valid optional ID"
        );
    }
}

/* Tests for ArrayUtility class. */
class ArrayUtilityTest extends CATSUnitTestCase
{
    /* Tests for implodeRange(). */
    function testImplodeRange()
    {
        $pieces = array(
            'Zero',
            'One',
            'Two',
            'Three',
            'Four',
            'Five'
        );

        $result = ArrayUtility::implodeRange(' ', $pieces, 0, 5);
        $this->assertIdentical($result, 'Zero One Two Three Four Five');

        $result = ArrayUtility::implodeRange(' ', $pieces, 0, 4);
        $this->assertIdentical($result, 'Zero One Two Three Four');

        $result = ArrayUtility::implodeRange(' ', $pieces, 1, 4);
        $this->assertIdentical($result, 'One Two Three Four');

        $result = ArrayUtility::implodeRange(' ', $pieces, 1, 3);
        $this->assertIdentical($result, 'One Two Three');

        $result = ArrayUtility::implodeRange(' ', $pieces, 2, 3);
        $this->assertIdentical($result, 'Two Three');

        $result = ArrayUtility::implodeRange(' ', $pieces, 2, 2);
        $this->assertIdentical($result, 'Two');

        $result = ArrayUtility::implodeRange(' ', $pieces, 0, 6);
        $this->assertIdentical($result, 'Zero One Two Three Four Five');

        $result = ArrayUtility::implodeRange(' ', $pieces, -500, 500);
        $this->assertIdentical($result, 'Zero One Two Three Four Five');

        $result = ArrayUtility::implodeRange(', ', $pieces, -500, 500);
        $this->assertIdentical($result, 'Zero, One, Two, Three, Four, Five');
    }
}

/* Tests for AttachmentsTest class. */
class AttachmentsTest extends CATSUnitTestCase
{
}

/* Tests for DatabaseSearch class. */
class DatabaseSearchTest extends CATSUnitTestCase
{
    function testMakeREGEXPString()
    {
        //FIXME: Write me!
    }

    function testMakeBooleanSQLWhere()
    {
        $tests = array(
            array(
                'java',
                '((field REGEXP \'[[:<:]]java[[:>:]]\'))'
            ),
            array(
                'java sql',
                '((field REGEXP \'[[:<:]]java[[:>:]]\') AND (field REGEXP \'[[:<:]]sql[[:>:]]\'))'
            ),
            array(
                'java | sql',
                '((field REGEXP \'[[:<:]]java[[:>:]]\') OR (field REGEXP \'[[:<:]]sql[[:>:]]\'))'
            ),
            array(
                'java,sql',
                '((field REGEXP \'[[:<:]]java[[:>:]]\') OR (field REGEXP \'[[:<:]]sql[[:>:]]\'))'
            ),
            array(
                'java, ,,sql',
                '((field REGEXP \'[[:<:]]java[[:>:]]\') OR (field REGEXP \'[[:<:]]sql[[:>:]]\'))'
            ),
            array(
                'java -sql',
                '((field REGEXP \'[[:<:]]java[[:>:]]\') AND NOT (field REGEXP \'[[:<:]]sql[[:>:]]\'))'
            ),
            array(
                'java !sql',
                '((field REGEXP \'[[:<:]]java[[:>:]]\') AND NOT (field REGEXP \'[[:<:]]sql[[:>:]]\'))'
            ),
            array(
                'java*',
                '((field LIKE \'%java%\'))'
            ),
            array(
                'java* sql*',
                '((field LIKE \'%java%\') AND (field LIKE \'%sql%\'))'
            ),
            array(
                'java (',
                '0'
            ),
            array(
                'java) (',
                '0'
            ),
            array(
                'java ()',
                '((field REGEXP \'[[:<:]]java[[:>:]]\'))'
            )
        );
        
        $db = DatabaseConnection::getInstance();
        foreach ($tests as $test)
        {
            $this->assertIdentical(
                DatabaseSearch::makeBooleanSQLWhere($test[0], $db, 'field'),
                $test[1]
            );
        }
    }
}

/* Tests for BrowserDetection class. */
class BrowserDetectionTest extends CATSUnitTestCase
{
    /* See http://www.useragentstring.com/ for updating. */
    function testDetect()
    {
        // FIXME: Add more browsers!
        $intendedMatches = array(
            array(
                '',
                array('name' => 'Masked', 'version' => ''),
                'Detected masked user agent properly.'
            ),
            array(
                ' ',
                array('name' => 'Masked', 'version' => ''),
                'Detected masked user agent properly.'
            ),
            array(
                'I don\'t exist!',
                array('name' => 'Unknown', 'version' => ''),
                'Detected an unknown user agent properly.'
            ),
            array(
                'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1',
                array('name' => 'Firefox', 'version' => '2.0.0.1'),
                'Detected Firefox 2.0.0.1.'
            ),
            array(
                'Mozilla/5.0 (Windows; U; Windows NT 6.0; fi) AppleWebKit/522.12.1 (KHTML, like Gecko) Version/3.0.1 Safari/522.12.2',
                array('name' => 'Safari', 'version' => '3.0.1'),
                'Detected Safari 3.0.1.'
            ),
            array(
                'Mozilla/5.0 (Macintosh; U; PPC Mac OS X; tr-tr) AppleWebKit/418 (KHTML, like Gecko) Safari/417.9.3',
                array('name' => 'Safari', 'version' => '2.0.3'),
                'Detected Safari 2.0.3.'
            ),
            array(
                'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
                array('name' => 'Internet Explorer', 'version' => '7.0'),
                'Detected Internet Explorer 7.0.5730.11.'
            ),
            array(
                'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.8b2) Gecko/20050702)',
                array('name' => 'Mozilla', 'version' => '1.8b'),
                'Detected Mozilla rv:1.8b.'
            ),
            array(
                'Mozilla/5.0 (compatible; Konqueror/3.5; Linux) KHTML/3.5.3 (like Gecko) Kubuntu 6.06 Dapper',
                array('name' => 'Konqueror', 'version' => '3.5'),
                'Detected Konqueror 3.5.'
            ),
            array(
                'Opera/9.02 (Windows NT 5.1; U; en)',
                array('name' => 'Opera', 'version' => '9.02'),
                'Detected Opera 9.02.'
            ),
            array(
                'Mozilla/5.0 (compatible; iCab 3.0.2; Macintosh; U; PPC Mac OS)',
                array('name' => 'iCab', 'version' => '3.0.2'),
                'Detected iCab 3.0.2.'
            ),
            array(
                'iCab/2.9.1 (Macintosh; U; PPC)',
                array('name' => 'iCab', 'version' => '2.9.1'),
                'Detected iCab 2.9.1.'
            ),
            array(
                'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.9) Gecko/20061211 SeaMonkey/1.0.7',
                array('name' => 'SeaMonkey', 'version' => '1.0.7'),
                'Detected SeaMonkey 1.0.7.'
            ),
            array(
                'Mozilla/4.0 (compatible; MSIE 7.0; America Online Browser 1.1; rev1.5; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
                array('name' => 'America Online Browser', 'version' => '1.1'),
                'Detected America Online Browser 1.1.'
            ),
            array(
                'Mozilla/4.0 (compatible; MSIE 6.0; AOL 9.0; Windows NT 5.1; SV1; FreeprodTB; FunWebProducts; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
                array('name' => 'AOL', 'version' => '9.0'),
                'Detected AOL 9.0.'
            ),
            array(
                'Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US; rv:1.8.0.7) Gecko/20060911 Camino/1.0.3',
                array('name' => 'Camino', 'version' => '1.0.3'),
                'Detected Camino 1.0.3.'
            ),
            array(
                'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
                array('name' => 'Googlebot', 'version' => '2.1'),
                'Detected Googlebot 2.1.'
            ),
            array(
                'Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)',
                array('name' => 'Yahoo Crawler', 'version' => ''),
                'Detected Yahoo Crawler.'
            ),
            array(
                'Lynx/2.8.5rel.5 libwww-FM/2.14 SSL-MM/1.4.1 OpenSSL/0.9.8d',
                array('name' => 'Lynx', 'version' => '2.8.5'),
                'Detected Lynx 2.8.5.'
            ),
            array(
                'Links (0.99pre14; CYGWIN_NT-5.1 1.5.22(0.156/4/2) i686; 80x25)',
                array('name' => 'Links', 'version' => '0.99pre14'),
                'Detected Links 0.99pre14.'
            ),
            array(
                'curl/7.15.4 (i686-pc-cygwin) libcurl/7.15.4 OpenSSL/0.9.8d zlib/1.2.3',
                array('name' => 'cURL', 'version' => '7.15.4'),
                'Detected cURL 7.15.4.'
            ),
            array(
                'Wget/1.10.2',
                array('name' => 'Wget', 'version' => '1.10.2'),
                'Detected Wget 1.10.2.'
            ),
            array(
                'W3C_Validator/1.432.2.5',
                array('name' => 'W3C Validator', 'version' => '1.432.2.5'),
                'Detected W3C Validator 1.432.2.5.'
            ),
            array(
                'W3C-checklink/4.2.1 [4.21] libwww-perl/5.803',
                array('name' => 'W3C Link Checker', 'version' => '4.2.1'),
                'Detected W3C Link Checker 4.2.1.'
            ),
            array(
                'Jigsaw/2.2.5 W3C_CSS_Validator_JFouffa/2.0',
                array('name' => 'W3C CSS Validator', 'version' => '2.0'),
                'Detected W3C CSS Validator 2.0.'
            )
        );

        foreach ($intendedMatches as $intendedMatch)
        {
            $this->assertIdentical(
                BrowserDetection::detect($intendedMatch[0]),
                $intendedMatch[1],
                ltrim($intendedMatch[2] . ' %s')
            );
        }
    }
}

/* Tests for Calendar class. */
class CalendarTest extends CATSUnitTestCase
{
}

/* Tests for Candidates class. */
class CandidatesTest extends CATSUnitTestCase
{
}

/* Tests for CATSUtility class. */
class CATSUtilityTest extends CATSUnitTestCase
{
    function testGetVersion()
    {
        //FIXME: Write me!
    }

    function testGetVersionAsInteger()
    {
        //FIXME: Write me!
    }

    function testGetBuild()
    {
        //FIXME: Write me!
    }

    function testGetAbsoluteURI()
    {
        //FIXME: Write me!
    }

    function getIndexName()
    {
        //FIXME: Write me!
    }
}

/* Tests for Companies class. */
class CompaniesTest extends CATSUnitTestCase
{
}

/* Tests for Contacts class. */
class ContactsTest extends CATSUnitTestCase
{
}

/* Tests for Dashboard class. */
class DashboardTest extends CATSUnitTestCase
{
}

/* Tests for DatabaseConnection class. */
class DatabaseConnectionTest extends CATSUnitTestCase
{
    function testMakeQueryString()
    {
        $db = DatabaseConnection::getInstance();

        $strings = array(
            array('test string',  "'test string'"),
            array('te\st', "'te\\\st'"),
            array('te\s\t', "'te\\\s\\\\t'"),
            array('te\'st',  "'te\\'st'"),
            array('\'; DELETE FROM test_table; SELECT \'',  "'\'; DELETE FROM test_table; SELECT \''"),
            array('te\'s`t',  "'te\\'s`t'")
        );

        foreach ($strings as $key => $value)
        {
            $this->assertIdentical(
                $db->makeQueryString($value[0]),
                $value[1],
                $value[0] . ' => ' . $value[1]
            );
        }
    }
    
    function testEscapeString()
    {
        $db = DatabaseConnection::getInstance();

        $strings = array(
            array('test string',  "test string"),
            array('te\st', "te\\\st"),
            array('te\s\t', "te\\\s\\\\t"),
            array('te\'st',  "te\\'st"),
            array('\'; DELETE FROM test_table; SELECT \'',  "\'; DELETE FROM test_table; SELECT \'"),
            array('te\'s`t',  "te\\'s`t")
        );

        foreach ($strings as $key => $value)
        {
            $this->assertIdentical(
                $db->escapeString($value[0]),
                $value[1],
                $value[0] . ' => ' . $value[1]
            );
        }
    }

    function testMakeQueryStringOrNULL()
    {
        $db = DatabaseConnection::getInstance();

        $strings = array(
            array('test string',  "'test string'"),
            array('te\st', "'te\\\st'"),
            array('te\s\t', "'te\\\s\\\\t'"),
            array('te\'st',  "'te\\'st'"),
            array('\'; DELETE FROM test_table; SELECT \'',  "'\'; DELETE FROM test_table; SELECT \''"),
            array('te\'s`t',  "'te\\'s`t'"),
            array('    ',  'NULL'),
            array(' ',  'NULL'),
            array('	 		',  'NULL'),
            array('',  'NULL')
        );

        foreach ($strings as $key => $value)
        {
            $this->assertIdentical(
                $db->makeQueryStringOrNULL($value[0]),
                $value[1],
                $value[0] . ' => ' . $value[1]
            );
        }
    }

    function testMakeQueryInteger()
    {
        $db = DatabaseConnection::getInstance();

        $strings = array(
            array('1.5',  1),
            array('not-a-double', 0),
            array('1.999', 1),
            array('1notastring', 1),
            array('-22356', -22356)
        );

        foreach ($strings as $key => $value)
        {
            $this->assertIdentical(
                $db->makeQueryInteger($value[0]),
                $value[1],
                $value[0] . ' => ' . $value[1]
            );
        }
    }

    function testMakeQueryIntegerOrNULL()
    {
        $db = DatabaseConnection::getInstance();

        $strings = array(
            array('1.5',  1),
            array('not-a-double', 0),
            array('1.999', 1),
            array('1notastring', 1),
            array('-22356', -22356),
            array('-1', 'NULL')
        );

        foreach ($strings as $key => $value)
        {
            $this->assertIdentical(
                $db->makeQueryIntegerOrNULL($value[0]),
                $value[1],
                $value[0] . ' => ' . $value[1]
            );
        }
    }

    function testMakeQueryDouble()
    {
        $db = DatabaseConnection::getInstance();

        $strings = array(
            array('1.5',  '1.5'),
            array('not-a-double', '0.0'),
            array('1.99999999999999', '2', 2),
            array('1.80123', '1.80', 2),
            array('1.99999999999999', '1.99999999999999'),
        );

        foreach ($strings as $key => $value)
        {
            if (isset($value[2]))
            {
                $queryDouble = $db->makeQueryDouble($value[0], $value[2]);
            }
            else
            {
                $queryDouble = $db->makeQueryDouble($value[0]);
            }
            
            $this->assertIdentical(
                $queryDouble,
                $value[1],
                $value[0] . ' => ' . $value[1]
            );
        }
    }

    function testQuery()
    {
        $db = DatabaseConnection::getInstance();

        $queryResult = $db->query('INSERT INTO installtest (id) VALUES(35)');
        $this->assertNotIdentical(
            $queryResult,
            false,
            'INSERT query should succeed'
        );

        $queryResult = $db->query('SELECT * FROM installtest LIMIT 1');
        $this->assertNotIdentical(
            $queryResult,
            false,
            'SELECT query should succeed'
        );
        $this->assertEqual(
            mysql_num_rows($queryResult),
            1,
            '1 row should be returned'
        );
        $this->assertTrue(
            !$db->isEOF(),
            'EOF should not be received'
        );

        $queryResult = $db->query('UPDATE installtest SET id = 34 WHERE id = 35');
        $this->assertNotIdentical(
            $queryResult,
            false,
            'UPDATE query should succeed'
        );

        $queryResult = $db->query('DELETE FROM installtest WHERE id = 34');
        $this->assertNotIdentical(
            $queryResult,
            false,
            'DELETE query should succeed'
        );
    }
}

/* Tests for DateUtility class. */
class DateUtilityTest extends CATSUnitTestCase
{
    /* Tests for getStartingWeekday(). */
    function testGetStartingWeekday()
    {
        $this->assertIdentical(
            DateUtility::getStartingWeekday(CALENDAR_MONTH_MARCH, 2006),
            CALENDAR_DAY_WEDNSDAY
        );

        $this->assertIdentical(
            DateUtility::getStartingWeekday(CALENDAR_MONTH_MARCH, 1987),
            CALENDAR_DAY_SUNDAY
        );
        $this->assertIdentical(
            DateUtility::getStartingWeekday(CALENDAR_MONTH_APRIL, 1987),
            CALENDAR_DAY_WEDNSDAY
        );
    }

    /* Tests for getStartingWeekday(). */
    function testGetDaysInMonth()
    {
        $this->assertIdentical(
            DateUtility::getDaysInMonth(CALENDAR_MONTH_MARCH, 2006),
            31
        );

        $this->assertIdentical(
            DateUtility::getDaysInMonth(CALENDAR_MONTH_MARCH, 1987),
            DateUtility::getDaysInMonth(CALENDAR_MONTH_MARCH, 2006)
        );

        $this->assertIdentical(
            DateUtility::getDaysInMonth(CALENDAR_MONTH_APRIL, 1987),
            30
        );

        /* Leap years... */
        $this->assertIdentical(
            DateUtility::getDaysInMonth(CALENDAR_MONTH_FEBRUARY, 2008),
            29
        );
        $this->assertIdentical(
            DateUtility::getDaysInMonth(CALENDAR_MONTH_FEBRUARY, 2006),
            28
        );
    }

    /* Tests for getMonthName(). */
    function testGetMonthName()
    {
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_JANUARY),
            'January'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_FEBRUARY),
            'February'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_MARCH),
            'March'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_APRIL),
            'April'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_MAY),
            'May'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_JUNE),
            'June'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_JULY),
            'July'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_AUGUST),
            'August'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_SEPTEMBER),
            'September'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_OCTOBER),
            'October'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_NOVEMBER),
            'November'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_DECEMBER),
            'December'
        );
    }

    /* Tests for validate(). */
    function testValidate()
    {
        $validDates = array(
            array('/', '01/01/01', DATE_FORMAT_MMDDYY),
            array('/', '02/27/05', DATE_FORMAT_MMDDYY),
            array('/', '02/28/05', DATE_FORMAT_MMDDYY),
            array('/', '02/29/04', DATE_FORMAT_MMDDYY),
            array('/', '02/29/00', DATE_FORMAT_MMDDYY),
            array('/', '12/31/05', DATE_FORMAT_MMDDYY),
            array('-', '12-31-05', DATE_FORMAT_MMDDYY),
            array('-', '02-29-00', DATE_FORMAT_MMDDYY),
            array('-', '22-02-07', DATE_FORMAT_DDMMYY),
            array('-', '2007-03-25', DATE_FORMAT_YYYYMMDD)
        );

        $invalidDates = array(
            array('/', '00/00/00', DATE_FORMAT_MMDDYY),
            array('/', '02/29/05', DATE_FORMAT_MMDDYY),
            array('/', '02/31/05', DATE_FORMAT_MMDDYY),
            array('/', '13/01/05', DATE_FORMAT_MMDDYY),
            array('/', '00/01/05', DATE_FORMAT_MMDDYY),
            array('/', '12-01-05', DATE_FORMAT_MMDDYY),
            array('-', '00/01/05', DATE_FORMAT_MMDDYY),
            array('-', '00-01-05', DATE_FORMAT_MMDDYY),
            array('/', '00/01/2005', DATE_FORMAT_MMDDYY),
            array('-', '00/01/2005', DATE_FORMAT_MMDDYY),
            array('-', '00-01-2005', DATE_FORMAT_MMDDYY),
            array('-', '000105', DATE_FORMAT_MMDDYY),
            array('-', 'Test!', DATE_FORMAT_MMDDYY),
            array('-', '02-29-07', DATE_FORMAT_DDMMYY),
            array('-', '2007-03-40', DATE_FORMAT_YYYYMMDD),
            array('-', 'This sentence contains 12-01-05.', DATE_FORMAT_MMDDYY)
        );

        foreach ($validDates as $key => $value)
        {
            $this->assertTrue(
                DateUtility::validate($value[0], $value[1], $value[2]),
                $value[1] . ' (Separator: ' . $value[0] . ')'
            );
        }

        foreach ($invalidDates as $key => $value)
        {
            $this->assertFalse(
                DateUtility::validate($value[0], $value[1], $value[2]),
                $value[1] . ' (Separator: ' . $value[0] . ')'
            );
        }
    }

    /* Tests for convert(). */
    function testConvert()
    {
        $dates = array(
            array('/', '01/01/01', DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD, '2001/01/01'),
            array('/', '02/27/01', DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD, '2001/02/27'),
            array('-', '01-01-01', DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD, '2001-01-01'),
            array('-', '02-27-01', DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD, '2001-02-27'),
            array('-', '2002-01-30', DATE_FORMAT_YYYYMMDD, DATE_FORMAT_MMDDYY, '01-30-02'),
        );

        foreach ($dates as $key => $value)
        {
            $this->assertIdentical(
                DateUtility::convert($value[0], $value[1], $value[2], $value[3]),
                $value[4]
            );
        }
    }
}

/* Tests for EmailTemplates class. */
class EmailTemplatesTest extends CATSUnitTestCase
{
}

/* Tests for Encryption class. */
class EncryptionTest extends CATSUnitTestCase
{
}

/* Tests for Export class. */
class ExportTest extends CATSUnitTestCase
{
}

/* Tests for FileUtility class. */
class FileUtilityTest extends CATSUnitTestCase
{
    function testSizeToHuman()
    {
        $tests = array(
            array(1024,       false, 0, '1 KB'),
            array(2048,       false, 0, '2 KB'),
            array(1048576,    false, 0, '1 MB'),
            array(2097152,    false, 0, '2 MB'),
            array(1073741824, false, 0, '1 GB'),
            array(1024,       2,     0, '1 KB'),
            array(1048576,    2,     0, '1 MB'),
            array(1073741824, 2,     0, '1 GB'),
            array(1536,       1,     0, '1.5 KB'),
            array(1536,       2,     0, '1.5 KB'),
            array(1546,       2,     0, '1.51 KB'),
            array(1024,       false, 0, '1 KB'),
            array(1024,       3,     2, '0.001 MB'),
            array(0,          false, 0, '0 B'),
            array(0,          false, 1, '0 KB'),
            array(0,          false, 2, '0 MB'),
            array(0,          false, 3, '0 GB'),
            array(0,          false, 4, '0 TB'),
            array(0,          false, 5, '0 PB')
        );

        foreach ($tests as $key => $value)
        {
            $this->assertIdentical(
                FileUtility::sizeToHuman($value[0], $value[1], $value[2]),
                $value[3]
            );
        }
    }
    
    function testGetUniqueDirectory()
    {
        /* Get two random directory names; one with extra data and one
         * without.
         */
        $directoryA = FileUtility::getUniqueDirectory('attachments');
        $directoryB = FileUtility::getUniqueDirectory('attachments/', 'Extra Data!');
        
        /* Directories are also unique in time, with randomness added. */
        sleep(1);
        $directoryC = FileUtility::getUniqueDirectory('attachments');
        $directoryD = FileUtility::getUniqueDirectory('attachments');
        
        /* Make sure all directory names look like md5 strings. */
        $this->assertEqual(
            strlen($directoryA),
            32,
            sprintf("'%s' should be 32 characters long", $directoryA)
        );
        $this->assertEqual(
            strlen($directoryB),
            32,
            sprintf("'%s' should be 32 characters long", $directoryB)
        );
        $this->assertEqual(
            strlen($directoryC),
            32,
            sprintf("'%s' should be 32 characters long", $directoryB)
        );
        $this->assertEqual(
            strlen($directoryD),
            32,
            sprintf("'%s' should be 32 characters long", $directoryB)
        );

        /* Make sure extra data is actually being added (directory names
         * should not be identical).
         */
        $this->assertNotEqual(
            $directoryA,
            $directoryB,
            sprintf("'%s' should not equal '%s'", $directoryA, $directoryB)
        );
        $this->assertNotEqual(
            $directoryA,
            $directoryC,
            sprintf("'%s' should not equal '%s'", $directoryA, $directoryC)
        );
        $this->assertNotEqual(
            $directoryA,
            $directoryD,
            sprintf("'%s' should not equal '%s'", $directoryA, $directoryD)
        );
        $this->assertNotEqual(
            $directoryB,
            $directoryC,
            sprintf("'%s' should not equal '%s'", $directoryB, $directoryC)
        );
        $this->assertNotEqual(
            $directoryB,
            $directoryD,
            sprintf("'%s' should not equal '%s'", $directoryB, $directoryD)
        );
        $this->assertNotEqual(
            $directoryC,
            $directoryD,
            sprintf("'%s' should not equal '%s'", $directoryC, $directoryD)
        );

        /* No directory names should exist. */
        $this->assertFalse(
            file_exists('attachments/' . $directoryA),
            sprintf("'attachments/%s' should not exist", $directoryA)
        );
        $this->assertFalse(
            file_exists('attachments/' . $directoryB),
            sprintf("'attachments/%s' should not exist", $directoryB)
        );
        $this->assertFalse(
            file_exists('attachments/' . $directoryC),
            sprintf("'attachments/%s' should not exist", $directoryB)
        );
        $this->assertFalse(
            file_exists('attachments/' . $directoryD),
            sprintf("'attachments/%s' should not exist", $directoryB)
        );
    }
}

/* Tests for ForeignEntries class. */
class ForeignEntriesTest extends CATSUnitTestCase
{
}

/* Tests for GraphGenerator class. */
class GraphGeneratorTest extends CATSUnitTestCase
{
}

/* Tests for Graphs class. */
class GraphsTest extends CATSUnitTestCase
{
}

/* Tests for History class. */
class HashUtilityTest extends CATSUnitTestCase
{
    function testHashInt32()
    {
        // FIXME: Test a couple of constants?
    }
    
    function testUnHashInt32()
    {
        // FIXME: Test a couple of constants?
    }
    
    function testInt32HashingSanity()
    {
        /* Max value for a signed 32-bit integer. */
        $integerMax = 2147483647;
        
        $maxValue = ($integerMax - HashUtility::HASH_OFFSET);
        
        $integersToTest = array(
            0, 1, 2, 3, 4, 5,
            10, 21, 30, 43, 50,
            101, 211, 300, 461, 500,
            1011, 2000, 3000, 4051, 4096,
            1000000000,
            2000000000,
            $maxValue
        );
        
        $integersToTestNegative = array(
            1, 2, 3, 4, 5,
            10, 21, 30, 43, 50,
            101, 211, 300, 461, 500,
            1011, 2000, 3000, 4051, 4096,
            1000000000,
            2000000000,
            $maxValue,
            $integerMax,
            $integerMax + HashUtility::HASH_OFFSET
        );
        
        foreach($integersToTest as $integer)
        {
            $hash     = HashUtility::hashInt32($integer);
            $unhashed = HashUtility::unhashInt32($hash);
            
            $this->assertEqual(
                $integer,
                $unhashed,
                sprintf("HashUtility::hashInt32(%s) should equal HashUtility::unhashInt32(%s), equals %s", $integer, $hash, $unhashed)
            );
        }
        
        foreach($integersToTestNegative as $integer)
        {
            $integer *= -1;
            
            $hash     = HashUtility::hashInt32($integer);
            $unhashed = HashUtility::unhashInt32($hash);
            
            $this->assertEqual(
                $integer,
                $unhashed,
                sprintf("HashUtility::hashInt32(%s) should equal HashUtility::unhashInt32(%s), equals %s", $integer, $hash, $unhashed)
            );
        }
    }
}

/* Tests for History class. */
class HistoryTest extends CATSUnitTestCase
{
}

/* Tests for Hooks class. */
class HooksTest extends CATSUnitTestCase
{
}

/* Tests for HotLists class. */
class SavedListsTest extends CATSUnitTestCase
{
}

/* Tests for InfoString class. */
class InfoStringTest extends CATSUnitTestCase
{
}

/* Tests for Installation class. */
class InstallationTestsTest extends CATSUnitTestCase
{
}

/* Tests for CareerPortal class. */
class CareerPortalTest extends CATSUnitTestCase
{
}

/* Tests for ListEditor class. */
class ListEditorTest extends CATSUnitTestCase
{
}

/* Tests for LoginActivity class. */
class LoginActivityTest extends CATSUnitTestCase
{
}

/* Tests for Mailer class. */
class MailerTest extends CATSUnitTestCase
{
}

/* Tests for ModuleUtility class. */
class ModuleUtilityTest extends CATSUnitTestCase
{
}

/* Tests for MRU class. */
class MRUTest extends CATSUnitTestCase
{
}

/* Tests for DocumentToText class. */
class DocumentToTextTest extends CATSUnitTestCase
{
}

/* Tests for NewVersionCheck class. */
class NewVersionCheckTest extends CATSUnitTestCase
{
}

/* Tests for PagerCheck class. */
class PagerTest extends CATSUnitTestCase
{
}

/* Tests for Pipelines class. */
class PipelinesTest extends CATSUnitTestCase
{
}

/* Tests for ResultSetUtility class. */
class ResultSetUtilityTest extends CATSUnitTestCase
{
    /* Tests for findRowByColumnValue(). */
    function testFindRowByColumnValue()
    {
        $input = array(
            0   =>  array(
                'ID'    => 100,
                'Name'  => 'Cat',
                'Sound' => 'Meow',
                'Type'  => 'Mammal'
            ),
            1   =>  array(
                'ID'    => 200,
                'Name'  => 'Dog',
                'Sound' => 'Bark',
                'Type'  => 'Mammal'
            ),
            2  =>  array(
                'ID'    => 300,
                'Name'  => 'Wolf',
                'Sound' => 'Howl',
                'Type'  => 'Mammal'
            ),
            3   =>  array(
                'ID'    => 400,
                'Name'  => 'Cow',
                'Sound' => 'Moo',
                'Type'  => 'Mammal'
            ),
            4 =>  array(
                'ID'    => 500,
                'Name'  => 'Snake',
                'Sound' => 'Hiss',
                'Type'  => 'Reptile'
            )
        );

        /* Test simple 'finding' functionality. */
        $this->assertIdentical(
            ResultSetUtility::findRowByColumnValue($input, 'ID', 100),
            0
        );
        $this->assertIdentical(
            ResultSetUtility::findRowByColumnValue($input, 'ID', 200),
            1
        );
        $this->assertIdentical(
            ResultSetUtility::findRowByColumnValue($input, 'ID', 300),
            2
        );
        $this->assertIdentical(
            ResultSetUtility::findRowByColumnValue($input, 'ID', 400),
            3
        );
        $this->assertIdentical(
            ResultSetUtility::findRowByColumnValue($input, 'ID', 500),
            4
        );
        $this->assertIdentical(
            ResultSetUtility::findRowByColumnValue($input, 'ID', 500.0),
            4
        );
        $this->assertIdentical(
            ResultSetUtility::findRowByColumnValue($input, 'ID', '500'),
            4
        );
        $this->assertIdentical(
            ResultSetUtility::findRowByColumnValue($input, 'Type', 'Mammal'),
            0
        );
        $this->assertIdentical(
            ResultSetUtility::findRowByColumnValue($input, 'Sound', 'Hiss'),
            4
        );
        $this->assertIdentical(
            ResultSetUtility::findRowByColumnValue($input, 'ID', 600),
            false
        );

        /* Test skipping. */
        $this->assertIdentical(
            ResultSetUtility::findRowByColumnValue($input, 'Type', 'Mammal', 1),
            1
        );
        $this->assertIdentical(
            ResultSetUtility::findRowByColumnValue($input, 'Type', 'Mammal', 2),
            2
        );


        /* Test strict matching. */
        $this->assertIdentical(
            ResultSetUtility::findRowByColumnValueStrict($input, 'Sound', 'Hiss'),
            4
        );
        $this->assertIdentical(
            ResultSetUtility::findRowByColumnValueStrict($input, 'ID', '500'),
            false
        );
        $this->assertIdentical(
            ResultSetUtility::findRowByColumnValueStrict($input, 'ID', 500.0),
            false
        );

        /* Just in case strict and non-strict functions aren't identical... */
        $this->assertIdentical(
            ResultSetUtility::findRowByColumnValueStrict($input, 'Type', 'Mammal', 1),
            1
        );
        $this->assertIdentical(
            ResultSetUtility::findRowByColumnValueStrict($input, 'Type', 'Mammal', 2),
            2
        );
    }
}

/* Tests for StringUtility class. */
class StringUtilityTest extends CATSUnitTestCase
{
    /* Tests for isURL(). */
    function testIsURL()
    {
        $validURLs = array(
            'http://www.nospammonkeys.org',
            'http://www.eggheads.org/~wcc/test.txt',
            'ftp://ftp.eggheads.org/wcc/test.txt',
            'http://wcc:test@www.nospammonkeys.org',
            'http://test@www.nospammonkeys.org',
            'http://www.eggheads.org:80/~wcc/test.txt',
            'http://wcc:test@www.nospammonkeys.org:80/q.php?test=1&test2=bl+ah',
            'http://wcc:test@www.nospammonkeys.org:80/q.php?test=1&test2=/blah@blah.com',
            'www.cognizo.com/index.php',
            'http://24.72.64.156/index.php',
            'localhost/index.php'
        );

        $invalidURLs = array(
            '770-667-5085',
            'nntp://129.222.2532.5/index.php',
            '/index.php',
            'My web site is http://www.microsoft.com/index.php and this is a test sentence.'
        );

        foreach ($validURLs as $key => $value)
        {
            $this->assertTrue(
                StringUtility::isURL($value),
                sprintf("'%s' should be recognized as a URL", $value)
            );
        }

        foreach ($invalidURLs as $key => $value)
        {
            $this->assertFalse(
                StringUtility::isURL($value),
                sprintf("'%s' should not be recognized as a URL", $value)
            );
        }
    }

    /* Tests for extractURL(). */
    function testExtractURL()
    {
        $URLsToExtract = array(
            array(
                'http://wcc:test@www.nospammonkeys.org:80/q.php?test=1&test2=/blah@blah.com',
                'http://wcc:test@www.nospammonkeys.org/q.php?test=1&test2=/blah@blah.com'
            ),
            array(
                'http://wcc@www.nospammonkeys.org:80/q.php?test=1&test2=/blah@blah.com',
                'http://wcc@www.nospammonkeys.org/q.php?test=1&test2=/blah@blah.com'
            ),
            array(
                'www.cognizo.com/index.php',
                'http://www.cognizo.com/index.php'
            ),
            array(
                '24.72.64.156/index.php',
                'http://24.72.64.156/index.php'
            )
        );

        foreach ($URLsToExtract as $key => $value)
        {
            $formattedURL = StringUtility::extractURL($value[0]);
            $this->assertTrue(
                $formattedURL === $value[1],
                sprintf("Extracting URL from '%s' should result in '%s'", $value[0], $value[1])
            );
        }
    }

    /* Tests for isPhoneNumber(). */
    function testIsPhoneNumber()
    {
        $validPhoneNumbers = array(
            '7706675085',
            '(770) 667-5085',
            '(770) 667/5085',
            '(770) 667.5085',
            '(770) 667 5085',
            '(770)667/5085',
            '(770)6675085',
            '770-667-5085',
            '770.667.5085',
            '770/667/5085',
            '(+01) 909-444-4444',
            '(+01)909-444-4444',
            '(+01)9094444444',
            '+019094444444',
            '+01 9094444444',
            '1-800-444-3899',
            '770-667-5085 x 15',
            '770-667-5085 ex 15',
            '770-667-5085 ext 15',
            '770 - 667 - 5085 extension 15',
            '770-667-5085x15',
            '770-667-5085ex15',
            '770-667-5085ext15',
            '770-667-5085extension 15',
            '(+01)9094444444extension 15',
            '1-800-444-3899 x 90'
        );

        $invalidPhoneNumbers = array(
            '770-667-5085 (Cell)',
            'AAA-BBB-CCCC',
            'ThisIsNotAPhoneNumber x 15',
            '801 East Street #12',
            '301 Glendale Road ext 504',
            '/index.php'
        );

        foreach ($validPhoneNumbers as $key => $value)
        {
            $this->assertTrue(
                StringUtility::isPhoneNumber($value),
                sprintf("'%s' should be recognized as a phone number", $value)
            );
        }

        foreach ($invalidPhoneNumbers as $key => $value)
        {
            $this->assertFalse(
                StringUtility::isPhoneNumber($value),
                sprintf("'%s' should not be recognized as a phone number", $value)
            );
        }
    }

    /* Tests for containsPhoneNumber(). */
    function testContainsPhoneNumber()
    {
        $validStrings = array(
            '770-667-5085 (Cell)',
            'My phone number is 770-667-5085.',
            'Cell:770-667-5085.',
            'ph7706675085'
        );

        $invalidStrings = array(
            'My phone number is 770-667-508.',
            'ph770667509'
        );

        foreach ($validStrings as $key => $value)
        {
            $this->assertTrue(
                StringUtility::containsPhoneNumber($value),
                sprintf("'%s' should be recognized as containing a phone number", $value)
            );
        }

        foreach ($invalidStrings as $key => $value)
        {
            $this->assertFalse(
                StringUtility::containsPhoneNumber($value),
                sprintf("'%s' should not be recognized as containing a phone number", $value)
            );
        }

        /* Some sample text to test with. */
        $fairyTale = implode('', file('./modules/tests/SampleText.txt'));

        /* I can assure you that none of Grimm's fairy tales contain phone numbers. */
        $this->assertFalse(StringUtility::containsPhoneNumber($fairyTale));
    }

    function testExtractPhoneNumber()
    {
        $phoneNumbersToExtract = array(
            array(
                '(+01)9094444444extension 15',
                '909-444-4444 x 15'
            ),
            array(
                '1-800-444-3899 x 90',
                '800-444-3899 x 90'
            ),
            array(
                '+019094444444',
                '909-444-4444'
            ),
            array(
                '7706675085',
                '770-667-5085'
            ),
            array(
                '770-667-5085 extension 15',
                '770-667-5085 x 15'
            ),
            array(
                '(770) 667/5085',
                '770-667-5085'
            ),
            array(
                '(770) 667.5085',
                '770-667-5085'
            ),
            array(
                'my phone number is (770) 667.5085extension 15, it is.',
                '770-667-5085 x 15'
            ),
            array(
                '+420466052932',
                '+420466052932'
            ),
            array(
                '+17706675085',
                '770-667-5085'
            )
        );

        foreach ($phoneNumbersToExtract as $key => $value)
        {
            $formattedPhoneNumber = StringUtility::extractPhoneNumber($value[0]);
            $this->assertTrue(
                $formattedPhoneNumber === $value[1],
                sprintf("Extracting phone number from '%s' should result in '%s'", $value[0], $value[1])
            );
        }
    }

    function testIsEmailAddress()
    {
        $validEmails = array(
            'wcc@nospammonkeys.org',
            'will.buckner [at] eggheads [dot] org',
            'will.buckner (at) eggheads (dot) org',
            'will.buckner@eggheads [dot] org',
            'will.buckner [at] eggheads.org',
            'will.buckner[AT]eggheads[DOT]org',
            'will.buckner at eggheads dot org',
            'wcc [at] lists [dot] nospammonkeys [DOT] org'
        );

        $invalidEmails = array(
            'i am at the movies dot dot dot',
            'not@valid',
            'not@valid...com',
            'my e-mail address is will.buckner [at] eggheads [dot] org'
        );

        foreach ($validEmails as $key => $value)
        {
            $this->assertTrue(
                StringUtility::isEmailAddress($value),
                sprintf("'%s' should be recognized as an e-mail address", $value)
            );
        }

        foreach ($invalidEmails as $key => $value)
        {
            $this->assertFalse(
                StringUtility::isEmailAddress($value),
                sprintf("'%s' should not be recognized as an e-mail address", $value)
            );
        }
    }

    /* Tests for containsEmailAddress(). */
    function testContainsEmailAddress()
    {
        $validStrings = array(
            'my e-mail address is will.buckner [at] eggheads [dot] org',
            'Email: will.buckner (at) eggheads (dot) org',
            'E-Mail:wcc@nospammonkeys.org',
            'E-Mail: wcc [at] lists [dot] nospammonkeys [dot] org'
        );

        $invalidStrings = array(
            'i am at the movies dot dot dot',
            'not@valid',
            'not@valid...com'
        );

        foreach ($validStrings as $key => $value)
        {
            $this->assertTrue(
                StringUtility::containsEmailAddress($value),
                sprintf("'%s' should be recognized as containing an e-mail address", $value)
            );
        }

        foreach ($invalidStrings as $key => $value)
        {
            $this->assertFalse(
                StringUtility::containsEmailAddress($value),
                sprintf("'%s' should not be recognized as containing an e-mail address", $value)
            );
        }

        /* Some sample text to test with. */
        $fairyTale = implode('', file('./modules/tests/SampleText.txt'));

        /* I can assure you that none of Grimm's fairy tales contain e-mail addresses. */
        $this->assertFalse(StringUtility::containsEmailAddress($fairyTale));
    }

    /* Tests for extractEmailAddress(). */
    function testExtractEmailAddress()
    {
        $emailAddressesToExtract = array(
            array(
                'wcc@nospammonkeys.org',
                'wcc@nospammonkeys.org'
            ),
            array(
                'wcc@lists.nospammonkeys.org',
                'wcc@lists.nospammonkeys.org'
            ),
            array(
                'wcc at nospammonkeys dot org',
                'wcc@nospammonkeys.org'
            ),
            array(
                'wcc [at] nospammonkeys [dot] org',
                'wcc@nospammonkeys.org'
            ),
            array(
                'wcc [at] lists [dot] nospammonkeys [dot] org',
                'wcc@lists.nospammonkeys.org'
            ),
            array(
                'wcc (at) nospammonkeys (dot) org',
                'wcc@nospammonkeys.org'
            ),
            array(
                'wcc.test (at) nospammonkeys (dot) org',
                'wcc.test@nospammonkeys.org'
            ),
            array(
                'wcc_test(at)nospammonkeys(dot)org',
                'wcc_test@nospammonkeys.org'
            ),
            array(
                'my e-mail address is wcc (at) no (DOT) spammonkeys (DOT) org, but thanks anyway.',
                'wcc@no.spammonkeys.org'
            )
        );

        foreach ($emailAddressesToExtract as $key => $value)
        {
            $formattedEmailAddress = StringUtility::extractEmailAddress($value[0]);
            $this->assertTrue(
                $formattedEmailAddress === $value[1],
                sprintf("Extracting e-mail address from '%s' should result in '%s'", $value[0], $value[1])
            );
        }
    }

    /* Tests for removeEmailAddress(). */
    function testRemoveEmailAddress()
    {
        $this->assertIdentical(
            StringUtility::removeEmailAddress('wcc@nospammonkeys.org', true),
            ''
        );

        $this->assertIdentical(
            StringUtility::removeEmailAddress('wcc@nospammonkeys.org', false),
            ''
        );

        $this->assertIdentical(
            StringUtility::removeEmailAddress('Will Buckner wcc@nospammonkeys.org', true),
            'Will Buckner'
        );

        $this->assertIdentical(
            StringUtility::removeEmailAddress('Will Buckner wcc@nospammonkeys.org', false),
            'Will Buckner '
        );

        $this->assertIdentical(
            StringUtility::removeEmailAddress('Will Buckner wcc@nospammonkeys.org 770.223.0123   ', true),
            'Will Buckner  770.223.0123'
        );

        $this->assertIdentical(
            StringUtility::removeEmailAddress('Will Buckner wcc@nospammonkeys.org 770.223.0123   ', false),
            'Will Buckner  770.223.0123   '
        );


        $this->assertNotIdentical(
            StringUtility::removeEmailAddress('wcc@nospammonkeys.org ', false),
            ''
        );
        $this->assertNotIdentical(
            StringUtility::removeEmailAddress(' wcc@nospammonkeys.org    ', true),
            '     '
        );
    }

    /* Tests for isCityStateZip(). */
    function disabledtestIsCityStateZip()
    {
        $validCityStateZips = array(
            'Alpharetta, GA  30004',
            'O Fallon, IL  62269',
            'My Really Long City  , MI  48048',
            'My Really Long City  , MI  48048-5404',
            'Maplewood, MN 55119-5805',
            'New Haven, MI  48048',
            'Natick, MA  01760',
            'Plano, TX  75093',
            'Sterling, VA  20164'
        );

        $invalidCityStateZips = array(
            '12345',
            'abdde',
            'Test Texas 1223',
            'Test, TX 1111',
            'PO Box 55403',
            'P.O. Box 55403',
            'Post Office Box 55403'
        );

        foreach ($validCityStateZips as $key => $value)
        {
            $this->assertTrue(
                StringUtility::isCityStateZip($value),
                sprintf("'%s' should be recognized as a 'City, State, Zip' combination", $value)
            );
        }

        foreach ($invalidCityStateZips as $key => $value)
        {
            $this->assertFalse(
                StringUtility::isCityStateZip($value),
                sprintf("'%s' should not be recognized as a 'City, State, Zip' combination", $value)
            );
        }
    }

    /* Tests for removeEmptyLines(). */
    function testRemoveEmptyLines()
    {
        $this->assertIdentical(
            StringUtility::removeEmptyLines(
                "  	\n				\r\n		\r\n	\n                    "
            ),
            ''
        );

        $this->assertIdentical(
            StringUtility::removeEmptyLines(
                "  	\n			Will Buckner	\r\n		\r\n	\n                    "
            ),
            'Will Buckner'
        );

        $this->assertIdentical(
            StringUtility::removeEmptyLines("\n\r\n\r\n\n"),
            ''
        );

        $this->assertNotIdentical(
            StringUtility::removeEmptyLines("\n\ra\n\r\n\n"),
            ''
        );
    }

    /* Tests for countTokens(). */
    function testCountTokens()
    {
        $this->assertIdentical(
            StringUtility::countTokens(',', '1,2,3,4,5'),
            5
        );
        $this->assertIdentical(
            StringUtility::countTokens(' ', '1 2 3 4 5'),
            5
        );
        $this->assertIdentical(
            StringUtility::countTokens(', -/', '1 2-3,4/5'),
            5
        );
        $this->assertIdentical(
            StringUtility::countTokens('*%', '*One%Two**Three%%Four*Five*'),
            5
        );
    }

    /* Tests for tokenize(). */
    function testTokenize()
    {
        $output = array(
            'Zero',
            'One',
            'Two',
            'Three',
            'Four',
            'Five'
        );

        $this->assertIdentical(
            StringUtility::tokenize(', -/', 'Zero  One Two-Three,Four/ Five'),
            $output
        );
        $this->assertIdentical(
            StringUtility::tokenize(', ', 'Zero, One, Two, Three, Four, Five'),
            $output
        );
        $this->assertIdentical(
            StringUtility::tokenize('!', 'Zero!!!!!!One!Two!Three!!Four!Five'),
            $output
        );
        $this->assertIdentical(
            StringUtility::tokenize('*%', '*Zero*One%Two**Three%%Four*Five*'),
            $output
        );

        $this->assertIdentical(
            StringUtility::tokenize('*%', 'Test'),
            array('Test')
        );
    }

    /* Tests for makeInitialName(). */
    function testMakeFirstInitialName()
    {
        $this->assertIdentical(
            StringUtility::makeInitialName('Michael', 'Zimmermann', true),
            'Zimmermann, M.'
        );
        $this->assertIdentical(
            StringUtility::makeInitialName('Michael', 'Zimmermann', true, 50),
            'Zimmermann, M.'
        );
        $this->assertIdentical(
            StringUtility::makeInitialName('Michael', 'Zimmermann', true, 10),
            'Zimmermann, M.'
        );
        $this->assertIdentical(
            StringUtility::makeInitialName('Michael', 'Zimmermann',  true, 9),
            'Zimmerman, M.'
        );
        $this->assertIdentical(
            StringUtility::makeInitialName('Michael', 'Zimmermann',  true, 1),
            'Z, M.'
        );
    }

    /* Tests for escapeSingleQuotes(). */
    function testEscapeSingleQuotes()
    {
        $this->assertIdentical(
            StringUtility::escapeSingleQuotes('Test'),
            'Test'
        );

        $this->assertIdentical(
            StringUtility::escapeSingleQuotes("'Test'"),
            "\\'Test\\'"
        );

        $this->assertIdentical(
            StringUtility::escapeSingleQuotes("'Test ' String'"),
            "\\'Test \\' String\\'"
        );
    }
}

/* Tests for VCard class. */
class VCardTest extends CATSUnitTestCase
{
    function testVersion()
    {
        $this->assertIdentical(VCard::VCARD_VERSION, '2.1');
    }

    function testVCard1()
    {
        $vCard = new vCard();

        $vCard->setName('Smith', 'John');
        $output = trim($vCard->getVCard());

        $outputLines = explode("\n", $output);
        $outputLines = array_map('trim', $outputLines);

        $this->assertIdentical($outputLines[0], 'BEGIN:VCARD');
        $this->assertIdentical($outputLines[1], 'VERSION:2.1');
        $this->assertIdentical($outputLines[2], 'N:Smith;John;;;');
        $this->assertIdentical($outputLines[3], 'FN:John Smith');

        /* Test revision timestamp. */
        $this->assertPatternIn(
            '/^REV:\d{8}T\d{6}$/',
            $outputLines[4]
        );
        $currentREVNumeric = date('YmdHis');

        $vCardREVNumeric = preg_replace('/REV:|T/', '', $outputLines[4]);

        $this->assertTrue(
            $vCardREVNumeric >= ($currentREVNumeric - 5) &&
            $vCardREVNumeric <= ($currentREVNumeric + 5),
            'REV is within +/-5 seconds of current timestamp'
        );

        $this->assertIdentical($outputLines[5], 'MAILER:CATS');
        $this->assertIdentical($outputLines[6], 'END:VCARD');

        $this->assertIdentical($vCard->getFilename(), 'John Smith.vcf');
    }

    function testVCard2()
    {
        $vCard = new vCard();

        $vCard->setOrganization('Testing, Inc.');
        $vCard->setName('Smith', 'John', 'J.', 'Mr.', 'Jr.');
        $vCard->setEmail('test@testerson.org');
        $vCard->setPhoneNumber('612-555-3000', 'CELL');
        $vCard->setTitle('Senior Tester');
        $vCard->setAddress(
            '555 Testing Dr',
            'Suite 100',
            'Testertown',
            'TN',
            '12345',
            '',
            'USA',
            '',
            'HOME'
        );
        $vCard->setNote('Test note.');
        $vCard->setURL('http://www.slashdot.org');
        $output = trim($vCard->getVCard());

        $outputLines = explode("\n", $output);
        $outputLines = array_map('trim', $outputLines);

        $this->assertIdentical($outputLines[0], 'BEGIN:VCARD');
        $this->assertIdentical($outputLines[1], 'VERSION:2.1');
        $this->assertIdentical($outputLines[2], 'ORG;ENCODING=QUOTED-PRINTABLE:Testing, Inc.');
        $this->assertIdentical($outputLines[3], 'N:Smith;John;J.;Mr.;Jr.');
        $this->assertIdentical($outputLines[4], 'FN:Mr. John J. Smith Jr.');
        $this->assertIdentical($outputLines[5], 'EMAIL;INTERNET:test@testerson.org');
        $this->assertIdentical($outputLines[6], 'TEL;CELL:612-555-3000');
        $this->assertIdentical($outputLines[7], 'TITLE;ENCODING=QUOTED-PRINTABLE:Senior Tester');
        $this->assertIdentical($outputLines[8], 'ADR;HOME;ENCODING=QUOTED-PRINTABLE:;Suite 100;555 Testing Dr;Testertown;TN;12345;USA');
        $this->assertIdentical($outputLines[9], 'ORG;ENCODING=QUOTED-PRINTABLE:Test note.');
        $this->assertIdentical($outputLines[10], 'URL:http://www.slashdot.org');

        /* Test revision timestamp. */
        $this->assertPatternIn(
            '/^REV:\d{8}T\d{6}$/',
            $outputLines[11]
        );
        $currentREVNumeric = date('YmdHis');

        $vCardREVNumeric = preg_replace('/REV:|T/', '', $outputLines[11]);

        $this->assertTrue(
            $vCardREVNumeric >= ($currentREVNumeric - 5) &&
            $vCardREVNumeric <= ($currentREVNumeric + 5),
            'REV is within +/-5 seconds of current timestamp'
        );

        $this->assertIdentical($outputLines[12], 'MAILER:CATS');
        $this->assertIdentical($outputLines[13], 'END:VCARD');

        $this->assertIdentical($vCard->getFilename(), 'John Smith.vcf');
    }
}

?>
