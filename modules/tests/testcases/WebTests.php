<?php
/*
 * CATS
 * Tests Module - Web Test Cases
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * All rights reserved.
 *
 * Debugging tips:
 * If you can't figure out why a test case is failing, try adding
 * $this->showSource(); right before the failing test. The page's HTML
 * source will be displayed in the browser.
 *
 * $Id: WebTests.php 3565 2007-11-12 09:09:22Z will $
 */

class CandidatesWebTest extends CATSWebTestCase
{
    function testCandidates()
    {
        /* Log in and make sure no errors occurred. */
        if (!$this->login())
        {
            /* Abort. */
            return false;
        }

        /* Add a test user. */
        $testUserID = $this->addUser(
            'Test User',
            'ATxyz',
            'testuser101',
            ACCESS_LEVEL_DELETE,
            'password101'
        );

        /* Abort if setting up the test failed. */
        if ($testUserID === false)
        {
            return false;
        }

        /* Start back at the home page. */
        $this->assertGET(CATSUtility::getAbsoluteURI('index.php?m=home'));
        $this->runPageLoadAssertions(false);

        /* Click on the Candidates tab. */
        $this->assertClickLink('Candidates');
        $this->runPageLoadAssertions(false);

        /* Test the main Candidates page. */
        $this->assertTitle('CATS - Candidates');
        $this->assertPattern('/<h2>Candidates: Home<\/h2>/');
        // FIXME: More tests here.

        /* Click on the Add Candidate sub-tab. */
        $this->assertClickLink('Add Candidate');
        $this->runPageLoadAssertions(false);

        /* Test the Add Candidate page. */
        $this->assertField('firstName');
        $this->assertField('lastName');
        $this->assertDateInputExists(
            'dateAvailable', 'false', 'MM-DD-YY'
        );
        $this->assertField('email1');
        $this->assertField('email2');
        $this->assertField('phoneHome');
        $this->assertField('phoneCell');
        $this->assertField('phoneWork');
        $this->assertField('city');
        $this->assertField('state');
        $this->assertField('zip');
        $this->assertField('address');
        $this->assertField('source');
        $this->assertField('keySkills');
        $this->assertField('currentEmployer');
        $this->assertField('canRelocate');
        $this->assertField('notes');
        $this->assertField('addressBlock');

        /* Try to add a candidate with only a first name and make sure that we
         * receive a fatal error.
         */
        $this->setField('firstName', 'TestCand');
        $this->assertClickSubmit('Add Candidate');
        $this->runPageLoadAssertions(true);
        $this->back();

        /* Try to add a candidate with all required fields (and only the
         * required fields) filled in.
         */
        $this->setField('firstName', 'TestCand');
        $this->setField('lastName', 'ATxyz');
        $this->setField('source', '');
        $this->assertClickSubmit('Add Candidate');
        $this->runPageLoadAssertions(false);

        /* We should now be on the Candidate Details page for the candidate
         * that we just added. Verify that the candidate was added correctly.
         */
        $this->assertPattern('/TestCand\s+ATxyz/');
        $this->assertPattern('/Name:/');
        $this->assertPattern('/E-Mail:/');
        $this->assertPattern('/Home Phone:/');
        $this->assertPattern('/Source:/');
        $this->assertPattern('/\(' . TESTER_FULLNAME . '\)/');
        $this->assertPattern('/Job Order Pipeline/');

        /* Click the Edit link for the candidate that we just added. */
        $this->assertClickLinkById('edit_link');
        $this->runPageLoadAssertions(false);

        /* Test the Edit Candidate page. */
        $this->assertField('firstName');
        $this->assertField('lastName');
        $this->assertDateInputExists(
            'dateAvailable', 'false', 'MM-DD-YY'
        );
        $this->assertField('email1');
        $this->assertField('email2');
        $this->assertField('phoneHome');
        $this->assertField('phoneCell');
        $this->assertField('phoneWork');
        $this->assertField('city');
        $this->assertField('state');
        $this->assertField('zip');
        $this->assertField('address');
        $this->assertField('source');
        $this->assertField('keySkills');
        $this->assertField('currentEmployer');
        $this->assertField('canRelocate');
        $this->assertField('notes');
        $this->assertField('owner');

        /* Try to remove the first name and save the candidate. Make sure that
         * we receive a fatal error.
         */
        $this->setField('firstName', '');
        $this->assertClickSubmit('Save');
        $this->runPageLoadAssertions(true);
        $this->back();

        /* Change a few things and save the form. */
        $this->setField('address', 'SavePattern223');
        $this->setField('notes', 'SavePattern225');
        $this->setField('keySkills', 'SavePattern229');
        $this->setField('owner', 'ATxyz, Test User');
        $this->assertClickSubmit('Save');
        $this->runPageLoadAssertions(false);

        /* We should now be back on the Candidate Details page for the
         * candidate. Verify that our changes were saved.
         */
        $this->assertPattern('/SavePattern223/');
        $this->assertPattern('/SavePattern225/');
        $this->assertPattern('/SavePattern229/');
        $this->assertPattern(
            '/<td class="\w+">Owner:<\/td>\s*<td class="\w+">Test User ATxyz/'
        );

        /* Click on the Search Candidates sub-tab. */
        $this->assertClickLink('Search Candidates');
        $this->runPageLoadAssertions(false);

        /* Test the Search Candidates sub-tab. */
        $this->assertFieldById('searchMode');
        $this->assertFieldById('searchText');
        $this->assertFieldById('searchCandidates');

        /* Test Search By Resume for general errors and go back. */
        $this->setFieldById('searchMode', 'Resume');
        $this->setFieldById('searchText', 'java');
        $this->assertClickSubmitById('searchCandidates');
        $this->runPageLoadAssertions(false);
        $this->back();

        /* Try to search for the candidate that we just added by key skills. */
        $this->setFieldById('searchMode', 'Key Skills');
        $this->setFieldById('searchText', 'SavePattern229');
        $this->assertClickSubmitById('searchCandidates');
        $this->runPageLoadAssertions(false);
        $this->assertPattern('/TestCand/');
        $this->assertPattern('/ATxyz/');

        /* Try to search for the candidate that we just added by full name. */
        $this->setFieldById('searchMode', 'Candidate Name');
        $this->setFieldById('searchText', 'ATxyz');
        $this->assertClickSubmitById('searchCandidates');
        $this->runPageLoadAssertions(false);
        $this->assertPattern('/TestCand/');
        $this->assertPattern('/ATxyz/');

        /* Click on the candidate. */
        $this->assertClickLink('ATxyz');
        $this->runPageLoadAssertions(false);

        /* Try to delete the candidate. */
        $this->assertClickLinkById('delete_link');
        $this->runPageLoadAssertions(false);

        /* Delete the test user. */
        $this->deleteUser($testUserID);

        /* We're done; log out. */
        $this->logout();
    }
}

