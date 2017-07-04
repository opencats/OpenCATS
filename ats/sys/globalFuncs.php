<?php 

function evIncBe($name,$silent=false,$args=array()){
	if ($silent){
		@include_once(ATS_BE_DIR.'/'.$name.'.php');
	} else {
		include_once(ATS_BE_DIR.'/'.$name.'.php');
	}
}


function evCheckBe($name){
	return file_exists(ATS_BE_DIR.'/'.$name.'.php');
}

function evIncBeMany($name,$silent=false,$args=array()){
	if ($silent){
		@include(ATS_BE_DIR.'/'.$name.'.php');
	} else {
		include(ATS_BE_DIR.'/'.$name.'.php');
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
function evdd($arr,$msg=null){evPrint($arr,$msg); die();}
function evPrint($arr,$msg=null,$return=false){
	if ($return) {ob_start();}
	print($msg.'<br/>');evKrumo($arr);
	if ($return) {$ret=ob_get_contents();ob_end_clean(); return $ret;}
}

function evStrStartsWith($haystack, $needle)
{
	$length = strlen($needle);
	return (substr($haystack, 0, $length) === $needle);
}

function evStrEndsWith($haystack, $needle)
{
	$length = strlen($needle);
	if ($length == 0) {
		return true;
	}

	return (substr($haystack, -$length) === $needle);
}

function evStrContains($str,$search, $case = false){
	if (!$case){
		$search = strtolower($search);
		$str = strtolower($str);
	}
	return (strpos($str, $search) !== false);
}

function evRunOb($ob,$a1,$a2=array()){
	if (is_object($ob)){
		return evRunWithBuf($ob,$a1,$a2);
	} else {
		return evRunWithBuff($ob,$a1);
	}
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


/*function evIncBeWithEval($path,$namespace){
	//if (isset($GLOBALS['evIncBeWithEval'][$path])) return $GLOBALS['atsSingletons'][$path];	
	$code = trim(file_get_contents(ATS_BE_DIR.'/'.$path.'.php'));
	$dir = dirname(ATS_BE_DIR.'/'.$path.'.php');
	chdir($dir);
	$prefix = '<?php';
	if (substr($code, 0, strlen($prefix)) == $prefix) {
		$code = substr($code, strlen($prefix));
	}
	//vd(array(
		'$path'=>$path,
		'$namespace'=>$namespace,	
		'$code'=>$code,	
	));
	eval('namespace '.$namespace.'; '.$code);
}*/

function evGetSingleton($path,$className=null, $silent=false,$namespace=null){
	if (isset($GLOBALS['atsSingletons'][$path])) return $GLOBALS['atsSingletons'][$path];
	if (!evCheckBe($path)) return null;
	//if ($namespace==null) {
		evIncBe($path,$silent);
	//} else {
	//	evIncBeWithEval($path,$namespace);
	//}
	if ($className==null) $className=basename($path);
	if ($namespace!=null) $className = '\\'.$namespace.'\\'.$className;

	if ($silent){
		$res = @call_user_func($className.'::getInstance');
	} else {
		$res = call_user_func($className.'::getInstance');
	}
	/*vd(array(
			'$className'=>$className,
	));
	if ("EventDataHandler" == $className){
		die();
	}*/
	
	if ($res===false) return null;
	$GLOBALS['atsSingletons'][$path]=$res;
	return $res;	
}



evIncBe('sys/Controller');
function evGetControllerObj($modName,$silent=false,$namespace=null){
	switch($modName){
		case 'calendar':
			$namespace='ats';
		default:
			//noop
	}
	$modNameUC = ucfirst($modName);
	return evGetSingleton('mod/'.$modName.'/'.$modNameUC, $modNameUC, $silent,$namespace);

}

function evClassName($obj){
	$npath = explode('\\', get_class($obj));
	$name = array_pop($npath);
	//$namespace = implode('\\',$npath);
	return $name;
}

function evClassNameSpace($obj){
	$npath = explode('\\', get_class($obj));
	$name = array_pop($npath);
	$namespace = implode('\\',$npath);
	return $namespace;
}


evIncBe('sys/Model');
function evCreateObj($modName,$suffix=''){
	if (evStrContains($modName,'\\')){//with namespace
		$npath = explode('\\', $modName);
		$name = array_pop($npath);
		$lcname = lcfirst($name);
		$namespace = implode('\\',$npath);
		$path='mod/'.$lcname.'/'.ucfirst($name).$suffix;
		evIncBe($path);
		$className = $modName.$suffix;
		return new $className();
		/*$npath = explode('\\', $modName);
		$name = array_pop($npath);
		$lcname = lcfirst($name);
		$namespace = implode('\\',$npath);	
		$path='mod/'.$lcname.'/'.ucfirst($name).$suffix;	
		evIncBeWithEval($path,$namespace);
		$className = $modName.$suffix;
		return new $className();*/
	} else {
		$modNameUC = ucfirst($modName);
		$className = $modNameUC.$suffix;
		evIncBe('mod/'.$modName.'/'.$className);
		return new $className();
	}
}

function evCreateModelObj($modName){
	return evCreateObj($modName,'Model');
}

evIncBe('sys/View');
function evCreateViewObj($modName){
	return evCreateObj($modName,'View');
	/*$modNameUC = ucfirst($modName);
	$className = $modNameUC.'View';
	evIncBe('mod/'.$modName.'/'.$className);
	return new $className();*/
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

evIncBe('sys/EnumType');
evIncBe('sys/RouteEnum');
function evIndex(){
	//cho nl2br(print_r($_REQUEST,true));

	$front = evGetControllerObj('front');
	//cho 'test2';
	$front->handleRequest();
	
}


function evAtsObStart($name){
	if (!isset($GLOBALS['atsHooks'])){
		E::setupHooks();
		$GLOBALS['atsHooks']=1;
	}
	if (ATS_INDEX){
		return ob_start();
	} else {
		return true;
	}
}

function evAtsObEnd($name, $args, $out = false){
	if (ATS_INDEX){
		$output=ob_get_contents();ob_end_clean();
		//cho $output;
		//cho 'obEnd:'.$name;
		evSetGlobal($name,array(
				'output'=>$output,
				'result'=>$args
				));
		if ($out) echo $output;
	}
}

function evConvertDateMDDM($dateMD){
	if (!evStrEmpty($dateMD)){
		return substr($dateMD,3,2).'-'.substr($dateMD,0,2).'-'.substr($dateMD,6,2);
	}
	return $dateMD;
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


function evEscHtml($string){
	return htmlspecialchars($string);
}
function evEscJs($string){
	return json_encode($string);
}

function evArrDflt($arr,$key,$default){
	$result = (isset($arr[$key]))?$arr[$key]:$default;
	return $result;
}

?>