<?php

namespace OpenCATS\Tests\IntegrationTests;

use DatabaseConnection;

class DatabaseConnectionTest extends DatabaseTestCase
{
    public function testMakeQueryString()
    {
        $db = DatabaseConnection::getInstance();

        $strings = [
            ['test string',  "'test string'"],
            ['te\st', "'te\\\st'"],
            ['te\s\t', "'te\\\s\\\\t'"],
            ['te\'st',  "'te\\'st'"],
            ['\'; DELETE FROM test_table; SELECT \'',  "'\'; DELETE FROM test_table; SELECT \''"],
            ['te\'s`t',  "'te\\'s`t'"],
        ];

        foreach ($strings as $key => $value) {
            $this->assertSame(
                $db->makeQueryString($value[0]),
                $value[1],
                $value[0] . ' => ' . $value[1]
            );
        }
    }

    public function testEscapeString()
    {
        $db = DatabaseConnection::getInstance();

        $strings = [
            ['test string',  "test string"],
            ['te\st', "te\\\st"],
            ['te\s\t', "te\\\s\\\\t"],
            ['te\'st',  "te\\'st"],
            ['\'; DELETE FROM test_table; SELECT \'',  "\'; DELETE FROM test_table; SELECT \'"],
            ['te\'s`t',  "te\\'s`t"],
        ];

        foreach ($strings as $key => $value) {
            $this->assertSame(
                $db->escapeString($value[0]),
                $value[1],
                $value[0] . ' => ' . $value[1]
            );
        }
    }

    public function testMakeQueryStringOrNULL()
    {
        $db = DatabaseConnection::getInstance();

        $strings = [
            ['test string',  "'test string'"],
            ['te\st', "'te\\\st'"],
            ['te\s\t', "'te\\\s\\\\t'"],
            ['te\'st',  "'te\\'st'"],
            ['\'; DELETE FROM test_table; SELECT \'',  "'\'; DELETE FROM test_table; SELECT \''"],
            ['te\'s`t',  "'te\\'s`t'"],
            ['    ',  'NULL'],
            [' ',  'NULL'],
            ['	 		',  'NULL'],
            ['',  'NULL'],
        ];

        foreach ($strings as $key => $value) {
            $this->assertSame(
                $db->makeQueryStringOrNULL($value[0]),
                $value[1],
                $value[0] . ' => ' . $value[1]
            );
        }
    }

    public function testMakeQueryInteger()
    {
        $db = DatabaseConnection::getInstance();

        $strings = [
            ['1.5',  1],
            ['not-a-double', 0],
            ['1.999', 1],
            ['1notastring', 1],
            ['-22356', -22356],
        ];

        foreach ($strings as $key => $value) {
            $this->assertSame(
                $db->makeQueryInteger($value[0]),
                $value[1],
                $value[0] . ' => ' . $value[1]
            );
        }
    }

    public function testMakeQueryIntegerOrNULL()
    {
        $db = DatabaseConnection::getInstance();

        $strings = [
            ['1.5',  1],
            ['not-a-double', 0],
            ['1.999', 1],
            ['1notastring', 1],
            ['-22356', -22356],
            ['-1', 'NULL'],
        ];

        foreach ($strings as $key => $value) {
            $this->assertSame(
                $db->makeQueryIntegerOrNULL($value[0]),
                $value[1],
                $value[0] . ' => ' . $value[1]
            );
        }
    }

    public function testMakeQueryDouble()
    {
        $db = DatabaseConnection::getInstance();

        $strings = [
            ['1.5',  '1.5'],
            ['not-a-double', '0.0'],
            ['1.99999999999999', '2', 2],
            ['1.80123', '1.80', 2],
            ['1.99999999999999', '1.99999999999999'],
        ];

        foreach ($strings as $key => $value) {
            if (isset($value[2])) {
                $queryDouble = $db->makeQueryDouble($value[0], $value[2]);
            } else {
                $queryDouble = $db->makeQueryDouble($value[0]);
            }

            $this->assertSame(
                $queryDouble,
                $value[1],
                $queryDouble . ' should be ' . $value[1]
            );
        }
    }

    public function testQuery()
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
            mysqli_num_rows($queryResult),
            1,
            '1 row should be returned'
        );
        $this->assertTrue(
            ! $db->isEOF(),
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
