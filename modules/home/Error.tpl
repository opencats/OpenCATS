<?php TemplateUtility::printHeader('Home'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>
    <main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">CATS: Error</h1>
            </header>

            <div class="alert alert-danger" role="alert">
                <p>A fatal error has occurred.</p>
                <?php echo($this->errorMessage); ?>
            </div>
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
