# 14 — Permissions & Access-Control Matrix

The authoritative access-control reference for OpenCATS. This document describes the *actual* mechanism in the code (not the design the code was apparently meant to support), then gives the complete module/action guard matrix across all 21 web modules.

> **Headline finding (verified):** OpenCATS has **no role/permission matrix at runtime.** Every user carries a single numeric `access_level`. The "secured object" / ACL category system (`lib/ACL.php`) that would let a dotted key like `candidates.duplicates` map to a *different* level than `candidates.add` is **dormant** — its configuration class `ACL_SETUP` is commented out, so `ACL::getAccessLevel()` always falls through to `return $defaultAccessLevel`, i.e. the user's one `access_level`. The dotted keys in the guards are therefore decorative; only the numeric `< ACCESS_LEVEL_X` comparison and the user's single level matter. See [How a guard resolves](#how-a-guard-resolves).

---

## Access levels — the 9 `ACCESS_LEVEL_*` constants

All nine are defined in one block (constants.php:74-82):

```php
define('ACCESS_LEVEL_DELETED',  -100);   // constants.php:74
define('ACCESS_LEVEL_DISABLED', 0);      // constants.php:75
define('ACCESS_LEVEL_READ',     100);    // constants.php:76
define('ACCESS_LEVEL_EDIT',     200);    // constants.php:77
define('ACCESS_LEVEL_DELETE',   300);    // constants.php:78
define('ACCESS_LEVEL_DEMO',     350);    // constants.php:79
define('ACCESS_LEVEL_SA',       400);    // constants.php:80
define('ACCESS_LEVEL_MULTI_SA', 450);    // constants.php:81
define('ACCESS_LEVEL_ROOT',     500);    // constants.php:82
```

| Constant | Value | Meaning (per code + schema seed) |
|---|---:|---|
| `ACCESS_LEVEL_DELETED` | **-100** | Soft-deleted account. Set when `account_deleted = 1` (lib/Session.php:869-871 forces level to `DISABLED`). Not offered in the user-add UI. |
| `ACCESS_LEVEL_DISABLED` | **0** | "Account Disabled - cannot log in" (db/cats_schema.sql:26). Login refused when `accessLevel <= ACCESS_LEVEL_DISABLED` (lib/Users.php:697, :871). |
| `ACCESS_LEVEL_READ` | **100** | Read-only standard user (db/cats_schema.sql:27). Also the fallback when an account is inactive (lib/Session.php:863-865). |
| `ACCESS_LEVEL_EDIT` | **200** | Add / Edit (db/cats_schema.sql:28). |
| `ACCESS_LEVEL_DELETE` | **300** | Add / Edit / Delete (db/cats_schema.sql:29). Highest level a non-admin user is normally assigned; also the default selected in the add/edit-user dropdown (modules/settings/SettingsUI.php:1163, :1355). |
| `ACCESS_LEVEL_DEMO` | **350** | Special **demo** account level. Assigned at login, *overriding* the stored level, when the site/user is flagged demo and demo mode is enabled (lib/Session.php:850-855). Used by `== ACCESS_LEVEL_DEMO` equality gates to *block* writes while still allowing reads. **Not** a row in the `access_level` table. |
| `ACCESS_LEVEL_SA` | **400** | Site Administrator: "add, edit, remove site users… edit site settings" (db/cats_schema.sql:30). SA always gets EEO visibility (lib/Session.php:844-848). |
| `ACCESS_LEVEL_MULTI_SA` | **450** | Multi-site admin. Used only by two cross-site visibility gates (lib/Candidates.php:2302, lib/JobOrders.php:1237). **Not** a row in the `access_level` table — unreachable via the standard add/edit-user UI. |
| `ACCESS_LEVEL_ROOT` | **500** | Root: "add, edit, remove sites; assign SA status" (db/cats_schema.sql:31). The seeded `admin` user is ROOT (db/cats_schema.sql:1092). Required by a handful of gates (e.g. `changeVersionName`, attachments three-directory migration). |

### Ordering and how a guard reads

Guards are plain numeric comparisons of the form:

```php
if ($this->getUserAccessLevel('candidates.delete') < ACCESS_LEVEL_DELETE) { ...deny... }
```

(modules/candidates/CandidatesUI.php:129)

Because the constants are monotonically increasing (-100 < 0 < 100 < … < 500), a guard `< ACCESS_LEVEL_X` blocks **everyone whose level is below X**. A higher level always satisfies a lower-level guard. The one exception is the `DEMO` (350) level, which sits *between* `DELETE` (300) and `SA` (400): a demo user passes any `< READ/EDIT/DELETE` guard (350 ≥ all of those) but fails any `< SA/ROOT` guard. To block demo users from a write that a 300-level user could do, the code uses an **equality** gate `== ACCESS_LEVEL_DEMO` (see anomalies below).

---

## How a guard resolves

There are exactly three call layers, and they all collapse to the same value.

1. **Controller guard** calls `$this->getUserAccessLevel('module.action')` — a thin wrapper on the `UserInterface` base class:

   ```php
   protected function getUserAccessLevel($securedObjectName)
   {
       return $_SESSION['CATS']->getAccessLevel($securedObjectName);
   }
   ```
   (lib/UserInterface.php:429-432)

2. **Session** forwards to the ACL library, passing the user's category list and the user's single stored level as the default:

   ```php
   public function getAccessLevel($securedObjectName)
   {
       return ACL::getAccessLevel($securedObjectName, $this->getUserCategories(), $this->_accessLevel);
   }
   ```
   (lib/Session.php:404-407)

3. **ACL** is supposed to look the secured object up in a category→object→level map. But the map's setup class is commented out:

   ```php
   public static function getAccessLevel($securedObjectName, $userCategories, $defaultAccessLevel)
   {
       if( !class_exists('ACL_SETUP') || empty(ACL_SETUP::$ACCESS_LEVEL_MAP))
       {
           return $defaultAccessLevel;
       }
       ...
   }
   ```
   (lib/ACL.php:52-57)

   `ACL_SETUP` is only ever defined **inside a `/* ... */` comment block** (lib/ACL.php:22-46 — note the opening `/*` at line ~21 and closing `*/` at line 46). It is therefore never declared, `class_exists('ACL_SETUP')` is `false`, and the function returns the default on the very first line. The dotted-key resolution loop (lib/ACL.php:71-83) and the per-category lookups are dead code.

**Net effect:** `getUserAccessLevel('candidates.duplicates')`, `getUserAccessLevel('candidates.add')`, and `getUserAccessLevel(ACL::SECOBJ_ROOT)` all return the **same** number — `$_SESSION['CATS']->_accessLevel`, the user's one access level. The dotted key string is passed in, ignored by the dormant ACL, and discarded. `getUserCategories()` (the user's `categories` column, lib/Session.php:561-564, 823) is likewise consulted by the dead code only.

