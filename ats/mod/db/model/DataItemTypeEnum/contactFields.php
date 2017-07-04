<?php 

$contactFields = array(
		'id'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('id'),
				'desc'=>'Id kontaktu',
				'dbColumn'=>'contactID',
				'sqlColumn'=>'contact_id',
		),
		'siteId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>__('Site id'),
				'dbColumn'=>'siteId',
				'sqlColumn'=>'site_id',
		),
		'companyId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>'Id firmy',
				'dbColumn'=>'companyID',
				'sqlColumn'=>'company_id',
		),

		'userId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>'Użytkownik',
				'dbColumn'=>'enteredBy',
				'sqlColumn'=>'entered_by',
		),
		'mgUserId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>'Opiekun',
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
		'firstName'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'Imię',
				'dbColumn'=>'firstName',
				'sqlColumn'=>'first_name',
		),
		'lastName'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'Nazwisko',
				'dbColumn'=>'lastName',
				'sqlColumn'=>'last_name',
		),
		'phoneWork'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('phone'),
				'desc'=>'Telefon służbowy',
				'dbColumn'=>'phoneWork',
				'sqlColumn'=>'phone_work',
		),
		'phoneCell'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('phone'),
				'desc'=>'Telefon kom.',
				'dbColumn'=>'phoneCell',
				'sqlColumn'=>'phone_cell',
		),
		'phoneOther'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('phone'),
				'desc'=>'Inny telefon',
				'dbColumn'=>'phoneOther',
				'sqlColumn'=>'phone_other',
		),
		'email'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'e-mail',
				'dbColumn'=>'email1',
				'sqlColumn'=>'email1',
		),
		'emailOther'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'Dodatkowy e-mail',
				'dbColumn'=>'email2',
				'sqlColumn'=>'email2',
		),		
);