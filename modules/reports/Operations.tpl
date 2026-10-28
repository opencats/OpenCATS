<?php
$op = $this->operations;
$f = $op['filters'];
$e = array('Template','escapeHtml');
$url = function ($changes = array()) use ($f) {
    if (isset($changes['view']) || isset($changes['recruiter']) || isset($changes['desk'])) $changes['comparisonPage'] = '1';
    return CATSUtility::getIndexName() . '?' . http_build_query(array_merge(array('m'=>'reports','a'=>'reports'), $f, $changes));
};
$value = fn($kind) => $op['available'][$kind] ? $op['totals'][$kind] : 'N/A';
// Presentation only: retain the exact condition strings and Activity IDs in the model.
$conditionLabels = array(
    'open' => 'Open',
    'in_progress' => 'In progress',
    'W01: overdue commitment' => 'Overdue follow-up',
    'W02: no recorded submission' => 'No submission recorded',
    'W03: no recorded candidate progress' => 'No candidate progress recorded',
    'W07: stale relationship' => 'Account contact due',
    'W07: no next action' => 'Account follow-up needed',
    'W07: overdue task' => 'Overdue account follow-up',
    'W07: due today' => 'Account follow-up due today',
    'W07: upcoming' => 'Upcoming account follow-up',
    'W08: invalid assignee' => 'Task assignment needs attention',
    'W08: invalid owner' => 'Job owner needs attention',
    'W08: invalid recruiter' => 'Job recruiter needs attention'
);
$recordStatus = function ($row) use ($conditionLabels) {
    if (!empty($row['overdue'])) return 'Overdue';
    if (isset($row['status']))
    {
        $labels = array();
        foreach (explode('; ', $row['status']) as $condition)
            $labels[] = $conditionLabels[$condition] ??
                (preg_match('/^W[0-9]+:/', $condition) ? 'Needs attention — open record for details' : $condition);
        return implode('; ', $labels);
    }
    return array(100=>'Call: not reached',200=>'Email',300=>'Meeting',400=>'Other activity',500=>'Call: talked',
        600=>'Call: left message',700=>'Call: missed',800=>'Status change')[(int) ($row['type'] ?? 0)] ?? 'Recorded activity (type unavailable)';
};
$deskLabel = fn($name) => $name === 'Unassigned / no Job Desk' ? 'Unassigned / no linked job desk' : $name;
// Keep charts bounded to comparison dimensions, while retaining Unassigned when present.
$chartRows = function ($dimension, $limit, $metric) use ($op, $deskLabel) {
    $rows = array_filter($op['groups'][$dimension], fn($row) => $row[$metric] > 0);
    uasort($rows, fn($a,$b) => ($metric==='tasks' ? $b['overdue'] <=> $a['overdue'] : 0) ?: ($b[$metric] <=> $a[$metric]) ?: strcmp($a['name'],$b['name']));
    $unassigned = $rows[0] ?? null;
    if ($unassigned !== null) unset($rows[0]);
    $rows = array_slice($rows, 0, $limit - ($unassigned === null ? 0 : 1), true);
    if ($unassigned !== null) $rows[0] = $unassigned;
    foreach ($rows as $id=>&$row)
    {
        $row['scopeID'] = $id;
        if ($dimension === 'desk') $row['name'] = $deskLabel($row['name']);
    }
    unset($row);
    return array_values($rows);
};
$rowCue = function ($row) use ($f) {
    $status = $row['status'] ?? '';
    if (!empty($row['overdue']) || preg_match('/(?:^|; )W(?:01: overdue commitment|07: overdue task)(?:;|$)/', $status))
        return array('class'=>'reports-cue-overdue','label'=>'Overdue');
    if ($f['view'] === 'tasks' && ($row['sourceDate'] ?? null) === DateUtility::getAdjustedDate('Y-m-d'))
        return array('class'=>'reports-cue-attention','label'=>'Due today');
    if (preg_match('/(?:^|; )W07: due today(?:;|$)/', $status))
        return array('class'=>'reports-cue-attention','label'=>'Due today');
    if ($status === 'W07: upcoming')
        return array('class'=>'reports-cue-attention','label'=>'Upcoming');
    if (preg_match('/(?:^|; )W0[2378]:/', $status))
        return array('class'=>'reports-cue-attention','label'=>'Needs attention');
    return null;
};
?>
<section aria-labelledby="operations-title" class="reports-operations mb-3">
<header class="oc-page-header mb-2">
    <h1 id="operations-title" class="h4 fw-semibold mb-1">Recruitment Operations</h1>
    <p class="small text-body-secondary mb-1"><?php echo $e($f['view']==='outcomes' ? 'Recorded event actors: Unknown' : ($f['recruiter'] === 'all' ? 'All recruiters you can report on' : $op['choices']['recruiter'][(int) $f['recruiter']])); ?> · <?php echo $e($f['desk'] === 'all' ? 'All job desks you can report on' : $op['choices']['desk'][(int) $f['desk']]); ?>.</p>
    <p class="small mb-0">Recorded events cover <?php echo $e($f['start'] . ' to ' . $f['end']); ?>. Workload and follow-up figures are current as of <?php echo $e(DateUtility::getAdjustedDate('Y-m-d')); ?>.</p>
    <p class="small mb-0"><strong>Coverage:</strong> jobs and tasks you can access; client-relationship follow-up covers your eligible accounts only, not all company accounts.</p>
