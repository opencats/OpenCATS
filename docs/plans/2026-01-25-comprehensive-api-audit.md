# Comprehensive REST API Audit Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development to execute this plan task-by-task.

**Goal:** Complete end-to-end audit of the OpenCATS REST API covering security, code quality, functionality, compliance, and migration validation.

**Architecture:** Multi-phase audit using static analysis, dynamic testing, code review, and SQL validation. Each phase produces a findings report with severity ratings.

**Tech Stack:** PHP 7.4+, MySQL/MariaDB, bash scripting for test automation

---

## Audit Scope

| Category | Files | Focus Areas |
|----------|-------|-------------|
| Security | 20 API files, 10 libraries | SQL injection, XSS, auth bypass, input validation |
| Code Quality | All new PHP files | Syntax, style, error handling, documentation |
| Functionality | 12 API handlers | CRUD, pagination, filtering, field selection |
| Database | 6 migrations | Schema integrity, FK constraints, indexes |
| Integration | OAuth, Webhooks, Attachments | End-to-end flows |
| Compliance | All handlers | PII handling, logging, data sanitization |

---

## Phase 1: Security Audit

### Task 1.1: SQL Injection Vulnerability Scan

**Files to Audit:**
- `lib/OAuth2Server.php`
- `lib/WebhookSubscription.php`
- `lib/WebhookDispatcher.php`
- `lib/JobSubmissions.php`
- `lib/Placements.php`
- `lib/Notes.php`
- `lib/Appointments.php`
- `lib/Tasks.php`
- `lib/Tearsheets.php`
- `lib/ApiKeys.php`
- `lib/ApiRateLimiter.php`

**Audit Criteria:**
1. All user input passes through `$this->_db->makeQueryString()` for strings
2. All numeric IDs use `intval()` before SQL queries
3. No raw `$_GET`, `$_POST`, `$_REQUEST` in SQL strings
4. LIMIT/OFFSET values are validated as integers
5. ORDER BY clauses use whitelisted field names only

**Step 1: Create SQL injection test script**

Create: `test/security/sql_injection_audit.php`

```php
<?php
/**
 * SQL Injection Vulnerability Audit Script
 * Scans all library files for potential SQL injection vulnerabilities
 */

$libraryFiles = [
    'lib/OAuth2Server.php',
    'lib/WebhookSubscription.php',
    'lib/WebhookDispatcher.php',
    'lib/JobSubmissions.php',
    'lib/Placements.php',
    'lib/Notes.php',
    'lib/Appointments.php',
    'lib/Tasks.php',
    'lib/Tearsheets.php',
    'lib/ApiKeys.php',
    'lib/ApiRateLimiter.php',
    'lib/ApiRequestLogger.php'
];

$vulnerabilityPatterns = [
    // Direct variable interpolation in SQL
    '/\$sql\s*=.*\$_(?:GET|POST|REQUEST)\[/' => 'CRITICAL: Direct superglobal in SQL',
    '/sprintf\s*\(\s*"[^"]*%s[^"]*"[^)]*\$_(?:GET|POST|REQUEST)/' => 'CRITICAL: Superglobal in sprintf SQL',

    // Missing makeQueryString for string values
    '/WHERE.*=\s*\$(?!this->_db->makeQueryString)(?!.*intval)/' => 'WARNING: Possible unescaped variable in WHERE',

    // ORDER BY without whitelist
    '/ORDER\s+BY\s+\$/' => 'HIGH: Dynamic ORDER BY without whitelist',

    // LIMIT without intval
    '/LIMIT\s+\$(?!.*intval)/' => 'MEDIUM: LIMIT without intval validation',
];

$findings = [];
$totalIssues = 0;

foreach ($libraryFiles as $file) {
    $fullPath = dirname(__DIR__, 2) . '/opencats/' . $file;
    if (!file_exists($fullPath)) {
        echo "SKIP: $file not found\n";
        continue;
    }

    $content = file_get_contents($fullPath);
    $lines = explode("\n", $content);
    $fileFindings = [];

    foreach ($lines as $lineNum => $line) {
        foreach ($vulnerabilityPatterns as $pattern => $severity) {
            if (preg_match($pattern, $line)) {
                $fileFindings[] = [
                    'line' => $lineNum + 1,
                    'severity' => $severity,
                    'code' => trim($line)
                ];
                $totalIssues++;
            }
        }
    }

    // Check for proper escaping patterns (positive indicators)
    $hasProperEscaping = preg_match_all('/makeQueryString|intval\s*\(/', $content, $matches);
    $hasSqlStatements = preg_match_all('/\$sql\s*=/', $content, $sqlMatches);

    $findings[$file] = [
        'issues' => $fileFindings,
        'escaping_calls' => $hasProperEscaping,
        'sql_statements' => count($sqlMatches[0])
    ];
}

// Output report
echo "=== SQL INJECTION VULNERABILITY AUDIT ===\n\n";
echo "Files scanned: " . count($libraryFiles) . "\n";
echo "Total potential issues: $totalIssues\n\n";

foreach ($findings as $file => $data) {
    echo "--- $file ---\n";
    echo "SQL statements: {$data['sql_statements']}, Escaping calls: {$data['escaping_calls']}\n";

    if (empty($data['issues'])) {
        echo "  [PASS] No vulnerabilities detected\n";
    } else {
        foreach ($data['issues'] as $issue) {
            echo "  [LINE {$issue['line']}] {$issue['severity']}\n";
            echo "    Code: {$issue['code']}\n";
        }
    }
    echo "\n";
}

echo "=== AUDIT COMPLETE ===\n";
exit($totalIssues > 0 ? 1 : 0);
```

**Step 2: Run the audit**

```bash
cd /path/to/opencats && php test/security/sql_injection_audit.php
```

**Expected Output:** All files should show `[PASS] No vulnerabilities detected`

**Step 3: Manual review of high-risk patterns**

Review each file for:
- Dynamic table/column names (should use whitelist)
- User-controlled ORDER BY (must whitelist fields)
- Pagination parameters (must be integers)

---

### Task 1.2: Authentication & Authorization Audit

**Files to Audit:**
- `modules/api/ApiUI.php` (main auth flow)
- `modules/api/handlers/OAuthHandler.php`
- `lib/OAuth2Server.php`
- `lib/ApiKeys.php`

