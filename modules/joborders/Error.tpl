<?php TemplateUtility::printHeader('Job Orders', array()); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>

<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2 oc-joborder-error-page">

    <div id="contents">
        <section class="oc-page-header mb-2"><h1 class="h5 fw-semibold mb-0">Job Orders: Error</h1></section>

        <div class="alert alert-danger" role="alert">
            A fatal error has occurred.<br>
            <br>
            <?php echo($this->errorMessage); ?>
        </div>
    </div>
</main>
<?php TemplateUtility::printFooter(); ?>
