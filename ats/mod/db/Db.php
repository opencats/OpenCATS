<?php 

class Db extends Controller {
	private $catsController = null;
	private $_db = null;
	private $_dbh = null;
	
	public function __construct($catsController = null)
	{
		if ($catsController==null) { $catsController = E::controller('cats');}
		$this->catsController = $catsController;
		$this->_db = $catsController->getDb();
		evIncBe('mod/db/model/dbh/mysql');
		$this->_dbh = new dbh_mysql($this->_db);
	}
	
	function getAllFields(){
		$dts = E::dataItemTypes();
		$result = array();
		foreach ($dts as $k =>$v){
			$result[($v->name())]=$v->getAllFields();
		}
		return $result;
	}

	function getDbVersion(){
		if ($this->_db==null){
			$this->_db = DatabaseConnection::getInstance();
		}
		$result = null;
		//$queryResult = $this->_db->query($sql);
		$sql = sprintf(
				"SELECT
                settings.value AS value
                    FROM
                    settings
                    WHERE
                    settings.setting = %s",
				$this->_db->makeQueryString('dbVersion')
				);		
		$ret = $this->_db->getAssoc($sql);
		return (isset($ret['value']))?$ret['value']:null;
	}
	
	//todo
	private function connect(){
		
	}
	
	private function executeSQL($args){
		$dataItemType = $args['dataItemType'];
		$qry=$args['sql'];
		
		if ($this->_dbh==null) $this->connect();

		$result = array('dataItemType'=>$dataItemType);
		$res = $this->_dbh->executeSQL(array(
				'sql'=>$qry
			));
		if (!$res){
			$result['wasError']=true;
			$result['errorMessage']=$this->_dbh->errorMessage();
			//vd(array(
			//	'$result'=>$result,
			//));
			throw new Exception('DbError:'.$result['errorMessage']);
		} else {
			$result['wasError']=false;
			$result['result']=$res;
		}
		return $result;
	}
	
	private function runDbHandlerMethod($args){
		$dataItemType = $args['dataItemType'];
		$dbHandlerMethod = $args['dbHandlerMethod'];
		return call_user_func(array($dataItemType->dbHandler,$dbHandlerMethod),$args);				
		/*		array(
				'result'=>$result,
				'fieldName'=>$k,
				'fieldDef'=>$v,
		));*/
		
	}
	
	public function fetch($args){
		$dataItemType = $args['dataItemType'];
		$fields = $dataItemType->fields;
		
		$vc = $this->_dbh->fetchArray($args['result']);
		if (!$vc) return $vc;
		//vd($vc);

		//$va = $this->_db->getAllAssoc($sql);
		$fieldsBySQlColumn=array();
		foreach($fields as $k =>$fdef){
			$ck = (isset($fdef['dbColumn']))?$fdef['dbColumn']:$k;
			$cn = (isset($fdef['sqlColumn']))?$fdef['sqlColumn']:$ck;
			//vd();
			$fieldsBySQlColumn[$cn]=array(
					'fieldDef'=>$fdef,
					'fieldName'=>$k,
			);
		}
		/*vd(array(
				'$fields'=>$fields,
				'$fieldsBySQlColumn'=>$fieldsBySQlColumn
		));*/
		//$vc = $va[0];
		$result = array();
		foreach ($vc as $k =>$v){
			if (isset($fieldsBySQlColumn[$k])){
				$n= $fieldsBySQlColumn[$k]['fieldName'];
				$fdef = $fieldsBySQlColumn[$k]['fieldDef'];
				$vd = $fdef['fieldTypeEnum']->onDbToMd($v);
				$result[$n]=$vd;
			} else {
				//$result[$k]=$v;musi byc definicja
			}
		}
		
		// ok teraz pola custom
		//zakladamy ze pk zawsze = id
		if (isset($result['id'])){
			$id = $result['id'];
			$cv = $this->loadCustomFieldValues(array(
					'id'=>$id,
					'dataItemType'=>$dataItemType,
			));
			foreach($cv as $k=>$v){
				if (!isset($result[$k])) $result[$k] = $v;
			}
		}
		
		// dla custom dodatkowo upewniamy sie ze sa ustawione
		$cfields = $dataItemType->customFields;
		if (is_array($cfields)){
			foreach ($cfields as $k=>$v){
				if (!isset($result[$k])) $result[$k] = null;
			}
		}
		
		//calc fields
		if  ($dataItemType->dbHandler != null){
			foreach ($dataItemType->calcFields as $k =>$v){
				if (isset($v['handlerReadMethod'])){
					//vd(array('$diType->dbHandler'=>$diType->dbHandler));
					$vh = $this->runDbHandlerMethod(array(
							'dataItemType'=>$dataItemType,
							'dbHandlerMethod'=>$v['handlerReadMethod'],
							'result'=>$result,
							'fieldName'=>$k,
							'fieldDef'=>$v,
							
					));
					$result[$k]= $vh;
				}
			}
		}
		
		return $result;
		
	}
	
