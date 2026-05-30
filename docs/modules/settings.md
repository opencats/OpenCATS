# Module: settings

> Source-derived design doc. Every claim cites a file and line that was opened. ACL constant values are from `constants.php` (`ACCESS_LEVEL_READ=100`, `ACCESS_LEVEL_EDIT=200`, `ACCESS_LEVEL_DELETE=300`, `ACCESS_LEVEL_DEMO=350`, `ACCESS_LEVEL_SA=400`, `ACCESS_LEVEL_MULTI_SA=450`, `ACCESS_LEVEL_ROOT=500`) (constants.php:74-82). `getUserAccessLevel($securedObjectName)` simply delegates to `$_SESSION['CATS']->getAccessLevel($securedObjectName)` (lib/UserInterface.php:429-432).

## Overview

The controller is declared as `class SettingsUI extends UserInterface` (modules/settings/SettingsUI.php:55). The constructor `public function __construct()` (SettingsUI.php:61) calls `parent::__construct()`, stores `$this->_realAccessLevel = $_SESSION['CATS']->getRealAccessLevel()` (SettingsUI.php:65), forces authentication (`$this->_authenticationRequired = true`, SettingsUI.php:66), sets `_moduleDirectory`/`_moduleName` to `'settings'` (SettingsUI.php:67-68), and (when `ACL_SETUP::$USER_ROLES` is non-empty) populates `$this->_settingsUserCategories` (SettingsUI.php:72-75). It defines two sub-tabs: **Administration** (`m=settings&a=administration`) and **My Profile** (`m=settings`) (SettingsUI.php:77-82).

`MAX_RECENT_LOGINS = 15` caps login-history rows on the user-details page (SettingsUI.php:58).

The constructor calls `$this->defineHooks()` (SettingsUI.php:84). `defineHooks()` (SettingsUI.php:87-128) installs the **career-portal lockdown**: in career-portal mode it hides all non-settings tabs (`TEMPLATE_UTILITY_EVALUATE_TAB_VISIBLE`, SettingsUI.php:91-99), redirects Home to settings (`HOME`, SettingsUI.php:102-108), redirects My Profile to administration (`SETTINGS_DISPLAY_PROFILE_SETTINGS`, SettingsUI.php:111-117), and `fatal()`s every other module's handle-request hook (`CLIENTS_HANDLE_REQUEST` … `REPORTS_HANDLE_REQUEST`, SettingsUI.php:120-126).

The module covers three areas, all dispatched from the single `handleRequest()` switch (SettingsUI.php:224-944):
- **My Profile** (self-service): `myProfile`, `changePassword` — minimum `ACCESS_LEVEL_READ`.
- **Administration / system settings** (mostly `ACCESS_LEVEL_SA` on POST): site name, localization, version check, email/calendar settings, extra fields, EEO, backups, career-portal config, questionnaires, email templates, install wizard.
- **User management**: `manageUsers`, `showUser`, `addUser`, `editUser`, `deleteUser` plus AJAX wizard variants.

`handleRequest()` first runs `if (!eval(Hooks::get('SETTINGS_HANDLE_REQUEST'))) return;` (SettingsUI.php:228) before the switch.

## Action catalog

One row per `case` in the `handleRequest()` switch. "Guard" is the literal `getUserAccessLevel(...)` comparison; where GET and POST differ, both are shown. `< ACCESS_LEVEL_X` means the action is blocked below level X (so X is the required minimum). Most write/POST paths require **`ACCESS_LEVEL_SA` (400)**.

