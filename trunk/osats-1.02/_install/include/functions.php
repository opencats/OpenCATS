<?php
/* OSATS Functions for Installation
*/

error_reporting(2039);
set_magic_quotes_runtime(0);
init_wiz();

//---------------------------------------------------------------------------------------//

function init_wiz() {
global $_COOKIE, $_POST, $_GET, $_SERVER, $_ENV;
echo "<H2>Welcome to the OSATS Installation.</H2>";
$globals_test = @ini_get('register_globals');
if ( isset($globals_test) && empty($globals_test) ) {
	if ( !empty($_GET) )  { extract($_GET, EXTR_SKIP);  }
	if ( !empty($_POST) ) { extract($_POST, EXTR_OVERWRITE); }
	define('_GLOBALS', FALSE);
	} else {
		define('_GLOBALS', TRUE);
	}

define('_PHP_SELF', $_SERVER['PHP_SELF']);

!empty($_SERVER['HTTP_HOST'])       ? define('_HTTP_HOST'      , $_SERVER['HTTP_HOST'])       : define('_HTTP_HOST'      , $_ENV['HTTP_HOST']);
!empty($_SERVER['QUERY_STRING'])    ? define('_QUERY_STRING'   , $_SERVER['QUERY_STRING'])    : define('_QUERY_STRING'   , $_ENV['QUERY_STRING']);
!empty($_SERVER['SCRIPT_NAME'])     ? define('_SCRIPT_NAME'    , $_SERVER['SCRIPT_NAME'])     : define('_SCRIPT_NAME'    , $_ENV['SCRIPT_NAME']);
!empty($_SERVER['HTTP_REFERER'])    ? define('_HTTP_REFERER'   , $_SERVER['HTTP_REFERER'])    : define('_HTTP_REFERER'   , $_ENV['HTTP_REFERER']);
!empty($_SERVER['REQUEST_METHOD'])  ? define('_REQUEST_METHOD' , $_SERVER['REQUEST_METHOD'])  : define('_REQUEST_METHOD' , $_ENV['REQUEST_METHOD']);
!empty($_SERVER['HTTP_USER_AGENT']) ? define('_HTTP_USER_AGENT', $_SERVER['HTTP_USER_AGENT']) : define('_HTTP_USER_AGENT', $_ENV['HTTP_USER_AGENT']);

$R_URI = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_ENV['REQUEST_URI'];
!empty($R_URI) ? define('_REQUEST_URI', $R_URI) : define('_REQUEST_URI', _PHP_SELF._QUERY_STRING);

switch(strtoupper(PHP_OS)) {
	case 'WINDOWS':
	case 'CYGWIN':
	case 'WINNT':
	case 'WIN32':
		define('_OS', 'W');
		break;

	case 'DARWIN':
	case 'OSX':
		define('_OS', 'M');
		break;

	default:
		define('_OS', 'U');
}

$base_path = str_replace('\\', '/', getcwd());
if ( substr($base_path, -1) == '/') {
	$base_path = substr($base_path, 0, -1);
}
define('WIZ_PATH', $base_path);
if ( !defined('OSATS_ROOT_PATH') &&  OSATS_ROOT_PATH != '' ) {
	define('OSATS_ROOT_PATH', preg_replace("/\/_install.*/i", "", WIZ_PATH));
}

$parts = pathinfo(_PHP_SELF);
define('BASE_URL', preg_replace("/\/_install.*/i", "", $parts["dirname"]));
if ( !defined('OSATS_URL') && OSATS_URL != '' ) {
	$root_url = "http://" . _HTTP_HOST. preg_replace("'/_install/install\.php$'i", "", _PHP_SELF);

define("OSATS_URL", $root_url);
}

$lang = !empty($_POST['lang']) ? $_POST['lang'] : $_COOKIE['lang'];

//include_once(WIZ_PATH."/language/english/global.php");
//include_once(WIZ_PATH."/language/english/admin.php");
//include_once(WIZ_PATH."/language/english/user.php");

}

//---------------------------------------------------------------------------------------//
/**
* used for checking php and sql version...
*/

function version_check($min, $curr) {

$testVer = intval( str_replace(".", "", $min) );
$curVer  = intval( str_replace(".", "", $curr ) );
if( $curVer < $testVer ) {
	return false;
	} else {
		return true;
	}
}

?>
