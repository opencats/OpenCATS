<?php 
evIncBe('sys/EnumType');
abstract class RouteEnum extends EnumType {

	//const E1 = array('','');

	//const E1 = StoreFields::E1();
	
	const frontView = 'frontView';
	const view = 'view';
	const desc = 'desc';
	
	public function getFrontView(){
		return $this->attrs[RouteEnum::frontView];
	} 

}


?>