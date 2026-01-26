#!/usr/bin/env php
<?php
/**
 * CATS
 * Webhook Security Audit Script
 *
 * Audits webhook implementation for security vulnerabilities.
 * Checks for SSRF protection, URL validation, timeout settings,
 * HMAC signature generation, and other security best practices.
 *
 * Files audited:
 * - lib/WebhookDispatcher.php
 * - lib/WebhookSubscription.php
 * - modules/api/handlers/SubscriptionHandler.php
 *
 * @package    CATS
 * @subpackage Test
 * @copyright Copyright (C) 2026 Space-O Technologies
 */

/**
 * WebhookSecurityAudit
 *
 * Performs static analysis of webhook implementation files
 * to detect potential security vulnerabilities.
 */
class WebhookSecurityAudit
{
    /** @var string Base path for the OpenCATS installation */
    private $_basePath;

    /** @var array Files to audit */
    private $_filesToAudit = array(
        'lib/WebhookDispatcher.php',
        'lib/WebhookSubscription.php',
        'modules/api/handlers/SubscriptionHandler.php'
    );

    /** @var array Security issues found */
    private $_issues = array();

    /** @var array File contents cache */
    private $_fileContents = array();

    /** @var bool Whether to output in color */
    private $_useColor = true;

    /* Severity levels */
    const SEVERITY_CRITICAL = 'CRITICAL';
    const SEVERITY_HIGH = 'HIGH';
    const SEVERITY_MEDIUM = 'MEDIUM';
    const SEVERITY_LOW = 'LOW';

    /* ANSI color codes */
    const COLOR_RESET = "\033[0m";
    const COLOR_RED = "\033[31m";
    const COLOR_YELLOW = "\033[33m";
    const COLOR_GREEN = "\033[32m";
    const COLOR_CYAN = "\033[36m";
    const COLOR_WHITE = "\033[37m";

    /**
     * Constructor
     *
     * @param string $basePath Base path for OpenCATS installation
     */
    public function __construct($basePath)
    {
        $this->_basePath = rtrim($basePath, '/');

        /* Detect if running in terminal that supports color */
        $this->_useColor = (php_sapi_name() === 'cli' &&
                          (getenv('TERM') || getenv('COLORTERM') ||
                           (function_exists('posix_isatty') && posix_isatty(STDOUT))));
    }

    /**
     * Run the security audit
     *
     * @return int Exit code (0 = pass, 1 = issues found)
     */
    public function run()
    {
        $this->printHeader();

        /* Load all files to audit */
        if (!$this->loadFiles())
        {
            return 1;
        }

        /* Run all security checks */
        $this->checkUrlValidation();
        $this->checkInternalIpBlocking();
        $this->checkHttpTimeout();
        $this->checkHmacSignature();
        $this->checkCallbackUrlValidationInSubscription();
        $this->checkSslVerification();
        $this->checkRedirectHandling();
        $this->checkSecretStorage();

        /* Output results */
        return $this->printResults();
    }

    /**
     * Load files to audit into memory
     *
     * @return bool True if all files loaded successfully
     */
    private function loadFiles()
    {
        $allLoaded = true;

        foreach ($this->_filesToAudit as $file)
        {
            $fullPath = $this->_basePath . '/' . $file;

            if (!file_exists($fullPath))
            {
                $this->addIssue(
                    self::SEVERITY_CRITICAL,
                    $file,
                    "File not found: {$fullPath}"
                );
                $allLoaded = false;
                continue;
            }

            $content = file_get_contents($fullPath);
            if ($content === false)
            {
                $this->addIssue(
                    self::SEVERITY_CRITICAL,
                    $file,
                    "Unable to read file: {$fullPath}"
                );
                $allLoaded = false;
                continue;
            }

            $this->_fileContents[$file] = $content;
        }

        return $allLoaded;
    }

