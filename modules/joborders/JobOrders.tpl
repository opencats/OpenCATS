<?php TemplateUtility::printHeader('Job Orders', array('js/highlightrows.js', 'js/sweetTitles.js', 'js/export.js', 'js/dataGrid.js', 'js/dataGridFilters.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2 oc-joborders-page">
    <div id="contents">
        <section class="oc-page-header d-flex flex-wrap align-items-center gap-2 mb-2">
            <h1 class="h5 fw-semibold mb-0">Job Orders: Home</h1>
            <?php if ($this->totalJobOrders): ?>
            <form name="jobOrdersViewSelectorForm" id="jobOrdersViewSelectorForm"
            action="<?php echo Template::escapeAttr(CATSUtility::getIndexName()); ?>" method="get"
            class="d-flex flex-wrap align-items-center gap-2 small ms-auto">
            <input type="hidden" name="m" value="joborders">
            <input type="hidden" name="a" value="list">
            <div class="oc-datagrid-navigation"><?php $this->dataGrid->printNavigation(false); ?></div>
            <div><select name="view" id="view" onchange="<?php echo($this->dataGrid->getJSAddFilter('Status', '==', 'this.value', 'true')); ?>" class="form-select form-select-sm" aria-label="Job order status">
                <?php
                                                foreach($this->jobOrderFilters as $filter){
                                                    echo '<option value="'.$filter.'"';
                                                    if($this->dataGrid->getFilterValue('Status') == $filter){
                                                        echo ' selected="selected"';
                                                    }
                                                    echo ">".$filter."</option>";
                                                }
                                            ?>
                <option value=""<?php if ($this->dataGrid->getFilterValue('Status') == ''): ?> selected="selected"<?php endif; ?>>All</option>
                </select></div>
            <div class="form-check form-check-inline mb-0 me-0"><input class="form-check-input" type="checkbox" name="onlyMyJobOrders" id="onlyMyJobOrders" <?php if ($this->dataGrid->getFilterValue('OwnerID') ==  $this->userID): ?>checked<?php endif; ?> onclick="<?php echo $this->dataGrid->getJSAddRemoveFilterFromCheckbox('OwnerID', '==',  $this->userID); ?>" />
                <label class="form-check-label" for="onlyMyJobOrders">Only My Job Orders</label></div>
            <div class="form-check form-check-inline mb-0 me-0"><input class="form-check-input" type="checkbox" name="onlyHotJobOrders" id="onlyHotJobOrders" <?php if ($this->dataGrid->getFilterValue('IsHot') == '1'): ?>checked<?php endif; ?> onclick="<?php echo $this->dataGrid->getJSAddRemoveFilterFromCheckbox('IsHot', '==', '\'1\''); ?>" />
                <label class="form-check-label" for="onlyHotJobOrders">Only Hot Job Orders</label></div>
            <div class="oc-datagrid-rows-per-page"><?php $this->dataGrid->drawRowsPerPageSelector(); ?></div>
            <div class="oc-datagrid-filter-control"><?php $this->dataGrid->drawShowFilterControl(); ?></div>
        </form>
        <?php endif; ?>
    </section>

    <?php if ($this->errMessage != ''): ?>
    <div id="errorMessage" class="alert alert-danger py-2" role="alert">
        <div class="fw-semibold">There was a problem with your request:</div>
        <?php echo $this->errMessage; ?>
    </div>
    <?php endif; ?>

    <?php if ($this->totalJobOrders): ?>
    <section class="card oc-joborders-list">
        <div class="card-header bg-secondary-subtle py-1 px-2 small">
            Job Orders - Page <?php echo($this->dataGrid->getCurrentPageHTML()); ?>
            (<?php echo($this->dataGrid->getNumberOfRows()); ?> Items)
            (<?php if ($this->dataGrid->getFilterValue('Status') != '') echo ($this->dataGrid->getFilterValue('Status')); else echo ('All'); ?>)
            <?php if ($this->dataGrid->getFilterValue('OwnerID') == $this->userID): ?>(Only My Job Orders)<?php endif; ?>
            <?php if ($this->dataGrid->getFilterValue('IsHot') == '1'): ?>(Only Hot Job Orders)<?php endif; ?>
        </div>
        <div class="card-body p-2">
            <div class="oc-joborders-filters"><?php $this->dataGrid->drawFilterArea(); ?></div>
            <div class="oc-joborders-datagrid"><?php $this->dataGrid->draw(); ?></div>
        </div>
        <div class="card-footer bg-body py-1 px-2 d-flex flex-wrap justify-content-between gap-2 small">
            <div class="oc-joborders-actions"><?php $this->dataGrid->printActionArea(); ?></div>
            <div class="oc-joborders-pagination"><?php $this->dataGrid->printNavigation(true); ?></div>
        </div>
    </section>
    <?php else: ?>
    <section class="card oc-joborders-empty">
        <div class="card-body p-3">
            <h2 class="h6">No job orders yet</h2>
            <?php if ($this->getUserAccessLevel('joborders.add') >= ACCESS_LEVEL_EDIT): ?>
            <p>Add a job order, then attach candidates to the pipeline with their status (interviewing, qualifying, etc.).</p>
            <button type="button" class="btn btn-sm btn-primary"
            onclick="showPopWin(<?php echo Template::escapeJsAttr(CATSUtility::getIndexName() . '?m=joborders&a=addJobOrderPopup'); ?>, 400, 250, null);">
            Add Job Order
            </button>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
</div>
</main>
<?php TemplateUtility::printFooter(); ?>
