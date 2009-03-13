<?php
    // $Id: simpletest_test.php 801 2006-09-11 16:17:03Z will $
    require_once(dirname(__FILE__) . '/../simpletest.php');

    SimpleTest::ignore('ShouldNeverBeRunEither');

    class ShouldNeverBeRun extends UnitTestCase {
        function testWithNoChanceOfSuccess() {
            $this->fail('Should be ignored');
        }
    }

    class ShouldNeverBeRunEither extends ShouldNeverBeRun { }
?>