    /**
     * Check #1: URL validation with filter_var FILTER_VALIDATE_URL
     * Severity: HIGH if missing (SSRF risk)
     */
    private function checkUrlValidation()
    {
        $checkName = 'URL Validation (FILTER_VALIDATE_URL)';
        $found = false;

        /* Check in SubscriptionHandler.php for subscription creation */
        $handlerFile = 'modules/api/handlers/SubscriptionHandler.php';
        if (isset($this->_fileContents[$handlerFile]))
        {
            $content = $this->_fileContents[$handlerFile];

            /* Look for filter_var with FILTER_VALIDATE_URL */
            if (preg_match('/filter_var\s*\(\s*\$[^,]+,\s*FILTER_VALIDATE_URL\s*\)/i', $content))
            {
                $found = true;
            }
        }

        /* Also check WebhookDispatcher.php */
        $dispatcherFile = 'lib/WebhookDispatcher.php';
        if (isset($this->_fileContents[$dispatcherFile]))
        {
            $content = $this->_fileContents[$dispatcherFile];

            /* Check if URL validation happens before dispatch */
            if (preg_match('/filter_var\s*\(\s*\$[^,]+,\s*FILTER_VALIDATE_URL\s*\)/i', $content))
            {
                $found = true;
            }
        }

        if (!$found)
        {
            /* Check if validation exists elsewhere */
            $foundInHandler = isset($this->_fileContents[$handlerFile]) &&
                strpos($this->_fileContents[$handlerFile], 'FILTER_VALIDATE_URL') !== false;

            if ($foundInHandler)
            {
                $found = true;
            }
        }

        if ($found)
        {
            $this->addPass($checkName, 'URL validation with FILTER_VALIDATE_URL is implemented');
        }
        else
        {
            $this->addIssue(
                self::SEVERITY_HIGH,
                'SubscriptionHandler.php / WebhookDispatcher.php',
                "Missing URL validation with filter_var(FILTER_VALIDATE_URL) - SSRF risk"
            );
        }
    }

    /**
     * Check #2: Internal IP blocking (127., 10., 192.168., localhost)
     * Severity: MEDIUM if missing
     */
    private function checkInternalIpBlocking()
    {
        $checkName = 'Internal IP Blocking';
        $found = false;

        $internalPatterns = array(
            '127\.',
            '10\.',
            '192\.168\.',
            'localhost',
            '0\.0\.0\.0',
            '::1',
            '169\.254\.'  /* Link-local addresses */
        );

        /* Check in SubscriptionHandler.php */
        $handlerFile = 'modules/api/handlers/SubscriptionHandler.php';
        if (isset($this->_fileContents[$handlerFile]))
        {
            $content = $this->_fileContents[$handlerFile];

            foreach ($internalPatterns as $pattern)
            {
                if (preg_match('/' . $pattern . '/', $content))
                {
                    $found = true;
                    break;
                }
            }

            /* Also check for common SSRF protection patterns */
            if (strpos($content, 'gethostbyname') !== false ||
                strpos($content, 'parse_url') !== false ||
                strpos($content, 'isPrivateIp') !== false ||
                strpos($content, 'isInternalUrl') !== false)
            {
                $found = true;
            }
        }

        /* Check in WebhookDispatcher.php */
        $dispatcherFile = 'lib/WebhookDispatcher.php';
        if (isset($this->_fileContents[$dispatcherFile]))
        {
            $content = $this->_fileContents[$dispatcherFile];

            foreach ($internalPatterns as $pattern)
            {
                if (preg_match('/' . $pattern . '/', $content))
                {
                    $found = true;
                    break;
                }
            }

            /* Also check for common SSRF protection patterns */
            if (strpos($content, 'gethostbyname') !== false ||
                strpos($content, 'parse_url') !== false ||
                strpos($content, 'isPrivateIp') !== false ||
                strpos($content, 'isInternalUrl') !== false ||
                strpos($content, 'validateUrl') !== false)
            {
                $found = true;
            }
        }

        if ($found)
        {
            $this->addPass($checkName, 'Internal IP/hostname blocking appears to be implemented');
        }
        else
        {
            $this->addIssue(
                self::SEVERITY_MEDIUM,
                'WebhookDispatcher.php / SubscriptionHandler.php',
                "Missing internal IP blocking (127.*, 10.*, 192.168.*, localhost) - SSRF risk"
            );
        }
    }

