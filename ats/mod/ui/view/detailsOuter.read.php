<?php

//vd($args);

$cols = evArrDflt($args,'cols',1);
$rows = $args['rows'];

?>
<table class="detailsOutside">
<?php 
foreach ($rows as $rind =>$row){
for($i=0;$i<$cols;$i++){
	if (is_array($row)){
?>		
		<tr><td><?php echo evd(array('$row[$i]'=>$row[$i]));?><?php echo $row[$i]['content'];?></td></tr>
<?php 		
	} else {		
	?>
		<tr><td><?php echo evEscHtml($row);?></td></tr>
<?php }}} ?>	
</table>