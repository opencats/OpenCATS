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

**Normal Contact Activity logging cannot safely refresh Company recency with
existing stored attribution.** The implementation excludes that unverified
history. It can derive recency from existing explicitly Company-linked Activity
records only. This is a material source limitation, not evidence that accounts
have never been contacted. Do not present this slice as complete operational
Company-contact capture or enable a dashboard without addressing/disclosing it.
The exact unsupported path and safe boundary are recorded below.

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

## Actual Activity evidence and attribution blocker

The source stores type 500 **Call (Talked)**, 300 **Meeting**, 200 **Email**,
100 **Not reached**, 600 **Call (LVM)**, 700 **Call (Missed)**, 400 **Other** and
800 **Status Change**. Only 500 and 300 qualify. Meeting means a recorded meeting,
not independently verified attendance. Email has no structured direct/bulk,
reply or genuine-engagement outcome, so it is excluded. Notes are never parsed
for invented evidence, and Task state/history never counts as contact.

`activity` has a typed parent and optional Job Order but no historical Company
snapshot. `ActivityEntries::getAllByCompany()` joins `contact.company_id` at read
time, so moving an employer rewrites the apparent historical roll-up. It remains
unchanged for existing callers but is unsuitable for verified recency.

`Contacts::update()` can log Company changes through generic History, but Contact
creation does not capture an initial Company snapshot, and imports/direct updates
and retained history cannot guarantee a complete employment timeline. Absence
of a recorded move does not prove continuous association. Job Order's current
Company is also not a substitute for historical relationship attribution.

`ActivityEntries::getRelationshipContactDates()` therefore only uses explicit
`DATA_ITEM_COMPANY` parent records of qualifying types, joined to existing
Companies. It ignores sentinel dates, future timestamps and evidence before
Company creation. Normal UI supports Candidate/Contact Activities; moreover,
`ActivityEntries::add()` with a Company reaches `_updateDataItemModified()` and
calls nonexistent `Companies::updateModified()` **after the insert**. That existing
unsupported write path was discovered by focused tests, not changed or endorsed.
Tests seed explicit legacy/source Activity fixtures directly; they do not claim
that native Company Activity creation works.

A separately reviewed factual-attribution change is needed for useful ongoing
Company recency from normal Contact logging (including moved/backdated contacts).
No schema snapshot, history reconstruction, backfill or new logging workflow was
invented here. With ordinary Contact-only source data, `lastContact` stays NULL;
results explicitly include `contactEvidenceScope` explaining the exclusion.
Stage 5 consumers must retain that limitation beside No recorded contact.

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
- `lib/ActivityEntries.php`: conservative explicit Company contact evidence query.
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
Companies, current Contacts, filtered open relationship Tasks, grouped explicit
Company Activities), plus the existing Task backend's parent/pipeline authorization
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
large-owner performance; and the complete source-attribution limitation above.
Full application/CI/runtime gates remain separate. Do not start Stage 4B or Stage 5
as part of this change.

## Stage 4A-1 revised scope — 10 October 2026

The maintainer superseded the proposed historical-attribution design with simple
Contact transfer logging. The unfinished attribution columns/migration, employer
periods, Company-history filters, movement links, backdated-attribution logic and
related UI/backup changes were removed before delivery. No such migration was
applied to an application or test database.

`Contacts::update()` now adds one ordinary Contact Activity of type **Other** when
the Company actually changes. Its plain-text description snapshots the old/new
Company names; assignment/removal have distinct wording. Normal Contact Activity
history and the existing current-Company Activity query remain unchanged.

This improves recruiter visibility only. It **does not resolve historical Company
Activity attribution**: transfers, and all other Contact Activities, remain
excluded by Stage 4A's conservative Company-recency query. Its evidence-scope
metadata, configuration, Task matching and reason ordering are unchanged. No Task,
notification, schema change, special timeline or new Activity retrieval path is
introduced. Future attribution work requires separate scope approval.

See `docs/crm/stage-4a-1-contact-transfers.md` for the simplified implementation,
dual-runtime focused verification, exact removals and remaining limitations.