> So in the matrix below, the "Guard key" column documents the *string literal the code passes* (useful for grepping and for understanding the original intent), but the **"Required level" is what actually gates access**, because the key never changes the returned value.

### Where the single `_accessLevel` comes from

Set once at login from `user.access_level` (lib/Session.php:821, :1026), then possibly overridden:

- Stored value copied into both `_accessLevel` and `_realAccessLevel` (lib/Session.php:821-822).
- **Demo override:** if the site row `is_demo` and user `is_demo` and `ENABLE_DEMO_MODE` and the request is not from `127.0.0.1`, the level is forced to `ACCESS_LEVEL_DEMO` (350) regardless of the stored value (lib/Session.php:850-856).
- **Inactive account:** forced down to `READ` (lib/Session.php:863-866).
- **Deleted account:** forced down to `DISABLED` (lib/Session.php:869-871).
- `setRealAccessLevel()` can only *lower* `_accessLevel`, never raise it (lib/Session.php:422-430).

### SA vs MULTI_SA vs ROOT in practice

- **SA (400)** is the practical administrator. It passes every per-action guard except the few `< ROOT` ones, can manage users and site settings (most settings writes are `< ACCESS_LEVEL_SA`), and unlocks the SA-only features (candidate de-dupe, administrative hide/show, "show other users' calendars", `emailCandidates` send stage).
- **MULTI_SA (450)** is *only* meaningful to two gates that decide cross-site list visibility: `lib/Candidates.php:2302` (`getAccessLevel('candidates') < ACCESS_LEVEL_MULTI_SA`) and `lib/JobOrders.php:1237` (`getAccessLevel(SECOBJ_ROOT) < ACCESS_LEVEL_MULTI_SA`). It is **not a selectable level** in the user UI (no `access_level` row), so in a stock install no user ever has it; the gates effectively always restrict to the user's own site.
- **ROOT (500)** adds the handful of root-only operations: `settings.administration.changeVersionName` (modules/settings/SettingsUI.php:2614), the attachments three-directory migration (modules/install/ajax/attachmentsToThreeDirectory.php:53), and the slave-DB user query restriction `CATS_SLAVE && accessLevel < ACCESS_LEVEL_ROOT` (lib/Users.php:877). The seeded `admin` is ROOT.
- **DISABLED (0)** and **DELETED (-100)** cannot log in at all (`accessLevel <= ACCESS_LEVEL_DISABLED` → login failure, lib/Users.php:697).

