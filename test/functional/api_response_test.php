#!/usr/bin/env php
<?php
/**
 * CATS
 * API Response Format Validation Script
 *
 * Verifies all REST API handlers return properly formatted JSON responses
 * following OpenCATS API standards.
 *
 * Usage: php api_response_test.php
 *
 * Checks performed:
 * 1. All handlers use sendSuccess() for success responses
 * 2. All handlers use sendError() for error responses
 * 3. List methods include pagination metadata (total, page, limit)
 * 4. Each handler has a format* method for response formatting
 * 5. Handlers use json_encode for responses
 *
 * Copyright (C) 2026 Space-O Technologies (https://www.spaceotechnologies.com/)
 *
 * @package    CATS
 * @subpackage Tests
 */

/**
 * API Response Format Validator
 *
 * Static analysis tool that inspects handler source code to verify
 * API response format compliance without requiring runtime execution.
 */
class ApiResponseValidator
{
    /** @var string Base path to handlers directory */
    private $handlersPath;

    /** @var array Test results storage */
    private $results = [];

    /** @var int Total issues count */
    private $totalIssues = 0;

    /** @var int Total passes count */
    private $totalPasses = 0;

    /** @var int Total warnings count */
    private $totalWarnings = 0;

    /**
     * Handler configuration with expected response fields
     * Maps handler names to their expected response fields
     */
    private $handlerExpectations = [
        'JobOrderHandler' => [
            'fields' => ['id', 'title', 'clientCorporation', 'status'],
            'formatterMethod' => 'formatJobOrder',
            'formatterClass' => 'EntityFormatter',
            'hasList' => true,
            'isUtility' => false,
            'description' => 'Job Orders API'
        ],
        'CandidateHandler' => [
            'fields' => ['id', 'firstName', 'lastName', 'email'],
            'formatterMethod' => 'formatCandidate',
            'formatterClass' => 'EntityFormatter',
            'hasList' => true,
            'isUtility' => false,
            'description' => 'Candidates API'
        ],
        'CompanyHandler' => [
            'fields' => ['id', 'name', 'address', 'phone'],
            'formatterMethod' => 'formatCompany',
            'formatterClass' => 'EntityFormatter',
            'hasList' => true,
            'isUtility' => false,
            'description' => 'Companies API'
        ],
        'ContactHandler' => [
            'fields' => ['id', 'firstName', 'lastName', 'clientCorporation'],
            'formatterMethod' => 'formatContact',
            'formatterClass' => 'EntityFormatter',
            'hasList' => true,
            'isUtility' => false,
            'description' => 'Contacts API'
        ],
        'TearsheetHandler' => [
            'fields' => ['id', 'name', 'description', 'jobOrders'],
            'formatterMethod' => 'formatTearsheet',
            'formatterClass' => 'EntityFormatter',
            'hasList' => true,
            'isUtility' => false,
            'description' => 'Tearsheets API'
        ],
        'JobSubmissionHandler' => [
            'fields' => ['id', 'candidate', 'jobOrder', 'status'],
            'formatterMethod' => 'formatSubmission',
            'formatterClass' => null, // Uses private method
            'hasList' => true,
            'isUtility' => false,
            'description' => 'Job Submissions API'
        ],
        'PlacementHandler' => [
            'fields' => ['id', 'candidate', 'jobOrder', 'salary'],
            'formatterMethod' => 'formatPlacement',
            'formatterClass' => null, // Uses private method
            'hasList' => true,
            'isUtility' => false,
            'description' => 'Placements API'
        ],
        'NoteHandler' => [
            'fields' => ['id', 'action', 'comments', 'dateAdded'],
            'formatterMethod' => 'formatNote',
            'formatterClass' => 'EntityFormatter',
            'hasList' => true,
            'isUtility' => false,
            'description' => 'Notes API'
        ],
        'AppointmentHandler' => [
            'fields' => ['id', 'title', 'startDate', 'endDate'],
            'formatterMethod' => 'formatAppointment',
            'formatterClass' => null, // Uses private method
            'hasList' => true,
            'isUtility' => false,
            'description' => 'Appointments API'
        ],
        'TaskHandler' => [
            'fields' => ['id', 'subject', 'priority', 'status'],
            'formatterMethod' => 'formatTask',
            'formatterClass' => null, // Uses private method
            'hasList' => true,
            'isUtility' => false,
            'description' => 'Tasks API'
        ],
        'SubscriptionHandler' => [
            'fields' => ['id', 'name', 'entityType', 'callbackUrl'],
            'formatterMethod' => 'formatSubscription',
            'formatterClass' => null, // Uses private method
            'hasList' => true,
            'isUtility' => false,
            'description' => 'Webhook Subscriptions API'
        ],
        // Utility/Infrastructure handlers (not standard CRUD)
        'OAuthHandler' => [
            'fields' => [],
            'formatterMethod' => null,
            'formatterClass' => null,
            'hasList' => false,
            'isUtility' => true,
            'description' => 'OAuth 2.0 Authentication (Utility)'
        ],
        'MetaHandler' => [
            'fields' => [],
            'formatterMethod' => null,
            'formatterClass' => null,
            'hasList' => false,
            'isUtility' => true,
            'description' => 'Entity Schema Discovery (Utility)'
        ],
        'MassUpdateHandler' => [
            'fields' => [],
            'formatterMethod' => null,
            'formatterClass' => null,
            'hasList' => false,
            'isUtility' => true,
            'description' => 'Bulk Update Operations (Utility)'
        ],
        'AssociationHandler' => [
            'fields' => [],
            'formatterMethod' => null,
            'formatterClass' => null,
            'hasList' => false,
            'isUtility' => true,
            'description' => 'Entity Associations (Utility)'
        ],
        'AttachmentHandler' => [
            'fields' => ['id', 'title', 'contentType'],
            'formatterMethod' => 'formatAttachment',
            'formatterClass' => 'EntityFormatter',
            'hasList' => true,
            'isUtility' => false,
            'description' => 'File Attachments API'
        ]
    ];

