# Module: reports

## Overview

The reports module is implemented by a single controller class declared as
`class ReportsUI extends UserInterface` (modules/reports/ReportsUI.php:35). At
file scope it pulls in its data/lib dependencies:
`include_once(LEGACY_ROOT . '/lib/Statistics.php')`,
`/lib/DateUtility.php`, `/lib/Candidates.php`, and `/lib/CommonErrors.php`
(ReportsUI.php:30-33).

The constructor `public function __construct()` (ReportsUI.php:37) calls
`parent::__construct()` then sets:
- `$this->_authenticationRequired = true;` (ReportsUI.php:41)
- `$this->_moduleDirectory = 'reports';` (ReportsUI.php:42)
- `$this->_moduleName = 'reports';` (ReportsUI.php:43)
- `$this->_moduleTabText = 'Reports';` (ReportsUI.php:44)
- `$this->_subTabs` with a single entry, `'EEO Reports' =>
  CATSUtility::getIndexName() . '?m=reports&amp;a=customizeEEOReport'`
  (ReportsUI.php:45-47).

Request dispatch is `public function handleRequest()` (ReportsUI.php:51), which
first runs `if (!eval(Hooks::get('REPORTS_HANDLE_REQUEST'))) return;`
(ReportsUI.php:53), then `switch ($action)` on `$this->getAction()`
(ReportsUI.php:55-56).

Reports that exist (one per private handler / switch case):
1. **Statistics dashboard** — `reports()` (default action), counts by entity and
   time period (ReportsUI.php:127-203).
2. **Submission report** — `showSubmissionReport()`, submissions grouped by job
   order for a time period (ReportsUI.php:222-300).
3. **Placement report** — `showPlacementReport()`, placements grouped by job
   order for a time period (ReportsUI.php:302-380).
4. **Job order report (customize form)** — `customizeJobOrderReport()`, an
   editable form pre-filled from pipeline statistics (ReportsUI.php:382-432).
5. **Job order report (PDF output)** — `generateJobOrderReportPDF()`, an FPDF
   "Recruiting Summary Report" (ReportsUI.php:443-595).
6. **EEO report form** — `customizeEEOReport()`, blank EEO criteria form
   (ReportsUI.php:434-441).
7. **EEO report preview** — `generateEEOReportPreview()`, EEO statistics with
   graphs (ReportsUI.php:597-752).
8. **Graph view (kiosk)** — `graphView()`, a full-screen auto-refreshing single
   image page (ReportsUI.php:205-220).

## Action catalog

Every guard uses the identical pattern `if
($this->getUserAccessLevel('reports.show') < ACCESS_LEVEL_READ) { ... }` unless
noted. `ACCESS_LEVEL_READ` is defined as `100` (constants.php:76).
`getUserAccessLevel` is `protected function getUserAccessLevel($securedObjectName)`
returning `$_SESSION['CATS']->getAccessLevel($securedObjectName)`
(lib/UserInterface.php:429-432).

