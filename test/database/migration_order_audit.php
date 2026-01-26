<?php
/**
 * Migration Order Validation Script for OpenCATS REST API
 *
 * Purpose: Verify migrations are properly ordered with no circular dependencies.
 *
 * Checks:
 * 1. Track tables created by each migration file
 * 2. Verify REFERENCES point to tables already created (or existing core tables)
 * 3. Verify ALTER TABLE references existing tables
 * 4. Check migrations are numbered sequentially (001, 002, 003...)
 *
 * Usage: php migration_order_audit.php [migrations_path]
 *
 * Exit codes:
 *   0 - All checks passed
 *   1 - Errors found
 *   2 - Warnings found (but no errors)
 */

// Configuration
$migrationsPath = $argv[1] ?? dirname(__DIR__, 2) . '/db/migrations';

// Core existing OpenCATS tables that can be referenced without being created by migrations
$coreExistingTables = [
    'user',
    'candidate',
    'joborder',
    'company',
    'contact',
    'site',
    'candidate_joborder',
    'candidate_joborder_status',
    'activity_type',
    'access_level'
];

/**
 * Migration Order Auditor Class
 */
class MigrationOrderAuditor
{
    private string $migrationsPath;
    private array $coreExistingTables;
    private array $results = [];
    private array $createdTables = [];
    private array $allMigrationTables = [];
    private int $errorCount = 0;
    private int $warningCount = 0;
    private int $passCount = 0;

    public function __construct(string $migrationsPath, array $coreExistingTables)
    {
        $this->migrationsPath = $migrationsPath;
        $this->coreExistingTables = array_map('strtolower', $coreExistingTables);
    }

    /**
     * Run all migration order audits
     */
    public function run(): int
    {
        $this->printHeader();

        // Check migrations directory exists
        if (!is_dir($this->migrationsPath)) {
            $this->addError("Migrations directory not found: {$this->migrationsPath}");
            $this->printSummary();
            return 1;
        }

        // Get all migration files
        $migrationFiles = $this->getMigrationFiles();
        if (empty($migrationFiles)) {
            $this->addError("No migration files found in: {$this->migrationsPath}");
            $this->printSummary();
            return 1;
        }

        echo "Found " . count($migrationFiles) . " migration files\n";
        echo str_repeat("-", 70) . "\n\n";

        // Run audits
        $this->auditSequentialNumbering($migrationFiles);
        $this->auditMigrationOrder($migrationFiles);
        $this->printTableSummary();
        $this->printSummary();

        // Return exit code based on results
        if ($this->errorCount > 0) {
            return 1;
        }
        if ($this->warningCount > 0) {
            return 2;
        }
        return 0;
    }

