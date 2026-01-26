<?php
/**
 * Code Style Consistency Audit Script for OpenCATS REST API
 *
 * This script checks for code style consistency across all API-related files.
 *
 * Checks performed:
 * 1. Tabs vs spaces (should use spaces, flags tabs)
 * 2. Debugging code (var_dump, print_r, die(), exit() except exit(0) or exit(1))
 * 3. TODO/FIXME/XXX/HACK comments
 * 4. Public methods without PHPDoc
 * 5. Error suppression operator (@$, @file, @mysql, @preg)
 *
 * Usage: php code_style_audit.php [--verbose]
 *
 * Exit codes:
 *   0 - All checks passed
 *   1 - Style issues found
 *   2 - Script error (e.g., files not found)
 */

// Configuration
$baseDir = dirname(dirname(dirname(__FILE__)));
$verbose = in_array('--verbose', $argv) || in_array('-v', $argv);

// Files to check
$filesToCheck = [
    // API handlers
    'modules/api/handlers/OAuthHandler.php',
    'modules/api/handlers/AttachmentHandler.php',
    'modules/api/handlers/MetaHandler.php',
    'modules/api/handlers/MassUpdateHandler.php',
    'modules/api/handlers/TearsheetHandler.php',
    'modules/api/handlers/AssociationHandler.php',
    'modules/api/handlers/SubscriptionHandler.php',
    'modules/api/handlers/JobOrderHandler.php',
    'modules/api/handlers/CandidateHandler.php',
    'modules/api/handlers/CompanyHandler.php',
    'modules/api/handlers/ContactHandler.php',
    'modules/api/handlers/JobSubmissionHandler.php',
    'modules/api/handlers/PlacementHandler.php',
    'modules/api/handlers/NoteHandler.php',
    'modules/api/handlers/AppointmentHandler.php',
    'modules/api/handlers/TaskHandler.php',

    // API traits
    'modules/api/traits/ApiHelpers.php',
    'modules/api/traits/WebhookTrigger.php',

    // API formatters
    'modules/api/formatters/EntityFormatter.php',

    // Main API file
    'modules/api/ApiUI.php',

    // Library files
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
    'lib/ApiResponse.php',
    'lib/ApiRequestLogger.php',
    'lib/ApiConfig.php',
    'lib/ApiRateLimiter.php',
];

/**
 * Issue types with descriptions
 */
$issueTypes = [
    'TABS' => 'Tab characters found (use spaces)',
    'DEBUG' => 'Debugging code found',
    'TODO' => 'TODO/FIXME/XXX/HACK comment found',
    'PHPDOC' => 'Public method without PHPDoc',
    'SUPPRESS' => 'Error suppression operator (@) used',
];

/**
 * Check a file for style issues
 *
 * @param string $filePath Full path to the file
 * @return array Array of issues found
 */
function checkFile($filePath)
{
    $issues = [];

    if (!file_exists($filePath)) {
        return ['error' => "File not found: $filePath"];
    }

    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);

    // Track if we're inside a class for public method detection
    $inClass = false;
    $braceCount = 0;
    $lastDocBlock = null;
    $lastDocBlockLine = 0;

    foreach ($lines as $lineNum => $line) {
        $lineNumber = $lineNum + 1; // 1-indexed for human readability

        // Check 1: Tabs vs spaces
        if (preg_match('/^\t+/', $line)) {
            $issues[] = [
                'type' => 'TABS',
                'line' => $lineNumber,
                'message' => 'Line starts with tab character(s)',
            ];
        }

        // Also check for tabs in middle of line (but not in strings)
        if (preg_match('/\t/', $line) && !preg_match('/^\t+/', $line)) {
            // Simple check - if tab not at start, it might be in code
            // Skip if it looks like it's in a string literal
            if (!preg_match('/["\'][^"\']*\t[^"\']*["\']/', $line)) {
                $issues[] = [
                    'type' => 'TABS',
                    'line' => $lineNumber,
                    'message' => 'Tab character found in line',
                ];
            }
        }

        // Check 2: Debugging code
        // var_dump
        if (preg_match('/\bvar_dump\s*\(/', $line)) {
            $issues[] = [
                'type' => 'DEBUG',
                'line' => $lineNumber,
                'message' => 'var_dump() found',
            ];
        }

        // print_r
        if (preg_match('/\bprint_r\s*\(/', $line)) {
            $issues[] = [
                'type' => 'DEBUG',
                'line' => $lineNumber,
                'message' => 'print_r() found',
            ];
        }

        // die() - but not commented out
        if (preg_match('/(?<![\/\*])\s*\bdie\s*\(/', $line) && !preg_match('/^\s*(\/\/|#|\*)/', $line)) {
            $issues[] = [
                'type' => 'DEBUG',
                'line' => $lineNumber,
                'message' => 'die() found',
            ];
        }

        // exit() - except exit(0) or exit(1)
        if (preg_match('/\bexit\s*\(/', $line) && !preg_match('/^\s*(\/\/|#|\*)/', $line)) {
            // Allow exit(0) and exit(1)
            if (!preg_match('/\bexit\s*\(\s*[01]\s*\)/', $line)) {
                $issues[] = [
                    'type' => 'DEBUG',
                    'line' => $lineNumber,
                    'message' => 'exit() found (only exit(0) or exit(1) allowed)',
                ];
            }
        }

        // Check 3: TODO/FIXME/XXX/HACK comments
        if (preg_match('/\b(TODO|FIXME|XXX|HACK)\b/i', $line, $matches)) {
            $issues[] = [
                'type' => 'TODO',
                'line' => $lineNumber,
                'message' => strtoupper($matches[1]) . ' comment found',
            ];
        }

        // Check 5: Error suppression operator
        // Look for @$ @file @mysql @preg patterns
        if (preg_match('/@\$/', $line) && !preg_match('/^\s*\*/', $line)) {
            $issues[] = [
                'type' => 'SUPPRESS',
                'line' => $lineNumber,
                'message' => 'Error suppression @$ found',
            ];
        }

        if (preg_match('/@file_/', $line) || preg_match('/@file\s*\(/', $line)) {
            $issues[] = [
                'type' => 'SUPPRESS',
                'line' => $lineNumber,
                'message' => 'Error suppression @file* found',
            ];
        }

        if (preg_match('/@mysql/', $line)) {
            $issues[] = [
                'type' => 'SUPPRESS',
                'line' => $lineNumber,
                'message' => 'Error suppression @mysql* found',
            ];
        }

        if (preg_match('/@preg/', $line)) {
            $issues[] = [
                'type' => 'SUPPRESS',
                'line' => $lineNumber,
                'message' => 'Error suppression @preg* found',
            ];
        }

        // Track DocBlocks for public method check
        if (preg_match('/^\s*\/\*\*/', $line)) {
            $lastDocBlock = $lineNumber;
        }
        if (preg_match('/^\s*\*\//', $line) && $lastDocBlock !== null) {
            $lastDocBlockLine = $lineNumber;
        }

        // Check 4: Public methods without PHPDoc
        // Match public function declarations
        if (preg_match('/^\s*public\s+(static\s+)?function\s+(\w+)\s*\(/', $line, $matches)) {
            $methodName = $matches[2];

            // Check if there was a DocBlock ending on the previous line or within 2 lines
            $hasDocBlock = ($lastDocBlockLine >= $lineNumber - 3 && $lastDocBlockLine < $lineNumber);

            if (!$hasDocBlock) {
                $issues[] = [
                    'type' => 'PHPDOC',
                    'line' => $lineNumber,
                    'message' => "Public method '$methodName' lacks PHPDoc",
                ];
            }
        }
    }

    return $issues;
}

