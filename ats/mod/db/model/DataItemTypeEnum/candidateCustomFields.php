<?php 

$candidateCustomFields = array(
		'langs'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'Języki',
		),
		'fullAddress'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'Pełny adres',
		),
		'currentPosition'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'Aktualne stanowisko',
		),
		'expectations'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'Oczekiwania/predysp.',
		),
		'remarks'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'Uwagi',
		),
		'recoms'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'Rekomendacje',
		),
		'education'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'Wykształcenie',
		),
		'wojew'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'Wojewodztwo',
		),
		'internalNumber'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'defaultValueHandlerMethod'=>'dvNextInternalNumber',
				'desc'=>'Numer wewnętrzny kandydata',
		),
		
	);