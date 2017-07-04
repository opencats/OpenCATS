<?php 

class SmsView extends View {

	protected function showView($viewName,$args){
		return include('view/'.$viewName.'.php');
	}	
	
}

?>