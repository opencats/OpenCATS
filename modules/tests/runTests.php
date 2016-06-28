<?php
set_time_limit(60);

/* SimpleTest */
error_reporting(E_ALL); /* Simpletest doesn't work with E_STRICT. */
require_once('./lib/simpletest/unit_tester.php');
require_once('./lib/simpletest/reporter.php');
require_once('./lib/simpletest/form.php');

/* CATS Test Framework. */
include_once('CATSUnitTestCase.php');
include_once('./modules/tests/TestCaseList.php');
include_once('./modules/tests/testcases/UnitTests.php');

$testCaseList = new TestCaseList();
$groupTest = new TestSuite('CATS Test Suite');
foreach ($testCaseList->getUnitTests() as $offset => $value)
{
    $groupTest->add(new $value[0]());
}
$reporter = new TextReporter();
$groupTest->run($reporter);


?>
