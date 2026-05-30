# Module: home

The `home` module renders the OpenCATS **Dashboard** (the landing page shown after login) and also hosts the global **Quick Search** ("Search Everything") and the saved-search add/delete endpoints used by the search UI.

## Overview

### Classes declared

| Class | Declared at | Extends | Role |
|-------|-------------|---------|------|
| `HomeUI` | `modules/home/HomeUI.php:34` | `UserInterface` | Module controller / request dispatcher |
| `ImportantPipelineDashboard` | `modules/home/dataGrids.php:40` | `DataGrid` | "Important Candidates" grid |
| `CallsDataGrid` | `modules/home/dataGrids.php:201` | `DataGrid` | "My Recent Calls" grid |

`HomeUI` is the only class in `HomeUI.php`; the two DataGrid subclasses live in the separate `modules/home/dataGrids.php` (a self-aware "Multiple classes per file probably also bad" FIXME at `modules/home/dataGrids.php:196`). `HomeUI.php` includes `lib/NewVersionCheck.php`, `lib/CommonErrors.php`, and `lib/Dashboard.php` at the top (`modules/home/HomeUI.php:30-32`).

### Constructor settings

```php
public function __construct()                       // modules/home/HomeUI.php:36
{
    parent::__construct();
    $this->_authenticationRequired = true;          // :40
    $this->_moduleDirectory = 'home';               // :41
    $this->_moduleName = 'home';                    // :42
    $this->_moduleTabText = 'Dashboard';            // :43
    $this->_subTabs = array();                       // :44
}
```

Authentication is required (`_authenticationRequired = true`, `modules/home/HomeUI.php:40`); there are **no** per-action access-level checks anywhere in the module — `getUserAccessLevel(...)` does not appear in `HomeUI.php` at all (verified by grep). The tab label is "Dashboard" and the module declares no sub-tabs.

### What the dashboard shows

The `home()` action assembles the dashboard from several sources (`modules/home/HomeUI.php:105-153`):

- **News / version check** via `NewVersionCheck::getnews()` (`:109`).
- **Recent Hires** — `Dashboard::getPlacements()` (`:111-112`), rendered as a table in `Home.tpl`.
- **My Upcoming Calls** and **My Upcoming Events** — two `Calendar::getUpcomingEventsHTML(7, ...)` calls (`:114-118`).
- **Important Candidates** grid — `home:ImportantPipelineDashboard` DataGrid (`:128`).
- **My Recent Calls** grid — `home:CallsDataGrid` DataGrid, restricted to the last month (`:141-143`).
- **Hiring Overview** graph — an `<img>` pointing at `m=graphs&a=miniPlacementStatistics` (`Home.tpl:66`), with weekly/monthly/yearly toggles wired through `swapHomeGraph()` (`js/home.js:29`).

## Action catalog

`handleRequest()` (`modules/home/HomeUI.php:48`) reads `$action = $this->getAction()` and runs the `HOME_HANDLE_REQUEST` hook before the switch (`:52`). None of the cases call `getUserAccessLevel()`; the only gate is the module-level `_authenticationRequired = true`.

| Action | ACL guard | Required level | Handler | lib calls | Template |
|--------|-----------|----------------|---------|-----------|----------|
| `quickSearch` | (none) | (none) | `quickSearch()` (`:205`) | `QuickSearch::candidates/companies/contacts/jobOrders`; `StringUtility::makeInitialName` | `./modules/home/SearchEverything.tpl` (`:391`) |
| `deleteSavedSearch` | (none) — only `isPostBack()` check (`:66`) | (none) | `deleteSavedSearch()` (`:155`) | `SavedSearches::remove`; `CATSUtility::transferRelativeURI` | none (redirect) |
| `addSavedSearch` | (none) — only `isPostBack()` check (`:79`) | (none) | `addSavedSearch()` (`:180`) | `SavedSearches::save`; `CATSUtility::transferRelativeURI` | none (redirect) |
| `home` (and `default`) | (none) | (none) | `home()` (`:105`) | `NewVersionCheck::getnews`; `Dashboard::getPlacements`; `Calendar::getUpcomingEventsHTML`; `DataGrid::get` x2 | `./modules/home/Home.tpl` (`:152`) |

A `getAttachment` case is commented out (`modules/home/HomeUI.php:89-95`, "FIXME: undefined function getAttachment()").

