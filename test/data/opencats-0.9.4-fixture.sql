/*
 * Minimal OpenCATS 0.9.4 upgrade fixture.
 *
 * This deliberately adds only a small amount of transactional data.
 * The historical 0.9.4 schema already provides the site, users,
 * Internal Postings company, reference data and Career Portal templates.
 */

INSERT INTO company_department (
    company_department_id,
    name,
    company_id,
    site_id,
    date_created,
    created_by
)
VALUES (
    1,
    'Recruitment',
    1,
    1,
    '2017-01-01 09:00:00',
    1
);

INSERT INTO candidate (
    candidate_id,
    site_id,
    last_name,
    first_name,
    phone_cell,
    address,
    city,
    state,
    zip,
    source,
    notes,
    key_skills,
    current_employer,
    entered_by,
    owner,
    date_created,
    date_modified,
    email1,
    import_id,
    is_hot,
    can_relocate,
    best_time_to_call
)
VALUES (
    1,
    1,
    'Müller',
    'Test',
    '01234567890',
    '1 Test Street',
    'Testville',
    'Testshire',
    'TE1 1ST',
    'OpenCATS upgrade fixture',
    'Legacy &amp;amp; encoded candidate note',
    'PHP, SQL, Recruitment',
    'Example Employer',
    1,
    1,
    '2017-01-01 09:00:00',
    '2017-01-01 09:00:00',
    'candidate@example.com',
    0,
    0,
    0,
    'Any time'
);

INSERT INTO contact (
    contact_id,
    company_id,
    site_id,
    last_name,
    first_name,
    title,
    email1,
    phone_work,
    address,
    city,
    state,
    zip,
    notes,
    entered_by,
    owner,
    date_created,
    date_modified,
    left_company,
    import_id,
    company_department_id,
    reports_to
)
VALUES (
    1,
    1,
    1,
    'Contact',
    'Test',
    'Hiring Manager',
    'contact@example.com',
    '01234567891',
    '1 Test Street',
    'Testville',
    'Testshire',
    'TE1 1ST',
    'OpenCATS 0.9.4 upgrade fixture contact',
    1,
    1,
    '2017-01-01 09:00:00',
    '2017-01-01 09:00:00',
    0,
    0,
    1,
    -1
);

INSERT INTO joborder (
    joborder_id,
    recruiter,
    contact_id,
    company_id,
    entered_by,
    owner,
    site_id,
    client_job_id,
    title,
    description,
    notes,
    type,
    status,
    openings,
    city,
    state,
    date_created,
    date_modified,
    public,
    company_department_id,
    openings_available
)
VALUES (
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    'TEST-001',
    'Test Job Order',
    'Legacy OpenCATS job order used for upgrade testing.',
    'Legacy &amp;amp; encoded job order note',
    'C',
    'Active',
    1,
    'Testville',
    'Testshire',
    '2017-01-01 09:00:00',
    '2017-01-01 09:00:00',
    1,
    1,
    1
);

INSERT INTO activity (
    activity_id,
    data_item_id,
    data_item_type,
    joborder_id,
    site_id,
    entered_by,
    date_created,
    type,
    notes,
    date_modified
)
VALUES (
    1,
    1,
    100,
    1,
    1,
    1,
    '2017-01-01 10:00:00',
    100,
    'Legacy &amp;amp; encoded activity note',
    '2017-01-01 10:00:00'
);
