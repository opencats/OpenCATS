<?php TemplateUtility::printHeader('Warning oversight'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">
<div id="contents">
    <header class="oc-page-header mb-2"><h1 class="h5 fw-semibold mb-0">Warning oversight</h1></header>
    <?php include(LEGACY_ROOT . '/modules/home/WarningReviewNotice.tpl'); ?>
    <p>Current source conditions within your existing warning visibility. Select a Company ID to inspect that Company under its native read permissions. Personal suppression does not remove these conditions.</p>
    <form method="get" class="row g-2 mb-2">
        <input type="hidden" name="m" value="reports"><input type="hidden" name="a" value="warningOversight">
        <?php foreach (array('rule' => array('' => 'All warning types', 'W01' => 'W01', 'W02' => 'W02', 'W03' => 'W03', 'W07' => 'W07', 'W08' => 'W08'),
            'state' => array('' => 'All dispositions', 'outstanding' => 'Outstanding source conditions', 'remind_later' => 'Remind Later', 'close' => 'Temporarily Closed', 'reappeared' => 'Expired / reappeared'),
            'sort' => array('source' => 'Warning source', 'action' => 'Latest action')) as $field => $options): ?>
        <div class="col-12 col-md-3"><label class="form-label" for="oversight-<?php echo $field; ?>"><?php echo Template::escapeHtml(ucfirst($field)); ?></label>
        <select class="form-select" id="oversight-<?php echo $field; ?>" name="<?php echo $field; ?>">
            <?php foreach ($options as $value => $label): ?><option value="<?php echo Template::escapeAttr($value); ?>" <?php if ($this->filters[$field] === $value) echo 'selected'; ?>><?php echo Template::escapeHtml($label); ?></option><?php endforeach; ?>
        </select></div>
        <?php endforeach; ?>
        <div class="col-12 col-md-3"><label class="form-label" for="oversight-reviewer">Reviewing user ID (0 = all)</label>
            <input class="form-control" id="oversight-reviewer" name="reviewer" type="number" min="0" max="2147483647" value="<?php echo (int) $this->filters['reviewer']; ?>"></div>
        <div class="col-12 col-md-3"><label class="form-label" for="oversight-company">Company ID (0 = default queue)</label>
            <input class="form-control" id="oversight-company" name="companyID" type="number" min="0" max="2147483647" value="<?php echo (int) $this->filters['companyID']; ?>"></div>
        <div class="col-12"><button class="btn btn-sm btn-primary" type="submit">Apply filters</button></div>
    </form>
    <p>Distinct warning subjects/reasons: outstanding <?php echo (int) $this->oversight['counts']['outstanding']; ?>;
        Remind Later <?php echo (int) $this->oversight['counts']['remind_later']; ?>;
        Closed <?php echo (int) $this->oversight['counts']['close']; ?>;
        expired/reappeared <?php echo (int) $this->oversight['counts']['reappeared']; ?>.</p>
    <p>Categories can overlap across recruiters. W07 reasons are independent. Review-action events for these current subjects since
        <?php echo Template::escapeHtml(TaskPresentation::date($this->oversight['since'])); ?>: <?php echo (int) $this->oversight['events']; ?>.
        Event counts follow warning type and reviewer filters.</p>
    <div class="table-responsive"><table class="table table-sm table-striped align-middle">
        <thead><tr><th>Warning / current condition</th><th>Related record</th><th>Responsible user</th><th>Personal disposition / reviewer</th><th>Action / next review</th><th>History / reset</th></tr></thead>
        <tbody>
        <?php foreach ($this->oversight['rows'] as $row):
            $warning = $row['warning']; $identity = $row['identity']; $state = $row['state'];
            $reviewer = $state ? (int) $state['reviewing_user'] : 0;
        ?>
        <tr>
            <td><?php echo Template::escapeHtml($identity['warning_rule'] . ' — ' . ucwords(str_replace('_', ' ', $identity['reason_key']))); ?>
                <?php if ($identity['reason_id']): ?><br>Task #<?php echo (int) $identity['reason_id']; ?><?php endif; ?>
                <?php if ($warning['sourceDate']): ?><br>Source date: <?php echo Template::escapeHtml(TaskPresentation::date(substr($warning['sourceDate'], 0, 10))); ?><?php endif; ?>
            </td>
            <td><a href="<?php echo Template::escapeAttr(($identity['data_item_type'] === DATA_ITEM_TASK ? CATSUtility::getIndexName() . '?m=tasks&a=show&taskID=' . (int) $identity['data_item_id'] : TaskPresentation::parentURL($identity['data_item_type'], $identity['data_item_id']))); ?>"><?php echo Template::escapeHtml($warning['title']); ?></a></td>
            <td><?php echo Template::escapeHtml($this->oversight['names'][$warning['responsibleUserID']] ?? ($warning['responsibleUserID'] ? '#' . $warning['responsibleUserID'] : 'Unassigned')); ?></td>
            <td><?php echo Template::escapeHtml(array('outstanding' => 'Active', 'remind_later' => 'Remind Later', 'close' => 'Temporarily Closed', 'reappeared' => 'Expired / evidence changed')[$row['status']]); ?>
                <?php if ($state): ?><br><?php echo Template::escapeHtml($this->oversight['names'][$reviewer] ?? '#' . $reviewer); ?>
                <br><?php echo Template::escapeHtml(ucwords(str_replace('_', ' ', $state['action']))); ?>
                <?php if ($state['close_reason']): ?><br><?php echo Template::escapeHtml(ucwords(str_replace('_', ' ', $state['close_reason']))); ?><?php endif; ?><?php endif; ?>
            </td>
            <td><?php if ($state): echo Template::escapeHtml(TaskPresentation::dateTime($state['action_at'])); ?>
                <br><?php echo Template::escapeHtml(TaskPresentation::date($state['review_until'])); ?><?php endif; ?></td>
            <td><?php if ($state):
                $historyParams = array('m' => 'reports', 'a' => 'warningOversight', 'rule' => $identity['warning_rule'], 'reviewer' => $reviewer,
                    'companyID' => $identity['data_item_type'] === DATA_ITEM_COMPANY ? $identity['data_item_id'] : 0,
                    'sourceType' => $identity['data_item_type'], 'sourceID' => $identity['data_item_id'], 'reason' => $identity['reason_key'], 'reasonID' => $identity['reason_id']); ?>
                <a href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?' . http_build_query($historyParams)); ?>">Review history</a>
                <?php if ($_SESSION['CATS']->getRealAccessLevel() >= ACCESS_LEVEL_SA && $state['action'] !== 'reset'):
                    $reviewForm = array_merge($identity, array('reviewing_user' => $reviewer, 'expectedVersion' => (int) $state['version'],
                        'expectedEvidence' => $row['evidence'], 'requestID' => bin2hex(random_bytes(16)), 'returnTo' => 'reports')); ?>
                <form method="post" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=home&a=reviewWarning'); ?>">
                    <?php include(LEGACY_ROOT . '/modules/home/WarningReviewFields.tpl'); ?>
                    <button class="btn btn-sm btn-outline-secondary" name="reviewAction" value="reset">Reset personal review</button>
                </form>
                <?php endif; endif; ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$this->oversight['rows']): ?><tr><td colspan="6">No accessible warnings match these filters.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
    <nav aria-label="Warning oversight pages" class="d-flex gap-2">
        <?php foreach (array(-1 => 'Previous', 1 => 'Next') as $step => $label):
            $page = $this->pager->getCurrentPage() + $step;
            if ($page < 1 || $page > $this->pager->getTotalPages()) continue;
            $params = array_merge($this->filters, array('m' => 'reports', 'a' => 'warningOversight', 'page' => $page)); ?>
        <a class="btn btn-sm btn-outline-secondary" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?' . http_build_query($params)); ?>"><?php echo $label; ?></a>
        <?php endforeach; ?>
        <span>Page <?php echo (int) $this->pager->getCurrentPage(); ?> of <?php echo (int) $this->pager->getTotalPages(); ?></span>
    </nav>
    <?php if ($this->oversightHistory): ?>
    <section class="card mt-2"><h2 class="card-header h6">Warning review history — <?php echo Template::escapeHtml($this->historyLabel); ?></h2><div class="table-responsive">
        <table class="table table-sm mb-0"><thead><tr><th>Action</th><th>Close reason</th><th>Next review</th><th>Action time</th><th>Acting user</th></tr></thead><tbody>
        <?php foreach ($this->oversightHistory as $event): ?><tr>
            <td><?php echo Template::escapeHtml(ucwords(str_replace('_', ' ', $event['action']))); ?></td>
            <td><?php echo Template::escapeHtml(ucwords(str_replace('_', ' ', $event['closeReason'] ?? ''))); ?></td>
            <td><?php echo Template::escapeHtml(TaskPresentation::date($event['reviewUntil'])); ?></td>
            <td><?php echo Template::escapeHtml($event['date']); ?></td><td><?php echo Template::escapeHtml($event['actor']); ?></td>
        </tr><?php endforeach; ?></tbody></table>
    </div></section>
    <?php endif; ?>
</div>
</main>
<?php TemplateUtility::printFooter(); ?>
