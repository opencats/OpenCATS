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
    function testMakeREGEXPString()
    {
        //FIXME: Write me!
    }

    function testMakeBooleanSQLWhere()
    {
        $tests = [['java', '((field REGEXP \'[[:<:]]java[[:>:]]\'))'], ['java sql', '((field REGEXP \'[[:<:]]java[[:>:]]\') AND (field REGEXP \'[[:<:]]sql[[:>:]]\'))'], ['java | sql', '((field REGEXP \'[[:<:]]java[[:>:]]\') OR (field REGEXP \'[[:<:]]sql[[:>:]]\'))'], ['java,sql', '((field REGEXP \'[[:<:]]java[[:>:]]\') OR (field REGEXP \'[[:<:]]sql[[:>:]]\'))'], ['java, ,,sql', '((field REGEXP \'[[:<:]]java[[:>:]]\') OR (field REGEXP \'[[:<:]]sql[[:>:]]\'))'], ['java -sql', '((field REGEXP \'[[:<:]]java[[:>:]]\') AND NOT (field REGEXP \'[[:<:]]sql[[:>:]]\'))'], ['java !sql', '((field REGEXP \'[[:<:]]java[[:>:]]\') AND NOT (field REGEXP \'[[:<:]]sql[[:>:]]\'))'], ['java*', '((field LIKE \'%java%\'))'], ['java* sql*', '((field LIKE \'%java%\') AND (field LIKE \'%sql%\'))'], ['java (', '0'], ['java) (', '0'], ['java ()', '((field REGEXP \'[[:<:]]java[[:>:]]\'))']];

        $db = DatabaseConnection::getInstance();
        foreach ($tests as $test)
        {
            $this->assertSame(
                DatabaseSearch::makeBooleanSQLWhere($test[0], $db, 'field'),
                $test[1]
                );
        }
    }
}
?>
