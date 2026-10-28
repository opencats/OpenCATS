<?php $out = $op['outcomes']; $definitions = $out['definitions']; ?>
<section aria-labelledby="outcomes-title" class="card mb-2">
<h2 id="outcomes-title" class="card-header h6 mb-0">Recruitment Progress &amp; Outcomes</h2>
<div class="card-body p-2">
<p class="small mb-1">Recorded milestones · distinct candidate–job pairs in each period. One candidate submitted to two jobs counts twice. Repeat entries to the same stage within a period count once.</p>
<p class="small mb-1"><strong>Event actor: Unknown.</strong> Current owners and pipeline adders receive no event credit. Desk breakdowns use the Job Order’s current Desk and may change after transfer.</p>
<p class="small mb-2">These are retained status changes, not conversion rates or confirmed employment starts. Later reversals do not erase recorded attainment. Deleted or newly restricted records can change earlier totals.</p>
<div class="row g-2 mb-2">
<?php foreach ($definitions as $key=>$definition): ?>
<div class="col-6 col-xl-3"><div class="border rounded p-2 h-100"><div class="small"><?php echo $e($definition['label']); ?></div>
<div class="h4 mb-0"><?php if ($out['available'][$key]): ?><a href="<?php echo Template::escapeAttr($url(array('milestone'=>$key,'page'=>'1'))); ?>"><?php echo $out['totals'][$key]; ?></a><?php else: ?>N/A<?php endif; ?></div>
<div class="small text-body-secondary"><?php echo $e($definition['configuredLabel'] === null ? 'Configured stage unavailable' : 'Recorded stage: ' . $definition['configuredLabel']); ?></div></div></div>
<?php endforeach; ?>
</div>
<?php $comparison=$out['comparison']; ?>
<div class="table-responsive"><table class="table table-sm table-striped" aria-label="Recruitment outcome period comparisons">
<caption class="small caption-top">Equivalent elapsed periods · <?php echo $comparison['days']; ?> calendar days each. Today may be incomplete.
Current: <?php echo $e($comparison['currentStart'].' to '.$comparison['currentEnd']); ?> · Previous: <?php echo $e($comparison['previousStart'].' to '.$comparison['previousEnd']); ?>.</caption>
<thead><tr><th>Recorded milestone</th><th>Selected period</th><th>Comparable current</th><th>Previous</th><th>Change</th><th>Change %</th></tr></thead><tbody>
<?php foreach ($definitions as $key=>$definition): $change=$out['changes'][$key]; ?>
<tr><th scope="row" class="fw-normal"><?php echo $e($definition['label']); ?></th>
<?php if ($out['available'][$key]): ?><td><?php echo $out['totals'][$key]; ?></td><td><?php echo $change['current']; ?></td><td><?php echo $change['previous']; ?></td><td><?php echo ($change['absolute']>0?'+':'').$change['absolute']; ?></td><td><?php echo $change['percent']===null?'N/A':$e(number_format($change['percent'],1).'%'); ?></td>
<?php else: ?><td colspan="5">N/A — source access or configured stage unavailable</td><?php endif; ?></tr>
<?php endforeach; ?>
</tbody></table></div>
<p class="small mb-2">Percentage change is N/A when the previous value is zero. Changes describe recorded volume; they are not performance ratings.
<?php if ($comparison['currentEnd']<$f['end']): ?>The previous period is shorter, so both comparison windows are capped. Selected-period totals still include all selected dates.<?php endif; ?>
Dates use the report session offset (<?php echo $e(($out['timeZoneOffset']>=0?'+':'').$out['timeZoneOffset']); ?> hours from system time); historical daylight-saving offsets are not reconstructed.</p>
<?php $chartDefinitions=array_filter($definitions,fn($definition,$key)=>$out['available'][$key],ARRAY_FILTER_USE_BOTH); ?>
<?php if ($chartDefinitions): ?>
<section class="mb-2" aria-label="Recorded milestone charts"><div class="row g-2 pt-2">
<div class="col-12 col-xl-6"><?php echo Charts::render('outcomes-trend',Charts::recruitmentOutcomes($out['weeks'],$chartDefinitions,true)); ?></div>
<div class="col-12 col-xl-6">
<?php $deskPager=new Pager(count($out['desks']),10,(int) $f['comparisonPage']); $deskRows=array_slice($out['desks'],$deskPager->getThisPageStartRow(),10,true); ?>
<?php echo Charts::render('outcomes-desks',Charts::recruitmentOutcomes($deskRows,$chartDefinitions)); ?>
<p class="small">Current Job Desk · <?php echo count($deskRows); ?> of <?php echo count($out['desks']); ?> Desks shown. Each chart has exact values; all Desk pages remain available.</p>
<nav class="small d-flex gap-2" aria-label="Outcome Desk pages">
<?php if ($deskPager->getCurrentPage()>1): ?><a href="<?php echo Template::escapeAttr($url(array('comparisonPage'=>$deskPager->getCurrentPage()-1))); ?>">Previous Desks</a><?php endif; ?>
<?php if ($deskPager->getCurrentPage()<$deskPager->getTotalPages()): ?><a href="<?php echo Template::escapeAttr($url(array('comparisonPage'=>$deskPager->getCurrentPage()+1))); ?>">Next Desks</a><?php endif; ?>
</nav></div></div></section>
<?php else: ?><p role="status">N/A — recruitment outcomes are not available with your access or configured stages.</p><?php endif; ?>
<details class="small mb-2"><summary>Unavailable measures</summary><p class="mt-1 mb-0">Confirmed starts, full-vacancy fill rates, acquisition and placement credit are unavailable. A Placed status does not confirm employment started or every opening was filled. No recruiter targets or performance traffic lights are applied.</p></details>
</div></section>
<section class="card" aria-labelledby="outcomes-records"><h2 id="outcomes-records" class="card-header h6 mb-0">Contributing candidate–job pairs</h2><div class="card-body p-2">
<nav class="d-flex flex-wrap gap-2 mb-2" aria-label="Outcome milestones">
<?php foreach ($definitions as $key=>$definition): ?><a class="btn btn-sm <?php echo $key===$f['milestone']?'btn-success':'btn-outline-secondary'; ?>" <?php if ($key===$f['milestone']) echo 'aria-current="page"'; ?> href="<?php echo Template::escapeAttr($url(array('milestone'=>$key,'page'=>'1'))); ?>"><?php echo $e($definition['label']); ?></a><?php endforeach; ?>
</nav>
<?php if (!$out['available'][$f['milestone']]): ?><p role="status">N/A — this milestone is not available with your access or configured stages.</p><?php else: ?>
<p class="small mb-2">First recorded attainment within the selected dates, not necessarily the first ever. Repeated transitions are shown separately. Links open another tab and retain the report filters here. Speculative classification is current.</p>
<div class="table-responsive"><table class="table table-sm table-striped" aria-label="Contributing recruitment outcomes"><thead><tr>
<?php foreach (array('candidate'=>'Candidate','job'=>'Job Order','date'=>'First recorded date','repeats'=>'Recorded entries') as $sort=>$label): ?>
<th scope="col"><a href="<?php echo Template::escapeAttr($url(array('outcomeSort'=>$sort,'outcomeDirection'=>$f['outcomeSort']===$sort && $f['outcomeDirection']==='asc'?'desc':'asc','page'=>'1'))); ?>"><?php echo $label; ?></a><?php if ($f['outcomeSort']===$sort) echo $f['outcomeDirection']==='asc'?' ↑':' ↓'; ?></th>
<?php endforeach; ?><th scope="col">Current Job Desk</th><th scope="col">Current vacancy classification</th><th scope="col">Event actor</th></tr></thead><tbody>
<?php foreach ($out['details'] as $row): ?><tr>
<td><a href="<?php echo Template::escapeAttr(CATSUtility::getIndexName().'?m=candidates&a=show&candidateID='.(int) $row['candidateID']); ?>" target="_blank" rel="noopener"><?php echo $e($row['title']); ?></a></td>
<td><a href="<?php echo Template::escapeAttr(CATSUtility::getIndexName().'?m=joborders&a=show&jobOrderID='.(int) $row['jobID']); ?>" target="_blank" rel="noopener"><?php echo $e($row['jobTitle']); ?></a></td>
<td><?php echo $e($row['sourceDate']); ?></td><td><?php echo (int) $row['repeats']; ?></td><td><?php echo $e($row['deskName']); ?></td><td><?php echo (int) $row['isSpeculative']===1?'Speculative':'Genuine'; ?></td><td>Unknown</td></tr><?php endforeach; ?>
<?php if (!$out['details']): ?><tr><td colspan="7">No recorded milestones match this selection.</td></tr><?php endif; ?>
</tbody></table></div>
<nav class="small d-flex gap-2" aria-label="Outcome record pages"><span><?php echo $out['pager']->getTotalRows(); ?> pairs · page <?php echo $out['pager']->getCurrentPage(); ?> of <?php echo $out['pager']->getTotalPages(); ?></span>
<?php if ($out['pager']->getCurrentPage()>1): ?><a href="<?php echo Template::escapeAttr($url(array('page'=>$out['pager']->getCurrentPage()-1))); ?>">Previous pairs</a><?php endif; ?>
<?php if ($out['pager']->getCurrentPage()<$out['pager']->getTotalPages()): ?><a href="<?php echo Template::escapeAttr($url(array('page'=>$out['pager']->getCurrentPage()+1))); ?>">Next pairs</a><?php endif; ?>
</nav>
<?php endif; ?></div></section>
