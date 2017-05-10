<?php 

class E {
	
private static $cache=array();

public static function ui($name=null){
	if ($name==null) return EnumTypeEnum::uiType();
	if (!isset(self::$cache['ui.'.$name])){
		self::$cache['ui.'.$name]=EnumTypeEnum::uiType()->enumValueOf($name);
	}
	return self::$cache['ui.'.$name];
}


}

?>