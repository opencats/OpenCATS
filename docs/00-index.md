# OpenCATS Source & Design Documentation — Index

This documentation set describes the OpenCATS codebase (version `0.9.7.4`, per
`constants.php:45`) **as the source actually is** — a long-lived legacy PHP 7.4 application
whose logic lives mostly in `lib/` and `modules/`, with a thin modern PSR-4 layer under
`src/OpenCATS/`. Every factual claim in these documents is cited to a real file and line range.
Where the code and the root `CLAUDE.md` disagree, the code wins and the discrepancy is noted.

The 20 documents are ordered to be read sequentially. The glossary (doc 02) is a reference you
can consult at any point. Evidence of coverage lives in
[`_evidence/file-inventory.md`](_evidence/file-inventory.md) and
[`_evidence/coverage.md`](_evidence/coverage.md).

## Repository facts established in Phase 0

- 21 application modules under `modules/`; 74 `lib/*.php` classes; 30 PHP files under `src/`.
- 55 `CREATE TABLE` statements in `db/cats_schema.sql`; 8 `db/*.sql` files total.
- 9 `ACCESS_LEVEL_*`, 9 `DATA_ITEM_*`, 4 `SETTINGS_*` constants in `constants.php`.
- 230 distinct `Hooks::get('KEY')` keys across 261 call sites; hooks are executed via `eval()`.

---

## Orientation

**[01 — Repository Overview](01-repository-overview.md)**
What OpenCATS is and its CATS lineage; tech stack and versions (`composer.json`, Dockerfiles);
top-level directory map with each folder's role; the `lib/` vs `modules/` vs `src/OpenCATS/`
split; the three entry points (`index.php`, `ajax.php`, `QueueCLI.php`); how to run via
`docker/`. Includes a Mermaid layer diagram.

**[02 — Glossary & Domain Model](02-glossary-domain-model.md)**
Precise definitions of this system's domain terms *as used in the code*: candidate, job order,
pipeline, activity, the `DATA_ITEM_*` types (with values), site/`siteID` multi-tenancy,
ACL/access level, hooks, attachments, saved lists, extra fields. Each term tied to the
classes/tables/constants that implement it. *Reference — consult anytime.*

**[03 — Architecture Overview](03-architecture-overview.md)**
The request lifecycle traced from `index.php`: bootstrap, `CATSSession` (`$_SESSION['CATS']`),
CSRF enforcement on POST, `ModuleUtility::loadModule`, `UserInterface::handleRequest` dispatch,
`Template` rendering, the `DatabaseConnection::getInstance()` mysqli singleton, the `siteID`
multi-tenancy model, and the `eval(Hooks::get())` extension mechanism. Mermaid request-flow +
component/layer diagrams.

## Structure: data & source

**[04 — Database Schema & ER Model](04-database-schema-er.md)**
Every `CREATE TABLE` in `db/cats_schema.sql`: columns/types, PK/keys, explicit and `*_id`-implied
FKs, and how `site_id` scopes tables. Mermaid `erDiagram`s split by domain, plus a
table-by-table data dictionary. (Full migration history is doc 19.)

**[05 — Module Source Analysis](05-module-source-analysis.md)** *(index → per-module files)*
For each `modules/<name>/<Name>UI.php`: the full `handleRequest()` action switch, the
`getUserAccessLevel('<module>.<action>')` guard on each action, the `lib/` classes/methods
called, the `.tpl` templates rendered, and module JS. This file is the index; one detail file
per module lives under `docs/modules/`. Proposed split below.

**[06 — Component & Class Diagrams](06-component-class-diagrams.md)**
Mermaid `classDiagram`s for real classes: the `lib/` domain classes (`Candidates`, `JobOrders`,
`Companies`, `Contacts`) with their actual public methods and the `$siteID` constructor pattern,
and the `src/OpenCATS/Entity` repository classes (`JobOrder`+`JobOrderRepository`,
`Company`+`CompanyRepository`).

## Behavior & flows

