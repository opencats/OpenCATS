# Module: companies

## Overview

The companies module controller is declared as:

```php
class CompaniesUI extends UserInterface
```

(modules/companies/CompaniesUI.php:44)

It declares one constant, `NOTES_MAXLEN`, used to truncate the "Misc. Notes" on the show page:

```php
const NOTES_MAXLEN = 500;
```

(modules/companies/CompaniesUI.php:49)

The constructor settings (modules/companies/CompaniesUI.php:52-65):

```php
public function __construct()
{
    parent::__construct();

    $this->_authenticationRequired = true;
    $this->_moduleDirectory = 'companies';
    $this->_moduleName = 'companies';
    $this->_moduleTabText = 'Companies';
    $this->_subTabs = array(
        'Add Company'     => CATSUtility::getIndexName() . '?m=companies&amp;a=add*al=' . ACCESS_LEVEL_EDIT . '@companies.add' . '*hrmode=0',
        'Search Companies' => CATSUtility::getIndexName() . '?m=companies&amp;a=search*hrmode=0',
        'Go To My Company' => CATSUtility::getIndexName() . '?m=companies&amp;a=internalPostings*hrmode=0'
    );
}
```

- `_authenticationRequired = true` (CompaniesUI.php:56) — every action requires a logged-in session.
- The "Add Company" sub-tab carries an inline ACL guard token `*al=ACCESS_LEVEL_EDIT@companies.add` (CompaniesUI.php:61), so the tab is hidden unless the user has `companies.add >= ACCESS_LEVEL_EDIT`.
- All three sub-tabs carry `*hrmode=0` (CompaniesUI.php:61-63), so they are suppressed in HR mode.

Dispatch is performed in `handleRequest()` (CompaniesUI.php:68-202). Before the switch, the request-level hook fires and can short-circuit the whole request:

```php
if (!eval(Hooks::get('CLIENTS_HANDLE_REQUEST'))) return;
```

(CompaniesUI.php:72)

The internal hook names use the `CLIENTS_*` prefix even though the module is named "companies" — this matches the note that OpenCATS internally calls companies "clients".

## Action catalog

`$action = $this->getAction();` (CompaniesUI.php:70). One row per `switch` case in `handleRequest()`. Required level is the threshold the guard compares against with `<` (i.e. the action requires `>=` that level).

