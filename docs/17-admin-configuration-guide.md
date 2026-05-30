# 17 — Admin & Configuration Guide

> Source-derived. Every constant and default value below is quoted from the repo at the cited file + line. The two configuration files are `config.php` (deployment-time, hand-edited PHP `define()`s, written by the install wizard) and `constants.php` (compile-time enums shipped with the code). In-app administration lives in the **settings** module; this guide cross-references `docs/modules/settings.md` rather than re-deriving its ACL matrix.

---

## config.php reference

Every `define()` in `config.php` (read in full, lines 1–490). Values are the committed defaults exactly as shipped.

### Database

| Constant | Default value | Controls | Cite |
|---|---|---|---|
| `DATABASE_USER` | `'cats'` | MySQL username | config.php:40 |
| `DATABASE_PASS` | `'password'` | MySQL password (committed placeholder — see Sensitive defaults) | config.php:41 |
| `DATABASE_HOST` | `'localhost'` | MySQL host | config.php:42 |
| `DATABASE_NAME` | `'cats_dev'` | MySQL schema name | config.php:43 |

### Licensing

| Constant | Default value | Controls | Cite |
|---|---|---|---|
| `LICENSE_KEY` | `'3163GQ-54ISGW-14E4SHD-ES9ICL-X02DTG-GYRSQ6'` | License key consumed by `License` / the Professional upgrade flow (committed value — see Sensitive defaults) | config.php:31 |
| `LEGACY_ROOT` | `'.'` (only if not already defined) | Base path prefix used by every `include_once` | config.php:34-37 |

### Parsing / Resume binaries

| Constant | Default value | Controls | Cite |
|---|---|---|---|
| `PARSING_ENABLED` | `false` | Enables Resfly.com resume import services | config.php:51 |
| `ANTIWORD_PATH` | `"\\path\\to\\antiword"` | Path to `antiword` (.doc → text) | config.php:62 |
| `ANTIWORD_MAP` | `'8859-1.txt'` | Antiword character-mapping file | config.php:63 |
| `PDFTOTEXT_PATH` | `"\\path\\to\\pdftotext"` | Path to XPDF `pdftotext` (PDF → text) | config.php:69 |
| `HTML2TEXT_PATH` | `"\\path\\to\\html2text"` | Path to `html2text` | config.php:75 |
| `UNRTF_PATH` | `"\\path\\to\unrtf"` | Path to GNU `unrtf` (.rtf → text) | config.php:81 |

> The four binary paths ship as Windows-style placeholders and must be set to real executable paths for resume text extraction to work.

### Search / Sphinx

| Constant | Default value | Controls | Cite |
|---|---|---|---|
| `ENABLE_SPHINX` | `false` | Use Sphinx to accelerate document/resume search | config.php:97 |
| `SPHINX_HOST` | `'localhost'` | Sphinx searchd host | config.php:98 |
| `SPHINX_PORT` | `3312` | Sphinx searchd port | config.php:99 |
| `SPHINX_INDEX` | `'cats catsdelta'` | Space-separated Sphinx index names searched | config.php:100 |
| `US_ZIPS_ENABLED` | `false` | Enables US zipcode DB + distance-from-zip filtering | config.php:251 |

### Mail / SMTP

| Constant | Default value | Controls | Cite |
|---|---|---|---|
| `MAIL_MAILER` | `3` | Mail transport: `0`=Disabled, `1`=PHP mail(), `2`=Sendmail, `3`=SMTP | config.php:197 |
| `MAIL_SENDMAIL_PATH` | `"/usr/sbin/sendmail"` | Sendmail binary (only when `MAIL_MAILER`=2) | config.php:202 |
| `MAIL_SMTP_HOST` | `"localhost"` | SMTP host (only when `MAIL_MAILER`=3) | config.php:208 |
| `MAIL_SMTP_PORT` | `587` | SMTP port | config.php:209 |
| `MAIL_SMTP_AUTH` | `true` | Whether SMTP requires authentication | config.php:210 |
| `MAIL_SMTP_USER` | `"user"` | SMTP username | config.php:211 |
| `MAIL_SMTP_PASS` | `"password"` | SMTP password (committed placeholder) | config.php:212 |
| `MAIL_SMTP_SECURE` | `"tls"` | SMTP encryption: `''`, `'ssl'`, or `'tls'` | config.php:214 |
| `$GLOBALS['eventReminderEmail']` | heredoc template | Body of calendar event-reminder emails (uses `%FULLNAME%`, `%EVENTNAME%`, `%DUETIME%`, `%NOTES%` placeholders) | config.php:217-231 |

