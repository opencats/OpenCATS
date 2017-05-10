<?php  

class EnumTypeEnum extends EnumType {
	
	protected static $fields = array(
			"activityType" => array(
					'desc'=>"Typ działania",
					'definitionPath'=>'mod/activity/model/ActivityTypeEnum',
			),
			"activityStatus" => array(
					'desc'=>"Stan działania",
					'definitionPath'=>'mod/activity/model/ActivityStatusEnum',
			),
			"jobOrderStatus" => array(
					'desc'=>"Status zlecenia",
					'definitionPath'=>'mod/joborder/model/JobOrderStatusEnum',
			),
			"eventType" => array(
					'desc'=>"Typ zdarzenia",
					'definitionPath'=>'mod/calendar/model/EventTypeEnum',
			),
			"uiType" => array(
					'desc'=>"Elementy UI",
					'definitionPath'=>'sys/UITypeEnum',
			)
	);
	
public function getFields() {
	return self::$fields;
}

//function getDefPath(){
//	return $this->getAttr('definitionPath');
//}

function enumValues(){
	//evIncEnum($this);
	evIncBe($this->getAttr('definitionPath'));
	$fcname=ucfirst($this->name()).'Enum::values';
	//vd(array(
	//	'$fcname'=>$fcname	
	//));
	$values = call_user_func($fcname);
	return $values;
}
function enumValueOf($name){
	evIncBe($this->getAttr('definitionPath'));
	$fcname=ucfirst($this->name()).'Enum::'.$name;
	$value = call_user_func($fcname);
	return $value;
}

function enumByAttr($attrName,$attrValue){
	$ev = $this->enumValues();
	$result = null;
	foreach($ev as $k=>$v){
		if ($attrValue==$v->getAttr($attrName)){
			$result = $v;
			break;
		}
	}
	return $result;
}

}

?>