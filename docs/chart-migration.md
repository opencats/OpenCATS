# Chart migration audit (before implementation)

Repository-wide searches covered graph URLs, class names, includes, wrappers,
JavaScript/CSS, templates, exports, schema migrations, hooks and library assets.
No AGENTS.md is present. Baseline: PHP 8.4.1 test application; Home's empty image
shows decorative bars and a NO DATA overlay, with tiny duplicate range labels.

| Consumer | Old endpoint / implementation | Information | Active? | Replacement |
| --- | --- | --- | --- | --- |
| Home Hiring Overview | miniPlacementStatistics / pipelineStatisticsGraph | Submitted, interviewing, placed status-change events in four periods | Yes | Grouped bars, period explanation, accessible four-row table |
| Job Order details | miniJobOrderPipeline / GraphComparisonChart | Total pipeline, contacted, replied, qualifying, submitted, interviewing, offered, candidate/client declined, placed; counts and percentages | Yes | Horizontal bars, full labels, counts and percentage table |
| Reports expanded pipeline | graphView, invoked by Graphs::_getGraphHTML | Same Job Order data at larger size; refresh every five minutes | Yes | Same shared component in standalone responsive page |
| EEO ethnicity | generic / GraphSimple | Counts by configured ethnic type | Yes | Horizontal bars; existing table retained |
| EEO veteran status | generic / GraphSimple | Counts by configured veteran type | Yes | Horizontal bars; existing table retained |
| EEO gender | genericPie / GraphPie | Recorded male/female counts and percentages | Yes | Horizontal comparison and exact values |
| EEO disability | genericPie / GraphPie | Recorded Yes/No disability counts and percentages | Yes | Horizontal comparison and exact values |
| Recruiting-summary PDF | jobOrderReportGraph | Screened, submitted, interviewed, placed editable report values | Yes | Compact labelled numeric summary; no duplicate image |
| Old dashboard widgets | activity, newCandidates, newJobOrders, newSubmissions / GraphSimple | Daily record counts for current and previous week | No | Remove |
| Unused wrapper | miniPipeline | Targets an action absent from GraphsUI | No | Remove |
| Developer graph | testGraph / GRAPH_TEST | Four test values | No | Remove |
| WebForm CAPTCHA | wordVerify / WordVerify / AntiSpam | Verification characters | No | Remove graph-specific branches |

## Data and contracts

- Home calls `Dashboard::getPipelineData(view)`: sums status-history events for
  SUBMITTED, INTERVIEWING, PLACED. Repeated transitions count repeatedly. Four
  calendar weeks/months/years, oldest first, including the current partial period;
  weekly boundaries respect CalendarSettings.firstDayMonday and labels respect
  date format. Database NOW determines periods. No new totals, rates or distinct
  candidate metrics. Login is required. Recent Hires and Important Candidates
  show records, but do not duplicate the four-period event series.
- Job Order calls `Statistics::getPipelineData(jobOrderID)`; existing query excludes
  Closed orders. Progression counts accumulate placed -> offered -> interviewing
  -> submitted -> qualifying -> replied -> contacted. Declines remain separate;
  total includes all statuses. Percentages are rounded to whole numbers relative
  to the total (legacy renderer increases denominator if a value exceeds it).
  Keep these as overlapping cumulative categories, never a stacked distribution.
  Joborders.show permission and valid ID guard the containing page. The old image
  endpoint required login; expanded Reports page also requires reports.show.
  The candidate pipeline table contains individual records, not these aggregates.
- EEO calls `Statistics::getEEOReport(period,status)` unchanged. `week` means
  candidate.date_modified within seven days; `month` within one month; `all` no
  date restriction. Status joins preserve existing placed/rejected semantics.
  reports.show and canSeeEEOInfo guard the report. Configured tracking settings
  determine visible panels (including the existing genderTracking check for
  disability). Ethnic/veteran tables already provide exact values. Unknown gender
  and disability values are not included in the two-category graphs. The former
  pie renderer also showed whole percentages of those recorded categories; these
  are retained in the tooltips and accessible tables.
- Recruiting summary uses `Statistics::getJobOrderReport`, then user-editable
  dataSet1..4 and comma-separated dataSet in the existing GET PDF request. Screened
  is pipeline membership; the other three count status-history transitions.
  Keep report parameters, notes, labels, PDF download and reports.show permission.
  Four existing text values convey everything in the duplicate graph; bring them
  into the space previously occupied by the image, without a server renderer.
- Keep active hooks: GRAPH_MINI_PIPELINE, GRAPH_GENERIC, GRAPH_GENERIC_PIE,
  GRAPH_JOB_ORDER_REPORT, REPORTS_GRAPH and report pre/post hooks. Rendering hooks
  receive structured chart data rather than obsolete Artichow objects.

## Dead-code evidence

