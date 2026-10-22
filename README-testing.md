# CI/CD Testing Infrastructure Notes

## Overview of Databases
This project uses two distinct MariaDB instances during the testing phase to ensure data isolation:

### 1. opencatsdb (Port 3306)
* **Purpose:** Primary application database for functional testing.
* **Data:** Pre-seeded with `db/cats_schema.sql` and `test/data/securityTests.sql` via Docker's `initdb.d`.
* **Used By:** Manual development, Behat (Gherkin/Selenium) suites.

### 2. integrationtestdb (Port 3307)
* **Purpose:** A "Disposable Sandbox" for PHPUnit Integration Tests.
* **Behavior:** The `DatabaseTestCase.php` class drops and recreates this database for every test run to ensure a clean schema build from `db/cats_schema.sql`.

---

## Running Tests Locally
To mirror the CI environment on your machine:

1. **Prepare the environment:**
   ```bash
   cp test/config.php ./config.php
   touch ./INSTALL_BLOCK
   ```

2. **Start the containers:**
   ```bash
   cd docker/
   docker compose -f docker-compose-test.yml pull php
   docker compose -f docker-compose-test.yml up -d
   ```

3. **Install PHP dependencies (Composer):**
   ```bash
   docker compose -f docker-compose-test.yml exec -T --workdir /var/www/public php composer install --no-interaction --prefer-dist
   ```

4. **Run the suites:**
   * **PHPUnit Unit Tests:** `docker compose -f docker-compose-test.yml exec php ./vendor/bin/phpunit --testsuite UnitTests`
   * **PHPUnit Integration Tests:** `docker compose -f docker-compose-test.yml exec php ./vendor/bin/phpunit --testsuite IntegrationTests`
   * **Behat:** `docker compose -f docker-compose-test.yml exec php ./vendor/bin/behat -c ./test/behat.yml`

---

## PHP base images

Both Compose files use the prebuilt `opencats/php-base` image, defaulting to
PHP 8.4.27. CI tests PHP 8.4.27 and 8.5.12. To select PHP 8.5.12 locally:

```bash
PHP_VERSION=8.5.12 ./test/runLocalTests.sh
```

In fish, use `env PHP_VERSION=8.5.12 ./test/runLocalTests.sh`.
The local runner inherits `PHP_VERSION`; no script edit is needed.

The canonical `docker/php/Dockerfile` includes mysqli, GD with JPEG/FreeType,
SOAP, ZIP, LDAP and mbstring, plus antiword, html2text, pdftotext, unrtf,
Composer and Dockerize. Rebuild and publish the complete image once per PHP
update, rather than compiling these dependencies during every test run.
From the repository root, for each version (8.4.27 and 8.5.12):

```bash
docker build --pull --no-cache -f docker/php/Dockerfile --build-arg PHP_VERSION=8.4.27 -t opencats/php-base:8.4.27 .
docker run --rm opencats/php-base:8.4.27 php -v
docker run --rm opencats/php-base:8.4.27 php -m
docker push opencats/php-base:8.4.27
```

Publish both versioned images before changing CI references. When updating PHP,
keep the CI matrix and release job, both Compose defaults, and the Dockerfile
default in sync. Existing containers must be recreated to use a different image.

## Troubleshooting
* **Connection Errors:** If tests fail to connect, verify the `integrationtestdb` container is healthy. The CI uses `mysqladmin ping` to ensure the DB is ready before PHP attempts to connect.
* **Risky Tests:** PHPUnit may flag empty test methods (placeholders) as "Risky." These do not fail the build but should be implemented in future PRs.
