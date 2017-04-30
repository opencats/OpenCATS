<?php
// Here you can initialize variables that will be available to your tests
define('LEGACY_ROOT', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))));
define('DATABASE_USER', 'dev');
define('DATABASE_PASS', 'dev');
define('DATABASE_NAME', 'cats_unittestdb');
define('DATABASE_HOST', 'unittestdb');
define('OFFSET_GMT', 2);
define('SQL_CHARACTER_SET', 'utf8');
define('CATS_SLAVE', false);

include_once (LEGACY_ROOT . '/vendor/autoload.php');
include_once (LEGACY_ROOT . '/code/vendor/autoload.php');
include_once (LEGACY_ROOT . '/constants.php');
$canConnectAndSelectDb = false;
$count = 30;
while (!$canConnectAndSelectDb && $count > 0)
{
    $connection = @mysql_connect(
        DATABASE_HOST, DATABASE_USER, DATABASE_PASS
    );
    if ($connection)
    {
        $canConnectAndSelectDb = true;
    }
    sleep(1);
    --$count;
}
if (!$canConnectAndSelectDb) {
    echo "Timeout while waiting for the DB and database.\n";
}
