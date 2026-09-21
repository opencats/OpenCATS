<?php TemplateUtility::printHeader('Settings', array('js/massImport.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, '', 'settings'); ?>
<link rel="stylesheet" type="text/css" href="<?php echo TemplateUtility::getVersionedAssetURL('modules/import/MassImport.css'); ?>" />
    <main id="main" class="container-fluid py-2">
        <div id="contents">
            <header class="oc-page-header mb-3"><h1 class="h5 fw-semibold mb-0">Import Resumes</h1></header>
            <div class="card card-body p-3">
                <ol class="row g-2 list-unstyled mb-3" aria-label="Resume import progress">
                    <?php foreach (array(1 => 'Upload resume documents', 2 => 'Process Documents', 3 => 'Review', 4 => 'Finish Up') as $step => $label): ?>
                    <li class="col-12 col-sm-6 col-lg-3">
                        <div class="rounded p-2 h-100 <?php echo $this->step == $step ? 'bg-primary text-white' : 'bg-body-tertiary'; ?>"<?php if ($this->step == $step): ?> aria-current="step"<?php endif; ?>>
                            <span class="fw-semibold">Step <?php echo $step; ?></span><br />
                            <?php echo $label; ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ol>

                <?php if (isset($this->errorMessage)): ?>
                    <div class="alert alert-danger" role="alert">
                    <?php echo $this->errorMessage; ?>
                    </div>
                <?php else: ?>
                    <?php echo $this->subTemplateContents; ?>
                <?php endif; ?>
            </div>

        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
