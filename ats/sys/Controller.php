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
	}
	
	public function getRoute($name){
		$className = get_class($this);
		$classname = evStrToLower($className);
		evIncBe('mod/'.$classname.'/model/'.$className.'RouteEnum');
		if (evStrEmpty($name)) $name = 'main';
		return call_user_func($className.'RouteEnum::'.$name);
	}
	
	
}

?>