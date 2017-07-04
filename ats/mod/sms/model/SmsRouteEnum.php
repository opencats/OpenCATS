<?php  

class SmsRouteEnum extends RouteEnum {

	protected static $fields;
	protected static $values;

	public static function init() {
		self::$fields= array(
				/*"main" => array(
						'desc'=>'Widok główny',
						'view'=>'MainView',
						RouteEnum::frontView =>'MainView',
				),*/
				"send" => array(
						'desc'=>'Wyślij wiadomość SMS',
						'view'=>'SendFormView',
						RouteEnum::frontView =>'ModalView',
						'icon'=>'assets/svg/smartphone.svg',
				),
				"sendAction" => array(
						'desc'=>'Stan wysyłki SMS',
						'view'=>'SendActionView',
						RouteEnum::frontView =>'ModalView',
				),
				"messageStatus" => array(
						'desc'=>'Stan wysyłki SMS',
						'view'=>'messageStatus',
						RouteEnum::frontView =>'ModalView',
				),
				
		);
	}
}
SmsRouteEnum::init();
SmsRouteEnum::initValues();
?>