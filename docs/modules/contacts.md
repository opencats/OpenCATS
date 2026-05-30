# Module: contacts

Source-derived design documentation for the OpenCATS **contacts** module. Every claim below is cited to a file and line that was opened directly. Nothing here is generic; it reflects only this repository's code as of the reviewed commit.

## Overview

The controller is declared as:

```php
class ContactsUI extends UserInterface
```

(`modules/contacts/ContactsUI.php:43`)

Constructor settings (`modules/contacts/ContactsUI.php:61-74`):

- `$this->_authenticationRequired = true;` (`ContactsUI.php:65`) — all actions require login.
- `$this->_moduleDirectory = 'contacts';` (`ContactsUI.php:66`)
- `$this->_moduleName = 'contacts';` (`ContactsUI.php:67`)
- `$this->_moduleTabText = 'Contacts';` (`ContactsUI.php:68`)
- `$this->_subTabs` (`ContactsUI.php:69-73`) defines three sub-tabs:
  - `'Add Contact'` → `?m=contacts&a=add` guarded inline by `*al=ACCESS_LEVEL_EDIT@contacts.add` (`ContactsUI.php:70`)
  - `'Search Contacts'` → `?m=contacts&a=search` (`ContactsUI.php:71`)
  - `'Cold Call List'` → `?m=contacts&a=showColdCallList` (`ContactsUI.php:72`)

Two class constants control display truncation: `NOTES_MAXLEN = 500` (`ContactsUI.php:48`), `TRUNCATE_CLIENT_NAME = 22` (`ContactsUI.php:53`), `TRUNCATE_TITLE = 24` (`ContactsUI.php:58`).

Included libraries (`ContactsUI.php:30-40`): `StringUtility`, `ResultSetUtility`, `DateUtility`, `Contacts`, `Companies`, `JobOrders`, `ActivityEntries`, `Export`, `ExtraFields`, `Calendar`, `CommonErrors`. `Search.php` (`ContactsUI.php:145`) and `VCard.php` (`ContactsUI.php:187`) are included lazily inside their switch cases.

Dispatch is via `handleRequest()` (`ContactsUI.php:77`), which first runs the hook `CONTACTS_HANDLE_REQUEST` (`ContactsUI.php:81`) then a `switch ($action)` (`ContactsUI.php:83`).

## Action catalog

One row per `case` in the `switch` (`modules/contacts/ContactsUI.php:83-201`). All ACL guards use the literal form `if ($this->getUserAccessLevel('<key>') < ACCESS_LEVEL_<X>) { CommonErrors::fatal(...) }`.

