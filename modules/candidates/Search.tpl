<?php TemplateUtility::printHeader('Candidates', array('modules/candidates/validator.js', 'js/searchSaved.js', 'js/sweetTitles.js', 'js/searchAdvanced.js', 'js/highlightrows.js', 'js/export.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2 oc-candidate-search-page">

        <div id="contents">
            <header class="oc-page-header mb-2"><h1 class="h5 fw-semibold mb-0">Candidates: Search Candidates</h1></header>

            <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">Search Candidates</h2>

            <div class="card card-body p-2 mb-2" id="searchTable ">
                <div class="row g-2 align-items-start mb-2">
                    <div class="col-12 col-sm">
                        <form name="searchForm" id="searchForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get" autocomplete="off">
                            <input type="hidden" name="m" id="moduleName" value="candidates">
                            <input type="hidden" name="a" id="moduleAction" value="search">
                            <input type="hidden" name="getback" id="getback" value="getback">

                            <?php TemplateUtility::printSavedSearch($this->savedSearchRS); ?>

                            <div class="d-flex flex-wrap align-items-end gap-2">
                            <div><label id="searchModeLabel" for="searchMode" class="form-label small mb-1">Search By:</label>&nbsp;
                            <select id="searchMode" name="mode" onclick="advancedSearchConsider();" class="form-select form-select-sm">
                                <option value="searchByFullName"<?php if ($this->mode == "searchByFullName"): ?> selected<?php endif; ?>>Candidate Name</option>
                                <option value="searchByResume"<?php if ($this->mode == "searchByResume" || empty($this->mode)): ?> selected<?php endif; ?>>Resume Keywords</option>
                                <option value="searchByKeySkills"<?php if ($this->mode == "searchByKeySkills"): ?> selected<?php endif; ?>>Key Skills</option>
                                <option value="searchByCity"<?php if ($this->mode == "searchByCity"): ?> selected<?php endif; ?>>City</option>
                                <option value="phoneNumber"<?php if ($this->mode == "phoneNumber"): ?> selected<?php endif; ?>>Phone Number</option>
                            </select></div>
                            <div class="flex-grow-1"><label for="searchText" class="form-label small mb-1">Search Text</label><input type="text" class="form-control form-control-sm" id="searchText" name="wildCardString" value="<?php if (!empty($this->wildCardString)) $this->_($this->wildCardString); ?>"></div>
                            <button type="submit" class="btn btn-sm btn-primary" id="searchCandidates" name="searchCandidates" value="Search">Search</button>
                            </div>
                            <?php TemplateUtility::printAdvancedSearch('searchByKeySkills,searchByResume'); ?>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                document.searchForm.wildCardString.focus();
            </script>

            <?php if ($this->isResumeMode && $this->isResultsMode): ?>
                <br>
                <?php if (!empty($this->rs)): ?>
                    <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">Search Results &nbsp;<?php $this->_($this->pageStart); ?> to <?php $this->_($this->pageEnd); ?> of <?php $this->_($this->totalResults); ?></h2>
                    <?php echo($this->exportForm['header']); ?>
                <?php else: ?>
                    <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">Search Results</h2>
                <?php endif; ?>

                <div class="table-responsive"><table class="table table-sm table-striped table-hover align-middle mb-0 sortable">
                    <thead>
                        <tr>
                            <th scope="col">&nbsp;</th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('firstName', 'First Name'); ?>
                            </th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('lastName', 'Last Name'); ?>
                            </th>
                            <th scope="col">Resume</th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('city', 'City'); ?>
                            </th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('state', 'State'); ?>
                            </th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('dateCreatedSort', 'Created'); ?>
                            </th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('dateModifiedSort', 'Modified'); ?>
                            </th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('ownerSort', 'Owner'); ?>
                            </th>
                        </tr>
                    </thead><tbody>

                    <?php if (!empty($this->rs)): ?>
                        <?php foreach ($this->rs as $rowNumber => $data): ?>
                            <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                                <?php if ($data['candidateID'] > 0): ?>
                                    <td>
                                        <input type="checkbox" id="checked_<?php echo($data['candidateID']); echo($data['attachmentID']); ?>" name="checked_<?php echo($data['candidateID']); ?>" class="form-check-input">
                                        <a href="javascript:void(0);" onClick="window.open('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($data['candidateID']); ?>')" title="View in New Window">
                                            <img src="images/new_window.gif" class="abstop" alt="(Preview)" width="15" height="15">
                                        </a>
                                    </td>
                                    <td>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($data['candidateID']); ?>">
                                            <?php $this->_($data['firstName']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($data['candidateID']); ?>">
                                            <?php $this->_($data['lastName']); ?>
                                        </a>
                                    </td>
                                <?php else: ?>
                                    <td>&nbsp;</td>
                                    <td>
                                    </td>
                                    <td colspan="2">
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=add&amp;attachmentID=<?php $this->_($data['attachmentID']); ?>">
                                            <img src="images/candidate_tiny.gif" width="16" height="16" class="absmiddle" alt="" title="Create Candidate Profile">
                                        </a>
                                        &nbsp;Bulk Resume
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <a href="#" onclick="window.open('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=viewResume&amp;wildCardString=<?php $this->_(urlencode($this->wildCardString)); ?>&amp;attachmentID=<?php $this->_($data['attachmentID']); ?>', 'viewResume', 'scrollbars=1,width=700,height=600')">
                                        <img src="images/resume_preview_inline.gif" class="abstop" alt="(Preview)" width="15" height="15">
                                    </a>&nbsp;
                                    <?php echo($data['excerpt']); ?>
                                </td>
                                <td><?php $this->_($data['city']); ?></td>
                                <td><?php $this->_($data['state']); ?></td>
                                <td><?php $this->_($data['dateCreated']); ?></td>
                                <td><?php $this->_($data['dateModified']); ?></td>
                                <td><?php $this->_($data['ownerAbbrName']); ?>&nbsp;</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">No matching entries found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody></table></div>
                <?php echo($this->exportForm['footer']); ?>
                <?php echo($this->exportForm['menu']); ?>
                <?php if (!empty($this->rs)): ?>
                    <div><?php $this->pager->printNavigation(); ?></div>
                    <br>
                <?php endif; ?>
            <?php elseif ($this->isResultsMode): ?>
                <br>
                <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">Search Results (<?php echo(count($this->rs)); ?>)</h2>

                <?php if (!empty($this->rs)): ?>
                    <?php echo($this->exportForm['header']); ?>
                    <div class="table-responsive"><table class="table table-sm table-striped table-hover align-middle mb-0 sortable" onmouseover="javascript:trackTableHighlight(event)">
                        <thead><tr>
                            <th scope="col">&nbsp;</th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('firstName', 'First Name'); ?>
                            </th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('lastName', 'Last Name'); ?>
                            </th>
                            <th scope="col">Key Skills</th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('city', 'City'); ?>
                            </th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('state', 'State'); ?>
                            </th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('dateCreated', 'Created'); ?>
                            </th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('dateModified', 'Modified'); ?>
                            </th>
                            <th scope="col">
                                <?php $this->pager->printSortLink('owner_user.last_name', 'Owner'); ?>
                            </th>
                        </tr></thead><tbody>

                        <?php foreach ($this->rs as $rowNumber => $data): ?>
                            <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                                <td>
                                    <input type="checkbox" id="checked_<?php echo($data['candidateID']); ?>" name="checked_<?php echo($data['candidateID']); ?>" class="form-check-input">
                                    <a href="javascript:void(0);" onClick="window.open('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($data['candidateID']); ?>')" title="View in New Window">
                                        <img src="images/new_window.gif" class="abstop" alt="(Preview)" width="15" height="15">
                                    </a>&nbsp;
                                </td>
                                <td>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($data['candidateID']); ?>">
                                        <?php $this->_($data['firstName']); ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($data['candidateID']); ?>">
                                        <?php $this->_($data['lastName']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (isset($data['resumeID'])): ?>
                                        <a href="javascript:void(0);" onclick="window.open('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=viewResume&amp;wildCardString=<?php $this->_(urlencode($this->wildCardString)); ?>&amp;attachmentID=<?php $this->_($data['resumeID']); ?>', 'viewResume', 'scrollbars=1,width=700,height=600')" Title="View resume">
                                            <img src="images/resume_preview_inline.gif" class="abstop" alt="(Preview)" width="15" height="15">
                                        </a>
                                    <?php endif; ?>
                                    <?php $this->_($data['keySkills']); ?>&nbsp;
                                </td>
                                <td><?php $this->_($data['city']); ?>&nbsp;</td>
                                <td><?php $this->_($data['state']); ?>&nbsp;</td>
                                <td><?php $this->_($data['dateCreated']); ?>&nbsp;</td>
                                <td><?php $this->_($data['dateModified']); ?>&nbsp;</td>
                                <td><?php $this->_($data['ownerAbbrName']); ?>&nbsp;</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody></table></div>
                    <?php echo($this->exportForm['footer']); ?>
                    <?php echo($this->exportForm['menu']); ?>
                <?php else: ?>
                    <p>No matching entries found.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
