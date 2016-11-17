ALTER TABLE `access_level` DROP INDEX `IDX_access_level1`;
ALTER TABLE `activity` DROP INDEX `IDX_activity1`;
ALTER TABLE `activity` DROP INDEX `IDX_activity3`;
ALTER TABLE `activity` DROP INDEX `IDX_activity4`;
ALTER TABLE `activity` DROP INDEX `IDX_activity5`;
ALTER TABLE `activity` DROP INDEX `IDX_activity7`;
ALTER TABLE `activity` DROP INDEX `IDX_activity6`;
ALTER TABLE `activity` DROP INDEX `IDX_activity8`;
ALTER TABLE `activity` DROP INDEX `IDX_activity9`;
ALTER TABLE `attachment` DROP INDEX `IDX_attachment1`;
ALTER TABLE `attachment` DROP INDEX `IDX_attachment5`;
ALTER TABLE `attachment` DROP INDEX `IDX_attachment2`;
ALTER TABLE `attachment` DROP INDEX `IDX_attachment_fulltext1`;
ALTER TABLE `calendar_event_type` DROP INDEX `IDX_calendar_event_type1`;
ALTER TABLE `candidate` DROP INDEX `IDX_candidate1`;
ALTER TABLE `candidate` DROP INDEX `IDX_candidate2`;
ALTER TABLE `candidate` DROP INDEX `IDX_candidate4`;
ALTER TABLE `candidate` DROP INDEX `IDX_candidate5`;
ALTER TABLE `candidate` DROP INDEX `IDX_candidate6`;
ALTER TABLE `candidate` DROP INDEX `IDX_candidate7`;
ALTER TABLE `candidate` DROP INDEX `IDX_candidate8`;
ALTER TABLE `candidate` DROP INDEX `IDX_candidate9`;
ALTER TABLE `candidate` DROP INDEX `IDX_candidate10`;
ALTER TABLE `candidate` DROP INDEX `IDX_candidate11`;
ALTER TABLE `candidate` DROP INDEX `IDX_candidate13`;
ALTER TABLE `candidate` DROP COLUMN `email`;
ALTER TABLE `candidate_joborder` DROP INDEX `IDX_candidate_joborder1`;
ALTER TABLE `candidate_joborder` DROP INDEX `IDX_candidate_joborder2`;
ALTER TABLE `candidate_joborder` DROP INDEX `IDX_candidate_joborder4`;
ALTER TABLE `candidate_joborder` DROP INDEX `IDX_candidate_joborder5`;
ALTER TABLE `candidate_joborder` DROP INDEX `IDX_candidate_joborder6`;
ALTER TABLE `candidate_joborder` DROP INDEX `IDX_candidate_joborder7`;
ALTER TABLE `candidate_jobordrer_status_type` DROP INDEX `IDX_candidate_status_type1`;
ALTER TABLE `client` DROP INDEX `IDX_client2`;
ALTER TABLE `client` DROP INDEX `IDX_client3`;
ALTER TABLE `client` DROP INDEX `IDX_client4`;
ALTER TABLE `client` DROP INDEX `IDX_client7`;
ALTER TABLE `client` DROP INDEX `IDX_client5`;
ALTER TABLE `client` DROP INDEX `IDX_client6`;
ALTER TABLE `contact` DROP INDEX `IDX_contact2`;
ALTER TABLE `contact` DROP INDEX `IDX_contact3`;
ALTER TABLE `contact` DROP INDEX `IDX_contact4`;
ALTER TABLE `contact` DROP INDEX `IDX_contact12`;
ALTER TABLE `contact` DROP INDEX ` IDX_contact13`;
ALTER TABLE `contact` DROP INDEX `IDX_contact11`;
ALTER TABLE `contact` DROP INDEX `IDX_contact5`;
ALTER TABLE `data_item_type` DROP INDEX `IDX_data_item_type1`;
ALTER TABLE `joborder` DROP INDEX `IDX_joborder2`;
ALTER TABLE `joborder` DROP INDEX `IDX_joborder7`;
ALTER TABLE `joborder` DROP INDEX `IDX_joborder3`;
ALTER TABLE `joborder` DROP INDEX `IDX_joborder8`;
ALTER TABLE `joborder` DROP INDEX `IDX_joborder11`;
ALTER TABLE `joborder` DROP INDEX `IDX_joborder13`;
ALTER TABLE `joborder` DROP INDEX `IDX_joborder14`;
ALTER TABLE `joborder` DROP INDEX `IDX_joborder1`;
ALTER TABLE `joborder` DROP INDEX `IDX_joborder16`;
ALTER TABLE `joborder` DROP INDEX `IDX_joborder17`;
ALTER TABLE `joborder` DROP INDEX `IDX_joborder5`;
ALTER TABLE `joborder` DROP INDEX `IDX_joborder10`;
ALTER TABLE `joborder_status` DROP INDEX `IDX_joborder_status1`;
ALTER TABLE `user` DROP INDEX `IDX_user3`;
ALTER TABLE `user` DROP INDEX `IDX_user2`;
ALTER TABLE `user` DROP INDEX `IDX_user4`;
ALTER TABLE `user_login` DROP INDEX `IDX_user_login1`;
ALTER TABLE `user_login` DROP INDEX `IDX_user_login3`;
ALTER TABLE `user_login` DROP INDEX `IDX_user_login4`;
ALTER TABLE `work_status_type` DROP INDEX `IDX_work_status_type1`;

