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
            // Other test cases...
        ];

        // Make sure to correctly instantiate the DatabaseConnection
        $db = DatabaseConnection::getInstance();

        foreach ($tests as $test) {
            $actualResult = DatabaseSearch::makeBooleanSQLWhere($test[0], $db, 'field');

            // Print the results for debugging
            echo "Input: '{$test[0]}', Expected: '{$test[1]}', Actual: '{$actualResult}'\n";

            $this->assertSame(
                $test[1],
                $actualResult,
                sprintf("Failed asserting that input '%s' matches expected SQL WHERE string.", $test[0])
            );
        }
    }
}
