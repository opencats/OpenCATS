<?php $reviewReturnTo = $reviewReturnTo ?? 'companies'; ?>
<?php include(LEGACY_ROOT . '/modules/home/WarningReviewNotice.tpl'); ?>
<?php if ($this->recruitmentWarnings['warnings']): ?>
<section class="card mb-3">
    <div class="card-header"><h2 class="h6 mb-0">Personal warning reviews</h2></div>
    <div class="card-body">
        <?php foreach ($this->recruitmentWarnings['warnings'] as $warning): ?>
        <div class="mb-2">
            <strong><?php echo Template::escapeHtml($warning['code']); ?> — <?php echo Template::escapeHtml(str_replace('_', ' ', $warning['reason'])); ?></strong>
            <?php if ($warning['code'] !== 'W07'): ?>
            <p class="small mb-1">Overdue since <?php echo Template::escapeHtml(TaskPresentation::dateTime($warning['deadline'])); ?><?php if ($warning['coverage'] !== 'uncovered'): ?> — <a href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&a=show&taskID=' . (int) $warning['taskID']); ?>"><?php echo $warning['coverage'] === 'covered' ? 'Open follow-up Task' : 'Overdue follow-up Task'; ?></a><?php endif; ?></p>
            <?php else: ?>
            <p class="small mb-1"><?php echo Template::escapeHtml(implode(', ', array_map(fn($reason) => str_replace('_', ' ', $reason), $warning['relationshipAttention']['reasons']))); ?><?php if ($warning['relationshipAttention']['tasks']): ?>; Tasks <?php echo Template::escapeHtml(implode(', ', array_column($warning['relationshipAttention']['tasks'], 'taskID'))); ?><?php endif; ?></p>
            <?php endif; ?>
            <?php foreach ($this->recruitmentWarnings['reviews'][WarningReviews::reviewKey($warning)] ?? array() as $review): ?>
                <?php include(LEGACY_ROOT . '/modules/home/WarningReviewControls.tpl'); ?>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
<?php include(LEGACY_ROOT . '/modules/home/WarningReviewModal.tpl'); ?>
