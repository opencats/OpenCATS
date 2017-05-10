<?php  

class UITypeEnum extends EnumType {
	
	protected static $fields = array(
			"selectOptions" => array(
					'desc'=>"Select options",
					'htmlTemplate'=>'mod/ui/view/SelectOptions.html',
			)
	);
	
public function getFields() {
	return self::$fields;
}

//function getDefPath(){
//	return $this->getAttr('definitionPath');
//}

/*function enumValues(){
	evIncEnum($this);
	$fcname=ucfirst($this->name()).'Enum::values';
	//vd(array(
	//	'$fcname'=>$fcname	
	//));
	$values = call_user_func($fcname);
	return $values;
}*/

/*function enumByAttr($attrName,$attrValue){
	$ev = $this->enumValues();
	$result = null;
	foreach($ev as $k=>$v){
		if ($attrValue==$v->getAttr($attrName)){
			$result = $v;
			break;
		}
	}
	return $result;
}*/

public function html($args){
	evIncBe($this->getAttr('htmlTemplate'), false, $args);
}

}

?>