	private function prepareSQLCond($args){
		$result = '1=1';
		$dataItemType = $args['dataItemType'];
		if (isset($args['id'])){
			$id = $args['id'];
			$idFieldName = ($dataItemType->_idFieldSQL!=null)?$dataItemType->_idFieldSQL:'id';
			$idVal = $this->_db->makeQueryInteger($id);
			$result = $idFieldName.' = '.$idVal;
		} else if (isset($args['sqlCond'])){
			$result = $args['sqlCond'];
		}
		return $result;
	}
	
	public function select($args){
		$sqlCond = $this->prepareSQLCond($args);		
		$dataItemType = $args['dataItemType'];
		$dbTable = $dataItemType->dbTable;
		$fields = $dataItemType->fields;
		$sql = 'SELECT * from '.$dbTable;
		$sql.=' WHERE '.$sqlCond;
		return $this->executeSQL(array(
				'sql'=>$sql,
				'dataItemType'=>$dataItemType,
		));
	}
	
	private $siteId = null;
	
	private function getSiteId(){
		if ($this->siteId==null){
			$this->siteId = E::c('user')->getCurrentUserSiteId();
		}
		return $this->siteId;
	}
	
	
	private function divideFields($args){
		$dataItemType = $args['dataItemType'];
		$values = $args['fs'];

		$customFields = $dataItemType->customFields;
		$fields =  $dataItemType->fields;
		
		$fieldsToUpdateInDb=array();
		$customFieldsV=array();
		foreach($values as $k =>$v){
			if (isset($fields[$k])){// db field
				$fieldsToUpdateInDb[$k]=$v;
			} else if (isset($customFields[$k])){// only defined
				$customFieldsV[$k]=$v;
		
			}
		}
		
		return array(
				'dbFieldsV'=>$fieldsToUpdateInDb,
				'customFieldsV'=>$customFieldsV,
		);
		
	}
	
	private function getInsertId(){
		return $this->_dbh->insertId();
	}
	
	public function insert($args){
		$fs = $args['fs'];
		$dataItemType = $args['dataItemType'];
		if (!isset($fs['siteId'])){
			$fs['siteId']=$this->getSiteId();
		}
		$siteId = $fs['siteId'];
		
		
		$dres = $this->divideFields(array(
				'dataItemType'=>$dataItemType,
				'fs'=>$fs,
		));
		
		$fs = $dres['dbFieldsV'];
		$customFieldsV=$dres['customFieldsV'];
		
		$dbTable = $dataItemType->dbTable;
		$fields = $dataItemType->fields;
		$sql = 'INSERT INTO '.$dbTable.' (';
		$con='';
		$names = '';
		$values = '';
		$trace = array();
		foreach ($fs as $k =>$v){
			$fdef = $fields[$k];
			$cname = (isset($fdef['sqlColumn']))?$fdef['sqlColumn']:$fdef['dbColumn'];
			$names .= $con.$cname;
			if ($v==null && isset($fdef['defaultValueHandlerMethod'])){
				$vh = $this->runDbHandlerMethod(array(
						'dataItemType'=>$dataItemType,
						'dbHandlerMethod'=>$fdef['defaultValueHandlerMethod'],
						'result'=>$fs,
						'insertId'=>null,
						'fieldName'=>$k,
						'fieldDef'=>$fdef,
							
				));
				$v=$vh;
			}
			
			$vd = $fdef['fieldTypeEnum']->onMdToDb($v);
			$values.= $con.$this->_db->makeQueryString($vd);
			$con=',';
			$trace[$k]=array(
				'$fdef'=>$fdef,
				'$v'=>$v,
				'$vd'=>$vd,	
			);
		}
		$sql.=$names.') VALUES ('.$values.')';
		//$idFieldName = ($dataItemType->_idFieldSQL!=null)?$dataItemType->_idFieldSQL:'id';
		//$idVal = $this->_db->makeQueryInteger($id);
		//$sql.=' WHERE '.$idFieldName.' = '.$idVal;
		/*vd(array(
			'$dataItemType'=>$dataItemType,	
			'$sql'=>$sql,
			'$trace'=>$trace,	
		));*/
		//$wasError =  !(boolean) $this->_db->query($sql);
		$res = $this->executeSQL(array(
				'dataItemType'=>$dataItemType,
				'sql'=>$sql,
		));
		
		$insertId = $this->getInsertId();
		
		if (sizeof($customFieldsV)>0){
			foreach($customFieldsV as $k =>$v){
				$val=$v;
				if (isset($dataItemType->customFields[$k])){
					$fdef = $dataItemType->customFields[$k];
					if ($v==null && isset($fdef['defaultValueHandlerMethod'])){
						$vh = $this->runDbHandlerMethod(array(
								'dataItemType'=>$dataItemType,
								'dbHandlerMethod'=>$fdef['defaultValueHandlerMethod'],
								'result'=>$fs,
								'insertId'=>$insertId,
								'fieldName'=>$k,
								'fieldDef'=>$fdef,
									
						));
						$val=$vh;
					}
				}
				
				$this->setCustomFieldValue(array(
						'field'=>$k,
						'value'=>$val,
						'id'=>$insertId,
						'siteId'=>$siteId,
						'dataItemType'=>$dataItemType,
				));
			}
		}
		
		return array(
				'wasError'=>false,
				'insertId'=>$insertId,
		);
	}
	
