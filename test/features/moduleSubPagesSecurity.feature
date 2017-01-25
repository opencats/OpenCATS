@security
Feature: Access Level to objects check - sub pages (show, ...)
  In order to protect sensitive information from users who should not have access to them
  All objects in the system need to be controlled by the Access Level of user

  ######## DASHBOARD(HOME) #######
  # no sub pages
  
  ####### ACTIVITIES #######
  # no sub pages
 
  ####### JOB ORDERS #######  
       
  @javascript @joborders
  Scenario Outline: Job Order Show page visibility
    Given I am logged in with <accessLevel> access level
    And I am on "/index.php?m=joborders"
    When I follow link "OpenCATS Tester"
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
   Scenario Outline: Candidate Show page visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=candidates"
     When I follow link "Pipin"
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
     | DISABLED    | not see      | not see         | not         | not        | not       | not see | not see       | not see       | not see       | not see         | not see     | not see                | not           | not            | not see       | not                | not          | not            | not                     | not              |
     | READONLY    | not see      | see             |             |            |           | see     | not see       | not see       | not see       | not see         | not see     | not see                | not           | not            | not see       | not                | not          | not            | not                     | not              |
     | EDIT        | see          | see             |             |            |           | see     | see           | see           | see           | not see         | not see     | not see                |               |                | see           | not                |              | not            |                         | not              |
     | DELETE      | see          | see             |             |            |           | see     | see           | see           | see           | see             | not see     | not see                |               |                | see           |                    |              |                |                         |                  |
     | DEMO        | see          | see             |             |            |           | see     | see           | see           | see           | see             | see         | not see                |               |                | see           |                    |              |                |                         |                  |
     | ADMIN       | see          | see             |             |            |           | see     | see           | see           | see           | see             | see         | not see                |               |                | see           |                    |              |                |                         |                  |
     | MULTI_ADMIN | see          | see             |             |            |           | see     | see           | see           | see           | see             | see         | see                    |               |                | see           |                    |              |                |                         |                  |
     | ROOT        | see          | see             |             |            |           | see     | see           | see           | see           | see             | see         | see                    |               |                | see           |                    |              |                |                         |                  |
     
    ####### COMPANIES #######

    @javascript @companies
    Scenario Outline: Company Show page visibility for disabled level
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=home"
     When I follow link "Google"
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
   
    @javascript @companies
    Scenario Outline: Company Show page visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=home&a=quickSearch&quickSearchFor=google"
     When I follow link "Google"
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
     | READONLY    | not see     | see            |             |            | not see       | not see     | not see       | not see         | not see     | not see    | not          | not         | not              |           |
     | EDIT        | see         | see            |             |            | see           | not see     | see           | not see         | see         | see        |              |             | not              |           | 
     | DELETE      | see         | see            |             |            | see           | not see     | see           | see             | see         | see        |              |             |                  |           |
     | DEMO        | see         | see            |             |            | see           | see         | see           | see             | see         | see        |              |             |                  |           |
     | ADMIN       | see         | see            |             |            | see           | see         | see           | see             | see         | see        |              |             |                  |           |
     | MULTI_ADMIN | see         | see            |             |            | see           | see         | see           | see             | see         | see        |              |             |                  |           |
     | ROOT        | see         | see            |             |            | see           | see         | see           | see             | see         | see        |              |             |                  |           |
     
  ####### CONTACTS #######
  
  @javascript @contacts
    Scenario Outline: Contacts Show page visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=contacts"
     When I follow link "Elizabeth"
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
   Scenario Outline: Lists Show page visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=lists"
     When I follow link "UK Candidates"
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
     | READONLY    | see          |             | see        | see       | see    | see         | see    | see    | see           |                |               | not       |        |
     | EDIT        | see          |             | see        | see       | see    | see         | see    | see    | see           |                |               | not       |        |
     | DELETE      | see          |             | see        | see       | see    | see         | see    | see    | see           |                |               | not       |        |
     | DEMO        | see          |             | see        | see       | see    | see         | see    | see    | see           |                |               | not       |        |
     | ADMIN       | see          |             | see        | see       | see    | see         | see    | see    | see           |                |               |           |        |
     | MULTI_ADMIN | see          |             | see        | see       | see    | see         | see    | see    | see           |                |               |           |        |
     | ROOT        | see          |             | see        | see       | see    | see         | see    | see    | see           |                |               |           |        |
     
  ####### REPORTS #######
  # no sub pages
  
  ####### SETTINGS #######
  # no sub pages
  
  ####### CALENDAR #######
  # no sub pages

##missing checks for quick action menus on Show pages
     