# 15 — Functional Specification

This document describes **what OpenCATS does as a product**, derived strictly from the
feature set evidenced in the codebase: the per-module controllers (`modules/*/`),
their dispatched actions, the access-level (ACL) guards on those actions, and the
`lib/` classes they call. Every feature here is traced to the module/action/lib that
implements it, with a real file+line cite.

The detailed per-user/per-object access-control matrix lives in **doc 14**; this doc
cites only the *minimum* access level that each feature's guard enforces and defers the
full matrix there. ACL constant values (from `constants.php:74-82`):
`ACCESS_LEVEL_READ=100`, `ACCESS_LEVEL_EDIT=200`, `ACCESS_LEVEL_DELETE=300`,
`ACCESS_LEVEL_DEMO=350`, `ACCESS_LEVEL_SA=400`, `ACCESS_LEVEL_MULTI_SA=450`,
`ACCESS_LEVEL_ROOT=500`. `getUserAccessLevel($key)` delegates to
`$_SESSION['CATS']->getAccessLevel($key)` (`lib/UserInterface.php:429-432`).

> Scope note on guard keys: several actions are dispatched under a *different* ACL key
> than their host module (e.g. candidate/joborder pipeline actions guard `pipelines.*`).
> These mismatches are called out where they occur and are catalogued in
> `docs/_evidence/acl-summary.md`.

---

## Scope — what OpenCATS does

OpenCATS is a single-tenant-per-site applicant tracking system (ATS) for recruiting
agencies. The evidenced product surface is:

- **Candidate management** — `modules/candidates/CandidatesUI.php` (controller class at
  `:53`): people records, resume/profile-image attachments, tagging, duplicate
  detection/merge, mass e-mail, questionnaire results, and pipeline membership.
- **Job order management** — `modules/joborders/JobOrdersUI.php` (`:52`): the open
  reqs/positions, their status, openings, attachments, and candidate pipeline.
- **Pipelines & activities** — the `candidate_joborder` pipeline, status changes, and
  activity logging, shared across candidates/joborders/contacts via `lib/Pipelines.php`
  and `lib/ActivityEntries.php` (guard key family `pipelines.*`).
- **Companies & contacts** — `modules/companies/CompaniesUI.php` (`:44`, internally
  "clients") and `modules/contacts/ContactsUI.php` (`:43`).
- **Calendar & reminders** — `modules/calendar/CalendarUI.php` (`:35`) plus the
  `Reminders` queue task (`modules/calendar/tasks/Reminders.php`).
- **Saved lists / hotlists** — `modules/lists/ListsUI.php` (`:44`), backed by
  `saved_list` / `saved_list_entry`.
- **Search** — per-module search actions plus the global Quick Search in
  `modules/home/HomeUI.php` (`quickSearch`, `:205`); optional Sphinx full-text resume
  search (see Search section).
- **Reporting & graphs** — `modules/reports/ReportsUI.php` (`:35`) for statistics /
  submission / placement / job-order / EEO reports, with chart images served by
  `modules/graphs/GraphsUI.php` (`:38`).
- **Import & export** — `modules/import/ImportUI.php` (`:48`) for CSV/delimited and
  bulk-resume import; `modules/export/ExportUI.php` (`:41`) for CSV export.
- **Career portal** — `modules/careers/CareersUI.php` (`:47`), the public job board /
  application form (disabled by default).
- **Administration** — `modules/settings/SettingsUI.php` (`:55`): users, sites,
  e-mail/calendar/EEO settings, extra fields, career-portal config, questionnaires,
  e-mail templates, backups, system info.
- **Authentication** — `modules/login/LoginUI.php` (`:37`), with optional LDAP.
- **Public feeds** — `modules/rss/RssUI.php` (`:43`) and `modules/xml/XmlUI.php` (`:48`)
  emit shared job orders as RSS / XML.

Read-only / serving modules without a tab: attachment file serving
(`modules/attachments/AttachmentsUI.php`, `:33`) and the activity log
(`modules/activity/ActivityUI.php`, `:36`).

---

## Feature areas

### 1. Candidate management — `modules/candidates/`

