<?php
namespace OpenCATS\Tests\UnitTests;

use OpenCATS\Entity\Company;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CompanyClassificationTest extends TestCase
{
    public function testIndependentTierAndLifecycleAndNullDefaults(): void
    {
        $company = new Company('Example');
        self::assertNull($company->getCommercialTier());
        self::assertNull($company->getRelationshipStatus());
        foreach (array_merge(array(null), Company::getCommercialTiers()) as $tier)
        {
            foreach (array_merge(array(null), Company::getRelationshipStatuses()) as $status)
            {
                $company->setCommercialTier($tier);
                $company->setRelationshipStatus($status);
                self::assertSame($tier, $company->getCommercialTier());
                self::assertSame($status, $company->getRelationshipStatus());
            }
        }
        $company->setCommercialTier('');
        $company->setRelationshipStatus('');
        self::assertNull($company->getCommercialTier());
        self::assertNull($company->getRelationshipStatus());
    }

    public static function invalidValues(): array
    {
        return array(array(array('A')), array(false), array(1), array('E'), array('a'), array('Unclassified'));
    }

    #[DataProvider('invalidValues')]
    public function testInvalidTierIsRejected($value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Company('Example'))->setCommercialTier($value);
    }

    #[DataProvider('invalidValues')]
    public function testInvalidLifecycleIsRejected($value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Company('Example'))->setRelationshipStatus($value);
    }
}
