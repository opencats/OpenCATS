<?php
$identity = $review['identity'];
$reviewForm = array_merge($identity, array('expectedVersion' => $review['version'],
    'expectedEvidence' => $review['evidence'], 'requestID' => bin2hex(random_bytes(16)), 'returnTo' => $reviewReturnTo));
if ($reviewReturnTo === 'home' && isset($reviewHomeContext)) $reviewForm = array_merge($reviewForm, $reviewHomeContext);
$reviewLabel = $identity['warning_rule'] . ' — ' . $warning['title'] . ': ' . str_replace('_', ' ', $identity['reason_key']);
?>
<div class="my-2">
    <span><?php echo Template::escapeHtml(str_replace('_', ' ', $identity['reason_key'])); ?> — <?php echo Template::escapeHtml($review['status']); ?><?php if ($review['suppressed']): ?> until <?php echo Template::escapeHtml(TaskPresentation::date($review['state']['review_until'])); ?><?php endif; ?></span>
    <?php if ($review['canReview']): ?>
    <div class="d-flex flex-wrap gap-2 mt-1">
        <?php
        $quickActions = $review['suppressed'] ? array('reset' => 'Show Again') : array();
        if (in_array($identity['warning_rule'], array('W02', 'W03'), true))
            $quickActions['remind_later'] = 'Mark Reviewed — 5 working days';
        foreach ($quickActions as $quickAction => $quickLabel):
            $reviewForm['requestID'] = bin2hex(random_bytes(16));
        ?>
        <form method="post" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=home&a=reviewWarning'); ?>">
            <?php include(LEGACY_ROOT . '/modules/home/WarningReviewFields.tpl'); ?>
            <?php if ($quickAction === 'remind_later'): ?>
            <input type="hidden" name="reviewUntil" value="<?php echo Template::escapeAttr(WarningReviews::reminderDates()[5]); ?>">
            <?php endif; ?>
            <button class="btn btn-sm btn-outline-secondary" name="reviewAction" value="<?php echo Template::escapeAttr($quickAction); ?>"><?php echo Template::escapeHtml($quickLabel); ?></button>
        </form>
        <?php endforeach; ?>
        <?php foreach (array('remind_later' => 'Remind Later', 'close' => 'Temporary Close') as $reviewAction => $actionLabel):
            if ($reviewAction === 'close' && $identity['warning_rule'] !== 'W07') continue;
        ?>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#warningReviewModal"
            data-review="<?php echo Template::escapeAttr(json_encode($reviewForm, JSON_THROW_ON_ERROR)); ?>"
            data-review-action="<?php echo Template::escapeAttr($reviewAction); ?>"
            data-review-label="<?php echo Template::escapeAttr($reviewLabel); ?>"><?php echo Template::escapeHtml($actionLabel); ?></button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="d-flex flex-wrap gap-2 mt-1">
        <?php if ($identity['warning_rule'] === 'W07' && $_SESSION['CATS']->getAccessLevel('companies.show') >= ACCESS_LEVEL_READ): ?>
            <?php if ($identity['reason_key'] === 'no_next_action' &&
                $_SESSION['CATS']->getAccessLevel('tasks.show') >= ACCESS_LEVEL_READ &&
                $_SESSION['CATS']->getAccessLevel('tasks.add') >= ACCESS_LEVEL_EDIT): ?>
            <a class="btn btn-sm btn-outline-primary" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&a=add&dataItemType=' . DATA_ITEM_COMPANY . '&dataItemID=' . (int) $identity['data_item_id']); ?>">Add follow-up Task</a>
            <?php endif; ?>
            <?php if ($identity['reason_key'] === 'stale_relationship' &&
                $_SESSION['CATS']->getAccessLevel('contacts.show') >= ACCESS_LEVEL_READ &&
                $_SESSION['CATS']->getAccessLevel('contacts.addActivityScheduleEvent') >= ACCESS_LEVEL_EDIT &&
                $_SESSION['CATS']->getAccessLevel('contacts.logActivityScheduleEvent') >= ACCESS_LEVEL_EDIT): ?>
            <a class="btn btn-sm btn-outline-primary" href="<?php echo Template::escapeAttr(TaskPresentation::parentURL(DATA_ITEM_COMPANY, $identity['data_item_id'])); ?>">Open Company to log Contact activity</a>
            <?php endif; ?>
        <?php elseif (in_array($identity['warning_rule'], array('W02', 'W03'), true) &&
            $_SESSION['CATS']->getAccessLevel('joborders.show') >= ACCESS_LEVEL_READ): ?>
            <a class="btn btn-sm btn-outline-primary" href="<?php echo Template::escapeAttr(TaskPresentation::parentURL(DATA_ITEM_JOBORDER, $identity['data_item_id'])); ?>">Open Job Order / Pipeline</a>
        <?php endif; ?>
    </div>
</div>