</header>
<form method="get" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName()); ?>" class="card card-body p-2 mb-2">
    <input type="hidden" name="m" value="reports"><input type="hidden" name="a" value="reports">
    <input type="hidden" name="view" value="<?php echo Template::escapeAttr($f['view']); ?>">
    <?php if ($f['view']==='outcomes'): foreach (array('milestone','outcomeSort','outcomeDirection') as $key): ?>
    <input type="hidden" name="<?php echo $key; ?>" value="<?php echo Template::escapeAttr($f[$key]); ?>">
    <?php endforeach; endif; ?>
    <div class="row g-2 align-items-end">
        <div class="col-6 col-md"><label class="form-label small mb-1" for="ops-range">Period</label><select id="ops-range" name="range" class="form-select form-select-sm">
        <?php foreach (array('week'=>'This week','month'=>'This month','quarter'=>'This quarter','custom'=>'Custom') as $key=>$label): ?><option value="<?php echo $key; ?>" <?php if ($f['range']===$key) echo 'selected'; ?>><?php echo $label; ?></option><?php endforeach; ?>
        </select></div>
        <div class="col-6 col-md" data-operations-custom-date><label class="form-label small mb-1" for="ops-start">From</label><input id="ops-start" type="date" name="start" class="form-control form-control-sm" value="<?php echo Template::escapeAttr($f['start']); ?>"></div>
        <div class="col-6 col-md" data-operations-custom-date><label class="form-label small mb-1" for="ops-end">Through</label><input id="ops-end" type="date" name="end" class="form-control form-control-sm" value="<?php echo Template::escapeAttr($f['end']); ?>"></div>
        <?php foreach (array('recruiter'=>$f['view']==='outcomes'?'Event actor':($f['view']==='activity'?'Recorded by':'Recruiter'),'desk'=>'Job desk') as $key=>$label): ?>
        <div class="col-6 col-md"><label class="form-label small mb-1" for="ops-<?php echo $key; ?>"><?php echo $label; ?></label><select id="ops-<?php echo $key; ?>" name="<?php echo $key; ?>" class="form-select form-select-sm"><option value="all">All available</option>
        <?php foreach ($op['choices'][$key] as $id=>$name): ?><option value="<?php echo (int) $id; ?>" <?php if ($f[$key] !== 'all' && (int) $f[$key] === (int) $id) echo 'selected'; ?>><?php echo $e($key === 'desk' ? $deskLabel($name) : $name); ?></option><?php endforeach; ?>
</select></div>
        <?php endforeach; ?>
        <div class="col-12 col-md-auto"><button class="btn btn-sm btn-success" type="submit">Apply filters</button></div>
    </div>
