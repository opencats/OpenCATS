<?php 
//vd($res);
$mtype = 'info';
$error = false;
$sent = false;
if ($res['hStatus']==200){
	if ($res['mStatus']=='pending'){
		$message = 'Wiadomość oczekuje w kolejce na urządzenie nadawcze...';
		$mtype='warn';
	} else if ($res['mStatus']=='sent') {
		$message = 'Wiadomość została wysłana.';
		$mtype = 'success';
		$sent = true;
	} else {
		$message = 'Status wiadomości:'.$res['mStatus'];
	}	
} else {
	$message = "Błąd HTTP:"+$res['hStatus'];
	$error = true;
}

E::uiO('message',array(
		'type'=>$mtype,
		'text'=>$message,
));


if ($res['mStatus']=='sent'){
	$ca = explode('/',$conc);
	$ditname = $ca[0];
	$dit = E::enum('dataItemType',$ditname);
	$ditid = $ca[1];
	$joId = null;
	if ($dit->name()=='jobOrder'){// dod. pole do ustawienia
		$joId = $ditid;
	}
	E::c('activity')->logActivity(array(
			'fs'=>array(
					'notes'=>"Wysłano SMS na nr:".$smsnumber." o treści: ".$smsmessage.".",
					'type'=>E::enum('activityType','sms')->dbValue,
					'userId'=>E::c('user')->getCurrentUserId(),
					'siteId'=>E::c('user')->getCurrentUserSiteId(),
					'dtCreated'=>date('Y-m-d H:i:s'),
					'dataItemType'=>$dit->dbValue,
					'dataItemId'=>$ditid,
					'jobOrderId'=>$joId,
			),
	));
}