| Action (`a=`) | Exact ACL guard | Required level | Handler method | lib calls (selected) | Template |
|---|---|---|---|---|---|
| `show` | `getUserAccessLevel('contacts.show') < ACCESS_LEVEL_READ` (`ContactsUI.php:86`) | `ACCESS_LEVEL_READ` | `show()` (`ContactsUI.php:90,240`) | `Contacts::get`, `JobOrders::getAll`, `ActivityEntries::getAllByDataItem`, `Contacts::getUpcomingEvents`, `extraFields->getValuesForShow` | `Show.tpl` (`ContactsUI.php:421`) |
| `add` (GET) | `getUserAccessLevel('contacts.add') < ACCESS_LEVEL_EDIT` (`ContactsUI.php:94`) | `ACCESS_LEVEL_EDIT` | `add()` (`ContactsUI.php:104,427`) | `Companies::getDefaultCompany`/`get`, `Contacts::getAll`, `extraFields->getValuesForAdd` | `Add.tpl` (`ContactsUI.php:476`) |
| `add` (POST) | same as above (`ContactsUI.php:94`) | `ACCESS_LEVEL_EDIT` | `onAdd()` (`ContactsUI.php:100,482`) | `Companies::getDepartments`/`updateDepartments`, `Contacts::add`, `extraFields->setValuesOnEdit` | redirect (`ContactsUI.php:583-591`) |
| `edit` (GET) | `getUserAccessLevel('contacts.edit') < ACCESS_LEVEL_EDIT` (`ContactsUI.php:110`) | `ACCESS_LEVEL_EDIT` | `edit()` (`ContactsUI.php:120,598`) | `Contacts::getForEditing`, `Companies::getSelectList`/`getDepartments`, `Users::getSelectList`, `EmailTemplates::getByTag`, `Contacts::getAll` | `Edit.tpl` (`ContactsUI.php:687`) |
| `edit` (POST) | same as above (`ContactsUI.php:110`) | `ACCESS_LEVEL_EDIT` | `onEdit()` (`ContactsUI.php:116,693`) | `Contacts::get`, `Users::get`, `EmailTemplates::getByTag`, `Companies::getDepartments`/`updateDepartments`, `Contacts::update`, `extraFields->setValuesOnEdit` | redirect (`ContactsUI.php:866`) |
| `delete` (POST only) | `getUserAccessLevel('contacts.delete') < ACCESS_LEVEL_DELETE` (`ContactsUI.php:126`) | `ACCESS_LEVEL_DELETE` | `onDelete()` (`ContactsUI.php:132,874`); non-POST → `CommonErrors::fatal(COMMONERROR_BADFIELDS...)` (`ContactsUI.php:136`) | `Contacts::delete`, MRU `removeEntry` | redirect (`ContactsUI.php:897`) |
| `search` (GET form) | `getUserAccessLevel('contacts.search') < ACCESS_LEVEL_READ` (`ContactsUI.php:141`) | `ACCESS_LEVEL_READ` | `search()` (`ContactsUI.php:153,903`) | `SavedSearches::get` | `Search.tpl` (`ContactsUI.php:919`) |
| `search` (getback) | same as above (`ContactsUI.php:141`) | `ACCESS_LEVEL_READ` | `onSearch()` (`ContactsUI.php:149,925`) | `ContactsSearch::byFullName`/`byCompanyName`/`byTitle`, `ExportUtility::getForm`, `SavedSearches::add`/`get` | `Search.tpl` (`ContactsUI.php:1076`) |
| `addActivityScheduleEvent` (GET) | `getUserAccessLevel('contacts.addActivityScheduleEvent') < ACCESS_LEVEL_EDIT` (`ContactsUI.php:159`) | `ACCESS_LEVEL_EDIT` | `addActivityScheduleEvent()` (`ContactsUI.php:169,1096`) | `Contacts::get`, `Contacts::getNonClosedJobOrdersArray`, `Calendar::getAllEventTypes` | `AddActivityScheduleEventModal.tpl` (`ContactsUI.php:1135`) |
| `addActivityScheduleEvent` (POST) | same as above (`ContactsUI.php:159`) | `ACCESS_LEVEL_EDIT` | `onAddActivityScheduleEvent()` → `_addActivityScheduleEvent()` (`ContactsUI.php:165,1141,1325`) | `ActivityEntries::getTypes`/`add`, `Calendar::addEvent`/`getAllEventTypes` | `AddActivityScheduleEventModal.tpl` (`ContactsUI.php:1613`) |
| `showColdCallList` | `getUserAccessLevel('contacts.showColdCallList') < ACCESS_LEVEL_READ` (`ContactsUI.php:175`) | `ACCESS_LEVEL_READ` | `showColdCallList()` (`ContactsUI.php:179,1082`) | `Contacts::getColdCallList` | `ColdCallList.tpl` (`ContactsUI.php:1092`) |
| `downloadVCard` | `getUserAccessLevel('contacts.downloadVCard') < ACCESS_LEVEL_READ` (`ContactsUI.php:183`) | `ACCESS_LEVEL_READ` | `downloadVCard()` (`ContactsUI.php:189,1159`) | `Contacts::get`, `Companies::get`, `VCard::set*`, `VCard::printVCardWithHeaders` | none (sends vCard headers) |
| `listByView` / `default` | `getUserAccessLevel('contacts.list') < ACCESS_LEVEL_READ` (`ContactsUI.php:195`) | `ACCESS_LEVEL_READ` | `listByView()` (`ContactsUI.php:199,207`) | `Contacts::getCount`, `DataGrid::get` | `Contacts.tpl` (`ContactsUI.php:234`) |

Note: the `default` branch falls through to `listByView` (`ContactsUI.php:193-200`), so any unrecognized action is treated as the list view under `contacts.list`.

## Per-action detail

### listByView (`ContactsUI.php:207-235`)
Runs hook `CONTACTS_LIST_BY_VIEW_TOP` (`:209`). Retrieves recent datagrid parameters via `DataGrid::getRecentParamaters("contacts:ContactsListByViewDataGrid")` (`:211`); if empty, defaults to `rangeStart=0, maxResults=15, filterVisible=false` (`:217-219`). Builds the grid with `DataGrid::get(...)` (`:222`). Assigns `active`, `dataGrid`, `userID` (from `$_SESSION['CATS']->getUserID()`), `errMessage` (`:224-227`). Instantiates `new Contacts($this->_siteID)` and assigns `totalContacts` from `$contacts->getCount()` (`:229-230`). Runs hook `CONTACTS_LIST_BY_VIEW` (`:232`), then displays `Contacts.tpl` (`:234`).

