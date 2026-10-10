<?php
include_once(LEGACY_ROOT . '/vendor/autoload.php');
use OpenCATS\Entity\Company;
use OpenCATS\Entity\CompanyRepository;


/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

include_once(LEGACY_ROOT . '/lib/Pager.php');
include_once(LEGACY_ROOT . '/lib/CompanySettings.php');
include_once(LEGACY_ROOT . '/lib/ListEditor.php');
include_once(LEGACY_ROOT . '/lib/EmailTemplates.php');
include_once(LEGACY_ROOT . '/lib/Attachments.php');
include_once(LEGACY_ROOT . '/lib/JobOrders.php');
include_once(LEGACY_ROOT . '/lib/Contacts.php');


/**
 *  Companies Library
 *  @package    CATS
 *  @subpackage Library
 */
class Companies
{
    private $_db;

    public $extraFields;


    public function __construct()
    {
        $this->_db = DatabaseConnection::getInstance();
        $this->extraFields = new ExtraFields(DATA_ITEM_COMPANY);
    }

    /**
     * Current user's relationship responsibilities, independent of Task assignment.
     * No cross-owner/unassigned queue is implied by this API. Results are derived
     * on every call; callers must escape labels and date-format ISO values.
     */
    public function getRelationshipAttention()
    {
        if (!isset($_SESSION['CATS']) || !$_SESSION['CATS']->isLoggedIn() ||
            $_SESSION['CATS']->getUserID() <= 0) return array();
        // Do not turn an inaccessible source into a false "missing" warning.
        foreach (array('companies.show', 'contacts.show', 'tasks.list', 'tasks.show',
            'activity.listByViewDataGrid') as $action)
            if ($_SESSION['CATS']->getAccessLevel($action) < ACCESS_LEVEL_READ) return array();

        include_once(LEGACY_ROOT . '/lib/Tasks.php');
        include_once(LEGACY_ROOT . '/lib/ActivityEntries.php');
        $settings = new CompanySettings();
        $policy = $settings->getAttention();
        $labels = $settings->getAll();
        $companies = $this->_db->getAllAssoc('SELECT company.company_id AS companyID,
            company.name, company.owner, company.commercial_tier AS commercialTier,
            company.relationship_status AS relationshipStatus FROM company
            INNER JOIN user ON user.user_id = company.owner
            WHERE company.owner = ' . (int) $_SESSION['CATS']->getUserID() .
            ' AND user.access_level > ' . ACCESS_LEVEL_DISABLED);
        $eligible = array();
        foreach ($companies as $company)
        {
            $tier = $company['commercialTier'] ?? 'Unclassified';
            $status = $company['relationshipStatus'] ?? 'Unclassified';
            if (empty($policy['tiers'][$tier]['enabled']) || empty($policy['lifecycles'][$status])) continue;
            $company['reviewDays'] = $policy['tiers'][$tier]['days'];
            $company['tierLabel'] = CompanySettings::formatTier($company['commercialTier'], $labels);
            $eligible[$company['companyID']] = $company;
        }
        if (!$eligible) return array();
        $ids = array_keys($eligible);
        $contacts = $this->_db->getAllAssoc('SELECT contact_id AS contactID, company_id AS companyID
            FROM contact WHERE left_company = 0 AND company_id IN (' . implode(',', $ids) . ')');
        $contactCompanies = array_column($contacts, 'companyID', 'contactID');
        // One SQL-filtered collection; existing Tasks::canRead also checks pipeline context.
        $tasks = (new Tasks())->getAll(array('relationshipCompanyIDs' => $ids,
            'openOnly' => true, 'purpose' => 'relationship_follow_up'));
        $byCompany = array();
        foreach ($tasks as $task)
        {
            $companyID = $task['dataItemType'] == DATA_ITEM_COMPANY ? $task['dataItemID'] :
                ($contactCompanies[$task['dataItemID']] ?? null);
            if ($companyID !== null && isset($eligible[$companyID])) $byCompany[$companyID][] = $task;
        }
        $lastContacts = (new ActivityEntries())->getRelationshipContactDates($ids);
        $today = DateUtility::getAdjustedDate('Y-m-d');
        $todayDate = new DateTimeImmutable($today);
        $results = array();
        foreach ($eligible as $id => $company)
        {
            $end = $todayDate->modify('+' . $company['reviewDays'] . ' days')->format('Y-m-d');
            $reasons = array();
            $dueDates = array();
            $commitments = array();
            foreach ($byCompany[$id] ?? array() as $task)
            {
                $due = $task['dueDate'];
                if (!is_string($due) || !preg_match('/^([1-9][0-9]{3})-([0-9]{2})-([0-9]{2})$/D', $due, $parts) ||
                    !checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1]) || $due > $end) continue;
                $reason = $due < $today ? 'overdue_task' : ($due === $today ? 'due_today' : 'upcoming');
                $reasons[$reason] = true;
                if (!isset($dueDates[$reason]) || $due < $dueDates[$reason]) $dueDates[$reason] = $due;
                $commitments[] = array('taskID' => $task['taskID'], 'title' => $task['title'],
                    'dueDate' => $due, 'assignedTo' => $task['assignedTo']);
            }
            if (!$commitments) $reasons['no_next_action'] = true;
            $lastContact = $lastContacts[$id] ?? null;
            $contactDate = $lastContact === null ? null : DateUtility::getAdjustedDate('Y-m-d', strtotime($lastContact));
            $age = $contactDate === null ? null : (int) (new DateTimeImmutable($contactDate))->diff($todayDate)->format('%r%a');
            if ($age === null || $age > $company['reviewDays']) $reasons['stale_relationship'] = true;
            $ordered = array();
            foreach (array('overdue_task', 'due_today', 'no_next_action', 'stale_relationship', 'upcoming') as $reason)
                if (isset($reasons[$reason])) $ordered[] = $reason;
            if (!$ordered) continue;
            $company['primaryReason'] = $ordered[0];
            $company['reasons'] = $ordered;
            $company['lastContact'] = $lastContact;
            $company['contactAgeDays'] = $age;
            $company['contactLabel'] = $lastContact === null ? 'No recorded contact' : 'Recorded conversation or meeting';
            $company['contactEvidenceScope'] = 'Explicit Company-linked Activities only; Contact history attribution is unverified.';
            $company['tasks'] = $commitments;
            // Unknown contact age sorts before known dates; it is never assigned an age.
            $company['sortDate'] = $dueDates[$ordered[0]] ?? $contactDate ?? '';
            $results[] = $company;
        }
        $rank = array_flip(array('overdue_task', 'due_today', 'no_next_action', 'stale_relationship', 'upcoming'));
        $tiers = array_flip(array('A', 'B', 'C', 'D', 'Unclassified'));
        usort($results, function ($a, $b) use ($rank, $tiers) {
            return array($rank[$a['primaryReason']], $tiers[$a['commercialTier'] ?? 'Unclassified'], $a['sortDate'], (int) $a['companyID']) <=>
                array($rank[$b['primaryReason']], $tiers[$b['commercialTier'] ?? 'Unclassified'], $b['sortDate'], (int) $b['companyID']);
        });
        return $results;
    }


    /**
     * Adds a company to the database and returns its company ID.
     *
     * @param string Name
     * @param string Address line 1
     * @param string Address line 2
     * @param string City
     * @param string State
     * @param string Zip code
     * @param string Phone 1
     * @param string Phone 2
     * @param string Url
     * @param string Key technologies
     * @param boolean Is company hot
     * @param string Company notes
     * @param integer Entered-by user
     * @param integer Owner user
     * @param string $commercialTier A/B/C/D or null for Unclassified
     * @param string $relationshipStatus Accepted lifecycle value or null
     * @return new Company ID, or -1 on failure.
     */
    public function add($name, $address, $address2, $city, $state, $zip, $phone1,
                        $phone2, $faxNumber, $url, $keyTechnologies, $isHot,
                        $notes, $enteredBy, $owner, $country = '',
                        $commercialTier = null, $relationshipStatus = null)
    {
        $company= Company::create(
            $name,
            $address,
            $address2,
            $city,
            $state,
            $zip,
            $country,
            $phone1,
            $phone2,
            $faxNumber,
            $url,
            $keyTechnologies,
            $isHot,
            $notes,
            $enteredBy,
            $owner,
            $commercialTier,
            $relationshipStatus
        );
        $CompanyRepository = new CompanyRepository($this->_db);
        try {
            $companyId = $CompanyRepository->persist($company, new History());
        } catch(CompanyRepositoryException $e) {
            return -1;
        }
        return $companyId;
    }

    /**
     * Updates a company.
     *
     * @param integer Company ID
     * @param string Name
     * @param string Address line 1
     * @param string Address line 2
     * @param string City
     * @param string State
     * @param string Zip Code
     * @param string Phone 1
     * @param string Phone 2
     * @param string URL
     * @param string Key Technologies
     * @param boolean Is company hot
     * @param string Company notes
     * @param integer Owner user
     * @param integer Billing contact ID
     * @param string|false|null $commercialTier false preserves the current value; null clears it
     * @param string|false|null $relationshipStatus false preserves the current value; null clears it
     * @return boolean True if successful; false otherwise.
     */
    public function update($companyID, $name, $address, $address2, $city, $state,
                           $zip, $phone1, $phone2, $faxNumber, $url,
                           $keyTechnologies, $isHot, $notes, $owner,
                           $billingContact, $email, $emailAddress, $country = false,
                           $commercialTier = false, $relationshipStatus = false)
    {
        /* false means omitted by an existing caller; null explicitly clears. */
        $classificationSQL = '';
        foreach (array('commercial_tier' => $commercialTier,
                       'relationship_status' => $relationshipStatus) as $column => $value)
        {
            if ($value === false)
            {
                continue;
            }
            $allowed = $column === 'commercial_tier'
                ? Company::getCommercialTiers() : Company::getRelationshipStatuses();
            $value = Company::normalizeClassification($value, $allowed);
            $classificationSQL .= $column . ' = ' . $this->_db->makeQueryStringOrNULL($value) . ', ';
        }
        if ((!is_int($companyID) && !is_string($companyID)) ||
            !ctype_digit((string) $companyID) || (int) $companyID <= 0)
        {
            return false;
        }
        $preHistory = $this->get($companyID);
        if (empty($preHistory))
        {
            return false;
        }

        if ($country === false)
        {
            $countrySQL = ",\n";
        }
        else
        {
            $countrySQL = sprintf(
                ",\n                country          = %s,\n",
                $this->_db->makeQueryStringOrNULL($country)
            );
        }

        $sql = sprintf(
            "UPDATE
                company
             SET
                name             = %s,
                address         = %s,
                address2        = %s,
                city             = %s,
                state            = %s,
                zip              = %s%s
                phone1           = %s,
                phone2           = %s,
                fax_number       = %s,
                url              = %s,
                key_technologies = %s,
                is_hot           = %s,
                notes            = %s,
                billing_contact  = %s,
                owner            = %s,
                %sdate_modified    = NOW()
            WHERE
                company_id = %s",
            $this->_db->makeQueryString($name),
            $this->_db->makeQueryString($address),
            $this->_db->makeQueryString($address2),
            $this->_db->makeQueryString($city),
            $this->_db->makeQueryString($state),
            $this->_db->makeQueryString($zip),
            $countrySQL,
            $this->_db->makeQueryString($phone1),
            $this->_db->makeQueryString($phone2),
            $this->_db->makeQueryString($faxNumber),
            $this->_db->makeQueryString($url),
            $this->_db->makeQueryString($keyTechnologies),
            ($isHot ? '1' : '0'),
            $this->_db->makeQueryString($notes),
            $this->_db->makeQueryInteger($billingContact),
            $this->_db->makeQueryInteger($owner),
            $classificationSQL,
            $this->_db->makeQueryInteger($companyID)
        );

        $queryResult = $this->_db->query($sql);
        $postHistory = $this->get($companyID);

        if (!$queryResult)
        {
            return false;
        }

        $history = new History();
        $history->storeHistoryChanges(DATA_ITEM_COMPANY, $companyID, $preHistory, $postHistory);

        if (!empty($emailAddress))
        {
            /* Send e-mail notification. */
            //FIXME: Make subject configurable.
            $mailer = new Mailer();
            $mailerStatus = $mailer->sendToOne(
                array($emailAddress, ''),
                'CATS Notification: Company Ownership Change',
                $email,
                true
            );
        }

        return true;
    }

    /**
     * Removes a company and all associated records from the system.
     *
     * @param integer Company ID
     * @return void
     */
    public function delete($companyID)
    {
        /* Find associated contacts. */
        $sql = sprintf(
            "SELECT
                contact_id AS contactID
            FROM
                contact
            WHERE
                company_id = %s",
            $companyID
        );
        $contactsRS = $this->_db->getAllAssoc($sql);

        /* Find associated job orders. */
        $sql = sprintf(
            "SELECT
                joborder_id AS jobOrderID
            FROM
                joborder
            WHERE
                company_id = %s",
            $companyID
        );
        $jobOrdersRS = $this->_db->getAllAssoc($sql);

        /* Find associated attachments. */
        $attachments = new Attachments();
        $attachmentsRS = $attachments->getAll(
            DATA_ITEM_COMPANY, $companyID
        );

        /* Delete associated contacts. */
        $contacts = new Contacts();
        foreach ($contactsRS as $rowIndex => $row)
        {
            $contacts->delete($row['contactID']);
        }

        /* Delete associated job orders. */
        $jobOrders = new JobOrders();
        foreach ($jobOrdersRS as $rowIndex => $row)
        {
            $jobOrders->delete($row['jobOrderID']);
        }

        /* Delete associated attachments. */
        foreach ($attachmentsRS as $rowNumber => $row)
        {
            $attachments->delete($row['attachmentID']);
        }

        /* Delete company activity entries. */
        $sql = sprintf(
            "DELETE FROM
                activity
            WHERE
                data_item_type = %s
            AND
                data_item_id = %s",
            DATA_ITEM_COMPANY,
            $this->_db->makeQueryInteger($companyID)
        );
        $this->_db->query($sql);

        /* Delete company calendar events. */
        $sql = sprintf(
            "DELETE FROM
                calendar_event
            WHERE
                data_item_type = %s
            AND
                data_item_id = %s",
            DATA_ITEM_COMPANY,
            $this->_db->makeQueryInteger($companyID)
        );
        $this->_db->query($sql);

        /* Delete company departments. */
        $sql = sprintf(
            "DELETE FROM
                company_department
            WHERE
                company_id = %s",
            $this->_db->makeQueryInteger($companyID)
        );
        $this->_db->query($sql);

        /* Delete from saved lists. */
        $sql = sprintf(
            "DELETE FROM
                saved_list_entry
            WHERE
                data_item_id = %s
            AND
                data_item_type = %s",
            $this->_db->makeQueryInteger($companyID),
            DATA_ITEM_COMPANY
        );
        $this->_db->query($sql);

        /* Delete extra fields. */
        $this->extraFields->deleteValueByDataItemID($companyID);

        /* Delete the company. */
        $sql = sprintf(
            "DELETE FROM
                company
            WHERE
                company_id = %s",
            $companyID
        );
        $this->_db->query($sql);

        $history = new History();
        $history->storeHistoryDeleted(DATA_ITEM_COMPANY, $companyID);
    }

    /**
     * Returns all relevent company information for a given company ID.
     *
     * @param integer Company ID
     * @return array Company data
     */
    public function get($companyID)
    {
        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.owner AS owner,
                company.name AS name,
                company.is_hot AS isHot,
                company.commercial_tier AS commercialTier,
                company.relationship_status AS relationshipStatus,
                company.address AS address,
                company.address2 AS address2,
                company.city AS city,
                company.state AS state,
                company.zip AS zip,
                company.country AS country,
                company.phone1 AS phone1,
                company.phone2 AS phone2,
                company.fax_number AS faxNumber,
                company.url AS url,
                company.key_technologies AS keyTechnologies,
                company.notes AS notes,
                company.default_company AS defaultCompany,
                billing_contact.contact_id AS billingContact,
                CONCAT(
                    billing_contact.first_name, ' ', billing_contact.last_name
                ) AS billingContactFullName,
                DATE_FORMAT(
                    company.date_created, '" . DateUtility::getMysqlDateTimeFormat() . "'
                ) AS dateCreated,
                CONCAT(
                    entered_by_user.first_name, ' ', entered_by_user.last_name
                ) AS enteredByFullName,
                CONCAT(
                    owner_user.first_name, ' ', owner_user.last_name
                ) AS ownerFullName,
                owner_user.email AS owner_email
            FROM
                company
            LEFT JOIN user AS entered_by_user
                ON company.entered_by = entered_by_user.user_id
            LEFT JOIN user AS owner_user
                ON company.owner = owner_user.user_id
            LEFT JOIN contact AS billing_contact
                ON company.billing_contact = billing_contact.contact_id
            WHERE
                company.company_id = %s",
            $this->_db->makeQueryInteger($companyID)
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Returns all company information relevent for the Edit Company page for
     * a given company ID.
     *
     * @param integer Company ID
     * @return array Company data
     */
    public function getForEditing($companyID)
    {
        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.owner AS owner,
                company.name AS name,
                company.is_hot AS isHot,
                company.commercial_tier AS commercialTier,
                company.relationship_status AS relationshipStatus,
                company.address AS address,
                company.address2 AS address2,
                company.city AS city,
                company.state AS state,
                company.zip AS zip,
                company.country AS country,
                company.phone1 AS phone1,
                company.phone2 AS phone2,
                company.fax_number AS faxNumber,
                company.url AS url,
                company.key_technologies AS keyTechnologies,
                company.notes AS notes,
                company.default_company AS defaultCompany,
                billing_contact.contact_id AS billingContact
            FROM
                company
            LEFT JOIN contact AS billing_contact
                ON company.billing_contact = billing_contact.contact_id
            WHERE
                company.company_id = %s",
            $this->_db->makeQueryInteger($companyID)
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Used by new site creation code to set a new company as the
     * default company for a site.  The default company can
     * not be deleted, and is referred to as "Internal Postings."
     *
     * @param integer Company ID
     * @return array Company data
     */
    public function setCompanyDefault($companyID)
    {
        $sql = sprintf(
            "UPDATE
                company
             SET
                default_company = 1,
                date_modified  = NOW()
            WHERE
                company_id = %s",
            $this->_db->makeQueryInteger($companyID)
        );

        $preHistory = $this->get($companyID);
        $queryResult = $this->_db->query($sql);
        $postHistory = $this->get($companyID);

        if (!$queryResult)
        {
            return false;
        }

        $history = new History();
        $history->storeHistoryChanges(
            DATA_ITEM_COMPANY, $companyID, $preHistory, $postHistory
        );

        return true;
    }

    /**
     * Returns all relevent company information for a given company ID.
     *
     * @param integer Company ID
     * @return array Company data
     */
    public function getDefaultCompany()
    {
        $sql = sprintf(
            "SELECT
                company.company_id AS companyID
            FROM
                company
            WHERE
                company.default_company = 1"
        );
        $rs = $this->_db->getAssoc($sql);

        if (empty($rs))
        {
            return false;
        }

        return $rs['companyID'];
    }

    /**
     * Returns a minimal record set of all companies (for use when creating
     * drop-down lists of companies, etc.).
     *
     * @return array Companies data
     */
    public function getSelectList()
    {
        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS name
            FROM
                company
            ORDER BY
                company.name ASC"
        );

        return $this->_db->getAllAssoc($sql);
     }

    /**
     * Returns an array of location data (city, state, zip) for the specified
     * company ID.
     *
     * @param integer Company ID
     * @return array Companies data
     */
    public function getLocationArray($companyID)
    {
        $sql = sprintf(
            "SELECT
                company.address AS address,
                company.city AS city,
                company.state AS state,
                company.zip AS zip,
                company.country AS country
            FROM
                company
            WHERE
                company.company_id = %s",
            $this->_db->makeQueryInteger($companyID)
        );

        return $this->_db->getAssoc($sql);
     }

    /**
     * Returns an array of contacts data (contactID, firstName, lastName)
     * for the specified company ID.
     *
     * @param integer Company ID
     * @return array Contacts data
     */
    public function getContactsArray($companyID)
    {
        $sql = sprintf(
            "SELECT
                contact.contact_id AS contactID,
                contact.first_name AS firstName,
                contact.last_name AS lastName
            FROM
                contact
            WHERE
                contact.company_id = %s
            ORDER BY
                contact.last_name ASC,
                contact.first_name ASC",
            $this->_db->makeQueryInteger($companyID)
        );

        return $this->_db->getAllAssoc($sql);
     }

    /**
     * Returns an array of job orders data (jobOrderID, title, companyName)
     * for the specified company ID.
     *
     * @param integer Company ID
     * @return array Job Orders data
     */
    public function getJobOrdersArray($companyID)
    {
        $sql = sprintf(
            "SELECT
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                company.name AS companyName
            FROM
                joborder
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            WHERE
                joborder.company_id = %s
            ORDER BY
                title ASC",
            $this->_db->makeQueryInteger($companyID)
        );

        return $this->_db->getAllAssoc($sql);
     }

    /**
     * Returns a response array of all departments for a company.
     * by getDifferencesFromList (ListEditor.php).
     *
     * @param integer Company ID
     * @return array Departments
     */
    public function getDepartments($companyID)
    {
        $sql = sprintf(
            "SELECT
                company_department.company_department_id AS departmentID,
                company_department.name AS name
            FROM
                company_department
            WHERE
                company_department.company_id = %s
            ORDER BY
                company_department.name ASC",
            $this->_db->makeQueryInteger($companyID)
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Updates a companies departments with an array generated
     * by getDifferencesFromList (ListEditor.php).
     *
     * @param integer Company ID
     * @param array getDifferencesFromList
     * @return void
     */
    public function updateDepartments($companyID, $updates)
    {
        $history = new History();

        foreach ($updates as $update)
        {
            switch ($update[2])
            {
                case LIST_EDITOR_ADD:
                    $sql = sprintf(
                        "INSERT INTO company_department (
                            name,
                            company_id,
                            date_created
                         )
                         VALUES (
                            %s,
                            %s,
                            NOW()
                         )",
                         $this->_db->makeQueryString($update[0]),
                         $this->_db->makeQueryInteger($companyID)
                    );
                    $this->_db->query($sql);

                    $history->storeHistorySimple(
                        DATA_ITEM_COMPANY,
                        0,
                        '(USER) added ' . $update[0] . ' to departments.'
                    );

                    break;

                case LIST_EDITOR_REMOVE:
                    $sql = sprintf(
                        "DELETE FROM
                            company_department
                         WHERE
                            company_department_id = %s",
                         $this->_db->makeQueryInteger($update[1])
                    );
                    $this->_db->query($sql);

                    $history->storeHistorySimple(
                        DATA_ITEM_COMPANY,
                        0,
                        '(USER) removed ' . $update[0] . ' from departments.'
                    );

                    break;

                case LIST_EDITOR_MODIFY:
                    $sql = sprintf(
                        "UPDATE
                            company_department
                         SET
                            name = %s
                         WHERE
                            company_department_id = %s",
                         $this->_db->makeQueryString($update[0]),
                         $this->_db->makeQueryInteger($update[1])
                    );
                    $this->_db->query($sql);

                    $history->storeHistorySimple(
                        DATA_ITEM_COMPANY,
                        0,
                        '(USER) renamed a department to ' . $update[0] . '.'
                    );

                    break;

                default:
                    break;
            }
        }
    }

    /**
     * Returns the company ID of a company name, or -1 if the company does not exist.
     *
     * @param company name
     * @return companyID
     */
    public function companyByName($name)
    {
        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS name
            FROM
                company
            WHERE
                company.name = %s",
            $this->_db->makeQueryStringOrNULL($name)
        );

        $rs = $this->_db->getAssoc($sql);
        if (empty($rs)) {
            return -1;
        }

        return $rs['companyID'];
    }
}


