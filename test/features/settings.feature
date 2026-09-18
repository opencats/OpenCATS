@core @settings
Feature: Settings presentation and existing workflows
  Background:
    Given I am authenticated as "Administrator"

  @settings_core
  Scenario Outline: Core Settings pages open
    When I am on "<route>"
    Then I should see "<text>"
    And I should not see "Fatal error"

    Examples:
      | route                                                     | text                |
      | /index.php?m=settings                                      | View Profile        |
      | /index.php?m=settings&a=administration                      | Site Management     |
      | /index.php?m=settings&a=administration&s=siteName           | New Site Name       |
      | /index.php?m=settings&a=administration&s=localization       | Localization        |

  @settings_users
  Scenario: Profile and edit user retain their navigation
    When I am on "/index.php?m=settings&a=manageUsers"
    Then I should see "User Management"
    When I follow "Administrator"
    Then I should see "User Details"
    When I follow "Edit"
    Then I should see "Edit Site User"
    And I should see "First Name"

  @settings_users
  Scenario: Add User form retains required fields
    When I am on "/index.php?m=settings&a=addUser"
    Then I should see "Add Site User"
    And I should see "Retype Password"
    And I should see "Access Level"

  @settings_users @javascript
  Scenario: Change Password rejects empty submission
    When I am on "/index.php?m=settings&a=myProfile&s=changePassword"
    Then I should see "Current Password"
    When I press "Change Password"
    Then the Settings validation alert contains "You must enter your current password."
    And I confirm the popup

  @settings_email
  Scenario Outline: Email configuration pages open
    When I am on "<route>"
    Then I should see "<text>"
    And I should not see "Fatal error"
    Examples:
      | route                                       | text               |
      | /index.php?m=settings&a=emailSettings        | E-Mail Settings    |
      | /index.php?m=settings&a=emailTemplates       | E-Mail Templates   |

  @settings_customize
  Scenario Outline: Customization pages open
    When I am on "<route>"
    Then I should see "<text>"
    And I should not see "Fatal error"
    Examples:
      | route                                           | text                   |
      | /index.php?m=settings&a=customizeCalendar         | Calendar Customization |
      | /index.php?m=settings&a=customizeExtraFields      | Customize Extra Fields |
      | /index.php?m=settings&a=reports                   | Report Settings        |

  @settings_eeo
  Scenario: EEO settings open
    When I am on "/index.php?m=settings&a=eeo"
    Then I should see "Track Gender"
    And I should see "Track Disability Status"

  @settings_career
  Scenario Outline: Career Portal administration opens
    When I am on "<route>"
    Then I should see "<text>"
    And I should not see "Fatal error"
    Examples:
      | route                                                   | text                          |
      | /index.php?m=settings&a=careerPortalSettings              | Enable Public Career Portal   |
      | /index.php?m=settings&a=careerPortalQuestionnaire         | Careers Website Questionnaire |

  @settings_system
  Scenario Outline: System and history pages open
    When I am on "<route>"
    Then I should see "<text>"
    And I should not see "Fatal error"
    Examples:
      | route                                                    | text                      |
      | /index.php?m=settings&a=createBackup                      | Create Site Backup        |
      | /index.php?m=settings&a=loginActivity                     | Recent Login Activity     |
      | /index.php?m=settings&a=administration&s=systemInformation | PHP Version               |

  @settings_contracts
  Scenario Outline: Settings form submission contracts remain intact
    When I am on "<route>"
    Then the Settings form "<form>" submits to action "<action>" using "post"
    Examples:
      | route                                               | form                     | action                 |
      | /index.php?m=settings&a=administration&s=siteName      | changeSiteNameForm       | administration         |
      | /index.php?m=settings&a=administration&s=localization  | localizationForm         | administration         |
      | /index.php?m=settings&a=addUser                       | addUserForm              | addUser                |
      | /index.php?m=settings&a=myProfile&s=changePassword     | changePasswordForm       | changePassword         |
      | /index.php?m=settings&a=emailSettings                 | emailSettingsForm        | emailSettings          |
      | /index.php?m=settings&a=customizeCalendar             | editCalendarForm         | customizeCalendar      |
      | /index.php?m=settings&a=customizeExtraFields          | editSettingsForm         | customizeExtraFields   |
      | /index.php?m=settings&a=eeo                           | EEOForm                  | eeo                    |
      | /index.php?m=settings&a=careerPortalSettings          | careerPortalSettingsForm | careerPortalSettings   |
      | /index.php?m=settings&a=careerPortalQuestionnaire     | questionnaireForm        | careerPortalQuestionnaire |

  @settings_users @javascript
  Scenario: Add User validation remains active
    When I am on "/index.php?m=settings&a=addUser"
    And I press "Add User"
    Then the Settings validation alert contains "You must enter a first name."
    And I confirm the popup
    When I press "Cancel"
    Then I should see "User Management"

  @settings_core @javascript
  Scenario: Site Details can be cancelled without saving
    When I am on "/index.php?m=settings&a=administration&s=siteName"
    And I fill in "siteName" with "Unsaved Settings name"
    And I press "Back"
    Then I should see "Site Management"
    When I follow "Change Site Details"
    Then the "siteName" field should not contain "Unsaved Settings name"

  @settings_customize @javascript
  Scenario: Calendar settings reset and save current values
    When I am on "/index.php?m=settings&a=customizeCalendar"
    And I remember the Settings field "dayStart"
    And I select "0" from "dayStart"
    And I press "Reset"
    Then the Settings field "dayStart" retains its value
    When I press "Save"
    And I am on "/index.php?m=settings&a=customizeCalendar"
    Then the Settings field "dayStart" retains its value

  @settings_eeo @javascript
  Scenario: EEO dependent controls remain linked without saving
    When I am on "/index.php?m=settings&a=eeo"
    And I check "enabled"
    And I uncheck "enabled"
    Then the checkbox "genderTracking" should be unchecked
    When I check "genderTracking"
    Then the checkbox "enabled" should be checked

  @settings_career @javascript
  Scenario: Questionnaire question editor preserves its session workflow
    When I am on "/index.php?m=settings&a=careerPortalQuestionnaire"
    And I follow "(add question)"
    And I fill in "questionText" with "Settings presentation question"
    And I press "Add Question"
    Then I should see "Settings presentation question"
    And I should see "(add answer)"

  @settings_system
  Scenario: Backup controls render without generating an archive
    When I am on "/index.php?m=settings&a=createBackup"
    Then I should see a "input[value='Create Full System Backup']" element
    And I should see a "input[value='Create Attachments Backup']" element

  @settings_career @javascript
  Scenario: Enabled Career Portal administration and template editing preserve content
    Given a disposable Settings Career Portal fixture exists
    When I am on "/index.php?m=settings&a=careerPortalSettings"
    Then I should see "Allow Browsing of All Public Job Orders"
    And I should see "Built in Templates"
    And I should see "Settings fixture"
    When I open the Settings fixture template editor
    Then the Settings form "careerPortalSettingsForm" submits to action "careerPortalTemplateEdit" using "post"
    When I follow "expand0"
    Then the "edittext0" field should contain "<h1>Settings fixture &amp; preview</h1>"
    When I follow "expand6"
    And I select "First Name *" from "jobapplyselect6"
    And I press "Insert job application field"
    Then the "edittext6" field should contain "<input-firstName>"
    When I press "Save and Continue Editing"
    Then the Settings fixture template content is unchanged

  @settings_career
  Scenario: Questionnaire preview displays the supplied question
    Given a disposable Settings Career Portal fixture exists
    When I open the Settings fixture questionnaire preview
    Then I should see "Settings preview question"
    And I should see "Describe your experience"
    And I should see a "textarea[maxlength='200']" element

  @settings_users
  Scenario: Initial password wizard retains its form without changing credentials
    When I am on "/index.php?m=settings&a=newInstallPassword"
    Then I should see "Create Administrator Password"
    And the Settings form "configurationForm" submits to action "newInstallPassword" using "post"
    And I should see a "input[name='password1']" element
    And I should see a "input[name='password2']" element
    And I should see a "input[name='csrfToken']" element
