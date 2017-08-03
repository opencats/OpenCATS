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
if( ini_get('safe_mode') )
{
	//don't do anything in safe mode
}
else
{
	/* Allow this script to run longer. */
	set_time_limit(300);
}

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
include_once('./modules/tests/TestCaseList.php');


class TestsUI extends UserInterface
{
    private $_testCaseList;
    private $reporter;


    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = true;
        $this->_moduleName = 'tests';
        $this->_moduleDirectory = 'tests';
        $this->_testCaseList = new TestCaseList();
        
        $microTimeArray = explode(' ', microtime());
        $microTimeStart = $microTimeArray[1] + $microTimeArray[0];
        
        $this->reporter = new CATSTestReporter($microTimeStart);
        $this->reporter->showPasses = true;
        $this->reporter->showFails = true;
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
        $this->_template->assign('reporter', $this->reporter);
        $this->_template->assign('systemTestCases', $this->_testCaseList->getSystemTests());
        $this->_template->assign('AJAXTestCases', $this->_testCaseList->getAjaxTests());
        $this->_template->display('./modules/tests/Tests.tpl');
    }

    private function runSelectedTests()
    {
        include('./modules/tests/testcases/WebTests.php');
        include('./modules/tests/testcases/AJAXTests.php');

        /* FIXME: 2 groups! Web, AJAX. */
        $testSuite = new TestSuite('CATS Test Suite');

        foreach ($this->_testCaseList->getSystemTests() as $offset => $value)
        {
            if ($this->isChecked($value[0], $_POST))
            {
                $testSuite->add(new $value[0]());
            }
        }
        foreach ($this->_testCaseList->getAjaxTests() as $offset => $value)
        {
            if ($this->isChecked($value[0], $_POST))
            {
                $testSuite->add(new $value[0]());
            }
        }

        $testSuite->run($this->reporter);
    }
}

?>
