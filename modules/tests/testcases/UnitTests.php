<?php
/*
 * CATS
 * Tests Module - Unit Test Cases
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * All rights reserved.
 *
 * DO NOT RUN TABS-TO-SPACES ON THIS FILE!!!
 *
 * $Id: UnitTests.php 3565 2007-11-12 09:09:22Z will $
 */

include_once('./lib/DatabaseConnection.php');

/* Tests for DatabaseSearch class. */
class DatabaseSearchTest extends CATSUnitTestCase
{
    function testMakeREGEXPString()
    {
        //FIXME: Write me!
    }

    function testMakeBooleanSQLWhere()
    {
        $tests = array(
            array(
                'java',
                '((field REGEXP \'[[:<:]]java[[:>:]]\'))'
            ),
            array(
                'java sql',
                '((field REGEXP \'[[:<:]]java[[:>:]]\') AND (field REGEXP \'[[:<:]]sql[[:>:]]\'))'
            ),
            array(
                'java | sql',
                '((field REGEXP \'[[:<:]]java[[:>:]]\') OR (field REGEXP \'[[:<:]]sql[[:>:]]\'))'
            ),
            array(
                'java,sql',
                '((field REGEXP \'[[:<:]]java[[:>:]]\') OR (field REGEXP \'[[:<:]]sql[[:>:]]\'))'
            ),
            array(
                'java, ,,sql',
                '((field REGEXP \'[[:<:]]java[[:>:]]\') OR (field REGEXP \'[[:<:]]sql[[:>:]]\'))'
            ),
            array(
                'java -sql',
                '((field REGEXP \'[[:<:]]java[[:>:]]\') AND NOT (field REGEXP \'[[:<:]]sql[[:>:]]\'))'
            ),
            array(
                'java !sql',
                '((field REGEXP \'[[:<:]]java[[:>:]]\') AND NOT (field REGEXP \'[[:<:]]sql[[:>:]]\'))'
            ),
            array(
                'java*',
                '((field LIKE \'%java%\'))'
            ),
            array(
                'java* sql*',
                '((field LIKE \'%java%\') AND (field LIKE \'%sql%\'))'
            ),
            array(
                'java (',
                '0'
            ),
            array(
                'java) (',
                '0'
            ),
            array(
                'java ()',
                '((field REGEXP \'[[:<:]]java[[:>:]]\'))'
            )
        );
        
        $db = DatabaseConnection::getInstance();
        foreach ($tests as $test)
        {
            $this->assertIdentical(
                DatabaseSearch::makeBooleanSQLWhere($test[0], $db, 'field'),
                $test[1]
            );
        }
    }
}

/* Tests for DatabaseConnection class. */
class DatabaseConnectionTest extends CATSUnitTestCase
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
            $this->assertIdentical(
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
            $this->assertIdentical(
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
            $this->assertIdentical(
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
            $this->assertIdentical(
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
            $this->assertIdentical(
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
            
            $this->assertIdentical(
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
        $this->assertNotIdentical(
            $queryResult,
            false,
            'INSERT query should succeed'
        );

        $queryResult = $db->query('SELECT * FROM installtest LIMIT 1');
        $this->assertNotIdentical(
            $queryResult,
            false,
            'SELECT query should succeed'
        );
        $this->assertEqual(
            mysql_num_rows($queryResult),
            1,
            '1 row should be returned'
        );
        $this->assertTrue(
            !$db->isEOF(),
            'EOF should not be received'
        );

        $queryResult = $db->query('UPDATE installtest SET id = 34 WHERE id = 35');
        $this->assertNotIdentical(
            $queryResult,
            false,
            'UPDATE query should succeed'
        );

        $queryResult = $db->query('DELETE FROM installtest WHERE id = 34');
        $this->assertNotIdentical(
            $queryResult,
            false,
            'DELETE query should succeed'
        );
    }
}


?>
