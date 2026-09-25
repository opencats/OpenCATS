@core @careers
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

  @javascript
  Scenario Outline: Public landing, listing, details and application work at different widths
    Given There is a public career portal job "Career Portal Responsive Job" with questionnaire "Portal UI Questionnaire"
    And I view the career portal at "<width>" pixels wide
    And I am on "<entry>"
    Then I should see "Available Openings at"
    And the public portal attribution is visible
    And the public portal fits the viewport
    When I follow "Show All Jobs"
    Then I should see "Career Portal Responsive Job"
    And the public portal fits the viewport
    When I follow "Career Portal Responsive Job"
    Then I should see "Career Portal formatted description"
    And the public portal fits the viewport
    When I follow "Apply to Position"
    Then I should see "Applying to: Career Portal Responsive Job"
    And the application retains its multipart resume controls
    And the public portal fits the viewport
    And the public portal attribution is visible
    When I press "Submit Application Now"
    Then the career portal validation alert contains "first name"
    When I confirm the popup
    And I fill in "First Name:" with "Career"
    And I fill in "Last Name:" with "Applicant"
    And I fill in "Email Address:" with "career.portal.responsive@example.com"
    And I fill in "Confirm Email:" with "career.portal.responsive@example.com"
    And I press "Submit Application Now"
    Then I should see "Portal UI Questionnaire"
    And the public portal fits the viewport
    When I check "First test answer"
    And I press "Continue"
    Then I should see "Application Submitted For: Career Portal Responsive Job"
    And the public portal fits the viewport
    And the public portal attribution is visible

    Examples:
      | width | entry                |
      | 1280  | /index.php?m=careers  |
      | 390   | /careers/            |

  @javascript
  Scenario: Returning candidate logs in and views their profile
    Given candidate registration is enabled on the public portal
    And I am on "/careers/"
    When I fill in "Enter your e-mail address:" with "career.portal.profile@example.com"
    And I fill in "Last name:" with "Profile"
    And I fill in "Zip code:" with "12345"
    And I press "Login"
    Then I should see "Welcome back Career"
    When I follow "Update Profile"
    Then I should see "My Profile"
    And the "firstName" field should contain "Career"
    And the "email1" field should contain "career.portal.profile@example.com"
    When I view the career portal at "390" pixels wide
    Then the public portal fits the viewport
    And the public portal attribution is visible

  Scenario: Existing legacy template selection keeps its presentation
    Given There is a public career portal job "Career Portal Custom Job" with questionnaire "Portal UI Questionnaire"
    And the public portal uses the original custom template
    And I am on "/index.php?m=careers"
    Then I should see "Available Openings at"
    And the "body" element should contain "images/careers_show.gif"
    And the "head" element should not contain "bootstrap.min.css"
    And the "head" element should contain "Customer CSS remains untouched"
    When I am on "/index.php?m=careers&p=showAll"
    And I follow "Career Portal Custom Job"
    Then the "#applyToPosition" element should contain "images/careers_apply.gif"

  @javascript
  Scenario: Resume upload and populate retain application fields
    Given There is a public career portal job "Career Portal Resume Job" with questionnaire "Portal UI Questionnaire"
    And I am on "/careers/index.php?p=showAll"
    When I follow "Career Portal Resume Job"
    And I follow "Apply to Position"
    And I fill in "First Name:" with "Career"
    And I upload the public portal resume fixture
    Then the "firstName" field should contain "Career"
    And the "#resumeContents" element should contain "Public portal resume fixture"
    When I press "Populate Fields ->"
    Then the "firstName" field should contain "Career"
    And the "#resumeContents" element should contain "Public portal resume fixture"
    And the application retains its multipart resume controls

  @javascript
  Scenario: New candidate continues through registration to the application
    Given There is a public career portal job "Career Portal Registration Job" with questionnaire "Portal UI Questionnaire"
    And candidate registration is enabled on the public portal
    And I am on "/careers/index.php?p=showAll"
    When I follow "Career Portal Registration Job"
    And I follow "Apply to Position"
    Then I should see "I have not registered on this website."
    When I fill in "Enter your e-mail address:" with "career.portal.new@example.com"
    And I press "Continue to Application"
    Then I should see "Applying to: Career Portal Registration Job"
    And the "email" field should contain "career.portal.new@example.com"
    And the application retains its multipart resume controls
