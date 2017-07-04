<? 
/**
 * MySQL DB Handler
 * @ev:parent:core/core/db/handlers
 */
 
function _ev_constr($args){
	_ev_pconstr($args);
	}
	
/*
 * Connection to db
*/
function doConnect($host='',$user='',$pass='',$name='',$connectfunc='mysql_connect'){
	if ($host=='') $host=$this->dbHost;
	if ($user=='') $user=$this->dbUser;
	if ($pass=='') $pass=$this->dbPass;
	if ($name=='') $name=$this->dbName;
	
	if ($host=='localhost') $host='127.0.0.1';
    //$link = mysql_init();

	//mysql_options($link, MYSQLI_OPT_LOCAL_INFILE, TRUE);
		
	$link = mysql_connect($host, $user, $pass,FALSE,128);
	//@mysqli_real_connect($link, $cfg['Server']['host'], $user, $password, FALSE, $server_port, $server_socket, $client_flags);
	//@mysql_real_connect($link, $host, $user, $pass);
	
	if ($link){
		$this->Id = $link; 
		// choosing db 
		if (mysql_select_db($name,$this->Id)){
		   	//if (//vphpincISO2UTF==1){
			    $this->query('SET NAMES utf8;');
		   		$this->query('SET CHARACTER SET utf8;');
			 //  	}
			return $this->Id;
			}
		else
			$this->intError(mysql_error());
		}
	else
		$this->intError($this->getStr('ConnectionError'));     
	return false; //error ocured
	}

/*
 * Connect to db
 */
function Connect($host='',$user='',$pass='',$name=''){
	return $this->DoConnect($host,$user,$pass,$name,'mysql_connect');		
	}

/*
 * Persistent connect
 */
function PConnect($host='',$user='',$pass='',$name=''){
	return $this->DoConnect($host,$user,$pass,$name,'mysql_pconnect');		
	}

/**
 * Get error message for last operation
 */	
function Error(){
	$error=mysql_error($this->Id);
	return _ev_parent::Error($error);
	}	
	
/**
 * Get error number for last operation
 */	
function errno(){
	$error=mysql_errno($this->Id);
	return $error;
	}	
	
/**
 * DB query
 */
 //!MB error handling mysql only
function db_query($dbname,$sql,$id='',$unbuf=false){
	//$result=mysql_db_query($dbname,$sql,$id);
	if ($result=mysql_select_db($dbname,$id)){
	if ($unbuf)	
		$result=mysql_unbuffered_query($sql,$id);
	else
		$result=mysql_query($sql,$id);
	if ($id=='') $id=$this->Id;

	if (strtoupper(substr($sql,0,6))=='INSERT'){
		$this->last_insert_id=mysql_insert_id($id);
		}
	$this->ErrorPostFix='';
	} else {
		$this->intError('mysqlError: Cannot select db='.$dbname);
	}
	return $result;
	}	
	
	
/*
 * DB query
 */
function query($sql,$errmsgpostfix='',$unbuf=false){
	$this->ErrorPostFix=$errmsgpostfix;
	$ret=$this->db_query($this->dbName,$sql, $this->Id,$unbuf);
	return $ret;
	}	
	

//!mbtodo obsolete
function ExecuteSQL($sql,$errmsgpostfix='',$unbuf=false)	{return $this->query($sql,$errmsgpostfix,$unbuf);}
	


// number of rows
function num_rows($result) {return mysql_num_rows($result);}

// number of affected rows
/*function affected_rows($result=null) {
	if ($result==null)
		$result=$this->Id;
	return mysql_affected_rows($result);
	}*/	
	
function fetch_array($result){return mysql_fetch_array($result);}

function insert_id(){ return $this->last_insert_id;//mysql_insert_id($this->Id);
}

function affected_rows(){return mysql_affected_rows($this->Id);}

function fetch_object($res)	{return mysql_fetch_object($res);}
function fetch_row($res){return mysql_fetch_row($res);}

function fetch_field($res){return mysql_fetch_field($res);}

function free_result($res){return	mysql_free_result($res);}

function field_table($res){return mysql_field_table($res,0);}

function field_len($res,$i)	{return mysql_field_len($res,$i);}

function num_fields($res){return mysql_num_fields($res);}

function result($res,$ind){return mysql_result($res,$ind);}	