| Action (`a=`) | Exact ACL guard | Required level | Handler method | Key lib calls | Template |
|---|---|---|---|---|---|
| `show` | `if ($this->getUserAccessLevel('companies.show') < ACCESS_LEVEL_READ)` (CompaniesUI.php:77) | `ACCESS_LEVEL_READ` | `show()` (CompaniesUI.php:245) | `Companies::get`, `Attachments::getAll`, `JobOrders::getAll`, `Contacts::getAll`, `ActivityEntries::getAllByCompany`, `extraFields->getValuesForShow`, `Companies::getDepartments` | `Show.tpl` (CompaniesUI.php:507) |
| `internalPostings` | `if ($this->getUserAccessLevel('companies.internalPostings') < ACCESS_LEVEL_READ)` (CompaniesUI.php:85) | `ACCESS_LEVEL_READ` | `internalPostings()` (CompaniesUI.php:513) | `Companies::getDefaultCompany` | redirects to `m=companies&a=show` (no template) |
| `add` (GET) | `if ($this->getUserAccessLevel('companies.add') < ACCESS_LEVEL_EDIT)` (CompaniesUI.php:93) | `ACCESS_LEVEL_EDIT` | `add()` (CompaniesUI.php:526) | `extraFields->getValuesForAdd` | `Add.tpl` (CompaniesUI.php:538) |
| `add` (POST) | same guard (CompaniesUI.php:93) | `ACCESS_LEVEL_EDIT` | `onAdd()` (CompaniesUI.php:544) | `Companies::add`, `extraFields->setValuesOnEdit`, `ListEditor::getDifferencesFromList`, `Companies::updateDepartments` | redirects to `a=show` |
| `edit` (GET) | `if ($this->getUserAccessLevel('companies.edit') < ACCESS_LEVEL_EDIT)` (CompaniesUI.php:109) | `ACCESS_LEVEL_EDIT` | `edit()` (CompaniesUI.php:650) | `Companies::getForEditing`, `Companies::getContactsArray`, `Users::getSelectList`, `extraFields->getValuesForEdit`, `Companies::getDepartments`, `EmailTemplates::getByTag` | `Edit.tpl` (CompaniesUI.php:724) |
| `edit` (POST) | same guard (CompaniesUI.php:109) | `ACCESS_LEVEL_EDIT` | `onEdit()` (CompaniesUI.php:730) | `Companies::get`, `Users::get`, `EmailTemplates::getByTag`, `Companies::getDepartments`, `Companies::updateDepartments`, `Companies::update`, `extraFields->setValuesOnEdit`, `Contacts::updateByCompany` | redirects to `a=show` |
| `delete` (POST) | `if ($this->getUserAccessLevel('companies.delete') < ACCESS_LEVEL_DELETE)` (CompaniesUI.php:125) | `ACCESS_LEVEL_DELETE` | `onDelete()` (CompaniesUI.php:926) | `Companies::get`, `Companies::delete`, MRU `removeEntry` | redirects to `a=listByView` |
| `delete` (GET, non-postback) | same guard (CompaniesUI.php:125) | `ACCESS_LEVEL_DELETE` | — | `CommonErrors::fatal(COMMONERROR_BADFIELDS, ...)` (CompaniesUI.php:135) | Error |
| `search` (GET, not getback) | `if ($this->getUserAccessLevel('companies.search') < ACCESS_LEVEL_READ)` (CompaniesUI.php:140) | `ACCESS_LEVEL_READ` | `search()` (CompaniesUI.php:969) | `SavedSearches::get` | `Search.tpl` (CompaniesUI.php:984) |
| `search` (getback) | same guard (CompaniesUI.php:140) | `ACCESS_LEVEL_READ` | `onSearch()` (CompaniesUI.php:990) | `SearchCompanies::byName` / `::byKeyTechnologies`, `SavedSearches::add`/`get`, `ExportUtility::getForm` | `Search.tpl` (CompaniesUI.php:1124) |
| `createAttachment` (GET) | `if ($this->getUserAccessLevel('companies.createAttachment') < ACCESS_LEVEL_EDIT)` (CompaniesUI.php:159) | `ACCESS_LEVEL_EDIT` | `createAttachment()` (CompaniesUI.php:1131) | — | `CreateAttachmentModal.tpl` (CompaniesUI.php:1145) |
| `createAttachment` (POST) | same guard (CompaniesUI.php:159) | `ACCESS_LEVEL_EDIT` | `onCreateAttachment()` (CompaniesUI.php:1153) | `AttachmentCreator::createFromUpload` | `CreateAttachmentModal.tpl` (CompaniesUI.php:1179) |
| `deleteAttachment` (POST) | `if ($this->getUserAccessLevel('companies.deleteAttachment') < ACCESS_LEVEL_DELETE)` (CompaniesUI.php:178) | `ACCESS_LEVEL_DELETE` | `onDeleteAttachment()` (CompaniesUI.php:1187) | `Attachments::delete` | redirects to `a=show` |
| `deleteAttachment` (GET, non-postback) | same guard (CompaniesUI.php:178) | `ACCESS_LEVEL_DELETE` | — | `CommonErrors::fatal(COMMONERROR_BADFIELDS, ...)` (CompaniesUI.php:188) | Error |
| `listByView` / `default` | `if ($this->getUserAccessLevel('companies.list') < ACCESS_LEVEL_READ)` (CompaniesUI.php:195) | `ACCESS_LEVEL_READ` | `listByView()` (CompaniesUI.php:208) | `DataGrid::getRecentParamaters`, `DataGrid::get` | `Companies.tpl` (CompaniesUI.php:239) |

Note the guard string for the default/list case is `companies.list` (CompaniesUI.php:195), not `companies.listByView`.

The `add`/`edit`/`createAttachment` cases branch on `$this->isPostBack()` (CompaniesUI.php:97,113,165); `search` branches on `$this->isGetBack()` (CompaniesUI.php:146); `delete`/`deleteAttachment` require `isPostBack()` and otherwise raise `COMMONERROR_BADFIELDS` (CompaniesUI.php:129-136, 182-189).

## Per-action detail

### listByView (CompaniesUI.php:208-240)

- If `$_SESSION['CATS']->isHrMode()` is true it immediately forwards to `internalPostings()` and `die()`s (CompaniesUI.php:213-217), so HR-mode users never see the company pager.
- Pulls recent datagrid parameters via `DataGrid::getRecentParamaters("companies:CompaniesListByViewDataGrid")` (CompaniesUI.php:219); if empty it defaults to `rangeStart=0, maxResults=15, filterVisible=false` (CompaniesUI.php:225-227).
- Builds the grid with `DataGrid::get("companies:CompaniesListByViewDataGrid", $dataGridProperties)` (CompaniesUI.php:230).
- Assigns `active`, `dataGrid`, `userID` (from `$_SESSION['CATS']->getUserID()`), `errMessage` (CompaniesUI.php:232-235).
- Fires `CLIENTS_LIST_BY_VIEW` (CompaniesUI.php:237) then displays `Companies.tpl` (CompaniesUI.php:239).
- The method accepts an `$errMessage` argument and is reused throughout the module as an "error bail-out" target (e.g. invalid IDs) rather than only for direct dispatch.

### show (CompaniesUI.php:245-508)

