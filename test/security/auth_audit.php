#!/usr/bin/env php
<?php
/**
 * OpenCATS Authentication & Authorization Security Audit
 *
 * This script audits the REST API authentication and authorization implementation
 * for security best practices and potential vulnerabilities.
 *
 * Files audited:
 * - lib/OAuth2Server.php
 * - lib/ApiKeys.php
 * - modules/api/ApiUI.php
 * - All handlers in modules/api/handlers/
 *
 * Checks performed:
 * 1. Secure token generation (random_bytes or openssl_random_pseudo_bytes)
 * 2. Timing-safe password comparison (password_verify, not ===)
 * 3. Token expiry enforcement (expires < NOW)
 * 4. All handlers receive userID for authorization
 * 5. API key comparison uses hash_equals (timing-safe)
 * 6. Auth-exempt endpoints are intentional (ping, auth, oauth only)
 *
 * @package    CATS
 * @subpackage Testing
 * @copyright  Copyright (C) 2026 Space-O Technologies
 */

// Severity levels
define('SEVERITY_CRITICAL', 'CRITICAL');
define('SEVERITY_HIGH', 'HIGH');
define('SEVERITY_MEDIUM', 'MEDIUM');
define('SEVERITY_LOW', 'LOW');
define('SEVERITY_INFO', 'INFO');
define('SEVERITY_PASS', 'PASS');

// Exit codes
define('EXIT_SUCCESS', 0);
define('EXIT_CRITICAL', 1);
define('EXIT_HIGH', 2);

class AuthSecurityAudit
{
    /**
     * @var string Base path to OpenCATS installation
     */
    private $basePath;

    /**
     * @var array Collection of audit findings
     */
    private $findings = [];

    /**
     * @var array Statistics counters
     */
    private $stats = [
        'total_checks' => 0,
        'passed' => 0,
        'critical' => 0,
        'high' => 0,
        'medium' => 0,
        'low' => 0,
        'info' => 0
    ];

    /**
     * @var array Files to audit
     */
    private $filesToAudit = [];

    /**
     * @var array Known auth-exempt endpoints (intentionally do not require auth)
     */
    private $authExemptEndpoints = [
        'ping',  // Health check endpoint
        'auth',  // Authentication endpoint itself
        'oauth'  // OAuth endpoints
    ];