| Action (`a=`) | ACL guard (verbatim object + comparison) | Required min level | Handler method | Key lib calls | Template |
|---|---|---|---|---|---|
| `tags` | `settings.tags < ACCESS_LEVEL_SA` unless `hasUserCategory('careerportal')` (SettingsUI.php:234) | SA (400) / careerportal | `changeTags()` / `onChangeTags()` (SettingsUI.php:240,244) | `Tags->getAll()` (SettingsUI.php:214) | `tags.tpl` (SettingsUI.php:221) |
| `changePassword` | `settings.changePassword == ACCESS_LEVEL_DEMO` blocks (SettingsUI.php:250) | not DEMO | `onChangePassword()` (POST only, SettingsUI.php:256) | `Users->changePassword()` (SettingsUI.php:2896), `Users->isUserLDAP()` (SettingsUI.php:2852) | `MyProfile.tpl` (SettingsUI.php:2969) |
| `newInstallPassword` | `settings.newInstallPassword < ACCESS_LEVEL_SA` (SettingsUI.php:261) | **SA (400)** | `newInstallPassword()` / `onNewInstallPassword()` (SettingsUI.php:267,271) | `Users->changePassword()` (SettingsUI.php:2342) | `NewInstallWizard.tpl` (SettingsUI.php:2190) |
| `forceEmail` | `settings.forceEmail < ACCESS_LEVEL_SA` (SettingsUI.php:276) | **SA (400)** | `forceEmail()` / `onForceEmail()` (SettingsUI.php:282,286) | `Users->updateSelfEmail()` (SettingsUI.php:2272) | `NewInstallWizard.tpl` (SettingsUI.php:2256) |
| `newSiteName` | `settings.newSiteName < ACCESS_LEVEL_SA` (SettingsUI.php:291) | **SA (400)** | `newSiteName()` / `onNewSiteName()` (SettingsUI.php:297,301) | `Site->setName()`, `Companies->add()` (SettingsUI.php:2372-2381) | `NewInstallWizard.tpl` (SettingsUI.php:2201) |
| `upgradeSiteName` | `settings.upgradeSiteName < ACCESS_LEVEL_SA` → redirect to `newInstallFinished` (SettingsUI.php:306-309) | **SA (400)** | `upgradeSiteName()` / `onNewSiteName()` (SettingsUI.php:312,316) | `Site->setName()` (SettingsUI.php:2372) | `NewInstallWizard.tpl` (SettingsUI.php:2212) |
| `newInstallFinished` | `settings.newSiteName < ACCESS_LEVEL_SA` (note: object is `newSiteName`) (SettingsUI.php:321) | **SA (400)** | `newInstallFinished()` / `onNewInstallFinished()` (SettingsUI.php:327,331) | `MailerSettings->getAll()`, `NewVersionCheck::checkForUpdate()` (SettingsUI.php:2285-2290) | `NewInstallWizard.tpl` (SettingsUI.php:2307) |
| `manageUsers` | `settings.manageUsers < ACCESS_LEVEL_DEMO` (SettingsUI.php:336) | DEMO (350) | `manageUsers()` (SettingsUI.php:340) | `Users->getAll()`, `Users->getLicenseData()` (SettingsUI.php:2741-2742) | `Users.tpl` (SettingsUI.php:2771) |
| `professional` | `settings.professional < ACCESS_LEVEL_DEMO` (SettingsUI.php:344) | DEMO (350) | `manageProfessional()` (SettingsUI.php:348) | `License->setKey()/isProfessional()`, `CATSUtility::changeConfigSetting()` (SettingsUI.php:2793-2817) | `Professional.tpl` (SettingsUI.php:2841) |
| `previewPage` | `settings.previewPage < ACCESS_LEVEL_READ` (SettingsUI.php:352) | READ (100) | `previewPage()` (SettingsUI.php:356) | — (reads `$_GET['url']/['message']`, SettingsUI.php:1682-1685) | `PreviewPage.tpl` (SettingsUI.php:1686) |
| `previewPageTop` | `settings.previewPageTop < ACCESS_LEVEL_READ` (SettingsUI.php:360) | READ (100) | `previewPageTop()` (SettingsUI.php:364) | — | `PreviewPageTop.tpl` (SettingsUI.php:1697) |
| `showUser` | `settings.showUser < ACCESS_LEVEL_DEMO` **unless** viewing own `userID` (SettingsUI.php:368-369) | DEMO (350) or self | `showUser()` (SettingsUI.php:373) | `Users->get()`, `getAccessLevels()`, `getLastLoginAttempts()` (SettingsUI.php:1041-1058) | `ShowUser.tpl` (SettingsUI.php:1121) |
| `addUser` (GET) | `settings.addUser.GET < ACCESS_LEVEL_DEMO` (SettingsUI.php:387) | DEMO (350) | `addUser()` (SettingsUI.php:391) | `Users->getAll()/getAccessLevels()/getLicenseData()` (SettingsUI.php:1130-1133) | `AddUser.tpl` (SettingsUI.php:1170) |
| `addUser` (POST) | `settings.addUser.POST < ACCESS_LEVEL_SA` (SettingsUI.php:379) | **SA (400)** | `onAddUser()` (SettingsUI.php:383) | `Users->add()`, `usernameExists()`, `updateCategories()` (SettingsUI.php:1232-1257) | redirect → `showUser` (SettingsUI.php:1271) |
| `editUser` (GET) | `settings.editUser.GET < ACCESS_LEVEL_DEMO` (SettingsUI.php:408) | DEMO (350) | `editUser()` (SettingsUI.php:412) | `Users->get()/getLicenseData()/getAccessLevels()` (SettingsUI.php:1290-1292) | `EditUser.tpl` (SettingsUI.php:1363) |
| `editUser` (POST) | `settings.editUser.POST < ACCESS_LEVEL_SA` (SettingsUI.php:400) | **SA (400)** | `onEditUser()` (SettingsUI.php:404) | `Users->update()/resetPassword()/updateCategories()` (SettingsUI.php:1440-1477) | redirect → `showUser` (SettingsUI.php:1484) |
| `createBackup` | `settings.createBackup < ACCESS_LEVEL_SA` (SettingsUI.php:418) | **SA (400)** | `createBackup()` (SettingsUI.php:422) | `Attachments->getAll()` (SettingsUI.php:2219) | `Backup.tpl` (SettingsUI.php:2233) |
| `deleteBackup` | `settings.deleteBackup < ACCESS_LEVEL_SA` (SettingsUI.php:426) | **SA (400)** | `deleteBackup()` (POST only, SettingsUI.php:432) | `Attachments->deleteAll(... content_type='catsbackup')` (SettingsUI.php:2239) | redirect → `createBackup` (SettingsUI.php:2245) |
| `customizeExtraFields` (GET) | `settings.customizeExtraFields.GET < ACCESS_LEVEL_DEMO` (SettingsUI.php:452) | DEMO (350) | `customizeExtraFields()` (SettingsUI.php:456) | `*.extraFields->getSettings()`, `getValuesTypes()` (SettingsUI.php:1523-1534) | `CustomizeExtraFields.tpl` (SettingsUI.php:1542) |
| `customizeExtraFields` (POST) | `settings.customizeExtraFields.POST < ACCESS_LEVEL_SA` (SettingsUI.php:444) | **SA (400)** | `onCustomizeExtraFields()` (SettingsUI.php:448) | `ExtraFields->define/remove/addOptionToColumn/...` (SettingsUI.php:1567-1606) | redirect → `customizeExtraFields` (SettingsUI.php:1611) |
| `customizeCalendar` (GET) | `settings.customizeCalendar.GET < ACCESS_LEVEL_DEMO` (SettingsUI.php:472) | DEMO (350) | `customizeCalendar()` (SettingsUI.php:476) | `CalendarSettings->getAll()` (SettingsUI.php:2127) | `CustomizeCalendar.tpl` (SettingsUI.php:2132) |
| `customizeCalendar` (POST) | `settings.customizeCalendar.POST < ACCESS_LEVEL_SA` (SettingsUI.php:464) | **SA (400)** | `onCustomizeCalendar()` (SettingsUI.php:468) | `CalendarSettings->set()` (SettingsUI.php:2150) | redirect → `administration` (SettingsUI.php:2167) |
| `reports` | `settings.reports < ACCESS_LEVEL_DEMO` (SettingsUI.php:481) | DEMO (350) | `reports()` (GET; POST is empty) (SettingsUI.php:491) | — | `CustomizeReports.tpl` (SettingsUI.php:2177) |
| `emailSettings` (GET) | `settings.emailSettings.GET < ACCESS_LEVEL_DEMO` (SettingsUI.php:507) | DEMO (350) | `emailSettings()` (SettingsUI.php:511) | `MailerSettings->getAll()`, `EmailTemplates->getAll()` (SettingsUI.php:2064-2069) | `EmailSettings.tpl` (SettingsUI.php:2077) |
| `emailSettings` (POST) | `settings.emailSettings.POST < ACCESS_LEVEL_SA` (SettingsUI.php:499) | **SA (400)** | `onEmailSettings()` (SettingsUI.php:503) | `MailerSettings->set()`, `EmailTemplates->updateIsActive()` (SettingsUI.php:2092-2114) | redirect → `administration` (SettingsUI.php:2118) |
| `careerPortalQuestionnairePreview` | `settings.careerPortalQuestionnairePreview < ACCESS_LEVEL_DEMO` (SettingsUI.php:516) | DEMO (350) | `careerPortalQuestionnairePreview()` (SettingsUI.php:520) | `Questionnaire->get()/getQuestions()` (SettingsUI.php:3929-3936) | `CareerPortalQuestionnaireShow.tpl` (SettingsUI.php:3944) |
| `careerPortalQuestionnaire` | `settings.careerPortalQuestionnaire < ACCESS_LEVEL_DEMO` (SettingsUI.php:525) | DEMO (350) | `careerPortalQuestionnaire()` / `onCareerPortalQuestionnaire()` (SettingsUI.php:531,535) | `Questionnaire->get/getQuestions/add/update/deleteQuestions/addQuestions` (SettingsUI.php:3409-3884) | `CareerPortalQuestionnaire.tpl` (SettingsUI.php:3473) |
| `careerPortalQuestionnaireUpdate` | `settings.careerPortalQuestionnaireUpdate < ACCESS_LEVEL_DEMO` (SettingsUI.php:540) | DEMO (350) | `careerPortalQuestionnaireUpdate()` (SettingsUI.php:544) | `Questionnaire->getAll(true)`, `delete()` (SettingsUI.php:3906-3913) | redirect → `careerPortalSettings` (SettingsUI.php:3917) |
| `careerPortalTemplateEdit` (GET) | `settings.careerPortalTemplateEdit < ACCESS_LEVEL_DEMO` unless careerportal (SettingsUI.php:559) | DEMO (350) / careerportal | `careerPortalTemplateEdit()` (SettingsUI.php:563) | `CareerPortalSettings->getAllFromCustomTemplate()`, `extraFields->getValuesForAdd()` (SettingsUI.php:1713-1754) | `CareerPortalTemplateEdit.tpl` (SettingsUI.php:1769) |
| `careerPortalTemplateEdit` (POST) | `settings.careerPortalTemplateEdit.POST < ACCESS_LEVEL_SA` unless careerportal (SettingsUI.php:551) | **SA (400)** / careerportal | `onCareerPortalTemplateEdit()` (SettingsUI.php:555) | `CareerPortalSettings->setForTemplate()` (SettingsUI.php:1792-1804) | redirect → templateEdit/careerPortalSettings (SettingsUI.php:1814-1821) |
| `careerPortalSettings` (GET) | `settings.careerPortalSettings.GET < ACCESS_LEVEL_DEMO` unless careerportal (SettingsUI.php:582); outer guard `settings.careerPortalSettings < ACCESS_LEVEL_DEMO` (SettingsUI.php:568) | DEMO (350) / careerportal | `careerPortalSettings()` (SettingsUI.php:586) | `CareerPortalSettings->getAll()/getDefaultTemplates()/getCustomTemplates()`, `Questionnaire->getAll(true)` (SettingsUI.php:1832-1841) | `CareerPortalSettings.tpl` (SettingsUI.php:1851) |
| `careerPortalSettings` (POST) | `settings.careerPortalSettings.POST < ACCESS_LEVEL_SA` unless careerportal (SettingsUI.php:574) | **SA (400)** / careerportal | `onCareerPortalSettings()` (SettingsUI.php:578) | `CareerPortalSettings->set()` (SettingsUI.php:1867-1930) | redirect → `administration` (SettingsUI.php:1935) |
| `eeo` (GET) | `settings.eeo.GET < ACCESS_LEVEL_DEMO` (SettingsUI.php:602) | DEMO (350) | `EEOEOCSettings()` (SettingsUI.php:606) | `EEOSettings->getAll()` (SettingsUI.php:2028) | `EEOEOCSettings.tpl` (SettingsUI.php:2034) |
| `eeo` (POST) | `settings.eeo.POST < ACCESS_LEVEL_SA` (SettingsUI.php:594) | **SA (400)** | `onEEOEOCSettings()` (SettingsUI.php:598) | `EEOSettings->set()` (SettingsUI.php:2047-2051) | redirect → `administration` (SettingsUI.php:2055) |
| `onCareerPortalTweak` | `settings.careerPortalTweak < ACCESS_LEVEL_SA` unless careerportal (SettingsUI.php:611) | **SA (400)** / careerportal | `onCareerPortalTweak()` (POST only, SettingsUI.php:618) | `CareerPortalSettings->getAllFrom*Template()/setForTemplate()/deleteCustomTemplate()/set('activeBoard'/'activeName')` (SettingsUI.php:1960-2015) | redirect → `careerPortalSettings` (SettingsUI.php:2019) |
| `deleteUser` | `settings.deleteUser < ACCESS_LEVEL_SA` (SettingsUI.php:628) | **SA (400)** | `onDeleteUser()` (POST only; also requires `iAmTheAutomatedTester`) (SettingsUI.php:634,1504) | `Users->delete()` (SettingsUI.php:1512) | redirect → `manageUsers` (SettingsUI.php:1514) |
| `emailTemplates` (GET) | `settings.emailTemplates.GET < ACCESS_LEVEL_DEMO` unless careerportal (SettingsUI.php:654) | DEMO (350) / careerportal | `emailTemplates()` (SettingsUI.php:658) | `EmailTemplates->getAll()` (SettingsUI.php:1618) | `EmailTemplates.tpl` (SettingsUI.php:1625) |
| `emailTemplates` (POST) | `settings.emailTemplates.POST < ACCESS_LEVEL_SA` unless careerportal (SettingsUI.php:646) | **SA (400)** / careerportal | `onEmailTemplates()` (SettingsUI.php:650) | `EmailTemplates->update()` (SettingsUI.php:1671) | redirect → `emailTemplates` (SettingsUI.php:1673) |
| `aspLocalization` | `settings.aspLocalization < ACCESS_LEVEL_SA` (SettingsUI.php:663) | **SA (400)** | `onAspLocalization()` (POST only) (SettingsUI.php:669) | `Site->setLocalization()` (SettingsUI.php:2699) | `NewInstallWizard.tpl` (SettingsUI.php:2709) |
| `loginActivity` | `settings.loginActivity < ACCESS_LEVEL_DEMO` (SettingsUI.php:674) | DEMO (350) | `loginActivity()` (SettingsUI.php:681) | `LoginActivityPager->getPage()`, `BrowserDetection` (SettingsUI.php:3007-3039) | `LoginActivity.tpl` (SettingsUI.php:3048) |
| `viewItemHistory` | `settings.viewItemHistory < ACCESS_LEVEL_DEMO` (SettingsUI.php:685) | DEMO (350) | `viewItemHistory()` (SettingsUI.php:689) | `Candidates/JobOrders/Companies/Contacts->get()`, `History->getAll()` (SettingsUI.php:3074-3100) | `ItemHistory.tpl` (SettingsUI.php:3106) |
| `ajax_tags_add` | session check only; POST required (SettingsUI.php:692-704) | (none — auth only) | `onAddNewTag()` (SettingsUI.php:703) | `Tags->add()` (SettingsUI.php:138) | inline echo |
| `ajax_tags_del` | session check only; POST required (SettingsUI.php:706-718) | (none — auth only) | `onRemoveTag()` (SettingsUI.php:717) | `Tags->delete()` (SettingsUI.php:178) | inline echo |
| `ajax_tags_upd` | session check only; POST required (SettingsUI.php:720-732) | (none — auth only) | `onChangeTag()` (SettingsUI.php:731) | `Tags->update()` (SettingsUI.php:191) | inline echo |
| `ajax_wizardAddUser` | `settings.addUser < ACCESS_LEVEL_SA` (SettingsUI.php:740) | **SA (400)** | `wizard_addUser()` (SettingsUI.php:745) | `Users->usernameExists()/getLicenseData()/add()` (SettingsUI.php:3142-3155) | inline echo |
| `ajax_wizardDeleteUser` | `settings.deleteUser < ACCESS_LEVEL_SA`; POST required (SettingsUI.php:754-763) | **SA (400)** | `wizard_deleteUser()` (SettingsUI.php:764) | `Users->delete()` (SettingsUI.php:3183) | inline echo |
| `ajax_wizardCheckKey` | `settings.checkKey < ACCESS_LEVEL_SA` (SettingsUI.php:773) | **SA (400)** | `wizard_checkKey()` (SettingsUI.php:778) | `License->setKey()/isProfessional()`, `CATSUtility::changeConfigSetting()` (SettingsUI.php:3198-3230) | inline echo |
| `ajax_wizardLocalization` | `settings.localization < ACCESS_LEVEL_SA` (SettingsUI.php:787) | **SA (400)** | `wizard_localization()` (SettingsUI.php:792) | `Site->setLocalization()/setLocalizationConfigured()` (SettingsUI.php:3285-3286) | inline echo |
| `ajax_wizardFirstTimeSetup` | `settings.firstTimeSetup < ACCESS_LEVEL_SA` (SettingsUI.php:801) | **SA (400)** | `wizard_firstTimeSetup()` (SettingsUI.php:806) | `Site->setFirstTimeSetup()` (SettingsUI.php:3302) | inline echo |
| `ajax_wizardLicense` | `settings.license < ACCESS_LEVEL_SA` (SettingsUI.php:815) | **SA (400)** | `wizard_license()` (SettingsUI.php:820) | `Site->setAgreedToLicense()` (SettingsUI.php:3294) | inline echo |
| `ajax_wizardPassword` | `settings.password < ACCESS_LEVEL_SA` (SettingsUI.php:829) | **SA (400)** | `wizard_password()` (SettingsUI.php:834) | `Users->changePassword()` (SettingsUI.php:3319) | inline echo |
| `ajax_wizardSiteName` | `settings.siteName < ACCESS_LEVEL_SA` (SettingsUI.php:843) | **SA (400)** | `wizard_siteName()` (SettingsUI.php:848) | `Site->setName()`, `Companies->add()` (SettingsUI.php:3356-3360) | inline echo |
| `ajax_wizardEmail` | `settings.setEmail < ACCESS_LEVEL_READ` (SettingsUI.php:857) | READ (100) | `wizard_email()` (SettingsUI.php:862) | `Users->updateSelfEmail()` (SettingsUI.php:3340) | inline echo |
| `ajax_wizardImport` | `settings.import < ACCESS_LEVEL_SA` (SettingsUI.php:871) | **SA (400)** | `wizard_import()` (SettingsUI.php:876) | `ImportUtility::getDirectoryFiles()` (SettingsUI.php:3377) | inline echo |
| `ajax_wizardWebsite` | `settings.website < ACCESS_LEVEL_SA` (SettingsUI.php:885) | **SA (400)** | `wizard_website()` (SettingsUI.php:890) | hook `SETTINGS_CP_REQUEST` (SettingsUI.php:3388) | inline echo |
| `administration` (GET) | `settings.administration.GET < ACCESS_LEVEL_DEMO` unless careerportal (SettingsUI.php:904) | DEMO (350) / careerportal | `administration()` (SettingsUI.php:908) | `SystemInfo->getSystemInfo()`, `CareerPortalSettings->getAll()`, `Candidates->getCount()` (SettingsUI.php:2408-2568) | `Administration.tpl` + sub-pages (SettingsUI.php:2579) |
| `administration` (POST) | `settings.administration.POST < ACCESS_LEVEL_SA` unless careerportal (SettingsUI.php:896) | **SA (400)** / careerportal | `onAdministration()` (SettingsUI.php:900) | `Site->setName()/setLocalization()/setDefaultPhoneCountryCode()`, `SystemInfo->updateVersionCheckPrefs()` (SettingsUI.php:2644-2730) | redirect → `administration` (SettingsUI.php:2610) |
| `addEmailTemplate` | none in switch; method re-checks `$this->_realAccessLevel < ACCESS_LEVEL_SA` (SettingsUI.php:968) | **SA (400)** (in handler) | `addEmailTemplate()` (POST only, SettingsUI.php:915) | `EmailTemplates->add()` (SettingsUI.php:976) | `EmailTemplates.tpl` via `emailTemplates()` (SettingsUI.php:983) |
| `deleteEmailTemplate` | none in switch; method re-checks `$this->_realAccessLevel < ACCESS_LEVEL_SA` (SettingsUI.php:948) | **SA (400)** (in handler) | `deleteEmailTemplate()` (POST only, SettingsUI.php:926) | `EmailTemplates->delete()` (SettingsUI.php:961) | `EmailTemplates.tpl` via `emailTemplates()` (SettingsUI.php:963) |
| `myProfile` / `default` | `settings.myProfile < ACCESS_LEVEL_READ` (SettingsUI.php:937) | READ (100) | `myProfile()` (SettingsUI.php:941) | — (assigns session flags) | `MyProfile.tpl` / `ChangePassword.tpl` (SettingsUI.php:999-1009) |