    /**
     * Constructor
     *
     * @param string $handlersPath Path to handlers directory
     */
    public function __construct($handlersPath)
    {
        $this->handlersPath = $handlersPath;
    }

    /**
     * Run all validation tests
     *
     * @return bool True if all tests pass
     */
    public function runTests()
    {
        $this->printHeader();

        // Get all handler files
        $handlerFiles = glob($this->handlersPath . '/*Handler.php');

        if (empty($handlerFiles)) {
            $this->printError("No handler files found in: {$this->handlersPath}");
            return false;
        }

        echo "Found " . count($handlerFiles) . " handler files\n\n";

        // Test each handler
        foreach ($handlerFiles as $handlerFile) {
            $handlerName = basename($handlerFile, '.php');
            $this->validateHandler($handlerFile, $handlerName);
        }

        // Print summary
        $this->printSummary();

        return ($this->totalIssues === 0);
    }

    /**
     * Validate a single handler file
     *
     * @param string $filePath Full path to handler file
     * @param string $handlerName Handler class name
     */
    private function validateHandler($filePath, $handlerName)
    {
        $content = file_get_contents($filePath);

        if ($content === false) {
            $this->recordResult($handlerName, 'File Read', 'FAIL', "Cannot read file: {$filePath}");
            return;
        }

        // Get expectations for this handler
        $expectations = isset($this->handlerExpectations[$handlerName])
            ? $this->handlerExpectations[$handlerName]
            : null;

        $description = $expectations ? $expectations['description'] : $handlerName;
        echo "=== {$handlerName} ({$description}) ===\n";

        // Run validation checks
        $this->checkApiHelpersTrait($content, $handlerName);
        $this->checkSendSuccessUsage($content, $handlerName);
        $this->checkSendErrorUsage($content, $handlerName);
        $this->checkJsonEncode($content, $handlerName);
        $this->checkFormatterMethod($content, $handlerName, $expectations);
        $this->checkPaginationMetadata($content, $handlerName, $expectations);
        $this->checkResponseFields($content, $handlerName, $expectations);
        $this->checkHttpStatusCodes($content, $handlerName);
        $this->checkCrudMethodCoverage($content, $handlerName);

        echo "\n";
    }

    /**
     * Check if handler uses ApiHelpers trait
     */
    private function checkApiHelpersTrait($content, $handlerName)
    {
        if (preg_match('/use\s+ApiHelpers\s*;/', $content)) {
            $this->recordResult($handlerName, 'ApiHelpers Trait', 'PASS', 'Uses ApiHelpers trait');
        } else {
            $this->recordResult($handlerName, 'ApiHelpers Trait', 'FAIL',
                'Missing ApiHelpers trait - required for sendSuccess/sendError methods');
        }
    }

