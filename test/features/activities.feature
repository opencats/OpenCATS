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
    And I select "Not reached" from "activityTypeID"
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

  @javascript
  Scenario: Log activity with manual date and time
    Given I am authenticated as "Administrator"
    And There is a person called "Samwise Gamgee" with "keySkills=gardening"
    And I am on "/index.php?m=candidates"
    And I follow "Samwise"
    And I follow "Log an Activity"
    And I wait for the activity note box to appear
    And I switch to the iframe "popupInner"
    And I select "Not reached" from "activityTypeID"
    And fill in "activityNote" with "Manual timestamp note"
    And I set hidden field "activityDate" to "07-07-26"
    And I select "4" from "activityHour"
    And I select "37" from "activityMinute"
    And I select "PM" from "activityMeridiem"
    And press "Save"
    And press "Close"
    And I switch to the iframe ""
    And I follow "Activities"
    Then I should see "Samwise"
    And I should see "Manual timestamp note"
    And I should see "07-07-26 (04:37 PM)"
