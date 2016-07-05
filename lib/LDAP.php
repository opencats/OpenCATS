<?php
/*
 * OpenCATS
 * LDAP Library
 * author: libregeek@gmail.com
 */

class LDAP
{
	static private $_instance;
	public  $failureUrl = '';
	private $_connection;
	private $_bind;

	static function getInstance()
	{
		if (self::$_instance == null)
		{
			self::$_instance = new LDAP();
		}
        if (!self::$_instance->_connection) {
			self::$_instance->connect();
		}
        if (!self::$_instance->_connection) {
            return NULL;
        }
		return self::$_instance;
	}


	/* Prevent this class from being instantiated by any means other
	 * than getInstance().
	 */
	private function __construct() {}
	private function __clone() {}


	public function connect()
	{
		$this->_connection = @ldap_connect(LDAP_HOST, LDAP_PORT);
		if (!$this->_connection)
		{
			return false;
		}
		@ldap_set_option($this->_connection, LDAP_OPT_PROTOCOL_VERSION, LDAP_PROTOCOL_VERSION);
		return true;
	}

	public function authenticate($username, $password)
	{   
		if ($this->_connection) 
		{
			if (LDAP_BIND_DN != "") 
			{
				$this->_bind = @ldap_bind($this->_connection, LDAP_BIND_DN, LDAP_BIND_PASSWORD);
				if(!$this->_bind) 
				{
					$this->_bind = NULL;
					return false;
				}

				$search = @ldap_search( $this->_connection, LDAP_BASEDN, '('.LDAP_ATTRIBUTE_UID . '=' . $username.')');
				if (!$search) 
				{
					return false;
				}

				$result = @ldap_get_entries( $this->_connection, $search);
				if ($result[0]) 
				{
					if (ldap_bind( $this->_connection, $result[0][LDAP_ATTRIBUTE_DN][0], $password) ) 
					{
						return true;
					}
					else 
					{
						return false;
					}
				}
			}
			else 
			{
                $trans = array('{$username}' => $username);
                $username = strtr(LDAP_ACCOUNT, $trans);
				$this->_bind = @ldap_bind($this->_connection, $username, $password);
				if(!$this->_bind) 
				{
					$this->_bind = NULL;
					return false;
				}
				return true;
			}
		}
	}

	public function searchUid($username)
	{
		$search = @ldap_search( $this->_connection,
				LDAP_BASEDN, LDAP_ATTRIBUTE_UID . '=' . $username);
		if ($search) 
		{
			$result = @ldap_get_entries( $this->_connection, $search);
			if ($result[0]) 
			{
				return true;
			}
			else 
			{
				return NULL;
			}
		}
	}

    public function getUserInfo($username)
    {
        $search = @ldap_search( $this->_connection,
				LDAP_BASEDN, LDAP_ATTRIBUTE_UID . '=' . $username);
		
        if ($search) 
		{
			$result = @ldap_get_entries( $this->_connection, $search);
			$userInfo = array($result[0][LDAP_ATTRIBUTE_LASTNAME][0], $result[0][LDAP_ATTRIBUTE_FIRSTNAME][0], $result[0][LDAP_ATTRIBUTE_EMAIL][0], $result[0][LDAP_ATTRIBUTE_UID][0]);
            return $userInfo;
		}
        
        return NULL;
    
    }
}
?>