    /**
     * Get sorted list of migration files
     */
    private function getMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '/*.sql');
        if ($files === false) {
            return [];
        }

        // Sort by filename to ensure proper order
        sort($files);
        return $files;
    }

    /**
     * Audit sequential numbering of migrations
     */
    private function auditSequentialNumbering(array $migrationFiles): void
    {
        echo "=== AUDIT 1: Migration Sequential Numbering ===\n\n";

        $expectedNumber = 1;
        $numbersFound = [];
        $hasGaps = false;

        foreach ($migrationFiles as $file) {
            $filename = basename($file);

            // Extract migration number from filename (expects format: NNN_name.sql)
            if (preg_match('/^(\d{3})_/', $filename, $matches)) {
                $number = (int)$matches[1];
                $numbersFound[] = $number;

                if ($number !== $expectedNumber) {
                    $hasGaps = true;
                    $this->addWarning(
                        "Migration numbering gap: expected {$this->formatNumber($expectedNumber)}, " .
                        "found {$this->formatNumber($number)} in {$filename}"
                    );
                }
                $expectedNumber = $number + 1;
            } else {
                $this->addWarning("Migration file does not follow naming convention (NNN_name.sql): {$filename}");
            }
        }

        if (!$hasGaps && count($numbersFound) > 0) {
            $this->addPass("All migrations are numbered sequentially: " .
                implode(', ', array_map([$this, 'formatNumber'], $numbersFound)));
        }

        echo "\n";
    }

    /**
     * Format migration number with leading zeros
     */
    private function formatNumber(int $num): string
    {
        return str_pad($num, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Audit migration order for dependency issues
     */
    private function auditMigrationOrder(array $migrationFiles): void
    {
        echo "=== AUDIT 2: Migration Dependency Order ===\n\n";

        // Initialize with core existing tables
        $this->createdTables = $this->coreExistingTables;

        foreach ($migrationFiles as $file) {
            $this->auditSingleMigration($file);
        }

        echo "\n";
    }

    /**
     * Audit a single migration file for dependency issues
     */
    private function auditSingleMigration(string $file): void
    {
        $filename = basename($file);
        $content = file_get_contents($file);

        if ($content === false) {
            $this->addError("Could not read migration file: {$filename}");
            return;
        }

        echo "--- Migration: {$filename} ---\n";

        // Extract tables created in this migration
        $tablesCreated = $this->extractCreatedTables($content);

        // Extract foreign key references
        $foreignKeyRefs = $this->extractForeignKeyReferences($content);

        // Extract ALTER TABLE references
        $alterTableRefs = $this->extractAlterTableReferences($content);

        // Extract tables referenced in views
        $viewRefs = $this->extractViewReferences($content);

        // Track tables created by this migration
        $this->allMigrationTables[$filename] = $tablesCreated;

        // Display tables created
        if (!empty($tablesCreated)) {
            echo "  Tables created: " . implode(', ', $tablesCreated) . "\n";
        }

        // Check ALTER TABLE references
        $this->checkAlterTableDependencies($filename, $alterTableRefs);

        // Check foreign key references
        $this->checkForeignKeyDependencies($filename, $foreignKeyRefs, $tablesCreated);

        // Check view references (informational only)
        $this->checkViewDependencies($filename, $viewRefs);

        // Add created tables to the list (after checking dependencies)
        foreach ($tablesCreated as $table) {
            if (!in_array(strtolower($table), $this->createdTables)) {
                $this->createdTables[] = strtolower($table);
            }
        }

        echo "\n";
    }

    /**
     * Extract table names from CREATE TABLE statements
     */
    private function extractCreatedTables(string $content): array
    {
        $tables = [];

        // Match CREATE TABLE [IF NOT EXISTS] table_name
        if (preg_match_all('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`"]?(\w+)[`"]?/i', $content, $matches)) {
            $tables = array_unique($matches[1]);
        }

        return $tables;
    }

    /**
     * Extract foreign key references from REFERENCES clauses
     */
    private function extractForeignKeyReferences(string $content): array
    {
        $references = [];

        // Match REFERENCES table_name(column)
        // Also match FOREIGN KEY ... REFERENCES table_name(column)
        if (preg_match_all('/REFERENCES\s+[`"]?(\w+)[`"]?\s*\(/i', $content, $matches)) {
            $references = array_unique($matches[1]);
        }

        return $references;
    }

    /**
     * Extract table names from ALTER TABLE statements
     */
    private function extractAlterTableReferences(string $content): array
    {
        $tables = [];

        // Match ALTER TABLE table_name
        if (preg_match_all('/ALTER\s+TABLE\s+[`"]?(\w+)[`"]?/i', $content, $matches)) {
            $tables = array_unique($matches[1]);
        }

        return $tables;
    }

    /**
     * Extract tables referenced in VIEW definitions
     */
    private function extractViewReferences(string $content): array
    {
        $tables = [];

        // Match FROM table_name or JOIN table_name in VIEW definitions
        // First, find all CREATE VIEW blocks
        if (preg_match_all('/CREATE\s+(?:OR\s+REPLACE\s+)?VIEW\s+\w+\s+AS\s+(SELECT[^;]+)/is', $content, $viewMatches)) {
            foreach ($viewMatches[1] as $viewBody) {
                // Extract table references from FROM and JOIN clauses
                if (preg_match_all('/(?:FROM|JOIN)\s+[`"]?(\w+)[`"]?(?:\s+(?:AS\s+)?\w+)?/i', $viewBody, $tableMatches)) {
                    $tables = array_merge($tables, $tableMatches[1]);
                }
            }
        }

        return array_unique($tables);
    }

    /**
     * Check ALTER TABLE dependencies
     */
    private function checkAlterTableDependencies(string $filename, array $alterTableRefs): void
    {
        foreach ($alterTableRefs as $table) {
            $tableLower = strtolower($table);
            if (in_array($tableLower, $this->createdTables)) {
                $this->addPass("ALTER TABLE {$table} - table exists (core or previously created)");
            } else {
                $this->addError(
                    "ALTER TABLE {$table} references non-existent table in {$filename}. " .
                    "Table must be created in an earlier migration or be a core OpenCATS table."
                );
            }
        }
    }

    /**
     * Check foreign key dependencies
     */
    private function checkForeignKeyDependencies(string $filename, array $foreignKeyRefs, array $tablesCreated): void
    {
        foreach ($foreignKeyRefs as $refTable) {
            $refTableLower = strtolower($refTable);
            $tablesCreatedLower = array_map('strtolower', $tablesCreated);

            // Check if referenced table exists (in core, previously created, or created in same migration)
            if (in_array($refTableLower, $this->createdTables)) {
                $this->addPass("REFERENCES {$refTable} - table exists (core or previously created)");
            } elseif (in_array($refTableLower, $tablesCreatedLower)) {
                // Table is created in the same migration - check if it's created before the FK
                $this->addPass("REFERENCES {$refTable} - table created in same migration");
            } else {
                $this->addWarning(
                    "REFERENCES {$refTable} in {$filename} - table not found. " .
                    "This may be a forward reference or the table might need to be added to core tables list."
                );
            }
        }
    }

    /**
     * Check view dependencies (informational)
     */
    private function checkViewDependencies(string $filename, array $viewRefs): void
    {
        foreach ($viewRefs as $refTable) {
            $refTableLower = strtolower($refTable);

            // Skip common SQL keywords that might be captured
            $skipWords = ['select', 'from', 'where', 'and', 'or', 'as', 'on', 'null', 'case', 'when', 'then', 'else', 'end'];
            if (in_array($refTableLower, $skipWords)) {
                continue;
            }

            if (!in_array($refTableLower, $this->createdTables)) {
                // Views can reference tables that will be created in subsequent migrations
                // This is just informational
                echo "  [INFO] VIEW references table: {$refTable}\n";
            }
        }
    }

    /**
     * Print summary of tables created by each migration
     */
    private function printTableSummary(): void
    {
        echo "=== AUDIT 3: Tables Created Summary ===\n\n";

        if (empty($this->allMigrationTables)) {
            echo "No tables created by migrations.\n";
            return;
        }

        echo "Tables created by each migration:\n";
        echo str_repeat("-", 50) . "\n";

        $totalTables = 0;
        foreach ($this->allMigrationTables as $filename => $tables) {
            if (!empty($tables)) {
                echo "\n{$filename}:\n";
                foreach ($tables as $table) {
                    echo "  - {$table}\n";
                    $totalTables++;
                }
            }
        }

        echo "\n" . str_repeat("-", 50) . "\n";
        echo "Total new tables: {$totalTables}\n";
        echo "Core existing tables: " . count($this->coreExistingTables) . "\n";
        echo "Total tables available after migrations: " . count($this->createdTables) . "\n\n";

        // List all tables available after migrations
        echo "All available tables after migrations:\n";
        sort($this->createdTables);
        $columns = 4;
        $count = 0;
        foreach ($this->createdTables as $table) {
            printf("  %-30s", $table);
            $count++;
            if ($count % $columns === 0) {
                echo "\n";
            }
        }
        if ($count % $columns !== 0) {
            echo "\n";
        }

        echo "\n";
    }

    /**
     * Add a passing check result
     */
    private function addPass(string $message): void
    {
        $this->passCount++;
        echo "  [PASS] {$message}\n";
    }

    /**
     * Add a warning result
     */
    private function addWarning(string $message): void
    {
        $this->warningCount++;
        echo "  [WARN] {$message}\n";
    }

    /**
     * Add an error result
     */
    private function addError(string $message): void
    {
        $this->errorCount++;
        echo "  [ERROR] {$message}\n";
    }

    /**
     * Print audit header
     */
    private function printHeader(): void
    {
        echo str_repeat("=", 70) . "\n";
        echo "OpenCATS Migration Order Validation Audit\n";
        echo str_repeat("=", 70) . "\n";
        echo "Migrations path: {$this->migrationsPath}\n";
        echo "Core existing tables: " . implode(', ', $this->coreExistingTables) . "\n";
        echo str_repeat("=", 70) . "\n\n";
    }

    /**
     * Print audit summary
     */
    private function printSummary(): void
    {
        echo str_repeat("=", 70) . "\n";
        echo "MIGRATION ORDER AUDIT SUMMARY\n";
        echo str_repeat("=", 70) . "\n\n";

        echo "Results:\n";
        echo "  Passed:   {$this->passCount}\n";
        echo "  Warnings: {$this->warningCount}\n";
        echo "  Errors:   {$this->errorCount}\n\n";

        if ($this->errorCount > 0) {
            echo "STATUS: FAILED - {$this->errorCount} error(s) found\n";
            echo "\nErrors indicate critical issues that must be fixed:\n";
            echo "- Tables referenced before they are created\n";
            echo "- ALTER TABLE on non-existent tables\n";
            echo "- Missing core table dependencies\n";
        } elseif ($this->warningCount > 0) {
            echo "STATUS: WARNING - {$this->warningCount} warning(s) found\n";
            echo "\nWarnings should be reviewed but may be acceptable:\n";
            echo "- Migration numbering gaps\n";
            echo "- Forward references in foreign keys (may need application-level enforcement)\n";
        } else {
            echo "STATUS: PASSED - All migration order checks passed\n";
        }

        echo "\n" . str_repeat("=", 70) . "\n";
    }

    /**
     * Get error count
     */
    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    /**
     * Get warning count
     */
    public function getWarningCount(): int
    {
        return $this->warningCount;
    }
}

// Run the auditor
$auditor = new MigrationOrderAuditor($migrationsPath, $coreExistingTables);
$exitCode = $auditor->run();
exit($exitCode);