| Action (`a=`) | Exact ACL guard | Required level | Handler | lib calls | Template |
|---|---|---|---|---|---|
| `graphView` | `getUserAccessLevel('reports.show') < ACCESS_LEVEL_READ` (ReportsUI.php:59) | ACCESS_LEVEL_READ | `graphView()` (ReportsUI.php:205) | none (reads `$_GET['theImage']`) | GraphView.tpl (ReportsUI.php:219) |
| `generateJobOrderReportPDF` | `getUserAccessLevel('reports.show') < ACCESS_LEVEL_READ` (ReportsUI.php:67) | ACCESS_LEVEL_READ | `generateJobOrderReportPDF()` (ReportsUI.php:443) | FPDF (`vendor/autoload.php`), `DateUtility::getAdjustedDate`, `CATSUtility::getAbsoluteURI` / `getIndexName` (ReportsUI.php:445,495,524) | none — `$pdf->Output(); die();` (ReportsUI.php:593-594) |
| `showSubmissionReport` | `getUserAccessLevel('reports.show') < ACCESS_LEVEL_READ` (ReportsUI.php:75) | ACCESS_LEVEL_READ | `showSubmissionReport()` (ReportsUI.php:222) | `Statistics::getSubmissionJobOrders`, `Statistics::getSubmissionsByJobOrder` (ReportsUI.php:285,290) | SubmissionReport.tpl (ReportsUI.php:299) |
| `showPlacementReport` | `getUserAccessLevel('reports.show') < ACCESS_LEVEL_READ` (ReportsUI.php:83) | ACCESS_LEVEL_READ | `showPlacementReport()` (ReportsUI.php:302) | `Statistics::getPlacementsJobOrders`, `Statistics::getPlacementsByJobOrder` (ReportsUI.php:365,370) | PlacedReport.tpl (ReportsUI.php:379) |
| `customizeJobOrderReport` | `getUserAccessLevel('reports.show') < ACCESS_LEVEL_READ` (ReportsUI.php:91) | ACCESS_LEVEL_READ | `customizeJobOrderReport()` (ReportsUI.php:382) | `Statistics::getJobOrderReport`, `DateUtility::getAdjustedDate` (ReportsUI.php:396,413) | JobOrderReport.tpl (ReportsUI.php:431) |
| `customizeEEOReport` | `getUserAccessLevel('reports.show') < ACCESS_LEVEL_READ \|\| !$_SESSION['CATS']->canSeeEEOInfo()` (ReportsUI.php:99-100) | ACCESS_LEVEL_READ **and** `canSeeEEOInfo()` | `customizeEEOReport()` (ReportsUI.php:434) | none | EEOReport.tpl (ReportsUI.php:440) |
| `generateEEOReportPreview` | `getUserAccessLevel('reports.show') < ACCESS_LEVEL_READ \|\| !$_SESSION['CATS']->canSeeEEOInfo()` (ReportsUI.php:108-109) | ACCESS_LEVEL_READ **and** `canSeeEEOInfo()` | `generateEEOReportPreview()` (ReportsUI.php:597) | `Statistics::getEEOReport`, `EEOSettings::getAll`, `CATSUtility::getAbsoluteURI` (ReportsUI.php:603,739,649) | EEOReport.tpl (ReportsUI.php:751) |
| `reports` / *default* | `getUserAccessLevel('reports.show') < ACCESS_LEVEL_READ` (ReportsUI.php:118) | ACCESS_LEVEL_READ | `reports()` (ReportsUI.php:127) | `Statistics::getCompanyCount`/`getCandidateCount`/`getSubmissionCount`/`getPlacementCount`/`getContactCount`/`getJobOrderCount` (ReportsUI.php:133-196) | Reports.tpl (ReportsUI.php:202) |

On a failed guard every case calls `CommonErrors::fatal(COMMONERROR_PERMISSION,
$this, 'Invalid user level for action.')` (e.g. ReportsUI.php:61).

## Per-action detail

### reports() — statistics dashboard (ReportsUI.php:127-203)
Instantiates `$statistics = new Statistics($this->_siteID)`
(ReportsUI.php:130) and builds the `$statisticsData` array by calling each count
method nine times with the `TIME_PERIOD_*` flags (TODATE, TODAY, YESTERDAY,
THISWEEK, LASTWEEK, THISMONTH, LASTMONTH, THISYEAR, LASTYEAR). The entity groups
are companies (ReportsUI.php:133-141), candidates (144-152), submissions
(155-163), placements (166-174), contacts (177-185), and job orders (188-196).
Fires `REPORTS_SHOW` (ReportsUI.php:198), then assigns `active` and
`statisticsData` and displays Reports.tpl (ReportsUI.php:200-202).

### graphView() — kiosk graph (ReportsUI.php:205-220)
Reads `$_GET['theImage']` (defaulting to `''`) into the `theImage` template var
(ReportsUI.php:207-214), fires `REPORTS_GRAPH` (ReportsUI.php:216), assigns
`active`, and displays GraphView.tpl. No ACL beyond the switch guard and no
Statistics calls; the image URL is supplied by the caller via the query string.

### showSubmissionReport() / showPlacementReport() (ReportsUI.php:222-380)
Both read `$_GET['period']` (with a `//FIXME: getTrimmedInput` note,
ReportsUI.php:224,304) and map the string to a `TIME_PERIOD_*` constant plus a
human `$reportTitle` via a `switch` (default = `today` →
`TIME_PERIOD_TODAY`, ReportsUI.php:235-282, 315-362). Submission flow calls
`getSubmissionJobOrders($period)` (ReportsUI.php:285) then, in a loop, attaches
`['submissionsRS']` from `getSubmissionsByJobOrder($period,
$submissionJobOrdersData['jobOrderID'], $this->_siteID)` (ReportsUI.php:290-292)
— note the call passes three args while the method signature accepts two (see
lib/ dependencies). The placement flow is structurally identical with
`getPlacementsJobOrders` / `getPlacementsByJobOrder` (ReportsUI.php:365-372).
**Both** handlers fire the hook `REPORTS_SHOW_SUBMISSION`
(ReportsUI.php:295 and ReportsUI.php:375 — the placement handler reuses the
submission hook key). Submission displays SubmissionReport.tpl; placement
displays PlacedReport.tpl (ReportsUI.php:299,379).

