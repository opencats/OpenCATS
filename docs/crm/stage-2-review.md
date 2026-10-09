# Stage 2 review — 9 October 2026

Scope: verification and minimal correction of the uncommitted Stage 2 backend
against feature plan v0.7 (Section 4 and all eight Section 5 Stage 2 acceptance
criteria) and `stage-2-backend.md`. Starting branch/HEAD remains
`feature/crm-desk-foundation` / `f66ed1459652d7885b81691c72751e40a0f65bb9`.
No Stage 3 work, commits, pushes, merges or PRs.

## Findings by severity

### P2 — fixed: migration 399 rejected equivalent MySQL integer metadata

The schema guard compared `SHOW FULL COLUMNS` types literally with `int(11)`.
MySQL versions that report `int` therefore failed the migration at `task_id`,
even though their integer representation is equivalent. This metadata behaviour
is documented in the [MySQL 8.0.19 release notes](https://dev.mysql.com/doc/relnotes/mysql/8.0/en/news-8-0-19.html).

A regression using real schema data with the integer display widths removed
reproduced `Existing Task column is incompatible: task_id` before correction.
The two-line production correction normalises only plain `int` to `int(11)`
before validation. Unsigned/different-width storage types, nullability,
defaults, engine and index checks remain unchanged. No schema data is rewritten.
The compatibility case is simulated metadata over MariaDB, not a claim that a
MySQL server was provisioned or exercised.

### P2 — remaining scalability concern: per-row authorisation and repeated counts

`Tasks::getAll()` first materialises all SQL-filtered Task rows, including their
descriptions. Each visible linked row calls its existing parent model, and
pipeline context adds a `Pipelines::canAccess()` query. From code inspection,
with L linked rows whose module permission passes and P authorised context
checks, this is approximately `1 + L + P` SELECTs per list call. Repeated Tasks
on the same parent are not deduplicated. Standalone rows have no parent query.
`getCount()` calls `getAll()` and repeats the same work and memory allocation;
calling both does not share a result.

Returned rows and counts use the same authorisation boundary, so the concern is
cost, not an observed disclosure. Existing composite indexes narrow supported
filters but cannot remove repeated parent reads. No pagination, large-data
benchmark, SQL ACL duplication, cache or new ACL abstraction was introduced.
Before dashboard-scale use, profile representative data and preserve the same
scope before pagination/counting. This review deliberately leaves that work
outside Stage 2's minimal correction.

No P0/P1 defect was identified in the reviewed paths.

## History compatibility and transactions

All production callers of the three changed History methods were inspected:
Companies, Contacts, Candidates, JobOrders, CompanyRepository and
JobOrderRepository. They ignore the return value. Method names, parameters,
SQL, loose change comparison, dateModified exclusion and stored text conventions
are unchanged. `storeHistoryChanges()` now returns true for no change; successful
writes return the database outcome; `storeHistoryNew()` forwards the categorised
write result. There is no added History commit, rollback or transaction start.
The existing CompanyRepository regression also covers its mocked History call.

Task writes use the existing DatabaseConnection transaction methods and lock
the current Task during update. Coverage includes exceptions raised by a
history trigger, explicit false history returns, and creation/update rollback.
Additional coverage verifies existing Company history rows remain under their
caller's transaction, no-op history adds no rows, and a Task update rejects an
already-active caller transaction without committing or rolling it back.

These guarantees assume the normal InnoDB schema. Legacy installations must
complete their existing engine-conversion migrations; custom nontransactional
History tables and connection-loss/commit-acknowledgement failure are not
validated by this review. The shared transaction implementation was not changed.

## Authorisation review

Linked Tasks require current parent visibility, Task read/action permission,
and both visible records for pipeline context. Missing parents or removed
pipeline context fail closed, including history/list/count access. Company
ownership is not treated as an ACL. No Desk, Sector or Task assignment bypasses
parent visibility.

The creator/assignee/administrator mapping in the implementation report remains
unchanged: an assignee may edit ordinary fields and complete/cancel/reopen with
parent READ and Task EDIT/action permission; reassignment and relinking require
creator-management or appropriately privileged administrator scope. Destination
links are checked again. Standalone Tasks remain creator/current-assignee/admin
only, including after relinking from a shared parent or reassignment away from a
previous assignee. Former linked parents and pipeline references are checked
before returning history. Closed Tasks retain authorised read access.

Tests cover cross-user denial, effective admin restriction, hidden/missing
parents, parent action denial, pipeline visibility on both sides, attempted
assignee reassignment/relinking, creator changes, standalone loss of access,
former context restrictions and list/count agreement. No production change to
Task authorisation was necessary.

## Schema, older backups and existing behaviour

The fresh-install schema and migration 399 match in columns and indexes. Tests
cover fresh/upgrade parity, retry after a missing index, repeated retry without
data loss, and incompatible-table rejection without version advancement.

A revision-398-format native backup is constructed with no Task table or Task
history. The test drops all disposable tables (as the install reset does),
replays the native `((ENDOFQUERY))` SQL chunks, confirms preserved Company data,
empty historical History and revision 398, then executes migration 399 and
creates a usable Task. This exercises old SQL backup format plus upgrade; it is
not a ZIP extraction/browser test or certification of every older installation
revision. Restoring into a clean/reset database is the tested path; overlaying
an old backup onto existing newer operational data is not endorsed.

Current backup round-trip preserves Tasks, NULL values and Task audit rows.
Other entity history is still deliberately omitted, matching previous native
backup policy. The existing Sector backup regression is also selected to check
non-Task data and nullable/escaped values.

Activity, ACL, CandidateAuthorization, Pipelines, parent models, configuration
and AGENTS.md have no Stage 2 modifications. Ordinary Task completion adds no
Activity. Pipeline lifecycle/removal does not automatically alter Task status.
History's new return values and backup's inclusion of Task audit rows are the
intentional shared changes; other stored-history/backup policies are preserved.

## Acceptance criteria and remaining gaps

| v0.7 Stage 2 criterion | Implementation/evidence |
| --- | --- |
| Four optional parent types; standalone/unassigned linked work; validated IDs and assignees | Implemented; focused persistence/input/disabled-user cases |
| Parent and Task permissions; assignee READ-parent updates; standalone privacy; scoped lists/counts | Implemented; positive/negative cases plus cross-user/relink review |
| Nullable date-only due date; priorities/statuses; completion/reopen; auditable date/status/reassignment | Implemented; round-trips, no-op completion and both audit-failure paths |
| Seven purposes; General default; no automatic Activity | Implemented; purpose validation and unchanged Activity count |
| Optional existing pipeline identity; both parents authorised; no automatic Task state changes | Implemented; hidden/denied/removed context cases |
| Parent deletion/privacy and disabled-user retention | Implemented; actual Candidate and cascading Company delete paths |
| Task backup/restore | Implemented; Task/history round-trip and pre-Task format restoration/upgrade |
| Focused verification | Expanded review additionally exercises explicitly requested PHP 8.4.1 |

No mandatory Stage 2 backend operation in these criteria is identified as
unimplemented. Native Tasks have no UI, HTTP actions, hard-delete workflow,
Calendar integration, warning matching or dashboard. Those are not missing
Stage 2 deliverables: lifecycle closure uses Completed/Cancelled, and later
stage UI/rules remain deferred. CSRF and rendering/escaping must be enforced at
future HTTP/template boundaries; this backend-only review does not certify
unimplemented endpoints. Large-result efficiency and full archived/production
recovery remain validation limitations, as described above.

## Verification results

All final selected runs exited 0. No passing checks were repeated for reassurance.

| Check | PHP 8.4.1 | PHP 8.5.10 |
| --- | --- | --- |
| TaskTest (17 methods) | 17 tests, 376 assertions; 162.825 s | 17 tests, 376 assertions; 163.129 s |
| Selected existing regressions | 18 tests, 551 assertions; 0.486 s | 18 tests, 551 assertions; 0.554 s |
| Existing Sector backup round-trip | 1 test, 18 assertions; 9.533 s | 1 test, 18 assertions; 9.529 s |
| PHP syntax (six changed/new PHP files) | All passed | All passed |

Total per runtime: **36 tests, 945 assertions**. The metadata regression before
the correction failed as expected on PHP 8.5.10 (1 test, 0 assertions, 1 error);
the final TaskTest runs include its passing result.

Diagnostics were retained: TaskTest and Sector backup each reported three
copied-config duplicate-constant warnings on both runtimes; each also reported
21 unchanged legacy deprecations on PHP 8.5. The existing regression selection
reported four PHPUnit notices on both runtimes and 23 deprecations on PHP 8.5.
No unrelated legacy/configuration cleanup or suppression was applied.

Tests ran in `/tmp/crm-stage2-review` within existing containers
`crm-stage1c-php-1` (8.4.1) and `crm-stage1c85-php-1` (8.5.10). Their separate
integration DB containers used disposable `cats_stage2_review` databases. The
temporary bootstrap defined that database name and loaded the normal test
bootstrap. The code copies and temporary DB grants were removed afterward;
logs remain outside the checkout in `/tmp/crm-stage2-review-logs`.

Commands inside each test copy:

```sh
php vendor/bin/phpunit --bootstrap task-bootstrap.php --display-warnings --display-deprecations src/OpenCATS/Tests/IntegrationTests/TaskTest.php

php vendor/bin/phpunit --filter 'testNoNewDuplicateMigrationKeys|testMigrationKeysAreInAscendingOrder|test_persist_|testMissingPipelineCannotBeRated|testPipelineDetailsCannotLeakHiddenCandidateNotes|testMissingCandidateCannotBeAdded|testPipelineFiltersHiddenCandidatesBeforePagination|ExistingInstallFlowTest::' src/OpenCATS/Tests/UnitTests/SchemaMigrationsTest.php src/OpenCATS/Tests/UnitTests/CompanyRepositoryTest.php src/OpenCATS/Tests/UnitTests/ExistingInstallFlowTest.php src/OpenCATS/Tests/UnitTests/ActivityPipelineAuthorizationTest.php src/OpenCATS/Tests/UnitTests/JobOrderPipelineAuthorizationTest.php

php vendor/bin/phpunit --bootstrap task-bootstrap.php --filter testNativeBackupRestoresSectorDefinitionsAndNullableAssignments src/OpenCATS/Tests/IntegrationTests/SectorTest.php
```

`php -l` covered constants.php, lib/Tasks.php, lib/History.php,
modules/install/Schema.php, modules/install/backupDB.php and TaskTest.php on
both runtimes. `git diff --check` passed. No full suite, browser run, complete
permission matrix, production migration or deployment was performed.

## Review delta and final working tree

This pass changed only migration 399's integer metadata comparison, added five
focused methods to TaskTest, linked the original report to this review, and
added this review report. No further History, Task model, backup or ACL code
correction was required.

The original Stage 2 changes remain uncommitted: five tracked files modified
(constants.php, db/cats_schema.sql, lib/History.php, modules/install/Schema.php,
modules/install/backupDB.php), plus lib/Tasks.php, TaskTest.php and the two
report documents untracked. The pre-existing untracked AGENTS.md is untouched.
Branch and HEAD are unchanged; nothing was staged, committed, pushed or opened
as a PR. No prior-stage test passes were substituted for this review's runs.