class CompaniesWebTest extends CATSWebTestCase
{
    function testCompanies()
    {
        /* Log in and make sure no errors occurred. */
        if (!$this->login())
        {
            /* Abort. */
            return false;
        }

        /* Add a test user. */
        $testUserID = $this->addUser(
            'Test User',
            'ATxyz',
            'testuser101',
            ACCESS_LEVEL_DELETE,
            'password101'
        );

        /* Abort if setting up the test failed. */
        if ($testUserID === false)
        {
            return false;
        }

        /* Start back at the home page. */
        $this->assertGET(CATSUtility::getAbsoluteURI('index.php?m=home'));
        $this->runPageLoadAssertions(false);

        /* Click on the Companies tab. */
        $this->assertClickLink('Companies');
        $this->runPageLoadAssertions(false);

        /* Test the main Companies page. */
        $this->assertTitle('CATS - Companies');
        $this->assertPattern('/<h2>Companies: Home<\/h2>/');
        // FIXME: More tests here.

        /* Click on the Add Company sub-tab. */
        $this->assertClickLink('Add Company');
        $this->runPageLoadAssertions(false);

        /* Ensure that correct fields exist. */
        $this->assertField('name');
        $this->assertField('phone1');
        $this->assertField('phone2');
        $this->assertField('address');
        $this->assertField('city');
        $this->assertField('state');
        $this->assertField('zip');
        $this->assertField('url');
        $this->assertField('notes');
        $this->assertField('isHot');

        /* Try to add a company without a name and make sure that we receive a
         * fatal error.
         */
        $this->assertClickSubmit('Add Company');
        $this->runPageLoadAssertions(true);
        $this->back();

        /* Try to add a company with all required fields (and only the required
         * fields) filled in.
         */
        $this->setField('name', 'Test Company ATxyz');
        $this->assertClickSubmit('Add Company');
        $this->runPageLoadAssertions(false);

        /* We should now be on the Company Details page for the company that we
         * just added. Verify that the company was added correctly.
         */
        $this->assertPattern('/Test Company ATxyz/');
        $this->assertPattern('/Name:/');
        $this->assertPattern('/Primary Phone:/');
        $this->assertPattern('/Key Technologies:/');
        $this->assertPattern('/\(' . TESTER_FULLNAME . '\)/');

        /* Click the Edit link for the company that we just added. */
        $this->assertClickLinkById('edit_link');
        $this->runPageLoadAssertions(false);

        /* Test the Edit Company page. */
        $this->assertField('name');
        $this->assertField('phone1');
        $this->assertField('phone2');
        $this->assertField('address');
        $this->assertField('city');
        $this->assertField('state');
        $this->assertField('zip');
        $this->assertField('url');
        $this->assertField('notes');
        $this->assertField('owner');
        $this->assertField('isHot');

        /* Try to remove the name and save the company. Make sure that we
         * receive a fatal error.
         */
        $this->setField('name', '');
        $this->assertClickSubmit('Save');
        $this->runPageLoadAssertions(true);
        $this->back();

        /* Change a few things and save the form. */
        $this->setField('city', 'SavePattern223');
        $this->setField('address', 'SavePattern224');
        $this->setField('keyTechnologies', 'SavePattern229');
        $this->setField('owner', 'ATxyz, Test User');
        $this->assertClickSubmit('Save');
        $this->runPageLoadAssertions(false);

        /* We should now be back on the Company Details page for the company.
         * Verify that our changes were saved.
         */
        $this->assertPattern('/SavePattern223/');
        $this->assertPattern('/SavePattern224/');
        $this->assertPattern('/SavePattern229/');
        $this->assertPattern(
            '/<td class="\w+">Owner:<\/td>\s*<td class="\w+">Test User ATxyz/'
        );

        /* Click on the Search Companies sub-tab. */
        $this->assertClickLink('Search Companies');
        $this->runPageLoadAssertions(false);

        /* Test the Search Companies page. */
        $this->assertFieldById('searchMode');
        $this->assertFieldById('searchText');
        $this->assertFieldById('searchCompanies');

        /* Try to search for the company that we just added by key technologies. */
        $this->setFieldById('searchMode', 'Key Technologies');
        $this->setFieldById('searchText', 'SavePattern229');
        $this->assertClickSubmitById('searchCompanies');
        $this->runPageLoadAssertions(false);
        $this->assertPattern('/Test Company ATxyz/');

        /* Try to search for the company that we just added by company name. */
        $this->setFieldById('searchMode', 'Name');
        $this->setFieldById('searchText', 'Test Company ATxyz');
        $this->assertClickSubmitById('searchCompanies');
        $this->runPageLoadAssertions(false);
        $this->assertPattern('/Test Company ATxyz/');

        /* Click on the company. */
        $this->assertClickLink('Test Company ATxyz');
        $this->runPageLoadAssertions(false);

        /* Try to delete the company. */
        $this->assertClickLinkById('delete_link');
        $this->runPageLoadAssertions(false);

        /* Delete the test user. */
        $this->deleteUser($testUserID);

        /* We're done; log out. */
        $this->logout();
    }
}