### customizeJobOrderReport() (ReportsUI.php:382-432)
Validates `jobOrderID` via `$this->isRequiredIDValid('jobOrderID', $_GET)`,
else `CommonErrors::fatal(COMMONERROR_BADINDEX, ...)` (ReportsUI.php:385-388).
Fetches `getJobOrderReport($jobOrderID)` (ReportsUI.php:396); empty result →
`COMMONERROR_BADINDEX` "could not be found" (ReportsUI.php:399-402). Maps result
fields into `$reportParameters` (siteName from
`$_SESSION['CATS']->getSiteName()`, companyName, jobOrderName=title,
accountManager=ownerFullName, recruiter=recruiterFullName,
ReportsUI.php:404-408). `periodLine` is `sprintf('%s - %s',
strtok($data['dateCreated'], ' '), DateUtility::getAdjustedDate('m-d-y'))`
(ReportsUI.php:410-414). dataSet1..4 = pipeline / submitted / pipelineInterving
/ pipelinePlaced (ReportsUI.php:416-419). Displays JobOrderReport.tpl
(ReportsUI.php:431). (A local `$dataSet` array is built at ReportsUI.php:421-426
but is not assigned to the template.)

### generateJobOrderReportPDF() (ReportsUI.php:443-595)
Includes `vendor/autoload.php` for FPDF (ReportsUI.php:445). Reads
`isASP`/`unixName` from the session (ReportsUI.php:448-450) and seven
`getTrimmedInput(... , $_GET)` fields: siteName, companyName, jobOrderName,
periodLine, accountManager, recruiter, notes (ReportsUI.php:452-458). `dataSet`
comes from `explode(',', $_GET['dataSet'])`, defaulting to `array(4,3,2,1)`
(ReportsUI.php:460-468). Font is hardcoded `'Arial'` (ReportsUI.php:473). Fires
`REPORTS_CUSTOMIZE_JO_REPORT_PRE` after the first page is added
(ReportsUI.php:478). A special-case logo block runs only when `$isASP &&
$unixName == 'cognizo'` (ReportsUI.php:480-487). The pie graph image is pulled
from the graphs module:
`CATSUtility::getIndexName() . '?m=graphs&a=jobOrderReportGraph&data=' .
urlencode(implode(',', $dataSet))` wrapped in `CATSUtility::getAbsoluteURI`
(ReportsUI.php:524-528), embedded via `$pdf->Image(...)` (ReportsUI.php:530).
Colored "Screened/Submitted/Interviewed/Placed" lines and the dataSet values are
written (ReportsUI.php:532-587), a border `Rect` drawn (ReportsUI.php:589),
then `REPORTS_CUSTOMIZE_JO_REPORT_POST` fires (ReportsUI.php:591) and the PDF is
sent with `$pdf->Output(); die();` (ReportsUI.php:593-594). Code comments flag
the graph-fetch as unauthenticated and possibly triggering an FPDF "could not
make seekable" warning (ReportsUI.php:517-523).

### customizeEEOReport() (ReportsUI.php:434-441)
Assigns `modePeriod = 'all'`, `modeStatus = 'all'`, `active`, empty `subActive`,
and displays EEOReport.tpl (ReportsUI.php:436-440). Guarded additionally by
`canSeeEEOInfo()` (see catalog).

### generateEEOReportPreview() (ReportsUI.php:597-752)
Reads `period` and `status` via `getTrimmedInput(... , $_GET)`
(ReportsUI.php:599-600), calls `getEEOReport($modePeriod, $modeStatus)`
(ReportsUI.php:603). Builds `$labelPeriod` (week/month → " Last Week"/" Last
Month", ReportsUI.php:607-620) and `$labelStatus` (rejected/placed →
" Rejected"/" Placed", ReportsUI.php:622-635). Builds four graph URLs against the
**graphs** module:
- Ethnic: `?m=graphs&a=generic` bar graph, width 400 height 240
  (ReportsUI.php:649-657).
