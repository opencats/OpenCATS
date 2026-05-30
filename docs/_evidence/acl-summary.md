# ACL Summary (raw, collected from per-module agents)

Scratch evidence feeding doc 14 (access-control matrix). Each line is `module.action => required ACCESS_LEVEL`,
as extracted verbatim from each `getUserAccessLevel('key') < ACCESS_LEVEL_X` dispatch guard. Notes capture
guard-key mismatches (action dispatched under a different permission key than its module).

## candidates (modules/candidates/CandidatesUI.php)
- candidates.show => READ
- candidates.add => EDIT
- candidates.edit => EDIT
- candidates.delete => DELETE
- candidates.search => READ
- candidates.viewResume => READ
- considerForJobSearch => guard key candidates.search @ EDIT
- addToPipeline => guard key pipelines.addToPipeline @ EDIT
- candidates.addCandidateTags => EDIT
- addActivity => guard key pipelines.addActivity @ EDIT
- changeStatus => guard key pipelines.changeStatus @ EDIT
- removeFromPipeline => guard key pipelines.removeFromPipeline @ DELETE
- candidates.addEditImage => EDIT
- candidates.createAttachment => EDIT
- administrativeHideShow => guard key candidates.hidden @ SA
- candidates.deleteAttachment => DELETE
- candidates.emailCandidates => SA (two-stage READ then SA, effective SA)
- candidates.show_questionnaire => READ
- linkDuplicate/merge/mergeInfo/removeDuplicity/addDuplicates => guard key candidates.duplicates @ SA
- listByView/default => guard key candidates.list @ READ

## joborders (modules/joborders/JobOrdersUI.php)
- joborders.show => READ
- addJobOrderPopup => guard key joborders.add @ EDIT
- joborders.add => EDIT
- joborders.edit => EDIT
- joborders.delete => DELETE
- joborders.search => READ
- addActivity => guard key pipelines.addActivity @ EDIT
- changeStatus => guard key pipelines.changeStatus @ EDIT
- joborders.considerCandidateSearch => EDIT
- addToPipeline => guard key pipelines.addToPipeline @ EDIT
- addCandidateModal => guard key candidates.add @ EDIT
- removeFromPipeline => guard key pipelines.removeFromPipeline @ DELETE
- joborders.createAttachment => EDIT
- joborders.deleteAttachment => DELETE
- joborders.administrativeHideShow => SA
- listByView/default => guard key joborders.list @ READ

## companies (modules/companies/CompaniesUI.php)
- companies.show => READ
- companies.internalPostings => READ
- companies.add => EDIT
- companies.edit => EDIT
- companies.delete => DELETE
- companies.search => READ
- companies.createAttachment => EDIT
- companies.deleteAttachment => DELETE
- companies.list (default) => READ
- inline gates: companies.show @ DEMO (history flag, :482); companies.email @ DEMO (edit, :703)

## contacts (modules/contacts/ContactsUI.php)
- contacts.show => READ
- contacts.add => EDIT
- contacts.edit => EDIT
- contacts.delete => DELETE (POST-only)
- contacts.search => READ
- contacts.addActivityScheduleEvent => EDIT
- contacts.showColdCallList => READ
- contacts.downloadVCard => READ
- contacts.list (default) => READ
- inline gates: contacts.show @ DEMO (history); contacts.emailContact == DEMO (email gate)

## activity (modules/activity/ActivityUI.php)
- viewByDate => (none — auth only)
- listByViewDataGrid => (none)
- default => (none)
- NOTE: no getUserAccessLevel guards in this controller. Related AJAX: ajax/editActivity.php guards contacts.editActivity @ EDIT; ajax/deleteActivity.php guards contacts.deleteActivity @ EDIT.

