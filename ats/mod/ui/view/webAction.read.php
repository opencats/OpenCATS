<?php

$route=$args['route'];
$href=$args['href'];

$modalSize = evArrDflt($args,'modalSize','400, 160');

$routeEnum = E::routeEnum($route);
$icon = null;
$text = evArrDflt($args,'text',$route);
if ($routeEnum!=null){
	$icon = $routeEnum->icon;
	$alt = $routeEnum->desc;
	$text = $routeEnum->desc;
}

?>
<a href="#" onclick="showPopWin('<?php echo E::routeHref($route); ?>?<?php echo $href; ?>', <?php echo $modalSize;?>, null); return false;">
<?php if ($icon!=null){?>
	<img src="<?php echo $icon;?>" width="16" height="16" border="0" alt="<?php echo $alt;?>" class="absmiddle" />
<?php } else { 
	echo $text;
}?>
</a>