#!/usr/bin/env bash

#
# OpenCATS Local Docker Test Runner
#
# Creates a clean Docker test environment, temporarily installs the
# OpenCATS test configuration, invokes the canonical runAllTests.sh
# inside the PHP container, captures the complete test output, and
# restores the developer configuration on exit.
#

set -Eeuo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd -- "${SCRIPT_DIR}/.." && pwd)"
DOCKER_DIR="${ROOT_DIR}/docker"

CONFIG_FILE="${ROOT_DIR}/config.php"
TEST_CONFIG_FILE="${ROOT_DIR}/test/config.php"
INSTALL_BLOCK="${ROOT_DIR}/INSTALL_BLOCK"
COMPOSE_FILE="${DOCKER_DIR}/docker-compose-test.yml"

REPORT_DIR="${ROOT_DIR}/reports/local"
LOCAL_LOG="${REPORT_DIR}/local-test.log"

BACKUP_DIR="$(mktemp -d)"

HAD_CONFIG=0
HAD_INSTALL_BLOCK=0

cleanup()
{
    local exit_status=$?

    trap - EXIT
    set +e

    echo
    echo "Restoring local OpenCATS configuration..."

    if [[ "${HAD_CONFIG}" -eq 1 ]]
    then
        cp -a "${BACKUP_DIR}/config.php" "${CONFIG_FILE}"
    else
        rm -f "${CONFIG_FILE}"
    fi

    if [[ "${HAD_INSTALL_BLOCK}" -eq 0 ]]
    then
        rm -f "${INSTALL_BLOCK}"
    fi

    rm -rf "${BACKUP_DIR}"

    echo

    if [[ "${exit_status}" -eq 0 ]]
    then
        echo "Local OpenCATS test run completed successfully."
    else
        echo "Local OpenCATS test run failed with exit status ${exit_status}."
    fi

    echo "Complete local test log:"
    echo "  ${LOCAL_LOG}"
    echo
    echo "Docker test containers have been left running for inspection."

    exit "${exit_status}"
}

trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

echo "========================================"
echo "OpenCATS Local Docker Test Suite"
echo "========================================"
echo

#
# Pre-flight checks.
#
if ! command -v docker >/dev/null 2>&1
then
    echo "Error: docker is not available."
    exit 1
fi

if ! docker compose version >/dev/null 2>&1
then
    echo "Error: docker compose is not available."
    exit 1
fi

if [[ ! -f "${TEST_CONFIG_FILE}" ]]
then
    echo "Error: ${TEST_CONFIG_FILE} does not exist."
    exit 1
fi

if [[ ! -f "${COMPOSE_FILE}" ]]
then
    echo "Error: ${COMPOSE_FILE} does not exist."
    exit 1
fi

#
# Preserve the developer configuration exactly as it was before the run.
#
if [[ -f "${CONFIG_FILE}" ]]
then
    HAD_CONFIG=1
    cp -a "${CONFIG_FILE}" "${BACKUP_DIR}/config.php"
fi

if [[ -e "${INSTALL_BLOCK}" ]]
then
    HAD_INSTALL_BLOCK=1
fi

echo "Installing OpenCATS test configuration..."

cp "${TEST_CONFIG_FILE}" "${CONFIG_FILE}"

if [[ "${HAD_INSTALL_BLOCK}" -eq 0 ]]
then
    touch "${INSTALL_BLOCK}"
fi

#
# Prepare persistent local test logs.
#
mkdir -p "${REPORT_DIR}"
rm -f "${LOCAL_LOG}"

cd "${DOCKER_DIR}"

#
# Remove the previous environment completely.
#
# --volumes guarantees that MariaDB starts with a genuinely clean database.
# Bind-mounted OpenCATS source files are unaffected.
#
echo
echo "Removing previous Docker test environment..."

docker compose -f "${COMPOSE_FILE}" \
    down --volumes --remove-orphans

#
# Start the complete test stack using the configured test images.
#
echo
echo "Starting Docker test environment..."

docker compose -f "${COMPOSE_FILE}" \
    up -d

echo
docker compose -f "${COMPOSE_FILE}" ps

echo
echo "PHP test container:"
docker compose -f "${COMPOSE_FILE}" exec -T php php -v

echo
echo "PHP runtime details:"
docker compose -f "${COMPOSE_FILE}" exec -T php php -r '
echo "PHP_VERSION=" . PHP_VERSION . PHP_EOL;
echo "PHP_VERSION_ID=" . PHP_VERSION_ID . PHP_EOL;
echo "SAPI=" . PHP_SAPI . PHP_EOL;
echo "error_reporting=" . error_reporting() . PHP_EOL;
echo "display_errors=" . ini_get("display_errors") . PHP_EOL;
'

#
# Use the canonical OpenCATS test runner inside the PHP container.
# Capture the complete output while preserving runAllTests.sh exit status.
#
echo
echo "Running OpenCATS test suite..."
echo

set +e

docker compose -f "${COMPOSE_FILE}" exec -T \
    --workdir /var/www/public \
    php \
    sh ./test/runAllTests.sh \
    2>&1 | tee "${LOCAL_LOG}"

TEST_STATUS=${PIPESTATUS[0]}

set -e

exit "${TEST_STATUS}"
