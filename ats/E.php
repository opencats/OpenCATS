<?php 

class E {
	
private static $cache=array();

/**
 * TODO:optimize
 * @param unknown $prefix
 * @param unknown $name
 * @return mixed
 */
private static function _getEnum($prefix,$name){
	$cname = 'EnumTypeEnum::'.$prefix;
	$enumTypeEnum = call_user_func($cname);
	if ($name==null) return $enumTypeEnum;
	$cacheKey = $prefix.'.'.$name;
	if (!isset(self::$cache[$cacheKey])){
		self::$cache[$cacheKey]=$enumTypeEnum->enumValueOf($name);
	}
	return self::$cache[$cacheKey];
} 

public static function ui($name=null){
	return self::_getEnum('uiType',$name);
}

public static function uiO($args,$def=null){
	if (is_array($args)){
		$name = $args['name'];
		$def = $args['def'];
		$template = evArrDflt($args,'template','default');
	} else {
		$name = $args;
		$template = evArrDflt($def,'template','default');
	}
	$uit = self::ui($name);
	return $uit->byTemplate($template,$def);
}

public static function activityStatus(){
	return EnumTypeEnum::activityStatus();
}

public static function jobOrderType(){
	return EnumTypeEnum::jobOrderType();
}

public static function dataItemType($name=null){
	return self::_getEnum('dataItemType',$name);
}
public static function dataItemTypes(){
	return EnumTypeEnum::dataItemType()->enumValues();
}

public static function dataItemFieldType($name=null){
	return self::_getEnum('dataItemFieldType',$name);
}


public static function enum($typeName, $name=null){
	return self::_getEnum($typeName,$name);
}
//deprecated
public static function getEnum($typeName, $name){
	return self::_getEnum($typeName,$name);
}

public static function routeEnum($route){
	$ra=explode('/',$route);
	$module=$ra[0];
	$name=$ra[1];
	$classname = evStrToLower($module);
	$className = ucfirst($module);
	evIncBe('mod/'.$classname.'/model/'.$className.'RouteEnum');
	$result = null;
	$fcname = $className.'RouteEnum';
	if (call_user_func($fcname.'::hasName',$name)){
		$result = call_user_func($fcname.'::'.$name);
	}
	return $result;	
} 

public static function db(){
	return evGetControllerObj('db');
}

public static function front(){
	return evGetControllerObj('front');
}

public static function settings(){
	return evGetControllerObj('settings');
}

public static function c($name){
	return E::controller($name);
}

public static function controller($name){
	return evGetControllerObj($name);
}

/*public static function loadFieldValuesForAdd($args){
	$db=E::db();
	return $db->fetchNew($args);
}*/

public static function loadCustomFieldValues($args){
	$dataItemType = $args['dataItemType'];
	$id = $args['id'];
	//$values=$args['values'];
	$siteId=$args['siteId'];
	
	$db=E::db();
	$result = $db->select(array(
			'id'=>$id,
			//'siteId'=>$siteId,
			'dataItemType'=>$dataItemType,
	));
	return $db->fetch($result);
}

public static function setFieldValues($args){
	return self::setCustomFieldValues($args);
}

public static function setCustomFieldValues($args){	
	$dataItemType = $args['dataItemType'];
	$id = $args['id'];
	$values=$args['values'];
	$siteId=evArrDflt($args,'siteId',null);
	$template=(isset($args['template']))?$args['template']:null;
	
	$db=E::db();
	
	//vd($args);
	//ie();
	
	return $db->update(array(
			'id'=>$id,
			'dataItemType'=>$dataItemType,
			'fs'=>$values,
			'template'=>$template,
	));
	

	
}

public static function showFields($args){
	return self::showCustomFields($args);
}

public static function showCustomFields($args){

	E::uiO('customFields',$args);
	/*E::ui('customFields')->byTemplate($template,array(
			'sectionTitle'=>$sectionTitle,
			'fieldDefs'=>$fieldsAsoc,
			'dataItemType'=>$dataItemType,
			'fl'=>$fl,
			'template'=>$template,
	));*/	
}


public static function showCustomFieldsBe($args){
	$type = $args['data']['type'];
	$dataItemType = E::dataItemType()->byAttr('dbValue',$type);
	$args['$dataItemType']=$dataItemType;
	$dataItemName = $dataItemType->name();
	$customFields = $dataItemType->customFields;
	$template = 'be';
	E::ui('customFields')->byTemplate($template,array(
			'fieldDefs'=>$customFields,
			'dataItemType'=>$dataItemType,
	));
	

	//vd($args);
	

	
}

/**
 * Temporary off in  hooks
 * @param unknown $args
 */
public static function drawActions($args){
	$front = E::front();
	$actionRoute = $front->getActionRoute();
	$obj=$front->getObject();
	if ($actionRoute!=null){
		$actionArray = $actionRoute->actions;
		$idVar = $actionRoute->idVar;
		$idVal = ($idVar!=null && isset($_GET[$idVar]))?$_GET[$idVar]:null;
		if (is_array($actionArray)){
			foreach($actionArray as $k =>$v){
				$bShow = true;
				if (isset($v['cond'])) {
					$bShow=eval($v['cond']);
				}
				if ($bShow){
					$link = $v['link'];
					if ($idVal!=null) $link = evStrReplace($link,'[id]',$idVal);
					$onclick = (isset($v['onclick']))?$v['onclick']:'';
					if ($idVal!=null) $onclick = evStrReplace($onclick,'[id]',$idVal);
					echo '<li><a href="'.$link.'" onclick="'.$onclick.'"><img src="'.$v['iconHref'].'" class="absmiddle" alt="'.$v['desc'].'" width="16" border="0" height="16">&nbsp;'.$v['desc'].'</a></li>';			
				}
			}
		}
	}
		
	//cho '<div style="position:absolute;top:300px;">';
	//vd($actionArray);
	//cho '</div>';
	//return true;
}
public static function setupHooks(){
	//$_SESSION['hooks']['TEMPLATE_UTILITY_DRAW_SUBTABS']=array();
	//$_SESSION['hooks']['TEMPLATE_UTILITY_DRAW_SUBTABS'][]='E::drawActions(array(\'active\'=>$active,\'subActive\'=>$subActive));';
}

public static function ctHref($route){
	$a=explode('/',$route);
	return SITE_URL.'index.php?m='.$a[0].((isset($a[1]))?('&a='.$a[1]):'');
}

public static function href($args){
	$route = $args['route'];
	return 'index.php?route='.$route;
}

public static function routeHref($route){
	return /*SITE_PATH.*/$route;
}

public static function routeHrefO($route){
	echo self::routeHref($route);
}

}

?>