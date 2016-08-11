#### P - permitted, F - forbidden ####

@security @actions
Feature: Security using ACL - actions
  In order to protect sensitive information from users who should not have access to them
  All accesses in the system need to be controlled by the Access Control List

@settings @actions
Scenario Outline: Settings module actions
  Given I am logged in with <accessLevel> access level
  
  When I do GET request "index.php?m=settings&a=tags"
  Then the response should <PTags> contain "Tags Settings"
  And the response should <FTags> contain "You don't have permission"
  
  #handling function does not do anything
  #When I do POST request "index.php?m=settings&a=tags"
  #Then the response should <PTags> contain "Bad Server Information"
  #And the response should <FTags> contain "You don't have permission"
  
  When I do POST request "index.php?m=settings&a=changePassword"
  Then the response should <PChangePasswd> contain "Invalid Information"
  And the response should <FChangePasswd> contain "You don't have permission"
  And the response should <FChangePasswdDemo> contain "You are not allowed to change your password."
  
  When I do GET request "index.php?m=settings&a=newInstallPassword"
  Then the response should <PNewInstallPswd> contain "Create Administrator Password"
  And the response should <FNewInstallPswd> contain "You don't have permission"
  
  When I do POST request "index.php?m=settings&a=newInstallPassword"
  Then the response should <PNewInstallPswd> contain "Unable to reset password"
  And the response should <FNewInstallPswd> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=forceEmail"
  Then the response should <PForceEmail> contain "E-Mail Address"
  And the response should <FForceEmail> contain "You don't have permission"
  
  When I do POST request "index.php?m=settings&a=forceEmail"
  Then the response should <PForceEmail> contain "Please enter an e-mail address"
  And the response should <FForceEmail> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=newSiteName"
  Then the response should <PNewSiteName> contain "Site Name"
  And the response should <FNewSiteName> contain "Settings Saved"
  
  When I do POST request "index.php?m=settings&a=newSiteName"
  Then the response should <PNewSiteName> contain "Please enter a site name."
  And the response should <FNewSiteName> contain "My Recent Calls"
  
  When I do GET request "index.php?m=settings&a=upgradeSiteName"
  Then the response should <PUpgSiteName> contain "Site Name"
  And the response should <FUpgSiteName> contain "Settings Saved"
  
  When I do POST request "index.php?m=settings&a=upgradeSiteName"
  Then the response should <PUpgSiteName> contain "Please enter a site name."
  And the response should <FUpgSiteName> contain "My Recent Calls"
  
  When I do GET request "index.php?m=settings&a=newInstallFinished"
  Then the response should <PNewInstallFnshd> contain "Settings Saved"
  And the response should <FNewInstallFnshd> contain "Settings Saved"
  
  When I do POST request "index.php?m=settings&a=newInstallFinished"
  Then the response should <PNewInstallFnshd> contain "My Recent Calls"
  And the response should <FNewInstallFnshd> contain "My Recent Calls"
  
  Examples:
  | accessLevel | PTags   | FTags   | PChangePasswd | FChangePasswd | FChangePasswdDemo | PNewInstallPswd  | FNewInstallPswd    | PForceEmail | FForceEmail | PNewSiteName | FNewSiteName | PUpgSiteName | FUpgSiteName | PNewInstallFnshd | FNewInstallFnshd |
  | DISABLED    | not     | not     | not           | not           | not               | not              | not                | not         | not         | not          | not          | not          | not          | not              | not              |
  | READONLY    | not     |         |               | not           | not               |                  | not                |             | not         | not          |              | not          |              |                  | not              |
  | EDIT        | not     |         |               | not           | not               |                  | not                |             | not         | not          |              | not          |              |                  | not              |
  | DELETE      | not     |         |               | not           | not               |                  | not                |             | not         | not          |              | not          |              |                  | not              |
  | DEMO        | not     |         | not           | not           |                   |                  | not                |             | not         | not          |              | not          |              |                  | not              |
  | ADMIN       |         | not     |               | not           | not               |                  | not                |             | not         |              | not          |              | not          |                  | not              |
  | MULTI_ADMIN |         | not     |               | not           | not               |                  | not                |             | not         |              | not          |              | not          |                  | not              |
  | ROOT        |         | not     |               | not           | not               |                  | not                |             | not         |              | not          |              | not          |                  | not              |
  
