<?php
/* CLI-only component fixture. Real templates/assets, prepared synthetic warning state; no database access. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
chdir(dirname(__DIR__, 2));
define('LEGACY_ROOT', '.');
require 'constants.php';
require 'lib/CATSUtility.php';
require 'lib/Template.php';
require 'lib/TemplateUtility.php';
require_once 'lib/Hooks.php';
require_once 'lib/ModuleUtility.php';
require_once 'lib/ACL.php';
require 'lib/WarningReviews.php';
require 'lib/UserInterface.php';
require 'modules/home/HomeUI.php';
require_once 'lib/Pager.php';
require_once 'lib/MRU.php';
$_SESSION['CATS'] = new class {
    public function getTimeZoneOffset() { return 0; }
    public function isDateDMY() { return true; }
    public function isTimeFormat24() { return true; }
    public function getCSRFToken() { return 'fixture-token'; }
    public function getAccessLevel($key) { return ACCESS_LEVEL_SA; }
    public function getUserID() { return 10; }
    public function getRealAccessLevel() { return ACCESS_LEVEL_SA; }
    public function getSiteName() { return 'Recruitment · synthetic fixture'; }
    public function isLoggedIn() { return true; }
    public function isHrMode() { return false; }
    public function accountActive() { return true; }
    public function getCachedBuild() { return 0; }
    public function getExecutionTime() { return 'fixture'; }
    public function getMRU() { return new MRU(10); }
};
require_once 'lib/DatabaseConnection.php';
$fixtureDatabase = new class extends DatabaseConnection
{
    public function __construct() {}
    public function connect() { throw new RuntimeException('Fixture connections are forbidden.'); }
    public function query($sql, $ignoreErrors = false) { throw new RuntimeException('Fixture writes are forbidden.'); }
    public function getAllAssoc($sql = null)
    {
        if (!preg_match('/FROM\s+mru/', $sql)) throw new RuntimeException('Unexpected fixture collection read.');
        return array(
            array('dataItemText' => 'Morgan & Co', 'URL' => 'index.php?m=companies&amp;a=show&amp;companyID=7'),
            array('dataItemText' => 'Alex Taylor', 'URL' => 'index.php?m=contacts&amp;a=show&amp;contactID=12'),
            array('dataItemText' => 'Production Engineer', 'URL' => 'index.php?m=joborders&amp;a=show&amp;jobOrderID=7'),
            array('dataItemText' => 'Priya Shah', 'URL' => 'index.php?m=candidates&amp;a=show&amp;candidateID=9'),
            array('dataItemText' => 'Westhaven Manufacturing', 'URL' => 'index.php?m=companies&amp;a=show&amp;companyID=8')
        );
    }
    public function getAssoc($sql = null)
    {
        if (!preg_match('/FROM\s+`system`/', $sql)) throw new RuntimeException('Unexpected fixture database read.');
        return array();
    }
};
(new ReflectionProperty(DatabaseConnection::class, '_instance'))->setValue(null, $fixtureDatabase);
$kind = $argv[1] ?? 'home';
if (!in_array($kind, array('home', 'companies', 'joborders', 'homeActivity'), true)) throw new InvalidArgumentException('Invalid fixture.');
$result = array('warnings' => array(), 'visibleWarnings' => array(), 'reviews' => array(), 'suppressedWarnings' => array(),
    'counts' => array('W01' => 1, 'W02' => 1, 'W03' => 1, 'W07' => 1, 'W08' => 1),
    'visibleCounts' => array('W01' => 1, 'W02' => 1, 'W03' => 0, 'W07' => 1, 'W08' => 1),
    'suppressedReasonCount' => 2, 'jobsAtRisk' => 1);
foreach (array('W01' => 'overdue_commitment', 'W02' => 'no_recorded_submission', 'W03' => 'no_recorded_candidate_progress',
    'W07' => 'no_next_action', 'W08' => 'invalid_owner') as $code => $reason)
{
    $type = $code === 'W07' ? DATA_ITEM_COMPANY : ($code === 'W01' ? DATA_ITEM_TASK : DATA_ITEM_JOBORDER);
    $row = array('code' => $code, 'entityType' => $type, 'entityID' => 7, 'title' => 'Example <script>unsafe</script> & Co',
        'reason' => $reason, 'deadline' => '2026-10-01 09:00:00', 'coverage' => 'uncovered');
    $reasons = $code === 'W07' ? array('no_next_action', 'stale_relationship') : array($reason);
    if ($code === 'W07') $row['relationshipAttention'] = array('reasons' => $reasons, 'tasks' => array());
    $result['warnings'][] = $row;
    if ($code !== 'W03')
    {
        $visible = $row;
        if ($code === 'W07') $visible['relationshipAttention']['reasons'] = array('no_next_action');
        $result['visibleWarnings'][] = $visible;
    }
    if (!in_array($code, array('W02', 'W03', 'W07'), true)) continue;
    foreach ($reasons as $reason)
    {
        $suppressed = $code === 'W03' || $reason === 'stale_relationship';
        $review = array('identity' => WarningReviews::identity($code, $type, 7, $reason), 'version' => 3,
            'evidence' => str_repeat('a', 64), 'canReview' => true, 'suppressed' => $suppressed,
            'status' => $suppressed ? 'Remind later' : 'Active warning',
            'state' => array('action' => 'remind_later', 'review_until' => WarningReviews::reminderDates()[5]));
        $result['reviews'][WarningReviews::reviewKey($row)][$reason] = $review;
        if ($suppressed) $result['suppressedWarnings'][] = array('warning' => $row, 'review' => $review);
    }
}
if ($kind !== 'home')
    $result['warnings'] = array_values(array_filter($result['warnings'], fn($row) => $kind === 'companies' ? $row['code'] === 'W07' : in_array($row['code'], array('W02', 'W03'), true)));
$template = new Template();
$scenario = $argv[2] ?? '';
$fixtureToday = '2026-10-15';
$tasks = array();
if ($scenario !== '')
{
    $titles = array('W01' => 'Call Alex about interview feedback', 'W02' => 'Production Engineer',
        'W03' => 'Production Engineer', 'W07' => 'Morgan & Co', 'W08' => 'Production Engineer');
    foreach (array('warnings', 'visibleWarnings') as $collection)
        foreach ($result[$collection] as &$fixtureWarning)
        {
            $code = $fixtureWarning['code'];
            $fixtureWarning['title'] = $titles[$code];
            $fixtureWarning['sourceDate'] = array('W01' => '2026-10-14', 'W02' => '2026-10-06 09:00:00', 'W03' => '2026-10-01 09:00:00', 'W07' => '2026-09-16 10:00:00', 'W08' => null)[$code];
            $fixtureWarning['ageDays'] = $fixtureWarning['sourceDate'] === null ? null :
                (in_array($code, array('W02', 'W03'), true) ? DateUtility::getWorkingDaysBetween(substr($fixtureWarning['sourceDate'], 0, 10), $fixtureToday) : DateUtility::getCalendarDaysBetween(substr($fixtureWarning['sourceDate'], 0, 10), $fixtureToday));
            if ($code === 'W07') $fixtureWarning['relationshipAttention'] += array('name' => $fixtureWarning['title'], 'tierLabel' => 'A', 'relationshipStatus' => 'Client', 'lastContact' => $fixtureWarning['sourceDate']);
        }
    unset($fixtureWarning);
    foreach ($result['suppressedWarnings'] as &$fixtureReview) $fixtureReview['warning']['title'] = $titles[$fixtureReview['warning']['code']];
    unset($fixtureReview);
}
if ($kind === 'home')
{
    if (in_array($scenario, array('empty', 'denied'), true))
    {
        foreach (array('warnings', 'visibleWarnings', 'reviews', 'suppressedWarnings') as $key) $result[$key] = array();
        $result['counts'] = $result['visibleCounts'] = array_fill_keys(array('W01', 'W02', 'W03', 'W07', 'W08'), 0);
        $result['suppressedReasonCount'] = $result['jobsAtRisk'] = 0;
    }
    elseif ($scenario !== '')
    {
        foreach (range(7, in_array($scenario, array('busy', 'page2', 'fullfirst'), true) ? 46 : (in_array($scenario, array('normal', 'long', 'compact2', 'removed'), true) ? 13 : 9)) as $id)
            $tasks[] = array('taskID' => $id, 'title' => $id === 7 ? 'Call Alex about interview feedback' : ($id === 8 ? 'Confirm next steps with Morgan & Co' : (array(11 => 'Confirm interview availability', 12 => 'Check candidate references', 13 => 'Review salary expectations')[$id] ?? 'Prepare candidate shortlist #' . $id)),
                'description' => $id === 7 ? 'Ask for feedback on the design interview.' : ($id === 8 ? 'Confirm the hiring brief with the recruitment team.' : 'Compile the approved CVs for the hiring team.'),
                'dueDate' => in_array($id, array(10, 13), true) ? null : (new DateTimeImmutable($fixtureToday))->modify($id === 7 ? '-1 day' : ($id === 8 ? '+0 days' : '+2 days'))->format('Y-m-d'), 'purpose' => 'candidate_update',
                'priority' => $id === 7 ? 'low' : ($id === 9 ? 'high' : 'normal'), 'status' => 'open', 'dataItemType' => $id === 7 ? DATA_ITEM_CONTACT : ($id === 8 ? DATA_ITEM_COMPANY : DATA_ITEM_JOBORDER),
                'dataItemID' => $id === 7 ? 12 : 7, 'assignedTo' => 10);
    }
    // Seven Tasks and two independent warnings exceed both the six-row reference and eight-row trial.
    // Administrator responsibility warnings remain in busy/mixed and shared review fixtures.
    if (in_array($scenario, array('normal', 'long', 'compact2', 'removed'), true))
    {
        $result['visibleWarnings'] = array_values(array_filter($result['visibleWarnings'], fn($warning) => $warning['code'] !== 'W08'));
        $result['visibleCounts']['W08'] = 0;
    }
    if ($scenario === 'mixed')
        foreach ($result['visibleWarnings'] as &$visibleWarning)
            if ($visibleWarning['code'] === 'W07')
            {
                $visibleWarning['reason'] = 'overdue_task';
                $visibleWarning['relationshipAttention']['reasons'] = array('overdue_task', 'no_next_action');
                $visibleWarning['relationshipAttention']['tasks'] = array(array('taskID' => 7, 'dueDate' => $tasks[0]['dueDate']));
            }
    unset($visibleWarning);
    if ($scenario === 'quiet')
    {
        $tasks = array($tasks[1]);
        foreach (array('warnings', 'visibleWarnings', 'reviews', 'suppressedWarnings') as $key) $result[$key] = array();
        $result['counts'] = $result['visibleCounts'] = array_fill_keys(array('W01', 'W02', 'W03', 'W07', 'W08'), 0);
        $result['suppressedReasonCount'] = $result['jobsAtRisk'] = 0;
    }
    if ($scenario === 'long')
    {
        $tasks[0]['title'] = 'Discuss interview feedback, revised requirements and the next candidate shortlist with the regional engineering team';
        $tasks[0]['description'] = 'Agree a practical next step with the hiring team. Include feedback from the production and design interviews, confirm the revised brief and record the agreed follow-up date.';
        foreach ($result['visibleWarnings'] as &$longWarning)
        {
            if (in_array($longWarning['code'], array('W02', 'W08'), true)) $longWarning['title'] = 'Senior Mechanical Design Engineer — specialist manufacturing systems and international customer programmes';
            if ($longWarning['code'] === 'W07') $longWarning['title'] = 'Northbridge Engineering, Manufacturing & Technical Recruitment Partnerships Limited';
            if (in_array($longWarning['code'], array('W02', 'W07'), true))
            {
                $longWarning['ageDays'] = $longWarning['code'] === 'W02' ? DateUtility::getWorkingDaysBetween('2026-09-16', $fixtureToday) : DateUtility::getCalendarDaysBetween('2026-09-16', $fixtureToday);
                $longWarning['sourceDate'] = '2026-09-16 09:00:00';
            }
        }
        unset($longWarning);
    }
    if ($scenario === 'unsafe' && $tasks)
    {
        $tasks[0]['priority'] = 'Legacy <priority>';
        $tasks[0]['title'] = 'Call <script>unsafe</script> & discuss feedback';
        $tasks[0]['description'] = 'Agree the next step. <script>unsafe</script> This is a Task, not a recorded conversation.';
    }
    // A W01 source is this exact Task. Keep its identity/title coherent in every fixture variant.
    foreach (array('warnings', 'visibleWarnings') as $collection)
        foreach ($result[$collection] as &$fixtureWarning)
            if ($fixtureWarning['code'] === 'W01' && $tasks) $fixtureWarning['title'] = $tasks[0]['title'];
    unset($fixtureWarning);
    if ($scenario === 'warning-led')
    {
        // Prepared authorised results, not a query simulation or a new warning rule.
        // One saved Company Task, four independent calculated conditions; no automatic Tasks.
        $tasks = array($tasks[1]);
        foreach (array('warnings', 'visibleWarnings') as $collection)
            $result[$collection] = array_values(array_filter($result[$collection], fn($warning) => $warning['code'] !== 'W01'));
        $progress = array_values(array_filter($result['warnings'], fn($warning) => $warning['code'] === 'W03'))[0];
        // Same Job's recorded actionable anchor supports both no-submission/no-progress conditions.
        $progress['sourceDate'] = '2026-10-06 09:00:00';
        $progress['ageDays'] = DateUtility::getWorkingDaysBetween('2026-10-06', $fixtureToday);
        $progress['deadline'] = DateUtility::addWorkingDays($progress['sourceDate'], 5);
        foreach ($result['warnings'] as &$sourceWarning)
            if ($sourceWarning['code'] === 'W03') $sourceWarning = $progress;
        unset($sourceWarning);
        array_splice($result['visibleWarnings'], 1, 0, array($progress));
        $result['counts']['W01'] = $result['visibleCounts']['W01'] = 0;
        $result['visibleCounts']['W03'] = 1;
        foreach ($result['reviews'][WarningReviews::reviewKey($progress)] as &$progressReview)
        {
            $progressReview['suppressed'] = false;
            $progressReview['status'] = 'Active warning';
            $progressReview['state'] = null;
        }
        unset($progressReview);
        $result['suppressedWarnings'] = array_values(array_filter($result['suppressedWarnings'], fn($item) => $item['warning']['code'] !== 'W03'));
        $result['suppressedReasonCount'] = count($result['suppressedWarnings']);
    }
    if ($scenario === 'removed')
    {
        array_shift($tasks);
        foreach (array('warnings', 'visibleWarnings') as $collection)
            $result[$collection] = array_values(array_filter($result[$collection], fn($warning) => $warning['code'] !== 'W01'));
        $result['counts']['W01'] = $result['visibleCounts']['W01'] = 0;
    }
    if ($scenario === 'summary-single')
    {
        $result['visibleWarnings'] = array_values(array_filter($result['visibleWarnings'], fn($warning) => $warning['code'] === 'W07'));
        $result['visibleCounts'] = array_fill_keys(array('W01','W02','W03','W07','W08'),0);
        $result['visibleCounts']['W07'] = 1;
    }
    $state = array('homeView' => in_array($scenario, array('page2', 'fullfirst'), true) ? 'all' : 'compact', 'homeFilter' => 'all',
        'homePage' => in_array($scenario, array('page2', 'compact2'), true) ? '2' : '1', 'homeContext' => '');
    $rows = HomeUI::priorityRows($tasks, $result['visibleWarnings'], $fixtureToday);
    $pager = new Pager(count($rows), $state['homeView'] === 'all' ? 25 : 8, (int) $state['homePage']);
    $pager->setSortByParameters('m=home&amp;a=priorities&amp;filter=all', '', '');
    $rows = array_slice($rows, $pager->getThisPageStartRow(), $state['homeView'] === 'all' ? 25 : 8);
    $fixtureContexts = array();
    foreach ($rows as &$row)
    {
        if ($row['task'] === null) continue;
        $row['permissions'] = array('edit' => true, 'complete' => true);
        $fixtureType = $row['task']['dataItemType'];
        $companyName = $scenario === 'long' ? 'Northbridge Engineering, Manufacturing & Technical Recruitment Partnerships Limited' : 'Morgan & Co';
        $row['parentContext'] = array('type' => $fixtureType, 'id' => $row['task']['dataItemID'],
            'name' => $fixtureType === DATA_ITEM_CONTACT ? 'Alex Taylor' : ($fixtureType === DATA_ITEM_COMPANY ? $companyName : ($scenario === 'long' ? 'Senior Mechanical Design Engineer — specialist manufacturing systems and international customer programmes' : 'Production Engineer')),
            'companyName' => $fixtureType === DATA_ITEM_CONTACT ? $companyName : ($fixtureType === DATA_ITEM_JOBORDER ? 'Westhaven Manufacturing' : ''));
        if ($scenario === 'restricted')
        {
            $row['permissions'] = array('edit' => false, 'complete' => false);
            $row['parentContext'] = null;
        }
        $fixtureContexts[$fixtureType . ':' . $row['task']['dataItemID']] = $row['parentContext'];
    }
    unset($row);
    foreach ($rows as &$row)
        if ($row['task'] === null)
        {
            $warning = $row['warnings'][0];
            $row['parentContext'] = $fixtureContexts[$warning['entityType'] . ':' . $warning['entityID']] ?? null;
        }
    unset($row);
    $template->assign('homeToday', $fixtureToday);
    $template->assign('priorityOverview', HomeUI::priorityOverview($tasks, $result['visibleWarnings'], $fixtureToday));
    $template->assign('homeState', $state);
    $template->assign('priorityRows', $rows);
    $template->assign('priorityPager', $pager);
    $template->assign('myTaskCount', count($tasks));
}
if ($kind === 'home' && $scenario !== '')
{
    $empty = in_array($scenario, array('empty', 'denied'), true);
    $restricted = in_array($scenario, array('restricted', 'denied'), true);
    $template->assign('homeAccounts', $restricted ? null : ($empty ? array() : array(
        array('companyID' => 7, 'name' => 'Morgan & Co', 'commercialTier' => 'A', 'tierLabel' => 'A — Key accounts', 'relationshipStatus' => 'Client', 'lastContact' => '2026-09-16'),
        array('companyID' => 8, 'name' => 'Westhaven Manufacturing', 'commercialTier' => 'B', 'tierLabel' => 'B', 'relationshipStatus' => 'Engaged', 'lastContact' => '2026-10-01'))));
    $template->assign('homeJobs', $restricted ? null : array('total' => $empty ? 0 : 3, 'rows' => $empty ? array() : array(
        array('jobOrderID' => 7, 'title' => 'Production Engineer', 'companyName' => 'Westhaven Manufacturing', 'status' => 'Active', 'daysOld' => 18),
        array('jobOrderID' => 8, 'title' => 'Quality Engineer', 'companyName' => 'Morgan & Co', 'status' => 'On Hold', 'daysOld' => 11),
        array('jobOrderID' => 9, 'title' => 'Maintenance Technician', 'companyName' => 'Westhaven Manufacturing', 'status' => 'Active', 'daysOld' => 5))));
    $template->assign('homeCalendar', $restricted ? null : ($empty ? array() : array(
        array('eventID' => 21, 'title' => 'Production interview — Alex Taylor', 'date' => '2026-10-15 14:00:00', 'allDay' => 0, 'time' => '14:00'),
        array('eventID' => 22, 'title' => 'Client brief — Morgan & Co', 'date' => '2026-10-16 10:00:00', 'allDay' => 0, 'time' => '10:00'))));
}
if ($kind === 'homeActivity')
{
    $_SERVER['PHP_SELF'] = 'index.php';
    $entry = array('activityID' => 1, 'dataItemType' => DATA_ITEM_CONTACT, 'dataItemID' => 12, 'subjectName' => 'Alex Taylor', 'typeDescription' => 'Call (Talked)', 'dateCreated' => '14-10-26 15:30', 'notes' => 'Confirmed the revised brief and agreed to review the candidate pack on Friday.', 'enteredByFirstName' => 'Maya', 'enteredByLastName' => 'Patel');
    if ($scenario === 'unsafe') foreach (array('typeDescription','notes','enteredByFirstName','subjectName') as $field) $entry[$field] = '<img src=x onerror="window.homeUnsafe=1">';
    $entries = array($entry);
    if ($scenario === 'mixed' || $scenario === 'mixed-company')
    {
        $entries = array(
            array_replace($entry, array('dataItemType' => DATA_ITEM_CANDIDATE, 'dataItemID' => 21, 'subjectName' => 'Priya Shah', 'notes' => 'Called; awaiting feedback.')),
            array_replace($entry, array('activityID' => 2, 'dateCreated' => '14-10-26 15:00', 'dataItemType' => DATA_ITEM_CONTACT, 'dataItemID' => 12, 'subjectName' => 'Helen Ward', 'notes' => 'Confirmed availability.')),
            array_replace($entry, array('activityID' => 3, 'dateCreated' => '14-10-26 14:00', 'dataItemType' => DATA_ITEM_CANDIDATE, 'dataItemID' => 22, 'subjectName' => 'Alex Taylor', 'notes' => 'Discussed the next steps.')));
        if ($scenario === 'mixed-company')
        {
            $entries[0] = array_replace($entries[0], array('dataItemType' => DATA_ITEM_CONTACT, 'dataItemID' => 13, 'subjectName' => 'Daniel Brooks'));
            $entries[2] = array_replace($entries[2], array('dataItemType' => DATA_ITEM_COMPANY, 'dataItemID' => 7, 'subjectName' => 'Morgan & Co', 'notes' => 'Updated the account briefing.'));
        }
    }
    $template->assign('homeActivity', $scenario === 'restricted' ? null : ($scenario === 'empty' ? array() : $entries));
    $template->assign('homeActivityURL', TaskPresentation::parentURL($scenario === 'mixed' ? DATA_ITEM_JOBORDER : ($scenario === 'mixed-company' ? DATA_ITEM_COMPANY : DATA_ITEM_CONTACT), in_array($scenario, array('mixed','mixed-company'), true) ? 7 : 12));
    $template->display('modules/home/RecentActivity.tpl');
    exit;
}
$template->assign('recruitmentWarnings', $result);
$submitInterceptor = '<script>window.addEventListener("load", function() { document.addEventListener("submit", function(e) { if (e.defaultPrevented) return; window.submitted = Object.fromEntries(new FormData(e.target, e.submitter)); window.submissions = (window.submissions || 0) + 1; e.preventDefault(); }); });</script>';
if ($kind === 'home' && $scenario !== '')
{
    // Reuse actual module metadata and TemplateUtility chrome, without discovery/migrations.
    foreach ($coreModules as $moduleName => $position)
    {
        $files = glob('modules/' . $moduleName . '/*UI.php');
        if (!$files) continue;
        require_once $files[0];
        $class = basename($files[0], '.php');
        $module = new $class();
        $_SESSION['modules'][$moduleName] = array($class, $module->getModuleTabText(), array());
    }
    $template->assign('active', new HomeUI());
    ob_start();
    $template->display('modules/home/Home.tpl');
    $fixtureHTML = str_replace('</head>', '<meta name="fixture-clock" content="' . $fixtureToday . '"></head>', ob_get_clean());
    if ($scenario === 'warning-led')
        $fixtureHTML = str_replace('<h1 class="h4 fw-semibold mb-0">Your working day</h1>', '<h1 class="h4 fw-semibold mb-0">Your working day</h1><p class="mb-0">Synthetic warning-led example: 4 calculated warnings and 1 saved Task. Administrator visibility includes responsibility warnings.</p>', $fixtureHTML);
    echo str_replace('</body>', $submitInterceptor . '</body>', $fixtureHTML);
    exit;
}
echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="icon" href="data:,"><link rel="stylesheet" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css"><link rel="stylesheet" href="main.css">';
TemplateUtility::printBootstrapBundle();
echo '<script src="js/warningReviews.js"></script>';
if (($argv[2] ?? '') !== '') echo '<script src="js/home.js"></script>';
echo '</head><body><main id="main" class="container-fluid py-3">';

if ($kind === 'home') $template->display('modules/home/RecruitmentWarnings.tpl');
elseif ($kind === 'joborders') $template->display('modules/joborders/RecruitmentWarnings.tpl');
else $template->display('modules/home/WarningReviewSource.tpl');
// Real Task panel exercises shared bundle loading and coexisting quick-add markup.
if ($scenario === '') {
$template->assign('parentType', DATA_ITEM_COMPANY);
$template->assign('parentID', 7);
$template->assign('taskRowsHTML', '<p>No open Tasks.</p>');
$template->display('modules/tasks/Parent.tpl');
}
echo '</main>' . $submitInterceptor . '</body></html>';
