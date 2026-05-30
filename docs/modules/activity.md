# Module: activity

Source-derived design doc for the OpenCATS `activity` module (legacy PHP 7.4 ATS). Every claim below is cited to a file and line that was opened during research.

## Overview

The module controller is declared as:

```php
class ActivityUI extends UserInterface
```

(`modules/activity/ActivityUI.php:36`). It extends the shared `UserInterface` base class (`lib/UserInterface.php`).

The constructor sets four instance properties and calls the parent constructor first (`modules/activity/ActivityUI.php:47-55`):

```php
public function __construct()
{
    parent::__construct();

    $this->_authenticationRequired = true;   // modules/activity/ActivityUI.php:51
    $this->_moduleDirectory = 'activity';     // :52
    $this->_moduleName = 'activity';          // :53
    $this->_moduleTabText = 'Activities';     // :54
}
```

So the module requires an authenticated session (`_authenticationRequired = true`, `modules/activity/ActivityUI.php:51`) and appears as the "Activities" tab (`_moduleTabText`, `modules/activity/ActivityUI.php:54`). The base class default for that flag is also `true` (`lib/UserInterface.php:50`).

Two display-tuning class constants are defined but are not referenced anywhere else in the file (see Unverified): `TRUNCATE_REGARDING = 24` (`modules/activity/ActivityUI.php:41`) and `ACTIVITY_NOTE_MAXLEN = 140` (`modules/activity/ActivityUI.php:44`).

### What an "activity" is and the DATA_ITEM_* it attaches to

The empty-state template literally describes activities as records produced as a side-effect of other actions: "Activities are automatically recorded based on actions you perform." (`modules/activity/ActivityDataGrid.tpl:53`). The activity module itself is read-only/reporting; it never creates activities. Creation/edit/delete of activity rows happens through `lib/ActivityEntries.php` (called from other modules and the two AJAX handlers below).

Each `activity` row carries a `data_item_type` and `data_item_id` linking it to a parent record. The DATA_ITEM_* constants are defined in `constants.php`:

- `DATA_ITEM_CANDIDATE = 100` (`constants.php:57`)
- `DATA_ITEM_COMPANY   = 200` (`constants.php:58`)
- `DATA_ITEM_CONTACT   = 300` (`constants.php:59`)
- `DATA_ITEM_JOBORDER  = 400` (`constants.php:60`)

The datagrid SQL only surfaces activities attached to **candidates** and **contacts**: the query is a `UNION` of one branch filtered `activity.data_item_type = DATA_ITEM_CANDIDATE` joined to `candidate` (`modules/activity/dataGrids.php:204-207, 265`) and one branch filtered `activity.data_item_type = DATA_ITEM_CONTACT` joined to `contact` (`modules/activity/dataGrids.php:253-256, 270`). Each branch also joins `joborder` and `company` for the "Regarding" column (`modules/activity/dataGrids.php:200-203, 249-252`). When a row's `data_item_type` is `DATA_ITEM_CANDIDATE` the grid renders a candidate link, otherwise a contact link (`modules/activity/dataGrids.php:101, 109`).

In the writer layer, `ActivityEntries::_updateDataItemModified()` handles `DATA_ITEM_CANDIDATE`, `DATA_ITEM_COMPANY`, `DATA_ITEM_CONTACT`, and `DATA_ITEM_JOBORDER` cases (`lib/ActivityEntries.php:618, 622, 626, 630`).

## Action catalog

Dispatch happens in `handleRequest()` via `switch ($action)` (`modules/activity/ActivityUI.php:57-82`). There are **no `getUserAccessLevel(...)` guards anywhere in `ActivityUI.php`** — a grep for `getUserAccessLevel`, `getAccessLevel`, and `ACCESS_LEVEL` in the controller returns nothing. The only access gate at the module level is the authentication requirement set in the constructor (`modules/activity/ActivityUI.php:51`).

