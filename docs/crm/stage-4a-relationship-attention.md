# Stage 4A — Relationship Attention

Branch: `feature/crm-desk-foundation`. Baseline:
`ad7fd613c07db89e8e6c76c4003b05874815079f`; Stage 3 UI is committed in
`a8539a2`, followed by the committed dropdown CSS refinement in `ad7fd61`.
Stage 4A and the revised Stage 4A-1 are delivered together in one local commit,
as authorised by the maintainer on 10 October 2026. No master changes, rebase,
merge, push or PR.

Reviewed the approved v0.7 plan (8 October 2026), particularly Sections 4, 5,
6 and 16, at `/home/russh/Downloads/OpenCATS-CRM-Feature-Plan-v0.7.md`.
Its decision register was updated in place for the latest Stage 4A brief;
previous Stage 1–3 business decisions were preserved. This external roadmap
is not tracked in this repository.

## Delivered scope and important limitation

Administrator Settings and reusable, current-owner Company attention results
are implemented. No dashboard, recruitment warnings, automatic Tasks,
notifications, lifecycle changes, acknowledgement/snooze or persisted derived
flags were added.

**Stage 4A-2 decision, 10 October 2026:** ordinary Contact Activities now supply
Company recency through each Contact's current Company, following
`ActivityEntries::getAllByCompany()`. The maintainer explicitly accepts that an
employer transfer can move earlier interaction evidence to the new Company.
Contacts marked `left_company = 1` are excluded from recency, although the broader
Company Activity display includes them. Explicit Company-linked evidence remains
supported. No historical employer reconstruction is attempted. Absence of a
qualifying Activity does not prove a Company has never been contacted.

## Approved defaults and evaluation

- Both tier and lifecycle must be enabled. Defaults: A/14, B/30, C/60 calendar
  days; D and Unclassified disabled. Default lifecycles: Fresh Prospect,
  Prospect, Engaged and Client; Dormant, Lost and Unclassified disabled.
- Existing Company Settings stores configuration separately from tier labels.
  Canonical codes drive eligibility and sorting even after relabelling.
  Administrators can change tier/lifecycle eligibility and intervals. Enabling
  D/Unclassified requires entering an interval; no business default is invented.
- Server validation requires every recognised key, scalar boolean/0/1 flags and
  positive integral intervals (technical bound 1–36500). Disabled intervals may
  be NULL. Invalid settings fail closed rather than silently being reinterpreted.
- Existing native settings rows (12 values, each within VARCHAR(255)) are saved
  together using DatabaseConnection transactions and delete/insert conventions.
  Defaults apply on fresh installs and upgrades without a migration. Neither
  labels nor Company classifications, Activities or Tasks are rewritten.
- `Companies::getRelationshipAttention()` returns one row per eligible Company
  owned by the signed-in user, requiring an existing enabled owner. No cross-owner
  selector, management/unassigned queue, reassignment or Desk-derived access is
  introduced. Task assignment is independent of Company ownership.
- Relationship follow-up coverage requires an authorised Open/In Progress Task
  with purpose `relationship_follow_up`, a valid calendar due date no later than
  today plus the tier interval, and a Company parent or current Company Contact
  with `left_company = 0`. Task links describe current work, not historical contact.
- All overdue dates count as commitments. Undated, completed/cancelled, unrelated,
  generic/administrative and beyond-window Tasks do not supply timely coverage.
  Current Task state is evaluated anew after completion/rescheduling. Excluded
  Companies do not change existing My Tasks behaviour.
- Reasons are ordered `overdue_task`, `due_today`, `no_next_action`,
  `stale_relationship`, `upcoming`. All applicable reasons are returned alongside
  the primary reason. No next action means no qualifying dated commitment,
  including overdue commitments. Scheduling never clears stale contact evidence.
- Known contact is stale when calendar age **exceeds** the interval (14 is fresh
  for A; 15 is stale). Unknown contact has NULL date/age and the label **No recorded
  contact**, placed in the stale/contact-review group without inventing a date.
  No extra no-task grace period is introduced.
- Group sorting: reason, canonical A/B/C/D/Unclassified, earliest relevant due
  date or oldest contact date, then numeric Company ID. Unknown contact sorts
  before known contact dates within its group. Calendar today and Activity-date
  conversion reuse `DateUtility::getAdjustedDate()`; Task due dates remain ISO
  date-only values without timezone conversion.

## Existing mechanisms reused

`CompanySettings::getAll()/formatTier()` and native Settings administration,
Bootstrap form rows, Template escaping, Session CSRF validation, module/action
access levels, existing `company.owner`/user disabled status, Company and Contact
record conventions, `Tasks::getAll()` and its existing `canRead()`/parent/pipeline
checks, Activity type constants, `DateUtility`, and `DatabaseConnection` quoting,
queries and transactions. Existing test harnesses/fixtures are extended.

