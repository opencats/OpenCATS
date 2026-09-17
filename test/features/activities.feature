@core @activities
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
    And I filter Activities notes by "Call Gandalf"
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
    And I follow "All"
    And I filter Activities notes by "Manual timestamp note"
    Then I should see "Samwise"
    And I should see "Manual timestamp note"
    And the Activities grid contains "07-07-26 (04:37 PM)"

  @javascript @activities_grid
  Scenario: Activities sorting pagination and page size
    Given I am authenticated as "Administrator"
    And Activities has 32 dated entries
    Then I should see "First Name"
    And I should see "Last Name"
    And I should see "Regarding"
    And I should see "Entered By"
    And Activities shows "15" rows starting with "Activities fixture 32"
    When I sort Activities by "Date"
    Then Activities shows "15" rows starting with "Activities fixture 01"
    When I sort Activities by "Date"
    Then Activities shows "15" rows starting with "Activities fixture 32"
    When I follow "Next"
    Then Activities shows "15" rows starting with "Activities fixture 17"
    When I follow "Prev"
    And I select "30" from "Rows per page"
    Then Activities shows "30" rows starting with "Activities fixture 32"
    When I follow "Next"
    Then Activities shows "2" rows starting with "Activities fixture 02"

  @javascript @activities_grid
  Scenario: Activities filters and related record links
    Given I am authenticated as "Administrator"
    And Activities has 32 dated entries
    When I filter Activities notes by "Activities fixture 12"
    Then Activities shows "1" rows starting with "Activities fixture 12"
    When I filter Activities notes by "Activities fixture 31"
    Then Activities shows "1" rows starting with "Activities fixture 31"
    When I filter Activities notes by "Activities fixture"
    Then Activities shows "15" rows starting with "Activities fixture 32"
    When I follow "Action"
    Then the Activities panel "ActionAreafa38ef7cee937a20ca246bc3e411b5fb" is "visible"
    When I follow "ActivitiesFixture"
    Then I should see "Log an Activity"

  @javascript @activities_form
  Scenario: Activity validation toggles and cancel
    Given I am authenticated as "Administrator"
    And There is a person called "ActivitiesModal Fixture" with "keySkills=activities"
    And I follow "Log an Activity"
    And I wait for the activity note box to appear
    And I switch to the iframe "popupInner"
    When I press "Save"
    Then the Activities validation alert contains "You must select an activity type."
    And I confirm the popup
    When I uncheck "addActivity"
    Then the Activities field "activityTypeID" is "disabled"
    And the Activities field "activityNote" is "disabled"
    When I check "addActivity"
    Then the Activities field "activityNote" is "enabled"
    When I check "scheduleEvent"
    Then the Activities panel "scheduleEventDiv" is "visible"
    When I select "Email" from "activityTypeID"
    And I press "Save"
    Then the Activities validation alert contains "You must enter an event title."
    And I confirm the popup
    When I click on the element "#allDay1"
    Then the Activities field "hour" is "disabled"
    And the Activities field "duration" is "disabled"
    When I click on the element "#allDay0"
    Then the Activities field "hour" is "enabled"
    When I uncheck "scheduleEvent"
    Then the Activities panel "scheduleEventDiv" is "hidden"
    When I press "Cancel"
    And I switch to the iframe ""
    Then I should see "ActivitiesModal"

  @javascript @activities_form
  Scenario Outline: Log activity and schedule an event together using date and time preferences
    Given Activities uses "<format>" dates and "<clock>" hour time
    And Activities reminder controls are available
    And I am authenticated as "Administrator"
    And Activities has 32 dated entries
    And I follow "ActivitiesFixture"
    And I follow "Log an Activity"
    And I wait for the activity note box to appear
    And I switch to the iframe "popupInner"
    And I select "Meeting" from "activityTypeID"
    And I fill in "activityNote" with "Activities combined fixture"
    And I check "scheduleEvent"
    And I fill in "title" with "Activities scheduled fixture"
    And I fill in "description" with "Activities event description"
    And I set hidden field "dateAdd" to "<date>"
    And I select "<hour>" from "hour"
    And I select "30" from "minute"
    And I select "90" from "duration"
    And I check "publicEntry"
    And I check "reminderToggle"
    Then the Activities panel "reminderArea" is "visible"
    When I fill in "sendEmail" with "activities@example.invalid"
    And I select "30" from "reminderTime"
    And I press "Save"
    Then I should see "Activities combined fixture"
    And I should see "has been scheduled"
    And the Activities event is saved as "public" with duration "90"
    When I press "Close"
    And I switch to the iframe ""
    Then I should see "Activities combined fixture"

    Examples:
      | format | clock | date     | hour |
      | MDY    | 12    | 06-12-31 | 9    |
      | DMY    | 24    | 12-06-31 | 21   |

  @javascript @activities_form
  Scenario: Schedule an all-day private event without logging an activity
    Given I am authenticated as "Administrator"
    And Activities has 32 dated entries
    And I follow "ActivitiesFixture"
    And I follow "Schedule Event"
    And I wait for the activity note box to appear
    And I switch to the iframe "popupInner"
    Then the Activities panel "addActivityTR" is "hidden"
    And the Activities panel "scheduleEventDiv" is "visible"
    When I fill in "title" with "Activities scheduled fixture"
    And I click on the element "#allDay1"
    And I press "Save"
    Then I should see "has been scheduled"
    And the Activities event is saved as "private" with duration "0"

  @javascript @activities_grid
  Scenario: Activities columns can be hidden and restored
    Given I am authenticated as "Administrator"
    And Activities has 32 dated entries
    When I toggle the Activities column "Notes"
    Then the Activities column "Notes" is "hidden"
    When I toggle the Activities column "Notes"
    Then the Activities column "Notes" is "visible"
    When I toggle the Activities column "Reset to Default Columns"
    And I move the Activities Date column after First Name
    Then Activities starts with the "First Name" column
    When I toggle the Activities column "Reset to Default Columns"
    Then Activities starts with the "Date" column
