<?php
class TestCaseList
{
    public function getIntegrationTests() {
        return array(
            array('DatabaseConnectionTest', 'DatabaseConnection Unit Tests'),
            array('DatabaseSearchTest',     'DatabaseSearch Unit Tests'),
            array('DateUtilityTest',        'DateUtility Unit Tests'),
        );
    }
    
    public function getUnitTests() {
        return array(
            array('AddressParserTest',      'AddressParser Unit Tests'),
            array('AJAXInterfaceTest',      'AJAX Interface Unit Tests'),
            array('AttachmentsTest',        'Attachments Unit Tests'),
            array('ArrayUtilityTest',       'ArrayUtility Unit Tests'),
            array('BrowserDetectionTest',   'Browser Detection Unit Tests'),
            array('CalendarTest',           'Calendar Unit Tests'),
            array('EmailTemplatesTest',     'EmailTemplates Unit Tests'),
            array('EncryptionTest',         'Encryption Unit Tests'),
            array('ExportTest',             'Export Unit Tests'),
            array('FileUtilityTest',        'FileUtility Unit Tests'),
            array('ResultSetUtilityTest',   'ResultSetUtility Unit Tests'),
            array('StringUtilityTest',      'StringUtility Unit Tests'),
            array('VCardTest',              'VCard Unit Tests')
        );
    }
}