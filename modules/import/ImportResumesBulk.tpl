<?php TemplateUtility::printHeader('Import', array('modules/import/import.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, ''); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
    <main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-3"><h1 class="h5 fw-semibold mb-0">Import Data</h1></header>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold" id="importHide2">Import Data - Step 2</p>

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

           <?php endif; ?>
            <div id="importTable1" class="alert alert-warning">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-md text-break">CATS may discard or fail to read some of the submitted data which it does not
                    understand how to use. Do not discard the original data!
                    </div>
                </div>

            </div>

            <form name="importDataForm" id="importDataForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=import&amp;a=importUploadResume" enctype="multipart/form-data" method="post" autocomplete="off" onsubmit="document.getElementById('nextSpan').style.display='none'; document.getElementById('uploadingSpan').style.display='';">
                <div id="importHide3" class="card card-body p-2 mb-3">
                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-3 fw-semibold">
                            <span>Import:</span>
                        </div>
                        <div class="col-12 col-md text-break">
                            <img src="images/file/doc.gif">&nbsp;Resume&nbsp;<a href="javascript:void(0);" onclick="showPopWin('index.php?m=import&a=whatIsBulkResumes', 420, 275, null);">(How do I use bulk resumes?)</a><br />
                            <span class="fst-italic">This will not create candidates, it will only add resumes to the <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&a=search">resume search</a>!</span>
                        </div>
                    </div>

                    <div id="importSingle" class="row g-2 mb-2">
                        <div class="col-12 col-md-3 fw-semibold">
                            <span>Files:</span>
                        </div>
                        <div class="col-12 col-md text-break">
                            <br />
                            <?php if (count($this->foundFiles) == 0): ?>
                                CATS has not found any files to import in /upload/.
                <br />
                            <?php else: ?>
                                <span id="foundFilesSpan">
                                    CATS has found <?php echo(count($this->foundFiles)); ?> files to import.
                <br />
                                </span>
                            <?php endif; ?>
                            <input class="btn btn-sm btn-outline-secondary" type="button" value="Back" id="back" onclick="document.location.href='?m=import';">&nbsp;
                            <span id="nextScreenButton" style="display:none;" class="fw-semibold">
                            </span>
                            <?php if (count($this->foundFiles) != 0): ?>
                                <input type="button" class="btn btn-sm btn-outline-secondary" id="startImport" value="Start import" onclick="startMassImport();" />
                                <div id="pleaseWaitImport" style="display:none;" role="status">
                <br />
                                    Please wait, importing resumes...
                                    <div id="progressBar" class="my-3">
                                        <div id="empty" class="progress" role="progressbar" aria-label="Resume import" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                            <div id="d2" class="progress-bar" style="width:0%;"><span id="d1"></span></div>
                                        </div>
                                    </div>
                                    Processing resume <span id="processingResumeNumber"></span> / <?php echo(count($this->foundFiles)); ?>...
                <br />
                                    <input class="btn btn-sm btn-outline-secondary" type="button" value="Abort" id="abortImportButton" onclick="abortImport=true;">
                                </div>
                            <?php endif; ?>
                <br />
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </main>

    <script type="text/javascript">
        initPopUp();

        totalFiles = <?php echo(count($this->foundFiles)); ?>;
    </script>

<?php TemplateUtility::printFooter(); ?>
