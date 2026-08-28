/*
SQLyog Enterprise - MySQL GUI v8.02 RC
MySQL - 5.1.31-community : Database - cats_dev
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*Table structure for table `access_level` */

CREATE TABLE `access_level` (
  `access_level_id` int(11) NOT NULL DEFAULT '0',
  `short_description` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `long_description` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`access_level_id`),
  KEY `IDX_access_level` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `access_level` */

insert  into `access_level`(`access_level_id`,`short_description`,`long_description`) values (0,'Account Disabled','Disabled - The lowest access level. User cannot log in.');
insert  into `access_level`(`access_level_id`,`short_description`,`long_description`) values (100,'Read Only','Read Only - A standard user that can view data on the system in a read-only mode.');
insert  into `access_level`(`access_level_id`,`short_description`,`long_description`) values (200,'Add / Edit','Edit - All lower access, plus the ability to edit information on the system.');
insert  into `access_level`(`access_level_id`,`short_description`,`long_description`) values (300,'Add / Edit / Delete','Delete - All lower access, plus the ability to delete information on the system.');
insert  into `access_level`(`access_level_id`,`short_description`,`long_description`) values (400,'Site Administrator','Site Administrator - All lower access, plus the ability to add, edit, and remove site users, as well as the ability to edit site settings.');
insert  into `access_level`(`access_level_id`,`short_description`,`long_description`) values (500,'Root','Root Administrator - All lower access, plus the ability to add, edit, and remove sites, as well as the ability to assign Site Administrator status to a user.');

/*Table structure for table `activity` */

