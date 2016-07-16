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

include_once('./lib/StringUtility.php');
include_once('./lib/DateUtility.php');   /* Depends on StringUtility. */
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

/* Tests for DateUtility class. */
class DateUtilityTest extends CATSUnitTestCase
{
    /* Tests for getStartingWeekday(). */
    function testGetStartingWeekday()
    {
        $this->assertIdentical(
            DateUtility::getStartingWeekday(CALENDAR_MONTH_MARCH, 2006),
            CALENDAR_DAY_WEDNSDAY
        );

        $this->assertIdentical(
            DateUtility::getStartingWeekday(CALENDAR_MONTH_MARCH, 1987),
            CALENDAR_DAY_SUNDAY
        );
        $this->assertIdentical(
            DateUtility::getStartingWeekday(CALENDAR_MONTH_APRIL, 1987),
            CALENDAR_DAY_WEDNSDAY
        );
    }

    /* Tests for getStartingWeekday(). */
    function testGetDaysInMonth()
    {
        $this->assertIdentical(
            DateUtility::getDaysInMonth(CALENDAR_MONTH_MARCH, 2006),
            31
        );

        $this->assertIdentical(
            DateUtility::getDaysInMonth(CALENDAR_MONTH_MARCH, 1987),
            DateUtility::getDaysInMonth(CALENDAR_MONTH_MARCH, 2006)
        );

        $this->assertIdentical(
            DateUtility::getDaysInMonth(CALENDAR_MONTH_APRIL, 1987),
            30
        );

        /* Leap years... */
        $this->assertIdentical(
            DateUtility::getDaysInMonth(CALENDAR_MONTH_FEBRUARY, 2008),
            29
        );
        $this->assertIdentical(
            DateUtility::getDaysInMonth(CALENDAR_MONTH_FEBRUARY, 2006),
            28
        );
    }

    /* Tests for getMonthName(). */
    function testGetMonthName()
    {
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_JANUARY),
            'January'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_FEBRUARY),
            'February'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_MARCH),
            'March'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_APRIL),
            'April'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_MAY),
            'May'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_JUNE),
            'June'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_JULY),
            'July'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_AUGUST),
            'August'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_SEPTEMBER),
            'September'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_OCTOBER),
            'October'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_NOVEMBER),
            'November'
        );
        $this->assertIdentical(
            DateUtility::getMonthName(CALENDAR_MONTH_DECEMBER),
            'December'
        );
    }

    /* Tests for validate(). */
    function testValidate()
    {
        $validDates = array(
            array('/', '01/01/01', DATE_FORMAT_MMDDYY),
            array('/', '02/27/05', DATE_FORMAT_MMDDYY),
            array('/', '02/28/05', DATE_FORMAT_MMDDYY),
            array('/', '02/29/04', DATE_FORMAT_MMDDYY),
            array('/', '02/29/00', DATE_FORMAT_MMDDYY),
            array('/', '12/31/05', DATE_FORMAT_MMDDYY),
            array('-', '12-31-05', DATE_FORMAT_MMDDYY),
            array('-', '02-29-00', DATE_FORMAT_MMDDYY),
            array('-', '22-02-07', DATE_FORMAT_DDMMYY),
            array('-', '2007-03-25', DATE_FORMAT_YYYYMMDD)
        );

        $invalidDates = array(
            array('/', '00/00/00', DATE_FORMAT_MMDDYY),
            array('/', '02/29/05', DATE_FORMAT_MMDDYY),
            array('/', '02/31/05', DATE_FORMAT_MMDDYY),
            array('/', '13/01/05', DATE_FORMAT_MMDDYY),
            array('/', '00/01/05', DATE_FORMAT_MMDDYY),
            array('/', '12-01-05', DATE_FORMAT_MMDDYY),
            array('-', '00/01/05', DATE_FORMAT_MMDDYY),
            array('-', '00-01-05', DATE_FORMAT_MMDDYY),
            array('/', '00/01/2005', DATE_FORMAT_MMDDYY),
            array('-', '00/01/2005', DATE_FORMAT_MMDDYY),
            array('-', '00-01-2005', DATE_FORMAT_MMDDYY),
            array('-', '000105', DATE_FORMAT_MMDDYY),
            array('-', 'Test!', DATE_FORMAT_MMDDYY),
            array('-', '02-29-07', DATE_FORMAT_DDMMYY),
            array('-', '2007-03-40', DATE_FORMAT_YYYYMMDD),
            array('-', 'This sentence contains 12-01-05.', DATE_FORMAT_MMDDYY)
        );

        foreach ($validDates as $key => $value)
        {
            $this->assertTrue(
                DateUtility::validate($value[0], $value[1], $value[2]),
                $value[1] . ' (Separator: ' . $value[0] . ')'
            );
        }

        foreach ($invalidDates as $key => $value)
        {
            $this->assertFalse(
                DateUtility::validate($value[0], $value[1], $value[2]),
                $value[1] . ' (Separator: ' . $value[0] . ')'
            );
        }
    }

    /* Tests for convert(). */
    function testConvert()
    {
        $dates = array(
            array('/', '01/01/01', DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD, '2001/01/01'),
            array('/', '02/27/01', DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD, '2001/02/27'),
            array('-', '01-01-01', DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD, '2001-01-01'),
            array('-', '02-27-01', DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD, '2001-02-27'),
            array('-', '2002-01-30', DATE_FORMAT_YYYYMMDD, DATE_FORMAT_MMDDYY, '01-30-02'),
        );

        foreach ($dates as $key => $value)
        {
            $this->assertIdentical(
                DateUtility::convert($value[0], $value[1], $value[2], $value[3]),
                $value[4]
            );
        }
    }
}

?>
