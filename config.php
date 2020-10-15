<?php
/*
 * CATS
 * Configuration File
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * $Id: config.php 3826 2007-12-10 06:03:18Z will $
 */

/* License key. */
define('LICENSE_KEY','3163GQ-54ISGW-14E4SHD-ES9ICL-X02DTG-GYRSQ6');

/* legacy root. */
if( !defined('LEGACY_ROOT') )
{
    define('LEGACY_ROOT', '.');
}

/* Database configuration. */
define('DATABASE_USER', 'cats');
define('DATABASE_PASS', 'password');
define('DATABASE_HOST', 'localhost');
define('DATABASE_NAME', 'cats_dev');

/* Authentication Configuration
 * Options are sql, ldap, sql+ldap
 */
define ('AUTH_MODE', 'sql');

/* Resfly.com Resume Import Services Enabled */
define('PARSING_ENABLED', false);

/* If you have an SSL compatible server, you can enable SSL for all of CATS. */
define('SSL_ENABLED', false);

/* Text parser settings. Remember to use double backslashes (\) to represent
 * one backslash (\). On Windows, installing in C:\antiword\ is
 * recomended, in which case you should set ANTIWORD_PATH (below) to
 * 'C:\\antiword\\antiword.exe'. Windows Antiword will have problems locating
 * mapping files if you install it anywhere but C:\antiword\.
 */
define('ANTIWORD_PATH', "\\path\\to\\antiword");
define('ANTIWORD_MAP', '8859-1.txt');

/* XPDF / pdftotext settings. Remember to use double backslashes (\) to represent
 * one backslash (\).
 * http://www.foolabs.com/xpdf/
 */
define('PDFTOTEXT_PATH', "\\path\\to\\pdftotext");

/* html2text settings. Remember to use double backslashes (\) to represent
 * one backslash (\). 'html2text' can be found at:
 * http://www.mbayer.de/html2text/
 */
define('HTML2TEXT_PATH', "\\path\\to\\html2text");

/* UnRTF settings. Remember to use double backslashes (\) to represent
 * one backslash (\). 'unrtf' can be found at:
 * http://www.gnu.org/software/unrtf/unrtf.html
 */
define('UNRTF_PATH', "\\path\\to\unrtf");

/* Temporary directory. Set this to a directory that is writable by the
 * web server. The default should be fine for most systems. Remember to
 * use double backslashes (\) to represent one backslash (\) on Windows.
 */
define('CATS_TEMP_DIR', './temp');

/* If User Details and Login Activity pages in the settings module are
 * unbearably slow, set this to false.
 */
define('ENABLE_HOSTNAME_LOOKUP', false);

/* CATS can optionally use Sphinx to speed up document searching.
 * Install Sphinx and set ENABLE_SPHINX (below) to true to enable Sphinx.
 */
define('ENABLE_SPHINX', false);
define('SPHINX_API', './lib/sphinx/sphinxapi.php');
define('SPHINX_HOST', 'localhost');
define('SPHINX_PORT', 3312);
define('SPHINX_INDEX', 'cats catsdelta');

/* Probably no need to edit anything below this line. */


/* Pager settings. These are the number of results per page. */
define('CONTACTS_PER_PAGE',      15);
define('CANDIDATES_PER_PAGE',    15);
define('CLIENTS_PER_PAGE',       15);
define('LOGIN_ENTRIES_PER_PAGE', 15);

/* Maximum number of characters of the owner/recruiter users' last names
 * to show before truncating.
 */
define('LAST_NAME_MAXLEN', 6);

/* Length of resume excerpts displayed in Search Candidates results. */
define('SEARCH_EXCERPT_LENGTH', 256);

/* Number of MRU list items. */
define('MRU_MAX_ITEMS', 5);

/* MRU item length. Truncate the rest */
define('MRU_ITEM_LENGTH', 20);

/* Number of recent search items. */
define('RECENT_SEARCH_MAX_ITEMS', 5);

/* HTML Encoding. */
define('HTML_ENCODING', 'UTF-8');

/* AJAX Encoding. */
define('AJAX_ENCODING', 'UTF-8');

