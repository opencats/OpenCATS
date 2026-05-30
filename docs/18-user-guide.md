# 18 — User Guide

This is a task-oriented guide for recruiters using OpenCATS. Every instruction
below describes only screens, fields, buttons, and actions that actually exist in
this repository's templates (`modules/<module>/*.tpl`) and controller actions
(`modules/<module>/*UI.php`). Each task cites the real action and screen behind
it. Where a feature is gated by an access level, the requirement is noted.

## A note on access levels

OpenCATS gates actions by a numeric per-user access level
(`constants.php:74-82`):

| Level | Constant | Value |
|------:|----------|------:|
| Read-only | `ACCESS_LEVEL_READ` | 100 |
| Edit | `ACCESS_LEVEL_EDIT` | 200 |
| Delete | `ACCESS_LEVEL_DELETE` | 300 |
| Demo | `ACCESS_LEVEL_DEMO` | 350 |
| Site Admin (SA) | `ACCESS_LEVEL_SA` | 400 |

Throughout this guide, "requires Edit" means your account's level for that
secured object must be ≥ `ACCESS_LEVEL_EDIT` (200), and so on. When you lack the
level for an action, OpenCATS calls `CommonErrors::fatal(COMMONERROR_PERMISSION, …)`
and the tab/button is usually hidden (the `*al=…@securedObject` tab convention,
`lib/TemplateUtility.php:656-678, 730-752`).

---

## Getting started

### Logging in

Open OpenCATS in a browser. If you are not logged in, the front controller routes
you to the login module (`index.php:259-269`), which renders the login form
(`modules/login/Login.tpl`).

1. Enter your **username** and **password**. If your installation hosts multiple
   sites, a **site name** field may be present; you can also log in by typing your
   full e-mail address as the username (in which case site name is ignored)
   (`modules/login/LoginUI.php:228-244`).
2. Submit. The form posts to `?m=login&a=attemptLogin`
   (`modules/login/Login.tpl:45`), handled by `attemptLogin()`
   (`modules/login/LoginUI.php:179`).
3. On success you are redirected to the Dashboard (`m=home`)
   (`modules/login/LoginUI.php:431-435`). On failure the login form re-renders
   with an "Invalid username or password." message
   (`modules/login/LoginUI.php:250-283`).

There is a **Forgot Password** link that posts to `?m=login&a=forgotPassword`
(`modules/login/ForgotPassword.tpl:34-35`). *Note:* the forgot-password handler
references functions/constants that do not exist in this tree and appears
non-functional — see Unverified.

To **log out**, use the logout control in the top-right header block
(`lib/TemplateUtility.php:95-211`); it posts to `m=logout`, which clears your
session and returns you to the login screen (`index.php:210-257`).

### The Home dashboard

After login you land on the **Dashboard** tab (`HomeUI`, tab text "Dashboard",
`modules/home/HomeUI.php:43`), rendered by `Home.tpl`. The dashboard shows
(`modules/home/HomeUI.php:105-152`, `modules/home/Home.tpl`):

- **My Recent Calls** — a compact grid of your recent call/contact activity, last
  month only (`Home.tpl:14`; `CallsDataGrid`, `modules/home/dataGrids.php:201`).
- **My Upcoming Calls** and **My Upcoming Events** — the next 7 days of calendar
  items (`Home.tpl:18,22`; `Calendar::getUpcomingEventsHTML(7, …)`,
  `modules/home/HomeUI.php:114-118`).
- **Recent Hires** — recent placements (`Home.tpl:39-47`;
  `Dashboard::getPlacements()`).
- **Hiring Overview** graph with Weekly/Monthly/Yearly toggles
  (`Home.tpl:58-66`; `swapHomeGraph()` in `js/home.js:29`).
- **Important Candidates** — the pipeline grid of candidates who are Submitted,
  Interviewing, or Offered in active job orders (`Home.tpl:75`;
  `ImportantPipelineDashboard`, `modules/home/dataGrids.php:40`).

The Dashboard requires only a logged-in session; it has no per-action access-level
checks (`modules/home/HomeUI.php:40`).

### Top navigation tabs and Quick Search

Every authenticated page renders the same chrome (`lib/TemplateUtility.php`): a
**top tab bar** built from the registered modules (Dashboard, Candidates, Job
Orders, Companies, Contacts, Activities, Calendar, Lists, Reports — visibility
depends on your access level), a logged-in user / logout header block, and a
**Quick Search** box with a "Recent:" most-recently-used list
(`printQuickSearch()`, `lib/TemplateUtility.php:265-311`).

The Quick Search box ("Search Everything") posts to `?m=home&a=quickSearch`
(`modules/home/HomeUI.php:205`) and returns four result tables — Job Orders,
Candidates, Companies, Contacts — rendered by `SearchEverything.tpl`. Click any
result to open that record's detail page.

---

## Managing candidates

