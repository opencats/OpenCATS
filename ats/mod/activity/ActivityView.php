<?php 

class ActivityView extends View {

	protected function showView($viewName,$args){
		//evIncBe('lib/phpexcel');
		//$route = $args['route'];
		include('view/'.$viewName.'.php');
	}
	
}

?>