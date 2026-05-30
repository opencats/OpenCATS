# 05 — Module Source Analysis (Index)

OpenCATS' web application is organized as 21 modules under `modules/`. Each module is a directory
containing a controller class (`<Name>UI.php`), Smarty-style `.tpl` templates, optional
module-specific JavaScript, and sometimes a `dataGrids.php`. The front controller loads a module
via `ModuleUtility::loadModule($_GET['m'])`, which `include_once`s
`modules/<name>/<Class>.php`, instantiates the class, and calls `handleRequest()`
(lib/ModuleUtility.php:51-79). Each controller's `handleRequest()` reads the action from
`$_GET['a']` (via `getAction()`) and dispatches through a `switch`, with most actions guarded by a
`getUserAccessLevel('<module>.<action>') < ACCESS_LEVEL_*` check.

This document is the **index**. One detail file per module lives under
[`docs/modules/`](modules/), each with: the controller class declaration and constructor settings,
a full action catalog (action → exact ACL guard → required level → handler → lib calls → template),
per-action tracing, template/JS inventories, lib dependencies, and the hooks fired. The
authoritative cross-module permission matrix is [doc 14](14-permissions-access-control-matrix.md).

## Module → detail file

| Module | Controller class (file) | Auth required | Detail doc |
|---|---|---|---|
| candidates | `CandidatesUI` (`modules/candidates/CandidatesUI.php:53`) | yes | [candidates.md](modules/candidates.md) |
| joborders | `JobOrdersUI` (`modules/joborders/JobOrdersUI.php:52`) | yes | [joborders.md](modules/joborders.md) |
| companies | `CompaniesUI` (`modules/companies/CompaniesUI.php:44`) | yes | [companies.md](modules/companies.md) |
| contacts | `ContactsUI` (`modules/contacts/ContactsUI.php:43`) | yes | [contacts.md](modules/contacts.md) |
| activity | `ActivityUI` (`modules/activity/ActivityUI.php:36`) | yes | [activity.md](modules/activity.md) |
| calendar | `CalendarUI` (`modules/calendar/CalendarUI.php:35`) | yes | [calendar.md](modules/calendar.md) |
| attachments | `AttachmentsUI` (`modules/attachments/AttachmentsUI.php:33`) | yes | [attachments.md](modules/attachments.md) |
| home | `HomeUI` (`modules/home/HomeUI.php:34`) | yes | [home.md](modules/home.md) |
| lists | `ListsUI` (`modules/lists/ListsUI.php:44`) | yes | [lists.md](modules/lists.md) |
| login | `LoginUI` (`modules/login/LoginUI.php:37`) | **no** | [login.md](modules/login.md) |
| settings | `SettingsUI` (`modules/settings/SettingsUI.php:55`) | yes | [settings.md](modules/settings.md) |
| reports | `ReportsUI` (`modules/reports/ReportsUI.php:35`) | yes | [reports.md](modules/reports.md) |
| graphs | `GraphsUI` (`modules/graphs/GraphsUI.php:38`) | **no** | [graphs.md](modules/graphs.md) |
| import | `ImportUI` (`modules/import/ImportUI.php:48`) | yes | [import.md](modules/import.md) |
| export | `ExportUI` (`modules/export/ExportUI.php:41`) | yes | [export.md](modules/export.md) |
| careers | `CareersUI` (`modules/careers/CareersUI.php:47`) | **no** (public portal) | [careers.md](modules/careers.md) |
| queue | `QueueUI` (`modules/queue/QueueUI.php:35`) | yes | [queue.md](modules/queue.md) |
| rss | `RssUI` (`modules/rss/RssUI.php`) | **no** (public feed) | [rss.md](modules/rss.md) |
| xml | `XmlUI` (`modules/xml/XmlUI.php`) | **no** (public feed) | [xml.md](modules/xml.md) |
| wizard | `WizardUI` (`modules/wizard/WizardUI.php:40`) | **no** | [wizard.md](modules/wizard.md) |
| install | `CATSUI` (`modules/install/CATSUI.php:30`) | **no** | [install.md](modules/install.md) |

## Cross-cutting findings (verified while reading every controller)

These are patterns and anomalies that recur across modules and matter for the architecture,
permissions, and security docs.

### The dispatch + ACL pattern is consistent — but the guard *key* often differs from the action
The recruiting modules (candidates, joborders, companies, contacts, calendar) follow the canonical
shape: `case '<action>':` → `if ($this->getUserAccessLevel('<module>.<x>') < ACCESS_LEVEL_Y) { CommonErrors::fatal(...) }`
→ handler. But several actions guard under a **different permission key** than their own module:
- In both `candidates` and `joborders`, the pipeline actions guard on `pipelines.*` keys —
  `addToPipeline`→`pipelines.addToPipeline`, `addActivity`→`pipelines.addActivity`,
  `changeStatus`→`pipelines.changeStatus`, `removeFromPipeline`→`pipelines.removeFromPipeline`
  (modules/candidates/CandidatesUI.php; modules/joborders/JobOrdersUI.php:183,200,242,277).