The **Candidates** tab (`CandidatesUI`, `modules/candidates/CandidatesUI.php:73`)
opens the candidate list (`Candidates.tpl`, action `listByView`). Its two
sub-tabs are **Add Candidate** and **Search Candidates**
(`modules/candidates/CandidatesUI.php:74-77`). The list view itself requires Read
(`candidates.list`, guard at `CandidatesUI.php:435`).

### Add a candidate

1. Click the **Add Candidate** sub-tab (visible only with Edit on
   `candidates.add`, `CandidatesUI.php:75`). This opens the add form `Add.tpl`
   (`CandidatesUI.php:871`).
2. Fill in the fields. The form's required fields are **First Name** and **Last
   Name** (marked with `*`); other fields include E-Mail, 2nd E-Mail, Web Site,
   Home/Cell/Work Phone, Address, City, State, Postal Code, Best Time to Call,
   Current Employer, Current Pay, Desired Pay, Source, Key Skills, Misc. Notes,
   Can Relocate, and Date Available (`modules/candidates/Add.tpl:76-504`).
   EEO selects (Gender, Ethnic Background, Veteran Status, Disability Status)
   appear when EEO tracking is enabled (`Add.tpl:340-390`).
3. Submit. The form posts back to `a=add`; `onAdd()` checks for duplicates and
   inserts the candidate, logs an "Added a new candidate." activity, and
   redirects to the candidate's detail page
   (`CandidatesUI.php:1153, 1160, 1168-1178`).

**Requires:** Edit on `candidates.add` (`CandidatesUI.php:97`).

### Attach / parse a resume

You can attach a resume while adding a candidate (the **Resume** upload field on
`Add.tpl:291`), or afterward from the candidate's detail page.

- On the detail page, the **Add Attachment** control opens the upload modal
  `CreateAttachmentModal.tpl` (`a=createAttachment`, `CandidatesUI.php:2519`),
  which posts the file to `onCreateAttachment()`. The uploaded file is run through
  `AttachmentCreator::createFromUpload(...)`, which extracts text for full-text
  search; check the "resume" flag to mark it as the candidate's resume
  (`CandidatesUI.php:2541-2597`).
- To view extracted resume text, use the resume preview link, which opens
  `ResumeView.tpl` (`a=viewResume`, `CandidatesUI.php:2424`).

Resume **parsing** (auto-filling fields from a resume) depends on a parsing
license being enabled (`LicenseUtility`, referenced in `add()`/`onAdd`); when not
licensed, you simply attach the file without auto-parse.

**Requires:** Edit on `candidates.createAttachment` (`CandidatesUI.php:288`).

### Edit a candidate

1. On a candidate's detail page (`Show.tpl`), click the **Edit** link
   (`?m=candidates&a=edit&candidateID=…`, `Show.tpl:449`).
2. Update fields in `Edit.tpl` (`CandidatesUI.php:1184`). You can reassign the
   **Owner**; if you check the ownership-change option, an assignment e-mail can
   be sent (built from the `EMAIL_TEMPLATE_OWNERSHIPASSIGNCANDIDATE` template,
   `CandidatesUI.php:1365-1418`).
3. Submit. `onEdit()` normalizes phone numbers, updates the record, saves extra
   fields and possible sources, and redirects to the detail page
   (`CandidatesUI.php:1283, 1461-1516`).

**Requires:** Edit on `candidates.edit` (`CandidatesUI.php:113`).

### Search candidates

1. Click the **Search Candidates** sub-tab → `Search.tpl` (`a=search`,
   `CandidatesUI.php:2098`).
2. Pick a search **mode** from the dropdown: **Candidate Name**, **Resume
   Keywords** (default), **Key Skills**, **City**, or **Phone Number**
   (`modules/candidates/Search.tpl:31-36`).
3. Enter your query and submit. Results return in a DataGrid; the search is also
   saved to your saved-searches list (`onSearch()`, `CandidatesUI.php:2123-2418`).

**Requires:** Read on `candidates.search` (`CandidatesUI.php:144`).

### View a candidate

From the list or a search result, click the candidate's name to open `Show.tpl`
(`a=show`, `CandidatesUI.php:576`). The detail page shows the profile fields, the
candidate's pipelines (the job orders they are in), logged activities, upcoming
calendar events, attachments, EEO data, tags, and questionnaire results
(`CandidatesUI.php:669-790`).

**Requires:** Read on `candidates.show` (`CandidatesUI.php:89`).

### Add a candidate to a job order pipeline

Two paths:

- **From the candidate detail page:** use the "Consider for a job order" control,
  which opens `ConsiderSearchModal.tpl` (`a=considerForJobSearch`,
  `CandidatesUI.php:1550`; `Show.tpl:599-600`). Search for a job order, then add
  the candidate. Adding requires Edit on `candidates.search`
  (`CandidatesUI.php:176`).
- **From the candidate list Action menu:** select candidates with the row
  checkboxes, then choose **Add To Job Order** from the Action menu
  (`modules/candidates/dataGrids.php:60`). This opens the same consider/add modal.

