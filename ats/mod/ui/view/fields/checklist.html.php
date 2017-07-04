<?php 
 //vd($args);
 $options = $v['options'];
//vd(array(
// 		'$value'=>$value 		
// ));
$withOther=false;
if (isset($v['withOther'])){
	$withOther = $v['withOther'];
}
$fa = explode(',',$value); 
$fv=array();
foreach ($fa as $ka=>$va){
	$fv[$va]=1;
}
 
?>
<fieldset class="checkListGroup">
<legend></legend>  
<ul class="checkbox">
<?php 
foreach ($options as $ok =>$ov){
?>   
	<li><input id="fs_<?php echo $k;?>_<?php echo $ok;?>" name="fs[<?php echo $k;?>][<?php echo $ok;?>]" type="checkbox" <?php if (isset($fv[$ok])) { ?>checked<?php } ?> style="float:bottom;"><label for="fs_<?php echo $k;?>_<?php echo $ok;?>"><?php echo evEscHtml($ov);?></label></li>                           
<?php 
}//foreach ($options
if($withOther){
?>   
<li><input type="text" class="inputbox" id="fs_<?php echo $k;?>_other" name="fs[<?php echo $k;?>_other]" style="width: 50px;" <?php if(isset($fl[$k])): ?>value="<?php echo evEscHtml($fl[($k.'_other')]); ?>"<?php endif; ?> /><?php echo __("Inne");?></li>
<?php 
}//$withOther
?>
   
</ul>
</fieldset> 
<style>
</style>