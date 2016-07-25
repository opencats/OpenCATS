<?php
/*
 * OpenCATS
 * ACL Library
 * author: 
 */

include_once("./config.php");
    
class ACL
{
    static private $_instance;
    public $permissions;
    
    static function getInstance()
    {
		if (self::$_instance == null)
        {
            self::$_instance = new ACL();
        }
        return self::$_instance;
    }
    
	/* Prevent this class from being instantiated by any means other
	 * than getInstance().
	 */
	private function __construct() {
        $this->permissions = $this->populatePermissionList();
	}
    private function __clone() {}

    private function populatePermissionList()
    {
        $permissions = array();
        return $permissions;
    }
    
}
?>
