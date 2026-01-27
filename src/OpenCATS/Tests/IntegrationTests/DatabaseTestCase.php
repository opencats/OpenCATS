<?php
namespace OpenCATS\Tests\IntegrationTests;

use PHPUnit\Framework\TestCase;

class DatabaseTestCase extends TestCase
{
    private $connection;

    function setUp()
    {
        global $mySQLConnection;
        parent::setUp();

        // Ensure roots are defined for legacy includes
        if (!defined('LEGACY_ROOT')) {
            define('LEGACY_ROOT', '.');
        }

        include_once('./constants.php');

        // We define these for the rest of the app logic,
        // but we will use explicit strings for the connection call below.
        if (!defined('DATABASE_NAME')) define('DATABASE_NAME', 'cats_integrationtest');
        if (!defined('DATABASE_HOST')) define('DATABASE_HOST', 'integrationtestdb');

        include_once('./config.php');
        include_once(LEGACY_ROOT . '/lib/DatabaseConnection.php');

        // FIXED: Added quotes around strings
        $mySQLConnection = @mysqli_connect(
            'integrationtestdb',
            'dev',
            'dev'
        );

        if (!$mySQLConnection)
        {
            throw new \Exception('Error connecting to the mysql server: ' . mysqli_connect_error());
        }

        $this->mySQLQuery('DROP DATABASE IF EXISTS ' . DATABASE_NAME);
        $this->mySQLQuery('CREATE DATABASE ' . DATABASE_NAME);

        // FIXED: Corrected parameter order for mysqli_select_db
        @mysqli_select_db($mySQLConnection, DATABASE_NAME);

        $this->mySQLQueryMultiple(file_get_contents('db/cats_schema.sql'), ";\n");
    }

    private function MySQLQueryMultiple($SQLData, $delimiter = ';')
    {
        $SQLStatments = explode($delimiter, $SQLData);

        foreach ($SQLStatments as $SQL)
        {
            $SQL = trim($SQL);

            if (empty($SQL))
            {
                continue;
            }

            $this->mySQLQuery($SQL);
        }
    }

    private function mySQLQuery($query, $ignoreErrors = false)
    {
        global $mySQLConnection;

        $queryResult = mysqli_query($mySQLConnection, $query);
        if (!$queryResult && !$ignoreErrors)
        {
            // FIXED: Using mysqli_error() since $queryResult is false on failure
            $error = "errno: " . mysqli_errno($mySQLConnection) . ", ";
            $error .= "error: " . mysqli_error($mySQLConnection);

            if ($error == 'Query was empty')
            {
                return $queryResult;
            }

            die (
                '<p style="background: #ec3737; padding: 4px; margin-top: 0; font:'
                . ' normal normal bold 12px/130% Arial, Tahoma, sans-serif;">Query'
                . " Error -- Please Report This Bug!</p><pre>\n\nMySQL Query "
                . "Failed: " . $error . "\n\n" . $query . "</pre>\n\n"
            );
        }

        return $queryResult;
    }

    function tearDown()
    {
        $this->mySQLQuery('DROP DATABASE IF EXISTS ' . DATABASE_NAME);
    }
}
