<?php TemplateUtility::printHeader('User handover'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<main id="main" class="container-fluid py-2">
<header class="oc-page-header mb-2"><h1 class="h5 fw-semibold mb-0">Settings: Users: Handover</h1></header>
<p>Transfer selected current responsibilities. Historical attribution, Activities, completed Tasks, placements and personal warning reviews stay with their original users. Desks and Sectors are unchanged.</p>
<?php if ($this->message !== ''): ?><p class="alert alert-info" role="status"><?php echo Template::escapeHtml($this->message); ?></p><?php endif; ?>
<form method="post" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName()); ?>?m=settings&amp;a=handover">
<input type="hidden" name="postback" value="postback">
<input type="hidden" name="csrfToken" value="<?php echo Template::escapeAttr($_SESSION['CATS']->getCSRFToken()); ?>">
<div class="row g-2 mb-2 align-items-center">
<label class="col-sm-3 col-form-label" for="handoverFrom">Departing user</label>
<div class="col-sm-5"><select class="form-select form-select-sm" name="from" id="handoverFrom" required>
<option value="">Select user</option>
<?php foreach ($this->users as $user): ?>
<option value="<?php echo (int) $user['userID']; ?>"<?php if ($this->selection['from'] == $user['userID']) echo ' selected'; ?>><?php echo Template::escapeHtml($user['firstName'] . ' ' . $user['lastName'] . ($user['accessLevel'] <= ACCESS_LEVEL_DISABLED ? ' (disabled)' : '')); ?></option>
<?php endforeach; ?>
</select></div>
<div class="col-sm-4"><button class="btn btn-outline-danger btn-sm" name="operation" value="disable" type="submit" formnovalidate>Disable account now</button></div>
</div>
<p class="form-text">Disabling access is independent of transfer. It keeps the user and their records and takes effect on their next request. Your own account cannot be disabled here.</p>
<div class="row g-2 mb-3">
<label class="col-sm-3 col-form-label" for="handoverDefault">Default replacement</label>
<div class="col-sm-5"><select class="form-select form-select-sm" name="default" id="handoverDefault" required>
<option value="">Select active user</option>
<?php foreach ($this->replacements as $user): ?>
<option value="<?php echo (int) $user['userID']; ?>"<?php if ($this->selection['default'] == $user['userID']) echo ' selected'; ?>><?php echo Template::escapeHtml($user['firstName'] . ' ' . $user['lastName']); ?></option>
<?php endforeach; ?>
</select></div></div>
<fieldset class="mb-3"><legend class="h6">Responsibilities to transfer</legend>
<?php foreach ($this->categories as $key => $category): ?>
<div class="row g-2 mb-2 align-items-center">
<div class="col-sm-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="categories[]" id="handover<?php echo Template::escapeAttr($key); ?>" value="<?php echo Template::escapeAttr($key); ?>"<?php if (in_array($key, $this->selection['categories'], true)) echo ' checked'; ?>><label class="form-check-label" for="handover<?php echo Template::escapeAttr($key); ?>"><?php echo Template::escapeHtml($category['label']); ?></label></div></div>
<label class="col-sm-2 col-form-label" for="replacement<?php echo Template::escapeAttr($key); ?>">Replacement</label>
<div class="col-sm-3"><select class="form-select form-select-sm" name="overrides[<?php echo Template::escapeAttr($key); ?>]" id="replacement<?php echo Template::escapeAttr($key); ?>">
<option value="">Use default replacement</option>
<?php foreach ($this->replacements as $user): ?>
<option value="<?php echo (int) $user['userID']; ?>"<?php if (($this->selection['overrides'][$key] ?? '') == $user['userID']) echo ' selected'; ?>><?php echo Template::escapeHtml($user['firstName'] . ' ' . $user['lastName']); ?></option>
<?php endforeach; ?>
</select></div></div>
<?php endforeach; ?>
</fieldset>
<p class="form-text">Open/In Progress Tasks; active Candidates; Contacts who have not left; Companies except Dormant/Lost lifecycles; actionable Active Job Orders. Owner and recruiter are independent.</p>
<button class="btn btn-primary btn-sm" type="submit" name="operation" value="preview">Preview from first page</button>
<a class="btn btn-outline-secondary btn-sm" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName()); ?>?m=settings&amp;a=manageUsers">Users</a>
</form>
<?php if ($this->preview): ?>
<h2 class="h6 mt-4">Preview for departing user #<?php echo (int) $this->preview['from']; ?></h2>
<p>Counts below cover this page: at most 50 visible records per category. Confirmation transfers only records marked eligible on this page. Use Next page to inspect further records. Preview makes no database changes.</p>
<?php foreach ($this->preview['categories'] as $key => $page): ?>
<h3 class="h6 mt-3"><?php echo Template::escapeHtml($this->categories[$key]['label']); ?> → <?php echo Template::escapeHtml($page['recipient']); ?> (#<?php echo (int) $page['to']; ?>)</h3>
<p><?php echo (int) $page['eligible']; ?> eligible; <?php echo (int) $page['excluded']; ?> excluded; <?php echo (int) $page['blocked']; ?> cannot transfer. <?php if ($page['more']): ?>More visible records on the next page.<?php endif; ?></p>
<div class="table-responsive"><table class="table table-sm table-striped align-middle"><thead><tr><th>ID</th><th>Record</th><th>Outcome</th><th>Reason</th></tr></thead><tbody>
<?php foreach ($page['rows'] as $row): ?><tr><td><?php echo (int) $row['id']; ?></td><td><?php echo Template::escapeHtml($row['label']); ?></td><td><?php echo Template::escapeHtml($row['state']); ?></td><td><?php echo Template::escapeHtml($row['reason']); ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php endforeach; ?>
<h3 class="h6 mt-3">Future Calendar appointments requiring attention</h3>
<p><?php echo min(50, count($this->calendar)); ?> visible appointments shown<?php if (count($this->calendar) > 50): ?>; more are available in Calendar<?php endif; ?>. Calendar appointments will not be reassigned.</p>
<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Appointment</th><th>Date (UTC)</th><th>Action</th></tr></thead><tbody>
<?php foreach (array_slice($this->calendar, 0, 50) as $event): ?><tr><td><?php echo Template::escapeHtml($event['title']); ?></td><td><?php echo Template::escapeHtml($event['date']); ?></td><td>Review in Calendar</td></tr><?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
<?php if ($this->results !== null): ?>
<h2 class="h6 mt-4">Actual results</h2>
<?php foreach ($this->results as $key => $rows): ?>
<h3 class="h6"><?php echo Template::escapeHtml($this->categories[$key]['label']); ?></h3>
<p><?php echo count(array_filter($rows, fn($row) => $row['state'] === 'transferred')); ?> transferred;
<?php echo count(array_filter($rows, fn($row) => $row['state'] === 'skipped')); ?> skipped;
<?php echo count(array_filter($rows, fn($row) => $row['state'] === 'failed')); ?> failed.</p>
<div class="table-responsive"><table class="table table-sm table-striped"><thead><tr><th>Record ID</th><th>Result</th><th>Reason</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><td><?php echo $row['id'] === null ? 'Unavailable' : (int) $row['id']; ?></td><td><?php echo Template::escapeHtml($row['state']); ?></td><td><?php echo Template::escapeHtml($row['reason']); ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php endforeach; ?>
<?php endif; ?>
<?php if ($this->savedPreview): ?>
<form class="mt-3" method="post" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName()); ?>?m=settings&amp;a=handover">
<input type="hidden" name="postback" value="postback">
<input type="hidden" name="csrfToken" value="<?php echo Template::escapeAttr($_SESSION['CATS']->getCSRFToken()); ?>">
<input type="hidden" name="previewToken" value="<?php echo Template::escapeAttr($this->savedPreview['token']); ?>">
<?php if (!$this->savedPreview['executed']): ?><button class="btn btn-primary btn-sm" type="submit" name="operation" value="execute">Confirm eligible transfers on this page</button><?php endif; ?>
<?php if (array_filter($this->savedPreview['preview']['categories'], fn($page) => $page['more'])): ?><button class="btn btn-outline-secondary btn-sm" type="submit" name="operation" value="next">Next preview page</button><?php endif; ?>
</form>
<?php endif; ?>
</main>
<?php TemplateUtility::printFooter(); ?>