class ContactsWebTest extends CATSWebTestCase
{
    function testContacts()
    {
        /* Log in and make sure no errors occurred. */
        if (!$this->login())
        {
            /* Abort. */
            return false;
        }

        /* Add a test company. */
        $testCompanyID = $this->addCompany(
            'Test Company ATxyz'
        );

        /* Add a test user. */
        $testUserID = $this->addUser(
            'Test User',
            'ATxyz',
            'testuser101',
            ACCESS_LEVEL_DELETE,
            'password101'
        );

        /* Start back at the home page. */
        $this->assertGET(CATSUtility::getAbsoluteURI('index.php?m=home'));
        $this->runPageLoadAssertions(false);

        /* Click on the Contacts tab. */
        $this->assertClickLink('Contacts');
        $this->runPageLoadAssertions(false);

        /* Test the main Contacts page. */
        $this->assertTitle('CATS - Contacts');
        $this->assertPattern('/<h2>Contacts: Home<\/h2>/');
        // FIXME: More tests here.

        /* Click on the Add Contact sub-tab. */
        $this->assertClickLink('Add Contact');
        $this->runPageLoadAssertions(false);

        /* Ensure that correct fields exist. */
        $this->assertField('firstName');
        $this->assertField('lastName');
        $this->assertField('title');
        $this->assertField('department');
        $this->assertField('companyID');
        $this->assertField('phoneOther');
        $this->assertField('phoneCell');
        $this->assertField('phoneWork');
        $this->assertField('email1');
        $this->assertField('email2');
        $this->assertField('address');
        $this->assertField('city');
        $this->assertField('state');
        $this->assertField('zip');
        $this->assertField('notes');
        $this->assertField('isHot');

        /* Try to add a contact with only a first name. Make sure we receive a
         * fatal error.
         */
        $this->setField('firstName', 'Test Contact');
        $this->assertClickSubmit('Add Contact');
        $this->runPageLoadAssertions(true);
        $this->back();

        /* Try to add a contact with all required fields (and only the required
         * fields) filled in.
         */
        $this->setField('firstName', 'Test Contact');
        $this->setField('lastName', 'ATxyz');
        $this->setField('companyID', $testCompanyID);
        $this->setField('title', 'Test Title 109');
        $this->assertClickSubmit('Add Contact');
        $this->runPageLoadAssertions(false);

        /* We should now be on the Contact Details page for the contact that
         * we just added. Verify that the contact was added correctly.
         */
        $this->assertPattern('/Test Contact\s+ATxyz/');
        $this->assertPattern('/E-Mail:/');
        $this->assertPattern('/Title:/');
        $this->assertPattern('/Department:/');
        $this->assertPattern('/Test Company ATxyz/');
        $this->assertPattern('/\(' . TESTER_FULLNAME . '\)/');

        /* Click the Edit link for the contact that we just added. */
        $this->assertClickLinkById('edit_link');
        $this->runPageLoadAssertions(false);

        /* Test the Edit Contact page. */
        $this->assertField('firstName');
        $this->assertField('lastName');
        $this->assertField('title');
        $this->assertField('department');
        $this->assertField('companyID');
        $this->assertField('phoneOther');
        $this->assertField('phoneCell');
        $this->assertField('phoneWork');
        $this->assertField('email1');
        $this->assertField('email2');
        $this->assertField('address');
        $this->assertField('city');
        $this->assertField('state');
        $this->assertField('zip');
        $this->assertField('notes');
        $this->assertField('owner');
        $this->assertField('isHot');

        /* Try to remove the first name and save the contact. Make sure that we
         * receive a fatal error.
         */
        $this->setField('firstName', '');
        $this->assertClickSubmit('Save');
        $this->runPageLoadAssertions(true);
        $this->back();

        /* Change a few things and save the form. */
        $this->setField('address', 'SavePattern223');
        $this->setField('notes', 'SavePattern225');
        $this->setField('title', 'SavePattern226');
        $this->setField('owner', 'ATxyz, Test User');
        $this->assertClickSubmit('Save');
        $this->runPageLoadAssertions(false);

        /* We should now be back on the Contact Details page for the contact.
         * Verify that our changes were saved.
         */
        $this->assertPattern('/SavePattern223/');
        $this->assertPattern('/SavePattern225/');
        $this->assertPattern('/SavePattern226/');
        $this->assertPattern(
            '/<td class="\w+">Owner:<\/td>\s*<td class="\w+">Test User ATxyz/'
        );

        /* Click on the Search Contacts sub-tab. */
        $this->assertClickLink('Search Contacts');
        $this->runPageLoadAssertions(false);

        /* Test the Search Contacts page. */
        $this->assertFieldById('searchMode');
        $this->assertFieldById('searchText');
        $this->assertFieldById('searchContacts');

        /* Try to search for the contact that we just added by company name. */
        $this->setFieldById('searchMode', 'Company Name');
        $this->setFieldById('searchText', 'Test Company ATxyz');
        $this->runPageLoadAssertions(false);
        $this->assertClickSubmitById('searchContacts');
        $this->assertPattern('/Test Contact/');
        $this->assertPattern('/ATxyz/');

        /* Try to search for the contact that we just added by title. */
        $this->setFieldById('searchMode', 'Title');
        $this->setFieldById('searchText', 'SavePattern226');
        $this->assertClickSubmitById('searchContacts');
        $this->runPageLoadAssertions(false);
        $this->assertPattern('/Test Contact/');
        $this->assertPattern('/ATxyz/');

        /* Try to search for the contact that we just added by full name. */
        $this->setFieldById('searchMode', 'Contact Name');
        $this->setFieldById('searchText', 'Test Contact ATxyz');
        $this->assertClickSubmitById('searchContacts');
        $this->runPageLoadAssertions(false);
        $this->assertPattern('/Test Contact/');
        $this->assertPattern('/ATxyz/');

        /* Click on the contact. */
        $this->assertClickLink('ATxyz');
        $this->runPageLoadAssertions(false);

        /* Try to download the contact's vCard. */
        $this->assertClickLinkById('vCard');
        $this->runPageLoadAssertions(false);

        /* Test the vCard content. */
        $output = trim($this->getRawSource());

        $outputLines = explode("\n", $output);
        $outputLines = array_map('trim', $outputLines);

        $this->assertIdentical(
            $outputLines[0],
            'BEGIN:VCARD'
        );
        $this->assertIdentical(
            $outputLines[1],
            'VERSION:2.1'
        );
        $this->assertIdentical(
            $outputLines[2],
            'N:ATxyz;Test Contact;;;'
        );
        $this->assertIdentical(
            $outputLines[3],
            'FN:Test Contact ATxyz'
        );
        $this->assertIdentical(
            $outputLines[4],
            'ADR;ENCODING=QUOTED-PRINTABLE:;;SavePattern223;;;;'
        );
        $this->assertIdentical(
            $outputLines[5],
            'TITLE;ENCODING=QUOTED-PRINTABLE:SavePattern226'
        );
        $this->assertIdentical(
            $outputLines[6],
            'ORG;ENCODING=QUOTED-PRINTABLE:Test Company ATxyz'
        );

        /* Test revision timestamp. */
        $this->assertPatternIn(
            '/^REV:\d{8}T\d{6}$/',
            $outputLines[7]
        );

        $this->assertIdentical(
            $outputLines[8],
            'MAILER:CATS'
        );
        $this->assertIdentical(
            $outputLines[9], 'END:VCARD'
        );

        $this->back();

        /* Try to delete the contact. */
        $this->assertClickLinkById('delete_link');
        $this->runPageLoadAssertions(false);

        /* Delete the test company. */
        $this->deleteCompany($testCompanyID);

        /* Delete the test user. */
        $this->deleteUser($testUserID);

        /* We're done; log out. */
        $this->logout();
    }
}

