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
			self::$_instance->connect();
			self::$_instance->bind();
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
		if (!$this->_connection){
			die("Error Connecting to LDAP Server".ldap_error($this->_connection));
		}

		if (!$this->_connection)
		{
			return false;
		}
		@ldap_set_option($this->_connection, LDAP_OPT_PROTOCOL_VERSION, LDAP_PROTOCOL_VERSION);
		return true;
	}

	public function bind()
	{
	print "GHG";
		if ($this->_connection) 
		{
			if (LDAP_BIND_DN != "") 
			{
				$this->_bind = @ldap_bind($this->_connection, LDAP_BIND_DN, LDAP_BIND_PASSWORD);
			}
			else 
			{
				$this->_bind = @ldap_bind($this->_connection);
			}
			if(!$this->_bind) 
			{
				die ("LDAP bind error: ". ldap_error($this->_connection));
			}
		}
	}

	public function authenticate($username, $password)
	{   
		$search = @ldap_search( $this->_connection, LDAP_BASEDN, '('.LDAP_UID . '=' . $username.')');
		if ($search) 
		{
			$result = @ldap_get_entries( $this->_connection, $search);
			if ($result[0]) 
			{
				if (ldap_bind( $this->_connection, $result[0]['dn'], $password) ) 
				{
					return true;
				}
				else 
				{
					return NULL;
				}
			}
		}
	}

	public function searchUid($username)
	{
		$search = @ldap_search( $this->_connection,
				LDAP_BASEDN, LDAP_UID . '=' . $username);
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

}
?>
