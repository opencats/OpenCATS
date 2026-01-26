#!/bin/bash
#
# CATS
# Master Audit Runner Script - OpenCATS REST API
#
# Runs all audit scripts and generates a summary report.
#
# Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
# Copyright (C) 2026 Space-O Technologies (https://www.spaceotechnologies.com/)
#
# The contents of this file are subject to the CATS Public License
# Version 1.1a (the "License"); you may not use this file except in
# compliance with the License. You may obtain a copy of the License at
# http://www.catsone.com/.
#
# Software distributed under the License is distributed on an "AS IS"
# basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
# License for the specific language governing rights and limitations
# under the License.
#
# @package    CATS
# @subpackage Test
# @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
# @version    $Id: run_full_audit.sh 2026-01-25 $
#

# =============================================================================
# CONFIGURATION
# =============================================================================

# Get the directory where this script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

# Report directory and timestamp
REPORT_DIR="$SCRIPT_DIR/reports"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
REPORT_FILE="$REPORT_DIR/audit_${TIMESTAMP}.txt"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m' # No Color

# =============================================================================
# COUNTERS AND TRACKING
# =============================================================================

TOTAL_SCRIPTS=0
SCRIPTS_PASSED=0
SCRIPTS_FAILED=0
SCRIPTS_SKIPPED=0

# Issue counters by category
SECURITY_ISSUES=0
QUALITY_ISSUES=0
DATABASE_ISSUES=0
FUNCTIONAL_ISSUES=0
INTEGRATION_ISSUES=0
COMPLIANCE_ISSUES=0

# Critical issues flag
HAS_CRITICAL_ISSUES=0

# Arrays to track script results
declare -a PASSED_SCRIPTS
declare -a FAILED_SCRIPTS
declare -a SKIPPED_SCRIPTS

# =============================================================================
# FUNCTIONS
# =============================================================================

# Print section header
print_header() {
    local title="$1"
    echo ""
    echo "=============================================================================="
    echo "$title"
    echo "=============================================================================="
    echo ""
}

# Print subsection header
print_subheader() {
    local title="$1"
    echo ""
    echo "------------------------------------------------------------------------------"
    echo "$title"
    echo "------------------------------------------------------------------------------"
}

# Run a single audit script
# Arguments:
#   $1 - Script path (relative to project root)
#   $2 - Category (security, quality, database, functional, integration, compliance)
#   $3 - Description
run_audit() {
    local script_path="$1"
    local category="$2"
    local description="$3"
    local full_path="$PROJECT_ROOT/$script_path"
    local exit_code=0

    ((TOTAL_SCRIPTS++))

    # Check if script exists
    if [[ ! -f "$full_path" ]]; then
        echo -e "  ${YELLOW}[SKIP]${NC} $description"
        echo "         File not found: $script_path"
        ((SCRIPTS_SKIPPED++))
        SKIPPED_SCRIPTS+=("$script_path")
        return 0
    fi

    # Make script executable if needed
    if [[ ! -x "$full_path" ]]; then
        chmod +x "$full_path" 2>/dev/null
    fi

    echo -e "  ${CYAN}[RUN]${NC}  $description"
    echo "         Script: $script_path"

    # Determine how to run the script
    local extension="${script_path##*.}"
    local output=""

    if [[ "$extension" == "php" ]]; then
        output=$(php "$full_path" 2>&1)
        exit_code=$?
    elif [[ "$extension" == "sh" ]]; then
        output=$(bash "$full_path" 2>&1)
        exit_code=$?
    else
        echo -e "         ${YELLOW}Unknown script type: $extension${NC}"
        ((SCRIPTS_SKIPPED++))
        SKIPPED_SCRIPTS+=("$script_path")
        return 0
    fi

    # Process result
    if [[ $exit_code -eq 0 ]]; then
        echo -e "         ${GREEN}[PASS]${NC} Exit code: $exit_code"
        ((SCRIPTS_PASSED++))
        PASSED_SCRIPTS+=("$script_path")
    elif [[ $exit_code -eq 2 ]]; then
        # Exit code 2 typically means warnings only (no critical issues)
        echo -e "         ${YELLOW}[WARN]${NC} Exit code: $exit_code (warnings found)"
        ((SCRIPTS_PASSED++))
        PASSED_SCRIPTS+=("$script_path")
        # Count as issues for the category
        case "$category" in
            security)    ((SECURITY_ISSUES++)) ;;
            quality)     ((QUALITY_ISSUES++)) ;;
            database)    ((DATABASE_ISSUES++)) ;;
            functional)  ((FUNCTIONAL_ISSUES++)) ;;
            integration) ((INTEGRATION_ISSUES++)) ;;
            compliance)  ((COMPLIANCE_ISSUES++)) ;;
        esac
    else
        echo -e "         ${RED}[FAIL]${NC} Exit code: $exit_code"
        ((SCRIPTS_FAILED++))
        FAILED_SCRIPTS+=("$script_path")
        HAS_CRITICAL_ISSUES=1
        # Count as issues for the category
        case "$category" in
            security)    ((SECURITY_ISSUES++)) ;;
            quality)     ((QUALITY_ISSUES++)) ;;
            database)    ((DATABASE_ISSUES++)) ;;
            functional)  ((FUNCTIONAL_ISSUES++)) ;;
            integration) ((INTEGRATION_ISSUES++)) ;;
            compliance)  ((COMPLIANCE_ISSUES++)) ;;
        esac
    fi

    # Save output to report file
    {
        echo ""
        echo "=============================================================================="
        echo "SCRIPT: $script_path"
        echo "EXIT CODE: $exit_code"
        echo "=============================================================================="
        echo "$output"
        echo ""
    } >> "$REPORT_FILE"

    echo ""
    return $exit_code
}

