<?php 



class LibAuthObj {
	private $hAuth = null;
	
	function __construct($obj){
		$this->hAuth = $obj;
	}
	
	function getDefinedProviders(){
		return $this->hAuth->getDefinedProviders();
	}
	
	function getConnectedProviders(){
		return $this->hAuth->getConnectedProviders();
	}
	
	function isConnectedWith($name){
		return $this->hAuth->isConnectedWith($name);
	}
	
	function authenticate($name){
		return $this->hAuth->authenticate($name);
	}
	
}


class LibAuth {
	
	
	public static function create(){
		
		
		$config = array(
				// "base_url" the url that point to HybridAuth Endpoint (where index.php and config.php are found)
				"base_url" => SITE_URL,
		
				"providers" => array (
						// facebook
						"Facebook" => array ( // 'id' is your facebook application id
								"enabled" => true,
								"keys" => array ( "id" => "1002132683251695", "secret" => "929023ce8f7026a04a1edf2a72ad3382" ),
								"scope" => "email, user_about_me, user_birthday, user_hometown" // optional
						),
						// google
						"Google" => array ( // 'id' is your google client id
								"enabled" => true,
								"keys" => array ( "id" => "", "secret" => "" ),
						),
		

		
						// twitter
						"Twitter" => array ( // 'key' is your twitter application consumer key
								"enabled" => true,
								"keys" => array ( "key" => "", "secret" => "" )
						),
		
						// and so on ...
				),
		
				"debug_mode" => true ,
		
				// to enable logging, set 'debug_mode' to true, then provide here a path of a writable file
				"debug_file" => ATS_TEMP_DIR."/hauth.log",
		);
		
		
		include_once('hybridauth/src/autoload.php');
		$obj = new Hybridauth\Hybridauth( $config );
		$result = new LibAuthObj($obj);
		return $result;
	}

}