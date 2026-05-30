# 16 — Developer Setup

This document describes how to set up OpenCATS for local development and testing **using only the build, container, and CI tooling that actually ships in this repository**. Every command and value below is cited to a real file and line. Where the repo's own files disagree with each other (and they do), the discrepancy is flagged rather than smoothed over.

OpenCATS is a legacy PHP 7.4 application. The PHP runtime constraint is declared in `composer.json` (`"php": "^7.4"`, composer.json:6) and the CI matrix pins exactly `'7.4'` (.github/workflows/ci.yml:21).

---

## Prerequisites

| Requirement | Source of truth | Notes |
|---|---|---|
| PHP 7.4 | composer.json:6 (`"php": "^7.4"`); ci.yml:21 (`php-version: ['7.4']`) | The Docker image is `php:7.4-fpm-alpine` (docker/php/Dockerfile:1). |
| Composer | composer.json (project root); installed into the PHP image at docker/php/Dockerfile:42-46 | CI uses `composer:v2` (ci.yml:31). |
| Docker + `docker compose` | docker/docker-compose.yml, docker/docker-compose-test.yml | Required for integration/Behat tests and the easiest way to run the app. |

### PHP extensions and binaries baked into the PHP image

The development/test PHP container (`docker/php/Dockerfile`) is the canonical runtime. It installs:

- **PHP extensions** (docker/php/Dockerfile:38-40):
  - `mcrypt` via PECL `mcrypt-1.0.9` (docker/php/Dockerfile:38)
  - `gd` configured `--with-freetype --with-jpeg` (docker/php/Dockerfile:39-40)
  - `mysqli`, `gd`, `soap`, `zip`, `ldap` via `docker-php-ext-install` (docker/php/Dockerfile:40)
- **Resume/document text-extraction binaries** (used by the resume parser): `antiword`, `html2text`, `poppler-utils` (provides `pdftotext`) (docker/php/Dockerfile:5-11), and `unrtf` built from source `unrtf-0.21.10` (docker/php/Dockerfile:24-31).
- **Helper tools**: `composer` moved to `/usr/local/bin/composer` (docker/php/Dockerfile:42-46) and `dockerize` `v0.2.0` (docker/php/Dockerfile:47-49).