A narrow `relationshipCompanyIDs` Task filter selects the relevant Company and
current Contact parents before the existing authorization pass. No parallel
Task collection, repository, ACL, paging, rule engine or Activity implementation
was created. Existing Task filter callers retain their behaviour.

## Activity evidence and accepted current-employer attribution

Only Call (Talked), type 500, and Meeting, type 300, qualify. Email (200), Not
reached (100), Other (400), voicemail (600), missed call (700) and Status Change
(800) do not. Meeting means a recorded meeting, not independently verified
attendance. Generic Email has no reliable engagement/bulk distinction. Notes,
Tasks and Task completion never establish contact; transfer notes remain Other.

`ActivityEntries::getRelationshipContactDates()` aggregates two sources in one
query: explicit `DATA_ITEM_COMPANY` Activities and `DATA_ITEM_CONTACT` Activities
joined through `contact.company_id`, with `contact.left_company = 0`. Each source
returns a maximum per requested Company, then UNION ALL and an outer MAX select
one latest date per Company. The same current-owner Company scope restricts both
branches; no complete histories are loaded and no per-Company query is issued.

Both sources retain the Company creation-date lower bound and exclude sentinel,
invalid calendar and future timestamps. Existing DateUtility timezone conversion,
calendar-day ages and strict greater-than staleness boundaries are unchanged.
Unknown contact has NULL date/age. `contactEvidenceScope` identifies both sources
and explicitly states that historical employer attribution is not retained.

The association follows the existing `getAllByCompany()` convention. That display
query remains unchanged and still includes departed Contacts; recency deliberately
requires current Contacts, just like Stage 4A's follow-up Task matching. Moving a
Contact can cause older calls/meetings to contribute to the new Company (subject
to the date safeguards). This is an intentional maintainer-approved trade-off,
not a claim that the interaction happened during that employment. Transfer
logging provides a human-readable record but does not set attribution boundaries.

The existing direct Company Activity-add path still calls missing
`Companies::updateModified()` after insertion and is not a supported capture
workflow. Existing explicitly Company-linked records remain readable evidence;
normal recruiter Contact logging now contributes without additional workflow.
More sophisticated attribution requires a demonstrated business requirement and
separate approval. No migration, snapshot, employment period or backfill is added.

## Permissions and result contract

No rows are returned without an authenticated positive user ID and read access
to `companies.show`, `contacts.show`, `tasks.list`, `tasks.show` and
`activity.listByViewDataGrid`. Unavailable source permissions do not become false
missing-task/contact claims. Current Company/Contact models have no additional
hidden-record rule; existing Task authorization checks secondary pipeline access
before evidence is returned. The Activity helper independently checks Company and
Activity read permissions and joins existing Company records.

Settings dispatch and persistence require real administrator access plus
`settings.relationshipAttention`; POST requests validate the existing CSRF token.
Configuration rejects array-shaped scalar inputs, invalid/missing/extra keys,
invalid flags and intervals. The Settings template escapes labels and values.

Results include Company ID/name/owner, canonical tier/lifecycle, tier label,
review interval, primary/ordered reasons, nullable lastContact/contactAgeDays,
contact label/evidence scope, relevant Task ID/title/dueDate/assignedTo, and the
sorting date. Text is raw model data: consumers must use existing escaping and
configured date presentation. No rendered dashboard or count endpoint exists.

## Files and schema

- `lib/CompanySettings.php`: defaults, validation, native settings persistence.
- `lib/Companies.php`: owned, authorised derived attention results.
- `lib/ActivityEntries.php`: grouped explicit Company and current-Contact evidence query.
- `lib/Tasks.php`: SQL filter for eligible Company/current Contact parents.
- `modules/settings/SettingsUI.php`, `Administration.tpl`,
  `RelationshipAttention.tpl`: administrator route, navigation and native form.
- `src/OpenCATS/Tests/IntegrationTests/RelationshipAttentionTest.php` and
  `src/OpenCATS/Tests/UnitTests/RelationshipAttentionAuthorizationTest.php`.
- This report; external v0.7 roadmap decision register updated in place.

No schema revision, DDL, migration, new table, derived flag or operational-data
change. Pre-existing `config.php`, `config.php.local`, `AGENTS.md`, `reports/local/`
and committed `main.css` are preserved. Disposable test/preview artifacts are
outside the worktree.

## Original Stage 4A verification — PHP 8.5.10

Used the existing `crm-stage1c85-php-1` container, an isolated code/config copy
`/tmp/crm-stage4a-code`, and the existing integration server's disposable
`cats_integrationtest` database/grant. The existing DatabaseTestCase creates and
drops that database per test. No First Base/application dataset was accessed or
modified. No PHP environment or database server was provisioned.

