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
   docker compose -f docker-compose-test.yml up -d
   ```

3. **Install PHP dependencies (Composer):**
   ```bash
   docker run --rm -v "$(pwd)/..":/app -w /app composer:2 composer install --no-interaction --prefer-dist --ignore-platform-reqs
   ```

4. **Run the suites:**
   * **PHPUnit Unit Tests:** `docker exec -it opencats_test_php ./vendor/bin/phpunit src/OpenCATS/Tests/UnitTests`
   * **PHPUnit Integration Tests:** `docker exec -it opencats_test_php ./vendor/bin/phpunit src/OpenCATS/Tests/IntegrationTests`
   * **Behat:** `docker exec -it opencats_test_php ./vendor/bin/behat -c ./test/behat.yml`

---

## Troubleshooting
* **Connection Errors:** If tests fail to connect, verify the `integrationtestdb` container is healthy. The CI uses `mysqladmin ping` to ensure the DB is ready before PHP attempts to connect.
* **Risky Tests:** PHPUnit may flag empty test methods (placeholders) as "Risky." These do not fail the build but should be implemented in future PRs.
