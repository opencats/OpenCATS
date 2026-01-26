#!/bin/bash
# ============================================================
# OpenCATS REST API - Database Schema Integrity Audit
# ============================================================
# Purpose: Verify database migration schema integrity
#
# Checks:
# 1. All CREATE TABLE have PRIMARY KEY
# 2. All tables use ENGINE=InnoDB
# 3. All tables use utf8mb4 charset
# 4. Count _id columns vs foreign key constraints
# 5. Count indexes
# 6. Check for SQL syntax issues
#
# Usage: ./schema_audit.sh
# ============================================================

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Counters
TOTAL_ERRORS=0
TOTAL_WARNINGS=0
TOTAL_PASS=0

# Migration files to check
MIGRATION_DIR="$(dirname "$0")/../../db/migrations"
MIGRATION_FILES=(
    "001_add_api_and_tearsheets.sql"
    "002_oauth2_tables.sql"
    "003_job_submission_placement.sql"
    "004_extended_entities.sql"
    "005_tearsheet_candidates.sql"
    "006_webhooks.sql"
)

echo "============================================================"
echo "OpenCATS Database Schema Integrity Audit"
echo "============================================================"
echo ""

# Function to print result indicators
print_pass() {
    echo -e "  ${GREEN}[PASS]${NC} $1"
    TOTAL_PASS=$((TOTAL_PASS + 1))
}

print_warn() {
    echo -e "  ${YELLOW}[WARN]${NC} $1"
    TOTAL_WARNINGS=$((TOTAL_WARNINGS + 1))
}

print_error() {
    echo -e "  ${RED}[ERROR]${NC} $1"
    TOTAL_ERRORS=$((TOTAL_ERRORS + 1))
}

print_info() {
    echo -e "  ${BLUE}[INFO]${NC} $1"
}

# Helper function to safely count grep matches
count_matches() {
    local pattern="$1"
    local file="$2"
    local count
    count=$(grep -c "$pattern" "$file" 2>/dev/null) || count=0
    echo "$count"
}

# Helper function to safely count grep matches (case insensitive)
count_matches_i() {
    local pattern="$1"
    local file="$2"
    local count
    count=$(grep -ci "$pattern" "$file" 2>/dev/null) || count=0
    echo "$count"
}

# Helper function to safely count grep matches (extended regex)
count_matches_E() {
    local pattern="$1"
    local file="$2"
    local count
    count=$(grep -cE "$pattern" "$file" 2>/dev/null) || count=0
    echo "$count"
}

