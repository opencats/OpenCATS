@core
Feature: Job Orders
  In order to fullfil customers job orders 
  As a user
  I need to be able to track all interaction in the application
  
  @javascript
  Scenario: View add job order page
    Given I am authenticated as "Administrator"
    And There is a company called "Test Company ATxyz"
    And There is a user "testuser101" named "Marcus Gomez" with "password101" password 
    And I am on "/index.php?m=candidates" 
    And I follow "Job Orders"
    And I follow "Add Job Order"
    And I switch to the iframe "popupInner"
    And press "Create Job Order"
    And I switch to the iframe ""
    Then I should see "Title"
    And I should see "Company"
    And I should see "Department"
    And I should see "Contact"
    And I should see "City"
    And I should see "State"
    And I should see "Recruiter"
    And I should see "Owner"
    And I should see "Start Date"
    And I should see "Duration"
    And I should see "Maximum Rate"
    And I should see "Type"
    And I should see "Salary"
    And I should see "Openings"
    And I should see "Company Job ID"
    And I should see "Hot"
    And I should see "Public"
    
  @javascript
  Scenario: Add job order with title only gets fatal error
    Given I am authenticated as "Administrator"
    And There is a company called "Test Company ATxyz"
    And There is a user "testuser101" named "Marcus Gomez" with "password101" password 
    And I am on "/index.php?m=candidates" 
    And I follow "Job Orders"
    And I follow "Add Job Order"
    And I switch to the iframe "popupInner"
    And press "Create Job Order"
    And I switch to the iframe ""
    And fill in "title" with "Javascript developer"
    And press "Add Job Order" 
    Then I should see "Form Error" in alert popup
    And I should see "You must select a company" in alert popup
    And I should see "You must enter a city" in alert popup
    And I should see "You must enter a state" in alert popup
    And I confirm the popup
    
  @javascript
  Scenario: Add job order succesfully
    Given I am authenticated as "Administrator"
    And There is a company called "Test Company ATxyz"
    And There is a user "testuser101" named "Marcus Gomez" with "password101" password 
    And I am on "/index.php?m=candidates" 
    And I follow "Job Orders"
    And I follow "Add Job Order"
    And I switch to the iframe "popupInner"
    And press "Create Job Order"
    And I switch to the iframe ""
    And fill in "title" with "Javascript developer"
    And fill in "companyName" with "Test Company ATxyz"
    And I wait for "#CompanyResults div#suggest0"
    And I click on the element "#CompanyResults div#suggest0"
    And I select "Gomez, Marcus" in the "#recruiter" select
    And fill in "city" with "Minneapolis"
    And fill in "state" with "MN"
    And press "Add Job Order" 
    Then I should see "Title"
    And I should see "Company Name"
    And I should see "Recruiter"
    And I should see "Contact"
    And I should see "Location"
    And I should see "Javascript developer"
    And I should see "Test Company ATxyz"
    And I should see "Minneapolis"
    And I should see "MN"
    And I should see "Marcus Gomez"
    
  @javascript
  Scenario: Edit job order has fields
    # To be refactored by creating the Job Order programatically
    Given I am authenticated as "Administrator"
    And There is a company called "Test Company ATxyz"
    And There is a user "testuser101" named "Marcus Gomez" with "password101" password 
    And I am on "/index.php?m=candidates" 
    And I follow "Job Orders"
    And I follow "Add Job Order"
    And I switch to the iframe "popupInner"
    And press "Create Job Order"
    And I switch to the iframe ""
    And fill in "title" with "Javascript developer"
    And fill in "companyName" with "Test Company ATxyz"
    And I wait for "#CompanyResults div#suggest0"
    And I click on the element "#CompanyResults div#suggest0"
    And I select "Gomez, Marcus" in the "#recruiter" select
    And fill in "city" with "Minneapolis"
    And fill in "state" with "MN"
    And press "Add Job Order"
    And I follow "Edit"
    Then I should see "Title"
    And I should see "Company"
    And I should see "Recruiter"
    And I should see "Contact"
    And I should see "City"
    And I should see "State"
    And I should see "Javascript developer"
    # FIXME: next line is not working even though it's displayed on the screen
    # And I should see "Test Company ATxyz"
    And I should see "Marcus Gomez"
    And I should see "Total Openings"
    And I should see "Duration"
    And I should see "Maximum Rate"
    And I should see "Salary"
    And I should see "Hot"
    And I should see "Description"
    And I should see "Internal Notes"
    And I should see "Owner"
    
  @javascript
  Scenario: Edit job order with blank title pops error 
    # To be refactored by creating the Job Order programatically
    Given I am authenticated as "Administrator"
    And There is a company called "Test Company ATxyz"
    And There is a user "testuser101" named "Marcus Gomez" with "password101" password 
    And I am on "/index.php?m=candidates" 
    And I follow "Job Orders"
    And I follow "Add Job Order"
    And I switch to the iframe "popupInner"
    And press "Create Job Order"
    And I switch to the iframe ""
    And fill in "title" with "Javascript developer"
    And fill in "companyName" with "Test Company ATxyz"
    And I wait for "#CompanyResults div#suggest0"
    And I click on the element "#CompanyResults div#suggest0"
    And I select "Gomez, Marcus" in the "#recruiter" select
    And fill in "city" with "Minneapolis"
    And fill in "state" with "MN"
    And press "Add Job Order"
    And I follow "Edit"
    And fill in "title" with ""
    And press "Save"
    Then I should see "Form Error" in alert popup
    And I should see "You must enter a job title" in alert popup
    And I confirm the popup

  @javascript
  Scenario: Edit job order updates record
    # To be refactored by creating the Job Order programatically
    Given I am authenticated as "Administrator"
    And There is a company called "Test Company ATxyz"
    And There is a user "testuser101" named "Marcus Gomez" with "password101" password 
    And I am on "/index.php?m=candidates" 
    And I follow "Job Orders"
    And I follow "Add Job Order"
    And I switch to the iframe "popupInner"
    And press "Create Job Order"
    And I switch to the iframe ""
    And fill in "title" with "Javascript developer"
    And fill in "companyName" with "Test Company ATxyz"
    And I wait for "#CompanyResults div#suggest0"
    And I click on the element "#CompanyResults div#suggest0"
    And I select "Gomez, Marcus" in the "#recruiter" select
    And fill in "city" with "Minneapolis"
    And fill in "state" with "MN"
    And press "Add Job Order"
    And I follow "Edit"
    And fill in "title" with "Frontend developer"
    And I select "Administrator, CATS" in the "#recruiter" select
    And I select "Gomez, Marcus" in the "#owner" select
    And fill in "openings" with "999"
    And fill in "companyName" with "Test Company ATxyz"
    And I wait for "#CompanyResults div#suggest0"
    And I click on the element "#CompanyResults div#suggest0"
    And press "Save"
    Then I should see "Frontend developer"
    And I should see "CATS Administrator"
    And I should see "Marcus Gomez"
    And I should see "999"

  @javascript
  Scenario: Search job order by company name
    Given There is a company called "Test Company ATxyz"
    And There is a company called "Test Company BigJump"
    And There is a job order for a "Javascript developer" for "Test Company ATxyz"
    And There is a job order for a "PHP developer" for "Test Company BigJump"
    And There is a user "testuser101" named "Marcus Gomez" with "password101" password
    And I login as "testuser101" "password101"
    And I am on "/index.php?m=joborders&a=search" 
    And I select "Company Name" in the "#searchMode" select
    And I fill in "searchText" with "Test Company BigJump"
    And press "Search" 
    Then I should see "PHP developer"
    And I should see "Test Company BigJump"
    And I should not see "Test Company ATxyz"
    And I should not see "Javascript developer"
    
  @javascript
  Scenario: Search job order by job title
    Given There is a company called "Test Company ATxyz"
    And There is a company called "Test Company BigJump"
    And There is a job order for a "Javascript developer" for "Test Company ATxyz"
    And There is a job order for a "PHP developer" for "Test Company BigJump"
    And There is a user "testuser101" named "Marcus Gomez" with "password101" password
    And I login as "testuser101" "password101"
    And I am on "/index.php?m=joborders&a=search" 
    And I select "Job Title" in the "#searchMode" select
    And I fill in "searchText" with "PHP developer"
    And press "Search" 
    Then I should see "PHP developer"
    And I should see "Test Company BigJump"
    And I should not see "Test Company ATxyz"
    And I should not see "Javascript developer"
    
  @javascript
  Scenario: Open job order from search result list
    Given There is a company called "Test Company ATxyz"
    And There is a company called "Test Company BigJump"
    And There is a job order for a "Javascript developer" for "Test Company ATxyz"
    And There is a job order for a "PHP developer" for "Test Company BigJump"
    And There is a user "testuser101" named "Marcus Gomez" with "password101" password
    And I login as "testuser101" "password101"
    And I am on "/index.php?m=joborders&a=search" 
    And I select "Job Title" in the "#searchMode" select
    And I fill in "searchText" with "PHP developer"
    And press "Search" 
    When I click on "PHP developer" on the row containing "Active"
    Then I should see "PHP developer"
    And I should see "Job Order Details"
    And I should see "Test Company BigJump"
    
  @javascript
  Scenario: Open job order from search result and delete it 
    Given There is a company called "Test Company ATxyz"
    And There is a company called "Test Company BigJump"
    And There is a job order for a "Javascript developer" for "Test Company ATxyz"
    And There is a job order for a "PHP developer" for "Test Company BigJump"
    And There is a user "testuser101" named "Marcus Gomez" with "password101" password
    And I login as "testuser101" "password101"
    And I am on "/index.php?m=joborders&a=search" 
    And I select "Job Title" in the "#searchMode" select
    And I fill in "searchText" with "PHP developer"
    And press "Search" 
    When I click on "PHP developer" on the row containing "Active"
    And follow "Delete"
    And I should see "Delete this job order?" in alert popup
    And I confirm the popup
    Then I should see "Job Orders: Home"
    
  @javascript
  Scenario: Add candidate from modal has candidate fields 
    Given There is a company called "Test Company ATxyz"
    And There is a job order for a "Javascript developer" for "Test Company ATxyz"
    And There is a user "testuser101" named "Marcus Gomez" with "password101" password
    And I login as "testuser101" "password101"
    And I am on "/index.php?m=joborders&a=search" 
    And I select "Job Title" in the "#searchMode" select
    And I fill in "searchText" with "Javascript developer"
    And press "Search" 
    And I click on "Javascript developer" on the row containing "Active"
    And follow "Add Candidate to This Job Order Pipeline"
    And I switch to the iframe "popupInner"
    And follow "Add Candidate"
    Then I should see "First Name"
    And I should see "Middle Name"
    And I should see "Last Name"
    And I should see "E-Mail"
    And I should see "2nd E-Mail"
    And I should see "Home Phone"
    And I should see "Cell Phone"
    And I should see "Work Phone"
    And I should see "City"
    And I should see "State"
    And I should see "Postal Code"
    And I should see "Address"
    And I should see "Source"
    And I should see "Key Skills"
    And I should see "Current Employer"
    And I should see "Notes"
    And I should see "Date Available"

  @javascript
  Scenario: Add candidate from modal only with first name fails 
    Given There is a company called "Test Company ATxyz"
    And There is a job order for a "Javascript developer" for "Test Company ATxyz"
    And There is a user "testuser101" named "Marcus Gomez" with "password101" password
    And I login as "testuser101" "password101"
    And I am on "/index.php?m=joborders&a=search" 
    And I select "Job Title" in the "#searchMode" select
    And I fill in "searchText" with "Javascript developer"
    And press "Search" 
    And I click on "Javascript developer" on the row containing "Active"
    And follow "Add Candidate to This Job Order Pipeline"
    And I switch to the iframe "popupInner"
    And follow "Add Candidate"
    And I fill in "firstName" with "John"
    And press "Add Candidate"
    Then I should see "You must enter last name" in alert popup
    And I confirm the popup
    
  @javascript
  Scenario: Add candidate from modal with all required field succeeds 
    Given There is a company called "Test Company ATxyz"
    And There is a job order for a "Javascript developer" for "Test Company ATxyz"
    And There is a user "testuser101" named "Marcus Gomez" with "password101" password
    And I login as "testuser101" "password101"
    And I am on "/index.php?m=joborders&a=search" 
    And I select "Job Title" in the "#searchMode" select
    And I fill in "searchText" with "Javascript developer"
    And press "Search" 
    And I click on "Javascript developer" on the row containing "Active"
    And follow "Add Candidate to This Job Order Pipeline"
    And I switch to the iframe "popupInner"
    And follow "Add Candidate"
    And I fill in "firstName" with "John"
    And I fill in "lastName" with "John"
    And press "Add Candidate"
    Then I should see "The candidate has been successfully added to the pipeline for the selected job order."
    
  @javascript
  Scenario: Job Order detail page is updated after adding a candidate 
    Given There is a company called "Test Company ATxyz"
    And There is a job order for a "Javascript developer" for "Test Company ATxyz"
    And There is a user "testuser101" named "Marcus Gomez" with "password101" password
    And I login as "testuser101" "password101"
    And I am on "/index.php?m=joborders&a=search" 
    And I select "Job Title" in the "#searchMode" select
    And I fill in "searchText" with "Javascript developer"
    And press "Search" 
    And I click on "Javascript developer" on the row containing "Active"
    And follow "Add Candidate to This Job Order Pipeline"
    And I switch to the iframe "popupInner"
    And follow "Add Candidate"
    And I fill in "firstName" with "John"
    And I fill in "lastName" with "Doe"
    And press "Add Candidate"
    And press "Close"
    And I switch to the iframe ""
    Then I should see "John"
    And I should see "Doe"