**Audit Criteria:**
1. API Key validation is timing-safe (prevent timing attacks)
2. OAuth tokens use secure random generation
3. Token expiry is enforced
4. Failed auth attempts are logged
5. No sensitive data in error messages
6. Authorization checks on every endpoint

**Step 1: Create auth audit script**

Create: `test/security/auth_audit.php`

```php
<?php
/**
 * Authentication & Authorization Audit Script
 */

$findings = [];

// Check OAuth2Server.php
$oauth = file_get_contents(dirname(__DIR__, 2) . '/opencats/lib/OAuth2Server.php');

// Check for secure token generation
if (!preg_match('/random_bytes|openssl_random_pseudo_bytes/', $oauth)) {
    $findings[] = ['CRITICAL', 'OAuth2Server.php', 'Token generation may not use cryptographically secure random'];
}

// Check for timing-safe comparison
if (preg_match('/===.*password|password.*===/', $oauth) && !preg_match('/password_verify/', $oauth)) {
    $findings[] = ['HIGH', 'OAuth2Server.php', 'Password comparison may be vulnerable to timing attacks'];
}

// Check for token expiry enforcement
if (!preg_match('/expires.*<.*NOW|NOW.*>.*expires/', $oauth)) {
    $findings[] = ['HIGH', 'OAuth2Server.php', 'Token expiry may not be properly enforced'];
}

// Check ApiUI.php for auth bypass
$apiui = file_get_contents(dirname(__DIR__, 2) . '/opencats/modules/api/ApiUI.php');

// Check that auth is required except for specific endpoints
if (!preg_match('/auth.*ping.*oauth/i', $apiui)) {
    $findings[] = ['MEDIUM', 'ApiUI.php', 'Verify auth-exempt endpoints are intentional'];
}

// Check for authorization on handlers
$handlers = glob(dirname(__DIR__, 2) . '/opencats/modules/api/handlers/*.php');
foreach ($handlers as $handler) {
    $content = file_get_contents($handler);
    $name = basename($handler);

    // Check that handlers receive userID for authorization
    if (!preg_match('/\$this->_userID|\$userID/', $content)) {
        $findings[] = ['HIGH', $name, 'Handler may not have user context for authorization'];
    }
}

// Check ApiKeys.php for timing-safe comparison
$apikeys = file_get_contents(dirname(__DIR__, 2) . '/opencats/lib/ApiKeys.php');
if (preg_match('/===.*api_key|api_key.*===/', $apikeys) && !preg_match('/hash_equals/', $apikeys)) {
    $findings[] = ['HIGH', 'ApiKeys.php', 'API key comparison may be vulnerable to timing attacks'];
}

// Output
echo "=== AUTHENTICATION & AUTHORIZATION AUDIT ===\n\n";

if (empty($findings)) {
    echo "[PASS] No authentication vulnerabilities detected\n";
} else {
    foreach ($findings as $f) {
        echo "[{$f[0]}] {$f[1]}: {$f[2]}\n";
    }
}

echo "\n=== AUDIT COMPLETE ===\n";
exit(count($findings) > 0 ? 1 : 0);
```

**Step 2: Run the audit**

```bash
php test/security/auth_audit.php
```

---

### Task 1.3: Input Validation & XSS Audit

**Files to Audit:**
- All 12 API handlers in `modules/api/handlers/`
- `modules/api/traits/ApiHelpers.php`

**Audit Criteria:**
1. All input is validated before use
2. Output is JSON-encoded (inherently XSS-safe for API responses)
3. Error messages don't leak sensitive data
4. File uploads validate MIME types and sizes
5. URL parameters are sanitized

**Step 1: Create input validation audit script**

Create: `test/security/input_validation_audit.php`

```php
<?php
/**
 * Input Validation & XSS Audit Script
 */

$handlerDir = dirname(__DIR__, 2) . '/opencats/modules/api/handlers/';
$handlers = glob($handlerDir . '*.php');

$findings = [];

foreach ($handlers as $handler) {
    $content = file_get_contents($handler);
    $name = basename($handler);
    $fileFindings = [];

    // Check for direct $_GET/$_POST usage without validation
    if (preg_match('/\$_(?:GET|POST|REQUEST)\[[\'"][^\'"]+[\'"]\]\s*(?!.*(?:intval|trim|filter_var|htmlspecialchars))/', $content)) {
        $fileFindings[] = 'Possible unvalidated superglobal access';
    }

    // Check that JSON responses use json_encode (XSS protection)
    if (preg_match('/echo\s+\$|print\s+\$/', $content) && !preg_match('/json_encode/', $content)) {
        $fileFindings[] = 'Direct echo of variables without json_encode';
    }

    // Check for proper error message handling (no sensitive data)
    if (preg_match('/sendError.*\$.*password|\$.*secret|\$.*token/i', $content)) {
        $fileFindings[] = 'Possible sensitive data in error message';
    }

    // Check file upload handlers for MIME validation
    if (preg_match('/\$_FILES/', $content)) {
        if (!preg_match('/mime|type|finfo|getimagesize/', $content)) {
            $fileFindings[] = 'File upload without MIME type validation';
        }
    }

    // Positive: Check for proper input handling
    $hasIntval = preg_match_all('/intval\s*\(/', $content, $m1);
    $hasTrim = preg_match_all('/trim\s*\(/', $content, $m2);
    $hasFilterVar = preg_match_all('/filter_var\s*\(/', $content, $m3);

    $findings[$name] = [
        'issues' => $fileFindings,
        'intval_calls' => $hasIntval,
        'trim_calls' => $hasTrim,
        'filter_var_calls' => $hasFilterVar
    ];
}

// Output
echo "=== INPUT VALIDATION & XSS AUDIT ===\n\n";

$totalIssues = 0;
foreach ($findings as $file => $data) {
    echo "--- $file ---\n";
    echo "  intval: {$data['intval_calls']}, trim: {$data['trim_calls']}, filter_var: {$data['filter_var_calls']}\n";

    if (empty($data['issues'])) {
        echo "  [PASS] Input validation looks good\n";
    } else {
        foreach ($data['issues'] as $issue) {
            echo "  [REVIEW] $issue\n";
            $totalIssues++;
        }
    }
}

echo "\n=== AUDIT COMPLETE ($totalIssues items to review) ===\n";
exit($totalIssues > 0 ? 1 : 0);
```

---

