# Stage 4A-1 revision — simple Contact transfer logging

The 10 October 2026 revised brief supersedes the earlier historical-attribution
specification. Branch: `feature/crm-desk-foundation`; implementation baseline:
`ad7fd613c07db89e8e6c76c4003b05874815079f`. The maintainer authorised a combined
local Stage 4A/4A-1 commit on 10 October 2026. No push, PR, merge or rebase.
Existing Stages 1–3 and the Stage 4A implementation are preserved.

## Removed unfinished work

The earlier attempt had introduced these uncommitted changes:

- Nullable `activity.company_id` and `movement_history_id`, their indexes and
  migration 400, with fresh-install schema additions.
- Activity attribution snapshots, backdated-date resolution against History,
  mutation guards for movement markers and Company-period selection.
- Changes to full Contact and Company Activity reads, movement descriptions,
  previous-employer links and period-filter parameters on Company Show.
- A shared movement template and extra Company/Contact timeline markup.
- Stage 4A inclusion of attributed Contact evidence and altered evidence metadata.
- Contact movement events linked to structured History rows; extra validation
  of the Contact Activity form's insertion result.
- A backup filter retaining Company-change History for the proposed links.

All of that superseded functionality was removed. `ActivityEntries.php` and
`Companies.php` were restored byte-for-byte from the saved pre-Stage-4A-1 snapshot,
which already contained Stage 4A. Schema, installer, backup, Company/Contact
controllers and Show templates again match committed HEAD. The new movement
partial was deleted. No abandoned-attribution tests had yet been added. No
migration was applied to a database; no database rollback/backfill was needed.
No unrelated bug fix from the abandoned implementation was retained.

## Minimal implementation and reuse

Only `lib/Contacts.php` changes production behaviour for this revision.
`Contacts::update()` retains its existing callers, positional signature, Contact
permissions in `ContactsUI`, department handling, change History and ordinary
Activity rendering. It reuses:

- `Contacts::get()` for the previous/new Company names;
- `History::storeHistoryChanges()` for the existing structured audit trail;
- `ActivityEntries::add()` with `DATA_ITEM_CONTACT` and `ACTIVITY_OTHER`;
- `DatabaseConnection` quoting, row locking and transaction methods;
- existing `TemplateUtility::highlightStatusChangeActivityNote()` / Template
  escaping in the standard Contact and Company Activity displays.

After a successful Contact update, a changed Company creates exactly one visible
Activity. The normal History audit may also record the field change and Activity
creation; these do not become duplicate entries in the Activity list.

Examples:

- A → B: `Contact transferred from "Company A" to "Company B".`
- Unassigned → B: `Contact assigned to "Company B".`
- A → Unassigned: `Contact removed from "Company A" (now Unassigned).`

Names are stored as plain text in the Activity note. Later Company renames do not
rewrite the event. A missing old Company uses its numeric reference as a fallback.
Existing output escaping handles quotes, ampersands and HTML-like Company names.
Unassigned sentinels NULL/empty/0/-1 are normalised to the existing -1 convention;
no visible transfer is generated between equivalent unassigned values.

Contact creation, unchanged/repeated saves and unrelated edits generate no transfer
Activity. Positive destination IDs must identify an existing Company; malformed,
array-shaped or nonexistent identifiers are rejected before updating the Contact.
The existing Contact edit permission remains the only initiating action path.
Automatic informational logging does not require a separate manual Activity action.

The Contact row is locked before comparing old/new Companies. Contact update,
structured History and the transfer Activity share the existing transaction;
failed Contact or Activity writes roll back the Contact and audit changes.
The native transaction helper does not nest: update returns false if a caller
already owns a transaction, without committing/rolling back the caller's work.
A repository-wide caller audit found one production invocation:
`ContactsUI::onEdit()` at `modules/contacts/ContactsUI.php:846`. Its request
entry/dispatch path and registered repository hooks do not open an outer
transaction. Other Contact usages call distinct methods such as `add()`,
`updateByCompany()` and `updateModified()`; none delegates to `update()`.
The only additional direct caller is the new integration-test helper, including
its deliberate outer-transaction rejection case. No existing production caller
requires transaction nesting. Deployment-specific hooks or external consumers
outside this checkout cannot be certified by this audit. Existing department
resolution still occurs before the transaction guard, as in the established
update flow; rejection of an outer transaction is not a blanket guarantee of
zero department-resolution side effects.

No Task or notification is created. Existing independently requested ownership
email behaviour is unchanged; transfer logging introduces no mail call.
Transfer entries are ordinary informational Activities, not special immutable
history objects, reminders or outstanding actions. Existing Activity editing and
deleting permissions continue to apply.

## Stage 4A remains conservative

No Activity table columns, new tables, migration, attribution backfill, employer
periods, filtered history UI, previous-employer links or new JavaScript remain.
The current schema revision remains 399. Existing Contact history is available
exactly as before; `getAllByCompany()` retains its current-employer roll-up.

Stage 4A still excludes Contact Activities because historical Company attribution
is not reliable. Transfers use Other, never Call (Talked) or Meeting. Their
creation does not refresh Company contact recency. Configuration, thresholds,
Task matching, reason sorting and `contactEvidenceScope` are unchanged.
The known unsupported direct Company-parent Activity-add path is not changed.
Future Company-attribution work remains a separate decision; this revision does
not remove that limitation or justify expanding into Stage 4B/5.

## Files changed by the simplified revision

