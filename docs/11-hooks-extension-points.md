# 11 — Hooks & Extension Points

OpenCATS ships a single, lightweight extension mechanism: **hooks**. A hook is a
named slot at a point in the code where a module can inject PHP source that is
executed inline via `eval()`. There is no event bus, no listener registry, and
no plugin manifest beyond what each module declares. This document describes how
the mechanism works, how it is wired up at boot, and enumerates every hook key
that actually fires in this codebase.

All claims below are cited to real `file:line` locations in this repository.

---

## How hooks work

The entire hook runtime is one class, `Hooks`, with one static method. Here is
the real implementation (`lib/Hooks.php:52-72`):

```php
public static function get($hookName)
{
    if (!isset($_SESSION['hooks']))
    {
        return 'return true;';
    }

    $hooks = @$_SESSION['hooks'];

    $hookCommands = '';

    if (isset($hooks[$hookName]))
    {
        foreach ($hooks[$hookName] as $value)
        {
            $hookCommands .= $value . "\n";
        }
    }

    return $hookCommands . ' return true;';
}
```

Key facts about this method:

- `Hooks::get($hookName)` does **not** execute anything. It **returns a string of
  PHP source code**. The caller is responsible for running it.
- The returned string is the concatenation of every code fragment registered
  under `$_SESSION['hooks'][$hookName]` (multiple modules can register for the
  same key — the fragments are joined with newlines), followed by the literal
  `' return true;'` terminator (`lib/Hooks.php:67,71`).
- If no module registered for that key — or if `$_SESSION['hooks']` is not set
  at all — the method returns just `'return true;'` (`lib/Hooks.php:56,71`). This
  is the no-op default: an unhooked slot evaluates to `true` and execution
  continues normally.

### The call-site idiom

Call sites run the returned string with PHP's `eval()`. The dominant pattern is
a guarded short-circuit:

```php
if (!eval(Hooks::get('INDEX_LOAD_HOME'))) return;   // index.php:199
```

Because the default return is `return true;`, the `eval()` evaluates to `true`
and the `if (!...)` body is skipped — the handler proceeds normally. A registered
hook fragment, however, can run `return false;` to make `eval()` yield `false`,
which causes the host method to `return` early. **This is how a hook
short-circuits the handler it is attached to** — see the `HOME` hook in the
`SettingsUI` example below, which calls `CATSUtility::transferRelativeURI(...)`
then `return false;` to redirect away from the normal home page
(`modules/settings/SettingsUI.php:102-108`).

A minority of call sites use bare `eval()` with no guard, when the slot exists
only to *emit output* rather than to alter control flow. Examples:

- `eval(Hooks::get('UPDATE_SPHINX_DELTA'));` (`lib/Attachments.php:160`)
- `eval(Hooks::get('TEMPLATE_UTILITY_PRINT_FOOTER'));` (`lib/TemplateUtility.php:858`)
- `eval(Hooks::get('XML_FEED_SUBMISSION_SETTINGS_HEADERS'));` (`modules/settings/SettingsUI.php:50`)
- `eval(Hooks::get('XML_FEED_SUBMISSION_SETTINGS_BODY'));` (`modules/settings/SettingsUI.php:1862`)

Template (`.tpl`) files use the same idiom inline, e.g.
`<?php eval(Hooks::get('CANDIDATE_TEMPLATE_ABOVE_FREEFORM')); ?>`
(`modules/candidates/Add.tpl:132`).

---

## How hooks are registered

Hook code is contributed by **modules**, not configured in a settings file. The
flow is:

1. **A module declares its hooks.** Every UI module extends `UserInterface`,
   which provides a protected `$_hooks = array()` (`lib/UserInterface.php:51`) and
   a getter `getHooks()` that simply returns it (`lib/UserInterface.php:94-97`):

   ```php
   public function getHooks()
   {
       return $this->_hooks;
   }
   ```

   A module populates `$this->_hooks`, conventionally from a `defineHooks()`
   method called in its constructor. `SettingsUI` does exactly this — its
   constructor sets `$this->_hooks = $this->defineHooks();`
   (`modules/settings/SettingsUI.php:84`).

2. **`_refreshModuleList()` scans every module and collects the hooks.** During
   bootstrap, `ModuleUtility::getModules()` calls `_refreshModuleList()`
   (`lib/ModuleUtility.php:147-175,193`). That method opens `MODULES_PATH`, walks
   each module directory, `include_once`s the `*UI.php` file, instantiates the
   class, and calls `getHooks()` on it (`lib/ModuleUtility.php:224-282`). The
   collected fragments are merged into a single array, keyed by hook name, with
   each module's fragment appended to that key's list
   (`lib/ModuleUtility.php:275-279`):

   ```php
   $moduleHooks = $module->getHooks();
   foreach ($moduleHooks as $name => $data)
   {
       $hooks[$name][] = $data;
   }
   ```

3. **The merged map is stored in the session and cache.** The completed `$hooks`
   array is assigned to `$_SESSION['hooks']` (`lib/ModuleUtility.php:295`) — the
   exact structure that `Hooks::get()` later reads. When `CACHE_MODULES` is on,
   it is also serialized to `modules.cache` (`lib/ModuleUtility.php:304-308`). On
   subsequent requests, if `modules.cache` exists (and no maintenance is forced),
   `_refreshModuleList()` short-circuits by reloading the cached hooks straight
   into `$_SESSION['hooks']` without re-scanning (`lib/ModuleUtility.php:207-214`).