class CalendarWebTest extends CATSWebTestCase
{
    function testCalendar()
    {
        /* Log in and make sure no errors occurred. */
        if (!$this->login())
        {
            /* Abort. */
            return false;
        }

        /* Click on the Calendar tab. */
        $this->assertClickLink('Calendar');
        $this->runPageLoadAssertions(false);

        /* Test the main Calendar page. */
        $this->assertTitle('CATS - Calendar');
        $this->assertPattern('/<h2>Calendar<\/h2>/');
        $this->assertPattern('/My Upcoming Events/');
        $this->assertPattern(
            sprintf('/setCalendarViewMonth\(%s, %s\);/', date('Y'), date('n'))
        );

        /* Check Add Form */
        $this->assertDateInputExists(
            'dateAdd', 'true', 'MM-DD-YY', true
        );
        $this->assertFieldById('type');
        $this->assertFieldById('allDay0');
        $this->assertFieldById('allDay1');
        $this->assertFieldById('hour');
        $this->assertFieldById('minute');
        $this->assertFieldById('meridiem');
        $this->assertFieldById('sendEmail');
        $this->assertFieldById('reminderTime');
        $this->assertFieldById('duration');
        $this->assertFieldById('description');
        $this->assertFieldById('type');
        $this->assertField('allDay');
        $this->assertField('hour');
        $this->assertField('minute');
        $this->assertField('meridiem');
        $this->assertField('sendEmail');
        $this->assertField('reminderTime');
        $this->assertField('duration');
        $this->assertField('description');

        /* Check Edit Form */
        $this->assertDateInputExists(
            'dateEdit', 'true', 'MM-DD-YY', true
        );
        $this->assertFieldById('typeEdit');
        $this->assertFieldById('allDayEdit0');
        $this->assertFieldById('allDayEdit1');
        $this->assertFieldById('hourEdit');
        $this->assertFieldById('minuteEdit');
        $this->assertFieldById('meridiemEdit');
        $this->assertFieldById('sendEmailEdit');
        $this->assertFieldById('reminderTimeEdit');
        $this->assertFieldById('durationEdit');
        $this->assertFieldById('descriptionEdit');

        // FIXME: More tests here.

        /* Test the Add Event page. */

        // FIXME: Test add / remove / edit events.

        /* We're done; log out. */
        $this->logout();
    }
}

