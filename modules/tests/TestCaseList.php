<?php
class TestCaseList
{
    public function getSystemTests() {
        return [['CandidatesWebTest', 'Candidates Module System Tests'], ['CompaniesWebTest', 'Companies Module System Tests'], ['ContactsWebTest', 'Contacts Module System Tests'], ['ReportsWebTest', 'Reports Module System Tests'], ['CalendarWebTest', 'Calendar Module System Tests'], ['SettingsWebTest', 'Settings Module System Tests']];
    }
    
    public function getAjaxTests() {
        return [['ActivityTest', 'Activity AJAX Tests'], ['GetCompanyContactsTest', 'GetCompanyContacts AJAX Tests'], ['GetCompanyLocationTest', 'GetCompanyLocation AJAX Tests'], ['GetCompanyLocationAndDepartmentsTest', 'GetCompanyLocationAndDepartments AJAX Tests'], ['GetCompanyNamesTest', 'GetCompanyNames AJAX Tests'], ['GetDataItemJobOrdersTest', 'GetDataItemJobOrders AJAX Tests'], ['GetParsedAddressTest', 'GetParsedAddress AJAX Tests'], ['GetPipelineDetailsTest', 'GetPipelineDetails AJAX Tests'], ['GetPipelineJobOrderTest', 'GetPipelineJobOrder AJAX Tests'], ['SetCandidateJobOrderRatingTest', 'SetCandidateJobOrderRating AJAX Tests'], ['TestEmailSettingsTest', 'TestEmailSettings AJAX Tests'], ['ZipLookupTest', 'ZipLookup AJAX Tests']];
    }
}
?>
