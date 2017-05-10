<?php 
define('ATS_BE_DIR',dirname(__FILE__));
define('ATS_FE_DIR',dirname(__FILE__).'/..');
define('ATS_CATS_DIR',dirname(__FILE__).'/..');
define('CATS_FE_DIR',dirname(__FILE__).'/..');
define('SITE_URL','http://'.$_SERVER['HTTP_HOST'].'/');
define('ATS_LOCALE_DIR', ATS_BE_DIR .'/locale');
define('ATS_HTML_ENCODING', 'UTF-8');
define('ATS_DEFAULT_LOCALE', 'pl');
define('ATS_INDEX', true);
include_once(ATS_BE_DIR.'/sys/globalFuncs.php');
include_once(ATS_BE_DIR.'/E.php');
//cho 'route:'.$_GET['route'];
if ($_GET['route']!='js'){
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
?>