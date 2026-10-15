<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: Administration</h1>
            </header>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">System Information</p>

            <div class="card card-body p-2 mb-2">
                    <div class="row g-2 mb-2">
                        <div class="col-12"><span class="fw-semibold">General Information</span></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-sm-4 col-lg-3">
                            Operating System:
                        </div>
                        <div class="col-12 col-sm">
                            <?php echo(php_uname()); ?>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-sm-4 col-lg-3">
                            Operating System Type:
                        </div>
                        <div class="col-12 col-sm">
                            CATS thinks your operating system is <span class="bold"><?php $this->_($this->OSType); ?>.</span>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-sm-4 col-lg-3">
                            PHP Version:
                        </div>
                        <div class="col-12 col-sm">
                            <?php echo(PHP_VERSION); ?>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-sm-4 col-lg-3">
                            Database Version:
                        </div>
                        <div class="col-12 col-sm">
                            <?php $this->_($this->databaseVersion); ?>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-sm-4 col-lg-3">
                            Installation Directory:
                        </div>
                        <div class="col-12 col-sm">
                            <?php $this->_($this->installationDirectory); ?>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-12">&nbsp;</div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-12"><span class="fw-semibold">Module Schema Version Information</span></div>
                    </div>
                    <?php foreach ($this->schemaVersions as $rowIndex => $row): ?>
                    <div class="row g-2 mb-2">
                        <div class="col-sm-4 col-lg-3">
                            <?php $this->_($row['name']); ?>
                        </div>
                        <div class="col-12 col-sm">
                            <?php echo($row['version']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
            </div>
            <input type="button" name="back" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration';"  class="btn btn-sm btn-outline-secondary" />

        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
