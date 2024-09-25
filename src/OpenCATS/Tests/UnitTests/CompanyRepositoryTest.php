<?php

namespace OpenCATS\Tests\UnitTests;

use OpenCATS\Entity\Company;
use OpenCATS\Entity\CompanyRepository;
use PHPUnit\Framework\TestCase;

if (! defined('LEGACY_ROOT')) {
    define('LEGACY_ROOT', '.');
}

include_once(LEGACY_ROOT . '/lib/History.php');

class CompanyRepositoryTests extends TestCase
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

    public const COMPANY_ID = 1;

    public function test_persist_CreatesNewCompany_InputValuesAreEscaped()
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

    public function test_persist_CreateNewCompany_ExecutesSqlQuery()
    {
        $databaseConnectionMock = $this->getDatabaseConnectionMock();
        $databaseConnectionMock->expects($this->exactly(1))
            ->method('query')
            ->willReturn(true);
        $historyMock = $this->getHistoryMock();
        $CompanyRepository = new CompanyRepository($databaseConnectionMock);
        $CompanyRepository->persist($this->createCompany(), $historyMock);
    }

    public function test_persist_CreateNewCompany_StoresHistoryWithCompanyId()
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
    public function test_persist_FailToCreateNewCompany_ThrowsException()
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