> Note: `MAIL_MAILER` defaults to `3` (SMTP) here even though the inline comment recommends `0` for Windows. Mailer values can also be changed in-app via Email Settings (`MailerSettings->set()`), which overrides config-file behavior for runtime sends.

### Security / SSL

| Constant | Default value | Controls | Cite |
|---|---|---|---|
| `SSL_ENABLED` | `false` | Forces SSL for all of CATS | config.php:54 |
| `ENABLE_DEMO_MODE` | `false` | Marks the install a demo site (relaxes/locks certain behavior) | config.php:166 |
| `ENABLE_SINGLE_SESSION` | `false` | Enforce only one active session per user (excludes demo) | config.php:172 |
| `CATS_SLAVE` | `false` | Replication-slave mode: blocks all DB writes; only ROOT users may log in | config.php:237 |
| `DEMO_LOGIN` | `'john@mycompany.net'` | Demo-mode auto-login username | config.php:185 |
| `DEMO_PASSWORD` | `'john99'` | Demo-mode auto-login password | config.php:186 |
| `TESTER_LOGIN` / `TESTER_PASSWORD` / `TESTER_FIRSTNAME` / `TESTER_LASTNAME` / `TESTER_FULLNAME` / `TESTER_USER_ID` | `'john@mycompany.net'`, `'john99'`, `'John'`, `'Anderson'`, `'John Anderson'`, `4` | Automated-testing fixtures ("only useful for the CATS core team") | config.php:177-182 |

### Auth / LDAP

| Constant | Default value | Controls | Cite |
|---|---|---|---|
| `AUTH_MODE` | `'sql'` | Authentication backend: `'sql'`, `'ldap'`, or `'sql+ldap'` | config.php:48 |
| `LDAP_HOST` | `'ldap.forumsys.com'` | LDAP server host (committed demo value) | config.php:255 |
| `LDAP_PORT` | `'389'` | LDAP port | config.php:256 |
| `LDAP_PROTOCOL_VERSION` | `3` | LDAP protocol version | config.php:257 |
| `LDAP_BASEDN` | `'dc=example,dc=com'` | LDAP search base DN | config.php:259 |
| `LDAP_BIND_DN` | `'cn=read-only-admin,dc=example,dc=com'` | Bind DN for directory lookups | config.php:261 |
| `LDAP_BIND_PASSWORD` | `'password'` | Bind password (committed placeholder) | config.php:262 |
| `LDAP_ACCOUNT` | `'cn={$username},dc=example,dc=com'` | User-DN pattern; `{$username}` token is required | config.php:264 |
| `LDAP_ATTRIBUTE_UID` | `'uid'` | LDAP attribute → username | config.php:266 |
| `LDAP_ATTRIBUTE_DN` | `'dn'` | LDAP attribute → DN | config.php:267 |
| `LDAP_ATTRIBUTE_LASTNAME` | `'sn'` | LDAP attribute → last name | config.php:268 |
| `LDAP_ATTRIBUTE_FIRSTNAME` | `'givenname'` | LDAP attribute → first name | config.php:269 |
| `LDAP_ATTRIBUTE_EMAIL` | `'mail'` | LDAP attribute → email | config.php:270 |
| `LDAP_SITEID` | `1` | Site ID that LDAP-provisioned users are assigned to | config.php:272 |
| `LDAP_AD` | `false` | Toggle for Active Directory / Samba LDAP servers | config.php:273 |

> When `AUTH_MODE` involves LDAP, the settings module's add-user path short-circuits (no manual creation) — see `docs/modules/settings.md` "Add / edit / delete user" (LDAP short-circuit, SettingsUI.php:1178-1182), and `Users->isUserLDAP()` (lib/Users.php:1331).

### Performance / Caching

| Constant | Default value | Controls | Cite |
|---|---|---|---|
| `CACHE_MODULES` | `false` | Scan `modules/` once and cache to `modules.cache`; must delete cache after hook/schema/module changes | config.php:245 |
| `ENABLE_HOSTNAME_LOOKUP` | `false` | Reverse-DNS lookups on User Details / Login Activity pages (disable if slow) | config.php:92 |
| `OFFSET_GMT` | `2` | GMT offset for time display | config.php:169 |