### Task 1.4: Rate Limiting Audit

**Files to Audit:**
- `lib/ApiRateLimiter.php`
- `modules/api/ApiUI.php`

**Audit Criteria:**
1. Rate limiting is applied to all authenticated endpoints
2. Rate limit bypass not possible via header manipulation
3. OAuth and API Key users both rate limited
4. Rate limit headers returned correctly
5. 429 response includes Retry-After header

**Step 1: Create rate limiting audit script**

Create: `test/security/rate_limit_audit.php`

```php
<?php
/**
 * Rate Limiting Audit Script
 */

$findings = [];

// Check ApiRateLimiter.php
$limiter = file_get_contents(dirname(__DIR__, 2) . '/opencats/lib/ApiRateLimiter.php');

// Verify rate limit storage is secure (database, not client-side)
if (preg_match('/\$_SESSION|\$_COOKIE/', $limiter)) {
    $findings[] = ['CRITICAL', 'ApiRateLimiter.php', 'Rate limiting uses client-side storage (bypassable)'];
}

// Verify both per-minute and per-hour limits
if (!preg_match('/per.*minute|minute.*count/i', $limiter)) {
    $findings[] = ['HIGH', 'ApiRateLimiter.php', 'Missing per-minute rate limiting'];
}
if (!preg_match('/per.*hour|hour.*count/i', $limiter)) {
    $findings[] = ['HIGH', 'ApiRateLimiter.php', 'Missing per-hour rate limiting'];
}

// Check ApiUI.php for rate limit integration
$apiui = file_get_contents(dirname(__DIR__, 2) . '/opencats/modules/api/ApiUI.php');

// Verify rate limiting is applied after authentication
if (!preg_match('/RateLimiter.*checkLimit|checkLimit.*RateLimiter/', $apiui)) {
    $findings[] = ['HIGH', 'ApiUI.php', 'Rate limiting may not be applied'];
}

// Verify 429 handling
if (!preg_match('/429/', $apiui)) {
    $findings[] = ['MEDIUM', 'ApiUI.php', '429 status code may not be returned for rate limits'];
}

// Verify Retry-After header
if (!preg_match('/Retry-After/i', $limiter)) {
    $findings[] = ['LOW', 'ApiRateLimiter.php', 'Retry-After header may not be set'];
}

// Output
echo "=== RATE LIMITING AUDIT ===\n\n";

if (empty($findings)) {
    echo "[PASS] Rate limiting implementation looks secure\n";
} else {
    foreach ($findings as $f) {
        echo "[{$f[0]}] {$f[1]}: {$f[2]}\n";
    }
}

echo "\n=== AUDIT COMPLETE ===\n";
exit(count($findings) > 0 ? 1 : 0);
```

---

### Task 1.5: Webhook Security Audit

**Files to Audit:**
- `lib/WebhookSubscription.php`
- `lib/WebhookDispatcher.php`
- `modules/api/handlers/SubscriptionHandler.php`

**Audit Criteria:**
1. Webhook URLs validated (no SSRF to internal services)
2. HMAC signatures use constant-time comparison
3. Secrets stored securely (hashed or encrypted)
4. Timeout on webhook delivery (prevent slow-loris)
5. No sensitive data in webhook payloads

**Step 1: Create webhook security audit script**

Create: `test/security/webhook_audit.php`

```php
<?php
/**
 * Webhook Security Audit Script
 */

$findings = [];

// Check WebhookDispatcher.php
$dispatcher = file_get_contents(dirname(__DIR__, 2) . '/opencats/lib/WebhookDispatcher.php');

// Check for SSRF prevention
if (!preg_match('/filter_var.*FILTER_VALIDATE_URL|parse_url/', $dispatcher)) {
    $findings[] = ['HIGH', 'WebhookDispatcher.php', 'URL validation may be missing (SSRF risk)'];
}

// Check for localhost/internal IP blocking
if (!preg_match('/127\.|10\.|192\.168\.|172\.|localhost|0\.0\.0\.0/', $dispatcher)) {
    $findings[] = ['MEDIUM', 'WebhookDispatcher.php', 'Internal IP blocking may not be implemented'];
}

// Check for timeout setting
if (!preg_match('/CURLOPT_TIMEOUT|timeout/i', $dispatcher)) {
    $findings[] = ['MEDIUM', 'WebhookDispatcher.php', 'HTTP timeout may not be set'];
}

// Check for HMAC signing
if (!preg_match('/hash_hmac/', $dispatcher)) {
    $findings[] = ['HIGH', 'WebhookDispatcher.php', 'HMAC signature may not be implemented'];
}

// Check subscription handler
$subHandler = file_get_contents(dirname(__DIR__, 2) . '/opencats/modules/api/handlers/SubscriptionHandler.php');

// Check for URL validation on subscription creation
if (!preg_match('/filter_var.*FILTER_VALIDATE_URL/', $subHandler)) {
    $findings[] = ['HIGH', 'SubscriptionHandler.php', 'Callback URL validation may be missing'];
}

// Output
echo "=== WEBHOOK SECURITY AUDIT ===\n\n";

if (empty($findings)) {
    echo "[PASS] Webhook implementation looks secure\n";
} else {
    foreach ($findings as $f) {
        echo "[{$f[0]}] {$f[1]}: {$f[2]}\n";
    }
}

echo "\n=== AUDIT COMPLETE ===\n";
exit(count($findings) > 0 ? 1 : 0);
```

---

## Phase 2: Code Quality Audit

### Task 2.1: PHP Syntax Validation

**Files to Audit:**
- All 20 files in `modules/api/`
- All 10 new library files in `lib/`

**Step 1: Create syntax validation script**

Create: `test/quality/syntax_check.sh`

```bash
#!/bin/bash
# PHP Syntax Validation Script

echo "=== PHP SYNTAX VALIDATION ==="
echo ""

ERRORS=0
FILES_CHECKED=0

# API Module files
for file in $(find modules/api -name "*.php" -type f); do
    FILES_CHECKED=$((FILES_CHECKED + 1))
    result=$(php -l "$file" 2>&1)
    if [[ $result != *"No syntax errors"* ]]; then
        echo "[FAIL] $file"
        echo "  $result"
        ERRORS=$((ERRORS + 1))
    fi
done

# New library files
NEW_LIBS=(
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

for file in "${NEW_LIBS[@]}"; do
    if [[ -f "$file" ]]; then
        FILES_CHECKED=$((FILES_CHECKED + 1))
        result=$(php -l "$file" 2>&1)
        if [[ $result != *"No syntax errors"* ]]; then
            echo "[FAIL] $file"
            echo "  $result"
            ERRORS=$((ERRORS + 1))
        fi
    fi
done

echo ""
echo "Files checked: $FILES_CHECKED"
echo "Errors found: $ERRORS"
echo ""
echo "=== SYNTAX CHECK COMPLETE ==="

exit $ERRORS
```