/**
 * Format issue output
 *
 * @param string $type Issue type
 * @param int $line Line number
 * @param string $message Issue message
 * @return string Formatted string
 */
function formatIssue($type, $line, $message)
{
    return sprintf("    [%s] Line %d: %s", $type, $line, $message);
}

// Main execution
echo "=======================================================\n";
echo "OpenCATS REST API - Code Style Consistency Audit\n";
echo "=======================================================\n\n";

$totalFiles = 0;
$filesWithIssues = 0;
$totalIssues = 0;
$issuesByType = [];
$missingFiles = [];

foreach ($filesToCheck as $relativePath) {
    $fullPath = $baseDir . '/' . $relativePath;

    if (!file_exists($fullPath)) {
        $missingFiles[] = $relativePath;
        continue;
    }

    $totalFiles++;
    $issues = checkFile($fullPath);

    if (isset($issues['error'])) {
        echo "[ERROR] $relativePath\n";
        echo "    " . $issues['error'] . "\n\n";
        continue;
    }

    $issueCount = count($issues);
    $totalIssues += $issueCount;

    if ($issueCount > 0) {
        $filesWithIssues++;
        echo "[STYLE] $relativePath ($issueCount issue" . ($issueCount > 1 ? 's' : '') . ")\n";

        if ($verbose) {
            foreach ($issues as $issue) {
                echo formatIssue($issue['type'], $issue['line'], $issue['message']) . "\n";

                // Track issues by type
                if (!isset($issuesByType[$issue['type']])) {
                    $issuesByType[$issue['type']] = 0;
                }
                $issuesByType[$issue['type']]++;
            }
        } else {
            // Group issues by type for non-verbose output
            $typeGroups = [];
            foreach ($issues as $issue) {
                if (!isset($typeGroups[$issue['type']])) {
                    $typeGroups[$issue['type']] = [];
                }
                $typeGroups[$issue['type']][] = $issue['line'];

                // Track issues by type
                if (!isset($issuesByType[$issue['type']])) {
                    $issuesByType[$issue['type']] = 0;
                }
                $issuesByType[$issue['type']]++;
            }

            foreach ($typeGroups as $type => $lines) {
                $lineList = implode(', ', array_slice($lines, 0, 5));
                if (count($lines) > 5) {
                    $lineList .= ' ...';
                }
                echo "    [$type] " . count($lines) . " occurrence(s) on lines: $lineList\n";
            }
        }
        echo "\n";
    } else {
        echo "[PASS]  $relativePath\n";
    }
}

// Summary
echo "\n=======================================================\n";
echo "SUMMARY\n";
echo "=======================================================\n";
echo "Files checked:      $totalFiles\n";
echo "Files with issues:  $filesWithIssues\n";
echo "Total issues:       $totalIssues\n";

if (count($missingFiles) > 0) {
    echo "\nMissing files (" . count($missingFiles) . "):\n";
    foreach ($missingFiles as $file) {
        echo "  - $file\n";
    }
}

if (count($issuesByType) > 0) {
    echo "\nIssues by type:\n";
    foreach ($issuesByType as $type => $count) {
        echo "  [$type] $count - " . $issueTypes[$type] . "\n";
    }
}

echo "\n";

if ($totalIssues > 0) {
    echo "STATUS: STYLE ISSUES FOUND\n";
    echo "Run with --verbose for detailed issue locations.\n";
    exit(1);
} elseif (count($missingFiles) > 0) {
    echo "STATUS: SOME FILES MISSING\n";
    exit(2);
} else {
    echo "STATUS: ALL CHECKS PASSED\n";
    exit(0);
}
