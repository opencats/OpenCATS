<?php 
namespace ats;

class CalendarModel extends \Model {

	public function getEventsByStartEnd($startDate,$endDate,$userId=null){
		$result = array();
		$db = $this->db();
		$userCond = ($userId!=null)?'and (public = 1 or entered_by = '.$userId.')':''; 
		$res = $db->select(array(
				'dataItemType'=>\E::enum('dataItemType','event'),
				'sqlCond'=>'date >= "'.$startDate.'" and date <= "'.$endDate.'"'.$userCond,
		));
		$i=0;
		while ($rec = $db->fetch($res)){
			$i++;
			if ($i>500) break;
			$result[]=$rec;
		}
		
		return $result;
	}
	
	public function getEventById($id){
		$db = $this->db();
		$res = $db->select(array(
				'dataItemType'=>\E::enum('dataItemType','event'),
				'id'=>$id,
		));
		return $db->fetch($res);
	}
	
}