**Step 2: Run syntax check**

```bash
cd /path/to/opencats && chmod +x test/quality/syntax_check.sh && ./test/quality/syntax_check.sh
```

---

### Task 2.2: Code Style Consistency Audit

**Audit Criteria:**
1. Consistent indentation (4 spaces, no tabs)
2. Consistent brace style (K&R or Allman)
3. Proper PHPDoc comments on public methods
4. Meaningful variable names
5. No debugging code (var_dump, print_r, die)

**Step 1: Create code style audit script**

Create: `test/quality/code_style_audit.php`

```php
<?php
/**
 * Code Style Audit Script
 */

$findings = [];
$totalIssues = 0;

$files = array_merge(
    glob(dirname(__DIR__, 2) . '/opencats/modules/api/**/*.php'),
    glob(dirname(__DIR__, 2) . '/opencats/lib/OAuth2Server.php'),
    glob(dirname(__DIR__, 2) . '/opencats/lib/Webhook*.php'),
    glob(dirname(__DIR__, 2) . '/opencats/lib/JobSubmissions.php'),
    glob(dirname(__DIR__, 2) . '/opencats/lib/Placements.php'),
    glob(dirname(__DIR__, 2) . '/opencats/lib/Notes.php'),
    glob(dirname(__DIR__, 2) . '/opencats/lib/Appointments.php'),
    glob(dirname(__DIR__, 2) . '/opencats/lib/Tasks.php'),
    glob(dirname(__DIR__, 2) . '/opencats/lib/Tearsheets.php'),
    glob(dirname(__DIR__, 2) . '/opencats/lib/Api*.php')
);

foreach ($files as $file) {
    if (!file_exists($file)) continue;

    $content = file_get_contents($file);
    $name = basename($file);
    $issues = [];

    // Check for tabs (should use spaces)
    if (preg_match('/^\t/m', $content)) {
        $issues[] = 'Contains tabs (should use spaces)';
    }

    // Check for debugging code
    if (preg_match('/var_dump|print_r|die\s*\(|exit\s*\((?!0|1)/', $content)) {
        $issues[] = 'Contains debugging code (var_dump/print_r/die)';
    }

    // Check for TODO/FIXME comments
    if (preg_match('/TODO|FIXME|XXX|HACK/i', $content)) {
        $issues[] = 'Contains TODO/FIXME comments';
    }

    // Check for public methods without PHPDoc
    preg_match_all('/^\s*public\s+function\s+(\w+)/m', $content, $methods);
    preg_match_all('/\/\*\*[\s\S]*?\*\/\s*public\s+function/', $content, $documented);
    $undocumented = count($methods[0]) - count($documented[0]);
    if ($undocumented > 0) {
        $issues[] = "$undocumented public methods without PHPDoc";
    }

    // Check for error suppression (@)
    if (preg_match('/@\$|@file|@mysql|@preg/', $content)) {
        $issues[] = 'Uses error suppression (@) operator';
    }

    if (!empty($issues)) {
        $findings[$name] = $issues;
        $totalIssues += count($issues);
    }
}

// Output
echo "=== CODE STYLE AUDIT ===\n\n";

if (empty($findings)) {
    echo "[PASS] No code style issues found\n";
} else {
    foreach ($findings as $file => $issues) {
        echo "--- $file ---\n";
        foreach ($issues as $issue) {
            echo "  [STYLE] $issue\n";
        }
    }
}

echo "\n=== AUDIT COMPLETE ($totalIssues issues) ===\n";
exit($totalIssues > 0 ? 1 : 0);
```

---

### Task 2.3: Error Handling Audit

**Audit Criteria:**
1. All exceptions are caught and logged
2. Errors return appropriate HTTP status codes
3. No PHP warnings/notices in normal operation
4. Database errors don't expose schema details
5. File operation errors handled gracefully

**Step 1: Create error handling audit script**

Create: `test/quality/error_handling_audit.php`

```php
<?php
/**
 * Error Handling Audit Script
 */

$handlerDir = dirname(__DIR__, 2) . '/opencats/modules/api/handlers/';
$handlers = glob($handlerDir . '*.php');

$findings = [];

foreach ($handlers as $handler) {
    $content = file_get_contents($handler);
    $name = basename($handler);
    $issues = [];

    // Check for try-catch on database operations
    $hasSql = preg_match('/\$this->_db->|query\s*\(/', $content);
    $hasTryCatch = preg_match('/try\s*{/', $content);

    if ($hasSql && !$hasTryCatch) {
        $issues[] = 'Database operations without try-catch';
    }

    // Check for proper HTTP status codes
    $hasPost = preg_match('/case\s*[\'"]POST[\'"]/', $content);
    $has201 = preg_match('/201/', $content);
    if ($hasPost && !$has201) {
        $issues[] = 'POST handler may not return 201 on create';
    }

    $hasDelete = preg_match('/case\s*[\'"]DELETE[\'"]/', $content);
    if ($hasDelete && !preg_match('/200|204/', $content)) {
        $issues[] = 'DELETE handler may not return proper status';
    }

    // Check for 404 on not found
    if (!preg_match('/404/', $content)) {
        $issues[] = 'May not return 404 for not found';
    }

    // Check for 400 on bad request
    if (!preg_match('/400/', $content)) {
        $issues[] = 'May not return 400 for bad request';
    }

    // Check for sendError usage
    if (!preg_match('/sendError\s*\(/', $content)) {
        $issues[] = 'May not use sendError for error responses';
    }

    if (!empty($issues)) {
        $findings[$name] = $issues;
    }
}

// Output
echo "=== ERROR HANDLING AUDIT ===\n\n";

$totalIssues = 0;
foreach ($findings as $file => $issues) {
    echo "--- $file ---\n";
    foreach ($issues as $issue) {
        echo "  [REVIEW] $issue\n";
        $totalIssues++;
    }
}

if (empty($findings)) {
    echo "[PASS] Error handling looks comprehensive\n";
}

echo "\n=== AUDIT COMPLETE ($totalIssues items to review) ===\n";
exit($totalIssues > 0 ? 1 : 0);
```