---

## The matrix

One row per dispatch-table action across all 21 web modules. **Required level** is the actual numeric gate (the constant in the `<`/`==` comparison). **Guard key** is the literal string passed to `getUserAccessLevel()` (decorative — see above). Actions with no `getUserAccessLevel` check are `(none — auth only)` (still require a logged-in session via the base `UserInterface`/`handleRequest` auth flow) or `(public)` where `_authenticationRequired = false`.

Source of truth: each controller's `handleRequest()` switch. Cross-checked against `docs/_evidence/acl-summary.md` and `docs/modules/*.md`.

### candidates — modules/candidates/CandidatesUI.php

| Action | Guard key | Required level | Source |
|---|---|---|---|
| show | candidates.show | READ (100) | CandidatesUI.php:89 |
| add | candidates.add | EDIT (200) | CandidatesUI.php:97 |
| edit | candidates.edit | EDIT (200) | CandidatesUI.php:113 |
| delete | candidates.delete | DELETE (300) | CandidatesUI.php:129 |
| search | candidates.search | READ (100) | CandidatesUI.php:144 |
| viewResume | candidates.viewResume | READ (100) | CandidatesUI.php:162 |
| considerForJobSearch | **candidates.search** | EDIT (200) | CandidatesUI.php:176 |
| addToPipeline | **pipelines.addToPipeline** | EDIT (200) | CandidatesUI.php:191 |
| addCandidateTags | candidates.addCandidateTags | EDIT (200) | CandidatesUI.php:206 |
| addActivity | **pipelines.addActivity** | EDIT (200) | CandidatesUI.php:222 |
| changeStatus | **pipelines.changeStatus** | EDIT (200) | CandidatesUI.php:239 |
| removeFromPipeline | **pipelines.removeFromPipeline** | DELETE (300) | CandidatesUI.php:256 |
| addEditImage | candidates.addEditImage | EDIT (200) | CandidatesUI.php:271 |
| createAttachment | candidates.createAttachment | EDIT (200) | CandidatesUI.php:288 |
| administrativeHideShow | **candidates.hidden** | SA (400) | CandidatesUI.php:308 |
| deleteAttachment | candidates.deleteAttachment | DELETE (300) | CandidatesUI.php:324 |
| savedLists | candidates.savedLists | READ (100) | CandidatesUI.php:341 |
| emailCandidates | candidates.emailCandidates | **SA (400)** (two-stage: READ at :350, then SA at :354 — effective SA) | CandidatesUI.php:350,354 |
| show_questionnaire | candidates.show_questionnaire | READ (100) | CandidatesUI.php:362 |
| linkDuplicate / merge / mergeInfo / removeDuplicity / addDuplicates | **candidates.duplicates** | SA (400) | CandidatesUI.php:370,379,387,403,418 |
| listByView / default | **candidates.list** | READ (100) | CandidatesUI.php:435 |

Inline (non-dispatch) gates: `candidates.hidden < SA` blocks setting/viewing admin-hidden records (CandidatesUI.php:614, :1203); `candidates.priviledgedUser < DEMO` (CandidatesUI.php:795); `candidates.emailCandidates == DEMO` blocks demo email send (CandidatesUI.php:1236, :3519).

### joborders — modules/joborders/JobOrdersUI.php

| Action | Guard key | Required level | Source |
|---|---|---|---|
| show | joborders.show | READ (100) | acl-summary; JobOrdersUI.php |
| addJobOrderPopup | **joborders.add** | EDIT (200) | acl-summary |
| add | joborders.add | EDIT (200) | acl-summary |
| edit | joborders.edit | EDIT (200) | acl-summary |
| delete | joborders.delete | DELETE (300) | acl-summary |
| search | joborders.search | READ (100) | acl-summary |
| addActivity | **pipelines.addActivity** | EDIT (200) | acl-summary |
| changeStatus | **pipelines.changeStatus** | EDIT (200) | acl-summary |
| considerCandidateSearch | joborders.considerCandidateSearch | EDIT (200) | acl-summary |
| addToPipeline | **pipelines.addToPipeline** | EDIT (200) | acl-summary |
| addCandidateModal | **candidates.add** | EDIT (200) | acl-summary |
| removeFromPipeline | **pipelines.removeFromPipeline** | DELETE (300) | acl-summary |
| createAttachment | joborders.createAttachment | EDIT (200) | acl-summary |
| deleteAttachment | joborders.deleteAttachment | DELETE (300) | acl-summary |
| administrativeHideShow | joborders.administrativeHideShow | SA (400) | acl-summary |
| listByView / default | **joborders.list** | READ (100) | acl-summary |

