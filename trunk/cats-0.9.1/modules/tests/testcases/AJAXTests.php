<?php
/*
 * CATS
 * Tests Module - AJAX Test Cases
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * All rights reserved.
 *
 * $Id: AJAXTests.php 2380 2007-04-25 21:01:23Z will $
 */

include_once('./lib/ActivityEntries.php');


class ActivityTest extends CATSAJAXTestCase
{
    function testActivity()
    {
        /* Log in and make sure no errors occurred. */
        if (!$this->login())
        {
            return false;
        }

        /* Add a test candidate. */
        $testCandidateID = $this->addCandidate(
            'TestCand', 'ATxyz'
        );

        /* Add a test company. */
        $testCompanyID = $this->addCompany(
            'Test Company ATxyz'
        );

        /* Add a test job order. */
        $testJobOrderID1 = $this->addJobOrder(
            'Test JobOrder ATxyz',
            $testCompanyID,
            '-1',
            'H',
            TESTER_USER_ID,
            1,
            'Test City',
            'MN'
        );

        /* Add another test job order. */
        $testJobOrderID2 = $this->addJobOrder(
            'Test JobOrder ATxyx',
            $testCompanyID,
            '-1',
            'H',
            TESTER_USER_ID,
            1,
            'Test City',
            'AL'
        );

        /* POST the AJAX call to ajax.php. */
        $this->post(
            CATSUtility::getAbsoluteURI('ajax.php'),
            array(
                'f' => 'addActivity',
                'dataItemID' => $testCandidateID,
                'dataItemType' => (string) DATA_ITEM_CANDIDATE,
                'type' => (string) ACTIVITY_CALL_TALKED,
                'notes' => 'Test notes.',
                'jobOrderID' => $testJobOrderID1
            )
        );

        /* Use SimpleXML to parse the XML response. */
        $xml = $this->getSimpleXML();

        /* Make sure the error code is 0 and there is no error message. */
        $this->runXMLLoadAssertions($xml, false);

        $this->assertEqual(
            (string) $xml->type,
            (string) ACTIVITY_CALL_TALKED
        );
        $this->assertEqual(
            (string) $xml->typedescription,
            'Call (Talked)'
        );
        $this->assertEqual(
            (string) $xml->notes,
            'Test notes.'
        );
        $this->assertEqual(
            (string) $xml->enteredby,
            StringUtility::makeInitialName(
                TESTER_FIRSTNAME, TESTER_LASTNAME, false, LAST_NAME_MAXLEN
            )
        );
        $this->assertEqual(
            (string) $xml->regarding, 'Test JobOrder ATxyz (Test Company ATxyz)'
        );

        /* We don't know what these values are going to be exactly, so we
         * just make sure they "look right".
         */
        $this->assertPatternIn(
            '/\d+$/',
            $xml->activityid
        );
        $this->assertPatternIn(
            '/\d+-\d+-\d+ \(\d+:\d+ [AP]M\)/',
            $xml->date
        );

        /* Store the activity ID so we can delete / edit this activity
         * later.
         */
        $addedActivityID = (string) $xml->activityid;

        /* POST the AJAX call to ajax.php. */
        $this->post(
            CATSUtility::getAbsoluteURI('ajax.php'),
            array(
                'f' => 'editActivity',
                'activityID' => $addedActivityID,
                'type' => (string) ACTIVITY_CALL_LVM,
                'notes' => 'Test notes that are now edited.',
                'jobOrderID' => $testJobOrderID2
            )
        );

        /* Use SimpleXML to parse the XML response. */
        $xml = $this->getSimpleXML();

        /* Make sure the error code is 0 and there is no error message. */
        $this->runXMLLoadAssertions($xml, false);

        $this->assertEqual(
            (string) $xml->type,
            (string) ACTIVITY_CALL_LVM
        );
        $this->assertEqual(
            (string) $xml->typedescription,
            'Call (LVM)'
        );
        $this->assertEqual(
            (string) $xml->notes,
            'Test notes that are now edited.'
        );
        $this->assertEqual(
            (string) $xml->regarding,
            'Test JobOrder ATxyx (Test Company ATxyz)'
        );

        /* POST the AJAX call to ajax.php. */
        $this->post(
            CATSUtility::getAbsoluteURI('ajax.php'),
            array(
                'f' => 'deleteActivity',
                'activityID' => $addedActivityID
            )
        );

        /* Use SimpleXML to parse the XML response. */
        $xml = $this->getSimpleXML();

        /* Make sure the error code is 0 and there is no error message. */
        $this->runXMLLoadAssertions($xml, false);

        $this->assertEqual(
            (string) $xml->response,
            'Success!'
        );

        /* Delete the test company. This will also delete the associated job orders. */
        $this->deleteCompany($testCompanyID);

        /* Delete the test candidate. */
        $this->deleteCandidate($testCandidateID);

        /* We're done; log out. */
        $this->logout();
    }
}

