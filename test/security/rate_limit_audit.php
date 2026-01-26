#!/usr/bin/env php
<?php
/**
 * CATS
 * Rate Limiting Security Audit Script
 *
 * Verifies that the API rate limiting implementation is secure and complete.
 *
 * Checks:
 * 1. Rate limiting uses server-side storage, not $_SESSION or $_COOKIE (CRITICAL)
 * 2. Per-minute rate limiting exists (HIGH)
 * 3. Per-hour rate limiting exists (HIGH)
 * 4. Rate limiting is applied after authentication in ApiUI (HIGH)
 * 5. 429 status code returned for rate limit exceeded (MEDIUM)
 * 6. Retry-After header is set (LOW)
 *
 * Exit codes:
 *   0 - All checks passed
 *   1 - Critical or high severity issues found
 *
 * @package    CATS
 * @subpackage Tests
 * @copyright Copyright (C) 2026 Space-O Technologies
 */

// Color codes for terminal output
define('COLOR_RED', "\033[31m");
define('COLOR_GREEN', "\033[32m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_CYAN', "\033[36m");
define('COLOR_RESET', "\033[0m");

// Severity levels
define('SEVERITY_CRITICAL', 'CRITICAL');
define('SEVERITY_HIGH', 'HIGH');
define('SEVERITY_MEDIUM', 'MEDIUM');
define('SEVERITY_LOW', 'LOW');
define('SEVERITY_PASS', 'PASS');

/**
 * RateLimitAudit class
 * Performs security audit on rate limiting implementation
 */
class RateLimitAudit
{
    private $issues = [];
    private $basePath;
    private $rateLimiterFile;
    private $apiUIFile;
    private $rateLimiterContent;
    private $apiUIContent;

    public function __construct($basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        $this->rateLimiterFile = $this->basePath . '/lib/ApiRateLimiter.php';
        $this->apiUIFile = $this->basePath . '/modules/api/ApiUI.php';
    }

    /**
     * Run all audit checks
     *
     * @return int Exit code (0 = pass, 1 = fail)
     */
    public function run()
    {
        $this->printHeader();

        // Load files
        if (!$this->loadFiles()) {
            return 1;
        }

        // Run all checks
        $this->checkServerSideStorage();
        $this->checkPerMinuteLimit();
        $this->checkPerHourLimit();
        $this->checkRateLimitingAfterAuth();
        $this->check429StatusCode();
        $this->checkRetryAfterHeader();

        // Print results
        $this->printResults();

        // Determine exit code
        return $this->hasHighSeverityIssues() ? 1 : 0;
    }

    /**
     * Print header
     */
    private function printHeader()
    {
        echo COLOR_CYAN . "=================================================\n";
        echo "     OpenCATS Rate Limiting Security Audit\n";
        echo "=================================================\n" . COLOR_RESET;
        echo "Date: " . date('Y-m-d H:i:s') . "\n\n";
    }

    /**
     * Load the files to audit
     *
     * @return bool True if files loaded successfully
     */
    private function loadFiles()
    {
        echo "Loading files for audit...\n";

        if (!file_exists($this->rateLimiterFile)) {
            $this->addIssue(SEVERITY_CRITICAL, 'lib/ApiRateLimiter.php', 'File does not exist - no rate limiting implementation found');
            return false;
        }

        if (!file_exists($this->apiUIFile)) {
            $this->addIssue(SEVERITY_CRITICAL, 'modules/api/ApiUI.php', 'File does not exist - API handler not found');
            return false;
        }

        $this->rateLimiterContent = file_get_contents($this->rateLimiterFile);
        $this->apiUIContent = file_get_contents($this->apiUIFile);

        echo "  - lib/ApiRateLimiter.php: " . COLOR_GREEN . "Loaded" . COLOR_RESET . "\n";
        echo "  - modules/api/ApiUI.php: " . COLOR_GREEN . "Loaded" . COLOR_RESET . "\n\n";

        return true;
    }

    /**
     * Check #1: Rate limiting uses server-side storage (not $_SESSION or $_COOKIE)
     * Severity: CRITICAL if bypassed
     */
    private function checkServerSideStorage()
    {
        echo "Checking server-side storage...\n";

        $usesSession = preg_match('/\$_SESSION\s*\[/', $this->rateLimiterContent);
        $usesCookie = preg_match('/\$_COOKIE\s*\[/', $this->rateLimiterContent);
        $usesDatabase = preg_match('/DatabaseConnection|->getAssoc|->query|api_request_log/i', $this->rateLimiterContent);

        if ($usesSession) {
            $this->addIssue(
                SEVERITY_CRITICAL,
                'lib/ApiRateLimiter.php',
                'Uses $_SESSION for rate limit storage - can be bypassed by not sending session cookie'
            );
        }

        if ($usesCookie) {
            $this->addIssue(
                SEVERITY_CRITICAL,
                'lib/ApiRateLimiter.php',
                'Uses $_COOKIE for rate limit storage - can be bypassed by not sending cookies'
            );
        }

        if (!$usesDatabase && !$usesSession && !$usesCookie) {
            $this->addIssue(
                SEVERITY_CRITICAL,
                'lib/ApiRateLimiter.php',
                'No persistent storage mechanism detected for rate limiting'
            );
        }

        if ($usesDatabase && !$usesSession && !$usesCookie) {
            $this->addPass('Server-side storage: Uses database (api_request_log table)');
        }
    }

    /**
     * Check #2: Per-minute rate limiting exists
     * Severity: HIGH if missing
     */
    private function checkPerMinuteLimit()
    {
        echo "Checking per-minute rate limiting...\n";

        // Check for minute-based limit constant or variable
        $hasMinuteConstant = preg_match('/REQUESTS_PER_MINUTE|requestsPerMinute|minute_count/i', $this->rateLimiterContent);
        $hasMinuteLogic = preg_match('/time\s*\(\s*\)\s*-\s*60|strtotime.*minute|60\s*seconds/i', $this->rateLimiterContent);

        if (!$hasMinuteConstant && !$hasMinuteLogic) {
            $this->addIssue(
                SEVERITY_HIGH,
                'lib/ApiRateLimiter.php',
                'No per-minute rate limiting detected - API vulnerable to burst attacks'
            );
        } else {
            // Verify the logic is actually implemented
            if (preg_match('/minuteCount.*>=.*_requestsPerMinute|minute_count.*>=/', $this->rateLimiterContent)) {
                $this->addPass('Per-minute rate limiting: Implemented with comparison check');
            } else {
                $this->addIssue(
                    SEVERITY_HIGH,
                    'lib/ApiRateLimiter.php',
                    'Per-minute rate limit variable exists but enforcement logic not found'
                );
            }
        }
    }

    /**
     * Check #3: Per-hour rate limiting exists
     * Severity: HIGH if missing
     */
    private function checkPerHourLimit()
    {
        echo "Checking per-hour rate limiting...\n";

        // Check for hour-based limit constant or variable
        $hasHourConstant = preg_match('/REQUESTS_PER_HOUR|requestsPerHour|hour_count/i', $this->rateLimiterContent);
        $hasHourLogic = preg_match('/time\s*\(\s*\)\s*-\s*3600|strtotime.*hour|3600\s*seconds/i', $this->rateLimiterContent);

        if (!$hasHourConstant && !$hasHourLogic) {
            $this->addIssue(
                SEVERITY_HIGH,
                'lib/ApiRateLimiter.php',
                'No per-hour rate limiting detected - API vulnerable to sustained attacks'
            );
        } else {
            // Verify the logic is actually implemented
            if (preg_match('/hourCount.*>=.*_requestsPerHour|hour_count.*>=/', $this->rateLimiterContent)) {
                $this->addPass('Per-hour rate limiting: Implemented with comparison check');
            } else {
                $this->addIssue(
                    SEVERITY_HIGH,
                    'lib/ApiRateLimiter.php',
                    'Per-hour rate limit variable exists but enforcement logic not found'
                );
            }
        }
    }

    /**
     * Check #4: Rate limiting is applied after authentication in ApiUI
     * Severity: HIGH if missing
     */
    private function checkRateLimitingAfterAuth()
    {
        echo "Checking rate limiting application order...\n";

        // Check if ApiRateLimiter is included
        if (!preg_match('/include.*ApiRateLimiter|require.*ApiRateLimiter/', $this->apiUIContent)) {
            $this->addIssue(
                SEVERITY_HIGH,
                'modules/api/ApiUI.php',
                'ApiRateLimiter.php is not included - rate limiting not active'
            );
            return;
        }

        // Check if rate limiting is used
        if (!preg_match('/new\s+ApiRateLimiter|ApiRateLimiter::/', $this->apiUIContent)) {
            $this->addIssue(
                SEVERITY_HIGH,
                'modules/api/ApiUI.php',
                'ApiRateLimiter class is included but never instantiated'
            );
            return;
        }

        // Check the order: authentication should come before rate limiting
        // Look for _authenticate() call and then rate limiting after it
        $authMatch = preg_match('/if\s*\(\s*!\s*\$this->_authenticate\s*\(\s*\)\s*\)/', $this->apiUIContent);
        $rateLimitAfterAuth = preg_match(
            '/_authenticate.*?ApiRateLimiter|_authenticated.*?ApiRateLimiter/s',
            $this->apiUIContent
        );

        // Also check for the comment indicating rate limiting after auth
        $hasProperComment = preg_match('/Check rate limits after authentication/', $this->apiUIContent);

        if ($authMatch && ($rateLimitAfterAuth || $hasProperComment)) {
            $this->addPass('Rate limiting order: Applied after authentication');
        } else {
            $this->addIssue(
                SEVERITY_HIGH,
                'modules/api/ApiUI.php',
                'Rate limiting should be applied AFTER authentication to prevent abuse'
            );
        }

        // Additional check: rate limiting should use the API key ID from auth
        if (preg_match('/\$this->_apiKeyID.*ApiRateLimiter|\$rateLimitIdentifier.*_apiKeyID/', $this->apiUIContent)) {
            $this->addPass('Rate limiting identifier: Uses authenticated API key ID');
        } else {
            $this->addIssue(
                SEVERITY_MEDIUM,
                'modules/api/ApiUI.php',
                'Rate limiting may not be properly tied to authenticated user/API key'
            );
        }
    }

    /**
     * Check #5: 429 status code returned for rate limit exceeded
     * Severity: MEDIUM if missing
     */
    private function check429StatusCode()
    {
        echo "Checking 429 status code implementation...\n";

        // Check in ApiUI for 429 response
        $has429InUI = preg_match('/sendError.*429|http_response_code\s*\(\s*429\s*\)|429.*rate/i', $this->apiUIContent);

        // Also check if the rate limiter returns info that can be used for 429
        $limiterReturnsInfo = preg_match('/allowed.*false|return.*allowed/i', $this->rateLimiterContent);

        if (!$has429InUI) {
            $this->addIssue(
                SEVERITY_MEDIUM,
                'modules/api/ApiUI.php',
                'HTTP 429 (Too Many Requests) status code not returned when rate limited'
            );
        } else {
            $this->addPass('HTTP 429 status: Returned when rate limit exceeded');
        }
    }

    /**
     * Check #6: Retry-After header is set
     * Severity: LOW if missing
     */
    private function checkRetryAfterHeader()
    {
        echo "Checking Retry-After header...\n";

        // Check in rate limiter for retry_after calculation
        $hasRetryAfterInLimiter = preg_match('/retry_after|Retry-After/i', $this->rateLimiterContent);

        // Check in ApiUI for header setting
        $hasRetryAfterHeader = preg_match('/Retry-After.*header|header.*Retry-After/i', $this->apiUIContent);

        // Also check the getHeaders method
        $hasHeaderMethod = preg_match('/getHeaders.*Retry-After|Retry-After.*\$headers/is', $this->rateLimiterContent);

        if (!$hasRetryAfterInLimiter && !$hasRetryAfterHeader && !$hasHeaderMethod) {
            $this->addIssue(
                SEVERITY_LOW,
                'lib/ApiRateLimiter.php',
                'Retry-After header not set when rate limited - clients cannot know when to retry'
            );
        } else {
            // Verify the header is actually being sent
            if ($hasHeaderMethod || $hasRetryAfterHeader) {
                $this->addPass('Retry-After header: Implemented in rate limit response');
            } else {
                $this->addIssue(
                    SEVERITY_LOW,
                    'lib/ApiRateLimiter.php',
                    'Retry-After value calculated but may not be sent as header'
                );
            }
        }

        // Additional check: X-RateLimit headers for rate limit transparency
        if (preg_match('/X-RateLimit-Limit|X-RateLimit-Remaining|X-RateLimit-Reset/', $this->rateLimiterContent)) {
            $this->addPass('Rate limit headers: X-RateLimit-* headers implemented');
        }
    }

    /**
     * Add an issue to the results
     */
    private function addIssue($severity, $file, $description)
    {
        $this->issues[] = [
            'severity' => $severity,
            'file' => $file,
            'description' => $description,
            'is_pass' => false
        ];
    }

    /**
     * Add a pass result
     */
    private function addPass($description)
    {
        $this->issues[] = [
            'severity' => SEVERITY_PASS,
            'file' => '',
            'description' => $description,
            'is_pass' => true
        ];
    }

    /**
     * Check if there are any high severity issues
     */
    private function hasHighSeverityIssues()
    {
        foreach ($this->issues as $issue) {
            if (in_array($issue['severity'], [SEVERITY_CRITICAL, SEVERITY_HIGH])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get color for severity level
     */
    private function getSeverityColor($severity)
    {
        switch ($severity) {
            case SEVERITY_CRITICAL:
                return COLOR_RED;
            case SEVERITY_HIGH:
                return COLOR_RED;
            case SEVERITY_MEDIUM:
                return COLOR_YELLOW;
            case SEVERITY_LOW:
                return COLOR_YELLOW;
            case SEVERITY_PASS:
                return COLOR_GREEN;
            default:
                return COLOR_RESET;
        }
    }

    /**
     * Print audit results
     */
    private function printResults()
    {
        echo "\n" . COLOR_CYAN . "=================================================\n";
        echo "                    AUDIT RESULTS\n";
        echo "=================================================\n" . COLOR_RESET;

        $criticalCount = 0;
        $highCount = 0;
        $mediumCount = 0;
        $lowCount = 0;
        $passCount = 0;

        foreach ($this->issues as $issue) {
            $color = $this->getSeverityColor($issue['severity']);
            $severity = str_pad("[{$issue['severity']}]", 11);

            if ($issue['is_pass']) {
                echo $color . $severity . COLOR_RESET . " " . $issue['description'] . "\n";
                $passCount++;
            } else {
                echo $color . $severity . COLOR_RESET . " " . $issue['file'] . ": " . $issue['description'] . "\n";

                switch ($issue['severity']) {
                    case SEVERITY_CRITICAL:
                        $criticalCount++;
                        break;
                    case SEVERITY_HIGH:
                        $highCount++;
                        break;
                    case SEVERITY_MEDIUM:
                        $mediumCount++;
                        break;
                    case SEVERITY_LOW:
                        $lowCount++;
                        break;
                }
            }
        }

        echo "\n" . COLOR_CYAN . "=================================================\n";
        echo "                      SUMMARY\n";
        echo "=================================================\n" . COLOR_RESET;

        echo "Critical: " . ($criticalCount > 0 ? COLOR_RED . $criticalCount . COLOR_RESET : '0') . "\n";
        echo "High:     " . ($highCount > 0 ? COLOR_RED . $highCount . COLOR_RESET : '0') . "\n";
        echo "Medium:   " . ($mediumCount > 0 ? COLOR_YELLOW . $mediumCount . COLOR_RESET : '0') . "\n";
        echo "Low:      " . ($lowCount > 0 ? COLOR_YELLOW . $lowCount . COLOR_RESET : '0') . "\n";
        echo "Passed:   " . ($passCount > 0 ? COLOR_GREEN . $passCount . COLOR_RESET : '0') . "\n";

        echo "\n";
        if ($criticalCount > 0 || $highCount > 0) {
            echo COLOR_RED . "AUDIT FAILED: Critical or high severity issues found!" . COLOR_RESET . "\n";
        } else {
            echo COLOR_GREEN . "[PASS] Rate limiting implementation looks secure" . COLOR_RESET . "\n";
        }
        echo "\n";
    }
}

// Main execution
if (php_sapi_name() === 'cli' || defined('STDIN')) {
    // Determine base path
    $basePath = dirname(dirname(dirname(__FILE__)));

    // Allow override via command line argument
    if (isset($argv[1])) {
        $basePath = $argv[1];
    }

    echo "Base path: {$basePath}\n\n";

    $audit = new RateLimitAudit($basePath);
    $exitCode = $audit->run();
    exit($exitCode);
} else {
    echo "This script must be run from the command line.\n";
    exit(1);
}
