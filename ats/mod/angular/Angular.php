<?php 


class Angular extends Controller {
	
	public function htmlHeader($args=null){
		return $this->view->htmlHeader($args);
	}
	
	public function htmlApp($args=null){
		return $this->view->htmlApp($args);
	}
	
	
}
