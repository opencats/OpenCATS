<?php $reviewReturnTo = 'home'; ?>
<div class="col-12">
    <section class="card" aria-labelledby="recruitmentWarningsHeading">
        <h2 id="recruitmentWarningsHeading" class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">My Recruitment Warnings</h2>
        <div class="card-body p-2">
            <?php include(LEGACY_ROOT . '/modules/home/WarningReviewNotice.tpl'); ?>
            <ul class="list-inline mb-2">
                <?php foreach ($this->recruitmentWarnings['visibleCounts'] as $code => $count): ?>
                    <li class="list-inline-item"><strong><?php echo Template::escapeHtml($code); ?></strong>: <?php echo (int) $count; ?></li>
                <?php endforeach; ?>
            </ul>
            <p class="small mb-2"><?php echo array_sum($this->recruitmentWarnings['visibleCounts']); ?> visible warnings;
                <?php echo (int) $this->recruitmentWarnings['suppressedReasonCount']; ?> personally suppressed reasons.
                Underlying authorised warnings: <?php echo array_sum($this->recruitmentWarnings['counts']); ?>;
                Jobs at risk: <?php echo (int) $this->recruitmentWarnings['jobsAtRisk']; ?>.</p>
            <?php if ($this->recruitmentWarnings['visibleWarnings']): ?>
                <ul class="mb-0">
                    <?php foreach (array_slice($this->recruitmentWarnings['visibleWarnings'], 0, 20) as $warning): ?>
                        <?php $source = array(DATA_ITEM_COMPANY => array('companies', 'companyID'), DATA_ITEM_JOBORDER => array('joborders', 'jobOrderID'), DATA_ITEM_TASK => array('tasks', 'taskID'))[$warning['entityType']]; ?>
                        <li>
                            <?php echo Template::escapeHtml($warning['code']); ?> —
                            <a href="<?php echo CATSUtility::getIndexName(); ?>?m=<?php echo $source[0]; ?>&amp;a=show&amp;<?php echo $source[1]; ?>=<?php echo (int) $warning['entityID']; ?>"><?php echo Template::escapeHtml($warning['title']); ?></a>:
                            <?php echo Template::escapeHtml(str_replace('_', ' ', $warning['reason'])); ?>
                            <?php if ($warning['code'] === 'W07'): ?>
                                <span class="text-body-secondary">(Visible reasons: <?php echo Template::escapeHtml(implode(', ', array_map(function ($reason) { return str_replace('_', ' ', $reason); }, $warning['relationshipAttention']['reasons']))); ?><?php if ($warning['relationshipAttention']['tasks']): ?>;
                                    Tasks <?php echo Template::escapeHtml(implode(', ', array_column($warning['relationshipAttention']['tasks'], 'taskID'))); ?><?php endif; ?>)</span>
                            <?php endif; ?>
                            <?php foreach ($this->recruitmentWarnings['reviews'][WarningReviews::reviewKey($warning)] ?? array() as $review): ?>
                                <?php if (!$review['suppressed']) include(LEGACY_ROOT . '/modules/home/WarningReviewControls.tpl'); ?>
                            <?php endforeach; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if (count($this->recruitmentWarnings['visibleWarnings']) > 20): ?>
                    <p class="small mt-2 mb-0">Showing 20 of <?php echo count($this->recruitmentWarnings['visibleWarnings']); ?> visible warnings;
                        <?php echo count($this->recruitmentWarnings['visibleWarnings']) - 20; ?> additional warnings.</p>
                <?php endif; ?>
            <?php else: ?>
                <p class="mb-0">No visible recruitment warnings.</p>
            <?php endif; ?>
            <?php if ($this->recruitmentWarnings['suppressedWarnings']): ?>
            <button type="button" class="btn btn-sm btn-outline-secondary mt-3" data-bs-toggle="collapse" data-bs-target="#suppressedWarnings" aria-expanded="false" aria-controls="suppressedWarnings">Personally Reviewed / Remind Later (<?php echo (int) $this->recruitmentWarnings['suppressedReasonCount']; ?>)</button>
            <div class="collapse mt-2" id="suppressedWarnings">
                <ul class="mb-0">
                    <?php foreach ($this->recruitmentWarnings['suppressedWarnings'] as $suppressed):
                        $warning = $suppressed['warning'];
                        $review = $suppressed['review'];
                        $source = $warning['code'] === 'W07' ? array('companies', 'companyID') : array('joborders', 'jobOrderID');
                    ?>
                    <li>
                        <?php echo Template::escapeHtml($warning['code']); ?> —
                        <a href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=' . $source[0] . '&a=show&' . $source[1] . '=' . (int) $warning['entityID']); ?>"><?php echo Template::escapeHtml($warning['title']); ?></a>
                        <?php include(LEGACY_ROOT . '/modules/home/WarningReviewControls.tpl'); ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php include(LEGACY_ROOT . '/modules/home/WarningReviewModal.tpl'); ?>
