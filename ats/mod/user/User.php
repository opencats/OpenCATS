<?php 

class User extends Controller {

	public function getCurrentUserData(){
		return E::c('cats')->getUserData();
	}
	
	public function getCurrentUserId(){
		return E::c('cats')->getUserId();
	}
	public function getCurrentUserSiteId(){
		return E::c('cats')->getSiteId();
	}
	
	public function getUserById($id){
		return $this->model()->getUserById($id);
	}

	public function updateProfile($args){
		$id = $args['fs']['id'];
		$res = $this->model()->updateProfile($args);
		if ($id ==$this->getCurrentUserId()){
			E::c('cats')->reloadUserData();
		}
	}
	
}