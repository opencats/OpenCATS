<?php  

class FrontRouteEnum extends RouteEnum {
	

public function getFields() {
			
	return array(
			"/" => array(
					'desc'=>'Widok główny',
					'view'=>'MainView'
			)
			, "SQLITE" => array('desc'=>'jajo')
			, "FILES" => array('desc' => 'jajo2')
	);
}

}

?>