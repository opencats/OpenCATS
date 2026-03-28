/*!40101 SET NAMES utf8mb4 */;

/*!40101 SET SQL_MODE=''*/;

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/* Table structure for table `access_level` */

CREATE TABLE `access_level` (
  `access_level_id` INT(11) NOT NULL DEFAULT '0',
  `short_description` VARCHAR(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `long_description` TEXT COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`access_level_id`),
  KEY `IDX_access_level` (`short_description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `access_level` */

INSERT INTO `access_level` (`access_level_id`, `short_description`, `long_description`) VALUES (0, 'Account Disabled', 'Disabled - The lowest access level. User cannot log in.');
INSERT INTO `access_level` (`access_level_id`, `short_description`, `long_description`) VALUES (100, 'Read Only', 'Read Only - A standard user that can view data on the system in a read-only mode.');
INSERT INTO `access_level` (`access_level_id`, `short_description`, `long_description`) VALUES (200, 'Add / Edit', 'Edit - All lower access, plus the ability to edit information on the system.');
INSERT INTO `access_level` (`access_level_id`, `short_description`, `long_description`) VALUES (300, 'Add / Edit / Delete', 'Delete - All lower access, plus the ability to delete information on the system.');
INSERT INTO `access_level` (`access_level_id`, `short_description`, `long_description`) VALUES (400, 'Site Administrator', 'Site Administrator - All lower access, plus the ability to add, edit, and remove site users, as well as the ability to edit site settings.');
INSERT INTO `access_level` (`access_level_id`, `short_description`, `long_description`) VALUES (500, 'Root', 'Root Administrator - All lower access, plus the ability to add, edit, and remove sites, as well as the ability to assign Site Administrator status to a user.');

/* Table structure for table `activity` */

CREATE TABLE `activity` (
  `activity_id` INT(11) NOT NULL AUTO_INCREMENT,
  `data_item_type` INT(11) NOT NULL DEFAULT '0',
  `data_item_id` INT(11) NOT NULL DEFAULT '0',
  `joborder_id` INT(11),
  `entered_by` INT(11) NOT NULL DEFAULT '0',
  `date_occurred` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `date_created` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `type` INT(11) NOT NULL DEFAULT '0',
  `notes` TEXT COLLATE utf8mb4_unicode_ci,
  `date_modified` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`activity_id`),
  KEY `IDX_entered_by` (`entered_by`),
  KEY `IDX_type` (`type`),
  KEY `IDX_data_item_type` (`data_item_type`),
  KEY `IDX_type_id` (`data_item_type`, `data_item_id`),
  KEY `IDX_joborder_id` (`joborder_id`),
  KEY `IDX_date_created` (`date_created`),
  KEY `IDX_date_occurred` (`date_occurred`),
  KEY `IDX_date_modified` (`date_modified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `activity` */

/* Table structure for table `activity_type` */

CREATE TABLE `activity_type` (
  `activity_type_id` INT(11) NOT NULL DEFAULT '0',
  `short_description` VARCHAR(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`activity_type_id`),
  KEY `IDX_activity_type1` (`short_description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `activity_type` */

INSERT INTO `activity_type` (`activity_type_id`, `short_description`) VALUES (100, 'Not reached');
INSERT INTO `activity_type` (`activity_type_id`, `short_description`) VALUES (200, 'Email');
INSERT INTO `activity_type` (`activity_type_id`, `short_description`) VALUES (300, 'Meeting');
INSERT INTO `activity_type` (`activity_type_id`, `short_description`) VALUES (400, 'Other');
INSERT INTO `activity_type` (`activity_type_id`, `short_description`) VALUES (500, 'Call (Talked)');
INSERT INTO `activity_type` (`activity_type_id`, `short_description`) VALUES (600, 'Call (LVM)');
INSERT INTO `activity_type` (`activity_type_id`, `short_description`) VALUES (700, 'Call (Missed)');
INSERT INTO `activity_type` (`activity_type_id`, `short_description`) VALUES (800, 'Status Change');

/* Table structure for table `attachment` */

CREATE TABLE `attachment` (
  `attachment_id` INT(11) NOT NULL AUTO_INCREMENT,
  `data_item_id` INT(11) NOT NULL DEFAULT '0',
  `data_item_type` INT(11) NOT NULL DEFAULT '0',
  `title` VARCHAR(128) COLLATE utf8mb4_unicode_ci,
  `original_filename` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `stored_filename` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `content_type` VARCHAR(255) COLLATE utf8mb4_unicode_ci,
  `resume` INT(1) NOT NULL DEFAULT '0',
  `text` TEXT COLLATE utf8mb4_unicode_ci,
  `date_created` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `date_modified` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `profile_image` INT(1) DEFAULT '0',
  `directory_name` VARCHAR(64) COLLATE utf8mb4_unicode_ci,
  `md5_sum` VARCHAR(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `file_size_kb` INT(11) DEFAULT '0',
  `md5_sum_text` VARCHAR(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`attachment_id`),
  KEY `IDX_type_id` (`data_item_type`, `data_item_id`),
  KEY `IDX_data_item_id` (`data_item_id`),
  KEY `IDX_CANDIDATE_MD5_SUM` (`md5_sum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `attachment` */

/* Table structure for table `calendar_event` */

CREATE TABLE `calendar_event` (
  `calendar_event_id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` INT(11) NOT NULL DEFAULT '0',
  `date` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `title` TEXT COLLATE utf8mb4_unicode_ci NOT NULL,
  `all_day` INT(1) NOT NULL DEFAULT '0',
  `data_item_id` INT(11) NOT NULL DEFAULT '-1',
  `data_item_type` INT(11) NOT NULL DEFAULT '-1',
  `entered_by` INT(11) NOT NULL DEFAULT '0',
  `date_created` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `date_modified` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `joborder_id` INT(11) DEFAULT NULL,
  `description` TEXT COLLATE utf8mb4_unicode_ci,
  `duration` INT(11) NOT NULL DEFAULT '60',
  `reminder_enabled` INT(1) NOT NULL DEFAULT '0',
  `reminder_email` TEXT COLLATE utf8mb4_unicode_ci,
  `reminder_time` INT(11) DEFAULT '0',
  `public` INT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`calendar_event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `calendar_event` */

/* Table structure for table `calendar_event_type` */

CREATE TABLE `calendar_event_type` (
  `calendar_event_type_id` INT(11) NOT NULL DEFAULT '0',
  `short_description` VARCHAR(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `icon_image` VARCHAR(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`calendar_event_type_id`),
  KEY `IDX_short_description` (`short_description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `calendar_event_type` */

INSERT INTO `calendar_event_type` (`calendar_event_type_id`, `short_description`, `icon_image`) VALUES (100, 'Call', 'images/phone.gif');
INSERT INTO `calendar_event_type` (`calendar_event_type_id`, `short_description`, `icon_image`) VALUES (200, 'Email', 'images/email.gif');
INSERT INTO `calendar_event_type` (`calendar_event_type_id`, `short_description`, `icon_image`) VALUES (300, 'Meeting', 'images/meeting.gif');
INSERT INTO `calendar_event_type` (`calendar_event_type_id`, `short_description`, `icon_image`) VALUES (400, 'Interview', 'images/interview.gif');
INSERT INTO `calendar_event_type` (`calendar_event_type_id`, `short_description`, `icon_image`) VALUES (500, 'Personal', 'images/personal.gif');
INSERT INTO `calendar_event_type` (`calendar_event_type_id`, `short_description`, `icon_image`) VALUES (600, 'Other', '');

/* Table structure for table `candidate` */

CREATE TABLE `candidate` (
  `candidate_id` INT(11) NOT NULL AUTO_INCREMENT,
  `last_name` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `first_name` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `middle_name` VARCHAR(32) COLLATE utf8mb4_unicode_ci,
  `phone_home` VARCHAR(40) COLLATE utf8mb4_unicode_ci,
  `phone_cell` VARCHAR(40) COLLATE utf8mb4_unicode_ci,
  `phone_work` VARCHAR(40) COLLATE utf8mb4_unicode_ci,
  `address` TEXT COLLATE utf8mb4_unicode_ci,
  `address2` TEXT COLLATE utf8mb4_unicode_ci,
  `city` VARCHAR(64) COLLATE utf8mb4_unicode_ci,
  `state` VARCHAR(64) COLLATE utf8mb4_unicode_ci,
  `zip` VARCHAR(16) COLLATE utf8mb4_unicode_ci,
  `country` VARCHAR(2) COLLATE utf8mb4_unicode_ci,
  `source` VARCHAR(128) COLLATE utf8mb4_unicode_ci,
  `date_available` DATETIME,
  `can_relocate` INT(1) NOT NULL DEFAULT '0',
  `notes` TEXT COLLATE utf8mb4_unicode_ci,
  `key_skills` TEXT COLLATE utf8mb4_unicode_ci,
  `current_employer` VARCHAR(128) COLLATE utf8mb4_unicode_ci,
  `entered_by` INT(11) NOT NULL DEFAULT '0' COMMENT 'Created-by user.',
  `owner` INT(11),
  `date_created` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `date_modified` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `email1` VARCHAR(128) COLLATE utf8mb4_unicode_ci,
  `email2` VARCHAR(128) COLLATE utf8mb4_unicode_ci,
  `web_site` VARCHAR(128) COLLATE utf8mb4_unicode_ci,
  `import_id` INT(11) NOT NULL DEFAULT '0',
  `is_hot` INT(1) NOT NULL DEFAULT '0',
  `eeo_ethnic_type_id` INT(11) DEFAULT '0',
  `eeo_veteran_type_id` INT(11) DEFAULT '0',
  `eeo_disability_status` VARCHAR(5) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `eeo_gender` VARCHAR(5) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `desired_pay` VARCHAR(64) COLLATE utf8mb4_unicode_ci,
  `current_pay` VARCHAR(64) COLLATE utf8mb4_unicode_ci,
  `is_active` INT(1) DEFAULT '1',
  `is_admin_hidden` INT(1) DEFAULT '0',
  `best_time_to_call` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`candidate_id`),
  KEY `IDX_first_name` (`first_name`),
  KEY `IDX_last_name` (`last_name`),
  KEY `IDX_phone_home` (`phone_home`),
  KEY `IDX_phone_cell` (`phone_cell`),
  KEY `IDX_phone_work` (`phone_work`),
  KEY `IDX_key_skills` (`key_skills`(255)),
  KEY `IDX_entered_by` (`entered_by`),
  KEY `IDX_owner` (`owner`),
  KEY `IDX_date_created` (`date_created`),
  KEY `IDX_date_modified` (`date_modified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `candidate` */

/* Table structure for table `candidate_duplicates` */

CREATE TABLE `candidate_duplicates` (
  `old_candidate_id` INT(11) NOT NULL,
  `new_candidate_id` INT(11) NOT NULL,
  PRIMARY KEY (`old_candidate_id`, `new_candidate_id`),
  KEY `IDX_old_candidate_id` (`old_candidate_id`),
  KEY `IDX_new_candidate_id` (`new_candidate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `candidate_duplicates` */

/* Table structure for table `candidate_joborder` */

CREATE TABLE `candidate_joborder` (
  `candidate_joborder_id` INT(11) NOT NULL AUTO_INCREMENT,
  `candidate_id` INT(11) NOT NULL DEFAULT '0',
  `joborder_id` INT(11) NOT NULL DEFAULT '0',
  `status` INT(11) NOT NULL DEFAULT '0',
  `date_submitted` DATETIME,
  `date_created` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `date_modified` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `rating_value` INT(5),
  `added_by` INT(11),
  PRIMARY KEY (`candidate_joborder_id`),
  KEY `IDX_candidate_id` (`candidate_id`),
  KEY `IDX_date_submitted` (`date_submitted`),
  KEY `IDX_date_created` (`date_created`),
  KEY `IDX_date_modified` (`date_modified`),
  KEY `IDX_joborder_id` (`joborder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `candidate_joborder` */

/* Table structure for table `candidate_joborder_status` */

CREATE TABLE `candidate_joborder_status` (
  `candidate_joborder_status_id` INT(11) NOT NULL DEFAULT '0',
  `short_description` VARCHAR(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `can_be_scheduled` INT(1) NOT NULL DEFAULT '0',
  `triggers_email` INT(1) NOT NULL DEFAULT '1',
  `is_enabled` INT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`candidate_joborder_status_id`),
  KEY `IDX_short_description` (`short_description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `candidate_joborder_status` */

INSERT INTO `candidate_joborder_status` (`candidate_joborder_status_id`, `short_description`, `can_be_scheduled`, `triggers_email`, `is_enabled`) VALUES (0, 'No Status', 0, 0, 1);
INSERT INTO `candidate_joborder_status` (`candidate_joborder_status_id`, `short_description`, `can_be_scheduled`, `triggers_email`, `is_enabled`) VALUES (100, 'No Contact', 0, 0, 1);
INSERT INTO `candidate_joborder_status` (`candidate_joborder_status_id`, `short_description`, `can_be_scheduled`, `triggers_email`, `is_enabled`) VALUES (200, 'Contacted', 0, 0, 1);
INSERT INTO `candidate_joborder_status` (`candidate_joborder_status_id`, `short_description`, `can_be_scheduled`, `triggers_email`, `is_enabled`) VALUES (250, 'Candidate Responded', 0, 0, 1);
INSERT INTO `candidate_joborder_status` (`candidate_joborder_status_id`, `short_description`, `can_be_scheduled`, `triggers_email`, `is_enabled`) VALUES (300, 'Qualifying', 0, 1, 1);
INSERT INTO `candidate_joborder_status` (`candidate_joborder_status_id`, `short_description`, `can_be_scheduled`, `triggers_email`, `is_enabled`) VALUES (400, 'Submitted', 0, 1, 1);
INSERT INTO `candidate_joborder_status` (`candidate_joborder_status_id`, `short_description`, `can_be_scheduled`, `triggers_email`, `is_enabled`) VALUES (500, 'Interviewing', 0, 1, 1);
INSERT INTO `candidate_joborder_status` (`candidate_joborder_status_id`, `short_description`, `can_be_scheduled`, `triggers_email`, `is_enabled`) VALUES (600, 'Offered', 0, 1, 1);
INSERT INTO `candidate_joborder_status` (`candidate_joborder_status_id`, `short_description`, `can_be_scheduled`, `triggers_email`, `is_enabled`) VALUES (650, 'Not in Consideration', 0, 0, 1);
INSERT INTO `candidate_joborder_status` (`candidate_joborder_status_id`, `short_description`, `can_be_scheduled`, `triggers_email`, `is_enabled`) VALUES (675, 'Candidate Declined', 0, 0, 1);
INSERT INTO `candidate_joborder_status` (`candidate_joborder_status_id`, `short_description`, `can_be_scheduled`, `triggers_email`, `is_enabled`) VALUES (700, 'Client Declined', 0, 0, 1);
INSERT INTO `candidate_joborder_status` (`candidate_joborder_status_id`, `short_description`, `can_be_scheduled`, `triggers_email`, `is_enabled`) VALUES (800, 'Placed', 0, 1, 1);

/* Table structure for table `candidate_joborder_status_history` */

CREATE TABLE `candidate_joborder_status_history` (
  `candidate_joborder_status_history_id` INT(11) NOT NULL AUTO_INCREMENT,
  `candidate_id` INT(11) NOT NULL DEFAULT '0',
  `joborder_id` INT(11) NOT NULL DEFAULT '0',
  `date` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `status_from` INT(11) NOT NULL DEFAULT '0',
  `status_to` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`candidate_joborder_status_history_id`),
  KEY `IDX_status_to` (`status_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `candidate_joborder_status_history` */

/* Table structure for table `candidate_source` */

CREATE TABLE `candidate_source` (
  `source_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci,
  `date_created` DATETIME,
  PRIMARY KEY (`source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `candidate_source` */

/* Table structure for table `candidate_tag` */

CREATE TABLE `candidate_tag` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `candidate_id` INT(10) UNSIGNED NOT NULL,
  `tag_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Table structure for table `career_portal_questionnaire` */

CREATE TABLE `career_portal_questionnaire` (
  `career_portal_questionnaire_id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `description` VARCHAR(255),
  `is_active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`career_portal_questionnaire_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Data for the table `career_portal_questionnaire` */

/* Table structure for table `career_portal_questionnaire_answer` */

CREATE TABLE `career_portal_questionnaire_answer` (
  `career_portal_questionnaire_answer_id` INT(11) NOT NULL AUTO_INCREMENT,
  `career_portal_questionnaire_question_id` INT(11) NOT NULL,
  `career_portal_questionnaire_id` INT(11) NOT NULL,
  `text` VARCHAR(255) NOT NULL DEFAULT '',
  `action_source` VARCHAR(128),
  `action_notes` TEXT,
  `action_is_hot` TINYINT(1) DEFAULT '0',
  `action_is_active` TINYINT(1) DEFAULT '0',
  `action_can_relocate` TINYINT(1) DEFAULT '0',
  `action_key_skills` VARCHAR(255),
  `position` INT(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`career_portal_questionnaire_answer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Data for the table `career_portal_questionnaire_answer` */

/* Table structure for table `career_portal_questionnaire_history` */

CREATE TABLE `career_portal_questionnaire_history` (
  `career_portal_questionnaire_history_id` INT(11) NOT NULL AUTO_INCREMENT,
  `candidate_id` INT(11) NOT NULL DEFAULT '0',
  `question` VARCHAR(255) NOT NULL DEFAULT '',
  `answer` VARCHAR(255) NOT NULL DEFAULT '',
  `questionnaire_title` VARCHAR(255) NOT NULL DEFAULT '',
  `questionnaire_description` VARCHAR(255) NOT NULL DEFAULT '',
  `date` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`career_portal_questionnaire_history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Data for the table `career_portal_questionnaire_history` */

/* Table structure for table `career_portal_questionnaire_question` */

CREATE TABLE `career_portal_questionnaire_question` (
  `career_portal_questionnaire_question_id` INT(11) NOT NULL AUTO_INCREMENT,
  `career_portal_questionnaire_id` INT(11) NOT NULL,
  `text` VARCHAR(255) NOT NULL DEFAULT '',
  `minimum_length` INT(11),
  `maximum_length` INT(11),
  `required` TINYINT(1) NOT NULL DEFAULT '0',
  `position` INT(4) NOT NULL DEFAULT '0',
  `type` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`career_portal_questionnaire_question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Data for the table `career_portal_questionnaire_question` */

/* Table structure for table `career_portal_template` */

CREATE TABLE `career_portal_template` (
  `career_portal_template_id` INT(11) NOT NULL AUTO_INCREMENT,
  `career_portal_name` VARCHAR(255),
  `setting` VARCHAR(128) NOT NULL DEFAULT '',
  `value` TEXT,
  PRIMARY KEY (`career_portal_template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4;

/* Data for the table `career_portal_template` */

INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (1, 'Blank Page', 'Left', '');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (2, 'Blank Page', 'Footer', '');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (3, 'Blank Page', 'Header', '');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (4, 'Blank Page', 'Content - Main', '');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (5, 'Blank Page', 'CSS', '');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (6, 'Blank Page', 'Content - Search Results', '');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (7, 'Blank Page', 'Content - Questionnaire', '');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (8, 'Blank Page', 'Content - Job Details', '');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (9, 'Blank Page', 'Content - Thanks for your Submission', '');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (10, 'Blank Page', 'Content - Apply for Position', '');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (11, 'CATS 2.0', 'Left', '');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (12, 'CATS 2.0', 'Footer', '</div>');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (13, 'CATS 2.0', 'Header', '<div id=\"container\">\r\n	<div id=\"logo\"><img src=\"images/careers_cats.gif\" alt=\"IMAGE: CATS Applicant Tracking System Careers Page\" /></div>\r\n    <div id=\"actions\">\r\n    	<h2>Shortcuts:</h2>\r\n        <a href=\"index.php\" onmouseover=\"buttonMouseOver(\'returnToMain\',true);\" onmouseout=\"buttonMouseOver(\'returnToMain\',false);\"><img src=\"images/careers_return.gif\" id=\"returnToMain\" alt=\"IMAGE: Return to Main\" /></a>\r\n<a href=\"<rssURL>\" onmouseover=\"buttonMouseOver(\'rssFeed\',true);\" onmouseout=\"buttonMouseOver(\'rssFeed\',false);\"><img src=\"images/careers_rss.gif\" id=\"rssFeed\" alt=\"IMAGE: RSS Feed\" /></a>\r\n        <a href=\"index.php?m=careers&p=showAll\" onmouseover=\"buttonMouseOver(\'showAllJobs\',true);\" onmouseout=\"buttonMouseOver(\'showAllJobs\',false);\"><img src=\"images/careers_show.gif\" id=\"showAllJobs\" alt=\"IMAGE: Show All Jobs\" /></a>\r\n    </div>');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (14, 'CATS 2.0', 'Content - Main', '<div id=\"careerContent\">\r\n        <registeredCandidate>\r\n        <h1>Available Openings at <siteName></h1>\r\n        <div id=\"descriptive\">\r\n               <p>Change your life today by becoming an integral part of our winning team.</p>\r\n               <p>If you are interested, we invite you to view the <a href=\"index.php?m=careers&p=showAll\">current opening positions</a> at our company.</p><br /><br /><registeredLoginTitle><h1 style=\"padding:0;margin:0;border:0\">Have you applied with us before?</h1></registeredLoginTitle><registeredLogin>\r\n        </div>\r\n        <div id=\"detailsTools\">\r\n        	<h2>Perform an action:</h2>\r\n        	<ul>\r\n                    <li><a href=\"\">Visit our website</a></li>\r\n                </ul>\r\n        </div>\r\n</div>');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (15, 'CATS 2.0', 'CSS', 'table.sortable\r\n{\r\ntext-align:left;\r\nempty-cells: show;\r\nwidth: 940px;\r\n}\r\ntd\r\n{\r\npadding:5px;\r\n}\r\ntr.rowHeading\r\n{\r\n background: #e0e0e0; border: 1px solid #cccccc; border-left: none; border-right: none;\r\n}\r\ntr.oddTableRow\r\n{\r\nbackground: #ebebeb; \r\n}\r\ntr.evenTableRow\r\n{\r\n background: #ffffff; \r\n}\r\na.sortheader:hover,\r\na.sortheader:link,\r\na.sortheader:visited\r\n{\r\ncolor:#000;\r\n}\r\n\r\nbody, html { margin: 0; padding: 0; background: #ffffff; font: normal 12px/14px Arial, Helvetica, sans-serif; color: #000000; }\r\n#container { margin: 0 auto; padding: 0; width: 940px; height: auto; }\r\n#logo { float: left; margin: 0; }\r\n	#logo img { width: 424px; height: 103px; }\r\n#actions { float: right; margin: 0; width: 310px; height: 100px; background: #efefef; border: 1px solid #cccccc; }\r\n	#actions img { float: left; margin: 2px 6px 2px 15px; width: 130px; height: 25px; }\r\n#footer { clear: both; margin: 20px auto 0 auto; width: 150px; }\r\n	#footer img { width: 137px; height: 38px; }\r\n\r\na:link, a:active { color: #1763b9; }\r\na:hover { color: #c75a01; }\r\na:visited { color: #333333; }\r\nimg { border: none; }\r\n\r\nh1 { margin: 0 0 10px 0; font: bold 18px Arial, Helvetica, sans-serif; }\r\nh2 { margin: 8px 0 8px 15px; font: bold 14px Arial, Helvetica, sans-serif; }\r\nh3 { margin: 0; font: bold 14px Arial, Helvetica, sans-serif; }\r\np { font: normal 12px Arial, Helvetica, sans-serif; }\r\np.instructions { margin: 0 0 0 10px; font: italic 12px Arial, Helvetica, sans-serif; color: #666666; }\r\n\r\n\r\n/* CONTENTS ON PAGE SPECS */\r\n#careerContent { clear: both; padding: 15px 0 0 0; }\r\n\r\n	\r\n/* DISPLAY JOB DETAILS */\r\n#detailsTable { width: 400px; }\r\n	#detailsTable td.detailsHeader { width: 30%; }\r\ndiv#descriptive { float: left; width: 585px; }\r\ndiv#detailsTools { float: right; padding: 0 0 8px 0; width: 280px; background: #ffffff; border: 1px solid #cccccc; }\r\n	div#detailsTools img { margin: 2px 6px 5px 15px;  }\r\n\r\n/* DISPLAY APPLICATION FORM */\r\ndiv.applyBoxLeft, div.applyBoxRight { width: 450px; height: 470px; background: #f9f9f9; border: 1px solid #cccccc; border-top: none; }\r\ndiv.applyBoxLeft { float: left; margin: 0 10px 0 0; }\r\ndiv.applyBoxRight { float: right; margin: 0 0 0 10px; }\r\n	div.applyBoxLeft div, div.applyBoxRight div { margin: 0 0 5px 0; padding: 3px 10px; background: #efefef; border-top: 1px solid #cccccc; border-bottom: 1px solid #cccccc; }\r\n	div.applyBoxLeft table, div.applyBoxRight table { margin: 0 auto; width: 420px; }\r\n	div.applyBoxLeft table td, div.applyBoxRight table td { padding: 3px; vertical-align: top; }\r\n		td.label { text-align: right; width: 110px; }\r\n        form#applyToJobForm {  }\r\n	form#applyToJobForm label { font-weight: bold; }\r\n	form#applyToJobForm input.inputBoxName, form#applyToJobForm input.inputBoxNormal { width: 285px; height: 15px; }\r\n        form#applyToJobForm input.submitButton { width: 197px; height: 27px; background: url(\'images/careers_submit.gif\') no-repeat; }\r\n\r\n        form#applyToJobForm input.submitButtonDown { width: 197px; height: 27px; background: url(\'images/careers_submit-o.gif\') no-repeat; }\r\n	form#applyToJobForm textarea { margin: 8px 0 0 0; width: 410px; height: 170px; }\r\n	form#applyToJobForm textarea.inputBoxArea{ width: 285px; height: 70px; }\r\n\r\n');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (16, 'CATS 2.0', 'Content - Search Results', '<div id=\"careerContent\">\r\n        <registeredCandidate>\r\n        <h1>Current Available Openings, Recently Posted Jobs: <numberOfSearchResults></h1>\r\n<searchResultsTable>\r\n    </div>');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (17, 'CATS 2.0', 'Content - Questionnaire', '<div id=\"careerContent\">\r\n<questionnaire>\r\n<br /><br />\r\n<div style=\"text-align: right;\">\r\n<submit value=\"Continue\">\r\n</div>\r\n</div>');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (18, 'CATS 2.0', 'Content - Job Details', '<div id=\"careerContent\">\r\n        <registeredCandidate>\r\n        <h1>Position Details: <title></h1>\r\n        <table id=\"detailsTable\">\r\n            <tr>\r\n                <td class=\"detailsHeader\"><strong>Location:</strong></td>\r\n                <td><location></td>\r\n			</tr>\r\n			<tr>\r\n                <td class=\"detailsHeader\"><strong>Openings:</strong></td>\r\n                <td><openings></td>\r\n			</tr>\r\n            <tr>\r\n                <td class=\"detailsHeader\"><strong>Salary Range:</strong></td>\r\n                <td><salary></td>\r\n            </tr>\r\n        </table>\r\n        <div id=\"descriptive\">\r\n            <p><strong>Description:</strong></p>\r\n            <description>\r\n		</div>\r\n        <div id=\"detailsTools\">\r\n        	<h2>Perform an action:</h2>\r\n        	<a-applyToJob onmouseover=\"buttonMouseOver(\'applyToPosition\',true);\" onmouseout=\"buttonMouseOver(\'applyToPosition\',false);\"><img src=\"images/careers_apply.gif\" id=\"applyToPosition\" alt=\"IMAGE: Apply to Position\" /></a>\r\n        </div>\r\n    </div>');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (19, 'CATS 2.0', 'Content - Thanks for your Submission', '<div id=\"careerContent\">\r\n            <h1>Application Submitted For: <title></h1>\r\n            <div id=\"descriptive\">\r\n                <p>Please check your email inbox &#8212; You should receive an email confirmation of your application.</p>\r\n                <p>Thank you for submitting your application to us. We will review it shortly and make contact with you soon.</p>\r\n                </div>\r\n			<div id=\"detailsTools\">\r\n                <h2>Perform an action:</h2>\r\n                <ul>\r\n                	<li><a href=\"\">Visit our website</a></li>\r\n		</ul>\r\n        	</div>\r\n    </div>');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (20, 'CATS 2.0', 'Content - Apply for Position', '<div id=\"careerContent\">\r\n        <h1>Applying to: <title></h1>\r\n        <div class=\"applyBoxLeft\">\r\n            <div><h3>1. Import Resume (or CV) and Populate Fields</h3></div>\r\n            <table>\r\n                <tr>\r\n                    <td>\r\n                      \r\n                    <input-resumeUploadPreview>\r\n                    </td>\r\n                </tr>\r\n            </table>\r\n            <br />\r\n\r\n            <div><h3>2. Tell us about yourself</h3></div>\r\n            <p class=\"instructions\">All fields marked with asterisk (*) are required.</p>\r\n            <table>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"firstNameLabel\" for=\"firstName\">*First Name:</label></td>\r\n                    <td><input-firstName></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"lastNameLabel\" for=\"lastName\">*Last Name:</label></td>\r\n                    <td><input-lastName></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"emailLabel\" for=\"email\">*Email Adddress:</label></td>\r\n                    <td><input-email></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"emailConfirmLabel\" for=\"emailconfirm\">*Confirm Email:</label></td>\r\n                    <td><input-emailconfirm></td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n       \r\n        <div class=\"applyBoxRight\">\r\n            <div><h3>3. How may we contact you?</h3></div>\r\n            <table>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"homePhoneLabel\" for=\"homePhone\">Home Phone:</label></td>\r\n                    <td><input-phone-home></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"mobilePhoneLabel\" for=\"mobilePhone\">Mobile Phone:</label></td>\r\n                    <td><input-phone-cell></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"workPhoneLabel\" for=\"workPhone\">Work Phone:</label></td>\r\n                    <td><input-phone></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"bestTimeLabel\" for=\"bestTime\">*Best time to call:</label></td>\r\n                    <td><input-best-time-to-call></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"mailingAddressLabel\" for=\"mailingAddress\">Mailing Address:</label></td>\r\n                    <td><input-address></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"cityProvinceLabel\" for=\"cityProvince\">*City/Province:</label></td>\r\n                    <td><input-city></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"stateCountryLabel\" for=\"stateCountry\">*State/Country:</label></td>\r\n                    <td><input-state><br /><input-country></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"zipPostalLabel\" for=\"zipPostal\">*Zip/Postal Code:</label></td>\r\n                    <td><input-zip></td>\r\n                </tr>\r\n            </table>\r\n            <br />\r\n            <div><h3>4. Additional Information</h3></div>\r\n            <table>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"keySkillsLabel\" for=\"keySkills\">*Key Skills:</label></td>\r\n                    <td><input-keySkills></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"captchaLabel\" for=\"captcha\">*Captcha:</label></td>\r\n                    <td><input-captcha req></td>\r\n                </tr>\r\n                <tr>\r\n                    <td>&nbsp;</td>\r\n                    <td><img src=\"images/careers_submit.gif\" onmouseover=\"buttonMouseOver(\'submitApplicationNow\',true)\" onmouseout=\"buttonMouseOver(\'submitApplicationNow\',false)\" style=\"cursor: pointer;\" id=\"submitApplicationNow\" alt=\"Submit Application Now\" onclick=\"if (applyValidate()) { document.applyToJobForm.submit(); }\" /></td>\r\n                </tr>\r\n            </table>\r\n               </div>\r\n    </div>');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (21, 'CATS 2.0', 'Content - Candidate Registration', '<div id=\"careerContent\">\r\n    <h1><applyContent>Applying to <title></applyContent></h1>\r\n    <center>\r\n    <table cellpadding=\"0\" cellspacing=\"0\">\r\n        <tr>\r\n            <td><label id=\"emailLabel\" for=\"email\"><h2>Enter your e-mail address:</h2></label></td>\r\n            <td><input-email></td>\r\n        </tr>\r\n        <tr>\r\n            <td align=\"right\" valign=\"top\"><input-new></td>\r\n            <td style=\"line-height: 18px;\">\r\n                <applyContent>\r\n                <strong>I have not registered on this website.</strong><br />\r\n                (I haven\'t applied to any jobs online)\r\n                </applyContent>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <td align=\"right\" valign=\"top\"><input-registered></td>\r\n            <td style=\"line-height: 20px;\">\r\n                <strong>I have registered before</strong><br />\r\n                and my last name is:<br />\r\n                <input-lastName><br />\r\n                and my zip code is:<br />\r\n                <input-zip><br /><br />\r\n                <input-rememberMe> Remember my information for future visits<br /><br />\r\n                <input-submit><br /><br />\r\n            </td>\r\n        </tr>\r\n    </table>\r\n    </center>\r\n</div>\r\n');
INSERT INTO `career_portal_template` (`career_portal_template_id`, `career_portal_name`, `setting`, `value`) VALUES (22, 'CATS 2.0', 'Content - Candidate Profile', '<div id=\"careerContent\">    <h1 style=\"padding: 0; margin: 0; border: 0;\">My Profile</h1><h3 style=\"font-weight: normal;\">Any changes you make to your profile will be updated on our website for all    past and future jobs you apply for.</h3>    <br />    <div class=\"applyBoxLeft\">        <div><h3>1. Tell us about yourself</h3></div>        <p class=\"instructions\">All fields marked with asterisk (*) are required.</p>        <table>            <tr>                <td class=\"label\"><label id=\"firstNameLabel\" for=\"firstName\">*First Name:</label></td>                <td><input-firstName></td>            </tr>            <tr>                <td class=\"label\"><label id=\"lastNameLabel\" for=\"lastName\">*Last Name:</label></td>                <td><input-lastName></td>            </tr>            <tr>                <td class=\"label\"><label id=\"emailLabel\" for=\"email\">*Email Adddress:</label></td>                <td><input-email1></td>            </tr>            <tr>                <td colspan=\"2\">                    <input-resume>                </td>            </tr>        </table>    </div>    <div class=\"applyBoxRight\">        <div><h3>2. How may we contact you?</h3></div>        <table>            <tr>                <td class=\"label\"><label id=\"homePhoneLabel\" for=\"homePhone\">Home Phone:</label></td>                <td><input-phoneHome></td>            </tr>            <tr>                <td class=\"label\"><label id=\"mobilePhoneLabel\" for=\"mobilePhone\">Mobile Phone:</label></td>                <td><input-phoneCell></td>            </tr>            <tr>                <td class=\"label\"><label id=\"workPhoneLabel\" for=\"workPhone\">Work Phone:</label></td>                <td><input-phoneWork></td>            </tr>            <tr>                <td class=\"label\"><label id=\"bestTimeLabel\" for=\"bestTime\">*Best time to call:</label></td>                <td><input-bestTimeToCall></td>            </tr>            <tr>                <td class=\"label\"><label id=\"mailingAddressLabel\" for=\"mailingAddress\">Mailing Address:</label></td>                <td><input-address></td>            </tr>            <tr>                <td class=\"label\"><label id=\"cityProvinceLabel\" for=\"cityProvince\">*City/Province:</label></td>                <td><input-city></td>            </tr>            <tr>                <td class=\"label\"><label id=\"stateCountryLabel\" for=\"stateCountry\">*State/Country:</label></td>                <td><input-state><br /><input-country></td>            </tr>            <tr>                <td class=\"label\"><label id=\"zipPostalLabel\" for=\"zipPostal\">*Zip/Postal Code:</label></td>                <td><input-zip></td>            </tr>        </table>        <br />        <div><h3>3. Additional Information</h3></div>        <table>            <tr>                <td class=\"label\"><label id=\"keySkillsLabel\" for=\"keySkills\">*Key Skills:</label></td>                <td><input-keySkills></td>            </tr>            <tr>                <td>&nbsp;</td>                <td style=\"padding-top: 40px;\"><input-submit></td>            </tr>        </table>    </div></div>');

/* Table structure for table `career_portal_template_site` */

CREATE TABLE `career_portal_template_site` (
  `career_portal_template_id` INT(11) NOT NULL AUTO_INCREMENT,
  `career_portal_name` VARCHAR(255),
  `setting` VARCHAR(128) NOT NULL DEFAULT '',
  `value` TEXT,
  PRIMARY KEY (`career_portal_template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Data for the table `career_portal_template_site` */

/* Table structure for table `company` */

CREATE TABLE `company` (
  `company_id` INT(11) NOT NULL AUTO_INCREMENT,
  `billing_contact` INT(11),
  `name` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `address` TEXT COLLATE utf8mb4_unicode_ci,
  `address2` TEXT COLLATE utf8mb4_unicode_ci,
  `city` VARCHAR(64) COLLATE utf8mb4_unicode_ci,
  `state` VARCHAR(64) COLLATE utf8mb4_unicode_ci,
  `zip` VARCHAR(16) COLLATE utf8mb4_unicode_ci,
  `country` VARCHAR(2) COLLATE utf8mb4_unicode_ci,
  `phone1` VARCHAR(40) COLLATE utf8mb4_unicode_ci,
  `phone2` VARCHAR(40) COLLATE utf8mb4_unicode_ci,
  `url` VARCHAR(128) COLLATE utf8mb4_unicode_ci,
  `key_technologies` TEXT COLLATE utf8mb4_unicode_ci,
  `notes` TEXT COLLATE utf8mb4_unicode_ci,
  `entered_by` INT(11),
  `owner` INT(11),
  `date_created` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `date_modified` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `is_hot` INT(1),
  `fax_number` VARCHAR(40) COLLATE utf8mb4_unicode_ci,
  `import_id` INT(11),
  `default_company` INT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`company_id`),
  KEY `IDX_name` (`name`),
  KEY `IDX_key_technologies` (`key_technologies`(255)),
  KEY `IDX_entered_by` (`entered_by`),
  KEY `IDX_owner` (`owner`),
  KEY `IDX_date_created` (`date_created`),
  KEY `IDX_date_modified` (`date_modified`),
  KEY `IDX_is_hot` (`is_hot`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `company` */

INSERT INTO `company` (`company_id`, `billing_contact`, `name`, `address`, `city`, `state`, `zip`, `country`, `phone1`, `phone2`, `url`, `key_technologies`, `notes`, `entered_by`, `owner`, `date_created`, `date_modified`, `is_hot`, `fax_number`, `import_id`, `default_company`) VALUES (1, NULL, 'Internal Postings', '', '', '', '', NULL, '', '', '', '', '', 0, 0, '1000-01-01 00:00:00', '1000-01-01 00:00:00', 0, '', NULL, 1);

/* Table structure for table `company_department` */

CREATE TABLE `company_department` (
  `company_department_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) COLLATE utf8mb4_unicode_ci,
  `company_id` INT(11) NOT NULL,
  `date_created` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `created_by` INT(11),
  PRIMARY KEY (`company_department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `company_department` */

/* Table structure for table `contact` */

CREATE TABLE `contact` (
  `contact_id` INT(11) NOT NULL AUTO_INCREMENT,
  `company_id` INT(11) NOT NULL,
  `last_name` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `first_name` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `title` VARCHAR(128) COLLATE utf8mb4_unicode_ci,
  `email1` VARCHAR(128) COLLATE utf8mb4_unicode_ci,
  `email2` VARCHAR(128) COLLATE utf8mb4_unicode_ci,
  `phone_work` VARCHAR(40) COLLATE utf8mb4_unicode_ci,
  `phone_cell` VARCHAR(40) COLLATE utf8mb4_unicode_ci,
  `phone_other` VARCHAR(40) COLLATE utf8mb4_unicode_ci,
  `address` TEXT COLLATE utf8mb4_unicode_ci,
  `address2` TEXT COLLATE utf8mb4_unicode_ci,
  `city` VARCHAR(64) COLLATE utf8mb4_unicode_ci,
  `state` VARCHAR(64) COLLATE utf8mb4_unicode_ci,
  `zip` VARCHAR(16) COLLATE utf8mb4_unicode_ci,
  `country` VARCHAR(2) COLLATE utf8mb4_unicode_ci,
  `is_hot` INT(1),
  `notes` TEXT COLLATE utf8mb4_unicode_ci,
  `entered_by` INT(11) NOT NULL DEFAULT '0',
  `owner` INT(11),
  `date_created` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `date_modified` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `left_company` INT(1) NOT NULL DEFAULT '0',
  `import_id` INT(11) NOT NULL DEFAULT '0',
  `company_department_id` INT(11) NOT NULL,
  `reports_to` INT(11) DEFAULT '-1',
  PRIMARY KEY (`contact_id`),
  KEY `IDX_first_name` (`first_name`),
  KEY `IDX_last_name` (`last_name`),
  KEY `IDX_client_id` (`company_id`),
  KEY `IDX_title` (`title`),
  KEY `IDX_owner` (`owner`),
  KEY `IDX_date_created` (`date_created`),
  KEY `IDX_date_modified` (`date_modified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `contact` */

/* Table structure for table `data_item_type` */

CREATE TABLE `data_item_type` (
  `data_item_type_id` INT(11) NOT NULL DEFAULT '0',
  `short_description` VARCHAR(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`data_item_type_id`),
  KEY `IDX_short_description` (`short_description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `data_item_type` */

INSERT INTO `data_item_type` (`data_item_type_id`, `short_description`) VALUES (100, 'Candidate');
INSERT INTO `data_item_type` (`data_item_type_id`, `short_description`) VALUES (200, 'Company');
INSERT INTO `data_item_type` (`data_item_type_id`, `short_description`) VALUES (300, 'Contact');
INSERT INTO `data_item_type` (`data_item_type_id`, `short_description`) VALUES (400, 'Job Order');

/* Table structure for table `eeo_ethnic_type` */

CREATE TABLE `eeo_ethnic_type` (
  `eeo_ethnic_type_id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`eeo_ethnic_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

/* Data for the table `eeo_ethnic_type` */

INSERT INTO `eeo_ethnic_type` (`eeo_ethnic_type_id`, `type`) VALUES (1, 'American Indian');
INSERT INTO `eeo_ethnic_type` (`eeo_ethnic_type_id`, `type`) VALUES (2, 'Asian or Pacific Islander');
INSERT INTO `eeo_ethnic_type` (`eeo_ethnic_type_id`, `type`) VALUES (3, 'Hispanic or Latino');
INSERT INTO `eeo_ethnic_type` (`eeo_ethnic_type_id`, `type`) VALUES (4, 'Non-Hispanic Black');
INSERT INTO `eeo_ethnic_type` (`eeo_ethnic_type_id`, `type`) VALUES (5, 'Non-Hispanic White');

/* Table structure for table `eeo_veteran_type` */

CREATE TABLE `eeo_veteran_type` (
  `eeo_veteran_type_id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`eeo_veteran_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

/* Data for the table `eeo_veteran_type` */

INSERT INTO `eeo_veteran_type` (`eeo_veteran_type_id`, `type`) VALUES (1, 'No Veteran Status');
INSERT INTO `eeo_veteran_type` (`eeo_veteran_type_id`, `type`) VALUES (2, 'Eligible Veteran');
INSERT INTO `eeo_veteran_type` (`eeo_veteran_type_id`, `type`) VALUES (3, 'Disabled Veteran');
INSERT INTO `eeo_veteran_type` (`eeo_veteran_type_id`, `type`) VALUES (4, 'Eligible and Disabled');

/* Table structure for table `email_history` */

CREATE TABLE `email_history` (
  `email_history_id` INT(11) NOT NULL AUTO_INCREMENT,
  `from_address` VARCHAR(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `recipients` TEXT COLLATE utf8mb4_unicode_ci NOT NULL,
  `text` TEXT COLLATE utf8mb4_unicode_ci,
  `user_id` INT(11),
  `date` DATETIME,
  PRIMARY KEY (`email_history_id`),
  KEY `IDX_date` (`date`),
  KEY `IDX_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `email_history` */

/* Table structure for table `email_template` */

CREATE TABLE `email_template` (
  `email_template_id` INT(11) NOT NULL AUTO_INCREMENT,
  `text` TEXT COLLATE utf8mb4_unicode_ci,
  `allow_substitution` INT(1) NOT NULL DEFAULT '0',
  `tag` VARCHAR(255) COLLATE utf8mb4_unicode_ci,
  `title` VARCHAR(255) COLLATE utf8mb4_unicode_ci,
  `possible_variables` TEXT COLLATE utf8mb4_unicode_ci,
  `disabled` INT(1) DEFAULT '0',
  PRIMARY KEY (`email_template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `email_template` */

INSERT INTO `email_template` (`email_template_id`, `text`, `allow_substitution`, `tag`, `title`, `possible_variables`, `disabled`) VALUES (1, '* Auto generated message. Please DO NOT reply *\r\n%DATETIME%\r\n\r\nDear %CANDFULLNAME%,\r\n\r\nThis E-Mail is a notification that your status in our database has been changed for the position %JBODTITLE% (%JBODCLIENT%).\r\n\r\nYour previous status was <B>%CANDPREVSTATUS%</B>.\r\nYour new status is <B>%CANDSTATUS%</B>.\r\n\r\nTake care,\r\n%USERFULLNAME%\r\n%SITENAME%', 1, 'EMAIL_TEMPLATE_STATUSCHANGE', 'Status Changed (Sent to Candidate)', '%CANDSTATUS%%CANDOWNER%%CANDFIRSTNAME%%CANDFULLNAME%%CANDPREVSTATUS%%JBODCLIENT%%JBODTITLE%', 0);
INSERT INTO `email_template` (`email_template_id`, `text`, `allow_substitution`, `tag`, `title`, `possible_variables`, `disabled`) VALUES (2, '%DATETIME%\r\n\r\nDear %CANDOWNER%,\r\n\r\nThis E-Mail is a notification that a Candidate has been assigned to you.\r\n\r\nCandidate Name: %CANDFULLNAME%\r\nCandidate URL: %CANDCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCANDIDATE', 'Candidate Assigned (Sent to Assigned Recruiter)', '%CANDOWNER%%CANDFIRSTNAME%%CANDFULLNAME%%CANDCATSURL%', 0);
INSERT INTO `email_template` (`email_template_id`, `text`, `allow_substitution`, `tag`, `title`, `possible_variables`, `disabled`) VALUES (3, '%DATETIME%\r\n\r\nDear %JBODOWNER%,\r\n\r\nThis E-Mail is a notification that a Job Order has been assigned to you.\r\n\r\nJob Order Title: %JBODTITLE%\r\nJob Order Client: %JBODCLIENT%\r\nJob Order ID: %JBODID%\r\nJob Order URL: %JBODCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNJOBORDER', 'Job Order Assigned (Sent to Assigned Recruiter)', '%JBODOWNER%%JBODTITLE%%JBODCLIENT%%JBODCATSURL%%JBODID%', 0);
INSERT INTO `email_template` (`email_template_id`, `text`, `allow_substitution`, `tag`, `title`, `possible_variables`, `disabled`) VALUES (4, '%DATETIME%\r\n\r\nDear %CONTOWNER%,\r\n\r\nThis E-Mail is a notification that a Contact has been assigned to you.\r\n\r\nContact Name: %CONTFULLNAME%\r\nContact Client: %CONTCLIENTNAME%\r\nContact URL: %CONTCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCONTACT', 'Contact Assigned (Sent to Assigned Recruiter)', '%CONTOWNER%%CONTFIRSTNAME%%CONTFULLNAME%%CONTCLIENTNAME%%CONTCATSURL%', 0);
INSERT INTO `email_template` (`email_template_id`, `text`, `allow_substitution`, `tag`, `title`, `possible_variables`, `disabled`) VALUES (5, '%DATETIME%\r\n\r\nDear %CLNTOWNER%,\r\n\r\nThis E-Mail is a notification that a Client has been assigned to you.\r\n\r\nClient Name: %CLNTNAME%\r\nClient URL %CLNTCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCLIENT', 'Client Assigned (Sent to Assigned Recruiter)', '%CLNTOWNER%%CLNTNAME%%CLNTCATSURL%', 0);
INSERT INTO `email_template` (`email_template_id`, `text`, `allow_substitution`, `tag`, `title`, `possible_variables`, `disabled`) VALUES (6, '* This is an auto-generated message. Please do not reply. *\r\n%DATETIME%\r\n\r\nDear %CANDFULLNAME%,\r\n\r\nThank you for applying to the %JBODTITLE% position with our online career portal! Your application has been entered into our system and someone will review it shortly.\r\n\r\n--\r\n%SITENAME%', 1, 'EMAIL_TEMPLATE_CANDIDATEAPPLY', 'Candidate Application Received (Sent to Candidate using Career Portal)', '%CANDFIRSTNAME%%CANDFULLNAME%%JBODCLIENT%%JBODTITLE%%JBODOWNER%', 0);
INSERT INTO `email_template` (`email_template_id`, `text`, `allow_substitution`, `tag`, `title`, `possible_variables`, `disabled`) VALUES (7, '%DATETIME%\r\n\r\nDear %JBODOWNER%,\r\n\r\nThis e-mail is a notification that a candidate has applied to your job order through the online candidate portal.\r\n\r\nJob Order: %JBODTITLE%\r\nCandidate Name: %CANDFULLNAME%\r\nCandidate URL: %CANDCATSURL%\r\nJob Order URL: %JBODCATSURL%\r\n\r\n--\r\nCATS\r\n%SITENAME%', 1, 'EMAIL_TEMPLATE_CANDIDATEPORTALNEW', 'Candidate Application Received (Sent to Owner of Job Order from Career Portal)', '%CANDFIRSTNAME%%CANDFULLNAME%%JBODOWNER%%JBODTITLE%%JBODCLIENT%%JBODCATSURL%%JBODID%%CANDCATSURL%', 0);

/* Table structure for table `extension_statistics` */

CREATE TABLE `extension_statistics` (
  `extension_statistics_id` INT(11) NOT NULL AUTO_INCREMENT,
  `extension` VARCHAR(128) NOT NULL DEFAULT '',
  `action` VARCHAR(128) NOT NULL DEFAULT '',
  `user` VARCHAR(128) NOT NULL DEFAULT '',
  `date` DATE,
  PRIMARY KEY (`extension_statistics_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Data for the table `extension_statistics` */

/* Table structure for table `extra_field` */

CREATE TABLE `extra_field` (
  `extra_field_id` INT(11) NOT NULL AUTO_INCREMENT,
  `data_item_id` INT(11) DEFAULT '0',
  `field_name` VARCHAR(255),
  `value` TEXT,
  `import_id` INT(11),
  `data_item_type` INT(11) DEFAULT '0',
  PRIMARY KEY (`extra_field_id`),
  KEY `assoc_id` (`data_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Data for the table `extra_field` */

/* Table structure for table `extra_field_settings` */

CREATE TABLE `extra_field_settings` (
  `extra_field_settings_id` INT(11) NOT NULL AUTO_INCREMENT,
  `field_name` VARCHAR(255) COLLATE utf8mb4_unicode_ci,
  `import_id` INT(11),
  `date_created` DATETIME,
  `data_item_type` INT(11) DEFAULT '0',
  `extra_field_type` INT(11) NOT NULL DEFAULT '1',
  `extra_field_options` TEXT COLLATE utf8mb4_unicode_ci,
  `position` INT(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`extra_field_settings_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `extra_field_settings` */

/* Table structure for table `feedback` */

CREATE TABLE `feedback` (
  `feedback_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11),
  `date_created` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `subject` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `reply_to_address` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `reply_to_name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `feedback` TEXT COLLATE utf8mb4_unicode_ci NOT NULL,
  `archived` INT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`feedback_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `feedback` */

/* Table structure for table `history` */

CREATE TABLE `history` (
  `history_id` INT(11) NOT NULL AUTO_INCREMENT,
  `data_item_type` INT(11),
  `data_item_id` INT(11),
  `the_field` VARCHAR(64) COLLATE utf8mb4_unicode_ci,
  `previous_value` TEXT COLLATE utf8mb4_unicode_ci,
  `new_value` TEXT COLLATE utf8mb4_unicode_ci,
  `description` VARCHAR(192) COLLATE utf8mb4_unicode_ci,
  `set_date` DATETIME,
  `entered_by` INT(11),
  PRIMARY KEY (`history_id`),
  KEY `IDX_DATA_ENTERED_BY` (`entered_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `history` */

/* Table structure for table `http_log` */

CREATE TABLE `http_log` (
  `log_id` INT(11) NOT NULL AUTO_INCREMENT,
  `remote_addr` CHAR(16) NOT NULL,
  `http_user_agent` VARCHAR(255),
  `script_filename` VARCHAR(255),
  `request_method` VARCHAR(16),
  `query_string` VARCHAR(255),
  `request_uri` VARCHAR(255),
  `script_name` VARCHAR(255),
  `log_type` INT(11) NOT NULL,
  `date` DATETIME DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Data for the table `http_log` */

/* Table structure for table `http_log_types` */

CREATE TABLE `http_log_types` (
  `log_type_id` INT(11) NOT NULL,
  `name` VARCHAR(16) NOT NULL,
  `description` VARCHAR(255),
  `default_log_type` TINYINT(1) UNSIGNED ZEROFILL NOT NULL DEFAULT '0',
  PRIMARY KEY (`log_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

/* Data for the table `http_log_types` */

INSERT INTO `http_log_types` (`log_type_id`, `name`, `description`, `default_log_type`) VALUES (1, 'XML', 'XML Job Feed', 0);

/* Table structure for table `import` */

CREATE TABLE `import` (
  `import_id` INT(11) NOT NULL AUTO_INCREMENT,
  `module_name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `reverted` INT(1) NOT NULL DEFAULT '0',
  `import_errors` TEXT COLLATE utf8mb4_unicode_ci,
  `added_lines` INT(11),
  `date_created` DATETIME,
  PRIMARY KEY (`import_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `import` */

/* Table structure for table `installtest` */

CREATE TABLE `installtest` (
  `id` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `installtest` */

/* Table structure for table `joborder` */

CREATE TABLE `joborder` (
  `joborder_id` INT(11) NOT NULL AUTO_INCREMENT,
  `recruiter` INT(11),
  `contact_id` INT(11),
  `company_id` INT(11),
  `entered_by` INT(11) NOT NULL DEFAULT '0',
  `owner` INT(11),
  `client_job_id` VARCHAR(32) COLLATE utf8mb4_unicode_ci,
  `title` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` TEXT COLLATE utf8mb4_unicode_ci,
  `notes` TEXT COLLATE utf8mb4_unicode_ci,
  `type` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'C',
  `duration` VARCHAR(64) COLLATE utf8mb4_unicode_ci,
  `rate_max` VARCHAR(255) COLLATE utf8mb4_unicode_ci,
  `salary` VARCHAR(64) COLLATE utf8mb4_unicode_ci,
  `status` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `is_hot` INT(1) NOT NULL DEFAULT '0',
  `openings` INT(11),
  `city` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `state` VARCHAR(64) COLLATE utf8mb4_unicode_ci,
  `country` VARCHAR(2) COLLATE utf8mb4_unicode_ci,
  `start_date` DATETIME,
  `date_created` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `date_modified` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `public` INT(1) NOT NULL DEFAULT '0',
  `company_department_id` INT(11),
  `is_admin_hidden` INT(1) DEFAULT '0',
  `openings_available` INT(11) DEFAULT '0',
  `questionnaire_id` INT(11),
  `import_id` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`joborder_id`),
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
  KEY `IDX_date_modified` (`date_modified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `joborder` */

/* Table structure for table `module_schema` */

CREATE TABLE `module_schema` (
  `module_schema_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) COLLATE utf8mb4_unicode_ci,
  `version` INT(11),
  PRIMARY KEY (`module_schema_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `module_schema` */

INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (1, 'activity', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (2, 'attachments', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (3, 'calendar', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (4, 'candidates', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (5, 'careers', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (6, 'companies', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (7, 'contacts', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (8, 'export', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (9, 'extension-statistics', 1);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (10, 'graphs', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (11, 'home', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (12, 'import', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`) VALUES (13, 'install');
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (14, 'joborders', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (15, 'lists', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (16, 'login', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (17, 'queue', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (18, 'reports', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (19, 'rss', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (20, 'settings', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (21, 'tests', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (22, 'wizard', 0);
INSERT INTO `module_schema` (`module_schema_id`, `name`, `version`) VALUES (23, 'xml', 0);

/* Table structure for table `mru` */

CREATE TABLE `mru` (
  `mru_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11),
  `data_item_type` INT(11) NOT NULL DEFAULT '0',
  `data_item_text` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `url` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `date_created` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`mru_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `mru` */

/* Table structure for table `queue` */

CREATE TABLE `queue` (
  `queue_id` INT(11) NOT NULL AUTO_INCREMENT,
  `task` VARCHAR(125) NOT NULL,
  `args` TEXT,
  `priority` TINYINT(2) NOT NULL DEFAULT '5' COMMENT '1-5, 1 is highest priority',
  `date_created` DATETIME NOT NULL,
  `date_timeout` DATETIME NOT NULL,
  `date_completed` DATETIME,
  `locked` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `error` TINYINT(1) UNSIGNED DEFAULT '0',
  `response` VARCHAR(255),
  PRIMARY KEY (`queue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Data for the table `queue` */

/* Table structure for table `saved_list` */

CREATE TABLE `saved_list` (
  `saved_list_id` INT(11) NOT NULL AUTO_INCREMENT,
  `description` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `data_item_type` INT(11) NOT NULL DEFAULT '0',
  `is_dynamic` INT(1) DEFAULT '0',
  `datagrid_instance` VARCHAR(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `parameters` TEXT COLLATE utf8mb4_unicode_ci,
  `created_by` INT(11) DEFAULT '0',
  `number_entries` INT(11) DEFAULT '0',
  `date_created` DATETIME,
  `date_modified` DATETIME,
  PRIMARY KEY (`saved_list_id`),
  KEY `IDX_data_item_type` (`data_item_type`),
  KEY `IDX_description` (`description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `saved_list` */

/* Table structure for table `saved_list_entry` */

CREATE TABLE `saved_list_entry` (
  `saved_list_entry_id` INT(11) NOT NULL AUTO_INCREMENT,
  `saved_list_id` INT(11) NOT NULL,
  `data_item_type` INT(11) NOT NULL DEFAULT '0',
  `data_item_id` INT(11) NOT NULL DEFAULT '0',
  `date_created` DATETIME,
  PRIMARY KEY (`saved_list_entry_id`),
  KEY `IDX_type_id` (`data_item_type`, `data_item_id`),
  KEY `IDX_data_item_type` (`data_item_type`),
  KEY `IDX_data_item_id` (`data_item_id`),
  KEY `IDX_hot_list_id` (`saved_list_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `saved_list_entry` */

/* Table structure for table `saved_search` */

CREATE TABLE `saved_search` (
  `search_id` INT(11) NOT NULL AUTO_INCREMENT,
  `data_item_text` TEXT COLLATE utf8mb4_unicode_ci,
  `url` TEXT COLLATE utf8mb4_unicode_ci,
  `is_custom` INT(1),
  `data_item_type` INT(11),
  `user_id` INT(11),
  `date_created` DATETIME,
  PRIMARY KEY (`search_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `saved_search` */

/* Table structure for table `settings` */

CREATE TABLE `settings` (
  `settings_id` INT(11) NOT NULL AUTO_INCREMENT,
  `setting` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `value` VARCHAR(255) COLLATE utf8mb4_unicode_ci,
  `settings_type` INT(11) DEFAULT '0',
  PRIMARY KEY (`settings_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `settings` */

INSERT INTO `settings` (`settings_id`, `setting`, `value`, `settings_type`) VALUES (1, 'fromAddress', 'admin@example.com', 1);
INSERT INTO `settings` (`settings_id`, `setting`, `value`, `settings_type`) VALUES (3, 'configured', '1', 1);

/* Table structure for table `site` */

CREATE TABLE `site` (
  `site_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `is_demo` INT(1) NOT NULL DEFAULT '0',
  `user_licenses` INT(11) NOT NULL DEFAULT '0',
  `entered_by` INT(11) NOT NULL DEFAULT '0',
  `date_created` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `unix_name` VARCHAR(128) CHARACTER SET utf8mb4,
  `company_id` INT(11),
  `is_free` INT(1),
  `account_active` INT(1) NOT NULL DEFAULT '1',
  `account_deleted` INT(1) NOT NULL DEFAULT '0',
  `reason_disabled` TEXT CHARACTER SET utf8mb4,
  `time_zone` INT(5) DEFAULT '0',
  `time_zone_iana` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UTC',
  `time_format_24` INT(1) DEFAULT '0',
  `date_format_ddmmyy` INT(1) DEFAULT '0',
  `default_phone_country_code` VARCHAR(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '+1',
  `is_hr_mode` INT(1) DEFAULT '0',
  `file_size_kb` INT(11) DEFAULT '0',
  `page_views` BIGINT(20) DEFAULT '0',
  `page_view_days` INT(11) DEFAULT '0',
  `last_viewed_day` DATE NOT NULL DEFAULT '1000-01-01',
  `first_time_setup` TINYINT(4) DEFAULT '0',
  `localization_configured` INT(1) DEFAULT '0',
  `agreed_to_license` INT(1) DEFAULT '0',
  `limit_warning` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`site_id`),
  KEY `IDX_account_deleted` (`account_deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `site` */

INSERT INTO `site` (`site_id`, `name`, `is_demo`, `user_licenses`, `entered_by`, `date_created`, `unix_name`, `company_id`, `is_free`, `account_active`, `account_deleted`, `reason_disabled`, `time_zone`, `time_zone_iana`, `time_format_24`, `date_format_ddmmyy`, `is_hr_mode`, `file_size_kb`, `page_views`, `page_view_days`, `last_viewed_day`, `first_time_setup`, `localization_configured`, `agreed_to_license`, `limit_warning`) VALUES (1, 'example.com', 0, 0, 0, '1000-01-01 00:00:00', NULL, NULL, 0, 1, 0, NULL, 2, 'UTC', 0, 1, 0, 0, 574, 1, '1000-01-01', 0, 0, 1, 0);

/* Table structure for table `sph_counter` */

CREATE TABLE `sph_counter` (
  `counter_id` INT(11) NOT NULL,
  `max_doc_id` INT(11) NOT NULL,
  PRIMARY KEY (`counter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Data for the table `sph_counter` */

/* Table structure for table `system` */

CREATE TABLE `system` (
  `system_id` INT(20) NOT NULL DEFAULT '0',
  `uid` INT(20),
  `available_version` INT(11) DEFAULT '0',
  `date_version_checked` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `available_version_description` TEXT COLLATE utf8mb4_unicode_ci,
  `disable_version_check` INT(1) DEFAULT '0',
  PRIMARY KEY (`system_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `system` */

INSERT INTO `system` (`system_id`, `uid`, `available_version`, `date_version_checked`, `available_version_description`, `disable_version_check`) VALUES (0, 0, 0, '1000-01-01 00:00:00', '', 1);

/* Table structure for table `tag` */

CREATE TABLE `tag` (
  `tag_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tag_parent_id` INT(10) UNSIGNED,
  `title` VARCHAR(255),
  `description` VARCHAR(500),
  `date_created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `tag` */

/* Table structure for table `user` */

CREATE TABLE `user` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_name` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `email` VARCHAR(128) COLLATE utf8mb4_unicode_ci,
  `password` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `access_level` INT(11) NOT NULL DEFAULT '100',
  `can_change_password` INT(1) NOT NULL DEFAULT '1',
  `is_test_user` INT(1) NOT NULL DEFAULT '0',
  `last_name` VARCHAR(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `first_name` VARCHAR(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `is_demo` INT(1) DEFAULT '0',
  `categories` VARCHAR(192) COLLATE utf8mb4_unicode_ci,
  `session_cookie` VARCHAR(256) COLLATE utf8mb4_unicode_ci,
  `pipeline_entries_per_page` INT(8) DEFAULT '15',
  `column_preferences` LONGTEXT COLLATE utf8mb4_unicode_ci,
  `force_logout` INT(1) DEFAULT '0',
  `title` VARCHAR(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `phone_work` VARCHAR(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `phone_cell` VARCHAR(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `phone_other` VARCHAR(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `address` TEXT COLLATE utf8mb4_unicode_ci,
  `notes` TEXT COLLATE utf8mb4_unicode_ci,
  `company` VARCHAR(255) COLLATE utf8mb4_unicode_ci,
  `city` VARCHAR(64) COLLATE utf8mb4_unicode_ci,
  `state` VARCHAR(64) COLLATE utf8mb4_unicode_ci,
  `zip_code` VARCHAR(16) COLLATE utf8mb4_unicode_ci,
  `country` VARCHAR(128) COLLATE utf8mb4_unicode_ci,
  `can_see_eeo_info` INT(1) DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `IDX_first_name` (`first_name`),
  KEY `IDX_last_name` (`last_name`),
  KEY `IDX_access_level` (`access_level`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `user` */

INSERT INTO `user` (`user_id`, `user_name`, `email`, `password`, `access_level`, `can_change_password`, `is_test_user`, `last_name`, `first_name`, `is_demo`, `categories`, `session_cookie`, `pipeline_entries_per_page`, `column_preferences`, `force_logout`, `title`, `phone_work`, `phone_cell`, `phone_other`, `address`, `notes`, `company`, `city`, `state`, `zip_code`, `country`, `can_see_eeo_info`) VALUES (1, 'admin', 'admin@example.com', md5('cats'), 500, 1, 0, 'Administrator', 'CATS', 0, NULL, NULL, 15, NULL, 0, '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);

/* Table structure for table `user_login` */

CREATE TABLE `user_login` (
  `user_login_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11),
  `ip` VARCHAR(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_agent` VARCHAR(255) COLLATE utf8mb4_unicode_ci,
  `date` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  `successful` INT(1) NOT NULL DEFAULT '0',
  `host` VARCHAR(255) COLLATE utf8mb4_unicode_ci,
  `date_refreshed` DATETIME,
  PRIMARY KEY (`user_login_id`),
  KEY `IDX_user_id` (`user_id`),
  KEY `IDX_ip` (`ip`),
  KEY `IDX_date` (`date`),
  KEY `IDX_date_refreshed` (`date_refreshed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `user_login` */

/* Table structure for table `word_verification` */

CREATE TABLE `word_verification` (
  `word_verification_ID` INT(11) NOT NULL AUTO_INCREMENT,
  `word` VARCHAR(28) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`word_verification_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `word_verification` */

/* Table structure for table `xml_feed_submits` */

CREATE TABLE `xml_feed_submits` (
  `feed_id` INT(11) NOT NULL AUTO_INCREMENT,
  `feed_site` VARCHAR(75) NOT NULL,
  `feed_url` VARCHAR(255) NOT NULL,
  `date_last_post` DATE NOT NULL,
  PRIMARY KEY (`feed_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Data for the table `xml_feed_submits` */

/* Table structure for table `xml_feeds` */

CREATE TABLE `xml_feeds` (
  `xml_feed_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `description` VARCHAR(255),
  `website` VARCHAR(255),
  `post_url` VARCHAR(255) NOT NULL,
  `success_string` VARCHAR(255) NOT NULL,
  `xml_template_name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`xml_feed_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

/* Data for the table `xml_feeds` */

INSERT INTO `xml_feeds` (`xml_feed_id`, `name`, `description`, `website`, `post_url`, `success_string`, `xml_template_name`) VALUES (1, 'Indeed', 'Indeed.com job search engine.', 'http://www.indeed.com', 'http://www.indeed.com/jsp/includejobs.jsp', 'Thank you for submitting your XML job feed', 'indeed');
INSERT INTO `xml_feeds` (`xml_feed_id`, `name`, `description`, `website`, `post_url`, `success_string`, `xml_template_name`) VALUES (2, 'SimplyHired', 'SimplyHired.com job search engine', 'http://www.simplyhired.com', 'http://www.simplyhired.com/confirmation.php', 'Thanks for Contacting Us', 'simplyhired');

/* Table structure for table `zipcodes` */

CREATE TABLE `zipcodes` (
  `zipcode` MEDIUMINT(9) NOT NULL DEFAULT '0',
  `city` TINYTEXT COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` VARCHAR(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `areacode` SMALLINT(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`zipcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Data for the table `zipcodes` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