    /**
     * Check sendSuccess() usage for success responses
     */
    private function checkSendSuccessUsage($content, $handlerName)
    {
        // Get expectations for this handler
        $expectations = isset($this->handlerExpectations[$handlerName])
            ? $this->handlerExpectations[$handlerName]
            : null;

        $isUtility = $expectations && !empty($expectations['isUtility']);

        $sendSuccessCalls = preg_match_all('/\$this->sendSuccess\s*\(/', $content, $matches);

        // Utility handlers have lower requirements
        $minRequired = $isUtility ? 1 : 2;

        if ($sendSuccessCalls >= $minRequired) {
            $this->recordResult($handlerName, 'sendSuccess Usage', 'PASS',
                "Uses sendSuccess() {$sendSuccessCalls} times for success responses");
        } elseif ($sendSuccessCalls === 1) {
            $this->recordResult($handlerName, 'sendSuccess Usage', 'WARN',
                'Only 1 sendSuccess() call found - expected more for CRUD operations');
        } else {
            $this->recordResult($handlerName, 'sendSuccess Usage', 'FAIL',
                'No sendSuccess() calls found - handler may not return proper JSON responses');
        }

        // Check for raw echo json_encode without sendSuccess
        $rawJsonEcho = preg_match_all('/echo\s+json_encode\s*\((?!.*sendSuccess)/', $content, $matches);
        if ($rawJsonEcho > 0) {
            $this->recordResult($handlerName, 'Raw JSON Output', 'WARN',
                "Found {$rawJsonEcho} raw echo json_encode calls - should use sendSuccess() instead");
        }
    }

    /**
     * Check sendError() usage for error responses
     */
    private function checkSendErrorUsage($content, $handlerName)
    {
        // Get expectations for this handler
        $expectations = isset($this->handlerExpectations[$handlerName])
            ? $this->handlerExpectations[$handlerName]
            : null;

        $isUtility = $expectations && !empty($expectations['isUtility']);

        $sendErrorCalls = preg_match_all('/\$this->sendError\s*\(/', $content, $matches);

        // Utility handlers have lower requirements
        $minRequired = $isUtility ? 1 : 3;

        if ($sendErrorCalls >= $minRequired) {
            $this->recordResult($handlerName, 'sendError Usage', 'PASS',
                "Uses sendError() {$sendErrorCalls} times for error handling");
        } elseif ($sendErrorCalls >= 1) {
            $this->recordResult($handlerName, 'sendError Usage', 'WARN',
                "Only {$sendErrorCalls} sendError() calls - may need more error handling");
        } else {
            $this->recordResult($handlerName, 'sendError Usage', 'FAIL',
                'No sendError() calls found - handler lacks proper error responses');
        }

        // Check for proper HTTP error codes
        $errorWith400 = preg_match_all('/sendError\s*\([^,]+,\s*400\)/', $content);
        $errorWith404 = preg_match_all('/sendError\s*\([^,]+,\s*404\)/', $content);
        $errorWith500 = preg_match_all('/sendError\s*\([^,]+,\s*500\)/', $content);
        $errorWith405 = preg_match_all('/sendError\s*\([^,]+,\s*405\)/', $content);

        $codesUsed = [];
        if ($errorWith400) $codesUsed[] = '400';
        if ($errorWith404) $codesUsed[] = '404';
        if ($errorWith500) $codesUsed[] = '500';
        if ($errorWith405) $codesUsed[] = '405';

        // Utility handlers need fewer error codes
        $minCodes = $isUtility ? 1 : 3;

        if (count($codesUsed) >= $minCodes) {
            $this->recordResult($handlerName, 'HTTP Error Codes', 'PASS',
                'Uses appropriate HTTP error codes: ' . implode(', ', $codesUsed));
        } elseif (count($codesUsed) >= 1) {
            $this->recordResult($handlerName, 'HTTP Error Codes', 'WARN',
                'Limited HTTP error codes: ' . implode(', ', $codesUsed));
        }
    }

    /**
     * Check for json_encode usage (handled by ApiHelpers)
     */
    private function checkJsonEncode($content, $handlerName)
    {
        // Check if ApiHelpers trait is used (which contains json_encode in sendSuccess/sendError)
        if (preg_match('/use\s+ApiHelpers\s*;/', $content)) {
            $this->recordResult($handlerName, 'JSON Encoding', 'PASS',
                'JSON encoding handled by ApiHelpers trait (sendSuccess/sendError use json_encode)');
        } else {
            // Check for direct json_encode
            if (preg_match('/json_encode\s*\(/', $content)) {
                $this->recordResult($handlerName, 'JSON Encoding', 'WARN',
                    'Uses direct json_encode - should use ApiHelpers trait instead');
            } else {
                $this->recordResult($handlerName, 'JSON Encoding', 'FAIL',
                    'No JSON encoding mechanism found');
            }
        }
    }

