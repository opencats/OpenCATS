<?php
TemplateUtility::printHeader(
    'Companies',
    array(
        'modules/companies/validator.js',
        'js/searchSaved.js',
        'js/sweetTitles.js',
        'js/searchAdvanced.js',
        'js/export.js'
    )
);
?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<?php TemplateUtility::printQuickSearch(); ?>

<main id="main" class="container-fluid py-2 oc-company-search-page">
<div id="contents" class="oc-company-search-content">

<section class="oc-page-header mb-2">
<h1 class="h5 fw-semibold mb-0">Search Companies</h1>
</section>

<section class="card mb-2 oc-company-search-form">
<div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">
Search Companies
</div>

<div class="card-body p-2">
<form name="searchForm" id="searchForm"
action="<?php echo Template::escapeAttr(CATSUtility::getIndexName()); ?>"
method="get" autocomplete="off">

<input type="hidden" name="m" id="moduleName" value="companies">
<input type="hidden" name="a" id="moduleAction" value="search">
<input type="hidden" name="getback" id="getback" value="getback">

<div class="mb-2">
<?php TemplateUtility::printSavedSearch($this->savedSearchRS); ?>
</div>

<div class="d-flex flex-wrap align-items-end gap-2">
<div>
<label id="searchModeLabel" for="searchMode"
class="form-label small mb-1">
Search By
</label>

<select id="searchMode" name="mode"
onclick="advancedSearchConsider();"
class="form-select form-select-sm">
<option value="searchByName"
<?php if ($this->mode == 'searchByName'): ?>selected<?php endif; ?>>
Name
</option>
<option value="searchByKeyTechnologies"
<?php if ($this->mode == 'searchByKeyTechnologies'): ?>selected<?php endif; ?>>
Key Technologies
</option>
</select>
</div>

<div class="flex-grow-1" style="max-width: 30rem;">
<label for="searchText" class="form-label small mb-1">
Search Text
</label>

<div class="d-flex align-items-center gap-1">
<input type="text" class="form-control form-control-sm"
id="searchText" name="wildCardString"
value="<?php
if (!empty($this->wildCardString))
{
    echo Template::escapeAttr(
        urldecode($this->wildCardString)
    );
}
?>">

<span class="text-danger" title="Required">*</span>
</div>
</div>

<button type="submit" class="btn btn-sm btn-primary"
id="searchCompanies" name="searchCompanies">
Search
</button>
</div>

<div class="mt-2">
<?php TemplateUtility::printAdvancedSearch('searchByKeyTechnologies'); ?>
</div>
</form>
</div>
</section>

<script>
document.searchForm.wildCardString.focus();
</script>

<?php if ($this->isResultsMode): ?>
<section class="card oc-company-search-results">
<div class="card-header bg-secondary-subtle py-1 px-2">
<div class="d-flex align-items-center justify-content-between gap-2">
<span class="fw-semibold">Search Results</span>
<span class="badge text-bg-secondary">
<?php echo count($this->rs); ?>
</span>
</div>
</div>

<?php if (!empty($this->rs)): ?>
<?php echo $this->exportForm['header']; ?>

<div class="table-responsive">
<table class="sortable table table-sm table-striped table-hover align-middle mb-0">
<thead>
<tr>
<th scope="col" class="text-nowrap">
<span class="visually-hidden">Select</span>
</th>
<th scope="col" class="text-nowrap">
<?php $this->pager->printSortLink('name', 'Name'); ?>
</th>
<th scope="col" class="text-nowrap">
<?php $this->pager->printSortLink('phone1', 'Primary Phone'); ?>
</th>
<th scope="col">Key Technologies</th>
<th scope="col" class="text-nowrap">
<?php $this->pager->printSortLink('dateCreated', 'Created'); ?>
</th>
<th scope="col" class="text-nowrap">
<?php $this->pager->printSortLink(
    'owner_user.last_name',
    'Owner'
); ?>
</th>
</tr>
</thead>

<tbody>
<?php foreach ($this->rs as $data): ?>
<tr>
<td class="text-nowrap">
<input type="checkbox"
class="form-check-input m-0"
id="checked_<?php echo Template::escapeAttr(
    $data['companyID']
); ?>"
name="checked_<?php echo Template::escapeAttr(
    $data['companyID']
); ?>"
aria-label="Select <?php echo Template::escapeAttr(
    $data['name']
); ?>">

<a href="<?php echo Template::escapeUrl(
    CATSUtility::getIndexName() .
    '?m=companies&a=show&companyID=' .
    $data['companyID']
); ?>"
target="_blank"
rel="noopener noreferrer"
class="ms-1"
title="View in New Window">
<img src="images/new_window.gif"
alt="View in New Window"
width="15" height="15">
</a>
</td>

<td>
<a href="<?php echo Template::escapeUrl(
    CATSUtility::getIndexName() .
    '?m=companies&a=show&companyID=' .
    $data['companyID']
); ?>"
class="<?php echo Template::escapeAttr(
    $data['linkClass']
); ?>">
<?php $this->_($data['name']); ?>
</a>
</td>

<td class="text-nowrap">
<?php $this->_($data['phone1']); ?>
</td>

<td>
<?php $this->_($data['keyTechnologies']); ?>
</td>

<td class="text-nowrap">
<?php $this->_($data['dateCreated']); ?>
</td>

<td class="text-nowrap">
<?php $this->_($data['ownerAbbrName']); ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<?php echo $this->exportForm['footer']; ?>

<div class="card-footer bg-body py-1 px-2">
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
<div>
<?php echo $this->exportForm['menu']; ?>
</div>

<div>
<?php $this->pager->printNavigation('name'); ?>
</div>
</div>
</div>
<?php else: ?>
<div class="card-body p-2 small text-body-secondary">
No matching entries found.
</div>
<?php endif; ?>
</section>
<?php endif; ?>

</div>
</main>

<?php TemplateUtility::printFooter(); ?>
