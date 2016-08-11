-- MySQL dump 10.16  Distrib 10.1.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: cats
-- ------------------------------------------------------
-- Server version	10.1.14-MariaDB-1~jessie

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `access_level`
--

DROP TABLE IF EXISTS `access_level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `access_level` (
  `access_level_id` int(11) NOT NULL DEFAULT '0',
  `short_description` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `long_description` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`access_level_id`),
  KEY `IDX_access_level` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_level`
--

LOCK TABLES `access_level` WRITE;
/*!40000 ALTER TABLE `access_level` DISABLE KEYS */;
INSERT INTO `access_level` VALUES (0,'Account Disabled','Disabled - The lowest access level. User cannot log in.'),(100,'Read Only','Read Only - A standard user that can view data on the system in a read-only mode.'),(200,'Add / Edit','Edit - All lower access, plus the ability to edit information on the system.'),(300,'Add / Edit / Delete','Delete - All lower access, plus the ability to delete information on the system.'),(400,'Site Administrator','Site Administrator - All lower access, plus the ability to add, edit, and remove site users, as well as the ability to edit site settings.'),(500,'Root','Root Administrator - All lower access, plus the ability to add, edit, and remove sites, as well as the ability to assign Site Administrator status to a user.');
/*!40000 ALTER TABLE `access_level` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity`
--

DROP TABLE IF EXISTS `activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity` (
  `activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `data_item_id` int(11) NOT NULL DEFAULT '0',
  `data_item_type` int(11) NOT NULL DEFAULT '0',
  `joborder_id` int(11) DEFAULT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `entered_by` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `type` int(11) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8_unicode_ci,
  `date_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity`
--

LOCK TABLES `activity` WRITE;
/*!40000 ALTER TABLE `activity` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_type`
--

DROP TABLE IF EXISTS `activity_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_type` (
  `activity_type_id` int(11) NOT NULL DEFAULT '0',
  `short_description` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`activity_type_id`),
  KEY `IDX_activity_type1` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_type`
--

LOCK TABLES `activity_type` WRITE;
/*!40000 ALTER TABLE `activity_type` DISABLE KEYS */;
INSERT INTO `activity_type` VALUES (100,'Call'),(200,'Email'),(300,'Meeting'),(400,'Other'),(500,'Call (Talked)'),(600,'Call (LVM)'),(700,'Call (Missed)');
/*!40000 ALTER TABLE `activity_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attachment`
--

DROP TABLE IF EXISTS `attachment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attachment` (
  `attachment_id` int(11) NOT NULL AUTO_INCREMENT,
  `data_item_id` int(11) NOT NULL DEFAULT '0',
  `data_item_type` int(11) NOT NULL DEFAULT '0',
  `site_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `original_filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `stored_filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `content_type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resume` int(1) NOT NULL DEFAULT '0',
  `text` text COLLATE utf8_unicode_ci,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attachment`
--

LOCK TABLES `attachment` WRITE;
/*!40000 ALTER TABLE `attachment` DISABLE KEYS */;
/*!40000 ALTER TABLE `attachment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_event`
--

DROP TABLE IF EXISTS `calendar_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_event` (
  `calendar_event_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `title` text COLLATE utf8_unicode_ci NOT NULL,
  `all_day` int(1) NOT NULL DEFAULT '0',
  `data_item_id` int(11) NOT NULL DEFAULT '-1',
  `data_item_type` int(11) NOT NULL DEFAULT '-1',
  `entered_by` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_event`
--

LOCK TABLES `calendar_event` WRITE;
/*!40000 ALTER TABLE `calendar_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `calendar_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_event_type`
--

DROP TABLE IF EXISTS `calendar_event_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_event_type` (
  `calendar_event_type_id` int(11) NOT NULL DEFAULT '0',
  `short_description` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `icon_image` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`calendar_event_type_id`),
  KEY `IDX_short_description` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_event_type`
--

LOCK TABLES `calendar_event_type` WRITE;
/*!40000 ALTER TABLE `calendar_event_type` DISABLE KEYS */;
INSERT INTO `calendar_event_type` VALUES (100,'Call','images/phone.gif'),(200,'Email','images/email.gif'),(300,'Meeting','images/meeting.gif'),(400,'Interview','images/interview.gif'),(500,'Personal','images/personal.gif'),(600,'Other','');
/*!40000 ALTER TABLE `calendar_event_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `candidate`
--

DROP TABLE IF EXISTS `candidate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
) ENGINE=MyISAM AUTO_INCREMENT=7211 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidate`
--

LOCK TABLES `candidate` WRITE;
/*!40000 ALTER TABLE `candidate` DISABLE KEYS */;
/*!40000 ALTER TABLE `candidate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `candidate_joborder`
--

DROP TABLE IF EXISTS `candidate_joborder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidate_joborder` (
  `candidate_joborder_id` int(11) NOT NULL AUTO_INCREMENT,
  `candidate_id` int(11) NOT NULL DEFAULT '0',
  `joborder_id` int(11) NOT NULL DEFAULT '0',
  `site_id` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `date_submitted` datetime DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidate_joborder`
--

LOCK TABLES `candidate_joborder` WRITE;
/*!40000 ALTER TABLE `candidate_joborder` DISABLE KEYS */;
/*!40000 ALTER TABLE `candidate_joborder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `candidate_joborder_status`
--

DROP TABLE IF EXISTS `candidate_joborder_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidate_joborder_status` (
  `candidate_joborder_status_id` int(11) NOT NULL DEFAULT '0',
  `short_description` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `can_be_scheduled` int(1) NOT NULL DEFAULT '0',
  `triggers_email` int(1) NOT NULL DEFAULT '1',
  `is_enabled` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`candidate_joborder_status_id`),
  KEY `IDX_short_description` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidate_joborder_status`
--

LOCK TABLES `candidate_joborder_status` WRITE;
/*!40000 ALTER TABLE `candidate_joborder_status` DISABLE KEYS */;
INSERT INTO `candidate_joborder_status` VALUES (100,'No Contact',0,0,1),(200,'Contacted',0,0,1),(300,'Qualifying',0,1,1),(400,'Submitted',0,1,1),(500,'Interviewing',0,1,1),(600,'Offered',0,1,1),(700,'Client Declined',0,0,1),(800,'Placed',0,1,1),(0,'No Status',0,0,1),(650,'Not in Consideration',0,0,1),(250,'Candidate Responded',0,0,1);
/*!40000 ALTER TABLE `candidate_joborder_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `candidate_joborder_status_history`
--

DROP TABLE IF EXISTS `candidate_joborder_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidate_joborder_status_history` (
  `candidate_joborder_status_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `candidate_id` int(11) NOT NULL DEFAULT '0',
  `joborder_id` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidate_joborder_status_history`
--

LOCK TABLES `candidate_joborder_status_history` WRITE;
/*!40000 ALTER TABLE `candidate_joborder_status_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `candidate_joborder_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `candidate_jobordrer_status_type`
--

DROP TABLE IF EXISTS `candidate_jobordrer_status_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidate_jobordrer_status_type` (
  `candidate_status_type_id` int(11) NOT NULL DEFAULT '0',
  `short_description` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `can_be_scheduled` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`candidate_status_type_id`),
  KEY `IDX_short_description` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidate_jobordrer_status_type`
--

LOCK TABLES `candidate_jobordrer_status_type` WRITE;
/*!40000 ALTER TABLE `candidate_jobordrer_status_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `candidate_jobordrer_status_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `candidate_source`
--

DROP TABLE IF EXISTS `candidate_source`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidate_source` (
  `source_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `site_id` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`source_id`),
  KEY `siteID` (`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidate_source`
--

LOCK TABLES `candidate_source` WRITE;
/*!40000 ALTER TABLE `candidate_source` DISABLE KEYS */;
/*!40000 ALTER TABLE `candidate_source` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `candidate_tag`
--

DROP TABLE IF EXISTS `candidate_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidate_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned DEFAULT NULL,
  `candidate_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidate_tag`
--

LOCK TABLES `candidate_tag` WRITE;
/*!40000 ALTER TABLE `candidate_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `candidate_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `career_portal_questionnaire`
--

DROP TABLE IF EXISTS `career_portal_questionnaire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `career_portal_questionnaire` (
  `career_portal_questionnaire_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `site_id` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`career_portal_questionnaire_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `career_portal_questionnaire`
--

LOCK TABLES `career_portal_questionnaire` WRITE;
/*!40000 ALTER TABLE `career_portal_questionnaire` DISABLE KEYS */;
/*!40000 ALTER TABLE `career_portal_questionnaire` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `career_portal_questionnaire_answer`
--

DROP TABLE IF EXISTS `career_portal_questionnaire_answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `career_portal_questionnaire_answer`
--

LOCK TABLES `career_portal_questionnaire_answer` WRITE;
/*!40000 ALTER TABLE `career_portal_questionnaire_answer` DISABLE KEYS */;
/*!40000 ALTER TABLE `career_portal_questionnaire_answer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `career_portal_questionnaire_history`
--

DROP TABLE IF EXISTS `career_portal_questionnaire_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `career_portal_questionnaire_history` (
  `career_portal_questionnaire_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `candidate_id` int(11) NOT NULL DEFAULT '0',
  `question` varchar(255) NOT NULL DEFAULT '',
  `answer` varchar(255) NOT NULL DEFAULT '',
  `questionnaire_title` varchar(255) NOT NULL DEFAULT '',
  `questionnaire_description` varchar(255) NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`career_portal_questionnaire_history_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `career_portal_questionnaire_history`
--

LOCK TABLES `career_portal_questionnaire_history` WRITE;
/*!40000 ALTER TABLE `career_portal_questionnaire_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `career_portal_questionnaire_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `career_portal_questionnaire_question`
--

DROP TABLE IF EXISTS `career_portal_questionnaire_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `career_portal_questionnaire_question`
--

LOCK TABLES `career_portal_questionnaire_question` WRITE;
/*!40000 ALTER TABLE `career_portal_questionnaire_question` DISABLE KEYS */;
/*!40000 ALTER TABLE `career_portal_questionnaire_question` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `career_portal_template`
--

DROP TABLE IF EXISTS `career_portal_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `career_portal_template` (
  `career_portal_template_id` int(11) NOT NULL AUTO_INCREMENT,
  `career_portal_name` varchar(255) DEFAULT NULL,
  `setting` varchar(128) NOT NULL DEFAULT '',
  `value` text,
  PRIMARY KEY (`career_portal_template_id`)
) ENGINE=MyISAM AUTO_INCREMENT=78 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `career_portal_template`
--

LOCK TABLES `career_portal_template` WRITE;
/*!40000 ALTER TABLE `career_portal_template` DISABLE KEYS */;
INSERT INTO `career_portal_template` VALUES (56,'Blank Page','Left',''),(57,'Blank Page','Footer',''),(58,'Blank Page','Header',''),(59,'Blank Page','Content - Main',''),(60,'Blank Page','CSS',''),(61,'Blank Page','Content - Search Results',''),(62,'Blank Page','Content - Questionnaire',''),(63,'Blank Page','Content - Job Details',''),(64,'Blank Page','Content - Thanks for your Submission',''),(65,'Blank Page','Content - Apply for Position',''),(66,'CATS 2.0','Left',''),(67,'CATS 2.0','Footer','</div>'),(68,'CATS 2.0','Header','<div id=\"container\">\r\n	<div id=\"logo\"><img src=\"images/careers_cats.gif\" alt=\"IMAGE: CATS Applicant Tracking System Careers Page\" /></div>\r\n    <div id=\"actions\">\r\n    	<h2>Shortcuts:</h2>\r\n        <a href=\"index.php\" onmouseover=\"buttonMouseOver(\'returnToMain\',true);\" onmouseout=\"buttonMouseOver(\'returnToMain\',false);\"><img src=\"images/careers_return.gif\" id=\"returnToMain\" alt=\"IMAGE: Return to Main\" /></a>\r\n<a href=\"<rssURL>\" onmouseover=\"buttonMouseOver(\'rssFeed\',true);\" onmouseout=\"buttonMouseOver(\'rssFeed\',false);\"><img src=\"images/careers_rss.gif\" id=\"rssFeed\" alt=\"IMAGE: RSS Feed\" /></a>\r\n        <a href=\"index.php?m=careers&p=showAll\" onmouseover=\"buttonMouseOver(\'showAllJobs\',true);\" onmouseout=\"buttonMouseOver(\'showAllJobs\',false);\"><img src=\"images/careers_show.gif\" id=\"showAllJobs\" alt=\"IMAGE: Show All Jobs\" /></a>\r\n    </div>'),(69,'CATS 2.0','Content - Main','<div id=\"careerContent\">\r\n        <registeredCandidate>\r\n        <h1>Available Openings at <siteName></h1>\r\n        <div id=\"descriptive\">\r\n               <p>Change your life today by becoming an integral part of our winning team.</p>\r\n               <p>If you are interested, we invite you to view the <a href=\"index.php?m=careers&p=showAll\">current opening positions</a> at our company.</p><br /><br /><registeredLoginTitle><h1 style=\"padding:0;margin:0;border:0\">Have you applied with us before?</h1></registeredLoginTitle><registeredLogin>\r\n        </div>\r\n        <div id=\"detailsTools\">\r\n        	<h2>Perform an action:</h2>\r\n        	<ul>\r\n                    <li><a href=\"\">Visit our website</a></li>\r\n                </ul>\r\n        </div>\r\n</div>'),(70,'CATS 2.0','CSS','table.sortable\r\n{\r\ntext-align:left;\r\nempty-cells: show;\r\nwidth: 940px;\r\n}\r\ntd\r\n{\r\npadding:5px;\r\n}\r\ntr.rowHeading\r\n{\r\n background: #e0e0e0; border: 1px solid #cccccc; border-left: none; border-right: none;\r\n}\r\ntr.oddTableRow\r\n{\r\nbackground: #ebebeb; \r\n}\r\ntr.evenTableRow\r\n{\r\n background: #ffffff; \r\n}\r\na.sortheader:hover,\r\na.sortheader:link,\r\na.sortheader:visited\r\n{\r\ncolor:#000;\r\n}\r\n\r\nbody, html { margin: 0; padding: 0; background: #ffffff; font: normal 12px/14px Arial, Helvetica, sans-serif; color: #000000; }\r\n#container { margin: 0 auto; padding: 0; width: 940px; height: auto; }\r\n#logo { float: left; margin: 0; }\r\n	#logo img { width: 424px; height: 103px; }\r\n#actions { float: right; margin: 0; width: 310px; height: 100px; background: #efefef; border: 1px solid #cccccc; }\r\n	#actions img { float: left; margin: 2px 6px 2px 15px; width: 130px; height: 25px; }\r\n#footer { clear: both; margin: 20px auto 0 auto; width: 150px; }\r\n	#footer img { width: 137px; height: 38px; }\r\n\r\na:link, a:active { color: #1763b9; }\r\na:hover { color: #c75a01; }\r\na:visited { color: #333333; }\r\nimg { border: none; }\r\n\r\nh1 { margin: 0 0 10px 0; font: bold 18px Arial, Helvetica, sans-serif; }\r\nh2 { margin: 8px 0 8px 15px; font: bold 14px Arial, Helvetica, sans-serif; }\r\nh3 { margin: 0; font: bold 14px Arial, Helvetica, sans-serif; }\r\np { font: normal 12px Arial, Helvetica, sans-serif; }\r\np.instructions { margin: 0 0 0 10px; font: italic 12px Arial, Helvetica, sans-serif; color: #666666; }\r\n\r\n\r\n/* CONTENTS ON PAGE SPECS */\r\n#careerContent { clear: both; padding: 15px 0 0 0; }\r\n\r\n	\r\n/* DISPLAY JOB DETAILS */\r\n#detailsTable { width: 400px; }\r\n	#detailsTable td.detailsHeader { width: 30%; }\r\ndiv#descriptive { float: left; width: 585px; }\r\ndiv#detailsTools { float: right; padding: 0 0 8px 0; width: 280px; background: #ffffff; border: 1px solid #cccccc; }\r\n	div#detailsTools img { margin: 2px 6px 5px 15px;  }\r\n\r\n/* DISPLAY APPLICATION FORM */\r\ndiv.applyBoxLeft, div.applyBoxRight { width: 450px; height: 470px; background: #f9f9f9; border: 1px solid #cccccc; border-top: none; }\r\ndiv.applyBoxLeft { float: left; margin: 0 10px 0 0; }\r\ndiv.applyBoxRight { float: right; margin: 0 0 0 10px; }\r\n	div.applyBoxLeft div, div.applyBoxRight div { margin: 0 0 5px 0; padding: 3px 10px; background: #efefef; border-top: 1px solid #cccccc; border-bottom: 1px solid #cccccc; }\r\n	div.applyBoxLeft table, div.applyBoxRight table { margin: 0 auto; width: 420px; }\r\n	div.applyBoxLeft table td, div.applyBoxRight table td { padding: 3px; vertical-align: top; }\r\n		td.label { text-align: right; width: 110px; }\r\n        form#applyToJobForm {  }\r\n	form#applyToJobForm label { font-weight: bold; }\r\n	form#applyToJobForm input.inputBoxName, form#applyToJobForm input.inputBoxNormal { width: 285px; height: 15px; }\r\n        form#applyToJobForm input.submitButton { width: 197px; height: 27px; background: url(\'images/careers_submit.gif\') no-repeat; }\r\n\r\n        form#applyToJobForm input.submitButtonDown { width: 197px; height: 27px; background: url(\'images/careers_submit-o.gif\') no-repeat; }\r\n	form#applyToJobForm textarea { margin: 8px 0 0 0; width: 410px; height: 170px; }\r\n	form#applyToJobForm textarea.inputBoxArea{ width: 285px; height: 70px; }\r\n\r\n'),(71,'CATS 2.0','Content - Search Results','<div id=\"careerContent\">\r\n        <registeredCandidate>\r\n        <h1>Current Available Openings, Recently Posted Jobs: <numberOfSearchResults></h1>\r\n<searchResultsTable>\r\n    </div>'),(72,'CATS 2.0','Content - Questionnaire','<div id=\"careerContent\">\r\n<questionnaire>\r\n<br /><br />\r\n<div style=\"text-align: right;\">\r\n<submit value=\"Continue\">\r\n</div>\r\n</div>'),(73,'CATS 2.0','Content - Job Details','<div id=\"careerContent\">\r\n        <registeredCandidate>\r\n        <h1>Position Details: <title></h1>\r\n        <table id=\"detailsTable\">\r\n            <tr>\r\n                <td class=\"detailsHeader\"><strong>Location:</strong></td>\r\n                <td><city>, <state></td>\r\n			</tr>\r\n			<tr>\r\n                <td class=\"detailsHeader\"><strong>Openings:</strong></td>\r\n                <td><openings></td>\r\n			</tr>\r\n            <tr>\r\n                <td class=\"detailsHeader\"><strong>Salary Range:</strong></td>\r\n                <td><salary></td>\r\n            </tr>\r\n        </table>\r\n        <div id=\"descriptive\">\r\n            <p><strong>Description:</strong></p>\r\n            <description>\r\n		</div>\r\n        <div id=\"detailsTools\">\r\n        	<h2>Perform an action:</h2>\r\n        	<a-applyToJob onmouseover=\"buttonMouseOver(\'applyToPosition\',true);\" onmouseout=\"buttonMouseOver(\'applyToPosition\',false);\"><img src=\"images/careers_apply.gif\" id=\"applyToPosition\" alt=\"IMAGE: Apply to Position\" /></a>\r\n        </div>\r\n    </div>'),(74,'CATS 2.0','Content - Thanks for your Submission','<div id=\"careerContent\">\r\n            <h1>Application Submitted For: <title></h1>\r\n            <div id=\"descriptive\">\r\n                <p>Please check your email inbox &#8212; You should receive an email confirmation of your application.</p>\r\n                <p>Thank you for submitting your application to us. We will review it shortly and make contact with you soon.</p>\r\n                </div>\r\n			<div id=\"detailsTools\">\r\n                <h2>Perform an action:</h2>\r\n                <ul>\r\n                	<li><a href=\"\">Visit our website</a></li>\r\n		</ul>\r\n        	</div>\r\n    </div>'),(75,'CATS 2.0','Content - Apply for Position','<div id=\"careerContent\">\r\n        <h1>Applying to: <title></h1>\r\n        <div class=\"applyBoxLeft\">\r\n            <div><h3>1. Import Resume (or CV) and Populate Fields</h3></div>\r\n            <table>\r\n                <tr>\r\n                    <td>\r\n                      \r\n                    <input-resumeUploadPreview>\r\n                    </td>\r\n                </tr>\r\n            </table>\r\n            <br />\r\n\r\n            <div><h3>2. Tell us about yourself</h3></div>\r\n            <p class=\"instructions\">All fields marked with asterisk (*) are required.</p>\r\n            <table>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"firstNameLabel\" for=\"firstName\">*First Name:</label></td>\r\n                    <td><input-firstName></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"lastNameLabel\" for=\"lastName\">*Last Name:</label></td>\r\n                    <td><input-lastName></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"emailLabel\" for=\"email\">*Email Adddress:</label></td>\r\n                    <td><input-email></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"emailConfirmLabel\" for=\"emailconfirm\">*Confirm Email:</label></td>\r\n                    <td><input-emailconfirm></td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n       \r\n        <div class=\"applyBoxRight\">\r\n            <div><h3>3. How may we contact you?</h3></div>\r\n            <table>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"homePhoneLabel\" for=\"homePhone\">Home Phone:</label></td>\r\n                    <td><input-phone-home></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"mobilePhoneLabel\" for=\"mobilePhone\">Mobile Phone:</label></td>\r\n                    <td><input-phone-cell></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"workPhoneLabel\" for=\"workPhone\">Work Phone:</label></td>\r\n                    <td><input-phone></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"bestTimeLabel\" for=\"bestTime\">*Best time to call:</label></td>\r\n                    <td><input-best-time-to-call></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"mailingAddressLabel\" for=\"mailingAddress\">Mailing Address:</label></td>\r\n                    <td><input-address></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"cityProvinceLabel\" for=\"cityProvince\">*City/Province:</label></td>\r\n                    <td><input-city></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"stateCountryLabel\" for=\"stateCountry\">*State/Country:</label></td>\r\n                    <td><input-state></td>\r\n                </tr>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"zipPostalLabel\" for=\"zipPostal\">*Zip/Postal Code:</label></td>\r\n                    <td><input-zip></td>\r\n                </tr>\r\n            </table>\r\n            <br />\r\n            <div><h3>4. Additional Information</h3></div>\r\n            <table>\r\n                <tr>\r\n                    <td class=\"label\"><label id=\"keySkillsLabel\" for=\"keySkills\">*Key Skills:</label></td>\r\n                    <td><input-keySkills></td>\r\n                </tr>\r\n                <tr>\r\n                    <td>&nbsp;</td>\r\n                    <td><img src=\"images/careers_submit.gif\" onmouseover=\"buttonMouseOver(\'submitApplicationNow\',true)\" onmouseout=\"buttonMouseOver(\'submitApplicationNow\',false)\" style=\"cursor: pointer;\" id=\"submitApplicationNow\" alt=\"Submit Application Now\" onclick=\"if (applyValidate()) { document.applyToJobForm.submit(); }\" /></td>\r\n                </tr>\r\n            </table>\r\n               </div>\r\n    </div>'),(76,'CATS 2.0','Content - Candidate Registration','<div id=\"careerContent\">\r\n    <h1><applyContent>Applying to <title></applyContent></h1>\r\n    <center>\r\n    <table cellpadding=\"0\" cellspacing=\"0\">\r\n        <tr>\r\n            <td><label id=\"emailLabel\" for=\"email\"><h2>Enter your e-mail address:</h2></label></td>\r\n            <td><input-email></td>\r\n        </tr>\r\n        <tr>\r\n            <td align=\"right\" valign=\"top\"><input-new></td>\r\n            <td style=\"line-height: 18px;\">\r\n                <applyContent>\r\n                <strong>I have not registered on this website.</strong><br />\r\n                (I haven\'t applied to any jobs online)\r\n                </applyContent>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <td align=\"right\" valign=\"top\"><input-registered></td>\r\n            <td style=\"line-height: 20px;\">\r\n                <strong>I have registered before</strong><br />\r\n                and my last name is:<br />\r\n                <input-lastName><br />\r\n                and my zip code is:<br />\r\n                <input-zip><br /><br />\r\n                <input-rememberMe> Remember my information for future visits<br /><br />\r\n                <input-submit><br /><br />\r\n            </td>\r\n        </tr>\r\n    </table>\r\n    </center>\r\n</div>\r\n'),(77,'CATS 2.0','Content - Candidate Profile','<div id=\"careerContent\">    <h1 style=\"padding: 0; margin: 0; border: 0;\">My Profile</h1><h3 style=\"font-weight: normal;\">Any changes you make to your profile will be updated on our website for all    past and future jobs you apply for.</h3>    <br />    <div class=\"applyBoxLeft\">        <div><h3>1. Tell us about yourself</h3></div>        <p class=\"instructions\">All fields marked with asterisk (*) are required.</p>        <table>            <tr>                <td class=\"label\"><label id=\"firstNameLabel\" for=\"firstName\">*First Name:</label></td>                <td><input-firstName></td>            </tr>            <tr>                <td class=\"label\"><label id=\"lastNameLabel\" for=\"lastName\">*Last Name:</label></td>                <td><input-lastName></td>            </tr>            <tr>                <td class=\"label\"><label id=\"emailLabel\" for=\"email\">*Email Adddress:</label></td>                <td><input-email1></td>            </tr>            <tr>                <td colspan=\"2\">                    <input-resume>                </td>            </tr>        </table>    </div>    <div class=\"applyBoxRight\">        <div><h3>2. How may we contact you?</h3></div>        <table>            <tr>                <td class=\"label\"><label id=\"homePhoneLabel\" for=\"homePhone\">Home Phone:</label></td>                <td><input-phoneHome></td>            </tr>            <tr>                <td class=\"label\"><label id=\"mobilePhoneLabel\" for=\"mobilePhone\">Mobile Phone:</label></td>                <td><input-phoneCell></td>            </tr>            <tr>                <td class=\"label\"><label id=\"workPhoneLabel\" for=\"workPhone\">Work Phone:</label></td>                <td><input-phoneWork></td>            </tr>            <tr>                <td class=\"label\"><label id=\"bestTimeLabel\" for=\"bestTime\">*Best time to call:</label></td>                <td><input-bestTimeToCall></td>            </tr>            <tr>                <td class=\"label\"><label id=\"mailingAddressLabel\" for=\"mailingAddress\">Mailing Address:</label></td>                <td><input-address></td>            </tr>            <tr>                <td class=\"label\"><label id=\"cityProvinceLabel\" for=\"cityProvince\">*City/Province:</label></td>                <td><input-city></td>            </tr>            <tr>                <td class=\"label\"><label id=\"stateCountryLabel\" for=\"stateCountry\">*State/Country:</label></td>                <td><input-state></td>            </tr>            <tr>                <td class=\"label\"><label id=\"zipPostalLabel\" for=\"zipPostal\">*Zip/Postal Code:</label></td>                <td><input-zip></td>            </tr>        </table>        <br />        <div><h3>3. Additional Information</h3></div>        <table>            <tr>                <td class=\"label\"><label id=\"keySkillsLabel\" for=\"keySkills\">*Key Skills:</label></td>                <td><input-keySkills></td>            </tr>            <tr>                <td>&nbsp;</td>                <td style=\"padding-top: 40px;\"><input-submit></td>            </tr>        </table>    </div></div>');
/*!40000 ALTER TABLE `career_portal_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `career_portal_template_site`
--

DROP TABLE IF EXISTS `career_portal_template_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `career_portal_template_site` (
  `career_portal_template_id` int(11) NOT NULL AUTO_INCREMENT,
  `career_portal_name` varchar(255) DEFAULT NULL,
  `site_id` int(11) NOT NULL,
  `setting` varchar(128) NOT NULL DEFAULT '',
  `value` text,
  PRIMARY KEY (`career_portal_template_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `career_portal_template_site`
--

LOCK TABLES `career_portal_template_site` WRITE;
/*!40000 ALTER TABLE `career_portal_template_site` DISABLE KEYS */;
/*!40000 ALTER TABLE `career_portal_template_site` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company`
--

DROP TABLE IF EXISTS `company`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company`
--

LOCK TABLES `company` WRITE;
/*!40000 ALTER TABLE `company` DISABLE KEYS */;
/*!40000 ALTER TABLE `company` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_department`
--

DROP TABLE IF EXISTS `company_department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_department` (
  `company_department_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`company_department_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_department`
--

LOCK TABLES `company_department` WRITE;
/*!40000 ALTER TABLE `company_department` DISABLE KEYS */;
/*!40000 ALTER TABLE `company_department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact`
--

DROP TABLE IF EXISTS `contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact`
--

LOCK TABLES `contact` WRITE;
/*!40000 ALTER TABLE `contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_item_type`
--

DROP TABLE IF EXISTS `data_item_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_item_type` (
  `data_item_type_id` int(11) NOT NULL DEFAULT '0',
  `short_description` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`data_item_type_id`),
  KEY `IDX_short_description` (`short_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_item_type`
--

LOCK TABLES `data_item_type` WRITE;
/*!40000 ALTER TABLE `data_item_type` DISABLE KEYS */;
INSERT INTO `data_item_type` VALUES (100,'Candidate'),(200,'Company'),(300,'Contact'),(400,'Job Order');
/*!40000 ALTER TABLE `data_item_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eeo_ethnic_type`
--

DROP TABLE IF EXISTS `eeo_ethnic_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eeo_ethnic_type` (
  `eeo_ethnic_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`eeo_ethnic_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eeo_ethnic_type`
--

LOCK TABLES `eeo_ethnic_type` WRITE;
/*!40000 ALTER TABLE `eeo_ethnic_type` DISABLE KEYS */;
INSERT INTO `eeo_ethnic_type` VALUES (1,'American Indian'),(2,'Asian or Pacific Islander'),(3,'Hispanic or Latino'),(4,'Non-Hispanic Black'),(5,'Non-Hispanic White');
/*!40000 ALTER TABLE `eeo_ethnic_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eeo_veteran_type`
--

DROP TABLE IF EXISTS `eeo_veteran_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eeo_veteran_type` (
  `eeo_veteran_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`eeo_veteran_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eeo_veteran_type`
--

LOCK TABLES `eeo_veteran_type` WRITE;
/*!40000 ALTER TABLE `eeo_veteran_type` DISABLE KEYS */;
INSERT INTO `eeo_veteran_type` VALUES (1,'No Veteran Status'),(2,'Eligible Veteran'),(3,'Disabled Veteran'),(4,'Eligible and Disabled');
/*!40000 ALTER TABLE `eeo_veteran_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_history`
--

DROP TABLE IF EXISTS `email_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_history`
--

LOCK TABLES `email_history` WRITE;
/*!40000 ALTER TABLE `email_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_template`
--

DROP TABLE IF EXISTS `email_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_template`
--

LOCK TABLES `email_template` WRITE;
/*!40000 ALTER TABLE `email_template` DISABLE KEYS */;
INSERT INTO `email_template` VALUES (20,'* Auto generated message. Please DO NOT reply *\r\n%DATETIME%\r\n\r\nDear %CANDFULLNAME%,\r\n\r\nThis E-Mail is a notification that your status in our database has been changed for the position %JBODTITLE% (%JBODCLIENT%).\r\n\r\nYour previous status was <B>%CANDPREVSTATUS%</B>.\r\nYour new status is <B>%CANDSTATUS%</B>.\r\n\r\nTake care,\r\n%USERFULLNAME%\r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_STATUSCHANGE','Status Changed (Sent to Candidate)','%CANDSTATUS%%CANDOWNER%%CANDFIRSTNAME%%CANDFULLNAME%%CANDPREVSTATUS%%JBODCLIENT%%JBODTITLE%',0),(28,'%DATETIME%\r\n\r\nDear %CANDOWNER%,\r\n\r\nThis E-Mail is a notification that a Candidate has been assigned to you.\r\n\r\nCandidate Name: %CANDFULLNAME%\r\nCandidate URL: %CANDCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_OWNERSHIPASSIGNCANDIDATE','Candidate Assigned (Sent to Assigned Recruiter)','%CANDOWNER%%CANDFIRSTNAME%%CANDFULLNAME%%CANDCATSURL%',0),(27,'%DATETIME%\r\n\r\nDear %JBODOWNER%,\r\n\r\nThis E-Mail is a notification that a Job Order has been assigned to you.\r\n\r\nJob Order Title: %JBODTITLE%\r\nJob Order Client: %JBODCLIENT%\r\nJob Order ID: %JBODID%\r\nJob Order URL: %JBODCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_OWNERSHIPASSIGNJOBORDER','Job Order Assigned (Sent to Assigned Recruiter)','%JBODOWNER%%JBODTITLE%%JBODCLIENT%%JBODCATSURL%%JBODID%',0),(26,'%DATETIME%\r\n\r\nDear %CONTOWNER%,\r\n\r\nThis E-Mail is a notification that a Contact has been assigned to you.\r\n\r\nContact Name: %CONTFULLNAME%\r\nContact Client: %CONTCLIENTNAME%\r\nContact URL: %CONTCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_OWNERSHIPASSIGNCONTACT','Contact Assigned (Sent to Assigned Recruiter)','%CONTOWNER%%CONTFIRSTNAME%%CONTFULLNAME%%CONTCLIENTNAME%%CONTCATSURL%',0),(25,'%DATETIME%\r\n\r\nDear %CLNTOWNER%,\r\n\r\nThis E-Mail is a notification that a Client has been assigned to you.\r\n\r\nClient Name: %CLNTNAME%\r\nClient URL %CLNTCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_OWNERSHIPASSIGNCLIENT','Client Assigned (Sent to Assigned Recruiter)','%CLNTOWNER%%CLNTNAME%%CLNTCATSURL%',0),(30,'* This is an auto-generated message. Please do not reply. *\r\n%DATETIME%\r\n\r\nDear %CANDFULLNAME%,\r\n\r\nThank you for applying to the %JBODTITLE% position with our online career portal! Your application has been entered into our system and someone will review it shortly.\r\n\r\n--\r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_CANDIDATEAPPLY','Candidate Application Received (Sent to Candidate using Career Portal)','%CANDFIRSTNAME%%CANDFULLNAME%%JBODCLIENT%%JBODTITLE%%JBODOWNER%',0),(31,'%DATETIME%\r\n\r\nDear %JBODOWNER%,\r\n\r\nThis e-mail is a notification that a candidate has applied to your job order through the online candidate portal.\r\n\r\nJob Order: %JBODTITLE%\r\nCandidate Name: %CANDFULLNAME%\r\nCandidate URL: %CANDCATSURL%\r\nJob Order URL: %JBODCATSURL%\r\n\r\n--\r\nCATS\r\n%SITENAME%',1,1,'EMAIL_TEMPLATE_CANDIDATEPORTALNEW','Candidate Application Received (Sent to Owner of Job Order from Career Portal)','%CANDFIRSTNAME%%CANDFULLNAME%%JBODOWNER%%JBODTITLE%%JBODCLIENT%%JBODCATSURL%%JBODID%%CANDCATSURL%',0);
/*!40000 ALTER TABLE `email_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `extension_statistics`
--

DROP TABLE IF EXISTS `extension_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `extension_statistics` (
  `extension_statistics_id` int(11) NOT NULL AUTO_INCREMENT,
  `extension` varchar(128) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `action` varchar(128) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `user` varchar(128) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `date` date DEFAULT NULL,
  PRIMARY KEY (`extension_statistics_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `extension_statistics`
--

LOCK TABLES `extension_statistics` WRITE;
/*!40000 ALTER TABLE `extension_statistics` DISABLE KEYS */;
/*!40000 ALTER TABLE `extension_statistics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `extra_field`
--

DROP TABLE IF EXISTS `extra_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=MyISAM AUTO_INCREMENT=21502 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `extra_field`
--

LOCK TABLES `extra_field` WRITE;
/*!40000 ALTER TABLE `extra_field` DISABLE KEYS */;
/*!40000 ALTER TABLE `extra_field` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `extra_field_settings`
--

DROP TABLE IF EXISTS `extra_field_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `extra_field_settings`
--

LOCK TABLES `extra_field_settings` WRITE;
/*!40000 ALTER TABLE `extra_field_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `extra_field_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `reply_to_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `reply_to_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `feedback` text COLLATE utf8_unicode_ci NOT NULL,
  `archived` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`feedback_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback`
--

LOCK TABLES `feedback` WRITE;
/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `history`
--

DROP TABLE IF EXISTS `history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `history`
--

LOCK TABLES `history` WRITE;
/*!40000 ALTER TABLE `history` DISABLE KEYS */;
/*!40000 ALTER TABLE `history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `http_log`
--

DROP TABLE IF EXISTS `http_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `date` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `http_log`
--

LOCK TABLES `http_log` WRITE;
/*!40000 ALTER TABLE `http_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `http_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `http_log_types`
--

DROP TABLE IF EXISTS `http_log_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `http_log_types` (
  `log_type_id` int(11) NOT NULL,
  `name` varchar(16) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `default_log_type` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0',
  PRIMARY KEY (`log_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `http_log_types`
--

LOCK TABLES `http_log_types` WRITE;
/*!40000 ALTER TABLE `http_log_types` DISABLE KEYS */;
INSERT INTO `http_log_types` VALUES (1,'XML','XML Job Feed',0);
/*!40000 ALTER TABLE `http_log_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import`
--

DROP TABLE IF EXISTS `import`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `import` (
  `import_id` int(11) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `reverted` int(1) NOT NULL DEFAULT '0',
  `site_id` int(11) NOT NULL DEFAULT '0',
  `import_errors` text COLLATE utf8_unicode_ci,
  `added_lines` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`import_id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import`
--

LOCK TABLES `import` WRITE;
/*!40000 ALTER TABLE `import` DISABLE KEYS */;
/*!40000 ALTER TABLE `import` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `installtest`
--

DROP TABLE IF EXISTS `installtest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `installtest` (
  `id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `installtest`
--

LOCK TABLES `installtest` WRITE;
/*!40000 ALTER TABLE `installtest` DISABLE KEYS */;
/*!40000 ALTER TABLE `installtest` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `joborder`
--

DROP TABLE IF EXISTS `joborder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `joborder`
--

LOCK TABLES `joborder` WRITE;
/*!40000 ALTER TABLE `joborder` DISABLE KEYS */;
/*!40000 ALTER TABLE `joborder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_schema`
--

DROP TABLE IF EXISTS `module_schema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_schema` (
  `module_schema_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  PRIMARY KEY (`module_schema_id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_schema`
--

LOCK TABLES `module_schema` WRITE;
/*!40000 ALTER TABLE `module_schema` DISABLE KEYS */;
INSERT INTO `module_schema` VALUES (1,'activity',0),(2,'attachments',0),(3,'calendar',0),(4,'candidates',0),(5,'careers',0),(6,'companies',0),(7,'contacts',0),(8,'export',0),(9,'extension-statistics',1),(10,'graphs',0),(11,'home',0),(12,'import',0),(13,'install',364),(14,'joborders',0),(15,'lists',0),(16,'login',0),(17,'queue',0),(18,'reports',0),(19,'rss',0),(20,'settings',0),(21,'tests',0),(22,'toolbar',0),(23,'wizard',0),(24,'xml',0);
/*!40000 ALTER TABLE `module_schema` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mru`
--

DROP TABLE IF EXISTS `mru`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mru` (
  `mru_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `data_item_type` int(11) NOT NULL DEFAULT '0',
  `data_item_text` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`mru_id`),
  KEY `IDX_user_site` (`user_id`,`site_id`)
) ENGINE=MyISAM AUTO_INCREMENT=194 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mru`
--

LOCK TABLES `mru` WRITE;
/*!40000 ALTER TABLE `mru` DISABLE KEYS */;
/*!40000 ALTER TABLE `mru` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `queue`
--

DROP TABLE IF EXISTS `queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `queue`
--

LOCK TABLES `queue` WRITE;
/*!40000 ALTER TABLE `queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saved_list`
--

DROP TABLE IF EXISTS `saved_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saved_list`
--

LOCK TABLES `saved_list` WRITE;
/*!40000 ALTER TABLE `saved_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `saved_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saved_list_entry`
--

DROP TABLE IF EXISTS `saved_list_entry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=MyISAM AUTO_INCREMENT=7093 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saved_list_entry`
--

LOCK TABLES `saved_list_entry` WRITE;
/*!40000 ALTER TABLE `saved_list_entry` DISABLE KEYS */;
/*!40000 ALTER TABLE `saved_list_entry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saved_search`
--

DROP TABLE IF EXISTS `saved_search`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saved_search`
--

LOCK TABLES `saved_search` WRITE;
/*!40000 ALTER TABLE `saved_search` DISABLE KEYS */;
/*!40000 ALTER TABLE `saved_search` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `settings_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `settings_type` int(11) DEFAULT '0',
  PRIMARY KEY (`settings_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (18,'fromAddress','noreply@yourdomain.com',1,1),(19,'configured','1',1,1),(20,'candidateJoborderStatusSendsMessage','a:11:{i:0;s:1:\"0\";i:100;s:1:\"0\";i:200;i:0;i:250;i:0;i:300;i:1;i:400;i:1;i:500;i:1;i:600;i:1;i:650;s:1:\"0\";i:700;i:0;i:800;i:1;}',1,1);
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site`
--

DROP TABLE IF EXISTS `site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site` (
  `site_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `is_demo` int(1) NOT NULL DEFAULT '0',
  `user_licenses` int(11) NOT NULL DEFAULT '0',
  `entered_by` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site`
--

LOCK TABLES `site` WRITE;
/*!40000 ALTER TABLE `site` DISABLE KEYS */;
INSERT INTO `site` VALUES (1,'testdomain.com',0,0,0,'2005-06-01 00:00:00',NULL,NULL,0,1,0,NULL,-3,0,1,0,465,1780,14,'2016-07-04',0,0,1,0),(180,'CATS_ADMIN',0,0,0,'2005-06-01 00:00:00','catsadmin',NULL,0,1,0,NULL,-3,0,1,0,0,0,0,NULL,0,0,0,0);
/*!40000 ALTER TABLE `site` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sph_counter`
--

DROP TABLE IF EXISTS `sph_counter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sph_counter` (
  `counter_id` int(11) NOT NULL,
  `max_doc_id` int(11) NOT NULL,
  PRIMARY KEY (`counter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sph_counter`
--

LOCK TABLES `sph_counter` WRITE;
/*!40000 ALTER TABLE `sph_counter` DISABLE KEYS */;
/*!40000 ALTER TABLE `sph_counter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system`
--

DROP TABLE IF EXISTS `system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system` (
  `system_id` int(20) NOT NULL DEFAULT '0',
  `uid` int(20) DEFAULT NULL,
  `available_version` int(11) DEFAULT '0',
  `date_version_checked` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `available_version_description` text COLLATE utf8_unicode_ci,
  `disable_version_check` int(1) DEFAULT '0',
  PRIMARY KEY (`system_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system`
--

LOCK TABLES `system` WRITE;
/*!40000 ALTER TABLE `system` DISABLE KEYS */;
INSERT INTO `system` VALUES (0,2618174,900,'2009-11-19 00:00:00','',1);
/*!40000 ALTER TABLE `system` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag` (
  `tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_parent_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `site_id` int(11) unsigned DEFAULT NULL,
  `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tag_id`)
) ENGINE=MyISAM AUTO_INCREMENT=84 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag`
--

LOCK TABLES `tag` WRITE;
/*!40000 ALTER TABLE `tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `session_cookie` varchar(48) COLLATE utf8_unicode_ci DEFAULT NULL,
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
) ENGINE=MyISAM AUTO_INCREMENT=1254 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,1,'admin','admin@testdomain.com','21232f297a57a5a743894a0e4a801fc3',500,1,0,'Administrator','CATS',0,NULL,'CATS=63141ada1735df1a81579c547b172ca0',15,'a:11:{s:31:\"home:ImportantPipelineDashboard\";a:6:{i:0;a:2:{s:4:\"name\";s:10:\"First Name\";s:5:\"width\";i:85;}i:1;a:2:{s:4:\"name\";s:9:\"Last Name\";s:5:\"width\";i:75;}i:2;a:2:{s:4:\"name\";s:6:\"Status\";s:5:\"width\";i:75;}i:3;a:2:{s:4:\"name\";s:8:\"Position\";s:5:\"width\";i:275;}i:4;a:2:{s:4:\"name\";s:7:\"Company\";s:5:\"width\";i:210;}i:5;a:2:{s:4:\"name\";s:8:\"Modified\";s:5:\"width\";i:80;}}s:18:\"home:CallsDataGrid\";a:2:{i:0;a:2:{s:4:\"name\";s:4:\"Time\";s:5:\"width\";i:90;}i:1;a:2:{s:4:\"name\";s:4:\"Name\";s:5:\"width\";i:175;}}s:39:\"candidates:candidatesListByViewDataGrid\";a:9:{i:0;a:2:{s:4:\"name\";s:11:\"Attachments\";s:5:\"width\";i:31;}i:1;a:2:{s:4:\"name\";s:10:\"First Name\";s:5:\"width\";s:3:\"119\";}i:2;a:2:{s:4:\"name\";s:9:\"Last Name\";s:5:\"width\";s:3:\"149\";}i:3;a:2:{s:4:\"name\";s:4:\"City\";s:5:\"width\";i:75;}i:4;a:2:{s:4:\"name\";s:5:\"State\";s:5:\"width\";i:50;}i:5;a:2:{s:4:\"name\";s:10:\"Key Skills\";s:5:\"width\";s:3:\"280\";}i:6;a:2:{s:4:\"name\";s:5:\"Owner\";s:5:\"width\";s:2:\"72\";}i:7;a:2:{s:4:\"name\";s:7:\"Created\";s:5:\"width\";i:60;}i:8;a:2:{s:4:\"name\";s:8:\"Modified\";s:5:\"width\";i:60;}}s:25:\"activity:ActivityDataGrid\";a:7:{i:0;a:2:{s:4:\"name\";s:4:\"Date\";s:5:\"width\";i:110;}i:1;a:2:{s:4:\"name\";s:10:\"First Name\";s:5:\"width\";i:85;}i:2;a:2:{s:4:\"name\";s:9:\"Last Name\";s:5:\"width\";i:75;}i:3;a:2:{s:4:\"name\";s:9:\"Regarding\";s:5:\"width\";i:125;}i:4;a:2:{s:4:\"name\";s:8:\"Activity\";s:5:\"width\";i:65;}i:5;a:2:{s:4:\"name\";s:5:\"Notes\";s:5:\"width\";i:240;}i:6;a:2:{s:4:\"name\";s:10:\"Entered By\";s:5:\"width\";i:60;}}s:37:\"joborders:JobOrdersListByViewDataGrid\";a:12:{i:0;a:2:{s:4:\"name\";s:11:\"Attachments\";s:5:\"width\";i:10;}i:1;a:2:{s:4:\"name\";s:2:\"ID\";s:5:\"width\";i:26;}i:2;a:2:{s:4:\"name\";s:5:\"Owner\";s:5:\"width\";i:55;}i:3;a:2:{s:4:\"name\";s:5:\"Title\";s:5:\"width\";i:170;}i:4;a:2:{s:4:\"name\";s:7:\"Company\";s:5:\"width\";i:135;}i:5;a:2:{s:4:\"name\";s:4:\"Type\";s:5:\"width\";i:30;}i:6;a:2:{s:4:\"name\";s:6:\"Status\";s:5:\"width\";i:40;}i:7;a:2:{s:4:\"name\";s:7:\"Created\";s:5:\"width\";i:55;}i:8;a:2:{s:4:\"name\";s:3:\"Age\";s:5:\"width\";i:30;}i:9;a:2:{s:4:\"name\";s:9:\"Submitted\";s:5:\"width\";i:18;}i:10;a:2:{s:4:\"name\";s:8:\"Pipeline\";s:5:\"width\";i:18;}i:11;a:2:{s:4:\"name\";s:9:\"Recruiter\";s:5:\"width\";i:65;}}s:37:\"companies:CompaniesListByViewDataGrid\";a:9:{i:0;a:2:{s:4:\"name\";s:11:\"Attachments\";s:5:\"width\";i:10;}i:1;a:2:{s:4:\"name\";s:4:\"Name\";s:5:\"width\";i:255;}i:2;a:2:{s:4:\"name\";s:4:\"Jobs\";s:5:\"width\";i:40;}i:3;a:2:{s:4:\"name\";s:4:\"City\";s:5:\"width\";i:90;}i:4;a:2:{s:4:\"name\";s:5:\"State\";s:5:\"width\";i:50;}i:5;a:2:{s:4:\"name\";s:5:\"Phone\";s:5:\"width\";i:85;}i:6;a:2:{s:4:\"name\";s:5:\"Owner\";s:5:\"width\";i:65;}i:7;a:2:{s:4:\"name\";s:7:\"Created\";s:5:\"width\";i:60;}i:8;a:2:{s:4:\"name\";s:8:\"Modified\";s:5:\"width\";i:60;}}s:35:\"contacts:ContactsListByViewDataGrid\";a:9:{i:0;a:2:{s:4:\"name\";s:11:\"Attachments\";s:5:\"width\";i:10;}i:1;a:2:{s:4:\"name\";s:10:\"First Name\";s:5:\"width\";i:80;}i:2;a:2:{s:4:\"name\";s:9:\"Last Name\";s:5:\"width\";i:80;}i:3;a:2:{s:4:\"name\";s:7:\"Company\";s:5:\"width\";i:135;}i:4;a:2:{s:4:\"name\";s:5:\"Title\";s:5:\"width\";i:135;}i:5;a:2:{s:4:\"name\";s:10:\"Work Phone\";s:5:\"width\";i:85;}i:6;a:2:{s:4:\"name\";s:5:\"Owner\";s:5:\"width\";i:85;}i:7;a:2:{s:4:\"name\";s:7:\"Created\";s:5:\"width\";i:60;}i:8;a:2:{s:4:\"name\";s:8:\"Modified\";s:5:\"width\";i:60;}}s:19:\"lists:ListsDataGrid\";a:7:{i:0;a:2:{s:4:\"name\";s:5:\"Count\";s:5:\"width\";i:45;}i:1;a:2:{s:4:\"name\";s:11:\"Description\";s:5:\"width\";i:355;}i:2;a:2:{s:4:\"name\";s:9:\"Data Type\";s:5:\"width\";i:75;}i:3;a:2:{s:4:\"name\";s:9:\"List Type\";s:5:\"width\";i:75;}i:4;a:2:{s:4:\"name\";s:5:\"Owner\";s:5:\"width\";i:75;}i:5;a:2:{s:4:\"name\";s:7:\"Created\";s:5:\"width\";i:60;}i:6;a:2:{s:4:\"name\";s:8:\"Modified\";s:5:\"width\";i:60;}}s:53:\"candidates:candidatesSavedListByViewDataGrid:s:1:\"1\";\";a:9:{i:0;a:2:{s:4:\"name\";s:11:\"Attachments\";s:5:\"width\";i:31;}i:1;a:2:{s:4:\"name\";s:10:\"First Name\";s:5:\"width\";i:75;}i:2;a:2:{s:4:\"name\";s:9:\"Last Name\";s:5:\"width\";i:85;}i:3;a:2:{s:4:\"name\";s:4:\"City\";s:5:\"width\";i:75;}i:4;a:2:{s:4:\"name\";s:5:\"State\";s:5:\"width\";i:50;}i:5;a:2:{s:4:\"name\";s:10:\"Key Skills\";s:5:\"width\";i:200;}i:6;a:2:{s:4:\"name\";s:5:\"Owner\";s:5:\"width\";i:65;}i:7;a:2:{s:4:\"name\";s:8:\"Modified\";s:5:\"width\";i:60;}i:8;a:2:{s:4:\"name\";s:13:\"Added To List\";s:5:\"width\";i:75;}}s:53:\"candidates:candidatesSavedListByViewDataGrid:s:1:\"2\";\";a:9:{i:0;a:2:{s:4:\"name\";s:11:\"Attachments\";s:5:\"width\";i:31;}i:1;a:2:{s:4:\"name\";s:10:\"First Name\";s:5:\"width\";i:75;}i:2;a:2:{s:4:\"name\";s:9:\"Last Name\";s:5:\"width\";i:85;}i:3;a:2:{s:4:\"name\";s:4:\"City\";s:5:\"width\";i:75;}i:4;a:2:{s:4:\"name\";s:5:\"State\";s:5:\"width\";i:50;}i:5;a:2:{s:4:\"name\";s:10:\"Key Skills\";s:5:\"width\";i:200;}i:6;a:2:{s:4:\"name\";s:5:\"Owner\";s:5:\"width\";i:65;}i:7;a:2:{s:4:\"name\";s:8:\"Modified\";s:5:\"width\";i:60;}i:8;a:2:{s:4:\"name\";s:13:\"Added To List\";s:5:\"width\";i:75;}}s:51:\"companies:companiesSavedListByViewDataGrid:s:1:\"3\";\";a:9:{i:0;a:2:{s:4:\"name\";s:11:\"Attachments\";s:5:\"width\";i:10;}i:1;a:2:{s:4:\"name\";s:4:\"Name\";s:5:\"width\";i:255;}i:2;a:2:{s:4:\"name\";s:4:\"Jobs\";s:5:\"width\";i:40;}i:3;a:2:{s:4:\"name\";s:4:\"City\";s:5:\"width\";i:90;}i:4;a:2:{s:4:\"name\";s:5:\"State\";s:5:\"width\";i:50;}i:5;a:2:{s:4:\"name\";s:5:\"Phone\";s:5:\"width\";i:85;}i:6;a:2:{s:4:\"name\";s:5:\"Owner\";s:5:\"width\";i:65;}i:7;a:2:{s:4:\"name\";s:7:\"Created\";s:5:\"width\";i:60;}i:8;a:2:{s:4:\"name\";s:8:\"Modified\";s:5:\"width\";i:60;}}}',0,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),(1250,180,'cats@rootadmin','0','cantlogin',0,0,0,'Automated','CATS',0,NULL,NULL,15,NULL,0,'','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_login`
--

DROP TABLE IF EXISTS `user_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_login` (
  `user_login_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
) ENGINE=MyISAM AUTO_INCREMENT=50 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_login`
--

LOCK TABLES `user_login` WRITE;
/*!40000 ALTER TABLE `user_login` DISABLE KEYS */;
INSERT INTO `user_login` VALUES (48,1,1,'172.19.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36','2016-07-04 02:31:19',1,'172.19.0.1','2016-07-04 02:31:57'),(49,1,1,'172.19.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36','2016-07-04 02:32:05',1,'172.19.0.1','2016-07-04 02:32:09');
/*!40000 ALTER TABLE `user_login` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `word_verification`
--

DROP TABLE IF EXISTS `word_verification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `word_verification` (
  `word_verification_ID` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(28) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`word_verification_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `word_verification`
--

LOCK TABLES `word_verification` WRITE;
/*!40000 ALTER TABLE `word_verification` DISABLE KEYS */;
/*!40000 ALTER TABLE `word_verification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `xml_feed_submits`
--

DROP TABLE IF EXISTS `xml_feed_submits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `xml_feed_submits` (
  `feed_id` int(11) NOT NULL AUTO_INCREMENT,
  `feed_site` varchar(75) NOT NULL,
  `feed_url` varchar(255) NOT NULL,
  `date_last_post` date NOT NULL,
  PRIMARY KEY (`feed_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `xml_feed_submits`
--

LOCK TABLES `xml_feed_submits` WRITE;
/*!40000 ALTER TABLE `xml_feed_submits` DISABLE KEYS */;
/*!40000 ALTER TABLE `xml_feed_submits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `xml_feeds`
--

DROP TABLE IF EXISTS `xml_feeds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `xml_feeds`
--

LOCK TABLES `xml_feeds` WRITE;
/*!40000 ALTER TABLE `xml_feeds` DISABLE KEYS */;
INSERT INTO `xml_feeds` VALUES (1,'Indeed','Indeed.com job search engine.','http://www.indeed.com','http://www.indeed.com/jsp/includejobs.jsp','Thank you for submitting your XML job feed','indeed'),(2,'SimplyHired','SimplyHired.com job search engine','http://www.simplyhired.com','http://www.simplyhired.com/confirmation.php','Thanks for Contacting Us','simplyhired');
/*!40000 ALTER TABLE `xml_feeds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `zipcodes`
--

DROP TABLE IF EXISTS `zipcodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zipcodes` (
  `zipcode` mediumint(9) NOT NULL DEFAULT '0',
  `city` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `areacode` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`zipcode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zipcodes`
--

LOCK TABLES `zipcodes` WRITE;
/*!40000 ALTER TABLE `zipcodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `zipcodes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-07-04  2:34:57
