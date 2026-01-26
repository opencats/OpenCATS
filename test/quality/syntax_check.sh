#!/bin/bash
#
# PHP Syntax Validation Script for OpenCATS REST API
# Runs php -l on all API module files and new library files
#

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get the root directory (relative to this script)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "$SCRIPT_DIR/../.." && pwd)"

echo "=========================================="
echo "OpenCATS PHP Syntax Validation"
echo "=========================================="
echo "Root directory: $ROOT_DIR"
echo ""

# Counters
FILES_CHECKED=0
ERRORS_FOUND=0
FILES_MISSING=0

# Function to check a single file
check_file() {
    local file="$1"

    if [[ ! -f "$file" ]]; then
        echo -e "${YELLOW}[MISSING]${NC} $file"
        ((FILES_MISSING++))
        return 1
    fi

    ((FILES_CHECKED++))

    # Run php -l and capture output
    OUTPUT=$(php -l "$file" 2>&1)
    EXIT_CODE=$?

    if [[ $EXIT_CODE -ne 0 ]]; then
        echo -e "${RED}[ERROR]${NC} $file"
        echo "$OUTPUT" | grep -v "^No syntax errors"
        ((ERRORS_FOUND++))
        return 1
    fi

    # Success - don't print anything (show only failures)
    return 0
}

# Function to check all PHP files in a directory recursively
check_directory() {
    local dir="$1"

    if [[ ! -d "$dir" ]]; then
        echo -e "${YELLOW}[MISSING DIR]${NC} $dir"
        return 1
    fi

    # Find all PHP files recursively
    while IFS= read -r -d '' file; do
        check_file "$file"
    done < <(find "$dir" -type f -name "*.php" -print0 2>/dev/null)
}

echo "Checking API modules..."
echo "----------------------------------------"
check_directory "$ROOT_DIR/modules/api"

echo ""
echo "Checking new library files..."
echo "----------------------------------------"

# List of new library files to check
LIBRARY_FILES=(
    "lib/OAuth2Server.php"
    "lib/WebhookSubscription.php"
    "lib/WebhookDispatcher.php"
    "lib/JobSubmissions.php"
    "lib/Placements.php"
    "lib/Notes.php"
    "lib/Appointments.php"
    "lib/Tasks.php"
    "lib/Tearsheets.php"
    "lib/ApiKeys.php"
    "lib/ApiRateLimiter.php"
    "lib/ApiRequestLogger.php"
    "lib/ApiConfig.php"
    "lib/ApiResponse.php"
)

for file in "${LIBRARY_FILES[@]}"; do
    check_file "$ROOT_DIR/$file"
done

# Summary
echo ""
echo "=========================================="
echo "Summary"
echo "=========================================="
echo -e "Files checked:  ${GREEN}$FILES_CHECKED${NC}"
echo -e "Files missing:  ${YELLOW}$FILES_MISSING${NC}"

if [[ $ERRORS_FOUND -eq 0 ]]; then
    echo -e "Syntax errors:  ${GREEN}$ERRORS_FOUND${NC}"
    echo ""
    echo -e "${GREEN}All syntax checks passed!${NC}"
else
    echo -e "Syntax errors:  ${RED}$ERRORS_FOUND${NC}"
    echo ""
    echo -e "${RED}Syntax validation failed!${NC}"
fi

echo "=========================================="

# Exit with error count as exit code
exit $ERRORS_FOUND
