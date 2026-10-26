<?php if (($this->homeAccounts ?? null) !== null || ($this->homeJobs ?? null) !== null || ($this->homeCalendar ?? null) !== null): ?>
<div class="home-support row g-3 mb-2" aria-label="Supporting work">
<?php if (($this->homeAccounts ?? null) !== null): ?>
<section class="col-12 col-lg-4" aria-labelledby="home-accounts-heading"><div class="card h-100"><div class="card-body p-3">
<h2 class="h6 mb-1" id="home-accounts-heading">My accounts by tier</h2>
<p class="small text-body-secondary mb-2">Owned by me</p>
<ul class="list-unstyled mb-2">
<?php foreach ($this->homeAccounts as $account): ?>
<li class="border-top py-2"><a href="<?php echo Template::escapeAttr(TaskPresentation::parentURL(DATA_ITEM_COMPANY, $account['companyID'])); ?>"><?php echo Template::escapeHtml($account['name']); ?></a><span class="d-block text-body-secondary"><?php echo Template::escapeHtml($account['tierLabel'] . ($account['relationshipStatus'] ? ' · ' . $account['relationshipStatus'] : '')); ?></span><?php if (array_key_exists('lastContact', $account)): ?><span class="d-block text-body-secondary"><?php echo $account['lastContact'] === null ? 'No qualifying contact recorded' : 'Last qualifying contact: ' . Template::escapeHtml(TaskPresentation::date(substr($account['lastContact'], 0, 10))); ?></span><?php endif; ?></li>
<?php endforeach; ?>
</ul>
<?php if (!$this->homeAccounts): ?><p class="text-body-secondary">No classified accounts in this view.</p><?php endif; ?>
<a href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=companies'); ?>">All Companies</a>
</div></div></section>
<?php endif; ?>
<?php if (($this->homeJobs ?? null) !== null): ?>
<section class="col-12 col-lg-4" aria-labelledby="home-current-jobs"><div class="card h-100"><div class="card-body p-3">
<h2 class="h6 mb-1" id="home-current-jobs" tabindex="-1">My current jobs</h2>
<p class="small text-body-secondary mb-2">Owner or recruiter &middot; <?php echo count($this->homeJobs['rows']); ?> of <?php echo (int) $this->homeJobs['total']; ?></p>
<ul class="list-unstyled mb-2">
<?php foreach ($this->homeJobs['rows'] as $job): ?>
<li class="border-top py-2"><a href="<?php echo Template::escapeAttr(TaskPresentation::parentURL(DATA_ITEM_JOBORDER, $job['jobOrderID'])); ?>"><?php echo Template::escapeHtml($job['title']); ?></a>
<?php if ($job['companyName'] !== null): ?><span class="d-block text-body-secondary"><?php echo Template::escapeHtml($job['companyName']); ?></span><?php endif; ?>
<span class="d-block"><?php echo Template::escapeHtml($job['status']); ?><?php if ($job['daysOld'] !== null && $job['daysOld'] >= 0): ?> &middot; Created <?php echo (int) $job['daysOld']; ?> calendar days ago<?php endif; ?></span></li>
<?php endforeach; ?>
</ul>
<?php if (!$this->homeJobs['rows']): ?><p class="text-body-secondary">No current jobs in this view.</p><?php endif; ?>
<a href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=joborders'); ?>">All Job Orders</a>
</div></div></section>
<?php endif; ?>
<?php if (($this->homeCalendar ?? null) !== null): ?>
<section class="col-12 col-lg-4" aria-labelledby="home-calendar-heading"><div class="card h-100"><div class="card-body p-3">
<h2 class="h6 mb-1" id="home-calendar-heading">My Calendar</h2>
<p class="small text-body-secondary mb-2">Today and upcoming</p>
<ul class="list-unstyled mb-2">
<?php foreach ($this->homeCalendar as $event): $date = substr($event['date'], 0, 10); ?>
<li class="border-top py-2"><span class="d-block text-body-secondary"><?php echo Template::escapeHtml(TaskPresentation::date($date) . ' · ' . ($event['allDay'] ? 'All day' : $event['time'])); ?></span><a href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=calendar&view=DAYVIEW&year=' . substr($date, 0, 4) . '&month=' . (int) substr($date, 5, 2) . '&day=' . (int) substr($date, 8, 2) . '&showEvent=' . (int) $event['eventID']); ?>"><?php echo Template::escapeHtml($event['title']); ?></a></li>
<?php endforeach; ?>
</ul>
<?php if (!$this->homeCalendar): ?><p class="text-body-secondary">No upcoming appointments.</p><?php endif; ?>
<a href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=calendar'); ?>">Open Calendar</a>
</div></div></section>
<?php endif; ?>
</div>
<?php endif; ?>
