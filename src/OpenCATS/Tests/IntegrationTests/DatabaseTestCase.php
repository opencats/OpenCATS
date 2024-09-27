<?php

namespace OpenCATS\Tests\IntegrationTests;

use PHPUnit\Framework\TestCase;
use mysqli;
use Exception;

class DatabaseTestCase extends TestCase
{
    private $connection;

    protected function setUp(): void
    {
        parent::setUp();

        // Include necessary files
        $this->loadRequiredFiles();

        // Define database constants only if not already defined
        $this->defineDatabaseConstants();

        // Initialize MySQL connection
        $this->initializeDatabaseConnection();

        // Drop and recreate the test database
        $this->resetDatabase();

        // Import the database schema
        $this->importSchema();
    }

    /**
     * Load necessary files like constants and configuration.
     */
    private function loadRequiredFiles(): void
    {
        include_once './constants.php';
        include_once './config.php';
        include_once LEGACY_ROOT . '/lib/DatabaseConnection.php';
    }

    /**
     * Define database constants if not already defined.
     */
    private function defineDatabaseConstants(): void
    {
        if (!defined('DATABASE_NAME')) {
            define('DATABASE_NAME', 'cats_integrationtest');
        }

        if (!defined('DATABASE_HOST')) {
            define('DATABASE_HOST', 'integrationtestdb');
        }

        if (!defined('DATABASE_USER')) {
            define('DATABASE_USER', 'your_db_user'); // Replace with actual user
        }

        if (!defined('DATABASE_PASS')) {
            define('DATABASE_PASS', 'your_db_password'); // Replace with actual password
        }
    }

    /**
     * Initialize the MySQL database connection.
     *
     * @throws Exception If the connection fails.
     */
    private function initializeDatabaseConnection(): void
    {
        $this->connection = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS);

        if ($this->connection->connect_error) {
            throw new Exception('Error connecting to the MySQL server: ' . $this->connection->connect_error);
        }
    }

    /**
     * Drop the existing database (if it exists) and create a new one.
     *
     * @throws Exception If the queries fail.
     */
    private function resetDatabase(): void
    {
        $this->mySQLQuery('DROP DATABASE IF EXISTS ' . DATABASE_NAME);
        $this->mySQLQuery('CREATE DATABASE ' . DATABASE_NAME);

        // Select the test database
        if (!$this->connection->select_db(DATABASE_NAME)) {
            throw new Exception('Failed to select database: ' . $this->connection->error);
        }
    }

    /**
     * Import the database schema from the provided SQL file.
     *
     * @throws Exception If the file or the queries fail.
     */
    private function importSchema(): void
    {
        $schemaFile = 'db/cats_schema.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception('Schema file not found: ' . $schemaFile);
        }

        $schemaSQL = file_get_contents($schemaFile);
        $this->mySQLQueryMultiple($schemaSQL, ";\n");
    }

    /**
     * Execute multiple SQL queries, separated by a given delimiter.
     *
     * @param string $SQLData The SQL data to execute.
     * @param string $delimiter The delimiter between SQL statements.
     */
    private function mySQLQueryMultiple(string $SQLData, string $delimiter = ';'): void
    {
        $SQLStatements = explode($delimiter, $SQLData);

        foreach ($SQLStatements as $SQL) {
            $SQL = trim($SQL);
            if (!empty($SQL)) {
                $this->mySQLQuery($SQL);
            }
        }
    }

    /**
     * Execute a single SQL query.
     *
     * @param string $query The SQL query to execute.
     * @param bool $ignoreErrors Whether to ignore query errors.
     * @return bool Returns true on success, false otherwise.
     * @throws Exception If the query fails and $ignoreErrors is false.
     */
    private function mySQLQuery(string $query, bool $ignoreErrors = false): bool
    {
        $result = $this->connection->query($query);

        if (!$result && !$ignoreErrors) {
            throw new Exception('MySQL Query Failed: ' . $this->connection->error . "\nQuery: " . $query);
        }

        return (bool)$result;
    }

    /**
     * Clean up after each test by dropping the test database and closing the connection.
     */
    protected function tearDown(): void
    {
        // Drop the test database after each test
        $this->mySQLQuery('DROP DATABASE IF EXISTS ' . DATABASE_NAME);

        // Close the MySQL connection
        if ($this->connection) {
            $this->connection->close();
        }

        parent::tearDown();
    }
}
