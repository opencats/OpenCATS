<?php 



class Front extends Controller {	
	
	private $contentController = null;
	
	//public static function Show(){		
	//	return Front::getInstance()->showView('MainView');		
	//}
	
	public function handleRequest(){
		$route='/';
		if (isset($_REQUEST['route'])){
			$route = $_REQUEST['route'];
		}
		$mainInput = array(
				'route'=>$route,
				'request'=>$_REQUEST
		);
		if ('/'==$route){
			
		} else {
			$ra=explode('/',$route);
			$module=$ra[0];
			if ('cats'!=$module && 'ajax'!=$module){
				evincBe('locale/lang');
			}
			$action=isset($ra[1])?$ra[1]:null;
			$this->contentController = evGetControllerObj($module);
			if ($this->contentController==null){
				//$this->showView('error',array('message'=>__('No controller found').':'.$module));
				echo __('No controller found').':'.$module;
				die();
			} else {
				//vd(array('$module'=>$module,'$this->conentController'=>$this->contentController));
				$contentControllerRoute = $this->contentController->getRoute($action);
				if ($contentControllerRoute==null){
					echo sprintf(__('No route %s for module %s'),$action,$module);
					//$this->showView('error',array('message'=>'No route '.$action.' for module '.$module));
				} else {
					$frontView = $contentControllerRoute->getFrontView();
					$response = evRunWithBuf($this->contentController,'handleRequest',array(
							'input'=>$mainInput,
							'route'=>$contentControllerRoute
					));
					$this->view->show(array(
							'route'=>$contentControllerRoute,
							'view'=>$frontView,
							'content'=> $response['output']
					));
				}
			}
			
		}
		
	}
	
}



?>