- Validates `companyID` via `isRequiredIDValid('companyID', $_GET)`; on failure calls `listByView('Invalid company ID.')` (CompaniesUI.php:248-252).
- `$companies = new Companies($this->_siteID); $data = $companies->get($companyID);` (CompaniesUI.php:256-257). Empty result -> `listByView('The specified company ID could not be found.')` (CompaniesUI.php:260-264).
- Formats `cityAndState` via `StringUtility::makeCityStateString` (CompaniesUI.php:269-271).
- Notes are run through `nl2br(htmlspecialchars(... ENT_QUOTES))` and truncated to `NOTES_MAXLEN` to make `shortNotes` (CompaniesUI.php:277-293).
- Title style: `isHot == 1` -> `titleClass = 'jobTitleHot'` else `'jobTitleCold'` (CompaniesUI.php:296-303).
- Builds a Google Maps link only when address+city+state are present (CompaniesUI.php:306-326).
- Attachments: `new Attachments($this->_siteID); $attachments->getAll(DATA_ITEM_COMPANY, $companyID)` (CompaniesUI.php:329-332); per-row icon from `FileUtility::getAttachmentIcon` (CompaniesUI.php:337-343).
- Job orders: `new JobOrders($this->_siteID); $jobOrders->getAll(JOBORDERS_STATUS_ALL, -1, $companyID, -1)` (CompaniesUI.php:347-350); per-row date fix-up and recruiter/owner abbreviated names (CompaniesUI.php:357-385).
- Contacts: `new Contacts($this->_siteID); $contacts->getAll(-1, $companyID)` (CompaniesUI.php:390-391). Rows with `leftCompany == 0` are accumulated into `$contactsRSWC` (current contacts); rows that left get `linkClass = 'jobLinkDead'` (CompaniesUI.php:423-430).
- Activity: `new ActivityEntries($this->_siteID); $activityEntries->getAllByCompany($companyID)` (CompaniesUI.php:434-435); blank notes -> `'(No Notes)'`, blank regarding/jobOrderID -> `'General'` (CompaniesUI.php:440-449).
- Adds an MRU entry `addEntry(DATA_ITEM_COMPANY, $companyID, $data['name'])` (CompaniesUI.php:471-473).
- Extra fields via `$companies->extraFields->getValuesForShow($companyID)` (CompaniesUI.php:476); departments via `$companies->getDepartments($companyID)` (CompaniesUI.php:479).
- "Privileged"/history flag: `privledgedUser` is false when `companies.show < ACCESS_LEVEL_DEMO`, otherwise true (CompaniesUI.php:482-489). This drives the "View History" link in the template (Show.tpl:244).
- Fires `CLIENTS_SHOW` (CompaniesUI.php:505) then displays `Show.tpl` (CompaniesUI.php:507).

### internalPostings (CompaniesUI.php:513-521)

Resolves the site's default ("Internal Postings") company via `$companies->getDefaultCompany()` and redirects with `CATSUtility::transferRelativeURI('m=companies&a=show&companyID=' . $companyID)` (CompaniesUI.php:515-520).

### add (GET) (CompaniesUI.php:526-539)

Loads add-mode extra fields `extraFields->getValuesForAdd()` (CompaniesUI.php:531), fires `CLIENTS_ADD` (CompaniesUI.php:533), assigns `extraFieldRS`, `active`, `subActive='Add Company'`, displays `Add.tpl` (CompaniesUI.php:535-538).

### onAdd (POST) (CompaniesUI.php:544-645)

- Phone1/phone2/fax are normalized with `StringUtility::extractPhoneNumber`, falling back to raw trimmed input (CompaniesUI.php:546-580).
- URL normalized with `StringUtility::extractURL` if non-empty (CompaniesUI.php:582-591).
- `isHot` from `isChecked('isHot', $_POST)` (CompaniesUI.php:594).
- Reads name/address/address2/city/state/zip/keyTechnologies/notes and `departmentsCSV` (CompaniesUI.php:596-606).
- Required-field check: empty `name` -> `listByView('Required fields are missing.')` (CompaniesUI.php:609-613). Name is the only required field.
- Fires `CLIENTS_ON_ADD_PRE` (CompaniesUI.php:615).
- `$companyID = $companies->add($name, $address, $address2, $city, $state, $zip, $phone1, $phone2, $faxNumber, $url, $keyTechnologies, $isHot, $notes, $this->_userID, $this->_userID)` (CompaniesUI.php:618-622) — note `enteredBy` and `owner` are both set to the current user. `$companyID <= 0` -> `COMMONERROR_RECORDERROR` fatal (CompaniesUI.php:624-627).
- Fires `CLIENTS_ON_ADD_POST` (CompaniesUI.php:629).
- Saves extra fields `extraFields->setValuesOnEdit($companyID)` (CompaniesUI.php:632).
- Departments: computes diffs against an empty list with `ListEditor::getDifferencesFromList(array(), 'name', 'departmentID', $departmentsCSV)` and applies `$companies->updateDepartments` (CompaniesUI.php:635-640).
- Redirects to `a=show` (CompaniesUI.php:642-644).

### edit (GET) (CompaniesUI.php:650-725)

