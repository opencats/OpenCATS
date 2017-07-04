<?php 

$isModal = evGetGlobal('isModal');
//vd(array('$isModal'=>$isModal));
if ($isModal){
	echo evGetGlobal('printModalHeader')['output'];
} else {
	echo evGetGlobal('printHeader')['output'];
	echo evGetGlobal('printHeaderBlock')['output'];
	echo evGetGlobal('printTabs')['output'];
} 

echo $args['content'];
echo evGetGlobal('printFooter')['output'];

function putJs($content){
	//evGetGlobal('printModalHeader')['result']
	$result = '<script type="text/javascript">'."\n"; 
	$result.=evGetJs('js/lib');
	$result.=evGetJs('js/quickAction');
	$result.=evGetJs('js/calendarDateInput');
	$result.=evGetJs('js/submodal/subModal');
	$result.=evGetJs('js/jquery-1.3.2.min');
	$result.= 'CATSIndexName = "'.CATSUtility::getIndexName().'";';
	$result = evStrReplace($result,"'</script>'","'</'+'script'+'>'");
	$result.='</script>'."\n";	
	$result=evStrReplace($content,'###JsPlaceHolder###',$result);
	return $result;
}

?>