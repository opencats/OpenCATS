<?php 

class Ui extends Controller {
		
	public function isOldUI($name){
		$uData = E::c('user')->getCurrentUserData();
		$oldUI = $uData['oldUI'];
		$a = explode(',',$oldUI);
		return in_array($name,$a);
	}
	
}