class GetCompanyContactsTest extends CATSAJAXTestCase
{
    function testGetCompanyContacts()
    {
        /* Log in and make sure no errors occurred. */
        if (!$this->login())
        {
            return false;
        }

        /* Add a test company. */
        $testCompanyID = $this->addCompany(
            'Test Company ATxyz',
            'Minneapolis',
            'MN',
            '55404'
        );

        /* Add a test contact. */
        $testContactID1 = $this->addContact(
            'Test Contact',
            'ATxyz',
            $testCompanyID,
            'Test Title 101'
        );

        /* Add another test contact. */
        $testContactID2 = $this->addContact(
            'Test Contact',
            'ATxyx',
            $testCompanyID,
            'Test Title 102'
        );

        /* POST the AJAX call to ajax.php. */
        $this->post(
            CATSUtility::getAbsoluteURI('ajax.php'),
            array(
                'f' => 'getCompanyContacts',
                'companyID' => $testCompanyID
            )
        );

        /* Use SimpleXML to parse the XML response. */
        $xml = $this->getSimpleXML();

        /* Make sure the error code is 0 and there is no error message. */
        $this->runXMLLoadAssertions($xml, false);

        $this->assertEqual(
            (string) $xml->contact[0]->id,
            (string) $testContactID1
        );
        $this->assertEqual(
            (string) $xml->contact[0]->firstname,
            (string) 'Test Contact'
        );
        $this->assertEqual(
            (string) $xml->contact[0]->lastname,
            'ATxyz'
        );

        $this->assertEqual(
            (string) $xml->contact[1]->id,
            (string) $testContactID2
        );
        $this->assertEqual(
            (string) $xml->contact[1]->firstname,
            (string) 'Test Contact'
        );
        $this->assertEqual(
            (string) $xml->contact[1]->lastname,
            'ATxyx'
        );

        /* Delete the test company. This will also delete the associated contacts. */
        $this->deleteCompany($testCompanyID);

        /* We're done; log out. */
        $this->logout();
    }
}

class GetCompanyLocationTest extends CATSAJAXTestCase
{
    function testGetCompanyLocation()
    {
        /* Log in and make sure no errors occurred. */
        if (!$this->login())
        {
            return false;
        }

        /* Add a test company. */
        $testCompanyID = $this->addCompany(
            'Test Company ATxyz',
            'Minneapolis',
            'MN',
            '55404',
            '"Test Department"'
        );

        /* POST the AJAX call to ajax.php. */
        $this->post(
            CATSUtility::getAbsoluteURI('ajax.php'),
            array(
                'f' => 'getCompanyLocation',
                'companyID' => $testCompanyID
            )
        );

        /* Use SimpleXML to parse the XML response. */
        $xml = $this->getSimpleXML();

        /* Make sure the error code is 0 and there is no error message. */
        $this->runXMLLoadAssertions($xml, false);

        $this->assertEqual(
            (string) $xml->city,
            (string) 'Minneapolis'
        );
        $this->assertEqual(
            (string) $xml->state,
            'MN'
        );
        $this->assertEqual(
            (string) $xml->zip,
            '55404'
        );

        /* Delete the test company. This will also delete the associated job orders. */
        $this->deleteCompany($testCompanyID);

        /* We're done; log out. */
        $this->logout();
    }
}

