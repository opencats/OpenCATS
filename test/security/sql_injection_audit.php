<?php
/**
 * CATS
 * SQL Injection Vulnerability Audit Script
 *
 * Scans library files for SQL injection vulnerabilities in the OpenCATS REST API.
 * Checks for unsafe patterns and reports issues by file, line number, and severity.
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * Copyright (C) 2026 Space-O Technologies (https://www.spaceotechnologies.com/)
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * @package    CATS
 * @subpackage Test
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: sql_injection_audit.php 2026-01-25 $
 */

/**
 * SQL Injection Vulnerability Audit Class
 *
 * Scans PHP files for common SQL injection vulnerability patterns.
 */
class SQLInjectionAudit
{
    /** @var array Files to scan */
    private $_filesToScan = array(
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
        'lib/ApiRateLimiter.php',
        'lib/ApiRequestLogger.php'
    );

    /** @var string Base path for file scanning */
    private $_basePath;

    /** @var array Collected issues */
    private $_issues = array();

    /** @var array Positive security indicators */
    private $_positiveIndicators = array(
        'makeQueryString' => 0,
        'makeQueryInteger' => 0,
        'makeQueryStringOrNULL' => 0,
        'makeQueryIntegerOrNULL' => 0,
        'makeQueryDouble' => 0,
        'intval' => 0,
        'escapeString' => 0
    );

    /** @var int Total files scanned */
    private $_filesScanned = 0;

    /** @var int Total lines scanned */
    private $_linesScanned = 0;

    /**
     * Constructor
     *
     * @param string $basePath Base path to the OpenCATS installation
     */
    public function __construct($basePath = null)
    {
        if ($basePath === null)
        {
            // Default to two directories up from this script
            $basePath = dirname(dirname(dirname(__FILE__)));
        }
        $this->_basePath = rtrim($basePath, '/');
    }

    /**
     * Run the full audit
     *
     * @return array Audit results
     */
    public function run()
    {
        $this->_issues = array();
        $this->_filesScanned = 0;
        $this->_linesScanned = 0;

        // Reset positive indicators
        foreach ($this->_positiveIndicators as $key => $value)
        {
            $this->_positiveIndicators[$key] = 0;
        }

        echo "==========================================================================\n";
        echo "SQL INJECTION VULNERABILITY AUDIT - OpenCATS REST API\n";
        echo "==========================================================================\n\n";
        echo "Base Path: " . $this->_basePath . "\n\n";

        foreach ($this->_filesToScan as $file)
        {
            $this->_scanFile($file);
        }

        $this->_printResults();

        return array(
            'issues'             => $this->_issues,
            'filesScanned'       => $this->_filesScanned,
            'linesScanned'       => $this->_linesScanned,
            'positiveIndicators' => $this->_positiveIndicators,
            'totalIssues'        => count($this->_issues)
        );
    }

    /**
     * Scan a single file for vulnerabilities
     *
     * @param string $relativePath Relative path to the file
     */
    private function _scanFile($relativePath)
    {
        $fullPath = $this->_basePath . '/' . $relativePath;

        if (!file_exists($fullPath))
        {
            echo "[SKIP] File not found: {$relativePath}\n";
            return;
        }

        echo "[SCAN] {$relativePath}\n";
        $this->_filesScanned++;

        $content = file_get_contents($fullPath);
        $lines = explode("\n", $content);
        $this->_linesScanned += count($lines);

        // Count positive indicators
        $this->_countPositiveIndicators($content);

        // Run vulnerability checks
        $this->_checkDirectSuperglobalInSQL($relativePath, $lines);
        $this->_checkSuperglobalInSprintf($relativePath, $lines);
        $this->_checkMissingMakeQueryString($relativePath, $lines);
        $this->_checkDynamicOrderBy($relativePath, $lines);
        $this->_checkLimitWithoutIntval($relativePath, $lines);
    }

    /**
     * Count positive security indicators in file content
     *
     * @param string $content File content
     */
    private function _countPositiveIndicators($content)
    {
        foreach (array_keys($this->_positiveIndicators) as $indicator)
        {
            $count = preg_match_all('/' . preg_quote($indicator, '/') . '\s*\(/', $content, $matches);
            $this->_positiveIndicators[$indicator] += $count;
        }
    }

