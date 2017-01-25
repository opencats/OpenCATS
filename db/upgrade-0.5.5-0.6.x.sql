

ALTER IGNORE TABLE joborder ADD COLUMN public int(1) NOT NULL DEFAULT 0;

CREATE TABLE `candidate_foreign` (
  `alien_id` int(11) NOT NULL auto_increment,
  `assoc_id` int(11) default NULL,
  `field_name` varchar(255) default NULL,
  `value` text,
  `import_id` int(11) default NULL,
  PRIMARY KEY  (`alien_id`),
  KEY `assoc_id` (`assoc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `client_foreign` (
  `alien_id` int(11) NOT NULL auto_increment,
  `assoc_id` int(11) default NULL,
  `field_name` varchar(255) default NULL,
  `value` text,
  `import_id` int(11) default NULL,
  PRIMARY KEY  (`alien_id`),
  KEY `assoc_id` (`assoc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `contact_foreign` (
  `alien_id` int(11) NOT NULL auto_increment,
  `assoc_id` int(11) default NULL,
  `field_name` varchar(255) default NULL,
  `value` text,
  `import_id` int(11) default NULL,
  PRIMARY KEY  (`alien_id`),
  KEY `assoc_id` (`assoc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `import` (
  `import_id` int(11) NOT NULL auto_increment,
  `module_name` varchar(255) NOT NULL default '',
  `reverted` int(1) NOT NULL default '0',
  `site_id` int(11) NOT NULL default '0',
  `import_errors` longtext,
  `added_lines` int(11) default NULL,
  PRIMARY KEY  (`import_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `candidate` ADD COLUMN `import_id` INTEGER(11) NOT NULL DEFAULT '0';
ALTER TABLE `contact` ADD COLUMN `import_id` INTEGER(11) NOT NULL DEFAULT '0';
ALTER TABLE `client` ADD COLUMN `import_id` INTEGER;

INSERT IGNORE INTO system (system_id, uid, date_version_checked) VALUES (0, 0, '2001-01-01');

#r492 8-8-6 BH
ALTER TABLE `calendar_event` CHANGE `description` title text character set utf8 collate utf8_unicode_ci NOT NULL;
ALTER TABLE `calendar_event` ADD COLUMN `description` text character set utf8 collate utf8_unicode_ci;
ALTER TABLE `calendar_event` ADD COLUMN `duration` int(11) NOT NULL default '60';
ALTER TABLE `calendar_event` ADD COLUMN `reminder_enabled` int(1) NOT NULL default '0';
ALTER TABLE `calendar_event` ADD COLUMN `reminder_email` varchar(255) default '';
ALTER TABLE `calendar_event` ADD COLUMN `reminder_time` int(11) NOT NULL default '0';

UPDATE calendar_event SET duration='60';

#r495 8-8-6 BH
DROP TABLE IF EXISTS `calendar_event_type`;
CREATE TABLE `calendar_event_type` (
  `calendar_event_type_id` int(11) NOT NULL default '0',
  `short_description` varchar(32) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `icon_image` varchar(255) default NULL,
  PRIMARY KEY  (`calendar_event_type_id`),
  KEY `IDX_short_description` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
INSERT INTO `calendar_event_type` VALUES (100, 'Call', 'images/phone.gif');
INSERT INTO `calendar_event_type` VALUES (200, 'Email', 'images/email.gif');
INSERT INTO `calendar_event_type` VALUES (300, 'Meeting', 'images/meeting.gif');
INSERT INTO `calendar_event_type` VALUES (400, 'Interview', 'images/interview.gif');
INSERT INTO `calendar_event_type` VALUES (500, 'Personal', 'images/personal.gif');
INSERT INTO `calendar_event_type` VALUES (600, 'Other', NULL);

#r497 8-9-6 BH
ALTER TABLE `system` ADD COLUMN `schema_version` int(11) NOT NULL default '0';
UPDATE system SET schema_version = 497;

#r501 8-9-6 BH
UPDATE system SET schema_version = 501;
ALTER TABLE `joborder` CHANGE `rate_max` `rate_max` varchar(255) default NULL;

#r510 8-9-6 BH
UPDATE system SET schema_version = 510;
CREATE TABLE `saved_search` (
  `search_id` int(11) NOT NULL auto_increment,
  `data_item_text` text,
  `url` text,
  `is_custom` int(1) default NULL,
  `data_item_type` int(11) default NULL,
  `user_id` int(11) default NULL,
  `site_id` int(11) default NULL,
  `date_created` datetime default NULL,
  PRIMARY KEY  (`search_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#r518 8-11-6 PC
UPDATE system SET schema_version = 518;
CREATE TABLE `hot_list` (
  `hot_list_id` int(11) NOT NULL auto_increment,
  `hot_list_description` varchar(64) NOT NULL,
  `hot_list_type` int(11) NOT NULL default 0,
  `site_id` int(11) NOT NULL default 0,
  PRIMARY KEY (`hot_list_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `candidate` ADD COLUMN `is_hot` INT(1) NOT NULL DEFAULT 0;
ALTER TABLE `candidate` ADD COLUMN `hot_list_id` int(11) NULL;

#r519 8-11-6 WB
UPDATE system SET schema_version = 519;
ALTER TABLE `import` TYPE = MYISAM;
ALTER TABLE `import` CHANGE `module_name` `module_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `import` CHANGE `import_errors` `import_errors` LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `import` CHANGE `import_errors` `import_errors` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `import` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `candidate_foreign` TYPE = MYISAM;
ALTER TABLE `candidate_foreign` CHANGE `field_name` `field_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `candidate_foreign` CHANGE `value` `value` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `candidate_foreign` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `client_foreign` TYPE = MYISAM;
ALTER TABLE `client_foreign` CHANGE `field_name` `field_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `client_foreign` CHANGE `value` `value` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `client_foreign` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `contact_foreign` TYPE = MYISAM;
ALTER TABLE `contact_foreign` CHANGE `field_name` `field_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `contact_foreign` CHANGE `value` `value` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `contact_foreign` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `saved_search` CHANGE `data_item_text` `data_item_text` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `saved_search` CHANGE `url` `url` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `saved_search` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `hot_list` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

#r520 8-11-6 WB
UPDATE system SET schema_version = 520;
DROP TABLE IF EXISTS `candidate_joborder_status`;
CREATE TABLE `candidate_joborder_status` (
  `candidate_status_id` int(11) NOT NULL default '0',
  `short_description` varchar(32) collate utf8_unicode_ci NOT NULL default '',
  `can_be_scheduled` int(1) NOT NULL default '0',
  `triggers_email` int(1) NOT NULL default '1',
  `is_enabled` int(1) NOT NULL default '1',
  PRIMARY KEY  (`candidate_status_id`),
  KEY `IDX_short_description` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
INSERT INTO `candidate_joborder_status` VALUES (100, 'No Contact', 0, 1, 1);
INSERT INTO `candidate_joborder_status` VALUES (200, 'Contacted', 0, 1, 1);
INSERT INTO `candidate_joborder_status` VALUES (300, 'Negotiating', 0, 1, 1);
INSERT INTO `candidate_joborder_status` VALUES (400, 'Submitted', 0, 1, 1);
INSERT INTO `candidate_joborder_status` VALUES (500, 'Interviewing', 0, 1, 1);
INSERT INTO `candidate_joborder_status` VALUES (600, 'Offered', 0, 1, 1);
INSERT INTO `candidate_joborder_status` VALUES (700, 'Passed On', 0, 1, 1);
INSERT INTO `candidate_joborder_status` VALUES (800, 'Placed', 0, 1, 1);
CREATE TABLE `candidate_joborder_status_history` (
  `candidate_joborder_status_history_id` int(11) NOT NULL auto_increment,
  `candidate_id` int(11) NOT NULL default '0',
  `joborder_id` int(11) NOT NULL default '0',
  `date` datetime NOT NULL default '1000-01-01 00:00:00',
  `status_from` int(11) NOT NULL default '0',
  `status_to` int(11) NOT NULL default '0',
  PRIMARY KEY  (`candidate_joborder_status_history_id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;
DROP TABLE IF EXISTS `candidate_joborder_status_type`;
ALTER TABLE `candidate_joborder` ADD `status` INT DEFAULT '0' NOT NULL AFTER `submitted` ;

#r530 8-13-6 BH
UPDATE system SET schema_version = 530;
DROP TABLE IF EXISTS `client_department`;
CREATE TABLE `client_department` (
  `department_id` int(11) NOT NULL auto_increment,
  `name` varchar(128) character set utf8 collate utf8_unicode_ci default NULL,
  `client_id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `created_by` int(11) default NULL,
  PRIMARY KEY  (`department_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 CHARACTER SET utf8 COLLATE utf8_unicode_ci;

#r534 8-13-6 BH
UPDATE system SET schema_version = 534;
DROP TABLE IF EXISTS `candidate_source`;
CREATE TABLE `candidate_source` (
  `source_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `site_id` int(11) default NULL,
  `date_created` datetime default NULL,
  PRIMARY KEY  (`source_id`),
  KEY `siteID` (`site_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

#r538 8-11-6 WB
UPDATE system SET schema_version = 538;
ALTER TABLE `client_department` TYPE = MYISAM;
ALTER TABLE `client_department` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

#r539 8-14-6 BH
UPDATE system SET schema_version = 539;
ALTER TABLE `contact` ADD COLUMN `department_id` int(11) NOT NULL default '0';
ALTER TABLE `contact` CHANGE `department` `department` varchar(128) character set utf8 collate utf8_unicode_ci default NULL;
ALTER TABLE `client_department` CHANGE `name` `name` varchar(128) character set utf8 collate utf8_unicode_ci default NULL;
INSERT INTO client_department (name, client_id, site_id, date_created)
    SELECT DISTINCT(department), client_id, site_id, NOW() FROM contact WHERE department != '';
UPDATE contact, (SELECT contact.contact_id as contactID, contact.client_id
    AS clientID, contact.department AS department, client_department.department_id
    AS departmentID FROM contact, client_department WHERE client_department.name =
    contact.department AND client_department.client_id = contact.client_id) AS theRows
    SET contact.department_id = theRows.departmentID WHERE contact.contact_id = theRows.contactID;
ALTER TABLE `contact` DROP `department`;
ALTER TABLE `candidate_source` TYPE = MYISAM;
ALTER TABLE `candidate_source` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

#r552 8-15-6 BH
UPDATE system SET schema_version = 552;
ALTER TABLE `calendar_event` ADD COLUMN `public` int(1) NOT NULL default '1';

#r555 8-16-6 BH
UPDATE system SET schema_version = 555;
DROP TABLE IF EXISTS `dashboard_module`;
CREATE TABLE `dashboard_module` (
  `dashboard_module_id` int(11) NOT NULL auto_increment,
  `object` varchar(255) default NULL,
  `name` varchar(255) default NULL,
  `function` varchar(255) default NULL,
  `title` varchar(255) default NULL,
  `description` text,
  `preview_image` varchar(255) default NULL,
  `paramater_CSV` text,
  `paramater_defaults` text,
  PRIMARY KEY  (`dashboard_module_id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci COLLATE utf8_unicode_ci;
INSERT INTO dashboard_module VALUES
  (1, 'graphs', 'weeklyActivity', 'weeklyActivityDashboard', 'Weekly Activity', 'Shows the current week and previous week activity count on a bar graph.', 'http://localhost/cats/index.php?m=graphs&a=weeklyActivity&width=365&height=180', NULL, NULL),
  (2, 'calendar', 'calendarToday', 'summaryDashboardToday', 'Todays Events', 'Shows a list of todays events.', NULL, '\"Number of events to show: ,vartextshort\"', NULL),
  (3, 'generic', 'titleBar', 'titleBar', 'Title Bar', 'Displays a custom title bar.', NULL, '\"Title: ,vartext\"', ''),
  (4, 'generic', 'text', 'text', 'Text', 'Displays text.', NULL, '\"Text: ,vartextlong\"', NULL),
  (5, 'jobOrders', 'recentJobOrders', 'recentJobOrdersDashboard', 'Recent Job Orders', 'Displays the latest job orders added to the system.', NULL, '\"Number of job orders to display: ,vartextshort\"', '\"6\"');
DROP TABLE IF EXISTS `dashboard_component`;
CREATE TABLE `dashboard_component` (
  `dashboard_component_id` int(11) NOT NULL auto_increment,
  `module_name` varchar(255) default NULL,
  `module_paramaters` text,
  `site_id` int(11) default NULL,
  `column_number` int(11) default NULL,
  `position` int(11) default NULL,
  PRIMARY KEY  (`dashboard_component_id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci COLLATE utf8_unicode_ci;
INSERT INTO dashboard_component SELECT NULL, 'text', '\"Welcome, ((USERNAME))!\r\nDate: ((DATE))\r\nTime: ((TIME))\"', site_id, 0, 0 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'calendarToday', '\"2\"', site_id, 0, 1 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'titleBar', '\"Current Status:\"', site_id, 2, 0 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'weeklyActivity', NULL, site_id, 2, 1 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'recentJobOrders', '\"12\"', site_id, 1, 0 FROM site;

#r556 8-17-6 WB
UPDATE system SET schema_version = 556;
ALTER TABLE `candidate_joborder_status` CHANGE `candidate_status_id` `candidate_joborder_status_id` int(11) NOT NULL default '0';
INSERT INTO `candidate_joborder_status` VALUES (0, 'No Status', 0, 0, 1);

#r558 8-17-6 BH
UPDATE system SET schema_version = 558;
INSERT INTO dashboard_module VALUES (NULL, 'generic', 'html', 'text', 'HTML', 'Displays formatted HTML.', NULL, '\"HTML Code: ,vartextlong\"', NULL);

#r559 8-17-6 BH
UPDATE system SET schema_version = 559;
INSERT INTO dashboard_module VALUES (NULL, 'graphs', 'newCandidates', 'newCandidatesDashboard', 'New Candidates', 'Shows the current week and previous week new candidate count on a bar graph.', 'http://localhost/cats/index.php?m=graphs&a=newCandidates&width=300&height=180', NULL, NULL);
INSERT INTO dashboard_component SELECT NULL, 'newCandidates', NULL, site_id, 2, 2 FROM site;

#r560 8-17-6 BH
UPDATE system SET schema_version = 560;
DROP TABLE IF EXISTS `dashboard_module`;
CREATE TABLE `dashboard_module` (
  `dashboard_module_id` int(11) NOT NULL auto_increment,
  `object` varchar(255) collate utf8_unicode_ci default NULL,
  `name` varchar(255) collate utf8_unicode_ci default NULL,
  `function` varchar(255) collate utf8_unicode_ci default NULL,
  `title` varchar(255) collate utf8_unicode_ci default NULL,
  `description` text collate utf8_unicode_ci,
  `preview_image` varchar(255) collate utf8_unicode_ci default NULL,
  `paramater_CSV` text collate utf8_unicode_ci,
  `paramater_defaults` text collate utf8_unicode_ci,
  PRIMARY KEY  (`dashboard_module_id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
INSERT INTO dashboard_module VALUES
  (1, 'graphs', 'weeklyActivity', 'weeklyActivityDashboard', 'Weekly Activity', 'Shows the current week and previous week activity count on a bar graph.', 'index.php?m=graphs&a=weeklyActivity&width=365&height=180', NULL, NULL),
  (2, 'calendar', 'calendarToday', 'summaryDashboardToday', 'Todays Events', 'Shows a list of todays events.', 'images/dashboard_preview/upcoming_events.png', '\"Number of events to show: ,vartextshort\"', NULL),
  (3, 'generic', 'titleBar', 'titleBar', 'Title Bar', 'Displays a custom title bar.', 'images/dashboard_preview/title_bar.png', '\"Title: ,vartext\"', ''),
  (4, 'generic', 'text', 'text', 'Text', 'Displays text.', 'images/dashboard_preview/textbox.png', '\"Text: ,vartextlong\"', NULL),
  (5, 'jobOrders', 'recentJobOrders', 'recentJobOrdersDashboard', 'Recent Job Orders', 'Displays the latest job orders added to the system.', 'images/dashboard_preview/recent_job_orders.png', '\"Number of job orders to display: ,vartextshort\"', '\"6\"'),
  (6, 'generic', 'html', 'text', 'HTML', 'Displays formatted HTML.', NULL, '\"HTML Code: ,vartextlong\"', NULL),
  (7, 'graphs', 'newCandidates', 'newCandidatesDashboard', 'New Candidates', 'Shows the current week and previous week new candidate count on a bar graph.', 'index.php?m=graphs&a=newCandidates&width=300&height=180', NULL, NULL),
  (8, 'graphs', 'newJobOrders', 'newJobOrdersDashboard', 'New Job Orders', 'Shows the current week and previous week new job order count on a bar graph.', 'index.php?m=graphs&a=newJobOrders&width=300&height=180', NULL, NULL),
  (9, 'graphs', 'newSubmissions', 'newSubmissionsDashboard', 'New Submissions', 'Shows the current week and previous week new count of how many candidates were submitted to job orders on a bar graph.', 'index.php?m=graphs&a=newSubmissions&width=300&height=180', NULL, NULL),
  (10, 'generic', 'image', 'image', 'Image', 'Displays an image.', 'images/dashboard_preview/image.png', '\"URL to image: ,vartext\",\"Optional hyperlink URL: ,vartext\"', NULL);

#r563 8-18-6 BH
UPDATE system SET schema_version = 563;
DROP TABLE IF EXISTS `dashboard_module`;
CREATE TABLE `dashboard_module` (
  `dashboard_module_id` int(11) NOT NULL auto_increment,
  `object` varchar(255) collate utf8_unicode_ci default NULL,
  `name` varchar(255) collate utf8_unicode_ci default NULL,
  `function` varchar(255) collate utf8_unicode_ci default NULL,
  `title` varchar(255) collate utf8_unicode_ci default NULL,
  `description` text collate utf8_unicode_ci,
  `preview_image` varchar(255) collate utf8_unicode_ci default NULL,
  `paramater_CSV` text collate utf8_unicode_ci,
  `paramater_defaults` text collate utf8_unicode_ci,
  PRIMARY KEY  (`dashboard_module_id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
INSERT INTO dashboard_module VALUES
  (1, 'graphs', 'weeklyActivity', 'weeklyActivityDashboard', 'Weekly Activity', 'Shows the current week and previous week activity count on a bar graph.', 'index.php?m=graphs&a=weeklyActivity&width=365&height=180', NULL, NULL),
  (2, 'calendar', 'calendarToday', 'summaryDashboardToday', 'Todays Events', 'Shows a list of todays events.', 'images/dashboard_preview/upcoming_events.png', '\"Number of events to show: ,vartextshort\"', NULL),
  (3, 'generic', 'titleBar', 'titleBar', 'Title Bar', 'Displays a custom title bar.', 'images/dashboard_preview/title_bar.png', '\"Title: ,vartext\"', ''),
  (4, 'generic', 'text', 'text', 'Text', 'Displays text.', 'images/dashboard_preview/textbox.png', '\"Text: ,vartextlong\"', NULL),
  (5, 'jobOrders', 'recentJobOrders', 'recentJobOrdersDashboard', 'Recent Job Orders', 'Displays the latest job orders added to the system.', 'images/dashboard_preview/recent_job_orders.png', '\"Number of job orders to display: ,vartextshort\"', '\"6\"'),
  (6, 'generic', 'html', 'html', 'HTML', 'Displays formatted HTML.', NULL, '\"HTML Code: ,vartextlong\"', NULL),
  (7, 'graphs', 'newCandidates', 'newCandidatesDashboard', 'New Candidates', 'Shows the current week and previous week new candidate count on a bar graph.', 'index.php?m=graphs&a=newCandidates&width=300&height=180', NULL, NULL),
  (8, 'graphs', 'newJobOrders', 'newJobOrdersDashboard', 'New Job Orders', 'Shows the current week and previous week new job order count on a bar graph.', 'index.php?m=graphs&a=newJobOrders&width=300&height=180', NULL, NULL),
  (9, 'graphs', 'newSubmissions', 'newSubmissionsDashboard', 'New Submissions', 'Shows the current week and previous week new count of how many candidates were submitted to job orders on a bar graph.', 'index.php?m=graphs&a=newSubmissions&width=300&height=180', NULL, NULL),
  (10, 'generic', 'image', 'image', 'Image', 'Displays an image.', 'images/dashboard_preview/image.png', '\"URL to image: ,vartext\",\"Optional hyperlink URL: ,vartext\"', NULL),
  (11, 'graphs', 'pipeline', 'pipelineDashboard', 'Pipeline', 'Displays the current pipeline status on a graph.', 'images/dashboard_preview/pipeline.png', '\"Candidate Pipeline Color: ,colorpickerartichow\",\"Resume Recieved Color: ,colorpickerartichow\",\"Submitted Color: ,colorpickerartichow\",\"Rejected Color: ,colorpickerartichow\",\"Interviews Color: ,colorpickerartichow\",\"Offers Color: ,colorpickerartichow\",\"Accepted Color: ,colorpickerartichow\"', '\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"Orange\",\"DarkGreen\",\"DarkGreen\"');
DROP TABLE IF EXISTS `dashboard_component`;
CREATE TABLE `dashboard_component` (
  `dashboard_component_id` int(11) NOT NULL auto_increment,
  `module_name` varchar(255) collate utf8_unicode_ci default NULL,
  `module_paramaters` text collate utf8_unicode_ci,
  `site_id` int(11) default NULL,
  `column_number` int(11) default NULL,
  `position` int(11) default NULL,
  PRIMARY KEY  (`dashboard_component_id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
INSERT INTO dashboard_component SELECT NULL, 'text', '\"Welcome, ((USERNAME))!\r\nDate: ((DATE))\r\nTime: ((TIME))\"', site_id, 0, 1 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'calendarToday', '\"2\"', site_id, 0, 2 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'titleBar', '\"Current Status:\"', site_id, 2, 0 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'weeklyActivity', NULL, site_id, 2, 2 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'newCandidates', NULL, site_id, 2, 3 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'text', '\"((LASTWEEKTHROUGHTHISWEEK)) (Last 2 weeks):\"', site_id, 2, 1 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'html', '\"<a href=\"\"http://www.catsone.com/forum/\"\" target=newwin1> CATS Forums</a><br />\r\n<a href=\"\"http://www.cognizo.com\"\" target=newwin2>Cognizo Technologies</a>\"', site_id, 1, 2 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'newSubmissions', NULL, site_id, 2, 4 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'pipeline', '\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"Orange\",\"DarkGreen\",\"DarkGreen\"', site_id, 1, 0 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'recentJobOrders', '\"12\"', site_id, 0, 3 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'titleBar', '\"Welcome!\"', site_id, 0, 0 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'titleBar', '\"My Links:\"', site_id, 1, 1  FROM site;

#r567 8-19-6 WB
UPDATE system SET schema_version = 567;
UPDATE `candidate_joborder` SET status = 400 WHERE submitted = 1;
TRUNCATE TABLE `candidate_joborder_status_history`;
ALTER TABLE `candidate_joborder_status_history` ADD COLUMN `site_id` int(11) NOT NULL;
INSERT INTO `candidate_joborder_status_history` (candidate_id, joborder_id, date, status_from, status_to, site_id)
        SELECT candidate_id, joborder_id, date_submitted, 0, 400, site_id FROM candidate_joborder WHERE submitted = 1;
ALTER TABLE `candidate_joborder` DROP `submitted`;

#r572 8-20-6 WB
UPDATE system SET schema_version = 572;
ALTER TABLE `hot_list` CHANGE `hot_list_description` `description` varchar(64) character set utf8 collate utf8_unicode_ci NOT NULL;
ALTER TABLE `hot_list` CHANGE `hot_list_type` `data_item_type` int(11) NOT NULL default '0';

#r586 8-21-6 BH
UPDATE system SET schema_version = 586;
UPDATE dashboard_component SET module_paramaters = '\"((SITENAME)) Links:\"' WHERE module_paramaters = '\"My Links:\"';

#r587 8-21-6 BH
UPDATE system SET schema_version = 587;
CREATE TABLE `calendar_settings` (
  `calendar_settings_id` int(11) NOT NULL auto_increment,
  `the_field` varchar(255) NOT NULL default '',
  `the_value` varchar(255) default NULL,
  `site_id` int(11) NOT NULL default '0',
  `entered_by` int(11) default NULL,
  PRIMARY KEY  (`calendar_settings_id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

#r593 8-21-6 BH
UPDATE system SET schema_version = 593;
CREATE TABLE `candidate_foreign_settings` (
  `alien_id` int(11) NOT NULL auto_increment,
  `field_name` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `import_id` int(11) default NULL,
  `site_id` int(11) NOT NULL default '0',
  `date_created` datetime default NULL,
  PRIMARY KEY  (`alien_id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

#r595 8-21-6 BH
UPDATE system SET schema_version = 595;
CREATE TABLE `client_foreign_settings` (
  `alien_id` int(11) NOT NULL auto_increment,
  `field_name` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `import_id` int(11) default NULL,
  `site_id` int(11) NOT NULL default '0',
  `date_created` datetime default NULL,
  PRIMARY KEY  (`alien_id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

#r596 8-21-6 BH
UPDATE system SET schema_version = 596;
CREATE TABLE `contact_foreign_settings` (
  `alien_id` int(11) NOT NULL auto_increment,
  `field_name` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `import_id` int(11) default NULL,
  `site_id` int(11) NOT NULL default '0',
  `date_created` datetime default NULL,
  PRIMARY KEY  (`alien_id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `contact` CHANGE `department_id` `department_id` int(11) default '0';

#r601 8-22-6 PC
UPDATE system SET schema_version = 601;
ALTER TABLE `candidate` DROP COLUMN hot_list_id;
CREATE TABLE `hot_list_entries` (
  `hot_list_id` int(11) NOT NULL DEFAULT 0,
  `data_item_type` int(11) NOT NULL DEFAULT 0,
  `data_item_id` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

#r614 8-23-6 BH
UPDATE system SET schema_version = 614;
ALTER TABLE `user_login` ADD COLUMN `host` varchar(255) default NULL;

#r661 8-25-6 BH
UPDATE system SET schema_version = 661;
UPDATE system SET disable_version_check = 0;
UPDATE system SET date_version_checked = 0;

#r673 8-28-6 WB
UPDATE system SET schema_version = 673;
ALTER TABLE `dashboard_module` CHANGE `paramater_CSV` `parameter_CSV` text collate utf8_unicode_ci;
ALTER TABLE `dashboard_module` CHANGE `paramater_defaults` `parameter_defaults` text collate utf8_unicode_ci;
ALTER TABLE `dashboard_component` CHANGE `module_paramaters` `module_parameters` text collate utf8_unicode_ci;
UPDATE dashboard_module SET parameter_CSV = '"No Status Color: ,colorpickerartichow","No Contact Color: ,colorpickerartichow","Contacted Color: ,colorpickerartichow","Negotiating Color: ,colorpickerartichow","Submitted Color: ,colorpickerartichow","Interviewing Color: ,colorpickerartichow","Offered Color: ,colorpickerartichow","Passed On Color: ,colorpickerartichow","Placed Color: ,colorpickerartichow"' WHERE name = "pipeline";
UPDATE dashboard_module SET parameter_defaults = '"DarkGreen","DarkGreen","DarkGreen","DarkGreen","Orange","DarkGreen","DarkGreen","DarkGreen","DarkGreen"' WHERE name = "pipeline";

#r674 8-29-6 BH
UPDATE system SET schema_version = 674;
UPDATE dashboard_component SET module_parameters = "\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"Orange\",\"DarkGreen\",\"AlmostBlack\",\"DarkGreen\"" WHERE module_name = "pipeline";
UPDATE dashboard_module SET parameter_defaults = "\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"Orange\",\"DarkGreen\",\"AlmostBlack\",\"DarkGreen\"" WHERE name = "pipeline";

#r675 8-29-6 BH
UPDATE system SET schema_version = 675;
UPDATE dashboard_module SET parameter_CSV = '"No Contact Color: ,colorpickerartichow","Contacted Color: ,colorpickerartichow","Negotiating Color: ,colorpickerartichow","Submitted Color: ,colorpickerartichow","Interviewing Color: ,colorpickerartichow","Offered Color: ,colorpickerartichow","Passed On Color: ,colorpickerartichow","Placed Color: ,colorpickerartichow"' WHERE name = "pipeline";
UPDATE dashboard_component SET module_parameters = "\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"Orange\",\"DarkGreen\",\"AlmostBlack\",\"DarkGreen\"" WHERE module_name = "pipeline";
UPDATE dashboard_module SET parameter_defaults = "\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"Orange\",\"DarkGreen\",\"AlmostBlack\",\"DarkGreen\"" WHERE name = "pipeline";

#r700 8-30-6 BH
UPDATE system SET schema_version = 700;
DELETE FROM dashboard_component;
INSERT INTO dashboard_component SELECT NULL, 'text', '\"Welcome, ((USERNAME))!\r\nDate: ((DATE))\r\nTime: ((TIME))\"', site_id, 0, 1 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'calendarToday', '\"2\"', site_id, 0, 2 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'titleBar', '\"Current Status: ((LASTWEEKTHROUGHTHISWEEK)) (Last 2 weeks):\"', site_id, 2, 0 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'newJobOrders', null, site_id, 2, 3 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'newCandidates', null, site_id, 2, 3 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'recentJobOrders', '\"6\"', site_id, 1, 1 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'weeklyActivity', null, site_id, 0, 6 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'titleBar', '\"Last 2 weeks activity:\"', site_id, 0, 5 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'newSubmissions', null, site_id, 2, 4 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'pipeline', '\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"Orange\",\"DarkGreen\",\"AlmostBlack\",\"DarkGreen\"', site_id, 1, 0 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'titleBar', '\"((SITENAME)) Links:\"', site_id, 0, 3 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'titleBar', '\"Welcome!\"', site_id, 0, 0 FROM site;
INSERT INTO dashboard_component SELECT NULL, 'html', '\"<a href=\"\"http://www.catsone.com/forum/\"\" target=newwin1> CATS Forums</a><br />\r\n<a href=\"\"http://www.cognizo.com\"\" target=newwin2>Cognizo Technologies</a>\"', site_id, 0, 4 FROM site;

#r701 8-29-6 BH
UPDATE system SET schema_version = 701;
UPDATE dashboard_module SET parameter_CSV = '"Total Pipeline Color: ,colorpickerartichow","Contacted Color: ,colorpickerartichow","Negotiating Color: ,colorpickerartichow","Submitted Color: ,colorpickerartichow","Interviewing Color: ,colorpickerartichow","Offered Color: ,colorpickerartichow","Passed On Color: ,colorpickerartichow","Placed Color: ,colorpickerartichow"' WHERE name = "pipeline";

#r752 9-5-6 WB
UPDATE system SET schema_version = 752;
CREATE TABLE `email_template` (
  `email_template_id` INT NOT NULL AUTO_INCREMENT ,
  `text` TEXT,
  `allow_substitution` INT( 1 ) DEFAULT '0' NOT NULL ,
  `site_id` INT DEFAULT '0' NOT NULL ,
  PRIMARY KEY ( `email_template_id` )
) TYPE = MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `candidate` CHANGE `email1` `email1` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `candidate` CHANGE `email2` `email2` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `candidate` CHANGE `web_site` `web_site` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `candidate` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `client` CHANGE `fax_number` `fax_number` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `client` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `calendar_event_type` CHANGE `icon_image` `icon_image` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `calendar_event_type` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `candidate_foreign_settings` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `client_foreign_settings` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `contact_foreign_settings` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `hot_list_entries` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `joborder` CHANGE `client_job_id` `client_job_id` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `joborder` CHANGE `rate_max` `rate_max` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `joborder` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `mru` CHANGE `data_item_text` `data_item_text` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `mru` CHANGE `url` `url` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `mru` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `candidate_source` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `candidate_source` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

#r753 9-5-6 BH
UPDATE system SET schema_version = 753;
UPDATE dashboard_component SET module_parameters = '\"6\"' where module_name = "calendarToday";
UPDATE dashboard_component SET module_parameters = '\"8\"' where module_name = "recentJobOrders";
ALTER IGNORE TABLE email_template ADD COLUMN template_title VARCHAR(255) default NULL;

#r754 9-5-6 BH
UPDATE system SET schema_version = 754;
ALTER TABLE email_template CHANGE template_title tag VARCHAR(255) default NULL;
ALTER IGNORE TABLE email_template ADD COLUMN title VARCHAR(255) default NULL;

#r755 9-5-6 BH
UPDATE system SET schema_version = 755;
INSERT INTO email_template SELECT NULL, '%%DATETIME%%\r\n\r\nDear %%CANDFULLNAME%%,\r\n\r\nThis E-Mail is a notification that your status in our database has been changed to %%CANDSTATUS%%.\r\n\r\nTake care,\r\n%%USERFULLNAME%%\r\n%%SITENAME%%', 1, site_id, 'EMAIL_TEMPLATE_STATUSCHANGE', 'Status Changed (Sent to Candidate)' FROM site;
INSERT INTO email_template SELECT NULL, '%%DATETIME%%\r\n\r\nDear %%USERFULLNAME%%,\r\n\r\nThis E-Mail is a notification that one of your candidates (%%CANDFULLNAME%%) has changed ownership.\r\n\r\nCandidate Name: %%CANDFULLNAME%%\r\nNew Candidate Owner: %%CANDOWNER%%\r\nCandidate URL: %%CANDCATSURL%%\r\n\r\nTake care,\r\nCATS \r\n%%SITENAME%%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPCHANGE', 'Ownership Changed (Sent to Recruiter)' FROM site;

#r756 9-5-6 BH
UPDATE system SET schema_version = 756;
DELETE FROM email_template WHERE tag = 'EMAIL_TEMPLATE_OWNERSHIPCHANGE';
INSERT INTO email_template SELECT NULL, '%%DATETIME%%\r\n\r\nDear %%USERFULLNAME%%,\r\n\r\nThis E-Mail is a notification that a candidate has been assigned to you.\r\n\r\nCandidate Name: %%CANDFULLNAME%%\r\nCandidate URL: %%CANDCATSURL%%\r\n\r\nTake care,\r\nCATS \r\n%%SITENAME%%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGN', 'Candidate Assigned (Sent to Assigned Recruiter)' FROM site;

#r757 9-5-6 WB
UPDATE system SET schema_version = 757;
ALTER TABLE `hot_list_entries` ADD `hot_list_entry_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `hot_list_entries` RENAME `hot_list_entry`;
ALTER TABLE `hot_list_entry` ADD INDEX `IDX_type_id` ( `data_item_type` , `data_item_id` );
ALTER TABLE `hot_list_entry` ADD INDEX `IDX_data_item_type` ( `data_item_type` );
ALTER TABLE `hot_list_entry` ADD INDEX `IDX_data_item_id` ( `data_item_id` );
ALTER TABLE `hot_list_entry` ADD INDEX `IDX_hot_list_id` ( `hot_list_id` );
ALTER TABLE `hot_list` ADD INDEX `IDX_data_item_type` ( `data_item_type` );
ALTER TABLE `hot_list` ADD INDEX `IDX_description` ( `description` );
ALTER TABLE `hot_list` ADD INDEX `IDX_site_id` ( `site_id` );
ALTER TABLE `hot_list_entry` ADD `site_id` INT DEFAULT '0' NOT NULL;
UPDATE hot_list_entry, hot_list
        SET hot_list_entry.site_id = hot_list.site_id
        WHERE hot_list_entry.hot_list_id = hot_list.hot_list_id;

#r760 9-5-6 BH
UPDATE system SET schema_version = 760;
ALTER IGNORE TABLE email_template ADD `possible_variables` text;
DELETE FROM email_template WHERE tag = 'EMAIL_TEMPLATE_OWNERSHIPASSIGN';
INSERT INTO email_template SELECT NULL, '%%DATETIME%%\r\n\r\nDear %%CANDFULLNAME%%,\r\n\r\nThis E-Mail is a notification that your status in our database has been changed to %%CANDSTATUS%%.\r\n\r\nTake care,\r\n%%USERFULLNAME%%\r\n%%SITENAME%%', 1, site_id, 'EMAIL_TEMPLATE_STATUSCHANGE', 'Status Changed (Sent to Candidate)', '%%CANDSTATUS%%%%CANDOWNER%%%%CANDFIRSTNAME%%%%CANDFULLNAME%%' FROM site; 
INSERT INTO email_template SELECT NULL, '%%DATETIME%%\r\n\r\nDear %%USERFULLNAME%%,\r\n\r\nThis E-Mail is a notification that a Client has been assigned to you.\r\n\r\nClient Name: %%CLNTNAME%%\r\nClient URL %%CLNTCATSURL%%\r\n\r\nTake care,\r\nCATS \r\n%%SITENAME%%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCLIENT', 'Client Assigned (Sent to Assigned Recruiter)', '%%CLNTOWNER%%%%CLNTNAME%%%%CLNTCATSURL%%' FROM site;
INSERT INTO email_template SELECT NULL, '%%DATETIME%%\r\n\r\nDear %%USERFULLNAME%%,\r\n\r\nThis E-Mail is a notification that a Contact has been assigned to you.\r\n\r\nContact Name: %%CONTFULLNAME%%\r\nContact URL: %%CONTCATSURL%%\r\n\r\nTake care,\r\nCATS \r\n%%SITENAME%%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCONTACT', 'Contact Assigned (Sent to Assigned Recruiter)', '%%CONTOWNER%%%%CONTFIRSTNAME%%%%CONTFULLNAME%%%%CONTCLIENTNAME%%%%CONTCATSURL%%' FROM site;
INSERT INTO email_template SELECT NULL, '%%DATETIME%%\r\n\r\nDear %%USERFULLNAME%%,\r\n\r\nThis E-Mail is a notification that a Job Order has been assigned to you.\r\n\r\nJob Order Title: %%JBODTITLE%%\r\nJob Order Client: %%JBODCLIENT%%\r\nJob Order URL: %%JBODCATSURL%%\r\n\r\nTake care,\r\nCATS \r\n%%SITENAME%%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNJOBORDER', 'Job Order Assigned (Sent to Assigned Recruiter)', '%%JBODOWNER%%%%JBODTITLE%%%%JBODCLIENT%%%%JBODCATSURL%%' FROM site;
INSERT INTO email_template SELECT NULL, '%%DATETIME%%\r\n\r\nDear %%USERFULLNAME%%,\r\n\r\nThis E-Mail is a notification that a candidate has been assigned to you.\r\n\r\nCandidate Name: %%CANDFULLNAME%%\r\nCandidate URL: %%CANDCATSURL%%\r\n\r\nTake care,\r\nCATS \r\n%%SITENAME%%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCANDIDATE', 'Candidate Assigned (Sent to Assigned Recruiter)', '%%CANDSTATUS%%%%CANDOWNER%%%%CANDFIRSTNAME%%%%CANDFULLNAME%%%%CANDCATSURL%%' FROM site;

#r761 9-5-6 BH
#r762 9-6-6 BH
UPDATE system SET schema_version = 762;
DELETE FROM email_template WHERE tag = 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCANDIDATE';
INSERT INTO email_template SELECT NULL, '%%DATETIME%%\r\n\r\nDear %%USERFULLNAME%%,\r\n\r\nThis E-Mail is a notification that a Candidate has been assigned to you.\r\n\r\nCandidate Name: %%CANDFULLNAME%%\r\nCandidate URL: %%CANDCATSURL%%\r\n\r\nTake care,\r\nCATS \r\n%%SITENAME%%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCANDIDATE', 'Candidate Assigned (Sent to Assigned Recruiter)', '%%CANDSTATUS%%%%CANDOWNER%%%%CANDFIRSTNAME%%%%CANDFULLNAME%%%%CANDCATSURL%%' FROM site;

#r763 9-6-6 BH
UPDATE system SET schema_version = 763;
ALTER IGNORE TABLE `candidate_joborder` ADD `match_value` int(5) default NULL;

#r764 9-6-6 BH
UPDATE system SET schema_version = 764;
ALTER TABLE `candidate_joborder` CHANGE `match_value` `rating_value` int(5) default NULL;

#r765 9-7-6 BH
#r766 9-7-6 BH
UPDATE system SET schema_version = 766;
DELETE FROM email_template;
INSERT INTO email_template SELECT NULL, '%DATETIME%\r\n\r\nDear %CANDFULLNAME%,\r\n\r\nThis E-Mail is a notification that your status in our database has been changed to %CANDSTATUS%.\r\n\r\nTake care,\r\n%USERFULLNAME%\r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_STATUSCHANGE', 'Status Changed (Sent to Candidate)', '%CANDSTATUS%%CANDOWNER%%CANDFIRSTNAME%%CANDFULLNAME%' FROM site; 
INSERT INTO email_template SELECT NULL, '%DATETIME%\r\n\r\nDear %USERFULLNAME%,\r\n\r\nThis E-Mail is a notification that a Client has been assigned to you.\r\n\r\nClient Name: %CLNTNAME%\r\nClient URL %CLNTCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCLIENT', 'Client Assigned (Sent to Assigned Recruiter)', '%CLNTOWNER%%CLNTNAME%%CLNTCATSURL%' FROM site;
INSERT INTO email_template SELECT NULL, '%DATETIME%\r\n\r\nDear %USERFULLNAME%,\r\n\r\nThis E-Mail is a notification that a Contact has been assigned to you.\r\n\r\nContact Name: %CONTFULLNAME%\r\nContact URL: %CONTCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCONTACT', 'Contact Assigned (Sent to Assigned Recruiter)', '%CONTOWNER%%CONTFIRSTNAME%%CONTFULLNAME%%CONTCLIENTNAME%%CONTCATSURL%' FROM site;
INSERT INTO email_template SELECT NULL, '%DATETIME%\r\n\r\nDear %USERFULLNAME%,\r\n\r\nThis E-Mail is a notification that a Job Order has been assigned to you.\r\n\r\nJob Order Title: %JBODTITLE%\r\nJob Order Client: %JBODCLIENT%\r\nJob Order URL: %JBODCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNJOBORDER', 'Job Order Assigned (Sent to Assigned Recruiter)', '%JBODOWNER%%JBODTITLE%%JBODCLIENT%%JBODCATSURL%' FROM site;
INSERT INTO email_template SELECT NULL, '%DATETIME%\r\n\r\nDear %USERFULLNAME%,\r\n\r\nThis E-Mail is a notification that a candidate has been assigned to you.\r\n\r\nCandidate Name: %CANDFULLNAME%\r\nCandidate URL: %CANDCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCANDIDATE', 'Candidate Assigned (Sent to Assigned Recruiter)', '%CANDSTATUS%%CANDOWNER%%CANDFIRSTNAME%%CANDFULLNAME%%CANDCATSURL%' FROM site;

#r780 9-7-6 WB
UPDATE system SET schema_version = 780;
UPDATE dashboard_component SET module_parameters = '\"Current Status: ((LASTWEEKTHROUGHTHISWEEK)) (Last Two Weeks)\"' WHERE module_name = 'titleBar' AND module_parameters = '\"Current Status: ((LASTWEEKTHROUGHTHISWEEK)) (Last 2 weeks):\"';
UPDATE dashboard_component SET module_parameters = '\"Activity (Last Two Weeks)\"' WHERE module_name = 'titleBar' AND module_parameters = '\"Last 2 weeks activity:\"';
UPDATE dashboard_component SET module_parameters = '\"((SITENAME)) Links\"' WHERE module_name = 'titleBar' AND module_parameters = '\"((SITENAME)) Links:\"';


#r792 9-7-6 WB
UPDATE system SET schema_version = 792;
ALTER TABLE `calendar_settings` CHANGE `the_field` `setting` varchar(255) NOT NULL default '';
ALTER TABLE `calendar_settings` CHANGE `the_value` `value` varchar(255) default NULL;
UPDATE `calendar_settings` SET setting = 'defaultPublic' WHERE setting = 'allPublic';

#r796 9-10-6 WB
UPDATE system SET schema_version = 796;
INSERT INTO `candidate_joborder_status` VALUES (650, 'N/A', 0, 1, 1);
UPDATE candidate_joborder_status SET short_description = 'Rejected by Client' WHERE candidate_joborder_status_id = 700;
UPDATE candidate_joborder_status SET triggers_email = 0 WHERE candidate_joborder_status_id IN (650, 700);

#r797 9-11-6 BH
UPDATE system SET schema_version = 797;
DELETE FROM email_template WHERE tag != 'EMAIL_TEMPLATE_STATUSCHANGE';

#r798 9-11-6 BH
UPDATE system SET schema_version = 798;
DELETE FROM email_template;
INSERT INTO email_template SELECT NULL, '* Auto generated message. Please DO NOT reply *\r\n%DATETIME%\r\n\r\nDear %CANDFULLNAME%,\r\n\r\nThis E-Mail is a notification that your status in our database has been changed.\r\n\r\nYour previous status was %CANDPREVSTATUS%.\r\nYour new status is %CANDSTATUS%.\r\n\r\nTake care,\r\n%USERFULLNAME%\r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_STATUSCHANGE', 'Status Changed (Sent to Candidate)', '%CANDSTATUS%%CANDOWNER%%CANDFIRSTNAME%%CANDFULLNAME%%CANDPREVSTATUS%' FROM site; 
INSERT INTO email_template SELECT NULL, '%DATETIME%\r\n\r\nDear %USERFULLNAME%,\r\n\r\nThis E-Mail is a notification that a Client has been assigned to you.\r\n\r\nClient Name: %CLNTNAME%\r\nClient URL %CLNTCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCLIENT', 'Client Assigned (Sent to Assigned Recruiter)', '%CLNTOWNER%%CLNTNAME%%CLNTCATSURL%' FROM site;
INSERT INTO email_template SELECT NULL, '%DATETIME%\r\n\r\nDear %USERFULLNAME%,\r\n\r\nThis E-Mail is a notification that a Contact has been assigned to you.\r\n\r\nContact Name: %CONTFULLNAME%\r\nContact URL: %CONTCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCONTACT', 'Contact Assigned (Sent to Assigned Recruiter)', '%CONTOWNER%%CONTFIRSTNAME%%CONTFULLNAME%%CONTCLIENTNAME%%CONTCATSURL%' FROM site;
INSERT INTO email_template SELECT NULL, '%DATETIME%\r\n\r\nDear %USERFULLNAME%,\r\n\r\nThis E-Mail is a notification that a Job Order has been assigned to you.\r\n\r\nJob Order Title: %JBODTITLE%\r\nJob Order Client: %JBODCLIENT%\r\nJob Order URL: %JBODCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNJOBORDER', 'Job Order Assigned (Sent to Assigned Recruiter)', '%JBODOWNER%%JBODTITLE%%JBODCLIENT%%JBODCATSURL%' FROM site;
INSERT INTO email_template SELECT NULL, '%DATETIME%\r\n\r\nDear %USERFULLNAME%,\r\n\r\nThis E-Mail is a notification that a candidate has been assigned to you.\r\n\r\nCandidate Name: %CANDFULLNAME%\r\nCandidate URL: %CANDCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCANDIDATE', 'Candidate Assigned (Sent to Assigned Recruiter)', '%CANDSTATUS%%CANDOWNER%%CANDFIRSTNAME%%CANDFULLNAME%%CANDCATSURL%' FROM site;

#r799 9-12-6 BH
UPDATE system SET schema_version = 799;
DELETE FROM email_template;
INSERT INTO email_template SELECT NULL, '* Auto generated message. Please DO NOT reply *\r\n%DATETIME%\r\n\r\nDear %CANDFULLNAME%,\r\n\r\nThis E-Mail is a notification that your status in our database has been changed for the position %JBODTITLE% (%JBODCLIENT%).\r\n\r\nYour previous status was <B>%CANDPREVSTATUS%</B>.\r\nYour new status is <B>%CANDSTATUS%</B>.\r\n\r\nTake care,\r\n%USERFULLNAME%\r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_STATUSCHANGE', 'Status Changed (Sent to Candidate)', '%CANDSTATUS%%CANDOWNER%%CANDFIRSTNAME%%CANDFULLNAME%%CANDPREVSTATUS%%JBODCLIENT%%JBODTITLE%' FROM site;
INSERT INTO email_template SELECT NULL, '%DATETIME%\r\n\r\nDear %USERFULLNAME%,\r\n\r\nThis E-Mail is a notification that a Client has been assigned to you.\r\n\r\nClient Name: %CLNTNAME%\r\nClient URL %CLNTCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCLIENT', 'Client Assigned (Sent to Assigned Recruiter)', '%CLNTOWNER%%CLNTNAME%%CLNTCATSURL%' FROM site;
INSERT INTO email_template SELECT NULL, '%DATETIME%\r\n\r\nDear %USERFULLNAME%,\r\n\r\nThis E-Mail is a notification that a Contact has been assigned to you.\r\n\r\nContact Name: %CONTFULLNAME%\r\nContact Client: %CONTCLIENTNAME%\r\nContact URL: %CONTCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCONTACT', 'Contact Assigned (Sent to Assigned Recruiter)', '%CONTOWNER%%CONTFIRSTNAME%%CONTFULLNAME%%CONTCLIENTNAME%%CONTCATSURL%' FROM site;
INSERT INTO email_template SELECT NULL, '%DATETIME%\r\n\r\nDear %USERFULLNAME%,\r\n\r\nThis E-Mail is a notification that a Job Order has been assigned to you.\r\n\r\nJob Order Title: %JBODTITLE%\r\nJob Order Client: %JBODCLIENT%\r\nJob Order ID: %JBODID%\r\nJob Order URL: %JBODCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNJOBORDER', 'Job Order Assigned (Sent to Assigned Recruiter)', '%JBODOWNER%%JBODTITLE%%JBODCLIENT%%JBODCATSURL%%JBODID%' FROM site;
INSERT INTO email_template SELECT NULL, '%DATETIME%\r\n\r\nDear %USERFULLNAME%,\r\n\r\nThis E-Mail is a notification that a candidate has been assigned to you.\r\n\r\nCandidate Name: %CANDFULLNAME%\r\nCandidate URL: %CANDCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCANDIDATE', 'Candidate Assigned (Sent to Assigned Recruiter)', '%CANDOWNER%%CANDFIRSTNAME%%CANDFULLNAME%%CANDCATSURL%' FROM site;

#r800 9-13-6 BH
UPDATE system SET schema_version = 800;
INSERT INTO candidate_joborder_status VALUES (250, 'Candidate Responded', 0, 1, 1);
UPDATE candidate_joborder_status SET triggers_email = 0 WHERE candidate_joborder_status_id = 200;
ALTER IGNORE TABLE `user` ADD `is_demo` int(1) default 0;
UPDATE user SET is_demo = 1 WHERE user_name = "john@customsearch.com" ;
UPDATE dashboard_component SET module_parameters = '\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"Orange\",\"DarkGreen\",\"AlmostBlack\",\"DarkGreen\"' WHERE module_name = 'pipeline';
UPDATE dashboard_module SET parameter_CSV = '\"Total Pipeline Color: ,colorpickerartichow\",\"Contacted Color: ,colorpickerartichow\",\"Candidate Replied Color: ,colorpickerartichow\",\"Negotiating Color: ,colorpickerartichow\",\"Submitted Color: ,colorpickerartichow\",\"Interviewing Color: ,colorpickerartichow\",\"Offered Color: ,colorpickerartichow\",\"Passed On Color: ,colorpickerartichow\",\"Placed Color: ,colorpickerartichow\"' WHERE name = 'pipeline';
UPDATE dashboard_module SET parameter_defaults = '\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"DarkGreen\",\"Orange\",\"DarkGreen\",\"AlmostBlack\",\"DarkGreen\"' WHERE name = 'pipeline';
UPDATE candidate_joborder_status SET triggers_email = 0 WHERE candidate_joborder_status_id = 100;

#r801 9-13-6 BH
UPDATE system SET schema_version = 801;
DELETE FROM email_template WHERE tag != 'EMAIL_TEMPLATE_STATUSCHANGE';
INSERT INTO email_template SELECT NULL, '%DATETIME%\r\n\r\nDear %CLNTOWNER%,\r\n\r\nThis E-Mail is a notification that a Client has been assigned to you.\r\n\r\nClient Name: %CLNTNAME%\r\nClient URL %CLNTCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCLIENT', 'Client Assigned (Sent to Assigned Recruiter)', '%CLNTOWNER%%CLNTNAME%%CLNTCATSURL%' FROM site;
INSERT INTO email_template SELECT NULL, '%DATETIME%\r\n\r\nDear %CONTOWNER%,\r\n\r\nThis E-Mail is a notification that a Contact has been assigned to you.\r\n\r\nContact Name: %CONTFULLNAME%\r\nContact Client: %CONTCLIENTNAME%\r\nContact URL: %CONTCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCONTACT', 'Contact Assigned (Sent to Assigned Recruiter)', '%CONTOWNER%%CONTFIRSTNAME%%CONTFULLNAME%%CONTCLIENTNAME%%CONTCATSURL%' FROM site;
INSERT INTO email_template SELECT NULL, '%DATETIME%\r\n\r\nDear %JBODOWNER%,\r\n\r\nThis E-Mail is a notification that a Job Order has been assigned to you.\r\n\r\nJob Order Title: %JBODTITLE%\r\nJob Order Client: %JBODCLIENT%\r\nJob Order ID: %JBODID%\r\nJob Order URL: %JBODCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNJOBORDER', 'Job Order Assigned (Sent to Assigned Recruiter)', '%JBODOWNER%%JBODTITLE%%JBODCLIENT%%JBODCATSURL%%JBODID%' FROM site;
INSERT INTO email_template SELECT NULL, '%DATETIME%\r\n\r\nDear %CANDOWNER%,\r\n\r\nThis E-Mail is a notification that a Candidate has been assigned to you.\r\n\r\nCandidate Name: %CANDFULLNAME%\r\nCandidate URL: %CANDCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%', 1, site_id, 'EMAIL_TEMPLATE_OWNERSHIPASSIGNCANDIDATE', 'Candidate Assigned (Sent to Assigned Recruiter)', '%CANDOWNER%%CANDFIRSTNAME%%CANDFULLNAME%%CANDCATSURL%' FROM site;

#r802 9-13-6 BH
UPDATE system SET schema_version = 802;
ALTER IGNORE TABLE `email_template` ADD `disabled` int(1) default 0;

#r803 9-13-6 BH
UPDATE system SET schema_version = 803;
UPDATE candidate_joborder_status SET short_description = 'Client Declined' WHERE candidate_joborder_status_id = 700;
UPDATE candidate_joborder_status SET short_description = 'Not in Consideration' WHERE candidate_joborder_status_id = 650;
UPDATE dashboard_module SET parameter_CSV = '\"Total Pipeline Color: ,colorpickerartichow\",\"Contacted Color: ,colorpickerartichow\",\"Candidate Replied Color: ,colorpickerartichow\",\"Negotiating Color: ,colorpickerartichow\",\"Submitted Color: ,colorpickerartichow\",\"Interviewing Color: ,colorpickerartichow\",\"Offered Color: ,colorpickerartichow\",\"Client Declined Color: ,colorpickerartichow\",\"Placed Color: ,colorpickerartichow\"' WHERE name = 'pipeline';

#r804 9-14-6 BH
UPDATE system SET schema_version = 804;
ALTER TABLE `contact` CHANGE `left_company` `left_company` int(1) DEFAULT '0' NOT NULL;

#r890 9-15-6 WB
UPDATE system SET schema_version = 890;
CREATE TABLE `mailer_settings` (
  `mailer_settings_id` int(11) NOT NULL auto_increment,
  `setting` varchar(255) NOT NULL default '',
  `value` varchar(255) default NULL,
  `site_id` int(11) NOT NULL default '0',
  `entered_by` int(11) default NULL,
  PRIMARY KEY (`mailer_settings_id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

#r900 9-15-6 WB
UPDATE system SET schema_version = 900;
DROP TABLE IF EXISTS `candidate_status_type`;
DROP TABLE IF EXISTS `candidate_joborder_status_type`;

#r901 9-15-6 WB
UPDATE system SET schema_version = 901;
DROP TABLE IF EXISTS `quotation`;

#r902 9-15-6 WB
UPDATE system SET schema_version = 902;
ALTER TABLE `system` DROP `local_version`;

#r903 9-18-6 BH
UPDATE system SET schema_version = 903;
DROP TABLE IF EXISTS `email_history`;
CREATE TABLE `email_history` (
  `email_sent_id` int(11) NOT NULL auto_increment,
  `from_addr` varchar(128) default NULL,
  `to_addr` varchar(192) default NULL,
  `text` text,
  `user_id` int(11) NOT NULL default '0',
  `site_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`email_sent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#r904 9-18-6 BH
UPDATE system SET schema_version = 904;
ALTER IGNORE TABLE email_history ADD COLUMN date datetime default NULL;
