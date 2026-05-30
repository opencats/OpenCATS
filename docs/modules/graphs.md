# Module: graphs

Server-side chart-image generation module. Every action ends by streaming a binary image (PNG or JPEG) to the browser and calling `die()`; no HTML template is ever rendered by this module. Images are consumed via `<img src="index.php?m=graphs&a=...">` URLs embedded by the home dashboard, the reports module, and the `lib/Graphs.php` helper.

## Overview

Controller class declaration:

```php
class GraphsUI extends UserInterface
```
(modules/graphs/GraphsUI.php:38)

The constructor (modules/graphs/GraphsUI.php:44-70) calls `parent::__construct()` then:

- Sets `$this->_authenticationRequired = false;` (modules/graphs/GraphsUI.php:48) — so the framework does **not** force a login for this module. Login is instead enforced selectively inside `handleRequest()` (see below).
- Sets `$this->_moduleDirectory = 'graphs';` and `$this->_moduleName = 'graphs';` (modules/graphs/GraphsUI.php:49-50), and `$this->_subTabs = array();` (modules/graphs/GraphsUI.php:51).
- Reads `$_GET['width']` (capped: must be `< 2000`, else defaults to `300`) into `$this->width` (modules/graphs/GraphsUI.php:53-60).
- Reads `$_GET['height']` (capped: must be `< 1200`, else defaults to `200`) into `$this->height` (modules/graphs/GraphsUI.php:62-69).

These two private fields are declared at the top of the class (modules/graphs/GraphsUI.php:40-41).

**Emits image, not HTML.** Each handler constructs a graph object and calls `$graph->draw()` followed by `die()`. The underlying `draw()` methods in `lib/GraphGenerator.php` create an artichow `Graph` and call `$graph->setFormat(...)` (IMG_PNG by default, IMG_JPG/IMG_JPEG for the report graph) then `$graph->draw()`, which writes the image bytes directly to the output stream. The module also overrides the framework error path so failures never produce HTML:

```php
protected function fatal($error, $directoryOverride = '')
{
    // FIXME: Generate an image containing the error message?
    die($error);
}
```
(modules/graphs/GraphsUI.php:625-629) — "really, we never want the graphs module to return anything but an image" (modules/graphs/GraphsUI.php:619-621).

The included libraries are pulled in at file top: `Statistics.php`, `Graphs.php`, `GraphGenerator.php`, `DateUtility.php`, `CommonErrors.php`, `Dashboard.php` (modules/graphs/GraphsUI.php:30-35).

### Routing / authentication model

`handleRequest()` (modules/graphs/GraphsUI.php:73-134) routes on `$action = $this->getAction()` (which returns `$_GET['a']`, lib/UserInterface.php:193-201). It uses a two-tier switch:

1. A **first switch** (modules/graphs/GraphsUI.php:78-99) handles the actions that "do not require a login" (comment at line 77): `testGraph`, `wordVerify`, `jobOrderReportGraph`, `generic`, `genericPie`. Each `return`s immediately after dispatch.
2. Only if `$_SESSION['CATS']->isLoggedIn()` is true (modules/graphs/GraphsUI.php:101) does a **second switch** (modules/graphs/GraphsUI.php:103-132) dispatch the login-gated actions: `activity`, `newCandidates`, `newJobOrders`, `newSubmissions`, `miniPlacementStatistics`, `miniJobOrderPipeline`. Its `default` case calls `CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'No graph specified.')` (modules/graphs/GraphsUI.php:130).

**There is no `getUserAccessLevel(...)` call anywhere in this module** (verified by grep: no match for `getUserAccessLevel` or `ACCESS_LEVEL` in modules/graphs/GraphsUI.php). Authorization is binary: either no auth at all (tier 1) or merely "logged in" (tier 2, via `isLoggedIn()`). No per-action access-level check exists. If a logged-out user requests a tier-2 action, neither switch matches and `handleRequest()` simply falls through and returns nothing (no image, no error).

## Action catalog

One row per reachable `case`. "ACL guard" is the literal gate executed before the handler. No action uses `getUserAccessLevel(...)`.

