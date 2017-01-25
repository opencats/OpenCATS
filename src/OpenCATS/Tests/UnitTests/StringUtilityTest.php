<?php
use PHPUnit\Framework\TestCase;

include_once('./lib/StringUtility.php');

class StringUtilityTest extends TestCase
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
        $this->assertSame(
            StringUtility::removeEmailAddress('wcc@nospammonkeys.org', true),
            ''
            );

        $this->assertSame(
            StringUtility::removeEmailAddress('wcc@nospammonkeys.org', false),
            ''
            );

        $this->assertSame(
            StringUtility::removeEmailAddress('Will Buckner wcc@nospammonkeys.org', true),
            'Will Buckner'
            );

        $this->assertSame(
            StringUtility::removeEmailAddress('Will Buckner wcc@nospammonkeys.org', false),
            'Will Buckner '
            );

        $this->assertSame(
            StringUtility::removeEmailAddress('Will Buckner wcc@nospammonkeys.org 770.223.0123   ', true),
            'Will Buckner  770.223.0123'
            );

        $this->assertSame(
            StringUtility::removeEmailAddress('Will Buckner wcc@nospammonkeys.org 770.223.0123   ', false),
            'Will Buckner  770.223.0123   '
            );

        $this->assertNotSame(
            StringUtility::removeEmailAddress('wcc@nospammonkeys.org ', false),
            ''
            );
        $this->assertNotSame(
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
        $this->assertSame(
            StringUtility::removeEmptyLines(
                "  	\n				\r\n		\r\n	\n                    "
                ),
            ''
            );

        $this->assertSame(
            StringUtility::removeEmptyLines(
                "  	\n			Will Buckner	\r\n		\r\n	\n                    "
                ),
            'Will Buckner'
            );

        $this->assertSame(
            StringUtility::removeEmptyLines("\n\r\n\r\n\n"),
            ''
            );

        $this->assertNotSame(
            StringUtility::removeEmptyLines("\n\ra\n\r\n\n"),
            ''
            );
    }

    /* Tests for countTokens(). */
    function testCountTokens()
    {
        $this->assertSame(
            StringUtility::countTokens(',', '1,2,3,4,5'),
            5
            );
        $this->assertSame(
            StringUtility::countTokens(' ', '1 2 3 4 5'),
            5
            );
        $this->assertSame(
            StringUtility::countTokens(', -/', '1 2-3,4/5'),
            5
            );
        $this->assertSame(
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

        $this->assertSame(
            StringUtility::tokenize(', -/', 'Zero  One Two-Three,Four/ Five'),
            $output
            );
        $this->assertSame(
            StringUtility::tokenize(', ', 'Zero, One, Two, Three, Four, Five'),
            $output
            );
        $this->assertSame(
            StringUtility::tokenize('!', 'Zero!!!!!!One!Two!Three!!Four!Five'),
            $output
            );
        $this->assertSame(
            StringUtility::tokenize('*%', '*Zero*One%Two**Three%%Four*Five*'),
            $output
            );

        $this->assertSame(
            StringUtility::tokenize('*%', 'Test'),
            array('Test')
            );
    }

    /* Tests for makeInitialName(). */
    function testMakeFirstInitialName()
    {
        $this->assertSame(
            StringUtility::makeInitialName('Michael', 'Zimmermann', true),
            'Zimmermann, M.'
            );
        $this->assertSame(
            StringUtility::makeInitialName('Michael', 'Zimmermann', true, 50),
            'Zimmermann, M.'
            );
        $this->assertSame(
            StringUtility::makeInitialName('Michael', 'Zimmermann', true, 10),
            'Zimmermann, M.'
            );
        $this->assertSame(
            StringUtility::makeInitialName('Michael', 'Zimmermann',  true, 9),
            'Zimmerman, M.'
            );
        $this->assertSame(
            StringUtility::makeInitialName('Michael', 'Zimmermann',  true, 1),
            'Z, M.'
            );
    }

    /* Tests for escapeSingleQuotes(). */
    function testEscapeSingleQuotes()
    {
        $this->assertSame(
            StringUtility::escapeSingleQuotes('Test'),
            'Test'
            );

        $this->assertSame(
            StringUtility::escapeSingleQuotes("'Test'"),
            "\\'Test\\'"
            );

        $this->assertSame(
            StringUtility::escapeSingleQuotes("'Test ' String'"),
            "\\'Test \\' String\\'"
            );
    }
}
?>