| Feature | What it does | Inputs | Rules / notes | Min level | Cite |
|---|---|---|---|---|---|
| Add candidate | Creates a candidate; optional resume upload+parse; logs an "Added a new candidate" activity (type 400) | name, contact, key skills, source, extra fields, optional resume file | Duplicate check via `Candidates::checkDuplicity` before `Candidates::add`; resume parse round-trips re-render the form | EDIT (`candidates.add`) | `CandidatesUI.php:96-103`, handler `add()`/`onAdd()` `:871/:1153`; activity `:1168-1174` |
| Edit candidate | Updates record, sources, extra fields; optional owner-change e-mail | candidateID, owner, fields | Phone normalized via `StringUtility::extractPhoneNumber`; admin-hidden records blocked below SA | EDIT (`candidates.edit`) | `:112-113`, `onEdit()` `:1283`; hidden check `:1203-1207` |
| Delete candidate | POST-only delete + MRU removal | candidateID (POST) | GET → `COMMONERROR_BADFIELDS` | DELETE (`candidates.delete`) | `:128-129`, `onDelete()` `:1522` |
| Search candidates | By full name / key skills / résumé full-text / city / phone; saves the search | mode, wildCardString | Résumé mode uses `SearchByResumePager`; persists via `SavedSearches::add` | READ (`candidates.search`) | `:143-144`, `onSearch()` `:2123` (mode switch `:2190-2382`) |
| View résumé | Renders extracted résumé text in a popup with keyword highlighting | attachmentID | `SearchUtility::makePreview` highlight | READ (`candidates.viewResume`) | `:161-162`, `viewResume()` `:2424` |
| Add/replace résumé or attachment | Uploads a file via `AttachmentCreator::createFromUpload`; flags résumé vs generic | candidateID, file, isResume | de-dup via `duplicatesOccurred()` | EDIT (`candidates.createAttachment`) | `:287-288`, `onCreateAttachment()` `:2541` |
| Add/replace profile image | Uploads a profile image attachment | candidateID, image file | modal | EDIT (`candidates.addEditImage`) | `:270-271`, `onAddEditImage()` `:2482` |
| Delete attachment | POST-only attachment delete | attachmentID (POST) | GET → BADFIELDS | DELETE (`candidates.deleteAttachment`) | `:323-324`, `onDeleteAttachment()` `:2605` |
| Assign tags | Assigns tags to a candidate | candidateID, tag IDs/titles | modal; `Tags::AddTagsToCandidate` | EDIT (`candidates.addCandidateTags`) | `:205-206`, `onAddCandidateTags()` `:1969` |
| Consider for job order | Searches job orders to add a candidate to | candidateID, search | modal | EDIT (guard key `candidates.search`) | `:175-176`, `considerForJobSearch()` `:1550` |
| Add to pipeline | Adds one/many candidates to a job-order pipeline; logs activity (type 400) | jobOrderID, candidateID(s) | skips already-piped candidates; `Pipelines::add` | EDIT (guard key `pipelines.addToPipeline`) | `:190-191`, `onAddToPipeline()` `:1676` |
| Log activity / schedule event | Logs an activity entry and/or creates a calendar event | activity type, note, event fields | `ActivityEntries::add` + `Calendar::addEvent` | EDIT (guard key `pipelines.addActivity`) | `:221-222`, `_addActivity()` `:3112` |
| Change pipeline status | Changes candidate↔joborder status, adjusts openings, optional status-change e-mail | statusID, regardingID, message | Blocks `PIPELINE_STATUS_PLACED` if no openings (`JobOrders::checkOpenings`); logs `ACTIVITY_STATUS_CHANGE` | EDIT (guard key `pipelines.changeStatus`) | `:238-239`, `_changeStatus()` `:3420` (openings `:3468-3478`) |
| Remove from pipeline | POST-only removal | candidateID, jobOrderID (POST) | GET → BADFIELDS modal | DELETE (guard key `pipelines.removeFromPipeline`) | `:255-256`, `onRemoveFromPipeline()` `:2066` |
| Mass e-mail candidates | Sends e-mail to selected candidates | candidate IDs, subject, body | Two-stage guard, effective **SA**; also gated on `MAIL_MAILER != 0` in the datagrid action area | SA (`candidates.emailCandidates`) | `:349-354`, `onEmailCandidates()` `:3610`; datagrid gate `dataGrids.php:63` |
| View questionnaire results | Shows a candidate's questionnaire answers | candidateID | — | READ (`candidates.show_questionnaire`) | `:361-362`, `onShowQuestionnaire()` `:3724` |
| Administrative hide/show | Hides a candidate from non-SA users | candidateID, state (POST) | SA-only; hidden records divert non-SA `show()` to the list | SA (guard key `candidates.hidden`) | `:307-308`, `administrativeHideShow()` `:2636` |
| Duplicate link / merge | Find-duplicate search, link, merge field values, remove duplicity | candidateID, duplicateCandidateID | All five sub-actions SA | SA (guard key `candidates.duplicates`) | `:369-424` (`linkDuplicate`/`merge`/`mergeInfo`/`removeDuplicity`/`addDuplicates`) |
| Candidate list (home) | Paginated datagrid with "My/Hot" filters and Add-to-List/Export action area | grid params | default action | READ (guard key `candidates.list`) | `:433-435`, `listByView()` `:439` |

The candidate Show page also surfaces hotlist membership
(`Candidates::getListsForCandidate`, `lib/Candidates.php:1900`) and upcoming calendar
events (`Candidates::getUpcomingEvents`, `:984`). Note: `HotList.tpl` and `Duplicates.tpl`
exist in the module dir but are **not** referenced by any `display()` in the controller
(`docs/modules/candidates.md:140-141`).

### 2. Job order management — `modules/joborders/`