### show (`ContactsUI.php:240-422`)
- Validates `contactID` from `$_GET` via `isRequiredIDValid`; invalid → `CommonErrors::fatal(COMMONERROR_BADINDEX...)` (`:243-246`).
- `$contacts->get($contactID)` (`:251`); empty → `COMMONERROR_BADINDEX` (`:254-257`).
- Formats `cityAndState` via `StringUtility::makeCityStateString` (`:262`); converts notes to HTML and truncates to `NOTES_MAXLEN` building `shortNotes`/`isShortNotes` (`:270-286`).
- Sets `titleClassContact`/`titleClassCompany` to `jobTitleHot`/`jobTitleCold` based on `isHotContact`/`isHotCompany` (`:289-306`).
- `new JobOrders($this->_siteID)` then `$jobOrders->getAll(JOBORDERS_STATUS_ALL, -1, -1, $contactID)` (`:308-309`); per-row fixes zero dates, sets `linkClass`, and builds `recruiterAbbrName`/`ownerAbbrName` via `StringUtility::makeInitialName` (`:313-346`).
- `new ActivityEntries($this->_siteID)` then `getAllByDataItem($contactID, DATA_ITEM_CONTACT)` (`:349-350`); defaults empty notes to `'(No Notes)'`, missing regarding to `'General'`, builds `enteredByAbbrName` (`:353-372`).
- `$contacts->getUpcomingEvents($contactID)` (`:376`); builds `enteredByAbbrName` per row (`:379-387`).
- Adds an MRU entry via `$_SESSION['CATS']->getMRU()->addEntry(DATA_ITEM_CONTACT, $contactID, firstName lastName)` (`:391-393`).
- Extra fields via `$contacts->extraFields->getValuesForShow($contactID)` (`:396`).
- **History gate:** `$privledgedUser` is `false` when `getUserAccessLevel('contacts.show') < ACCESS_LEVEL_DEMO`, else `true` (`:399-406`). The "View History" link in `Show.tpl:226-231` is shown only when `$privledgedUser` is true.
- Runs hook `CONTACTS_SHOW` (`:419`), displays `Show.tpl` (`:421`).

### add (GET) (`ContactsUI.php:427-477`)
Instantiates `Companies` and `Contacts` (`:429-430`). In HR mode (`$_SESSION['CATS']->isHrMode()`) it uses `getDefaultCompany()` and pulls `getAll(-1, $selectedCompanyID)` for reports-to (`:433-438`). Otherwise, if `selected_company_id` is not a valid GET ID, all selection vars are empty (`:439-444`); if valid, it loads that company and its contacts (`:445-450`). Extra fields from `getValuesForAdd()` (`:453`). Default company resolved via `getDefaultCompany()`/`get()` (`:455-463`). Runs hook `CONTACTS_ADD` (`:465`), displays `Add.tpl` (`:476`).

### onAdd (POST) (`ContactsUI.php:482-593`)
- Requires valid `companyID` in `$_POST` (`:485-488`).
- Phone fields are normalized via `StringUtility::extractPhoneNumber` with raw fallback for work/cell/other (`:490-524`).
- Trims `firstName, lastName, title, department, reportsTo, email1, email2, address, address2, city, state, zip, notes` (`:528-540`); `isHot` via `isChecked` (`:543`); `departmentsCSV` (`:546`).
- Required-field check: `firstName`, `lastName`, `title` — empty → `COMMONERROR_MISSINGFIELDS` (`:549-552`).
- Department list reconciliation: `Companies::getDepartments` + `ListEditor::getDifferencesFromList(..., 'name', 'departmentID', $departmentsCSV)` + `Companies::updateDepartments` (`:555-560`).
- Hook `CONTACTS_ON_ADD_PRE` (`:562`).
- `$contacts->add(...)` (`:565-569`) — see signature below; `<= 0` → `COMMONERROR_RECORDERROR` (`:571-574`). Passes `$this->_userID` for both enteredBy and owner.
- `extraFields->setValuesOnEdit($contactID)` (`:577`).
- Hook `CONTACTS_ON_ADD_POST` (`:579`).
- Redirect: if `$_GET['v']` set and `!= -1` → company show page; else contact show page (`:581-592`).

### edit (GET) (`ContactsUI.php:598-688`)
- Requires valid `contactID` in `$_GET` (`:601-604`).
- `$contacts->getForEditing($contactID)` (`:609`); empty → `COMMONERROR_BADINDEX` (`:612-615`).
- `Companies::getSelectList()` (`:618`), `Users::getSelectList()` (`:620-621`), MRU add (`:624-626`), extra fields via `getValuesForEdit` (`:629`), departments via `getDepartments`/`ListEditor::getStringFromList` (`:632-633`).
- Email template `EmailTemplates::getByTag('EMAIL_TEMPLATE_OWNERSHIPASSIGNCONTACT')` (`:635-638`); `emailTemplateDisabled` set from its `disabled` flag (`:640-647`).
- Reports-to list via `getAll(-1, companyID)` (`:649`).
- **Email gate:** `$canEmail` is `false` when `getUserAccessLevel('contacts.emailContact') == ACCESS_LEVEL_DEMO`, else `true` (`:651-658`).
- Default company resolution (`:660-669`). Hook `CONTACTS_EDIT` (`:671`). Displays `Edit.tpl` (`:687`).

