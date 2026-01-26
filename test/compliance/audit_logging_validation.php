#!/usr/bin/env php
<?php
/**
 * OpenCATS REST API - Audit Logging Validation Script
 *
 * Purpose: Verify ApiRequestLogger captures required audit trail data.
 *
 * This script validates:
 * 1. api_key_id - Who made the request
 * 2. endpoint - What was accessed
 * 3. method - HTTP method used
 * 4. response_code/status_code - Result of request
 * 5. request_time/timestamp - When it happened
 * 6. ip_address - Client IP
 *
 * Also verifies logs are stored in database (INSERT INTO api_request_log)
 *
 * @package    OpenCATS
 * @subpackage Tests
 * @copyright  2026 Space-O Technologies
 */

// Color output for terminal
define('COLOR_GREEN', "\033[32m");
define('COLOR_RED', "\033[31m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_RESET', "\033[0m");

class AuditLoggingValidator
{
    private $sourceFile;
    private $sourceCode;
    private $results = [];
    private $passed = 0;
    private $failed = 0;

    /**
     * Required audit fields with alternative names
     */
    private $requiredFields = [
        'api_key_id' => [
            'patterns' => ['api_key_id', 'apiKeyID', '_apiKeyID', 'apiKeyId'],
            'description' => 'Who made the request',
            'found' => false,
            'details' => ''
        ],
        'endpoint' => [
            'patterns' => ['endpoint', '_endpoint', 'path', 'uri', 'route'],
            'description' => 'What was accessed',
            'found' => false,
            'details' => ''
        ],
        'method' => [
            'patterns' => ['method', '_method', 'http_method', 'request_method'],
            'description' => 'HTTP method used',
            'found' => false,
            'details' => ''
        ],
        'response_code' => [
            'patterns' => ['status_code', 'statusCode', 'response_code', 'responseCode', 'http_code'],
            'description' => 'Result of request',
            'found' => false,
            'details' => ''
        ],
        'request_time' => [
            'patterns' => ['request_time', 'timestamp', 'date', 'created_at', 'NOW()'],
            'description' => 'When it happened',
            'found' => false,
            'details' => ''
        ],
        'ip_address' => [
            'patterns' => ['ip_address', 'ipAddress', '_ipAddress', 'remote_addr', 'REMOTE_ADDR', 'client_ip'],
            'description' => 'Client IP address',
            'found' => false,
            'details' => ''
        ]
    ];

    /**
     * Constructor
     *
     * @param string $sourceFile Path to ApiRequestLogger.php
     */
    public function __construct($sourceFile)
    {
        $this->sourceFile = $sourceFile;
    }

    /**
     * Run all validations
     *
     * @return bool True if all validations pass
     */
    public function validate()
    {
        $this->printHeader();

        // Load source file
        if (!$this->loadSourceFile()) {
            return false;
        }

        // Validate required fields
        $this->printSection("Required Audit Fields");
        $this->validateRequiredFields();

        // Validate database storage
        $this->printSection("Storage Verification");
        $this->validateDatabaseStorage();

        // Validate class structure
        $this->printSection("Class Structure");
        $this->validateClassStructure();

        // Print summary
        $this->printSummary();

        return $this->failed === 0;
    }

    /**
     * Load and validate source file
     *
     * @return bool
     */
    private function loadSourceFile()
    {
        if (!file_exists($this->sourceFile)) {
            $this->printResult('[FAIL]', "Source file not found: {$this->sourceFile}", false);
            $this->failed++;
            return false;
        }

        $this->sourceCode = file_get_contents($this->sourceFile);

        if (empty($this->sourceCode)) {
            $this->printResult('[FAIL]', "Source file is empty", false);
            $this->failed++;
            return false;
        }

        $this->printResult('[PASS]', "Source file loaded: {$this->sourceFile}", true);
        $this->passed++;

        return true;
    }

    /**
     * Validate all required audit fields are captured
     */
    private function validateRequiredFields()
    {
        foreach ($this->requiredFields as $fieldName => &$field) {
            $found = false;
            $matchedPattern = '';

            foreach ($field['patterns'] as $pattern) {
                // Check if pattern exists in source code (case-insensitive for some)
                if (stripos($this->sourceCode, $pattern) !== false) {
                    $found = true;
                    $matchedPattern = $pattern;
                    break;
                }
            }

            $field['found'] = $found;

            if ($found) {
                // Extract context around the match
                $context = $this->extractContext($matchedPattern);
                $field['details'] = "Found as '{$matchedPattern}'" . ($context ? " - {$context}" : "");
                $this->printResult(
                    '[PASS]',
                    "{$fieldName}: {$field['description']}",
                    true,
                    $field['details']
                );
                $this->passed++;
            } else {
                $field['details'] = "Not found. Searched for: " . implode(', ', $field['patterns']);
                $this->printResult(
                    '[FAIL]',
                    "{$fieldName}: {$field['description']}",
                    false,
                    $field['details']
                );
                $this->failed++;
            }
        }
    }

    /**
     * Validate database storage mechanism
     */
    private function validateDatabaseStorage()
    {
        $checks = [
            'insert_statement' => [
                'pattern' => '/INSERT\s+INTO\s+api_request_log/i',
                'description' => 'Database INSERT statement for api_request_log',
                'required' => true
            ],
            'database_connection' => [
                'pattern' => '/DatabaseConnection::getInstance|new\s+DatabaseConnection|PDO/i',
                'description' => 'Database connection instantiation',
                'required' => true
            ],
            'query_execution' => [
                'pattern' => '/->query\s*\(|->execute\s*\(/i',
                'description' => 'Query execution method',
                'required' => true
            ],
            'not_file_only' => [
                'pattern' => '/file_put_contents|fwrite|error_log\s*\(\s*[\'"][^\'"]+[\'"]\s*,/i',
                'description' => 'Not using file-only logging (should NOT match)',
                'required' => false,
                'inverse' => true
            ]
        ];

        foreach ($checks as $checkName => $check) {
            $matches = preg_match($check['pattern'], $this->sourceCode);
            $passed = false;
            $details = '';

            if (isset($check['inverse']) && $check['inverse']) {
                // For inverse checks, NOT matching is success
                $passed = !$matches;
                if ($passed) {
                    $details = "No file-only logging detected (good)";
                } else {
                    $details = "Warning: File-based logging detected";
                }
            } else {
                $passed = (bool) $matches;
                if ($passed) {
                    // Extract the matched text for context
                    preg_match($check['pattern'], $this->sourceCode, $matchedText);
                    $details = "Pattern matched" . (isset($matchedText[0]) ? ": {$matchedText[0]}" : "");
                } else {
                    $details = "Pattern not found";
                }
            }

            if ($passed) {
                $this->printResult('[PASS]', $check['description'], true, $details);
                $this->passed++;
            } else {
                if ($check['required']) {
                    $this->printResult('[FAIL]', $check['description'], false, $details);
                    $this->failed++;
                } else {
                    $this->printResult('[WARN]', $check['description'], null, $details);
                }
            }
        }

        // Additional check: Verify INSERT contains all required fields
        $this->validateInsertStatement();
    }

    /**
     * Validate the INSERT statement contains all required fields
     */
    private function validateInsertStatement()
    {
        // Extract INSERT statement
        if (preg_match('/INSERT\s+INTO\s+api_request_log\s*\([^)]+\)/is', $this->sourceCode, $match)) {
            $insertStatement = $match[0];
            $requiredInInsert = [
                'api_key_id',
                'endpoint',
                'method',
                'status_code',
                'request_time',
                'ip_address'
            ];

            $missingFields = [];
            foreach ($requiredInInsert as $field) {
                if (stripos($insertStatement, $field) === false) {
                    $missingFields[] = $field;
                }
            }

            if (empty($missingFields)) {
                $this->printResult(
                    '[PASS]',
                    "INSERT statement contains all required audit fields",
                    true,
                    "All 6 required fields present in INSERT"
                );
                $this->passed++;
            } else {
                $this->printResult(
                    '[FAIL]',
                    "INSERT statement missing audit fields",
                    false,
                    "Missing: " . implode(', ', $missingFields)
                );
                $this->failed++;
            }
        } else {
            $this->printResult(
                '[FAIL]',
                "Could not parse INSERT statement",
                false,
                "Unable to extract INSERT INTO api_request_log statement"
            );
            $this->failed++;
        }
    }

    /**
     * Validate class structure and methods
     */
    private function validateClassStructure()
    {
        $structureChecks = [
            'class_definition' => [
                'pattern' => '/class\s+ApiRequestLogger/',
                'description' => 'ApiRequestLogger class defined'
            ],
            'log_method' => [
                'pattern' => '/function\s+log\s*\(/i',
                'description' => 'log() method exists'
            ],
            'constructor' => [
                'pattern' => '/function\s+__construct\s*\(/i',
                'description' => '__construct() method for initialization'
            ],
            'ip_capture' => [
                'pattern' => '/_getClientIP|getClientIP|getRemoteAddr/i',
                'description' => 'IP address capture method'
            ],
            'sql_injection_protection' => [
                'pattern' => '/makeQueryString|prepare|bindParam|bindValue|escape/i',
                'description' => 'SQL injection protection'
            ]
        ];

        foreach ($structureChecks as $checkName => $check) {
            $matches = preg_match($check['pattern'], $this->sourceCode);

            if ($matches) {
                $this->printResult('[PASS]', $check['description'], true);
                $this->passed++;
            } else {
                $this->printResult('[FAIL]', $check['description'], false);
                $this->failed++;
            }
        }
    }

    /**
     * Extract context around a matched pattern
     *
     * @param string $pattern Pattern to find
     * @return string Context snippet
     */
    private function extractContext($pattern)
    {
        $pos = stripos($this->sourceCode, $pattern);
        if ($pos === false) {
            return '';
        }

        // Get surrounding context (50 chars before and after)
        $start = max(0, $pos - 30);
        $length = strlen($pattern) + 60;
        $context = substr($this->sourceCode, $start, $length);

        // Clean up whitespace
        $context = preg_replace('/\s+/', ' ', $context);
        $context = trim($context);

        return strlen($context) > 60 ? substr($context, 0, 60) . '...' : $context;
    }

    /**
     * Print header
     */
    private function printHeader()
    {
        echo "\n";
        echo COLOR_BLUE . str_repeat("=", 70) . COLOR_RESET . "\n";
        echo COLOR_BLUE . "  OpenCATS REST API - Audit Logging Validation" . COLOR_RESET . "\n";
        echo COLOR_BLUE . str_repeat("=", 70) . COLOR_RESET . "\n";
        echo "\n";
        echo "Source: " . $this->sourceFile . "\n";
        echo "Date: " . date('Y-m-d H:i:s') . "\n";
        echo "\n";
    }

    /**
     * Print section header
     *
     * @param string $title Section title
     */
    private function printSection($title)
    {
        echo "\n";
        echo COLOR_YELLOW . "--- {$title} ---" . COLOR_RESET . "\n";
        echo "\n";
    }

    /**
     * Print a validation result
     *
     * @param string      $status  Status indicator ([PASS], [FAIL], [WARN])
     * @param string      $message Result message
     * @param bool|null   $success True for pass, false for fail, null for warning
     * @param string|null $details Additional details
     */
    private function printResult($status, $message, $success = null, $details = null)
    {
        if ($success === true) {
            $color = COLOR_GREEN;
        } elseif ($success === false) {
            $color = COLOR_RED;
        } else {
            $color = COLOR_YELLOW;
        }

        echo $color . $status . COLOR_RESET . " " . $message . "\n";

        if ($details) {
            echo "       " . COLOR_BLUE . $details . COLOR_RESET . "\n";
        }
    }

    /**
     * Print summary
     */
    private function printSummary()
    {
        echo "\n";
        echo COLOR_BLUE . str_repeat("=", 70) . COLOR_RESET . "\n";
        echo COLOR_BLUE . "  Validation Summary" . COLOR_RESET . "\n";
        echo COLOR_BLUE . str_repeat("=", 70) . COLOR_RESET . "\n";
        echo "\n";

        $total = $this->passed + $this->failed;

        echo COLOR_GREEN . "Passed: {$this->passed}" . COLOR_RESET . "\n";
        echo COLOR_RED . "Failed: {$this->failed}" . COLOR_RESET . "\n";
        echo "Total:  {$total}\n";
        echo "\n";

        if ($this->failed === 0) {
            echo COLOR_GREEN . "[SUCCESS] All audit logging validations passed!" . COLOR_RESET . "\n";
        } else {
            echo COLOR_RED . "[FAILURE] {$this->failed} validation(s) failed. Review required." . COLOR_RESET . "\n";
        }

        echo "\n";
        echo COLOR_BLUE . str_repeat("=", 70) . COLOR_RESET . "\n";
        echo "\n";
    }

    /**
     * Get results as array
     *
     * @return array
     */
    public function getResults()
    {
        return [
            'passed' => $this->passed,
            'failed' => $this->failed,
            'total' => $this->passed + $this->failed,
            'fields' => $this->requiredFields
        ];
    }
}

// =============================================================================
// Main Execution
// =============================================================================

// Determine base path
$basePath = dirname(dirname(dirname(__FILE__)));
$sourceFile = $basePath . '/lib/ApiRequestLogger.php';

// Allow command-line override
if (isset($argv[1])) {
    $sourceFile = $argv[1];
}

// Check for help
if (isset($argv[1]) && in_array($argv[1], ['-h', '--help'])) {
    echo "Usage: php audit_logging_validation.php [source_file]\n";
    echo "\n";
    echo "Arguments:\n";
    echo "  source_file    Path to ApiRequestLogger.php (optional)\n";
    echo "                 Default: {$basePath}/lib/ApiRequestLogger.php\n";
    echo "\n";
    echo "Purpose:\n";
    echo "  Validates that the ApiRequestLogger captures all required audit trail data:\n";
    echo "  - api_key_id: Who made the request\n";
    echo "  - endpoint: What was accessed\n";
    echo "  - method: HTTP method used\n";
    echo "  - response_code: Result of request\n";
    echo "  - request_time: When it happened\n";
    echo "  - ip_address: Client IP address\n";
    echo "\n";
    echo "  Also verifies logs are stored in database (not just files).\n";
    echo "\n";
    exit(0);
}

// Run validation
$validator = new AuditLoggingValidator($sourceFile);
$success = $validator->validate();

// Exit with appropriate code
exit($success ? 0 : 1);
