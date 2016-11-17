<?php
namespace OpenCATS\Tests\UnitTests;
use PHPUnit\Framework\TestCase;
use OpenCATS\Entity\JobOrder;

class JobOrderTest extends TestCase
{
    const JOB_ORDER_TITLE = 'Test job order';
    const COMPANY_ID = 1;
    const CONTACT_ID = 1;
    const JOB_ORDER_DESCRIPTION = 'Some description';
    const JOB_ORDER_NOTES = 'Some note';
    const JOB_ORDER_DURATION_IN_DAYS = 30;
    const JOB_ORDER_MAX_RATE = 60000;
    const JOB_ORDER_TYPE = '';
    const JOB_ORDER_IS_HOT = 1;
    const JOB_ORDER_PUBLIC = 1;
    const JOB_ORDER_OPENINGS = 'Openings';
    const JOB_ORDER_AVAILABLE_OPENINGS = 'Openings';
    const COMPANY_JOB_ID = 10;
    const JOB_ORDER_SALARY = 30000;
    const CITY = 'Colonia';
    const STATE = 'MALDONADO';
    const JOB_ORDER_START_DATE = '2016-05-02';
    const JOB_ORDER_ENTERED_BY = 31337;
    const JOB_ORDER_RECRUITER = 31337;
    const JOB_ORDER_OWNER = null;
    const DEPARTMENT = 'DepartmentOne';
    const DEPARTMENT_ID = 1234;
    const SITE_ID = 1;
    const JOB_ORDER_QUESTIONNAIRE = 'How do you see yourself in 5 years?';
    
    function test_create_CreateAndGetJobOrderTitle_ReturnsName()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::JOB_ORDER_TITLE, $jobOrder->getTitle());
    }
    
    function test_create_CreateAndGetCompanyJobId_ReturnsCompanyJobId()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::COMPANY_JOB_ID, $jobOrder->getCompanyJobId());
    }
    
    function test_create_CreateAndGetCompanyId_ReturnsCompanyId()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::COMPANY_ID, $jobOrder->getCompanyId());
    }
    
    function test_create_CreateAndGetContactId_ReturnsContactId()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::CONTACT_ID, $jobOrder->getContactId());
    }
    
    function test_create_CreateAndGetDescription_ReturnsDescription()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::JOB_ORDER_DESCRIPTION, $jobOrder->getDescription());
    }
    
    function test_create_CreateAndGetNotes_ReturnsNotes()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::JOB_ORDER_NOTES, $jobOrder->getNotes());
    }
    
    function test_create_CreateAndGetDuration_ReturnsDuration()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::JOB_ORDER_DURATION_IN_DAYS, $jobOrder->getDuration());
    }
    
    function test_create_CreateAndGetMaxRate_ReturnsMaxRate()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::JOB_ORDER_MAX_RATE, $jobOrder->getMaxRate());
    }
    
    function test_create_CreateAndGetType_ReturnsType()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::JOB_ORDER_TYPE, $jobOrder->getType());
    }
    
    function test_create_CreateAndGetIsHot_ReturnsIsHot()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::JOB_ORDER_IS_HOT, $jobOrder->isHot());
    }
    
    function test_create_CreateAndGetIsPublic_ReturnsIsPublic()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::JOB_ORDER_PUBLIC, $jobOrder->isPublic());
    }
    
    function test_create_CreateAndGetOpenings_ReturnsOpenings()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::JOB_ORDER_OPENINGS, $jobOrder->getOpenings());
    }
    
    function test_create_CreateAndGetAvailableOpenings_ReturnsAvailableOpenings()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::JOB_ORDER_AVAILABLE_OPENINGS, $jobOrder->getAvailableOpenings());
    }
    
    function test_create_CreateAndGetSalary_ReturnsSalary()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::JOB_ORDER_SALARY, $jobOrder->getSalary());
    }
    
    function test_create_CreateAndGetCity_ReturnsCity()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::CITY, $jobOrder->getCity());
    }
    
    function test_create_CreateAndGetState_ReturnsState()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::STATE, $jobOrder->getState());
    }
    
    function test_create_CreateAndGetDepartmentId_ReturnsDepartmentId()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::DEPARTMENT_ID, $jobOrder->getDepartmentId());
    }
    
    function test_create_CreateAndGetStartDate_ReturnsStartDate()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(null, $jobOrder->getStartDate());
    }
    
    function test_create_CreateAndGetEnteredBy_ReturnsEnteredBy()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::JOB_ORDER_ENTERED_BY, $jobOrder->getEnteredBy());
    }
    
    function test_create_CreateAndGetRecruiter_ReturnsRecruiter()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::JOB_ORDER_RECRUITER, $jobOrder->getRecruiter());
    }
    
    function test_create_CreateAndGetOwner_ReturnsOwner()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::JOB_ORDER_OWNER, $jobOrder->getOwner());
    }
    
    function test_create_CreateAndGetSiteId_ReturnsSiteId()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::SITE_ID, $jobOrder->getSiteId());
    }
    
    function test_create_CreateAndGetQuestionnaireReturnsQuestionnaire()
    {
        $jobOrder = $this->createJobOrder();
        $this->assertEquals(self::JOB_ORDER_QUESTIONNAIRE, $jobOrder->getQuestionnaire());
    }
    
    private function createJobOrder()
    {
        return JobOrder::create(
            self::SITE_ID,
            self::JOB_ORDER_TITLE,
            self::COMPANY_ID,
            self::CONTACT_ID,
            self::JOB_ORDER_DESCRIPTION,
            self::JOB_ORDER_NOTES,
            self::JOB_ORDER_DURATION_IN_DAYS,
            self::JOB_ORDER_MAX_RATE,
            self::JOB_ORDER_TYPE,
            self::JOB_ORDER_IS_HOT,
            self::JOB_ORDER_PUBLIC,
            self::JOB_ORDER_OPENINGS,
            self::COMPANY_JOB_ID,
            self::JOB_ORDER_SALARY,
            self::CITY,
            self::STATE,
            self::JOB_ORDER_START_DATE,
            self::JOB_ORDER_ENTERED_BY,
            self::JOB_ORDER_RECRUITER,
            self::JOB_ORDER_OWNER,
            self::DEPARTMENT_ID,
            self::JOB_ORDER_QUESTIONNAIRE
        );
    }
}