@core @sectors
Feature: Job Order Sector foundation
  @javascript
  Scenario: Administrators maintain stable Sector definitions
    Given I am authenticated as "Administrator"
    When I am on "/index.php?m=settings&a=sectors"
    And I fill in "sectorName1" with "Distribution & <Sector>"
    And I select "Inactive" from "sectorActive1"
    And I save Sector "1"
    Then I should see "Sector saved."
    And the "sectorName1" field should contain "Distribution & <Sector>"
    And the "sectorActive1" field should contain "0"
    When I fill in "sectorName1" with "Distribution and Logistics"
    And I select "Active" from "sectorActive1"
    And I save Sector "1"
    Then I should see "Sector saved."
    When I fill in "New Sector name" with "Browser custom Sector"
    And I press "Add Sector" and wait for navigation
    Then I should see "Sector saved."

  @javascript
  Scenario: Sector remains independent of Desk and supports combined filters
    Given I am authenticated as "Administrator"
    When I am on "/index.php?m=joborders&a=add&selected_company_id=1"
    Then the "sectorID" field should contain ""
    When I fill in "title" with "Sector browser job"
    And I fill in "city" with "London"
    And I select "Industrial" from "Desk"
    And I select "Technology" from "Sector"
    And I press "Add Job Order" and wait for navigation
    Then I should see "Technology"
    And I should see "Industrial"
    When I follow "Edit" and wait for navigation
    And I select "Finance" from "Sector"
    And I press "Save" and wait for navigation
    Then I should see "Finance"
    And I should see "Industrial"
    When I am on "/index.php?m=joborders"
    And I filter Job Orders by Desk "Industrial", Sector "Finance" and Recruiter "Administrator"
    Then I should see "Sector browser job"
    When I follow "Sector browser job" and wait for navigation
    And I follow "Edit" and wait for navigation
    And I select "Unclassified" from "Sector"
    And I press "Save" and wait for navigation
    Then I should see "Unclassified"
    And I should see "Industrial"
