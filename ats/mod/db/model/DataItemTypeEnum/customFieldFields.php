<?php 

$customFieldFields = array(
		'id'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('id'),
				'desc'=>'Id pola',
				'dbColumn'=>'customFieldId',
				'sqlColumn'=>'extra_field_id', 
		),
		'siteId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>'Id strony',
				'dbColumn'=>'siteId',
				'sqlColumn'=>'site_id',
		),		
		'importId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>'Id importu',
				'dbColumn'=>'importId',
				'sqlColumn'=>'import_id',
		),
		'dataItemId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>'Id elementu',
				'dbColumn'=>'dataItemID',
				'sqlColumn'=>'data_item_id',
		),
		'dataItemType'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('select'),
				'desc'=>'Typ elementu',
				'dbColumn'=>'dataItemType',
				'sqlColumn'=>'data_item_type',
				//'options'=>E::enum('dataItemType')->getAsoc('dbValue','desc'),//dataItemType.lazyinit!!!
		),
		'fieldName'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'Nazwa pola',
				'dbColumn'=>'fieldName',
				'sqlColumn'=>'field_name',
		),
		'fieldValue'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('text'),
				'desc'=>'Wartość pola',
				'dbColumn'=>'fieldValue',
				'sqlColumn'=>'value',
		),	
	);
