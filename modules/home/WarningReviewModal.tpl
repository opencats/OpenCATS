<?php $reviewToday = new DateTimeImmutable(DateUtility::getAdjustedDate('Y-m-d')); ?>
<div class="modal fade" id="warningReviewModal" tabindex="-1" aria-labelledby="warningReviewTitle" aria-describedby="warningReviewDescription" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <form method="post" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=home&a=reviewWarning'); ?>">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="warningReviewTitle">Personal warning review</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="warningReviewDescription"></p>
                <?php
                $reviewForm = array_fill_keys(array('warning_rule', 'data_item_type', 'data_item_id', 'reason_key', 'reason_id',
                    'expectedVersion', 'expectedEvidence', 'requestID', 'returnTo', 'reviewAction'), '');
                if (isset($reviewHomeContext)) $reviewForm = array_merge($reviewForm, $reviewHomeContext);
                include(LEGACY_ROOT . '/modules/home/WarningReviewFields.tpl');
                ?>
                <div data-review-reminder>
                    <label for="warningReviewReminder" class="form-label">Remind me in</label>
                    <select id="warningReviewReminder" name="reviewUntil" class="form-select" required disabled>
                        <?php foreach (WarningReviews::reminderDates() as $days => $date): ?>
                        <option value="<?php echo Template::escapeAttr($date); ?>"<?php if ($days === 5) echo ' selected'; ?>><?php echo (int) $days; ?> working day<?php echo $days === 1 ? '' : 's'; ?> — <?php echo Template::escapeHtml(TaskPresentation::date($date)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div data-review-close hidden>
                    <label for="warningReviewCloseReason" class="form-label">Close reason</label>
                    <select id="warningReviewCloseReason" name="closeReason" class="form-select mb-3" required disabled>
                        <option value="" selected disabled>Choose a reason...</option>
                        <?php foreach (array('already_handled' => 'Already handled', 'not_applicable' => 'Not applicable', 'incorrect_warning' => 'Incorrect warning', 'no_further_action' => 'No further action') as $value => $label): ?>
                        <option value="<?php echo Template::escapeAttr($value); ?>"><?php echo Template::escapeHtml($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="warningReviewDeadline" class="form-label">Review deadline (within 30 calendar days)</label>
                    <input type="date" id="warningReviewDeadline" name="reviewUntil" class="form-control" required disabled
                        min="<?php echo $reviewToday->modify('+1 day')->format('Y-m-d'); ?>" max="<?php echo $reviewToday->modify('+30 days')->format('Y-m-d'); ?>">
                </div>
                <p class="small text-body-secondary mt-3 mb-0">This affects only your warning list. The warning returns at the beginning of the selected date or when its evidence changes.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" data-review-confirm>Save review</button>
            </div>
        </form>
    </div></div>
</div>
