<?php  
/**
 * attrs:
 * 	desc - opis
 *  descShort - krotki opis
 *  module - moduł/kontroler docelowy/obsługujący zawartość
 */
class FrontRouteEnum extends RouteEnum {
	
	protected static $fields;
	protected static $values;

	public static function init() {
		self::$fields= array(
				/*"main" => array(
						'desc'=>'Widok główny',
						'view'=>'MainView'
				),*/
				/*"joborders" => array(
						'desc'=>'Zlecenia',
						'subRouteEnumPath'=>'mod/joborder/model/JobOrderRouteEnum'
				),*/
				'ustawienia' => array(
						'desc'=>'Ustawienia',
						'module'=>'settings',						
				),
				"FILES" => array('desc' => 'jajo2')
		);
	}
}
FrontRouteEnum::init();
FrontRouteEnum::initValues();
?>