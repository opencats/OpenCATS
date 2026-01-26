#!/usr/bin/env php
<?php
/**
 * OpenCATS CRUD Operation Completeness Audit
 *
 * This script audits the REST API handlers to verify that each handler
 * implements all expected HTTP methods (GET, POST, PUT, DELETE).
 *
 * The audit performs static analysis of handler switch statements to
 * identify implemented methods.
 *
 * Checks performed:
 * 1. Verify handler files exist
 * 2. Check for expected HTTP method implementations via case statements
 * 3. Report missing methods per handler
 * 4. Summary with total missing methods count
 *
 * @package    CATS
 * @subpackage Testing
 * @copyright  Copyright (C) 2026 Space-O Technologies
 */

// Exit codes
define('EXIT_SUCCESS', 0);
define('EXIT_FAILURE', 1);

/**
 * CRUD Completeness Auditor Class
 */
class CrudCompletenessAudit
{
    /**
     * @var string Base path to OpenCATS installation
     */
    private $basePath;

    /**
     * @var string Path to handlers directory
     */
    private $handlersPath;

    /**
     * @var array Handler expectations configuration
     * Each handler maps to array of expected HTTP methods
     */
    private $handlerExpectations = [
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
        'AttachmentHandler' => ['GET', 'POST', 'DELETE'],  // No PUT - files are replaced
        'MassUpdateHandler' => ['POST'],  // POST only
        'AssociationHandler' => ['GET', 'POST', 'DELETE'],  // No PUT
        'MetaHandler' => ['GET'],  // GET only
        'OAuthHandler' => ['GET', 'POST']  // GET and POST only
    ];

    /**
     * @var array Audit results per handler
     */
    private $results = [];

    /**
     * @var int Total missing methods count
     */
    private $totalMissing = 0;

    /**
     * @var int Total handlers checked
     */
    private $handlersChecked = 0;

    /**
     * @var int Handlers passed (all methods found)
     */
    private $handlersPassed = 0;

    /**
     * @var int Handlers failed (missing methods)
     */
    private $handlersFailed = 0;

    /**
     * @var int Handlers not found
     */
    private $handlersNotFound = 0;

    /**
     * Constructor
     *
     * @param string $basePath Base path to OpenCATS installation
     */
    public function __construct($basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        $this->handlersPath = $this->basePath . '/modules/api/handlers';
    }

    /**
     * Run the complete audit
     *
     * @return int Exit code
     */
    public function run()
    {
        $this->printHeader();

        // Check handlers directory exists
        if (!is_dir($this->handlersPath)) {
            $this->printError("Handlers directory not found: {$this->handlersPath}");
            return EXIT_FAILURE;
        }

        // Audit each handler
        foreach ($this->handlerExpectations as $handlerName => $expectedMethods) {
            $this->auditHandler($handlerName, $expectedMethods);
        }

        // Print results
        $this->printResults();
        $this->printSummary();

        // Return appropriate exit code
        return ($this->totalMissing > 0 || $this->handlersNotFound > 0)
            ? EXIT_FAILURE
            : EXIT_SUCCESS;
    }

    /**
     * Audit a single handler for expected methods
     *
     * @param string $handlerName Handler class name
     * @param array $expectedMethods Expected HTTP methods
     */
    private function auditHandler($handlerName, $expectedMethods)
    {
        $this->handlersChecked++;

        $fileName = $handlerName . '.php';
        $filePath = $this->handlersPath . '/' . $fileName;

        // Check file exists
        if (!file_exists($filePath)) {
            $this->results[$handlerName] = [
                'exists' => false,
                'file' => $fileName,
                'expected' => $expectedMethods,
                'found' => [],
                'missing' => $expectedMethods,
                'status' => 'NOT_FOUND'
            ];
            $this->handlersNotFound++;
            $this->totalMissing += count($expectedMethods);
            return;
        }

        // Read and analyze file content
        $content = file_get_contents($filePath);
        $foundMethods = $this->findImplementedMethods($content);

        // Calculate missing methods
        $missingMethods = array_diff($expectedMethods, $foundMethods);

        // Store results
        $this->results[$handlerName] = [
            'exists' => true,
            'file' => $fileName,
            'expected' => $expectedMethods,
            'found' => $foundMethods,
            'missing' => array_values($missingMethods),
            'status' => empty($missingMethods) ? 'PASS' : 'FAIL'
        ];

        if (empty($missingMethods)) {
            $this->handlersPassed++;
        } else {
            $this->handlersFailed++;
            $this->totalMissing += count($missingMethods);
        }
    }