The actual add is `onAddToPipeline()`, which adds each selected candidate to the
chosen job order's pipeline and logs an "Added candidate to job order." activity
(`CandidatesUI.php:1676-1758`). **Requires:** Edit on `pipelines.addToPipeline`
(`CandidatesUI.php:191`).

### Tag candidates

On the candidate detail page, open the tag modal `AssignCandidateTagModal.tpl`
(`a=addCandidateTags`, `CandidatesUI.php:1998`; `Show.tpl:433`), assign tags, and
save. **Requires:** Edit on `candidates.addCandidateTags` (`CandidatesUI.php:206`).

On the candidate **list**, you can also filter by tag using the "Filter by tag"
dropdown (`Candidates.tpl:42-83`).

### Handle duplicates / merge

Duplicate handling is **Site-Admin only** (Edit-level users do not see it).

- **Link a duplicate:** `a=linkDuplicate` runs a candidate search and shows
  `LinkDuplicity.tpl` so you can mark another candidate as a duplicate
  (`CandidatesUI.php:3761`).
- **Merge two candidates:** `a=merge` loads both records into `Merge.tpl`; you
  choose which field values to keep, and `a=mergeInfo` performs the merge and
  redirects to the surviving record (`CandidatesUI.php:3819, 3845`).

**Requires:** `ACCESS_LEVEL_SA` on `candidates.duplicates`
(`CandidatesUI.php:370, 379, 387`).

### Add a candidate to a hotlist (saved list)

From the candidate **list**, select rows and choose **Add To List** from the
Action menu (`modules/candidates/dataGrids.php:59`). This opens the lists module's
add-to-list modal. See **Lists & search** below for details.

**Requires:** Edit on `lists` (enforced by the lists AJAX endpoints, e.g.
`modules/lists/ajax/addToLists.php:71`).

### E-mail candidates

From the candidate **list**, select candidates and choose **Send E-Mail** from
the Action menu (shown only when mailing is configured and you have the level —
`modules/candidates/dataGrids.php:63`). The mass-mail form is `SendEmail.tpl`
(`a=emailCandidates`, `CandidatesUI.php:349`).

**Requires:** the controller guard ultimately requires `ACCESS_LEVEL_SA` on
`candidates.emailCandidates` (`CandidatesUI.php:350-358`), and `MAIL_MAILER` must
be configured.

---

## Managing job orders

The **Job Orders** tab (`JobOrdersUI`, tab text "Job Orders",
`modules/joborders/JobOrdersUI.php:83`) opens the job-order list `JobOrders.tpl`
(`listByView`, requires Read on `joborders.list`, `JobOrdersUI.php:355`). Its
sub-tabs are **Add Job Order** (a popup, gated by Edit on `joborders.add`) and
**Search Job Orders** (`JobOrdersUI.php:86-87`). The list has a status dropdown
and "Only My Job Orders" / "Only Hot Job Orders" filter checkboxes
(`JobOrders.tpl:31-52`).

### Create a job order

1. Click **Add Job Order**. A small popup (`AddModalPopup.tpl`,
   `a=addJobOrderPopup`, `JobOrdersUI.php:586`) lets you start a blank job order or
   copy from an existing one; the "Create Job Order" button opens the full add
   form (`AddModalPopup.tpl:35`).
2. The add form is `Add.tpl` (`a=add`, `JobOrdersUI.php:603`). Required fields are
   **Title**, **Company**, **Recruiter**, **City**, and **Openings**
   (client-side `checkAddForm`, `modules/joborders/validator.js:11-19`); other
   fields include Contact, Owner, Type, Status, Salary, Max Rate, Duration, Start
   Date, Department, Hot, and Public (for the career portal).
3. Submit. `onAdd()` validates and inserts the job order and redirects to its
   detail page (`JobOrdersUI.php:735, 836-853`).

**Requires:** Edit on `joborders.add` (`JobOrdersUI.php:117`).

### Edit a job order

1. On the job-order detail page (`Show.tpl`), click **Edit**
   (`m=joborders&a=edit`, `Show.tpl:45,335`) → `Edit.tpl` (`JobOrdersUI.php:861`).
2. Editable fields include **Title**, Start Date, Company, Duration, Maximum Rate,
   Department, Salary, Contact, Type, City, **Total Openings**, State, **Remaining
   Openings**, Recruiter, Company Job ID, Owner, Hot, and **Status**
   (`modules/joborders/Edit.tpl:30-253`). Remaining Openings cannot exceed Total
   Openings (`validator.js:222-252`).
3. Submit. `onEdit()` updates the record and redirects to the detail page
   (`JobOrdersUI.php:995, 1173-1186`).

**Requires:** Edit on `joborders.edit` (`JobOrdersUI.php:133`).

### View the pipeline and add candidates to it

The job-order detail page (`Show.tpl`, `a=show`, `JobOrdersUI.php:403`) displays
the job order's fields plus its **pipeline** of candidates and a mini pipeline
graph (`Graphs::miniJobOrderPipeline`, `JobOrdersUI.php:529-530`).

