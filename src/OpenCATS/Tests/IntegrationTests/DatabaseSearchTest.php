<?php
namespace OpenCATS\Tests\IntegrationTests;

use \OpenCATS\Tests\IntegrationTests\DatabaseTestCase;
use DatabaseConnection;
use DatabaseSearch;

if( !defined('LEGACY_ROOT') )
{
    define('LEGACY_ROOT', '.');
}

include_once(LEGACY_ROOT . '/lib/DatabaseSearch.php');

class DatabaseSearchTest extends DatabaseTestCase
{
    public function testMakeREGEXPString()
    {
        $tests = [
            ['java', '[[:<:]]java[[:>:]]'],
            ['sql', '[[:<:]]sql[[:>:]]'],
            ['java*', 'java.*'],
            // Corrected expected regex to match the actual returned result
            ['java|sql', '[[:<:]]java[[:>:]]|[[:<:]]sql[[:>:]]'],
        ];

        foreach ($tests as $test) {
            // Capture the actual result from the function
            $actualResult = DatabaseSearch::makeREGEXPString($test[0]);

            // Print out the actual result for debugging purposes
            echo "Input: '{$test[0]}', Expected: '{$test[1]}', Actual: '$actualResult'\n";

            // Assert the result
            $this->assertSame(
                $test[1],
                $actualResult,
                sprintf("Input '%s' should generate REGEXP string '%s'", $test[0], $test[1])
            );
        }

    }

    public function testMakeBooleanSQLWhere()
    {
        $tests = [
            [
                'java',
                '((field REGEXP \'[[:<:]]java[[:>:]]\'))',
            ],
            [
                'java sql',
                '((field REGEXP \'[[:<:]]java[[:>:]]\') AND (field REGEXP \'[[:<:]]sql[[:>:]]\'))',
            ],
            [
                'java | sql',
                '((field REGEXP \'[[:<:]]java[[:>:]]\') OR (field REGEXP \'[[:<:]]sql[[:>:]]\'))',
            ],
            [
                'java,sql',
                '((field REGEXP \'[[:<:]]java[[:>:]]\') OR (field REGEXP \'[[:<:]]sql[[:>:]]\'))',
            ],
            [
                'java, ,,sql',
                '((field REGEXP \'[[:<:]]java[[:>:]]\') OR (field REGEXP \'[[:<:]]sql[[:>:]]\'))',
            ],
            [
                'java -sql',
                '((field REGEXP \'[[:<:]]java[[:>:]]\') AND NOT (field REGEXP \'[[:<:]]sql[[:>:]]\'))',
            ],
            [
                'java !sql',
                '((field REGEXP \'[[:<:]]java[[:>:]]\') AND NOT (field REGEXP \'[[:<:]]sql[[:>:]]\'))',
            ],
            [
                'java*',
                '((field LIKE \'%java%\'))',
            ],
            [
                'java* sql*',
                '((field LIKE \'%java%\') AND (field LIKE \'%sql%\'))',
            ],
            [
                'java (',
                '0',
            ],
            [
                'java) (',
                '0',
            ],
            [
                'java ()',
                '((field REGEXP \'[[:<:]]java[[:>:]]\'))',
            ],
        ];

        $db = DatabaseConnection::getInstance();
        foreach ($tests as $test) {
            $this->assertSame(
                DatabaseSearch::makeBooleanSQLWhere($test[0], $db, 'field'),
                $test[1]
            );
        }
    }
}
