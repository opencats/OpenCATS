@core
Feature: Candidate filters
  In order for my organization to be able to effectively actionate over the candidates in the database. 
  As an administrator
  I need to be able to filter my candidates by different criteria.
  
  @javascript
  Scenario: Filter candidates by contains
    Given I am authenticated as "Administrator" 
    And There is a person called "Frodo Baggins" with "keySkills=leadership"
    And There is a person called "Sam Gamyi" with "keySkills=gardening"
    And I am on "/index.php?m=candidates" 
    When I follow "Filter"
    And I select "Key Skills" from "filterResultsAreaTable0279d9da627056b26e6990f8bd470fbf1columnName"
    And I select "contains" from "filterResultsAreaTable0279d9da627056b26e6990f8bd470fbf1operator"
    And fill in "filterResultsAreaTable0279d9da627056b26e6990f8bd470fbf1value" with "leadership"
    And press "Apply"
    Then I should see "leadership"
    And I should not see "gardening"

  @javascript
  Scenario: Filter candidates by is equal to
    Given I am authenticated as "Administrator" 
    And There is a person called "Pippin Tuk" with "city=Shire"
    And There is a person called "Meriadoc Brandigamo" with "city=Bree"
    And I am on "/index.php?m=candidates" 
    When I follow "Filter"
    And I select "City" from "filterResultsAreaTable0279d9da627056b26e6990f8bd470fbf1columnName"
    And I select "is equal to" from "filterResultsAreaTable0279d9da627056b26e6990f8bd470fbf1operator"
    And fill in "filterResultsAreaTable0279d9da627056b26e6990f8bd470fbf1value" with "Shire"
    And press "Apply"
    Then I should see "Shire"
    And I should not see "Bree"
        
  @javascript
  Scenario: Add near zip code and change it to another field type
    Given I am authenticated as "Administrator" 
    And There is a person called "Pippin Tuk" with "city=Shire"
    And There is a person called "Meriadoc Brandigamo" with "city=Bree"
    And I am on "/index.php?m=candidates" 
    When I follow "Filter"
    And I select "Near Zipcode" from "filterResultsAreaTable0279d9da627056b26e6990f8bd470fbf1columnName"
    And I select "City" from "filterResultsAreaTable0279d9da627056b26e6990f8bd470fbf1columnName"
    And I select "is equal to" from "filterResultsAreaTable0279d9da627056b26e6990f8bd470fbf1operator"
    And fill in "filterResultsAreaTable0279d9da627056b26e6990f8bd470fbf1value" with "Shire"
    And press "Apply"
    Then I should not see "ZipCode"
    And I should see "City"
    And I should see "Shire"
    
  @javascript
  Scenario: Add all filters until the add new button is disabled
    Given I am authenticated as "Administrator" 
    And I am on "/index.php?m=candidates" 
    When I follow "Filter"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    And press "Add New"
    Then I should see "First Name"
    And I should see "Last Name"
    And I should see "E-Mail"
    And the "input[value='Add New']" element should contain "disabled"
    