CREATE TABLE `candidate_duplicates` (
  `old_candidate_id` int(11) NOT NULL,
  `new_candidate_id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,    
  PRIMARY KEY (`old_candidate_id`, `new_candidate_id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
