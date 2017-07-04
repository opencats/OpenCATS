<?php 

class SmsModel extends Model {
	private $gw = null;
	private $deviceID = null;
	
	private function initGW(){
		if ($this->gw==null){
			evIncBe('lib/smsGateway');
			$ucData = E::c('user')->getCurrentUserData();
			$this->gw = new SmsGateway($ucData['meGWMail'], $ucData['meGWPassword']);
			$this->deviceID = $ucData['meGWDeviceId'];
		}
	}
	
	public function sendMessageToNumber($args){
		$number = $args['number'];
		$message = $args['message'];
		
		$this->initGW();
		$result = $this->gw->sendMessageToNumber($number, $message, $this->deviceID);
		/*vd(array(
			'$result'=>$result	
		));*/
		$hStatus = $result['status'];
		$mInfo = $result['response']['result']['success'][0];
		$mId = $mInfo['id'];
		$mStatus = $mInfo['status'];
		return array(
				'mStatus'=>$mStatus,
				'hStatus'=>$hStatus,
				'gwResult'=>$result,
				'mId'=>$mId,
			);
		
	}
	
	public function getMessageStatus($args){
		$mId = (is_array($args))?$args['mId']:$args;	
		
		$this->initGW();
		$result = $this->gw->getMessage($mId);
		/*vd(array(
			'$result'=>$result	
		));*/
		$hStatus = $result['status'];
		$mInfo = $result['response']['result'];
		$mId = $mInfo['id'];
		$mStatus = $mInfo['status'];
		
		return array(
				'mStatus'=>$mStatus,
				'hStatus'=>$hStatus,
				'gwResult'=>$result,
				'mId'=>$mId,
		);
	}
	
	
	
}

?>