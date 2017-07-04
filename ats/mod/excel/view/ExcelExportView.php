<?php 

$i = $args['input']['request']['i']; 
$dataSetName = $i;//enum

if (!isset($args['input']['request']['id'])){
	$errors[]="W wywołaniu brak ID dla zlecenia.";
} else {
$id = $args['input']['request']['id']; 

$dataSetEnum = E::getEnum('excelDataSet',$dataSetName);
$exc = Exc::load(PHP_EXCEL_HOME.'/../../xls/'.$dataSetEnum->exportTemplate);

$required=array(
		'id'=>$id,
		'dataSetName'=>$dataSetName,
		'version'=>ATS_VERSION,
);

$errors = array();
$fv = array();

//bierzemy enum


$script = $dataSetEnum->exportScript;

include($script);

$allFields = E::db()->getAllFields();

if (sizeof($errors)==0){

foreach ( $exc->getNamedRanges() as $v ) {
	$fieldDef = null;
	//$errors=array();
	if (evStrStartsWith($v->getName(),'ats.')){
		$key = substr($v->getName(),4);
		if (isset($required[$key])) {
			$v->getWorksheet()->getCell($v->getName())->setValue($required[$key]);
			$required[$key]=true;
			continue;
		}
		$ka = explode('.',trim($key));
		if (!isset($allFields[($ka[0])])){
			$errors[]="Klucz1 pola nie istnieje:".$key;
		} else {
			$f = $allFields[($ka[0])]; 
			if (!isset($f[($ka[1])])){
				$errors[]="Klucz2 pola nie istnieje:".$key;
			} else {
				//$fieldDef = $f[($ka[1])];
				//$fieldsData = $allData[($ka[0])]['fields'];
				//$customFieldsData = $allData[($ka[0])]['customFields'];
				//$dbColumn = (isset($fieldDef['dbColumn']))?$fieldDef['dbColumn']:null;
				//if (isset($fieldsData[$dbColumn])){//pole db
				//	$fv[$key]=$fieldsData[$dbColumn];
				//} else 
				$fieldDef = $f[($ka[1])];
				if (!isset($fieldDef['fieldTypeEnum'])) $errors[]='Brak definicji fypu pola (fieldTypeEnum) dla '.$key;
				$ftEnum = $fieldDef['fieldTypeEnum'];
				$itemData = $allData[($ka[0])];
				if (isset($itemData[($ka[1])])){
					$valueUi=$ftEnum->mdToUiRead($itemData[($ka[1])],$fieldDef);
					$v->getWorksheet()->getCell($v->getName())->setValue($valueUi);
					$fv[$key]=$valueUi;
				} else {
					//brak wartosci ok $errors[]="Wartość pola nie istnieje:".$key;
				}				
			}
		}
		/*vd(array(
				'$v'=>$v,
				'$v->getName()'=>$v->getName(),
				'$fieldDef'=>$fieldDef,
				'$errors'=>$errors,
				'$v->getRange()'=>$v->getRange(),
				'getCell'=>$v->getWorksheet()->getCell($v->getName()),
				'arr'=>$v->getWorksheet()->namedRangeToArray($v->getName())
		));*/
		
	}
}

foreach ($required as $k=>$v){
	if (!($v===true)){
		$errors[]="Brak wymaganego obszaru danych 'ats.".$k."' (dataset ".$dataSetName.").";
	}
}
}//errors
//vd(array('$fv'=>$fv));


//vd(array('exc'=>$exc));

/*// Redirect output to a client’s web browser (OpenDocument)
header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
header('Content-Disposition: attachment;filename="01simple.ods"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($exc->getPhpExcel(), 'OpenDocument');
$objWriter->save('php://output');*/
//exit;
}//isset(id)
$out = !(sizeof($errors)>0);
// Redirect output to a client’s web browser (Excel5)
if ($out){
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="zlecenie.xls"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($exc->getPhpExcel(), 'Excel5');
$objWriter->save('php://output');
} else {
	echo nl2br(print_r($errors,true));
}