@settings @actions
Scenario Outline: Settings module actions
  Given I am logged in with <accessLevel> access level
  
  When I do GET request "index.php?m=settings&a=manageUsers"
  Then the response should <PManageUsers> contain "Settings: User Management"
  And the response should <FManageUsers> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=professional"
  Then the response should <PProfessional> contain "Settings: Professional Membership"
  And the response should <FProfessional> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=previewPage"
  Then the response should <PPreviewPage> contain "Page Preview"
  And the response should <FPreviewPage> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=previewPageTop"
  Then the response should <PPreviewPageTop> contain "CATS - Page Preview"
  And the response should <FPreviewPageTop> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=showUser"
  Then the response should <PShowUser> contain "Bad Server Information"
  And the response should <FShowUser> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=addUser"
  Then the response should <PAddUserGET> contain "Settings: Add Site User"
  And the response should <FAddUserGET> contain "You don't have permission"
  
  When I do POST request "index.php?m=settings&a=addUser"
  Then the response should <PAddUserPOST> contain "Required Fields are Missing"
  And the response should <FAddUserPOST> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=editUser"
  Then the response should <PEditUserGET> contain "Bad Server Information"
  And the response should <FEditUserGET> contain "You don't have permission"
  
  When I do POST request "index.php?m=settings&a=editUser"
  Then the response should <PEditUserPOST> contain "Bad Server Information"
  And the response should <FEditUserPOST> contain "You don't have permission"
  
  Examples:
  | accessLevel | PManageUsers | FManageUsers | PProfessional | FProfessional | PPreviewPage | FPreviewPage | PPreviewPageTop | FPreviewPageTop | PShowUser | FShowUser | PAddUserGET | FAddUserGET | PAddUserPOST | FAddUserPOST | PEditUserGET | FEditUserGET | PEditUserPOST | FEditUserPOST |
  | DISABLED    | not          | not          | not           | not           | not          | not          | not             | not             | not       | not       | not         | not         | not          | not          | not       | not       | not           | not           | 
  | READONLY    | not          |              | not           |               |              | not          |                 | not             | not       |           | not         |             | not          |              | not       |           | not           |               |
  | EDIT        | not          |              | not           |               |              | not          |                 | not             | not       |           | not         |             | not          |              | not       |           | not           |               |
  | DELETE      | not          |              | not           |               |              | not          |                 | not             | not       |           | not         |             | not          |              | not       |           | not           |               |
  | DEMO        |              | not          |               | not           |              | not          |                 | not             |           | not       |             | not         | not          |              |           | not       | not           |               |
  | ADMIN       |              | not          |               | not           |              | not          |                 | not             |           | not       |             | not         |              | not          |           | not       |               | not           |
  | MULTI_ADMIN |              | not          |               | not           |              | not          |                 | not             |           | not       |             | not         |              | not          |           | not       |               | not           |
  | ROOT        |              | not          |               | not           |              | not          |                 | not             |           | not       |             | not         |              | not          |           | not       |               | not           |
  
