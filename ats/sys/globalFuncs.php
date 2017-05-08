<?php 

function evIncBe($name){
	include_once(ATS_BE_DIR.'/'.$name.'.php');
}

function evIncFe($name){
	include_once(ATS_FE_DIR.'/'.$name.'.php');
}

function evKrumo($arr){
	evIncBe('sys/krumo/class.krumo');
	return krumo($arr);
}

function str_replace_first($from, $to, $subject)
{
	$from = '/'.preg_quote($from, '/').'/';

	return preg_replace($from, $to, $subject, 1);
}

function str_ireplace_first($from, $to, $subject)
{
	$from = '/'.preg_quote($from, '/').'/i';

	return preg_replace($from, $to, $subject, 1);
}

function evStrReplace($subject, $from, $to, $once = false, $caseins = false){
	if ($caseins){
		if ($once){
			return str_ireplace_first($from, $to, $subject);
		} else {
			return str_ireplace($from, $to, $subject);
		}
	} else {
		if ($once){
			return str_replace_first($from, $to, $subject);
		} else {
			return str_replace($from, $to, $subject);
		}
	}
}

function evStrNotEmpty($vr){return ((isset($vr)) && ($vr!=''));}
function evStrEmpty($vr){return !evStrNotEmpty($vr);}
function evEmptyOrZero($vr){ return (evEmpty($vr) || $vr == '0');}
//function evd($arr,$msg=null){print($msg.'<br/>'); print "<pre>"; print_r($arr);print "</pre>";}
function evd($arr,$msg=null){evPrint($arr,$msg);}
function evPrint($arr,$msg=null,$return=false){
	if ($return) {ob_start();}
	print($msg.'<br/>');evKrumo($arr);
	if ($return) {$ret=ob_get_contents();ob_end_clean(); return $ret;}
}

function evRunWithBuf($obj,$method,$args){
	//cho 'start:'.$method;
	ob_start();	
	$result=call_user_func(array($obj,$method),$args);
	$output=ob_get_contents();
	ob_end_clean();
	//cho 'stop:'.$method;
	return array('result'=>$result,'output'=>$output);
}

function evStrToLower($str){
	return strtolower($str);
}

evIncBe('sys/Controller');
function evGetControllerObj($modName){
	$modNameUC = ucfirst($modName);
	evIncBe('mod/'.$modName.'/'.$modNameUC);
	//vd(array('evGetControllerObj'=>'mod/'.$modName.'/'.$modNameUC));
	$res = call_user_func($modNameUC.'::getInstance');
	return ($res===false)?null:$res;
}

evIncBe('sys/Model');
function evCreateModelObj($modName){
	$modNameUC = ucfirst($modName);
	$className = $modNameUC.'Model';
	evIncBe('mod/'.$modName.'/'.$className);
	return new $className();
}

evIncBe('sys/View');
function evCreateViewObj($modName){
	$modNameUC = ucfirst($modName);
	$className = $modNameUC.'View';
	evIncBe('mod/'.$modName.'/'.$className);
	return new $className();
}

function evSetGlobal($name,$value){
	$GLOBALS[$name]=$value;
}

function evGetGlobal($name){
	return (isset($GLOBALS[$name]))?$GLOBALS[$name]:null;
}


evIncBe('sys/RouteEnum');
function evIndex(){
	//cho nl2br(print_r($_REQUEST,true));

	$front = evGetControllerObj('front');
	//cho 'test2';
	$front->handleRequest();
	
}


?>