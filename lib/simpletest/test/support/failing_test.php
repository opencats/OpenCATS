<?php
require_once(__DIR__ . '/../../autorun.php');

class FailingTest extends UnitTestCase {
    function test_fail() {
        $this->assertEqual(1,2);
    }
}
?>