### Concrete example: `SettingsUI::defineHooks()`

`modules/settings/SettingsUI.php:87-128` is the canonical real-world example of a
module injecting per-key code. It returns an associative array `key => PHP source
string`. Highlights:

- `'TEMPLATE_UTILITY_EVALUATE_TAB_VISIBLE'` — hides all tabs except `settings`
  when the user is in career-portal mode (`SettingsUI.php:91-99`).
- `'HOME'` — redirects to `m=settings` and `return false;` (short-circuits the
  home handler) for career-portal users (`SettingsUI.php:102-108`).
- `'SETTINGS_DISPLAY_PROFILE_SETTINGS'` — redirects profile to administration and
  `return false;` for career-portal users (`SettingsUI.php:111-117`).
- A block of `*_HANDLE_REQUEST` keys (`CLIENTS_HANDLE_REQUEST`,
  `CONTACTS_HANDLE_REQUEST`, `CALENDAR_HANDLE_REQUEST`, `JO_HANDLE_REQUEST`,
  `CANDIDATES_HANDLE_REQUEST`, `ACTIVITY_HANDLE_REQUEST`, `REPORTS_HANDLE_REQUEST`)
  that each call `$this->fatal(...)` to deny access to those modules in
  career-portal mode (`SettingsUI.php:120-126`).

Note this is also evidence that the **same hook key can be both *defined* by one
module and *fired* by another** — `SettingsUI` defines `JO_HANDLE_REQUEST`, but
the slot fires inside `JobOrdersUI` (`modules/joborders/JobOrdersUI.php:96`).
This is the principal cross-module wiring path in the codebase.

---

## Where hooks fire — full key inventory

The tables below list **every distinct hook key that appears in a `Hooks::get()`
call** in this repository, grouped by area, with firing `file:line(s)` and a
one-line note on scope. Source of truth: a repo-wide grep of
`Hooks::get('[A-Z_]+')` across `*.php` and `*.tpl` (260 call sites; 229 distinct
keys — see *Source evidence*).

### Bootstrap / index / AJAX / module loading

| Key | Fires at | Scope / what an add-on can do |
|---|---|---|
| `INDEX_LOAD_HOME` | `index.php:199` | Front-controller home dispatch; redirect or replace the landing page. |
| `AJAX_HOOK` | `ajax.php:161` | Wrap/short-circuit the generic AJAX endpoint. |
| `LOAD_MODULE` | `lib/ModuleUtility.php:76` | Intercept module loading/dispatch. |
| `SORT_MODULES_RETURN_POS` / `SORT_MODULES_RETURN_NEG` | `lib/ModuleUtility.php:383` / `:384` | Influence module tab ordering in `_sortModules`. |
| `CATS_UTILITY_GET_INDEX_URL` | `lib/CATSUtility.php:429` | Override the computed index URL. |
| `NEW_VERSION_CHECK_CHECK_FOR_UPDATE` | `lib/NewVersionCheck.php:73` | Hook the update-check routine. |
| `PARSER_ENABLE_CHECK` | `lib/License.php:712` | Gate resume-parser availability. |
| `EXCEPTION_NOTIFY_DEV` | `lib/CommonErrors.php:319` | Notify on uncaught exceptions. |
| `QUEUEERROR_NOTIFY_DEV` | `lib/QueueProcessor.php:92` | Notify on queue-processing errors. |

### Login / session / auth

| Key | Fires at | Scope |
|---|---|---|
| `SHOW_LOGIN_FORM_PRE` / `SHOW_LOGIN_FORM_POST` | `modules/login/LoginUI.php:130` / `:154` | Inject markup/logic around the login form render. |
| `NO_COOKIES_MODAL` | `modules/login/LoginUI.php:171` | Customize the "cookies disabled" modal. |
| `LOGIN_NO_CREDENTIALS` | `modules/login/LoginUI.php:215` | Handle missing-credentials path. |
| `LOGIN_UNSUCCESSFUL` | `modules/login/LoginUI.php:274` | Handle failed-login path. |
| `LICENSE_TERMS` | `modules/login/LoginUI.php:311` | Override license-terms display. |
| `ASP_WIZARD_PAGES` / `ASP_WIZARD_IMPORT` | `modules/login/LoginUI.php:353` / `:368` | Hosted/ASP signup-wizard customization. |
| `LOGGED_IN` | `modules/login/LoginUI.php:385` | Post-authentication action. |
| `LOGGED_IN_MESSAGES` | `modules/login/LoginUI.php:393` | Inject post-login messages. |
| `LOGGED_IN_HOME_PAGE` | `modules/login/LoginUI.php:433` | Choose the landing page after login. |
| `FORGOT_PASSWORD` / `ON_FORGOT_PASSWORD` | `modules/login/LoginUI.php:444` / `:457` | Customize forgot-password display / handling. |
| `TRANSPARENT_LOGIN_POST` | `lib/Session.php:1050` | Post-step in transparent/SSO login. |

### Candidates

