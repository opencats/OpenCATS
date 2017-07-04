<?php 
namespace ats;

class Calendar extends \Controller {

	public function showCalendarEditor($args){
		return $this->view->showEditor($args);
	}
	
	public function getEventById($id){
		return $this->model()->getEventById($id);
	}

}