    /**
     * Constructor
     *
     * @param string $basePath Base path to OpenCATS installation
     */
    public function __construct($basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Run the complete audit
     *
     * @return int Exit code
     */
    public function run()
    {
        $this->printHeader();

        // Discover files to audit
        $this->discoverFiles();

        if (empty($this->filesToAudit)) {
            $this->addFinding(SEVERITY_CRITICAL, 'General', 'No authentication files found to audit');
            $this->printSummary();
            return EXIT_CRITICAL;
        }

        // Run all audit checks
        $this->auditOAuth2Server();
        $this->auditApiKeys();
        $this->auditApiUI();
        $this->auditHandlers();

        // Print results
        $this->printFindings();
        $this->printSummary();

        // Return appropriate exit code
        if ($this->stats['critical'] > 0) {
            return EXIT_CRITICAL;
        }
        if ($this->stats['high'] > 0) {
            return EXIT_HIGH;
        }
        return EXIT_SUCCESS;
    }

    /**
     * Discover files to audit
     */
    private function discoverFiles()
    {
        $files = [
            'lib/OAuth2Server.php',
            'lib/ApiKeys.php',
            'modules/api/ApiUI.php'
        ];

        // Add handler files
        $handlerDir = $this->basePath . '/modules/api/handlers';
        if (is_dir($handlerDir)) {
            $handlerFiles = glob($handlerDir . '/*.php');
            foreach ($handlerFiles as $file) {
                $files[] = 'modules/api/handlers/' . basename($file);
            }
        }

        foreach ($files as $file) {
            $fullPath = $this->basePath . '/' . $file;
            if (file_exists($fullPath)) {
                $this->filesToAudit[$file] = $fullPath;
            }
        }
    }

    /**
     * Audit OAuth2Server.php for security issues
     */
    private function auditOAuth2Server()
    {
        $file = 'lib/OAuth2Server.php';
        if (!isset($this->filesToAudit[$file])) {
            $this->addFinding(SEVERITY_HIGH, $file, 'OAuth2Server.php not found - OAuth implementation missing');
            return;
        }

        $content = file_get_contents($this->filesToAudit[$file]);

        $this->printSection("Auditing: {$file}");

        // Check 1: Secure token generation
        $this->checkSecureTokenGeneration($file, $content);

        // Check 2: Timing-safe password comparison
        $this->checkTimingSafeComparison($file, $content);

        // Check 3: Token expiry enforcement
        $this->checkTokenExpiry($file, $content);

        // Check 4: Client secret hashing
        $this->checkSecretHashing($file, $content);

        // Check 5: SQL injection prevention
        $this->checkSqlParameterization($file, $content);
    }

    /**
     * Audit ApiKeys.php for security issues
     */
    private function auditApiKeys()
    {
        $file = 'lib/ApiKeys.php';
        if (!isset($this->filesToAudit[$file])) {
            $this->addFinding(SEVERITY_HIGH, $file, 'ApiKeys.php not found - API key authentication missing');
            return;
        }

        $content = file_get_contents($this->filesToAudit[$file]);

        $this->printSection("Auditing: {$file}");

        // Check 1: Secure token generation
        $this->checkSecureTokenGeneration($file, $content);

        // Check 2: Secret hashing (password_hash)
        $this->checkSecretHashing($file, $content);

        // Check 3: Timing-safe comparison for authentication
        $this->checkApiKeyTimingSafe($file, $content);

        // Check 4: SQL injection prevention
        $this->checkSqlParameterization($file, $content);

        // Check 5: Session token expiry
        $this->checkSessionTokenExpiry($file, $content);

        // Check 6: Insecure plaintext secret storage
        $this->checkPlaintextSecretStorage($file, $content);
    }

    /**
     * Audit ApiUI.php for security issues
     */
    private function auditApiUI()
    {
        $file = 'modules/api/ApiUI.php';
        if (!isset($this->filesToAudit[$file])) {
            $this->addFinding(SEVERITY_CRITICAL, $file, 'ApiUI.php not found - API module missing');
            return;
        }

        $content = file_get_contents($this->filesToAudit[$file]);

        $this->printSection("Auditing: {$file}");

        // Check 1: Auth-exempt endpoints are intentional
        $this->checkAuthExemptEndpoints($file, $content);

        // Check 2: Authentication enforced before routing
        $this->checkAuthEnforcement($file, $content);

        // Check 3: UserID passed to handlers
        $this->checkUserIdPassedToHandlers($file, $content);

        // Check 4: OAuth token validation
        $this->checkOAuthValidation($file, $content);

        // Check 5: CORS configuration
        $this->checkCorsConfiguration($file, $content);
    }

    /**
     * Audit all API handlers for authorization
     */
    private function auditHandlers()
    {
        $this->printSection("Auditing: API Handlers");

        $handlers = [];
        foreach ($this->filesToAudit as $file => $path) {
            if (strpos($file, 'modules/api/handlers/') === 0) {
                $handlers[$file] = $path;
            }
        }

        if (empty($handlers)) {
            $this->addFinding(SEVERITY_HIGH, 'handlers/', 'No API handlers found');
            return;
        }

        foreach ($handlers as $file => $path) {
            $content = file_get_contents($path);

            // Check 1: Constructor receives userID
            $this->checkHandlerReceivesUserId($file, $content);

            // Check 2: Handler uses userID for authorization
            $this->checkHandlerUsesUserId($file, $content);
        }
    }

    /**
     * Check for secure token generation (random_bytes or openssl_random_pseudo_bytes)
     */
    private function checkSecureTokenGeneration($file, $content)
    {
        $this->stats['total_checks']++;

        // Check for random_bytes usage
        $hasRandomBytes = preg_match('/random_bytes\s*\(/i', $content);

        // Check for openssl_random_pseudo_bytes usage
        $hasOpenssl = preg_match('/openssl_random_pseudo_bytes\s*\(/i', $content);

        // Check for insecure methods
        $hasInsecure = preg_match('/(mt_rand|rand|uniqid|sha1\(time|md5\(time|microtime)/i', $content);

        if ($hasRandomBytes || $hasOpenssl) {
            if ($hasRandomBytes) {
                $this->addFinding(SEVERITY_PASS, $file, 'Uses random_bytes() for secure token generation');
            }
            if ($hasOpenssl) {
                $this->addFinding(SEVERITY_PASS, $file, 'Uses openssl_random_pseudo_bytes() as fallback for token generation');
            }
            $this->stats['passed']++;
        } else {
            $this->addFinding(SEVERITY_CRITICAL, $file, 'No secure random token generation found. Must use random_bytes() or openssl_random_pseudo_bytes()');
        }

        if ($hasInsecure) {
            // Check if it's actually used for key generation
            if (preg_match('/(mt_rand|rand)\s*\([^)]*\)[^;]*[\'"]?[a-zA-Z]*key/i', $content)) {
                $this->addFinding(SEVERITY_HIGH, $file, 'Potentially insecure random function used for key/token generation (mt_rand, rand, etc.)');
            } else {
                $this->addFinding(SEVERITY_LOW, $file, 'Insecure random function found, but may not be used for security-sensitive operations');
            }
        }
    }

    /**
     * Check for timing-safe password comparison (password_verify)
     */
    private function checkTimingSafeComparison($file, $content)
    {
        $this->stats['total_checks']++;

        // Check for password_verify usage
        $hasPasswordVerify = preg_match('/password_verify\s*\(/i', $content);

        // Check for direct === comparison with secret/password
        $hasDirectComparison = preg_match('/\$[a-zA-Z_]*(?:secret|password|hash)[a-zA-Z_]*\s*===\s*\$|\$[a-zA-Z_]*\s*===\s*\$[a-zA-Z_]*(?:secret|password|hash)/i', $content);

        if ($hasPasswordVerify) {
            $this->addFinding(SEVERITY_PASS, $file, 'Uses password_verify() for timing-safe password comparison');
            $this->stats['passed']++;
        } else {
            $this->addFinding(SEVERITY_HIGH, $file, 'password_verify() not found - may be vulnerable to timing attacks');
        }

        if ($hasDirectComparison) {
            $this->addFinding(SEVERITY_MEDIUM, $file, 'Direct === comparison found with secret/password variable - potential timing attack vector');
        }
    }

    /**
     * Check for token expiry enforcement
     */
    private function checkTokenExpiry($file, $content)
    {
        $this->stats['total_checks']++;

        // Check for expiry comparison patterns
        $hasExpiryCheck = preg_match('/expires[_a-zA-Z]*\s*[<>]=?\s*(time|NOW|strtotime)|strtotime\s*\(\s*\$[a-zA-Z_]*expires/i', $content);

        // Check for expiry constants
        $hasExpiryConstants = preg_match('/const\s+[A-Z_]*(?:LIFETIME|EXPIRY|EXPIRE)[A-Z_]*\s*=\s*\d+/i', $content);

        if ($hasExpiryCheck) {
            $this->addFinding(SEVERITY_PASS, $file, 'Token expiry enforcement found');
            $this->stats['passed']++;
        } else {
            $this->addFinding(SEVERITY_HIGH, $file, 'No token expiry enforcement pattern found');
        }

        if ($hasExpiryConstants) {
            $this->addFinding(SEVERITY_PASS, $file, 'Token lifetime constants defined');
            $this->stats['passed']++;
        }
    }

    /**
     * Check for secret hashing with password_hash
     */
    private function checkSecretHashing($file, $content)
    {
        $this->stats['total_checks']++;

        // Check for password_hash usage
        $hasPasswordHash = preg_match('/password_hash\s*\(/i', $content);

        // Check for storing plaintext secrets
        $storesPlaintext = preg_match('/INSERT\s+INTO[^;]+(?:secret|password)\s*=\s*%s[^;]+makeQueryString\s*\(\s*\$[a-zA-Z_]*(?:secret|password)\s*\)/i', $content);

        if ($hasPasswordHash) {
            $this->addFinding(SEVERITY_PASS, $file, 'Uses password_hash() for secure secret storage');
            $this->stats['passed']++;
        }

        if ($storesPlaintext && !$hasPasswordHash) {
            $this->addFinding(SEVERITY_HIGH, $file, 'Secrets may be stored in plaintext without hashing');
        }
    }

    /**
     * Check for SQL parameterization to prevent injection
     */
    private function checkSqlParameterization($file, $content)
    {
        $this->stats['total_checks']++;

        // Check for makeQueryString usage (OpenCATS parameterization)
        $hasParameterization = preg_match('/makeQueryString\s*\(|makeQueryInteger\s*\(/i', $content);

        // Check for potential raw variable interpolation in SQL
        $hasRawInterpolation = preg_match('/sprintf\s*\([^)]*"\s*(?:SELECT|INSERT|UPDATE|DELETE)[^"]*\$[a-zA-Z_]+[^"]*"/i', $content);

        if ($hasParameterization) {
            $this->addFinding(SEVERITY_PASS, $file, 'Uses parameterized queries (makeQueryString/makeQueryInteger)');
            $this->stats['passed']++;
        }

        if ($hasRawInterpolation) {
            $this->addFinding(SEVERITY_MEDIUM, $file, 'Potential raw variable interpolation in SQL - verify parameterization');
        }
    }

    /**
     * Check API key comparison for timing safety
     */
    private function checkApiKeyTimingSafe($file, $content)
    {
        $this->stats['total_checks']++;

        // Check for hash_equals usage
        $hasHashEquals = preg_match('/hash_equals\s*\(/i', $content);

        // Check for direct string comparison with api_key
        $hasDirectApiKeyComparison = preg_match('/\$[a-zA-Z_]*(?:api_?key|api_?secret)[a-zA-Z_]*\s*===\s*(?:\$|\"|\')|(?:\$|\"|\')\s*===\s*\$[a-zA-Z_]*(?:api_?key|api_?secret)/i', $content);

        // Using password_verify is also timing-safe
        $hasPasswordVerify = preg_match('/password_verify\s*\(/i', $content);

        if ($hasHashEquals) {
            $this->addFinding(SEVERITY_PASS, $file, 'Uses hash_equals() for timing-safe API key comparison');
            $this->stats['passed']++;
        } elseif ($hasPasswordVerify) {
            $this->addFinding(SEVERITY_PASS, $file, 'Uses password_verify() for timing-safe secret comparison (acceptable)');
            $this->stats['passed']++;
        }

        if ($hasDirectApiKeyComparison && !$hasHashEquals && !$hasPasswordVerify) {
            $this->addFinding(SEVERITY_MEDIUM, $file, 'Direct === comparison found for API key/secret - consider using hash_equals() or password_verify()');
        }
    }

    /**
     * Check for session token expiry
     */
    private function checkSessionTokenExpiry($file, $content)
    {
        $this->stats['total_checks']++;

        // Check for expires_date in session validation
        $hasSessionExpiry = preg_match('/expires[_a-zA-Z]*\s*[>]?\s*NOW\(\)|NOW\(\)\s*[<]?\s*expires/i', $content);

        // Check for TOKEN_EXPIRY constant
        $hasExpiryConstant = preg_match('/const\s+TOKEN[_A-Z]*EXPIRY\s*=/i', $content);

        if ($hasSessionExpiry || $hasExpiryConstant) {
            $this->addFinding(SEVERITY_PASS, $file, 'Session token expiry enforcement found');
            $this->stats['passed']++;
        } else {
            $this->addFinding(SEVERITY_MEDIUM, $file, 'Session token expiry validation not clearly evident');
        }
    }

    /**
     * Check for plaintext secret storage (development mode)
     */
    private function checkPlaintextSecretStorage($file, $content)
    {
        $this->stats['total_checks']++;

        // Check for createSimple method or plaintext storage comments
        $hasPlaintextMethod = preg_match('/function\s+createSimple\s*\(|Stored\s+in\s+plaintext|for\s+dev/i', $content);

        if ($hasPlaintextMethod) {
            $this->addFinding(SEVERITY_MEDIUM, $file, 'Plaintext secret storage method found (createSimple) - ensure only used in development');
        } else {
            $this->addFinding(SEVERITY_PASS, $file, 'No plaintext secret storage method detected');
            $this->stats['passed']++;
        }
    }

    /**
     * Check that only intended endpoints are auth-exempt
     */
    private function checkAuthExemptEndpoints($file, $content)
    {
        $this->stats['total_checks']++;

        // Extract the auth check condition
        preg_match('/if\s*\(\s*\$action\s*!==\s*[\'"]([^"\']+)[\'"]\s*&&\s*\$action\s*!==\s*[\'"]([^"\']+)[\'"]\s*&&\s*\$action\s*!==\s*[\'"]([^"\']+)[\'"]/', $content, $matches);

        if (count($matches) >= 4) {
            $exemptEndpoints = array_slice($matches, 1);
            $expectedExempt = $this->authExemptEndpoints;

            $unexpected = array_diff($exemptEndpoints, $expectedExempt);
            $missing = array_diff($expectedExempt, $exemptEndpoints);

            if (empty($unexpected) && empty($missing)) {
                $this->addFinding(SEVERITY_PASS, $file, 'Auth-exempt endpoints are correct: ' . implode(', ', $exemptEndpoints));
                $this->stats['passed']++;
            } else {
                if (!empty($unexpected)) {
                    $this->addFinding(SEVERITY_HIGH, $file, 'Unexpected auth-exempt endpoints found: ' . implode(', ', $unexpected));
                }
                if (!empty($missing)) {
                    $this->addFinding(SEVERITY_INFO, $file, 'Expected exempt endpoints not found: ' . implode(', ', $missing));
                }
            }
        } else {
            // Try simpler pattern
            if (preg_match('/auth.*ping.*oauth|ping.*auth.*oauth/i', $content)) {
                $this->addFinding(SEVERITY_PASS, $file, 'Auth-exempt endpoints appear to be ping, auth, oauth');
                $this->stats['passed']++;
            } else {
                $this->addFinding(SEVERITY_MEDIUM, $file, 'Could not determine auth-exempt endpoints - manual review needed');
            }
        }
    }

    /**
     * Check that authentication is enforced before routing
     */
    private function checkAuthEnforcement($file, $content)
    {
        $this->stats['total_checks']++;

        // Check for _authenticate call before _routeRequest
        $hasAuthBeforeRoute = preg_match('/_authenticate\s*\(\s*\)[^}]+_routeRequest/s', $content);

        // Check for auth check with return on failure
        $hasAuthReturnOnFailure = preg_match('/_authenticate\s*\(\s*\)[^{]*{[^}]*sendError[^}]*return/s', $content) ||
                                   preg_match('/!\$this->_authenticate\s*\(\s*\)[^}]+sendError[^}]+return/s', $content);

        if ($hasAuthBeforeRoute || $hasAuthReturnOnFailure) {
            $this->addFinding(SEVERITY_PASS, $file, 'Authentication enforced before request routing');
            $this->stats['passed']++;
        } else {
            $this->addFinding(SEVERITY_CRITICAL, $file, 'Authentication may not be enforced before request routing');
        }
    }

    /**
     * Check that userID is passed to handlers
     */
    private function checkUserIdPassedToHandlers($file, $content)
    {
        $this->stats['total_checks']++;

        // Check handler instantiation includes userID
        $handlerPatterns = preg_match_all('/new\s+\w+Handler\s*\([^)]+\$this->_userID[^)]*\)/i', $content, $matches);

        // Count total handler instantiations
        $totalHandlers = preg_match_all('/new\s+\w+Handler\s*\(/i', $content);

        // Handlers that don't need userID (meta-only handlers)
        $metaHandlersWithoutUser = preg_match_all('/new\s+(?:MetaHandler|OAuthHandler)\s*\(/i', $content);
        $adjustedTotal = $totalHandlers - $metaHandlersWithoutUser;

        if ($adjustedTotal > 0) {
            if ($handlerPatterns >= $adjustedTotal) {
                $this->addFinding(SEVERITY_PASS, $file, "All data handlers receive userID for authorization ({$handlerPatterns} handlers)");
                $this->stats['passed']++;
            } else {
                $this->addFinding(SEVERITY_HIGH, $file, "Only {$handlerPatterns} of {$adjustedTotal} handlers receive userID - some may lack authorization");
            }
        } else {
            $this->addFinding(SEVERITY_INFO, $file, 'No data handlers found that require userID');
        }
    }

    /**
     * Check OAuth token validation
     */
    private function checkOAuthValidation($file, $content)
    {
        $this->stats['total_checks']++;

        // Check for OAuth validateAccessToken call
        $hasOAuthValidation = preg_match('/validateAccessToken\s*\(/i', $content);

        // Check for OAuth2Server instantiation
        $hasOAuthServer = preg_match('/new\s+OAuth2Server\s*\(/i', $content);

        if ($hasOAuthValidation && $hasOAuthServer) {
            $this->addFinding(SEVERITY_PASS, $file, 'OAuth 2.0 access token validation implemented');
            $this->stats['passed']++;
        } elseif ($hasOAuthServer) {
            $this->addFinding(SEVERITY_MEDIUM, $file, 'OAuth2Server instantiated but validateAccessToken not found');
        } else {
            $this->addFinding(SEVERITY_INFO, $file, 'OAuth2Server not used in this file');
        }
    }

    /**
     * Check CORS configuration
     */
    private function checkCorsConfiguration($file, $content)
    {
        $this->stats['total_checks']++;

        // Check for wildcard CORS
        $hasWildcardCors = preg_match('/Access-Control-Allow-Origin[\'"]?\s*:\s*[\'"]?\s*\*/', $content);

        // Check for configurable CORS
        $hasConfigurableCors = preg_match('/API_CORS|CORS_ALLOWED/i', $content);

        if ($hasWildcardCors && !$hasConfigurableCors) {
            $this->addFinding(SEVERITY_MEDIUM, $file, 'Hardcoded wildcard (*) CORS origin - consider restricting in production');
        } elseif ($hasConfigurableCors) {
            $this->addFinding(SEVERITY_PASS, $file, 'CORS origin is configurable via constants');
            $this->stats['passed']++;
        } else {
            $this->addFinding(SEVERITY_INFO, $file, 'No CORS configuration found');
        }
    }

    /**
     * Check that handler constructor receives userID
     */
    private function checkHandlerReceivesUserId($file, $content)
    {
        $this->stats['total_checks']++;

        // Extract class name
        preg_match('/class\s+(\w+Handler)/i', $content, $classMatch);
        $className = isset($classMatch[1]) ? $classMatch[1] : basename($file, '.php');

        // Skip handlers that don't need userID
        if (preg_match('/^(Meta|OAuth)Handler$/i', $className)) {
            $this->addFinding(SEVERITY_INFO, $file, "{$className} does not require userID (meta/auth handler)");
            return;
        }

        // Check constructor for userID parameter
        $hasUserIdInConstructor = preg_match('/function\s+__construct\s*\([^)]*\$[a-zA-Z_]*user[iI][dD][^)]*\)/i', $content);

        // Check for userID property assignment
        $hasUserIdProperty = preg_match('/\$this->_userID\s*=\s*\$/i', $content);

        if ($hasUserIdInConstructor && $hasUserIdProperty) {
            $this->addFinding(SEVERITY_PASS, $file, "{$className} receives and stores userID for authorization");
            $this->stats['passed']++;
        } elseif ($hasUserIdInConstructor) {
            $this->addFinding(SEVERITY_LOW, $file, "{$className} receives userID but property assignment not found");
        } else {
            $this->addFinding(SEVERITY_HIGH, $file, "{$className} does not receive userID in constructor - may lack authorization");
        }
    }

    /**
     * Check that handler uses userID for operations
     */
    private function checkHandlerUsesUserId($file, $content)
    {
        $this->stats['total_checks']++;

        // Extract class name
        preg_match('/class\s+(\w+Handler)/i', $content, $classMatch);
        $className = isset($classMatch[1]) ? $classMatch[1] : basename($file, '.php');

        // Skip meta/auth handlers
        if (preg_match('/^(Meta|OAuth)Handler$/i', $className)) {
            return;
        }

        // Check for userID usage in operations
        $usesUserIdInOperations = preg_match('/\$this->_userID/', $content);

        if ($usesUserIdInOperations) {
            $this->addFinding(SEVERITY_PASS, $file, "{$className} uses userID for operations");
            $this->stats['passed']++;
        } else {
            $this->addFinding(SEVERITY_MEDIUM, $file, "{$className} does not appear to use userID - verify authorization logic");
        }
    }

    /**
     * Add a finding to the collection
     */
    private function addFinding($severity, $file, $description)
    {
        $this->findings[] = [
            'severity' => $severity,
            'file' => $file,
            'description' => $description
        ];

        // Update stats
        switch ($severity) {
            case SEVERITY_CRITICAL:
                $this->stats['critical']++;
                break;
            case SEVERITY_HIGH:
                $this->stats['high']++;
                break;
            case SEVERITY_MEDIUM:
                $this->stats['medium']++;
                break;
            case SEVERITY_LOW:
                $this->stats['low']++;
                break;
            case SEVERITY_INFO:
                $this->stats['info']++;
                break;
        }
    }

    /**
     * Print the audit header
     */
    private function printHeader()
    {
        echo "\n";
        echo "==========================================================================\n";
        echo "  OpenCATS Authentication & Authorization Security Audit\n";
        echo "==========================================================================\n";
        echo "  Base Path: {$this->basePath}\n";
        echo "  Date: " . date('Y-m-d H:i:s') . "\n";
        echo "==========================================================================\n\n";
    }

    /**
     * Print a section header
     */
    private function printSection($title)
    {
        echo "\n--- {$title} ---\n";
    }

    /**
     * Print all findings
     */
    private function printFindings()
    {
        echo "\n";
        echo "==========================================================================\n";
        echo "  FINDINGS\n";
        echo "==========================================================================\n\n";

        // Group findings by severity
        $bySeverity = [
            SEVERITY_CRITICAL => [],
            SEVERITY_HIGH => [],
            SEVERITY_MEDIUM => [],
            SEVERITY_LOW => [],
            SEVERITY_INFO => [],
            SEVERITY_PASS => []
        ];

        foreach ($this->findings as $finding) {
            $bySeverity[$finding['severity']][] = $finding;
        }

        // Print critical and high first
        foreach ([SEVERITY_CRITICAL, SEVERITY_HIGH, SEVERITY_MEDIUM, SEVERITY_LOW, SEVERITY_INFO] as $severity) {
            if (!empty($bySeverity[$severity])) {
                echo "[{$severity}]\n";
                foreach ($bySeverity[$severity] as $finding) {
                    echo "  [{$severity}] {$finding['file']}: {$finding['description']}\n";
                }
                echo "\n";
            }
        }

        // Print passed checks
        if (!empty($bySeverity[SEVERITY_PASS])) {
            echo "[PASSED CHECKS]\n";
            foreach ($bySeverity[SEVERITY_PASS] as $finding) {
                echo "  [PASS] {$finding['file']}: {$finding['description']}\n";
            }
            echo "\n";
        }
    }

    /**
     * Print audit summary
     */
    private function printSummary()
    {
        echo "\n";
        echo "==========================================================================\n";
        echo "  SUMMARY\n";
        echo "==========================================================================\n";
        echo "  Files Audited:    " . count($this->filesToAudit) . "\n";
        echo "  Total Checks:     {$this->stats['total_checks']}\n";
        echo "  Passed:           {$this->stats['passed']}\n";
        echo "  --------------------\n";
        echo "  CRITICAL:         {$this->stats['critical']}\n";
        echo "  HIGH:             {$this->stats['high']}\n";
        echo "  MEDIUM:           {$this->stats['medium']}\n";
        echo "  LOW:              {$this->stats['low']}\n";
        echo "  INFO:             {$this->stats['info']}\n";
        echo "==========================================================================\n";

        if ($this->stats['critical'] > 0) {
            echo "\n  *** CRITICAL ISSUES FOUND - IMMEDIATE ACTION REQUIRED ***\n";
        } elseif ($this->stats['high'] > 0) {
            echo "\n  ** HIGH SEVERITY ISSUES FOUND - ACTION RECOMMENDED **\n";
        } else {
            echo "\n  Authentication audit completed. Review findings above.\n";
        }
        echo "\n";
    }
}

// =============================================================================
// MAIN EXECUTION
// =============================================================================

// Determine base path
$basePath = dirname(dirname(dirname(__FILE__))); // Go up from test/security/ to root

// Allow override via command line argument
if (isset($argv[1])) {
    $basePath = $argv[1];
}

// Verify base path
if (!file_exists($basePath . '/lib/DatabaseConnection.php')) {
    echo "Error: Could not find OpenCATS installation at: {$basePath}\n";
    echo "Usage: php auth_audit.php [/path/to/opencats]\n";
    exit(1);
}

// Run the audit
$audit = new AuthSecurityAudit($basePath);
$exitCode = $audit->run();

exit($exitCode);