For non-postback `deleteSavedSearch` / `addSavedSearch` requests, the handler calls `CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid request.')` (`:72`, `:85`).

## Per-action detail

### `home` (default dashboard) — `modules/home/HomeUI.php:105`

```php
private function home()
{
    if (!eval(Hooks::get('HOME'))) return;          // :107
    NewVersionCheck::getnews();                      // :109
    $dashboard = new Dashboard($this->_siteID);      // :111
    $placedRS = $dashboard->getPlacements();         // :112
    $calendar = new Calendar($this->_siteID);
    $upcomingEventsHTML    = $calendar->getUpcomingEventsHTML(7, UPCOMING_FOR_DASHBOARD);      // :115
    $calendar = new Calendar($this->_siteID);
    $upcomingEventsFupHTML = $calendar->getUpcomingEventsHTML(7, UPCOMING_FOR_DASHBOARD_FUP);  // :118
    ...
}
```

DataGrid construction (`modules/home/HomeUI.php:122-145`):

- `ImportantPipelineDashboard` is built with `array('rangeStart'=>0,'maxResults'=>15,'filterVisible'=>false)` via `DataGrid::get("home:ImportantPipelineDashboard", $dataGridProperties)` (`:122-128`), assigned to template var `dataGrid` (`:130`).
- `CallsDataGrid` uses the same base properties plus `startDate=''`, `endDate=''`, and `period='DATE_SUB(CURDATE(), INTERVAL 1 MONTH)'` (the "Only show a month of activities" comment, `:138-141`), via `DataGrid::get("home:CallsDataGrid", $dataGridProperties)` (`:143`), assigned to `dataGrid2` (`:145`).

Template assignments (`:147-151`): `active` (=`$this`), `placedRS`, `upcomingEventsHTML`, `upcomingEventsFupHTML`, and `wildCardQuickSearch=''`. Renders `./modules/home/Home.tpl` (`:152`).

### `quickSearch` — `modules/home/HomeUI.php:205`

Bails with `CommonErrors::fatal(COMMONERROR_BADFIELDS, ...)` if `$_GET['quickSearchFor']` is unset (`:210-213`). Otherwise it trims the query (`:215`) and runs four searches against a `QuickSearch` instance (`:218-222`):

```php
$search = new QuickSearch($this->_siteID);
$candidatesRS = $search->candidates($query);   // :219
$companiesRS  = $search->companies($query);    // :220
$contactsRS   = $search->contacts($query);     // :221
$jobOrdersRS  = $search->jobOrders($query);    // :222
//$listsRS      = $search->lists($query);        // :223 (commented out; no lists() method exists)
```

It then post-processes each result set in PHP (`:225-379`):
- Owner display names via `StringUtility::makeInitialName(..., LAST_NAME_MAXLEN)`, falling back to `'None'` when no owner (e.g. candidates `:229-241`).
- Empty phone fields are replaced with `'None'` (candidates `:243-251`, companies `:273-276`, contacts `:321-329`).
- Hot/cold link CSS classes: candidates/contacts/companies/job orders get `jobLinkHot`/`jobLinkCold` (e.g. job orders `:342-349`), contacts who left a company get `jobLinkDead` (`:294-296`).
- Job orders with `startDate == '00-00-00'` are blanked (`:337-340`).

Assigns `active`, `jobOrdersRS`, `candidatesRS`, `companiesRS`, `contactsRS`, `wildCardQuickSearch` (`:381-387`; `listsRS` assignment commented out at `:386`), fires `HOME_QUICK_SEARCH` (`:389`), and renders `./modules/home/SearchEverything.tpl` (`:391`).

### `addSavedSearch` — `modules/home/HomeUI.php:180`

Requires `$_POST['searchID']` (else `COMMONERROR_BADINDEX`, `:182-185`) and `$_POST['currentURL']` (else `COMMONERROR_BADFIELDS`, `:187-190`). Fires `HOME_ADD_SAVED_SEARCH_PRE` (`:195`), promotes the recent search to a custom saved search via `(new SavedSearches($this->_siteID))->save($searchID)` (`:197-198`), fires `HOME_ADD_SAVED_SEARCH_POST` (`:200`), then `CATSUtility::transferRelativeURI($currentURL)` (`:202`).

