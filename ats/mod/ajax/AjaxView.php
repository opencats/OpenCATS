<?php 

class AjaxView extends View {
	
	function show($args){
		chdir(CATS_FE_DIR);
		include_once('./ajax_cats.php');
	}
	
}

?>