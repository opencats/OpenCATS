<?php 

class Cats extends Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function getRoute($name){
		include_once('model/CatsRouteEnum.php');
		return CatsRouteEnum::main();
	}
	

	public function handleRequest($args){
		//vd(array('args'=>$args));
		$input = $args['input'];
		$route = $input['route'];
		$a = explode('/',$route);
		if (!isset($_GET['m']) && isset($a[1])) $_GET['m']=$a[1];
		if (!isset($_GET['a']) && isset($a[2])) $_GET['a']=$a[2];
		
		$response = evRunWithBuf($this->view,'show',array());
		$content = $response['output'];
		
		//$content = evStrReplace($content,'src="js/','src="/js/');
		//$content = evStrReplace($content,'src="images/','src="/images/');
		
		//$content = evStrReplace($content,'href="not-ie.css','href="/not-ie.css');
		//$content = evStrReplace($content,'import "main.css','import "/main.css');
		///$content = evStrReplace($content,'href="ie.css','href="/ie.css');
		
		echo trim($content);
		
	}
	public function getSiteId(){
		$this->incCats();
		return $_SESSION['CATS']->getSiteID();
	}
	public function getUserId(){
		$this->incCats();
		return $_SESSION['CATS']->getUserID();
	}
	
	public function getUserData(){
		$this->incCats();
		return $_SESSION['CATS']->getUserData();
	}
	
	private $included = false;
	private function incCats(){
		if ($this->included) return;
		ob_start();
		chdir(CATS_FE_DIR);
		include_once('./index_cats.php');
		$cntCats=ob_get_contents();
		ob_end_clean();
	}
	
	private function getArrayMd($aRS,$diType,$id){
		$result = E::loadCustomFieldValues(array(
				'id'=>$id,
				'siteId'=>$this->getSiteId(),
				'dataItemType'=>$diType,
		));
		if (!is_array($diType->fields)) echo "ERROR: Brak definicji pól dla ".$diType->name().".";
		//vd(array('$diType->fields'=>$diType->fields));
		foreach ($diType->fields as $k =>$v){			
			if (isset($v['dbColumn']) && isset($aRS[($v['dbColumn'])])){
				$result[$k]=$aRS[($v['dbColumn'])];
			}
		}
		if  ($diType->dbHandler != null){
			foreach ($diType->calcFields as $k =>$v){
				if (isset($v['handlerReadMethod'])){
					//vd(array('$diType->dbHandler'=>$diType->dbHandler));
					$result[$k]=call_user_func(array($diType->dbHandler,$v['handlerReadMethod']),array(
							'result'=>$result,
							'fieldName'=>$k,
							'fieldDef'=>$v,
					));
				}
			}
		}

		return $result;
	}
	
	public function getDb(){
		$this->incCats();
		return DatabaseConnection::getInstance();
	}
	
	public function getJobOrderPipelines($jobOrderId){
		$this->incCats();
		include_once('./lib/Pipelines.php');
		$pipelines = new Pipelines($this->getSiteId());
		$pipelinesRS = $pipelines->getJobOrderPipeline($jobOrderId);
		//vd(array('$pipelinesRS'=>$pipelinesRS));
		$result = array();
		$diType = E::dataItemType('jobOrderPipeline');
		if (is_array($pipelinesRS)){
			foreach($pipelinesRS as $k =>$v){
				$result[]=$this->getArrayMd($v, $diType, $jobOrderId);
			}
		}
		return $result;
	}
	
	public function getJobOrder($id){
		$this->incCats();
		include_once('./modules/joborders/JobOrdersUI.php');
		$jobOrders = new JobOrders($this->getSiteId());
		$jobOrderDetails = $jobOrders->get($id);
		return $this->getArrayMd($jobOrderDetails, E::dataItemType('jobOrder'), $id);
	}
	
	public function getCompany($id){
		$this->incCats();
		include_once('./modules/companies/CompaniesUI.php');
		$companies = new Companies($this->getSiteId());
		$data = $companies->get($id);
		return $this->getArrayMd($data, E::dataItemType('company'), $id);
	}

	public function getUser($id){
		$this->incCats();
		include_once('./modules/settings/SettingsUI.php');
		$users = new Users($this->getSiteId());
		$data = $users->get($id);
		return $this->getArrayMd($data, E::dataItemType('user'), $id);
	}
	
	private $calendar = null;
	
	public function calendar(){
		if ($this->calendar == null ){
			$this->incCats();
			include_once('./modules/calendar/CalendarUI.php');
			$this->calendar = new Calendar($this->getSiteId());
		}
		return $this->calendar;
	}
	
	//public function getCalendarHTMLOfLink($dataItemID, $dataItemType, $showTitle){
	//	return $this->calendar()->getHTMLOfLink($dataItemID, $dataItemType, $showTitle);
	//}
	
	public function reloadUserData(){
		$uData = $this->getUser($this->getUserId());
		$_SESSION['CATS']->setUserData($uData);
	}

}

?>