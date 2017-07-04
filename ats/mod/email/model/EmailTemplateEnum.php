<?php  

class EmailTemplateEnum extends EnumType {		

	protected static $fields;
	protected static $values;
	
	static function init(){
		self::$fields= array(
			"statusChanged" => array(
					'desc'=> "Zmieniony status (wysyłany do kandydata)",
					'descShort'=>"Zmieniony status",
					'descLong'=>"Używany przy dodaniu działania zmieniającego status kandydata w obsłudze zgłoszenia",
					'dbValue'=>'EMAIL_TEMPLATE_STATUSCHANGE',
			),
			"candAssigned" => array(
					'desc'=> "Kadydat przypisany (Wysyłany do rekrutera)",
					'descShort'=>"Kadydat przypisany",
					'descLong'=>"Używany w wypadku zmiany opiekuna kandydata (rekrutera)",
					'dbValue'=>'EMAIL_TEMPLATE_OWNERSHIPASSIGNCANDIDATE',
			),
			"jobOrderAssigned" => array(
					'desc'=> "Zlecenie przypisane (Wysyłany do przypisanego rekrutera)",
					'descShort'=>"Zlecenie przypisane",
					'descLong'=>"Używany w wypadku zmiany opiekuna zlecenia (rekrutera)",
					'dbValue'=>'EMAIL_TEMPLATE_OWNERSHIPASSIGNJOBORDER',
			),
			"contactAssigned" => array(
					'desc'=> "Kontakt przypisany (Wysyłany do przypisanego rekrutera)",
					'descShort'=>"Kontakt przypisany",
					'descLong'=>"Używany w wypadku zmiany opiekuna kontaktu (rekrutera)",
					'dbValue'=>'EMAIL_TEMPLATE_OWNERSHIPASSIGNCONTACT',
			),
			"companyAssigned" => array(
					'desc'=> "Firma przypisana (Wysyłany do przypisanego rekrutera)",
					'descShort'=>"Firma przypisana",
					'descLong'=>"Używany w wypadku zmiany opiekuna firmy (rekrutera)",
					'dbValue'=>'EMAIL_TEMPLATE_OWNERSHIPASSIGNCLIENT',
			),
			"welcomeATS" => array(
					'desc'=> "Powitalny e-mail dla Administratora OpenATS(wersja multisite)",
					'descShort'=>"Powitalny e-mail",
					'descLong'=>"Używany w przy utworzeniu nowej strony na platformie",
					'dbValue'=>'EMAIL_TEMPLATE_WELCOME_TO_CATS',
			),
			"candAppResponse" => array(
					'desc'=> "Odpowiedź na elektroniczne zgłoszenie od kandydata(c-portal)",
					'descShort'=>"Odpowiedź na zgłoszenie",
					'descLong'=>"Używany w przy automatycznej odpowiedzi na zgłoszenie kandydata wysłane z poziomu portalu zewnętrznego (c-portal) - wysyłany do kandydata.",
					'dbValue'=>'EMAIL_TEMPLATE_CANDIDATEAPPLY',
			),
			"candAppReceived" => array(
					'desc'=> "Notyfikacja zgłoszenia od kandydata(c-portal)",
					'descShort'=>"Powiadomienie o zgłoszeniu",
					'descLong'=>"Używany w przy notyfikacji o zgłoszeniu kandydata wysłanego z poziomu portalu zewnętrznego (c-portal) - wysyłany do opiekuna zlecenia(rekruter).",
					'dbValue'=>'EMAIL_TEMPLATE_CANDIDATEPORTALNEW',
			),
		);
	}
}
EmailTemplateEnum::init();
EmailTemplateEnum::initValues();