<?php 

class CompanyDataHandler extends DbHandler {
	
	function readFullNameOrName($args){
		$cdata = $args['result'];
		$result=$cdata['name'];
		if (isset($cdata['fullName'])){
			if (!evStrEmpty($cdata['fullName'])){
				$result=$cdata['fullName'];
			}
		}
		return $result;
		//vd($args);
	}
	
	function readFullAddress($args){
		$a = $args['result'];
		if (isset($a['fullAddress']) && !evStrEmpty($a['fullAddress']) && evStrEmpty($a['city'])){
			return $a['fullAddress'];
		}
		return $a['zip'].' '.$a['city'].', '.$a['street'];
	}
	
}