- `joborders`' `addCandidateModal` guards on `candidates.add` (JobOrdersUI.php:260).
- The default/list cases guard on `<module>.list`, not `<module>.listByView`
  (e.g. JobOrdersUI.php:355, CompaniesUI.php).
- The activity AJAX mutators guard on `contacts.editActivity` / `contacts.deleteActivity`
  (ajax/editActivity.php:42, ajax/deleteActivity.php:41).

### A large set of modules have NO per-action `getUserAccessLevel` guard at all
Confirmed by reading each controller: **activity, home, lists, export, queue** (authenticated but
no level check), and **login, graphs, careers, rss, xml, wizard, install** (which set
`_authenticationRequired = false`). For these, the only access control is the module-level
authentication flag plus, in some cases, a POST/`isPostBack()` check or an ad-hoc check inside a
handler. This is a recurring authorization gap catalogued in
[doc 14](14-permissions-access-control-matrix.md) and [doc 20](20-security-maintainability-review.md).

### Settings concentrates the privileged surface
`settings` is the only module that routinely requires `ACCESS_LEVEL_SA` (400) — most POST/write
actions — with read/GET paths at `ACCESS_LEVEL_DEMO` (350). One sub-action, `changeVersionName`,
requires `ACCESS_LEVEL_ROOT` (500) (modules/settings/SettingsUI.php:2614). A few settings actions
(`ajax_tags_*`, `addEmailTemplate`/`deleteEmailTemplate`) lack a switch-level guard;
the email-template ones enforce SA inside the handler instead (SettingsUI.php:948,968).

### Public/unauthenticated surface
The externally reachable modules are **careers** (job board + candidate application + resume
upload), **rss** and **xml** (job feeds), and **graphs** (chart images, some tiers public). `index.php`
routes to careers/rss/xml via the `$careerPage`/`$rssPage`/`$xmlPage` flags set by the root shims
`careers/index.php`, `rss/index.php`, `xml/index.php`, and these requests are **excluded from the
global CSRF check** (index.php:145-150). Detail in [doc 13](13-api-integration.md).

### Multi-class controller files
`home/dataGrids.php` defines `ImportantPipelineDashboard` and `CallsDataGrid`;
`joborders/dataGrids.php` defines `JobOrdersListByViewDataGrid` and `joborderSavedListByViewDataGrid`
(base `JobOrdersDataGrid` is in lib/JobOrders.php:878). The CLAIM in earlier notes that these grid
classes live inside the `*UI.php` files is **false** — they live in `dataGrids.php`.

### Vestigial / dead code spotted (full list in each module doc's Unverified footer)
- `install`'s `CATSUI::handleRequest()` is **empty** (CATSUI.php:42-44); real installer logic is in
  `modules/install/ajax/ui.php` (25 actions reached via `ajax.php` as `install:ui`).
- `queue`'s `QueueUI::handleRequest()` switch is only `default: break;` — all queue work is in
  `lib/QueueProcessor.php` driven by `QueueCLI.php`.
- `wizard`'s page-definition constructor block is commented out; the only `new Wizard(...)` caller
  is a commented-out block in `LoginUI.php` — so the wizard session is normally empty.
- Orphan templates with no `display()` caller: `candidates` Duplicates/HotList/Error/ErrorModal,
  `reports` NewDataItems.tpl, `import` ImportCommits.tpl; 0-byte templates in `careers`
  (Openings.tpl, SearchOpenings.tpl) and an empty `ajax/getReportHTML.php`.
- `login`'s forgot-password POST path references `Users::getPassword()` and constants
  `PASSWORD_RESET_SUBJECT/BODY` that **do not exist** in the repo — path appears non-functional.

## Source evidence

This index is compiled from the 21 per-module documents under `docs/modules/`, each of which was
written by reading the corresponding controller in full plus its templates/JS/`dataGrids.php` and
the cited `lib/` methods. Cross-cutting facts cite: `lib/ModuleUtility.php:51-79`,
`index.php:145-205`, `constants.php:74-82`, and the controller class-declaration lines in the table
above. Raw ACL extracts: [`_evidence/acl-summary.md`](_evidence/acl-summary.md).

## Unverified / open questions

- The exact runtime resolution of a dotted permission key (e.g. `candidates.duplicates`) to a
  numeric level per user/role lives in `lib/ACL.php` + `lib/Session.php::getUserAccessLevel`;
  resolved in [doc 14](14-permissions-access-control-matrix.md).
- Whether `modules.cache` is used in practice (depends on `CACHE_MODULES`) — see
  [doc 03](03-architecture-overview.md).
- Per-module "Unverified" footers list module-local anomalies not re-verified globally here.