### Administration sub-pages (`a=administration&s=...`)

`administration()` dispatches a secondary `$_GET['s']` switch with its own guards (SettingsUI.php:2438-2528):
- `s=siteName` → `SiteName.tpl` (SettingsUI.php:2443).
- `s=newVersionCheck` → requires `$systemAdministration` (ROOT≥500 or DEMO) else fatal; `NewVersionCheck.tpl` (SettingsUI.php:2447-2456).
- `s=passwords` → requires `$systemAdministration` else fatal; `Passwords.tpl` (SettingsUI.php:2460-2465).
- `s=localization` → `settings.administration.localization < ACCESS_LEVEL_SA` (SettingsUI.php:2469); `Localization.tpl` (SettingsUI.php:2489).
- `s=systemInformation` → `settings.administration.systemInformation < ACCESS_LEVEL_SA` (SettingsUI.php:2493); reads `DatabaseConnection`, `ModuleUtility::getModuleSchemaVersions()`; `SystemInformation.tpl` (SettingsUI.php:2498-2522).

`$systemAdministration` is true when `getUserAccessLevel('settings.administration') >= ACCESS_LEVEL_ROOT` OR `== ACCESS_LEVEL_DEMO` (SettingsUI.php:2428).

`onAdministration()` switches on POST `administrationMode` (SettingsUI.php:2592):
- `changeSiteName` → `settings.administration.changeSiteName < ACCESS_LEVEL_SA` (SettingsUI.php:2595); `changeSiteName()` → `Site->setName()` (SettingsUI.php:2717).
- `changeVersionCheck` → `settings.administration.changeVersionName < ACCESS_LEVEL_ROOT` (note **ROOT/500** required here) (SettingsUI.php:2614); `changeNewVersionCheck()` → `SystemInfo->updateVersionCheckPrefs()` (SettingsUI.php:2730).
- `localization` → `settings.administration.localization < ACCESS_LEVEL_SA` (SettingsUI.php:2628); `Site->setLocalization()/setDefaultPhoneCountryCode()` then logs the user out (SettingsUI.php:2645-2669).

