@core @desks
Feature: Recruitment Desk foundation
  @javascript
  Scenario: User defaults and independent Job Order Desk edits
    Given I am authenticated as "Administrator"
    When I am on "/index.php?m=settings&a=editUser&userID=1"
    And I select "Commercial" from "Desk"
    And I press "Save" and wait for navigation
    Then I should see "Commercial"
    When I am on "/index.php?m=joborders&a=add&selected_company_id=1"
    Then the "deskID" field should contain "1"
    When I fill in "title" with "Desk browser job"
    And I fill in "city" with "London"
    And I select "Industrial" from "Desk"
    And I press "Add Job Order" and wait for navigation
    Then I should see "Desk browser job"
    And I should see "Industrial"
    When I follow "Edit"
    And I wait for "#deskID"
    And I select "Unassigned" from "Desk"
    And I press "Save" and wait for navigation
    Then I should see "Unassigned"
    When I am on "/index.php?m=settings&a=editUser&userID=1"
    And I select "Unassigned" from "Desk"
    And I press "Save" and wait for navigation
    When I am on "/index.php?m=joborders&a=add&selected_company_id=1"
    Then the "deskID" field should contain ""

  @javascript
  Scenario: Administrators can rename and retire a Desk
    Given I am authenticated as "Administrator"
    When I am on "/index.php?m=settings&a=desks"
    And I fill in "deskName1" with "Commercial & Recruitment"
    And I select "Inactive" from "deskActive1"
    And I press "Save Desk" and wait for navigation
    Then I should see "Desk saved."
    And the "deskName1" field should contain "Commercial & Recruitment"
    And the "deskActive1" field should contain "0"
    When I fill in "deskName1" with "Commercial"
    And I select "Active" from "deskActive1"
    And I press "Save Desk" and wait for navigation
    Then I should see "Desk saved."
