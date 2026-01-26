#!/usr/bin/env php
<?php
/**
 * Error Handling Audit Script for OpenCATS REST API
 *
 * Verifies proper error handling in all API handlers.
 *
 * Checks:
 * 1. Database operations have try-catch
 * 2. POST handlers return 201 on create
 * 3. DELETE handlers return 200 or 204
 * 4. Handlers return 404 for not found
 * 5. Handlers return 400 for bad request
 * 6. Handlers use sendError() for error responses
 *
 * Copyright (C) 2026 Space-O Technologies (https://www.spaceotechnologies.com/)
 *
 * @package    CATS
 * @subpackage Test
 */

// Color output helpers
class Colors
{
    const RED = "\033[31m";
    const GREEN = "\033[32m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const CYAN = "\033[36m";
    const BOLD = "\033[1m";
    const RESET = "\033[0m";

    public static function red($text) { return self::RED . $text . self::RESET; }
    public static function green($text) { return self::GREEN . $text . self::RESET; }
    public static function yellow($text) { return self::YELLOW . $text . self::RESET; }
    public static function blue($text) { return self::BLUE . $text . self::RESET; }
    public static function cyan($text) { return self::CYAN . $text . self::RESET; }
    public static function bold($text) { return self::BOLD . $text . self::RESET; }
}

class ErrorHandlingAudit
{
    private $handlersPath;
    private $totalIssues = 0;
    private $totalPasses = 0;
    private $results = [];
    private $verbose = false;

    // Checks configuration
    private $checks = [
        'tryCatch' => [
            'name' => 'Database Try-Catch',
            'description' => 'Database operations should have try-catch blocks'
        ],
        'postCreate201' => [
            'name' => 'POST Creates Return 201',
            'description' => 'POST handlers should return 201 on successful create'
        ],
        'deleteResponse' => [
            'name' => 'DELETE Returns 200/204',
            'description' => 'DELETE handlers should return 200 or 204'
        ],
        'notFound404' => [
            'name' => 'Not Found Returns 404',
            'description' => 'Handlers should return 404 for not found errors'
        ],
        'badRequest400' => [
            'name' => 'Bad Request Returns 400',
            'description' => 'Handlers should return 400 for bad request errors'
        ],
        'sendErrorUsage' => [
            'name' => 'Uses sendError()',
            'description' => 'Handlers should use sendError() for error responses'
        ],
    ];

    public function __construct($handlersPath, $verbose = false)
    {
        $this->handlersPath = $handlersPath;
        $this->verbose = $verbose;
    }

    /**
     * Run the audit
     * @return int Exit code (0 for success, 1 for issues found)
     */
    public function run()
    {
        $this->printHeader();

        // Find all handler files
        $handlers = glob($this->handlersPath . '/*.php');

        if (empty($handlers)) {
            echo Colors::red("No handler files found in: {$this->handlersPath}\n");
            return 1;
        }

        echo Colors::cyan("Found " . count($handlers) . " handler files\n\n");

        // Audit each handler
        foreach ($handlers as $handlerFile) {
            $this->auditHandler($handlerFile);
        }

        // Print summary
        return $this->printSummary();
    }

    /**
     * Print header
     */
    private function printHeader()
    {
        echo Colors::bold("\n" . str_repeat("=", 70) . "\n");
        echo Colors::bold("  OpenCATS REST API - Error Handling Audit\n");
        echo Colors::bold(str_repeat("=", 70) . "\n\n");
        echo "Handlers Path: " . Colors::blue($this->handlersPath) . "\n";
        echo "Date: " . date('Y-m-d H:i:s') . "\n\n";
    }