    /**
     * Find implemented HTTP methods in handler content
     *
     * Looks for patterns like:
     * - case 'GET':
     * - case 'POST':
     * - case 'PUT':
     * - case 'DELETE':
     * - === 'GET'
     * - === 'POST'
     * - !== 'POST' (used for method validation - means POST is required)
     * - $_SERVER['REQUEST_METHOD'] !== 'POST' (explicit method check)
     * - Handlers that use $_GET without method check (implicit GET-only)
     *
     * @param string $content File content
     * @return array Array of found HTTP methods
     */
    private function findImplementedMethods($content)
    {
        $methods = [];
        $httpMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'];

        foreach ($httpMethods as $method) {
            // Check for case statement pattern (most common)
            $casePattern = "/case\s*['\"]" . $method . "['\"]\s*:/i";

            // Check for direct comparison pattern (=== 'METHOD')
            $comparisonPattern = "/===\s*['\"]" . $method . "['\"]/i";

            // Check for not-equals comparison with REQUEST_METHOD
            // Pattern: if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            // This means the handler ONLY accepts POST (rejects other methods)
            $notEqualsPattern = "/\\\$_SERVER\s*\[\s*['\"]REQUEST_METHOD['\"]\s*\]\s*!==\s*['\"]" . $method . "['\"]/i";

            // Check for method handling function pattern
            $functionPattern = "/handle" . ucfirst(strtolower($method)) . "\s*\(/i";

            // Pattern found in switch or direct comparison
            if (preg_match($casePattern, $content) ||
                preg_match($comparisonPattern, $content) ||
                preg_match($functionPattern, $content)) {
                $methods[] = $method;
            }

            // Check for !== 'METHOD' pattern - this means only that METHOD is accepted
            // e.g., if ($_SERVER['REQUEST_METHOD'] !== 'POST') { error } means POST is implemented
            if (preg_match($notEqualsPattern, $content)) {
                $methods[] = $method;
            }
        }

        // Special case for AssociationHandler which also accepts PUT as alias for POST
        if (preg_match("/case\s*['\"]PUT['\"]\s*:\s*\n\s*case\s*['\"]POST['\"]/i", $content) ||
            preg_match("/case\s*['\"]POST['\"]\s*:\s*\n\s*case\s*['\"]PUT['\"]/i", $content)) {
            if (!in_array('PUT', $methods)) {
                $methods[] = 'PUT';
            }
            if (!in_array('POST', $methods)) {
                $methods[] = 'POST';
            }
        }

        // Special case: Handlers that use $_GET but don't check REQUEST_METHOD
        // are implicitly GET-only handlers (read-only endpoints like MetaHandler)
        // Detect this by: uses $_GET, has public handle() function, no switch on method
        $hasHandle = preg_match("/public\s+function\s+handle\s*\(\s*\)/i", $content);
        $usesGetParams = preg_match("/\\\$_GET\s*\[/", $content);
        $hasMethodSwitch = preg_match("/switch\s*\(\s*\\\$method\s*\)/i", $content);
        $hasMethodCheck = preg_match("/\\\$_SERVER\s*\[\s*['\"]REQUEST_METHOD['\"]\s*\]/", $content);

        if ($hasHandle && $usesGetParams && !$hasMethodSwitch && !$hasMethodCheck) {
            // This is likely a GET-only handler
            if (!in_array('GET', $methods)) {
                $methods[] = 'GET';
            }
        }

        return array_unique($methods);
    }

    /**
     * Print audit header
     */
    private function printHeader()
    {
        echo "\n";
        echo "==========================================================================\n";
        echo "  OpenCATS CRUD Operation Completeness Audit\n";
        echo "==========================================================================\n";
        echo "  Base Path: {$this->basePath}\n";
        echo "  Handlers Path: {$this->handlersPath}\n";
        echo "  Date: " . date('Y-m-d H:i:s') . "\n";
        echo "==========================================================================\n\n";
    }

