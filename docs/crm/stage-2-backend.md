# Stage 2 — native Task backend

Follow-up: [9 October verification and minimal-correction review](stage-2-review.md)
records the explicitly requested dual-runtime checks and migration compatibility fix.
The original implementation evidence below is retained as historical evidence.

Implementation baseline: `feature/crm-desk-foundation` at
`f66ed1459652d7885b81691c72751e40a0f65bb9`, containing Stage 1A, 1B and 1C.
This is a dependency on that Stage 1 feature-branch baseline, not evidence of
an upstream merge. Starting source schema: 398; Task migration: 399.
Q03/Q04/Q05/Q07/Q12 were already approved in feature plan v0.7 on 8 October
2026. This implementation does not reopen those decisions.

## Changed files

- `constants.php`: Task data-item identity.
- `db/cats_schema.sql`: fresh-install Task table and indexes.
- `modules/install/Schema.php`: migration 399.
- `lib/Tasks.php`: scoped Task backend and validation.
- `lib/History.php`: return write outcomes to support atomic Task auditing.
- `modules/install/backupDB.php`: include Task audit rows.
- `src/OpenCATS/Tests/IntegrationTests/TaskTest.php`: focused regression coverage.
- `docs/crm/stage-2-backend.md`: dependency, API, security and verification report.

Final working tree: five tracked files modified and the three new files above
untracked. The pre-existing untracked `AGENTS.md` is untouched. No branch change
or new commit was made.

## Backend contract

`lib/Tasks.php` provides `add($values)`, `get($id)`, `update($id, $changes)`,
`getAll($filters)`, `getCount($filters)` and `getHistory($id)`.
Mutable fields use existing camelCase model aliases: `title`, `description`,
`dueDate`, `priority`, `status`, `purpose`, `assignedTo`, `dataItemType`,
`dataItemID` and `candidateJobOrderID`. Omitted fields retain their values on
update. Unknown fields, array-shaped scalar values and invalid IDs are rejected.
Creator, timestamps and completion actor are never accepted from input.

Due dates are nullable ISO calendar dates, with no timezone conversion. Text is
plain text; future templates must escape it at output boundaries. The public
choice methods return stable codes and display labels. Defaults are Open,
Normal and General. Closed work must return to Open before another transition;
reopening clears current completion metadata while retaining history. Completing
work never inserts Activity.

A parent is a single nullable type/ID pair using the existing four entity types.
Standalone work requires an assignee. Linked work may be unassigned. Existing
assignments to disabled users survive; new assignments must pass the existing
active-user eligibility rule. Candidate–job context uses
`candidate_joborder.candidate_joborder_id`, with both records authorised. When
the primary parent is a Candidate or Job Order, its ID must match that context.

## Reused boundaries

- `DatabaseConnection` query quoting and transaction methods; InnoDB Task and
  History writes share a transaction, with the current Task locked on update.
- `History::storeHistoryNew()`, `storeHistoryChanges()` and `getAll()`. The write
  methods now return their query result so Task writes can roll back on audit
  failure; existing callers and positional signatures are preserved.
- Session `getAccessLevel()` / existing `ACL` inheritance, with Task action keys
  `tasks.show`, `tasks.list`, `tasks.add`, `tasks.edit`, `tasks.complete`,
  `tasks.cancel`, `tasks.reopen`, and `tasks.admin`. These require the ordinary
  READ, EDIT or SA levels; administrator scope also checks real access level.
- Existing parent model `get()` methods plus their module `.show`/`.edit`
  permissions. Company/Contact have no invented owner-only visibility rule.
  Candidate visibility reuses `CandidateAuthorization::canAccessCandidate()`;
  Job Order visibility uses the existing `joborders.hidden` SA check.
- `Pipelines::canAccess()` for both sides of optional context; `Users::get()`
  and the same `accessLevel > ACCESS_LEVEL_DISABLED` eligibility as user lists.
- `CATSSchema::get()` / `ModuleUtility::processModuleSchema()` and native
  `dumpDB()` rather than separate migration or backup mechanisms.

