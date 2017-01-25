<?php
class TestCaseList
{
    public function getSystemTests() {
        return array(
            array('CandidatesWebTest', 'Candidates Module System Tests'),
            array('CompaniesWebTest',  'Companies Module System Tests'),
            array('ContactsWebTest',   'Contacts Module System Tests'),
            array('ReportsWebTest',    'Reports Module System Tests'),
            array('CalendarWebTest',   'Calendar Module System Tests'),
            array('SettingsWebTest',   'Settings Module System Tests'),
        );
    }
    
    public function getAjaxTests() {
        return array(
            array('ActivityTest',                         'Activity AJAX Tests'),
            array('GetCompanyContactsTest',               'GetCompanyContacts AJAX Tests'),
            array('GetCompanyLocationTest',               'GetCompanyLocation AJAX Tests'),
            array('GetCompanyLocationAndDepartmentsTest', 'GetCompanyLocationAndDepartments AJAX Tests'),
            array('GetCompanyNamesTest',                  'GetCompanyNames AJAX Tests'),
            array('GetDataItemJobOrdersTest',             'GetDataItemJobOrders AJAX Tests'),
            array('GetParsedAddressTest',                 'GetParsedAddress AJAX Tests'),
            array('GetPipelineDetailsTest',               'GetPipelineDetails AJAX Tests'),
            array('GetPipelineJobOrderTest',              'GetPipelineJobOrder AJAX Tests'),
            array('SetCandidateJobOrderRatingTest',       'SetCandidateJobOrderRating AJAX Tests'),
            array('TestEmailSettingsTest',                'TestEmailSettings AJAX Tests'),
            array('ZipLookupTest',                        'ZipLookup AJAX Tests')
        );
    }
}
?>
