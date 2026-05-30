# Module: lists

## Overview

The lists module implements OpenCATS **saved lists** (a.k.a. hotlists): named, site-scoped collections of data items (candidates, companies, contacts, or job orders) that a user can group together and act on in bulk.

The controller is declared as:

```php
class ListsUI extends UserInterface
```
(modules/lists/ListsUI.php:44)

**Constructor** (modules/lists/ListsUI.php:47-60):

```php
public function __construct()
{
    parent::__construct();

    $this->_authenticationRequired = true;
    $this->_moduleDirectory = 'lists';
    $this->_moduleName = 'lists';
    $this->_moduleTabText = 'Lists';
    $this->_subTabs = array(
        'Show Lists'     => CATSUtility::getIndexName() . '?m=lists'
       /* 'New Static List' => ... */
       /* 'New Dynamic List' => ... */
    );
}
```

Key facts:

- The module requires the user to be logged in (`$this->_authenticationRequired = true`, modules/lists/ListsUI.php:51).
- The secured-object name is `'lists'` (`$this->_moduleName = 'lists'`, modules/lists/ListsUI.php:53). This is the string passed to `getAccessLevel('lists')` by the AJAX endpoints (see Action catalog).
- Only one live sub-tab exists, **Show Lists** (modules/lists/ListsUI.php:56). The "New Static List" / "New Dynamic List" sub-tabs are commented out (modules/lists/ListsUI.php:57-58); they reference an `*al=ACCESS_LEVEL_EDIT@lists.newListStatic` syntax that is not active.

**What a saved list is** — backed by two tables (db/cats_schema.sql:903-919, db/cats_schema.sql:925-937):

- `saved_list` — one row per list: `saved_list_id`, `description` (the list name, `varchar(64)`), `data_item_type`, `site_id`, `is_dynamic` (`int(1)` default `0`), `datagrid_instance`, `parameters` (`text`), `created_by`, `number_entries`, `date_created`, `date_modified`.
- `saved_list_entry` — one row per member item: `saved_list_entry_id`, `saved_list_id`, `data_item_type`, `data_item_id`, `site_id`, `date_created`.

A list's `data_item_type` is one of the `DATA_ITEM_*` constants (`DATA_ITEM_CANDIDATE`, `DATA_ITEM_COMPANY`, `DATA_ITEM_CONTACT`, `DATA_ITEM_JOBORDER`), branched on at modules/lists/ListsUI.php:177-194. The list module itself has the data-item type `DATA_ITEM_LIST = 700` (constants.php:63), used when registering a viewed list in the MRU (modules/lists/ListsUI.php:210-212).

Although the schema and `SavedLists::getAll()` support a `is_dynamic` / dynamic-vs-static distinction (`ALL_LISTS=0`, `STATIC_LISTS=1`, `DYNAMIC_LISTS=2`; constants.php:152-154), every write path in this repo creates static lists (`is_dynamic = 0`, lib/SavedLists.php:212, lib/SavedLists.php:309-332), and `showList()` only resolves a datagrid for `$listRS['isDynamic'] == 0` (modules/lists/ListsUI.php:173). Dynamic lists are effectively dormant.

## Action catalog

Dispatch is in `handleRequest()` via `switch ($action)` (modules/lists/ListsUI.php:63-119). Action comes from `$this->getAction()` (the `a` request parameter).

**Important:** `ListsUI` contains **no** `getUserAccessLevel(...)` / `getAccessLevel(...)` calls anywhere (verified: grep of modules/lists/ListsUI.php finds the strings only inside the two commented-out sub-tab lines, :57-58). The only gate the controller imposes is login (`_authenticationRequired`). Access-level enforcement (`ACCESS_LEVEL_EDIT`) exists **only** in the four AJAX endpoints under `modules/lists/ajax/`. The dispatcher `ModuleUtility::loadModule()` does not check access level either (lib/ModuleUtility.php:51-79), and index.php only enforces login + CSRF-on-POST, not a per-module access level (index.php:112-205).

