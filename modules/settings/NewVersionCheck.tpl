<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: Administration</h1>
            </header>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">New Version</p>

            <div class="card card-body p-2 mb-2">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm">
                        <?php if ($this->versionCheckPref): ?>
                            <p>
                                Your current version is: <span class="bold"><?php echo(CATSUtility::getVersion()); ?> Build <?php echo(CATSUtility::getBuild()); ?></span>.<br />
                                <?php if ($this->newVersion): ?>
                                    <span class="bold">A new version is available!</span><br />
                                    Go to <a href="http://www.catsone.com/" target="_blank">www.catsone.com</a> to learn more.<br />
                                <?php else: ?>
                                    Catsone.com reports your version is up to date.<br />
                                <?php endif; ?>
                            </p>
                            <?php $this->_($this->newVersionNews); ?>
                        <?php else: ?>
                            <span>Version checking is currently disabled.</span><br /><br />
                        <?php endif; ?>
                        <!-- <form action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration" id="changeNewVersionForm" method="post">
                            <input type="hidden" name="postback" value="postback" />
                            <input type="hidden" name="administrationMode" value="changeVersionCheck" />

                            <input type="checkbox" name="versionCheck" id="versionCheck"<?php if ($this->versionCheckPref): ?> checked<?php endif; ?> />Enable New Version Check (Recommended)<br /><br />
                            <input type="submit" name="save" class = "button" value="Save" />
                            <input type="button" name="back" class = "button" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration';" />
                        </form> -->
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
