<?php 

class FrontView extends View {
	
	protected function showView($viewName,$args){
		return include('view/'.$viewName.'.php');
	}
	
	public function show($args){
		$view = $args['view'];
		$_startTime = $args['startTime'];
		$inputRoute = $args['input']['route'];
		
		$footer = evGetGlobal('printFooter');
				
		if (evStrContains($footer['output'],'<div class="footerBlock">')){
			$footerBlock='<span id="footerResponse">ATS Response Time: '.evGetExecTime($_startTime).' seconds.</span>';
			$footerBlock.='<br/><span id="footerResponse">ATS Version: '.ATS_VERSION.'.</span>';
			$footer['output'] = evStrReplace($footer['output'],'<div class="footerBlock">','<div class="footerBlock" style="text-align:center;">'.$footerBlock);
		}
		evSetGlobal('printFooter',$footer);
		return $this->showView('Front'.$view,$args);
		//include_once('view/Front'.$view.'.php');
	}
	
}

?>