| Action (`a=`) | Exact ACL guard | Required level | Handler | lib calls | Template |
|---|---|---|---|---|---|
| `showList` | (none — login only) | (none) | `showList()` (modules/lists/ListsUI.php:155) | `new SavedLists($this->_siteID)`, `$savedLists->get($savedListID)` | modules/lists/List.tpl |
| `quickActionAddToListModal` | (none — login only) | (none) | `quickActionAddToListModal()` (modules/lists/ListsUI.php:228) | `new SavedLists`, `getAll($dataItemType, STATIC_LISTS)` | modules/lists/QuickActionAddToListModal.tpl |
| `addToListFromDatagridModal` | (none — login only) | (none) | `addToListFromDatagridModal()` (modules/lists/ListsUI.php:266) | `new SavedLists`, `getAll($dataItemType, STATIC_LISTS)` | modules/lists/QuickActionAddToListModal.tpl |
| `removeFromListDatagrid` | `isPostBack()` only (no ACL) | (none) | `removeFromListDatagrid()` (modules/lists/ListsUI.php:309); non-POST → `CommonErrors::fatalModal(COMMONERROR_BADFIELDS,...)` (modules/lists/ListsUI.php:98) | `new SavedLists`, `removeEntryMany($savedListID, ...)` | (redirect, no template) |
| `deleteStaticList` | `isPostBack()` only (no ACL) | (none) | `onDeleteStaticList()` (modules/lists/ListsUI.php:371); non-POST → `CommonErrors::fatalModal(COMMONERROR_BADFIELDS,...)` (modules/lists/ListsUI.php:109) | `new SavedLists`, `delete($savedListID)` | (redirect, no template) |
| `listByView` / *default* | (none — login only) | (none) | `listByView()` (modules/lists/ListsUI.php:124) | (none; uses `DataGrid`) | modules/lists/Lists.tpl |

Commented-out / dead switch case: `show` (modules/lists/ListsUI.php:71-75, marked `FIXME: function show() undefined`).

### AJAX endpoints (separate files, not in the `switch`)

These are invoked from `js/lists.js` via `AJAX_callCATSFunction(..., "lists:<endpoint>", ...)` and routed to `modules/lists/ajax/<endpoint>.php`. Each one **does** enforce an access-level guard:

| AJAX endpoint | Exact ACL guard | Required level | lib calls |
|---|---|---|---|
| `lists:addToLists` (modules/lists/ajax/addToLists.php) | `if ($_SESSION['CATS']->getAccessLevel('lists') < ACCESS_LEVEL_EDIT)` (addToLists.php:71) | `ACCESS_LEVEL_EDIT` (200) | `addEntryMany($list, $dataItemType, ...)` |
| `lists:deleteList` (modules/lists/ajax/deleteList.php) | `if ($_SESSION['CATS']->getAccessLevel('lists') < ACCESS_LEVEL_EDIT)` (deleteList.php:44) | `ACCESS_LEVEL_EDIT` (200) | `delete($savedListID)` |
| `lists:editListName` (modules/lists/ajax/editListName.php) | `if ($_SESSION['CATS']->getAccessLevel('lists') < ACCESS_LEVEL_EDIT)` (editListName.php:44) | `ACCESS_LEVEL_EDIT` (200) | `getIDByDescription`, `updateListName` |
| `lists:newList` (modules/lists/ajax/newList.php) | `if ($_SESSION['CATS']->getAccessLevel('lists') < ACCESS_LEVEL_EDIT)` (newList.php:44) | `ACCESS_LEVEL_EDIT` (200) | `getIDByDescription`, `newListName` |

All four also require `$_SERVER['REQUEST_METHOD'] === 'POST'` (e.g. addToLists.php:65-69) and run through `SecureAJAXInterface` (e.g. addToLists.php:63).

## Per-action detail

### `listByView()` (default action) — modules/lists/ListsUI.php:124-149

Renders the "Lists: Home" page. Reads recent datagrid parameters for `"lists:ListsDataGrid"` via `DataGrid::getRecentParamaters(...)` (modules/lists/ListsUI.php:129); if empty, defaults to `rangeStart=0, maxResults=15, filterVisible=false` (modules/lists/ListsUI.php:135-137). Builds the grid with `DataGrid::get("lists:ListsDataGrid", $dataGridProperties)` (modules/lists/ListsUI.php:140), assigns `active`, `dataGrid`, `userID` (modules/lists/ListsUI.php:142-144), fires hook `LISTS_LIST_BY_VIEW` (modules/lists/ListsUI.php:146), and displays `./modules/lists/Lists.tpl` (modules/lists/ListsUI.php:148). The grid class is `ListsDataGrid` (modules/lists/dataGrids.php:38), which queries the `saved_list` table (modules/lists/dataGrids.php:135-170).