class ReportsWebTest extends CATSWebTestCase
{
    function testReports()
    {
        /* Log in and make sure no errors occurred. */
        if (!$this->login())
        {
            /* Abort. */
            return false;
        }

        /* Click on the Calendar tab. */
        $this->assertClickLink('Reports');
        $this->runPageLoadAssertions(false);

        /* Test the main Reports page. */
        $this->assertTitle('CATS - Reports');
        $this->assertPattern('/<h2>Reports<\/h2>/');
        $this->assertPattern('/Today/');
        $this->assertPattern('/Yesterday/');
        $this->assertPattern('/This Week/');
        $this->assertPattern('/Last Week/');
        $this->assertPattern('/This Month/');
        $this->assertPattern('/Last Month/');
        $this->assertPattern('/This Year/');
        $this->assertPattern('/Last Year/');
        $this->assertPattern('/To Date/');

        /* Test submission reports. */
        $this->assertClickLink('New Submissions', 0);
        $this->runPageLoadAssertions(false);
        $this->assertTitle('CATS - Today\'s Report');
        $this->back();

        $this->assertClickLink('New Submissions', 1);
        $this->runPageLoadAssertions(false);
        $this->assertTitle('CATS - Yesterday\'s Report');
        $this->back();

        $this->assertClickLink('New Submissions', 2);
        $this->runPageLoadAssertions(false);
        $this->assertTitle('CATS - This Week\'s Report');
        $this->back();

        $this->assertClickLink('New Submissions', 3);
        $this->runPageLoadAssertions(false);
        $this->assertTitle('CATS - Last Week\'s Report');
        $this->back();

        $this->assertClickLink('New Submissions', 4);
        $this->runPageLoadAssertions(false);
        $this->assertTitle('CATS - This Month\'s Report');
        $this->back();

        $this->assertClickLink('New Submissions', 5);
        $this->runPageLoadAssertions(false);
        $this->assertTitle('CATS - Last Month\'s Report');
        $this->back();

        $this->assertClickLink('New Submissions', 6);
        $this->runPageLoadAssertions(false);
        $this->assertTitle('CATS - This Year\'s Report');
        $this->back();

        $this->assertClickLink('New Submissions', 7);
        $this->runPageLoadAssertions(false);
        $this->assertTitle('CATS - Last Year\'s Report');
        $this->back();

        $this->assertClickLink('Total Submissions', 0);
        $this->runPageLoadAssertions(false);
        $this->assertTitle('CATS - To Date Report');
        $this->back();

        /* We're done; log out. */
        $this->logout();
    }
}

