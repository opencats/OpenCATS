<?php 

class Ajax extends Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function getRoute($name){
		include_once('model/AjaxRouteEnum.php');
		return AjaxRouteEnum::main();
	}
	

	public function handleRequest($args){
		//vd(array('args'=>$args));
		$input = $args['input'];
		$route = $input['route'];
		//$a = explode('/',$route);
		//if (!isset($_GET['m']) && isset($a[1])) $_GET['m']=$a[1];
		//if (!isset($_GET['a']) && isset($a[2])) $_GET['a']=$a[2];
		
		$response = evRunWithBuf($this->view,'show',array());
		$content = $response['output'];
		
		//$content = evStrReplace($content,'src="js/','src="/js/');
		//$content = evStrReplace($content,'src="images/','src="/images/');
		
		//$content = evStrReplace($content,'href="not-ie.css','href="/not-ie.css');
		//$content = evStrReplace($content,'import "main.css','import "/main.css');
		//$content = evStrReplace($content,'href="ie.css','href="/ie.css');
		
		echo trim($content);
		
	}


}

?>