| Action (`a=`) | ACL guard | Required level | Handler | Key lib calls | Output |
|---|---|---|---|---|---|
| `testGraph` | none (tier-1 switch) | (none) | `testGraph()` :137 | `new GraphSimple(...)`; `Hooks::get('GRAPH_TEST')`; `->draw()` | image/png, then `die()` |
| `wordVerify` | none (tier-1 switch) | (none) | `wordVerify()` :591 | `new Graphs()`; `$graphs->getVerificationImageText()`; `new WordVerify($text)`; `->draw()` | CAPTCHA image, then `die()` |
| `jobOrderReportGraph` | none (tier-1 switch) | (none) | `jobOrderReportGraph()` :150 | `new jobOrderReportGraph(...)`; `Hooks::get('GRAPH_JOB_ORDER_REPORT')`; `->draw(IMG_JPG)` | image/jpeg, then `die()` |
| `generic` | none (tier-1 switch) | (none) | `generic()` :357 | `new GraphSimple(...)`; `Hooks::get('GRAPH_GENERIC')`; `->draw()` | image/png, then `die()` |
| `genericPie` | none (tier-1 switch) | (none) | `genericPie()` :388 | `new GraphPie(...)`; `Hooks::get('GRAPH_GENERIC_PIE')`; `->draw()` | image/png, then `die()` |
| `activity` | `$_SESSION['CATS']->isLoggedIn()` :101 | logged-in only | `activity()` :191 | `new Statistics`; `getActivitiesByPeriod(TIME_PERIOD_LASTTWOWEEKS)`; `new GraphSimple(...)`; `Hooks::get('GRAPH_WEEKLY_ACTIVITY')` | image/png, then `die()` |
| `newCandidates` | `isLoggedIn()` :101 | logged-in only | `newCandidates()` :248 | `getCandidatesByPeriod(...)`; `new GraphSimple(...)`; `Hooks::get('GRAPH_NEW_CANDIDATES')` | image/png, then `die()` |
| `newJobOrders` | `isLoggedIn()` :101 | logged-in only | `newJobOrders()` :303 | `getJobOrdersByPeriod(...)`; `new GraphSimple(...)`; `Hooks::get('GRAPH_NEW_JOB_ORDERS')` | image/png, then `die()` |
| `newSubmissions` | `isLoggedIn()` :101 | logged-in only | `newSubmissions()` :537 | `getSubmissionsByPeriod(...)`; `new GraphSimple(...)`; `Hooks::get('GRAPH_NEW_SUBMISSIONS')` | image/png, then `die()` |
| `miniPlacementStatistics` | `isLoggedIn()` :101 | logged-in only | `miniPlacementStatistics()` :405 | `new Dashboard`; `getPipelineData($view)`; `Graphs::getColorOptions()`; `new pipelineStatisticsGraph(...)` | image/png, then `die()` |
| `miniJobOrderPipeline` | `isLoggedIn()` :101 | logged-in only | `miniJobOrderPipeline()` :465 | `isRequiredIDValid('params',...)`; `new Statistics`; `getPipelineData($_GET['params'])`; `new GraphComparisonChart(...)`; `Hooks::get('GRAPH_MINI_PIPELINE')` | image/png, then `die()` |

Note: `miniPlacementStatistics` and `miniJobOrderPipeline` are the only tier-2 actions wired into the routing switch's `case` labels (modules/graphs/GraphsUI.php:121-128) but the public-facing helper names in `lib/Graphs.php` differ — see lib/ dependencies below.

## Per-action detail

### `testGraph()` — development stub (modules/graphs/GraphsUI.php:137-148)
Hard-codes `$x = array(1,2,3,4)` / `$y = array(1,2,3,4)` and builds `new GraphSimple($x, $y, 'DarkGreen', 'Test Graph', $this->width, $this->height)` (line 142). Runs the `GRAPH_TEST` hook (line 144), then `$graph->draw(); die();`. Comment: "I am used for development purposes and intentionally empty." (line 139).

### `jobOrderReportGraph()` — bar chart of pipeline counts (modules/graphs/GraphsUI.php:150-189)
Reads `data` from `$_GET` via `getTrimmedInput('data', $_GET)` (line 153) and `explode(',', ...)`. Validates: for the first 4 elements, any missing or non-`ctype_digit` value is coerced to `0` (lines 161-167); then `array_slice($x, 0, 4)` keeps exactly four (line 170). If `data` is empty, `$x = array(0,0,0,0)` (line 174). Labels fixed to `array('Screened','Submitted','Interviewed','Placed')` (line 177). Builds four `LinearGradient` colors (Red/DarkGreen/DarkBlue/Orange → White) (lines 179-182), `new jobOrderReportGraph($y, $x, $colorArray, '', 800, 800)` (line 183, fixed 800x800 — ignores width/height). Runs `GRAPH_JOB_ORDER_REPORT` (line 185) then `$graph->draw(IMG_JPG); die();` (line 187) — JPEG output. Linked from reports: `?m=graphs&a=jobOrderReportGraph&data=...` (modules/reports/ReportsUI.php:526).

