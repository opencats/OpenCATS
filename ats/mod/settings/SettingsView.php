<?php 

class SettingsView extends View {

	protected function showView($viewName,$args){
		/*vd(array(
			'$viewName'=>$viewName,
			'$args'=>$args,	
		));*/
		return include('view/'.$viewName.'.php');
	}
	
}