`Graphs` widget methods have no callers other than the Job Order wrapper.
Historical dashboard_module migrations mention old widgets, but later migrations
at Schema.php remove that table. Current Dashboard has only placements/pipeline
business methods; no dynamic graph-widget registry remains. No candidate-module
chart caller exists. WebForm is only included by SettingsUI, never instantiated
or extended anywhere; WFT_ANTI_SPAM_IMAGE has no external references. Remove its
CAPTCHA cases, not the unrelated form library. Settings' Graphs include is unused.
`graphNoData.jpg` has no references. noDataByGender/Disability images are exclusively
EEO fallbacks. Dashboard preview assets have only obsolete migration references;
retain historical migrations and their preview assets as upgrade history.

## Presentation rationale

Home uses a compact grouped chart, because each period contains three distinct
counts worth comparing; lines could imply continuous measurements and a total
would wrongly add different status events. A four-row values disclosure gives
exact counts without requiring hover. No percentage-change KPI is introduced.
The current period is explicitly incomplete. Only the existing card changes.
Horizontal Job Order and EEO bars allow long category labels and accurate common-
baseline comparisons. Gender/disability move away from pies to make small and
zero counts readable. PDF uses the already-existing four values without a chart.

## Completed migration and validation

All active rows in the inventory above are migrated; all dead rows are removed.
`lib/Charts.php` supplies structured data and safely serialized, accessible markup;
`js/charts.js` is the single shared renderer. Home loads only the selected range,
using the authenticated `home.hiringOverview` fragment action when switching;
ordinary range links and visible value tables work without JavaScript. Rendering
failures preserve values. Empty/zero selections show a status rather than a canvas.
Print handling exposes the compact values disclosures. No SQL or business
calculations moved into JavaScript. `lib/Dashboard.php` and `lib/Statistics.php`
are unchanged. Active hook names remain, but extensions manipulating former graph
objects must adapt to the structured arrays documented in `Charts.php`.

Chart.js **4.5.1**, MIT, is bundled locally as the unmodified production minified
UMD asset. The official archive integrity, Chart.js notice and bundled
@kurkle/color notice were verified and retained; see `js/chartjs/README.md`.
No npm installation, build pipeline, CDN, framework or jQuery was introduced.
All existing copyright/licence comment blocks in modified files were compared
with the starting commit and preserved exactly.

### Focused automated results

- PHP 8.4.1 and PHP 8.5.10: ChartsTest, 6 tests / 354 assertions passed on each.
- Both PHP versions: ChartDataTest, 1 test / 35 assertions passed on each, using
  the existing isolated integration-test database harness. Covers exact period
  labels, repeated transitions, Monday boundaries, pipeline aggregation/closed
  orders, EEO filters and report values.
- Focused containing-page Behat selection: 14 scenarios / 94 steps passed,
  including populated JavaScript Home rendering and all three range switches,
  no-JavaScript range pages, pipeline/expanded view, EEO and PDF download.
- Chart permission selection: 12 scenarios / 36 steps passed.
- All 16 touched PHP/template files syntax-checked on both PHP versions;
  JavaScript syntax checks passed for charts.js and home.js.
- Working and staged whitespace checks passed. Repository-wide dependency search
  finds only this audit, removal assertions, a changelog entry and two historical
  installer migration URLs. Those migrations refer to the subsequently removed
  dashboard_module table; no live graph endpoint or Artichow dependency remains.
- Full PHP regression suites were deliberately left to the maintainer.