    /**
     * Pattern 1: Direct $_GET/$_POST/$_REQUEST in SQL strings (CRITICAL)
     *
     * Looks for superglobals directly concatenated or interpolated into SQL.
     *
     * @param string $file  File being scanned
     * @param array  $lines Array of file lines
     */
    private function _checkDirectSuperglobalInSQL($file, $lines)
    {
        $sqlKeywords = 'SELECT|INSERT|UPDATE|DELETE|FROM|WHERE|AND|OR|ORDER|LIMIT|JOIN|SET|INTO|VALUES';

        foreach ($lines as $lineNum => $line)
        {
            // Skip comments
            if ($this->_isCommentLine($line))
            {
                continue;
            }

            // Pattern: SQL keyword with direct superglobal
            // Matches things like: "SELECT * FROM table WHERE id = " . $_GET['id']
            // Or: "... WHERE id = {$_GET['id']}"
            // Or: "... WHERE id = $_POST[id]"
            if (preg_match('/(' . $sqlKeywords . ').*\$_(GET|POST|REQUEST|COOKIE)\s*\[/i', $line))
            {
                $this->_addIssue(
                    'CRITICAL',
                    $file,
                    $lineNum + 1,
                    'Direct superglobal in SQL string',
                    trim($line)
                );
            }

            // Pattern: Concatenation with superglobal near SQL
            if (preg_match('/\.\s*\$_(GET|POST|REQUEST|COOKIE)\s*\[/', $line) &&
                preg_match('/(' . $sqlKeywords . ')/i', $line))
            {
                // Avoid duplicate if already caught
                $alreadyCaught = false;
                foreach ($this->_issues as $issue)
                {
                    if ($issue['file'] === $file &&
                        $issue['line'] === $lineNum + 1 &&
                        $issue['severity'] === 'CRITICAL')
                    {
                        $alreadyCaught = true;
                        break;
                    }
                }
                if (!$alreadyCaught)
                {
                    $this->_addIssue(
                        'CRITICAL',
                        $file,
                        $lineNum + 1,
                        'Superglobal concatenated into SQL',
                        trim($line)
                    );
                }
            }
        }
    }

    /**
     * Pattern 2: Superglobal in sprintf SQL (CRITICAL)
     *
     * Looks for sprintf SQL queries where superglobals are passed directly.
     *
     * @param string $file  File being scanned
     * @param array  $lines Array of file lines
     */
    private function _checkSuperglobalInSprintf($file, $lines)
    {
        $inSprintf = false;
        $sprintfBuffer = '';
        $sprintfStartLine = 0;

        foreach ($lines as $lineNum => $line)
        {
            // Skip comments
            if ($this->_isCommentLine($line))
            {
                continue;
            }

            // Detect start of sprintf with SQL
            if (preg_match('/sprintf\s*\(\s*["\'].*(?:SELECT|INSERT|UPDATE|DELETE)/i', $line))
            {
                $inSprintf = true;
                $sprintfBuffer = $line;
                $sprintfStartLine = $lineNum + 1;
            }
            elseif ($inSprintf)
            {
                $sprintfBuffer .= ' ' . $line;
            }

            // Check if sprintf ends on this line
            if ($inSprintf && preg_match('/\);/', $line))
            {
                // Now check the complete sprintf for superglobals
                if (preg_match('/\$_(GET|POST|REQUEST|COOKIE)\s*\[/', $sprintfBuffer))
                {
                    // Check if it's properly escaped
                    if (!preg_match('/makeQuery(?:String|Integer|Double)\s*\(\s*\$_(GET|POST|REQUEST|COOKIE)/', $sprintfBuffer) &&
                        !preg_match('/intval\s*\(\s*\$_(GET|POST|REQUEST|COOKIE)/', $sprintfBuffer) &&
                        !preg_match('/escapeString\s*\(\s*\$_(GET|POST|REQUEST|COOKIE)/', $sprintfBuffer))
                    {
                        $this->_addIssue(
                            'CRITICAL',
                            $file,
                            $sprintfStartLine,
                            'Unescaped superglobal in sprintf SQL',
                            $this->_truncateSnippet($sprintfBuffer)
                        );
                    }
                }
                $inSprintf = false;
                $sprintfBuffer = '';
            }
        }
    }

