<?php TemplateUtility::printHeader('Import', array('modules/import/import.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, '', 'settings'); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
    <main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-3"><h1 class="h5 fw-semibold mb-0">Import Data</h1></header>

            <?php if (isset($this->errorMessage)): ?>

                <div id="importHide1" class="alert alert-danger" role="alert">
                    <h2 id="importHide0" class="h6">Error!</h2>
                    <?php echo($this->errorMessage); ?>
                </div>

            <?php elseif (isset($this->successMessage)): ?>

                <div id="importHide1" class="alert alert-success" role="status">
                    <h2 id="importHide0" class="h6">Success</h2>
                    <?php echo($this->successMessage); ?>
                </div>

            <?php elseif (isset($this->pendingCommits)): ?>

                <p class="alert alert-warning" role="alert" id="importHide0">Notice</p>

                <div id="importHide1" class="alert alert-info">
                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md text-break">
                            You have recently imported CSV data.  You can click here to review or delete the imported data.<br />
                            <input type="button" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=import&amp;a=viewpending';" value="View Recent Imports" class="btn btn-sm btn-outline-secondary" />
                        </div>
                    </div>
                </div>

            <?php endif; ?>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Import Data</p>

            <div id="importTable1" class="alert alert-warning">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-md text-break">CATS may discard or fail to read some of the submitted data which it does not
                    understand how to use. Do not discard the original data!
                    </div>
                </div>
            </div>

            <div id="importTable2" class="card card-body p-2 mb-3">
                <div class="row g-2 mb-2">
                    <div class="col-12">
                    <form name="importDataForm" id="importDataForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get" autocomplete="off">
                        <input type="hidden" name="m" value="import">
                        <input type="hidden" name="a" value="importSelectType">
                        <fieldset class="mb-3"><legend class="h6">What would you like to import?</legend>

                        <div class="form-check"><label class="form-check-label"><input class="form-check-input" type="radio" name="typeOfImport" value="resume" checked>Resumes</label></div>
                        <div class="form-check"><label class="form-check-label"><input class="form-check-input" type="radio" name="typeOfImport" value="Candidates">Candidates</label></div>
                        <div class="form-check"><label class="form-check-label"><input class="form-check-input" type="radio" name="typeOfImport" value="JobOrders">Job Orders</label></div>
                        <div class="form-check"><label class="form-check-label"><input class="form-check-input" type="radio" name="typeOfImport" value="Companies" >Companies</label></div>
                        <div class="form-check"><label class="form-check-label"><input class="form-check-input" type="radio" name="typeOfImport" value="Contacts" >Contacts</label></div>

                        </fieldset>
                        <input class="btn btn-sm btn-primary" type="submit" value="Next">
                    </form>
                    </div>
                </div>
            </div>

            <?php if ($this->bulk['numBulkAttachments'] > 0 && $this->getUserAccessLevel('import.import') >= ACCESS_LEVEL_SA): ?>
            <br />
            <div class="alert alert-warning mt-3">
                You have uploaded <b><?php echo number_format($this->bulk['numBulkAttachments'], 0); ?></b>
                unclassified resume documents. You can search these documents; but, they are not attached to
                candidates because candidate information (like their name, address, etc.) was not available when they were uploaded.
                <br />
                Rescan the documents to try to automatically detect candidate information. Enter it manually if necessary.
                <br />
                <div class="mt-3">
                    <div class="row g-2 mb-2">
                        <div class="col-auto">
                            <form method="post" action="<?php echo CATSUtility::getIndexName(); ?>?m=import&amp;a=importBulkResumes" class="d-inline">
                                <input type="hidden" name="postback" value="postback" />
                                <input type="submit" value="Rescan Documents" name="rescan" id="rescanButton" class="btn btn-sm btn-primary" />
                            </form>
                        </div>
                        <div class="col-12 col-md text-break">
                            <form method="post" action="<?php echo CATSUtility::getIndexName(); ?>?m=import&amp;a=deleteBulkResumes" class="d-inline" onsubmit="return confirm('This will delete all searchable attachments that have not been associated with candidates. This action cannot be undone. Are you sure you want to continue?');">
                                <input type="hidden" name="postback" value="postback" />
                                <input type="submit" value="Delete Documents" name="delete" id="deleteButton" class="btn btn-sm btn-outline-danger" />
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <br />

        </div>
    </main>

<?php TemplateUtility::printFooter(); ?>