### `showList()` — modules/lists/ListsUI.php:155-223

Displays the contents of one saved list. Validates `savedListID` from `$_GET` with `isRequiredIDValid('savedListID', $_GET)`, else `CommonErrors::fatalModal(COMMONERROR_BADINDEX,...)` (modules/lists/ListsUI.php:158-163). Loads the list via `$savedLists->get($savedListID)` (modules/lists/ListsUI.php:171). For static lists (`$listRS['isDynamic'] == 0`, modules/lists/ListsUI.php:173) it selects the per-type datagrid instance (modules/lists/ListsUI.php:177-194):

- `DATA_ITEM_CANDIDATE` → `candidates:candidatesSavedListByViewDataGrid`
- `DATA_ITEM_COMPANY` → `companies:companiesSavedListByViewDataGrid`
- `DATA_ITEM_CONTACT` → `contacts:contactSavedListByViewDataGrid`
- `DATA_ITEM_JOBORDER` → `joborders:joborderSavedListByViewDataGrid`

Adds an MRU entry with `DATA_ITEM_LIST` (modules/lists/ListsUI.php:210-212), builds the per-list datagrid keyed by `$savedListID` (modules/lists/ListsUI.php:214), assigns `active`/`dataGrid`/`listRS`/`userID`, and displays `./modules/lists/List.tpl` (modules/lists/ListsUI.php:221).

Note: if a list's `dataItemType` is none of the four cases, `$dataGridInstance` is never assigned and is used at modules/lists/ListsUI.php:197 (latent undefined-variable case).

### `quickActionAddToListModal()` — modules/lists/ListsUI.php:228-261

Add-to-list popup for a single item. Validates `dataItemType` and `dataItemID` from `$_GET` (modules/lists/ListsUI.php:231-242). Wraps the single ID in an array (modules/lists/ListsUI.php:246), fetches candidate lists via `$savedLists->getAll($dataItemType, STATIC_LISTS)` (modules/lists/ListsUI.php:250), gets a human label via `TemplateUtility::getDataItemTypeDescription($dataItemType)` (modules/lists/ListsUI.php:252), and displays `QuickActionAddToListModal.tpl` (modules/lists/ListsUI.php:260). Passes `sessionCookie` (`$_SESSION['CATS']->getCookie()`) to the template (modules/lists/ListsUI.php:258).

### `addToListFromDatagridModal()` — modules/lists/ListsUI.php:266-304

Same popup but for multiple items selected in a datagrid. Validates `dataItemType` (modules/lists/ListsUI.php:269), rebuilds the grid with `DataGrid::getFromRequest()` and pulls selected IDs with `$dataGrid->getExportIDs()` (modules/lists/ListsUI.php:275-277), validates each ID (modules/lists/ListsUI.php:280-287), then loads lists and renders the same modal (modules/lists/ListsUI.php:291-303).

### `removeFromListDatagrid()` — modules/lists/ListsUI.php:309-366

Removes selected datagrid items from a list. Gated by `isPostBack()` in the switch (modules/lists/ListsUI.php:92-99). Validates `dataItemType` (`$_POST`, modules/lists/ListsUI.php:312), pulls IDs from the grid (modules/lists/ListsUI.php:318-320) and validates each (modules/lists/ListsUI.php:323-330), validates `savedListID` (modules/lists/ListsUI.php:336-340). Removes in batches of >200 via `$savedLists->removeEntryMany($savedListID, $dataItemIDArrayTemp)` (modules/lists/ListsUI.php:347-361), then `CATSUtility::transferRelativeURI('m=lists&a=showList&savedListID='.$savedListID)` (modules/lists/ListsUI.php:365).

### `onDeleteStaticList()` — modules/lists/ListsUI.php:371-389

Deletes an entire list. Gated by `isPostBack()` in the switch (modules/lists/ListsUI.php:103-110). Validates `savedListID` (`$_POST`, modules/lists/ListsUI.php:374-378), calls `$savedLists->delete($savedListID)` (modules/lists/ListsUI.php:385) — which deletes both the `saved_list` row and its `saved_list_entry` rows (lib/SavedLists.php:232-257) — then redirects to `m=lists` (modules/lists/ListsUI.php:388).

## Templates