### Pagination / UI

| Constant | Default value | Controls | Cite |
|---|---|---|---|
| `CANDIDATES_PER_PAGE` | `15` | Rows per page in candidate-style grids | config.php:106 |
| `LOGIN_ENTRIES_PER_PAGE` | `15` | Rows per page in login-activity log | config.php:107 |
| `LAST_NAME_MAXLEN` | `6` | Truncation length for owner/recruiter last names | config.php:112 |
| `SEARCH_EXCERPT_LENGTH` | `256` | Resume-excerpt length in Search Candidates results | config.php:115 |
| `MRU_MAX_ITEMS` | `5` | Most-recently-used list size | config.php:118 |
| `MRU_ITEM_LENGTH` | `20` | MRU item truncation length | config.php:121 |
| `RECENT_SEARCH_MAX_ITEMS` | `5` | Recent-search list size | config.php:124 |
| `HTML_ENCODING` | `'UTF-8'` | HTML output encoding | config.php:127 |
| `AJAX_ENCODING` | `'UTF-8'` | AJAX/XML output encoding | config.php:130 |
| `SQL_CHARACTER_SET` | `'utf8'` | MySQL connection charset | config.php:133 |
| `INSERT_BOM_CSV_LENGTH` | `'3'` | Number of BOM bytes prepended to CSV exports | config.php:137 |
| `INSERT_BOM_CSV_1` / `_2` / `_3` / `_4` | `'239'`, `'187'`, `'191'`, `''` | UTF-8 BOM bytes (EF BB BF) for CSV | config.php:138-141 |

### Paths

| Constant | Default value | Controls | Cite |
|---|---|---|---|
| `CATS_TEMP_DIR` | `'./temp'` | Web-server-writable temp directory | config.php:87 |
| `MODULES_PATH` | `'./modules/'` | Module discovery root | config.php:144 |
| `CATS_SESSION_NAME` | `'CATS'` | PHP session name (A-Z, 0-9 only); change per-install when co-hosting multiple OpenCATS instances | config.php:148 |

### Career-portal email subjects

| Constant | Default value | Controls | Cite |
|---|---|---|---|
| `CAREERS_CANDIDATEAPPLY_SUBJECT` | `'Thank You for Your Application'` | Subject of confirmation email to candidate on career-portal apply | config.php:153 |
| `CAREERS_OWNERAPPLY_SUBJECT` | `'CATS - A Candidate Has Applied to Your Job Order'` | Subject of notification email to the job-order owner | config.php:158 |
| `CANDIDATE_STATUSCHANGE_SUBJECT` | `'Job Application Status Change'` | Subject of status-change notification to candidate | config.php:163 |

### Commented-out / optional config

The tail of `config.php` ships several **commented-out** customization blocks (not active defaults): `IMPORT_FILE_ENCODING` (config.php:276-280), `JOB_ORDER_STATUS_GROUP` / `_SHARING` / `_FILTERING` / `_STATISTICS` / `_DEFAULT` (config.php:284-314), the `JOB_TYPES` class (config.php:321-328), and an `ACL_SETUP` user-role/access-level-map block (config.php:332-357). A large commented list of every "secure object name" used by the ACL is at config.php:359-488. To activate any of these, uncomment and edit.

---

## constants.php (admin-relevant)

`constants.php` ships fixed enums. The two families an admin cares about:

### Access levels

| Constant | Value | Cite |
|---|---|---|
| `ACCESS_LEVEL_DELETED` | `-100` | constants.php:74 |
| `ACCESS_LEVEL_DISABLED` | `0` | constants.php:75 |
| `ACCESS_LEVEL_READ` | `100` | constants.php:76 |
| `ACCESS_LEVEL_EDIT` | `200` | constants.php:77 |
| `ACCESS_LEVEL_DELETE` | `300` | constants.php:78 |
| `ACCESS_LEVEL_DEMO` | `350` | constants.php:79 |
| `ACCESS_LEVEL_SA` | `400` | constants.php:80 |
| `ACCESS_LEVEL_MULTI_SA` | `450` | constants.php:81 |
| `ACCESS_LEVEL_ROOT` | `500` | constants.php:82 |

Full ACL semantics (per-secured-object resolution, `getUserAccessLevel()` delegation) are deferred to **doc 14**.

