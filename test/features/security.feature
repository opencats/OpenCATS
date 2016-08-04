@security
Feature: Access Level to objects check
  In order to protect sensitive information from users who should not have access to them
  All objects in the system need to be controlled by the Access Level of user

  ######## DASHBOARD(HOME) #######

  @javascript @dashboard
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

  @javascript @activities
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
    And I should <filter> "Filter"
    And I should <rowsPerPage> "Rows Per Page"
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
     
  @javascript $joborders
  Scenario Outline: Job Orders module visibility
    Given I am logged in with <accessLevel> access level
    And I am on "/index.php?m=joborders"
    Then I should <addJobOrder> "Add Job Order"
    And I should <searchJobOrder> "Search Job Orders"
    And the page should <quickSearch> contain "Quick Search"
    And the page should <quickSearch> contain "quickSearchFor"
    And the page should <quickSearch> contain "quickSearch"
    And I should <jobOrdersHome> "Job Orders: Home"
    And the page should <selectView> contain "view"
    And the page should <onlyMyJobOrders> contain "onlyMyJobOrders"
    And the page should <onlyHotJobOrders> contain "onlyHotJobOrders"
    And I should <filter> "Filter"
    And I should <rowsPerPage> "Rows Per Page"
    And I should <action> "Action"
    And I should <jobOrders> "Job Orders - Page"
    And the page should <addToList> contain "Add To List"
    And the page should <export> contain "Export"
    And I should <alphabetFilter> "ALL"
     
  Examples:
     | accessLevel | addJobOrder  | searchJobOrder  | quickSearch | jobOrdersHome | selectView | onlyMyJobOrders | onlyHotJobOrders | filter | rowsPerPage | action | jobOrders | addToList | export | alphabetFilter |
     | DISABLED    | not see      | not see         | not         | not see       | not        | not             | not              | not see| not see     | not see| not see   | not       | not    | not see        |
     | READONLY    | not see      | see             |             | see           |            |                 |                  | see    | see         | see    | see       |           |        | see            |
     | EDIT        | see          | see             |             | see           |            |                 |                  | see    | see         | see    | see       |           |        | see            |
     | DELETE      | see          | see             |             | see           |            |                 |                  | see    | see         | see    | see       |           |        | see            |
     | DEMO        | see          | see             |             | see           |            |                 |                  | see    | see         | see    | see       |           |        | see            |
     | ADMIN       | see          | see             |             | see           |            |                 |                  | see    | see         | see    | see       |           |        | see            |
     | MULTI_ADMIN | see          | see             |             | see           |            |                 |                  | see    | see         | see    | see       |           |        | see            |
     | ROOT        | see          | see             |             | see           |            |                 |                  | see    | see         | see    | see       |           |        | see            |
    
  @javascript @joborders
  Scenario Outline: Job Order Show page visibility
    Given I am logged in with <accessLevel> access level
    And I am on "/index.php?m=joborders"
    When I follow "OpenCATS Tester"
    Then I should <addJobOrder> "Add Job Order"
    And I should <searchJobOrder> "Search Job Orders"
    And the page should <quickSearch> contain "Quick Search"
    And the page should <quickSearch> contain "quickSearchFor"
    And the page should <quickSearch> contain "quickSearch"
    And the page should <actionMenu> contain "showHideSingleQuickActionMenu"
    And I should <addAttachment> "Add Attachment"
    And I should <generateReport> "Generate Report"
    And I should <viewHistory> "View History"
    And I should <editJobOrder> "Edit"
    And I should <deleteJobOrder> "Delete"
    And I should <administrativeHideShow> "Administrative"
    And I should <addToPipeline> "Add Candidate to This Job Order Pipeline"
    And I should <export> "Export"
    And I should <details> "Job Order Details"
    And the page should <logAnActivity> contain "Log an Activity"  
    And the page should <removeFromPipeline> contain "Remove from Pipeline"
    And the page should <setMatchingRating> contain "<map"
    And the page should <deleteAttachment> contain "index.php?m=joborders&amp;a=deleteAttachment"

     
  Examples:
     | accessLevel | addJobOrder | searchJobOrder | quickSearch | actionMenu  | addAttachment | generateReport | viewHistory | editJobOrder  | deleteJobOrder  | administrativeHideShow | addToPipeline | export | logAnActivity | removeFromPipeline | setMatchingRating | details | deleteAttachment |
     | DISABLED    | not see     | not see        | not         | not         | not see       | not see        | not see     | not see       | not see         | not see                | not see       | not see|not            | not                | not               | not see | not              | 
     | READONLY    | not see     | see            |             |             | not see       | see            | not see     | not see       | not see         | not see                | not see       | see    |not            | not                | not               | see     | not              |
     | EDIT        | see         | see            |             |             | see           | see            | not see     | see           | not see         | not see                | see           | see    |               | not                |                   | see     | not              |
     | DELETE      | see         | see            |             |             | see           | see            | not see     | see           | see             | not see                | see           | see    |               |                    |                   | see     |                  |
     | DEMO        | see         | see            |             |             | see           | see            | see         | see           | see             | not see                | see           | see    |               |                    |                   | see     |                  |
     | ADMIN       | see         | see            |             |             | see           | see            | see         | see           | see             | not see                | see           | see    |               |                    |                   | see     |                  |
     | MULTI_ADMIN | see         | see            |             |             | see           | see            | see         | see           | see             | see                    | see           | see    |               |                    |                   | see     |                  |
     | ROOT        | see         | see            |             |             | see           | see            | see         | see           | see             | see                    | see           | see    |               |                    |                   | see     |                  |
  
  ####### CANDIDATES #######
  
  @javascript @candidates
  Scenario Outline: Candidates module visibility
    Given I am logged in with <accessLevel> access level
    And I am on "/index.php?m=candidates"
    Then I should <addCandidate> "Add Candidate"
    And I should <searchCandidate> "Search Candidates"
    And the page should <quickSearch> contain "Quick Search"
    And the page should <quickSearch> contain "quickSearchFor"
    And the page should <quickSearch> contain "quickSearch"
    And I should <candidatesHome> "Candidates: Home"
    And I should <filterByTag> "Filter by tag"
    And the page should <onlyMyCandidates> contain "onlyMyCandidates"
    And the page should <onlyHotCandidates> contain "onlyHotCandidates"
    And I should <filter> "Filter"
    And I should <rowsPerPage> "Rows Per Page"
    And I should <action> "Action"
    And I should <candidates> "Candidates - Page"
    And the page should <addToList> contain "Add To List"
    And the page should <export> contain "Export"
    And the page should <addToPipeline> contain "Add To Pipeline"
    And the page should <sendEmail> contain "Send E-Mail"
    And I should <alphabetFilter> "ALL"
    
  Examples:
     | accessLevel | addCandidate | searchCandidate | quickSearch | candidatesHome | filterByTag | onlyMyCandidates| onlyHotCandidates| filter | rowsPerPage | action | candidates | addToList | export | addToPipeline | sendEmail | alphabetFilter |
     | DISABLED    | not see      | not see         | not         | not see        | not see     | not             | not              | not see| not see     | not see| not see    | not       | not    | not           | not       | not see        |
     | READONLY    | not see      | see             |             | see            | see         |                 |                  | see    | see         | see    | see        |           |        |               |           | see            |  
     | EDIT        | see          | see             |             | see            | see         |                 |                  | see    | see         | see    | see        |           |        |               |           | see            | 
     | DELETE      | see          | see             |             | see            | see         |                 |                  | see    | see         | see    | see        |           |        |               |           | see            | 
     | DEMO        | see          | see             |             | see            | see         |                 |                  | see    | see         | see    | see        |           |        |               |           | see            | 
     | ADMIN       | see          | see             |             | see            | see         |                 |                  | see    | see         | see    | see        |           |        |               |           | see            | 
     | MULTI_ADMIN | see          | see             |             | see            | see         |                 |                  | see    | see         | see    | see        |           |        |               |           | see            | 
     | ROOT        | see          | see             |             | see            | see         |                 |                  | see    | see         | see    | see        |           |        |               |           | see            | 


   @javascript @candidates
   Scenario Outline: Candidate Show page visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=candidates"
     When I follow "Pippin"
     Then I should <addCandidate> "Add Candidate"
     And I should <searchCandidate> "Search Candidates"
     And the page should <quickSearch> contain "Quick Search"
     And the page should <quickSearch> contain "quickSearchFor"
     And the page should <quickSearch> contain "quickSearch"
     And the page should <actionMenu> contain "showHideSingleQuickActionMenu"
     And I should <details> "Candidate Details"
     And I should <scheduleEvent> "Schedule Event"
     And I should <addAttachment> "Add Attachment"
     And I should <editCandidate> "Edit"
     And I should <deleteCandidate> "Delete"
     And I should <viewHistory> "View History"
     And I should <administrativeHideShow> "Administrative"
     And the page should <addToPipeline> contain "Add This Candidate to Job Order Pipeline"
     And I should <logAnActivity> "Log an Activity"
     And the page should <logAnActivity2> contain "Log an Activity"  
     And the page should <removeFromPipeline> contain "Remove from Pipeline"
     And the page should <editActivity> contain "editActivity"
     And the page should <deleteActivity> contain "deleteActivity"
     And the page should <setMatchingRating> contain "<map"
     And the page should <deleteAttachment> contain "index.php?m=candidates&amp;a=deleteAttachment"
     #When I click on "arrow"
     #Then the page should <addToList> contain "Add To List"
     #And the page should <addToPipeline> contain "Add To Pipeline"
     
   Examples:
     | accessLevel | addCandidate | searchCandidate | quickSearch | actionMenu | addToList | details | scheduleEvent | addAttachment | editCandidate | deleteCandidate | viewHistory | administrativeHideShow | addToPipeline | logAnActivity2 | logAnActivity | removeFromPipeline | editActivity | deleteActivity | setMatchingRating | deleteAttachment |
     | DISABLE     | not see      | not see         | not         | not        | not       | not see | not see       | not see       | not see       | not see         | not see     | not see                | not           | not            | not see       | not                | not          | not            | not                     | not              |
     | READONLY    | not see      | see             |             |            |           | see     | not see       | not see       | not see       | not see         | not see     | not see                | not           | not            | not see       | not                | not          | not            | not                     | not              |
     | EDIT        | see          | see             |             |            |           | see     | see           | see           | see           | not see         | not see     | not see                |               |                | see           | not                |              | not            |                         | not              |
     | DELETE      | see          | see             |             |            |           | see     | see           | see           | see           | see             | not see     | not see                |               |                | see           |                    |              |                |                         |                  |
     | DEMO        | see          | see             |             |            |           | see     | see           | see           | see           | see             | see         | not see                |               |                | see           |                    |              |                |                         |                  |
     | ADMIN       | see          | see             |             |            |           | see     | see           | see           | see           | see             | see         | not see                |               |                | see           |                    |              |                |                         |                  |
     | MULTI_ADMIN | see          | see             |             |            |           | see     | see           | see           | see           | see             | see         | see                    |               |                | see           |                    |              |                |                         |                  |
     | ROOT        | see          | see             |             |            |           | see     | see           | see           | see           | see             | see         | see                    |               |                | see           |                    |              |                |                         |                  |
     
   ####### COMPANIES #######
    
    @javascript @companies
    Scenario Outline: Companies module visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=companies"
     Then I should <addCompany> "Add Company"
     And I should <searchCompany> "Search Companies"
     And the page should <quickSearch> contain "Quick Search"
     And the page should <quickSearch> contain "quickSearchFor"
     And the page should <quickSearch> contain "quickSearch"
     And I should <companiesHome> "Companies: Home"
     And the page should <onlyMyCompanies> contain "onlyMyCompanies"
     And the page should <onlyHotCompanies> contain "onlyHotCompanies"
     And I should <filter> "Filter"
     And I should <rowsPerPage> "Rows Per Page"
     And I should <action> "Action"
     And I should <companies> "Companies - Page"
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
     
    @javascript @companies
    Scenario Outline: Company Show page visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=companies"
     When I follow "Google"
     Then I should <addCompany> "Add Company"
     And I should <searchCompany> "Search Companies"
     And the page should <quickSearch> contain "Quick Search"
     And the page should <quickSearch> contain "quickSearchFor"
     And the page should <quickSearch> contain "quickSearch"
     And the page should <actionMenu> contain "showHideSingleQuickActionMenu"
     And I should <addAttachment> "Add Attachment"
     And I should <viewHistory> "View History"
     And I should <editCompany> "Edit"
     And I should <deleteCompany> "Delete"
     And I should <addJobOrder> "Add Job Order"
     And I should <addContact> "Add Contact"
     And the page should <editJobOrder> contain "index.php?m=joborders&amp;a=edit"  
     And the page should <editContact> contain "index.php?m=contacts&amp;a=edit"
     And the page should <deleteAttachment> contain "index.php?m=companies&amp;a=deleteAttachment"
     And the page should <sendEmail> contain "Send E-Mail"
     
     Examples:
     | accessLevel | addCompany  | searchCompany  | quickSearch | actionMenu | addAttachment | viewHistory | editCompany   | deleteCompany   | addJobOrder | addContact | editJobOrder | editContact | deleteAttachment | sendEmail |
     | DISABLED    | not see     | not see        | not         | not        | not see       | not see     | not see       | not see         | not see     | not see    | not          | not         | not              | not       |
     | READONLY    | not see     | see            |             |            | not see       | not see     | not see       | not see         | not see     | not see    | not          | not         | not              |           |
     | EDIT        | see         | see            |             |            | see           | not see     | see           | not see         | see         | see        |              |             | not              |           | 
     | DELETE      | see         | see            |             |            | see           | not see     | see           | see             | see         | see        |              |             |                  |           |
     | DEMO        | see         | see            |             |            | see           | see         | see           | see             | see         | see        |              |             |                  |           |
     | ADMIN       | see         | see            |             |            | see           | see         | see           | see             | see         | see        |              |             |                  |           |
     | MULTI_ADMIN | see         | see            |             |            | see           | see         | see           | see             | see         | see        |              |             |                  |           |
     | ROOT        | see         | see            |             |            | see           | see         | see           | see             | see         | see        |              |             |                  |           |
     
    ####### CONTACTS #######
  
