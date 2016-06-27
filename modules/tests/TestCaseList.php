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
}