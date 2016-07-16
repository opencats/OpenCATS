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
include_once('./lib/ResultSetUtility.php');
include_once('./lib/DatabaseConnection.php');
include_once('./lib/VCard.php');
include_once('./lib/HashUtility.php');

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

/* Tests for StringUtility class. */
class StringUtilityTest extends CATSUnitTestCase
{
    /* Tests for isURL(). */
    function testIsURL()
    {
        $validURLs = array(
            'http://www.nospammonkeys.org',
            'http://www.eggheads.org/~wcc/test.txt',
            'ftp://ftp.eggheads.org/wcc/test.txt',
            'http://wcc:test@www.nospammonkeys.org',
            'http://test@www.nospammonkeys.org',
            'http://www.eggheads.org:80/~wcc/test.txt',
            'http://wcc:test@www.nospammonkeys.org:80/q.php?test=1&test2=bl+ah',
            'http://wcc:test@www.nospammonkeys.org:80/q.php?test=1&test2=/blah@blah.com',
            'www.cognizo.com/index.php',
            'http://24.72.64.156/index.php',
            'localhost/index.php'
        );

        $invalidURLs = array(
            '770-667-5085',
            'nntp://129.222.2532.5/index.php',
            '/index.php',
            'My web site is http://www.microsoft.com/index.php and this is a test sentence.'
        );

        foreach ($validURLs as $key => $value)
        {
            $this->assertTrue(
                StringUtility::isURL($value),
                sprintf("'%s' should be recognized as a URL", $value)
            );
        }

        foreach ($invalidURLs as $key => $value)
        {
            $this->assertFalse(
                StringUtility::isURL($value),
                sprintf("'%s' should not be recognized as a URL", $value)
            );
        }
    }

    /* Tests for extractURL(). */
    function testExtractURL()
    {
        $URLsToExtract = array(
            array(
                'http://wcc:test@www.nospammonkeys.org:80/q.php?test=1&test2=/blah@blah.com',
                'http://wcc:test@www.nospammonkeys.org/q.php?test=1&test2=/blah@blah.com'
            ),
            array(
                'http://wcc@www.nospammonkeys.org:80/q.php?test=1&test2=/blah@blah.com',
                'http://wcc@www.nospammonkeys.org/q.php?test=1&test2=/blah@blah.com'
            ),
            array(
                'www.cognizo.com/index.php',
                'http://www.cognizo.com/index.php'
            ),
            array(
                '24.72.64.156/index.php',
                'http://24.72.64.156/index.php'
            )
        );

        foreach ($URLsToExtract as $key => $value)
        {
            $formattedURL = StringUtility::extractURL($value[0]);
            $this->assertTrue(
                $formattedURL === $value[1],
                sprintf("Extracting URL from '%s' should result in '%s'", $value[0], $value[1])
            );
        }
    }

    /* Tests for isPhoneNumber(). */
    function testIsPhoneNumber()
    {
        $validPhoneNumbers = array(
            '7706675085',
            '(770) 667-5085',
            '(770) 667/5085',
            '(770) 667.5085',
            '(770) 667 5085',
            '(770)667/5085',
            '(770)6675085',
            '770-667-5085',
            '770.667.5085',
            '770/667/5085',
            '(+01) 909-444-4444',
            '(+01)909-444-4444',
            '(+01)9094444444',
            '+019094444444',
            '+01 9094444444',
            '1-800-444-3899',
            '770-667-5085 x 15',
            '770-667-5085 ex 15',
            '770-667-5085 ext 15',
            '770 - 667 - 5085 extension 15',
            '770-667-5085x15',
            '770-667-5085ex15',
            '770-667-5085ext15',
            '770-667-5085extension 15',
            '(+01)9094444444extension 15',
            '1-800-444-3899 x 90'
        );

        $invalidPhoneNumbers = array(
            '770-667-5085 (Cell)',
            'AAA-BBB-CCCC',
            'ThisIsNotAPhoneNumber x 15',
            '801 East Street #12',
            '301 Glendale Road ext 504',
            '/index.php'
        );

        foreach ($validPhoneNumbers as $key => $value)
        {
            $this->assertTrue(
                StringUtility::isPhoneNumber($value),
                sprintf("'%s' should be recognized as a phone number", $value)
            );
        }

        foreach ($invalidPhoneNumbers as $key => $value)
        {
            $this->assertFalse(
                StringUtility::isPhoneNumber($value),
                sprintf("'%s' should not be recognized as a phone number", $value)
            );
        }
    }

