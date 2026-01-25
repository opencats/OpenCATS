-- ============================================================
-- OpenCATS Database Migration
-- Feature: OAuth 2.0 Authentication Tables
-- Version: 1.0.0
-- Date: 2026-01-25
--
-- Run this migration with:
-- mysql -u opencats -p opencats < 002_oauth2_tables.sql
--
-- NOTE: Uses InnoDB engine for OAuth tables to support foreign
-- keys and transactions (security-critical authentication data).
-- Uses utf8mb4 charset for full Unicode support.
-- ============================================================

-- ============================================================
-- 1. OAUTH CLIENTS TABLE
-- Stores registered OAuth 2.0 applications/clients
-- ============================================================

CREATE TABLE IF NOT EXISTS oauth_clients (
    client_id VARCHAR(80) NOT NULL COMMENT 'Unique client identifier',
    client_secret VARCHAR(80) NOT NULL COMMENT 'Client secret for authentication',
    redirect_uri VARCHAR(2000) DEFAULT NULL COMMENT 'Authorized redirect URI(s)',
    grant_types VARCHAR(80) DEFAULT 'authorization_code refresh_token' COMMENT 'Allowed grant types',
    scope VARCHAR(4000) DEFAULT NULL COMMENT 'Allowed scopes for this client',
    user_id INT(11) DEFAULT NULL COMMENT 'User who owns/created this client',
    client_name VARCHAR(255) NOT NULL COMMENT 'Human-readable application name',
    is_confidential TINYINT(1) DEFAULT 1 COMMENT '1=confidential client, 0=public client',
    date_created DATETIME NOT NULL COMMENT 'When the client was registered',

    PRIMARY KEY (client_id),
    UNIQUE KEY idx_client_secret (client_secret),
    KEY idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='OAuth 2.0 registered applications/clients';

-- ============================================================
-- 2. OAUTH ACCESS TOKENS TABLE
-- Stores issued access tokens
-- ============================================================

CREATE TABLE IF NOT EXISTS oauth_access_tokens (
    access_token VARCHAR(40) NOT NULL COMMENT 'The access token string',
    client_id VARCHAR(80) NOT NULL COMMENT 'Client that requested this token',
    user_id INT(11) DEFAULT NULL COMMENT 'User who authorized this token',
    expires DATETIME NOT NULL COMMENT 'Token expiration timestamp',
    scope VARCHAR(4000) DEFAULT NULL COMMENT 'Granted scopes for this token',

    PRIMARY KEY (access_token),
    KEY idx_client_id (client_id),
    KEY idx_expires (expires),
    CONSTRAINT fk_access_tokens_client FOREIGN KEY (client_id) REFERENCES oauth_clients(client_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='OAuth 2.0 access tokens';

-- ============================================================
-- 3. OAUTH REFRESH TOKENS TABLE
-- Stores refresh tokens for obtaining new access tokens
-- ============================================================

CREATE TABLE IF NOT EXISTS oauth_refresh_tokens (
    refresh_token VARCHAR(40) NOT NULL COMMENT 'The refresh token string',
    client_id VARCHAR(80) NOT NULL COMMENT 'Client that requested this token',
    user_id INT(11) DEFAULT NULL COMMENT 'User who authorized this token',
    expires DATETIME NOT NULL COMMENT 'Token expiration timestamp',
    scope VARCHAR(4000) DEFAULT NULL COMMENT 'Granted scopes for this token',

    PRIMARY KEY (refresh_token),
    KEY idx_client_id (client_id),
    KEY idx_expires (expires),
    CONSTRAINT fk_refresh_tokens_client FOREIGN KEY (client_id) REFERENCES oauth_clients(client_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='OAuth 2.0 refresh tokens';

-- ============================================================
-- 4. OAUTH AUTHORIZATION CODES TABLE
-- Stores temporary authorization codes (authorization code flow)
-- ============================================================

CREATE TABLE IF NOT EXISTS oauth_authorization_codes (
    authorization_code VARCHAR(40) NOT NULL COMMENT 'The authorization code string',
    client_id VARCHAR(80) NOT NULL COMMENT 'Client that requested this code',
    user_id INT(11) DEFAULT NULL COMMENT 'User who authorized this code',
    redirect_uri VARCHAR(2000) DEFAULT NULL COMMENT 'Redirect URI for this authorization',
    expires DATETIME NOT NULL COMMENT 'Code expiration timestamp',
    scope VARCHAR(4000) DEFAULT NULL COMMENT 'Requested scopes',
    id_token VARCHAR(1000) DEFAULT NULL COMMENT 'OpenID Connect ID token (if applicable)',

    PRIMARY KEY (authorization_code),
    KEY idx_client_id (client_id),
    KEY idx_expires (expires),
    CONSTRAINT fk_auth_codes_client FOREIGN KEY (client_id) REFERENCES oauth_clients(client_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='OAuth 2.0 authorization codes (temporary, for auth code flow)';

-- ============================================================
-- 5. OAUTH SCOPES TABLE
-- Defines available OAuth 2.0 scopes
-- ============================================================

CREATE TABLE IF NOT EXISTS oauth_scopes (
    scope VARCHAR(80) NOT NULL COMMENT 'Scope identifier',
    is_default TINYINT(1) DEFAULT 0 COMMENT '1=granted by default if no scope requested',
    description VARCHAR(255) DEFAULT NULL COMMENT 'Human-readable scope description',

    PRIMARY KEY (scope)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='OAuth 2.0 available scopes';

-- ============================================================
-- 6. INSERT DEFAULT SCOPES
-- ============================================================

INSERT IGNORE INTO oauth_scopes (scope, is_default, description) VALUES
    ('read', 1, 'Read access to candidates, jobs, companies, and contacts'),
    ('write', 0, 'Create and update candidates, jobs, companies, and contacts'),
    ('admin', 0, 'Administrative access including user management and settings');

-- ============================================================
-- MIGRATION COMPLETE
-- ============================================================

SELECT 'OAuth 2.0 migration completed successfully!' as status;
SELECT 'Tables created: oauth_clients, oauth_access_tokens, oauth_refresh_tokens, oauth_authorization_codes, oauth_scopes' as info;
SELECT 'Default scopes inserted: read (default), write, admin' as info;
