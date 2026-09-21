@core @importExport
Feature: Import presentation and Export download contracts
  Background:
    Given I am authenticated as "Administrator"

  Scenario: All supported import types remain available
    When I am on "/index.php?m=import"
    Then I should see "Do not discard the original data!"
    And Import offers its five original type values

  @javascript
  Scenario Outline: Structured uploads advance to mapping without importing records
    When I am on "/index.php?m=import&a=importSelectType&typeOfImport=<type>"
    Then the Import upload contract is intact
    When I upload the small "<format>" Import fixture
    Then I should see "Map Data"
    And I should see "First Name"
    And I select "cats" from "importType0"
    And I select "first_name" from "importIntoField0"
    And the "importIntoField0" field should contain "first_name"
    And Import sample data can be shown and hidden
    And Import loading preserves the form submission contract
    Examples:
      | type       | format |
      | Candidates | csv    |
      | Contacts   | tab    |

  @javascript
  Scenario: Missing upload displays the existing error
    When I am on "/index.php?m=import&a=importSelectType&typeOfImport=Companies"
    And I press "Next"
    Then I should see "No file was uploaded."

  @javascript
  Scenario: Recent errors retain a POST revert with the correct import ID
    Given a disposable Import history entry
    When I open the disposable Import errors
    Then I should see "Line 2: focused import error"
    And the Import revert contract is intact
    When I press "Revert Import"
    Then I should see "The revert was successful."
    And the disposable Import history entry is gone

  Scenario Outline: Destructive import routes reject GET
    When I am on "/index.php?m=import&a=<action>"
    Then I should see "Invalid request."
    Examples:
      | action            |
      | revert            |
      | deleteBulkResumes |
      | importBulkResumes |

  Scenario: Resume import opens the existing wizard
    When I am on "/index.php?m=import&a=importSelectType&typeOfImport=resume"
    Then I should see "Upload resume documents"
    And I should see "Process Documents"
    And I should see "Review"
    And I should see "Finish Up"

  Scenario: Bulk help keeps its standalone content and close action
    When I am on "/index.php?m=import&a=whatIsBulkResumes"
    Then I should see "Bulk Resumes"
    And I should see "make them searchable"
    And the response should contain "parentGoToURL"

  Scenario: Bulk resume queue opens
    When I am on "/index.php?m=import&a=showMassImport"
    Then I should see "How do I use bulk resumes?"
    And the response should contain "value=\"Back\""

  Scenario Outline: Legacy Export returns CSV for selected and explicit-page records
    When I am on "/index.php?m=export&a=export&dataItemType=100&<selection>"
    Then the Export response is a CSV download containing "Tuk"
    Examples:
      | selection                       |
      | onlySelected=1&checked_20000=on |
      | ids=20000                       |

  Scenario: Candidate DataGrid keeps its Export action and CSV response
    When I am on "/index.php?m=candidates"
    Then the response should contain "exportByDataGrid"
    When I request the candidate DataGrid export
    Then the Export response is a CSV download containing "Tuk"

  @javascript
  Scenario: Contacts options retain their visibility and company validation
    When I am on "/index.php?m=import&a=importSelectType&typeOfImport=Contacts"
    And I upload the small "csv" Import fixture
    Then the Contacts mapping options still work

  @javascript
  Scenario Outline: Specialised mass-import templates render deterministic parser states
    When I open the "<view>" Import presentation fixture
    Then I should see "<text>"
    And the Import fixture fits a narrow viewport
    Examples:
      | view     | text                                     |
      | queue    | You have 1 document in your upload queue. |
      | empty    | To import multiple files                 |
      | progress | processes your resume documents          |
      | review   | Parsed and Ready to Import               |
      | finish   | failed.txt                               |
      | edit     | Candidate Details                        |
      | error    | Import preview error.                    |
      | modal    | Import preview error.                    |
      | recent   | Line 2: focused import error              |
      | bulk     | CATS has found 1 files to import.         |
      | landing  | unclassified resume documents            |

  @javascript
  Scenario: Manual edit keeps copy controls and save identity
    When I open the "edit" Import presentation fixture
    Then the mass-import copy and save controls work

  @javascript
  Scenario: Manual edit needs no external validation script
    When I open the "edit" Import presentation fixture
    Then the mass-import editor works without remote validation

  @javascript
  Scenario: Mass-import progress remains proportional
    When I open the "progress" Import presentation fixture
    Then mass-import progress fills half the available width