### Settings-type IDs

These identify which settings row a `*Settings` lib reads/writes:

| Constant | Value | Cite |
|---|---|---|
| `SETTINGS_MAILER` | `1` | constants.php:68 |
| `SETTINGS_CALENDAR` | `2` | constants.php:69 |
| `SETTINGS_EEO` | `3` | constants.php:70 |
| `SETTINGS_CAREER_PORTAL` | `4` | constants.php:71 |

### Other admin-relevant constants

| Constant | Value | Note | Cite |
|---|---|---|---|
| `CATS_VERSION` | `'0.9.7.4'` | Application version string | constants.php:45 |
| `DEFAULT_ADMIN_PASSWORD` | `'cats'` | Default admin password (the edit-user flow explicitly refuses to keep `'cats'`) | constants.php:178 |
| `DEFAULT_MAIL_FROM_ADDRESS` | `'noreply@yourdomain.com'` | Default From address | constants.php:179 |
| `CATS_ADMIN_SITE` | `180` | Special admin site_id (used by backups, `getFirstSiteID`) | constants.php:187 |
| `BACKUP_TAR` / `BACKUP_ZIP` / `BACKUP_CATS` | `1` / `2` / `3` | Backup format IDs | constants.php:147-149 |
| `EXTRA_FIELD_TEXT`…`EXTRA_FIELD_RADIO` | `1`–`6` | Extra-field type IDs | constants.php:133-138 |
| `MODULE_SETTINGS_ENTRIES` / `_USER_LEVEL` / `MODULE_SETTINGS_USER_CATEGORIES` | `3` / `2` / `4` | Settings-tab entry counts | constants.php:182-184 |

`Users.php` also defines login/add-user status flags an admin may see in errors: `LOGIN_SUCCESS=1`, `LOGIN_INVALID_USER=-1`, `LOGIN_INVALID_PASSWORD=-2`, `LOGIN_DISABLED=-3`, `LOGIN_CANT_CHANGE_PASSWORD=-4`, `LOGIN_ROOT_ONLY=-5`, `LOGIN_PENDING_APPROVAL=-6` (lib/Users.php:40-47); `ADD_USER_SUCCESS=1`, `ADD_USER_BAD_PASS=-1`, `ADD_USER_EXISTS=-2`, `ADD_USER_DB_ERROR=-3` (lib/Users.php:50-53); and the LDAP sentinel `LDAPUSER_PASSWORD='_LDAPUSER_'` (lib/Users.php:55).

---

## The settings module (admin UI)

All in-app administration is the **settings** module (`class SettingsUI extends UserInterface`, modules/settings/SettingsUI.php:55), authentication-forced, dispatched from one `handleRequest()` switch. The authoritative action/ACL catalog is **`docs/modules/settings.md`** (Action catalog table + ACL-SUMMARY). Summary of what an admin can configure — most write/POST paths require **`ACCESS_LEVEL_SA` (400)**:

