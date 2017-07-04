<?php 



class AngularView extends View {
	
	protected function showView($viewName,$args){
		return include('view/'.$viewName.'.php');
	}
	
	public function htmlHeader($args){
		include_once('config.php');
		include('view/htmlHeader.php');
	}
	
	public function htmlApp($args){
		include_once('config.php');
		include('view/htmlApp.php');
	}
	
	
}
