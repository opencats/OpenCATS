<?php
use PHPUnit\Framework\TestCase;

class DatabaseConnectionTest extends TestCase
{
    private $connection;
    
    function setUp()
    {
        global $mySQLConnection;
        parent::setUp();
        include_once('./constants.php');
        define('DATABASE_NAME', 'cats_unittests');
        define('DATABASE_HOST', 'unittestdb');
        
        include_once('./config.php');
        include_once('./lib/DatabaseConnection.php');
        $mySQLConnection = @mysql_connect(
            DATABASE_HOST, DATABASE_USER, DATABASE_PASS
        );
        if (!$mySQLConnection)
        {
            throw new Exception('Error connecting to the mysql server');
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
    
    function testMakeQueryString()
    {
        $db = DatabaseConnection::getInstance();

        $strings = array(
            array('test string',  "'test string'"),
            array('te\st', "'te\\\st'"),
            array('te\s\t', "'te\\\s\\\\t'"),
            array('te\'st',  "'te\\'st'"),
            array('\'; DELETE FROM test_table; SELECT \'',  "'\'; DELETE FROM test_table; SELECT \''"),
            array('te\'s`t',  "'te\\'s`t'")
        );

        foreach ($strings as $key => $value)
        {
            $this->assertSame(
                $db->makeQueryString($value[0]),
                $value[1],
                $value[0] . ' => ' . $value[1]
                );
        }
    }

    function testEscapeString()
    {
        $db = DatabaseConnection::getInstance();

        $strings = array(
            array('test string',  "test string"),
            array('te\st', "te\\\st"),
            array('te\s\t', "te\\\s\\\\t"),
            array('te\'st',  "te\\'st"),
            array('\'; DELETE FROM test_table; SELECT \'',  "\'; DELETE FROM test_table; SELECT \'"),
            array('te\'s`t',  "te\\'s`t")
        );

        foreach ($strings as $key => $value)
        {
            $this->assertSame(
                $db->escapeString($value[0]),
                $value[1],
                $value[0] . ' => ' . $value[1]
                );
        }
    }

    function testMakeQueryStringOrNULL()
    {
        $db = DatabaseConnection::getInstance();

        $strings = array(
            array('test string',  "'test string'"),
            array('te\st', "'te\\\st'"),
            array('te\s\t', "'te\\\s\\\\t'"),
            array('te\'st',  "'te\\'st'"),
            array('\'; DELETE FROM test_table; SELECT \'',  "'\'; DELETE FROM test_table; SELECT \''"),
            array('te\'s`t',  "'te\\'s`t'"),
            array('    ',  'NULL'),
            array(' ',  'NULL'),
            array('	 		',  'NULL'),
            array('',  'NULL')
        );

        foreach ($strings as $key => $value)
        {
            $this->assertSame(
                $db->makeQueryStringOrNULL($value[0]),
                $value[1],
                $value[0] . ' => ' . $value[1]
                );
        }
    }

    function testMakeQueryInteger()
    {
        $db = DatabaseConnection::getInstance();

        $strings = array(
            array('1.5',  1),
            array('not-a-double', 0),
            array('1.999', 1),
            array('1notastring', 1),
            array('-22356', -22356)
        );

        foreach ($strings as $key => $value)
        {
            $this->assertSame(
                $db->makeQueryInteger($value[0]),
                $value[1],
                $value[0] . ' => ' . $value[1]
                );
        }
    }

    function testMakeQueryIntegerOrNULL()
    {
        $db = DatabaseConnection::getInstance();

        $strings = array(
            array('1.5',  1),
            array('not-a-double', 0),
            array('1.999', 1),
            array('1notastring', 1),
            array('-22356', -22356),
            array('-1', 'NULL')
        );

        foreach ($strings as $key => $value)
        {
            $this->assertSame(
                $db->makeQueryIntegerOrNULL($value[0]),
                $value[1],
                $value[0] . ' => ' . $value[1]
                );
        }
    }

    function testMakeQueryDouble()
    {
        $db = DatabaseConnection::getInstance();

        $strings = array(
            array('1.5',  '1.5'),
            array('not-a-double', '0.0'),
            array('1.99999999999999', '2', 2),
            array('1.80123', '1.80', 2),
            array('1.99999999999999', '1.99999999999999'),
        );

        foreach ($strings as $key => $value)
        {
            if (isset($value[2]))
            {
                $queryDouble = $db->makeQueryDouble($value[0], $value[2]);
            }
            else
            {
                $queryDouble = $db->makeQueryDouble($value[0]);
            }

            $this->assertSame(
                $queryDouble,
                $value[1],
                $queryDouble . ' should be ' . $value[1]
                );
        }
    }

    function testQuery()
    {
        $db = DatabaseConnection::getInstance();

        $queryResult = $db->query('INSERT INTO installtest (id) VALUES(35)');
        $this->assertNotSame(
            $queryResult,
            false,
            'INSERT query should succeed'
            );

        $queryResult = $db->query('SELECT * FROM installtest LIMIT 1');
        $this->assertNotSame(
            $queryResult,
            false,
            'SELECT query should succeed'
            );
        $this->assertEquals(
            mysql_num_rows($queryResult),
            1,
            '1 row should be returned'
            );
        $this->assertTrue(
            !$db->isEOF(),
            'EOF should not be received'
            );

        $queryResult = $db->query('UPDATE installtest SET id = 34 WHERE id = 35');
        $this->assertNotSame(
            $queryResult,
            false,
            'UPDATE query should succeed'
            );

        $queryResult = $db->query('DELETE FROM installtest WHERE id = 34');
        $this->assertNotSame(
            $queryResult,
            false,
            'DELETE query should succeed'
            );
    }
}

?>