</form>
<nav class="d-flex flex-wrap gap-2 mb-2" aria-label="Operational reports">
<?php foreach (array('overview'=>'Overview','activity'=>'Activity & Engagement','jobs'=>'Desk Workload','tasks'=>'Follow-up & Candidate Care','warnings'=>'Warnings & Follow-up','outcomes'=>'Recruitment Progress & Outcomes') as $view=>$label): ?>
<a class="btn btn-sm <?php echo $view===$f['view'] ? 'btn-success' : 'btn-outline-secondary'; ?>" <?php if ($view===$f['view']) echo 'aria-current="page"'; ?> href="<?php echo Template::escapeAttr($url(array('view'=>$view,'page'=>'1','recruiter'=>$view==='outcomes' || $f['view']==='outcomes' ? 'all' : $f['recruiter'],'desk'=>$view==='outcomes' || $f['view']==='outcomes' ? 'all' : $f['desk']))); ?>"><?php echo $label; ?></a>
<?php endforeach; ?>
</nav>
<?php if ($f['view']==='outcomes'): include __DIR__ . '/Outcomes.tpl'; else: ?>
<div class="row g-2 mb-2">
<?php foreach (array('activity'=>'Recorded activity','tasks'=>'Outstanding tasks','jobs'=>'Active Job Orders','warnings'=>'Records needing attention') as $key=>$label): ?>
<div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body py-2 px-3"><div class="small"><?php echo $label; ?></div><div class="h4 mb-0"><?php if ($op['available'][$key]): ?><a href="<?php echo Template::escapeAttr($url(array('view'=>$key,'page'=>'1'))); ?>"><?php echo $value($key); ?></a><?php else: ?>N/A<?php endif; ?></div>
<div class="small text-body-secondary"><?php echo !$op['available'][$key] ? 'Not available with your access' :
    ($key==='tasks' ? $op['totals']['overdue'] . ' past due · current' :
    ($key==='warnings' ? 'Current · each record counted once' :
    ($key==='jobs' ? 'Current · Active job workload' : 'Selected dates · includes status changes'))); ?></div>
