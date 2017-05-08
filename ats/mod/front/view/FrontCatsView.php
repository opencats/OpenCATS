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
?>

<?php 

echo $args['content'];

?>