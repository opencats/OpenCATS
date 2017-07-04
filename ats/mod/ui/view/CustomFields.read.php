<?php 
//$fieldDefs = $args['fieldDefs'];
//$dataItemType =$args['dataItemType'];
//$template = $args['template'];
include('common/customFields.inc.php');


$dataItemName = $dataItemType->name();
$fl = array();
if (isset($args['fl'])){
	$fl = $args['fl'];
}

if (is_array($fieldDefs)){
	?>
<table class="detailsInside" height="100%" width="100%">
<?php 
$trClass='evenTableRow';
foreach ($fieldDefs as $k=>$v){
	if (!is_array($v)){
		?>
	<tr style="background-color: #e4e4e4;">
	<td colspan="2">
	<?php echo $v;?>
	</td>
	</tr>	
	<?php 			
			} else {
	
	$fieldTypeEnum =$v['fieldTypeEnum'];
	$value = (isset($fl[$k]))?$fl[$k]:null;
	$value = $fieldTypeEnum->onMdToUiRead($value,$v); 
?>
<tr>
	<td class="vertical"><?php echo $v['desc'];?></td>	
	<td class="data"><?php 
if (!file_exists(dirname(__FILE__).'/fields.read/'.$fieldTypeEnum->name().'.html.php')){	
	echo $value;
} else {
	include(dirname(__FILE__).'/fields.read/'.$fieldTypeEnum->name().'.html.php');
}	
	?></td>
</tr>
<?php 
$trClass = ($trClass=='evenTableRow')?'oddTableRow':'evenTableRow';
			}//isArray$v
}//foreach ($customFields
?>
</table>
<?php 	
}//is_array