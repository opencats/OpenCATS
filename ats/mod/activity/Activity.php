<?php 

class Activity extends Controller {

public function logActivity($args){
	return $this->model->logActivity($args);
}

}

