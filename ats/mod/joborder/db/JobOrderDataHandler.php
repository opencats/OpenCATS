<?php 

class JobOrderDataHandler extends DbHandler {
	
	private $refContact = null;
	
	function readContactWorkPhone($args){
		$cdata = $args['result'];
		///$result = null;
		if ($this->refContact==null){
			if (isset($cdata['contactId']) && $cdata['contactId']!=null){
				$res = $this->db()->select(array(
					'dataItemType'=>E::enum('dataItemType','contact'),
					'id'=>$cdata['contactId'],	
				));
				$this->refContact = $this->db()->fetch($res);
			}
		}
		return ($this->refContact==null)?null:$this->refContact['phoneWork'];
	}
	
}

?>