    /**
     * Pattern 3: Missing makeQueryString for string values (WARNING)
     *
     * Looks for sprintf SQL with %s placeholders that use raw variables.
     *
     * @param string $file  File being scanned
     * @param array  $lines Array of file lines
     */
    private function _checkMissingMakeQueryString($file, $lines)
    {
        $inSprintf = false;
        $sprintfBuffer = '';
        $sprintfStartLine = 0;
        $braceCount = 0;

        foreach ($lines as $lineNum => $line)
        {
            // Skip comments
            if ($this->_isCommentLine($line))
            {
                continue;
            }

            // Detect start of sprintf
            if (preg_match('/sprintf\s*\(/', $line))
            {
                $inSprintf = true;
                $sprintfBuffer = $line;
                $sprintfStartLine = $lineNum + 1;
                $braceCount = substr_count($line, '(') - substr_count($line, ')');
            }
            elseif ($inSprintf)
            {
                $sprintfBuffer .= ' ' . $line;
                $braceCount += substr_count($line, '(') - substr_count($line, ')');
            }

            // Check if sprintf ends
            if ($inSprintf && $braceCount <= 0)
            {
                // Check if this is a SQL sprintf
                if (preg_match('/(?:SELECT|INSERT|UPDATE|DELETE|FROM|WHERE)/i', $sprintfBuffer))
                {
                    // Look for '%s' in sprintf format that isn't wrapped in makeQueryString
                    // This is a heuristic check - we count %s placeholders and compare
                    // to makeQueryString calls
                    $percentSCount = preg_match_all("/'%s'/", $sprintfBuffer, $matches);
                    $makeQueryStringCount = preg_match_all('/makeQueryString\s*\(/', $sprintfBuffer, $matches);

                    // Also check for raw '%s' without quotes (potential injection)
                    if (preg_match('/[^\'"]%s[^\'"]/', $sprintfBuffer) ||
                        preg_match('/"%s"/', $sprintfBuffer))
                    {
                        // Check if there's a variable being passed without makeQueryString
                        if (preg_match('/,\s*\$[a-zA-Z_][a-zA-Z0-9_]*\s*[,)]/', $sprintfBuffer))
                        {
                            // Exclude if all variables are wrapped in escaping functions
                            $variables = array();
                            preg_match_all('/,\s*(\$[a-zA-Z_][a-zA-Z0-9_]*)\s*[,)]/', $sprintfBuffer, $variables);

                            if (!empty($variables[1]))
                            {
                                foreach ($variables[1] as $var)
                                {
                                    // Check if this variable is not escaped
                                    $escapedPattern = '/(?:makeQuery(?:String|Integer|Double|StringOrNULL|IntegerOrNULL)|intval|escapeString)\s*\(\s*' . preg_quote($var, '/') . '/';
                                    if (!preg_match($escapedPattern, $sprintfBuffer))
                                    {
                                        // Check if it's used with %s (string placeholder)
                                        if (preg_match('/%s/', $sprintfBuffer))
                                        {
                                            $this->_addIssue(
                                                'WARNING',
                                                $file,
                                                $sprintfStartLine,
                                                'Possible missing makeQueryString for variable: ' . $var,
                                                $this->_truncateSnippet($sprintfBuffer)
                                            );
                                            break; // One warning per sprintf block
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $inSprintf = false;
                $sprintfBuffer = '';
            }
        }
    }

    /**
     * Pattern 4: Dynamic ORDER BY without whitelist (HIGH)
     *
     * ORDER BY clauses with user-controlled column names can allow information disclosure.
     *
     * @param string $file  File being scanned
     * @param array  $lines Array of file lines
     */
    private function _checkDynamicOrderBy($file, $lines)
    {
        foreach ($lines as $lineNum => $line)
        {
            // Skip comments
            if ($this->_isCommentLine($line))
            {
                continue;
            }

            // Look for ORDER BY with variable
            if (preg_match('/ORDER\s+BY\s+["\']?\s*\.\s*\$/i', $line) ||
                preg_match('/ORDER\s+BY\s+["\']?\s*\{\s*\$/i', $line) ||
                preg_match('/ORDER\s+BY\s+%s/i', $line))
            {
                $this->_addIssue(
                    'HIGH',
                    $file,
                    $lineNum + 1,
                    'Dynamic ORDER BY - verify whitelist validation',
                    trim($line)
                );
            }

            // Also check for direct superglobal in ORDER BY
            if (preg_match('/ORDER\s+BY.*\$_(GET|POST|REQUEST)/i', $line))
            {
                $this->_addIssue(
                    'CRITICAL',
                    $file,
                    $lineNum + 1,
                    'Superglobal in ORDER BY clause',
                    trim($line)
                );
            }
        }
    }

    /**
     * Pattern 5: LIMIT without intval validation (MEDIUM)
     *
     * LIMIT clauses should use integer casting to prevent injection.
     *
     * @param string $file  File being scanned
     * @param array  $lines Array of file lines
     */
    private function _checkLimitWithoutIntval($file, $lines)
    {
        foreach ($lines as $lineNum => $line)
        {
            // Skip comments
            if ($this->_isCommentLine($line))
            {
                continue;
            }

            // Look for LIMIT with %s instead of %d or %s with makeQueryInteger
            if (preg_match('/LIMIT\s+%s/i', $line))
            {
                $this->_addIssue(
                    'MEDIUM',
                    $file,
                    $lineNum + 1,
                    'LIMIT using %s instead of %d - verify integer validation',
                    trim($line)
                );
            }

            // Look for LIMIT with direct variable concatenation
            if (preg_match('/LIMIT\s+["\']?\s*\.\s*\$/i', $line))
            {
                // Check if it's wrapped in intval or makeQueryInteger
                if (!preg_match('/LIMIT\s+["\']?\s*\.\s*(?:intval|makeQueryInteger)\s*\(/', $line))
                {
                    $this->_addIssue(
                        'MEDIUM',
                        $file,
                        $lineNum + 1,
                        'LIMIT with variable concatenation - verify integer casting',
                        trim($line)
                    );
                }
            }

            // Direct superglobal in LIMIT
            if (preg_match('/LIMIT.*\$_(GET|POST|REQUEST)/i', $line))
            {
                if (!preg_match('/(?:intval|makeQueryInteger)\s*\(\s*\$_(GET|POST|REQUEST)/', $line))
                {
                    $this->_addIssue(
                        'HIGH',
                        $file,
                        $lineNum + 1,
                        'Superglobal in LIMIT clause without integer casting',
                        trim($line)
                    );
                }
            }
        }
    }

    /**
     * Check if a line is a comment
     *
     * @param string $line Line to check
     * @return bool True if line is a comment
     */
    private function _isCommentLine($line)
    {
        $trimmed = trim($line);
        return (
            strpos($trimmed, '//') === 0 ||
            strpos($trimmed, '#') === 0 ||
            strpos($trimmed, '*') === 0 ||
            strpos($trimmed, '/*') === 0
        );
    }

    /**
     * Add an issue to the collection
     *
     * @param string $severity Severity level (CRITICAL, HIGH, MEDIUM, WARNING)
     * @param string $file     File name
     * @param int    $line     Line number
     * @param string $message  Description of the issue
     * @param string $snippet  Code snippet
     */
    private function _addIssue($severity, $file, $line, $message, $snippet)
    {
        $this->_issues[] = array(
            'severity' => $severity,
            'file'     => $file,
            'line'     => $line,
            'message'  => $message,
            'snippet'  => $snippet
        );
    }

    /**
     * Truncate a code snippet for display
     *
     * @param string $snippet Code snippet
     * @param int    $maxLen  Maximum length
     * @return string Truncated snippet
     */
    private function _truncateSnippet($snippet, $maxLen = 100)
    {
        $snippet = preg_replace('/\s+/', ' ', trim($snippet));
        if (strlen($snippet) > $maxLen)
        {
            return substr($snippet, 0, $maxLen - 3) . '...';
        }
        return $snippet;
    }

    /**
     * Print the audit results
     */
    private function _printResults()
    {
        echo "\n";
        echo "==========================================================================\n";
        echo "AUDIT RESULTS\n";
        echo "==========================================================================\n\n";

        echo "Files Scanned: " . $this->_filesScanned . "\n";
        echo "Lines Scanned: " . $this->_linesScanned . "\n\n";

        // Print positive indicators
        echo "--------------------------------------------------------------------------\n";
        echo "POSITIVE SECURITY INDICATORS (Safe Escaping Functions Used)\n";
        echo "--------------------------------------------------------------------------\n";
        foreach ($this->_positiveIndicators as $indicator => $count)
        {
            printf("  %-30s %d occurrences\n", $indicator . '():', $count);
        }
        echo "\n";

        // Group issues by severity
        $bySeverity = array(
            'CRITICAL' => array(),
            'HIGH'     => array(),
            'MEDIUM'   => array(),
            'WARNING'  => array()
        );

        foreach ($this->_issues as $issue)
        {
            $bySeverity[$issue['severity']][] = $issue;
        }

        // Print issues by severity
        echo "--------------------------------------------------------------------------\n";
        echo "ISSUES FOUND\n";
        echo "--------------------------------------------------------------------------\n\n";

        $severityColors = array(
            'CRITICAL' => "\033[1;31m", // Bold Red
            'HIGH'     => "\033[0;31m", // Red
            'MEDIUM'   => "\033[0;33m", // Yellow
            'WARNING'  => "\033[0;36m"  // Cyan
        );
        $resetColor = "\033[0m";

        $totalIssues = 0;
        foreach ($bySeverity as $severity => $issues)
        {
            if (empty($issues))
            {
                continue;
            }

            $totalIssues += count($issues);
            $color = isset($severityColors[$severity]) ? $severityColors[$severity] : '';

            echo $color . "[$severity] " . count($issues) . " issue(s)" . $resetColor . "\n";
            echo str_repeat('-', 74) . "\n";

            foreach ($issues as $issue)
            {
                echo "  File: " . $issue['file'] . "\n";
                echo "  Line: " . $issue['line'] . "\n";
                echo "  Issue: " . $issue['message'] . "\n";
                echo "  Code: " . $issue['snippet'] . "\n";
                echo "\n";
            }
        }

        if ($totalIssues === 0)
        {
            echo "\033[0;32mNo SQL injection vulnerabilities detected!\033[0m\n";
        }

        echo "==========================================================================\n";
        echo "SUMMARY\n";
        echo "==========================================================================\n";
        printf("  CRITICAL: %d\n", count($bySeverity['CRITICAL']));
        printf("  HIGH:     %d\n", count($bySeverity['HIGH']));
        printf("  MEDIUM:   %d\n", count($bySeverity['MEDIUM']));
        printf("  WARNING:  %d\n", count($bySeverity['WARNING']));
        echo "--------------------------------------------------------------------------\n";
        printf("  TOTAL:    %d\n", $totalIssues);
        echo "==========================================================================\n\n";

        if ($totalIssues > 0)
        {
            echo "Audit completed with issues. Please review and fix the vulnerabilities above.\n\n";
        }
        else
        {
            echo "Audit completed successfully. No SQL injection vulnerabilities found.\n\n";
        }
    }

    /**
     * Get the count of issues by severity
     *
     * @param string $severity Severity level to count (or null for total)
     * @return int Count of issues
     */
    public function getIssueCount($severity = null)
    {
        if ($severity === null)
        {
            return count($this->_issues);
        }

        $count = 0;
        foreach ($this->_issues as $issue)
        {
            if ($issue['severity'] === $severity)
            {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Check if audit passed (no CRITICAL or HIGH issues)
     *
     * @return bool True if audit passed
     */
    public function passed()
    {
        return (
            $this->getIssueCount('CRITICAL') === 0 &&
            $this->getIssueCount('HIGH') === 0
        );
    }
}

// =============================================================================
// MAIN EXECUTION
// =============================================================================

// Determine base path (should be run from test/security/ directory or project root)
$scriptDir = dirname(__FILE__);
$basePath = null;

// Try to find the base path by looking for the lib directory
$possiblePaths = array(
    $scriptDir . '/../..',           // If run from test/security/
    $scriptDir,                       // If run from project root
    getcwd(),                         // Current working directory
    getcwd() . '/../..'              // Two levels up from cwd
);

foreach ($possiblePaths as $path)
{
    $testPath = realpath($path);
    if ($testPath && is_dir($testPath . '/lib'))
    {
        $basePath = $testPath;
        break;
    }
}

if ($basePath === null)
{
    echo "Error: Could not determine base path. Please run from project root or test/security/ directory.\n";
    exit(1);
}

// Run the audit
$audit = new SQLInjectionAudit($basePath);
$results = $audit->run();

// Exit with appropriate code
if (!$audit->passed())
{
    exit(1); // Failed - CRITICAL or HIGH issues found
}

if ($audit->getIssueCount() > 0)
{
    exit(2); // Warnings - MEDIUM or WARNING issues found
}

exit(0); // Passed - No issues found
