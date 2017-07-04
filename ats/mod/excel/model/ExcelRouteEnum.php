<?php  

class ExcelRouteEnum extends RouteEnum {

	protected static $fields;
	protected static $values;

	public static function init() {
		self::$fields= array(
				"main" => array(
						'desc'=>'Widok główny',
						'view'=>'MainView',
						RouteEnum::frontView =>'MainView',
				),
				"excel" => array(
						'desc'=>'Widok importu danych Excel',
						'view'=>'MainView',
						RouteEnum::frontView =>'MainView',
				),
				"import" => array(
						'desc'=>'Widok importu danych Excel',
						'view'=>'ExcelImportView',
						'tab'=>'settings',
						'subTab'=>'administration',
						'incCats'=>true,
						RouteEnum::frontView =>'NMainView',
				),
				"export" => array(
						'desc'=>'Widok exportu danych Excel',
						'view'=>'ExcelExportView',
						RouteEnum::frontView =>'BlankView',
				),
				
				
				"FILES" => array('desc' => 'jajo2')
		);
	}
}
ExcelRouteEnum::init();
ExcelRouteEnum::initValues();
?>