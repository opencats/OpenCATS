<?php 

$userFields = array(
		'id'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('id'),
				'desc'=>'Id użytkownika',
				'dbColumn'=>'userId',
				'sqlColumn'=>'user_id',
		),
		'siteId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>__('Site id'),
				'dbColumn'=>'siteId',
				'sqlColumn'=>'site_id',
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
		'email'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'e-mail',
				'dbColumn'=>'email',
				'sqlColumn'=>'email',
		),
		'company'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'Firma',
				'dbColumn'=>'company',
		),
		'phoneWork'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('phone'),
				'desc'=>'Telefon służbowy',
				'dbColumn'=>'phoneWork',
				'sqlColumn'=>'phone_work',
		),
		
		);
