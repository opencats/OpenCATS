-- ============================================================
-- OpenCATS Database Migration
-- Feature: REST API + Tearsheets
-- Version: 1.0.0
-- Date: 2026-01-25
-- 
-- Run this migration with:
-- mysql -u opencats -p opencats < 001_add_api_and_tearsheets.sql
-- ============================================================

-- ============================================================
-- 1. API KEYS TABLE
-- Stores API credentials for programmatic access
-- ============================================================

CREATE TABLE IF NOT EXISTS api_keys (
    api_key_id INT(11) NOT NULL AUTO_INCREMENT,
    site_id INT(11) NOT NULL DEFAULT 1,
    user_id INT(11) NOT NULL,
    api_key VARCHAR(64) NOT NULL COMMENT 'Public API key',
    api_secret VARCHAR(64) NOT NULL COMMENT 'Secret key for authentication',
    description VARCHAR(255) DEFAULT NULL COMMENT 'Human-readable description',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_date DATETIME NOT NULL,
    last_used DATETIME DEFAULT NULL,
    
    PRIMARY KEY (api_key_id),
    UNIQUE KEY idx_api_key (api_key),
    KEY idx_user_id (user_id),
    KEY idx_site_id (site_id),
    
    CONSTRAINT fk_api_keys_user 
        FOREIGN KEY (user_id) REFERENCES user(user_id) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 
  COMMENT='API authentication keys for REST API access';

-- ============================================================
-- 2. API SESSIONS TABLE
-- Stores temporary session tokens for API access
-- ============================================================

CREATE TABLE IF NOT EXISTS api_sessions (
    session_id INT(11) NOT NULL AUTO_INCREMENT,
    api_key_id INT(11) NOT NULL,
    session_token VARCHAR(128) NOT NULL,
    created_date DATETIME NOT NULL,
    expires_date DATETIME NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    
    PRIMARY KEY (session_id),
    UNIQUE KEY idx_session_token (session_token),
    KEY idx_api_key_id (api_key_id),
    KEY idx_expires (expires_date),
    
    CONSTRAINT fk_api_sessions_key 
        FOREIGN KEY (api_key_id) REFERENCES api_keys(api_key_id) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Temporary API session tokens';

-- ============================================================
-- 3. TEARSHEET TABLE
-- Stores saved job order lists (like Bullhorn tearsheets)
-- ============================================================

CREATE TABLE IF NOT EXISTS tearsheet (
    tearsheet_id INT(11) NOT NULL AUTO_INCREMENT,
    site_id INT(11) NOT NULL DEFAULT 1,
    user_id INT(11) NOT NULL COMMENT 'Owner of the tearsheet',
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=visible to all users',
    date_created DATETIME NOT NULL,
    date_modified DATETIME DEFAULT NULL,
    
    PRIMARY KEY (tearsheet_id),
    KEY idx_user_id (user_id),
    KEY idx_site_id (site_id),
    KEY idx_name (name),
    
    CONSTRAINT fk_tearsheet_user 
        FOREIGN KEY (user_id) REFERENCES user(user_id) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Saved job order lists';

-- ============================================================
-- 4. TEARSHEET_JOBORDER TABLE
-- Many-to-many relationship between tearsheets and job orders
-- ============================================================

CREATE TABLE IF NOT EXISTS tearsheet_joborder (
    tearsheet_joborder_id INT(11) NOT NULL AUTO_INCREMENT,
    tearsheet_id INT(11) NOT NULL,
    joborder_id INT(11) NOT NULL,
    date_added DATETIME NOT NULL,
    added_by INT(11) DEFAULT NULL COMMENT 'User who added this job',
    notes TEXT DEFAULT NULL COMMENT 'Optional notes for this job in this tearsheet',
    
    PRIMARY KEY (tearsheet_joborder_id),
    UNIQUE KEY idx_tearsheet_job (tearsheet_id, joborder_id),
    KEY idx_joborder_id (joborder_id),
    KEY idx_date_added (date_added),
    
    CONSTRAINT fk_tj_tearsheet 
        FOREIGN KEY (tearsheet_id) REFERENCES tearsheet(tearsheet_id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_tj_joborder 
        FOREIGN KEY (joborder_id) REFERENCES joborder(joborder_id) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Jobs contained in tearsheets';

-- ============================================================
-- 5. API REQUEST LOG TABLE (Optional - for debugging)
-- Logs API requests for troubleshooting
-- ============================================================

CREATE TABLE IF NOT EXISTS api_request_log (
    log_id INT(11) NOT NULL AUTO_INCREMENT,
    api_key_id INT(11) DEFAULT NULL,
    endpoint VARCHAR(100) NOT NULL,
    method VARCHAR(10) NOT NULL,
    status_code INT(3) NOT NULL,
    request_time DATETIME NOT NULL,
    response_time_ms INT(11) DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    error_message TEXT DEFAULT NULL,

    PRIMARY KEY (log_id),
    KEY idx_api_key_id (api_key_id),
    KEY idx_request_time (request_time),
    KEY idx_endpoint (endpoint),

    CONSTRAINT fk_api_request_log_key
        FOREIGN KEY (api_key_id) REFERENCES api_keys(api_key_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='API request logging for debugging';

-- ============================================================
-- 6. INSERT DEFAULT DATA
-- ============================================================

-- Create a development API key for testing
-- WARNING: Change or remove this in production!
INSERT INTO api_keys (site_id, user_id, api_key, api_secret, description, created_date)
SELECT 1, user_id, 'dev-test-key-12345', 'dev-test-secret', 'Development testing key - REMOVE IN PRODUCTION', NOW()
FROM user 
WHERE access_level >= 500 
LIMIT 1;

-- Create a sample public tearsheet
INSERT INTO tearsheet (site_id, user_id, name, description, is_public, date_created)
SELECT 1, user_id, 'Active Job Postings', 'Primary list of active jobs for distribution to job boards', 1, NOW()
FROM user 
WHERE access_level >= 400 
LIMIT 1;

-- Add some jobs to the sample tearsheet (if jobs exist)
INSERT INTO tearsheet_joborder (tearsheet_id, joborder_id, date_added)
SELECT 
    (SELECT tearsheet_id FROM tearsheet WHERE name = 'Active Job Postings' LIMIT 1),
    joborder_id,
    NOW()
FROM joborder
WHERE status = 'Active'
  AND public = 1
LIMIT 10;

-- ============================================================
-- 7. USEFUL VIEWS (Optional)
-- ============================================================

-- View: Tearsheets with job counts
CREATE OR REPLACE VIEW v_tearsheets_summary AS
SELECT 
    t.tearsheet_id,
    t.name,
    t.description,
    t.is_public,
    t.date_created,
    t.date_modified,
    u.first_name as owner_first_name,
    u.last_name as owner_last_name,
    COUNT(tj.joborder_id) as job_count,
    MAX(tj.date_added) as last_job_added
FROM tearsheet t
LEFT JOIN user u ON t.user_id = u.user_id
LEFT JOIN tearsheet_joborder tj ON t.tearsheet_id = tj.tearsheet_id
GROUP BY t.tearsheet_id;

-- View: API keys with usage stats
CREATE OR REPLACE VIEW v_api_keys_summary AS
SELECT 
    ak.api_key_id,
    ak.api_key,
    ak.description,
    ak.is_active,
    ak.created_date,
    ak.last_used,
    u.first_name,
    u.last_name,
    u.user_name,
    (SELECT COUNT(*) FROM api_request_log arl WHERE arl.api_key_id = ak.api_key_id) as total_requests
FROM api_keys ak
LEFT JOIN user u ON ak.user_id = u.user_id;

-- ============================================================
-- MIGRATION COMPLETE
-- ============================================================

SELECT 'Migration completed successfully!' as status;
SELECT 'Tables created: api_keys, api_sessions, tearsheet, tearsheet_joborder, api_request_log' as info;
SELECT 'Views created: v_tearsheets_summary, v_api_keys_summary' as info;
SELECT 'Default API key created: dev-test-key-12345 (REMOVE IN PRODUCTION!)' as warning;
