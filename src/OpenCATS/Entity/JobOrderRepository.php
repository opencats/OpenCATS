<?php
namespace OpenCATS\Entity;
use OpenCATS\Entity\JobOrder;
use OpenCATS\Entity\JobOrderRepositoryException;

include_once('./lib/History.php');

// FIXME: It's way too similar to CompanyRepository
// Remove duplicated code 
class JobOrderRepository
{
    private $databaseConnection;
    
    function __construct(\DatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }
    
    function persist(JobOrder $jobOrder, \History $history)
    {
        // FIXME: Is the OrNULL usage below correct? Can these fields be NULL?
        $sql = sprintf(
            "INSERT INTO joborder (
                title,
                client_job_id,
                company_id,
                contact_id,
                description,
                notes,
                duration,
                rate_max,
                type,
                is_hot,
                public,
                openings,
                openings_available,
                salary,
                city,
                state,
                company_department_id,
                start_date,
                entered_by,
                recruiter,
                owner,
                site_id,
                date_created,
                date_modified,
                questionnaire_id
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
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                NOW(),
                NOW(),
                %s
            )",
            $this->databaseConnection->makeQueryString($jobOrder->getTitle()),
            $this->databaseConnection->makeQueryString($jobOrder->getCompanyJobId()),
            $this->databaseConnection->makeQueryInteger($jobOrder->getCompanyId()),
            $this->databaseConnection->makeQueryInteger($jobOrder->getContactId()),
            $this->databaseConnection->makeQueryString($jobOrder->getDescription()),
            $this->databaseConnection->makeQueryString($jobOrder->getNotes()),
            $this->databaseConnection->makeQueryString($jobOrder->getDuration()),
            $this->databaseConnection->makeQueryString($jobOrder->getMaxRate()),
            $this->databaseConnection->makeQueryString($jobOrder->getType()),
            ($jobOrder->isHot() ? '1' : '0'),
            ($jobOrder->isPublic() ? '1' : '0'),
            $this->databaseConnection->makeQueryInteger($jobOrder->getOpenings()),
            $this->databaseConnection->makeQueryInteger($jobOrder->getAvailableOpenings()),
            $this->databaseConnection->makeQueryString($jobOrder->getSalary()),
            $this->databaseConnection->makeQueryString($jobOrder->getCity()),
            $this->databaseConnection->makeQueryString($jobOrder->getState()),
            $this->databaseConnection->makeQueryInteger($jobOrder->getDepartmentId()),
            $this->databaseConnection->makeQueryStringOrNULL($jobOrder->getStartDate()),
            $this->databaseConnection->makeQueryInteger($jobOrder->getEnteredBy()),
            $this->databaseConnection->makeQueryInteger($jobOrder->getRecruiter()),
            $this->databaseConnection->makeQueryInteger($jobOrder->getOwner()),
            $jobOrder->getSiteId(),
            // Questionnaire ID or NULL if none
            $jobOrder->getQuestionnaire() !== false ? $this->databaseConnection->makeQueryInteger($jobOrder->getQuestionnaire()) : 'NULL'
        );
        if ($result = $this->databaseConnection->query($sql)) {
            $jobOrderId = $this->databaseConnection->getLastInsertID();
            // FIXME: History should be split in HistoryService and History (Entity)
            // Also, the action of saving a history should not be explicitely done
            // by each Entity Service, but instead, each Entity Service should
            // dispatch a hook and the History Service should listen to all
            // hooks and persist the History entities.
            // That way, the code is more mantainable as not all Entities need to
            // be aware of History and vice-versa
            $history->storeHistoryNew(DATA_ITEM_JOBORDER, $jobOrderId);
            return $jobOrderId;
        } else {
            throw new JobOrderRepositoryException('errorPersistingJobOrder');
        }
    }
}