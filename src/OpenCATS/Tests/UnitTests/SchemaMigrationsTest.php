<?php
use PHPUnit\Framework\TestCase;

/*
 * Guards the install migration list in modules/install/Schema.php.
 *
 * The migrations are the string keys of the array returned by
 * CATSSchema::get(). A duplicate key is NOT a PHP error: PHP silently keeps
 * only the last occurrence, so the earlier migration is dropped before the
 * upgrade runner (ModuleUtility::updateSchema) ever sees it. That surfaces
 * later as a mysteriously missing table/column.
 *
 * Because PHP has already de-duplicated by the time the array is built, we
 * cannot detect this from CATSSchema::get() -- we must scan the source text.
 */
class SchemaMigrationsTest extends TestCase
{
    /*
     * Pre-existing duplicate keys that are intentionally left as-is for now.
     * New duplicates must not be adde.
     */
    private const KNOWN_DUPLICATE_KEYS = array(283);

    private function migrationKeysInSourceOrder(): array
    {
        $path = LEGACY_ROOT . '/modules/install/Schema.php';
        $source = file_get_contents($path);
        $this->assertNotFalse($source, "Could not read $path");

        /*
         * Array keys look like `    '123' => '` at the start of a line. The SQL
         * values are single-quoted strings, so no such line-anchored pattern can
         * appear inside them -- this reliably matches only the migration keys.
         */
        preg_match_all("/^\s*'(\d+)'\s*=>/m", $source, $matches);

        return array_map('intval', $matches[1]);
    }

    public function testNoNewDuplicateMigrationKeys(): void
    {
        $keys = $this->migrationKeysInSourceOrder();
        $this->assertNotEmpty($keys, 'No migration keys found in Schema.php');

        $counts = array_count_values($keys);
        $duplicates = array_keys(array_filter($counts, fn($n) => $n > 1));

        $unexpected = array_diff($duplicates, self::KNOWN_DUPLICATE_KEYS);
        sort($unexpected);

        $this->assertSame(
            array(),
            $unexpected,
            'Duplicate migration key(s) in Schema.php: ' . implode(', ', $unexpected)
                . '. A duplicate key silently drops the earlier migration -- '
                . 'use the next unused number instead.'
        );
    }

    public function testMigrationKeysAreInAscendingOrder(): void
    {
        $keys = $this->migrationKeysInSourceOrder();

        $previous = null;
        foreach ($keys as $key)
        {
            if ($previous !== null)
            {
                $this->assertGreaterThanOrEqual(
                    $previous,
                    $key,
                    "Migration key $key appears after $previous. Keys must be listed in ascending order."
                );
            }
            $previous = $key;
        }
    }
}
