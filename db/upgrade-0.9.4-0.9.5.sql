/* Upgrade file for DB schema update from version 0.9.4 to 0.9.5 */
/* new column in joborder table for import */
ALTER TABLE `joborder`
ADD COLUMN `import_id` int(11) NOT NULL DEFAULT '0' AFTER `questionnaire_id`;
/* new table for candidate de-duplication */
CREATE TABLE `candidate_duplicates` (
  `old_candidate_id` int(11) NOT NULL,
  `new_candidate_id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  PRIMARY KEY (`old_candidate_id`, `new_candidate_id`),
  KEY `IDX_old_candidate_id` (`old_candidate_id`),
  KEY `IDX_new_candidate_id` (`new_candidate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;