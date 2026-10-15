<?php TemplateUtility::printHeader('Job Orders', array('modules/joborders/validator.js', 'js/sweetTitles.js',  'js/searchAdvanced.js', 'js/highlightrows.js', 'js/export.js', 'js/searchSaved.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2 oc-joborder-search-page">

    <div id="contents">
        <section class="oc-page-header mb-2"><h1 class="h5 fw-semibold mb-0">Job Orders: Search Job Orders</h1></section>

        <section class="card mb-2 oc-joborder-search-form" id="searchTable">
            <div class="card-body p-2">

                <form name="searchForm" id="searchForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get" autocomplete="off">
                    <input type="hidden" name="m" id="moduleName" value="joborders">
                    <input type="hidden" name="a" id="moduleAction" value="search">
                    <input type="hidden" name="getback" id="getback" value="getback">

                    <?php TemplateUtility::printSavedSearch($this->savedSearchRS); ?>

                    <div class="row g-2 align-items-end">
                        <div class="col-sm-4 col-lg-3">
                            <label class="form-label small mb-1" id="searchModeLabel" for="searchMode">Search By:</label>&nbsp;
                            <select id="searchMode" name="mode" onclick="advancedSearchConsider();" class="form-select form-select-sm">
                            <option value="searchByJobTitle"<?php if ($this->mode == "searchByJobTitle"): ?> selected="selected"<?php endif; ?>>Job Title</option>
                            <option value="searchByCompanyName"<?php if ($this->mode == "searchByCompanyName"): ?> selected="selected"<?php endif; ?>>Company Name</option>
                            </select></div>
                        <div class="col-sm-6 col-lg-7">
                            <label for="searchText" class="form-label small mb-1">Search Text <span class="text-danger" title="Required">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="searchText" name="wildCardString" value="<?php if (!empty($this->wildCardString)) echo(urldecode($this->wildCardString)); ?>"></div>
                        <div class="col-sm-2">
                            <button type="submit" class="btn btn-sm btn-primary" id="searchJobOrders" name="searchJobOrders" value="Search">Search</button></div>
                    </div>
                    <?php TemplateUtility::printAdvancedSearch('searchByKeySkills,searchByResume'); ?>
                </form>

            </div>
        </section>

        <script>
                document.searchForm.wildCardString.focus();
            </script>

        <?php if ($this->isResultsMode): ?>
        <br>
        <h2 class="h6 fw-semibold mt-2">Search Results</h2>

        <?php if (!empty($this->rs)): ?>
        <?php echo($this->exportForm['header']); ?>

        <div class="card mb-2 oc-joborder-search-results">
            <div class="table-responsive">
                <table class="sortable table table-sm table-striped table-hover align-middle mb-0" onmouseover="javascript:trackTableHighlight(event)">
                    <thead>
                        <tr>
                            <th scope="col"><span class="visually-hidden">Select</span></th>
                            <th scope="col">
                            <?php $this->pager->printSortLink('title', 'Title'); ?>
                            </th>
                            <th scope="col">
                            <?php $this->pager->printSortLink('companyName', 'Company'); ?>
                            </th>
                            <th scope="col">
                            <?php $this->pager->printSortLink('type', 'Type'); ?>
                            </th>
                            <th scope="col">
                            <?php $this->pager->printSortLink('status', 'Status'); ?>
                            </th>
                            <th scope="col">
                            <?php $this->pager->printSortLink('dateCreated', 'Created'); ?>
                            </th>
                            <th scope="col">
                            <?php $this->pager->printSortLink('startDate', 'Start'); ?>
                            </th>
                            <th scope="col">
                            <?php $this->pager->printSortLink('recruiterLastName', 'Recruiter'); ?>
                            </th>
                            <th scope="col">
                            <?php $this->pager->printSortLink('owner_user.last_name', 'Owner'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php foreach ($this->rs as $rowNumber => $data): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td>
                                <input class="form-check-input" type="checkbox" id="checked_<?php echo($data['jobOrderID']); ?>" name="checked_<?php echo($data['jobOrderID']); ?>">
                                <a href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=joborders&a=show&jobOrderID=' . $data['jobOrderID']); ?>" target="_blank" rel="noopener" title="View in New Window">
                                <img src="images/new_window.gif" alt="(Preview)" width="15" height="15">
                                </a>
                            </td>
                            <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php $this->_($data['jobOrderID']); ?>" class="<?php $this->_($data['linkClass']); ?>">
                                <?php $this->_($data['title']); ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php $this->_($data['companyID']); ?>">
                                <?php $this->_($data['companyName']); ?>
                                </a>
                            </td>
                            <td><?php $this->_($data['type']); ?>&nbsp;</td>
                            <td><?php $this->_($data['status']); ?>&nbsp;</td>
                            <td><?php $this->_($data['dateCreated']); ?>&nbsp;</td>
                            <td><?php $this->_($data['startDate']); ?>&nbsp;</td>
                            <td><?php $this->_($data['recruiterAbbrName']); ?>&nbsp;</td>
                            <td><?php $this->_($data['ownerAbbrName']); ?>&nbsp;</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php echo($this->exportForm['footer']); ?>
        <?php echo($this->exportForm['menu']); ?>
        <?php else: ?>
        <div class="alert alert-info" role="status">No matching entries found.</div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
<?php TemplateUtility::printFooter(); ?>
