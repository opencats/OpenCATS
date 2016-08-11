
INSERT INTO `user`
(`user_id`, `site_id`, `user_name`, `email`, `password`, `access_level`, `can_change_password`, `is_test_user`, `last_name`, `first_name`, `is_demo`, `categories`, `session_cookie`, `pipeline_entries_per_page`, `column_preferences`, `force_logout`, `title`, `phone_work`, `phone_cell`, `phone_other`, `address`, `notes`, `company`, `city`, `state`, `zip_code`, `country`, `can_see_eeo_info`)
VALUES
(2001, 1, 'testerDisabled', 'noreply@noreply.com', 'f5d1278e8109edd94e1e4197e04873b9', 0, 1, 0, 'Disabled', 'Tester', 0, NULL, NULL, 15, NULL, 0, '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(2002, 1, 'testerRead', 'noreply@noreply.com', 'f5d1278e8109edd94e1e4197e04873b9', 100, 1, 0, 'Readonly', 'Tester', 0, NULL, NULL, 15, NULL, 0, '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(2003, 1, 'testerEdit', 'noreply@noreply.com', 'f5d1278e8109edd94e1e4197e04873b9', 200, 1, 0, 'Edit', 'Tester', 0, NULL, NULL, 15, NULL, 0, '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(2004, 1, 'testerDelete', 'noreply@noreply.com', 'f5d1278e8109edd94e1e4197e04873b9', 300, 1, 0, 'Delete', 'Tester', 0, NULL, NULL, 15, NULL, 0, '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(2005, 1, 'testerDemo', 'noreply@noreply.com', 'f5d1278e8109edd94e1e4197e04873b9', 350, 1, 0, 'Demo', 'Tester', 0, NULL, NULL, 15, NULL, 0, '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(2006, 1, 'testerSA', 'noreply@noreply.com', 'f5d1278e8109edd94e1e4197e04873b9', 400, 1, 0, 'SiteAdmin', 'Tester', 0, NULL, NULL, 15, NULL, 0, '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(2007, 1, 'testerMultiSA', 'noreply@noreply.com', 'f5d1278e8109edd94e1e4197e04873b9', 450, 1, 0, 'MultiSiteAdmin', 'Tester', 0, NULL, NULL, 15, NULL, 0, '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(2008, 1, 'testerRoot', 'noreply@noreply.com', 'f5d1278e8109edd94e1e4197e04873b9', 500, 1, 0, 'Root', 'Tester', 0, NULL, NULL, 15, NULL, 0, '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);

INSERT INTO `contact`
(`contact_id`, `company_id`, `site_id`, `last_name`, `first_name`, `title`, `email1`, `email2`, `phone_work`, `phone_cell`, `phone_other`, `address`, `city`, `state`, `zip`, `is_hot`, `notes`, `entered_by`, `owner`, `date_created`, `date_modified`, `left_company`, `import_id`, `company_department_id`, `reports_to`)
VALUES
(1, 2, 1, 'Blue', 'Elizabeth', 'Google HR', 'elizabeth@blue.com', '', '', '', '', '', '', '', '', 0, '', 1, 1, '2016-01-01 14:47:47', '2016-01-01 15:04:48', 0, 0, 0, 0);

INSERT INTO `company`
(`company_id`, `site_id`, `billing_contact`, `name`, `address`, `city`, `state`, `zip`, `phone1`, `phone2`, `url`, `key_technologies`, `notes`, `entered_by`, `owner`, `date_created`, `date_modified`, `is_hot`, `fax_number`, `import_id`, `default_company`)
VALUES
(1, 1, NULL, 'Internal Postings', '', '', '', '', '', '', '', '', '', 0, 0, '2009-11-19 10:00:20', '2009-11-19 10:00:20', 0, '', NULL, 1),
(2, 1, NULL, 'Google', '', '', '', '', '', '', '', '', '', 1, 1, '2016-01-01 14:47:24', '2016-01-01 14:47:24', 0, '', NULL, 0);

INSERT INTO `candidate`
(`candidate_id`, `site_id`, `last_name`, `first_name`, `middle_name`, `phone_home`, `phone_cell`, `phone_work`, `address`, `city`, `state`, `zip`, `source`, `date_available`, `can_relocate`, `notes`, `key_skills`, `current_employer`, `entered_by`, `owner`, `date_created`, `date_modified`, `email1`, `email2`, `web_site`, `import_id`, `is_hot`, `eeo_ethnic_type_id`, `eeo_veteran_type_id`, `eeo_disability_status`, `eeo_gender`, `desired_pay`, `current_pay`, `is_active`, `is_admin_hidden`, `best_time_to_call`)
VALUES
(2, 1, 'Tuk', 'Pippin', '', '', '', '', '', '', '', '', '(none)', NULL, 0, '', '', '', 1, 1, '2016-01-01 14:48:32', '2016-01-01 14:48:57', '', '', '', 0, 0, 0, 0, '', '', '', '', 1, 0, '');

INSERT INTO `joborder`
(`joborder_id`, `recruiter`, `contact_id`, `company_id`, `entered_by`, `owner`, `site_id`, `client_job_id`, `title`, `description`, `notes`, `type`, `duration`, `rate_max`, `salary`, `status`, `is_hot`, `openings`, `city`, `state`, `start_date`, `date_created`, `date_modified`, `public`, `company_department_id`, `is_admin_hidden`, `openings_available`, `questionnaire_id`)
VALUES
(1, 1, 1, 2, 1, 1, 1, '', 'OpenCATS Tester', '', '', 'H', '', '', '', 'Active', 0, 1, 'London', 'United Kingdom', NULL, '2016-01-01 14:48:21', '2016-01-01 15:03:11', 0, 0, 0, 1, NULL);

INSERT INTO `candidate_joborder`
(`candidate_joborder_id`, `candidate_id`, `joborder_id`, `site_id`, `status`, `date_submitted`, `date_created`, `date_modified`, `rating_value`, `added_by`)
VALUES
(1, 2, 1, 1, 100, NULL, '2016-01-01 14:48:57', '2016-01-01 14:48:57', NULL, 1);

INSERT INTO `saved_list`
(`saved_list_id`, `description`, `data_item_type`, `site_id`, `is_dynamic`, `datagrid_instance`, `parameters`, `created_by`, `number_entries`, `date_created`, `date_modified`)
VALUES
(1, 'UK Candidates', 100, 1, 0, '', NULL, 1, 1, '2016-01-01 14:51:46', '2016-01-01 14:51:51');

INSERT INTO `saved_list_entry`
(`saved_list_entry_id`, `saved_list_id`, `data_item_type`, `data_item_id`, `site_id`, `date_created`)
VALUES
(1, 1, 100, 2, 1, '2016-01-01 14:51:51');

INSERT INTO `activity`
(`activity_id`, `data_item_id`, `data_item_type`, `joborder_id`, `site_id`, `entered_by`, `date_created`, `type`, `notes`, `date_modified`)
VALUES
(1, 2, 100, 1, 1, 1, '2016-01-01 14:48:57', 400, 'Added candidate to pipeline.', '2016-01-01 14:48:57'),
(2, 1, 300, -1, 1, 1, '2016-01-01 15:04:48', 100, '', '2016-01-01 15:04:48');

INSERT INTO `attachment`
(`attachment_id`, `data_item_id`, `data_item_type`, `site_id`, `title`, `original_filename`, `stored_filename`, `content_type`, `resume`, `text`, `date_created`, `date_modified`, `profile_image`, `directory_name`, `md5_sum`, `file_size_kb`, `md5_sum_text`)
VALUES
(1, 2, 100, 1, 'attachment', 'attachment.txt', 'attachment.txt', 'text/plain', 1, 'This is an attachment.', '2016-01-01 14:56:38', '2016-01-01 14:56:38', 0, 'site_1/0xxx/c24480286cfed53c62d3ad5fd2141d76/', 'fd909012e17ce5fd09ce494b0d04071b', 0, 'fd909012e17ce5fd09ce494b0d04071b'),
(2, 1, 400, 1, 'attachment', 'attachment.txt', 'attachment.txt', 'text/plain', 0, NULL, '2016-01-01 14:58:29', '2016-01-01 14:58:29', 0, 'site_1/0xxx/1c19d9b34efca9525d403eb881767078/', 'fd909012e17ce5fd09ce494b0d04071b', 0, ''),
(3, 2, 200, 1, 'attachment', 'attachment.txt', 'attachment.txt', 'text/plain', 0, NULL, '2016-01-01 14:59:39', '2016-01-01 14:59:39', 0, 'site_1/0xxx/0b57e9fe291046263fbf96c66315097e/', 'fd909012e17ce5fd09ce494b0d04071b', 0, '');

INSERT INTO `history`
(`history_id`, `data_item_type`, `data_item_id`, `the_field`, `previous_value`, `new_value`, `description`, `set_date`, `entered_by`, `site_id`)
VALUES
(1, 200, 1, '!newEntry!', NULL, NULL, '(USER) created entry.', '2009-11-19 10:00:20', 1, 1),
(2, 200, 1, 'defaultCompany', NULL, '1', '(USER) changed field(s): defaultCompany.', '2009-11-19 10:00:20', 1, 1),
(3, 100, 1, '!newEntry!', NULL, NULL, '(USER) created entry.', '2009-11-19 10:24:54', 1, 1),
(4, 100, 1, '(DELETED)', NULL, NULL, '(USER) deleted entry.', '2009-11-20 14:29:36', 1, 1),
(5, 200, 2, '!newEntry!', NULL, NULL, '(USER) created entry.', '2016-01-01 14:47:24', 1, 1),
(6, 300, 1, '!newEntry!', NULL, NULL, '(USER) created entry.', '2016-01-01 14:47:47', 1, 1),
(7, 400, 1, '!newEntry!', NULL, NULL, '(USER) created entry.', '2016-01-01 14:48:21', 1, 1),
(8, 100, 2, '!newEntry!', NULL, NULL, '(USER) created entry.', '2016-01-01 14:48:32', 1, 1),
(9, 100, 2, 'ACTIVITY', '(NEW)', 'Added candidate to pipeline.', '(USER) Added activity.', '2016-01-01 14:48:57', 1, 1),
(10, 300, 1, 'email1', NULL, 'elizabeth@blue.com', NULL, '2016-01-01 15:01:26', 1, 1),
(11, 300, 1, 'reportsTo', '-1', NULL, '(USER) changed field(s): email1, reportsTo.', '2016-01-01 15:01:26', 1, 1),
(12, 400, 1, 'contactID', '-1', '1', NULL, '2016-01-01 15:03:11', 1, 1),
(13, 400, 1, 'contactFullName', NULL, 'Elizabeth Blue', NULL, '2016-01-01 15:03:11', 1, 1),
(14, 400, 1, 'contactEmail', NULL, 'elizabeth@blue.com', '(USER) changed field(s): contactID, contactFullName, contactEmail.', '2016-01-01 15:03:11', 1, 1),
(15, 300, 1, 'ACTIVITY', '(NEW)', NULL, '(USER) Added activity.', '2016-01-01 15:04:48', 1, 1);

INSERT INTO `mru`
(`mru_id`, `user_id`, `site_id`, `data_item_type`, `data_item_text`, `url`, `date_created`)
VALUES
(212, 1, 1, 200, 'Google', 'index.php?m=companies&amp;a=show&amp;companyID=2', '2016-01-01 14:59:41'),
(323, 1, 1, 300, 'Elizabeth Blue', 'index.php?m=contacts&amp;a=show&amp;contactID=1', '2016-01-01 15:04:49'),
(286, 1, 1, 400, 'OpenCATS Tester', 'index.php?m=joborders&amp;a=show&amp;jobOrderID=1', '2016-01-01 15:03:11'),
(148, 1, 1, 100, 'Pippin Tuk', 'index.php?m=candidates&amp;a=show&amp;candidateID=2', '2016-01-01 14:56:39'),
(324, 2002, 1, 400, 'OpenCATS Tester', 'index.php?m=joborders&amp;a=show&amp;jobOrderID=1', '2016-01-01 15:05:00'),
(325, 2003, 1, 400, 'OpenCATS Tester', 'index.php?m=joborders&amp;a=show&amp;jobOrderID=1', '2016-01-01 15:05:02'),
(326, 2004, 1, 400, 'OpenCATS Tester', 'index.php?m=joborders&amp;a=show&amp;jobOrderID=1', '2016-01-01 15:05:05'),
(327, 2005, 1, 400, 'OpenCATS Tester', 'index.php?m=joborders&amp;a=show&amp;jobOrderID=1', '2016-01-01 15:05:08'),
(328, 2006, 1, 400, 'OpenCATS Tester', 'index.php?m=joborders&amp;a=show&amp;jobOrderID=1', '2016-01-01 15:05:10'),
(329, 2007, 1, 400, 'OpenCATS Tester', 'index.php?m=joborders&amp;a=show&amp;jobOrderID=1', '2016-01-01 15:05:13'),
(330, 2008, 1, 400, 'OpenCATS Tester', 'index.php?m=joborders&amp;a=show&amp;jobOrderID=1', '2016-01-01 15:05:16'),
(331, 2002, 1, 100, 'Pippin Tuk', 'index.php?m=candidates&amp;a=show&amp;candidateID=2', '2016-01-01 15:05:18'),
(332, 2003, 1, 100, 'Pippin Tuk', 'index.php?m=candidates&amp;a=show&amp;candidateID=2', '2016-01-01 15:05:21'),
(333, 2004, 1, 100, 'Pippin Tuk', 'index.php?m=candidates&amp;a=show&amp;candidateID=2', '2016-01-01 15:05:24'),
(334, 2005, 1, 100, 'Pippin Tuk', 'index.php?m=candidates&amp;a=show&amp;candidateID=2', '2016-01-01 15:05:26'),
(335, 2006, 1, 100, 'Pippin Tuk', 'index.php?m=candidates&amp;a=show&amp;candidateID=2', '2016-01-01 15:05:29'),
(336, 2007, 1, 100, 'Pippin Tuk', 'index.php?m=candidates&amp;a=show&amp;candidateID=2', '2016-01-01 15:05:32'),
(337, 2008, 1, 100, 'Pippin Tuk', 'index.php?m=candidates&amp;a=show&amp;candidateID=2', '2016-01-01 15:05:34'),
(338, 2002, 1, 200, 'Google', 'index.php?m=companies&amp;a=show&amp;companyID=2', '2016-01-01 15:05:38'),
(339, 2003, 1, 200, 'Google', 'index.php?m=companies&amp;a=show&amp;companyID=2', '2016-01-01 15:05:40'),
(340, 2004, 1, 200, 'Google', 'index.php?m=companies&amp;a=show&amp;companyID=2', '2016-01-01 15:05:42'),
(341, 2005, 1, 200, 'Google', 'index.php?m=companies&amp;a=show&amp;companyID=2', '2016-01-01 15:05:44'),
(342, 2006, 1, 200, 'Google', 'index.php?m=companies&amp;a=show&amp;companyID=2', '2016-01-01 15:05:46'),
(343, 2007, 1, 200, 'Google', 'index.php?m=companies&amp;a=show&amp;companyID=2', '2016-01-01 15:05:48'),
(344, 2008, 1, 200, 'Google', 'index.php?m=companies&amp;a=show&amp;companyID=2', '2016-01-01 15:05:51'),
(345, 2002, 1, 300, 'Elizabeth Blue', 'index.php?m=contacts&amp;a=show&amp;contactID=1', '2016-01-01 15:05:54'),
(346, 2003, 1, 300, 'Elizabeth Blue', 'index.php?m=contacts&amp;a=show&amp;contactID=1', '2016-01-01 15:05:55'),
(347, 2004, 1, 300, 'Elizabeth Blue', 'index.php?m=contacts&amp;a=show&amp;contactID=1', '2016-01-01 15:05:57'),
(348, 2005, 1, 300, 'Elizabeth Blue', 'index.php?m=contacts&amp;a=show&amp;contactID=1', '2016-01-01 15:05:59'),
(349, 2006, 1, 300, 'Elizabeth Blue', 'index.php?m=contacts&amp;a=show&amp;contactID=1', '2016-01-01 15:06:01'),
(350, 2007, 1, 300, 'Elizabeth Blue', 'index.php?m=contacts&amp;a=show&amp;contactID=1', '2016-01-01 15:06:03'),
(351, 2008, 1, 300, 'Elizabeth Blue', 'index.php?m=contacts&amp;a=show&amp;contactID=1', '2016-01-01 15:06:05'),
(352, 2002, 1, 700, 'UK Candidates', 'index.php?m=lists&amp;a=showList&amp;savedListID=1', '2016-01-01 15:06:08'),
(353, 2003, 1, 700, 'UK Candidates', 'index.php?m=lists&amp;a=showList&amp;savedListID=1', '2016-01-01 15:06:09'),
(354, 2004, 1, 700, 'UK Candidates', 'index.php?m=lists&amp;a=showList&amp;savedListID=1', '2016-01-01 15:06:11'),
(355, 2005, 1, 700, 'UK Candidates', 'index.php?m=lists&amp;a=showList&amp;savedListID=1', '2016-01-01 15:06:13'),
(356, 2006, 1, 700, 'UK Candidates', 'index.php?m=lists&amp;a=showList&amp;savedListID=1', '2016-01-01 15:06:15'),
(357, 2007, 1, 700, 'UK Candidates', 'index.php?m=lists&amp;a=showList&amp;savedListID=1', '2016-01-01 15:06:17'),
(358, 2008, 1, 700, 'UK Candidates', 'index.php?m=lists&amp;a=showList&amp;savedListID=1', '2016-01-01 15:06:19');