### `activity()` / `newCandidates()` / `newJobOrders()` / `newSubmissions()` — 14-day daily bar charts
Near-identical structure (modules/graphs/GraphsUI.php:191-246, 248-301, 303-355, 537-589). Each:
- Instantiates `new Statistics($this->_siteID)` and calls the matching period query with `TIME_PERIOD_LASTTWOWEEKS` (`getActivitiesByPeriod` :195, `getCandidatesByPeriod` :252, `getJobOrdersByPeriod` :307, `getSubmissionsByPeriod` :541).
- Computes `$firstDay` via `mktime(...)` using `DateUtility::getAdjustedDate('m'|'d'|'w'|'Y')`, offsetting the day by `- getAdjustedDate('w') - 7` (e.g. lines 198-205). A `// FIXME: Factor out these calculations? Common to most of these graphs.` comment marks the duplication (line 197).
- Builds 14 day-of-month labels into `$y` (loop, lines 210-221).
- Buckets result rows into a 14-slot `$x` array, choosing slot `dayOfWeek` vs `dayOfWeek + 7` based on `DateUtility::getWeekNumber($thisDay) != DateUtility::getWeekNumber()` (lines 226-238).
- Constructs `new GraphSimple($y, $x, <color>, <title>, width, height)` with per-graph color/title: `'DarkGreen','Activity'` (240), `'Blue','New Candidates'` (295), `'Red','New Job Orders'` (349), `'Orange','New Submissions'` (583).
- Runs its hook (`GRAPH_WEEKLY_ACTIVITY` :242, `GRAPH_NEW_CANDIDATES` :297, `GRAPH_NEW_JOB_ORDERS` :351, `GRAPH_NEW_SUBMISSIONS` :585) then `$graph->draw(); die();`.

### `generic()` — arbitrary bar chart from query string (modules/graphs/GraphsUI.php:357-386)
`labels` and `data` both come from `$_GET` via `getTrimmedInput` + `explode(',', ...)` (lines 360-361). Every odd-indexed label is prefixed with `'||'` (a layout hack to stagger labels, lines 363-369). `title` from `$_GET` (line 378). Builds `new GraphSimple($labels, $data, "DarkGreen", $title, $this->width, $this->height)` (line 380), runs `GRAPH_GENERIC` (line 382), `draw(); die();`. A commented-out `$colorArray` block sits at lines 371-376. Linked from reports at modules/reports/ReportsUI.php:650 and :673.

### `genericPie()` — arbitrary pie chart from query string (modules/graphs/GraphsUI.php:388-402)
Same `labels`/`data`/`title` extraction as `generic()`. Builds `new GraphPie($labels, $data, $title, $this->width, $this->height)` (line 396), runs `GRAPH_GENERIC_PIE` (line 398), `draw(); die();`. Linked from reports at modules/reports/ReportsUI.php:695 and :723 (those URLs also pass a `legendOffset` param, which `genericPie()` does not read).

### `miniPlacementStatistics()` — dashboard hiring-overview chart (modules/graphs/GraphsUI.php:405-462)
Reads optional `$_GET['view']` (cast to int), defaulting to `DASHBOARD_GRAPH_WEEKLY` (lines 407-414). Uses `new Dashboard($this->_siteID)` and `$dashboard->getPipelineData($view)` (lines 416-417). For each row, pushes a label + three blank labels and the `submitted`/`interviewing`/`placed`/`0` quad, tracking `$noData` (lines 423-440). Drops the dangling 16th value (`unset($x[15])`, line 443). Calls `Graphs::getColorOptions()` (line 445, return value unused beyond the call), then builds a repeating 4-color gradient array (blue/Orange/MidGreen/DarkGreen) (lines 448-454). Constructs `new pipelineStatisticsGraph($y, $x, $colorArray, width, height, "Submissions", "Interviews", "Hires", $view, $noData)` (lines 456-458) and `draw(); die();`. **No hook is fired here.** This is the chart embedded on the home page: `?m=graphs&a=miniPlacementStatistics&width=495&height=230` (modules/home/Home.tpl:66).