	public function update($args){
		$id = $args['id'];
		$fs = $args['fs'];
		$dataItemType = $args['dataItemType'];
		$template = evArrDflt($args,'template',null);
		$siteId=$this->getSiteId();
		$isAfterInsert = ($template == 'add');
		
		//$customFields = $dataItemType->customFields;
		//$fields =  $dataItemType->fields;
		
		//vd($args);
		//die();
		$dres = $this->divideFields(array(
				'dataItemType'=>$dataItemType,
				'fs'=>$fs,
		));
		
		/*vd(array(
			'$args'=>$args,	
			'$dres'=>$dres,	
		));
		//ie();*/
		
		$customFieldsV=$dres['customFieldsV'];
		$fieldsToUpdateInDb=$dres['dbFieldsV'];
		


		/*vd(array(
		 '$fieldsToUpdateInDb'=>$fieldsToUpdateInDb
		 ));*/
		
		if (sizeof($fieldsToUpdateInDb)>0){
				//$db->update(array(
				//		'id'=>$id,
				//		'dataItemType'=>$dataItemType,
				//		'fs'=>$fieldsToUpdateInDb,
				//));
			
			
			
			$dbTable = $dataItemType->dbTable;
			$fields = $dataItemType->fields;
			
			$sql = 'UPDATE '.$dbTable.' SET ';
			$con='';
			foreach ($fieldsToUpdateInDb as $k =>$v){
				$fdef = $fields[$k];
				$cname = (isset($fdef['sqlColumn']))?$fdef['sqlColumn']:$fdef['dbColumn'];
				if ($isAfterInsert){
					if ($v==null && isset($fdef['defaultValueHandlerMethod'])){
						$vh = $this->runDbHandlerMethod(array(
								'dataItemType'=>$dataItemType,
								'dbHandlerMethod'=>$fdef['defaultValueHandlerMethod'],
								'result'=>$fs,
								'insertId'=>$id,
								'fieldName'=>$k,
								'fieldDef'=>$fdef,
									
						));
						$v=$vh;
					}
				}
				$vd = $fdef['fieldTypeEnum']->onMdToDb($v);
				$upd = $cname.' = '.$this->_db->makeQueryString($vd);//tu jeszcze w zal. od typu ?
				$sql.=$con.$upd;
				$con=',';
			}		
			$idFieldName = ($dataItemType->_idFieldSQL!=null)?$dataItemType->_idFieldSQL:'id';
			$idVal = $this->_db->makeQueryInteger($id);
			$sql.=' WHERE '.$idFieldName.' = '.$idVal;		
			$wasError =  !(boolean) $this->_db->query($sql);
			/*vd(array(
					'$sql'=>$sql,
					'$wasError'=>$wasError,
			));*/
			//ie();
		
		} 
		
		if (sizeof($customFieldsV)>0){
			foreach($customFieldsV as $k =>$v){
				$val=$v;
				if ($isAfterInsert && isset($dataItemType->customFields[$k])){
					$fdef = $dataItemType->customFields[$k];
					if ($v==null && isset($fdef['defaultValueHandlerMethod'])){
						$vh = $this->runDbHandlerMethod(array(
								'dataItemType'=>$dataItemType,
								'dbHandlerMethod'=>$fdef['defaultValueHandlerMethod'],
								'result'=>$fs,
								'insertId'=>$id,
								'fieldName'=>$k,
								'fieldDef'=>$fdef,
									
						));
						$val = $vh;
					}
				}
				$this->setCustomFieldValue(array(
						'field'=>$k,
						'value'=>$val,
						'id'=>$id,
						'siteId'=>$siteId,
						'dataItemType'=>$dataItemType,
				));
			}
		}
		
		
		return array(
			'wasError'=>$wasError,	
		);		
	} 
	
