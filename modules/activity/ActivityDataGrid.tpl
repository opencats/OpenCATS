<?php TemplateUtility::printHeader('Activities', array('js/highlightrows.js', 'js/sweetTitles.js', 'js/dataGrid.js', 'js/dataGridFilters.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">
    <div id="contents">
        <section class="oc-page-header d-flex flex-wrap align-items-center gap-2 mb-2">
            <h1 class="h5 fw-semibold mb-0">Activities</h1>
            <?php if ($this->numActivities): ?>
            <div class="d-flex flex-wrap align-items-center gap-2 ms-auto small">
                <div class="oc-datagrid-navigation"><?php $this->dataGrid->printNavigation(false); ?></div>
                <nav aria-label="Activity date ranges"><?php echo($this->quickLinks); ?></nav>
            </div>
            <?php endif; ?>
        </section>

        <?php if ($this->numActivities): ?>
        <section class="card">
            <div class="card-header bg-body d-flex flex-wrap align-items-center justify-content-between gap-2 py-1 px-2 small">
                <span>Activities - Page <?php echo($this->dataGrid->getCurrentPageHTML()); ?></span>
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
                <div><?php $this->dataGrid->printNavigation(true); ?></div>
            </div>
        </section>
        <?php else: ?>
        <section class="card">
            <div class="card-body p-3">
                <p class="text-body-secondary mb-0">Activities are automatically recorded based on actions you perform.</p>
            </div>
        </section>
        <?php endif; ?>
    </div>
</main>
<?php TemplateUtility::printFooter(); ?>
