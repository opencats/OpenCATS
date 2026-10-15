# IDOR assessment and remediation

Starting commit: `68711a9d158c6bddbd16e723bdeef282fb8e94b6`

Branch: `security/idor-hardening`

Worktree: `/srv/http/opencats-idor-hardening`

The original `/srv/http/opencats-bootstrap` checkout was dirty. A clean worktree
was created from its current HEAD; its configuration, untracked files, and
`master` branch were preserved. No push, merge, rebase, or squash was performed.

## Ordered changes and validation

All focused tests listed below passed under PHP **8.4.1** (Docker) and
**8.5.10** (local CLI). Each issue was reviewed and committed before starting
the next. Changed PHP files passed syntax checks and `git diff --check`.

### 1. Tag management — `7620285`

Normal page and direct AJAX requests now share the existing `settings.tags`
Site Administrator check. Its existing `careerportal` exception is preserved;
this change does not redesign that role's permissions.

Files:

- `modules/settings/SettingsUI.php`
- `src/OpenCATS/Tests/Support/AuthorizationTestCase.php`
- `src/OpenCATS/Tests/UnitTests/SettingsTagAuthorizationTest.php`

Focused coverage: **10 tests, 26 assertions**. Normal-user GET/POST denial,
all three direct AJAX mutations, explicit ACL overrides, administrator page
access, and administrator add/update/delete operations.

### 2. Job-order attachment deletion — `183bce9`

The endpoint loads the attachment through `Attachments::get()` and requires
an existing attachment belonging to the requested job order. The existing
`joborders.deleteAttachment` ACL remains authoritative. IDs are compared as
integers because request and database values may be strings. Missing rows
are detected by `attachmentID`: the attachment API can return a retrieval URL
even when no attachment exists.

Files:

- `modules/joborders/JobOrdersUI.php`
- `src/OpenCATS/Tests/UnitTests/JobOrderAttachmentAuthorizationTest.php`

Focused coverage: **7 tests, 20 assertions**. Matching attachment deletion,
missing attachment, candidate/company/contact attachment substitution,
another job order's attachment, and preservation of the delete ACL.

### 3. Hidden candidates in pipelines — `b56b63e`

Job-order insertion uses `CandidateAuthorization::canAccessCandidate()`.
`Pipelines::getJobOrderPipeline()` selects the candidate visibility flag and
filters records using `canAccessCandidateRecord()` before callers paginate
or render them. This avoids an extra candidate query for every pipeline row
and preserves result order with contiguous indexes.

Files:

- `lib/Pipelines.php`
- `modules/joborders/JobOrdersUI.php`
- `src/OpenCATS/Tests/UnitTests/JobOrderPipelineAuthorizationTest.php`

Focused coverage: **6 tests, 18 assertions**. Visible candidate insertion,
hidden candidate denial/administrator access, missing candidate denial, and
pipeline read filtering. The separately requested pipeline-detail route was
also identified and addressed in item 6.

### 4. Candidate email/phone lookup — `3aa865e`

Both lookup endpoints authorize the resolved candidate through
`CandidateAuthorization` and use its returned record for the name. Denied
access uses the same ID `-1` response as a missing candidate, without a name.
No additional hidden-candidate SQL condition was introduced.

Files:

- `ajax/getCandidateIdByEmail.php`
- `ajax/getCandidateIdByPhone.php`
- `src/OpenCATS/Tests/Support/AuthorizationTestCase.php`
- `src/OpenCATS/Tests/UnitTests/CandidateLookupAuthorizationTest.php`

Focused coverage: **8 tests, 20 assertions**. Both actual endpoint scripts,
each with visible, hidden/non-admin, hidden/admin, and missing candidates.

### 5. Administrator-only export — `8278546`

`ExportUI::handleRequest()` requires `getAccessLevel('export') >= ACCESS_LEVEL_SA`
before dispatch or parsing record selections. The gate covers default/normal,
explicit-ID, selected-checkbox, and `exportByDataGrid` requests. Repository
call-site inspection found no other callers producing CSV through `Export`
or `DataGrid::drawCSV()`. `ajax/getDataGridPager.php` renders HTML, not CSV.

Export controls are hidden on regular and saved-list DataGrids, legacy search
forms, the Lists page, and the job-order pipeline. Bulk selection remains
available where other actions use it. CSV formatting code is unchanged.

Files:

- `modules/export/ExportUI.php`
- `lib/Export.php`
- `modules/candidates/dataGrids.php`
- `modules/companies/dataGrids.php`
- `modules/contacts/dataGrids.php`
- `modules/joborders/dataGrids.php`
- `modules/lists/dataGrids.php`
- `modules/joborders/Show.tpl`
- `src/OpenCATS/Tests/UnitTests/ExportAuthorizationTest.php`

