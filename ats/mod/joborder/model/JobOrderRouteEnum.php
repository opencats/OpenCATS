<?php  

class JobOrderRouteEnum extends RouteEnum {

	protected static $fields;
	protected static $values;

	public static function init() {
		self::$fields= array(
				"show" => array(
						'desc'=>'Szczegóły zlecenia',
						'idVar'=>'jobOrderID',
						'actions'=>array(
							'edit'=>array(
									'link'=>'index.php?m=joborders&a=edit&jobOrderID=[id]',
									'iconHref'=>'images/actions/edit.gif',
									'desc'=>'Edytuj',
							),
							'delete'=>array(
										'cond'=>'return false;',
										'link'=>'index.php?m=joborders&a=delete&jobOrderID=[id]',
										'iconHref'=>'images/actions/delete.gif',
										'onclick'=>'javascript:return confirm(\'Usunąć to zlecenie?\');',
										'desc'=>'Usuń',
							),
							'adminShow'=>array(
									'cond'=>'return ($obj[\'isAdminHidden\']==1);',
									'link'=>'index.php?m=joborders&a=administrativeHideShow&jobOrderID=[id]&state=0',
									'iconHref'=>'images/resume_preview_inline.gif',
									'desc'=>'Pokaż(Administrator)',
							),
							'adminHide'=>array(
									'cond'=>'return ($obj[\'isAdminHidden\']==0);',
									'link'=>'index.php?m=joborders&a=administrativeHideShow&jobOrderID=[id]&state=1',
									'iconHref'=>'images/resume_preview_inline.gif',
									'desc'=>'Ukryj(Administrator)',
							),
							'createAttachment'=>array(
									//'cond'=>'return ($obj[\'isAdminHidden\']==0);',
									'link'=>'#',
									'iconHref'=>'images/paperclip_add.gif',
									'onclick'=>'showPopWin(\'index.php?m=joborders&a=createAttachment&jobOrderID=[id]\', 400, 125, null); return false;',
									'desc'=>'Dodaj załącznik',
							),
							'addCandidate'=>array(
									//'cond'=>'return ($obj[\'isAdminHidden\']==0);',
									'link'=>'showPopWin(\'index.php?m=joborders&a=considerCandidateSearch&jobOrderID=[id]\', 820, 550, null); return false;',
									'iconHref'=>'images/consider.gif',
									'onclick'=>'showPopWin(\'index.php?m=joborders&a=createAttachment&jobOrderID=[id]\', 400, 125, null); return false;',
									'desc'=>'Dodaj kandydata do obsługi tego zlecenia',
							),
								
						)
				)
				, "SQLITE" => array('desc'=>'jajo')
				, "FILES" => array('desc' => 'jajo2')
		);
	}
}
JobOrderRouteEnum::init();
JobOrderRouteEnum::initValues();
?>