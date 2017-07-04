<?php 

$jobOrderSections = array(	
							'col1read'=>array(
									'desc'=>'',
									'fields'=>array(
											'contactWorkPhone',
											'test1',
									),
							),	
							'rangeAndConditions1'=>array(
									'desc'=>'',
									'fields'=>array(
											'rangeOfService',
									),
							),
							'rangeAndConditions2'=>array(
									'desc'=>'',
									'fields'=>array(
											'dueDateOfService',
											'costsOfService',
											'condOfPayment',
									),
							),
							'requirements1'=>array(
									'desc'=>'',
									'fields'=>array(
											array(
													'title'=>'Charakter pracy',
													'labelFor'=>array(
															'jobNature'=>'',
													),
											),
											'jobNature',
											'jobNatureOther',
											array(
												'title'=>'Proponowane wynagrodzenie - okres próbny',	
											),
											'netSalaryProbBase',
											'netSalaryProbBonus',
											'netSalaryProbProv',
											'netSalaryProbOther',
											array(
													'title'=>'Proponowane wynagrodzenie - po okresie próbnym',
											),
											'netSalaryBase',
											'netSalaryBonus',
											'netSalaryProv',
											'netSalaryOther',
											array(
													'title'=>'Możliwe formy zatrudnienia',
													'labelFor'=>array(
															'possibleJobTypes'=>'',
													),
											),
											'possibleJobTypes',
											array(
													'title'=>'Narzędzia',
													'labelFor'=>array(
															'tools'=>'',
													),
											),
											'tools',
											'toolsOther',
											array(
													'title'=>'Pakiet motywacyjny',
													'labelFor'=>array(
															'motivPackage'=>'',
													),
											),
											'motivPackage',
											'motivPackageOther',											
									),
							),
							'requirements2'=>array(
									'desc'=>'',
									'fields'=>array(
											array(
												'title'=>'Wykształcenie'	
											),
											'educationReq',
											'educationPref',
											'educationFields',
											array(
													'title'=>'Doświadczenie - wymagane'
											),
											'expReqYearsInBranch',
											'expReqYearsInGeneral',
											'expReqYearsB2B',
											'expReqYearsB2C',
											array(
													'title'=>'Doświadczenie - mile widziane'
											),
											'expPrefYearsInBranch',
											'expPrefYearsInGeneral',
											'expPrefYearsB2B',
											'expPrefYearsB2C',
											array(
													'title'=>'Dodatkowe kursy i uprawnienia/umiejętności i cechy osobowe',
													'labelFor'=>array(
															'addSkillsReq'=>'Wymagane',
															'addSkillsPref'=>'Mile widziane',
													),
											),
											'addSkillsReq',
											'addSkillsPref',
											array(
													'title'=>'Programy komputerowe, kompetencje IT',
													'labelFor'=>array(
															'compSkillsReq'=>'Wymagane',
															'compSkillsPref'=>'Mile widziane',
													),
											),
											'compSkillsReq',
											'compSkillsPref',
											array(
													'title'=>'Języki',
													'labelFor'=>array(
															'langSkillsReq'=>'Wymagane',
															'langSkillsPref'=>'Mile widziane',
													),
											),
											'langSkillsReq',
											'langSkillsPref',
											array(
													'title'=>'Prawo jazdy',
											),
											'drivingLicence',
											array(
													'title'=>'Inne umiejętności',
													'labelFor'=>array(
															'otherSkillsReq'=>'Wymagane',
															'otherSkillsPref'=>'Mile widziane',
													),
											),
											'otherSkillsReq',
											'otherSkillsPref',
												
									),
							),
						);
