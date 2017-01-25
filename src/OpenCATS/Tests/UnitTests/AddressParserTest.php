<?php
use PHPUnit\Framework\TestCase;

include_once('./lib/StringUtility.php');
include_once('./lib/AddressParser.php'); /* Depends on StringUtility. */

class AddressParserTest extends TestCase
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

        $this->assertSame($parsedAddressArray['firstName'], 'Raed-Anne');
        $this->assertSame($parsedAddressArray['middleName'], '');
        $this->assertSame($parsedAddressArray['lastName'], 'Stanford');
        $this->assertSame($parsedAddressArray['addressLineOne'], '110 North Elk Road');
        $this->assertSame($parsedAddressArray['addressLineTwo'], '');
        $this->assertSame($parsedAddressArray['city'], 'Sterling');
        $this->assertSame($parsedAddressArray['state'], 'VA');
        $this->assertSame($parsedAddressArray['zip'], '20164');
        $this->assertSame($parsedAddressArray['email'], 'raed@btjjdtjt.com');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
            );
        $this->assertSame($phoneNumbers['cellPhone'], '743-959-6344');
        $this->assertSame($phoneNumbers['homePhone'], '743-450-3855');
        $this->assertSame($phoneNumbers['workPhone'], '');
        $this->assertSame($phoneNumbers['fax'], '743-450-3333');
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

        $this->assertSame($parsedAddressArray['firstName'], 'Lee');
        $this->assertSame($parsedAddressArray['middleName'], 'Ann');
        $this->assertSame($parsedAddressArray['lastName'], 'Chambers');
        $this->assertSame($parsedAddressArray['addressLineOne'], '9128 Standard Blvd');
        $this->assertSame($parsedAddressArray['addressLineTwo'], '');
        $this->assertSame($parsedAddressArray['city'], 'El Paso');
        $this->assertSame($parsedAddressArray['state'], 'TN');
        $this->assertSame($parsedAddressArray['zip'], '75593');
        $this->assertSame($parsedAddressArray['email'], 'lachambers@yahoooo.com');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
            );
        $this->assertSame($phoneNumbers['cellPhone'], '');
        $this->assertSame($phoneNumbers['homePhone'], '962-398-0687');
        $this->assertSame($phoneNumbers['workPhone'], '');
        $this->assertSame($phoneNumbers['fax'], '');
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

        $this->assertSame($parsedAddressArray['firstName'], 'Michael');
        $this->assertSame($parsedAddressArray['middleName'], 'Nicholas');
        $this->assertSame($parsedAddressArray['lastName'], 'O\'Mercurio');
        $this->assertSame($parsedAddressArray['addressLineOne'], '57830 Decoration Park');
        $this->assertSame($parsedAddressArray['addressLineTwo'], 'Apt. 7');
        $this->assertSame($parsedAddressArray['city'], 'My Really Long City');
        $this->assertSame($parsedAddressArray['state'], 'MI');
        $this->assertSame($parsedAddressArray['zip'], '48048');
        $this->assertSame($parsedAddressArray['email'], 'mikeomercurio@yahooooooooooo.com');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
            );
        $this->assertSame($phoneNumbers['cellPhone'], '586-299-2513');
        $this->assertSame($phoneNumbers['homePhone'], '');
        $this->assertSame($phoneNumbers['workPhone'], '444-300-2929');
        $this->assertSame($phoneNumbers['fax'], '');
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

        $this->assertSame($parsedAddressArray['firstName'], 'Enock');
        $this->assertSame($parsedAddressArray['middleName'], '');
        $this->assertSame($parsedAddressArray['lastName'], 'Chamberlin');
        $this->assertSame($parsedAddressArray['addressLineOne'], '281 Kerby Road Apt # C');
        $this->assertSame($parsedAddressArray['addressLineTwo'], '');
        $this->assertSame($parsedAddressArray['city'], 'Arlington');
        $this->assertSame($parsedAddressArray['state'], 'Texas');
        $this->assertSame($parsedAddressArray['zip'], '79999-5801');
        $this->assertSame($parsedAddressArray['email'], 'enock21@hooooootmail.com');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
            );
        $this->assertSame($phoneNumbers['cellPhone'], '817-715-6875');
        $this->assertSame($phoneNumbers['homePhone'], '817-303-3864');
        $this->assertSame($phoneNumbers['workPhone'], '817-393-3899');
        $this->assertSame($phoneNumbers['fax'], '');
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

        $this->assertSame($parsedAddressArray['firstName'], 'Horacio');
        $this->assertSame($parsedAddressArray['middleName'], '');
        $this->assertSame($parsedAddressArray['lastName'], 'Alfonzo');
        $this->assertSame($parsedAddressArray['addressLineOne'], 'PO BOX 422428');
        $this->assertSame($parsedAddressArray['addressLineTwo'], '');
        $this->assertSame($parsedAddressArray['city'], 'Miami Beach');
        $this->assertSame($parsedAddressArray['state'], 'FL');
        $this->assertSame($parsedAddressArray['zip'], '33541-1234');
        $this->assertSame($parsedAddressArray['email'], 'halfonzo@belleast.net');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
            );
        $this->assertSame($phoneNumbers['cellPhone'], '');
        $this->assertSame($phoneNumbers['homePhone'], '305-777-1222');
        $this->assertSame($phoneNumbers['workPhone'], '');
        $this->assertSame($phoneNumbers['fax'], '');
    }

    function testSampleAddress6()
    {

        $address = <<<EOF
EHhn nunbiv $%&%*$* !!!!!!! I am < wcc@nospammonkeys.org > NOT a valid address! RH%
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_PERSON);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertSame($parsedAddressArray['firstName'], '');
        $this->assertSame($parsedAddressArray['middleName'], '');
        $this->assertSame($parsedAddressArray['lastName'], '');
        $this->assertSame($parsedAddressArray['addressLineOne'], '');
        $this->assertSame($parsedAddressArray['addressLineTwo'], '');
        $this->assertSame($parsedAddressArray['city'], '');
        $this->assertSame($parsedAddressArray['state'], '');
        $this->assertSame($parsedAddressArray['zip'], '');
        $this->assertSame($parsedAddressArray['email'], 'wcc@nospammonkeys.org');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
            );
        $this->assertSame($phoneNumbers['cellPhone'], '');
        $this->assertSame($phoneNumbers['homePhone'], '');
        $this->assertSame($phoneNumbers['workPhone'], '');
        $this->assertSame($phoneNumbers['fax'], '');
    }

    function testSampleAddress7()
    {

        $address = <<<EOF
EHhn nunbiv $%&%*$* !!!!!!! I am NOT a valid address! RH%
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_PERSON);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertSame($parsedAddressArray['firstName'], '');
        $this->assertSame($parsedAddressArray['middleName'], '');
        $this->assertSame($parsedAddressArray['lastName'], '');
        $this->assertSame($parsedAddressArray['addressLineOne'], '');
        $this->assertSame($parsedAddressArray['addressLineTwo'], '');
        $this->assertSame($parsedAddressArray['city'], '');
        $this->assertSame($parsedAddressArray['state'], '');
        $this->assertSame($parsedAddressArray['zip'], '');
        $this->assertSame($parsedAddressArray['email'], '');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
            );
        $this->assertSame($phoneNumbers['cellPhone'], '');
        $this->assertSame($phoneNumbers['homePhone'], '');
        $this->assertSame($phoneNumbers['workPhone'], '');
        $this->assertSame($phoneNumbers['fax'], '');
    }

    function testSampleAddress8()
    {

        $address = <<<EOF
Will G. Buckner
wcc@nospammonkeys.org
EOF;
        $this->addressParser->parse($address, ADDRESSPARSER_MODE_PERSON);
        $parsedAddressArray = $this->addressParser->getAddressArray();

        $this->assertSame($parsedAddressArray['firstName'], 'Will');
        $this->assertSame($parsedAddressArray['middleName'], 'G.');
        $this->assertSame($parsedAddressArray['lastName'], 'Buckner');
        $this->assertSame($parsedAddressArray['addressLineOne'], '');
        $this->assertSame($parsedAddressArray['addressLineTwo'], '');
        $this->assertSame($parsedAddressArray['city'], '');
        $this->assertSame($parsedAddressArray['state'], '');
        $this->assertSame($parsedAddressArray['zip'], '');
        $this->assertSame($parsedAddressArray['email'], 'wcc@nospammonkeys.org');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
            );
        $this->assertSame($phoneNumbers['cellPhone'], '');
        $this->assertSame($phoneNumbers['homePhone'], '');
        $this->assertSame($phoneNumbers['workPhone'], '');
        $this->assertSame($phoneNumbers['fax'], '');
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

        $this->assertSame($parsedAddressArray['firstName'], 'Roger');
        $this->assertSame($parsedAddressArray['middleName'], 'B.');
        $this->assertSame($parsedAddressArray['lastName'], 'Pickler');
        $this->assertSame($parsedAddressArray['addressLineOne'], '2073 Physics Ct. E');
        $this->assertSame($parsedAddressArray['addressLineTwo'], '');
        $this->assertSame($parsedAddressArray['city'], 'Maplewood');
        $this->assertSame($parsedAddressArray['state'], 'MI');
        $this->assertSame($parsedAddressArray['zip'], '55219-5815');
        $this->assertSame($parsedAddressArray['email'], 'rbpicker2@mail.fake.com');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
            );
        $this->assertSame($phoneNumbers['cellPhone'], '641-244-8444');
        $this->assertSame($phoneNumbers['homePhone'], '641-748-2441');
        $this->assertSame($phoneNumbers['workPhone'], '');
        $this->assertSame($phoneNumbers['fax'], '');
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

        $this->assertSame($parsedAddressArray['firstName'], 'Mike');
        $this->assertSame($parsedAddressArray['middleName'], '');
        $this->assertSame($parsedAddressArray['lastName'], 'Jackson');
        $this->assertSame($parsedAddressArray['addressLineOne'], 'PO Box 30205');
        $this->assertSame($parsedAddressArray['addressLineTwo'], '');
        $this->assertSame($parsedAddressArray['city'], 'Salt Lake City');
        $this->assertSame($parsedAddressArray['state'], 'UT');
        $this->assertSame($parsedAddressArray['zip'], '84130-0285');
        $this->assertSame($parsedAddressArray['email'], '');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
            );
        $this->assertSame($phoneNumbers['cellPhone'], '');
        $this->assertSame($phoneNumbers['homePhone'], '');
        $this->assertSame($phoneNumbers['workPhone'], '');
        $this->assertSame($phoneNumbers['fax'], '');
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

        $this->assertSame($parsedAddressArray['firstName'], 'Mike');
        $this->assertSame($parsedAddressArray['middleName'], '');
        $this->assertSame($parsedAddressArray['lastName'], 'Jackson');
        $this->assertSame($parsedAddressArray['addressLineOne'], 'RR 2');
        $this->assertSame($parsedAddressArray['addressLineTwo'], 'Box 101');
        $this->assertSame($parsedAddressArray['city'], 'Salt Lake City');
        $this->assertSame($parsedAddressArray['state'], 'UT');
        $this->assertSame($parsedAddressArray['zip'], '84130-0285');
        $this->assertSame($parsedAddressArray['email'], '');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
            );
        $this->assertSame($phoneNumbers['cellPhone'], '');
        $this->assertSame($phoneNumbers['homePhone'], '');
        $this->assertSame($phoneNumbers['workPhone'], '');
        $this->assertSame($phoneNumbers['fax'], '');
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

        $this->assertSame($parsedAddressArray['firstName'], 'Mike');
        $this->assertSame($parsedAddressArray['middleName'], '');
        $this->assertSame($parsedAddressArray['lastName'], 'Jackson');
        $this->assertSame($parsedAddressArray['addressLineOne'], 'RR 2');
        $this->assertSame($parsedAddressArray['addressLineTwo'], 'Box 101');
        $this->assertSame($parsedAddressArray['city'], 'Salt Lake City');
        $this->assertSame($parsedAddressArray['state'], 'UT');
        $this->assertSame($parsedAddressArray['zip'], '84130');
        $this->assertSame($parsedAddressArray['email'], '');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
            );
        $this->assertSame($phoneNumbers['cellPhone'], '');
        $this->assertSame($phoneNumbers['homePhone'], '');
        $this->assertSame($phoneNumbers['workPhone'], '');
        $this->assertSame($phoneNumbers['fax'], '');
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

        $this->assertSame($parsedAddressArray['firstName'], 'Roger');
        $this->assertSame($parsedAddressArray['middleName'], 'B.');
        $this->assertSame($parsedAddressArray['lastName'], 'Pickler');
        $this->assertSame($parsedAddressArray['addressLineOne'], '2073 Physics Ct. E');
        $this->assertSame($parsedAddressArray['addressLineTwo'], '');
        $this->assertSame($parsedAddressArray['city'], 'Maplewood');
        $this->assertSame($parsedAddressArray['state'], 'MI');
        $this->assertSame($parsedAddressArray['zip'], '55219-5815');
        $this->assertSame($parsedAddressArray['email'], 'rbpicker2@mail.fake.com');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
            );
        $this->assertSame($phoneNumbers['cellPhone'], '');
        $this->assertSame($phoneNumbers['homePhone'], '641-748-2441');
        $this->assertSame($phoneNumbers['workPhone'], '');
        $this->assertSame($phoneNumbers['fax'], '');
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

        $this->assertSame($parsedAddressArray['firstName'], 'Roger');
        $this->assertSame($parsedAddressArray['middleName'], 'B.');
        $this->assertSame($parsedAddressArray['lastName'], 'Pickler');
        $this->assertSame($parsedAddressArray['addressLineOne'], '2073 Physics Ct. E');
        $this->assertSame($parsedAddressArray['addressLineTwo'], '');
        $this->assertSame($parsedAddressArray['city'], 'Maplewood');
        $this->assertSame($parsedAddressArray['state'], 'MI');
        $this->assertSame($parsedAddressArray['zip'], '55219-5815');
        $this->assertSame($parsedAddressArray['email'], 'rbpicker2@mail.fake.com');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
            );
        $this->assertSame($phoneNumbers['cellPhone'], '');
        $this->assertSame($phoneNumbers['homePhone'], '641-748-2441');
        $this->assertSame($phoneNumbers['workPhone'], '');
        $this->assertSame($phoneNumbers['fax'], '555-444-5555');
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

        $this->assertSame($parsedAddressArray['firstName'], 'Roger');
        $this->assertSame($parsedAddressArray['middleName'], 'B.');
        $this->assertSame($parsedAddressArray['lastName'], 'Pickler');
        $this->assertSame($parsedAddressArray['addressLineOne'], '2073 Physics Ct. E');
        $this->assertSame($parsedAddressArray['addressLineTwo'], '');
        $this->assertSame($parsedAddressArray['city'], 'Maplewood');
        $this->assertSame($parsedAddressArray['state'], 'MI');
        $this->assertSame($parsedAddressArray['zip'], '55219-5815');
        $this->assertSame($parsedAddressArray['email'], 'rbpicker2@mail.fake.com');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
            );
        $this->assertSame($phoneNumbers['cellPhone'], '');
        $this->assertSame($phoneNumbers['homePhone'], '641-748-2441');
        $this->assertSame($phoneNumbers['workPhone'], '');
        $this->assertSame($phoneNumbers['fax'], '444-345-4444');
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

        $this->assertSame($parsedAddressArray['firstName'], '');
        $this->assertSame($parsedAddressArray['middleName'], '');
        $this->assertSame($parsedAddressArray['lastName'], '');
        $this->assertSame($parsedAddressArray['company'], 'CompuServe, Inc.');
        $this->assertSame($parsedAddressArray['addressLineOne'], '5000 Arlington Centre Boulevard');
        $this->assertSame($parsedAddressArray['addressLineTwo'], 'P.O. Box 20212');
        $this->assertSame($parsedAddressArray['city'], 'Columbus');
        $this->assertSame($parsedAddressArray['state'], 'OH');
        $this->assertSame($parsedAddressArray['zip'], '43220');
        $this->assertSame($parsedAddressArray['email'], '');


        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
            );
        $this->assertSame($phoneNumbers['cellPhone'], '');
        $this->assertSame($phoneNumbers['homePhone'], '');
        $this->assertSame($phoneNumbers['workPhone'], '');
        $this->assertSame($phoneNumbers['fax'], '614-444-4555');
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

        $this->assertSame($parsedAddressArray['firstName'], '');
        $this->assertSame($parsedAddressArray['middleName'], '');
        $this->assertSame($parsedAddressArray['lastName'], '');
        $this->assertSame($parsedAddressArray['company'], 'Graphical Brass Interfaces, Inc.');
        $this->assertSame($parsedAddressArray['addressLineOne'], 'R.R. 1, Box 210A');
        $this->assertSame($parsedAddressArray['addressLineTwo'], '');
        $this->assertSame($parsedAddressArray['city'], 'Monticello');
        $this->assertSame($parsedAddressArray['state'], 'IN');
        $this->assertSame($parsedAddressArray['zip'], '47960');
        $this->assertSame($parsedAddressArray['email'], '');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
            );
        $this->assertSame($phoneNumbers['cellPhone'], '');
        $this->assertSame($phoneNumbers['homePhone'], '');
        $this->assertSame($phoneNumbers['workPhone'], '');
        $this->assertSame($phoneNumbers['fax'], '');
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

        $this->assertSame($parsedAddressArray['firstName'], '');
        $this->assertSame($parsedAddressArray['middleName'], '');
        $this->assertSame($parsedAddressArray['lastName'], '');
        $this->assertSame($parsedAddressArray['company'], '1st Tech Systems, Inc.');
        $this->assertSame($parsedAddressArray['addressLineOne'], '53-54 Cape Road');
        $this->assertSame($parsedAddressArray['addressLineTwo'], '');
        $this->assertSame($parsedAddressArray['city'], 'Mendon');
        $this->assertSame($parsedAddressArray['state'], 'MA');
        $this->assertSame($parsedAddressArray['zip'], '01756');
        $this->assertSame($parsedAddressArray['email'], 'info@firsttechsystemsinc.com');

        /* Test phone numbers. */
        $phoneNumbers = $this->makePhoneNumberArray(
            $parsedAddressArray['phoneNumbers']
            );
        $this->assertSame($phoneNumbers['cellPhone'], '');
        $this->assertSame($phoneNumbers['homePhone'], '');
        $this->assertSame($phoneNumbers['workPhone'], '');
        $this->assertSame($phoneNumbers['fax'], '');
    }
}
?>