- Validates `companyID` (`$_GET`); failures bail to `listByView` (CompaniesUI.php:653-657).
- `$data = $companies->getForEditing($companyID)` (CompaniesUI.php:662); empty -> bail (CompaniesUI.php:665-669).
- `$contactsRS = $companies->getContactsArray($companyID)` for the billing-contact select (CompaniesUI.php:672).
- `$usersRS = (new Users(...))->getSelectList()` for the owner select (CompaniesUI.php:674-675).
- Adds MRU entry (CompaniesUI.php:678-680).
- Extra fields `getValuesForEdit`, departments `getDepartments` + `ListEditor::getStringFromList($departmentsRS, 'name')` (CompaniesUI.php:683-687).
- Loads ownership-change email template by tag `EMAIL_TEMPLATE_OWNERSHIPASSIGNCLIENT` (CompaniesUI.php:689-692); `emailTemplateDisabled` is true if missing or `disabled == 1` (CompaniesUI.php:694-701).
- `canEmail` is false only when `companies.email == ACCESS_LEVEL_DEMO`, else true (CompaniesUI.php:703-710). This is an inline ACL read inside `edit()`, distinct from the dispatch guard.
- Fires `CLIENTS_EDIT` (CompaniesUI.php:712) then displays `Edit.tpl` (CompaniesUI.php:724).

### onEdit (POST) (CompaniesUI.php:730-921)

- Validates `companyID` (required, `$_POST`), `owner` (optional), `billingContact` (optional) (CompaniesUI.php:735-753).
- Same phone/fax/url/isHot normalization as `onAdd` (CompaniesUI.php:755-803).
- Ownership-change email: only when `isChecked('ownershipChange', $_POST) && $owner > 0` (CompaniesUI.php:810). Loads the owner via `Users::get`, loads template by tag `EMAIL_TEMPLATE_OWNERSHIPASSIGNCLIENT`, and substitutes `%CLNTOWNER%`, `%CLNTNAME%`, `%CLNTCATSURL%` (CompaniesUI.php:814-855). Otherwise `$email`/`$emailAddress` are empty (CompaniesUI.php:862-866).
- Required-field check: empty `name` -> bail (CompaniesUI.php:881-885).
- Fires `CLIENTS_ON_EDIT_PRE` (CompaniesUI.php:887).
- Departments diffed against current `getDepartments` and applied via `updateDepartments` *before* the company row update (CompaniesUI.php:889-893).
- `$companies->update($companyID, $name, $address, $address2, $city, $state, $zip, $phone1, $phone2, $faxNumber, $url, $keyTechnologies, $isHot, $notes, $owner, $billingContact, $email, $emailAddress)`; false -> `COMMONERROR_RECORDERROR` fatal (CompaniesUI.php:895-900).
- Fires `CLIENTS_ON_EDIT_POST` (CompaniesUI.php:902).
- Extra fields saved (CompaniesUI.php:905).
- If `$_POST['updateContacts'] == 'yes'`, propagates address to all contacts via `Contacts::updateByCompany($companyID, $address, $address2, $city, $state, $zip)` (CompaniesUI.php:908-915).
- Redirects to `a=show` (CompaniesUI.php:918-920).

### onDelete (POST) (CompaniesUI.php:926-964)

- Validates `companyID` (`$_POST`); failure -> bail (CompaniesUI.php:929-933).
- `$rs = $companies->get($companyID)`; empty -> bail (CompaniesUI.php:937-944).
- Refuses to delete the default company: `if ($rs['defaultCompany'] == 1) { listByView('Cannot delete default company.'); return; }` (CompaniesUI.php:946-950).
- Fires `CLIENTS_ON_DELETE_PRE` (CompaniesUI.php:952).
- `$companies->delete($companyID)` (CompaniesUI.php:954) — this cascade-deletes contacts, job orders, attachments, saved-list entries, and extra fields (see lib section).
- Removes MRU entry (CompaniesUI.php:957-959).
- Fires `CLIENTS_ON_DELETE_POST` (CompaniesUI.php:961), redirects to `a=listByView` (CompaniesUI.php:963).

### search (GET) (CompaniesUI.php:969-985)

Loads saved searches `SavedSearches::get(DATA_ITEM_COMPANY)` (CompaniesUI.php:971-972), fires `CLIENTS_SEARCH` (CompaniesUI.php:974), assigns empty wildcard fields, `isResultsMode=false`, `mode=''`, displays `Search.tpl` (CompaniesUI.php:976-984).

### onSearch (getback) (CompaniesUI.php:990-1125)

- Requires `$_GET['wildCardString']`; missing -> bail (CompaniesUI.php:998-1002).
- Paging: `new SearchPager(CANDIDATES_PER_PAGE, $currentPage, $this->_siteID, $_GET)` (CompaniesUI.php:1016-1018). Default `sortBy='name'`, `sortDirection='ASC'` (CompaniesUI.php:1020-1036).
- Fires `CLIENTS_ON_SEARCH_PRE` (CompaniesUI.php:1043).
- Mode comes from `getSanitisedInput('mode', $_GET)` (CompaniesUI.php:1046). `$search = new SearchCompanies($this->_siteID)` (CompaniesUI.php:1049):
  - `searchByName` -> `$search->byName($query, $sortBy, $sortDirection)` (CompaniesUI.php:1052-1055).
  - `searchByKeyTechnologies` -> `$search->byKeyTechnologies($query, $sortBy, $sortDirection)` (CompaniesUI.php:1057-1060).
  - default -> `listByView('Invalid search mode.')` (CompaniesUI.php:1062-1065).
