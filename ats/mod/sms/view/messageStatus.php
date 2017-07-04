<?php 

//vd(array('args'=>$args));
$mId = $args['input']['request']['id'];
$conc = evArrDflt($args['input']['request'],'conc','');
$smsnumber = evArrDflt($args['input']['request'],'number','');
$smsmessage = evArrDflt($args['input']['request'],'message','');

$res = $this->getModel()->getMessageStatus($mId);

include('mstat.fragment.php');

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
<input type="hidden" id="number" name="number" value="<?php echo $smsnumber;?>">
<input type="hidden" id="message" name="message" value="<?php echo evEscHtml($smsmessage);?>">
<div class="btn-group" id="buttonsGroup">
<?php if (!$sent){ ?>
<input type="submit" class="btn btn-warning btn-md" name="submit" id="submit" value="Sprawdź">&nbsp;
<?php }//sent
E::uiO('closeModalButton'); ?>
</div>
	<div id="waitDiv" style="display:none;">
	<p> <i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i> Proszę czekać ...</p>
	</div>
</form>