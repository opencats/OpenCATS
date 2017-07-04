<?php 

class AjaxView extends View {
	
	protected function showView($viewName,$args){
		chdir(CATS_FE_DIR);
		include('./ajax_cats.php');
	}
	
}

?>