    /**
     * Check for formatter method existence
     */
    private function checkFormatterMethod($content, $handlerName, $expectations)
    {
        if (!$expectations) {
            $this->recordResult($handlerName, 'Formatter Method', 'WARN',
                'No formatter expectations defined - skipping formatter check');
            return;
        }

        // Utility handlers don't need formatters
        if (!empty($expectations['isUtility'])) {
            $this->recordResult($handlerName, 'Formatter Method', 'PASS',
                'Utility handler - custom response formatting OK');
            return;
        }

        $formatterMethod = $expectations['formatterMethod'];
        $formatterClass = $expectations['formatterClass'];

        if ($formatterClass) {
            // Check for static call to EntityFormatter
            $pattern = '/' . preg_quote($formatterClass, '/') . '::' . preg_quote($formatterMethod, '/') . '\s*\(/';
            if (preg_match($pattern, $content)) {
                $this->recordResult($handlerName, 'Formatter Method', 'PASS',
                    "Uses {$formatterClass}::{$formatterMethod}() for response formatting");
            } else {
                // Check if it has a private formatter method instead
                if (preg_match('/private\s+function\s+' . preg_quote($formatterMethod, '/') . '\s*\(/', $content)) {
                    $this->recordResult($handlerName, 'Formatter Method', 'PASS',
                        "Uses private {$formatterMethod}() method for response formatting");
                } else {
                    $this->recordResult($handlerName, 'Formatter Method', 'WARN',
                        "Expected {$formatterClass}::{$formatterMethod}() not found");
                }
            }
        } elseif ($formatterMethod) {
            // Check for private formatter method
            if (preg_match('/private\s+function\s+' . preg_quote($formatterMethod, '/') . '\s*\(/', $content)) {
                $this->recordResult($handlerName, 'Formatter Method', 'PASS',
                    "Has private {$formatterMethod}() method for response formatting");
            } elseif (preg_match('/function\s+' . preg_quote($formatterMethod, '/') . '\s*\(/', $content)) {
                $this->recordResult($handlerName, 'Formatter Method', 'PASS',
                    "Has {$formatterMethod}() method for response formatting");
            } else {
                $this->recordResult($handlerName, 'Formatter Method', 'FAIL',
                    "Missing {$formatterMethod}() formatter method");
            }
        } else {
            $this->recordResult($handlerName, 'Formatter Method', 'PASS',
                'No specific formatter required for this handler type');
        }
    }

    /**
     * Check for pagination metadata in list responses
     */
    private function checkPaginationMetadata($content, $handlerName, $expectations)
    {
        if (!$expectations || !$expectations['hasList']) {
            return;
        }

        // Check for sendPaginatedResponse usage
        if (preg_match('/\$this->sendPaginatedResponse\s*\(/', $content)) {
            $this->recordResult($handlerName, 'Pagination Metadata', 'PASS',
                'Uses sendPaginatedResponse() which includes total, page, limit metadata');
            return;
        }

        // Check for manual pagination metadata
        $hasTotalKey = preg_match('/[\'"]total[\'"]\s*=>/', $content);
        $hasPageKey = preg_match('/[\'"]page[\'"]\s*=>/', $content);
        $hasLimitKey = preg_match('/[\'"]limit[\'"]\s*=>/', $content);
        $hasDataKey = preg_match('/[\'"]data[\'"]\s*=>/', $content);

        $metadataFound = [];
        if ($hasTotalKey) $metadataFound[] = 'total';
        if ($hasPageKey) $metadataFound[] = 'page';
        if ($hasLimitKey) $metadataFound[] = 'limit';
        if ($hasDataKey) $metadataFound[] = 'data';

        if (count($metadataFound) >= 4) {
            $this->recordResult($handlerName, 'Pagination Metadata', 'PASS',
                'List responses include: ' . implode(', ', $metadataFound));
        } elseif (count($metadataFound) >= 2) {
            $this->recordResult($handlerName, 'Pagination Metadata', 'WARN',
                'Partial pagination metadata: ' . implode(', ', $metadataFound));
        } else {
            $this->recordResult($handlerName, 'Pagination Metadata', 'FAIL',
                'Missing pagination metadata (total, page, limit, data)');
        }
    }

