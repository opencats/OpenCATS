<?php  

class CalendarRouteEnum extends RouteEnum {

	protected static $fields;
	protected static $values;

	public static function init() {
		self::$fields= array(
				/*"main" => array(
						'desc'=>'Widok główny',
						'view'=>'MainView',
						RouteEnum::frontView =>'MainView',
				),*/
				"eventToOutook" => array(
						'desc'=>'Wysyłka do Outlook',
						'view'=>'SendToOutlookView',
						RouteEnum::frontView =>'BlankView',
						'icon'=>'assets/svg/outlook.svg',
				),
				'eventSource'=>array(
						'desc'=>'Źrodło danych dla kalendarza',
						'view'=>'EventSourceJson',
						RouteEnum::frontView =>'BlankView',
				),
		);
	}
}
CalendarRouteEnum::init();
calendarRouteEnum::initValues();