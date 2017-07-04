<?php 

class EventDataHandler extends DbHandler {
	
	private $dates = array();
	private $users = array();
	private $endDates = array();
	
	private function getStartDateObj($args){
		$a = $args['result'];
		if (!isset($this->dates[($a['id'])])){
			$this->dates[($a['id'])] = DateTime::createFromFormat('Y-m-d H:i:s',$a['startDateTime']);
		}
		return $this->dates[($a['id'])];
	}
	
	private function getEndDateObj($args){
		$a = $args['result'];
		if (!isset($this->endDates[($a['id'])])){
			$this->endDates[($a['id'])] = DateTime::createFromFormat('Y-m-d H:i:s',$a['startDateTime']);
			$this->endDates[($a['id'])]->modify('+'.$a['length'].' minutes');
		}
		return $this->endDates[($a['id'])];
	}
	
	private function getUserData($args){
		$a = $args['result'];
		$uid = $a['userId'];
		if (!isset($this->users[$uid])){
			$this->users[$uid]=E::c('user')->getUserById($uid);
		}
		return $this->users[$uid];
	}
	
	function readEventTitle($args){
		$a = $args['result'];
		return $a['subject'];
	}
	
	function readEventEndDate($args){
		//$a = $args['result'];
		//$stop_date =  DateTime::createFromFormat('Y-m-d H:i:s',$a['startDate']);
		//$stop_date->modify('+'.$a['length'].' minutes');
		//return $stop_date->format("Y-m-d H:i:s");;
		$d = $this->getEndDateObj($args);
		return $d->format("Y-m-d H:i:s");		
	}

	function readEventEnd($args){
		//$a = $args['result'];
		////$stop_date =  DateTime::createFromFormat('Y-m-d H:i:s',$a['startDate']);//  new DateTime($endDateD);
		//cho 'date before day adding: ' . $stop_date->format('Y-m-d H:i:s');
		//$stop_date->modify('+'.$a['length'].' minutes');
		//return $stop_date->format("Y-m-d").'T'.$stop_date->format("H:i:s");
		$d = $this->getEndDateObj($args);
		return $d->format("Y-m-d").' '.$d->format("H:i:s");
	}
	
	function readEventEndTime($args){
		$d = $this->getEndDateObj($args);
		return $d->format("H:i");
	}
	
	function readEventStart($args){
		$d = $this->getStartDateObj($args);
		return $d->format("Y-m-d").' '.$d->format("H:i:s");
	}
	
	function readEventStartDate($args){
		$d = $this->getStartDateObj($args);
		return $d->format("Y-m-d");
	}
	
	function readEventStartTime($args){
		$d = $this->getStartDateObj($args);
		return $d->format("H:i");
	}
	
	function readEventYear($args){
		return $this->getStartDateObj($args)->format("Y");
	}

	function readEventMonth($args){
		return $this->getStartDateObj($args)->format("m");
	}
	function readEventDay($args){
		return $this->getStartDateObj($args)->format("d");
	}
	function readEventHour($args){
		return $this->getStartDateObj($args)->format("H");
	}
	function readEventMinute($args){
		return $this->getStartDateObj($args)->format("i");
	}
	function readEventDate($args){
		return $this->getStartDateObj($args)->format("Y-m-d");
	}
	function readEventTime($args){
		return $this->getStartDateObj($args)->format("H:i");
	}
	
	
	function readEventTypeDesc($args){
		$result = null;
		$a = $args['result'];
		$etv = $a['type'];
		if ($etv!=null){
			$result = E::enum('eventType')->enumByAttr('dbValue',$etv)->desc;
		}
		return $result;
		
	}
	

	
	function readUserFirstName($args){
		$uData = $this->getUserData($args);
		return $uData['firstName'];
	}
	
	function readUserLastName($args){
		$uData = $this->getUserData($args);
		return $uData['lastName'];
	}
}