    /* Tests for containsPhoneNumber(). */
    function testContainsPhoneNumber()
    {
        $validStrings = array(
            '770-667-5085 (Cell)',
            'My phone number is 770-667-5085.',
            'Cell:770-667-5085.',
            'ph7706675085'
        );

        $invalidStrings = array(
            'My phone number is 770-667-508.',
            'ph770667509'
        );

        foreach ($validStrings as $key => $value)
        {
            $this->assertTrue(
                StringUtility::containsPhoneNumber($value),
                sprintf("'%s' should be recognized as containing a phone number", $value)
            );
        }

        foreach ($invalidStrings as $key => $value)
        {
            $this->assertFalse(
                StringUtility::containsPhoneNumber($value),
                sprintf("'%s' should not be recognized as containing a phone number", $value)
            );
        }

        /* Some sample text to test with. */
        $fairyTale = implode('', file('./modules/tests/SampleText.txt'));

        /* I can assure you that none of Grimm's fairy tales contain phone numbers. */
        $this->assertFalse(StringUtility::containsPhoneNumber($fairyTale));
    }

    function testExtractPhoneNumber()
    {
        $phoneNumbersToExtract = array(
            array(
                '(+01)9094444444extension 15',
                '909-444-4444 x 15'
            ),
            array(
                '1-800-444-3899 x 90',
                '800-444-3899 x 90'
            ),
            array(
                '+019094444444',
                '909-444-4444'
            ),
            array(
                '7706675085',
                '770-667-5085'
            ),
            array(
                '770-667-5085 extension 15',
                '770-667-5085 x 15'
            ),
            array(
                '(770) 667/5085',
                '770-667-5085'
            ),
            array(
                '(770) 667.5085',
                '770-667-5085'
            ),
            array(
                'my phone number is (770) 667.5085extension 15, it is.',
                '770-667-5085 x 15'
            ),
            array(
                '+420466052932',
                '+420466052932'
            ),
            array(
                '+17706675085',
                '770-667-5085'
            )
        );

        foreach ($phoneNumbersToExtract as $key => $value)
        {
            $formattedPhoneNumber = StringUtility::extractPhoneNumber($value[0]);
            $this->assertTrue(
                $formattedPhoneNumber === $value[1],
                sprintf("Extracting phone number from '%s' should result in '%s'", $value[0], $value[1])
            );
        }
    }

    function testIsEmailAddress()
    {
        $validEmails = array(
            'wcc@nospammonkeys.org',
            'will.buckner [at] eggheads [dot] org',
            'will.buckner (at) eggheads (dot) org',
            'will.buckner@eggheads [dot] org',
            'will.buckner [at] eggheads.org',
            'will.buckner[AT]eggheads[DOT]org',
            'will.buckner at eggheads dot org',
            'wcc [at] lists [dot] nospammonkeys [DOT] org'
        );

        $invalidEmails = array(
            'i am at the movies dot dot dot',
            'not@valid',
            'not@valid...com',
            'my e-mail address is will.buckner [at] eggheads [dot] org'
        );

        foreach ($validEmails as $key => $value)
        {
            $this->assertTrue(
                StringUtility::isEmailAddress($value),
                sprintf("'%s' should be recognized as an e-mail address", $value)
            );
        }

        foreach ($invalidEmails as $key => $value)
        {
            $this->assertFalse(
                StringUtility::isEmailAddress($value),
                sprintf("'%s' should not be recognized as an e-mail address", $value)
            );
        }
    }

