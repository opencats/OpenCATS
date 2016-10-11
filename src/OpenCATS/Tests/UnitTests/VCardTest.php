<?php
use PHPUnit\Framework\TestCase;

include_once('./lib/VCard.php');

class VCardTest extends TestCase
{
    function testVersion()
    {
        $this->assertSame(VCard::VCARD_VERSION, '2.1');
    }

    function testVCard1()
    {
        $vCard = new vCard();

        $vCard->setName('Smith', 'John');
        $output = trim($vCard->getVCard());

        $outputLines = explode("\n", $output);
        $outputLines = array_map('trim', $outputLines);

        $this->assertSame($outputLines[0], 'BEGIN:VCARD');
        $this->assertSame($outputLines[1], 'VERSION:2.1');
        $this->assertSame($outputLines[2], 'N:Smith;John;;;');
        $this->assertSame($outputLines[3], 'FN:John Smith');

        /* Test revision timestamp. */
        $this->assertRegExp(
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

        $this->assertSame($outputLines[5], 'MAILER:CATS');
        $this->assertSame($outputLines[6], 'END:VCARD');

        $this->assertSame($vCard->getFilename(), 'John Smith.vcf');
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

        $this->assertSame($outputLines[0], 'BEGIN:VCARD');
        $this->assertSame($outputLines[1], 'VERSION:2.1');
        $this->assertSame($outputLines[2], 'ORG;ENCODING=QUOTED-PRINTABLE:Testing, Inc.');
        $this->assertSame($outputLines[3], 'N:Smith;John;J.;Mr.;Jr.');
        $this->assertSame($outputLines[4], 'FN:Mr. John J. Smith Jr.');
        $this->assertSame($outputLines[5], 'EMAIL;INTERNET:test@testerson.org');
        $this->assertSame($outputLines[6], 'TEL;CELL:612-555-3000');
        $this->assertSame($outputLines[7], 'TITLE;ENCODING=QUOTED-PRINTABLE:Senior Tester');
        $this->assertSame($outputLines[8], 'ADR;HOME;ENCODING=QUOTED-PRINTABLE:;Suite 100;555 Testing Dr;Testertown;TN;12345;USA');
        $this->assertSame($outputLines[9], 'ORG;ENCODING=QUOTED-PRINTABLE:Test note.');
        $this->assertSame($outputLines[10], 'URL:http://www.slashdot.org');

        /* Test revision timestamp. */
        $this->assertRegExp(
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

        $this->assertSame($outputLines[12], 'MAILER:CATS');
        $this->assertSame($outputLines[13], 'END:VCARD');

        $this->assertSame($vCard->getFilename(), 'John Smith.vcf');
    }
}
?>