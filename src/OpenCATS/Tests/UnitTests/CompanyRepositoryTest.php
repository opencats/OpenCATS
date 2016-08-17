<?php
namespace OpenCATS\Tests\UnitTests;
use PHPUnit\Framework\TestCase;
use OpenCATS\Entity\CompanyRepository;
use OpenCATS\Entity\Company;
include_once('./lib/History.php');

class CompanyRepositoryTests extends TestCase
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
    const COMPANY_ID = 1;
    
    function test_persist_CreatesNewCompany_InputValuesAreEscaped()
    {
        $databaseConnectionMock = $this->getDatabaseConnectionMock();
        $databaseConnectionMock->expects($this->exactly(11))
            ->method('makeQueryString')
            ->withConsecutive(
                [$this->equalTo(self::COMPANY_NAME)],
                [$this->equalTo(self::ADDRESS)],
                [$this->equalTo(self::CITY)],
                [$this->equalTo(self::STATE)],
                [$this->equalTo(self::ZIP_CODE)],
                [$this->equalTo(self::PHONE_NUMBER_ONE)],
                [$this->equalTo(self::PHONE_NUMBER_TWO)],
                [$this->equalTo(self::FAX_NUMBER)],
                [$this->equalTo(self::URL)],
                [$this->equalTo(self::KEY_TECHNOLOGIES)],
                [$this->equalTo(self::NOTES)]
            );
        $databaseConnectionMock->expects($this->exactly(2))
            ->method('makeQueryInteger')
            ->withConsecutive(
                [$this->equalTo(self::ENTERED_BY)],
                [$this->equalTo(self::OWNER)]
            );
        $databaseConnectionMock->method('query')
            ->willReturn(true);
        $databaseConnectionMock->method('getLastInsertID')
            ->willReturn(self::COMPANY_ID);
        $historyMock = $this->getHistoryMock();
        $CompanyRepository = new CompanyRepository($databaseConnectionMock);
        $CompanyRepository->persist($this->createCompany(), $historyMock);
    }
    
    function test_persist_CreateNewCompany_ExecutesSqlQuery()
    {
        $databaseConnectionMock = $this->getDatabaseConnectionMock();
        $databaseConnectionMock->expects($this->exactly(1))
            ->method('query')
            ->willReturn(true);
        $historyMock = $this->getHistoryMock();
        $CompanyRepository = new CompanyRepository($databaseConnectionMock);
        $CompanyRepository->persist($this->createCompany(), $historyMock);
    }
    
    function test_persist_CreateNewCompany_StoresHistoryWithCompanyId()
    {
        $databaseConnectionMock = $this->getDatabaseConnectionMock();
        $databaseConnectionMock->method('query')
            ->willReturn(true);
        $databaseConnectionMock->method('getLastInsertID')
            ->willReturn(self::COMPANY_ID);
        $historyMock = $this->getHistoryMock();
        $historyMock->expects($this->exactly(1))
            ->method('storeHistoryNew')
            ->withConsecutive(
                [DATA_ITEM_COMPANY, self::COMPANY_ID]
            );
        $CompanyRepository = new CompanyRepository($databaseConnectionMock);
        $CompanyRepository->persist($this->createCompany(), $historyMock);
    }
    
    /**
     * @expectedException OpenCATS\Entity\CompanyRepositoryException
     */
    function test_persist_FailToCreateNewCompany_ThrowsException()
    {
        $databaseConnectionMock = $this->getDatabaseConnectionMock();
        $databaseConnectionMock->method('query')
            ->willReturn(false);
        $historyMock = $this->getHistoryMock();
        $CompanyRepository = new CompanyRepository($databaseConnectionMock);
        $CompanyRepository->persist($this->createCompany(), $historyMock);
    }
    
    private function getHistoryMock()
    {
        return $this->createMock(\History::class);
    }
    
    private function getDatabaseConnectionMock()
    {
        return $this->getMockBuilder('\DatabaseConnection')
            ->setMethods(['makeQueryString', 'makeQueryInteger', 'query', 'getLastInsertID'])
            ->getMock();
    }
    
    private function createCompany()
    {
        return Company::create(
            self::SITE_ID,
            self::COMPANY_NAME,
            self::ADDRESS,
            self::CITY,
            self::STATE,
            self::ZIP_CODE,
            self::PHONE_NUMBER_ONE, 
            self::PHONE_NUMBER_TWO,
            self::FAX_NUMBER, 
            self::URL,
            self::KEY_TECHNOLOGIES,
            self::IS_HOT,
            self::NOTES,
            self::ENTERED_BY,
            self::OWNER
        );
    }
}