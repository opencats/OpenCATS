<?php
require_once(__DIR__ . '/../autorun.php');
require_once(__DIR__ . '/../default_reporter.php');

class TestOfCommandLineParsing extends UnitTestCase {
    
    function testDefaultsToEmptyStringToMeanNullToTheSelectiveReporter() {
        $parser = new SimpleCommandLineParser([]);
        $this->assertIdentical($parser->getTest(), '');
        $this->assertIdentical($parser->getTestCase(), '');
    }
    
    function testNotXmlByDefault() {
        $parser = new SimpleCommandLineParser([]);
        $this->assertFalse($parser->isXml());
    }
    
    function testCanDetectRequestForXml() {
        $parser = new SimpleCommandLineParser(['--xml']);
        $this->assertTrue($parser->isXml());
    }
    
    function testCanReadAssignmentSyntax() {
        $parser = new SimpleCommandLineParser(['--test=myTest']);
        $this->assertEqual($parser->getTest(), 'myTest');
    }
    
    function testCanReadFollowOnSyntax() {
        $parser = new SimpleCommandLineParser(['--test', 'myTest']);
        $this->assertEqual($parser->getTest(), 'myTest');
    }
    
    function testCanReadShortForms() {
        $parser = new SimpleCommandLineParser(['-t', 'myTest', '-c', 'MyClass', '-x']);
        $this->assertEqual($parser->getTest(), 'myTest');
        $this->assertEqual($parser->getTestCase(), 'MyClass');
        $this->assertTrue($parser->isXml());
    }
}
?>