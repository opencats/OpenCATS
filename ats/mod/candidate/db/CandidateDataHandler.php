<?php 

class CandidateDataHandler extends DbHandler {
	
	/*public function calcUserFields($args){
		$fieldName = $args['fieldName'];
		$a = $args['result'];
		$result = null;
		switch ($fieldName){
			case 'namePositionContact':
				$result = $a['firstName'].' '.$a['lastName'].' - '.$a['position'].', tel.'.$a['phoneWork'].', '.$a['email'];
				break;
		}
		return $result;
	}*/
	
	function readFullAddress($args){
		$a = $args['result'];
		if (isset($a['fullAddress']) && !evStrEmpty($a['fullAddress']) && evStrEmpty($a['city'])){
			return $a['fullAddress'];
		}
		return $a['zip'].' '.$a['city'].', '.$a['street'];
	}
	
	function dvNextInternalNumber($args){
		$insertId=$args['insertId'];//zawsze ustawione tylko dla cusomfiedls
		/*$db = $this->db();
		$res = $db->select(array(				
			'dataItemType'=>E::dataItemType('customField'),
			'sqlCond'=>'field_name = "internalNumber"',
			'sqlWhat'=>'max(extra_field_id)',
		));
		$ar = $db->fetch($res);
		evdd(array('$args'=>$args,
				'ar'=>$ar));*/		
		return $insertId;
	}
	
}