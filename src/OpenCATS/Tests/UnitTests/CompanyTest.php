<?php
use PHPUnit\Framework\TestCase;
use \OpenCATS\Entity\Company;
    
class CompanyTest extends TestCase
{
    const COMPANY_NAME = "Test Company Name";
    const SITE_ID = -1;
    const ADDRESS = "O'Higgins 123";
    const CITY = "Colonia";
    const STATE = "Maldonado";
    const ZIP_CODE = "31337";
    const PHONE_NUMBER_ONE = "+53 123 45678";
    const PHONE_NUMBER_TWO = "+53 987 65432";
    const FAX_NUMBER = '+53 123 65432';
    const URL = 'http://www.testcompany.com/';
    const KEY_TECHNOLOGIES = 'PHP and Javascript';
    const IS_HOT = 1;
    const NOTES = "This is a note";
    const ENTERED_BY = 1; // USER ID
    const OWNER = 1; // USER ID
    
    function setUp()
    {
        $this->company = new Company(self::SITE_ID, self::COMPANY_NAME);
    }
    
    function tearDown()
    {
        $this->company = null;
    }
    
    function test_Company_CreateWithNameAndSiteId_HasNameAndSiteIdSet()
    {
        $this->assertEquals(self::SITE_ID, $this->company->getSiteId());
        $this->assertEquals(self::COMPANY_NAME, $this->company->getName());
    }
    
    function test_Company_SetAddress_CompanyInstanceHasAddress()
    {
        $this->company->setAddress(self::ADDRESS);
        $this->assertEquals(self::ADDRESS, $this->company->getAddress());
    }
    
    function test_Company_SetCity_CompanyInstanceHasCity()
    {
        $this->company->setCity(self::CITY);
        $this->assertEquals(self::CITY, $this->company->getCity());
    }
    
    function test_Company_SetState_CompanyInstanceHasState()
    {
        $this->company->setState(self::STATE);
        $this->assertEquals(self::STATE, $this->company->getState());
    }
    
    function test_Company_SetZip_CompanyInstanceHasZip()
    {
        $this->company->setZipCode(self::ZIP_CODE);
        $this->assertEquals(self::ZIP_CODE, $this->company->getZipCode());
    }
    
    function test_Company_SetPhoneNumberOne_CompanyInstanceHasPhoneNumberOne()
    {
        $this->company->setPhoneNumberOne(self::PHONE_NUMBER_ONE);
        $this->assertEquals(self::PHONE_NUMBER_ONE, $this->company->getPhoneNumberOne());
    }
    
    function test_Company_SetPhoneNumberTwo_CompanyInstanceHasPhoneNumberTwo()
    {
        $this->company->setPhoneNumberTwo(self::PHONE_NUMBER_TWO);
        $this->assertEquals(self::PHONE_NUMBER_TWO, $this->company->getPhoneNumberTwo());
    }
    
    function test_Company_SetFaxNumber_CompanyInstanceHasFaxNumber()
    {
        $this->company->setFaxNumber(self::FAX_NUMBER);
        $this->assertEquals(self::FAX_NUMBER, $this->company->getFaxNumber());
    }
    
    // TODO: URL should be renamed to Website as URL is a technical but a business concept
    function test_Company_SetUrl_CompanyInstanceHasUrl()
    {
        $this->company->setUrl(self::URL);
        $this->assertEquals(self::URL, $this->company->getUrl());
    }
    
    function test_Company_SetKeyTechnologies_CompanyInstanceHasKeyTechnologies()
    {
        $this->company->setKeyTechnologies(self::KEY_TECHNOLOGIES);
        $this->assertEquals(self::KEY_TECHNOLOGIES, $this->company->getKeyTechnologies());
    }
    
    function test_Company_SetIsHot_CompanyInstanceIsHot()
    {
        $this->company->setIsHot(self::IS_HOT);
        $this->assertEquals(self::IS_HOT, $this->company->isHot());
    }
    
    function test_Company_SetNotes_CompanyInstanceHasNotes()
    {
        $this->company->setNotes(self::NOTES);
        $this->assertEquals(self::NOTES, $this->company->getNotes());
    }
    
    // TODO: Rename EnteredBy to EnteredByUser, to make it explicit that's 
    // awaiting for a user id
    function test_Company_SetEnteredBy_CompanyInstanceHasEnteredBy()
    {
        $this->company->setEnteredBy(self::ENTERED_BY);
        $this->assertEquals(self::ENTERED_BY, $this->company->getEnteredBy());
    }
    function test_Company_SetOwner_CompanyInstanceHasOwner()
    {
        $this->company->setOwner(self::OWNER);
        $this->assertEquals(self::OWNER, $this->company->getOwner());
    }
}