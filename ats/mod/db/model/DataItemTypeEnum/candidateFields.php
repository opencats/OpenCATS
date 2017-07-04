<?php 

$candidateFields = array(
		'id'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('id'),
				'desc'=>'Id kandydata',
				'dbColumn'=>'candidateID',
				'sqlColumn'=>'candidate_id',
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
		'dtAvailable'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('dateTime'),
				'desc'=>'Data modyfikacji',
				'dbColumn'=>'dateAvailable',
				'sqlColumn'=>'date_available',
		),
		'email'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'e-mail',
				'dbColumn'=>'email1',
				'sqlColumn'=>'email1',
		),
		'email2'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'e-mail',
				'dbColumn'=>'email2',
				'sqlColumn'=>'email2',
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
		'phoneHome'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('phone'),
				'desc'=>'Telefon domowy',
				'dbColumn'=>'phoneWork',
				'sqlColumn'=>'phone_home',
		),
		'phoneCell'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('phone'),
				'desc'=>'Telefon służbowy',
				'dbColumn'=>'phoneCell',
				'sqlColumn'=>'phone_cell',
		),
		'phoneWork'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('phone'),
				'desc'=>'Telefon służbowy',
				'dbColumn'=>'phoneWork',
				'sqlColumn'=>'phone_work',
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
		
		'keySkills'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'Kluczowe umiejętności',
				'dbColumn'=>'keySkills',
				'sqlColumn'=>'key_skills',
		),
		
		
	);