| Admin capability | Action(s) `a=` | Min level | Cite (settings.md / source) |
|---|---|---|---|
| **User management** — list users | `manageUsers` | DEMO (350) | settings.md Action catalog; SettingsUI.php:336 |
| Add user | `addUser` GET / POST | DEMO view, **SA** to create | SettingsUI.php:379-391; `Users->add()` lib/Users.php:89 |
| Edit user / reset password / access level | `editUser` GET / POST | DEMO view, **SA** to save | SettingsUI.php:400-412; `Users->update()` lib/Users.php:150, `resetPassword()` lib/Users.php:734 |
| Delete user | `deleteUser` | **SA** + `iAmTheAutomatedTester` flag | SettingsUI.php:628-634; `Users->delete()` lib/Users.php:256 |
| View single user / login attempts | `showUser` | DEMO or self | SettingsUI.php:368-373 |
| **Site / system settings** — site name | `administration` / `newSiteName` / `aspLocalization` | **SA** | SettingsUI.php:291,663,896; `Site->setName()` lib/Site.php:57 |
| Localization (timezone, D-M-Y, phone country code) | `administration` (s=localization) | **SA** | SettingsUI.php:2469,2628; `Site->setLocalization()` lib/Site.php:80, `setDefaultPhoneCountryCode()` lib/Site.php:104 |
| System information (DB version, schema versions) | `administration` (s=systemInformation) | **SA** | SettingsUI.php:2493 |
| Version check / news | `administration` (s=newVersionCheck) | ROOT (500) to change | SettingsUI.php:2447,2614 |
| **Email settings** (mailer + status-change notify) | `emailSettings` GET / POST | DEMO view, **SA** save | SettingsUI.php:499-511; `MailerSettings->set()` lib/Mailer.php:476 |
| **Email templates** (list/edit/toggle/add/delete) | `emailTemplates`, `addEmailTemplate`, `deleteEmailTemplate` | **SA** (add/delete enforced in-handler via `_realAccessLevel`) | SettingsUI.php:646-658,948,968; `EmailTemplates->*` lib/EmailTemplates.php:52-374 |
| **Extra fields** (per entity) | `customizeExtraFields` GET / POST | DEMO view, **SA** save | SettingsUI.php:444-456; `ExtraFields->*` lib/ExtraFields.php:88-384 |
| **Calendar customization** | `customizeCalendar` GET / POST | DEMO view, **SA** save | SettingsUI.php:464-476; `CalendarSettings->set()` lib/Calendar.php:1089 |
| **Career-portal settings / boards / templates** | `careerPortalSettings`, `careerPortalTemplateEdit`, `onCareerPortalTweak` | **SA** to save (bypassed for `careerportal` category users) | SettingsUI.php:551-618; `CareerPortalSettings->*` lib/CareerPortal.php:73-403 |
| Career-portal questionnaires | `careerPortalQuestionnaire*` | DEMO | SettingsUI.php:516-544; `Questionnaire->*` lib/Questionnaire.php |
| **EEO settings** | `eeo` GET / POST | DEMO view, **SA** save | SettingsUI.php:594-606; `EEOSettings->set()` |
| **Backup** (create/delete site backups) | `createBackup`, `deleteBackup` | **SA** | SettingsUI.php:418-432; AJAX engine `modules/settings/ajax/backup.php` re-checks `getAccessLevel(ACL::SECOBJ_ROOT) < ACCESS_LEVEL_SA` |
| **License / Professional upgrade** | `professional`, `ajax_wizardCheckKey` | DEMO view, **SA** to apply | SettingsUI.php:344-348,773; `License->setKey()/isProfessional()` |
| Login activity log | `loginActivity` | DEMO | SettingsUI.php:674 |
| Item history | `viewItemHistory` | DEMO | SettingsUI.php:685 |
| First-run install wizard (password/site/email/license) | `newInstallPassword`, `forceEmail`, `newInstallFinished`, `ajax_wizard*` | **SA** (email = READ) | SettingsUI.php:261-885 |
| **My Profile** (self-service, change password) | `myProfile`, `changePassword` | READ (100); blocked for DEMO password change | SettingsUI.php:250,937 |

For the verbatim ACL guard string of each action, the handler method, the lib calls, and the template, see `docs/modules/settings.md` (Action catalog + ACL-SUMMARY).

---

## User & access-level administration

Users are scoped to a **site** (`Users` is constructed with a `$siteID`, lib/Users.php:69-73). The admin flow:

- **Create**: `onAddUser()` calls `Users->add($lastName,$firstName,$email,$username,$password,$accessLevel,$eeoIsVisible=false,$userSiteID=-1)` (lib/Users.php:89-90). The new user's `access_level` is passed directly. A license gate blocks adding users above READ when no seats remain (`if (!$license['canAdd'] && $accessLevel > ACCESS_LEVEL_READ)`, SettingsUI.php:1197). Role/category is only applied when `$category[3] <= $this->_realAccessLevel` (SettingsUI.php:1254), so an admin cannot grant a role above their own real level.
- **Assign access level / edit**: `onEditUser()` → `Users->update($userID,...,$accessLevel=-1,...)` (lib/Users.php:150-151). An admin **cannot change their own access level** — the code forces `$accessLevel = $this->_realAccessLevel` when `$userID == $this->_userID` (SettingsUI.php:1419-1422). Password resets that are blank, or set to the default `'cats'`, are refused (SettingsUI.php:1405-1451; cf. `DEFAULT_ADMIN_PASSWORD`, constants.php:178).
- **Delete**: `Users->delete()` exists but the in-app `deleteUser` is gated behind `ACCESS_LEVEL_SA` **and** an `iAmTheAutomatedTester` POST flag; the source comment states it is wired up only for automated testing due to referential-integrity concerns (SettingsUI.php:1491-1493).