    /* Tests for containsEmailAddress(). */
    function testContainsEmailAddress()
    {
        $validStrings = array(
            'my e-mail address is will.buckner [at] eggheads [dot] org',
            'Email: will.buckner (at) eggheads (dot) org',
            'E-Mail:wcc@nospammonkeys.org',
            'E-Mail: wcc [at] lists [dot] nospammonkeys [dot] org'
        );

        $invalidStrings = array(
            'i am at the movies dot dot dot',
            'not@valid',
            'not@valid...com'
        );

        foreach ($validStrings as $key => $value)
        {
            $this->assertTrue(
                StringUtility::containsEmailAddress($value),
                sprintf("'%s' should be recognized as containing an e-mail address", $value)
            );
        }

        foreach ($invalidStrings as $key => $value)
        {
            $this->assertFalse(
                StringUtility::containsEmailAddress($value),
                sprintf("'%s' should not be recognized as containing an e-mail address", $value)
            );
        }

        /* Some sample text to test with. */
        $fairyTale = implode('', file('./modules/tests/SampleText.txt'));

        /* I can assure you that none of Grimm's fairy tales contain e-mail addresses. */
        $this->assertFalse(StringUtility::containsEmailAddress($fairyTale));
    }

    /* Tests for extractEmailAddress(). */
    function testExtractEmailAddress()
    {
        $emailAddressesToExtract = array(
            array(
                'wcc@nospammonkeys.org',
                'wcc@nospammonkeys.org'
            ),
            array(
                'wcc@lists.nospammonkeys.org',
                'wcc@lists.nospammonkeys.org'
            ),
            array(
                'wcc at nospammonkeys dot org',
                'wcc@nospammonkeys.org'
            ),
            array(
                'wcc [at] nospammonkeys [dot] org',
                'wcc@nospammonkeys.org'
            ),
            array(
                'wcc [at] lists [dot] nospammonkeys [dot] org',
                'wcc@lists.nospammonkeys.org'
            ),
            array(
                'wcc (at) nospammonkeys (dot) org',
                'wcc@nospammonkeys.org'
            ),
            array(
                'wcc.test (at) nospammonkeys (dot) org',
                'wcc.test@nospammonkeys.org'
            ),
            array(
                'wcc_test(at)nospammonkeys(dot)org',
                'wcc_test@nospammonkeys.org'
            ),
            array(
                'my e-mail address is wcc (at) no (DOT) spammonkeys (DOT) org, but thanks anyway.',
                'wcc@no.spammonkeys.org'
            )
        );

        foreach ($emailAddressesToExtract as $key => $value)
        {
            $formattedEmailAddress = StringUtility::extractEmailAddress($value[0]);
            $this->assertTrue(
                $formattedEmailAddress === $value[1],
                sprintf("Extracting e-mail address from '%s' should result in '%s'", $value[0], $value[1])
            );
        }
    }

    /* Tests for removeEmailAddress(). */
    function testRemoveEmailAddress()
    {
        $this->assertIdentical(
            StringUtility::removeEmailAddress('wcc@nospammonkeys.org', true),
            ''
        );

        $this->assertIdentical(
            StringUtility::removeEmailAddress('wcc@nospammonkeys.org', false),
            ''
        );

        $this->assertIdentical(
            StringUtility::removeEmailAddress('Will Buckner wcc@nospammonkeys.org', true),
            'Will Buckner'
        );

        $this->assertIdentical(
            StringUtility::removeEmailAddress('Will Buckner wcc@nospammonkeys.org', false),
            'Will Buckner '
        );

        $this->assertIdentical(
            StringUtility::removeEmailAddress('Will Buckner wcc@nospammonkeys.org 770.223.0123   ', true),
            'Will Buckner  770.223.0123'
        );

        $this->assertIdentical(
            StringUtility::removeEmailAddress('Will Buckner wcc@nospammonkeys.org 770.223.0123   ', false),
            'Will Buckner  770.223.0123   '
        );


        $this->assertNotIdentical(
            StringUtility::removeEmailAddress('wcc@nospammonkeys.org ', false),
            ''
        );
        $this->assertNotIdentical(
            StringUtility::removeEmailAddress(' wcc@nospammonkeys.org    ', true),
            '     '
        );
    }