- Per-row hot styling + `ownerAbbrName` (CompaniesUI.php:1068-1092).
- Export form: `ExportUtility::getForm(DATA_ITEM_COMPANY, $companyIDs, 40, 15)` over the result `companyID`s (CompaniesUI.php:1094-1097).
- Saves the search: `SavedSearches::add(DATA_ITEM_COMPANY, $query, $_SERVER['REQUEST_URI'], false)` (CompaniesUI.php:1100-1106).
- Fires `CLIENTS_ON_SEARCH_POST` (CompaniesUI.php:1111), assigns results, `isResultsMode=true`, displays `Search.tpl` (CompaniesUI.php:1113-1124).

### createAttachment (CompaniesUI.php:1131-1148) / onCreateAttachment (CompaniesUI.php:1153-1182)

- `createAttachment()` validates `companyID` (`$_GET`) with `fatalModal(COMMONERROR_BADINDEX, ...)` on failure (CompaniesUI.php:1134-1137), fires `CLIENTS_CREATE_ATTACHMENT` (CompaniesUI.php:1141), shows `CreateAttachmentModal.tpl` with `isFinishedMode=false` (CompaniesUI.php:1143-1147).
- `onCreateAttachment()` validates `companyID` (`$_POST`) (CompaniesUI.php:1156-1159), fires `CLIENTS_ON_CREATE_ATTACHMENT_PRE` (CompaniesUI.php:1163), then `new AttachmentCreator($this->_siteID); ->createFromUpload(DATA_ITEM_COMPANY, $companyID, 'file', false, false)` (CompaniesUI.php:1165-1168). On error -> `fatalModal(COMMONERROR_FILEERROR, ...)` (CompaniesUI.php:1170-1173). Fires `CLIENTS_ON_CREATE_ATTACHMENT_POST` (CompaniesUI.php:1175), redisplays the modal with `isFinishedMode=true` (CompaniesUI.php:1177-1181). The `DocumentToText.php` lib is included by the dispatcher for this action (CompaniesUI.php:163).

### onDeleteAttachment (POST) (CompaniesUI.php:1187-1214)

Validates `attachmentID` and `companyID` (both `$_POST`, `fatalModal` on failure) (CompaniesUI.php:1190-1199), fires `CLIENTS_ON_DELETE_ATTACHMENT_PRE` (CompaniesUI.php:1204), `Attachments::delete($attachmentID)` (CompaniesUI.php:1206-1207), fires `CLIENTS_ON_DELETE_ATTACHMENT_POST` (CompaniesUI.php:1209), redirects to `a=show` (CompaniesUI.php:1211-1213).

### _formatListByViewResults (CompaniesUI.php:1224-1278)

Private helper that applies hot styling (`jobLinkHot`/`jobLinkCold`), `ownerAbbrName`, an attachment `iconTag` (paperclip), and blanks zero `jobOrdersCount` (CompaniesUI.php:1236-1274). Note: it is defined but is not referenced anywhere else in `CompaniesUI.php` — the list grid does its own rendering via the datagrid `pagerRender` closures (see dataGrids.php / lib).

## Templates

All templates live under `modules/companies/`.

- **Companies.tpl** (the list view). Header JS: `js/highlightrows.js`, `js/export.js`, `js/dataGrid.js`, `js/dataGridFilters.js` (Companies.tpl:2). Renders the `CompaniesListByViewDataGrid` via `$this->dataGrid->draw()` (Companies.tpl:73) with navigation/filter/action areas. Two header checkboxes toggle datagrid filters: "Only My Companies" -> filter `OwnerID == userID` (Companies.tpl:29) and "Only Hot Companies" -> filter `IsHot == '1'` (Companies.tpl:33). Shows `errMessage` block when set (Companies.tpl:43-57).

- **Show.tpl** (details). Header JS: `js/activity.js`, `js/sorttable.js`, `js/attachment.js` (Show.tpl:5). Uses `OpenCATS\UI\QuickActionMenu` (Show.tpl:3) with `$_SESSION['CATS']->getAccessLevel('companies.edit')` (Show.tpl:31). Template-level ACL gates:
  - Delete-attachment form rendered only if `companies.deleteAttachment >= ACCESS_LEVEL_DELETE` (Show.tpl:182).
  - "Add Attachment" link only if `companies.createAttachment >= ACCESS_LEVEL_EDIT` (Show.tpl:194).
  - "Edit" link only if `companies.edit >= ACCESS_LEVEL_EDIT` (Show.tpl:228).
  - "Delete" form only if `companies.delete >= ACCESS_LEVEL_DELETE` **and** `defaultCompany != 1` (Show.tpl:234).
  - "View History" link only if `$this->privledgedUser` (Show.tpl:244), linking to `m=settings&a=viewItemHistory&dataItemType=200` (200 = `DATA_ITEM_COMPANY`).
  - Job-order/contact action links use cross-module guards: `joborders.edit`/`joborders.add` (Show.tpl:290,300), `contacts.edit`/`contacts.add` (Show.tpl:350,388,400), and activity edit/delete use `contacts.editActivity`/`contacts.deleteActivity` (Show.tpl:450,455).
  - Output is escaped with the `Template::escapeHtml/escapeAttr/escapeUrl/escapeJsAttr` helpers and `$this->_()`.