Cross-site visibility for the job-order list is gated by `getAccessLevel(SECOBJ_ROOT) < ACCESS_LEVEL_MULTI_SA` (lib/JobOrders.php:1237).

### companies — modules/companies/CompaniesUI.php

| Action | Guard key | Required level | Source |
|---|---|---|---|
| show | companies.show | READ (100) | acl-summary |
| internalPostings | companies.internalPostings | READ (100) | acl-summary |
| add | companies.add | EDIT (200) | acl-summary |
| edit | companies.edit | EDIT (200) | acl-summary |
| delete | companies.delete | DELETE (300) | acl-summary |
| search | companies.search | READ (100) | acl-summary |
| createAttachment | companies.createAttachment | EDIT (200) | acl-summary |
| deleteAttachment | companies.deleteAttachment | DELETE (300) | acl-summary |
| listByView / default | companies.list | READ (100) | acl-summary |

Inline DEMO gates: `companies.show == DEMO` hides the item-history flag (CompaniesUI.php:482); `companies.email == DEMO` blocks emailing on edit (CompaniesUI.php:703).

### contacts — modules/contacts/ContactsUI.php

| Action | Guard key | Required level | Source |
|---|---|---|---|
| show | contacts.show | READ (100) | acl-summary |
| add | contacts.add | EDIT (200) | acl-summary |
| edit | contacts.edit | EDIT (200) | acl-summary |
| delete (POST-only) | contacts.delete | DELETE (300) | acl-summary |
| search | contacts.search | READ (100) | acl-summary |
| addActivityScheduleEvent | contacts.addActivityScheduleEvent | EDIT (200) | acl-summary |
| showColdCallList | contacts.showColdCallList | READ (100) | acl-summary |
| downloadVCard | contacts.downloadVCard | READ (100) | acl-summary |
| listByView / default | contacts.list | READ (100) | acl-summary |

Inline DEMO gates: `contacts.show == DEMO` (item history); `contacts.emailContact == DEMO` (email gate).

### activity — modules/activity/ActivityUI.php

| Action | Guard key | Required level | Source |
|---|---|---|---|
| viewByDate | — | (none — auth only) | acl-summary |
| listByViewDataGrid | — | (none — auth only) | acl-summary |
| default | — | (none — auth only) | acl-summary |

No `getUserAccessLevel` guards in this controller. Related AJAX endpoints **do** guard: `ajax/editActivity.php` → `contacts.editActivity` @ EDIT (200); `ajax/deleteActivity.php` → `contacts.deleteActivity` @ EDIT (200). Note the cross-module key `contacts.*` for activity AJAX.

### calendar — modules/calendar/CalendarUI.php

| Action | Guard key | Required level | Source |
|---|---|---|---|
| addEvent | calendar.addEvent | EDIT (200) | acl-summary |
| editEvent | calendar.editEvent | EDIT (200) for own; SA (400) to edit others' (via `calendar.show`) | acl-summary |
| deleteEvent | calendar.deleteEvent | DELETE (300) for own; SA (400) for others' | acl-summary |
| dynamicData | — | (none in handler; tab-level READ on `calendar`) | acl-summary |
| showCalendar / default | — | (no hard gate; tab READ on `calendar`; `calendar.show >= SA` enables "show other users") | acl-summary |

### home — modules/home/HomeUI.php

| Action | Guard key | Required level | Source |
|---|---|---|---|
| home / quickSearch / addSavedSearch / deleteSavedSearch | — | (none — auth only) | acl-summary; HomeUI.php |

### lists — modules/lists/ListsUI.php

| Action | Guard key | Required level | Source |
|---|---|---|---|
| listByView / showList / quickActionAddToListModal / addToListFromDatagridModal / removeFromListDatagrid / deleteStaticList | — | (none — auth only, web controller) | acl-summary |
| (AJAX) addToLists / deleteList / editListName / newList | lists | EDIT (200) via `getAccessLevel('lists')` | acl-summary |

### login — modules/login/LoginUI.php  *(public)*

`_authenticationRequired = false` (LoginUI.php:43).

| Action | Guard key | Required level | Source |
|---|---|---|---|
| attemptLogin / forgotPassword / noCookiesModal / showLoginForm | — | (public) | acl-summary; LoginUI.php:43 |

### settings — modules/settings/SettingsUI.php

Pattern: most **POST writes** require `< SA` (400); paired **GET/reads** require `< DEMO` (350); a few are ROOT. Representative rows (all `getUserAccessLevel('...') < LEVEL`):

