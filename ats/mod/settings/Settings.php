<?php 

class Settings extends Controller {
	
	public function __construct()
	{
		parent::__construct();
	}

	public static function Show(){
		return Settings::getInstance()->showView('MainView');
	}
	
	/*public function handleRequest($args){
		$input = $args['input'];
		$request = $input['request'];
		$_GET['m']='settings';
		$_REQUEST['m']='settings';
		if ($args['route']==SettingsRouteEnum::auth()){
				$type = $request['type'];
				evIncBe('lib/auth');
				$auth = LibAuth::create();
				//$res = $auth->authenticate('Facebook');
				
				//vd(array('args'=>$args));
		} else {
			//vIncBe('lib/auth');
			parent::handleRequest($args);
		}
	}*/
	
	public function authProfile(){
		evIncBe('lib/auth');
		include('view/authProfile.php');
	}
	

}

?>