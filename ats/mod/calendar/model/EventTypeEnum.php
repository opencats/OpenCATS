<?php  

class EventTypeEnum extends EnumType {
		
	protected static $fields;
	
	static function init(){
		self::$fields= array(
			"call" => array(
					'desc'=> __("Call"),
					'iconImage'=>'images/phone.gif',
					'dbValue'=>100
			), 	 	
			"email" => array(
					'desc'=> __("Email"),
					'iconImage'=>'images/email.gif',
					'dbValue'=>200
			), 	
			"meeting" => array(
					'desc'=>__("Meeting"),
					'iconImage'=>'images/meeting.gif',
					'dbValue'=>300
			),	 	
			"interview" => array(
					'desc'=>__("Interview"),
					'iconImage'=>'images/interview.gif',
					'dbValue'=>400
			), 	 	
			"personal" => array(
					'desc'=>__("Personal"),
					'iconImage'=>'images/personal.gif',
					'dbValue'=>500
			),
			"other" => array(
					'desc'=>__("Other"),
					'iconImage'=>'',
					'dbValue'=>600
			)		
	);
	}
	
	public function getFields() {
		return self::$fields;    
	}
	

}

EventTypeEnum::init();

?>