Focused coverage: **30 tests, 58 assertions**. Denied dispatch variants,
legacy CSV quoting/newline preservation for administrator exports,
administrator DataGrid dispatch, and menu visibility while retaining other
bulk actions. No second candidate visibility filter was added to export.

### 6. Parent authorization, preserving collaboration — sixth commit

The authorship-only allegation is rejected. Multiple users are intentionally
allowed to update pipeline ratings and activities when the existing ACLs permit
it; no `enteredBy == current user` or pipeline creator restriction was added.
Evidence in the existing code:

- `TemplateUtility::getRatingObject()` uses `pipelines.editRating`.
- Candidate activity controls use `candidates.edit` at EDIT level and
  `candidates.delete` at DELETE level.
- Contact activity controls use `contacts.editActivity` and
  `contacts.deleteActivity`, both at EDIT level.
- The company page displays contact activities from
  `ActivityEntries::getAllByCompany()`; its Contacts permissions are appropriate.
- Activity history explicitly records editing/deleting another user's activity.

Confirmed gaps and fixes:

1. Rating requests accepted any pipeline-row ID without checking parent access.
   `Pipelines::canAccess()` checks the existing candidate/job-order read ACLs,
   requires both parents to exist through inner joins, reuses candidate visibility
   authorization, and applies the existing `joborders.hidden` administrator rule.
   The rating endpoint retains its `pipelines.editRating` check.
2. The separately requested pipeline-detail read could reveal hidden candidates'
   activity notes. It now uses the same pipeline parent check before querying
   notes.
3. Generic activity edit/delete endpoints checked only Contacts permissions.
   They now load the activity's actual parent type and ID and use
   `ActivityEntries::canModify()`, which applies the existing UI permissions,
   parent read permission, parent existence, and candidate visibility.
   The supported candidate/contact activity types are accepted; other types
   fail closed. There are no normal UI callers for company/job-order-owned
   activity rows; company activity display is contact-owned.

Files:

- `ajax/deleteActivity.php`
- `ajax/editActivity.php`
- `ajax/setCandidateJobOrderRating.php`
- `lib/ActivityEntries.php`
- `lib/Pipelines.php`
- `src/OpenCATS/Tests/Support/runAuthorizationAjax.php`
- `src/OpenCATS/Tests/UnitTests/ActivityPipelineAuthorizationTest.php`
- `docs/security/idor-assessment.md`

Focused coverage: **30 tests, 178 assertions**. Real AJAX scripts run in isolated
CLI subprocesses with real session/ACL behavior and a database double. Tests
verify cross-user edits remain allowed, module-specific denials, candidate
DELETE versus EDIT thresholds, missing/unsupported parents, hidden candidates,
hidden job orders for ratings, and private pipeline-detail notes.

## Combined verification and limitations

- **91 new focused tests, 320 assertions**, passing on both PHP versions.
- Complete `UnitTests`: **248 tests, 2,131 assertions**, passing on both versions.
- Existing suite diagnostics remain: duplicate `HTML_ENCODING` in config,
  an existing test's dynamic-property deprecation, PHPUnit mock notices, and
  PHP 8.5 legacy cast/semicolon deprecations. No new application warning source
  was introduced. The CLI endpoint fixture permits only those known legacy
  compile deprecations and fails on other warnings/errors.
- The final broad runs also reported PHPUnit's deprecation of the
  `--do-not-cache-result` test-runner option; this is not application behavior.
- Tests exercise dispatch, real authorization/data helpers, mutation queries,
  and output using database doubles. Browser/Behat and live-database integration
  suites were not run. This is a targeted assessment, not a full security audit.

Reproduce a focused suite with:

```sh
php vendor/bin/phpunit src/OpenCATS/Tests/UnitTests/SettingsTagAuthorizationTest.php
```

Substitute the relevant test file from the lists above. Run the complete unit
suite with `php vendor/bin/phpunit --testsuite UnitTests`.

## Remaining audit follow-up

- Audit other job-order pipeline operations for consistent parent visibility,
  particularly `onRemoveFromPipeline()` and the add-activity/change-status modal
  reads. Do not introduce creator ownership restrictions.
- Audit the job-order pipeline list endpoint for the job order's own hidden/read
  boundary; the candidate rows are filtered by this patch, but that is a
  separate check from access to the job order itself.
- Audit activity reassociation through `jobOrderID` separately from the activity's
  candidate/contact parent, including visibility of referenced job titles.
- Review other object attachment endpoints for parent binding, and review whether
  the pre-existing career-portal exception for tag administration is intended.
