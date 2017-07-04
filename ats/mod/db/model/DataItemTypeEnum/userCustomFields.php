<?php

$userCustomFields = array(
		'position'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'Stanowisko pracy',
		),
		'meGWDeviceId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'SMSGW - Id urządzenia',
		),
		'meGWMail'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'SMSGW - e-mail',
		),
		'meGWPassword'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'SMSGW - hasło',
		),
		'oldUI'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('checkList'),
				'desc'=>'Opcje UI',
				'help'=>array(
					'tip'=>'Przełączanie opcji UI',						
				),
				'options'=>array(
						'calendar'=>'Stara wersja kalendarza',
				),
		),
		
		
	);