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

            <?php else: ?>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold" id="importHide2">Import Data - Step 2</p>

            <div id="importTable1" class="alert alert-warning">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-md text-break">CATS may discard or fail to read some of the submitted data which it does not
                    understand how to use. Do not discard the original data!
                    </div>
                </div>

            </div>

            <?php endif; ?>

            <form name="importDataForm" id="importDataForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=import&amp;a=importUploadFile" enctype="multipart/form-data" method="post" autocomplete="off" onsubmit="document.getElementById('nextSpan').style.display='none'; document.getElementById('uploadingSpan').style.display='';">
                <div id="importHide3" class="card card-body p-2 mb-3">
                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-3 fw-semibold">
                            <span>Import Into:</span>
                        </div>
                        <div class="col-12 col-md text-break">
                            <?php if ($this->typeOfImport == 'Candidates'): ?>
                                <img src="images/candidate_inline.gif">&nbsp;Candidates
                            <?php elseif ($this->typeOfImport == 'JobOrders'): ?>
                                <img src="images/mru/job_order.gif">&nbsp;Job Orders
                            <?php elseif ($this->typeOfImport == 'Companies'): ?>
                                <img src="images/mru/company.gif">&nbsp;Companies
                            <?php elseif ($this->typeOfImport == 'Contacts'): ?>
                                <img src="images/mru/contact.gif">&nbsp;Contacts
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-3 fw-semibold">
                            <label class="form-label" id="fileLabel" for="file">File:</label>
                        </div>
                        <div class="col-12 col-md text-break">
                            <input class="form-control form-control-sm" type="file" id="file" name="file" />
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-3 fw-semibold">
                            <span id="dataTypeLabel">File Format:</span>
                        </div>
                        <div class="col-12 col-md text-break" role="group" aria-labelledby="dataTypeLabel">
                                <input type="hidden" name="typeOfImport" value="<?php echo($this->typeOfImport); ?>">

                                <div class="form-check"><label class="form-check-label"><input class="form-check-input" type="radio" name="typeOfFile" value="csv" checked>Comma Delimited (CSV)</label></div>
                                <div class="form-check"><label class="form-check-label"><input class="form-check-input" type="radio" name="typeOfFile" value="tab" >Tab Delimited</label></div>

                                <span id="nextSpan">
                                    <input class="btn btn-sm btn-outline-secondary" type="button" value="Back" onclick="document.location.href='?m=import';">
                                    <input class="btn btn-sm btn-primary" type="submit" value="Next">
                                </span>
                                <span id="uploadingSpan" role="status" style="display:none;">
                                    Uploading file, please wait...<br />
                                    <span class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading</span></span>
                                </span>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </main>

<?php TemplateUtility::printFooter(); ?>