- **modules/lists/Lists.tpl** — the Lists home page. Loads `js/highlightrows.js, js/sweetTitles.js, js/export.js, js/dataGrid.js, js/dataGridFilters.js` (Lists.tpl:2). Draws the `ListsDataGrid` (`->drawFilterArea()`, `->draw()`, `->printNavigation(true)`, Lists.tpl:41-49). When the grid has no rows it shows an empty-state panel telling the user to "Create lists from the job orders, candidates, companies or contacts tab" (Lists.tpl:52-73).
- **modules/lists/List.tpl** — a single list's contents page. Additionally loads `js/lists.js` (List.tpl:2). Title shows `$this->listRS['description']` (List.tpl:14). The "Delete List" link calls `deleteListFromListView($savedListID, $numberEntries)` (List.tpl:18). Draws the per-type member datagrid plus `printActionArea()` (List.tpl:34-43). Two header links (Duplicate List, Rename List) are commented out (List.tpl:16-17).
- **modules/lists/QuickActionAddToListModal.tpl** — the add-to-list modal, rendered via `TemplateUtility::printModalHeader('Candidates', array('js/lists.js'), 'Add to '.$this->dataItemDesc.' Static Lists')` (QuickActionAddToListModal.tpl:2). Hidden field `dataItemArray` holds the comma-joined item IDs (QuickActionAddToListModal.tpl:9). For each existing list it renders a checkbox row plus inline edit/delete rows wired to `editListRow()`, `saveListRow()`, `deleteListRow()` (QuickActionAddToListModal.tpl:11-32). A "New List" row (`savedListNew`) is wired to `commitNewList(sessionCookie, dataItemType)` (QuickActionAddToListModal.tpl:34-42). Footer buttons call `addListRow()`, `addItemsToList(sessionCookie, dataItemType)`, and `parentHidePopWin()` (QuickActionAddToListModal.tpl:48-52). Two inline JS helpers `relabelEvenOdd()` and `getCheckedBoxes()` are emitted per-list at the bottom (QuickActionAddToListModal.tpl:59-106).

## JavaScript

**js/lists.js** (loaded by List.tpl:2 and QuickActionAddToListModal.tpl:2). Functions:

- `editListRow(rowNumber)` (js/lists.js:28) — toggles a list row into inline-edit mode.
- `saveListRow(rowNumber, sessionCookie)` (js/lists.js:36) — POSTs `savedListID` + `savedListName` to `lists:editListName` via `AJAX_callCATSFunction` (js/lists.js:104-113); handles `success` / `collision` / `badName` responses (js/lists.js:79-101).
- `addListRow(sessionCookie)` (js/lists.js:116) — reveals the new-list input row.
- `commitNewList(sessionCookie, dataItemType)` (js/lists.js:123) — POSTs `dataItemType` + `description` to `lists:newList` (js/lists.js:189-198); on `success` reloads the page with a cache-busting query string (js/lists.js:163-173).
- `deleteListFromListView(savedListID, numberEntries)` (js/lists.js:201) — confirms (if non-empty), then builds and submits a POST form to `m=lists&a=deleteStaticList` with `postback`, `savedListID`, and (if present) `csrfToken` hidden inputs (js/lists.js:208-235).
- `deleteListRow(savedListID, sessionCookie, numberEntries)` (js/lists.js:238) — confirms, then POSTs `savedListID` to `lists:deleteList` (js/lists.js:297-306).
- `addItemsToList(sessionCookie, dataItemType)` (js/lists.js:310) — gathers checked lists via `getCheckedBoxes()`, POSTs `dataItemType` + `listsToAdd` + `itemsToAdd` to `lists:addToLists` with a 60s timeout (js/lists.js:369-378); on `success` calls `parentHidePopWinRefresh()` after 1.5s (js/lists.js:361-365).

Note the asymmetry: `deleteListFromListView` (full-page delete) explicitly appends a `csrfToken` hidden input (js/lists.js:224-232), whereas the AJAX paths rely on `AJAX_callCATSFunction` / `SecureAJAXInterface` instead.

## lib/ dependencies (cited)

### lib/SavedLists.php — `class SavedLists` (lib/SavedLists.php:38)

Constructor `public function __construct($siteID)` stores `$this->_siteID` and grabs `DatabaseConnection::getInstance()` (lib/SavedLists.php:44-48). All queries are site-scoped via `site_id = %s`. Methods used by this module:

- `public function get($savedListID)` (lib/SavedLists.php:58) — single list row (incl. `number_entries`).
- `public function getAll($dataItemType = -1, $listType = ALL_LISTS)` (lib/SavedLists.php:94) — all lists, optionally filtered by data-item type and by `STATIC_LISTS` / `DYNAMIC_LISTS` (lib/SavedLists.php:108-116).
- `public function getIDByDescription($description)` (lib/SavedLists.php:148) — returns the list ID for a name or `-1` (used by newList/editListName collision checks).
- `public function updateListName($savedListID, $description)` (lib/SavedLists.php:179) — UPDATE description + `date_modified`.
- `public function newListName($description, $dataItemType)` (lib/SavedLists.php:204) — INSERT a new static list (`is_dynamic = 0`, `number_entries = 0`, `created_by = getUserID()`).
- `public function delete($savedListID)` (lib/SavedLists.php:232) — DELETE the `saved_list` row and all matching `saved_list_entry` rows.
- `public function getListsByItem($dataItemType, $dataItemID)` (lib/SavedLists.php:266) — lists a given item belongs to (joins `saved_list_entry` → `saved_list`).
- `public function updateSavedLists($updates, $dataItemType)` (lib/SavedLists.php:301) — applies `LIST_EDITOR_ADD/REMOVE/MODIFY` diffs to the `saved_list` table.
- `function addEntryMany($savedListID, $dataItemType, $dataItemIDs)` (lib/SavedLists.php:391) — bulk-inserts entries (skips if any already present), then recomputes the count.
- `function removeEntryMany($savedListID, $dataItemIDs)` (lib/SavedLists.php:445) — bulk-deletes entries by `data_item_id IN (...)`, then recomputes the count.
- `private function updateSavedListItemCountAndTimeStamp($savedListID)` (lib/SavedLists.php:471) — recomputes `number_entries` and bumps `date_modified`.
- `public function updateDataItemSavedLists($updates, $dataItemID, $dataItemType)` (lib/SavedLists.php:522) — applies list-editor diffs to `saved_list_entry` for a single data item.

### lib/ListEditor.php — `class ListEditor` (lib/ListEditor.php:47)

Static, non-instantiable helper (private ctor/clone, lib/ListEditor.php:50-51). Defines the editor diff constants `LIST_EDITOR_UNKNOWN=-1, LIST_EDITOR_UNCHANGED=0, LIST_EDITOR_ADD=1, LIST_EDITOR_REMOVE=2, LIST_EDITOR_MODIFY=3` (lib/ListEditor.php:33-37). Notable methods:

- `public static function getArrayVaulesfromCSV($string)` (lib/ListEditor.php:61) — parses a quoted-CSV list string into an array.
- `public static function getStringFromList($rs, $index)` (lib/ListEditor.php:118) — serialises a recordset column back to CSV.
- `public static function getAddValues($theArray)` (lib/ListEditor.php:149) / `getEditValues($theArray)` (lib/ListEditor.php:170) — split `!!EDIT!!`-tagged tokens.
- `public static function getDifferencesFromList($rsOriginal, $rsFieldNameOriginal, $rsFieldIndexOriginal, $stringListEditor)` (lib/ListEditor.php:211) — produces the `[name, id, action]` diff array consumed by `SavedLists::updateSavedLists`/`updateDataItemSavedLists`. Includes a delete safeguard requiring a `&DELETEALLOWED&` marker before any `LIST_EDITOR_REMOVE` is emitted (lib/ListEditor.php:217-221, lib/ListEditor.php:277-285).

ListEditor is `include_once`'d by `ListsUI` (modules/lists/ListsUI.php:38) but `ListsUI` itself does not call its methods directly; they are used through the `SavedLists::update*` paths.

### Other includes (modules/lists/ListsUI.php:30-41)

`StringUtility, DateUtility, ResultSetUtility, Companies, Contacts, JobOrders, Attachments, Export, ListEditor, FileUtility, SavedLists, ExtraFields`. The data-item lib classes (Companies/Contacts/JobOrders) back the per-type member datagrids referenced in `showList()`.

## Hooks fired

| Hook key | Where | Cite |
|---|---|---|
| `LISTS_HANDLE_REQUEST` | top of `handleRequest()`, before the switch; `if (!eval(Hooks::get('LISTS_HANDLE_REQUEST'))) return;` | modules/lists/ListsUI.php:67 |
| `LISTS_LIST_BY_VIEW` | inside `listByView()`, after template assigns, before display | modules/lists/ListsUI.php:146 |