| Action | ACL guard in handler | Required level | Handler method | lib calls | Template |
|--------|---------------------|----------------|----------------|-----------|----------|
| `viewByDate` (with `getback`) | none (`modules/activity/ActivityUI.php:65-69`) | (none — auth only) | `onSearch()` (`:140`) | `new ActivityEntries($this->_siteID)` (`:263`), `->getCount()` (`:264`) | `./modules/activity/ActivityDataGrid.tpl` (`:266`) |
| `viewByDate` (no `getback`) | none (`modules/activity/ActivityUI.php:65, 71-73`) | (none — auth only) | `search()` (`:127`) | none | `./modules/activity/Search.tpl` (`:134`) |
| `listByViewDataGrid` | none (`modules/activity/ActivityUI.php:77`) | (none — auth only) | `listByViewDataGrid()` (`:87`) | `new ActivityEntries($this->_siteID)` (`:118`), `->getCount()` (`:119`) | `./modules/activity/ActivityDataGrid.tpl` (`:121`) |
| `default` (any other/empty action) | none (`modules/activity/ActivityUI.php:78-80`) | (none — auth only) | `listByViewDataGrid()` (`:87`) | same as above | `./modules/activity/ActivityDataGrid.tpl` (`:121`) |

Note: `viewByDate` is one switch case (`modules/activity/ActivityUI.php:65`) that branches internally on `$this->isGetBack()` (`modules/activity/ActivityUI.php:66`) to either `onSearch()` or `search()`; both rows above describe that single case. `listByViewDataGrid` and `default` share the same case body (`modules/activity/ActivityUI.php:77-80`).

## Per-action detail (traced with cites)

### `handleRequest()` dispatch
`$action = $this->getAction();` (`modules/activity/ActivityUI.php:59`; `getAction()` is defined in `lib/UserInterface.php:193`). Before the switch, the request-level hook fires and can short-circuit: `if (!eval(Hooks::get('ACTIVITY_HANDLE_REQUEST'))) return;` (`modules/activity/ActivityUI.php:61`).

### `listByViewDataGrid()` (default landing page)
Defined `private function listByViewDataGrid()` (`modules/activity/ActivityUI.php:87`).
- Reads recent datagrid params: `DataGrid::getRecentParamaters("activity:ActivityDataGrid")` (`modules/activity/ActivityUI.php:89`).
- If empty, seeds defaults `rangeStart=0`, `maxResults=15`, `filterVisible=false` (`modules/activity/ActivityUI.php:93-100`).
- Forces a one-month window: `startDate=''`, `endDate=''`, `period='DATE_SUB(CURDATE(), INTERVAL 1 MONTH)'` (`modules/activity/ActivityUI.php:103-105`).
- Builds the grid: `DataGrid::get("activity:ActivityDataGrid", $dataGridProperties)` (`modules/activity/ActivityUI.php:107`).
- Builds quick links via `$this->getQuickLinks()` (`modules/activity/ActivityUI.php:109`).
- Fires hook `ACTIVITY_LIST_BY_VIEW_DG` and may return early (`modules/activity/ActivityUI.php:111`).
- Assigns `quickLinks`, `active` (`$this`), `dataGrid`, `userID` (from `$_SESSION['CATS']->getUserID()`) (`modules/activity/ActivityUI.php:113-116`).
- Instantiates `new ActivityEntries($this->_siteID)` and assigns `numActivities` from `->getCount()` (`modules/activity/ActivityUI.php:118-119`).
- Renders `./modules/activity/ActivityDataGrid.tpl` (`modules/activity/ActivityUI.php:121`).

### `search()` (date-range search form)
Defined `private function search()` (`modules/activity/ActivityUI.php:127`). Fires hook `ACTIVITY_SEARCH` (may return early) (`modules/activity/ActivityUI.php:129`). Assigns `isResultsMode=false`, `wildCardString=''`, `active=$this` (`modules/activity/ActivityUI.php:131-133`) and renders `./modules/activity/Search.tpl` (`modules/activity/ActivityUI.php:134`).