# Function to audit a single migration file
audit_file() {
    local file="$1"
    local filepath="${MIGRATION_DIR}/${file}"

    echo ""
    echo "------------------------------------------------------------"
    echo "Auditing: ${file}"
    echo "------------------------------------------------------------"

    # Check if file exists
    if [[ ! -f "$filepath" ]]; then
        print_error "File not found: ${filepath}"
        return 1
    fi

    local file_errors=0
    local file_warnings=0
    local file_pass=0

    # ============================================================
    # CHECK 1: All CREATE TABLE have PRIMARY KEY
    # ============================================================
    echo ""
    echo "  Check 1: PRIMARY KEY verification"

    # Count CREATE TABLE statements
    local create_tables
    create_tables=$(count_matches_i "CREATE TABLE" "$filepath")

    # Count PRIMARY KEY definitions
    local primary_keys
    primary_keys=$(count_matches_i "PRIMARY KEY" "$filepath")

    if [[ "$create_tables" -eq "$primary_keys" || "$primary_keys" -ge "$create_tables" ]]; then
        print_pass "All $create_tables CREATE TABLE statements have PRIMARY KEY"
        file_pass=$((file_pass + 1))
    else
        print_warn "Found $create_tables CREATE TABLE but only $primary_keys PRIMARY KEY definitions"
        file_warnings=$((file_warnings + 1))
    fi

    # ============================================================
    # CHECK 2: Engine type verification
    # ============================================================
    echo ""
    echo "  Check 2: Storage engine verification"

    local innodb_count
    innodb_count=$(count_matches_i "ENGINE=InnoDB" "$filepath")

    local myisam_count
    myisam_count=$(count_matches_i "ENGINE=MyISAM" "$filepath")

    if [[ "$myisam_count" -gt 0 ]]; then
        print_warn "Found $myisam_count tables using MyISAM (no FK support)"
        file_warnings=$((file_warnings + 1))
    fi

    if [[ "$innodb_count" -gt 0 ]]; then
        print_pass "Found $innodb_count tables using InnoDB"
        file_pass=$((file_pass + 1))
    fi

    if [[ "$innodb_count" -eq 0 && "$myisam_count" -eq 0 && "$create_tables" -gt 0 ]]; then
        print_error "No engine specification found for tables"
        file_errors=$((file_errors + 1))
    fi

    # ============================================================
    # CHECK 3: Character set verification
    # ============================================================
    echo ""
    echo "  Check 3: Character set verification"

    local utf8mb4_count
    utf8mb4_count=$(count_matches_i "utf8mb4" "$filepath")

    local utf8_only_count
    utf8_only_count=$(count_matches_E "CHARSET=utf8[^m]" "$filepath")

    if [[ "$utf8mb4_count" -gt 0 ]]; then
        print_pass "Found $utf8mb4_count utf8mb4 charset specifications"
        file_pass=$((file_pass + 1))
    fi

    if [[ "$utf8_only_count" -gt 0 ]]; then
        print_warn "Found $utf8_only_count tables using utf8 instead of utf8mb4"
        file_warnings=$((file_warnings + 1))
    fi

    # ============================================================
    # CHECK 4: Foreign key analysis
    # ============================================================
    echo ""
    echo "  Check 4: Foreign key analysis"

    # Count _id columns (potential foreign key candidates)
    local id_columns
    id_columns=$(grep -oE "[a-z_]+_id" "$filepath" 2>/dev/null | sort -u | wc -l | tr -d ' \n')
    if [[ -z "$id_columns" ]]; then
        id_columns=0
    fi

    # Count FOREIGN KEY constraints
    local fk_count
    fk_count=$(count_matches_i "FOREIGN KEY" "$filepath")

    # Count REFERENCES clauses
    local references_count
    references_count=$(count_matches_i "REFERENCES" "$filepath")

    print_info "Found $id_columns unique _id columns"
    print_info "Found $fk_count FOREIGN KEY constraints"
    print_info "Found $references_count REFERENCES clauses"

    if [[ "$fk_count" -eq "$references_count" ]]; then
        print_pass "FK and REFERENCES counts match"
        file_pass=$((file_pass + 1))
    else
        print_warn "FK count ($fk_count) != REFERENCES count ($references_count)"
        file_warnings=$((file_warnings + 1))
    fi

    # ============================================================
    # CHECK 5: Index analysis
    # ============================================================
    echo ""
    echo "  Check 5: Index analysis"

    # Count KEY definitions
    local key_count
    key_count=$(count_matches_E "^[[:space:]]*(KEY|UNIQUE KEY|INDEX)" "$filepath")

    # Count CREATE INDEX statements
    local create_index_count
    create_index_count=$(count_matches_i "CREATE INDEX" "$filepath")

    local total_indexes=$((key_count + create_index_count))

    print_info "Found $key_count inline KEY definitions"
    print_info "Found $create_index_count CREATE INDEX statements"
    print_pass "Total indexes: $total_indexes"
    file_pass=$((file_pass + 1))

    # ============================================================
    # CHECK 6: SQL syntax issues
    # ============================================================
    echo ""
    echo "  Check 6: SQL syntax issues"

    # Check for double semicolons
    local double_semicolons
    double_semicolons=$(count_matches ";;" "$filepath")

    if [[ "$double_semicolons" -gt 0 ]]; then
        print_error "Found $double_semicolons double semicolons (;;)"
        file_errors=$((file_errors + 1))
    else
        print_pass "No double semicolons found"
        file_pass=$((file_pass + 1))
    fi

    # Check for trailing commas before closing parentheses
    local trailing_commas
    trailing_commas=$(count_matches_E ",\s*\)" "$filepath")

    if [[ "$trailing_commas" -gt 0 ]]; then
        print_error "Found $trailing_commas trailing commas before closing parentheses"
        file_errors=$((file_errors + 1))
    else
        print_pass "No trailing commas before parentheses found"
        file_pass=$((file_pass + 1))
    fi

    # Check for unbalanced parentheses in CREATE TABLE
    local open_parens
    local close_parens
    open_parens=$(grep -o "(" "$filepath" 2>/dev/null | wc -l | tr -d ' \n')
    close_parens=$(grep -o ")" "$filepath" 2>/dev/null | wc -l | tr -d ' \n')

    if [[ -z "$open_parens" ]]; then open_parens=0; fi
    if [[ -z "$close_parens" ]]; then close_parens=0; fi

    if [[ "$open_parens" -eq "$close_parens" ]]; then
        print_pass "Balanced parentheses ($open_parens open, $close_parens close)"
        file_pass=$((file_pass + 1))
    else
        print_error "Unbalanced parentheses: $open_parens open vs $close_parens close"
        file_errors=$((file_errors + 1))
    fi

    # Check for common SQL keywords typos
    local typos_found=0

    # Check AUTOINCREMENT vs AUTO_INCREMENT
    if grep -qi "AUTOINCREMENT" "$filepath" 2>/dev/null; then
        if ! grep -qi "AUTO_INCREMENT" "$filepath" 2>/dev/null; then
            print_warn "Found AUTOINCREMENT (MySQL uses AUTO_INCREMENT)"
            file_warnings=$((file_warnings + 1))
            typos_found=1
        fi
    fi

    # Check for VARCHAR without size
    local varchar_no_size
    varchar_no_size=$(count_matches_E "VARCHAR[[:space:]]+[A-Z]" "$filepath")
    if [[ "$varchar_no_size" -gt 0 ]]; then
        print_warn "Found $varchar_no_size VARCHAR without size specification"
        file_warnings=$((file_warnings + 1))
        typos_found=1
    fi

    if [[ "$typos_found" -eq 0 ]]; then
        print_pass "No common SQL typos found"
        file_pass=$((file_pass + 1))
    fi

    # ============================================================
    # File Summary
    # ============================================================
    echo ""
    echo "  File Summary:"
    echo "    Tables: $create_tables"
    echo "    InnoDB: $innodb_count"
    echo "    MyISAM: $myisam_count"
    echo "    Indexes: $total_indexes"
    echo "    Foreign Keys: $fk_count"
}