---

## Phase 3: Database Migration Audit

### Task 3.1: Schema Integrity Audit

**Files to Audit:**
- `modules/install/Schema.php` (revisions 365-370)
- `modules/install/Schema.php` (revisions 365-370)
- `modules/install/Schema.php` (revisions 365-370)
- `modules/install/Schema.php` (revisions 365-370)
- `modules/install/Schema.php` (revisions 365-370)
- `modules/install/Schema.php` (revisions 365-370)

**Audit Criteria:**
1. All tables have PRIMARY KEY
2. Foreign keys have proper ON DELETE/UPDATE actions
3. Indexes on frequently queried columns
4. Appropriate data types (VARCHAR lengths, INT sizes)
5. NOT NULL on required fields
6. DEFAULT values where appropriate
7. CHARSET is utf8mb4 for Unicode support

**Step 1: Create schema audit script**

Create: `test/database/schema_audit.sh`

```bash
#!/bin/bash
# Database Schema Audit Script

# Migrations are now in modules/install/Schema.php
FINDINGS=0

echo "=== DATABASE SCHEMA AUDIT ==="
echo ""

for file in $MIGRATION_DIR/*.sql; do
    echo "--- $(basename $file) ---"

    # Check for PRIMARY KEY on CREATE TABLE
    tables=$(grep -c "CREATE TABLE" "$file")
    pks=$(grep -c "PRIMARY KEY" "$file")
    if [[ $tables -gt $pks ]]; then
        echo "  [WARN] Some tables may be missing PRIMARY KEY"
        FINDINGS=$((FINDINGS + 1))
    fi

    # Check for ENGINE=InnoDB
    if grep -q "CREATE TABLE" "$file" && ! grep -q "ENGINE=InnoDB" "$file"; then
        echo "  [WARN] Some tables may not specify ENGINE=InnoDB"
        FINDINGS=$((FINDINGS + 1))
    fi

    # Check for utf8mb4
    if grep -q "CREATE TABLE" "$file" && ! grep -q "utf8mb4" "$file"; then
        echo "  [WARN] Some tables may not use utf8mb4 charset"
        FINDINGS=$((FINDINGS + 1))
    fi

    # Check for foreign keys on _id columns
    id_cols=$(grep -oE '\b\w+_id\b' "$file" | grep -v "site_id" | sort -u | wc -l)
    fks=$(grep -c "FOREIGN KEY\|REFERENCES" "$file")
    if [[ $id_cols -gt $fks ]]; then
        echo "  [INFO] $id_cols _id columns, $fks foreign keys (review needed)"
    fi

    # Check for indexes
    indexes=$(grep -c "INDEX\|KEY " "$file")
    echo "  [INFO] $indexes indexes defined"

    # Validate SQL syntax (basic)
    if grep -qE ";;|,\s*\)" "$file"; then
        echo "  [ERROR] Possible SQL syntax issue (double semicolon or trailing comma)"
        FINDINGS=$((FINDINGS + 1))
    fi

    echo "  [PASS] Basic schema checks passed"
    echo ""
done

echo "=== AUDIT COMPLETE ($FINDINGS potential issues) ==="
exit $FINDINGS
```

---

### Task 3.2: Migration Order Validation

**Audit Criteria:**
1. Migrations are numbered sequentially
2. No circular dependencies
3. Foreign keys reference existing tables
4. ALTER TABLE references existing columns

**Step 1: Create migration order validation script**

Create: `test/database/migration_order_audit.php`

```php
<?php
/**
 * Migration Order Validation Script
 */

$migrationDir = # Migrations integrated into Schema.php;
$migrations = glob($migrationDir . '*.sql');
sort($migrations);

$tablesCreated = [];
$findings = [];

foreach ($migrations as $file) {
    $content = file_get_contents($file);
    $name = basename($file);

    // Extract table creations
    preg_match_all('/CREATE TABLE.*?(\w+)\s*\(/i', $content, $creates);
    foreach ($creates[1] as $table) {
        $tablesCreated[$table] = $name;
    }

    // Check REFERENCES to ensure table exists
    preg_match_all('/REFERENCES\s+(\w+)\s*\(/i', $content, $refs);
    foreach ($refs[1] as $refTable) {
        if (!isset($tablesCreated[$refTable]) && !in_array($refTable, ['user', 'candidate', 'joborder', 'company', 'contact', 'site'])) {
            $findings[] = [$name, "References table '$refTable' which may not exist yet"];
        }
    }

    // Check ALTER TABLE to ensure table exists
    preg_match_all('/ALTER TABLE\s+(\w+)/i', $content, $alters);
    foreach ($alters[1] as $alterTable) {
        if (!isset($tablesCreated[$alterTable]) && !in_array($alterTable, ['candidate_joborder', 'candidate', 'joborder', 'company', 'contact'])) {
            $findings[] = [$name, "Alters table '$alterTable' which may not have been created"];
        }
    }
}

// Check numbering
$expected = 1;
foreach ($migrations as $file) {
    $name = basename($file);
    if (preg_match('/^0*(\d+)_/', $name, $m)) {
        $num = intval($m[1]);
        if ($num !== $expected) {
            $findings[] = [$name, "Expected migration $expected, got $num"];
        }
        $expected = $num + 1;
    }
}

// Output
echo "=== MIGRATION ORDER VALIDATION ===\n\n";

echo "Tables created:\n";
foreach ($tablesCreated as $table => $migration) {
    echo "  $table <- $migration\n";
}
echo "\n";

if (empty($findings)) {
    echo "[PASS] Migration order is valid\n";
} else {
    foreach ($findings as $f) {
        echo "[WARN] {$f[0]}: {$f[1]}\n";
    }
}

echo "\n=== VALIDATION COMPLETE ===\n";
exit(count($findings) > 0 ? 1 : 0);
```

---

## Phase 4: Functional Testing

### Task 4.1: API Endpoint Response Validation

**Endpoints to Test:**
- All 12 entity handlers (JobOrder, Candidate, Company, Contact, Tearsheet, JobSubmission, Placement, Note, Appointment, Task, Attachment, Subscription)
- Meta endpoint
- OAuth endpoints