    /**
     * Check for expected response fields in formatter
     * For handlers using EntityFormatter, we check the formatter file
     * For handlers with private formatter methods, we check the handler itself
     */
    private function checkResponseFields($content, $handlerName, $expectations)
    {
        if (!$expectations) {
            return;
        }

        $expectedFields = $expectations['fields'];
        $foundFields = [];
        $missingFields = [];

        // Determine which content to search based on formatter class
        $searchContent = $content;
        $searchSource = 'handler';

        // If handler uses EntityFormatter, load that file instead
        if ($expectations['formatterClass'] === 'EntityFormatter') {
            $formatterPath = dirname($this->handlersPath) . '/formatters/EntityFormatter.php';
            if (file_exists($formatterPath)) {
                $searchContent = file_get_contents($formatterPath);
                $searchSource = 'EntityFormatter';
            }
        }

        foreach ($expectedFields as $field) {
            // Check for field in array keys
            $pattern = '/[\'"]' . preg_quote($field, '/') . '[\'"]\s*=>/';
            if (preg_match($pattern, $searchContent)) {
                $foundFields[] = $field;
            } else {
                $missingFields[] = $field;
            }
        }

        if (empty($missingFields)) {
            $this->recordResult($handlerName, 'Response Fields', 'PASS',
                "All expected fields present in {$searchSource}: " . implode(', ', $foundFields));
        } elseif (count($foundFields) >= count($expectedFields) / 2) {
            $this->recordResult($handlerName, 'Response Fields', 'WARN',
                "Some fields missing in {$searchSource}: " . implode(', ', $missingFields));
        } else {
            $this->recordResult($handlerName, 'Response Fields', 'FAIL',
                "Many expected fields missing in {$searchSource}: " . implode(', ', $missingFields));
        }
    }

    /**
     * Check HTTP status code usage
     */
    private function checkHttpStatusCodes($content, $handlerName)
    {
        // Check for 201 on create
        $has201 = preg_match('/sendSuccess\s*\([^,]+,\s*201\)/', $content);

        // Check for proper status code method names
        $hasCreate = preg_match('/function\s+handle(?:Post|Create)/', $content);

        if ($has201 && $hasCreate) {
            $this->recordResult($handlerName, 'HTTP 201 on Create', 'PASS',
                'Returns HTTP 201 for successful create operations');
        } elseif ($hasCreate && !$has201) {
            $this->recordResult($handlerName, 'HTTP 201 on Create', 'WARN',
                'Has create method but may not return HTTP 201');
        }
    }

    /**
     * Check CRUD method coverage
     */
    private function checkCrudMethodCoverage($content, $handlerName)
    {
        // Get expectations for this handler
        $expectations = isset($this->handlerExpectations[$handlerName])
            ? $this->handlerExpectations[$handlerName]
            : null;

        $isUtility = $expectations && !empty($expectations['isUtility']);

        // Extended patterns to detect HTTP methods (including direct REQUEST_METHOD checks)
        $crudMethods = [
            'GET' => preg_match('/case\s+[\'"]GET[\'"]\s*:/', $content) ||
                     preg_match('/handleGet\s*\(/', $content) ||
                     preg_match('/REQUEST_METHOD.*[\'"]GET[\'"]/', $content) ||
                     preg_match('/[\'"]GET[\'"].*REQUEST_METHOD/', $content),
            'POST' => preg_match('/case\s+[\'"]POST[\'"]\s*:/', $content) ||
                      preg_match('/handlePost\s*\(/', $content) ||
                      preg_match('/REQUEST_METHOD.*[\'"]POST[\'"]/', $content) ||
                      preg_match('/[\'"]POST[\'"].*REQUEST_METHOD/', $content),
            'PUT' => preg_match('/case\s+[\'"]PUT[\'"]\s*:/', $content) ||
                     preg_match('/handlePut\s*\(/', $content) ||
                     preg_match('/REQUEST_METHOD.*[\'"]PUT[\'"]/', $content) ||
                     preg_match('/[\'"]PUT[\'"].*REQUEST_METHOD/', $content),
            'DELETE' => preg_match('/case\s+[\'"]DELETE[\'"]\s*:/', $content) ||
                        preg_match('/handleDelete\s*\(/', $content) ||
                        preg_match('/REQUEST_METHOD.*[\'"]DELETE[\'"]/', $content) ||
                        preg_match('/[\'"]DELETE[\'"].*REQUEST_METHOD/', $content)
        ];

        $supported = array_keys(array_filter($crudMethods));

        if (count($supported) === 4) {
            $this->recordResult($handlerName, 'CRUD Coverage', 'PASS',
                'Full CRUD support: ' . implode(', ', $supported));
        } elseif (count($supported) >= 2) {
            // Utility handlers with partial CRUD are acceptable
            if ($isUtility) {
                $this->recordResult($handlerName, 'CRUD Coverage', 'PASS',
                    'Utility handler HTTP methods: ' . implode(', ', $supported));
            } else {
                $this->recordResult($handlerName, 'CRUD Coverage', 'WARN',
                    'Partial CRUD support: ' . implode(', ', $supported));
            }
        } elseif (count($supported) >= 1) {
            // Utility handlers with single method are acceptable
            if ($isUtility) {
                $this->recordResult($handlerName, 'CRUD Coverage', 'PASS',
                    'Utility handler HTTP method: ' . implode(', ', $supported));
            } else {
                $this->recordResult($handlerName, 'CRUD Coverage', 'WARN',
                    'Limited HTTP methods: ' . implode(', ', $supported));
            }
        } else {
            // No HTTP methods detected - check if it's a utility that might work differently
            if ($isUtility) {
                // Check for sendSuccess which indicates at least some response handling
                if (preg_match('/sendSuccess\s*\(/', $content)) {
                    $this->recordResult($handlerName, 'CRUD Coverage', 'PASS',
                        'Utility handler with response handling (non-standard HTTP routing)');
                } else {
                    $this->recordResult($handlerName, 'CRUD Coverage', 'FAIL',
                        'No HTTP methods detected');
                }
            } else {
                $this->recordResult($handlerName, 'CRUD Coverage', 'FAIL',
                    'No HTTP methods detected');
            }
        }
    }

