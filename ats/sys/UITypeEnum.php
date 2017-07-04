<?php  
define('ATS_UI_TYPE','opui');

class UITypeEnum extends EnumType {

	protected static $fields;
	protected static $values;
	
	public static function init() {
		self::$fields = array(
			"selectOptions" => array(
					'desc'=>"Select options",
					'template'=>array(
							'html'=>'mod/ui/view/SelectOptions.html',
					),
			),
			"calendar" => array(
					'desc'=>"Kalendarz",
					'template'=>array(
							'default'=>'mod/ui/view/calendar.edit',
					),
			),
			"customFields" => array(
					'desc'=>"Custom fields",
					'template'=>array(
							'default'=>'mod/ui/view/CustomFields.read',
							'be'=>'mod/ui/view/CustomFields.be',
							'read'=>'mod/ui/view/CustomFields.read',
							'add'=>'mod/ui/view/CustomFields.edit',
							'edit'=>'mod/ui/view/CustomFields.edit',
					),
			),
			"dOuter" => array(
					'desc'=>"Detale - outer (podzial wiersze/kolumny)",
					'template'=>array(
							'default'=>'mod/ui/view/detailsOuter.read',
					),
			),
			'ouInit' => array(
					'desc'=>'OpenUI5 - inicjalizacja',
					'template'=>array(
							'default'=>'mod/ui/view/ouInit.default',
					),
			),
			'ouXmlView' => array(
					'desc'=>'OpenUI5 - XmlView',
					'template'=>array(
							'default'=>'mod/ui/view/ouXmlView.default',
					),
			),
			"message" => array(
					'desc'=>"Komunikat UI - ",
					'template'=>array(
							'default'=>'mod/ui/view/message.read',
					),
			),
			"closeModalButton" => array(
					'desc'=>"Guzior zamykający okno modal.",
					'template'=>array(
							'default'=>'mod/ui/view/closeModalButton.read',
					),
			),
			"webAction" => array(
					'desc'=>"Akcja Web.",
					'template'=>array(
							'default'=>'mod/ui/view/webAction.read',
					),
			),
				
				
		);
	}


	public function html($args){
		evIncBeMany($this->getAttr('template')['html'], false, $args);
	}
	
	public function byTemplate($tname,$args){
		$tmpl = $this->getAttr('template');
		if (!isset($tmpl[$tname])) {
			throw new \Exception('Unknown UITypeEnum::'.$this->name.' template reference:'.$tname.' (not defined?).');
		}
		evIncBeMany($this->getAttr('template')[$tname], false, $args);
	}
	
}
UITypeEnum::init();
UITypeEnum::initValues();
?>