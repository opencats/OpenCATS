<?php if ($this->recruitmentWarnings || $this->data['w02ReviewedAt'] !== null || $this->data['w03ReviewedAt'] !== null): ?>
<section class="card mb-3">
    <div class="card-header"><h2 class="h6 mb-0">Recruitment reminders</h2></div>
    <div class="card-body">
    <?php foreach (array('W02' => 'No recorded submission', 'W03' => 'No recorded candidate progress') as $code => $label):
        $reviewed = $this->data[$code === 'W02' ? 'w02ReviewedAt' : 'w03ReviewedAt'];
        $until = DateUtility::addWorkingDays($reviewed, 5);
        $warning = $this->recruitmentWarnings[$code] ?? null;
        if ($warning === null && $reviewed === null) continue;
    ?>
        <div class="row align-items-center mb-3">
            <div class="col-md-7">
                <strong><?php echo Template::escapeHtml($label); ?></strong>
                <?php if ($until !== null && $reviewed <= $this->reviewClock && $this->reviewClock < $until): ?>
                <div class="text-muted">Reviewed until <?php echo Template::escapeHtml(TaskPresentation::dateTime($until)); ?></div>
                <?php elseif ($warning !== null): ?>
                <div class="text-muted">Overdue since <?php echo Template::escapeHtml(TaskPresentation::dateTime($warning['deadline'])); ?><?php if ($warning['coverage'] !== 'uncovered'): ?> — <a href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&a=show&taskID=' . (int) $warning['taskID']); ?>"><?php echo $warning['coverage'] === 'covered' ? 'Open follow-up Task' : 'Overdue follow-up Task'; ?></a><?php endif; ?></div>
                <?php elseif ($until !== null): ?><div class="text-muted">Review expired</div>
                <?php else: ?><div class="text-muted">Review date unavailable</div><?php endif; ?>
            </div>
            <?php if ($this->getUserAccessLevel('joborders.edit') >= ACCESS_LEVEL_EDIT): ?>
            <div class="col-md-5 mt-2 mt-md-0">
                <form method="post" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=joborders&a=reviewRecruitmentWarning'); ?>" class="d-flex flex-wrap gap-2">
                    <input type="hidden" name="csrfToken" value="<?php echo Template::escapeAttr($_SESSION['CATS']->getCSRFToken()); ?>">
                    <input type="hidden" name="jobOrderID" value="<?php echo (int) $this->jobOrderID; ?>">
                    <input type="hidden" name="warningCode" value="<?php echo Template::escapeAttr($code); ?>">
                    <?php if ($warning !== null): ?><button type="submit" name="reviewAction" value="review" class="btn btn-sm btn-outline-secondary">Reviewed — remind me in five working days</button><?php endif; ?>
                    <?php if ($reviewed !== null): ?><button type="submit" name="reviewAction" value="show" class="btn btn-sm btn-outline-secondary">Show again</button><?php endif; ?>
                </form>
            </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
