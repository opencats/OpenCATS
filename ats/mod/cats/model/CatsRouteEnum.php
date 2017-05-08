<?php 

class CatsRouteEnum extends RouteEnum {


	public function getFields() {
			
		return array(
				"main" => array(
						'desc'=>'Strona główna',
						'view'=>'CatsPassThrough',
						RouteEnum::frontView =>'CatsView'
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