### `miniJobOrderPipeline()` — single job-order pipeline funnel (modules/graphs/GraphsUI.php:465-534)
Guards with `if (!$this->isRequiredIDValid('params', $_GET)) { die(); }` (lines 468-471) — note this dies silently (no image) when `params` is missing/invalid. Calls `new Statistics($this->_siteID)` then `$statistics->getPipelineData($_GET['params'])` (line 473). Chooses a 9-stage label set, with a wider/narrower variant depending on `$this->width > 600` (lines 476-503). Computes cumulative funnel values `$x[8]..$x[0]` (placed → totalPipeline) (lines 505-513). Calls `Graphs::getColorOptions()` (line 515, result unused), builds 9 DarkGreen gradients then overrides index 4 (Orange) and 7 (AlmostBlack) (lines 518-523). Builds `new GraphComparisonChart($y, $x, $colorArray, 'Status of Candidates', width, height, $statisticsData['totalPipeline'])` (lines 525-528), runs `GRAPH_MINI_PIPELINE` (line 530), `draw(); die();`.

### `wordVerify()` — CAPTCHA image (modules/graphs/GraphsUI.php:591-615)
Validates: if neither `wordVerifyID` is a valid ID nor `wordVerifyString` is set, calls `CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid word verification ID.')` (lines 593-597). If `wordVerifyID` set, instantiates `new Graphs()` and `$graphs->getVerificationImageText($wordVerifyID)` (lines 603-604); otherwise uses `$_GET['wordVerifyString']` directly (line 608). Builds `new WordVerify($text)` and `$graph->draw(); die();` (lines 611-614). **No hook fired.** This is the public CAPTCHA renderer used by `Graphs::verificationImage()` (lib/Graphs.php:226-259).

## lib/ dependencies

### `lib/Graphs.php` — graph-URL/HTML helper + CAPTCHA persistence
Class `Graphs` (lib/Graphs.php:43). Constructor sets `$this->_graphsEnabled` based on `function_exists('ImageCreateFromJpeg')` (lib/Graphs.php:48-58). Key members:
- `public static function getColorOptions()` (lib/Graphs.php:62) — returns a large name→RGB-triple map (e.g. `'DarkGreen' => array(0,128,0)`, lines 64-115). Called by `miniPlacementStatistics()` and `miniJobOrderPipeline()`.
- `public function activity($width, $height)` (lib/Graphs.php:119), `newCandidates(...)` (:130), `newJobOrders(...)` (:141), `newSubmissions(...)` (:152), `miniPipeline($width,$height,$params)` (:163), `miniJobOrderPipeline($width,$height,$params)` (:174) — each returns `''` if graphs disabled, else delegates to `_getGraphHTML(...)`.
- `private function _getGraphHTML($graphName, $width, $height, $params = array(), $borderStyle = "none")` (lib/Graphs.php:185) — builds the `<a><img src="...?m=graphs&a=<graphName>&width=&height=..." ...></a>` markup. The image `src` is `index.php?m=graphs&a=<action>&width=&height=` (lines 195-201) and the click opens a fullscreen window pointing at `?m=reports&a=graphView&theImage=<urlencoded image url>` (lines 210-222). (Note: helper name `miniPipeline` maps to `a=miniPipeline`, but the controller switch case is `miniPlacementStatistics`; see Unverified section.)
- `public function verificationImage()` (lib/Graphs.php:226) — generates a random 6-char string, `INSERT`s it into `word_verification`, and returns `<img src="...?m=graphs&a=wordVerify&wordVerifyID=...">` plus a hidden input.
- `public function getVerificationImageText($wordVerifyID)` (lib/Graphs.php:262) — `SELECT word FROM word_verification WHERE word_verification_id = ...`; called by `wordVerify()`.
- `public function clearVerificationImageText($wordVerifyID)` (lib/Graphs.php:289) — deletes the row.