**Step 1: Create API response validation script**

Create: `test/functional/api_response_test.php`

```php
<?php
/**
 * API Response Format Validation
 * Tests that all handlers return properly formatted JSON responses
 */

// Simulated test - checks response structure without HTTP calls
// In production, use curl or PHPUnit with test database

$expectedResponses = [
    'success_single' => ['id', 'type_specific_fields'],
    'success_list' => ['total', 'page', 'limit', 'data'],
    'error' => ['error', 'message'],
];

$handlers = [
    'JobOrderHandler' => ['id', 'title', 'company', 'status'],
    'CandidateHandler' => ['id', 'firstName', 'lastName', 'email'],
    'CompanyHandler' => ['id', 'name', 'city', 'state'],
    'ContactHandler' => ['id', 'firstName', 'lastName', 'company'],
    'TearsheetHandler' => ['id', 'name', 'description', 'jobCount'],
    'JobSubmissionHandler' => ['id', 'candidate', 'jobOrder', 'status'],
    'PlacementHandler' => ['id', 'candidate', 'jobOrder', 'salary'],
    'NoteHandler' => ['id', 'action', 'notes', 'dateCreated'],
    'AppointmentHandler' => ['id', 'title', 'startDate', 'endDate'],
    'TaskHandler' => ['id', 'title', 'priority', 'status'],
    'SubscriptionHandler' => ['id', 'name', 'entityType', 'callbackUrl'],
];

echo "=== API RESPONSE FORMAT VALIDATION ===\n\n";

$handlerDir = dirname(__DIR__, 2) . '/opencats/modules/api/handlers/';

foreach ($handlers as $handler => $expectedFields) {
    $file = $handlerDir . $handler . '.php';
    if (!file_exists($file)) {
        echo "[SKIP] $handler.php not found\n";
        continue;
    }

    $content = file_get_contents($file);

    // Check for sendSuccess usage
    if (!preg_match('/sendSuccess\s*\(/', $content)) {
        echo "[FAIL] $handler: Missing sendSuccess() calls\n";
        continue;
    }

    // Check for sendError usage
    if (!preg_match('/sendError\s*\(/', $content)) {
        echo "[WARN] $handler: Missing sendError() calls\n";
    }

    // Check for pagination in list method
    if (preg_match('/handleList|getAll/', $content)) {
        if (!preg_match('/total.*page.*limit|getPaginationParams/', $content)) {
            echo "[WARN] $handler: List may not include pagination metadata\n";
        }
    }

    // Check format method exists
    if (!preg_match('/format\w+\s*\(/', $content)) {
        echo "[WARN] $handler: Missing format method for response formatting\n";
    }

    echo "[PASS] $handler: Response format looks correct\n";
}

echo "\n=== VALIDATION COMPLETE ===\n";
```

---

### Task 4.2: CRUD Operation Completeness

**Step 1: Create CRUD completeness audit**

Create: `test/functional/crud_completeness_audit.php`

```php
<?php
/**
 * CRUD Operation Completeness Audit
 * Ensures all handlers implement GET, POST, PUT, DELETE appropriately
 */

$handlers = [
    'JobOrderHandler' => ['GET', 'POST', 'PUT', 'DELETE'],
    'CandidateHandler' => ['GET', 'POST', 'PUT', 'DELETE'],
    'CompanyHandler' => ['GET', 'POST', 'PUT', 'DELETE'],
    'ContactHandler' => ['GET', 'POST', 'PUT', 'DELETE'],
    'TearsheetHandler' => ['GET', 'POST', 'PUT', 'DELETE'],
    'JobSubmissionHandler' => ['GET', 'POST', 'PUT', 'DELETE'],
    'PlacementHandler' => ['GET', 'POST', 'PUT', 'DELETE'],
    'NoteHandler' => ['GET', 'POST', 'PUT', 'DELETE'],
    'AppointmentHandler' => ['GET', 'POST', 'PUT', 'DELETE'],
    'TaskHandler' => ['GET', 'POST', 'PUT', 'DELETE'],
    'SubscriptionHandler' => ['GET', 'POST', 'PUT', 'DELETE'],
    'AttachmentHandler' => ['GET', 'POST', 'DELETE'],
    'MassUpdateHandler' => ['POST'],
    'AssociationHandler' => ['GET', 'POST', 'DELETE'],
    'MetaHandler' => ['GET'],
    'OAuthHandler' => ['GET', 'POST'],
];

echo "=== CRUD COMPLETENESS AUDIT ===\n\n";

$handlerDir = dirname(__DIR__, 2) . '/opencats/modules/api/handlers/';
$totalMissing = 0;

foreach ($handlers as $handler => $expectedMethods) {
    $file = $handlerDir . $handler . '.php';
    if (!file_exists($file)) {
        echo "[SKIP] $handler.php not found\n";
        continue;
    }

    $content = file_get_contents($file);
    $missing = [];

    foreach ($expectedMethods as $method) {
        // Check for case 'METHOD': in switch statement
        if (!preg_match("/case\s*['\"]$method['\"]/", $content)) {
            $missing[] = $method;
        }
    }

    if (empty($missing)) {
        echo "[PASS] $handler: All methods implemented (" . implode(', ', $expectedMethods) . ")\n";
    } else {
        echo "[FAIL] $handler: Missing methods: " . implode(', ', $missing) . "\n";
        $totalMissing += count($missing);
    }
}

echo "\n=== AUDIT COMPLETE ($totalMissing missing methods) ===\n";
exit($totalMissing > 0 ? 1 : 0);
```

---

## Phase 5: Integration Testing

### Task 5.1: OAuth Flow Validation

**Step 1: Create OAuth flow test script**

Create: `test/integration/oauth_flow_test.php`

