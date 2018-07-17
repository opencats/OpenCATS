<?php

/*
require_once(LEGACY_ROOT . '/constants.php');

class ACL_SETUP {

    // defining user roles
    public static $USER_ROLES = array(
        'candidate' => array('Candidate', 'candidate', 'This is a candidate.', ACCESS_LEVEL_SA, ACCESS_LEVEL_READ),
        'demo' => array('Demo', 'demo', 'This is a demo user.', ACCESS_LEVEL_SA, ACCESS_LEVEL_READ)
    );
   
    // defining access levels different from the default access level    
    public static $ACCESS_LEVEL_MAP = array(
        'candidate' => array(
        ),
        'demo' => array(
            'candidates' => ACCESS_LEVEL_DELETE,
            'candidates.emailCandidates' => ACCESS_LEVEL_DISABLED,
            'candidates.history' => ACCESS_LEVEL_DEMO,
            'joborders' => ACCESS_LEVEL_DELETE,
            'joborders.show' => ACCESS_LEVEL_DEMO,
            'joborders.email' => ACCESS_LEVEL_DISABLED,
        )
    );
};
*/

/* All possible secure object names 
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
            'candidates.duplicates'
            'calendar.show'
            'calendar.addEvent'
            'calendar.editEvent'
            'calendar.deleteEvent'
            */

?>