#!/usr/bin/env php
<?php
/**
 * PII (Personally Identifiable Information) Handling Audit Script
 *
 * Verifies proper handling of personally identifiable information in the OpenCATS REST API.
 *
 * Files checked:
 * - All library files in lib/ (OAuth2Server, Webhook*, Api*, JobSubmissions, Placements, Notes, Appointments, Tasks, Tearsheets)
 * - All handlers in modules/api/handlers/
 *
 * Checks performed:
 * 1. CRITICAL: No plain text password storage (INSERT...password without password_hash)
 * 2. HIGH: No passwords logged (error_log.*password, log.*password)
 * 3. HIGH: No API keys/secrets in error messages (sendError.*key|secret|token)
 * 4. MEDIUM: Webhook payloads sanitize sensitive data (look for sanitize/redact patterns)
 * 5. MEDIUM: Request logger doesn't log full request body with passwords
 *
 * PII fields tracked:
 * - password, secret, token, api_key
 * - social_security, ssn
 * - credit_card, card_number
 *
 * @package    CATS
 * @subpackage Compliance
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
define('EXIT_CRITICAL_HIGH', 1);

/**
 * PII Handling Audit Class
 */
class PIIHandlingAudit
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
     * @var array PII field patterns to check for
     */
    private $piiFields = [
        'password' => 'Password',
        'secret' => 'Secret/API Secret',
        'token' => 'Token',
        'api_key' => 'API Key',
        'apikey' => 'API Key',
        'api_secret' => 'API Secret',
        'social_security' => 'Social Security Number',
        'ssn' => 'SSN',
        'credit_card' => 'Credit Card',
        'card_number' => 'Card Number',
        'cvv' => 'CVV',
        'private_key' => 'Private Key',
        'access_token' => 'Access Token',
        'refresh_token' => 'Refresh Token',
        'client_secret' => 'Client Secret'
    ];

    /**
     * @var array Library files to audit
     */
    private $libraryPatterns = [
        'OAuth2Server.php',
        'WebhookSubscription.php',
        'WebhookDispatcher.php',
        'ApiKeys.php',
        'ApiResponse.php',
        'ApiRequestLogger.php',
        'ApiConfig.php',
        'ApiRateLimiter.php',
        'JobSubmissions.php',
        'Placements.php',
        'Notes.php',
        'Appointments.php',
        'Tasks.php',
        'Tearsheets.php',
        'Users.php',
        'Candidates.php',
        'Contacts.php',
        'Companies.php'
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
            $this->addFinding(SEVERITY_CRITICAL, 'General', 'No files found to audit');
            $this->printSummary();
            return EXIT_CRITICAL_HIGH;
        }

        echo "Files to audit: " . count($this->filesToAudit) . "\n";
        echo str_repeat('-', 70) . "\n\n";

        // Run all audit checks on each file
        foreach ($this->filesToAudit as $file => $path) {
            $this->auditFile($file, $path);
        }

        // Print results
        $this->printFindings();
        $this->printSummary();

        // Return appropriate exit code
        if ($this->stats['critical'] > 0 || $this->stats['high'] > 0) {
            return EXIT_CRITICAL_HIGH;
        }
        return EXIT_SUCCESS;
    }

    /**
     * Discover files to audit
     */
    private function discoverFiles()
    {
        // Add library files
        $libPath = $this->basePath . '/lib';
        if (is_dir($libPath)) {
            foreach ($this->libraryPatterns as $pattern) {
                $fullPath = $libPath . '/' . $pattern;
                if (file_exists($fullPath)) {
                    $this->filesToAudit['lib/' . $pattern] = $fullPath;
                }
            }
        }

        // Add handler files
        $handlerDir = $this->basePath . '/modules/api/handlers';
        if (is_dir($handlerDir)) {
            $handlerFiles = glob($handlerDir . '/*.php');
            foreach ($handlerFiles as $file) {
                $this->filesToAudit['modules/api/handlers/' . basename($file)] = $file;
            }
        }

        // Add ApiUI.php
        $apiUI = $this->basePath . '/modules/api/ApiUI.php';
        if (file_exists($apiUI)) {
            $this->filesToAudit['modules/api/ApiUI.php'] = $apiUI;
        }

        // Add traits
        $traitsDir = $this->basePath . '/modules/api/traits';
        if (is_dir($traitsDir)) {
            $traitFiles = glob($traitsDir . '/*.php');
            foreach ($traitFiles as $file) {
                $this->filesToAudit['modules/api/traits/' . basename($file)] = $file;
            }
        }
    }

    /**
     * Audit a single file for all PII handling issues
     *
     * @param string $file Relative file path
     * @param string $path Full file path
     */
    private function auditFile($file, $path)
    {
        if (!file_exists($path)) {
            $this->addFinding(SEVERITY_INFO, $file, 'File not found');
            return;
        }

        $content = file_get_contents($path);
        $lines = explode("\n", $content);

        echo "Auditing: {$file}\n";

        // Check 1: CRITICAL - No plain text password storage
        $this->checkPlaintextPasswordStorage($file, $content, $lines);

        // Check 2: HIGH - No passwords logged
        $this->checkPasswordLogging($file, $content, $lines);

        // Check 3: HIGH - No API keys/secrets in error messages
        $this->checkSecretsInErrors($file, $content, $lines);

        // Check 4: MEDIUM - Webhook payloads sanitize sensitive data
        $this->checkWebhookSanitization($file, $content, $lines);

        // Check 5: MEDIUM - Request logger doesn't log full request body with passwords
        $this->checkRequestLoggerPII($file, $content, $lines);

        // Check 6: Look for positive security patterns
        $this->checkPositivePatterns($file, $content, $lines);
    }

    /**
     * Check 1: CRITICAL - No plain text password storage
     * Looks for INSERT statements with password that don't use password_hash
     *
     * @param string $file
     * @param string $content
     * @param array $lines
     */
    private function checkPlaintextPasswordStorage($file, $content, $lines)
    {
        $this->stats['total_checks']++;

        $issues = [];

        foreach ($lines as $lineNum => $line) {
            // Skip comment lines
            $trimmedLine = trim($line);
            if ($this->isCommentLine($trimmedLine)) {
                continue;
            }

            // Check for INSERT with password that's not hashed
            // Pattern: INSERT...password = %s where the %s value is not password_hash()
            if (preg_match('/INSERT\s+INTO.*password/i', $line)) {
                // Look at surrounding context for password_hash
                $contextStart = max(0, $lineNum - 10);
                $contextEnd = min(count($lines) - 1, $lineNum + 10);
                $context = implode("\n", array_slice($lines, $contextStart, $contextEnd - $contextStart + 1));

                // Check if password_hash is used in the context
                if (!preg_match('/password_hash\s*\(/i', $context)) {
                    // Additional check: is it actually storing a password value?
                    if (preg_match('/password\s*(?:=|,)\s*[\'"]?%s/i', $line) ||
                        preg_match('/password\s*(?:=|,)\s*\$/i', $line)) {
                        $issues[] = [
                            'line' => $lineNum + 1,
                            'code' => $trimmedLine,
                            'detail' => 'INSERT with password field - password_hash() not found in context'
                        ];
                    }
                }
            }

            // Check for UPDATE with password without password_hash
            if (preg_match('/UPDATE\s+.*SET\s+.*password\s*=/i', $line)) {
                $contextStart = max(0, $lineNum - 10);
                $contextEnd = min(count($lines) - 1, $lineNum + 10);
                $context = implode("\n", array_slice($lines, $contextStart, $contextEnd - $contextStart + 1));

                if (!preg_match('/password_hash\s*\(/i', $context)) {
                    if (preg_match('/password\s*=\s*[\'"]?%s/i', $line) ||
                        preg_match('/password\s*=\s*\$/i', $line)) {
                        $issues[] = [
                            'line' => $lineNum + 1,
                            'code' => $trimmedLine,
                            'detail' => 'UPDATE with password field - password_hash() not found in context'
                        ];
                    }
                }
            }

            // Check for direct assignment to database field named password
            if (preg_match('/\$row\[[\'"]password[\'"]\]\s*=\s*\$/', $line) ||
                preg_match('/\$data\[[\'"]password[\'"]\]\s*=\s*\$/', $line)) {
                // Check if password_hash is used
                if (!preg_match('/password_hash\s*\(/i', $line)) {
                    $issues[] = [
                        'line' => $lineNum + 1,
                        'code' => $trimmedLine,
                        'detail' => 'Direct password assignment without password_hash()'
                    ];
                }
            }
        }

        if (empty($issues)) {
            // Only add PASS if file contains password-related code
            if (preg_match('/password/i', $content)) {
                $this->addFinding(SEVERITY_PASS, $file, 'No plaintext password storage detected');
            }
            $this->stats['passed']++;
        } else {
            foreach ($issues as $issue) {
                $this->addFinding(
                    SEVERITY_CRITICAL,
                    $file,
                    "Line {$issue['line']}: {$issue['detail']} - " . substr($issue['code'], 0, 80)
                );
            }
        }
    }

    /**
     * Check 2: HIGH - No passwords logged
     * Looks for error_log, log, or similar functions with password data
     *
     * @param string $file
     * @param string $content
     * @param array $lines
     */
    private function checkPasswordLogging($file, $content, $lines)
    {
        $this->stats['total_checks']++;

        $issues = [];

        // PII patterns to look for in logging
        $sensitivePatterns = [
            'password' => '/\b(password|passwd|pwd)\b/i',
            'secret' => '/\b(secret|client_secret|api_secret)\b/i',
            'token' => '/\b(token|access_token|refresh_token|auth_token)\b/i',
            'api_key' => '/\b(api_?key|apikey)\b/i',
            'ssn' => '/\b(ssn|social_security)\b/i',
            'credit_card' => '/\b(credit_?card|card_?number|cvv)\b/i'
        ];

        foreach ($lines as $lineNum => $line) {
            $trimmedLine = trim($line);
            if ($this->isCommentLine($trimmedLine)) {
                continue;
            }

            // Check for logging functions
            $loggingPatterns = [
                '/error_log\s*\(/i',
                '/\blog\s*\(/i',
                '/->log\s*\(/i',
                '/Logger::/i',
                '/syslog\s*\(/i',
                '/fwrite\s*\([^,]*log/i',
                '/file_put_contents\s*\([^,]*log/i',
                '/debug_log\s*\(/i',
                '/print_r\s*\(/i',
                '/var_dump\s*\(/i',
                '/var_export\s*\(/i'
            ];

            $isLoggingLine = false;
            foreach ($loggingPatterns as $logPattern) {
                if (preg_match($logPattern, $line)) {
                    $isLoggingLine = true;
                    break;
                }
            }

            if ($isLoggingLine) {
                // Check if the line contains sensitive data
                foreach ($sensitivePatterns as $fieldType => $pattern) {
                    if (preg_match($pattern, $line)) {
                        // Exclude if it's just mentioning the field name in an error message string
                        // but not actually logging the value
                        if (preg_match('/[\'"].*' . $fieldType . '.*is\s+(required|missing|invalid)/i', $line)) {
                            continue; // This is likely just an error message about the field
                        }

                        // Check if it's logging a variable that contains the sensitive data
                        if (preg_match('/\$[a-zA-Z_]*' . $fieldType . '/i', $line) ||
                            preg_match('/\$_(?:POST|GET|REQUEST)\s*\[[\'"]' . $fieldType . '/i', $line)) {
                            $issues[] = [
                                'line' => $lineNum + 1,
                                'code' => $trimmedLine,
                                'field' => $this->piiFields[$fieldType] ?? $fieldType
                            ];
                        }
                    }
                }
            }
        }

        if (empty($issues)) {
            $this->addFinding(SEVERITY_PASS, $file, 'No PII logging detected');
            $this->stats['passed']++;
        } else {
            foreach ($issues as $issue) {
                $this->addFinding(
                    SEVERITY_HIGH,
                    $file,
                    "Line {$issue['line']}: Potential {$issue['field']} logging - " . substr($issue['code'], 0, 80)
                );
            }
        }
    }

    /**
     * Check 3: HIGH - No API keys/secrets in error messages
     * Looks for sendError or exception messages containing sensitive data
     *
     * @param string $file
     * @param string $content
     * @param array $lines
     */
    private function checkSecretsInErrors($file, $content, $lines)
    {
        $this->stats['total_checks']++;

        $issues = [];

        foreach ($lines as $lineNum => $line) {
            $trimmedLine = trim($line);
            if ($this->isCommentLine($trimmedLine)) {
                continue;
            }

            // Check for error sending patterns
            $errorPatterns = [
                '/sendError\s*\(/i',
                '/throw\s+new\s+\w*Exception\s*\(/i',
                '/ApiResponse::error\s*\(/i',
                '/json_encode\s*\([^)]*error/i'
            ];

            $isErrorLine = false;
            foreach ($errorPatterns as $errorPattern) {
                if (preg_match($errorPattern, $line)) {
                    $isErrorLine = true;
                    break;
                }
            }

            if ($isErrorLine) {
                // Check if the error message contains sensitive variable interpolation
                $sensitiveVarPatterns = [
                    '/\$[a-zA-Z_]*(?:key|secret|token|password|apikey|api_key)/i',
                    '/\$_(?:POST|GET|REQUEST)\s*\[[\'"](?:key|secret|token|password|api_?key)/i'
                ];

                foreach ($sensitiveVarPatterns as $varPattern) {
                    if (preg_match($varPattern, $line)) {
                        // Extract the variable name
                        preg_match($varPattern, $line, $matches);
                        $varName = $matches[0] ?? 'sensitive variable';

                        $issues[] = [
                            'line' => $lineNum + 1,
                            'code' => $trimmedLine,
                            'var' => $varName
                        ];
                    }
                }
            }
        }

        if (empty($issues)) {
            $this->addFinding(SEVERITY_PASS, $file, 'No secrets in error messages detected');
            $this->stats['passed']++;
        } else {
            foreach ($issues as $issue) {
                $this->addFinding(
                    SEVERITY_HIGH,
                    $file,
                    "Line {$issue['line']}: Error message may expose {$issue['var']} - " . substr($issue['code'], 0, 80)
                );
            }
        }
    }

    /**
     * Check 4: MEDIUM - Webhook payloads sanitize sensitive data
     * Looks for webhook payload building and checks for sanitization
     *
     * @param string $file
     * @param string $content
     * @param array $lines
     */
    private function checkWebhookSanitization($file, $content, $lines)
    {
        // Only check webhook-related files
        if (!preg_match('/webhook/i', $file) && !preg_match('/webhook/i', $content)) {
            return;
        }

        $this->stats['total_checks']++;

        // Check if the file handles webhook payloads
        $hasWebhookPayload = preg_match('/payload|buildPayload|preparePayload|webhookData/i', $content);

        if (!$hasWebhookPayload) {
            $this->addFinding(SEVERITY_INFO, $file, 'No webhook payload handling detected');
            return;
        }

        // Look for positive sanitization patterns
        $hasSanitization = preg_match('/sanitize|redact|mask|filter|removePassword|removeSensitive|excludeFields/i', $content);

        // Look for sensitive fields being included in payloads
        $includesSensitive = false;
        foreach ($lines as $lineNum => $line) {
            $trimmedLine = trim($line);
            if ($this->isCommentLine($trimmedLine)) {
                continue;
            }

            // Check for payload building with sensitive fields
            if (preg_match('/payload.*=.*password|payload.*=.*secret|payload.*=.*token/i', $line) ||
                preg_match('/\$data\[[\'"]password[\'"]\]|->password(?!\s*=)/i', $line)) {

                // Check if it's explicitly excluding the field
                if (!preg_match('/unset|exclude|remove|skip/i', $line)) {
                    $includesSensitive = true;
                    $this->addFinding(
                        SEVERITY_MEDIUM,
                        $file,
                        "Line " . ($lineNum + 1) . ": Webhook payload may include sensitive field - " . substr($trimmedLine, 0, 80)
                    );
                }
            }
        }

        if ($hasSanitization) {
            $this->addFinding(SEVERITY_PASS, $file, 'Webhook payload sanitization patterns found');
            $this->stats['passed']++;
        } elseif (!$includesSensitive) {
            $this->addFinding(SEVERITY_PASS, $file, 'No sensitive fields detected in webhook payloads');
            $this->stats['passed']++;
        }
    }

    /**
     * Check 5: MEDIUM - Request logger doesn't log full request body with passwords
     *
     * @param string $file
     * @param string $content
     * @param array $lines
     */
    private function checkRequestLoggerPII($file, $content, $lines)
    {
        // Only check files that handle request logging
        if (!preg_match('/logger|logging|ApiRequestLogger/i', $file) &&
            !preg_match('/logRequest|log.*request|_log\s*\(/i', $content)) {
            return;
        }

        $this->stats['total_checks']++;

        $issues = [];

        // Check for logging of full request body without sanitization
        foreach ($lines as $lineNum => $line) {
            $trimmedLine = trim($line);
            if ($this->isCommentLine($trimmedLine)) {
                continue;
            }

            // Check for logging $_POST, $_REQUEST, or request body directly
            $bodyLoggingPatterns = [
                '/file_put_contents.*\$_POST/i',
                '/file_put_contents.*\$_REQUEST/i',
                '/error_log.*json_encode\s*\(\s*\$_POST/i',
                '/error_log.*json_encode\s*\(\s*\$_REQUEST/i',
                '/log.*json_encode\s*\(\s*\$_POST/i',
                '/log.*getRequestBody/i',
                '/log.*file_get_contents\s*\(\s*[\'"]php:\/\/input/i'
            ];

            foreach ($bodyLoggingPatterns as $pattern) {
                if (preg_match($pattern, $line)) {
                    // Check if there's sanitization
                    $contextStart = max(0, $lineNum - 5);
                    $contextEnd = min(count($lines) - 1, $lineNum + 5);
                    $context = implode("\n", array_slice($lines, $contextStart, $contextEnd - $contextStart + 1));

                    if (!preg_match('/sanitize|redact|mask|filter|removePassword|excludeFields/i', $context)) {
                        $issues[] = [
                            'line' => $lineNum + 1,
                            'code' => $trimmedLine
                        ];
                    }
                    break;
                }
            }
        }

        // Check for positive patterns (sanitization before logging)
        $hasSanitization = preg_match('/sanitize.*log|redact.*log|mask.*log|filter.*password.*log/i', $content);
        $hasExclusion = preg_match('/excludeFields|EXCLUDE_FIELDS|sensitiveFields/i', $content);

        if (!empty($issues)) {
            foreach ($issues as $issue) {
                $this->addFinding(
                    SEVERITY_MEDIUM,
                    $file,
                    "Line {$issue['line']}: Request body logged without apparent sanitization - " . substr($issue['code'], 0, 80)
                );
            }
        } elseif ($hasSanitization || $hasExclusion) {
            $this->addFinding(SEVERITY_PASS, $file, 'Request logging includes sanitization/exclusion patterns');
            $this->stats['passed']++;
        } else {
            // No logging of full bodies found - that's also acceptable
            $this->addFinding(SEVERITY_PASS, $file, 'No full request body logging detected');
            $this->stats['passed']++;
        }
    }

    /**
     * Check 6: Look for positive security patterns
     * Finds and reports good PII handling practices
     *
     * @param string $file
     * @param string $content
     * @param array $lines
     */
    private function checkPositivePatterns($file, $content, $lines)
    {
        $positivePatterns = [];

        // password_hash usage
        if (preg_match('/password_hash\s*\(/i', $content)) {
            $positivePatterns[] = 'Uses password_hash() for secure password storage';
        }

        // password_verify usage
        if (preg_match('/password_verify\s*\(/i', $content)) {
            $positivePatterns[] = 'Uses password_verify() for secure password comparison';
        }

        // Token redaction
        if (preg_match('/redact|mask.*token|token.*mask/i', $content)) {
            $positivePatterns[] = 'Implements token redaction/masking';
        }

        // Sensitive field exclusion
        if (preg_match('/excludeFields|sensitiveFields|SENSITIVE_KEYS/i', $content)) {
            $positivePatterns[] = 'Defines sensitive field exclusion lists';
        }

        // Data sanitization
        if (preg_match('/sanitize[A-Z]|sanitizeData|sanitizePayload/i', $content)) {
            $positivePatterns[] = 'Implements data sanitization methods';
        }

        // Hash comparison
        if (preg_match('/hash_equals\s*\(/i', $content)) {
            $positivePatterns[] = 'Uses hash_equals() for timing-safe comparison';
        }

        foreach ($positivePatterns as $pattern) {
            $this->addFinding(SEVERITY_PASS, $file, $pattern);
            $this->stats['passed']++;
        }
    }

    /**
     * Check if a line is a comment
     *
     * @param string $trimmedLine
     * @return bool
     */
    private function isCommentLine($trimmedLine)
    {
        // Single-line comments
        if (strpos($trimmedLine, '//') === 0) {
            return true;
        }
        // Block comment indicators
        if (strpos($trimmedLine, '*') === 0 || strpos($trimmedLine, '/*') === 0) {
            return true;
        }
        // Doc blocks
        if (strpos($trimmedLine, '/**') === 0) {
            return true;
        }
        return false;
    }

    /**
     * Add a finding to the collection
     *
     * @param string $severity
     * @param string $file
     * @param string $description
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
        echo "  OpenCATS REST API - PII Handling Audit\n";
        echo "==========================================================================\n";
        echo "  Base Path: {$this->basePath}\n";
        echo "  Date: " . date('Y-m-d H:i:s') . "\n";
        echo "==========================================================================\n\n";

        echo "PII Fields Monitored:\n";
        echo "  - Passwords, Secrets, Tokens, API Keys\n";
        echo "  - SSN, Social Security Numbers\n";
        echo "  - Credit Card Numbers, CVV\n\n";
    }

    /**
     * Print all findings grouped by file
     */
    private function printFindings()
    {
        echo "\n";
        echo "==========================================================================\n";
        echo "  FINDINGS BY FILE\n";
        echo "==========================================================================\n\n";

        // Group findings by file
        $byFile = [];
        foreach ($this->findings as $finding) {
            if (!isset($byFile[$finding['file']])) {
                $byFile[$finding['file']] = [];
            }
            $byFile[$finding['file']][] = $finding;
        }

        foreach ($byFile as $file => $findings) {
            echo "File: {$file}\n";
            echo str_repeat('-', 70) . "\n";

            // Sort by severity
            usort($findings, function ($a, $b) {
                $order = [
                    SEVERITY_CRITICAL => 0,
                    SEVERITY_HIGH => 1,
                    SEVERITY_MEDIUM => 2,
                    SEVERITY_LOW => 3,
                    SEVERITY_INFO => 4,
                    SEVERITY_PASS => 5
                ];
                return ($order[$a['severity']] ?? 6) - ($order[$b['severity']] ?? 6);
            });

            foreach ($findings as $finding) {
                $severityColor = $this->getSeverityColor($finding['severity']);
                echo "  [{$finding['severity']}] {$finding['description']}\n";
            }
            echo "\n";
        }

        // Print issues summary by severity
        echo "\n";
        echo "==========================================================================\n";
        echo "  ISSUES BY SEVERITY\n";
        echo "==========================================================================\n\n";

        $severities = [SEVERITY_CRITICAL, SEVERITY_HIGH, SEVERITY_MEDIUM, SEVERITY_LOW];
        foreach ($severities as $severity) {
            $severityFindings = array_filter($this->findings, function ($f) use ($severity) {
                return $f['severity'] === $severity;
            });

            if (!empty($severityFindings)) {
                echo "[{$severity}] - " . count($severityFindings) . " issue(s)\n";
                foreach ($severityFindings as $finding) {
                    echo "  - {$finding['file']}: {$finding['description']}\n";
                }
                echo "\n";
            }
        }
    }

    /**
     * Get color code for severity (for terminal output)
     *
     * @param string $severity
     * @return string
     */
    private function getSeverityColor($severity)
    {
        switch ($severity) {
            case SEVERITY_CRITICAL:
                return "\033[31m"; // Red
            case SEVERITY_HIGH:
                return "\033[91m"; // Light Red
            case SEVERITY_MEDIUM:
                return "\033[33m"; // Yellow
            case SEVERITY_LOW:
                return "\033[36m"; // Cyan
            case SEVERITY_PASS:
                return "\033[32m"; // Green
            default:
                return "\033[0m"; // Reset
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
        echo "  --------------------\n";
        echo "  CRITICAL:         {$this->stats['critical']}\n";
        echo "  HIGH:             {$this->stats['high']}\n";
        echo "  MEDIUM:           {$this->stats['medium']}\n";
        echo "  LOW:              {$this->stats['low']}\n";
        echo "  INFO:             {$this->stats['info']}\n";
        echo "  PASSED:           {$this->stats['passed']}\n";
        echo "==========================================================================\n";

        if ($this->stats['critical'] > 0) {
            echo "\n  *** CRITICAL ISSUES FOUND - IMMEDIATE ACTION REQUIRED ***\n";
            echo "  Plain text password storage detected. This is a severe security risk.\n";
        } elseif ($this->stats['high'] > 0) {
            echo "\n  ** HIGH SEVERITY ISSUES FOUND - ACTION RECOMMENDED **\n";
            echo "  PII may be exposed in logs or error messages.\n";
        } elseif ($this->stats['medium'] > 0) {
            echo "\n  * MEDIUM SEVERITY ISSUES FOUND - REVIEW RECOMMENDED *\n";
            echo "  Webhook payloads or request logging may need sanitization.\n";
        } else {
            echo "\n  PII handling audit passed. No critical or high issues found.\n";
        }
        echo "\n";
    }

    /**
     * Get exit code
     *
     * @return int
     */
    public function getExitCode()
    {
        if ($this->stats['critical'] > 0 || $this->stats['high'] > 0) {
            return EXIT_CRITICAL_HIGH;
        }
        return EXIT_SUCCESS;
    }

    /**
     * Get statistics
     *
     * @return array
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * Get findings
     *
     * @return array
     */
    public function getFindings()
    {
        return $this->findings;
    }
}

// =============================================================================
// MAIN EXECUTION
// =============================================================================

// Determine base path
$basePath = dirname(dirname(dirname(__FILE__))); // Go up from test/compliance/ to root

// Allow override via command line argument
if (isset($argv[1])) {
    $basePath = $argv[1];
}

// Verify base path
if (!file_exists($basePath . '/lib/DatabaseConnection.php')) {
    echo "Error: Could not find OpenCATS installation at: {$basePath}\n";
    echo "Usage: php pii_audit.php [/path/to/opencats]\n";
    exit(1);
}

// Run the audit
$audit = new PIIHandlingAudit($basePath);
$exitCode = $audit->run();

exit($exitCode);
