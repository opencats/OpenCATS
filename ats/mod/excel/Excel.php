<?php 



class Excel extends Controller {

	public function handleRequest($args){
		$route = $args['route'];
		$input = $args['input'];
		//vd(array('args'=>$args));
		$response = evRunWithBuf($this->view,'show',array(
						'route'=>$route,
						'input'=>$input,	
		));
		$content = $response['output'];
		echo trim($content);
	}
	
}

?>