### `onSearch()` (date-range search results)
Defined `private function onSearch()` (`modules/activity/ActivityUI.php:140`).
- Reads `period` via `$this->getTrimmedInput('period', $_GET)` (`modules/activity/ActivityUI.php:142`; helper in `lib/UserInterface.php:374`).
- If `period` is one of `lastweek|lastmonth|lastsixmonths|lastyear|all` (`modules/activity/ActivityUI.php:143-144`), maps it to a SQL `DATE_SUB(...)` expression (or `''` for `all`) in the inner switch (`modules/activity/ActivityUI.php:147-169`), and clears start/end date strings (`modules/activity/ActivityUI.php:171-175`).
- Otherwise validates start/end day/month/year using `$this->isRequiredIDValid(...)` (`modules/activity/ActivityUI.php:180-193`; helper in `lib/UserInterface.php:318`) and PHP `checkdate(...)` (`modules/activity/ActivityUI.php:195-203`), calling `CommonErrors::fatal(COMMONERROR_BADFIELDS, ...)` on failure (`modules/activity/ActivityUI.php:184, 192, 197, 202`).
- Formats dates with `DateUtility::formatSearchDate(...)` (`modules/activity/ActivityUI.php:206-211`); note the end date uses `$_GET['endDay']+1` (`modules/activity/ActivityUI.php:210`).
- Builds a `baseURL` for `m=activity&a=viewByDate&getback=getback...` (`modules/activity/ActivityUI.php:230-233`).
- Loads/seeds recent datagrid params identically to the landing page (`modules/activity/ActivityUI.php:235-246`), then injects `startDate`/`endDate`/`period` (`modules/activity/ActivityUI.php:248-250`) and builds the grid (`modules/activity/ActivityUI.php:252`).
- Fires hook `ACTIVITY_LIST_BY_VIEW_DG` (note: the **same** hook key as the landing page, not a distinct one) (`modules/activity/ActivityUI.php:256`).
- Assigns the same template vars (`quickLinks`, `active`, `dataGrid`, `userID`, `numActivities` via `new ActivityEntries(...)->getCount()`) (`modules/activity/ActivityUI.php:258-264`) and renders `./modules/activity/ActivityDataGrid.tpl` (`modules/activity/ActivityUI.php:266`).

### `getQuickLinks()` (private helper)
Defined `private function getQuickLinks()` (`modules/activity/ActivityUI.php:275`). Builds Today/Yesterday/Last Week/Last Month/Last 6 Months/All links pointing at `m=activity&a=viewByDate&getback=getback` (`modules/activity/ActivityUI.php:290-335`), using `DateUtility::subtractDaysFromDate(time(), 1)` for "yesterday" (`modules/activity/ActivityUI.php:283`) and `CATSUtility::getIndexName()` for the base URL (`modules/activity/ActivityUI.php:292`). Returns the links joined with ` | ` (`modules/activity/ActivityUI.php:337`).

## Templates

### `ActivityDataGrid.tpl`
Header pulls JS `js/highlightrows.js`, `js/sweetTitles.js`, `js/dataGrid.js`, `js/dataGridFilters.js` (`modules/activity/ActivityDataGrid.tpl:2`). When `numActivities` is truthy it renders the "Activities" datagrid: navigation (`printNavigation`), rows-per-page selector, filter control, filter area, the grid `draw()`, and an action area + bottom navigation (`modules/activity/ActivityDataGrid.tpl:8-39`). When there are zero activities it shows the empty-state graphic and the "automatically recorded" message (`modules/activity/ActivityDataGrid.tpl:41-60`). `quickLinks` is echoed next to the navigation (`modules/activity/ActivityDataGrid.tpl:16`).

### `Search.tpl`
Header pulls `js/highlightrows.js`, `modules/activity/validator.js`, `js/sweetTitles.js` (`modules/activity/Search.tpl:2`). Echoes `quickLinks` (`modules/activity/Search.tpl:15`). If `$this->rs` is non-empty it renders a sortable results table (`id="activityTable"`) with columns Date / icon / First Name / Last Name / Regarding / Activity / Notes / Entered By using `$this->pager->printSortLink(...)` headers (`modules/activity/Search.tpl:20-88`). It escapes output via `Template::escapeHtml/escapeUrl/escapeAttr` and `$this->_()` (`modules/activity/Search.tpl:52, 56, 60-67`), and notes are run through `TemplateUtility::highlightStatusChangeActivityNote(...)` then `nl2br` (`modules/activity/Search.tpl:80`). If empty but `isResultsMode` is set, it prints "No activities found on ..." (`modules/activity/Search.tpl:90-91`).

Caveat: `Search.tpl` references `$this->rs`, `$this->pager`, `$this->startDate`, and the per-row `activityURL`/`itemInfo`/`icon`/`enteredByAbbrName` keys (`modules/activity/Search.tpl:20, 21, 49-89`), but `onSearch()`/`search()` in `ActivityUI.php` never assign `rs`, `pager`, or `startDate` — they assign a `dataGrid` and render `ActivityDataGrid.tpl` instead (`modules/activity/ActivityUI.php:131-134, 258-266`). See Unverified.

## JavaScript