class GetCompanyLocationAndDepartmentsTest extends CATSAJAXTestCase
{
    function testGetCompanyLocationAndDepartments()
    {
        /* Log in and make sure no errors occurred. */
        if (!$this->login())
        {
            return false;
        }

        /* Add a test company. */
        $testCompanyID = $this->addCompany(
            'Test Company ATxyz',
            'Minneapolis',
            'MN',
            '55404',
            '"Test Department A","Test Department B"'
        );

        /* POST the AJAX call to ajax.php. */
        $this->post(
            CATSUtility::getAbsoluteURI('ajax.php'),
            array(
                'f' => 'getCompanyLocationAndDepartments',
                'companyID' => $testCompanyID
            )
        );

        /* Use SimpleXML to parse the XML response. */
        $xml = $this->getSimpleXML();

        /* Make sure the error code is 0 and there is no error message. */
        $this->runXMLLoadAssertions($xml, false);

        $this->assertEqual(
            (string) $xml->city,
            (string) 'Minneapolis'
        );
        $this->assertEqual(
            (string) $xml->state,
            'MN'
        );
        $this->assertEqual(
            (string) $xml->zip,
            '55404'
        );
        $this->assertEqual(
            (string) $xml->departments,
            '"Test Department A","Test Department B"'
        );

        /* Delete the test company. This will also delete the associated job orders. */
        $this->deleteCompany($testCompanyID);

        /* We're done; log out. */
        $this->logout();
    }
}

class GetCompanyNamesTest extends CATSAJAXTestCase
{
    function testGetCompanyNames()
    {
        /* Log in and make sure no errors occurred. */
        if (!$this->login())
        {
            return false;
        }
    }
}