### onEdit (POST) (`ContactsUI.php:693-869`)
- Requires valid `contactID` and `companyID` in `$_POST` (`:696-705`), and `isOptionalIDValid('owner', ...)` (`:708-711`).
- Phone normalization identical to `onAdd` (`:717-751`).
- **Ownership-change email:** if `isChecked('ownershipChange')` and `$owner > 0` (`:755`), loads contact via `Contacts::get`, owner via `Users::get`, builds the email body from `EMAIL_TEMPLATE_OWNERSHIPASSIGNCONTACT` replacing `%CONTOWNER%`, `%CONTFIRSTNAME%`, `%CONTFULLNAME%`, `%CONTCLIENTNAME%`, `%CONTCATSURL%` (`:757-808`). Otherwise email and address are empty (`:811-815`).
- Trims same field set as add (`:817-829`); `isHot`, `leftCompany` via `isChecked` (`:831-832`); `departmentsCSV` (`:835`).
- Required-field check `firstName`/`lastName`/`title` (`:838-841`). Hook `CONTACTS_ON_EDIT_PRE` (`:843`). Department reconciliation (`:846-851`).
- `$contacts->update(...)` (`:853-856`); falsy → `COMMONERROR_RECORDERROR` (`:858`). `extraFields->setValuesOnEdit` (`:862`). Hook `CONTACTS_ON_EDIT_POST` (`:864`). Redirect to contact show (`:866-868`).

### onDelete (POST) (`ContactsUI.php:874-898`)
Requires valid `contactID` in `$_POST` (`:878-881`). Hook `CONTACTS_DELETE_PRE` (`:885`). `$contacts->delete($contactID)` (`:888`); removes MRU entry (`:891-893`); hook `CONTACTS_DELETE_POST` (`:895`); redirects to `m=contacts&a=listByView` (`:897`).

### search (GET form) (`ContactsUI.php:903-920`)
`SavedSearches::get(DATA_ITEM_CONTACT)` (`:905-906`), hook `CONTACTS_SEARCH` (`:908`), assigns blank wildcard fields and `isResultsMode=false`, displays `Search.tpl` (`:919`).

### onSearch (getback) (`ContactsUI.php:925-1077`)
- Requires `$_GET['wildCardString']` present → else `COMMONERROR_WILDCARDSTRING` (`:934-937`).
- Pagination via `SearchPager(CANDIDATES_PER_PAGE, $currentPage, $this->_siteID, $_GET)` (`:951-953`); sort defaults `lastName`/`ASC` (`:955-971`).
- Mode from `getSanitisedInput('mode', $_GET)` (`:979`); dispatch on `new ContactsSearch($this->_siteID)`:
  - `searchByFullName` → `byFullName($query, $sortBy, $sortDirection)` (`:987`)
  - `searchByCompanyName` → `byCompanyName(...)` (`:992`)
  - `searchByTitle` → `byTitle(...)` (`:997`)
  - default → `COMMONERROR_BADINDEX` 'Invalid search mode.' (`:1001`)
- Per-row link classes: `jobLinkHot`/`jobLinkCold` for contact; `jobLinkDead` if `leftCompany==1` else hot/cold for company (`:1005-1027`); `ownerAbbrName` or `'None'` (`:1029-1041`).
- Builds export form via `ExportUtility::getForm(DATA_ITEM_CONTACT, $contactIDs, 40, 15)` (`:1045-1048`); persists the search with `SavedSearches::add(DATA_ITEM_CONTACT, $query, $_SERVER['REQUEST_URI'], false)` (`:1051-1057`). Hook `CONTACTS_ON_SEARCH` (`:1062`). Displays `Search.tpl` with `isResultsMode=true` (`:1076`).

### addActivityScheduleEvent (GET modal) (`ContactsUI.php:1096-1138`)
- Requires valid `contactID` in `$_GET` → `CommonErrors::fatalModal(COMMONERROR_BADINDEX...)` (`:1099-1102`).
- `Contacts::get`, `Contacts::getNonClosedJobOrdersArray` (regarding list), `Calendar::getAllEventTypes` (`:1106-1112`).
- `onlyScheduleEvent` from `isChecked('onlyScheduleEvent', $_GET)` (`:1115`). Hook `CONTACTS_ADD_ACTIVITY_SCHEDULE_EVENT` (`:1117`).
- `allowEventReminders` true only when `SystemUtility::isSchedulerEnabled() && !$_SESSION['CATS']->isDemo()` (`:1119-1126`).
- Assigns and displays `AddActivityScheduleEventModal.tpl` with `isFinishedMode=false` (`:1128-1137`).

### onAddActivityScheduleEvent (POST) → _addActivityScheduleEvent (`ContactsUI.php:1141-1152`, `:1325-1616`)
`onAddActivityScheduleEvent` validates `isOptionalIDValid('regardingID', $_POST)` (`:1144-1147`) and delegates to private `_addActivityScheduleEvent($regardingID)` (`:1151`).