# =============================================================================
# MAIN EXECUTION
# =============================================================================

# Create reports directory if it doesn't exist
mkdir -p "$REPORT_DIR"

# Start report file
{
    echo "=============================================================================="
    echo "OPENCATS REST API - FULL AUDIT REPORT"
    echo "=============================================================================="
    echo ""
    echo "Date/Time:    $(date '+%Y-%m-%d %H:%M:%S')"
    echo "Report File:  $REPORT_FILE"
    echo "Project Root: $PROJECT_ROOT"
    echo ""
} > "$REPORT_FILE"

# Print header to console
echo ""
echo -e "${BOLD}=============================================================================="
echo "OPENCATS REST API - FULL AUDIT RUNNER"
echo "==============================================================================${NC}"
echo ""
echo "Date/Time:    $(date '+%Y-%m-%d %H:%M:%S')"
echo "Report File:  $REPORT_FILE"
echo "Project Root: $PROJECT_ROOT"

# =============================================================================
# PHASE 1: SECURITY AUDIT
# =============================================================================

print_header "PHASE 1: SECURITY AUDIT"
echo "PHASE 1: SECURITY AUDIT" >> "$REPORT_FILE"

run_audit "test/security/sql_injection_audit.php" "security" "SQL Injection Audit"
run_audit "test/security/auth_audit.php" "security" "Authentication Audit"
run_audit "test/security/input_validation_audit.php" "security" "Input Validation Audit"
run_audit "test/security/rate_limit_audit.php" "security" "Rate Limiting Audit"
run_audit "test/security/webhook_audit.php" "security" "Webhook Security Audit"

# =============================================================================
# PHASE 2: CODE QUALITY AUDIT
# =============================================================================

print_header "PHASE 2: CODE QUALITY AUDIT"
echo "PHASE 2: CODE QUALITY AUDIT" >> "$REPORT_FILE"

run_audit "test/quality/syntax_check.sh" "quality" "PHP Syntax Check"
run_audit "test/quality/code_style_audit.php" "quality" "Code Style Audit"
run_audit "test/quality/error_handling_audit.php" "quality" "Error Handling Audit"

# =============================================================================
# PHASE 3: DATABASE AUDIT
# =============================================================================

print_header "PHASE 3: DATABASE AUDIT"
echo "PHASE 3: DATABASE AUDIT" >> "$REPORT_FILE"

run_audit "test/database/schema_audit.sh" "database" "Database Schema Audit"
run_audit "test/database/migration_order_audit.php" "database" "Migration Order Audit"

# =============================================================================
# PHASE 4: FUNCTIONAL TESTING
# =============================================================================

print_header "PHASE 4: FUNCTIONAL TESTING"
echo "PHASE 4: FUNCTIONAL TESTING" >> "$REPORT_FILE"

run_audit "test/functional/api_response_test.php" "functional" "API Response Test"
run_audit "test/functional/crud_completeness_audit.php" "functional" "CRUD Completeness Audit"

# =============================================================================
# PHASE 5: INTEGRATION TESTING
# =============================================================================

print_header "PHASE 5: INTEGRATION TESTING"
echo "PHASE 5: INTEGRATION TESTING" >> "$REPORT_FILE"

run_audit "test/integration/oauth_flow_test.php" "integration" "OAuth Flow Test"
run_audit "test/integration/webhook_validation.php" "integration" "Webhook Validation"

# =============================================================================
# PHASE 6: COMPLIANCE AUDIT
# =============================================================================

print_header "PHASE 6: COMPLIANCE AUDIT"
echo "PHASE 6: COMPLIANCE AUDIT" >> "$REPORT_FILE"

run_audit "test/compliance/pii_audit.php" "compliance" "PII Audit"
run_audit "test/compliance/audit_logging_validation.php" "compliance" "Audit Logging Validation"

# =============================================================================
# SUMMARY
# =============================================================================

print_header "AUDIT SUMMARY"

# Calculate totals
TOTAL_ISSUES=$((SECURITY_ISSUES + QUALITY_ISSUES + DATABASE_ISSUES + FUNCTIONAL_ISSUES + INTEGRATION_ISSUES + COMPLIANCE_ISSUES))

# Print summary to console
echo "SCRIPT EXECUTION RESULTS"
echo "------------------------------------------------------------------------------"
echo "  Total Scripts:    $TOTAL_SCRIPTS"
echo -e "  Scripts Passed:   ${GREEN}$SCRIPTS_PASSED${NC}"
echo -e "  Scripts Failed:   ${RED}$SCRIPTS_FAILED${NC}"
echo -e "  Scripts Skipped:  ${YELLOW}$SCRIPTS_SKIPPED${NC}"
echo ""