- **Add.tpl**. Header JS: `modules/companies/validator.js`, `js/sweetTitles.js`, `js/listEditor.js`, `js/addressParser.js` (Add.tpl:2). Form `addCompanyForm` posts to `m=companies&a=add` with `onsubmit="return checkAddForm(...)"` (Add.tpl:18). Has a freeform "address block" textarea parsed by `AddressParser_parse(...)` (Add.tpl:35,58), a Zip "Lookup" calling `CityState_populate('zip', 'ajaxIndicator')` (Add.tpl:114), a Departments list-editor select wired to `listEditor('Departments', 'departmentsSelect', 'departmentsCSV')` writing hidden `departmentsCSV` (Add.tpl:133-138), an `isHot` checkbox (Add.tpl:147), and renders extra fields from `extraFieldRS` `addHTML` (Add.tpl:156-167). Includes two hooks evaluated in-template: `CANDIDATE_TEMPLATE_ABOVE_FREEFORM` / `CANDIDATE_TEMPLATE_BELOW_FREEFORM` (Add.tpl:32,38).

- **Edit.tpl**. Header JS: `modules/companies/validator.js`, `js/sweetTitles.js`, `js/listEditor.js` (Edit.tpl:2). Form `editCompanyForm` posts to `m=companies&a=edit` with `onsubmit="return checkEditForm(...)"` (Edit.tpl:18). For the default company the name is rendered read-only as a hidden field (Edit.tpl:33-38). Billing-contact select from `contactsRS` (Edit.tpl:50-56), departments select with list editor (Edit.tpl:66-74), owner select from `usersRS` (Edit.tpl:221-227), and a hidden `changeAddress` row (revealed on any address/phone keystroke) offering `updateContacts` yes/no synchronization (Edit.tpl:182-190). The "E-Mail new owner of change" checkbox (`ownershipChange`) is disabled when `!canEmail` (Edit.tpl:229-231) and the owner select's `onchange` reveals it only when `!emailTemplateDisabled` (Edit.tpl:218).

- **Search.tpl**. Header JS includes `modules/companies/validator.js`, `js/searchSaved.js`, `js/searchAdvanced.js`, etc. (Search.tpl:2). GET form with hidden `getback=getback` (Search.tpl:26), a `mode` select offering `searchByName` / `searchByKeyTechnologies` (Search.tpl:31-34), and renders results table with sort links and an export form when `isResultsMode` (Search.tpl:47-98).

- **CreateAttachmentModal.tpl**. Modal header includes `modules/companies/validator.js` (CreateAttachmentModal.tpl:2). Multipart form posting to `m=companies&a=createAttachment` with `onsubmit="return checkAttachmentForm(...)"` (CreateAttachmentModal.tpl:5); on finish shows a success message and a Close button calling `parentHidePopWinRefresh()` (CreateAttachmentModal.tpl:18-23).

- **Error.tpl / ErrorModal.tpl** are present in the directory but are not referenced by name from `CompaniesUI.php`; error rendering goes through `CommonErrors::fatal`/`fatalModal`.

## JavaScript

The only module-local script is **modules/companies/validator.js** (referenced by Add.tpl, Edit.tpl, Search.tpl, CreateAttachmentModal.tpl). It contains:

- `checkAddForm(form)` / `checkEditForm(form)` — both call `checkName()` (validator.js:11-39).
- `checkAttachmentForm(form)` — calls `checkFilename()` (validator.js:41-54).
- `checkSearchByNameForm(form)` -> `checkSearchName()`; `checkSearchByKeyTechnologiesForm(form)` -> `checkSearchKeyTechnologies()` (validator.js:56-84).
- Field validators: `checkName()` requires the `name` field, reddening `nameLabel` when empty (validator.js:86-104); `checkSearchName()` / `checkSearchKeyTechnologies()` validate the search inputs by id (validator.js:106-144); `checkFilename()` requires the `file` input (validator.js:146-164).

All validators show a JavaScript `alert("Form Error:\n" + ...)` and return false on failure. There are no AJAX or other `.js` files in this module directory; richer behavior (datagrid, address parser, list editor, city/state lookup, activity, attachments) comes from shared `js/*` scripts loaded by the templates.

## Datagrids (modules/companies/dataGrids.php)