| Feature | What it does | Min level | Cite |
|---|---|---|---|
| Add job order | Creates a req (company, contact, recruiter, owner, openings, type, hot/public, questionnaire) | EDIT (`joborders.add`) | `JobOrdersUI.php:117`, `onAdd()` `:735` → `JobOrders::add` (`lib/JobOrders.php:94`) |
| Add via popup | "Add Job Order" sub-tab popup (`addJobOrderPopup`), lists existing orders to copy from | EDIT (guard key `joborders.add`) | `:109`, `addJobOrderPopup()` `:586` |
| Edit job order | Updates fields, status, openings-available; optional owner-change e-mail | EDIT (`joborders.edit`) | `:133`, `onEdit()` `:995` → `JobOrders::update` (`lib/JobOrders.php:163`) |
| Delete job order | POST-only; nulls `calendar_event.joborder_id`, deletes extra fields | DELETE (`joborders.delete`) | `:149`, `onDelete()` `:1194` |
| Show job order | Detail page: type, hot/public styling, attachments, career-portal flags, mini pipeline graph, questionnaire | READ (`joborders.show`) | `:101`, `show()` `:403`; mini pipeline `Graphs::miniJobOrderPipeline` `:529` |
| Search job orders | By job title / company name; saves the search; export form | READ (`joborders.search`) | `:164`, `onSearch()` `:1732` |
| Pipeline ops (consider/add/remove/activity/status) | Same pipeline operations as candidates, from the job-order side; `changeStatus`/`addActivity` **delegate** to `CandidatesUI::publicChangeStatus`/`publicAddActivity` | EDIT/DELETE (guard keys `pipelines.*`, `candidates.add` for quick-add) | `:183-277`; delegation `:1654-1670`, `:1635` |
| Attachments | Create (EDIT) / delete (DELETE) job-order attachments | EDIT / DELETE (`joborders.createAttachment` / `.deleteAttachment`) | `:293`, `:313` |
| Administrative hide/show | SA-only POST hide/show | SA (`joborders.administrativeHideShow`) | `:338`, `administrativeHideShow()` `:1968` |
| Job-order list | Datagrid with status filter, "Only My"/"Only Hot" toggles, Add-to-List/Export | READ (guard key `joborders.list`) | `:355`, `listByView()` `:368` |

Job-order types are `C/C2H/FL/H` by default (`lib/JobOrderTypes.php:15-22`); statuses come
from `lib/JobOrderStatuses.php`.

### 3. Pipeline & activities (cross-area)

The pipeline is the `candidate_joborder` association joining a candidate to a job order
with a status. It is operated from both the candidate and job-order modules (above) and
implemented by:

- **`lib/Pipelines.php`** — `add()` (`:61`), `remove()` (`:140`), `setStatus()` (`:295`),
  `getStatusesForPicking()` (`:405`), `getCandidatePipeline()` (`:470`),
  `getJobOrderPipeline()` (`:609`).
- **`lib/ActivityEntries.php`** — `add()` (`:88`) writes an `activity` row and stamps the
  parent's (and any job order's) `date_modified`; activities are **only ever created as a
  side effect** of other actions, never directly by the activity module
  (`docs/modules/activity.md:35`).

