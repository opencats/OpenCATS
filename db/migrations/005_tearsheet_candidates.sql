-- ============================================================
-- Migration 005: Add Tearsheet Candidate Association Table
--
-- This migration adds support for associating candidates with
-- tearsheets, similar to job order associations.
--
-- Date: 2026-01-25
-- ============================================================

-- ============================================================
-- 1. TEARSHEET_CANDIDATE TABLE
-- Many-to-many relationship between tearsheets and candidates
-- ============================================================

CREATE TABLE IF NOT EXISTS tearsheet_candidate (
    tearsheet_candidate_id INT(11) NOT NULL AUTO_INCREMENT,
    tearsheet_id INT(11) NOT NULL,
    candidate_id INT(11) NOT NULL,
    date_added DATETIME NOT NULL,
    added_by INT(11) DEFAULT NULL COMMENT 'User who added this candidate',
    notes TEXT DEFAULT NULL COMMENT 'Optional notes for this candidate in this tearsheet',

    PRIMARY KEY (tearsheet_candidate_id),
    UNIQUE KEY idx_tearsheet_candidate (tearsheet_id, candidate_id),
    KEY idx_candidate_id (candidate_id),
    KEY idx_date_added (date_added)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
  COMMENT='Candidates contained in tearsheets';

-- ============================================================
-- END OF MIGRATION 005
-- ============================================================