    /* Tests for isCityStateZip(). */
    function disabledtestIsCityStateZip()
    {
        $validCityStateZips = array(
            'Alpharetta, GA  30004',
            'O Fallon, IL  62269',
            'My Really Long City  , MI  48048',
            'My Really Long City  , MI  48048-5404',
            'Maplewood, MN 55119-5805',
            'New Haven, MI  48048',
            'Natick, MA  01760',
            'Plano, TX  75093',
            'Sterling, VA  20164'
        );

        $invalidCityStateZips = array(
            '12345',
            'abdde',
            'Test Texas 1223',
            'Test, TX 1111',
            'PO Box 55403',
            'P.O. Box 55403',
            'Post Office Box 55403'
        );

        foreach ($validCityStateZips as $key => $value)
        {
            $this->assertTrue(
                StringUtility::isCityStateZip($value),
                sprintf("'%s' should be recognized as a 'City, State, Zip' combination", $value)
            );
        }

        foreach ($invalidCityStateZips as $key => $value)
        {
            $this->assertFalse(
                StringUtility::isCityStateZip($value),
                sprintf("'%s' should not be recognized as a 'City, State, Zip' combination", $value)
            );
        }
    }

    /* Tests for removeEmptyLines(). */
    function testRemoveEmptyLines()
    {
        $this->assertIdentical(
            StringUtility::removeEmptyLines(
                "  	\n				\r\n		\r\n	\n                    "
            ),
            ''
        );

        $this->assertIdentical(
            StringUtility::removeEmptyLines(
                "  	\n			Will Buckner	\r\n		\r\n	\n                    "
            ),
            'Will Buckner'
        );

        $this->assertIdentical(
            StringUtility::removeEmptyLines("\n\r\n\r\n\n"),
            ''
        );

        $this->assertNotIdentical(
            StringUtility::removeEmptyLines("\n\ra\n\r\n\n"),
            ''
        );
    }

    /* Tests for countTokens(). */
    function testCountTokens()
    {
        $this->assertIdentical(
            StringUtility::countTokens(',', '1,2,3,4,5'),
            5
        );
        $this->assertIdentical(
            StringUtility::countTokens(' ', '1 2 3 4 5'),
            5
        );
        $this->assertIdentical(
            StringUtility::countTokens(', -/', '1 2-3,4/5'),
            5
        );
        $this->assertIdentical(
            StringUtility::countTokens('*%', '*One%Two**Three%%Four*Five*'),
            5
        );
    }

    /* Tests for tokenize(). */
    function testTokenize()
    {
        $output = array(
            'Zero',
            'One',
            'Two',
            'Three',
            'Four',
            'Five'
        );

        $this->assertIdentical(
            StringUtility::tokenize(', -/', 'Zero  One Two-Three,Four/ Five'),
            $output
        );
        $this->assertIdentical(
            StringUtility::tokenize(', ', 'Zero, One, Two, Three, Four, Five'),
            $output
        );
        $this->assertIdentical(
            StringUtility::tokenize('!', 'Zero!!!!!!One!Two!Three!!Four!Five'),
            $output
        );
        $this->assertIdentical(
            StringUtility::tokenize('*%', '*Zero*One%Two**Three%%Four*Five*'),
            $output
        );

        $this->assertIdentical(
            StringUtility::tokenize('*%', 'Test'),
            array('Test')
        );
    }

    /* Tests for makeInitialName(). */
    function testMakeFirstInitialName()
    {
        $this->assertIdentical(
            StringUtility::makeInitialName('Michael', 'Zimmermann', true),
            'Zimmermann, M.'
        );
        $this->assertIdentical(
            StringUtility::makeInitialName('Michael', 'Zimmermann', true, 50),
            'Zimmermann, M.'
        );
        $this->assertIdentical(
            StringUtility::makeInitialName('Michael', 'Zimmermann', true, 10),
            'Zimmermann, M.'
        );
        $this->assertIdentical(
            StringUtility::makeInitialName('Michael', 'Zimmermann',  true, 9),
            'Zimmerman, M.'
        );
        $this->assertIdentical(
            StringUtility::makeInitialName('Michael', 'Zimmermann',  true, 1),
            'Z, M.'
        );
    }

    /* Tests for escapeSingleQuotes(). */
    function testEscapeSingleQuotes()
    {
        $this->assertIdentical(
            StringUtility::escapeSingleQuotes('Test'),
            'Test'
        );

        $this->assertIdentical(
            StringUtility::escapeSingleQuotes("'Test'"),
            "\\'Test\\'"
        );

        $this->assertIdentical(
            StringUtility::escapeSingleQuotes("'Test ' String'"),
            "\\'Test \\' String\\'"
        );
    }
}

/* Tests for VCard class. */
class VCardTest extends CATSUnitTestCase
{
    function testVersion()
    {
        $this->assertIdentical(VCard::VCARD_VERSION, '2.1');
    }