The integration harness emits existing duplicate-constant warnings (#877).
PHP 8.5 reports pre-existing noncanonical casts/case syntax (#879), including an
unchanged boolean cast in JobOrdersUI. No new chart-code PHP compatibility warning
was observed. These unrelated defects were not changed.

Focused commands (from the project root inside the test PHP environment):

```sh
./vendor/bin/phpunit --do-not-record-test-run-history src/OpenCATS/Tests/UnitTests/ChartsTest.php
./vendor/bin/phpunit --testsuite IntegrationTests --filter ChartDataTest --testdox
./vendor/bin/behat -c /tmp/behat-charts.yml --suite default --tags '@charts,@home_graph,@home_graph_render,@reports_graph,@reports_eeo,@reports_joborder' --format progress
./vendor/bin/behat -c /tmp/behat-charts.yml --suite security --tags @chart_permissions --format progress
```

The repository's old Selenium Chrome image cannot parse modern JavaScript.
Focused browser tests used temporary `selenium/standalone-chrome:3.141.59-20210929`
on the existing docker_default network. The temporary Behat config copied
`test/behat.yml`, changed wd_host to that container, and used absolute feature and
bootstrap autoload paths. Repository Docker/Behat configuration is unchanged.
The temporary browser container and visual fixtures were removed after review.

### Visual review

| Family | Representative populated review | Empty/zero review |
| --- | --- | --- |
| Home | Weekly/monthly/yearly; desktop half-width dashboard card and 390px mobile; legends, period labels, switching and values | Desktop/mobile status and retained zero-value periods |
| Job Order pipeline | All ten categories; desktop containing page and 390px; full labels, overlapping progression explanation, counts/percentages | Empty job order |
| Expanded pipeline | 768px and 390px; all ten categories and values | Empty selection |
| EEO ethnicity/veteran | Desktop report columns and 390px stacked view; long labels and existing tables | All categories zero |
| EEO gender/disability | Desktop report columns and 390px; counts and rounded percentages, including zero categories | Both categories zero |
| Recruiting PDF | Actual download with 9/7/3/1 and notes, text extraction and rendered-page inspection; all labels/values legible | Zero-value handling covered by existing numeric summary path |

Home uses its existing card space with legible grouped comparisons and accessible
exact values instead of the tiny generated image. Reports retain enough room for
all categories; responsive horizontal bars wrap labels without dropping them.
`test/scripts/chartVisualFixtures.php` provides guarded, removable cats_test-only
fixtures for repeating the manual review. Browser viewport overrides were reset.

### Separate findings

- New [#902](https://github.com/opencats/OpenCATS/issues/902): EEO status joins count
  a candidate once per matching membership. Preserved and documented by the test.
- New [#903](https://github.com/opencats/OpenCATS/issues/903): obsolete Selenium
  Chrome image cannot exercise modern frontend dependencies.
- Existing [#886](https://github.com/opencats/OpenCATS/issues/886): EEO focus-field
  and disability tracking visibility defects; left unchanged.
- Existing [#877](https://github.com/opencats/OpenCATS/issues/877) and
  [#879](https://github.com/opencats/OpenCATS/issues/879): harness/PHP warnings above.

### Branch and file manifest

Started from clean tracked Bootstrap work at
`feature/bootstrap-5.3` / `4da341a8025ca876f855bfd8b718e60b1226aa23`.
Work is isolated on `feature/chartjs-artichow-replacement`. Unrelated untracked
`reports/local/` and `test/runPhpMatrix.sh` are excluded. No Bootstrap branch
rewrite or push is part of this task.

Complete task file manifest (A added, M modified, D removed):

```text
D	images/graphNoData.jpg
D	images/noDataByDisability.png
D	images/noDataByGender.png
M	js/home.js
D	lib/GraphGenerator.php
D	lib/Graphs.php
M	lib/WebForm.php
D	lib/artichow/AntiSpam.class.php
D	lib/artichow/Artichow.cfg.php
D	lib/artichow/Artichow.class.php
D	lib/artichow/BarPlot.class.php
D	lib/artichow/BarPlotDashboard.class.php
D	lib/artichow/BarPlotPipeline.class.php
D	lib/artichow/Component.class.php
D	lib/artichow/Graph.class.php
D	lib/artichow/Image.class.php
D	lib/artichow/LinePlot.class.php
D	lib/artichow/MathPlot.class.php
D	lib/artichow/Pattern.class.php
D	lib/artichow/Pie.class.php
D	lib/artichow/Plot.class.php
D	lib/artichow/ScatterPlot.class.php
D	lib/artichow/common.php
D	lib/artichow/font/Tuffy.ttf
D	lib/artichow/font/TuffyBold.ttf
D	lib/artichow/font/TuffyBoldItalic.ttf
D	lib/artichow/font/TuffyItalic.ttf
D	lib/artichow/images/book.png
D	lib/artichow/images/paperclip.png
D	lib/artichow/images/star.png
D	lib/artichow/inc/Axis.class.php
D	lib/artichow/inc/Border.class.php
D	lib/artichow/inc/Color.class.php
D	lib/artichow/inc/Drawer.class.php
D	lib/artichow/inc/Font.class.php
D	lib/artichow/inc/Gradient.class.php
D	lib/artichow/inc/Grid.class.php
D	lib/artichow/inc/Label.class.php
D	lib/artichow/inc/Legend.class.php
D	lib/artichow/inc/Mark.class.php
D	lib/artichow/inc/Math.class.php
D	lib/artichow/inc/Shadow.class.php
D	lib/artichow/inc/Text.class.php
D	lib/artichow/inc/Tick.class.php
D	lib/artichow/inc/Tools.class.php
D	lib/artichow/patterns/BarDepth.php
D	lib/artichow/patterns/LightLine.php
M	main.css
D	modules/graphs/GraphsUI.php
M	modules/home/Home.tpl
M	modules/home/HomeUI.php
M	modules/joborders/JobOrdersUI.php
M	modules/joborders/Show.tpl
M	modules/reports/EEOReport.tpl
M	modules/reports/GraphView.tpl
M	modules/reports/ReportsUI.php
M	modules/settings/SettingsUI.php
M	scripts/countcode.sh
M	test/features/bootstrap/FeatureContext.php
M	test/features/bootstrap/HomeSteps.php
M	test/features/home.feature
M	test/features/reports.feature
A	docs/chart-migration.md
A	js/chartjs/COLOR-LICENSE.md
A	js/chartjs/LICENSE.md
A	js/chartjs/README.md
A	js/chartjs/chart.umd.min.js
A	js/charts.js
A	lib/Charts.php
A	src/OpenCATS/Tests/IntegrationTests/ChartDataTest.php
A	src/OpenCATS/Tests/UnitTests/ChartsTest.php
A	test/features/bootstrap/ChartsSteps.php
A	test/features/chart_permissions.feature
A	test/features/charts.feature
A	test/scripts/chartVisualFixtures.php
```
