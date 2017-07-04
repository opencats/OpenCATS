<?php 

class ExcelView extends View {

	protected function showView($viewName,$args){
		return include('view/'.$viewName.'.php');
	}
	
	function show($args){
		evIncBe('lib/phpexcel');
		//$route = $args['route'];
		//include_once('view/'.$route->view.'.php');
		//$this->showView()
		parent::show($args);
	}
	
}

?>