| Key | Fires at | Scope |
|---|---|---|
| `CANDIDATES_HANDLE_REQUEST` | `modules/candidates/CandidatesUI.php:83` | Gate/route the candidates module. |
| `CANDIDATE_LIST_BY_VIEW` | `:568` | Hook the list/datagrid view. |
| `CANDIDATE_SHOW` | `:858` | Hook the candidate detail page. |
| `CANDIDATE_ADD` | `:956` | Hook the add-candidate form. |
| `CANDIDATE_ON_ADD_PRE` / `CANDIDATE_ON_ADD_POST` | `:2871` / `:3098` | Straddle candidate creation. |
| `CANDIDATE_EDIT` | `:1261` | Hook the edit form. |
| `CANDIDATE_ON_EDIT_PRE` / `CANDIDATE_ON_EDIT_POST` | `:1458` / `:1512` | Straddle candidate update. |
| `CANDIDATE_DELETE` | `:1532` | Hook candidate deletion. |
| `CANDIDATE_ON_CONSIDER_FOR_JOB_SEARCH` | `:1662` | Hook consider-for-job search. |
| `CANDIDATE_ADD_TO_PIPELINE_PRE` / `CANDIDATE_ADD_TO_PIPELINE_POST` / `CANDIDATE_ADD_TO_PIPELINE_POST_IND` | `:1726` / `:1763` / `:1760` | Straddle pipeline add (POST_IND fires per candidate). |
| `CANDIDATE_ADD_ACTIVITY_CHANGE_STATUS` | `:1816`, `:1952` | Hook activity/status-change flows. |
| `CANDIDATE_ON_ADD_ACTIVITY_CHANGE_STATUS_PRE` / `..._POST` | `:3133`,`:3482` / `:3393`,`:3588` | Straddle activity+status change. |
| `CANDIDATE_REMOVE_FROM_PIPELINE_PRE` / `..._POST` | `:2083` / `:2088` | Straddle pipeline removal. |
| `CANDIDATE_SEARCH` | `:2103` | Hook the candidate search UI. |
| `CANDIDATE_ON_SEARCH` | `:2392` | Hook search execution. |
| `CANDIDATE_VIEW_RESUME` | `:2447` | Hook resume viewing. |
| `CANDIDATE_ADD_EDIT_IMAGE` | `:2469` | Hook image add/edit. |
| `CANDIDATE_ON_ADD_EDIT_IMAGE_PRE` / `..._POST` | `:2492` / `:2506` | Straddle image upload. |
| `CANDIDATE_CREATE_ATTACHMENT` | `:2529` | Hook attachment creation UI. |
| `CANDIDATE_ON_CREATE_ATTACHMENT_PRE` / `..._POST` | `:2567`,`:2943`,`:3018`,`:3054` / `:2592`,`:2969`,`:3042`,`:3074` | Straddle attachment creation (multiple upload paths). |
| `CANDIDATE_ON_DELETE_ATTACHMENT_PRE` / `..._POST` | `:2622` / `:2627` | Straddle attachment deletion. |
| `DUPLICATE_ON_LINK_DUPLICATES` | `:3810` | Hook duplicate-candidate linking. |
| `CANDIDATE_TEMPLATE_ABOVE_FREEFORM` / `CANDIDATE_TEMPLATE_BELOW_FREEFORM` | `modules/candidates/Add.tpl:132`/`:138`; also `modules/companies/Add.tpl:32`/`:38` | Inject markup around the freeform address box (reused on the company add form). |
| `CANDIDATE_TEMPLATE_SHOW_PIPELINE_ACTION` | `modules/candidates/Show.tpl:555` | Inject pipeline-action markup on the show page. |

> Note: `CANDIDATE_ON_ADD_ACTIVITY_CHANGE_STATUS_POST` is also fired from
> `modules/contacts/ContactsUI.php:1601` — a candidate-named key reused in the
> contacts module.

### Job orders (JO)

