<?php 

$classes = array(
	'success'=>'success',
	'info'=>'info',
	'error'=>'danger',
	'warn'=>'warning',
	'fatal'=>'danger',	
);

$type = evArrDflt($args,'type','info');
$class = $classes[$type];
$text = evArrDflt($args,'text','');
?>
<div class="alert alert-<?php echo $class;?>">
<?php echo $text;?>
</div>