    /**
     * Record a test result
     */
    private function recordResult($handler, $check, $status, $message)
    {
        $this->results[] = [
            'handler' => $handler,
            'check' => $check,
            'status' => $status,
            'message' => $message
        ];

        // Print result with color coding
        $statusTag = "[{$status}]";

        switch ($status) {
            case 'PASS':
                $this->totalPasses++;
                echo "  {$statusTag} {$check}: {$message}\n";
                break;
            case 'WARN':
                $this->totalWarnings++;
                echo "  {$statusTag} {$check}: {$message}\n";
                break;
            case 'FAIL':
                $this->totalIssues++;
                echo "  {$statusTag} {$check}: {$message}\n";
                break;
        }
    }

    /**
     * Print header
     */
    private function printHeader()
    {
        echo "================================================================================\n";
        echo "                    OpenCATS API Response Format Validator                      \n";
        echo "================================================================================\n";
        echo "Date: " . date('Y-m-d H:i:s') . "\n";
        echo "Handlers Path: {$this->handlersPath}\n";
        echo "--------------------------------------------------------------------------------\n\n";
    }

    /**
     * Print summary
     */
    private function printSummary()
    {
        $total = $this->totalPasses + $this->totalWarnings + $this->totalIssues;

        echo "================================================================================\n";
        echo "                              VALIDATION SUMMARY                                \n";
        echo "================================================================================\n\n";

        echo "Results by Handler:\n";
        echo "-------------------\n";

        // Group results by handler
        $byHandler = [];
        foreach ($this->results as $result) {
            $handler = $result['handler'];
            if (!isset($byHandler[$handler])) {
                $byHandler[$handler] = ['pass' => 0, 'warn' => 0, 'fail' => 0];
            }
            $byHandler[$handler][strtolower($result['status'])]++;
        }

        foreach ($byHandler as $handler => $counts) {
            $status = ($counts['fail'] > 0) ? 'FAIL' :
                      (($counts['warn'] > 0) ? 'WARN' : 'PASS');
            echo sprintf("  %-30s [%s] Pass: %d, Warn: %d, Fail: %d\n",
                $handler, $status, $counts['pass'], $counts['warn'], $counts['fail']);
        }

        echo "\n--------------------------------------------------------------------------------\n";
        echo "Overall Results:\n";
        echo "----------------\n";
        echo "  Total Checks:    {$total}\n";
        echo "  [PASS]:          {$this->totalPasses}\n";
        echo "  [WARN]:          {$this->totalWarnings}\n";
        echo "  [FAIL]:          {$this->totalIssues}\n";
        echo "--------------------------------------------------------------------------------\n";

        // Overall status
        if ($this->totalIssues === 0 && $this->totalWarnings === 0) {
            echo "\n[PASS] All API response format checks passed!\n";
        } elseif ($this->totalIssues === 0) {
            echo "\n[WARN] Passed with {$this->totalWarnings} warning(s) - review recommended\n";
        } else {
            echo "\n[FAIL] {$this->totalIssues} issue(s) require attention\n";
        }

        echo "================================================================================\n";
    }

