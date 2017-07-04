<?php 
$userData = E::c('user')->getCurrentUserData();
$ph = evArrDflt($args['input']['request'],'ph','');
$conc = evArrDflt($args['input']['request'],'conc','');
//$u = E::db()->getUser(array('id'=>$_SESSION['CATS']->getUserID()));
//vd($u);
if (!isset($userData['meGWDeviceId'])
	|| !isset($userData['meGWMail'])
	|| !isset($userData['meGWPassword'])
		) {
		E::uiO('message',array(
			'type'=>'error',	
			'text'=>'Brak danych potrzebnych do wysłania SMS: Id urządzenia, email, i hasło. Uzupełnij odpowiednie dane w swoim profilu.',	
			));
?><?php 
	E::uiO('closeModalButton');
	} else {

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
<form class="form-horizontal"name="sendSmsForm" id="sendSmsForm" action="<?php echo E::routeHref('sms/sendAction')?>" enctype="multipart/form-data" method="post"
onSubmit="upgOnSubmit('Proszę czekać ...');"
>
<input type="hidden" id="phNumber" name="phNumber" value="<?php echo $ph;?>">
<input type="hidden" id="conc" name="conc" value="<?php echo $conc;?>">
<div class="form-group row">
<label class="control-label col-xs-3" for="fs_number" style="text-align:right;">*&nbsp;Telefon:</label>
<div class="col-xs-3">
<input id="fs_number" class="form-control input-sm" type="text" tabindex="16" name="fs[number]" value="<?php echo $ph; ?>" style="width: 150px;" />
</div>
</div>
<div class="form-group row">
<label class="control-label col-xs-3" for="fs_message" style="text-align:right;">*&nbsp;Wiadomość:</label>
<div class="col-xs-3">
<textarea id="fs_message" class="form-control input-sm" tabindex="20" name="fs[message]" rows="2" style="width: 250px;"></textarea>
</div>
</div>
<div class="form-group row">
<div class="col-xs-3">
</div>
<div class="col-xs-9">
<div class="btn-group" id="buttonsGroup">
<input type="submit" class="btn btn-primary btn-md" name="submit" id="submit" value="Wyślij">&nbsp;
<input type="button" class="btn btn-info btn-md" name="cancel" value="Anuluj" onclick="parentHidePopWin();">
</div>
	<div id="waitDiv" style="display:none;">
	<p> <i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i> Proszę czekać ...</p>
	</div>
</div>
</div>




</form>

<?php 

	}
