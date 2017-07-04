<?php  

class AccessLevelEnum extends RouteEnum {

	protected static $fields;
	protected static $values;

	public static function init() {
		self::$fields= array(
				"disabled" => array(
						'desc'=> 'Konto wyłączone. najniższy poziom dostępu. Uzytkownik nie może się zalogować.',
						'shortDesc'=>'Konto wyłączone',
						'defineName'=>'ACCESS_LEVEL_DISABLED',
						'dbValue'=>0
				),
				"read" => array(
						'desc'=> 'Tylko do odczytu. Uzytkownik na dostęp tylko do odczytu danych.',
						'shortDesc'=>'Tylko do odczytu',
						'defineName'=>'ACCESS_LEVEL_READ',
						'dbValue'=>100
				),
				"modify" => array(
						'desc'=> 'Zmiana danych. Uzytkownik może zmieniać istniejące i dodawać nowe dane.',
						'shortDesc'=>'Zmiana danych',
						'defineName'=>'ACCESS_LEVEL_EDIT',
						'dbValue'=>200
				),
				"delete" => array(
						'desc'=> 'Usuwanie danych. Uzytkownik może także usuwać istniejące dane.',
						'shortDesc'=>'Usuwanie danych',
						'defineName'=>'ACCESS_LEVEL_DELETE',
						'dbValue'=>300
				),
				"admin" => array(
						'desc'=> 'Administrator. Użytkownik ma uprawnienia do  zarządzania użytkowanikami i zmiany innych ustawień systemu.',
						'shortDesc'=>'Administrator',
						'defineName'=>'ACCESS_LEVEL_SA',
						'dbValue'=>400
				),
				"root" => array(
						'desc'=> 'Root. Użytkownik ma uprawnienia do  zarządzania użytkowanikami i zmiany innych ustawień systemu.',
						'shortDesc'=>'Root',
						'defineName'=>'ACCESS_LEVEL_ROOT',
						'dbValue'=>500
				),
		);
	}
}
AccessLevelEnum::init();
AccessLevelEnum::initValues();
?>