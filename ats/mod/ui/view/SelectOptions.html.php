<?php
$eTypes = $args['enum']->enumValues();
$dbValue=isset($args['dbValue'])?$args['dbValue']:null;
foreach($eTypes as $k =>$v){
	$selected = ($dbValue!=null && $v->dbValue==$dbValue);
	?>    
		<option <?php if ($selected) { ?>selected="selected"<?php } ?> value="<?php echo($v->dbValue); ?>"><?php echo $v->desc;?></option>
<?php } //foreach($eTypes ?>