### `modules/activity/validator.js`
Defines `function checkDate(form)` (`modules/activity/validator.js:29`). It reads `startMonth/startDay/startYear` and `endMonth/endDay/endYear` from DOM inputs, decrements the months for the JS `Date` API (`modules/activity/validator.js:33-43`), builds two `Date` objects (`modules/activity/validator.js:45-53`), and `alert()`s + returns false if the start date is after the end date, otherwise returns true (`modules/activity/validator.js:55-63`). The file header comment is mislabeled "Candidates Form Validation" (`modules/activity/validator.js:3`).

This is the only JS file in the module directory; `ActivityDataGrid.tpl` and `Search.tpl` additionally reference shared scripts under `js/` (see Templates above).

## lib/ dependencies (cited)

`ActivityUI.php` includes: `lib/ActivityEntries.php`, `lib/StringUtility.php`, `lib/Contacts.php`, `lib/Candidates.php`, `lib/DateUtility.php`, `lib/InfoString.php` (`modules/activity/ActivityUI.php:28-33`). `dataGrids.php` includes `lib/ActivityEntries.php`, `lib/Hooks.php`, `lib/InfoString.php`, `lib/Width.php` (`modules/activity/dataGrids.php:34-37`).

`lib/ActivityEntries.php` — class `ActivityEntries` (`lib/ActivityEntries.php:63`), constructor `public function __construct($siteID)` storing the site ID and DB singleton (`lib/ActivityEntries.php:69-73`). Methods actually called by this domain:

- `getCount()` — called by `ActivityUI` for `numActivities`. Signature `public function getCount()` (`lib/ActivityEntries.php:399`); returns `COUNT(*)` of `activity` rows for the site (`lib/ActivityEntries.php:399-412`). Called at `modules/activity/ActivityUI.php:119` and `:264`.
- `update($activityID, $activityType, $activityNotes, $jobOrderID = false, $date = false, $timezoneOffset)` (`lib/ActivityEntries.php:181-182`) — returns `true` on success (`lib/ActivityEntries.php:309`); called by the edit AJAX handler at `ajax/editActivity.php:118`.
- `get($activityID)` (`lib/ActivityEntries.php:420`) — returns a single activity row including `type`, `typeDescription`, `notes`, `regarding`, `dateCreated` (`lib/ActivityEntries.php:420-447+`); called by the edit AJAX handler at `ajax/editActivity.php:121`.
- `delete($activityID)` (`lib/ActivityEntries.php:318`) — re-reads the row, runs a site-scoped `DELETE`, writes a History entry, and updates the parent (and job order) modified timestamps; returns `true`/`false` (`lib/ActivityEntries.php:318-392`); called by the delete AJAX handler at `ajax/deleteActivity.php:59`.

Other public methods exist on the class but are not invoked by the activity module/AJAX pair: `add(...)` (`lib/ActivityEntries.php:88`), `getAllByDataItem(...)` (`lib/ActivityEntries.php:470`), `getAllByCompany(...)` (`lib/ActivityEntries.php:527`), `getTypes()` (`lib/ActivityEntries.php:590`). `add()` writes the row and a History "(NEW)" entry, and stamps parent/job-order modified timestamps (`lib/ActivityEntries.php:106-169`).

`modules/activity/dataGrids.php` — class `ActivityDataGrid extends DataGrid` (`modules/activity/dataGrids.php:39`), constructor `public function __construct($siteID, $parameters)` (`modules/activity/dataGrids.php:45`). It builds `dateCriterion` from `period`/`startDate`/`endDate` params (`modules/activity/dataGrids.php:57-72`), defines column render/sort/filter config (`modules/activity/dataGrids.php:77-145`), and `getSQL(...)` produces the candidate+contact `UNION` query (`modules/activity/dataGrids.php:155-280`). Default sort is `dateCreatedSort DESC` with an `activityID` tie-breaker injected (`modules/activity/dataGrids.php:74-75, 157-161`).

## Related AJAX handlers (this domain, brief)

Two AJAX endpoints mutate activity rows and exist outside the module directory:

- `ajax/editActivity.php` (exists; 142 lines). Uses `SecureAJAXInterface` (`ajax/editActivity.php:34`), requires `POST` (`ajax/editActivity.php:36-40`), and guards with `if ($_SESSION['CATS']->getAccessLevel('contacts.editActivity') < ACCESS_LEVEL_EDIT)` → returns `ERROR_NO_PERMISSION` (`ajax/editActivity.php:42-46`). It validates `activityID`/`type`/`jobOrderID`/`notes` (`ajax/editActivity.php:48-70`), then calls `ActivityEntries::update(...)` and `get(...)` and returns XML (`ajax/editActivity.php:118, 121, 130-140`).
- `ajax/deleteActivity.php` (exists; 65 lines). Uses `SecureAJAXInterface` (`ajax/deleteActivity.php:33`), requires `POST` (`ajax/deleteActivity.php:35-39`), and guards with `if ($_SESSION['CATS']->getAccessLevel('contacts.deleteActivity') < ACCESS_LEVEL_EDIT)` → `ERROR_NO_PERMISSION` (`ajax/deleteActivity.php:41-45`). It validates `activityID` (`ajax/deleteActivity.php:47-51`) and calls `ActivityEntries::delete(...)` (`ajax/deleteActivity.php:59`).

Note the secured-object names for both are namespaced under `contacts.*` (`contacts.editActivity`, `contacts.deleteActivity`), not `activity.*`.

## Hooks fired (keys + cites)

- `ACTIVITY_HANDLE_REQUEST` — `if (!eval(Hooks::get('ACTIVITY_HANDLE_REQUEST'))) return;` (`modules/activity/ActivityUI.php:61`).
- `ACTIVITY_LIST_BY_VIEW_DG` — fired in `listByViewDataGrid()` (`modules/activity/ActivityUI.php:111`) and again in `onSearch()` (`modules/activity/ActivityUI.php:256`).
- `ACTIVITY_SEARCH` — fired in `search()` (`modules/activity/ActivityUI.php:129`).

## Source evidence

- `modules/activity/ActivityUI.php` (read in full, 341 lines): controller, dispatch, all handler methods, quick links.
- `modules/activity/ActivityDataGrid.tpl` (read in full): main list / empty-state template.
- `modules/activity/Search.tpl` (read in full): date-range search results template.
- `modules/activity/validator.js` (read in full): client-side date-range validation.
- `modules/activity/dataGrids.php` (read in full): `ActivityDataGrid` and the candidate+contact UNION SQL.
- `lib/ActivityEntries.php` (read: class decl `:63`, constructor `:69`, `add` `:88`, `update` `:181`, `delete` `:318`, `getCount` `:399`, `get` `:420`, method index for `getAllByDataItem` `:470`, `getAllByCompany` `:527`, `getTypes` `:590`, `_updateDataItemModified` cases `:614-630`).
- `lib/UserInterface.php` (read: `_authenticationRequired` `:50`, `getAction` `:193`, `isGetBack` `:225`, `isRequiredIDValid` `:318`, `getTrimmedInput` `:374`, `getUserAccessLevel` `:429-432`).
- `ajax/editActivity.php`, `ajax/deleteActivity.php` (read in full).
- `constants.php:57-60` (DATA_ITEM_* values).

## Unverified / open questions

- **`Search.tpl` appears dead/mismatched.** The controller's `viewByDate` results path (`onSearch()`) assigns a `dataGrid` and renders `ActivityDataGrid.tpl`, never `Search.tpl`, and never assigns `rs`/`pager`/`startDate` that `Search.tpl` consumes (`modules/activity/ActivityUI.php:258-266` vs `modules/activity/Search.tpl:20-91`). `search()` does render `Search.tpl` (`modules/activity/ActivityUI.php:134`) but only assigns `isResultsMode`/`wildCardString`/`active`, so the results table branch can never populate. Whether `Search.tpl`'s results table is reachable at all was not confirmed.
- **Unused constants.** `TRUNCATE_REGARDING` (`modules/activity/ActivityUI.php:41`) and `ACTIVITY_NOTE_MAXLEN` (`modules/activity/ActivityUI.php:44`) are declared but not referenced elsewhere in the read files; their runtime use (if any) was not located.
- **`_dataItemIDColumn = 'company.company_id'`** is set on the datagrid (`modules/activity/dataGrids.php:91`) even though the grid lists candidate/contact activities; its effect was not traced into the `DataGrid` base class.
- **No module-level ACL.** `ActivityUI.php` contains no `getUserAccessLevel(...)`/`getAccessLevel(...)` guards; access control beyond authentication (e.g., whether `index.php` enforces a module-level access level for `m=activity`) was not traced in this pass.
- **Timezone param.** `update(...)` declares `$timezoneOffset` as a required trailing param after optional params (`lib/ActivityEntries.php:182`); the caller passes `$_SESSION['CATS']->getTimeZoneOffset()` (`ajax/editActivity.php:118`). How/whether `$timezoneOffset` is used inside `update()` beyond line 207 was not fully read.
