<?php 

abstract class Singleton {
	private static $instance = null;
	
	public static function getInstance2(){
		if (self::$instance == null){
			self::$instance = new static();
		}
		return self::$instance;
	}	
	
	public static function getInstance(){
 
		static $instances = array();
		$calledClass = get_called_class();
		//vd(array('$calledClass'=>$calledClass));
		if (!isset($instances[$calledClass])) {
			$instances[$calledClass] = new $calledClass();
		}
		return $instances[$calledClass];
	}
	
}


?>