Access-level meanings from an admin POV (values from constants.php:74-82; full per-object matrix deferred to **doc 14**):

- **DISABLED (0)** — account exists but cannot act / is switched off.
- **READ (100)** … **DELETE (300)** — ordinary recruiter tiers (view → edit → delete data).
- **DEMO (350)** — demo/limited tier; can *view* most admin/settings pages (many GET guards use `< ACCESS_LEVEL_DEMO`) but is blocked from password change and most writes.
- **SA (400)** — Site Administrator; the level required for nearly all settings writes (user create/edit, email/extra-fields/calendar/EEO/career-portal saves, backups, license).
- **MULTI_SA (450)** — multi-site administrator (admin across sites); the add-user flow applies multisite username suffixing.
- **ROOT (500)** — superuser; required for version-check changes (SettingsUI.php:2614) and is the only level allowed to log in when `CATS_SLAVE` is enabled (config.php:233-237).

`Users` exposes the supporting reads used by the UI: `get()` (lib/Users.php:278), `getAll()` (lib/Users.php:474), `getAccessLevels()` (lib/Users.php:561), `getLastLoginAttempts()` (lib/Users.php:584), `getLicenseData()` (lib/Users.php:890), `usernameExists()` (lib/Users.php:941), `isUserLDAP()` (lib/Users.php:1331).

---

## Site administration / multi-tenancy

Operationally a **site** is one tenant/account: a `site` table row holding `name`, `is_demo`, `user_licenses`, `unix_name`, `time_zone`, `date_format_ddmmyy`, `default_phone_country_code`, and an `account_deleted` flag (columns visible in `Site->getSiteBySiteID()`, lib/Site.php:154-170). All `Site` operations are scoped to the `$siteID` passed to the constructor (lib/Site.php:44-48). Site-level admin operations:

- `setName($name)` (lib/Site.php:57), `setLocalization($timeZone,$isDMY)` (lib/Site.php:80), `setDefaultPhoneCountryCode($countryCode)` (lib/Site.php:104).
- First-run flags: `setAgreedToLicense()` (lib/Site.php:206), `setLocalizationConfigured()` (lib/Site.php:224), `setFirstTimeSetup()` (lib/Site.php:242) — driven by the install wizard / settings wizard AJAX actions.

**Single vs multi-site**: the schema supports multiple sites. `CATS_ADMIN_SITE` (`180`, constants.php:187) is a reserved administrative site; `getFirstSiteID()` returns the lowest non-admin, non-deleted site_id (lib/Site.php:183-205), and `getSiteByUnixName()` (lib/Site.php:126) resolves a site by its `unix_name` (used for per-site URL routing / career portal). `MULTI_SA` (450) is the access level for administering across sites, and `addUser` performs username suffixing in the multisite case (settings.md, SettingsUI.php). A typical single-tenant deployment uses one operational site plus the admin site. (Whether multi-site is fully wired in the open-source build vs. hosted CATS is left to **doc 14 / Unverified**.)

---

## Operational notes

- **`INSTALL_BLOCK`** — a sentinel file in the OpenCATS root. While it is **absent**, the installer is live: `index.php` redirects to `modules/install/notinstalled.php` unless a maintenance POST is present (index.php:44-48), and `ajax.php` only allows the `install` module (ajax.php:95-100). The install wizard writes/relies on this file and instructs you that "to run the installer again, you must first delete the file named INSTALL_BLOCK" (installwizard.php:105,435,451). So: file present = installed/locked; file deleted = installer re-enabled.
- **Maintenance / reindex** — `index.php` bypasses the not-installed redirect when `$_POST['performMaintenence']` is set (index.php:44). This is the entry used by the maintenance/upgrade path; the schema/version surface is shown on the Administration → System Information sub-page via `ModuleUtility::getModuleSchemaVersions()` (settings.md; SettingsUI.php:2498-2516). Sphinx indexing is controlled by `ENABLE_SPHINX` + `SPHINX_*` (config.php:97-100); building the Sphinx indexes themselves is an external `indexer` task, not an in-app action. (No dedicated `modules/maintenance` controller exists; see Unverified.)
- **Async queue / scheduled jobs** — the asynchronous queue processor (`lib/QueueProcessor.php`) and its cron driver are documented in **doc 12 (`docs/12-async-queue-scheduled-jobs.md`)**; configure that cron separately.
- **Backups** — two paths: (1) in-app under settings (`createBackup` / `deleteBackup`, SA-only), with the real work in `modules/settings/ajax/backup.php` (zips a DB dump + all attachment rows); (2) CLI/cron via **`scripts/makeBackup.php`**, invoked as `php scripts/makeBackup.php 1`, which requires the UNIX `zip` utility and a UNIX environment (scripts/makeBackup.php:27-29,52-60). It bootstraps `config.php`, `constants.php`, `DatabaseConnection`, and `modules/install/backupDB.php`.

