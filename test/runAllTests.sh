#!/bin/sh

#
# OpenCATS complete test runner.
#
# This script runs inside the PHP test container.
# Docker/environment setup is handled separately by runLocalTests.sh or CI.
#

set -u

cd /var/www/public/ || exit 1

echo "========================================"
echo "OpenCATS Test Suite"
echo "========================================"

echo
echo "Waiting for test services..."

dockerize \
    -wait tcp://opencats_test_mariadb:3306 \
    -wait http://opencats_test_web:80 \
    -timeout 60s

php test/scripts/waitForDb.php || exit $?

echo
echo "Finalizing install schema version..."

php test/scripts/finalizeInstallSchemaVersion.php || exit $?

STATUS=0

echo
echo "========================================"
echo "PHPUnit Integration Tests"
echo "========================================"

./vendor/bin/phpunit --testsuite IntegrationTests || STATUS=$?

echo
echo "========================================"
echo "Behat Default Suite"
echo "========================================"

./vendor/bin/behat -v -c ./test/behat.yml --suite="default" || STATUS=$?

echo
echo "========================================"
echo "Behat Security Suite"
echo "========================================"

./vendor/bin/behat -v -c ./test/behat.yml --suite="security" || STATUS=$?

echo
echo "========================================"

if [ "${STATUS}" -eq 0 ]
then
    echo "All OpenCATS tests passed."
else
    echo "One or more OpenCATS test suites failed."
fi

echo "========================================"

exit "${STATUS}"
