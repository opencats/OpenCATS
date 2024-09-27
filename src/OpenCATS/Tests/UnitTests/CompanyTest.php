<?php

use OpenCATS\Entity\Company;
use PHPUnit\Framework\TestCase;

class CompanyTest extends TestCase
{
    public const COMPANY_NAME = "Test Company Name";
    public const SITE_ID = -1;
    public const ADDRESS = "O'Higgins 123";
    public const CITY = "Colonia";
    public const STATE = "Maldonado";
    public const ZIP_CODE = "31337";
    public const PHONE_NUMBER_ONE = "+53 123 45678";
    public const PHONE_NUMBER_TWO = "+53 987 65432";
    public const FAX_NUMBER = '+53 123 65432';
    public const URL = 'http://www.testcompany.com/';
    public const KEY_TECHNOLOGIES = 'PHP and Javascript';
    public const IS_HOT = 1;
    public const NOTES = "This is a note";
    public const ENTERED_BY = 1; // USER ID
    public const OWNER = 1; // USER ID
    private $company;

    protected function setUp(): void
    {
        $this->company = new Company(self::SITE_ID, self::COMPANY_NAME);
    }

    protected function tearDown(): void
    {
        $this->company = null;
    }

    public function test_Company_CreateWithNameAndSiteId_HasNameAndSiteIdSet()
    {
        $this->assertEquals(self::SITE_ID, $this->company->getSiteId());
        $this->assertEquals(self::COMPANY_NAME, $this->company->getName());
    }

    public function test_Company_SetAddress_CompanyInstanceHasAddress()
    {
        $this->company->setAddress(self::ADDRESS);
        $this->assertEquals(self::ADDRESS, $this->company->getAddress());
    }

    public function test_Company_SetCity_CompanyInstanceHasCity()
    {
        $this->company->setCity(self::CITY);
        $this->assertEquals(self::CITY, $this->company->getCity());
    }

    public function test_Company_SetState_CompanyInstanceHasState()
    {
        $this->company->setState(self::STATE);
        $this->assertEquals(self::STATE, $this->company->getState());
    }

    public function test_Company_SetZip_CompanyInstanceHasZip()
    {
        $this->company->setZipCode(self::ZIP_CODE);
        $this->assertEquals(self::ZIP_CODE, $this->company->getZipCode());
    }

    public function test_Company_SetPhoneNumberOne_CompanyInstanceHasPhoneNumberOne()
    {
        $this->company->setPhoneNumberOne(self::PHONE_NUMBER_ONE);
        $this->assertEquals(self::PHONE_NUMBER_ONE, $this->company->getPhoneNumberOne());
    }

    public function test_Company_SetPhoneNumberTwo_CompanyInstanceHasPhoneNumberTwo()
    {
        $this->company->setPhoneNumberTwo(self::PHONE_NUMBER_TWO);
        $this->assertEquals(self::PHONE_NUMBER_TWO, $this->company->getPhoneNumberTwo());
    }

    public function test_Company_SetFaxNumber_CompanyInstanceHasFaxNumber()
    {
        $this->company->setFaxNumber(self::FAX_NUMBER);
        $this->assertEquals(self::FAX_NUMBER, $this->company->getFaxNumber());
    }

    public function test_Company_SetUrl_CompanyInstanceHasUrl()
    {
        $this->company->setUrl(self::URL);
        $this->assertEquals(self::URL, $this->company->getUrl());
    }

    public function test_Company_SetKeyTechnologies_CompanyInstanceHasKeyTechnologies()
    {
        $this->company->setKeyTechnologies(self::KEY_TECHNOLOGIES);
        $this->assertEquals(self::KEY_TECHNOLOGIES, $this->company->getKeyTechnologies());
    }

    public function test_Company_SetIsHot_CompanyInstanceIsHot()
    {
        $this->company->setIsHot(self::IS_HOT);
        $this->assertEquals(self::IS_HOT, $this->company->isHot());
    }

    public function test_Company_SetNotes_CompanyInstanceHasNotes()
    {
        $this->company->setNotes(self::NOTES);
        $this->assertEquals(self::NOTES, $this->company->getNotes());
    }

    public function test_Company_SetEnteredBy_CompanyInstanceHasEnteredBy()
    {
        $this->company->setEnteredBy(self::ENTERED_BY);
        $this->assertEquals(self::ENTERED_BY, $this->company->getEnteredBy());
    }

    public function test_Company_SetOwner_CompanyInstanceHasOwner()
    {
        $this->company->setOwner(self::OWNER);
        $this->assertEquals(self::OWNER, $this->company->getOwner());
    }
}
