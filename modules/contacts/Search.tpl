<?php TemplateUtility::printHeader('Contacts', array('modules/contacts/validator.js', 'js/searchSaved.js', 'js/sweetTitles.js', 'js/searchAdvanced.js', 'js/highlightrows.js', 'js/export.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2 oc-contact-search-page">

    <div id="contents">
        <section class="oc-page-header mb-2">
            <h1 class="h5 fw-semibold mb-0">Search Contacts</h1>
        </section>

        <section class="card mb-2 oc-contact-search-form">
            <div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">
                Search Contacts
            </div>

            <div class="card-body p-2">
                <form name="searchForm" id="searchForm"
                    action="<?php echo Template::escapeAttr(CATSUtility::getIndexName()); ?>"
                    method="get" autocomplete="off">

                    <input type="hidden" name="m" id="moduleName" value="contacts">
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

                            <select id="searchMode" name="mode" onclick="advancedSearchConsider();" class="form-select form-select-sm">
                                <option value="searchByFullName"<?php if ($this->mode == "searchByFullName"): ?> selected<?php endif; ?>>Contact Name</option>
                                <option value="searchByCompanyName"<?php if ($this->mode == "searchByCompanyName"): ?> selected<?php endif; ?>>Company Name</option>
                                <option value="searchByTitle"<?php if ($this->mode == "searchByTitle"): ?> selected<?php endif; ?>>Title</option>
                            </select>
                        </div>

                        <div class="flex-grow-1">
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
                        id="searchContacts" name="searchContacts">
                        Search
                        </button>
                    </div>

                    <div class="mt-2">
                        <?php TemplateUtility::printAdvancedSearch(''); ?>
                    </div>
                </form>
            </div>
        </section>

        <script>
            document.searchForm.wildCardString.focus();
        </script>

        <?php if ($this->isResultsMode): ?>
        <section class="card oc-contact-search-results">
            <div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">Search Results (<?php echo(count($this->rs)); ?>)</div>

            <?php if (!empty($this->rs)): ?>
            <?php echo($this->exportForm['header']); ?>
            <div class="table-responsive">
                <table class="sortable table table-sm table-striped table-hover align-middle mb-0" onmouseover="javascript:trackTableHighlight(event)">
                    <thead>
                        <tr>
                            <th scope="col"><span class="visually-hidden">Select</span></th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('firstName', 'First Name'); ?>
                            </th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('lastName', 'Last Name'); ?>
                            </th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('title', 'Title'); ?>
                            </th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('companyName', 'Company'); ?>
                            </th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('dateCreated', 'Created'); ?>
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
                                <input type="checkbox" class="form-check-input" aria-label="Select <?php echo Template::escapeAttr($data['firstName'] . ' ' . $data['lastName']); ?>" id="checked_<?php echo($data['contactID']); ?>" name="checked_<?php echo($data['contactID']); ?>">
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php $this->_($data['contactID']); ?>" target="_blank" rel="noopener noreferrer" title="View in New Window">
                                <img src="images/new_window.gif" alt="(Preview)" width="15" height="15">
                                </a>
                            </td>
                            <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php $this->_($data['contactID']); ?>" class="<?php $this->_($data['linkClassContact']); ?>">
                                <?php $this->_($data['firstName']); ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php $this->_($data['contactID']); ?>" class="<?php $this->_($data['linkClassContact']); ?>">
                                <?php $this->_($data['lastName']); ?>
                                </a>
                            </td>
                            <td><?php $this->_($data['title']); ?></td>
                            <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php $this->_($data['companyID']); ?>" class="<?php $this->_($data['linkClassCompany']); ?>">
                                <?php $this->_($data['companyName']); ?>
                                </a>
                            </td>
                            <td><?php $this->_($data['dateCreated']); ?></td>
                            <td><?php $this->_($data['ownerAbbrName']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php echo($this->exportForm['footer']); ?>
            <div class="card-footer bg-body py-1 px-2"><?php echo($this->exportForm['menu']); ?></div>
            <?php else: ?>
            <div class="card-body p-2 small text-body-secondary">No matching entries found.</div>
            <?php endif; ?>
        </section>
        <?php endif; ?>
    </div>
</main>
<?php TemplateUtility::printFooter(); ?>
