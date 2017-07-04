<?php 
$fieldDefs = $args['fieldDefs'];
$dataItemType =$args['dataItemType'];

$dataItemName = $dataItemType->name();

if (is_array($fieldDefs)){
	?>
	<table class="sortable" id="customFieldsTable<?php echo $dataItemName;?>" width="560">
	<thead>
	<tr>
	<th width="400" nowrap="nowrap" align="left">
	<?php echo __("Field Name");?>                                            </th>
	<th align="left">
	<?php echo __("Field Type");?>                                            </th>
	</tr>
	</thead>
<tbody>
<?php 
$trClass='evenTableRow';
foreach ($fieldDefs as $k=>$v){		
?>
<tr class="<?php echo $trClass;?>">
<td><?php echo $v['desc'];?></td>	
<td><?php echo $v['fieldTypeEnum']->desc;?></td>
</tr>
<?php 
$trClass = ($trClass=='evenTableRow')?'oddTableRow':'evenTableRow';
}//foreach ($customFields
?>
</tbody></table>
<?php 	
}//is_array