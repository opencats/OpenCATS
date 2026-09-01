@core
Feature: Career Portal
  In order to accept applications
  As a recruiter
  I need the career portal questionnaire process to complete successfully

  @javascript
  Scenario: Applicant submits a partially completed questionnaire
    Given There is a public career portal job "Career Portal CI Job" with questionnaire "CI Questionnaire"
    And I am on "/index.php?m=careers&p=showAll"
    When I follow "Career Portal CI Job"
    And I click on the element "#applyToPosition"
    And fill in "firstName" with "Career"
    And fill in "lastName" with "Applicant"
    And fill in "email" with "career.portal@example.com"
    And fill in "emailconfirm" with "career.portal@example.com"
    And I click on the element "#submitApplicationNow"
    Then I should see "CI Questionnaire"
    And I should see "First test question"
    And I should see "Second test question"
    When I click on the element "input[type='checkbox']"
    And press "Continue"
    Then I should see "Application Submitted For: Career Portal CI Job"

  Scenario: Rich-text job description is rendered
    Given There is a public career portal job "Career Portal Description Job" with questionnaire "Description Test Questionnaire"
    And I am on "/index.php?m=careers&p=showAll"
    When I follow "Career Portal Description Job"
    Then the "body" element should contain "<strong>Career Portal formatted description</strong>"
