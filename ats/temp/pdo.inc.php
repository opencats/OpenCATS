<? 
/**
 * PDO DB Handler
 * @ev:parent:core/core/db/handlers
 */
 
function _ev_constr($args){
	$this->errorExc = null;
	$this->last_affected_rows = null;
	$this->resources = array();
	_ev_pconstr($args);
	$this->dbTypePDO=$this->getEnv('dbTypePDO');
	}

/*
 * Connection to db
*/
function doConnect($host='',$user='',$pass='',$name='',$connectfunc='pdo_connect'){
	if ($host=='') $host=$this->dbHost;
	if ($user=='') $user=$this->dbUser;
	if ($pass=='') $pass=$this->dbPass;
	if ($name=='') $name=$this->dbName;
	$sqOptArr=array(PDO::ATTR_PERSISTENT => ($connectfunc=='pdo_pconnect'));
	
	if ($host=='localhost') $host='127.0.0.1';
	try{
		$this->errorExc=null;
		$this->Id = new PDO($this->dbTypePDO.':host='.$host.';dbname='.$name, $user, $pass,$sqOptArr);
		$this->query('SET NAMES utf8;');
		$this->query('SET CHARACTER SET utf8;');
		
		}
	catch (PDOException $e) {
		$this->errorExc=$e;
		$this->intError($this->Error());
		return false;
		}
	return $this->Id;
	}


/*
 * Connect to db
 */
function Connect($host='',$user='',$pass='',$name=''){
	return $this->DoConnect($host,$user,$pass,$name,'pdo_connect');		
	}

/*
 * Persistent connect
 */
function PConnect($host='',$user='',$pass='',$name=''){
	return $this->DoConnect($host,$user,$pass,$name,'pdo_pconnect');		
	}
	
	
/**
 * Get error message for last operation
 */	
function Error(){
	$error='';
	if ($this->errorExc!=null){
		$error=$this->errorExc->getCode().':'.$this->errorExc->getMessage();
		}
	return _ev_parent::Error($error);
	}	
	
	
/**
 * Get error number for last operation
 */	
function errno(){
	$error=0;
	if ($this->errorExc!=null){
		$error=$this->errorExc->getCode();
		}	
	return $error;
	}	
	
/*
 * DB query
 */
function query($sql,$errmsgpostfix='',$unbuf=false){
	$this->ErrorPostFix=$errmsgpostfix;
	//$ret=$this->db_query($this->dbName,$sql, $this->Id,$unbuf);
	$res=array('sql'=>$sql);
	$ret=false;
	try{
		
/*		if (strtoupper(substr($sql,0,6))=='SELECT'){
			$res['rows']=$this->Id->query($sql);
			$res['numRows']=count($res['rows']);
			$res['rowCounter']=0;
			$ret= true;
			} 
		else {
			$res['numRows']=$this->Id->exec($sql);
			$this->last_affected_rows = $res['numRows'];
			$ret=true;
			if (strtoupper(substr($sql,0,6))=='INSERT'){
				$res['insertId']=$this->Id->lastInsertId();
				$this->last_insert_id =  $res['insertId'];
				}
			}
		if ($ret){
			$this->resources[]=$res;
			$ret=count($this->resources)-1;
		}	*/
		$stmt=$this->id->prepare($sql);
		if ($stmt->execute()){
			$this->last_affected_rows=$stmt->rowCount();
			return $stmt;
			}
		}
	catch(PDOException $e){
		$res['exc']=$e;
		$this->errorExc=$e;
	}	
	
	
	
	return $ret;
	}	
	
//TODO moze nie dzialac dla select
function num_rows($result) {return  $result->rowCount();/*$this->resources[$result]['numRows'];*/}		

function fetch_array($result){
	/*$ret = $this->resources[$result]['rows'][($this->resources[$result]['rowCounter'])]; 
	$this->resources[$result]['rowCounter']++; 
	return $ret;*/
	return $result->fetch(PDO::FETCH_BOTH);
	}
	
function insert_id(){ return $this->Id->lastInsertId();/*$this->last_insert_id;*///mysql_insert_id($this->Id);
}	

function affected_rows(){return $this->last_affected_rows;}

function fetch_object($res)	{return $res->fetchObject();}
function fetch_row($res){return $res->fetch(PDO::FETCH_NUM);}

