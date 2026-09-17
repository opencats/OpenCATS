@core @calendar @javascript
Feature: Calendar
  Calendar navigation and event forms preserve the existing scheduling workflows.

  Scenario: Validate an incomplete event
    Given I am authenticated as "Administrator"
    And I am on "/index.php?m=calendar"
    When I follow "Add Event"
    And I press "Add Event"
    Then I should see "You must select an Event Type" in alert popup
    And I should see "You must enter a Description" in alert popup
    And I confirm the popup
    And I should see a "#addEventForm" element

  Scenario Outline: Create, navigate, edit and delete an event in each date and time format
    Given Calendar uses "<dateFormat>" dates and "<timeFormat>" hour time
    And Calendar reminder controls are available
    And I am authenticated as "Administrator"
    And I am on "/index.php?m=calendar&view=DAYVIEW&year=2030&month=6&day=12"
    When I follow "Add Event"
    And I fill in "title" with "Calendar workflow event"
    And I select "Meeting" from "type"
    And I select "Jun" from "dateAdd_Month_ID"
    And I select "12" from "dateAdd_Day_ID"
    And I fill in "dateAdd_Year_ID" with "30"
    And I select "09" from "hour"
    And I select "15" from "minute"
    And I fill in "description" with "Calendar workflow details"
    And I check "reminderToggle"
    Then Calendar panel "sendEmailTD" should be "visible"
    When I fill in "sendEmail" with "calendar-ui@example.invalid"
    And I select "30 min early" from "reminderTime"
    And I press "Add Event"
    Then I wait until I see "Calendar workflow details"
    And the "#viewEventTitle" element should contain "Calendar workflow event"
    And the "#calendarDay" element should contain "Calendar workflow event"
    And Calendar view "calendarDay" should have "1" events
    And the "#viewEventReminder" element should contain "30 minutes"
    When I click on the element "#linkDayForeward > *"
    Then Calendar view "calendarDay" should have "0" events
    When I click on the element "#linkDayBack > *"
    Then Calendar view "calendarDay" should have "1" events
    When I click on the element "#calendarDayParent [data-calendar-view=week]"
    Then the "#calendarWeek" element should contain "Calendar workflow event"
    When I click on the element "#linkWeekForeward > *"
    Then Calendar view "calendarWeek" should have "0" events
    When I click on the element "#linkWeekBack > *"
    Then Calendar view "calendarWeek" should have "1" events
    When I click on the element "#calendarWeekParent [data-calendar-view=month]"
    Then the "#calendarMonth" element should contain "Calendar workflow event"
    When I click on the element "#linkMonthForeward > *"
    And I click on the element "#linkMonthBack > *"
    Then the "#calendarMonth" element should contain "Calendar workflow event"
    When I click on the element "#calendarMonth .calendarEntry"
    And I press "Edit Event"
    Then the "dateEdit_Year_ID" field should contain "30"
    And the "minuteEdit" field should contain "15"
    And Calendar panel "sendEmailTDEdit" should be "visible"
    When I uncheck "reminderToggleEdit"
    Then Calendar panel "sendEmailTDEdit" should be "hidden"
    When I fill in "titleEdit" with "Calendar workflow updated"
    And I fill in "descriptionEdit" with "Updated calendar details"
    And I click on the element "#allDayEdit1"
    Then Calendar field "hourEdit" should be "disabled"
    And Calendar field "durationEdit" should be "disabled"
    When I press "Save"
    Then I wait until I see "Updated calendar details"
    And the "#viewEventTitle" element should contain "Calendar workflow updated"
    And the "#viewEventTime" element should contain "All Day / No Specific Time"
    When I press "Edit Event"
    And I press "Delete"
    Then I should see "Are you sure you want to delete this entry?" in alert popup
    When I cancel the Calendar dialog
    Then Calendar panel "editEventTD" should be "visible"
    When I press "Delete"
    And I confirm the popup
    Then I should not see "Calendar workflow updated"
    When I follow "Goto Today"
    And I follow "My Upcoming Events"
    Then I should see "My Upcoming Events / Calls"

    Examples:
      | dateFormat | timeFormat |
      | MDY        | 12         |
      | DMY        | 24         |

  Scenario Outline: Navigate beyond cached months and select a date to create an event
    Given Calendar AJAX is "<ajax>"
    And I am authenticated as "Administrator"
    And I am on "/index.php?m=calendar&view=MONTHVIEW&year=2030&month=6"
    When I click on the element "#linkMonthForeward > *"
    And I click on the element "#linkMonthForeward > *"
    Then I wait until I see "August 2030"
    And I wait for "#linkMonthForeward > *"
    When I click on the element "#linkMonthBack > *"
    And I click on the element "#linkMonthBack > *"
    Then the Calendar heading should be "Calendar: June 2030"
    When I click on the element "#calendarMonthCell15"
    Then Calendar panel "addEventTD" should be "visible"
    And Calendar field "hour" should be "disabled"
    When I click on the element "#allDay0"
    Then Calendar field "hour" should be "enabled"
    And Calendar field "duration" should be "enabled"
    When I follow "My Upcoming Events"
    Then Calendar panel "upcomingEventsTD" should be "visible"
    And Calendar panel "addEventTD" should be "hidden"

    Examples:
      | ajax     |
      | enabled  |
      | disabled |

  Scenario Outline: Open grouped events and preserve their record associations while editing
    Given I am authenticated as "Administrator"
    And Calendar has grouped events linked to a "<kind>"
    And I am on "/index.php?m=calendar&view=MONTHVIEW&year=2030&month=6"
    Then the "#calendarMonth" element should contain "2 Events"
    When I click on the element "#calendarMonth .calendarEntryMultiple a"
    Then Calendar view "calendarDay" should have "2" events
    When I click on the element "#calendarDay .calendarEntry"
    Then the Calendar event association should still link to "<kind>"
    When I press "Edit Event"
    And I fill in "descriptionEdit" with "Edited linked calendar details"
    And I press "Save"
    Then I wait until I see "Edited linked calendar details"
    And the Calendar event association should still link to "<kind>"
    When I click on the element "#viewEventLink a"
    Then I should see "Calendar workflow <kind>"

    Examples:
      | kind      |
      | company   |
      | candidate |
      | contact   |
      | joborder  |

  Scenario: Refresh private events and keep read-only event actions restricted
    Given I am authenticated as "Administrator"
    And Calendar has grouped events linked to a "company"
    And Calendar has another user's private event
    And I am on "/index.php?m=calendar&view=DAYVIEW&year=2030&month=6&day=12"
    Then Calendar view "calendarDay" should have "2" events
    When I check "hideNonPublic"
    Then Calendar view "calendarDay" should have "3" events
    When I uncheck "hideNonPublic"
    Then Calendar view "calendarDay" should have "2" events
    When I press "Logout"
    And I login as "testerRead" "tester"
    And I am on "/index.php?m=calendar&view=DAYVIEW&year=2030&month=6&day=12"
    Then Calendar view "calendarDay" should have "3" events
    When I click on the element "#calendarDay .calendarEntry"
    Then Calendar panel "viewEventTD" should be "visible"
    And I should not see "Edit Event"
    And I should not see "Add Event"
