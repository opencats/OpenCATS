CI/CD Testing Infrastructure Notes
Overview of Databases
This project uses two distinct MariaDB instances during the testing phase to ensure data isolation:

opencatsdb (Port 3306):

Purpose: Primary application database.

Data: Pre-seeded with test.sql and securityTests.sql via Docker's initdb.d.

Used By: Manual dev testing, Behat (Gherkin/Selenium) functional tests.

Persistence: Persistent during the life of the container.

integrationtestdb (Port 3307):

Purpose: A "Disposable Sandbox" for PHPUnit Integration Tests.

Data: None at startup.

Used By: src/OpenCATS/Tests/IntegrationTests/ via DatabaseTestCase.php.

Behavior: The PHP code drops and recreates this database for every single test class to ensure a clean schema build from db/cats_schema.sql.

Critical CI Synchronization
Because the Integration Tests call die() if the connection to integrationtestdb fails, the GitHub Action MUST wait for both containers to be healthy. We use mysqladmin ping in the workflow because:

Docker saying "Started" only means the process exists.

mysqladmin ping confirms the database has finished its internal initialization (like MyISAM table checks) and is actually ready for the PHP mysqli_connect call.

Troubleshooting "Query Failed" in CI
If you see the Red Error box in logs:

Check Hostnames: Ensure DatabaseTestCase.php points to integrationtestdb (the service name, not localhost).

Check Timing: Increase the timeout in ci.yml if the runner is slow.