    /**
     * Check #3: HTTP timeout setting (CURLOPT_TIMEOUT)
     * Severity: MEDIUM if missing
     */
    private function checkHttpTimeout()
    {
        $checkName = 'HTTP Timeout Setting (CURLOPT_TIMEOUT)';
        $found = false;
        $timeoutValue = null;

        /* Check in WebhookDispatcher.php */
        $dispatcherFile = 'lib/WebhookDispatcher.php';
        if (isset($this->_fileContents[$dispatcherFile]))
        {
            $content = $this->_fileContents[$dispatcherFile];

            /* Look for CURLOPT_TIMEOUT */
            if (preg_match('/CURLOPT_TIMEOUT\s*=>\s*(\d+|self::\w+|static::\w+|\$\w+)/i', $content, $matches))
            {
                $found = true;
                $timeoutValue = $matches[1];
            }

            /* Also check for curl_setopt with CURLOPT_TIMEOUT */
            if (preg_match('/curl_setopt\s*\(\s*\$\w+\s*,\s*CURLOPT_TIMEOUT\s*,\s*(\d+)/i', $content, $matches))
            {
                $found = true;
                $timeoutValue = $matches[1];
            }

            /* Check for HTTP_TIMEOUT constant */
            if (preg_match('/const\s+HTTP_TIMEOUT\s*=\s*(\d+)/i', $content, $matches))
            {
                $found = true;
                $timeoutValue = $matches[1] . ' (const HTTP_TIMEOUT)';
            }
        }

        /* Also check SubscriptionHandler.php for test webhook functionality */
        $handlerFile = 'modules/api/handlers/SubscriptionHandler.php';
        if (isset($this->_fileContents[$handlerFile]))
        {
            $content = $this->_fileContents[$handlerFile];

            if (preg_match('/CURLOPT_TIMEOUT\s*,\s*(\d+)/i', $content, $matches))
            {
                if (!$found)
                {
                    $found = true;
                    $timeoutValue = $matches[1];
                }
            }
        }

        if ($found)
        {
            $this->addPass($checkName, "HTTP timeout is configured" . ($timeoutValue ? " ({$timeoutValue}s)" : ""));
        }
        else
        {
            $this->addIssue(
                self::SEVERITY_MEDIUM,
                'WebhookDispatcher.php',
                "Missing CURLOPT_TIMEOUT setting - DoS risk from slow/hanging connections"
            );
        }
    }

    /**
     * Check #4: HMAC signature generation with hash_hmac
     * Severity: HIGH if missing
     */
    private function checkHmacSignature()
    {
        $checkName = 'HMAC Signature Generation';
        $foundGenerate = false;
        $foundVerify = false;
        $algorithm = null;

        /* Check in WebhookDispatcher.php */
        $dispatcherFile = 'lib/WebhookDispatcher.php';
        if (isset($this->_fileContents[$dispatcherFile]))
        {
            $content = $this->_fileContents[$dispatcherFile];

            /* Look for hash_hmac for signature generation */
            if (preg_match('/hash_hmac\s*\(\s*[\'"](\w+)[\'"]/', $content, $matches))
            {
                $foundGenerate = true;
                $algorithm = $matches[1];
            }

            /* Look for generateSignature method */
            if (strpos($content, 'generateSignature') !== false)
            {
                $foundGenerate = true;
            }

            /* Look for verifySignature method */
            if (strpos($content, 'verifySignature') !== false)
            {
                $foundVerify = true;
            }

            /* Check for hash_equals for timing-safe comparison */
            if (strpos($content, 'hash_equals') !== false)
            {
                $foundVerify = true;
            }
        }

        /* Also check SubscriptionHandler.php for test webhook HMAC */
        $handlerFile = 'modules/api/handlers/SubscriptionHandler.php';
        if (isset($this->_fileContents[$handlerFile]))
        {
            $content = $this->_fileContents[$handlerFile];

            if (preg_match('/hash_hmac\s*\(\s*[\'"](\w+)[\'"]/', $content, $matches))
            {
                $foundGenerate = true;
                if (!$algorithm)
                {
                    $algorithm = $matches[1];
                }
            }
        }

        if ($foundGenerate)
        {
            $detail = "HMAC signature generation implemented";
            if ($algorithm)
            {
                $detail .= " using {$algorithm}";
            }
            if ($foundVerify)
            {
                $detail .= " with verification support";
            }
            $this->addPass($checkName, $detail);
        }
        else
        {
            $this->addIssue(
                self::SEVERITY_HIGH,
                'WebhookDispatcher.php',
                "Missing HMAC signature generation with hash_hmac - webhook authenticity cannot be verified"
            );
        }
    }

