<?php  

class ExcelDataSetEnum extends RouteEnum {

	protected static $fields;
	protected static $values;

	public static function init() {
		self::$fields= array(
				"jobOrder" => array(
						'desc'=>'Zlecenie',
						'exportScript'=>'export/script/joborder.php',
						'exportTemplate'=>'joborder/zlecenie.xls',
				),
		);
	}
}
ExcelDataSetEnum::init();
ExcelDataSetEnum::initValues();
?>