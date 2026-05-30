# File Inventory (Phase 0)

Complete walk of the OpenCATS repository. Status flags: **read-full** (opened entirely this
session), **skimmed** (header/structure/grep inspected this session), **not-relevant** (asset,
vendor, or generated; will not be source-analyzed). Anything still **skimmed** at the end of
Phase 2 is acknowledged as such in `coverage.md`.

> All counts below were produced by directory listings + grep against the working tree on the
> `master` branch (HEAD `67b6007`). They are headline numbers for Phase 1 scoping; per-file
> line citations are added as each Phase 2 document is written.

## Headline counts

| Thing counted | Count | How measured |
|---|---|---|
| Application modules (`modules/*/`) | **21** | `ls -d modules/*/` |
| `lib/*.php` classes/files | **74** | `ls -1 lib/*.php` |
| `lib/` subdirectories (3rd-party/helpers) | 2 (`artichow/`, `datagrid/`) | `ls -d lib/*/` |
| `src/OpenCATS/` PHP files (entities, UI, tests) | **30** | `find src -name '*.php'` |
| `ajax/` endpoint handlers | **20** | `ls -1 ajax/*.php` |
| `CREATE TABLE` statements in `db/cats_schema.sql` | **55** | `grep -ciE '^\s*CREATE TABLE'` |
| `db/upgrade-*.sql` migration scripts | 6 (+`upgrade-zipcodes.sql`) | `ls db/` |
| `ACCESS_LEVEL_*` constants (`constants.php`) | **9** | `grep "define('ACCESS_LEVEL_"` |
| `DATA_ITEM_*` constants (`constants.php`) | **9** | `grep "define('DATA_ITEM_"` |
| `SETTINGS_*` constants (`constants.php`) | 4 | `grep "define('SETTINGS_"` |
| Distinct `Hooks::get('KEY')` keys used | **230** | `grep -rhoE "Hooks::get\('[A-Z_]+'\)" \| sort -u \| wc -l` |
| `Hooks::get()` call sites (non-distinct) | 261 | same grep without `sort -u` |
| Application version (`CATS_VERSION`) | `0.9.7.4` | `constants.php:45` |

Verified key constant values (constants.php):
`ACCESS_LEVEL_DELETED=-100, DISABLED=0, READ=100, EDIT=200, DELETE=300, DEMO=350, SA=400, MULTI_SA=450, ROOT=500`
`DATA_ITEM_CANDIDATE=100, COMPANY=200, CONTACT=300, JOBORDER=400, BULKRESUME=500, USER=600, LIST=700, PIPELINE=800, DUPLICATE=900`
`SETTINGS_MAILER=1, CALENDAR=2, EEO=3, CAREER_PORTAL=4`

---

## Root entry points & config