@settings @actions
Scenario Outline: Settings module actions
  Given I am logged in with <accessLevel> access level

  When I do GET request "index.php?m=settings&a=createBackup"
  Then the response should <PCreateBackup> contain "Settings: Site Backup"
  And the response should <FCreateBackup> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=deleteBackup"
  Then the response should <PDeleteBackup> contain "Settings: Site Backup"
  And the response should <FDeleteBackup> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=customizeExtraFields"
  Then the response should <PExFieldsGET> contain "Settings: Customization"
  And the response should <FExFieldsGET> contain "You don't have permission"
  
  When I do POST request "index.php?m=settings&a=customizeExtraFields"
  Then the response should <PExFieldsPOST> contain "Settings: Customization"
  And the response should <FExFieldsPOST> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=customizeCalendar"
  Then the response should <PCalendGET> contain "Settings: Customization"
  And the response should <FCalendGET> contain "You don't have permission"
  
  When I do POST request "index.php?m=settings&a=customizeCalendar"
  Then the response should <PCalendPOST> contain "Settings: Administration"
  And the response should <FCalendPOST> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=reports"
  Then the response should <PReports> contain "Settings: Reports"
  And the response should <FReports> contain "You don't have permission"
  
  #handling function does not exist
  #When I do POST request "index.php?m=settings&a=reports"
  #Then the response should <PReports> contain "Bad Server Information"
  #And the response should <FReports> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=emailSettings"
  Then the response should <PEmailSetGET> contain "Settings: Administration"
  And the response should <FEmailSetGET> contain "You don't have permission"
  
  When I do POST request "index.php?m=settings&a=emailSettings"
  Then the response should <PEmailSetPOST> contain "Settings: Administration"
  And the response should <FEmailSetPOST> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=careerPortalQuestionnairePreview"
  Then the response should <PCPQuestion> contain "Bad Server Information"
  And the response should <FCPQuestion> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=careerPortalQuestionnaire"
  Then the response should <PCPQuestion> contain "Settings: Administration"
  And the response should <FCPQuestion> contain "You don't have permission"
  
  When I do POST request "index.php?m=settings&a=careerPortalQuestionnaire"
  Then the response should <PCPQuestion> contain "Settings: Administration"
  And the response should <FCPQuestion> contain "You don't have permission"
  
  
  Examples:
  | accessLevel | PCreateBackup | FCreateBackup | PDeleteBackup | FDeleteBackup | PExFieldsGET | FExFieldsGET | PExFieldsPOST | FExFieldsPOST | PCalendGET | FCalendGET | PCalendPOST | FCalendPOST | PReports | FReports | PEmailSetGET | FEmailSetGET | PEmailSetPOST | FEmailSetPOST | PCPQuestion | FCPQuestion |
  | DISABLED    | not           | not           | not           | not           | not          | not          | not           | not           | not        | not        | not         | not         | not      | not      | not          | not          | not           |   not         | not         | not         |
  | READONLY    | not           |               | not           |               | not          |              | not           |               | not        |            | not         |             | not      |          | not          |              | not           |               | not         |             |
  | EDIT        | not           |               | not           |               | not          |              | not           |               | not        |            | not         |             | not      |          | not          |              | not           |               | not         |             |
  | DELETE      | not           |               | not           |               | not          |              | not           |               | not        |            | not         |             | not      |          | not          |              | not           |               | not         |             |
  | DEMO        | not           |               | not           |               |              | not          | not           |               |            | not        | not         |             |          | not      |              | not          | not           |               |             | not         |
  | ADMIN       |               | not           |               | not           |              | not          |               | not           |            | not        |             | not         |          | not      |              | not          |               |  not           |             | not         |
  | MULTI_ADMIN |               | not           |               | not           |              | not          |               | not           |            | not        |             | not         |          | not      |              | not          |               |  not           |             | not         |
  | ROOT        |               | not           |               | not           |              | not          |               | not           |            | not        |             | not         |          | not      |              | not          |               |  not           |             | not         |
  