## Per-action detail (major areas)

### Add / edit / delete user
- **Add (`onAddUser`, SettingsUI.php:1176)**: LDAP mode short-circuits (no manual creation, SettingsUI.php:1178-1182). Validates required fields, password match, e-mail-format usernames, multisite username suffixing, and `usernameExists()` (SettingsUI.php:1206-1235). License gate: `if (!$license['canAdd'] && $accessLevel > ACCESS_LEVEL_READ)` fatals (SettingsUI.php:1197). Inserts via `Users->add($lastName,$firstName,$email,$username,$password,$accessLevel,$eeoIsVisible)` (signature: `add($lastName,$firstName,$email,$username,$password,$accessLevel,$eeoIsVisible=false,$userSiteID=-1)`, lib/Users.php:89-90). Role/category is only applied when `$category[3] <= $this->_realAccessLevel` via `Users->updateCategories()` (SettingsUI.php:1254-1257).
- **Edit (`onEditUser`, SettingsUI.php:1369)**: forbids changing one's own access level (`if ($userID == $this->_userID) $accessLevel = $this->_realAccessLevel;`, SettingsUI.php:1419-1422); refuses blank password resets and password `'cats'` (SettingsUI.php:1405-1451). Calls `Users->update($userID,$lastName,$firstName,$email,$username,$accessLevel,$eeoIsVisible)` (signature `update($userID,$lastName,$firstName,$email,$username,$accessLevel=-1,$eeoIsVisible=false)`, lib/Users.php:150-151) and `resetPassword()` (lib/Users.php:734). Categories are cleared then re-applied (SettingsUI.php:1462-1482).
- **Delete (`onDeleteUser`, SettingsUI.php:1495)**: gated by `ACCESS_LEVEL_SA` in the switch AND an `iAmTheAutomatedTester` POST flag (SettingsUI.php:1504); the inline comment states it exists "only for automated testing" because of referential-integrity issues (SettingsUI.php:1491-1493). Calls `Users->delete()` (lib/Users.php:256).
- **showUser (SettingsUI.php:1025)** loads `Users->get()` (lib/Users.php:278), `getAccessLevels()` (lib/Users.php:561), and up to `MAX_RECENT_LOGINS` rows via `getLastLoginAttempts($userID,$limit)` (lib/Users.php:584), shortening user-agents through `BrowserDetection::detect()` (SettingsUI.php:1067).

