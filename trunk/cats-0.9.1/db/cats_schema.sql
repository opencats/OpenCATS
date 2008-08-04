
DROP TABLE IF EXISTS `access_level`;
CREATE TABLE `access_level` (
  `access_level_id` int(11) NOT NULL default '0',
  `short_description` varchar(32) collate utf8_unicode_ci NOT NULL default '',
  `long_description` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`access_level_id`),
  KEY `IDX_access_level` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `access_level` WRITE;
INSERT INTO `access_level` VALUES (0,'Account Disabled','Disabled - The lowest access level. User cannot log in.'),(100,'Read Only','Read Only - A standard user that can view data on the system in a read-only mode.'),(200,'Add / Edit','Edit - All lower access, plus the ability to edit information on the system.'),(300,'Add / Edit / Delete','Delete - All lower access, plus the ability to delete information on the system.'),(400,'Site Administrator','Site Administrator - All lower access, plus the ability to add, edit, and remove site users, as well as the ability to edit site settings.'),(500,'Root','Root Administrator - All lower access, plus the ability to add, edit, and remove sites, as well as the ability to assign Site Administrator status to a user.');
UNLOCK TABLES;
DROP TABLE IF EXISTS `activity`;
CREATE TABLE `activity` (
  `activity_id` int(11) NOT NULL auto_increment,
  `data_item_id` int(11) NOT NULL default '0',
  `data_item_type` int(11) NOT NULL default '0',
  `joborder_id` int(11) default NULL,
  `site_id` int(11) NOT NULL default '0',
  `entered_by` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `type` int(11) NOT NULL default '0',
  `notes` text collate utf8_unicode_ci,
  `date_modified` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`activity_id`),
  KEY `IDX_entered_by` (`entered_by`),
  KEY `IDX_site_id` (`site_id`),
  KEY `IDX_type` (`type`),
  KEY `IDX_data_item_type` (`data_item_type`),
  KEY `IDX_type_id` (`data_item_type`,`data_item_id`),
  KEY `IDX_joborder_id` (`joborder_id`),
  KEY `IDX_date_created` (`date_created`),
  KEY `IDX_date_modified` (`date_modified`),
  KEY `IDX_data_item_id_type_site` (`site_id`,`data_item_id`,`data_item_type`),
  KEY `IDX_site_created` (`site_id`,`date_created`),
  KEY `IDX_activity_site_type_created_job` (`site_id`,`data_item_type`,`date_created`,`entered_by`,`joborder_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `activity` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `activity_type`;
CREATE TABLE `activity_type` (
  `activity_type_id` int(11) NOT NULL default '0',
  `short_description` varchar(32) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`activity_type_id`),
  KEY `IDX_activity_type1` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `activity_type` WRITE;
INSERT INTO `activity_type` VALUES (100,'Call'),(200,'Email'),(300,'Meeting'),(400,'Other'),(500,'Call (Talked)'),(600,'Call (LVM)'),(700,'Call (Missed)');
UNLOCK TABLES;
DROP TABLE IF EXISTS `attachment`;
CREATE TABLE `attachment` (
  `attachment_id` int(11) NOT NULL auto_increment,
  `data_item_id` int(11) NOT NULL default '0',
  `data_item_type` int(11) NOT NULL default '0',
  `site_id` int(11) NOT NULL default '0',
  `title` varchar(128) collate utf8_unicode_ci default NULL,
  `original_filename` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `stored_filename` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `content_type` varchar(64) collate utf8_unicode_ci default NULL,
  `resume` int(1) NOT NULL default '0',
  `text` text collate utf8_unicode_ci,
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `profile_image` int(1) default '0',
  `directory_name` varchar(64) collate utf8_unicode_ci default NULL,
  `md5_sum` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  `file_size_kb` int(11) default '0',
  `md5_sum_text` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`attachment_id`),
  KEY `IDX_type_id` (`data_item_type`,`data_item_id`),
  KEY `IDX_data_item_id` (`data_item_id`),
  KEY `IDX_CANDIDATE_MD5_SUM` (`md5_sum`),
  KEY `IDX_site_file_size` (`site_id`,`file_size_kb`),
  KEY `IDX_site_file_size_created` (`site_id`,`file_size_kb`,`date_created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `attachment` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `calendar_event`;
CREATE TABLE `calendar_event` (
  `calendar_event_id` int(11) NOT NULL auto_increment,
  `type` int(11) NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `title` text collate utf8_unicode_ci NOT NULL,
  `all_day` int(1) NOT NULL default '0',
  `data_item_id` int(11) NOT NULL default '-1',
  `data_item_type` int(11) NOT NULL default '-1',
  `entered_by` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `site_id` int(11) NOT NULL default '0',
  `joborder_id` int(11) NOT NULL default '-1',
  `description` text collate utf8_unicode_ci,
  `duration` int(11) NOT NULL default '60',
  `reminder_enabled` int(1) NOT NULL default '0',
  `reminder_email` text collate utf8_unicode_ci,
  `reminder_time` int(11) default '0',
  `public` int(1) NOT NULL default '1',
  PRIMARY KEY  (`calendar_event_id`),
  KEY `IDX_site_id_date` (`site_id`,`date`),
  KEY `IDX_site_data_item_type_id` (`site_id`,`data_item_type`,`data_item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `calendar_event` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `calendar_event_type`;
CREATE TABLE `calendar_event_type` (
  `calendar_event_type_id` int(11) NOT NULL default '0',
  `short_description` varchar(32) collate utf8_unicode_ci NOT NULL default '',
  `icon_image` varchar(128) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`calendar_event_type_id`),
  KEY `IDX_short_description` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `calendar_event_type` WRITE;
INSERT INTO `calendar_event_type` VALUES (100,'Call','images/phone.gif'),(200,'Email','images/email.gif'),(300,'Meeting','images/meeting.gif'),(400,'Interview','images/interview.gif'),(500,'Personal','images/personal.gif'),(600,'Other','');
UNLOCK TABLES;
DROP TABLE IF EXISTS `candidate`;
CREATE TABLE `candidate` (
  `candidate_id` int(11) NOT NULL auto_increment,
  `site_id` int(11) NOT NULL default '0',
  `last_name` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `first_name` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `middle_name` varchar(32) collate utf8_unicode_ci default NULL,
  `phone_home` varchar(40) collate utf8_unicode_ci default NULL,
  `phone_cell` varchar(40) collate utf8_unicode_ci default NULL,
  `phone_work` varchar(40) collate utf8_unicode_ci default NULL,
  `address` text collate utf8_unicode_ci,
  `city` varchar(64) collate utf8_unicode_ci default NULL,
  `state` varchar(64) collate utf8_unicode_ci default NULL,
  `zip` varchar(16) collate utf8_unicode_ci default NULL,
  `source` varchar(128) collate utf8_unicode_ci default NULL,
  `date_available` datetime default NULL,
  `can_relocate` int(1) NOT NULL default '0',
  `notes` text collate utf8_unicode_ci,
  `key_skills` text collate utf8_unicode_ci,
  `current_employer` varchar(128) collate utf8_unicode_ci default NULL,
  `entered_by` int(11) NOT NULL default '0' COMMENT 'Created-by user.',
  `owner` int(11) default NULL,
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `email1` varchar(128) collate utf8_unicode_ci default NULL,
  `email2` varchar(128) collate utf8_unicode_ci default NULL,
  `web_site` varchar(128) collate utf8_unicode_ci default NULL,
  `import_id` int(11) NOT NULL default '0',
  `is_hot` int(1) NOT NULL default '0',
  `eeo_ethnic_type_id` int(11) default '0',
  `eeo_veteran_type_id` int(11) default '0',
  `eeo_disability_status` varchar(5) collate utf8_unicode_ci default '',
  `eeo_gender` varchar(5) collate utf8_unicode_ci default '',
  `desired_pay` varchar(64) collate utf8_unicode_ci default NULL,
  `current_pay` varchar(64) collate utf8_unicode_ci default NULL,
  `is_active` int(1) default '1',
  `is_admin_hidden` int(1) default '0',
  PRIMARY KEY  (`candidate_id`),
  KEY `IDX_first_name` (`first_name`),
  KEY `IDX_last_name` (`last_name`),
  KEY `IDX_phone_home` (`phone_home`),
  KEY `IDX_phone_cell` (`phone_cell`),
  KEY `IDX_phone_work` (`phone_work`),
  KEY `IDX_key_skills` (`key_skills`(255)),
  KEY `IDX_entered_by` (`entered_by`),
  KEY `IDX_owner` (`owner`),
  KEY `IDX_date_created` (`date_created`),
  KEY `IDX_date_modified` (`date_modified`),
  KEY `IDX_site_first_last_modified` (`site_id`,`first_name`,`last_name`,`date_modified`),
  KEY `IDX_site_id_email_1_2` (`site_id`,`email1`(8),`email2`(8))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `candidate` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `candidate_joborder`;
CREATE TABLE `candidate_joborder` (
  `candidate_joborder_id` int(11) NOT NULL auto_increment,
  `candidate_id` int(11) NOT NULL default '0',
  `joborder_id` int(11) NOT NULL default '0',
  `site_id` int(11) NOT NULL default '0',
  `status` int(11) NOT NULL default '0',
  `date_submitted` datetime default NULL,
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `rating_value` int(5) default NULL,
  `added_by` int(11) default NULL,
  PRIMARY KEY  (`candidate_joborder_id`),
  KEY `IDX_candidate_id` (`candidate_id`),
  KEY `IDX_site_id` (`site_id`),
  KEY `IDX_date_submitted` (`date_submitted`),
  KEY `IDX_date_created` (`date_created`),
  KEY `IDX_date_modified` (`date_modified`),
  KEY `IDX_status_special` (`site_id`,`status`),
  KEY `IDX_site_joborder` (`site_id`,`joborder_id`),
  KEY `IDX_joborder_id` (`joborder_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `candidate_joborder` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `candidate_joborder_status`;
CREATE TABLE `candidate_joborder_status` (
  `candidate_joborder_status_id` int(11) NOT NULL default '0',
  `short_description` varchar(32) collate utf8_unicode_ci NOT NULL default '',
  `can_be_scheduled` int(1) NOT NULL default '0',
  `triggers_email` int(1) NOT NULL default '1',
  `is_enabled` int(1) NOT NULL default '1',
  PRIMARY KEY  (`candidate_joborder_status_id`),
  KEY `IDX_short_description` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `candidate_joborder_status` WRITE;
INSERT INTO `candidate_joborder_status` VALUES (100,'No Contact',0,0,1),(200,'Contacted',0,0,1),(300,'Qualifying',0,1,1),(400,'Submitted',0,1,1),(500,'Interviewing',0,1,1),(600,'Offered',0,1,1),(700,'Client Declined',0,0,1),(800,'Placed',0,1,1),(0,'No Status',0,0,1),(650,'Not in Consideration',0,0,1),(250,'Candidate Responded',0,0,1);
UNLOCK TABLES;
DROP TABLE IF EXISTS `candidate_joborder_status_history`;
CREATE TABLE `candidate_joborder_status_history` (
  `candidate_joborder_status_history_id` int(11) NOT NULL auto_increment,
  `candidate_id` int(11) NOT NULL default '0',
  `joborder_id` int(11) NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `status_from` int(11) NOT NULL default '0',
  `status_to` int(11) NOT NULL default '0',
  `site_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`candidate_joborder_status_history_id`),
  KEY `IDX_site_id` (`site_id`),
  KEY `IDX_status_to` (`status_to`),
  KEY `IDX_status_to_site_id` (`status_to`,`site_id`),
  KEY `IDX_candidate_joborder_status_to_site` (`candidate_id`,`joborder_id`,`status_to`,`site_id`),
  KEY `IDX_joborder_site` (`joborder_id`,`site_id`),
  KEY `IDX_site_joborder_status_to` (`site_id`,`joborder_id`,`status_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `candidate_joborder_status_history` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `candidate_jobordrer_status_type`;
CREATE TABLE `candidate_jobordrer_status_type` (
  `candidate_status_type_id` int(11) NOT NULL default '0',
  `short_description` varchar(32) collate utf8_unicode_ci NOT NULL default '',
  `can_be_scheduled` int(1) NOT NULL default '0',
  PRIMARY KEY  (`candidate_status_type_id`),
  KEY `IDX_short_description` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `candidate_jobordrer_status_type` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `candidate_source`;
CREATE TABLE `candidate_source` (
  `source_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci default NULL,
  `site_id` int(11) default NULL,
  `date_created` datetime default NULL,
  PRIMARY KEY  (`source_id`),
  KEY `siteID` (`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `candidate_source` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `career_portal_questionnaire`;
CREATE TABLE `career_portal_questionnaire` (
  `career_portal_questionnaire_id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `site_id` int(11) NOT NULL default '0',
  `description` varchar(255) default NULL,
  `is_active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`career_portal_questionnaire_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `career_portal_questionnaire` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `career_portal_questionnaire_answer`;
CREATE TABLE `career_portal_questionnaire_answer` (
  `career_portal_questionnaire_answer_id` int(11) NOT NULL auto_increment,
  `career_portal_questionnaire_question_id` int(11) NOT NULL,
  `career_portal_questionnaire_id` int(11) NOT NULL,
  `text` varchar(255) NOT NULL default '',
  `action_source` varchar(128) default NULL,
  `action_notes` text,
  `action_is_hot` tinyint(1) default '0',
  `action_is_active` tinyint(1) default '0',
  `action_can_relocate` tinyint(1) default '0',
  `action_key_skills` varchar(255) default NULL,
  `position` int(4) NOT NULL default '0',
  `site_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`career_portal_questionnaire_answer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `career_portal_questionnaire_answer` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `career_portal_questionnaire_history`;
CREATE TABLE `career_portal_questionnaire_history` (
  `career_portal_questionnaire_history_id` int(11) NOT NULL auto_increment,
  `site_id` int(11) NOT NULL default '0',
  `candidate_id` int(11) NOT NULL default '0',
  `question` varchar(255) NOT NULL default '',
  `answer` varchar(255) NOT NULL default '',
  `questionnaire_title` varchar(255) NOT NULL default '',
  `questionnaire_description` varchar(255) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`career_portal_questionnaire_history_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `career_portal_questionnaire_history` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `career_portal_questionnaire_question`;
CREATE TABLE `career_portal_questionnaire_question` (
  `career_portal_questionnaire_question_id` int(11) NOT NULL auto_increment,
  `career_portal_questionnaire_id` int(11) NOT NULL,
  `text` varchar(255) NOT NULL default '',
  `minimum_length` int(11) default NULL,
  `maximum_length` int(11) default NULL,
  `required` tinyint(1) NOT NULL default '0',
  `position` int(4) NOT NULL default '0',
  `site_id` int(11) NOT NULL default '0',
  `type` int(11) NOT NULL default '0',
  PRIMARY KEY  (`career_portal_questionnaire_question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `career_portal_questionnaire_question` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `career_portal_template`;
CREATE TABLE `career_portal_template` (
  `career_portal_template_id` int(11) NOT NULL auto_increment,
  `career_portal_name` varchar(255) default NULL,
  `setting` varchar(128) NOT NULL default '',
  `value` text,
  PRIMARY KEY  (`career_portal_template_id`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;

LOCK TABLES `career_portal_template` WRITE;
INSERT INTO `career_portal_template` VALUES (46,'CATS 2.0','Left',''),(47,'CATS 2.0','Footer','</div>'),(48,'CATS 2.0','Header','<div id=\"container\">\r\n	<div id=\"logo\"><img src=\"images/careers_cats.gif\" alt=\"IMAGE: CATS Applicant Tracking System Careers Page\" /></div>\r\n    <div id=\"actions\">\r\n    	<h2>Shortcuts:</h2>\r\n        <a href=\"index.php\" onmouseover=\"buttonMouseOver(\'returnToMain\',true);\" onmouseout=\"buttonMouseOver(\'returnToMain\',false);\"><img src=\"images/careers_return.gif\" id=\"returnToMain\" alt=\"IMAGE: Return to Main\" /></a>\r\n<a href=\"<rssURL>\" onmouseover=\"buttonMouseOver(\'rssFeed\',true);\" onmouseout=\"buttonMouseOver(\'rssFeed\',false);\"><img src=\"images/careers_rss.gif\" id=\"rssFeed\" alt=\"IMAGE: RSS Feed\" /></a>\r\n        <a href=\"index.php?m=careers&p=showAll\" onmouseover=\"buttonMouseOver(\'showAllJobs\',true);\" onmouseout=\"buttonMouseOver(\'showAllJobs\',false);\"><img src=\"images/careers_show.gif\" id=\"showAllJobs\" alt=\"IMAGE: Show All Jobs\" /></a>\r\n    </div>'),(49,'CATS 2.0','Content - Main','<div id=\"careerContent\">\r\n        <h1>Available Openings at <siteName></h1>\r\n        <div id=\"descriptive\">\r\n               <p>Change your life today by becoming an integral part of our winning team.</p>\r\n               <p>If you are interested, we invite you to view the <a href=\"index.php?m=careers&p=showAll\">current opening positions</a> at our company.</p>\r\n        </div>\r\n        <div id=\"detailsTools\">\r\n        	<h2>Perform an action:</h2>\r\n        	<ul>\r\n                    <li><a href=\"\">Visit our website</a></li>\r\n                </ul>\r\n        </div>\r\n</div>'),(50,'CATS 2.0','CSS','table.sortable\r\n{\r\ntext-align:left;\r\nempty-cells: show;\r\nwidth: 940px;\r\n}\r\ntd\r\n{\r\npadding:5px;\r\n}\r\ntr.rowHeading\r\n{\r\n background: #e0e0e0; border: 1px solid #cccccc; border-left: none; border-right: none;\r\n}\r\ntr.oddTableRow\r\n{\r\nbackground: #ebebeb; \r\n}\r\ntr.evenTableRow\r\n{\r\n background: #ffffff; \r\n}\r\na.sortheader:hover,\r\na.sortheader:link,\r\na.sortheader:visited\r\n{\r\ncolor:#000;\r\n}\r\n\r\nbody, html { margin: 0; padding: 0; background: #ffffff; font: normal 12px/14px Arial, Helvetica, sans-serif; color: #000000; }\r\n#container { margin: 0 auto; padding: 0; width: 940px; height: auto; }\r\n#logo { float: left; margin: 0; }\r\n	#logo img { width: 424px; height: 103px; }\r\n#actions { float: right; margin: 0; width: 310px; height: 100px; background: #efefef; border: 1px solid #cccccc; }\r\n	#actions img { float: left; margin: 2px 6px 2px 15px; width: 130px; height: 25px; }\r\n#footer { clear: both; margin: 20px auto 0 auto; width: 150px; }\r\n	#footer img { width: 137px; height: 38px; }\r\n\r\na:link, a:active { color: #1763b9; }\r\na:hover { color: #c75a01; }\r\na:visited { color: #333333; }\r\nimg { border: none; }\r\n\r\nh1 { margin: 0 0 10px 0; font: bold 18px Arial, Helvetica, sans-serif; }\r\nh2 { margin: 8px 0 8px 15px; font: bold 14px Arial, Helvetica, sans-serif; }\r\nh3 { margin: 0; font: bold 14px Arial, Helvetica, sans-serif; }\r\np { font: normal 12px Arial, Helvetica, sans-serif; }\r\np.instructions { margin: 0 0 0 10px; font: italic 12px Arial, Helvetica, sans-serif; color: #666666; }\r\n\r\n\r\n/* CONTENTS ON PAGE SPECS */\r\n#careerContent { clear: both; padding: 15px 0 0 0; }\r\n\r\n	\r\n/* DISPLAY JOB DETAILS */\r\n#detailsTable { width: 400px; }\r\n	#detailsTable td.detailsHeader { width: 30%; }\r\ndiv#descriptive { float: left; width: 585px; }\r\ndiv#detailsTools { float: right; padding: 0 0 8px 0; width: 280px; background: #ffffff; border: 1px solid #cccccc; }\r\n	div#detailsTools img { margin: 2px 6px 5px 15px;  }\r\n\r\n/* DISPLAY APPLICATION FORM */\r\ndiv.applyBoxLeft, div.applyBoxRight { width: 450px; height: 470px; background: #f9f9f9; border: 1px solid #cccccc; border-top: none; }\r\ndiv.applyBoxLeft { float: left; margin: 0 10px 0 0; }\r\ndiv.applyBoxRight { float: right; margin: 0 0 0 10px; }\r\n	div.applyBoxLeft div, div.applyBoxRight div { margin: 0 0 5px 0; padding: 3px 10px; background: #efefef; border-top: 1px solid #cccccc; border-bottom: 1px solid #cccccc; }\r\n	div.applyBoxLeft table, div.applyBoxRight table { margin: 0 auto; width: 420px; }\r\n	div.applyBoxLeft table td, div.applyBoxRight table td { padding: 3px; vertical-align: top; }\r\n		td.label { text-align: right; width: 110px; }\r\n        form#applyToJobForm {  }\r\n	form#applyToJobForm label { font-weight: bold; }\r\n	form#applyToJobForm input.inputBoxName, form#applyToJobForm input.inputBoxNormal { width: 285px; height: 15px; }\r\n        form#applyToJobForm input.submitButton { width: 197px; height: 27px; background: url(\'images/careers_submit.gif\') no-repeat; }\r\n\r\n        form#applyToJobForm input.submitButtonDown { width: 197px; height: 27px; background: url(\'images/careers_submit-o.gif\') no-repeat; }\r\n	form#applyToJobForm textarea { margin: 8px 0 0 0; width: 410px; height: 170px; }\r\n	form#applyToJobForm textarea.inputBoxArea{ width: 285px; height: 70px; }\r\n\r\n'),(51,'CATS 2.0','Content - Search Results','<div id=\"careerContent\">\r\n        <h1>Current Available Openings, Recently Posted Jobs: <numberOfSearchResults></h1>\r\n<searchResultsTable>\r\n    </div>'),(52,'CATS 2.0','Content - Questionnaire','<div id=\"careerContent\">\r\n<questionnaire>\r\n<br /><br />\r\n<div style=\"text-align: right;\">\r\n<submit value=\"Continue\">\r\n</div>\r\n</div>'),(53,'CATS 2.0','Content - Job Details','<div id=\"careerContent\">\r\n        <h1>Position Details: <title></h1>\r\n        <table id=\"detailsTable\">\r\n            <tr>\r\n                <td class=\"detailsHeader\"><strong>Location:</strong></td>\r\n                <td><city>, <state></td>\r\n			</tr>\r\n			<tr>\r\n                <td class=\"detailsHeader\"><strong>Openings:</strong></td>\r\n                <td><openings></td>\r\n			</tr>\r\n            <tr>\r\n                <td class=\"detailsHeader\"><strong>Salary Range:</strong></td>\r\n                <td><salary></td>\r\n            </tr>\r\n        </table>\r\n        <div id=\"descriptive\">\r\n            <p><strong>Description:</strong></p>\r\n            <description>\r\n		</div>\r\n        <div id=\"detailsTools\">\r\n        	<h2>Perform an action:</h2>\r\n        	<a-applyToJob onmouseover=\"buttonMouseOver(\'applyToPosition\',true);\" onmouseout=\"buttonMouseOver(\'applyToPosition\',false);\"><img src=\"images/careers_apply.gif\" id=\"applyToPosition\" alt=\"IMAGE: Apply to Position\" /></a>\r\n        </div>\r\n    </div>'),(54,'CATS 2.0','Content - Thanks for your Submission','<div id=\"careerContent\">\r\n            <h1>Application Submitted For: Senior Software Engineer</h1>\r\n            <div id=\"descriptive\">\r\n                <p>Please check your email inbox &#8212; You should receive an email confirmation of your application.</p>\r\n                <p>Thank you for submitting your application to us. We will review it shortly and make contact with you soon.</p>\r\n                </div>\r\n			<div id=\"detailsTools\">\r\n                <h2>Perform an action:</h2>\r\n                <ul>\r\n                	<li><a href=\"\">Visit our website</a></li>\r\n		</ul>\r\n        	</div>\r\n    </div>'),(55,'CATS 2.0','Content - Apply for Position','<div id=\"careerContent\">\r\n        <h1>Applying to: Senior Software Engineer</h1>\r\n        <div class=\"applyBoxLeft\">\r\n            <div><h3>1. Import Resume (or CV) and Populate Fields</h3></div>\r\n            <table>\r\n                <form id=\"applyNowForm\" action=\"\">\r\n                <tr>\r\n                    <td>\r\n                      \r\n                    <input-resumeUploadPreview>\r\n                    </td>\r\n                </tr>\r\n            </form></table>\r\n            <br />\r\n\r\n            <div><h3>2. Tell us about yourself</h3></div>\r\n            <p class=\"instructions\">All fields marked with asterisk (*) are required.</p>\r\n            <table>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"firstNameLabel\" for=\"firstName\">*First Name:</label></td>\r\n                    <td><input-firstName></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"lastNameLabel\" for=\"lastName\">*Last Name:</label></td>\r\n                    <td><input-lastName></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"emailLabel\" for=\"email\">*Email Adddress:</label></td>\r\n                    <td><input-email></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"emailConfirmLabel\" for=\"emailconfirm\">*Confirm Email:</label></td>\r\n                    <td><input-emailconfirm></td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n       \r\n        <div class=\"applyBoxRight\">\r\n            <div><h3>3. How may we contact you?</h3></div>\r\n            <table>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"homePhoneLabel\" for=\"homePhone\">Home Phone:</label></td>\r\n                    <td><input-phone-home></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"mobilePhoneLabel\" for=\"mobilePhone\">Mobile Phone:</label></td>\r\n                    <td><input type=\"text\" name=\"mobilePhone\" id=\"mobilePhone\" class=\"inputbox\" value=\"\" /></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"workPhoneLabel\" for=\"workPhone\">Work Phone:</label></td>\r\n                    <td><input-phone></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"bestTimeLabel\" for=\"bestTime\">*Best time to call:</label></td>\r\n                    <td><input type=\"text\" name=\"bestTime\" id=\"bestTime\" class=\"inputbox\" value=\"\"  /></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"mailingAddressLabel\" for=\"mailingAddress\">Mailing Address:</label></td>\r\n                    <td><input-address></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"cityProvinceLabel\" for=\"cityProvince\">*City/Province:</label></td>\r\n                    <td><input-city></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"stateCountryLabel\" for=\"stateCountry\">*State/Country:</label></td>\r\n                    <td><input-state></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"zipPostalLabel\" for=\"zipPostal\">*Zip/Postal Code:</label></td>\r\n                    <td><input-zip></td>\r\n                </tr>\r\n            </table>\r\n            <br />\r\n            <div><h3>4. Additional Information</h3></div>\r\n            <table>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"keySkillsLabel\" for=\"keySkills\">*Key Skills:</label></td>\r\n                    <td><input-keySkills></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"referralSourceLabel\" for=\"referralSource\">*Referral Source:</label></td>\r\n                    <td><input type=\"text\" name=\"referralSource\" id=\"referralSource\" class=\"inputbox\" value=\"\" /></td>\r\n                </tr>\r\n                <tr>\r\n                    <td>&nbsp;</td>\r\n                    <td><a href=\"javascript:void(0);\" onclick=\"applyToJobForm.submit();\" onmouseover=\"buttonMouseOver(\'submitApplicationNow\',true);\" onmouseout=\"buttonMouseOver(\'submitApplicationNow\',false);\"><img src=\"images/careers_submit.gif\" id=\"submitApplicationNow\" alt=\"Submit Application Now\" /></a></td>\r\n                </tr>\r\n            </table>\r\n               </div>\r\n    </div>');
UNLOCK TABLES;
DROP TABLE IF EXISTS `career_portal_template_site`;
CREATE TABLE `career_portal_template_site` (
  `career_portal_template_id` int(11) NOT NULL auto_increment,
  `career_portal_name` varchar(255) default NULL,
  `site_id` int(11) NOT NULL,
  `setting` varchar(128) NOT NULL default '',
  `value` text,
  PRIMARY KEY  (`career_portal_template_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `career_portal_template_site` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `company`;
CREATE TABLE `company` (
  `company_id` int(11) NOT NULL auto_increment,
  `site_id` int(11) NOT NULL default '0',
  `billing_contact` int(11) default NULL,
  `name` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `address` text collate utf8_unicode_ci,
  `city` varchar(64) collate utf8_unicode_ci default NULL,
  `state` varchar(64) collate utf8_unicode_ci default NULL,
  `zip` varchar(16) collate utf8_unicode_ci default NULL,
  `phone1` varchar(40) collate utf8_unicode_ci default NULL,
  `phone2` varchar(40) collate utf8_unicode_ci default NULL,
  `url` varchar(128) collate utf8_unicode_ci default NULL,
  `key_technologies` text collate utf8_unicode_ci,
  `notes` text collate utf8_unicode_ci,
  `entered_by` int(11) default NULL,
  `owner` int(11) default NULL,
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `is_hot` int(1) default NULL,
  `fax_number` varchar(40) collate utf8_unicode_ci default NULL,
  `import_id` int(11) default NULL,
  `default_company` int(1) NOT NULL default '0',
  PRIMARY KEY  (`company_id`),
  KEY `IDX_site_id` (`site_id`),
  KEY `IDX_name` (`name`),
  KEY `IDX_key_technologies` (`key_technologies`(255)),
  KEY `IDX_entered_by` (`entered_by`),
  KEY `IDX_owner` (`owner`),
  KEY `IDX_date_created` (`date_created`),
  KEY `IDX_date_modified` (`date_modified`),
  KEY `IDX_is_hot` (`is_hot`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `company` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `company_department`;
CREATE TABLE `company_department` (
  `company_department_id` int(11) NOT NULL auto_increment,
  `name` varchar(128) collate utf8_unicode_ci default NULL,
  `company_id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(11) default NULL,
  PRIMARY KEY  (`company_department_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `company_department` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `contact`;
CREATE TABLE `contact` (
  `contact_id` int(11) NOT NULL auto_increment,
  `company_id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL default '0',
  `last_name` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `first_name` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `title` varchar(128) collate utf8_unicode_ci default NULL,
  `email1` varchar(128) collate utf8_unicode_ci default NULL,
  `email2` varchar(128) collate utf8_unicode_ci default NULL,
  `phone_work` varchar(40) collate utf8_unicode_ci default NULL,
  `phone_cell` varchar(40) collate utf8_unicode_ci default NULL,
  `phone_other` varchar(40) collate utf8_unicode_ci default NULL,
  `address` text collate utf8_unicode_ci,
  `city` varchar(64) collate utf8_unicode_ci default NULL,
  `state` varchar(64) collate utf8_unicode_ci default NULL,
  `zip` varchar(16) collate utf8_unicode_ci default NULL,
  `is_hot` int(1) default NULL,
  `notes` text collate utf8_unicode_ci,
  `entered_by` int(11) NOT NULL default '0',
  `owner` int(11) default NULL,
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `left_company` int(1) NOT NULL default '0',
  `import_id` int(11) NOT NULL default '0',
  `company_department_id` int(11) NOT NULL,
  `reports_to` int(11) default '-1',
  PRIMARY KEY  (`contact_id`),
  KEY `IDX_site_id` (`site_id`),
  KEY `IDX_first_name` (`first_name`),
  KEY `IDX_last_name` (`last_name`),
  KEY `IDX_client_id` (`company_id`),
  KEY ` IDX_title` (`title`),
  KEY `IDX_owner` (`owner`),
  KEY `IDX_date_created` (`date_created`),
  KEY `IDX_date_modified` (`date_modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `contact` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `data_item_type`;
CREATE TABLE `data_item_type` (
  `data_item_type_id` int(11) NOT NULL default '0',
  `short_description` varchar(32) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`data_item_type_id`),
  KEY `IDX_short_description` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `data_item_type` WRITE;
INSERT INTO `data_item_type` VALUES (100,'Candidate'),(200,'Company'),(300,'Contact'),(400,'Job Order');
UNLOCK TABLES;
DROP TABLE IF EXISTS `eeo_ethnic_type`;
CREATE TABLE `eeo_ethnic_type` (
  `eeo_ethnic_type_id` int(11) NOT NULL auto_increment,
  `type` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`eeo_ethnic_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

LOCK TABLES `eeo_ethnic_type` WRITE;
INSERT INTO `eeo_ethnic_type` VALUES (1,'American Indian'),(2,'Asian or Pacific Islander'),(3,'Hispanic or Latino'),(4,'Non-Hispanic Black'),(5,'Non-Hispanic White');
UNLOCK TABLES;
DROP TABLE IF EXISTS `eeo_veteran_type`;
CREATE TABLE `eeo_veteran_type` (
  `eeo_veteran_type_id` int(11) NOT NULL auto_increment,
  `type` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`eeo_veteran_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

LOCK TABLES `eeo_veteran_type` WRITE;
INSERT INTO `eeo_veteran_type` VALUES (1,'No Veteran Status'),(2,'Eligible Veteran'),(3,'Disabled Veteran'),(4,'Eligible and Disabled');
UNLOCK TABLES;
DROP TABLE IF EXISTS `email_history`;
CREATE TABLE `email_history` (
  `email_history_id` int(11) NOT NULL auto_increment,
  `from_address` varchar(128) collate utf8_unicode_ci NOT NULL default '',
  `recipients` text collate utf8_unicode_ci NOT NULL,
  `text` text collate utf8_unicode_ci,
  `user_id` int(11) default NULL,
  `site_id` int(11) NOT NULL default '0',
  `date` datetime default NULL,
  PRIMARY KEY  (`email_history_id`),
  KEY `IDX_site_id` (`site_id`),
  KEY `IDX_date` (`date`),
  KEY `IDX_user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `email_history` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `email_template`;
CREATE TABLE `email_template` (
  `email_template_id` int(11) NOT NULL auto_increment,
  `text` text collate utf8_unicode_ci,
  `allow_substitution` int(1) NOT NULL default '0',
  `site_id` int(11) NOT NULL default '0',
  `tag` varchar(255) collate utf8_unicode_ci default NULL,
  `title` varchar(255) collate utf8_unicode_ci default NULL,
  `possible_variables` text collate utf8_unicode_ci,
  `disabled` int(1) default '0',
  PRIMARY KEY  (`email_template_id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `email_template` WRITE;
INSERT INTO `email_template` VALUES (20,'* Auto generated message. Please DO NOT reply *\r\n%DATETIME%\r\n\r\nDear %CANDFULLNAME%,\r\n\r\nThis E-Mail is a notification that your status in our database has been changed for the position %JBODTITLE% (%JBODCLIENT%).\r\n\r\nYour previous status was <B>%CANDPREVSTATUS%</B>.\r\nYour new status is <B>%CANDSTATUS%</B>.\r\n\r\nTake care,\r\n%USERFULLNAME%\r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_STATUSCHANGE','Status Changed (Sent to Candidate)','%CANDSTATUS%%CANDOWNER%%CANDFIRSTNAME%%CANDFULLNAME%%CANDPREVSTATUS%%JBODCLIENT%%JBODTITLE%',0),(28,'%DATETIME%\r\n\r\nDear %CANDOWNER%,\r\n\r\nThis E-Mail is a notification that a Candidate has been assigned to you.\r\n\r\nCandidate Name: %CANDFULLNAME%\r\nCandidate URL: %CANDCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_OWNERSHIPASSIGNCANDIDATE','Candidate Assigned (Sent to Assigned Recruiter)','%CANDOWNER%%CANDFIRSTNAME%%CANDFULLNAME%%CANDCATSURL%',0),(27,'%DATETIME%\r\n\r\nDear %JBODOWNER%,\r\n\r\nThis E-Mail is a notification that a Job Order has been assigned to you.\r\n\r\nJob Order Title: %JBODTITLE%\r\nJob Order Client: %JBODCLIENT%\r\nJob Order ID: %JBODID%\r\nJob Order URL: %JBODCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_OWNERSHIPASSIGNJOBORDER','Job Order Assigned (Sent to Assigned Recruiter)','%JBODOWNER%%JBODTITLE%%JBODCLIENT%%JBODCATSURL%%JBODID%',0),(26,'%DATETIME%\r\n\r\nDear %CONTOWNER%,\r\n\r\nThis E-Mail is a notification that a Contact has been assigned to you.\r\n\r\nContact Name: %CONTFULLNAME%\r\nContact Client: %CONTCLIENTNAME%\r\nContact URL: %CONTCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_OWNERSHIPASSIGNCONTACT','Contact Assigned (Sent to Assigned Recruiter)','%CONTOWNER%%CONTFIRSTNAME%%CONTFULLNAME%%CONTCLIENTNAME%%CONTCATSURL%',0),(25,'%DATETIME%\r\n\r\nDear %CLNTOWNER%,\r\n\r\nThis E-Mail is a notification that a Client has been assigned to you.\r\n\r\nClient Name: %CLNTNAME%\r\nClient URL %CLNTCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_OWNERSHIPASSIGNCLIENT','Client Assigned (Sent to Assigned Recruiter)','%CLNTOWNER%%CLNTNAME%%CLNTCATSURL%',0),(30,'* This is an auto-generated message. Please do not reply. *\r\n%DATETIME%\r\n\r\nDear %CANDFULLNAME%,\r\n\r\nThank you for applying to the %JBODTITLE% position with our online career portal! Your application has been entered into our system and someone will review it shortly.\r\n\r\n--\r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_CANDIDATEAPPLY','Candidate Application Received (Sent to Candidate using Career Portal)','%CANDFIRSTNAME%%CANDFULLNAME%%JBODCLIENT%%JBODTITLE%%JBODOWNER%',0),(31,'%DATETIME%\r\n\r\nDear %JBODOWNER%,\r\n\r\nThis e-mail is a notification that a candidate has applied to your job order through the online candidate portal.\r\n\r\nJob Order: %JBODTITLE%\r\nCandidate Name: %CANDFULLNAME%\r\nCandidate URL: %CANDCATSURL%\r\nJob Order URL: %JBODCATSURL%\r\n\r\n--\r\nCATS\r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_CANDIDATEPORTALNEW','Candidate Application Received (Sent to Owner of Job Order from Career Portal)','%CANDFIRSTNAME%%CANDFULLNAME%%JBODOWNER%%JBODTITLE%%JBODCLIENT%%JBODCATSURL%%JBODID%%CANDCATSURL%',0);
UNLOCK TABLES;
DROP TABLE IF EXISTS `extension_statistics`;
CREATE TABLE `extension_statistics` (
  `extension_statistics_id` int(11) NOT NULL auto_increment,
  `extension` varchar(128) NOT NULL default '',
  `action` varchar(128) NOT NULL default '',
  `user` varchar(128) NOT NULL default '',
  `date` date default NULL,
  PRIMARY KEY  (`extension_statistics_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `extension_statistics` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `extra_field`;
CREATE TABLE `extra_field` (
  `extra_field_id` int(11) NOT NULL auto_increment,
  `data_item_id` int(11) default '0',
  `field_name` varchar(255) default NULL,
  `value` text,
  `import_id` int(11) default NULL,
  `site_id` int(11) default '0',
  `data_item_type` int(11) default '0',
  PRIMARY KEY  (`extra_field_id`),
  KEY `assoc_id` (`data_item_id`),
  KEY `IDX_site_id` (`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `extra_field` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `extra_field_settings`;
CREATE TABLE `extra_field_settings` (
  `extra_field_settings_id` int(11) NOT NULL auto_increment,
  `field_name` varchar(255) collate utf8_unicode_ci default NULL,
  `import_id` int(11) default NULL,
  `site_id` int(11) NOT NULL default '0',
  `date_created` datetime default NULL,
  `data_item_type` int(11) default '0',
  `extra_field_type` int(11) NOT NULL default '1',
  `extra_field_options` text collate utf8_unicode_ci,
  `position` int(4) NOT NULL default '0',
  PRIMARY KEY  (`extra_field_settings_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `extra_field_settings` WRITE;
INSERT INTO `extra_field_settings` VALUES (1,'AdminUser',NULL,180,'2005-06-01 00:00:00',200,1,NULL,1),(2,'UnixName',NULL,180,'2005-06-01 00:00:00',200,1,NULL,2),(3,'BillingNotes',NULL,180,'2005-06-01 00:00:00',200,1,NULL,3),(4,'IPAddress',NULL,180,'2005-06-01 00:00:00',300,1,NULL,4);
UNLOCK TABLES;
DROP TABLE IF EXISTS `feedback`;
CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) default NULL,
  `site_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `subject` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `reply_to_address` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `reply_to_name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `feedback` text collate utf8_unicode_ci NOT NULL,
  `archived` int(1) NOT NULL default '0',
  PRIMARY KEY  (`feedback_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `feedback` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `history`;
CREATE TABLE `history` (
  `history_id` int(11) NOT NULL auto_increment,
  `data_item_type` int(11) default NULL,
  `data_item_id` int(11) default NULL,
  `the_field` varchar(64) collate utf8_unicode_ci default NULL,
  `previous_value` text collate utf8_unicode_ci,
  `new_value` text collate utf8_unicode_ci,
  `description` varchar(192) collate utf8_unicode_ci default NULL,
  `set_date` datetime default NULL,
  `entered_by` int(11) default NULL,
  `site_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`history_id`),
  KEY `IDX_DATA_ENTERED_BY` (`entered_by`),
  KEY `IDX_data_item_id_type_site` (`data_item_id`,`data_item_type`,`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `history` WRITE;
/*!40000 ALTER TABLE `history` DISABLE KEYS */;
/*!40000 ALTER TABLE `history` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `http_log`;
CREATE TABLE `http_log` (
  `log_id` int(11) NOT NULL auto_increment,
  `site_id` int(11) NOT NULL,
  `remote_addr` char(16) NOT NULL,
  `http_user_agent` varchar(255) default NULL,
  `script_filename` varchar(255) default NULL,
  `request_method` varchar(16) default NULL,
  `query_string` varchar(255) default NULL,
  `request_uri` varchar(255) default NULL,
  `script_name` varchar(255) default NULL,
  `log_type` int(11) NOT NULL,
  `date` datetime default '0000-00-00 00:00:00',
  PRIMARY KEY  (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `http_log` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `http_log_types`;
CREATE TABLE `http_log_types` (
  `log_type_id` int(11) NOT NULL,
  `name` varchar(16) NOT NULL,
  `description` varchar(255) default NULL,
  `default_log_type` tinyint(1) unsigned zerofill NOT NULL default '0',
  PRIMARY KEY  (`log_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `http_log_types` WRITE;
INSERT INTO `http_log_types` VALUES (1,'XML','XML Job Feed',0);
UNLOCK TABLES;
DROP TABLE IF EXISTS `import`;
CREATE TABLE `import` (
  `import_id` int(11) NOT NULL auto_increment,
  `module_name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `reverted` int(1) NOT NULL default '0',
  `site_id` int(11) NOT NULL default '0',
  `import_errors` text collate utf8_unicode_ci,
  `added_lines` int(11) default NULL,
  `date_created` datetime default NULL,
  PRIMARY KEY  (`import_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `import` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `installtest`;
CREATE TABLE `installtest` (
  `id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `installtest` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `joborder`;
CREATE TABLE `joborder` (
  `joborder_id` int(11) NOT NULL auto_increment,
  `recruiter` int(11) default NULL,
  `contact_id` int(11) default NULL,
  `company_id` int(11) default NULL,
  `entered_by` int(11) NOT NULL default '0',
  `owner` int(11) default NULL,
  `site_id` int(11) NOT NULL default '0',
  `client_job_id` varchar(32) collate utf8_unicode_ci default NULL,
  `title` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `description` text collate utf8_unicode_ci,
  `notes` text collate utf8_unicode_ci,
  `type` varchar(64) collate utf8_unicode_ci NOT NULL default 'C',
  `duration` varchar(64) collate utf8_unicode_ci default NULL,
  `rate_max` varchar(255) collate utf8_unicode_ci default NULL,
  `salary` varchar(64) collate utf8_unicode_ci default NULL,
  `status` varchar(16) collate utf8_unicode_ci NOT NULL default 'Active',
  `is_hot` int(1) NOT NULL default '0',
  `openings` int(11) default NULL,
  `city` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `state` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `start_date` datetime default NULL,
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `public` int(1) NOT NULL default '0',
  `company_department_id` int(11) default NULL,
  `is_admin_hidden` int(1) default '0',
  `openings_available` int(11) default '0',
  PRIMARY KEY  (`joborder_id`),
  KEY `IDX_recruiter` (`recruiter`),
  KEY `IDX_title` (`title`),
  KEY `IDX_client_id` (`company_id`),
  KEY `IDX_start_date` (`start_date`),
  KEY `IDX_contact_id` (`contact_id`),
  KEY `IDX_is_hot` (`is_hot`),
  KEY `IDX_jopenings` (`openings`),
  KEY `IDX_owner` (`owner`),
  KEY `IDX_entered_by` (`entered_by`),
  KEY `IDX_date_created` (`date_created`),
  KEY `IDX_date_modified` (`date_modified`),
  KEY `IDX_site_id_status` (`site_id`,`status`(8))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `joborder` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `module_schema`;
CREATE TABLE `module_schema` (
  `module_schema_id` int(11) NOT NULL auto_increment,
  `name` varchar(64) collate utf8_unicode_ci default NULL,
  `version` int(11) default NULL,
  PRIMARY KEY  (`module_schema_id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `module_schema` WRITE;
INSERT INTO `module_schema` VALUES (1,'activity',0),(2,'attachments',0),(3,'calendar',0),(4,'candidates',0),(5,'careers',0),(6,'companies',0),(7,'contacts',0),(8,'export',0),(9,'extension-statistics',1),(10,'graphs',0),(11,'home',0),(12,'import',0),(13,'install',342),(14,'joborders',0),(15,'lists',0),(16,'login',0),(17,'queue',0),(18,'reports',0),(19,'rss',0),(20,'settings',0),(21,'tests',0),(22,'toolbar',0),(23,'wizard',0),(24,'xml',0);
UNLOCK TABLES;
DROP TABLE IF EXISTS `mru`;
CREATE TABLE `mru` (
  `mru_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) default NULL,
  `site_id` int(11) NOT NULL default '0',
  `data_item_type` int(11) NOT NULL default '0',
  `data_item_text` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `url` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`mru_id`),
  KEY `IDX_user_site` (`user_id`,`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `mru` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `queue`;
CREATE TABLE `queue` (
  `queue_id` int(11) NOT NULL auto_increment,
  `site_id` int(11) NOT NULL,
  `task` varchar(125) NOT NULL,
  `args` text,
  `priority` tinyint(2) NOT NULL default '5' COMMENT '1-5, 1 is highest priority',
  `date_created` datetime NOT NULL,
  `date_timeout` datetime NOT NULL,
  `date_completed` datetime default NULL,
  `locked` tinyint(1) unsigned NOT NULL default '0',
  `error` tinyint(1) unsigned default '0',
  `response` varchar(255) default NULL,
  PRIMARY KEY  (`queue_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `queue` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `saved_list`;
CREATE TABLE `saved_list` (
  `saved_list_id` int(11) NOT NULL auto_increment,
  `description` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `data_item_type` int(11) NOT NULL default '0',
  `site_id` int(11) NOT NULL default '0',
  `is_dynamic` int(1) default '0',
  `datagrid_instance` varchar(64) collate utf8_unicode_ci default '',
  `parameters` text collate utf8_unicode_ci,
  `created_by` int(11) default '0',
  `number_entries` int(11) default '0',
  `date_created` datetime default NULL,
  `date_modified` datetime default NULL,
  PRIMARY KEY  (`saved_list_id`),
  KEY `IDX_data_item_type` (`data_item_type`),
  KEY `IDX_description` (`description`),
  KEY `IDX_site_id` (`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `saved_list` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `saved_list_entry`;
CREATE TABLE `saved_list_entry` (
  `saved_list_entry_id` int(11) NOT NULL auto_increment,
  `saved_list_id` int(11) NOT NULL,
  `data_item_type` int(11) NOT NULL default '0',
  `data_item_id` int(11) NOT NULL default '0',
  `site_id` int(11) NOT NULL default '0',
  `date_created` datetime default NULL,
  PRIMARY KEY  (`saved_list_entry_id`),
  KEY `IDX_type_id` (`data_item_type`,`data_item_id`),
  KEY `IDX_data_item_type` (`data_item_type`),
  KEY `IDX_data_item_id` (`data_item_id`),
  KEY `IDX_hot_list_id` (`saved_list_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `saved_list_entry` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `saved_search`;
CREATE TABLE `saved_search` (
  `search_id` int(11) NOT NULL auto_increment,
  `data_item_text` text collate utf8_unicode_ci,
  `url` text collate utf8_unicode_ci,
  `is_custom` int(1) default NULL,
  `data_item_type` int(11) default NULL,
  `user_id` int(11) default NULL,
  `site_id` int(11) default NULL,
  `date_created` datetime default NULL,
  PRIMARY KEY  (`search_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `saved_search` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `settings_id` int(11) NOT NULL auto_increment,
  `setting` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `value` varchar(255) collate utf8_unicode_ci default NULL,
  `site_id` int(11) NOT NULL default '0',
  `settings_type` int(11) default '0',
  PRIMARY KEY  (`settings_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `settings` WRITE;
INSERT INTO `settings` VALUES (1,'fromAddress','',1,1),(2,'fromAddress','',180,1),(3,'configured','1',1,1),(4,'configured','1',180,1);
UNLOCK TABLES;
DROP TABLE IF EXISTS `site`;
CREATE TABLE `site` (
  `site_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `is_demo` int(1) NOT NULL default '0',
  `user_licenses` int(11) NOT NULL default '0',
  `entered_by` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `unix_name` varchar(128) character set utf8 default NULL,
  `company_id` int(11) default NULL,
  `is_free` int(1) default NULL,
  `account_active` int(1) NOT NULL default '1',
  `account_deleted` int(1) NOT NULL default '0',
  `reason_disabled` text character set utf8,
  `time_zone` int(5) default '0',
  `time_format_24` int(1) default '0',
  `date_format_ddmmyy` int(1) default '0',
  `is_hr_mode` int(1) default '0',
  `file_size_kb` int(11) default '0',
  `page_views` bigint(20) default '0',
  `page_view_days` int(11) default '0',
  `last_viewed_day` date default NULL,
  `first_time_setup` tinyint(4) default '0',
  `localization_configured` int(1) default '0',
  `agreed_to_license` int(1) default '0',
  PRIMARY KEY  (`site_id`),
  KEY `IDX_account_deleted` (`account_deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=181 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `site` WRITE;
INSERT INTO `site` VALUES (1,'default_site',0,0,0,'2005-06-01 00:00:00',NULL,NULL,0,1,0,NULL,-6,0,0,0,0,0,0,NULL,0,0,0),(180,'CATS_ADMIN',0,0,0,'2005-06-01 00:00:00','catsadmin',NULL,0,1,0,NULL,-6,0,0,0,0,0,0,NULL,0,0,0);
UNLOCK TABLES;
DROP TABLE IF EXISTS `sph_counter`;
CREATE TABLE `sph_counter` (
  `counter_id` int(11) NOT NULL,
  `max_doc_id` int(11) NOT NULL,
  PRIMARY KEY  (`counter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `sph_counter` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `system`;
CREATE TABLE `system` (
  `system_id` int(20) NOT NULL default '0',
  `uid` int(20) default NULL,
  `available_version` int(11) default '0',
  `date_version_checked` datetime NOT NULL default '0000-00-00 00:00:00',
  `available_version_description` text collate utf8_unicode_ci,
  `disable_version_check` int(1) default NULL,
  PRIMARY KEY  (`system_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `system` WRITE;
INSERT INTO `system` VALUES (0,0,NULL,'0000-00-00 00:00:00',NULL,0);
UNLOCK TABLES;
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL auto_increment,
  `site_id` int(11) NOT NULL default '0',
  `user_name` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `email` varchar(128) collate utf8_unicode_ci default NULL,
  `password` varchar(128) collate utf8_unicode_ci NOT NULL default '',
  `access_level` int(11) NOT NULL default '100',
  `can_change_password` int(1) NOT NULL default '1',
  `is_test_user` int(1) NOT NULL default '0',
  `last_name` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  `first_name` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  `is_demo` int(1) default '0',
  `categories` varchar(192) collate utf8_unicode_ci default NULL,
  `session_cookie` varchar(48) collate utf8_unicode_ci default NULL,
  `pipeline_entries_per_page` int(8) default '15',
  `column_preferences` longtext collate utf8_unicode_ci,
  `force_logout` int(1) default '0',
  `title` varchar(64) collate utf8_unicode_ci default '',
  `phone_work` varchar(64) collate utf8_unicode_ci default '',
  `phone_cell` varchar(64) collate utf8_unicode_ci default '',
  `phone_other` varchar(64) collate utf8_unicode_ci default '',
  `address` text collate utf8_unicode_ci,
  `notes` text collate utf8_unicode_ci,
  `company` varchar(255) collate utf8_unicode_ci default NULL,
  `city` varchar(64) collate utf8_unicode_ci default NULL,
  `state` varchar(64) collate utf8_unicode_ci default NULL,
  `zip_code` varchar(16) collate utf8_unicode_ci default NULL,
  `country` varchar(128) collate utf8_unicode_ci default NULL,
  `can_see_eeo_info` int(1) default '0',
  PRIMARY KEY  (`user_id`),
  KEY `IDX_site_id` (`site_id`),
  KEY `IDX_first_name` (`first_name`),
  KEY `IDX_last_name` (`last_name`),
  KEY `IDX_access_level` (`access_level`)
) ENGINE=MyISAM AUTO_INCREMENT=1251 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `user` WRITE;
INSERT INTO `user` VALUES (1,1,'admin','','cats',500,1,0,'Administrator','CATS',0,NULL,NULL,15,NULL,0,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),(1250,180,'cats@rootadmin','0','cantlogin',0,0,0,'Automated','CATS',0,NULL,NULL,15,NULL,0,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0);
UNLOCK TABLES;
DROP TABLE IF EXISTS `user_login`;
CREATE TABLE `user_login` (
  `user_login_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) default NULL,
  `site_id` int(11) NOT NULL default '0',
  `ip` varchar(128) collate utf8_unicode_ci NOT NULL default '',
  `user_agent` varchar(255) collate utf8_unicode_ci default NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `successful` int(1) NOT NULL default '0',
  `host` varchar(255) collate utf8_unicode_ci default NULL,
  `date_refreshed` datetime default NULL,
  PRIMARY KEY  (`user_login_id`),
  KEY `IDX_user_id` (`user_id`),
  KEY `IDX_ip` (`ip`),
  KEY `IDX_date` (`date`),
  KEY `IDX_date_refreshed` (`date_refreshed`),
  KEY `IDX_site_id_date` (`site_id`,`date`),
  KEY `IDX_successful_site_id` (`successful`,`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `user_login` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `word_verification`;
CREATE TABLE `word_verification` (
  `word_verification_ID` int(11) NOT NULL auto_increment,
  `word` varchar(28) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`word_verification_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `word_verification` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `xml_feed_submits`;
CREATE TABLE `xml_feed_submits` (
  `feed_id` int(11) NOT NULL auto_increment,
  `feed_site` varchar(75) NOT NULL,
  `feed_url` varchar(255) NOT NULL,
  `date_last_post` date NOT NULL,
  PRIMARY KEY  (`feed_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `xml_feed_submits` WRITE;
UNLOCK TABLES;
DROP TABLE IF EXISTS `xml_feeds`;
CREATE TABLE `xml_feeds` (
  `xml_feed_id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) default NULL,
  `website` varchar(255) default NULL,
  `post_url` varchar(255) NOT NULL,
  `success_string` varchar(255) NOT NULL,
  `xml_template_name` varchar(255) NOT NULL,
  PRIMARY KEY  (`xml_feed_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

LOCK TABLES `xml_feeds` WRITE;
INSERT INTO `xml_feeds` VALUES (1,'Indeed','Indeed.com job search engine.','http://www.indeed.com','http://www.indeed.com/jsp/includejobs.jsp','Thank you for submitting your XML job feed','indeed'),(2,'SimplyHired','SimplyHired.com job search engine','http://www.simplyhired.com','http://www.simplyhired.com/confirmation.php','Thanks for Contacting Us','simplyhired');
UNLOCK TABLES;
DROP TABLE IF EXISTS `zipcodes`;
CREATE TABLE `zipcodes` (
  `zipcode` mediumint(9) NOT NULL default '0',
  `city` tinytext collate utf8_unicode_ci NOT NULL,
  `state` varchar(2) collate utf8_unicode_ci NOT NULL default '',
  `areacode` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`zipcode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
