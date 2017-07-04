<?php 


evIncBe('mod/db/DbHandler');
class DataItemTypeEnum extends EnumType {

	protected static $fields;
	protected static $values;
	
	protected function __construct( $name ) {
		parent::__construct($name);
		//$allFields = array();
		$allFields = $this->getAttr('fields');
		$a = $this->getAttr('customFields');
		if (is_array($a)){
			foreach($a as $k=>$v){
				$allFields[$k]=$v;
			}
		}
		$a = $this->getAttr('calcFields');
		if (is_array($a)){
			foreach($a as $k=>$v){
				$allFields[$k]=$v;
			}
		}
		$this->addAttr('allFields',$allFields);		
	}

	public static function init() {
		//include('DataItemTypeEnum/userSections.php');
		//include('DataItemTypeEnum/userFields.php');
		//include('DataItemTypeEnum/userCustomFields.php');
		//include('DataItemTypeEnum/candidateSections.php');
		//include('DataItemTypeEnum/candidateFields.php');
		//include('DataItemTypeEnum/candidateCustomFields.php');
		//include('DataItemTypeEnum/companyFields.php');
		//include('DataItemTypeEnum/jobOrderCustomFields.php');
		//include('DataItemTypeEnum/jobOrderFields.php');
		//include('DataItemTypeEnum/jobOrderSections.php');
		//include('DataItemTypeEnum/jobOrderPipelineFields.php');
		//include('DataItemTypeEnum/activityFields.php');
		//include('DataItemTypeEnum/contactFields.php');
		//include('DataItemTypeEnum/contactSections.php');
		
		self::$fields = array(
				'user' => array(
						'desc'=>__('Site User'),
						'dbValue'=>5,
						'sections'=>'mod/db/model/DataItemTypeEnum/userSections',
						'_idFieldSQL'=>'user_id',
						'dbTable'=>'user',
						'fields'=>'mod/db/model/DataItemTypeEnum/userFields',
						'dbHandler'=>evGetSingleton('mod/user/db/UserDataHandler'),
						'customFields'=>'mod/db/model/DataItemTypeEnum/userCustomFields',
						'calcFields'=>array(
								'namePositionContact'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>'Imię nazwisko stanowisko kontakt',
										'handlerReadMethod'=>'calcUserFields',
										//'dbColumn'=>'name',// not stored
								),
						),
				),
				
				"candidate" => array(
						'desc'=>__('Candidate'),
						'dbValue'=>100,
						'sections'=>'mod/db/model/DataItemTypeEnum/candidateSections',
						'_idFieldSQL'=>'candidate_id',
						'dbTable'=>'candidate',
						'fields'=>'mod/db/model/DataItemTypeEnum/candidateFields',
						'dbHandler'=>evGetSingleton('mod/candidate/db/CandidateDataHandler'),
						'customFields'=>'mod/db/model/DataItemTypeEnum/candidateCustomFields',
						'calcFields'=>array(
								'fullAddress'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>'Pełny adres',
										'handlerReadMethod'=>'readFullAddress',
										//'dbColumn'=>'fullAddress',
								),
						),
				),
				"company" => array(
						'desc'=>__('Company'),
						'dbValue'=>200,
						'dbTable'=>'company',
						'_idFieldSQL'=>'company_id',
						'dbHandler'=>evGetSingleton('mod/company/db/CompanyDataHandler'),
						'idColumn'=>'company_id',
						'sections'=>'mod/db/model/DataItemTypeEnum/companySections',
						'fields'=>'mod/db/model/DataItemTypeEnum/companyFields',
						'calcFields'=>array(
								'fullNameOrName'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>__('Company Name'),
										'handlerReadMethod'=>'readFullNameOrName',
										//'dbColumn'=>'name',// not stored
								),
								'fullAddress'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>'Pełny adres',
										'handlerReadMethod'=>'readFullAddress',
										//'dbColumn'=>'fullAddress',
								),
						),
						'customFields'=>array(
								// rangeAndConditions
								'fullName'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>__('Full Company Name'),
								),
								'nip'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>__('NIP'),
								),
								'regon'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>__('REGON'),
								),
								'fullAddress'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>__('Company Full Adres'),
										//'handlerReadMethod'=>'readFullAddress',
										//'dbColumn'=>'fullAddress',
								),
								
						),
						
				),
				"contact" => array(
						'desc'=>__('Contact'),
						'dbValue'=>300,
						'dbTable'=>'contact',
						'_idFieldSQL'=>'contact_id',
						'sections'=>'mod/db/model/DataItemTypeEnum/contactSections',
						'fields'=>'mod/db/model/DataItemTypeEnum/contactFields',
				),
				"jobOrder" => array(
						'desc'=>'Zlecenie',
						'dbValue'=>400,
						'dbTable'=>'joborder',
						'_idFieldSQL'=>'joborder_id',
						'dbHandler'=>evGetSingleton('mod/joborder/db/JobOrderDataHandler'),
						'sections'=>'mod/db/model/DataItemTypeEnum/jobOrderSections',
						'fields'=>'mod/db/model/DataItemTypeEnum/jobOrderFields',
						'customFields'=>'mod/db/model/DataItemTypeEnum/jobOrderCustomFields',//$jobOrderCustomFields,
						'calcFields'=>array(
								'contactWorkPhone'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('phone'),
										'desc'=>'Kontakt - telefon',
										'handlerReadMethod'=>'readContactWorkPhone',
										'phoneConc'=>array(//uzywane w phone.read
												'name'=>'contact',
												'idField'=>'contactId',
												),
										//'dbColumn'=>'name',// not stored
								),
						),
				),
				"jobOrderPipeline" => array(
						'desc'=>__('Job order pipeline'),
						'dbValue'=>600,
						'dbTable'=>'candidate_joborder',
						'_idFieldSQL'=>'candidate_joborder_id',
						//'sections'=>$jobOrderPipelineSections,
						'fields'=>'mod/db/model/DataItemTypeEnum/jobOrderPipelineFields',
						//'customFields'=>$jobOrderPipelineCustomFields,
				),
				"event" => array(
						'desc'=>"Działania",
						'dbValue'=>700,
						'dbTable'=>'calendar_event',
						'_idFieldSQL'=>'calendar_event_id',
						'dbHandler'=>evGetSingleton('mod/calendar/db/EventDataHandler'),
						//'sections'=>$jobOrderPipelineSections,
						'fields'=>'mod/db/model/DataItemTypeEnum/eventFields',
						'calcFields'=>'mod/db/model/DataItemTypeEnum/eventCalcFields',
				),				
				"activity" => array(
						'desc'=>"Aktywność",
						'dbValue'=>901,
						'dbTable'=>'activity',
						'_idFieldSQL'=>'activity_id',
						//'sections'=>$jobOrderPipelineSections,
						'fields'=>'mod/db/model/DataItemTypeEnum/activityFields',
						//'customFields'=>$jobOrderPipelineCustomFields,
				),	
				"customField" => array(
						'desc'=>"Aktywność",
						'dbValue'=>902,
						'dbTable'=>'extra_field',
						'_idFieldSQL'=>'extra_field_id',
						//'sections'=>$jobOrderPipelineSections,
						'fields'=>'mod/db/model/DataItemTypeEnum/customFieldFields',
						//'customFields'=>$jobOrderPipelineCustomFields,
				),
				
		);
	}
	
	/**
	 * Zwraca wszystkie definicje pol (fields, custom i dodatkowe)
	 */
	function getAllFields(){
		$result = array();
		//vd(array(
		//	'$this->customFields'=>$this->customFields	
		//));
		if (is_array($this->customFields)){
			foreach ($this->customFields as $k =>$v){
				$result[$k]=$v;
			}
		}
		if (is_array($this->fields)){
			foreach ($this->fields as $k =>$v){
				$result[$k]=$v;
			}
		}
		if (is_array($this->calcFields)){
			foreach ($this->calcFields as $k =>$v){
				$result[$k]=$v;
			}
		}
		return $result;
	}
	
	//lazy loading sections/fields/customFields/calcFields
	protected function llAttr(){
		return array(				
			'attr'=>array(	
				'sections'=>true,
				'fields'=>true,
				'customFields'=>true,
				'calcFields'=>true,
				),
			'path'=>dirname(__FILE__).'/'.get_class($this).'/',		
		);
	}	
}
DataItemTypeEnum::init();
DataItemTypeEnum::initValues();
?>