In `_addActivityScheduleEvent`:
- Requires valid `contactID` in `$_POST` → `fatalModal` (`:1338-1341`). (Note: the `CONTACT_ON_ADD_ACTIVITY_SCHEDULE_EVENT_PRE` hook is commented out at `:1345`.)
- **Activity branch** (`isChecked('addActivity')`, `:1347`): validates `activityTypeID` via `isRequiredIDValid` and `ResultSetUtility::findRowByColumnValue` against `ActivityEntries::getTypes()` (`:1349-1362`); parses optional date/time honoring `$_SESSION['CATS']->isDateDMY()` and 12→24h conversion (`:1366-1403`); calls `ActivityEntries::add($contactID, DATA_ITEM_CONTACT, $activityTypeID, $activityNote, $this->_userID, $regardingID, $activityDateCreated)` (`:1406-1414`).
- **Schedule-event branch** (`isChecked('scheduleEvent')`, `:1428`): validates `dateAdd`, `eventTypeID`, `allDay`, and (when not all-day) `hour`/`minute`/`meridiem` (`:1431-1525`); builds the MySQL date; computes `eventJobOrderID = $regardingID` if `> 0` else `null` (`:1536-1543`); calls `Calendar::addEvent(...)` with `DATA_ITEM_CONTACT` (`:1545-1551`); `<= 0` → `COMMONERROR_RECORDERROR` modal (`:1553-1556`); builds the result `eventHTML` (`:1569-1575`).
- Runs hook `CANDIDATE_ON_ADD_ACTIVITY_CHANGE_STATUS_POST` (`:1601`) — note this is the *candidate* hook key, reused here.
- Displays `AddActivityScheduleEventModal.tpl` with `isFinishedMode=true` (`:1613-1615`).

### showColdCallList (`ContactsUI.php:1082-1093`)
`$contacts->getColdCallList()` (called with no args; `:1086`), hook `CONTACTS_COLD_CALL_LIST` (`:1088`), displays `ColdCallList.tpl` (`:1092`).

### downloadVCard (`ContactsUI.php:1159-1232`)
- Requires valid `contactID` in `$_GET` (`:1162-1165`).
- `Contacts::get`, `Companies::get($contact['companyID'])` (`:1169-1173`); empty contact → `COMMONERROR_BADINDEX` (`:1176-1179`).
- Builds `new VCard()` (`:1182`): `setName(lastName, firstName)` (`:1184`), phone numbers with types `PREF;WORK;VOICE` and `CELL;VOICE` (`:1186-1194`), address (splitting a single-field newline address when address2 empty, `:1198-1214`), email1, company URL, title, organization (`:1216-1227`). A `FIXME` notes fax is not yet modeled (`:1196`). Hook `CONTACTS_GET_VCARD` (`:1229`), then `printVCardWithHeaders()` (`:1231`).

### _formatListByViewResults (`ContactsUI.php:1242-1313`)
Private helper that sets `ownerAbbrName`, contact/company link classes, and truncates `companyName`/`title` to the class constants. Runs hook `CONTACTS_FORMAT_LIST_BY_VIEW` (`:1310`). Note: this helper is defined but is **not** called from `listByView()` in this file (the datagrid does its own formatting); see Unverified.

## Templates

All templates live in `modules/contacts/`.

- **Contacts.tpl** (list/home): includes `js/highlightrows.js, js/export.js, js/dataGrid.js, js/dataGridFilters.js` (`Contacts.tpl:2`). Renders the datagrid (`:75`), "Only My Contacts"/"Only Hot Contacts" filter checkboxes (`:31-37`), and an empty-state "Add contacts" panel gated by `getUserAccessLevel('contacts.add') >= ACCESS_LEVEL_EDIT` (`:93`).
- **Show.tpl** (detail): `use OpenCATS\UI\QuickActionMenu;` (`Show.tpl:4`); JS `js/activity.js, js/attachment.js` (`:6`). vCard link (`:41`), quick action menu seeded with `getAccessLevel('contacts.edit')` (`:38`). Inline ACL gates:
  - Schedule-Event link: `getUserAccessLevel('contacts.addActivityScheduleEvent') >= ACCESS_LEVEL_EDIT` (`:199`)
  - Edit link: `getUserAccessLevel('contacts.edit') >= ACCESS_LEVEL_EDIT` (`:210`)
  - Delete form (POST to `a=delete`): `getUserAccessLevel('contacts.delete') >= ACCESS_LEVEL_DELETE` (`:216`)
  - View History link: `$this->privledgedUser` (`:226`); links to `dataItemType=300` (`:227`)
  - Edit activity icon: `getUserAccessLevel('contacts.editActivity') >= ACCESS_LEVEL_EDIT` (`:294`)
  - Delete activity icon: `getUserAccessLevel('contacts.deleteActivity') >= ACCESS_LEVEL_EDIT` (`:299`)
  - "Log an Activity / Schedule Event" link: `getUserAccessLevel('contacts.logActivityScheduleEvent') >= ACCESS_LEVEL_EDIT` (`:309`)