## calendar (modules/calendar/CalendarUI.php)
- calendar.addEvent => EDIT
- calendar.editEvent => EDIT (own; SA via calendar.show to edit others')
- calendar.deleteEvent => DELETE (own; SA via calendar.show to delete others')
- calendar.dynamicData => (none in handler; tab-level READ@calendar)
- showCalendar/default => (no hard gate; tab READ@calendar; calendar.show >= SA enables "show other users")

## home (modules/home/HomeUI.php) — NO ACL guards (auth only)
- home.home / quickSearch / addSavedSearch / deleteSavedSearch => (none)

## lists (modules/lists/ListsUI.php) — NO controller ACL guards; AJAX endpoints guard lists @ EDIT
- listByView / showList / quickActionAddToListModal / addToListFromDatagridModal / removeFromListDatagrid / deleteStaticList => (none, web)
- ajax addToLists/deleteList/editListName/newList => getAccessLevel('lists') @ EDIT

## login (modules/login/LoginUI.php) — _authenticationRequired=false; NO guards (public by design)
- attemptLogin / forgotPassword / noCookiesModal / showLoginForm => (none)

## settings (modules/settings/SettingsUI.php) — see acl detail; most POST writes @ SA, reads @ DEMO
- key highlights: most POST => SA; reads/GET => DEMO; changeVersionName => ROOT; ajax_tags_add/del/upd => (none); addEmailTemplate/deleteEmailTemplate => SA (enforced in handler not switch); ajax/backup.php => SA
- (full per-action list captured in docs/modules/settings.md ACL-SUMMARY)

## reports (modules/reports/ReportsUI.php) — every action @ READ; EEO actions also require canSeeEEOInfo()
- graphView / generateJobOrderReportPDF / showSubmissionReport / showPlacementReport / customizeJobOrderReport / reports / default => READ
- customizeEEOReport / generateEEOReportPreview => READ + canSeeEEOInfo()

## import (modules/import/ImportUI.php)
- revert => EDIT; viewerrors/viewpending/importSelectType/whatIsBulkResumes/showMassImport/massImportDocument/massImportEdit => (none)
- importUploadFile => EDIT; massImport => EDIT; importBulkResumes => SA; deleteBulkResumes => SA
- import => EDIT; onImportFieldsDelimited => EDIT (foreign field path => SA)

## careers (modules/careers/CareersUI.php) — _authenticationRequired=false; ALL public, NO guards
- all p=/pa= branches => (none — public); gated only by career portal 'enabled' setting

## graphs (modules/graphs/GraphsUI.php) — _authenticationRequired=false; NO getUserAccessLevel
- tier-1 (testGraph/wordVerify/jobOrderReportGraph/generic/genericPie) => (none, public)
- tier-2 (activity/newCandidates/newJobOrders/newSubmissions/miniPlacementStatistics/miniJobOrderPipeline) => (none) but require isLoggedIn()

## export (modules/export/ExportUI.php) — auth only, NO guards
- export / exportByDataGrid / default => (none). Note: Export::getFormattedOutput only supports DATA_ITEM_CANDIDATE.

## queue (modules/queue/QueueUI.php) — no-op controller; auth only
- default => (none). Real logic in lib/QueueProcessor.php + QueueCLI.php.

## rss (modules/rss/RssUI.php) — _authenticationRequired=false; public
- jobOrders / default => (none, public)

## xml (modules/xml/XmlUI.php) — _authenticationRequired=false; public
- jobOrders / default => (none, public) + allowBrowse==1 portal-setting gate

## wizard (modules/wizard/WizardUI.php) — _authenticationRequired=false; NO guards
- ajax_getPage / show => (none). Note: ajax_getPage eval()s session-stored PHP per page.

## install (modules/install/CATSUI.php) — class name CATSUI != module; _authenticationRequired=false; empty handleRequest()
- real logic in modules/install/ajax/ui.php (install:ui via ajax.php), 25 actions, all (none), gated by INSTALL_BLOCK file
- attachmentsReindex => SA (only when INSTALL_BLOCK exists); attachmentsToThreeDirectory => ROOT

## attachments (modules/attachments/AttachmentsUI.php) — auth only; NO getUserAccessLevel
- getAttachment => (none); gated by session + md5(directoryName)==directoryNameHash; retrieval passes site_id=false (cross-site read gap)
