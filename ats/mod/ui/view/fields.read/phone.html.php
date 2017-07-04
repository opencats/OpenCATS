<?php 
echo $value;
if (!evStrEmpty($value)){//tylko wtedy ma sens
if (isset($v['phoneConc']) && is_array($v['phoneConc'])){
	$conc = $v['phoneConc']['name'].'/'.$fl[($v['phoneConc']['idField'])];
} else {
	$conc = $dataItemName.'/'.$fl['id'];
}
	
	E::uiO('webAction',array(
		'modal'=>true,
		'route'=>'sms/send',
		'href'=>'ph='.$value.'&conc='.$conc,
		'modalSize'=>'520, 180',
	));
}
