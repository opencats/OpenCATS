<?php TemplateUtility::printHeader('Import'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
    <main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-3"><h1 class="h5 fw-semibold mb-0">Import: Error</h1></header>

            <p class="alert alert-danger" role="alert">
                A fatal error has occurred.
                <br />
                <?php echo($this->errorMessage); ?>
            </p>
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