/* SQL Character Set. */
define('SQL_CHARACTER_SET', 'utf8');

/* Insert BOM in the beginning of CSV file */
/* This is UTF-8 BOM, EF BB BF for UTF-8 */
define('INSERT_BOM_CSV_LENGTH', '3');
define('INSERT_BOM_CSV_1', '239');
define('INSERT_BOM_CSV_2', '187');
define('INSERT_BOM_CSV_3', '191');
define('INSERT_BOM_CSV_4', '');

/* Path to modules. */
define('MODULES_PATH', './modules/');

/* Unique session name. The only reason you might want to modify this is
 * for multiple CATS installations on one server. A-Z, 0-9 only! */
define('CATS_SESSION_NAME', 'CATS');

/* Subject line of e-mails sent to candidates via the career portal when they
 * apply for a job order.
 */
define('CAREERS_CANDIDATEAPPLY_SUBJECT', 'Thank You for Your Application');

/* Subject line of e-mails sent to job order owners via the career portal when
 * they apply for a job order.
 */
define('CAREERS_OWNERAPPLY_SUBJECT', 'CATS - A Candidate Has Applied to Your Job Order');

/* Subject line of e-mails sent to candidates when their status changes for a
 * job order.
 */
define('CANDIDATE_STATUSCHANGE_SUBJECT', 'Job Application Status Change');

/* Password request settings.
 *
 * In FORGOT_PASSWORD_FROM, %s is the placeholder for the password.
 */
define('FORGOT_PASSWORD_FROM_NAME', 'CATS');
define('FORGOT_PASSWORD_SUBJECT',   'CATS - Password Retrieval Request');
define('FORGOT_PASSWORD_BODY',      'You recently requested that your OpenCATS: Applicant Tracking System password be sent to you. Your current password is %s.');

/* Is this a demo site? */
define('ENABLE_DEMO_MODE', false);

/* Offset to GMT Time. */
define('OFFSET_GMT', 2);

/* Should we enforce only one session per user (excluding demo)? */
define('ENABLE_SINGLE_SESSION', false);

/* Automated testing. This is only useful for the CATS core team at the moment;
 * don't worry about this yet.
 */
define('TESTER_LOGIN',     'john@mycompany.net');
define('TESTER_PASSWORD',  'john99');
define('TESTER_FIRSTNAME', 'John');
define('TESTER_LASTNAME',  'Anderson');
define('TESTER_FULLNAME',  'John Anderson');
define('TESTER_USER_ID',   4);

/* Demo login. */
define('DEMO_LOGIN',     'john@mycompany.net');
define('DEMO_PASSWORD',  'john99');

/* This setting configures the method used to send e-mail from CATS. CATS
 * can send e-mail via SMTP, PHP's built-in mail support, or via Sendmail.
 * 0 is recomended for Windows.
 *
 * 0: Disabled
 * 1: PHP Built-In Mail Support
 * 2: Sendmail
 * 3: SMTP
 */
define('MAIL_MAILER', 3);

/* Sendmail Settings. You don't need to worry about this unless MAIL_MAILER
 * is set to 2.
 */
define('MAIL_SENDMAIL_PATH', "/usr/sbin/sendmail");

/* SMTP Settings. You don't need to worry about this unless MAIL_MAILER is
 * set to 3. If your server requires authentication, set MAIL_SMTP_AUTH to
 * true and configure MAIL_SMTP_USER and MAIL_SMTP_PASS.
 */
define('MAIL_SMTP_HOST', "localhost");
define('MAIL_SMTP_PORT', 587);
define('MAIL_SMTP_AUTH', true);
define('MAIL_SMTP_USER', "user");
define('MAIL_SMTP_PASS', "password");
//Options: '', 'ssl' or 'tls'
define('MAIL_SMTP_SECURE', "tls");

/* Event reminder E-Mail Template. */
$GLOBALS['eventReminderEmail'] = <<<EOF
%FULLNAME%,

This is a reminder from the OpenCATS Applicant Tracking System about an
upcoming event.