`Hooks::get($hookName)` returns `'return true;'` when no hooks are registered (lib/Hooks.php:52-56), so by default both `eval(...)` calls evaluate true and execution continues. No other `Hooks::get(...)` calls exist in the module (also note `ModuleUtility::loadModule` fires `LOAD_MODULE`, lib/ModuleUtility.php:75, which is not lists-specific).

## Source evidence

- modules/lists/ListsUI.php — controller (read in full, 392 lines).
- modules/lists/Lists.tpl — home page template (read in full).
- modules/lists/List.tpl — single-list template (read in full).
- modules/lists/QuickActionAddToListModal.tpl — add-to-list modal (read in full).
- modules/lists/dataGrids.php — `ListsDataGrid` (read in full).
- modules/lists/ajax/addToLists.php, deleteList.php, editListName.php, newList.php — AJAX endpoints (read in full).
- js/lists.js — client-side logic (read in full; this is the module's JS, loaded by the templates).
- lib/SavedLists.php — data layer (read in full).
- lib/ListEditor.php — diff helper + LIST_EDITOR_* constants (read in full).
- constants.php:63 (`DATA_ITEM_LIST` = 700), constants.php:76-81 (access levels), constants.php:152-154 (`ALL_LISTS`/`STATIC_LISTS`/`DYNAMIC_LISTS`).
- db/cats_schema.sql:903-919 (`saved_list`), db/cats_schema.sql:925-937 (`saved_list_entry`).
- lib/ModuleUtility.php:51-79 (`loadModule` — no ACL gate), lib/UserInterface.php:429-431 (`getUserAccessLevel`), index.php:112-205 (login + CSRF dispatch).

## Unverified / open questions

- **No per-action access control on the controller.** None of the `ListsUI` web actions (`listByView`, `showList`, the two modal actions, `removeFromListDatagrid`, `onDeleteStaticList`) call `getUserAccessLevel(...)`/`getAccessLevel(...)`. The only enforced level (`ACCESS_LEVEL_EDIT`) is in the four `modules/lists/ajax/*.php` endpoints. I did not find a framework-level table mapping the `lists` module to a minimum access level; index.php and ModuleUtility enforce only login (and CSRF on POST). The ACL-SUMMARY below therefore reports `(none)` for controller actions; treat "anyone logged in can view/delete a list via the web actions" as a finding to confirm against any intended access-control design.
- The `js/lists.js` reference `AJAX_callCATSFunction(..., "lists:editListName", ...)` maps endpoint name → `modules/lists/ajax/editListName.php` by convention; I read the four endpoint files directly but did not trace the `SecureAJAXInterface` routing layer that resolves `"lists:<name>"` to a file.
- `showList()` `$dataGridInstance` is unset when `dataItemType` is not one of the four handled constants (modules/lists/ListsUI.php:177-197) — latent bug, not exercised in normal flow; not verified at runtime.
- Dynamic lists (`is_dynamic = 1`, `DYNAMIC_LISTS`) are supported by the schema and `getAll()` but no code path in this module creates or renders them; the `newList`/`newListName` paths hardcode `is_dynamic = 0`. Whether dynamic lists are produced elsewhere in the codebase was not investigated.
- `addEntryMany` / `removeEntryMany` interpolate `implode(',', $dataItemIDs)` directly into SQL `IN (...)` (lib/SavedLists.php:406, lib/SavedLists.php:459). The IDs are validated upstream (controller `isRequiredIDValid`, AJAX `isRequiredValueValid`/`ctype_digit`), but I did not exhaustively audit every caller for injection safety.

---

ACL-SUMMARY
```
lists.listByView                 => (none)
lists.showList                   => (none)
lists.quickActionAddToListModal  => (none)
lists.addToListFromDatagridModal => (none)
lists.removeFromListDatagrid     => (none)   [POST-only via isPostBack()]
lists.deleteStaticList           => (none)   [POST-only via isPostBack()]
lists.ajax:addToLists            => ACCESS_LEVEL_EDIT
lists.ajax:deleteList            => ACCESS_LEVEL_EDIT
lists.ajax:editListName          => ACCESS_LEVEL_EDIT
lists.ajax:newList               => ACCESS_LEVEL_EDIT
```
