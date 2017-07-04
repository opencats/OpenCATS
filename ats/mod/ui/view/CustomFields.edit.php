<?php 
//$fieldDefs = $args['fieldDefs'];
//$dataItemType =$args['dataItemType'];
//$template = $args['template'];
//$sectionTitle = $args['sectionTitle'];
include('common/customFields.inc.php');




$dataItemName = $dataItemType->name();
$fl = array();
if (isset($args['fl'])){
	$fl = $args['fl'];
}


if (is_array($fieldDefs)){
?>
<table class="detailsInside" width="100%">
<?php if (!evStrEmpty($sectionTitle)) { ?>
<tr>
<td colspan="2" style="border-top: thin solid;"><strong>
<?php echo $sectionTitle;?>
</strong></td>
</tr>
<?php } //!evStrEmpty($sectionTitle)
 	$trClass='evenTableRow';
	foreach ($fieldDefs as $k=>$v){
		if (!is_array($v)){
?>		
<tr style="background-image: url(images/gradient.gif);">
<td colspan="2" style="border-top: thin solid;">
<?php echo $v;?>
</td>
</tr>	
<?php 			
		} else {
		
		$required = false;
		if (isset($v['required'])){
			$required = true;
		}
		$reqStr = '';
		if (isset($v['rules'])){
			$reqStr = (isset($v['rules']['required']) && $v['rules']['required']==true)?'*':'';
		}
		$value = (isset($fl[$k]))?$fl[$k]:null;
		//$value = $fieldTypeEnum->onMdToUiEdit($value,$template);
		?>
<tr>
<td class="vertical">
<label id="fsl_<?php echo $k;?>" for="fs_<?php echo $k;?>" style="white-space: nowrap;"><?php echo $reqStr;?><?php echo $v['desc'];?>:</label>
</td>
<td class="data">
<?php 
$ftname = $v['fieldTypeEnum']->name;
$ftlname = evStrToLower($ftname);
$dir = dirname(__FILE__);
switch($ftname){
	case 'money':
	case 'intPositive':
	case 'phone':	
	case 'itemName':
		//tymczasowo zmapowane
		$ftlname='string';
	case 'string':
	case 'text':
	case 'date':
	case 'select':
	case 'checkList':	
		include($dir.'/fields/'.$ftlname.'.html.php');
		break;
	default:
		echo 'Unhandled html field type:'.$ftname;	
}	
if ($required) {
  echo 	'&nbsp;*';	
}?>
</td>
</tr>
 <?php 
$trClass = ($trClass=='evenTableRow')?'oddTableRow':'evenTableRow';
		}//isArray$v
}//foreach ($customFields
?>
</table>
<?php 	
}//is_array                       