    /**
     * Print error message
     */
    private function printError($message)
    {
        echo "[ERROR] {$message}\n";
    }

    /**
     * Get test results
     *
     * @return array Test results
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Get issue count
     *
     * @return int Number of issues found
     */
    public function getIssueCount()
    {
        return $this->totalIssues;
    }

    /**
     * Get warning count
     *
     * @return int Number of warnings found
     */
    public function getWarningCount()
    {
        return $this->totalWarnings;
    }
}

/**
 * EntityFormatter Validator
 *
 * Validates the EntityFormatter class separately
 */
class EntityFormatterValidator
{
    private $formatterPath;
    private $results = [];
    private $issues = 0;
    private $warnings = 0;
    private $passes = 0;

    /**
     * Expected formatter methods and their key fields
     */
    private $expectedMethods = [
        'formatJobOrder' => ['id', 'title', 'status', 'clientCorporation'],
        'formatCandidate' => ['id', 'firstName', 'lastName', 'email'],
        'formatCompany' => ['id', 'name', 'address'],
        'formatContact' => ['id', 'firstName', 'lastName', 'clientCorporation'],
        'formatTearsheet' => ['id', 'name', 'description'],
        'formatPlacement' => ['id', 'candidate', 'jobOrder', 'salary'],
        'formatNote' => ['id', 'action', 'comments'],
        'formatAppointment' => ['id', 'title', 'startDate', 'endDate'],
        'formatTask' => ['id', 'subject', 'priority', 'status'],
        'formatAttachment' => ['id', 'title', 'contentType']
    ];

    public function __construct($formatterPath)
    {
        $this->formatterPath = $formatterPath;
    }

    public function validate()
    {
        echo "\n=== EntityFormatter Validation ===\n";

        if (!file_exists($this->formatterPath)) {
            echo "  [FAIL] EntityFormatter not found at: {$this->formatterPath}\n";
            $this->issues++;
            return false;
        }

        $content = file_get_contents($this->formatterPath);

        // Check for each expected method
        foreach ($this->expectedMethods as $method => $fields) {
            $this->validateMethod($content, $method, $fields);
        }

        // Check for static methods
        $staticCount = preg_match_all('/public\s+static\s+function/', $content);
        if ($staticCount >= count($this->expectedMethods) * 0.8) {
            echo "  [PASS] Most formatter methods are static ({$staticCount} found)\n";
            $this->passes++;
        } else {
            echo "  [WARN] Expected more static methods (found {$staticCount})\n";
            $this->warnings++;
        }

        // Check for consistent id field handling
        $intvalIdCount = preg_match_all('/[\'"]id[\'"]\s*=>\s*intval\s*\(/', $content);
        if ($intvalIdCount >= 5) {
            echo "  [PASS] ID fields properly cast to int ({$intvalIdCount} instances)\n";
            $this->passes++;
        } else {
            echo "  [WARN] Some ID fields may not be cast to int\n";
            $this->warnings++;
        }

        return $this->issues === 0;
    }

    private function validateMethod($content, $methodName, $expectedFields)
    {
        // Check if method exists
        $pattern = '/function\s+' . preg_quote($methodName, '/') . '\s*\(/';
        if (!preg_match($pattern, $content)) {
            echo "  [FAIL] Missing method: {$methodName}\n";
            $this->issues++;
            return;
        }

        // Extract method body (simplified)
        $methodPattern = '/function\s+' . preg_quote($methodName, '/') . '\s*\([^)]*\)\s*\{/';
        if (preg_match($methodPattern, $content)) {
            echo "  [PASS] Method exists: {$methodName}\n";
            $this->passes++;
        }
    }

    public function getIssueCount()
    {
        return $this->issues;
    }

    public function getWarningCount()
    {
        return $this->warnings;
    }
}

/**
 * ApiHelpers Trait Validator
 *
 * Validates the ApiHelpers trait
 */
class ApiHelpersValidator
{
    private $traitPath;
    private $issues = 0;
    private $warnings = 0;
    private $passes = 0;

    public function __construct($traitPath)
    {
        $this->traitPath = $traitPath;
    }

