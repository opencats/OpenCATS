<?php 

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