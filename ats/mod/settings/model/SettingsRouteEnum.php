<?php 

class SettingsRouteEnum extends RouteEnum {


	public function getFields() {
			
		return array(
				"main" => array(
						'desc'=>'Ustawienia - Dane użytkownika',
						'view'=>'User',
						RouteEnum::frontView =>'MainView'
				),
				"user" => array(
						'desc'=>'Ustawienia - Dane użytkownika',
						'view'=>'User',
						RouteEnum::frontView =>'MainView'
				)
				, "SQLITE" => array('desc'=>'jajo')
				, "FILES" => array('desc' => 'jajo2')
		);
	}

}


?>