class SettingsWebTest extends CATSWebTestCase
{
    function testSettings()
    {
        /* Log in and make sure no errors occurred. */
        if (!$this->login())
        {
            /* Abort. */
            return false;
        }

        /* Click on the Settings tab. */
        $this->assertClickLink('Settings');
        $this->runPageLoadAssertions(false);

        /* Test the main Settings page (My Profile). */
        $this->assertTitle('CATS - Settings');
        $this->assertPattern('/<h2>Settings: My Profile<\/h2>/');

        /* Test View Profile. */
        $this->assertClickLink('View Profile');
        $this->assertPattern('/' . TESTER_LOGIN . '/');
        $this->assertPattern('/' . TESTER_FIRSTNAME . '\s+' . TESTER_LASTNAME . '/');
        $this->runPageLoadAssertions(false);
        $this->back();

        /* Test Change Password. */
        $this->assertClickLink('Change Password');
        $this->runPageLoadAssertions(false);
        $this->assertField('currentPassword');
        $this->assertField('newPassword');
        $this->assertField('retypeNewPassword');
        $this->assertField('changePassword');
        /* FIXME: Test change password functionality. */

        /* Click on the Administration sub-tab. */
        $this->assertClickLink('Administration');
        $this->runPageLoadAssertions(false);

        /* Click on the My Profile sub-tab. */
        $this->assertClickLink('My Profile');
        $this->runPageLoadAssertions(false);
        $this->back();

        /* Click on the User Management sub-tab. */
        $this->assertClickLink('User Management');
        $this->runPageLoadAssertions(false);

        $this->assertClickLink(TESTER_LASTNAME);
        $this->runPageLoadAssertions(false);
        $this->back();

        /* Click on Add User. */
        $this->assertClickLinkById('add_link');
        $this->runPageLoadAssertions(false);

        /* Test the Add User page. */
        $this->assertField('firstName');
        $this->assertField('lastName');
        $this->assertField('username');
        $this->assertField('password');
        $this->assertField('retypePassword');
        $this->assertField('accessLevel');

        /* Try to add a user with only a first name and make sure that we
         * receive a fatal error.
         */
        $this->setField('firstName', 'Test User');
        $this->assertClickSubmit('Add User');
        $this->runPageLoadAssertions(true);
        $this->back();

        /* Try to add a user with all required fields (and only the required
         * fields) filled in.
         */
        $this->setField('firstName', 'Test User');
        $this->setField('lastName', 'ATxyz');
        $this->setField('username', 'testuser109');
        $this->setField('password', 'testpass109');
        $this->setField('retypePassword', 'testpass109');
        $this->setField('accessLevel', 'Delete (Default)');
        $this->assertClickSubmit('Add User');
        $this->runPageLoadAssertions(false);

        /* We should now be on the User Details page for the user that we just
         * added. Verify that the user was added correctly.
         */
        $this->assertPattern('/Test\s+User\s+ATxyz/');
        $this->assertPattern('/Username:/');
        $this->assertPattern('/E-Mail:/');
        $this->assertPattern('/Access Level:/');
        $this->assertPattern('/Last Successful Login:/');
        $this->assertPattern('/Last Failed Login:/');

        /* Get the user ID. */
        $matchResult = preg_match(
            '/userID=(?P<userID>\d+)/', $this->getUrl(), $matches
        );
        $this->assertTrue($matchResult, 'URL should contain userID=');
        $userID = $matches['userID'];

        $this->assertClickLinkById('edit_link');
        $this->runPageLoadAssertions(false);

        $this->assertField('firstName');
        $this->assertField('lastName');
        $this->assertField('username');
        $this->assertField('accessLevel');

        /* Try to remove the first name and save the contact. Make sure that
         * we receive a fatal error.
         */
        $this->setField('firstName', '');
        $this->assertClickSubmit('Save');
        $this->runPageLoadAssertions(true);
        $this->back();

        /* Change a few things and save the form. */
        $this->setField('username', 'SavePattern223');
        $this->assertClickSubmit('Save');
        $this->runPageLoadAssertions(false);
        $this->assertPattern('/SavePattern223/');

        /* Test login activity pages. */
        $this->assertClickLink('Administration');
        $this->assertClickLink('Login Activity');
        $this->runPageLoadAssertions(false);

        $this->assertGET(
            CATSUtility::getAbsoluteURI(
                'index.php?m=settings&a=loginActivity&view=successful'
            ),
            'Manually loading successful login activity page should succeed'
        );
        $this->runPageLoadAssertions(false);

        $this->assertGET(
            CATSUtility::getAbsoluteURI(
                'index.php?m=settings&a=loginActivity&view=unsuccessful'
            ),
            'Manually loading unsuccessful login activity page should succeed'
        );
        $this->runPageLoadAssertions(false);

        /* Delete the user. */
        $this->deleteUser($userID);

        /* We're done; log out. */
        $this->logout();
    }
}


?>