- Veteran: `?m=graphs&a=generic`, 400x240 (ReportsUI.php:672-680).
- Gender: `?m=graphs&a=genericPie`, 320x300, `legendOffset=1.575`; falls back to
  `images/noDataByGender.png` when both male and female counts are 0
  (ReportsUI.php:694-708).
- Disability: `?m=graphs&a=genericPie`, 320x300; falls back to
  `images/noDataByDisability.png` when both disabled/non-disabled are 0
  (ReportsUI.php:722-736).

Then `$EEOSettings = new EEOSettings($this->_siteID); $EEOSettingsRS =
$EEOSettings->getAll();` (ReportsUI.php:738-739) and assigns
`EEOReportStatistics`, the four graph URLs, `modePeriod`, `modeStatus`,
`EEOSettingsRS`, `active`, `subActive` before displaying EEOReport.tpl
(ReportsUI.php:741-751). This handler fires no hook.

## Templates

- **Reports.tpl** (modules/reports/Reports.tpl) — the dashboard. Standard
  chrome via `TemplateUtility::printHeader('Reports')`, `printHeaderBlock`,
  `printTabs`, `printQuickSearch` (Reports.tpl:2-6). Nine `statisticsTable`
  blocks (Today, Yesterday, This/Last Week, This/Last Month, This/Last Year, To
  Date) print `$this->statisticsData[...]` values (e.g. Reports.tpl:31,35).
  "New Submissions" and "New Placements" cells are hyperlinks to
  `a=showSubmissionReport&period=...` and `a=showPlacementReport&period=...`
  with `target="_blank"` (Reports.tpl:44,51,84,91, ...).
- **SubmissionReport.tpl** — title from `$this->reportTitle`, iterates
  `$this->submissionJobOrdersRS`; for each job order prints title/company/owner
  then a `sortable` table of submission rows (firstName, lastName,
  ownerFullName, dateSubmitted) from `submissionsRS`
  (SubmissionReport.tpl:15-35). Closes with
  `TemplateUtility::printReportFooter()` (SubmissionReport.tpl:36).
- **PlacedReport.tpl** — mirror of SubmissionReport.tpl over
  `$this->placementsJobOrdersRS` / `placementsRS`; the "Date Placed" column
  actually renders `$placementsData['dateSubmitted']` (PlacedReport.tpl:23,31).
- **JobOrderReport.tpl** — GET form posting to `m=reports`,
  `a=generateJobOrderReportPDF` (JobOrderReport.tpl:20-22). Text inputs bound to
  `$this->reportParameters[...]` (siteName, companyName, jobOrderName,
  periodLine, accountManager, recruiter, JobOrderReport.tpl:30-75) plus four
  dataSet inputs and a notes textarea. A hidden `dataSet` field is populated by
  inline JS `setDataSet()` (see JavaScript). Loads
  `modules/joborders/validator.js`, `js/company.js`, `js/sweetTitles.js` via
  `printHeader` (JobOrderReport.tpl:2).
- **EEOReport.tpl** — GET form posting to `m=reports`,
  `a=generateEEOReportPreview` (EEOReport.tpl:20-22). Radio groups `period`
  (all/month/week) and `status` (all/placed/rejected), with `checked` driven by
  `$this->modePeriod` / `$this->modeStatus` (EEOReport.tpl:33-46). When
  `$this->EEOReportStatistics` is set it renders preview graphs gated on
  `$this->EEOSettingsRS['ethnicTracking'|'veteranTracking'|'genderTracking']`
  using `urlEthnicGraph`, `urlVeteranGraph`, `urlGenderGraph`,
  `urlDisabilityGraph` (EEOReport.tpl:53-153). Same JS includes as
  JobOrderReport.tpl (EEOReport.tpl:2).
- **GraphView.tpl** — standalone full HTML page (not the standard chrome) that
  shows `$this->theImage` centered, with inline JS reloading every 5 minutes
  (GraphView.tpl:33,37-46).
- **Error.tpl** — fatal-error page printing `$this->errorMessage` inside
  standard chrome (Error.tpl:19-23).
- **NewDataItems.tpl** — present in the directory
  (modules/reports/NewDataItems.tpl:1) but **not referenced** by any
  `display()` call in ReportsUI.php; it is an orphan template within this
  module.

## JavaScript

There are no `.js` files in modules/reports/. JavaScript is inline in templates
or pulled from other modules:

