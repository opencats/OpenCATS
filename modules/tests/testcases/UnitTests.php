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