- `lib/Contacts.php`
- `src/OpenCATS/Tests/IntegrationTests/ContactTransferTest.php`
- `src/OpenCATS/Tests/UnitTests/ContactTransferAuthorizationTest.php`
- `docs/crm/stage-4a-relationship-attention.md` (revision/limitation note)
- `docs/crm/stage-4a-1-contact-transfers.md` (this report)

The earlier Stage 4A files are included in the combined delivery. Checksums confirm
`config.php`, `config.php.local`, `AGENTS.md` and `main.css` are unchanged from
entry to Stage 4A-1. Existing `reports/local/` is untouched. No local configuration,
production data or disposable test artifacts are included in the implementation.

## Verification

Used the existing `crm-stage1c-php-1` (PHP **8.4.1**) and
`crm-stage1c85-php-1` (PHP **8.5.10**) containers with a disposable code/config
copy and their separate existing integration DB servers/grants. Only
`cats_integrationtest` was created/dropped by the established DatabaseTestCase.
No live/First Base dataset or application configuration was accessed or changed;
no infrastructure was provisioned.

One focused selection of **15 tests per runtime** comprised the four new transfer
integration tests, one existing-dispatch permission test, all six Stage 4A tests,
and four existing Contact Activity edit/delete permission regressions.

At the maintainer's request, the complete corrected selection was rerun together
in one PHPUnit invocation per runtime on 10 October 2026:

| Runtime | Combined result | Exit status | Diagnostics |
| --- | --- | --- | --- |
| PHP 8.4.1 | 15 tests passed, 132 assertions | 0 | None |
| PHP 8.5.10 | 15 tests passed, 132 assertions | 0 | 22 existing legacy deprecations |

These complete-run results supersede the earlier fourteen passes plus isolated
fixture-correction rerun. The earlier missing Template include was fixed in the
test fixture; no production correction was required. PHP 8.5 diagnostics concern
legacy non-canonical casts and a switch-case semicolon, not transfer logging.
No suppression was introduced. The disposable config uses guarded constants to
avoid duplicate test-bootstrap definitions.

The selected files were:

```
src/OpenCATS/Tests/IntegrationTests/ContactTransferTest.php
src/OpenCATS/Tests/IntegrationTests/RelationshipAttentionTest.php
src/OpenCATS/Tests/UnitTests/ContactTransferAuthorizationTest.php
src/OpenCATS/Tests/UnitTests/RelationshipAttentionAuthorizationTest.php
src/OpenCATS/Tests/UnitTests/ActivityPipelineAuthorizationTest.php
```

Command: `php vendor/bin/phpunit --display-warnings --display-deprecations`
with those files and filter
`ContactTransfer|RelationshipAttention|testActivityUsesActualParentAclAndAllowsColleagues.*"contact`.

Coverage includes creation without transfer; A-to-B exactly once with stored
names; name preservation after Company rename; repeated/unrelated saves;
assignment/removal and returning to an employer; preserved old/new Contact
Activities and unchanged Company roll-up; invalid references; forced database
failures during Contact update and Activity insert with rollback of Contact and
History; existing Contact edit permissions; escaping; no Tasks; and unchanged
Stage 4A evidence/scope. Existing Activity edit/delete ACL and all Stage 4A
eligibility/settings/coverage/order/security tests were exercised.

Previously completed syntax checks for all **12 changed/new PHP or PHP-template
files** on both runtimes are carried forward unchanged; this verification pass
changed documentation only. A fresh `git diff --check` passed.
Complete-run logs: `/tmp/crm-stage4a1-logs/final-combined-php84.log` and
`/tmp/crm-stage4a1-logs/final-combined-php85.log`. Earlier syntax logs remain in
`simple-syntax-php84.log` and `simple-syntax-php85.log` in that directory.
Disposable code copies were removed; the normal integration harness removed its
test databases.

The final audit reconstructed every tracked pre-Stage-4A-1 file using the saved
starting diff and verified identical current bytes, including all six tracked
Stage 4A implementation files and the preserved local config. Schema, migration,
backup, Company/Contact controllers and Show templates match HEAD. Inspection
of the remaining production diff and untracked implementation/tests found no
abandoned historical-attribution functionality. Searches for movement-history,
backdate resolver, employer-period/navigation parameters and activity Company
columns found none in source, schema or tests. The movement partial is absent.

No complete PHPUnit, integration, Behat, browser or security suite was run. No
new rendering/UI mechanism remains, so no browser suite or new visual acceptance
claim was necessary. Live recruiter Edit/save/history confirmation remains a
maintainer check, including any deployment-specific hooks/customisations.

## Handoff and limitations

The simplified change is ready for maintainer review, with the focused gates
passing on both supported runtimes. No outstanding business decision is required
for transfer logging. Comprehensive CI/manual release checks remain separate.

Transfer notes are deliberately informational snapshots. The existing Company
Activity roll-up can still associate old Contact interactions with the Contact's
current employer; this accepted limitation is unchanged. Stage 4A therefore
retains its conservative evidence exclusion. This revision does not resolve that
limitation for Stage 5, and does not begin Stage 4B or Stage 5.

The combined commit includes the original Stage 4A files and this revision's five
files listed above (14 files in total). Local `config.php`, `config.php.local`,
`AGENTS.md` and `reports/local/` remain outside the commit, preserved unchanged.
The branch remains `feature/crm-desk-foundation`. No schema, controller, template,
backup or JavaScript diff remains from the abandoned Stage 4A-1 design.

Commit message: `feat(crm): add relationship attention rules and contact transfer logging`.
The commit preparation changed documentation only; the completed combined tests
and syntax checks above remain applicable without rerunning them.