    /**
     * Audit a single handler file
     * @param string $file Handler file path
     */
    private function auditHandler($file)
    {
        $filename = basename($file);
        $content = file_get_contents($file);

        echo Colors::bold(str_repeat("-", 60) . "\n");
        echo Colors::bold("Handler: " . Colors::cyan($filename) . "\n");
        echo Colors::bold(str_repeat("-", 60) . "\n");

        $handlerResult = [
            'file' => $filename,
            'path' => $file,
            'checks' => [],
            'issues' => 0,
            'passes' => 0
        ];

        // Run all checks
        $handlerResult['checks']['tryCatch'] = $this->checkTryCatch($content, $filename);
        $handlerResult['checks']['postCreate201'] = $this->checkPostCreate201($content, $filename);
        $handlerResult['checks']['deleteResponse'] = $this->checkDeleteResponse($content, $filename);
        $handlerResult['checks']['notFound404'] = $this->checkNotFound404($content, $filename);
        $handlerResult['checks']['badRequest400'] = $this->checkBadRequest400($content, $filename);
        $handlerResult['checks']['sendErrorUsage'] = $this->checkSendErrorUsage($content, $filename);

        // Count issues and passes
        foreach ($handlerResult['checks'] as $check) {
            if ($check['status'] === 'PASS') {
                $handlerResult['passes']++;
                $this->totalPasses++;
            } else {
                $handlerResult['issues']++;
                $this->totalIssues++;
            }
        }

        // Print handler summary
        $statusColor = $handlerResult['issues'] === 0 ? 'green' : 'yellow';
        $statusText = $handlerResult['issues'] === 0 ? '[PASS]' : '[REVIEW]';
        echo "\n" . Colors::$statusColor($statusText) . " ";
        echo $handlerResult['passes'] . " passed, " . $handlerResult['issues'] . " needs review\n\n";

        $this->results[] = $handlerResult;
    }

    /**
     * Check 1: Database operations have try-catch
     * Looks for $this->_db-> or query() calls and checks if they're wrapped in try-catch
     */
    private function checkTryCatch($content, $filename)
    {
        $result = [
            'name' => $this->checks['tryCatch']['name'],
            'status' => 'PASS',
            'message' => '',
            'details' => []
        ];

        // Find database operation patterns
        $dbPatterns = [
            '/\$this->_db->/' => 'Direct database access ($this->_db->)',
            '/\$db->query\s*\(/' => 'Direct query() call',
            '/\$this->_db->query\s*\(/' => 'Database query() call',
            '/->getAll\s*\(/' => 'getAll() call',
            '/->getAssoc\s*\(/' => 'getAssoc() call',
        ];

        $hasDbOperations = false;
        $hasTryCatch = preg_match('/try\s*\{/', $content);
        $issues = [];

        foreach ($dbPatterns as $pattern => $desc) {
            if (preg_match($pattern, $content)) {
                $hasDbOperations = true;
                // Check if this specific operation is wrapped in try-catch
                // This is a simplified check - we look for try blocks containing db operations
                if (!$this->isOperationInTryCatch($content, $pattern)) {
                    $issues[] = $desc . ' without try-catch';
                }
            }
        }

        // For handlers that do direct DB operations (like MassUpdateHandler), verify try-catch
        if ($hasDbOperations && !empty($issues)) {
            // Check if the handler uses library classes (which have their own error handling)
            $usesLibrary = preg_match('/new\s+(Candidates|Companies|JobOrders|Contacts|Notes|Tasks|Placements|Attachments|Tearsheets)\s*\(/', $content);

            if (!$usesLibrary && !$hasTryCatch) {
                $result['status'] = 'REVIEW';
                $result['message'] = 'Direct database operations found without try-catch';
                $result['details'] = $issues;
            } elseif ($usesLibrary) {
                $result['message'] = 'Uses library classes with built-in error handling';
            } else {
                $result['message'] = 'Has try-catch blocks for database operations';
            }
        } else {
            $result['message'] = $hasDbOperations ? 'Database operations properly wrapped' : 'No direct database operations';
        }

        $this->printCheckResult($result);
        return $result;
    }

