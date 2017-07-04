<?php 



class Front extends Controller {	
	
	private $contentController = null;
	private $module = null;
	private $action = null;
	
	//public static function Show(){		
	//	return Front::getInstance()->showView('MainView');		
	//}
	
	public function handleRequest(){
		$_startTime = microtime();
		$route='main';
		if (isset($_REQUEST['route'])){
			$route = $_REQUEST['route'];
		}
		$mainInput = array(
				'route'=>$route,
				'request'=>$_REQUEST,
				'files'=>$_FILES,
		);
		
		
		if ('main'==$route){
			
		} else {
			
			$ra=explode('/',$route);
			$module=$ra[0];
			$action=isset($ra[1])?$ra[1]:null;
			if ('cats'!=$module && 'ajax'!=$module){
				$frontRoute = $this->getRoute($module);

				if ($frontRoute!=null){
					//$className = get_class($frontRoute);
					//$mcl = lcfirst(evStrReplace($className,'RouteEnum',''));
					$module = $frontRoute->module;
					/*vd(array('$route'=>$route,
							'$frontRoute'=>$frontRoute,
							'$module'=>$module,
					));*/
				}
				
				//vincBe('locale/lang');
				$this->module=$module;
				$this->action=$action;
				
			} else {
				$this->module = (isset($_GET['m']))?$_GET['m']:null;
				$this->action = (isset($_GET['a']))?$_GET['a']:null;

			}
			if ('front'==$module) return;
			
			$this->contentController = evGetControllerObj($module,false);

			/*vd(array(
					'$this->contentController'=>$this->contentController
			));*/
			//die();
			if ($this->contentController==null){
				//$this->showView('error',array('message'=>__('No controller found').':'.$module));
				echo __('No controller found').':'.$module;
				die();
			} else {
				
				$contentControllerRoute = $this->contentController->getRoute($action);
				//vd(array(
				//	'$contentControllerRoute'=>$contentControllerRoute	
				//));
				if ($contentControllerRoute==null){
					echo sprintf(__('No route %s for module %s'),$action,$module);
					//$this->showView('error',array('message'=>'No route '.$action.' for module '.$module));
				} else {
					if ($contentControllerRoute->tab !=null){
						$_GET['m']=$contentControllerRoute->tab;
						//$_REQUEST['m']=$tab;
					}
					if ($contentControllerRoute->subTab !=null){
						$_GET['a']=$contentControllerRoute->subTab;
						//$_REQUEST['m']=$tab;
					}
					if ($contentControllerRoute->incCats){
						ob_start();
						chdir(CATS_FE_DIR);
						include_once('./index_cats.php');
						$cntCats=ob_get_contents();
						ob_end_clean();
					}
					
					
					$frontView = $contentControllerRoute->getFrontView();
					if ($frontView==null) $frontView='MainView';
					/*vd(array(
							'$module'=>$module,
							'$this->conentController'=>$this->contentController,
							'$contentControllerRoute'=>$contentControllerRoute,
							'$contentControllerRoute->name'=>$contentControllerRoute->name(),
							'$contentControllerRoute->getAttr(frontView)'=>$contentControllerRoute->getAttr('frontView'),
							'$frontView'=>$frontView
					));*/

					$response = evRunWithBuf($this->contentController,'handleRequest',array(
							'input'=>$mainInput,
							'route'=>$contentControllerRoute
					));
					//cho '<br/><br/><br/><br/><br/><br/><br/><br/>';
					/*vd(array(
						'$response'=>$response,	
					));*/
					$output = $response['output'];
					
					/*$response = evRunWithBuf($this->view,'show',array(
							'route'=>$contentControllerRoute,
							'view'=>$frontView,
							'content'=> $output
					));
					$content = $response['output'];
					
					$content = $this->view->filterOutput(array(
							'content'=>$content
					));
					echo $content;*/
					$this->view->show(array(
							'input'=>$mainInput,
							'route'=>$contentControllerRoute,
							'view'=>$frontView,
							'content'=> $output,
							'startTime'=>$_startTime,
					));
				}
			}
			
		}
		
	}
	
	
	function getModule(){
		return $this->module;
	}
	
	function getAction(){
		return $this->action;
	}
	
	function getActionRoute(){
		$route = $this->getRoute($this->getModule());
		if ($route!=null && $this->getAction()!=null){
			$actionRoute = $route->getSubRoute($this->getAction());
		} else {
			$actionRoute = $route;
		}
		return $actionRoute;
	}
	
	private $object = null;
	
	public function getObject(){
		return $this->object;
	}
	
	public function setObject($obj){
		$this->object=$obj;
	}
	
}



?>