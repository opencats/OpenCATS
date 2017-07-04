<?php 
$companyFields = array(
								'id'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('id'),
										'desc'=>__('Company id'),
										'dbColumn'=>'companyID',
										'sqlColumn'=>'company_id',
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
										'dbColumn'=>'enteredBy',
										'sqlColumn'=>'entered_by',
								),	
								'mgUserId'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('fk'),
										'desc'=>'Przypisany do',
										'dbColumn'=>'owner',
										'sqlColumn'=>'owner',
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
								'name'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('itemName'),
										'desc'=>__('Company Name'),
										'dbColumn'=>'name',
										'rules'=>array(
											'required'=>true,	
										),
								),
								'zip'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>__("Postal Code"),
										'dbColumn'=>'zip',
								),
								'city'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>__("City"),
										'dbColumn'=>'city',
								),
								'street'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>__('Street and number'),
										'dbColumn'=>'address',
								),
								'phone'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('phone'),
										'desc'=>'Główny telefon',
										'dbColumn'=>'phone1',
								),
								'phone2'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('phone'),
										'desc'=>'Dodatkowy telefon',
										'dbColumn'=>'phone2',
								),
								'email'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>'e-mail',
										'dbColumn'=>'fax_number',
								),
		
		
		
						);