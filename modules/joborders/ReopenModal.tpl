<?php TemplateUtility::printModalHeader('Job Orders', array('js/pipeline.js'), 'Reopen Job Order'); ?>
<div class="modal fade" id="job-reopen-modal" tabindex="-1" aria-labelledby="job-reopen-title" aria-describedby="job-reopen-description" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <form method="post" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=joborders&a=reopen'); ?>">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="job-reopen-title">Reopen Job Order</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="job-reopen-description">This Job Order is currently <?php echo Template::escapeHtml($this->reopenJob['status']); ?>.<br>To add candidates, it must be reopened.</p>
                <div class="alert alert-danger d-none" role="alert" tabindex="-1" data-reopen-error></div>
                <input type="hidden" name="postback" value="postback">
                <input type="hidden" name="csrfToken" value="<?php echo Template::escapeAttr($_SESSION['CATS']->getCSRFToken()); ?>">
                <input type="hidden" name="jobOrderID" value="<?php echo (int) $this->reopenJob['jobOrderID']; ?>">
                <input type="hidden" name="expectedVersion" value="<?php echo Template::escapeAttr($this->reopenJob['reopenVersion']); ?>">
                <label class="form-label" for="job-new-vacancies">How many new vacancies?</label>
                <input class="form-control" type="number" id="job-new-vacancies" name="newVacancies" min="1" max="9999" step="1" value="1" required>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="reset">Reset</button>
                <button class="btn btn-primary" type="submit">Submit</button>
            </div>
        </form>
    </div></div>
</div>
<?php TemplateUtility::printBootstrapBundle(); ?>
<?php TemplateUtility::printFooter(); ?>