| Action / key | Required level | Source |
|---|---|---|
| tags | SA (400) (unless careerportal category) | SettingsUI.php:234 |
| changePassword | `== DEMO` block | SettingsUI.php:250 |
| newInstallPassword | SA (400) | SettingsUI.php:261 |
| forceEmail | SA (400) | SettingsUI.php:276 |
| newSiteName / upgradeSiteName | SA (400) | SettingsUI.php:291,306,321 |
| manageUsers | DEMO (350) | SettingsUI.php:336 |
| professional | DEMO (350) | SettingsUI.php:344 |
| previewPage / previewPageTop | READ (100) | SettingsUI.php:352,360 |
| showUser | DEMO (350) | SettingsUI.php:368 |
| addUser.POST | SA (400) | SettingsUI.php:379 |
| addUser.GET | DEMO (350) | SettingsUI.php:387 |
| editUser.POST | SA (400) | SettingsUI.php:400 |
| editUser.GET | DEMO (350) | SettingsUI.php:408 |
| createBackup / deleteBackup | SA (400) | SettingsUI.php:418,426 |
| customizeExtraFields.POST / .GET | SA / DEMO | SettingsUI.php:444,452 |
| customizeCalendar.POST / .GET | SA / DEMO | SettingsUI.php:464,472 |
| reports | DEMO (350) | SettingsUI.php:481 |
| emailSettings.POST / .GET | SA / DEMO | SettingsUI.php:499,507 |
| careerPortal* (Questionnaire/TemplateEdit/Settings/Tweak) | DEMO read / SA write (careerportal category exempt) | SettingsUI.php:516-611 |
| eeo.POST / .GET | SA / DEMO | SettingsUI.php:594,602 |
| deleteUser | SA (400) | SettingsUI.php:628,759 |
| emailTemplates.POST / .GET | SA / DEMO (careerportal exempt) | SettingsUI.php:646,654 |
| aspLocalization | SA (400) | SettingsUI.php:663 |
| loginActivity / viewItemHistory | DEMO (350) | SettingsUI.php:674,685 |
| addUser / deleteUser / checkKey / localization / firstTimeSetup / license / password / siteName / import / website | SA (400) | SettingsUI.php:740-885 |
| setEmail | READ (100) | SettingsUI.php:857 |
| administration.POST / .GET | SA / DEMO (careerportal exempt) | SettingsUI.php:896,904 |
| myProfile | READ (100) | SettingsUI.php:937 |
| administration (menu) | shown if ROOT or `== DEMO` | SettingsUI.php:2428 |
| administration.localization / systemInformation / changeSiteName | SA (400) | SettingsUI.php:2469,2493,2595,2628 |
| administration.changeVersionName | **ROOT (500)** | SettingsUI.php:2614 |
| (two-stage write guard) `_realAccessLevel < SA` | SA (400) | SettingsUI.php:948,968 |

`ajax_tags_add/del/upd` have **no** guard (none); `addEmailTemplate`/`deleteEmailTemplate` and `ajax/backup.php` enforce SA inside the handler. (acl-summary; docs/modules/settings.md)

### reports — modules/reports/ReportsUI.php

| Action | Guard key | Required level | Source |
|---|---|---|---|
| graphView / generateJobOrderReportPDF / showSubmissionReport / showPlacementReport / customizeJobOrderReport / reports / default | reports.* | READ (100) | acl-summary |
| customizeEEOReport / generateEEOReportPreview | reports.* | READ (100) **+ `canSeeEEOInfo()`** | acl-summary |

`canSeeEEOInfo()` is a separate boolean flag (`user.can_see_eeo_info`), forced true for SA+ (lib/Session.php:416-418, 844-848).

### import — modules/import/ImportUI.php

| Action | Guard key | Required level | Source |
|---|---|---|---|
| revert | import.* | EDIT (200) | acl-summary |
| viewerrors / viewpending / importSelectType / whatIsBulkResumes / showMassImport / massImportDocument / massImportEdit | — | (none — auth only) | acl-summary |
| importUploadFile | import.* | EDIT (200) | acl-summary |
| massImport | import.* | EDIT (200) | acl-summary |
| importBulkResumes | import.* | SA (400) | acl-summary |
| deleteBulkResumes | import.* | SA (400) | acl-summary |
| import | import.* | EDIT (200) | acl-summary |
| onImportFieldsDelimited | import.* | EDIT (200); foreign-field path → SA (400) | acl-summary |

### careers — modules/careers/CareersUI.php  *(public)*

`_authenticationRequired = false` (CareersUI.php:53).

| Action | Guard key | Required level | Source |
|---|---|---|---|
| all `p=` / `pa=` branches | — | (public) — gated only by the career-portal `enabled` setting | acl-summary; CareersUI.php:53 |

### graphs — modules/graphs/GraphsUI.php  *(public)*