### Site / system settings
- **Site name**: `Site->setName($name)` (lib/Site.php:57) from `onNewSiteName`/`changeSiteName`/`wizard_siteName` (SettingsUI.php:2373,2718,3357).
- **Localization**: `Site->setLocalization($timeZone,$isDMY)` (lib/Site.php:80) and the new `Site->setDefaultPhoneCountryCode($countryCode)` (lib/Site.php:104); `onAdministration` persists then forces logout for the change to take effect (SettingsUI.php:2660-2671).
- **System information** reads `DatabaseConnection::getInstance()->getRDBMSVersion()` and `ModuleUtility::getModuleSchemaVersions()` (SettingsUI.php:2498-2516).
- **Version check**: `SystemInfo->updateVersionCheckPrefs()` + `NewVersionCheck::checkForUpdate()` (SettingsUI.php:2730-2732).

### Email templates
`emailTemplates()` lists via `EmailTemplates->getAll()` (lib/EmailTemplates.php:374). `onEmailTemplates()` saves a single template through `EmailTemplates->update($emailTemplateID,$title,$text,$disabled)` (lib/EmailTemplates.php:116). `addEmailTemplate()` inserts a `CUSTOM` template with the default variable placeholder string `%CANDSTATUS%%CANDOWNER%%CANDFIRSTNAME%%CANDFULLNAME%%CANDPREVSTATUS%` via `EmailTemplates->add($text,$title,$tag,$siteID,$possibleVariables)` (lib/EmailTemplates.php:71; SettingsUI.php:974-976). `deleteEmailTemplate()` → `EmailTemplates->delete($templateID)` (lib/EmailTemplates.php:52). Email-settings page also toggles per-template active flags via `EmailTemplates->updateIsActive($emailTemplateID,$disabled)` (lib/EmailTemplates.php:173; SettingsUI.php:2114). **Note:** `addEmailTemplate`/`deleteEmailTemplate` have no `getUserAccessLevel` guard in the switch; the SA enforcement lives inside the handlers using `$this->_realAccessLevel < ACCESS_LEVEL_SA` (SettingsUI.php:948,968).

### Extra fields
`customizeExtraFields()` pulls per-entity settings from `Candidates/Contacts/Companies/JobOrders ->extraFields->getSettings()` (lib/ExtraFields.php:57) plus `ExtraFields::getValuesTypes()` (static, lib/ExtraFields.php:960). `onCustomizeExtraFields()` parses a comma-joined `commandList` of URL-encoded commands and dispatches per verb (SettingsUI.php:1550-1608): `ADDFIELD`→`define($fieldName,$fieldType)` (lib/ExtraFields.php:88), `DELETEFIELD`→`remove()` (lib/ExtraFields.php:135), `ADDOPTION`→`addOptionToColumn()` (lib/ExtraFields.php:176), `DELETEOPTION`→`deleteOptionFromColumn()` (lib/ExtraFields.php:237), `SWAPFIELDS`→`swapColumns()` (lib/ExtraFields.php:295), `RENAMEROW`→`renameColumn()` (lib/ExtraFields.php:384).

