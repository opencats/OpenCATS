<?php 

function evIncBe($name,$silent=false,$args=array()){
	if ($silent){
		@include_once(ATS_BE_DIR.'/'.$name.'.php');
	} else {
		include_once(ATS_BE_DIR.'/'.$name.'.php');
	}
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

function evStrContains($str,$search, $case = false){
	if (!$case){
		$search = strtolower($search);
		$str = strtolower($str);
	}
	return (strpos($str, $search) !== false);
}

function evRunWithBuff($callable,$args){
	//cho 'start:'.$method;
	//vd(array(
	//	'$callable'=>$callable,
	//	'$args'=>$args	
	//));
	
	ob_start();
	$result=call_user_func($callable,$args);
	$output=ob_get_contents();
	ob_end_clean();
	//cho 'stop:'.$method;
	return array('result'=>$result,'output'=>$output);
	
}

function evRunWithBuf($obj,$method,$args){
	$callable = array($obj,$method); 
	return evRunWithBuff($callable,$args);
}

function evStrToLower($str){
	return strtolower($str);
}

evIncBe('sys/Controller');
function evGetControllerObj($modName,$silent=false){
	$modNameUC = ucfirst($modName);
	evIncBe('mod/'.$modName.'/'.$modNameUC,$silent);
	//vd(array('evGetControllerObj'=>'mod/'.$modName.'/'.$modNameUC));
	if ($silent){
		$res = @call_user_func($modNameUC.'::getInstance');
	} else {
		$res = call_user_func($modNameUC.'::getInstance');
	}
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

function evGetExecTime($_startTime)
{
	$_endTime = microtime();

	if (!isset($_startTime) || empty($_startTime))
	{
		$_startTime = $_endTime;
	}

	list($a_dec, $a_sec) = explode(' ', $_startTime);
	list($b_dec, $b_sec) = explode(' ', $_endTime);

	$duration = $b_sec - $a_sec + $b_dec - $a_dec;
	$duration = sprintf('%0.2f', $duration);

	return $duration;
}


evIncBe('sys/RouteEnum');
function evIndex(){
	//cho nl2br(print_r($_REQUEST,true));

	$front = evGetControllerObj('front');
	//cho 'test2';
	$front->handleRequest();
	
}


function evAtsObStart($name){
	if (ATS_INDEX){
		return ob_start();
	} else {
		return true;
	}
}

function evAtsObEnd($name, $args){
	if (ATS_INDEX){
		$output=ob_get_contents();ob_end_clean();
		//cho $output;
		//cho 'obEnd:'.$name;
		evSetGlobal($name,array(
				'output'=>$output,
				'result'=>$args
				));
	}
}

function evConvertDateDbToTime($dateDb){
	return substr($dateDb,11,5);
}

function evConvertDateDbToDate($dateDb){
	return substr($dateDb,8,2).'-'.substr($dateDb,5,2).'-'.substr($dateDb,2,2);
}

function evConvertDateDbToDateTime($dateDb){
	return evConvertDateDbToDate($dateDb).' '.evConvertDateDbToTime($dateDb);
}

function evCatsTimeToUTime($activityHour,$activityMinute,$activityAMPM){
	
/*
 *           // handle 24h
            if (intval($hour)>11){
            	$hour = intval($hour)-12;
            	$meridiem='PM';
            }
 */	
	
	$h = $activityHour * 1;
	$time = null;
	if ($h > 11) {
		$time = strtotime(
				sprintf('%s:%s', $activityHour, $activityMinute,$activityAMPM)
				);
	} else {
		$time = strtotime(
				sprintf('%s:%s %s', $activityHour, $activityMinute, 'AM')
				);
	}
	return $time;
}

evIncBe('sys/EnumTypeEnum');
/*function evIncEnum($enumTypeEnum){
	$path=$enumTypeEnum->getDefPath();
	return evIncBe($path);
}*/

//vd(array('EnumTypeEnum::values'=>EnumTypeEnum::values()));
//f//oreach(EnumTypeEnum::values() as $k =>$v){
//	evIncEnum($v);
//	$values = call_user_func(ucfirst($v->name()).'Enum::values');
//	evDbg($values);
//}
function evShowJs($args){
	$jsFlagPhpNoHeader=1;
	include_once(ATS_FE_DIR.'/'.$args['path'].'.js');
}

evIncBe('lib/Minifier');
function evGetJs($path){
	$result = evRunWithBuff("evShowJs",array('path'=>$path));
	$minifiedCode = \JShrink\Minifier::minify($result['output'], array('flaggedComments' => false));
	return $minifiedCode;
}


?>