**[07 — Core User Workflows](07-core-user-workflows.md)**
End-to-end traces through code: add/edit a candidate, create a job order, add a candidate to a
pipeline, change pipeline status, log an activity, upload & parse a resume — each step citing
action → `lib/` method → SQL.

**[08 — Sequence Diagrams](08-sequence-diagrams.md)**
Mermaid `sequenceDiagram`s for traced chains: login/auth, candidate save, an AJAX call through
`ajax.php` (`f=<function>`), the async queue via `QueueCLI.php`, and an email send via
`lib/Mailer`. Participants are real classes/files.

**[09 — State Diagrams](09-state-diagrams.md)**
`stateDiagram-v2` for lifecycles that exist in code/schema: candidate pipeline status, job order
status, async queue item status, and session/login state — with the actual status values from
`constants.php` and the schema.

**[10 — UI Workflow & Navigation](10-ui-workflow.md)**
How modules map to screens (`.tpl`), tabs/sub-tabs, list views (`DataGrid`), and show/edit/add
flows and their transitions. Mermaid navigation flowcharts overall and per module, grounded in
the actual templates and `*UI` actions.

## Subsystems

**[11 — Hooks & Extension Points](11-hooks-extension-points.md)**
The hook mechanism end to end: `lib/Hooks.php`, the discovery/caching in `ModuleUtility`, and
every `eval(Hooks::get('KEY'))` call site — what fires where, what variables are in scope, and
what an add-on (`optional-updates/`) can do. Flags the `eval()` security implications (→ doc 20).

**[12 — Async Queue & Scheduled Jobs](12-async-queue-scheduled-jobs.md)**
`QueueCLI.php` and `lib/QueueProcessor.php` in full: cron invocation contract, the `queue` table,
job types, enqueue call sites, status transitions (→ doc 09), failure/retry, logging.

**[13 — APIs & Integrations](13-api-integration.md)**
`ajax.php` dispatch and the 20 `ajax/*.php` handlers; the public career portal
(`modules/careers`, `careers/`) and `rss`/`xml` shims; Sphinx full-text search (`ENABLE_SPHINX`);
external resume-text binaries (antiword, pdftotext, html2text, unrtf) and their config; email/SMTP;
the `wsdl/` SOAP contracts; the QueueCLI cron contract (operational depth in doc 12).

**[14 — Permissions & Access-Control Matrix](14-permissions-access-control-matrix.md)**
Authoritative access-control reference: the `ACCESS_LEVEL_*` values, then a complete matrix of
every `module.action` (from doc 05) → required access level (from each
`getUserAccessLevel(...)` guard). How levels are assigned to users and how SA/MULTI_SA/ROOT differ.

## Specifications & guides

**[15 — Functional Specification](15-functional-specification.md)**
The feature set derived from the modules + actions + access levels of docs 05/14: what each
feature does, its inputs, rules, and required permission level.

**[16 — Developer Setup](16-developer-setup.md)**
From `docker/`, `composer.json`, `phpunit.xml.dist`, `README-testing.md`, `config.php`: composer
(dev vs `--no-dev`), `docker compose up`, `config.php`+`INSTALL_BLOCK`+install wizard, unit vs
integration vs Behat tests, the lint CI runs, and ports (80/443/8080/3306/3307).

**[17 — Admin & Configuration Guide](17-admin-configuration-guide.md)**
Every `config.php` constant/flag and what it controls; the `settings`/admin modules; user + site
administration; and the ACL model from an admin's viewpoint (full matrix in doc 14).

**[18 — User Guide](18-user-guide.md)**
End-user task instructions per module, derived from the actual screens (`.tpl`) and actions:
managing candidates, job orders, companies, contacts, pipelines, activities, calendar, searches.

## Operations & review

**[19 — Upgrade & Migration History](19-upgrade-migration-history.md)**
Every `db/upgrade-*.sql` in order: target version and the schema/data changes each applies; the
install wizard's role; how a deployment moves from `cats_schema.sql` through upgrades; gaps.

