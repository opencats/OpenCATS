<?php TemplateUtility::printHeader('Import', array('modules/import/import.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
    <main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-3"><h1 class="h5 fw-semibold mb-0">Import Data</h1></header>

            <?php if (isset($this->successMessage)): ?>

                <div class="alert alert-success" role="status">
                    <h2 class="h6">Success</h2>
                    <?php echo($this->successMessage); ?>
                </div>

            <?php endif; ?>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Recent Commits</p>

            <div class="card card-body p-2 mb-3">
                <div class="row g-2 mb-2">
                    <div class="col">
                        <?php foreach ($this->data as $data): ?>
                            <article class="border-bottom pb-3 mb-3">
                            Import #<?php echo($data['importID']); ?> <?php echo($data['dateCreated']); ?> - <?php echo($data['addedLines']); ?> entries added to database.<br />
                            <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=import&amp;a=revert" class="d-inline">
                                <input type="hidden" name="postback" value="postback" />
                                <input type="hidden" name="importID" value="<?php echo($data['importID']) ?>" />
                                <input type="submit" value="Revert Import" class="btn btn-sm btn-outline-danger">
                            </form>
                            <input type="button" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=import&amp;a=viewerrors&amp;importID=<?php echo($data['importID']) ?>';" value="View Errors" class="btn btn-sm btn-outline-secondary">
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php if (isset($this->importErrors)): ?>

                <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Errors Reported by Import</p>

                <div class="alert alert-danger" role="alert">
                    <div class="row g-2">
                        <div class="col">
                            <pre class="text-wrap text-break mb-0"><?php echo($this->importErrors) ?></pre>
                        </div>
                    </div>
                </div>
                <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=import&amp;a=revert" class="d-inline">
                    <input type="hidden" name="postback" value="postback" />
                    <input type="hidden" name="importID" value="<?php echo($this->importID); ?>" />
                    <input type="submit" value="Revert Import" class="btn btn-sm btn-outline-danger">
                </form>

            <?php endif; ?>

        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
