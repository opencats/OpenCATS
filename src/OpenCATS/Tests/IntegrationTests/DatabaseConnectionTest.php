<?php
namespace OpenCATS\Tests\IntegrationTests;

use \OpenCATS\Tests\IntegrationTests\DatabaseTestCase;
use DatabaseConnection;

class DatabaseConnectionTest extends DatabaseTestCase
{
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
            array('0', "'0'"),
            array('    ',  'NULL'),
            array(' ',  'NULL'),
            array('	 		',  'NULL'),
            array('',  'NULL'),
            array(null, 'NULL')
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
        $this->assertSame(
            1,
            mysqli_num_rows($queryResult),
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

    function testGetAssocWithoutQueryUsesActiveResultSetAndAdvances()
    {
        $db = DatabaseConnection::getInstance();

        $db->query('INSERT INTO installtest (id) VALUES (101), (102)');
        $db->query('SELECT id FROM installtest ORDER BY id ASC');

        $firstRow = $db->getAssoc();
        $secondRow = $db->getAssoc();
        $thirdRow = $db->getAssoc();

        $this->assertSame(
            array('id' => '101'),
            $firstRow,
            'First row should be returned from the active result set.'
        );
        $this->assertSame(
            array('id' => '102'),
            $secondRow,
            'Second call should advance to the next row in the active result set.'
        );
        $this->assertSame(
            array(),
            $thirdRow,
            'Exhausted active result sets should return an empty array.'
        );
    }

    function testGetAssocWithoutQuerySupportsCountRowAfterQuery()
    {
        $db = DatabaseConnection::getInstance();

        $db->query('INSERT INTO installtest (id) VALUES (201), (202), (203)');
        $db->query('SELECT COUNT(*) AS totalRows FROM installtest');

        $countRow = $db->getAssoc();

        $this->assertSame(
            array('totalRows' => '3'),
            $countRow,
            'No-argument getAssoc() should read the count-like row from the active result set.'
        );
    }

    function testGetNumRowsReturnsRowCountForActiveSelectResult()
    {
        $db = DatabaseConnection::getInstance();

        $db->query('INSERT INTO installtest (id) VALUES (301), (302), (303)');
        $db->query('SELECT id FROM installtest ORDER BY id ASC');

        $this->assertSame(
            3,
            $db->getNumRows(),
            'getNumRows() should return row count from the active SELECT result set.'
        );
    }

    function testGetAffectedRowsReflectsInsertUpdateAndDelete()
    {
        $db = DatabaseConnection::getInstance();

        $db->query('INSERT INTO installtest (id) VALUES (401), (402)');
        $this->assertSame(
            2,
            $db->getAffectedRows(),
            'getAffectedRows() should return inserted row count.'
        );

        $db->query('UPDATE installtest SET id = id + 1000 WHERE id IN (401, 402)');
        $this->assertSame(
            2,
            $db->getAffectedRows(),
            'getAffectedRows() should return updated row count.'
        );

        $db->query('DELETE FROM installtest WHERE id IN (1401, 1402)');
        $this->assertSame(
            2,
            $db->getAffectedRows(),
            'getAffectedRows() should return deleted row count.'
        );
    }

    function testGetLastInsertIDReturnsAutoIncrementValue()
    {
        $db = DatabaseConnection::getInstance();

        $db->query(
            'CREATE TABLE test_autoincrement ('
            . 'id INT NOT NULL AUTO_INCREMENT, '
            . 'label VARCHAR(32) NOT NULL, '
            . 'PRIMARY KEY (id)'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );

        $db->query("INSERT INTO test_autoincrement (label) VALUES ('first row')");
        $firstInsertId = $db->getLastInsertID();

        $db->query("INSERT INTO test_autoincrement (label) VALUES ('second row')");
        $secondInsertId = $db->getLastInsertID();

        $this->assertSame(
            1,
            (int) $firstInsertId,
            'First insert should return auto-increment ID 1.'
        );
        $this->assertSame(
            2,
            (int) $secondInsertId,
            'Second insert should return auto-increment ID 2.'
        );
    }

}

?>