# ============================================================
# Main execution
# ============================================================

# Process each migration file
for file in "${MIGRATION_FILES[@]}"; do
    audit_file "$file"
done

# ============================================================
# Final Summary
# ============================================================
echo ""
echo "============================================================"
echo "AUDIT SUMMARY"
echo "============================================================"
echo ""
echo -e "  ${GREEN}Passed:${NC}   $TOTAL_PASS"
echo -e "  ${YELLOW}Warnings:${NC} $TOTAL_WARNINGS"
echo -e "  ${RED}Errors:${NC}   $TOTAL_ERRORS"
echo ""

TOTAL_FINDINGS=$((TOTAL_WARNINGS + TOTAL_ERRORS))

if [[ "$TOTAL_ERRORS" -gt 0 ]]; then
    echo -e "  ${RED}AUDIT FAILED${NC} - $TOTAL_ERRORS errors found"
elif [[ "$TOTAL_WARNINGS" -gt 0 ]]; then
    echo -e "  ${YELLOW}AUDIT PASSED WITH WARNINGS${NC} - $TOTAL_WARNINGS warnings"
else
    echo -e "  ${GREEN}AUDIT PASSED${NC} - No issues found"
fi

echo ""
echo "============================================================"
echo "Total findings: $TOTAL_FINDINGS"
echo "============================================================"

# Exit with findings count (0 = success, >0 = issues found)
exit $TOTAL_FINDINGS
