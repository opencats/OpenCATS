<?php
/*
 * CATS
 * Test Framework Module
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * $Id: TestsUI.php 3241 2007-10-19 08:38:32Z will $
 */

/* Allow this script to run as long as possible. */
set_time_limit(300);

/* SimpleTest */
error_reporting(E_ALL); /* Simpletest doesn't work with E_STRICT. */
require_once('lib/simpletest/web_tester.php');
require_once('lib/simpletest/unit_tester.php');
require_once('lib/simpletest/reporter.php');
require_once('lib/simpletest/form.php');

/* CATS Test Framework. */
include_once('./modules/tests/CATSTestReporter.php');
include_once('./modules/tests/CATSWebTestCase.php');
include_once('./modules/tests/CATSAJAXTestCase.php');
include_once('./modules/tests/CATSUnitTestCase.php');


class TestsUI extends UserInterface
{
    private $_unitTestCases;
    private $_systemTestCases;
    private $_AJAXTestCases;


    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = true;
        $this->_moduleName = 'tests';
        $this->_moduleDirectory = 'tests';

        $this->_unitTestCases = array(
            array('AddressParserTest',      'AddressParser Unit Tests'),
            array('AJAXInterfaceTest',      'AJAX Interface Unit Tests'),
            array('AttachmentsTest',        'Attachments Unit Tests'),
            array('ArrayUtilityTest',       'ArrayUtility Unit Tests'),
            array('BrowserDetectionTest',   'Browser Detection Unit Tests'),
            array('CalendarTest',           'Calendar Unit Tests'),
            array('DatabaseConnectionTest', 'DatabaseConnection Unit Tests'),
            array('DatabaseSearchTest',     'DatabaseSearch Unit Tests'),
            array('DateUtilityTest',        'DateUtility Unit Tests'),
            array('EmailTemplatesTest',     'EmailTemplates Unit Tests'),
            array('EncryptionTest',         'Encryption Unit Tests'),
            array('ExportTest',             'Export Unit Tests'),
            array('FileUtilityTest',        'FileUtility Unit Tests'),
            array('HashUtilityTest',        'HashUtility Unit Tests'),
            array('ResultSetUtilityTest',   'ResultSetUtility Unit Tests'),
            array('StringUtilityTest',      'StringUtility Unit Tests'),
            array('VCardTest',              'VCard Unit Tests')
        );
        $this->_systemTestCases = array(
            array('LoginWebTest',      'Login Module System Tests'),
            array('HomeWebTest',       'Home Module System Tests'),
            array('ActivitiesWebTest', 'Activities Module System Tests'),
            array('JobOrdersWebTest',  'Job Orders Module System Tests'),
            array('CandidatesWebTest', 'Candidates Module System Tests'),
            array('CompaniesWebTest',  'Companies Module System Tests'),
            array('ContactsWebTest',   'Contacts Module System Tests'),
            array('ReportsWebTest',    'Reports Module System Tests'),
            array('CalendarWebTest',   'Calendar Module System Tests'),
            array('SettingsWebTest',   'Settings Module System Tests'),
        );
        $this->_AJAXTestCases = array(
            array('ActivityTest',                         'Activity AJAX Tests'),
            array('GetCompanyContactsTest',               'GetCompanyContacts AJAX Tests'),
            array('GetCompanyLocationTest',               'GetCompanyLocation AJAX Tests'),
            array('GetCompanyLocationAndDepartmentsTest', 'GetCompanyLocationAndDepartments AJAX Tests'),
            array('GetCompanyNamesTest',                  'GetCompanyNames AJAX Tests'),
            array('GetDataItemJobOrdersTest',             'GetDataItemJobOrders AJAX Tests'),
            array('GetParsedAddressTest',                 'GetParsedAddress AJAX Tests'),
            array('GetPipelineDetailsTest',               'GetPipelineDetails AJAX Tests'),
            array('GetPipelineJobOrderTest',              'GetPipelineJobOrder AJAX Tests'),
            array('SetCandidateJobOrderRatingTest',       'SetCandidateJobOrderRating AJAX Tests'),
            array('TestEmailSettingsTest',                'TestEmailSettings AJAX Tests'),
            array('ZipLookupTest',                        'ZipLookup AJAX Tests')
        );
    }


    public function handleRequest()
    {
        $action = $this->getAction();
        switch ($action)
        {
            case 'runSelectedTests':
                $this->runSelectedTests();
                break;

            /* Main tests page. */
            case 'selectTests':
            default:
                $this->selectTests();
                break;
        }
    }

    private function selectTests()
    {
        $this->_template->assign('unitTestCases', $this->_unitTestCases);
        $this->_template->assign('systemTestCases', $this->_systemTestCases);
        $this->_template->assign('AJAXTestCases', $this->_AJAXTestCases);
        $this->_template->display('./modules/tests/Tests.tpl');
    }

    private function runSelectedTests()
    {
        include('./modules/tests/testcases/UnitTests.php');
        include('./modules/tests/testcases/WebTests.php');
        include('./modules/tests/testcases/AJAXTests.php');

        $microTimeArray = explode(' ', microtime());
        $microTimeStart = $microTimeArray[1] + $microTimeArray[0];

        /* FIXME: 3 groups! Unit, Web, AJAX. */
        $groupTest = new GroupTest('CATS Test Suite');

        foreach ($this->_unitTestCases as $offset => $value)
        {
            if ($this->isChecked($value[0], $_POST))
            {
                $groupTest->addTestCase(new $value[0]());
            }
        }
        foreach ($this->_systemTestCases as $offset => $value)
        {
            if ($this->isChecked($value[0], $_POST))
            {
                $groupTest->addTestCase(new $value[0]());
            }
        }
        foreach ($this->_AJAXTestCases as $offset => $value)
        {
            if ($this->isChecked($value[0], $_POST))
            {
                $groupTest->addTestCase(new $value[0]());
            }
        }

        $reporter = new CATSTestReporter($microTimeStart);
        $reporter->showPasses = true;
        $reporter->showFails = true;

        $groupTest->run($reporter);
    }
}

?>