`SavedSearches::save($searchID)` (`lib/Search.php:1726`) runs `UPDATE saved_search SET is_custom = 1 WHERE search_id = %s AND user_id = %s AND site_id = %s` (`lib/Search.php:1728-1744`) — scoped to the current user (`$_SESSION['CATS']->getUserID()`, set in the constructor at `lib/Search.php:1692`) and site.

### `deleteSavedSearch` — `modules/home/HomeUI.php:155`

Same field validation as add (`:157-165`). Fires `HOME_DELETE_SAVED_SEARCH_PRE` (`:170`), calls `(new SavedSearches($this->_siteID))->remove($searchID)` (`:172-173`), fires `HOME_DELETE_SAVED_SEARCH_POST` (`:175`), then redirects via `CATSUtility::transferRelativeURI($currentURL)` (`:177`).

`SavedSearches::remove($searchID)` (`lib/Search.php:1702`) runs `DELETE FROM saved_search WHERE search_id = %s AND user_id = %s AND site_id = %s` (`lib/Search.php:1704-1717`).

## Dashboard DataGrids

### `ImportantPipelineDashboard` — `modules/home/dataGrids.php:40`

Constructor `__construct($siteID, $parameters)` (`:46`). Configuration: `ajaxMode = true` (`:51`), no export column/checkboxes (`:52-53`), action area + choose-columns box + resizing on (`:54-56`), `ignoreSavedColumnLayouts = true` (`:59`), default sort `dateModifiedSort DESC` (`:61-62`). Default columns: First Name, Last Name, Status, Position, Company, Modified (`:64-71`). Calls `parent::__construct("home:ImportantPipelineDashboard", $parameters)` (`:120`).

`getSQL(...)` (`:128`) selects from `candidate_joborder` joined to `candidate`, `joborder`, `company`, `candidate_joborder_status`, and `user` (`:150-161`), filtered to the site (`:163`) and to pipeline rows whose status is `PIPELINE_STATUS_SUBMITTED`, `PIPELINE_STATUS_INTERVIEWING`, or `PIPELINE_STATUS_OFFERED` (`:165-170`), and whose job order status is in `JobOrderStatuses::getOpenStatusSQL()` (`:172`, `:184`). `statusSort` orders Submitted(1)/Interviewing(2)/other(3) (`:148`). This is the "Important Candidates (Submitted, Interviewing, Offered in Active Job Orders)" panel (`Home.tpl:74`).

`JobOrderStatuses::getOpenStatusSQL()` (`lib/JobOrderStatuses.php:133`) builds a quoted `IN (...)` list from the "Open" status group.

### `CallsDataGrid` — `modules/home/dataGrids.php:201`