`_authenticationRequired = false` (GraphsUI.php:48). No `getUserAccessLevel` calls.

| Action | Guard key | Required level | Source |
|---|---|---|---|
| testGraph / wordVerify / jobOrderReportGraph / generic / genericPie | — | (public) | acl-summary |
| activity / newCandidates / newJobOrders / newSubmissions / miniPlacementStatistics / miniJobOrderPipeline | — | (none) but require `isLoggedIn()` | acl-summary |

### export — modules/export/ExportUI.php

| Action | Guard key | Required level | Source |
|---|---|---|---|
| export / exportByDataGrid / default | — | (none — auth only) | acl-summary |

Note: `Export::getFormattedOutput` only supports `DATA_ITEM_CANDIDATE`.

### queue — modules/queue/QueueUI.php

| Action | Guard key | Required level | Source |
|---|---|---|---|
| default | — | (none — auth only); no-op controller | acl-summary |

Real queue logic lives in `lib/QueueProcessor.php` + `QueueCLI.php` (CLI, outside the web ACL surface).

### rss — modules/rss/RssUI.php  *(public)*

`_authenticationRequired = false` (RssUI.php:49).

| Action | Guard key | Required level | Source |
|---|---|---|---|
| jobOrders / default | — | (public) | acl-summary; RssUI.php:49 |

### xml — modules/xml/XmlUI.php  *(public)*

`_authenticationRequired = false` (XmlUI.php:54).

| Action | Guard key | Required level | Source |
|---|---|---|---|
| jobOrders / default | — | (public) + `allowBrowse == 1` career-portal setting gate | acl-summary; XmlUI.php:54 |

### wizard — modules/wizard/WizardUI.php  *(public)*

`_authenticationRequired = false` (WizardUI.php:46). No guards.

| Action | Guard key | Required level | Source |
|---|---|---|---|
| ajax_getPage / show | — | (public) | acl-summary; WizardUI.php:46 |

Note: `ajax_getPage` `eval()`s session-stored PHP per page (see doc 20).

### install — modules/install/CATSUI.php (+ ajax)  *(public)*

`_authenticationRequired = false` (CATSUI.php:36); `handleRequest()` is empty — class name is `CATSUI`, not `InstallUI`.

| Action | Guard key | Required level | Source |
|---|---|---|---|
| (25 install actions in `modules/install/ajax/ui.php`, via `install:ui`) | — | (public) — gated only by the `INSTALL_BLOCK` file | acl-summary |
| attachmentsReindex | SECOBJ_ROOT | SA (400) — only when `INSTALL_BLOCK` exists | acl-summary |
| attachmentsToThreeDirectory | SECOBJ_ROOT | **ROOT (500)** | attachmentsToThreeDirectory.php:53 |

### attachments — modules/attachments/AttachmentsUI.php

| Action | Guard key | Required level | Source |
|---|---|---|---|
| getAttachment / default | — | (none — auth only) | acl-summary |

No `getUserAccessLevel`. Retrieval is gated by session + `md5(directoryName) == directoryNameHash`, and passes `site_id = false` (cross-site read gap — see doc 20).

---

## Guard-key anomalies

These are the cases where the literal "guard key" string differs from the action name or is reused across modules. **Because the ACL map is dormant, none of these change behavior today** — they all resolve to the user's single level — but they document original intent and are the seams that would break if anyone re-enabled `ACL_SETUP`.

