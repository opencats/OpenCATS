<?php 

class CatsRouteEnum extends RouteEnum {

	protected static $fields;
	protected static $values;

	public static function init() {
		self::$fields = array(
				"main" => array(
						'desc'=>'Strona główna',
						'view'=>'CatsPassThrough',
						//RouteEnum::frontView =>'NMainView'
						RouteEnum::frontView =>'CatsView'
				),
				"user" => array(
						'desc'=>'Ustawienia - Dane użytkownika',
						'view'=>'User',
						RouteEnum::frontView =>'MainView'
				)
		);
	}

}
CatsRouteEnum::init();
CatsRouteEnum::initValues();
?>