### Career-portal settings
Backed by `class CareerPortalSettings` (lib/CareerPortal.php:39) which declares `public $requiredTemplateFields` (lib/CareerPortal.php:42). `careerPortalSettings()` loads `getAll()` (lib/CareerPortal.php:73), `getDefaultTemplates()` (lib/CareerPortal.php:232), `getCustomTemplates()` (lib/CareerPortal.php:253), and active questionnaires (SettingsUI.php:1832-1841). `onCareerPortalSettings()` iterates each setting; checkbox settings (`enabled`, `allowBrowse`, `candidateRegistration`, `showDepartment`, `showCompany`) are set to `'1'`/`'0'` via `set($setting,$value)` (lib/CareerPortal.php:403); toggling `enabled` mid-loop redirects back (SettingsUI.php:1863-1932). `careerPortalTemplateEdit()`/`onCareerPortalTemplateEdit()` use `getAllFromCustomTemplate()` (lib/CareerPortal.php:146) and `setForTemplate($setting,$value,$template)` (lib/CareerPortal.php:358), keying POST fields by `md5($setting)` (SettingsUI.php:1792-1804). `onCareerPortalTweak()` handles board lifecycle: `new`/`duplicate` copy default+custom templates, `delete`→`deleteCustomTemplate()` (lib/CareerPortal.php:334), `setAsActive`→`set('activeBoard',...)` (SettingsUI.php:1951-2016). Career-portal-category users bypass the SA requirement on these actions via `!$_SESSION['CATS']->hasUserCategory('careerportal')` clauses.

### Questionnaires
`Questionnaire` (lib/Questionnaire.php). `careerPortalQuestionnaire()` builds an in-session working copy in `$_SESSION['CATS_QUESTIONNAIRE']` (SettingsUI.php:3436) and on edit loads `get()` (lib/Questionnaire.php:89) + `getQuestions()` (lib/Questionnaire.php:157), labeling types via `convertQuestionConstantToType()` (lib/Questionnaire.php:468). `onCareerPortalQuestionnaire()` (SettingsUI.php:3476) mutates the session copy across multiple steps (edit text/type/position, add question/answer/action, removals, bubble-sort, renumber) using `convertQuestionTypeToConstant()` (lib/Questionnaire.php:457); on save it persists via `update()`/`add()` then `deleteQuestions()` + `addQuestions()` (lib/Questionnaire.php:262,110,235,294; SettingsUI.php:3861-3887). `careerPortalQuestionnaireUpdate()` deletes selected questionnaires via `delete()` (lib/Questionnaire.php:137; SettingsUI.php:3913). `careerPortalQuestionnairePreview()` renders read-only (SettingsUI.php:3920).

### Backup
The `createBackup` page (`createBackup()`, SettingsUI.php:2215) lists existing backup attachments from `Attachments->getAll(DATA_ITEM_COMPANY, getSiteCompanyID())` against `CATS_ADMIN_SITE`. `deleteBackup()` removes them via `Attachments->deleteAll(... "AND content_type = 'catsbackup'")` (SettingsUI.php:2239). The heavy lifting is the AJAX handler `modules/settings/ajax/backup.php`, instantiated through `SecureAJAXInterface` (backup.php:34). It independently re-checks `$_SESSION['CATS']->getAccessLevel(ACL::SECOBJ_ROOT) < ACCESS_LEVEL_SA` and dies on failure (backup.php:41-44) and requires POST (backup.php:36-39). `a=start` deletes old backups and creates a placeholder attachment via `AttachmentCreator` (backup.php:75-124); `a=backup` opens a `ZipFileCreator`, optionally dumps the DB through `dumpDB()` from `modules/install/backupDB.php` in ~1MB chunks, adds every attachment row read directly from the `attachment` table, finalizes the zip, and updates size/MD5 via `Attachments->setSizeMD5()` (backup.php:126-312). Progress is streamed by writing `progress.txt` in the backup directory (backup.php:62-72).

## Templates (`modules/settings/*.tpl`)

User management / profile:
- `MyProfile.tpl` — self-service profile landing page (SettingsUI.php:1009).
- `ChangePassword.tpl` — change-password form (SettingsUI.php:999).
- `Users.tpl` — site user list / management table (SettingsUI.php:2771).
- `ShowUser.tpl` — single-user detail incl. recent login attempts (SettingsUI.php:1121).
- `AddUser.tpl` — add-user form (SettingsUI.php:1170).
- `EditUser.tpl` — edit-user form (SettingsUI.php:1363).

Administration / system:
- `Administration.tpl` — admin landing page / settings hub (SettingsUI.php:2526).
- `SiteName.tpl` — change-site-name sub-page (SettingsUI.php:2443).
- `NewVersionCheck.tpl` — version-check preference/news sub-page (SettingsUI.php:2456).
- `Passwords.tpl` — password-policy sub-page (SettingsUI.php:2465).
- `Localization.tpl` — timezone/date-format/phone-country sub-page (SettingsUI.php:2489).
- `SystemInformation.tpl` — DB/OS/schema-version info (SettingsUI.php:2522).
- `Professional.tpl` — Professional license-key upgrade page (SettingsUI.php:2841).
- `NewInstallWizard.tpl` — multi-purpose first-run wizard (password/site name/email/conclusion) (SettingsUI.php:2190 et al.).
- `Backup.tpl` — backup management page (SettingsUI.php:2233).
- `CustomizeReports.tpl` — reports customization page (SettingsUI.php:2177).
- `CustomizeCalendar.tpl` — calendar settings (SettingsUI.php:2132).
- `CustomizeExtraFields.tpl` — extra-field editor (SettingsUI.php:1542).
- `EmailSettings.tpl` — mailer + status-change-notification settings (SettingsUI.php:2077).
- `EmailTemplates.tpl` — email-template list/editor (SettingsUI.php:1625).
- `EEOEOCSettings.tpl` — EEO/EOC toggles (SettingsUI.php:2034).
- `LoginActivity.tpl` — paged login-activity log (SettingsUI.php:3048).
- `ItemHistory.tpl` — data-item revision history (SettingsUI.php:3106).

Career portal:
- `CareerPortalSettings.tpl` — career-portal settings + board list (SettingsUI.php:1851).
- `CareerPortalTemplateEdit.tpl` — per-board template field editor (SettingsUI.php:1769).
- `CareerPortalQuestionnaire.tpl` — questionnaire builder (SettingsUI.php:3473).
- `CareerPortalQuestionnaireShow.tpl` — questionnaire preview (SettingsUI.php:3944).

Tags / misc / shared:
- `tags.tpl` — tag tree editor (SettingsUI.php:221).
- `PreviewPage.tpl` — framed preview page (SettingsUI.php:1686).
- `PreviewPageTop.tpl` — top-frame preview message + close button (SettingsUI.php:1697).
- `Error.tpl` — error display (referenced by module conventions; not directly displayed in SettingsUI.php switch — see Unverified).