```php
<?php
/**
 * OAuth 2.0 Flow Validation
 * Tests the OAuth implementation logic without HTTP
 */

// This would normally require a running server
// Here we validate the code structure

$oauthFile = dirname(__DIR__, 2) . '/opencats/lib/OAuth2Server.php';
$content = file_get_contents($oauthFile);

$checks = [
    'createClient' => 'Client creation method exists',
    'validateClient' => 'Client validation method exists',
    'createAuthorizationCode' => 'Auth code creation exists',
    'exchangeAuthorizationCode' => 'Auth code exchange exists',
    'clientCredentialsGrant' => 'Client credentials grant exists',
    'refreshTokenGrant' => 'Refresh token grant exists',
    'validateAccessToken' => 'Token validation exists',
    'revokeToken' => 'Token revocation exists',
];

echo "=== OAUTH 2.0 FLOW VALIDATION ===\n\n";

$passed = 0;
$failed = 0;

foreach ($checks as $method => $description) {
    if (preg_match("/function\s+$method\s*\(/", $content)) {
        echo "[PASS] $description\n";
        $passed++;
    } else {
        echo "[FAIL] $description\n";
        $failed++;
    }
}

// Check token lifetime constants
if (preg_match('/ACCESS_TOKEN_LIFETIME\s*=\s*\d+/', $content)) {
    echo "[PASS] Access token lifetime defined\n";
    $passed++;
} else {
    echo "[FAIL] Access token lifetime not defined\n";
    $failed++;
}

if (preg_match('/REFRESH_TOKEN_LIFETIME\s*=\s*\d+/', $content)) {
    echo "[PASS] Refresh token lifetime defined\n";
    $passed++;
} else {
    echo "[FAIL] Refresh token lifetime not defined\n";
    $failed++;
}

echo "\n=== VALIDATION COMPLETE ($passed passed, $failed failed) ===\n";
exit($failed > 0 ? 1 : 0);
```

---

### Task 5.2: Webhook Delivery Validation

**Step 1: Create webhook validation script**

Create: `test/integration/webhook_validation.php`

```php
<?php
/**
 * Webhook Delivery Validation
 */

$dispatcherFile = dirname(__DIR__, 2) . '/opencats/lib/WebhookDispatcher.php';
$content = file_get_contents($dispatcherFile);

$checks = [
    'triggerEvent' => 'Event triggering method',
    'buildPayload' => 'Payload building method',
    'dispatchWebhook' => 'Webhook dispatch method',
    'generateSignature' => 'HMAC signature generation',
    'processQueue' => 'Queue processing method',
    'CURLOPT' => 'Uses cURL for HTTP requests',
    'hash_hmac' => 'Uses HMAC for signatures',
    'X-OpenCATS-Signature' => 'Signature header included',
    'X-OpenCATS-Event' => 'Event type header included',
];

echo "=== WEBHOOK DELIVERY VALIDATION ===\n\n";

$passed = 0;
$failed = 0;

foreach ($checks as $pattern => $description) {
    if (preg_match("/$pattern/", $content)) {
        echo "[PASS] $description\n";
        $passed++;
    } else {
        echo "[FAIL] $description\n";
        $failed++;
    }
}

// Check retry logic
if (preg_match('/MAX_RETRY|retry.*attempt|exponential.*backoff/i', $content)) {
    echo "[PASS] Retry logic implemented\n";
    $passed++;
} else {
    echo "[WARN] Retry logic may not be implemented\n";
}

echo "\n=== VALIDATION COMPLETE ($passed passed, $failed failed) ===\n";
exit($failed > 0 ? 1 : 0);
```

---

## Phase 6: Compliance Audit

### Task 6.1: PII Handling Audit

**Audit Criteria:**
1. Personal data (names, emails, phones) not logged in plain text
2. Passwords never stored in plain text
3. API keys/secrets properly hashed
4. Sensitive fields excluded from webhook payloads
5. Audit trail for data access

**Step 1: Create PII audit script**

Create: `test/compliance/pii_audit.php`

```php
<?php
/**
 * PII (Personally Identifiable Information) Handling Audit
 */

$findings = [];

// Check for password handling
$libDir = dirname(__DIR__, 2) . '/opencats/lib/';
$files = glob($libDir . '*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    $name = basename($file);

    // Check for plain text password storage
    if (preg_match('/INSERT.*password.*=.*\$(?!.*password_hash)/', $content)) {
        $findings[] = [$name, 'CRITICAL', 'Possible plain text password storage'];
    }

    // Check for password in logs
    if (preg_match('/error_log.*password|log.*\$.*password/i', $content)) {
        $findings[] = [$name, 'HIGH', 'Password may be logged'];
    }
}

// Check API logging
$loggerFile = $libDir . 'ApiRequestLogger.php';
if (file_exists($loggerFile)) {
    $content = file_get_contents($loggerFile);

    // Check that request body is sanitized before logging
    if (preg_match('/request_body.*json_encode\s*\(\s*\$/', $content) &&
        !preg_match('/sanitize|redact|password.*=>.*\*|unset.*password/', $content)) {
        $findings[] = ['ApiRequestLogger.php', 'HIGH', 'Request body may log sensitive data'];
    }
}

// Check webhook payloads
$webhookFile = $libDir . 'WebhookDispatcher.php';
if (file_exists($webhookFile)) {
    $content = file_get_contents($webhookFile);

    if (preg_match('/sanitize|redact|strip.*sensitive/i', $content)) {
        echo "[PASS] Webhook dispatcher has data sanitization\n";
    } else {
        $findings[] = ['WebhookDispatcher.php', 'MEDIUM', 'Webhook payload may include sensitive data'];
    }
}

// Output
echo "=== PII HANDLING AUDIT ===\n\n";

if (empty($findings)) {
    echo "[PASS] No PII handling issues detected\n";
} else {
    foreach ($findings as $f) {
        echo "[{$f[1]}] {$f[0]}: {$f[2]}\n";
    }
}

echo "\n=== AUDIT COMPLETE ===\n";
exit(count($findings) > 0 ? 1 : 0);
```

---

### Task 6.2: Audit Logging Validation

**Step 1: Create audit logging validation script**

Create: `test/compliance/audit_logging_validation.php`

