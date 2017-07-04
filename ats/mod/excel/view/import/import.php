<?php 

//vd($_POST);

$files = $args['input']['files'];

$originalFilename = $files['file']['name'];
$tempFilename     = $files['file']['tmp_name'];
$contentType      = $files['file']['type'];
$fileSize         = $files['file']['size'];
$fileUploadError  = $files['file']['error'];

if (get_magic_quotes_gpc())
{
	$originalFilename = stripslashes($originalFilename);
	$contentType      = stripslashes($contentType);
}

if (!file_exists($tempFilename)){
	E::uiO('message',array(
			'type'=>'error',
			'text'=>'Brak pliku:'.$tempFilename,
	));
	include('upload.php');
} else {

$exc = Exc::load($tempFilename);
$nr = $exc->getNamedRanges();
$allData = array();
//odczyt danych z excela
foreach ($nr as $v ) {
	if (evStrStartsWith($v->getName(),'ats.')){
		$key = substr($v->getName(),4);
		
		$name = $v->getName();
		$value = $v->getWorksheet()->namedRangeToArray($name);
		/*vd(array(
				'$name'=>$name,
				'$value'=>$value,
		));*/
		$allData[$key]=$value;
	}
}

$inserts=array();
// na razie zawsze insert
$cc = E::controller('cats');
$userId = $cc->getUserId();
$siteId = $cc->getSiteId();
$curDate = date('Y-m-d H:i:s');
foreach ($allData as $k =>$v){
	$a = explode('.',$k);
	$itemName = $a[0];
	$itemcol = $a[1];
	foreach ($v as $ind =>$a1){
		$inserts[$itemName][$ind][$itemcol]=$a1[0];
	}	
}

$db = E::db();
/*vd(array(
	'$inserts'=>$inserts	
));*/
$allFields = E::db()->getAllFields();
$dateType = E::enum('dataItemFieldType','date');
$dateTimeType = E::enum('dataItemFieldType','dateTime');
$imported=array();
$impOrder = array(

	'company'=>array(
		'doImport'=>true,
		'key'=>'name',
		'addValues'=>array(
			'siteId'=>$siteId,
			'dtCreated'=>$curDate,
			'userId'=>$userId,
			'mgUserId'=>$userId,
		),	
	),
	'contact'=>array(
			'doImport'=>true,
			'key'=>'phoneWork',
			'fk'=>array(
				'companyId'=>'company',	
			),
			'addValues'=>array(
					'siteId'=>$siteId,
					'dtCreated'=>$curDate,
					'userId'=>$userId,
					'mgUserId'=>$userId,
			),
	),
	'jobOrder'=>array(
		'doImport'=>true,
		//'key'=>'phoneWork'
		'fk'=>array(
				'companyId'=>'company',
				'contactId'=>'contact',
		),
		'addValues'=>array(
				'siteId'=>$siteId,
				'dtCreated'=>$curDate,
				'userId'=>$userId,
				'mgUserId'=>$userId,
				'rcUserId'=>$userId,
		),
	),	
	'candidate'=>array(
		'doImport'=>true,
		//'key'=>'phoneWork'
		'addValues'=>array(
				'siteId'=>$siteId,
				'dtCreated'=>$curDate,
				'userId'=>$userId,
		),
	),	
);


//ie();
$importedIds=array();
$realInserts =array();
$insid = 1;
//foreach($inserts as $itemName => $recs){
foreach($impOrder as $itemName =>$impDef){
	$recs = (isset($inserts[$itemName]))?$inserts[$itemName]:array();
	if (sizeof($recs)>0){
		$dataItemType = E::dataItemType($itemName);
		$countOk = 0;
		$errors = array();
		foreach($recs as $recno => $rec){
			//$rec['siteId']=$siteId;
			
			$doImport = true;
			if (isset($impDef['key'])){
				$kval = $rec[($impDef['key'])];
				if (isset($importedIds[$itemName][$kval])){
					$doImport = false;
				}
			}
			
			if ($doImport) {
				
				if (is_array($rec)){//format from Excel to db
					foreach($rec as $k =>$v){
						//$allKey = $itemName.'.'.$k;
						$fdef = $allFields[$itemName][$k];
						if ($fdef['fieldTypeEnum']==$dateType){
							$rec[$k]=substr($v,6,4).'-'.substr($v,3,2).'-'.substr($v,0,2);
						} else if ($fdef['fieldTypeEnum']==$dateTimeType) {
							$rec[$k]=substr($v,6,4).'-'.substr($v,3,2).'-'.substr($v,0,2);
						}
					}
				}
			
				//fk by $importedIds
				if (isset($impDef['fk'])){
					foreach ($impDef['fk'] as $fname =>$dtiName){
						$key = $impOrder[$dtiName]['key'];
						//$kval = $allData[($dtiName.'.'.$key)][$recno];
						$kval = $inserts[$dtiName][$recno][$key];
						//foreach ($importedIds[$dtiName] )
						//$kval = $this->findKVal()
						$rec[$fname] = $importedIds[$dtiName][$kval];
					}
				}
				
				if (isset($impDef['addValues'])){
					foreach($impDef['addValues'] as $k=>$v){
						$rec[$k]=$v;
					}
				}
				
				$ins = array(
					'dataItemType'=>$dataItemType,
					'fs'=> $rec,	
				);
				//vd($ins);
				$res = $db->insert(array(
				 'dataItemType'=>$dataItemType,
				 'fs'=> $rec,
				 ));
				//$res = array(
				//	'insertId'=>$insid++,	
				//	'wasError'=>false,	
				//);
				$realInserts[]=array(
						'ins'=>$ins,
						'res'=>$res,
				);

				if ($res['wasError']){
					$errors[]="Błąd przy imporcie danych.";
				} else {
					$countOk++;
					//budowa
					if (isset($impDef['key'])){
						$kval = $rec[($impDef['key'])];
						$importedIds[$itemName][$kval]=$res['insertId'];
					}
				}
			
			}
		}
		$imported[$itemName]=array(
				'dataItemType'=>$dataItemType,
				'countOk'=>$countOk,
				'errors'=>$errors,
		);
	}
}

/*vd(array(
		'$allFields'=>$allFields,
		'$allData'=>$allData,
		'$inserts'=>$inserts,
		'$importedIds'=>$importedIds,
		'$realInserts'=>$realInserts,
));*/

$rows=array();
if (sizeof($imported)>0){
	foreach($imported as $k =>$v){
		$desc = $v['dataItemType']->desc;
		$str = $desc.' - zaimportowano '.$v['countOk'];
		$rows[]=$str;
		if (sizeof($v['errors'])>0){
			$str = $desc." - Błędy:<br/>";
			foreach ($v['errors'] as $l =>$e){
				$str.=$e;
			}
			$rows[]=$str;
		}
	}
} else {
	$rows[]='Nie zaimportowano żadnych danych';
}

?>
<h3>Imort danych z arkusza Excel (*.xls)</h3>
<?php 

/*vd(array(
		'$rows'=>$rows,
		
));*/

E::uiO(array(
	'name'=>'dOuter',
	'def'=>array(
		'cols'=>1,
		'rows'=>$rows,
	)		
));

}//file_exists