    /**
     * Check if a database operation pattern is inside a try-catch block
     */
    private function isOperationInTryCatch($content, $pattern)
    {
        // Find all try-catch blocks
        if (preg_match_all('/try\s*\{([^{}]*(?:\{[^{}]*\}[^{}]*)*)\}\s*catch/', $content, $tryMatches)) {
            foreach ($tryMatches[1] as $tryBlock) {
                if (preg_match($pattern, $tryBlock)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check 2: POST handlers return 201 on create
     */
    private function checkPostCreate201($content, $filename)
    {
        $result = [
            'name' => $this->checks['postCreate201']['name'],
            'status' => 'PASS',
            'message' => '',
            'details' => []
        ];

        // Check if handler has handlePost method
        $hasPostHandler = preg_match('/function\s+handlePost\s*\(/', $content) ||
                          preg_match('/case\s+[\'"]POST[\'"]/', $content);

        if (!$hasPostHandler) {
            $result['message'] = 'No POST handler found';
            $this->printCheckResult($result);
            return $result;
        }

        // Look for sendSuccess with 201 in POST context
        $has201Response = preg_match('/sendSuccess\s*\([^,]+,\s*201\s*\)/', $content);

        // Check if create operations exist and return 201
        $hasCreateLogic = preg_match('/->add\s*\(|->create\s*\(|Created?\s+successfully/i', $content);

        if ($hasCreateLogic) {
            if ($has201Response) {
                $result['message'] = 'POST create returns 201 status';
            } else {
                $result['status'] = 'REVIEW';
                $result['message'] = 'POST handler may not return 201 for creates';
                $result['details'][] = 'sendSuccess() with 201 not found for create operations';
            }
        } else {
            $result['message'] = 'POST handler verified';
        }

        $this->printCheckResult($result);
        return $result;
    }

    /**
     * Check 3: DELETE handlers return 200 or 204
     */
    private function checkDeleteResponse($content, $filename)
    {
        $result = [
            'name' => $this->checks['deleteResponse']['name'],
            'status' => 'PASS',
            'message' => '',
            'details' => []
        ];

        // Check if handler has handleDelete method
        $hasDeleteHandler = preg_match('/function\s+handleDelete\s*\(/', $content) ||
                            preg_match('/case\s+[\'"]DELETE[\'"]/', $content);

        if (!$hasDeleteHandler) {
            $result['message'] = 'No DELETE handler found';
            $this->printCheckResult($result);
            return $result;
        }

        // Look for sendSuccess in DELETE context (default is 200)
        // or sendSuccess with explicit 200/204
        $hasProperResponse = preg_match('/sendSuccess\s*\(\s*\[/', $content);

        if ($hasProperResponse) {
            $result['message'] = 'DELETE handler returns proper response';
        } else {
            // Check if it's using sendError properly for failures
            $usesSendError = preg_match('/sendError\s*\([\'"].*delete.*[\'"]\s*,\s*\d+\s*\)/i', $content);
            if ($usesSendError) {
                $result['message'] = 'DELETE handler uses proper error responses';
            } else {
                $result['status'] = 'REVIEW';
                $result['message'] = 'DELETE response handling needs review';
            }
        }

        $this->printCheckResult($result);
        return $result;
    }

    /**
     * Check 4: Handlers return 404 for not found
     */
    private function checkNotFound404($content, $filename)
    {
        $result = [
            'name' => $this->checks['notFound404']['name'],
            'status' => 'PASS',
            'message' => '',
            'details' => []
        ];

        // Look for 404 responses
        $has404Response = preg_match('/sendError\s*\([\'"][^"\']*not\s*found[^"\']*[\'"]\s*,\s*404\s*\)/i', $content);

        // Check if handler checks for entity existence
        $checksExistence = preg_match('/\$existing\s*=|->get\s*\(\s*\$id\s*\)|!empty\s*\(\s*\$/', $content);

        if ($checksExistence) {
            if ($has404Response) {
                $result['message'] = 'Returns 404 for not found cases';
            } else {
                $result['status'] = 'REVIEW';
                $result['message'] = 'May not return 404 for not found cases';
                $result['details'][] = 'sendError with 404 not found for "not found" scenarios';
            }
        } else {
            // Handler might not need existence checks (like list endpoints)
            $result['message'] = 'Entity existence check not required or 404 handled';
        }

        $this->printCheckResult($result);
        return $result;
    }

    /**
     * Check 5: Handlers return 400 for bad request
     */
    private function checkBadRequest400($content, $filename)
    {
        $result = [
            'name' => $this->checks['badRequest400']['name'],
            'status' => 'PASS',
            'message' => '',
            'details' => []
        ];

        // Look for 400 responses for validation errors
        $has400Response = preg_match('/sendError\s*\([^)]*,\s*400\s*\)/', $content);

        // Check for validation patterns
        $hasValidation = preg_match('/empty\s*\(\s*\$input\[|Missing\s+required|required\s+field/i', $content);

        if ($hasValidation) {
            if ($has400Response) {
                $result['message'] = 'Returns 400 for validation errors';
            } else {
                $result['status'] = 'REVIEW';
                $result['message'] = 'Validation exists but 400 response not found';
            }
        } else {
            // Check if it has any input handling
            $hasInputHandling = preg_match('/\$input\s*=|\$_GET\[|\$_POST\[/', $content);
            if ($hasInputHandling && !$has400Response) {
                $result['status'] = 'REVIEW';
                $result['message'] = 'Input handling without 400 validation responses';
            } else {
                $result['message'] = 'Validation handling verified';
            }
        }

        $this->printCheckResult($result);
        return $result;
    }

    /**
     * Check 6: Handlers use sendError() for error responses
     */
    private function checkSendErrorUsage($content, $filename)
    {
        $result = [
            'name' => $this->checks['sendErrorUsage']['name'],
            'status' => 'PASS',
            'message' => '',
            'details' => []
        ];

        // Look for sendError usage
        $usesSendError = preg_match_all('/\$this->sendError\s*\(/', $content, $matches);

        // Check for improper error handling patterns
        $hasDirectEcho = preg_match('/echo\s+[\'"].*error.*[\'"]/i', $content);
        $hasDirectDie = preg_match('/die\s*\(\s*[\'"]/', $content);
        $hasDirectExit = preg_match('/exit\s*\(\s*[\'"]/', $content);

        $issues = [];
        if ($hasDirectEcho) {
            $issues[] = 'Direct echo for error output';
        }
        if ($hasDirectDie) {
            $issues[] = 'Direct die() calls';
        }
        if ($hasDirectExit) {
            $issues[] = 'Direct exit() with string';
        }

        if ($usesSendError > 0 && empty($issues)) {
            $result['message'] = "Uses sendError() for errors ({$usesSendError} occurrences)";
        } elseif (!empty($issues)) {
            $result['status'] = 'REVIEW';
            $result['message'] = 'May have inconsistent error handling';
            $result['details'] = $issues;
        } else {
            // Check if it uses ApiHelpers trait
            $usesApiHelpers = preg_match('/use\s+ApiHelpers/', $content);
            if ($usesApiHelpers) {
                $result['message'] = 'Uses ApiHelpers trait (sendError available)';
            } else {
                $result['status'] = 'REVIEW';
                $result['message'] = 'Does not use ApiHelpers trait';
            }
        }

        $this->printCheckResult($result);
        return $result;
    }

    /**
     * Print check result
     */
    private function printCheckResult($result)
    {
        $status = $result['status'] === 'PASS'
            ? Colors::green('[PASS]')
            : Colors::yellow('[REVIEW]');

        echo "  {$status} {$result['name']}: {$result['message']}\n";

        if ($this->verbose && !empty($result['details'])) {
            foreach ($result['details'] as $detail) {
                echo "         " . Colors::yellow("-> " . $detail) . "\n";
            }
        }
    }

    /**
     * Print final summary
     * @return int Exit code
     */
    private function printSummary()
    {
        echo Colors::bold("\n" . str_repeat("=", 70) . "\n");
        echo Colors::bold("  AUDIT SUMMARY\n");
        echo Colors::bold(str_repeat("=", 70) . "\n\n");

        echo Colors::cyan("Handlers Audited: ") . count($this->results) . "\n";
        echo Colors::green("Total Passes: ") . $this->totalPasses . "\n";
        echo Colors::yellow("Total Items for Review: ") . $this->totalIssues . "\n\n";

        // Per-handler summary
        echo Colors::bold("Per-Handler Summary:\n");
        echo str_repeat("-", 60) . "\n";

        foreach ($this->results as $result) {
            $status = $result['issues'] === 0
                ? Colors::green('[PASS]')
                : Colors::yellow('[REVIEW]');

            printf("  %-35s %s %d/%d checks passed\n",
                Colors::cyan($result['file']),
                $status,
                $result['passes'],
                count($result['checks'])
            );
        }

        echo "\n" . str_repeat("-", 60) . "\n\n";

        // Overall status
        if ($this->totalIssues === 0) {
            echo Colors::green(Colors::bold("STATUS: ALL CHECKS PASSED\n"));
            echo "All handlers implement proper error handling patterns.\n\n";
            return 0;
        } else {
            echo Colors::yellow(Colors::bold("STATUS: {$this->totalIssues} ITEMS NEED REVIEW\n"));
            echo "Some handlers may need error handling improvements.\n";
            echo "Run with -v flag for detailed information.\n\n";
            return 1;
        }
    }
}

// Main execution
$verbose = in_array('-v', $argv) || in_array('--verbose', $argv);

// Determine handlers path
$scriptDir = dirname(__FILE__);
$handlersPath = realpath($scriptDir . '/../../modules/api/handlers');

if (!$handlersPath || !is_dir($handlersPath)) {
    // Try from repository root
    $handlersPath = realpath($scriptDir . '/../../../modules/api/handlers');
}

if (!$handlersPath || !is_dir($handlersPath)) {
    echo Colors::red("Error: Could not find handlers directory\n");
    echo "Expected at: modules/api/handlers/\n";
    exit(1);
}

$audit = new ErrorHandlingAudit($handlersPath, $verbose);
$exitCode = $audit->run();
exit($exitCode);
