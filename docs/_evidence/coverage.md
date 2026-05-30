# Coverage Ledger

Maps each document to the files it consumed. All 20 core documents + the 21 per-module detail
files are complete and persisted under `docs/`. Status: **☑ complete** for every document.

Legend: ☑ complete

## Document status

| Doc | File | Status | Primary files consumed |
|---|---|---|---|
| 00 | `docs/00-index.md` | ☑ | (index; inventory) |
| 01 | `docs/01-repository-overview.md` | ☑ | composer.json/.lock, docker/* , Dockerfile, index.php, ajax.php, QueueCLI.php, constants.php, README.md |
| 02 | `docs/02-glossary-domain-model.md` | ☑ | constants.php (full), db/cats_schema.sql, lib/{Candidates,JobOrders,Pipelines,ActivityEntries,Site,ACL,SavedLists,ExtraFields,Hooks,Template}.php |
| 03 | `docs/03-architecture-overview.md` | ☑ | index.php, lib/{ModuleUtility,UserInterface,Session,Template,DatabaseConnection,Hooks}.php, CandidatesUI.php, config.php |
| 04 | `docs/04-database-schema-er.md` | ☑ | db/cats_schema.sql (full, 55 tables) |
| 05 | `docs/05-module-source-analysis.md` (+ `docs/modules/*.md`) | ☑ | all 21 `modules/*/*UI.php` + templates/JS/dataGrids.php; `_evidence/acl-summary.md` |
| 06 | `docs/06-component-class-diagrams.md` | ☑ | lib/{Candidates,JobOrders,Companies,Contacts,Pipelines,ActivityEntries,UserInterface}.php, src/OpenCATS/Entity/*, src/OpenCATS/UI/* |
| 07 | `docs/07-core-user-workflows.md` | ☑ | CandidatesUI, JobOrdersUI, lib/{Candidates,JobOrders,Pipelines,ActivityEntries,Attachments,DocumentToText}.php, JobOrder(Repository), config.php |
| 08 | `docs/08-sequence-diagrams.md` | ☑ | LoginUI, lib/{Session,Users,AJAXInterface,QueueProcessor,Mailer,Calendar}.php, ajax.php, ajax/getCandidateIdByEmail.php, QueueCLI.php |
| 09 | `docs/09-state-diagrams.md` | ☑ | constants.php (PIPELINE_STATUS_*, LOGIN_*, ACCESS_LEVEL_*), db/cats_schema.sql, lib/{Pipelines,JobOrderStatuses,QueueProcessor,Session}.php, index.php |
| 10 | `docs/10-ui-workflow.md` | ☑ | lib/{UserInterface,TemplateUtility,DataGrid}.php, ModuleUtility.php, candidates/joborders/home templates + dataGrids.php |
| 11 | `docs/11-hooks-extension-points.md` | ☑ | lib/Hooks.php, lib/ModuleUtility.php, lib/UserInterface.php, SettingsUI.php, all eval(Hooks::get()) sites (grep), optional-updates/latest-sphinx-search/* |
| 12 | `docs/12-async-queue-scheduled-jobs.md` | ☑ | QueueCLI.php, lib/QueueProcessor.php, db/cats_schema.sql (queue), modules/queue/constants.php, lib/XmlJobExport.php |
| 13 | `docs/13-api-integration.md` | ☑ | ajax.php, lib/AJAXInterface.php, all 20 ajax/*, careers/rss/xml shims, config.php (Sphinx/binaries/mail), lib/{DocumentToText,Search,Mailer,ParseUtility,License}.php, wsdl/* |
| 14 | `docs/14-permissions-access-control-matrix.md` | ☑ | constants.php:74-82, lib/{Session,ACL,UserInterface,Users}.php, db/cats_schema.sql (access_level/user), `_evidence/acl-summary.md`, all module docs |
| 15 | `docs/15-functional-specification.md` | ☑ | docs/modules/*.md, doc 05, acl-summary.md; spot-verified config.php, lib/{ParseUtility,License}.php |
| 16 | `docs/16-developer-setup.md` | ☑ | docker/docker-compose*.yml, Dockerfile, composer.json, phpunit.xml.dist, README-testing.md, test/{behat.yml,config.php}, .github/workflows/ci.yml, config.php, index.php |
| 17 | `docs/17-admin-configuration-guide.md` | ☑ | config.php (full), constants.php, docs/modules/settings.md, SettingsUI.php, lib/{Site,Users}.php |
| 18 | `docs/18-user-guide.md` | ☑ | docs/modules/*.md, doc 10, doc 09, constants.php; spot-verified candidates/joborders templates |
| 19 | `docs/19-upgrade-migration-history.md` | ☑ | db/upgrade-*.sql (all 7), db/cats_schema.sql, modules/install/ajax/ui.php, installwizard.php |
| 20 | `docs/20-security-maintainability-review.md` | ☑ | index.php, lib/{Session,DatabaseConnection,FileUtility,Users,Hooks,Attachments}.php, ajax.php, CareersUI.php, AttachmentsUI.php, WizardUI.php, config.php, Security.MD |

## Code-file accounting (final)

| Area | Files | Accounting |
|---|---|---|
| `modules/` controllers (`*UI.php` + install ajax/ui.php) | 21 modules | **read-full** — every controller's `handleRequest()` read in full for its per-module doc (docs/modules/*.md) |
| `modules/` templates / JS / dataGrids.php | many | **skimmed→read** — templates skimmed per module to confirm what each action renders; key ones read |
| `lib/*.php` | 74 | **read-full** for the ~35 classes central to docs 02/03/06/07/08/10/11/12/13/14/20 (Candidates, JobOrders, Companies, Contacts, Pipelines, ActivityEntries, Session, Users, ModuleUtility, UserInterface, Template, TemplateUtility, DatabaseConnection, Hooks, ACL, DataGrid, QueueProcessor, Mailer, AJAXInterface, Attachments, FileUtility, DocumentToText, Search, Site, SavedLists, ListEditor, ExtraFields, Calendar, JobOrderStatuses, Statistics, XmlJobExport, ParseUtility, License, CareerPortal, Questionnaire); **skimmed** for the remaining utility classes (cited where reached) |
| `src/OpenCATS/` (non-test, 9) | 9 | **read-full** — Entity/* and UI/* read for doc 06 |
| `src/OpenCATS/Tests/*` | 21 | **skimmed** — covered structurally for doc 16 (suites/bootstrap) |
| `db/*.sql` | 8 | **read-full** — cats_schema.sql (doc 04) and all 7 upgrade-*.sql (doc 19); zipcodes body confirmed data-only via grep |
| `ajax/*.php` | 20 | **read-full** — all 20 enumerated for doc 13 |
| `docker/`, `test/`, `.github/workflows/` | — | **read-full** for doc 16 |
| `config.php`, `constants.php`, `index.php`, `ajax.php`, `QueueCLI.php` | 5 | **read-full** across docs 01/03/12/16/17/20 |
| `lib/artichow/`, `lib/datagrid/`, vendor/assets | — | **not-relevant** — vendored charting/grid libs; named, not analyzed |

## Verification performed by the lead (not delegated)

Spot-checked load-bearing citations directly against source:
- CSRF block + careers/rss/xml exemption — `index.php:145-160` ✓
- `Hooks::get()` returns eval-able string, `' return true;'` default — `lib/Hooks.php:54-72` ✓
- `ACCESS_LEVEL_DEMO=350 / MULTI_SA=450 / ROOT=500` — `constants.php:79,81,82` ✓
- 11 `PIPELINE_STATUS_*` constants (NOSTATUS=0, NOCONTACT=100, CANDIDATE_REPLIED=250) — `constants.php:120-130` ✓
- **ACL dormant**: `class ACL_SETUP {...}` is inside the `/* ... Example: ... */` comment (opens `lib/ACL.php:20`, closes `:46`); `getAccessLevel()` returns `$defaultAccessLevel` when `!class_exists('ACL_SETUP')` — `lib/ACL.php:52-56` ✓
- `makeQueryString()` wraps `mysqli_real_escape_string` — `lib/DatabaseConnection.php:486,495` ✓
- `Pipelines::add()` INSERTs into `candidate_joborder` with a status column — `lib/Pipelines.php:61,98-102` ✓
- `access_level` seed has 100/200/300/400/500 only (no 350/450) — `db/cats_schema.sql:27…` ✓