### `lib/GraphGenerator.php` — graph-image renderer classes
Defines `GRAPH_TREND_LINES` = false (lib/GraphGenerator.php:33). Conditionally includes artichow classes only if `function_exists('ImageCreateFromJpeg')` (lib/GraphGenerator.php:36-45): `LinePlot`, `BarPlot`, `Label`, `BarPlotPipeline`, `BarPlotDashboard`, `AntiSpam`, `Pie`. Classes:
- `class GraphSimple` (lib/GraphGenerator.php:52); ctor `__construct($xLabels, $xValues, $color, $title, $width, $height)` (:59); `public function draw($format = false)` (:70) — dies if no GD, defaults to `IMG_PNG`, builds an artichow `BarPlot` (+ optional `LinePlot` trend line) and `$graph->draw()` (:117).
- `class GraphPie` (:126); ctor `__construct($xLabels, $xValues, $title, $width, $height)` (:132); `draw($format = false)` (:142) — `IMG_PNG` default, builds an artichow `Pie` with bottom legend.
- `class GraphComparisonChart` (:191); ctor `__construct($xLabels, $xValues, $colorArray, $title, $width, $height, $totalValue)` (:200); `draw($format = false)` (:212) — `IMG_PNG` default, uses `BarPlotPipeline`; ends with its own `die()` (:260).
- `class pipelineStatisticsGraph` (:270); ctor `__construct($xLabels, $xValues, $colorArray, $width, $height, $legend1, $legend2, $legend3, $view, $noData)` (:283); `draw($format = false)` (:299) — `IMG_PNG` default, uses `BarPlotDashboard`, three-entry legend; ends with `die()` (:356).
- `class jobOrderReportGraph` (:366); ctor `__construct($xLabels, $xValues, $colorArray, $title, $width, $height)` (:375); `draw($format = false)` (:387) — **default `IMG_JPEG`** (:397), uses `BarPlotPipeline`; ends with `die()` (:426).
- `class WordVerify` (:436); ctor `__construct($text)` (:441); `draw()` (:447) — wraps artichow `AntiSpam`, `setText(...)`, `draw()`.

### `lib/artichow/` — vendor charting library (not deep-dived)
The third-party Artichow GD-based charting library lives under `lib/artichow/` (e.g. `Graph.class.php`, `BarPlot.class.php`, `LinePlot.class.php`, `Pie.class.php`, `AntiSpam.class.php`, `BarPlotPipeline.class.php`, `BarPlotDashboard.class.php`). It provides the `Graph`, `BarPlot`, `LinePlot`, `Pie`, `AntiSpam`, color (`Color`, `LinearGradient`, named colors like `DarkGreen`/`Orange`/`White`), and font (`Tuffy`) primitives used by the `GraphGenerator.php` classes. The actual pixel rendering and image streaming (`setFormat(IMG_PNG/IMG_JPG)`, `draw()`) is handled inside this vendor lib and is out of scope here.

## Hooks fired

Hooks are invoked as `if (!eval(Hooks::get('<KEY>'))) return;`. `Hooks::get($hookName)` (lib/Hooks.php:52-72) returns concatenated PHP from `$_SESSION['hooks'][$hookName]` followed by `' return true;'` (or just `'return true;'` when no hooks registered) — so with no plugin registered these are no-ops returning true. Keys fired by this module:

| Hook key | Fired in | Cite |
|---|---|---|
| `GRAPH_TEST` | `testGraph()` | modules/graphs/GraphsUI.php:144 |
| `GRAPH_JOB_ORDER_REPORT` | `jobOrderReportGraph()` | modules/graphs/GraphsUI.php:185 |
| `GRAPH_WEEKLY_ACTIVITY` | `activity()` | modules/graphs/GraphsUI.php:242 |
| `GRAPH_NEW_CANDIDATES` | `newCandidates()` | modules/graphs/GraphsUI.php:297 |
| `GRAPH_NEW_JOB_ORDERS` | `newJobOrders()` | modules/graphs/GraphsUI.php:351 |
| `GRAPH_NEW_SUBMISSIONS` | `newSubmissions()` | modules/graphs/GraphsUI.php:585 |
| `GRAPH_GENERIC` | `generic()` | modules/graphs/GraphsUI.php:382 |
| `GRAPH_GENERIC_PIE` | `genericPie()` | modules/graphs/GraphsUI.php:398 |
| `GRAPH_MINI_PIPELINE` | `miniJobOrderPipeline()` | modules/graphs/GraphsUI.php:530 |

No hook is fired by `wordVerify()` or `miniPlacementStatistics()`.

## Consumption by the reports module

The reports module reaches graph images by URL, never by direct call:
- `?m=graphs&a=jobOrderReportGraph&data=...` (modules/reports/ReportsUI.php:526).
- `?m=graphs&a=generic&title=&labels=&data=&width=&height=` (modules/reports/ReportsUI.php:650, :673).
- `?m=graphs&a=genericPie&title=&labels=&data=&width=&height=&legendOffset=` (modules/reports/ReportsUI.php:695, :723).