---

## Source evidence

- `config.php` — read in full (1–490); every `define()` cited above by line.
- `constants.php` — `ACCESS_LEVEL_*` (74–82), `SETTINGS_*` (68–71), `DATA_ITEM_*` (57–64), `EXTRA_FIELD_*` (133–138), `BACKUP_*` (147–149), `CATS_VERSION` (45), `DEFAULT_ADMIN_PASSWORD` (178), `DEFAULT_MAIL_FROM_ADDRESS` (179), `CATS_ADMIN_SITE` (187), `MODULE_SETTINGS_*` (182–184) confirmed by grep.
- `lib/Site.php` — constructor (44–48), `setName` (57), `setLocalization` (80), `setDefaultPhoneCountryCode` (104), `getSiteByUnixName` (126), `getSiteBySiteID` (154), `getFirstSiteID` (183), `setAgreedToLicense`/`setLocalizationConfigured`/`setFirstTimeSetup` (206/224/242).
- `lib/Users.php` — login/add-user status flags + LDAP sentinel (40–55), class + constructor (63–73), `add()` signature (89–90).
- `index.php` — installer/maintenance gate (44–48), session/extension checks (74–98).
- `ajax.php` — installer-active gate (95–100).
- `installwizard.php` — `INSTALL_BLOCK` instructions (105, 435, 451).
- `scripts/makeBackup.php` — CLI invocation + UNIX/zip requirement + bootstrap (27–60).
- `docs/modules/settings.md` — authoritative settings action/ACL catalog (cross-referenced, not re-derived).

---

## Sensitive defaults (committed)

Stated factually here; remediation detail is **doc 20**:

- `LICENSE_KEY` is committed with a real-looking value `'3163GQ-54ISGW-14E4SHD-ES9ICL-X02DTG-GYRSQ6'` (config.php:31).
- `DATABASE_PASS` defaults to `'password'` (config.php:41); `MAIL_SMTP_PASS` to `'password'` (config.php:212); `LDAP_BIND_PASSWORD` to `'password'` (config.php:262).
- `DEFAULT_ADMIN_PASSWORD` is `'cats'` (constants.php:178); demo/tester creds are committed (`DEMO_PASSWORD='john99'`, config.php:186; `TESTER_PASSWORD='john99'`, config.php:178).
- `LDAP_HOST` ships a public demo server `'ldap.forumsys.com'` (config.php:255).

---

## Unverified / open questions

- **`performMaintenence` handler**: `index.php:44` bypasses the install redirect on this POST flag, but I did not trace which module/page consumes the resulting maintenance POST or where the upgrade/reindex logic ultimately runs (no `modules/maintenance` directory exists). The DB schema-version surface is read on the System Information sub-page, but the write/upgrade trigger path is unconfirmed here.
- **Sphinx index build**: `ENABLE_SPHINX`/`SPHINX_*` configure *querying* Sphinx; the index-build/`indexer` invocation (and any delta-index cron, given `SPHINX_INDEX='cats catsdelta'`) is external and was not located in this repo.
- **Multi-site completeness**: the `site` table and `MULTI_SA` level exist, but whether the open-source build fully exposes multi-tenant admin (vs. the hosted CATS product) was not exhaustively verified; deferred to doc 14.
- **Mailer default mismatch**: `MAIL_MAILER=3` (SMTP) is the committed default even though the inline comment recommends `0` for Windows (config.php:188-197) — noted, not resolved.
- **`MailerSettings` / `EmailTemplates` runtime override vs config**: in-app email settings persist to the settings table (`SETTINGS_MAILER`); the precedence between config-file `MAIL_*` constants and stored `MailerSettings` at send time was not traced end-to-end (see doc on the mailer / module settings).
