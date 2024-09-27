<?php

use PHPUnit\Framework\TestCase;

if (!defined('LEGACY_ROOT')) {
    define('LEGACY_ROOT', '.');
}

include_once(LEGACY_ROOT . '/lib/StringUtility.php');

class VCardTest extends TestCase
{
    /* Tests for isURL(). */
    public function testIsURL(): void
    {
        $validURLs = [
            'http://www.nospammonkeys.org',
            'ftp://ftp.eggheads.org/wcc/test.txt',
            'http://wcc:test@www.nospammonkeys.org',
            'http://test@www.nospammonkeys.org',
            'http://www.eggheads.org:80/~wcc/test.txt',
            'http://wcc:test@www.nospammonkeys.org:80/q.php?test=1&test2=bl+ah',
            'http://wcc:test@www.nospammonkeys.org:80/q.php?test=1&test2=/blah@blah.com',
            'www.cognizo.com/index.php',
            'http://24.72.64.156/index.php',
            'localhost/index.php',
        ];

        $invalidURLs = [
            '770-667-5085',
            'nntp://129.222.2532.5/index.php',
            '/index.php',
            'My web site is http://www.microsoft.com/index.php and this is a test sentence.',
        ];

        foreach ($validURLs as $value) {
            $this->assertTrue(
                StringUtility::isURL($value),
                sprintf("'%s' should be recognized as a URL", $value)
            );
        }

        foreach ($invalidURLs as $value) {
            $this->assertFalse(
                StringUtility::isURL($value),
                sprintf("'%s' should not be recognized as a URL", $value)
            );
        }
    }

    /* Tests for extractURL(). */
    public function testExtractURL(): void
    {
        $URLsToExtract = [
            [
                'http://wcc:test@www.nospammonkeys.org:80/q.php?test=1&test2=/blah@blah.com',
                'http://wcc:test@www.nospammonkeys.org/q.php?test=1&test2=/blah@blah.com',
            ],
            [
                'http://wcc@www.nospammonkeys.org:80/q.php?test=1&test2=/blah@blah.com',
                'http://wcc@www.nospammonkeys.org/q.php?test=1&test2=/blah@blah.com',
            ],
            [
                'www.cognizo.com/index.php',
                'http://www.cognizo.com/index.php',
            ],
            [
                '24.72.64.156/index.php',
                'http://24.72.64.156/index.php',
            ],
        ];

        foreach ($URLsToExtract as $value) {
            $formattedURL = StringUtility::extractURL($value[0]);
            $this->assertSame(
                $value[1],
                $formattedURL,
                sprintf("Extracting URL from '%s' should result in '%s'", $value[0], $value[1])
            );
        }
    }

    /* Tests for isPhoneNumber(). */
    public function testIsPhoneNumber(): void
    {
        $validPhoneNumbers = [
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
            '1-800-444-3899 x 90',
        ];

        $invalidPhoneNumbers = [
            '770-667-5085 (Cell)',
            'AAA-BBB-CCCC',
            'ThisIsNotAPhoneNumber x 15',
            '801 East Street #12',
            '301 Glendale Road ext 504',
            '/index.php',
        ];

        foreach ($validPhoneNumbers as $value) {
            $this->assertTrue(
                StringUtility::isPhoneNumber($value),
                sprintf("'%s' should be recognized as a phone number", $value)
            );
        }

        foreach ($invalidPhoneNumbers as $value) {
            $this->assertFalse(
                StringUtility::isPhoneNumber($value),
                sprintf("'%s' should not be recognized as a phone number", $value)
            );
        }
    }

    /* Tests for containsPhoneNumber(). */
    public function testContainsPhoneNumber(): void
    {
        $validStrings = [
            '770-667-5085 (Cell)',
            'My phone number is 770-667-5085.',
            'Cell:770-667-5085.',
            'ph7706675085',
        ];

        $invalidStrings = [
            'My phone number is 770-667-508.',
            'ph770667509',
        ];

        foreach ($validStrings as $value) {
            $this->assertTrue(
                StringUtility::containsPhoneNumber($value),
                sprintf("'%s' should be recognized as containing a phone number", $value)
            );
        }

        foreach ($invalidStrings as $value) {
            $this->assertFalse(
                StringUtility::containsPhoneNumber($value),
                sprintf("'%s' should not be recognized as containing a phone number", $value)
            );
        }
    }

    /* Tests for extractPhoneNumber(). */
    public function testExtractPhoneNumber(): void
    {
        $phoneNumbersToExtract = [
            [
                '(+01)9094444444extension 15',
                '909-444-4444 x 15',
            ],
            [
                '1-800-444-3899 x 90',
                '800-444-3899 x 90',
            ],
            [
                '+019094444444',
                '909-444-4444',
            ],
            [
                '7706675085',
                '770-667-5085',
            ],
            [
                '770-667-5085 extension 15',
                '770-667-5085 x 15',
            ],
            [
                '(770) 667/5085',
                '770-667-5085',
            ],
            [
                '(770) 667.5085',
                '770-667-5085',
            ],
            [
                'my phone number is (770) 667.5085extension 15, it is.',
                '770-667-5085 x 15',
            ],
            [
                '+420466052932',
                '+420466052932',
            ],
            [
                '+17706675085',
                '770-667-5085',
            ],
        ];

        foreach ($phoneNumbersToExtract as $value) {
            $formattedPhoneNumber = StringUtility::extractPhoneNumber($value[0]);
            $this->assertSame(
                $value[1],
                $formattedPhoneNumber,
                sprintf("Extracting phone number from '%s' should result in '%s'", $value[0], $value[1])
            );
        }
    }

