<?php 

class UserDataHandler extends DbHandler {
	
	public function calcUserFields($args){
		$fieldName = $args['fieldName'];
		$a = $args['result'];
		$result = null;
		switch ($fieldName){
			case 'namePositionContact':
				$result = $a['firstName'].' '.$a['lastName'].' - '.$a['position'].', tel.'.$a['phoneWork'].', '.$a['email'];
				break;
		}
		return $result;
	}
	
}