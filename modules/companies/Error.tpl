<?php TemplateUtility::printHeader('Companies'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>

<main id="main" class="container-fluid py-2 oc-company-error-page">
<div id="contents">
<h1 class="h5 fw-semibold mb-2">Companies: Error</h1>

<div class="alert alert-danger mb-0" role="alert">
<div class="fw-semibold mb-1">A fatal error has occurred.</div>
<?php echo $this->errorMessage; ?>
</div>
</div>
</main>

<?php TemplateUtility::printFooter(); ?>
