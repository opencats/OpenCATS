# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

OpenCATS is a PHP (7.4) candidate/applicant tracking system for recruiters. It is a long-lived legacy codebase (originating as "CATS" ~2005) with a thin modern layer added under `src/`. Most application logic lives in the legacy procedural/OO code under `lib/` and `modules/`; new code is expected in the PSR-4 `OpenCATS\` namespace under `src/`.

## Commands

### Dependencies
```bash
composer install                 # dev install
composer install --no-dev        # production install (releases ship without dev deps)
```

### Tests
PHPUnit unit tests run on the host; integration and Behat tests run in Docker (they need MariaDB).
```bash
# Unit tests (no DB needed) — runs on host
./vendor/bin/phpunit --testsuite UnitTests
./vendor/bin/phpunit --filter testMethodName path/to/SomeTest.php   # single test

# Integration + Behat (need the test DB stack)
cp test/config.php ./config.php && touch ./INSTALL_BLOCK
cd docker/ && docker compose -f docker-compose-test.yml up -d --build
docker compose -f docker-compose-test.yml exec -T --workdir /var/www/public php composer install --no-interaction --prefer-dist
docker compose -f docker-compose-test.yml exec php ./vendor/bin/phpunit --testsuite IntegrationTests
docker compose -f docker-compose-test.yml exec php ./vendor/bin/behat -c ./test/behat.yml
docker compose -f docker-compose-test.yml exec php ./vendor/bin/behat -c ./test/behat.yml --suite=security
```
- `UnitTests` and `IntegrationTests` directories are defined in `phpunit.xml.dist`, bootstrapped via `src/OpenCATS/Tests/bootstrap.php`. `config.php` is NOT loaded for unit tests (HTML_ENCODING is defined in phpunit.xml.dist instead).
- Integration tests use a disposable DB (`integrationtestdb`, port 3307) that `DatabaseTestCase.php` drops/recreates from `db/cats_schema.sql` each run. The functional/Behat DB is `opencatsdb` (port 3306), seeded with `db/cats_schema.sql` + `test/data/securityTests.sql`. See `README-testing.md`.
- Lint (what CI runs): `find src -name "*.php" -print0 | xargs -0 -n1 php -l`. CI (`.github/workflows/ci.yml`) only PHP-lints `src/`, not `lib/`/`modules/`.

### Run the app locally
```bash
cd docker/ && docker compose up --build   # web on :80/:443, phpMyAdmin on :8080
```
Copy/configure `config.php` (DB credentials, feature flags) before first run. The app shows the install wizard until an `INSTALL_BLOCK` file exists.

## Architecture

### Request flow (front controller + module dispatch)
1. `index.php` is the single web entry point. URLs are `index.php?m=<module>&a=<action>&...` (e.g. `?m=candidates&a=edit&candidateID=55`). `ajax.php` is the AJAX entry point (`f=<function>&...`), and `QueueCLI.php` is a cron-invoked CLI entry for the async queue.
2. `index.php` bootstraps core libs, starts the session (`$_SESSION['CATS']` is a `CATSSession`), enforces CSRF on POST (token in `$_POST['csrfToken']`), then calls `ModuleUtility::loadModule($_GET['m'])`.
3. `ModuleUtility` scans `modules/`, includes `modules/<name>/<Name>UI.php`, instantiates the `*UI` class, and calls `handleRequest()`.
4. Each `modules/<name>/<Name>UI.php` extends `UserInterface` (`lib/UserInterface.php`). `handleRequest()` reads the action via `getAction()` and `switch`es on it. Every case checks `getUserAccessLevel('<module>.<action>')` against an `ACCESS_LEVEL_*` constant before proceeding — replicate this guard when adding actions.

### Layers
- **`modules/<name>/`** — per-module UI controllers (`*UI.php`) + Smarty-style `.tpl` templates + module-specific JS. Controllers gather request input, call into `lib/` data classes, assign vars to a `Template`, and `$this->_template->display('./modules/<name>/X.tpl')`.
- **`lib/`** — the bulk of business logic and data access. Domain classes (e.g. `Candidates.php`, `JobOrders.php`, `Companies.php`, `Contacts.php`) take a `$siteID` in their constructor and use `DatabaseConnection::getInstance()` (mysqli singleton). Build queries with `$this->_db->makeQueryString(...)` for escaping. `lib/` also holds cross-cutting services: `Session`, `Users`, `ACL`, `Template`, `Hooks`, `Mailer`, `Search`, `DataGrid`, import/export, etc.
- **`src/OpenCATS/`** — the modern PSR-4 layer (`composer.json` autoload). `Entity/` has repository-pattern classes (e.g. `JobOrder` + `JobOrderRepository`); `UI/` has newer UI helpers. Prefer adding new, testable code here.

### Multi-tenancy
The system is multi-site: nearly all data is scoped by `siteID`, obtained from the session (`$_SESSION['CATS']->getSiteID()`) and passed into `lib/` domain classes. Queries must filter by `site_id`.

### Access levels (`constants.php`)
`ACCESS_LEVEL_DISABLED=0`, `READ=100`, `EDIT=200`, `DELETE=300`, `DEMO=350`, `SA=400` (super admin). Data-item type constants (`DATA_ITEM_CANDIDATE=100`, etc.) identify entity types across activity/history/attachments code.

### Hooks
`lib/Hooks.php` exposes extension points invoked via `eval(Hooks::get('SOME_HOOK'))` throughout `index.php` and module controllers. These let `optional-updates/`-style add-ons inject behavior without editing core flow.

## Conventions & gotchas
- `config.php` is committed with placeholder/dev values (DB creds, `PARSING_ENABLED`, `ENABLE_SPHINX`, antiword/pdftotext paths). It is environment config — don't treat committed values as production secrets, and don't commit real ones.
- Sphinx full-text search is optional (`ENABLE_SPHINX`); resume text extraction relies on external binaries (antiword, pdftotext, html2text, unrtf) configured by path in `config.php` and installed in the PHP Docker image.
- The career portal (`modules/careers`, `careers/`) is the main public-facing/XSS surface and is disabled by default; uploads are whitelisted by filetype. See `Security.MD`.
- Legacy code predates PSR autoloading: `lib/` classes are pulled in via explicit `include_once`. When wiring new code, follow the include chain already present in the relevant entry point or controller.
- DB schema lives in `db/cats_schema.sql`; `db/upgrade-*.sql` are historical migration scripts.
