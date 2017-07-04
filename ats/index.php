<?php 
define('ATS_VERSION','111');

define('ATS_BE_DIR',dirname(__FILE__));
define('ATS_FE_DIR',dirname(__FILE__).'/..');
define('ATS_CATS_DIR',dirname(__FILE__).'/..');
define('CATS_FE_DIR',dirname(__FILE__).'/..');
include_once('config.local.php');
define('SITE_URL','http://'.$_SERVER['HTTP_HOST'].SITE_PATH);
define('SITE_DOMAIN',$_SERVER['HTTP_HOST']);
define('ATS_TEMP_DIR',ATS_FE_DIR.'/temp');
define('ATS_LOCALE_DIR', ATS_BE_DIR .'/locale');
define('ATS_HTML_ENCODING', 'UTF-8');
define('ATS_DEFAULT_LOCALE', 'pl');
define('ATS_INDEX', true);
include_once('/sys/globalFuncs.php');
include_once(ATS_BE_DIR.'/E.php');

//sinclude_once('config.access.php');

include_once(ATS_BE_DIR.'/locale/lang.php');

require_once(ATS_CATS_DIR.'/constants.php');

include_once(ATS_BE_DIR.'/config.access.php');



$route = (isset($_GET['route']))?$_GET['route']:null;
if ($route == 'upgrade.php'){
	$_GET['route']='cats';
	$_REQUEST['route']='cats';
}
//cho 'route:'.$_GET['route'];
if ($route!='js'){
if (ATS_INDEX){
	evIndex();
} else {
	$ajaxInd = false;
	if (isset($_GET['route'])){
		$ajaxInd=('ajax'==$_GET['route']);
	}
	include_once(ATS_CATS_DIR.(($ajaxInd)?'/ajax_cats.php':'/index_cats.php'));
}
}