| Key | Fires at | Scope |
|---|---|---|
| `JO_HANDLE_REQUEST` | `modules/joborders/JobOrdersUI.php:96` | Gate/route the job-orders module. |
| `JO_LIST_BY_VIEW` | `:392` | Hook the list view. |
| `JO_SHOW` | `:578` | Hook the JO detail page. |
| `JO_ADD_MODAL` | `:595` | Hook the add modal. |
| `JO_ADD` | `:727` | Hook the add form. |
| `JO_ON_ADD` / `JO_ON_ADD_POST` | `:833` / `:851` | Straddle JO creation. |
| `JO_EDIT` | `:987` | Hook the edit form. |
| `JO_ON_EDIT_PRE` / `JO_ON_EDIT_POST` | `:1171` / `:1184` | Straddle JO update. |
| `JO_ON_DELETE_PRE` / `JO_ON_DELETE_POST` | `:1204` / `:1214` | Straddle JO deletion. |
| `JO_CONSIDER_CANDIDATE_SEARCH` / `JO_ON_CONSIDER_CANDIDATE_SEARCH` | `:1233` / `:1311` | Hook consider-candidate search UI / execution. |
| `JO_ON_ADD_PIPELINE` / `JO_ON_ADD_PIPELINE_POST` | `:1337` / `:1359` | Straddle adding a candidate to a JO pipeline. |
| `JO_ADD_CANDIDATE_MODAL` / `JO_ON_ADD_CANDIDATE_MODAL` | `:1426` / `:1453` | Hook add-candidate modal / handler. |
| `JO_ADD_ACTIVITY_CHANGE_STATUS` | `:1525`, `:1628` | Hook activity/status-change UI. |
| `JO_ON_ADD_ACTIVITY_CHANGE_STATUS` | `:1645`, `:1664` | Hook activity+status execution. |
| `JO_ON_REMOVE_PIPELINE` / `JO_ON_REMOVE_PIPELINE_POST` | `:1695` / `:1700` | Straddle pipeline removal. |
| `JO_SEARCH` / `JO_ON_SEARCH` | `:1724` / `:1870` | Hook JO search UI / execution. |
| `JO_CREATE_ATTACHMENT` | `:1892` | Hook attachment-create UI. |
| `JO_ON_CREATE_ATTACHMENT_PRE` / `..._POST` | `:1912` / `:1924` | Straddle attachment creation. |
| `JO_ON_DELETE_ATTACHMENT_PRE` / `..._POST` | `:1954` / `:1959` | Straddle attachment deletion. |
| `JO_FORMAT_LIST_BY_VIEW_RESULTS` | `:2082` | Post-process list results. |
| `JO_AJAX_GET_PIPELINE` | `ajax/getPipelineJobOrder.php:180` | Hook the pipeline AJAX endpoint. |
| `JO_GET_EDIT_SQL` | `lib/JobOrders.php:546` | Alter the edit-fetch SQL. |
| `JO_GET_ALL_SQL` | `lib/JobOrders.php:742` | Alter the list-all SQL. |
| `JOBORDERS_DATAGRID_COLUMNS` | `lib/JobOrders.php:1205` | Customize datagrid columns. |
| `JOBORDER_DATAGRID_GETSQL` | `lib/JobOrders.php:1263` | Alter datagrid SQL. |
| `JOBORDERS_DATAGRID_DEFAULTS` | `modules/joborders/dataGrids.php:69`, `:128` | Set datagrid defaults. |
| `JO_TEMPLATE_BOTTOM_OF_TOP` | `modules/joborders/Edit.tpl:277` | Inject markup on edit form. |
| `JO_TEMPLATE_SHOW_BOTTOM_OF_LEFT` / `JO_TEMPLATE_SHOW_BOTTOM_OF_RIGHT` | `modules/joborders/Show.tpl:146` / `:212` | Inject markup on the show page columns. |

### Search (job-order search internals)

| Key | Fires at | Scope |
|---|---|---|
| `JO_SEARCH_SQL` | `lib/Search.php:990`,`1071`,`1147`,`1668` | Append/alter the JO search SQL (multiple search modes). |
| `JO_SEARCH_BY_TITLE` | `lib/Search.php:991` | Title-search SQL fragment. |
| `JO_SEARCH_BY_CLIENT_NAME` | `lib/Search.php:1072` | Client-name search fragment. |
| `JO_SEARCH_BY_EVERYTHING` | `lib/Search.php:1669` | Search-everything fragment. |

(The same four keys also fire in the Sphinx override — see *The optional-updates
add-on pattern* below.)

### Companies / clients

| Key | Fires at | Scope |
|---|---|---|
| `CLIENTS_HANDLE_REQUEST` | `modules/companies/CompaniesUI.php:72` | Gate/route the companies module. |
| `CLIENTS_LIST_BY_VIEW` | `:237` | Hook the list view. |
| `CLIENTS_SHOW` | `:505` | Hook the detail page. |
| `CLIENTS_ADD` | `:533` | Hook the add form. |
| `CLIENTS_ON_ADD_PRE` / `CLIENTS_ON_ADD_POST` | `:615` / `:629` | Straddle company creation. |
| `CLIENTS_EDIT` | `:712` | Hook the edit form. |
| `CLIENTS_ON_EDIT_PRE` / `CLIENTS_ON_EDIT_POST` | `:887` / `:902` | Straddle company update. |
| `CLIENTS_ON_DELETE_PRE` / `CLIENTS_ON_DELETE_POST` | `:952` / `:961` | Straddle company deletion. |
| `CLIENTS_SEARCH` | `:974` | Hook company search UI. |
| `CLIENTS_ON_SEARCH_PRE` / `CLIENTS_ON_SEARCH_POST` | `:1043` / `:1111` | Straddle search execution. |
| `CLIENTS_CREATE_ATTACHMENT` | `:1141` | Hook attachment-create UI. |
| `CLIENTS_ON_CREATE_ATTACHMENT_PRE` / `..._POST` | `:1163` / `:1175` | Straddle attachment creation. |
| `CLIENTS_ON_DELETE_ATTACHMENT_PRE` / `..._POST` | `:1204` / `:1209` | Straddle attachment deletion. |

### Contacts