CREATE TABLE `activity` (
  `activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `data_item_id` int(11) NOT NULL DEFAULT '0',
  `data_item_type` int(11) NOT NULL DEFAULT '0',
  `joborder_id` int(11) DEFAULT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `entered_by` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `type` int(11) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8_unicode_ci,
  `date_modified` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`activity_id`),
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

/*Data for the table `activity` */

/*Table structure for table `activity_type` */

CREATE TABLE `activity_type` (
  `activity_type_id` int(11) NOT NULL DEFAULT '0',
  `short_description` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`activity_type_id`),
  KEY `IDX_activity_type1` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `activity_type` */

insert  into `activity_type`(`activity_type_id`,`short_description`) values (100,'Call');
insert  into `activity_type`(`activity_type_id`,`short_description`) values (200,'Email');
insert  into `activity_type`(`activity_type_id`,`short_description`) values (300,'Meeting');
insert  into `activity_type`(`activity_type_id`,`short_description`) values (400,'Other');
insert  into `activity_type`(`activity_type_id`,`short_description`) values (500,'Call (Talked)');
insert  into `activity_type`(`activity_type_id`,`short_description`) values (600,'Call (LVM)');
insert  into `activity_type`(`activity_type_id`,`short_description`) values (700,'Call (Missed)');

/*Table structure for table `attachment` */

CREATE TABLE `attachment` (
  `attachment_id` int(11) NOT NULL AUTO_INCREMENT,
  `data_item_id` int(11) NOT NULL DEFAULT '0',
  `data_item_type` int(11) NOT NULL DEFAULT '0',
  `site_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `original_filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `stored_filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `content_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resume` int(1) NOT NULL DEFAULT '0',
  `text` text COLLATE utf8_unicode_ci,
  `date_created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `date_modified` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `profile_image` int(1) DEFAULT '0',
  `directory_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `md5_sum` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `file_size_kb` int(11) DEFAULT '0',
  `md5_sum_text` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`attachment_id`),
  KEY `IDX_type_id` (`data_item_type`,`data_item_id`),
  KEY `IDX_data_item_id` (`data_item_id`),
  KEY `IDX_CANDIDATE_MD5_SUM` (`md5_sum`),
  KEY `IDX_site_file_size` (`site_id`,`file_size_kb`),
  KEY `IDX_site_file_size_created` (`site_id`,`file_size_kb`,`date_created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `attachment` */

/*Table structure for table `calendar_event` */

CREATE TABLE `calendar_event` (
  `calendar_event_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `title` text COLLATE utf8_unicode_ci NOT NULL,
  `all_day` int(1) NOT NULL DEFAULT '0',
  `data_item_id` int(11) NOT NULL DEFAULT '-1',
  `data_item_type` int(11) NOT NULL DEFAULT '-1',
  `entered_by` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `date_modified` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `site_id` int(11) NOT NULL DEFAULT '0',
  `joborder_id` int(11) NOT NULL DEFAULT '-1',
  `description` text COLLATE utf8_unicode_ci,
  `duration` int(11) NOT NULL DEFAULT '60',
  `reminder_enabled` int(1) NOT NULL DEFAULT '0',
  `reminder_email` text COLLATE utf8_unicode_ci,
  `reminder_time` int(11) DEFAULT '0',
  `public` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`calendar_event_id`),
  KEY `IDX_site_id_date` (`site_id`,`date`),
  KEY `IDX_site_data_item_type_id` (`site_id`,`data_item_type`,`data_item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `calendar_event` */

/*Table structure for table `calendar_event_type` */

CREATE TABLE `calendar_event_type` (
  `calendar_event_type_id` int(11) NOT NULL DEFAULT '0',
  `short_description` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `icon_image` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`calendar_event_type_id`),
  KEY `IDX_short_description` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `calendar_event_type` */

insert  into `calendar_event_type`(`calendar_event_type_id`,`short_description`,`icon_image`) values (100,'Call','images/phone.gif');
insert  into `calendar_event_type`(`calendar_event_type_id`,`short_description`,`icon_image`) values (200,'Email','images/email.gif');
insert  into `calendar_event_type`(`calendar_event_type_id`,`short_description`,`icon_image`) values (300,'Meeting','images/meeting.gif');
insert  into `calendar_event_type`(`calendar_event_type_id`,`short_description`,`icon_image`) values (400,'Interview','images/interview.gif');
insert  into `calendar_event_type`(`calendar_event_type_id`,`short_description`,`icon_image`) values (500,'Personal','images/personal.gif');
insert  into `calendar_event_type`(`calendar_event_type_id`,`short_description`,`icon_image`) values (600,'Other','');

/*Table structure for table `candidate` */

CREATE TABLE `candidate` (
  `candidate_id` int(11) NOT NULL AUTO_INCREMENT,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `last_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `first_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `middle_name` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone_home` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone_cell` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone_work` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8_unicode_ci,
  `city` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `source` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_available` datetime DEFAULT NULL,
  `can_relocate` int(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8_unicode_ci,
  `key_skills` text COLLATE utf8_unicode_ci,
  `current_employer` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entered_by` int(11) NOT NULL DEFAULT '0' COMMENT 'Created-by user.',
  `owner` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `date_modified` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `email1` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email2` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `web_site` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `import_id` int(11) NOT NULL DEFAULT '0',
  `is_hot` int(1) NOT NULL DEFAULT '0',
  `eeo_ethnic_type_id` int(11) DEFAULT '0',
  `eeo_veteran_type_id` int(11) DEFAULT '0',
  `eeo_disability_status` varchar(5) COLLATE utf8_unicode_ci DEFAULT '',
  `eeo_gender` varchar(5) COLLATE utf8_unicode_ci DEFAULT '',
  `desired_pay` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `current_pay` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_active` int(1) DEFAULT '1',
  `is_admin_hidden` int(1) DEFAULT '0',
  `best_time_to_call` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
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
  KEY `IDX_date_modified` (`date_modified`),
  KEY `IDX_site_first_last_modified` (`site_id`,`first_name`,`last_name`,`date_modified`),
  KEY `IDX_site_id_email_1_2` (`site_id`,`email1`(8),`email2`(8))
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `candidate` */

/*Table structure for table `candidate_joborder` */

CREATE TABLE `candidate_joborder` (
  `candidate_joborder_id` int(11) NOT NULL AUTO_INCREMENT,
  `candidate_id` int(11) NOT NULL DEFAULT '0',
  `joborder_id` int(11) NOT NULL DEFAULT '0',
  `site_id` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `date_submitted` datetime DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `date_modified` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `rating_value` int(5) DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`candidate_joborder_id`),
  KEY `IDX_candidate_id` (`candidate_id`),
  KEY `IDX_site_id` (`site_id`),
  KEY `IDX_date_submitted` (`date_submitted`),
  KEY `IDX_date_created` (`date_created`),
  KEY `IDX_date_modified` (`date_modified`),
  KEY `IDX_status_special` (`site_id`,`status`),
  KEY `IDX_site_joborder` (`site_id`,`joborder_id`),
  KEY `IDX_joborder_id` (`joborder_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `candidate_joborder` */

/*Table structure for table `candidate_joborder_status` */

CREATE TABLE `candidate_joborder_status` (
  `candidate_joborder_status_id` int(11) NOT NULL DEFAULT '0',
  `short_description` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `can_be_scheduled` int(1) NOT NULL DEFAULT '0',
  `triggers_email` int(1) NOT NULL DEFAULT '1',
  `is_enabled` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`candidate_joborder_status_id`),
  KEY `IDX_short_description` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `candidate_joborder_status` */

insert  into `candidate_joborder_status`(`candidate_joborder_status_id`,`short_description`,`can_be_scheduled`,`triggers_email`,`is_enabled`) values (100,'No Contact',0,0,1);
insert  into `candidate_joborder_status`(`candidate_joborder_status_id`,`short_description`,`can_be_scheduled`,`triggers_email`,`is_enabled`) values (200,'Contacted',0,0,1);
insert  into `candidate_joborder_status`(`candidate_joborder_status_id`,`short_description`,`can_be_scheduled`,`triggers_email`,`is_enabled`) values (300,'Qualifying',0,1,1);
insert  into `candidate_joborder_status`(`candidate_joborder_status_id`,`short_description`,`can_be_scheduled`,`triggers_email`,`is_enabled`) values (400,'Submitted',0,1,1);
insert  into `candidate_joborder_status`(`candidate_joborder_status_id`,`short_description`,`can_be_scheduled`,`triggers_email`,`is_enabled`) values (500,'Interviewing',0,1,1);
insert  into `candidate_joborder_status`(`candidate_joborder_status_id`,`short_description`,`can_be_scheduled`,`triggers_email`,`is_enabled`) values (600,'Offered',0,1,1);
insert  into `candidate_joborder_status`(`candidate_joborder_status_id`,`short_description`,`can_be_scheduled`,`triggers_email`,`is_enabled`) values (700,'Client Declined',0,0,1);
insert  into `candidate_joborder_status`(`candidate_joborder_status_id`,`short_description`,`can_be_scheduled`,`triggers_email`,`is_enabled`) values (800,'Placed',0,1,1);
insert  into `candidate_joborder_status`(`candidate_joborder_status_id`,`short_description`,`can_be_scheduled`,`triggers_email`,`is_enabled`) values (0,'No Status',0,0,1);
insert  into `candidate_joborder_status`(`candidate_joborder_status_id`,`short_description`,`can_be_scheduled`,`triggers_email`,`is_enabled`) values (650,'Not in Consideration',0,0,1);
insert  into `candidate_joborder_status`(`candidate_joborder_status_id`,`short_description`,`can_be_scheduled`,`triggers_email`,`is_enabled`) values (250,'Candidate Responded',0,0,1);

/*Table structure for table `candidate_joborder_status_history` */

CREATE TABLE `candidate_joborder_status_history` (
  `candidate_joborder_status_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `candidate_id` int(11) NOT NULL DEFAULT '0',
  `joborder_id` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `status_from` int(11) NOT NULL DEFAULT '0',
  `status_to` int(11) NOT NULL DEFAULT '0',
  `site_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`candidate_joborder_status_history_id`),
  KEY `IDX_site_id` (`site_id`),
  KEY `IDX_status_to` (`status_to`),
  KEY `IDX_status_to_site_id` (`status_to`,`site_id`),
  KEY `IDX_candidate_joborder_status_to_site` (`candidate_id`,`joborder_id`,`status_to`,`site_id`),
  KEY `IDX_joborder_site` (`joborder_id`,`site_id`),
  KEY `IDX_site_joborder_status_to` (`site_id`,`joborder_id`,`status_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `candidate_joborder_status_history` */

/*Table structure for table `candidate_jobordrer_status_type` */

CREATE TABLE `candidate_jobordrer_status_type` (
  `candidate_status_type_id` int(11) NOT NULL DEFAULT '0',
  `short_description` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `can_be_scheduled` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`candidate_status_type_id`),
  KEY `IDX_short_description` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `candidate_jobordrer_status_type` */

/*Table structure for table `candidate_source` */

CREATE TABLE `candidate_source` (
  `source_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `site_id` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`source_id`),
  KEY `siteID` (`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `candidate_source` */

/*Table structure for table `candidate_tag` */

CREATE TABLE `candidate_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned DEFAULT NULL,
  `candidate_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `candidate_tag` */

insert  into `candidate_tag`(`id`,`site_id`,`candidate_id`,`tag_id`) values (55,1,1,5);
insert  into `candidate_tag`(`id`,`site_id`,`candidate_id`,`tag_id`) values (56,1,1,78);
insert  into `candidate_tag`(`id`,`site_id`,`candidate_id`,`tag_id`) values (57,1,1,80);
insert  into `candidate_tag`(`id`,`site_id`,`candidate_id`,`tag_id`) values (58,1,1,81);

/*Table structure for table `career_portal_questionnaire` */

CREATE TABLE `career_portal_questionnaire` (
  `career_portal_questionnaire_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `site_id` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`career_portal_questionnaire_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `career_portal_questionnaire` */

/*Table structure for table `career_portal_questionnaire_answer` */

CREATE TABLE `career_portal_questionnaire_answer` (
  `career_portal_questionnaire_answer_id` int(11) NOT NULL AUTO_INCREMENT,
  `career_portal_questionnaire_question_id` int(11) NOT NULL,
  `career_portal_questionnaire_id` int(11) NOT NULL,
  `text` varchar(255) NOT NULL DEFAULT '',
  `action_source` varchar(128) DEFAULT NULL,
  `action_notes` text,
  `action_is_hot` tinyint(1) DEFAULT '0',
  `action_is_active` tinyint(1) DEFAULT '0',
  `action_can_relocate` tinyint(1) DEFAULT '0',
  `action_key_skills` varchar(255) DEFAULT NULL,
  `position` int(4) NOT NULL DEFAULT '0',
  `site_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`career_portal_questionnaire_answer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `career_portal_questionnaire_answer` */

/*Table structure for table `career_portal_questionnaire_history` */

CREATE TABLE `career_portal_questionnaire_history` (
  `career_portal_questionnaire_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `candidate_id` int(11) NOT NULL DEFAULT '0',
  `question` varchar(255) NOT NULL DEFAULT '',
  `answer` varchar(255) NOT NULL DEFAULT '',
  `questionnaire_title` varchar(255) NOT NULL DEFAULT '',
  `questionnaire_description` varchar(255) NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`career_portal_questionnaire_history_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `career_portal_questionnaire_history` */

/*Table structure for table `career_portal_questionnaire_question` */

CREATE TABLE `career_portal_questionnaire_question` (
  `career_portal_questionnaire_question_id` int(11) NOT NULL AUTO_INCREMENT,
  `career_portal_questionnaire_id` int(11) NOT NULL,
  `text` varchar(255) NOT NULL DEFAULT '',
  `minimum_length` int(11) DEFAULT NULL,
  `maximum_length` int(11) DEFAULT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `position` int(4) NOT NULL DEFAULT '0',
  `site_id` int(11) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`career_portal_questionnaire_question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `career_portal_questionnaire_question` */

/*Table structure for table `career_portal_template` */

CREATE TABLE `career_portal_template` (
  `career_portal_template_id` int(11) NOT NULL AUTO_INCREMENT,
  `career_portal_name` varchar(255) DEFAULT NULL,
  `setting` varchar(128) NOT NULL DEFAULT '',
  `value` text,
  PRIMARY KEY (`career_portal_template_id`)
) ENGINE=MyISAM AUTO_INCREMENT=78 DEFAULT CHARSET=utf8;

/*Data for the table `career_portal_template` */

insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (56,'Blank Page','Left','');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (57,'Blank Page','Footer','');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (58,'Blank Page','Header','');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (59,'Blank Page','Content - Main','');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (60,'Blank Page','CSS','');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (61,'Blank Page','Content - Search Results','');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (62,'Blank Page','Content - Questionnaire','');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (63,'Blank Page','Content - Job Details','');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (64,'Blank Page','Content - Thanks for your Submission','');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (65,'Blank Page','Content - Apply for Position','');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (66,'CATS 2.0','Left','');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (67,'CATS 2.0','Footer','</div>');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (68,'CATS 2.0','Header','<div id=\"container\">\r\n	<div id=\"logo\"><img src=\"images/careers_cats.gif\" alt=\"IMAGE: CATS Applicant Tracking System Careers Page\" /></div>\r\n    <div id=\"actions\">\r\n    	<h2>Shortcuts:</h2>\r\n        <a href=\"index.php\" onmouseover=\"buttonMouseOver(\'returnToMain\',true);\" onmouseout=\"buttonMouseOver(\'returnToMain\',false);\"><img src=\"images/careers_return.gif\" id=\"returnToMain\" alt=\"IMAGE: Return to Main\" /></a>\r\n<a href=\"<rssURL>\" onmouseover=\"buttonMouseOver(\'rssFeed\',true);\" onmouseout=\"buttonMouseOver(\'rssFeed\',false);\"><img src=\"images/careers_rss.gif\" id=\"rssFeed\" alt=\"IMAGE: RSS Feed\" /></a>\r\n        <a href=\"index.php?m=careers&p=showAll\" onmouseover=\"buttonMouseOver(\'showAllJobs\',true);\" onmouseout=\"buttonMouseOver(\'showAllJobs\',false);\"><img src=\"images/careers_show.gif\" id=\"showAllJobs\" alt=\"IMAGE: Show All Jobs\" /></a>\r\n    </div>');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (69,'CATS 2.0','Content - Main','<div id=\"careerContent\">\r\n        <registeredCandidate>\r\n        <h1>Available Openings at <siteName></h1>\r\n        <div id=\"descriptive\">\r\n               <p>Change your life today by becoming an integral part of our winning team.</p>\r\n               <p>If you are interested, we invite you to view the <a href=\"index.php?m=careers&p=showAll\">current opening positions</a> at our company.</p><br /><br /><registeredLoginTitle><h1 style=\"padding:0;margin:0;border:0\">Have you applied with us before?</h1></registeredLoginTitle><registeredLogin>\r\n        </div>\r\n        <div id=\"detailsTools\">\r\n        	<h2>Perform an action:</h2>\r\n        	<ul>\r\n                    <li><a href=\"\">Visit our website</a></li>\r\n                </ul>\r\n        </div>\r\n</div>');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (70,'CATS 2.0','CSS','table.sortable\r\n{\r\ntext-align:left;\r\nempty-cells: show;\r\nwidth: 940px;\r\n}\r\ntd\r\n{\r\npadding:5px;\r\n}\r\ntr.rowHeading\r\n{\r\n background: #e0e0e0; border: 1px solid #cccccc; border-left: none; border-right: none;\r\n}\r\ntr.oddTableRow\r\n{\r\nbackground: #ebebeb; \r\n}\r\ntr.evenTableRow\r\n{\r\n background: #ffffff; \r\n}\r\na.sortheader:hover,\r\na.sortheader:link,\r\na.sortheader:visited\r\n{\r\ncolor:#000;\r\n}\r\n\r\nbody, html { margin: 0; padding: 0; background: #ffffff; font: normal 12px/14px Arial, Helvetica, sans-serif; color: #000000; }\r\n#container { margin: 0 auto; padding: 0; width: 940px; height: auto; }\r\n#logo { float: left; margin: 0; }\r\n	#logo img { width: 424px; height: 103px; }\r\n#actions { float: right; margin: 0; width: 310px; height: 100px; background: #efefef; border: 1px solid #cccccc; }\r\n	#actions img { float: left; margin: 2px 6px 2px 15px; width: 130px; height: 25px; }\r\n#footer { clear: both; margin: 20px auto 0 auto; width: 150px; }\r\n	#footer img { width: 137px; height: 38px; }\r\n\r\na:link, a:active { color: #1763b9; }\r\na:hover { color: #c75a01; }\r\na:visited { color: #333333; }\r\nimg { border: none; }\r\n\r\nh1 { margin: 0 0 10px 0; font: bold 18px Arial, Helvetica, sans-serif; }\r\nh2 { margin: 8px 0 8px 15px; font: bold 14px Arial, Helvetica, sans-serif; }\r\nh3 { margin: 0; font: bold 14px Arial, Helvetica, sans-serif; }\r\np { font: normal 12px Arial, Helvetica, sans-serif; }\r\np.instructions { margin: 0 0 0 10px; font: italic 12px Arial, Helvetica, sans-serif; color: #666666; }\r\n\r\n\r\n/* CONTENTS ON PAGE SPECS */\r\n#careerContent { clear: both; padding: 15px 0 0 0; }\r\n\r\n	\r\n/* DISPLAY JOB DETAILS */\r\n#detailsTable { width: 400px; }\r\n	#detailsTable td.detailsHeader { width: 30%; }\r\ndiv#descriptive { float: left; width: 585px; }\r\ndiv#detailsTools { float: right; padding: 0 0 8px 0; width: 280px; background: #ffffff; border: 1px solid #cccccc; }\r\n	div#detailsTools img { margin: 2px 6px 5px 15px;  }\r\n\r\n/* DISPLAY APPLICATION FORM */\r\ndiv.applyBoxLeft, div.applyBoxRight { width: 450px; height: 470px; background: #f9f9f9; border: 1px solid #cccccc; border-top: none; }\r\ndiv.applyBoxLeft { float: left; margin: 0 10px 0 0; }\r\ndiv.applyBoxRight { float: right; margin: 0 0 0 10px; }\r\n	div.applyBoxLeft div, div.applyBoxRight div { margin: 0 0 5px 0; padding: 3px 10px; background: #efefef; border-top: 1px solid #cccccc; border-bottom: 1px solid #cccccc; }\r\n	div.applyBoxLeft table, div.applyBoxRight table { margin: 0 auto; width: 420px; }\r\n	div.applyBoxLeft table td, div.applyBoxRight table td { padding: 3px; vertical-align: top; }\r\n		td.label { text-align: right; width: 110px; }\r\n        form#applyToJobForm {  }\r\n	form#applyToJobForm label { font-weight: bold; }\r\n	form#applyToJobForm input.inputBoxName, form#applyToJobForm input.inputBoxNormal { width: 285px; height: 15px; }\r\n        form#applyToJobForm input.submitButton { width: 197px; height: 27px; background: url(\'images/careers_submit.gif\') no-repeat; }\r\n\r\n        form#applyToJobForm input.submitButtonDown { width: 197px; height: 27px; background: url(\'images/careers_submit-o.gif\') no-repeat; }\r\n	form#applyToJobForm textarea { margin: 8px 0 0 0; width: 410px; height: 170px; }\r\n	form#applyToJobForm textarea.inputBoxArea{ width: 285px; height: 70px; }\r\n\r\n');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (71,'CATS 2.0','Content - Search Results','<div id=\"careerContent\">\r\n        <registeredCandidate>\r\n        <h1>Current Available Openings, Recently Posted Jobs: <numberOfSearchResults></h1>\r\n<searchResultsTable>\r\n    </div>');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (72,'CATS 2.0','Content - Questionnaire','<div id=\"careerContent\">\r\n<questionnaire>\r\n<br /><br />\r\n<div style=\"text-align: right;\">\r\n<submit value=\"Continue\">\r\n</div>\r\n</div>');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (73,'CATS 2.0','Content - Job Details','<div id=\"careerContent\">\r\n        <registeredCandidate>\r\n        <h1>Position Details: <title></h1>\r\n        <table id=\"detailsTable\">\r\n            <tr>\r\n                <td class=\"detailsHeader\"><strong>Location:</strong></td>\r\n                <td><city>, <state></td>\r\n			</tr>\r\n			<tr>\r\n                <td class=\"detailsHeader\"><strong>Openings:</strong></td>\r\n                <td><openings></td>\r\n			</tr>\r\n            <tr>\r\n                <td class=\"detailsHeader\"><strong>Salary Range:</strong></td>\r\n                <td><salary></td>\r\n            </tr>\r\n        </table>\r\n        <div id=\"descriptive\">\r\n            <p><strong>Description:</strong></p>\r\n            <description>\r\n		</div>\r\n        <div id=\"detailsTools\">\r\n        	<h2>Perform an action:</h2>\r\n        	<a-applyToJob onmouseover=\"buttonMouseOver(\'applyToPosition\',true);\" onmouseout=\"buttonMouseOver(\'applyToPosition\',false);\"><img src=\"images/careers_apply.gif\" id=\"applyToPosition\" alt=\"IMAGE: Apply to Position\" /></a>\r\n        </div>\r\n    </div>');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (74,'CATS 2.0','Content - Thanks for your Submission','<div id=\"careerContent\">\r\n            <h1>Application Submitted For: <title></h1>\r\n            <div id=\"descriptive\">\r\n                <p>Please check your email inbox &#8212; You should receive an email confirmation of your application.</p>\r\n                <p>Thank you for submitting your application to us. We will review it shortly and make contact with you soon.</p>\r\n                </div>\r\n			<div id=\"detailsTools\">\r\n                <h2>Perform an action:</h2>\r\n                <ul>\r\n                	<li><a href=\"\">Visit our website</a></li>\r\n		</ul>\r\n        	</div>\r\n    </div>');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (75,'CATS 2.0','Content - Apply for Position','<div id=\"careerContent\">\r\n        <h1>Applying to: <title></h1>\r\n        <div class=\"applyBoxLeft\">\r\n            <div><h3>1. Import Resume (or CV) and Populate Fields</h3></div>\r\n            <table>\r\n                <tr>\r\n                    <td>\r\n                      \r\n                    <input-resumeUploadPreview>\r\n                    </td>\r\n                </tr>\r\n            </table>\r\n            <br />\r\n\r\n            <div><h3>2. Tell us about yourself</h3></div>\r\n            <p class=\"instructions\">All fields marked with asterisk (*) are required.</p>\r\n            <table>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"firstNameLabel\" for=\"firstName\">*First Name:</label></td>\r\n                    <td><input-firstName></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"lastNameLabel\" for=\"lastName\">*Last Name:</label></td>\r\n                    <td><input-lastName></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"emailLabel\" for=\"email\">*Email Adddress:</label></td>\r\n                    <td><input-email></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"emailConfirmLabel\" for=\"emailconfirm\">*Confirm Email:</label></td>\r\n                    <td><input-emailconfirm></td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n       \r\n        <div class=\"applyBoxRight\">\r\n            <div><h3>3. How may we contact you?</h3></div>\r\n            <table>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"homePhoneLabel\" for=\"homePhone\">Home Phone:</label></td>\r\n                    <td><input-phone-home></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"mobilePhoneLabel\" for=\"mobilePhone\">Mobile Phone:</label></td>\r\n                    <td><input-phone-cell></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"workPhoneLabel\" for=\"workPhone\">Work Phone:</label></td>\r\n                    <td><input-phone></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"bestTimeLabel\" for=\"bestTime\">*Best time to call:</label></td>\r\n                    <td><input-best-time-to-call></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"mailingAddressLabel\" for=\"mailingAddress\">Mailing Address:</label></td>\r\n                    <td><input-address></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"cityProvinceLabel\" for=\"cityProvince\">*City/Province:</label></td>\r\n                    <td><input-city></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"stateCountryLabel\" for=\"stateCountry\">*State/Country:</label></td>\r\n                    <td><input-state></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"zipPostalLabel\" for=\"zipPostal\">*Zip/Postal Code:</label></td>\r\n                    <td><input-zip></td>\r\n                </tr>\r\n            </table>\r\n            <br />\r\n            <div><h3>4. Additional Information</h3></div>\r\n            <table>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"keySkillsLabel\" for=\"keySkills\">*Key Skills:</label></td>\r\n                    <td><input-keySkills></td>\r\n                </tr>\r\n                <tr>\r\n                    <td>&nbsp;</td>\r\n                    <td><img src=\"images/careers_submit.gif\" onmouseover=\"buttonMouseOver(\'submitApplicationNow\',true)\" onmouseout=\"buttonMouseOver(\'submitApplicationNow\',false)\" style=\"cursor: pointer;\" id=\"submitApplicationNow\" alt=\"Submit Application Now\" onclick=\"if (applyValidate()) { document.applyToJobForm.submit(); }\" /></td>\r\n                </tr>\r\n            </table>\r\n               </div>\r\n    </div>');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (76,'CATS 2.0','Content - Candidate Registration','<div id=\"careerContent\">\r\n    <h1><applyContent>Applying to <title></applyContent></h1>\r\n    <center>\r\n    <table cellpadding=\"0\" cellspacing=\"0\">\r\n        <tr>\r\n            <td><label id=\"emailLabel\" for=\"email\"><h2>Enter your e-mail address:</h2></label></td>\r\n            <td><input-email></td>\r\n        </tr>\r\n        <tr>\r\n            <td align=\"right\" valign=\"top\"><input-new></td>\r\n            <td style=\"line-height: 18px;\">\r\n                <applyContent>\r\n                <strong>I have not registered on this website.</strong><br />\r\n                (I haven\'t applied to any jobs online)\r\n                </applyContent>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <td align=\"right\" valign=\"top\"><input-registered></td>\r\n            <td style=\"line-height: 20px;\">\r\n                <strong>I have registered before</strong><br />\r\n                and my last name is:<br />\r\n                <input-lastName><br />\r\n                and my zip code is:<br />\r\n                <input-zip><br /><br />\r\n                <input-rememberMe> Remember my information for future visits<br /><br />\r\n                <input-submit><br /><br />\r\n            </td>\r\n        </tr>\r\n    </table>\r\n    </center>\r\n</div>\r\n');
insert  into `career_portal_template`(`career_portal_template_id`,`career_portal_name`,`setting`,`value`) values (77,'CATS 2.0','Content - Candidate Profile','<div id=\"careerContent\">    <h1 style=\"padding: 0; margin: 0; border: 0;\">My Profile</h1><h3 style=\"font-weight: normal;\">Any changes you make to your profile will be updated on our website for all    past and future jobs you apply for.</h3>    <br />    <div class=\"applyBoxLeft\">        <div><h3>1. Tell us about yourself</h3></div>        <p class=\"instructions\">All fields marked with asterisk (*) are required.</p>        <table>            <tr>                <td class=\"label\"><label id=\"firstNameLabel\" for=\"firstName\">*First Name:</label></td>                <td><input-firstName></td>            </tr>            <tr>                <td class=\"label\"><label id=\"lastNameLabel\" for=\"lastName\">*Last Name:</label></td>                <td><input-lastName></td>            </tr>            <tr>                <td class=\"label\"><label id=\"emailLabel\" for=\"email\">*Email Adddress:</label></td>                <td><input-email1></td>            </tr>            <tr>                <td colspan=\"2\">                    <input-resume>                </td>            </tr>        </table>    </div>    <div class=\"applyBoxRight\">        <div><h3>2. How may we contact you?</h3></div>        <table>            <tr>                <td class=\"label\"><label id=\"homePhoneLabel\" for=\"homePhone\">Home Phone:</label></td>                <td><input-phoneHome></td>            </tr>            <tr>                <td class=\"label\"><label id=\"mobilePhoneLabel\" for=\"mobilePhone\">Mobile Phone:</label></td>                <td><input-phoneCell></td>            </tr>            <tr>                <td class=\"label\"><label id=\"workPhoneLabel\" for=\"workPhone\">Work Phone:</label></td>                <td><input-phoneWork></td>            </tr>            <tr>                <td class=\"label\"><label id=\"bestTimeLabel\" for=\"bestTime\">*Best time to call:</label></td>                <td><input-bestTimeToCall></td>            </tr>            <tr>                <td class=\"label\"><label id=\"mailingAddressLabel\" for=\"mailingAddress\">Mailing Address:</label></td>                <td><input-address></td>            </tr>            <tr>                <td class=\"label\"><label id=\"cityProvinceLabel\" for=\"cityProvince\">*City/Province:</label></td>                <td><input-city></td>            </tr>            <tr>                <td class=\"label\"><label id=\"stateCountryLabel\" for=\"stateCountry\">*State/Country:</label></td>                <td><input-state></td>            </tr>            <tr>                <td class=\"label\"><label id=\"zipPostalLabel\" for=\"zipPostal\">*Zip/Postal Code:</label></td>                <td><input-zip></td>            </tr>        </table>        <br />        <div><h3>3. Additional Information</h3></div>        <table>            <tr>                <td class=\"label\"><label id=\"keySkillsLabel\" for=\"keySkills\">*Key Skills:</label></td>                <td><input-keySkills></td>            </tr>            <tr>                <td>&nbsp;</td>                <td style=\"padding-top: 40px;\"><input-submit></td>            </tr>        </table>    </div></div>');

/*Table structure for table `career_portal_template_site` */

CREATE TABLE `career_portal_template_site` (
  `career_portal_template_id` int(11) NOT NULL AUTO_INCREMENT,
  `career_portal_name` varchar(255) DEFAULT NULL,
  `site_id` int(11) NOT NULL,
  `setting` varchar(128) NOT NULL DEFAULT '',
  `value` text,
  PRIMARY KEY (`career_portal_template_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `career_portal_template_site` */

/*Table structure for table `company` */

CREATE TABLE `company` (
  `company_id` int(11) NOT NULL AUTO_INCREMENT,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `billing_contact` int(11) DEFAULT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `address` text COLLATE utf8_unicode_ci,
  `city` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone1` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone2` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `key_technologies` text COLLATE utf8_unicode_ci,
  `notes` text COLLATE utf8_unicode_ci,
  `entered_by` int(11) DEFAULT NULL,
  `owner` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `date_modified` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `is_hot` int(1) DEFAULT NULL,
  `fax_number` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `import_id` int(11) DEFAULT NULL,
  `default_company` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`company_id`),
  KEY `IDX_site_id` (`site_id`),
  KEY `IDX_name` (`name`),
  KEY `IDX_key_technologies` (`key_technologies`(255)),
  KEY `IDX_entered_by` (`entered_by`),
  KEY `IDX_owner` (`owner`),
  KEY `IDX_date_created` (`date_created`),
  KEY `IDX_date_modified` (`date_modified`),
  KEY `IDX_is_hot` (`is_hot`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `company` */

insert  into `company`(`company_id`,`site_id`,`billing_contact`,`name`,`address`,`city`,`state`,`zip`,`phone1`,`phone2`,`url`,`key_technologies`,`notes`,`entered_by`,`owner`,`date_created`,`date_modified`,`is_hot`,`fax_number`,`import_id`,`default_company`) values (1,1,NULL,'Internal Postings','','','','','','','','','',0,0,'2009-11-19 10:00:20','2009-11-19 10:00:20',0,'',NULL,1);

/*Table structure for table `company_department` */

CREATE TABLE `company_department` (
  `company_department_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`company_department_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `company_department` */

/*Table structure for table `contact` */

CREATE TABLE `contact` (
  `contact_id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `last_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `first_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `title` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email1` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email2` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone_work` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone_cell` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone_other` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8_unicode_ci,
  `city` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_hot` int(1) DEFAULT NULL,
  `notes` text COLLATE utf8_unicode_ci,
  `entered_by` int(11) NOT NULL DEFAULT '0',
  `owner` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `date_modified` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `left_company` int(1) NOT NULL DEFAULT '0',
  `import_id` int(11) NOT NULL DEFAULT '0',
  `company_department_id` int(11) NOT NULL,
  `reports_to` int(11) DEFAULT '-1',
  PRIMARY KEY (`contact_id`),
  KEY `IDX_site_id` (`site_id`),
  KEY `IDX_first_name` (`first_name`),
  KEY `IDX_last_name` (`last_name`),
  KEY `IDX_client_id` (`company_id`),
  KEY ` IDX_title` (`title`),
  KEY `IDX_owner` (`owner`),
  KEY `IDX_date_created` (`date_created`),
  KEY `IDX_date_modified` (`date_modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `contact` */

/*Table structure for table `data_item_type` */

CREATE TABLE `data_item_type` (
  `data_item_type_id` int(11) NOT NULL DEFAULT '0',
  `short_description` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`data_item_type_id`),
  KEY `IDX_short_description` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `data_item_type` */

insert  into `data_item_type`(`data_item_type_id`,`short_description`) values (100,'Candidate');
insert  into `data_item_type`(`data_item_type_id`,`short_description`) values (200,'Company');
insert  into `data_item_type`(`data_item_type_id`,`short_description`) values (300,'Contact');
insert  into `data_item_type`(`data_item_type_id`,`short_description`) values (400,'Job Order');

/*Table structure for table `eeo_ethnic_type` */

CREATE TABLE `eeo_ethnic_type` (
  `eeo_ethnic_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`eeo_ethnic_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Data for the table `eeo_ethnic_type` */

insert  into `eeo_ethnic_type`(`eeo_ethnic_type_id`,`type`) values (1,'American Indian');
insert  into `eeo_ethnic_type`(`eeo_ethnic_type_id`,`type`) values (2,'Asian or Pacific Islander');
insert  into `eeo_ethnic_type`(`eeo_ethnic_type_id`,`type`) values (3,'Hispanic or Latino');
insert  into `eeo_ethnic_type`(`eeo_ethnic_type_id`,`type`) values (4,'Non-Hispanic Black');
insert  into `eeo_ethnic_type`(`eeo_ethnic_type_id`,`type`) values (5,'Non-Hispanic White');

/*Table structure for table `eeo_veteran_type` */

CREATE TABLE `eeo_veteran_type` (
  `eeo_veteran_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`eeo_veteran_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Data for the table `eeo_veteran_type` */

insert  into `eeo_veteran_type`(`eeo_veteran_type_id`,`type`) values (1,'No Veteran Status');
insert  into `eeo_veteran_type`(`eeo_veteran_type_id`,`type`) values (2,'Eligible Veteran');
insert  into `eeo_veteran_type`(`eeo_veteran_type_id`,`type`) values (3,'Disabled Veteran');
insert  into `eeo_veteran_type`(`eeo_veteran_type_id`,`type`) values (4,'Eligible and Disabled');

/*Table structure for table `email_history` */

CREATE TABLE `email_history` (
  `email_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `from_address` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `recipients` text COLLATE utf8_unicode_ci NOT NULL,
  `text` text COLLATE utf8_unicode_ci,
  `user_id` int(11) DEFAULT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`email_history_id`),
  KEY `IDX_site_id` (`site_id`),
  KEY `IDX_date` (`date`),
  KEY `IDX_user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `email_history` */

/*Table structure for table `email_template` */

CREATE TABLE `email_template` (
  `email_template_id` int(11) NOT NULL AUTO_INCREMENT,
  `text` text COLLATE utf8_unicode_ci,
  `allow_substitution` int(1) NOT NULL DEFAULT '0',
  `site_id` int(11) NOT NULL DEFAULT '0',
  `tag` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `possible_variables` text COLLATE utf8_unicode_ci,
  `disabled` int(1) DEFAULT '0',
  PRIMARY KEY (`email_template_id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `email_template` */

insert  into `email_template`(`email_template_id`,`text`,`allow_substitution`,`site_id`,`tag`,`title`,`possible_variables`,`disabled`) values (20,'* Auto generated message. Please DO NOT reply *\r\n%DATETIME%\r\n\r\nDear %CANDFULLNAME%,\r\n\r\nThis E-Mail is a notification that your status in our database has been changed for the position %JBODTITLE% (%JBODCLIENT%).\r\n\r\nYour previous status was <B>%CANDPREVSTATUS%</B>.\r\nYour new status is <B>%CANDSTATUS%</B>.\r\n\r\nTake care,\r\n%USERFULLNAME%\r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_STATUSCHANGE','Status Changed (Sent to Candidate)','%CANDSTATUS%%CANDOWNER%%CANDFIRSTNAME%%CANDFULLNAME%%CANDPREVSTATUS%%JBODCLIENT%%JBODTITLE%',0);
insert  into `email_template`(`email_template_id`,`text`,`allow_substitution`,`site_id`,`tag`,`title`,`possible_variables`,`disabled`) values (28,'%DATETIME%\r\n\r\nDear %CANDOWNER%,\r\n\r\nThis E-Mail is a notification that a Candidate has been assigned to you.\r\n\r\nCandidate Name: %CANDFULLNAME%\r\nCandidate URL: %CANDCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_OWNERSHIPASSIGNCANDIDATE','Candidate Assigned (Sent to Assigned Recruiter)','%CANDOWNER%%CANDFIRSTNAME%%CANDFULLNAME%%CANDCATSURL%',0);
insert  into `email_template`(`email_template_id`,`text`,`allow_substitution`,`site_id`,`tag`,`title`,`possible_variables`,`disabled`) values (27,'%DATETIME%\r\n\r\nDear %JBODOWNER%,\r\n\r\nThis E-Mail is a notification that a Job Order has been assigned to you.\r\n\r\nJob Order Title: %JBODTITLE%\r\nJob Order Client: %JBODCLIENT%\r\nJob Order ID: %JBODID%\r\nJob Order URL: %JBODCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_OWNERSHIPASSIGNJOBORDER','Job Order Assigned (Sent to Assigned Recruiter)','%JBODOWNER%%JBODTITLE%%JBODCLIENT%%JBODCATSURL%%JBODID%',0);
insert  into `email_template`(`email_template_id`,`text`,`allow_substitution`,`site_id`,`tag`,`title`,`possible_variables`,`disabled`) values (26,'%DATETIME%\r\n\r\nDear %CONTOWNER%,\r\n\r\nThis E-Mail is a notification that a Contact has been assigned to you.\r\n\r\nContact Name: %CONTFULLNAME%\r\nContact Client: %CONTCLIENTNAME%\r\nContact URL: %CONTCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_OWNERSHIPASSIGNCONTACT','Contact Assigned (Sent to Assigned Recruiter)','%CONTOWNER%%CONTFIRSTNAME%%CONTFULLNAME%%CONTCLIENTNAME%%CONTCATSURL%',0);
insert  into `email_template`(`email_template_id`,`text`,`allow_substitution`,`site_id`,`tag`,`title`,`possible_variables`,`disabled`) values (25,'%DATETIME%\r\n\r\nDear %CLNTOWNER%,\r\n\r\nThis E-Mail is a notification that a Client has been assigned to you.\r\n\r\nClient Name: %CLNTNAME%\r\nClient URL %CLNTCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_OWNERSHIPASSIGNCLIENT','Client Assigned (Sent to Assigned Recruiter)','%CLNTOWNER%%CLNTNAME%%CLNTCATSURL%',0);
insert  into `email_template`(`email_template_id`,`text`,`allow_substitution`,`site_id`,`tag`,`title`,`possible_variables`,`disabled`) values (30,'* This is an auto-generated message. Please do not reply. *\r\n%DATETIME%\r\n\r\nDear %CANDFULLNAME%,\r\n\r\nThank you for applying to the %JBODTITLE% position with our online career portal! Your application has been entered into our system and someone will review it shortly.\r\n\r\n--\r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_CANDIDATEAPPLY','Candidate Application Received (Sent to Candidate using Career Portal)','%CANDFIRSTNAME%%CANDFULLNAME%%JBODCLIENT%%JBODTITLE%%JBODOWNER%',0);
insert  into `email_template`(`email_template_id`,`text`,`allow_substitution`,`site_id`,`tag`,`title`,`possible_variables`,`disabled`) values (31,'%DATETIME%\r\n\r\nDear %JBODOWNER%,\r\n\r\nThis e-mail is a notification that a candidate has applied to your job order through the online candidate portal.\r\n\r\nJob Order: %JBODTITLE%\r\nCandidate Name: %CANDFULLNAME%\r\nCandidate URL: %CANDCATSURL%\r\nJob Order URL: %JBODCATSURL%\r\n\r\n--\r\nCATS\r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_CANDIDATEPORTALNEW','Candidate Application Received (Sent to Owner of Job Order from Career Portal)','%CANDFIRSTNAME%%CANDFULLNAME%%JBODOWNER%%JBODTITLE%%JBODCLIENT%%JBODCATSURL%%JBODID%%CANDCATSURL%',0);

/*Table structure for table `extension_statistics` */

CREATE TABLE `extension_statistics` (
  `extension_statistics_id` int(11) NOT NULL AUTO_INCREMENT,
  `extension` varchar(128) NOT NULL DEFAULT '',
  `action` varchar(128) NOT NULL DEFAULT '',
  `user` varchar(128) NOT NULL DEFAULT '',
  `date` date DEFAULT NULL,
  PRIMARY KEY (`extension_statistics_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `extension_statistics` */

/*Table structure for table `extra_field` */

CREATE TABLE `extra_field` (
  `extra_field_id` int(11) NOT NULL AUTO_INCREMENT,
  `data_item_id` int(11) DEFAULT '0',
  `field_name` varchar(255) DEFAULT NULL,
  `value` text,
  `import_id` int(11) DEFAULT NULL,
  `site_id` int(11) DEFAULT '0',
  `data_item_type` int(11) DEFAULT '0',
  PRIMARY KEY (`extra_field_id`),
  KEY `assoc_id` (`data_item_id`),
  KEY `IDX_site_id` (`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `extra_field` */

/*Table structure for table `extra_field_settings` */

CREATE TABLE `extra_field_settings` (
  `extra_field_settings_id` int(11) NOT NULL AUTO_INCREMENT,
  `field_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `import_id` int(11) DEFAULT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `data_item_type` int(11) DEFAULT '0',
  `extra_field_type` int(11) NOT NULL DEFAULT '1',
  `extra_field_options` text COLLATE utf8_unicode_ci,
  `position` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`extra_field_settings_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `extra_field_settings` */

insert  into `extra_field_settings`(`extra_field_settings_id`,`field_name`,`import_id`,`site_id`,`date_created`,`data_item_type`,`extra_field_type`,`extra_field_options`,`position`) values (1,'AdminUser',NULL,180,'2005-06-01 00:00:00',200,1,NULL,1);
insert  into `extra_field_settings`(`extra_field_settings_id`,`field_name`,`import_id`,`site_id`,`date_created`,`data_item_type`,`extra_field_type`,`extra_field_options`,`position`) values (2,'UnixName',NULL,180,'2005-06-01 00:00:00',200,1,NULL,2);
insert  into `extra_field_settings`(`extra_field_settings_id`,`field_name`,`import_id`,`site_id`,`date_created`,`data_item_type`,`extra_field_type`,`extra_field_options`,`position`) values (3,'BillingNotes',NULL,180,'2005-06-01 00:00:00',200,1,NULL,3);
insert  into `extra_field_settings`(`extra_field_settings_id`,`field_name`,`import_id`,`site_id`,`date_created`,`data_item_type`,`extra_field_type`,`extra_field_options`,`position`) values (4,'IPAddress',NULL,180,'2005-06-01 00:00:00',300,1,NULL,4);

/*Table structure for table `feedback` */

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `reply_to_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `reply_to_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `feedback` text COLLATE utf8_unicode_ci NOT NULL,
  `archived` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`feedback_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `feedback` */

/*Table structure for table `history` */

CREATE TABLE `history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `data_item_type` int(11) DEFAULT NULL,
  `data_item_id` int(11) DEFAULT NULL,
  `the_field` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `previous_value` text COLLATE utf8_unicode_ci,
  `new_value` text COLLATE utf8_unicode_ci,
  `description` varchar(192) COLLATE utf8_unicode_ci DEFAULT NULL,
  `set_date` datetime DEFAULT NULL,
  `entered_by` int(11) DEFAULT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`history_id`),
  KEY `IDX_DATA_ENTERED_BY` (`entered_by`),
  KEY `IDX_data_item_id_type_site` (`data_item_id`,`data_item_type`,`site_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `history` */

insert  into `history`(`history_id`,`data_item_type`,`data_item_id`,`the_field`,`previous_value`,`new_value`,`description`,`set_date`,`entered_by`,`site_id`) values (1,200,1,'!newEntry!',NULL,NULL,'(USER) created entry.','2009-11-19 10:00:20',1,1);
insert  into `history`(`history_id`,`data_item_type`,`data_item_id`,`the_field`,`previous_value`,`new_value`,`description`,`set_date`,`entered_by`,`site_id`) values (2,200,1,'defaultCompany',NULL,'1','(USER) changed field(s): defaultCompany.','2009-11-19 10:00:20',1,1);
insert  into `history`(`history_id`,`data_item_type`,`data_item_id`,`the_field`,`previous_value`,`new_value`,`description`,`set_date`,`entered_by`,`site_id`) values (3,100,1,'!newEntry!',NULL,NULL,'(USER) created entry.','2009-11-19 10:24:54',1,1);
insert  into `history`(`history_id`,`data_item_type`,`data_item_id`,`the_field`,`previous_value`,`new_value`,`description`,`set_date`,`entered_by`,`site_id`) values (4,100,1,'(DELETED)',NULL,NULL,'(USER) deleted entry.','2009-11-20 14:29:36',1,1);

/*Table structure for table `http_log` */

CREATE TABLE `http_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `site_id` int(11) NOT NULL,
  `remote_addr` char(16) NOT NULL,
  `http_user_agent` varchar(255) DEFAULT NULL,
  `script_filename` varchar(255) DEFAULT NULL,
  `request_method` varchar(16) DEFAULT NULL,
  `query_string` varchar(255) DEFAULT NULL,
  `request_uri` varchar(255) DEFAULT NULL,
  `script_name` varchar(255) DEFAULT NULL,
  `log_type` int(11) NOT NULL,
  `date` datetime DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `http_log` */

/*Table structure for table `http_log_types` */

CREATE TABLE `http_log_types` (
  `log_type_id` int(11) NOT NULL,
  `name` varchar(16) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `default_log_type` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0',
  PRIMARY KEY (`log_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `http_log_types` */

insert  into `http_log_types`(`log_type_id`,`name`,`description`,`default_log_type`) values (1,'XML','XML Job Feed',0);

/*Table structure for table `import` */

CREATE TABLE `import` (
  `import_id` int(11) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `reverted` int(1) NOT NULL DEFAULT '0',
  `site_id` int(11) NOT NULL DEFAULT '0',
  `import_errors` text COLLATE utf8_unicode_ci,
  `added_lines` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`import_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `import` */

/*Table structure for table `installtest` */

CREATE TABLE `installtest` (
  `id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `installtest` */

/*Table structure for table `joborder` */

CREATE TABLE `joborder` (
  `joborder_id` int(11) NOT NULL AUTO_INCREMENT,
  `recruiter` int(11) DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `entered_by` int(11) NOT NULL DEFAULT '0',
  `owner` int(11) DEFAULT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `client_job_id` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8_unicode_ci,
  `notes` text COLLATE utf8_unicode_ci,
  `type` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'C',
  `duration` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rate_max` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `salary` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  `is_hot` int(1) NOT NULL DEFAULT '0',
  `openings` int(11) DEFAULT NULL,
  `city` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `state` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `start_date` datetime DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `date_modified` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `public` int(1) NOT NULL DEFAULT '0',
  `company_department_id` int(11) DEFAULT NULL,
  `is_admin_hidden` int(1) DEFAULT '0',
  `openings_available` int(11) DEFAULT '0',
  `questionnaire_id` int(11) DEFAULT NULL,
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
  KEY `IDX_date_modified` (`date_modified`),
  KEY `IDX_site_id_status` (`site_id`,`status`(8))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `joborder` */

/*Table structure for table `module_schema` */

CREATE TABLE `module_schema` (
  `module_schema_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  PRIMARY KEY (`module_schema_id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `module_schema` */

insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (1,'activity',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (2,'attachments',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (3,'calendar',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (4,'candidates',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (5,'careers',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (6,'companies',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (7,'contacts',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (8,'export',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (9,'extension-statistics',1);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (10,'graphs',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (11,'home',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (12,'import',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (13,'install',363);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (14,'joborders',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (15,'lists',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (16,'login',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (17,'queue',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (18,'reports',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (19,'rss',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (20,'settings',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (21,'tests',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (22,'toolbar',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (23,'wizard',0);
insert  into `module_schema`(`module_schema_id`,`name`,`version`) values (24,'xml',0);

/*Table structure for table `mru` */

CREATE TABLE `mru` (
  `mru_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `data_item_type` int(11) NOT NULL DEFAULT '0',
  `data_item_text` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`mru_id`),
  KEY `IDX_user_site` (`user_id`,`site_id`)
) ENGINE=MyISAM AUTO_INCREMENT=112 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `mru` */

/*Table structure for table `queue` */

CREATE TABLE `queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `site_id` int(11) NOT NULL,
  `task` varchar(125) NOT NULL,
  `args` text,
  `priority` tinyint(2) NOT NULL DEFAULT '5' COMMENT '1-5, 1 is highest priority',
  `date_created` datetime NOT NULL,
  `date_timeout` datetime NOT NULL,
  `date_completed` datetime DEFAULT NULL,
  `locked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `error` tinyint(1) unsigned DEFAULT '0',
  `response` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`queue_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `queue` */

/*Table structure for table `saved_list` */

CREATE TABLE `saved_list` (
  `saved_list_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `data_item_type` int(11) NOT NULL DEFAULT '0',
  `site_id` int(11) NOT NULL DEFAULT '0',
  `is_dynamic` int(1) DEFAULT '0',
  `datagrid_instance` varchar(64) COLLATE utf8_unicode_ci DEFAULT '',
  `parameters` text COLLATE utf8_unicode_ci,
  `created_by` int(11) DEFAULT '0',
  `number_entries` int(11) DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`saved_list_id`),
  KEY `IDX_data_item_type` (`data_item_type`),
  KEY `IDX_description` (`description`),
  KEY `IDX_site_id` (`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `saved_list` */

/*Table structure for table `saved_list_entry` */

CREATE TABLE `saved_list_entry` (
  `saved_list_entry_id` int(11) NOT NULL AUTO_INCREMENT,
  `saved_list_id` int(11) NOT NULL,
  `data_item_type` int(11) NOT NULL DEFAULT '0',
  `data_item_id` int(11) NOT NULL DEFAULT '0',
  `site_id` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`saved_list_entry_id`),
  KEY `IDX_type_id` (`data_item_type`,`data_item_id`),
  KEY `IDX_data_item_type` (`data_item_type`),
  KEY `IDX_data_item_id` (`data_item_id`),
  KEY `IDX_hot_list_id` (`saved_list_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `saved_list_entry` */

/*Table structure for table `saved_search` */

CREATE TABLE `saved_search` (
  `search_id` int(11) NOT NULL AUTO_INCREMENT,
  `data_item_text` text COLLATE utf8_unicode_ci,
  `url` text COLLATE utf8_unicode_ci,
  `is_custom` int(1) DEFAULT NULL,
  `data_item_type` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `site_id` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`search_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `saved_search` */

/*Table structure for table `settings` */

CREATE TABLE `settings` (
  `settings_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `settings_type` int(11) DEFAULT '0',
  PRIMARY KEY (`settings_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `settings` */

insert  into `settings`(`settings_id`,`setting`,`value`,`site_id`,`settings_type`) values (1,'fromAddress','admin@testdomain.com',1,1);
insert  into `settings`(`settings_id`,`setting`,`value`,`site_id`,`settings_type`) values (2,'fromAddress','admin@testdomain.com',180,1);
insert  into `settings`(`settings_id`,`setting`,`value`,`site_id`,`settings_type`) values (3,'configured','1',1,1);
insert  into `settings`(`settings_id`,`setting`,`value`,`site_id`,`settings_type`) values (4,'configured','1',180,1);

/*Table structure for table `site` */

CREATE TABLE `site` (
  `site_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `is_demo` int(1) NOT NULL DEFAULT '0',
  `user_licenses` int(11) NOT NULL DEFAULT '0',
  `entered_by` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `unix_name` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `is_free` int(1) DEFAULT NULL,
  `account_active` int(1) NOT NULL DEFAULT '1',
  `account_deleted` int(1) NOT NULL DEFAULT '0',
  `reason_disabled` text CHARACTER SET utf8,
  `time_zone` int(5) DEFAULT '0',
  `time_format_24` int(1) DEFAULT '0',
  `date_format_ddmmyy` int(1) DEFAULT '0',
  `is_hr_mode` int(1) DEFAULT '0',
  `file_size_kb` int(11) DEFAULT '0',
  `page_views` bigint(20) DEFAULT '0',
  `page_view_days` int(11) DEFAULT '0',
  `last_viewed_day` date DEFAULT NULL,
  `first_time_setup` tinyint(4) DEFAULT '0',
  `localization_configured` int(1) DEFAULT '0',
  `agreed_to_license` int(1) DEFAULT '0',
  `limit_warning` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`site_id`),
  KEY `IDX_account_deleted` (`account_deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=181 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `site` */

insert  into `site`(`site_id`,`name`,`is_demo`,`user_licenses`,`entered_by`,`date_created`,`unix_name`,`company_id`,`is_free`,`account_active`,`account_deleted`,`reason_disabled`,`time_zone`,`time_format_24`,`date_format_ddmmyy`,`is_hr_mode`,`file_size_kb`,`page_views`,`page_view_days`,`last_viewed_day`,`first_time_setup`,`localization_configured`,`agreed_to_license`,`limit_warning`) values (1,'testdomain.com',0,0,0,'2005-06-01 00:00:00',NULL,NULL,0,1,0,NULL,2,0,1,0,0,574,1,'2009-11-19',0,0,1,0);
insert  into `site`(`site_id`,`name`,`is_demo`,`user_licenses`,`entered_by`,`date_created`,`unix_name`,`company_id`,`is_free`,`account_active`,`account_deleted`,`reason_disabled`,`time_zone`,`time_format_24`,`date_format_ddmmyy`,`is_hr_mode`,`file_size_kb`,`page_views`,`page_view_days`,`last_viewed_day`,`first_time_setup`,`localization_configured`,`agreed_to_license`,`limit_warning`) values (180,'CATS_ADMIN',0,0,0,'2005-06-01 00:00:00','catsadmin',NULL,0,1,0,NULL,2,0,1,0,0,0,0,NULL,0,0,0,0);

/*Table structure for table `sph_counter` */

CREATE TABLE `sph_counter` (
  `counter_id` int(11) NOT NULL,
  `max_doc_id` int(11) NOT NULL,
  PRIMARY KEY (`counter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `sph_counter` */

/*Table structure for table `system` */

CREATE TABLE `system` (
  `system_id` int(20) NOT NULL DEFAULT '0',
  `uid` int(20) DEFAULT NULL,
  `available_version` int(11) DEFAULT '0',
  `date_version_checked` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `available_version_description` text COLLATE utf8_unicode_ci,
  `disable_version_check` int(1) DEFAULT "0",
  PRIMARY KEY (`system_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `system` */

insert  into `system`(`system_id`,`uid`,`available_version`,`date_version_checked`,`available_version_description`,`disable_version_check`) values (0,2618174,900,'2009-11-19 00:00:00','',1);

/*Table structure for table `tag` */

CREATE TABLE `tag` (
  `tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_parent_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `site_id` int(11) unsigned DEFAULT NULL,
  `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `tag` */

insert  into `tag`(`tag_id`,`tag_parent_id`,`title`,`description`,`site_id`,`date_created`) values (1,NULL,'tag1','-',1,'2009-11-19 10:24:02');
insert  into `tag`(`tag_id`,`tag_parent_id`,`title`,`description`,`site_id`,`date_created`) values (5,1,'tag13','-',1,'2009-11-19 12:06:08');
insert  into `tag`(`tag_id`,`tag_parent_id`,`title`,`description`,`site_id`,`date_created`) values (77,1,'test tag','-',1,'2009-11-20 13:13:35');
insert  into `tag`(`tag_id`,`tag_parent_id`,`title`,`description`,`site_id`,`date_created`) values (78,NULL,'tag2','-',1,'2009-11-20 13:13:42');
insert  into `tag`(`tag_id`,`tag_parent_id`,`title`,`description`,`site_id`,`date_created`) values (79,78,'tag21','-',1,'2009-11-20 13:13:47');
insert  into `tag`(`tag_id`,`tag_parent_id`,`title`,`description`,`site_id`,`date_created`) values (80,78,'tag22','-',1,'2009-11-20 13:13:50');
insert  into `tag`(`tag_id`,`tag_parent_id`,`title`,`description`,`site_id`,`date_created`) values (81,78,'tag23','-',1,'2009-11-20 13:13:52');

/*Table structure for table `user` */

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `user_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `access_level` int(11) NOT NULL DEFAULT '100',
  `can_change_password` int(1) NOT NULL DEFAULT '1',
  `is_test_user` int(1) NOT NULL DEFAULT '0',
  `last_name` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `first_name` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `is_demo` int(1) DEFAULT '0',
  `categories` varchar(192) COLLATE utf8_unicode_ci DEFAULT NULL,
  `session_cookie` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pipeline_entries_per_page` int(8) DEFAULT '15',
  `column_preferences` longtext COLLATE utf8_unicode_ci,
  `force_logout` int(1) DEFAULT '0',
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT '',
  `phone_work` varchar(64) COLLATE utf8_unicode_ci DEFAULT '',
  `phone_cell` varchar(64) COLLATE utf8_unicode_ci DEFAULT '',
  `phone_other` varchar(64) COLLATE utf8_unicode_ci DEFAULT '',
  `address` text COLLATE utf8_unicode_ci,
  `notes` text COLLATE utf8_unicode_ci,
  `company` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip_code` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `can_see_eeo_info` int(1) DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `IDX_site_id` (`site_id`),
  KEY `IDX_first_name` (`first_name`),
  KEY `IDX_last_name` (`last_name`),
  KEY `IDX_access_level` (`access_level`)
) ENGINE=MyISAM AUTO_INCREMENT=1251 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `user` */

insert  into `user`(`user_id`,`site_id`,`user_name`,`email`,`password`,`access_level`,`can_change_password`,`is_test_user`,`last_name`,`first_name`,`is_demo`,`categories`,`session_cookie`,`pipeline_entries_per_page`,`column_preferences`,`force_logout`,`title`,`phone_work`,`phone_cell`,`phone_other`,`address`,`notes`,`company`,`city`,`state`,`zip_code`,`country`,`can_see_eeo_info`) values (1,1,'admin','admin@testdomain.com','admin',500,1,0,'Administrator','CATS',0,NULL,'CATS=e29233aabf2cdb71373582023ff9747e',15,'a:4:{s:31:\"home:ImportantPipelineDashboard\";a:6:{i:0;a:2:{s:4:\"name\";s:10:\"First Name\";s:5:\"width\";i:85;}i:1;a:2:{s:4:\"name\";s:9:\"Last Name\";s:5:\"width\";i:75;}i:2;a:2:{s:4:\"name\";s:6:\"Status\";s:5:\"width\";i:75;}i:3;a:2:{s:4:\"name\";s:8:\"Position\";s:5:\"width\";i:275;}i:4;a:2:{s:4:\"name\";s:7:\"Company\";s:5:\"width\";i:210;}i:5;a:2:{s:4:\"name\";s:8:\"Modified\";s:5:\"width\";i:80;}}s:18:\"home:CallsDataGrid\";a:2:{i:0;a:2:{s:4:\"name\";s:4:\"Time\";s:5:\"width\";i:90;}i:1;a:2:{s:4:\"name\";s:4:\"Name\";s:5:\"width\";i:175;}}s:39:\"candidates:candidatesListByViewDataGrid\";a:9:{i:0;a:2:{s:4:\"name\";s:11:\"Attachments\";s:5:\"width\";i:31;}i:1;a:2:{s:4:\"name\";s:10:\"First Name\";s:5:\"width\";i:75;}i:2;a:2:{s:4:\"name\";s:9:\"Last Name\";s:5:\"width\";i:85;}i:3;a:2:{s:4:\"name\";s:4:\"City\";s:5:\"width\";i:75;}i:4;a:2:{s:4:\"name\";s:5:\"State\";s:5:\"width\";i:50;}i:5;a:2:{s:4:\"name\";s:10:\"Key Skills\";s:5:\"width\";i:215;}i:6;a:2:{s:4:\"name\";s:5:\"Owner\";s:5:\"width\";i:65;}i:7;a:2:{s:4:\"name\";s:7:\"Created\";s:5:\"width\";i:60;}i:8;a:2:{s:4:\"name\";s:8:\"Modified\";s:5:\"width\";i:60;}}s:25:\"activity:ActivityDataGrid\";a:7:{i:0;a:2:{s:4:\"name\";s:4:\"Date\";s:5:\"width\";i:110;}i:1;a:2:{s:4:\"name\";s:10:\"First Name\";s:5:\"width\";i:85;}i:2;a:2:{s:4:\"name\";s:9:\"Last Name\";s:5:\"width\";i:75;}i:3;a:2:{s:4:\"name\";s:9:\"Regarding\";s:5:\"width\";i:125;}i:4;a:2:{s:4:\"name\";s:8:\"Activity\";s:5:\"width\";i:65;}i:5;a:2:{s:4:\"name\";s:5:\"Notes\";s:5:\"width\";i:240;}i:6;a:2:{s:4:\"name\";s:10:\"Entered By\";s:5:\"width\";i:60;}}}',0,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0);
insert  into `user`(`user_id`,`site_id`,`user_name`,`email`,`password`,`access_level`,`can_change_password`,`is_test_user`,`last_name`,`first_name`,`is_demo`,`categories`,`session_cookie`,`pipeline_entries_per_page`,`column_preferences`,`force_logout`,`title`,`phone_work`,`phone_cell`,`phone_other`,`address`,`notes`,`company`,`city`,`state`,`zip_code`,`country`,`can_see_eeo_info`) values (1250,180,'cats@rootadmin','0','cantlogin',0,0,0,'Automated','CATS',0,NULL,NULL,15,NULL,0,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0);

/*Table structure for table `user_login` */

CREATE TABLE `user_login` (
  `user_login_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `successful` int(1) NOT NULL DEFAULT '0',
  `host` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_refreshed` datetime DEFAULT NULL,
  PRIMARY KEY (`user_login_id`),
  KEY `IDX_user_id` (`user_id`),
  KEY `IDX_ip` (`ip`),
  KEY `IDX_date` (`date`),
  KEY `IDX_date_refreshed` (`date_refreshed`),
  KEY `IDX_site_id_date` (`site_id`,`date`),
  KEY `IDX_successful_site_id` (`successful`,`site_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `user_login` */

insert  into `user_login`(`user_login_id`,`user_id`,`site_id`,`ip`,`user_agent`,`date`,`successful`,`host`,`date_refreshed`) values (1,1,1,'127.0.0.1','Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1.5) Gecko/20091102 Firefox/3.5.5','2009-11-19 09:59:57',1,'127.0.0.1','2009-11-20 14:29:36');

/*Table structure for table `word_verification` */

CREATE TABLE `word_verification` (
  `word_verification_ID` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(28) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`word_verification_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `word_verification` */

/*Table structure for table `xml_feed_submits` */

CREATE TABLE `xml_feed_submits` (
  `feed_id` int(11) NOT NULL AUTO_INCREMENT,
  `feed_site` varchar(75) NOT NULL,
  `feed_url` varchar(255) NOT NULL,
  `date_last_post` date NOT NULL,
  PRIMARY KEY (`feed_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `xml_feed_submits` */

/*Table structure for table `xml_feeds` */

CREATE TABLE `xml_feeds` (
  `xml_feed_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `post_url` varchar(255) NOT NULL,
  `success_string` varchar(255) NOT NULL,
  `xml_template_name` varchar(255) NOT NULL,
  PRIMARY KEY (`xml_feed_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `xml_feeds` */

insert  into `xml_feeds`(`xml_feed_id`,`name`,`description`,`website`,`post_url`,`success_string`,`xml_template_name`) values (1,'Indeed','Indeed.com job search engine.','http://www.indeed.com','http://www.indeed.com/jsp/includejobs.jsp','Thank you for submitting your XML job feed','indeed');
insert  into `xml_feeds`(`xml_feed_id`,`name`,`description`,`website`,`post_url`,`success_string`,`xml_template_name`) values (2,'SimplyHired','SimplyHired.com job search engine','http://www.simplyhired.com','http://www.simplyhired.com/confirmation.php','Thanks for Contacting Us','simplyhired');

/*Table structure for table `zipcodes` */

CREATE TABLE `zipcodes` (
  `zipcode` mediumint(9) NOT NULL DEFAULT '0',
  `city` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `areacode` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`zipcode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `zipcodes` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
