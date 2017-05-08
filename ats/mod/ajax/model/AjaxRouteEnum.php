<?php 

class AjaxRouteEnum extends RouteEnum {

	public function getFields() {
			
		return array(
				"main" => array(
						'desc'=>'Redir do CATS',
						'view'=>'CatsPassThrough',
						RouteEnum::frontView =>'CatsAjaxView'
				)
		);
	}

}


?>