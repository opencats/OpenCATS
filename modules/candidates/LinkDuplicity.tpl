<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */
?>
<?php TemplateUtility::printModalHeader('Candidates', array(), 'Select duplicate to this Candidate'); ?>
<main class="container-fluid p-2 oc-candidate-linkduplicity">

    <?php if (!$this->isFinishedMode): ?>
        <p>Search for a candidate below, and then click on the candidate name to link
        this candidate as a duplicate to them.</p>

        <div class="card card-body p-2 mb-2">
            <form id="searchByCandidateNameForm" name="searchByJobTitleForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=linkDuplicate" method="post">
                <input type="hidden" name="postback" id="postback" value="postback">
                <input type="hidden" id="mode_candidateName" name="mode" value="searchByCandidateName">
                <input type="hidden" id="candidateID" name="candidateID" value=<?php echo($this->duplicateCandidateID)?>>
                <input type="hidden" id="candidateID_jobtitle" name="candidateIDArrayStored" value="<?php echo($this->candidateIDArrayStored); ?>">

                <div class="row g-2 align-items-start mb-2">
                    <div class="col-12 col-sm"><label for="wildCardString_candidateName">Search by Candidate Name:</label></div>
                    <div class="col-12 col-sm"><div class="d-flex align-items-center gap-1"><input type="text" class="form-control form-control-sm" id="wildCardString_candidateName" name="wildCardString"><span class="text-danger" title="Required">*</span></div></div>
                </div>
                <div class="row g-2 align-items-start mb-2">
                    <div class="col-12 col-sm"><button type="submit" class="btn btn-sm btn-primary" id="searchByCandidateName" name="searchByCandidateName" value="Search by Candidate Name">Search by Candidate Name</button></div>
                </div>
                <div class="row g-2 align-items-start mb-2">
                    <div class="col-12 col-sm">&nbsp;</div>
                </div>
            </form>
        </div>

        <?php if (empty($_POST['mode']) || $_POST['mode'] == 'searchByCandidateName'): ?>
            <script>
                document.searchByCandidateNameForm.wildCardString.focus();
            </script>
        <?php endif; ?>

        <?php if ($this->isResultsMode): ?>
            <br>
            <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">Search Results</h2>

            <?php if (!empty($this->rs)): ?>
                <div class="table-responsive"><table class="table table-sm table-striped table-hover align-middle mb-0 sortable">
                    <thead><tr>
                        <th scope="col">First Name</th>
                        <th scope="col">Last Name</th>
                        <th scope="col">E-mail</th>
                        <th scope="col">Cell phone</th>
                        <th scope="col">City</th>
                        <th scope="col">State</th>
                        <th scope="col">Owner</th>
                        <th scope="col">Action</th>
                    </tr></thead><tbody>

                    <?php foreach ($this->rs as $rowNumber => $data): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td>
                                <?php if (!$data['linked']): ?>
                                    <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addDuplicates&amp;getback=getback" style="display:inline;">
                                        <input type="hidden" name="postback" value="postback">
                                        <input type="hidden" name="candidateID" value="<?php $this->_($data['candidateID']); ?>">
                                        <input type="hidden" name="duplicateCandidateID" value="<?php $this->_($data['duplicateCandidateID']); ?>">
                                        <button type="submit" class="<?php $this->_($data['linkClass']); ?> linkButton">
                                            <?php $this->_($data['firstName']); ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="<?php $this->_($data['linkClass']); ?>"><?php $this->_($data['firstName']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$data['linked']): ?>
                                    <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addDuplicates&amp;getback=getback" style="display:inline;">
                                        <input type="hidden" name="postback" value="postback">
                                        <input type="hidden" name="candidateID" value="<?php $this->_($data['candidateID']); ?>">
                                        <input type="hidden" name="duplicateCandidateID" value="<?php $this->_($data['duplicateCandidateID']); ?>">
                                        <button type="submit" class="<?php $this->_($data['linkClass']); ?> linkButton">
                                            <?php $this->_($data['lastName']); ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="<?php $this->_($data['linkClass']); ?>"><?php $this->_($data['lastName']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php $this->_($data['email1']); ?></td>
                            <td><?php $this->_($data['phoneCell']); ?></td>
                            <td><?php $this->_($data['city']); ?></td>
                            <td><?php $this->_($data['state']); ?></td>
                            <td><?php $this->_($data['ownerFirstName'])." ".$this->_($data['ownerLastName']); ?></td>
                            <td>
                                <a href="#" title="Show Candidate" onclick="javascript:openCenteredPopup('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;display=popup&amp;candidateID=<?php $this->_($data['candidateID']); ?>', 'viewCandidateDetails', 1000, 675, true); return false;">
                                    <img src="images/new_browser_inline.gif" alt="consider" width="16" height="16" class="absmiddle">
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody></table></div>
            <?php else: ?>
                <p>No matching entries found.</p>
            <?php endif; ?>
        <?php else: ?>
            <br>
            <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">All Candidates</h2>

            <?php if (!empty($this->rs)): ?>
                <div class="table-responsive"><table class="table table-sm table-striped table-hover align-middle mb-0 sortable">
                    <thead><tr>
                        <th scope="col">First Name</th>
                        <th scope="col">Last Name</th>
                        <th scope="col">E-mail</th>
                        <th scope="col">Cell phone</th>
                        <th scope="col">City</th>
                        <th scope="col">State</th>
                        <th scope="col">Owner</th>
                        <th scope="col">Action</th>
                    </tr></thead><tbody>

                    <?php foreach ($this->rs as $rowNumber => $data): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td>
                                <?php if (!$data['linked']): ?>
                                    <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addDuplicates&amp;getback=getback" style="display:inline;">
                                        <input type="hidden" name="postback" value="postback">
                                        <input type="hidden" name="candidateID" value="<?php $this->_($data['candidateID']); ?>">
                                        <input type="hidden" name="duplicateCandidateID" value="<?php $this->_($data['duplicateCandidateID']); ?>">
                                        <button type="submit" class="<?php $this->_($data['linkClass']); ?> linkButton">
                                            <?php $this->_($data['firstName']); ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="<?php $this->_($data['linkClass']); ?>"><?php $this->_($data['firstName']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$data['linked']): ?>
                                    <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addDuplicates&amp;getback=getback" style="display:inline;">
                                        <input type="hidden" name="postback" value="postback">
                                        <input type="hidden" name="candidateID" value="<?php $this->_($data['candidateID']); ?>">
                                        <input type="hidden" name="duplicateCandidateID" value="<?php $this->_($data['duplicateCandidateID']); ?>">
                                        <button type="submit" class="<?php $this->_($data['linkClass']); ?> linkButton">
                                            <?php $this->_($data['lastName']); ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="<?php $this->_($data['linkClass']); ?>"><?php $this->_($data['lastName']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php $this->_($data['email1']); ?></td>
                            <td><?php $this->_($data['phoneCell']); ?></td>
                            <td><?php $this->_($data['city']); ?></td>
                            <td><?php $this->_($data['state']); ?></td>
                            <td><?php $this->_($data['ownerFirstName'])." ".$this->_($data['ownerLastName']); ?></td>
                            <td>
                                <a href="#" title="Show Candidate" onclick="javascript:openCenteredPopup('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;display=popup&amp;candidateID=<?php $this->_($data['candidateID']); ?>', 'viewCandidateDetails', 1000, 675, true); return false;">
                                    <img src="images/new_browser_inline.gif" alt="consider" width="16" height="16" class="absmiddle">
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody></table></div>
            <?php else: ?>
                <p>No candidates found.</p>
            <?php endif; ?>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-success py-2" role="alert">This candidate has been successfully added as a duplicate for the selected candidate.</div>

        <form method="get" action="<?php echo(CATSUtility::getIndexName()); ?>">
            <button type="button" name="close" value="Close" onclick="parentHidePopWinRefresh();" class="btn btn-sm btn-outline-secondary">Close</button>
        </form>
    <?php endif; ?>

</main>
    </body>
</html>
