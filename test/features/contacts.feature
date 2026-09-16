@core @contacts
Feature: Contacts
  Contacts use the shared OpenCATS UI while preserving contact workflows.

  @javascript
  Scenario: Add, edit and find a contact for a company
    Given I am authenticated as "Administrator"
    And There is a company called "Contacts UI Company"
    And I am on "/index.php?m=contacts&a=add"
    Then I should see a "main #addContactForm" element
    When I fill in "Company" with "Contacts UI Company"
    And I wait for "#CompanyResults div#suggest0"
    And I click on the element "#CompanyResults div#suggest0"
    And I fill in "First Name" with "BootstrapContact"
    And I fill in "Last Name" with "Migration"
    And I fill in "Work Phone" with "01234567890"
    And I press "Add Contact"
    Then I should see "BootstrapContact"
    And I should see "Contacts UI Company"
    And I should see a "main.oc-contact-show-page" element
    When I follow "edit_link"
    Then I should see a "main #editContactForm" element
    When I fill in "Title" with "Updated contact title"
    And I press "Save"
    Then I should see "Updated contact title"
    When I follow "Search Contacts"
    And I select "Contact Name" from "Search By"
    And I fill in "Search Text" with "BootstrapContact"
    And I press "searchContacts"
    Then I should see "BootstrapContact"
    And I should see a ".oc-contact-search-results table tbody a" element
    When I follow "Cold Call List"
    Then I should see "BootstrapContact"
    And I should see "Contacts UI Company"

  Scenario: Search with no matching contacts
    Given I am authenticated as "Administrator"
    And I am on "/index.php?m=contacts&a=search"
    When I fill in "Search Text" with "NoMatchingContact987654321"
    And I press "searchContacts"
    Then I should see "No matching entries found."
    And I should not see a ".oc-contact-search-results table" element
