Feature: Security using ACL
  In order to protect sensitive information from users who should not have access to them
  All accesses in the system need to be controlled by the Access Control List
     
  @javascript
  Scenario Outline: Candidates module visibility
    Given I am logged in with <accessLevel> access level
    And I am on "/index.php?m=candidates"
    Then I should <addCandidate> "Add Candidate"
    And I should <searchCandidate> "Search Candidates"
    
    Examples:
     | accessLevel | addCandidate | searchCandidate |
     | READONLY    | not see      | see             |
     | EDIT        | see          | see             |
     | DELETE      | see          | see             |
     | ADMIN       | see          | see             |
     | ROOT        | see          | see             |
    
    @javascript
    Scenario Outline: Candidate Show page visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=candidates"
     When I follow "Pippin"
     Then I should <addCandidate> "Add Candidate"
     And I should <searchCandidate> "Search Candidates"
     And I should <scheduleEvent> "Schedule Event"
     And I should <addAttachment> "Add Attachment"
     And I should <editCandidate> "Edit"
     And I should <deleteCandidate> "Delete"
     And I should <viewHistory> "View History"
     And I should <administrativeHideShow> "Administrative"
     And I should <addToPipeline> "Add This Candidate to Job Order Pipeline"
     And I should <logAnActivity> "Log an Activity"
     And the page should <logAnActivity2> contain "Log an Activity"  
     And the page should <removeFromPipeline> contain "Remove from Pipeline"
     And the page should <editActivity> contain "editActivity"
     And the page should <deleteActivity> contain "deleteActivity"
     And the page should <setMatchingRating> contain "<map"
     
     
     Examples:
     | accessLevel | addCandidate | searchCandidate | scheduleEvent | addAttachment | editCandidate | deleteCandidate | viewHistory | administrativeHideShow | addToPipeline | logAnActivity2 | logAnActivity | removeFromPipeline | editActivity | deleteActivity | setMatchingRating |
     | READONLY    | not see      | see             | not see       | not see       | not see       | not see         | not see     | not see                | not see       | not            | not see       | not                | not          | not            | not                   |
     | EDIT        | see          | see             | see           | see           | see           | not see         | not see     | not see                | see           |                | see           | not                |              | not            |                       |
     | DELETE      | see          | see             | see           | see           | see           | see             | not see     | not see                | see           |                | see           |                    |              |                |                       |
     | ADMIN       | see          | see             | see           | see           | see           | see             | see         | not see                | see           |                | see           |                    |              |                |                       |
     | ROOT        | see          | see             | see           | see           | see           | see             | see         | see                    | see           |                | see           |                    |              |                |                       |
     
    @javascript
    Scenario Outline: Job Orders module visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=joborders"
     Then I should <addJobOrder> "Add Job Order"
     And I should <searchJobOrder> "Search Job Orders"
    
    Examples:
     | accessLevel | addJobOrder  | searchJobOrder  |
     | READONLY    | not see      | see             |
     | EDIT        | see          | see             |
     | DELETE      | see          | see             |
     | ADMIN       | see          | see             |
     | ROOT        | see          | see             |
    
    @javascript
    Scenario Outline: Job Order Show page visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=joborders"
     When I follow "OpenCATS Tester"
     Then I should <addJobOrder> "Add Job Order"
     And I should <searchJobOrder> "Search Job Orders"
     And I should <addAttachment> "Add Attachment"
     And I should <generateReport> "Generate Report"
     And I should <viewHistory> "View History"
     And I should <editJobOrder> "Edit"
     And I should <deleteJobOrder> "Delete"
     And I should <administrativeHideShow> "Administrative"
     And I should <addToPipeline> "Add Candidate to This Job Order Pipeline"
     And the page should <logAnActivity> contain "Log an Activity"  
     And the page should <removeFromPipeline> contain "Remove from Pipeline"
     And the page should <setMatchingRating> contain "<map"
     
     Examples:
     | accessLevel | addJobOrder | searchJobOrder | addAttachment | generateReport | viewHistory | editJobOrder  | deleteJobOrder  | administrativeHideShow | addToPipeline | logAnActivity  | removeFromPipeline | setMatchingRating |
     | READONLY    | not see     | see            | not see       | see            | not see     | not see       | not see         | not see                | not see       | not            | not                | not               | 
     | EDIT        | see         | see            | see           | see            | not see     | see           | not see         | not see                | see           |                | not                |                   |    
     | DELETE      | see         | see            | see           | see            | not see     | see           | see             | not see                | see           |                |                    |                   |
     | ADMIN       | see         | see            | see           | see            | see         | see           | see             | not see                | see           |                |                    |                   |
     | ROOT        | see         | see            | see           | see            | see         | see           | see             | see                    | see           |                |                    |                   |
     
    @javascript
    Scenario Outline: Companies module visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=companies"
     Then I should <addCompany> "Add Company"
     And I should <searchCompany> "Search Companies"
    
    Examples:
     | accessLevel | addCompany   | searchCompany   |
     | READONLY    | not see      | see             |
     | EDIT        | see          | see             |
     | DELETE      | see          | see             |
     | ADMIN       | see          | see             |
     | ROOT        | see          | see             |
     
    @javascript
    Scenario Outline: Company Show page visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=companies"
     When I follow "Google"
     Then I should <addCompany> "Add Company"
     And I should <searchCompany> "Search Companies"
     And I should <addAttachment> "Add Attachment"
     And I should <viewHistory> "View History"
     And I should <editCompany> "Edit"
     And I should <deleteCompany> "Delete"
     And I should <addJobOrder> "Add Job Order"
     And I should <addContact> "Add Contact"
     And the page should <editJobOrder> contain "index.php?m=joborders&amp;a=edit"  
     And the page should <editContact> contain "index.php?m=contacts&amp;a=edit"
     
     Examples:
     | accessLevel | addCompany  | searchCompany  | addAttachment | viewHistory | editCompany   | deleteCompany   | addJobOrder | addContact | editJobOrder | editContact |
     | READONLY    | not see     | see            | not see       | not see     | not see       | not see         | not see     | not see    | not          | not         |
     | EDIT        | see         | see            | see           | not see     | see           | not see         | see         | see        |              |             |
     | DELETE      | see         | see            | see           | not see     | see           | see             | see         | see        |              |             |
     | ADMIN       | see         | see            | see           | see         | see           | see             | see         | see        |              |             |
     | ROOT        | see         | see            | see           | see         | see           | see             | see         | see        |              |             |
     
    @javascript
    Scenario Outline: Contacts module visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=contacts"
     Then I should <addContact> "Add Contact"
     And I should <searchContact> "Search Contacts"
     And I should <coldCallList> "Cold Call List"
    
    Examples:
     | accessLevel | addContact   | searchContact   | coldCallList |
     | READONLY    | not see      | see             | not see      |
     | EDIT        | see          | see             | see          | 
     | DELETE      | see          | see             | see          |
     | ADMIN       | see          | see             | see          |
     | ROOT        | see          | see             | see          |
     
    @javascript
    Scenario Outline: Contacts Show page visibility
     Given I am logged in with <accessLevel> access level
     And I am on "/index.php?m=contacts"
     When I follow "Elizabeth"
     Then I should <addContact> "Add Contact"
     And I should <searchContact> "Search Contacts"
     And I should <coldCallList> "Cold Call List"
     
     And I should <scheduleEvent> "Schedule Event"
     And I should <viewHistory> "View History"
     And I should <editContact> "Edit"
     And I should <deleteContact> "Delete"
     And I should <logAnActivity> "Log an Activity"
     And the page should <editActivity> contain "editActivity"
     And the page should <deleteActivity> contain "deleteActivity"
     
     Examples:
     | accessLevel | addContact   | searchContact   | coldCallList | scheduleEvent | viewHistory | editContact | deleteContact | logAnActivity | editActivity | deleteActivity |
     | READONLY    | not see      | see             | not see      | not see       | not see     | not see     | not see       | not see       | not          | not            |
     | EDIT        | see          | see             | see          | see           | not see     | see         | not see       | see           |              |                |
     | DELETE      | see          | see             | see          | see           | not see     | see         | see           | see           |              |                |
     | ADMIN       | see          | see             | see          | see           | see         | see         | see           | see           |              |                |
     | ROOT        | see          | see             | see          | see           | see         | see         | see           | see           |              |                |
     
    
    