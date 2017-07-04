<?php 

$activityFields = array(
		'id'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('id'),
				'desc'=>'Id działania',
				'dbColumn'=>'activityID',
				'sqlColumn'=>'activity_id',
		),
		'siteId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>'Id strony',
				'dbColumn'=>'siteId',
				'sqlColumn'=>'site_id',
		),
		
		'userId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>'Wprowadził',
				'dbColumn'=>'enteredBy',
				'sqlColumn'=>'entered_by',
		),

		'jobOrderId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>'Id zlecenia',
				'dbColumn'=>'jobOrderID',
				'sqlColumn'=>'joborder_id',
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
		'dtCreated'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('date'),
				'desc'=>'Data utworzenia',
				'dbColumn'=>'dateCreated',
				'sqlColumn'=>'date_created',
		),
		'dtModified'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('date'),
				'desc'=>'Data modyfikacji',
				'dbColumn'=>'dateModified',
				'sqlColumn'=>'date_modified',
		),
		'type'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('select'),
				'desc'=>'Typ działania',
				'dbColumn'=>'type',
				'sqlColumn'=>'type',
				'options'=>E::enum('activityType')->getAsoc('dbValue','desc'),
		),
		'notes'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('text'),
				'desc'=>'Notatki',
				'dbColumn'=>'notes',
				'sqlColumn'=>'notes',
		),	
	);