- `class CompaniesListByViewDataGrid extends CompaniesDataGrid` (dataGrids.php:38) — the main list grid (`"companies:CompaniesListByViewDataGrid"`, dataGrids.php:66). Default sort `dateCreatedSort DESC` (dataGrids.php:51-52); `showExportCheckboxes`, `showActionArea`, `showChooseColumnsBox`, `allowResizing` all true (dataGrids.php:46-49). Default columns: Attachments, Name, Jobs, City, State, Phone, Owner, Created, Modified (dataGrids.php:54-64). Its action area adds "Add To List" (popup to `m=lists&a=addToListFromDatagridModal&dataItemType=DATA_ITEM_COMPANY`) and "Export" (`m=export&a=exportByDataGrid`) (dataGrids.php:86-87).
- `class companiesSavedListByViewDataGrid extends CompaniesDataGrid` (dataGrids.php:95) — same columns/config but its action area offers "Remove From This List" (`m=lists&a=removeFromListDatagrid`) instead of "Add To List" (dataGrids.php:143-144).
- The shared base `class CompaniesDataGrid extends DataGrid` is defined in `lib/Companies.php:750`, not in this file; it defines all column SQL/`pagerRender` closures and `getSQL()` (lib/Companies.php:756-997). `getSQL()` joins `saved_list_entry` (INNER when a saved-list id is passed as the misc argument, else LEFT) and exposes `company.company_id AS exportID` so export checkboxes render (lib/Companies.php:935-997, comment at dataGrids.php:46).

## lib/ dependencies (cited)

`lib/Companies.php` — `class Companies` (lib/Companies.php:51). Constructor builds `$this->extraFields = new ExtraFields($siteID, DATA_ITEM_COMPANY)` (lib/Companies.php:63). Methods called by the module:

- `public function add($name, $address, $address2, $city, $state, $zip, $phone1, $phone2, $faxNumber, $url, $keyTechnologies, $isHot, $notes, $enteredBy, $owner)` (lib/Companies.php:86-88) — builds an `OpenCATS\Entity\Company` via `Company::create(...)` and persists with `CompanyRepository::persist(...)`, returning `-1` on `CompanyRepositoryException` (lib/Companies.php:90-114).
- `public function update($companyID, $name, $address, $address2, $city, $state, $zip, $phone1, $phone2, $faxNumber, $url, $keyTechnologies, $isHot, $notes, $owner, $billingContact, $email, $emailAddress)` (lib/Companies.php:137-140) — raw `UPDATE company`, stores history diff, and if `$emailAddress` is non-empty sends a "CATS Notification: Company Ownership Change" email via `Mailer::sendToOne` (lib/Companies.php:197-208).
- `public function delete($companyID)` (lib/Companies.php:219) — deletes the company row, records history-deleted, then cascades: deletes associated contacts (via `Contacts::delete`), job orders (via `JobOrders::delete`), attachments (via `Attachments::delete`), `saved_list_entry` rows, and extra-field values (lib/Companies.php:219-311).
- `public function get($companyID)` (lib/Companies.php:319) — full record with owner/entered-by/billing-contact joins (lib/Companies.php:319-370).
- `public function getForEditing($companyID)` (lib/Companies.php:379) — slimmer record for the edit form (lib/Companies.php:379-413).
- `public function getDefaultCompany()` (lib/Companies.php:459) — returns the `default_company = 1` company id for the site or false (lib/Companies.php:459-480).
- `public function getContactsArray($companyID)` (lib/Companies.php:541) — contacts for the billing-contact dropdown (lib/Companies.php:541-562).
- `public function getDepartments($companyID)` (lib/Companies.php:602) — `company_department` rows (lib/Companies.php:602-621).
- `public function updateDepartments($companyID, $updates)` (lib/Companies.php:631) — applies `LIST_EDITOR_ADD/REMOVE/MODIFY` diffs to `company_department` with history entries (lib/Companies.php:631-716).
- (Also present but not called by the module dispatch: `setCompanyDefault` :423, `getSelectList` :488, `getLocationArray` :513, `getJobOrdersArray` :571, `companyByName` :724.)

`lib/Contacts.php` — `class Contacts`:
- `public function getAll($userID = -1, $companyID = -1)` (lib/Contacts.php:561) — used by `show()` with `(-1, $companyID)`.
- `public function updateByCompany($companyID, $address, $address2, $city, $state, $zip)` (lib/Contacts.php:315-316) — used by `onEdit()` to sync contact addresses.
- `public function delete($contactID)` (lib/Contacts.php:358) — invoked by `Companies::delete` cascade.

Other libs invoked by handlers:
- `JobOrders::getAll($status, $userID = -1, $companyID = -1, $contactID = -1, $onlyHot = false, $onlyPublic = false, $allowAdministrativeHidden = false)` (lib/JobOrders.php:563-564) — called as `getAll(JOBORDERS_STATUS_ALL, -1, $companyID, -1)`.
- `Attachments::getAll($dataItemType, $dataItemID)` (lib/Attachments.php:513).
- `ActivityEntries::getAllByCompany($companyID)` (lib/ActivityEntries.php:527).
- `SearchCompanies::byName($wildCardString, $sortBy, $sortDirection)` (lib/Search.php:806) and `SearchCompanies::byKeyTechnologies($wildCardString)` (lib/Search.php:854); `class SearchCompanies` at lib/Search.php:784.

The dispatcher also `include_once`s a broad set of libs at the top of the file (StringUtility, DateUtility, ResultSetUtility, Companies, Contacts, JobOrders, ActivityEntries, Attachments, Export, ListEditor, FileUtility, ExtraFields, CommonErrors) (CompaniesUI.php:30-42), plus `Search.php` for the search action (CompaniesUI.php:144) and `DocumentToText.php` for attachment creation (CompaniesUI.php:163).

