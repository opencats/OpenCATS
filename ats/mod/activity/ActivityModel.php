<?php 

class ActivityModel extends Model {

public function logActivity($args){
	$fs = $args['fs'];
	
	return $this->db()->insert(array(
			'dataItemType'=>E::enum('dataItemType','activity'),
			'fs'=>$fs,
	));
}
	
}

?>