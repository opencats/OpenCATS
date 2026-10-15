@security
Feature: Access Level to objects check - main pages
  In order to protect sensitive information from users who should not have access to them
  All objects in the system need to be controlled by the Access Level of user
  
  ######## DASHBOARD(HOME) #######

  @dashboard
  Scenario Outline: Dashboard module visibility
    Given I am logged in with <accessLevel> access level
    And I am on "/index.php?m=home"
    Then the page should <quickSearch> contain "Quick Search"
    And the page should <quickSearch> contain "quickSearchFor"
    And the page should <quickSearch> contain "quickSearch"
    And I should <recentCalls> "My Recent Calls"
    And I should <upcomingCalls> "My Upcoming Calls"
    And I should <upcomingEvents> "My Upcoming Events"
    And I should <recentHires> "Recent Hires"
    And I should <hiringOverview> "Hiring Overview"
    And I should <importantCandidates> "Important Candidates"
    
    Examples:
     | accessLevel | quickSearch | recentCalls | upcomingCalls | upcomingEvents | recentHires | hiringOverview | importantCandidates| 
     | DISABLED    | not         | not see     | not see       | not see        | not see     | not see        | not see            |
     | READONLY    |             | see         | see           | see            | see         | see            | see                | 
     | EDIT        |             | see         | see           | see            | see         | see            | see                |
     | DELETE      |             | see         | see           | see            | see         | see            | see                |
     | DEMO        |             | see         | see           | see            | see         | see            | see                |
     | ADMIN       |             | see         | see           | see            | see         | see            | see                |
     | MULTI_ADMIN |             | see         | see           | see            | see         | see            | see                |
     | ROOT        |             | see         | see           | see            | see         | see            | see                |
     
  ####### ACTIVITIES #######
    
  @activities
  Scenario Outline: Activities module visibility
    Given I am logged in with <accessLevel> access level
    And I am on "/index.php?m=activity"
    Then the page should <quickSearch> contain "Quick Search"
    And the page should <quickSearch> contain "quickSearchFor"
    And the page should <quickSearch> contain "quickSearch"
    And I should <timeFilter> "Today"
    And I should <timeFilter> "Yesterday"
    And I should <timeFilter> "Last Week"
    And I should <timeFilter> "Last Month"
    And I should <timeFilter> "Last 6 Months"
    And I should <timeFilter> "All"
    And I should <filter> "More filters"
    And I should <rowsPerPage> a "select[aria-label='Rows per page']" element
    And I should <action> "Action"
    And I should <activities> "Activities - Page"
    And I should <alphabetFilter> "ALL"
  
    Examples:
     | accessLevel | quickSearch | timeFilter  | filter  | rowsPerPage | action  | activities | alphabetFilter |
     | DISABLED    | not         | not see     | not see | not see     | not see | not see    | not see        |
     | READONLY    |             | see         | see     | see         | see     | see        | see            |
     | EDIT        |             | see         | see     | see         | see     | see        | see            |
     | DELETE      |             | see         | see     | see         | see     | see        | see            |
     | DEMO        |             | see         | see     | see         | see     | see        | see            |
     | ADMIN       |             | see         | see     | see         | see     | see        | see            |
     | MULTI_ADMIN |             | see         | see     | see         | see     | see        | see            |
     | ROOT        |             | see         | see     | see         | see     | see        | see            |
     
  ####### JOB ORDERS #######
    
  @joborders
  Scenario Outline: Job Orders module visibility
    Given I am logged in with <accessLevel> access level
    And I am on "/index.php?m=joborders"
    Then I should <addJobOrder> "Add Job Order"
    And I should <searchJobOrder> "Search Job Orders"
    And the page should <quickSearch> contain "Quick Search"
    And the page should <quickSearch> contain "quickSearchFor"
    And the page should <quickSearch> contain "quickSearch"
    And I should <jobOrdersHome> "Job Orders: Home"
    And I should <selectView> a "select#view" element
    And the page should <onlyMyJobOrders> contain "onlyMyJobOrders"
    And the page should <onlyHotJobOrders> contain "onlyHotJobOrders"
    And I should <filter> "More filters"
    And I should <rowsPerPage> a "select[aria-label='Rows per page']" element
    And I should <action> "Action"
    And I should <jobOrders> "Job Orders - Page"
    And the page should <addToList> contain "Add To List"
    And the page should <export> contain "Export"
    And I should <alphabetFilter> "ALL"
     
  Examples:
     | accessLevel | addJobOrder  | searchJobOrder  | quickSearch | jobOrdersHome | selectView | onlyMyJobOrders | onlyHotJobOrders | filter | rowsPerPage | action | jobOrders | addToList | export | alphabetFilter |
     | DISABLED    | not see      | not see         | not         | not see       | not see    | not             | not              | not see| not see     | not see| not see   | not       | not    | not see        |
     | READONLY    | not see      | see             |             | see           | see        |                 |                  | see    | see         | see    | see       |           |        | see            |
     | EDIT        | see          | see             |             | see           | see        |                 |                  | see    | see         | see    | see       |           |        | see            |
     | DELETE      | see          | see             |             | see           | see        |                 |                  | see    | see         | see    | see       |           |        | see            |
     | DEMO        | see          | see             |             | see           | see        |                 |                  | see    | see         | see    | see       |           |        | see            |
     | ADMIN       | see          | see             |             | see           | see        |                 |                  | see    | see         | see    | see       |           |        | see            |
     | MULTI_ADMIN | see          | see             |             | see           | see        |                 |                  | see    | see         | see    | see       |           |        | see            |
     | ROOT        | see          | see             |             | see           | see        |                 |                  | see    | see         | see    | see       |           |        | see            |
     
  ####### CANDIDATES #######
     
  @candidates
  Scenario Outline: Candidates module visibility
    Given I am logged in with <accessLevel> access level
    And I am on "/index.php?m=candidates"
    Then I should <addCandidate> "Add Candidate"
    And I should <searchCandidate> "Search Candidates"
    And the page should <quickSearch> contain "Quick Search"
    And the page should <quickSearch> contain "quickSearchFor"
    And the page should <quickSearch> contain "quickSearch"
    And I should <candidatesHome> a "main.oc-candidates-page h1" element
    And I should <filterByTag> "Filter by tag"
    And the page should <onlyMyCandidates> contain "onlyMyCandidates"
    And the page should <onlyHotCandidates> contain "onlyHotCandidates"
    And I should <filter> "More filters"
    And I should <rowsPerPage> a "select[aria-label='Rows per page']" element
    And I should <action> "Action"
    And I should <candidates> a ".oc-candidates-datagrid table" element
    And the page should <addToList> contain "Add To List"
    And the page should <export> contain "Export"
    And the page should <addToPipeline> contain "Add To Job Order"
    And the page should <sendEmail> contain "Send E-Mail"
    And I should <alphabetFilter> "ALL"
    
   Examples:
     | accessLevel | addCandidate | searchCandidate | quickSearch | candidatesHome | filterByTag | onlyMyCandidates| onlyHotCandidates| filter | rowsPerPage | action | candidates | addToList | export | addToPipeline | sendEmail | alphabetFilter |
     | DISABLED    | not see      | not see         | not         | not see        | not see     | not             | not              | not see| not see     | not see| not see    | not       | not    | not           | not       | not see        |
     | READONLY    | not see      | see             |             | see            | see         |                 |                  | see    | see         | see    | see        |           |        | not           | not       | see            |  
     | EDIT        | see          | see             |             | see            | see         |                 |                  | see    | see         | see    | see        |           |        |               | not       | see            | 
     | DELETE      | see          | see             |             | see            | see         |                 |                  | see    | see         | see    | see        |           |        |               | not       | see            | 
     | DEMO        | see          | see             |             | see            | see         |                 |                  | see    | see         | see    | see        |           |        |               | not       | see            | 
     | ADMIN       | see          | see             |             | see            | see         |                 |                  | see    | see         | see    | see        |           |        |               |           | see            | 
     | MULTI_ADMIN | see          | see             |             | see            | see         |                 |                  | see    | see         | see    | see        |           |        |               |           | see            | 
     | ROOT        | see          | see             |             | see            | see         |                 |                  | see    | see         | see    | see        |           |        |               |           | see            | 

    ####### COMPANIES #######
    
    @companies
    Scenario Outline: Companies module visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=companies"
     Then I should <addCompany> "Add Company"
     And I should <searchCompany> "Search Companies"
     And the page should <quickSearch> contain "Quick Search"
     And the page should <quickSearch> contain "quickSearchFor"
     And the page should <quickSearch> contain "quickSearch"
     And I should <companiesHome> the page heading "Companies"
     And the page should <onlyMyCompanies> contain "onlyMyCompanies"
     And the page should <onlyHotCompanies> contain "onlyHotCompanies"
     And I should <filter> "More filters"
     And I should <rowsPerPage> a "select[aria-label='Rows per page']" element
     And I should <action> "Action"
     And I should <companies> a "main.oc-companies-page table" element
     And the page should <addToList> contain "Add To List"
     And the page should <export> contain "Export"
     And I should <alphabetFilter> "ALL"
    
    Examples:
     | accessLevel | addCompany   | searchCompany   | quickSearch | companiesHome | onlyMyCompanies | onlyHotCompanies | filter | rowsPerPage | action | companies | addToList | export | alphabetFilter |
     | DISABLED    | not see      | not see         | not         | not see       | not             | not              | not see| not see     | not see| not see   | not       | not    | not see        |
     | READONLY    | not see      | see             |             | see           |                 |                  | see    | see         | see    | see       |           |        | see            |
     | EDIT        | see          | see             |             | see           |                 |                  | see    | see         | see    | see       |           |        | see            |
     | DELETE      | see          | see             |             | see           |                 |                  | see    | see         | see    | see       |           |        | see            |
     | DEMO        | see          | see             |             | see           |                 |                  | see    | see         | see    | see       |           |        | see            |
     | ADMIN       | see          | see             |             | see           |                 |                  | see    | see         | see    | see       |           |        | see            |
     | MULTI_ADMIN | see          | see             |             | see           |                 |                  | see    | see         | see    | see       |           |        | see            |
     | ROOT        | see          | see             |             | see           |                 |                  | see    | see         | see    | see       |           |        | see            |
     
   ####### CONTACTS #######
     
   @contacts
   Scenario Outline: Contacts module visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=contacts"
     Then I should <addContact> "Add Contact"
     And I should <searchContact> "Search Contacts"
     And I should <coldCallList> "Cold Call List"
     And the page should <quickSearch> contain "Quick Search"
     And the page should <quickSearch> contain "quickSearchFor"
     And the page should <quickSearch> contain "quickSearch"
     And I should <contactsHome> a "main.oc-contacts-page h1" element
     And the page should <onlyMyContacts> contain "onlyMyContacts"
     And the page should <onlyHotContacts> contain "onlyHotContacts"
     And I should <filter> "More filters"
     And I should <rowsPerPage> a "select[aria-label='Rows per page']" element
     And I should <action> "Action"
     And I should <contacts> a ".oc-contacts-datagrid table" element
     And the page should <addToList> contain "Add To List"
     And the page should <export> contain "Export"
     And I should <alphabetFilter> "ALL"
    
    Examples:
     | accessLevel | addContact   | searchContact   | coldCallList | quickSearch | contactsHome | onlyMyContacts  | onlyHotContacts  | filter | rowsPerPage | action | contacts  | addToList | export | alphabetFilter |
     | DISABLED    | not see      | not see         | not see      | not         | not see      | not             | not              | not see| not see     | not see| not see   | not       | not    | not see        |
     | READONLY    | not see      | see             | see          |             | see          |                 |                  | see    | see         | see    | see       |           |        | see            |
     | EDIT        | see          | see             | see          |             | see          |                 |                  | see    | see         | see    | see       |           |        | see            |
     | DELETE      | see          | see             | see          |             | see          |                 |                  | see    | see         | see    | see       |           |        | see            |
     | DEMO        | see          | see             | see          |             | see          |                 |                  | see    | see         | see    | see       |           |        | see            |
     | ADMIN       | see          | see             | see          |             | see          |                 |                  | see    | see         | see    | see       |           |        | see            |
     | MULTI_ADMIN | see          | see             | see          |             | see          |                 |                  | see    | see         | see    | see       |           |        | see            |
     | ROOT        | see          | see             | see          |             | see          |                 |                  | see    | see         | see    | see       |           |        | see            |
     
     ####### LISTS #######
     
    @lists
    Scenario Outline: Lists module visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=lists"
     Then I should <showLists> "Show Lists"
     And the page should <quickSearch> contain "Quick Search"
     And the page should <quickSearch> contain "quickSearchFor"
     And the page should <quickSearch> contain "quickSearch"
     And I should <listsHome> "Lists: Home"
     And I should <rowsPerPage> a "select[aria-label='Rows per page']" element
     And I should <lists> "Lists - Page"
     And I should <alphabetFilter> "ALL"
    
    Examples:
     | accessLevel | showLists    | quickSearch | listsHome | rowsPerPage | lists    | alphabetFilter |
     | DISABLED    | not see      | not         | not see   | not see     | not see  | not see        |
     | READONLY    | see          |             | see       | see         | see      | see            |
     | EDIT        | see          |             | see       | see         | see      | see            |
     | DELETE      | see          |             | see       | see         | see      | see            |
     | DEMO        | see          |             | see       | see         | see      | see            |
     | ADMIN       | see          |             | see       | see         | see      | see            |
     | MULTI_ADMIN | see          |             | see       | see         | see      | see            |
     | ROOT        | see          |             | see       | see         | see      | see            |
     
       ####### REPORTS #######
  
  @reports
    Scenario Outline: Reports module visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=reports"
     And the page should <quickSearch> contain "Quick Search"
     And the page should <quickSearch> contain "quickSearchFor"
     And the page should <quickSearch> contain "quickSearch"
     And I should <timePeriods> "Today"
     And I should <timePeriods> "Yesterday"
     And I should <timePeriods> "This Week"
     And I should <timePeriods> "Last Week"
     And I should <timePeriods> "This Month"
     And I should <timePeriods> "Last Month"
     And I should <timePeriods> "This Year"
     And I should <timePeriods> "Last Year"
     And I should <timePeriods> "To Date"
    
    Examples:
     | accessLevel | quickSearch  | timePeriods |
     | DISABLED    | not          | not see     |
     | READONLY    |              | see         |
     | EDIT        |              | see         |
     | DELETE      |              | see         |
     | DEMO        |              | see         |
     | ADMIN       |              | see         |
     | MULTI_ADMIN |              | see         |
     | ROOT        |              | see         |
     