- **Add.tpl**: form posts to `a=add&v=<companyID or -1>` (`Add.tpl:18`); JS `validator.js, company.js, sweetTitles.js, listEditor.js, contact.js, suggest.js` (`:2`). Company autocomplete + "Internal Contact" default-company toggle (`:50-54`); department list editor (`:75-80`); reports-to select (`:89-94`); extra fields rendered via `addHTML` (`:230`).
- **Edit.tpl**: form posts to `a=edit` (`Edit.tpl:18`); JS includes `validator.js, ..., contact.js, company.js` (`:2`). Owner select with `ownershipChange` checkbox shown on change, disabled when `!canEmail` (`:259-272`); `leftCompany` toggle reveals "Previous Company" label (`:48,127`); extra fields via `editHTML` (`:249`).
- **Search.tpl**: GET form with `getback` hidden field (`Search.tpl:26`); mode select offers `searchByFullName`/`searchByCompanyName`/`searchByTitle` (`:32-34`); JS `modules/contacts/validator.js, js/searchSaved.js, ...` (`:2`). Results table links to contact/company show pages; export form header/footer/menu (`:53,106,107`).
- **AddActivityScheduleEventModal.tpl**: modal header chooses "Log Activity" vs "Schedule Event" by `onlyScheduleEvent` (`:3-7`); form posts to `a=addActivityScheduleEvent` (`:15`); activity-type options use constants `ACTIVITY_CALL`, `ACTIVITY_CALL_TALKED`, `ACTIVITY_CALL_LVM`, `ACTIVITY_CALL_MISSED`, `ACTIVITY_EMAIL`, `ACTIVITY_MEETING`, `ACTIVITY_OTHER` (`:80-86`); event-type select pre-selects `CALENDAR_EVENT_INTERVIEW` (`:107`); reminder area hidden unless `allowEventReminders` (`:155`). Finished mode renders activity/event result summaries (`:208-229`). JS `modules/contacts/activityvalidator.js, js/activity.js` (`:4,6`).
- **ColdCallList.tpl**: sortable table of company/first/last/title/work-phone; JS `js/sorttable.js, js/highlightrows.js` (`:2`).
- **Error.tpl** / **ErrorModal.tpl**: fatal-error rendering surfaces used by `CommonErrors::fatal`/`fatalModal`.

## JavaScript

- **validator.js** (`modules/contacts/validator.js`): `checkAddForm`/`checkEditForm` both validate first name, last name, company, title (`:11-45`); individual checks `checkFirstName`/`checkLastName`/`checkCompany`/`checkTitle` color the matching label red on failure (`:77-156`). `checkSearchByFullNameForm`/`checkSearchByCompanyNameForm` validate the wildcard fields (`:47-75`, `:158-196`). Referenced by Add.tpl, Edit.tpl, Search.tpl.
- **activityvalidator.js** (`modules/contacts/activityvalidator.js`): `checkActivityForm` runs `checkActivityType` (only when `addActivity` checked, `:22-46`) and `checkEventTitle` (only when `scheduleEvent` checked, `:48-72`). Referenced by AddActivityScheduleEventModal.tpl.

## lib/ dependencies (cited)

`lib/Contacts.php` (`class Contacts`, `lib/Contacts.php:43`):

- `public function add($companyID, $firstName, $lastName, $title, $department, $reportsTo, $email1, $email2, $phoneWork, $phoneCell, $phoneOther, $address, $address2, $city, $state, $zip, $isHot, $notes, $enteredBy, $owner)` (`Contacts.php:83-85`) — inserts a `contact` row with `left_company` hard-coded to `0` (`:137`), calls `History::storeHistoryNew(DATA_ITEM_CONTACT, ...)` (`:176-177`), returns new ID or `-1`.
- `public function update($contactID, $companyID, $firstName, $lastName, $title, $department, $reportsTo, $email1, $email2, $phoneWork, $phoneCell, $phoneOther, $address, $address2, $city, $state, $zip, $isHot, $leftCompany, $notes, $owner, $email, $emailAddress)` (`Contacts.php:210-213`).
- `public function delete($contactID)` (`Contacts.php:358`) — deletes the `contact` row, resets dependents' `reports_to` to `-1` (`:372-384`), deletes `saved_list_entry` rows (`:387-400`), deletes extra-field values (`:403`), records `storeHistoryDeleted` (`:405-406`).
- `public function getCount()` (`Contacts.php:414`) — `COUNT(*)` of `contact` for the site.
- `public function get($contactID)` (`Contacts.php:435`).
- `public function getForEditing($contactID)` (`Contacts.php:511`).
- `public function getAll($userID = -1, $companyID = -1)` (`Contacts.php:561`).
- `public function getUpcomingEvents($contactID)` (`Contacts.php:638`) — delegates to `Calendar::getUpcomingEventsByDataItem(DATA_ITEM_CONTACT, $contactID)` (`:641-643`).
- `public function getNonClosedJobOrdersArray($contactID)` (`Contacts.php:713`).
- `public function getColdCallList($userID = -1, $companyID = -1)` (`Contacts.php:750`).

