<? 
/**
 * @ev:parent:core/object
 * Core class for database abstraction layer. DBH member represents physical
 * database service.
 */
class e{

var $DBH=null; // database handle

/**
 * Constructor
 */
function _ev_constr($args){
	$this->DBH=null;
	$this->doHist=false;
	$this->histAliases=null;
	_ev_pconstr($args);
	$this->LocalizedFields=false;
	$this->dbPrefix=$this->getEnv('dbPrefix');
	//evcore_object::evcore_object($args);
	$this->p_StrKeys[]='core/db';
	$this->dbVersion='';
	$this->dbAliases=array();
	//$this->__sysEnvCache=array();
	$this->addAlias(
			'sys.cfg',array(
			'table'=>'sys_cfg',
			'tsql'=>array(
				'200502112001'=>'CREATE TABLE `sys_cfg` (`cfg_id` int(11) NOT NULL auto_increment,`cfg_path` varchar(100) default NULL, `cfg_xml` text,  PRIMARY KEY  (`cfg_id`));',
				'200512250201'=>'ALTER TABLE `sys_cfg` ADD INDEX `cfg_path` ( `cfg_path` );',
				),
			'tinfo'=>array(
				)
			));
	$this->addAlias(
			'sys.ser',array(
			'table'=>'sys_ser',
			'tsql'=>array(
				'200502251301'=>"CREATE TABLE `sys_ser` ( `sr_id` int(11) NOT NULL auto_increment, `sr_code` varchar(60) default NULL, `sr_data` text, `sr_datetime` datetime NOT NULL default '0000-00-00 00:00:00', `sr_timestamp` timestamp NOT NULL default now(), `sr_counter` int(11) NOT NULL default '1', PRIMARY KEY  (`sr_id`), UNIQUE KEY `sr_code` (`sr_code`));"
				),
			'tinfo'=>array(
				)
			));
	$this->addAlias(
		'sys.env',array(
			'table'=>'sys_env',
			'tsql'=>array(
				'200503041401'=>"CREATE TABLE sys_env ( env_id int(11) NOT NULL auto_increment, env_path varchar(100), env_xml text, PRIMARY KEY (env_id));",
				'200512250201'=>'ALTER TABLE `sys_env` ADD INDEX `env_path` ( `env_path` );',
				),
			'tinfo'=>array(
				)
			));			

	$arr2=array();
	$this->setCfg('dict',$arr2);
	$this->Inc(array('path'=>'core/core/db/handlers/'.$this->GetEnv('dbType')));
	$this->Inc(array('path'=>'core/db/handlers/'.$this->GetEnv('dbType')));
	
	if ($this->DBH==null)
	if ($this->getEnv('dbConnect')==evT){
		$this->Connect();
		}
		
		
	$this->dbDict=&$this->GetCfg('dict');
	$this->syncObj=null;
	}


/**
 * Std conversion from db array to asoc
 */
 
function a2Asoc($rec,$pref){
	$aret=array();
	$pref=$pref.='_';
	foreach($rec as $k=>$v){
		$kc=(strpos(';'.$k,$pref)==1)?substr($k,strlen($pref)):$k;
		$aret[$kc]=$v;
		}	
	return $aret;
	} 

/**
 * Conversion of upgrade queries
 */
function convertTableQueryToPrefix($arg){
	$alias=$arg['alias'];
	$ntable=$arg['ntable'];
	$value=$arg['qry'];
	$a=&$this->dbAliases[$alias]['tsql'];
	$otable=$this->dbAliases[$alias]['tableWoPrefix'];
	return str_replace($otable,$ntable,$value);	
	}

/**
 * function converts the name of an given table in sql definitions
 */
function convertTableNameSQL($arg){
	$alias=$arg['alias'];
	$ntable=$arg['ntable'];
	$a=&$this->dbAliases[$alias]['tsql'];
		
	$otable=$this->dbAliases[$alias]['table'];
	$this->dbAliases[$alias]['tableWoPrefix']=$this->dbAliases[$alias]['table'];
	$this->dbAliases[$alias]['table']=$ntable;
	
	if (count($a)>0)
	foreach ($a as $key => $value){
		$a[$key]=str_replace($otable,$ntable,$value);	
		}
	}

/**
 * Add aliasInfo to internal structures
 */
function addAlias($aname,$adef){
	$this->dbAliases[$aname]=$adef;
	$this->setEnv('dbAliases/'.$aname,$adef);
	$GLOBALS[dbgAls]=0;
	if (!evEmpty($this->dbPrefix)){
		if (isset($adef['ignoreDbPrefix']))
			if ($adef['ignoreDbPrefix']==evT)
				return;
		$ntable=$this->dbPrefix.$adef['table'];
		$GLOBALS[dbgAls]=1;
		$this->convertTableNameSQL(array('alias'=>$aname,'ntable'=>$ntable));
		}
	}

/**
 * Adding an alias with automated tinfo generation from aname
 */
function addAliasDefault($aname,$adef){
	$minfo=explode('.',$aname);	
	$adef['tinfo']=array(
		'md'=>$minfo[0],
		'primKey'=>$minfo[1].'_id',
		'fields'=>array(
			$minfo[1].'_id'=>array(
				'visible'=>array(
					'edit'=>evT,
					'browse'=>evT
					),
				'caption'=>'Identyfikator'
				),
			)
		);
	return $this->addAlias($aname,$adef);
	}
	
/**
 * Add a single upgrade query
 */
function addTSql($aname,$adef){
	foreach($adef as $k=>$v){
		if (!evEmpty($this->dbPrefix)){
			$ntable=$this->dbAliases[$aname]['table'];
			$v=$this->convertTableQueryToPrefix(array('alias'=>$aname,'ntable'=>$ntable,'qry'=>$v));
			}		
		$this->dbAliases[$aname]['tsql'][$k]=$v;
		}
	}

/**
 * Get a value from sys.env table
 */
function getSysEnv($name){
	$res=$this->callDB('sys','Select',array('alias'=>'sys.env','cond'=>'env_path="'.$name.'"'));
	$numrows=$this->callDB('sys','num_rows',array('res'=>$res));
	if ($numrows==0) return '';
	else{
		$rec=$this->callDB('sys','fetch',array('res'=>$res));
		//$this->dbVersion=$rec['env_xml'];
		return $rec['env_xml'];
		}	
	}

/**
 * Set value in sys.env table
 */	
function setSysEnv($name,$value){
	$res=$this->callDB('sys','Select',array('alias'=>'sys.env','cond'=>'env_path="'.$name.'"'));
	$numrows=$this->callDB('sys','num_rows',array('res'=>$res));
	if ($numrows>0)
		$res=$this->callDB('sys','update',array('alias'=>'sys.env','cond'=>'env_path="'.$name.'"','arr'=>array('env_xml'=>$value)));
	else
		$res=$this->callDB('sys','insert',array('alias'=>'sys.env','arr'=>array('env_path'=>$name,'env_xml'=>$value)));
	return $res;
	}


/**
 * Read db structure version
 */
function getDBVersion(){
	if (!empty($this->dbVersion)) return $this->dbVersion;
	$dbVer=$this->getSysEnv($this->p_StrKey.'/Version');
	if (empty($dbVer)) $dbVer='0';
	$this->dbVersion=$dbVer;
	return $dbVer;
	}

/**
 * Set db structure version
 */
function setDBVersion($dbVersion){
	$res=$this->setSysEnv($this->p_StrKey.'/Version',$dbVersion);
	if ($res) $this->dbVersion=$dbVersion;	
	}

/**
 * Checks and upgrades db structure to apropiate version (internal)
 */
function _Setup($arg) {
	//ev_dbg('dbSetup:'.get_class($this));
	// default parameter value setting 
	if (count($arg)>0){
		$dfrom=$arg['dfrom'];
		$dto=$arg['dto'];
		}
	else {
		$dfrom=$this->getDBVersion();
		$dto='999999999999';
		}

	$ret=$dfrom;
	
	$bSetDBVersion=false;

	// sql upgrade statements comes from aliasInfo structures	
	$al=$this->dbAliases;
	
	// gather sql statements for db upgrade 
	$sqlA=array();
	if (count($al)>0)
	foreach ($al as $k => $v){
		if (count($v['tsql'])>0)
		foreach( $v['tsql'] as $k2 => $v2){
			//$this->e/vDbg('d2:'.$k2);
			if (($k2>$dfrom) && ($k2<=$dto)){
				if (!isset($sqlA[$k2])){ // no prevoius elems
					$sqlA[$k2]=$v2;
					} 
				else if (is_array($sqlA[$k2])) { // set of sql's 
					$sqlA[$k2][]=$v2;
					} 
				else { //one sql - make array
					$tmpv=$sqlA[$k2];
					$sqlA[$k2]=array();
					$sqlA[$k2][]=$tmpv;
					$sqlA[$k2][]=$v2;
					}		
				}
			}
		} 
	
	// sort queries and execute
	if (count($sqlA)>0){
		$bSetDBVersion=true;
		ksort($sqlA);
		foreach ($sqlA as $k2 => $v2) {	
			if (is_array($v2))
				foreach($v2 as $k4=> $v4){
					echo 'qry:'.$v4."<br/>";
					$this->Execute(array('qry'=>$v4));
					}
			else{
				echo 'qry:'.$v2."<br/>";
				$this->Execute(array('qry'=>$v2));
				}
			if ($k2>$ret) $ret=$k2;
			}
		}
	
	// set new version number 
	if ($bSetDBVersion)
		$this->setDBVersion($ret);	
	
	$this->setEnv($this->p_StrKey.'/Version',$ret);
	
	return $ret;
	}
	
/**
 * Checks and upgrades db structure to apropiate version
 */
function Setup($arg) {return $this->_Setup($arg);}


/**
 * Connect to database
 */
function Connect(){
	$this->DBH=$this->Obj(array('path'=>'core/db/'.$this->getEnv('dbType'),'tmp'=>evT));
	if ($this->DBH!=null){
		if ($this->TableExists('hist'))
			$this->doHist=true;
		// check for additional history tables
		if ($this->histAliases!=null)
			$this->doHist=true;
		/*//ev_dbg(array(
			'histAliases'=>$this->histAliases,
			'doHist'=>$this->doHist,
			),'Connect');*/		
		}
	return ($this->DBH!=null);
	}

/**
 * Reconect to given database
 * @ev:arg dbName - name of database to connect to
 */
function reConnect($arg){
	$this->DBH=$this->Obj(array('path'=>'core/db/'.$this->getEnv('dbType'),'tmp'=>evT));
	$this->DBH->dbName=$arg['dbName'];
	$this->DBH->doConnect();
}

/**
 * Check workflow db triggers for actions
 */
function CheckWflowTriggers($trtype,$alias,&$arr,$arrp=''){
	$ret=true;
	if ($this->getInst('wflow'))
		{
		$msg=$this->CheckTriggersMsg('wflow_'.$trtype,$alias,$arr,$arrp);
		$ret=evEmpty($msg);	
		}
	if (!$ret)
		$this->intError('Acces denied (wflow - '.$trtype.','.get_class($this).','.$alias.') - '.$msg);
	return $ret;
	}
	
/**
 * Process triggers returning error message
 */	
function CheckTriggersMsg($trtype,$alias,&$arr,$arrp=''){
	$ret='';
	$trname=$this->dbAliases[$alias]['triggers'][$trtype];
	if (is_array($trname)){
		//$md='';
		if (isset($trname['md'])){
			$md=$trname['md'];
		}
		else {
			$mdpath=$this->p_Path;
			if (strpos('_'.$mdpath,'md/')==1){
				$mdpath=str_replace('md/','',$mdpath);
				}
			$md=$mdpath.'/triggers';			
			
		}	
		$mt=$trname['mt'];
		$ret=$this->callMD($md,$mt,array(
		'alias'=>$alias,
		'arr'=>&$arr,
		'arrp'=>&$arrp,
		'trtype'=>$trtype,
		));
		}
	else
		//if (!evEmpty($trname)) { $ret=$this->$trname($alias,$arr,$arrp,$trtype);}
		if (!evEmpty($trname)) { $ret=$this->$trname(
			array(
				'alias'=>$alias,
				'arr'=>&$arr,
				'arrp'=>&$arrp,
				'trtype'=>$trtype,
			)
		);
		}
	return $ret;		
	}

/*
 * Process triggers returning bool
 */	
function CheckTriggers($trtype,$alias,&$arr,$arrp=''){
	return evEmpty($this->CheckTriggersMsg($trtype,$alias,$arr,$arrp));		
	}

/*
 * Process all triggers returning bool
 */	
function CheckAllTriggers($trtype,$alias,&$arr,$arrp=''){
	$ret=$this->CheckWflowTriggers($trtype,$alias,$arr,$arrp);
	if ($ret) $ret=$this->CheckTriggers($trtype,$alias,$arr,$arrp);
	return $ret;
	}

/*
 * Get table info for given alias
 */	
function getDBCfg($alias){
	if (is_array($alias)){
		$alias=$alias['alias'];
		}
	return $this->dbAliases[$alias]['tinfo'];
	}

function addHistAlias($arg){
	$md=$arg['md'];
	$dbVersion = '20091104';
	if (isset($arg['dbVersion']))
		$dbVersion = $arg['dbVersion'];
	$tablename=$arg['tableName'];
	$histAlias='hist';
	if (isset($arg['alias']))
		$histAlias=$arg['alias'];
	if (isset($arg['forAlias'])){
		$this->histAliases[($arg['forAlias'])]=$histAlias;
		}	
 	$this->addAlias($histAlias,array(
			'table'=>$tablename,//'tj_hist',
			'tsql'=>array(
				$dbVersion.'0001'=>"CREATE TABLE `".$tablename."` ( `hist_id` int(11) NOT NULL auto_increment,  `hist_path` varchar(160) NOT NULL default '',  `hist_oper` varchar(1) NOT NULL default '',  `hist_serialized` LONGBLOB ,  `hist_ret` int(4) NOT NULL default '0',  `hist_user_ip` varchar(20) NOT NULL default '',  `hist_datetime` timestamp NOT NULL default CURRENT_TIMESTAMP,  PRIMARY KEY  (`hist_id`));",
				$dbVersion.'0002'=>"ALTER TABLE `".$tablename."` ADD `hist_md` VARCHAR( 20 ) NOT NULL , ADD `hist_alias` VARCHAR( 30 ) NOT NULL ,ADD `hist_dbversion` VARCHAR( 12 ) NOT NULL ;",
				$dbVersion.'0003'=>"ALTER TABLE `".$tablename."` DROP `hist_path`;",
				$dbVersion.'0004'=>"ALTER TABLE `".$tablename."` ADD `hist_code` VARCHAR( 120 ) NOT NULL , ADD `hist_parentid` INT(11) NOT NULL default 0, ADD `hist_comment` TEXT NOT NULL;",
				$dbVersion.'0005'=>"ALTER TABLE `".$tablename."` CHANGE `hist_comment` `hist_comments` TEXT NOT NULL", 
				),
			'tinfo'=>array(
				'md'=>$md,
				'primKey'=>'hist_id',
				'fields'=>array(
					'hist_id'=>array(
						'visible'=>array(
							'edit'=>evT,
							'browse'=>evT
							),
						'caption'=>'Identyfikator'
						),
					)
				)
			));	
	}

/*
 * Store audit info in db
 */	
function SaveHistory($arr){
	$primvalue='';
	$insId='';
	$cond='';
	$hQry=$arr['qry'];
	if ($arr['alias']=='hist')  return 0; // redundancy !
	if (strpos($arr['alias'],'.hist')>0) return 0;//possible redundancy
	$bIncHist=false;
	//ev_dbg($arr,'SaveHistory');
	$histAlias='hist';
	if ($this->histAliases!=null)
		if (isset($this->histAliases[($arr['alias'])])){
			$histAlias=$this->histAliases[($arr['alias'])];
			$bIncHist=true;
			}
	if (!$bIncHist){
		if (!isset($this->includeHistory))
			$this->includeHistory=array_flip(explode(',',$this->getEnv('historyIncludeAliases')));
		if (isset($this->includeHistory[($arr['alias'])]))
			$bIncHist=true;//included //return 0; // excluded
		}
	if (!$bIncHist)	return 0;
	//ev_dbg(array('histAliases'=>$this->histAliases),'SaveHistory-do');
	if ($arr['op']!='I'){
	/*	$tinfo=$this->getDbCfg($arr['alias']);
		$primfield=$tinfo['primary_field'];
		if ($arr['op']=='D') // when deleted - we have prim in array
			$primvalue=$arr[$primfield];
		else{
			$tmp=$this->dbAliases[$arr['alias']]['table'];
			$cond=$arr['cond'];
			$qry="SELECT * FROM $tmp WHERE $cond";
    		$result = $this->Execute($qry);
    		if ($this->num_rows($result)>0){
    			$rec=$this->fetch($result);
    			$primvalue=$rec[$primfield];
    			}
			}*/
		$cond=$arr['cond'];	
		//$path=$arr['alias'].'/'.$cond=$arr['cond'];
		}
	else{
		$insId=$arr['insId'];
		//$path=$arr['alias'].'/'.$insId;
		//$primvalue=$this->insert_id();
		//$primvalue=$insId;
		}
	//$tinfo=$this->getDbCfg($arr['alias']);
	//$primfield=$tinfo['primary_field'];				
		
	//$path=$this->ModName.'/'.$arr['alias'].'/'.$primvalue;
	$dbModule=str_replace('/','.',$this->p_StrKey);
	$dbMd=str_replace('md/','',$dbModule);
	$dbMd=str_replace('/db','',$dbMd);
	$dbVersion=$this->getDbVersion();
	$dbAlias=$arr['alias'];
	$path=$dbModule.'/'.$dbAlias.'/'.$dbVersion;
	$arru=array();
	//$arru['hist_path']=$path;
	$arru['hist_oper']=$arr['op'];
	$arru['hist_md']=$dbMd;
	$arru['hist_alias']=$dbAlias;
	$arru['hist_dbversion']=$dbVersion;
	$dateTime=date('Y-m-d H:i:s');
	$arru['hist_serialized']=evSerialize(array(
		'createdBy'=>'core.core.db::SaveHistory',
		'createdDateTime'=>$dateTime,
		'type'=>'historyData',
		'version'=>'1.1',
		'content'=>array(
			'arr'=>$arr['arr'],
			'ret'=>$arr['ret'],
			'ip'=>$_SERVER['REMOTE_ADDR'],
			'op'=>$arr['op'],
			'cond'=>$cond,
			'insId'=>$insId,
			'dateTime'=>$dateTime,
			'dbVersion'=>$dbVersion,
			'dbModule'=>$dbModule,
			'dbAlias'=>$dbAlias,
			'qry'=>$hQry,
			),
		));
	$arru['hist_ret']=$arr['ret'];
	$arru['hist_user_ip']=$_SERVER['REMOTE_ADDR'];
	$arru['hist_datetime']=$dateTime;//'now()';
	foreach($arr['hist'] as $k =>$v){//tODO: validation - this  can be error causing when wrong data received
		$arru[('hist_'.$k)]=$v;
		}
	// parent id handler
	if (isset($arr['hist']['parentid'])){
		if ($arr['hist']['parentid']==0)
		if (!empty($insId)) 
			$arru[('hist_parentid')]=$insId;
		}
			
	$ret=$this->Insert(array('alias'=>$histAlias,'arr'=>$arru,'nohist'=>'Y'));
	return $ret;
	}
	
function getHistXML($arg){
	$fromDateT=$arg['fromDateTime'];
	$toDateT=$arg['toDateTime'];
	$cond='hist_datetime>"'.$fromDateT.'" and hist_datetime<="'.$toDateT.'"';
	$ret=$this->select(array(
		'alias'=>'hist',
		'cond'=>$cond,
		'orderby'=>'hist_datetime asc',
		));
	$dateTime=date('Y-m-d H:i:s');	
	//$this->incLib('array2xml');
	$arg['histInfo']['itemCount']=0;
	$xmlRet='<dbHist version="1.0" createdDateTime="'.$dateTime.'">'."\r\n";	
	if ($ret){
		$xmlRet.='<history from="'.$fromDateT.'" to="'.$toDateT.'">'."\r\n";			
		while ($rec=$this->fetch($ret)){
			$bAdd=true;
			if (isset($arg['filter'])){
				if (isset($arg['filter']['alias'])){
					if ($arg['filter']['alias'] != $rec['hist_alias'])
						$bAdd=false;
					}
				}
			if ($bAdd) {
				if (!isset($arg['histInfo']['fromDateTime'])) $arg['histInfo']['fromDateTime'] = $rec['hist_datetime'];
				$arg['histInfo']['toDateTime']=$rec['hist_datetime'];
				$arg['histInfo']['itemCount']++;
				//ev_dbg()
				//$xmlRec=evArray2XML(evUnserialize($rec['hist_serialized']),'operations');
				$xmlRec='<hItem histId="'.$rec['hist_id'].'" histOper="'.$rec['hist_oper'].'" histAlias="'.$rec['hist_alias'].'" histDateTime="'.$rec['hist_datetime'].'" histSerialized="'.$rec['hist_serialized'].'"/>'."\r\n";
				$xmlRet.=$xmlRec;	
				}
			}
		$xmlRet.='</history>'."\r\n";	
		} else {
			$errorMessage=$this->error();
			$xmlRet.='<errorMessage value="'.$errorMessage.'">'."\r\n";	
		}	
	
	
			
	$xmlRet.='</dbHist>'."\r\n";	
	return $xmlRet;
	}	
	
function _convDF($str){
	$str=str_replace(' ','_',trim($str));
	$str=str_replace(':','-',$str);
	return $str;
}	
function _convFD($str){
	$fa=explode('_',trim($str));
	//$str=str_replace(' ','_',$str);
	//$str=str_replace(':','-',$str);
	$str=$fa[0].' '.str_replace('-',':',$fa[1]);
	return $str;
	}	

function saveHistXMLToFile($arg){
	$saveHistXMLLastDateTime=$this->getSysEnv('saveHistXMLLastDateTime');
	if (empty($saveHistXMLLastDateTime)) {$this->intError('saveHistError: No saveHistXMLLastDateTime value in sys.env'); return false;}
	if (!isset($arg['toDateTime'])){
		$arg['toDateTime']=date('Y-m-d H:i:s',time()-3);
		}
	$arg['fromDateTime']=$saveHistXMLLastDateTime;
	if (($arg['fromDateTime']>=$arg['toDateTime']) || ($arg['toDateTime']>=date('Y-m-d H:i:s'))) return false;
	$ar=array();
	$arg['histInfo']=&$ar;
	$histXML=$this->callMD('sys/db','getHistXML',$arg);
	//ev_dbg($ar,'histInfo');
	//$file2=evLogDir.'/hist_'.$arg['histInfo']['fromDateTime'].'_'.$arg['histInfo']['toDateTime'].'.xml';
	if ($ar['itemCount']>0){
		$file2=evLogDir.'/hist_save/hist_'.$this->_convDF($arg['fromDateTime']).'_'.$this->_convDF($arg['toDateTime']).'.xml';
		$fp = fopen($file2, "w+");
		fwrite($fp, $histXML);
		fclose($fp);
		$this->setSysEnv('saveHistXMLLastDateTime',$arg['toDateTime']);
		}
	return true;	
	}
	
function procHistItem(&$it){
	$data=trim($it['histSerialized']);
	//ev_dbg('|'.$data.'|','$data');
	$it['histUnserialized']=evUnserialize($data);
	//$un=evUnserialize($data);
	$hCnt=$it['histUnserialized']['content'];
	$error='';
	$ret=true;
	switch ($hCnt['op']){
		case 'I'://insert
			$tinfo=$this->getDbCfg($hCnt['dbAlias']);
			//ev_dbg($tinfo,'$tinfo');
			$primfield=$tinfo['primKey'];
			$hCnt['arr'][($primfield)]=$hCnt['insId'];
			//ev_dbg($hCnt,'$hCnt');
			$res=$this->Insert(array(
				'alias'=>$hCnt['dbAlias'],
				'arr'=>$hCnt['arr'],
				'noHist'=>'Y',
				));
			
			if (!$res)
				$error=$this->error();	
			//	echo($this->error());
			//ev_dbg(array('res'=>$res,'error'=>$error),'res');	
			break;
		case 'U': // update
			//ev_dbg($hCnt,'$hCnt');
			//$error='Not implemented';
			$res=$this->Update(array(
				'alias'=>$hCnt['dbAlias'],
				'arr'=>$hCnt['arr'],
				'cond'=>$hCnt['cond'],
				'noHist'=>'Y',
				));
			if (!$res)
				$error=$this->error();				
			break;	
		case 'D': // delete
			//ev_dbg($hCnt,'$hCnt');
			//$error='Not implemented';
			$res=$this->Delete(array(
				'alias'=>$hCnt['dbAlias'],
				'cond'=>$hCnt['cond'],
				'noHist'=>'Y',
				));
			if (!$res)
				$error=$this->error();				
			break;	
		default:
			$error='Undefined operation: "'.$hCnt['op'].'"';			
		}
	if (!empty($error)){
		//ev_dbg('procHistError');
		$this->intError('procHistError: histId='.$it['histId'].' errorMessage='.$error);
		$ret=false;
		}	
	//ev_dbg($it);
	return $ret;
	}	
	
function loadHistXMLFromFile($arg){
	$loadHistXMLLastDateTime=$this->getSysEnv('loadHistXMLLastDateTime');
	if (empty($loadHistXMLLastDateTime)) {$this->intError('loadHistError: No loadHistXMLLastDateTime value in sys.env'); return false;}
	$pregExpr='/hist_'.$this->_convDF($loadHistXMLLastDateTime).'_*/';
	$files=evPregLs(evLogDir.'/hist_load',$pregExpr);
	if (count($files)<1) return true; // just no file
	if (count($files)>1) {$this->intError('loadHistError: To many files : '.evLogDir.$pregExpr);return false;}
	//ev_dbg($files,'files');
	//return;
	$file2=$files[0];
	//$file2=evLogDir.'/hist_'.$this->_convDF($arg['fromDateTime']).'_'.$this->_convDF($arg['toDateTime']).'.xml';
	$histXML = file_get_contents ($file2);
	$xmlArr=$this->xmlToArr($histXML,'root');
	//ev_dbg($xmlArr,'$xmlArr');	
	//foreach ($xmlArr['history']['hItem'] as $k => $v){
	//	$xmlArr['history']['hItem'][$k]['histUnserialized']=evUnserialize($v['histSerialized']);
	//	}

	$ret=true;
	if ($arg['process']==evT){
		if (isset($xmlArr['history']['hItem']['histSerialized'])){
			$a=$xmlArr['history']['hItem'];//['histSerialized'];
			//ev_dbg('|'.$a.'|','$a');
			if (!$this->procHistItem($a)) $ret=false;
			}
		else
		foreach ($xmlArr['history']['hItem'] as $k => $v){
			if (!$this->procHistItem($v)) {$ret=false;}
			}
		}
		

	if ($ret){ // update loadHistXMLLastDateTime
		$fa=explode('_',basename($file2));
		//ev_dbg($fa,'$fa');	
		$nf=$fa[3].'_'.str_replace('.xml','',$fa[4]);	
		$this->setSysEnv('loadHistXMLLastDateTime',$this->_convFD($nf));
		// move the file to done folder
		$fbName=basename($file2);
		rename($file2,evLogDir.'/hist_done/'.$fbName);
		}	
		
	//ev_dbg($xmlArr,'$xmlArr');	
	return $ret;
	}	

/**
 * Load DB info into env (obsolete)
 */	
function loadDBInfo($arg=''){
	$table='';
	if (is_array($arg)){
		$table=$arg['table'];
	}
	if ($this->DBH==null) $this->Connect();
	$dbName=$this->DBH->dbName;
	$ref=$this->getEnv('dbInfo/'.$dbName);
	if ($ref==null){
		$qry='SHOW TABLES FROM '.$dbName;
		$res=$this->Execute($qry,false);
		while ($row = $this->Fetch($res)){
		 	$this->setEnv('dbInfo/'.$dbName.'/tables/'.$row[0].'/name',$row[0]);
			}
		}
	}

/**
 * Load table structure info from db
 * @ev:arg table - table name
 */	
function loadTblInfo($arg=''){
	$table=$arg;
	if (is_array($arg)){
		$table=$arg['table'];
		}
	$ea=explode(' ',$table);
	$table = $ea[0];	
	if ($this->DBH==null) $this->Connect();
	$dbName=$this->DBH->dbName;
	if (!evEmpty($table)){
		$tbi=&$this->getEnv('dbInfo/'.$dbName.'/tables/'.$table);
		if (count($tbi['fields'])<1){
			$qry='SHOW FULL FIELDS FROM '.$table;
			$res2=$this->Execute($qry,false);
			while ($row2 = $this->Fetch($res2)){
				$tbi['fields'][($row2['Field'])]['name']=$row2['Field'];
				$tbi['fields'][($row2['Field'])]['type']=$row2['Type'];
				$tbi['fields'][($row2['Field'])]['collation']=$row2['Collation'];
				$tbi['fields'][($row2['Field'])]['null']=$row2['Null'];
				$tbi['fields'][($row2['Field'])]['key']=$row2['Key'];
				$tbi['fields'][($row2['Field'])]['default']=$row2['Default'];
				$tbi['fields'][($row2['Field'])]['extra']=$row2['Extra'];
				$tbi['fields'][($row2['Field'])]['privileges']=$row2['Privileges'];
				$tbi['fields'][($row2['Field'])]['comment']=$row2['Comment'];
				}
			}// count tbifields
		}
	return $tbi;			
	}	
	
/**
 * Check if table or alias exists
 * @ev:arg debug - debug mode
 * @ev:arg table - table name
 * @ev:arg alias - table alias
 */
function tableExists($alias){
	if (is_array($alias)){
		$debug=$alias['debug'];
		$table=$alias['table'];
		$alias=$alias['alias'];
		}
	$tablename=(evEmpty($alias))?$table:$this->dbAliases[$alias]['table'];
	$this->LoadDBInfo();
	$dbName=$this->DBH->dbName;
	$tablename=strtolower($tablename);
	$tname=$this->getEnv('dbInfo/'.$dbName.'/tables/'.$tablename.'/name');
	return (!evEmpty($tname));
	}

/**
 * Check if table exists (obsolete)
 */
function table_exists($alias){return $this->tableExists($alias);}

/**
 * Check if field exists
 * @ev:arg table - table name
 * @ev:arg alias - table alias
 * @ev:arg fieldname - field name
 */
function fieldExists($alias,$fieldname=''){
	$ret=false;
	$tablename='';
	if (is_array($alias)){
		$tablename=$alias['table'];
		$fieldname=$alias['fieldname'];
		$alias=$alias['alias'];
		}
	$this->LoadDBInfo();
	if (evEmpty($alias)) $alias=$this->getAliasFromTableName($tablename);
	if ($this->tableExists($alias)){
		if (evEmpty($tablename)) $tablename=$this->dbAliases[$alias]['table'];
		$tbi=$this->LoadTBlInfo($tablename);
		$dbName=$this->DBH->dbName;
		$ret=(isset($tbi['fields'][$fieldname]));
		}
	return $ret;	
	}

/**
 * Retrieve table name for alias
 */
function getTableNameFromAlias($alias){
	if (strpos($alias,' ')>0){
		$expl=explode(' ',$alias);
		$aliasp=$expl[0];
		return $this->dbAliases[$aliasp]['table'].' '.$expl[1];		
	}
	else
		return $this->dbAliases[$alias]['table'];
	}

/**
 * Retrieve alias for table name
 */	
function getAliasFromTableName($tname){
	if (is_array($tname))
		$tname=$tname['table'];
	$ret='';
	$ta=$this->dbAliases;
	foreach($ta as $k => $v){
		if ($v['table']==$tname){
			
			$ret=$k;
			//$this->ingGetAliasFromAliasInfo($v,'No alias found for :'.)
			break;
			}
		}
	return $ret;
	}


/**
 * Get error message after sql failure
 */
function error(){return $this->_lastErrorMessage;//$this->DBH->Error();
}

/**
 * Get error number after sql failure
 */
function errno(){return $this->DBH->errno();}

/**
 * Get inserted record id
 */
function insert_id($link=''){
	if ($this->stored_insert_id>0) return $this->stored_insert_id;
	else return $this->DBH->insert_id($link);
	}

/**
 * Get number of rows for last query
 */
function num_rows($arg){return (is_array($arg))?$this->DBH->num_rows($arg['res']):$this->DBH->num_rows($arg);}

/**
 * Get number of affected rows for last query
 */
function affected_rows($arg){return (is_array($arg))?$this->DBH->affected_rows($arg['res']):$this->DBH->affected_rows($arg);}

/**
 * Get number of all rows (obsolete)
 */
function num_all_rows($arg){return $this->NumAllRows;}

/**
 * Execute given qry
 * @ev:arg qry - sql
 */
function Execute($args,$unbuf=false){
	if ($this->DBH==null) $this->Connect();
	if ($this->DBH==null) {$this->intErrorL('errNotConnected');return -1;}
	if (is_array($args))
		$qry=$args['qry'];
	else
		$qry=$args;
	//ev_dbg('qryExec:'.$qry);
	//cho "\n".'qryExec:'.$qry."\n";	
	if ($GLOBALS['ocDebugDb']){
		echo "\nQryDebug:".$qry."\n";
		}
	$result = $this->DBH->ExecuteSQL($qry,$this->ErrorPostFix,$unbuf);
	if (!$result){ // output message in case of error
		if ($GLOBALS['ocDebugDb'])
			echo "QryDebugError:".$this->DBH->Error()."\n";
		if (!isset($this->b_quiet)){
	   		$this->intError($this->DBH->Error());
	   		$this->intError($this->DBH->Error());
	   		$this->intError('qry:'.$qry);
			}
		}
	if (isset($this->b_quiet)) unset($this->b_quiet);	
	 
	$this->ErrorPostFix='';
    return $result;
	}

	
function intRunTSQLForDateTable($aliasInfo,$after,$before){
mLog(mlError,'after:'.$after.' before:'.$before);	
if (!$this->tableExists(array('table'=>$after))){
	$tsqla=$aliasInfo['tsql'];
	foreach($tsqla as $qry){
		$qry=str_replace($before,$after,$qry);
		$this->Execute(array('qry'=>$qry));
		}
	}
}	

function intGetTableNameFromAliasInfo($aliasInfo,$errorNoAlias){

		//$tmp=$this->intGetTableNameFromAliasInfo($this->dbAliases[$alias])	
		$tmp=$aliasInfo['table'];
		if (evEmpty($tmp)){
			$this->intError($errorNoAlias);//,'Brak aliasu  lub nazwy tabeli "table" '.$alias.' ('.get_class($this).'->Select('.$aliases.','.$cond.','.$orderby.','.$rest.')',1);
			/*$aa=debug_backtrace();
			foreach($aa as $k=>$v){
				echo 'file:'.$v['file'].'<br/>';
				echo 'line:'.$v['line'].'<br/>';
			}*/
			die;
			}
		//Log(mlError,'tableNameSuff:'.print_r($aliasInfo,true));	
		if (!empty($aliasInfo['tableNameSuffix'])){
			$tmp.=date($aliasInfo['tableNameSuffix']);
			$this->intRunTSQLForDateTable($aliasInfo,$tmp,$aliasInfo['table']);
			}
	return $tmp;		
}	

/**
 * Convert aliases to corresponding table names
 */
function intConvertAliasesToTablespace($aliases,&$usedaliases,&$con){
    //Log(mlError,'convertaliases for '.implode('',$aliases));			
	$tablespace='';
	$alarray=explode(',',$aliases);
	foreach ($alarray as $key => $value)
		{
		$value=trim($value);
		$al1=explode(' ',$value);
		$alias=$al1[0];
		array_push($usedaliases,$alias);
		$al2=$al1[1];
		if (!evEmpty($al2))
			$al2=' '.$al2;
		$errorNoAlias='Brak aliasu  lub nazwy tabeli "table" '.$alias.' ('.get_class($this).'->Select('.$aliases.','.$cond.','.$orderby.','.$rest.')';	
		$tmp=$this->intGetTableNameFromAliasInfo($this->dbAliases[$alias],$errorNoAlias);	
	/*	$tmp=$this->dbAliases[$alias]['table'];
		if (evEmpty($tmp)){
			$this->intError('Brak aliasu  lub nazwy tabeli "table" '.$alias.' ('.get_class($this).'->Select('.$aliases.','.$cond.','.$orderby.','.$rest.')',1);
			/*$aa=debug_backtrace();
			foreach($aa as $k=>$v){
				echo 'file:'.$v['file'].'<br/>';
				echo 'line:'.$v['line'].'<br/>';
			}*/
	/*		die;
			}
		mLog(mlError,'tableNameSuff:'.print_r($this->dbAliases[$alias],true));	
		if (!empty($this->dbAliases[$alias]['tableNameSuffix'])){
			$tmp.=date($this->dbAliases[$alias]['tableNameSuffix']);
			$this->intRunTSQLForDateTable($this->dbAliases[$alias],$tmp,$this->dbAliases[$alias]['table']);
			}*/
			
		$tablespace.=$con.$tmp.$al2;
		$con=', ';
		}
	return $tablespace;
	}			

/**
 * Localize field names adding language postfix
 */	
function localizeFieldNames($ualiases,&$fields,&$cond,&$orderby,&$rest){
	$lng=$this->getEnv('lng');
	foreach ($ualiases as $key => $alias){
		if (!evEmpty($this->dbAliases[$alias]['tinfo']['localizedFields'])){
			$tmparr=explode(',',$this->dbAliases[$alias]['tinfo']['localizedFields']);
			foreach ($tmparr as $k => $fieldname){
				if ($this->fieldExists($alias,$fieldname.'_'.$lng)){
					$fields=str_replace($fieldname,$fieldname.'_'.$lng,$fields);
					$cond=str_replace($fieldname,$fieldname.'_'.$lng,$cond);
					$orderby=str_replace($fieldname,$fieldname.'_'.$lng,$orderby);
					$rest=str_replace($fieldname,$fieldname.'_'.$lng,$rest);
  					}
  				}
  			}
		}	
	}

	
function procJoinTable($joinTable,&$tablespace,&$con){
	// join table
	if (count($joinTable)>1){
		$cj='';
		if ((!empty($joinTable['leftKey'])) && (!empty($joinTable['rightKey'])))
			$cj=$joinTable['leftKey'].'='.$joinTable['rightKey'];

		//composite join key
		$cjc=(empty($cj))?'':' AND ';	
		if (!empty($joinTable['compKey']))
			$cj.=$cjc.$joinTable['compKey'];
		if ((isset($joinTable['alias'])) && (!isset($joinTable['table']))){
			$joinTable['table']=$this->getTableNameFromAlias($joinTable['alias']);
		}	
		
		if (!isset($joinTable['table'])){
			$tablespace.=$con.$joinTable['leftTable'].' LEFT JOIN '.$joinTable['rightTable'].' ON ('.$cj.')';
		} else {
			//$tablespace.=$con.$joinTable['table'];
			//if (evEmpty($cond)) $cond=$cj;
			//else $cond.=' AND '.$cj;
			$tablespace.=' LEFT JOIN '.$joinTable['table'].' ON ('.$cj.')';
			}
			
				
		$con=', ';
		}


}	
	
/**
 * Perform "select" query
 * @ev:arg alias/aliases - alias name/names
 * @ev:arg table - table name
 * @ev:arg fields - comma delimited field list (default *)
 * @ev:arg cond - select condition (where clause)
 * @ev:arg orderby - select order (order by clause)
 * @ev:arg groupby - select grouping (groub by clause)
 * @ev:arg rest - ending part (subselect etc)
 * @ev:arg pos - select position (paging)
 * @ev:arg limit select limit (paging)
 * @ev:arg joinTable - standard left join (leftTable,rightTable, leftKey, rightKey)
 * @ev:arg forlist - special behaviour for list control
 */	
function Select($args){
	_ev_sprof();
	$fields='*';
	$groupby='';
	$svErrorMessage='';	
	$arr=$args;
	$joinTable=$arr['joinTable'];
	$aliases=$arr['aliases'];
	$table=$arr['table'];
	if (evEmpty($aliases)) $aliases=$arr['alias'];
	$cond=$arr['cond'];
	if (evEmpty($arr['cond']))	$cond='';
	$orderby=$arr['orderby'];
	if (evEmpty($arr['orderby']))	$orderby='';
	if (!evEmpty($arr['groupby']))	$groupby=$arr['groupby'];
	$rest=$arr['rest'];	
	if (evEmpty($arr['rest']))	$rest='';
	$store=$arr['store'];	
	if (evEmpty($arr['store']))	$store=false;
	$forlist=$arr['forlist'];	
	if (evEmpty($arr['forlist']))	$forlist=false;
	if (!evEmpty($arr['fields']))	$fields=$arr['fields'];
	$unbuf=false;
	if (isset($arr['unbuffered'])) $unbuf=$arr['unbuffered'];
	$pos=$args['pos'];
	$maxrows=$args['maxrows'];
	if (!evEmpty($arr['limit'])){
		$tmpar=explode(',',$arr['limit']);
		$pos=intval($tmpar[0]);
		$maxrows=intval($tmpar[1]);
		}
	$this->ErrorPostFix=get_class($this).'->Select('.$aliases.','.$cond.','.$orderby.','.$rest.')';
	if (!evEmpty($cond)) $cond='WHERE '.$cond;
	if (!evEmpty($orderby)) $orderby='ORDER BY '.$orderby;
	if (!evEmpty($groupby)) $groupby='GROUP BY '.$groupby;

	$con='';
	$tablespace='';
	$usedaliases=array();

	if (!evEmpty($aliases)){
		$tablespace=$this->intConvertAliasesToTablespace($aliases,$usedaliases,$con);
		}
		
	if (!evEmpty($table)){
		$tablespace.=$con.$table;
		$con=', ';
		}
	//Log(mlAction,'joinTableCount:'.count($joinTable));
	//if ($GLOBALS['dbdb'])
	//ev_dbg(array('jpoin',$joinTable));	
	if (count($joinTable))
	if ((isset($joinTable['table'])) || (isset($joinTable['alias']))){	
		//only one join
		//if ($GLOBALS['dbdb'])
		//ev_dbg('jpin2');
		$this->procJoinTable($joinTable,$tablespace,$con);	
		}
	else {
		
			foreach($joinTable as $k=>$table){
				$this->procJoinTable($table,$tablespace,$con);
				}
		
	}	
	
	if (evEmpty($tablespace))
		$this->intError('Empty tablespace  '.$aliases.' ('.get_class($this).'->Select('.$aliases.$table.','.$cond.','.$orderby.','.$rest.')',1);
		
	//if (is_array($this->LocalizedFields))
	if ($this->LocalizedFields)
		$this->LocalizeFieldNames($usedaliases,$fields,$cond,$orderby,$rest);
		
	if (evEmpty($tablespace))
		evError('No table space defined ! (evcore_db::cmList)');
			
	$qry = "select $fields from $tablespace $cond $groupby $orderby";
	if ($forlist==evT){ // special behavoiur for list control
		$this->NumAllRows=0;
		if ($maxrows>0){
			$qryc="select count(*) from $tablespace $cond $groupby";
			$res=$this->Execute($qryc,$unbuf);
			//pg compatibility
			$tmarr=$this->DBH->fetch_array($res);
			$this->NumAllRows=$tmarr[0];				
			}
	}
	$qpos='';
	// $this->SqlQuery = $qry;
	if (!$pos) 
		$pos = 0;
		// $this->PrevMaxRows=$this->MaxRows;
		if ($maxrows>0){
			// $qpos = " limit ".$this->Pos.", ".$this->MaxRows;
			switch ($this->dbType){
				case 'pg':
					$qpos = " limit ".$maxrows.' offset '.$pos;
					break;
				default:
					$qpos = " limit ".$pos.", ".$maxrows;
					break;
				}
			} 
		
	if (!evEmpty($qpos)) 
		$qry.=' '.$qpos;
	//$GLOBALS['dbgLastQuery']=$qry;
	//if ($arr['log']==evT)
	//Log(mlError,"selectQry:".$qry);
	//cho 'qry:'.$qry;	
	//if (isset($args['debug'])) 
	//	$this->evDbg($qry);
	//ev_dbg($qry);
	//v_dbg($args);
	//if ($GLOBALS['qdeb'])
	//Log(mlAction,$qry);
	$ret=$this->Execute($qry,$unbuf);
	if (!$ret) {//preserve errorMessage
		$svErrorMessage=$this->DBH->Error();
		mLog(mlError,'dbSelect Error:'.$svErrorMessage."\n".print_r(array(
				'qry'=>$qry,
				'unbuf'=>$unbuf,
				),true));		
		}
			
	if (!empty($svErrorMessage)) $this->_lastErrorMessage=$svErrorMessage;	
			
	_ev_eprof();
	$this->SelectRets[$ret]['usedAliases']=$usedaliases;
	return $ret;
	}

/**
 * Fetch single record as array
 */  
function fetch($result){
	$rarr=null;
	if (is_array($result))
		$result=$result['res'];
	if ($result>0) 
		$rarr = $this->DBH->fetch_array($result);
	if (count($rarr))
	if ($this->LocalizedFields){
		$rarr= $this->callMD('util/lng','convLngArray', array('arr'=>&$rarr,'lngCode'=>$this->getEnv('lng')) ) ;
		}
	$tarr=array();
	$t2=$rarr;
	// regular expression based formatting or callBack based formatting
	if (count($this->SelectRets[$result]['usedAliases']))
	foreach($this->SelectRets[$result]['usedAliases'] as $k=>$alias){
		if (count($this->dbAliases[$alias]['tinfo']['formatFunc'])){
			$tmp=$this->dbAliases[$alias]['tinfo']['formatFunc']['mt'];
			$md=$this->dbAliases[$alias]['tinfo']['formatFunc']['md'];
			if (!evEmpty($md))	
				$this->callMD($md,$tmp,array('arr'=>&$rarr));
			else
				$this->$tmp(array('arr'=>&$rarr));
			}
		}
	return $rarr;
	}

/**
 * Fetch alias
 */	
function fetchLng($result) {return $this->Fetch($result);} // todo lng conversion	

function fetchF(&$res,&$obj){
	if ($this->_freeResult>0){
		$this->_freeResultCounter++;
		if ($this->_freeResultCounter>$this->_freeResult) {// need to reload query
			$obj->logMem('bf');
			mysql_free_result($res);
			$obj->logMem('af');
			$this->_freeResultArg['selArg']['limit']=$this->_freeResult.','.($this->_freeResultArg['numFetch']+5);
			$this->_freeResult+=$this->_freeResultArg['numFetch']-2;
			$res=$this->select($this->_freeResultArg['selArg']);
			mLog(4,'selectReload------------------------------------- limit='.$this->_freeResultArg['selArg']['limit']);
			}
		}
	$rec=$this->fetch($res);
	mLog(4,'fetchF uid='.$rec['uid']);
	return $rec;
	}

function setFreeResult($arg){
	if (is_array($arg)){
		$this->_freeResultArg=$arg;
		$this->_freeResult=$arg['numFetch'];
		$this->_freeResultCounter=0;
		}
	else if ($arg==0){
		$this->_freeResult=0;
		}	
	}

/**
 * Remove records from table
 */
function Delete($alias,$cond='0=1'){
	$tmp='';
	$svErrorMessage='';
	$nohist=false;
	$usedaliases=array();
	$hist=array();
	$con='';
	if (is_array($alias)){
		$tmparr=$alias;
		$alias=$tmparr['alias'];
		$cond=$tmparr['cond'];
		$nohist=((!evEmpty($tmparr['nohist'])) || (!evEmpty($tmparr['noHist'])));
		if (evEmpty($alias)){
			$tmp=$tmparr['table'];
			$alias=$this->GetAliasFromTableName($tmp);
			}				
		else {
			$tmp=$this->dbAliases[$alias]['table'];
			//$tmp=$this->intConvertAliasesToTablespace($alias,$usedaliases,$con);
			}
		if (isset($tmparr['hist']))	
			$hist=$tmparr['hist'];	
		}
	else {
		$tmp=$this->dbAliases[$alias]['table'];
		//$tmp=$this->intConvertAliasesToTablespace($alias,$usedaliases,$con);
		}
	
	$this->ErrorPostFix=get_class($this).'->Delete('.$alias.','.$cond.')';
	
	$arr=array(); // to do select here (look in deletetable funca)
	$ret=false;
	$qry="SELECT * FROM $tmp WHERE $cond";
    $result = $this->Execute($qry);
    if ($this->num_rows($result)>0){
    	$arr=$this->Fetch($result);
    	$this->DeletedRec=$arr;
    	if ($this->CheckWflowTriggers('beforedelete',$alias,$arr,array('cond'=>$cond)))
		if ($this->CheckTriggers('beforedelete',$alias,$arr,array('cond'=>$cond))){	
			$qry="DELETE FROM $tmp where $cond";
			$ret=$this->Execute($qry);
			if (!$ret) { //preserve errorMessage
				$svErrorMessage=$this->DBH->Error();
				mLog(mlError,'dbDelete Error:'.$svErrorMessage."\n".print_r(array(
					'qry'=>$qry,
					),true));				
				}
			if (!$nohist)
				if ($this->doHist)
					$this->SaveHistory(array('alias'=>$alias,'arr'=>$arr,'op'=>'D','cond'=>$cond,'ret'=>$ret,'res'=>$ret,'qry'=>$qry,'hist'=>$hist));	
			$this->CheckTriggers('afterdelete',$alias,$arr,array('cond'=>$cond,'res'=>$ret));
			if (!empty($svErrorMessage)) $this->_lastErrorMessage=$svErrorMessage;		
			}
    	}
	return $ret;
	}

/**
 * Update records in table (internal)
 */
function _Update($table,$whereclause,$arr,$opt='') {//,$fillprfs=false)
	//global $prFs;
	// limit problems in Execute(
	$this->MaxRows=0;
	$this->Pos=0;
	if (isset($opt['quiet'])) $this->b_quiet=true;
	foreach ($arr as $n=>$v){
		if (($v=='now()') || (strpos('_'.$v,'PASSWORD(')==1) || (strpos('_'.$v,'OLD_PASSWORD(')==1)){
			$vlist .= "$v";
			$set .= "$n = ".$v.", ";
			}
		else{
			if (is_array($v)){// set ?
				$vall='';
				foreach($v as $vk => $vv){
					$vall.=$vk.',';
					}
				//$v=ereg_replace(",$", "", $vall);
				$v=trim($vall,',');
				}
			else
				$v=addslashes($v);
			//if ($this->opc[nl2br])
			//	$v=nl2br($v);
			if (!isset($opt['nosl'][$n]))
				$set .= "$n = '".$v."', ";
			else
				$set .= "$n = ".$v.", ";
			}
		}
		
	// cut commas
	$set = trim($set," ,");//ereg_replace(", $", "", $set);
	// query SQL
	$qry = "UPDATE $table SET $set WHERE $whereclause";
	//if ($GLOBALS['mdbg1'])
	//Log(mlAction,'updateQry: '.$qry);
	//if($GLOBALS['dbgdb'])
	//if ($opt['debug']==evT)
    //ev_dbg(array('updateQry'=>$qry));
	//ev_dbg(debug_backtrace());
	$this->histQry=$qry;
	return $this->Execute($qry);	
	}

/**
 * Update records in table
 * @ev:arg alias
 * @ev:arg table
 * @ev:arg arr - field values array
 * @ev:arg cond - update condition
 */
function Update($alias,$cond='brak',$arr='') {//,$fillprfs=false)
	$tmp='';
	$svErrorMessage='';	
	$nohist=false;
	$usedaliases=array();
	$opt=array();
	$con='';
	$hist=array();
	if (is_array($alias)){
		$tmparr=$alias;
		$cond=$tmparr['cond'];
		$nohist=(!evEmpty($tmparr['nohist']));
		if (is_array($tmparr['arr']))
			$arr=$tmparr['arr'];
		if (isset($tmparr['opt']))
			$opt=$tmparr['opt'];	
		$alias=$tmparr['alias'];
		if (evEmpty($alias)){
			$tmp=$tmparr['table'];
			$alias=$this->GetAliasFromTableName($tmp);
			}
		else{
			$tmp=$this->intConvertAliasesToTablespace($alias,$usedaliases,$con);
			}
		if (isset($tmparr['hist']))	
			$hist=$tmparr['hist'];	
		}
	else{
		$tmp=$this->intConvertAliasesToTablespace($alias,$usedaliases,$con);
		}
	$this->ErrorPostFix=get_class($this).'->cmUpdate('.$alias.",$cond,$arr,$fillprfs)";
	
	if (evEmpty($tmp)) $this->intError('Nonexistent alias/table: '.$alias.'/'.$tmp,1);
	if ($cond=='') $cond='1=1';
	$ret=false;
	if ($this->CheckWflowTriggers('beforeupdate',$alias,$arr,array('cond'=>$cond,'opt'=>$opt)))
	if ($this->CheckTriggers('beforeupdate',$alias,$arr,array('cond'=>$cond,'opt'=>$opt)))
		{
		$ret=$this->_Update("$tmp",$cond,$arr,$opt);//,$fillprfs);
		if (!$ret){ //preserve errorMessage
			$svErrorMessage=$this->DBH->Error();
			mLog(mlError,'dbUpdate Error:'.$svErrorMessage."\n".print_r(array(
				'tables'=>$tmp,
				'cond'=>$cond,
				'arr'=>$arr,
				'opt'=>$opt,
				),true));
			}
		//ev_dbg('update');	
		if (!$nohist)
			if ($this->doHist)
				$this->SaveHistory(array('alias'=>$alias,'arr'=>$arr,'op'=>'U','ret'=>$ret,'res'=>$ret,'cond'=>$cond,'qry'=>$this->histQry,'hist'=>$hist));	
		$this->CheckTriggers('afterupdate',$alias,$arr,array('cond'=>$cond,'res'=>$ret,'opt'=>$opt));
		if (!empty($svErrorMessage)) $this->_lastErrorMessage=$svErrorMessage;
		}
	return $ret;
	}

/**
 * Insert record into table (internal)
 * 
 */	
function _Insert($table,$arr,$opt){//,$fillprfs=false)
	//Log(4,print_r($arr,true));
	$this->Arr=$arr;
	$nlist = "";
        $vlist = "";
	$tmparr=$this->Arr;
	if (isset($opt['quiet'])) $this->b_quiet=true;
	if ((isset($this->Arr)) && (count($this->Arr)>0))
       // while (list($n, $v) = each($this->Arr)){
       foreach($arr as $n => $v){
            if ($nlist!=''){
               $nlist.=',';
               $vlist.=',';
               }
            $nlist .= "$n";
			if (($v=='now()') || (strpos('_'.$v,'PASSWORD(')==1))
				$vlist .= "$v";
			else{
				if (is_array($v)){ // set ?
					$vall='';
					foreach($v as $vk => $vv){
						$vall.=$vk.',';
						}
					//$v=ereg_replace(",$", "", $vall);
					$v=trim($vall,',');
					}
				else{
					///cho 'n='.$n.'<br/>';
					$v=addslashes($v);
					}
				
				if ($this->opc['nl2br'])
					$v=nl2br($v);
				if (!isset($opt['nosl'][$n]))	
					$vlist .= "'".$v."'";
				else	
					$vlist .= "".$v."";
				}
            }
	$nlist = trim($nlist," ,");//ereg_replace(", $", "", $nlist);
    $vlist = trim($vlist," ,");//ereg_replace(", $", "", $vlist);
	// query SQL
 
	$qry = "INSERT INTO $table ($nlist) VALUES ($vlist)";
    //Log(4,'InsertQry:'.$qry);
	//ev_dbg('InsertQry:'.$qry);
	$this->histQry=$qry;	
	$res=$this->Execute($qry);
	
		
	if ($res){
		$this->InsertId=$this->insert_id();
		}
	else{
		//$this->InsertId=0;
		}
	return $res;
	}

/**
 * Insert record into table 
 * @ev:arg alias - table alias
 * @ev:arg arr - field values array
 */	
function Insert($alias, $arr=''){
	$tmp='';
	$svErrorMessage='';	
	$nohist=false;
	$opt=array();
	$hist=array();	
	if (is_array($alias)){
		$tmparr=$alias;
		$alias=$tmparr['alias'];
		$nohist=(!evEmpty($tmparr['nohist']));
		if (isset($tmparr['opt']))
			$opt=$tmparr['opt'];		
		$arr=$tmparr['arr'];
		if (evEmpty($alias)){
			$tmp=$tmparr['table'];
			$alias=$this->GetAliasFromTableName($tmp);
			}				
		else{
			//$tmp=$this->dbAliases["$alias"]['table'];
			$tmp=$this->intGetTableNameFromAliasInfo($this->dbAliases["$alias"],'NO table found for alias:'.$alias);
			}
		if (isset($tmparr['hist']))	
			$hist=$tmparr['hist'];	
		}
	else{
		//$tmp=$this->dbAliases["$alias"]['table'];
		$tmp=$this->intGetTableNameFromAliasInfo($this->dbAliases["$alias"],'NO table found for alias:'.$alias);
		}
	$this->ErrorPostFix=get_class($this).'->cmInsert('.$alias.',..)';
	if (evEmpty($tmp)){
		$this->intError('No alias found:'.$alias.'); //('.get_class($this).'->Insert('.$alias.','.$arr.')',1);
		exit;
		}
	$ret=false;
	if ($this->CheckWflowTriggers('beforeinsert',$alias,$arr,array('opt'=>$opt)))
	if ($this->CheckTriggers('beforeinsert',$alias,$arr,array('opt'=>$opt))){	
		$ret=$this->_Insert($tmp,$arr,$opt);
		if (!$ret) {//preserve errorMessage
			$svErrorMessage=$this->DBH->Error();
			mLog(mlError,'dbInsert Error:'.$svErrorMessage."\n".print_r(array(
				'tables'=>$tmp,
				'arr'=>$arr,
				'opt'=>$opt,
				),true));			
			}
		$insId=$this->InsertId;
		if (!$nohist){ 
			$this->stored_insert_id=0;
			//Log(mlError,'doHistOnSleelct:'.$this->doHist);
			if ($this->doHist){
				// preserve insert id
				$this->stored_insert_id=$this->DBH->insert_id();
				//nDebug('storedid:'.$this->stored_insert_id);
				//Log(mlError,'doHistOnSleelct2:'.$this->stored_insert_id);
				$this->SaveHistory(array('alias'=>$alias,'arr'=>$arr,'op'=>'I','insId'=>$insId,'ret'=>$ret,'res'=>$ret,'qry'=>$this->histQry,'hist'=>$hist));
				//nDebug('afterhistory');
				}
			}	
		//$this->CheckTriggers('afterinsert',$alias,$arr));
		$this->CheckTriggers('afterinsert',$alias,$arr,array('1'=>'1','insertId'=>$insId,'res'=>$ret,'opt'=>$opt));
		if (!empty($svErrorMessage)) $this->_lastErrorMessage=$svErrorMessage;
		}		
	return $ret;			
	}

/**
 * Returning preformated options array (combos, dictionaries etc)
 * @ev:arg alias - table alias
 * @ev:arg key - key field
 * @ev:arg value - value field
 * @ev:arg cond - condition (where clause)
 * @ev:arg dmpos - "doesn't mind" position
 * @ev:arg orderby - order
 */
function optionsArray($arg){
	$res=1;
	$arr=$arg;
	$alias=$arr['alias'];
	$key=$arr['key'];
	$value=$arr['value'];
	$cond=$arr['cond'];
	$orderby=$arr['orderby'];
	$doesntmindpos=$arr['dmpos'];
	$res=$this->Select($arr);
	$ret=array();
	if (!evEmpty($doesntmindpos))
		$ret['--']=$doesntmindpos;
	while ($arr=$this->FetchLng($res)){
		$tmp1=$arr["$key"];
		if (strpos($value,' ')>0){
			$ret["$tmp1"]='';
			$arrtmp=explode(' ',$value);
			$con='';
			foreach ($arrtmp as $k => $v){
				$ret["$tmp1"].=$con.$arr[$v];
				$con=' - ';
				}
			}
		else
			$ret["$tmp1"]=$arr["$value"];
		}
	return $ret;
	}
	
//sample conf:C:\eclipse.workspace\ls\apps.ls\md.sys.cfg.xml\db\ocproduct.inc.php
//array(
//	'aliasFrom'
//	'aliasTo'
//  'idFrom'
//	)	
function recSynchr($arg){
	if ($this->syncObj==null){
		$this->syncObj=&$this->Obj('core/db/sync');
		}
	$arg['db']=&$this;	
	return 	$this->syncObj->recSynchr($arg);
	}	
	
}//classe	