| File | Role | Status |
|---|---|---|
| `index.php` | Front controller. Bootstraps libs, starts `CATSSession`, enforces CSRF on POST, dispatches to `ModuleUtility::loadModule($_GET['m'])`. | read-full |
| `ajax.php` | AJAX front controller (`f=<function>`), routes to `lib/AJAXInterface.php` + `ajax/*.php`. | read-full |
| `QueueCLI.php` | CLI entry point invoked by cron to process the async queue via `lib/QueueProcessor.php`. | skimmed |
| `config.php` | Deployment configuration: DB creds, feature flags (`PARSING_ENABLED`, `ENABLE_SPHINX`, `SSL_ENABLED`), external binary paths, mail. | skimmed |
| `constants.php` | Global `define()`s: `CATS_VERSION`, `ACCESS_LEVEL_*`, `DATA_ITEM_*`, `SETTINGS_*`, pagination, encodings, module path. | skimmed |
| `installwizard.php` | Standalone install wizard (DB setup, writes `config.php`/`INSTALL_BLOCK`). | not-yet-read |
| `installtest.php` | Pre-install environment checks. | not-yet-read |
| `rebuild_old_docs.php` | One-off maintenance script (resume text rebuild). | not-yet-read |
| `Error.tpl` | Top-level fatal error template. | not-relevant |
| `.htaccess` | Apache rules; upload-folder protection (see Security.MD). | skimmed |
| `composer.json` / `composer.lock` | PHP deps: php ^7.4, ckeditor, sphinxsearch-api, phpmailer, fpdf; dev: behat/mink/phpunit. PSR-4 `OpenCATS\` → `src/OpenCATS/`. | read-full |
| `phpunit.xml.dist` | Test suites `UnitTests`/`IntegrationTests`; bootstrap `src/OpenCATS/Tests/bootstrap.php`. | read-full |
| `README.md`, `README-testing.md`, `Security.MD`, `CHANGELOG.MD`, `LICENSE.md` | Docs. | read-full (first 3), skimmed (rest) |
| `CLAUDE.md` | Repo map for Claude Code (to be verified against source). | read-full |

## `modules/` — 21 application modules

Each module is loaded by `ModuleUtility::loadModule()`, which includes `modules/<name>/<Class>.php`
and calls `handleRequest()`. The controller class extends `UserInterface` (except where noted).

| Module dir | Files | Main controller class (file) | Status |
|---|---|---|---|
| `activity` | 5 | `ActivityUI` (`ActivityUI.php`) | skimmed |
| `attachments` | 1 | `AttachmentsUI` (`AttachmentsUI.php`) | skimmed |
| `calendar` | 8 | `CalendarUI` (`CalendarUI.php`) | skimmed |
| `candidates` | 26 | `CandidatesUI` (`CandidatesUI.php`) | read-full (handleRequest + header) |
| `careers` | 7 | `CareersUI` (`CareersUI.php`) — public career portal | skimmed |
| `companies` | 11 | `CompaniesUI` (`CompaniesUI.php`) | skimmed |
| `contacts` | 13 | `ContactsUI` (`ContactsUI.php`) | skimmed |
| `export` | 1 | `ExportUI` (`ExportUI.php`) | skimmed |
| `graphs` | 1 | `GraphsUI` (`GraphsUI.php`) | skimmed |
| `home` | 6 | `HomeUI` (`HomeUI.php`) — also defines dashboard DataGrid classes | skimmed |
| `import` | 20 | `ImportUI` (`ImportUI.php`), `Import.php` | skimmed |
| `install` | 10 | `CATSUI` (`CATSUI.php`) — note: class name ≠ module name | skimmed |
| `joborders` | 13 | `JobOrdersUI` (`JobOrdersUI.php`) — also DataGrid subclasses | skimmed |
| `lists` | 6 | `ListsUI` (`ListsUI.php`) | skimmed |
| `login` | 7 | `LoginUI` (`LoginUI.php`) | skimmed |
| `queue` | 5 | `QueueUI` (`QueueUI.php`) | skimmed |
| `reports` | 9 | `ReportsUI` (`ReportsUI.php`) | skimmed |
| `rss` | 1 | `RssUI` (`RssUI.php`) | skimmed |
| `settings` | 36 | `SettingsUI` (`SettingsUI.php`) | skimmed |
| `wizard` | 4 | `WizardUI` (`WizardUI.php`) | skimmed |
| `xml` | 2 | `XmlUI` (`XmlUI.php`) | skimmed |

Module dirs also contain `.tpl` templates (Smarty-style, rendered by `lib/Template.php`),
module-specific `*.js`, and some contain `dataGrids.php` (DataGrid column definitions).

## `lib/` — 74 PHP classes (business logic + services)

Domain/data-access (constructor takes `$siteID`, uses `DatabaseConnection::getInstance()`):
`Candidates.php`, `JobOrders.php`, `Companies.php`, `Contacts.php`, `Pipelines.php`,
`ActivityEntries.php`, `Attachments.php`, `Calendar.php`, `Tags.php`, `SavedLists.php`,
`Questionnaire.php`, `Users.php`, `Site.php`, `Statistics.php`, `History.php`, `LoginActivity.php`,
`EmailTemplates.php`, `ExtraFields.php`, `JobOrderStatuses.php`, `JobOrderTypes.php`,
`CareerPortal.php`, `Search.php`, `DatabaseSearch.php`, `Dashboard.php`, `MRU.php`,
`License.php` — **status: skimmed (signatures/constructor pattern); to read-full in docs 06/07.**

Core framework/services:
`ModuleUtility.php` (read-full), `UserInterface.php` (skimmed), `Session.php` (skimmed),
`Template.php` (skimmed), `TemplateUtility.php`, `Hooks.php` (read-full), `ACL.php`,
`DatabaseConnection.php` (skimmed), `AJAXInterface.php`, `CommonErrors.php`, `CATSUtility.php`,
`QueueProcessor.php`, `Mailer.php`, `DataGrid.php`, `Pager.php`, `WebForm.php`, `Wizard.php`.

Utilities/helpers:
`ArrayUtility.php`, `StringUtility.php`, `DateUtility.php`, `FileUtility.php`, `HashUtility.php`,
`ResultSetUtility.php`, `SystemUtility.php`, `ParseUtility.php`, `ImportUtility.php`,
`AddressParser.php`, `VCard.php`, `Width.php`, `InfoString.php`, `BrowserDetection.php`,
`DocumentToText.php`, `FileCompressor.php`, `ZipLookup.php`, `LDAP.php`, `HttpLogger.php`,
`SystemInfo.php`, `NewVersionCheck.php`, `InstallationTests.php`, `GraphGenerator.php`,
`Graphs.php`, `Export.php`, `XmlJobExport.php`, `ListEditor.php`, `ImportableEntity.php`,
`CandidatesImport.php`, `CompaniesImport.php`, `ContactsImport.php`.

Non-PHP / vendored: `lib/artichow/` (graph rendering lib), `lib/datagrid/`, `lib/mime.types`,
`lib/IFrameBlank.html` — **status: not-relevant** (vendor/asset; cited only if reached).

## `src/OpenCATS/` — modern PSR-4 layer (30 PHP files)

| Path | Role | Status |
|---|---|---|
| `Entity/JobOrder.php`, `JobOrderRepository.php`, `JobOrderRepositoryException.php` | Repository-pattern job order entity. | skimmed |
| `Entity/Company.php`, `CompanyRepository.php`, `CompanyRepositoryException.php` | Repository-pattern company entity. | skimmed |
| `UI/QuickActionMenu.php`, `CandidateQuickActionMenu.php`, `CandidateDuplicateQuickActionMenu.php` | Newer UI menu helpers. | skimmed |
| `Tests/bootstrap.php` | PHPUnit bootstrap. | read-full |
| `Tests/UnitTests/*` (13 files) | Unit tests (no DB). | skimmed |
| `Tests/IntegrationTests/*` (`DatabaseTestCase.php`, `DatabaseConnectionTest.php`, `DatabaseSearchTest.php`) | DB integration tests. | skimmed |
| `Tests/Behat/*`, `Tests/Fixtures/SampleText.txt` | Behat Mink driver glue + fixtures. | skimmed |

## `db/`

| File | Role | Status |
|---|---|---|
| `cats_schema.sql` | Base schema, 55 `CREATE TABLE`. | read-full (doc 04) |
| `upgrade-0.5.0-0.5.1.sql` … `upgrade-0.6.x-0.7.0.sql`, `upgrade-0.9.4-0.9.5.sql` | Versioned migrations. | to read-full (doc 19) |
| `upgrade-zipcodes.sql` | Zipcode data migration. | skimmed |
| `cats_testdata.bak` | Binary/dump test data. | not-relevant |

## `ajax/` — 20 endpoint handlers (invoked via `ajax.php`)

`deleteActivity.php`, `editActivity.php`, `getCandidateIdByEmail.php`, `getCandidateIdByPhone.php`,
`getCompanyContacts.php`, `getCompanyLocation.php`, `getCompanyLocationAndDepartments.php`,
`getCompanyNames.php`, `getDataGridPager.php`, `getDataItemJobOrders.php`, `getParsedAddress.php`,
`getPipelineDetails.php`, `getPipelineJobOrder.php`, `getReportHTML.php`, `replaceTemplateTags.php`,
`setCandidateJobOrderRating.php`, `setColumnWidth.php`, `showTemplate.php`, `testEmailSettings.php`,
`zipLookup.php` — **status: skimmed; to read-full in doc 13.**

## `docker/`, `test/`, scripts, other

| Path | Role | Status |
|---|---|---|
| `docker/docker-compose.yml` | Dev stack: nginx (`:80/:443`), php-fpm, mariadb (`:3306`), phpMyAdmin (`:8080`). | read-full |
| `docker/docker-compose-test.yml` | CI/test stack: `opencatsdb` (3306) + disposable `integrationtestdb` (3307). | to read-full (doc 16) |
| `docker/php/Dockerfile` | php:7.4-fpm-alpine + antiword/poppler/html2text/unrtf + mysqli/gd/soap/zip/ldap/mcrypt + composer. | read-full |
| `docker/php/scripts/install-composer.sh` | Composer installer. | not-relevant |
| `test/behat.yml`, `test/config.php` | Behat config + test app config. | skimmed |
| `test/features/*.feature` (8) | Gherkin features incl. security suites. | skimmed |
| `test/data/securityTests.sql`, `test/scripts/*` | Seed data + helpers (`waitForDb.php`). | skimmed |
| `careers/index.php`, `rss/index.php`, `xml/index.php` | Public entry shims that set flags and re-enter `index.php`. | to read-full (doc 13) |
| `config/sphinx`, `optional-updates/latest-sphinx-search/` | Sphinx config + optional add-on (`Search.php` override). | skimmed (doc 11/13) |
| `scripts/*.sh`, `scripts/*.php` | Ops scripts: sphinx reindex/rotate, backup, code counting. | not-relevant (skim doc 13/16) |
| `wsdl/*.wsdl` (`keyCheck`, `parse`, `status`) | SOAP WSDLs (parsing/licensing service contracts). | skimmed (doc 13) |
| `images/`, `js/`, `*.css`, `attachments/`, `temp/`, `upload/` | Static assets / runtime dirs. | not-relevant |

## Notable findings recorded during inventory (to confirm in Phase 2)

- The `install` module's controller class is **`CATSUI`** (in `modules/install/CATSUI.php`), not
  `InstallUI` — class name diverges from module name.
- `lib/Hooks.php` is tiny (75 lines): `Hooks::get($hookName)` returns a **string of PHP code**
  pulled from `$_SESSION['hooks']`, terminated with `' return true;'`. Call sites run it via
  `eval(Hooks::get('KEY'))` — security-relevant for doc 11/20 (lib/Hooks.php:52-72).
- Modules and their hooks are discovered by `ModuleUtility::_refreshModuleList()` and cached in
  `$_SESSION['modules']` / `$_SESSION['hooks']` (and optionally `modules.cache` when
  `CACHE_MODULES`) — lib/ModuleUtility.php.
- `home/HomeUI.php` and `joborders/JobOrdersUI.php` each define multiple classes (the controller
  plus `DataGrid` subclasses) in one file.
- Schema contains an apparent typo table name `candidate_jobordrer_status_type` alongside
  `candidate_joborder_status` and `candidate_joborder_status_history` — to verify in doc 04.
