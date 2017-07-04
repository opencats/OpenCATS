<?php 

class UserModel extends Model {
	
	public function getUserById($id){
		$db = $this->db();
		$res = $db->select(array(
				'dataItemType'=>E::enum('dataItemType','user'),
				'id'=>$id,
		));
		return $db->fetch($res);
	}
	
	public function updateProfile($args){
		$fs = $args['fs'];
		$id = $fs['id'];
		$db = $this->db();
		$res = $db->update(array(
				'dataItemType'=>E::enum('dataItemType','user'),
				'id'=>$id,
				'fs'=>$fs,
		));
	}
	
}