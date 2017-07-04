<?php 
/*vd(array(
	'$args'=>$args,	
		
));
return;*/

//$fs = $_POST['fs'];
$fs = $args['fs'];
$conc = evArrDflt($args['input']['request'],'conc','');
$smsMessage = $fs['message'];
$number = $fs['number'];
$number = evStrReplace($number,' ','');
$number = evStrReplace($number,'-','');
$number = '+48'.$number;
$error = false;
if (strlen($number)!=12){
	$error = true;
	E::uiO('message',array(
			'type'=>'error',
			'text'=>'Błędny format numeru telefonu. Wprowadź 9 cyfr ewentualnie poprzedzielanych "-" ',
	));
	
} else if (evStrEmpty($smsMessage)){
	$error = true;
	E::uiO('message',array(
			'type'=>'error',
			'text'=>'Brak tekstu wiadomości do wysłania.',
	));

} else {//strlen($number

	
	$res = $this->getModel()->sendMessageToNumber(array(
			'number'=>$number,
			'message'=>$smsMessage,
	));

	include('mstat.fragment.php');
	
	/*if ($res['hStatus']==200){
		E::uiO('message',array(
				'type'=>'info',
				'text'=>'Wysyłka wiadomości rozpoczęta...',
		));
		$mId = $res['mId'];
		$mStatus = $res['mStatus'];
		
	} else {
		E::uiO('message',array(
				'type'=>'error',
				'text'=>'Problem z wysyłką wiadomości:',
		));
		
	}*/
if (!$error){
	$mId = $res['mId'];
	$mStatus = $res['mStatus'];
}

}//strlen($number





//vd(array('$result'=>$result));

?>
<script type="text/javascript">
//<![CDATA[


function upgOnSubmit(txt){
	var d=document.getElementById('waitDiv');
	//d.style.visibility = 'visible';
	d.style.display='block';
    d=document.getElementById('buttonsGroup');
	d.style.display='none';
}
//]]>
</script>
<form name="sendSmsForm" id="sendSmsForm" action="<?php echo E::routeHref('sms/messageStatus')?>?id=<?php echo $mId;?>" enctype="multipart/form-data" method="post"
onSubmit="upgOnSubmit('Proszę czekać ...');"
>
<input type="hidden" id="conc" name="conc" value="<?php echo $conc;?>">
<input type="hidden" id="number" name="number" value="<?php echo $number;?>">
<input type="hidden" id="message" name="message" value="<?php echo evEscHtml($smsMessage);?>">
<div class="btn-group" id="buttonsGroup">
<?php if (!$error){?>
<input type="submit" class="btn btn-warning btn-md" name="submit" id="submit" value="Sprawdź">&nbsp;
<?php }//error
E::uiO('closeModalButton'); ?>
</div>
	<div id="waitDiv" style="display:none;">
	<p> <i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i> Proszę czekać ...</p>
	</div>
</form>