class CompaniesDataGrid extends DataGrid
{
    protected $_commercialTierLabels;
    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($instanceName, $parameters, $misc)
    {
        $this->_db = DatabaseConnection::getInstance();
        $this->_assignedCriterion = "";
        $this->_dataItemIDColumn = 'company.company_id';

        $this->_commercialTierLabels = (new CompanySettings())->getAll();
        $this->_classColumns = array(
            'Commercial Tier' => array(
                'select' => 'company.commercial_tier AS commercialTier',
                'pagerRender' => 'return htmlspecialchars(CompanySettings::formatTier($rsData[\'commercialTier\'], $this->_commercialTierLabels));',
                'sortableColumn' => 'commercialTier',
                'filter' => "COALESCE(company.commercial_tier, 'Unclassified')",
                'filterTypes' => '==',
                'exportable' => false,
                'pagerWidth' => 120),
            'Relationship Lifecycle' => array(
                'select' => 'company.relationship_status AS relationshipStatus',
                'pagerRender' => 'return htmlspecialchars($rsData[\'relationshipStatus\'] ?? \'Unclassified\');',
                'sortableColumn' => 'relationshipStatus',
                'filter' => "COALESCE(company.relationship_status, 'Unclassified')",
                'filterTypes' => '==',
                'exportable' => false,
                'pagerWidth' => 130),
            'Attachments' => array(  'select'   => 'IF(attachment_id, 1, 0) AS attachmentPresent',
                                     'pagerRender' => '
                                                    if ($rsData[\'attachmentPresent\'] == 1)
                                                    {
                                                        $return = \'<img src="images/paperclip.gif" alt="" width="16" height="16" title="Attachment Present" />\';
                                                    }
                                                    else
                                                    {
                                                        $return = \'<img src="images/mru/blank.gif" alt="" width="16" height="16" />\';
                                                    }

                                                    return $return;
                                                   ',

                                     'pagerWidth'    => 10,
                                     'pagerOptional' => true,
                                     'pagerNoTitle' => true,
                                     'sizable'  => false,
                                     'exportable' => false,
                                     'filterable' => false),

            'Name' =>     array('select'         => 'company.name AS name',
                                      'pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=companies&amp;a=show&amp;companyID=\'.$rsData[\'companyID\'].\'" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'name\']).\'</a>\';',
                                      'sortableColumn' => 'name',
                                      'pagerWidth'     => 60,
                                      'pagerOptional'  => false,
                                      'alphaNavigation'=> true,
                                      'filter'         => 'company.name'),

            'Jobs' =>       array('select'   => '(
                                                            SELECT
                                                                COUNT(*)
                                                            FROM
                                                                joborder
                                                            WHERE
                                                                company_id = company.company_id

                                                        ) AS jobs',
                                     'pagerRender'      => 'if ($rsData[\'jobs\'] != 0) {return $rsData[\'jobs\'];} else {return \'\';}',
                                     'sortableColumn'     => 'jobs',
                                     'pagerWidth'    => 40,
                                     'filterHaving'  => 'jobs',
                                     'filterTypes'   => '===>=<'),

            'Phone' =>     array('select'   => 'company.phone1 AS phone',
                                     'sortableColumn'     => 'phone',
                                     'pagerWidth'    => 80,
                                     'filter'         => 'company.phone1'),

            'Phone 2' =>     array('select'   => 'company.phone2 AS phone2',
                                     'sortableColumn'     => 'phone2',
                                     'pagerWidth'    => 80,
                                     'filter'         => 'company.phone2'),


            'City' =>           array('select'   => 'company.city AS city',
                                     'sortableColumn'     => 'city',
                                     'pagerWidth'    => 80,
                                     'alphaNavigation' => true,
                                     'filter'         => 'company.city'),


            'State' =>          array('select'   => 'company.state AS state',
                                     'sortableColumn'     => 'state',
                                     'filterType' => 'dropDown',
                                     'pagerWidth'    => 50,
                                     'alphaNavigation' => true,
                                     'filter'         => 'company.state'),

            'Zip' =>            array('select'  => 'company.zip AS zip',
                                     'sortableColumn'    => 'zip',
                                     'pagerWidth'   => 50,
                                     'filter'         => 'company.zip'),


            'Web Site' =>      array('select'  => 'company.url AS webSite',
                                     'pagerRender'     => 'return \'<a href="\'.htmlspecialchars($rsData[\'webSite\']).\'" target="_blank">\'.htmlspecialchars($rsData[\'webSite\']).\'</a>\';',
                                     'sortableColumn'    => 'webSite',
                                     'pagerWidth'   => 80,
                                     'filter'         => 'company.url'),

            'Owner' =>         array('select'   => 'owner_user.first_name AS ownerFirstName,' .
                                                   'owner_user.last_name AS ownerLastName,' .
                                                   'CONCAT(owner_user.last_name, owner_user.first_name) AS ownerSort',
                                     'pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'ownerFirstName\'], $rsData[\'ownerLastName\'], false, LAST_NAME_MAXLEN);',
                                     'exportRender'     => 'return $rsData[\'ownerFirstName\'] . " " .$rsData[\'ownerLastName\'];',
                                     'sortableColumn'     => 'ownerSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(owner_user.first_name, owner_user.last_name)'),

            'Contact' =>       array('select'   => 'contact.first_name AS contactFirstName,' .
                                                   'contact.last_name AS contactLastName,' .
                                                   'CONCAT(contact.last_name, contact.first_name) AS contactSort,' .
                                                   'contact.contact_id AS contactID',
                                     'pagerRender'      => 'return \'<a href="'.CATSUtility::getIndexName().'?m=contacts&amp;a=show&amp;contactID=\'.$rsData[\'contactID\'].\'">\'.StringUtility::makeInitialName($rsData[\'contactFirstName\'], $rsData[\'contactLastName\'], false, LAST_NAME_MAXLEN).\'</a>\';',
                                     'exportRender'     => 'return $rsData[\'contactFirstName\'] . " " .$rsData[\'contactLastName\'];',
                                     'sortableColumn'     => 'contactSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(contact.first_name, contact.last_name)'),


            'Created' =>       array('select'   => 'DATE_FORMAT(company.date_created, \'%m-%d-%y\') AS dateCreated',
                                     'pagerRender'      => 'return $rsData[\'dateCreated\'];',
                                     'sortableColumn'     => 'dateCreatedSort',
                                     'pagerWidth'    => 60,
                                     'filterHaving' => 'DATE_FORMAT(company.date_created, \'%m-%d-%y\')'),

            'Modified' =>      array('select'   => 'DATE_FORMAT(company.date_modified, \'%m-%d-%y\') AS dateModified',
                                     'pagerRender'      => 'return $rsData[\'dateModified\'];',
                                     'sortableColumn'     => 'dateModifiedSort',
                                     'pagerWidth'    => 60,
                                     'pagerOptional' => false,
                                     'filterHaving' => 'DATE_FORMAT(company.date_modified, \'%m-%d-%y\')'),

            'Misc Notes' =>     array('select'  => 'company.notes AS notes',
                                     'sortableColumn'    => 'notes',
                                     'pagerWidth'   => 300,
                                     'filter'         => 'company.notes'),

            'OwnerID' =>       array('select'    => '',
                                     'filter'    => 'company.owner',
                                     'pagerOptional' => false,
                                     'filterable' => false,
                                     'filterDescription' => 'Only My Companies'),

            'IsHot' =>         array('select'    => '',
                                     'filter'    => 'company.is_hot',
                                     'pagerOptional' => false,
                                     'filterable' => false,
                                     'filterDescription' => 'Only Hot Companies')
        );

        if (US_ZIPS_ENABLED)
        {
            $this->_classColumns['Near Zipcode'] =
                               array('select'  => 'company.zip AS zip',
                                     'filter' => 'company.zip',
                                     'pagerOptional' => false,
                                     'filterTypes'   => '=@');
        }

        /* Extra fields get added as columns here. */
        $companies = new Companies();
        $extraFieldsRS = $companies->extraFields->getSettings();
        foreach ($extraFieldsRS as $index => $data)
        {
            $fieldName = $data['fieldName'];

            if (!isset($this->_classColumns[$fieldName]))
            {
                $columnDefinition = $companies->extraFields->getDataGridDefinition($index, $data, $this->_db);

                /* Return false for extra fields that should not be columns. */
                if ($columnDefinition !== false)
                {
                    $this->_classColumns[$fieldName] = $columnDefinition;
                }
            }
        }

        parent::__construct($instanceName, $parameters, $misc);
    }

    /**
     * Returns the sql statment for the pager.
     *
     * @return array Clients data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        if ($this->getMiscArgument() != 0)
        {
            $savedListID = (int) $this->getMiscArgument();
            $joinSQL  .= ' INNER JOIN saved_list_entry
                                    ON saved_list_entry.data_item_type = '.DATA_ITEM_COMPANY.'
                                    AND saved_list_entry.data_item_id = company.company_id
                                    AND saved_list_entry.saved_list_id = '.$savedListID;
        }
        else
        {
            $joinSQL  .= ' LEFT JOIN saved_list_entry
                                    ON saved_list_entry.data_item_type = '.DATA_ITEM_COMPANY.'
                                    AND saved_list_entry.data_item_id = company.company_id';
        }

        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                IF(attachment_id, 1, 0) AS attachmentPresent,
                company.is_hot AS isHot,
                company.company_id AS companyID,
                company.company_id AS exportID,
                company.is_hot AS isHot,
                company.date_modified AS dateModifiedSort,
                company.date_created AS dateCreatedSort,
            %s
            FROM
                company
            LEFT JOIN user AS owner_user
                ON company.owner = owner_user.user_id
            LEFT JOIN joborder
                ON company.company_id = joborder.company_id
            LEFT JOIN contact
                ON company.billing_contact = contact.contact_id
            LEFT JOIN attachment
                ON company.company_id = attachment.data_item_id
                AND attachment.data_item_type = %s
            %s
            WHERE
                1=1
            %s
            %s
            GROUP BY company.company_id
            %s
            %s
            %s",
            $distinct,
            $selectSQL,
            DATA_ITEM_COMPANY,
            $joinSQL,
            (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
            $this->_assignedCriterion,
            (strlen($havingSQL) > 0) ? ' HAVING ' . $havingSQL : '',
            $orderSQL,
            $limitSQL
        );

        return $sql;
    }
}


?>
