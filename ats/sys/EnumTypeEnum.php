<?php  

class EnumTypeEnum extends EnumType {

	protected static $fields;
	protected static $values;
	
	public static function init() {
		self::$fields = array(
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
			"jobOrderType" => array(
					'desc'=>"Typ zlecenia",
					'definitionPath'=>'mod/joborder/model/JobOrderTypeEnum',
			),
			"eventType" => array(
					'desc'=>"Typ zdarzenia",
					'definitionPath'=>'mod/calendar/model/EventTypeEnum',
			),
			"uiType" => array(
					'desc'=>"Elementy UI",
					'definitionPath'=>'sys/UITypeEnum',
			),
			"dataItemFieldType" => array(
					'desc'=>"Główne Elementy DB",
					'definitionPath'=>'mod/db/model/DataItemFieldTypeEnum',
			),
			"dataItemType" => array(
					'desc'=>"Główne Elementy DB",
					'definitionPath'=>'mod/db/model/DataItemTypeEnum',
			),
			"excelDataSet" => array(
					'desc'=>"Zestawy obszarów danych (import/export Excela)",
					'definitionPath'=>'mod/excel/model/ExcelDataSetEnum',
			),
			"emailTemplate" => array(
					'desc'=>"Szablony wiadomości e-mail",
					'definitionPath'=>'mod/email/model/EmailTemplateEnum',
			),
			"accessLevel" => array(
					'desc'=>"Poziomy dostępu dla użytkowników",
					'definitionPath'=>'mod/user/model/AccessLevelEnum',
			),	
		);
	}

	function enumValues(){
		evIncBe($this->getAttr('definitionPath'));
		$fcname=ucfirst($this->name()).'Enum::values';
		/*vd(array(
			'$fcname'=>$fcname,	
		));*/
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
	
	function byAttr($attrName,$attrValue){
		return $this->enumByAttr($attrName,$attrValue);
	}
	
	
	function getAsoc($keyAttrName,$valueAttrName){

		$values = $this->enumValues();
		/*vd(array(
				'$this'=>$this,
				'$this->name()'=>$this->name(),
				'$values'=>$values,
		));*/
		$result = array();
		foreach($values as $k=>$v){
			$result[($v->getAttr($keyAttrName))]=$v->getAttr($valueAttrName);
		}
		return $result;
	}

}
EnumTypeEnum::init();
EnumTypeEnum::initValues();
?>