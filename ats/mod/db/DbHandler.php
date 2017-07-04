<?php 

abstract class DbHandler extends Singleton {
	
	private static $db = null;
	
	public function db(){
		if (self::$db == null){
			self::$db = E::db();
		}
		return self::$db;
	}
	
}