To add candidates, use the **"consider candidate" / Add to Pipeline** control,
which opens `ConsiderSearchModal.tpl` (`a=considerCandidateSearch`,
`JobOrdersUI.php:1223`; `Show.tpl:444`). Search by candidate name, then add the
selected candidates; the add posts to `a=addToPipeline` (`onAddToPipeline()`,
`JobOrdersUI.php:1320`). You can also quick-add a brand-new candidate from this
modal via `a=addCandidateModal` (`JobOrdersUI.php:1370`).

**Requires:** Edit on `joborders.considerCandidateSearch` and
`pipelines.addToPipeline` (`JobOrdersUI.php:220, 242`).

### Change a candidate's pipeline status

From either the candidate detail page or the job-order pipeline, open the change-
status modal `ChangeStatusModal.tpl`:

- Candidate side: `a=changeStatus` (`CandidatesUI.php:1841`; `Show.tpl:565-566`).
- Job-order side: `a=changeStatus` (`JobOrdersUI.php:1532`).

Pick the new status from the dropdown (the pickable statuses come from
`Pipelines::getStatusesForPicking()`); some status changes can trigger a
notification e-mail (built from `EMAIL_TEMPLATE_STATUSCHANGE`), and you can add a
custom message (`CandidatesUI.php:1876-1964`). Submitting runs
`Pipelines::setStatus(...)`, which records the change and an audit history row
(`CandidatesUI.php:3420, 3541-3543`). Moving a candidate to **Placed** requires an
open opening and decrements the available count
(`CandidatesUI.php:3468-3478, 3549`).

**Requires:** Edit on `pipelines.changeStatus`
(`CandidatesUI.php:239`, `JobOrdersUI.php:200`).

To **remove** a candidate from a pipeline, use the remove control on the pipeline
row; this posts to `a=removeFromPipeline` and requires Delete on
`pipelines.removeFromPipeline` (`CandidatesUI.php:256`, `JobOrdersUI.php:277`).

### Job-order attachments

On the detail page, **Add Attachment** opens `CreateAttachmentModal.tpl`
(`a=createAttachment`, `JobOrdersUI.php:1879`; `Show.tpl:280`), which uploads a
file via `AttachmentCreator::createFromUpload(...)`. Delete an attachment with the
inline delete form (`a=deleteAttachment`, `Show.tpl:263`).

**Requires:** Edit to add (`joborders.createAttachment`, `JobOrdersUI.php:293`);
Delete to remove (`joborders.deleteAttachment`, `JobOrdersUI.php:313`).

---

## Pipelines & activities

### Pipeline statuses

A candidate's progress through a job-order pipeline is a numeric status stored on
the `candidate_joborder` join row. The defined statuses are
(`constants.php:120-130`, seeded labels at `db/cats_schema.sql:269-279`):

| Value | Label |
|------:|-------|
| 0 | No Status |
| 100 | No Contact |
| 200 | Contacted |
| 250 | Candidate Responded |
| 300 | Qualifying |
| 400 | Submitted |
| 500 | Interviewing |
| 600 | Offered |
| 650 | Not in Consideration |
| 700 | Client Declined |
| 800 | Placed |

When you add a candidate to a pipeline, the practical initial status is **No
Contact** (100) (`lib/Pipelines.php:111`). Statuses Qualifying, Submitted,
Interviewing, Offered, and Placed are flagged to optionally send an e-mail on
change (`db/cats_schema.sql:269-279`). The engine allows moving to any enabled
status — there is no enforced forward-only path (see doc 09).

Move a candidate through these statuses using the **change-status** modal
described above.

### Logging an activity

From a candidate's detail page, use the **Log an Activity / Schedule Event**
control, which opens `AddActivityScheduleEventModal.tpl` (`a=addActivity`,
`CandidatesUI.php:1773`; `Show.tpl:345-346`). Check **Log Activity**, choose an
activity type (Call, Call - Talked, Left Message, Missed, E-Mail, Meeting, Other),
optionally tie it to one of the candidate's open pipelines, add a note, and save.
This calls `ActivityEntries::add(...)` (`CandidatesUI.php:3194-3202`).

Contacts have an equivalent **Log an Activity / Schedule Event** flow from the
contact detail page (`a=addActivityScheduleEvent`, `ContactsUI.php:1096`).

Note: activities are largely recorded automatically as side effects of actions
(adding a candidate, adding to a pipeline, changing status). The **Activities**
tab (`ActivityUI`, `modules/activity/ActivityUI.php:54`) is a read-only report of
recent activity on candidates and contacts; you can filter it by date
(`viewByDate`/`Search.tpl`, `ActivityUI.php:127`).

**Requires:** Edit on `pipelines.addActivity` (candidates,
`CandidatesUI.php:222`) / `contacts.addActivityScheduleEvent`
(`ContactsUI.php:159`).

### Scheduling an event

