<?php  

class JobOrderStatusEnum extends EnumType {
		
	protected static $fields;
	protected static $values;
	
	static function init(){
		self::$fields= array(
			"active" => array(
					'desc'=> __("Active"),
					//'defineName'=>'ACTIVITY_CALL',
					'dbValue'=>'Active'
			),
			"upcoming" => array(
					'desc'=> __("Upcoming"),
					//'defineName'=>'ACTIVITY_CALL_TALKED',
					'dbValue'=>'Upcoming'
			),
			"Lead" => array(
					'desc'=>__("Prospective / Lead"),
					//'defineName'=>'ACTIVITY_CALL_LVM',
					'dbValue'=>'Lead'
			),
			"onHold" => array(
					'desc'=>__("On Hold"),
					//'defineName'=>'ACTIVITY_CALL_MISSED',
					'dbValue'=>'OnHold'
			),
			"Full" => array(
					'desc'=>__("Full"),
					//'defineName'=>'ACTIVITY_EMAIL',
					'dbValue'=>'Full'
			),
			"Closed" => array(
					'desc'=>__("Closed"),
					//'defineName'=>'ACTIVITY_MEETING',
					'dbValue'=>'Closed'
			),
			"Canceled" => array(
					'desc'=>__("Canceled"),
					//'defineName'=>'ACTIVITY_OTHER',
					'dbValue'=>'Canceled'
			)		
		);
	}
	
	public function getFields() {
		return self::$fields;    
	}
}
JobOrderStatusEnum::init();
JobOrderStatusEnum::initValues();
?>