Static assets (not templates): `Settings.js`, `validator.js`, `downloads.css` (listed in `modules/settings/`).

## ajax/ handlers

- `modules/settings/ajax/backup.php` — backup generator (see Backup detail). Uses `SecureAJAXInterface` (backup.php:34); independent SA check `getAccessLevel(ACL::SECOBJ_ROOT) < ACCESS_LEVEL_SA` (backup.php:41); POST-only (backup.php:36). Actions `start` (backup.php:75) and `backup` (backup.php:126). Fires `FORCE_ATTACHMENT_LOCAL` per attachment (backup.php:285).

The `ajax_*` and `ajax_tags_*` cases are **not** separate files — they are dispatched inside `SettingsUI::handleRequest()` (see Action catalog).

## lib/ dependencies (cited)

Included at top of SettingsUI.php:30-49 (LoginActivity, NewVersionCheck, Candidates, Companies, Contacts, Graphs, Site, ListEditor, SystemUtility, Mailer, EmailTemplates, License, History, Pipelines, CareerPortal, WebForm, CommonErrors, ImportUtility, Questionnaire, Tags). Methods actually invoked:

- **lib/Users.php**: `add($lastName,$firstName,$email,$username,$password,$accessLevel,$eeoIsVisible=false,$userSiteID=-1)` (89-90); `update($userID,$lastName,$firstName,$email,$username,$accessLevel=-1,$eeoIsVisible=false)` (150-151); `updateSelfEmail($userID,$email)` (202); `updateCategories($userID,$categories)` (228); `delete($userID)` (256); `get($userID)` (278); `getAll()` (474); `getAccessLevels()` (561); `getLastLoginAttempts($userID,$limit)` (584); `changePassword($userID,$currentPassword,$newPassword)` (649); `resetPassword($userID,$newPassword)` (734); `getLicenseData()` (890); `usernameExists($username)` (941); `isUserLDAP($userID)` (1331).
- **lib/Site.php**: `setName($name)` (57); `setLocalization($timeZone,$isDMY)` (80); `setDefaultPhoneCountryCode($countryCode)` (104); `setAgreedToLicense()` (206); `setLocalizationConfigured()` (224); `setFirstTimeSetup()` (242).
- **lib/EmailTemplates.php**: `delete($templateID)` (52); `add($text,$title,$tag,$siteID,$possibleVariables)` (71); `update($emailTemplateID,$title,$text,$disabled)` (116); `updateIsActive($emailTemplateID,$disabled)` (173); `getAll()` (374).
- **lib/ExtraFields.php**: `getSettings()` (57); `define($fieldName,$fieldType)` (88); `remove($fieldName)` (135); `addOptionToColumn($fieldName,$optionName)` (176); `deleteOptionFromColumn($fieldName,$optionName)` (237); `swapColumns($fieldName1,$fieldName2)` (295); `renameColumn($oldName,$newName)` (384); `getValuesForAdd()` (613); `getValuesTypes()` (static, 960).
- **lib/Questionnaire.php**: `getAll($includeInactive=false)` (62); `get($id)` (89); `add($title,$description,$isActive)` (110); `delete($id)` (137); `getQuestions($id)` (157); `deleteQuestions($id)` (235); `update($id,$title,$description,$isActive)` (262); `addQuestions($questionnaireID,$questions)` (294); `convertQuestionTypeToConstant($type)` (457); `convertQuestionConstantToType($type)` (468).
- **lib/CareerPortal.php** (`CareerPortalSettings`, class at 39): `getAll()` (73); `getAllFromCustomTemplate($template)` (146); `getAllFromDefaultTemplate($template)` (173); `getDefaultTemplates()` (232); `getCustomTemplates()` (253); `deleteCustomTemplate($template)` (334); `setForTemplate($setting,$value,$template)` (358); `set($setting,$value)` (403). Public `$requiredTemplateFields` (42).
- **Other libs**: `MailerSettings::getAll()` (lib/Mailer.php:417) / `set()` (lib/Mailer.php:476); `CalendarSettings::getAll()` (lib/Calendar.php:1039) / `set()` (lib/Calendar.php:1089); `EEOSettings` (class lib/Candidates.php:2369); `Tags->add/delete/update` (SettingsUI.php:138,178,191).

## Hooks fired (cited)

- `XML_FEED_SUBMISSION_SETTINGS_HEADERS` — eval'd at file load (SettingsUI.php:50).
- `SETTINGS_HANDLE_REQUEST` — top of `handleRequest()` (SettingsUI.php:228).
- `SETTINGS_DISPLAY_PROFILE_SETTINGS` — in `myProfile()` (SettingsUI.php:1012); also defined in `defineHooks()` (SettingsUI.php:111).
- `SETTINGS_ADD_USER` — in `addUser()` before display (SettingsUI.php:1168).
- `SETTINGS_ON_ADD_USER` — in `onAddUser()` after insert (SettingsUI.php:1269).
- `SETTINGS_EMAIL_TEMPLATES` — in `emailTemplates()` (SettingsUI.php:1620). (Also commented-out in `changeTags()`, SettingsUI.php:216.)
- `SETTINGS_CAREER_PORTAL` — in `careerPortalSettings()` (SettingsUI.php:1838).
- `XML_FEED_SUBMISSION_SETTINGS_BODY` — inside `onCareerPortalSettings()` loop (SettingsUI.php:1862).
- `SETTINGS_DISPLAY_ADMINISTRATION` — in `administration()` (SettingsUI.php:2563).
- `SETTINGS_CP_REQUEST` — in `wizard_website()` (SettingsUI.php:3388).
- `FORCE_ATTACHMENT_LOCAL` — in ajax/backup.php per attachment (backup.php:285).
- Defined-only (in `defineHooks()`, fired elsewhere): `TEMPLATE_UTILITY_EVALUATE_TAB_VISIBLE` (SettingsUI.php:91), `HOME` (102), and `*_HANDLE_REQUEST` for clients/contacts/calendar/jo/candidates/activity/reports (120-126).

## Source evidence

- `modules/settings/SettingsUI.php` (3949 lines) — read in full (lines 1-3949).
- `modules/settings/ajax/backup.php` — read in full (1-314).
- `modules/settings/*.tpl` — enumerated by directory listing and cross-referenced to `display()` calls in SettingsUI.php.
- `lib/Users.php`, `lib/Site.php`, `lib/EmailTemplates.php`, `lib/ExtraFields.php`, `lib/Questionnaire.php`, `lib/CareerPortal.php` — opened to confirm signatures/line numbers (cited above).
- `constants.php:74-82` — ACCESS_LEVEL_* values. `lib/UserInterface.php:429-432` — `getUserAccessLevel()` delegation.

## Unverified / open questions