@javascript @contacts
    Scenario Outline: Contacts module visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=contacts"
     Then I should <addContact> "Add Contact"
     And I should <searchContact> "Search Contacts"
     And I should <coldCallList> "Cold Call List"
     And the page should <quickSearch> contain "Quick Search"
     And the page should <quickSearch> contain "quickSearchFor"
     And the page should <quickSearch> contain "quickSearch"
     And I should <contactsHome> "Contacts: Home"
     And the page should <onlyMyContacts> contain "onlyMyContacts"
     And the page should <onlyHotContacts> contain "onlyHotContacts"
     And I should <filter> "Filter"
     And I should <rowsPerPage> "Rows Per Page"
     And I should <action> "Action"
     And I should <contacts> "Contacts - Page"
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
     
     
    @javascript @contacts
    Scenario Outline: Contacts Show page visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=contacts"
     When I follow "Elizabeth"
     Then I should <addContact> "Add Contact"
     And I should <searchContact> "Search Contacts"
     And the page should <quickSearch> contain "Quick Search"
     And the page should <quickSearch> contain "quickSearchFor"
     And the page should <quickSearch> contain "quickSearch"
     And the page should <actionMenu> contain "showHideSingleQuickActionMenu"
     And I should <coldCallList> "Cold Call List"
     And I should <scheduleEvent> "Schedule Event"
     And I should <viewHistory> "View History"
     And I should <editContact> "Edit"
     And I should <deleteContact> "Delete"
     And I should <logAnActivity> "Log an Activity"
     And the page should <editActivity> contain "editActivity"
     And the page should <deleteActivity> contain "deleteActivity"
     
     Examples:
     | accessLevel | addContact   | searchContact   | actionMenu | quickSearch | coldCallList | scheduleEvent | viewHistory | editContact | deleteContact | logAnActivity | editActivity | deleteActivity |
     | DISABLED    | not see      | not see         | not        | not         | not see      | not see       | not see     | not see     | not see       | not see       | not          | not            |
     | READONLY    | not see      | see             |            |             | see          | not see       | not see     | not see     | not see       | not see       | not          | not            |
     | EDIT        | see          | see             |            |             | see          | see           | not see     | see         | not see       | see           |              |                |
     | DELETE      | see          | see             |            |             | see          | see           | not see     | see         | see           | see           |              |                |
     | DEMO        | see          | see             |            |             | see          | see           | see         | see         | see           | see           |              |                |
     | ADMIN       | see          | see             |            |             | see          | see           | see         | see         | see           | see           |              |                |
     | MULTI_ADMIN | see          | see             |            |             | see          | see           | see         | see         | see           | see           |              |                |
     | ROOT        | see          | see             |            |             | see          | see           | see         | see         | see           | see           |              |                |
     
     
   ####### LISTS #######
  
  @javascript @lists
    Scenario Outline: Lists module visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=lists"
     Then I should <showLists> "Show Lists"
     And the page should <quickSearch> contain "Quick Search"
     And the page should <quickSearch> contain "quickSearchFor"
     And the page should <quickSearch> contain "quickSearch"
     And I should <listsHome> "Lists: Home"
     And I should <rowsPerPage> "Rows Per Page"
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
     
     
    @javascript @lists
    Scenario Outline: Lists Show page visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=lists"
     When I follow "list"
     Then I should <showLists> "Show Lists"
     And the page should <quickSearch> contain "Quick Search"
     And the page should <quickSearch> contain "quickSearchFor"
     And the page should <quickSearch> contain "quickSearch"
     And I should <deleteList> "Delete List"
     And I should <listsHome> "Lists:"
     And I should <filter> "Filter"
     And I should <rowsPerPage> "Rows Per Page"
     And I should <action> "Action"
     And I should <lists> "- Page"
     And I should <alphabetFilter> "ALL"
     And the page should <removeFromList> contain "Remove From This List"
     And the page should <addToPipeline> contain "Add To Pipeline"
     And the page should <sendEmail> contain "Send E-Mail"
     And the page should <export> contain "Export"
     
     Examples:
     | accessLevel | showLists    | quickSearch | deleteList | listsHome | filter | rowsPerPage | action | lists  | alphabetFilter| removeFromList | addToPipeline | sendEmail | export |
     | DISABLED    | not see      | not         | not see    | not see   | not see| not see     | not see| not see| not see       | not            | not           | not       | not    |
     | READONLY    | see          |             | see        | see       | see    | see         | see    | see    | see           |                |               |           |        |
     | EDIT        | see          |             | see        | see       | see    | see         | see    | see    | see           |                |               |           |        |
     | DELETE      | see          |             | see        | see       | see    | see         | see    | see    | see           |                |               |           |        |
     | DEMO        | see          |             | see        | see       | see    | see         | see    | see    | see           |                |               |           |        |
     | ADMIN       | see          |             | see        | see       | see    | see         | see    | see    | see           |                |               |           |        |
     | MULTI_ADMIN | see          |             | see        | see       | see    | see         | see    | see    | see           |                |               |           |        |
     | ROOT        | see          |             | see        | see       | see    | see         | see    | see    | see           |                |               |           |        |
     
  ####### REPORTS #######
  
  @javascript @reports
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
@javascript @settings
    Scenario Outline: Settings module visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=settings"
     Then I should <viewProfile> "View Profile"
     And I should <changePassword> "Change Password"
     And I should <administration> "Administration"
     And I should <myProfile> "My Profile"
     And I should <download> "Downloads"
     And the page should <quickSearch> contain "Quick Search"
     And the page should <quickSearch> contain "quickSearchFor"
     And the page should <quickSearch> contain "quickSearch"
     And I should <settingsHome> "Settings: My Profile"
    
    Examples:
     | accessLevel | viewProfile  | changePassword  | administration | myProfile | download | quickSearch  | settingsHome |
     | DISABLED    | not see      | not see         | not see        | not see   | not see  | not          | not see      |
     | READONLY    | see          | see             | not see        | see       | see      |              | see          |
     | EDIT        | see          | see             | not see        | see       | see      |              | see          |
     | DELETE      | see          | see             | not see        | see       | see      |              | see          |
     | DEMO        | see          | see             | see            | see       | see      |              | see          |
     | ADMIN       | see          | see             | see            | see       | see      |              | see          |
     | MULTI_ADMIN | see          | see             | see            | see       | see      |              | see          |
     | ROOT        | see          | see             | see            | see       | see      |              | see          |
     
####### CALENDAR #######
##TO-DO

##missing checks for quick action menus on Show pages
     