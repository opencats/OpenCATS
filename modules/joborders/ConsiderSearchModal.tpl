<?php TemplateUtility::printModalHeader('Job Orders', 'js/sorttable.js', 'Add Candidate to This Job Order'); ?>
<main class="container-fluid p-2 oc-joborder-considersearchmodal">

    <?php if (!$this->isFinishedMode): ?>
    <p>Search for a candidate below, and then click on the candidate's
    first or last name to add the selected candidate to the job order
    pipeline.</p>

    <section class="card mb-2 oc-joborder-search-form">
        <div class="card-body p-2">
            <form id="searchByFullNameForm" name="searchByFullNameForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=considerCandidateSearch" method="post">
                <input type="hidden" name="postback" id="postback" value="postback">
                <input type="hidden" id="mode_fullname" name="mode" value="searchByFullName">
                <input type="hidden" id="jobOrderID_fullName" name="jobOrderID" value="<?php echo($this->jobOrderID); ?>">

                <label for="wildCardString_fullname" class="form-label small mb-1">Search by Full Name <span class="text-danger" title="Required">*</span></label>
                <input type="text" class="form-control form-control-sm mb-2" id="wildCardString_fullname" name="wildCardString">

                <button type="submit" class="btn btn-sm btn-primary" id="searchByFullName" name="searchByFullName" value="Search by Full Name">Search by Full Name</button>

                &nbsp;

            </form>
        </div>
    </section>
    <br>

    <a class="btn btn-sm btn-outline-secondary" href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=addCandidateModal&amp;jobOrderID=<?php echo($this->jobOrderID); ?>">
    <img src="images/candidate_inline.gif" width="16" height="16" class="absmiddle" alt="add">&nbsp;Add Candidate
    </a>
    <br>

    <?php if (empty($_POST['mode']) || $_POST['mode'] == 'searchByFullName'): ?>
    <script>
                document.searchByFullNameForm.wildCardString.focus();
            </script>
    <?php else: ?>
    <script>
                document.searchByKeySkillsForm.wildCardString.focus();
            </script>
    <?php endif; ?>

    <?php if ($this->isResultsMode): ?>
    <br>
    <h2 class="h6 fw-semibold mt-2">Search Results</h2>

    <?php if (!empty($this->rs)): ?>
    <div class="card mb-2 oc-joborder-search-results">
        <div class="table-responsive">
            <table class="sortable table table-sm table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col"><span class="visually-hidden">Duplicate</span></th>
                        <th scope="col">First Name</th>
                        <th scope="col">Last Name</th>
                        <th scope="col">Key Skills</th>
                        <th scope="col">Created</th>
                        <th scope="col">Owner</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($this->rs as $rowNumber => $data): ?>
                    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                        <?php if($data['isDuplicateCandidate'] == 1): ?>
                        <td>
                            <img src="images/wf_error.gif" alt="" width="16" height="16" title="Duplicate Candidate">
                        </td>
                        <?php else: ?>
                        <td>
                            <img src="images/mru/blank.gif" alt="" width="16" height="16">
                        </td>
                        <?php endif; ?>
                        <?php if (!$data['inPipeline']): ?>
                        <td>
                            <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=addToPipeline&amp;getback=getback" style="display:inline;">
                                <input type="hidden" name="postback" value="postback">
                                <input type="hidden" name="jobOrderID" value="<?php echo($this->jobOrderID); ?>">
                                <input type="hidden" name="candidateID" value="<?php $this->_($data['candidateID']); ?>">
                                <button type="submit" class="btn btn-sm btn-link p-0">
                                <?php $this->_($data['firstName']); ?>
                                </button>
                            </form>
                            &nbsp;
                        </td>
                        <td>
                            <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=addToPipeline&amp;getback=getback" style="display:inline;">
                                <input type="hidden" name="postback" value="postback">
                                <input type="hidden" name="jobOrderID" value="<?php echo($this->jobOrderID); ?>">
                                <input type="hidden" name="candidateID" value="<?php $this->_($data['candidateID']); ?>">
                                <button type="submit" class="btn btn-sm btn-link p-0">
                                <?php $this->_($data['lastName']); ?>
                                </button>
                            </form>
                            &nbsp;
                        </td>
                        <?php else: ?>
                        <td><?php $this->_($data['firstName']); ?>&nbsp;</td>
                        <td><?php $this->_($data['lastName']); ?>&nbsp;</td>
                        <?php endif; ?>
                        <td><?php $this->_($data['keySkills']); ?>&nbsp;</td>
                        <td><?php $this->_($data['dateCreated']); ?>&nbsp;</td>
                        <td><?php $this->_($data['ownerAbbrName']); ?>&nbsp;</td>
                        <td>
                            <a href="#" title="Show Candidate" onclick="javascript:openCenteredPopup('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;display=popup&amp;candidateID=<?php $this->_($data['candidateID']); ?>', 'viewCandidateDetails', 1000, 675, true); return false;">
                            <img src="images/new_browser_inline.gif" alt="consider" width="16" height="16" class="absmiddle">
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info" role="status">No matching entries found.</div>
    <?php endif; ?>
    <?php endif; ?>
    <?php else: ?>
    <div class="alert alert-success" role="status">The selected candidate has been successfully added to the pipeline for this job order.</div>

    <form method="get" action="<?php echo(CATSUtility::getIndexName()); ?>">
        <button class="btn btn-sm btn-outline-secondary" type="button" name="close" value="Close" onclick="parentGoToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php echo($this->jobOrderID); ?>');">Close</button>
    </form>
    <?php endif; ?>
</main>
</body>
</html>