In the same Log Activity / Schedule Event modal, check **Schedule Event** to
create a calendar event instead of (or in addition to) an activity: set the date,
event type (the default is Interview), all-day or a time, and an optional reminder
(reminders appear only when the scheduler is enabled and you are not a demo user).
This calls `Calendar::addEvent(...)` (`CandidatesUI.php:3336-3341`).

---

## Companies & contacts

### Companies

The **Companies** tab (`CompaniesUI`, `modules/companies/CompaniesUI.php:60`)
opens the company list `Companies.tpl` (`listByView`, Read on `companies.list`,
`CompaniesUI.php:195`). Sub-tabs: **Add Company**, **Search Companies**, **Go To
My Company** (all suppressed in HR mode) (`CompaniesUI.php:61-63`).

**Add a company:** the **Add Company** sub-tab opens `Add.tpl` (`a=add`,
`CompaniesUI.php:526`). The only required field is **Name**
(`CompaniesUI.php:609-613`); other fields include Address (with a freeform
address-block parser and a Zip "Lookup"), phones, fax, URL, Key Technologies,
Departments (list editor), Hot, and Misc. Notes (`modules/companies/Add.tpl`).
Submit posts to `a=add`/`onAdd()` and redirects to the company page.
**Requires:** Edit on `companies.add` (`CompaniesUI.php:93`).

**Edit a company:** from `Show.tpl`, the **Edit** link opens `Edit.tpl` (`a=edit`,
`CompaniesUI.php:650`). You can change the owner (with an optional ownership-change
e-mail) and choose to propagate the company's address to all its contacts via the
"update contacts" option (`CompaniesUI.php:908-915`). **Requires:** Edit on
`companies.edit` (`CompaniesUI.php:109`).

**Search companies:** the **Search Companies** sub-tab opens `Search.tpl`
(`a=search`, `CompaniesUI.php:969`) with two modes — **searchByName** and
**searchByKeyTechnologies** (`modules/companies/Search.tpl:31-34`). **Requires:**
Read on `companies.search` (`CompaniesUI.php:140`).

**My Company / internal postings:** the **Go To My Company** sub-tab
(`a=internalPostings`, `CompaniesUI.php:513`) redirects to the site's default
("Internal Postings") company.

### Contacts

The **Contacts** tab (`ContactsUI`, `modules/contacts/ContactsUI.php:68`) opens
the contact list `Contacts.tpl` (`listByView`, Read on `contacts.list`,
`ContactsUI.php:195`). Sub-tabs: **Add Contact**, **Search Contacts**, **Cold Call
List** (`ContactsUI.php:69-73`).

**Add a contact:** the **Add Contact** sub-tab opens `Add.tpl` (`a=add`,
`ContactsUI.php:427`). Required fields are **First Name**, **Last Name**, and
**Title** (`ContactsUI.php:549-552`); you also pick the **Company** (with
autocomplete), department, reports-to, phones, e-mails, and address. **Requires:**
Edit on `contacts.add` (`ContactsUI.php:94`).

**Edit a contact:** from `Show.tpl`, the **Edit** link opens `Edit.tpl` (`a=edit`,
`ContactsUI.php:598`). The **Left Company** toggle marks a contact as no longer at
the company; owner reassignment can send an ownership-change e-mail. **Requires:**
Edit on `contacts.edit` (`ContactsUI.php:110`).

**Search contacts:** the **Search Contacts** sub-tab opens `Search.tpl`
(`a=search`, `ContactsUI.php:903`) with modes **searchByFullName**,
**searchByCompanyName**, **searchByTitle** (`modules/contacts/Search.tpl:32-34`).
**Requires:** Read on `contacts.search` (`ContactsUI.php:141`).

**Cold Call List:** the **Cold Call List** sub-tab (`a=showColdCallList`,
`ContactsUI.php:1082`) renders `ColdCallList.tpl`, a sortable table of
company / first name / last name / title / work phone. **Requires:** Read on
`contacts.showColdCallList` (`ContactsUI.php:175`).

**vCard download:** on a contact's detail page, the vCard link (`Show.tpl:41`)
hits `a=downloadVCard` (`ContactsUI.php:1159`), which streams a `.vcf` vCard with
the contact's name, phones, address, e-mail, title, and organization. **Requires:**
Read on `contacts.downloadVCard` (`ContactsUI.php:183`).

---

## Calendar

The **Calendar** tab (`CalendarUI`, `modules/calendar/CalendarUI.php:44`) requires
Read on `calendar`. Sub-tabs: **My Upcoming Events**, **Add Event**, **Goto
Today** (`CalendarUI.php:45-49`).

### View the calendar

The default action `showCalendar` renders `Calendar.tpl` (`CalendarUI.php:100`),
which supports **month**, **week**, and **day** views and a sidebar of upcoming
events. Site Admins can additionally toggle "Show Entries from Other Users"
(`Calendar.tpl:19-23`). The view is driven client-side by `Calendar.js` /
`CalendarUI.js`, which fetch month data via `?m=calendar&a=dynamicData`.

### Add an event

