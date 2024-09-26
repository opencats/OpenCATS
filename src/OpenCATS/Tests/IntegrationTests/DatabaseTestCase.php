<?php

namespace OpenCATS\Tests\IntegrationTests;

use PHPUnit\Framework\TestCase;
use mysqli;

class DatabaseTestCase extends TestCase
{
    private $connection;

    protected function setUp(): void
    {
        parent::setUp();

        // Include necessary files
        include_once('./constants.php');
        include_once('./config.php');
        include_once(LEGACY_ROOT . '/lib/DatabaseConnection.php');

        // Define database constants
        define('DATABASE_NAME', 'cats_integrationtest');
        define('DATABASE_HOST', 'integrationtestdb');

        // Initialize MySQL connection
        $this->connection = new mysqli(
            DATABASE_HOST,
            DATABASE_USER,
            DATABASE_PASS
        );

        if ($this->connection->connect_error) {
            throw new \Exception('Error connecting to the MySQL server: ' . $this->connection->connect_error);
        }

        // Drop and recreate the test database
        $this->mySQLQuery('DROP DATABASE IF EXISTS ' . DATABASE_NAME);
        $this->mySQLQuery('CREATE DATABASE ' . DATABASE_NAME);

        // Select the test database
        if (! $this->connection->select_db(DATABASE_NAME)) {
            throw new \Exception('Failed to select database: ' . $this->connection->error);
        }

        // Import the schema
        $this->mySQLQueryMultiple(file_get_contents('db/cats_schema.sql'), ";\n");
    }

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

    private function mySQLQuery(string $query, bool $ignoreErrors = false): bool
    {
        $result = $this->connection->query($query);

        if (!$result && !$ignoreErrors) {
            throw new \Exception('MySQL Query Failed: ' . $this->connection->error . "\nQuery: " . $query);
        }

        return (bool) $result;
    }

    protected function tearDown(): void
    {
        // Drop the test database after each test
        $this->mySQLQuery('DROP DATABASE IF EXISTS ' . DATABASE_NAME);

        // Close the connection
        if ($this->connection) {
            $this->connection->close();
        }

        parent::tearDown();
    }
}