## Hooks fired (keys + cites)

All hooks are evaluated with the pattern `if (!eval(Hooks::get('KEY'))) return;`. Keys use the legacy `CLIENTS_*` prefix:

| Hook key | Location |
|---|---|
| `CLIENTS_HANDLE_REQUEST` | CompaniesUI.php:72 |
| `CLIENTS_LIST_BY_VIEW` | CompaniesUI.php:237 |
| `CLIENTS_SHOW` | CompaniesUI.php:505 |
| `CLIENTS_ADD` | CompaniesUI.php:533 |
| `CLIENTS_ON_ADD_PRE` | CompaniesUI.php:615 |
| `CLIENTS_ON_ADD_POST` | CompaniesUI.php:629 |
| `CLIENTS_EDIT` | CompaniesUI.php:712 |
| `CLIENTS_ON_EDIT_PRE` | CompaniesUI.php:887 |
| `CLIENTS_ON_EDIT_POST` | CompaniesUI.php:902 |
| `CLIENTS_ON_DELETE_PRE` | CompaniesUI.php:952 |
| `CLIENTS_ON_DELETE_POST` | CompaniesUI.php:961 |
| `CLIENTS_SEARCH` | CompaniesUI.php:974 |
| `CLIENTS_ON_SEARCH_PRE` | CompaniesUI.php:1043 |
| `CLIENTS_ON_SEARCH_POST` | CompaniesUI.php:1111 |
| `CLIENTS_CREATE_ATTACHMENT` | CompaniesUI.php:1141 |
| `CLIENTS_ON_CREATE_ATTACHMENT_PRE` | CompaniesUI.php:1163 |
| `CLIENTS_ON_CREATE_ATTACHMENT_POST` | CompaniesUI.php:1175 |
| `CLIENTS_ON_DELETE_ATTACHMENT_PRE` | CompaniesUI.php:1204 |
| `CLIENTS_ON_DELETE_ATTACHMENT_POST` | CompaniesUI.php:1209 |

Additional hooks evaluated inside `Add.tpl` (not in the controller): `CANDIDATE_TEMPLATE_ABOVE_FREEFORM` (Add.tpl:32) and `CANDIDATE_TEMPLATE_BELOW_FREEFORM` (Add.tpl:38).

## Source evidence

Files read in full: `modules/companies/CompaniesUI.php` (1282 lines), `modules/companies/Companies.tpl`, `modules/companies/Show.tpl`, `modules/companies/Add.tpl`, `modules/companies/Edit.tpl`, `modules/companies/Search.tpl`, `modules/companies/CreateAttachmentModal.tpl`, `modules/companies/dataGrids.php`, `modules/companies/validator.js`, `lib/Companies.php` (1001 lines). Targeted reads: `lib/Contacts.php` (signatures at :315, :358, :561), `lib/JobOrders.php` (:563), `lib/Attachments.php` (:513), `lib/ActivityEntries.php` (:527), `lib/Search.php` (:784, :806, :854).

Access-level constant ordering (`ACCESS_LEVEL_DEMO`, `_READ`, `_EDIT`, `_DELETE`) is referenced but the numeric values were not opened; see open questions.

## Unverified / open questions

- **`byKeyTechnologies` arity mismatch.** `onSearch()` calls `$search->byKeyTechnologies($query, $sortBy, $sortDirection)` with three arguments (CompaniesUI.php:1059), but the method is declared `public function byKeyTechnologies($wildCardString)` with a single parameter (lib/Search.php:854). The extra arguments are silently ignored by PHP; I did not read the method body, so whether sorting is applied internally is unverified.
- **`_formatListByViewResults` appears unused.** No caller for this private method exists within `CompaniesUI.php`; list rendering is handled by the datagrid `pagerRender` closures. Not searched outside the module (it is private, so it cannot be).
- **Numeric ACL ordering not opened.** The relative order of `ACCESS_LEVEL_DEMO`, `_READ`, `_EDIT`, `_DELETE` (which determines whether the `companies.email == ACCESS_LEVEL_DEMO` and `companies.show < ACCESS_LEVEL_DEMO` checks behave as "demo is the lowest tier") was not confirmed from the constants definition file.
- **`getUserAccessLevel` resolution.** `getUserAccessLevel('companies.<action>')` is inherited from `UserInterface`; how it maps a string key to a per-user level (and the default when an action key is unknown, e.g. `companies.list`, `companies.internalPostings`, `companies.email`) was not read in this pass.
- **HR-mode `die()` in `listByView`.** When `isHrMode()` is true, `listByView()` calls `internalPostings()` then `die()` (CompaniesUI.php:213-216); `internalPostings()` itself issues a redirect via `transferRelativeURI`, so the `die()` is effectively defensive. Behavior when no default company exists (`getDefaultCompany()` returns false) was not traced.
- **`Error.tpl` / `ErrorModal.tpl`** exist in the module directory but are not referenced by `CompaniesUI.php`; their actual invokers (likely `CommonErrors`) were not traced.