    /**
     * Check #5: Callback URL validation in subscription creation
     * Severity: HIGH if missing
     */
    private function checkCallbackUrlValidationInSubscription()
    {
        $checkName = 'Callback URL Validation in Subscription Creation';
        $found = false;

        /* Check in SubscriptionHandler.php handlePost method */
        $handlerFile = 'modules/api/handlers/SubscriptionHandler.php';
        if (isset($this->_fileContents[$handlerFile]))
        {
            $content = $this->_fileContents[$handlerFile];

            /* Look for callbackUrl validation in POST handler */
            if (strpos($content, 'callbackUrl') !== false)
            {
                /* Check for validation before insertion */
                if (strpos($content, 'FILTER_VALIDATE_URL') !== false)
                {
                    $found = true;
                }

                /* Check for empty check */
                if (preg_match('/empty\s*\(\s*\$input\s*\[\s*[\'"]callbackUrl[\'"]\s*\]\s*\)/i', $content))
                {
                    /* Has empty check, but need validation too */
                    if (strpos($content, 'FILTER_VALIDATE_URL') !== false)
                    {
                        $found = true;
                    }
                }
            }
        }

        /* Also check WebhookSubscription.php add() method */
        $subscriptionFile = 'lib/WebhookSubscription.php';
        if (isset($this->_fileContents[$subscriptionFile]))
        {
            $content = $this->_fileContents[$subscriptionFile];

            /* Look for URL validation in add method */
            if (strpos($content, 'FILTER_VALIDATE_URL') !== false)
            {
                $found = true;
            }
        }

        if ($found)
        {
            $this->addPass($checkName, 'Callback URL validation is implemented during subscription creation');
        }
        else
        {
            $this->addIssue(
                self::SEVERITY_HIGH,
                'SubscriptionHandler.php',
                "Insufficient callback URL validation in subscription creation - accepts potentially malicious URLs"
            );
        }
    }

    /**
     * Check #6: SSL certificate verification
     * Severity: HIGH if disabled
     */
    private function checkSslVerification()
    {
        $checkName = 'SSL Certificate Verification';
        $sslEnabled = true;
        $details = array();

        /* Check in WebhookDispatcher.php */
        $dispatcherFile = 'lib/WebhookDispatcher.php';
        if (isset($this->_fileContents[$dispatcherFile]))
        {
            $content = $this->_fileContents[$dispatcherFile];

            /* Check for CURLOPT_SSL_VERIFYPEER */
            if (preg_match('/CURLOPT_SSL_VERIFYPEER\s*=>\s*(true|false|0|1)/i', $content, $matches))
            {
                $value = strtolower($matches[1]);
                if ($value === 'false' || $value === '0')
                {
                    $sslEnabled = false;
                    $details[] = 'CURLOPT_SSL_VERIFYPEER is disabled';
                }
                else
                {
                    $details[] = 'CURLOPT_SSL_VERIFYPEER is enabled';
                }
            }

            /* Check for CURLOPT_SSL_VERIFYHOST */
            if (preg_match('/CURLOPT_SSL_VERIFYHOST\s*=>\s*(\d+)/i', $content, $matches))
            {
                if (intval($matches[1]) < 2)
                {
                    $sslEnabled = false;
                    $details[] = 'CURLOPT_SSL_VERIFYHOST is set to ' . $matches[1] . ' (should be 2)';
                }
                else
                {
                    $details[] = 'CURLOPT_SSL_VERIFYHOST is properly set to ' . $matches[1];
                }
            }
        }

        if ($sslEnabled)
        {
            $this->addPass($checkName, 'SSL verification is properly enabled: ' . implode(', ', $details));
        }
        else
        {
            $this->addIssue(
                self::SEVERITY_HIGH,
                'WebhookDispatcher.php',
                "SSL verification is disabled: " . implode(', ', $details) . " - MITM attack risk"
            );
        }
    }