| Key | Fires at | Scope |
|---|---|---|
| `CONTACTS_HANDLE_REQUEST` | `modules/contacts/ContactsUI.php:81` | Gate/route the contacts module. |
| `CONTACTS_LIST_BY_VIEW_TOP` / `CONTACTS_LIST_BY_VIEW` | `:209` / `:232` | Hook list view top / body. |
| `CONTACTS_SHOW` | `:419` | Hook the detail page. |
| `CONTACTS_ADD` | `:465` | Hook the add form. |
| `CONTACTS_ON_ADD_PRE` / `CONTACTS_ON_ADD_POST` | `:562` / `:579` | Straddle contact creation. |
| `CONTACTS_EDIT` | `:671` | Hook the edit form. |
| `CONTACTS_ON_EDIT_PRE` / `CONTACTS_ON_EDIT_POST` | `:843` / `:864` | Straddle contact update. |
| `CONTACTS_DELETE_PRE` / `CONTACTS_DELETE_POST` | `:885` / `:895` | Straddle contact deletion. |
| `CONTACTS_SEARCH` / `CONTACTS_ON_SEARCH` | `:908` / `:1062` | Hook search UI / execution. |
| `CONTACTS_COLD_CALL_LIST` | `:1088` | Hook the cold-call list. |
| `CONTACTS_ADD_ACTIVITY_SCHEDULE_EVENT` | `:1117` | Hook activity+event-schedule UI. |
| `CONTACT_ON_ADD_ACTIVITY_SCHEDULE_EVENT_PRE` | `:1345` | Pre-step of activity+event scheduling. |
| `CONTACTS_GET_VCARD` | `:1229` | Hook vCard export. |
| `CONTACTS_FORMAT_LIST_BY_VIEW` | `:1310` | Post-process list results. |

### Calendar

| Key | Fires at | Scope |
|---|---|---|
| `CALENDAR_HANDLE_REQUEST` | `modules/calendar/CalendarUI.php:57` | Gate/route the calendar module. |
| `CALENDAR_SHOW` | `:261` | Hook calendar rendering. |
| `CALENDAR_DATA` | `:342` | Hook calendar data feed. |
| `CALENDAR_ADD_PRE` / `CALENDAR_ADD_POST` | `:471` / `:503` | Straddle event creation. |
| `CALENDAR_EDIT_PRE` / `CALENDAR_EDIT_POST` | `:675` / `:686` | Straddle event update. |
| `CALENDAR_DELETE_PRE` / `CALENDAR_DELETE_POST` | `:740` / `:744` | Straddle event deletion. |

### Activity

| Key | Fires at | Scope |
|---|---|---|
| `ACTIVITY_HANDLE_REQUEST` | `modules/activity/ActivityUI.php:61` | Gate/route the activity module. |
| `ACTIVITY_LIST_BY_VIEW_DG` | `:111`, `:256` | Hook the activity datagrid. |
| `ACTIVITY_SEARCH` | `:129` | Hook activity search. |

### Home

| Key | Fires at | Scope |
|---|---|---|
| `HOME_HANDLE_REQUEST` | `modules/home/HomeUI.php:52` | Gate/route the home module. |
| `HOME` | `modules/home/HomeUI.php:107` | Home page render (redirectable; see SettingsUI example). |
| `HOME_DELETE_SAVED_SEARCH_PRE` / `..._POST` | `:170` / `:175` | Straddle saved-search deletion. |
| `HOME_ADD_SAVED_SEARCH_PRE` / `..._POST` | `:195` / `:200` | Straddle saved-search creation. |
| `HOME_QUICK_SEARCH` | `:389` | Hook the quick-search box. |

### Settings

| Key | Fires at | Scope |
|---|---|---|
| `SETTINGS_HANDLE_REQUEST` | `modules/settings/SettingsUI.php:228` | Gate/route the settings module. |
| `SETTINGS_DISPLAY_PROFILE_SETTINGS` | `:1012` | Hook profile-settings page (redirectable). |
| `SETTINGS_ADD_USER` / `SETTINGS_ON_ADD_USER` | `:1168` / `:1269` | Hook add-user form / handler. |
| `SETTINGS_EMAIL_TEMPLATES` | `:216`, `:1620` | Hook email-template settings. |
| `SETTINGS_CAREER_PORTAL` | `:1838` | Hook career-portal settings. |
| `SETTINGS_DISPLAY_ADMINISTRATION` | `:2563` | Hook the administration page. |
| `SETTINGS_CP_REQUEST` | `:3388` | Hook career-portal request handling. |
| `XML_FEED_SUBMISSION_SETTINGS_HEADERS` / `..._BODY` | `:50` / `:1862` | Emit markup in the XML-feed settings page (bare `eval`, output-only). |
| `SETTINGS_USERS_FULLQUOTALICENSES` | `modules/settings/AddUser.tpl:174` | Inject markup re: license quota on add-user form. |
| `CAREER_PORTAL_SUBMIT_XML_FEEDS` | `modules/settings/CareerPortalSettings.tpl:72` | Inject markup on career-portal settings template. |
| `FORCE_ATTACHMENT_LOCAL` | `modules/settings/ajax/backup.php:285` | (also fires in `lib/Attachments.php`) force local attachment storage during backup. |

### Import

