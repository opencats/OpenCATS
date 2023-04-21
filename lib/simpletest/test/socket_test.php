<?php
// $Id: socket_test.php 1782 2008-04-25 17:09:06Z pp11 $
require_once(__DIR__ . '/../autorun.php');
require_once(__DIR__ . '/../socket.php');
Mock::generate('SimpleSocket');

class TestOfSimpleStickyError extends UnitTestCase {
    
    function testSettingError() {
        $error = new SimpleStickyError();
        $this->assertFalse($error->isError());
        $error->setError('Ouch');
        $this->assertTrue($error->isError());
        $this->assertEqual($error->getError(), 'Ouch');
    }
    
    function testClearingError() {
        $error = new SimpleStickyError();
        $error->setError('Ouch');
        $this->assertTrue($error->isError());
        $error->clearError();
        $this->assertFalse($error->isError());
    }
}
?>