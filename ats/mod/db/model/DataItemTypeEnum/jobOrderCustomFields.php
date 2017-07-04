<?php 

$jobOrderCustomFields = array(
		'test1'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'test1',
		),
								// rangeAndConditions
								'rangeOfService'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('text'),
										'desc'=>__('Range of service'),
								),
								'dueDateOfService'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('date'),
										'desc'=>__('Due date of service'),
								),
								'costsOfService'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('money'),
										'desc'=>__('Costs of service'),
								),
								'condOfPayment'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('select'),
										'desc'=>__('Conditions of payment'),
										'options'=>array(
												'vat7days'=>'7 dni od daty wystawienia Faktury Vat (+23% Vat)',
												'vat14days'=>'14 dni od daty wystawienia Faktury Vat (+23% Vat)',
												'vat24days'=>'24 dni od daty wystawienia Faktury Vat (+23% Vat)',
												'cash'=>'Gotówka',
										),
								),
								//requirements
								'educationReq'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('select'),
										'desc'=>'Wymagane',
										'options'=>array(
												'podstawowe'=>'Podstawowe',
												'zawodowe'=>'Zawodowe',
												'srednie'=>'Średnie',
												'wyzsze'=>'Wyższe',
												'podyplomowe'=>'Podyplomowe',
										),
								),
								'educationPref'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('select'),
										'desc'=>'Mile widziane',
										'options'=>array(
												'podstawowe'=>'Podstawowe',
												'zawodowe'=>'Zawodowe',
												'srednie'=>'Średnie',
												'wyzsze'=>'Wyższe',
												'podyplomowe'=>'Podyplomowe',
										),
								),
								'educationFields'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>'Kierunki wykształcenia',
								),
								'expReqYearsInBranch'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('intPositive'),
										'desc'=>__('Years in Branch'),
								),
								'expReqYearsInGeneral'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('intPositive'),
										'desc'=>__('Years in General'),
								),
								'expReqYearsB2B'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('intPositive'),
										'desc'=>__('Years in B2B'),
								),
								'expReqYearsB2C'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('intPositive'),
										'desc'=>__('Years in B2C'),
								),
								'expPrefYearsInBranch'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('intPositive'),
										'desc'=>__('Years in Branch'),
								),
								'expPrefYearsInGeneral'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('intPositive'),
										'desc'=>__('Years in General'),
								),
								'expPrefYearsB2B'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('intPositive'),
										'desc'=>__('Years in B2B'),
								),
								'expPrefYearsB2C'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('intPositive'),
										'desc'=>__('Years in B2C'),
								),							
								'drivingLicence'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('checkList'),
										'desc'=>__('Driving licence'),
										'options'=>array(
												'AM'=>'AM',
												'A1'=>'A1',
												'A2'=>'A2',
												'A'=>'A',
												'B1'=>'B1',
												'B'=>'B',
												'C1'=>'C1',
												'C'=>'C',
												'D1'=>'D1',
												'D'=>'D',
												'BE'=>'BE',
												'C1E'=>'C1E',
												'CE'=>'CE',
												'D1E'=>'D1E',
												'DE'=>'DE',
												'T'=>'T',
										),
								),
								'jobNature'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('checkList'),
										'desc'=>'Charakter pracy',
										'withOther'=>false,
										'options'=>array(
												'biurowa'=>'Biurowa',
												'fizyczna'=>'Fizyczna',
												'terenowa'=>'Terenowa',
												'wyjazdy'=>'Wyjazdy',
										),
								),
								'jobNatureOther'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>'Inny charakter (jaki)',
								),
								'netSalaryProbBase'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('money'),
										'desc'=>'Podstawa',
								),
								'netSalaryProbBonus'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('money'),
										'desc'=>'Premia',
								),
								'netSalaryProbProv'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('money'),
										'desc'=>'Prowizja',
								),
								'netSalaryProbOther'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('money'),
										'desc'=>'Inne',
								),
								'netSalaryBase'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('money'),
										'desc'=>'Podstawa',
								),
								'netSalaryBonus'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('money'),
										'desc'=>'Premia',
								),
								'netSalaryProv'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('money'),
										'desc'=>'Prowizja',
								),
								'netSalaryOther'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('money'),
										'desc'=>'Inne',
								),
								'tools'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('checkList'),
										'desc'=>'Narzędzia',
										'options'=>array(
												'samochod'=>'Samochód',
												'laptop'=>'Laptop',
												'telefon'=>'Telefon',
												'mieszkanie'=>'Mieszkanie',
										),
								),
								'toolsOther'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>'Inne narzędzia (jakie)',
								),
								'motivPackage'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('checkList'),
										'desc'=>'Pakiet motywacyjny',
										'options'=>array(
												'karta'=>'Karta Benefit/Multisport',
												'opieka'=>'Prywatna Opieka Medyczna',
												'ubezpieczenie'=>'Ubezpieczenie',
												'szkolenia'=>'Szkolenia',
										),
								),
								'motivPackageOther'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>'Inne (jakie)',
								),
								'possibleJobTypes'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('checkList'),
										'desc'=>'Możliwe formy zatrudnienia',
										'withOther'=>false,
										'options'=>E::jobOrderType()->getAsoc('dbValue','desc'),
								),
								'addSkillsReq'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>'Dodatkowe umiejętności - wymagane',
								),
								'addSkillsPref'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>'Dodatkowe umiejętności - mile widziane',
								),
								'compSkillsReq'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>'Umiejętności IT - wymagane',
								),
								'compSkillsPref'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>'Umiejętności IT - mile widziane',
								),
								'langSkillsReq'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>'Umiejętności językowe - wymagane',
								),
								'langSkillsPref'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>'Umiejętności językowe - mile widziane',
								),
								'otherSkillsReq'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>'Inne umiejętności - wymagane',
								),
								'otherSkillsPref'=>array(
										'fieldTypeEnum'=>E::dataItemFieldType('string'),
										'desc'=>'Inne umiejętności - mile widziane',
								),
								
								
						);
