@core @home
Feature: Home dashboard and global Quick Search
  Background:
    Given I am authenticated as "Administrator"

  Scenario: Dashboard keeps its widgets and global search
    When I am on "/index.php?m=home"
    Then I should see "My Recent Calls"
    And I should see "My Upcoming Calls"
    And I should see "My Upcoming Events"
    And I should see "Recent Hires"
    And I should see "Hiring Overview"
    And I should see "Important Candidates"
    And I should see a "input[name=quickSearchFor]" element
    And I should see a "#homeGraph" element

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

  @javascript @home_graph
  Scenario: Hiring Overview keeps period switching
    When I am on "/index.php?m=home"
    When I press "Monthly"
    Then the Home graph view is "1"
    When I press "Yearly"
    Then the Home graph view is "2"
    When I press "Weekly"
    Then the Home graph view is "0"

  @javascript @home_grid
  Scenario: Important Candidates keeps sorting, paging and Show All
    Given Home has 16 important candidates
    When I am on "/index.php?m=home"
    And I sort Home candidates by "First Name"
    Then Home shows "15" fixture rows starting with "HomeFixture01"
    When I follow "Next"
    Then Home shows "1" fixture rows starting with "HomeFixture16"
    When I follow "Show All"
    Then Home shows "16" fixture rows starting with "HomeFixture"
    When I sort Home candidates by "First Name"
    Then Home shows "16" fixture rows starting with "HomeFixture01"
    When I sort Home candidates by "First Name"
    Then Home shows "16" fixture rows starting with "HomeFixture16"
    And Home candidate links reach their records

  # Existing backend failure: https://github.com/opencats/OpenCATS/issues/895
  # Keep the image assertion active; period controls are checked independently.
  @javascript @home_graph_image
  Scenario: Hiring Overview server image loads
    When I am on "/index.php?m=home"
    Then the Home graph is loaded

  @home_widgets
  Scenario: Calls, calendar output and Recent Hires retain their records
    Given Home has recent calls, upcoming calendar entries and a hire
    When I am on "/index.php?m=home"
    Then Home widget records and Calendar links are present
    When I follow "Home upcoming call"
    Then the Home destination contains "showEvent="

  @javascript @home_search
  Scenario: Quick Search retains wildcard matching and sortable candidate results
    Given Home has 16 important candidates
    When I am on "/index.php?m=home"
    And I fill in "quickSearchFor" with "HomeFixture*"
    And I press "Go"
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