**[20 — Security & Maintainability Review](20-security-maintainability-review.md)**
Concrete code findings: CSRF handling, the per-action ACL guard pattern, SQL escaping via
`makeQueryString`, the career-portal XSS surface + upload filetype whitelist, `eval()`-based
Hooks risk (→ doc 11), legacy `include_once` coupling, password/credential handling, committed
`config.php`. Cross-checked against `Security.MD`. Ends with a prioritized tech-debt register.

---

## Proposed per-module file split for Doc 05 (`docs/modules/`)

One file per module discovered in Phase 0 (21 files). Each will document that module's controller
class, full `handleRequest()` action list with ACL guards, `lib/` calls, templates, and JS.

| # | File | Module | Controller class (file) |
|---|---|---|---|
| 1 | `docs/modules/candidates.md` | candidates | `CandidatesUI` (`CandidatesUI.php`) |
| 2 | `docs/modules/joborders.md` | joborders | `JobOrdersUI` (`JobOrdersUI.php`) |
| 3 | `docs/modules/companies.md` | companies | `CompaniesUI` (`CompaniesUI.php`) |
| 4 | `docs/modules/contacts.md` | contacts | `ContactsUI` (`ContactsUI.php`) |
| 5 | `docs/modules/activity.md` | activity | `ActivityUI` (`ActivityUI.php`) |
| 6 | `docs/modules/calendar.md` | calendar | `CalendarUI` (`CalendarUI.php`) |
| 7 | `docs/modules/attachments.md` | attachments | `AttachmentsUI` (`AttachmentsUI.php`) |
| 8 | `docs/modules/home.md` | home | `HomeUI` (`HomeUI.php`) |
| 9 | `docs/modules/lists.md` | lists | `ListsUI` (`ListsUI.php`) |
| 10 | `docs/modules/login.md` | login | `LoginUI` (`LoginUI.php`) |
| 11 | `docs/modules/settings.md` | settings | `SettingsUI` (`SettingsUI.php`) |
| 12 | `docs/modules/reports.md` | reports | `ReportsUI` (`ReportsUI.php`) |
| 13 | `docs/modules/graphs.md` | graphs | `GraphsUI` (`GraphsUI.php`) |
| 14 | `docs/modules/import.md` | import | `ImportUI` (`ImportUI.php`) + `Import.php` |
| 15 | `docs/modules/export.md` | export | `ExportUI` (`ExportUI.php`) |
| 16 | `docs/modules/careers.md` | careers | `CareersUI` (`CareersUI.php`) |
| 17 | `docs/modules/queue.md` | queue | `QueueUI` (`QueueUI.php`) |
| 18 | `docs/modules/rss.md` | rss | `RssUI` (`RssUI.php`) |
| 19 | `docs/modules/xml.md` | xml | `XmlUI` (`XmlUI.php`) |
| 20 | `docs/modules/wizard.md` | wizard | `WizardUI` (`WizardUI.php`) |
| 21 | `docs/modules/install.md` | install | `CATSUI` (`CATSUI.php`) — class name differs from module name |

## Source evidence

Phase 0 inventory commands and counts are recorded in
[`_evidence/file-inventory.md`](_evidence/file-inventory.md). Index scoping derives from:
`index.php`, `ajax.php`, `QueueCLI.php`, `constants.php:45,57-82`, `composer.json`,
`lib/ModuleUtility.php` (`loadModule`, `_refreshModuleList`), `lib/Hooks.php:38-72`,
`db/cats_schema.sql` (55 tables), and the `modules/*/` controller-class scan.

## Unverified / open questions

- Exact `getUserAccessLevel('<module>.<action>')` keys per action are confirmed only for
  `candidates` so far; the rest are catalogued in Phase 2 (docs 05/14).
- Whether all 230 hook keys have live `eval` sites vs. being defined-but-unused is resolved in
  doc 11.
- The `candidate_jobordrer_status_type` table name (apparent typo) is confirmed against schema
  usage in doc 04.