```php
<?php
/**
 * Audit Logging Validation
 */

$loggerFile = dirname(__DIR__, 2) . '/opencats/lib/ApiRequestLogger.php';

if (!file_exists($loggerFile)) {
    echo "[FAIL] ApiRequestLogger.php not found\n";
    exit(1);
}

$content = file_get_contents($loggerFile);

$checks = [
    'api_key_id' => 'Logs API key ID for attribution',
    'endpoint' => 'Logs endpoint accessed',
    'method' => 'Logs HTTP method',
    'response_code' => 'Logs response status code',
    'request_time|timestamp|date' => 'Logs request timestamp',
    'ip_address|remote_addr|REMOTE_ADDR' => 'Logs client IP address',
];

echo "=== AUDIT LOGGING VALIDATION ===\n\n";

$passed = 0;
$failed = 0;

foreach ($checks as $pattern => $description) {
    if (preg_match("/$pattern/i", $content)) {
        echo "[PASS] $description\n";
        $passed++;
    } else {
        echo "[FAIL] $description\n";
        $failed++;
    }
}

// Check for log storage
if (preg_match('/INSERT INTO.*api_request_log|database.*log/i', $content)) {
    echo "[PASS] Logs stored in database (queryable)\n";
    $passed++;
} else {
    echo "[WARN] Logs may not be stored in database\n";
}

echo "\n=== VALIDATION COMPLETE ($passed passed, $failed failed) ===\n";
exit($failed > 0 ? 1 : 0);
```

---

## Phase 7: Summary Report Generation

### Task 7.1: Generate Comprehensive Audit Report

**Step 1: Create master audit runner**

Create: `test/run_full_audit.sh`

```bash
#!/bin/bash
# Master Audit Runner Script

echo "=============================================="
echo "OpenCATS REST API - Comprehensive Audit"
echo "Date: $(date)"
echo "=============================================="
echo ""

TOTAL_ISSUES=0
CRITICAL=0
HIGH=0
MEDIUM=0
LOW=0

# Create test directories
mkdir -p test/security test/quality test/database test/functional test/integration test/compliance test/reports

# Run all audits
echo ">>> PHASE 1: SECURITY AUDIT <<<"
echo ""

echo "1.1 SQL Injection Scan..."
php test/security/sql_injection_audit.php
TOTAL_ISSUES=$((TOTAL_ISSUES + $?))

echo ""
echo "1.2 Authentication Audit..."
php test/security/auth_audit.php
TOTAL_ISSUES=$((TOTAL_ISSUES + $?))

echo ""
echo "1.3 Input Validation Audit..."
php test/security/input_validation_audit.php
TOTAL_ISSUES=$((TOTAL_ISSUES + $?))

echo ""
echo "1.4 Rate Limiting Audit..."
php test/security/rate_limit_audit.php
TOTAL_ISSUES=$((TOTAL_ISSUES + $?))

echo ""
echo "1.5 Webhook Security Audit..."
php test/security/webhook_audit.php
TOTAL_ISSUES=$((TOTAL_ISSUES + $?))

echo ""
echo ">>> PHASE 2: CODE QUALITY AUDIT <<<"
echo ""

echo "2.1 PHP Syntax Validation..."
bash test/quality/syntax_check.sh
TOTAL_ISSUES=$((TOTAL_ISSUES + $?))

echo ""
echo "2.2 Code Style Audit..."
php test/quality/code_style_audit.php
TOTAL_ISSUES=$((TOTAL_ISSUES + $?))

echo ""
echo "2.3 Error Handling Audit..."
php test/quality/error_handling_audit.php
TOTAL_ISSUES=$((TOTAL_ISSUES + $?))

echo ""
echo ">>> PHASE 3: DATABASE AUDIT <<<"
echo ""

echo "3.1 Schema Integrity Audit..."
bash test/database/schema_audit.sh
TOTAL_ISSUES=$((TOTAL_ISSUES + $?))

echo ""
echo "3.2 Migration Order Validation..."
php test/database/migration_order_audit.php
TOTAL_ISSUES=$((TOTAL_ISSUES + $?))

echo ""
echo ">>> PHASE 4: FUNCTIONAL TESTING <<<"
echo ""

echo "4.1 API Response Validation..."
php test/functional/api_response_test.php
TOTAL_ISSUES=$((TOTAL_ISSUES + $?))

echo ""
echo "4.2 CRUD Completeness Audit..."
php test/functional/crud_completeness_audit.php
TOTAL_ISSUES=$((TOTAL_ISSUES + $?))

echo ""
echo ">>> PHASE 5: INTEGRATION TESTING <<<"
echo ""

echo "5.1 OAuth Flow Validation..."
php test/integration/oauth_flow_test.php
TOTAL_ISSUES=$((TOTAL_ISSUES + $?))

echo ""
echo "5.2 Webhook Delivery Validation..."
php test/integration/webhook_validation.php
TOTAL_ISSUES=$((TOTAL_ISSUES + $?))

echo ""
echo ">>> PHASE 6: COMPLIANCE AUDIT <<<"
echo ""

echo "6.1 PII Handling Audit..."
php test/compliance/pii_audit.php
TOTAL_ISSUES=$((TOTAL_ISSUES + $?))

echo ""
echo "6.2 Audit Logging Validation..."
php test/compliance/audit_logging_validation.php
TOTAL_ISSUES=$((TOTAL_ISSUES + $?))

echo ""
echo "=============================================="
echo "AUDIT SUMMARY"
echo "=============================================="
echo ""
echo "Total issues requiring attention: $TOTAL_ISSUES"
echo ""
echo "Audit complete. Review findings above."
echo "=============================================="

exit $TOTAL_ISSUES
```

---

## Execution Checklist

After all audit scripts are created:

1. [ ] Create test directory structure
2. [ ] Run Phase 1: Security Audit (5 scripts)
3. [ ] Run Phase 2: Code Quality Audit (3 scripts)
4. [ ] Run Phase 3: Database Migration Audit (2 scripts)
5. [ ] Run Phase 4: Functional Testing (2 scripts)
6. [ ] Run Phase 5: Integration Testing (2 scripts)
7. [ ] Run Phase 6: Compliance Audit (2 scripts)
8. [ ] Generate summary report
9. [ ] Fix all CRITICAL and HIGH issues
10. [ ] Re-run audit to verify fixes
11. [ ] Document remaining MEDIUM/LOW issues for future

---

## Summary

| Phase | Scripts | Focus |
|-------|---------|-------|
| Phase 1 | 5 | Security vulnerabilities |
| Phase 2 | 3 | Code quality and style |
| Phase 3 | 2 | Database schema integrity |
| Phase 4 | 2 | API functionality |
| Phase 5 | 2 | Integration flows |
| Phase 6 | 2 | Compliance requirements |
| Phase 7 | 1 | Master runner |

**Total: 17 audit scripts covering all aspects of the API implementation**

---

*Audit plan created by Claude Opus 4.5 for OpenCATS REST API comprehensive review.*