1. **`pipelines.*` keys used by candidates AND joborders.** The pipeline actions `addToPipeline`, `addActivity`, `changeStatus`, `removeFromPipeline` are guarded under `pipelines.*` keys in both `CandidatesUI.php` (:191,:222,:239,:256) and `JobOrdersUI.php` — even though neither module is "pipelines". There is no `pipelines` module controller; the key namespace exists only as a string. Intended as a shared permission for pipeline operations.
2. **`contacts.*` keys used by the activity AJAX endpoints.** `ajax/editActivity.php` and `ajax/deleteActivity.php` guard `contacts.editActivity` / `contacts.deleteActivity`, not `activity.*` (acl-summary, activity section). The activity controller itself has no guards.
3. **`candidates.add` used by joborders.** `JobOrdersUI.php` action `addCandidateModal` guards on `candidates.add`, borrowing the candidates namespace.
4. **`candidates.search` reused for a write.** `considerForJobSearch` guards on `candidates.search` but requires **EDIT** (200), not the READ that the `search` action requires (CandidatesUI.php:176 vs :144) — same key, different required level depending on action.
5. **Key ≠ action name within candidates:** `administrativeHideShow` → `candidates.hidden`; the duplicate-handling family (`linkDuplicate`/`merge`/`mergeInfo`/`removeDuplicity`/`addDuplicates`) all → `candidates.duplicates`; list actions → `candidates.list`.
6. **`SECOBJ_ROOT` (empty-string key) for global gates.** `''` is `ACL::SECOBJ_ROOT` (lib/ACL.php:17) and is used for "whole system" checks: `JobOrders.php:1237`, `Candidates.php:2302`, `attachmentsToThreeDirectory.php:53`, and the SA/ROOT menu check in `SettingsUI.php:2287`. Under a live ACL this would be the catch-all root object; today it just returns the user level.
7. **Two-stage guards.** `candidates.emailCandidates` is checked at READ (:350) and again at SA (:354) within the same action, so the effective requirement is **SA**. Settings `addUser`/`editUser` are split into `.GET` (DEMO) and `.POST` (SA) keys for the same action.
8. **Equality (`== DEMO`) gates vs. `<` gates.** Most guards use `<`. A distinct set use `== ACCESS_LEVEL_DEMO` specifically to block demo users while *not* blocking 300-level users (e.g. `candidates.emailCandidates == DEMO` at :1236; `companies.email == DEMO`; `contacts.emailContact == DEMO`; `settings.changePassword == DEMO`). These are the only guards that single out exactly the 350 band.

---

## How access levels are assigned

- **Storage:** `user.access_level INT NOT NULL DEFAULT 100` (db/cats_schema.sql:1060). One integer per user; there is no per-module or per-object permission storage. The seeded `admin` user is `500` (ROOT) (db/cats_schema.sql:1092); the automated `cats@rootadmin` system user is `0` / `cantlogin` (db/cats_schema.sql:1093).
- **The `access_level` reference table** holds only the *selectable* levels: 0, 100, 200, 300, 400, 500 (db/cats_schema.sql:26-31). **DEMO (350), MULTI_SA (450), and DELETED (-100) are not rows**, so the add/edit-user dropdown (`Users::getAccessLevels()`, lib/Users.php:561-575, fed to the template at SettingsUI.php:1056, :1130) can never assign them. DEMO and DELETED are applied at runtime by Session; MULTI_SA is effectively unreachable in stock installs.
- **Add user** (`settings` POST → `onAddUser`): `accessLevel` read from POST (SettingsUI.php:1188), license-checked (no add allowed above READ when out of allotments, :1197), then `Users::add(... $accessLevel ...)` (SettingsUI.php:1237-1239). The whole `addUser.POST` path is gated `< ACCESS_LEVEL_SA` (SettingsUI.php:379), so **only an SA (or above) can create users**, and the default dropdown selection is `ACCESS_LEVEL_DELETE` (SettingsUI.php:1163).
- **Edit user** (`onEditUser`): `accessLevel` taken from POST when present (SettingsUI.php:1377-1383); if the field is absent it falls back to `$this->_realAccessLevel` (SettingsUI.php:1421). License logic prevents downgrading the last paid seat (SettingsUI.php:1304). Gated `editUser.POST < SA` (SettingsUI.php:400). EEO visibility (`eeoIsVisible`) is set alongside the level (SettingsUI.php:1441).
- **Site SA:** any user with `access_level >= 400` is a site administrator for their `site_id`; SA gates the user-management and settings-write surface. The SA/ROOT settings menu is shown when `getAccessLevel(SECOBJ_ROOT) >= ACCESS_LEVEL_SA` (SettingsUI.php:2287, 2296).
- **The demo account (DEMO, 350):** never assigned via the table. Granted at login when the site row and user row are both `is_demo`, `ENABLE_DEMO_MODE` is on, and the request is not from localhost (lib/Session.php:850-856). Demo users get read access everywhere (`350 ≥ READ/EDIT/DELETE`) but are blocked from SA features and from the specific `== DEMO` write gates. Column preferences are not persisted for demo users (lib/Session.php:874).

---

## Unguarded / public surface summary

**No per-action ACL checks (logged-in users only — any level that can log in, i.e. ≥ READ):**

- **activity** (ActivityUI.php) — controller has no guards; only the activity *AJAX* endpoints check `contacts.editActivity`/`contacts.deleteActivity` @ EDIT.
- **home** (HomeUI.php) — dashboard, quick search, saved searches — none.
- **lists** (ListsUI.php) — web controller none; only list *AJAX* mutations check `lists` @ EDIT. So the web "delete static list" / "remove from list" paths are reachable by any logged-in user.
- **export** (ExportUI.php) — none. Any logged-in user can export candidate data.
- **queue** (QueueUI.php) — none (no-op web controller).
- **attachments** (AttachmentsUI.php) — none; relies on the hash check and (per doc 20) reads with `site_id = false`, a cross-site read gap.

