<?php 

$dataItem = $args['dataItem'];
$section = $args['section'];
$template = $args['template'];
$fl = $args['fl'];

$dataItemType = E::dataItemType($dataItem);
$sectionAsoc = $dataItemType->sections[$section];
$sectionTitle = $sectionAsoc['desc'];

//vd(array(
//	'$sectionAsoc'=>$sectionAsoc,
//
//)/);

$fieldsAsoc = array();
$i=0;
$labelFor = array();
foreach($sectionAsoc['fields'] as $ind => $v){
	if (is_array($v)){
		$secKey = '_section'.$i;
		$fieldsAsoc[$secKey]=$v['title'];
		$labelFor = (isset($v['labelFor']))?$v['labelFor']:array();
		$i++;
	} else {
		$fieldsAsoc[$v]=$dataItemType->allFields[$v];
		if (isset($labelFor[$v])) $fieldsAsoc[$v]['desc']=$labelFor[$v];
	}
}

$fieldDefs = $fieldsAsoc;