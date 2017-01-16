<?php
// Here you can initialize variables that will be available to your tests
// Here you can initialize variables that will be available to your tests
define('LEGACY_ROOT', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))));
define('DATABASE_USER', 'dev');
define('DATABASE_PASS', 'dev');
define('DATABASE_NAME', 'cats_test');
define('DATABASE_HOST', 'opencatsdb:3306');
define('OFFSET_GMT', 2);
define('SQL_CHARACTER_SET', 'utf8');
define('CATS_SLAVE', false);
include_once (LEGACY_ROOT . '/vendor/autoload.php');
include_once (LEGACY_ROOT . '/code/vendor/autoload.php');
include_once (LEGACY_ROOT . '/constants.php');
include_once (LEGACY_ROOT . '/lib/Site.php');
include_once (LEGACY_ROOT . '/lib/DatabaseConnection.php');
include_once (LEGACY_ROOT . '/lib/Users.php');