    public function validate()
    {
        echo "\n=== ApiHelpers Trait Validation ===\n";

        if (!file_exists($this->traitPath)) {
            echo "  [FAIL] ApiHelpers trait not found at: {$this->traitPath}\n";
            $this->issues++;
            return false;
        }

        $content = file_get_contents($this->traitPath);

        // Check for required methods
        $requiredMethods = [
            'sendSuccess' => 'Success response method',
            'sendError' => 'Error response method',
            'getRequestBody' => 'Request body parser',
            'getPaginationParams' => 'Pagination parameter handler',
            'sendPaginatedResponse' => 'Paginated response helper'
        ];

        foreach ($requiredMethods as $method => $description) {
            if (preg_match('/function\s+' . preg_quote($method, '/') . '\s*\(/', $content)) {
                echo "  [PASS] Has {$method}(): {$description}\n";
                $this->passes++;
            } else {
                echo "  [FAIL] Missing {$method}(): {$description}\n";
                $this->issues++;
            }
        }

        // Check sendSuccess has json_encode
        if (preg_match('/function\s+sendSuccess.*json_encode/s', $content)) {
            echo "  [PASS] sendSuccess() uses json_encode for JSON output\n";
            $this->passes++;
        } else {
            echo "  [WARN] sendSuccess() may not use json_encode properly\n";
            $this->warnings++;
        }

        // Check sendError has json_encode
        if (preg_match('/function\s+sendError.*json_encode/s', $content)) {
            echo "  [PASS] sendError() uses json_encode for JSON output\n";
            $this->passes++;
        } else {
            echo "  [WARN] sendError() may not use json_encode properly\n";
            $this->warnings++;
        }

        // Check for http_response_code usage
        if (preg_match('/http_response_code\s*\(/', $content)) {
            echo "  [PASS] Uses http_response_code() for HTTP status\n";
            $this->passes++;
        } else {
            echo "  [WARN] May not set HTTP response codes properly\n";
            $this->warnings++;
        }

        // Check pagination response structure
        if (preg_match('/[\'"]total[\'"]\s*=>.*[\'"]page[\'"]\s*=>.*[\'"]limit[\'"]\s*=>.*[\'"]data[\'"]\s*=>/s', $content)) {
            echo "  [PASS] Paginated response includes standard metadata (total, page, limit, data)\n";
            $this->passes++;
        } else {
            echo "  [WARN] Paginated response may have non-standard structure\n";
            $this->warnings++;
        }

        return $this->issues === 0;
    }

    public function getIssueCount()
    {
        return $this->issues;
    }

    public function getWarningCount()
    {
        return $this->warnings;
    }
}

// =============================================================================
// Main Execution
// =============================================================================

// Determine paths
$scriptDir = dirname(__FILE__);
$basePath = realpath($scriptDir . '/../../');
$handlersPath = $basePath . '/modules/api/handlers';
$formatterPath = $basePath . '/modules/api/formatters/EntityFormatter.php';
$traitPath = $basePath . '/modules/api/traits/ApiHelpers.php';

// Check if paths exist
if (!is_dir($handlersPath)) {
    echo "[ERROR] Handlers directory not found: {$handlersPath}\n";
    echo "Please ensure you're running this script from the OpenCATS test directory.\n";
    exit(1);
}

// Run validators
$validator = new ApiResponseValidator($handlersPath);
$handlersPassed = $validator->runTests();

$formatterValidator = new EntityFormatterValidator($formatterPath);
$formatterPassed = $formatterValidator->validate();

$helpersValidator = new ApiHelpersValidator($traitPath);
$helpersPassed = $helpersValidator->validate();

// Final summary
echo "\n================================================================================\n";
echo "                           FINAL VALIDATION REPORT                              \n";
echo "================================================================================\n";

$totalIssues = $validator->getIssueCount() +
               $formatterValidator->getIssueCount() +
               $helpersValidator->getIssueCount();

$totalWarnings = $validator->getWarningCount() +
                 $formatterValidator->getWarningCount() +
                 $helpersValidator->getWarningCount();

echo "\nComponent Status:\n";
echo "  API Handlers:     " . ($handlersPassed ? "[PASS]" : "[FAIL]") . "\n";
echo "  EntityFormatter:  " . ($formatterPassed ? "[PASS]" : "[FAIL]") . "\n";
echo "  ApiHelpers Trait: " . ($helpersPassed ? "[PASS]" : "[FAIL]") . "\n";

echo "\nTotal Issues: {$totalIssues}\n";
echo "Total Warnings: {$totalWarnings}\n";

if ($totalIssues === 0 && $totalWarnings === 0) {
    echo "\n[PASS] All API response format validations passed!\n";
    exit(0);
} elseif ($totalIssues === 0) {
    echo "\n[WARN] Passed with warnings - review recommended\n";
    exit(0);
} else {
    echo "\n[FAIL] Validation failed - {$totalIssues} issues require attention\n";
    exit(1);
}