</div></div></div>
<?php endforeach; ?>
</div>
<?php if ($f['view']==='overview' && isset($op['outcomes'])): $out=$op['outcomes']; ?>
<section class="card mb-2" aria-labelledby="outcomes-overview"><div class="card-body p-2">
<h2 id="outcomes-overview" class="h6 mb-1"><a href="<?php echo Template::escapeAttr($url(array('view'=>'outcomes','page'=>'1','recruiter'=>'all'))); ?>">Recruitment outcomes</a></h2>
<p class="small mb-1">Selected dates · distinct candidate–job pairs per recorded milestone. Event actor Unknown; current Job Desk classification.</p>
<div class="d-flex flex-wrap gap-3 small">
<?php foreach ($out['definitions'] as $key=>$definition): ?><span><?php echo $e($definition['label']); ?>: <strong><?php echo $out['available'][$key] ? $out['totals'][$key] : 'N/A'; ?></strong><?php if ($out['available'][$key]): ?> · comparable current <?php echo $out['changes'][$key]['current']; ?> · previous <?php echo $out['changes'][$key]['previous']; ?> · change <?php echo ($out['changes'][$key]['absolute']>0?'+':'').$out['changes'][$key]['absolute']; ?><?php endif; ?></span><?php endforeach; ?>
</div>
<?php if ($out['actorUnavailable']): ?><p class="small mb-0">Outcome attribution is unavailable for the selected recruiter. Open outcomes to inspect Unknown event actors.</p><?php endif; ?>
<p class="small text-body-secondary mb-0">Comparisons use <?php echo $e($out['comparison']['currentStart'].' to '.$out['comparison']['currentEnd']); ?> versus <?php echo $e($out['comparison']['previousStart'].' to '.$out['comparison']['previousEnd']); ?>; today may be incomplete. <?php if ($out['comparison']['currentEnd']<$f['end']): ?>Both comparison windows are capped to the shorter previous period.<?php endif; ?> Placements are recorded statuses, not confirmed starts.</p>
</div></section>
<?php endif; ?>
<div class="row g-2 mb-2">
<section class="col-12 col-xl-8" aria-labelledby="ops-workload"><div class="card h-100">
<h2 class="card-header h6 mb-0" id="ops-workload">Recruiter & Desk Workload</h2>
<div class="card-body p-2">
<?php if ($f['view']==='overview'): ?>
<h3 class="h6 mb-1">Outstanding Tasks by recruiter</h3>
<?php if ($op['available']['tasks']): $taskRows=$chartRows('recruiter',6,'tasks'); ?>
<?php echo Charts::render('operations-task-workload', Charts::operationsTasks($taskRows)); ?>
<p class="small mb-2"><?php echo count($taskRows); ?> of <?php echo count($op['groups']['recruiter']); ?> recruiters shown, ordered by overdue then outstanding work; Unassigned is retained where present. <a href="<?php echo Template::escapeAttr($url(array('view'=>'tasks','page'=>'1'))); ?>">Inspect outstanding Tasks</a></p>
<?php else: ?><p>N/A — Task figures are not available with your access.</p><?php endif; ?>
<?php elseif ($f['view']==='jobs'): ?>
<h3 class="h6 mb-1">Active Job Orders by Desk</h3>
<?php if ($op['available']['jobs']): $jobRows=$chartRows('desk',10,'jobs'); echo Charts::render('operations-desk-workload', Charts::operationsJobs($jobRows)); ?>
<p class="small mb-2"><?php echo count($jobRows); ?> of <?php echo count($op['groups']['desk']); ?> desks shown. Use a Desk filter to inspect its Active jobs.</p>
<?php else: ?><p>N/A — Job figures are not available with your access.</p><?php endif; ?>
<?php elseif ($f['view']==='activity'): ?>
<h3 class="h6 mb-1">Recorded activity by week</h3>
<?php if ($op['available']['activity']): ?>
<?php $buckets = array(); for ($week=(new DateTimeImmutable($f['start']))->modify('monday this week'); $week<=new DateTimeImmutable($f['end']); $week=$week->modify('+7 days')) $buckets[$week->format('Y-m-d')]=0; foreach ($op['trend'] as $date=>$count) { $week=(new DateTimeImmutable($date))->modify('monday this week')->format('Y-m-d'); $buckets[$week]=($buckets[$week] ?? 0)+$count; }
$weekLabels=array(); foreach (array_keys($buckets) as $week) $weekLabels[]=$week . ($week<$f['start'] || (new DateTimeImmutable($week))->modify('+6 days')->format('Y-m-d')>$f['end'] ? ' (partial)' : '');
$chart = Charts::comparison('Recorded activity by week', $weekLabels, array_values($buckets), 'Selected dates only; partial weeks are not full-week comparisons.'); $chart['horizontal']=false; $chart['type']='line'; $chart['datasets'][0]=array('label'=>'Recorded activity','data'=>array_values($buckets),'borderColor'=>'#0d6efd','backgroundColor'=>'#0d6efd','tension'=>0,'fill'=>false); echo Charts::render('operations-activity-trend',$chart); ?>
<?php if ($op['comparison'] !== null): $comparison=$op['comparison']; ?>
<div class="table-responsive"><table class="table table-sm mb-2" aria-label="Equivalent recorded activity periods"><caption class="small caption-top py-1">Equivalent elapsed periods · <?php echo $comparison['days']; ?> calendar days each. Only the dates shown are compared; today may be incomplete.</caption><thead><tr><th>Recorded activity period</th><th>Events</th></tr></thead><tbody>
<tr><th scope="row" class="fw-normal">Current: <?php echo $e($comparison['currentStart'].' to '.$comparison['currentEnd']); ?></th><td><?php echo $comparison['current']; ?></td></tr>
<tr><th scope="row" class="fw-normal">Previous: <?php echo $e($comparison['previousStart'].' to '.$comparison['previousEnd']); ?></th><td><?php echo $comparison['previous']; ?></td></tr>
</tbody></table></div>
<?php $activityDifference=$comparison['current']-$comparison['previous']; ?>
<p class="small mb-2"><?php echo $activityDifference===0 ? 'The same number of recorded events in both periods.' : abs($activityDifference) . ($activityDifference>0 ? ' more' : ' fewer') . ' recorded events than the previous comparison period.'; ?></p>
<?php if ($comparison['currentEnd']<$f['end']): ?><p class="small mb-2">The previous period is shorter, so both comparison windows are capped. The summary still includes all selected dates.</p><?php endif; ?>
<?php endif; ?>
<?php else: ?><p>N/A — Activity figures are not available with your access.</p><?php endif; ?>
<?php else: ?>
<h3 class="h6 mb-1">Follow-up on this page</h3>
<?php if ($op['available'][$f['view']]): ?>
<ul class="list-group list-group-flush mb-2">
<?php $exceptionCount=0; foreach ($op['details'] as $row): $cue=$rowCue($row); if ($cue===null || $exceptionCount===5) continue; ++$exceptionCount; ?>
<li class="list-group-item px-0 py-1"><span class="reports-cue <?php echo $cue['class']; ?>"><?php echo $cue['label']; ?></span> <a href="<?php echo Template::escapeAttr($row['url']); ?>" target="_blank" rel="noopener"><?php echo $e($row['title']); ?></a><span class="d-block small text-body-secondary"><?php echo $e($row['actorName']); ?> · <?php echo $e($recordStatus($row)); ?></span></li>
<?php endforeach; ?>
<?php if (!$exceptionCount): ?><li class="list-group-item px-0 py-1">No dated overdue or flagged follow-ups on this page. All matching records remain below.</li><?php endif; ?>
</ul>
<?php else: ?><p>N/A — these figures are not available with your access.</p><?php endif; ?>
<?php endif; ?>
<details id="operations-comparisons"><summary class="small">Exact recruiter and Desk comparisons</summary>
<p class="small text-body-secondary my-2">Current recruiter rows show active users with contributing records. Totals and Desk breakdowns cover all permitted work, so current recruiter rows may not sum to those totals. Activity &amp; Engagement retains historical contributors. Attention records can involve more than one recruiter. Recruiter attention totals overlap and must not be added together.</p>
<?php foreach (array('recruiter'=>'Recruiter','desk'=>'Job desk') as $dimension=>$label): ?>
<div class="table-responsive"><table class="table table-sm table-striped align-middle mb-2"><caption class="visually-hidden"><?php echo $label; ?> workload for your selection</caption>
<thead><tr><th scope="col"><?php echo $label; ?></th><th scope="col">Activity <span class="fw-normal small">(period)</span></th><th scope="col">Jobs</th><th scope="col">Tasks</th><th scope="col">Overdue</th><th scope="col">Needs attention</th></tr></thead><tbody>
<?php $comparisonPager = new Pager(count($op['groups'][$dimension]), $f['view']==='overview' ? 5 : 10, (int) $f['comparisonPage']); $comparisonRows = array_slice($op['groups'][$dimension], $f['view']==='overview' ? 0 : $comparisonPager->getThisPageStartRow(), $f['view']==='overview' ? 5 : 10, true); ?>
<?php foreach ($comparisonRows as $id=>$row): ?><tr><th scope="row" class="fw-normal"><a href="<?php echo Template::escapeAttr($url(array($dimension=>(string) $id,'page'=>'1'))); ?>"><?php echo $e($dimension === 'desk' ? $deskLabel($row['name']) : $row['name']); ?></a></th>
<?php foreach (array('activity','jobs','tasks','overdue','warnings') as $key): ?><td<?php if ($op['available'][$key==='overdue' ? 'tasks' : $key] && $row[$key]>0 && in_array($key,array('overdue','warnings'),true)) echo ' class="' . ($key==='overdue'?'reports-number-overdue':'reports-number-attention') . '"'; ?>><?php echo $op['available'][$key==='overdue' ? 'tasks' : $key] ? (int) $row[$key] : 'N/A'; ?></td><?php endforeach; ?></tr><?php endforeach; ?>
<?php if (!$comparisonRows): ?><tr><td colspan="6">No records match your selection.</td></tr><?php endif; ?>
</tbody></table></div>
<?php if (count($op['groups'][$dimension]) > count($comparisonRows)): ?><p class="small mb-1"><?php echo count($op['groups'][$dimension]); ?> comparison rows; showing <?php echo count($comparisonRows); ?>. <?php if ($f['view']==='overview'): ?><a href="<?php echo Template::escapeAttr($url(array('view'=>'jobs','page'=>'1'))); ?>">Open detailed workload</a><?php else: ?><?php if ($comparisonPager->getCurrentPage()>1): ?><a href="<?php echo Template::escapeAttr($url(array('comparisonPage'=>$comparisonPager->getCurrentPage()-1))); ?>">Previous comparisons</a><?php endif; ?> <?php if ($comparisonPager->getCurrentPage()<$comparisonPager->getTotalPages()): ?><a href="<?php echo Template::escapeAttr($url(array('comparisonPage'=>$comparisonPager->getCurrentPage()+1))); ?>">Next comparisons</a><?php endif; ?><?php endif; ?></p><?php endif; ?>
<?php endforeach; ?>
</details>
</div></div></section>
<section class="col-12 col-xl-4" aria-labelledby="ops-oversight"><div class="card h-100"><h2 class="card-header h6 mb-0" id="ops-oversight">Warnings & Follow-up</h2><div class="card-body p-2">
<p class="small mb-2">Current issues; review decisions cover the selected dates.</p>
<dl class="row small mb-2">
<dt class="col-8">Jobs flagged for attention</dt><dd class="col-4"><?php echo $op['available']['warnings'] ? $op['jobsAtRisk'] : 'N/A'; ?></dd>
<?php foreach (array('remind_later'=>'Issues scheduled for review','close'=>'Issues temporarily set aside','reappeared'=>'Issues requiring another review') as $key=>$label): ?><dt class="col-8"><?php echo $label; ?></dt><dd class="col-4"><?php echo $op['available']['warnings'] && $op['oversight'] ? $op['dispositions'][$key] : 'N/A'; ?></dd><?php endforeach; ?>
<dt class="col-8">Issues reviewed more than once</dt><dd class="col-4"><?php echo $op['available']['warnings'] && $op['oversight'] ? $op['repeatedReviews'] : 'N/A'; ?></dd>
<dt class="col-8">Review decisions recorded</dt><dd class="col-4"><?php echo $op['available']['warnings'] && $op['oversight'] ? $op['reviewActions'] : 'N/A'; ?></dd>
</dl>
<p class="small text-body-secondary mb-2">One record can have several issues. Setting an issue aside does not resolve it.</p>
<?php if ($op['oversight']): ?><a href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=reports&a=warningOversight'); ?>" target="_blank" rel="noopener">View review decisions and reasons</a><p class="small text-body-secondary mt-1 mb-0">Opens another tab with its own filters and the last 30 days of decisions.</p><?php else: ?><p class="small mb-0">Review details are not available with your access.</p><?php endif; ?>
</div></div></section>
</div>
<p class="small text-body-secondary mb-2">Recruiter attention totals overlap and must not be added together. Colours highlight dated overdue work or existing attention requirements, not recruiter performance.</p>
<details class="small mb-2" id="operations-about">
<summary>About these figures</summary>
<div class="pt-2">
<p class="mb-1">Activity counts recorded events, including administrative and status changes, credited to the person who recorded them. Calls include calls not reached, talked calls, messages left and missed calls. “Calls: talked” counts only records explicitly marked as talked; emails and meetings do not prove delivery, attendance or qualifying engagement.</p>
<p class="mb-1">Jobs count authorised Active Job Orders only. Status alone does not establish a genuine available vacancy; Full and Closed records remain accessible through native searches. Tasks include open and in-progress work, attributed to the assignee. Job workload is attributed to the current recruiter, not whoever originally obtained the vacancy. Desk figures use the associated Job Order’s current Desk, including an explicitly linked recruitment pipeline for Tasks; Sector and a recruiter's own Desk membership do not allocate this work.</p>
<p class="mb-1">Records needing attention count each Task, Job Order or Company once. One record can have multiple warning issues and responsible recruiters. Recruiter attention rows can overlap; use the filtered total rather than adding rows. Client-relationship coverage stays limited to your eligible accounts. Missing permissions show N/A, not zero.</p>
<p class="mb-1">Review figures count separate issues on currently flagged records. Reminder and temporary Close counts can overlap across reviewers. “Requiring another review” means the review date has arrived or the underlying information has changed. “Reviewed more than once” counts issues with multiple decisions in the selected dates, including different reviewers. Decisions count individual History actions, including resets; they do not count resolved records or all past issues.</p>
<p class="mb-0">Workload is current, not a historical trend. Job progress warnings require recorded recruitment-cycle information. Unknown dates remain Unknown. Historical Activity uses current record access and the linked Job’s current Desk. Historical backlog, inferred candidate contact dates and unverified outcomes are unavailable. Record and review links open a separate tab; the review screen uses its own filters and 30-day decision window.</p>
</div>
</details>
<?php if ($f['view']==='activity'): ?>
<section class="card mb-2"><h2 class="card-header h6 mb-0">Activity & Engagement</h2><div class="card-body p-2">
<p class="small">Recorded calls, emails and meetings; these do not establish delivery, attendance or outcomes.</p>
<div class="table-responsive"><table class="table table-sm" aria-label="Activity by recorded person"><thead><tr><th>Recorded by</th><th>All activity</th><th>Calls</th><th>Calls: talked</th><th>Emails</th><th>Meetings</th><th>Status changes</th><th>Other / unclassified</th></tr></thead><tbody>
<?php foreach (array_slice($op['groups']['recruiter'],(new Pager(count($op['groups']['recruiter']),10,(int) $f['comparisonPage']))->getThisPageStartRow(),10) as $row): ?><tr><th scope="row"><?php echo $e($row['name']); ?></th><?php foreach (array('activity','calls','conversations','emails','meetings','statusChanges','other') as $key): ?><td><?php echo $op['available']['activity'] ? $row[$key] : 'N/A'; ?></td><?php endforeach; ?></tr><?php endforeach; ?>
</tbody></table></div>
<?php if ($op['available']['activity']): ?>
<?php $activityRows=array_slice($op['groups']['recruiter'],(new Pager(count($op['groups']['recruiter']),10,(int) $f['comparisonPage']))->getThisPageStartRow(),10); echo Charts::render('operations-activity-recruiters', Charts::operationsActivity($activityRows)); ?>
<?php endif; ?>
</div></section>
<?php endif; ?>
<?php if ($f['view']!=='overview'): ?>
<section class="card"><h2 class="card-header h6 mb-0"><?php echo $e(array('activity'=>'Recorded Activity — Details','jobs'=>'Desk Workload — Active Jobs','tasks'=>'Follow-up & Candidate Care — Outstanding Tasks','warnings'=>'Warnings & Follow-up — Records Needing Attention')[$f['view']]); ?></h2>
<div class="card-body p-2">
<?php if ($f['view']==='tasks'): ?><p class="small">Outstanding tasks include work not started and work in progress. Past-due tasks need follow-up; missing due dates are shown as Unknown.</p><?php endif; ?>
<?php if ($f['view']==='jobs'): ?><p class="small">Active jobs by recruiter and job desk. Created dates show when records were added; see Warnings & Follow-up for jobs flagged for attention.</p><?php endif; ?>
<?php if (!$op['available'][$f['view']]): ?><p role="status">N/A — these figures are not available with your access.</p><?php else: ?>
<p class="small">Open a record to investigate. Links open another tab and keep your report filters here.</p>
<div class="table-responsive"><table class="table table-sm table-striped"><thead><tr><th scope="col">Record</th><th scope="col"><?php echo $f['view']==='activity' ? 'Recorded by' : 'Recruiter / responsible person'; ?></th><th scope="col">Job Desk</th><th scope="col"><?php echo $f['view']==='tasks' ? 'Due date' : ($f['view']==='jobs' ? 'Created' : 'Recorded / issue date'); ?></th><th scope="col">Activity / follow-up</th></tr></thead><tbody>
<?php foreach ($op['details'] as $row): ?><tr><td><a href="<?php echo Template::escapeAttr($row['url']); ?>" target="_blank" rel="noopener"><?php echo $e($row['title']); ?></a> <span class="small text-body-secondary">#<?php echo (int) $row['id']; ?></span></td><td><?php echo $e($row['actorName']); ?></td><td><?php echo $e($deskLabel($row['deskName'])); ?></td><td><?php echo $e($row['sourceDate'] ?? 'Unknown'); ?></td><td><?php $cue=$rowCue($row); if ($cue!==null): ?><span class="reports-cue <?php echo $cue['class']; ?>"><?php echo $cue['label']; ?></span> <?php endif; ?><?php echo $e($recordStatus($row)); ?></td></tr><?php endforeach; ?>
<?php if (!$op['details']): ?><tr><td colspan="5">No records to show on this page.</td></tr><?php endif; ?>
</tbody></table></div>
<nav class="small d-flex gap-2" aria-label="Contributing records pages"><span><?php echo $op['pager']->getTotalRows(); ?> records · page <?php echo (int) $f['page']; ?> of <?php echo $op['pager']->getTotalPages(); ?></span>
<?php if ((int) $f['page']>1): ?><a href="<?php echo Template::escapeAttr($url(array('page'=>(int) $f['page']-1))); ?>">Previous records</a><?php endif; ?>
<?php if ((int) $f['page']<$op['pager']->getTotalPages()): ?><a href="<?php echo Template::escapeAttr($url(array('page'=>(int) $f['page']+1))); ?>">Next records</a><?php endif; ?>
<a class="ms-auto" href="<?php echo Template::escapeAttr($url(array('view'=>'overview','page'=>'1'))); ?>">Return to overview</a></nav>
<?php endif; ?>
</div></section>
<?php endif; ?>
<?php endif; ?>
</section>
