<?php  

class ActivityTypeEnum extends EnumType {
		
	protected static $fields;
	protected static $values;
	
	static function init(){
		self::$fields= array(
			"call" => array(
					'desc'=> __("Call"),
					'defineName'=>'ACTIVITY_CALL',
					'dbValue'=>100
			),
			"callTalked" => array(
					'desc'=> __("Call (Talked)"),
					'defineName'=>'ACTIVITY_CALL_TALKED',
					'dbValue'=>500
			),
			"callLVM" => array(
					'desc'=>__("Call (LVM)"),
					'defineName'=>'ACTIVITY_CALL_LVM',
					'dbValue'=>600
			),
			"callMissed" => array(
					'desc'=>__("Call (Missed)"),
					'defineName'=>'ACTIVITY_CALL_MISSED',
					'dbValue'=>700
			),
			"email" => array(
					'desc'=>__("E-Mail"),
					'defineName'=>'ACTIVITY_EMAIL',
					'dbValue'=>200
			),
			"sms" => array(
					'desc'=>"SMS",
					'defineName'=>'ACTIVITY_SMS',
					'dbValue'=>201
			),
			"meeting" => array(
					'desc'=>__("Meeting"),
					'defineName'=>'ACTIVITY_MEETING',
					'dbValue'=>300
			),
			"other" => array(
					'desc'=>__("Other"),
					'defineName'=>'ACTIVITY_OTHER',
					'dbValue'=>400
			)		
		);
	}

}
ActivityTypeEnum::init();
ActivityTypeEnum::initValues();
?>