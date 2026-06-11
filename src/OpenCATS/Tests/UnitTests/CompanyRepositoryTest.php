<?php
namespace OpenCATS\Tests\UnitTests;
use PHPUnit\Framework\TestCase;
use OpenCATS\Entity\CompanyRepository;
use OpenCATS\Entity\Company;

include_once(LEGACY_ROOT . '/lib/History.php');
include_once(LEGACY_ROOT . '/lib/DatabaseConnection.php');

class CompanyRepositoryTest extends TestCase
{
    const COMPANY_NAME = "Test Company Name";
    const ADDRESS = "O'Higgins 123";
    const ADDRESS2 = "Apt O'Higgins 4B";
    const CITY = "Colonia";
    const STATE = "Maldonado";
    const ZIP_CODE = "31337";
    const COUNTRY = "US";
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
        $expectedStringValues = [
            self::COMPANY_NAME,
            self::ADDRESS,
            self::ADDRESS2,
            self::CITY,
            self::STATE,
            self::ZIP_CODE,
            self::PHONE_NUMBER_ONE,
            self::PHONE_NUMBER_TWO,
            self::FAX_NUMBER,
            self::URL,
            self::KEY_TECHNOLOGIES,
            self::NOTES
        ];
        $stringCallIndex = 0;
        $databaseConnectionMock->expects($this->exactly(12))
            ->method('makeQueryString')
            ->willReturnCallback(function($value) use ($expectedStringValues, &$stringCallIndex) {
                $this->assertSame($expectedStringValues[$stringCallIndex], $value);
                $stringCallIndex++;
                return "'" . $value . "'";
            });
        $expectedIntegerValues = [
            self::ENTERED_BY,
            self::OWNER
        ];
        $integerCallIndex = 0;
        $databaseConnectionMock->expects($this->exactly(1))
            ->method('makeQueryStringOrNULL')
            ->willReturnCallback(function($value) {
                $this->assertSame(self::COUNTRY, $value);
                return "'" . $value . "'";
            });
        $databaseConnectionMock->expects($this->exactly(2))
            ->method('makeQueryInteger')
            ->willReturnCallback(function($value) use ($expectedIntegerValues, &$integerCallIndex) {
                $this->assertSame($expectedIntegerValues[$integerCallIndex], $value);
                $integerCallIndex++;
                return (string) $value;
            });
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
            ->with(
                $this->equalTo(DATA_ITEM_COMPANY),
                $this->equalTo(self::COMPANY_ID)
            );
        $CompanyRepository = new CompanyRepository($databaseConnectionMock);
        $CompanyRepository->persist($this->createCompany(), $historyMock);
    }
    
    function test_persist_FailToCreateNewCompany_ThrowsException()
    {
        $databaseConnectionMock = $this->getDatabaseConnectionMock();
        $databaseConnectionMock->method('query')
            ->willReturn(false);
        $historyMock = $this->getHistoryMock();
        $CompanyRepository = new CompanyRepository($databaseConnectionMock);
        $this->expectException(\OpenCATS\Entity\CompanyRepositoryException::class);
        $CompanyRepository->persist($this->createCompany(), $historyMock);
    }
    
    private function getHistoryMock()
    {
        return $this->createMock(\History::class);
    }
    
    private function getDatabaseConnectionMock()
    {
        return $this->getMockBuilder('\DatabaseConnection')
            ->disableOriginalConstructor()
            ->onlyMethods(['makeQueryString', 'makeQueryStringOrNULL', 'makeQueryInteger', 'query', 'getLastInsertID'])
            ->getMock();
    }
    
    private function createCompany()
    {
        return Company::create(
            self::COMPANY_NAME,
            self::ADDRESS,
            self::ADDRESS2,
            self::CITY,
            self::STATE,
            self::ZIP_CODE,
            self::COUNTRY,
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
