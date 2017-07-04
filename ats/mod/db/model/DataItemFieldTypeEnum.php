<?php 

class DataItemFieldTypeEnum extends RouteEnum {

	protected static $fields;
	protected static $values;
	
	public static function init() {
		self::$fields = array(
				"string" => array(
						'desc'=>__('Text Box'),
						'dbValue'=>1,
				),
				"text" => array(
						'desc'=>__('Multiline Text Box'),
						'dbValue'=>2,
				),
				"check" => array(
						'desc'=>__('Check Box'),
						'dbValue'=>3,
				),
				"date" => array(
						'desc'=>__('Date'),
						'dbValue'=>4,
				),
				"select" => array(
						'desc'=>__('Dropdown List'),
						'dbValue'=>5,
				),
				"radio" => array(
						'desc'=>__('Radio Button List'),
						'dbValue'=>6,
				),
				"int" => array(
						'desc'=>__('Integer'),
						'dbValue'=>100,
				),
				"intPositive" => array(
						'desc'=>__('Positive Integer'),
						'dbValue'=>101,
				),
				"money" => array(
						'desc'=>__('Money'),
						'dbValue'=>150,
				),
				"checkList" => array(
						'desc'=>__('Check Box List'),
						'dbValue'=>200,
				),
				"reachText" => array(
						'desc'=>__('Reach Text Field'),
						'dbValue'=>300,
				),
				"string32" => array(
						'desc'=>__('Text Box'),
						'dbValue'=>400,
				),
				"itemName" => array(
						'desc'=>'Nazwa elementu',
						'dbValue'=>401,
				),
				"phone" => array(
						'desc'=>'Numer telefonu',
						'dbValue'=>500,
				),
				"dateTime" => array(
						'desc'=>'Data i czas',
						'dbValue'=>600,
				),
				"time" => array(
						'desc'=>'Czas',
						'dbValue'=>601,
				),
				"id" => array(
						'desc'=>__('Id of object'),
						'dbValue'=>999,
				),
				"fk" => array(
						'desc'=>__('Id of external object'),
						'dbValue'=>998,
				),
				
		);
	}
	
	function dbToUiRead($value,$fieldDef){
		return $this->mdToUiRead($this->onDbToMd($value),$fieldDef);
	}
	
	function mdToUiRead($value,$fieldDef){
		return $this->onMdToUiRead($value,$fieldDef);
	}

	function onMdToUiRead($value,$fieldDef){
		switch($this->name()){
			case 'select':
				if (!evStrEmpty($value)){
					$value = $fieldDef['options'][$value];
				}
			default:
				//noop
		}
		return $value;
	}
	
	function onMdToDb($value){
		switch($this->name()){
			case 'checkList':
					$ka = array_keys($value);
					//vd(array('$ka'=>$ka));
					//ie();
					$ks = '|'.implode('|',$ka).'|';
					$value = $ks;
			default:
				//noop
		}
		return $value;
	}
	
	function onDbToMd($value){
		switch($this->name()){
			case 'checkList':
				$ka = explode('|',trim($value,'|'));
				//vd(array('$ka'=>$ka));
				//ie();
				$ks = implode(',',$ka);
				$value = $ks;
			default:
				//noop
		}
		return $value;
	}
	

}
DataItemFieldTypeEnum::init();
DataItemFieldTypeEnum::initValues();
?>