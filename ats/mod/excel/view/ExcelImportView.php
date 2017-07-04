<?php 

//vd($args);

$request = $args['input']['request'];

$krok = evArrDflt($request,'krok','upload');
switch ($krok){
	case 'upload':
		include('import/upload.php');
		break;
	case 'import':
		include('import/import.php');
		break;
}