Click **Add Event** (or click an empty day/time cell). Fill the Add Event form in
the sidebar — title, type, date, all-day or time, public/private, optional
reminder — and submit; it posts to `?m=calendar&a=addEvent` (`Calendar.tpl:47-191`),
handled by `onAddEvent()` (`CalendarUI.php:350`). A **title** is required
(`CalendarUI.php:413-416`). **Requires:** Edit on `calendar.addEvent`
(`CalendarUI.php:352`).

### Edit / delete an event

Click an event to open the read-only View panel, then **Edit** (shown only with
Edit on `calendar.editEvent`, `Calendar.tpl:364-366`), which posts to
`a=editEvent` (`onEditEvent()`, `CalendarUI.php:512`). The Edit panel has a
**Delete** button (shown only with Delete on `calendar.deleteEvent`,
`Calendar.tpl:340-342`), which posts to `a=deleteEvent` (`onDeleteEvent()`,
`CalendarUI.php:712`). You may edit or delete **your own** events; editing or
deleting **another user's** event requires `ACCESS_LEVEL_SA` on `calendar.show`
(`CalendarUI.php:593-594, 734-735`).

### Reminders

When adding or editing an event, a reminder option is available **only** if the
background scheduler is enabled and you are not a demo user
(`allowEventReminders`, `CalendarUI.php:263-270`; UI hidden otherwise,
`Calendar.tpl:120,270`). Due reminders are e-mailed by a scheduled task that runs
every minute (`modules/calendar/tasks/Reminders.php`).

---

## Lists & search

### Saved lists (hotlists)

A **saved list** is a named collection of candidates, companies, contacts, or job
orders. The **Lists** tab (`ListsUI`, `modules/lists/ListsUI.php:54`) opens the
Lists home `Lists.tpl` (`listByView`), with a single sub-tab **Show Lists**
(`ListsUI.php:56`).

- **Create a list / add items:** from any candidate/company/contact/job-order
  **list** view, select rows and choose **Add To List** from the Action menu. This
  opens `QuickActionAddToListModal.tpl` (`a=addToListFromDatagridModal`,
  `ListsUI.php:266`), where you check existing lists or create a **New List**, then
  **Add**. The add is performed by the `lists:addToLists` AJAX endpoint.
- **View a list:** click a list on the Lists home → `List.tpl` (`a=showList`,
  `ListsUI.php:155`), showing the list's members in a datagrid.
- **Rename / delete a list:** inline rename and delete controls are on the
  add-to-list modal and the list view; a full **Delete List** link is on
  `List.tpl:18`. Removing selected members uses `a=removeFromListDatagrid`.

**Requires:** the list-modifying AJAX endpoints (add, new, rename, delete) all
require Edit on the `lists` secured object (e.g.
`modules/lists/ajax/addToLists.php:71`). *Note:* the lists web actions themselves
carry no per-action ACL beyond login — see Unverified.

### Quick search

Use the top-bar Quick Search box ("Search Everything") on any page; it posts to
`?m=home&a=quickSearch` and returns matching job orders, candidates, companies,
and contacts (`modules/home/HomeUI.php:205`). See **Getting started** above.

### Saved searches

Each module's search form (`Search.tpl` for candidates, job orders, companies,
contacts) records your query as a saved/recent search via `SavedSearches::add(...)`
on submit (e.g. `CandidatesUI.php:2396`). The search form lists your prior saved
searches so you can re-run them (`SavedSearches::get(...)`, e.g.
`CandidatesUI.php:2101`). You can promote a recent search to a permanent saved
search or delete one; these post to `?m=home&a=addSavedSearch` /
`?m=home&a=deleteSavedSearch` (`modules/home/HomeUI.php:180, 155`).

---

## Reports

The **Reports** tab (`ReportsUI`, `modules/reports/ReportsUI.php:44`) requires
Read on `reports.show` for every report (`ReportsUI.php:118` etc.). Its sub-tab is
**EEO Reports** (`ReportsUI.php:45-47`).

### Statistics dashboard and graphs

The default Reports page (`Reports.tpl`, `reports()`, `ReportsUI.php:127`) shows
counts of companies, candidates, submissions, placements, contacts, and job orders
across nine time periods (Today, Yesterday, This/Last Week, This/Last Month,
This/Last Year, To Date). The "New Submissions" and "New Placements" cells link to
the **submission report** (`a=showSubmissionReport`) and **placement report**
(`a=showPlacementReport`), which group records by job order for a chosen period
(`ReportsUI.php:222, 302`).

A per-job-order **Recruiting Summary Report** is available as a customizable form
(`a=customizeJobOrderReport`, `ReportsUI.php:382`) that generates a PDF
(`a=generateJobOrderReportPDF`, `ReportsUI.php:443`). Graph images on these reports
are produced by the graphs module (`?m=graphs&a=…`); the kiosk **Graph View**
(`a=graphView`) shows a single auto-refreshing graph (`ReportsUI.php:205`).

### EEO reports (permission-gated)