class GetDataItemJobOrdersTest extends CATSAJAXTestCase
{
    function testGetDataItemJobOrders()
    {
        /* Log in and make sure no errors occurred. */
        if (!$this->login())
        {
            return false;
        }

        /* Add a test company. */
        $testCompanyID = $this->addCompany(
            'Test Company ATxyz'
        );

        /* Add a test contact. */
        $testContactID = $this->addContact(
            'Test Contact',
            'ATxyz',
            $testCompanyID,
            'Test Title 101'
        );

        /* Add a test candidate. */
        $testCandidateID = $this->addCandidate(
            'TestCand',
            'ATxyz'
        );

        /* Add a test job order. */
        $testJobOrderID1 = $this->addJobOrder(
            'Test JobOrder ATxyz',
            $testCompanyID,
            $testContactID,
            'H',
            TESTER_USER_ID,
            1,
            'Test City',
            'MN'
        );

        /* Add another test job order. */
        $testJobOrderID2 = $this->addJobOrder(
            'Test JobOrder ATxyx',
            $testCompanyID,
            $testContactID,
            'H',
            TESTER_USER_ID,
            1,
            'Test City',
            'AL'
        );

        /* Add another test job order. */
        $testJobOrderID3 = $this->addJobOrder(
            'Test JobOrder ATxyy',
            $testCompanyID,
            '-1',
            'H',
            TESTER_USER_ID,
            1,
            'Test City',
            'AL'
        );

        /* Consider the test candidate for the first test job order. */
        $this->assertGET(
            CATSUtility::getAbsoluteURI(CATSUtility::getIndexName() .'?m=joborders&a=addToPipeline'),
            array(
                'getback' => 'getback',
                'jobOrderID' => $testJobOrderID1,
                'candidateID' => $testCandidateID
            ),
            'Considering test candidate for test job order should succees'
        );
        $this->runPageLoadAssertions(false);

        /* POST the AJAX call to ajax.php. */
        $this->post(
            CATSUtility::getAbsoluteURI('ajax.php'),
            array(
                'f' => 'getDataItemJobOrders',
                'dataItemID' => $testCompanyID,
                'dataItemType' => DATA_ITEM_COMPANY
            )
        );

        /* Use SimpleXML to parse the XML response. */
        $xml = $this->getSimpleXML();

        /* Make sure the error code is 0 and there is no error message. */
        $this->runXMLLoadAssertions($xml, false);

        $this->assertEqual(
            (string) $xml->joborder[0]->id,
            (string) $testJobOrderID1
        );
        $this->assertEqual(
            (string) $xml->joborder[0]->title,
            (string) 'Test JobOrder ATxyz'
        );
        $this->assertEqual(
            (string) $xml->joborder[0]->companyname,
            'Test Company ATxyz'
        );
        $this->assertEqual(
            (string) $xml->joborder[0]->assigned,
            '0'
        );

        $this->assertEqual(
            (string) $xml->joborder[1]->id,
            (string) $testJobOrderID2
        );
        $this->assertEqual(
            (string) $xml->joborder[1]->title,
            (string) 'Test JobOrder ATxyx'
        );
        $this->assertEqual(
            (string) $xml->joborder[1]->companyname,
            'Test Company ATxyz'
        );
        $this->assertEqual(
            (string) $xml->joborder[1]->assigned,
            '0'
        );

        $this->assertEqual(
            (string) $xml->joborder[2]->id,
            (string) $testJobOrderID3
        );
        $this->assertEqual(
            (string) $xml->joborder[2]->title,
            (string) 'Test JobOrder ATxyy'
        );
        $this->assertEqual(
            (string) $xml->joborder[2]->companyname,
            'Test Company ATxyz'
        );
        $this->assertEqual(
            (string) $xml->joborder[2]->assigned,
            '0'
        );

        /* POST the AJAX call to ajax.php. */
        $this->post(
            CATSUtility::getAbsoluteURI('ajax.php'),
            array(
                'f' => 'getDataItemJobOrders',
                'dataItemID' => $testContactID,
                'dataItemType' => DATA_ITEM_CONTACT
            )
        );

        /* Use SimpleXML to parse the XML response. */
        $xml = $this->getSimpleXML();

        /* Make sure the error code is 0 and there is no error message. */
        $this->runXMLLoadAssertions($xml, false);

        $this->assertEqual(
            (string) $xml->joborder[0]->id,
            (string) $testJobOrderID1
        );
        $this->assertEqual(
            (string) $xml->joborder[0]->title,
            (string) 'Test JobOrder ATxyz'
        );
        $this->assertEqual(
            (string) $xml->joborder[0]->companyname,
            'Test Company ATxyz'
        );
        $this->assertEqual(
            (string) $xml->joborder[0]->assigned,
            '1'
        );

        $this->assertEqual(
            (string) $xml->joborder[1]->id,
            (string) $testJobOrderID2
        );
        $this->assertEqual(
            (string) $xml->joborder[1]->title,
            (string) 'Test JobOrder ATxyx'
        );
        $this->assertEqual(
            (string) $xml->joborder[1]->companyname,
            'Test Company ATxyz'
        );
        $this->assertEqual(
            (string) $xml->joborder[1]->assigned,
            '1'
        );

        $this->assertEqual(
            (string) $xml->joborder[2]->id,
            (string) $testJobOrderID3
        );
        $this->assertEqual(
            (string) $xml->joborder[2]->title,
            (string) 'Test JobOrder ATxyy'
        );
        $this->assertEqual(
            (string) $xml->joborder[2]->companyname,
            'Test Company ATxyz'
        );
        $this->assertEqual(
            (string) $xml->joborder[2]->assigned,
            '0'
        );

        /* POST the AJAX call to ajax.php. */
        $this->post(
            CATSUtility::getAbsoluteURI('ajax.php'),
            array(
                'f' => 'getDataItemJobOrders',
                'dataItemID' => $testCandidateID,
                'dataItemType' => DATA_ITEM_CANDIDATE
            )
        );

        /* Use SimpleXML to parse the XML response. */
        $xml = $this->getSimpleXML();

        /* Make sure the error code is 0 and there is no error message. */
        $this->runXMLLoadAssertions($xml, false);

        $this->assertEqual(
            (string) $xml->joborder[0]->id,
            (string) $testJobOrderID1
        );
        $this->assertEqual(
            (string) $xml->joborder[0]->title,
            (string) 'Test JobOrder ATxyz'
        );
        $this->assertEqual(
            (string) $xml->joborder[0]->companyname,
            'Test Company ATxyz'
        );
        $this->assertEqual(
            (string) $xml->joborder[0]->assigned,
            '0'
        );

        /* Delete the test candidate. */
        $this->deleteCandidate($testCandidateID);

        /* Delete the test company. This will also delete the associated contacts. */
        $this->deleteCompany($testCompanyID);

        /* We're done; log out. */
        $this->logout();
    }
}

