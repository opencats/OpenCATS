<?php  

class ActivityStatusEnum extends EnumType {		

	protected static $fields;
	protected static $values;
	
	static function init(){
		self::$fields= array(
			"noStatus" => array(
					'desc'=> __("No Status"),
					'descShort'=>__("No Status"),
					//'defineName'=>'ACTIVITY_CALL',
					'dbValue'=>0
			),
// 			"noContact" => array(
// 					'desc'=> __("No Contact"),
// 					'descShort'=> __("No Contact"),
// 					//'defineName'=>'ACTIVITY_CALL_TALKED',
// 					'dbValue'=>100
// 			),
// 			"contacted" => array(
// 					'desc'=>__("Contacted"),
// 					'descShort'=> __("Contacted"),
// 					//'defineName'=>'ACTIVITY_CALL_LVM',
// 					'dbValue'=>200
// 			),
// 			"candidateResp" => array(
// 					'desc'=>__("Candidate Responded"),
// 					'descShort'=>__("Cand resp"),
// 					//'defineName'=>'ACTIVITY_CALL_LVM',
// 					'dbValue'=>250
// 			),				
			"qualifying" => array(
					'desc'=>"Do weryfikacji",
					'descShort'=>"Do weryfikacji",
					//'defineName'=>'ACTIVITY_CALL_MISSED',
					'dbValue'=>300
			),
			"rejected" => array(
					'desc'=>"Odrzucony",
					'descShort'=>"Odrzucony",
					//'defineName'=>'ACTIVITY_CALL_MISSED',
					'dbValue'=>301
			),
// 			"submitted" => array(
// 					'desc'=>__("Submitted"),
// 					'descShort'=>__("Submitted"),
// 					//'defineName'=>'ACTIVITY_EMAIL',
// 					'dbValue'=>400
// 			),
// 			"interviewing" => array(
// 					'desc'=>__("Interviewing"),
// 					'descShort'=>__("Interviewing"),
// 					//'defineName'=>'ACTIVITY_MEETING',
// 					'dbValue'=>500
// 			),
			"offered" => array(
					'desc'=>"Rekomendacja",
					'descShort'=>"Rekomendacja",
					'dbValue'=>600
			),	
// 			"notInCons" => array(
// 					'desc'=>__("Not in Consideration"),
// 					'descShort'=>__("Not in Consideration"),
// 					//'defineName'=>'ACTIVITY_OTHER',
// 					'dbValue'=>650
// 			),
			"clientDecl" => array(
					'desc'=>"Odmowa klienta",
					'descShort'=>"Odmowa klienta",
					//'defineName'=>'ACTIVITY_OTHER',
					'dbValue'=>700
			),
			"placed" => array(
					'desc'=>__("Placed"),
					'descShort'=>__("Placed"),
					//'defineName'=>'ACTIVITY_OTHER',
					'dbValue'=>800
			),
			"resigned" => array(
					'desc'=>"Rezygnacja kandydata",
					'descShort'=>'Rezygnacja kandydata',
					//'defineName'=>'ACTIVITY_OTHER',
					'dbValue'=>801
			)
		);
	}
}
ActivityStatusEnum::init();
ActivityStatusEnum::initValues();
?>