<?php
/* CLI-only component fixture. Real templates/assets, prepared synthetic warning state; no database access. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
chdir(dirname(__DIR__, 2));
define('LEGACY_ROOT', '.');
require 'constants.php';
require 'lib/CATSUtility.php';
require 'lib/Template.php';
require 'lib/TemplateUtility.php';
require 'lib/WarningReviews.php';
$_SESSION['CATS'] = new class {
    public function getTimeZoneOffset() { return 0; }
    public function isDateDMY() { return true; }
    public function isTimeFormat24() { return true; }
    public function getCSRFToken() { return 'fixture-token'; }
    public function getAccessLevel($key) { return ACCESS_LEVEL_SA; }
};
$kind = $argv[1] ?? 'home';
if (!in_array($kind, array('home', 'companies', 'joborders'), true)) throw new InvalidArgumentException('Invalid fixture.');
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
echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="icon" href="data:,"><link rel="stylesheet" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css"><link rel="stylesheet" href="main.css">';
TemplateUtility::printBootstrapBundle();
echo '<script src="js/warningReviews.js"></script></head><body><main class="container-fluid py-2">';
$template = new Template();
$template->assign('recruitmentWarnings', $result);
if ($kind === 'home') $template->display('modules/home/RecruitmentWarnings.tpl');
elseif ($kind === 'joborders') $template->display('modules/joborders/RecruitmentWarnings.tpl');
else $template->display('modules/home/WarningReviewSource.tpl');
// Real Task panel exercises shared bundle loading and coexisting quick-add markup.
$template->assign('parentType', DATA_ITEM_COMPANY);
$template->assign('parentID', 7);
$template->assign('taskRowsHTML', '<p>No open Tasks.</p>');
$template->display('modules/tasks/Parent.tpl');
echo '</main><script>document.addEventListener("submit", function(e) { window.submitted = Object.fromEntries(new FormData(e.target, e.submitter)); e.preventDefault(); });</script></body></html>';
