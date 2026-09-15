@core @candidates
Feature: Candidate workflows
  Recruiters can create, update and find candidate records.

  @javascript
  Scenario: Add, edit and find a candidate
    Given I am authenticated as "Administrator"
    And I am on "/index.php?m=candidates&a=add"
    When I fill in "firstName" with "BootstrapCandidate"
    And I fill in "lastName" with "Migration"
    And I press "Add Candidate"
    Then I should see "BootstrapCandidate"
    When I follow "edit_link"
    And I fill in "keySkills" with "Candidate workflow verification"
    And I press "Save"
    Then I should see "Candidate workflow verification"
    When I am on "/index.php?m=candidates&a=search"
    And I select "Candidate Name" from "searchMode"
    And I fill in "searchText" with "BootstrapCandidate"
    And I press "searchCandidates"
    Then I should see "BootstrapCandidate"
    When I follow "BootstrapCandidate"
    Then I should see "Candidate workflow verification"

  Scenario: Search for a candidate with no matches
    Given I am authenticated as "Administrator"
    And I am on "/index.php?m=candidates&a=search"
    When I select "Candidate Name" from "searchMode"
    And I fill in "searchText" with "NoCandidateMatches987654321"
    And I press "searchCandidates"
    Then I should see "No matching entries found."
