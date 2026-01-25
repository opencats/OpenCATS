-- ============================================================
-- OpenCATS Database Migration
-- Feature: JobSubmission Enhancement + Placement Tables
-- Version: 1.0.0
-- Date: 2026-01-25
--
-- Run this migration with:
-- mysql -u opencats -p opencats < 003_job_submission_placement.sql
--
-- This migration adds Bullhorn-compatible fields to the existing
-- candidate_joborder table (JobSubmission equivalent) and creates
-- new placement tables for tracking hired candidates.
--
-- NOTE: Uses InnoDB engine for new tables with foreign key support.
-- Uses utf8mb4 charset for full Unicode support.
-- ============================================================

-- ============================================================
-- 1. ENHANCE CANDIDATE_JOBORDER TABLE (JobSubmission equivalent)
-- Add Bullhorn-compatible status workflow and tracking fields
-- ============================================================

-- Add bullhorn_status for Bullhorn API compatibility
-- (keeps existing status INT field for legacy OpenCATS workflows)
ALTER TABLE candidate_joborder
    ADD COLUMN IF NOT EXISTS bullhorn_status VARCHAR(50) DEFAULT 'Submitted'
    COMMENT 'Bullhorn-compatible status: Submitted, Reviewed, Interview, Offer, Placed, Rejected, Withdrawn'
    AFTER status;

-- Add date tracking for workflow stages
ALTER TABLE candidate_joborder
    ADD COLUMN IF NOT EXISTS date_interview DATETIME DEFAULT NULL
    COMMENT 'Date of first interview'
    AFTER date_submitted;

ALTER TABLE candidate_joborder
    ADD COLUMN IF NOT EXISTS date_offer DATETIME DEFAULT NULL
    COMMENT 'Date offer was made'
    AFTER date_interview;

-- Add source tracking
ALTER TABLE candidate_joborder
    ADD COLUMN IF NOT EXISTS source VARCHAR(100) DEFAULT NULL
    COMMENT 'Source of the submission (e.g., Job Board, Referral, Direct)'
    AFTER date_offer;

-- Add send_to_client flag
ALTER TABLE candidate_joborder
    ADD COLUMN IF NOT EXISTS send_to_client TINYINT(1) DEFAULT 0
    COMMENT 'Whether candidate was sent/presented to client'
    AFTER source;

-- Add indexes for new columns
-- Using CREATE INDEX with IF NOT EXISTS workaround for MySQL compatibility
DROP PROCEDURE IF EXISTS add_index_if_not_exists;
DELIMITER //
CREATE PROCEDURE add_index_if_not_exists()
BEGIN
    -- Index on bullhorn_status
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE()
        AND table_name = 'candidate_joborder'
        AND index_name = 'IDX_bullhorn_status'
    ) THEN
        CREATE INDEX IDX_bullhorn_status ON candidate_joborder(bullhorn_status);
    END IF;

    -- Index on source
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE()
        AND table_name = 'candidate_joborder'
        AND index_name = 'IDX_source'
    ) THEN
        CREATE INDEX IDX_source ON candidate_joborder(source);
    END IF;

    -- Index on send_to_client
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE()
        AND table_name = 'candidate_joborder'
        AND index_name = 'IDX_send_to_client'
    ) THEN
        CREATE INDEX IDX_send_to_client ON candidate_joborder(send_to_client);
    END IF;
END //
DELIMITER ;

CALL add_index_if_not_exists();
DROP PROCEDURE IF EXISTS add_index_if_not_exists;

-- ============================================================
-- 2. PLACEMENT TABLE
-- Tracks hired candidates (successful job placements)
-- ============================================================