	public function loadAttTextFV($val,$siteId){
		$wcVal = '%'.str_replace('*', '%', $val) . '%';
		$sql = sprintf(
				"SELECT
                attachment.attachment_id AS attachmentId,
                attachment.text AS text,
                attachment.data_item_id AS dataItemId,
				attachment.data_item_type as dataItemType
	
            FROM
                attachment
            WHERE
                attachment.text like %s
            AND
                attachment.site_id = %s
	
			ORDER BY
				attachment.data_item_type,
				attachment.data_item_id ",
				$this->_db->makeQueryString($wcVal),
				$siteId
				);
	
		$va = $this->_db->getAllAssoc($sql);
		return $va;
	
	}
	
	public function loadCustomFieldValuesFV($val,$siteId){
		$wcVal = '%'.str_replace('*', '%', $val) . '%';
		$sql = sprintf(
				"SELECT
                extra_field.field_name AS fieldName,
                extra_field.value AS value,
                extra_field.extra_field_id AS extraFieldSettingsID,
                extra_field.data_item_id AS dataItemId,
				extra_field.data_item_type as dataItemType
				
            FROM
                extra_field
            WHERE
                extra_field.value like %s
            AND
                extra_field.site_id = %s
				
			ORDER BY 
				extra_field.data_item_type,
				extra_field.data_item_id ",
				$this->_db->makeQueryString($wcVal),
				$siteId		
				);
		
		$va = $this->_db->getAllAssoc($sql);
		return $va;
		
	}

	public function loadCustomFieldValues($args){
		$id = $args['id'];
		$siteId = evArrDflt($args,'siteId',$this->getSiteId());
		$dataItemType = $args['dataItemType'];

		$dbItemType = $dataItemType->dbValue;
		
		$sql = sprintf(
				"SELECT
                extra_field.field_name AS fieldName,
                extra_field.value AS value,
                extra_field.extra_field_id AS extraFieldSettingsID,
                extra_field.data_item_id AS dataItemID
            FROM
                extra_field
            WHERE
                extra_field.data_item_id = %s
            AND
                extra_field.data_item_type = %s
            AND
                extra_field.site_id = %s",
				$this->_db->makeQueryInteger($id),
				$dbItemType,
				$siteId
		
				);
		
		$va = $this->_db->getAllAssoc($sql);	

		
		$customFields = $dataItemType->customFields;
		$values=array();
		foreach($va as $i => $v){
			if (isset($customFields[($v['fieldName'])])){
				$cf = $customFields[($v['fieldName'])];
				$values[($v['fieldName'])] = $cf['fieldTypeEnum']->onDbToMd($v['value']);
			}
		}
		
		//vd(array(
		//		'$values'=>$values
		//));
		
		/*$result = $this->select(array(
				'id'=>$id,
				'dataItemType'=>$dataItemType,
		));
		$se = $this->fetch($result);
		foreach ($se as $k=>$v){
			$values[$k]=$v;
		}*/
		
		return $values;
	}
	
	function setCustomFieldValue($args){
		$field = $args['field'];
		$value = $args['value'];
		$id = $args['id'];
		$siteId = $args['siteId'];
		$dataItemType = $args['dataItemType'];
		
		$dbItemType = $dataItemType->dbValue;
		$customFields = $dataItemType->customFields;
		
		$value = $customFields[$field]['fieldTypeEnum']->onMdToDb($value); 
		
		/* Delete old entries. */
		$sql = sprintf(
				"DELETE FROM
                extra_field
            WHERE
                extra_field.field_name = %s
            AND
                extra_field.data_item_id = %s
            AND
                extra_field.site_id = %s
            AND
                extra_field.data_item_type = %s",
				$this->_db->makeQueryString($field),
				$this->_db->makeQueryInteger($id),
				$siteId,
				$dbItemType
				);
		$this->_db->query($sql);
		
		/* Don't set empty values at all. 0 is okay. */
		if (empty($value) && $value !== 0 && $value !== '0')
		{
			return false;
		}
		
		$sql = sprintf(
				"INSERT INTO extra_field (
                data_item_id,
                field_name,
                value,
                import_id,
                site_id,
                data_item_type
            )
            VALUES (
                %s,
                %s,
                %s,
                0,
                %s,
                %s
            )",
				$this->_db->makeQueryInteger($id),
				$this->_db->makeQueryString($field),
				$this->_db->makeQueryString($value),
				$siteId,
				$dbItemType
				);
		
		return (boolean) $this->_db->query($sql);
	}
	
	
	public function getUser($args){
		$id=$args['id'];
		return $this->catsController->getUser($id);
	}
	
}


?>