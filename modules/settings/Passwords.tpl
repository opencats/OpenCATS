<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: Administration</h1>
            </header>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Passwords</p>

            <div class="card card-body p-2 mb-2">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm">
                        <div class="card card-body p-2 mb-2">
                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 col-lg-3">
                                    Allow retrieval of forgotten passwords through email:
                                </div>
                                <div class="col-12 col-sm">
                                    <input type="checkbox" name="ForgottenPasswords" class="form-check-input">
                                </div>
                            </div>
                        </div>
                        <input type="button" name="back" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration';"  class="btn btn-sm btn-outline-secondary" />
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
