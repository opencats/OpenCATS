<?php
require_once(__DIR__ . '/../autorun.php');

class AllTests extends TestSuite {
    function __construct() {
        $this->TestSuite('All tests for SimpleTest ' . SimpleTest::getVersion());
        $this->addFile(__DIR__ . '/unit_tests.php');
        $this->addFile(__DIR__ . '/shell_test.php');
        $this->addFile(__DIR__ . '/live_test.php');
        $this->addFile(__DIR__ . '/acceptance_test.php');
    }
}
?>
