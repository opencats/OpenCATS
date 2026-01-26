-- Migration: 006_webhooks.sql
-- Description: Create webhook tables for Bullhorn-compatible event subscriptions
-- Author: OpenCATS Migration
-- Date: 2026-01-25

-- ============================================================================
-- Table: webhook_subscriptions
-- Description: Store webhook subscription configurations for event notifications
-- ============================================================================
CREATE TABLE IF NOT EXISTS webhook_subscriptions (
    subscription_id INT NOT NULL AUTO_INCREMENT,
    site_id INT NOT NULL COMMENT 'Multi-tenant site identifier',
    name VARCHAR(255) NOT NULL COMMENT 'Friendly name for the subscription',
    entity_type VARCHAR(50) NOT NULL COMMENT 'Entity type: candidate, joborder, placement, etc.',
    event_types VARCHAR(255) NOT NULL COMMENT 'Comma-separated event types: create,update,delete',
    callback_url VARCHAR(2048) NOT NULL COMMENT 'URL to POST events to',
    secret VARCHAR(255) NULL COMMENT 'Secret for HMAC signature verification',
    is_active TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active, 0=inactive',
    created_by INT NOT NULL COMMENT 'User ID who created this subscription',
    date_created DATETIME NOT NULL,
    date_modified DATETIME NULL,
    PRIMARY KEY (subscription_id),
    INDEX idx_webhook_subscriptions_site_entity (site_id, entity_type),
    INDEX idx_webhook_subscriptions_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: webhook_delivery_log
-- Description: Log webhook delivery attempts for debugging and retry logic
-- ============================================================================
CREATE TABLE IF NOT EXISTS webhook_delivery_log (
    log_id INT NOT NULL AUTO_INCREMENT,
    subscription_id INT NOT NULL COMMENT 'FK to webhook_subscriptions',
    event_type VARCHAR(50) NOT NULL COMMENT 'Event type: create, update, delete',
    entity_id INT NOT NULL COMMENT 'ID of the entity that triggered the event',
    payload TEXT NOT NULL COMMENT 'JSON payload sent to callback URL',
    response_code INT NULL COMMENT 'HTTP response code from callback',
    response_body TEXT NULL COMMENT 'Response body from callback URL',
    attempt_count INT NOT NULL DEFAULT 1 COMMENT 'Number of delivery attempts',
    status ENUM('pending', 'success', 'failed', 'retrying') NOT NULL DEFAULT 'pending',
    date_created DATETIME NOT NULL,
    date_completed DATETIME NULL COMMENT 'When delivery was completed (success or final failure)',
    PRIMARY KEY (log_id),
    INDEX idx_webhook_delivery_log_subscription (subscription_id),
    INDEX idx_webhook_delivery_log_status (status),
    CONSTRAINT fk_webhook_delivery_log_subscription
        FOREIGN KEY (subscription_id) REFERENCES webhook_subscriptions (subscription_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: webhook_event_queue
-- Description: Queue for asynchronous webhook delivery processing
-- ============================================================================
CREATE TABLE IF NOT EXISTS webhook_event_queue (
    queue_id INT NOT NULL AUTO_INCREMENT,
    subscription_id INT NOT NULL COMMENT 'FK to webhook_subscriptions',
    event_type VARCHAR(50) NOT NULL COMMENT 'Event type: create, update, delete',
    entity_type VARCHAR(50) NOT NULL COMMENT 'Entity type: candidate, joborder, placement, etc.',
    entity_id INT NOT NULL COMMENT 'ID of the entity that triggered the event',
    payload TEXT NOT NULL COMMENT 'JSON payload to send',
    priority INT NOT NULL DEFAULT 5 COMMENT 'Priority: lower number = higher priority',
    scheduled_at DATETIME NOT NULL COMMENT 'When to attempt delivery',
    date_created DATETIME NOT NULL,
    PRIMARY KEY (queue_id),
    INDEX idx_webhook_event_queue_scheduled_priority (scheduled_at, priority),
    CONSTRAINT fk_webhook_event_queue_subscription
        FOREIGN KEY (subscription_id) REFERENCES webhook_subscriptions (subscription_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
