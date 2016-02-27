<?php
/*
 * CATS
 * Sourcer Module Schema
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
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
 * $Id: Schema.php 3813 2007-12-05 23:16:22Z brian $
 */


class CATSSchema
{
    public static function get()
    {
        /* Revision => code pairs */
        return array(
            '0' => '
            ',
            '1' => '
                ALTER IGNORE TABLE `attachment` ADD COLUMN `directory_name` VARCHAR(40) DEFAULT 0;
            ',
            '2' => '
                UPDATE `attachment` SET directory_name = attachment_id;
            ',
            '3' => '
                UPDATE system SET disable_version_check = 1;
            ',
            /* Upgrade directory names to prevent iteration through attachments folder. */
            '4' => 'PHP:
                include_once(\'lib/FileUtility.php\');
                $rs = $db->getAllAssoc(\'SELECT * FROM attachment\');
                foreach ($rs as $index => $data)
                {
                    if (strlen($data[\'directory_name\']) < 25)
                    {
                        $dir = FileUtility::getUniqueDirectory();
                        if (rename(\'attachments/\' . $data[\'directory_name\'], \'attachments/\' . $dir))
                        {
                            $db->query("UPDATE attachment SET directory_name = \'" . $dir . "\' WHERE attachment_id = " . $data[\'attachment_id\']);
                        }
                    }
                }
            ',
            '5' => '
                UPDATE client SET name = \'Internal Postings\' WHERE default_client = 1;
            ',
            '8' => '
                DROP TABLE IF EXISTS `job_board_settings`;
                DROP TABLE IF EXISTS `job_board_template`;
                DROP TABLE IF EXISTS `job_board_template_site`;
                CREATE TABLE `job_board_settings` (
                  `job_board_settings_id` int(11) NOT NULL auto_increment,
                  `setting` varchar(128) NOT NULL default \'\',
                  `value` text,
                  `site_id` int(11) NOT NULL default \'0\',
                  `entered_by` int(11) default NULL,
                  PRIMARY KEY  (`job_board_settings_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
                CREATE TABLE `job_board_template` (
                  `job_board_template_id` int(11) NOT NULL auto_increment,
                  `job_board_name` varchar(96) NOT NULL,
                  `setting` varchar(128) NOT NULL default \'\',
                  `value` text,
                  PRIMARY KEY  (`job_board_template_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
                CREATE TABLE `job_board_template_site` (
                  `job_board_template_id` int(11) NOT NULL auto_increment,
                  `job_board_name` varchar(96) NOT NULL,
                  `site_id` int(11) NOT NULL,
                  `setting` varchar(128) NOT NULL default \'\',
                  `value` text,
                  PRIMARY KEY  (`job_board_template_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
            ',
            '16' => '
                DELETE FROM email_template WHERE tag = \'EMAIL_TEMPLATE_CANDIDATEAPPLY\';
                INSERT INTO
                    email_template
                SELECT
                    NULL,
                    \'* Auto generated message. Please DO NOT reply *\r\n%DATETIME%\r\n\r\nDear %CANDFULLNAME%,\r\n\r\nThank you for applying to the job %JBODTITLE% with our online career portal!  Your application has been entered in the system and a recruiter will review it shortly.\r\n\r\nTake care,\r\n%SITENAME%\',
                    1,
                    site_id,
                    \'EMAIL_TEMPLATE_CANDIDATEAPPLY\',
                    \'Candidate Application Recieved (Sent to Candidate using Career Portal)\',
                    \'%CANDFIRSTNAME%%CANDFULLNAME%%JBODCLIENT%%JBODTITLE%%JBODOWNER%\',
                    0
                FROM
                    site
                WHERE
                    site_id != 180;

                DELETE FROM email_template WHERE tag = \'EMAIL_TEMPLATE_CANDIDATEPORTALNEW\';
                INSERT INTO
                    email_template
                SELECT
                    NULL,
                    \'%DATETIME%\r\n\r\nDear %JBODOWNER%,\r\n\r\nThis E-Mail is a notification that a Candidate has applied to your job order through the online candidate portal.\r\n\r\nCandidate Name: %CANDFULLNAME%\r\nCandidate URL: %CANDCATSURL%\r\nJob Order URL: %JBODCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%\',
                    1,
                    site_id,
                    \'EMAIL_TEMPLATE_CANDIDATEPORTALNEW\',
                    \'Candidate Application Recieved (Sent to Owner of Job Order from Career Portal)\',
                    \'%CANDFIRSTNAME%%CANDFULLNAME%%JBODOWNER%%JBODTITLE%%JBODCLIENT%%JBODCATSURL%%JBODID%%CANDCATSURL%\',
                    0
                FROM
                    site
                WHERE
                    site_id != 180;
            ',
            '22' => '
                ALTER IGNORE TABLE `site` ADD COLUMN `timezone_offset` int(11) DEFAULT 0;
                ALTER IGNORE TABLE `site` ADD COLUMN `time_format_24` int(1) DEFAULT 0;
                ALTER IGNORE TABLE `site` ADD COLUMN `date_format_ddmmyy` int(1) DEFAULT 0;
            ',
            '23' => '
                ALTER TABLE `site` CHANGE `timezone_offset` `time_zone` int(5) DEFAULT 0;
            ',
            '24' => '
                UPDATE `site` SET time_zone = ' . OFFSET_GMT . ';
            ',
            '27' => 'PHP:
                $db->query("DELETE FROM job_board_template");
                $db->query(
                    "INSERT INTO job_board_template VALUES
                        (NULL, \'CATS Standard\', \'Header\', \'<div style=\"text-align:center;\" class=\"mainLogoText\"><siteName></div>\r\n<div style=\"text-align:center;\" class=\"subLogoText\">Career Center</div>\r\n<br />\r\n<center>\r\n<table width=\"950\">\r\n<tr style=\"vertical-align: top; border-collapse: collapse;\">\r\n<td style=\"width:180px;\">\r\n<p class=\"noteUnsized\">Menu</p>\r\n<br />\r\n<a-LinkMain>Main Page</a><br />\r\n<a-ListAll>List All Jobs</a><br />\r\n</td>\'),
                        (NULL, \'CATS Standard\', \'Content - Main\', \'<td>\r\n<p class=\"noteUnsized\">Main</p>\r\n<br />\r\n<!-- Main content starts here -->\r\nWelcome to the <siteName> Career Portal!<br />\r\n<p class=\"mainHeading\">Careers at <siteName></p>\r\nIf you are interested in joining a winning team, we invite you to view our current openings and submit your resume.<br />\r\n<p class=\"mainHeading\">Current Openings</p>\r\nThere are currently <numberOfOpenPositions> openings.  <a-ListAll>Click Here</a> to view them.\r\n</td>\'),
                        (NULL, \'CATS Standard\', \'Content - Apply for Position\', \'<td>\r\n<p class=\"noteUnsized\">Apply to Position</p>\r\n<br>\r\nYou are applying for the position \\\'<title>\\\'.  Please type in your information below - we will match the provided E-Mail address with our system and update your existing profile if it exists.<br>\r\n<br>\r\n<table>\r\n<tr><td style=\"width:200px;\">First Name:</td><td><input-firstName></td></tr>\r\n<tr><td style=\"width:200px;\">Last Name:</td><td><input-lastName></td></tr>\r\n<tr><td>&nbsp;</td><td>&nbsp;</td></tr>\r\n<tr><td style=\"width:200px;\">E-Mail Address:</td><td><input-email></td></tr>\r\n<tr><td style=\"width:200px;\">Retype E-Mail:</td><td><input-emailconfirm></td></tr>\r\n<tr><td>&nbsp;</td><td>&nbsp;</td></tr>\r\n<tr><td style=\"width:200px;\">Resume Upload:</td><td><input-resumeUpload></td></tr>\r\n<tr><td style=\"width:200px;\">Extra notes concerning this position:</td><td><input-extraNotes></td></tr>\r\n</table>\r\n<br>\r\n<submit value=\"Apply for Position\">\r\n</td>\'),
                        (NULL, \'CATS Standard\', \'Footer\', \'</tr>\r\n</table>\r\n</center>\'),
                        (NULL, \'CATS Standard\', \'CSS\', \'body\r\n{\r\npadding: 8px 18px 8px 18px;\r\nmargin: 0px;\r\nfont: normal normal normal 10px Verdana, Tahoma, sans-serif;\r\n}\r\n.inputBoxName\r\n{\r\nwidth:220px;\r\n}\r\n.inputBoxNormal\r\n{\r\nwidth:300px;\r\n}\r\n.inputBoxArea\r\n{\r\nwidth:250px;\r\nheight:100px;\r\n}\r\n.mainLogoText\r\n{\r\ncolor: #2f4f88;\r\nmargin-bottom: 0px;\r\npadding: 0px 5px 0px 5px;\r\nfont: normal normal bold 30px Verdana, Tahoma, sans-serif;\r\n}\r\n.subLogoText\r\n{\r\ncolor: #000000;\r\nmargin-bottom: 0px;\r\npadding: 0px 2px 0px 2px;\r\nfont: normal normal bold 14px Verdana, Tahoma, sans-serif;\r\n}\r\np.mainHeading\r\n{\r\ncolor: #000000;\r\nmargin-bottom: 0px;\r\npadding: 0px 2px 0px 2px;\r\nfont: normal normal bold 16px Verdana, Tahoma, sans-serif;\r\n}\r\np.noteUnsized\r\n{\r\nbackground-image: url(\\\'images/bgBlue.gif\\\');\r\nbackground-repeat: repeat-x;\r\npadding: 4px;\r\nmargin-top: 0px;\r\nmargin-bottom: 8px;\r\nborder-top: 1px solid #bbb;\r\nborder-bottom: 1px solid #bbb;\r\nfont: normal normal bold  10px Verdana, Tahoma, sans-serif;\r\ncolor: #2f4c87;\r\n}\r\np.noteUnsized\r\n{\r\nbackground-image: url(\\\'images/bgBlue.gif\\\');\r\nbackground-repeat: repeat-x;\r\npadding: 4px;\r\nmargin-top: 0px;\r\nmargin-bottom: 8px;\r\nborder-top: 1px solid #bbb;\r\nborder-bottom: 1px solid #bbb;\r\nfont: normal normal bold  10px Verdana, Tahoma, sans-serif;\r\ncolor: #2f4c87;\r\n}\r\ntable.sortable\r\n{\r\nmargin: 5px 0px 5px 0px;\r\nborder-collapse: collapse;\r\nborder: 1px solid #ccc;\r\nfont: normal normal normal  10px Verdana, Tahoma, sans-serif;\r\nempty-cells: show;\r\n}\r\ntr.rowHeading\r\n{\r\ntext-align:center;\r\nbackground-image: url(\\\'images/bgBlue.gif\\\');\r\nbackground-repeat: repeat-x;\r\npadding: 4px;\r\nmargin-top: 0px;\r\nmargin-bottom: 8px;\r\nborder-top: 1px solid #bbb;\r\nborder-bottom: 1px solid #bbb;\r\nfont: normal normal bold  10px Verdana, Tahoma, sans-serif;\r\ncolor: #2f4c87;\r\n}\r\ntr.oddTableRow\r\n{\r\nbackground: #fff;\r\nheight:20px;\r\n}\r\ntr.evenTableRow\r\n{\r\nbackground: #f4f4f4;\r\nheight:20px;\r\n}\r\na.sortheader:hover,\r\na.sortheader:link,\r\na.sortheader:visited\r\n{text-decoration: none;}\r\n \'),
                        (NULL, \'CATS Standard\', \'Content - Search Results\', \'<td>\r\n<p class=\"noteUnsized\">Search Results</p>\r\n<br />\r\nWe have found the following <numberOfSearchResults> positions for you:<br />\r\n<searchResultsTable>\r\n</td>\'),
                        (NULL, \'CATS Standard\', \'Content - Job Details\', \'<td>\r\n<p class=\"noteUnsized\">Job Details</p>\r\n<br>\r\n<table>\r\n<tr><td style=\"width:160px; font-weight:bold;\">Title:</td><td><title></td></tr>\r\n<tr><td style=\"width:160px; font-weight:bold;\">Date Created:</td><td><created></td></tr>\r\n<tr><td style=\"width:160px; font-weight:bold;\">Location:</td><td><city>, <state></td></tr>\r\n<tr><td style=\"width:160px; font-weight:bold;\">Openings:</td><td><openings></td></tr>\r\n</table>\r\n<br>\r\n<description><br />\r\n<br />\r\n<br>\r\n<a-applyToJob>Apply to Job</a><br>\r\n</td>\'),
                        (NULL, \'CATS Standard\', \'Content - Thanks for your Submission\', \'<td>\r\n<p class=\"noteUnsized\">Apply to Position</p>\r\n<br>\r\n<br>\r\nThanks for your submission!<br>\r\n<br>\r\nYou should receive an e-mail confirmation of your submission shortly.<br>\r\n<br>\r\n\r\n<a-jobDetails>Go Back to Job Details</a>\r\n</td>\'),
                        (NULL, \'Blank Page\', \'Header\', \'\'),
                        (NULL, \'Blank Page\', \'Left\', \'\'),
                        (NULL, \'Blank Page\', \'Content - Main\', \'\'),
                        (NULL, \'Blank Page\', \'Content - Apply for Position\', null),
                        (NULL, \'Blank Page\', \'Content - Search Results\', \'\'),
                        (NULL, \'Blank Page\', \'Footer\', \'\'),
                        (NULL, \'Blank Page\', \'CSS\', \'\');"
                );
            ',
            '28' => '
                ALTER TABLE `calendar_event` CHANGE `reminder_time` `reminder_time` int(11) DEFAULT 0;
            ',
            '41' => '
                UPDATE dashboard_component SET module_parameters = \'\\"Activity (Last Two Weeks)\\"\' WHERE module_name = \'titleBar\' AND module_parameters = \'\\"Last 2 weeks activity:\\"\';
                UPDATE dashboard_component SET module_parameters = \'\\"Recent Activity (Last Two Weeks)\\"\' WHERE module_name = \'titleBar\' AND module_parameters = \'\\"Activity (Last Two Weeks)\\"\';
                UPDATE dashboard_component SET module_name = \'activity\' WHERE module_name = \'weeklyActivity\';
                UPDATE dashboard_module SET name = \'activity\', function = \'activityDashboard\', title = \'Activity\', preview_image = \'index.php?m=graphs&a=activity&width=365&height=180\' WHERE name = \'weeklyActivity\' AND object = \'graphs\';
                UPDATE dashboard_module SET preview_image = \'index.php?m=graphs&a=activity&width=365&height=180\' WHERE name = \'activity\' AND object = \'graphs\';
                UPDATE dashboard_module SET preview_image = \'images/dashboard_preview/activity.png\' WHERE name = \'activity\' AND object = \'graphs\';
                UPDATE dashboard_module SET preview_image = \'images/dashboard_preview/candidates.png\' WHERE name = \'newCandidates\' AND object = \'graphs\';
                UPDATE dashboard_module SET preview_image = \'images/dashboard_preview/joborders.png\' WHERE name = \'newJobOrders\' AND object = \'graphs\';
                UPDATE dashboard_module SET preview_image = \'images/dashboard_preview/submissions.png\' WHERE name = \'newSubmissions\' AND object = \'graphs\';
                UPDATE dashboard_module SET preview_image = \'images/dashboard_preview/text.png\' WHERE name = \'text\' AND object = \'generic\';
                UPDATE dashboard_module SET name = \'myUpcomingEvents\', function = \'getUpcomingDashboard\', title = \'My Upcoming Events\' WHERE name = \'calendarToday\' AND object = \'calendar\';
                UPDATE dashboard_component SET module_name = \'myUpcomingEvents\' WHERE module_name = \'calendarToday\';
            ',
            '42' => '
                UPDATE joborder SET date_modified = date_created WHERE date_modified = \'0000-00-00\';
            ',
            '47' => 'PHP:
                $db->query("DELETE FROM job_board_template");
                $db->query(
                    "INSERT INTO job_board_template VALUES
                        (NULL, \'CATS Standard\', \'Header\', \'<div style=\"text-align:center;\" class=\"mainLogoText\"><siteName></div>\r\n<div style=\"text-align:center;\" class=\"subLogoText\">Career Center</div>\r\n<br />\r\n<center>\r\n<table width=\"950\">\r\n<tr style=\"vertical-align: top; border-collapse: collapse;\">\r\n<td style=\"width:180px;\">\r\n<p class=\"noteUnsized\">Menu</p>\r\n<br />\r\n<a-LinkMain>Main Page</a><br />\r\n<a-ListAll>List All Jobs</a><br />\r\n</td>\'),
                        (NULL, \'CATS Standard\', \'Content - Main\', \'<td>\r\n<p class=\"noteUnsized\">Main</p>\r\n<br />\r\n<!-- Main content starts here -->\r\nWelcome to the <siteName> Career Portal!<br />\r\n<p class=\"mainHeading\">Careers at <siteName></p>\r\nIf you are interested in joining a winning team, we invite you to view our current openings and submit your resume.<br />\r\n<p class=\"mainHeading\">Current Openings</p>\r\nThere are currently <numberOfOpenPositions> openings.  <a-ListAll>Click Here</a> to view them.\r\n</td>\'),
                        (NULL, \'CATS Standard\', \'Content - Apply for Position\', \'<td>\r\n<p class=\"noteUnsized\">Apply to Position</p>\r\n<br>\r\nYou are applying for the position \\\'<title>\\\'.  Please type in your information below - we will match the provided E-Mail address with our system and update your existing profile if it exists.<br>\r\n<br>\r\n<table>\r\n<tr><td style=\"width:200px;\">First Name:</td><td><input-firstName></td></tr>\r\n<tr><td style=\"width:200px;\">Last Name:</td><td><input-lastName></td></tr>\r\n<tr><td>&nbsp;</td><td>&nbsp;</td></tr>\r\n<tr><td style=\"width:200px;\">E-Mail Address:</td><td><input-email></td></tr>\r\n<tr><td style=\"width:200px;\">Retype E-Mail:</td><td><input-emailconfirm></td></tr>\r\n<tr><td>&nbsp;</td><td>&nbsp;</td></tr>\r\n<tr><td style=\"width:200px;\">Resume Upload:</td><td><input-resumeUpload></td></tr>\r\n<tr><td style=\"width:200px;\">Extra notes concerning this position:</td><td><input-extraNotes></td></tr>\r\n</table>\r\n<br>\r\n<submit value=\"Apply for Position\">\r\n</td>\'),
                        (NULL, \'CATS Standard\', \'Footer\', \'</tr>\r\n</table>\r\n</center>\'),
                        (NULL, \'CATS Standard\', \'CSS\', \'body\r\n{\r\npadding: 8px 18px 8px 18px;\r\nmargin: 0px;\r\nfont: normal normal normal 10px Verdana, Tahoma, sans-serif;\r\n}\r\n.inputBoxName\r\n{\r\nwidth:220px;\r\n}\r\n.inputBoxNormal\r\n{\r\nwidth:300px;\r\n}\r\n.inputBoxArea\r\n{\r\nwidth:250px;\r\nheight:100px;\r\n}\r\n.mainLogoText\r\n{\r\ncolor: #2f4f88;\r\nmargin-bottom: 0px;\r\npadding: 0px 5px 0px 5px;\r\nfont: normal normal bold 30px Verdana, Tahoma, sans-serif;\r\n}\r\n.subLogoText\r\n{\r\ncolor: #000000;\r\nmargin-bottom: 0px;\r\npadding: 0px 2px 0px 2px;\r\nfont: normal normal bold 14px Verdana, Tahoma, sans-serif;\r\n}\r\np.mainHeading\r\n{\r\ncolor: #000000;\r\nmargin-bottom: 0px;\r\npadding: 0px 2px 0px 2px;\r\nfont: normal normal bold 16px Verdana, Tahoma, sans-serif;\r\n}\r\np.noteUnsized\r\n{\r\nbackground-image: url(\\\'images/blue_gradient.jpg\\\');\r\nbackground-repeat: repeat-x;\r\npadding: 4px;\r\nmargin-top: 0px;\r\nmargin-bottom: 8px;\r\nborder-top: 1px solid #bbb;\r\nborder-bottom: 1px solid #bbb;\r\nfont: normal normal bold  12px/120% Verdana, Tahoma, sans-serif;\r\ncolor: #f4f4f4;\r\n}\r\np.noteUnsized\r\n{\r\nbackground-image: url(\\\'images/blue_gradient.jpg\\\');\r\nbackground-repeat: repeat-x;\r\npadding: 4px;\r\nmargin-top: 0px;\r\nmargin-bottom: 8px;\r\nborder-top: 1px solid #bbb;\r\nborder-bottom: 1px solid #bbb;\r\nfont: normal normal bold  12px/120% Verdana, Tahoma, sans-serif;\r\ncolor: #f4f4f4;\r\n}\r\ntable.sortable\r\n{\r\nmargin: 5px 0px 5px 0px;\r\nborder-collapse: collapse;\r\nborder: 1px solid #ccc;\r\nfont: normal normal normal  10px Verdana, Tahoma, sans-serif;\r\nempty-cells: show;\r\n}\r\ntr.rowHeading\r\n{\r\ntext-align:center;\r\nbackground-image: url(\\\'images/blue_gradient.jpg\\\');\r\nbackground-repeat: repeat-x;\r\npadding: 4px;\r\nmargin-top: 0px;\r\nmargin-bottom: 8px;\r\nborder-top: 1px solid #bbb;\r\nborder-bottom: 1px solid #bbb;\r\nfont: normal normal bold  10px Verdana, Tahoma, sans-serif;\r\ncolor: #2f4c87;\r\n}\r\ntr.oddTableRow\r\n{\r\nbackground: #fff;\r\nheight:20px;\r\n}\r\ntr.evenTableRow\r\n{\r\nbackground: #f4f4f4;\r\nheight:20px;\r\n}\r\na.sortheader:hover,\r\na.sortheader:link,\r\na.sortheader:visited\r\n{text-decoration: none;}\r\n \'),
                        (NULL, \'CATS Standard\', \'Content - Search Results\', \'<td>\r\n<p class=\"noteUnsized\">Search Results</p>\r\n<br />\r\nWe have found the following <numberOfSearchResults> positions for you:<br />\r\n<searchResultsTable>\r\n</td>\'),
                        (NULL, \'CATS Standard\', \'Content - Job Details\', \'<td>\r\n<p class=\"noteUnsized\">Job Details</p>\r\n<br>\r\n<table>\r\n<tr><td style=\"width:160px; font-weight:bold;\">Title:</td><td><title></td></tr>\r\n<tr><td style=\"width:160px; font-weight:bold;\">Date Created:</td><td><created></td></tr>\r\n<tr><td style=\"width:160px; font-weight:bold;\">Location:</td><td><city>, <state></td></tr>\r\n<tr><td style=\"width:160px; font-weight:bold;\">Openings:</td><td><openings></td></tr>\r\n</table>\r\n<br>\r\n<description><br />\r\n<br />\r\n<br>\r\n<a-applyToJob>Apply to Job</a><br>\r\n</td>\'),
                        (NULL, \'CATS Standard\', \'Content - Thanks for your Submission\', \'<td>\r\n<p class=\"noteUnsized\">Apply to Position</p>\r\n<br>\r\n<br>\r\nThanks for your submission!<br>\r\n<br>\r\nYou should receive an e-mail confirmation of your submission shortly.<br>\r\n<br>\r\n\r\n<a-jobDetails>Go Back to Job Details</a>\r\n</td>\'),
                        (NULL, \'Blank Page\', \'Header\', \'\'),
                        (NULL, \'Blank Page\', \'Left\', \'\'),
                        (NULL, \'Blank Page\', \'Content - Main\', \'\'),
                        (NULL, \'Blank Page\', \'Content - Apply for Position\', null),
                        (NULL, \'Blank Page\', \'Content - Search Results\', \'\'),
                        (NULL, \'Blank Page\', \'Footer\', \'\'),
                        (NULL, \'Blank Page\', \'CSS\', \'\');"
                );
            ',
            '48' => '
                ALTER IGNORE TABLE `candidate_joborder` ADD COLUMN `added_by` int(11) DEFAULT NULL;
            ',
            '49' => '
                DELETE FROM email_template WHERE site_id = 180;
            ',
            '50' => '
                UPDATE
                    email_template
                SET
                    text = \'%DATETIME%\r\n\r\nDear %JBODOWNER%,\r\n\r\nThis e-mail is a notification that a candidate has applied to your job order through the online candidate portal.\r\n\r\nJob Order: %JBODTITLE%\r\nCandidate Name: %CANDFULLNAME%\r\nCandidate URL: %CANDCATSURL%\r\nJob Order URL: %JBODCATSURL%\r\n\r\n--\r\nCATS\r\n%SITENAME%\'
                WHERE
                    tag = \'EMAIL_TEMPLATE_CANDIDATEPORTALNEW\'
                    AND text = \'%DATETIME%\r\n\r\nDear %JBODOWNER%,\r\n\r\nThis E-Mail is a notification that a Candidate has applied to your job order through the online candidate portal.\r\n\r\nCandidate Name: %CANDFULLNAME%\r\nCandidate URL: %CANDCATSURL%\r\nJob Order URL: %JBODCATSURL%\r\n\r\nTake care,\r\nCATS \r\n%SITENAME%\';

                UPDATE
                    email_template
                SET
                    text = \'* This is an auto-generated message. Please do not reply. *\r\n%DATETIME%\r\n\r\nDear %CANDFULLNAME%,\r\n\r\nThank you for applying to the %JBODTITLE% position with our online career portal! Your application has been entered into our system and someone will review it shortly.\r\n\r\n--\r\n%SITENAME%\'
                WHERE
                    tag = \'EMAIL_TEMPLATE_CANDIDATEAPPLY\'
                    AND text = \'* Auto generated message. Please DO NOT reply *\r\n%DATETIME%\r\n\r\nDear %CANDFULLNAME%,\r\n\r\nThank you for applying to the job %JBODTITLE% with our online career portal!  Your application has been entered in the system and a recruiter will review it shortly.\r\n\r\nTake care,\r\n%SITENAME%\';
            ',
            '51' => '
                UPDATE
                    email_template
                SET
                    title = \'Candidate Application Received (Sent to Candidate using Career Portal)\'
                WHERE
                    title = \'Candidate Application Recieved (Sent to Candidate using Career Portal)\';

                UPDATE
                    email_template
                SET
                    title = \'Candidate Application Received (Sent to Owner of Job Order from Career Portal)\'
                WHERE
                    title = \'Candidate Application Recieved (Sent to Owner of Job Order from Career Portal)\';
            ',
            '55' => 'PHP:
                $db->query("ALTER IGNORE TABLE `user` DROP COLUMN `is_beta_tester`", true);
                $db->query("DROP TABLE IF EXISTS `address_parser_failures`", false);
                $db->query("DROP TABLE IF EXISTS `admin_user`", false);
                $db->query("DROP TABLE IF EXISTS `admin_user_login`", false);
                $db->query("DROP TABLE IF EXISTS `candidate_joborder_status_type`", false);
                $db->query("DROP TABLE IF EXISTS `version`", false);
            ',
            '56' => 'PHP:
                $db->query("DELETE FROM job_board_template");
                $db->query(
                    "INSERT INTO job_board_template VALUES
                        (NULL, \'CATS Standard\', \'Header\', \'<div style=\"text-align:center;\" class=\"mainLogoText\"><siteName></div>\r\n<div style=\"text-align:center;\" class=\"subLogoText\">Career Center</div>\r\n<br />\r\n<table width=\"950\" align=\"center\">\r\n<tr style=\"vertical-align: top; border-collapse: collapse;\">\r\n<td style=\"width:180px;\">\r\n<p class=\"noteUnsized\">Menu</p>\r\n<br />\r\n<a-LinkMain>Main Page</a><br />\r\n<a-ListAll>List All Jobs</a><br />\r\n</td>\'),
                        (NULL, \'CATS Standard\', \'Content - Main\', \'<td>\r\n<p class=\"noteUnsized\">Main</p>\r\n<br />\r\n<!-- Main content starts here -->\r\nWelcome to the <siteName> Career Portal!<br />\r\n<p class=\"mainHeading\">Careers at <siteName></p>\r\nIf you are interested in joining a winning team, we invite you to view our current openings and submit your resume.<br />\r\n<p class=\"mainHeading\">Current Openings</p>\r\nThere are currently <numberOfOpenPositions> openings.  <a-ListAll>Click Here</a> to view them.\r\n</td>\'),
                        (NULL, \'CATS Standard\', \'Content - Apply for Position\', \'<td>\r\n<p class=\"noteUnsized\">Apply to Position</p>\r\n<br>\r\nYou are applying for the position \\\'<title>\\\'.  Please type in your information below - we will match the provided E-Mail address with our system and update your existing profile if it exists.<br>\r\n<br>\r\n<table>\r\n<tr><td style=\"width:200px;\">First Name:</td><td><input-firstName></td></tr>\r\n<tr><td style=\"width:200px;\">Last Name:</td><td><input-lastName></td></tr>\r\n<tr><td>&nbsp;</td><td>&nbsp;</td></tr>\r\n<tr><td style=\"width:200px;\">E-Mail Address:</td><td><input-email></td></tr>\r\n<tr><td style=\"width:200px;\">Retype E-Mail:</td><td><input-emailconfirm></td></tr>\r\n<tr><td>&nbsp;</td><td>&nbsp;</td></tr>\r\n<tr><td style=\"width:200px;\">Resume Upload:</td><td><input-resumeUpload></td></tr>\r\n<tr><td style=\"width:200px;\">Extra notes concerning this position:</td><td><input-extraNotes></td></tr>\r\n</table>\r\n<br>\r\n<submit value=\"Apply for Position\">\r\n</td>\'),
                        (NULL, \'CATS Standard\', \'Footer\', \'</tr>\r\n</table>\'),
                        (NULL, \'CATS Standard\', \'CSS\', \'body\r\n{\r\npadding: 8px 18px 8px 18px;\r\nmargin: 0px;\r\nfont: normal normal normal 10px Verdana, Tahoma, sans-serif;\r\n}\r\n.inputBoxName\r\n{\r\nwidth:220px;\r\n}\r\n.inputBoxNormal\r\n{\r\nwidth:300px;\r\n}\r\n.inputBoxArea\r\n{\r\nwidth:250px;\r\nheight:100px;\r\n}\r\n.mainLogoText\r\n{\r\ncolor: #2f4f88;\r\nmargin-bottom: 0px;\r\npadding: 0px 5px 0px 5px;\r\nfont: normal normal bold 30px Verdana, Tahoma, sans-serif;\r\n}\r\n.subLogoText\r\n{\r\ncolor: #000000;\r\nmargin-bottom: 0px;\r\npadding: 0px 2px 0px 2px;\r\nfont: normal normal bold 14px Verdana, Tahoma, sans-serif;\r\n}\r\np.mainHeading\r\n{\r\ncolor: #000000;\r\nmargin-bottom: 0px;\r\npadding: 0px 2px 0px 2px;\r\nfont: normal normal bold 16px Verdana, Tahoma, sans-serif;\r\n}\r\np.noteUnsized\r\n{\r\nbackground-image: url(\\\'images/blue_gradient.jpg\\\');\r\nbackground-repeat: repeat-x;\r\npadding: 4px;\r\nmargin-top: 0px;\r\nmargin-bottom: 8px;\r\nborder-top: 1px solid #bbb;\r\nborder-bottom: 1px solid #bbb;\r\nfont: normal normal bold  12px/120% Verdana, Tahoma, sans-serif;\r\ncolor: #f4f4f4;\r\n}\r\np.noteUnsized\r\n{\r\nbackground-image: url(\\\'images/blue_gradient.jpg\\\');\r\nbackground-repeat: repeat-x;\r\npadding: 4px;\r\nmargin-top: 0px;\r\nmargin-bottom: 8px;\r\nborder-top: 1px solid #bbb;\r\nborder-bottom: 1px solid #bbb;\r\nfont: normal normal bold  12px/120% Verdana, Tahoma, sans-serif;\r\ncolor: #f4f4f4;\r\n}\r\ntable.sortable\r\n{\r\nmargin: 5px 0px 5px 0px;\r\nborder-collapse: collapse;\r\nborder: 1px solid #ccc;\r\nfont: normal normal normal  10px Verdana, Tahoma, sans-serif;\r\nempty-cells: show;\r\n}\r\ntr.rowHeading\r\n{\r\ntext-align:center;\r\nbackground-image: url(\\\'images/blue_gradient.jpg\\\');\r\nbackground-repeat: repeat-x;\r\npadding: 4px;\r\nmargin-top: 0px;\r\nmargin-bottom: 8px;\r\nborder-top: 1px solid #bbb;\r\nborder-bottom: 1px solid #bbb;\r\nfont: normal normal bold  10px Verdana, Tahoma, sans-serif;\r\ncolor: #2f4c87;\r\n}\r\ntr.oddTableRow\r\n{\r\nbackground: #fff;\r\nheight:20px;\r\n}\r\ntr.evenTableRow\r\n{\r\nbackground: #f4f4f4;\r\nheight:20px;\r\n}\r\na.sortheader:hover,\r\na.sortheader:link,\r\na.sortheader:visited\r\n{text-decoration: none;}\r\n \'),
                        (NULL, \'CATS Standard\', \'Content - Search Results\', \'<td>\r\n<p class=\"noteUnsized\">Search Results</p>\r\n<br />\r\nWe have found the following <numberOfSearchResults> positions for you:<br />\r\n<searchResultsTable>\r\n</td>\'),
                        (NULL, \'CATS Standard\', \'Content - Job Details\', \'<td>\r\n<p class=\"noteUnsized\">Job Details</p>\r\n<br>\r\n<table>\r\n<tr><td style=\"width:160px; font-weight:bold;\">Title:</td><td><title></td></tr>\r\n<tr><td style=\"width:160px; font-weight:bold;\">Date Created:</td><td><created></td></tr>\r\n<tr><td style=\"width:160px; font-weight:bold;\">Location:</td><td><city>, <state></td></tr>\r\n<tr><td style=\"width:160px; font-weight:bold;\">Openings:</td><td><openings></td></tr>\r\n</table>\r\n<br>\r\n<description><br />\r\n<br />\r\n<br>\r\n<a-applyToJob>Apply to Job</a><br>\r\n</td>\'),
                        (NULL, \'CATS Standard\', \'Content - Thanks for your Submission\', \'<td>\r\n<p class=\"noteUnsized\">Apply to Position</p>\r\n<br>\r\n<br>\r\nThanks for your submission!<br>\r\n<br>\r\nYou should receive an e-mail confirmation of your submission shortly.<br>\r\n<br>\r\n\r\n<a-jobDetails>Go Back to Job Details</a>\r\n</td>\'),
                        (NULL, \'Blank Page\', \'Header\', \'\'),
                        (NULL, \'Blank Page\', \'Left\', \'\'),
                        (NULL, \'Blank Page\', \'Content - Main\', \'\'),
                        (NULL, \'Blank Page\', \'Content - Apply for Position\', null),
                        (NULL, \'Blank Page\', \'Content - Search Results\', \'\'),
                        (NULL, \'Blank Page\', \'Footer\', \'\'),
                        (NULL, \'Blank Page\', \'CSS\', \'\');"
                );
            ',
            '57' => '
                DELETE FROM history WHERE description LIKE \'%to pipeline for%\';
                DELETE FROM history WHERE description = \'(USER) changed rating status.\';
            ',
            '58' => '
                UPDATE candidate_joborder_status SET triggers_email = 0 WHERE candidate_joborder_status_id = 250;
            ',
            '59' => '
                ALTER IGNORE TABLE `attachment` ADD COLUMN `md5_sum` VARCHAR(40) NOT NULL DEFAULT \'\';
                ALTER IGNORE TABLE `attachment` ADD COLUMN `file_size_kb` INT(11) DEFAULT 0;
            ',
            '60' => 'PHP:
                include_once(\'lib/Attachments.php\');
                $rs = $db->getAllAssoc(\'SELECT * FROM attachment\');
                foreach ($rs as $index => $data)
                {
                    $md5sum = @md5_file(\'attachments/\' . $data[\'directory_name\'] . \'/\' . $data[\'stored_filename\']);
                    $fileSize = @filesize(\'attachments/\' . $data[\'directory_name\'] . \'/\' . $data[\'stored_filename\']);
                    $fileSize = (int) $fileSize / 1024;
                    $db->query("UPDATE attachment SET md5_sum = \'" . $md5sum . "\', file_size_kb = " . $fileSize . " WHERE attachment_id = " . $data[\'attachment_id\']);
                }
            ',
            '61' => '
                ALTER IGNORE TABLE `mailer_settings` DROP COLUMN `entered_by`;
                ALTER IGNORE TABLE `job_board_settings` DROP COLUMN `entered_by`;
            ',
            '65' => '
                CREATE TABLE `sph_counter` (
                    counter_id INT PRIMARY KEY NOT NULL,
                    max_doc_id INT NOT NULL
                );
            ',
            '66' => '
                DELETE FROM access_level WHERE access_level_id = 600;
            ',
            '67' => '
                ALTER IGNORE TABLE `user` ADD COLUMN `pipeline_entries_per_page` INT(8) DEFAULT 15;
            ',
            '68' => '
                CREATE INDEX IDX_CANDIDATE_MD5_SUM ON attachment (md5_sum);
            ',
            '69' => '
                ALTER IGNORE TABLE `calendar_event` CHANGE `data_item_id` `data_item_id` INT(11) NOT NULL DEFAULT -1;
                ALTER IGNORE TABLE `calendar_event` CHANGE `data_item_type` `data_item_type` INT(11) NOT NULL DEFAULT -1;
                ALTER IGNORE TABLE `calendar_event` CHANGE `joborder_id` `joborder_id` INT(11) NOT NULL DEFAULT -1;
            ',
            '75' => '
                ALTER TABLE `client` RENAME `company`;
                ALTER TABLE `company` CHANGE `client_id` `company_id` int(11) NOT NULL auto_increment;
                ALTER TABLE `client_foreign` RENAME `company_foreign`;
                ALTER TABLE `client_foreign_settings` RENAME `company_foreign_settings`;
                ALTER TABLE `client_department` RENAME `company_department`;
            ',
            '76' => '
                ALTER TABLE `site` CHANGE `client_id` `company_id` int(11);
                ALTER TABLE `company_department` CHANGE `department_id` `company_department_id` int(11) NOT NULL auto_increment;
            ',
            '77' => '
                ALTER TABLE `joborder` CHANGE `client_id` `company_id` int(11);
                ALTER TABLE `contact` CHANGE `client_id` `company_id` int(11) NOT NULL;
            ',
            '78' => '
                ALTER TABLE `joborder` CHANGE `department_id` `company_department_id` int(11);
            ',
            '79' => '
                ALTER TABLE `company` CHANGE `default_client` `default_company` int(1);
            ',
            '80' => '
                UPDATE company SET default_company = 0 WHERE default_company IS NULL;
                UPDATE contact SET company_id = -1 WHERE company_id IS NULL;
                UPDATE contact SET department_id = -1 WHERE department_id IS NULL;
                ALTER TABLE `contact` CHANGE `company_id` `company_id` int(11) NOT NULL;
                ALTER TABLE `contact` CHANGE `department_id` `company_department_id` int(11) NOT NULL;
                ALTER TABLE `company` CHANGE `default_company` `default_company` int(1) NOT NULL default 0;
            ',
            '81' => '
                UPDATE joborder SET company_department_id = -1 WHERE company_department_id IS NULL;
                ALTER TABLE `company_department` CHANGE `client_id` `company_id` int(11) NOT NULL;
            ',
            '100' => '
                UPDATE dashboard_component SET module_parameters = REPLACE(module_parameters, \'A HREF\', \'a href\') WHERE module_name = \'html\';
                UPDATE dashboard_component SET module_parameters = REPLACE(module_parameters, "<A\nHREF", \'<a href\') WHERE module_name = \'html\';
                UPDATE dashboard_component SET module_parameters = REPLACE(module_parameters, "<A \nHREF", \'<a href\') WHERE module_name = \'html\';
                UPDATE dashboard_component SET module_parameters = REPLACE(module_parameters, "<A\r\nHREF", \'<a href\') WHERE module_name = \'html\';
                UPDATE dashboard_component SET module_parameters = REPLACE(module_parameters, "<A \r\nHREF", \'<a href\') WHERE module_name = \'html\';
                UPDATE dashboard_component SET module_parameters = REPLACE(module_parameters, \'target=newwin3\', \'target=\"_blank\"\') WHERE module_name = \'html\';
                UPDATE dashboard_component SET module_parameters = REPLACE(module_parameters, \'target=newwin2\', \'target=\"_blank\"\') WHERE module_name = \'html\';
                UPDATE dashboard_component SET module_parameters = REPLACE(module_parameters, \'target=newwin1\', \'target=\"_blank\"\') WHERE module_name = \'html\';
                UPDATE dashboard_component SET module_parameters = REPLACE(module_parameters, \'</A>\', \'</a>\') WHERE module_name = \'html\';
                UPDATE dashboard_component SET module_parameters = REPLACE(module_parameters, \'target=_blank\', \'target=\\"\\"_blank\\"\\"\') WHERE module_name = \'html\';
                UPDATE dashboard_component SET module_parameters = REPLACE(module_parameters, \'target=\\"_blank\\"\', \'target=\\"\\"_blank\\"\\"\') WHERE module_name = \'html\';
            ',
            '101' => 'PHP:
                $db->query(\'UPDATE job_board_template SET value = REPLACE(value, "<center>", "<div style=\"text-align:center;\"><table align=\"center\"><tr><td style=\"text-align:left;\">");\');
            ',
            '102' => 'PHP:
                $db->query(\'UPDATE job_board_template SET value = REPLACE(value, "</center>", "</td></tr></table></div>");\');
            ',
            '103' => 'PHP:
                $db->query(\'UPDATE job_board_template_site SET value = REPLACE(value, "<center>", "<div style=\"text-align:center;\"><table align=\"center\"><tr><td style=\"text-align:left;\">");\');
            ',
            '104' => 'PHP:
                $db->query(\'UPDATE job_board_template_site SET value = REPLACE(value, "</center>", "</td></tr></table></div>");\');
            ',
            '105' => '
                INSERT INTO
                    candidate_source
                SELECT
                    NULL,
                    sources_to_add.name,
                    sources_to_add.site_id,
                    NOW()
                FROM
                    (SELECT DISTINCT
                        candidate.source AS name,
                        candidate.site_id AS site_id
                     FROM
                        candidate
                     LEFT JOIN
                        candidate_source AS cs ON
                            cs.name = candidate.source AND
                            cs.site_id = candidate.site_id
                     WHERE
                        (ISNULL(cs.name) OR cs.name = "") AND
                        (!ISNULL(candidate.source) AND candidate.source != "")
                    ) AS sources_to_add;
             ',
             '108' => '
                UPDATE candidate SET candidate.source = "(none)" WHERE candidate.source = "(None)" OR candidate.source = "";
                DELETE FROM candidate_source WHERE candidate_source.name = "(none)";
             ',
             '109' => '
                UPDATE user SET user_name = \'john@mycompany.net\' WHERE user_name = \'john@customsearch.com\';
                UPDATE site SET name = \'MyCompany.NET\' WHERE name LIKE \'Custom Search,%\';
             ',
             /* This update needs hand holding.
             '124' => 'PHP:
                include_once(\'modules/install/scripts/114.php\');
                update_114($db);
             ',*/
             '150' => 'PHP:
                $badFileExtensions = array(\'shtml\', \'php\', \'php5\', \'php4\', \'cgi\', \'pl\', \'py\', \'phps\');
                include_once(\'modules/install/scripts/150.php\');
                update_150($db);
             ',
             '153' => '
                UPDATE dashboard_component SET module_parameters = REPLACE(module_parameters, \'<a href=\\"\\"http://www.cognizo.com\\"\\" target=\\"\\"_blank\\"\\">Cognizo Technologies</a>\', \'\');
             ',
             '154' => '
                UPDATE dashboard_component SET module_parameters = REPLACE(module_parameters, \'<a href=\\"\\"http://www.cognizo.com\\"\\" target=newwin2>Cognizo Technologies</a>\', \'\');
             ',
             '156' => '
                ALTER IGNORE TABLE `candidate_foreign_settings` CHANGE `alien_id` `candidate_foreign_settings_id` INT(11) ;
                ALTER IGNORE TABLE `company_foreign_settings` CHANGE `alien_id` `company_foreign_settings_id` INT(11);
                ALTER IGNORE TABLE `contact_foreign_settings` CHANGE `alien_id` `contact_foreign_settings_id` INT(11);
                ALTER IGNORE TABLE `candidate_foreign` CHANGE `alien_id` `candidate_foreign_id` INT(11);
                ALTER IGNORE TABLE `company_foreign` CHANGE `alien_id` `company_foreign_id` INT(11);
                ALTER IGNORE TABLE `contact_foreign` CHANGE `alien_id` `contact_foreign_id` INT(11);
                ALTER IGNORE TABLE `candidate_foreign` CHANGE `assoc_id` `candidate_id` INT(11);
                ALTER IGNORE TABLE `company_foreign` CHANGE `assoc_id` `company_id` INT(11);
                ALTER IGNORE TABLE `contact_foreign` CHANGE `assoc_id` `contact_id` INT(11);
             ',
             '157' => '
                ALTER IGNORE TABLE `candidate_foreign_settings` CHANGE `candidate_foreign_settings_id` `candidate_foreign_settings_id` INT(11) NOT NULL auto_increment;
                ALTER IGNORE TABLE `company_foreign_settings` CHANGE `company_foreign_settings_id` `company_foreign_settings_id` INT(11) NOT NULL auto_increment;
                ALTER IGNORE TABLE `contact_foreign_settings` CHANGE `contact_foreign_settings_id` `contact_foreign_settings_id` INT(11) NOT NULL auto_increment;
                ALTER IGNORE TABLE `candidate_foreign` CHANGE `candidate_foreign_id` `candidate_foreign_id` INT(11) NOT NULL auto_increment;
                ALTER IGNORE TABLE `company_foreign` CHANGE `company_foreign_id` `company_foreign_id` INT(11) NOT NULL auto_increment;
                ALTER IGNORE TABLE `contact_foreign` CHANGE `contact_foreign_id` `contact_foreign_id` INT(11) NOT NULL auto_increment;
             ',
             '158' => '
                ALTER TABLE `user` RENAME `site_user`;
                ALTER IGNORE TABLE `email_history` CHANGE `user_id` `site_user_id` INT(11);
                ALTER IGNORE TABLE `feedback` CHANGE `user_id` `site_user_id` INT(11);
                ALTER IGNORE TABLE `mru` CHANGE `user_id` `site_user_id` INT(11);
                ALTER IGNORE TABLE `saved_search` CHANGE `user_id` `site_user_id` INT(11);
                ALTER IGNORE TABLE `user_login` CHANGE `user_id` `site_user_id` INT(11);
             ',
             '159' => '
                ALTER IGNORE TABLE `site_user` CHANGE `user_id` `site_user_id` INT(11);
             ',
             '160' => '
                ALTER IGNORE TABLE `site_user` CHANGE `site_user_id` `site_user_id` INT(11) NOT NULL auto_increment;
             ',
             '161' => '
                DELETE FROM mailer_settings WHERE setting="SMTPHost" OR setting="SMTPPort" OR setting="SMTPAuth" OR setting="SMTPUser" OR setting="SMTPPass" OR setting="mode" OR setting="modeConfigurable";
             ',
             '162' => '
                DROP TABLE IF EXISTS `timecard_user`;
             ',
             '163' => '
                ALTER IGNORE TABLE `system` DROP COLUMN `schema_version`;
             ',
             '164' => '
                DROP TABLE IF EXISTS `work_status_type`;
             ',
             '165' => '
                ALTER IGNORE TABLE `system` CHANGE `date_version_checked` `date_version_checked` datetime NOT NULL default \'0000-00-00\';
             ',
             '166' =>
             '
                ALTER TABLE `candidate_foreign` RENAME `extra_field`;
                ALTER IGNORE TABLE `extra_field` ADD COLUMN `data_item_type` INT(11) DEFAULT 0;
                ALTER IGNORE TABLE `extra_field` CHANGE `candidate_id` `data_item_id` INT(11) DEFAULT 0;
                UPDATE `extra_field` SET `data_item_type` = 100;

                INSERT INTO
                    extra_field
                SELECT
                    NULL,
                    company_id,
                    field_name,
                    value,
                    import_id,
                    site_id,
                    200
                FROM
                    company_foreign;

                INSERT INTO
                    extra_field
                SELECT
                    NULL,
                    contact_id,
                    field_name,
                    value,
                    import_id,
                    site_id,
                    300
                FROM
                    contact_foreign;
             ',
             '167' =>
             '
                DROP TABLE `contact_foreign`;
                DROP TABLE `company_foreign`;

                ALTER TABLE `candidate_foreign_settings` RENAME `extra_field_settings`;
                ALTER IGNORE TABLE `extra_field_settings` ADD COLUMN `data_item_type` INT(11) DEFAULT 0;
                UPDATE `extra_field_settings` SET `data_item_type` = 100;
             ',
             '168' =>
             '
                INSERT INTO
                    extra_field_settings
                SELECT
                    NULL,
                    field_name,
                    import_id,
                    site_id,
                    date_created,
                    200
                FROM
                    company_foreign_settings;

                INSERT INTO
                    extra_field_settings
                SELECT
                    NULL,
                    field_name,
                    import_id,
                    site_id,
                    date_created,
                    300
                FROM
                    contact_foreign_settings;

                DROP TABLE `contact_foreign_settings`;
                DROP TABLE `company_foreign_settings`;
             ',
             '169' =>
             '
                ALTER IGNORE TABLE `extra_field` CHANGE `candidate_foreign_id` `extra_field_id` INT(11) NOT NULL auto_increment;
                ALTER IGNORE TABLE `extra_field_settings` CHANGE `candidate_foreign_settings_id` `extra_field_settings_id` INT(11) NOT NULL auto_increment;
             ',
             '170' =>
             '
                UPDATE data_item_type SET short_description = "Company" WHERE data_item_type_id = 200;
             ',
             '172' =>
             '
                ALTER TABLE `job_board_settings` RENAME `career_portal_settings`;
                ALTER TABLE `job_board_template` RENAME `career_portal_template`;
                ALTER TABLE `job_board_template_site` RENAME `career_portal_template_site`;
                ALTER IGNORE TABLE `career_portal_settings` CHANGE `job_board_settings_id` `career_portal_settings_id` INT(11) NOT NULL auto_increment;
                ALTER IGNORE TABLE `career_portal_template` CHANGE `job_board_template_id` `career_portal_template_id` INT(11) NOT NULL auto_increment;
                ALTER IGNORE TABLE `career_portal_template_site` CHANGE `job_board_template_id` `career_portal_template_id` INT(11) NOT NULL auto_increment;
                ALTER IGNORE TABLE `career_portal_template_site` CHANGE `job_board_name` `career_portal_name` VARCHAR(255);
                ALTER IGNORE TABLE `career_portal_template` CHANGE `job_board_name` `career_portal_name` VARCHAR(255);
             ',
             '173' =>
             '
                ALTER TABLE `mailer_settings` RENAME `settings`;
                ALTER IGNORE TABLE `settings` ADD COLUMN `settings_type` INT(11) DEFAULT 0;
                UPDATE `settings` SET `settings_type` = 1;
                INSERT INTO `settings` SELECT NULL, setting, value, site_id, 2 FROM calendar_settings;
                DROP TABLE calendar_settings;
             ',
             '174' =>
             '
                CREATE TABLE `eeo_ethnic_type` (
                  `eeo_ethnic_type_id` int(11) PRIMARY KEY NOT NULL auto_increment,
                  `type` varchar(128) NOT NULL default \'\'
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
                CREATE TABLE `eeo_veteran_type` (
                  `eeo_veteran_type_id` int(11) PRIMARY KEY NOT NULL auto_increment,
                  `type` varchar(128) NOT NULL default \'\'
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
             ',
             '175' =>
             '
                INSERT INTO `eeo_ethnic_type` values
                    (1, "American Indian"),
                    (2, "Asian or Pacific Islander"),
                    (3, "Hispanic or Latino"),
                    (4, "Non-Hispanic Black"),
                    (5, "Non-Hispanic White");
                INSERT INTO `eeo_veteran_type` values
                    (1, "No"),
                    (2, "Eligible Veteran"),
                    (3, "Disabled Veteran"),
                    (4, "Eligible and Disabled");
             ',
             '176' =>
             '
                ALTER IGNORE TABLE `candidate` ADD COLUMN `eeo_ethnic_type_id` INT(11) DEFAULT 0;
                ALTER IGNORE TABLE `candidate` ADD COLUMN `eeo_veteran_type_id` INT(11) DEFAULT 0;
                ALTER IGNORE TABLE `candidate` ADD COLUMN `eeo_disability_status` VARCHAR(5) DEFAULT \'\';
                ALTER IGNORE TABLE `candidate` ADD COLUMN `eeo_gender` VARCHAR(5) DEFAULT \'\';
             ',
             '177' =>
             '
                UPDATE eeo_veteran_type SET type = "No Veteran Status" where eeo_veteran_type_id = 1;
             ',
             '178' =>
             '
                ALTER IGNORE TABLE `attachment` CHANGE `attachment_id` `attachment_id` INT(11) NOT NULL auto_increment;
             ',
             '179' =>
             '
                ALTER IGNORE TABLE `attachment` DROP INDEX IDX_text;
             ',
             '180' =>
             '
                ALTER IGNORE TABLE `attachment` CHANGE `directory_name` `directory_name` VARCHAR(64);
             ',
             '182' => '
                 ALTER IGNORE TABLE `attachment` ADD COLUMN `md5_sum_text` VARCHAR(40) NOT NULL DEFAULT \'\';
             ',
             '185' =>
             '
                ALTER IGNORE TABLE `attachment` DROP INDEX IDX_date_modified;
             ',
             '186' => '
                 ALTER IGNORE TABLE `site_user` ADD COLUMN `column_preferences` text;
             ',
             '190' => '
                ALTER IGNORE TABLE `candidate` CHANGE `address1` `address` text;
                UPDATE candidate SET address = CONCAT(CONCAT(address, \'\n\'), address2) WHERE NOT address2 = \'\';
                ALTER IGNORE TABLE `candidate` DROP COLUMN `address2`;
             ',
             '191' => '
                ALTER IGNORE TABLE `contact` CHANGE `address1` `address` text;
                UPDATE contact SET address = CONCAT(CONCAT(address, \'\n\'), address2) WHERE NOT address2 = \'\';
                ALTER IGNORE TABLE `contact` DROP COLUMN `address2`;

             ',
             '192' => '
                ALTER IGNORE TABLE `company` CHANGE `address1` `address` text;
                UPDATE company SET address = CONCAT(CONCAT(address, \'\n\'), address2) WHERE NOT address2 = \'\';
                ALTER IGNORE TABLE `company` DROP COLUMN `address2`;
                UPDATE company SET address = CONCAT(CONCAT(address, \'\n\'), address3) WHERE NOT address3 = \'\';
                ALTER IGNORE TABLE `company` DROP COLUMN `address3`;
             ',
             '193' => 'PHP:
                $db->query(\'UPDATE career_portal_template SET value = REPLACE(value, "<input-address1>", "<input-address>");\');
                $db->query(\'UPDATE career_portal_template SET value = REPLACE(value, "<input-address2>", "");\');
                $db->query(\'UPDATE career_portal_template_site SET value = REPLACE(value, "<input-address1>", "<input-address>");\');
                $db->query(\'UPDATE career_portal_template_site SET value = REPLACE(value, "<input-address2>", "");\');
             ',
             '194' => '
                ALTER IGNORE TABLE `candidate` ADD COLUMN desired_salary varchar(64);
                ALTER IGNORE TABLE `candidate` ADD COLUMN current_salary varchar(64);
             ',
             '196' => '
                UPDATE access_level SET short_description = "Account Disabled" WHERE access_level_id = 0;
                UPDATE access_level SET short_description = "Read Only" WHERE access_level_id = 100;
                UPDATE access_level SET short_description = "Add / Edit" WHERE access_level_id = 200;
                UPDATE access_level SET short_description = "Add / Edit / Delete" WHERE access_level_id = 300;
                UPDATE access_level SET short_description = "Site Administrator" WHERE access_level_id = 400;
             ',
             '197' => '
                ALTER IGNORE TABLE `hot_list` RENAME `saved_list`;
                ALTER IGNORE TABLE `hot_list_entry` RENAME `saved_list_entry`;
                ALTER IGNORE TABLE `saved_list` CHANGE `hot_list_id` `saved_list_id` int(11) NOT NULL auto_increment;
             ',
             '198' => '
                ALTER IGNORE TABLE `saved_list_entry` CHANGE `hot_list_entry_id` `saved_list_entry_id` int(11) NOT NULL auto_increment;
             ',
             '199' => '
                ALTER IGNORE TABLE `saved_list_entry` CHANGE `hot_list_id` `saved_list_id` int(11) NOT NULL;
             ',
             '200' => '
                ALTER IGNORE TABLE `candidate` ADD COLUMN middle_name varchar(32) AFTER first_name;
             ',

             /* Extra fields refactoring. */
             '201' => '
                ALTER IGNORE TABLE `extra_field_settings` ADD COLUMN `extra_field_type` INT(11) NOT NULL DEFAULT '.EXTRA_FIELD_TEXT.';
             ',
             '202' => '
                UPDATE `extra_field_settings` SET `extra_field_type` = '.EXTRA_FIELD_CHECKBOX.' WHERE field_name LIKE "(CB) %";
             ',
             '203' => '
                UPDATE `extra_field_settings` SET field_name = REPLACE(field_name, \'(CB) \', \'\') WHERE `extra_field_type`  = '.EXTRA_FIELD_CHECKBOX.';
                UPDATE `extra_field` SET field_name = REPLACE(field_name, \'(CB) \', \'\') WHERE field_name LIKE "(CB) %";
             ',
             '204' => '
                ALTER IGNORE TABLE `extra_field_settings` ADD COLUMN `extra_field_parameters` text;
             ',
             '205' => '
                ALTER IGNORE TABLE `extra_field_settings` CHANGE `extra_field_parameters` `extra_field_options` text;
             ',
             /* Add missing indexes to speed up job order queries. */
             '222' => '
                CREATE INDEX IDX_site_id ON candidate_joborder_status_history (site_id);
                CREATE INDEX IDX_status_to ON candidate_joborder_status_history (status_to);
                CREATE INDEX IDX_status_to_site_id ON candidate_joborder_status_history (status_to, site_id);
             ',
             /* Remove table dashboard_module; this information is now stored in a static array. */
             '224' => '
                DROP TABLE IF EXISTS dashboard_module;
             ',
             /* Convert dashboard component parameters to serialize()d arrays. */
             '225' => 'PHP:
                include_once(\'./lib/ListEditor.php\');

                $rs = $db->getAllAssoc(
                    "SELECT
                        dashboard_component_id,
                        module_parameters
                    FROM
                        dashboard_component"
                );

                foreach ($rs as $rowIndex => $row)
                {
                    $array = ListEditor::getArrayVaulesfromCSV(
                        $row[\'module_parameters\']
                    );

                    $serializedValue = serialize($array);

                    $db->query(
                        "UPDATE
                            dashboard_component
                        SET
                            module_parameters = \'" . mysql_real_escape_string($serializedValue) . "\'
                        WHERE
                            dashboard_component_id = " . $row[\'dashboard_component_id\']
                    );
                }
             ',
             '226' => '
                ALTER IGNORE TABLE `candidate` CHANGE `desired_salary` `desired_pay` varchar(64);
                ALTER IGNORE TABLE `candidate` CHANGE `current_salary` `current_pay` varchar(64);
             ',
             '227' => '
                ALTER TABLE `site_user` RENAME `user`;
                ALTER IGNORE TABLE `email_history` CHANGE `site_user_id` `user_id` INT(11);
                ALTER IGNORE TABLE `feedback` CHANGE `site_user_id` `user_id` INT(11);
                ALTER IGNORE TABLE `mru` CHANGE `site_user_id` `user_id` INT(11);
                ALTER IGNORE TABLE `saved_search` CHANGE `site_user_id` `user_id` INT(11);
                ALTER IGNORE TABLE `user_login` CHANGE `site_user_id` `user_id` INT(11);
             ',
             '228' => '
                ALTER IGNORE TABLE `user` CHANGE `site_user_id` `user_id` INT(11);
             ',
             '229' => '
                ALTER IGNORE TABLE `user` CHANGE `user_id` `user_id` INT(11) NOT NULL auto_increment;
             ',
             '230' => '
                ALTER IGNORE TABLE `contact` ADD COLUMN `reports_to` INT(11) default -1;
             ',
             '231' => '
                ALTER IGNORE TABLE `candidate` ADD COLUMN `is_active` INT(1) default 1;
             ',
             '240' => '
                UPDATE activity SET type = 400 WHERE type = 0;
             ',
             '241' => '
                ALTER IGNORE TABLE `user` ADD COLUMN `force_logout` INT(1) default 0;
             ',
             '242' => '
                UPDATE saved_list_entry,candidate SET saved_list_entry.site_id = candidate.site_id WHERE candidate.candidate_id = data_item_id;
             ',
             '243' => '
                 ALTER IGNORE TABLE `candidate` ADD COLUMN `is_admin_hidden` INT(1) default 0;
                 ALTER IGNORE TABLE `joborder` ADD COLUMN `is_admin_hidden` INT(1) default 0;
             ',
             '244' => '
                ALTER IGNORE TABLE `site` ADD COLUMN `is_hr_mode` INT(1) default 0;
             ',
             '247' => 'PHP:
                $db->query("UPDATE career_portal_template SET value = REPLACE(value, \'tr.rowHeading\r\n{\r\ntext-align:center;\r\n\', \'tr.rowHeading\r\n{\r\ntext-align:left;\r\n\') where setting=\'CSS\';");
             ',
             '248' => 'PHP:
                $db->query("UPDATE career_portal_template_site SET value = REPLACE(value, \'tr.rowHeading\r\n{\r\ntext-align:center;\r\n\', \'tr.rowHeading\r\n{\r\ntext-align:left;\r\n\') where setting=\'CSS\';");
             ',
             '249' => '
                DROP TABLE IF EXISTS `http_log`;
                DROP TABLE IF EXISTS `http_log_types`;
                DROP TABLE IF EXISTS `queue`;
                DROP TABLE IF EXISTS `xml_feeds`;
                DROP TABLE IF EXISTS `xml_feed_submits`;
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
                  PRIMARY KEY  (`log_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
                CREATE TABLE `http_log_types` (
                  `log_type_id` int(11) NOT NULL,
                  `name` varchar(16) NOT NULL,
                  `description` varchar(255) default NULL,
                  `default_log_type` tinyint(1) unsigned zerofill NOT NULL default \'0\',
                  PRIMARY KEY  (`log_type_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
                INSERT INTO http_log_types
                VALUES (1, \'XML\', \'XML Job Feed\', 0);
                CREATE TABLE `queue` (
                  `queue_id` int(11) NOT NULL auto_increment,
                  `site_id` int(11) NOT NULL,
                  `task` varchar(125) NOT NULL,
                  `args` text,
                  `priority` tinyint(2) NOT NULL default \'5\' COMMENT \'1-5, 1 is highest priority\',
                  `date_created` datetime NOT NULL,
                  `date_timeout` datetime NOT NULL,
                  `date_completed` datetime default NULL,
                  `locked` tinyint(1) unsigned NOT NULL default \'0\',
                  `error` tinyint(1) unsigned default \'0\',
                  PRIMARY KEY  (`queue_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
                CREATE TABLE `xml_feed_submits` (
                  `feed_id` int(11) NOT NULL auto_increment,
                  `feed_site` varchar(75) NOT NULL,
                  `feed_url` varchar(255) NOT NULL,
                  `date_last_post` date NOT NULL,
                  PRIMARY KEY  (`feed_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
                CREATE TABLE `xml_feeds` (
                  `xml_feed_id` int(11) NOT NULL auto_increment,
                  `name` varchar(50) NOT NULL,
                  `description` varchar(255) default NULL,
                  `website` varchar(255) default NULL,
                  `post_url` varchar(255) NOT NULL,
                  `success_string` varchar(255) NOT NULL,
                  `xml_template_name` varchar(255) NOT NULL,
                PRIMARY KEY  (`xml_feed_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
                INSERT INTO xml_feeds
                VALUES
                   (1, \'Indeed\', \'Indeed.com job search engine.\', \'http://www.indeed.com\',
                   \'http://www.indeed.com/jsp/includejobs.jsp\',
                   \'Thank you for submitting your XML job feed\', \'indeed\'),
                   (2, \'SimplyHired\', \'SimplyHired.com job search engine\', \'http://www.simplyhired.com\',
                   \'http://www.simplyhired.com/confirmation.php\',
                   \'Thanks for Contacting Us\', \'simplyhired\');
            ',
            '250' => '
                ALTER IGNORE TABLE `http_log` ADD COLUMN `date` datetime default \'0000-00-00 00:00:00\';
                CREATE INDEX IDX_CANDIDATE_JOBORDER_STATUS_HISTORY_JOBORDER_ID ON candidate_joborder_status_history (joborder_id);
            ',
            '251' => '
                UPDATE system SET disable_version_check = 1;
            ',
            '253' => 'PHP:
                $rs = $db->query(\'SELECT * FROM zipcodes\');

                if ($rs && mysql_fetch_row($rs))
                {
                    $db->query(\'DELETE FROM zipcodes\');
                    $schemaZipcodes = @file_get_contents(\'db/upgrade-zipcodes.sql\');
                    $db->queryMultiple($schemaZipcodes);
                    CATSUtility::changeConfigSetting(\'US_ZIPS_ENABLED\', "true");
                }
                else
                {
                    CATSUtility::changeConfigSetting(\'US_ZIPS_ENABLED\', "false");
                }
            ',
            '254' => '
                ALTER IGNORE TABLE `saved_list` ADD COLUMN `is_dynamic` INT(1) default 0;
                ALTER IGNORE TABLE `saved_list` ADD COLUMN `datagridInstance` VARCHAR(64) default \'\';
                ALTER IGNORE TABLE `saved_list` ADD COLUMN `parameters` TEXT default \'\';
                ALTER IGNORE TABLE `saved_list` ADD COLUMN `created_by` INT(11) default 0;
            ',
            '255' => '
                ALTER IGNORE TABLE `saved_list` CHANGE `datagridInstance` `datagrid_instance` varchar(64) default \'\';
            ',
            '256' => '
                ALTER IGNORE TABLE `user` ADD COLUMN `title` VARCHAR(64) default \'\';
                ALTER IGNORE TABLE `user` ADD COLUMN `phone_work` VARCHAR(64) default \'\';
                ALTER IGNORE TABLE `user` ADD COLUMN `phone_cell` VARCHAR(64) default \'\';
                ALTER IGNORE TABLE `user` ADD COLUMN `phone_other` VARCHAR(64) default \'\';
            ',
            '257' => '
                ALTER IGNORE TABLE `user` ADD COLUMN `address` TEXT;
            ',
            '258' => '
                ALTER IGNORE TABLE `user` ADD COLUMN `notes` TEXT;
            ',
            '260' => '
                ALTER IGNORE TABLE `site` CHANGE `is_trial` `is_free` INT(1);
            ',
            '261' => '
                ALTER IGNORE TABLE `site` DROP COLUMN `trial_expires`;
            ',
            '263' => '
                UPDATE site SET user_licenses = 2 WHERE is_free = 1;
            ',
            '264' => '
                ALTER IGNORE TABLE `site` ADD COLUMN `file_size_kb` INT(11) default 0;
            ',
            '265' => 'PHP:
                $rs = $db->getAllAssoc(
                    "SELECT
                        site_id as site_id
                    FROM
                        site"
                );

                foreach ($rs as $rowIndex => $row)
                {
                    $sizeRS = $db->getAssoc(
                        "SELECT
                            SUM(file_size_kb) AS size
                         FROM
                            attachment
                         WHERE
                            site_id = ".$row[\'site_id\']);

                    $db->query(
                        "UPDATE
                            site
                         SET
                            file_size_kb = ".($sizeRS[\'size\']*1)
                        ." WHERE
                            site_id = ".$row[\'site_id\']);
                }
            ',
            '266' => '
                ALTER IGNORE TABLE `user` ADD COLUMN `company` varchar(255);
                ALTER IGNORE TABLE `user` ADD COLUMN `city` varchar(255);
                ALTER IGNORE TABLE `user` ADD COLUMN `state` varchar(255);
                ALTER IGNORE TABLE `user` ADD COLUMN `zip_code` varchar(255);
                ALTER IGNORE TABLE `user` ADD COLUMN `country` varchar(255);
            ',
            '267' => '
                ALTER IGNORE TABLE `user` ADD COLUMN `can_see_eeo_info` INT(1) default 0;
            ',
            '268' => '
                ALTER IGNORE TABLE `queue` ADD COLUMN `response` VARCHAR(255) default null;
            ',
            '269' => '
                ALTER IGNORE TABLE `saved_list` ADD COLUMN `number_entries` INT(11) default 0;
            ',
            '270' => 'PHP:
                $rs = $db->getAllAssoc(
                    "SELECT
                        saved_list_id as saved_list_id
                    FROM
                        saved_list"
                );

                foreach ($rs as $rowIndex => $row)
                {
                    $countRS = $db->getAssoc(
                        "SELECT
                            COUNT(saved_list_entry_id) AS numberOfEntries
                         FROM
                            saved_list_entry
                         WHERE
                            saved_list_id = ".$row[\'saved_list_id\']);

                    $db->query(
                        "UPDATE
                            saved_list
                         SET
                            number_entries = ".($countRS[\'numberOfEntries\']*1)
                        ." WHERE
                            saved_list_id = ".$row[\'saved_list_id\']);
                }
            ',
            '271' => '
                ALTER IGNORE TABLE `saved_list` ADD COLUMN `date_created` DATETIME;
                ALTER IGNORE TABLE `saved_list` ADD COLUMN `date_modified` DATETIME;
            ',
            '272' => '
                 ALTER IGNORE TABLE `user` CHANGE `column_preferences` `column_preferences` longtext;
            ',
            '273' => '
                ALTER IGNORE TABLE `saved_list_entry` ADD COLUMN `date_created` DATETIME;
            ',
            '274' => '
                CREATE INDEX `IDX_site_file_size` ON `attachment` (`site_id`,`file_size_kb`);
                CREATE INDEX `IDX_data_item_id_type_site` ON `activity` (`site_id`,`data_item_id`,`data_item_type`);
            ',
            '275' => '
                CREATE INDEX `IDX_site_created` ON `activity` (`site_id`,`date_created`);
            ',
            '276' => '
                CREATE INDEX `IDX_site_first_last_modified` ON `candidate` (`site_id`,`first_name`,`last_name`,`date_modified`);
            ',
            '277' => '
                CREATE INDEX `IDX_account_deleted` ON `site` (`account_deleted`);
                CREATE INDEX `IDX_site_id_date` ON `user_login` (`site_id`,`date`);
                CREATE INDEX `IDX_site_file_size_created` ON `attachment` (`site_id`,`file_size_kb`,`date_created`);
            ',
            '278' => '
                DROP INDEX `IDX_data_item_id` ON `activity`;

                CREATE INDEX `IDX_candidate_joborder_status_to_site` ON `candidate_joborder_status_history` (`candidate_id`,`joborder_id`,`status_to`,`site_id`);
                CREATE INDEX `IDX_activity_site_type_created_job` ON `activity` (`site_id`,`data_item_type`,`date_created`,`entered_by`,`joborder_id`);
                CREATE INDEX `IDX_site_joborder` ON `candidate_joborder` (`site_id`,`joborder_id`);

                DROP INDEX `IDX_joborder_id` ON `candidate_joborder`;
                CREATE INDEX `IDX_joborder_id` ON `candidate_joborder` (`joborder_id`);

                DROP INDEX `IDX_CANDIDATE_JOBORDER_STATUS_HISTORY_JOBORDER_ID` ON `candidate_joborder_status_history`;
                CREATE INDEX `IDX_joborder_site` ON `candidate_joborder_status_history` (`joborder_id`,`site_id`);

                CREATE INDEX `IDX_site_joborder_status_to` ON `candidate_joborder_status_history` (`site_id`,`joborder_id`,`status_to`);
            ',
            '279' => '
                DROP INDEX `IDX_site_id` ON attachment;
            ',
            '280' => '
                DROP INDEX `IDX_data_item_type` ON attachment;
            ',
            '281' => '
                CREATE INDEX `IDX_data_item_id_type_site` ON `history` (`data_item_id`, `data_item_type`, `site_id`);
                CREATE INDEX `IDX_successful_site_id` ON `user_login` (`successful` ,  `site_id`);
                DROP INDEX `IDX_DATA_TYPE` ON `history`;
                DROP INDEX `IDX_DATA_ID` ON `history`;
                DROP INDEX `IDX_site_id` ON `user_login`;
                DROP INDEX `IDX_successful` ON `user_login`;
            ',
            '282' => '
                DROP TABLE IF EXISTS `dashboard_component`;
            ',
            '283' => '
                DROP TABLE IF EXISTS `dashboard_module`;
            ',
            '283' => '
                DROP INDEX `IDX_email1` ON `candidate`;
            ',
            '284' => '
                DROP INDEX `IDX_site_id` ON `candidate`;
            ',
            '285' => '
                CREATE INDEX `IDX_site_id_email_1_2` ON `candidate` (`site_id`,`email1`(8),`email2`(8));
            ',
            '286' => '
                ALTER TABLE  `joborder` CHANGE  `status`  `status` VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'Active\';
            ',
            '287' => '
                CREATE INDEX `IDX_site_id_status` ON `joborder` (`site_id`,`status`(8));
            ',
            '288' => '
                DROP INDEX `IDX_site_id` ON `joborder`;
                DROP INDEX `IDX_status` ON `joborder`;
            ',
            '289' => '
                CREATE INDEX `IDX_site_id_date` ON `calendar_event` (`site_id`,`date`);
            ',
            '290' => '
                CREATE INDEX `IDX_site_data_item_type_id` ON `calendar_event` (`site_id`,`data_item_type`,`data_item_id`);
            ',
            '291' => '
                ALTER IGNORE TABLE `site` ADD COLUMN `pageViews` BIGINT;
                ALTER IGNORE TABLE `site` ADD COLUMN `pageViewDays` INT;
            ',
            '292' => '
                ALTER IGNORE TABLE `site` ADD COLUMN `lastViewedDay` DATE;
            ',
            '293' => '
                 ALTER IGNORE TABLE `site` CHANGE `pageViewDays` `page_view_days` INT;
                 ALTER IGNORE TABLE `site` CHANGE `lastViewedDay` `last_viewed_day` DATE;
            ',
            '294' => '
                ALTER IGNORE TABLE `site` CHANGE `page_view_days` `page_view_days` INT DEFAULT 0;
                ALTER IGNORE TABLE `site` CHANGE `pageViews` `page_views` BIGINT DEFAULT 0;
            ',
            '295' => '
                UPDATE site SET page_views = 0, page_view_days = 0;
            ',
            '296' => '
                UPDATE candidate_joborder_status SET short_description = \'Qualifying\' WHERE short_description = \'Negotiating\';
            ',
            '297' => '
                ALTER IGNORE TABLE `import` ADD COLUMN date_created date;
                UPDATE `import` SET date_created = NOW();
            ',
            '298' => '
                ALTER IGNORE TABLE `import` CHANGE date_created date_created datetime;
                UPDATE `import` SET date_created = NOW();
            ',
            '299' => '
                ALTER IGNORE TABLE `site` ADD COLUMN `first_time_setup` TINYINT DEFAULT 0;
            ',
            '300' => '
                ALTER TABLE  `access_level` ORDER BY  `access_level_id`;
            ',
            '301' => '
                UPDATE site SET first_time_setup = 0;
            ',
            '302' => '
                ALTER TABLE  `email_history` CHANGE  `to_addr`  `to` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'\';
                ALTER TABLE  `email_history` CHANGE  `from_addr`  `from` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'\';
            ',
            '303' => '
                ALTER IGNORE TABLE `settings` CHANGE `mailer_settings_id` `settings_id` INT(11) NOT NULL auto_increment;
            ',
            '304' => '
                ALTER TABLE  `email_history` CHANGE  `to`  `recipients` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'\';
                ALTER TABLE  `email_history` CHANGE  `from`  `from_address` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'\';
            ',
            '305' => '
                ALTER TABLE  `calendar_event_type` CHANGE  `icon_image`  `icon_image` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'\';
            ',
            '306' => '
                ALTER TABLE `joborder` ADD COLUMN `openings_avaliable` INT DEFAULT 0;
                UPDATE `joborder` SET openings_avaliable = openings;
            ',
            '307' => 'PHP:
                $db->query("ALTER IGNORE TABLE `site` ADD COLUMN `localization_configured` int(1) DEFAULT 0", true);
            ',
            '308' => 'PHP:
                $db->query("ALTER IGNORE TABLE `site` ADD COLUMN `agreed_to_license` int(1) DEFAULT 0", true);
                $db->query("UPDATE `site` SET `agreed_to_license` = 1", true);
            ',
            '309' => '
                ALTER TABLE `calendar_event` CHANGE `reminder_email` `reminder_email` TEXT DEFAULT \'\';
            ',
            '310' => '
                UPDATE `history` SET `data_item_type` = 800 WHERE `the_field` = \'PIPELINE\' AND `data_item_type` = 500;
            ',
            '311' => '
                UPDATE
                    `history`
                SET
                    `description` = REPLACE(
                        `description`,
                        \'for job order\',
                        \'for job order \'
                    )
                WHERE
                    `the_field` = \'PIPELINE\'
                    AND `data_item_type` = 800
                    AND `description` LIKE \'(USER) changed pipeline status of candidate%\';
            ',
            '320' => '
                INSERT INTO `settings` SELECT NULL, `setting`, `value`, `site_id`, 4 FROM `career_portal_settings`;
                DROP TABLE IF EXISTS `career_portal_settings`;
            ',
            '321' => '
                ALTER TABLE `joborder` CHANGE `openings_avaliable` `openings_available` INT DEFAULT 0;
            ',
            '322' => '
                ALTER TABLE  `user` CHANGE  `city`  `city` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
                                    CHANGE  `state`  `state` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
                                    CHANGE  `zip_code`  `zip_code` VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
                                    CHANGE  `country`  `country` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL
            ',
            '323' => '
                DROP TABLE IF EXISTS `joborder_status`;
            ',
            '324' => '
                DROP TABLE IF EXISTS `career_portal_questionnaire`;
                DROP TABLE IF EXISTS `career_portal_questionnaire_question`;
                DROP TABLE IF EXISTS `career_portal_questionnaire_answer`;
                CREATE TABLE `career_portal_questionnaire` (
                  `career_portal_questionnaire_id` int(11) NOT NULL auto_increment,
                  `title` varchar(255) NOT NULL default \'\',
                  PRIMARY KEY  (`career_portal_questionnaire_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
                CREATE TABLE `career_portal_questionnaire_question` (
                  `career_portal_questionnaire_question_id` int(11) NOT NULL auto_increment,
                  `career_portal_questionnaire_id` int(11) NOT NULL,
                  `text` varchar(255) NOT NULL default \'\',
                  `minimum_length` int(11) default NULL,
                  `maximum_length` int(11) default NULL,
                  `required` tinyint(1) NOT NULL default 0,
                  PRIMARY KEY  (`career_portal_questionnaire_question_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
                CREATE TABLE `career_portal_questionnaire_answer` (
                  `career_portal_questionnaire_answer_id` int(11) NOT NULL auto_increment,
                  `career_portal_questionnaire_question_id` int(11) NOT NULL,
                  `career_portal_questionnaire_id` int(11) NOT NULL,
                  `text` varchar(255) NOT NULL default \'\',
                  `action_source` varchar(128) default NULL,
                  `action_notes` text default NULL,
                  `action_is_hot` tinyint(1) default 0,
                  `action_is_active` tinyint(1) default 0,
                  `action_can_relocate` tinyint(1) default 0,
                  `action_key_skills` varchar(255) default NULL,
                  PRIMARY KEY  (`career_portal_questionnaire_answer_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
            ',
            '325' => '
                ALTER TABLE `career_portal_questionnaire_question` ADD COLUMN `position` int(4) NOT NULL default 0;
                ALTER TABLE `career_portal_questionnaire_answer` ADD COLUMN `position` int(4) NOT NULL default 0;
            ',
            '326' => '
                ALTER TABLE `career_portal_questionnaire_question` ADD COLUMN `site_id` int(11) NOT NULL default 0;
                ALTER TABLE `career_portal_questionnaire_answer` ADD COLUMN `site_id` int(11) NOT NULL default 0;
                ALTER TABLE `career_portal_questionnaire` ADD COLUMN `site_id` int(11) NOT NULL default 0;
            ',
            '327' => '
                ALTER TABLE `career_portal_questionnaire_question` ADD COLUMN `type` int(11) NOT NULL default 0;
            ',
            '328' => '
                ALTER TABLE `career_portal_questionnaire` ADD COLUMN `description` varchar(255) default NULL;
                ALTER TABLE `career_portal_questionnaire` ADD COLUMN `is_active` TINYINT(1) NOT NULL default 1;
            ',
            '329' => '
                DROP TABLE IF EXISTS `career_portal_questionnaire_history`;
                CREATE TABLE `career_portal_questionnaire_history` (
                  `career_portal_questionnaire_history_id` int(11) NOT NULL auto_increment,
                  `site_id` int(11) NOT NULL default 0,
                  `candidate_id` int(11) NOT NULL default 0,
                  `career_portal_questionnaire_id` int(11) NOT NULL default 0,
                  `question` varchar(255) NOT NULL default \'\',
                  `answer` varchar(255) NOT NULL default \'\',
                  PRIMARY KEY  (`career_portal_questionnaire_history_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
            ',
            '330' => '
                ALTER TABLE `career_portal_questionnaire_history` DROP COLUMN `career_portal_questionnaire_id`;
                ALTER TABLE `career_portal_questionnaire_history` ADD COLUMN `questionnaire_title` VARCHAR(255) NOT NULL default \'\';
                ALTER TABLE `career_portal_questionnaire_history` ADD COLUMN `questionnaire_description` VARCHAR(255) NOT NULL default \'\';
                ALTER TABLE `career_portal_questionnaire_history` ADD COLUMN `date` datetime NOT NULL default \'0000-00-00 00:00:00\';
            ',
            '333' => '
                UPDATE settings SET value = "CATS 2.0" WHERE value="CATS Standard" AND setting="activeBoard";
            ',
            '334' => '
                ALTER TABLE `system` CHANGE `avaliable_version` `available_version` INT DEFAULT 0;
            ',
            '335' => '
                ALTER TABLE `system` CHANGE `avaliable_version_description` `available_version_description` TEXT default \'\';
            ',
            '336' => '
                ALTER TABLE `extra_field_settings` ADD COLUMN `position` int(4) NOT NULL default 0;
                UPDATE extra_field_settings SET position = extra_field_settings_id;
            ',
            '341' => 'PHP:
                $lists = $db->getAllAssoc("SELECT * FROM saved_list");
                foreach($lists as $list)
                {
                    $db->query(sprintf("UPDATE saved_list SET description = \"%s\" WHERE saved_list_id = %s", mysql_real_escape_string(urldecode($list[\'description\'])), $list[\'saved_list_id\']));
                }
            ',
            '343' => '
                ALTER TABLE site ADD COLUMN `limit_warning` TINYINT(1) NOT NULL default 0;
            ',
            '347' => '
                ALTER TABLE `candidate` ADD COLUMN `best_time_to_call` varchar(255) NOT NULL default \'\';
            ',
            '348' => '
                DELETE FROM saved_list_entry
                WHERE data_item_type = 100
                AND data_item_id NOT IN (SELECT candidate_id FROM candidate);

                DELETE FROM saved_list_entry
                WHERE data_item_type = 200
                AND data_item_id NOT IN (SELECT company_id FROM company);

                DELETE FROM saved_list_entry
                WHERE data_item_type = 300
                AND data_item_id NOT IN (SELECT contact_id FROM contact);

                DELETE FROM saved_list_entry
                WHERE data_item_type = 400
                AND data_item_id NOT IN (SELECT joborder_id FROM joborder);
            ',
            /* Regenerate count of all saved lists */
            '349' => 'PHP:
                $rs = $db->getAllAssoc(
                    "SELECT
                        saved_list_id as saved_list_id
                    FROM
                        saved_list"
                );

                foreach ($rs as $rowIndex => $row)
                {
                    $countRS = $db->getAssoc(
                        "SELECT
                            COUNT(saved_list_entry_id) AS numberOfEntries
                         FROM
                            saved_list_entry
                         WHERE
                            saved_list_id = ".$row[\'saved_list_id\']);

                    $db->query(
                        "UPDATE
                            saved_list
                         SET
                            number_entries = ".($countRS[\'numberOfEntries\']*1)
                        ." WHERE
                            saved_list_id = ".$row[\'saved_list_id\']);
                }
            ',
            '357' => '
                ALTER IGNORE TABLE `joborder` ADD COLUMN `questionnaire_id` int(11) default NULL;
            ',
            '360' => '
                UPDATE extra_field_settings SET position = extra_field_settings_id WHERE position = 0;
            ',
            '362' => 'PHP:
                    $rs = $db->getAllAssoc(
                    "SELECT
                        joborder_id
                    FROM
                        joborder"
                );

                foreach ($rs as $rowIndex => $row)
                {
                    $jobOrderData = $db->getAssoc(
                        "SELECT
                            *
                         FROM
                            joborder
                         WHERE
                            joborder_id = ".$row[\'joborder_id\']);

                    $db->query(
                        "UPDATE
                            joborder
                         SET
                            description = ".($db->makeQueryString(nl2br(htmlspecialchars($jobOrderData[\'description\'])))).",
                            notes = ".($db->makeQueryString(nl2br(htmlspecialchars($jobOrderData[\'notes\']))))."
                         WHERE
                            joborder_id = " . $row[\'joborder_id\']);
                }
            ',
            '363' => 'PHP:
                $schemaNewCareerPortal = @file_get_contents(\'modules/install/scripts/359.sql\');
                $db->queryMultiple($schemaNewCareerPortal, ";\n");
            ',
            '364' => '
                UPDATE user SET password = md5(password) WHERE can_change_password=1;
            ',

        );
    }
}

?>
