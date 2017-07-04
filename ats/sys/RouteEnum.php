<?php 

abstract class RouteEnum extends EnumType {
	
	const frontView = 'frontView';
	const view = 'view';
	const desc = 'desc';
	
	public function getFrontView(){
		return $this->getAttr(RouteEnum::frontView);
	} 

	function getSubRoute($name){
		$path = $this->getAttr('subRouteEnumPath');
		evIncBe($path);
		$cname = basename($path);
		$ccname=$cname.'::hasName';
		$value=null;
		if (call_user_func($ccname,$name)){
			$fcname=$cname.'::'.$name;
			$value = call_user_func($fcname);
		}
		return $value;
	}
	
}
?>