#!/usr/bin/env php
<?php
/**
 * Input Validation & XSS Security Audit Script
 *
 * Audits all OpenCATS REST API handlers for:
 * - Direct $_GET/$_POST usage without validation
 * - Direct echo of variables without json_encode (XSS risk)
 * - Sensitive data in error messages
 * - File uploads without MIME type validation
 *
 * @package    CATS
 * @subpackage Security
 * @author     OpenCATS Security Team
 */

// Define base path
define('AUDIT_BASE_PATH', realpath(dirname(__FILE__) . '/../..'));

/**
 * Input Validation Audit Class
 */
class InputValidationAudit
{
    /**
     * Files to audit
     * @var array
     */
    private $filesToAudit = [];

    /**
     * Audit results per file
     * @var array
     */
    private $results = [];

    /**
     * Total issues found
     * @var int
     */
    private $totalIssues = 0;

    /**
     * Total positive indicators found
     * @var int
     */
    private $totalPositiveIndicators = 0;

    /**
     * Sensitive keywords to check in error messages
     * @var array
     */
    private $sensitiveKeywords = [
        'password',
        'secret',
        'token',
        'api_key',
        'apikey',
        'private_key',
        'privatekey',
        'credential',
        'auth_token',
        'access_token',
        'refresh_token',
        'ssn',
        'social_security'
    ];

    /**
     * Validation function patterns (positive indicators)
     * @var array
     */
    private $validationPatterns = [
        'intval' => '/\bintval\s*\(/i',
        'trim' => '/\btrim\s*\(/i',
        'filter_var' => '/\bfilter_var\s*\(/i',
        'filter_input' => '/\bfilter_input\s*\(/i',
        'htmlspecialchars' => '/\bhtmlspecialchars\s*\(/i',
        'htmlentities' => '/\bhtmlentities\s*\(/i',
        'addslashes' => '/\baddslashes\s*\(/i',
        'strip_tags' => '/\bstrip_tags\s*\(/i',
        'preg_match' => '/\bpreg_match\s*\(/i',
        'is_numeric' => '/\bis_numeric\s*\(/i',
        'is_int' => '/\bis_int\s*\(/i',
        'is_array' => '/\bis_array\s*\(/i',
        'ctype_digit' => '/\bctype_digit\s*\(/i',
        'ctype_alnum' => '/\bctype_alnum\s*\(/i',
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->discoverFiles();
    }

    /**
     * Discover files to audit
     */
    private function discoverFiles()
    {
        $handlersPath = AUDIT_BASE_PATH . '/modules/api/handlers';
        $traitsPath = AUDIT_BASE_PATH . '/modules/api/traits';

        // Scan handlers directory
        if (is_dir($handlersPath)) {
            $files = glob($handlersPath . '/*.php');
            foreach ($files as $file) {
                $this->filesToAudit[] = $file;
            }
        }

        // Add ApiHelpers.php from traits
        $apiHelpers = $traitsPath . '/ApiHelpers.php';
        if (file_exists($apiHelpers)) {
            $this->filesToAudit[] = $apiHelpers;
        }

        // Add WebhookTrigger.php from traits
        $webhookTrigger = $traitsPath . '/WebhookTrigger.php';
        if (file_exists($webhookTrigger)) {
            $this->filesToAudit[] = $webhookTrigger;
        }
    }

    /**
     * Run the full audit
     * @return bool True if no issues found
     */
    public function runAudit()
    {
        $this->printHeader();

        if (empty($this->filesToAudit)) {
            echo "\n[WARNING] No files found to audit.\n";
            echo "Expected files in:\n";
            echo "  - " . AUDIT_BASE_PATH . "/modules/api/handlers/*.php\n";
            echo "  - " . AUDIT_BASE_PATH . "/modules/api/traits/ApiHelpers.php\n\n";
            return false;
        }

        echo "\nFiles to audit: " . count($this->filesToAudit) . "\n";
        echo str_repeat('=', 70) . "\n\n";

        foreach ($this->filesToAudit as $file) {
            $this->auditFile($file);
        }

        $this->printSummary();

        return $this->totalIssues === 0;
    }

