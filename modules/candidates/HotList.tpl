<?php TemplateUtility::printNonSelectableHeader('Candidates', array( 'js/highlightrows.js', 'js/export.js', 'js/listEditor.js', 'js/dataGrid.js', 'js/dataGridFilters.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2 oc-candidate-hotlist-page">

        <div id="contents">
<header class="oc-page-header d-flex flex-wrap align-items-center gap-2 mb-2"><h1 class="h5 fw-semibold mb-0">Candidates: Hot Lists</h1>
<form name="candidatesViewSelectorForm" id="candidatesViewSelectorForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get">
                            <input type="hidden" name="m" value="candidates">
                            <input type="hidden" name="a" value="hotList">

                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <div class="row g-2 align-items-start mb-2">
                                    <div class="col-12 col-sm">
                                        <?php $this->pager->printNavigation('lastName', false); ?>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <select name="view" aria-label="Hot List" id="hotListSelect" onChange="if (this.value != 'edit' &amp;&amp; this.value != 'nullline') { document.candidatesViewSelectorForm.submit(); } else { if (this.value == 'edit') { listEditor('Hot Lists', 'hotListSelect', 'hotListCSV', false, 'candidatesViewSelectorForm', 0); } if (this.value == 'nullline') { this.value = '(none)'; } }"  class="form-select form-select-sm">
                                            <?php if ($this->getUserAccessLevel('candidates.manageHotLists') >= ACCESS_LEVEL_DELETE): ?>
                                                <option value="edit">(Manage Hot Lists)</option>
                                            <?php else: ?>
                                                <option value="nullline">Hot Lists (Select to View):</option>
                                            <?php endif; ?>
                                            <option value="nullline">------------------------</option>
                                            <option value="-1" <?php if ($this->hotListID == -1): ?> selected<?php endif; ?>>All Hot Candidates</option>
                                            <?php foreach ($this->hotListsRS as $row => $rowIndex) : ?>
                                                <option value="<?php echo($this->hotListsRS[$row]['hotListID']) ?>"<?php if ($this->hotListID == $this->hotListsRS[$row]['hotListID']): ?> selected<?php endif; ?>"><?php echo($this->hotListsRS[$row]['description']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <?php if ($_SESSION['CATS']->getCheckBox('onlyMyCandidates')) : ?>
                                            <input type="hidden" name="myCandidates" value="off">
                                            <input type="checkbox" name="onlyMyCandidates" id="onlyMyCandidates" onclick="document.candidatesViewSelectorForm.submit();" checked class="form-check-input"><label for="onlyMyCandidates">Only My Candidates</label>
                                        <?php else: ?>
                                            <input type="checkbox" name="onlyMyCandidates" id="onlyMyCandidates" onclick="document.candidatesViewSelectorForm.submit();" class="form-check-input"><label for="onlyMyCandidates">Only My Candidates</label>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" id="hotListCSV" name="hotListCSV" value="<?php $this->_($this->hotListString); ?>">
                        </form>
</header>
<h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">All Candidates - Page <?php echo($this->currentPage); ?> <?php if ($this->pager->getSortBy() == 'dateModifiedSort'): ?>(Most Recently Modified First)<?php endif; ?> <?php if ($this->onlyMyCandidates): ?>(Only My Candidates)<?php endif; ?></h2>

                        <?php if (!empty($this->rs)): ?><?php echo($this->exportForm['formHead']); ?><?php endif; ?>

            <?php $this->pager->drawPager();  ?>

            <?php if (!empty($this->rs)): ?><?php echo($this->exportForm['formFooter']); ?><?php endif; ?>

        </div>
    </main>

<?php TemplateUtility::printFooter(); ?>