CREATE TABLE IF NOT EXISTS placement (
    placement_id INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique placement identifier',
    site_id INT(11) NOT NULL DEFAULT 1 COMMENT 'Site/tenant ID',
    candidate_id INT(11) NOT NULL COMMENT 'Placed candidate',
    joborder_id INT(11) NOT NULL COMMENT 'Job order filled',
    company_id INT(11) NOT NULL COMMENT 'Hiring company',
    contact_id INT(11) DEFAULT NULL COMMENT 'Primary contact at company',

    -- Status tracking
    status VARCHAR(50) DEFAULT 'Active'
        COMMENT 'Placement status: Active, Completed, Terminated',

    -- Date tracking
    start_date DATE NOT NULL COMMENT 'Employment start date',
    end_date DATE DEFAULT NULL COMMENT 'Employment end date (if contract/terminated)',

    -- Compensation details
    salary DECIMAL(12,2) DEFAULT NULL COMMENT 'Salary amount',
    salary_type VARCHAR(20) DEFAULT 'Yearly'
        COMMENT 'Salary type: Yearly, Hourly, Daily',

    -- Fee structure
    fee DECIMAL(12,2) DEFAULT NULL COMMENT 'Placement fee amount',
    fee_type VARCHAR(20) DEFAULT 'Percentage'
        COMMENT 'Fee type: Percentage, Flat',

    -- Billing rates (for contract placements)
    bill_rate DECIMAL(10,2) DEFAULT NULL COMMENT 'Bill rate per hour/day',
    pay_rate DECIMAL(10,2) DEFAULT NULL COMMENT 'Pay rate per hour/day',

    -- Referral tracking
    referral_fee DECIMAL(12,2) DEFAULT NULL COMMENT 'Referral fee if applicable',

    -- Notes
    notes TEXT DEFAULT NULL COMMENT 'Additional placement notes',

    -- Audit fields
    date_created DATETIME NOT NULL COMMENT 'When placement was created',
    date_modified DATETIME DEFAULT NULL COMMENT 'Last modification timestamp',
    created_by INT(11) NOT NULL COMMENT 'User who created the placement',
    owner INT(11) NOT NULL COMMENT 'User who owns/manages this placement',

    PRIMARY KEY (placement_id),

    -- Indexes for common queries
    KEY idx_site_id (site_id),
    KEY idx_candidate_id (candidate_id),
    KEY idx_joborder_id (joborder_id),
    KEY idx_company_id (company_id),
    KEY idx_contact_id (contact_id),
    KEY idx_status (status),
    KEY idx_start_date (start_date),
    KEY idx_end_date (end_date),
    KEY idx_owner (owner),
    KEY idx_created_by (created_by),
    KEY idx_date_created (date_created),

    -- Composite indexes for common lookups
    KEY idx_site_status (site_id, status),
    KEY idx_site_candidate (site_id, candidate_id),
    KEY idx_site_company (site_id, company_id),
    KEY idx_site_joborder (site_id, joborder_id),

    -- Foreign key constraints
    CONSTRAINT fk_placement_candidate FOREIGN KEY (candidate_id)
        REFERENCES candidate(candidate_id) ON DELETE RESTRICT,
    CONSTRAINT fk_placement_joborder FOREIGN KEY (joborder_id)
        REFERENCES joborder(joborder_id) ON DELETE RESTRICT,
    CONSTRAINT fk_placement_company FOREIGN KEY (company_id)
        REFERENCES company(company_id) ON DELETE RESTRICT

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Placement records for hired candidates';

-- ============================================================
-- 3. PLACEMENT HISTORY TABLE
-- Tracks changes to placement records for audit trail
-- ============================================================

CREATE TABLE IF NOT EXISTS placement_history (
    history_id INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique history record ID',
    placement_id INT(11) NOT NULL COMMENT 'Related placement',

    -- Change tracking
    field_changed VARCHAR(50) NOT NULL COMMENT 'Name of field that was changed',
    old_value TEXT DEFAULT NULL COMMENT 'Previous value',
    new_value TEXT DEFAULT NULL COMMENT 'New value',

    -- Audit fields
    changed_by INT(11) NOT NULL COMMENT 'User who made the change',
    date_changed DATETIME NOT NULL COMMENT 'When the change was made',

    PRIMARY KEY (history_id),

    -- Indexes
    KEY idx_placement_id (placement_id),
    KEY idx_changed_by (changed_by),
    KEY idx_date_changed (date_changed),
    KEY idx_field_changed (field_changed),
    KEY idx_placement_date (placement_id, date_changed),

    -- Foreign key to placement
    CONSTRAINT fk_placement_history_placement FOREIGN KEY (placement_id)
        REFERENCES placement(placement_id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Audit trail for placement changes';

-- ============================================================
-- 4. ADD STATUS VALUES TO EXISTING LOOKUP TABLE
-- Add Bullhorn-compatible status options if not present
-- ============================================================

-- Insert additional status values for Bullhorn compatibility
-- These use status IDs that don't conflict with existing values
INSERT IGNORE INTO candidate_joborder_status
    (candidate_joborder_status_id, short_description, can_be_scheduled, triggers_email, is_enabled)
VALUES
    (850, 'Rejected', 0, 1, 1),
    (900, 'Withdrawn', 0, 0, 1),
    (350, 'Reviewed', 0, 0, 1);

-- ============================================================
-- 5. USEFUL VIEWS FOR REPORTING
-- ============================================================

-- View: Active placements with full details
CREATE OR REPLACE VIEW v_placements_summary AS
SELECT
    p.placement_id,
    p.site_id,
    p.status,
    p.start_date,
    p.end_date,
    p.salary,
    p.salary_type,
    p.fee,
    p.fee_type,
    p.bill_rate,
    p.pay_rate,
    p.date_created,
    -- Candidate info
    c.candidate_id,
    c.first_name AS candidate_first_name,
    c.last_name AS candidate_last_name,
    c.email1 AS candidate_email,
    -- Job info
    j.joborder_id,
    j.title AS job_title,
    -- Company info
    co.company_id,
    co.name AS company_name,
    -- Contact info
    ct.contact_id,
    ct.first_name AS contact_first_name,
    ct.last_name AS contact_last_name,
    -- Owner info
    u.user_id AS owner_id,
    u.first_name AS owner_first_name,
    u.last_name AS owner_last_name
FROM placement p
LEFT JOIN candidate c ON p.candidate_id = c.candidate_id
LEFT JOIN joborder j ON p.joborder_id = j.joborder_id
LEFT JOIN company co ON p.company_id = co.company_id
LEFT JOIN contact ct ON p.contact_id = ct.contact_id
LEFT JOIN user u ON p.owner = u.user_id;

-- View: JobSubmission (candidate_joborder) with Bullhorn-compatible fields
CREATE OR REPLACE VIEW v_job_submissions AS
SELECT
    cjo.candidate_joborder_id AS submission_id,
    cjo.site_id,
    cjo.candidate_id,
    cjo.joborder_id,
    cjo.status AS status_id,
    cjs.short_description AS status_name,
    cjo.bullhorn_status,
    cjo.date_submitted,
    cjo.date_interview,
    cjo.date_offer,
    cjo.source,
    cjo.send_to_client,
    cjo.rating_value,
    cjo.date_created,
    cjo.date_modified,
    -- Candidate info
    c.first_name AS candidate_first_name,
    c.last_name AS candidate_last_name,
    c.email1 AS candidate_email,
    -- Job info
    j.title AS job_title,
    j.company_id,
    -- Company name
    co.name AS company_name
FROM candidate_joborder cjo
LEFT JOIN candidate_joborder_status cjs ON cjo.status = cjs.candidate_joborder_status_id
LEFT JOIN candidate c ON cjo.candidate_id = c.candidate_id
LEFT JOIN joborder j ON cjo.joborder_id = j.joborder_id
LEFT JOIN company co ON j.company_id = co.company_id;

-- View: Placement revenue summary by month
CREATE OR REPLACE VIEW v_placement_revenue_monthly AS
SELECT
    p.site_id,
    YEAR(p.start_date) AS year,
    MONTH(p.start_date) AS month,
    COUNT(*) AS placement_count,
    SUM(CASE WHEN p.fee_type = 'Flat' THEN p.fee ELSE 0 END) AS flat_fee_total,
    SUM(CASE WHEN p.fee_type = 'Percentage' THEN p.fee * p.salary / 100 ELSE 0 END) AS percentage_fee_total,
    SUM(COALESCE(p.referral_fee, 0)) AS referral_fee_total,
    AVG(p.salary) AS avg_salary,
    AVG(p.bill_rate) AS avg_bill_rate,
    AVG(p.pay_rate) AS avg_pay_rate
FROM placement p
WHERE p.status IN ('Active', 'Completed')
GROUP BY p.site_id, YEAR(p.start_date), MONTH(p.start_date);

-- ============================================================
-- 6. UPDATE BULLHORN_STATUS BASED ON EXISTING STATUS
-- Sync existing records to have bullhorn_status populated
-- ============================================================

UPDATE candidate_joborder cjo
SET bullhorn_status = CASE
    WHEN cjo.status = 0 THEN 'Submitted'      -- No Status -> Submitted
    WHEN cjo.status = 100 THEN 'Submitted'    -- No Contact
    WHEN cjo.status = 200 THEN 'Submitted'    -- Contacted
    WHEN cjo.status = 250 THEN 'Submitted'    -- Candidate Responded
    WHEN cjo.status = 300 THEN 'Reviewed'     -- Qualifying
    WHEN cjo.status = 350 THEN 'Reviewed'     -- Reviewed
    WHEN cjo.status = 400 THEN 'Submitted'    -- Submitted
    WHEN cjo.status = 500 THEN 'Interview'    -- Interviewing
    WHEN cjo.status = 600 THEN 'Offer'        -- Offered
    WHEN cjo.status = 650 THEN 'Rejected'     -- Not in Consideration
    WHEN cjo.status = 700 THEN 'Rejected'     -- Client Declined
    WHEN cjo.status = 800 THEN 'Placed'       -- Placed
    WHEN cjo.status = 850 THEN 'Rejected'     -- Rejected
    WHEN cjo.status = 900 THEN 'Withdrawn'    -- Withdrawn
    ELSE 'Submitted'
END
WHERE bullhorn_status IS NULL OR bullhorn_status = 'Submitted';

-- ============================================================
-- MIGRATION COMPLETE
-- ============================================================

SELECT 'JobSubmission/Placement migration completed successfully!' AS status;
SELECT 'Enhanced: candidate_joborder table with Bullhorn-compatible fields' AS info;
SELECT 'Tables created: placement, placement_history' AS info;
SELECT 'Views created: v_placements_summary, v_job_submissions, v_placement_revenue_monthly' AS info;
SELECT 'Status values added: Rejected (850), Withdrawn (900), Reviewed (350)' AS info;