`lib/Companies.php` (`class Companies`):
- `public function get($companyID)` (`Companies.php:319`)
- `public function getDefaultCompany()` (`Companies.php:459`)
- `public function getSelectList()` (`Companies.php:488`)
- `public function getDepartments($companyID)` (`Companies.php:602`)
- `public function updateDepartments($companyID, $updates)` (`Companies.php:631`)

`lib/ActivityEntries.php` (`class ActivityEntries`):
- `public function add($dataItemID, $dataItemType, $activityType, $activityNotes, $enteredBy, $jobOrderID = -1, $dateCreated = false)` (`ActivityEntries.php:88-89`)
- `public function getAllByDataItem($dataItemID, $dataItemType)` (`ActivityEntries.php:470`)
- `public function getTypes()` (`ActivityEntries.php:590`)

`lib/Calendar.php` (`class Calendar`):
- `public function addEvent($type, $date, $description, $allDay, $enteredBy, $dataItemID, $dataItemType, $jobOrderID, $title, $duration, $reminderEnabled, $reminderEmail, $reminderTime, $isPublic, $timeZoneOffset)` (`Calendar.php:323-326`)
- `public function getAllEventTypes()` (`Calendar.php:257`)
- `public function getUpcomingEventsByDataItem($dataItemType, $dataItemID)` (`Calendar.php:617`)

`lib/VCard.php` (`class VCard`), methods used by `downloadVCard`:
- `public function setName($lastName, $firstName, $additionalNames = '', $prefix = '', $suffix = '', $formattedName = '')` (`VCard.php:71-72`)
- `public function setPhoneNumber($phoneNumber, $type = 'VOICE')` (`VCard.php:137`)
- `public function setEmail($emailAddress)` (`VCard.php:151`)
- `public function setTitle($title)` (`VCard.php:165`)
- `public function setOrganization($organization)` (`VCard.php:179`)
- `public function setAddress($streetAddress, $extendedAddress, $city, $region, $postalCode, $postOfficeAddress = '', $country = '', $label = '', $type = '')` (`VCard.php:203-205`)
- `public function setURL($url, $type = '')` (`VCard.php:268`)
- `public function printVCardWithHeaders()` (`VCard.php:368`)

### dataGrids.php (`modules/contacts/dataGrids.php`)
- `class ContactsListByViewDataGrid extends ContactsDataGrid` (`:38`) — default columns Attachments/First Name/Last Name/Company/Title/Work Phone/Owner/Created/Modified (`:54-64`); default sort `dateCreatedSort DESC` (`:51-52`); action area adds "Add To List" popup (`m=lists&a=addToListFromDatagridModal&dataItemType=DATA_ITEM_CONTACT`) and "Export" (`m=export&a=exportByDataGrid`) (`:82-83`).
- `class contactSavedListByViewDataGrid extends ContactsDataGrid` (`:92`) — same columns; action area offers "Remove From This List" and "Export" (`:136-137`).
- `ContactsDataGrid` itself is defined in `lib/Contacts.php:856` with `getSQL(...)` at `lib/Contacts.php:1042`.

## Hooks fired (keys + cites)

| Hook key | Location |
|---|---|
| `CONTACTS_HANDLE_REQUEST` | `ContactsUI.php:81` |
| `CONTACTS_LIST_BY_VIEW_TOP` | `ContactsUI.php:209` |
| `CONTACTS_LIST_BY_VIEW` | `ContactsUI.php:232` |
| `CONTACTS_SHOW` | `ContactsUI.php:419` |
| `CONTACTS_ADD` | `ContactsUI.php:465` |
| `CONTACTS_ON_ADD_PRE` | `ContactsUI.php:562` |
| `CONTACTS_ON_ADD_POST` | `ContactsUI.php:579` |
| `CONTACTS_EDIT` | `ContactsUI.php:671` |
| `CONTACTS_ON_EDIT_PRE` | `ContactsUI.php:843` |
| `CONTACTS_ON_EDIT_POST` | `ContactsUI.php:864` |
| `CONTACTS_DELETE_PRE` | `ContactsUI.php:885` |
| `CONTACTS_DELETE_POST` | `ContactsUI.php:895` |
| `CONTACTS_SEARCH` | `ContactsUI.php:908` |
| `CONTACTS_ON_SEARCH` | `ContactsUI.php:1062` |
| `CONTACTS_COLD_CALL_LIST` | `ContactsUI.php:1088` |
| `CONTACTS_ADD_ACTIVITY_SCHEDULE_EVENT` | `ContactsUI.php:1117` |
| `CONTACTS_GET_VCARD` | `ContactsUI.php:1229` |
| `CONTACTS_FORMAT_LIST_BY_VIEW` | `ContactsUI.php:1310` |
| `CANDIDATE_ON_ADD_ACTIVITY_CHANGE_STATUS_POST` | `ContactsUI.php:1601` (candidate-prefixed key reused in `_addActivityScheduleEvent`) |
| `CONTACT_ON_ADD_ACTIVITY_SCHEDULE_EVENT_PRE` | `ContactsUI.php:1345` — **commented out**, not fired |