The **EEO Reports** sub-tab opens the EEO criteria form `EEOReport.tpl`
(`a=customizeEEOReport`, `ReportsUI.php:434`); choosing a period (all/month/week)
and status (all/placed/rejected) and submitting generates the EEO preview with
ethnic, veteran, gender, and disability graphs (`a=generateEEOReportPreview`,
`ReportsUI.php:597`).

**Requires:** Read on `reports.show` **and** your account must be allowed to see
EEO information (`$_SESSION['CATS']->canSeeEEOInfo()`)
(`ReportsUI.php:99-100, 108-109`).

---

## Import & export

The import module (`ImportUI`, `modules/import/ImportUI.php:48`) is reached via
`?m=import`. It supports CSV/tab-delimited imports and bulk-resume imports.

### Importing candidates / contacts / companies / job orders via CSV

1. Go to the import home (`Import1.tpl`, `import()`), pick the destination type,
   and continue to the upload screen `Import2.tpl` (`importSelectType()`,
   `ImportUI.php:383`).
2. Upload your CSV or tab-delimited file (`a=importUploadFile`).
3. On the **column-mapping** screen `Import.tpl` (`onImportDelimited()`,
   `ImportUI.php:608`), map each file column to a candidate/contact/company/job-
   order field; OpenCATS auto-matches columns by header name and shows sample
   rows. For Contacts you can opt to auto-generate companies.
4. Confirm to run the import. `onImportFieldsDelimited()` inserts the rows and
   records the batch so it can be **reverted within 7 days**
   (`ImportUI.php:757, 1042-1051`). Use **View Errors** / **Revert** on the recent-
   imports screen (`ImportRecent.tpl`, `viewPending()`).

**Requires:** Edit on `import.import` (`ImportUI.php:157, 437, 759`). Mapping a
column into an "extra field" additionally requires `ACCESS_LEVEL_SA`
(`ImportUI.php:920`).

### Bulk resume import

Choosing "resume" on the type screen starts the mass-import wizard `MassImport.tpl`
(`massImport()`, `ImportUI.php:1513`): place resume files in the site's
`massimport` upload directory, let OpenCATS convert each to text (and parse, if
licensed), review/edit, then import. Unique resumes are added as candidates with
the resume attached; the rest are stored as searchable bulk-resume attachments
(`getMassImportCandidates()`, `ImportUI.php:1673`).

**Requires:** Edit on `import.massImport` (`ImportUI.php:1524`); the bulk-resume
rescan/delete operations require `ACCESS_LEVEL_SA` on `import.bulkResumes`
(`ImportUI.php:2086, 2119`).

### Exporting a datagrid to CSV

Most list and search views include an **Export** option in the Action menu (and an
export form on search-result pages) offering "Export All Records", "Export Current
Page", and "Export Selected Records" (`ExportUtility::getForm`, `lib/Export.php:53`).

- The Action-menu **Export** runs the datagrid CSV path: `?m=export&a=exportByDataGrid`
  → `DataGrid::drawCSV()` streams `export.csv` from the grid's columns
  (`modules/export/ExportUI.php:135`; `lib/DataGrid.php:1444`).
- The search-result export form runs `?m=export&a=export` → `onExport()`, which
  produces `export.csv` (`modules/export/ExportUI.php:77`). *Note:* this
  `getFormattedOutput()` path only produces data for **candidates**; other types
  fall through to an empty file (`lib/Export.php:132-141`).

**Requires:** any authenticated user — the export module has no per-action access-
level guard (`modules/export/ExportUI.php:47`).

---

## Career portal (if enabled)

The careers module (`CareersUI`, `modules/careers/CareersUI.php:47`) is the only
public, unauthenticated surface. It is **disabled by default** and must be turned
on by a Site Admin via the Career Portal settings; when disabled the controller
hard-stops with a blank "Job Board Disabled" page
(`lib/CareerPortal.php:77`; `modules/careers/CareersUI.php:101-105`). Only job
orders marked **Public** appear (`JobOrders::getAll(JOBORDERS_STATUS_SHARE, …)`,
`CareersUI.php:119`).

A public applicant can:

1. **Browse openings** — the job list (`p=showAll`) shows publicly shared jobs
   when "allow browse" is on; each title links to the job detail page
   (`CareersUI.php:157, 1419-1491`).
2. **View a job** — `p=showJob` shows the job's title, description, location, etc.,
   with an **Apply** link (`CareersUI.php:876`).
3. **Apply** — `p=applyToJob` renders the application form (name, address, e-mail,
   phone, key skills, source, employer, EEO selects when enabled) and lets the
   applicant **upload a resume**; if parsing is licensed, the resume can pre-fill
   fields (`CareersUI.php:454-783`).
4. **Submit** — `p=onApplyToJobOrder` creates/updates the candidate, attaches the
   resume, adds the candidate to the job's pipeline, logs an activity, and e-mails
   the applicant and the job owner (`CareersUI.php:784, 1494, 1721-1902`).
5. **Answer a questionnaire** — if the job order has a questionnaire, it is shown
   before the "Thanks for your Submission" page (`CareersUI.php:797-874`).

