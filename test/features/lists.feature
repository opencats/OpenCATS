@core @lists
Feature: Saved Lists presentation and shared workflows
  Background:
    Given I am authenticated as "Administrator"

  Scenario: Lists home retains its columns and links
    When I am on "/index.php?m=lists"
    Then I should see "Lists: Home"
    And I should see "Show Lists"
    And I should see "Count"
    And I should see "Description"
    And I should see "Data Type"
    And I should see "List Type"
    And I should see "Owner"
    And I should see "Created"
    And I should see "Modified"
    When I follow "UK Candidates"
    Then I should see "Lists: UK Candidates"
    And I should see "Tuk"
    And I should see "Delete List"

  @javascript
  Scenario: Lists home retains sorting and rows per page
    When I am on "/index.php?m=lists"
    And I select "30 / page" from "Rows per page"
    Then the "Rows per page" field should contain "30"
    When I follow "Description"
    Then I should see "UK Candidates"
    And the Lists grid parameter "sortDirection" is "ASC"
    When I follow "Description"
    Then the Lists grid parameter "sortDirection" is "DESC"
    When I follow "U"
    Then I should see "UK Candidates"
    When I follow "ALL"
    Then I should see "UK Candidates"

  Scenario Outline: Saved lists reuse each entity grid
    Given a disposable "<kind>" saved list
    When I open the disposable saved list
    Then I should see "Lists: Lists workflow fixture"
    And I should see "<member>"
    And I should see "Delete List"
    And I should see "More filters"
    And I should see "Action"
    And the response should contain "Remove From This List"
    And the response should contain "exportByDataGrid"
    Examples:
      | kind      | member          |
      | candidate | Tuk             |
      | company   | Google          |
      | contact   | Elizabeth       |
      | joborder  | OpenCATS Tester |

  @javascript
  Scenario Outline: Add To List modal keeps checkbox, edit and new states
    Given a disposable "<kind>" saved list
    When I open the Lists modal for "<kind>"
    Then I should see "Lists workflow fixture"
    And I should see "Add To Lists"
    And I should see "Cancel"
    When I select and edit the disposable list
    Then the disposable list name is "Lists workflow fixture"
    And I should see "Delete"
    And I should see "Save"
    When I press "New List"
    Then I should see "New list name"
    Examples:
      | kind      |
      | candidate |
      | company   |
      | contact   |
      | joborder  |

  @javascript
  Scenario: Modal can rename, create and add a candidate to a list
    Given a disposable "candidate" saved list
    When I open the Lists modal for "candidate"
    And I select and edit the disposable list
    And I rename the disposable list to "Lists workflow renamed"
    And I save the disposable list name
    Then I wait until I see "Lists workflow renamed"
    When I press "New List"
    And I fill in "New list name" with "Lists workflow created"
    And I save the new Lists name
    Then I wait until I see "Lists workflow created"
    When I check "Lists workflow created"
    And I press "Add To Lists"
    Then I wait until I see "Items have been added to lists successfully."
    And the new list contains the candidate

  @javascript
  Scenario: DataGrid selection opens the shared modal and Cancel closes it
    When I open Add To List with two selected candidates
    Then the Lists modal retains the selected candidates
    When I press "Cancel"
    Then the Lists modal closes

  @javascript
  Scenario: Removing membership and deleting an empty list keeps the candidate
    Given a disposable "candidate" saved list
    When I open the disposable saved list
    And I remove the candidate from the disposable list
    Then I should see "(0 Items)"
    And the disposable list has no members and the candidate still exists
    When I press "Delete List"
    Then I should see "Lists: Home"
    And the disposable list is deleted and the candidate still exists

  @javascript
  Scenario: Lists pagination keeps the shared grid parameters
    Given 16 disposable lists for pagination
    When I am on "/index.php?m=lists"
    And I follow "Next"
    Then the Lists grid parameter "rangeStart" is "15"
    When I follow "Prev"
    Then the Lists grid parameter "rangeStart" is "0"

  @javascript
  Scenario: Saved list filters can be applied and cleared
    When I am on "/index.php?m=lists&a=showList&savedListID=60001"
    And I press "More filters"
    And I filter the saved list by first name "NoSuchListsMember"
    Then I should see "(0 Items)"
    When I press "Remove All"
    Then I should see "Tuk"

  @javascript
  Scenario: Deleting an empty list in the modal keeps the remaining rows usable
    Given an empty disposable saved list
    When I open the Lists modal for "candidate"
    And I select and edit the disposable list
    And I delete the disposable list in the modal
    Then I should see "UK Candidates"
    And I should see "New List"
    And the disposable list is deleted and the candidate still exists