- **JobOrderReport.tpl** inline `setDataSet()` concatenates the four dataSet
  field values into the hidden `dataSet` input
  (`document.getElementById('dataSet').value = ... dataSet1 + ',' + ...`,
  JobOrderReport.tpl:82-91); it is wired to each input's `onchange`
  (JobOrderReport.tpl:98,107,116,125) and invoked once at load
  (`<script>setDataSet();</script>`, JobOrderReport.tpl:130). A final inline
  script focuses the siteName field (JobOrderReport.tpl:150-152).
- **EEOReport.tpl** inline script focuses the siteName field
  (EEOReport.tpl:158-160).
- **GraphView.tpl** inline script schedules a 5-minute reload
  (`setTimeout('location.reload(true)', 1000 * 60 * 5)`, GraphView.tpl:37-46).
- External JS loaded by both form templates via `printHeader`:
  `modules/joborders/validator.js`, `js/company.js`, `js/sweetTitles.js`
  (JobOrderReport.tpl:2, EEOReport.tpl:2).

## lib/ dependencies (cited)

**lib/Statistics.php** — `class Statistics` (lib/Statistics.php:41),
constructor `public function __construct($siteID)` stores siteID, the DB
singleton, and `$_timeZoneOffset` from `$_SESSION['CATS']->getTimeZoneOffset()`
(lib/Statistics.php:48-55). Methods used by this module:
- `public function getCandidateCount($period)` (lib/Statistics.php:64)
- `public function getSubmissionCount($period)` (lib/Statistics.php:90)
- `public function getPlacementCount($period)` (lib/Statistics.php:123)
- `public function getCompanyCount($period)` (lib/Statistics.php:151)
- `public function getContactCount($period)` (lib/Statistics.php:177)
- `public function getJobOrderCount($period)` (lib/Statistics.php:203)
- `public function getSubmissionJobOrders($period)` (lib/Statistics.php:229) —
  returns `getAllAssoc` rows; filters `joborder.status IN
  JobOrderStatuses::getStatisticsStatusSQL()` and `HAVING submittedCount > 0`
  (lib/Statistics.php:257,262-263,269).
- `public function getSubmissionsByJobOrder($period, $jobOrderID)`
  (lib/Statistics.php:279) — **two-parameter** signature; the controller calls
  it with three arguments (`$period, jobOrderID, $this->_siteID`,
  ReportsUI.php:290-292), so the extra `$this->_siteID` argument is ignored.
- `public function getPlacementsJobOrders($period)` (lib/Statistics.php:339)
- `public function getPlacementsByJobOrder($period, $jobOrderID)`
  (lib/Statistics.php:389) — also two-parameter, called with three
  (ReportsUI.php:370-372).
- `public function getJobOrderReport($jobOrderID)` (lib/Statistics.php:601) —
  single GROUP BY query returning pipeline/submitted/pipelinePlaced/
  pipelineInterving via correlated subqueries keyed on
  `PIPELINE_STATUS_SUBMITTED`/`_PLACED`/`_INTERVIEWING`
  (lib/Statistics.php:679,682,685); returns `getAssoc` (lib/Statistics.php:691).
- `public function getEEOReport($modePeriod, $modeStatus)`
  (lib/Statistics.php:694) — branches on period (`month`/`week` →
  date_modified intervals, lib/Statistics.php:698-704) and status
  (`placed` → `status >= PIPELINE_STATUS_PLACED`; `rejected` → `status =
  PIPELINE_STATUS_NOTINCONSIDERATION`, lib/Statistics.php:713-729).

**lib/DateUtility.php** — `DateUtility::getAdjustedDate(...)` used for the PDF
date and the job-order periodLine (ReportsUI.php:413,495).

**lib/CommonErrors.php** — `CommonErrors::fatal(COMMONERROR_PERMISSION|
COMMONERROR_BADINDEX, $this, msg)` for guard and validation failures
(ReportsUI.php:61,387,401).

**lib/Graphs.php / lib/GraphGenerator.php** — Not called directly from
ReportsUI.php. The report graphs are rendered indirectly through the **graphs**
module by URL (`?m=graphs&a=jobOrderReportGraph|generic|genericPie`,
ReportsUI.php:524-528,649-657,694-703). Those actions exist in the graphs
controller (`case 'jobOrderReportGraph'` GraphsUI.php:88, `case 'generic'`
GraphsUI.php:92, `case 'genericPie'` GraphsUI.php:96; handlers
`private function jobOrderReportGraph()` GraphsUI.php:150, `private function
generic()` GraphsUI.php:357, `private function genericPie()` GraphsUI.php:388).
For reference, `class Graphs` (lib/Graphs.php:43) exposes graph helpers such as
`public function activity($width, $height)` (lib/Graphs.php:119) and
`private function _getGraphHTML($graphName, $width, $height, $params = array(),
$borderStyle = "none")` (lib/Graphs.php:185); `lib/GraphGenerator.php` defines
the drawing classes with `public function draw($format = false)` entry points
(e.g. lib/GraphGenerator.php:70,142,212).

