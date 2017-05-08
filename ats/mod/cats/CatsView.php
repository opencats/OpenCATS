<?php 

class CatsView extends View {
	
	function show($args){
		chdir(CATS_FE_DIR);
		include_once('./index_cats.php');
	}
	
}

?>