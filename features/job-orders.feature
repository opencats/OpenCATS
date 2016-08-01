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
