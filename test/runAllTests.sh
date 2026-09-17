#!/bin/sh

#
# OpenCATS complete test runner.
#
# This script runs inside the PHP test container.
# Docker/environment setup is handled separately by runLocalTests.sh or CI.
#
# Each suite writes a persistent log and records its own PASS/FAIL result.
# The overall exit status remains non-zero if any suite fails.
#

set -u

cd /var/www/public/ || exit 1

REPORT_DIR="/var/www/public/reports/local"
SUMMARY_LOG="${REPORT_DIR}/summary.log"

PHPUNIT_LOG="${REPORT_DIR}/phpunit-integration.log"
BEHAT_DEFAULT_LOG="${REPORT_DIR}/behat-default.log"
BEHAT_SECURITY_LOG="${REPORT_DIR}/behat-security.log"

mkdir -p "${REPORT_DIR}"

rm -f \
    "${SUMMARY_LOG}" \
    "${PHPUNIT_LOG}" \
    "${BEHAT_DEFAULT_LOG}" \
    "${BEHAT_SECURITY_LOG}"

STATUS=0

run_logged_suite()
{
    label="$1"
    log_file="$2"
    shift 2

    status_file="${REPORT_DIR}/.$$.status"

    rm -f "${status_file}"

    echo
    echo "========================================"
    echo "${label}"
    echo "========================================"

    #
    # POSIX sh has no PIPESTATUS equivalent.
    # Record the real test command status from inside the subshell while
    # tee continues to display and persist the complete suite output.
    #
    (
        "$@"
        suite_status=$?
        printf '%s\n' "${suite_status}" > "${status_file}"
    ) 2>&1 | tee "${log_file}"

    if [ -f "${status_file}" ]
    then
        suite_status="$(cat "${status_file}")"
    else
        suite_status=1
    fi

    rm -f "${status_file}"

    if [ "${suite_status}" -eq 0 ]
    then
        result="PASS"
    else
        result="FAIL (exit ${suite_status})"

        if [ "${STATUS}" -eq 0 ]
        then
            STATUS="${suite_status}"
        fi
    fi

    printf '%-30s %s\n' "${label}:" "${result}" \
        | tee -a "${SUMMARY_LOG}"
}

echo "========================================"
echo "OpenCATS Test Suite"
echo "========================================"

echo
echo "Waiting for test services..."

dockerize \
    -wait tcp://opencats_test_mariadb:3306 \
    -wait http://opencats_test_web:80 \
    -timeout 60s

SERVICE_STATUS=$?

if [ "${SERVICE_STATUS}" -ne 0 ]
then
    echo "Test service startup failed with exit status ${SERVICE_STATUS}."
    exit "${SERVICE_STATUS}"
fi

php test/scripts/waitForDb.php
DB_STATUS=$?

if [ "${DB_STATUS}" -ne 0 ]
then
    echo "Database readiness check failed with exit status ${DB_STATUS}."
    exit "${DB_STATUS}"
fi

echo
echo "Finalizing install schema version..."

php test/scripts/finalizeInstallSchemaVersion.php
SCHEMA_STATUS=$?

if [ "${SCHEMA_STATUS}" -ne 0 ]
then
    echo "Schema finalization failed with exit status ${SCHEMA_STATUS}."
    exit "${SCHEMA_STATUS}"
fi

run_logged_suite \
    "PHPUnit Integration Tests" \
    "${PHPUNIT_LOG}" \
    ./vendor/bin/phpunit \
        --testsuite IntegrationTests \
        --testdox

run_logged_suite \
    "Behat Default Suite" \
    "${BEHAT_DEFAULT_LOG}" \
    ./vendor/bin/behat \
        -v \
        -c ./test/behat.yml \
        --suite="default"

run_logged_suite \
    "Behat Security Suite" \
    "${BEHAT_SECURITY_LOG}" \
    ./vendor/bin/behat \
        -v \
        -c ./test/behat.yml \
        --suite="security"

echo
echo "========================================"
echo "Test Suite Summary"
echo "========================================"

cat "${SUMMARY_LOG}"

echo

if [ "${STATUS}" -eq 0 ]
then
    echo "All OpenCATS tests passed."
else
    echo "One or more OpenCATS test suites failed."
fi

echo
echo "Detailed logs:"
echo "  ${PHPUNIT_LOG}"
echo "  ${BEHAT_DEFAULT_LOG}"
echo "  ${BEHAT_SECURITY_LOG}"
echo "  ${SUMMARY_LOG}"
echo "========================================"

exit "${STATUS}"