Status changes can fire per-status e-mail triggers (overlaid from
`MailerSettings`' serialized `candidateJoborderStatusSendsMessage`, `CandidatesUI.php:1906-1912`)
and enforce openings on `PIPELINE_STATUS_PLACED` (`CandidatesUI.php:3468-3478`). All
pipeline write actions require **EDIT** (or **DELETE** for removal) under the
`pipelines.*` guard family.

The **activity log** (`modules/activity/`) is a read-only/reporting view: a date-range
search and a one-month datagrid of candidate+contact activities; it has **no ACL guards**
(auth only) (`ActivityUI.php` — verified no `getUserAccessLevel`). Activity edit/delete
are AJAX endpoints (`ajax/editActivity.php`, `ajax/deleteActivity.php`) guarded by
`contacts.editActivity` / `contacts.deleteActivity` @ EDIT (`docs/modules/activity.md:131-134`).

### 4. Companies & contacts — `modules/companies/`, `modules/contacts/`

Companies (internally "clients", hooks prefixed `CLIENTS_*`):

| Feature | Min level | Cite |
|---|---|---|
| Show / list company (with job orders, contacts, activity, departments, map link) | READ (`companies.show` / `companies.list`) | `CompaniesUI.php:77`, `:195` |
| "Go To My Company" (internal postings → default company) | READ (`companies.internalPostings`) | `:85`, `internalPostings()` `:513` |
| Add / edit company (name required; owner-change e-mail; department list-editor; address-sync to contacts) | EDIT (`companies.add` / `.edit`) | `:93`, `:109` |
| Delete company (cascade: contacts, job orders, attachments, list entries, extra fields; **refuses the default company**) | DELETE (`companies.delete`) | `:125`, `onDelete()` `:926` (default-company guard `:946-950`) |
| Search company (by name / key technologies) | READ (`companies.search`) | `:140`, `onSearch()` `:990` |
| Attachments (create/delete) | EDIT / DELETE | `:159`, `:178` |
| View history link | gated by `companies.show >= DEMO` (`privledgedUser`) | `:482-489` |
| Owner-change e-mail | gated by `companies.email == DEMO` (`canEmail`) | `:703-710` |

Contacts:

| Feature | Min level | Cite |
|---|---|---|
| Show / list contact | READ (`contacts.show` / `contacts.list`) | `ContactsUI.php:86`, `:195` |
| Add / edit contact (first/last/title required; reports-to; owner-change e-mail) | EDIT (`contacts.add` / `.edit`) | `:94`, `:110` |
| Delete contact (POST-only) | DELETE (`contacts.delete`) | `:126`, `onDelete()` `:874` |
| Search contact (full name / company / title) | READ (`contacts.search`) | `:141`, `onSearch()` `:925` |
| Log activity / schedule event | EDIT (`contacts.addActivityScheduleEvent`) | `:159`, `_addActivityScheduleEvent()` `:1325` |
| Cold call list | READ (`contacts.showColdCallList`) | `:175`, `showColdCallList()` `:1082` |
| Download vCard | READ (`contacts.downloadVCard`) | `:183`, `downloadVCard()` `:1159` |

HR-mode users are diverted from the company list to internal postings
(`CompaniesUI.php:213-217`).

### 5. Calendar & reminders — `modules/calendar/`

The calendar tab requires `ACCESS_LEVEL_READ@calendar` (`CalendarUI.php:44`). Month/week/day
views are assembled by `showCalendar()` (`:100`) with an AJAX month feed via `dynamicData()`
(`:312`, no per-action guard beyond the tab).

| Feature | What it does | Min level | Cite |
|---|---|---|---|
| Add event | Creates a calendar event (all-day or timed, optional reminder/public) | EDIT (`calendar.addEvent`) | `:352`, `onAddEvent()` `:350` → `Calendar::addEvent` |
| Edit event | Updates an event | EDIT own; **SA** to edit others' (`calendar.show >= SA`) | `:514`, `:593-594`, `onEditEvent()` `:512` |
| Delete event | POST-only delete | DELETE own; **SA** to delete others' | `:714`, `:734-735`, `onDeleteEvent()` `:712` |
| Show others' events | "Show entries from other users" toggle | SA (`calendar.show`) | `:180`, super-user flag |

Reminders are sent by a recurring queue task: `Reminders` runs every minute
(`getSchedule()` = `'* * * * *'`) and e-mails due events, then disables their reminder flag
(`modules/calendar/tasks/Reminders.php:47-102`). Reminder UI is only offered when
`SystemUtility::isSchedulerEnabled() && !isDemo()` (`CalendarUI.php:263-270`).

### 6. Saved lists / hotlists — `modules/lists/`

A saved list is a named, site-scoped collection of one data-item type
(candidate/company/contact/joborder), backed by `saved_list` + `saved_list_entry`
(`db/cats_schema.sql:903-937`). Only **static** lists are ever created — the schema's
`is_dynamic`/dynamic-list path is dormant (every write path hardcodes `is_dynamic = 0`,
`lib/SavedLists.php:212`; `docs/modules/lists.md:46`).

| Feature | What it does | Min level | Cite |
|---|---|---|---|
| List of lists / show a list | Datagrids over `saved_list` and per-type member grids | **(none — login only)** | `ListsUI.php:124`, `:155` |
| Add to list (single / from datagrid) | Add-to-list modal | **(none — login only)** for the modal; EDIT for the AJAX commit | `:228`, `:266`; AJAX `lists:addToLists` @ EDIT (`ajax/addToLists.php:71`) |
| Remove from list / delete list | Bulk remove / delete | **(none — login only)**, POST-only via `isPostBack()` | `:309`, `:371` |
| New / rename / delete (AJAX) | `lists:newList` / `editListName` / `deleteList` | **EDIT** (`lists`) | `ajax/newList.php:44`, `editListName.php:44`, `deleteList.php:44` |

> Access-control gap (verbatim from evidence): the **web controller actions** in `ListsUI`
> call no `getUserAccessLevel()` — only the four `ajax/*.php` endpoints enforce `lists` @ EDIT
> (`docs/modules/lists.md:52,193`). So any logged-in user can view/remove/delete lists via the
> web actions; only the AJAX add/new/rename/delete paths require EDIT. Flagged for doc 14.

### 7. Search

| Surface | What it does | Min level | Cite |
|---|---|---|---|
| Quick Search ("Search Everything") | Cross-entity search of candidates/companies/contacts/job orders | **(none — login only)** | `HomeUI.php:205` (`quickSearch()`), `QuickSearch` in `lib/Search.php:1373` |
| Per-module search | Candidate (5 modes incl. résumé full-text), job order, company, contact | READ on the module's `*.search` key | candidates `:143`; joborders `:164`; companies `:140`; contacts `:141` |
| Saved searches | Persist a search (`SavedSearches::add`); promote to custom (`addSavedSearch`), delete (`deleteSavedSearch`) | **(none — login only)** for add/delete; user+site scoped in SQL | `HomeUI.php:180`, `:155`; `lib/Search.php:1726`, `:1702` |

Résumé full-text search uses `SearchByResumePager` (`CandidatesUI.php:2253-2270`).
Sphinx full-text search is an *optional* backend referenced in the broader architecture
(see doc 13/03); it is configuration-driven and not a distinct module action — **unverified
in this pass** (see footer).

### 8. Reporting & graphs — `modules/reports/`, `modules/graphs/`

Every reports action requires **READ** (`reports.show`); EEO actions additionally require
`canSeeEEOInfo()` (`lib/Session.php:416-419`).

| Report | What it does | Min level | Cite |
|---|---|---|---|
| Statistics dashboard | Counts of companies/candidates/submissions/placements/contacts/job orders across 9 time periods | READ | `ReportsUI.php:118`, `reports()` `:127` |
| Submission report | Submissions grouped by job order for a period | READ | `:75`, `showSubmissionReport()` `:222` |
| Placement report | Placements grouped by job order for a period | READ | `:83`, `showPlacementReport()` `:302` |
| Job-order report (form) | Editable form prefilled from pipeline stats | READ | `:91`, `customizeJobOrderReport()` `:382` |
| Job-order report (PDF) | FPDF "Recruiting Summary Report" with an embedded pie graph | READ | `:67`, `generateJobOrderReportPDF()` `:443` |
| EEO report (form + preview) | EEO criteria form and statistics with ethnic/veteran/gender/disability graphs | READ **and** `canSeeEEOInfo()` | `:99-100`, `:108-109`; preview `:597` |
| Graph view (kiosk) | Full-screen auto-refreshing single image page | READ | `:59`, `graphView()` `:205` |

Charts are PNG/JPEG images served by `modules/graphs/`. Public (no-auth) graph actions:
`testGraph`, `wordVerify` (CAPTCHA), `jobOrderReportGraph`, `generic`, `genericPie`
(`GraphsUI.php:78-99`). Logged-in-only graph actions (no ACL beyond `isLoggedIn()`):
`activity`, `newCandidates`, `newJobOrders`, `newSubmissions`, `miniPlacementStatistics`,
`miniJobOrderPipeline` (`:101-132`). The graphs module has **no** `getUserAccessLevel`
call at all (`docs/modules/graphs.md:43`).

### 9. Import & export — `modules/import/`, `modules/export/`

Import supports two flows:

| Feature | What it does | Min level | Cite |
|---|---|---|---|
| Delimited (CSV/tab) import | Column-mapped, row-by-row INSERT of Candidates/Job Orders/Companies/Contacts; revertable for 7 days | EDIT (`import.import`); foreign/extra-field branch requires **SA** | `ImportUI.php:437`, `onImportFieldsDelimited()` `:757` (SA branch `:920`) |
| Revert an import | Deletes rows tagged with that `import_id` | EDIT (`import.import`) | `:157`, `revert()` `:155` |
| View pending / view errors | Lists recent imports; shows per-row errors | (none — auth only) | `viewPending()` `:230`, `viewErrors()` `:196` |
| Mass-resume wizard | Scans the per-site `massimport` dir, converts to text, optional parse, edit, import as candidates or bulk-resume attachments | EDIT (`import.massImport`) | `:1524`, `massImport()` `:1513` |
| Bulk-resume import / delete | Re-index / delete bulk-resume attachments | **SA** (`import.bulkResumes`) | `:2119`, `:2086` |

Résumé-to-text conversion shells out to external binaries (antiword/pdftotext/html2text,
plus internal RTF/ODT/DOCX) via `lib/DocumentToText.php:72`. Résumé **parsing** (field
extraction) is gated by `LicenseUtility::isParsingEnabled()` and is effectively off —
see Non-features.

Export (CSV):

| Feature | What it does | Min level | Cite |
|---|---|---|---|
| Export (`export`) | CSV download by `dataItemType` + selection mode | (none — auth only) | `ExportUI.php:77`; **only `DATA_ITEM_CANDIDATE` is implemented** (`lib/Export.php:132-141`) |
| Export by datagrid | Streams a CSV from a reconstructed grid's exportable columns | (none — auth only) | `ExportUI.php:135`, `DataGrid::drawCSV()` |

The export module has no ACL guard and no nav tab; it is invoked from datagrid action areas
(`ExportUtility::getForm`). Non-candidate exports via the `export` action return false →
empty CSV (`docs/modules/export.md:76`).

### 10. Career portal — `modules/careers/` (disabled by default)

The career portal is the **only unauthenticated, externally facing** application surface
(`_authenticationRequired = false`, `CareersUI.php:53`). It is reached via the `careers/`
root shim or `?showCareerPortal=1` and is **hard-disabled by default**: when the `enabled`
setting is `'0'` (its default, `lib/CareerPortal.php:77`) the controller dies with a "Job
Board Disabled" comment (`CareersUI.php:101-105`).

Routing is by `p`/`pa` parameters, not `a=` actions; there are **no** per-branch ACL guards.

| Public feature | What it does | Cite |
|---|---|---|
| Job list | Lists publicly shared job orders (`JOBORDERS_STATUS_SHARE`) when `allowBrowse == 1` | `p=showAll` `CareersUI.php:157`; `getResultsTable()` `:1419` |
| Job detail | Single job page; non-public jobs redirect to the list | `p=showJob` `:876` |
| Apply | Application form with resume upload + optional parse; sub-actions login/preview/parse | `p=applyToJob` `:454` |
| Submit application | Creates/updates candidate, attaches resume, adds to pipeline, logs activity, e-mails applicant + owner | `p=onApplyToJobOrder` `:784` / `onApplyToJobOrder()` `:1494` |
| Questionnaire step | Renders the configured questionnaire and applies answers via `Questionnaire::doActions` | `:797-874`, `:1652` |
| Candidate registration / profile | Returning-candidate login + profile edit (when `candidateRegistration` enabled) | `p=candidateRegistration` `:400`, `p=registeredCandidateProfile` `:190` |

The global CSRF check is intentionally skipped for the portal
(`index.php:145-150`); public POSTs are guarded only by `REQUEST_METHOD === 'POST'`.
Upload filenames are whitelisted by `FileUtility::makeSafeFilename` (`lib/FileUtility.php:192-196`).

### 11. Administration — `modules/settings/` (SA-gated)

Settings spans **My Profile** (self-service), **Administration / system**, and **User
management**. Reads are generally **DEMO (350)**; almost all POST writes require **SA (400)**
(`docs/modules/settings.md` ACL-SUMMARY).

| Feature | Min level | Cite |
|---|---|---|
| My Profile / change password | READ; change-password blocked only for DEMO accounts | `SettingsUI.php:937`, `:250` |
| Manage users (list) | DEMO | `:336`, `manageUsers()` `:340` |
| Show user (own or others') | DEMO; self-view bypasses | `:368-369` |
| Add / edit user | GET DEMO, **POST SA**; cannot change own access level on edit | add `:379/:387`, edit `:400/:408` (self-guard `:1419-1422`) |
| Delete user | **SA** + `iAmTheAutomatedTester` flag (exists "only for automated testing") | `:628`, `onDeleteUser()` `:1495` |
| E-mail settings (mailer + status-change triggers) | GET DEMO, **POST SA** | `:499/:507` |
| E-mail templates (list/edit/add/delete) | GET DEMO, **POST SA**; add/delete enforce SA inside the handler via `_realAccessLevel` | `:646/:654`; `:948`, `:968` |
| Extra fields editor | GET DEMO, **POST SA** | `:444/:452`, `onCustomizeExtraFields()` `:448` |
| Calendar settings | GET DEMO, **POST SA** | `:464/:472` |
| EEO/EOC settings | GET DEMO, **POST SA** | `:594/:602` |
| Career-portal settings / board templates / tweaks | GET DEMO, **POST SA** (career-portal-category users bypass SA) | `:568-618` |
| Questionnaire builder | DEMO | `:516-544` |
| Backups (create/delete; ZIP generator) | **SA**; AJAX backup independently re-checks `getAccessLevel(ACL::SECOBJ_ROOT) < SA` | `:418/:426`; `ajax/backup.php:41` |
| Site name / localization / version check / system info | mostly **SA**; version-check change requires **ROOT (500)** | `:291`, `:663`; `:2614` (changeVersionName ROOT) |
| Login activity log | DEMO | `:674`, `loginActivity()` `:681` |
| Item history | DEMO | `:685`, `viewItemHistory()` `:689` |
| Tags add/del/upd (AJAX, in-controller) | (none — session/POST only) | `:692-732` |
| Install/first-run wizard endpoints (`ajax_wizard*`) | mostly **SA** (`ajax_wizardEmail` READ) | `:740-890` |

In career-portal mode the settings module locks down the whole app: it hides non-settings
tabs, redirects Home/My-Profile, and `fatal()`s every other module's handle-request hook
(`defineHooks()`, `SettingsUI.php:87-128`).

### 12. Authentication — `modules/login/`

| Feature | What it does | Cite |
|---|---|---|
| Login | `attemptLogin` delegates to `CATSSession::processLogin` → `Users::isCorrectLogin`; site-scoped username; password via `password_verify` with lazy MD5→bcrypt migration | `LoginUI.php:179`, `:247`; `lib/Users.php:796`, `:1296-1310` |
| Post-login routing | Routes to home, or to forced setup pages (site-name upgrade, e-mail-disabled, force-email) | `LoginUI.php:288-435` |
| Logout | POST-only, in the front controller (`m=logout`); unsets session, redirects to login | `index.php:210-257` |
| No-cookies modal | Cookie-disabled warning | `LoginUI.php:169` |
| LDAP login (optional) | `AUTH_MODE` = `ldap`/`sql+ldap`; first-time LDAP users auto-created **disabled** (pending approval) | `lib/Users.php:838-867`; `lib/LDAP.php` |

All login actions are public (`_authenticationRequired = false`, `LoginUI.php:43`) — no ACL
guards by design.

> **Forgot-password appears broken (per `docs/modules/login.md`).** `onForgotPassword()`
> calls `Users::getPassword()` (`LoginUI.php:460`) — a method that **does not exist** in
> `lib/Users.php` — and uses `PASSWORD_RESET_SUBJECT` / `PASSWORD_RESET_BODY` constants that
> are **not defined anywhere** in the repo. Passwords are stored as `password_hash()` output,
> so no plaintext could be recovered even if it ran. The flow is treated as **dead/broken**
> (`docs/modules/login.md:209-211`).

### 13. Public feeds — `modules/rss/`, `modules/xml/`

Both are public (`_authenticationRequired = false`) and emit publicly shared job orders:
`rss` outputs RSS 2.0 (`RssUI.php:43`); `xml` outputs template-driven XML for job boards,
gated by the portal `allowBrowse` setting (`XmlUI.php:48`; `docs/_evidence/acl-summary.md:120-124`).

---

## Feature → permission summary table

Compact; one row per representative feature. Defer to **doc 14** for the full per-object
matrix. "Min level" is the level the action's guard compares against (or the effective
result for two-stage/owner guards). "(none)" = login/auth only.

| Feature | Module | Min access level |
|---|---|---|
| View candidate / list | candidates | READ |
| Search candidate / view résumé | candidates | READ |
| Add / edit candidate, tags, attachments, profile image | candidates | EDIT |
| Delete candidate / attachment | candidates | DELETE |
| Mass-email candidates | candidates | **SA** |
| Hide/show candidate; duplicate link/merge | candidates | **SA** |
| Add to pipeline / log activity / change status | candidates·joborders (`pipelines.*`) | EDIT |
| Remove from pipeline | candidates·joborders (`pipelines.*`) | DELETE |
| View / search / list job order | joborders | READ |
| Add / edit job order + attachments | joborders | EDIT |
| Delete job order / attachment | joborders | DELETE |
| Hide/show job order | joborders | **SA** |
| View / search / list company·contact | companies·contacts | READ |
| Add / edit company·contact + attachments / activity | companies·contacts | EDIT |
| Delete company·contact | companies·contacts | DELETE |
| Cold call list / vCard | contacts | READ |
| View calendar | calendar | READ (tab) |
| Add / edit / delete own event | calendar | EDIT / EDIT / DELETE |
| Edit/delete others' events; show others' | calendar | **SA** |
| View / show / add-to / remove-from / delete list (web) | lists | (none — login only) |
| New / rename / delete / add-to list (AJAX) | lists | EDIT |
| Quick search; saved-search add/delete | home | (none — login only) |
| All reports | reports | READ |
| EEO report form/preview | reports | READ + `canSeeEEOInfo()` |
| Public graphs (test/captcha/generic/pie/jobOrderReport) | graphs | (none — public) |
| Dashboard graphs | graphs | (none — `isLoggedIn()`) |
| CSV export / export-by-datagrid | export | (none — auth only) |
| Delimited import / revert | import | EDIT |
| Foreign-field import; bulk-resume import/delete | import | **SA** |
| Mass-resume wizard | import | EDIT |
| Career portal (all branches) | careers | (none — public; portal `enabled` setting) |
| My profile / change password | settings | READ (not DEMO for password) |
| Read admin/settings pages | settings | DEMO |
| Write most settings; add/edit user (POST); backups | settings | **SA** |
| Change version-check pref | settings | **ROOT** |
| Login / forgot-password / logout | login | (none — public) |
| RSS / XML job feeds | rss · xml | (none — public) |
| Serve attachment file | attachments | (none — auth only + dir-hash) |
| Activity log views | activity | (none — auth only) |

---

## Non-features / vestigial

Things present in the tree that are **dead, dormant, or non-functional** as shipped:

- **Async queue UI** — `modules/queue/QueueUI.php` is a no-op: its `handleRequest()` switch
  has only `default: break;`, renders nothing, has no template/JS, and never calls
  `QueueProcessor` (`docs/modules/queue.md:48,54`). The header comment claiming an "XML
  job-posting export" is stale/misleading (`:50,167`). The real async engine
  (`lib/QueueProcessor.php` + `QueueCLI.php`) runs out-of-band via cron.
- **Only two live queue tasks** — `Reminders` (calendar e-mail reminders) and
  `CleanExceptions` are the only registered recurring tasks
  (`modules/calendar/tasks/tasks.php:39`, `modules/queue/tasks.php:41`); `SampleTask.php` /
  `SampleRecurring.php` are templates only (`docs/modules/queue.md:144`).
- **Wizard module** — `modules/wizard/WizardUI.php` is a generic page-renderer whose only
  page producer (in `LoginUI.php`) is **commented out**, so the session it needs is
  normally empty and `show()` redirects to home (`docs/modules/wizard.md:66-67,256-260`).
  Its constructor's wizard pages are an inert commented-out block, and `ajax_getPage()`
  `eval()`s session-stored PHP that is never populated in this tree.
  The first-login wizard block in `LoginUI.php` is likewise commented out / `false &&`-gated
  (`docs/modules/login.md:100,214`).
- **SOAP résumé parsing** — `lib/ParseUtility.php` is a SOAP client
  (`new SoapClient('wsdl/parse.wsdl')`, `:60`) gated by `LicenseUtility::isParsingEnabled()`,
  which short-circuits because `PARSING_ENABLED` is hard-set to `false`
  (`config.php:51`; gate `lib/License.php:687-713`). The candidate/careers/import "parse
  resume" paths therefore never call the remote service. The parser targeted a defunct
  CATS hosted endpoint.
- **`reports/ajax/getReportHTML.php`** — the doc records this as a 0-byte empty file with no
  callers (`docs/modules/reports.md:331-333`); verified there is **no** `modules/reports/ajax/`
  directory in the current tree, so the stub is gone/orphaned.
- **Dynamic saved lists** — schema + `SavedLists::getAll()` support `is_dynamic`, but every
  write path creates static lists only; dynamic lists are dormant
  (`docs/modules/lists.md:46`).
- **Orphan templates** — e.g. candidates `HotList.tpl`/`Duplicates.tpl`
  (`docs/modules/candidates.md:140-141`), reports `NewDataItems.tpl`
  (`docs/modules/reports.md:207-210`), import `ImportCommits.tpl` (references a non-existent
  `a=commit`, `docs/modules/import.md:53`), careers `Openings.tpl`/`SearchOpenings.tpl`
  (0-byte, `docs/modules/careers.md:319-321`) — present but not dispatched.
- **Forgot-password** — broken as described in §12 (undefined method + undefined constants).
- **`Export` for non-candidates** — `Export::getFormattedOutput()` only handles
  `DATA_ITEM_CANDIDATE`; all other types return false (`lib/Export.php:132-141`).
- **`graphs` helper/controller naming mismatch** — `Graphs::miniPipeline()` emits
  `a=miniPipeline`, but the controller has no such case (it has `miniPlacementStatistics`);
  a request for `a=miniPipeline` would error (`docs/modules/graphs.md:161`).

---

## Source evidence

Primary inputs (all read for this doc):

- Per-module design docs: `docs/modules/{candidates,joborders,companies,contacts,calendar,lists,home,activity,reports,graphs,import,export,careers,settings,login,queue,wizard,attachments,rss,xml}.md`.
- ACL catalogue: `docs/_evidence/acl-summary.md`.
- Spot-verified against source:
  - `config.php:51` — `define('PARSING_ENABLED', false);`
  - `lib/License.php:687-713` — `isParsingEnabled()` gated on `isSOAPEnabled()` + `PARSING_ENABLED`.
  - `lib/ParseUtility.php:4,41,53,60,134-135` — SOAP `SoapClient`/WSDL résumé parser.
  - Absence of `modules/reports/ajax/` (no `getReportHTML.php`) — confirmed via `find`/`ls`.
- ACL constants and `getUserAccessLevel` delegation: `constants.php:74-82`,
  `lib/UserInterface.php:429-432`.

---

## Unverified / open questions

- **Sphinx full-text search** is referenced as an optional backend in the architecture
  docs but was not exercised here; whether/how it changes the candidate résumé-search
  action at runtime (vs. the SQL `SearchByResumePager`) is **unverified** in this pass.
- **Numeric ACL resolution per object key** — how dotted keys like `pipelines.addActivity`,
  `companies.list`, `companies.email`, `settings.addUser.POST` resolve to a per-user numeric
  level (and the default for unknown keys) lives in `lib/Session.php`/`lib/ACL.php`, which was
  not traced here; the *guards* are cited verbatim, the *resolution* is in doc 14.
- **Lists access-control gap** (web actions unguarded vs. AJAX EDIT) is reported from the
  module doc as a finding to confirm against intended design (`docs/modules/lists.md:193`).
- **Attachment serving** uses `Attachments::get($id, false)` (site scoping disabled),
  authorized only by `md5(directory_name)` matching `directoryNameHash`
  (`docs/modules/attachments.md:185`) — cross-site reachability is flagged, not asserted.
- **`canSeeEEOInfo()` semantics** were cited (`lib/Session.php:416-419`) but the full rule for
  when a user gains EEO visibility (SA forced-on at login per `docs/modules/login.md:91`) was
  not re-derived here.
- Whether any installed plugin/hook re-enables the wizard, queue UI, or SOAP parsing at
  runtime (via `$_SESSION['hooks']`) was not enumerated; in the shipped tree all are inert.
