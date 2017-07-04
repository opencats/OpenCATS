<?php 

class Model {
	private $db = null;
	

	
	protected function db(){
		if ($this->db==null){
			$this->db=E::c('db');
		}
		return $this->db;
	}
	
}


?>