@settings @actions
Scenario Outline: Settings module actions
  Given I am logged in with <accessLevel> access level  
  
  When I do GET request "index.php?m=settings&a=careerPortalQuestionnaireUpdate"
  Then the response should <PCPQuestionUpdt> contain "Settings: Administration"
  And the response should <FCPQuestionUpdt> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=careerPortalTemplateEdit"
  Then the response should <PCPTempEditGET> contain "Required Fields are Missing"
  And the response should <FCPTempEditGET> contain "You don't have permission"
  
  When I do POST request "index.php?m=settings&a=careerPortalTemplateEdit"
  Then the response should <PCPTempEditPOST> contain "Required Fields are Missing"
  And the response should <FCPTempEditPOST> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=careerPortalSettings"
  Then the response should <PCPSettingsGET> contain "Settings: Administration"
  And the response should <FCPSettingsGET> contain "You don't have permission"
  
  When I do POST request "index.php?m=settings&a=careerPortalSettings"
  Then the response should <PCPSettingsPOST> contain "Settings: Administration"
  And the response should <FCPSettingsPOST> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=eeo"
  Then the response should <PEEOGET> contain "Settings: Administration"
  And the response should <FEEOGET> contain "You don't have permission"
  
  When I do POST request "index.php?m=settings&a=eeo"
  Then the response should <PEEOPOST> contain "Settings: Administration"
  And the response should <FEEOPOST> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=onCareerPortalTweak"
  Then the response should <PCPTweak> contain "Bad Server Information"
  And the response should <FCPTweak> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=deleteUser"
  Then the response should <PDeleteUser> contain "Bad Server Information"
  And the response should <FDeleteUser> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=emailTemplates"
  Then the response should <PEmailTempGET> contain "Administration: E-Mail Templates"
  And the response should <FEmailTempGET> contain "You don't have permission"
  
  When I do POST request "index.php?m=settings&a=emailTemplates"
  Then the response should <PEmailTempPOST> contain "Bad Server Information"
  And the response should <FEmailTempPOST> contain "You don't have permission"
  
  When I do POST request "index.php?m=settings&a=aspLocalization"
  Then the response should <PAspLocalization> contain "Localization Settings Saved"
  And the response should <FAspLocalization> contain "You don't have permission"

  
  Examples:
  | accessLevel | PCPQuestionUpdt | FCPQuestionUpdt | PCPTempEditGET | FCPTempEditGET | PCPTempEditPOST| FCPTempEditPOST| PCPSettingsGET | FCPSettingsGET | PCPSettingsPOST | FCPSettingsPOST | PEEOGET | FEEOGET | PEEOPOST | FEEOPOST | PCPTweak | FCPTweak | PDeleteUser | FDeleteUser | PEmailTempGET | FEmailTempGET | PEmailTempPOST | FEmailTempPOST | PAspLocalization | FAspLocalization |
  | DISABLED    | not             | not             | not            | not            | not            | not            | not            | not            |  not            | not             | not     | not     | not      | not      | not      | not      | not         | not         | not             | not             | not          | not           | not              | not              |
  | READONLY    | not             |                 | not            |                | not            |                | not            |                |  not            |                 | not     |         | not      |          | not      |          | not         |             | not             |                 | not          |               | not              |                  |
  | EDIT        | not             |                 | not            |                | not            |                | not            |                |  not            |                 | not     |         | not      |          | not      |          | not         |             | not             |                 | not          |               | not              |                  |
  | DELETE      | not             |                 | not            |                | not            |                | not            |                |  not            |                 | not     |         | not      |          | not      |          | not         |             | not             |                 | not          |               | not              |                  |
  | DEMO        |                 | not             |                | not            | not            |                |                | not            |  not            |                 |         | not     | not      |          | not      |          | not         |             |                 | not             | not          |               | not              |                  |
  | ADMIN       |                 | not             |                | not            |                | not            |                | not            |                 | not             |         | not     |          | not      |          | not      |             | not         |                 | not             |              | not           |                  | not              |
  | MULTI_ADMIN |                 | not             |                | not            |                | not            |                | not            |                 | not             |         | not     |          | not      |          | not      |             | not         |                 | not             |              | not           |                  | not              |
  | ROOT        |                 | not             |                | not            |                | not            |                | not            |                 | not             |         | not     |          | not      |          | not      |             | not         |                 | not             |              | not           |                  | not              |
  
