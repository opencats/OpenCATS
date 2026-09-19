<?php TemplateUtility::printHeader('Lists', array('js/highlightrows.js', 'js/sweetTitles.js', 'js/export.js', 'js/dataGrid.js', 'js/dataGridFilters.js', 'js/lists.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">
    <div id="contents">
        <section class="oc-page-header d-flex flex-wrap align-items-center gap-2 mb-2">
            <h1 class="h5 fw-semibold mb-0 text-break">Lists: <?php $this->_($this->listRS['description']); ?></h1>
            <button type="button" class="btn btn-sm btn-outline-danger ms-auto"
                onclick="deleteListFromListView(<?php $this->_($this->listRS['savedListID']); ?>, <?php $this->_($this->listRS['numberEntries']); ?>);">Delete List</button>
        </section>

        <section class="card">
            <div class="card-header bg-secondary-subtle py-1 px-2 d-flex flex-wrap align-items-center justify-content-between gap-2 small">
                <div class="text-break">
                    <?php $this->_($this->listRS['description']); ?> - Page <?php echo($this->dataGrid->getCurrentPageHTML()); ?>
                    (<?php echo($this->dataGrid->getNumberOfRows()); ?> Items)
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="oc-datagrid-rows-per-page"><?php $this->dataGrid->drawRowsPerPageSelector(); ?></div>
                    <div class="oc-datagrid-filter-control"><?php $this->dataGrid->drawShowFilterControl(); ?></div>
                </div>
            </div>
            <div class="card-body p-2">
                <?php $this->dataGrid->drawFilterArea(); ?>
                <?php $this->dataGrid->draw(); ?>
            </div>
            <div class="card-footer bg-body py-1 px-2 d-flex flex-wrap align-items-center justify-content-between gap-2 small">
                <div><?php $this->dataGrid->printActionArea(); ?></div>
                <div class="mw-100"><?php $this->dataGrid->printNavigation(true); ?></div>
            </div>
        </section>
    </div>
</main>
<?php TemplateUtility::printFooter(); ?>