    /**
     * Print individual handler results
     */
    private function printResults()
    {
        echo "Handler Audit Results:\n";
        echo str_repeat('-', 74) . "\n";

        foreach ($this->results as $handlerName => $result) {
            $this->printHandlerResult($handlerName, $result);
        }
    }

    /**
     * Print single handler result
     *
     * @param string $handlerName Handler name
     * @param array $result Result data
     */
    private function printHandlerResult($handlerName, $result)
    {
        $statusIndicator = $this->getStatusIndicator($result['status']);

        echo "\n{$statusIndicator} {$handlerName}\n";
        echo "   File: {$result['file']}\n";

        if (!$result['exists']) {
            echo "   Status: FILE NOT FOUND\n";
            echo "   Expected Methods: " . implode(', ', $result['expected']) . "\n";
            return;
        }

        echo "   Expected: " . implode(', ', $result['expected']) . "\n";
        echo "   Found:    " . implode(', ', $result['found']) . "\n";

        if (!empty($result['missing'])) {
            echo "   Missing:  " . implode(', ', $result['missing']) . "\n";
        } else {
            echo "   Missing:  (none)\n";
        }
    }

    /**
     * Get status indicator string
     *
     * @param string $status Status code
     * @return string Status indicator
     */
    private function getStatusIndicator($status)
    {
        switch ($status) {
            case 'PASS':
                return '[PASS]';
            case 'FAIL':
                return '[FAIL]';
            case 'NOT_FOUND':
                return '[MISS]';
            default:
                return '[????]';
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
        echo "  Handlers Checked:     {$this->handlersChecked}\n";
        echo "  Handlers Passed:      {$this->handlersPassed}\n";
        echo "  Handlers Failed:      {$this->handlersFailed}\n";
        echo "  Handlers Not Found:   {$this->handlersNotFound}\n";
        echo "  --------------------\n";
        echo "  Total Missing Methods: {$this->totalMissing}\n";
        echo "==========================================================================\n";

        if ($this->totalMissing > 0 || $this->handlersNotFound > 0) {
            echo "\n  *** AUDIT FAILED - Missing methods or handlers detected ***\n";

            // List all failures
            if ($this->handlersNotFound > 0) {
                echo "\n  Missing Handler Files:\n";
                foreach ($this->results as $name => $result) {
                    if ($result['status'] === 'NOT_FOUND') {
                        echo "    - {$name}\n";
                    }
                }
            }

            if ($this->handlersFailed > 0) {
                echo "\n  Handlers with Missing Methods:\n";
                foreach ($this->results as $name => $result) {
                    if ($result['status'] === 'FAIL' && !empty($result['missing'])) {
                        echo "    - {$name}: " . implode(', ', $result['missing']) . "\n";
                    }
                }
            }
        } else {
            echo "\n  All handlers implement expected methods.\n";
        }
        echo "\n";
    }

    /**
     * Print error message
     *
     * @param string $message Error message
     */
    private function printError($message)
    {
        echo "\nERROR: {$message}\n\n";
    }

    /**
     * Get audit results as array (for programmatic use)
     *
     * @return array Audit results
     */
    public function getResults()
    {
        return [
            'handlers' => $this->results,
            'summary' => [
                'total_checked' => $this->handlersChecked,
                'passed' => $this->handlersPassed,
                'failed' => $this->handlersFailed,
                'not_found' => $this->handlersNotFound,
                'total_missing_methods' => $this->totalMissing
            ]
        ];
    }
}

// =============================================================================
// MAIN EXECUTION
// =============================================================================

// Determine base path
$basePath = dirname(dirname(dirname(__FILE__))); // Go up from test/functional/ to root

// Allow override via command line argument
if (isset($argv[1])) {
    $basePath = $argv[1];
}

// Verify base path
if (!file_exists($basePath . '/lib/DatabaseConnection.php')) {
    echo "Error: Could not find OpenCATS installation at: {$basePath}\n";
    echo "Usage: php crud_completeness_audit.php [/path/to/opencats]\n";
    exit(EXIT_FAILURE);
}

// Run the audit
$audit = new CrudCompletenessAudit($basePath);
$exitCode = $audit->run();

exit($exitCode);
