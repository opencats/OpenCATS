<?php 
//$glxml = &evGetGlobal()
//vd($args);
//$gl = evGetGlobal('ouXmlViews');
if (!isset($GLOBALS['ouXmlViews'])) $GLOBALS['ouXmlViews']=array();
$GLOBALS['ouXmlViews'][]=$args;

?>