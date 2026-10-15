<?php
TemplateUtility::printHeader(
    'Contacts',
    array(
        'js/highlightrows.js',
        'js/export.js',
        'js/dataGrid.js',
        'js/dataGridFilters.js'
    )
);
?>

<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>

<main id="main" class="container-fluid py-2 oc-contacts-page">
    <div id="contents" class="oc-contacts-content">
        <?php if ($this->totalContacts): ?>

        <section
            class="oc-page-header d-flex flex-wrap align-items-center gap-2 mb-2"
            >
            <div class="d-flex align-items-baseline gap-2 flex-shrink-0">
                <h1 class="h5 fw-semibold mb-0">Contacts</h1>

                <span class="small text-body-secondary">
                <?php echo number_format(
    $this->dataGrid->getNumberOfRows()
); ?> items
                </span>
            </div>

            <form
                name="contactsViewSelectorForm"
                id="contactsViewSelectorForm"
                action="<?php echo Template::escapeAttr(
    CATSUtility::getIndexName()
); ?>"
                method="get"
                class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-end gap-2 small ms-auto"
                >
                <input type="hidden" name="m" value="contacts">
                <input type="hidden" name="a" value="listByView">

                <div class="oc-datagrid-navigation flex-shrink-0">
                    <?php $this->dataGrid->printNavigation(false); ?>
                </div>

                <div class="form-check form-check-inline mb-0 me-0 flex-shrink-0">
                    <input
                    class="form-check-input"
                    type="checkbox"
                    name="onlyMyCompanies"
                    id="onlyMyContacts"
                    <?php
if ($this->dataGrid->getFilterValue('OwnerID') == $this->userID)
{
    echo 'checked';
}
?>
                    onclick="<?php echo $this->dataGrid
->getJSAddRemoveFilterFromCheckbox(
    'OwnerID',
    '==',
    $this->userID
); ?>"
                    >

                    <label
                        class="form-check-label text-nowrap"
                        for="onlyMyContacts"
                        >
                        Only My Contacts
                    </label>
                </div>

                <div class="form-check form-check-inline mb-0 me-0 flex-shrink-0">
                    <input
                    class="form-check-input"
                    type="checkbox"
                    name="onlyHotCompanies"
                    id="onlyHotContacts"
                    <?php
    if ($this->dataGrid->getFilterValue('IsHot') == '1')
    {
        echo 'checked';
    }
    ?>
                    onclick="<?php echo $this->dataGrid
    ->getJSAddRemoveFilterFromCheckbox(
        'IsHot',
        '==',
        '\'1\''
    ); ?>"
                    >

                    <label
                        class="form-check-label text-nowrap"
                        for="onlyHotContacts"
                        >
                        Only Hot Contacts
                    </label>
                </div>

                <div class="oc-datagrid-rows-per-page flex-shrink-0">
                    <?php $this->dataGrid->drawRowsPerPageSelector(); ?>
                </div>

                <div class="oc-datagrid-filter-control flex-shrink-0">
                    <?php $this->dataGrid->drawShowFilterControl(); ?>
                </div>
            </form>
        </section>

        <?php if ($this->errMessage != ''): ?>
        <div
            id="errorMessage"
            class="alert alert-danger py-2"
            role="alert"
            >
            <div class="fw-semibold">
                There was a problem with your request:
            </div>

            <div>
                <?php echo $this->errMessage; ?>
            </div>
        </div>
        <?php endif; ?>

        <section class="card oc-contacts-list">
            <div class="card-body p-2">
                <div class="oc-contacts-filters">
                    <?php $this->dataGrid->drawFilterArea(); ?>
                </div>

                <div class="oc-contacts-datagrid">
                    <?php $this->dataGrid->draw(); ?>
                </div>
            </div>

            <div class="card-footer bg-body py-1 px-2">
                <div
                    class="d-flex flex-wrap align-items-center justify-content-between gap-2 small"
                    >
                    <div class="oc-contacts-actions">
                        <?php $this->dataGrid->printActionArea(); ?>
                    </div>

                    <div class="oc-contacts-pagination">
                        <?php $this->dataGrid->printNavigation(true); ?>
                    </div>
                </div>
            </div>
        </section>

        <?php else: ?>
        <section class="oc-page-header mb-2"><h1 class="h5 fw-semibold mb-0">Contacts</h1></section>
        <section class="card"><div class="card-body p-3">
                <p class="text-body-secondary">Add contacts to keep track of people you work with.</p>
                <?php if ($this->getUserAccessLevel('contacts.add') >= ACCESS_LEVEL_EDIT): ?>
                <a class="btn btn-sm btn-primary" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=add">Add Contact</a>
                <?php endif; ?>
            </div></section>
        <?php endif; ?>
    </div>
</main>

<?php TemplateUtility::printFooter(); ?>
