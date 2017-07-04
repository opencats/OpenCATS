<?php 

class AjaxRouteEnum extends RouteEnum {

	protected static $fields;
	protected static $values;
	
	public static function init() {
		self::$fields = array(
				"main" => array(
						'desc'=>'Redir do CATS',
						'view'=>'CatsPassThrough',
						RouteEnum::frontView =>'CatsAjaxView'
				)
		);
	}
}
AjaxRouteEnum::init();
AjaxRouteEnum::initValues();
?>