####### SETTINGS #######  

@settings
    Scenario Outline: Settings module visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=settings"
     Then I should <viewProfile> "View Profile"
     And I should <changePassword> "Change Password"
     And I should <administration> "Administration"
     And I should <myProfile> "My Profile"
     And the page should <quickSearch> contain "Quick Search"
     And the page should <quickSearch> contain "quickSearchFor"
     And the page should <quickSearch> contain "quickSearch"
     And I should <settingsHome> "Settings: My Profile"
    
    Examples:
     | accessLevel | viewProfile  | changePassword  | administration | myProfile | quickSearch  | settingsHome |
     | DISABLED    | not see      | not see         | not see        | not see   | not          | not see      |
     | READONLY    | see          | see             | not see        | see       |              | see          |
     | EDIT        | see          | see             | not see        | see       |              | see          |
     | DELETE      | see          | see             | not see        | see       |              | see          |
     | DEMO        | see          | see             | see            | see       |              | see          |
     | ADMIN       | see          | see             | see            | see       |              | see          |
     | MULTI_ADMIN | see          | see             | see            | see       |              | see          |
     | ROOT        | see          | see             | see            | see       |              | see          |
     
####### CALENDAR #######

  @javascript @calendar
    Scenario Outline: Calendar module visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=calendar"
     Then I should <upcomingEvents> "My Upcoming Events"
     And I should <addEvent> "Add Event"
     And I should <gotoToday> "Goto Today"
     And I should <myUpcomingEvents> "My Upcoming Events / Calls"
     And the page should <quickSearch> contain "Quick Search"
     And the page should <quickSearch> contain "quickSearchFor"
     And the page should <quickSearch> contain "quickSearch"
     And the page should <hideNonPublic> contain "hideNonPublic"
     And the page should <switchPeriod> contain "userCalendarViewDay"
     And the page should <switchPeriod> contain "userCalendarViewWeek"
     And the page should <switchPeriod> contain "userCalendarViewMonth"
     And the page should <switchMonth> contain "linkMonthBack"
     And the page should <switchMonth> contain "linkMonthForeward"
     And the page should <calendarTable> contain "calendarMonth"
     
     
    
    Examples:
     | accessLevel | upcomingEvents | addEvent | gotoToday | myUpcomingEvents | quickSearch  | hideNonPublic | switchPeriod | switchMonth | calendarTable |
     | DISABLED    | not see        | not see  | not see   | not see          | not          | not           | not          | not         | not           |
     | READONLY    | see            | not see  | see       | see              |              |               |              |             |               |
     | EDIT        | see            | see      | see       | see              |              |               |              |             |               |
     | DELETE      | see            | see      | see       | see              |              |               |              |             |               |
     | DEMO        | see            | see      | see       | see              |              |               |              |             |               |
     | ADMIN       | see            | see      | see       | see              |              |               |              |             |               |
     | MULTI_ADMIN | see            | see      | see       | see              |              |               |              |             |               |
     | ROOT        | see            | see      | see       | see              |              |               |              |             |               |
