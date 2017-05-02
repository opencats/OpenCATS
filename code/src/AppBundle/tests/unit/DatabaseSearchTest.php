<?php
namespace AppBundle\tests\unit;

include_once(LEGACY_ROOT . '/lib/DatabaseConnection.php');
include_once(LEGACY_ROOT . '/lib/DatabaseSearch.php');

class DatabaseSearchTest extends DatabaseTestCase
{
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

        $db = \DatabaseConnection::getInstance();
        foreach ($tests as $test)
        {
            $this->assertSame(
                \DatabaseSearch::makeBooleanSQLWhere($test[0], $db, 'field'),
                $test[1]
            );
        }
    }
}
?>