**lib/EEOSettings (EEOSettings class)** — `new EEOSettings($this->_siteID)` then
`->getAll()` for the preview (ReportsUI.php:738-739). The include for
`lib/Candidates.php` is present at the top of the controller
(ReportsUI.php:32) though Candidates is not referenced in the report handlers.

## Hooks fired (keys + cites)

| Hook key | Where fired |
|---|---|
| `REPORTS_HANDLE_REQUEST` | start of `handleRequest()` (ReportsUI.php:53) |
| `REPORTS_SHOW` | end of `reports()` before display (ReportsUI.php:198) |
| `REPORTS_GRAPH` | in `graphView()` before display (ReportsUI.php:216) |
| `REPORTS_SHOW_SUBMISSION` | in `showSubmissionReport()` (ReportsUI.php:295) **and** in `showPlacementReport()` (ReportsUI.php:375) |
| `REPORTS_CUSTOMIZE_JO_REPORT_PRE` | in `generateJobOrderReportPDF()` after first page (ReportsUI.php:478) |
| `REPORTS_CUSTOMIZE_JO_REPORT_POST` | in `generateJobOrderReportPDF()` before `Output()` (ReportsUI.php:591) |

All are invoked via the `if (!eval(Hooks::get('<KEY>'))) return;` idiom.
`Hooks::get` is `public static function get($hookName)` which returns the
concatenated hook commands plus `' return true;'`, or just `'return true;'` when
`$_SESSION['hooks']` is unset (lib/Hooks.php:52-72). No `REPORTS_*` keys are
predefined in lib/Hooks.php; they are resolved dynamically from session-loaded
modules. No `GRAPH_*` hook is fired from within this module (graph hooks, if
any, would live in the graphs module).

## Source evidence

- modules/reports/ReportsUI.php (read in full, 755 lines)
- modules/reports/Reports.tpl, SubmissionReport.tpl, PlacedReport.tpl,
  JobOrderReport.tpl, EEOReport.tpl, GraphView.tpl, Error.tpl, NewDataItems.tpl
  (all read)
- lib/Statistics.php:41-55, 64-270, 601-735 (class, ctor, cited methods)
- lib/UserInterface.php:429-432 (`getUserAccessLevel`)
- lib/Session.php:416-419 (`canSeeEEOInfo`)
- lib/Hooks.php:52-72 (`Hooks::get`)
- constants.php:74-117 (`ACCESS_LEVEL_*`, `TIME_PERIOD_*`)
- lib/Graphs.php:43,119,185 and lib/GraphGenerator.php:70,142,212 (referenced
  graph rendering classes)
- modules/graphs/GraphsUI.php:88,92,96,150,357,388 (graph actions reached by
  report URLs)

## Unverified / open questions

- **ajax/getReportHTML.php is a 0-byte empty file** and is not referenced
  anywhere in the codebase (grep for `getReportHTML` returns no PHP/JS callers).
  Its intended relationship to the reports module is unverified.
- **NewDataItems.tpl** is unreferenced by ReportsUI.php; whether it is dead code
  or reached by another module was not verified.
- The `reports.show` secured-object → required access level wiring (how
  `getAccessLevel('reports.show')` resolves to a numeric level per user) lives in
  the ACL/Session layer; I confirmed the constant value (100) and the guard
  comparison but did not trace the full `reports.show` ACL entry definition.
- `getSubmissionsByJobOrder`/`getPlacementsByJobOrder` are called with a third
  `$this->_siteID` argument that the two-parameter signatures ignore
  (ReportsUI.php:290-292, 370-372 vs lib/Statistics.php:279,389). The effective
  site scoping of those queries was not traced.
- `showPlacementReport()` fires `REPORTS_SHOW_SUBMISSION` rather than a
  placement-specific hook (ReportsUI.php:375); whether this is intentional was
  not determined.
- The `vendor/autoload.php` FPDF availability and the exact FPDF version are
  assumed from the `new FPDF()` usage (ReportsUI.php:445,475); not verified
  against composer config.