    /**
     * Check #7: Redirect handling limits
     * Severity: LOW if unlimited
     */
    private function checkRedirectHandling()
    {
        $checkName = 'HTTP Redirect Handling';
        $hasLimit = false;

        /* Check in WebhookDispatcher.php */
        $dispatcherFile = 'lib/WebhookDispatcher.php';
        if (isset($this->_fileContents[$dispatcherFile]))
        {
            $content = $this->_fileContents[$dispatcherFile];

            /* Check for CURLOPT_MAXREDIRS */
            if (preg_match('/CURLOPT_MAXREDIRS\s*=>\s*(\d+)/i', $content, $matches))
            {
                $hasLimit = true;
                $maxRedirs = intval($matches[1]);
            }

            /* Check for CURLOPT_FOLLOWLOCATION */
            if (preg_match('/CURLOPT_FOLLOWLOCATION\s*=>\s*(true|false)/i', $content, $matches))
            {
                if (strtolower($matches[1]) === 'false')
                {
                    $hasLimit = true; /* Redirects disabled entirely */
                }
            }
        }

        if ($hasLimit)
        {
            $detail = isset($maxRedirs) ? "Max redirects: {$maxRedirs}" : "Redirect following configured";
            $this->addPass($checkName, $detail);
        }
        else
        {
            $this->addIssue(
                self::SEVERITY_LOW,
                'WebhookDispatcher.php',
                "No CURLOPT_MAXREDIRS limit set - potential for redirect loops or redirect-based SSRF"
            );
        }
    }

    /**
     * Check #8: Secret storage (not logged/exposed)
     * Severity: MEDIUM if secrets appear in logs
     */
    private function checkSecretStorage()
    {
        $checkName = 'Secret Storage Security';
        $issues = array();

        foreach ($this->_fileContents as $file => $content)
        {
            /* Check if secret appears in log statements */
            if (preg_match('/(?:error_log|print_r|var_dump|echo)\s*\([^)]*secret/i', $content))
            {
                $issues[] = "{$file}: Secret may be logged";
            }

            /* Check if secret is included in response body */
            if (preg_match('/json_encode\s*\([^)]*secret/i', $content) &&
                strpos($content, 'formatSubscription') !== false)
            {
                /* Check if secret is explicitly excluded in formatSubscription */
                if (preg_match('/function\s+formatSubscription[^}]+secret/is', $content))
                {
                    $issues[] = "{$file}: Secret may be exposed in API response";
                }
            }
        }

        if (empty($issues))
        {
            $this->addPass($checkName, 'Secrets appear to be handled securely (not logged or exposed)');
        }
        else
        {
            foreach ($issues as $issue)
            {
                $this->addIssue(
                    self::SEVERITY_MEDIUM,
                    '',
                    $issue
                );
            }
        }
    }

    /**
     * Add a security issue
     *
     * @param string $severity Severity level
     * @param string $file     File where issue was found
     * @param string $description Issue description
     */
    private function addIssue($severity, $file, $description)
    {
        $this->_issues[] = array(
            'severity' => $severity,
            'file' => $file,
            'description' => $description
        );
    }

    /**
     * Add a passing check
     *
     * @param string $checkName Name of the check
     * @param string $detail    Details about why it passed
     */
    private function addPass($checkName, $detail)
    {
        $this->_issues[] = array(
            'severity' => 'PASS',
            'file' => $checkName,
            'description' => $detail
        );
    }

    /**
     * Print colored text
     *
     * @param string $text  Text to print
     * @param string $color Color code
     */
    private function colorize($text, $color)
    {
        if ($this->_useColor)
        {
            return $color . $text . self::COLOR_RESET;
        }
        return $text;
    }

    /**
     * Print the audit header
     */
    private function printHeader()
    {
        echo "\n";
        echo $this->colorize("==========================================================", self::COLOR_CYAN) . "\n";
        echo $this->colorize("       OpenCATS Webhook Security Audit", self::COLOR_CYAN) . "\n";
        echo $this->colorize("==========================================================", self::COLOR_CYAN) . "\n";
        echo "\n";
        echo "Date: " . date('Y-m-d H:i:s') . "\n";
        echo "Base Path: " . $this->_basePath . "\n";
        echo "\n";
        echo $this->colorize("Files being audited:", self::COLOR_WHITE) . "\n";
        foreach ($this->_filesToAudit as $file)
        {
            echo "  - " . $file . "\n";
        }
        echo "\n";
        echo $this->colorize("----------------------------------------------------------", self::COLOR_CYAN) . "\n";
        echo "\n";
    }

