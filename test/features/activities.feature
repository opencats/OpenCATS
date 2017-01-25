@core
Feature: Activities
  In order to build a customer and clients knowledge base for my organization, 
  As a user
  I need to be able to track all interaction in the application
  
  @javascript
  Scenario: Access activities page
    Given I am authenticated as "Administrator" 
    And There is a person called "Frodo Baggins" with "keySkills=leadership"
    And I am on "/index.php?m=candidates" 
    And I follow "Frodo"
    And I follow "Log an Activity"
    And I wait for the activity note box to appear
    And I switch to the iframe "popupInner"
    And fill in "activityNote" with "Call Gandalf"
    And press "Save"
    And press "Close"
    And I switch to the iframe ""
    And I follow "Activities"
    Then I should see "Frodo"
    And I should see "Call Gandalf"
    And I should see "Yesterday"
    And I should see "Today"
    And I should see "Last week"