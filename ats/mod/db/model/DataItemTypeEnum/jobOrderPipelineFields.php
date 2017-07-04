<?php 

$jobOrderPipelineFields = array(
		'id'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('id'),
				'desc'=>'Id obsługi zlecenia',
				'dbColumn'=>'candidateJobOrderID',
				'sqlColumn'=>'candidate_joborder_id',
		),
		'siteId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>__('Site id'),
				'dbColumn'=>'siteId',
				'sqlColumn'=>'site_id',
		),
		'userId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>'Wprowadził',
				'dbColumn'=>'addedBy',
				'sqlColumn'=>'added_by',
		),
		'candidateId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>'Id kandydata',
				'dbColumn'=>'candidateID',
				'sqlColumn'=>'candidate_id',
		),
		'jobOrderId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>'Id zlecenia',
				'dbColumn'=>'jobOrderID',
				'sqlColumn'=>'joborder_id',
		),
		'dtCreated'=>array(
				'dbColumn'=>'dateCreated',
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
		),
		'dtModified'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'dbColumn'=>'dateModified',
		),
		'dtOffered'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'dbColumn'=>'dateOffered',
				'sqlColumn'=>'date_submitted',
		),
		
);