CREATE TABLE `admin_user` (
  `user_id` int(11) NOT NULL auto_increment,
  `user_name` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  `password` varchar(10) collate utf8_unicode_ci NOT NULL default '',
  `disabled` int(1) NOT NULL default '0',
  `can_change_password` int(1) NOT NULL default '1',
  `last_name` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  `first_name` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`user_id`),
  KEY `IDX_first_name` (`first_name`),
  KEY `IDX_last_name` (`last_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `admin_user_login` (
  `user_login_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `ip` varchar(128) collate utf8_unicode_ci NOT NULL default '',
  `user_agent` varchar(255) collate utf8_unicode_ci default NULL,
  `date` datetime NOT NULL default '1000-01-01 00:00:00',
  `successful` int(1) NOT NULL default '0',
  PRIMARY KEY  (`user_login_id`),
  KEY `IDX_user_id` (`user_id`),
  KEY `IDX_ip` (`ip`),
  KEY `IDX_date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `candidate_joborder_status` (
  `candidate_joborder_status_id` int(11) NOT NULL auto_increment,
  `type` int(11) NOT NULL default '0',
  `candidate_id` int(11) NOT NULL default '0',
  `joborder_id` int(11) NOT NULL default '0',
  `site_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`candidate_joborder_status_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `candidate_status_type` (
  `candidate_status_type_id` int(11) NOT NULL default '0',
  `short_description` varchar(32) collate utf8_unicode_ci NOT NULL default '',
  `can_be_scheduled` int(1) NOT NULL default '0',
  PRIMARY KEY  (`candidate_status_type_id`),
  KEY `IDX_candidate_status_type1` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `site_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '1000-01-01 00:00:00',
  `subject` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `reply_to_address` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `reply_to_name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `feedback` text collate utf8_unicode_ci NOT NULL,
  `archived` int(1) NOT NULL default '0',
  PRIMARY KEY  (`feedback_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `mru` (
  `mru_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `site_id` int(11) NOT NULL default '0',
  `data_item_type` int(11) NOT NULL default '0',
  `data_item_text` varchar(64) character set utf8 NOT NULL default '',
  `url` varchar(255) character set utf8 NOT NULL default '',
  `date_created` datetime NOT NULL default '1000-01-01 00:00:00',
  PRIMARY KEY  (`mru_id`),
  KEY `IDX_user_site` (`user_id`,`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `site` (
  `site_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `is_demo` int(1) NOT NULL default '0',
  `user_licenses` int(11) NOT NULL default '0',
  `entered_by` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '1000-01-01 00:00:00',
  PRIMARY KEY  (`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `zipcodes` (
  `zipcode` mediumint(9) NOT NULL default '0',
  `city` tinytext collate utf8_unicode_ci NOT NULL,
  `state` varchar(2) collate utf8_unicode_ci NOT NULL default '',
  `areacode` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`zipcode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `system` (
  `system_id` int(20) NOT NULL default '0',
  `uid` int(20) default NULL,
  `local_version` int(20) default NULL,
  `avaliable_version` int(20) default NULL,
  `date_version_checked` date NOT NULL,
  `avaliable_version_description` text,
  `disable_version_check` int(1) NOT NULL default '0',
  PRIMARY KEY  (`system_id`)
);
CREATE TABLE `version` (
  `db_version` VARCHAR(10)
);

ALTER TABLE `activity` ADD COLUMN `site_id` INTEGER(11) NOT NULL DEFAULT '1' ;
ALTER TABLE `attachment` MODIFY COLUMN `attachment_id` INTEGER(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `attachment` MODIFY COLUMN `data_item_id` INTEGER(11) NOT NULL DEFAULT '0' ;
ALTER TABLE `attachment` ADD COLUMN `site_id` INTEGER(11) NOT NULL DEFAULT '1' ;
ALTER TABLE `calendar_event` ADD COLUMN `site_id` INTEGER(11) NOT NULL DEFAULT '1';
ALTER TABLE `candidate` ADD COLUMN `site_id` INTEGER(11) NOT NULL DEFAULT '1' ;
ALTER TABLE `candidate` ADD COLUMN `can_relocate` INTEGER(1) NOT NULL DEFAULT '0';
ALTER TABLE `candidate` ADD COLUMN `current_employer` VARCHAR(128) COLLATE utf8_unicode_ci DEFAULT NULL;
ALTER TABLE `candidate` ADD COLUMN `email1` VARCHAR(128) COLLATE utf8_general_ci DEFAULT NULL UNIQUE;
ALTER TABLE `candidate` ADD COLUMN `email2` VARCHAR(128) COLLATE utf8_general_ci DEFAULT NULL;
ALTER TABLE `candidate` ADD COLUMN `web_site` VARCHAR(128) NULL DEFAULT NULL;
ALTER TABLE `candidate_joborder` ADD COLUMN `site_id` INTEGER(11) NOT NULL DEFAULT '1';
ALTER TABLE `client` ADD COLUMN `site_id` INTEGER(11) NOT NULL DEFAULT '1';
ALTER TABLE `client` ADD COLUMN `is_hot` INTEGER(1) DEFAULT NULL;
ALTER TABLE `client` ADD COLUMN `fax_number` VARCHAR(40) DEFAULT NULL;
ALTER TABLE `contact` ADD COLUMN `site_id` INTEGER(11) NOT NULL DEFAULT '1';
ALTER TABLE `contact` ADD COLUMN `is_hot` INTEGER(1) DEFAULT NULL;
ALTER TABLE `contact` ADD `left_company` int( 1 ) ;
ALTER TABLE `joborder` ADD COLUMN `site_id` INTEGER(11) NOT NULL DEFAULT '1';
ALTER TABLE `joborder` ADD COLUMN `client_job_id` VARCHAR(32) COLLATE utf8_general_ci DEFAULT NULL;
ALTER TABLE `user` ADD COLUMN `site_id` INTEGER(11) NOT NULL DEFAULT '1';
ALTER TABLE `user` ADD COLUMN `email` VARCHAR(128) COLLATE utf8_unicode_ci DEFAULT NULL;
ALTER TABLE `user_login` ADD COLUMN `site_id` INTEGER(11) NOT NULL DEFAULT '1';
ALTER TABLE `access_level` ADD KEY `IDX_access_level` (`short_description`);

ALTER TABLE `activity` ADD KEY `IDX_data_item_id` (`data_item_id`);
ALTER TABLE `activity` ADD KEY `IDX_entered_by` (`entered_by`);
ALTER TABLE `activity` ADD KEY `IDX_site_id` (`site_id`);
ALTER TABLE `activity` ADD KEY `IDX_type` (`type`);
ALTER TABLE `activity` ADD KEY `IDX_data_item_type` (`data_item_type`);
ALTER TABLE `activity` ADD KEY `IDX_type_id` (`data_item_type`, `data_item_id`);
ALTER TABLE `activity` ADD KEY `IDX_joborder_id` (`joborder_id`);
ALTER TABLE `activity` ADD KEY `IDX_date_created` (`date_created`);
ALTER TABLE `activity` ADD KEY `IDX_date_modified` (`date_modified`);
ALTER TABLE `attachment` ADD KEY `IDX_site_id` (`site_id`);
ALTER TABLE `attachment` ADD KEY `IDX_data_item_type` (`data_item_type`);
ALTER TABLE `attachment` ADD KEY `IDX_type_id` (`data_item_type`, `data_item_id`);
ALTER TABLE `attachment` ADD KEY `IDX_date_modified` (`date_modified`);
ALTER TABLE `attachment` ADD KEY `IDX_data_item_id` (`data_item_id`);
ALTER TABLE `attachment` ADD FULLTEXT KEY `IDX_text` (`text`);
ALTER TABLE `calendar_event_type` ADD KEY `IDX_short_description` (`short_description`);
ALTER TABLE `candidate` ADD KEY `IDX_site_id` (`site_id`);
ALTER TABLE `candidate` ADD KEY `IDX_first_name` (`first_name`);
ALTER TABLE `candidate` ADD KEY `IDX_last_name` (`last_name`);
ALTER TABLE `candidate` ADD KEY `IDX_phone_home` (`phone_home`);
ALTER TABLE `candidate` ADD KEY `IDX_phone_cell` (`phone_cell`);
ALTER TABLE `candidate` ADD KEY `IDX_phone_work` (`phone_work`);
ALTER TABLE `candidate` ADD KEY `IDX_key_skills` (`key_skills`(255));
ALTER TABLE `candidate` ADD KEY `IDX_entered_by` (`entered_by`);
ALTER TABLE `candidate` ADD KEY `IDX_owner` (`owner`);
ALTER TABLE `candidate` ADD KEY `IDX_date_created` (`date_created`);
ALTER TABLE `candidate` ADD KEY `IDX_date_modified` (`date_modified`);
ALTER TABLE `candidate` ADD KEY `IDX_email1` (`email1`);
ALTER TABLE `candidate_joborder` ADD KEY `IDX_candidate_id` (`candidate_id`);
ALTER TABLE `candidate_joborder` ADD KEY `IDX_joborder_id` (`joborder_id`);
ALTER TABLE `candidate_joborder` ADD KEY `IDX_site_id` (`site_id`);
ALTER TABLE `candidate_joborder` ADD KEY `IDX_submitted` (`submitted`);
ALTER TABLE `candidate_joborder` ADD KEY `IDX_date_submitted` (`date_submitted`);
ALTER TABLE `candidate_joborder` ADD KEY `IDX_date_created` (`date_created`);
ALTER TABLE `candidate_joborder` ADD KEY `IDX_date_modified` (`date_modified`);
ALTER TABLE `candidate_jobordrer_status_type` ADD KEY `IDX_short_description` (`short_description`);
ALTER TABLE `client` ADD KEY `IDX_site_id` (`site_id`);
ALTER TABLE `client` ADD KEY `IDX_name` (`name`);
ALTER TABLE `client` ADD KEY `IDX_key_technologies` (`key_technologies`(255));
ALTER TABLE `client` ADD KEY `IDX_entered_by` (`entered_by`);
ALTER TABLE `client` ADD KEY `IDX_owner` (`owner`);
ALTER TABLE `client` ADD KEY `IDX_date_created` (`date_created`);
ALTER TABLE `client` ADD KEY `IDX_date_modified` (`date_modified`);
ALTER TABLE `client` ADD KEY `IDX_is_hot` (`is_hot`);
ALTER TABLE `contact` ADD KEY `IDX_site_id` (`site_id`);
ALTER TABLE `contact` ADD KEY `IDX_first_name` (`first_name`);
ALTER TABLE `contact` ADD KEY `IDX_last_name` (`last_name`);
ALTER TABLE `contact` ADD KEY `IDX_client_id` (`client_id`);
ALTER TABLE `contact` ADD KEY ` IDX_title` (`title`);
ALTER TABLE `contact` ADD KEY `IDX_owner` (`owner`);
ALTER TABLE `contact` ADD KEY `IDX_date_created` (`date_created`);
ALTER TABLE `contact` ADD KEY `IDX_date_modified` (`date_modified`);
ALTER TABLE `data_item_type` ADD KEY `IDX_short_description` (`short_description`);
ALTER TABLE `joborder` ADD KEY `IDX_recruiter` (`recruiter`);
ALTER TABLE `joborder` ADD KEY `IDX_site_id` (`site_id`);
ALTER TABLE `joborder` ADD KEY `IDX_title` (`title`);
ALTER TABLE `joborder` ADD KEY `IDX_client_id` (`client_id`);
ALTER TABLE `joborder` ADD KEY `IDX_status` (`status`);
ALTER TABLE `joborder` ADD KEY `IDX_start_date` (`start_date`);
ALTER TABLE `joborder` ADD KEY `IDX_contact_id` (`contact_id`);
ALTER TABLE `joborder` ADD KEY `IDX_is_hot` (`is_hot`);
ALTER TABLE `joborder` ADD KEY `IDX_jopenings` (`openings`);
ALTER TABLE `joborder` ADD KEY `IDX_owner` (`owner`);
ALTER TABLE `joborder` ADD KEY `IDX_entered_by` (`entered_by`);
ALTER TABLE `joborder` ADD KEY `IDX_date_created` (`date_created`);
ALTER TABLE `joborder` ADD KEY `IDX_date_modified` (`date_modified`);
ALTER TABLE `joborder_status` ADD KEY `IDX_short_description` (`short_description`);
ALTER TABLE `user` ADD KEY `IDX_site_id` (`site_id`);
ALTER TABLE `user` ADD KEY `IDX_first_name` (`first_name`);
ALTER TABLE `user` ADD KEY `IDX_last_name` (`last_name`);
ALTER TABLE `user` ADD KEY `IDX_access_level` (`access_level`);
ALTER TABLE `user_login` ADD KEY `IDX_user_id` (`user_id`);
ALTER TABLE `user_login` ADD KEY `IDX_site_id` (`site_id`);
ALTER TABLE `user_login` ADD KEY `IDX_ip` (`ip`);
ALTER TABLE `user_login` ADD KEY `IDX_date` (`date`);
ALTER TABLE `work_status_type` ADD KEY `IDX_short_description` (`short_description`);

INSERT INTO site VALUES (1, 'default_site', 0, 0, 0, NOW());
INSERT INTO version VALUES ('0.5.5');
UPDATE access_level SET access_level_id = -1 where short_description = 'Disabled';
