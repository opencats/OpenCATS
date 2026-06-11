<?php
namespace OpenCATS\Tests\IntegrationTests;

use \OpenCATS\Tests\IntegrationTests\DatabaseTestCase;
use DatabaseConnection;

class EntityDeleteCleanupTest extends DatabaseTestCase
{
    private function countRows(DatabaseConnection $db, $sql)
    {
        $row = $db->getColumn($sql, 0, 0);

        return (int) $row[0];
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (session_status() !== PHP_SESSION_ACTIVE)
        {
            @session_start();
        }

        $_SESSION['CATS'] = new class {
            public function getUserID()
            {
                return 1;
            }

            public function isLoggedIn()
            {
                return false;
            }

            public function getTimeZoneOffset()
            {
                return 0;
            }

            public function isDateDMY()
            {
                return false;
            }
        };

        include_once(LEGACY_ROOT . '/lib/CATSUtility.php');
        include_once(LEGACY_ROOT . '/lib/Candidates.php');
        include_once(LEGACY_ROOT . '/lib/Companies.php');
        include_once(LEGACY_ROOT . '/lib/Contacts.php');
        include_once(LEGACY_ROOT . '/lib/JobOrders.php');
    }

    protected function tearDown(): void
    {
        /* The anonymous class stored in setUp() cannot be serialized when PHP
         * writes session data at shutdown, so it must not outlive the test. */
        unset($_SESSION['CATS']);

        parent::tearDown();
    }