@settings @actions
Scenario Outline: Settings module actions
  Given I am logged in with <accessLevel> access level  
  
  When I do GET request "index.php?m=settings&a=loginActivity"
  Then the response should <PLoginActivity> contain "Settings: Login Activity"
  And the response should <FLoginActivity> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=viewItemHistory"
  Then the response should <PViewItemHist> contain "Bad Server Information"
  And the response should <FViewItemHist> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=getFirefoxModal"
  Then the response should <PGetFirefox> contain "The CATS Toolbar is designed for Mozilla Firefox"
  And the response should <FGetFirefox> contain "You don't have permission"
    
  When I do GET request "index.php?m=settings&a=downloads"
  Then the response should <PDownloads> contain "Settings: Downloads"
  And the response should <FDownloads> contain "You don't have permission"
  
  #When I do GET request "index.php?m=settings&a=ajax_tags_add"
  #Then the response should <PAddTags> contain "Bad Server Information"
  #And the response should <FAddTags> contain "You don't have permission"
  
  #When I do GET request "index.php?m=settings&a=ajax_tags_del"
  #Then the response should <PDelTags> contain "Bad Server Information"
  #And the response should <FDelTags> contain "You don't have permission"
    
  #When I do GET request "index.php?m=settings&a=ajax_tags_upd"
  #Then the response should <PUpdTags> contain "Bad Server Information"
  #And the response should <FUpdTags> contain "You don't have permission"
  
  #When I do GET request "index.php?m=settings&a=ajax_wizardAddUser"
  #Then the response should <PAddUser> contain "Bad Server Information"
  #And the response should <FAddUser> contain "You don't have permission"
  
  #When I do GET request "index.php?m=settings&a=ajax_wizardDeleteUser"
  #Then the response should <PDeleteUser> contain "Bad Server Information"
  #And the response should <FDeleteUser> contain "You don't have permission"
  
  Examples:
  | accessLevel | PLoginActivity | FLoginActivity | PViewItemHist | FViewItemHist | PGetFirefox | FGetFirefox | PDownloads | FDownloads | PAddTags | FAddTags | PDelTags | FDelTags | PUpdTags | FUpdTags | PAddUser | FAddUser | PDeleteUser | FDeleteUser |
  | DISABLED    | not            | not            | not           | not           | not         | not         | not        | not        | not      | not      | not      | not      | not      | not      |          | not      |             |             |
  | READONLY    | not            |                | not           |               |             | not         |            | not        |          | not      |          | not      |          | not      |          | not      |             |             |
  | EDIT        | not            |                | not           |               |             | not         |            | not        |          | not      |          | not      |          | not      |          | not      |             |             |
  | DELETE      | not            |                | not           |               |             | not         |            | not        |          | not      |          | not      |          | not      |          | not      |             |             |
  | DEMO        |                | not            |               | not           |             | not         |            | not        |          | not      |          | not      |          | not      |          | not      |             |             |
  | ADMIN       |                | not            |               | not           |             | not         |            | not        |          | not      |          | not      |          | not      |          | not      |             |             |
  | MULTI_ADMIN |                | not            |               | not           |             | not         |            | not        |          | not      |          | not      |          | not      |          | not      |             |             |
  | ROOT        |                | not            |               | not           |             | not         |            | not        |          | not      |          | not      |          | not      |          | not      |             |             |
    