- **`addEmailTemplate` / `deleteEmailTemplate` switch guards**: these two cases (SettingsUI.php:912-932) have **no** `getUserAccessLevel` check in the switch; authorization relies solely on the in-handler `$this->_realAccessLevel < ACCESS_LEVEL_SA` test (SettingsUI.php:948,968). Whether `_realAccessLevel` can diverge from the per-object ACL for these objects was not traced beyond the constructor assignment (SettingsUI.php:65).
- **`Error.tpl`**: present in the directory but I found no `display('./modules/settings/Error.tpl')` call within SettingsUI.php; its trigger (likely a base-class/error path) was not confirmed.
- **`Settings.js`, `validator.js`, `downloads.css`**: referenced by templates rather than the controller; their exact include sites were not traced.
- **`Tags` add/update signatures**: I cited the call sites (SettingsUI.php:138,178,191) but did not open `lib/Tags.php` to confirm parameter lists; the third `"-"` argument's meaning is unverified.
- **`getAccessLevel()` semantics for per-object ACL strings** (e.g. `settings.addUser.POST` vs `settings.addUser`): I confirmed `getUserAccessLevel` delegates to `$_SESSION['CATS']->getAccessLevel()` (lib/UserInterface.php:431) but did not trace how unknown/compound object names resolve in `lib/Session.php`/`lib/ACL.php`.
- **`reports` POST branch** is empty (SettingsUI.php:485-488); no save logic exists for that path.
- Template *titles* could not be auto-extracted (grep returned empty for most `.tpl`); roles above are inferred from the controller's `display()` call sites, which is authoritative for routing but not for in-page headings.

---

## ACL-SUMMARY

```
settings.tags => ACCESS_LEVEL_SA          (bypassed for careerportal)
settings.changePassword => (none)         (blocks only ACCESS_LEVEL_DEMO)
settings.newInstallPassword => ACCESS_LEVEL_SA
settings.forceEmail => ACCESS_LEVEL_SA
settings.newSiteName => ACCESS_LEVEL_SA
settings.upgradeSiteName => ACCESS_LEVEL_SA
settings.newInstallFinished => ACCESS_LEVEL_SA   (guard object is settings.newSiteName)
settings.manageUsers => ACCESS_LEVEL_DEMO
settings.professional => ACCESS_LEVEL_DEMO
settings.previewPage => ACCESS_LEVEL_READ
settings.previewPageTop => ACCESS_LEVEL_READ
settings.showUser => ACCESS_LEVEL_DEMO    (bypassed when viewing own userID)
settings.addUser.GET => ACCESS_LEVEL_DEMO
settings.addUser.POST => ACCESS_LEVEL_SA
settings.editUser.GET => ACCESS_LEVEL_DEMO
settings.editUser.POST => ACCESS_LEVEL_SA
settings.createBackup => ACCESS_LEVEL_SA
settings.deleteBackup => ACCESS_LEVEL_SA
settings.customizeExtraFields.GET => ACCESS_LEVEL_DEMO
settings.customizeExtraFields.POST => ACCESS_LEVEL_SA
settings.customizeCalendar.GET => ACCESS_LEVEL_DEMO
settings.customizeCalendar.POST => ACCESS_LEVEL_SA
settings.reports => ACCESS_LEVEL_DEMO
settings.emailSettings.GET => ACCESS_LEVEL_DEMO
settings.emailSettings.POST => ACCESS_LEVEL_SA
settings.careerPortalQuestionnairePreview => ACCESS_LEVEL_DEMO
settings.careerPortalQuestionnaire => ACCESS_LEVEL_DEMO
settings.careerPortalQuestionnaireUpdate => ACCESS_LEVEL_DEMO
settings.careerPortalTemplateEdit (GET) => ACCESS_LEVEL_DEMO   (bypassed for careerportal)
settings.careerPortalTemplateEdit.POST => ACCESS_LEVEL_SA      (bypassed for careerportal)
settings.careerPortalSettings (outer) => ACCESS_LEVEL_DEMO     (bypassed for careerportal)
settings.careerPortalSettings.GET => ACCESS_LEVEL_DEMO         (bypassed for careerportal)
settings.careerPortalSettings.POST => ACCESS_LEVEL_SA          (bypassed for careerportal)
settings.eeo.GET => ACCESS_LEVEL_DEMO
settings.eeo.POST => ACCESS_LEVEL_SA
settings.careerPortalTweak => ACCESS_LEVEL_SA                  (bypassed for careerportal)
settings.deleteUser => ACCESS_LEVEL_SA
settings.emailTemplates.GET => ACCESS_LEVEL_DEMO               (bypassed for careerportal)
settings.emailTemplates.POST => ACCESS_LEVEL_SA               (bypassed for careerportal)
settings.aspLocalization => ACCESS_LEVEL_SA
settings.loginActivity => ACCESS_LEVEL_DEMO
settings.viewItemHistory => ACCESS_LEVEL_DEMO
settings.ajax_tags_add => (none)          (session/POST only)
settings.ajax_tags_del => (none)          (session/POST only)
settings.ajax_tags_upd => (none)          (session/POST only)
settings.addUser (ajax_wizardAddUser) => ACCESS_LEVEL_SA
settings.deleteUser (ajax_wizardDeleteUser) => ACCESS_LEVEL_SA
settings.checkKey => ACCESS_LEVEL_SA
settings.localization (ajax) => ACCESS_LEVEL_SA
settings.firstTimeSetup => ACCESS_LEVEL_SA
settings.license => ACCESS_LEVEL_SA
settings.password => ACCESS_LEVEL_SA
settings.siteName => ACCESS_LEVEL_SA
settings.setEmail => ACCESS_LEVEL_READ
settings.import => ACCESS_LEVEL_SA
settings.website => ACCESS_LEVEL_SA
settings.administration.GET => ACCESS_LEVEL_DEMO              (bypassed for careerportal)
settings.administration.POST => ACCESS_LEVEL_SA              (bypassed for careerportal)
settings.administration.localization => ACCESS_LEVEL_SA
settings.administration.systemInformation => ACCESS_LEVEL_SA
settings.administration.changeSiteName => ACCESS_LEVEL_SA
settings.administration.changeVersionName => ACCESS_LEVEL_ROOT
addEmailTemplate => ACCESS_LEVEL_SA       (no switch guard; enforced in handler via _realAccessLevel)
deleteEmailTemplate => ACCESS_LEVEL_SA    (no switch guard; enforced in handler via _realAccessLevel)
settings.myProfile => ACCESS_LEVEL_READ   (default case)
ajax/backup.php => ACCESS_LEVEL_SA        (getAccessLevel(ACL::SECOBJ_ROOT), POST only)
```
