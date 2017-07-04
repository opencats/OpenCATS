<?php 
 //vd($args);
 $options = $v['options'];

?>
<select id="fs_<?php echo $k?>" name="fs[<?php echo $k?>]" class="inputbox" style="width: 150px;">
	<option value="">(<?php echo __("None");?>)</option>
<?php 
foreach ($options as $ok =>$ov){
?>   
	<option value="<?php echo $ok;?>" <?php if ($ok==$value) {?>selected="selected"<?php } ?>><?php echo evEscHtml($ov);?></option>                           
<?php 
}//foreach ($options
?>      
</select>