Constructor `__construct($siteID, $parameters)` (`:207`). Table width 30% (`:210`), `ajaxMode = true` (`:212`), `allowSorting = false` (`:216`), `listStyle = true` (`:221`), `ignoreSavedColumnLayouts = true` (`:222`). Builds `dateCriterion` from the passed `period` parameter (e.g. the dashboard's `DATE_SUB(CURDATE(), INTERVAL 1 MONTH)`), or otherwise from `startDate`/`endDate` (`:224-239`). Captures `$_userID = $_SESSION['CATS']->getUserID()` (`:252`). Default columns: Time, Name (`:244-247`). Calls `parent::__construct("home:CallsDataGrid", $parameters)` (`:273`).

`getSQL(...)` (`:281`) is a `UNION` of two activity queries — one joining `activity` to `candidate` for `DATA_ITEM_CANDIDATE` activities (`:327-330`), one joining to `contact` for `DATA_ITEM_CONTACT` activities (`:381-384`). Both are filtered to `activity.entered_by = $this->_userID` (`:332`, `:386`) and the current site (`:333-334`, `:387-388`), ordered `dateCreatedSort DESC` and hard-capped at `LIMIT 6` (`:392-393`). This is the "My Recent Calls" panel (`Home.tpl:13`).

`dataGrids.php` includes `lib/Hooks.php`, `lib/InfoString.php`, `lib/Pipelines.php`, `lib/Width.php` at the top (`:34-37`) and `lib/ActivityEntries.php` + `lib/Hooks.php` + `lib/InfoString.php` again mid-file (`:197-199`).

## Templates

| Template | Used by | Notes |
|----------|---------|-------|
| `Home.tpl` | `home()` (`HomeUI.php:152`) | Dashboard layout |
| `SearchEverything.tpl` | `quickSearch()` (`HomeUI.php:391`) | Quick Search results |
| `Error.tpl` | (not referenced from `HomeUI.php`) | Generic fatal-error page |
| `FriendlyError.tpl` | (not referenced from `HomeUI.php`) | "Friendly" support error page |

- **`Home.tpl`** — loads JS `js/sweetTitles.js`, `js/dataGrid.js`, `js/dataGridFilters.js`, `js/home.js` (`Home.tpl:2`). Lays out, top to bottom: My Recent Calls grid (`$this->dataGrid2->drawHTML()`, `:14`), upcoming calls/events HTML (`:18`, `:22`), Recent Hires table over `$this->placedRS` (`:39-47`) with a "no hires" placeholder image when empty (`:49-53`), the Hiring Overview graph image + image-map toggles calling `swapHomeGraph(DASHBOARD_GRAPH_WEEKLY|MONTHLY|YEARLY)` (`:58-66`), and the Important Candidates grid (`$this->dataGrid->draw()`, `:75`) with pager/show-all (`:76`) and a "no candidates" placeholder (`:78-82`).
- **`SearchEverything.tpl`** — loads `js/sorttable.js` (`:2`); renders four sortable result tables for Job Orders (`:20-60`), Candidates (`:66-100`), Companies (`:106-134`), Contacts (`:140-184`), each showing "No matching entries found." when empty. Owner/recruiter names and hot/cold link classes come from the post-processing done in `quickSearch()`.
- **`Error.tpl`** — minimal fatal-error page echoing `$this->errorMessage` (`:21`).
- **`FriendlyError.tpl`** — support-styled error page with optional modal mode, echoes `errorTitle`/`errorMessage`, has a demo-account note, and fires the `FRIENDLYERRORS_CONTACTCATS` hook (`:52`).

## JavaScript

**`js/home.js`** (referenced by `Home.tpl:2`):

```js
function swapHomeGraph(view)                         // js/home.js:29
{
    var homeGraphImage = document.getElementById("homeGraph");
    homeGraphImage.src = CATSIndexName +
        "?m=graphs&a=miniPlacementStatistics&width=495&height=230&view=" + view;  // :33
}

function trackTableHighlight()                        // js/home.js:38
{
    return;                                            // :40  (intentional no-op)
}
```

`swapHomeGraph(view)` swaps the Hiring Overview graph image to the weekly/monthly/yearly view; `trackTableHighlight()` is a deliberate no-op ("We don't need to mouseover.", `js/home.js:36`). The grids additionally rely on the shared `js/dataGrid.js` / `js/dataGridFilters.js` loaded by `Home.tpl`.

## lib/ dependencies (cited)

- **`lib/Dashboard.php`** — `Dashboard::__construct($siteID)` (`:46`); `Dashboard::getPlacements()` (`:57`) returns up to 10 rows from `candidate_joborder_status_history` where `status_to = 800` (placed) for the site (`:84-91`); `Dashboard::getPipelineData($view)` (`:107`) builds the weekly/monthly/yearly placement-statistics arrays (used by the graphs module, not directly by `HomeUI`).
- **`lib/Search.php`** — `QuickSearch::__construct($siteID)` (`:1373`); `QuickSearch::candidates($wildCardString)` (`:1389`), `::companies($wildCardString)` (`:1466`), `::contacts($wildCardString)` (`:1521`), `::jobOrders($wildCardString)` (`:1609`). `SavedSearches::__construct($siteID)` (`:1687`); `SavedSearches::save($searchID)` (`:1726`); `SavedSearches::remove($searchID)` (`:1702`).
- **`lib/JobOrderStatuses.php`** — `JobOrderStatuses::getOpenStatusSQL()` (`:133`), used by `ImportantPipelineDashboard::getSQL` (`dataGrids.php:184`).
- **`lib/Calendar.php`** — `Calendar::getUpcomingEventsHTML($limit, $flag = UPCOMING_FOR_CALENDAR)` (`:685`); the dashboard passes `UPCOMING_FOR_DASHBOARD` (events, excludes type 100, `:695-699`) and `UPCOMING_FOR_DASHBOARD_FUP` (calls, type 100 only, `:701-705`).
- **`lib/NewVersionCheck.php`** — `NewVersionCheck::getnews()` called at `HomeUI.php:109`.
- **`lib/CommonErrors.php`** — `CommonErrors::fatal(...)` for bad requests (`HomeUI.php:72,85,159,164,184,188,212`).
- **`lib/Pipelines.php`** — included by `dataGrids.php:36`; no `Pipelines` method is called directly in the home module (the grids define their own SQL and use `PIPELINE_STATUS_*` constants + `JobOrderStatuses::getOpenStatusSQL()`).
- Constants used: `UPCOMING_FOR_DASHBOARD`, `UPCOMING_FOR_DASHBOARD_FUP`, `DASHBOARD_GRAPH_WEEKLY/MONTHLY/YEARLY` all defined in `constants.php:158-164`.

## Hooks fired

| Hook key | Where | File:line |
|----------|-------|-----------|
| `HOME_HANDLE_REQUEST` | top of `handleRequest()` | `modules/home/HomeUI.php:52` |
| `HOME` | top of `home()` | `modules/home/HomeUI.php:107` |
| `HOME_QUICK_SEARCH` | before rendering quick-search results | `modules/home/HomeUI.php:389` |
| `HOME_DELETE_SAVED_SEARCH_PRE` | before delete | `modules/home/HomeUI.php:170` |
| `HOME_DELETE_SAVED_SEARCH_POST` | after delete | `modules/home/HomeUI.php:175` |
| `HOME_ADD_SAVED_SEARCH_PRE` | before save | `modules/home/HomeUI.php:195` |
| `HOME_ADD_SAVED_SEARCH_POST` | after save | `modules/home/HomeUI.php:200` |
| `FRIENDLYERRORS_CONTACTCATS` | in `FriendlyError.tpl` | `modules/home/FriendlyError.tpl:52` |

Related hooks fired **outside** the module but on the home/dashboard path:
- `INDEX_LOAD_HOME` — fired by the front controller just before `ModuleUtility::loadModule('home')` when no module is specified and the user is logged in (`index.php:199`, context `index.php:192-202`).
- `LOGGED_IN`, `LOGGED_IN_MESSAGES`, `LOGGED_IN_HOME_PAGE` — fired in the login module after authentication (`modules/login/LoginUI.php:385,393,433`), not within `home`.

## Source evidence

- `modules/home/HomeUI.php` (read in full, lines 1-395) — class, constructor, dispatcher, all four handlers.
- `modules/home/dataGrids.php` (read in full, lines 1-413) — `ImportantPipelineDashboard` and `CallsDataGrid`.
- `modules/home/Home.tpl` (1-89), `modules/home/SearchEverything.tpl` (1-188), `modules/home/Error.tpl` (1-26), `modules/home/FriendlyError.tpl` (1-63).
- `js/home.js` (1-40).
- `lib/Dashboard.php` (1-273), `lib/Search.php` (`QuickSearch` 1366-1455, `SavedSearches` 1680-1799), `lib/JobOrderStatuses.php` (125-153), `lib/Calendar.php` (685-714), `constants.php` (155-165), `index.php` (190-210), `modules/login/LoginUI.php` (385,393,433 via grep).

## Unverified / open questions

- The DataGrid base class behavior (`DataGrid::get`, `draw`, `drawHTML`, `printNavigation`, `printShowAll`, AJAX paging, column-layout persistence) was not read; the home grids set `ignoreSavedColumnLayouts = true` and `ajaxMode = true`, but the underlying mechanics live in `lib/DataGrid.php` (not opened here).
- `Error.tpl` and `FriendlyError.tpl` are not referenced from `HomeUI.php`; they appear to be invoked indirectly by `CommonErrors::fatal(...)` / the framework's error path, which was not traced.
- `NewVersionCheck::getnews()` is called for its side effects; its return value is unused in `home()` and its implementation was not read.
- `lib/Pipelines.php` is included by `dataGrids.php` but no direct call site was found in the home module — included presumably for `PIPELINE_STATUS_*` constants; the exact definition location of those constants was not opened.
- No CSRF token validation is visible on `addSavedSearch`/`deleteSavedSearch` beyond the `isPostBack()` check (`HomeUI.php:66,79`); whether `isPostBack()` enforces a token was not verified in the base `UserInterface`.

---

## ACL-SUMMARY

```
home.home               => (none)
home.quickSearch        => (none)
home.addSavedSearch     => (none)
home.deleteSavedSearch  => (none)
```
