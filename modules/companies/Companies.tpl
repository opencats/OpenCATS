<?php
TemplateUtility::printHeader(
    'Companies',
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

<main class="container-fluid py-2 oc-companies-page">
<div class="oc-companies-content">

<section
class="oc-page-header d-flex flex-wrap align-items-center gap-2 mb-2"
>
<div class="d-flex align-items-baseline gap-2 flex-shrink-0">
<h1 class="h5 fw-semibold mb-0">Companies</h1>

<span class="small text-body-secondary">
<?php echo number_format(
    $this->dataGrid->getNumberOfRows()
); ?> items
</span>
</div>

<form
name="companiesViewSelectorForm"
id="companiesViewSelectorForm"
action="<?php echo Template::escapeAttr(
    CATSUtility::getIndexName()
); ?>"
method="get"
class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-end gap-2 small ms-auto"
>
<input type="hidden" name="m" value="companies">
<input type="hidden" name="a" value="listByView">

<div class="oc-datagrid-navigation flex-shrink-0">
<?php $this->dataGrid->printNavigation(false); ?>
</div>

<div class="form-check form-check-inline mb-0 me-0 flex-shrink-0">
<input
class="form-check-input"
type="checkbox"
name="onlyMyCompanies"
id="onlyMyCompanies"
<?php
if ($this->dataGrid->getFilterValue('OwnerID') !== '')
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
for="onlyMyCompanies"
    >
    Only My Companies
    </label>
    </div>

    <div class="form-check form-check-inline mb-0 me-0 flex-shrink-0">
    <input
    class="form-check-input"
    type="checkbox"
    name="onlyHotCompanies"
    id="onlyHotCompanies"
    <?php
    if ($this->dataGrid->getFilterValue('IsHot') !== '')
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
    for="onlyHotCompanies"
        >
        Only Hot Companies
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

        <section class="card oc-companies-list">
        <div class="card-body p-2">
        <div class="oc-companies-filters">
        <?php $this->dataGrid->drawFilterArea(); ?>
        </div>

        <div class="oc-companies-datagrid">
        <?php $this->dataGrid->draw(); ?>
        </div>
        </div>

        <div class="card-footer bg-body py-1 px-2">
        <div
        class="d-flex flex-wrap align-items-center justify-content-between gap-2 small"
        >
        <div class="oc-companies-actions">
        <?php $this->dataGrid->printActionArea(); ?>
        </div>

        <div class="oc-companies-pagination">
        <?php $this->dataGrid->printNavigation(true); ?>
        </div>
        </div>
        </div>
        </section>

        </div>
        </main>

        <?php TemplateUtility::printFooter(); ?>
