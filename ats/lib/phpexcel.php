<?php 

define('PHP_EXCEL_HOME',dirname(__FILE__) . '/PHPExcel-1.8');
$_GET['route']='js';
include_once(dirname(__FILE__) .'/../index.php');

class ExcObj {
	
	private $phpExcel;
	
	function __construct($obj){
		$this->phpExcel = $obj;
	}
	
	function getNamedRanges(){
		return $this->phpExcel->getNamedRanges();
	}
	
	function getActiveSheet(){
		return $this->phpExcel->getActiveSheet();
	}
	
	function getPhpExcel(){
		return $this->phpExcel;
	}
	
	
}


class Exc {
	
	public static function load($filename){
		/** Include PHPExcel_IOFactory */
		include_once PHP_EXCEL_HOME.'/Classes/PHPExcel/IOFactory.php';
		$objPHPExcel = PHPExcel_IOFactory::load($filename);
		$result = new ExcObj($objPHPExcel);
		return $result;
	}
}

/*
$exc = Exc::load(PHP_EXCEL_HOME.'/Examples/01simple.xls');
$exc = Exc::load(dirname(__FILE__).'/konta.xls');

// start pobieranie w kolejności nazw kolumn
foreach ( $exc->getNamedRanges() as $v ) {
	echo 'range:'.$v->getRange();
	//vd(array(
		'$v'=>$v,
		'$v->getName()'=>$v->getName(),	
		'arr'=>$v->getWorksheet()->namedRangeToArray($v->getName())	
	));
}

$exc = Exc::load(dirname(__FILE__).'/konta.xls');
$arr = $exc->getActiveSheet()->namedRangeToArray('ats');
//vd(array(
		'$arr'=>$arr,
));
*/

?>