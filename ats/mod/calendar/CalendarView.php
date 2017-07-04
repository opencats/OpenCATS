<?php 
namespace ats;

class CalendarView extends \View {

	protected function showView($viewName,$args){
		include('view/'.$viewName.'.php');
	}
	
	/*function show($args){
		//evIncBe('lib/phpexcel');
		$route = $args['route'];
		include_once('view/'.$route->view.'.php');
	}*/
	
	function showEditor($args){
		$this->showView('CalendarEditorView',$args);
	}
	
	public function getHTMLOfLink($dataItemID, $dataItemType, $showTitle = true){
		return \E::c('cats')->calendar()->getHTMLOfLink($dataItemID, $dataItemType, $showTitle);
	}
	
}

?>