-- ============================================================
-- OpenCATS Database Migration
-- Feature: Extended Entities (Note, Appointment, Task)
-- Version: 1.0.0
-- Date: 2026-01-25
--
-- Run this migration with:
-- mysql -u opencats -p opencats < 004_extended_entities.sql
--
-- This migration creates Bullhorn-compatible tables for:
-- - Notes (extends activity concept for API compatibility)
-- - Appointments (calendar events linked to entities)
-- - Tasks (to-do items with due dates)
--
-- NOTE: Uses InnoDB engine for foreign key support.
-- Uses utf8mb4 charset for full Unicode support.
-- ============================================================

-- ============================================================
-- 1. NOTES TABLE
-- Stores notes/comments linked to candidates, contacts, jobs, companies
-- Extends the activity concept for Bullhorn API compatibility
-- ============================================================

CREATE TABLE IF NOT EXISTS note (
    note_id INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique note identifier',
    site_id INT(11) NOT NULL DEFAULT 1 COMMENT 'Site/tenant ID',

    -- Note content
    action TEXT NOT NULL COMMENT 'Note title/action/subject',
    comments TEXT DEFAULT NULL COMMENT 'Detailed note content',

    -- Entity association (polymorphic)
    person_type ENUM('candidate', 'contact', 'joborder', 'company') NOT NULL
        COMMENT 'Type of entity this note is associated with',
    person_id INT(11) NOT NULL COMMENT 'ID of the associated entity',

    -- Related job order (optional, for context)
    joborder_id INT(11) DEFAULT NULL COMMENT 'Related job order if applicable',

    -- Activity type (maps to OpenCATS activity_type)
    activity_type INT(11) DEFAULT 400 COMMENT 'Activity type: 100=Call, 200=Email, 300=Meeting, 400=Other',

    -- Audit fields
    date_created DATETIME NOT NULL COMMENT 'When note was created',
    date_modified DATETIME DEFAULT NULL COMMENT 'Last modification timestamp',
    entered_by INT(11) NOT NULL COMMENT 'User who created the note',

    PRIMARY KEY (note_id),

    -- Indexes for common queries
    KEY idx_site_id (site_id),
    KEY idx_person (person_type, person_id),
    KEY idx_site_person (site_id, person_type, person_id),
    KEY idx_entered_by (entered_by),
    KEY idx_date_created (date_created),
    KEY idx_joborder (joborder_id),
    KEY idx_activity_type (activity_type),

    -- Composite indexes for common lookups
    KEY idx_site_entered_by (site_id, entered_by),
    KEY idx_site_date (site_id, date_created)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Notes/comments for candidates, contacts, jobs, companies';

-- ============================================================
-- 2. APPOINTMENTS TABLE
-- Calendar events/meetings linked to entities
-- ============================================================

CREATE TABLE IF NOT EXISTS appointment (
    appointment_id INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique appointment identifier',
    site_id INT(11) NOT NULL DEFAULT 1 COMMENT 'Site/tenant ID',

    -- Appointment details
    title VARCHAR(255) NOT NULL COMMENT 'Appointment title/subject',
    description TEXT DEFAULT NULL COMMENT 'Detailed description',
    type VARCHAR(50) DEFAULT 'Meeting'
        COMMENT 'Type: Meeting, Phone Call, Interview, Presentation, Other',

    -- Scheduling
    start_date DATETIME NOT NULL COMMENT 'Appointment start time',
    end_date DATETIME NOT NULL COMMENT 'Appointment end time',
    all_day TINYINT(1) DEFAULT 0 COMMENT 'Whether this is an all-day event',
    location VARCHAR(255) DEFAULT NULL COMMENT 'Location/address of meeting',

    -- Entity association (polymorphic, optional)
    person_type ENUM('candidate', 'contact', 'joborder', 'company') DEFAULT NULL
        COMMENT 'Type of entity this appointment is associated with',
    person_id INT(11) DEFAULT NULL COMMENT 'ID of the associated entity',

    -- Related job order (optional, for interviews)
    joborder_id INT(11) DEFAULT NULL COMMENT 'Related job order if applicable',

    -- Status tracking
    status VARCHAR(50) DEFAULT 'Scheduled'
        COMMENT 'Status: Scheduled, Completed, Cancelled, Rescheduled',

    -- Notification settings
    reminder_minutes INT(11) DEFAULT NULL COMMENT 'Minutes before to send reminder',

    -- Ownership
    owner INT(11) NOT NULL COMMENT 'User who owns/created the appointment',

    -- Audit fields
    date_created DATETIME NOT NULL COMMENT 'When appointment was created',
    date_modified DATETIME DEFAULT NULL COMMENT 'Last modification timestamp',

    PRIMARY KEY (appointment_id),

    -- Indexes for common queries
    KEY idx_site_id (site_id),
    KEY idx_owner (owner),
    KEY idx_site_owner (site_id, owner),
    KEY idx_start_date (start_date),
    KEY idx_end_date (end_date),
    KEY idx_dates (start_date, end_date),
    KEY idx_person (person_type, person_id),
    KEY idx_joborder (joborder_id),
    KEY idx_status (status),
    KEY idx_type (type),

    -- Composite indexes for calendar queries
    KEY idx_site_dates (site_id, start_date, end_date),
    KEY idx_owner_dates (owner, start_date, end_date)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Appointments and calendar events';

-- ============================================================
-- 3. TASKS TABLE
-- To-do items with due dates and status tracking
-- ============================================================

CREATE TABLE IF NOT EXISTS task (
    task_id INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique task identifier',
    site_id INT(11) NOT NULL DEFAULT 1 COMMENT 'Site/tenant ID',

    -- Task details
    subject VARCHAR(255) NOT NULL COMMENT 'Task title/subject',
    description TEXT DEFAULT NULL COMMENT 'Detailed task description',

    -- Status and priority
    status VARCHAR(50) DEFAULT 'Not Started'
        COMMENT 'Status: Not Started, In Progress, Completed, Deferred, Waiting',
    priority VARCHAR(20) DEFAULT 'Normal'
        COMMENT 'Priority: High, Normal, Low',

    -- Scheduling
    due_date DATE DEFAULT NULL COMMENT 'Task due date',
    start_date DATE DEFAULT NULL COMMENT 'Task start date (optional)',

    -- Entity association (polymorphic, optional)
    person_type ENUM('candidate', 'contact', 'joborder', 'company') DEFAULT NULL
        COMMENT 'Type of entity this task is associated with',
    person_id INT(11) DEFAULT NULL COMMENT 'ID of the associated entity',

    -- Related job order (optional)
    joborder_id INT(11) DEFAULT NULL COMMENT 'Related job order if applicable',

    -- Notification settings
    reminder_date DATETIME DEFAULT NULL COMMENT 'When to send reminder',

    -- Ownership
    owner INT(11) NOT NULL COMMENT 'User who owns the task',
    assigned_to INT(11) DEFAULT NULL COMMENT 'User task is assigned to (if different from owner)',

    -- Audit fields
    date_created DATETIME NOT NULL COMMENT 'When task was created',
    date_modified DATETIME DEFAULT NULL COMMENT 'Last modification timestamp',
    date_completed DATETIME DEFAULT NULL COMMENT 'When task was completed',

    PRIMARY KEY (task_id),

    -- Indexes for common queries
    KEY idx_site_id (site_id),
    KEY idx_owner (owner),
    KEY idx_assigned_to (assigned_to),
    KEY idx_site_owner (site_id, owner),
    KEY idx_status (status),
    KEY idx_priority (priority),
    KEY idx_due_date (due_date),
    KEY idx_person (person_type, person_id),
    KEY idx_joborder (joborder_id),

    -- Composite indexes for task lists
    KEY idx_site_status (site_id, status),
    KEY idx_owner_status (owner, status),
    KEY idx_owner_due (owner, due_date),
    KEY idx_site_owner_status (site_id, owner, status)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tasks and to-do items';

-- ============================================================
-- 4. USEFUL VIEWS FOR REPORTING
-- ============================================================

-- View: Notes with entity and user details
CREATE OR REPLACE VIEW v_notes_detail AS
SELECT
    n.note_id,
    n.site_id,
    n.action,
    n.comments,
    n.person_type,
    n.person_id,
    n.joborder_id,
    n.activity_type,
    at.short_description AS activity_type_name,
    n.date_created,
    n.date_modified,
    n.entered_by,
    u.first_name AS entered_by_first_name,
    u.last_name AS entered_by_last_name,
    CONCAT(u.first_name, ' ', u.last_name) AS entered_by_name,
    -- Entity name based on person_type
    CASE n.person_type
        WHEN 'candidate' THEN (SELECT CONCAT(first_name, ' ', last_name) FROM candidate WHERE candidate_id = n.person_id)
        WHEN 'contact' THEN (SELECT CONCAT(first_name, ' ', last_name) FROM contact WHERE contact_id = n.person_id)
        WHEN 'company' THEN (SELECT name FROM company WHERE company_id = n.person_id)
        WHEN 'joborder' THEN (SELECT title FROM joborder WHERE joborder_id = n.person_id)
    END AS entity_name,
    -- Job order title if specified
    j.title AS joborder_title
FROM note n
LEFT JOIN user u ON n.entered_by = u.user_id
LEFT JOIN activity_type at ON n.activity_type = at.activity_type_id
LEFT JOIN joborder j ON n.joborder_id = j.joborder_id;

-- View: Appointments with entity and owner details
CREATE OR REPLACE VIEW v_appointments_detail AS
SELECT
    a.appointment_id,
    a.site_id,
    a.title,
    a.description,
    a.type,
    a.start_date,
    a.end_date,
    a.all_day,
    a.location,
    a.person_type,
    a.person_id,
    a.joborder_id,
    a.status,
    a.reminder_minutes,
    a.owner,
    a.date_created,
    a.date_modified,
    u.first_name AS owner_first_name,
    u.last_name AS owner_last_name,
    CONCAT(u.first_name, ' ', u.last_name) AS owner_name,
    -- Entity name based on person_type
    CASE a.person_type
        WHEN 'candidate' THEN (SELECT CONCAT(first_name, ' ', last_name) FROM candidate WHERE candidate_id = a.person_id)
        WHEN 'contact' THEN (SELECT CONCAT(first_name, ' ', last_name) FROM contact WHERE contact_id = a.person_id)
        WHEN 'company' THEN (SELECT name FROM company WHERE company_id = a.person_id)
        WHEN 'joborder' THEN (SELECT title FROM joborder WHERE joborder_id = a.person_id)
    END AS entity_name,
    -- Job order title if specified
    j.title AS joborder_title
FROM appointment a
LEFT JOIN user u ON a.owner = u.user_id
LEFT JOIN joborder j ON a.joborder_id = j.joborder_id;

-- View: Tasks with entity and owner details
CREATE OR REPLACE VIEW v_tasks_detail AS
SELECT
    t.task_id,
    t.site_id,
    t.subject,
    t.description,
    t.status,
    t.priority,
    t.due_date,
    t.start_date,
    t.person_type,
    t.person_id,
    t.joborder_id,
    t.reminder_date,
    t.owner,
    t.assigned_to,
    t.date_created,
    t.date_modified,
    t.date_completed,
    -- Owner details
    owner_user.first_name AS owner_first_name,
    owner_user.last_name AS owner_last_name,
    CONCAT(owner_user.first_name, ' ', owner_user.last_name) AS owner_name,
    -- Assigned to details
    assigned_user.first_name AS assigned_to_first_name,
    assigned_user.last_name AS assigned_to_last_name,
    CONCAT(assigned_user.first_name, ' ', assigned_user.last_name) AS assigned_to_name,
    -- Entity name based on person_type
    CASE t.person_type
        WHEN 'candidate' THEN (SELECT CONCAT(first_name, ' ', last_name) FROM candidate WHERE candidate_id = t.person_id)
        WHEN 'contact' THEN (SELECT CONCAT(first_name, ' ', last_name) FROM contact WHERE contact_id = t.person_id)
        WHEN 'company' THEN (SELECT name FROM company WHERE company_id = t.person_id)
        WHEN 'joborder' THEN (SELECT title FROM joborder WHERE joborder_id = t.person_id)
    END AS entity_name,
    -- Job order title if specified
    j.title AS joborder_title,
    -- Calculate overdue status
    CASE
        WHEN t.status = 'Completed' THEN 'completed'
        WHEN t.due_date IS NULL THEN 'no_due_date'
        WHEN t.due_date < CURDATE() THEN 'overdue'
        WHEN t.due_date = CURDATE() THEN 'due_today'
        ELSE 'upcoming'
    END AS due_status
FROM task t
LEFT JOIN user owner_user ON t.owner = owner_user.user_id
LEFT JOIN user assigned_user ON t.assigned_to = assigned_user.user_id
LEFT JOIN joborder j ON t.joborder_id = j.joborder_id;

-- View: Upcoming tasks (not completed, ordered by due date)
CREATE OR REPLACE VIEW v_tasks_upcoming AS
SELECT * FROM v_tasks_detail
WHERE status != 'Completed'
ORDER BY
    CASE WHEN due_date IS NULL THEN 1 ELSE 0 END,
    due_date ASC;

-- View: Overdue tasks
CREATE OR REPLACE VIEW v_tasks_overdue AS
SELECT * FROM v_tasks_detail
WHERE status != 'Completed'
  AND due_date < CURDATE()
ORDER BY due_date ASC;

-- ============================================================
-- MIGRATION COMPLETE
-- ============================================================

SELECT 'Extended entities migration completed successfully!' AS status;
SELECT 'Tables created: note, appointment, task' AS info;
SELECT 'Views created: v_notes_detail, v_appointments_detail, v_tasks_detail, v_tasks_upcoming, v_tasks_overdue' AS info;