    function testVCard1()
    {
        $vCard = new vCard();

        $vCard->setName('Smith', 'John');
        $output = trim($vCard->getVCard());

        $outputLines = explode("\n", $output);
        $outputLines = array_map('trim', $outputLines);

        $this->assertIdentical($outputLines[0], 'BEGIN:VCARD');
        $this->assertIdentical($outputLines[1], 'VERSION:2.1');
        $this->assertIdentical($outputLines[2], 'N:Smith;John;;;');
        $this->assertIdentical($outputLines[3], 'FN:John Smith');

        /* Test revision timestamp. */
        $this->assertPatternIn(
            '/^REV:\d{8}T\d{6}$/',
            $outputLines[4]
        );
        $currentREVNumeric = date('YmdHis');

        $vCardREVNumeric = preg_replace('/REV:|T/', '', $outputLines[4]);

        $this->assertTrue(
            $vCardREVNumeric >= ($currentREVNumeric - 5) &&
            $vCardREVNumeric <= ($currentREVNumeric + 5),
            'REV is within +/-5 seconds of current timestamp'
        );

        $this->assertIdentical($outputLines[5], 'MAILER:CATS');
        $this->assertIdentical($outputLines[6], 'END:VCARD');

        $this->assertIdentical($vCard->getFilename(), 'John Smith.vcf');
    }

    function testVCard2()
    {
        $vCard = new vCard();

        $vCard->setOrganization('Testing, Inc.');
        $vCard->setName('Smith', 'John', 'J.', 'Mr.', 'Jr.');
        $vCard->setEmail('test@testerson.org');
        $vCard->setPhoneNumber('612-555-3000', 'CELL');
        $vCard->setTitle('Senior Tester');
        $vCard->setAddress(
            '555 Testing Dr',
            'Suite 100',
            'Testertown',
            'TN',
            '12345',
            '',
            'USA',
            '',
            'HOME'
        );
        $vCard->setNote('Test note.');
        $vCard->setURL('http://www.slashdot.org');
        $output = trim($vCard->getVCard());

        $outputLines = explode("\n", $output);
        $outputLines = array_map('trim', $outputLines);

        $this->assertIdentical($outputLines[0], 'BEGIN:VCARD');
        $this->assertIdentical($outputLines[1], 'VERSION:2.1');
        $this->assertIdentical($outputLines[2], 'ORG;ENCODING=QUOTED-PRINTABLE:Testing, Inc.');
        $this->assertIdentical($outputLines[3], 'N:Smith;John;J.;Mr.;Jr.');
        $this->assertIdentical($outputLines[4], 'FN:Mr. John J. Smith Jr.');
        $this->assertIdentical($outputLines[5], 'EMAIL;INTERNET:test@testerson.org');
        $this->assertIdentical($outputLines[6], 'TEL;CELL:612-555-3000');
        $this->assertIdentical($outputLines[7], 'TITLE;ENCODING=QUOTED-PRINTABLE:Senior Tester');
        $this->assertIdentical($outputLines[8], 'ADR;HOME;ENCODING=QUOTED-PRINTABLE:;Suite 100;555 Testing Dr;Testertown;TN;12345;USA');
        $this->assertIdentical($outputLines[9], 'ORG;ENCODING=QUOTED-PRINTABLE:Test note.');
        $this->assertIdentical($outputLines[10], 'URL:http://www.slashdot.org');

        /* Test revision timestamp. */
        $this->assertPatternIn(
            '/^REV:\d{8}T\d{6}$/',
            $outputLines[11]
        );
        $currentREVNumeric = date('YmdHis');

        $vCardREVNumeric = preg_replace('/REV:|T/', '', $outputLines[11]);

        $this->assertTrue(
            $vCardREVNumeric >= ($currentREVNumeric - 5) &&
            $vCardREVNumeric <= ($currentREVNumeric + 5),
            'REV is within +/-5 seconds of current timestamp'
        );

        $this->assertIdentical($outputLines[12], 'MAILER:CATS');
        $this->assertIdentical($outputLines[13], 'END:VCARD');

        $this->assertIdentical($vCard->getFilename(), 'John Smith.vcf');
    }
}

?>