| Key | Fires at | Scope |
|---|---|---|
| `IMPORT_REVERT` | `modules/import/ImportUI.php:183` | Hook import revert. |
| `IMPORT_VIEW_ERRORS` | `:209` | Hook error view. |
| `IMPORT_VIEW_PENDING` | `:241` | Hook pending-imports view. |
| `IMPORT_UPLOAD` | `:402` | Hook the upload step. |
| `IMPORT_ADD_FOREIGN` | `:1065` | Hook foreign-row import. |
| `IMPORT_ADD_CANDIDATE` / `IMPORT_ADD_CANDIDATE_POST` | `:1095` / `:1107` | Straddle candidate import. |
| `IMPORT_ADD_JOBORDER` / `IMPORT_ADD_JOBORDER_POST` | `:1127` / `:1139` | Straddle job-order import. |
| `IMPORT_ADD_CLIENT` / `IMPORT_ADD_CLIENT_POST` | `:1168` / `:1179` | Straddle company import. |
| `IMPORT_ADD_CONTACT_CLIENT` / `..._POST` | `:1224` / `:1234` | Straddle contact-with-company import. |
| `IMPORT_ADD_CONTACT` / `IMPORT_ADD_CONTACT_POST` | `:1273` / `:1284` | Straddle contact import. |
| `MASS_IMPORT_SPACE_CHECK` | `:1585` | Disk-space gate for mass import. |
| `IMPORT_NOTIFY_DEV` | `:1633` | Notify on import issues. |

### Reports / graphs

| Key | Fires at | Scope |
|---|---|---|
| `REPORTS_HANDLE_REQUEST` | `modules/reports/ReportsUI.php:53` | Gate/route the reports module. |
| `REPORTS_SHOW` | `:198` | Hook the reports landing page. |
| `REPORTS_GRAPH` | `:216` | Hook graph rendering in reports. |
| `REPORTS_SHOW_SUBMISSION` | `:295`, `:375` | Hook submission reports. |
| `REPORTS_CUSTOMIZE_JO_REPORT_PRE` / `..._POST` | `:478` / `:591` | Straddle JO-report customization. |
| `GRAPH_TEST` | `modules/graphs/GraphsUI.php:144` | Hook the test graph. |
| `GRAPH_JOB_ORDER_REPORT` | `:185` | Hook the JO report graph. |
| `GRAPH_WEEKLY_ACTIVITY` | `:242` | Hook the weekly-activity graph. |
| `GRAPH_NEW_CANDIDATES` | `:297` | Hook the new-candidates graph. |
| `GRAPH_NEW_JOB_ORDERS` | `:351` | Hook the new-job-orders graph. |
| `GRAPH_GENERIC` / `GRAPH_GENERIC_PIE` | `:382` / `:398` | Hook generic line / pie graph rendering. |
| `GRAPH_MINI_PIPELINE` | `:530` | Hook the mini-pipeline graph. |
| `GRAPH_NEW_SUBMISSIONS` | `:585` | Hook the new-submissions graph. |

### Careers / RSS / XML / export

| Key | Fires at | Scope |
|---|---|---|
| `CAREERS_SITEID` | `modules/careers/CareersUI.php:81`; `lib/EmailTemplates.php:297` | Resolve/override the careers-portal site ID. |
| `CAREERS_PAGE_BOTTOM` | `modules/careers/CareersUI.php:1118` | Inject markup at the bottom of careers pages. |
| `RSS_SITEID` | `modules/rss/RssUI.php:105`; `modules/xml/XmlUI.php:109` | Resolve/override the RSS/XML feed site ID. |
| `XML_SUBMIT_FEEDS_TO_QUEUE` | `lib/XmlJobExport.php:110` | Hook XML job-feed queueing. |
| `EXPORT` | `modules/export/ExportUI.php:120` | Hook the data-export action. |

### Lists

| Key | Fires at | Scope |
|---|---|---|
| `LISTS_HANDLE_REQUEST` | `modules/lists/ListsUI.php:67` | Gate/route the lists module. |
| `LISTS_LIST_BY_VIEW` | `:146` | Hook the lists view. |

### Pipelines / users

| Key | Fires at | Scope |
|---|---|---|
| `PIPELINES_ADD_SQL` | `lib/Pipelines.php:95` | Append/alter pipeline-insert SQL. |
| `USERS_GET_SELECT_SQL` | `lib/Users.php:636` | Alter the user-select SQL. |

### Attachments / file storage

| Key | Fires at | Scope |
|---|---|---|
| `ATTACHMENTS_HANDLE_REQUEST` | `modules/attachments/AttachmentsUI.php:55` | Gate/route the attachments module. |
| `ATTACHMENT_RETRIEVAL` | `:110` | Hook attachment retrieval/serving. |
| `CREATE_ATTACHMENT_FINISHED` | `lib/Attachments.php:1288` | Post-attachment-create action. |
| `UPDATE_SPHINX_DELTA` | `lib/Attachments.php:160` | Trigger a Sphinx delta-index update (bare `eval`). |
| `FORCE_ATTACHMENT_LOCAL` | `lib/Attachments.php:175`; `modules/settings/ajax/backup.php:285` | Force local attachment storage. |
| `FORCE_ATTACHMENT_REMOTE` | `lib/Attachments.php:192` | Force remote attachment storage. |
| `FORCE_ATTACHMENT_DELETE` | `lib/Attachments.php:202` | Force attachment delete behavior. |
| `FILE_UTILITY_UPLOAD_PATH` | `lib/FileUtility.php:482` | Override the upload path. |
| `FILE_UTILITY_SPACE_CHECK` | `lib/FileUtility.php:581` | Override the disk-space check. |

