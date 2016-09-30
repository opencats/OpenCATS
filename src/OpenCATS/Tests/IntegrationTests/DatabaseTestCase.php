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
        include_once('./constants.php');
        define('DATABASE_NAME', 'cats_integrationtest');
        define('DATABASE_HOST', 'integrationtestdb');

        include_once('./config.php');
        include_once('./lib/DatabaseConnection.php');
        $mySQLConnection = @mysql_connect(
            DATABASE_HOST, DATABASE_USER, DATABASE_PASS
            );
        if (!$mySQLConnection)
        {
            throw new \Exception('Error connecting to the mysql server');
        }
        $this->mySQLQuery('DROP DATABASE IF EXISTS ' . DATABASE_NAME);
        $this->mySQLQuery('CREATE DATABASE ' . DATABASE_NAME);

        @mysql_select_db(DATABASE_NAME, $mySQLConnection);

        $this->mySQLQueryMultiple(file_get_contents('db/cats_schema.sql'), ";\n");
    }

    // TODO: remove duplicated code
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

        $queryResult = mysql_query($query, $mySQLConnection);
        if (!$queryResult && !$ignoreErrors)
        {
            $error = mysql_error($mySQLConnection);

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
?>
