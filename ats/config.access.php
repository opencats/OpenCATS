<?php 

// defining user roles
const USER_ROLES = array(
		'praktykant' => array('Praktykant', 'praktykant', 'Rola praktykanta.', ACCESS_LEVEL_SA, ACCESS_LEVEL_READ),
		// 'demo' => array('Demo', 'demo', 'This is a demo user.', ACCESS_LEVEL_SA, ACCESS_LEVEL_READ)
);

/*
 * define('ACCESS_LEVEL_DELETED',  -100);
 define('ACCESS_LEVEL_DISABLED', 0);
 define('ACCESS_LEVEL_READ',     100);
 define('ACCESS_LEVEL_EDIT',     200);
 define('ACCESS_LEVEL_DELETE',   300);
 define('ACCESS_LEVEL_DEMO',     350);
 define('ACCESS_LEVEL_SA',       400);
 define('ACCESS_LEVEL_MULTI_SA', 450);
 define('ACCESS_LEVEL_ROOT',     500);
 */

// defining access levels different from the default access level
const ACCESS_LEVEL_MAP = array(

		'praktykant' => array(
				'' => ACCESS_LEVEL_DISABLED,
				'candidates.list' => ACCESS_LEVEL_READ,
				'candidates.add' => ACCESS_LEVEL_EDIT,
				'candidates.show' => ACCESS_LEVEL_READ,
				'candidates.edit' => ACCESS_LEVEL_EDIT,
				'settings' =>	ACCESS_LEVEL_READ,
		)
);

/*
 *
 'candidates' => ACCESS_LEVEL_DISABLED,
 'candidates.show' => ACCESS_LEVEL_DISABLED,
 'candidates.list' => ACCESS_LEVEL_DISABLED,
 'candidates.emailCandidates' => ACCESS_LEVEL_DISABLED,
 'candidates.history' => ACCESS_LEVEL_DEMO,
 'joborders' => ACCESS_LEVEL_DELETE,
 'joborders.show' => ACCESS_LEVEL_DEMO,
 'joborders.email' => ACCESS_LEVEL_DISABLED,
 *
 *  All possible secure object names
 'candidates.history'
 'settings.administration'
 'joborders.editRating'
 'pipelines.screening'
 'pipelines.editActivity'
 'pipelines.removeFromPipeline'
 'pipelines.addActivityChangeStatus'
 'pipelines.addToPipeline'
 'settings.tags'
 'settings.changePassword'
 'settings.newInstallPassword'
 'settings.forceEmail'
 'settings.newSiteName'
 'settings.upgradeSiteName'
 'settings.newSiteName'
 'settings.manageUsers'
 'settings.professional'
 'settings.previewPage'
 'settings.previewPageTop'
 'settings.showUser'
 'settings.addUser'
 'settings.editUser'
 'settings.createBackup'
 'settings.deleteBackup'
 'settings.customizeExtraFields'
 'settings.customizeCalendar'
 'settings.reports'
 'settings.careerPortalQuestionnairePreview'
 'settings.careerPortalQuestionnaire'
 'settings.careerPortalQuestionnaireUpdate'
 'settings.careerPortalTemplateEdit'
 'settings.careerPortalSettings'
 'settings.eeo'
 'settings.careerPortalTweak'
 'settings.deleteUser'
 'settings.aspLocalization'
 'settings.loginActivity'
 'settings.viewItemHistory'
 'settings.addUser'
 'settings.deleteUser'
 'settings.checkKey'
 'settings.localization'
 'settings.firstTimeSetup'
 'settings.license'
 'settings.password'
 'settings.siteName'
 'settings.setEmail'
 'settings.import'
 'settings.website'
 'settings.administration'
 'settings.myProfile'
 'settings.administration.localization'
 'settings.administration.systemInformation'
 'settings.administration.changeSiteName'
 'settings.administration.changeVersionName'
 'settings.addUser'
 'joborders.edit'
 'joborders.careerPortalUrl'
 'joborders.deleteAttachment'
 'joborders.createAttachement'
 'joborders.delete'
 'joborders.hidden'
 'joborders.considerCandidateSearch'
 'joborders.show'
 'joborders.add'
 'joborders.search'
 'joborders.administrativeHideShow'
 'joborders.list'
 'joborders.email'
 'candidates.add'
 'import.import'
 'import.massImport'
 'import.bulkResumes'
 'contacts.addActivityScheduleEvent'
 'contacts.edit'
 'contacts.delete'
 'contacts.editActivity'
 'contacts.deleteActivity'
 'contacts.logActivityScheduleEvent'
 'contacts.show'
 'contacts.add'
 'contacts.edit'
 'contacts.delete'
 'contacts.search'
 'contacts.addActivityScheduleEvent'
 'contacts.showColdCallList'
 'contacts.downloadVCard'
 'contacts.list'
 'contacts.emailContact'
 'companies.deleteAttachment'
 'companies.createAttachment'
 'companies.edit'
 'companies.delete'
 'companies.show'
 'companies.internalPostings'
 'companies.add'
 'companies.edit'
 'companies.delete'
 'companies.search'
 'companies.createAttachment'
 'companies.deleteAttachment'
 'companies.list'
 'companies.email'
 'candidates.deleteAttachment'
 'candidates.addActivityChangeStatus'
 'candidates.deleteAttachment'
 'candidates.createAttachment'
 'candidates.addCandidateTags'
 'candidates.edit'
 'candidates.delete'
 'candidates.administrativeHideShow'
 'candidates.considerForJobSearch'
 'candidates.manageHotLists'
 'candidates.show'
 'candidates.add'
 'candidates.search'
 'candidates.viewResume'
 'candidates.search'
 'candidates.hidden'
 'candidates.emailCandidates'
 'candidates.show_questionnaire'
 'candidates.list'
 'calendar.show'
 'calendar.addEvent'
 'calendar.editEvent'
 'calendar.deleteEvent'
 */


/*$al = E::enum('accessLevel')->enumValues();
//vd($al);
//const a = $al['admin']->dbValue;
//const r = $al['read']->dbValue;
global $ATS_USER_ROLES;
$ATS_USER_ROLES = array(
		'praktykant' => array('Praktykant', 'praktykant', 'Rola praktykanta.',$al['admin']->dbValue, $al['read']->dbValue),
		// 'demo' => array('Demo', 'demo', 'This is a demo user.', ACCESS_LEVEL_SA, ACCESS_LEVEL_READ)
);

global $ATS_USER_ROLE_ACL;
$ATS_USER_ROLE_ACL = array(

		'praktykant' => array(
				'' => $al['disabled']->dbValue,
				'candidates.list' => $al['read']->dbValue,
				'candidates.add' => $al['modify']->dbValue,
				'candidates.show' => $al['read']->dbValue,
				'candidates.edit' => $al['modify']->dbValue,
				'settings' =>	$al['read']->dbValue,
		)
);*/