**Public (`_authenticationRequired = false` — reachable with no session):**

- **login** (LoginUI.php:43) — by design.
- **careers** (CareersUI.php:53) — public job-board front end; gated by the career-portal `enabled` setting.
- **rss** (RssUI.php:49) — public job feed.
- **xml** (XmlUI.php:54) — public job XML; additionally gated by `allowBrowse == 1`.
- **graphs** (GraphsUI.php:48) — tier-1 graph actions are fully public; tier-2 still call `isLoggedIn()` internally.
- **wizard** (WizardUI.php:46) — public; `ajax_getPage` `eval()`s session PHP (high-risk; see doc 20).
- **install** (CATSUI.php:36) — public; real actions in `modules/install/ajax/ui.php` gated only by the `INSTALL_BLOCK` file existing on disk.

> **Security cross-reference:** the unguarded `export`, the web-path `lists` mutations, the `attachments` cross-site read, the public `wizard` `eval()`, and the file-gated `install` surface are all analyzed as findings in **doc 20 (Security)**. The dormant ACL is itself a defense-in-depth gap: every guard collapses to one number, so a single mis-set `user.access_level` (e.g. an SA where READ was intended) grants the full SA surface with no object-level backstop.

---

## Source evidence

- `constants.php:74-82` — the 9 `ACCESS_LEVEL_*` constants and their values.
- `lib/ACL.php:12-92` — `ACL::getAccessLevel()`; `ACL_SETUP` map commented out (lines ~21-46), so `class_exists('ACL_SETUP')` is false and the function returns `$defaultAccessLevel` (lib/ACL.php:54-57). Dotted-key loop (71-83) is dead code.
- `lib/Session.php:404-407` — `Session::getAccessLevel()` forwards to ACL with the user's single `_accessLevel` as default.
- `lib/Session.php:46-47, 821-822` — `_accessLevel` / `_realAccessLevel` set from `user.access_level` at login.
- `lib/Session.php:844-871` — runtime overrides: SA→EEO, demo→350, inactive→READ, deleted→DISABLED.
- `lib/Session.php:561-564` — `getUserCategories()` (consumed only by the dead ACL map).
- `lib/UserInterface.php:429-432` — `getUserAccessLevel()` wrapper.
- `lib/Users.php:561-575` — `getAccessLevels()` (only 0/100/200/300/400/500); `:697`, `:871`, `:877` — login/visibility level checks.
- `modules/settings/SettingsUI.php` — addUser/editUser flow (`:379`, `:400`, `:1163`, `:1188`, `:1237`, `:1355`, `:1377-1421`); full settings guard list (`:234`-`:2628`).
- `db/cats_schema.sql:16-31` — `access_level` table + seeded level rows; `:1060` `user.access_level` column; `:1092-1093` seeded users.
- Per-controller guards: `modules/candidates/CandidatesUI.php:89-435` (verified directly); other modules per `docs/_evidence/acl-summary.md` and `docs/modules/*.md`.
- Public modules `_authenticationRequired = false`: LoginUI.php:43, CareersUI.php:53, GraphsUI.php:48, XmlUI.php:54, RssUI.php:49, WizardUI.php:46, CATSUI.php:36.

---

## Unverified / open questions

- **Per-module guard line numbers** for joborders, companies, contacts, calendar, import, reports, lists-AJAX, and settings-AJAX rows marked "acl-summary" were taken from `docs/_evidence/acl-summary.md` and the per-module docs, not re-confirmed line-by-line in this pass. The candidates rows and all `_authenticationRequired` / constant / ACL-mechanism / settings-guard claims were verified directly against source. The required-level values and guard keys for those modules match the per-module agent extractions but should be spot-checked against the controllers if exact line citations are needed.
- **`access_level` table extra rows in some installs:** some historical schemas / upgrade scripts may add a 350 ("demo") or 450 row. The stock `db/cats_schema.sql` does **not** (verified), so DEMO/MULTI_SA are runtime-only / unreachable there; a site that hand-inserted those rows could surface them in the dropdown — not verified across all `db/upgrade-*.sql`.
- **Career-portal category exemptions** (`hasUserCategory('careerportal')`) bypass several settings SA gates. The category itself is a free-text `user.categories` value; how it gets assigned was not traced here.
- **AJAX endpoint coverage:** this matrix indexes the per-module dispatch switches plus the AJAX endpoints named in the evidence (activity, lists, settings backup/templates, install). A full sweep of every file under `modules/*/ajax/` and top-level `ajax.php` routing was not performed; additional standalone AJAX scripts may carry their own (or missing) guards.