Uploaded files pass through a filename whitelist (non-whitelisted extensions get
`.txt` appended so they cannot execute) (`lib/FileUtility.php:192-196`).

**Admin-enabled:** the portal must be enabled in settings and job orders must be
marked Public to appear here.

---

## Source evidence

- Access levels: `constants.php:74-82`. Pipeline statuses: `constants.php:120-130`,
  `db/cats_schema.sql:269-279`. Tab/sub-tab visibility convention:
  `lib/TemplateUtility.php:656-678, 730-752`; chrome: `lib/TemplateUtility.php:95-211,
  265-311`.
- Login: `modules/login/LoginUI.php:179-435`, `modules/login/Login.tpl`,
  `index.php:210-269`. Dashboard: `modules/home/HomeUI.php:40-205`,
  `modules/home/Home.tpl`, `modules/home/dataGrids.php`.
- Candidates: `modules/candidates/CandidatesUI.php:73-3922`,
  `modules/candidates/{Add,Edit,Show,Search}.tpl`, `dataGrids.php:48-72`.
- Job orders: `modules/joborders/JobOrdersUI.php:83-1990`,
  `modules/joborders/{Add,Edit,Show,Search,ConsiderSearchModal,CreateAttachmentModal,AddModalPopup}.tpl`,
  `validator.js`.
- Pipelines/activities: `lib/Pipelines.php:61,111,295,405`;
  `modules/candidates/AddActivityScheduleEventModal.tpl`,
  `modules/candidates/ChangeStatusModal.tpl`; `modules/activity/ActivityUI.php`.
- Companies/contacts: `modules/companies/CompaniesUI.php:60-1214`,
  `modules/contacts/ContactsUI.php:68-1232`, their `{Add,Edit,Show,Search}.tpl`,
  `ColdCallList.tpl`, `lib/VCard.php`.
- Calendar: `modules/calendar/CalendarUI.php:44-759`, `modules/calendar/Calendar.tpl`.
- Lists/search: `modules/lists/ListsUI.php:54-389`, `modules/lists/*.tpl`,
  `modules/lists/ajax/*.php`; `modules/home/HomeUI.php:155-205`.
- Reports/graphs: `modules/reports/ReportsUI.php:44-752`,
  `modules/reports/*.tpl`, `modules/graphs/GraphsUI.php`.
- Import/export: `modules/import/ImportUI.php:48-2164`, `modules/import/*.tpl`,
  `modules/export/ExportUI.php:41-152`, `lib/Export.php`, `lib/DataGrid.php:1444`.
- Careers: `modules/careers/CareersUI.php:47-1127`, `lib/CareerPortal.php:77`,
  `lib/FileUtility.php:166-203`, `index.php:145-170`.

---

## Unverified / open questions

- **Access-level resolution.** The numeric levels used here come from
  `constants.php:74-82`; the authoritative module/action → required-level matrix is
  [doc 14](14-permissions-access-control-matrix.md). Note doc 14's key finding: the
  dotted "secured object" keys (e.g. `candidates.add`) do **not** resolve per-object —
  the ACL map is commented out (`lib/ACL.php:20-46`), so every guard compares against
  the user's single `access_level`.
- **Forgot-password flow appears non-functional** in this tree:
  `onForgotPassword()` calls `Users::getPassword()` and uses
  `PASSWORD_RESET_SUBJECT`/`PASSWORD_RESET_BODY`, none of which are defined in the
  repository (`modules/login/LoginUI.php:459-468`). Treat the Forgot Password link
  as present-but-unverified.
- **Lists web actions carry no per-action ACL.** Only the lists AJAX endpoints
  enforce Edit on `lists`; the controller's `showList`/`listByView`/delete web
  actions are gated only by login (`modules/lists/ListsUI.php`; see the lists
  module doc). Whether a logged-in Read-only user can delete a list via the web
  action is a flagged finding, not confirmed at runtime.
- **Candidate Send E-Mail level** is effectively `ACCESS_LEVEL_SA` due to a
  two-stage guard (`CandidatesUI.php:350-358`) and also requires `MAIL_MAILER`
  configured; the exact admin configuration was not exercised.
- **`export`/`onExport` CSV** only yields candidate data; other data-item types
  produce an empty file (`lib/Export.php:132-141`). The datagrid export
  (`exportByDataGrid`) works for any grid. Behavior was read from source, not run.
- **Resume parsing / mass-import auto-fill** depends on `LicenseUtility` parsing
  being enabled and external text-extraction binaries being configured
  (`lib/DocumentToText.php`); availability is installation-specific and not
  verified here.
- Field lists above were verified against the `Add`/`Edit`/`Search` templates that
  were spot-read (candidates `Add.tpl`, candidates `Search.tpl`, job-order
  `Edit.tpl`). Field sets on company/contact forms are summarized from the module
  docs rather than a line-by-line template read; treat the company/contact field
  enumerations as indicative, with the cited actions authoritative.
