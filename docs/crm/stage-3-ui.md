# Stage 3 — native Task UI

Baseline: `feature/crm-desk-foundation`, committed Stage 2 at
`35b678552cac5f58ea88b2e1665984c879e590e7`. Reviewed feature plan v0.7
(8 October 2026), particularly Sections 4 and 5, and both Stage 2 reports.
Stage 3 remains uncommitted; the pre-existing untracked `AGENTS.md` is untouched.

## Delivered and reused

- Native Tasks navigation, My Tasks (current recruiter/open work by default),
  accessible-work list, Add/Edit/Show, assignment, Complete/Cancel/Reopen,
  success/error feedback and escaped audit history.
- Filters: assignee/Unassigned/all accessible, status/open work, priority,
  purpose, parent type/record, overdue/today/upcoming/undated and due-date range.
  Today uses existing `DateUtility::getAdjustedDate()` session-date conventions;
  stored due dates remain unchanged calendar dates. Default order is earliest
  due first, then undated, with Task ID as a stable tie-breaker.
- One shared open-Task panel on Company, Contact, Candidate and Job Order Show
  pages, with preselected creation and a link to all statuses for that parent.
- Existing `UserInterface`, module discovery/tab ordering, `Template`, Bootstrap
  cards/forms, `Users::getSelectList()`, Session CSRF and DataGrid rendering,
  sorting and pagination. No new dependencies; contextual creation uses the shared Task JavaScript described below.
- Task DataGrid uses a small protected `DataGrid::loadRows()` extension point.
  The default retains the existing SQL/FOUND_ROWS behaviour. The Task subclass
  obtains one authorised `Tasks::getAll()` collection and derives its count and
  page from that collection, including page correction. It never calls
  `getCount()`, builds a second SQL ACL, or persists an authorisation cache.
- Backend additions expose existing management permissions, parent labels,
  authorised existing pipeline suggestions and exact Activity handoff context.
  `update()` reuses its extracted, unchanged management predicate and still
  rechecks the locked record. Persistence, lifecycle and migration 399 are unchanged.

## Permission and request boundaries

All Task reads/writes use `Tasks`. Assignee ordinary edits retain parent-read
requirements without acquiring reassignment/relinking rights. Creator/admin
management, standalone privacy, hidden/missing parents, both pipeline records,
former-history associations and disabled-assignee rules remain backend-owned.
Permission hints only control presentation; forged association/assignment fields
still reach backend validation. Unknown edit fields and unexpected lifecycle
fields are rejected. Writes require POST and the existing Session CSRF check;
array-shaped fields/IDs and invalid identifiers are rejected. Task text, user
labels, links and history use existing Template escaping helpers.

## Activity handoff and plan reconciliation

v0.7 explicitly requires **Complete & Log Activity**, despite the shorter brief's
general deferral of Activity creation. Implemented that requirement as a handoff:
completion redirects to a confirmation with the existing Activity modal link.
Candidate/Contact parents use their existing workflow; a pipeline context retains
its exact Candidate and Job Order. A Company without pipeline context requires
choosing a Contact on its parent page; a Job Order without context requires
choosing a Candidate in its pipeline. Standalone work without context has no
interaction target. Activity action permissions still apply.

No Activity is submitted by the Task handler. Repeated completion submissions
therefore cannot create duplicate Activities. Cancelling/failing the existing
Activity form leaves the Task completed and clearly says that no Activity was
recorded by completion. The existing Activity form owns its own submissions;
no cross-module transaction or new Activity deduplication framework is added.
No Calendar integration, reminders, warnings, dashboards or analytics are added.

## Changed files

- `constants.php`
- `lib/DataGrid.php`, `lib/Tasks.php`
- `modules/{companies,contacts,candidates,joborders}/Show.tpl`
- `modules/tasks/TasksUI.php`, `modules/tasks/dataGrids.php`
- `modules/tasks/{Header,Footer,Error,List,Form,Show,Parent,ParentRows,QuickAdd}.tpl`
- `js/tasks.js`, `modules/tasks/tasks.css`
- `src/OpenCATS/Tests/IntegrationTests/TaskUITest.php`
- `src/OpenCATS/Tests/UnitTests/TaskDataGridLoadingTest.php`
- `docs/crm/stage-3-ui.md`

No schema, migration, configuration, existing ExtraFields or application data changes.

## Focused verification — PHP 8.5.10 only

Used the existing `crm-stage1c85-php-1` container, a disposable code copy
`/tmp/crm-stage3-ui`, and separate `cats_stage3_ui` database on its integration
DB container. The application's databases/configuration were not modified.
The temporary database/grant and code copy were removed after verification.
Logs remain outside the checkout at `/tmp/crm-stage3-ui-logs`.