### Template / UI chrome

| Key | Fires at | Scope |
|---|---|---|
| `TEMPLATE_LIVE_CHAT` | `lib/TemplateUtility.php:111` | Inject a live-chat widget. |
| `TEMPLATE_LOGIN_INFO_PRE_TOP_RIGHT` | `:113` | Inject markup before the top-right login info. |
| `TEMPLATE_LOGIN_INFO_TOP_RIGHT_UPGRADE` | `:133` | Inject an upgrade prompt in the top-right. |
| `TEMPLATE_LOGIN_INFO_EXTENDED_SITE_NAME` | `:167` | Override the extended site name. |
| `TEMPLATE_UTILITY_EVALUATE_TAB_VISIBLE` | `:637` | Decide per-tab visibility (used by SettingsUI for career-portal mode). |
| `TEMPLATE_UTILITY_DRAW_SUBTABS` | `:807` | Customize sub-tab rendering. |
| `TEMPLATEUTILITY_SHOWPRIVACYPOLICY` | `:855` | Toggle/insert the privacy-policy link. |
| `TEMPLATE_UTILITY_PRINT_FOOTER` | `:858` | Emit footer markup (bare `eval`). |
| `FRIENDLYERRORS_CONTACTCATS` | `modules/home/FriendlyError.tpl:52` | Customize the "contact us" block on error pages. |

---

## The PRE / POST pattern

A large share of the keys come in `*_PRE` / `*_POST` pairs that straddle a
mutating operation — e.g. `CALENDAR_ADD_PRE` / `CALENDAR_ADD_POST`
(`modules/calendar/CalendarUI.php:471`,`503`), `CANDIDATE_ON_ADD_PRE` /
`CANDIDATE_ON_ADD_POST` (`modules/candidates/CandidatesUI.php:2871`,`3098`),
`CLIENTS_ON_EDIT_PRE` / `CLIENTS_ON_EDIT_POST`
(`modules/companies/CompaniesUI.php:887`,`902`), and the import `*_POST` family.

Straddling a PRE/POST pair lets an add-on:

- **At PRE:** validate or mutate the request data before the write, or
  `return false;` to abort the operation entirely (the `if (!eval(...)) return;`
  guard makes the host method bail out).
- **At POST:** react to the committed change — e.g. mirror the record to an
  external system, send a notification, reindex, or write audit data — with the
  newly created/updated IDs in scope.

