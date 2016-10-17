
#r949 9-25-6 BH
UPDATE system SET schema_version = 949;
CREATE TABLE `history` (
  `history_id` int(11) NOT NULL auto_increment,
  `data_item_type` int(11) default NULL,
  `data_item_id` int(11) default NULL,
  `the_field` varchar(64) default NULL,
  `previous_value` text,
  `new_value` text,
  `description` varchar(192) default NULL,
  `set_date` datetime default NULL,
  `entered_by` int(11) default NULL,
  `site_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`history_id`),
  KEY `IDX_DATA_TYPE` (`data_item_type`),
  KEY `IDX_DATA_ID` (`data_item_id`),
  KEY `IDX_DATA_ENTERED_BY` (`entered_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#r950 9-27-6 BH
UPDATE system SET schema_version = 950;
ALTER IGNORE TABLE `site` ADD COLUMN `unix_name` varchar(128) default NULL;
ALTER IGNORE TABLE `site` ADD COLUMN `client_id` int(11) default NULL;
INSERT INTO `site` VALUES (180,'CATS_ADMIN',0,0,0,'2005-06-01 00:00:00','catsadmin', null);
INSERT INTO `user` VALUES (1250, 180, 'cats@rootadmin', 0, 'cantlogin', 0, 0, 0, 0, 'Automated', 'CATS', 0);
INSERT INTO `client_foreign_settings` VALUES (NULL, 'AdminUser', null, 180, '2005-06-01 00:00:00');
INSERT INTO `client_foreign_settings` VALUES (NULL, 'UnixName', null, 180, '2005-06-01 00:00:00');
INSERT INTO `contact_foreign_settings` VALUES (NULL, 'IPAddress', null, 180, '2005-06-01 00:00:00');

#r951 10-3-6 BH
UPDATE system SET schema_version = 951;
CREATE TABLE `word_verification` (
  `word_verification_ID` int(11) NOT NULL auto_increment,
  `word` varchar(28) NOT NULL default '',
  PRIMARY KEY  (`word_verification_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#r952 10-6-6 BH
UPDATE system SET schema_version = 952;
ALTER IGNORE TABLE `user` ADD COLUMN `categories` varchar(192) default NULL;

#r953 10-6-6 BH
UPDATE system SET schema_version = 953;
ALTER IGNORE TABLE `attachment` ADD COLUMN `profile_image` int(1) default 0;

#r954 10-6-6 BH
UPDATE system SET schema_version = 954;
ALTER IGNORE TABLE `client` ADD COLUMN `default_client` int(1) default 0;

#r955 10-6-6 BH
UPDATE system SET schema_version = 955;
ALTER IGNORE TABLE `user` ADD COLUMN `session_cookie` varchar(48) default NULL;

#r956 10-11-6 BH
UPDATE system SET schema_version = 956;
INSERT INTO mailer_settings SELECT NULL, 'modeConfigurable', '0', site_id, 0 FROM site WHERE site.client_id != 0;

#r957 10-11-6 BH
UPDATE system SET schema_version = 957;
ALTER IGNORE TABLE `candidate_foreign` ADD COLUMN `site_id` int(11) default 0;
ALTER IGNORE TABLE `client_foreign` ADD COLUMN `site_id` int(11) default 0;
ALTER IGNORE TABLE `contact_foreign` ADD COLUMN `site_id` int(11) default 0;
UPDATE candidate_foreign, candidate SET candidate_foreign.site_id = candidate.site_id WHERE candidate_foreign.assoc_id = candidate.candidate_id;
UPDATE client_foreign, client SET client_foreign.site_id = client.site_id WHERE client_foreign.assoc_id = client.client_id;
UPDATE contact_foreign, contact SET contact_foreign.site_id = contact.site_id WHERE contact_foreign.assoc_id = contact.contact_id;


#r958 10-11-6 BH
UPDATE system SET schema_version = 958;
ALTER IGNORE TABLE `site` ADD COLUMN `is_trial` int(1) NOT NULL default '0';
ALTER IGNORE TABLE `site` ADD COLUMN `trial_expires` datetime NOT NULL default '1000-01-01 00:00:00';

#r959 10-13-6 BH
UPDATE system SET schema_version = 959;
ALTER IGNORE TABLE `site` ADD COLUMN `account_active` int(1) NOT NULL default '1';

#r960 10-18-6 BH
UPDATE system SET schema_version = 960;
ALTER TABLE `candidate_foreign` TYPE = MYISAM;
ALTER TABLE `candidate_foreign` CHANGE `field_name` `field_name` VARCHAR(255) CHARACTER SET utf8  NULL DEFAULT NULL;
ALTER TABLE `candidate_foreign` CHANGE `value` `value` TEXT CHARACTER SET utf8 NULL DEFAULT NULL;
ALTER TABLE `candidate_foreign` DEFAULT CHARACTER SET utf8;
ALTER TABLE `client_foreign` TYPE = MYISAM;
ALTER TABLE `client_foreign` CHANGE `field_name` `field_name` VARCHAR(255) CHARACTER SET utf8  NULL DEFAULT NULL;
ALTER TABLE `client_foreign` CHANGE `value` `value` TEXT CHARACTER SET utf8 NULL DEFAULT NULL;
ALTER TABLE `client_foreign` DEFAULT CHARACTER SET utf8;
ALTER TABLE `contact_foreign` TYPE = MYISAM;
ALTER TABLE `contact_foreign` CHANGE `field_name` `field_name` VARCHAR(255) CHARACTER SET utf8 NULL DEFAULT NULL;
ALTER TABLE `contact_foreign` CHANGE `value` `value` TEXT CHARACTER SET utf8 NULL DEFAULT NULL;
ALTER TABLE `contact_foreign` DEFAULT CHARACTER SET utf8;
ALTER IGNORE TABLE site ADD COLUMN `account_deleted` int(1) NOT NULL default '0';

#r961 10-19-6 BH
UPDATE system SET schema_version = 961;
ALTER TABLE `site` CHANGE `unix_name` `unix_name` varchar(128) CHARACTER SET utf8 default NULL;

#r962 10-19-6 BH
UPDATE system SET schema_version = 962;
ALTER TABLE `site` ADD COLUMN `reason_disabled` text CHARACTER SET utf8 default NULL;

#r963 10-19-6 BH
UPDATE system SET schema_version = 963;
INSERT INTO email_template VALUES (null, '%%FULLNAME,\r\n\r\nThanks for your interest in CATS!  You have just set up a CATS employer site (Trial) which can be accessed from:\r\n\r\nLogin page: <a href=\"%%LOGINPAGE\">%%LOGINPAGE</a>\r\nUsername: %%USERNAME\r\nPassword: %%PASSWORD\r\n\r\nIt is an empty database.\r\n\r\nAfter 30 days, your trial will expire.  At this time, you can continue to use our site by purchasing a site license.  Purchasing a site license also allows you to add more users to the system.\r\n\r\nMore information can be found at <a href=\"http://www.catsone.net/index.php?m=asp&a=purchaseinfo\">http://www.catsone.net/index.php?m=asp&a=purchaseinfo</a> or by visiting \'My Account\' through the CATS settings tab.\r\n\r\nIf you have any questions or suggestions, feel free to contact us through the forums at <a href=\"http://www.catsone.com/forum/\">http://www.catsone.com/forum/</a> or through E-Mail at <a href=\"http://catsone.com/?page_id=9\">http://catsone.com/?page_id=9</a>.\r\n\r\nHappy recruiting!\r\n- CATS Team', 1, 180, 'EMAIL_TEMPLATE_WELCOME_TO_CATS', 'Welcome to CATS (Sent to new SA)', null, 0);

#r964 10-19-6 BH
UPDATE system SET schema_version = 964;
UPDATE site SET account_active = 1 WHERE site_id = 180;

#r965 10-19-6 BH
UPDATE system SET schema_version = 965;
INSERT INTO `client_foreign_settings` VALUES (NULL, 'BillingNotes', null, 180, '2005-06-01 00:00:00');

#r1087 10-23-6 WB
UPDATE system SET schema_version = 1087;
ALTER TABLE `email_history` CHANGE `email_sent_id` `email_history_id` INT(11) NOT NULL AUTO_INCREMENT;

#r1088 10-25-6 BH
UPDATE system SET schema_version = 1088;
ALTER TABLE `user_login` ADD COLUMN `date_refreshed` datetime DEFAULT NULL;

#r1127 10-26-6 WB
UPDATE system SET schema_version = 1127;
ALTER TABLE `user_login` ADD INDEX `IDX_successful` (`successful`) ;
ALTER TABLE `email_history` ADD INDEX `IDX_site_id` (`site_id`) ;
ALTER TABLE `email_history` ADD INDEX `IDX_date` (`date`) ;
ALTER TABLE `email_history` ADD INDEX `IDX_user_id` (`user_id`) ;
ALTER TABLE `candidate_foreign` ADD INDEX `IDX_site_id` (`site_id`) ;
ALTER TABLE `client_foreign` ADD INDEX `IDX_site_id` (`site_id`) ;
ALTER TABLE `contact_foreign` ADD INDEX `IDX_site_id` (`site_id`) ;

#r1134 10-27-6 BH
UPDATE system SET schema_version = 1134;
ALTER TABLE `joborder` ADD COLUMN `department_id` INT(11) NOT NULL DEFAULT 0;

#r1141 11-6-6 WB
UPDATE system SET schema_version = 1141;
ALTER TABLE `user_login` ADD INDEX `IDX_date_refreshed` (`date_refreshed`) ;

#r1142 11-6-6 WB
UPDATE system SET schema_version = 1142;

#r1143 11-7-6 BH
UPDATE system SET schema_version = 1143;
CREATE TABLE `module_schema` (
  `module_schema_id` int(11) NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  `version` int(11) default NULL,
  PRIMARY KEY  (`module_schema_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#r1200 11-18-6 BH
UPDATE system SET schema_version = 1200;
ALTER TABLE `candidate_joborder` ADD INDEX `IDX_status_special` (`site_id`, `status`) ;