class GetParsedAddressTest extends CATSAJAXTestCase
{
    function testGetParsedAddress1()
    {
        $address = <<<EOF
Enock R. Chamberlin
281 Kerby Road
Apt # C
Arlington, Texas  79999-5801
US
enock21@hooooootmail.com


Mobile: 817-715-6875
Home: 817-303-3864
Work: 8173933899
Contact Preference: Telephone
EOF;

        /* POST the AJAX call to ajax.php. */
        $this->post(
            CATSUtility::getAbsoluteURI('ajax.php'),
            array(
                'f' => 'getParsedAddress',
                'mode' => 'person',
                'addressBlock' => $address
            )
        );

        /* Use SimpleXML to parse the XML response. */
        $xml = $this->getSimpleXML();

        /* Make sure the error code is 0 and there is no error message. */
        $this->assertNoAJAXErrors($xml);

        $this->assertEqual(
            (string) $xml->name->first,
            'Enock'
        );
        $this->assertEqual(
            (string) $xml->name->middle,
            'R.'
        );
        $this->assertEqual(
            (string) $xml->name->last,
            'Chamberlin'
        );
        $this->assertEqual(
            (string) $xml->address->line[0],
            '281 Kerby Road'
        );
        $this->assertEqual(
            (string) $xml->address->line[1],
            'Apt # C'
        );
        $this->assertEqual(
            (string) $xml->city,
            'Arlington'
        );
        $this->assertEqual(
            (string) $xml->state,
            'Texas'
        );
        $this->assertEqual(
            (string) $xml->zip,
            '79999-5801'
        );
        $this->assertEqual(
            (string) $xml->email,
            'enock21@hooooootmail.com'
        );
        $this->assertEqual(
            (string) $xml->phonenumbers->cell,
            '817-715-6875'
        );
        $this->assertEqual(
            (string) $xml->phonenumbers->home,
            '817-303-3864'
        );
        $this->assertEqual(
            (string) $xml->phonenumbers->work,
            '817-393-3899'
        );
    }

    function testGetParsedAddress2()
    {
        $address = <<<EOF



blah test@test.org

EOF;

        /* POST the AJAX call to ajax.php. */
        $this->post(
            CATSUtility::getAbsoluteURI('ajax.php'),
            array(
                'f' => 'getParsedAddress',
                'mode' => 'person',
                'addressBlock' => $address
            )
        );

        /* Use SimpleXML to parse the XML response. */
        $xml = $this->getSimpleXML();

        /* Make sure the error code is 0 and there is no error message. */
        $this->assertNoAJAXErrors($xml);

        $this->assertEqual(
            (string) $xml->email,
            'test@test.org'
        );
    }
}

