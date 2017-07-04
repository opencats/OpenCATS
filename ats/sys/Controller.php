<?php 
include_once('Singleton.php');
class Controller extends Singleton {
	
	protected $model = null;
	protected $view = null;
	
	public function __construct()
	{
		//parent::__construct();
		$this->model = evCreateModelObj(get_class($this));
		$this->view = evCreateViewObj(get_class($this));
		$this->view->setModel($this->model);
	}
	
	public function getRoute($name){
		$className = evClassName($this);
		$classname = evStrToLower($className);
		evIncBe('mod/'.$classname.'/model/'.$className.'RouteEnum');
		if (evStrEmpty($name)) $name = 'main';
		$result = null;
		$fcname = $className.'RouteEnum';
		if (call_user_func($fcname.'::hasName',$name)){
			$result = call_user_func($fcname.'::'.$name);
		}
		return $result;
	}
	
	public function handleRequest($args){
		$route = $args['route'];
		$input = $args['input'];
		//vd(array('args'=>$args));
		$fs = evArrDflt($args['input']['request'],'fs',null);//aby miec detekcję czy formularz byl wysylany
		$fl = evArrDflt($args['input']['request'],'fs',array());// aby miec zaladowane dane
		$response = evRunWithBuf($this->view,'show',array(
				'route'=>$route,
				'input'=>$input,
				'fs'=>$fs,
				'fl'=>$fl,
		));
		$content = $response['output'];
		echo trim($content);
	}
	
	protected function model(){
		return $this->model;
	}
	
	protected function view(){
		return $this->view;
	}
	
}

?>