echo "ISSUES BY CATEGORY"
echo "------------------------------------------------------------------------------"
printf "  %-20s %d\n" "Security:" "$SECURITY_ISSUES"
printf "  %-20s %d\n" "Code Quality:" "$QUALITY_ISSUES"
printf "  %-20s %d\n" "Database:" "$DATABASE_ISSUES"
printf "  %-20s %d\n" "Functional:" "$FUNCTIONAL_ISSUES"
printf "  %-20s %d\n" "Integration:" "$INTEGRATION_ISSUES"
printf "  %-20s %d\n" "Compliance:" "$COMPLIANCE_ISSUES"
echo "------------------------------------------------------------------------------"
printf "  %-20s %d\n" "TOTAL ISSUES:" "$TOTAL_ISSUES"
echo ""

# List failed scripts
if [[ ${#FAILED_SCRIPTS[@]} -gt 0 ]]; then
    echo -e "${RED}FAILED SCRIPTS:${NC}"
    echo "------------------------------------------------------------------------------"
    for script in "${FAILED_SCRIPTS[@]}"; do
        echo "  - $script"
    done
    echo ""
fi

# List skipped scripts
if [[ ${#SKIPPED_SCRIPTS[@]} -gt 0 ]]; then
    echo -e "${YELLOW}SKIPPED SCRIPTS (not found):${NC}"
    echo "------------------------------------------------------------------------------"
    for script in "${SKIPPED_SCRIPTS[@]}"; do
        echo "  - $script"
    done
    echo ""
fi

# Final status
echo "=============================================================================="
if [[ $HAS_CRITICAL_ISSUES -eq 1 ]]; then
    echo -e "${RED}${BOLD}AUDIT FAILED - Critical issues found!${NC}"
else
    if [[ $TOTAL_ISSUES -gt 0 ]]; then
        echo -e "${YELLOW}${BOLD}AUDIT COMPLETED WITH WARNINGS${NC}"
    else
        echo -e "${GREEN}${BOLD}AUDIT PASSED${NC}"
    fi
fi
echo "=============================================================================="
echo ""
echo "Full report saved to: $REPORT_FILE"
echo ""

# Save summary to report file
{
    echo ""
    echo "=============================================================================="
    echo "AUDIT SUMMARY"
    echo "=============================================================================="
    echo ""
    echo "SCRIPT EXECUTION RESULTS"
    echo "------------------------------------------------------------------------------"
    echo "  Total Scripts:    $TOTAL_SCRIPTS"
    echo "  Scripts Passed:   $SCRIPTS_PASSED"
    echo "  Scripts Failed:   $SCRIPTS_FAILED"
    echo "  Scripts Skipped:  $SCRIPTS_SKIPPED"
    echo ""
    echo "ISSUES BY CATEGORY"
    echo "------------------------------------------------------------------------------"
    printf "  %-20s %d\n" "Security:" "$SECURITY_ISSUES"
    printf "  %-20s %d\n" "Code Quality:" "$QUALITY_ISSUES"
    printf "  %-20s %d\n" "Database:" "$DATABASE_ISSUES"
    printf "  %-20s %d\n" "Functional:" "$FUNCTIONAL_ISSUES"
    printf "  %-20s %d\n" "Integration:" "$INTEGRATION_ISSUES"
    printf "  %-20s %d\n" "Compliance:" "$COMPLIANCE_ISSUES"
    echo "------------------------------------------------------------------------------"
    printf "  %-20s %d\n" "TOTAL ISSUES:" "$TOTAL_ISSUES"
    echo ""

    if [[ ${#FAILED_SCRIPTS[@]} -gt 0 ]]; then
        echo "FAILED SCRIPTS:"
        echo "------------------------------------------------------------------------------"
        for script in "${FAILED_SCRIPTS[@]}"; do
            echo "  - $script"
        done
        echo ""
    fi

    if [[ ${#SKIPPED_SCRIPTS[@]} -gt 0 ]]; then
        echo "SKIPPED SCRIPTS (not found):"
        echo "------------------------------------------------------------------------------"
        for script in "${SKIPPED_SCRIPTS[@]}"; do
            echo "  - $script"
        done
        echo ""
    fi

    echo "=============================================================================="
    if [[ $HAS_CRITICAL_ISSUES -eq 1 ]]; then
        echo "AUDIT FAILED - Critical issues found!"
    else
        if [[ $TOTAL_ISSUES -gt 0 ]]; then
            echo "AUDIT COMPLETED WITH WARNINGS"
        else
            echo "AUDIT PASSED"
        fi
    fi
    echo "=============================================================================="
    echo ""
    echo "Report generated: $(date '+%Y-%m-%d %H:%M:%S')"
} >> "$REPORT_FILE"

# Exit with appropriate code
if [[ $HAS_CRITICAL_ISSUES -eq 1 ]]; then
    exit 1
fi

exit 0