    /**
     * Audit a single file
     * @param string $file Path to file
     */
    private function auditFile($file)
    {
        $filename = basename($file);
        $content = file_get_contents($file);
        $lines = explode("\n", $content);

        $this->results[$filename] = [
            'path' => $file,
            'issues' => [],
            'positiveIndicators' => [],
            'status' => 'PASS'
        ];

        // Check 1: Direct $_GET/$_POST usage without validation
        $this->checkDirectSuperglobalUsage($filename, $content, $lines);

        // Check 2: Direct echo without json_encode (XSS risk)
        $this->checkDirectEcho($filename, $content, $lines);

        // Check 3: Sensitive data in error messages
        $this->checkSensitiveErrorMessages($filename, $content, $lines);

        // Check 4: File uploads without MIME validation
        $this->checkFileUploadValidation($filename, $content, $lines);

        // Count positive indicators
        $this->countPositiveIndicators($filename, $content);

        // Determine overall status
        if (!empty($this->results[$filename]['issues'])) {
            $this->results[$filename]['status'] = 'REVIEW';
            $this->totalIssues += count($this->results[$filename]['issues']);
        }

        // Print file result
        $this->printFileResult($filename);
    }

    /**
     * Check for direct $_GET/$_POST usage without validation
     * @param string $filename
     * @param string $content
     * @param array $lines
     */
    private function checkDirectSuperglobalUsage($filename, $content, $lines)
    {
        // Pattern for $_GET/$_POST usage that's NOT wrapped in validation
        // We consider it validated if on the same line or nearby we see intval, trim, filter_var, etc.

        $superglobalPatterns = [
            '/\$_GET\s*\[/' => '$_GET',
            '/\$_POST\s*\[/' => '$_POST',
            '/\$_REQUEST\s*\[/' => '$_REQUEST',
        ];

        foreach ($lines as $lineNum => $line) {
            // Skip comment lines
            $trimmedLine = trim($line);
            if ($this->isCommentLine($trimmedLine)) {
                continue;
            }

            foreach ($superglobalPatterns as $pattern => $type) {
                if (preg_match($pattern, $line)) {
                    // Check if this line has validation
                    $hasValidation = $this->lineHasValidation($line);

                    // Also check if it's in an isset() check which is acceptable for presence checks
                    $isIssetCheck = preg_match('/isset\s*\([^)]*' . preg_quote($type, '/') . '/', $line);

                    // Check if empty() is used - this is also a form of validation
                    $isEmptyCheck = preg_match('/empty\s*\([^)]*' . preg_quote($type, '/') . '/', $line);

                    // Check if it's properly validated
                    if (!$hasValidation && !$isIssetCheck && !$isEmptyCheck) {
                        // Additional check: See if the value is immediately validated on the same assignment
                        $isValidatedAssignment = $this->isValidatedAssignment($line);

                        // Check if next line has validation (common pattern: explode then array_map)
                        $nextLineValidation = false;
                        if (isset($lines[$lineNum + 1])) {
                            $nextLineValidation = $this->lineHasValidation($lines[$lineNum + 1]);
                        }

                        // Check if the variable assigned here is later validated in a foreach/loop
                        $isValidatedInLoop = $this->isValidatedInSubsequentLoop($lines, $lineNum, $line);

                        if (!$isValidatedAssignment && !$nextLineValidation && !$isValidatedInLoop) {
                            $this->results[$filename]['issues'][] = [
                                'type' => 'UNVALIDATED_INPUT',
                                'line' => $lineNum + 1,
                                'message' => "Direct {$type} usage without explicit validation",
                                'code' => trim($line)
                            ];
                        }
                    }
                }
            }
        }
    }

