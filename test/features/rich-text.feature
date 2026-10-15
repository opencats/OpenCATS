@core @javascript @richtext
Feature: Rich-text editor contracts
  Scenario: Job descriptions survive copy, validation retry, save and edit
    Given I am authenticated as "Administrator"
    And a disposable rich-text job order
    When I open a copy of the rich-text job order
    Then the "description" editor contains "Existing &amp; copied é"
    And Internal Notes is a plain textarea
    When I set the "description" editor HTML to "<p>Copy <strong>saved</strong> é &amp; text</p>"
    And I fill in "title" with ""
    And I press "Add Job Order"
    And I confirm the popup
    And I fill in "title" with "Rich Text Saved Copy"
    And I fill in "city" with "London"
    And I press "Add Job Order" and wait for navigation
    Then the saved rich-text job description contains "<strong>saved</strong>"
    When I reopen the saved rich-text job order
    Then the "description" editor contains "<strong>saved</strong>"
    When I set the "description" source HTML to "<p>Source <em>edited</em></p><ul><li>Retained</li></ul>"
    And I press "Save" and wait for navigation
    Then the saved rich-text job description contains "<em>edited</em>"
    When I reopen the saved rich-text job order
    Then the "description" editor contains "<li>Retained</li>"

  Scenario: Candidate templates and source edits reach preview and POST without mail delivery
    Given I am authenticated as "Administrator"
    And disposable rich-text email fixtures
    When I open the rich-text candidate email form
    Then the "emailBody" editor is ready
    When I select "Rich Text Template" from "emailTemplate"
    Then the "emailBody" editor contains "%CANDFIRSTNAME%"
    And the "emailBody" editor contains "<br>"
    When I select the rich-text candidate preview
    Then I wait for rich-text preview "EditorCandidate"
    When I set the "emailBody" source HTML to "<p>Source %CANDFIRSTNAME%</p><p>Second</p>"
    And I select the rich-text candidate preview
    Then I wait for rich-text preview "Source EditorCandidate"
    And the "emailBody" editor contains "%CANDFIRSTNAME%"
    When I capture the email form submission without sending
    And I press "Send E-Mail"
    And I confirm the popup
    And I fill in "emailSubject" with "Editor regression"
    And I press "Send E-Mail"
    Then the captured email body is "<p>Source %CANDFIRSTNAME%</p><p>Second</p>"
    When I select "----" from "emailTemplate"
    Then the email editor and preview are empty
    When I press "Send E-Mail"
    Then empty email body submission remains allowed
    When I set the "emailBody" editor HTML to "<p>Keep on error</p>"
    And I select "Rich Text Empty Template" from "emailTemplate"
    Then an empty template error leaves the email body unchanged
