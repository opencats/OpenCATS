<?php  

class ActivityStatusEnum extends EnumType {
		
	protected static $fields;
	
	static function init(){
		self::$fields= array(
			"noStatus" => array(
					'desc'=> __("No Status"),
					//'defineName'=>'ACTIVITY_CALL',
					'dbValue'=>0
			),
			"noContact" => array(
					'desc'=> __("No Contact"),
					//'defineName'=>'ACTIVITY_CALL_TALKED',
					'dbValue'=>100
			),
			"contacted" => array(
					'desc'=>__("Contacted"),
					//'defineName'=>'ACTIVITY_CALL_LVM',
					'dbValue'=>200
			),
			"candidateResp" => array(
					'desc'=>__("Candidate Responded"),
					//'defineName'=>'ACTIVITY_CALL_LVM',
					'dbValue'=>250
			),				
			"qualifying" => array(
					'desc'=>__("Qualifying"),
					//'defineName'=>'ACTIVITY_CALL_MISSED',
					'dbValue'=>300
			),
			"submitted" => array(
					'desc'=>__("Submitted"),
					//'defineName'=>'ACTIVITY_EMAIL',
					'dbValue'=>400
			),
			"interviewing" => array(
					'desc'=>__("Interviewing"),
					//'defineName'=>'ACTIVITY_MEETING',
					'dbValue'=>500
			),
			"offered" => array(
					'desc'=>__("Offered"),
					'defineName'=>'ACTIVITY_OTHER',
					'dbValue'=>600
			),	
			"notInCons" => array(
					'desc'=>__("Not in Consideration"),
					//'defineName'=>'ACTIVITY_OTHER',
					'dbValue'=>650
			),
			"clientDecl" => array(
					'desc'=>__("Client Declined"),
					//'defineName'=>'ACTIVITY_OTHER',
					'dbValue'=>700
			),
			"placed" => array(
					'desc'=>__("Placed"),
					//'defineName'=>'ACTIVITY_OTHER',
					'dbValue'=>800
			)				
	);
	}
	
	public function getFields() {
		return self::$fields;    
	}
	

}

ActivityStatusEnum::init();

?>