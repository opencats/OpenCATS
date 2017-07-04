<?php 

$jobOrderFields = array(
		'id'=>array(
			'fieldTypeEnum'=>E::dataItemFieldType('id'),
			'desc'=>'Id zlecenia',	
			'dbColumn'=>'jobOrderID',
			'sqlColumn'=>'joborder_id',
		),
		'siteId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>__('Site id'),
				'dbColumn'=>'siteId',
				'sqlColumn'=>'site_id',
		),
		'companyId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'dbColumn'=>'companyID',
				'sqlColumn'=>'company_id',
		),
		'contactId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'dbColumn'=>'contactID',
				'sqlColumn'=>'contact_id',
		),		
		'userId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>'Wprowadził',
				'dbColumn'=>'enteredBy',
				'sqlColumn'=>'entered_by',
		),
		'mgUserId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>'Opiekun',
				'dbColumn'=>'owner',
				'sqlColumn'=>'owner',
		),
		'rcUserId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>'Rekruter',
				'dbColumn'=>'recruiter',
				'sqlColumn'=>'recruiter',
		),
		'dtCreated'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('dateTime'),
				'desc'=>'Data utworzenia',
				'dbColumn'=>'dateCreated',
				'sqlColumn'=>'date_created',
		),
		'dtModified'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('dateTime'),
				'desc'=>'Data modyfikacji',
				'dbColumn'=>'dateModified',
				'sqlColumn'=>'date_modified',
		),
		'startDate'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('date'),
				'desc'=>'Data uruchomienia',
				'dbColumn'=>'startDate',
				'sqlColumn'=>'start_date',
		),
		'position'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'dbColumn'=>'title',
		),
		'city'=>array(
				'dbColumn'=>'city',
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
		),
		'internalNumber'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string32'),
				'dbColumn'=>'companyJobID',
		),


		
);