All runs exited 0; results below are executions, not additive unique-test totals.

| Execution | Result |
| --- | --- |
| Initial TaskUITest + TaskDataGridLoadingTest | 8 tests, 247 assertions |
| Affected checks after grid-property/date/pipeline/request corrections, plus two existing Task permission regressions | 7 tests, 177 assertions |
| Rendering check after readable history labels | 1 test, 36 assertions |
| Standalone pipeline Activity handoff permission rendering | 1 test, 8 assertions |
| Syntax: all 18 changed/new PHP and PHP-template files | Passed; subsequently edited files checked again |
| `git diff --check` | Passed |

Initial output included 17 new Task-grid dynamic-property deprecations; explicit
property declarations removed them. Final focused runs report three duplicate
constant warnings from copied test configuration and 21 deprecations in unchanged
legacy code. These were retained, not suppressed or fixed out of scope.

Initial passing create/edit/lifecycle, cross-user/relink/parent visibility,
history/disabled-assignment, and default DataGrid SQL-loader checks are carried
forward without unnecessary reruns. The affected selection was:

```
testCsrfMalformedIdsInputsAndTransitions
testScopedGridDatesCountsPagingAndEscapedRows
testTemplatesEscapeTextAndExposeNativeForms
testPipelineSuggestionsAndActivityHandoffRespectBothParents
testTaskGridLoadsAuthorisedCollectionOnlyOnceIncludingPageCorrection
TaskTest::testAssigneeCanCompleteWithParentReadButCannotReassign
TaskTest::testStandalonePrivacyCreatorManagementAndActionDenial
```

Commands used `php vendor/bin/phpunit --bootstrap task-ui-bootstrap.php
--display-warnings --display-deprecations`, the listed test files, and `--filter`
for the affected selections. The bootstrap selected only `cats_stage3_ui`.

## Acceptance and remaining verification

The five functional v0.7 criteria are implemented: Calendar-independent task
management; consistent linked-record status/assignee/date; shared four-parent UI;
closed work excluded from open queues but inspectable; no invented interactions.
Focused handler/security/rendering checks pass. The plan's live browser-workflow
criterion remains pending maintainer verification, following the explicit brief's
browser-suite restriction. No PHP 8.4 or broad suite was run.

Static disposable rendered previews were visually inspected: Add/List at desktop
width and Edit/Show at 390px. Task fields align and stack using existing Bootstrap
breakpoints. This does not certify live navigation, modal submission, parent-page
customisations or the entire application's responsive header.

Known limits and manual checks:

- Large lists still materialise SQL-filtered authorised rows and repeat backend
  parent checks. Date/type-only filtering then occurs in memory; parent panels
  show all authorised open linked Tasks. No performance benchmark was performed.
- General parent selection uses type/record ID; record-page Add Task preselects
  both. Pipeline ID suggestions use the current saved/preselected Candidate or
  Job Order. Changing parent fields does not asynchronously reload suggestions;
  backend validation remains authoritative.
- Refresh the module cache using existing maintenance procedures and sign in
  again if an existing session/cache does not yet discover Tasks.
- Verify real-user Add/Edit/Show/list and lifecycle flows, including read-only,
  creator, assignee and administrator roles; hidden parents and both pipeline
  sides; date boundaries; inactive users; and validation recovery.
- Verify actual Activity modal success, cancellation and failure, exact pipeline
  preselection, and existing submission behaviour. Completion and Activity are
  explicitly separate operations.
- Inspect the Task panel within all four real parent pages, including existing
  ExtraFields/customisations and responsive breakpoints. Maintainer retains full
  regression, browser and PHP 8.4.1 compatibility gates.

## Contextual Quick Add refinement

The four parent panels now open one shared Bootstrap 5 modal without navigation.
`QuickAdd.tpl` uses modal-lg/centered/scrollable, an 85vh content cap, 14px labels,
compact controls, three Description rows and a persistent Cancel/Save footer.
Due date/priority share a row above assignment; narrow screens stack fields.
Purpose/status/pipeline live in the initially collapsed More options section.
The existing full-page form is unchanged.

`Tasks::getParentContext()` reuses backend parent/action checks before returning
actual names, secondary IDs and authorised Company labels for Contacts/Job Orders.
Parent identifiers remain hidden and are revalidated on POST; they are not an
access boundary. Backend defaults are exposed through `Tasks::getDefaults()` and
used by its existing validator and the modal, without alternative persistence.

`js/tasks.js` reuses the already bundled Bootstrap modal/collapse assets and
same-origin fetch conventions. Failed saves retain the form DOM and entered
values, show an in-modal error and focus it. Successful saves close the modal and
replace only the originating panel's escaped, authorised open-Task rows. Duplicate
in-flight submissions/dismissal are blocked; ambiguous network failure does not
retry automatically. If saving succeeds but list refresh fails, the response
still reports success and asks for a page refresh. Bootstrap supplies keyboard
trapping; title autofocus and restoration to the originating link are explicit.