## Source evidence

Files read in full: `modules/contacts/ContactsUI.php`, `modules/contacts/Contacts.tpl`, `Show.tpl`, `Add.tpl`, `Edit.tpl`, `Search.tpl`, `AddActivityScheduleEventModal.tpl`, `ColdCallList.tpl`, `Error.tpl`, `ErrorModal.tpl`, `dataGrids.php`, `validator.js`, `activityvalidator.js`.

Lib files opened for signatures/behavior: `lib/Contacts.php` (`add` `:83-180`, `update` `:210-213`, `delete` `:358-407`, `getCount` `:414-427`, `get` `:435`, `getForEditing` `:511`, `getAll` `:561`, `getUpcomingEvents` `:638-644`, `getNonClosedJobOrdersArray` `:713`, `getColdCallList` `:750`, `ContactsDataGrid` `:856`/`getSQL` `:1042`); `lib/Companies.php` (`:319,459,488,602,631`); `lib/ActivityEntries.php` (`:88-89,470,590`); `lib/Calendar.php` (`:257,323-326,617`); `lib/VCard.php` (`:71-72,137,151,165,179,203-205,268,368`).

## ACL keys referenced (controller + templates)

Controller switch guards: `contacts.show` (READ), `contacts.add` (EDIT), `contacts.edit` (EDIT), `contacts.delete` (DELETE), `contacts.search` (READ), `contacts.addActivityScheduleEvent` (EDIT), `contacts.showColdCallList` (READ), `contacts.downloadVCard` (READ), `contacts.list` (READ). Additional keys checked but not switch-guarded: `contacts.show` against `ACCESS_LEVEL_DEMO` for history (`:399`); `contacts.emailContact` against `ACCESS_LEVEL_DEMO` for owner-change email (`:651`). Template-only keys: `contacts.editActivity`, `contacts.deleteActivity`, `contacts.logActivityScheduleEvent` (Show.tpl `:294,299,309`).

## Unverified / open questions

- `_formatListByViewResults()` (`ContactsUI.php:1242`) is defined but I found **no call site** within `ContactsUI.php`. The actual row formatting for the list view appears to happen inside `ContactsDataGrid`/`ContactsListByViewDataGrid` (`lib/Contacts.php:856`, `modules/contacts/dataGrids.php:38`), which I did not read in full. Whether this helper is dead code or invoked elsewhere is unverified.
- The list-view ACL key is `contacts.list` (`:195`), but no sub-tab or controller action literally named `list` exists; `listByView`/`default` map to it. I did not inspect `lib/AccessLevels` (or equivalent) to confirm how these dotted keys (`contacts.editActivity`, `contacts.logActivityScheduleEvent`, etc.) resolve to numeric levels; their resolution logic is unverified.
- The `getColdCallList()` call passes no arguments (`:1086`) so it uses the defaults `$userID = -1, $companyID = -1` (`Contacts.php:750`); the SQL body of that method was not read in full.
- `ContactsSearch` (`byFullName`/`byCompanyName`/`byTitle`) lives in `lib/Search.php` (included at `:145`) — its signatures were not opened; only the call sites (`:987,992,997`) were verified.
- The reused hook key `CANDIDATE_ON_ADD_ACTIVITY_CHANGE_STATUS_POST` at `:1601` is verbatim a candidate-module key; whether this is intentional or a copy/paste artifact is unverified.
- `add` redirect branch keys on `$_GET['v']` (`:581`) while the form posts `v` in the action URL query string (Add.tpl `:18`); behavior when `v` is absent on a plain POST was not exercised.

## ACL-SUMMARY

```
contacts.show => ACCESS_LEVEL_READ
contacts.add => ACCESS_LEVEL_EDIT
contacts.edit => ACCESS_LEVEL_EDIT
contacts.delete => ACCESS_LEVEL_DELETE
contacts.search => ACCESS_LEVEL_READ
contacts.addActivityScheduleEvent => ACCESS_LEVEL_EDIT
contacts.showColdCallList => ACCESS_LEVEL_READ
contacts.downloadVCard => ACCESS_LEVEL_READ
contacts.list => ACCESS_LEVEL_READ
```
