<?php  

class JobOrderTypeEnum extends EnumType {
		
	protected static $fields;
	protected static $values;
	
	static function init(){
		self::$fields= array(
			"uz" => array(
					'desc'=> 'Umowa Zlecenie',
					'dbValue'=>'UZ'
			),
			"uop" => array(
					'desc'=> 'Umowa o Pracę',
					'dbValue'=>'UoP'
			),
			"uod" => array(
					'desc'=>'Umowa o Dzieło',
					'dbValue'=>'UoD'
			),
			"uot" => array(
					'desc'=>'Umowa o Pracę Tymcz.',
					'dbValue'=>'UoT'
			),
			"ua" => array(
					'desc'=>'Umowa Agencyjna',
					'dbValue'=>'UA'
			),
			"wd" => array(
					'desc'=>'Własna Działałność Gospodarcza',
					'dbValue'=>'WD'
			)	
		);
	}
	
	public function getFields() {
		return self::$fields;    
	}
	
}
JobOrderTypeEnum::init();
JobOrderTypeEnum::initValues();
?>