    public function testJobOrderDeleteResetsGeneralAndCalendarReferences()
    {
        $db = DatabaseConnection::getInstance();

        $db->query("INSERT INTO joborder (title, entered_by) VALUES ('Job Delete Cleanup', 1)");
        $jobOrderID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO activity (data_item_id, data_item_type, joborder_id, entered_by, type, notes)
             VALUES (1, %d, %d, 1, 100, 'activity reference')",
            DATA_ITEM_BULKRESUME,
            $jobOrderID
        ));
        $activityID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO calendar_event (type, date, title, data_item_id, data_item_type, entered_by, joborder_id)
             VALUES (100, NOW(), 'calendar reference', 1, %d, 1, %d)",
            DATA_ITEM_BULKRESUME,
            $jobOrderID
        ));
        $calendarEventID = (int) $db->getLastInsertID();

        $jobOrders = new \JobOrders(1);
        $jobOrders->delete($jobOrderID);

        $jobOrderCount = $this->countRows(
            $db,
            sprintf('SELECT COUNT(*) FROM joborder WHERE joborder_id = %d', $jobOrderID)
        );
        $this->assertSame(0, $jobOrderCount, 'Job order row should be deleted.');

        $activityRow = $db->getAssoc(sprintf(
            'SELECT activity_id, joborder_id FROM activity WHERE activity_id = %d',
            $activityID
        ));
        $this->assertNotEmpty($activityRow, 'Activity row should still exist.');
        $this->assertNull($activityRow['joborder_id'], 'activity.joborder_id should be set to NULL.');

        $calendarRow = $db->getAssoc(sprintf(
            'SELECT calendar_event_id, joborder_id FROM calendar_event WHERE calendar_event_id = %d',
            $calendarEventID
        ));
        $this->assertNotEmpty($calendarRow, 'Calendar event row should still exist.');
        $this->assertSame('-1', (string) $calendarRow['joborder_id'], 'calendar_event.joborder_id should be set to -1.');
    }

    public function testCandidateDeleteRemovesAssociatedRows()
    {
        $db = DatabaseConnection::getInstance();

        $db->query("INSERT INTO candidate (first_name, last_name, entered_by, owner) VALUES ('Delete', 'Candidate', 1, 1)");
        $candidateID = (int) $db->getLastInsertID();

        $db->query("INSERT INTO joborder (title, entered_by) VALUES ('Candidate Cleanup Job', 1)");
        $jobOrderID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO candidate_joborder (candidate_id, joborder_id, status, date_created, date_modified)
             VALUES (%d, %d, 100, NOW(), NOW())",
            $candidateID,
            $jobOrderID
        ));
        $candidateJoborderID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO candidate_joborder_status_history (candidate_id, joborder_id, date, status_from, status_to)
             VALUES (%d, %d, NOW(), 0, 100)",
            $candidateID,
            $jobOrderID
        ));
        $candidateHistoryID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO activity (data_item_id, data_item_type, entered_by, type, notes)
             VALUES (%d, %d, 1, 100, 'candidate cleanup activity')",
            $candidateID,
            DATA_ITEM_CANDIDATE
        ));
        $activityID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO calendar_event (type, date, title, data_item_id, data_item_type, entered_by)
             VALUES (100, NOW(), 'candidate cleanup event', %d, %d, 1)",
            $candidateID,
            DATA_ITEM_CANDIDATE
        ));
        $calendarEventID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO saved_list_entry (saved_list_id, data_item_type, data_item_id, date_created)
             VALUES (1, %d, %d, NOW())",
            DATA_ITEM_CANDIDATE,
            $candidateID
        ));
        $savedListEntryID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO extra_field (data_item_id, field_name, value, data_item_type)
             VALUES (%d, 'candidate_cleanup_field', 'candidate cleanup value', %d)",
            $candidateID,
            DATA_ITEM_CANDIDATE
        ));
        $extraFieldID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO candidate_tag (candidate_id, tag_id)
             VALUES (%d, 1001)",
            $candidateID
        ));
        $candidateTagID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO career_portal_questionnaire_history (candidate_id, question, answer, questionnaire_title, questionnaire_description, date)
             VALUES (%d, 'candidate cleanup question', 'candidate cleanup answer', 'candidate cleanup title', 'candidate cleanup description', NOW())",
            $candidateID
        ));
        $questionnaireHistoryID = (int) $db->getLastInsertID();

        $candidates = new \Candidates(1);
        $candidates->delete($candidateID);

        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM candidate WHERE candidate_id = %d', $candidateID)), 'Candidate row should be deleted.');
        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM candidate_joborder WHERE candidate_joborder_id = %d', $candidateJoborderID)), 'candidate_joborder row should be deleted.');
        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM candidate_joborder_status_history WHERE candidate_joborder_status_history_id = %d', $candidateHistoryID)), 'candidate_joborder_status_history row should be deleted.');
        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM activity WHERE activity_id = %d', $activityID)), 'Candidate activity row should be deleted.');
        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM calendar_event WHERE calendar_event_id = %d', $calendarEventID)), 'Candidate calendar event row should be deleted.');
        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM saved_list_entry WHERE saved_list_entry_id = %d', $savedListEntryID)), 'Candidate saved list row should be deleted.');
        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM extra_field WHERE extra_field_id = %d', $extraFieldID)), 'Candidate extra field row should be deleted.');
        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM candidate_tag WHERE id = %d', $candidateTagID)), 'candidate_tag row should be deleted.');
        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM career_portal_questionnaire_history WHERE career_portal_questionnaire_history_id = %d', $questionnaireHistoryID)), 'career_portal_questionnaire_history row should be deleted.');
    }

    public function testCompanyDeleteRemovesAssociatedRows()
    {
        $db = DatabaseConnection::getInstance();

        $db->query("INSERT INTO company (name, entered_by, owner) VALUES ('Delete Cleanup Company', 1, 1)");
        $companyID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO activity (data_item_id, data_item_type, entered_by, type, notes)
             VALUES (%d, %d, 1, 100, 'company cleanup activity')",
            $companyID,
            DATA_ITEM_COMPANY
        ));
        $activityID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO calendar_event (type, date, title, data_item_id, data_item_type, entered_by)
             VALUES (100, NOW(), 'company cleanup event', %d, %d, 1)",
            $companyID,
            DATA_ITEM_COMPANY
        ));
        $calendarEventID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO saved_list_entry (saved_list_id, data_item_type, data_item_id, date_created)
             VALUES (1, %d, %d, NOW())",
            DATA_ITEM_COMPANY,
            $companyID
        ));
        $savedListEntryID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO extra_field (data_item_id, field_name, value, data_item_type)
             VALUES (%d, 'company_cleanup_field', 'company cleanup value', %d)",
            $companyID,
            DATA_ITEM_COMPANY
        ));
        $extraFieldID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO company_department (name, company_id)
             VALUES ('Delete Cleanup Department', %d)",
            $companyID
        ));
        $companyDepartmentID = (int) $db->getLastInsertID();

        $companies = new \Companies(1);
        $companies->delete($companyID);

        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM company WHERE company_id = %d', $companyID)), 'Company row should be deleted.');
        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM activity WHERE activity_id = %d', $activityID)), 'Company activity row should be deleted.');
        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM calendar_event WHERE calendar_event_id = %d', $calendarEventID)), 'Company calendar event row should be deleted.');
        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM saved_list_entry WHERE saved_list_entry_id = %d', $savedListEntryID)), 'Company saved list row should be deleted.');
        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM extra_field WHERE extra_field_id = %d', $extraFieldID)), 'Company extra field row should be deleted.');
        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM company_department WHERE company_department_id = %d', $companyDepartmentID)), 'company_department row should be deleted.');
    }

    public function testContactDeleteRemovesAssociatedRowsAndResetsReferences()
    {
        $db = DatabaseConnection::getInstance();

        $db->query("INSERT INTO company (name, entered_by, owner) VALUES ('Delete Cleanup Contact Company', 1, 1)");
        $companyID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO contact (company_id, last_name, first_name, entered_by, owner, company_department_id)
             VALUES (%d, 'Parent', 'Delete', 1, 1, 1)",
            $companyID
        ));
        $contactID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "UPDATE company SET billing_contact = %d WHERE company_id = %d",
            $contactID,
            $companyID
        ));

        $db->query(sprintf(
            "INSERT INTO contact (company_id, last_name, first_name, entered_by, owner, company_department_id, reports_to)
             VALUES (%d, 'Child', 'Delete', 1, 1, 1, %d)",
            $companyID,
            $contactID
        ));
        $childContactID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO joborder (title, entered_by, contact_id, company_id)
             VALUES ('Delete Cleanup Contact Job', 1, %d, %d)",
            $contactID,
            $companyID
        ));
        $jobOrderID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO activity (data_item_id, data_item_type, entered_by, type, notes)
             VALUES (%d, %d, 1, 100, 'contact cleanup activity')",
            $contactID,
            DATA_ITEM_CONTACT
        ));
        $activityID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO calendar_event (type, date, title, data_item_id, data_item_type, entered_by)
             VALUES (100, NOW(), 'contact cleanup event', %d, %d, 1)",
            $contactID,
            DATA_ITEM_CONTACT
        ));
        $calendarEventID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO saved_list_entry (saved_list_id, data_item_type, data_item_id, date_created)
             VALUES (1, %d, %d, NOW())",
            DATA_ITEM_CONTACT,
            $contactID
        ));
        $savedListEntryID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO extra_field (data_item_id, field_name, value, data_item_type)
             VALUES (%d, 'contact_cleanup_field', 'contact cleanup value', %d)",
            $contactID,
            DATA_ITEM_CONTACT
        ));
        $extraFieldID = (int) $db->getLastInsertID();

        $db->query(sprintf(
            "INSERT INTO attachment (data_item_id, data_item_type, title)
             VALUES (%d, %d, 'contact cleanup attachment')",
            $contactID,
            DATA_ITEM_CONTACT
        ));
        $attachmentID = (int) $db->getLastInsertID();

        $contacts = new \Contacts(1);
        $contacts->delete($contactID);

        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM contact WHERE contact_id = %d', $contactID)), 'Contact row should be deleted.');

        $jobOrderRow = $db->getAssoc(sprintf('SELECT joborder_id, contact_id FROM joborder WHERE joborder_id = %d', $jobOrderID));
        $this->assertSame('-1', (string) $jobOrderRow['contact_id'], 'joborder.contact_id should be set to -1.');

        $companyRow = $db->getAssoc(sprintf('SELECT company_id, billing_contact FROM company WHERE company_id = %d', $companyID));
        $this->assertSame('-1', (string) $companyRow['billing_contact'], 'company.billing_contact should be set to -1.');

        $childContactRow = $db->getAssoc(sprintf('SELECT contact_id, reports_to FROM contact WHERE contact_id = %d', $childContactID));
        $this->assertSame('-1', (string) $childContactRow['reports_to'], 'contact.reports_to should be set to -1.');

        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM activity WHERE activity_id = %d', $activityID)), 'Contact activity row should be deleted.');
        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM calendar_event WHERE calendar_event_id = %d', $calendarEventID)), 'Contact calendar event row should be deleted.');
        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM saved_list_entry WHERE saved_list_entry_id = %d', $savedListEntryID)), 'Contact saved list row should be deleted.');
        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM extra_field WHERE extra_field_id = %d', $extraFieldID)), 'Contact extra field row should be deleted.');
        $this->assertSame(0, $this->countRows($db, sprintf('SELECT COUNT(*) FROM attachment WHERE attachment_id = %d', $attachmentID)), 'Contact attachment row should be deleted.');
    }
}