    /**
     * Print the audit results
     *
     * @return int Exit code (0 = pass, 1 = issues found)
     */
    private function printResults()
    {
        $criticalCount = 0;
        $highCount = 0;
        $mediumCount = 0;
        $lowCount = 0;
        $passCount = 0;

        /* Count issues by severity */
        foreach ($this->_issues as $issue)
        {
            switch ($issue['severity'])
            {
                case self::SEVERITY_CRITICAL:
                    $criticalCount++;
                    break;
                case self::SEVERITY_HIGH:
                    $highCount++;
                    break;
                case self::SEVERITY_MEDIUM:
                    $mediumCount++;
                    break;
                case self::SEVERITY_LOW:
                    $lowCount++;
                    break;
                case 'PASS':
                    $passCount++;
                    break;
            }
        }

        /* Print each issue */
        foreach ($this->_issues as $issue)
        {
            $severityColor = self::COLOR_WHITE;
            $prefix = '';

            switch ($issue['severity'])
            {
                case self::SEVERITY_CRITICAL:
                    $severityColor = self::COLOR_RED;
                    $prefix = '[CRITICAL]';
                    break;
                case self::SEVERITY_HIGH:
                    $severityColor = self::COLOR_RED;
                    $prefix = '[HIGH]    ';
                    break;
                case self::SEVERITY_MEDIUM:
                    $severityColor = self::COLOR_YELLOW;
                    $prefix = '[MEDIUM]  ';
                    break;
                case self::SEVERITY_LOW:
                    $severityColor = self::COLOR_YELLOW;
                    $prefix = '[LOW]     ';
                    break;
                case 'PASS':
                    $severityColor = self::COLOR_GREEN;
                    $prefix = '[PASS]    ';
                    break;
            }

            $fileInfo = !empty($issue['file']) ? $issue['file'] . ': ' : '';
            echo $this->colorize($prefix, $severityColor) . ' ' . $fileInfo . $issue['description'] . "\n";
        }

        /* Print summary */
        echo "\n";
        echo $this->colorize("----------------------------------------------------------", self::COLOR_CYAN) . "\n";
        echo $this->colorize("                      SUMMARY", self::COLOR_CYAN) . "\n";
        echo $this->colorize("----------------------------------------------------------", self::COLOR_CYAN) . "\n";
        echo "\n";

        echo "Checks Passed:     " . $this->colorize($passCount, self::COLOR_GREEN) . "\n";
        echo "Critical Issues:   " . $this->colorize($criticalCount, $criticalCount > 0 ? self::COLOR_RED : self::COLOR_GREEN) . "\n";
        echo "High Issues:       " . $this->colorize($highCount, $highCount > 0 ? self::COLOR_RED : self::COLOR_GREEN) . "\n";
        echo "Medium Issues:     " . $this->colorize($mediumCount, $mediumCount > 0 ? self::COLOR_YELLOW : self::COLOR_GREEN) . "\n";
        echo "Low Issues:        " . $this->colorize($lowCount, $lowCount > 0 ? self::COLOR_YELLOW : self::COLOR_GREEN) . "\n";

        echo "\n";

        /* Final verdict */
        if ($criticalCount === 0 && $highCount === 0 && $mediumCount === 0 && $lowCount === 0)
        {
            echo $this->colorize("[PASS] Webhook implementation looks secure", self::COLOR_GREEN) . "\n";
            echo "\n";
            return 0;
        }
        else if ($criticalCount > 0 || $highCount > 0)
        {
            echo $this->colorize("[FAIL] Critical or high severity issues found - immediate attention required", self::COLOR_RED) . "\n";
            echo "\n";
            return 1;
        }
        else
        {
            echo $this->colorize("[WARN] Medium/low severity issues found - review recommended", self::COLOR_YELLOW) . "\n";
            echo "\n";
            return 0;
        }
    }
}

/* ============================================================================
 * MAIN ENTRY POINT
 * ============================================================================ */

/* Determine base path */
$basePath = dirname(dirname(dirname(__FILE__))); /* Go up from test/security to root */

/* Allow override via command line argument */
if (isset($argv[1]))
{
    $basePath = $argv[1];
}

/* Run the audit */
$audit = new WebhookSecurityAudit($basePath);
$exitCode = $audit->run();

exit($exitCode);
