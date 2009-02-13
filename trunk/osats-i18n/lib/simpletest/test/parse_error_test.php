<?php
    // $Id: parse_error_test.php 424 2006-07-21 02:20:17Z will $
    
    require_once('../unit_tester.php');
    require_once('../reporter.php');

    $test = &new GroupTest('This should fail');
    $test->addTestFile('test_with_parse_error.php');
    $test->run(new HtmlReporter());
?>