Assignees with Task edit permission and parent visibility can edit ordinary
fields and complete/cancel/reopen without parent edit permission. They cannot
reassign or relink solely by being the assignee. Creators can manage with the
applicable parent edit permission; authorised existing administrators can manage
within parent visibility. New parent links require access to that parent.
Linked read access is constrained by the parent, while standalone read access
is creator/assignee/administrator only. Task `.show` and `.list` restrictions
also apply to list results and counts. History access checks former parent and
pipeline references too, so relinking cannot disclose inaccessible old context.

Existing parent deletions and pipeline removal leave Task records intact but
unavailable through the backend. No automatic completion, reassignment, cascade
or orphan recovery workflow is introduced. Removed/restricted pipeline context
makes its Task unavailable; this is deliberately fail-closed.

## Schema and recovery

Fresh installs and migration 399 create the same singular `task` table and
indexes `(assigned_to, status, due_date)` and
`(data_item_type, data_item_id, status)`. No site column or ExtraFields dependency
is added. Retry preserves existing Task data and restores missing expected
indexes. An incompatible pre-existing Task table raises an error without
advancing the install revision; it is not silently repurposed or destroyed.

Native backup discovers Task data automatically. Its historical omission of
`history` data is narrowed for `DATA_ITEM_TASK` only, preserving Task audit facts
through recovery without changing backup policy for other entity history.
Rollback of application code must retain the new table and Task history; no
schema down-migration or production migration has been run.

## Scope and limitations

No HTTP endpoints, UI, Calendar integration, Activity workflow, warnings or
analytics were added. CSRF and browser testing therefore have no new HTTP
surface in this stage. Stage 3 must use the scoped backend and existing request
protection and escaping facilities.

Lists apply validated SQL filters, then existing record-visibility helpers
before exposing rows or counts. They return the complete authorised result;
there is no pre-authorisation pagination or unscoped count. This favours reuse
and correctness over a second SQL implementation of record ACL. Large-result
performance and DataGrid integration remain Stage 3/5 work, not a claim that
this backend has been profiled for dashboard workloads.

## Verification

Focused execution uses PHP 8.5.10 in a disposable copy at
`/tmp/crm-stage2-backend` on `crm-stage1c85-php-1`, with a separately named
`cats_stage2_backend` integration database. The existing application database,
configuration, local `AGENTS.md` and existing test data are untouched.

Command (inside the disposable copy):

```sh
php vendor/bin/phpunit --bootstrap task-bootstrap.php --stop-on-error --display-warnings --display-deprecations src/OpenCATS/Tests/IntegrationTests/TaskTest.php
```

The temporary bootstrap defined `DATABASE_NAME` as `cats_stage2_backend`, then
loaded the repository test bootstrap. The test user received a temporary grant
for only that disposable database. No database was migrated in the application.

Result: **12 tests, 203 assertions, exit 0**, in 64.432 seconds. All selected
behaviour, security, install/upgrade/retry, audit-rollback and backup round-trip
checks passed. PHPUnit reported three duplicate-constant warnings from the
copied test configuration (`DATABASE_HOST`, `DATABASE_NAME`, `HTML_ENCODING`)
and 21 PHP 8.5 deprecations in unchanged legacy files (old cast spellings and
one switch-case delimiter). These diagnostics were not suppressed or repaired
as unrelated Stage 2 work. Earlier attempts stopped at a missing disposable-DB
grant and a missing required Contact fixture field; both were corrected before
the passing run. No previous-stage test results are claimed as Stage 2 evidence.

Syntax checks passed for all six changed/new PHP files; `git diff --check`
passed. Changes remain uncommitted; no push, PR, merge or deployment occurred.
The disposable database grant and code copy were removed after verification.

No PHP 8.4.1,
full PHPUnit/integration/security/browser suite or CI matrix is part of this
handoff. PHP 8.4.1 remains a supported runtime; normal CI is a separate gate.