The `graphView` action in reports (modules/reports/ReportsUI.php:205-220) just assigns `$_GET['theImage']` to the template and displays `./modules/reports/GraphView.tpl` — i.e. it renders a page whose `<img>` points back at a `?m=graphs&...` URL. `lib/Graphs.php::_getGraphHTML()` builds exactly that `graphView` popup link (lib/Graphs.php:210-222).

## Source evidence

- modules/graphs/GraphsUI.php — entire file read (1-632). Sole PHP file in `modules/graphs/`; no `.tpl` or `.js` files exist in the module (directory listing confirmed).
- lib/Graphs.php — entire file read (1-305).
- lib/GraphGenerator.php — entire file read (1-456).
- lib/Hooks.php:52-72 — `Hooks::get()` behavior.
- lib/UserInterface.php:50 (`$_authenticationRequired = true` default), :193-201 (`getAction()`), :318 (`isRequiredIDValid`), :374 (`getTrimmedInput`), :429 (`getUserAccessLevel`) — signatures confirmed; `getUserAccessLevel` is **not** referenced by the graphs module.
- modules/reports/ReportsUI.php:58, :205-220, :526, :650, :673, :695, :723 — graph URL consumers and `graphView`.
- modules/home/Home.tpl:66 — `miniPlacementStatistics` dashboard embed.
- modules/install/Schema.php:167-168 — dashboard_module rows referencing `index.php?m=graphs&a=activity`.
- lib/artichow/ directory listing — vendor charting classes present.

## Unverified / open questions

- **Naming mismatch between helper and controller.** `lib/Graphs.php` exposes helpers `miniPipeline()` (emits `a=miniPipeline`, lib/Graphs.php:170) and `miniJobOrderPipeline()` (`a=miniJobOrderPipeline`, lib/Graphs.php:181). The controller switch has cases `miniPlacementStatistics` and `miniJobOrderPipeline` (modules/graphs/GraphsUI.php:121,125) — there is **no** `miniPipeline` case. A request for `a=miniPipeline` would hit the tier-2 `default` and produce `COMMONERROR_BADFIELDS`. Whether `Graphs::miniPipeline()` is actually called anywhere was not traced.
- **`getColorOptions()` return unused.** In both `miniPlacementStatistics()` (line 445) and `miniJobOrderPipeline()` (line 515) the assigned `$colorOptions` is never read afterward; colors are built inline from named artichow color classes. Whether this is dead code or intentional was not verified.
- **MIME/Content-Type emission.** I asserted "image/png/jpeg" output based on `setFormat(IMG_PNG)`/`setFormat(IMG_JPG)` and `$graph->draw()`; the actual `header('Content-Type: ...')` call lives inside the artichow vendor lib (`lib/artichow/Graph.class.php` / `Image.class.php`), which per instructions was not deep-dived.
- **`legendOffset` param.** Reports passes `legendOffset` on `genericPie` URLs (ReportsUI.php:695,723) but `genericPie()` never reads it (modules/graphs/GraphsUI.php:388-402); `GraphPie::draw()` has a hardcoded `setPosition(NULL, 1.25)` with a `/*$this->legendOffset*/` comment (lib/GraphGenerator.php:177). Appears to be a dropped feature.
- **Fall-through for logged-out tier-2 requests.** When not logged in and `a` is a tier-2 action, `handleRequest()` returns with no output (no image, no error). Confirmed by structure (modules/graphs/GraphsUI.php:101-133); intended behavior not documented in source.
- **`$_GET['width']`/`['height']` validation.** Values are only bounded by an upper limit (`< 2000` / `< 1200`) and used loosely-typed; no lower bound or numeric check (modules/graphs/GraphsUI.php:53-69). Security implications not assessed here.

---

## ACL-SUMMARY

```
graphs.testGraph              => (none)
graphs.wordVerify             => (none)
graphs.jobOrderReportGraph    => (none)
graphs.generic                => (none)
graphs.genericPie             => (none)
graphs.activity               => (none, but requires isLoggedIn())
graphs.newCandidates          => (none, but requires isLoggedIn())
graphs.newJobOrders           => (none, but requires isLoggedIn())
graphs.newSubmissions         => (none, but requires isLoggedIn())
graphs.miniPlacementStatistics => (none, but requires isLoggedIn())
graphs.miniJobOrderPipeline   => (none, but requires isLoggedIn())
```
