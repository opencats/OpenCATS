<?php
namespace OpenCATS\Entity;
use OpenCATS\Entity\Company;

include_once('./lib/History.php');

class CompanyRepository
{
    private $databaseConnection;
    
    function __construct(\DatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }
    
    function persist(Company $company, \History $history)
    {
        $sql = sprintf(
            "INSERT INTO company (
                name,
                address,
                city,
                state,
                zip,
                phone1,
                phone2,
                fax_number,
                url,
                key_technologies,
                is_hot,
                notes,
                entered_by,
                owner,
                site_id,
                date_created,
                date_modified
            )
            VALUES (
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                NOW(),
                NOW()
            )",
            $this->databaseConnection->makeQueryString($company->getName()),
            $this->databaseConnection->makeQueryString($company->getAddress()),
            $this->databaseConnection->makeQueryString($company->getCity()),
            $this->databaseConnection->makeQueryString($company->getState()),
            $this->databaseConnection->makeQueryString($company->getZipCode()),
            $this->databaseConnection->makeQueryString($company->getPhoneNumberOne()),
            $this->databaseConnection->makeQueryString($company->getPhoneNumberTwo()),
            $this->databaseConnection->makeQueryString($company->getFaxNumber()),
            $this->databaseConnection->makeQueryString($company->getUrl()),
            $this->databaseConnection->makeQueryString($company->getKeyTechnologies()),
            ($company->isHot() ? '1' : '0'),
            $this->databaseConnection->makeQueryString($company->getNotes()),
            $this->databaseConnection->makeQueryInteger($company->getEnteredBy()),
            $this->databaseConnection->makeQueryInteger($company->getOwner()),
            $company->getSiteId()
        );
        if ($result = $this->databaseConnection->query($sql)) {
            $companyId = $this->databaseConnection->getLastInsertID();
            // FIXME: History should be split in HistoryService and History (Entity)
            // Also, the action of saving a history should not be explicitely done 
            // by each Entity Service, but instead, each Entity Service should 
            // dispatch a hook and the History Service should listen to all 
            // hooks and persist the History entities.
            // That way, the code is more mantainable as not all Entities need to
            // be aware of History and vice-versa
            $history->storeHistoryNew(DATA_ITEM_COMPANY, $companyId);
            return $companyId;
        } else {
            throw new CompanyRepositoryException('errorPersistingCompany');
        }
    }
}