class GetPipelineDetailsTest extends CATSAJAXTestCase
{
    function testGetPipelineDetails()
    {
        /* Log in and make sure no errors occurred. */
        if (!$this->login())
        {
            return false;
        }

        /* Add a test company. */
        $testCompanyID = $this->addCompany(
            'Test Company ATxyz'
        );

        /* Add a test candidate. */
        $testCandidateID = $this->addCandidate(
            'TestCand',
            'ATxyz'
        );

        /* Add a test job order. */
        $testJobOrderID1 = $this->addJobOrder(
            'Test JobOrder ATxyz',
            $testCompanyID,
            '-1',
            'H',
            TESTER_USER_ID,
            1,
            'Test City',
            'MN'
        );

        /* Add another test job order. */
        $testJobOrderID2 = $this->addJobOrder(
            'Test JobOrder ATxyx',
            $testCompanyID,
            '-1',
            'H',
            TESTER_USER_ID,
            1,
            'Test City',
            'AL'
        );

        /* Consider the test candidate for the first test job order. */
        $this->assertGET(
            CATSUtility::getAbsoluteURI(CATSUtility::getIndexName() .'?m=joborders&a=addToPipeline'),
            array(
                'getback' => 'getback',
                'candidateID' => $testCandidateID,
                'jobOrderID' => $testJobOrderID1
            ),
            'Considering test candidate for test job order should succees'
        );
        $this->runPageLoadAssertions(false);

        /* Get the candidate-joborder ID of the pipeline entry we just created. */
        $this->post(
            CATSUtility::getAbsoluteURI('ajax.php'),
            array(
                'f' => 'tests:getCandidateJobOrderID',
                'candidateID' => $testCandidateID,
                'jobOrderID' => $testJobOrderID1
            )
        );
        $xml = $this->getSimpleXML();
        $this->runXMLLoadAssertions($xml, false);
        $candidateJobOrderID = $xml->id;

        /* POST the AJAX call to ajax.php. */
        $this->post(
            CATSUtility::getAbsoluteURI('ajax.php'),
            array(
                'f' => 'getPipelineDetails',
                'candidateJobOrderID' => $candidateJobOrderID
            )
        );
        $this->runPageLoadAssertions(false);

        /* There aren't any activity entries yet. */
        $this->assertPattern('/Added candidate to pipeline./');

        /* Add an activity. */
        $this->addPipelineActivity(
            $testCandidateID,
            $testJobOrderID1,
            ACTIVITY_CALL_TALKED,
            'Test notes.'
        );

        /* POST the AJAX call to ajax.php. */
        $this->post(
            CATSUtility::getAbsoluteURI('ajax.php'),
            array(
                'f' => 'getPipelineDetails',
                'candidateJobOrderID' => $candidateJobOrderID
            )
        );
        $this->runPageLoadAssertions(false);

        /* There should be activity entries now. */
        $this->assertNoPattern('/<td>No activity entries could be found.<\/td>/');
        $this->assertPattern('/>Test notes.</');
        $this->assertPattern('/\(' . TESTER_FULLNAME . '\)/');
        $this->assertPattern('/>Activity History:</');
        $this->assertPattern('/' . date('m-d-y') . '/');

        /* Delete the test candidate. */
        $this->deleteCandidate($testCandidateID);

        /* Delete the test company. This will also delete the associated job orders. */
        $this->deleteCompany($testCompanyID);

        /* We're done; log out. */
        $this->logout();
    }
}

class GetPipelineJobOrderTest extends CATSAJAXTestCase
{
    function testGetPipelineJobOrder()
    {
        /* Log in and make sure no errors occurred. */
        if (!$this->login())
        {
            return false;
        }
    }
}

class SetCandidateJobOrderRatingTest extends CATSAJAXTestCase
{
    function testSetCandidateJobOrderRating()
    {
        /* Log in and make sure no errors occurred. */
        if (!$this->login())
        {
            return false;
        }
    }
}

class TestEmailSettingsTest extends CATSAJAXTestCase
{
    function testTestEmailSettings()
    {
        /* Log in and make sure no errors occurred. */
        if (!$this->login())
        {
            return false;
        }
    }
}

class ZipLookupTest extends CATSAJAXTestCase
{
    function testZipLookup()
    {
    }
}

?>
