<?php TemplateUtility::printHeader('Calendar'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2 oc-calendar-error">
    <div id="contents">
        <h1 class="h5 fw-semibold mb-2">Calendar</h1>
        <div class="alert alert-danger" role="alert">
            <p class="fw-semibold mb-1">A fatal error has occurred.</p>
            <?php $this->_($this->errorMessage); ?>
        </div>
    </div>
</main>
<?php TemplateUtility::printFooter(); ?>
