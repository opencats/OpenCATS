@core @home
Feature: Home dashboard and global Quick Search
  Background:
    Given I am authenticated as "Administrator"

  @home_data
  Scenario: Home shows assigned work, account, current job and appointment records
    Given Home has working-day records for "admin"
    When I am on "/index.php?m=home"
    Then Home shows the working-day records
    When I open the seeded Home Task
    Then I should see "Home regression follow-up"

  @home_search
  Scenario Outline: Global search keeps entity results and links
    When I am on "/index.php?m=home"
    And I fill in "quickSearchFor" with "<query>"
    And I press "Go"
    Then I should see "<section> Results"
    And the "quickSearchFor" field should contain "<query>"
    And I follow Home result "<link>" for "<destination>"
    Then the Home destination contains "<destination>"
    Examples:
      | query    | section    | link            | destination        |
      | OpenCATS | Job Orders | OpenCATS Tester | jobOrderID=40001   |
      | Tuk      | Candidates | Tuk             | candidateID=20000  |
      | Google   | Companies  | Google          | companyID=20002    |
      | Elizabeth | Contacts  | Elizabeth       | contactID=30001    |

  @home_search
  Scenario: Global search has separate empty results
    When I am on "/index.php?m=home"
    And I fill in "quickSearchFor" with "HomeNoMatch9f683b"
    And I press "Go"
    Then I should see "Job Orders Results"
    And I should see "Candidates Results"
    And I should see "Companies Results"
    And I should see "Contacts Results"
    And I should see "No matching entries found."
    And the "quickSearchFor" field should contain "HomeNoMatch9f683b"

  @home_data
  Scenario: Resolving the underlying work updates Home
    Given Home has working-day records for "admin"
    When I am on "/index.php?m=home"
    Then Home shows the working-day records
    When the Home working-day records are resolved
    And I am on "/index.php?m=home"
    Then Home no longer shows the resolved working-day records

  @home_data @javascript
  Scenario: Priority context loads recorded Activity for its linked Job Order
    Given Home has working-day records for "admin"
    When I am on "/index.php?m=home"
    And I open context for the seeded Home priority
    Then Home displays the seeded recorded Activity
    When the seeded Home Activity is corrected
    And I am on "/index.php?m=home"
    And I open context for the seeded Home priority
    Then Home displays the seeded recorded Activity

  @home_data
  Scenario: Home warning evidence responds to a qualifying Company contact
    Given Home has working-day records for "admin"
    When I am on "/index.php?m=home&a=priorities&filter=warnings"
    Then Home shows the seeded Company warning
    When the seeded Company gets a qualifying contact and next action
    And I am on "/index.php?m=home&a=priorities&filter=warnings"
    Then Home shows the changed Company warning evidence

  @home_data
  Scenario: Home filters retain the seeded Task and its native destination
    Given Home has working-day records for "admin"
    When I am on "/index.php?m=home&a=priorities&filter=tasks"
    Then I should see "Home regression follow-up"
    When I follow "Home regression follow-up"
    Then I should see "Home regression follow-up"

  @javascript @home_search
  Scenario: Quick Search retains wildcard matching and sortable candidate results
    Given Home has 16 important candidates
    When I am on "/index.php?m=home"
    And I fill in "quickSearchFor" with "HomeFixture*"
    And I press "Go" and wait for navigation
    Then I should see "Candidates Results"
    And I should see "HomeFixture01"
    And I should see "HomeFixture16"
    And the "quickSearchFor" field should contain "HomeFixture*"
    When I sort Home search candidates twice

  @home_errors
  Scenario: Missing Quick Search query keeps the friendly error
    When I am on "/index.php?m=home&a=quickSearch"
    Then I should see "No query string specified."
    And I should see "Quick Search"

  @home_errors
  Scenario Outline: Error templates preserve messages, hooks and modal behavior
    Then the Home "<kind>" error preserves its content and shell contract
    Examples:
      | kind  |
      | fatal |
      | page  |
      | modal |
      | demo  |
