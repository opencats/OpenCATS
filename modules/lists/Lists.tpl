<?php TemplateUtility::printHeader('Lists', array('js/highlightrows.js', 'js/sweetTitles.js', 'js/export.js', 'js/dataGrid.js', 'js/dataGridFilters.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">
    <div id="contents">
        <section class="oc-page-header d-flex flex-wrap align-items-center gap-2 mb-2">
            <h1 class="h5 fw-semibold mb-0">Lists: Home</h1>
            <?php if ($this->dataGrid->getNumberOfRows()): ?>
            <div class="d-flex flex-wrap align-items-center gap-2 small ms-auto">
                <div class="oc-datagrid-rows-per-page"><?php $this->dataGrid->drawRowsPerPageSelector(); ?></div>
            </div>
            <?php endif; ?>
        </section>

        <?php if ($this->dataGrid->getNumberOfRows()): ?>
        <section class="card">
            <div class="card-header bg-secondary-subtle py-1 px-2 small">
                Lists - Page <?php echo($this->dataGrid->getCurrentPageHTML()); ?>
                (<?php echo($this->dataGrid->getNumberOfRows()); ?> Items)
            </div>
            <div class="card-body p-2">
                <?php $this->dataGrid->drawFilterArea(); ?>
                <?php $this->dataGrid->draw(); ?>
            </div>
            <div class="card-footer bg-body py-1 px-2 d-flex flex-wrap justify-content-end gap-2 small">
                <div class="mw-100"><?php $this->dataGrid->printNavigation(true); ?></div>
            </div>
        </section>
        <?php else: ?>
        <section class="card">
            <div class="card-body p-3">
                <h2 class="h6">No lists to display</h2>
                <p>Create lists to group candidates, job orders, companies and contacts and perform actions on them quickly.</p>
                <p class="mb-0 text-body-secondary">Create lists from the <strong>job orders, candidates, companies</strong> or <strong>contacts</strong> tab.</p>
            </div>
        </section>
        <?php endif; ?>
    </div>
</main>
<?php TemplateUtility::printFooter(); ?>
