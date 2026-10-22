@core @calendar @calendar-xss @javascript
Feature: Calendar stored text stays inert at HTML output boundaries

  Scenario Outline: Existing title probes are literal in every grid and AJAX data
    Given Calendar AJAX is "enabled"
    And an existing public Calendar probe in "title"
    And I am authenticated as "Administrator"
    When I am on "/index.php?m=calendar&view=<view>&year=2030&month=6&day=12&week=3"
    Then Calendar contains no executable probe markup
    And Calendar "#<grid>" displays the literal "title" probe
    When I navigate away and AJAX-load the Calendar probe month
    Then Calendar contains no executable probe markup
    And Calendar "#calendarMonth" displays the literal "title" probe

    Examples:
      | view      | grid          |
      | MONTHVIEW | calendarMonth |
      | WEEKVIEW  | calendarWeek  |
      | DAYVIEW   | calendarDay   |

  Scenario: Descriptions stay inert until and after opening details
    Given Calendar AJAX is "enabled"
    And an existing public Calendar probe in "description"
    And I am authenticated as "Administrator"
    And I am on "/index.php?m=calendar&view=MONTHVIEW&year=2030&month=6"
    Then Calendar contains no executable probe markup
    When I open the stored Calendar probe
    Then Calendar contains no executable probe markup
    And Calendar "#viewEventDescription" displays the literal "description" probe
    When I navigate away and AJAX-load the Calendar probe month
    And I click on the element "#calendarMonth .calendarEntry"
    Then Calendar "#viewEventDescription" displays the literal "description" probe

  Scenario: Owner names remain plain text in grids and details
    Given an existing public Calendar probe in "owner"
    And I am authenticated as "Administrator"
    When I open the stored Calendar probe
    Then Calendar contains no executable probe markup
    And Calendar "#calendarDay" displays the literal "owner" probe
    And Calendar "#viewEventOwner" displays the literal "owner" probe

  Scenario: Add Edit creates and edits literal text viewed by another user
    Given Calendar uses "MDY" dates and "12" hour time
    When the Add Edit user creates a public Calendar probe
    Then Calendar contains no executable probe markup
    And Calendar "#viewEventTitle" displays the literal "title" probe
    And Calendar "#viewEventDescription" displays the literal "description" probe
    When the Calendar probe is saved twice without changing its text
    And I press "Logout"
    And I am authenticated as "Administrator"
    And I open the stored Calendar probe
    Then Calendar "#viewEventTitle" displays the literal "title" probe
    And Calendar "#viewEventDescription" displays the literal "description" probe
    When I press "Logout"
    And I login as "testerRead" "opencats-test-security-903"
    And I open the stored Calendar probe
    Then Calendar contains no executable probe markup
    And I should not see "Edit Event"
    And I should not see "Add Event"

  Scenario Outline: Linked record labels remain text inside working links
    Given I am authenticated as "Administrator"
    And Calendar has grouped events linked to a "<kind>"
    And the Calendar "<kind>" association has a probe label
    And I am on "/index.php?m=calendar&view=DAYVIEW&year=2030&month=6&day=12"
    When I click on the element "#calendarDay .calendarEntry"
    Then Calendar contains no executable probe markup
    And Calendar "#viewEventLink" displays the literal "title" probe
    And the Calendar association retains its icon and navigable URL for "<kind>"

    Examples:
      | kind      |
      | company   |
      | candidate |
      | contact   |
      | joborder  |