##@settings @actions
##Scenario Outline: Settings module actions
  ##Given I am logged in with <accessLevel> access level    
    
  #When I do GET request "index.php?m=settings&a=ajax_wizardCheckKey"
  #Then the response should <PCheckKey> contain "Bad Server Information"
  #And the response should <FCheckKey> contain "You don't have permission"
  
  #When I do GET request "index.php?m=settings&a=ajax_wizardLocalization"
  #Then the response should <PLocalization> contain "Bad Server Information"
  #And the response should <FLocalization> contain "You don't have permission"
  
  #When I do GET request "index.php?m=settings&a=ajax_wizardFirstTimeSetup"
  #Then the response should <PFirstTimeSetup> contain "Bad Server Information"
  #And the response should <FFirstTimeSetup> contain "You don't have permission"
    
  #When I do GET request "index.php?m=settings&a=ajax_wizardLicense"
  #Then the response should <PLicense> contain "Bad Server Information"
  #And the response should <FLicense> contain "You don't have permission"
  
  #When I do GET request "index.php?m=settings&a=ajax_wizardPassword"
  #Then the response should <PPasswd> contain "Bad Server Information"
  #And the response should <FPasswd> contain "You don't have permission"
  
  #When I do GET request "index.php?m=settings&a=ajax_wizardSiteName"
  #Then the response should <PSiteName> contain "Bad Server Information"
  #And the response should <FSiteName> contain "You don't have permission"
    
  #When I do GET request "index.php?m=settings&a=ajax_wizardEmail"
  #Then the response should <PEmail> contain "Bad Server Information"
  #And the response should <FEmail> contain "You don't have permission"
  
  #When I do GET request "index.php?m=settings&a=ajax_wizardImport"
  #Then the response should <PImport> contain "Bad Server Information"
  #And the response should <FImport> contain "You don't have permission"
  
  #Examples:
  #| accessLevel | PCheckKey | FCheckKey | PLocalization | FLocalization | PFirstTimeSetup | FFirstTimeSetup | PLicense | FLicense | PPasswd | FPasswd | PSiteName | FSiteName | PEmail | FEmail | PImport | FImports |
  #| DISABLED    | not       | not       | not           | not           | not             | not             | not      | not      | not     | not     | not       | not       | not    | not    |         | not      |
  #| READONLY    |           | not       |               | not           |                 | not             |          | not      |         | not     |           | not       |        | not    |         | not      |
  #| EDIT        |           | not       |               | not           |                 | not             |          | not      |         | not     |           | not       |        | not    |         | not      |
  #| DELETE      |           | not       |               | not           |                 | not             |          | not      |         | not     |           | not       |        | not    |         | not      |
  #| DEMO        |           | not       |               | not           |                 | not             |          | not      |         | not     |           | not       |        | not    |         | not      |
  #| ADMIN       |           | not       |               | not           |                 | not             |          | not      |         | not     |           | not       |        | not    |         | not      |
  #| MULTI_ADMIN |           | not       |               | not           |                 | not             |          | not      |         | not     |           | not       |        | not    |         | not      |
  #| ROOT        |           | not       |               | not           |                 | not             |          | not      |         | not     |           | not       |        | not    |         | not      |
  
@settings @actions
Scenario Outline: Settings module actions
  Given I am logged in with <accessLevel> access level  
  
  #When I do GET request "index.php?m=settings&a=ajax_wizardWebsite"
  #Then the response should <PWebsite> contain "Bad Server Information"
  #And the response should <FWebsite> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=administration"
  Then the response should <PAdministrationGET> contain "Settings: Administration"
  And the response should <FAdministrationGET> contain "You don't have permission"
  
  When I do POST request "index.php?m=settings&a=administration"
  Then the response should <PAdministrationPOST> contain "Settings: Administration"
  And the response should <FAdministrationPOST> contain "You don't have permission"
  
  When I do GET request "index.php?m=settings&a=myProfile"
  Then the response should <PMyProfile> contain "Settings: My Profile"
  And the response should <FMyProfile> contain "You don't have permission"
  
  
 Examples:
  | accessLevel | PWebsite | FWebsite | PAdministrationGET | FAdministrationGET | PAdministrationPOST | FAdministrationPOST | PMyProfile | FMyProfile |
  | DISABLED    | not      | not      | not                | not                | not                 | not                 | not        | not        |
  | READONLY    |          | not      | not                |                    | not                 |                     |            | not        |
  | EDIT        |          | not      | not                |                    | not                 |                     |            | not        |
  | DELETE      |          | not      | not                |                    | not                 |                     |            | not        |
  | DEMO        |          | not      |                    | not                | not                 |                     |            | not        |
  | ADMIN       |          | not      |                    | not                |                     | not                 |            | not        |
  | MULTI_ADMIN |          | not      |                    | not                |                     | not                 |            | not        |
  | ROOT        |          | not      |                    | not                |                     | not                 |            | not        |
  
 
 
 