<?php 


class dbh_mysql {
	private $catsDb = null;
	private $link = null;
	
	public function __construct($catsDb){
		$this->catsDb = $catsDb;
		$this->link=$this->catsDb->getConnection();
		//vd(array(
		//		'$this->link'=>$this->link,
		//		));
	}
	
	public function executeSQL($args){
		$sql = $args['sql'];
		$unbuf = false;
		
		if ($unbuf){
			$result=mysql_unbuffered_query($sql,$this->link);
		} else {
			$result=mysql_query($sql,$this->link);
		}
		
		return $result;
	}
	
	public function fetchArray($result){
		 $res = mysql_fetch_array($result);
		 return (is_array($res))?$res:array();
	}
	
	public function errorMessage(){
		$error=mysql_error($this->link);
		return $error;
	}
	
	public function insertId(){
		return mysql_insert_id($this->link);
	}
}