    /**
     * Check if a variable is validated in a subsequent foreach/for loop
     * Common pattern: $arr = explode(',', $_GET['x']); foreach($arr as $item) { $item = intval($item); }
     * @param array $lines
     * @param int $currentLineNum
     * @param string $currentLine
     * @return bool
     */
    private function isValidatedInSubsequentLoop($lines, $currentLineNum, $currentLine)
    {
        // Extract variable name if it's an assignment
        if (!preg_match('/\$([a-zA-Z_][a-zA-Z0-9_]*)\s*=/', $currentLine, $varMatch)) {
            return false;
        }
        $varName = $varMatch[1];

        // Look at next 15 lines for a foreach/for loop that uses this variable and validates
        $maxLookAhead = min($currentLineNum + 15, count($lines) - 1);
        for ($i = $currentLineNum + 1; $i <= $maxLookAhead; $i++) {
            $checkLine = $lines[$i];

            // Check for foreach loop using this variable
            if (preg_match('/foreach\s*\(\s*\$' . preg_quote($varName, '/') . '\s+as/', $checkLine)) {
                // Check next few lines inside the loop for validation
                for ($j = $i + 1; $j <= min($i + 5, count($lines) - 1); $j++) {
                    if ($this->lineHasValidation($lines[$j])) {
                        return true;
                    }
                    // Stop if we hit another foreach or closing brace at same level
                    if (preg_match('/^\s*(foreach|for|while|\})/', $lines[$j])) {
                        break;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if a line is a comment
     * @param string $trimmedLine
     * @return bool
     */
    private function isCommentLine($trimmedLine)
    {
        // Check for single-line comments
        if (strpos($trimmedLine, '//') === 0) {
            return true;
        }
        // Check for block comment indicators
        if (strpos($trimmedLine, '*') === 0 || strpos($trimmedLine, '/*') === 0) {
            return true;
        }
        // Check for doc blocks
        if (strpos($trimmedLine, '/**') === 0) {
            return true;
        }
        return false;
    }

    /**
     * Check if a line has validation functions
     * @param string $line
     * @return bool
     */
    private function lineHasValidation($line)
    {
        foreach ($this->validationPatterns as $name => $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        // Check for array_map with validation functions
        if (preg_match('/array_map\s*\(\s*[\'"](intval|trim|htmlspecialchars|strip_tags)[\'"]/', $line)) {
            return true;
        }

        // Check for explode followed by array_map on same or adjacent logic
        if (preg_match('/explode\s*\(/', $line) && preg_match('/array_map/', $line)) {
            return true;
        }

        return false;
    }

    /**
     * Check if a line is a validated assignment
     * Patterns like: $var = intval($_GET['id'])
     * @param string $line
     * @return bool
     */
    private function isValidatedAssignment($line)
    {
        // Check for ternary with validation: isset($_GET['x']) ? intval($_GET['x']) : default
        if (preg_match('/isset\s*\(\s*\$_(GET|POST|REQUEST)\s*\[/', $line)) {
            if ($this->lineHasValidation($line)) {
                return true;
            }
            // Also consider simple null coalescing or ternary with default value as partial validation
            if (preg_match('/\?\s*\$_(GET|POST|REQUEST)\s*\[.*?\]\s*:\s*/', $line)) {
                // Ternary expression - check if the value is later validated
                return true; // We'll flag real issues elsewhere
            }
        }

        // Check for direct assignment with validation
        if (preg_match('/=\s*(intval|trim|filter_var|filter_input|htmlspecialchars)\s*\(/', $line)) {
            return true;
        }

        return false;
    }

    /**
     * Check for direct echo without json_encode (XSS risk in API context)
     * @param string $filename
     * @param string $content
     * @param array $lines
     */
    private function checkDirectEcho($filename, $content, $lines)
    {
        foreach ($lines as $lineNum => $line) {
            // Check for echo statements
            if (preg_match('/\becho\s+/', $line)) {
                // Skip if it's json_encode
                if (preg_match('/echo\s+json_encode\s*\(/', $line)) {
                    continue;
                }

                // Skip if it's fread (file streaming)
                if (preg_match('/echo\s+fread\s*\(/', $line)) {
                    continue;
                }

                // Skip if it's a string literal only
                if (preg_match('/echo\s+[\'"][^\'"]*[\'"]/', $line) && !preg_match('/\$/', $line)) {
                    continue;
                }

                // Check for echo with variables
                if (preg_match('/echo\s+.*\$/', $line)) {
                    // This could be XSS if outputting user data
                    $this->results[$filename]['issues'][] = [
                        'type' => 'POTENTIAL_XSS',
                        'line' => $lineNum + 1,
                        'message' => 'Direct echo with variable - verify output encoding',
                        'code' => trim($line)
                    ];
                }
            }

            // Check for print statements with variables
            if (preg_match('/\bprint\s+.*\$/', $line) && !preg_match('/print_r/', $line)) {
                $this->results[$filename]['issues'][] = [
                    'type' => 'POTENTIAL_XSS',
                    'line' => $lineNum + 1,
                    'message' => 'Direct print with variable - verify output encoding',
                    'code' => trim($line)
                ];
            }
        }
    }

    /**
     * Check for sensitive data in error messages
     * @param string $filename
     * @param string $content
     * @param array $lines
     */
    private function checkSensitiveErrorMessages($filename, $content, $lines)
    {
        foreach ($lines as $lineNum => $line) {
            // Check for error handling patterns
            if (preg_match('/(sendError|throw\s+new|Exception|error_log|trigger_error)\s*\(/', $line)) {
                $lineLower = strtolower($line);
                foreach ($this->sensitiveKeywords as $keyword) {
                    if (strpos($lineLower, $keyword) !== false) {
                        // Check if it's actually in a string or variable name
                        if (preg_match('/[\'"].*' . preg_quote($keyword, '/') . '.*[\'"]/', $lineLower)) {
                            $this->results[$filename]['issues'][] = [
                                'type' => 'SENSITIVE_ERROR_DATA',
                                'line' => $lineNum + 1,
                                'message' => "Error message may contain sensitive keyword: {$keyword}",
                                'code' => trim($line)
                            ];
                        }
                    }
                }
            }
        }
    }

    /**
     * Check for file uploads without MIME type validation
     * @param string $filename
     * @param string $content
     * @param array $lines
     */
    private function checkFileUploadValidation($filename, $content, $lines)
    {
        // Check if file handles uploads ($_FILES usage)
        if (strpos($content, '$_FILES') === false) {
            return; // No file uploads in this file
        }

        // Check for MIME validation functions
        $hasMimeValidation = false;
        $hasFinfo = preg_match('/finfo_file|finfo_open|mime_content_type/', $content);
        $hasGetimagesize = preg_match('/getimagesize/', $content);
        $hasExifType = preg_match('/exif_imagetype/', $content);
        $hasMimeTypeCheck = preg_match('/\$_FILES\s*\[.*?\]\s*\[\s*[\'"]type[\'"]\s*\]/', $content);
        $hasContentTypeValidation = preg_match('/isAllowedMimeType|validateMimeType|checkMimeType/', $content);
        $hasAllowedTypes = preg_match('/allowedMimeTypes|allowed_types|mimeTypes/', $content);

        if ($hasFinfo || $hasGetimagesize || $hasExifType || $hasContentTypeValidation || $hasAllowedTypes) {
            $hasMimeValidation = true;
        }

        // Find $_FILES usage lines
        foreach ($lines as $lineNum => $line) {
            if (preg_match('/\$_FILES\s*\[/', $line)) {
                // If no MIME validation detected in file, flag this
                if (!$hasMimeValidation) {
                    // Only flag if this is an actual upload handling, not just checking existence
                    if (!preg_match('/(isset|empty)\s*\(\s*\$_FILES/', $line)) {
                        $this->results[$filename]['issues'][] = [
                            'type' => 'FILE_UPLOAD_NO_MIME_CHECK',
                            'line' => $lineNum + 1,
                            'message' => 'File upload detected without apparent MIME type validation',
                            'code' => trim($line)
                        ];
                        break; // Only report once per file
                    }
                }
            }
        }
    }

    /**
     * Count positive validation indicators in file
     * @param string $filename
     * @param string $content
     */
    private function countPositiveIndicators($filename, $content)
    {
        $indicators = [];

        foreach ($this->validationPatterns as $name => $pattern) {
            preg_match_all($pattern, $content, $matches);
            $count = count($matches[0]);
            if ($count > 0) {
                $indicators[$name] = $count;
                $this->totalPositiveIndicators += $count;
            }
        }

        // Also count json_encode usage (safe output)
        preg_match_all('/json_encode\s*\(/i', $content, $jsonMatches);
        if (count($jsonMatches[0]) > 0) {
            $indicators['json_encode'] = count($jsonMatches[0]);
            $this->totalPositiveIndicators += count($jsonMatches[0]);
        }

        $this->results[$filename]['positiveIndicators'] = $indicators;
    }

    /**
     * Print audit header
     */
    private function printHeader()
    {
        echo "\n";
        echo str_repeat('=', 70) . "\n";
        echo "  OpenCATS REST API - Input Validation & XSS Security Audit\n";
        echo str_repeat('=', 70) . "\n";
        echo "\nAudit Date: " . date('Y-m-d H:i:s') . "\n";
        echo "Base Path: " . AUDIT_BASE_PATH . "\n";
    }

    /**
     * Print result for a single file
     * @param string $filename
     */
    private function printFileResult($filename)
    {
        $result = $this->results[$filename];
        $status = $result['status'];
        $statusColor = $status === 'PASS' ? "\033[32m" : "\033[33m"; // Green or Yellow
        $reset = "\033[0m";

        echo "\n" . str_repeat('-', 70) . "\n";
        echo "File: {$filename}\n";
        echo "Status: {$statusColor}[{$status}]{$reset}\n";

        // Print positive indicators
        if (!empty($result['positiveIndicators'])) {
            echo "\nPositive Indicators:\n";
            foreach ($result['positiveIndicators'] as $indicator => $count) {
                echo "  + {$indicator}(): {$count} usage(s)\n";
            }
        }

        // Print issues
        if (!empty($result['issues'])) {
            echo "\nIssues Found: " . count($result['issues']) . "\n";
            foreach ($result['issues'] as $issue) {
                echo "\n  [{$issue['type']}] Line {$issue['line']}\n";
                echo "  Message: {$issue['message']}\n";
                echo "  Code: " . substr($issue['code'], 0, 80) . (strlen($issue['code']) > 80 ? '...' : '') . "\n";
            }
        }
    }

    /**
     * Print final summary
     */
    private function printSummary()
    {
        echo "\n" . str_repeat('=', 70) . "\n";
        echo "  AUDIT SUMMARY\n";
        echo str_repeat('=', 70) . "\n\n";

        $passCount = 0;
        $reviewCount = 0;

        foreach ($this->results as $filename => $result) {
            if ($result['status'] === 'PASS') {
                $passCount++;
            } else {
                $reviewCount++;
            }
        }

        echo "Files Audited: " . count($this->results) . "\n";
        echo "  [PASS]: {$passCount}\n";
        echo "  [REVIEW]: {$reviewCount}\n\n";

        echo "Total Issues Found: {$this->totalIssues}\n";
        echo "Total Positive Indicators: {$this->totalPositiveIndicators}\n\n";

        // Issue breakdown by type
        if ($this->totalIssues > 0) {
            $issueTypes = [];
            foreach ($this->results as $filename => $result) {
                foreach ($result['issues'] as $issue) {
                    if (!isset($issueTypes[$issue['type']])) {
                        $issueTypes[$issue['type']] = 0;
                    }
                    $issueTypes[$issue['type']]++;
                }
            }

            echo "Issue Breakdown:\n";
            foreach ($issueTypes as $type => $count) {
                echo "  - {$type}: {$count}\n";
            }
            echo "\n";
        }

        // Overall assessment
        if ($this->totalIssues === 0) {
            echo "\033[32m" . str_repeat('*', 50) . "\033[0m\n";
            echo "\033[32m*  ALL FILES PASSED INPUT VALIDATION AUDIT     *\033[0m\n";
            echo "\033[32m" . str_repeat('*', 50) . "\033[0m\n";
        } else {
            echo "\033[33m" . str_repeat('*', 50) . "\033[0m\n";
            echo "\033[33m*  {$reviewCount} FILE(S) REQUIRE REVIEW                    *\033[0m\n";
            echo "\033[33m" . str_repeat('*', 50) . "\033[0m\n";
        }

        echo "\n";
    }

    /**
     * Get exit code
     * @return int 0 if no issues, 1 if issues found
     */
    public function getExitCode()
    {
        return $this->totalIssues > 0 ? 1 : 0;
    }

    /**
     * Get results array
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Get total issues count
     * @return int
     */
    public function getTotalIssues()
    {
        return $this->totalIssues;
    }
}

// Run audit if executed directly
if (php_sapi_name() === 'cli' && realpath($argv[0]) === realpath(__FILE__)) {
    $audit = new InputValidationAudit();
    $audit->runAudit();
    exit($audit->getExitCode());
}
