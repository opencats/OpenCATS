@core
Feature: Customize extra fields
  In order for my organization to be able to effectively track relevant information about candidates, job orders, companies and contacts. 
  As an administrator
  I need to be able to customize the fields that each entity has
  
  @javascript
  Scenario: Adding an extra field for job orders
    Given I am authenticated as "Administrator" 
    And I am on "/index.php?m=settings&a=customizeExtraFields" 
    When I follow "Add field to Job Orders"
    And fill in "addFieldName0" with "Compensation range"
    And press "Add Field"
    And press "Save"
    Then I should see "Compensation range" 
    And I should see "Text Box"
