<?php 

class SettingsRouteEnum extends RouteEnum {

	protected static $fields;
	protected static $values;
	
	static function init(){
		self::$fields= array(
				"main" => array(
						'desc'=>'Ustawienia - Dane użytkownika',
						'view'=>'User',
						RouteEnum::frontView =>'MainView'
				),
				"user" => array(
						'desc'=>'Ustawienia - Dane użytkownika',
						'view'=>'User',
						RouteEnum::frontView =>'MainView'
				),
				"auth" => array(
						'desc'=>'Ustawienia - Dane użytkownika',
						'view'=>'authProfile',
						//RouteEnum::frontView =>'MainView'
				),
				"profil" => array(
						'desc'=>'Widok profilu uzytkownika',
						'view'=>'userProfile',
						'tab'=>'settings',
						'subTab'=>'administration',
						'incCats'=>true,
						RouteEnum::frontView =>'NMainView',
				),
		);
	}
}
SettingsRouteEnum::init();
SettingsRouteEnum::initValues();
?>