Refinement-only automated verification, run once in the existing PHP 8.5.10
container and disposable database: `--filter testQuickAdd TaskUITest.php` —
**4 tests, 156 assertions, exit 0, 6.856 seconds**. Coverage includes all four
contexts, Company-label visibility, hidden parents/pipelines, Task/parent action
permissions, CSRF, malformed IDs/fields, default persistence and escaped/scoped
refresh HTML. Six affected PHP/template files passed syntax checks;
`git diff --check` passed. Three copied-config warnings and 21 existing legacy
deprecations remain; no unrelated warning investigation or test reruns occurred.
Earlier Stage 3 passing results above are retained, not rerun for this refinement.

Disposable static previews confirmed desktop sizing, 390px stacking/body scroll,
visible footer, global blue select borders, initial focus, More options and
Escape/focus restoration. This was a small manual preview inspection, not a
browser suite or a live application save test. Maintainer should verify live
save/refresh, retained values after rejected/expired-session submissions, all
four real parent pages, and mobile keyboard behaviour.

The approved `main.css` rule `select:not(:disabled) { border-color:
var(--bs-primary); }` is intact; no competing modal select rule exists. Checksums
confirmed `main.css`, `config.php` and `AGENTS.md` were unchanged during this
refinement. Unrelated `reports/local/` files were left untouched. Everything
remains uncommitted on the original branch; master was not modified.

## Configured dates and readable grid parents

Task lists/panels, Add/Edit, Quick Add, Show, history (including previous/new due
values), and date-range filters now use the installation/session date convention.
The small shared `modules/tasks/TaskPresentation.php` adapter delegates validation
and conversion to existing `DateUtility`; `DateInput.tpl` and `js/tasks.js` reuse
`DateInputForDOM` with the same configured format as existing OpenCATS forms.
No global date helper or picker was modified. Invalid submitted dates remain
editable with their original text; valid due dates reach the existing Task model
as ISO calendar dates without timezone conversion. Timestamp presentation uses
existing configured date/time and session-offset conventions. The full-page form
retains its normal control size; contextual controls remain compact.

The main grid now displays escaped authorised parent names and secondary typed
IDs, with existing Show links and authorised Company names for Contact/Job Order
parents. Standalone work is labelled Standalone. It calls `getParentContext()`
for visible rows, reusing each distinct context within that grid instance; denied
or disappeared contexts never fall back to unverified names or links. Existing
backend visibility checks, numeric parent sorting, date sorting, filtering,
counts and pagination remain unchanged. No persistent permission cache is added.

Files changed for this refinement: `modules/tasks/{TasksUI.php,dataGrids.php,
TaskPresentation.php,DateInput.tpl,Header.tpl,Form.tpl,QuickAdd.tpl,List.tpl,
Show.tpl}`, `js/tasks.js`, the two existing Task UI/grid test files, and this report.
No schema, migration or additional backend persistence changes.

One focused PHP **8.5.10** run passed: **8 tests, 285 assertions**, 19.108 seconds.
Selected tests: `testConfigured*`, `testGridParentNames*`, `testTaskGrid*`,
`testCreateEditLifecycleAndActivityHandoff`,
`testScopedGridDatesCountsPagingAndEscapedRows`, and
`testQuickAddContextAndCompactTemplateForAllParents`. Coverage includes both
configured formats, leap-date parsing, date-only storage despite timezone offset,
invalid-value retention, clearing dates, modal CSRF and permission rejection,
date ranges, rendered forms/details/history, all four linked parent labels,
escaping, denied Company associations, hidden parents and repeated-parent reads.
All 11 affected PHP/template files passed PHP 8.5 syntax checks. `git diff --check`
passed. The same three copied-config warnings and 21 legacy deprecations were
reported; passing tests were not repeated. Other prior focused results above are
carried forward. No full suite, Behat, browser suite or PHP 8.4 test was run.

The existing PHP 8.5 container was reused with a disposable code copy and database;
no test environment was provisioned. Logs: `/tmp/crm-stage3-ui-logs/dates-parent-*`.
The disposable copy and database/grant were removed. Live date-picker rendering,
keyboard interaction, mobile layout and live save/refresh remain maintainer browser
checks for this refinement. Checksums again confirm `main.css`, `config.php` and
`AGENTS.md` unchanged, including the approved blue dropdown rule. All prior dirty
and untracked files remain intact. Branch: `feature/crm-desk-foundation`; no commit,
staging, push, PR or master changes.