    /* Tests for isEmailAddress(). */
    public function testIsEmailAddress(): void
    {
        $validEmails = [
            'wcc@nospammonkeys.org',
            'will.buckner [at] eggheads [dot] org',
            'will.buckner (at) eggheads (dot) org',
            'will.buckner@eggheads [dot] org',
            'will.buckner [at] eggheads.org',
            'will.buckner[AT]eggheads[DOT]org',
            'will.buckner at eggheads dot org',
            'wcc [at] lists [dot] nospammonkeys [DOT] org',
        ];

        $invalidEmails = [
            'i am at the movies dot dot dot',
            'not@valid',
            'not@valid...com',
            'my e-mail address is will.buckner [at] eggheads [dot] org',
        ];

        foreach ($validEmails as $value) {
            $this->assertTrue(
                StringUtility::isEmailAddress($value),
                sprintf("'%s' should be recognized as an e-mail address", $value)
            );
        }

        foreach ($invalidEmails as $value) {
            $this->assertFalse(
                StringUtility::isEmailAddress($value),
                sprintf("'%s' should not be recognized as an e-mail address", $value)
            );
        }
    }

    /* Tests for containsEmailAddress(). */
    public function testContainsEmailAddress(): void
    {
        $validStrings = [
            'my e-mail address is will.buckner [at] eggheads [dot] org',
            'Email: will.buckner (at) eggheads (dot) org',
            'E-Mail:wcc@nospammonkeys.org',
            'E-Mail: wcc [at] lists [dot] nospammonkeys [dot] org',
        ];

        $invalidStrings = [
            'i am at the movies dot dot dot',
            'not@valid',
            'not@valid...com',
        ];

        foreach ($validStrings as $value) {
            $this->assertTrue(
                StringUtility::containsEmailAddress($value),
                sprintf("'%s' should be recognized as containing an e-mail address", $value)
            );
        }

        foreach ($invalidStrings as $value) {
            $this->assertFalse(
                StringUtility::containsEmailAddress($value),
                sprintf("'%s' should not be recognized as containing an e-mail address", $value)
            );
        }
    }

    /* Tests for extractEmailAddress(). */
    public function testExtractEmailAddress(): void
    {
        $emailAddressesToExtract = [
            [
                'wcc@nospammonkeys.org',
                'wcc@nospammonkeys.org',
            ],
            [
                'wcc@lists.nospammonkeys.org',
                'wcc@lists.nospammonkeys.org',
            ],
            [
                'wcc at nospammonkeys dot org',
                'wcc@nospammonkeys.org',
            ],
            [
                'wcc [at] nospammonkeys [dot] org',
                'wcc@nospammonkeys.org',
            ],
            [
                'wcc [at] lists [dot] nospammonkeys [dot] org',
                'wcc@lists.nospammonkeys.org',
            ],
            [
                'wcc (at) nospammonkeys (dot) org',
                'wcc@nospammonkeys.org',
            ],
            [
                'wcc.test (at) nospammonkeys (dot) org',
                'wcc.test@nospammonkeys.org',
            ],
            [
                'wcc_test(at)nospammonkeys(dot)org',
                'wcc_test@nospammonkeys.org',
            ],
            [
                'my e-mail address is wcc (at) no (DOT) spammonkeys (DOT) org, but thanks anyway.',
                'wcc@no.spammonkeys.org',
            ],
        ];

        foreach ($emailAddressesToExtract as $value) {
            $formattedEmailAddress = StringUtility::extractEmailAddress($value[0]);
            $this->assertSame(
                $value[1],
                $formattedEmailAddress,
                sprintf("Extracting e-mail address from '%s' should result in '%s'", $value[0], $value[1])
            );
        }
    }

    /* Tests for removeEmailAddress(). */
    public function testRemoveEmailAddress(): void
    {
        $this->assertSame(
            StringUtility::removeEmailAddress('wcc@nospammonkeys.org', true),
            ''
        );

        $this->assertSame(
            StringUtility::removeEmailAddress('Will Buckner wcc@nospammonkeys.org', true),
            'Will Buckner'
        );

        $this->assertSame(
            StringUtility::removeEmailAddress('Will Buckner wcc@nospammonkeys.org 770.223.0123   ', true),
            'Will Buckner  770.223.0123'
        );
    }

    /* Tests for removeEmptyLines(). */
    public function testRemoveEmptyLines(): void
    {
        $this->assertSame(
            StringUtility::removeEmptyLines(
                "   \n              \r\n        \r\n    \n                    "
            ),
            ''
        );

        $this->assertSame(
            StringUtility::removeEmptyLines(
                "   \n          Will Buckner    \r\n        \r\n    \n                    "
            ),
            'Will Buckner'
        );

        $this->assertSame(
            StringUtility::removeEmptyLines("\n\r\n\r\n\n"),
            ''
        );
    }

    /* Tests for countTokens(). */
    public function testCountTokens(): void
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
    }

    /* Tests for tokenize(). */
    public function testTokenize(): void
    {
        $output = [
            'Zero',
            'One',
            'Two',
            'Three',
            'Four',
            'Five',
        ];

        $this->assertSame(
            StringUtility::tokenize(', -/', 'Zero  One Two-Three,Four/ Five'),
            $output
        );

        $this->assertSame(
            StringUtility::tokenize(', ', 'Zero, One, Two, Three, Four, Five'),
            $output
        );

        $this->assertSame(
            StringUtility::tokenize('*%', '*Zero*One%Two**Three%%Four*Five*'),
            $output
        );
    }

    /* Tests for makeInitialName(). */
    public function testMakeFirstInitialName(): void
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
    }

    /* Tests for escapeSingleQuotes(). */
    public function testEscapeSingleQuotes(): void
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