Because the PRE and POST fragments execute in the *same method body*, variables
set in PRE remain visible to POST (they share the host method's scope), so a pair
can thread state from before the write to after it. Some flows add a third
granularity, e.g. `CANDIDATE_ADD_TO_PIPELINE_POST_IND` fires once *per individual
candidate* inside a batch loop, while `CANDIDATE_ADD_TO_PIPELINE_POST` fires once
for the whole batch (`modules/candidates/CandidatesUI.php:1760`,`1763`).

---

## The `optional-updates/` add-on pattern

Beyond the in-code hook slots, OpenCATS demonstrates a second, coarser extension
style: **drop-in file overrides**, shipped under `optional-updates/`. The repo
contains exactly one such bundle:

```
optional-updates/latest-sphinx-search/
  README.MD
  Search.php       (78 KB — a full replacement for lib/Search.php)
  config.php       (sample)
  sphinx.conf      (sample)
```

`optional-updates/latest-sphinx-search/Search.php` is a near-verbatim copy of
`lib/Search.php` that adds newer Sphinx-search behavior. The diff is small at the
top of the file (e.g. it drops the `JobOrderStatuses.php` include and adds a
keyword-highlighting block), and critically **it preserves the same hook call
sites** — the override still fires `JO_SEARCH_SQL`, `JO_SEARCH_BY_TITLE`,
`JO_SEARCH_BY_CLIENT_NAME`, and `JO_SEARCH_BY_EVERYTHING`
(`optional-updates/latest-sphinx-search/Search.php:1257-1258`, `:1345-1346`,
`:1428`, `:1957-1958`), so any module-registered fragments for those keys keep
working after the swap.

Installation is **manual file replacement**, not autoloading. The bundle's
`README.MD` directs the operator to follow the wiki and apply it *after* a normal
install (`optional-updates/latest-sphinx-search/README.MD:1-7`). Nothing in the
running application references the `optional-updates/` path — a repo-wide grep
finds no PHP that includes or loads from `optional-updates/` (see *Source
evidence*). In practice the operator copies `Search.php` over `lib/Search.php`
(and applies the sample `config.php` / `sphinx.conf`). This is the real,
in-repo example of the override-by-replacement model: the extension is a whole
file that takes the place of a core file, retaining the hook contract.

---

## Security implications

The hook mechanism is, by construction, **`eval()` of strings sourced from
`$_SESSION['hooks']`**. The chain is:

1. `_refreshModuleList()` reads code fragments from each module's `getHooks()`
   and stores them in `$_SESSION['hooks']` (and, when caching is on, serializes
   them into `modules.cache`) (`lib/ModuleUtility.php:275-279,295,304-308`).
2. `Hooks::get()` concatenates those fragments into a PHP source string
   (`lib/Hooks.php:63-71`).
3. Call sites execute that string with `eval()` — running with the full
   privileges of the application (DB access, filesystem, session).

Trust assumptions to be explicit about:

- The hook code is **not user-supplied at request time**; it originates from
  module PHP files discovered on disk under `MODULES_PATH`
  (`lib/ModuleUtility.php:220-282`). Anyone who can write a `*UI.php` into a
  module directory (or alter an existing module's `defineHooks()`) can inject
  arbitrary PHP that the app will `eval()` on every relevant request. This is the
  same trust level as having write access to the application's source tree.
- The fragments transit `$_SESSION` and the on-disk `modules.cache` (a serialized
  object). Treat both as code, not data: a writable `modules.cache` or a
  tamperable session store would allow PHP-code injection into `eval()`.
- Because hooks run via `eval()`, a registration bug surfaces as a fatal PHP
  error inside the host method rather than a contained exception.

This is a deliberate design choice for a legacy extension model, not an exploit
in itself; the integrity guarantee is *"only trusted code reaches the modules
directory and the cache file."* For the broader security posture (input
handling, auth, CSRF, the recent AJAX/module authorization hardening), see
**doc 20 — Security**.

---

## Source evidence

- `lib/Hooks.php:38-73` — the entire `Hooks` class (`get()` at lines 52-72).
- `lib/UserInterface.php:51` (`protected $_hooks = array();`), `:94-97`
  (`getHooks()`).
- `lib/ModuleUtility.php:147-175` (`getModules`), `:193-312` (`_refreshModuleList`,
  including `:207-214` cache load, `:275-279` hook collection, `:295` session
  store, `:304-308` cache write).
- `modules/settings/SettingsUI.php:84` (`$this->_hooks = $this->defineHooks();`),
  `:87-128` (`defineHooks()`).
- Hook-key inventory derived from a repo-wide grep:
  `grep -rnoE "Hooks::get\('[A-Z_]+'\)" --include="*.php" --include="*.tpl" .`
  → **260 call sites**, **229 distinct keys** (the tables above list every
  distinct key; family members like the four `JO_SEARCH_*` and the many
  `CANDIDATE_ON_CREATE_ATTACHMENT_*` repeats are folded into single rows with all
  firing lines noted).
- `eval()` idiom variants confirmed by grep: guarded
  `if (!eval(Hooks::get('...'))) return;` is the norm (e.g. `index.php:199`,
  `ajax.php:161`); bare `eval(Hooks::get('...'));` for output-only slots
  (`lib/Attachments.php:160`, `lib/TemplateUtility.php:858`,
  `modules/settings/SettingsUI.php:50`,`:1862`); template form
  `<?php eval(Hooks::get('...')); ?>` (`modules/candidates/Add.tpl:132`).
- `optional-updates/latest-sphinx-search/` — `README.MD:1-7`, `Search.php`
  (hook sites at `:1257-1258`, `:1345-1346`, `:1428`, `:1957-1958`). A grep for
  `optional-updates` / `latest-sphinx-search` outside that directory returns no
  references, confirming the bundle is applied by manual file replacement, not
  loaded by the app.

---

## Unverified / open questions

- **Cache invalidation of hooks.** When `CACHE_MODULES` is on, hooks are read
  from `modules.cache` (`lib/ModuleUtility.php:207-214`). The precise conditions
  under which the cache is rebuilt after a module's `defineHooks()` changes
  (beyond `performMaintenence` POST and the file-existence check) were not traced
  end-to-end here.
- **Template-only keys.** Several keys appear only in `.tpl` files
  (`CANDIDATE_TEMPLATE_ABOVE_FREEFORM`, `CANDIDATE_TEMPLATE_BELOW_FREEFORM`,
  `CANDIDATE_TEMPLATE_SHOW_PIPELINE_ACTION`, `JO_TEMPLATE_BOTTOM_OF_TOP`,
  `JO_TEMPLATE_SHOW_BOTTOM_OF_LEFT/RIGHT`, `SETTINGS_USERS_FULLQUOTALICENSES`,
  `CAREER_PORTAL_SUBMIT_XML_FEEDS`, `FRIENDLYERRORS_CONTACTCATS`). They fire via
  `eval()` exactly like PHP-file slots, but no in-repo module registers code for
  them — they are inert no-op slots awaiting an external add-on.
- **No in-tree registrations except SettingsUI.** A scan of `getHooks()` /
  `defineHooks()` across the modules shows `SettingsUI` as the only module that
  actually populates `$_hooks` with real code. Every other key in the inventory
  is a *firing site with no shipped registration* — i.e. an extension point
  reserved for downstream/hosted add-ons rather than something exercised by the
  open-source distribution. (Default behavior for all such keys is the
  `return true;` no-op from `lib/Hooks.php:56,71`.)
- **`CANDIDATE_ON_ADD_ACTIVITY_CHANGE_STATUS_POST` cross-module fire** at
  `modules/contacts/ContactsUI.php:1601` uses a candidate-namespaced key inside
  the contacts module; whether this is intentional reuse or a historical
  copy-paste was not determined.
