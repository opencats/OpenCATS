<?php 

class Settings extends Controller {
	
	public function __construct()
	{
		parent::__construct();
	}

	public static function Show(){
		return Settings::getInstance()->showView('MainView');
	}
	
	public function handleRequest($args){
		//vd(array('args'=>$args));
	}
	

}

?>