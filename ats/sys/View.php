<?php 

abstract class View {
	
	private $model;
	
	/*public function show($args){
		$route = $args['route'];	
		include_once('view/'.$route->view.'.php');
	}*/
	protected abstract function showView($viewName,$args);
	
	function show($args){
		/*vd(array('$args'=>$args,
			'bt'=>debug_backtrace()));*/
		return $this->showView($args['route']->view,$args);
	}
	
	public function setModel($model){
		$this->model = $model;
	} 
	
	public function getModel(){
		return $this->model;
	}
	public function model(){
		return $this->model;
	}
	
}

?>