<?php 
//$cc = E::controller('cats');
//$cc->incCats();
ob_start();
chdir(CATS_FE_DIR);
include_once('./index_cats.php');
$cntCats=ob_get_contents();
ob_end_clean();

ob_start();
$isModal = evGetGlobal('isModal');
//vd(array('$isModal'=>$isModal));
if ($isModal){
	echo evGetGlobal('printModalHeader')['output'];
} else {
	echo evGetGlobal('printHeader')['output'];
	echo evGetGlobal('printHeaderBlock')['output'];
	echo evGetGlobal('printTabs')['output'];
} 
 echo $args['content'];
 echo evGetGlobal('printFooter')['output'];
//cho evGetGlobal('myProfile')['output'];
 $cnt=ob_get_contents();
 ob_end_clean();
 
function tplFilterOutput($args){
 	$content = $args['content'];
 	$content = evStrReplace($content,'src="js/','src="'.SITE_PATH.'js/');
 	$content = evStrReplace($content,'src="images/','src="'.SITE_PATH.'images/');
 	$content = evStrReplace($content,'src="locale/','src="'.SITE_PATH.'locale/');
 
 	$content = evStrReplace($content,'href="not-ie.css','href="'.SITE_PATH.'not-ie.css');
 	$content = evStrReplace($content,'import "main.css','import "'.SITE_PATH.'main.css');
 	$content = evStrReplace($content,'href="ie.css','href="'.SITE_PATH.'ie.css');
 	$content = evStrReplace($content,'href="index.php','href="'.SITE_PATH.'index.php');
 	return $content;
  }
  
 echo tplFilterOutput(array(
 		'content'=>$cnt
 ));