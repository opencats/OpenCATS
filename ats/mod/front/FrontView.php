<?php 

class FrontView extends View {
	
	public function show($args){
		$view = $args['view'];
		include_once('view/Front'.$view.'.php');
	}
	
}

?>