> The host CI runner additionally installs PHP extensions `gd, zlib` when running unit tests directly on the host (ci.yml:32) — see [Testing](#testing).

---

## Dependency install

Dependencies and PSR-4 autoloading are defined in `composer.json`.

- **Runtime requirements** (composer.json:5-11): `ckeditor/ckeditor`, `neutron/sphinxsearch-api`, `phpmailer/phpmailer`, `setasign/fpdf`.
- **Dev-only requirements** (composer.json:12-19): `behat/behat`, `behat/mink`, `friends-of-behat/mink-extension`, `behat/mink-browserkit-driver`, `behat/mink-selenium2-driver`, `phpunit/phpunit`. These are the test toolchain only.
- **Autoload** (composer.json:20-24): PSR-4 maps the `OpenCATS\` namespace to `src/OpenCATS/`. (Note: the legacy `modules/`, `lib/`, and root scripts are **not** autoloaded — they use manual `include`/`require`.)

### Development install (includes dev/test tools)

```bash
composer install
```

CI runs this exact form (with flags) on the host before linting and unit tests:

```bash
composer install --no-progress --prefer-dist
```

(ci.yml:42)

Inside the test PHP container, README-testing.md prescribes:

```bash
docker compose -f docker-compose-test.yml exec -T --workdir /var/www/public php composer install --no-interaction --prefer-dist
```

(README-testing.md, "Install PHP dependencies (Composer)")

### Production install (no dev tools)

For production deployments, install **without** dev dependencies:

```bash
composer install --no-dev
```

`Security.MD` states the dev packages are needed only for testing and should be removed from production systems: "if you pull in dependencies by using composer (rather than use the releases) — ensure you use the `--no-dev` option" (Security.MD, "Composer" section). The CI release job builds its archive from a checkout and explicitly excludes `docker/`, `test/`, `reports/`, `.github/`, and `phpunit.xml` (ci.yml:131) — it does **not** run `composer install --no-dev` itself, so released archives ship whatever `vendor/` is checked out. See [Unverified](#unverified--open-questions).

---

## Running the dev stack

The development stack is `docker/docker-compose.yml`. It defines five services:

| Service | Container name | Image | Ports | Source |
|---|---|---|---|---|
| `opencats` (web/nginx) | `opencats_web` | `prooph/nginx:www` | `80:80`, `443:443` | docker-compose.yml:2-9 |
| `php` (FPM) | `opencats_php` | built from `docker/php/Dockerfile` | — | docker-compose.yml:11-16 |
| `opencatsdata` (code volume) | `opencats_data` | `busybox` | — | docker-compose.yml:18-22 |
| `opencatsdb` (MariaDB) | `opencats_mariadb` | `mariadb` | `3306:3306` | docker-compose.yml:24-34 |
| `phpmyadmin` | `opencats_phpmyadmin` | `phpmyadmin/phpmyadmin` | `8080:80` | docker-compose.yml:36-44 |

Key wiring:
- The application code is mounted via the `opencatsdata` busybox volume container (`..:/var/www/public`, docker-compose.yml:21) and shared into `opencats` and `php` with `volumes_from` (docker-compose.yml:8-9, 15-16).
- `opencatsdb` is seeded from `../test/data` mounted into `/docker-entrypoint-initdb.d` (docker-compose.yml:32) and persists data to `./persist/mysql` (docker-compose.yml:33).
- `opencatsdb` MariaDB env: `MYSQL_ROOT_PASSWORD=root`, `MYSQL_USER=dev`, `MYSQL_PASSWORD=dev`, `MYSQL_DATABASE=cats` (docker-compose.yml:28-31).
- phpMyAdmin connects to `db` (the `opencatsdb` link) as `dev`/`dev` (docker-compose.yml:40-44).

### Workflow

From the `docker/` directory:

```bash
cd docker/
docker compose up -d --build
```

(`-f docker-compose-test.yml` is omitted here, so `docker compose` uses the default `docker-compose.yml`. The `--build` / `-d` form mirrors the documented test workflow at README-testing.md, "Start the containers".)

The app is then served on `http://localhost` (port 80) / `https://localhost` (port 443), and phpMyAdmin on `http://localhost:8080`.

> Note: the seeded `MYSQL_DATABASE` for the dev DB is `cats` (docker-compose.yml:31), while the on-disk `config.php` default `DATABASE_NAME` is `cats_dev` (config.php:43). These do not match out of the box — adjust `config.php` (or the compose env) to agree. See [Configuration & install](#configuration--install).

---

## Configuration & install

### config.php and DB constants

OpenCATS reads its configuration from `config.php` at the repo root, included first thing in `index.php` (`include_once('./config.php');`, index.php:42). The database constants (config.php:40-43) ship as:

```php
define('DATABASE_USER', 'cats');       // config.php:40
define('DATABASE_PASS', 'password');   // config.php:41
define('DATABASE_HOST', 'localhost');  // config.php:42
define('DATABASE_NAME', 'cats_dev');   // config.php:43
```

`LEGACY_ROOT` defaults to `.` (config.php:34-36). Auth mode defaults to `sql` (config.php, `AUTH_MODE`).

For the **test/Docker stack**, the repo ships a ready-made `test/config.php` whose DB constants point at the containerized MariaDB (test/config.php):

```php
define('DATABASE_USER', 'dev');
define('DATABASE_PASS', 'dev');
define('DATABASE_HOST', 'opencatsdb');
define('DATABASE_NAME', 'cats_test');
```

The documented testing flow copies this file over the root config (README-testing.md, "Prepare the environment"; ci.yml:60):

```bash
cp test/config.php ./config.php
```

### The INSTALL_BLOCK gate

`index.php` gates the application behind a sentinel file named `INSTALL_BLOCK` (index.php:44-48):

```php
if (!file_exists('INSTALL_BLOCK') && !isset($_POST['performMaintenence']))
{
    include(LEGACY_ROOT . '/modules/install/notinstalled.php');
    die();
}
```

If `INSTALL_BLOCK` does **not** exist, every request is short-circuited into the "not installed" page. There are two ways past this gate:

1. **Browser install wizard** — the repo ships `installwizard.php` and `installtest.php` at the root (both present, executable). The wizard is the supported, end-user install path that walks through environment checks and DB setup. (The detailed wizard steps live in the wizard itself, `installwizard.php`, and at documentation.opencats.org per README.md.)

2. **Manually create the sentinel** — for development/CI you bypass the wizard by touching the file (README-testing.md, "Prepare the environment"; ci.yml:61):

   ```bash
   touch ./INSTALL_BLOCK
   ```

   This is exactly what CI does after copying `test/config.php` — it pre-configures the DB by hand and creates `INSTALL_BLOCK` so the app considers itself installed without running the wizard.

---

## Testing

The test stack is `docker/docker-compose-test.yml`. It is separate from the dev stack and adds two MariaDB instances plus a Selenium node.

### Two databases (isolation by design)

README-testing.md, "Overview of Databases":

| DB | Host/Port | Purpose | Seeded / lifecycle |
|---|---|---|---|
| **`opencatsdb`** | container `opencats_test_mariadb`, port `3306` | Primary functional DB for **Behat** suites and manual dev | Pre-seeded from `db/cats_schema.sql` and `test/data/securityTests.sql` via `initdb.d` (docker-compose-test.yml:48-49; README-testing.md) |
| **`integrationtestdb`** | port `3307:3306` | Disposable sandbox for **PHPUnit Integration tests** | Dropped and recreated on every run by `DatabaseTestCase` (README-testing.md; see below) |

Important compose details for the test DBs:
- `opencatsdb` (test): `mariadb:10.7`, env `MYSQL_ROOT_PASSWORD=dev`, `MYSQL_USER=dev`, `MYSQL_PASSWORD=dev`, `MYSQL_DATABASE=cats_test` (docker-compose-test.yml:32-37); has a `mysqladmin ping` healthcheck (docker-compose-test.yml:38-43). Its published port is `3306` (docker-compose-test.yml:31).
- `integrationtestdb`: `mariadb:10.7`, port `3307:3306` (docker-compose-test.yml:58), `MYSQL_DATABASE=cats_integrationtest` (docker-compose-test.yml:62), same `dev`/`dev` creds and healthcheck (docker-compose-test.yml:60-69).
- `selenium`: `mlespiau/standalone-chrome:2.53.1-cd2.23` on port `4444` (docker-compose-test.yml:71-81), used by Behat's `selenium2` session at `http://selenium:4444/wd/hub` (test/behat.yml).

**The disposable recreation** happens in `DatabaseTestCase::setUp()` (src/OpenCATS/Tests/IntegrationTests/DatabaseTestCase.php:10-43): it connects to host `integrationtestdb` as `dev`/`dev` (DatabaseTestCase.php:25-29), runs `DROP DATABASE IF EXISTS cats_integrationtest` then `CREATE DATABASE ...` (DatabaseTestCase.php:36-37), selects it (DatabaseTestCase.php:40), and rebuilds the schema from `db/cats_schema.sql` (DatabaseTestCase.php:42). It also drops the DB again on teardown (DatabaseTestCase.php:91).

### PHPUnit suites and bootstrap

`phpunit.xml.dist` defines two suites (phpunit.xml.dist:3-8):
- `UnitTests` → `src/OpenCATS/Tests/UnitTests` (phpunit.xml.dist:4-6)
- `IntegrationTests` → `src/OpenCATS/Tests/IntegrationTests` (phpunit.xml.dist:7-9)

Bootstrap: `src/OpenCATS/Tests/bootstrap.php` (phpunit.xml.dist:2) — it loads `vendor/autoload.php` and defines `LEGACY_ROOT='.'` (bootstrap.php:3-6). `HTML_ENCODING` is defined as `UTF-8` in the PHPUnit config because `config.php` is not loaded for unit tests (phpunit.xml.dist:10-13).

> **Unit tests run on the host with no database.** `config.php` is intentionally not loaded for them (phpunit.xml.dist comment, line 11), which is why CI runs `UnitTests` directly on the host runner with no Docker (ci.yml:54-55). **Integration and Behat tests require Docker** (the two MariaDB containers + Selenium).

### Commands

**Prepare environment** (host; README-testing.md, "Prepare the environment"; ci.yml:60-61):

```bash
cp test/config.php ./config.php
touch ./INSTALL_BLOCK
```

**Start the test containers** (README-testing.md, "Start the containers"):

```bash
cd docker/
docker compose -f docker-compose-test.yml up -d --build
```

**Install dependencies in the container** (README-testing.md):

```bash
docker compose -f docker-compose-test.yml exec -T --workdir /var/www/public php composer install --no-interaction --prefer-dist
```

**PHPUnit — Unit tests** (host, no DB; README-testing.md "Run the suites"; CI form at ci.yml:55):

```bash
# Inside the container (README-testing.md):
docker compose -f docker-compose-test.yml exec php ./vendor/bin/phpunit --testsuite UnitTests
# On the host (CI, ci.yml:55):
./vendor/bin/phpunit --log-junit reports/unit-report.xml --testsuite UnitTests
```

**PHPUnit — Integration tests** (needs `integrationtestdb`; README-testing.md; CI form at ci.yml:82):

```bash
# README-testing.md:
docker compose -f docker-compose-test.yml exec php ./vendor/bin/phpunit --testsuite IntegrationTests
# CI (ci.yml:82):
docker compose -f docker-compose-test.yml exec -T --workdir /var/www/public php \
  ./vendor/bin/phpunit --log-junit /var/www/public/reports/integration-report.xml --testsuite IntegrationTests
```

**Behat** — config is `test/behat.yml` (test/behat.yml). It defines two suites:
- `default` → filters tag `@core`, context `FeatureContext` (test/behat.yml)
- `security` → filters tag `@security`, context `SecurityContext` (test/behat.yml)

Base URL `http://opencats`, default session `browserkit`, JS session `selenium2` (test/behat.yml).

```bash
# README-testing.md (runs the default suite via -c):
docker compose -f docker-compose-test.yml exec php ./vendor/bin/behat -c ./test/behat.yml

# CI runs each suite explicitly (ci.yml:85-86):
docker compose -f docker-compose-test.yml exec -T --workdir /var/www/public php \
  ./vendor/bin/behat -v -c ./test/behat.yml --suite="default" --format=progress
docker compose -f docker-compose-test.yml exec -T --workdir /var/www/public php \
  ./vendor/bin/behat -v -c ./test/behat.yml --suite="security" --format=progress
```

**Shut down** (CI, ci.yml:116):

```bash
cd docker && docker compose -f docker-compose-test.yml down
```

> Troubleshooting (README-testing.md, "Troubleshooting"): if tests can't connect, confirm `integrationtestdb` is healthy (CI uses `mysqladmin ping`). PHPUnit may mark empty placeholder tests as "Risky" — these do not fail the build.

---

## CI pipeline

`.github/workflows/ci.yml` ("CI/CD Pipeline") runs on pushes to `master`, `develop`, `feature/**`, tags `v*`, PRs to `master`/`develop`, and manual dispatch (ci.yml:3-9). It has two jobs.

### Job `tests` (matrix PHP 7.4, ci.yml:12-116)

In order:
1. **Checkout** (ci.yml:24-25) and **Setup PHP** 7.4 with `composer:v2` and extensions `gd, zlib` (ci.yml:27-32).
2. **Cache Composer** packages (ci.yml:34-39).
3. **Install Dependencies**: `composer install --no-progress --prefer-dist` (ci.yml:41-42).
4. **PHP Syntax Check (Lint)**: `find src -name "*.php" -print0 | xargs -0 -n1 php -l` (ci.yml:44-45). (Lints only `src/`, not legacy `modules/`/`lib/`.)
5. **Composer Security Audit**: `composer audit || true` — non-blocking so legacy vulnerabilities don't fail the build (ci.yml:47-49).
6. **Create Reports Directory**: `mkdir -p reports/behat-default reports/behat-security test/screenshots` (ci.yml:51-52).
7. **Run PHPUnit (Unit Tests)** on host: `./vendor/bin/phpunit --log-junit reports/unit-report.xml --testsuite UnitTests` (ci.yml:54-55).
8. **Run Integration Tests (Docker)** (ci.yml:57-87): copies `test/config.php`→`config.php`, `touch INSTALL_BLOCK`, `docker compose -f docker-compose-test.yml up -d`, waits for **both** DB containers to report `healthy` (ci.yml:66-72), pre-cleans `cats_integrationtest` (ci.yml:75), runs the PHPUnit `IntegrationTests` suite (ci.yml:82), then the Behat `default` and `security` suites (ci.yml:85-86).
9. **Docker Diagnostics** on failure (ci.yml:89-94), **Publish Test Report** via JUnit action — `fail_on_failure: false` (ci.yml:96-104), **Upload Behat Screenshots** on failure (ci.yml:106-112), **Shutdown Docker** always (ci.yml:114-116).

### Job `release` (ci.yml:118-140)

Runs only for tag pushes `refs/tags/v*` and `needs: tests` (ci.yml:120-121). It zips the checkout into `opencats-<tag>.zip`, excluding `*.git*`, `docker/*`, `test/*`, `reports/*`, `.github/*`, `phpunit.xml` (ci.yml:129-131), and creates a GitHub release with the `gh` CLI (ci.yml:133-140).

---

## Ports reference table

| Port | Service | Stack | Source |
|---|---|---|---|
| `80` | nginx web (HTTP) | dev + test | docker-compose.yml:6 / docker-compose-test.yml:6 |
| `443` | nginx web (HTTPS) | dev + test | docker-compose.yml:7 / docker-compose-test.yml:7 |
| `3306` | functional MariaDB (`opencatsdb`) | dev (`cats`) / test (`cats_test`) | docker-compose.yml:27 / docker-compose-test.yml:31 |
| `3307` | integration MariaDB (`integrationtestdb`, disposable `cats_integrationtest`) | test only | docker-compose-test.yml:58 |
| `8080` | phpMyAdmin | dev only | docker-compose.yml:39 |
| `4444` | Selenium standalone Chrome | test only | docker-compose-test.yml:81 |

---

## Source evidence

- **PHP version**: composer.json:6 (`"php": "^7.4"`); ci.yml:21 (matrix `'7.4'`); docker/php/Dockerfile:1 (`php:7.4-fpm-alpine`).
- **PHP image build**: docker/php/Dockerfile — system pkgs (5-11), build deps (14-22), `unrtf` from source (24-31), PHP exts `mcrypt`/`gd`/`mysqli`/`soap`/`zip`/`ldap` (38-40), composer (42-46), dockerize (47-49).
- **Composer**: composer.json — runtime require (5-11), dev require (12-19), PSR-4 `OpenCATS\` → `src/OpenCATS/` (20-24).
- **`--no-dev` for production**: Security.MD ("Composer" section).
- **Dev compose** `docker/docker-compose.yml`: services & ports (2-44); dev DB env `cats`/`dev`/`dev`/root (28-31).
- **Test compose** `docker/docker-compose-test.yml`: `opencatsdb` `mariadb:10.7` + seeds + healthcheck (29-49), `integrationtestdb` `3307:3306` (52-69), `selenium` `4444` (71-81).
- **config.php DB defaults**: config.php:40-43; `LEGACY_ROOT` config.php:34-36.
- **test/config.php**: DB → `dev`/`dev`/`opencatsdb`/`cats_test`.
- **INSTALL_BLOCK gate**: index.php:42 (`include_once('./config.php')`), index.php:44-48 (gate + `notinstalled.php`).
- **Install paths**: `installwizard.php`, `installtest.php` (repo root, both present); `touch ./INSTALL_BLOCK` (README-testing.md; ci.yml:61).
- **PHPUnit**: phpunit.xml.dist — bootstrap (2), suites (3-9), `HTML_ENCODING` const (10-13); bootstrap.php:3-6.
- **Disposable integration DB**: src/OpenCATS/Tests/IntegrationTests/DatabaseTestCase.php:10-43, 91.
- **Behat config**: test/behat.yml — `default`/`security` suites, tags `@core`/`@security`, base_url `http://opencats`, selenium2 `http://selenium:4444/wd/hub`.
- **Test commands**: README-testing.md ("Running Tests Locally"); CI forms at ci.yml:55, 82, 85-86, 116.
- **CI pipeline**: ci.yml — lint (44-45), audit (47-49), unit (54-55), integration+behat (57-87), release (118-140).

---

## Unverified / open questions

- **config.php vs dev DB mismatch.** The on-disk `config.php` defaults to host `localhost` / DB `cats_dev` (config.php:42-43), but the dev compose seeds DB `cats` on the `opencatsdb` host with user `dev` (docker-compose.yml:28-31). Running the dev stack as-shipped will not connect without editing `config.php` (or copying `test/config.php`). The repo provides no script that reconciles these for the *dev* stack — only the *test* flow copies `test/config.php` (README-testing.md; ci.yml:60).
- **No documented `docker compose up` for the dev stack inside this repo.** README-testing.md documents only the `-f docker-compose-test.yml` workflow. The plain `docker compose up -d --build` for `docker-compose.yml` is inferred by analogy; end-user install docs live at documentation.opencats.org (README.md), outside this repo.
- **Release archive may include dev dependencies.** The `release` job zips the checkout and excludes `test/`, `docker/`, etc. (ci.yml:131) but never runs `composer install --no-dev`. Whether shipped archives are dev-free depends on the `vendor/` state at tag time; Security.MD's `--no-dev` guidance targets manual composer users, not this job. Not verified what `vendor/` contains at release time.
- **Browser wizard internals not summarized here.** `installwizard.php` (50 KB) and `installtest.php` exist and are the supported install UI, but their step-by-step behavior was not read in full for this doc; treat documentation.opencats.org as authoritative for end-user install.
- **`phpunit.xml` vs `phpunit.xml.dist`.** Only `phpunit.xml.dist` is present in-repo; the release exclude list references `phpunit.xml` (ci.yml:131). No committed `phpunit.xml` override exists — PHPUnit falls back to the `.dist` file.
- **MariaDB image pinning differs between stacks.** Dev `opencatsdb` uses unpinned `mariadb` (docker-compose.yml:26); test stack pins `mariadb:10.7` (docker-compose-test.yml:30, 59). Dev behavior depends on whatever `mariadb:latest` resolves to.