One initial selection (both new test classes) ran **6 tests**. Two passed; four
failed because the initial settings JSON exceeded the native value column and
Contact fixtures omitted a required department field. After scoped corrections,
only those four were rerun: eligibility/settings and Task matching passed; two
exposed the existing unsupported Company Activity-add path above. Replacing that
fixture setup with explicit stored-source rows allowed the remaining two tests
to pass: **2 tests, 11 assertions, exit 0**. All six selected tests have now passed;
there was no additional full rerun at that checkpoint. Initial/correction assertion counts include
partial tests and are not presented as unique successful assertions.

Selected cases:

- `testEligibilitySettingsValidationAndImmediateRecalculation`
- `testTaskCoverageBoundariesContactsAndExcludedAccounts`
- `testInteractionEvidenceAttributionAndStaleness`
- `testReasonTierDateAndStableIDOrderingWithoutDuplicates`
- `testOwnershipPermissionsAndSettingsSecurity`
- `testAttentionSettingsRouteRejectsNonAdminAndInvalidCsrf`

Command: `php vendor/bin/phpunit --display-warnings --display-deprecations`
with the two named files; subsequent `--filter` selections contained failed cases
only. Ownership/security and route/CSRF passes are carried forward unchanged;
eligibility/settings and Task-coverage passes are carried forward from the first
correction. No earlier-stage test results are claimed as a new Stage 4A execution.

All nine changed/new PHP and PHP-template files passed PHP 8.5.10 syntax checks.
`git diff --check` passed. Logs remain at `/tmp/crm-stage4a-logs`.
Three duplicate constants in the disposable test configuration and 21 legacy
cast deprecations (22 when Settings loads CareerPortal) were reported. No test
suppression or unrelated compatibility cleanup was added.

At the original Stage 4A checkpoint, no full PHPUnit, integration, Behat, browser
or security suite and no PHP 8.4 run had been performed. The later combined
verification passed all 15 focused tests (132 assertions) on both PHP 8.4.1 and
PHP 8.5.10; see the Stage 4A-1 report below.
A disposable static render of the real Settings template was visually checked at
1280px and 390px using existing Bootstrap/application CSS. Fields align and stack;
labels are escaped. This is not a live Settings save or full application browser
acceptance check. Existing Add/Edit/Show/list/ExtraFields pages were not changed.

## Query costs and remaining verification

For a nonempty owner scope: six collection queries (policy, labels, owned
Companies, current Contacts, filtered open relationship Tasks, grouped Company/current-Contact
Activities), plus the existing Task backend's parent/pipeline authorization
queries. No per-Company Activity lookup, repeated full Task collection, or duplicate
Company result. Task parent checks can still repeat for a shared parent; this is
an existing backend cost, not a persistent authorization cache. Policy writes use
one transaction with 12 delete/insert pairs; reads use the native settings table.

Current-owner Company rows and relevant Contacts/Tasks are materialised in memory;
IN lists grow with that scope. No pagination, cache or speculative index was added.
The existing Activity parent/type indexes and Task parent/status indexes apply.
Representative EXPLAIN/timing and large-owner memory measurements remain required
before Stage 5; no production-scale benchmark is claimed.

Maintainer checks: live Settings navigation, valid save/validation recovery and
expired-session behaviour; real ACL categories; configured timezone boundaries;
large-owner performance; and the accepted current-employer attribution limitation above.
Full application/CI/runtime gates remain separate. Do not start Stage 4B or Stage 5
as part of this change.

## Stage 4A-1 checkpoint — superseded recency exclusion

The maintainer superseded the proposed historical-attribution design with simple
Contact transfer logging. The unfinished attribution columns/migration, employer
periods, Company-history filters, movement links, backdated-attribution logic and
related UI/backup changes were removed before delivery. No such migration was
applied to an application or test database.

`Contacts::update()` now adds one ordinary Contact Activity of type **Other** when
the Company actually changes. Its plain-text description snapshots the old/new
Company names; assignment/removal have distinct wording. Normal Contact Activity
history and the existing current-Company Activity query remain unchanged.

Transfer logging **does not resolve historical Company Activity attribution**.
At the Stage 4A-1 checkpoint all Contact evidence was excluded; Stage 4A-2
supersedes that exclusion with the accepted current-employer rule above.
Transfer notes themselves still do not count. Configuration, Task matching and
reason ordering remain unchanged. No Task, notification, schema change or
special timeline is introduced. Future historical attribution work requires
separate scope approval.

See `docs/crm/stage-4a-1-contact-transfers.md` for the simplified implementation,
dual-runtime focused verification, exact removals and remaining limitations.

See `docs/crm/stage-4a-2-current-company-recency.md` for the revised query,
combined verification and synthetic SQL performance evidence.
