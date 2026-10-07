@core @companies @company_classification
Feature: Company commercial classification
  Background:
    Given I am authenticated as "Administrator"

  @javascript
  Scenario: Labels, independent classification and clearing through existing forms
    Given I am on "/index.php?m=settings&a=companyClassification"
    When I fill in "Tier A" with "Strategic accounts"
    And I press "Save labels" and wait for navigation
    Then I should see "Commercial tier labels saved."
    When I am on "/index.php?m=companies&a=add"
    And I fill in "Company Name" with "CRM classification browser fixture"
    And I select "A — Strategic accounts" from "Commercial tier"
    And I select "Fresh Prospect" from "Relationship lifecycle"
    And I press "Add Company" and wait for navigation
    Then I should see "CRM classification browser fixture"
    And I should see "A — Strategic accounts"
    And I should see "Fresh Prospect"
    When I follow "Edit"
    And I wait for "#commercialTier"
    And I select "Unclassified" from "Commercial tier"
    And I select "Lost" from "Relationship lifecycle"
    And I press "Save" and wait for navigation
    Then I should see "Unclassified"
    And I should see "Lost"
