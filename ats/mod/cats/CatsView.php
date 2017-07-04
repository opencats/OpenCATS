<?php 

class CatsView extends View {
	
	protected function showView($viewName,$args){
		chdir(CATS_FE_DIR);
		include('./index_cats.php');
	}
	
	function show($args){
		return $this->showView(null,$args);
	}
	
}

?>