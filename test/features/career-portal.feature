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

  @javascript @returning-career-candidate
  Scenario: Returning candidate authenticates and applies using session identity
    Given There is a public career portal job "Career Portal Returning Job" with questionnaire "CI Questionnaire"
    And a returning Career Portal candidate exists
    And I am on "/index.php?m=careers&p=showAll"
    When I follow "Career Portal Returning Job"
    And I click on the element "#applyToPosition"
    And I choose to apply as a returning candidate
    Then the returning candidate CAPTCHA is visible and usable
    When I fill in "email" with "career.portal.returning@example.com"
    And I fill in "lastName" with "Applicant"
    And I fill in "zip" with "12345"
    And I correctly complete the Career Portal CAPTCHA
    And I press "Continue to Application"
    Then the "firstName" field should contain "Returning"
    And the "email" field should contain "career.portal.returning@example.com"
    And the returning candidate is authenticated without a remembered-candidate cookie
    When I click on the element "#submitApplicationNow"
    Then I should see "CI Questionnaire"
    When I press "Continue"
    Then I should see "Application Submitted For: Career Portal Returning Job"
    And the application belongs to the returning candidate
    When I am on "/index.php?m=careers&p=showAll"
    Then I should see "Welcome back Returning"
    And the returning candidate is authenticated without a remembered-candidate cookie
    When I follow "Update Profile"
    Then the "firstName" field should contain "Returning"
    When I am on "/index.php?m=careers&p=showAll"
    And I follow "Log Out"
    Then the returning candidate is logged out