'%EVENTNAME%'
Is scheduled to occur %DUETIME%.

Description:
%NOTES%

--
OPENCATS Applicant Tracking System
EOF;

/* Enable replication slave mode? This is probably only useful for the CATS
 * core team. If this setting is enabled, no writing to the database will
 * occur, and only ROOT users can login.
 */
define('CATS_SLAVE', false);

/* If enabled, CATS only scans the modules folder once and stores the results
 * in modules.cache.  When enabled, a performance boost is obtained, but
 * any changes to hooks, schemas, or what modules are installed will require
 * modules.cache to be deleted before they take effect.
 */

define('CACHE_MODULES', false);

/* If enabled, the US zipcode database is installed and the user can filter
 * by distance from a zipcode.
 */

define('US_ZIPS_ENABLED', false);

/* LDAP Configuration
 */
define ('LDAP_HOST', 'ldap.forumsys.com');
define ('LDAP_PORT', '389');
define ('LDAP_PROTOCOL_VERSION', 3);

define ('LDAP_BASEDN', 'dc=example,dc=com');

define ('LDAP_BIND_DN', 'cn=read-only-admin,dc=example,dc=com');
define ('LDAP_BIND_PASSWORD', 'password');

define ('LDAP_ACCOUNT', 'cn={$username},dc=example,dc=com'); // '{$username}' cannot be changed, else can

define ('LDAP_ATTRIBUTE_UID', 'uid');
define ('LDAP_ATTRIBUTE_DN', 'dn');
define ('LDAP_ATTRIBUTE_LASTNAME', 'sn');
define ('LDAP_ATTRIBUTE_FIRSTNAME', 'givenname');
define ('LDAP_ATTRIBUTE_EMAIL', 'mail');

define ('LDAP_SITEID', 1);
define ('LDAP_AD', false); // use for AD and Samba LDAP servers

/* Encodings available during Data Import */
/*const IMPORT_FILE_ENCODING = array(
    'ISO-8859-1', 'GB2312', 'Windows-1251', 'Windows-1252', 'Shift JIS',
'GBK', 'Windows-1256', 'ISO-8859-2', 'EUC-JP', 'ISO-8859-15', 'ISO-8859-9', 'Windows-1250',
'Windows-1254', 'EUC-KR', 'Big5', 'Windows-874', 'US-ASCII', 'TIS-620', 'ISO-8859-7', 'Windows-1255'
);*/

/* Job Order statuses (not pipeline statuses) defined in groups */
/* Uncomment and correct bellow if you want different statuses */
/*const JOB_ORDER_STATUS_GROUP = array(
    'Open' => array ('Active', 'On Hold', 'Full'),
    'Closed' => array('Closed', 'Canceled'),
    'Pre-Open' => array('Upcoming', 'Lead')
);*/

/* Job order status(es) used for XML, RSS and Careers portal */
/* Uncomment and correct bellow if you want different statuses to be included */
/*const JOB_ORDER_STATUS_SHARING = array(
    'Active'
);*/

/* Filters that can be used on main job order grid, the first one will be default selected */
/* Uncomment and correct bellow if you want different combination of statuses */
/*const JOB_ORDER_STATUS_FILTERING = array(
    'Active / On Hold / Full',
    'Active',
    'On Hold / Full',
    'Closed / Canceled',
    'Upcoming / Lead'
);*/

/* Job order status(es) used for submission/placement statistics */
/* Uncomment and correct bellow if you want different combination of statistics */
/*const JOB_ORDER_STATUS_STATISTICS = array(
    'Active', 'On Hold', 'Full', 'Closed'
);*/

/* Job Order Default status after creation */
/* Uncomment and correct bellow if you want different default status */
/*const JOB_ORDER_STATUS_DEFAULT = 'Active';*/

/* Job Types mapping
 *
 * Uncomment bellow if you want custom mapping */

 /*
class JOB_TYPES {
    public static $LIST = array(
        'PT' => 'Part-Time',
        'FT' => 'Full-Time',
        'ST' => 'Student',
        'FL' => 'Freelance'
    );
};
*/


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
